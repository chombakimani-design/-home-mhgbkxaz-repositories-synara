const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('dropship.db');
const sql = "CREATE TABLE IF NOT EXISTS payout_history (" +
    "id INTEGER PRIMARY KEY AUTOINCREMENT," +
    "recipient_id TEXT NOT NULL," +
    "recipient_type TEXT NOT NULL," +
    "recipient_name TEXT," +
    "recipient_phone TEXT," +
    "amount REAL NOT NULL," +
    "mpesa_conversation_id TEXT," +
    "status TEXT DEFAULT 'pending'," +
    "created_at DATETIME DEFAULT CURRENT_TIMESTAMP," +
    "completed_at DATETIME" +
");";
db.exec(sql, (err) => {
    if (err) console.error('Error:', err);
    else console.log('✅ Payout history table created');
    db.close();
});
