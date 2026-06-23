const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('dropship.db');

// All columns needed for the enhanced product table
const columns = [
    { name: 'is_featured', type: 'INTEGER DEFAULT 0' },
    { name: 'discount_percent', type: 'INTEGER DEFAULT 0' },
    { name: 'low_stock_threshold', type: 'INTEGER DEFAULT 5' },
    { name: 'category', type: 'TEXT' },
    { name: 'images', type: 'TEXT' },          // JSON array stored as TEXT
    { name: 'discount_start', type: 'INTEGER' }, // Unix timestamp
    { name: 'discount_end', type: 'INTEGER' }    // Unix timestamp
];

columns.forEach(col => {
    const sql = `ALTER TABLE products ADD COLUMN ${col.name} ${col.type}`;
    db.run(sql, (err) => {
        if (err) {
            if (err.message.includes('duplicate column name')) {
                console.log(`ℹ️ Column '${col.name}' already exists.`);
            } else {
                console.error(`❌ Error adding '${col.name}': ${err.message}`);
            }
        } else {
            console.log(`✅ Added column: ${col.name} ${col.type}`);
        }
    });
});

db.close(() => {
    console.log('📁 Database connection closed.');
});