# NatureVault - Saving the Worlds Knowledge
### Uses [PmWiki](https://www.pmwiki.org/) software

## Instructions

* Download this and place these files and folders into the "htdocs" or "public" folder of your server.
  * I like to use [SSH](https://www.chiark.greenend.org.uk/~sgtatham/putty/) if I am not personally hosting it, then when you are in the hosting directory (usually 'public') run (don't forget the period after):`git clone https://github.com/GiverofMemory/NatureVault.git .`
* Make sure you check wiki/local/config.php file and modify url's and directory references (like upload directory) to reflect your host's folder structure.  Without doing this certain things like pictures and skins and cookbooks may not work.
* If you need to install HTTPS support yourself, using [SSH](https://www.chiark.greenend.org.uk/~sgtatham/putty/) enter the command:`tls-setup.sh`
* If you need to remove everything from a folder to start again, use the command:`rm -rf *`
  * However if you want to use git clone again you may need to FTP into the folder and delete the ".git" folder which doesn't seem to get removed from the above command.
* You need to FTP using filezilla to set permissions of the wiki/wiki.d folder (by right clicking it and setting permissions) and all contents to allow public write so people can login to the wiki.

### Notes
* For personal hosting Abyss Web server works well.
* after typing out a command always hit the 'enter' key to run it.
* the period after the 'git clone' statement means that these files and folders are placed into the directory you are in, instead of making a new folder (which won't work).
