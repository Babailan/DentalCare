@echo off
setlocal
title Dental Appointment System - Port 4000
cd /d "%~dp0"

rem Prefer the XAMPP PHP build, then fall back to PHP on PATH.
set "PHP_EXE=C:\xampp2\php\php.exe"
if not exist "%PHP_EXE%" set "PHP_EXE=C:\xampp\php\php.exe"
if not exist "%PHP_EXE%" (
    where php >nul 2>nul
    if errorlevel 1 (
        echo PHP was not found.
        echo Install XAMPP or add php.exe to your PATH.
        pause
        exit /b 1
    )
    set "PHP_EXE=php"
)

echo ============================================================
echo  CIAT Dental Clinic
echo  URL: http://localhost:4000/index.php
echo  Press Ctrl+C to stop the server.
echo ============================================================
echo.

"%PHP_EXE%" -S localhost:4000 -t "%~dp0"

echo.
echo Server stopped.
pause
endlocal
