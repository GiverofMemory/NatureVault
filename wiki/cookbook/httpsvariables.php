<?php if (!defined('PmWiki')) exit();

/*
Copyright (c) 2015, Martin Fick
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those
of the authors and should not be interpreted as representing official policies,
either expressed or implied, of the FreeBSD Project.

# Author:  Martin Fick
# RandyB added "^" to return URL request, COOKIE, or SESSION variable regardless of php configuration
# 20190501: Updated for PHP 7.2 by Petko Yotov pmwiki.org/petko
*/

$RecipeInfo['HttpsVariables']['Version'] = '20190501';

# {$?!|@^~var} http variable substitutions before {$var}
Markup('{$?|!@^~var}1', '<{$var}',
  '/\\{\\$([\\?\\!\\|@^~])(\\w+)\\}/',
  "mu_HttpsVariables");

# {$?!|@^~var} http variable substitutions after {$var}
Markup('{$?|!@^~var}2', '>{$var}',
  '/\\{\\$([\\?\\!\\|@^~])(\\w+)\\}/',
  "mu_HttpsVariables");

# (:cookie [name] [value] [args]:)
Markup('cookie', '>if', "/\\(:cookie\\s+(.*?)\\s*:\\)/i",
  "mu_HttpsVariablesCookie");

# (:session [name] [value] [args]:)
Markup('session','>if',"/\\(:session\\s+(.*?)\\s*:\\)/i",
  "mu_HttpsVariablesSession");

function mu_HttpsVariables($m) {
  extract($GLOBALS['MarkupToHTML']);
  return HttpsVariables($pagename, $m[1], $m[2]);
}

function mu_HttpsVariablesCookie($m) {
  extract($GLOBALS['MarkupToHTML']);
  return HttpsVariablesCookie($pagename,  ParseArgs($m[1]));
}

function mu_HttpsVariablesSession($m) {
  extract($GLOBALS['MarkupToHTML']);
  return HttpsVariablesSession($pagename,  ParseArgs($m[1]));
}

function HttpsVariablesCookie($pagename, $args) {
  $name = $args['name'];
  $val = $args['value'];
  $exp = $args['expires'];
  $path = $args['path'];
  $domain = $args['domain'];
  $sec = $args['secure'];
  $http = $args['httponly'];

  $sargs = $args[''];
  foreach($sargs as $arg) {
    switch($arg) {
      case 'secure':      $sec = true; break;
      case 'httponly':    $http = true; break;
      default: if(!$name) $name = $arg;
           elseif(!$val)  $val = $arg;
    } 
  }
  if(!$name) return;

  if(!$val) $val = null;
  if(!$exp) $exp = 0;
  else      $exp = strtotime($exp);
  if(!$path) $path = '';
  if(!$domain) $domain = '';
  if(!$secure) $secure = false;
  if(!$http) $http = false;

  if(version_compare(phpversion(), '5.2', '>='))
    setcookie($name, $val, $exp, $path, $domain, $sec, $http);
  else
    setcookie($name, $val, $exp, $path, $domain, $sec);
  return;
}

function HttpsVariablesSession($pagename, $args)
{
  $name = $args['name'];
  $val = $args['value'];
  $sargs = $args[''];
  foreach($sargs as $arg) {
    if(!$name) $name = $arg;
    elseif(!$val)  $val = $arg;
  }
  if(!$name) return;
  session_start();
  $_SESSION[$name] = $val;
}

function HttpsVariables($pagename, $type, $var)
{
  global $Charset;
  switch($type) {
    case "?": $val= $_GET[$var]; break;
    case "|": $val= $_POST[$var]; break;
    case "!": $val= $_REQUEST[$var]; break;
    case "@": $val= $_COOKIE[$var]; break;
    case "^": 
        if (isset($_GET[$var])) {
          $val= $_GET[$var];
        } elseif (isset($_COOKIE[$var])) {
          $val= $_COOKIE[$var];
        } else { 
          $val= $_SESSION[$var];
        }
        break;    
    case "~": session_start();
        $val= $_SESSION[$var]; break;
  }
  if($val) 
    return htmlentities(stripmagic($val),ENT_COMPAT,$Charset);
  return "";
}