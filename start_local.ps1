# FASEEH LOCAL STARTUP SCRIPT
# This script prepares the database and starts the PHP server.

$DB_NAME = "faseeh_db"
$SQL_FILE = "clean_database.sql"

Write-Host "--- 🚀 Faseeh Academy Local Setup ---" -ForegroundColor Cyan

# 1. Check for MariaDB/MySQL
if (!(Get-Command mysql -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Error: MySQL/MariaDB not found in PATH." -ForegroundColor Red
    Write-Host "Please make sure MariaDB is installed and added to your environment variables."
    exit
}

# 2. Setup Database
Write-Host "📦 Setting up database: $DB_NAME..." -ForegroundColor Yellow
mysql -u root -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>$null

if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️ Warning: Could not create database. It might already exist or require a password." -ForegroundColor Yellow
}

Write-Host "📥 Importing schema from $SQL_FILE..." -ForegroundColor Yellow
mysql -u root $DB_NAME < $SQL_FILE

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database ready!" -ForegroundColor Green
} else {
    Write-Host "❌ Database import failed. Check your MySQL credentials." -ForegroundColor Red
}

# 3. Start PHP Server
Write-Host "🌐 Starting local server at http://localhost:8000" -ForegroundColor Cyan
Write-Host "Press Ctrl+C to stop." -ForegroundColor Gray
php -S localhost:8000 router.php
