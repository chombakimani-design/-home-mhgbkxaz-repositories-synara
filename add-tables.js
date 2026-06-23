const sqlite3 = require('sqlite3').verbose();
const crypto = require('crypto');

const db = new sqlite3.Database('dropship.db');

function hashPassword(password) {
    return crypto.createHash('sha256').update(password).digest('hex');
}

db.serialize(() => {
    // Enable foreign keys
    db.run('PRAGMA foreign_keys = ON');
    
    // Agent users table
    db.run(`CREATE TABLE IF NOT EXISTS agent_users (
        id TEXT PRIMARY KEY,
        agent_id TEXT,
        phone TEXT UNIQUE,
        password_hash TEXT,
        created_at INTEGER,
        FOREIGN KEY(agent_id) REFERENCES agents(id) ON DELETE CASCADE
    )`);
    
    // Rider users table
    db.run(`CREATE TABLE IF NOT EXISTS rider_users (
        id TEXT PRIMARY KEY,
        rider_id TEXT,
        phone TEXT UNIQUE,
        password_hash TEXT,
        created_at INTEGER,
        FOREIGN KEY(rider_id) REFERENCES boda_riders(id) ON DELETE CASCADE
    )`);
    
    // Admin users table
    db.run(`CREATE TABLE IF NOT EXISTS admin_users (
        id TEXT PRIMARY KEY,
        username TEXT UNIQUE,
        password_hash TEXT
    )`);
    
    // Sessions table
    db.run(`CREATE TABLE IF NOT EXISTS sessions (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        role TEXT,
        expires_at INTEGER
    )`);
    
    // Indexes for performance
    db.run(`CREATE INDEX IF NOT EXISTS idx_agent_users_phone ON agent_users(phone)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_rider_users_phone ON rider_users(phone)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_sessions_id ON sessions(id)`);
    db.run(`CREATE INDEX IF NOT EXISTS idx_sessions_expires ON sessions(expires_at)`);
    
    // Insert default admin (username: admin, password: admin123)
    const adminHash = hashPassword('admin123');
    db.run(`INSERT OR IGNORE INTO admin_users (id, username, password_hash) VALUES (?, ?, ?)`, 
        ['1', 'admin', adminHash]);
    
    console.log('✅ Tables created successfully');
    console.log('📋 Admin login: username="admin", password="admin123"');
});

db.close();
