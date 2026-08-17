@echo off
REM Starts the POS Print Agent on this PC.
REM Double-click to run, or drop a shortcut to this file in the Startup folder
REM (Win+R -> shell:startup) so it comes up automatically at login.

cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -NoProfile -File "%~dp0pos-print-agent.ps1" %*
pause
