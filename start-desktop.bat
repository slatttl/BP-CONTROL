@echo off
rem Spusti BP Control ako desktopovu aplikaciu (NativePHP vyzaduje PHP 8.3+, system ma 8.2).
cd /d "%~dp0"
set "PHP83=%LOCALAPPDATA%\bp-control\php83"
if not exist "%PHP83%\php.exe" (
    powershell -NoProfile -Command "Expand-Archive -Force 'vendor\nativephp\php-bin\bin\win\x64\php-8.3.zip' '%PHP83%'"
)
set "PATH=%PATH%;C:\Program Files\nodejs"
"%PHP83%\php.exe" artisan native:run --no-dependencies
