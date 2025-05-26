# EYGII - Eloquent Youth & Global Integrity

> **"Reviving world integrity and moral values"**

A modern, responsive website for Eloquent Youth & Global Integrity (EYGII), an NGO dedicated to empowering young people and promoting integrity and moral values in communities across Nigeria and beyond.

![EYGII Website](https://img.shields.io/badge/Status-Active-brightgreen)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)

## 🌟 Features

### 🎨 Modern Design
- **Glassmorphism UI** - Contemporary design with translucent elements
- **Responsive Layout** - Mobile-first design that works on all devices
- **Smooth Animations** - AOS (Animate On Scroll) library integration
- **Modern Typography** - Google Fonts (Inter + Playfair Display)
- **Professional Color Scheme** - Blue, gold, and neutral palette

### 🚀 Frontend Features
- **Interactive Hero Section** - Full-height hero with animated elements
- **Statistics Counter** - Animated counters showing impact metrics
- **Program Showcase** - Dynamic program cards with hover effects
- **Newsletter Subscription** - Email collection with validation
- **Contact Forms** - Multiple contact and volunteer forms
- **News & Blog System** - Dynamic content management
- **Donation Integration** - Bank details and donation information

### ⚙️ Backend Features
- **Admin Panel** - Comprehensive content management system
- **User Management** - Admin authentication and role management
- **Content Management** - CRUD operations for all content types
- **Database Integration** - MySQL with prepared statements
- **File Upload System** - Secure image upload for programs and news
- **Email System** - SMTP integration for notifications
- **Export Functionality** - CSV exports for all data types

### 🔐 Security Features
- **SQL Injection Protection** - Prepared statements throughout
- **XSS Prevention** - Proper output escaping
- **Session Management** - Secure admin authentication
- **File Upload Security** - Extension validation and secure handling
- **CSRF Protection** - Form validation and security tokens

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with custom properties
- **JavaScript (ES6+)** - Interactive functionality
- **Bootstrap 5.3.2** - Responsive framework
- **Font Awesome 6.4.0** - Icon library
- **AOS 2.3.1** - Animation library
- **Google Fonts** - Typography

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL 8.0+** - Database management
- **PDO** - Database abstraction layer

### Development Tools
- **XAMPP** - Local development environment
- **Git** - Version control
- **Composer** - Dependency management (optional)

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP** (or similar LAMP/WAMP stack)
  - PHP 8.0 or higher
  - MySQL 8.0 or higher
  - Apache Web Server
- **Git** (for version control)
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/UfereSamuel/eygii-foundation.git
cd eygii-foundation
```

### 2. Setup Local Environment

1. **Start XAMPP Services**
   ```bash
   # Start Apache and MySQL services
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

2. **Move Project to Web Directory**
   ```bash
   # Copy project to XAMPP htdocs
   cp -r eygii-foundation /Applications/XAMPP/xamppfiles/htdocs/eygii
   ```

### 3. Database Setup

1. **Create Database**
   ```bash
   # Access MySQL
   /Applications/XAMPP/xamppfiles/bin/mysql -u root
   
   # Create database
   CREATE DATABASE eygii CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   exit;
   ```

2. **Import Database Schema**
   ```bash
   # Import the database structure and sample data
   /Applications/XAMPP/xamppfiles/bin/mysql -u root eygii < setup/database.sql
   ```

### 4. Configuration

1. **Database Configuration**
   - Edit `config/database.php` if needed
   - Default settings work with standard XAMPP installation

2. **Email Configuration** (Optional)
   - Edit `config/email.php` for SMTP settings
   - Configure for production email sending

### 5. Initialize Admin User

1. **Run Admin Setup**
   ```bash
   # Navigate to setup URL
   http://localhost/eygii/setup/init_admin.php
   ```

2. **Default Admin Credentials**
   - **Username:** `admin`
   - **Email:** `eygii2017@gmail.com`
   - **Password:** `admin123` (Change immediately!)

### 6. Access the Website

- **Frontend:** `http://localhost/eygii/`
- **Admin Panel:** `http://localhost/eygii/admin/`

## 📁 Project Structure

```
eygii/
├── admin/                  # Admin panel
│   ├── api/               # API endpoints
│   ├── includes/          # Admin templates
│   ├── pages/             # Admin pages
│   └── dashboard.php      # Admin dashboard
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Images and media
├── config/               # Configuration files
│   ├── database.php      # Database configuration
│   └── email.php         # Email configuration
├── handlers/             # Form handlers
├── includes/             # Shared templates
│   ├── header.php        # Site header
│   └── footer.php        # Site footer
├── setup/                # Setup and initialization
│   ├── database.sql      # Database schema
│   └── init_admin.php    # Admin initialization
├── index.php             # Homepage
├── about.php             # About page
├── programs.php          # Programs page
├── news.php              # News page
├── contact.php           # Contact page
├── donate.php            # Donation page
├── get-involved.php      # Volunteer page
└── README.md             # This file
```

## 🎯 Usage

### Frontend Features

1. **Homepage**
   - Hero section with organization introduction
   - Mission statement and core values
   - Statistics showcase
   - Program previews
   - Newsletter subscription

2. **Programs Page**
   - Dynamic program listings
   - Detailed program information
   - Registration/inquiry forms

3. **News & Blog**
   - Latest news and updates
   - Blog posts and articles
   - Category filtering

4. **Contact & Volunteer**
   - Contact forms
   - Volunteer application
   - Organization information

### Admin Panel Features

1. **Dashboard**
   - Overview statistics
   - Recent activities
   - Quick actions

2. **Content Management**
   - Programs CRUD operations
   - News article management
   - Image upload and management

3. **Community Management**
   - Contact message handling
   - Volunteer application review
   - Newsletter subscriber management

4. **System Settings**
   - Site configuration
   - User management
   - System information

## 🔧 Configuration

### Database Configuration

Edit `config/database.php`:

```php
return [
    'host' => 'localhost',
    'dbname' => 'eygii',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```

### Email Configuration

Edit `config/email.php`:

```php
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',
    'from_email' => 'eygii2017@gmail.com',
    'from_name' => 'EYGII'
];
```

## 🚀 Deployment

### Production Deployment

1. **Server Requirements**
   - PHP 8.0+ with extensions: PDO, MySQL, GD, mbstring
   - MySQL 8.0+ or MariaDB 10.3+
   - Apache/Nginx web server
   - SSL certificate (recommended)

2. **Environment Setup**
   ```bash
   # Upload files to server
   # Update database configuration
   # Set proper file permissions
   chmod 755 assets/images/
   chmod 644 config/*.php
   ```

3. **Security Checklist**
   - [ ] Change default admin password
   - [ ] Update database credentials
   - [ ] Configure SMTP settings
   - [ ] Enable HTTPS
   - [ ] Set up regular backups
   - [ ] Configure firewall rules

## 🤝 Contributing

We welcome contributions to improve the EYGII website! Here's how you can help:

### Getting Started

1. **Fork the Repository**
2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make Your Changes**
4. **Commit Your Changes**
   ```bash
   git commit -m 'Add some amazing feature'
   ```
5. **Push to the Branch**
   ```bash
   git push origin feature/amazing-feature
   ```
6. **Open a Pull Request**

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use semantic HTML5 markup
- Write responsive CSS with mobile-first approach
- Test on multiple browsers and devices
- Document new features and changes

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Contact & Support

### EYGII Organization
- **Email:** eygii2017@gmail.com
- **Phone:** +234 813 661 3616 / +234 805 482 4514
- **Address:** K19, Joke Plaza, Bodija, Ibadan, Nigeria

### Technical Support
For technical issues or questions about the website:
- Create an issue on GitHub
- Contact the development team
- Check the documentation

## 🙏 Acknowledgments

- **Bootstrap Team** - For the excellent CSS framework
- **Font Awesome** - For the comprehensive icon library
- **AOS Library** - For smooth scroll animations
- **Google Fonts** - For beautiful typography
- **EYGII Team** - For their dedication to youth empowerment

## 📊 Project Status

- ✅ **Frontend Development** - Complete
- ✅ **Backend Development** - Complete
- ✅ **Admin Panel** - Complete
- ✅ **Database Design** - Complete
- ✅ **Security Implementation** - Complete
- ✅ **Testing** - Complete
- 🔄 **Documentation** - In Progress
- ⏳ **Production Deployment** - Pending

---

**Built with ❤️ for youth empowerment and global integrity**

*Last updated: December 2024* 