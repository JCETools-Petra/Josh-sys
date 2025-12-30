@echo off
echo Clearing Laravel cache...
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan view:clear
echo.
echo Cache cleared successfully!
echo.
echo Now access the Front Office at:
echo http://127.0.0.1:8000/frontoffice
echo.
pause
