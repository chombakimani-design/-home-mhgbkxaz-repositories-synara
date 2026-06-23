const sqlite3 = require('sqlite3').verbose();
const crypto = require('crypto');

const db = new sqlite3.Database('dropship.db');

function hashPassword(password) {
    return crypto.createHash('sha256').update(password).digest('hex');
}

const username = 'admin';
const passwordHash = hashPassword('admin'); // password is "admin"

db.run(
    `INSERT OR REPLACE INTO admin_users (username, password_hash) VALUES (?, ?)`,
    [username, passwordHash],
    function(err) {
        if (err) {
            console.error('Error:', err.message);
        } else {
            console.log('✅ Admin user created/updated successfully!');
            console.log(`Username: ${username}`);
            console.log('Password: admin');
        }
        db.close();
    }
);