function showLogoutConfirmation(callback) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'logout-confirmation-overlay';
    
    const dialog = document.createElement('div');
    dialog.className = 'modal-dialog';
    
    const header = document.createElement('div');
    header.className = 'modal-header';
    
    const iconWrapper = document.createElement('div');
    iconWrapper.className = 'modal-icon-wrapper';
    iconWrapper.style.background = 'linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.2))';
    
    const icon = document.createElement('div');
    icon.className = 'modal-icon';
    icon.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
    `;
    icon.style.color = '#3b82f6';
    icon.style.width = '24px';
    icon.style.height = '24px';
    
    iconWrapper.appendChild(icon);
    
    const title = document.createElement('h2');
    title.className = 'modal-title';
    title.textContent = 'Confirm Logout';
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'modal-close';
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    `;
    closeBtn.onclick = () => closeDialog();
    
    header.appendChild(iconWrapper);
    header.appendChild(title);
    header.appendChild(closeBtn);
    
    const content = document.createElement('div');
    content.className = 'modal-content';
    
    const message = document.createElement('p');
    message.className = 'modal-message';
    message.textContent = 'Are you sure you want to logout?';
    
    const subtitle = document.createElement('p');
    subtitle.className = 'modal-subtitle';
    subtitle.textContent = 'You will need to login again to access your dashboard.';
    subtitle.style.borderLeftColor = '#3b82f6';
    
    content.appendChild(message);
    content.appendChild(subtitle);
    
    const footer = document.createElement('div');
    footer.className = 'modal-footer';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'modal-btn modal-btn-cancel';
    cancelBtn.type = 'button';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = () => closeDialog();
    
    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'modal-btn modal-btn-danger';
    confirmBtn.type = 'button';
    confirmBtn.textContent = 'Logout';
    confirmBtn.onclick = () => {
        closeDialog();
        if (callback) callback();
    };
    
    footer.appendChild(cancelBtn);
    footer.appendChild(confirmBtn);
    
    dialog.appendChild(header);
    dialog.appendChild(content);
    dialog.appendChild(footer);
    overlay.appendChild(dialog);
    
    document.body.appendChild(overlay);
    
    requestAnimationFrame(() => {
        overlay.classList.add('active');
    });
    
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            closeDialog();
        }
    };
    
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeDialog();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
    
    function closeDialog() {
        overlay.classList.remove('active');
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
}

