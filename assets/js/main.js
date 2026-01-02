/**
 * Jack Nine Tables - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cookie Consent Banner
    const cookieBanner = document.getElementById('cookieBanner');
    const acceptCookies = document.getElementById('acceptCookies');

    try {
        if (cookieBanner && !localStorage.getItem('cookiesAccepted')) {
            setTimeout(() => {
                cookieBanner.classList.add('show');
            }, 1000);
        }

        if (acceptCookies) {
            acceptCookies.addEventListener('click', function() {
                try {
                    localStorage.setItem('cookiesAccepted', 'true');
                } catch (e) {}
                cookieBanner.classList.remove('show');
            });
        }
    } catch (e) {
        // localStorage may be blocked in private browsing
    }

    // Mobile Navigation Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
        function toggleMenu(e) {
            // Prevent any default behavior and stop propagation
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            navMenu.classList.toggle('active');
            return false;
        }

        // Use touchstart for mobile devices (fires before click)
        navToggle.addEventListener('touchstart', toggleMenu, { passive: false });

        // Also add click for desktop/fallback, but prevent double-firing
        let touchFired = false;
        navToggle.addEventListener('touchend', function() {
            touchFired = true;
            setTimeout(function() { touchFired = false; }, 300);
        });
        navToggle.addEventListener('click', function(e) {
            if (!touchFired) {
                toggleMenu(e);
            }
        });
    }

    // Auto-dismiss flash messages
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 15000);
    });

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(function(field) {
                removeError(field);

                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, 'This field is required');
                } else if (field.type === 'email' && !isValidEmail(field.value)) {
                    isValid = false;
                    showError(field, 'Please enter a valid email address');
                }
            });

            // Password confirmation check (only if field has a value)
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');

            if (password && confirmPassword && confirmPassword.value.trim() && password.value !== confirmPassword.value) {
                isValid = false;
                showError(confirmPassword, 'Passwords do not match');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    function showError(field, message) {
        field.classList.add('is-invalid');
        const error = document.createElement('div');
        error.className = 'form-error';
        error.textContent = message;
        field.parentNode.appendChild(error);
    }

    function removeError(field) {
        field.classList.remove('is-invalid');
        const existingErrors = field.parentNode.querySelectorAll('.form-error');
        existingErrors.forEach(function(error) {
            error.remove();
        });
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Password Toggle Visibility
    document.querySelectorAll('.password-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const wrapper = this.closest('.password-wrapper');
            const input = wrapper.querySelector('input');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });
    });
});

// Utility function for AJAX requests
async function fetchAPI(url, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    };

    const config = { ...defaults, ...options };

    try {
        const response = await fetch(url, config);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
