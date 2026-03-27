@echo off
setlocal

set "DDEV_BIN="
for /f "delims=" %%I in ('where ddev 2^>nul') do set "DDEV_BIN=%%I"
if not defined DDEV_BIN if exist "%LocalAppData%\Programs\DDEV\ddev.exe" set "DDEV_BIN=%LocalAppData%\Programs\DDEV\ddev.exe"
if not defined DDEV_BIN if exist "C:\Program Files\DDEV\ddev.exe" set "DDEV_BIN=C:\Program Files\DDEV\ddev.exe"

if not defined DDEV_BIN (
  echo DDEV executable was not found.
  exit /b 1
)

where cloudflared >nul 2>&1
if errorlevel 1 (
  echo cloudflared not found. Install with: choco install cloudflared -y
  exit /b 1
)

echo Starting 3 Cloudflare tunnels for all sites...
echo.
echo Each tunnel window will display a URL like:
echo   https://random-words.trycloudflare.com
echo.
echo After all 3 windows show their URL, run:
echo   configure-tunnels.cmd PRIMARY_URL SITE1_URL SITE2_URL
echo.

start "Tunnel-Primary" cmd /k "echo === PRIMARY SITE === && cloudflared tunnel --url https://primary.ddev.site --no-tls-verify"
timeout /t 5 /nobreak >nul

start "Tunnel-Site1" cmd /k "echo === SITE 1 === && cloudflared tunnel --url https://site1.ddev.site --no-tls-verify"
timeout /t 5 /nobreak >nul

start "Tunnel-Site2" cmd /k "echo === SITE 2 === && cloudflared tunnel --url https://site2.ddev.site --no-tls-verify"

echo.
echo 3 tunnel windows opened.
echo Wait for each window to show its URL, then run:
echo   configure-tunnels.cmd https://abc.trycloudflare.com https://def.trycloudflare.com https://ghi.trycloudflare.com
echo.

endlocal
