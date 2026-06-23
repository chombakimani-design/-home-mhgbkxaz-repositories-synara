<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple SQLite database
$db = new SQLite3('/home/mhgbkxaz/nodeapp/dropship.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $phone = $input['phone'] ?? '';
    $password = $input['password'] ?? '';
    
    // Simple password check (for testing only)
    $hash = hash('sha256', $password);
    
    $stmt = $db->prepare("SELECT au.*, a.name FROM agent_users au JOIN agents a ON au.agent_id = a.id WHERE au.phone = :phone AND au.password_hash = :hash");
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user) {
        // Create session
        $sessionId = bin2hex(random_bytes(32));
        $expires = time() + (7 * 24 * 60 * 60);
        $stmt2 = $db->prepare("INSERT INTO sessions (id, user_id, role, expires_at) VALUES (:id, :user_id, 'agent', :expires)");
        $stmt2->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt2->bindValue(':user_id', $user['agent_id'], SQLITE3_TEXT);
        $stmt2->bindValue(':expires', $expires, SQLITE3_INTEGER);
        $stmt2->execute();
        
        echo json_encode(['success' => true, 'sessionId' => $sessionId, 'role' => 'agent', 'name' => $user['name']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid phone or password']);
    }
    exit();
}

// Simple test endpoint
echo json_encode(['message' => 'Agent API is working', 'time' => time()]);
