@echo off
echo =======================================================
echo          SECLAB - CYBERSECURITY TRAINING GROUND
echo =======================================================
echo Menjalankan PHP Built-in Web Server pada http://localhost:8000
echo Tekan Ctrl+C di terminal ini untuk mematikan server.
echo.
start http://localhost:8000
php -S localhost:8000 index.php
pause
