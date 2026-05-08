# JobYaari - Blog Management System

A full-featured blog management system built with PHP + MySQL + jQuery/AJAX.

## 🔑 Admin Credentials
- **URL:** `/admin/`
- **Username:** `admin`
- **Password:** `Admin@123`

## 🚀 Setup Instructions

### 1. Database Setup
- Create a MySQL database
- Import `database/schema.sql`
- Edit `config.php` with your DB credentials

### 2. File Upload
- Upload all files to your server/hosting
- Ensure `public/images/uploads/` folder is writable: `chmod 755 public/images/uploads/`

### 3. Configuration
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'blog_system');
define('SITE_URL', 'https://your-domain.com');
```

## ✨ Features

**Frontend:**
- Responsive blog listing page
- AJAX filter by category (no page reload)
- AJAX search with live results
- Date filter
- Blog detail page with sidebar
- WhatsApp & Twitter share
- Live news ticker
- Mobile-first responsive design

**Admin Panel:**
- Secure login system
- Dashboard with stats
- Add/Edit/Delete blogs
- Quill rich text editor (bold, italic, headings, tables, bullets, images, links)
- Image upload with preview
- Category management
- Draft/Published status
- Mobile responsive admin

## 🛠️ Tech Stack
- PHP 7.4+ (Core PHP, no framework needed)
- MySQL / MariaDB
- HTML5 + CSS3 (fully responsive)
- jQuery 3.7 + AJAX
- Quill.js Rich Text Editor
- Google Fonts

## 🌐 Free Hosting Options
- InfinityFree (infinityfree.net)
- 000webhost
- Render.com
- Railway.app

## 📁 Project Structure
```
blog-system/
├── config.php          # Database config
├── admin/              # Admin panel
│   ├── index.php       # Dashboard
│   ├── login.php       # Admin login
│   ├── blogs.php       # All blogs list
│   ├── add-blog.php    # Add new blog
│   ├── edit-blog.php   # Edit blog
│   ├── delete-blog.php # Delete blog
│   ├── logout.php
│   ├── css/admin.css
│   ├── js/admin.js
│   └── includes/       # Sidebar, topbar
├── public/             # Frontend
│   ├── index.php       # Blog listing
│   ├── blog.php        # Blog detail
│   ├── ajax.php        # AJAX handler
│   ├── css/style.css
│   ├── js/main.js
│   └── images/uploads/ # Uploaded images
└── database/
    └── schema.sql      # Database setup
```
