/**
 * Theme Toggle Management
 * Handles dark mode and light mode switching with localStorage persistence
 */

(function() {
    'use strict';

    const THEME_STORAGE_KEY = 'donation-system-theme';
    const THEME_ATTRIBUTE = 'data-theme';
    const LIGHT_THEME = 'light';
    const DARK_THEME = 'dark';

    /**
     * Get the current theme from localStorage or default to light
     */
    function getStoredTheme() {
        try {
            const stored = localStorage.getItem(THEME_STORAGE_KEY);
            return stored === DARK_THEME ? DARK_THEME : LIGHT_THEME;
        } catch (e) {
            console.warn('Unable to access localStorage:', e);
            return LIGHT_THEME;
        }
    }

    /**
     * Save theme preference to localStorage
     */
    function saveTheme(theme) {
        try {
            localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch (e) {
            console.warn('Unable to save theme to localStorage:', e);
        }
    }

    /**
     * Apply theme to the document
     */
    function applyTheme(theme) {
        const html = document.documentElement;
        if (theme === DARK_THEME) {
            html.setAttribute(THEME_ATTRIBUTE, DARK_THEME);
        } else {
            html.removeAttribute(THEME_ATTRIBUTE);
        }
    }

    /**
     * Update theme toggle button icon
     */
    function updateToggleIcon(theme) {
        const toggleButton = document.getElementById('theme-toggle-btn');
        if (!toggleButton) return;

        const icon = toggleButton.querySelector('.theme-icon');
        if (!icon) return;

        if (theme === DARK_THEME) {
            icon.textContent = '☀️'; // Sun icon for light mode (clicking will switch to light)
            icon.setAttribute('aria-label', 'Switch to light mode');
        } else {
            icon.textContent = '🌙'; // Moon icon for dark mode (clicking will switch to dark)
            icon.setAttribute('aria-label', 'Switch to dark mode');
        }
    }

    /**
     * Toggle between light and dark theme
     */
    function toggleTheme() {
        const currentTheme = getStoredTheme();
        const newTheme = currentTheme === DARK_THEME ? LIGHT_THEME : DARK_THEME;
        
        applyTheme(newTheme);
        saveTheme(newTheme);
        updateToggleIcon(newTheme);
    }

    /**
     * Initialize theme on page load
     */
    function initTheme() {
        const theme = getStoredTheme();
        applyTheme(theme);
        updateToggleIcon(theme);
    }

    /**
     * Create and insert theme toggle button
     */
    function createToggleButton() {
        // Check if button already exists
        if (document.getElementById('theme-toggle-btn')) {
            return;
        }

        const button = document.createElement('button');
        button.id = 'theme-toggle-btn';
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle theme');
        button.setAttribute('title', 'Toggle dark/light mode');
        
        const icon = document.createElement('span');
        icon.className = 'theme-icon';
        icon.setAttribute('aria-hidden', 'true');
        
        button.appendChild(icon);
        document.body.appendChild(button);

        // Add click event listener
        button.addEventListener('click', toggleTheme);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            createToggleButton();
        });
    } else {
        // DOM is already ready
        initTheme();
        createToggleButton();
    }

    // Export functions for external use if needed
    window.themeManager = {
        toggle: toggleTheme,
        setTheme: function(theme) {
            if (theme === DARK_THEME || theme === LIGHT_THEME) {
                applyTheme(theme);
                saveTheme(theme);
                updateToggleIcon(theme);
            }
        },
        getTheme: getStoredTheme
    };
})();

