# SU Mentoring Platform

A comprehensive web-based mentoring system that connects mentors and mentees, facilitating meaningful professional relationships and growth opportunities.

## 🚀 Features

- **User Management**: Separate registration and profiles for mentors and mentees
- **Mentorship Matching**: Connect mentors with mentees based on skills and interests
- **Session Scheduling**: Interactive calendar for booking and managing mentoring sessions
- **Real-time Messaging**: Communication system between mentors and mentees
- **Goal Tracking**: Set and monitor mentoring objectives and progress
- **Resource Sharing**: Share documents, links, and learning materials
- **Notifications**: Email and in-app notifications for important events
- **Analytics Dashboard**: Track mentoring activities and outcomes

## 🛡️ Security Features

- **Secure Configuration**: Environment-based configuration management
- **Password Protection**: Strong password hashing and validation
- **Session Security**: Secure session management with CSRF protection
- **Data Sanitization**: Input validation and XSS prevention
- **Email Verification**: Account verification via email
- **Activity Logging**: Comprehensive audit trail
- **Two-Factor Authentication**: Optional 2FA support

## 📋 Requirements

- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx
- **Extensions**: PDO, MySQLi, cURL, OpenSSL, mbstring
- **Optional**: Composer for dependency management

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Shone-Kahuria/SU_mentoring.git
cd SU_mentoring
```

### 2. Install Dependencies

**This is required!** The project uses Composer for dependency management (PHPMailer).

```bash
# Install Composer if you don't have it
# Visit: https://getcomposer.org/download/

# Install project dependencies
composer install

# For production (optimized autoloader)
composer install --no-dev --optimize-autoloader
```

**Windows Users**: If you get "composer not recognized" error:
1. Download and install Composer from https://getcomposer.org/Composer-Setup.exe
2. Restart your terminal/command prompt
3. Run `composer install` from the project directory

### 3. Set Up Environment Configuration

**IMPORTANT**: Never commit your actual credentials!

```bash
# Copy the example environment file
cp .env.example.php includes/.env.php

# Edit the environment file with your actual credentials
# Update database credentials, email settings, and API keys
nano includes/.env.php
```

### 3. Configure Database

1. Create a new MySQL/MariaDB database
2. Update your database credentials in `includes/.env.php`
3. Run the database setup script:

```bash
php setup_database.php
```

Or manually import the schema:

```bash
mysql -u username -p mentoring_website < database_schema.sql
```

### 4. Set Up Web Server

#### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName mentoring.local
    DocumentRoot /path/to/SU_mentoring
    
    <Directory /path/to/SU_mentoring>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name mentoring.local;
    root /path/to/SU_mentoring;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
}
```

### 5. Set File Permissions

```bash
# Make sure web server can read files
chmod -R 644 *
chmod -R 755 */

# Protect sensitive files
chmod 600 includes/.env.php
chmod 700 logs/
chmod 700 backups/
```

### 6. Run Security Setup (Optional)

```bash
php setup_security.php
```

This script will:
- Check for credentials in your code
- Set up secure environment configuration
- Create git hooks to prevent credential commits
- Provide security recommendations

## 🔐 Security Best Practices

### Environment Configuration

1. **Never commit `.env.php`** - It contains your actual credentials
2. **Use strong passwords** - Minimum 12 characters with mixed case, numbers, and symbols
3. **Generate secure keys** - Use the provided key generator or OpenSSL
4. **Limit database permissions** - Create a dedicated database user with minimal required permissions

### File Security

```bash
# Files that should NEVER be committed:
includes/.env.php
config/local.php
*.log files
uploads/
backups/
```

### Production Checklist

- [ ] Set `APP_ENV` to `production`
- [ ] Set `APP_DEBUG` to `false`
- [ ] Enable HTTPS and update `APP_URL`
- [ ] Configure proper error logging
- [ ] Set up automated backups
- [ ] Enable firewall rules
- [ ] Update default passwords
- [ ] Configure email settings
- [ ] Set up monitoring and alerts

## 🗄️ Database Schema

The system uses a comprehensive database schema with the following main tables:

- **users**: User accounts and profiles
- **mentorships**: Mentor-mentee relationships
- **sessions**: Scheduled mentoring sessions
- **messages**: Communication between users
- **goals**: Mentoring objectives and progress
- **resources**: Shared files and materials
- **notifications**: System notifications
- **activity_logs**: Audit trail

See [`database_schema.sql`](database_schema.sql) for complete structure.

## 📡 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/register` - User registration
- `POST /api/auth/verify` - Email verification
- `POST /api/auth/reset-password` - Password reset

### Sessions
- `GET /api/sessions` - List user sessions
- `POST /api/sessions` - Create new session
- `PUT /api/sessions/{id}` - Update session
- `DELETE /api/sessions/{id}` - Cancel session

### Messages
- `GET /api/messages/{mentorship_id}` - Get conversation
- `POST /api/messages` - Send message
- `PUT /api/messages/{id}/read` - Mark as read

## 🔧 Configuration Options

### Email Settings

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

### Security Settings

```php
define('APP_SECRET_KEY', 'your-32-character-secret-key');
define('JWT_SECRET', 'your-jwt-secret-key');
define('SESSION_COOKIE_SECURE', true); // For HTTPS
define('SESSION_COOKIE_HTTPONLY', true);
```

### File Upload Settings

```php
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx');
```

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `.env.php`
   - Ensure database server is running
   - Verify user permissions

2. **Email Not Sending**
   - Check SMTP settings in `.env.php`
   - Verify email account app passwords
   - Check firewall settings

3. **Session Issues**
   - Ensure `session.save_path` is writable
   - Check session configuration
   - Clear browser cookies

4. **File Upload Errors**
   - Check PHP `upload_max_filesize` setting
   - Verify directory permissions
   - Ensure sufficient disk space

### Debug Mode

Enable debug mode in development:

```php
define('APP_DEBUG', true);
define('APP_ENV', 'development');
```

This will show detailed error messages and enable debugging features.

## 📝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes and test thoroughly
4. Ensure no credentials are committed
5. Submit a pull request

### Code Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write unit tests for new features
- Update documentation as needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs via GitHub Issues
- **Email**: Contact the development team
- **Community**: Join our discussion forums

## 🔄 Updates

Stay updated with the latest changes:

```bash
git pull origin master
php setup_database.php # If database schema changed
```

Remember to backup your `.env.php` file before updating!

---

**⚠️ Security Notice**: This application handles sensitive user data. Always follow security best practices, keep dependencies updated, and regularly review your configuration for security vulnerabilities.