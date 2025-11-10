-- Create database user with proper permissions
CREATE USER IF NOT EXISTS 'mentoring_user'@'localhost' IDENTIFIED BY 'mentoring_pass_123';
GRANT ALL PRIVILEGES ON mentoring_website.* TO 'mentoring_user'@'localhost';
FLUSH PRIVILEGES;