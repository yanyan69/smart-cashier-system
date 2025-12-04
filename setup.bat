@echo off
setlocal enabledelayedexpansion

:: Configuration - Edit these paths as needed
set XAMPP_PATH=C:\xampp
set HTDOCS_PATH=%XAMPP_PATH%\htdocs\smart-cashier-system
set REPO_URL=https://github.com/yanyan69/smart-cashier-system.git
set DB_NAME=cashier_db
set DB_USER=root
set DB_PASS=                   :: Leave empty if no password, or put your password here
set SQL_FILE=database\database.sql

:: Set password option correctly
set PASS_OPT=
if not "%DB_PASS%"=="" set PASS_OPT=-p%DB_PASS%

:: Step 1: Clone or update the project + AUTO CD INTO THE FOLDER
echo.
echo ================================================
echo   SMART CASHIER SYSTEM - AUTOMATED SETUP
echo ================================================
echo.

where git >nul 2>nul
if %errorlevel%==0 (
    if exist ".git" (
        echo [1/6] Already inside project folder. Updating from GitHub...
        git pull
    ) else (
        echo [1/6] Cloning the latest version from GitHub...
        git clone %REPO_URL% smart-cashier-system
        if errorlevel 1 (
            echo.
            echo ERROR: Git clone failed. Check your internet or URL.
            pause
            exit /b 1
        )
        echo.
        echo Changing directory into smart-cashier-system...
        cd smart-cashier-system
    )
) else (
    echo Git not installed. Skipping clone/pull. Assuming files are already here.
)

:: Now we are guaranteed to be inside the project folder
echo.
echo Current directory: %cd%
echo.

:: Step 2: Copy everything to XAMPP htdocs (safe overwrite, no deletion)
echo [2/6] Copying files to XAMPP htdocs...
if not exist "%HTDOCS_PATH%" mkdir "%HTDOCS_PATH%"
xcopy /s /e /h /y * "%HTDOCS_PATH%\" >nul
if errorlevel 1 (
    echo Warning: Some files might be in use. This is usually okay.
) else (
    echo Files copied successfully!
)

:: Step 3: Start XAMPP services
echo.
echo [3/6] Starting Apache and MySQL...
start "" "%XAMPP_PATH%\xampp-control.exe"
ping 127.0.0.1 -n 8 >nul

:: Step 4: Create and import database
echo.
echo [4/6] Creating database: %DB_NAME%
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u %DB_USER% %PASS_OPT% -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME%;"
if errorlevel 1 (
    echo ERROR: Could not create database. Is MySQL running? Password correct?
    pause
    exit /b 1
)

echo [5/6] Importing database structure and sample data...
"%XAMPP_PATH%\mysql\bin\mysql.exe" -u %DB_USER% %PASS_OPT% %DB_NAME% < %SQL_FILE%
if errorlevel 1 (
    echo ERROR: Import failed. Check if database.sql exists.
    pause
    exit /b 1
)
echo Database imported successfully!

:: Final step
echo.
echo [6/6] ALL DONE! Opening your browser...
echo.
echo ================================================
echo   SUCCESS! Your system is ready!
echo   URL: http://localhost/smart-cashier-system/
echo ================================================
echo.
start http://localhost/smart-cashier-system/
pause