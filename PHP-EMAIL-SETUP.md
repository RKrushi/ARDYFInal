# PHP Contact Form Email Setup Guide

## ✨ Features Implemented

✅ **Professional HTML Email Templates** - Beautiful styled emails for both admin and client
✅ **Auto-Reply System** - Automatic confirmation email sent to clients
✅ **Spam Protection** - Honeypot field to block bots
✅ **Form Validation** - Client-side and server-side validation
✅ **Multipart Emails** - HTML + Plain text fallback
✅ **Mobile Responsive** - Emails look great on all devices

---

## 🔧 Configuration

### 1. Update Admin Email Address
Edit `send-mail.php` (line 10):
```php
define('ADMIN_EMAIL', 'your-admin-email@ardyrealestatees.com');
```

### 2. Update Domain Name
Edit `send-mail.php` (line 11):
```php
define('FROM_DOMAIN', 'ardyrealestatees.com');
```
**Important:** This should match your actual domain name.

---

## 📧 Email Templates

### Admin Email Template
When a contact form is submitted, the admin receives a **beautifully formatted HTML email** containing:
- Client name, email, phone
- Service interest selection
- Budget range selection
- Client's message
- Quick reply button
- Professional branding

### Client Auto-Reply Template
The client automatically receives a **confirmation email** with:
- Personalized greeting
- Enquiry summary
- Your contact information
- WhatsApp quick link
- Professional design matching your brand

---

## 🧪 Testing the Contact Form

### Test on XAMPP (Local)

1. **Start Apache** in XAMPP Control Panel

2. **Enable PHP mail() function**:
   - Open `C:\xampp\php\php.ini`
   - Find `[mail function]` section
   - Configure SMTP settings:
     ```ini
     [mail function]
     SMTP=smtp.gmail.com
     smtp_port=587
     sendmail_from=your-email@gmail.com
     ```
   - Or use a local mail server like **Mercury** (included with XAMPP)

3. **Open the contact form**:
   ```
   http://localhost/FINAL/ARDY/contact.html
   ```

4. **Fill out the form** and submit

5. **Check browser console** (F12) for success/error messages

### Test on Live Server

1. Upload all files to your web hosting
2. Ensure `send-mail.php` has correct permissions (644 or 755)
3. Test by submitting the form
4. Check spam folder if emails don't arrive

---

## 🚨 Troubleshooting

### Emails Not Sending?

**Check PHP mail() function:**
```php
// Create test.php in same folder
<?php
$to = 'your-email@example.com';
$subject = 'Test Email';
$message = 'This is a test email from PHP mail()';
$headers = 'From: noreply@yourdomain.com';

if(mail($to, $subject, $message, $headers)) {
    echo 'Email sent successfully';
} else {
    echo 'Email failed to send';
}
?>
```

**Common Issues:**

1. **XAMPP Local Testing**: PHP `mail()` doesn't work by default on XAMPP
   - Solution: Configure SMTP or use PHPMailer library
   - Or test on live server instead

2. **Emails Going to Spam**:
   - Ensure `FROM_DOMAIN` matches your actual domain
   - Add SPF, DKIM records to your domain DNS
   - Use authenticated SMTP (not just mail() function)

3. **403/500 Errors**:
   - Check file permissions on `send-mail.php`
   - Ensure PHP is enabled on your server
   - Check server error logs

4. **CORS Errors**:
   - If form and PHP are on different domains, update `ALLOWED_ORIGIN` in send-mail.php

---

## 🎨 Customizing Email Templates

### Change Email Colors
Edit the inline styles in `send-mail.php`:
- Background: `#3d2a23` (dark brown)
- Gold accent: `#c4b693`
- Light background: `#f7f5f4`

### Change Auto-Reply Message
Edit the `sendAutoReply()` function in `send-mail.php` around line 195

### Remove Auto-Reply
Comment out this line in `send-mail.php`:
```php
// sendAutoReply($name, $email, $service, $budget, $message);
```

---

## 📋 Form Fields

The contact form collects:
- **Name** (required, min 2 characters)
- **Email** (required, valid format)
- **Phone** (required, min 6 digits)
- **Service Interest** (dropdown)
- **Budget Range** (dropdown)
- **Message** (optional, max 1000 characters)

---

## 🔐 Security Features

✅ **Input Sanitization** - All user inputs are cleaned with `htmlspecialchars()`
✅ **Honeypot Field** - Hidden field to catch bots
✅ **POST-only Submission** - Rejects GET requests
✅ **Email Validation** - Server-side validation
✅ **XSS Protection** - All output is escaped
✅ **CORS Control** - Optional origin restrictions

---

## 🌐 Using on Live Server

### For cPanel Hosting:
1. Upload files via File Manager or FTP
2. No changes needed - `mail()` works automatically
3. Test immediately

### For Other Hosting:
1. Check if `mail()` function is enabled
2. If not, use SMTP with PHPMailer library
3. Contact hosting support if issues persist

---

## 📞 Support

If emails still don't send, consider using:
- **PHPMailer** library with SMTP authentication
- **SendGrid**, **Mailgun**, or **Amazon SES** - Email API services
- Contact your hosting provider to enable PHP mail()

---

## ✅ What's Working Now

✔️ Form validation (client-side)
✔️ Form validation (server-side)  
✔️ Professional HTML email templates
✔️ Auto-reply to clients
✔️ Spam protection (honeypot)
✔️ Error handling
✔️ Success/failure messages
✔️ Mobile-responsive emails

**Ready to use!** Just update the email address in config and test on your live server.
