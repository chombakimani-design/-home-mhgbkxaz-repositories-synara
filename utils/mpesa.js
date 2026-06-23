// utils/mpesa.js
const axios = require('axios');

const CONSUMER_KEY = process.env.MPESA_CONSUMER_KEY;
const CONSUMER_SECRET = process.env.MPESA_CONSUMER_SECRET;
const SHORTCODE = process.env.MPESA_SHORTCODE;
const PASSKEY = process.env.MPESA_PASSKEY;
const BASE_URL = process.env.MPESA_ENV === 'sandbox'
  ? 'https://sandbox.safaricom.co.ke'
  : 'https://api.safaricom.co.ke';

async function getAccessToken() {
    const auth = Buffer.from(`${CONSUMER_KEY}:${CONSUMER_SECRET}`).toString('base64');
    const response = await axios.get(
        `${BASE_URL}/oauth/v1/generate?grant_type=client_credentials`,
        { headers: { Authorization: `Basic ${auth}` } }
    );
    return response.data.access_token;
}

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

// ─── B2C Payment ──────────────────────────────────────────────────
async function b2cPayment(phone, amount, command = 'BusinessPayment', remarks = 'Synara Payout') {
    const token = await getAccessToken();
    const timestamp = new Date().toISOString().replace(/[^0-9]/g, '').slice(0, 14);

    // SecurityCredential is generated from your initiator password
    // In sandbox, you can use a dummy value; in production, use proper encryption
    const securityCredential = process.env.MPESA_SECURITY_CREDENTIAL || 'dummy';

    const payload = {
        InitiatorName: process.env.MPESA_INITIATOR_NAME || 'testapi',
        SecurityCredential: securityCredential,
        CommandID: command, // 'BusinessPayment', 'SalaryPayment', 'PromotionPayment'
        Amount: amount,
        PartyA: process.env.MPESA_SHORTCODE,
        PartyB: phone.replace(/^0+/, '').replace(/^\+254/, '254'),
        Remarks: remarks,
        QueueTimeOutURL: process.env.B2C_CALLBACK_URL + '/timeout',
        ResultURL: process.env.B2C_CALLBACK_URL + '/result',
        Occasion: 'Weekly payout'
    };

    const response = await axios.post(
        `${BASE_URL}/mpesa/b2c/v1/paymentrequest`,
        payload,
        { headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' } }
    );
    return {
        success: true,
        conversationId: response.data.ConversationID,
        responseCode: response.data.ResponseCode,
        responseDescription: response.data.ResponseDescription,
    };
}

module.exports = { getAccessToken, stkPush, b2cPayment };