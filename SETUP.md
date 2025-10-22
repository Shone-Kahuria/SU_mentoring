# 🚀 Quick Setup Guide for Team Members

## ⚠️ Getting "vendor/autoload.php" Error?

If you're seeing this error:
```
Warning: require_once(vendor/autoload.php): Failed to open stream: No such file or directory
```

**You need to install Composer dependencies!** Follow the steps below.

---

## 📦 Step 1: Install Composer

### Windows
1. Download Composer installer: https://getcomposer.org/Composer-Setup.exe
2. Run the installer and follow the prompts
3. Restart your terminal/command prompt
4. Verify installation: `composer --version`

### macOS/Linux
```bash
# Download and install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 📥 Step 2: Install Project Dependencies

Open terminal/command prompt in the project directory and run:

```bash
# Navigate to project directory
cd C:\Apache24\htdocs\SU_mentoring

# Install all dependencies (PHPMailer, etc.)
composer install
```

**Expected Output:**
```
Loading composer repositories with package information
Installing dependencies from lock file (including require-dev)
Package operations: X installs, 0 updates, 0 removals
  - Installing phpmailer/phpmailer (v6.9.x)
...
Generating autoload files
```

---

## ✅ Step 3: Verify Installation

After running `composer install`, you should see:

1. ✅ A `vendor/` folder created in the project root
2. ✅ File `vendor/autoload.php` exists
3. ✅ Folder `vendor/phpmailer/phpmailer/` exists

**Quick Test:**
```bash
# Windows PowerShell
Test-Path vendor\autoload.php

# Windows CMD / Linux / macOS
ls vendor/autoload.php
```

If the file exists, you're good to go! 🎉

---

## 🔧 Step 4: Configure Environment (First Time Only)

If this is your first time setting up the project:

### 1. Create Database
```sql
CREATE DATABASE mentoring_website;
```

### 2. Copy Environment File
```bash
# Windows PowerShell
Copy-Item .env.example.php includes\.env.php

# macOS/Linux
cp .env.example.php includes/.env.php
```

### 3. Edit `includes/.env.php` with your credentials:
- Database name, username, password
- Email SMTP settings (Gmail, etc.)
- Any API keys

### 4. Run Database Setup
```bash
php setup_database.php
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "composer: command not found"
**Solution:** Composer is not installed or not in your PATH
- Reinstall Composer and make sure to check "Add to PATH" during installation
- Restart your terminal after installation

### Issue 2: "Your requirements could not be resolved"
**Solution:** PHP version mismatch
```bash
# Check your PHP version
php --version

# Project requires PHP 7.4 or higher
# Update PHP if needed
```

### Issue 3: "Failed to download phpmailer/phpmailer"
**Solution:** Network or authentication issue
```bash
# Clear Composer cache
composer clear-cache

# Try again
composer install
```

### Issue 4: "vendor folder still missing"
**Solution:** Run composer install from the correct directory
```bash
# Make sure you're in the project root
pwd  # or cd on Windows

# Should show: .../SU_mentoring
# Then run
composer install
```

---

## 📝 For Future Updates

When you pull new changes from Git:

```bash
# Pull latest code
git pull

# Update dependencies (if composer.json changed)
composer install

# Update database (if schema changed)
php setup_database.php
```

---

## 🤝 Need Help?

1. **Check if vendor folder exists:** `ls vendor/` or `dir vendor\`
2. **Verify Composer is installed:** `composer --version`
3. **Check PHP version:** `php --version` (needs 7.4+)
4. **Read error messages carefully** - they usually tell you what's missing

If you're still stuck, contact the team lead or check the main README.md for more details.

---

## 🎯 Quick Checklist

- [ ] Composer installed (`composer --version` works)
- [ ] Ran `composer install` in project directory
- [ ] `vendor/autoload.php` file exists
- [ ] Copied `.env.example.php` to `includes/.env.php`
- [ ] Updated database credentials in `includes/.env.php`
- [ ] Ran `php setup_database.php`
- [ ] Project loads without "vendor/autoload.php" error

**Once all checked, you're ready to code!** 🚀
