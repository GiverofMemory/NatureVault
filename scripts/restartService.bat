cmd /c ""C:\Tor\Browser\TorBrowser\Tor" tor.exe --service stop

timeout /t 2

cmd /c ""C:\Tor\Browser\TorBrowser\Tor" tor.exe --service start

timeout /t 2