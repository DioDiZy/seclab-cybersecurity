<?php
/**
 * SecLab - Logout Handler
 */
session_start();
session_unset();
session_destroy();
header("Location: login.php?msg=Anda%20telah%20berhasil%20logout.");
exit;
