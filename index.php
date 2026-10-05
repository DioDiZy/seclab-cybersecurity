<?php
/**
 * SecLab - Interactive Web Security Training Platform (DVWA-Lite)
 * MySQL Edition: Directly connected to `seclab_db` for Real-World Data Breach Demonstrations
 */

session_start();

// Authentication Gatekeeper
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// MySQL Configuration for XAMPP
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'seclab_db';
$upload_dir = __DIR__ . '/uploads/';

if (!file_exists($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Database Connection
$mysqli = @new mysqli($db_host, $db_user, $db_pass);
$db_error = null;

if ($mysqli->connect_error) {
    $db_error = "Koneksi MySQL Gagal: " . $mysqli->connect_error . ". Pastikan modul MySQL di XAMPP sudah berstatus 'Running'.";
} else {
    // Auto-create database & select
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$mysqli->select_db($db_name)) {
        $db_error = "Gagal memilih database $db_name: " . $mysqli->error;
    } else {
        // Auto-seed table if not exist
        initMySQLDatabase($mysqli);
    }
}

function initMySQLDatabase($conn) {
    // Check if table users has outdated schema (missing fullname)
    $colCheck = $conn->query("SHOW COLUMNS FROM `users` LIKE 'fullname'");
    if ($colCheck && $colCheck->num_rows == 0) {
        $conn->query("DROP TABLE IF EXISTS `users`");
        $conn->query("DROP TABLE IF EXISTS `comments`");
        $conn->query("DROP TABLE IF EXISTS `products`");
    }

    // 1. Users Table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `fullname` VARCHAR(150) NOT NULL,
            `username` VARCHAR(80) NOT NULL UNIQUE,
            `email` VARCHAR(120) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL,
            `nik_ktp` VARCHAR(20) NOT NULL,
            `credit_card` VARCHAR(25) NOT NULL,
            `salary` BIGINT(20) NOT NULL,
            `api_secret_key` VARCHAR(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Comments Table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `author` VARCHAR(100) NOT NULL,
            `email` VARCHAR(120) NOT NULL,
            `message` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Products Table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `sku` VARCHAR(30) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `price` BIGINT(20) NOT NULL,
            `stock` INT(11) NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed users if empty
    $check = $conn->query("SELECT COUNT(*) as total FROM `users`");
    if ($check) {
        $row = $check->fetch_assoc();
        if ($row['total'] == 0) {
            $conn->query("INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `role`, `nik_ktp`, `credit_card`, `salary`, `api_secret_key`) VALUES
                (1, 'Prof. Dr. Irwan Siregar, M.Kom', 'superadmin', 'irwan.siregar@enterprise-corp.id', 'AdminSecure#2026!$', 'Super Administrator', '3271041982050001', '4532-8921-3341-9012', 45000000, 'FLAG{sql1_b0c0r_d4t4_kr3d3ns14l_m4st3r}'),
                (2, 'Siti Nurhaliza, S.T (Lead DevOps)', 'siti_devops', 'siti.devops@enterprise-corp.id', 'DevOpsVault@Pass99', 'DevOps Engineer', '3271041991080004', '5412-7512-9901-2210', 28000000, 'FLAG{k3b0c0r4n_4p1_k3y_s3rv3r_pr0d}'),
                (3, 'Bambang Sudibyo (Direktur Keuangan)', 'bambang_finance', 'bambang.finance@enterprise-corp.id', 'DuitPerusahaan#2026', 'Finance Manager', '3174051978010003', '4000-1234-5678-9010', 38000000, 'FLAG{d4t4_g4j1_d4n_k4rtu_kr3d1t_l34k}'),
                (4, 'Dinda Larasati (HR Recruitment)', 'dinda_hr', 'dinda.hr@enterprise-corp.id', 'Recruit2026Secret', 'HR Specialist', '3201081995120002', '4111-2222-3333-4444', 15000000, 'FLAG{hr_p3rs0n4l_d4t4_3xp0s3d}'),
                (5, 'Ahmad Fauzi (Junior Programmer)', 'ahmad_dev', 'ahmad.fauzi@enterprise-corp.id', 'kopi_hitam123', 'Junior Developer', '3302061999030007', '5100-3344-5566-7788', 8500000, 'FLAG{jun10r_d3v_w34k_p4ssw0rd}');");

            $conn->query("INSERT INTO `comments` (`id`, `author`, `email`, `message`, `ip_address`, `created_at`) VALUES
                (1, 'Security Operation Center', 'soc@enterprise-corp.id', 'Perhatian: Sistem audit aktif 24/7. Segala aktivitas pengujian diawasi oleh tim SOC.', '192.168.1.10', NOW()),
                (2, 'Helpdesk Internal', 'helpdesk@enterprise-corp.id', 'Portal tiket bantuan aktif. Silakan lapor kendala teknis di sini.', '192.168.1.15', NOW());");

            $conn->query("INSERT INTO `products` (`id`, `sku`, `name`, `price`, `stock`, `category`) VALUES
                (1, 'PROD-SEC-001', 'Enterprise Hardware Security Token (FIDO2/WebAuthn)', 1250000, 45, 'Hardware Security'),
                (2, 'PROD-SEC-002', 'Next-Gen Firewall Appliance Rackmount 1U', 35000000, 12, 'Network Appliance'),
                (3, 'PROD-SRV-003', 'Dedicated Server Xeon Silver 64GB ECC RAM', 48000000, 8, 'Infrastructure'),
                (4, 'PROD-EDU-004', 'Buku Panduan Offensive Security & Certified PenTester', 275000, 150, 'Education');");
        }
    }
}

// Handle Reset Database
if (isset($_GET['action']) && $_GET['action'] === 'reset_db') {
    if (!$db_error && $mysqli) {
        $mysqli->query("DROP TABLE IF EXISTS `users`");
        $mysqli->query("DROP TABLE IF EXISTS `comments`");
        $mysqli->query("DROP TABLE IF EXISTS `products`");
        initMySQLDatabase($mysqli);
    }
    $files = glob($upload_dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) @unlink($file);
    }
    header("Location: index.php?msg=Database%20MySQL%20seclab_db%20dan%20folder%20uploads%20berhasil%20direset!");
    exit;
}

// Security Mode Toggle
if (!isset($_SESSION['sec_mode'])) {
    $_SESSION['sec_mode'] = 'vulnerable';
}
if (isset($_GET['toggle_mode'])) {
    $_SESSION['sec_mode'] = ($_SESSION['sec_mode'] === 'vulnerable') ? 'secure' : 'vulnerable';
    $curr_page = $_GET['page'] ?? 'home';
    header("Location: index.php?page=" . urlencode($curr_page));
    exit;
}

$page = $_GET['page'] ?? 'home';
$mode = $_SESSION['sec_mode'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecLab - Live MySQL Cyber Security Platform</title>
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
            --warning: #d29922;
            --code-bg: #090d13;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Top Navigation Header */
        header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.25rem; font-weight: 700; color: var(--accent); letter-spacing: 1px; display:flex; align-items:center; gap:8px; }
        .badge { font-size: 0.75rem; padding: 3px 8px; border-radius: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-danger { background: rgba(248, 81, 73, 0.2); color: var(--danger); border: 1px solid var(--danger); }
        .badge-success { background: rgba(63, 185, 80, 0.2); color: var(--success); border: 1px solid var(--success); }
        .badge-mysql { background: rgba(88, 166, 255, 0.2); color: var(--accent); border: 1px solid var(--accent); }
        
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .btn { padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid var(--border); color: var(--text-bright); background: var(--surface-hover); transition: 0.2s; }
        .btn:hover { background: #30363d; }
        .btn-mode { border-color: var(--accent); color: var(--accent); }
        .btn-danger { border-color: var(--danger); color: var(--danger); }
        
        /* Main Layout */
        .container { display: flex; flex: 1; }
        
        /* Sidebar */
        aside { width: 270px; background: var(--surface); border-right: 1px solid var(--border); padding: 20px 0; }
        .nav-category { font-size: 0.75rem; text-transform: uppercase; color: #8b949e; padding: 8px 20px; font-weight: 700; letter-spacing: 0.5px; }
        .nav-item { display: block; padding: 10px 20px; color: var(--text); text-decoration: none; font-size: 0.9rem; transition: 0.2s; border-left: 3px solid transparent; }
        .nav-item:hover { background: var(--surface-hover); color: var(--text-bright); }
        .nav-item.active { background: var(--surface-hover); color: var(--accent); border-left-color: var(--accent); font-weight: 600; }
        
        /* Content Area */
        main { flex: 1; padding: 30px; max-width: 1100px; }
        h1, h2, h3 { color: var(--text-bright); margin-bottom: 12px; }
        p { line-height: 1.6; margin-bottom: 15px; color: #8b949e; }
        
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 20px; margin-bottom: 24px; }
        .card-header { border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Forms & Inputs */
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-bright); font-weight: 500; }
        input[type="text"], input[type="password"], textarea, select {
            width: 100%; padding: 10px 14px; background: var(--code-bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text-bright); font-size: 0.9rem; outline: none;
        }
        input:focus, textarea:focus { border-color: var(--accent); }
        
        .btn-submit { background: #238636; color: white; border: 1px solid rgba(240,246,252,0.1); padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #2ea043; }

        /* Output Tables & Pre */
        .result-box { background: var(--code-bg); border: 1px solid var(--border); border-radius: 6px; padding: 14px; margin-top: 15px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85rem; }
        th, td { border: 1px solid var(--border); padding: 9px 12px; text-align: left; }
        th { background: var(--surface-hover); color: var(--text-bright); }
        
        details { background: var(--surface-hover); border: 1px solid var(--border); border-radius: 6px; padding: 12px; margin-top: 15px; }
        summary { cursor: pointer; font-weight: 600; color: var(--accent); outline: none; }
        pre { background: var(--code-bg); padding: 12px; border-radius: 4px; overflow-x: auto; margin-top: 10px; font-size: 0.85rem; color: #79c0ff; }
        code { font-family: monospace; background: rgba(110,118,129,0.2); padding: 2px 6px; border-radius: 4px; color: #ff7b72; }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-info { background: rgba(88,166,255,0.15); border: 1px solid var(--accent); color: var(--accent); }
        .alert-success { background: rgba(63,185,80,0.15); border: 1px solid var(--success); color: var(--success); }
        .alert-danger { background: rgba(248,81,73,0.15); border: 1px solid var(--danger); color: var(--danger); }
        
        .badge-leak { background: rgba(248, 81, 73, 0.3); color: #ff7b72; border: 1px solid #f85149; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <span>[SecLab]</span> Enterprise Threat Portal
    </div>
    <div class="header-actions">
        <span style="font-size:0.85rem; color:#f0f6fc;">
            User: <strong><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User') ?></strong> 
            (<span style="color:#79c0ff;"><?= htmlspecialchars($_SESSION['role'] ?? 'Guest') ?></span>)
        </span>
        <span class="badge badge-mysql">MySQL: seclab_db</span>
        <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= strtoupper($mode) ?></span>
        <a href="?page=<?= htmlspecialchars($page) ?>&toggle_mode=1" class="btn btn-mode">
            Switch ke <?= $mode === 'vulnerable' ? 'Secure' : 'Vulnerable' ?>
        </a>
        <a href="?action=reset_db" onclick="return confirm('Reset ulang isi database seclab_db dan file uploads?')" class="btn btn-danger">Reset DB</a>
        <a href="logout.php" class="btn" style="background:#da3633; color:#fff; border-color:#f85149;">Logout</a>
    </div>
</header>

<div class="container">
    <aside>
        <div class="nav-category">Overview & Audit</div>
        <a href="?page=home" class="nav-item <?= $page === 'home' ? 'active' : '' ?>">Home & Konsep</a>
        <a href="?page=db_inspector" class="nav-item <?= $page === 'db_inspector' ? 'active' : '' ?>" style="color:#79c0ff; font-weight:bold;">Live DB Inspector (phpMyAdmin)</a>
        
        <div class="nav-category" style="margin-top:15px;">Modul Serangan & Pertahanan</div>
        <a href="?page=sqli" class="nav-item <?= $page === 'sqli' ? 'active' : '' ?>">1. SQL Injection (Data Breach)</a>
        <a href="?page=xss_reflected" class="nav-item <?= $page === 'xss_reflected' ? 'active' : '' ?>">2. XSS Reflected</a>
        <a href="?page=xss_stored" class="nav-item <?= $page === 'xss_stored' ? 'active' : '' ?>">3. XSS Stored (Live MySQL)</a>
        <a href="?page=xss_dom" class="nav-item <?= $page === 'xss_dom' ? 'active' : '' ?>">4. XSS DOM-Based</a>
        <a href="?page=upload" class="nav-item <?= $page === 'upload' ? 'active' : '' ?>">5. File Upload (Web Shell / RCE)</a>
    </aside>

    <main>
        <?php if ($db_error): ?>
            <div class="alert alert-danger">
                <strong>Peringatan Database:</strong><br>
                <?= htmlspecialchars($db_error) ?><br><br>
                Silakan buka <strong>XAMPP Control Panel</strong> dan klik <strong>Start</strong> pada modul <strong>MySQL</strong>.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <?php
        // -------------------------------------------------------------
        // MODULE: HOME
        // -------------------------------------------------------------
        if ($page === 'home'): ?>
            <div class="card">
                <h2>SecLab - Real-World Data Breach Simulation</h2>
                <p>Laboratorium ini terhubung langsung ke database MySQL <strong><code>seclab_db</code></strong> di phpMyAdmin Anda. Data yang digunakan menyimulasikan data kredensial perusahaan nyata (NIK KTP, Gaji, Nomor Kartu Kredit, Password, dan API Secret Flag).</p>
                
                <div class="alert alert-info">
                    <strong>Status Sistem:</strong> Aplikasi terhubung aktif ke <strong>MySQL Server (localhost:3306 / seclab_db)</strong>.<br>
                    Anda dapat membuka <strong><a href="http://localhost/phpmyadmin" target="_blank" style="color:#58a6ff; text-decoration:underline;">http://localhost/phpmyadmin</a></strong> secara paralel untuk membandingkan data di phpMyAdmin dengan data yang diekstraksi melalui eksploitasi.
                </div>

                <h3>Tujuan Pembuktian Nyata:</h3>
                <ul style="margin-left: 20px; line-height: 1.8; color: var(--text);">
                    <li><strong>SQL Injection:</strong> Membuktikan bagaimana satu baris parameter tanpa sanitasi mampu membocorkan 100% data karyawan, NIK, kartu kredit, dan gaji dari database MySQL.</li>
                    <li><strong>Stored XSS:</strong> Membuktikan script malware tersimpan secara fisik di dalam tabel MySQL <code>comments</code> dan otomatis tereksekusi pada browser korban lain.</li>
                    <li><strong>Insecure File Upload:</strong> Membuktikan eksekusi Web Shell yang menembus direktori web server.</li>
                </ul>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE: LIVE DB INSPECTOR (PROOF OF DATA IN PHPMYADMIN)
        // -------------------------------------------------------------
        elseif ($page === 'db_inspector'):
            $users_res = $mysqli ? $mysqli->query("SELECT * FROM users") : null;
            $comments_res = $mysqli ? $mysqli->query("SELECT * FROM comments ORDER BY id DESC") : null;
            $products_res = $mysqli ? $mysqli->query("SELECT * FROM products") : null;
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Live Database Inspector (Tabel Fisik di MySQL seclab_db)</h2>
                    <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=seclab_db" target="_blank" class="btn btn-mode">Buka phpMyAdmin</a>
                </div>
                <p>Halaman ini menampilkan isi database <code>seclab_db</code> secara transparan untuk keperluan dosen/instruktur dalam membuktikan bahwa data kredensial benar-benar tersimpan di MySQL.</p>

                <h3>1. Tabel: <code>users</code> (Data Kredensial Sensitif)</h3>
                <div class="result-box">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th>Role</th>
                                <th>NIK / KTP</th>
                                <th>Kartu Kredit</th>
                                <th>Gaji (IDR)</th>
                                <th>API Secret Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users_res): while ($u = $users_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['fullname']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span style="color:#f85149; font-weight:bold;"><?= htmlspecialchars($u['password']) ?></span></td>
                                    <td><?= htmlspecialchars($u['role']) ?></td>
                                    <td><code><?= htmlspecialchars($u['nik_ktp']) ?></code></td>
                                    <td><span class="badge-leak"><?= htmlspecialchars($u['credit_card']) ?></span></td>
                                    <td>Rp <?= number_format($u['salary'], 0, ',', '.') ?></td>
                                    <td><code style="color:#79c0ff;"><?= htmlspecialchars($u['api_secret_key']) ?></code></td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin-top:25px;">2. Tabel: <code>comments</code> (Data Stored XSS)</h3>
                <div class="result-box">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Pengirim</th><th>Email</th><th>Pesan (Raw String di DB)</th><th>Waktu</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($comments_res): while ($cm = $comments_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $cm['id'] ?></td>
                                    <td><?= htmlspecialchars($cm['author']) ?></td>
                                    <td><?= htmlspecialchars($cm['email']) ?></td>
                                    <td><code><?= htmlspecialchars($cm['message']) ?></code></td>
                                    <td><?= $cm['created_at'] ?></td>
                                </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE 1: SQL INJECTION (DATA BREACH SIMULATION)
        // -------------------------------------------------------------
        elseif ($page === 'sqli'):
            $search_user = $_REQUEST['search_query'] ?? '';
            $sqli_rows = [];
            $executed_sql = '';
            $error_msg = '';

            if (!empty($search_user)) {
                if ($mode === 'vulnerable') {
                    // VULNERABLE: Direct concatenation (Standard Single Parameter DVWA Style)
                    $executed_sql = "SELECT id, fullname, username, email, role FROM users WHERE username = '$search_user'";
                    try {
                        $res = $mysqli->query($executed_sql);
                        if ($res && !is_bool($res)) {
                            while ($r = $res->fetch_assoc()) {
                                $sqli_rows[] = $r;
                            }
                        } elseif ($mysqli->error) {
                            $error_msg = $mysqli->error;
                        }
                    } catch (Exception $e) {
                        $error_msg = $e->getMessage();
                    }
                } else {
                    // SECURE: Parameterized Prepared Statement
                    $executed_sql = "SELECT id, fullname, username, email, role FROM users WHERE username = ?";
                    $stmt = $mysqli->prepare($executed_sql);
                    if ($stmt) {
                        $stmt->bind_param("s", $search_user);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($res) {
                            while ($r = $res->fetch_assoc()) {
                                $sqli_rows[] = $r;
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Modul 1: SQL Injection (Simulasi Kebocoran Data Perusahaan)</h2>
                    <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= $mode ?></span>
                </div>
                <p>Fitur pencarian direktori karyawan internal perusahaan berdasarkan username. Fitur normal hanya menampilkan ID, Nama, Username, Email, dan Role.</p>

                <form method="POST" action="index.php?page=sqli">
                    <div class="form-group">
                        <label for="search_query">Cari Username Karyawan:</label>
                        <input type="text" id="search_query" name="search_query" value="<?= htmlspecialchars($search_user) ?>" placeholder="Contoh: superadmin atau ' OR '1'='1">
                    </div>
                    <button type="submit" class="btn-submit">Cari Data</button>
                </form>

                <?php if ($executed_sql): ?>
                    <div class="result-box">
                        <strong>Kueri SQL yang Dikirim ke MySQL Server:</strong>
                        <pre><?= htmlspecialchars($executed_sql) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-danger" style="margin-top:15px;">MySQL Database Error: <?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

                <?php if (!empty($sqli_rows)): ?>
                    <div class="result-box">
                        <strong>Data yang Berhasil Terekstraksi (<?= count($sqli_rows) ?> Rekord):</strong>
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($sqli_rows[0]) as $colName): ?>
                                        <th><?= htmlspecialchars($colName) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sqli_rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $colVal): ?>
                                            <td><?= htmlspecialchars((string)$colVal) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($search_user && !$error_msg): ?>
                    <div class="result-box"><p>Tidak ada data karyawan yang cocok.</p></div>
                <?php endif; ?>

                <details>
                    <summary>Petunjuk Eksploitasi & Pembuktian Data Bocor</summary>
                    <div style="margin-top:10px; font-size:0.9rem;">
                        <p><strong>Skenario Serangan:</strong> Membocorkan NIK KTP, Gaji, Password, dan Kartu Kredit dari tabel MySQL <code>users</code>.</p>
                        <p><strong>1. Bypass Tampilkan Semua User (Boolean):</strong></p>
                        <code>' OR '1'='1</code> atau <code>' OR 1=1 #</code>
                        <p style="margin-top:10px;"><strong>2. Ekstraksi Kolom Sensitif / Flag (UNION Attack):</strong></p>
                        <code>' UNION SELECT id, fullname, password, credit_card, api_secret_key FROM users #</code>
                    </div>
                </details>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE 2: XSS REFLECTED
        // -------------------------------------------------------------
        elseif ($page === 'xss_reflected'):
            $query = $_GET['q'] ?? '';
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Modul 2: Reflected Cross-Site Scripting (XSS)</h2>
                    <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= $mode ?></span>
                </div>
                <p>Fitur pencarian katalog barang yang merefleksikan query input kembali ke dokumen HTML.</p>

                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="xss_reflected">
                    <div class="form-group">
                        <label for="q">Kata Kunci Pencarian:</label>
                        <input type="text" id="q" name="q" value="<?= $mode === 'secure' ? htmlspecialchars($query) : $query ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <button type="submit" class="btn-submit">Cari Produk</button>
                </form>

                <?php if (!empty($query)): ?>
                    <div class="result-box">
                        <p>Hasil pencarian untuk kata kunci: 
                            <?php if ($mode === 'vulnerable'): ?>
                                <strong><?= $query ?></strong>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <details>
                    <summary>Petunjuk Eksploitasi</summary>
                    <div style="margin-top:10px; font-size:0.9rem;">
                        <p><strong>Payload:</strong> <code>&lt;script&gt;alert('Reflected-XSS-Active: ' + document.domain)&lt;/script&gt;</code></p>
                    </div>
                </details>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE 3: XSS STORED (LIVE MYSQL STORAGE)
        // -------------------------------------------------------------
        elseif ($page === 'xss_stored'):
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_comment'])) {
                $author = $_POST['author'] ?? 'Anonymous';
                $email = $_POST['email'] ?? 'anon@test.id';
                $message = $_POST['message'] ?? '';
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

                if ($mode === 'secure') {
                    $author = htmlspecialchars(trim($author), ENT_QUOTES, 'UTF-8');
                    $email = htmlspecialchars(trim($email), ENT_QUOTES, 'UTF-8');
                    $message = htmlspecialchars(trim($message), ENT_QUOTES, 'UTF-8');
                }

                if (!empty($message) && $mysqli) {
                    $stmt = $mysqli->prepare("INSERT INTO comments (author, email, message, ip_address) VALUES (?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("ssss", $author, $email, $message, $ip);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                header("Location: index.php?page=xss_stored");
                exit;
            }
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Modul 3: Stored Cross-Site Scripting (Tersimpan Nyata di MySQL)</h2>
                    <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= $mode ?></span>
                </div>
                <p>Formulir diskusi publik. Pesan yang dikirim akan tersimpan secara persisten di tabel MySQL <code>comments</code>.</p>

                <form method="POST" action="index.php?page=xss_stored">
                    <input type="hidden" name="post_comment" value="1">
                    <div class="form-group">
                        <label for="author">Nama Pengirim:</label>
                        <input type="text" id="author" name="author" placeholder="Nama Anda" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" placeholder="email@domain.id" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Pesan Diskusi:</label>
                        <textarea id="message" name="message" rows="3" placeholder="Tulis komentar... Payload akan disimpan ke database MySQL." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Kirim Komentar ke MySQL</button>
                </form>

                <h3 style="margin-top: 25px;">Feed Diskusi Langsung dari MySQL:</h3>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                    <?php
                    $comments = $mysqli ? $mysqli->query("SELECT * FROM comments ORDER BY id DESC") : null;
                    if ($comments): while ($c = $comments->fetch_assoc()):
                    ?>
                        <div style="background: var(--code-bg); border: 1px solid var(--border); padding: 12px 16px; border-radius: 6px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.8rem; color:#8b949e;">
                                <strong><?= $mode === 'vulnerable' ? $c['author'] : htmlspecialchars($c['author']) ?> (<?= htmlspecialchars($c['email']) ?>)</strong>
                                <span><?= $c['created_at'] ?> | IP: <?= htmlspecialchars($c['ip_address']) ?></span>
                            </div>
                            <div style="color: var(--text-bright);">
                                <?php if ($mode === 'vulnerable'): ?>
                                    <?= $c['message'] ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($c['message'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; endif; ?>
                </div>

                <details>
                    <summary>Petunjuk Eksploitasi Stored XSS</summary>
                    <div style="margin-top:10px; font-size:0.9rem;">
                        <p><strong>Payload:</strong> <code>&lt;img src=x onerror="alert('Stored XSS Pwned! Cookie: ' + document.cookie)"&gt;</code></p>
                        <p>Buka tab <strong>Live DB Inspector</strong> untuk melihat payload script tersimpan di tabel <code>comments</code>.</p>
                    </div>
                </details>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE 4: XSS DOM-BASED
        // -------------------------------------------------------------
        elseif ($page === 'xss_dom'):
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Modul 4: DOM-Based Cross-Site Scripting (XSS)</h2>
                    <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= $mode ?></span>
                </div>
                <p>Fitur salam pembuka personal yang membaca hash URL (client-side) dan menulisnya ke DOM.</p>

                <div class="form-group">
                    <label>Pilih Bahasa / Preferensi:</label>
                    <select id="lang-select" onchange="updateGreeting(this.value)">
                        <option value="Indonesia">Bahasa Indonesia</option>
                        <option value="English">English</option>
                        <option value="Jawa">Basa Jawa</option>
                    </select>
                </div>

                <div class="result-box">
                    <strong>Output Sambutan:</strong>
                    <div id="greeting-output" style="margin-top: 8px; font-size: 1.1rem; color: var(--text-bright);">
                        Selamat Datang!
                    </div>
                </div>

                <script>
                    function updateGreeting(customVal) {
                        const val = customVal || window.location.hash.substring(1);
                        const output = document.getElementById('greeting-output');
                        
                        <?php if ($mode === 'vulnerable'): ?>
                            if (val) {
                                output.innerHTML = "Bahasa Terpilih: " + decodeURIComponent(val);
                            }
                        <?php else: ?>
                            if (val) {
                                output.textContent = "Bahasa Terpilih: " + decodeURIComponent(val);
                            }
                        <?php endif; ?>
                    }

                    window.addEventListener('DOMContentLoaded', () => {
                        if (window.location.hash) updateGreeting();
                    });
                    window.addEventListener('hashchange', () => updateGreeting());
                </script>

                <details>
                    <summary>Petunjuk Eksploitasi DOM XSS</summary>
                    <div style="margin-top:10px; font-size:0.9rem;">
                        <p><strong>Cara Uji:</strong> Masukkan hash payload pada URL browser:</p>
                        <code>index.php?page=xss_dom#&lt;img src=1 onerror=alert('DOM_XSS_Active')&gt;</code>
                    </div>
                </details>
            </div>

        <?php
        // -------------------------------------------------------------
        // MODULE 5: INSECURE FILE UPLOAD
        // -------------------------------------------------------------
        elseif ($page === 'upload'):
            $upload_status = '';
            $uploaded_file_url = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['attachment'])) {
                $file = $_FILES['attachment'];
                $fileName = $file['name'];
                $fileTmp = $file['tmp_name'];
                $fileSize = $file['size'];

                if ($mode === 'vulnerable') {
                    $targetPath = $upload_dir . basename($fileName);
                    if (move_uploaded_file($fileTmp, $targetPath)) {
                        $upload_status = 'success';
                        $uploaded_file_url = 'uploads/' . rawurlencode(basename($fileName));
                    } else {
                        $upload_status = 'error';
                    }
                } else {
                    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    $mime = '';
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $fileTmp);
                        finfo_close($finfo);
                    } else {
                        $mime = $file['type'];
                    }

                    $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];

                    if (!in_array($ext, $allowedExts) || ($mime && !in_array($mime, $allowedMimes))) {
                        $upload_status = 'invalid_type';
                    } elseif ($fileSize > 2 * 1024 * 1024) {
                        $upload_status = 'too_large';
                    } else {
                        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
                        $targetPath = $upload_dir . $safeName;
                        if (move_uploaded_file($fileTmp, $targetPath)) {
                            $upload_status = 'success';
                            $uploaded_file_url = 'uploads/' . $safeName;
                        } else {
                            $upload_status = 'error';
                        }
                    }
                }
            }
        ?>
            <div class="card">
                <div class="card-header">
                    <h2>Modul 5: Insecure File Upload (Web Shell / RCE)</h2>
                    <span class="badge <?= $mode === 'vulnerable' ? 'badge-danger' : 'badge-success' ?>"><?= $mode ?></span>
                </div>
                <p>Fitur unggah berkas profil pengguna.</p>

                <form method="POST" action="index.php?page=upload" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="attachment">Pilih Berkas Lampiran:</label>
                        <input type="file" id="attachment" name="attachment" required>
                    </div>
                    <button type="submit" class="btn-submit">Upload File</button>
                </form>

                <?php if ($upload_status === 'success'): ?>
                    <div class="alert alert-success" style="margin-top:15px;">
                        File berhasil diunggah! Akses URL: <a href="<?= $uploaded_file_url ?>" target="_blank" style="color:#3fb950; font-weight:bold;"><?= htmlspecialchars($uploaded_file_url) ?></a>
                    </div>
                <?php elseif ($upload_status === 'invalid_type'): ?>
                    <div class="alert alert-danger" style="margin-top:15px;">
                        Upload Ditolak: Tipe file dilarang! Hanya diperbolehkan format PNG, JPG, dan PDF.
                    </div>
                <?php elseif ($upload_status === 'too_large'): ?>
                    <div class="alert alert-danger" style="margin-top:15px;">
                        Upload Ditolak: Ukuran file melebihi batas 2MB.
                    </div>
                <?php elseif ($upload_status === 'error'): ?>
                    <div class="alert alert-danger" style="margin-top:15px;">
                        Terjadi kesalahan saat memindahkan file.
                    </div>
                <?php endif; ?>

                <details>
                    <summary>Petunjuk Eksploitasi & Payload Web Shell</summary>
                    <div style="margin-top:10px; font-size:0.9rem;">
                        <p><strong>Target:</strong> Unggah file <code>sample_materials/webshell.php</code> untuk mendapatkan akses RCE ke server host.</p>
                    </div>
                </details>
            </div>

        <?php endif; ?>
    </main>
</div>

</body>
</html>
