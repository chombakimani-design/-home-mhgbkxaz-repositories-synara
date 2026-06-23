<!DOCTYPE html>
<html>
<head>
    <title>Agent Login - SYNARA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #10b981, #059669);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 32px;
            padding: 48px 32px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        h1 { font-size: 28px; margin-bottom: 8px; color: #1e293b; }
        .subtitle { color: #64748b; margin-bottom: 32px; }
        input {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .error { color: #ef4444; margin-top: 16px; }
    </style>
</head>
<body>
<div class="login-container">
    <h1>🏪 Agent Login</h1>
    <div class="subtitle">Access your shop dashboard</div>
    <form id="loginForm">
        <input type="tel" id="phone" placeholder="Phone number" required>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div id="errorMsg" class="error"></div>
</div>
<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const phone = document.getElementById('phone').value;
        const password = document.getElementById('password').value;
        
        const res = await fetch('/agent-api.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone, password })
        });
        const data = await res.json();
        
        if (data.success) {
            localStorage.setItem('synara_session', data.sessionId);
            window.location.href = '/agent.html';
        } else {
            document.getElementById('errorMsg').innerText = data.error || 'Login failed';
        }
    });
</script>
</body>
</html>
