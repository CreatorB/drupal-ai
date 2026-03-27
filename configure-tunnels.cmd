@echo off
setlocal enabledelayedexpansion

if "%~3"=="" (
  echo Usage: configure-tunnels.cmd PRIMARY_URL SITE1_URL SITE2_URL
  echo.
  echo Example:
  echo   configure-tunnels.cmd https://abc.trycloudflare.com https://def.trycloudflare.com https://ghi.trycloudflare.com
  exit /b 1
)

set "DDEV_BIN="
for /f "delims=" %%I in ('where ddev 2^>nul') do set "DDEV_BIN=%%I"
if not defined DDEV_BIN if exist "%LocalAppData%\Programs\DDEV\ddev.exe" set "DDEV_BIN=%LocalAppData%\Programs\DDEV\ddev.exe"
if not defined DDEV_BIN if exist "C:\Program Files\DDEV\ddev.exe" set "DDEV_BIN=C:\Program Files\DDEV\ddev.exe"

set "P=%~1"
set "S1=%~2"
set "S2=%~3"

rem Strip https:// to get hostname
set "P=!P:https://=!"
set "S1=!S1:https://=!"
set "S2=!S2:https://=!"

rem Strip trailing slash if present
if "!P:~-1!"=="/" set "P=!P:~0,-1!"
if "!S1:~-1!"=="/" set "S1=!S1:~0,-1!"
if "!S2:~-1!"=="/" set "S2=!S2:~0,-1!"

(
echo {
echo   "!P!": "primary.ddev.site",
echo   "!S1!": "site1.ddev.site",
echo   "!S2!": "site2.ddev.site"
echo }
) > tunnel-sites.json

echo.
echo tunnel-sites.json created:
type tunnel-sites.json
echo.

if defined DDEV_BIN (
  echo Clearing Drupal cache...
  "%DDEV_BIN%" drush --uri=primary.ddev.site cr
)

echo.
echo Done! Sites mapped:
echo   Primary : %~1
echo   Site 1  : %~2
echo   Site 2  : %~3
echo.
echo Send these URLs to the client.

endlocal
