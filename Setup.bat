@ECHO OFF
set dir=%~dp0

call :Main
goto :EOF

:Main
    echo Hello %USERNAME%!
    echo  Make sure that the XAMPP database in online before running the setup
    pause
    cls
    call :CreateShortcut
    call :CreateDatabase
    pause
goto :EOF

:CreateShortcut
    echo Creating desktop shortcut...
    set TARGET=%dir%GambaBot.bat
    set SHORTCUT=%USERPROFILE%\Desktop\GambaBot.lnk
    set ICON=%dir%icon.ico
    set arg=/l
    set PWS=powershell.exe -ExecutionPolicy Bypass -NoLogo -NonInteractive -NoProfile

    %PWS% -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut(\"%SHORTCUT%\"); $Shortcut.TargetPath = \"%TARGET%\"; $Shortcut.Arguments = \"%arg%\"; $Shortcut.IconLocation = \"%ICON%\"; $Shortcut.Save()"
    echo Shortcut created!
goto :EOF

:CreateDatabase
    echo Creating database...
    php %dir%\Includes\Database\InitDb.php
    echo Database has been created!
goto :EOF