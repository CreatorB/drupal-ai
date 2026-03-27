@echo off
setlocal

set "DDEV_BIN="
for /f "delims=" %%I in ('where ddev 2^>nul') do set "DDEV_BIN=%%I"
if not defined DDEV_BIN if exist "%LocalAppData%\Programs\DDEV\ddev.exe" set "DDEV_BIN=%LocalAppData%\Programs\DDEV\ddev.exe"
if not defined DDEV_BIN if exist "C:\Program Files\DDEV\ddev.exe" set "DDEV_BIN=C:\Program Files\DDEV\ddev.exe"

if not defined DDEV_BIN (
  echo DDEV executable was not found.
  echo Install DDEV or add it to PATH, then run this script again.
  exit /b 1
)

echo Stopping all DDEV projects and shared DDEV services...
"%DDEV_BIN%" poweroff

if errorlevel 1 (
  echo.
  echo Failed to power off DDEV cleanly.
  exit /b 1
)

echo.
echo All DDEV-managed containers are stopped.

endlocal
