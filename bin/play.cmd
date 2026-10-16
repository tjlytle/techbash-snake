@echo off
rem play.cmd - runs play.ps1 with the execution policy bypassed, so it works on a fresh Windows box.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0play.ps1" %*
