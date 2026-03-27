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

echo Starting Drupal AI multisite...
"%DDEV_BIN%" start

if errorlevel 1 (
  echo.
  echo Failed to start the project.
  exit /b 1
)

echo.
echo Project is running.
echo Primary: https://primary.ddev.site
echo Site 1 : https://site1.ddev.site
echo Site 2 : https://site2.ddev.site
echo.
echo To stop only this project, run: stop-demo.cmd
echo To stop all DDEV projects, run: stop-all-ddev.cmd

endlocal
