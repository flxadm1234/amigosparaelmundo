@echo off
chcp 65001 >nul
setlocal

fltmc >nul 2>&1 || (
    echo [UAC] Solicitando permisos de Administrador para Firewall Windows...
    powershell -NoProfile -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

set ROOT=%~dp0
set PHP=%ROOT%.tools\php\php.exe
set MYSQLD=%ROOT%.tools\mariadb\bin\mariadbd.exe
set MYSQLD_DATADIR=%ROOT%.tools\mariadb\data
set HOST=0.0.0.0
set PORT=8000
set ROUTER=%ROOT%index.php

echo.
echo ==========================================
echo   AMIGOS TV - Servidor LAN Acceso Red
echo   Bind %HOST%:%PORT%
echo   IP LAN http://192.168.1.179:%PORT%/
echo ==========================================
echo.

if not exist "%PHP%" ( echo [ERROR] PHP no encontrado en %PHP% & exit /b 1 )

echo [0/3] Limpieza archivos stale MariaDB (PID, ibtmp1)...
if exist "%MYSQLD_DATADIR%\*.pid" del /f /q "%MYSQLD_DATADIR%\*.pid" >nul 2>&1
if exist "%MYSQLD_DATADIR%\ibtmp1"    del /f /q "%MYSQLD_DATADIR%\ibtmp1" >nul 2>&1
timeout /t 1 /nobreak >nul

echo [1/3] Iniciando MariaDB (puerto 3306)...
tasklist /fi "imagename eq mariadbd.exe" /fo csv 2>nul | findstr /i "mariadbd.exe" >nul
if errorlevel 1 (
    start "AMIGOS POR EL MUNDO-MariaDB" /min "%MYSQLD%" --datadir="%MYSQLD_DATADIR%" --port=3306 --bind-address=127.0.0.1 --skip-name-resolve
    timeout /t 4 /nobreak >nul
    echo       MariaDB iniciado.
) else (
    echo       MariaDB ya estaba corriendo.
)

echo [2/3] Agregando regla Firewall Windows (TCP %PORT% entrada)...
powershell -NoProfile -Command "$r = Get-NetFirewallRule -DisplayName 'AMIGOS POR EL MUNDO PHP LAN 8000' -ErrorAction SilentlyContinue; if (-not $r) { New-NetFirewallRule -DisplayName 'AMIGOS POR EL MUNDO PHP LAN 8000' -Direction Inbound -Protocol TCP -LocalPort %PORT% -Action Allow -Profile Private,Domain -ErrorAction Stop | Out-Null; Write-Host '      Regla Firewall creada.' } else { Write-Host '      Regla Firewall ya existente.' }" 2>&1 | findstr /v /r "^$"

echo [3/3] Iniciando PHP Dev Server en http://%HOST%:%PORT% (acceso red LAN)...
tasklist /fi "imagename eq php.exe" /fo csv 2>nul | findstr /i "php.exe" >nul
if not errorlevel 1 (
    echo       Deteniendo instancia PHP anterior...
    taskkill /f /im php.exe >nul 2>&1
    timeout /t 1 /nobreak >nul
)

set PHP_CLI_SERVER_WORKERS=4
start "AMIGOS POR EL MUNDO-PHP-LAN" "%PHP%" -d variables_order=EGPCS -S %HOST%:%PORT% "%ROUTER%"

echo.
echo ==========================================
echo  SERVIDOR LAN LISTO
echo  Local:   http://127.0.0.1:%PORT%/
echo  Red LAN: http://192.168.1.179:%PORT%/
echo  Admin:   http://192.168.1.179:%PORT%/admin/login
echo ==========================================
echo.
pause
endlocal
