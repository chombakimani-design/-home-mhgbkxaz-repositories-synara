// routes/mpesa.js
const express = require('express');
const router = express.Router();
const axios = require('axios');
const { db } = require('../server');

const CONSUMER_KEY = process.env.MPESA_CONSUMER_KEY;
const CONSUMER_SECRET = process.env.MPESA_CONSUMER_SECRET;
const SHORTCODE = process.env.MPESA_SHORTCODE;
const PASSKEY = process.env.MPESA_PASSKEY;
const BASE_URL = process.env.MPESA_ENV === 'sandbox'
  ? 'https://sandbox.safaricom.co.ke'
  : 'https://api.safaricom.co.ke';

// ─── Fee configuration ────────────────────────────────────────────
const AGENT_FEE_PERCENTAGE = 9;
const AGENT_FEE_CAP = 500;
const RIDER_FEE_PERCENTAGE = 9;
const RIDER_FEE_CAP = 100;

// ─── Helpers: Calculate fees ──────────────────────────────────────
function calculateAgentFee(amount) {
    const fee = (amount * AGENT_FEE_PERCENTAGE) / 100;
    return Math.min(fee, AGENT_FEE_CAP);
}

function calculateRiderFee(amount) {
    const fee = (amount * RIDER_FEE_PERCENTAGE) / 100;
    return Math.min(fee, RIDER_FEE_CAP);
}

// ─── Get OAuth Token ──────────────────────────────────────────────
async function getAccessToken() {
    const auth = Buffer.from(`${CONSUMER_KEY}:${CONSUMER_SECRET}`).toString('base64');
    const response = await axios.get(
        `${BASE_URL}/oauth/v1/generate?grant_type=client_credentials`,
        { headers: { Authorization: `Basic ${auth}` } }
    );
    return response.data.access_token;
}

// ─── Initiate STK Push ────────────────────────────────────────────
async function stkPush(phoneNumber, amount, accountRef, transactionDesc = 'Payment for order') {
    const token = await getAccessToken();
    const timestamp = new Date().toISOString().replace(/[^0-9]/g, '').slice(0, 14);
    const password = Buffer.from(`${SHORTCODE}${PASSKEY}${timestamp}`).toString('base64');
    const formattedPhone = phoneNumber.replace(/^0+/, '').replace(/^\+254/, '254');

    const payload = {
        BusinessShortCode: SHORTCODE,
        Password: password,
        Timestamp: timestamp,
        TransactionType: 'CustomerPayBillOnline',
        Amount: amount,
        PartyA: formattedPhone,
        PartyB: SHORTCODE,
        PhoneNumber: formattedPhone,
        CallBackURL: process.env.CALLBACK_URL,
        AccountReference: accountRef || 'SYNARA Order',
        TransactionDesc: transactionDesc,
    };

    const response = await axios.post(
        `${BASE_URL}/mpesa/stkpush/v1/processrequest`,
        payload,
        { headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } }
    );
    return {
        success: true,
        checkoutRequestId: response.data.CheckoutRequestID,
        responseCode: response.data.ResponseCode,
        responseDescription: response.data.ResponseDescription,
    };
}

// ─── POST /api/mpesa/stkpush ──────────────────────────────────────
router.post('/stkpush', async (req, res) => {
    try {
        const { phoneNumber, amount, accountReference, transactionDesc } = req.body;
        if (!phoneNumber || !amount) {
            return res.status(400).json({ success: false, error: 'Phone and amount are required' });
        }
        const result = await stkPush(phoneNumber, amount, accountReference, transactionDesc);

        if (accountReference && accountReference.startsWith('Order-')) {
            const orderId = accountReference.replace('Order-', '');
            db.run(
                `UPDATE orders SET checkout_request_id = ? WHERE id = ?`,
                [result.checkoutRequestId, orderId],
                (err) => { if (err) console.error('Failed to update order with checkout ID:', err); }
            );
        }
        res.json({
            success: true,
            checkoutRequestId: result.checkoutRequestId,
            message: 'STK Push sent. Please check your phone.',
        });
    } catch (error) {
        console.error('STK Push error:', error);
        res.status(500).json({ success: false, error: error.response?.data?.errorMessage || 'STK Push failed' });
    }
});

// ─── POST /api/mpesa/callback ─────────────────────────────────────
router.post('/callback', async (req, res) => {
    try {
        const callbackData = req.body;
        console.log('📞 M-Pesa Callback received:', JSON.stringify(callbackData, null, 2));

        const { stkCallback } = callbackData.Body;
        const resultCode = stkCallback.ResultCode;
        const resultDesc = stkCallback.ResultDesc;
        const checkoutRequestId = stkCallback.CheckoutRequestID;
        const metadata = stkCallback.CallbackMetadata?.Item || [];

        // Query order details – include product_total, delivery_fee, agent_id, rider_id
        db.get(
            `SELECT id, agent_id, rider_id, product_total, delivery_fee, buyer_phone, total_amount
             FROM orders WHERE checkout_request_id = ?`,
            [checkoutRequestId],
            (err, order) => {
                if (err) {
                    console.error('Database error:', err);
                    return res.status(200).json({ ResultCode: 0, ResultDesc: 'Success' });
                }
                if (!order) {
                    console.warn(`⚠️ Order not found for checkoutRequestId: ${checkoutRequestId}`);
                    return res.status(200).json({ ResultCode: 0, ResultDesc: 'Success' });
                }

                if (resultCode === 0) {
                    const receiptItem = metadata.find(item => item.Name === 'MpesaReceiptNumber');
                    const phoneItem = metadata.find(item => item.Name === 'PhoneNumber');
                    const amountItem = metadata.find(item => item.Name === 'Amount');

                    const totalAmount = amountItem?.Value || order.total_amount;
                    const productTotal = order.product_total || totalAmount; // fallback
                    const deliveryFee = order.delivery_fee || 0;

                    // ─── Calculate fees ────────────────────────────────────
                    const agentFee = calculateAgentFee(productTotal);
                    const agentNet = productTotal - agentFee;
                    const riderFee = calculateRiderFee(deliveryFee);
                    const riderNet = deliveryFee - riderFee;

                    // ─── Update order with payment and agent fee details ────
                    db.run(
                        `UPDATE orders 
                         SET status = 'paid', 
                             payment_status = 'paid',
                             mpesa_receipt = ?,
                             payment_amount = ?,
                             payment_phone = ?,
                             facilitation_fee = ?,
                             net_amount = ?,
                             payment_date = CURRENT_TIMESTAMP,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id = ?`,
                        [
                            receiptItem?.Value || null,
                            totalAmount,
                            phoneItem?.Value || null,
                            agentFee,
                            agentNet,
                            order.id
                        ],
                        (err) => {
                            if (err) {
                                console.error('Failed to update order:', err);
                            } else {
                                console.log(`✅ Order ${order.id} marked as paid. Receipt: ${receiptItem?.Value}`);
                                console.log(`   Product: KES ${productTotal}, Delivery: KES ${deliveryFee}`);
                                console.log(`   Agent Fee: KES ${agentFee}, Agent Net: KES ${agentNet}`);
                                console.log(`   Rider Fee: KES ${riderFee}, Rider Net: KES ${riderNet}`);
                            }
                        }
                    );

                    // ─── Record agent transaction ──────────────────────────
                    if (order.agent_id) {
                        db.run(
                            `INSERT INTO agent_transactions 
                             (agent_id, order_id, customer_phone, amount, facilitation_fee, net_amount, fee_percentage, fee_cap, mpesa_receipt, transaction_date)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)`,
                            [
                                order.agent_id,
                                order.id,
                                phoneItem?.Value || null,
                                productTotal,
                                agentFee,
                                agentNet,
                                AGENT_FEE_PERCENTAGE,
                                AGENT_FEE_CAP,
                                receiptItem?.Value || null
                            ],
                            (err) => {
                                if (err) console.error('Failed to insert agent transaction:', err);
                                else console.log(`✅ Agent transaction recorded for order ${order.id}`);
                            }
                        );
                    } else {
                        console.warn(`⚠️ No agent_id found for order ${order.id}, skipping agent transaction.`);
                    }

                    // ─── Record rider transaction ──────────────────────────
                    if (order.rider_id && deliveryFee > 0) {
                        db.run(
                            `INSERT INTO rider_transactions 
                             (rider_id, order_id, customer_phone, amount, facilitation_fee, net_amount, fee_percentage, fee_cap, mpesa_receipt, transaction_date)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)`,
                            [
                                order.rider_id,
                                order.id,
                                phoneItem?.Value || null,
                                deliveryFee,
                                riderFee,
                                riderNet,
                                RIDER_FEE_PERCENTAGE,
                                RIDER_FEE_CAP,
                                receiptItem?.Value || null
                            ],
                            (err) => {
                                if (err) console.error('Failed to insert rider transaction:', err);
                                else console.log(`✅ Rider transaction recorded for order ${order.id}`);
                            }
                        );
                    } else if (deliveryFee > 0 && !order.rider_id) {
                        console.warn(`⚠️ Delivery fee KES ${deliveryFee} exists but no rider assigned for order ${order.id}`);
                    }

                } else {
                    // Payment failed
                    db.run(
                        `UPDATE orders SET payment_status = 'failed', status = 'payment_failed', updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
                        [order.id],
                        (err) => { if (err) console.error('Failed to update order (failed):', err); }
                    );
                }
            }
        );
        res.status(200).json({ ResultCode: 0, ResultDesc: 'Success' });
    } catch (error) {
        console.error('Callback processing error:', error);
        res.status(200).json({ ResultCode: 0, ResultDesc: 'Success' });
    }
});

module.exports = router;