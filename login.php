<?php
session_start();
$config = require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? $_POST['password'] ?? '';

    if ($password === $config['admin_password']) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        echo json_encode(['success' => true]);
        exit;
    } else {
        sleep(2);
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'error' => 'Неверный пароль']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Вход в админ-панель</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --primary: var(--tg-theme-button-color, #248bed);
            --bg: var(--tg-theme-bg-color, #f5f5f5);
            --sec-bg: var(--tg-theme-secondary-bg-color, #fff);
            --text: var(--tg-theme-text-color, #000);
            --hint: var(--tg-theme-hint-color, #999);
        }

        body { 
            background: var(--bg); 
            color: var(--text);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .login-container {
            background: var(--sec-bg);
            padding: 30px 20px; 
            border-radius: 20px; 
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            width: 85%; 
            max-width: 320px;
            border: 1px solid rgba(127,127,127,0.1);
        }

        h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 800;
        }

        input {
            width: 100%; 
            padding: 14px; 
            margin: 10px 0; 
            border-radius: 12px;
            border: 1px solid rgba(127,127,127,0.2);
            background: var(--bg); 
            color: var(--text);
            box-sizing: border-box; 
            font-size: 16px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: var(--primary);
        }

        button#loginBtn {
            width: 100%; 
            padding: 14px; 
            border: none; 
            border-radius: 12px;
            background: var(--primary);
            color: var(--tg-theme-button-text-color, #fff);
            font-weight: 700; 
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: opacity 0.2s;
        }

        button:disabled { opacity: 0.6; }
        
        #status { 
            font-size: 14px; 
            margin-top: 15px; 
            min-height: 20px;
        }

        .floating-back {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: var(--sec-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            color: var(--text);
            border: 1px solid rgba(127,127,127,0.15);
            z-index: 100;
            text-decoration: none;
        }

        .floating-back svg { 
            width: 24px; 
            height: 24px; 
        }

        .floating-back:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 id="admin-title">Админ-панель</h3>
        <input type="password" id="password" placeholder="Введите пароль" autofocus>
        <button id="loginBtn" onclick="tryLogin()">Войти</button>
        <p id="status"></p>
    </div>

    <a href="index.php" class="floating-back" id="back-to-main">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
    </a>

    <script>
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();

        function tryLogin() {
            const pass = document.getElementById('password').value;
            const btn = document.getElementById('loginBtn');
            const status = document.getElementById('status');

            if (!pass) return;

            btn.disabled = true;
            status.innerText = 'Проверка...';
            status.style.color = 'var(--hint)';

            fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: pass })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
                    window.location.href = 'admin5577.php';
                } else {
                    status.innerText = 'Неверный пароль';
                    status.style.color = '#ff4d4d';
                    btn.disabled = false;
                    if (tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('error');
                }
            })
            .catch(err => {
                status.innerText = 'Ошибка сети';
                status.style.color = '#ff4d4d';
                btn.disabled = false;
            });
        }
        document.getElementById('password').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                tryLogin();
            }
        });
    </script>
</body>
</html>