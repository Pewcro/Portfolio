# Portfolio Website — Jonathan Parulian Tobing

A dynamic, modern portfolio website built with **PHP + MySQL (XAMPP)** featuring a secure Admin CMS to manage all content without touching code.

## ✨ Features
- Elegant dark space-themed design
- Dynamic content — profile, skills, and projects all from a database
- Secure Admin CMS dashboard (login protected)
- Direct photo upload from your computer (profile & project images)
- Change password from the dashboard
- Fully responsive (mobile-friendly)

## 🚀 Local Setup (XAMPP)

### Requirements
- [XAMPP](https://www.apachefriends.org/) with Apache & MySQL running

### Steps
1. **Clone this repo** into your XAMPP htdocs folder:
   ```bash
   git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git C:/xampp/htdocs/portfolio
   ```

2. **Import the database:**
   Open your browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database named `portfolio_db`
   - Click **Import** → select the `setup.sql` file → click **Go**

3. **Set up the admin account** by visiting:
   ```
   http://localhost/portfolio/setup_admin.php
   ```
   Then **delete** `setup_admin.php` immediately after.

4. **Open the website:**
   ```
   http://localhost/portfolio/
   ```

5. **Admin Dashboard:**
   ```
   http://localhost/portfolio/admin_login.php
   ```
   Default: `admin` / `admin123` — **change immediately after first login!**

## 📁 File Structure
```
portfolio/
├── index.php           # Main portfolio page
├── admin_login.php     # Admin login (secure, rate-limited)
├── admin_dashboard.php # Full CMS dashboard
├── logout.php          # Session logout
├── db.php              # Database connection (gitignored)
├── setup.sql           # Database schema & seed data
└── uploads/            # Uploaded images (gitignored)
    ├── profile/
    └── projects/
```

## 🔐 Security Features
- CSRF token protection on all forms
- Rate limiting (5 failed login attempts → 10 min lockout)
- Session fixation prevention
- MIME-type validated file uploads (not just extension)
- PHP script execution blocked in uploads folder
- All inputs sanitized via prepared statements

## 🛠️ Tech Stack
- **Frontend:** HTML5, Tailwind CSS, JavaScript
- **Backend:** PHP 8+
- **Database:** MySQL (via XAMPP)
- **Libraries:** Typed.js, Vanilla-Tilt.js, Font Awesome
