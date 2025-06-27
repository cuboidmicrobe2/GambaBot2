@ECHO OFF
set dir=%~dp0
set botFile=main.php
title GambaBot
cd %dir%

call :main
goto :EOF


:main
    CALL :botLoop
goto :EOF

:botLoop
    PHP %dir%%botFile%
goto :botLoop
