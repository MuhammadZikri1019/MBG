// ============================================
// MBG System - Dapur Management JavaScript
// File: mbg/assets/js/dapur.js
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // Initialize all functions
  initializeSidebar();
  initializeSearch();
  addRippleEffect();

  // Initialize tooltips if Bootstrap is available
  if (typeof bootstrap !== "undefined") {
    const tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Auto dismiss alerts
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach((alert) => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
});

// ============================================
// Sidebar Mobile Toggle
// ============================================
function initializeSidebar() {
  const mobileMenuToggle = document.getElementById("mobileMenuToggle");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  const mainContent = document.getElementById("mainContent");

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
      sidebarOverlay.classList.toggle("active");
      mainContent.classList.toggle("expanded");
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function () {
      sidebar.classList.add("collapsed");
      sidebarOverlay.classList.remove("active");
      mainContent.classList.remove("expanded");
    });
  }
}

// ============================================
// Search Functionality
// ============================================
function initializeSearch() {
  const searchInput = document.getElementById("searchInput");
  const dapurItems = document.querySelectorAll(".dapur-item");
  const emptyState = document.getElementById("emptyState");

  if (searchInput) {
    searchInput.addEventListener("input", function (e) {
      const searchTerm = e.target.value.toLowerCase().trim();
      let visibleCount = 0;

      dapurItems.forEach((item) => {
        const name = item.getAttribute("data-name");
        const shouldShow = name.includes(searchTerm);

        if (shouldShow) {
          item.style.display = "";
          item.style.animation = "fadeInUp 0.4s ease-out";
          visibleCount++;
        } else {
          item.style.display = "none";
        }
      });

      // Show/hide empty state
      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? "block" : "none";
      }
    });

    // Add search icon animation
    searchInput.addEventListener("focus", function () {
      this.parentElement
        .querySelector("i")
        .style.setProperty("transform", "translateY(-50%) scale(1.2)");
    });

    searchInput.addEventListener("blur", function () {
      this.parentElement
        .querySelector("i")
        .style.setProperty("transform", "translateY(-50%) scale(1)");
    });
  }
}

// ============================================
// Edit Dapur Function
// ============================================
function editDapur(data) {
  // Populate modal fields
  document.getElementById("edit_id_dapur").value = data.id_dapur;
  document.getElementById("edit_id_pengelola").value = data.id_pengelola;
  document.getElementById("edit_nama_dapur").value = data.nama_dapur;
  document.getElementById("edit_alamat").value = data.alamat;
  document.getElementById("edit_kapasitas_produksi").value =
    data.kapasitas_produksi || "";
  document.getElementById("edit_status").value = data.status;

  // Show modal with animation
  const modal = new bootstrap.Modal(document.getElementById("modalEdit"));
  modal.show();

  // Add entrance animation
  const modalDialog = document.querySelector("#modalEdit .modal-dialog");
  modalDialog.style.animation = "modalSlideIn 0.4s ease-out";
}

// ============================================
// Delete Dapur Function
// ============================================
function deleteDapur(id, nama) {
  // Custom sweet alert style confirmation
  const confirmation = confirm(
    `Apakah Anda yakin ingin menghapus dapur "${nama}"?\n\nTindakan ini tidak dapat dibatalkan!`
  );

  if (confirmation) {
    // Show loading
    showLoading();

    // Redirect to delete
    window.location.href = `dapur.php?delete=${id}`;
  }
}

// ============================================
// Loading Overlay
// ============================================
function showLoading() {
  const loadingHTML = `
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner-large"></div>
        </div>
    `;

  document.body.insertAdjacentHTML("beforeend", loadingHTML);
}

function hideLoading() {
  const overlay = document.getElementById("loadingOverlay");
  if (overlay) {
    overlay.style.animation = "fadeOut 0.3s ease-out";
    setTimeout(() => overlay.remove(), 300);
  }
}

// ============================================
// Ripple Effect
// ============================================
function addRippleEffect() {
  const buttons = document.querySelectorAll(".btn-action, .btn-primary");

  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const ripple = document.createElement("span");
      ripple.classList.add("ripple-effect");

      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.style.width = ripple.style.height = size + "px";
      ripple.style.left = x + "px";
      ripple.style.top = y + "px";

      this.appendChild(ripple);

      setTimeout(() => ripple.remove(), 600);
    });
  });
}

// Add ripple effect CSS dynamically
const rippleStyle = document.createElement("style");
rippleStyle.textContent = `
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);

// ============================================
// Form Validation
// ============================================
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;

  const inputs = form.querySelectorAll("input[required], select[required]");
  let isValid = true;

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("is-invalid");
      isValid = false;

      // Add shake animation
      input.style.animation = "shake 0.5s";
      setTimeout(() => {
        input.style.animation = "";
      }, 500);
    } else {
      input.classList.remove("is-invalid");
    }
  });

  return isValid;
}

// Add shake animation CSS
const shakeStyle = document.createElement("style");
shakeStyle.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        animation: shake 0.5s;
    }
`;
document.head.appendChild(shakeStyle);

// ============================================
// Card Hover 3D Effect
// ============================================
function init3DCards() {
  const cards = document.querySelectorAll(".dapur-card");

  cards.forEach((card) => {
    card.addEventListener("mousemove", function (e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      const centerX = rect.width / 2;
      const centerY = rect.height / 2;

      const rotateX = ((y - centerY) / centerY) * -5;
      const rotateY = ((x - centerX) / centerX) * 5;

      this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) scale(1.02)`;
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "";
    });
  });
}

// Initialize 3D effect
init3DCards();

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
// Auto-save form data to localStorage
// ============================================
function autoSaveForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return;

  const inputs = form.querySelectorAll("input, select, textarea");

  inputs.forEach((input) => {
    // Load saved data
    const savedValue = localStorage.getItem(`${formId}_${input.name}`);
    if (savedValue && !input.value) {
      input.value = savedValue;
    }

    // Save on input
    input.addEventListener("input", function () {
      localStorage.setItem(`${formId}_${input.name}`, this.value);
    });
  });

  // Clear on submit
  form.addEventListener("submit", function () {
    inputs.forEach((input) => {
      localStorage.removeItem(`${formId}_${input.name}`);
    });
  });
}

// ============================================
// Export Data Function
// ============================================
function exportToCSV() {
  const cards = document.querySelectorAll(".dapur-card");
  let csv = "Nama Dapur,Pengelola,Alamat,Jumlah Karyawan,Status\n";

  cards.forEach((card) => {
    const nama = card.querySelector(".dapur-title").textContent;
    const infoItems = card.querySelectorAll(".info-item span");
    const pengelola = infoItems[0]?.textContent || "";
    const alamat = infoItems[1]?.textContent || "";
    const karyawan = infoItems[2]?.textContent || "";
    const status =
      card.querySelector(".badge")?.textContent.toUpperCase() || "";

    csv += `"${nama}","${pengelola}","${alamat}","${karyawan}","${status}"\n`;
  });

  // Create download
  const blob = new Blob([csv], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `dapur_data_${new Date().getTime()}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
}

// ============================================
// Keyboard Shortcuts
// ============================================
document.addEventListener("keydown", function (e) {
  // Ctrl/Cmd + K to focus search
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    document.getElementById("searchInput")?.focus();
  }

  // ESC to clear search
  if (e.key === "Escape") {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
      searchInput.value = "";
      searchInput.dispatchEvent(new Event("input"));
    }
  }
});

// ============================================
// Status Filter
// ============================================
function filterByStatus(status) {
  const dapurItems = document.querySelectorAll(".dapur-item");
  let visibleCount = 0;

  dapurItems.forEach((item) => {
    const badge = item.querySelector(".badge");
    const itemStatus = badge?.textContent.toLowerCase();

    if (status === "all" || itemStatus === status.toLowerCase()) {
      item.style.display = "";
      item.style.animation = "fadeInUp 0.4s ease-out";
      visibleCount++;
    } else {
      item.style.display = "none";
    }
  });

  // Show/hide empty state
  const emptyState = document.getElementById("emptyState");
  if (emptyState) {
    emptyState.style.display = visibleCount === 0 ? "block" : "none";
  }
}

// ============================================
// Print Function
// ============================================
function printDapurList() {
  window.print();
}

// ============================================
// Copy to Clipboard
// ============================================
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    // Show toast notification
    showToast("Data berhasil disalin!", "success");
  });
}

function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `toast-notification toast-${type}`;
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
  }, 100);

  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Add toast CSS
const toastStyle = document.createElement("style");
toastStyle.textContent = `
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 25px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 10000;
    }
    
    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }
    
    .toast-success {
        border-left: 4px solid #28a745;
    }
    
    .toast-error {
        border-left: 4px solid #dc3545;
    }
    
    .toast-info {
        border-left: 4px solid #89CFF0;
    }
`;
document.head.appendChild(toastStyle);

console.log("🏠 Dapur Management System Loaded Successfully!");
