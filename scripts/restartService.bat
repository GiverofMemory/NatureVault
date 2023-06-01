cd "C:\Tor\Browser\TorBrowser\Tor" tor.exe --service stop

timeout /t 20

cd "C:\Tor\Browser\TorBrowser\Tor" tor.exe --service start

timeout /t 20