# 🗄️ Database Setup Guide - ARDY Real Estate Contact Form

## ✅ What's Now Included

Your contact form system now has **complete database integration**:

1. ✅ **MySQL Database Storage** - All submissions saved locally
2. ✅ **Admin Panel** - View and manage all submissions
3. ✅ **Email Sending** - PHP mail() function (no external API)
4. ✅ **Status Tracking** - Mark enquiries as New/Contacted/Closed
5. ✅ **Search & Filter** - Find submissions easily
6. ✅ **Automatic Backup** - Even if email fails, data is saved

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** ✅
3. Start **MySQL** ✅

### Step 2: Create Database
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **"Import"** tab
3. Click **"Choose File"** and select: `setup-database.sql`
4. Click **"Go"** button
5. Wait for success message ✅

### Step 3: Test Database Connection
1. Open: http://localhost/FINAL/ARDY/test-database.html
2. Click **"Test Database Connection"**
3. If successful, you're ready! ✅

### Step 4: Test Contact Form
1. Open: http://localhost/FINAL/ARDY/contact.html
2. Fill out the form and submit
3. Check browser console (F12) for success message
4. Data is now saved in database!

### Step 5: View Submissions in Admin Panel
1. Open: http://localhost/FINAL/ARDY/admin-panel.php
2. Password: `ardy2026` (change this!)
3. View all submissions, update status, search, filter

---

## 📁 New Files Created

| File | Purpose |
|------|---------|
| `db-config.php` | Database connection settings |
| `setup-database.sql` | Creates database and tables |
| `admin-panel.php` | View and manage submissions |
| `test-database.html` | Test database connection |
| `test-database.php` | Backend for database testing |

---

## 📊 Database Structure

### Table: `contact_submissions`
Stores all contact form submissions with:
- Name, Email, Phone
- Service Interest, Budget Range
- Message content
- IP Address, User Agent (for tracking)
- Email status (sent/failed)
- Status (new/contacted/closed)
- Timestamps (created/updated)

### Table: `admin_users`
For admin panel login

### Table: `activity_log`
Optional activity tracking

---

## 🔄 How It Works (Data Flow)

```
1. User fills contact form
          ↓
2. JavaScript sends data to send-mail.php
          ↓
3. PHP validates input
          ↓
4. PHP saves to MySQL database ✅
          ↓
5. PHP sends email to admin ✉️
          ↓
6. PHP sends auto-reply to client ✉️
          ↓
7. Updates email status in database
          ↓
8. Returns success/failure to user
```

**Important:** Even if email fails, data is still saved in database!

---

## 🎯 Admin Panel Features

### Login
- URL: `http://localhost/FINAL/ARDY/admin-panel.php`
- Default Password: `ardy2026` (change in admin-panel.php line 9)

### Dashboard
- Total submissions count
- New enquiries counter
- Contacted counter  
- Closed counter

### Submissions List
- View all contact submissions
- Filter by status (All/New/Contacted/Closed)
- Search by name, email, or phone
- See submission date/time
- View client message
- Check if email was sent successfully
- Update enquiry status with dropdown

### Actions
- Change status: New → Contacted → Closed
- Click email to send reply directly
- View full message on hover

---

## ⚙️ Configuration

### Database Settings
Edit `db-config.php`:
```php
define('DB_HOST', 'localhost');        // Host
define('DB_NAME', 'ardy_realestate');  // Database name
define('DB_USER', 'root');             // Username
define('DB_PASS', '');                 // Password (empty for XAMPP)
```

### Admin Password
Edit `admin-panel.php` line 9:
```php
$ADMIN_PASSWORD = 'ardy2026';  // Change this!
```

### Email Settings
Edit `send-mail.php`:
```php
define('ADMIN_EMAIL', 'your-email@ardyrealestatees.com');
define('FROM_DOMAIN', 'ardyrealestatees.com');
```

---

## 🧪 Testing Your Setup

### Test 1: Database Connection
```
Open: test-database.html
Click: "Test Database Connection"
Expected: ✅ Success message with tables list
```

### Test 2: Contact Form Submission
```
Open: contact.html
Fill: All required fields
Submit: Click "Send Message"
Expected: ✅ Success message
Check: admin-panel.php to see submission
```

### Test 3: Admin Panel
```
Open: admin-panel.php
Login: Password "ardy2026"
Expected: Dashboard with submission count
```

### Test 4: Email Sending
```
Submit form with your email
Check: Your inbox for auto-reply
Check: Admin email for notification
```

---

## 🔧 Troubleshooting

### Problem: "Database connection failed"
**Solutions:**
1. Make sure MySQL is running in XAMPP
2. Check database credentials in `db-config.php`
3. Verify database name is correct
4. Run `setup-database.sql` in phpMyAdmin

### Problem: "Database does not exist"
**Solution:**
1. Open phpMyAdmin
2. Click "Import"
3. Choose `setup-database.sql`
4. Click "Go"

### Problem: "Contact form submits but no data in admin panel"
**Check:**
1. Browser console (F12) for errors
2. Database connection test passes
3. Table `contact_submissions` exists
4. PHP error logs

### Problem: "Admin panel shows login but wrong password"
**Solution:**
1. Open `admin-panel.php`
2. Line 9: `$ADMIN_PASSWORD = 'ardy2026';`
3. Use this password or change it

### Problem: "Emails not sending"
**Note:** This is separate from database storage!
- Data is STILL saved in database
- See PHP-EMAIL-SETUP.md for email troubleshooting
- Test on live server for best results

---

## 📱 Admin Panel Preview

```
┌─────────────────────────────────────────────┐
│  🏢 ARDY Real Estate Admin Panel    [Logout]│
├─────────────────────────────────────────────┤
│                                             │
│  📊 STATS                                   │
│  Total: 15 | New: 7 | Contacted: 5 | Closed: 3
│                                             │
│  🔍 FILTERS & SEARCH                        │
│  [All] [New] [Contacted] [Closed] [Search…]│
│                                             │
│  📋 SUBMISSIONS TABLE                       │
│  ┌─────────────────────────────────────┐   │
│  │ ID | Date | Name | Contact | ...   │   │
│  ├─────────────────────────────────────┤   │
│  │ #15 | Mar 7 | John Smith | ...      │   │
│  │ #14 | Mar 6 | Sarah Ahmed | ...     │   │
│  │ #13 | Mar 6 | Mike Brown | ...      │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

---

## 🌐 Deploying to Live Server

### For cPanel Hosting:

1. **Upload Files:**
   - Upload all PHP files via File Manager or FTP
   - Keep same folder structure

2. **Create Database:**
   - Open cPanel → phpMyAdmin
   - Create new database: `ardy_realestate`
   - Import `setup-database.sql`

3. **Update Config:**
   - Edit `db-config.php` with cPanel database credentials
   - Usually format: `username_dbname`

4. **Update Permissions:**
   - Set PHP files to 644
   - Set folders to 755

5. **Test:**
   - Visit your-domain.com/test-database.html
   - Submit contact form
   - Check admin-panel.php

### For VPS/Dedicated Server:

1. Create MySQL database and user
2. Grant privileges: `GRANT ALL ON ardy_realestate.* TO 'user'@'localhost'`
3. Import SQL file
4. Update db-config.php
5. Test connection

---

## 🔐 Security Best Practices

### Before Going Live:

- [ ] Change admin password in `admin-panel.php`
- [ ] Use strong database password
- [ ] Limit database user privileges
- [ ] Add .htaccess protection to admin-panel.php
- [ ] Enable HTTPS on your domain
- [ ] Regular database backups
- [ ] Update FROM_DOMAIN to match your domain

### Recommended .htaccess for admin folder:
```apache
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

---

## 📊 Database Management

### Backup Database:
```sql
-- In phpMyAdmin, select database and click "Export"
-- Or via command line:
mysqldump -u root -p ardy_realestate > backup.sql
```

### View Recent Submissions (SQL):
```sql
SELECT * FROM contact_submissions 
ORDER BY created_at DESC 
LIMIT 10;
```

### Count by Status:
```sql
SELECT status, COUNT(*) as count 
FROM contact_submissions 
GROUP BY status;
```

### Export to CSV:
Use phpMyAdmin → Export → CSV format

---

## 📈 Future Enhancements

Want to add more features? Consider:
- Email notifications when new submission arrives
- Export submissions to Excel/PDF
- Advanced analytics dashboard
- Multiple admin users with roles
- Email templates editor
- Automated follow-up reminders
- Integration with CRM systems

---

## ✅ Quick Links

| Page | URL | Purpose |
|------|-----|---------|
| Contact Form | [contact.html](contact.html) | Public contact form |
| Admin Panel | [admin-panel.php](admin-panel.php) | View submissions |
| Test Database | [test-database.html](test-database.html) | Test connection |
| phpMyAdmin | http://localhost/phpmyadmin | Database management |

---

## 🎉 You're Done!

Your contact form now has:
- ✅ Database storage (MySQL)
- ✅ Admin panel
- ✅ Email sending (no external API)
- ✅ Status tracking
- ✅ Search & filter
- ✅ Automatic backups

**No external APIs used** - Everything runs on your server using PHP and MySQL!

---

**System:** Pure PHP + MySQL (no libraries)  
**Hosting:** Works on XAMPP, cPanel, VPS, Dedicated servers  
**No Dependencies:** No external APIs needed  
**Built For:** ARDY Real Estate Dubai  
**Last Updated:** March 2026
