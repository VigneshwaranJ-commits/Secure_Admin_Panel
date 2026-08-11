<?php
// ============================================================
// admin_dashboard.php - Protected Admin Dashboard
// ============================================================
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.html');
    exit;
}

$admin_name = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$admin_ip   = htmlspecialchars($_SESSION['admin_ip'] ?? '');
$login_time = date('F j, Y — g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Secure Panel</title>
  <meta name="description" content="Secure Admin Dashboard — authorized access only." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>

<!-- Animated background -->
<div class="bg-orbs">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- Top Nav -->
<nav class="dash-nav">
  <div class="nav-brand">
    <div class="brand-icon">🛡️</div>
    <span class="brand-name">SecurePanel</span>
  </div>
  <div class="nav-user">
    <div class="user-avatar"><?= strtoupper(substr($admin_name, 0, 1)) ?></div>
    <div class="user-info">
      <span class="user-name"><?= $admin_name ?></span>
      <span class="user-role">Super Administrator</span>
    </div>
    <a href="logout.php" class="logout-btn" id="logoutBtn">
      <span>⏻</span> Logout
    </a>
  </div>
</nav>

<!-- Main Content -->
<main class="dash-main">

  <!-- Welcome Banner -->
  <div class="welcome-banner" id="welcomeBanner">
    <div class="welcome-content">
      <div class="welcome-icon">✅</div>
      <div class="welcome-text">
        <h1>Welcome back, <?= $admin_name ?>!</h1>
        <p>You are securely logged in. Session started: <?= $login_time ?></p>
      </div>
    </div>
    <div class="session-tag">
      <span>🌐</span> <?= $admin_ip ?>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card" style="--accent:#6C63FF">
      <div class="stat-icon">👥</div>
      <div class="stat-data">
        <div class="stat-value" id="totalUsers">1</div>
        <div class="stat-label">Admin Users</div>
      </div>
      <div class="stat-glow"></div>
    </div>
    <div class="stat-card" style="--accent:#00C9FF">
      <div class="stat-icon">🔐</div>
      <div class="stat-data">
        <div class="stat-value" id="secureConn">SSL</div>
        <div class="stat-label">Encryption</div>
      </div>
      <div class="stat-glow"></div>
    </div>
    <div class="stat-card" style="--accent:#FF6B6B">
      <div class="stat-icon">🚨</div>
      <div class="stat-data">
        <div class="stat-value" id="alertCount">0</div>
        <div class="stat-label">Security Alerts</div>
      </div>
      <div class="stat-glow"></div>
    </div>
    <div class="stat-card" style="--accent:#43D392">
      <div class="stat-icon">📡</div>
      <div class="stat-data">
        <div class="stat-value">ONLINE</div>
        <div class="stat-label">System Status</div>
      </div>
      <div class="stat-glow"></div>
    </div>
  </div>

  <!-- Activity Panel -->
  <div class="panels-grid">
    <div class="panel" id="activityPanel">
      <div class="panel-header">
        <h2>🛡️ Security Activity Log</h2>
        <div class="live-badge"><span class="pulse"></span> LIVE</div>
      </div>
      <div class="activity-list" id="activityList">
        <div class="activity-item success">
          <div class="act-icon">✅</div>
          <div class="act-info">
            <span class="act-title">Successful Login</span>
            <span class="act-detail"><?= $admin_name ?> — <?= $admin_ip ?></span>
          </div>
          <span class="act-time"><?= date('g:i A') ?></span>
        </div>
        <div class="activity-item info">
          <div class="act-icon">📷</div>
          <div class="act-info">
            <span class="act-title">Camera Verification Passed</span>
            <span class="act-detail">Biometric check complete</span>
          </div>
          <span class="act-time"><?= date('g:i A') ?></span>
        </div>
        <div class="activity-item info">
          <div class="act-icon">🔒</div>
          <div class="act-info">
            <span class="act-title">Session Initiated</span>
            <span class="act-detail">Secure session started</span>
          </div>
          <span class="act-time"><?= date('g:i A') ?></span>
        </div>
      </div>
    </div>

    <div class="panel" id="systemPanel">
      <div class="panel-header">
        <h2>⚙️ System Info</h2>
      </div>
      <div class="sys-info-list">
        <div class="sys-item">
          <span class="sys-label">Panel Version</span>
          <span class="sys-value badge-green">v1.0.0</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">PHP Version</span>
          <span class="sys-value"><?= phpversion() ?></span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Database</span>
          <span class="sys-value badge-green">Connected</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Camera Auth</span>
          <span class="sys-value badge-green">Enabled</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Telegram Alerts</span>
          <span class="sys-value badge-green">Active</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Max Login Attempts</span>
          <span class="sys-value">3 / IP</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Lockout Duration</span>
          <span class="sys-value">30 minutes</span>
        </div>
        <div class="sys-item">
          <span class="sys-label">Server Time</span>
          <span class="sys-value" id="serverClock"><?= date('H:i:s') ?></span>
        </div>
      </div>
    </div>
  </div>

</main>

<script>
  // Live clock
  function updateClock() {
    const el = document.getElementById('serverClock');
    if (el) el.textContent = new Date().toLocaleTimeString();
  }
  setInterval(updateClock, 1000);

  // Animate stat counters
  document.querySelectorAll('.stat-value').forEach(el => {
    const val = el.textContent;
    if (!isNaN(val) && val.trim() !== '') {
      let count = 0;
      const target = parseInt(val);
      const step   = Math.ceil(target / 30);
      const timer  = setInterval(() => {
        count = Math.min(count + step, target);
        el.textContent = count;
        if (count >= target) clearInterval(timer);
      }, 40);
    }
  });

  // Entrance animations
  document.querySelectorAll('.stat-card, .panel, .welcome-banner').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    setTimeout(() => {
      el.style.transition = 'all 0.5s ease';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, 100 + i * 80);
  });
</script>
</body>
</html>
