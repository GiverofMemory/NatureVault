cd C:\Tor\Browser\TorBrowser\Tor

timeout /t 1

tor.exe --service stop

timeout /t 1

tor.exe --service start

timeout /t 10