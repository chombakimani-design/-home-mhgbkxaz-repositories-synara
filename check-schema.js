const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('dropship.db');

// Check columns in orders table
db.all("SELECT name FROM pragma_table_info('orders')", (err, rows) => {
    if (err) {
        console.error('Error:', err.message);
        return;
    }
    console.log('?? Columns in orders:');
    console.log(rows.map(r => r.name).join(', '));
    
    // Also check if agent_transactions table exists
    db.all("SELECT name FROM sqlite_master WHERE type='table' AND name='agent_transactions'", (err2, tables) => {
        if (err2) {
            console.error('Error checking tables:', err2.message);
        } else {
            console.log('\n?? agent_transactions table exists?', tables.length > 0 ? '? Yes' : '? No');
        }
        db.close();
    });
});
