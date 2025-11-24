// ============================================
// MBG System - Complete JavaScript
// Authentication + Dashboard Scroll Fix
// ============================================

// ============================================
// CRITICAL: DASHBOARD SCROLL FIX - FORCE ENABLE
// ============================================
function forceEnableScroll() {
  document.body.style.overflow = "";
  document.body.style.position = "";
  document.body.style.width = "";
  document.body.style.top = "";
  document.documentElement.style.overflow = "";
  document.body.classList.remove("mobile-sidebar-locked");
}

// Execute immediately
if (document.body) {
  forceEnableScroll();
}

// ============================================
// Authentication System
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  // CRITICAL: Force enable scroll on DOM ready
  forceEnableScroll();

  // Elements
  const signUpButton = document.getElementById("signUp");
  const signInButton = document.getElementById("signIn");
  const container = document.getElementById("authContainer");
  const mobileSignUp = document.getElementById("mobileSignUp");
  const mobileSignIn = document.getElementById("mobileSignIn");

  // Desktop Toggle
  if (signUpButton) {
    signUpButton.addEventListener("click", function () {
      container.classList.add("right-panel-active");
      addPulseEffect(this);
    });
  }

  if (signInButton) {
    signInButton.addEventListener("click", function () {
      container.classList.remove("right-panel-active");
      addPulseEffect(this);
    });
  }

  // Mobile Toggle
  if (mobileSignUp) {
    mobileSignUp.addEventListener("click", function (e) {
      e.preventDefault();
      container.classList.add("right-panel-active");
    });
  }

  if (mobileSignIn) {
    mobileSignIn.addEventListener("click", function (e) {
      e.preventDefault();
      container.classList.remove("right-panel-active");
    });
  }

  // Role Selector
  const roleButtons = document.querySelectorAll(".role-btn");
  roleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      roleButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");
      addRippleEffect(this);
    });
  });

  // Input Animation
  const inputs = document.querySelectorAll(".input-group-custom input");
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.parentElement.classList.add("focused");
    });

    input.addEventListener("blur", function () {
      if (!this.value) {
        this.parentElement.classList.remove("focused");
      }
    });
  });

  // Social Buttons Animation
  const socialBtns = document.querySelectorAll(".social-btn");
  socialBtns.forEach((btn, index) => {
    btn.style.animationDelay = `${0.1 * index}s`;

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      addRippleEffect(this);
    });
  });

  // Form Submit Animation
  const forms = document.querySelectorAll(".auth-form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const submitBtn = this.querySelector(".btn-primary");
      if (submitBtn) {
        addLoadingSpinner(submitBtn);
      }
    });
  });

  // Alert Auto Dismiss
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.classList.add("fade-out");
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

  // Initialize dashboard if exists
  if (document.querySelector(".sidebar")) {
    initializeDashboard();
  }

  console.log("✅ Auth System Loaded");
});

// ============================================
// Dashboard Initialization
// ============================================
function initializeDashboard() {
  // Force enable scroll
  forceEnableScroll();

  // Load sidebar state (desktop only)
  if (window.innerWidth >= 1200) {
    const sidebarCollapsed =
      localStorage.getItem("sidebarCollapsed") === "true";
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.querySelector(".main-content");

    if (sidebar && mainContent && sidebarCollapsed) {
      sidebar.classList.add("collapsed");
      mainContent.classList.add("expanded");
    }
  }

  console.log("✅ Dashboard Initialized");
}

// ============================================
// Toggle Password Visibility
// ============================================
function togglePassword(inputId, button) {
  const input = document.getElementById(inputId);
  const icon = button.querySelector("i");

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  }

  // Animation
  button.style.transform = "translateY(-50%) scale(1.2)";
  setTimeout(() => {
    button.style.transform = "translateY(-50%) scale(1)";
  }, 200);
}

// ============================================
// Dashboard Sidebar Toggle
// ============================================
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const mainContent = document.querySelector(".main-content");
  const isMobile = window.innerWidth < 1200;

  if (!sidebar || !overlay || !mainContent) return;

  // Toggle classes
  sidebar.classList.toggle("collapsed");
  overlay.classList.toggle("active");
  mainContent.classList.toggle("expanded");

  // Handle scroll ONLY on mobile
  if (isMobile) {
    if (!sidebar.classList.contains("collapsed")) {
      // Sidebar open - save scroll position and block scroll
      const scrollY = window.scrollY;
      document.body.style.position = "fixed";
      document.body.style.top = `-${scrollY}px`;
      document.body.style.width = "100%";
      document.body.style.overflow = "hidden";
      document.body.dataset.scrollY = scrollY;
    } else {
      // Sidebar closed - restore scroll
      const scrollY = document.body.dataset.scrollY || 0;
      document.body.style.position = "";
      document.body.style.top = "";
      document.body.style.width = "";
      document.body.style.overflow = "";
      window.scrollTo(0, parseInt(scrollY));
    }
  } else {
    // Desktop - always enable scroll
    forceEnableScroll();
  }

  // Save state (desktop only)
  if (!isMobile) {
    localStorage.setItem(
      "sidebarCollapsed",
      sidebar.classList.contains("collapsed")
    );
  }
}

// Close sidebar helper
function closeSidebar() {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const mainContent = document.querySelector(".main-content");

  if (!sidebar || !overlay || !mainContent) return;

  sidebar.classList.add("collapsed");
  overlay.classList.remove("active");
  mainContent.classList.add("expanded");

  // Restore scroll on mobile
  if (window.innerWidth < 1200) {
    const scrollY = document.body.dataset.scrollY || 0;
    document.body.style.position = "";
    document.body.style.top = "";
    document.body.style.width = "";
    document.body.style.overflow = "";
    window.scrollTo(0, parseInt(scrollY));
  } else {
    forceEnableScroll();
  }
}

// ============================================
// Event Listeners for Dashboard
// ============================================

// Page load
window.addEventListener("load", function () {
  forceEnableScroll();
  console.log("✅ Page loaded - Scroll enabled");
});

// ESC key
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    // Close alerts
    document.querySelectorAll(".alert").forEach((alert) => {
      alert.classList.add("fade-out");
      setTimeout(() => alert.remove(), 500);
    });

    // Close sidebar if open
    const sidebar = document.getElementById("sidebar");
    if (sidebar && !sidebar.classList.contains("collapsed")) {
      closeSidebar();
    }
  }

  // Enter to submit
  if (event.key === "Enter" && document.activeElement.tagName === "INPUT") {
    const form = document.activeElement.closest("form");
    if (form) {
      event.preventDefault();
      form.dispatchEvent(new Event("submit"));
    }
  }
});

// Overlay click
if (document.getElementById("sidebarOverlay")) {
  document
    .getElementById("sidebarOverlay")
    .addEventListener("click", closeSidebar);
}

// Menu links (mobile)
document.querySelectorAll(".sidebar-menu a").forEach((link) => {
  link.addEventListener("click", function () {
    if (window.innerWidth < 1200) {
      setTimeout(closeSidebar, 150);
    }
  });
});

// Window resize
let resizeTimer;
window.addEventListener("resize", function () {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(function () {
    if (window.innerWidth >= 1200) {
      const overlay = document.getElementById("sidebarOverlay");
      if (overlay) overlay.classList.remove("active");
      forceEnableScroll();
    }
  }, 250);
});

// Visibility change
document.addEventListener("visibilitychange", function () {
  if (!document.hidden && window.innerWidth >= 1200) {
    forceEnableScroll();
  }
});

// Click anywhere (desktop)
document.addEventListener(
  "click",
  function () {
    if (window.innerWidth >= 1200) {
      forceEnableScroll();
    }
  },
  { passive: true }
);

// Wheel event
document.addEventListener(
  "wheel",
  function () {
    if (window.innerWidth >= 1200) {
      if (
        document.body.style.overflow === "hidden" ||
        document.body.style.position === "fixed"
      ) {
        forceEnableScroll();
        console.warn("⚠️ Scroll blocked during wheel! Fixed.");
      }
    }
  },
  { passive: true }
);

// Scroll event
window.addEventListener(
  "scroll",
  function () {
    if (window.innerWidth >= 1200) {
      if (document.body.style.overflow === "hidden") {
        forceEnableScroll();
        console.warn("⚠️ Scroll blocked during scroll! Fixed.");
      }
    }
  },
  { passive: true }
);

// ============================================
// Aggressive Safety Net
// ============================================
setInterval(function () {
  if (window.innerWidth >= 1200) {
    if (
      document.body.style.overflow === "hidden" ||
      document.body.style.position === "fixed" ||
      document.body.classList.contains("mobile-sidebar-locked")
    ) {
      console.warn("⚠️ Scroll blocked! Auto-fixing...");
      forceEnableScroll();
    }
  }
}, 1000);

// Final check after 500ms
setTimeout(function () {
  forceEnableScroll();
  console.log("✅ Final scroll check complete");
}, 500);

// ============================================
// Ripple Effect
// ============================================
function addRippleEffect(element) {
  const ripple = document.createElement("span");
  ripple.classList.add("ripple-effect");

  const rect = element.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);

  ripple.style.width = ripple.style.height = size + "px";
  ripple.style.left = "50%";
  ripple.style.top = "50%";
  ripple.style.transform = "translate(-50%, -50%) scale(0)";

  element.style.position = "relative";
  element.style.overflow = "hidden";
  element.appendChild(ripple);

  setTimeout(() => {
    ripple.style.transform = "translate(-50%, -50%) scale(2)";
    ripple.style.opacity = "0";
  }, 10);

  setTimeout(() => ripple.remove(), 600);
}

// ============================================
// Pulse Effect
// ============================================
function addPulseEffect(element) {
  element.classList.add("pulse");
  setTimeout(() => element.classList.remove("pulse"), 500);
}

// ============================================
// Loading Spinner
// ============================================
function addLoadingSpinner(button) {
  const originalContent = button.innerHTML;
  button.disabled = true;
  button.innerHTML = '<span class="loading-spinner"></span>' + originalContent;

  setTimeout(() => {
    button.disabled = false;
    button.innerHTML = originalContent;
  }, 2000);
}

// ============================================
// Floating Label Effect
// ============================================
document.querySelectorAll(".input-group-custom input").forEach((input) => {
  input.addEventListener("input", function () {
    if (this.value) {
      this.classList.add("has-value");
    } else {
      this.classList.remove("has-value");
    }
  });
});

// ============================================
// Smooth Scroll
// ============================================
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// ============================================
// Password Strength Indicator
// ============================================
function checkPasswordStrength(password) {
  let strength = 0;

  if (password.length >= 8) strength++;
  if (password.length >= 12) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^a-zA-Z\d]/.test(password)) strength++;

  return strength;
}

const registerPassword = document.getElementById("registerPassword");
if (registerPassword) {
  const strengthIndicator = document.createElement("div");
  strengthIndicator.className = "password-strength";
  strengthIndicator.innerHTML = `
    <div class="strength-bars">
      <span class="strength-bar"></span>
      <span class="strength-bar"></span>
      <span class="strength-bar"></span>
      <span class="strength-bar"></span>
      <span class="strength-bar"></span>
    </div>
    <span class="strength-text">Kekuatan password</span>
  `;

  registerPassword.parentElement.insertAdjacentElement(
    "afterend",
    strengthIndicator
  );

  registerPassword.addEventListener("input", function () {
    const strength = checkPasswordStrength(this.value);
    const bars = strengthIndicator.querySelectorAll(".strength-bar");
    const text = strengthIndicator.querySelector(".strength-text");

    bars.forEach((bar, index) => {
      if (index < strength) {
        bar.classList.add("active");
      } else {
        bar.classList.remove("active");
      }
    });

    const strengthTexts = [
      "Sangat Lemah",
      "Lemah",
      "Cukup",
      "Kuat",
      "Sangat Kuat",
    ];
    const colors = ["#dc3545", "#fd7e14", "#ffc107", "#28a745", "#20c997"];

    if (this.value) {
      text.textContent = strengthTexts[strength - 1] || "Sangat Lemah";
      text.style.color = colors[strength - 1] || "#dc3545";
    } else {
      text.textContent = "Kekuatan password";
      text.style.color = "#7f8c8d";
    }
  });
}

// ============================================
// Inject CSS Styles
// ============================================
const style = document.createElement("style");
style.textContent = `
  .ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transition: transform 0.6s, opacity 0.6s;
    pointer-events: none;
  }
  
  .fade-out {
    animation: fadeOut 0.5s ease-out forwards;
  }
  
  @keyframes fadeOut {
    to {
      opacity: 0;
      transform: translateY(-10px);
    }
  }
  
  .pulse {
    animation: pulse 0.5s ease-out;
  }
  
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
  }
  
  .loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.6s linear infinite;
    margin-right: 8px;
  }
  
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  
  .password-strength {
    margin-top: 8px;
    font-size: 12px;
  }
  
  .strength-bars {
    display: flex;
    gap: 4px;
    margin-bottom: 5px;
  }
  
  .strength-bar {
    height: 4px;
    flex: 1;
    background: #e0e0e0;
    border-radius: 2px;
    transition: background-color 0.3s ease;
  }
  
  .strength-bar.active {
    background: #89CFF0;
  }
  
  .strength-text {
    color: #7f8c8d;
    font-weight: 600;
  }
`;
document.head.appendChild(style);

// ============================================
// OTP Input Handling
// ============================================
document.addEventListener('DOMContentLoaded', function() {
  const otpInputs = document.querySelectorAll('.otp-input');
  const otpForm = document.getElementById('otpForm');
  const hiddenOtpCode = document.getElementById('hiddenOtpCode');
  
  if (otpInputs.length > 0) {
    // Focus first input on load
    otpInputs[0].focus();
    
    otpInputs.forEach((input, index) => {
      // Input event - move to next field
      input.addEventListener('input', function(e) {
        const value = this.value;
        
        // Only allow digits
        this.value = value.replace(/[^0-9]/g, '');
        
        if (this.value.length === 1 && index < otpInputs.length - 1) {
          // Move to next input
          otpInputs[index + 1].focus();
        }
        
        // Update hidden field
        updateHiddenOTP();
        
        // Auto-submit if all filled
        if (index === otpInputs.length - 1 && this.value) {
          checkAndSubmit();
        }
      });
      
      // Keydown event - handle backspace
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
          // Move to previous input and clear it
          otpInputs[index - 1].focus();
          otpInputs[index - 1].value = '';
          updateHiddenOTP();
        }
      });
      
      // Paste event - distribute digits across inputs
      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text');
        const digits = pasteData.replace(/[^0-9]/g, '').split('');
        
        digits.forEach((digit, i) => {
          if (index + i < otpInputs.length) {
            otpInputs[index + i].value = digit;
          }
        });
        
        // Focus last filled input or next empty
        const lastFilledIndex = Math.min(index + digits.length, otpInputs.length - 1);
        otpInputs[lastFilledIndex].focus();
        
        updateHiddenOTP();
        checkAndSubmit();
      });
      
      // Focus event - select content
      input.addEventListener('focus', function() {
        this.select();
      });
    });
    
    // Update hidden OTP code field
    function updateHiddenOTP() {
      if (hiddenOtpCode) {
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        hiddenOtpCode.value = otp;
      }
    }
    
    // Check if all inputs filled and submit
    function checkAndSubmit() {
      const allFilled = Array.from(otpInputs).every(input => input.value.length === 1);
      
      if (allFilled && otpForm) {
        updateHiddenOTP();
        // Add visual feedback before submit
        otpInputs.forEach(input => {
          input.style.borderColor = '#28a745';
          input.style.background = '#d4edda';
        });
        
        // Submit after brief delay
        setTimeout(() => {
          otpForm.submit();
        }, 500);
      }
    }
  }
});

// ============================================
// Console Logs
// ============================================
console.log("🎨 MBG System Loaded!");
console.log("📱 Responsive: ✓ | 🔄 Scroll Fix: ULTRA ACTIVE | 🎯 Features: ✓");
console.log("💡 Monitoring: Click, Wheel, Scroll, Timer (1s)");
