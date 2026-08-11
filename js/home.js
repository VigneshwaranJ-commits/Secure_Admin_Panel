/* ============================================================
   home.js — Home Page JavaScript
   Secure Admin Panel
   ============================================================ */

'use strict';

// ---- Particle System ----
(function spawnParticles() {
  const container = document.querySelector('.particles');
  if (!container) return;

  const COLORS = ['#7c3aed','#3b82f6','#06b6d4','#10b981','#a78bfa'];
  const COUNT  = 25;

  for (let i = 0; i < COUNT; i++) {
    const p = document.createElement('div');
    p.className = 'particle';

    const size     = Math.random() * 4 + 2;
    const color    = COLORS[Math.floor(Math.random() * COLORS.length)];
    const left     = Math.random() * 100;
    const duration = Math.random() * 15 + 10;
    const delay    = Math.random() * 10;

    p.style.cssText = `
      width: ${size}px;
      height: ${size}px;
      left: ${left}%;
      background: ${color};
      animation-duration: ${duration}s;
      animation-delay: ${delay}s;
      box-shadow: 0 0 ${size * 3}px ${color};
    `;
    container.appendChild(p);
  }
})();

// ---- Scroll Reveal ----
(function initReveal() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, i * 80);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();

// ---- Navbar scroll effect ----
(function initNavbar() {
  const nav = document.querySelector('.navbar');
  if (!nav) return;
  window.addEventListener('scroll', () => {
    nav.style.background = window.scrollY > 50
      ? 'rgba(8,11,20,0.95)'
      : 'rgba(8,11,20,0.7)';
  });
})();

// ---- Feature Card hover glow ----
(function initCardGlow() {
  document.querySelectorAll('.feature-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x    = ((e.clientX - rect.left) / rect.width  * 100).toFixed(1);
      const y    = ((e.clientY - rect.top)  / rect.height * 100).toFixed(1);
      card.style.setProperty('--mouse-x', `${x}%`);
      card.style.setProperty('--mouse-y', `${y}%`);
    });
  });
})();

// ---- Counter Animation ----
(function animateCounters() {
  const els = document.querySelectorAll('[data-count]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el     = entry.target;
      const target = parseInt(el.dataset.count);
      const suffix = el.dataset.suffix || '';
      let count    = 0;
      const step   = Math.ceil(target / 60);
      const timer  = setInterval(() => {
        count = Math.min(count + step, target);
        el.textContent = count.toLocaleString() + suffix;
        if (count >= target) clearInterval(timer);
      }, 30);
      observer.unobserve(el);
    });
  }, { threshold: 0.5 });

  els.forEach(el => observer.observe(el));
})();

// ============================================================
//  CAMERA PERMISSION MODAL & ADMIN LOGIN FLOW
// ============================================================
const modal    = document.getElementById('cameraModal');
const allowBtn = document.getElementById('allowCameraBtn');
const denyBtn  = document.getElementById('denyCameraBtn');
const loginBtn = document.getElementById('adminLoginBtn');

function showModal() {
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function hideModal() {
  modal.classList.remove('active');
  document.body.style.overflow = '';
}

function goToLogin() {
  hideModal();
  window.location.href = 'admin_login.html';
}

function goToHome() {
  hideModal();
  // Stay on home — optionally show a message
  showDeniedMessage();
}

function showDeniedMessage() {
  const msg = document.getElementById('cameraRequiredMsg');
  if (msg) {
    msg.style.display = 'flex';
    setTimeout(() => { msg.style.display = 'none'; }, 5000);
  }
}

// When user clicks Admin Login
if (loginBtn) {
  loginBtn.addEventListener('click', () => {
    showModal();
  });
}

// Allow camera
if (allowBtn) {
  allowBtn.addEventListener('click', () => {
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => {
        // Stop stream immediately — actual capture happens on login page
        stream.getTracks().forEach(t => t.stop());
        // Mark camera allowed in sessionStorage
        sessionStorage.setItem('camera_allowed', '1');
        goToLogin();
      })
      .catch(() => {
        // Permission denied by browser
        hideModal();
        showDeniedMessage();
      });
  });
}

// Deny camera
if (denyBtn) {
  denyBtn.addEventListener('click', () => {
    goToHome();
  });
}

// Close modal on overlay click
if (modal) {
  modal.addEventListener('click', (e) => {
    if (e.target === modal) goToHome();
  });
}

// Keyboard
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && modal?.classList.contains('active')) goToHome();
});
