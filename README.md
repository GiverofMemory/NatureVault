# NatureVault - Saving the Worlds Knowledge
### Uses our own fork of [PmWiki](https://www.pmwiki.org/) software

## Instructions

### FOR OFFLINE USE: 

* Download and unzip folder.
* Navigate to wiki->local and rename the config.php to onlineconfig.php; and rename offlineconfig.php to config.php.
* Navigate to wiki->cookbook and unzip the server-php.zip file.
* Move the inner 'server' folder (not the server-php folder) to the main (root) folder (the one with all the favicons and things).  There should now be three folders in the main folder; server, wiki, and wiki.d.
* Inside the server folder double click pmwikiserv.bat.  A black command window will open, you must leave it open while using the wiki.
* Open your browser and go to http://localhost/wiki/pmwiki.php

for more info see https://www.pmwiki.org/wiki/Cookbook/Standalone

### FOR NORMAL ONLINE HOSTING:

* Download this and place these files and folders into the "htdocs" or "public" folder of your server.
  * To do this I like to use [SSH](https://www.chiark.greenend.org.uk/~sgtatham/putty/) if I am not personally hosting it, then when you are in the hosting directory (usually 'public') run (don't forget the period after):
  
      `git clone https://github.com/GiverofMemory/NatureVault.git .`
* Make sure you check wiki/local/config.php file and modify url's and directory references (like upload directory) to reflect your domain name and your host's folder structure.  Without doing this certain things like pictures and skins and cookbooks may not work.
* If you need to install HTTPS support yourself, using [SSH](https://www.chiark.greenend.org.uk/~sgtatham/putty/) enter the command:`tls-setup.sh` (or see [more options](https://manpages.ubuntu.com/manpages/xenial/man1/letsencrypt.1.html)).  This certificate is in the ".well-known" folder so don't delete it.  If you delete it you may have to wait until the certificate expires to renew, check here: https://crt.sh/.  You can only have [5 failed attempts per hour](https://community.letsencrypt.org/t/disaster-too-many-certificates-tried-on-one-domain/87856).
* If you need to remove everything from a folder to start again, use the command:`rm -rf *`
  * However if you want to use git clone again you may need to FTP into the folder and delete the ".git" folder which doesn't seem to get removed from the above command.
* You need to FTP (or see below for SSH instructions) using filezilla to set permissions of the wiki -> "wiki.d" folder (not the original wiki.d folder, the one inside the wiki folder) by right clicking the folder and setting permissions, then check all boxes, and also check "recurse to subfolders", and "apply to all files and directories" to allow public write so people can login to the wiki.  It should say permission 777.
* The same as above needs to be done for the wiki -> "Uploads" folder.
* If you cannot use FTP, here are the SSH comands when you are logged into your server: (note that /home/public might be different for your folder structure)
  * chmod 777 /home/public/wiki/uploads
  * chmod 777 /home/public/wiki/wiki.d
    * aside, the command: chmod 2777 /home/public/wiki/uploads might be temporary?
### Notes
* For personal hosting Abyss Web server works well.
* after typing out a command always hit the 'enter' key to run it.
* the period after the 'git clone' statement means that these files and folders are placed into the directory you are in, instead of making a new folder (which won't work).
* A good website to get new favicon's made is [favicon.io](https://favicon.io)
* For performing daily backups from a webhost like [nearlyfreespeech.net](https://nearlyfreespeech.net) and making a discord bot see [this page](https://www.naturevault.org/wiki/pmwiki.php/NatureVault/Github)
