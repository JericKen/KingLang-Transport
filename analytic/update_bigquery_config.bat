@echo off
REM ==========================================
REM BigQuery Configuration Updater (Windows)
REM ==========================================

echo.
echo ========================================
echo  BigQuery Configuration Updater
echo ========================================
echo.

REM Check if Node.js is installed
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Node.js is not installed!
    echo Please install Node.js from https://nodejs.org/
    pause
    exit /b 1
)

REM Run the update script
echo Running configuration update...
echo.
node update_bigquery_config.js

echo.
echo ========================================
echo  Update Complete!
echo ========================================
echo.
pause

