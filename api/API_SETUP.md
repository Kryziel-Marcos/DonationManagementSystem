# Quick EmailJS Setup Guide

## 🚀 Quick Start (5 Minutes)

### Step 1: Sign Up for EmailJS
1. Go to [https://www.emailjs.com/](https://www.emailjs.com/) and create a free account
2. Verify your email

### Step 2: Add Email Service
1. In EmailJS dashboard → **Email Services** → **Add New Service**
2. Choose **Gmail** (or your preferred provider)
3. Connect your email account
4. **Copy your Service ID** (looks like: `service_abc123`)

### Step 3: Create Email Template
1. In EmailJS dashboard → **Email Templates** → **Create New Template**
2. **Template Name:** `Donation Confirmation`
3. **Subject:** `Thank You for Your Donation, {{to_name}}!`
4. **Content:** Copy the template from `EMAILJS_SETUP.md` (Step 3)
5. **Copy your Template ID** (looks like: `template_xyz789`)

### Step 4: Get Public Key
1. In EmailJS dashboard → **Account** → **General**
2. **Copy your Public Key** (looks like: `abcdefghijklmnop`)

### Step 5: Configure
1. Open `assets/emailjs-integration.js`
2. Replace these three values:

```javascript
this.config = {
    publicKey: 'YOUR_EMAILJS_PUBLIC_KEY',      // ← Paste your Public Key here
    serviceId: 'YOUR_EMAILJS_SERVICE_ID',      // ← Paste your Service ID here
    templateId: 'YOUR_EMAILJS_TEMPLATE_ID'     // ← Paste your Template ID here
};
```

### Step 6: Test
1. Make a test donation in your system
2. Check your email inbox for the confirmation!

## 📋 What You Need

- ✅ EmailJS account (free)
- ✅ Email address (Gmail/Outlook/Yahoo)
- ✅ Three values: Public Key, Service ID, Template ID

## 📚 Full Documentation

See `EMAILJS_SETUP.md` for:
- Detailed step-by-step instructions
- Email template HTML code
- Troubleshooting guide
- Advanced configuration

## ⚠️ Important Notes

- Free tier: 200 emails/month
- Email is sent automatically after successful donation
- Check browser console (F12) if emails aren't sending
- All configuration is in `assets/emailjs-integration.js`

---

**Need Help?** Check `EMAILJS_SETUP.md` for detailed troubleshooting!

