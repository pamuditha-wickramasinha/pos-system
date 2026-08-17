@echo off
REM ===========================================================================
REM  Start POS  -  double-click this to open the till.
REM
REM  Starts the print agent (if not already running), then opens the POS in the
REM  browser. Edit the three settings below once, for this PC.
REM ===========================================================================

REM  Address of the POS page to open. Change this to your server.
REM  The agent's own server URL and token live in agent-config.json.
set "POS_URL=http://76.13.247.65/pos"

REM  1 = open as a clean app window with no address bar or tabs (recommended for
REM      a till, so staff cannot browse away). 0 = normal browser window.
set "POS_KIOSK=1"

REM ===========================================================================
REM  Nothing below here needs editing.
REM ===========================================================================

cd /d "%~dp0"

set "KIOSK_FLAG="
if "%POS_KIOSK%"=="1" set "KIOSK_FLAG=-Kiosk"

powershell -ExecutionPolicy Bypass -NoProfile -File "%~dp0start-pos.ps1" -Url "%POS_URL%" %KIOSK_FLAG%

if errorlevel 1 (
    echo.
    echo Something went wrong starting the POS. Read the message above.
    pause
)
