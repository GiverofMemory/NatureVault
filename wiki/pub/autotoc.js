/**
  AutoTOC - Unobtrusive Automatic Table of Contents for PmWiki
  Written by (c) Petko Yotov 2011-2017    www.pmwiki.org/Petko

  This text is written for PmWiki; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version. See pmwiki.php for full details
  and lack of warranty.

  Version 20170717
*/

var AT = {
  $: function(id) {
    return document.getElementById(id);
  },
  numhead: [],
  repeat: function(n, id) {
    var q = '';
    for(var i=1; i<=n; i++) q+= i18nTOC.indent;
    if(i18nTOC.prefix!='#') q+=i18nTOC.prefix;
    else q+=AT.numberheadings(n, id);
    return q;
  },
  numberheadings: function(n, id) {
    if(n<AT.numhead[6]) for(var j=AT.numhead[6]; j>n; j--)AT.numhead[j]=0;
    AT.numhead[6]=n;
    AT.numhead[n]++;
    var qq = '';
    for (var j=0; j<=n; j++) qq+=AT.numhead[j]+"."; qq+=' ';
    var el = AT.$(id);
    el.innerHTML = qq+el.innerHTML;
    return qq;
  },
  flip: function(state) {
    if(! state) {
      var tocQ = document.cookie.match(/TOC=(none|block)/);
      state = (tocQ)? tocQ[1] : 'block';
      state = (state=="none") ? 'block' : 'none';
    }
    var lx = document.getElementsByClassName('flipTOC');
    var tx = document.getElementsByClassName('innerTOC');
    for(var i=0; i<tx.length; i++) { tx[i].style.display = state; }
    for(var i=0; i<lx.length; i++) { lx[i].innerHTML = i18nTOC[state]; }

    document.cookie = "TOC="+state+"; path=/";
  },
  traverse_wikitext: function(el) {
    var nn = el.nodeName;
    var cn = el.className;
    if(cn && cn.match(/\bno(toc|tdm)\b/)) var q = 1;
    else if(nn.match(AT.levels)) AT.get_heading(el);
    else if(nn=='TABLE' && cn && cn.indexOf('markup')>=0) var q = 1;
    else if(el.hasChildNodes()) {
      var children = el.childNodes;
      for(var i=0; i<children.length; i++) AT.traverse_wikitext(children[i]);
    }
  },
  create_id: function(txt){
    var hash = "";
    var fld = txt.toLowerCase();
    for(var i in i18nTOC.custfold) fld = fld.replace(i18nTOC.custfold[i], i);
    for(var i in AT.i18nFold) fld = fld.replace(AT.i18nFold[i], i);

    if(i18nTOC.idenc=='numeric') {
      hash = 'at_'+AT.headings.length+1;
    }
    else if(i18nTOC.idenc=='acronyms') {
      hash = '_';
      lc = fld.replace(/[^-\s'\/a-z]+/g, '');
      var words = lc.split(/[-\s'\/]+/g);
      for (var i=0; i<words.length; i++) {
        if(words[i].length==0) continue;
        hash += words[i].charAt(0);
      }
    }
    else if(i18nTOC.idenc=='fold') {
      hash = fld.replace(/[^-\w\s]+/g, '').replace(/\s+/g, '_').replace(/([_-])[_-]+/g, '$1');
    }
    else { // default
      hash = encodeURIComponent(txt.replace(/\s+/g, '_')).replace(/%/g, '.')
      .replace(/'/g, '.27').replace(/~/g, '.7E').replace(/!/g, '.21')
      .replace(/\(/g, '.28').replace(/\)/g, '.29').replace(/\*/g, '.2A')
      .replace(/^([^a-zA-Z])/, 't$1');
    }
    if(AT.spa) {
      hash = AT.spa + hash;
    }
    var e = AT.$(hash);
    if(!e)return hash;
    var incr = 2;
    while(incr<9999) {
      var x = hash+'_'+incr;
      var e = AT.$(x);
      if(!e)return x;
      incr ++;
    }
  },
  get_heading: function(el) {
    var text = el.innerText || el.textContent;
    text = text.replace(/^\s*(.*)\s*$/, '$1');
    var id = el.id
    if(!id) {
      id = AT.create_id(text);
      el.id = id;
    }
    AT.cache[id] = 1;
    var level = parseInt(el.nodeName.substring(1));
    AT.headings.push([ level, id, text ]);
    if (AT.biggestheading>level) AT.biggestheading=level;
  },
  init: function(zone) {
    AT.headings = [ ];
    AT.cache = { };
    AT.biggestheading = 6;
    AT.numhead = [0, 0, 0, 0, 0, 0, 0];
    var qdiv = (zone.id == 'wikitext') ? document : zone;
    var div = qdiv.getElementsByClassName('AutoTOCdiv');
    AT.div = (div && div.length) ? div[0] : false;

    var notoc = qdiv.getElementsByClassName('noAutoTOC');
    if(notoc && notoc.length) return;

    if(i18nTOC.nbheadings<0 && !AT.div) return;
    AT.traverse_wikitext(zone);
    if(!AT.headings.length) return;
    if(AT.headings.length<i18nTOC.nbheadings && !AT.div) return;
    if(!AT.div){
      AT.div = document.createElement("div");
      AT.div.setAttribute('class', 'AutoTOCdiv');
      if(i18nTOC.prependToDiv) {
        var first = AT.$(i18nTOC.prependToDiv);
        if(first.firstChild) first.insertBefore(AT.div, first.firstChild);
        else first.appendChild(AT.div);
      }
      else {
        var first = AT.$(AT.headings[0][1]);
        first.parentNode.insertBefore(AT.div, first);
      }
    }

    var html = "<table class='frame tableTOC' border='0'><tr class='headerTOC'><td><b>"+i18nTOC.contents
    +"</b> <small>[<a class='flipTOC' href='javascript:AT.flip(0);'>"+i18nTOC.block+"</a>]</small></td></tr>";
    html += "<tr class='innerTOC'><td>";

    for(var i=0; i<AT.headings.length; i++) {
      var wh = AT.headings[i];
      html += AT.repeat(wh[0]-AT.biggestheading, wh[1]) + "<a href='#"+wh[1]+"'>"+wh[2].replace(/</, '&lt;')+"</a>"
      if(i<AT.headings.length-1) html += "<br/>";
    }
    html += "</td></tr></table>";
    AT.div.innerHTML = html;
    var tocQ = document.cookie.match(/TOC=(none|block)/);
    var state = (tocQ)? tocQ[1] : 'block';
    if(state=='none') AT.flip('none');

    var hh = location.hash;
    if(hh.length>1) {
      var cc = AT.$(hh.substring(1));
      if(cc) cc.scrollIntoView();
    }
  },
  preinit: function() {
    var pages = document.getElementsByClassName('spa_pagecontent');
    if(pages && pages.length) {
      for(var i=0; i<pages.length; i++){
        var page = pages[i];
        AT.spa = '__'+ page.id.substring(8)+'__';
        AT.init(page);
      }
    }
    else AT.init(AT.$('wikitext'));
  },
  spa: false,
  levels: new RegExp("^H[1-"+i18nTOC.levels+"]$", 'i'),
  i18nFold: { // Based on lehelk.com/2011/05/06/ (Latin), 2cyr.com/?7 (Cyrillic), en.wikipedia.org/wiki/Greek_alphabet
    'a':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250\u0430\u04D1\u04D3\u044A\u04D9\u04DB\u03B1]/g,
    'aa':/[\uA733]/g,
    'ae':/[\u00E6\u01FD\u01E3\u04D5]/g,
    'ao':/[\uA735]/g,
    'au':/[\uA737]/g,
    'av':/[\uA739\uA73B]/g,
    'ay':/[\uA73D]/g,
    'b':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253\u0431\u03B2]/g,
    'c':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g,
    'ch':/[\u0447\u045B\u04B7\u04B9\u04BD\u04BF\u04CC\u04F5\u03C7]/g,
    'd':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A\u0434\u0452\u03B4]/g,
    'dz':/[\u01F3\u01C6\u0455\u045F\u04E1]/g,
    'e':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD\u0435\u044D\u0450\u0451\u0454\u04D7\u04ED\u03B5]/g,
    'f':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C\u0444\u03C6]/g,
    'g':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F\u0433\u0453\u0491\u0493\u0495\u04F7\u04FB\u03B3]/g,
    'h':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265\u0445\u04A9\u04B3\u04FD\u04FF]/g,
    'hv':/[\u0195]/g,
    'i':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131\u0438\u0456\u0457\u045D\u048B\u04E3\u04E5\u03B9\u03B7]/g,
    'j':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249\u0436\u0458\u0497\u04C2\u04DD]/g,
    'k':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3\u043A\u045C\u049B\u049D\u049F\u04A1\u04C4\u03BA]/g,
    'l':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747\u043B\u0459\u04C6\u03BB]/g,
    'lj':/[\u01C9]/g,
    'm':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F\u043C\u04CE\u03BC]/g,
    'n':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5\u043D\u045A\u04A3\u04A5\u04C8\u04CA\u03BD]/g,
    'nj':/[\u01CC]/g,
    'o':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275\u043E\u04E7\u04E9\u04EB\u03BF\u03C9]/g,
    'oi':/[\u01A3]/g,
    'ou':/[\u0223]/g,
    'oo':/[\uA74F]/g,
    'p':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755\u043F\u04A7\u03C0]/g,
    'ps':/[\u03C8]/g,
    'q':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g,
    'r':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783\u0440\u048F\u03C1]/g,
    's':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B\u0441\u04AB\u03C2\u03C3]/g,
    'sh':/[\u0448\u04BB]/g,
    'sht':/[\u0449]/g,
    't':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787\u0442\u04AD\u03C4]/g,
    'th':/[\u03B8]/g,
    'tz':/[\uA729]/g,
    'ts':/[\u0446\u04B5]/g,
    'u':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289\u0443\u045E\u04AF\u04B1\u04EF\u04F1\u04F3]/g,
    'v':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C\u0432]/g,
    'vy':/[\uA761]/g,
    'w':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g,
    'x':/[\u0078\u24E7\uFF58\u1E8B\u1E8D\u03BE]/g,
    'y':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF\u0439\u044B\u044C\u048D\u04F9\u03C5]/g,
    'yu':/[\u044E]/g,
    'ya':/[\u044F]/g,
    'z':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763\u0437\u0499\u04DF\u03B6]/g
  }
}

setTimeout("AT.preinit()", 50);