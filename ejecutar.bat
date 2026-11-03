@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Tecnicas Contables - Lanzador

REM ============================================================================
REM  TECNICAS CONTABLES - Lanzador de la aplicacion
REM  Diego Alessandro Romero Montoya / Juan Pablo Henriquez Montano
REM
REM  Doble clic para abrir el sistema. NO requiere instalar WAMP ni nada:
REM   - La base de datos es SQLite (un archivo en la carpeta data\).
REM   - Incluye un PHP portable en la carpeta php\. Si se borro, este
REM     script lo descarga automaticamente (solo la primera vez).
REM ============================================================================

cd /d "%~dp0"
set "APP_DIR=%~dp0"
set "PUERTO=8000"
set "PHP_URL=https://windows.php.net/downloads/releases/php-8.4.26-nts-Win32-vs17-x64.zip"

echo ================================================================
echo   TECNICAS CONTABLES - Lanzador de la aplicacion
echo   (base de datos SQLite: no requiere WAMP ni MySQL)
echo ================================================================
echo.

REM ---------------------------------------------------------------
REM 1) Buscar PHP: primero el portable incluido, luego el del sistema
REM ---------------------------------------------------------------
set "PHP="
if exist "%APP_DIR%php\php.exe" set "PHP=%APP_DIR%php\php.exe"

if not defined PHP (
    where php >nul 2>&1
    if not errorlevel 1 set "PHP=php"
)
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP (
    for /d %%D in ("C:\wamp64\bin\php\php*") do if exist "%%D\php.exe" set "PHP=%%D\php.exe"
)

REM ---------------------------------------------------------------
REM 2) Si no hay PHP en ninguna parte, descargarlo (una sola vez)
REM ---------------------------------------------------------------
if not defined PHP (
    echo [*] No se encontro PHP en este equipo.
    echo     Descargando PHP portable: 33 MB, solo la primera vez...
    echo.
    mkdir "%APP_DIR%php" 2>nul
    curl -L -s -o "%TEMP%\tecnicas-php.zip" "%PHP_URL%"
    if errorlevel 1 goto :SIN_PHP
    tar -xf "%TEMP%\tecnicas-php.zip" -C "%APP_DIR%php"
    del "%TEMP%\tecnicas-php.zip" >nul 2>&1
    set "PHP=%APP_DIR%php\php.exe"
)

REM ---------------------------------------------------------------
REM 3) Si el php.ini se perdio, recrearlo (habilita SQLite y MySQL)
REM ---------------------------------------------------------------
if exist "%APP_DIR%php\" if not exist "%APP_DIR%php\php.ini" (
    (
        echo extension_dir = "ext"
        echo extension = pdo_sqlite
        echo extension = sqlite3
        echo extension = pdo_mysql
        echo extension = mbstring
        echo date.timezone = "America/El_Salvador"
    ) > "%APP_DIR%php\php.ini"
)

if not exist "%PHP%" goto :SIN_PHP

REM ---------------------------------------------------------------
REM 4) Levantar el servidor y abrir la aplicacion
REM ---------------------------------------------------------------
echo [+] PHP: %PHP%
echo [*] Levantando el sistema en http://localhost:%PUERTO%
echo.
echo     NO CIERRE la ventana "Servidor PHP" mientras use el sistema.
echo     La base de datos se crea sola en la carpeta data\.
echo.
start "Servidor PHP - Tecnicas Contables" "%PHP%" -S localhost:%PUERTO% -t "%APP_DIR%."
timeout /t 2 /nobreak >nul
start "" "http://localhost:%PUERTO%/"

echo Listo. Ya puede cerrar ESTA ventana (la del servidor PHP, no).
pause
exit /b 0

REM ---------------------------------------------------------------
:SIN_PHP
echo [X] No se pudo obtener PHP (revise su conexion a internet).
echo     Descargue manualmente el zip "nts-Win32-vs17-x64" de
echo     https://windows.php.net/download y descomprimalo en la
echo     carpeta "php" de este proyecto.
echo.
pause
exit /b 1
