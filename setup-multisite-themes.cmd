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

echo === SITE 1 ===
"%DDEV_BIN%" drush --uri=site1.ddev.site php:script scripts/setup_primary_plan.php
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site1.ddev.site php:script scripts/seed_demo_content.php
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site1.ddev.site theme:install site1_theme -y
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site1.ddev.site config:set system.theme default site1_theme -y
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site1.ddev.site cr
if errorlevel 1 exit /b 1

echo.
echo === SITE 2 ===
"%DDEV_BIN%" drush --uri=site2.ddev.site php:script scripts/setup_primary_plan.php
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site2.ddev.site php:script scripts/seed_demo_content.php
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site2.ddev.site theme:install site2_theme -y
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site2.ddev.site config:set system.theme default site2_theme -y
if errorlevel 1 exit /b 1
"%DDEV_BIN%" drush --uri=site2.ddev.site cr
if errorlevel 1 exit /b 1

echo.
echo Multisite setup complete.

endlocal
