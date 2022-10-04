<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004-2022 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script handles author tracking.
    
    Script maintained by Petko YOTOV www.pmwiki.org/petko
*/

SDV($AuthorNameChars, "- '\\w\\x80-\\xff");
SDV($AuthorCookie, $CookiePrefix.'author');
SDV($AuthorCookieExpires,$Now+60*60*24*30);
SDV($AuthorCookieDir,'/');
SDV($AuthorGroup,'Profiles');
SDV($AuthorRequiredFmt,
  "<h3 class='wikimessage'>$[An author name is required.]</h3>");
Markup('[[~','<links','/\\[\\[~(.*?)\\]\\]/',"[[$AuthorGroup/$1]]");

$LogoutCookies[] = $AuthorCookie;

if (!isset($Author)) {
  if (isset($_POST['author'])) {
    $x = stripmagic($_POST['author']);
    pmsetcookie($AuthorCookie, $x, $AuthorCookieExpires, $AuthorCookieDir);
  } elseif (@$_COOKIE[$AuthorCookie]) {
    $x = stripmagic(@$_COOKIE[$AuthorCookie]);
  } else $x = @$AuthId;
  $Author = PHSC(preg_replace("/[^$AuthorNameChars]/", '', strval($x)), 
                ENT_COMPAT);
}
if (!isset($AuthorPage)) $AuthorPage = 
    FmtPageName('$AuthorGroup/$Name', MakePageName("$AuthorGroup.$AuthorGroup", $Author));
SDV($AuthorLink,($Author) ? "[[~$Author]]" : '?');

## EnableSignatures() is now called from stdconfig.php, because author.php may be 
## included early by local config or recipe, before some variables are defined.
function EnableSignatures() {
  global $EnableLocalTimes, $CurrentLocalTime, $CurrentTime,
    $Author, $EnableAuthorSignature, $ROSPatterns;
  if (! IsEnabled($EnableAuthorSignature,1)) return;
  $time = IsEnabled($EnableLocalTimes, 0)? $CurrentLocalTime : $CurrentTime;
  SDVA($ROSPatterns, array(
    '/(?<!~)~~~~(?!~)/' => "[[~$Author]] $time",
    '/(?<!~)~~~(?!~)/' => "[[~$Author]]",
  ));
}
if (IsEnabled($EnablePostAuthorRequired,0))
  array_unshift($EditFunctions,'RequireAuthor');

## RequireAuthor forces an author to enter a name before posting.
function RequireAuthor($pagename, &$page, &$new) {
  global $Author, $MessagesFmt, $AuthorRequiredFmt, $EnablePost;
  if (!$Author) {
    $MessagesFmt[] = $AuthorRequiredFmt;
    $EnablePost = 0;
  }
}
