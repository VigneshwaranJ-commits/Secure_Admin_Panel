<?php
// ============================================================
// db.php - Database Connection
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASS', '');           // Default XAMPP MySQL password (empty)
define('DB_NAME', 'admin_panel');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');
?>
