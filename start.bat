@echo off
title Document Verification System - Server
set MARIA_DIR=C:\Users\Peto\AppData\Local\Temp\opencode\mariadb\mariadb-11.4.6-winx64
set PHP_EXE=
for /f "delims=" %%i in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%i"
if not defined PHP_EXE set "PHP_EXE=C:\Users\Peto\AppData\Local\Temp\opencode\php\php.exe"

echo Checking if MariaDB is already running on port 3306...
netstat -an | findstr /C:":3306" | findstr "LISTENING" >nul
if %errorlevel%==0 (
    echo MariaDB is already running.
) else (
    echo Starting MariaDB...
    start "MariaDB" /min "%MARIA_DIR%\bin\mariadbd.exe" --datadir="%MARIA_DIR%\data"
)

echo Waiting for MariaDB to accept connections...
set /a tries=0
:waitdb
netstat -an | findstr /C:":3306" | findstr "LISTENING" >nul
if %errorlevel%==0 goto dbready
set /a tries+=1
if %tries% geq 30 (
    echo ERROR: MariaDB did not start within 30 seconds.
    echo Check "%MARIA_DIR%\data\Peter.err"
    pause
    exit /b 1
)
timeout /t 1 /nobreak >nul
goto waitdb

:dbready
echo MariaDB is ready.
for %%i in ("%PHP_EXE%") do set "PHP_DIR=%%~dpi"
echo Using PHP: %PHP_EXE%
echo Starting PHP server on http://127.0.0.1:8080 ...
"%PHP_EXE%" -d extension_dir="%PHP_DIR%ext" -d extension=pdo_mysql -d extension=mysqli -d extension=mbstring -d extension=openssl -d extension=fileinfo -d extension=curl -d extension=gd -d upload_max_filesize=25M -d post_max_size=25M -S 127.0.0.1:8080 -t "D:\client1" "D:\client1\index.php"