@echo off
REM ============================================
REM  SU Mentoring - Quick Setup Script
REM  Windows Batch Script
REM ============================================

echo.
echo ========================================
echo   SU MENTORING - SETUP WIZARD
echo ========================================
echo.

REM Check if Composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer is not installed!
    echo.
    echo Please install Composer first:
    echo 1. Visit: https://getcomposer.org/Composer-Setup.exe
    echo 2. Run the installer
    echo 3. Restart this script
    echo.
    pause
    exit /b 1
)

echo [OK] Composer is installed
composer --version
echo.

REM Check if vendor folder exists
if exist "vendor\autoload.php" (
    echo [OK] Dependencies already installed
    echo.
) else (
    echo [INFO] Installing dependencies...
    echo.
    composer install
    
    if %ERRORLEVEL% EQU 0 (
        echo.
        echo [SUCCESS] Dependencies installed successfully!
    ) else (
        echo.
        echo [ERROR] Failed to install dependencies
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo   CHECKING CONFIGURATION
echo ========================================
echo.

REM Check if .env.php exists
if exist "includes\.env.php" (
    echo [OK] Environment file exists
) else (
    echo [WARNING] Environment file not found
    
    if exist ".env.example.php" (
        echo.
        echo Creating includes\.env.php from example...
        copy .env.example.php includes\.env.php >nul
        
        if %ERRORLEVEL% EQU 0 (
            echo [SUCCESS] Environment file created
            echo.
            echo [ACTION REQUIRED] Edit includes\.env.php with your database credentials
        )
    ) else (
        echo [ERROR] .env.example.php not found
    )
)

echo.
echo ========================================
echo   SETUP COMPLETE
echo ========================================
echo.
echo Next steps:
echo 1. Update database credentials in: includes\.env.php
echo 2. Create MySQL database: mentoring_website
echo 3. Run: php setup_database.php
echo 4. Access project in browser
echo.
echo For detailed instructions, see SETUP.md
echo.
pause
