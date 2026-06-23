require('dotenv').config();
const express = require('express');
const path = require('path');
const crypto = require('crypto');
const multer = require('multer');
const fs = require('fs');
const axios = require('axios');
const cron = require('node-cron'); // for weekly scheduler
const { runWeeklyPayout } = require('./utils/payout');
const { b2cPayment } = require('./utils/mpesa');
const db = require('./db'); // Import the database instance

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static('.'));

// ─── Helper functions ────────────────────────────────────────────
function hashPassword(password) {
    return crypto.createHash('sha256').update(password).digest('hex');
}

function generateSessionId() {
    return crypto.randomBytes(32).toString('hex');
}

// ─── Multer for file uploads ──────────────────────────────────────
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir);

const storage = multer.diskStorage({
    destination: (req, file, cb) => cb(null, 'uploads/'),
    filename: (req, file, cb) => {
        const unique = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, unique + '-' + file.originalname);
    }
});
const upload = multer({ storage });

// ─── Helper: create agent user ───────────────────────────────────
function createAgentUser(agentId, phone, passwordHash, res) {
    db.run(
        `INSERT INTO agent_users (id, agent_id, phone, password_hash, created_at)
         VALUES (?, ?, ?, ?, ?)`,
        [agentId, agentId, phone, passwordHash, Date.now()],
        function(err) {
            if (err) {
                res.status(500).json({ error: err.message });
                return;
            }
            res.json({ success: true, password: '12345678', agentId });
        }
    );
}

// ─── Create rider_ratings table if not exists ──────────────────
db.run(`
    CREATE TABLE IF NOT EXISTS rider_ratings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        rider_id TEXT NOT NULL,
        rating INTEGER NOT NULL,
        comment TEXT,
        rated_by TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rider_id) REFERENCES riders(id)
    )
`, (err) => {
    if (err) console.error('Error creating rider_ratings:', err);
    else console.log('✅ rider_ratings table ready');
});

// ─── File Upload ──────────────────────────────────────────────────
app.post('/api/upload', upload.array('images', 10), (req, res) => {
    if (!req.files || req.files.length === 0) {
        return res.status(400).json({ error: 'No files uploaded' });
    }
    const urls = req.files.map(file => `/uploads/${file.filename}`);
    res.json({ urls });
});

// ─── Agent Management ─────────────────────────────────────────────
app.post('/api/agents', (req, res) => {
    const { name, phone, location } = req.body;
    const id = Date.now().toString();
    const tempPassword = '12345678';
    const passwordHash = hashPassword(tempPassword);

    db.get('SELECT id FROM agent_users WHERE phone = ?', [phone], (err, existing) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        if (existing) return res.status(409).json({ error: 'Phone already registered' });

        const businessName = name + "'s Shop";
        db.run(
            `INSERT INTO agents (id, name, phone, location, business_name, created_at)
             VALUES (?, ?, ?, ?, ?, ?)`,
            [id, name, phone, location, businessName, Date.now()],
            function(err2) {
                if (err2) return res.status(500).json({ error: err2.message });
                createAgentUser(id, phone, passwordHash, res);
            }
        );
    });
});

app.get('/api/agents', (req, res) => {
    db.all('SELECT * FROM agents ORDER BY created_at DESC', (err, rows) => {
        res.json(rows || []);
    });
});

app.delete('/api/agents/:id', (req, res) => {
    const agentId = req.params.id;
    db.run('DELETE FROM agent_users WHERE agent_id = ?', [agentId], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        db.run('DELETE FROM agents WHERE id = ?', [agentId], (err2) => {
            if (err2) return res.status(500).json({ error: err2.message });
            res.json({ deleted: true });
        });
    });
});

// ─── Agent Login ──────────────────────────────────────────────────
app.post('/api/agent/login', (req, res) => {
    const { phone, password } = req.body;
    const passwordHash = hashPassword(password);
    db.get('SELECT * FROM agent_users WHERE phone = ?', [phone], (err, user) => {
        if (!user) return res.json({ success: false, error: 'Phone not found' });
        if (user.password_hash !== passwordHash) return res.json({ success: false, error: 'Invalid password' });
        db.get('SELECT * FROM agents WHERE id = ?', [user.agent_id], (err2, agent) => {
            if (!agent) return res.json({ success: false, error: 'Agent not found' });
            const sessionId = generateSessionId();
            const expiresAt = Date.now() + (7 * 24 * 60 * 60 * 1000);
            db.run(`INSERT INTO sessions (id, user_id, role, expires_at) VALUES (?, ?, ?, ?)`,
                [sessionId, agent.id, 'agent', expiresAt],
                () => res.json({ success: true, sessionId, role: 'agent', name: agent.name })
            );
        });
    });
});

app.get('/api/agent/session', (req, res) => {
    const sessionId = req.headers['x-session-id'];
    db.get('SELECT * FROM sessions WHERE id = ? AND expires_at > ?', [sessionId, Date.now()], (err, session) => {
        if (err || !session) return res.json({ authenticated: false });
        db.get('SELECT name FROM agents WHERE id = ?', [session.user_id], (err2, agent) => {
            res.json({ authenticated: true, role: 'agent', userId: session.user_id, name: agent?.name || null });
        });
    });
});

app.get('/api/verify-session', (req, res) => {
    const sessionId = req.headers['x-session-id'];
    if (!sessionId) return res.json({ authenticated: false });
    db.get('SELECT * FROM sessions WHERE id = ? AND expires_at > ?', [sessionId, Date.now()], (err, session) => {
        if (err || !session) return res.json({ authenticated: false });
        res.json({ authenticated: true, role: session.role, userId: session.user_id });
    });
});

app.post('/api/logout', (req, res) => {
    db.run('DELETE FROM sessions WHERE id = ?', [req.headers['x-session-id']], () => res.json({ success: true }));
});

// ─── Admin Login ──────────────────────────────────────────────────
app.post('/api/admin/login', (req, res) => {
    const { username, password } = req.body;
    const passwordHash = hashPassword(password);
    db.get('SELECT * FROM admin_users WHERE username = ? AND password_hash = ?', [username, passwordHash], (err, user) => {
        if (err || !user) return res.json({ success: false, error: 'Invalid credentials' });
        const sessionId = generateSessionId();
        const expiresAt = Date.now() + (7 * 24 * 60 * 60 * 1000);
        db.run(`INSERT INTO sessions (id, user_id, role, expires_at) VALUES (?, ?, ?, ?)`, [sessionId, user.id, 'admin', expiresAt],
            () => res.json({ success: true, sessionId, role: 'admin' })
        );
    });
});

// ─── Agent Applications ───────────────────────────────────────────
app.post('/api/agent/apply', (req, res) => {
    const { ownerName, businessName, phone, email, county, location, category, description } = req.body;
    const id = Date.now().toString();
    db.run(`INSERT INTO agent_applications (id, owner_name, business_name, phone, email, county, location, business_category, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [id, ownerName, businessName, phone, email, county, location, category, description, 'pending', Date.now()],
        (err) => err ? res.status(500).json({ error: err.message }) : res.json({ success: true })
    );
});

app.get('/api/admin/applications', (req, res) => {
    db.all('SELECT * FROM agent_applications ORDER BY created_at DESC', (err, rows) => res.json(rows || []));
});

app.post('/api/admin/applications/:id/approve', (req, res) => {
    const id = req.params.id;
    db.get('SELECT * FROM agent_applications WHERE id = ?', [id], (err, app) => {
        if (err || !app) return res.status(404).json({ error: 'Application not found' });
        const agentId = Date.now().toString();
        const tempPassword = '12345678';
        const passwordHash = hashPassword(tempPassword);
        db.run(`INSERT INTO agents (id, name, phone, location, business_name, created_at) VALUES (?, ?, ?, ?, ?, ?)`,
            [agentId, app.owner_name, app.phone, app.location, app.business_name, Date.now()],
            () => {
                db.run(`INSERT INTO agent_users (id, agent_id, phone, password_hash, created_at) VALUES (?, ?, ?, ?, ?)`,
                    [agentId, agentId, app.phone, passwordHash, Date.now()],
                    () => {
                        db.run(`UPDATE agent_applications SET status = 'approved' WHERE id = ?`, [id]);
                        res.json({ success: true, password: tempPassword });
                    }
                );
            }
        );
    });
});

// ─── Products ──────────────────────────────────────────────────────
app.get('/api/products', (req, res) => {
    db.all(`
        SELECT p.*, a.name as agent_name, a.phone as agent_phone
        FROM products p
        LEFT JOIN agents a ON p.agent_id = a.id
        ORDER BY p.created_at DESC
    `, (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        const products = rows.map(p => ({
            ...p,
            images: p.images ? JSON.parse(p.images) : [],
            status: p.stock <= 0 ? 'out_of_stock' : p.stock <= (p.low_stock_threshold || 5) ? 'low_stock' : 'in_stock'
        }));
        res.json(products);
    });
});

app.get('/api/agent/:agentId/products', (req, res) => {
    db.all('SELECT * FROM products WHERE agent_id = ? ORDER BY created_at DESC', [req.params.agentId], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        const products = rows.map(p => ({
            ...p,
            images: p.images ? JSON.parse(p.images) : [],
            status: p.stock <= 0 ? 'out_of_stock' : p.stock <= (p.low_stock_threshold || 5) ? 'low_stock' : 'in_stock'
        }));
        res.json(products);
    });
});

app.post('/api/products', (req, res) => {
    const { agent_id, name, description, price_kes, stock, image_url, images, category, discount_percent, discount_start, discount_end } = req.body;
    const id = Date.now().toString();
    const imagesJson = images ? JSON.stringify(images) : '[]';
    db.run(`
        INSERT INTO products (
            id, agent_id, name, description, price_kes, stock, image_url,
            images, category, discount_percent, discount_start, discount_end, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [
        id, agent_id, name, description, price_kes, stock, image_url || '',
        imagesJson, category || '',
        discount_percent || 0,
        discount_start || null,
        discount_end || null,
        Date.now()
    ], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ success: true, id });
    });
});

app.put('/api/products/:id/stock', (req, res) => {
    db.run('UPDATE products SET stock = ? WHERE id = ?', [req.body.stock, req.params.id], (err) =>
        err ? res.status(500).json({ error: err.message }) : res.json({ updated: true })
    );
});

app.delete('/api/products/:id', (req, res) => {
    db.run('DELETE FROM products WHERE id = ?', [req.params.id], (err) =>
        err ? res.status(500).json({ error: err.message }) : res.json({ deleted: true })
    );
});

app.post('/api/products/:id/promotion', (req, res) => {
    const { is_featured, discount_percent, discount_start, discount_end } = req.body;
    db.run(
        `UPDATE products SET is_featured = ?, discount_percent = ?, discount_start = ?, discount_end = ? WHERE id = ?`,
        [is_featured || 0, discount_percent || 0, discount_start || null, discount_end || null, req.params.id],
        (err) => err ? res.status(500).json({ error: err.message }) : res.json({ updated: true })
    );
});

app.post('/api/products/:id/notify-restock', (req, res) => {
    const productId = req.params.id;
    db.get(`
        SELECT p.*, a.name as agent_name, a.phone as agent_phone
        FROM products p LEFT JOIN agents a ON p.agent_id = a.id WHERE p.id = ?
    `, [productId], (err, product) => {
        if (err || !product) return res.status(404).json({ error: 'Product not found' });
        console.log(`📧 NOTIFICATION to ${product.agent_name} (${product.agent_phone}):`);
        console.log(`   Product "${product.name}" is ${product.stock <= 0 ? 'OUT OF STOCK' : 'LOW ON STOCK'} (${product.stock} left).`);
        res.json({ success: true, message: `Notification sent to ${product.agent_name}` });
    });
});

// ─── Orders ──────────────────────────────────────────────────────
app.get('/api/agent/:agentId/orders', (req, res) => {
    db.all(`
        SELECT o.*, p.name as product_name FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE p.agent_id = ? ORDER BY o.created_at DESC
    `, [req.params.agentId], (err, rows) => res.json(rows || []));
});

app.get('/api/agent/:agentId/pending-orders', (req, res) => {
    db.all(`
        SELECT o.*, p.name as product_name FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE p.agent_id = ? AND o.status = 'pending'
    `, [req.params.agentId], (err, rows) => res.json(rows || []));
});

app.post('/api/orders/dispatch', (req, res) => {
    db.run('UPDATE orders SET status = ? WHERE id = ?', ['dispatched', req.body.orderId], () => res.json({ success: true }));
});

// ─── NEW: Get rider orders ──────────────────────────────────────────
app.get('/api/rider/:riderId/orders', (req, res) => {
    db.all(
        `SELECT o.*, p.name as product_name 
         FROM orders o
         LEFT JOIN products p ON o.product_id = p.id
         WHERE o.rider_id = ? 
         ORDER BY o.created_at DESC`,
        [req.params.riderId],
        (err, rows) => {
            if (err) return res.status(500).json({ error: err.message });
            res.json(rows || []);
        }
    );
});

// ─── RIDER MANAGEMENT ──────────────────────────────────────────────

// ─── GET /api/riders ──────────────────────────────────────────────
app.get('/api/riders', (req, res) => {
    db.all(
        `SELECT * FROM riders ORDER BY created_at DESC`,
        (err, rows) => {
            if (err) return res.status(500).json({ error: err.message });
            res.json(rows || []);
        }
    );
});

// ─── POST /api/riders ─────────────────────────────────────────────
app.post('/api/riders', (req, res) => {
    const { name, phone, password, idNumber, vehicleType, vehiclePlate, status } = req.body;
    if (!name || !phone || !password) {
        return res.status(400).json({ error: 'Name, phone, and password are required' });
    }

    // Check if phone already exists
    db.get('SELECT id FROM riders WHERE phone = ?', [phone], (err, existing) => {
        if (err) return res.status(500).json({ error: err.message });
        if (existing) return res.status(409).json({ error: 'Phone already registered' });

        const id = 'rider_' + Date.now();
        const passwordHash = hashPassword(password);
        const riderStatus = status || 'pending';

        db.run(
            `INSERT INTO riders (
                id, name, phone, password_hash, id_number, vehicle_type, vehicle_plate, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)`,
            [id, name, phone, passwordHash, idNumber || null, vehicleType || null, vehiclePlate || null, riderStatus],
            function(err) {
                if (err) return res.status(500).json({ error: err.message });
                res.status(201).json({ success: true, riderId: id });
            }
        );
    });
});

// ─── PUT /api/riders/:id/verify ──────────────────────────────────
app.put('/api/riders/:id/verify', (req, res) => {
    const riderId = req.params.id;
    db.run(
        `UPDATE riders SET status = 'verified', updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
        [riderId],
        function(err) {
            if (err) return res.status(500).json({ error: err.message });
            if (this.changes === 0) return res.status(404).json({ error: 'Rider not found' });
            res.json({ success: true });
        }
    );
});

// ─── PUT /api/riders/:id/suspend ──────────────────────────────────
app.put('/api/riders/:id/suspend', (req, res) => {
    const riderId = req.params.id;
    db.run(
        `UPDATE riders SET status = 'suspended', updated_at = CURRENT_TIMESTAMP WHERE id = ?`,
        [riderId],
        function(err) {
            if (err) return res.status(500).json({ error: err.message });
            if (this.changes === 0) return res.status(404).json({ error: 'Rider not found' });
            res.json({ success: true });
        }
    );
});

// ─── DELETE /api/riders/:id ──────────────────────────────────────
app.delete('/api/riders/:id', (req, res) => {
    const riderId = req.params.id;
    db.run(
        `DELETE FROM riders WHERE id = ?`,
        [riderId],
        function(err) {
            if (err) return res.status(500).json({ error: err.message });
            if (this.changes === 0) return res.status(404).json({ error: 'Rider not found' });
            res.json({ deleted: true });
        }
    );
});

// ─── POST /api/riders/rate ──────────────────────────────────────
app.post('/api/riders/rate', (req, res) => {
    const { rider_id, rating, comment } = req.body;
    if (!rider_id || !rating) return res.status(400).json({ error: 'Rider ID and rating are required' });
    if (rating < 1 || rating > 5) return res.status(400).json({ error: 'Rating must be between 1 and 5' });

    db.run(
        `INSERT INTO rider_ratings (rider_id, rating, comment) VALUES (?, ?, ?)`,
        [rider_id, rating, comment || null],
        function(err) {
            if (err) return res.status(500).json({ error: err.message });
            // Update rider's average rating
            db.get(
                `SELECT AVG(rating) as avg_rating FROM rider_ratings WHERE rider_id = ?`,
                [rider_id],
                (err2, row) => {
                    if (err2) return res.status(500).json({ error: err2.message });
                    const avg = row ? Math.round(row.avg_rating * 10) / 10 : 5.0;
                    db.run(
                        `UPDATE riders SET rating = ? WHERE id = ?`,
                        [avg, rider_id],
                        (err3) => {
                            if (err3) console.error('Failed to update rider rating:', err3);
                        }
                    );
                }
            );
            res.json({ success: true, ratingId: this.lastID });
        }
    );
});

// ─── GET /api/deliveries ──────────────────────────────────────────
app.get('/api/deliveries', (req, res) => {
    db.all(`
        SELECT 
            o.id,
            o.id as order_id,
            o.agent_id,
            o.rider_id,
            o.buyer_name,
            o.delivery_address,
            o.status,
            o.total_amount as total_fee,
            o.delivery_fee,
            a.name as agent_name,
            r.name as rider_name,
            r.rating as rider_rating,
            o.created_at,
            o.updated_at
        FROM orders o
        LEFT JOIN agents a ON o.agent_id = a.id
        LEFT JOIN riders r ON o.rider_id = r.id
        ORDER BY o.created_at DESC
    `, (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        // Format response to match admin frontend expectations
        const deliveries = rows.map(row => ({
            id: row.id,
            order_id: row.order_id,
            agent_name: row.agent_name || 'N/A',
            rider_name: row.rider_name || 'Not assigned',
            pickup_address: 'N/A', // not stored, can be added later
            delivery_address: row.delivery_address || 'N/A',
            status: row.status || 'pending',
            total_fee: row.total_fee || 0,
            rider_rating: row.rider_rating || 'Not rated',
            created_at: row.created_at
        }));
        res.json(deliveries);
    });
});

// ─── GET /api/ratings ──────────────────────────────────────────────
app.get('/api/ratings', (req, res) => {
    db.all(`
        SELECT 
            rr.id,
            rr.rider_id,
            rr.rating,
            rr.comment,
            rr.created_at,
            r.name as rider_name
        FROM rider_ratings rr
        LEFT JOIN riders r ON rr.rider_id = r.id
        ORDER BY rr.created_at DESC
    `, (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows || []);
    });
});

// ─── Ads ──────────────────────────────────────────────────────────
app.get('/api/agent/:agentId/ads', (req, res) => {
    db.all('SELECT * FROM ads WHERE agent_id = ? ORDER BY created_at DESC', [req.params.agentId], (err, rows) => res.json(rows || []));
});

app.post('/api/ads', (req, res) => {
    const { agent_id, title, content, image_url } = req.body;
    const id = Date.now().toString();
    db.run(`INSERT INTO ads (id, agent_id, title, content, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?)`,
        [id, agent_id, title, content, image_url, Date.now()], (err) =>
            err ? res.status(500).json({ error: err.message }) : res.json({ success: true })
    );
});

app.delete('/api/ads/:id', (req, res) => {
    db.run('DELETE FROM ads WHERE id = ?', [req.params.id], () => res.json({ deleted: true }));
});

// ─── DYNAMIC BUYER PAGE WITH OPEN GRAPH META TAGS ──────────────
app.get('/buyer.html', (req, res) => {
    const productId = req.query.product;
    const platform = req.query.platform || 'social';
    const ref = req.query.ref || '';
    const campaign = req.query.campaign || '';

    let title = 'SYNARA - Everything in Sync';
    let description = 'Shop the best products in Kenya. Free delivery on first order.';
    let imageUrl = 'http://localhost:3000/images/synara-logo.png';
    let productName = 'Product';
    let productPrice = '0';
    let productDesc = '';

    if (productId) {
        db.get('SELECT * FROM products WHERE id = ?', [productId], (err, product) => {
            if (err || !product) {
                sendBuyerPage(res, title, description, imageUrl, productName, productPrice, productDesc, platform, ref, campaign, null);
            } else {
                let images = [];
                try { images = JSON.parse(product.images); } catch { images = []; }
                const firstImage = images.length > 0 ? images[0] : (product.image_url || '');
                if (firstImage && !firstImage.startsWith('http')) {
                    imageUrl = `http://localhost:3000${firstImage}`;
                } else if (firstImage) {
                    imageUrl = firstImage;
                } else {
                    imageUrl = 'http://localhost:3000/images/synara-logo.png';
                }
                title = `${product.name} - SYNARA`;
                description = product.description || 'Check out this amazing product on SYNARA!';
                productName = product.name;
                productPrice = `KES ${product.price_kes.toLocaleString()}`;
                productDesc = product.description || '';
                sendBuyerPage(res, title, description, imageUrl, productName, productPrice, productDesc, platform, ref, campaign, product);
            }
        });
    } else {
        sendBuyerPage(res, title, description, imageUrl, productName, productPrice, productDesc, platform, ref, campaign, null);
    }
});

function sendBuyerPage(res, title, description, imageUrl, productName, productPrice, productDesc, platform, ref, campaign, product) {
    const filePath = path.join(__dirname, 'buyer.html');
    fs.readFile(filePath, 'utf8', (err, data) => {
        if (err) {
            res.status(500).send('Error loading buyer page');
            return;
        }
        const productId = product ? product.id : '';
        const metaTags = `
            <meta property="og:title" content="${title}" />
            <meta property="og:description" content="${description}" />
            <meta property="og:image" content="${imageUrl}" />
            <meta property="og:url" content="http://localhost:3000/buyer.html?product=${productId}&platform=${platform}&ref=${ref}&campaign=${campaign}" />
            <meta property="og:type" content="product" />
            <meta property="og:site_name" content="SYNARA" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="${title}" />
            <meta name="twitter:description" content="${description}" />
            <meta name="twitter:image" content="${imageUrl}" />
        `;
        let modifiedHtml = data.replace('<!-- META_TAGS -->', metaTags);

        const productData = product ? JSON.stringify({
            id: product.id,
            name: product.name,
            price: product.price_kes,
            description: product.description,
            images: product.images ? JSON.parse(product.images) : [],
            platform: platform,
            ref: ref,
            campaign: campaign
        }) : 'null';
        const script = `<script>window.__AD_PRODUCT = ${productData};</script>`;
        modifiedHtml = modifiedHtml.replace('<!-- AD_PRODUCT_DATA -->', script);

        res.send(modifiedHtml);
    });
}

// ─── Frontend Entry ─────────────────────────────────────────────
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'buyer.html'));
});

// ================================================================
//  M-PESA INTEGRATION
// ================================================================

// ─── 1. Add missing columns to orders table (migration) ──────────
db.all("PRAGMA table_info(orders)", (err, columns) => {
    if (err) return console.error('Error checking orders table:', err);
    const columnNames = columns.map(c => c.name);
    const addColumn = (col, type) => {
        if (!columnNames.includes(col)) {
            db.run(`ALTER TABLE orders ADD COLUMN ${col} ${type}`, (e) => {
                if (e) console.error(`Error adding ${col}:`, e);
                else console.log(`✅ Added column ${col} to orders`);
            });
        }
    };
    addColumn('checkout_request_id', 'TEXT');
    addColumn('mpesa_receipt', 'TEXT');
    addColumn('payment_status', 'TEXT DEFAULT "pending"');
    addColumn('payment_amount', 'REAL');
    addColumn('payment_phone', 'TEXT');
    addColumn('payment_date', 'DATETIME');
});

// ─── 2. Payment status endpoint ───────────────────────────────────
app.get('/api/order/:id/payment', (req, res) => {
    db.get(
        `SELECT id, status, payment_status, mpesa_receipt, payment_amount, payment_phone, payment_date
         FROM orders WHERE id = ?`,
        [req.params.id],
        (err, row) => {
            if (err) return res.status(500).json({ success: false, error: 'Database error' });
            if (!row) return res.status(404).json({ success: false, error: 'Order not found' });
            res.json({
                success: true,
                order: {
                    id: row.id,
                    status: row.status,
                    paymentStatus: row.payment_status,
                    receipt: row.mpesa_receipt,
                    amount: row.payment_amount,
                    phone: row.payment_phone,
                    paymentDate: row.payment_date
                }
            });
        }
    );
});

// ─── 3. Mount M-Pesa routes ──────────────────────────────────────
const mpesaRoutes = require('./routes/mpesa');
app.use('/api/mpesa', mpesaRoutes);

// ─── 4. Agent & Rider Earnings Endpoints ─────────────────────────
app.get('/api/agent/:agentId/earnings', (req, res) => {
    const agentId = req.params.agentId;
    db.get(
        `SELECT 
            SUM(amount) as total_sales,
            SUM(facilitation_fee) as total_fees,
            SUM(net_amount) as total_earned,
            SUM(CASE WHEN settled = 0 THEN net_amount ELSE 0 END) as pending_amount
         FROM agent_transactions 
         WHERE agent_id = ?`,
        [agentId],
        (err, summary) => {
            if (err) return res.status(500).json({ error: err.message });

            db.all(
                `SELECT * FROM agent_transactions 
                 WHERE agent_id = ? 
                 ORDER BY transaction_date DESC 
                 LIMIT 50`,
                [agentId],
                (err2, transactions) => {
                    if (err2) return res.status(500).json({ error: err2.message });
                    res.json({
                        summary: {
                            totalSales: summary?.total_sales || 0,
                            totalFees: summary?.total_fees || 0,
                            totalEarned: summary?.total_earned || 0,
                            pendingAmount: summary?.pending_amount || 0
                        },
                        transactions: transactions || []
                    });
                }
            );
        }
    );
});

app.get('/api/rider/:riderId/earnings', (req, res) => {
    const riderId = req.params.riderId;
    db.get(
        `SELECT 
            SUM(amount) as total_delivery_fees,
            SUM(facilitation_fee) as total_fees,
            SUM(net_amount) as total_earned,
            SUM(CASE WHEN settled = 0 THEN net_amount ELSE 0 END) as pending_amount
         FROM rider_transactions 
         WHERE rider_id = ?`,
        [riderId],
        (err, summary) => {
            if (err) return res.status(500).json({ error: err.message });

            db.all(
                `SELECT * FROM rider_transactions 
                 WHERE rider_id = ? 
                 ORDER BY transaction_date DESC 
                 LIMIT 50`,
                [riderId],
                (err2, transactions) => {
                    if (err2) return res.status(500).json({ error: err2.message });
                    res.json({
                        summary: {
                            totalDeliveryFees: summary?.total_delivery_fees || 0,
                            totalFees: summary?.total_fees || 0,
                            totalEarned: summary?.total_earned || 0,
                            pendingAmount: summary?.pending_amount || 0
                        },
                        transactions: transactions || []
                    });
                }
            );
        }
    );
});

// ─── 4.2 & 4.3 – Payout Endpoints (Admin Only) ────────────────────

// ─── Get pending payouts list ────────────────────────────────────
app.get('/api/admin/pending-payouts', (req, res) => {
    const role = req.query.role; // 'agent' or 'rider'
    if (role === 'agent') {
        db.all(
            `SELECT a.id, a.name, a.phone, SUM(at.net_amount) as total_pending
             FROM agent_transactions at
             JOIN agents a ON at.agent_id = a.id
             WHERE at.settled = 0
             GROUP BY a.id
             HAVING total_pending >= 100`,
            (err, rows) => res.json(rows || [])
        );
    } else if (role === 'rider') {
        db.all(
            `SELECT r.id, r.name, r.phone, SUM(rt.net_amount) as total_pending
             FROM rider_transactions rt
             JOIN riders r ON rt.rider_id = r.id
             WHERE rt.settled = 0
             GROUP BY r.id
             HAVING total_pending >= 100`,
            (err, rows) => res.json(rows || [])
        );
    } else {
        res.status(400).json({ error: 'Invalid role' });
    }
});

// ─── Payout a single agent ────────────────────────────────────────
app.post('/api/admin/payout-agent', async (req, res) => {
    const { agentId, amount } = req.body;
    if (!agentId || amount < 100) return res.status(400).json({ error: 'Amount too low' });

    db.get('SELECT name, phone FROM agents WHERE id = ?', [agentId], async (err, agent) => {
        if (err || !agent) return res.status(404).json({ error: 'Agent not found' });

        try {
            const result = await b2cPayment(agent.phone, amount, 'BusinessPayment', 'Agent earnings payout');
            db.run(
                `UPDATE agent_transactions SET settled = 1, settled_date = CURRENT_TIMESTAMP WHERE agent_id = ? AND settled = 0`,
                [agentId],
                (err2) => {
                    if (err2) return res.status(500).json({ error: err2.message });
                    db.run(
                        `INSERT INTO payout_history (recipient_id, recipient_type, recipient_name, recipient_phone, amount, mpesa_conversation_id, status, completed_at)
                         VALUES (?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP)`,
                        [agentId, 'agent', agent.name, agent.phone, amount, result.conversationId],
                        (err3) => {
                            if (err3) return res.status(500).json({ error: err3.message });
                            res.json({ success: true, message: `KES ${amount} sent to ${agent.phone}` });
                        }
                    );
                }
            );
        } catch (error) {
            console.error('B2C error:', error);
            res.status(500).json({ success: false, error: error.message });
        }
    });
});

// ─── Payout a single rider ────────────────────────────────────────
app.post('/api/admin/payout-rider', async (req, res) => {
    const { riderId, amount } = req.body;
    if (!riderId || amount < 100) return res.status(400).json({ error: 'Amount too low' });

    db.get('SELECT name, phone FROM riders WHERE id = ?', [riderId], async (err, rider) => {
        if (err || !rider) return res.status(404).json({ error: 'Rider not found' });

        try {
            const result = await b2cPayment(rider.phone, amount, 'BusinessPayment', 'Rider earnings payout');
            db.run(
                `UPDATE rider_transactions SET settled = 1, settled_date = CURRENT_TIMESTAMP WHERE rider_id = ? AND settled = 0`,
                [riderId],
                (err2) => {
                    if (err2) return res.status(500).json({ error: err2.message });
                    db.run(
                        `INSERT INTO payout_history (recipient_id, recipient_type, recipient_name, recipient_phone, amount, mpesa_conversation_id, status, completed_at)
                         VALUES (?, ?, ?, ?, ?, ?, 'completed', CURRENT_TIMESTAMP)`,
                        [riderId, 'rider', rider.name, rider.phone, amount, result.conversationId],
                        (err3) => {
                            if (err3) return res.status(500).json({ error: err3.message });
                            res.json({ success: true, message: `KES ${amount} sent to ${rider.phone}` });
                        }
                    );
                }
            );
        } catch (error) {
            console.error('B2C error:', error);
            res.status(500).json({ success: false, error: error.message });
        }
    });
});

// ─── 4.3 – Manual Trigger for Weekly Payout ──────────────────────
app.post('/api/admin/run-weekly-payout', async (req, res) => {
    try {
        const result = await runWeeklyPayout();
        res.json({ success: true, result });
    } catch (err) {
        console.error('Weekly payout error:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});

// ─── 8.4 – Payout History ─────────────────────────────────────────
app.get('/api/admin/payout-history', (req, res) => {
    db.all(
        `SELECT * FROM payout_history ORDER BY created_at DESC LIMIT 100`,
        (err, rows) => res.json(rows || [])
    );
});

// ─── 5.2 – Automated Weekly Payout Scheduler ──────────────────────
// Schedule every Friday at 3 PM
cron.schedule('0 15 * * 5', async () => {
    console.log('⏰ Weekly payout scheduled job started.');
    try {
        await runWeeklyPayout();
    } catch (err) {
        console.error('Weekly payout failed:', err);
    }
});

// ─── Start Server ────────────────────────────────────────────────
app.listen(PORT, () => {
    console.log('✅ SYNARA running on port ' + PORT);
    console.log('🔐 Agent login ready');
    console.log('📱 M-Pesa endpoints available at /api/mpesa');
});