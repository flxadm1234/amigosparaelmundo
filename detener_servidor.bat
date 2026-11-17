@echo off
chcp 65001 >nul
echo.
echo ==========================================
echo   AMIGOS TV - Detener Servidor LAN
echo ==========================================
echo.
echo [1/2] Deteniendo PHP Dev Server...
taskkill /f /im php.exe >nul 2>&1
echo [2/2] Deteniendo MariaDB...
taskkill /f /im mariadbd.exe >nul 2>&1
echo.
echo Servidores detenidos.
timeout /t 2 /nobreak >nul
