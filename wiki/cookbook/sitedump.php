<?php if (!defined('PmWiki')) exit();
# creates a .tar.gz file of the complete site for download
# will silently reload the page if the required auth level is not met.
# 01 June 2017 tictactux@gmail.com
$RecipeInfo['SiteDump']['Version'] = '2017-06-01';

# add "?action=dump"
SDV($HandleActions['dump'],'HandleDump');
SDV($HandleAuth['dump'], 'edit');

function HandleDump($pagename, $auth) {
  global $WikiTitle;
  if (RetrieveAuthPage("NatureWiki.Naturewiki", $auth, false, READPAGE_CURRENT)) 
{
    $dt = date('Ymd-His');
    header("Content-type: application/x-tar");
    header("Content-disposition: attachment; filename=\"Backup-$WikiTitle-" . $dt . ".tar.gz\"");
    header("Content-transfer-encoding: binary");
    header("Pragma: no-cache");
    passthru("tar -czf - *");
  } 
else {
    Redirect($pagename);
  }
}