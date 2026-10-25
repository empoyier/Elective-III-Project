/**
 * APEX Car Rental Management System
 * Core UI JavaScript Utilities
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Minimum date validation for pickup/return fields
    const todayStr = new Date().toISOString().split('T')[0];
    const pickupInputs = document.querySelectorAll('input[type="date"][name*="pickup"], input[type="date"][id*="pickup"]');
    const returnInputs = document.querySelectorAll('input[type="date"][name*="return"], input[type="date"][id*="return"]');

    pickupInputs.forEach(input => {
        if (!input.value) {
            input.min = todayStr;
        }
        input.addEventListener('change', () => {
            returnInputs.forEach(ret => {
                ret.min = input.value;
                if (ret.value && ret.value < input.value) {
                    ret.value = input.value;
                }
            });
        });
    });

    // 2. Auto-close alert notifications after 5s
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(el => {
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.4s ease';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });
    // 3. Hide navbar on scroll down, show on scroll up
    const nav = document.querySelector('.site-nav');
    if (nav) {
        let lastScrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            if (currentScrollY > lastScrollY && currentScrollY > 80) {
                // Scrolling down — hide navbar
                nav.classList.add('nav-hidden');
            } else {
                // Scrolling up — show navbar
                nav.classList.remove('nav-hidden');
            }
            lastScrollY = currentScrollY;
        }, { passive: true });
    }
});

/**
 * Toast Notification Helper
 */
function showToast(message, type = 'info') {
    let container = document.getElementById('apex-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'apex-toast-container';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bg = type === 'success' ? '#059669' : (type === 'danger' ? '#DC2626' : '#0078D7');
    toast.style.cssText = `background:${bg};color:#ffffff;padding:12px 18px;border-radius:4px;font-size:14px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:all 0.3s ease;`;
    toast.textContent = message;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
