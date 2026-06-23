const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const crypto = require('crypto');
const path = require('path');

const app = express();
const PORT = 3456;  // Different port

app.use(express.json());
app.use(express.static('.'));

const db = new sqlite3.Database('dropship.db');

function hashPassword(pwd) {
    return crypto.createHash('sha256').update(pwd).digest('hex');
}

// Test endpoint
app.get('/test', (req, res) => {
    res.json({ status: 'ok', time: Date.now() });
});

// Login endpoint
app.post('/login', (req, res) => {
    const { phone, password } = req.body;
    const hash = hashPassword(password);
    
    db.get('SELECT au.*, a.name FROM agent_users au JOIN agents a ON au.agent_id = a.id WHERE au.phone = ? AND au.password_hash = ?', 
        [phone, hash], 
        (err, user) => {
            if (err || !user) {
                res.json({ success: false, message: 'Invalid credentials' });
                return;
            }
            res.json({ success: true, name: user.name, message: 'Login successful!' });
        });
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`✅ SIMPLE SERVER RUNNING on port ${PORT}`);
    console.log(`📍 Test: http://localhost:${PORT}/test`);
    console.log(`📍 Login: http://localhost:${PORT}/login`);
});
