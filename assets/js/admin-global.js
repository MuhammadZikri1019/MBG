// ============================================
// MBG System - Global Admin JavaScript
// File: mbg/assets/js/admin-global.js
// Fungsi umum untuk semua halaman admin
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // Initialize sidebar on all pages
  initializeGlobalSidebar();

  // Auto close alerts
  autoCloseAlerts();

  // Initialize tooltips
  initializeTooltips();

  // Add smooth scroll
  addSmoothScroll();
});

// ============================================
// Global Sidebar Toggle (Works on All Pages)
// ============================================
function initializeGlobalSidebar() {
  const mobileMenuToggle = document.getElementById("mobileMenuToggle");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const mainContent = document.getElementById("mainContent");

  // Mobile menu toggle button click
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", function (e) {
      e.stopPropagation();

      const isCollapsed = sidebar.classList.contains("collapsed");

      if (isCollapsed) {
        // Open sidebar
        sidebar.classList.remove("collapsed");
        sidebarOverlay.classList.add("active");
        mainContent.classList.remove("expanded");
      } else {
        // Close sidebar
        sidebar.classList.add("collapsed");
        sidebarOverlay.classList.remove("active");
        mainContent.classList.add("expanded");
      }

      console.log("Sidebar toggled:", !isCollapsed ? "closed" : "opened");
    });
  }

  // Sidebar overlay click to close
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function () {
      sidebar.classList.add("collapsed");
      sidebarOverlay.classList.remove("active");
      mainContent.classList.add("expanded");
      console.log("Sidebar closed via overlay");
    });
  }

  // Close sidebar when clicking menu link on mobile
  const sidebarLinks = document.querySelectorAll(".sidebar-menu a");
  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth <= 991) {
        sidebar.classList.add("collapsed");
        sidebarOverlay.classList.remove("active");
        mainContent.classList.add("expanded");
      }
    });
  });

  // Auto close sidebar on window resize
  let resizeTimer;
  window.addEventListener("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth > 991) {
        sidebar.classList.remove("collapsed");
        sidebarOverlay.classList.remove("active");
        mainContent.classList.remove("expanded");
      } else {
        sidebar.classList.add("collapsed");
        sidebarOverlay.classList.remove("active");
        mainContent.classList.add("expanded");
      }
    }, 250);
  });

  // Initialize sidebar state based on screen size
  if (window.innerWidth <= 991) {
    sidebar.classList.add("collapsed");
    mainContent.classList.add("expanded");
  }
}

// ============================================
// Auto Close Alerts
// ============================================
function autoCloseAlerts() {
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert.alert-dismissible");
    alerts.forEach((alert) => {
      if (typeof bootstrap !== "undefined") {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      } else {
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 300);
      }
    });
  }, 5000);
}

// ============================================
// Initialize Tooltips
// ============================================
function initializeTooltips() {
  if (typeof bootstrap !== "undefined") {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
}

// ============================================
// Smooth Scroll
// ============================================
function addSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href !== "#" && href.length > 1) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });
}

// ============================================
// Show Loading Overlay
// ============================================
function showLoading() {
  const loadingHTML = `
    <div class="loading-overlay" id="loadingOverlay">
      <div class="loading-spinner-large"></div>
    </div>
  `;

  if (!document.getElementById("loadingOverlay")) {
    document.body.insertAdjacentHTML("beforeend", loadingHTML);
  }
}

// ============================================
// Hide Loading Overlay
// ============================================
function hideLoading() {
  const overlay = document.getElementById("loadingOverlay");
  if (overlay) {
    overlay.style.animation = "fadeOut 0.3s ease-out";
    setTimeout(() => overlay.remove(), 300);
  }
}

// ============================================
// Show Toast Notification
// ============================================
function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `toast-notification toast-${type}`;
  toast.innerHTML = `
    <i class="bi bi-${
      type === "success"
        ? "check-circle"
        : type === "error"
        ? "exclamation-triangle"
        : "info-circle"
    }-fill me-2"></i>
    ${message}
  `;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
  }, 100);

  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ============================================
// Confirm Delete Dialog
// ============================================
function confirmDelete(itemName, itemType = "data") {
  return confirm(
    `Apakah Anda yakin ingin menghapus ${itemType} "${itemName}"?\n\nTindakan ini tidak dapat dibatalkan!`
  );
}

// ============================================
// Format Currency (Rupiah)
// ============================================
function formatRupiah(angka) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka);
}

// ============================================
// Format Date (Indonesian)
// ============================================
function formatDate(dateString) {
  const options = { day: "numeric", month: "long", year: "numeric" };
  return new Date(dateString).toLocaleDateString("id-ID", options);
}

// ============================================
// Copy to Clipboard
// ============================================
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(
    function () {
      showToast("Berhasil disalin ke clipboard!", "success");
    },
    function (err) {
      showToast("Gagal menyalin: " + err, "error");
    }
  );
}

// ============================================
// Keyboard Shortcuts
// ============================================
document.addEventListener("keydown", function (e) {
  // Ctrl/Cmd + K to focus search
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
      searchInput.focus();
      searchInput.select();
    }
  }

  // ESC to close modals or clear search
  if (e.key === "Escape") {
    // Clear search
    const searchInput = document.getElementById("searchInput");
    if (searchInput && searchInput.value) {
      searchInput.value = "";
      searchInput.dispatchEvent(new Event("input"));
    }

    // Close sidebar on mobile
    if (window.innerWidth <= 991) {
      const sidebar = document.getElementById("sidebar");
      const sidebarOverlay = document.getElementById("sidebarOverlay");
      const mainContent = document.getElementById("mainContent");

      if (sidebar && !sidebar.classList.contains("collapsed")) {
        sidebar.classList.add("collapsed");
        sidebarOverlay.classList.remove("active");
        mainContent.classList.add("expanded");
      }
    }
  }
});

// ============================================
// Add Global Styles
// ============================================
const globalStyles = document.createElement("style");
globalStyles.textContent = `
  /* Loading Overlay */
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    backdrop-filter: blur(5px);
  }

  .loading-spinner-large {
    width: 60px;
    height: 60px;
    border: 5px solid rgba(137, 207, 240, 0.3);
    border-radius: 50%;
    border-top-color: #89CFF0;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Toast Notification */
  .toast-notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 16px 24px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    z-index: 10000;
    display: flex;
    align-items: center;
    max-width: 400px;
    font-weight: 500;
  }

  .toast-notification.show {
    transform: translateY(0);
    opacity: 1;
  }

  .toast-success {
    border-left: 4px solid #28a745;
    color: #155724;
  }

  .toast-error {
    border-left: 4px solid #dc3545;
    color: #721c24;
  }

  .toast-info {
    border-left: 4px solid #89CFF0;
    color: #0c5460;
  }

  .toast-notification i {
    font-size: 20px;
  }

  /* Fade Out Animation */
  @keyframes fadeOut {
    to {
      opacity: 0;
      transform: translateY(-20px);
    }
  }

  /* Mobile Menu Toggle Animation */
  .mobile-menu-toggle {
    transition: transform 0.3s ease, background 0.3s ease;
  }

  .mobile-menu-toggle:active {
    transform: scale(0.95);
  }

  /* Ensure sidebar is on top */
  .sidebar {
    z-index: 1000 !important;
  }

  .sidebar-overlay {
    z-index: 999 !important;
  }

  .mobile-menu-toggle {
    z-index: 1100 !important;
  }
`;
document.head.appendChild(globalStyles);

// ============================================
// Log that script loaded
// ============================================
console.log("✅ Admin Global JavaScript Loaded Successfully!");
console.log("📱 Mobile menu toggle initialized");
console.log("⌨️  Keyboard shortcuts: Ctrl+K (Search), ESC (Close)");
