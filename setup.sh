#!/bin/bash
# ============================================
#  SU Mentoring - Quick Setup Script
#  Unix/Linux/macOS Shell Script
# ============================================

echo ""
echo "========================================"
echo "   SU MENTORING - SETUP WIZARD"
echo "========================================"
echo ""

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "[ERROR] Composer is not installed!"
    echo ""
    echo "Please install Composer first:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    echo ""
    echo "Or visit: https://getcomposer.org/download/"
    echo ""
    exit 1
fi

echo "[OK] Composer is installed"
composer --version
echo ""

# Check if vendor folder exists
if [ -f "vendor/autoload.php" ]; then
    echo "[OK] Dependencies already installed"
    echo ""
else
    echo "[INFO] Installing dependencies..."
    echo ""
    composer install
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "[SUCCESS] Dependencies installed successfully!"
    else
        echo ""
        echo "[ERROR] Failed to install dependencies"
        exit 1
    fi
fi

echo ""
echo "========================================"
echo "   CHECKING CONFIGURATION"
echo "========================================"
echo ""

# Check if .env.php exists
if [ -f "includes/.env.php" ]; then
    echo "[OK] Environment file exists"
else
    echo "[WARNING] Environment file not found"
    
    if [ -f ".env.example.php" ]; then
        echo ""
        echo "Creating includes/.env.php from example..."
        cp .env.example.php includes/.env.php
        
        if [ $? -eq 0 ]; then
            echo "[SUCCESS] Environment file created"
            echo ""
            echo "[ACTION REQUIRED] Edit includes/.env.php with your database credentials"
        fi
    else
        echo "[ERROR] .env.example.php not found"
    fi
fi

echo ""
echo "========================================"
echo "   SETUP COMPLETE"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Update database credentials in: includes/.env.php"
echo "2. Create MySQL database: mentoring_website"
echo "3. Run: php setup_database.php"
echo "4. Access project in browser"
echo ""
echo "For detailed instructions, see SETUP.md"
echo ""
