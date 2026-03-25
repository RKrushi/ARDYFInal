# 📧 Email Configuration Guide

## Problem
XAMPP doesn't have a built-in mail server, so PHP's `mail()` function fails. Your form data **is being saved to the database** ✅, but emails are not sending ❌.

## Solution Options

### **Option 1: Use Gmail SMTP (Recommended)** ⭐

I've created `send-mail-smtp.php` which uses Gmail's SMTP server.

#### Steps to Enable:

1. **Get Gmail App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Sign in to: `rkontam0@gmail.com`
   - Select "Mail" and "Windows Computer"  
   - Click "Generate"
   - Copy the 16-character password (format: `xxxx xxxx xxxx xxxx`)

2. **Configure the file:**
   - Open `send-mail-smtp.php`
   - Find line 18: `define('SMTP_PASS', '');`
   - Paste your app password: `define('SMTP_PASS', 'yourapppasswordhere');`
   - Remove spaces if any

3. **Update contact.html:**
   - Open `contact.html`
   - Find line 623: `fetch('send-mail.php', {`
   - Change to: `fetch('send-mail-smtp.php', {`

4. **Test the form!**

---

### **Option 2: Use Current Setup (For Testing Only)** 

Your current setup works perfectly for **saving data to database**. Emails will show as "FAILED" but all enquiries are captured in the admin panel.

**This is acceptable if:**
- You check the admin panel regularly
- You don't need email notifications
- You're just testing the system

---

### **Option 3: Install PHPMailer (Advanced)**

Use Composer to install PHPMailer library:

```bash
cd C:\xampp\htdocs\FINAL\ARDY
composer require phpmailer/phpmailer
```

Then I can create a version using PHPMailer for more robust email handling.

---

## Current Status

✅ **Working:**
- Contact form submission
- Data validation
- Database storage
- Admin panel display

❌ **Not Working:**
- Email notifications (due to missing SMTP config)

## Recommended Next Steps

1. Use **Option 1** (Gmail SMTP) for production
2. Or accept **Option 2** for now and check admin panel manually
3. Contact me if you need help with Option 3

---

## Quick Test Commands

Check database entries:
```bash
C:\xampp\mysql\bin\mysql.exe -u root ardy -e "SELECT id, name, email, phone, email_sent, created_at FROM contact_submissions ORDER BY id DESC LIMIT 5;"
```

Check if Apache & MySQL are running:
```bash
C:\xampp\xampp-control.exe
```
