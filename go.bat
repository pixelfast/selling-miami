@echo off

rem Check if there are any changes (staged, unstaged, or untracked)
git status --porcelain | findstr . >nul
if errorlevel 1 (
    echo No changes to commit.
    exit /b 0
)

rem Prompt for description
set /p desc="Enter commit description: "

rem Abort if description is empty
if "%desc%"=="" (
    echo Description cannot be empty.
    exit /b 1
)

rem Run git commands
git add .
git commit -m "%desc%"
git push