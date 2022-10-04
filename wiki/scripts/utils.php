<?php if (!defined('PmWiki')) exit();
/*  Copyright 2019-2022 Petko Yotov www.pmwiki.org/petko
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script includes and configures one or more JavaScript utilities, 
    when they are enabled by the wiki administrator, notably:
    
    * Tables of contents
    * Sortable tables
    * Localized time stamps
    * Improved recent changes
    * Syntax highlighting (PmWiki markup)
    * Syntax highlighting (external)
    * Collapsible sections
    * Email obfuscation
    
    To disable all these functions, add to config.php:
      $EnablePmUtils = 0;
*/

if ( IsEnabled($PmTOC['Enable'], 0)
  || IsEnabled($PmEmbed, 0) 
  || IsEnabled($EnableSortable, 0)
  || $LinkFunctions['mailto:'] == 'ObfuscateLinkIMap' 
  || IsEnabled($EnableHighlight, 0)
  || IsEnabled($ToggleNextSelector, 0)
  || IsEnabled($EnableLocalTimes, 0)
  ) {
  $utils = "$FarmD/pub/pmwiki-utils.js";
  if(file_exists($utils)) {
    $mtime = filemtime($utils);
    SDVA($HTMLHeaderFmt, array('pmwiki-utils' =>
      "<script type='text/javascript' src='\$FarmPubDirUrl/pmwiki-utils.js?st=$mtime'
        data-sortable='".@$EnableSortable."' data-highlight='".@$EnableHighlight."'
        data-pmtoc='".PHSC(json_encode(@$PmTOC), ENT_QUOTES)."'
        data-toggle='".PHSC(@$ToggleNextSelector, ENT_QUOTES)."'
        data-localtimes='".@$EnableLocalTimes."' data-fullname='{\$FullName}'
        data-pmembed='".PHSC(json_encode(@$PmEmbed), ENT_QUOTES)."'></script>"
    ));
  }
}

if (IsEnabled($EnablePmSyntax, 0)) { # inject before skins and local.css
  array_unshift($HTMLHeaderFmt, "<link rel='stylesheet' 
    href='\$FarmPubDirUrl/guiedit/pmwiki.syntax.css'>
  <script src='\$FarmPubDirUrl/guiedit/pmwiki.syntax.js' data-imap='{\$EnabledIMap}'
    data-label=\"$[Highlight]\" data-mode='$EnablePmSyntax'
    data-custom=\"".(is_array(@$CustomSyntax)
      ? PHSC(json_encode(array_values($CustomSyntax)), ENT_QUOTES)
      : '')."\"></script>");
}
