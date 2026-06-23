const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const crypto = require('crypto');

const app = express();
app.use(express.json());

const db = new sqlite3.Database('dropship.db');

function hashPassword(p) {
    return crypto.createHash('sha256').update(p).digest('hex');
}

app.post('/api/agent/login', (req, res) => {
    console.log('Request received:', req.body);
    const { phone, password } = req.body;
    const hash = hashPassword(password);
    
    db.get('SELECT * FROM agent_users WHERE phone = ?', [phone], (err, user) => {
        console.log('Database result:', user);
        if (!user) {
            res.json({ success: false, error: 'Phone not found' });
            return;
        }
        console.log('Stored hash:', user.password_hash);
        console.log('Generated hash:', hash);
        console.log('Match:', user.password_hash === hash);
        
        if (user.password_hash !== hash) {
            res.json({ success: false, error: 'Invalid password' });
            return;
        }
        
        res.json({ success: true, message: 'Login successful' });
    });
});

app.listen(3001, () => {
    console.log('Test server running on port 3001');
});
