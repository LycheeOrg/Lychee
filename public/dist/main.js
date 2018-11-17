/*! jQuery v3.3.1 | (c) JS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(e,t){"use strict";var n=[],r=e.document,i=Object.getPrototypeOf,o=n.slice,a=n.concat,s=n.push,u=n.indexOf,l={},c=l.toString,f=l.hasOwnProperty,p=f.toString,d=p.call(Object),h={},g=function e(t){return"function"==typeof t&&"number"!=typeof t.nodeType},y=function e(t){return null!=t&&t===t.window},v={type:!0,src:!0,noModule:!0};function m(e,t,n){var i,o=(t=t||r).createElement("script");if(o.text=e,n)for(i in v)n[i]&&(o[i]=n[i]);t.head.appendChild(o).parentNode.removeChild(o)}function x(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?l[c.call(e)]||"object":typeof e}var b="3.3.1",w=function(e,t){return new w.fn.init(e,t)},T=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;w.fn=w.prototype={jquery:"3.3.1",constructor:w,length:0,toArray:function(){return o.call(this)},get:function(e){return null==e?o.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=w.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return w.each(this,e)},map:function(e){return this.pushStack(w.map(this,function(t,n){return e.call(t,n,t)}))},slice:function(){return this.pushStack(o.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(n>=0&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:s,sort:n.sort,splice:n.splice},w.extend=w.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||g(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)n=a[t],a!==(r=e[t])&&(l&&r&&(w.isPlainObject(r)||(i=Array.isArray(r)))?(i?(i=!1,o=n&&Array.isArray(n)?n:[]):o=n&&w.isPlainObject(n)?n:{},a[t]=w.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},w.extend({expando:"jQuery"+("3.3.1"+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==c.call(e))&&(!(t=i(e))||"function"==typeof(n=f.call(t,"constructor")&&t.constructor)&&p.call(n)===d)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e){m(e)},each:function(e,t){var n,r=0;if(C(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},trim:function(e){return null==e?"":(e+"").replace(T,"")},makeArray:function(e,t){var n=t||[];return null!=e&&(C(Object(e))?w.merge(n,"string"==typeof e?[e]:e):s.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:u.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r,i=[],o=0,a=e.length,s=!n;o<a;o++)(r=!t(e[o],o))!==s&&i.push(e[o]);return i},map:function(e,t,n){var r,i,o=0,s=[];if(C(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&s.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&s.push(i);return a.apply([],s)},guid:1,support:h}),"function"==typeof Symbol&&(w.fn[Symbol.iterator]=n[Symbol.iterator]),w.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){l["[object "+t+"]"]=t.toLowerCase()});function C(e){var t=!!e&&"length"in e&&e.length,n=x(e);return!g(e)&&!y(e)&&("array"===n||0===t||"number"==typeof t&&t>0&&t-1 in e)}var E=function(e){var t,n,r,i,o,a,s,u,l,c,f,p,d,h,g,y,v,m,x,b="sizzle"+1*new Date,w=e.document,T=0,C=0,E=ae(),k=ae(),S=ae(),D=function(e,t){return e===t&&(f=!0),0},N={}.hasOwnProperty,A=[],j=A.pop,q=A.push,L=A.push,H=A.slice,O=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},P="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",R="(?:\\\\.|[\\w-]|[^\0-\\xa0])+",I="\\["+M+"*("+R+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+R+"))|)"+M+"*\\]",W=":("+R+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+I+")*)|.*)\\)|)",$=new RegExp(M+"+","g"),B=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),F=new RegExp("^"+M+"*,"+M+"*"),_=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),z=new RegExp("="+M+"*([^\\]'\"]*?)"+M+"*\\]","g"),X=new RegExp(W),U=new RegExp("^"+R+"$"),V={ID:new RegExp("^#("+R+")"),CLASS:new RegExp("^\\.("+R+")"),TAG:new RegExp("^("+R+"|[*])"),ATTR:new RegExp("^"+I),PSEUDO:new RegExp("^"+W),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+P+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},G=/^(?:input|select|textarea|button)$/i,Y=/^h\d$/i,Q=/^[^{]+\{\s*\[native \w/,J=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,K=/[+~]/,Z=new RegExp("\\\\([\\da-f]{1,6}"+M+"?|("+M+")|.)","ig"),ee=function(e,t,n){var r="0x"+t-65536;return r!==r||n?t:r<0?String.fromCharCode(r+65536):String.fromCharCode(r>>10|55296,1023&r|56320)},te=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ne=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},re=function(){p()},ie=me(function(e){return!0===e.disabled&&("form"in e||"label"in e)},{dir:"parentNode",next:"legend"});try{L.apply(A=H.call(w.childNodes),w.childNodes),A[w.childNodes.length].nodeType}catch(e){L={apply:A.length?function(e,t){q.apply(e,H.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function oe(e,t,r,i){var o,s,l,c,f,h,v,m=t&&t.ownerDocument,T=t?t.nodeType:9;if(r=r||[],"string"!=typeof e||!e||1!==T&&9!==T&&11!==T)return r;if(!i&&((t?t.ownerDocument||t:w)!==d&&p(t),t=t||d,g)){if(11!==T&&(f=J.exec(e)))if(o=f[1]){if(9===T){if(!(l=t.getElementById(o)))return r;if(l.id===o)return r.push(l),r}else if(m&&(l=m.getElementById(o))&&x(t,l)&&l.id===o)return r.push(l),r}else{if(f[2])return L.apply(r,t.getElementsByTagName(e)),r;if((o=f[3])&&n.getElementsByClassName&&t.getElementsByClassName)return L.apply(r,t.getElementsByClassName(o)),r}if(n.qsa&&!S[e+" "]&&(!y||!y.test(e))){if(1!==T)m=t,v=e;else if("object"!==t.nodeName.toLowerCase()){(c=t.getAttribute("id"))?c=c.replace(te,ne):t.setAttribute("id",c=b),s=(h=a(e)).length;while(s--)h[s]="#"+c+" "+ve(h[s]);v=h.join(","),m=K.test(e)&&ge(t.parentNode)||t}if(v)try{return L.apply(r,m.querySelectorAll(v)),r}catch(e){}finally{c===b&&t.removeAttribute("id")}}}return u(e.replace(B,"$1"),t,r,i)}function ae(){var e=[];function t(n,i){return e.push(n+" ")>r.cacheLength&&delete t[e.shift()],t[n+" "]=i}return t}function se(e){return e[b]=!0,e}function ue(e){var t=d.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function le(e,t){var n=e.split("|"),i=n.length;while(i--)r.attrHandle[n[i]]=t}function ce(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function fe(e){return function(t){return"input"===t.nodeName.toLowerCase()&&t.type===e}}function pe(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function de(e){return function(t){return"form"in t?t.parentNode&&!1===t.disabled?"label"in t?"label"in t.parentNode?t.parentNode.disabled===e:t.disabled===e:t.isDisabled===e||t.isDisabled!==!e&&ie(t)===e:t.disabled===e:"label"in t&&t.disabled===e}}function he(e){return se(function(t){return t=+t,se(function(n,r){var i,o=e([],n.length,t),a=o.length;while(a--)n[i=o[a]]&&(n[i]=!(r[i]=n[i]))})})}function ge(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}n=oe.support={},o=oe.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return!!t&&"HTML"!==t.nodeName},p=oe.setDocument=function(e){var t,i,a=e?e.ownerDocument||e:w;return a!==d&&9===a.nodeType&&a.documentElement?(d=a,h=d.documentElement,g=!o(d),w!==d&&(i=d.defaultView)&&i.top!==i&&(i.addEventListener?i.addEventListener("unload",re,!1):i.attachEvent&&i.attachEvent("onunload",re)),n.attributes=ue(function(e){return e.className="i",!e.getAttribute("className")}),n.getElementsByTagName=ue(function(e){return e.appendChild(d.createComment("")),!e.getElementsByTagName("*").length}),n.getElementsByClassName=Q.test(d.getElementsByClassName),n.getById=ue(function(e){return h.appendChild(e).id=b,!d.getElementsByName||!d.getElementsByName(b).length}),n.getById?(r.filter.ID=function(e){var t=e.replace(Z,ee);return function(e){return e.getAttribute("id")===t}},r.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&g){var n=t.getElementById(e);return n?[n]:[]}}):(r.filter.ID=function(e){var t=e.replace(Z,ee);return function(e){var n="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return n&&n.value===t}},r.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&g){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),r.find.TAG=n.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):n.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},r.find.CLASS=n.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&g)return t.getElementsByClassName(e)},v=[],y=[],(n.qsa=Q.test(d.querySelectorAll))&&(ue(function(e){h.appendChild(e).innerHTML="<a id='"+b+"'></a><select id='"+b+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&y.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||y.push("\\["+M+"*(?:value|"+P+")"),e.querySelectorAll("[id~="+b+"-]").length||y.push("~="),e.querySelectorAll(":checked").length||y.push(":checked"),e.querySelectorAll("a#"+b+"+*").length||y.push(".#.+[+~]")}),ue(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=d.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&y.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&y.push(":enabled",":disabled"),h.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&y.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),y.push(",.*:")})),(n.matchesSelector=Q.test(m=h.matches||h.webkitMatchesSelector||h.mozMatchesSelector||h.oMatchesSelector||h.msMatchesSelector))&&ue(function(e){n.disconnectedMatch=m.call(e,"*"),m.call(e,"[s!='']:x"),v.push("!=",W)}),y=y.length&&new RegExp(y.join("|")),v=v.length&&new RegExp(v.join("|")),t=Q.test(h.compareDocumentPosition),x=t||Q.test(h.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},D=t?function(e,t){if(e===t)return f=!0,0;var r=!e.compareDocumentPosition-!t.compareDocumentPosition;return r||(1&(r=(e.ownerDocument||e)===(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!n.sortDetached&&t.compareDocumentPosition(e)===r?e===d||e.ownerDocument===w&&x(w,e)?-1:t===d||t.ownerDocument===w&&x(w,t)?1:c?O(c,e)-O(c,t):0:4&r?-1:1)}:function(e,t){if(e===t)return f=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e===d?-1:t===d?1:i?-1:o?1:c?O(c,e)-O(c,t):0;if(i===o)return ce(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?ce(a[r],s[r]):a[r]===w?-1:s[r]===w?1:0},d):d},oe.matches=function(e,t){return oe(e,null,null,t)},oe.matchesSelector=function(e,t){if((e.ownerDocument||e)!==d&&p(e),t=t.replace(z,"='$1']"),n.matchesSelector&&g&&!S[t+" "]&&(!v||!v.test(t))&&(!y||!y.test(t)))try{var r=m.call(e,t);if(r||n.disconnectedMatch||e.document&&11!==e.document.nodeType)return r}catch(e){}return oe(t,d,null,[e]).length>0},oe.contains=function(e,t){return(e.ownerDocument||e)!==d&&p(e),x(e,t)},oe.attr=function(e,t){(e.ownerDocument||e)!==d&&p(e);var i=r.attrHandle[t.toLowerCase()],o=i&&N.call(r.attrHandle,t.toLowerCase())?i(e,t,!g):void 0;return void 0!==o?o:n.attributes||!g?e.getAttribute(t):(o=e.getAttributeNode(t))&&o.specified?o.value:null},oe.escape=function(e){return(e+"").replace(te,ne)},oe.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},oe.uniqueSort=function(e){var t,r=[],i=0,o=0;if(f=!n.detectDuplicates,c=!n.sortStable&&e.slice(0),e.sort(D),f){while(t=e[o++])t===e[o]&&(i=r.push(o));while(i--)e.splice(r[i],1)}return c=null,e},i=oe.getText=function(e){var t,n="",r=0,o=e.nodeType;if(o){if(1===o||9===o||11===o){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=i(e)}else if(3===o||4===o)return e.nodeValue}else while(t=e[r++])n+=i(t);return n},(r=oe.selectors={cacheLength:50,createPseudo:se,match:V,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(Z,ee),e[3]=(e[3]||e[4]||e[5]||"").replace(Z,ee),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||oe.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&oe.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return V.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=a(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(Z,ee).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=E[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&E(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(e,t,n){return function(r){var i=oe.attr(r,e);return null==i?"!="===t:!t||(i+="","="===t?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.slice(-n.length)===n:"~="===t?(" "+i.replace($," ")+" ").indexOf(n)>-1:"|="===t&&(i===n||i.slice(0,n.length+1)===n+"-"))}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,f,p,d,h,g=o!==a?"nextSibling":"previousSibling",y=t.parentNode,v=s&&t.nodeName.toLowerCase(),m=!u&&!s,x=!1;if(y){if(o){while(g){p=t;while(p=p[g])if(s?p.nodeName.toLowerCase()===v:1===p.nodeType)return!1;h=g="only"===e&&!h&&"nextSibling"}return!0}if(h=[a?y.firstChild:y.lastChild],a&&m){x=(d=(l=(c=(f=(p=y)[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]||[])[0]===T&&l[1])&&l[2],p=d&&y.childNodes[d];while(p=++d&&p&&p[g]||(x=d=0)||h.pop())if(1===p.nodeType&&++x&&p===t){c[e]=[T,d,x];break}}else if(m&&(x=d=(l=(c=(f=(p=t)[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]||[])[0]===T&&l[1]),!1===x)while(p=++d&&p&&p[g]||(x=d=0)||h.pop())if((s?p.nodeName.toLowerCase()===v:1===p.nodeType)&&++x&&(m&&((c=(f=p[b]||(p[b]={}))[p.uniqueID]||(f[p.uniqueID]={}))[e]=[T,x]),p===t))break;return(x-=i)===r||x%r==0&&x/r>=0}}},PSEUDO:function(e,t){var n,i=r.pseudos[e]||r.setFilters[e.toLowerCase()]||oe.error("unsupported pseudo: "+e);return i[b]?i(t):i.length>1?(n=[e,e,"",t],r.setFilters.hasOwnProperty(e.toLowerCase())?se(function(e,n){var r,o=i(e,t),a=o.length;while(a--)e[r=O(e,o[a])]=!(n[r]=o[a])}):function(e){return i(e,0,n)}):i}},pseudos:{not:se(function(e){var t=[],n=[],r=s(e.replace(B,"$1"));return r[b]?se(function(e,t,n,i){var o,a=r(e,null,i,[]),s=e.length;while(s--)(o=a[s])&&(e[s]=!(t[s]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),t[0]=null,!n.pop()}}),has:se(function(e){return function(t){return oe(e,t).length>0}}),contains:se(function(e){return e=e.replace(Z,ee),function(t){return(t.textContent||t.innerText||i(t)).indexOf(e)>-1}}),lang:se(function(e){return U.test(e||"")||oe.error("unsupported lang: "+e),e=e.replace(Z,ee).toLowerCase(),function(t){var n;do{if(n=g?t.lang:t.getAttribute("xml:lang")||t.getAttribute("lang"))return(n=n.toLowerCase())===e||0===n.indexOf(e+"-")}while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===h},focus:function(e){return e===d.activeElement&&(!d.hasFocus||d.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:de(!1),disabled:de(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!r.pseudos.empty(e)},header:function(e){return Y.test(e.nodeName)},input:function(e){return G.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:he(function(){return[0]}),last:he(function(e,t){return[t-1]}),eq:he(function(e,t,n){return[n<0?n+t:n]}),even:he(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:he(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:he(function(e,t,n){for(var r=n<0?n+t:n;--r>=0;)e.push(r);return e}),gt:he(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=r.pseudos.eq;for(t in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})r.pseudos[t]=fe(t);for(t in{submit:!0,reset:!0})r.pseudos[t]=pe(t);function ye(){}ye.prototype=r.filters=r.pseudos,r.setFilters=new ye,a=oe.tokenize=function(e,t){var n,i,o,a,s,u,l,c=k[e+" "];if(c)return t?0:c.slice(0);s=e,u=[],l=r.preFilter;while(s){n&&!(i=F.exec(s))||(i&&(s=s.slice(i[0].length)||s),u.push(o=[])),n=!1,(i=_.exec(s))&&(n=i.shift(),o.push({value:n,type:i[0].replace(B," ")}),s=s.slice(n.length));for(a in r.filter)!(i=V[a].exec(s))||l[a]&&!(i=l[a](i))||(n=i.shift(),o.push({value:n,type:a,matches:i}),s=s.slice(n.length));if(!n)break}return t?s.length:s?oe.error(e):k(e,u).slice(0)};function ve(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function me(e,t,n){var r=t.dir,i=t.next,o=i||r,a=n&&"parentNode"===o,s=C++;return t.first?function(t,n,i){while(t=t[r])if(1===t.nodeType||a)return e(t,n,i);return!1}:function(t,n,u){var l,c,f,p=[T,s];if(u){while(t=t[r])if((1===t.nodeType||a)&&e(t,n,u))return!0}else while(t=t[r])if(1===t.nodeType||a)if(f=t[b]||(t[b]={}),c=f[t.uniqueID]||(f[t.uniqueID]={}),i&&i===t.nodeName.toLowerCase())t=t[r]||t;else{if((l=c[o])&&l[0]===T&&l[1]===s)return p[2]=l[2];if(c[o]=p,p[2]=e(t,n,u))return!0}return!1}}function xe(e){return e.length>1?function(t,n,r){var i=e.length;while(i--)if(!e[i](t,n,r))return!1;return!0}:e[0]}function be(e,t,n){for(var r=0,i=t.length;r<i;r++)oe(e,t[r],n);return n}function we(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Te(e,t,n,r,i,o){return r&&!r[b]&&(r=Te(r)),i&&!i[b]&&(i=Te(i,o)),se(function(o,a,s,u){var l,c,f,p=[],d=[],h=a.length,g=o||be(t||"*",s.nodeType?[s]:s,[]),y=!e||!o&&t?g:we(g,p,e,s,u),v=n?i||(o?e:h||r)?[]:a:y;if(n&&n(y,v,s,u),r){l=we(v,d),r(l,[],s,u),c=l.length;while(c--)(f=l[c])&&(v[d[c]]=!(y[d[c]]=f))}if(o){if(i||e){if(i){l=[],c=v.length;while(c--)(f=v[c])&&l.push(y[c]=f);i(null,v=[],l,u)}c=v.length;while(c--)(f=v[c])&&(l=i?O(o,f):p[c])>-1&&(o[l]=!(a[l]=f))}}else v=we(v===a?v.splice(h,v.length):v),i?i(null,a,v,u):L.apply(a,v)})}function Ce(e){for(var t,n,i,o=e.length,a=r.relative[e[0].type],s=a||r.relative[" "],u=a?1:0,c=me(function(e){return e===t},s,!0),f=me(function(e){return O(t,e)>-1},s,!0),p=[function(e,n,r){var i=!a&&(r||n!==l)||((t=n).nodeType?c(e,n,r):f(e,n,r));return t=null,i}];u<o;u++)if(n=r.relative[e[u].type])p=[me(xe(p),n)];else{if((n=r.filter[e[u].type].apply(null,e[u].matches))[b]){for(i=++u;i<o;i++)if(r.relative[e[i].type])break;return Te(u>1&&xe(p),u>1&&ve(e.slice(0,u-1).concat({value:" "===e[u-2].type?"*":""})).replace(B,"$1"),n,u<i&&Ce(e.slice(u,i)),i<o&&Ce(e=e.slice(i)),i<o&&ve(e))}p.push(n)}return xe(p)}function Ee(e,t){var n=t.length>0,i=e.length>0,o=function(o,a,s,u,c){var f,h,y,v=0,m="0",x=o&&[],b=[],w=l,C=o||i&&r.find.TAG("*",c),E=T+=null==w?1:Math.random()||.1,k=C.length;for(c&&(l=a===d||a||c);m!==k&&null!=(f=C[m]);m++){if(i&&f){h=0,a||f.ownerDocument===d||(p(f),s=!g);while(y=e[h++])if(y(f,a||d,s)){u.push(f);break}c&&(T=E)}n&&((f=!y&&f)&&v--,o&&x.push(f))}if(v+=m,n&&m!==v){h=0;while(y=t[h++])y(x,b,a,s);if(o){if(v>0)while(m--)x[m]||b[m]||(b[m]=j.call(u));b=we(b)}L.apply(u,b),c&&!o&&b.length>0&&v+t.length>1&&oe.uniqueSort(u)}return c&&(T=E,l=w),x};return n?se(o):o}return s=oe.compile=function(e,t){var n,r=[],i=[],o=S[e+" "];if(!o){t||(t=a(e)),n=t.length;while(n--)(o=Ce(t[n]))[b]?r.push(o):i.push(o);(o=S(e,Ee(i,r))).selector=e}return o},u=oe.select=function(e,t,n,i){var o,u,l,c,f,p="function"==typeof e&&e,d=!i&&a(e=p.selector||e);if(n=n||[],1===d.length){if((u=d[0]=d[0].slice(0)).length>2&&"ID"===(l=u[0]).type&&9===t.nodeType&&g&&r.relative[u[1].type]){if(!(t=(r.find.ID(l.matches[0].replace(Z,ee),t)||[])[0]))return n;p&&(t=t.parentNode),e=e.slice(u.shift().value.length)}o=V.needsContext.test(e)?0:u.length;while(o--){if(l=u[o],r.relative[c=l.type])break;if((f=r.find[c])&&(i=f(l.matches[0].replace(Z,ee),K.test(u[0].type)&&ge(t.parentNode)||t))){if(u.splice(o,1),!(e=i.length&&ve(u)))return L.apply(n,i),n;break}}}return(p||s(e,d))(i,t,!g,n,!t||K.test(e)&&ge(t.parentNode)||t),n},n.sortStable=b.split("").sort(D).join("")===b,n.detectDuplicates=!!f,p(),n.sortDetached=ue(function(e){return 1&e.compareDocumentPosition(d.createElement("fieldset"))}),ue(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||le("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),n.attributes&&ue(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||le("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ue(function(e){return null==e.getAttribute("disabled")})||le(P,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),oe}(e);w.find=E,w.expr=E.selectors,w.expr[":"]=w.expr.pseudos,w.uniqueSort=w.unique=E.uniqueSort,w.text=E.getText,w.isXMLDoc=E.isXML,w.contains=E.contains,w.escapeSelector=E.escape;var k=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&w(e).is(n))break;r.push(e)}return r},S=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},D=w.expr.match.needsContext;function N(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var A=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function j(e,t,n){return g(t)?w.grep(e,function(e,r){return!!t.call(e,r,e)!==n}):t.nodeType?w.grep(e,function(e){return e===t!==n}):"string"!=typeof t?w.grep(e,function(e){return u.call(t,e)>-1!==n}):w.filter(t,e,n)}w.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?w.find.matchesSelector(r,e)?[r]:[]:w.find.matches(e,w.grep(t,function(e){return 1===e.nodeType}))},w.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(w(e).filter(function(){for(t=0;t<r;t++)if(w.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)w.find(e,i[t],n);return r>1?w.uniqueSort(n):n},filter:function(e){return this.pushStack(j(this,e||[],!1))},not:function(e){return this.pushStack(j(this,e||[],!0))},is:function(e){return!!j(this,"string"==typeof e&&D.test(e)?w(e):e||[],!1).length}});var q,L=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(w.fn.init=function(e,t,n){var i,o;if(!e)return this;if(n=n||q,"string"==typeof e){if(!(i="<"===e[0]&&">"===e[e.length-1]&&e.length>=3?[null,e,null]:L.exec(e))||!i[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(i[1]){if(t=t instanceof w?t[0]:t,w.merge(this,w.parseHTML(i[1],t&&t.nodeType?t.ownerDocument||t:r,!0)),A.test(i[1])&&w.isPlainObject(t))for(i in t)g(this[i])?this[i](t[i]):this.attr(i,t[i]);return this}return(o=r.getElementById(i[2]))&&(this[0]=o,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):g(e)?void 0!==n.ready?n.ready(e):e(w):w.makeArray(e,this)}).prototype=w.fn,q=w(r);var H=/^(?:parents|prev(?:Until|All))/,O={children:!0,contents:!0,next:!0,prev:!0};w.fn.extend({has:function(e){var t=w(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(w.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&w(e);if(!D.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?a.index(n)>-1:1===n.nodeType&&w.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(o.length>1?w.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?u.call(w(e),this[0]):u.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(w.uniqueSort(w.merge(this.get(),w(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}});function P(e,t){while((e=e[t])&&1!==e.nodeType);return e}w.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return k(e,"parentNode")},parentsUntil:function(e,t,n){return k(e,"parentNode",n)},next:function(e){return P(e,"nextSibling")},prev:function(e){return P(e,"previousSibling")},nextAll:function(e){return k(e,"nextSibling")},prevAll:function(e){return k(e,"previousSibling")},nextUntil:function(e,t,n){return k(e,"nextSibling",n)},prevUntil:function(e,t,n){return k(e,"previousSibling",n)},siblings:function(e){return S((e.parentNode||{}).firstChild,e)},children:function(e){return S(e.firstChild)},contents:function(e){return N(e,"iframe")?e.contentDocument:(N(e,"template")&&(e=e.content||e),w.merge([],e.childNodes))}},function(e,t){w.fn[e]=function(n,r){var i=w.map(this,t,n);return"Until"!==e.slice(-5)&&(r=n),r&&"string"==typeof r&&(i=w.filter(r,i)),this.length>1&&(O[e]||w.uniqueSort(i),H.test(e)&&i.reverse()),this.pushStack(i)}});var M=/[^\x20\t\r\n\f]+/g;function R(e){var t={};return w.each(e.match(M)||[],function(e,n){t[n]=!0}),t}w.Callbacks=function(e){e="string"==typeof e?R(e):w.extend({},e);var t,n,r,i,o=[],a=[],s=-1,u=function(){for(i=i||e.once,r=t=!0;a.length;s=-1){n=a.shift();while(++s<o.length)!1===o[s].apply(n[0],n[1])&&e.stopOnFalse&&(s=o.length,n=!1)}e.memory||(n=!1),t=!1,i&&(o=n?[]:"")},l={add:function(){return o&&(n&&!t&&(s=o.length-1,a.push(n)),function t(n){w.each(n,function(n,r){g(r)?e.unique&&l.has(r)||o.push(r):r&&r.length&&"string"!==x(r)&&t(r)})}(arguments),n&&!t&&u()),this},remove:function(){return w.each(arguments,function(e,t){var n;while((n=w.inArray(t,o,n))>-1)o.splice(n,1),n<=s&&s--}),this},has:function(e){return e?w.inArray(e,o)>-1:o.length>0},empty:function(){return o&&(o=[]),this},disable:function(){return i=a=[],o=n="",this},disabled:function(){return!o},lock:function(){return i=a=[],n||t||(o=n=""),this},locked:function(){return!!i},fireWith:function(e,n){return i||(n=[e,(n=n||[]).slice?n.slice():n],a.push(n),t||u()),this},fire:function(){return l.fireWith(this,arguments),this},fired:function(){return!!r}};return l};function I(e){return e}function W(e){throw e}function $(e,t,n,r){var i;try{e&&g(i=e.promise)?i.call(e).done(t).fail(n):e&&g(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}w.extend({Deferred:function(t){var n=[["notify","progress",w.Callbacks("memory"),w.Callbacks("memory"),2],["resolve","done",w.Callbacks("once memory"),w.Callbacks("once memory"),0,"resolved"],["reject","fail",w.Callbacks("once memory"),w.Callbacks("once memory"),1,"rejected"]],r="pending",i={state:function(){return r},always:function(){return o.done(arguments).fail(arguments),this},"catch":function(e){return i.then(null,e)},pipe:function(){var e=arguments;return w.Deferred(function(t){w.each(n,function(n,r){var i=g(e[r[4]])&&e[r[4]];o[r[1]](function(){var e=i&&i.apply(this,arguments);e&&g(e.promise)?e.promise().progress(t.notify).done(t.resolve).fail(t.reject):t[r[0]+"With"](this,i?[e]:arguments)})}),e=null}).promise()},then:function(t,r,i){var o=0;function a(t,n,r,i){return function(){var s=this,u=arguments,l=function(){var e,l;if(!(t<o)){if((e=r.apply(s,u))===n.promise())throw new TypeError("Thenable self-resolution");l=e&&("object"==typeof e||"function"==typeof e)&&e.then,g(l)?i?l.call(e,a(o,n,I,i),a(o,n,W,i)):(o++,l.call(e,a(o,n,I,i),a(o,n,W,i),a(o,n,I,n.notifyWith))):(r!==I&&(s=void 0,u=[e]),(i||n.resolveWith)(s,u))}},c=i?l:function(){try{l()}catch(e){w.Deferred.exceptionHook&&w.Deferred.exceptionHook(e,c.stackTrace),t+1>=o&&(r!==W&&(s=void 0,u=[e]),n.rejectWith(s,u))}};t?c():(w.Deferred.getStackHook&&(c.stackTrace=w.Deferred.getStackHook()),e.setTimeout(c))}}return w.Deferred(function(e){n[0][3].add(a(0,e,g(i)?i:I,e.notifyWith)),n[1][3].add(a(0,e,g(t)?t:I)),n[2][3].add(a(0,e,g(r)?r:W))}).promise()},promise:function(e){return null!=e?w.extend(e,i):i}},o={};return w.each(n,function(e,t){var a=t[2],s=t[5];i[t[1]]=a.add,s&&a.add(function(){r=s},n[3-e][2].disable,n[3-e][3].disable,n[0][2].lock,n[0][3].lock),a.add(t[3].fire),o[t[0]]=function(){return o[t[0]+"With"](this===o?void 0:this,arguments),this},o[t[0]+"With"]=a.fireWith}),i.promise(o),t&&t.call(o,o),o},when:function(e){var t=arguments.length,n=t,r=Array(n),i=o.call(arguments),a=w.Deferred(),s=function(e){return function(n){r[e]=this,i[e]=arguments.length>1?o.call(arguments):n,--t||a.resolveWith(r,i)}};if(t<=1&&($(e,a.done(s(n)).resolve,a.reject,!t),"pending"===a.state()||g(i[n]&&i[n].then)))return a.then();while(n--)$(i[n],s(n),a.reject);return a.promise()}});var B=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;w.Deferred.exceptionHook=function(t,n){e.console&&e.console.warn&&t&&B.test(t.name)&&e.console.warn("jQuery.Deferred exception: "+t.message,t.stack,n)},w.readyException=function(t){e.setTimeout(function(){throw t})};var F=w.Deferred();w.fn.ready=function(e){return F.then(e)["catch"](function(e){w.readyException(e)}),this},w.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--w.readyWait:w.isReady)||(w.isReady=!0,!0!==e&&--w.readyWait>0||F.resolveWith(r,[w]))}}),w.ready.then=F.then;function _(){r.removeEventListener("DOMContentLoaded",_),e.removeEventListener("load",_),w.ready()}"complete"===r.readyState||"loading"!==r.readyState&&!r.documentElement.doScroll?e.setTimeout(w.ready):(r.addEventListener("DOMContentLoaded",_),e.addEventListener("load",_));var z=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===x(n)){i=!0;for(s in n)z(e,t,s,n[s],!0,o,a)}else if(void 0!==r&&(i=!0,g(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(w(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},X=/^-ms-/,U=/-([a-z])/g;function V(e,t){return t.toUpperCase()}function G(e){return e.replace(X,"ms-").replace(U,V)}var Y=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function Q(){this.expando=w.expando+Q.uid++}Q.uid=1,Q.prototype={cache:function(e){var t=e[this.expando];return t||(t={},Y(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[G(t)]=n;else for(r in t)i[G(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][G(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(G):(t=G(t))in r?[t]:t.match(M)||[]).length;while(n--)delete r[t[n]]}(void 0===t||w.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!w.isEmptyObject(t)}};var J=new Q,K=new Q,Z=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,ee=/[A-Z]/g;function te(e){return"true"===e||"false"!==e&&("null"===e?null:e===+e+""?+e:Z.test(e)?JSON.parse(e):e)}function ne(e,t,n){var r;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(ee,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n=te(n)}catch(e){}K.set(e,t,n)}else n=void 0;return n}w.extend({hasData:function(e){return K.hasData(e)||J.hasData(e)},data:function(e,t,n){return K.access(e,t,n)},removeData:function(e,t){K.remove(e,t)},_data:function(e,t,n){return J.access(e,t,n)},_removeData:function(e,t){J.remove(e,t)}}),w.fn.extend({data:function(e,t){var n,r,i,o=this[0],a=o&&o.attributes;if(void 0===e){if(this.length&&(i=K.get(o),1===o.nodeType&&!J.get(o,"hasDataAttrs"))){n=a.length;while(n--)a[n]&&0===(r=a[n].name).indexOf("data-")&&(r=G(r.slice(5)),ne(o,r,i[r]));J.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof e?this.each(function(){K.set(this,e)}):z(this,function(t){var n;if(o&&void 0===t){if(void 0!==(n=K.get(o,e)))return n;if(void 0!==(n=ne(o,e)))return n}else this.each(function(){K.set(this,e,t)})},null,t,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){K.remove(this,e)})}}),w.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=J.get(e,t),n&&(!r||Array.isArray(n)?r=J.access(e,t,w.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=w.queue(e,t),r=n.length,i=n.shift(),o=w._queueHooks(e,t),a=function(){w.dequeue(e,t)};"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,a,o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return J.get(e,n)||J.access(e,n,{empty:w.Callbacks("once memory").add(function(){J.remove(e,[t+"queue",n])})})}}),w.fn.extend({queue:function(e,t){var n=2;return"string"!=typeof e&&(t=e,e="fx",n--),arguments.length<n?w.queue(this[0],e):void 0===t?this:this.each(function(){var n=w.queue(this,e,t);w._queueHooks(this,e),"fx"===e&&"inprogress"!==n[0]&&w.dequeue(this,e)})},dequeue:function(e){return this.each(function(){w.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=w.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=J.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var re=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,ie=new RegExp("^(?:([+-])=|)("+re+")([a-z%]*)$","i"),oe=["Top","Right","Bottom","Left"],ae=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&w.contains(e.ownerDocument,e)&&"none"===w.css(e,"display")},se=function(e,t,n,r){var i,o,a={};for(o in t)a[o]=e.style[o],e.style[o]=t[o];i=n.apply(e,r||[]);for(o in t)e.style[o]=a[o];return i};function ue(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return w.css(e,t,"")},u=s(),l=n&&n[3]||(w.cssNumber[t]?"":"px"),c=(w.cssNumber[t]||"px"!==l&&+u)&&ie.exec(w.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)w.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,w.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var le={};function ce(e){var t,n=e.ownerDocument,r=e.nodeName,i=le[r];return i||(t=n.body.appendChild(n.createElement(r)),i=w.css(t,"display"),t.parentNode.removeChild(t),"none"===i&&(i="block"),le[r]=i,i)}function fe(e,t){for(var n,r,i=[],o=0,a=e.length;o<a;o++)(r=e[o]).style&&(n=r.style.display,t?("none"===n&&(i[o]=J.get(r,"display")||null,i[o]||(r.style.display="")),""===r.style.display&&ae(r)&&(i[o]=ce(r))):"none"!==n&&(i[o]="none",J.set(r,"display",n)));for(o=0;o<a;o++)null!=i[o]&&(e[o].style.display=i[o]);return e}w.fn.extend({show:function(){return fe(this,!0)},hide:function(){return fe(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ae(this)?w(this).show():w(this).hide()})}});var pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]+)/i,he=/^$|^module$|\/(?:java|ecma)script/i,ge={option:[1,"<select multiple='multiple'>","</select>"],thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};ge.optgroup=ge.option,ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td;function ye(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&N(e,t)?w.merge([e],n):n}function ve(e,t){for(var n=0,r=e.length;n<r;n++)J.set(e[n],"globalEval",!t||J.get(t[n],"globalEval"))}var me=/<|&#?\w+;/;function xe(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===x(o))w.merge(p,o.nodeType?[o]:o);else if(me.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+w.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;w.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&w.inArray(o,r)>-1)i&&i.push(o);else if(l=w.contains(o.ownerDocument,o),a=ye(f.appendChild(o),"script"),l&&ve(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}!function(){var e=r.createDocumentFragment().appendChild(r.createElement("div")),t=r.createElement("input");t.setAttribute("type","radio"),t.setAttribute("checked","checked"),t.setAttribute("name","t"),e.appendChild(t),h.checkClone=e.cloneNode(!0).cloneNode(!0).lastChild.checked,e.innerHTML="<textarea>x</textarea>",h.noCloneChecked=!!e.cloneNode(!0).lastChild.defaultValue}();var be=r.documentElement,we=/^key/,Te=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,Ce=/^([^.]*)(?:\.(.+)|)/;function Ee(){return!0}function ke(){return!1}function Se(){try{return r.activeElement}catch(e){}}function De(e,t,n,r,i,o){var a,s;if("object"==typeof t){"string"!=typeof n&&(r=r||n,n=void 0);for(s in t)De(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=ke;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return w().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=w.guid++)),e.each(function(){w.event.add(this,t,i,r,n)})}w.event={global:{},add:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=J.get(e);if(y){n.handler&&(n=(o=n).handler,i=o.selector),i&&w.find.matchesSelector(be,i),n.guid||(n.guid=w.guid++),(u=y.events)||(u=y.events={}),(a=y.handle)||(a=y.handle=function(t){return"undefined"!=typeof w&&w.event.triggered!==t.type?w.event.dispatch.apply(e,arguments):void 0}),l=(t=(t||"").match(M)||[""]).length;while(l--)d=g=(s=Ce.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=w.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=w.event.special[d]||{},c=w.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&w.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(e,r,h,a)||e.addEventListener&&e.addEventListener(d,a)),f.add&&(f.add.call(e,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),w.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=J.hasData(e)&&J.get(e);if(y&&(u=y.events)){l=(t=(t||"").match(M)||[""]).length;while(l--)if(s=Ce.exec(t[l])||[],d=g=s[1],h=(s[2]||"").split(".").sort(),d){f=w.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,y.handle)||w.removeEvent(e,d,y.handle),delete u[d])}else for(d in u)w.event.remove(e,d+t[l],n,r,!0);w.isEmptyObject(u)&&J.remove(e,"handle events")}},dispatch:function(e){var t=w.event.fix(e),n,r,i,o,a,s,u=new Array(arguments.length),l=(J.get(this,"events")||{})[t.type]||[],c=w.event.special[t.type]||{};for(u[0]=t,n=1;n<arguments.length;n++)u[n]=arguments[n];if(t.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,t)){s=w.event.handlers.call(this,t,l),n=0;while((o=s[n++])&&!t.isPropagationStopped()){t.currentTarget=o.elem,r=0;while((a=o.handlers[r++])&&!t.isImmediatePropagationStopped())t.rnamespace&&!t.rnamespace.test(a.namespace)||(t.handleObj=a,t.data=a.data,void 0!==(i=((w.event.special[a.origType]||{}).handle||a.handler).apply(o.elem,u))&&!1===(t.result=i)&&(t.preventDefault(),t.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,t),t.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&e.button>=1))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?w(i,this).index(l)>-1:w.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(e,t){Object.defineProperty(w.Event.prototype,e,{enumerable:!0,configurable:!0,get:g(t)?function(){if(this.originalEvent)return t(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[e]},set:function(t){Object.defineProperty(this,e,{enumerable:!0,configurable:!0,writable:!0,value:t})}})},fix:function(e){return e[w.expando]?e:new w.Event(e)},special:{load:{noBubble:!0},focus:{trigger:function(){if(this!==Se()&&this.focus)return this.focus(),!1},delegateType:"focusin"},blur:{trigger:function(){if(this===Se()&&this.blur)return this.blur(),!1},delegateType:"focusout"},click:{trigger:function(){if("checkbox"===this.type&&this.click&&N(this,"input"))return this.click(),!1},_default:function(e){return N(e.target,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},w.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},w.Event=function(e,t){if(!(this instanceof w.Event))return new w.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?Ee:ke,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&w.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[w.expando]=!0},w.Event.prototype={constructor:w.Event,isDefaultPrevented:ke,isPropagationStopped:ke,isImmediatePropagationStopped:ke,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Ee,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Ee,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Ee,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},w.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:function(e){var t=e.button;return null==e.which&&we.test(e.type)?null!=e.charCode?e.charCode:e.keyCode:!e.which&&void 0!==t&&Te.test(e.type)?1&t?1:2&t?3:4&t?2:0:e.which}},w.event.addProp),w.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,t){w.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,o=e.handleObj;return i&&(i===r||w.contains(r,i))||(e.type=o.origType,n=o.handler.apply(this,arguments),e.type=t),n}}}),w.fn.extend({on:function(e,t,n,r){return De(this,e,t,n,r)},one:function(e,t,n,r){return De(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,w(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=ke),this.each(function(){w.event.remove(this,e,n,t)})}});var Ne=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,Ae=/<script|<style|<link/i,je=/checked\s*(?:[^=]|=\s*.checked.)/i,qe=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function Le(e,t){return N(e,"table")&&N(11!==t.nodeType?t:t.firstChild,"tr")?w(e).children("tbody")[0]||e:e}function He(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function Oe(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Pe(e,t){var n,r,i,o,a,s,u,l;if(1===t.nodeType){if(J.hasData(e)&&(o=J.access(e),a=J.set(t,o),l=o.events)){delete a.handle,a.events={};for(i in l)for(n=0,r=l[i].length;n<r;n++)w.event.add(t,i,l[i][n])}K.hasData(e)&&(s=K.access(e),u=w.extend({},s),K.set(t,u))}}function Me(e,t){var n=t.nodeName.toLowerCase();"input"===n&&pe.test(e.type)?t.checked=e.checked:"input"!==n&&"textarea"!==n||(t.defaultValue=e.defaultValue)}function Re(e,t,n,r){t=a.apply([],t);var i,o,s,u,l,c,f=0,p=e.length,d=p-1,y=t[0],v=g(y);if(v||p>1&&"string"==typeof y&&!h.checkClone&&je.test(y))return e.each(function(i){var o=e.eq(i);v&&(t[0]=y.call(this,i,o.html())),Re(o,t,n,r)});if(p&&(i=xe(t,e[0].ownerDocument,!1,e,r),o=i.firstChild,1===i.childNodes.length&&(i=o),o||r)){for(u=(s=w.map(ye(i,"script"),He)).length;f<p;f++)l=i,f!==d&&(l=w.clone(l,!0,!0),u&&w.merge(s,ye(l,"script"))),n.call(e[f],l,f);if(u)for(c=s[s.length-1].ownerDocument,w.map(s,Oe),f=0;f<u;f++)l=s[f],he.test(l.type||"")&&!J.access(l,"globalEval")&&w.contains(c,l)&&(l.src&&"module"!==(l.type||"").toLowerCase()?w._evalUrl&&w._evalUrl(l.src):m(l.textContent.replace(qe,""),c,l))}return e}function Ie(e,t,n){for(var r,i=t?w.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||w.cleanData(ye(r)),r.parentNode&&(n&&w.contains(r.ownerDocument,r)&&ve(ye(r,"script")),r.parentNode.removeChild(r));return e}w.extend({htmlPrefilter:function(e){return e.replace(Ne,"<$1></$2>")},clone:function(e,t,n){var r,i,o,a,s=e.cloneNode(!0),u=w.contains(e.ownerDocument,e);if(!(h.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||w.isXMLDoc(e)))for(a=ye(s),r=0,i=(o=ye(e)).length;r<i;r++)Me(o[r],a[r]);if(t)if(n)for(o=o||ye(e),a=a||ye(s),r=0,i=o.length;r<i;r++)Pe(o[r],a[r]);else Pe(e,s);return(a=ye(s,"script")).length>0&&ve(a,!u&&ye(e,"script")),s},cleanData:function(e){for(var t,n,r,i=w.event.special,o=0;void 0!==(n=e[o]);o++)if(Y(n)){if(t=n[J.expando]){if(t.events)for(r in t.events)i[r]?w.event.remove(n,r):w.removeEvent(n,r,t.handle);n[J.expando]=void 0}n[K.expando]&&(n[K.expando]=void 0)}}}),w.fn.extend({detach:function(e){return Ie(this,e,!0)},remove:function(e){return Ie(this,e)},text:function(e){return z(this,function(e){return void 0===e?w.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return Re(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||Le(this,e).appendChild(e)})},prepend:function(){return Re(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=Le(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return Re(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return Re(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(w.cleanData(ye(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return w.clone(this,e,t)})},html:function(e){return z(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!Ae.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=w.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(w.cleanData(ye(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var e=[];return Re(this,arguments,function(t){var n=this.parentNode;w.inArray(this,e)<0&&(w.cleanData(ye(this)),n&&n.replaceChild(t,this))},e)}}),w.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){w.fn[e]=function(e){for(var n,r=[],i=w(e),o=i.length-1,a=0;a<=o;a++)n=a===o?this:this.clone(!0),w(i[a])[t](n),s.apply(r,n.get());return this.pushStack(r)}});var We=new RegExp("^("+re+")(?!px)[a-z%]+$","i"),$e=function(t){var n=t.ownerDocument.defaultView;return n&&n.opener||(n=e),n.getComputedStyle(t)},Be=new RegExp(oe.join("|"),"i");!function(){function t(){if(c){l.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",c.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",be.appendChild(l).appendChild(c);var t=e.getComputedStyle(c);i="1%"!==t.top,u=12===n(t.marginLeft),c.style.right="60%",s=36===n(t.right),o=36===n(t.width),c.style.position="absolute",a=36===c.offsetWidth||"absolute",be.removeChild(l),c=null}}function n(e){return Math.round(parseFloat(e))}var i,o,a,s,u,l=r.createElement("div"),c=r.createElement("div");c.style&&(c.style.backgroundClip="content-box",c.cloneNode(!0).style.backgroundClip="",h.clearCloneStyle="content-box"===c.style.backgroundClip,w.extend(h,{boxSizingReliable:function(){return t(),o},pixelBoxStyles:function(){return t(),s},pixelPosition:function(){return t(),i},reliableMarginLeft:function(){return t(),u},scrollboxSize:function(){return t(),a}}))}();function Fe(e,t,n){var r,i,o,a,s=e.style;return(n=n||$e(e))&&(""!==(a=n.getPropertyValue(t)||n[t])||w.contains(e.ownerDocument,e)||(a=w.style(e,t)),!h.pixelBoxStyles()&&We.test(a)&&Be.test(t)&&(r=s.width,i=s.minWidth,o=s.maxWidth,s.minWidth=s.maxWidth=s.width=a,a=n.width,s.width=r,s.minWidth=i,s.maxWidth=o)),void 0!==a?a+"":a}function _e(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}var ze=/^(none|table(?!-c[ea]).+)/,Xe=/^--/,Ue={position:"absolute",visibility:"hidden",display:"block"},Ve={letterSpacing:"0",fontWeight:"400"},Ge=["Webkit","Moz","ms"],Ye=r.createElement("div").style;function Qe(e){if(e in Ye)return e;var t=e[0].toUpperCase()+e.slice(1),n=Ge.length;while(n--)if((e=Ge[n]+t)in Ye)return e}function Je(e){var t=w.cssProps[e];return t||(t=w.cssProps[e]=Qe(e)||e),t}function Ke(e,t,n){var r=ie.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function Ze(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=w.css(e,n+oe[a],!0,i)),r?("content"===n&&(u-=w.css(e,"padding"+oe[a],!0,i)),"margin"!==n&&(u-=w.css(e,"border"+oe[a]+"Width",!0,i))):(u+=w.css(e,"padding"+oe[a],!0,i),"padding"!==n?u+=w.css(e,"border"+oe[a]+"Width",!0,i):s+=w.css(e,"border"+oe[a]+"Width",!0,i));return!r&&o>=0&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))),u}function et(e,t,n){var r=$e(e),i=Fe(e,t,r),o="border-box"===w.css(e,"boxSizing",!1,r),a=o;if(We.test(i)){if(!n)return i;i="auto"}return a=a&&(h.boxSizingReliable()||i===e.style[t]),("auto"===i||!parseFloat(i)&&"inline"===w.css(e,"display",!1,r))&&(i=e["offset"+t[0].toUpperCase()+t.slice(1)],a=!0),(i=parseFloat(i)||0)+Ze(e,t,n||(o?"border":"content"),a,r,i)+"px"}w.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Fe(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=G(t),u=Xe.test(t),l=e.style;if(u||(t=Je(s)),a=w.cssHooks[t]||w.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"==(o=typeof n)&&(i=ie.exec(n))&&i[1]&&(n=ue(e,t,i),o="number"),null!=n&&n===n&&("number"===o&&(n+=i&&i[3]||(w.cssNumber[s]?"":"px")),h.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=G(t);return Xe.test(t)||(t=Je(s)),(a=w.cssHooks[t]||w.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=Fe(e,t,r)),"normal"===i&&t in Ve&&(i=Ve[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),w.each(["height","width"],function(e,t){w.cssHooks[t]={get:function(e,n,r){if(n)return!ze.test(w.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?et(e,t,r):se(e,Ue,function(){return et(e,t,r)})},set:function(e,n,r){var i,o=$e(e),a="border-box"===w.css(e,"boxSizing",!1,o),s=r&&Ze(e,t,r,a,o);return a&&h.scrollboxSize()===o.position&&(s-=Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-parseFloat(o[t])-Ze(e,t,"border",!1,o)-.5)),s&&(i=ie.exec(n))&&"px"!==(i[3]||"px")&&(e.style[t]=n,n=w.css(e,t)),Ke(e,n,s)}}}),w.cssHooks.marginLeft=_e(h.reliableMarginLeft,function(e,t){if(t)return(parseFloat(Fe(e,"marginLeft"))||e.getBoundingClientRect().left-se(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),w.each({margin:"",padding:"",border:"Width"},function(e,t){w.cssHooks[e+t]={expand:function(n){for(var r=0,i={},o="string"==typeof n?n.split(" "):[n];r<4;r++)i[e+oe[r]+t]=o[r]||o[r-2]||o[0];return i}},"margin"!==e&&(w.cssHooks[e+t].set=Ke)}),w.fn.extend({css:function(e,t){return z(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=$e(e),i=t.length;a<i;a++)o[t[a]]=w.css(e,t[a],!1,r);return o}return void 0!==n?w.style(e,t,n):w.css(e,t)},e,t,arguments.length>1)}});function tt(e,t,n,r,i){return new tt.prototype.init(e,t,n,r,i)}w.Tween=tt,tt.prototype={constructor:tt,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||w.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(w.cssNumber[n]?"":"px")},cur:function(){var e=tt.propHooks[this.prop];return e&&e.get?e.get(this):tt.propHooks._default.get(this)},run:function(e){var t,n=tt.propHooks[this.prop];return this.options.duration?this.pos=t=w.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):tt.propHooks._default.set(this),this}},tt.prototype.init.prototype=tt.prototype,tt.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=w.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){w.fx.step[e.prop]?w.fx.step[e.prop](e):1!==e.elem.nodeType||null==e.elem.style[w.cssProps[e.prop]]&&!w.cssHooks[e.prop]?e.elem[e.prop]=e.now:w.style(e.elem,e.prop,e.now+e.unit)}}},tt.propHooks.scrollTop=tt.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},w.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},w.fx=tt.prototype.init,w.fx.step={};var nt,rt,it=/^(?:toggle|show|hide)$/,ot=/queueHooks$/;function at(){rt&&(!1===r.hidden&&e.requestAnimationFrame?e.requestAnimationFrame(at):e.setTimeout(at,w.fx.interval),w.fx.tick())}function st(){return e.setTimeout(function(){nt=void 0}),nt=Date.now()}function ut(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=oe[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function lt(e,t,n){for(var r,i=(pt.tweeners[t]||[]).concat(pt.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function ct(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ae(e),y=J.get(e,"fxshow");n.queue||(null==(a=w._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,w.queue(e,"fx").length||a.empty.fire()})}));for(r in t)if(i=t[r],it.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!y||void 0===y[r])continue;g=!0}d[r]=y&&y[r]||w.style(e,r)}if((u=!w.isEmptyObject(t))||!w.isEmptyObject(d)){f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=y&&y.display)&&(l=J.get(e,"display")),"none"===(c=w.css(e,"display"))&&(l?c=l:(fe([e],!0),l=e.style.display||l,c=w.css(e,"display"),fe([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===w.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1;for(r in d)u||(y?"hidden"in y&&(g=y.hidden):y=J.access(e,"fxshow",{display:l}),o&&(y.hidden=!g),g&&fe([e],!0),p.done(function(){g||fe([e]),J.remove(e,"fxshow");for(r in d)w.style(e,r,d[r])})),u=lt(g?y[r]:0,r,p),r in y||(y[r]=u.start,g&&(u.end=u.start,u.start=0))}}function ft(e,t){var n,r,i,o,a;for(n in e)if(r=G(n),i=t[r],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=w.cssHooks[r])&&"expand"in a){o=a.expand(o),delete e[r];for(n in o)n in e||(e[n]=o[n],t[n]=i)}else t[r]=i}function pt(e,t,n){var r,i,o=0,a=pt.prefilters.length,s=w.Deferred().always(function(){delete u.elem}),u=function(){if(i)return!1;for(var t=nt||st(),n=Math.max(0,l.startTime+l.duration-t),r=1-(n/l.duration||0),o=0,a=l.tweens.length;o<a;o++)l.tweens[o].run(r);return s.notifyWith(e,[l,r,n]),r<1&&a?n:(a||s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l]),!1)},l=s.promise({elem:e,props:w.extend({},t),opts:w.extend(!0,{specialEasing:{},easing:w.easing._default},n),originalProperties:t,originalOptions:n,startTime:nt||st(),duration:n.duration,tweens:[],createTween:function(t,n){var r=w.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;n<r;n++)l.tweens[n].run(1);return t?(s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l,t])):s.rejectWith(e,[l,t]),this}}),c=l.props;for(ft(c,l.opts.specialEasing);o<a;o++)if(r=pt.prefilters[o].call(l,e,c,l.opts))return g(r.stop)&&(w._queueHooks(l.elem,l.opts.queue).stop=r.stop.bind(r)),r;return w.map(c,lt,l),g(l.opts.start)&&l.opts.start.call(e,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),w.fx.timer(w.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l}w.Animation=w.extend(pt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return ue(n.elem,e,ie.exec(t),n),n}]},tweener:function(e,t){g(e)?(t=e,e=["*"]):e=e.match(M);for(var n,r=0,i=e.length;r<i;r++)n=e[r],pt.tweeners[n]=pt.tweeners[n]||[],pt.tweeners[n].unshift(t)},prefilters:[ct],prefilter:function(e,t){t?pt.prefilters.unshift(e):pt.prefilters.push(e)}}),w.speed=function(e,t,n){var r=e&&"object"==typeof e?w.extend({},e):{complete:n||!n&&t||g(e)&&e,duration:e,easing:n&&t||t&&!g(t)&&t};return w.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in w.fx.speeds?r.duration=w.fx.speeds[r.duration]:r.duration=w.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){g(r.old)&&r.old.call(this),r.queue&&w.dequeue(this,r.queue)},r},w.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ae).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=w.isEmptyObject(e),o=w.speed(t,n,r),a=function(){var t=pt(this,w.extend({},e),o);(i||J.get(this,"finish"))&&t.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(e,t,n){var r=function(e){var t=e.stop;delete e.stop,t(n)};return"string"!=typeof e&&(n=t,t=e,e=void 0),t&&!1!==e&&this.queue(e||"fx",[]),this.each(function(){var t=!0,i=null!=e&&e+"queueHooks",o=w.timers,a=J.get(this);if(i)a[i]&&a[i].stop&&r(a[i]);else for(i in a)a[i]&&a[i].stop&&ot.test(i)&&r(a[i]);for(i=o.length;i--;)o[i].elem!==this||null!=e&&o[i].queue!==e||(o[i].anim.stop(n),t=!1,o.splice(i,1));!t&&n||w.dequeue(this,e)})},finish:function(e){return!1!==e&&(e=e||"fx"),this.each(function(){var t,n=J.get(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=w.timers,a=r?r.length:0;for(n.finish=!0,w.queue(this,e,[]),i&&i.stop&&i.stop.call(this,!0),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;t<a;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}}),w.each(["toggle","show","hide"],function(e,t){var n=w.fn[t];w.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(ut(t,!0),e,r,i)}}),w.each({slideDown:ut("show"),slideUp:ut("hide"),slideToggle:ut("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){w.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),w.timers=[],w.fx.tick=function(){var e,t=0,n=w.timers;for(nt=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||w.fx.stop(),nt=void 0},w.fx.timer=function(e){w.timers.push(e),w.fx.start()},w.fx.interval=13,w.fx.start=function(){rt||(rt=!0,at())},w.fx.stop=function(){rt=null},w.fx.speeds={slow:600,fast:200,_default:400},w.fn.delay=function(t,n){return t=w.fx?w.fx.speeds[t]||t:t,n=n||"fx",this.queue(n,function(n,r){var i=e.setTimeout(n,t);r.stop=function(){e.clearTimeout(i)}})},function(){var e=r.createElement("input"),t=r.createElement("select").appendChild(r.createElement("option"));e.type="checkbox",h.checkOn=""!==e.value,h.optSelected=t.selected,(e=r.createElement("input")).value="t",e.type="radio",h.radioValue="t"===e.value}();var dt,ht=w.expr.attrHandle;w.fn.extend({attr:function(e,t){return z(this,w.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){w.removeAttr(this,e)})}}),w.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?w.prop(e,t,n):(1===o&&w.isXMLDoc(e)||(i=w.attrHooks[t.toLowerCase()]||(w.expr.match.bool.test(t)?dt:void 0)),void 0!==n?null===n?void w.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=w.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!h.radioValue&&"radio"===t&&N(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(M);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),dt={set:function(e,t,n){return!1===t?w.removeAttr(e,n):e.setAttribute(n,n),n}},w.each(w.expr.match.bool.source.match(/\w+/g),function(e,t){var n=ht[t]||w.find.attr;ht[t]=function(e,t,r){var i,o,a=t.toLowerCase();return r||(o=ht[a],ht[a]=i,i=null!=n(e,t,r)?a:null,ht[a]=o),i}});var gt=/^(?:input|select|textarea|button)$/i,yt=/^(?:a|area)$/i;w.fn.extend({prop:function(e,t){return z(this,w.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each(function(){delete this[w.propFix[e]||e]})}}),w.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&w.isXMLDoc(e)||(t=w.propFix[t]||t,i=w.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=w.find.attr(e,"tabindex");return t?parseInt(t,10):gt.test(e.nodeName)||yt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),h.optSelected||(w.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),w.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){w.propFix[this.toLowerCase()]=this});function vt(e){return(e.match(M)||[]).join(" ")}function mt(e){return e.getAttribute&&e.getAttribute("class")||""}function xt(e){return Array.isArray(e)?e:"string"==typeof e?e.match(M)||[]:[]}w.fn.extend({addClass:function(e){var t,n,r,i,o,a,s,u=0;if(g(e))return this.each(function(t){w(this).addClass(e.call(this,t,mt(this)))});if((t=xt(e)).length)while(n=this[u++])if(i=mt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=t[a++])r.indexOf(" "+o+" ")<0&&(r+=o+" ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},removeClass:function(e){var t,n,r,i,o,a,s,u=0;if(g(e))return this.each(function(t){w(this).removeClass(e.call(this,t,mt(this)))});if(!arguments.length)return this.attr("class","");if((t=xt(e)).length)while(n=this[u++])if(i=mt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=t[a++])while(r.indexOf(" "+o+" ")>-1)r=r.replace(" "+o+" "," ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},toggleClass:function(e,t){var n=typeof e,r="string"===n||Array.isArray(e);return"boolean"==typeof t&&r?t?this.addClass(e):this.removeClass(e):g(e)?this.each(function(n){w(this).toggleClass(e.call(this,n,mt(this),t),t)}):this.each(function(){var t,i,o,a;if(r){i=0,o=w(this),a=xt(e);while(t=a[i++])o.hasClass(t)?o.removeClass(t):o.addClass(t)}else void 0!==e&&"boolean"!==n||((t=mt(this))&&J.set(this,"__className__",t),this.setAttribute&&this.setAttribute("class",t||!1===e?"":J.get(this,"__className__")||""))})},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&(" "+vt(mt(n))+" ").indexOf(t)>-1)return!0;return!1}});var bt=/\r/g;w.fn.extend({val:function(e){var t,n,r,i=this[0];{if(arguments.length)return r=g(e),this.each(function(n){var i;1===this.nodeType&&(null==(i=r?e.call(this,n,w(this).val()):e)?i="":"number"==typeof i?i+="":Array.isArray(i)&&(i=w.map(i,function(e){return null==e?"":e+""})),(t=w.valHooks[this.type]||w.valHooks[this.nodeName.toLowerCase()])&&"set"in t&&void 0!==t.set(this,i,"value")||(this.value=i))});if(i)return(t=w.valHooks[i.type]||w.valHooks[i.nodeName.toLowerCase()])&&"get"in t&&void 0!==(n=t.get(i,"value"))?n:"string"==typeof(n=i.value)?n.replace(bt,""):null==n?"":n}}}),w.extend({valHooks:{option:{get:function(e){var t=w.find.attr(e,"value");return null!=t?t:vt(w.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!N(n.parentNode,"optgroup"))){if(t=w(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=w.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=w.inArray(w.valHooks.option.get(r),o)>-1)&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),w.each(["radio","checkbox"],function(){w.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=w.inArray(w(e).val(),t)>-1}},h.checkOn||(w.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),h.focusin="onfocusin"in e;var wt=/^(?:focusinfocus|focusoutblur)$/,Tt=function(e){e.stopPropagation()};w.extend(w.event,{trigger:function(t,n,i,o){var a,s,u,l,c,p,d,h,v=[i||r],m=f.call(t,"type")?t.type:t,x=f.call(t,"namespace")?t.namespace.split("."):[];if(s=h=u=i=i||r,3!==i.nodeType&&8!==i.nodeType&&!wt.test(m+w.event.triggered)&&(m.indexOf(".")>-1&&(m=(x=m.split(".")).shift(),x.sort()),c=m.indexOf(":")<0&&"on"+m,t=t[w.expando]?t:new w.Event(m,"object"==typeof t&&t),t.isTrigger=o?2:3,t.namespace=x.join("."),t.rnamespace=t.namespace?new RegExp("(^|\\.)"+x.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,t.result=void 0,t.target||(t.target=i),n=null==n?[t]:w.makeArray(n,[t]),d=w.event.special[m]||{},o||!d.trigger||!1!==d.trigger.apply(i,n))){if(!o&&!d.noBubble&&!y(i)){for(l=d.delegateType||m,wt.test(l+m)||(s=s.parentNode);s;s=s.parentNode)v.push(s),u=s;u===(i.ownerDocument||r)&&v.push(u.defaultView||u.parentWindow||e)}a=0;while((s=v[a++])&&!t.isPropagationStopped())h=s,t.type=a>1?l:d.bindType||m,(p=(J.get(s,"events")||{})[t.type]&&J.get(s,"handle"))&&p.apply(s,n),(p=c&&s[c])&&p.apply&&Y(s)&&(t.result=p.apply(s,n),!1===t.result&&t.preventDefault());return t.type=m,o||t.isDefaultPrevented()||d._default&&!1!==d._default.apply(v.pop(),n)||!Y(i)||c&&g(i[m])&&!y(i)&&((u=i[c])&&(i[c]=null),w.event.triggered=m,t.isPropagationStopped()&&h.addEventListener(m,Tt),i[m](),t.isPropagationStopped()&&h.removeEventListener(m,Tt),w.event.triggered=void 0,u&&(i[c]=u)),t.result}},simulate:function(e,t,n){var r=w.extend(new w.Event,n,{type:e,isSimulated:!0});w.event.trigger(r,null,t)}}),w.fn.extend({trigger:function(e,t){return this.each(function(){w.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return w.event.trigger(e,t,n,!0)}}),h.focusin||w.each({focus:"focusin",blur:"focusout"},function(e,t){var n=function(e){w.event.simulate(t,e.target,w.event.fix(e))};w.event.special[t]={setup:function(){var r=this.ownerDocument||this,i=J.access(r,t);i||r.addEventListener(e,n,!0),J.access(r,t,(i||0)+1)},teardown:function(){var r=this.ownerDocument||this,i=J.access(r,t)-1;i?J.access(r,t,i):(r.removeEventListener(e,n,!0),J.remove(r,t))}}});var Ct=e.location,Et=Date.now(),kt=/\?/;w.parseXML=function(t){var n;if(!t||"string"!=typeof t)return null;try{n=(new e.DOMParser).parseFromString(t,"text/xml")}catch(e){n=void 0}return n&&!n.getElementsByTagName("parsererror").length||w.error("Invalid XML: "+t),n};var St=/\[\]$/,Dt=/\r?\n/g,Nt=/^(?:submit|button|image|reset|file)$/i,At=/^(?:input|select|textarea|keygen)/i;function jt(e,t,n,r){var i;if(Array.isArray(t))w.each(t,function(t,i){n||St.test(e)?r(e,i):jt(e+"["+("object"==typeof i&&null!=i?t:"")+"]",i,n,r)});else if(n||"object"!==x(t))r(e,t);else for(i in t)jt(e+"["+i+"]",t[i],n,r)}w.param=function(e,t){var n,r=[],i=function(e,t){var n=g(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(Array.isArray(e)||e.jquery&&!w.isPlainObject(e))w.each(e,function(){i(this.name,this.value)});else for(n in e)jt(n,e[n],t,i);return r.join("&")},w.fn.extend({serialize:function(){return w.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=w.prop(this,"elements");return e?w.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!w(this).is(":disabled")&&At.test(this.nodeName)&&!Nt.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=w(this).val();return null==n?null:Array.isArray(n)?w.map(n,function(e){return{name:t.name,value:e.replace(Dt,"\r\n")}}):{name:t.name,value:n.replace(Dt,"\r\n")}}).get()}});var qt=/%20/g,Lt=/#.*$/,Ht=/([?&])_=[^&]*/,Ot=/^(.*?):[ \t]*([^\r\n]*)$/gm,Pt=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Mt=/^(?:GET|HEAD)$/,Rt=/^\/\//,It={},Wt={},$t="*/".concat("*"),Bt=r.createElement("a");Bt.href=Ct.href;function Ft(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match(M)||[];if(g(n))while(r=o[i++])"+"===r[0]?(r=r.slice(1)||"*",(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function _t(e,t,n,r){var i={},o=e===Wt;function a(s){var u;return i[s]=!0,w.each(e[s]||[],function(e,s){var l=s(t,n,r);return"string"!=typeof l||o||i[l]?o?!(u=l):void 0:(t.dataTypes.unshift(l),a(l),!1)}),u}return a(t.dataTypes[0])||!i["*"]&&a("*")}function zt(e,t){var n,r,i=w.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&w.extend(!0,e,r),e}function Xt(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}function Ut(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}w.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Ct.href,type:"GET",isLocal:Pt.test(Ct.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":$t,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":w.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?zt(zt(e,w.ajaxSettings),t):zt(w.ajaxSettings,e)},ajaxPrefilter:Ft(It),ajaxTransport:Ft(Wt),ajax:function(t,n){"object"==typeof t&&(n=t,t=void 0),n=n||{};var i,o,a,s,u,l,c,f,p,d,h=w.ajaxSetup({},n),g=h.context||h,y=h.context&&(g.nodeType||g.jquery)?w(g):w.event,v=w.Deferred(),m=w.Callbacks("once memory"),x=h.statusCode||{},b={},T={},C="canceled",E={readyState:0,getResponseHeader:function(e){var t;if(c){if(!s){s={};while(t=Ot.exec(a))s[t[1].toLowerCase()]=t[2]}t=s[e.toLowerCase()]}return null==t?null:t},getAllResponseHeaders:function(){return c?a:null},setRequestHeader:function(e,t){return null==c&&(e=T[e.toLowerCase()]=T[e.toLowerCase()]||e,b[e]=t),this},overrideMimeType:function(e){return null==c&&(h.mimeType=e),this},statusCode:function(e){var t;if(e)if(c)E.always(e[E.status]);else for(t in e)x[t]=[x[t],e[t]];return this},abort:function(e){var t=e||C;return i&&i.abort(t),k(0,t),this}};if(v.promise(E),h.url=((t||h.url||Ct.href)+"").replace(Rt,Ct.protocol+"//"),h.type=n.method||n.type||h.method||h.type,h.dataTypes=(h.dataType||"*").toLowerCase().match(M)||[""],null==h.crossDomain){l=r.createElement("a");try{l.href=h.url,l.href=l.href,h.crossDomain=Bt.protocol+"//"+Bt.host!=l.protocol+"//"+l.host}catch(e){h.crossDomain=!0}}if(h.data&&h.processData&&"string"!=typeof h.data&&(h.data=w.param(h.data,h.traditional)),_t(It,h,n,E),c)return E;(f=w.event&&h.global)&&0==w.active++&&w.event.trigger("ajaxStart"),h.type=h.type.toUpperCase(),h.hasContent=!Mt.test(h.type),o=h.url.replace(Lt,""),h.hasContent?h.data&&h.processData&&0===(h.contentType||"").indexOf("application/x-www-form-urlencoded")&&(h.data=h.data.replace(qt,"+")):(d=h.url.slice(o.length),h.data&&(h.processData||"string"==typeof h.data)&&(o+=(kt.test(o)?"&":"?")+h.data,delete h.data),!1===h.cache&&(o=o.replace(Ht,"$1"),d=(kt.test(o)?"&":"?")+"_="+Et+++d),h.url=o+d),h.ifModified&&(w.lastModified[o]&&E.setRequestHeader("If-Modified-Since",w.lastModified[o]),w.etag[o]&&E.setRequestHeader("If-None-Match",w.etag[o])),(h.data&&h.hasContent&&!1!==h.contentType||n.contentType)&&E.setRequestHeader("Content-Type",h.contentType),E.setRequestHeader("Accept",h.dataTypes[0]&&h.accepts[h.dataTypes[0]]?h.accepts[h.dataTypes[0]]+("*"!==h.dataTypes[0]?", "+$t+"; q=0.01":""):h.accepts["*"]);for(p in h.headers)E.setRequestHeader(p,h.headers[p]);if(h.beforeSend&&(!1===h.beforeSend.call(g,E,h)||c))return E.abort();if(C="abort",m.add(h.complete),E.done(h.success),E.fail(h.error),i=_t(Wt,h,n,E)){if(E.readyState=1,f&&y.trigger("ajaxSend",[E,h]),c)return E;h.async&&h.timeout>0&&(u=e.setTimeout(function(){E.abort("timeout")},h.timeout));try{c=!1,i.send(b,k)}catch(e){if(c)throw e;k(-1,e)}}else k(-1,"No Transport");function k(t,n,r,s){var l,p,d,b,T,C=n;c||(c=!0,u&&e.clearTimeout(u),i=void 0,a=s||"",E.readyState=t>0?4:0,l=t>=200&&t<300||304===t,r&&(b=Xt(h,E,r)),b=Ut(h,b,E,l),l?(h.ifModified&&((T=E.getResponseHeader("Last-Modified"))&&(w.lastModified[o]=T),(T=E.getResponseHeader("etag"))&&(w.etag[o]=T)),204===t||"HEAD"===h.type?C="nocontent":304===t?C="notmodified":(C=b.state,p=b.data,l=!(d=b.error))):(d=C,!t&&C||(C="error",t<0&&(t=0))),E.status=t,E.statusText=(n||C)+"",l?v.resolveWith(g,[p,C,E]):v.rejectWith(g,[E,C,d]),E.statusCode(x),x=void 0,f&&y.trigger(l?"ajaxSuccess":"ajaxError",[E,h,l?p:d]),m.fireWith(g,[E,C]),f&&(y.trigger("ajaxComplete",[E,h]),--w.active||w.event.trigger("ajaxStop")))}return E},getJSON:function(e,t,n){return w.get(e,t,n,"json")},getScript:function(e,t){return w.get(e,void 0,t,"script")}}),w.each(["get","post"],function(e,t){w[t]=function(e,n,r,i){return g(n)&&(i=i||r,r=n,n=void 0),w.ajax(w.extend({url:e,type:t,dataType:i,data:n,success:r},w.isPlainObject(e)&&e))}}),w._evalUrl=function(e){return w.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,"throws":!0})},w.fn.extend({wrapAll:function(e){var t;return this[0]&&(g(e)&&(e=e.call(this[0])),t=w(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(e){return g(e)?this.each(function(t){w(this).wrapInner(e.call(this,t))}):this.each(function(){var t=w(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=g(e);return this.each(function(n){w(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(e){return this.parent(e).not("body").each(function(){w(this).replaceWith(this.childNodes)}),this}}),w.expr.pseudos.hidden=function(e){return!w.expr.pseudos.visible(e)},w.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},w.ajaxSettings.xhr=function(){try{return new e.XMLHttpRequest}catch(e){}};var Vt={0:200,1223:204},Gt=w.ajaxSettings.xhr();h.cors=!!Gt&&"withCredentials"in Gt,h.ajax=Gt=!!Gt,w.ajaxTransport(function(t){var n,r;if(h.cors||Gt&&!t.crossDomain)return{send:function(i,o){var a,s=t.xhr();if(s.open(t.type,t.url,t.async,t.username,t.password),t.xhrFields)for(a in t.xhrFields)s[a]=t.xhrFields[a];t.mimeType&&s.overrideMimeType&&s.overrideMimeType(t.mimeType),t.crossDomain||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest");for(a in i)s.setRequestHeader(a,i[a]);n=function(e){return function(){n&&(n=r=s.onload=s.onerror=s.onabort=s.ontimeout=s.onreadystatechange=null,"abort"===e?s.abort():"error"===e?"number"!=typeof s.status?o(0,"error"):o(s.status,s.statusText):o(Vt[s.status]||s.status,s.statusText,"text"!==(s.responseType||"text")||"string"!=typeof s.responseText?{binary:s.response}:{text:s.responseText},s.getAllResponseHeaders()))}},s.onload=n(),r=s.onerror=s.ontimeout=n("error"),void 0!==s.onabort?s.onabort=r:s.onreadystatechange=function(){4===s.readyState&&e.setTimeout(function(){n&&r()})},n=n("abort");try{s.send(t.hasContent&&t.data||null)}catch(e){if(n)throw e}},abort:function(){n&&n()}}}),w.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),w.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return w.globalEval(e),e}}}),w.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),w.ajaxTransport("script",function(e){if(e.crossDomain){var t,n;return{send:function(i,o){t=w("<script>").prop({charset:e.scriptCharset,src:e.url}).on("load error",n=function(e){t.remove(),n=null,e&&o("error"===e.type?404:200,e.type)}),r.head.appendChild(t[0])},abort:function(){n&&n()}}}});var Yt=[],Qt=/(=)\?(?=&|$)|\?\?/;w.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Yt.pop()||w.expando+"_"+Et++;return this[e]=!0,e}}),w.ajaxPrefilter("json jsonp",function(t,n,r){var i,o,a,s=!1!==t.jsonp&&(Qt.test(t.url)?"url":"string"==typeof t.data&&0===(t.contentType||"").indexOf("application/x-www-form-urlencoded")&&Qt.test(t.data)&&"data");if(s||"jsonp"===t.dataTypes[0])return i=t.jsonpCallback=g(t.jsonpCallback)?t.jsonpCallback():t.jsonpCallback,s?t[s]=t[s].replace(Qt,"$1"+i):!1!==t.jsonp&&(t.url+=(kt.test(t.url)?"&":"?")+t.jsonp+"="+i),t.converters["script json"]=function(){return a||w.error(i+" was not called"),a[0]},t.dataTypes[0]="json",o=e[i],e[i]=function(){a=arguments},r.always(function(){void 0===o?w(e).removeProp(i):e[i]=o,t[i]&&(t.jsonpCallback=n.jsonpCallback,Yt.push(i)),a&&g(o)&&o(a[0]),a=o=void 0}),"script"}),h.createHTMLDocument=function(){var e=r.implementation.createHTMLDocument("").body;return e.innerHTML="<form></form><form></form>",2===e.childNodes.length}(),w.parseHTML=function(e,t,n){if("string"!=typeof e)return[];"boolean"==typeof t&&(n=t,t=!1);var i,o,a;return t||(h.createHTMLDocument?((i=(t=r.implementation.createHTMLDocument("")).createElement("base")).href=r.location.href,t.head.appendChild(i)):t=r),o=A.exec(e),a=!n&&[],o?[t.createElement(o[1])]:(o=xe([e],t,a),a&&a.length&&w(a).remove(),w.merge([],o.childNodes))},w.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return s>-1&&(r=vt(e.slice(s)),e=e.slice(0,s)),g(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),a.length>0&&w.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?w("<div>").append(w.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},w.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){w.fn[t]=function(e){return this.on(t,e)}}),w.expr.pseudos.animated=function(e){return w.grep(w.timers,function(t){return e===t.elem}).length},w.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l,c=w.css(e,"position"),f=w(e),p={};"static"===c&&(e.style.position="relative"),s=f.offset(),o=w.css(e,"top"),u=w.css(e,"left"),(l=("absolute"===c||"fixed"===c)&&(o+u).indexOf("auto")>-1)?(a=(r=f.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),g(t)&&(t=t.call(e,n,w.extend({},s))),null!=t.top&&(p.top=t.top-s.top+a),null!=t.left&&(p.left=t.left-s.left+i),"using"in t?t.using.call(e,p):f.css(p)}},w.fn.extend({offset:function(e){if(arguments.length)return void 0===e?this:this.each(function(t){w.offset.setOffset(this,e,t)});var t,n,r=this[0];if(r)return r.getClientRects().length?(t=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:t.top+n.pageYOffset,left:t.left+n.pageXOffset}):{top:0,left:0}},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===w.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===w.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=w(e).offset()).top+=w.css(e,"borderTopWidth",!0),i.left+=w.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-w.css(r,"marginTop",!0),left:t.left-i.left-w.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===w.css(e,"position"))e=e.offsetParent;return e||be})}}),w.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(e,t){var n="pageYOffset"===t;w.fn[e]=function(r){return z(this,function(e,r,i){var o;if(y(e)?o=e:9===e.nodeType&&(o=e.defaultView),void 0===i)return o?o[t]:e[r];o?o.scrollTo(n?o.pageXOffset:i,n?i:o.pageYOffset):e[r]=i},e,r,arguments.length)}}),w.each(["top","left"],function(e,t){w.cssHooks[t]=_e(h.pixelPosition,function(e,n){if(n)return n=Fe(e,t),We.test(n)?w(e).position()[t]+"px":n})}),w.each({Height:"height",Width:"width"},function(e,t){w.each({padding:"inner"+e,content:t,"":"outer"+e},function(n,r){w.fn[r]=function(i,o){var a=arguments.length&&(n||"boolean"!=typeof i),s=n||(!0===i||!0===o?"margin":"border");return z(this,function(t,n,i){var o;return y(t)?0===r.indexOf("outer")?t["inner"+e]:t.document.documentElement["client"+e]:9===t.nodeType?(o=t.documentElement,Math.max(t.body["scroll"+e],o["scroll"+e],t.body["offset"+e],o["offset"+e],o["client"+e])):void 0===i?w.css(t,n,s):w.style(t,n,i,s)},t,a?i:void 0,a)}})}),w.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,t){w.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}}),w.fn.extend({hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),w.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)}}),w.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),g(e))return r=o.call(arguments,2),i=function(){return e.apply(t||this,r.concat(o.call(arguments)))},i.guid=e.guid=e.guid||w.guid++,i},w.holdReady=function(e){e?w.readyWait++:w.ready(!0)},w.isArray=Array.isArray,w.parseJSON=JSON.parse,w.nodeName=N,w.isFunction=g,w.isWindow=y,w.camelCase=G,w.type=x,w.now=Date.now,w.isNumeric=function(e){var t=w.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},"function"==typeof define&&define.amd&&define("jquery",[],function(){return w});var Jt=e.jQuery,Kt=e.$;return w.noConflict=function(t){return e.$===w&&(e.$=Kt),t&&e.jQuery===w&&(e.jQuery=Jt),w},t||(e.jQuery=e.$=w),w});

/* mousetrap v1.6.2 craig.is/killing/mice */
(function(p,t,h){function u(a,b,d){a.addEventListener?a.addEventListener(b,d,!1):a.attachEvent("on"+b,d)}function y(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return m[a.which]?m[a.which]:q[a.which]?q[a.which]:String.fromCharCode(a.which).toLowerCase()}function E(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function v(a){return"shift"==a||"ctrl"==a||"alt"==a||
"meta"==a}function z(a,b){var d,e=[];var c=a;"+"===c?c=["+"]:(c=c.replace(/\+{2}/g,"+plus"),c=c.split("+"));for(d=0;d<c.length;++d){var k=c[d];A[k]&&(k=A[k]);b&&"keypress"!=b&&B[k]&&(k=B[k],e.push("shift"));v(k)&&e.push(k)}c=k;d=b;if(!d){if(!n){n={};for(var h in m)95<h&&112>h||m.hasOwnProperty(h)&&(n[m[h]]=h)}d=n[c]?"keydown":"keypress"}"keypress"==d&&e.length&&(d="keydown");return{key:k,modifiers:e,action:d}}function C(a,b){return null===a||a===t?!1:a===b?!0:C(a.parentNode,b)}function e(a){function b(a){a=
a||{};var b=!1,l;for(l in n)a[l]?b=!0:n[l]=0;b||(w=!1)}function d(a,b,r,g,F,e){var l,D=[],h=r.type;if(!f._callbacks[a])return[];"keyup"==h&&v(a)&&(b=[a]);for(l=0;l<f._callbacks[a].length;++l){var d=f._callbacks[a][l];if((g||!d.seq||n[d.seq]==d.level)&&h==d.action){var c;(c="keypress"==h&&!r.metaKey&&!r.ctrlKey)||(c=d.modifiers,c=b.sort().join(",")===c.sort().join(","));c&&(c=g&&d.seq==g&&d.level==e,(!g&&d.combo==F||c)&&f._callbacks[a].splice(l,1),D.push(d))}}return D}function h(a,b,d,g){f.stopCallback(b,
b.target||b.srcElement,d,g)||!1!==a(b,d)||(b.preventDefault?b.preventDefault():b.returnValue=!1,b.stopPropagation?b.stopPropagation():b.cancelBubble=!0)}function c(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=y(a);b&&("keyup"==a.type&&x===b?x=!1:f.handleKey(b,E(a),a))}function k(a,d,r,g){function l(d){return function(){w=d;++n[a];clearTimeout(p);p=setTimeout(b,1E3)}}function e(d){h(r,d,a);"keyup"!==g&&(x=y(d));setTimeout(b,10)}for(var c=n[a]=0;c<d.length;++c){var f=c+1===d.length?e:l(g||
z(d[c+1]).action);m(d[c],f,g,a,c)}}function m(a,b,c,g,e){f._directMap[a+":"+c]=b;a=a.replace(/\s+/g," ");var h=a.split(" ");1<h.length?k(a,h,b,c):(c=z(a,c),f._callbacks[c.key]=f._callbacks[c.key]||[],d(c.key,c.modifiers,{type:c.action},g,a,e),f._callbacks[c.key][g?"unshift":"push"]({callback:b,modifiers:c.modifiers,action:c.action,seq:g,level:e,combo:a}))}var f=this;a=a||t;if(!(f instanceof e))return new e(a);f.target=a;f._callbacks={};f._directMap={};var n={},p,x=!1,q=!1,w=!1;f._handleKey=function(a,
c,e){var g=d(a,c,e),f;c={};var l=0,k=!1;for(f=0;f<g.length;++f)g[f].seq&&(l=Math.max(l,g[f].level));for(f=0;f<g.length;++f)g[f].seq?g[f].level==l&&(k=!0,c[g[f].seq]=1,h(g[f].callback,e,g[f].combo,g[f].seq)):k||h(g[f].callback,e,g[f].combo);g="keypress"==e.type&&q;e.type!=w||v(a)||g||b(c);q=k&&"keydown"==e.type};f._bindMultiple=function(a,b,c){for(var d=0;d<a.length;++d)m(a[d],b,c)};u(a,"keypress",c);u(a,"keydown",c);u(a,"keyup",c)}if(p){var m={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",
18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},q={106:"*",107:"+",109:"-",110:".",111:"/",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},B={"~":"`","!":"1","@":"2","#":"3",$:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},A={option:"alt",command:"meta","return":"enter",
escape:"esc",plus:"+",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},n;for(h=1;20>h;++h)m[111+h]="f"+h;for(h=0;9>=h;++h)m[h+96]=h.toString();e.prototype.bind=function(a,b,d){a=a instanceof Array?a:[a];this._bindMultiple.call(this,a,b,d);return this};e.prototype.unbind=function(a,b){return this.bind.call(this,a,function(){},b)};e.prototype.trigger=function(a,b){if(this._directMap[a+":"+b])this._directMap[a+":"+b]({},a);return this};e.prototype.reset=function(){this._callbacks={};
this._directMap={};return this};e.prototype.stopCallback=function(a,b){return-1<(" "+b.className+" ").indexOf(" mousetrap ")||C(b,this.target)?!1:"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable};e.prototype.handleKey=function(){return this._handleKey.apply(this,arguments)};e.addKeycodes=function(a){for(var b in a)a.hasOwnProperty(b)&&(m[b]=a[b]);n=null};e.init=function(){var a=e(t),b;for(b in a)"_"!==b.charAt(0)&&(e[b]=function(b){return function(){return a[b].apply(a,
arguments)}}(b))};e.init();p.Mousetrap=e;"undefined"!==typeof module&&module.exports&&(module.exports=e);"function"===typeof define&&define.amd&&define(function(){return e})}})("undefined"!==typeof window?window:null,"undefined"!==typeof window?document:null);

(function(a){var c={},d=a.prototype.stopCallback;a.prototype.stopCallback=function(e,b,a,f){return this.paused?!0:c[a]||c[f]?!1:d.call(this,e,b,a)};a.prototype.bindGlobal=function(a,b,d){this.bind(a,b,d);if(a instanceof Array)for(b=0;b<a.length;b++)c[a[b]]=!0;else c[a]=!0};a.init()})(Mousetrap);

"use strict";!function(n,t){"undefined"!=typeof module&&module.exports?module.exports=t():"function"==typeof define&&define.amd?define(t):window[n]=t()}("basicContext",function(){var n=null,t="item",e="separator",i=function(){var n=arguments.length<=0||void 0===arguments[0]?"":arguments[0];return document.querySelector(".basicContext "+n)},l=function(){var n=arguments.length<=0||void 0===arguments[0]?{}:arguments[0],i=0===Object.keys(n).length?!0:!1;return i===!0&&(n.type=e),null==n.type&&(n.type=t),null==n["class"]&&(n["class"]=""),n.visible!==!1&&(n.visible=!0),null==n.icon&&(n.icon=null),null==n.title&&(n.title="Undefined"),n.disabled!==!0&&(n.disabled=!1),n.disabled===!0&&(n["class"]+=" basicContext__item--disabled"),null==n.fn&&n.type!==e&&n.disabled===!1?(console.warn("Missing fn for item '"+n.title+"'"),!1):!0},o=function(n,i){var o="",r="";return l(n)===!1?"":n.visible===!1?"":(n.num=i,null!==n.icon&&(r="<span class='basicContext__icon "+n.icon+"'></span>"),n.type===t?o="\n		       <tr class='basicContext__item "+n["class"]+"'>\n		           <td class='basicContext__data' data-num='"+n.num+"'>"+r+n.title+"</td>\n		       </tr>\n		       ":n.type===e&&(o="\n		       <tr class='basicContext__item basicContext__item--separator'></tr>\n		       "),o)},r=function(n){var t="";return t+="\n	        <div class='basicContextContainer'>\n	            <div class='basicContext'>\n	                <table>\n	                    <tbody>\n	        ",n.forEach(function(n,e){return t+=o(n,e)}),t+="\n	                    </tbody>\n	                </table>\n	            </div>\n	        </div>\n	        "},a=function(){var n=arguments.length<=0||void 0===arguments[0]?{}:arguments[0],t={x:n.clientX,y:n.clientY};if("touchend"===n.type&&(null==t.x||null==t.y)){var e=n.changedTouches;null!=e&&e.length>0&&(t.x=e[0].clientX,t.y=e[0].clientY)}return(null==t.x||t.x<0)&&(t.x=0),(null==t.y||t.y<0)&&(t.y=0),t},s=function(n,t){var e=a(n),i=e.x,l=e.y,o={width:window.innerWidth,height:window.innerHeight},r={width:t.offsetWidth,height:t.offsetHeight};i+r.width>o.width&&(i-=i+r.width-o.width),l+r.height>o.height&&(l-=l+r.height-o.height),r.height>o.height&&(l=0,t.classList.add("basicContext--scrollable"));var s=e.x-i,u=e.y-l;return{x:i,y:l,rx:s,ry:u}},u=function(){var n=arguments.length<=0||void 0===arguments[0]?{}:arguments[0];return null==n.fn?!1:n.visible===!1?!1:n.disabled===!0?!1:(i("td[data-num='"+n.num+"']").onclick=n.fn,i("td[data-num='"+n.num+"']").oncontextmenu=n.fn,!0)},c=function(t,e,l,o){var a=r(t);document.body.insertAdjacentHTML("beforeend",a),null==n&&(n=document.body.style.overflow,document.body.style.overflow="hidden");var c=i(),d=s(e,c);return c.style.left=d.x+"px",c.style.top=d.y+"px",c.style.transformOrigin=d.rx+"px "+d.ry+"px",c.style.opacity=1,null==l&&(l=f),c.parentElement.onclick=l,c.parentElement.oncontextmenu=l,t.forEach(u),"function"==typeof e.preventDefault&&e.preventDefault(),"function"==typeof e.stopPropagation&&e.stopPropagation(),"function"==typeof o&&o(),!0},d=function(){var n=i();return null==n||0===n.length?!1:!0},f=function(){if(d()===!1)return!1;var t=document.querySelector(".basicContextContainer");return t.parentElement.removeChild(t),null!=n&&(document.body.style.overflow=n,n=null),!0};return{ITEM:t,SEPARATOR:e,show:c,visible:d,close:f}});
!function(n){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=n();else if("function"==typeof define&&define.amd)define([],n);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.basicModal=n()}}(function(){return function n(t,e,o){function l(c,s){if(!e[c]){if(!t[c]){var i="function"==typeof require&&require;if(!s&&i)return i(c,!0);if(a)return a(c,!0);var r=new Error("Cannot find module '"+c+"'");throw r.code="MODULE_NOT_FOUND",r}var u=e[c]={exports:{}};t[c][0].call(u.exports,function(n){var e=t[c][1][n];return l(e||n)},u,u.exports,n,t,e,o)}return e[c].exports}for(var a="function"==typeof require&&require,c=0;c<o.length;c++)l(o[c]);return l}({1:[function(n,t,e){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=null,l=(e.THEME={small:"basicModal__small",xclose:"basicModal__xclose"},function(){var n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return!0===(arguments.length>1&&void 0!==arguments[1]&&arguments[1])?document.querySelectorAll(".basicModal "+n):document.querySelector(".basicModal "+n)}),a=function(n,t){return null!=n&&(n.constructor===Object?Array.prototype.forEach.call(Object.keys(n),function(e){return t(n[e],e,n)}):Array.prototype.forEach.call(n,function(e,o){return t(e,o,n)}))},c=function(n){return null==n||0===Object.keys(n).length?(console.error("Missing or empty modal configuration object"),!1):(null==n.body&&(n.body=""),null==n.class&&(n.class=""),!1!==n.closable&&(n.closable=!0),null==n.buttons?(console.error("basicModal requires at least one button"),!1):null!=n.buttons.action&&(null==n.buttons.action.class&&(n.buttons.action.class=""),null==n.buttons.action.title&&(n.buttons.action.title="OK"),null==n.buttons.action.fn)?(console.error("Missing fn for action-button"),!1):null==n.buttons.cancel||(null==n.buttons.cancel.class&&(n.buttons.cancel.class=""),null==n.buttons.cancel.title&&(n.buttons.cancel.title="Cancel"),null!=n.buttons.cancel.fn)||(console.error("Missing fn for cancel-button"),!1))},s=function(n){var t="";return t+="\n\t        <div class='basicModalContainer basicModalContainer--fadeIn' data-closable='"+n.closable+"'>\n\t            <div class='basicModal basicModal--fadeIn "+n.class+"' role=\"dialog\">\n\t                <div class='basicModal__content'>\n\t                    "+n.body+"\n\t                </div>\n\t                <div class='basicModal__buttons'>\n\t        ",null!=n.buttons.cancel&&(-1===n.buttons.cancel.class.indexOf("basicModal__xclose")?t+="<a id='basicModal__cancel' class='basicModal__button "+n.buttons.cancel.class+"'>"+n.buttons.cancel.title+"</a>":t+="<div id='basicModal__cancel' class='basicModal__button "+n.buttons.cancel.class+'\' aria-label=\'close\'><svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><path d="M405 136.798l-29.798-29.798-119.202 119.202-119.202-119.202-29.798 29.798 119.202 119.202-119.202 119.202 29.798 29.798 119.202-119.202 119.202 119.202 29.798-29.798-119.202-119.202z"/></svg></div>'),null!=n.buttons.action&&(t+="<a id='basicModal__action' class='basicModal__button "+n.buttons.action.class+"'>"+n.buttons.action.title+"</a>"),t+="\n\t                </div>\n\t            </div>\n\t        </div>\n\t        "},i=e.getValues=function(){var n={},t=l("input[name]",!0),e=l("select[name]",!0);return a(t,function(t){var e=t.getAttribute("name"),o=t.value;n[e]=o}),a(e,function(t){var e=t.getAttribute("name"),o=t.options[t.selectedIndex].value;n[e]=o}),0===Object.keys(n).length?null:n},r=function(n){return null!=n.buttons.cancel&&(l("#basicModal__cancel").onclick=function(){if(!0===this.classList.contains("basicModal__button--active"))return!1;this.classList.add("basicModal__button--active"),n.buttons.cancel.fn()}),null!=n.buttons.action&&(l("#basicModal__action").onclick=function(){if(!0===this.classList.contains("basicModal__button--active"))return!1;this.classList.add("basicModal__button--active"),n.buttons.action.fn(i())}),a(l("input",!0),function(n){n.oninput=n.onblur=function(){this.classList.remove("error")}}),a(l("select",!0),function(n){n.onchange=n.onblur=function(){this.classList.remove("error")}}),!0},u=(e.show=function n(t){if(!1===c(t))return!1;if(null!=l())return b(!0),setTimeout(function(){return n(t)},301),!1;o=document.activeElement;var e=s(t);document.body.insertAdjacentHTML("beforeend",e),r(t);var a=l("input");null!=a&&a.select();var i=l("select");return null==a&&null!=i&&i.focus(),null!=t.callback&&t.callback(t),!0},e.error=function(n){d();var t=l("input[name='"+n+"']")||l("select[name='"+n+"']");if(null==t)return!1;t.classList.add("error"),"function"==typeof t.select?t.select():t.focus(),l().classList.remove("basicModal--fadeIn","basicModal--shake"),setTimeout(function(){return l().classList.add("basicModal--shake")},1)},e.visible=function(){return null!=l()}),d=(e.action=function(){var n=l("#basicModal__action");return null!=n&&(n.click(),!0)},e.cancel=function(){var n=l("#basicModal__cancel");return null!=n&&(n.click(),!0)},e.reset=function(){var n=l(".basicModal__button",!0);a(n,function(n){return n.classList.remove("basicModal__button--active")});var t=l("input",!0);a(t,function(n){return n.classList.remove("error")});var e=l("select",!0);return a(e,function(n){return n.classList.remove("error")}),!0}),b=e.close=function(){var n=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(!1===u())return!1;var t=l().parentElement;return("false"!==t.getAttribute("data-closable")||!1!==n)&&(t.classList.remove("basicModalContainer--fadeIn"),t.classList.add("basicModalContainer--fadeOut"),setTimeout(function(){return null!=t&&(null!=t.parentElement&&void t.parentElement.removeChild(t))},300),null!=o&&(o.focus(),o=null),!0)}},{}]},{},[1])(1)});
/*
 * @license
 *
 * Multiselect v2.5.5
 * http://crlcu.github.io/multiselect/
 *
 * Copyright (c) 2016-2018 Adrian Crisan
 * Licensed under the MIT license (https://github.com/crlcu/multiselect/blob/master/LICENSE)
 */

if("undefined"==typeof jQuery)throw new Error("multiselect requires jQuery");!function(t){"use strict";var e=t.fn.jquery.split(" ")[0].split(".");if(e[0]<2&&e[1]<7)throw new Error("multiselect requires jQuery version 1.7 or higher")}(jQuery),function(t){"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)}(function(t){"use strict";var e=function(t){function e(e,o){var n=e.prop("id");if(this.$left=e,this.$right=t(t(o.right).length?o.right:"#"+n+"_to"),this.actions={$leftAll:t(t(o.leftAll).length?o.leftAll:"#"+n+"_leftAll"),$rightAll:t(t(o.rightAll).length?o.rightAll:"#"+n+"_rightAll"),$leftSelected:t(t(o.leftSelected).length?o.leftSelected:"#"+n+"_leftSelected"),$rightSelected:t(t(o.rightSelected).length?o.rightSelected:"#"+n+"_rightSelected"),$undo:t(t(o.undo).length?o.undo:"#"+n+"_undo"),$redo:t(t(o.redo).length?o.redo:"#"+n+"_redo"),$moveUp:t(t(o.moveUp).length?o.moveUp:"#"+n+"_move_up"),$moveDown:t(t(o.moveDown).length?o.moveDown:"#"+n+"_move_down")},delete o.leftAll,delete o.leftSelected,delete o.right,delete o.rightAll,delete o.rightSelected,delete o.undo,delete o.redo,delete o.moveUp,delete o.moveDown,this.options={keepRenderingSort:o.keepRenderingSort,submitAllLeft:void 0===o.submitAllLeft||o.submitAllLeft,submitAllRight:void 0===o.submitAllRight||o.submitAllRight,search:o.search,ignoreDisabled:void 0!==o.ignoreDisabled&&o.ignoreDisabled,matchOptgroupBy:void 0!==o.matchOptgroupBy?o.matchOptgroupBy:"label"},delete o.keepRenderingSort,o.submitAllLeft,o.submitAllRight,o.search,o.ignoreDisabled,o.matchOptgroupBy,this.callbacks=o,"function"==typeof this.callbacks.sort){var i=this.callbacks.sort;this.callbacks.sort={left:i,right:i}}this.init()}return e.prototype={init:function(){var e=this;e.undoStack=[],e.redoStack=[],e.options.keepRenderingSort&&(e.skipInitSort=!0,!1!==e.callbacks.sort&&(e.callbacks.sort={left:function(e,o){return t(e).data("position")>t(o).data("position")?1:-1},right:function(e,o){return t(e).data("position")>t(o).data("position")?1:-1}}),e.$left.attachIndex(),e.$right.each(function(e,o){t(o).attachIndex()})),"function"==typeof e.callbacks.startUp&&e.callbacks.startUp(e.$left,e.$right),e.skipInitSort||("function"==typeof e.callbacks.sort.left&&e.$left.mSort(e.callbacks.sort.left),"function"==typeof e.callbacks.sort.right&&e.$right.each(function(o,n){t(n).mSort(e.callbacks.sort.right)})),e.options.search&&e.options.search.left&&(e.options.search.$left=t(e.options.search.left),e.$left.before(e.options.search.$left)),e.options.search&&e.options.search.right&&(e.options.search.$right=t(e.options.search.right),e.$right.before(t(e.options.search.$right))),e.events(),"function"==typeof e.callbacks.afterInit&&e.callbacks.afterInit()},events:function(){var e=this;e.options.search&&e.options.search.$left&&e.options.search.$left.on("keyup",function(t){if(e.callbacks.fireSearch(this.value)){e.$left.find('option:search("'+this.value+'")').mShow(),e.$left.find('option:not(:search("'+this.value+'"))').mHide(),e.$left.find("option").closest("optgroup").mHide(),e.$left.find("option:not(.hidden)").parent("optgroup").mShow()}else e.$left.find("option, optgroup").mShow()}),e.options.search&&e.options.search.$right&&e.options.search.$right.on("keyup",function(t){if(e.callbacks.fireSearch(this.value)){e.$right.find('option:search("'+this.value+'")').mShow(),e.$right.find('option:not(:search("'+this.value+'"))').mHide(),e.$right.find("option").closest("optgroup").mHide(),e.$right.find("option:not(.hidden)").parent("optgroup").mShow()}else e.$right.find("option, optgroup").mShow()}),e.$right.closest("form").on("submit",function(t){e.options.search&&(e.options.search.$left&&e.options.search.$left.val("").trigger("keyup"),e.options.search.$right&&e.options.search.$right.val("").trigger("keyup")),e.$left.find("option").prop("selected",e.options.submitAllLeft),e.$right.find("option").prop("selected",e.options.submitAllRight)}),e.$left.on("dblclick","option",function(t){t.preventDefault();var o=e.$left.find("option:selected:not(.hidden)");o.length&&e.moveToRight(o,t)}),e.$left.on("click","optgroup",function(e){"OPTGROUP"==t(e.target).prop("tagName")&&t(this).children().prop("selected",!0)}),e.$left.on("keypress",function(t){if(13===t.keyCode){t.preventDefault();var o=e.$left.find("option:selected:not(.hidden)");o.length&&e.moveToRight(o,t)}}),e.$right.on("dblclick","option",function(t){t.preventDefault();var o=e.$right.find("option:selected:not(.hidden)");o.length&&e.moveToLeft(o,t)}),e.$right.on("click","optgroup",function(e){"OPTGROUP"==t(e.target).prop("tagName")&&t(this).children().prop("selected",!0)}),e.$right.on("keydown",function(t){if(8===t.keyCode||46===t.keyCode){t.preventDefault();var o=e.$right.find("option:selected:not(.hidden)");o.length&&e.moveToLeft(o,t)}}),(navigator.userAgent.match(/MSIE/i)||navigator.userAgent.indexOf("Trident/")>0||navigator.userAgent.indexOf("Edge/")>0)&&(e.$left.dblclick(function(t){e.actions.$rightSelected.trigger("click")}),e.$right.dblclick(function(t){e.actions.$leftSelected.trigger("click")})),e.actions.$rightSelected.on("click",function(o){o.preventDefault();var n=e.$left.find("option:selected:not(.hidden)");n.length&&e.moveToRight(n,o),t(this).blur()}),e.actions.$leftSelected.on("click",function(o){o.preventDefault();var n=e.$right.find("option:selected:not(.hidden)");n.length&&e.moveToLeft(n,o),t(this).blur()}),e.actions.$rightAll.on("click",function(o){o.preventDefault();var n=e.$left.children(":not(span):not(.hidden)");n.length&&e.moveToRight(n,o),t(this).blur()}),e.actions.$leftAll.on("click",function(o){o.preventDefault();var n=e.$right.children(":not(span):not(.hidden)");n.length&&e.moveToLeft(n,o),t(this).blur()}),e.actions.$undo.on("click",function(t){t.preventDefault(),e.undo(t)}),e.actions.$redo.on("click",function(t){t.preventDefault(),e.redo(t)}),e.actions.$moveUp.on("click",function(o){o.preventDefault();var n=e.$right.find(":selected:not(span):not(.hidden)");n.length&&e.moveUp(n,o),t(this).blur()}),e.actions.$moveDown.on("click",function(o){o.preventDefault();var n=e.$right.find(":selected:not(span):not(.hidden)");n.length&&e.moveDown(n,o),t(this).blur()})},moveToRight:function(t,e,o,n){var i=this;return"function"==typeof i.callbacks.moveToRight?i.callbacks.moveToRight(i,t,e,o,n):!("function"==typeof i.callbacks.beforeMoveToRight&&!o&&!i.callbacks.beforeMoveToRight(i.$left,i.$right,t))&&(i.moveFromAtoB(i.$left,i.$right,t,e,o,n),n||(i.undoStack.push(["right",t]),i.redoStack=[]),"function"!=typeof i.callbacks.sort.right||o||i.doNotSortRight||i.$right.mSort(i.callbacks.sort.right),"function"!=typeof i.callbacks.afterMoveToRight||o||i.callbacks.afterMoveToRight(i.$left,i.$right,t),i)},moveToLeft:function(t,e,o,n){var i=this;return"function"==typeof i.callbacks.moveToLeft?i.callbacks.moveToLeft(i,t,e,o,n):!("function"==typeof i.callbacks.beforeMoveToLeft&&!o&&!i.callbacks.beforeMoveToLeft(i.$left,i.$right,t))&&(i.moveFromAtoB(i.$right,i.$left,t,e,o,n),n||(i.undoStack.push(["left",t]),i.redoStack=[]),"function"!=typeof i.callbacks.sort.left||o||i.$left.mSort(i.callbacks.sort.left),"function"!=typeof i.callbacks.afterMoveToLeft||o||i.callbacks.afterMoveToLeft(i.$left,i.$right,t),i)},moveFromAtoB:function(e,o,n,i,r,l){var c=this;return"function"==typeof c.callbacks.moveFromAtoB?c.callbacks.moveFromAtoB(c,e,o,n,i,r,l):(n.each(function(e,n){var i=t(n);if(c.options.ignoreDisabled&&i.is(":disabled"))return!0;if(i.is("optgroup")||i.parent().is("optgroup")){var r=i.is("optgroup")?i:i.parent(),l="optgroup["+c.options.matchOptgroupBy+'="'+r.prop(c.options.matchOptgroupBy)+'"]',a=o.find(l);if(a.length||(a=r.clone(!0),a.empty(),o.move(a)),i.is("optgroup")){var f="";c.options.ignoreDisabled&&(f=":not(:disabled)"),a.move(i.find("option"+f))}else a.move(i);r.removeIfEmpty()}else o.move(i)}),c)},moveUp:function(t){var e=this;if("function"==typeof e.callbacks.beforeMoveUp&&!e.callbacks.beforeMoveUp(t))return!1;t.first().prev().before(t),"function"==typeof e.callbacks.afterMoveUp&&e.callbacks.afterMoveUp(t)},moveDown:function(t){var e=this;if("function"==typeof e.callbacks.beforeMoveDown&&!e.callbacks.beforeMoveDown(t))return!1;t.last().next().after(t),"function"==typeof e.callbacks.afterMoveDown&&e.callbacks.afterMoveDown(t)},undo:function(t){var e=this,o=e.undoStack.pop();if(o)switch(e.redoStack.push(o),o[0]){case"left":e.moveToRight(o[1],t,!1,!0);break;case"right":e.moveToLeft(o[1],t,!1,!0)}},redo:function(t){var e=this,o=e.redoStack.pop();if(o)switch(e.undoStack.push(o),o[0]){case"left":e.moveToLeft(o[1],t,!1,!0);break;case"right":e.moveToRight(o[1],t,!1,!0)}}},e}(t);t.multiselect={defaults:{startUp:function(e,o){o.find("option").each(function(o,n){if("OPTGROUP"==t(n).parent().prop("tagName")){var i='optgroup[label="'+t(n).parent().attr("label")+'"]';e.find(i+' option[value="'+n.value+'"]').each(function(t,e){e.remove()}),e.find(i).removeIfEmpty()}else{e.find('option[value="'+n.value+'"]').remove()}})},afterInit:function(){return!0},beforeMoveToRight:function(t,e,o){return!0},afterMoveToRight:function(t,e,o){},beforeMoveToLeft:function(t,e,o){return!0},afterMoveToLeft:function(t,e,o){},beforeMoveUp:function(t){return!0},afterMoveUp:function(t){},beforeMoveDown:function(t){return!0},afterMoveDown:function(t){},sort:function(t,e){return"NA"==t.innerHTML?1:"NA"==e.innerHTML?-1:t.innerHTML>e.innerHTML?1:-1},fireSearch:function(t){return t.length>1}}};var o=window.navigator.userAgent,n=o.indexOf("MSIE ")+o.indexOf("Trident/")+o.indexOf("Edge/")>-3,i=o.toLowerCase().indexOf("safari")>-1,r=o.toLowerCase().indexOf("firefox")>-1;t.fn.multiselect=function(o){return this.each(function(){var n=t(this),i=n.data("crlcu.multiselect"),r=t.extend({},t.multiselect.defaults,n.data(),"object"==typeof o&&o);i||n.data("crlcu.multiselect",i=new e(n,r))})},t.fn.move=function(t){return this.append(t).find("option").prop("selected",!1),this},t.fn.removeIfEmpty=function(){return this.children().length||this.remove(),this},t.fn.mShow=function(){return this.removeClass("hidden").show(),(n||i)&&this.each(function(e,o){t(o).parent().is("span")&&t(o).parent().replaceWith(o),t(o).show()}),r&&this.attr("disabled",!1),this},t.fn.mHide=function(){return this.addClass("hidden").hide(),(n||i)&&this.each(function(e,o){t(o).parent().is("span")||t(o).wrap("<span>").hide()}),r&&this.attr("disabled",!0),this},t.fn.mSort=function(e){return this.children().sort(e).appendTo(this),this.find("optgroup").each(function(o,n){t(n).children().sort(e).appendTo(n)}),this},t.fn.attachIndex=function(){this.children().each(function(e,o){var n=t(o);n.is("optgroup")&&n.children().each(function(e,o){t(o).data("position",e)}),n.data("position",e)})},t.expr[":"].search=function(e,o,n){var i=new RegExp(n[3].replace(/([^a-zA-Z0-9])/g,"\\$1"),"i");return t(e).text().match(i)}});

"use strict";

var _templateObject = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject2 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"]),
    _templateObject3 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject5 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='Description' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='Description' value='$", "'></p>"]),
    _templateObject6 = _taggedTemplateLiteral(["<p>", " '$", "' ", " '$", "'?</p>"], ["<p>", " '$", "' ", " '$", "'?</p>"]),
    _templateObject7 = _taggedTemplateLiteral(["<p>", " '$", "'?</p>"], ["<p>", " '$", "'?</p>"]),
    _templateObject8 = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject9 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject10 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject11 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject13 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject14 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo' data-album-id='", "' data-id='", "' test=\"test\">\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo' data-album-id='", "' data-id='", "' test=\"test\">\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject15 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject16 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject17 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject18 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autoplay><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autoplay><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject19 = _taggedTemplateLiteral(["<img id='image' class='", "' src='", "' draggable='false'>"], ["<img id='image' class='", "' src='", "' draggable='false'>"]),
    _templateObject20 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject21 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject22 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject24 = _taggedTemplateLiteral(["<a class='tag'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='tag'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject25 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject26 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject27 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject28 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject29 = _taggedTemplateLiteral(["\n\t\t\t  <p class='signIn'>\n\t\t\t\t  <input class='text' name='username' autocomplete='"], ["\n\t\t\t  <p class='signIn'>\n\t\t\t\t  <input class='text' name='username' autocomplete='"]),
    _templateObject30 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='", "'>"]),
    _templateObject31 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='", "'>"]),
    _templateObject32 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject33 = _taggedTemplateLiteral(["\n\t\t\t\t\t <tr>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t </tr>\n\t\t\t\t\t "], ["\n\t\t\t\t\t <tr>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t </tr>\n\t\t\t\t\t "]),
    _templateObject34 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject35 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

function gup(b) {

	b = b.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

	var a = "[\\?&]" + b + "=([^&#]*)";
	var d = new RegExp(a);
	var c = d.exec(window.location.href);

	if (c === null) return '';else return c[1];
}
(function ($) {
	var Swipe = function Swipe(el) {
		var self = this;

		this.el = $(el);
		this.pos = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 } };
		this.startTime = null;

		el.on('touchstart', function (e) {
			self.touchStart(e);
		});
		el.on('touchmove', function (e) {
			self.touchMove(e);
		});
		el.on('touchend', function () {
			self.swipeEnd();
		});
		el.on('mousedown', function (e) {
			self.mouseDown(e);
		});
	};

	Swipe.prototype = {
		touchStart: function touchStart(e) {
			var touch = e.originalEvent.touches[0];

			this.swipeStart(e, touch.pageX, touch.pageY);
		},

		touchMove: function touchMove(e) {
			var touch = e.originalEvent.touches[0];

			this.swipeMove(e, touch.pageX, touch.pageY);
		},

		mouseDown: function mouseDown(e) {
			var self = this;

			this.swipeStart(e, e.pageX, e.pageY);

			this.el.on('mousemove', function (e) {
				self.mouseMove(e);
			});
			this.el.on('mouseup', function () {
				self.mouseUp();
			});
		},

		mouseMove: function mouseMove(e) {
			this.swipeMove(e, e.pageX, e.pageY);
		},

		mouseUp: function mouseUp(e) {
			this.swipeEnd(e);

			this.el.off('mousemove');
			this.el.off('mouseup');
		},

		swipeStart: function swipeStart(e, x, y) {
			this.pos.start.x = x;
			this.pos.start.y = y;
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.startTime = new Date().getTime();

			this.trigger('swipeStart', e);
		},

		swipeMove: function swipeMove(e, x, y) {
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.trigger('swipeMove', e);
		},

		swipeEnd: function swipeEnd(e) {
			this.trigger('swipeEnd', e);
		},

		trigger: function trigger(e, originalEvent) {
			var self = this;

			var event = $.Event(e),
			    x = self.pos.start.x - self.pos.end.x,
			    y = self.pos.end.y - self.pos.start.y,
			    radians = Math.atan2(y, x),
			    direction = 'up',
			    distance = Math.round(Math.sqrt(Math.pow(x, 2) + Math.pow(y, 2))),
			    angle = Math.round(radians * 180 / Math.PI),
			    speed = Math.round(distance / (new Date().getTime() - self.startTime) * 1000);

			if (angle < 0) {
				angle = 360 - Math.abs(angle);
			}

			if (angle <= 45 && angle >= 0 || angle <= 360 && angle >= 315) {
				direction = 'left';
			} else if (angle >= 135 && angle <= 225) {
				direction = 'right';
			} else if (angle > 45 && angle < 135) {
				direction = 'down';
			}

			event.originalEvent = originalEvent;

			event.swipe = { x: x, y: y, direction: direction, distance: distance, angle: angle, speed: speed };

			$(self.el).trigger(event);
		}
	};

	$.fn.swipe = function () {
		var swipe = new Swipe(this);

		return this;
	};
})(jQuery);
/**
 * @description Takes care of every action an album can handle and execute.
 */

album = {

	json: null

};

album.isSmartID = function (id) {

	return id === '0' || id === 'f' || id === 's' || id === 'r';
};

album.getParent = function () {

	if (album.json == null || album.isSmartID(album.json.id) === true || album.json.parent === 0) return '';

	return album.json.parent;
};

album.getID = function () {

	var id = null;

	var isID = function isID(id) {
		if (id === '0' || id === 'f' || id === 's' || id === 'r') return true;
		return $.isNumeric(id);
	};

	if (photo.json) id = photo.json.album;else if (album.json) id = album.json.id;

	// Search
	if (isID(id) === false) id = $('.album:hover, .album.active').attr('data-id');
	if (isID(id) === false) id = $('.photo:hover, .photo.active').attr('data-album-id');

	if (isID(id) === true) return id;else return false;
};

album.getByID = function (photoID) {

	// Function returns the JSON of a photo

	if (photoID == null || !album.json || !album.json.photos) {
		lychee.error('Error: Album json not found !');
		return undefined;
	}

	var i = 0;
	while (i < album.json.photos.length) {
		if (parseInt(album.json.photos[i].id) === parseInt(photoID)) {
			return album.json.photos[i];
		}
		i++;
	}

	lychee.error('Error: photo ' + photoID + ' not found !');
	return undefined;
};

album.deleteByID = function (photoID) {

	if (photoID == null || !album.json || !album.json.photos) {
		lychee.error('Error: Album json not found !');
		return false;
	}

	var deleted = false;

	$.each(album.json.photos, function (i) {

		if (parseInt(album.json.photos[i].id) === parseInt(photoID)) {
			album.json.photos.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

album.getParent = function () {

	if (album.json == null || album.isSmartID(album.json.id) === true || album.json.parent_id === 0) return 0;

	return album.json.parent_id;
};

album.load = function (albumID) {
	var refresh = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	password.get(albumID, function () {

		if (refresh === false) lychee.animate('.content', 'contentZoomOut');

		var startTime = new Date().getTime();

		var params = {
			albumID: albumID,
			password: password.value
		};

		api.post('Album::get', params, function (data) {

			var waitTime = 0;

			if (data === 'Warning: Album private!') {

				if (document.location.hash.replace('#', '').split('/')[1] !== undefined) {
					// Display photo only
					lychee.setMode('view');
				} else {
					// Album not public
					lychee.content.show();
					lychee.goto();
				}
				return false;
			}

			if (data === 'Warning: Wrong password!') {
				album.load(albumID, refresh);
				return false;
			}

			album.json = data;

			// Calculate delay
			var durationTime = new Date().getTime() - startTime;
			if (durationTime > 300) waitTime = 0;else waitTime = 300 - durationTime;

			// Skip delay when refresh is true
			// Skip delay when opening a blank Lychee
			if (refresh === true) waitTime = 0;
			if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;

			setTimeout(function () {

				view.album.init();

				if (refresh === false) {
					lychee.animate(lychee.content, 'contentZoomIn');
					header.setMode('album');
				}
			}, waitTime);
		});
	});
};

album.parse = function () {

	if (!album.json.title) album.json.title = lychee.locale['UNTITLED'];
};

album.add = function () {

	var action = function action(data) {

		// let title = data.title;

		var isNumber = function isNumber(n) {
			return !isNaN(parseInt(n, 10)) && isFinite(n);
		};

		basicModal.close();

		var params = {
			title: data.title,
			parent_id: 0
		};

		if (visible.albums()) {
			params.parent_id = 0;
		} else if (visible.album()) {
			params.parent_id = album.json.id;
		} else if (visible.photo()) {
			params.parent_id = photo.json.album;
		}

		api.post('Album::add', params, function (data) {

			if (data !== false && isNumber(data)) {
				albums.refresh();
				lychee.goto(data);
			} else {
				lychee.error(null, params, data);
			}
		});
	};

	basicModal.show({
		body: "<p>" + lychee.locale['TITLE_NEW_ALBUM'] + " <input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='Untitled'></p>",
		buttons: {
			action: {
				title: lychee.locale['CREATE_ALBUM'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

album.addandmove = function (photoIDs) {

	var action = function action(data) {

		// let title = data.title;

		var isNumber = function isNumber(n) {
			return !isNaN(parseInt(n, 10)) && isFinite(n);
		};

		basicModal.close();

		var params = {
			title: data.title,
			parent_id: 0 // root
		};

		api.post('Album::add', params, function (data) {

			if (data !== false && isNumber(data)) {
				albums.refresh();
				photo.setAlbum(photoIDs, data);
				lychee.goto(data);
			} else {
				lychee.error(null, params, data);
			}
		});
	};

	basicModal.show({
		body: "<p>" + lychee.locale['TITLE_NEW_ALBUM'] + " <input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='Untitled'></p>",
		buttons: {
			action: {
				title: lychee.locale['CREATE_ALBUM'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

album.delete = function (albumIDs) {

	var action = {};
	var cancel = {};
	var msg = '';

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	action.fn = function () {

		basicModal.close();

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post('Album::delete', params, function (data) {

			if (visible.albums()) {

				albumIDs.forEach(function (id) {
					view.albums.content.delete(id);
					albums.deleteByID(id);
				});
			} else {

				albums.refresh();
				lychee.goto();
			}

			if (data !== true) lychee.error(null, params, data);
		});
	};

	if (albumIDs.toString() === '0') {

		action.title = lychee.locale['CLEAR_UNSORTED'];
		cancel.title = lychee.locale['KEEP_UNSORTED'];

		msg = "<p>" + lychee.locale['DELETE_UNSORTED_CONFIRM'] + "</p>";
	} else if (albumIDs.length === 1) {

		var albumTitle = '';

		action.title = lychee.locale['DELETE_ALBUM_QUESTION'];
		cancel.title = lychee.locale['KEEP_ALBUM'];

		// Get title
		if (album.json) albumTitle = album.json.title;else if (albums.json) albumTitle = albums.getByID(albumIDs).title;

		// Fallback for album without a title
		if (albumTitle === '') albumTitle = lychee.locale['UNTITLED'];

		msg = lychee.html(_templateObject) + lychee.locale['DELETE_ALBUM_CONFIRMATION_1'] + (" '" + albumTitle + "' ") + lychee.locale['DELETE_ALBUM_CONFIRMATION_2'] + "</p>";
	} else {

		action.title = lychee.locale['DELETE_ALBUMS_QUESTION'];
		cancel.title = lychee.locale['KEEP_ALBUMS'];

		msg = lychee.html(_templateObject) + lychee.locale['DELETE_ALBUMS_CONFIRMATION_1'] + (" " + albumIDs.length + " ") + lychee.locale['DELETE_ALBUMS_CONFIRMATION_2'] + "</p>";
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: 'red'
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

album.setTitle = function (albumIDs) {

	var oldTitle = '';
	var msg = '';

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	if (albumIDs.length === 1) {

		// Get old title if only one album is selected
		if (album.json) oldTitle = album.json.title;else if (albums.json) oldTitle = albums.getByID(albumIDs).title;
	}

	var action = function action(data) {

		basicModal.close();

		var newTitle = data.title;

		if (visible.album()) {

			// Rename only one album

			album.json.title = newTitle;
			view.album.title();

			if (albums.json) albums.getByID(albumIDs[0]).title = newTitle;
		} else if (visible.albums()) {

			// Rename all albums

			albumIDs.forEach(function (id) {
				albums.getByID(id).title = newTitle;
				view.albums.content.title(id);
			});
		}

		var params = {
			albumIDs: albumIDs.join(),
			title: newTitle
		};

		api.post('Album::setTitle', params, function (data) {

			if (data !== true) lychee.error(null, params, data);
		});
	};

	var input = lychee.html(_templateObject2, oldTitle);

	if (albumIDs.length === 1) msg = lychee.html(_templateObject3, lychee.locale['ALBUM_NEW_TITLE'], input);else msg = lychee.html(_templateObject4, lychee.locale['ALBUMS_NEW_TITLE_1'], albumIDs.length, lychee.locale['ALBUMS_NEW_TITLE_2'], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['ALBUM_SET_TITLE'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

album.setDescription = function (albumID) {

	var oldDescription = album.json.description;

	var action = function action(data) {

		var description = data.description;

		basicModal.close();

		if (visible.album()) {
			album.json.description = description;
			view.album.description();
		}

		var params = {
			albumID: albumID,
			description: description
		};

		api.post('Album::setDescription', params, function (data) {

			if (data !== true) lychee.error(null, params, data);
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject5, lychee.locale['ALBUM_NEW_DESCRIPTION'], oldDescription),
		buttons: {
			action: {
				title: lychee.locale['ALBUM_SET_DESCRIPTION'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

album.setPublic = function (albumID, modal, e) {

	var password = '';

	albums.refresh();

	if (modal === true) {

		var text = '';
		var action = {};

		action.fn = function () {

			// Call setPublic function without showing the modal
			album.setPublic(album.getID(), false, e);
		};

		// Album public = Editing a shared album
		if (album.json.public === '1') {

			action.title = lychee.locale['EDIT_SHARING_TITLE'];
			text = lychee.locale['EDIT_SHARING_TEXT'];
		} else {

			action.title = lychee.locale['SHARE_ALBUM'];
			text = lychee.locale['SHARE_ALBUM_TEXT'];
		}

		var msg = "\n\t\t\t\t  <p class='less'>" + text + "</p>\n\t\t\t\t  <form>\n\t\t\t\t\t  <div class='choice'>\n\t\t\t\t\t\t  <label>\n\t\t\t\t\t\t\t  <input type='checkbox' name='hidden'>\n\t\t\t\t\t\t\t  <span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t\t  <span class='label'>" + lychee.locale['ALBUM_HIDDEN'] + "</span>\n\t\t\t\t\t\t  </label>\n\t\t\t\t\t\t  <p>" + lychee.locale['ALBUM_HIDDEN_EXPL'] + "</p>\n\t\t\t\t\t  </div>\n\t\t\t\t\t  <div class='choice'>\n\t\t\t\t\t\t  <label>\n\t\t\t\t\t\t\t  <input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t\t  <span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t\t  <span class='label'>" + lychee.locale['ALBUM_DOWNLOADABLE'] + "</span>\n\t\t\t\t\t\t  </label>\n\t\t\t\t\t\t  <p>" + lychee.locale['ALBUM_DOWNLOADABLE_EXPL'] + "</p>\n\t\t\t\t\t  </div>\n\t\t\t\t\t  <div class='choice'>\n\t\t\t\t\t\t  <label>\n\t\t\t\t\t\t\t  <input type='checkbox' name='password'>\n\t\t\t\t\t\t\t  <span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t\t  <span class='label'>" + lychee.locale['ALBUM_PASSWORD_PROT'] + "</span>\n\t\t\t\t\t\t  </label>\n\t\t\t\t\t\t  <p>" + lychee.locale['ALBUM_PASSWORD_PROT_EXPL'] + "</p>\n\t\t\t\t\t\t  <input class='text' name='passwordtext' type='password' placeholder='password' value=''>\n\t\t\t\t\t  </div>\n\t\t\t\t  </form>\n\t\t\t\t  ";

		basicModal.show({
			body: msg,
			buttons: {
				action: {
					title: action.title,
					fn: action.fn
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});

		if (album.json.public === '1' && album.json.visible === '0') $('.basicModal .choice input[name="hidden"]').click();
		if (album.json.downloadable === '1') $('.basicModal .choice input[name="downloadable"]').click();

		$('.basicModal .choice input[name="password"]').on('change', function () {

			if ($(this).prop('checked') === true) $('.basicModal .choice input[name="passwordtext"]').show().focus();else $('.basicModal .choice input[name="passwordtext"]').hide();
		});

		return true;
	}

	// Set data
	if (basicModal.visible()) {

		// Visible modal => Set album public
		album.json.public = '1';

		// Set visible
		if ($('.basicModal .choice input[name="hidden"]:checked').length === 1) album.json.visible = '0';else album.json.visible = '1';

		// Set downloadable
		if ($('.basicModal .choice input[name="downloadable"]:checked').length === 1) album.json.downloadable = '1';else album.json.downloadable = '0';

		// Set password
		if ($('.basicModal .choice input[name="password"]:checked').length === 1) {
			password = $('.basicModal .choice input[name="passwordtext"]').val();
			album.json.password = '1';
		} else {
			password = '';
			album.json.password = '0';
		}

		// Modal input has been processed, now it can be closed
		basicModal.close();
	} else {

		// Modal not visible => Set album private
		album.json.public = '0';
	}

	// Set data and refresh view
	if (visible.album()) {

		album.json.visible = album.json.public === '0' ? '1' : album.json.visible;
		album.json.downloadable = album.json.public === '0' ? '0' : album.json.downloadable;
		album.json.password = album.json.public === '0' ? '0' : album.json.password;

		view.album.public();
		view.album.hidden();
		view.album.downloadable();
		view.album.password();

		if (album.json.public === '1') contextMenu.shareAlbum(albumID, e);
	}

	var params = {
		albumID: albumID,
		public: album.json.public,
		password: password,
		visible: album.json.visible,
		downloadable: album.json.downloadable
	};

	api.post('Album::setPublic', params, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

album.share = function (service) {

	var url = location.href;

	switch (service) {
		case 'twitter':
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case 'facebook':
			window.open("http://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(album.json.title));
			break;
		case 'mail':
			location.href = "mailto:?subject=" + encodeURI(album.json.title) + "&body=" + encodeURI(url);
			break;
	}
};

album.getArchive = function (albumID) {

	var link = '';
	// double check with API_V2 this will not work...
	var url = api.path + "?function=Album::getArchive&albumID=" + albumID;

	if (location.href.indexOf('index.html') > 0) link = location.href.replace(location.hash, '').replace('index.html', url);else link = location.href.replace(location.hash, '') + url;

	if (lychee.publicMode === true) link += "&password=" + encodeURIComponent(password.value);

	location.href = link;
};

album.merge = function (albumIDs) {

	var title = '';
	var sTitle = '';
	var msg = '';

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	// Get title of first album
	if (albums.json) title = albums.getByID(albumIDs[0]).title;

	// Fallback for first album without a title
	if (title === '') title = lychee.locale['UNTITLED'];

	if (albumIDs.length === 2) {

		// Get title of second album
		if (albums.json) sTitle = albums.getByID(albumIDs[1]).title;

		// Fallback for second album without a title
		if (sTitle === '') sTitle = lychee.locale['UNTITLED'];

		msg = lychee.html(_templateObject6, lychee.locale['ALBUM_MERGE_1'], sTitle, lychee.locale['ALBUM_MERGE_2'], title);
	} else {

		msg = lychee.html(_templateObject7, lychee.locale['ALBUMS_MERGE'], title);
	}

	var action = function action() {

		basicModal.close();

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post('Album::merge', params, function (data) {

			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				album.reload();
			}
		});
	};

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['MERGE_ALBUM'],
				fn: action,
				class: 'red'
			},
			cancel: {
				title: lychee.locale['DONT_MERGE'],
				fn: basicModal.close
			}
		}
	});
};

album.move = function (albumIDs) {
	var titles = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];


	var title = '';
	var sTitle = '';
	var msg = '';

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	// Get title of first album
	if (albums.json && albums.getByID(albumIDs[0])) {
		title = albums.getByID(albumIDs[0]).title;
	} else {
		title = lychee.locale['ROOT'];
	}

	// Fallback for first album without a title
	if (title === '') title = lychee.locale['UNTITLED'];

	if (albumIDs.length === 2) {

		// Get title of second album
		if (albums.json) sTitle = albums.getByID(albumIDs[1]).title;

		// Fallback for second album without a title
		if (sTitle === '') sTitle = lychee.locale['UNTITLED'];

		msg = lychee.html(_templateObject6, lychee.locale['ALBUM_MOVE_1'], sTitle, lychee.locale['ALBUM_MOVE_2'], title);
	} else {

		msg = lychee.html(_templateObject7, lychee.locale['ALBUMS_MOVE'], title);
	}

	var action = function action() {

		basicModal.close();

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post('Album::move', params, function (data) {

			if (data !== true) lychee.error(null, params, data);else {
				album.reload();
			}
		});
	};

	basicModal.show({
		body: msg, //getMessage(albumIDs, titles, 'move'),
		buttons: {
			action: {
				title: lychee.locale['MOVE_ALBUMS'],
				fn: action,
				class: 'red'
			},
			cancel: {
				title: lychee.locale['NOT_MOVE_ALBUMS'],
				fn: basicModal.close
			}
		}
	});
};

album.reload = function () {

	var albumID = album.getID();

	album.refresh();
	albums.refresh();

	if (visible.album()) lychee.goto(albumID);else lychee.goto();
};

album.refresh = function () {

	album.json = null;
};
/**
 * @description Takes care of every action albums can handle and execute.
 */

albums = {

	json: null

};

albums.load = function () {

	var startTime = new Date().getTime();

	lychee.animate('.content', 'contentZoomOut');

	if (albums.json === null) {

		api.post('Albums::get', {}, function (data) {

			var waitTime = 0;

			// Smart Albums
			if (lychee.publicMode === false && data.smartalbums != null) albums._createSmartAlbums(data.smartalbums);

			albums.json = data;

			// Calculate delay
			var durationTime = new Date().getTime() - startTime;
			if (durationTime > 300) waitTime = 0;else waitTime = 300 - durationTime;

			// Skip delay when opening a blank Lychee
			if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;
			if (visible.album() && lychee.content.html() === '') waitTime = 0;

			setTimeout(function () {
				header.setMode('albums');
				view.albums.init();
				lychee.animate(lychee.content, 'contentZoomIn');
			}, waitTime);
		});
	} else {

		setTimeout(function () {
			header.setMode('albums');
			view.albums.init();
			lychee.animate(lychee.content, 'contentZoomIn');
		}, 300);
	}
};

albums.parse = function (album) {

	if (album.password === '1' && lychee.publicMode === true) {
		album.thumbs[0] = 'Lychee-front/images/password.svg';
		album.thumbs[1] = 'Lychee-front/images/password.svg';
		album.thumbs[2] = 'Lychee-front/images/password.svg';
	} else {
		if (!album.thumbs[0]) album.thumbs[0] = 'Lychee-front/images/no_images.svg';
		if (!album.thumbs[1]) album.thumbs[1] = 'Lychee-front/images/no_images.svg';
		if (!album.thumbs[2]) album.thumbs[2] = 'Lychee-front/images/no_images.svg';
	}
};

albums._createSmartAlbums = function (data) {

	data.unsorted = {
		id: 0,
		title: lychee.locale['UNSORTED'],
		sysdate: data.unsorted.num + ' ' + lychee.locale['NUM_PHOTOS'],
		unsorted: '1',
		thumbs: data.unsorted.thumbs,
		types: data.unsorted.types
	};

	data.starred = {
		id: 'f',
		title: lychee.locale['STARED'],
		sysdate: data.starred.num + ' ' + lychee.locale['NUM_PHOTOS'],
		star: '1',
		thumbs: data.starred.thumbs,
		types: data.starred.types
	};

	data.public = {
		id: 's',
		title: lychee.locale['PUBLIC'],
		sysdate: data.public.num + ' ' + lychee.locale['NUM_PHOTOS'],
		public: '1',
		thumbs: data.public.thumbs,
		hidden: '1',
		types: data.public.types
	};

	data.recent = {
		id: 'r',
		title: lychee.locale['RECENT'],
		sysdate: data.recent.num + ' ' + lychee.locale['NUM_PHOTOS'],
		recent: '1',
		thumbs: data.recent.thumbs,
		types: data.recent.types
	};
};

albums.getByID = function (albumID) {

	// Function returns the JSON of an album

	if (albumID == null) return undefined;
	if (!albums.json) return undefined;
	if (!albums.json.albums) return undefined;

	var json = undefined;

	var func = function func() {
		if (parseInt(this.id, 10) === parseInt(albumID, 10)) {
			json = this;
			return false; // stop the loop
		}
	};

	if (albums.json.shared_albums !== null) $.each(albums.json.albums, func);

	if (json === undefined && albums.json.shared_albums !== null) $.each(albums.json.shared_albums, func);

	return json;
};

albums.deleteByID = function (albumID) {

	// Function returns the JSON of an album

	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var deleted = false;

	$.each(albums.json.albums, function (i) {

		if (parseInt(albums.json.albums[i].id) === parseInt(albumID)) {
			albums.json.albums.splice(i, 1);
			deleted = true;
			return false; // stop the loop
		}
	});

	if (deleted === false) {
		if (!albums.json.shared_albums) return undefined;
		$.each(albums.json.shared_albums, function (i) {

			if (parseInt(albums.json.shared_albums[i].id) === parseInt(albumID)) {
				albums.json.shared_albums.splice(i, 1);
				deleted = true;
				return false; // stop the loop
			}
		});
	}

	return deleted;
};

albums.refresh = function () {
	albums.json = null;
};

/**
 * @description This module communicates with Lychee's API
 */

api = {

	path: 'php/index.php',
	onError: null

};

api.get_url = function (fn) {

	var api_url = '';

	if (lychee.api_V2) {
		// because the api is defined directly by the function called in the route.php
		api_url = 'api/' + fn;
	} else {
		api_url = api.path;
	}

	return api_url;
};

api.post = function (fn, params, callback) {

	loadingBar.show();

	params = $.extend({ function: fn }, params);

	var api_url = api.get_url(fn);

	var success = function success(data) {

		setTimeout(loadingBar.hide, 100);

		// Catch errors
		if (typeof data === 'string' && data.substring(0, 7) === 'Error: ') {
			api.onError(data.substring(7, data.length), params, data);
			return false;
		}

		callback(data);
	};

	var error = function error(jqXHR, textStatus, errorThrown) {

		api.onError('Server error or API not found.', params, errorThrown);
	};

	$.ajax({
		type: 'POST',
		url: api_url,
		data: params,
		dataType: 'json',
		success: success,
		error: error
	});
};

api.post_raw = function (fn, params, callback) {
	loadingBar.show();

	params = $.extend({ function: fn }, params);

	var api_url = api.get_url(fn);

	var success = function success(data) {

		setTimeout(loadingBar.hide, 100);

		// Catch errors
		if (typeof data === 'string' && data.substring(0, 7) === 'Error: ') {
			api.onError(data.substring(7, data.length), params, data);
			return false;
		}

		callback(data);
	};

	var error = function error(jqXHR, textStatus, errorThrown) {

		api.onError('Server error or API not found.', params, errorThrown);
	};

	$.ajax({
		type: 'POST',
		url: api_url,
		data: params,
		dataType: 'text',
		success: success,
		error: error
	});
};

/**
 * @description This module is used to generate HTML-Code.
 */

build = {};

build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';


	var html = '';

	html += lychee.html(_templateObject8, classes, icon);

	return html;
};

build.divider = function (title) {

	var html = '';

	html += lychee.html(_templateObject9, title);

	return html;
};

build.editIcon = function (id) {

	var html = '';

	html += lychee.html(_templateObject10, id, build.iconic('pencil'));

	return html;
};

build.multiselect = function (top, left) {

	return lychee.html(_templateObject11, top, left);
};

build.getThumbnailHtml = function (thumb, retinaThumbUrl, type) {
	var isVideo = type && type.indexOf('video') > -1;
	if (thumb == 'uploads/thumb/' && isVideo) {
		return "<span class=\"thumbimg\"><img src='play-icon.png' width='200' height='200' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	return "<span class=\"thumbimg" + (isVideo ? ' video' : '') + "\"><img src='" + thumb + "' srcset='" + retinaThumbUrl + " 1.5x' width='200' height='200' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
};

build.album = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var html = '';
	var date_stamp = data.sysdate;
	var sortingAlbums = [];

	var _lychee$retinize = lychee.retinize(data.thumbs[0]),
	    retinaThumbUrl = _lychee$retinize.path,
	    isPhoto = _lychee$retinize.isPhoto;

	// In the special case of take date sorting use the take stamps as title


	if (lychee.sortingAlbums !== '' && data.min_takestamp && data.max_takestamp) {

		sortingAlbums = lychee.sortingAlbums.replace('ORDER BY ', '').split(' ');
		if (sortingAlbums[0] === 'max_takestamp' || sortingAlbums[0] === 'min_takestamp') {
			if (data.min_takestamp !== '' && data.max_takestamp !== '') {
				date_stamp = data.min_takestamp === data.max_takestamp ? data.max_takestamp : data.min_takestamp + ' - ' + data.max_takestamp;
			} else if (data.min_takestamp !== '' && sortingAlbums[0] === 'min_takestamp') {
				date_stamp = data.min_takestamp;
			} else if (data.max_takestamp !== '' && sortingAlbums[0] === 'max_takestamp') {
				date_stamp = data.max_takestamp;
			}
		}
	}

	html += lychee.html(_templateObject12, disabled ? "disabled" : "", data.id, build.getThumbnailHtml(data.thumbs[2], data.thumbs[2], data.types[2]), build.getThumbnailHtml(data.thumbs[1], data.thumbs[1], data.types[1]), build.getThumbnailHtml(data.thumbs[0], data.thumbs[0], data.types[0]), data.title, data.title, date_stamp);

	if (lychee.publicMode === false) {

		html += lychee.html(_templateObject13, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' ? 'badge--visible' : '', data.hidden === '1' ? 'badge--not--hidden' : 'badge--hidden', build.iconic('eye'), data.unsorted === '1' ? 'badge--visible' : '', build.iconic('list'), data.recent === '1' ? 'badge--visible badge--list' : '', build.iconic('clock'), data.password === '1' ? 'badge--visible' : '', build.iconic('lock-locked'));
	}

	html += '</div>';

	return html;
};

build.photo = function (data) {

	var html = '';

	var _lychee$retinize2 = lychee.retinize(data.thumbUrl),
	    retinaThumbUrl = _lychee$retinize2.path;

	html += lychee.html(_templateObject14, data.album, data.id, build.getThumbnailHtml(data.thumbUrl, retinaThumbUrl, data.type), data.title, data.title);

	if (data.cameraDate === '1') html += lychee.html(_templateObject15, build.iconic('camera-slr'), data.sysdate);else html += lychee.html(_templateObject16, data.sysdate);

	html += "</div>";

	if (lychee.publicMode === false) {

		html += lychee.html(_templateObject17, data.star === '1' ? 'badge--visible' : '', build.iconic('star'), data.public === '1' && album.json.public !== '1' ? 'badge--visible' : '', build.iconic('eye'));
	}

	html += "</div>";

	return html;
};

build.imageview = function (data, visibleControls) {

	var html = '';
	var hasMedium = data.medium !== '';

	if (data.type.indexOf('video') > -1) {
		html += lychee.html(_templateObject18, visibleControls === true ? '' : 'full', data.url);
	} else if (hasMedium === false) {
		html += lychee.html(_templateObject19, visibleControls === true ? '' : 'full', data.url);
	} else {
		html += lychee.html(_templateObject19, visibleControls === true ? '' : 'full', data.medium);
	}

	html += "\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic('caret-left') + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic('caret-right') + "</a></div>\n\t\t\t";

	return html;
};

build.no_content = function (typ) {

	var html = '';

	html += lychee.html(_templateObject20, build.iconic(typ));

	switch (typ) {
		case 'magnifying-glass':
			html += lychee.html(_templateObject21, lychee.locale['VIEW_NO_RESULT']);
			break;
		case 'eye':
			html += lychee.html(_templateObject21, lychee.locale['VIEW_NO_PUBLIC_ALBUMS']);
			break;
		case 'cog':
			html += lychee.html(_templateObject21, lychee.locale['VIEW_NO_CONFIGURATION']);
			break;
		case 'question-mark':
			html += lychee.html(_templateObject21, lychee.locale['VIEW_PHOTO_NOT_FOUND']);
			break;
	}

	html += "</div>";

	return html;
};

build.uploadModal = function (title, files) {

	var html = '';

	html += lychee.html(_templateObject22, title);

	var i = 0;

	while (i < files.length) {

		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + '...' + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject23, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

build.tags = function (tags) {

	var html = '';

	if (tags !== '') {

		tags = tags.split(',');

		tags.forEach(function (tag, index, array) {
			html += lychee.html(_templateObject24, tag, index, build.iconic('x'));
		});
	} else {

		html = lychee.html(_templateObject25, lychee.locale['NO_TAGS']);
	}

	return html;
};

build.user = function (user) {
	var html = lychee.html(_templateObject26, user.id, user.id, user.username, user.id, user.id);

	return html;
};

/**
 * @description This module is used for the context menu.
 */

contextMenu = {};

contextMenu.add = function (e) {

	var items = [{ title: build.iconic('image') + lychee.locale['UPLOAD_PHOTO'], fn: function fn() {
			return $('#upload_files').click();
		} }, {}, { title: build.iconic('link-intact') + lychee.locale['IMPORT_LINK'], fn: upload.start.url }, { title: build.iconic('dropbox', 'ionicons') + lychee.locale['IMPORT_DROPBOX'], fn: upload.start.dropbox }, { title: build.iconic('terminal') + lychee.locale['IMPORT_SERVER'], fn: upload.start.server }, {}, { title: build.iconic('folder') + lychee.locale['NEW_ALBUM'], fn: album.add }];

	if (lychee.api_V2 && !lychee.admin) {
		items.splice(3, 2);
	}

	basicContext.show(items, e.originalEvent);

	upload.notify();
};

contextMenu.album = function (albumID, e) {

	// Notice for 'Merge':
	// fn must call basicContext.close() first,
	// in order to keep the selection

	if (album.isSmartID(albumID)) return false;

	// Show merge-item when there's more than one album
	var showMerge = albums.json && albums.json.albums && Object.keys(albums.json.albums).length > 1;

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
			return album.setTitle([albumID]);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE'], visible: showMerge, fn: function fn() {
			basicContext.close();contextMenu.mergeAlbum(albumID, e);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE'], fn: function fn() {
			basicContext.close();contextMenu.moveAlbum([albumID], e);
		} }, { title: build.iconic('trash') + lychee.locale['DELETE'], fn: function fn() {
			return album.delete([albumID]);
		} }];

	if (!lychee.sub_albums) {
		items.splice(2, 1);
	}

	$('.album[data-id="' + albumID + '"]').addClass('active');

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.albumMulti = function (albumIDs, e) {

	multiselect.stopResize();

	// Automatically merge selected albums when albumIDs contains more than one album
	// Show list of albums otherwise
	var autoMerge = albumIDs.length > 1;

	// Show merge-item when there's more than one album
	var showMerge = albums.json && albums.json.albums && Object.keys(albums.json.albums).length > 1;

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME_ALL'], fn: function fn() {
			return album.setTitle(albumIDs);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE_ALL'], visible: showMerge && autoMerge, fn: function fn() {
			return album.merge(albumIDs);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE'], visible: showMerge && !autoMerge, fn: function fn() {
			basicContext.close();contextMenu.mergeAlbum(albumIDs[0], e);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE_ALL'], fn: function fn() {
			basicContext.close();contextMenu.moveAlbum(albumIDs, e);
		} }, { title: build.iconic('trash') + lychee.locale['DELETE_ALL'], fn: function fn() {
			return album.delete(albumIDs);
		} }];

	if (!lychee.sub_albums) {
		items.splice(3, 1);
	}

	items.push();

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.buildList = function (lists, exclude, action) {
	var parent = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
	var layer = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;


	var items = [];

	var i = 0;
	while (i < lists.length) {
		if (layer === 0 && !lists[i].parent_id || lists[i].parent_id === parent) {
			(function () {

				var item = lists[i];

				var thumb = 'Lychee-front/images/no_cover.svg';
				if (item.thumbs && item.thumbs[0]) thumb = item.thumbs[0];else if (item.thumbUrl) thumb = item.thumbUrl;

				if (item.title === '') item.title = lychee.locale['UNTITLED'];

				var prefix = layer > 0 ? '&nbsp;&nbsp;'.repeat(layer - 1) + '└ ' : '';

				var html = lychee.html(_templateObject27, prefix, thumb, item.title);

				items.push({
					title: html,
					disabled: exclude.indexOf(item.id) !== -1,
					fn: function fn() {
						return action(item);
					}
				});

				items = items.concat(contextMenu.buildList(lists, exclude, action, item.id, layer + 1));
			})();
		}

		i++;
	}

	return items;
};

contextMenu.albumTitle = function (albumID, e) {

	api.post('Albums::get', {}, function (data) {

		var items = [];

		if (data.albums && data.albums.length > 1) {

			items = items.concat(contextMenu.buildList(data.albums, [parseInt(albumID, 10)], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (data.shared_albums && data.shared_albums.length > 1) {

			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.shared_albums, [parseInt(albumID, 10)], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (items.length > 0) {
			items.unshift({});
		}

		items.unshift({ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
				return album.setTitle([albumID]);
			} });

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.mergeAlbum = function (albumID, e) {

	api.post('Albums::get', {}, function (data) {

		var items = [];

		if (data.albums && data.albums.length > 1) {

			if (data.albums && data.albums.length > 1) {
				items = items.concat(contextMenu.buildList(data.albums, [parseInt(albumID, 10)], function (a) {
					return album.merge([albumID, a.id]);
				}));
				items.unshift({});
			}
		}

		if (items.length === 0) return false;

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.photo = function (photoID, e) {

	// Notice for 'Move':
	// fn must call basicContext.close() first,
	// in order to keep the selection

	var items = [{ title: build.iconic('star') + lychee.locale['STAR'], fn: function fn() {
			return photo.setStar([photoID]);
		} }, { title: build.iconic('tag') + lychee.locale['TAGS'], fn: function fn() {
			return photo.editTags([photoID]);
		} }, {}, { title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
			return photo.setTitle([photoID]);
		} }, { title: build.iconic('layers') + lychee.locale['DUPLICATE'], fn: function fn() {
			return photo.duplicate([photoID]);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE'], fn: function fn() {
			basicContext.close();contextMenu.move([photoID], e);
		} }, { title: build.iconic('trash') + lychee.locale['DELETE'], fn: function fn() {
			return photo.delete([photoID]);
		} }];

	$('.photo[data-id="' + photoID + '"]').addClass('active');

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.countSubAlbums = function (photoIDs) {

	var count = 0;

	var i = void 0,
	    j = void 0;

	if (album.albums) {

		for (i = 0; i < photoIDs.length; i++) {
			for (j = 0; j < album.albums.length; j++) {
				if (album.albums[j].id === photoIDs[i]) {
					count++;
					break;
				}
			}
		}
	}

	return count;
};

contextMenu.photoMulti = function (photoIDs, e) {

	// Notice for 'Move All':
	// fn must call basicContext.close() first,
	// in order to keep the selection and multiselect
	var subcount = contextMenu.countSubAlbums(photoIDs);
	var photocount = photoIDs.length - subcount;

	if (subcount && photocount) {
		multiselect.deselect('.photo.active, .album.active');
		multiselect.close();
		lychee.error('Please select either albums or photos!');
		return;
	}
	if (subcount) {
		contextMenu.albumMulti(photoIDs, e);
		return;
	}

	multiselect.stopResize();

	var items = [{ title: build.iconic('star') + lychee.locale['STAR_ALL'], fn: function fn() {
			return photo.setStar(photoIDs);
		} }, { title: build.iconic('tag') + lychee.locale['TAGS_ALL'], fn: function fn() {
			return photo.editTags(photoIDs);
		} }, {}, { title: build.iconic('pencil') + lychee.locale['RENAME_ALL'], fn: function fn() {
			return photo.setTitle(photoIDs);
		} }, { title: build.iconic('layers') + lychee.locale['DUPLICATE_ALL'], fn: function fn() {
			return photo.duplicate(photoIDs);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE_ALL'], fn: function fn() {
			basicContext.close();contextMenu.move(photoIDs, e);
		} }, { title: build.iconic('trash') + lychee.locale['DELETE_ALL'], fn: function fn() {
			return photo.delete(photoIDs);
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoTitle = function (albumID, photoID, e) {

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
			return photo.setTitle([photoID]);
		} }];

	var data = album.json;

	if (data.photos !== false && data.photos.length > 1) {

		items.push({});

		items = items.concat(contextMenu.buildList(data.photos, [photoID], function (a) {
			return lychee.goto(albumID + '/' + a.id);
		}));
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoMore = function (photoID, e) {

	// Show download-item when
	// a) Public mode is off
	// b) Downloadable is 1 and public mode is on
	var showDownload = lychee.publicMode === false || album.json && album.json.downloadable && album.json.downloadable === '1' && lychee.publicMode === true;

	var items = [{ title: build.iconic('fullscreen-enter') + lychee.locale['FULL_PHOTO'], fn: function fn() {
			return window.open(photo.getDirectLink());
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD'], visible: showDownload, fn: function fn() {
			return photo.getArchive(photoID);
		} }];

	basicContext.show(items, e.originalEvent);
};

contextMenu.getSubIDs = function (albums, albumID) {

	var ids = [parseInt(albumID, 10)];
	var a = void 0,
	    id = void 0;

	for (a = 0; a < albums.length; a++) {
		if (parseInt(albums[a].parent_id, 10) === parseInt(albumID, 10)) {

			var sub = contextMenu.getSubIDs(albums, albums[a].id);
			for (id = 0; id < sub.length; id++) {
				ids.push(sub[id]);
			}
		}
	}

	return ids;
};

contextMenu.moveAlbum = function (albumIDs, e) {

	api.post('Albums::get', {}, function (data) {

		var items = [];

		if (data.albums && data.albums.length > 1) {

			var title = '';
			if (albums.getByID(albumIDs[0])) {
				title = albums.getByID(albumIDs[0]).title;
			} else {
				title = lychee.locale['ROOT'];
			}
			// Disable all childs
			// It's not possible to move us into them
			var i = void 0,
			    s = void 0;
			var exclude = [];
			for (i = 0; i < albumIDs.length; i++) {
				var sub = contextMenu.getSubIDs(data.albums, albumIDs[i]);
				for (s = 0; s < sub.length; s++) {
					exclude.push(sub[s]);
				}
			}

			items = items.concat(contextMenu.buildList(data.albums, exclude, function (a) {
				return album.move([a.id].concat(albumIDs), [a.title, title]);
			}));

			items.unshift({ title: 'Root', fn: function fn() {
					return album.move([0].concat(albumIDs), ['Root', title]);
				} });
		}

		if (items.length === 0) return false;

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.move = function (photoIDs, e) {

	var items = [];

	api.post('Albums::get', {}, function (data) {

		if (data.albums.length > 0) {

			if (data.albums && data.albums.length > 1) {

				items = items.concat(contextMenu.buildList(data.albums, [album.getID()], function (a) {
					return photo.setAlbum(photoIDs, a.id);
				}));
			}

			// // Generate list of albums
			// $.each(data.albums, function() {
			//
			// 	if (!this.thumbs[0]) this.thumbs[0] = 'Lychee-front/images/no_cover.svg';
			// 	if (this.title==='') this.title = lychee.locale['UNTITLED'];
			//
			// 	let html = lychee.html`<img class='cover' width='16' height='16' src='${ this.thumbs[0] }'><div class='title'>${ this.title }</div>`;
			//
			// 	if (this.id!==album.getID()) items.push({
			// 		title: html,
			// 		fn: () => photo.setAlbum(photoIDs, this.id)
			// 	})
			//
			// });

			// Show Unsorted when unsorted is not the current album
			if (album.getID() !== '0') {

				items.unshift({});
				items.unshift({ title: lychee.locale['UNSORTED'], fn: function fn() {
						return photo.setAlbum(photoIDs, 0);
					} });
			}
		}
		items.unshift({});
		items.unshift({ title: lychee.locale['NEW_ALBUM'], fn: function fn() {
				return album.addandmove(photoIDs);
			} });

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.sharePhoto = function (photoID, e) {

	var link = photo.getViewLink(photoID);
	var iconClass = 'ionicons';

	var items = [{ title: "<input readonly id=\"link\" value=\"" + link + "\">", fn: function fn() {}, class: 'basicContext__item--noHover' }, {}, { title: build.iconic('twitter', iconClass) + 'Twitter', fn: function fn() {
			return photo.share(photoID, 'twitter');
		} }, { title: build.iconic('facebook', iconClass) + 'Facebook', fn: function fn() {
			return photo.share(photoID, 'facebook');
		} }, { title: build.iconic('envelope-closed') + 'Mail', fn: function fn() {
			return photo.share(photoID, 'mail');
		} }, { title: build.iconic('dropbox', iconClass) + 'Dropbox', visible: lychee.publicMode === false, fn: function fn() {
			return photo.share(photoID, 'dropbox');
		} }, { title: build.iconic('link-intact') + lychee.locale['DIRECT_LINK'], fn: function fn() {
			return window.open(photo.getDirectLink());
		} }, {}, { title: build.iconic('ban') + lychee.locale['MAKE_PRIVATE'], visible: lychee.publicMode === false, fn: function fn() {
			return photo.setPublic(photoID);
		} }];

	if (lychee.publicMode === true || lychee.api_V2 && !lychee.upload) {
		items.splice(7, 2);
	}

	basicContext.show(items, e.originalEvent);
	$('.basicContext input#link').focus().select();
};

contextMenu.shareAlbum = function (albumID, e) {

	var iconClass = 'ionicons';

	var items = [{ title: "<input readonly id=\"link\" value=\"" + location.href + "\">", fn: function fn() {}, class: 'basicContext__item--noHover' }, {}, { title: build.iconic('twitter', iconClass) + 'Twitter', fn: function fn() {
			return album.share('twitter');
		} }, { title: build.iconic('facebook', iconClass) + 'Facebook', fn: function fn() {
			return album.share('facebook');
		} }, { title: build.iconic('envelope-closed') + 'Mail', fn: function fn() {
			return album.share('mail');
		} }, {}, { title: build.iconic('pencil') + lychee.locale['EDIT_SHARING'], visible: lychee.publicMode === false, fn: function fn() {
			return album.setPublic(albumID, true, e);
		} }, { title: build.iconic('ban') + lychee.locale['MAKE_PRIVATE'], visible: lychee.publicMode === false, fn: function fn() {
			return album.setPublic(albumID, false);
		} }];

	if (lychee.publicMode === true || lychee.api_V2 && !lychee.upload) items.splice(5, 3);

	basicContext.show(items, e.originalEvent);
	$('.basicContext input#link').focus().select();
};

contextMenu.close = function () {

	if (!visible.contextMenu()) return false;

	basicContext.close();

	multiselect.deselect('.photo.active, .album.active');
	if (visible.multiselect()) multiselect.close();
};

csrf = {};

csrf.addLaravelCSRF = function (event, jqxhr, settings) {
	if (settings.url !== "https:" + lychee.updatePath) {
		jqxhr.setRequestHeader('X-XSRF-TOKEN', csrf.getCookie('XSRF-TOKEN'));
	}
};

csrf.escape = function (s) {
	return s.replace(/([.*+?\^${}()|\[\]\/\\])/g, '\\$1');
};

csrf.getCookie = function (name) {
	// we stop the selection at = (default json) but also at % to prevent any %3D at the end of the string
	var match = document.cookie.match(RegExp('(?:^|;\\s*)' + csrf.escape(name) + '=([^;^%]*)'));
	return match ? match[1] : null;
};

csrf.bind = function () {
	$(document).on('ajaxSend', csrf.addLaravelCSRF);
};

/**
 * @description This module takes care of the header.
 */

header = {

	_dom: $('.header')

};

header.dom = function (selector) {

	if (selector == null || selector === '') return header._dom;
	return header._dom.find(selector);
};

header.bind = function () {

	// Event Name
	var eventName = lychee.getEventName();

	header.dom('.header__title').on(eventName, function (e) {

		if ($(this).hasClass('header__title--editable') === false) return false;

		if (visible.photo()) contextMenu.photoTitle(album.getID(), photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom('#button_share').on(eventName, function (e) {
		if (photo.json.public === '1' || photo.json.public === '2' || lychee.api_V2 && !lychee.upload) contextMenu.sharePhoto(photo.getID(), e);else photo.setPublic(photo.getID(), e);
	});

	header.dom('#button_share_album').on(eventName, function (e) {
		if (album.json.public === '1' || lychee.api_V2 && !lychee.upload) contextMenu.shareAlbum(album.getID(), e);else album.setPublic(album.getID(), true, e);
	});

	header.dom('#button_signin').on(eventName, lychee.loginDialog);
	header.dom('#button_settings').on(eventName, leftMenu.open);
	header.dom('#button_info_album').on(eventName, sidebar.toggle);
	header.dom('#button_info').on(eventName, sidebar.toggle);
	header.dom('.button_add').on(eventName, contextMenu.add);
	header.dom('#button_more').on(eventName, function (e) {
		contextMenu.photoMore(photo.getID(), e);
	});
	header.dom('#button_move').on(eventName, function (e) {
		contextMenu.move([photo.getID()], e);
	});
	header.dom('.header__hostedwith').on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom('#button_trash_album').on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom('#button_trash').on(eventName, function () {
		photo.delete([photo.getID()]);
	});
	header.dom('#button_archive').on(eventName, function () {
		album.getArchive(album.getID());
	});
	header.dom('#button_star').on(eventName, function () {
		photo.setStar([photo.getID()]);
	});
	header.dom('#button_back_home').on(eventName, function () {
		lychee.goto();
	});
	header.dom('#button_back').on(eventName, function () {
		lychee.goto(album.getID());
	});

	header.dom('.header__search').on('keyup click', function () {
		search.find($(this).val());
	});
	header.dom('.header__clear').on(eventName, function () {
		header.dom('.header__search').focus();
		search.reset();
	});

	return true;
};

header.show = function () {

	lychee.imageview.removeClass('full');
	header.dom().removeClass('header--hidden');

	return true;
};

header.hide = function () {

	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {

		lychee.imageview.addClass('full');
		header.dom().addClass('header--hidden');

		return true;
	}

	return false;
};

header.setTitle = function () {
	var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'Untitled';


	var $title = header.dom('.header__title');
	var html = lychee.html(_templateObject28, title, build.iconic('caret-bottom'));

	$title.html(html);

	return true;
};

header.setMode = function (mode) {

	if (mode === 'albums' && lychee.publicMode === true) mode = 'public';

	switch (mode) {

		case 'public':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--public').addClass('header__toolbar--visible');

			return true;

		case 'albums':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--photo').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--albums').addClass('header__toolbar--visible');

			return true;

		case 'album':

			var albumID = album.getID();

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--album').addClass('header__toolbar--visible');

			// Hide download button when album empty
			if (album.json.photos === false) $('#button_archive').hide();else $('#button_archive').show();

			// Hide download button when not logged in and album not downloadable
			if (lychee.publicMode === true && album.json.downloadable === '0') $('#button_archive').hide();

			if (albumID === 's' || albumID === 'f' || albumID === 'r') {
				$('#button_info_album, #button_trash_album, #button_share_album').hide();
			} else if (albumID === '0') {
				$('#button_info_album, #button_share_album').hide();
				$('#button_trash_album').show();
			} else {
				$('#button_info_album, #button_trash_album, #button_share_album').show();
			}

			return true;

		case 'photo':

			header.dom().addClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--album').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--photo').addClass('header__toolbar--visible');

			return true;

	}

	return false;
};

header.setEditable = function (editable) {

	var $title = header.dom('.header__title');

	// Hide editable icon when not logged in
	if (lychee.publicMode === true || lychee.api_V2 && !lychee.upload) editable = false;

	if (editable) $title.addClass('header__title--editable');else $title.removeClass('header__title--editable');

	return true;
};

/**
 * @description This module is used for bindings.
 */

$(document).ready(function () {

	// Event Name
	var eventName = lychee.getEventName();

	// set CSRF protection (Laravel)
	csrf.bind();

	// Set API error handler
	api.onError = lychee.error;

	// Multiselect
	multiselect.bind();

	// Header
	header.bind();

	// Image View
	lychee.imageview.on(eventName, '.arrow_wrapper--previous', photo.previous).on(eventName, '.arrow_wrapper--next', photo.next);

	// Keyboard
	Mousetrap.bind(['left'], function () {
		if (visible.photo()) {
			$('#imageview a#previous').click();return false;
		}
	}).bind(['right'], function () {
		if (visible.photo()) {
			$('#imageview a#next').click();return false;
		}
	}).bind(['u'], function () {
		if (!visible.photo()) {
			$('#upload_files').click();return false;
		}
	}).bind(['s', 'f'], function () {
		if (visible.photo()) {
			header.dom('#button_star').click();return false;
		} else if (visible.albums()) {
			header.dom('.header__search').focus();return false;
		}
	}).bind(['r'], function () {
		if (visible.album()) {
			album.setTitle(album.getID());return false;
		} else if (visible.photo()) {
			photo.setTitle([photo.getID()]);return false;
		}
	}).bind(['d'], function () {
		if (visible.photo()) {
			photo.setDescription(photo.getID());return false;
		} else if (visible.album()) {
			album.setDescription(album.getID());return false;
		}
	}).bind(['t'], function () {
		if (visible.photo()) {
			photo.editTags([photo.getID()]);return false;
		}
	}).bind(['i'], function () {
		if (!visible.multiselect()) {
			sidebar.toggle();return false;
		}
	}).bind(['command+backspace', 'ctrl+backspace'], function () {
		if (visible.photo() && basicModal.visible() === false) {
			photo.delete([photo.getID()]);return false;
		} else if (visible.album() && basicModal.visible() === false) {
			album.delete([album.getID()]);return false;
		}
	}).bind(['command+a', 'ctrl+a'], function () {
		if (visible.album() && basicModal.visible() === false) {
			multiselect.selectAll();return false;
		} else if (visible.albums() && basicModal.visible() === false) {
			multiselect.selectAll();return false;
		}
	});

	Mousetrap.bindGlobal('enter', function () {
		if (basicModal.visible() === true) basicModal.action();
	});

	Mousetrap.bindGlobal(['esc', 'command+up'], function () {
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album()) lychee.goto();else if (visible.albums() && header.dom('.header__search').val().length !== 0) search.reset();
		return false;
	});

	if (eventName === 'touchend') {

		$(document)

		// Fullscreen on mobile
		.on('touchend', '#imageview #image', function (e) {
			if (swipe.obj == null || swipe.offset >= -5 && swipe.offset <= 5) {
				if (visible.header()) header.hide(e);else header.show();
			}
		})

		// Swipe on mobile
		.swipe().on('swipeStart', function () {
			if (visible.photo()) swipe.start($('#imageview #image'));
		}).swipe().on('swipeMove', function (e) {
			if (visible.photo()) swipe.move(e.swipe);
		}).swipe().on('swipeEnd', function (e) {
			if (visible.photo()) swipe.stop(e.swipe, photo.previous, photo.next);
		});
	}

	// Document
	$(document)

	// Navigation
	.on('click', '.album', function (e) {
		multiselect.albumClick(e, $(this));
	}).on('click', '.photo', function (e) {
		multiselect.photoClick(e, $(this));
	})

	// Context Menu
	.on('contextmenu', '.photo', function (e) {
		multiselect.photoContextMenu(e, $(this));
	}).on('contextmenu', '.album', function (e) {
		multiselect.albumContextMenu(e, $(this));
	})

	// // Navigation
	// .on('click', '.album', function() { lychee.goto($(this).attr('data-id')) })
	// .on('click', '.photo', function() { lychee.goto(album.getID() + '/' + $(this).attr('data-id')) })
	//
	// // Context Menu
	// .on('contextmenu', '.photo', function(e) { contextMenu.photo(photo.getID(), e) })
	// .on('contextmenu', '.album', function(e) { contextMenu.album(album.getID(), e) })

	// Upload
	.on('change', '#upload_files', function () {
		basicModal.close();upload.start.local(this.files);
	})

	// Drag and Drop upload
	.on('dragover', function () {
		return false;
	}, false).on('drop', function (e) {

		// Close open overlays or views which are correlating with the upload
		if (visible.photo()) lychee.goto(album.getID());
		if (visible.contextMenu()) contextMenu.close();

		// Detect if dropped item is a file or a link
		if (e.originalEvent.dataTransfer.files.length > 0) upload.start.local(e.originalEvent.dataTransfer.files);else if (e.originalEvent.dataTransfer.getData('Text').length > 3) upload.start.url(e.originalEvent.dataTransfer.getData('Text'));

		return false;
	});

	// Init
	lychee.init();
});

/**
 * @description This module is used for the context menu.
 */

leftMenu = {

	_dom: $('.leftMenu')

};

leftMenu.dom = function (selector) {

	if (selector == null || selector === '') return leftMenu._dom;
	return leftMenu._dom.find(selector);
};

leftMenu.build = function () {
	var html = '';
	html += '<a id="button_settings_close" class="closebtn" >&times;</a>';
	html += '<a class="linkMenu" id="button_settings_open">' + '<svg class="iconic"><use xlink:href="#cog"></use></svg>' + lychee.locale['SETTINGS'] + '</a>';
	if (lychee.api_V2) {
		html += '<a class="linkMenu" id="button_users">' + build.iconic('person') + 'Users</a>';
		html += '<a class="linkMenu" id="button_sharing">' + build.iconic('cloud') + 'Sharing</a>';
	}
	html += '<a class="linkMenu" id="button_logs">' + build.iconic('align-left') + lychee.locale['LOGS'] + '</a>';
	html += '<a class="linkMenu" id="button_diagnostics">' + build.iconic('wrench') + lychee.locale['DIAGNOSTICS'] + '</a>';
	html += '<a class="linkMenu" id="button_about">' + build.iconic('info') + lychee.locale['ABOUT_LYCHEE'] + '</a>';
	html += '<a class="linkMenu" id="button_signout">' + build.iconic('account-logout') + lychee.locale['SIGN_OUT'] + '</a>';
	leftMenu._dom.html(html);
};

/* Set the width of the side navigation to 250px and the left margin of the page content to 250px */
leftMenu.open = function () {
	leftMenu._dom.addClass('leftMenu__visible');
	$('.content').addClass('leftMenu__open');
	header.dom('.header__title').addClass('leftMenu__open');
	loadingBar.dom().addClass('leftMenu__open');
};

/* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
leftMenu.close = function () {
	leftMenu._dom.removeClass('leftMenu__visible');
	$('.content').removeClass('leftMenu__open');
	header.dom('.header__title').removeClass('leftMenu__open');
	loadingBar.dom().removeClass('leftMenu__open');

	multiselect.bind();
	lychee.load();
};

leftMenu.bind = function () {

	// Event Name
	var eventName = lychee.getEventName();

	leftMenu.dom('#button_settings_close').on(eventName, leftMenu.close);
	leftMenu.dom('#button_settings_open').on(eventName, settings.open);
	leftMenu.dom('#button_signout').on(eventName, lychee.logout);
	leftMenu.dom('#button_logs').on(eventName, leftMenu.Logs);
	leftMenu.dom('#button_diagnostics').on(eventName, leftMenu.Diagnostics);
	leftMenu.dom('#button_about').on(eventName, function () {
		return window.open(lychee.website);
	});

	if (lychee.api_V2) {
		leftMenu.dom('#button_users').on(eventName, leftMenu.Users);
		leftMenu.dom('#button_sharing').on(eventName, leftMenu.Sharing);
	}

	return true;
};

leftMenu.Logs = function () {
	if (lychee.api_V2) {
		view.logs_diagnostics.init('Logs');
	} else {
		window.open(lychee.logs());
	}
};

leftMenu.Diagnostics = function () {
	if (lychee.api_V2) {
		view.logs_diagnostics.init('Diagnostics');
	} else {
		window.open(lychee.diagnostics());
	}
};

leftMenu.Users = function () {
	users.list();
};

leftMenu.Sharing = function () {
	sharing.list();
};

/**
 * @description This module is used to show and hide the loading bar.
 */

loadingBar = {

	status: null,
	_dom: $('#loading')

};

loadingBar.dom = function (selector) {

	if (selector == null || selector === '') return loadingBar._dom;
	return loadingBar._dom.find(selector);
};

loadingBar.show = function (status, errorText) {

	if (status === 'error') {

		// Set status
		loadingBar.status = 'error';

		// Parse text
		if (errorText) errorText = errorText.replace('<br>', '');
		if (!errorText) errorText = lychee.locale['ERROR_TEXT'];

		// Move header down
		if (visible.header()) header.dom().addClass('header--error');

		// Modify loading
		loadingBar.dom().removeClass('loading uploading error success').html("<h1>" + lychee.locale['ERROR'] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 3000);

		return true;
	}

	if (status === 'success') {
		// Set status
		loadingBar.status = 'success';

		// Parse text
		if (errorText) errorText = errorText.replace('<br>', '');
		if (!errorText) errorText = lychee.locale['ERROR_TEXT'];

		// Move header down
		if (visible.header()) header.dom().addClass('header--error');

		// Modify loading
		loadingBar.dom().removeClass('loading uploading error success').html("<h1>" + lychee.locale['SUCCESS'] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 2000);

		return true;
	}

	if (loadingBar.status === null) {

		// Set status
		loadingBar.status = lychee.locale['LOADING'];

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {

			// Move header down
			if (visible.header()) header.dom().addClass('header--loading');

			// Modify loading
			loadingBar.dom().removeClass('loading uploading error').html('').addClass('loading').show();
		}, 1000);

		return true;
	}
};

loadingBar.hide = function (force) {

	if (loadingBar.status !== 'error' && loadingBar.status !== 'success' && loadingBar.status != null || force) {

		// Remove status
		loadingBar.status = null;

		// Move header up
		header.dom().removeClass('header--error header--loading');

		// Set timeout
		clearTimeout(loadingBar._timeout);
		setTimeout(function () {
			return loadingBar.dom().hide();
		}, 300);
	}
};

/**
 * @description This module provides the basic functions of Lychee.
 */

lychee = {

	title: document.title,
	version: '3.2.1',
	versionCode: '030201',

	updatePath: '//LycheeOrg.github.io/update.json',
	updateURL: 'https://github.com/LycheeOrg/Lychee',
	website: 'https://LycheeOrg.github.io',

	publicMode: false,
	viewMode: false,
	api_V2: false, // enable api_V2
	sub_albums: false, // enable sub_albums features
	admin: false, // enable admin mode (multi-user)
	upload: false, // enable possibility to upload (multi-user)
	lock: false, // locked user (multi-user)

	checkForUpdates: '1',
	sortingPhotos: '',
	sortingAlbums: '',
	location: '',

	lang: '',
	lang_available: {},

	dropbox: false,
	dropboxKey: '',

	content: $('.content'),
	imageview: $('#imageview'),

	locale: {

		'USERNAME': 'username',
		'PASSWORD': 'password',
		'ENTER': 'Enter',
		'CANCEL': 'Cancel',
		'SIGN_IN': 'Sign In',
		'CLOSE': 'Close',

		'SETTINGS': 'Settings',
		'CHANGE_LOGIN': 'Change Login',
		'CHANGE_SORTING': 'Change Sorting',
		'SET_DROPBOX': 'Set Dropbox',
		'ABOUT_LYCHEE': 'About Lychee',
		'DIAGNOSTICS': 'Diagnostics',
		'LOGS': 'Show Logs',
		'SIGN_OUT': 'Sign Out',
		'UPDATE_AVAILABLE': 'Update available!',

		'SMART_ALBUMS': 'Smart albums',
		'SHARED_ALBUMS': 'Shared albums',
		'ALBUMS': 'Albums',
		'PHOTOS': 'Pictures',

		'RENAME': 'Rename',
		'RENAME_ALL': 'Rename All',
		'MERGE': 'Merge',
		'MERGE_ALL': 'Merge All',
		'MAKE_PUBLIC': 'Make Public',
		'SHARE_ALBUM': 'Share Album',
		'SHARE_PHOTO': 'Share Photo',
		'DOWNLOAD_ALBUM': 'Download Album',
		'ABOUT_ALBUM': 'About Album',
		'DELETE_ALBUM': 'Delete Album',

		'DELETE_ALBUM_QUESTION': 'Delete Album and Photos',
		'KEEP_ALBUM': 'Keep Album',
		'DELETE_ALBUM_CONFIRMATION_1': 'Are you sure you want to delete the album',
		'DELETE_ALBUM_CONFIRMATION_2': 'and all of the photos it contains? This action can\'t be undone!',

		'DELETE_ALBUMS_QUESTION': 'Delete Albums and Photos',
		'KEEP_ALBUMS': 'Keep Albums',
		'DELETE_ALBUMS_CONFIRMATION_1': 'Are you sure you want to delete all',
		'DELETE_ALBUMS_CONFIRMATION_2': 'selected albums and all of the photos they contain? This action can\'t be undone!',

		'DELETE_UNSORTED_CONFIRM': 'Are you sure you want to delete all photos from \'Unsorted\'?<br>This action can\'t be undone!',
		'CLEAR_UNSORTED': 'Clear Unsorted',
		'KEEP_UNSORTED': 'Keep Unsorted',

		'EDIT_SHARING': 'Edit Sharing',
		'MAKE_PRIVATE': 'Make Private',

		'CLOSE_ALBUM': 'Close Album',
		'CLOSE_PHOTO': 'Close Photo',

		'ADD': 'Add',
		'MOVE': 'Move',
		'MOVE_ALL': 'Move All',
		'DUPLICATE': 'Duplicate',
		'DUPLICATE_ALL': 'Duplicate All',
		'DELETE': 'Delete',
		'DELETE_ALL': 'Delete All',
		'DOWNLOAD': 'Download',
		'UPLOAD_PHOTO': 'Upload Photo',
		'IMPORT_LINK': 'Import from Link',
		'IMPORT_DROPBOX': 'Import from Dropbox',
		'IMPORT_SERVER': 'Import from Server',
		'NEW_ALBUM': 'New Album',

		'TITLE_NEW_ALBUM': 'Enter a title for the new album:',
		'UNTITLED': 'Untilted',
		'UNSORTED': 'Unsorted',
		'STARED': 'Stared',
		'RECENT': 'Recent',
		'PUBLIC': 'Public',
		'NUM_PHOTOS': 'Photos',

		'CREATE_ALBUM': 'Create Album',

		'STAR_PHOTO': 'Star Photo',
		'STAR': 'Star',
		'STAR_ALL': 'Star All',
		'TAGS': 'Tags',
		'TAGS_ALL': 'Tags All',
		'UNSTAR_PHOTO': 'Unstar Photo',

		'FULL_PHOTO': 'Full Photo',
		'ABOUT_PHOTO': 'About Photo',
		'DIRECT_LINK': 'Direct Link',

		'ALBUM_ABOUT': 'About',
		'ALBUM_BASICS': 'Basics',
		'ALBUM_TITLE': 'Title',
		'ALBUM_NEW_TITLE': 'Enter a new title for this album:',
		'ALBUMS_NEW_TITLE_1': 'Enter a title for all',
		'ALBUMS_NEW_TITLE_2': 'selected albums:',
		'ALBUM_SET_TITLE': 'Set Title',
		'ALBUM_DESCRIPTION': 'Description',
		'ALBUM_NEW_DESCRIPTION': 'Enter a new description for this album:',
		'ALBUM_SET_DESCRIPTION': 'Set Description',
		'ALBUM_ALBUM': 'Album',
		'ALBUM_CREATED': 'Created',
		'ALBUM_IMAGES': 'Images',
		'ALBUM_SHARING': 'Share',
		'ALBUM_SHR_YES': 'YES',
		'ALBUM_SHR_NO': 'No',
		'ALBUM_PUBLIC': 'Public',
		'ALBUM_HIDDEN': 'Hidden',
		'ALBUM_HIDDEN_EXPL': 'Only people with the direct link can view this album.',
		'ALBUM_DOWNLOADABLE': 'Downloadable',
		'ALBUM_DOWNLOADABLE_EXPL': 'Visitors of your Lychee can download this album.',
		'ALBUM_PASSWORD': 'Password',
		'ALBUM_PASSWORD_PROT': 'Password protected',
		'ALBUM_PASSWORD_PROT_EXPL': 'Album only accessible with a valid password.',
		'ALBUM_PASSWORD_REQUIRED': 'This album is protected by a password. Enter the password below to view the photos of this album:',
		'ALBUM_MERGE_1': 'Are you sure you want to merge the album',
		'ALBUM_MERGE_2': 'into the album',
		'ALBUMS_MERGE': 'Are you sure you want to merge all selected albums into the album',
		'MERGE_ALBUM': 'Merge Albums',
		'DONT_MERGE': "Don't Merge",
		'ALBUM_MOVE_1': 'Are you sure you want to move the album',
		'ALBUM_MOVE_2': 'into the album',
		'ALBUMS_MOVE': 'Are you sure you want to move all selected albums into the album',
		'MOVE_ALBUMS': "Move Albums",
		'NOT_MOVE_ALBUMS': "Don't Move",
		'ROOT': "Root",

		'PHOTO_ABOUT': 'About',
		'PHOTO_BASICS': 'Basics',
		'PHOTO_TITLE': 'Title',
		'PHOTO_NEW_TITLE': 'Enter a new title for this photo:',
		'PHOTO_SET_TITLE': 'Set Title',
		'PHOTO_UPLOADED': 'Uploaded',
		'PHOTO_DESCRIPTION': 'Description',
		'PHOTO_NEW_DESCRIPTION': 'Enter a new description for this photo:',
		'PHOTO_SET_DESCRIPTION': 'Set Description',
		'PHOTO_IMAGE': 'Image',
		'PHOTO_SIZE': 'Size',
		'PHOTO_FORMAT': 'Format',
		'PHOTO_RESOLUTION': 'Resolution',
		'PHOTO_TAGS': 'Tags',
		'PHOTO_NOTAGS': 'No Tags',
		'PHOTO_NEW_TAGS': 'Enter your tags for this photo. You can add multiple tags by separating them with a comma:',
		'PHOTO_NEW_TAGS_1': 'Enter your tags for all',
		'PHOTO_NEW_TAGS_2': 'selected photos. Existing tags will be overwritten. You can add multiple tags by separating them with a comma:',
		'PHOTO_SET_TAGS': 'Set Tags',
		'PHOTO_CAMERA': 'Camera',
		'PHOTO_CAPTURED': 'Captured',
		'PHOTO_MAKE': 'Make',
		'PHOTO_TYPE': 'Type/Model',
		'PHOTO_SHUTTER': 'Shutter Speed',
		'PHOTO_APERTURE': 'Aperture',
		'PHOTO_FOCAL': 'Focal Length',
		'PHOTO_ISO': 'ISO',
		'PHOTO_SHARING': 'Sharing',
		'PHOTO_SHR_PLUBLIC': 'Public',
		'PHOTO_SHR_ALB': 'Yes (Album)',
		'PHOTO_SHR_PHT': 'Yes (Photo)',
		'PHOTO_SHR_NO': 'No',
		'PHOTO_DELETE': 'Delete Photo',
		'PHOTO_KEEP': 'Keep Photo',
		'PHOTO_DELETE_1': 'Are you sure you want to delete the photo',
		'PHOTO_DELETE_2': '? This action can\'t be undone!',
		'PHOTO_DELETE_ALL_1': 'Are you sure you want to delete all',
		'PHOTO_DELETE_ALL_2': 'selected photo? This action can\'t be undone!',
		'PHOTOS_NEW_TITLE_1': 'Enter a title for all',
		'PHOTOS_NEW_TITLE_2': 'selected photos:',
		'PHOTO_MAKE_PRIVATE_ALBUM': 'This photo is located in a public album. To make this photo private or public, edit the visibility of the associated album.',
		'PHOTO_SHOW_ALBUM': 'Show Album',

		'LOADING': 'Loading',
		'ERROR': 'Error',
		'ERROR_TEXT': 'Whoops, it looks like something went wrong. Please reload the site and try again!',
		'ERROR_DB_1': 'Unable to connect to host database because access was denied. Double-check your host, username and password and ensure that access from your current location is permitted.',
		'ERROR_DB_2': 'Unable to create the database. Double-check your host, username and password and ensure that the specified user has the rights to modify and add content to the database.',
		'ERROR_CONFIG_FILE': "Unable to save this configuration. Permission denied in <b>'data/'</b>. Please set the read, write and execute rights for others in <b>'data/'</b> and <b>'uploads/'</b>. Take a look at the readme for more information.",
		'ERROR_UNKNOWN': 'Something unexpected happened. Please try again and check your installation and server. Take a look at the readme for more information.',
		'ERROR_LOGIN': 'Unable to save login. Please try again with another username and password!',
		'SUCCESS': 'OK',
		'RETRY': 'Retry',

		'SETTINGS_SUCCESS_LOGIN': 'Login Info updated.',
		'SETTINGS_SUCCESS_SORT': 'Sorting order updated.',
		'SETTINGS_SUCCESS_DROPBOX': 'Dropbox Key updated.',
		'SETTINGS_SUCCESS_LANG': 'Language updated',

		'DB_INFO_TITLE': 'Enter your database connection details below:',
		'DB_INFO_HOST': 'Database Host (optional)',
		'DB_INFO_USER': 'Database Username',
		'DB_INFO_PASSWORD': 'Database Password',
		'DB_INFO_TEXT': 'Lychee will create its own database. If required, you can enter the name of an existing database instead:',
		'DB_NAME': 'Database Name (optional)',
		'DB_PREFIX': 'Table prefix (optional)',
		'DB_CONNECT': 'Connect',

		'LOGIN_TITLE': 'Enter a username and password for your installation:',
		'LOGIN_USERNAME': 'New Username',
		'LOGIN_PASSWORD': 'New Password',
		'LOGIN_CREATE': 'Create Login',

		'PASSWORD_TITLE': 'Enter your current username and password:',
		'USERNAME_CURRENT': 'Current Username',
		'PASSWORD_CURRENT': 'Current Password',
		'PASSWORD_TEXT': 'Your username and password will be changed to the following:',
		'PASSWORD_CHANGE': 'Change Login',

		'EDIT_SHARING_TITLE': 'Edit Sharing',
		'EDIT_SHARING_TEXT': 'The sharing-properties of this album will be changed to the following:',
		'SHARE_ALBUM_TEXT': 'This album will be shared with the following properties:',

		'SORT_ALBUM_BY_1': 'Sort albums by',
		'SORT_ALBUM_BY_2': 'in an',
		'SORT_ALBUM_BY_3': 'order.',

		'SORT_ALBUM_SELECT_1': 'Creation Time',
		'SORT_ALBUM_SELECT_2': 'Title',
		'SORT_ALBUM_SELECT_3': 'Description',
		'SORT_ALBUM_SELECT_4': 'Public',
		'SORT_ALBUM_SELECT_5': 'Latest Take Date',
		'SORT_ALBUM_SELECT_6': 'Oldest Take Date',

		'SORT_PHOTO_BY_1': 'Sort photos by',
		'SORT_PHOTO_BY_2': 'in an',
		'SORT_PHOTO_BY_3': 'order.',

		'SORT_PHOTO_SELECT_1': 'Upload Time',
		'SORT_PHOTO_SELECT_2': 'Take Date',
		'SORT_PHOTO_SELECT_3': 'Title',
		'SORT_PHOTO_SELECT_4': 'Description',
		'SORT_PHOTO_SELECT_5': 'Public',
		'SORT_PHOTO_SELECT_6': 'Star',
		'SORT_PHOTO_SELECT_7': 'Photo Format',

		'SORT_ASCENDING': 'Ascending',
		'SORT_DESCENDING': 'Descending',
		'SORT_CHANGE': 'Change Sorting',

		'DROPBOX_TITLE': 'Set Dropbox Key',
		'DROPBOX_TEXT': "In order to import photos from your Dropbox, you need a valid drop-ins app key from <a href='https://www.dropbox.com/developers/apps/create'>their website</a>. Generate yourself a personal key and enter it below:",

		'LANG_TEXT': 'Change Lychee language for:',
		'LANG_TITLE': 'Change Language',

		'VIEW_NO_RESULT': 'No results',
		'VIEW_NO_PUBLIC_ALBUMS': 'No public albums',
		'VIEW_NO_CONFIGURATION': 'No configuration',
		'VIEW_PHOTO_NOT_FOUND': 'Photo not found',

		'NO_TAGS': 'No Tags',

		'UPLOAD_MANAGE_NEW_PHOTOS': 'You can now manage your new photo(s).',
		'UPLOAD_COMPLETE': 'Upload complete',
		'UPLOAD_COMPLETE_FAILED': 'Failed to upload one or more photos.',
		'UPLOAD_IMPORTING': 'Importing',
		'UPLOAD_IMPORTING_URL': 'Importing URL',
		'UPLOAD_UPLOADING': 'Uploading',
		'UPLOAD_FINISHED': 'Finished',
		'UPLOAD_PROCESSING': 'Processing',
		'UPLOAD_FAILED': 'Failed',
		'UPLOAD_FAILED_ERROR': 'Upload failed. Server returned an error!',
		'UPLOAD_FAILED_WARNING': 'Upload failed. Server returned a warning!',
		'UPLOAD_SKIPPED': 'Skipped',
		'UPLOAD_ERROR_CONSOLE': 'Please take a look at the console of your browser for further details.',
		'UPLOAD_UNKNOWN': 'Server returned an unknown response. Please take a look at the console of your browser for further details.',
		'UPLOAD_ERROR_UNKNOWN': 'Upload failed. Server returned an unkown error!',
		'UPLOAD_IN_PROGRESS': 'Lychee is currently uploading!',
		'UPLOAD_IMPORT_WARN_ERR': 'The import has been finished, but returned warnings or errors. Please take a look at the log (Settings -> Show Log) for further details.',
		'UPLOAD_IMPORT_COMPLETE': 'Import complete',
		'UPLOAD_IMPORT_INSTR': 'Please enter the direct link to a photo to import it:',
		'UPLOAD_IMPORT': 'Import',
		'UPLOAD_IMPORT_SERVER': 'Importing from server',
		'UPLOAD_IMPORT_SERVER_FOLD': 'Folder empty or no readable files to process. Please take a look at the log (Settings -> Show Log) for further details.',
		'UPLOAD_IMPORT_SERVER_INSTR': 'This action will import all photos, folders and sub-folders which are located in the following directory. The <b>original files will be deleted</b> after the import when possible.',
		'UPLOAD_ABSOLUTE_PATH': 'Absolute path to directory',
		'UPLOAD_IMPORT_SERVER_EMPT': 'Could not start import because the folder was empty!'
	}

};

lychee.diagnostics = function () {
	if (lychee.api_V2) {
		return '/Diagnostics';
	} else {
		return 'plugins/Diagnostics/';
	}
};

lychee.logs = function () {
	if (lychee.api_V2) {
		return '/Logs';
	} else {
		return 'plugins/Log/';
	}
};

lychee.init = function () {

	api.post('Session::init', {}, function (data) {

		// Check status
		// 0 = No configuration
		// 1 = Logged out
		// 2 = Logged in

		lychee.api_V2 = data.api_V2 || false;
		lychee.sub_albums = data.sub_albums || false;
		// we copy the locale that exists only.
		// This ensure forward and backward compatibility.
		// e.g. if the front localization is unfished in a language
		// or if we need to change some locale string
		for (var key in data.locale) {
			lychee.locale[key] = data.locale[key];
		}

		if (data.status === 2) {

			// Logged in

			lychee.sortingPhotos = data.config.sortingPhotos || '';
			lychee.sortingAlbums = data.config.sortingAlbums || '';
			lychee.dropboxKey = data.config.dropboxKey || '';
			lychee.location = data.config.location || '';
			lychee.checkForUpdates = data.config.checkForUpdates || '1';
			lychee.lang = data.config.lang || '';
			lychee.lang_available = data.config.lang_available || {};

			lychee.upload = !lychee.api_V2;
			lychee.admin = !lychee.api_V2;

			// leftMenu
			leftMenu.build();
			leftMenu.bind();

			if (lychee.api_V2) {
				lychee.upload = data.admin || data.upload;
				lychee.admin = data.admin;
				lychee.lock = data.lock;
				lychee.setMode('logged_in');
			}

			// Show dialog when there is no username and password
			if (data.config.login === false) settings.createLogin();
		} else if (data.status === 1) {

			// Logged out

			lychee.checkForUpdates = data.config.checkForUpdates || '1';

			lychee.setMode('public');
		} else if (data.status === 0) {

			// No configuration

			lychee.setMode('public');

			header.dom().hide();
			lychee.content.hide();
			$('body').append(build.no_content('cog'));
			settings.createConfig();

			return true;
		}

		$(window).bind('popstate', lychee.load);
		lychee.load();
	});
};

lychee.login = function (data) {

	var user = data.username;
	var password = data.password;

	var params = {
		user: user,
		password: password
	};

	api.post('Session::login', params, function (data) {

		if (data === true) {

			window.location.reload();
		} else {

			// Show error and reactive button
			basicModal.error('password');
		}
	});
};

lychee.loginDialog = function () {

	var msg = lychee.html(_templateObject29) + lychee.locale['USERNAME'] + "' type='text' placeholder='username' autocapitalize='off' autocorrect='off'>\n\t\t\t\t  <input class='text' name='password' autocomplete='current-password' type='password' placeholder='" + lychee.locale['PASSWORD'] + ("'>\n\t\t\t  </p>\n\t\t\t  <p class='version'>Lychee " + lychee.version + "<span> &#8211; <a target='_blank' href='" + lychee.updateURL + "'>") + lychee.locale['UPDATE_AVAILABLE'] + "</a><span></p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['SIGN_IN'],
				fn: lychee.login
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});

	if (lychee.checkForUpdates === '1') lychee.getUpdate();
};

lychee.logout = function () {

	api.post('Session::logout', {}, function () {
		window.location.reload();
	});
};

lychee.goto = function () {
	var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';


	url = '#' + url;

	history.pushState(null, null, url);
	lychee.load();
};

lychee.load = function () {

	var albumID = '';
	var photoID = '';
	var hash = document.location.hash.replace('#', '').split('/');

	$('.no_content').remove();
	contextMenu.close();
	multiselect.close();

	if (hash[0] != null) albumID = hash[0];
	if (hash[1] != null) photoID = hash[1];

	if (albumID && photoID) {

		// Trash data
		photo.json = null;

		// Show Photo
		if (lychee.content.html() === '' || header.dom('.header__search').length && header.dom('.header__search').val().length !== 0) {
			lychee.content.hide();
			album.load(albumID, true);
		}
		photo.load(photoID, albumID);
	} else if (albumID) {

		// Trash data
		photo.json = null;

		// Show Album
		if (visible.photo()) view.photo.hide();
		if (visible.sidebar() && (albumID === '0' || albumID === 'f' || albumID === 's' || albumID === 'r')) sidebar.toggle();
		if (album.json && albumID === album.json.id) view.album.title();else album.load(albumID);
	} else {

		// Trash albums.json when filled with search results
		if (search.hash != null) {
			albums.json = null;
			search.hash = null;
		}

		// Trash data
		album.json = null;
		photo.json = null;

		// Hide sidebar
		if (visible.sidebar()) sidebar.toggle();

		// Show Albums
		if (visible.photo()) view.photo.hide();
		lychee.content.show();
		albums.load();
	}
};

lychee.getUpdate = function () {

	var success = function success(data) {
		if (data.lychee.version > parseInt(lychee.versionCode)) $('.version span').show();
	};

	$.ajax({
		url: lychee.updatePath,
		success: success
	});
};

lychee.setTitle = function (title, editable) {

	document.title = lychee.title + ' - ' + title;

	header.setEditable(editable);
	header.setTitle(title);
};

lychee.setMode = function (mode) {

	if (lychee.lock) {
		$('#button_settings_open').remove();
	}
	if (!lychee.upload) {
		$('#button_trash_album, .button_add').remove();
		$('#button_trash, #button_move, #button_star, #button_sharing').remove();

		$('#button_share, #button_share_album').removeClass('button--eye').addClass('button--share').find('use').attr('xlink:href', '#share');

		$(document).off('click', '.header__title--editable').off('touchend', '.header__title--editable').off('contextmenu', '.photo').off('contextmenu', '.album').off('drop');

		Mousetrap.unbind(['u']).unbind(['s']).unbind(['f']).unbind(['r']).unbind(['d']).unbind(['t']).unbind(['command+backspace', 'ctrl+backspace']).unbind(['command+a', 'ctrl+a']);
	}
	if (!lychee.admin) {
		$('#button_users, #button_logs, #button_diagnostics').remove();
	}

	if (mode === 'logged_in') return;

	$('#button_settings, .header__divider, .leftMenu').remove();

	// $('#button_share, #button_share_album')
	// 	.removeClass('button--eye')
	// 	.addClass('button--share')
	// 	.find('use')
	// 	.attr('xlink:href', '#share');
	//
	// $(document)
	// 	.off('click',       '.header__title--editable')
	// 	.off('touchend',    '.header__title--editable')
	// 	.off('contextmenu', '.photo')
	// 	.off('contextmenu', '.album')
	// 	.off('drop');
	//
	// Mousetrap
	// 	.unbind([ 'u' ])
	// 	.unbind([ 's' ])
	// 	.unbind([ 'f' ])
	// 	.unbind([ 'r' ])
	// 	.unbind([ 'd' ])
	// 	.unbind([ 't' ])
	// 	.unbind([ 'command+backspace', 'ctrl+backspace' ])
	// 	.unbind([ 'command+a', 'ctrl+a' ]);

	if (mode === 'public') {

		lychee.publicMode = true;
	} else if (mode === 'view') {

		Mousetrap.unbind(['esc', 'command+up']);

		$('#button_back, a#next, a#previous').remove();
		$('.no_content').remove();

		lychee.publicMode = true;
		lychee.viewMode = true;
	}
};

lychee.animate = function (obj, animation) {

	var animations = [['fadeIn', 'fadeOut'], ['contentZoomIn', 'contentZoomOut']];

	if (!obj.jQuery) obj = $(obj);

	for (var i = 0; i < animations.length; i++) {
		for (var x = 0; x < animations[i].length; x++) {
			if (animations[i][x] == animation) {
				obj.removeClass(animations[i][0] + ' ' + animations[i][1]).addClass(animation);
				return true;
			}
		}
	}

	return false;
};

lychee.retinize = function () {
	var path = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';


	var extention = path.split('.').pop();
	var isPhoto = extention !== 'svg';

	if (isPhoto === true) {

		path = path.replace(/\.[^/.]+$/, '');
		path = path + '@2x' + '.' + extention;
	}

	return {
		path: path,
		isPhoto: isPhoto
	};
};

lychee.loadDropbox = function (callback) {

	if (lychee.dropbox === false && lychee.dropboxKey != null && lychee.dropboxKey !== '') {

		loadingBar.show();

		var g = document.createElement('script');
		var s = document.getElementsByTagName('script')[0];

		g.src = 'https://www.dropbox.com/static/api/1/dropins.js';
		g.id = 'dropboxjs';
		g.type = 'text/javascript';
		g.async = 'true';
		g.setAttribute('data-app-key', lychee.dropboxKey);
		g.onload = g.onreadystatechange = function () {
			var rs = this.readyState;
			if (rs && rs !== 'complete' && rs !== 'loaded') return;
			lychee.dropbox = true;
			loadingBar.hide();
			callback();
		};
		s.parentNode.insertBefore(g, s);
	} else if (lychee.dropbox === true && lychee.dropboxKey != null && lychee.dropboxKey !== '') {

		callback();
	} else {

		settings.setDropboxKey(callback);
	}
};

lychee.getEventName = function () {

	var touchendSupport = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || navigator.vendor || window.opera) && 'ontouchend' in document.documentElement;
	return touchendSupport === true ? 'touchend' : 'click';
};

lychee.escapeHTML = function () {
	var html = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';


	// Ensure that html is a string
	html += '';

	// Escape all critical characters
	html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/`/g, '&#96;');

	return html;
};

lychee.html = function (literalSections) {

	// Use raw literal sections: we don’t want
	// backslashes (\n etc.) to be interpreted
	var raw = literalSections.raw;
	var result = '';

	for (var _len = arguments.length, substs = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
		substs[_key - 1] = arguments[_key];
	}

	substs.forEach(function (subst, i) {

		// Retrieve the literal section preceding
		// the current substitution
		var lit = raw[i];

		// If the substitution is preceded by a dollar sign,
		// we escape special characters in it
		if (lit.slice(-1) === '$') {
			subst = lychee.escapeHTML(subst);
			lit = lit.slice(0, -1);
		}

		result += lit;
		result += subst;
	});

	// Take care of last literal section
	// (Never fails, because an empty template string
	// produces one literal section, an empty string)
	result += raw[raw.length - 1];

	return result;
};

lychee.error = function (errorThrown) {
	var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';


	console.error({
		description: errorThrown,
		params: params,
		response: data
	});

	loadingBar.show('error', errorThrown);
};

/**
 * @description Select multiple albums or photos.
 */

var isSelectKeyPressed = function isSelectKeyPressed(e) {

	return e.metaKey || e.ctrlKey;
};

multiselect = {

	ids: [],
	albumsSelected: 0,
	photosSelected: 0

};

multiselect.position = {

	top: null,
	right: null,
	bottom: null,
	left: null

};

multiselect.bind = function () {

	$('.content').on('mousedown', function (e) {
		if (e.which === 1) multiselect.show(e);
	});

	return true;
};

multiselect.isSelected = function (id) {

	var pos = $.inArray(id, multiselect.ids);

	return {
		selected: pos !== -1,
		position: pos
	};
};

multiselect.toggleItem = function (object, id) {

	if (album.isSmartID(id)) return;

	var selected = multiselect.isSelected(id).selected;

	if (selected === false) multiselect.addItem(object, id);else multiselect.removeItem(object, id);
};

multiselect.addItem = function (object, id) {

	if (album.isSmartID(id)) return;
	if (multiselect.isSelected(id).selected === true) return;

	var isAlbum = object.hasClass('album');

	if (isAlbum && multiselect.photosSelected > 0 || !isAlbum && multiselect.albumsSelected > 0) {
		lychee.error('Please select either albums or photos!');
		return;
	}

	multiselect.ids.push(id);
	multiselect.select(object);

	if (isAlbum) {
		multiselect.albumsSelected++;
	} else {
		multiselect.photosSelected++;
	}
};

multiselect.removeItem = function (object, id) {
	var _multiselect$isSelect = multiselect.isSelected(id),
	    selected = _multiselect$isSelect.selected,
	    pos = _multiselect$isSelect.pos;

	if (selected === false) return;

	multiselect.ids.splice(pos, 1);
	multiselect.deselect(object);

	var isAlbum = object.hasClass('album');

	if (isAlbum) {
		multiselect.albumsSelected--;
	} else {
		multiselect.photosSelected--;
	}
};

multiselect.albumClick = function (e, albumObj) {

	var id = albumObj.attr('data-id');

	if (isSelectKeyPressed(e) && lychee.upload) {
		if (albumObj.hasClass('disabled') && !lychee.admin) return;
		multiselect.toggleItem(albumObj, id);
	} else {
		lychee.goto(id);
	}
};

multiselect.photoClick = function (e, photoObj) {

	var id = photoObj.attr('data-id');

	if (isSelectKeyPressed(e) && lychee.upload) {
		if (photoObj.hasClass('disabled') && !lychee.admin) return;
		multiselect.toggleItem(photoObj, id);
	} else {
		lychee.goto(album.getID() + '/' + id);
	}
};

multiselect.albumContextMenu = function (e, albumObj) {

	var id = albumObj.attr('data-id');
	var selected = multiselect.isSelected(id).selected;

	if (albumObj.hasClass('disabled') && !lychee.admin) return;

	if (selected !== false) {
		contextMenu.albumMulti(multiselect.ids, e);
		multiselect.clearSelection(false);
	} else {
		multiselect.clearSelection();
		contextMenu.album(id, e);
	}
};

multiselect.photoContextMenu = function (e, photoObj) {

	var id = photoObj.attr('data-id');
	var selected = multiselect.isSelected(id).selected;

	if (photoObj.hasClass('disabled') && !lychee.admin) return;

	if (selected !== false) {
		contextMenu.photoMulti(multiselect.ids, e);
		multiselect.clearSelection(false);
	} else if (visible.album()) {
		multiselect.clearSelection();
		contextMenu.photo(id, e);
	} else if (visible.photo()) {
		// should not happen... but you never know...
		multiselect.clearSelection();
		contextMenu.photo(photo.getID(), e);
	} else {
		lychee.error('Could not find what you wnat.');
	}
};

multiselect.clearSelection = function () {

	multiselect.deselect('.photo.active, .album.active');
	multiselect.ids = [];
	multiselect.albumsSelected = 0;
	multiselect.photosSelected = 0;
};

multiselect.show = function (e) {

	if (lychee.publicMode) return false;
	if (!visible.albums() && !visible.album()) return false;
	if ($('.album:hover, .photo:hover').length !== 0) return false;
	if (visible.search()) return false;
	if (visible.multiselect()) $('#multiselect').remove();

	sidebar.setSelectable(false);

	multiselect.position.top = e.pageY;
	multiselect.position.right = -1 * (e.pageX - $(document).width());
	multiselect.position.bottom = -1 * (multiselect.position.top - $(window).height());
	multiselect.position.left = e.pageX;

	$('body').append(build.multiselect(multiselect.position.top, multiselect.position.left));

	$(document).on('mousemove', multiselect.resize).on('mouseup', function (e) {
		if (e.which === 1) multiselect.getSelection(e);
	});
};

multiselect.resize = function (e) {

	if (multiselect.position.top === null || multiselect.position.right === null || multiselect.position.bottom === null || multiselect.position.left === null) return false;

	var newSize = {};
	var documentSize = {};

	// Get the position of the mouse
	var mousePos = {
		x: e.pageX,
		y: e.pageY
	};

	// Default CSS
	var newCSS = {
		top: null,
		bottom: null,
		height: null,
		left: null,
		right: null,
		width: null
	};

	if (mousePos.y >= multiselect.position.top) {

		documentSize.height = $(document).height();

		// Do not leave the screen
		newSize.height = mousePos.y - multiselect.position.top;
		if (multiselect.position.top + newSize.height >= documentSize.height) {
			newSize.height -= multiselect.position.top + newSize.height - documentSize.height + 2;
		}

		newCSS.top = multiselect.position.top;
		newCSS.bottom = 'inherit';
		newCSS.height = newSize.height;
	} else {

		newCSS.top = 'inherit';
		newCSS.bottom = multiselect.position.bottom;
		newCSS.height = multiselect.position.top - e.pageY;
	}

	if (mousePos.x >= multiselect.position.left) {

		documentSize.width = $(document).width();

		// Do not leave the screen
		newSize.width = mousePos.x - multiselect.position.left;
		if (multiselect.position.left + newSize.width >= documentSize.width) {
			newSize.width -= multiselect.position.left + newSize.width - documentSize.width + 2;
		}

		newCSS.right = 'inherit';
		newCSS.left = multiselect.position.left;
		newCSS.width = newSize.width;
	} else {

		newCSS.right = multiselect.position.right;
		newCSS.left = 'inherit';
		newCSS.width = multiselect.position.left - e.pageX;
	}

	// Updated all CSS properties at once
	$('#multiselect').css(newCSS);
};

multiselect.stopResize = function () {

	if (multiselect.position.top !== null) $(document).off('mousemove mouseup');
};

multiselect.getSize = function () {

	if (!visible.multiselect()) return false;

	var $elem = $('#multiselect');
	var offset = $elem.offset();

	return {
		top: offset.top,
		left: offset.left,
		width: parseInt($elem.css('width').replace('px', '')),
		height: parseInt($elem.css('height').replace('px', ''))
	};
};

multiselect.getSelection = function (e) {

	var tolerance = 150;
	var size = multiselect.getSize();

	if (visible.contextMenu()) return false;
	if (!visible.multiselect()) return false;

	if (!e.shiftKey && (size.width === 0 || size.height === 0)) {
		multiselect.close();
		return false;
	}

	$('.photo, .album').each(function () {

		var offset = $(this).offset();

		if (offset.top >= size.top - tolerance && offset.left >= size.left - tolerance && offset.top + 206 <= size.top + size.height + tolerance && offset.left + 206 <= size.left + size.width + tolerance) {

			var id = $(this).attr('data-id');

			multiselect.addItem($(this), id);
		}
	});

	multiselect.hide();
};

multiselect.select = function (id) {

	var el = $(id);

	el.addClass('selected');
	el.addClass('active');
};

multiselect.deselect = function (id) {

	var el = $(id);

	el.removeClass('selected');
	el.removeClass('active');
};

multiselect.hide = function () {

	sidebar.setSelectable(true);

	multiselect.stopResize();

	multiselect.position.top = null;
	multiselect.position.right = null;
	multiselect.position.bottom = null;
	multiselect.position.left = null;

	lychee.animate('#multiselect', 'fadeOut');
	setTimeout(function () {
		return $('#multiselect').remove();
	}, 300);
};

multiselect.close = function () {

	sidebar.setSelectable(true);

	multiselect.stopResize();

	multiselect.position.top = null;
	multiselect.position.right = null;
	multiselect.position.bottom = null;
	multiselect.position.left = null;

	lychee.animate('#multiselect', 'fadeOut');
	setTimeout(function () {
		return $('#multiselect').remove();
	}, 300);
};

multiselect.selectAll = function () {

	if (lychee.publicMode) return false;
	if (visible.search()) return false;
	if (!visible.albums() && !visible.album) return false;
	if (visible.multiselect()) $('#multiselect').remove();

	sidebar.setSelectable(false);

	multiselect.position.top = 70;
	multiselect.position.right = 40;
	multiselect.position.bottom = 90;
	multiselect.position.left = 20;

	$('body').append(build.multiselect(multiselect.position.top, multiselect.position.left));

	var documentSize = {
		width: $(document).width(),
		height: $(document).height()
	};

	var newSize = {
		width: documentSize.width - multiselect.position.right + 2,
		height: documentSize.height - multiselect.position.bottom
	};

	var e = {
		pageX: documentSize.width - multiselect.position.right / 2,
		pageY: documentSize.height - multiselect.position.bottom
	};

	$('#multiselect').css(newSize);

	multiselect.getSelection(e);
};

/**
 * @description Controls the access to password-protected albums and photos.
 */

password = {

	value: ''

};

password.get = function (albumID, callback) {

	if (lychee.publicMode === false) callback();else if (album.json && album.json.password === '0') callback();else if (albums.json && albums.getByID(albumID).password === '0') callback();else if (!albums.json && !album.json) {

		// Continue without password

		album.json = { password: true };
		callback('');
	} else {

		// Request password

		password.getDialog(albumID, callback);
	}
};

password.getDialog = function (albumID, callback) {

	var action = function action(data) {

		var passwd = data.password;

		var params = {
			albumID: albumID,
			password: passwd
		};

		api.post('Album::getPublic', params, function (data) {

			if (data === true) {
				basicModal.close();
				password.value = passwd;
				callback();
			} else {
				basicModal.error('password');
			}
		});
	};

	var cancel = function cancel() {

		basicModal.close();
		if (!visible.albums()) lychee.goto();
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['ALBUM_PASSWORD_REQUIRED'] + "\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale['PASSWORD'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['ENTER'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: cancel
			}
		}
	});
};

/**
 * @description Takes care of every action a photo can handle and execute.
 */

photo = {

	json: null,
	cache: null

};

photo.getID = function () {

	var id = null;

	if (photo.json) id = photo.json.id;else id = $('.photo:hover, .photo.active').attr('data-id');

	if ($.isNumeric(id) === true) return id;else return false;
};

photo.load = function (photoID, albumID) {

	var checkContent = function checkContent() {
		if (album.json != null && album.json.photos) photo.load(photoID, albumID);else setTimeout(checkContent, 100);
	};

	var checkPasswd = function checkPasswd() {
		if (password.value !== '') photo.load(photoID, albumID);else setTimeout(checkPasswd, 200);
	};

	// we need to check the album.json.photos because otherwise the script is too fast and this raise an error.
	if (album.json == null || album.json.photos == null) {
		checkContent();
		return false;
	}

	var params = {
		photoID: photoID,
		albumID: albumID,
		password: password.value
	};

	api.post('Photo::get', params, function (data) {

		if (data === 'Warning: Photo private!') {
			lychee.content.show();
			lychee.goto();
			return false;
		}

		if (data === 'Warning: Wrong password!') {
			checkPasswd();
			return false;
		}

		photo.json = data;

		if (!visible.photo()) view.photo.show();
		view.photo.init();
		lychee.imageview.show();

		setTimeout(function () {
			lychee.content.show();
			photo.preloadNext(photoID);
		}, 300);
	});
};

// Preload the next photo for better response time
photo.preloadNext = function (photoID) {
	if (album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).nextPhoto !== '') {

		var nextPhoto = album.getByID(photoID).nextPhoto;
		var url = album.getByID(nextPhoto).url;
		var medium = album.getByID(nextPhoto).medium;
		var href = medium != null && medium !== '' ? medium : url;

		$('head [data-prefetch]').remove();
		$('head').append("<link data-prefetch rel=\"prefetch\" href=\"" + href + "\">");
	}
};

photo.parse = function () {

	if (!photo.json.title) photo.json.title = lychee.locale['UNTITLED'];
};

photo.previous = function (animate) {

	if (photo.getID() !== false && album.json && album.getByID(photo.getID()) && album.getByID(photo.getID()).previousPhoto !== '') {

		var delay = 0;

		if (animate === true) {

			delay = 200;

			$('#imageview #image').css({
				WebkitTransform: 'translateX(100%)',
				MozTransform: 'translateX(100%)',
				transform: 'translateX(100%)',
				opacity: 0
			});
		}

		setTimeout(function () {
			if (photo.getID() === false) return false;
			lychee.goto(album.getID() + '/' + album.getByID(photo.getID()).previousPhoto);
		}, delay);
	}
};

photo.next = function (animate) {

	if (photo.getID() !== false && album.json && album.getByID(photo.getID()) && album.getByID(photo.getID()).nextPhoto !== '') {

		var delay = 0;

		if (animate === true) {

			delay = 200;

			$('#imageview #image').css({
				WebkitTransform: 'translateX(-100%)',
				MozTransform: 'translateX(-100%)',
				transform: 'translateX(-100%)',
				opacity: 0
			});
		}

		setTimeout(function () {
			if (photo.getID() === false) return false;
			lychee.goto(album.getID() + '/' + album.getByID(photo.getID()).nextPhoto);
		}, delay);
	}
};

photo.duplicate = function (photoIDs) {

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	albums.refresh();

	var params = {
		photoIDs: photoIDs.join()
	};

	api.post('Photo::duplicate', params, function (data) {

		if (data !== true) lychee.error(null, params, data);else album.load(album.getID());
	});
};

photo.delete = function (photoIDs) {

	var action = {};
	var cancel = {};
	var msg = '';
	var photoTitle = '';

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	if (photoIDs.length === 1) {

		// Get title if only one photo is selected
		if (visible.photo()) photoTitle = photo.json.title;else photoTitle = album.getByID(photoIDs).title;

		// Fallback for photos without a title
		if (photoTitle === '') photoTitle = lychee.locale['UNTITLED'];
	}

	action.fn = function () {

		var nextPhoto = null;
		var previousPhoto = null;

		basicModal.close();

		photoIDs.forEach(function (id, index, array) {

			// Change reference for the next and previous photo
			if (album.getByID(id).nextPhoto !== '' || album.getByID(id).previousPhoto !== '') {

				nextPhoto = album.getByID(id).nextPhoto;
				previousPhoto = album.getByID(id).previousPhoto;

				album.getByID(previousPhoto).nextPhoto = nextPhoto;
				album.getByID(nextPhoto).previousPhoto = previousPhoto;
			}

			album.deleteByID(id);
			// delete album.json.photos[id];
			view.album.content.delete(id);
		});

		albums.refresh();

		// Go to next photo if there is a next photo and
		// next photo is not the current one. Show album otherwise.
		if (visible.photo() && nextPhoto != null && nextPhoto !== photo.getID()) lychee.goto(album.getID() + '/' + nextPhoto);else if (!visible.albums()) lychee.goto(album.getID());

		var params = {
			photoIDs: photoIDs.join()
		};

		api.post('Photo::delete', params, function (data) {

			if (data !== true) lychee.error(null, params, data);
		});
	};

	if (photoIDs.length === 1) {

		action.title = lychee.locale['PHOTO_DELETE'];
		cancel.title = lychee.locale['PHOTO_KEEP'];

		msg = lychee.html(_templateObject) + lychee.locale['PHOTO_DELETE_1'] + (" '" + photoTitle + "'") + lychee.locale['PHOTO_DELETE_2'] + "</p>";
	} else {

		action.title = lychee.locale['PHOTO_DELETE'];
		cancel.title = lychee.locale['PHOTO_KEEP'];

		msg = lychee.html(_templateObject) + lychee.locale['PHOTO_DELETE_ALL_1'] + (" " + photoIDs.length + " ") + lychee.locale['PHOTO_DELETE_ALL_2'] + "</p>";
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: 'red'
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

photo.setTitle = function (photoIDs) {

	var oldTitle = '';
	var msg = '';

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	if (photoIDs.length === 1) {

		// Get old title if only one photo is selected
		if (photo.json) oldTitle = photo.json.title;else if (album.json) oldTitle = album.getByID(photoIDs).title;
	}

	var action = function action(data) {

		basicModal.close();

		var newTitle = data.title;

		if (visible.photo()) {
			photo.json.title = newTitle === '' ? 'Untitled' : newTitle;
			view.photo.title();
		}

		photoIDs.forEach(function (id, index, array) {
			album.getByID(id).title = newTitle;
			view.album.content.title(id);
		});

		var params = {
			photoIDs: photoIDs.join(),
			title: newTitle
		};

		api.post('Photo::setTitle', params, function (data) {

			if (data !== true) lychee.error(null, params, data);
		});
	};

	var input = lychee.html(_templateObject30, oldTitle);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject) + lychee.locale['PHOTO_NEW_TITLE'] + (" " + input + "</p>");else msg = lychee.html(_templateObject) + lychee.locale['PHOTOS_NEW_TITLE_1'] + (" " + photoIDs.length + " ") + lychee.locale['PHOTOS_NEW_TITLE_2'] + (" " + input + "</p>");

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['PHOTO_SET_TITLE'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

photo.setAlbum = function (photoIDs, albumID) {

	var nextPhoto = null;
	var previousPhoto = null;

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	photoIDs.forEach(function (id, index, array) {

		// Change reference for the next and previous photo
		if (album.getByID(id).nextPhoto !== '' || album.getByID(id).previousPhoto !== '') {

			nextPhoto = album.getByID(id).nextPhoto;
			previousPhoto = album.getByID(id).previousPhoto;

			album.getByID(previousPhoto).nextPhoto = nextPhoto;
			album.getByID(nextPhoto).previousPhoto = previousPhoto;
		}

		album.deleteByID(id);
		//		delete album.json.photos[id];
		view.album.content.delete(id);
	});

	albums.refresh();

	// Go to next photo if there is a next photo and
	// next photo is not the current one. Show album otherwise.
	if (visible.photo() && nextPhoto != null && nextPhoto !== photo.getID()) lychee.goto(album.getID() + '/' + nextPhoto);else if (!visible.albums()) lychee.goto(album.getID());

	var params = {
		photoIDs: photoIDs.join(),
		albumID: albumID
	};

	api.post('Photo::setAlbum', params, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

photo.setStar = function (photoIDs) {

	if (!photoIDs) return false;

	if (visible.photo()) {
		photo.json.star = photo.json.star === '0' ? '1' : '0';
		view.photo.star();
	}

	photoIDs.forEach(function (id, index, array) {
		album.getByID(id).star = album.getByID(id).star === '0' ? '1' : '0';
		view.album.content.star(id);
	});

	albums.refresh();

	var params = {
		photoIDs: photoIDs.join()
	};

	api.post('Photo::setStar', params, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

photo.setPublic = function (photoID, e) {

	if (photo.json.public === '2') {

		var action = function action() {

			basicModal.close();
			lychee.goto(photo.json.original_album);
		};

		basicModal.show({
			body: '<p>' + lychee.locale['PHOTO_MAKE_PRIVATE_ALBUM'] + '</p>',
			buttons: {
				action: {
					title: lychee.locale['PHOTO_SHOW_ALBUM'],
					fn: action
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});

		return false;
	}

	if (visible.photo()) {

		photo.json.public = photo.json.public === '0' ? '1' : '0';
		view.photo.public();
		if (photo.json.public === '1') contextMenu.sharePhoto(photoID, e);
	}

	album.getByID(photoID).public = album.getByID(photoID).public === '0' ? '1' : '0';
	view.album.content.public(photoID);

	albums.refresh();

	api.post('Photo::setPublic', { photoID: photoID }, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

photo.setDescription = function (photoID) {

	var oldDescription = photo.json.description;

	var action = function action(data) {

		basicModal.close();

		var description = data.description;

		if (visible.photo()) {
			photo.json.description = description;
			view.photo.description();
		}

		var params = {
			photoID: photoID,
			description: description
		};

		api.post('Photo::setDescription', params, function (data) {

			if (data !== true) lychee.error(null, params, data);
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject) + lychee.locale['PHOTO_NEW_DESCRIPTION'] + " <input class='text' name='description' type='text' maxlength='800' placeholder='" + lychee.locale['PHOTO_DESCRIPTION'] + ("' value='" + oldDescription + "'></p>"),
		buttons: {
			action: {
				title: lychee.locale['PHOTO_SET_DESCRIPTION'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

photo.editTags = function (photoIDs) {

	var oldTags = '';
	var msg = '';

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	// Get tags
	if (visible.photo()) oldTags = photo.json.tags;else if (visible.album() && photoIDs.length === 1) oldTags = album.getByID(photoIDs).tags;else if (visible.search() && photoIDs.length === 1) oldTags = album.getByID(photoIDs).tags;else if (visible.album() && photoIDs.length > 1) {
		var same = true;
		photoIDs.forEach(function (id, index, array) {
			same = album.getByID(id).tags === album.getByID(photoIDs[0]).tags && same === true;
		});
		if (same === true) oldTags = album.getByID(photoIDs[0]).tags;
	}

	// Improve tags
	oldTags = oldTags.replace(/,/g, ', ');

	var action = function action(data) {

		basicModal.close();
		photo.setTags(photoIDs, data.tags);
	};

	var input = lychee.html(_templateObject31, oldTags);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject) + lychee.locale['PHOTO_NEW_TAGS'] + (" " + input + "</p>");else msg = lychee.html(_templateObject) + lychee.locale['PHOTO_NEW_TAGS_1'] + (" " + photoIDs.length + " ") + lychee.locale['PHOTO_NEW_TAGS_2'] + (" " + input + "</p>");

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['PHOTO_SET_TAGS'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

photo.setTags = function (photoIDs, tags) {

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	// Parse tags
	tags = tags.replace(/(\ ,\ )|(\ ,)|(,\ )|(,{1,}\ {0,})|(,$|^,)/g, ',');
	tags = tags.replace(/,$|^,|(\ ){0,}$/g, '');

	if (visible.photo()) {
		photo.json.tags = tags;
		view.photo.tags();
	}

	photoIDs.forEach(function (id, index, array) {
		album.getByID(id).tags = tags;
	});

	var params = {
		photoIDs: photoIDs.join(),
		tags: tags
	};

	api.post('Photo::setTags', params, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

photo.deleteTag = function (photoID, index) {

	var tags = void 0;

	// Remove
	tags = photo.json.tags.split(',');
	tags.splice(index, 1);

	// Save
	photo.json.tags = tags.toString();
	photo.setTags([photoID], photo.json.tags);
};

photo.share = function (photoID, service) {

	var url = photo.getViewLink(photoID);

	switch (service) {
		case 'twitter':
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case 'facebook':
			window.open("http://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(photo.json.title));
			break;
		case 'mail':
			location.href = "mailto:?subject=" + encodeURI(photo.json.title) + "&body=" + encodeURI(url);
			break;
		case 'dropbox':
			lychee.loadDropbox(function () {
				var filename = photo.json.title + '.' + photo.getDirectLink().split('.').pop();
				Dropbox.save(photo.getDirectLink(), filename);
			});
			break;
	}
};

photo.getArchive = function (photoID) {

	var link = void 0;
	var url = api.path + "?function=Photo::getArchive&photoID=" + photoID;

	if (location.href.indexOf('index.html') > 0) link = location.href.replace(location.hash, '').replace('index.html', url);else link = location.href.replace(location.hash, '') + url;

	if (lychee.publicMode === true) link += "&password=" + encodeURIComponent(password.value);

	location.href = link;
};

photo.getDirectLink = function () {

	var url = '';

	if (photo.json && photo.json.url && photo.json.url !== '') url = photo.json.url;

	return url;
};

photo.getViewLink = function (photoID) {

	var url = 'view.php?p=' + photoID;

	if (location.href.indexOf('index.html') > 0) return location.href.replace('index.html' + location.hash, url);else return location.href.replace(location.hash, url);
};

/**
 * @description Searches through your photos and albums.
 */

search = {

	hash: null

};

search.find = function (term) {

	clearTimeout($(window).data('timeout'));

	$(window).data('timeout', setTimeout(function () {

		if (header.dom('.header__search').val().length !== 0) {

			api.post('search', { term: term }, function (data) {

				var html = '';
				var albumsData = '';
				var photosData = '';

				// Build albums
				if (data && data.albums) {
					albums.json = { albums: data.albums };
					$.each(albums.json.albums, function () {
						albums.parse(this);
						albumsData += build.album(this);
					});
				}

				// Build photos
				if (data && data.photos) {
					album.json = { photos: data.photos };
					$.each(album.json.photos, function () {
						photosData += build.photo(this);
					});
				}

				// 1. No albums and photos
				// 2. Only photos
				// 3. Only albums
				// 4. Albums and photos
				if (albumsData === '' && photosData === '') html = 'error';else if (albumsData === '') html = build.divider(lychee.locale['PHOTOS']) + photosData;else if (photosData === '') html = build.divider(lychee.locale['ALBUMS']) + albumsData;else html = build.divider(lychee.locale['ALBUMS']) + albumsData + build.divider(lychee.locale['PHOTOS']) + photosData;

				// Only refresh view when search results are different
				if (search.hash !== data.hash) {

					$('.no_content').remove();

					lychee.animate('.content', 'contentZoomOut');

					search.hash = data.hash;

					setTimeout(function () {

						if (html === 'error') {
							lychee.content.html('');
							$('body').append(build.no_content('magnifying-glass'));
						} else {
							lychee.content.html(html);
							lychee.animate(lychee.content, 'contentZoomIn');
						}
					}, 300);
				}
			});
		} else search.reset();
	}, 250));
};

search.reset = function () {

	header.dom('.header__search').val('');
	$('.no_content').remove();

	if (search.hash != null) {

		// Trash data
		albums.json = null;
		album.json = null;
		photo.json = null;
		search.hash = null;

		lychee.animate('.divider', 'fadeOut');
		lychee.goto();
	}
};

/**
 * @description Lets you change settings.
 */

settings = {};

settings.open = function () {
	if (lychee.api_V2) {
		// we may do something else here later
		view.settings.init();
	} else {
		view.settings.init();
	}
};

settings.createConfig = function () {

	var action = function action(data) {

		var dbName = data.dbName || '';
		var dbUser = data.dbUser || '';
		var dbPassword = data.dbPassword || '';
		var dbHost = data.dbHost || '';
		var dbTablePrefix = data.dbTablePrefix || '';

		if (dbUser.length < 1) {
			basicModal.error('dbUser');
			return false;
		}

		if (dbHost.length < 1) dbHost = 'localhost';
		if (dbName.length < 1) dbName = 'lychee';

		var params = {
			dbName: dbName,
			dbUser: dbUser,
			dbPassword: dbPassword,
			dbHost: dbHost,
			dbTablePrefix: dbTablePrefix
		};

		api.post('Config::create', params, function (data) {

			if (data !== true) {

				// Connection failed
				if (data === 'Warning: Connection failed!') {

					basicModal.show({
						body: '<p>' + lychee.locale['ERROR_DB_1'] + '</p>',
						buttons: {
							action: {
								title: lychee.locale['RETRY'],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Creation failed
				if (data === 'Warning: Creation failed!') {

					basicModal.show({
						body: '<p>' + lychee.locale['ERROR_DB_2'] + '</p>',
						buttons: {
							action: {
								title: lychee.locale['RETRY'],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Could not create file
				if (data === 'Warning: Could not create file!') {

					basicModal.show({
						body: "<p>" + lychee.locale['ERROR_CONFIG_FILE'] + "</p>",
						buttons: {
							action: {
								title: lychee.locale['RETRY'],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Something went wrong
				basicModal.show({
					body: '<p>' + lychee.locale['ERROR_UNKNOWN'] + '</p>',
					buttons: {
						action: {
							title: lychee.locale['RETRY'],
							fn: settings.createConfig
						}
					}
				});

				return false;
			} else {

				// Configuration successful
				window.location.reload();
			}
		});
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['DB_INFO_TITLE'] + "\n\t\t\t\t  <input name='dbHost' class='text' type='text' placeholder='" + lychee.locale['DB_INFO_HOST'] + "' value=''>\n\t\t\t\t  <input name='dbUser' class='text' type='text' placeholder='" + lychee.locale['DB_INFO_USER'] + "' value=''>\n\t\t\t\t  <input name='dbPassword' class='text' type='password' placeholder='" + lychee.locale['DB_INFO_PASSWORD'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['DB_INFO_TEXT'] + "\n\t\t\t\t  <input name='dbName' class='text' type='text' placeholder='" + lychee.locale['DB_NAME'] + "' value=''>\n\t\t\t\t  <input name='dbTablePrefix' class='text' type='text' placeholder='" + lychee.locale['DB_PREFIX'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['DB_CONNECT'],
				fn: action
			}
		}
	});
};

settings.createLogin = function () {

	var action = function action(data) {

		var username = data.username;
		var password = data.password;

		if (username.length < 1) {
			basicModal.error('username');
			return false;
		}

		if (password.length < 1) {
			basicModal.error('password');
			return false;
		}

		basicModal.close();

		var params = {
			username: username,
			password: password
		};

		api.post('Settings::setLogin', params, function (data) {

			if (data !== true) {

				basicModal.show({
					body: '<p>' + lychee.locale['ERROR_LOGIN'] + '</p>',
					buttons: {
						action: {
							title: lychee.locale['RETRY'],
							fn: settings.createLogin
						}
					}
				});
			}
		});
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['LOGIN_TITLE'] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale['LOGIN_USERNAME'] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale['LOGIN_CREATE'],
				fn: action
			}
		}
	});
};

// from https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
settings.getValues = function (form_name) {

	var values = {};
	var inputs_select = $(form_name + ' input[name], ' + form_name + ' select[name]');

	// Get value from all inputs
	$(inputs_select).each(function () {

		var name = $(this).attr('name');
		// Store name and value of input
		values[name] = $(this).val();
	});
	return Object.keys(values).length === 0 ? null : values;
};

// from https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
settings.bind = function (item, name, fn) {

	// if ($(item).length)
	// {
	//     console.log('found');
	// }
	// else
	// {
	//     console.log('not found: ' + item);
	// }
	// Action-button
	$(item).on('click', function () {
		fn(settings.getValues(name));
	});
};

settings.changeLogin = function (params) {

	if (params.username.length < 1) {
		loadingBar.show('error', 'new username cannot be empty.');
		$('input[name=username]').addClass('error');
		return false;
	} else {
		$('input[name=username]').removeClass('error');
	}

	if (params.password.length < 1) {
		loadingBar.show('error', 'new password cannot be empty.');
		$('input[name=password]').addClass('error');
		return false;
	} else {
		$('input[name=password]').removeClass('error');
	}

	api.post('Settings::setLogin', params, function (data) {

		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, datas, data);
		} else {
			$('input[name]').removeClass('error');
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_LOGIN']);
			view.settings.content.clearLogin();
		}
	});
};

settings.changeSorting = function (params) {

	api.post('Settings::setSorting', params, function (data) {

		if (data === true) {
			lychee.sortingAlbums = 'ORDER BY ' + params['typeAlbums'] + ' ' + params['orderAlbums'];
			lychee.sortingPhotos = 'ORDER BY ' + params['typePhotos'] + ' ' + params['orderPhotos'];
			albums.refresh();
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_SORT']);
		} else lychee.error(null, params, data);
	});
};

settings.changeDropboxKey = function (params) {
	// let key = params.key;

	if (params.key.length < 1) {
		loadingBar.show('error', 'key cannot be empty.');
		return false;
	}

	api.post('Settings::setDropboxKey', params, function (data) {

		if (data === true) {
			lychee.dropboxKey = params.key;
			// if (callback) lychee.loadDropbox(callback)
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_DROPBOX']);
		} else lychee.error(null, params, data);
	});
};

settings.changeLang = function (params) {

	api.post('Settings::setLang', params, function (data) {

		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_LANG']);
			lychee.init(); // to reload languages.
		} else lychee.error(null, params, data);
	});
};
sharing = {
	json: null
};

sharing.add = function () {

	var params = {
		albumIDs: '',
		UserIDs: ''
	};

	$('#albums_list_to option').each(function () {
		if (params.albumIDs !== '') params.albumIDs += ',';
		params.albumIDs += this.value;
	});

	$('#user_list_to option').each(function () {
		if (params.UserIDs !== '') params.UserIDs += ',';
		params.UserIDs += this.value;
	});

	api.post('Sharing::Add', params, function (data) {
		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show('success', 'Sharing updated!');
			sharing.list(); // reload user list
		}
	});
};

sharing.delete = function () {

	var params = {
		ShareIDs: ''
	};

	$('input[name="remove_id"]').each(function () {
		if (params.ShareIDs !== '') params.ShareIDs += ',';
		params.ShareIDs += this.value;
	});

	api.post('Sharing::Delete', params, function (data) {
		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show('success', 'Sharing removed!');
			sharing.list(); // reload user list
		}
	});
};

sharing.list = function () {
	api.post('Sharing::List', {}, function (data) {
		sharing.json = data;
		view.sharing.init();
	});
};
/**
 * @description This module takes care of the sidebar.
 */

sidebar = {

	_dom: $('.sidebar'),
	types: {
		DEFAULT: 0,
		TAGS: 1
	},
	createStructure: {}

};

sidebar.dom = function (selector) {

	if (selector == null || selector === '') return sidebar._dom;

	return sidebar._dom.find(selector);
};

sidebar.bind = function () {

	// This function should be called after building and appending
	// the sidebars content to the DOM.
	// This function can be called multiple times, therefore
	// event handlers should be removed before binding a new one.

	// Event Name
	var eventName = lychee.getEventName();

	sidebar.dom('#edit_title').off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setTitle([photo.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	sidebar.dom('#edit_description').off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setDescription(photo.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	sidebar.dom('#edit_tags').off(eventName).on(eventName, function () {
		photo.editTags([photo.getID()]);
	});

	sidebar.dom('#tags .tag span').off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data('index'));
	});

	return true;
};

sidebar.toggle = function () {

	if (visible.sidebar() || visible.sidebarbutton()) {

		header.dom('.button--info').toggleClass('active');
		lychee.content.toggleClass('content--sidebar');
		sidebar.dom().toggleClass('active');

		return true;
	}

	return false;
};

sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;


	// Attributes/Values inside the sidebar are selectable by default.
	// Selection needs to be deactivated to prevent an unwanted selection
	// while using multiselect.

	if (selectable === true) sidebar.dom().removeClass('notSelectable');else sidebar.dom().addClass('notSelectable');
};

sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '-';
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;


	if (attr == null || attr === '') return false;

	// Set a default for the value
	if (value == null || value === '') value = '-';

	// Escape value
	if (dangerouslySetInnerHTML === false) value = lychee.escapeHTML(value);

	// Set new value
	sidebar.dom('.attr_' + attr).html(value);

	return true;
};

sidebar.createStructure.photo = function (data) {

	if (data == null || data === '') return false;

	var editable = false;
	var exifHash = data.takestamp + data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;
	var structure = {};
	var _public = '';

	// Enable editable when user logged in
	if (lychee.publicMode === false && lychee.upload) editable = true;

	// Set value for public
	switch (data.public) {

		case '0':
			_public = lychee.locale['PHOTO_SHR_NO'];
			break;
		case '1':
			_public = lychee.locale['PHOTO_SHR_YES'];
			break;
		case '2':
			_public = lychee.locale['PHOTO_SHR_ALB'];
			break;
		default:
			_public = '-';
			break;

	}

	structure.basics = {
		title: lychee.locale['PHOTO_BASICS'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_TITLE'], value: data.title, editable: editable }, { title: lychee.locale['PHOTO_UPLOADED'], value: data.sysdate }, { title: lychee.locale['PHOTO_DESCRIPTION'], value: data.description, editable: editable }]
	};

	structure.image = {
		title: lychee.locale['PHOTO_IMAGE'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_SIZE'], value: data.size }, { title: lychee.locale['PHOTO_FORMAT'], value: data.type }, { title: lychee.locale['PHOTO_RESOLUTION'], value: data.width + ' x ' + data.height }]
	};

	// Only create tags section when user logged in
	if (lychee.publicMode === false && lychee.upload) {

		structure.tags = {
			title: lychee.locale['PHOTO_TAGS'],
			type: sidebar.types.TAGS,
			value: build.tags(data.tags),
			editable: editable
		};
	} else {

		structure.tags = {};
	}

	// Only create EXIF section when EXIF data available
	if (exifHash !== '0') {

		structure.exif = {
			title: lychee.locale['PHOTO_CAMERA'],
			type: sidebar.types.DEFAULT,
			rows: [{ title: lychee.locale['PHOTO_CAPTURED'], value: data.takedate }, { title: lychee.locale['PHOTO_MAKE'], value: data.make }, { title: lychee.locale['PHOTO_TYPE'], value: data.model }, { title: lychee.locale['PHOTO_SHUTTER'], value: data.shutter }, { title: lychee.locale['PHOTO_APERTURE'], value: data.aperture }, { title: lychee.locale['PHOTO_FOCAL'], value: data.focal }, { title: lychee.locale['PHOTO_ISO'], value: data.iso }]
		};
	} else {

		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale['PHOTO_SHARING'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_SHR_PLUBLIC'], value: _public }]
	};

	// Construct all parts of the structure
	structure = [structure.basics, structure.image, structure.tags, structure.exif, structure.sharing];

	return structure;
};

sidebar.createStructure.album = function (data) {

	if (data == null || data === '') return false;

	var editable = false;
	var structure = {};
	var _public = '';
	var hidden = '';
	var downloadable = '';
	var password = '';

	// Enable editable when user logged in
	if (lychee.publicMode === false && lychee.upload) editable = true;

	// Set value for public
	switch (data.public) {

		case '0':
			_public = lychee.locale['ALBUM_SHR_NO'];
			break;
		case '1':
			_public = lychee.locale['ALBUM_SHR_YES'];
			break;
		default:
			_public = '-';
			break;

	}

	// Set value for hidden
	switch (data.visible) {

		case '0':
			hidden = lychee.locale['ALBUM_SHR_YES'];
			break;
		case '1':
			hidden = lychee.locale['ALBUM_SHR_NO'];
			break;
		default:
			hidden = '-';
			break;

	}

	// Set value for downloadable
	switch (data.downloadable) {

		case '0':
			downloadable = lychee.locale['ALBUM_SHR_NO'];
			break;
		case '1':
			downloadable = lychee.locale['ALBUM_SHR_YES'];
			break;
		default:
			downloadable = '-';
			break;

	}

	// Set value for password
	switch (data.password) {

		case '0':
			password = lychee.locale['ALBUM_SHR_NO'];
			break;
		case '1':
			password = lychee.locale['ALBUM_SHR_YES'];
			break;
		default:
			password = '-';
			break;

	}

	structure.basics = {
		title: lychee.locale['ALBUM_BASICS'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_TITLE'], value: data.title, editable: editable }, { title: lychee.locale['ALBUM_DESCRIPTION'], value: data.description, editable: editable }]
	};

	structure.album = {
		title: lychee.locale['ALBUM_ALBUM'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_CREATED'], value: data.sysdate }, { title: lychee.locale['ALBUM_IMAGES'], value: data.photos.length }]
	};

	structure.share = {
		title: lychee.locale['ALBUM_SHARING'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_PUBLIC'], value: _public }, { title: lychee.locale['ALBUM_HIDDEN'], value: hidden }, { title: lychee.locale['ALBUM_DOWNLOADABLE'], value: downloadable }, { title: lychee.locale['ALBUM_PASSWORD'], value: password }]
	};

	// Construct all parts of the structure
	structure = [structure.basics, structure.album, structure.share];

	return structure;
};

sidebar.render = function (structure) {

	if (structure == null || structure === '' || structure === false) return false;

	var html = '';

	var renderDefault = function renderDefault(section) {

		var _html = '';

		_html += "\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>" + section.title + "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t ";

		section.rows.forEach(function (row) {

			var value = row.value;

			// Set a default for the value
			if (value === '' || value == null) value = '-';

			// Wrap span-element around value for easier selecting on change
			value = lychee.html(_templateObject32, row.title.toLowerCase(), value);

			// Add edit-icon to the value when editable
			if (row.editable === true) value += ' ' + build.editIcon('edit_' + row.title.toLowerCase());

			_html += lychee.html(_templateObject33, row.title, value);
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	var renderTags = function renderTags(section) {

		var _html = '';
		var editable = '';

		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon('edit_tags');

		_html += lychee.html(_templateObject34, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	structure.forEach(function (section) {

		if (section.type === sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === sidebar.types.TAGS) html += renderTags(section);
	});

	return html;
};

/**
 * @description Swipes and moves an object.
 */

swipe = {

	obj: null,
	tolerance: 150,
	offset: 0

};

swipe.start = function (obj, tolerance) {

	if (obj) swipe.obj = obj;
	if (tolerance) swipe.tolerance = tolerance;

	return true;
};

swipe.move = function (e) {

	if (swipe.obj === null) return false;

	swipe.offset = -1 * e.x;

	swipe.obj.css({
		WebkitTransform: 'translateX(' + swipe.offset + 'px)',
		MozTransform: 'translateX(' + swipe.offset + 'px)',
		transform: 'translateX(' + swipe.offset + 'px)'
	});
};

swipe.stop = function (e, left, right) {

	// Only execute once
	if (swipe.obj == null) return false;

	if (e.x <= -swipe.tolerance) {

		left(true);
	} else if (e.x >= swipe.tolerance) {

		right(true);
	} else {

		swipe.obj.css({
			WebkitTransform: 'translateX(0px)',
			MozTransform: 'translateX(0px)',
			transform: 'translateX(0px)'
		});
	}

	swipe.obj = null;
	swipe.offset = 0;
};
/**
 * @description Takes care of every action an album can handle and execute.
 */

upload = {};

upload.show = function (title, files, callback) {

	basicModal.show({
		body: build.uploadModal(title, files),
		buttons: {
			action: {
				title: lychee.locale['CLOSE'],
				class: 'hidden',
				fn: basicModal.close
			}
		},
		callback: callback
	});
};

upload.notify = function (title, text) {

	if (text == null || text === '') text = lychee.locale['UPLOAD_MANAGE_NEW_PHOTOS'];

	if (!window.webkitNotifications) return false;

	if (window.webkitNotifications.checkPermission() !== 0) window.webkitNotifications.requestPermission();

	if (window.webkitNotifications.checkPermission() === 0 && title) {
		var popup = window.webkitNotifications.createNotification('', title, text);
		popup.show();
	}
};

upload.start = {

	local: function local(files) {

		var albumID = album.getID();
		var error = false;
		var warning = false;

		var process = function process(files, file) {

			var formData = new FormData();
			var xhr = new XMLHttpRequest();
			var pre_progress = 0;
			var progress = 0;
			var next_file_started = false;

			var finish = function finish() {

				window.onbeforeunload = null;

				$('#upload_files').val('');

				if (error === false && warning === false) {

					// Success
					basicModal.close();
					upload.notify(lychee.locale['UPLOAD_COMPLETE']);
				} else if (error === false && warning === true) {

					// Warning
					$('.basicModal #basicModal__action.hidden').show();
					upload.notify(lychee.locale['UPLOAD_COMPLETE']);
				} else {

					// Error
					$('.basicModal #basicModal__action.hidden').show();
					upload.notify(lychee.locale['UPLOAD_COMPLETE'], lychee.locale['UPLOAD_COMPLETE_FAILED']);
				}

				albums.refresh();

				if (album.getID() === false) lychee.goto('0');else album.load(albumID);
			};

			formData.append('function', 'Photo::add');
			formData.append('albumID', albumID);
			formData.append(0, file);

			var api_url = api.get_url('Photo::add');

			xhr.open('POST', api_url);

			xhr.onload = function () {

				var data = null;
				var wait = false;
				var errorText = '';

				var isNumber = function isNumber(n) {
					return !isNaN(parseFloat(n)) && isFinite(n);
				};

				try {
					data = JSON.parse(xhr.responseText);
				} catch (e) {
					data = '';
				}

				file.ready = true;

				// Set status
				if (xhr.status === 200 && isNumber(data)) {

					// Success
					$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(lychee.locale['UPLOAD_FINISHED']).addClass('success');
				} else {

					if (data.substr(0, 6) === 'Error:') {

						errorText = data.substr(6) + ' ' + lychee.locale['UPLOAD_ERROR_CONSOLE'];
						error = true;

						// Error Status
						$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');

						// Throw error
						if (error === true) lychee.error(lychee.locale['UPLOAD_FAILED_ERROR'], xhr, data);
					} else if (data.substr(0, 8) === 'Warning:') {

						errorText = data.substr(8);
						warning = true;

						// Warning Status
						$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(lychee.locale['UPLOAD_SKIPPED']).addClass('warning');

						// Throw error
						if (error === true) lychee.error(lychee.locale['UPLOAD_FAILED_WARNING'], xhr, data);
					} else {

						errorText = lychee.locale['UPLOAD_UNKNOWN'];
						error = true;

						// Error Status
						$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');

						// Throw error
						if (error === true) lychee.error(lychee.locale['UPLOAD_ERROR_UNKNOWN'], xhr, data);
					}

					$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') p.notice').html(errorText).show();
				}

				// Check if there are file which are not finished
				for (var i = 0; i < files.length; i++) {

					if (files[i].ready === false) {
						wait = true;
						break;
					}
				}

				// Finish upload when all files are finished
				if (wait === false) finish();
			};

			xhr.upload.onprogress = function (e) {

				if (e.lengthComputable !== true) return false;

				// Calculate progress
				progress = e.loaded / e.total * 100 | 0;

				// Set progress when progress has changed
				if (progress > pre_progress) {
					$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(progress + '%');
					pre_progress = progress;
				}

				if (progress >= 100 && next_file_started === false) {

					// Scroll to the uploading file
					var scrollPos = 0;
					if (file.num + 1 > 4) scrollPos = (file.num + 1 - 4) * 40;
					$('.basicModal .rows').scrollTop(scrollPos);

					// Set status to processing
					$('.basicModal .rows .row:nth-child(' + (file.num + 1) + ') .status').html(lychee.locale['UPLOAD_PROCESSING']);

					// Upload next file
					if (file.next != null) {
						process(files, file.next);
						next_file_started = true;
					}
				}
			};

			xhr.setRequestHeader('X-XSRF-TOKEN', csrf.getCookie('XSRF-TOKEN'));
			xhr.send(formData);
		};

		if (files.length <= 0) return false;
		if (albumID === false || visible.albums() === true) albumID = 0;

		for (var i = 0; i < files.length; i++) {

			files[i].num = i;
			files[i].ready = false;

			if (i < files.length - 1) files[i].next = files[i + 1];else files[i].next = null;
		}

		window.onbeforeunload = function () {
			return lychee.locale['UPLOAD_IN_PROGRESS'];
		};

		upload.show(lychee.locale['UPLOAD_UPLOADING'], files, function () {

			// Upload first file
			process(files, files[0]);
		});
	},

	url: function url() {
		var _url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

		var albumID = album.getID();

		_url = typeof _url === 'string' ? _url : '';

		if (albumID === false) albumID = 0;

		var action = function action(data) {

			var files = [];

			if (data.link && data.link.length > 3) {

				basicModal.close();

				files[0] = {
					name: data.link
				};

				upload.show(lychee.locale['UPLOAD_IMPORTING_URL'], files, function () {

					$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_IMPORTING']);

					var params = {
						url: data.link,
						albumID: albumID
					};

					api.post('Import::url', params, function (data) {

						// Same code as in import.dropbox()

						if (data !== true) {

							$('.basicModal .rows .row p.notice').html(lychee.locale['UPLOAD_IMPORT_WARN_ERR']).show();

							$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_FINISHED']).addClass('warning');

							// Show close button
							$('.basicModal #basicModal__action.hidden').show();

							// Log error
							lychee.error(null, params, data);
						} else {

							basicModal.close();
						}

						upload.notify(lychee.locale['UPLOAD_IMPORT_COMPLETE']);

						albums.refresh();

						if (album.getID() === false) lychee.goto('0');else album.load(albumID);
					});
				});
			} else basicModal.error('link');
		};

		basicModal.show({
			body: lychee.html(_templateObject) + lychee.locale['UPLOAD_IMPORT_INSTR'] + (" <input class='text' name='link' type='text' placeholder='http://' value='" + _url + "'></p>"),
			buttons: {
				action: {
					title: lychee.locale['UPLOAD_IMPORT'],
					fn: action
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});
	},

	server: function server() {

		var albumID = album.getID();
		if (albumID === false) albumID = 0;

		var action = function action(data) {

			var files = [];

			files[0] = {
				name: data.path
			};

			upload.show(lychee.locale['UPLOAD_IMPORT_SERVER'], files, function () {

				$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_IMPORTING']);

				var params = {
					albumID: albumID,
					path: data.path
				};

				api.post('Import::server', params, function (data) {

					albums.refresh();
					upload.notify(lychee.locale['UPLOAD_IMPORT_COMPLETE']);

					if (data === 'Notice: Import only contained albums!') {

						// No error, but the folder only contained albums

						// Go back to the album overview to show the imported albums
						if (visible.albums()) lychee.load();else lychee.goto();

						basicModal.close();

						return true;
					} else if (data === 'Warning: Folder empty or no readable files to process!') {

						// Error because the import could not start

						$('.basicModal .rows .row p.notice').html(lychee.locale['UPLOAD_IMPORT_SERVER_FOLD']).show();

						$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');

						// Log error
						lychee.error(lychee.locale['UPLOAD_IMPORT_SERVER_EMPT'], params, data);
					} else if (data !== true) {

						// Maybe an error, maybe just some skipped photos

						$('.basicModal .rows .row p.notice').html(lychee.locale['UPLOAD_IMPORT_WARN_ERR']).show();

						$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_FINISHED']).addClass('warning');

						// Log error
						lychee.error(null, params, data);
					} else {

						// No error, everything worked fine

						basicModal.close();
					}

					if (album.getID() === false) lychee.goto('0');else album.load(albumID);

					// Show close button
					$('.basicModal #basicModal__action.hidden').show();
				});
			});
		};

		basicModal.show({
			body: lychee.html(_templateObject) + lychee.locale['UPLOAD_IMPORT_SERVER_INSTR'] + " <input class='text' name='path' type='text' maxlength='100' placeholder='" + lychee.locale['UPLOAD_ABSOLUTE_PATH'] + ("' value='" + lychee.location + "uploads/import/'></p>"),
			buttons: {
				action: {
					title: lychee.locale['UPLOAD_IMPORT'],
					fn: action
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});
	},

	dropbox: function dropbox() {

		var albumID = album.getID();
		if (albumID === false) albumID = 0;

		var success = function success(files) {

			var links = '';

			for (var i = 0; i < files.length; i++) {

				links += files[i].link + ',';

				files[i] = {
					name: files[i].link
				};
			}

			// Remove last comma
			links = links.substr(0, links.length - 1);

			upload.show('Importing from Dropbox', files, function () {

				$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_IMPORTING']);

				var params = {
					url: links,
					albumID: albumID
				};

				api.post('Import::url', params, function (data) {

					// Same code as in import.url()

					if (data !== true) {

						$('.basicModal .rows .row p.notice').html(lychee.locale['UPLOAD_IMPORT_WARN_ERR']).show();

						$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_FINISHED']).addClass('warning');

						// Show close button
						$('.basicModal #basicModal__action.hidden').show();

						// Log error
						lychee.error(null, params, data);
					} else {

						basicModal.close();
					}

					upload.notify(lychee.locale['UPLOAD_IMPORT_COMPLETE']);

					albums.refresh();

					if (album.getID() === false) lychee.goto('0');else album.load(albumID);
				});
			});
		};

		lychee.loadDropbox(function () {
			Dropbox.choose({
				linkType: 'direct',
				multiselect: true,
				success: success
			});
		});
	}

};

users = {
	json: null
};

users.update = function (params) {

	if ($('#UserData' + params.id + ' .choice input[name="upload"]:checked').length === 1) {
		params.upload = '1';
	} else {
		params.upload = '0';
	}
	if ($('#UserData' + params.id + ' .choice input[name="lock"]:checked').length === 1) {
		params.lock = '1';
	} else {
		params.lock = '0';
	}

	api.post('User::Save', params, function (data) {
		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show('success', 'User updated!');
			users.list(); // reload user list
		}
	});
};

users.create = function (params) {

	if ($('#UserCreate .choice input[name="upload"]:checked').length === 1) {
		params.upload = '1';
	} else {
		params.upload = '0';
	}
	if ($('#UserCreate .choice input[name="lock"]:checked').length === 1) {
		params.lock = '1';
	} else {
		params.lock = '0';
	}

	api.post('User::Create', params, function (data) {
		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show('success', 'User created!');
			users.list(); // reload user list
		}
	});
};

users.delete = function (params) {

	api.post('User::Delete', params, function (data) {
		if (data !== true) {
			loadingBar.show('error', data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show('success', 'User deleted!');
			users.list(); // reload user list
		}
	});
};

users.list = function () {
	api.post('User::List', {}, function (data) {
		users.json = data;
		view.users.init();
	});
};
/**
 * @description Responsible to reflect data changes to the UI.
 */

view = {};

view.albums = {

	init: function init() {

		view.albums.title();
		view.albums.content.init();
	},

	title: function title() {

		lychee.setTitle(lychee.locale['ALBUMS'], false);
	},

	content: {

		scrollPosition: 0,

		init: function init() {

			var smartData = '';
			var albumsData = '';
			var sharedData = '';

			// Smart Albums
			if (lychee.publicMode === false && albums.json.smartalbums != null) {

				albums.parse(albums.json.smartalbums.unsorted);
				albums.parse(albums.json.smartalbums.public);
				albums.parse(albums.json.smartalbums.starred);
				albums.parse(albums.json.smartalbums.recent);

				smartData = build.divider(lychee.locale['SMART_ALBUMS']);
				smartData += build.album(albums.json.smartalbums.unsorted);
				smartData += build.album(albums.json.smartalbums.public);
				smartData += build.album(albums.json.smartalbums.starred);
				smartData += build.album(albums.json.smartalbums.recent);
			}

			// Albums
			if (albums.json.albums && albums.json.albums.length !== 0) {

				$.each(albums.json.albums, function () {
					if (!this.parent_id || this.parent_id === 0) {
						albums.parse(this);
						albumsData += build.album(this);
					}
				});

				// Add divider
				if (lychee.publicMode === false) albumsData = build.divider(lychee.locale['ALBUMS']) + albumsData;
			}

			if (lychee.api_V2) {
				// Shared
				if (albums.json.shared_albums && albums.json.shared_albums.length !== 0) {

					$.each(albums.json.shared_albums, function () {
						if (!this.parent_id || this.parent_id === 0) {
							albums.parse(this);
							sharedData += build.album(this, true);
						}
					});

					// Add divider
					if (lychee.publicMode === false) sharedData = build.divider(lychee.locale['SHARED_ALBUMS']) + sharedData;
				}
			}

			if (smartData === '' && albumsData === '' && sharedData === '') {
				lychee.content.html('');
				$('body').append(build.no_content('eye'));
			} else {
				lychee.content.html(smartData + albumsData + sharedData);
			}

			// Restore scroll position
			if (view.albums.content.scrollPosition != null && view.albums.content.scrollPosition !== 0) {
				$(document).scrollTop(view.albums.content.scrollPosition);
			}
		},

		title: function title(albumID) {

			var title = albums.getByID(albumID).title;

			title = lychee.escapeHTML(title);

			$('.album[data-id="' + albumID + '"] .overlay h1').html(title).attr('title', title);
		},

		delete: function _delete(albumID) {

			$('.album[data-id="' + albumID + '"]').css('opacity', 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (albums.json.albums.length <= 0) lychee.content.find('.divider:last-child').remove();
			});
		}

	}

};

view.album = {

	init: function init() {

		album.parse();

		view.album.sidebar();
		view.album.title();
		view.album.public();
		view.album.content.init();

		album.json.init = 1;
	},

	title: function title() {

		if ((visible.album() || !album.json.init) && !visible.photo()) {

			switch (album.getID()) {
				case 'f':
					lychee.setTitle(lychee.locale['STARED'], false);
					break;
				case 's':
					lychee.setTitle(lychee.locale['PUBLIC'], false);
					break;
				case 'r':
					lychee.setTitle(lychee.locale['RECENT'], false);
					break;
				case '0':
					lychee.setTitle(lychee.locale['UNSORTED'], false);
					break;
				default:
					if (album.json.init) sidebar.changeAttr('title', album.json.title);
					lychee.setTitle(album.json.title, true);
					break;
			}
		}
	},

	content: {

		init: function init() {

			var photosData = '';

			if (album.json.albums && album.json.albums !== false) {
				$.each(album.json.albums, function () {
					albums.parse(this);
					photosData += build.album(this);
				});

				// Add divider
				if (album.json.albums.length > 0) {
					photosData = build.divider(lychee.locale['ALBUMS']) + photosData;
					photosData += build.divider(lychee.locale['PHOTOS']);
				}
			}
			if (album.json.photos && album.json.photos !== false) {

				// Build photos
				$.each(album.json.photos, function () {
					photosData += build.photo(this);
				});
			}

			// Save and reset scroll position
			view.albums.content.scrollPosition = $(document).scrollTop();
			requestAnimationFrame(function () {
				return $(document).scrollTop(0);
			});

			// Add photos to view
			lychee.content.html(photosData);
		},

		title: function title(photoID) {

			var title = album.getByID(photoID).title;

			title = lychee.escapeHTML(title);

			$('.photo[data-id="' + photoID + '"] .overlay h1').html(title).attr('title', title);
		},

		star: function star(photoID) {

			var $badge = $('.photo[data-id="' + photoID + '"] .icn-star');

			if (album.getByID(photoID).star === '1') $badge.addClass('badge--star');else $badge.removeClass('badge--star');
		},

		public: function _public(photoID) {

			var $badge = $('.photo[data-id="' + photoID + '"] .icn-share');

			if (album.getByID(photoID).public === '1') $badge.addClass('badge--visible');else $badge.removeClass('badge--visible');
		},

		delete: function _delete(photoID) {

			$('.photo[data-id="' + photoID + '"]').css('opacity', 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				// Only when search is not active
				if (!visible.albums()) {
					album.json.num--;
					view.album.num();
				}
			});
		}

	},

	description: function description() {

		sidebar.changeAttr('description', album.json.description);
	},

	num: function num() {

		sidebar.changeAttr('images', album.json.num);
	},

	public: function _public() {

		if (album.json.public === '1') {

			$('#button_share_album').addClass('active').attr('title', lychee.locale['SHARE_ALBUM']);

			$('.photo .iconic-share').remove();

			if (album.json.init) sidebar.changeAttr('public', lychee.locale['ALBUM_SHR_YES']);
		} else {

			$('#button_share_album').removeClass('active').attr('title', lychee.locale['MAKE_PUBLIC']);

			if (album.json.init) sidebar.changeAttr('public', lychee.locale['ALBUM_SHR_NO']);
		}
	},

	hidden: function hidden() {

		if (album.json.visible === '1') sidebar.changeAttr('hidden', lychee.locale['ALBUM_SHR_NO']);else sidebar.changeAttr('hidden', lychee.locale['ALBUM_SHR_YES']);
	},

	downloadable: function downloadable() {

		if (album.json.downloadable === '1') sidebar.changeAttr('downloadable', lychee.locale['ALBUM_SHR_YES']);else sidebar.changeAttr('downloadable', lychee.locale['ALBUM_SHR_NO']);
	},

	password: function password() {

		if (album.json.password === '1') sidebar.changeAttr('password', lychee.locale['ALBUM_SHR_YES']);else sidebar.changeAttr('password', lychee.locale['ALBUM_SHR_NO']);
	},

	sidebar: function (_sidebar) {
		function sidebar() {
			return _sidebar.apply(this, arguments);
		}

		sidebar.toString = function () {
			return _sidebar.toString();
		};

		return sidebar;
	}(function () {

		if ((visible.album() || !album.json.init) && !visible.photo()) {

			var structure = sidebar.createStructure.album(album.json);
			var html = sidebar.render(structure);

			sidebar.dom('.sidebar__wrapper').html(html);
			sidebar.bind();
		}
	})

};

view.photo = {

	init: function init() {

		photo.parse();

		view.photo.sidebar();
		view.photo.title();
		view.photo.star();
		view.photo.public();
		view.photo.photo();

		photo.json.init = 1;
	},

	show: function show() {

		// Change header
		lychee.content.addClass('view');
		header.setMode('photo');

		// Make body not scrollable
		$('body').css('overflow', 'hidden');

		// Fullscreen
		var timeout = null;
		$(document).bind('mousemove', function () {
			clearTimeout(timeout);
			header.show();
			timeout = setTimeout(header.hide, 2500);
		});

		lychee.animate(lychee.imageview, 'fadeIn');
	},

	hide: function hide() {

		header.show();

		lychee.content.removeClass('view');
		header.setMode('album');

		// Make body scrollable
		$('body').css('overflow', 'auto');

		// Disable Fullscreen
		$(document).unbind('mousemove');
		if ($('video').length) {
			$('video')[$('video').length - 1].pause();
		}

		// Hide Photo
		lychee.animate(lychee.imageview, 'fadeOut');
		setTimeout(function () {
			lychee.imageview.hide();
			view.album.sidebar();
		}, 300);
	},

	title: function title() {

		if (photo.json.init) sidebar.changeAttr('title', photo.json.title);
		lychee.setTitle(photo.json.title, true);
	},

	description: function description() {

		if (photo.json.init) sidebar.changeAttr('description', photo.json.description);
	},

	star: function star() {

		if (photo.json.star === '1') {

			// Starred
			$('#button_star').addClass('active').attr('title', lychee.locale['UNSTAR_PHOTO']);
		} else {

			// Unstarred
			$('#button_star').removeClass('active').attr('title', lychee.locale['STAR_PHOTO']);
		}
	},

	public: function _public() {

		if (photo.json.public === '1' || photo.json.public === '2') {

			// Photo public
			$('#button_share').addClass('active').attr('title', lychee.locale['SHARE_PHOTO']);

			if (photo.json.init) sidebar.changeAttr('public', lychee.locale['PHOTO_SHR_YES']);
		} else {

			// Photo private
			$('#button_share').removeClass('active').attr('title', 'Make Public');

			if (photo.json.init) sidebar.changeAttr('public', 'No');
		}
	},

	tags: function tags() {

		sidebar.changeAttr('tags', build.tags(photo.json.tags), true);
		sidebar.bind();
	},

	photo: function (_photo) {
		function photo() {
			return _photo.apply(this, arguments);
		}

		photo.toString = function () {
			return _photo.toString();
		};

		return photo;
	}(function () {

		lychee.imageview.html(build.imageview(photo.json, visible.header()));

		var $nextArrow = lychee.imageview.find('a#next');
		var $previousArrow = lychee.imageview.find('a#previous');
		var photoID = photo.getID();
		var hasNext = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).nextPhoto != null && album.getByID(photoID).nextPhoto !== '';
		var hasPrevious = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).previousPhoto != null && album.getByID(photoID).previousPhoto !== '';

		if (hasNext === false || lychee.viewMode === true) {

			$nextArrow.hide();
		} else {

			var nextPhotoID = album.getByID(photoID).nextPhoto;
			var nextPhoto = album.getByID(nextPhotoID);

			$nextArrow.css('background-image', lychee.html(_templateObject35, nextPhoto.thumbUrl));
		}

		if (hasPrevious === false || lychee.viewMode === true) {

			$previousArrow.hide();
		} else {

			var previousPhotoID = album.getByID(photoID).previousPhoto;
			var previousPhoto = album.getByID(previousPhotoID);

			$previousArrow.css('background-image', lychee.html(_templateObject35, previousPhoto.thumbUrl));
		}
	}),

	sidebar: function (_sidebar2) {
		function sidebar() {
			return _sidebar2.apply(this, arguments);
		}

		sidebar.toString = function () {
			return _sidebar2.toString();
		};

		return sidebar;
	}(function () {

		var structure = sidebar.createStructure.photo(photo.json);
		var html = sidebar.render(structure);

		sidebar.dom('.sidebar__wrapper').html(html);
		sidebar.bind();
	})

};

view.settings = {

	init: function init() {

		view.settings.title();
		view.settings.content.init();
	},

	title: function title() {

		lychee.setTitle('Settings', false);
	},

	clearContent: function clearContent() {
		lychee.content.unbind('mousedown');
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {

		init: function init() {
			view.settings.clearContent();
			view.settings.content.setLogin();
			if (lychee.admin) {
				view.settings.content.setSorting();
				view.settings.content.setDropboxKey();
				view.settings.content.setLang();
			}
		},

		setLogin: function setLogin() {
			var msg = "\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['PASSWORD_TITLE'] + "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='" + lychee.locale['USERNAME_CURRENT'] + "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='" + lychee.locale['PASSWORD_CURRENT'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['PASSWORD_TEXT'] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale['LOGIN_USERNAME'] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD'] + "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">" + lychee.locale['PASSWORD_CHANGE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			settings.bind('#basicModal__action_password_change', '.setLogin', settings.changeLogin);
		},

		clearLogin: function clearLogin() {
			$('input[name=oldUsername], input[name=oldPassword], input[name=username], input[name=password]').val('');
		},

		setSorting: function setSorting() {

			var sortingPhotos = [];
			var sortingAlbums = [];

			var msg = "\n\t\t\t<div class=\"setSorting\">\n\t\t\t  <p>" + lychee.locale['SORT_ALBUM_BY_1'] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_albums_type\" name=\"typeAlbums\">\n\t\t\t\t\t\t  <option value='id'>" + lychee.locale['SORT_ALBUM_SELECT_1'] + "</option>\n\t\t\t\t\t\t  <option value='title'>" + lychee.locale['SORT_ALBUM_SELECT_2'] + "</option>\n\t\t\t\t\t\t  <option value='description'>" + lychee.locale['SORT_ALBUM_SELECT_3'] + "</option>\n\t\t\t\t\t\t  <option value='public'>" + lychee.locale['SORT_ALBUM_SELECT_4'] + "</option>\n\t\t\t\t\t\t  <option value='max_takestamp'>" + lychee.locale['SORT_ALBUM_SELECT_5'] + "</option>\n\t\t\t\t\t\t  <option value='min_takestamp'>" + lychee.locale['SORT_ALBUM_SELECT_6'] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale['SORT_ALBUM_BY_2'] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_albums_order\" name=\"orderAlbums\">\n\t\t\t\t\t\t  <option value='ASC'>" + lychee.locale['SORT_ASCENDING'] + "</option>\n\t\t\t\t\t\t  <option value='DESC'>" + lychee.locale['SORT_DESCENDING'] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale['SORT_ALBUM_BY_3'] + "\n\t\t\t  </p>\n\t\t\t  <p>" + lychee.locale['SORT_PHOTO_BY_1'] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_photos_type\" name=\"typePhotos\">\n\t\t\t\t\t\t  <option value='id'>" + lychee.locale['SORT_PHOTO_SELECT_1'] + "</option>\n\t\t\t\t\t\t  <option value='takestamp'>" + lychee.locale['SORT_PHOTO_SELECT_2'] + "</option>\n\t\t\t\t\t\t  <option value='title'>" + lychee.locale['SORT_PHOTO_SELECT_3'] + "</option>\n\t\t\t\t\t\t  <option value='description'>" + lychee.locale['SORT_PHOTO_SELECT_4'] + "</option>\n\t\t\t\t\t\t  <option value='public'>" + lychee.locale['SORT_PHOTO_SELECT_5'] + "</option>\n\t\t\t\t\t\t  <option value='star'>" + lychee.locale['SORT_PHOTO_SELECT_6'] + "</option>\n\t\t\t\t\t\t  <option value='type'>" + lychee.locale['SORT_PHOTO_SELECT_7'] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale['SORT_PHOTO_BY_2'] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_photos_order\" name=\"orderPhotos\">\n\t\t\t\t\t\t  <option value='ASC'>" + lychee.locale['SORT_ASCENDING'] + "</option>\n\t\t\t\t\t\t  <option value='DESC'>" + lychee.locale['SORT_DESCENDING'] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale['SORT_PHOTO_BY_3'] + "\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">" + lychee.locale['SORT_CHANGE'] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);

			if (lychee.sortingAlbums !== '') {

				sortingAlbums = lychee.sortingAlbums.replace('ORDER BY ', '').split(' ');

				$('.setSorting select#settings_albums_type').val(sortingAlbums[0]);
				$('.setSorting select#settings_albums_order').val(sortingAlbums[1]);
			}

			if (lychee.sortingPhotos !== '') {

				sortingPhotos = lychee.sortingPhotos.replace('ORDER BY ', '').split(' ');

				$('.setSorting select#settings_photos_type').val(sortingPhotos[0]);
				$('.setSorting select#settings_photos_order').val(sortingPhotos[1]);
			}

			settings.bind('#basicModal__action_sorting_change', '.setSorting', settings.changeSorting);
		},

		setDropboxKey: function setDropboxKey() {
			var msg = "\n\t\t\t<div class=\"setDropBox\">\n\t\t\t  <p>" + lychee.locale['DROPBOX_TEXT'] + "\n\t\t\t  <input class='text' name='key' type='text' placeholder='Dropbox API Key' value='" + lychee.dropboxKey + "'>\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<a id=\"basicModal__action_dropbox_change\" class=\"basicModal__button\">" + lychee.locale['DROPBOX_TITLE'] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);
			settings.bind('#basicModal__action_dropbox_change', '.setDropBox', settings.changeDropboxKey);
		},

		setLang: function setLang() {
			var msg = "\n\t\t\t<div class=\"setLang\">\n\t\t\t<p>" + lychee.locale['LANG_TEXT'] + "\n\t\t\t  <span class=\"select\">\n\t\t\t\t  <select id=\"settings_photos_order\" name=\"lang\">";
			var i = 0;
			while (i < lychee.lang_available.length) {
				var lang_av = lychee.lang_available[i];
				msg += "<option " + (lychee.lang === lang_av ? 'selected' : '') + ">" + lang_av + "</option>";
				i += 1;
			}
			msg += "\n\t\t\t\t  </select>\n\t\t\t  </span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_lang\" class=\"basicModal__button\">" + lychee.locale['LANG_TITLE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);
			settings.bind('#basicModal__action_set_lang', '.setLang', settings.changeLang);
		}
	}

};

view.users = {
	init: function init() {

		view.users.title();
		view.users.content.init();
	},

	title: function title() {

		lychee.setTitle('Users', false);
	},

	clearContent: function clearContent() {
		lychee.content.unbind('mousedown');
		lychee.content.html('<div class="users_view"></div>');
	},

	content: {

		init: function init() {

			view.users.clearContent();

			if (users.json.length === 0) {
				$(".users_view").append('<div class="users_view_line" style="margin-bottom: 50px;"><p style="text-align: center">User list is empty!</p></div>');
			}

			var html = '';

			html += '<div class="users_view_line">' + '<p>' + '<span class="text">username</span>' + '<span class="text">new password</span>' + '<span class="text_icon">' + build.iconic('data-transfer-upload') + '</span>' + '<span class="text_icon">' + build.iconic('lock-locked') + '</span>' + '</p>' + '</div>';

			$(".users_view").append(html);

			$.each(users.json, function () {
				$(".users_view").append(build.user(this));
				// photosData += build.photo(this)
				settings.bind('#UserUpdate' + this.id, '#UserData' + this.id, users.update);
				settings.bind('#UserDelete' + this.id, '#UserData' + this.id, users.delete);
				if (this.upload === 1) {
					$('#UserData' + this.id + ' .choice input[name="upload"]').click();
				}
				if (this.lock === 1) {
					$('#UserData' + this.id + ' .choice input[name="lock"]').click();
				}
			});

			html = '<div class="users_view_line"';

			if (users.json.length === 0) {
				html += ' style="padding-top: 0px;"';
			}
			html += '>' + '<p id="UserCreate">' + '<input class="text" name="username" type="text" value="" placeholder="new username" />' + '<input class="text" name="password" type="text" placeholder="new password" />' + '<span class="choice">' + '<label>' + '<input type="checkbox" name="upload" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + '</label>' + '</span>' + '<span class="choice">' + '<label>' + '<input type="checkbox" name="lock" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + '</label>' + '</span>' + '</p>' + '<a id="UserCreate_button"  class="basicModal__button basicModal__button_CREATE">Create</a>' + '</div>';
			$(".users_view").append(html);
			settings.bind('#UserCreate_button', '#UserCreate', users.create);
		}
	}
};

view.sharing = {
	init: function init() {

		view.sharing.title();
		view.sharing.content.init();
	},

	title: function title() {

		lychee.setTitle('Sharing', false);
	},

	clearContent: function clearContent() {
		lychee.content.unbind('mousedown');
		lychee.content.html('<div class="sharing_view"></div>');
	},

	content: {

		init: function init() {

			view.sharing.clearContent();

			if (sharing.json.shared.length === 0) {
				$(".sharing_view").append('<div class="sharing_view_line" style="margin-bottom: 50px;"><p style="text-align: center">Sharing list is empty!</p></div>');
			}

			var html = '';

			html += "\n\t\t\t<div class=\"sharing_view_line\"><p>Share</p></div>\n\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"from\" id=\"albums_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">";

			$.each(sharing.json.albums, function () {
				html += "<option value=\"" + this.id + "\">" + this.title + "</option>";
			});

			html += "</select>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic('media-skip-forward') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic('chevron-right') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic('chevron-left') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic('media-skip-backward') + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"albums_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";

			html += "\n\t\t\t<div class=\"sharing_view_line\"><p class=\"with\">with</p></div>\n\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"from\" id=\"user_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">";

			$.each(sharing.json.users, function () {
				html += "<option value=\"" + this.id + "\">" + this.username + "</option>";
			});

			html += "</select>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic('media-skip-forward') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic('chevron-right') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic('chevron-left') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic('media-skip-backward') + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"user_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";
			html += "<div class=\"sharing_view_line\"><a id=\"Share_button\"  class=\"basicModal__button\">Share</a></div>";
			html += '<div class="sharing_view_line">';

			$.each(sharing.json.shared, function () {
				html += "<p><span class=\"text\">" + this.title + "</span><span class=\"text\">" + this.username + '</span><span class="choice">' + '<label>' + '<input type="checkbox" name="remove_id" value="' + this.id + '"/>' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + '</label>' + '</span></p>' + "";
			});

			html += '</div>';
			if (sharing.json.shared.length !== 0) {
				html += "<div class=\"sharing_view_line\"><a id=\"Remove_button\"  class=\"basicModal__button\">Remove</a></div>";
			}

			$(".sharing_view").append(html);

			$('#albums_list').multiselect();
			$('#user_list').multiselect();
			$("#Share_button").on('click', sharing.add).on('mouseenter', function () {
				$('#albums_list_to, #user_list_to').addClass('borderBlue');
			}).on('mouseleave', function () {
				$('#albums_list_to, #user_list_to').removeClass('borderBlue');
			});

			$('#Remove_button').on('click', sharing.delete);
		}
	}
};

view.logs_diagnostics = {
	init: function init(get) {

		view.logs_diagnostics.title(get);
		view.logs_diagnostics.content.init(get);
	},

	title: function title(get) {

		lychee.setTitle(get, false);
	},

	clearContent: function clearContent() {
		lychee.content.unbind('mousedown');
		lychee.content.html('<pre class="logs_diagnostics_view"></pre>');
	},

	content: {
		init: function init(get) {
			view.logs_diagnostics.clearContent();
			api.post_raw(get, {}, function (data) {
				$(".logs_diagnostics_view").html(data);
			});
		}
	}
};

/**
 * @description This module is used to check if elements are visible or not.
 */

visible = {};

visible.albums = function () {
	if (header.dom('.header__toolbar--public').hasClass('header__toolbar--visible')) return true;
	if (header.dom('.header__toolbar--albums').hasClass('header__toolbar--visible')) return true;
	return false;
};

visible.album = function () {
	if (header.dom('.header__toolbar--album').hasClass('header__toolbar--visible')) return true;
	return false;
};

visible.photo = function () {
	if ($('#imageview.fadeIn').length > 0) return true;
	return false;
};

visible.search = function () {
	if (search.hash != null) return true;
	return false;
};

visible.sidebar = function () {
	if (sidebar.dom().hasClass('active') === true) return true;
	return false;
};

visible.sidebarbutton = function () {
	if (visible.photo()) return true;
	if (visible.album() && $('#button_info_album:visible').length > 0) return true;
	return false;
};

visible.header = function () {
	if (header.dom().hasClass('header--hidden') === true) return false;
	return true;
};

visible.contextMenu = function () {
	return basicContext.visible();
};

visible.multiselect = function () {
	if ($('#multiselect').length > 0) return true;
	return false;
};

visible.leftMenu = function () {
	if (leftMenu.dom().hasClass('leftMenu__visible')) return true;
	return false;
};