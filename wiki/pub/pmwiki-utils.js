/*
  JavaScript utilities for PmWiki
  (c) 2009-2022 Petko Yotov www.pmwiki.org/petko
  based on PmWiki addons DeObMail, AutoTOC and Ape
  licensed GNU GPLv2 or any more recent version released by the FSF.

  libsortable() "Sortable tables" adapted for PmWiki from
  a Public Domain event listener by github.com/tofsjonas
*/

(function(){
  function aE(el, ev, fn) {
    if(typeof el == 'string') el = dqsa(el);
    for(var i=0; i<el.length; i++) el[i].addEventListener(ev, fn);
  }
  function dqs(str)  { return document.querySelector(str); }
  function dqsa(str) { return document.querySelectorAll(str); }
  function tap(q, fn) { aE(q, 'click', fn); };
  function pf(x) {var y = parseFloat(x); return isNaN(y)? 0:y; }
  function zpad(n) {return (n<10)?"0"+n : n; }
  function adjbb(el, html) { el.insertAdjacentHTML('beforebegin', html); }
  function adjbe(el, html) { el.insertAdjacentHTML('beforeend', html); }
  function adjab(el, html) { el.insertAdjacentHTML('afterbegin', html); }
  function adjae(el, html) { el.insertAdjacentHTML('afterend', html); }
  function getLS(key, parse) { 
    var x = window.localStorage.getItem(key)|| null;
    return parse ? JSON.parse(x) : x;}
  function setLS(key, value) { 
    if (typeof value == 'object') value = JSON.stringify(value);
    window.localStorage.setItem(key, value);}
  function PHSC(x) { return x.replace(/[&]/g, '&amp;').replace(/[<]/g, '&lt;').replace(/[>]/g, '&gt;'); }

  var __script__, wikitext;
  var log = console.log;

  function PmXMail() {
    var els = document.querySelectorAll('span._pmXmail');
    var LinkFmt = '<a href="%u" class="mail">%t</a>';

    for(var i=0; i<els.length; i++) {
      var x = els[i].querySelector('span._t');
      var txt = cb_mail(x.innerHTML);
      var y = els[i].querySelector('span._m');
      var url = cb_mail(y.innerHTML.replace(/^ *-&gt; */, ''));

      if(!url) url = 'mailto:'+txt.replace(/^mailto:/, '');

      url = url.replace(/"/g, '%22').replace(/'/g, '%27');
      var html = LinkFmt.replace(/%u/g, url).replace(/%t/g, txt);
      els[i].innerHTML = html;
    }
  }
  function cb_mail(x){
    return x.replace( /<span class=(['"]?)_d\1>[^<]+<\/span>/ig, '.')
      .replace( /<span class=(['"]?)_a\1>[^<]+<\/span>/ig, '@');
  }

  function is_toc_heading(el) {
    if(el.offsetParent === null) {return false;}  // hidden
    if(el.closest('.notoc,.markup2')) {return false;} 
    return true;
  }
  function posy(el) {
    var top = 0;
    if (el.offsetParent) {
      do {
        top += el.offsetTop;
      } while (el = el.offsetParent);
    }
    return top;
  }

  function any_id(h) {
    if(h.id) {return h.id;} // %id=anchor%
    var a = h.querySelector('a[id]'); // inline [[#anchor]]
    if(a && a.id) {return a.id;}
    var prev = h.previousElementSibling;
    if(prev) { // [[#anchor]] before !!heading
      var a = prev.querySelectorAll('a[id]');
      if(a.length) {
        last = a[a.length-1];
        if(last.id && ! last.nextElementSibling) {
          var atop = posy(last) + last.offsetHeight;
          var htop = posy(h);
          if( Math.abs(htop-atop)<20 ) {
            h.appendChild(last);
            return last.id;
          }
        }
      }
    }
    return false;
  }
  function inittoggle() {
    var tnext = __script__.dataset.toggle;
    if(! tnext) { return; }
    var x = dqsa(tnext);
    if(! x.length) return;
    for(var i=0; i<x.length; i++) togglenext(x[i]);
    tap(tnext, togglenext);
    tap('.pmtoggleall', toggleall);
  }
  function togglenext(z) {
    var el = z.type == 'click' ? this : z;
    var attr = el.dataset.pmtoggle=='closed' ? 'open' : 'closed';
    el.dataset.pmtoggle = attr;
  }
  function toggleall(){
    var curr = this.dataset.pmtoggleall;
    if(!curr) curr = 'closed';
    var toggles = dqsa('*[data-pmtoggle="'+curr+'"]');
    var next = curr=='closed' ? 'open' : 'closed';
    for(var i=0; i<toggles.length; i++) {
      toggles[i].dataset.pmtoggle = next;
    }
    var all = dqsa('.pmtoggleall');
    for(var i=0; i<all.length; i++) {
      all[i].dataset.pmtoggleall = next;
    }
  }

  function autotoc() {
    if(dqs('.noPmTOC')) { return; } // (:notoc:) in page
    var dtoc = __script__.dataset.pmtoc;
    try {dtoc = JSON.parse(dtoc);} catch(e) {dtoc = false;}
    if(! dtoc) { return; } // error

    if(! dtoc.Enable || !dtoc.MaxLevel) { return; } // disabled

    if(dtoc.NumberedHeadings)  {
      var specs = dtoc.NumberedHeadings.toString().split(/\./g);
      for(var i=0; i<specs.length; i++) {
        if(specs[i].match(/^[1AI]$/i)) numheadspec[i] = specs[i];
      }
    }

    var query = [];
    for(var i=1; i<=dtoc.MaxLevel; i++) {
      query.push('h'+i);
    }
    if(dtoc.EnableQMarkup) query.push('p.question');
    var pageheadings = wikitext.querySelectorAll(query.join(','));
    if(!pageheadings.length) { return; }

    var toc_headings = [ ];
    var minlevel = 1000, hcache = [ ];
    for(var i=0; i<pageheadings.length; i++) {
      var h = pageheadings[i];
      if(! is_toc_heading(h)) {continue;}
      toc_headings.push(h);
    }
    if(! toc_headings.length) return;

    var tocdiv = dqs('.PmTOCdiv');
    var shouldmaketoc = ( tocdiv || (toc_headings.length >= dtoc.MinNumber && dtoc.MinNumber != -1)) ? 1:0;
    if(!dtoc.NumberedHeadings && !shouldmaketoc) return;

    for(var i=0; i<toc_headings.length; i++) {
      var h = toc_headings[i];
      var level = pf(h.tagName.substring(1));
      if(! level) level = 6;
      minlevel = Math.min(minlevel, level);
      var id = any_id(h);
      hcache.push([h, level, id]);
    }

    prevlevel = 0;
    var html = '';
    for(var i=0; i<hcache.length; i++) {
      var hc = hcache[i];
      var actual_level = hc[1] - minlevel;
//       if(actual_level>prevlevel+1) actual_level = prevlevel+1;
//       prevlevel = actual_level;

      var currnb = numberheadings(actual_level);
      if(! hc[2]) {
        hc[2] = 'toc-'+currnb.replace(/\.+$/g, '');
        hc[0].id = hc[2];
      }
      if(dtoc.NumberedHeadings && currnb.length) adjab(hc[0], currnb+' ');

      if(! shouldmaketoc) { continue; }
      var txt = hc[0].textContent.trim().replace(/</g, '&lt;');
      var sectionedit = hc[0].querySelector('.sectionedit');
      if(sectionedit) {
        var selength = sectionedit.textContent.length;
        txt = txt.slice(0, -selength);
      }
      
      html += '<a style="margin-left: '+ actual_level
        + 'em;" href="#'+hc[2]+'">' + txt + '</a>\n';
      if(dtoc.EnableBacklinks) 
        adjbe(hc[0], ' <a class="back-arrow" href="#_toc">&uarr;</a>');
      
    }

    if(! shouldmaketoc) return;

    html = "<b>"+dtoc.contents+"</b> "
      +"[<input type='checkbox' id='PmTOCchk'><label for='PmTOCchk'>"
      +"<span class='pmtoc-show'>"+dtoc.show+"</span>"
      +"<span class='pmtoc-hide'>"+dtoc.hide+"</span></label>]"
      +"<div class='PmTOCtable'>" + html + "</div>";

    if(!tocdiv) {
      var wrap = "<div class='PmTOCdiv'></div>";
      if(dtoc.ParentElement && dqs(dtoc.ParentElement)) {
        adjab(dqs(dtoc.ParentElement), wrap);
      }
      else {
        adjbb(hcache[0][0], wrap);
      }
      tocdiv = dqs('.PmTOCdiv');

    }
    if(!tocdiv) return; // error?
    tocdiv.className += " frame";
    tocdiv.id = '_toc';

    tocdiv.innerHTML = html;

    if(getLS('closeTOC')) { dqs('#PmTOCchk').checked = true; }
    aE('#PmTOCchk', 'change', function(e){
      setLS('closeTOC', this.checked ? "close" : '');
    });

    var hh = location.hash;
    if(hh.length>1) {
      var cc = document.getElementById(hh.substring(1));
      if(cc) cc.scrollIntoView();
    }
  }

  var numhead = [0, 0, 0, 0, 0, 0, 0];
  var numheadspec = '1 1 1 1 1 1 1'.split(/ /g);
  function numhead_alpha(n, upper) {
    if(!n) return '_';
    var alpha = '', mod, start = upper=='A' ? 65 : 97;
    while (n>0) {
      mod = (n-1)%26;
      alpha = String.fromCharCode(start + mod) + '' + alpha;
      n = (n-mod)/26 | 0;
    }
    return alpha;
  }
  function numhead_roman(n, upper) {
    if(!n) return '_';
    // partially based on http://blog.stevenlevithan.com/?p=65#comment-16107
    var lst = [ [1000,'M'], [900,'CM'], [500,'D'], [400,'CD'], [100,'C'], [90,'XC'],
      [50,'L'], [40,'XL'], [10,'X'], [9,'IX'], [5,'V'], [4,'IV'], [1,'I'] ];
    var roman = '';
    for(var i=0; i<lst.length; i++) {
      while(n>=lst[i][0]) {
        roman += lst[i][1];
        n -= lst[i][0];
      }
    }
    return (upper == 'I') ? roman : roman.toLowerCase();
  }

  function numberheadings(n) {
    if(n<numhead[6]) for(var j=numhead[6]; j>n; j--) numhead[j]=0;
    numhead[6]=n;
    numhead[n]++;
    var qq = '';
    for (var j=0; j<=n; j++) {
      var curr = numhead[j];
      var currspec = numheadspec[j];
      if(currspec.match(/a/i)) { curr = numhead_alpha(curr, currspec); }
      else if(currspec.match(/i/i)) { curr = numhead_roman(curr, currspec); }

      qq+=curr+".";
    }
    return qq;
  }

  function makesortable() {
    if(! pf(__script__.dataset.sortable)) return;
    var tables = dqsa('table.sortable,table.sortable-footer');
    for(var i=0; i<tables.length; i++) {
      // non-pmwiki-core table, already ready
      if(tables[i].querySelector('thead')) continue;

      tables[i].classList.add('sortable'); // for .sortable-footer

      var thead = document.createElement('thead');
      tables[i].insertBefore(thead, tables[i].firstChild);

      var rows = tables[i].querySelectorAll('tr');
      thead.appendChild(rows[0]);
      var tbody = tables[i].querySelector('tbody');
      if(! tbody) {
        tbody = tables[i].appendChild(document.createElement('tbody'));
        for(var r=1; r<rows.length; r++) tbody.appendChild(rows[r]);
      }
      if(tables[i].className.match(/sortable-footer/)) {
        var tfoot = tables[i].appendChild(document.createElement('tfoot'));
        tfoot.appendChild(rows[rows.length-1]);
      }
      mkdatasort(rows);
    }
    libsortable();
  }
  function mkdatasort(rows) {
    var hcells = rows[0].querySelectorAll('th,td');
    var specialsort = [], span;
    for(var i=0; i<hcells.length; i++) {
      sortspan = hcells[i].querySelector('.sort-number,.sort-number-us,.sort-date');
      if(sortspan) specialsort[i] = sortspan.className;
    }
    if(! specialsort.length) return;
    for(var i=1; i<rows.length; i++) {
      var cells = rows[i].querySelectorAll('td,th');
      var k = 0;
      for(var j=0; j<cells.length && j<specialsort.length; j++) {
        if(! specialsort[j]) continue;
        var t = cells[j].innerText, ds = '';
        if(specialsort[j] == 'sort-number-us') {ds = t.replace(/[^-.\d]+/g, ''); }
        else if(specialsort[j] == 'sort-number') {ds = t.replace(/[^-,\d]+/g, '').replace(/,/g, '.'); }
        else if(specialsort[j] == 'sort-date') {ds = new Date(t).getTime(); }
        if(ds) cells[j].setAttribute('data-sort', ds);
      }
    }
  }
  function libsortable(){
    // adapted from Public Domain code by github.com/tofsjonas
    function getValue(obj) {
      obj = obj.cells[column_index];
      return obj.getAttribute('data-sort') || obj.innerText;
    }
    function reclassify(element, cname) {
      element.classList.remove('dir-u', 'dir-d');
      if(cname) element.classList.add(cname);
    }
    var column_index;
    document.addEventListener('click', function(e) {
      if(e.target.closest('a')) return; // links
      var element = e.target.closest('th');
      if (! element) return;
      var table = element.offsetParent;
      if (!table.classList.contains('sortable')) return;
                              
      var cells = element.parentNode.cells;
      for (var i = 0; i < cells.length; i++) {
        if (cells[i] === element) {
          column_index = i;
        } else {
          reclassify(cells[i], '');
        }
      }
      var cname = 'dir-d', reverse = false;
      if (element.classList.contains(cname)) {
        cname = 'dir-u';
        reverse = true;
      }
      reclassify(element, cname);
      var tbody = table.tBodies[0];
      var rows = [];
      for(var r=0; r<tbody.rows.length; r++) rows.push(tbody.rows[r]);
      
      rows.sort(function(x, y) {
        var a = getValue(reverse? y:x),
            b = getValue(reverse? x:y);
        var c = a.localeCompare(b, undefined, {numeric: true, sensitivity: 'base'}),
            d = a - b;
        return isNaN(d) ? c : d;
      });
      for (i = 0; i < rows.length; i++) {
        tbody.appendChild(rows[i]);
      }
    });
  }

  function highlight_pre() {
    if(!__script__.dataset.highlight) return;
    if (typeof hljs == 'undefined') return;
    
    var x = dqsa('.highlight,.hlt');
    
    for(var i=0; i<x.length; i++) {
      if(x[i].className.match(/(^| )(pm|pmwiki)( |$)/)) { continue;} // core highlighter
      var pre = Array.from(x[i].querySelectorAll('pre,code'));
      var n = x[i].nextElementSibling;
      if (n && n.tagName == 'PRE') pre.push(n);
      for(var j=0; j<pre.length; j++) {
        pre[j].className += ' ' + x[i].className;
        var varlinks = pre[j].querySelectorAll('a.varlink');
        var vararray = {};
        for(var v=0; v<varlinks.length; v++) {
          vararray[varlinks[v].textContent] = varlinks[v].href;
        }
        
        if(pre[j].children) pre[j].textContent = pre[j].textContent;

        hljs.highlightElement(pre[j]);
        var hlvars = pre[j].querySelectorAll('span.hljs-variable');
        for(var v=0; v<hlvars.length; v++) {
          var hlvar = hlvars[v].textContent;
          if(vararray.hasOwnProperty(hlvar)) {
            hlvars[v].innerHTML = '<a class="varlink" href="'+vararray[hlvar]+'">'+hlvar+'</a>';
          }
        }
      }
    }
  }
  
  var Now, ltmode, daymonth, pagename;
  function fmtLocalTime(stamp) {
    var d = new Date(stamp*1000);
    var tooltip = PHSC(d.toLocaleString());
    if(ltmode == 2)
      return [tooltip];
    if(Now-d < 24*3600000) 
      return [zpad(d.getHours()) + ':'+ zpad(d.getMinutes()), tooltip];
    var D = zpad(d.getDate()), M = zpad(d.getMonth()+1);
    var thedate = daymonth.replace(/%d/, D).replace(/%m/, M);
    if(Now-d < 334*24*3600000) return [thedate, tooltip];
    return [thedate + '/' + d.getFullYear(), tooltip];
  }
  
  function localTimes() {
    ltmode = pf(__script__.dataset.localtimes);
    if(! ltmode) return;
    if(ltmode>=11) {
      var days = Math.floor(ltmode/10);
      ltmode = ltmode%10;
    }
    else var days = 3;
    Now = new Date();
    pagename = __script__.dataset.fullname;
    var seenstamp = getLS('seenstamp', true);
    if(!seenstamp) seenstamp = {};
    var previous = seenstamp[pagename];
    
    var times = dqsa('time[datetime]');

    daymonth =  new Date(2021, 11, 26, 17)
      .toLocaleDateString().match(/26.*12/)? '%d/%m': '%m/%d';
    
    var h72 = Now.getTime()/1000-days*24*3600;
    
    for(var i=0; i<times.length; i++) {
      var itemdate = new Date(times[i].dateTime);      
      var stamp = Math.floor(itemdate.getTime()/1000);
      
      var li = times[i].closest('li');
      if (!li || !li.innerHTML.match(/<\/a>  \. \. \. /)) {
        var x = fmtLocalTime(stamp);
        times[i].innerHTML = x[0];
        times[i].title = x[1] ? x[1]: itemdate.textContent;
        continue;
      }
      var link = li.querySelector('a');
      if(link.className.match(/createlinktext|wikilink|selflink/)) {
        var diff = link.href + '?action=diff#diff' + stamp;
        if(stamp >= h72) {
          var h = link.href + '?action=diff&fmt=rclist';
          adjbe(li, ' <b class="rcplus" data-url="'+h+'">+</b>');
        }
      }
      // recent uploads, other? we want to know when the link becomes "visited"
      else diff = link.href + '#diff' + stamp; 
      times[i].innerHTML = '<a href="'+diff+'">'+times[i].innerHTML+'</a>';
    }

    var difflinks = dqsa('a[href*="#diff"]'), diffcnt = 0;
    for(var i=0; i<difflinks.length; i++) {
      var link = difflinks[i];
      if(link.hostname != location.hostname) continue;
      var a = link.href.match(/[#]diff(\d+)$/);
      if(!a) continue;
      diffcnt++;
      stamp = parseInt(a[1]);
      var x = fmtLocalTime(stamp);
      
      link.innerHTML = x[0];
      link.setAttribute('title', x[1] ? x[1]: link.textContent);
      
      var par = link.closest('li');
      if(!par) continue;
      par.insertBefore(link, par.firstChild);
      adjae(link, "&nbsp;&nbsp;");
      if(previous && stamp>previous) par.classList.add('rcnew');
    }
    if(!diffcnt) return;
    var pagetitle = dqs('#wikititle h1, h1.pagetitle');
    if(pagetitle) {
      var time = zpad(Now.getHours()) + ':'+ zpad(Now.getMinutes());
      adjbe(pagetitle, ' <span class="rcreload" title="Click to reload">'+time+'</span>');
      tap('.rcreload', function(){location.reload();});
    }
    aE('.rcnew', 'mouseup', function(e){
      if(e.which == 2) this.classList.remove('rcnew');
    });
    tap('.rcplus', function(e){
      var plus = this;
      plus.style.display = 'none';
      var basehref = plus.dataset.url.replace(/&fmt=rclist/, '#diff')
        .replace(/[&]/g, '&amp;');
      var fmt = '<p class="outdent"><a href="'+basehref+'%d" title="%T">%t</a> %s</p>\n';
      fetch(plus.dataset.url)
      .then(function(resp){return resp.text();})
      .then(function(text){
        var lines = text.split(/\n/g);
        var out = '';
        for(var i=0; i<lines.length; i++) {
          a = lines[i].match(/^(\d+):(.*)$/);
          if(!a) continue;
          var time = fmtLocalTime(pf(a[1]));
          out += fmt.replace(/%d/, a[1]).replace(/%T/, time[1])
            .replace(/%t/, time[0]).replace(/%s/, a[2]);
        }
        if(out) adjae(plus, out);
      })
      .catch(log);
    });
    if(dqs('form[name="authform"]') || location.href.match(/action=/)) return;
    seenstamp[pagename] = Math.floor(Now.getTime()/1000);
    setLS('seenstamp', seenstamp);
  }

  function ready(){
    __script__ = dqs('script[src*="pmwiki-utils.js"]');
    wikitext = document.getElementById('wikitext');
    var fn = [autotoc, inittoggle, PmXMail, localTimes, highlight_pre, makesortable];
    fn.forEach(function(a){a();});
  }
  if( document.readyState !== 'loading' ) ready();
  else window.addEventListener('DOMContentLoaded', ready);
})();

