#!/bin/sh

#https://stackoverflow.com/questions/16709404/how-to-automate-the-commit-and-push-process-git

#https://stackoverflow.com/questions/10054318/how-do-i-provide-a-username-and-password-when-running-git-clone-gitremote-git

#https://stackoverflow.com/questions/29776439/username-and-password-in-command-for-git-push

#https://faq.nearlyfreespeech.net/full/runscript

cd c:git/NatureVault

git pull

timeout /t 5

git add .

git commit -m "2023 hourly auto-update"

#The word 'token' below would be replaced with your github personal access token code.  It can be gotten for your account at github.com in your settings->developer settings->personal access tokens

git push https://token@github.com/GiverofMemory/NatureVault.git main