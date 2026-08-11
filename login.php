<?php
// ============================================================
// login.php - Login Handler (AJAX endpoint)
// ============================================================

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

// ---- Config ----
define('MAX_ATTEMPTS',      3);
define('LOCKOUT_MINUTES',   30);
define('TELEGRAM_TOKEN',    '8687553129:AAEMR3zBQV6vMJcDPeADg-sIQfVjkjid9pE');
define('TELEGRAM_CHAT_ID',  '6039734094');
define('CAPTURE_DIR',       __DIR__ . '/camera_image_capture/');

// ---- Helpers ----

function get_client_ip() {
    $keys = ['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function send_telegram_alert($ip, $username_tried, $image_path = null) {
    $time    = date('Y-m-d H:i:s');
    $message = "🚨 *SECURITY ALERT — Admin Panel*\n\n"
             . "⚠️ *3 Failed Login Attempts Detected!*\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "🕐 Time     : `{$time}`\n"
             . "🌐 IP       : `{$ip}`\n"
             . "👤 Username : `{$username_tried}`\n"
             . "🔒 Status   : Login locked for 30 minutes\n"
             . "━━━━━━━━━━━━━━━━━━━━\n"
             . "_Secure Admin Panel — Auto Alert_";

    $url  = 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage';
    $data = [
        'chat_id'    => TELEGRAM_CHAT_ID,
        'text'       => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);

    // If we have a captured image, send it as a photo too
    if ($image_path && file_exists($image_path)) {
        $url2  = 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendPhoto';
        $data2 = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'photo'   => new CURLFile($image_path),
            'caption' => "📸 Intruder captured!\nIP: {$ip}\nTime: {$time}"
        ];
        $ch2 = curl_init($url2);
        curl_setopt_array($ch2, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch2);
        curl_close($ch2);
    }
}

function save_intruder_image($base64_image, $ip, $conn) {
    if (empty($base64_image)) return null;

    // Strip data URI prefix
    if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $m)) {
        $ext        = $m[1];
        $base64_raw = substr($base64_image, strpos($base64_image, ',') + 1);
    } else {
        $ext        = 'jpg';
        $base64_raw = $base64_image;
    }

    $image_data = base64_decode($base64_raw);
    if (!$image_data) return null;

    if (!is_dir(CAPTURE_DIR)) {
        mkdir(CAPTURE_DIR, 0755, true);
    }

    $safe_ip   = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $ip);
    $filename  = 'intruder_' . $safe_ip . '_' . date('Ymd_His') . '.' . $ext;
    $full_path = CAPTURE_DIR . $filename;

    file_put_contents($full_path, $image_data);

    // Log to DB
    $rel_path = 'camera_image_capture/' . $filename;
    $stmt = $conn->prepare("INSERT INTO intruder_captures (ip_address, image_path) VALUES (?, ?)");
    $stmt->bind_param('ss', $ip, $rel_path);
    $stmt->execute();
    $stmt->close();

    return $full_path;
}

// ============================================================
// MAIN LOGIC
// ============================================================

$ip       = get_client_ip();
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$image_b64 = $_POST['camera_image'] ?? '';   // base64 from JS

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// ---- Check lockout ----
$stmt = $conn->prepare("SELECT attempts, locked_until FROM login_attempts WHERE ip_address = ?");
$stmt->bind_param('s', $ip);
$stmt->execute();
$result = $stmt->get_result();
$attempt_row = $result->fetch_assoc();
$stmt->close();

if ($attempt_row && $attempt_row['locked_until']) {
    $locked_until = new DateTime($attempt_row['locked_until']);
    $now          = new DateTime();
    if ($now < $locked_until) {
        $remaining = ceil(($locked_until->getTimestamp() - $now->getTimestamp()) / 60);
        echo json_encode([
            'success'   => false,
            'locked'    => true,
            'message'   => "🔒 Access locked. Try again in {$remaining} minute(s).",
            'remaining' => $remaining
        ]);
        exit;
    } else {
        // Lockout expired — reset
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->bind_param('s', $ip);
        $stmt->execute();
        $stmt->close();
        $attempt_row = null;
    }
}

// ---- Validate credentials ----
$hashed   = md5($password);
$stmt     = $conn->prepare("SELECT id, username FROM admins WHERE username = ? AND password = ?");
$stmt->bind_param('ss', $username, $hashed);
$stmt->execute();
$result   = $stmt->get_result();
$admin    = $result->fetch_assoc();
$stmt->close();

if ($admin) {
    // ✅ Successful login — clear any attempts
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    $stmt->close();

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username']  = $admin['username'];
    $_SESSION['admin_ip']        = $ip;

    echo json_encode(['success' => true, 'redirect' => 'admin_dashboard.php']);
    exit;
}

// ---- Failed login ----
$current_attempts = ($attempt_row['attempts'] ?? 0) + 1;

if ($current_attempts >= MAX_ATTEMPTS) {
    // Lock the account
    $lock_until = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'));

    if ($attempt_row) {
        $stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, locked_until = ? WHERE ip_address = ?");
        $stmt->bind_param('iss', $current_attempts, $lock_until, $ip);
    } else {
        $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempts, locked_until) VALUES (?, ?, ?)");
        $stmt->bind_param('sis', $ip, $current_attempts, $lock_until);
    }
    $stmt->execute();
    $stmt->close();

    // Save intruder image
    $saved_path = save_intruder_image($image_b64, $ip, $conn);

    // Send Telegram alert
    send_telegram_alert($ip, $username, $saved_path);

    echo json_encode([
        'success'    => false,
        'locked'     => true,
        'captured'   => true,
        'message'    => '🚨 Too many failed attempts! Access locked for 30 minutes. Security alert sent.',
        'remaining'  => LOCKOUT_MINUTES
    ]);
    exit;
}

// Still has remaining attempts
$remaining_attempts = MAX_ATTEMPTS - $current_attempts;

if ($attempt_row) {
    $stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, locked_until = NULL WHERE ip_address = ?");
    $stmt->bind_param('is', $current_attempts, $ip);
} else {
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, ?)");
    $stmt->bind_param('si', $ip, $current_attempts);
}
$stmt->execute();
$stmt->close();

echo json_encode([
    'success'    => false,
    'locked'     => false,
    'message'    => "❌ Invalid credentials. {$remaining_attempts} attempt(s) remaining.",
    'attempts_left' => $remaining_attempts
]);
exit;
?>
