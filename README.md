# 🛡️ SecurePanel — Secure Admin Panel System

A full-stack secure admin panel with **biometric camera verification**, **Telegram security alerts**, **intruder image capture**, and **IP-based lockout**.

---

## 📁 Project Structure

```
secure_admin_panel/
├── index.html              ← Home page (beautiful landing)
├── admin_login.html        ← Admin login with live camera preview
├── admin_dashboard.php     ← Protected dashboard (PHP session)
├── login.php               ← Login AJAX handler (PHP + MySQL)
├── logout.php              ← Logout handler
├── db.php                  ← Database connection
├── setup.sql               ← Database schema + admin credentials
├── css/
│   ├── style.css           ← Home page styles
│   ├── login.css           ← Login page styles
│   └── dashboard.css       ← Dashboard styles
├── js/
│   ├── home.js             ← Home page logic + camera modal
│   └── login.js            ← Login form + camera capture logic
└── camera_image_capture/   ← Intruder photos saved here
    └── .htaccess           ← Blocks direct browser access
```

---

## ⚙️ Setup Instructions

### 1. XAMPP Setup
1. Copy this folder to: `C:\xampp\htdocs\secure_admin_panel\`
2. Start **Apache** and **MySQL** in XAMPP Control Panel

### 2. Database Setup
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **SQL** tab
3. Paste and run the contents of `setup.sql`
4. This creates:
   - Database: `admin_panel`
   - Table: `admins` (with hashed credentials)
   - Table: `login_attempts` (for lockout tracking)
   - Table: `intruder_captures` (for image logs)

### 3. Admin Credentials
| Field    | Value          |
|----------|----------------|
| Username | `vigneshwaran` |
| Password | `password123`  |

### 4. Access the App
- **Home Page**: http://localhost/secure_admin_panel/index.html
- **Admin Login**: http://localhost/secure_admin_panel/admin_login.html
- **Dashboard**: http://localhost/secure_admin_panel/admin_dashboard.php

---

## 🔐 Security Features

| Feature | Details |
|---------|---------|
| 📷 Camera Auth | Front camera required before login page access |
| 🔒 IP Lockout | 30-minute lockout after 3 failed attempts |
| 📸 Intruder Capture | Camera photo taken on 3rd failed attempt |
| 📲 Telegram Alert | Instant alert + photo sent to bot |
| 🗄️ DB-backed | Attempts tracked per IP in MySQL |
| 🛡️ Session Auth | PHP sessions protect dashboard |
| 🚫 Folder Protection | `.htaccess` blocks direct image access |

---

## 📲 Telegram Bot
- **Token**: 
- **Chat ID**: 
- Alerts include: IP address, username attempted, timestamp, and intruder photo

---

## ⚠️ Notes
- The login page requires **HTTPS or localhost** for camera API (`getUserMedia`)
- XAMPP localhost works fine for development
- For production, deploy on HTTPS server
- Captured images are stored in `camera_image_capture/` and blocked from public access
