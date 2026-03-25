# Contact Form Email Setup Guide

## Overview
Your contact form now uses **EmailJS** - a free service that sends emails directly from JavaScript without needing a backend server. This guide will walk you through the complete setup process.

---

## Step 1: Create EmailJS Account

1. Go to [https://www.emailjs.com/](https://www.emailjs.com/)
2. Click **"Sign Up"** (it's free - no credit card required)
3. Complete the registration process
4. Verify your email address

---

## Step 2: Add Email Service

1. Log in to your EmailJS dashboard
2. Click **"Email Services"** in the left sidebar
3. Click **"Add New Service"**
4. Choose your email provider (recommended options):
   - **Gmail** (easiest for personal/business Gmail accounts)
   - **Outlook** (if using Microsoft email)
   - **Custom SMTP** (for custom domain emails)

### For Gmail:
   - Click **"Gmail"**
   - Click **"Connect Account"**
   - Sign in with the Gmail account that will SEND the emails
   - Grant permissions
   - Give your service a name (e.g., "ARDY Contact Form")
   - Click **"Create Service"**
   - **Save the Service ID** (you'll need this later)

### For Custom Domain (info@ardyrealestatees.com):
   - Choose **"Custom SMTP"** or your email provider
   - Enter your SMTP settings:
     - SMTP Server (e.g., mail.ardyrealestatees.com)
     - Port (usually 587 for TLS or 465 for SSL)
     - Username (usually your full email address)
     - Password (your email password)
   - Click **"Create Service"**
   - **Save the Service ID**

---

## Step 3: Create Email Template

1. Click **"Email Templates"** in the left sidebar
2. Click **"Create New Template"**
3. Copy and paste this template:

### Template Content:

**Subject:**
```
New Contact Form Submission - ARDY Real Estate
```

**Body:**
```html
<html>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
  <div style="background-color: #3D2A23; padding: 20px; text-align: center;">
    <h1 style="color: #C4B693; margin: 0; font-size: 24px;">New Contact Form Submission</h1>
  </div>
  
  <div style="background-color: #ffffff; padding: 30px; border: 1px solid #ddd;">
    <h2 style="color: #3D2A23; border-bottom: 2px solid #C4B693; padding-bottom: 10px;">Contact Details</h2>
    
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold; width: 150px;">Name:</td>
        <td style="padding: 10px; background-color: #ffffff;">{{from_name}}</td>
      </tr>
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold;">Email:</td>
        <td style="padding: 10px; background-color: #ffffff;">
          <a href="mailto:{{from_email}}" style="color: #3D2A23;">{{from_email}}</a>
        </td>
      </tr>
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold;">Phone:</td>
        <td style="padding: 10px; background-color: #ffffff;">
          <a href="tel:{{phone}}" style="color: #3D2A23;">{{phone}}</a>
        </td>
      </tr>
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold;">Service Interest:</td>
        <td style="padding: 10px; background-color: #ffffff;">{{service_interest}}</td>
      </tr>
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold;">Budget Range:</td>
        <td style="padding: 10px; background-color: #ffffff;">{{budget_range}}</td>
      </tr>
      <tr>
        <td style="padding: 10px; background-color: #F1ECEA; font-weight: bold;">Submitted:</td>
        <td style="padding: 10px; background-color: #ffffff;">{{submission_date}}</td>
      </tr>
    </table>
    
    <h3 style="color: #3D2A23; border-bottom: 2px solid #C4B693; padding-bottom: 10px;">Message:</h3>
    <div style="background-color: #F1ECEA; padding: 20px; border-left: 4px solid #C4B693; margin: 20px 0;">
      <p style="color: #3D2A23; line-height: 1.6; margin: 0; white-space: pre-wrap;">{{message}}</p>
    </div>
    
    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
    
    <p style="font-size: 12px; color: #666; text-align: center; margin: 0;">
      This email was sent from the ARDY Real Estate contact form.<br>
      Please respond within 2 business hours.
    </p>
  </div>
</body>
</html>
```

**To Email (Important!):**
```
info@ardyrealestatees.com
```
(Or whatever admin email address should receive the submissions)

**From Name:**
```
ARDY Website
```

4. Click **"Save"**
5. **Save the Template ID** (you'll need this later)

---

## Step 4: Get Your Public Key

1. Click your account name in the top right
2. Select **"Account"**
3. Find your **"Public Key"** (it looks like: `X_xGHJ8KLMxyz123`)
4. **Save this Public Key**

---

## Step 5: Update Your Website Code

1. Open `contact.html` in your code editor
2. Find this section near line 571:

```javascript
const EMAILJS_CONFIG = {
  publicKey: 'YOUR_PUBLIC_KEY',
  serviceID: 'YOUR_SERVICE_ID',
  templateID: 'YOUR_TEMPLATE_ID'
};
```

3. Replace with your actual values:

```javascript
const EMAILJS_CONFIG = {
  publicKey: 'X_xGHJ8KLMxyz123',      // Your Public Key from Step 4
  serviceID: 'service_abc1234',       // Your Service ID from Step 2
  templateID: 'template_xyz5678'      // Your Template ID from Step 3
};
```

4. If needed, update the admin email:

```javascript
const ADMIN_EMAIL = 'info@ardyrealestatees.com';
```

5. Save the file

---

## Step 6: Test Your Form

1. Open `contact.html` in your web browser
2. Fill out the contact form completely
3. Click **"Send Message"**
4. You should see a success message
5. Check your admin email inbox for the submission

---

## Troubleshooting

### Error: "EmailJS not configured"
- Make sure you replaced all three values: publicKey, serviceID, and templateID
- Values should NOT have quotes inside quotes

### Error: "Failed to send"
- Check your internet connection
- Verify your EmailJS Service is active
- Check your email service credentials in EmailJS dashboard
- Open browser console (F12) to see detailed error messages

### Email not received
- Check spam/junk folder
- Verify the "To Email" in your template is correct
- Test your EmailJS service by clicking "Send Test Email" in the dashboard
- Make sure your email service (Gmail/SMTP) is properly connected

### Free Tier Limits
- EmailJS free plan allows **200 emails per month**
- If you need more, upgrade to a paid plan ($10/month for 1000 emails)

---

## Security Notes

✅ **Safe:** Your Public Key is safe to expose in client-side code
✅ **Protected:** EmailJS prevents spam with rate limiting and domain restrictions
✅ **Recommended:** In EmailJS dashboard, add your website domain to "Allowed Domains" for extra security

---

## Alternative: Testing Without EmailJS Setup

If you want to test the form immediately without setting up EmailJS, you can temporarily use this demo configuration:

```javascript
const EMAILJS_CONFIG = {
  publicKey: 'F-Iw-KZ8YaHxd7QZM',
  serviceID: 'service_demo',
  templateID: 'template_demo'
};
```

⚠️ **Warning:** Demo credentials send to a test email, not your real admin address. Replace with your own credentials ASAP.

---

## Support

- **EmailJS Documentation:** https://www.emailjs.com/docs/
- **EmailJS Support:** https://www.emailjs.com/support/
- **Video Tutorial:** Search "EmailJS tutorial" on YouTube

---

## Summary Checklist

- [ ] Created EmailJS account
- [ ] Added email service and saved Service ID
- [ ] Created email template and saved Template ID
- [ ] Got Public Key from account settings
- [ ] Updated contact.html with all three credentials
- [ ] Tested form and received email
- [ ] Added website domain to Allowed Domains in EmailJS dashboard (optional but recommended)

---

**Your contact form is now ready to receive submissions!** 🎉
