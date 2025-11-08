/**
 * EmailJS Integration for Donation Notifications
 * Sends email notifications when donations are successfully processed
 */

class EmailJSIntegration {
    constructor() {
        // EmailJS configuration
        // TODO: Replace with your actual EmailJS Public Key, Service ID, and Template ID
        this.config = {
            publicKey: 'XL67sYYgjAuw2ep-p', // Get from https://dashboard.emailjs.com/admin/integration
            serviceId: 'service_lcj5q2q', // Get from https://dashboard.emailjs.com/admin
            templateId: 'template_o9mb8m7' // Get from https://dashboard.emailjs.com/admin/template
        };
        
        this.initialized = false;
        this.init();
    }

    init() {
        // Check if EmailJS is loaded
        if (typeof emailjs === 'undefined') {
            console.error('EmailJS SDK not loaded. Please include the EmailJS script in your HTML.');
            return;
        }

        // Initialize EmailJS with public key
        if (this.config.publicKey && this.config.publicKey !== 'YOUR_EMAILJS_PUBLIC_KEY') {
            emailjs.init(this.config.publicKey);
            this.initialized = true;
            console.log('EmailJS initialized successfully');
        } else {
            console.warn('EmailJS not configured. Please set your public key in emailjs-integration.js');
        }
    }

    /**
     * Send donation success notification email
     * @param {Object} donationData - Donation information
     * @param {string} donationData.userEmail - Recipient email address
     * @param {string} donationData.userName - Donor name
     * @param {string} donationData.organizationName - Organization name
     * @param {string} donationData.donationType - Type of donation (money, clothes, food, etc.)
     * @param {number} donationData.amount - Donation amount (if applicable)
     * @param {string} donationData.description - Donation description
     * @param {string} donationData.donationDate - Date of donation
     * @returns {Promise} Email sending promise
     */
    async sendDonationNotification(donationData) {
        if (!this.initialized) {
            console.error('EmailJS not initialized. Cannot send email.');
            return Promise.reject('EmailJS not initialized');
        }

        // Validate required fields
        if (!donationData.userEmail) {
            console.error('User email is required for donation notification');
            return Promise.reject('User email is required');
        }

        // Prepare email template parameters
        const templateParams = {
            to_email: donationData.userEmail,
            to_name: donationData.userName || 'Valued Donor',
            organization_name: donationData.organizationName || 'Organization',
            donation_type: this.formatDonationType(donationData.donationType),
            donation_amount: donationData.amount ? `₱${parseFloat(donationData.amount).toFixed(2)}` : 'N/A',
            donation_description: donationData.description || 'No description provided',
            donation_date: donationData.donationDate || new Date().toLocaleDateString(),
            donation_id: donationData.donationId || 'N/A',
            message: this.generateDonationMessage(donationData)
        };

        try {
            const response = await emailjs.send(
                this.config.serviceId,
                this.config.templateId,
                templateParams
            );
            
            console.log('Donation notification email sent successfully:', response);
            return response;
        } catch (error) {
            console.error('Failed to send donation notification email:', error);
            throw error;
        }
    }

    /**
     * Format donation type for display
     */
    formatDonationType(type) {
        const types = {
            'money': '💰 Money',
            'clothes': '👕 Clothes',
            'food': '🍔 Food',
            'blood': '🩸 Blood',
            'other': '📦 Other'
        };
        return types[type] || type;
    }

    /**
     * Generate personalized donation message
     */
    generateDonationMessage(donationData) {
        let message = `Thank you for your generous donation of `;
        
        if (donationData.donationType === 'money') {
            message += `₱${parseFloat(donationData.amount).toFixed(2)}`;
        } else {
            message += donationData.description || donationData.donationType;
        }
        
        message += ` to ${donationData.organizationName || 'the organization'}. `;
        message += `Your contribution makes a significant difference and is greatly appreciated.`;
        
        return message;
    }

    /**
     * Update EmailJS configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        if (this.config.publicKey && this.config.publicKey !== 'YOUR_EMAILJS_PUBLIC_KEY') {
            emailjs.init(this.config.publicKey);
            this.initialized = true;
        }
    }
}

// Initialize EmailJS integration
let emailJSIntegration;

// Initialize when DOM is ready or immediately if DOM already loaded
function initializeEmailJS() {
    if (typeof emailjs === 'undefined') {
        console.error('EmailJS SDK not loaded. Please include the EmailJS script before emailjs-integration.js');
        return;
    }
    emailJSIntegration = new EmailJSIntegration();
    window.emailJSIntegration = emailJSIntegration;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEmailJS);
} else {
    // DOM already loaded, initialize immediately
    initializeEmailJS();
}

// Helper function to send donation notification
window.sendDonationEmail = async (donationData) => {
    // Wait for EmailJS integration to be initialized (with timeout)
    let attempts = 0;
    const maxAttempts = 50; // 5 seconds max wait (50 * 100ms)
    
    while (!window.emailJSIntegration && attempts < maxAttempts) {
        await new Promise(resolve => setTimeout(resolve, 100));
        attempts++;
    }
    
    if (!window.emailJSIntegration) {
        console.error('EmailJS integration not initialized after waiting. Please check your configuration.');
        return;
    }
    
    // Wait for initialization to complete
    if (!window.emailJSIntegration.initialized) {
        console.warn('EmailJS is not fully initialized. Attempting to send email anyway...');
    }
    
    try {
        await window.emailJSIntegration.sendDonationNotification(donationData);
        console.log('Donation email notification sent successfully');
    } catch (error) {
        console.error('Error sending donation email:', error);
        // Don't throw error to user - email failure shouldn't block donation success
    }
};

