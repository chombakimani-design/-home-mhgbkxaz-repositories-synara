// utils/payout.js
const { b2cPayment } = require('./mpesa');
const { db } = require('../server');

const MIN_PAYOUT = 100; // KES 100 minimum

async function runWeeklyPayout() {
    console.log('🔄 Running weekly payouts...');
    const results = [];

    // ─── Agents ──────────────────────────────────────────────────────
    const agentRows = await new Promise((resolve, reject) => {
        db.all(
            `SELECT a.id, a.name, a.phone, SUM(at.net_amount) as total_pending
             FROM agent_transactions at
             JOIN agents a ON at.agent_id = a.id
             WHERE at.settled = 0
             GROUP BY a.id
             HAVING total_pending >= ?`,
            [MIN_PAYOUT],
            (err, rows) => {
                if (err) reject(err);
                else resolve(rows || []);
            }
        );
    });

    for (const agent of agentRows) {
        try {
            const result = await b2cPayment(agent.phone, agent.total_pending, 'BusinessPayment', 'Weekly agent earnings');
            await new Promise((resolve, reject) => {
                db.run(
                    `UPDATE agent_transactions SET settled = 1, settled_date = CURRENT_TIMESTAMP WHERE agent_id = ? AND settled = 0`,
                    [agent.id],
                    (err) => {
                        if (err) reject(err);
                        else resolve();
                    }
                );
            });
            // Record payout history
            await new Promise((resolve, reject) => {
                db.run(
                    `INSERT INTO payout_history (recipient_id, recipient_type, recipient_name, recipient_phone, amount, mpesa_conversation_id, status, completed_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP)`,
                    [agent.id, 'agent', agent.name, agent.phone, agent.total_pending, result.conversationId],
                    (err) => {
                        if (err) reject(err);
                        else resolve();
                    }
                );
            });
            console.log(`✅ Agent ${agent.name} paid KES ${agent.total_pending}`);
            results.push({ type: 'agent', id: agent.id, amount: agent.total_pending, status: 'success' });
        } catch (err) {
            console.error(`❌ Failed to pay agent ${agent.id}:`, err);
            results.push({ type: 'agent', id: agent.id, error: err.message, status: 'failed' });
        }
    }

    // ─── Riders ──────────────────────────────────────────────────────
    const riderRows = await new Promise((resolve, reject) => {
        db.all(
            `SELECT r.id, r.name, r.phone, SUM(rt.net_amount) as total_pending
             FROM rider_transactions rt
             JOIN riders r ON rt.rider_id = r.id
             WHERE rt.settled = 0
             GROUP BY r.id
             HAVING total_pending >= ?`,
            [MIN_PAYOUT],
            (err, rows) => {
                if (err) reject(err);
                else resolve(rows || []);
            }
        );
    });

    for (const rider of riderRows) {
        try {
            const result = await b2cPayment(rider.phone, rider.total_pending, 'BusinessPayment', 'Weekly rider earnings');
            await new Promise((resolve, reject) => {
                db.run(
                    `UPDATE rider_transactions SET settled = 1, settled_date = CURRENT_TIMESTAMP WHERE rider_id = ? AND settled = 0`,
                    [rider.id],
                    (err) => {
                        if (err) reject(err);
                        else resolve();
                    }
                );
            });
            await new Promise((resolve, reject) => {
                db.run(
                    `INSERT INTO payout_history (recipient_id, recipient_type, recipient_name, recipient_phone, amount, mpesa_conversation_id, status, completed_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP)`,
                    [rider.id, 'rider', rider.name, rider.phone, rider.total_pending, result.conversationId],
                    (err) => {
                        if (err) reject(err);
                        else resolve();
                    }
                );
            });
            console.log(`✅ Rider ${rider.name} paid KES ${rider.total_pending}`);
            results.push({ type: 'rider', id: rider.id, amount: rider.total_pending, status: 'success' });
        } catch (err) {
            console.error(`❌ Failed to pay rider ${rider.id}:`, err);
            results.push({ type: 'rider', id: rider.id, error: err.message, status: 'failed' });
        }
    }

    console.log('🎉 Weekly payout completed.');
    return results;
}

module.exports = { runWeeklyPayout };