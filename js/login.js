/* ============================================================
   login.js — Admin Login Page JavaScript
   Handles: camera stream, form submission, lockout timer,
            camera capture on failed attempts
   ============================================================ */

'use strict';

// ---- Redirect if camera wasn't allowed ----
// (Uncomment in production to enforce camera-first flow)
// if (!sessionStorage.getItem('camera_allowed')) {
//   window.location.href = 'index.html';
// }

// ============================================================
//  CAMERA STREAM
// ============================================================
const video        = document.getElementById('cameraPreview');
const captureCanvas = document.getElementById('captureCanvas');
let   mediaStream  = null;

async function startCamera() {
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
      audio: false
    });
    if (video) {
      video.srcObject = mediaStream;
      video.play();
    }
  } catch (err) {
    console.warn('Camera not available:', err.message);
    // If camera fails, redirect home (camera is mandatory)
    sessionStorage.removeItem('camera_allowed');
    window.location.href = 'index.html';
  }
}

function captureFrame() {
  if (!video || !captureCanvas || !mediaStream) return null;
  const ctx = captureCanvas.getContext('2d');
  captureCanvas.width  = video.videoWidth  || 640;
  captureCanvas.height = video.videoHeight || 480;
  // Draw mirrored (natural face)
  ctx.translate(captureCanvas.width, 0);
  ctx.scale(-1, 1);
  ctx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
  return captureCanvas.toDataURL('image/jpeg', 0.85);
}

// ============================================================
//  LOGIN FORM
// ============================================================
const form          = document.getElementById('loginForm');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const loginBtn      = document.getElementById('loginBtn');
const alertBox      = document.getElementById('alertBox');
const lockoutSection= document.getElementById('lockoutSection');
const countdownEl   = document.getElementById('countdown');
const meterFill     = document.getElementById('meterFill');
const attemptsLeft  = document.getElementById('attemptsLeft');

let lockoutInterval = null;
let attemptsUsed    = 0;

// Toggle password visibility
const togglePw = document.getElementById('togglePw');
if (togglePw) {
  togglePw.addEventListener('click', () => {
    const isPass = passwordInput.type === 'password';
    passwordInput.type = isPass ? 'text' : 'password';
    togglePw.textContent = isPass ? '🙈' : '👁️';
  });
}

// Show alert message
function showAlert(message, type = 'error') {
  alertBox.className  = `alert-box ${type}`;
  alertBox.innerHTML  = `<span>${message}</span>`;
  alertBox.style.display = 'flex';
}

function hideAlert() {
  alertBox.style.display = 'none';
  alertBox.className  = 'alert-box';
}

// Update attempts meter
function updateMeter(remaining) {
  if (!meterFill) return;
  if (remaining === 3) { meterFill.className = 'meter-fill'; }
  else if (remaining === 2) { meterFill.className = 'meter-fill warn'; }
  else if (remaining === 1) { meterFill.className = 'meter-fill danger'; }

  if (attemptsLeft) attemptsLeft.textContent = remaining;
}

// Lockout countdown timer
function startLockoutTimer(minutes) {
  if (form)          form.style.display      = 'none';
  if (lockoutSection) lockoutSection.classList.add('show');

  let remainingSecs = minutes * 60;

  function tick() {
    const m = Math.floor(remainingSecs / 60).toString().padStart(2, '0');
    const s = (remainingSecs % 60).toString().padStart(2, '0');
    if (countdownEl) countdownEl.textContent = `${m}:${s}`;

    if (remainingSecs <= 0) {
      clearInterval(lockoutInterval);
      // Reload the page to reset
      window.location.reload();
    }
    remainingSecs--;
  }

  tick();
  lockoutInterval = setInterval(tick, 1000);
}

// Set loading state
function setLoading(loading) {
  if (!loginBtn) return;
  loginBtn.disabled = loading;
  const text   = loginBtn.querySelector('.btn-text');
  const loader = loginBtn.querySelector('.btn-loader');
  if (loading) {
    text.style.display   = 'none';
    loader.style.display = 'block';
  } else {
    text.style.display   = 'inline';
    loader.style.display = 'none';
  }
}

// ============================================================
//  FORM SUBMIT
// ============================================================
if (form) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    if (!username || !password) {
      showAlert('⚠️ Please enter both username and password.', 'warning');
      return;
    }

    setLoading(true);

    // Capture camera frame for potential intruder image
    const cameraImage = captureFrame() || '';

    // Build form data
    const data = new FormData();
    data.append('username',     username);
    data.append('password',     password);
    data.append('camera_image', cameraImage);

    try {
      const res  = await fetch('login.php', { method: 'POST', body: data });
      const json = await res.json();

      if (json.success) {
        // ✅ Login successful
        showAlert('✅ Login successful! Redirecting…', 'success');
        setTimeout(() => {
          window.location.href = json.redirect || 'admin_dashboard.php';
        }, 800);
        return;
      }

      if (json.locked) {
        // Account locked
        if (json.captured) {
          showAlert('🚨 Security alert sent! Your image has been captured.', 'error');
        }
        setTimeout(() => startLockoutTimer(json.remaining || 30), 600);
        return;
      }

      // Wrong password — update meter
      attemptsUsed++;
      const remaining = json.attempts_left ?? (3 - attemptsUsed);
      updateMeter(remaining);
      showAlert(json.message || '❌ Invalid credentials.', 'error');

      // Shake the card
      const card = document.querySelector('.login-card');
      if (card) {
        card.style.animation = 'none';
        requestAnimationFrame(() => {
          card.style.animation = 'shake 0.4s ease';
        });
      }

      // Clear password field
      passwordInput.value = '';
      passwordInput.focus();

    } catch (err) {
      showAlert('⚠️ Network error. Please try again.', 'warning');
    } finally {
      setLoading(false);
    }
  });
}

// Shake animation injection
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
@keyframes shake {
  0%,100% { transform: translateX(0); }
  15%     { transform: translateX(-8px); }
  30%     { transform: translateX(8px); }
  45%     { transform: translateX(-6px); }
  60%     { transform: translateX(6px); }
  75%     { transform: translateX(-3px); }
  90%     { transform: translateX(3px); }
}`;
document.head.appendChild(shakeStyle);

// ============================================================
//  Check existing lockout on page load
// ============================================================
async function checkLockoutOnLoad() {
  // Quick ping to server to see if this IP is already locked
  const data = new FormData();
  data.append('username', '__check__');
  data.append('password', '__check__');
  data.append('camera_image', '');

  try {
    const res  = await fetch('login.php', { method: 'POST', body: data });
    const json = await res.json();
    if (json.locked) {
      startLockoutTimer(json.remaining || 30);
    }
  } catch (_) {}
}

// Start camera then check lockout
startCamera().then(() => {
  checkLockoutOnLoad();
});
