/**
 * Enhanced Notification System
 * Provides toast notifications and enhanced alert functionality
 */

class NotificationSystem {
    constructor() {
        this.toastContainer = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.querySelector('.toast-container')) {
            this.toastContainer = document.createElement('div');
            this.toastContainer.className = 'toast-container';
            document.body.appendChild(this.toastContainer);
        } else {
            this.toastContainer = document.querySelector('.toast-container');
        }

        // Create alert container for stacking alerts
        if (!document.querySelector('.alert-container')) {
            this.alertContainer = document.createElement('div');
            this.alertContainer.className = 'alert-container';
            this.alertContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-width: 400px;
            `;
            document.body.appendChild(this.alertContainer);
        } else {
            this.alertContainer = document.querySelector('.alert-container');
        }

        // Add close functionality to existing alerts
        this.enhanceExistingAlerts();
    }

    enhanceExistingAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (!alert.querySelector('.alert-close')) {
                this.addCloseButton(alert);
            }
            // Auto-dismiss alerts after 5 seconds
            this.autoDismissAlert(alert);
        });
    }

    addCloseButton(alert) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'alert-close';
        closeBtn.setAttribute('aria-label', 'Close alert');
        closeBtn.addEventListener('click', () => {
            this.closeAlert(alert);
        });
        alert.appendChild(closeBtn);
    }

    closeAlert(alert) {
        alert.classList.add('fade-out');
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

    autoDismissAlert(alert, duration = 5000) {
        // Add progress bar for visual countdown
        const progressBar = document.createElement('div');
        progressBar.className = 'alert-progress';
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            animation: alertProgress ${duration}ms linear forwards;
        `;
        alert.appendChild(progressBar);

        // Auto-dismiss after duration
        setTimeout(() => {
            this.closeAlert(alert);
        }, duration);
    }

    // Toast notification methods
    showToast(type, title, message, duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const progressBar = document.createElement('div');
        progressBar.className = 'toast-progress';
        
        const closeBtn = document.createElement('button');
        closeBtn.className = 'toast-close';
        closeBtn.setAttribute('aria-label', 'Close notification');
        closeBtn.addEventListener('click', () => {
            this.closeToast(toast);
        });

        toast.innerHTML = `
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        `;
        
        toast.appendChild(progressBar);
        toast.appendChild(closeBtn);
        
        // Add click to close functionality
        toast.addEventListener('click', () => {
            this.closeToast(toast);
        });

        this.toastContainer.appendChild(toast);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.closeToast(toast);
            }, duration);
        }

        return toast;
    }

    closeToast(toast) {
        toast.classList.add('fade-out');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Convenience methods
    success(title, message, duration = 5000) {
        return this.showToast('success', title, message, duration);
    }

    error(title, message, duration = 7000) {
        return this.showToast('error', title, message, duration);
    }

    warning(title, message, duration = 6000) {
        return this.showToast('warning', title, message, duration);
    }

    info(title, message, duration = 5000) {
        return this.showToast('info', title, message, duration);
    }

    // Enhanced alert methods
    showAlert(type, title, message, container = null, autoDismiss = true, duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        
        alertDiv.innerHTML = `
            <span class="alert-title">${title}</span>
            <span class="alert-message">${message}</span>
        `;

        this.addCloseButton(alertDiv);

        // Position alerts in top right corner using alert container
        this.alertContainer.appendChild(alertDiv);

        // Auto-dismiss if enabled
        if (autoDismiss) {
            this.autoDismissAlert(alertDiv, duration);
        }

        return alertDiv;
    }

    // Form validation helpers
    showFieldError(field, message) {
        // Remove existing error
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Add new error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = `
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        errorDiv.innerHTML = `
            <span>⚠️</span>
            <span>${message}</span>
        `;

        field.parentNode.appendChild(errorDiv);
        field.style.borderColor = 'var(--error-color)';
        field.focus();

        // Remove error on input
        field.addEventListener('input', () => {
            errorDiv.remove();
            field.style.borderColor = '';
        }, { once: true });
    }

    clearFieldErrors(form) {
        const errors = form.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const fields = form.querySelectorAll('input, textarea, select');
        fields.forEach(field => {
            field.style.borderColor = '';
        });
    }

    // Success feedback
    showFieldSuccess(field, message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'field-success';
        successDiv.style.cssText = `
            color: var(--success-color);
            font-size: 0.875rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        successDiv.innerHTML = `
            <span>✅</span>
            <span>${message}</span>
        `;

        field.parentNode.appendChild(successDiv);
        field.style.borderColor = 'var(--success-color)';

        // Remove success message after 3 seconds
        setTimeout(() => {
            successDiv.remove();
            field.style.borderColor = '';
        }, 3000);
    }
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notifications = new NotificationSystem();
});

// Global helper functions for easy access
window.showSuccess = (title, message, duration) => {
    return window.notifications.success(title, message, duration);
};

window.showError = (title, message, duration) => {
    return window.notifications.error(title, message, duration);
};

window.showWarning = (title, message, duration) => {
    return window.notifications.warning(title, message, duration);
};

window.showInfo = (title, message, duration) => {
    return window.notifications.info(title, message, duration);
};

window.showFieldError = (field, message) => {
    return window.notifications.showFieldError(field, message);
};

window.showFieldSuccess = (field, message) => {
    return window.notifications.showFieldSuccess(field, message);
};

// Auto-dismissing alert helpers
window.showAutoAlert = (type, title, message, duration = 5000) => {
    return window.notifications.showAlert(type, title, message, null, true, duration);
};

window.showAutoSuccess = (title, message, duration = 5000) => {
    return window.notifications.showAlert('success', title, message, null, true, duration);
};

window.showAutoError = (title, message, duration = 7000) => {
    return window.notifications.showAlert('error', title, message, null, true, duration);
};

window.showAutoWarning = (title, message, duration = 6000) => {
    return window.notifications.showAlert('warning', title, message, null, true, duration);
};

window.showAutoInfo = (title, message, duration = 5000) => {
    return window.notifications.showAlert('info', title, message, null, true, duration);
};

// Enhanced form validation with notifications
window.validateForm = (form) => {
    const notifications = window.notifications;
    notifications.clearFieldErrors(form);
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            notifications.showFieldError(field, `${field.name.replace('_', ' ')} is required`);
            isValid = false;
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !field.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            notifications.showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Password confirmation
    const passwordField = form.querySelector('input[name="password"]');
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    if (passwordField && confirmPasswordField && passwordField.value !== confirmPasswordField.value) {
        notifications.showFieldError(confirmPasswordField, 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
};
