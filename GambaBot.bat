@ECHO OFF
set dir=%~dp0
set botFile=main.php
title GambaBot
cd %dir%

call :main
goto :EOF


:main
    CALL :BotLoop
goto :EOF

:BotLoop
    PHP %dir%%botFile%

    if %ERRORLEVEL% eq 710 (
        exit
    )
goto :BotLoop
