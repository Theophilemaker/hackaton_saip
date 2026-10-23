@echo off
REM Place this in tools\nisr_engine\
REM Requires MinGW-w64 or MSYS2

setlocal

echo ============================================
echo   Building NISR Analytics Engine (C++)
echo ============================================

cd /d "%~dp0"

where g++ >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] g++ not found.
    echo.
    echo Install MinGW-w64:
    echo   Option 1: https://winlibs.com/  (easiest)
    echo   Option 2: via MSYS2 - pacman -S mingw-w64-x86_64-gcc
    echo.
    pause
    exit /b 1
)

echo Compiler: 
g++ --version | findstr /r "g++"

echo.
echo Compiling with maximum optimization...
g++ -O3 -std=c++17 -static ^
    -o "..\..\public\bin\nisr_engine.exe" ^
    main.cpp

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Build failed.
    pause
    exit /b 1
)

echo.
echo [SUCCESS] Built: public\bin\nisr_engine.exe
echo.
echo Test it:
echo   ..\..\public\bin\nisr_engine.exe schema "..\..\public\uploads\nisr\your-file.csv"
echo.

pause