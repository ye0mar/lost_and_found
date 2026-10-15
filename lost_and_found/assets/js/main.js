/**
 * Lost & Found Management System - Vanilla JavaScript Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle
    const toggleBtns = document.querySelectorAll('.password-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
            } else {
                input.type = 'password';
                this.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
            }
        });
    });

    // 2. Mobile Sidebar Toggle & Backdrop
    const mobileMenuBtn = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.app-sidebar');
    let backdrop = document.querySelector('.sidebar-backdrop');

    if (!backdrop && sidebar) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    if (mobileMenuBtn && sidebar && backdrop) {
        mobileMenuBtn.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('active');
        });

        backdrop.addEventListener('click', function () {
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('active');
        });
    }

    // 3. Profile Dropdown Menu
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // 4. Image File Upload Preview
    const imageInput = document.getElementById('itemImageInput');
    const previewImg = document.getElementById('imagePreview');
    const uploadPrompt = document.getElementById('uploadPrompt');

    if (imageInput && previewImg) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    if (uploadPrompt) uploadPrompt.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 5. Auto-dismissing Flash Messages
    const flashAlerts = document.querySelectorAll('.flash-alert');
    if (flashAlerts.length > 0) {
        setTimeout(() => {
            flashAlerts.forEach(alert => {
                alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                setTimeout(() => alert.remove(), 400);
            });
        }, 5000);
    }
});

/**
 * Confirm sensitive operations with an accessible modal confirmation.
 * @param {string} message
 * @returns {boolean}
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed with this operation?');
}
