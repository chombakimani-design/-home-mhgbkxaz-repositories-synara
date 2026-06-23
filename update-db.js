const sqlite3 = require('sqlite3').verbose();
const path = require('path');

const dbPath = path.join(__dirname, 'dropship.db');
const db = new sqlite3.Database(dbPath);

const sql = `
ALTER TABLE orders ADD COLUMN facilitation_fee REAL;
ALTER TABLE orders ADD COLUMN net_amount REAL;
ALTER TABLE orders ADD COLUMN agent_settled INTEGER DEFAULT 0;
ALTER TABLE orders ADD COLUMN agent_id TEXT;

CREATE TABLE IF NOT EXISTS agent_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    agent_id TEXT NOT NULL,
    order_id TEXT NOT NULL,
    customer_phone TEXT,
    amount REAL NOT NULL,
    facilitation_fee REAL NOT NULL,
    net_amount REAL NOT NULL,
    fee_percentage REAL,
    fee_cap REAL,
    status TEXT DEFAULT 'pending',
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    mpesa_receipt TEXT,
    settled INTEGER DEFAULT 0,
    settled_date DATETIME
);
`;

const statements = sql.split(';').filter(s => s.trim() !== '');

let completed = 0;
statements.forEach(stmt => {
    db.exec(stmt, (err) => {
        if (err) {
            if (err.message.includes('duplicate column name')) {
                console.log('?? Column already exists, skipping:', err.message);
            } else {
                console.error('Error executing:', err.message);
            }
        } else {
            console.log('? Executed:', stmt.trim().slice(0, 60) + '...');
        }
        completed++;
        if (completed === statements.length) {
            console.log('\n?? Database update complete!');
            db.close();
        }
    });
});
