<?php
/**
 * SecLab - Vulnerable Login Portal
 * Focus: SQL Injection (Authentication Bypass)
 */

session_start();

// MySQL Configuration
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'seclab_db';

$mysqli = @new mysqli($db_host, $db_user, $db_pass);
$error_msg = '';
$executed_sql = '';

// Security mode
if (!isset($_SESSION['sec_mode'])) {
    $_SESSION['sec_mode'] = 'vulnerable';
}
if (isset($_GET['toggle_mode'])) {
    $_SESSION['sec_mode'] = ($_SESSION['sec_mode'] === 'vulnerable') ? 'secure' : 'vulnerable';
    header("Location: login.php");
    exit;
}

$mode = $_SESSION['sec_mode'];

// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($mysqli && !$mysqli->connect_error) {
        $mysqli->select_db($db_name);

        if ($mode === 'vulnerable') {
            // VULNERABLE: Direct string concatenation in Auth query
            $executed_sql = "SELECT id, fullname, username, role, api_secret_key FROM users WHERE username = '$username' AND password = '$password'";
            try {
                $res = $mysqli->query($executed_sql);
                if ($res && $res->num_rows > 0) {
                    $userData = $res->fetch_assoc();
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['fullname'] = $userData['fullname'];
                    $_SESSION['username'] = $userData['username'];
                    $_SESSION['role'] = $userData['role'];
                    $_SESSION['flag'] = $userData['api_secret_key'];
                    header("Location: index.php");
                    exit;
                } else {
                    $error_msg = "Kombinasi username atau password salah!";
                }
            } catch (Exception $e) {
                $error_msg = "MySQL Error: " . $e->getMessage();
            }
        } else {
            // SECURE: Parameterized Prepared Statement
            $executed_sql = "SELECT id, fullname, username, role, api_secret_key FROM users WHERE username = ? AND password = ?";
            $stmt = $mysqli->prepare($executed_sql);
            if ($stmt) {
                $stmt->bind_param("ss", $username, $password);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                    $userData = $res->fetch_assoc();
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['fullname'] = $userData['fullname'];
                    $_SESSION['username'] = $userData['username'];
                    $_SESSION['role'] = $userData['role'];
                    $_SESSION['flag'] = $userData['api_secret_key'];
                    header("Location: index.php");
                    exit;
                } else {
                    $error_msg = "Kombinasi username atau password salah!";
                }
                $stmt->close();
            }
        }
    } else {
        $error_msg = "Gagal terhubung ke MySQL. Pastikan MySQL di XAMPP aktif.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SecLab Threat Portal</title>
    <style>
        :root {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-hover: #21262d;
            --border: #30363d;
            --text: #c9d1d9;
            --text-bright: #f0f6fc;
            --accent: #58a6ff;
            --danger: #f85149;
            --success: #3fb950;
            --code-bg: #090d13;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; }
        
        .login-card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; width: 100%; max-width: 440px; padding: 30px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); }
        .login-header { text-align: center; margin-bottom: 24px; }
        .login-header h1 { color: var(--accent); font-size: 1.5rem; margin-bottom: 6px; }
        .login-header p { font-size: 0.85rem; color: #8b949e; }
        
        .badge { font-size: 0.75rem; padding: 3px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-danger { background: rgba(248, 81, 73, 0.2); color: var(--danger); border: 1px solid var(--danger); }
        .badge-success { background: rgba(63, 185, 80, 0.2); color: var(--success); border: 1px solid var(--success); }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-bright); font-weight: 500; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 14px; background: var(--code-bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text-bright); font-size: 0.95rem; outline: none;
        }
        input:focus { border-color: var(--accent); }
        
        .btn-submit { width: 100%; background: #238636; color: white; border: 1px solid rgba(240,246,252,0.1); padding: 10px; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #2ea043; }

        .alert-danger { background: rgba(248,81,73,0.15); border: 1px solid var(--danger); color: var(--danger); padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 0.85rem; }
        
        .mode-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.8rem; background: var(--code-bg); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); }
        .mode-bar a { color: var(--accent); text-decoration: none; font-weight: 600; }
        
        details { background: var(--code-bg); border: 1px solid var(--border); border-radius: 6px; padding: 10px 14px; margin-top: 20px; font-size: 0.85rem; }
        summary { cursor: pointer; font-weight: 600; color: var(--accent); }
        code { color: #ff7b72; font-family: monospace; background: rgba(110,118,129,0.2); padding: 2px 4px; border-radius: 4px; }
        pre { background: #000; padding: 10px; border-radius: 4px; overflow-x: auto; margin-top: 8px; color: #79c0ff; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>[SecLab] Enterprise Login</h1>
        <p>Autentikasi Pegawai & Portal Lab Keamanan</p>
    </div>

    <div class="mode-bar">
        <span>Mode: <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= strtoupper($mode) ?></span></span>
        <a href="login.php?toggle_mode=1">Switch ke <?= $mode === 'vulnerable' ? 'Secure' : 'Vulnerable' ?></a>
    </div>

    <?php if ($error_msg): ?>
        <div class="alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Username (atau payload SQLi)" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password akun">
        </div>
        <button type="submit" name="login_btn" class="btn-submit">Login ke Sistem</button>
    </form>

    <?php if ($executed_sql): ?>
        <div style="margin-top:15px; font-size:0.8rem;">
            <strong>Debug SQL Query:</strong>
            <pre><?= htmlspecialchars($executed_sql) ?></pre>
        </div>
    <?php endif; ?>

    <details>
        <summary>Petunjuk Eksploitasi SQLi Auth Bypass</summary>
        <div style="margin-top:8px; line-height: 1.6;">
            <p><strong>Target Mahasiswa:</strong> Login sebagai Super Administrator tanpa mengetahui password!</p>
            <p><strong>Contoh Payload Form Login:</strong></p>
            <ul>
                <li>Username: <code>admin' #</code> atau <code>superadmin' #</code></li>
                <li>Password: <em>(kosongkan atau isi sembarang)</em></li>
            </ul>
            <p style="margin-top:6px;"><strong>Payload Universal (Any User):</strong></p>
            <ul>
                <li>Username: <code>' OR 1=1 LIMIT 1 #</code></li>
                <li>Password: <em>(bebas)</em></li>
            </ul>
        </div>
    </details>
</div>

</body>
</html>
