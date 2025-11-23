// ============================================
// MBG System - Karyawan Management JavaScript
// File: mbg/assets/js/karyawan.js
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // Initialize all functions
  initializeSidebar();
  initializeSearch();
  autoCloseAlerts();
  addRippleEffect();
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
  const karyawanItems = document.querySelectorAll(".karyawan-item");
  const emptyState = document.getElementById("emptyState");

  if (searchInput) {
    searchInput.addEventListener("input", function (e) {
      const searchTerm = e.target.value.toLowerCase().trim();
      let visibleCount = 0;

      karyawanItems.forEach((item) => {
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

    // Search icon animation
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
// Edit Karyawan Function
// ============================================
function editKaryawan(data) {
  // Populate modal fields
  document.getElementById("edit_id_karyawan").value = data.id_karyawan;
  document.getElementById("edit_nama").value = data.nama;
  document.getElementById("edit_email").value = data.email;
  document.getElementById("edit_no_telepon").value = data.no_telepon || "";
  document.getElementById("edit_id_pengelola").value = data.id_pengelola;
  document.getElementById("edit_id_dapur").value = data.id_dapur || "";
  document.getElementById("edit_bagian").value = data.bagian || "";
  document.getElementById("edit_alamat").value = data.alamat || "";
  document.getElementById("edit_tanggal_bergabung").value =
    data.tanggal_bergabung || "";
  document.getElementById("edit_status").value = data.status;

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("modalEdit"));
  modal.show();

  // Add entrance animation
  const modalDialog = document.querySelector("#modalEdit .modal-dialog");
  modalDialog.style.animation = "modalSlideIn 0.4s ease-out";
}

// ============================================
// Delete Karyawan Function
// ============================================
function deleteKaryawan(id, nama) {
  const confirmation = confirm(
    `Apakah Anda yakin ingin menghapus karyawan "${nama}"?\n\nTindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait!`
  );

  if (confirmation) {
    showLoading();
    window.location.href = `karyawan.php?delete=${id}`;
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
// Auto Close Alerts
// ============================================
function autoCloseAlerts() {
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach((alert) => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
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

// Add ripple CSS dynamically
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
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }
    
    .loading-spinner-large {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(137, 207, 240, 0.3);
        border-radius: 50%;
        border-top-color: var(--baby-blue);
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
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

// Add shake animation
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
// Filter by Status
// ============================================
function filterByStatus(status) {
  const karyawanItems = document.querySelectorAll(".karyawan-item");
  let visibleCount = 0;

  karyawanItems.forEach((item) => {
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
// Export to CSV
// ============================================
function exportToCSV() {
  const cards = document.querySelectorAll(".karyawan-card");
  let csv =
    "Nama,Email,No Telepon,Dapur,Pengelola,Bagian,Tanggal Bergabung,Status\n";

  cards.forEach((card) => {
    const nama = card.querySelector(".karyawan-name")?.textContent || "";
    const infoItems = card.querySelectorAll(".info-item span");
    const email = infoItems[0]?.textContent || "";
    const telepon = infoItems[1]?.textContent || "";
    const dapur = infoItems[2]?.textContent || "";
    const pengelola = infoItems[3]?.textContent || "";
    const bagian = card.querySelector(".karyawan-position")?.textContent || "";
    const tanggal = infoItems[4]?.textContent.replace("Bergabung: ", "") || "";
    const status =
      card.querySelector(".badge")?.textContent.toUpperCase() || "";

    csv += `"${nama}","${email}","${telepon}","${dapur}","${pengelola}","${bagian}","${tanggal}","${status}"\n`;
  });

  // Create download
  const blob = new Blob([csv], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `karyawan_data_${new Date().getTime()}.csv`;
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
// Toast Notification
// ============================================
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

// ============================================
// Card Animation on Scroll
// ============================================
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
};

const observer = new IntersectionObserver(function (entries) {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "translateY(0)";
    }
  });
}, observerOptions);

document.querySelectorAll(".karyawan-card").forEach((card) => {
  card.style.opacity = "0";
  card.style.transform = "translateY(30px)";
  card.style.transition = "all 0.6s ease-out";
  observer.observe(card);
});

console.log("👥 Karyawan Management System Loaded Successfully!");
