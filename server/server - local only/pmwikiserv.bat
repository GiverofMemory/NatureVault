:: http://localhost/wiki/pmwiki.php/NatureVault/Server
:: https://www.php.net/manual/en/features.commandline.webserver.php
@echo off
::If you want only the local machine to access your wiki use the following lines:
:: echo #####
:: echo OPEN IN YOUR BROWSER
:: echo OPEN IN YOUR BROWSER http://localhost/wiki/pmwiki.php
:: echo OPEN IN YOUR BROWSER
:: echo #####
:: php -S 127.0.0.1:80 -t ../

::If you want the wiki to be accessible to other machines on the network via 192.168.1.X:8888 then use following lines:
echo #####
echo OPEN IN YOUR BROWSER
echo OPEN IN YOUR BROWSER http://192.168.1.X:8888/wiki/pmwiki.php  (where X is specifying the local address of the server)
echo OPEN IN YOUR BROWSER
echo #####
php -S 0.0.0.0:8888