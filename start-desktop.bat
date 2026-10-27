@echo off
rem Spusti BP Control ako desktopovu aplikaciu. NativePHP vyzaduje PHP 8.3+.
rem Ak system nema PHP 8.3+, pouzije sa PHP 8.3 z balika nativephp/php-bin.
cd /d "%~dp0"
set "PHPBIN=php"
php -r "exit(PHP_VERSION_ID >= 80300 ? 0 : 1);" >nul 2>&1
if errorlevel 1 (
    set "PHPBIN=%LOCALAPPDATA%\bp-control\php83\php.exe"
    if not exist "%LOCALAPPDATA%\bp-control\php83\php.exe" (
        powershell -NoProfile -Command "Expand-Archive -Force 'vendor\nativephp\php-bin\bin\win\x64\php-8.3.zip' '%LOCALAPPDATA%\bp-control\php83'"
    )
)
set "PATH=%PATH%;C:\Program Files\nodejs"
"%PHPBIN%" artisan native:run --no-dependencies
