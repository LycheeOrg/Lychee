/*! jQuery v3.5.1 | (c) JS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(C,e){"use strict";var t=[],r=Object.getPrototypeOf,s=t.slice,g=t.flat?function(e){return t.flat.call(e)}:function(e){return t.concat.apply([],e)},u=t.push,i=t.indexOf,n={},o=n.toString,v=n.hasOwnProperty,a=v.toString,l=a.call(Object),y={},m=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType},x=function(e){return null!=e&&e===e.window},E=C.document,c={type:!0,src:!0,nonce:!0,noModule:!0};function b(e,t,n){var r,i,o=(n=n||E).createElement("script");if(o.text=e,t)for(r in c)(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function w(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?n[o.call(e)]||"object":typeof e}var f="3.5.1",S=function(e,t){return new S.fn.init(e,t)};function p(e){var t=!!e&&"length"in e&&e.length,n=w(e);return!m(e)&&!x(e)&&("array"===n||0===t||"number"==typeof t&&0<t&&t-1 in e)}S.fn=S.prototype={jquery:f,constructor:S,length:0,toArray:function(){return s.call(this)},get:function(e){return null==e?s.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=S.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return S.each(this,e)},map:function(n){return this.pushStack(S.map(this,function(e,t){return n.call(e,t,e)}))},slice:function(){return this.pushStack(s.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(S.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(S.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(0<=n&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:u,sort:t.sort,splice:t.splice},S.extend=S.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||m(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(S.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||S.isPlainObject(n)?n:{},i=!1,a[t]=S.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},S.extend({expando:"jQuery"+(f+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==o.call(e))&&(!(t=r(e))||"function"==typeof(n=v.call(t,"constructor")&&t.constructor)&&a.call(n)===l)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t,n){b(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(p(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},makeArray:function(e,t){var n=t||[];return null!=e&&(p(Object(e))?S.merge(n,"string"==typeof e?[e]:e):u.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:i.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},map:function(e,t,n){var r,i,o=0,a=[];if(p(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&a.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&a.push(i);return g(a)},guid:1,support:y}),"function"==typeof Symbol&&(S.fn[Symbol.iterator]=t[Symbol.iterator]),S.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){n["[object "+t+"]"]=t.toLowerCase()});var d=function(n){var e,d,b,o,i,h,f,g,w,u,l,T,C,a,E,v,s,c,y,S="sizzle"+1*new Date,p=n.document,k=0,r=0,m=ue(),x=ue(),A=ue(),N=ue(),D=function(e,t){return e===t&&(l=!0),0},j={}.hasOwnProperty,t=[],q=t.pop,L=t.push,H=t.push,O=t.slice,P=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},R="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",I="(?:\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",W="\\["+M+"*("+I+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+I+"))|)"+M+"*\\]",F=":("+I+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+W+")*)|.*)\\)|)",B=new RegExp(M+"+","g"),$=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),_=new RegExp("^"+M+"*,"+M+"*"),z=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),U=new RegExp(M+"|>"),X=new RegExp(F),V=new RegExp("^"+I+"$"),G={ID:new RegExp("^#("+I+")"),CLASS:new RegExp("^\\.("+I+")"),TAG:new RegExp("^("+I+"|[*])"),ATTR:new RegExp("^"+W),PSEUDO:new RegExp("^"+F),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+R+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},Y=/HTML$/i,Q=/^(?:input|select|textarea|button)$/i,J=/^h\d$/i,K=/^[^{]+\{\s*\[native \w/,Z=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,ee=/[+~]/,te=new RegExp("\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\([^\\r\\n\\f])","g"),ne=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},re=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ie=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},oe=function(){T()},ae=be(function(e){return!0===e.disabled&&"fieldset"===e.nodeName.toLowerCase()},{dir:"parentNode",next:"legend"});try{H.apply(t=O.call(p.childNodes),p.childNodes),t[p.childNodes.length].nodeType}catch(e){H={apply:t.length?function(e,t){L.apply(e,O.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function se(t,e,n,r){var i,o,a,s,u,l,c,f=e&&e.ownerDocument,p=e?e.nodeType:9;if(n=n||[],"string"!=typeof t||!t||1!==p&&9!==p&&11!==p)return n;if(!r&&(T(e),e=e||C,E)){if(11!==p&&(u=Z.exec(t)))if(i=u[1]){if(9===p){if(!(a=e.getElementById(i)))return n;if(a.id===i)return n.push(a),n}else if(f&&(a=f.getElementById(i))&&y(e,a)&&a.id===i)return n.push(a),n}else{if(u[2])return H.apply(n,e.getElementsByTagName(t)),n;if((i=u[3])&&d.getElementsByClassName&&e.getElementsByClassName)return H.apply(n,e.getElementsByClassName(i)),n}if(d.qsa&&!N[t+" "]&&(!v||!v.test(t))&&(1!==p||"object"!==e.nodeName.toLowerCase())){if(c=t,f=e,1===p&&(U.test(t)||z.test(t))){(f=ee.test(t)&&ye(e.parentNode)||e)===e&&d.scope||((s=e.getAttribute("id"))?s=s.replace(re,ie):e.setAttribute("id",s=S)),o=(l=h(t)).length;while(o--)l[o]=(s?"#"+s:":scope")+" "+xe(l[o]);c=l.join(",")}try{return H.apply(n,f.querySelectorAll(c)),n}catch(e){N(t,!0)}finally{s===S&&e.removeAttribute("id")}}}return g(t.replace($,"$1"),e,n,r)}function ue(){var r=[];return function e(t,n){return r.push(t+" ")>b.cacheLength&&delete e[r.shift()],e[t+" "]=n}}function le(e){return e[S]=!0,e}function ce(e){var t=C.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function fe(e,t){var n=e.split("|"),r=n.length;while(r--)b.attrHandle[n[r]]=t}function pe(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function de(t){return function(e){return"input"===e.nodeName.toLowerCase()&&e.type===t}}function he(n){return function(e){var t=e.nodeName.toLowerCase();return("input"===t||"button"===t)&&e.type===n}}function ge(t){return function(e){return"form"in e?e.parentNode&&!1===e.disabled?"label"in e?"label"in e.parentNode?e.parentNode.disabled===t:e.disabled===t:e.isDisabled===t||e.isDisabled!==!t&&ae(e)===t:e.disabled===t:"label"in e&&e.disabled===t}}function ve(a){return le(function(o){return o=+o,le(function(e,t){var n,r=a([],e.length,o),i=r.length;while(i--)e[n=r[i]]&&(e[n]=!(t[n]=e[n]))})})}function ye(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}for(e in d=se.support={},i=se.isXML=function(e){var t=e.namespaceURI,n=(e.ownerDocument||e).documentElement;return!Y.test(t||n&&n.nodeName||"HTML")},T=se.setDocument=function(e){var t,n,r=e?e.ownerDocument||e:p;return r!=C&&9===r.nodeType&&r.documentElement&&(a=(C=r).documentElement,E=!i(C),p!=C&&(n=C.defaultView)&&n.top!==n&&(n.addEventListener?n.addEventListener("unload",oe,!1):n.attachEvent&&n.attachEvent("onunload",oe)),d.scope=ce(function(e){return a.appendChild(e).appendChild(C.createElement("div")),"undefined"!=typeof e.querySelectorAll&&!e.querySelectorAll(":scope fieldset div").length}),d.attributes=ce(function(e){return e.className="i",!e.getAttribute("className")}),d.getElementsByTagName=ce(function(e){return e.appendChild(C.createComment("")),!e.getElementsByTagName("*").length}),d.getElementsByClassName=K.test(C.getElementsByClassName),d.getById=ce(function(e){return a.appendChild(e).id=S,!C.getElementsByName||!C.getElementsByName(S).length}),d.getById?(b.filter.ID=function(e){var t=e.replace(te,ne);return function(e){return e.getAttribute("id")===t}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n=t.getElementById(e);return n?[n]:[]}}):(b.filter.ID=function(e){var n=e.replace(te,ne);return function(e){var t="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return t&&t.value===n}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),b.find.TAG=d.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):d.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},b.find.CLASS=d.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&E)return t.getElementsByClassName(e)},s=[],v=[],(d.qsa=K.test(C.querySelectorAll))&&(ce(function(e){var t;a.appendChild(e).innerHTML="<a id='"+S+"'></a><select id='"+S+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&v.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||v.push("\\["+M+"*(?:value|"+R+")"),e.querySelectorAll("[id~="+S+"-]").length||v.push("~="),(t=C.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||v.push("\\["+M+"*name"+M+"*="+M+"*(?:''|\"\")"),e.querySelectorAll(":checked").length||v.push(":checked"),e.querySelectorAll("a#"+S+"+*").length||v.push(".#.+[+~]"),e.querySelectorAll("\\\f"),v.push("[\\r\\n\\f]")}),ce(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=C.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&v.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&v.push(":enabled",":disabled"),a.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&v.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),v.push(",.*:")})),(d.matchesSelector=K.test(c=a.matches||a.webkitMatchesSelector||a.mozMatchesSelector||a.oMatchesSelector||a.msMatchesSelector))&&ce(function(e){d.disconnectedMatch=c.call(e,"*"),c.call(e,"[s!='']:x"),s.push("!=",F)}),v=v.length&&new RegExp(v.join("|")),s=s.length&&new RegExp(s.join("|")),t=K.test(a.compareDocumentPosition),y=t||K.test(a.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},D=t?function(e,t){if(e===t)return l=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!d.sortDetached&&t.compareDocumentPosition(e)===n?e==C||e.ownerDocument==p&&y(p,e)?-1:t==C||t.ownerDocument==p&&y(p,t)?1:u?P(u,e)-P(u,t):0:4&n?-1:1)}:function(e,t){if(e===t)return l=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e==C?-1:t==C?1:i?-1:o?1:u?P(u,e)-P(u,t):0;if(i===o)return pe(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?pe(a[r],s[r]):a[r]==p?-1:s[r]==p?1:0}),C},se.matches=function(e,t){return se(e,null,null,t)},se.matchesSelector=function(e,t){if(T(e),d.matchesSelector&&E&&!N[t+" "]&&(!s||!s.test(t))&&(!v||!v.test(t)))try{var n=c.call(e,t);if(n||d.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){N(t,!0)}return 0<se(t,C,null,[e]).length},se.contains=function(e,t){return(e.ownerDocument||e)!=C&&T(e),y(e,t)},se.attr=function(e,t){(e.ownerDocument||e)!=C&&T(e);var n=b.attrHandle[t.toLowerCase()],r=n&&j.call(b.attrHandle,t.toLowerCase())?n(e,t,!E):void 0;return void 0!==r?r:d.attributes||!E?e.getAttribute(t):(r=e.getAttributeNode(t))&&r.specified?r.value:null},se.escape=function(e){return(e+"").replace(re,ie)},se.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},se.uniqueSort=function(e){var t,n=[],r=0,i=0;if(l=!d.detectDuplicates,u=!d.sortStable&&e.slice(0),e.sort(D),l){while(t=e[i++])t===e[i]&&(r=n.push(i));while(r--)e.splice(n[r],1)}return u=null,e},o=se.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=o(e)}else if(3===i||4===i)return e.nodeValue}else while(t=e[r++])n+=o(t);return n},(b=se.selectors={cacheLength:50,createPseudo:le,match:G,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(te,ne),e[3]=(e[3]||e[4]||e[5]||"").replace(te,ne),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||se.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&se.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return G.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=h(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(te,ne).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=m[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&m(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(n,r,i){return function(e){var t=se.attr(e,n);return null==t?"!="===r:!r||(t+="","="===r?t===i:"!="===r?t!==i:"^="===r?i&&0===t.indexOf(i):"*="===r?i&&-1<t.indexOf(i):"$="===r?i&&t.slice(-i.length)===i:"~="===r?-1<(" "+t.replace(B," ")+" ").indexOf(i):"|="===r&&(t===i||t.slice(0,i.length+1)===i+"-"))}},CHILD:function(h,e,t,g,v){var y="nth"!==h.slice(0,3),m="last"!==h.slice(-4),x="of-type"===e;return 1===g&&0===v?function(e){return!!e.parentNode}:function(e,t,n){var r,i,o,a,s,u,l=y!==m?"nextSibling":"previousSibling",c=e.parentNode,f=x&&e.nodeName.toLowerCase(),p=!n&&!x,d=!1;if(c){if(y){while(l){a=e;while(a=a[l])if(x?a.nodeName.toLowerCase()===f:1===a.nodeType)return!1;u=l="only"===h&&!u&&"nextSibling"}return!0}if(u=[m?c.firstChild:c.lastChild],m&&p){d=(s=(r=(i=(o=(a=c)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1])&&r[2],a=s&&c.childNodes[s];while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if(1===a.nodeType&&++d&&a===e){i[h]=[k,s,d];break}}else if(p&&(d=s=(r=(i=(o=(a=e)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1]),!1===d)while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if((x?a.nodeName.toLowerCase()===f:1===a.nodeType)&&++d&&(p&&((i=(o=a[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]=[k,d]),a===e))break;return(d-=v)===g||d%g==0&&0<=d/g}}},PSEUDO:function(e,o){var t,a=b.pseudos[e]||b.setFilters[e.toLowerCase()]||se.error("unsupported pseudo: "+e);return a[S]?a(o):1<a.length?(t=[e,e,"",o],b.setFilters.hasOwnProperty(e.toLowerCase())?le(function(e,t){var n,r=a(e,o),i=r.length;while(i--)e[n=P(e,r[i])]=!(t[n]=r[i])}):function(e){return a(e,0,t)}):a}},pseudos:{not:le(function(e){var r=[],i=[],s=f(e.replace($,"$1"));return s[S]?le(function(e,t,n,r){var i,o=s(e,null,r,[]),a=e.length;while(a--)(i=o[a])&&(e[a]=!(t[a]=i))}):function(e,t,n){return r[0]=e,s(r,null,n,i),r[0]=null,!i.pop()}}),has:le(function(t){return function(e){return 0<se(t,e).length}}),contains:le(function(t){return t=t.replace(te,ne),function(e){return-1<(e.textContent||o(e)).indexOf(t)}}),lang:le(function(n){return V.test(n||"")||se.error("unsupported lang: "+n),n=n.replace(te,ne).toLowerCase(),function(e){var t;do{if(t=E?e.lang:e.getAttribute("xml:lang")||e.getAttribute("lang"))return(t=t.toLowerCase())===n||0===t.indexOf(n+"-")}while((e=e.parentNode)&&1===e.nodeType);return!1}}),target:function(e){var t=n.location&&n.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===a},focus:function(e){return e===C.activeElement&&(!C.hasFocus||C.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:ge(!1),disabled:ge(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!b.pseudos.empty(e)},header:function(e){return J.test(e.nodeName)},input:function(e){return Q.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:ve(function(){return[0]}),last:ve(function(e,t){return[t-1]}),eq:ve(function(e,t,n){return[n<0?n+t:n]}),even:ve(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:ve(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:ve(function(e,t,n){for(var r=n<0?n+t:t<n?t:n;0<=--r;)e.push(r);return e}),gt:ve(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=b.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})b.pseudos[e]=de(e);for(e in{submit:!0,reset:!0})b.pseudos[e]=he(e);function me(){}function xe(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function be(s,e,t){var u=e.dir,l=e.next,c=l||u,f=t&&"parentNode"===c,p=r++;return e.first?function(e,t,n){while(e=e[u])if(1===e.nodeType||f)return s(e,t,n);return!1}:function(e,t,n){var r,i,o,a=[k,p];if(n){while(e=e[u])if((1===e.nodeType||f)&&s(e,t,n))return!0}else while(e=e[u])if(1===e.nodeType||f)if(i=(o=e[S]||(e[S]={}))[e.uniqueID]||(o[e.uniqueID]={}),l&&l===e.nodeName.toLowerCase())e=e[u]||e;else{if((r=i[c])&&r[0]===k&&r[1]===p)return a[2]=r[2];if((i[c]=a)[2]=s(e,t,n))return!0}return!1}}function we(i){return 1<i.length?function(e,t,n){var r=i.length;while(r--)if(!i[r](e,t,n))return!1;return!0}:i[0]}function Te(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Ce(d,h,g,v,y,e){return v&&!v[S]&&(v=Ce(v)),y&&!y[S]&&(y=Ce(y,e)),le(function(e,t,n,r){var i,o,a,s=[],u=[],l=t.length,c=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)se(e,t[r],n);return n}(h||"*",n.nodeType?[n]:n,[]),f=!d||!e&&h?c:Te(c,s,d,n,r),p=g?y||(e?d:l||v)?[]:t:f;if(g&&g(f,p,n,r),v){i=Te(p,u),v(i,[],n,r),o=i.length;while(o--)(a=i[o])&&(p[u[o]]=!(f[u[o]]=a))}if(e){if(y||d){if(y){i=[],o=p.length;while(o--)(a=p[o])&&i.push(f[o]=a);y(null,p=[],i,r)}o=p.length;while(o--)(a=p[o])&&-1<(i=y?P(e,a):s[o])&&(e[i]=!(t[i]=a))}}else p=Te(p===t?p.splice(l,p.length):p),y?y(null,t,p,r):H.apply(t,p)})}function Ee(e){for(var i,t,n,r=e.length,o=b.relative[e[0].type],a=o||b.relative[" "],s=o?1:0,u=be(function(e){return e===i},a,!0),l=be(function(e){return-1<P(i,e)},a,!0),c=[function(e,t,n){var r=!o&&(n||t!==w)||((i=t).nodeType?u(e,t,n):l(e,t,n));return i=null,r}];s<r;s++)if(t=b.relative[e[s].type])c=[be(we(c),t)];else{if((t=b.filter[e[s].type].apply(null,e[s].matches))[S]){for(n=++s;n<r;n++)if(b.relative[e[n].type])break;return Ce(1<s&&we(c),1<s&&xe(e.slice(0,s-1).concat({value:" "===e[s-2].type?"*":""})).replace($,"$1"),t,s<n&&Ee(e.slice(s,n)),n<r&&Ee(e=e.slice(n)),n<r&&xe(e))}c.push(t)}return we(c)}return me.prototype=b.filters=b.pseudos,b.setFilters=new me,h=se.tokenize=function(e,t){var n,r,i,o,a,s,u,l=x[e+" "];if(l)return t?0:l.slice(0);a=e,s=[],u=b.preFilter;while(a){for(o in n&&!(r=_.exec(a))||(r&&(a=a.slice(r[0].length)||a),s.push(i=[])),n=!1,(r=z.exec(a))&&(n=r.shift(),i.push({value:n,type:r[0].replace($," ")}),a=a.slice(n.length)),b.filter)!(r=G[o].exec(a))||u[o]&&!(r=u[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?se.error(e):x(e,s).slice(0)},f=se.compile=function(e,t){var n,v,y,m,x,r,i=[],o=[],a=A[e+" "];if(!a){t||(t=h(e)),n=t.length;while(n--)(a=Ee(t[n]))[S]?i.push(a):o.push(a);(a=A(e,(v=o,m=0<(y=i).length,x=0<v.length,r=function(e,t,n,r,i){var o,a,s,u=0,l="0",c=e&&[],f=[],p=w,d=e||x&&b.find.TAG("*",i),h=k+=null==p?1:Math.random()||.1,g=d.length;for(i&&(w=t==C||t||i);l!==g&&null!=(o=d[l]);l++){if(x&&o){a=0,t||o.ownerDocument==C||(T(o),n=!E);while(s=v[a++])if(s(o,t||C,n)){r.push(o);break}i&&(k=h)}m&&((o=!s&&o)&&u--,e&&c.push(o))}if(u+=l,m&&l!==u){a=0;while(s=y[a++])s(c,f,t,n);if(e){if(0<u)while(l--)c[l]||f[l]||(f[l]=q.call(r));f=Te(f)}H.apply(r,f),i&&!e&&0<f.length&&1<u+y.length&&se.uniqueSort(r)}return i&&(k=h,w=p),c},m?le(r):r))).selector=e}return a},g=se.select=function(e,t,n,r){var i,o,a,s,u,l="function"==typeof e&&e,c=!r&&h(e=l.selector||e);if(n=n||[],1===c.length){if(2<(o=c[0]=c[0].slice(0)).length&&"ID"===(a=o[0]).type&&9===t.nodeType&&E&&b.relative[o[1].type]){if(!(t=(b.find.ID(a.matches[0].replace(te,ne),t)||[])[0]))return n;l&&(t=t.parentNode),e=e.slice(o.shift().value.length)}i=G.needsContext.test(e)?0:o.length;while(i--){if(a=o[i],b.relative[s=a.type])break;if((u=b.find[s])&&(r=u(a.matches[0].replace(te,ne),ee.test(o[0].type)&&ye(t.parentNode)||t))){if(o.splice(i,1),!(e=r.length&&xe(o)))return H.apply(n,r),n;break}}}return(l||f(e,c))(r,t,!E,n,!t||ee.test(e)&&ye(t.parentNode)||t),n},d.sortStable=S.split("").sort(D).join("")===S,d.detectDuplicates=!!l,T(),d.sortDetached=ce(function(e){return 1&e.compareDocumentPosition(C.createElement("fieldset"))}),ce(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||fe("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),d.attributes&&ce(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||fe("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ce(function(e){return null==e.getAttribute("disabled")})||fe(R,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),se}(C);S.find=d,S.expr=d.selectors,S.expr[":"]=S.expr.pseudos,S.uniqueSort=S.unique=d.uniqueSort,S.text=d.getText,S.isXMLDoc=d.isXML,S.contains=d.contains,S.escapeSelector=d.escape;var h=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&S(e).is(n))break;r.push(e)}return r},T=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},k=S.expr.match.needsContext;function A(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var N=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function D(e,n,r){return m(n)?S.grep(e,function(e,t){return!!n.call(e,t,e)!==r}):n.nodeType?S.grep(e,function(e){return e===n!==r}):"string"!=typeof n?S.grep(e,function(e){return-1<i.call(n,e)!==r}):S.filter(n,e,r)}S.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?S.find.matchesSelector(r,e)?[r]:[]:S.find.matches(e,S.grep(t,function(e){return 1===e.nodeType}))},S.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(S(e).filter(function(){for(t=0;t<r;t++)if(S.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)S.find(e,i[t],n);return 1<r?S.uniqueSort(n):n},filter:function(e){return this.pushStack(D(this,e||[],!1))},not:function(e){return this.pushStack(D(this,e||[],!0))},is:function(e){return!!D(this,"string"==typeof e&&k.test(e)?S(e):e||[],!1).length}});var j,q=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(S.fn.init=function(e,t,n){var r,i;if(!e)return this;if(n=n||j,"string"==typeof e){if(!(r="<"===e[0]&&">"===e[e.length-1]&&3<=e.length?[null,e,null]:q.exec(e))||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof S?t[0]:t,S.merge(this,S.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:E,!0)),N.test(r[1])&&S.isPlainObject(t))for(r in t)m(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return(i=E.getElementById(r[2]))&&(this[0]=i,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):m(e)?void 0!==n.ready?n.ready(e):e(S):S.makeArray(e,this)}).prototype=S.fn,j=S(E);var L=/^(?:parents|prev(?:Until|All))/,H={children:!0,contents:!0,next:!0,prev:!0};function O(e,t){while((e=e[t])&&1!==e.nodeType);return e}S.fn.extend({has:function(e){var t=S(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(S.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&S(e);if(!k.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?-1<a.index(n):1===n.nodeType&&S.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(1<o.length?S.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?i.call(S(e),this[0]):i.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(S.uniqueSort(S.merge(this.get(),S(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),S.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return h(e,"parentNode")},parentsUntil:function(e,t,n){return h(e,"parentNode",n)},next:function(e){return O(e,"nextSibling")},prev:function(e){return O(e,"previousSibling")},nextAll:function(e){return h(e,"nextSibling")},prevAll:function(e){return h(e,"previousSibling")},nextUntil:function(e,t,n){return h(e,"nextSibling",n)},prevUntil:function(e,t,n){return h(e,"previousSibling",n)},siblings:function(e){return T((e.parentNode||{}).firstChild,e)},children:function(e){return T(e.firstChild)},contents:function(e){return null!=e.contentDocument&&r(e.contentDocument)?e.contentDocument:(A(e,"template")&&(e=e.content||e),S.merge([],e.childNodes))}},function(r,i){S.fn[r]=function(e,t){var n=S.map(this,i,e);return"Until"!==r.slice(-5)&&(t=e),t&&"string"==typeof t&&(n=S.filter(t,n)),1<this.length&&(H[r]||S.uniqueSort(n),L.test(r)&&n.reverse()),this.pushStack(n)}});var P=/[^\x20\t\r\n\f]+/g;function R(e){return e}function M(e){throw e}function I(e,t,n,r){var i;try{e&&m(i=e.promise)?i.call(e).done(t).fail(n):e&&m(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}S.Callbacks=function(r){var e,n;r="string"==typeof r?(e=r,n={},S.each(e.match(P)||[],function(e,t){n[t]=!0}),n):S.extend({},r);var i,t,o,a,s=[],u=[],l=-1,c=function(){for(a=a||r.once,o=i=!0;u.length;l=-1){t=u.shift();while(++l<s.length)!1===s[l].apply(t[0],t[1])&&r.stopOnFalse&&(l=s.length,t=!1)}r.memory||(t=!1),i=!1,a&&(s=t?[]:"")},f={add:function(){return s&&(t&&!i&&(l=s.length-1,u.push(t)),function n(e){S.each(e,function(e,t){m(t)?r.unique&&f.has(t)||s.push(t):t&&t.length&&"string"!==w(t)&&n(t)})}(arguments),t&&!i&&c()),this},remove:function(){return S.each(arguments,function(e,t){var n;while(-1<(n=S.inArray(t,s,n)))s.splice(n,1),n<=l&&l--}),this},has:function(e){return e?-1<S.inArray(e,s):0<s.length},empty:function(){return s&&(s=[]),this},disable:function(){return a=u=[],s=t="",this},disabled:function(){return!s},lock:function(){return a=u=[],t||i||(s=t=""),this},locked:function(){return!!a},fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),i||c()),this},fire:function(){return f.fireWith(this,arguments),this},fired:function(){return!!o}};return f},S.extend({Deferred:function(e){var o=[["notify","progress",S.Callbacks("memory"),S.Callbacks("memory"),2],["resolve","done",S.Callbacks("once memory"),S.Callbacks("once memory"),0,"resolved"],["reject","fail",S.Callbacks("once memory"),S.Callbacks("once memory"),1,"rejected"]],i="pending",a={state:function(){return i},always:function(){return s.done(arguments).fail(arguments),this},"catch":function(e){return a.then(null,e)},pipe:function(){var i=arguments;return S.Deferred(function(r){S.each(o,function(e,t){var n=m(i[t[4]])&&i[t[4]];s[t[1]](function(){var e=n&&n.apply(this,arguments);e&&m(e.promise)?e.promise().progress(r.notify).done(r.resolve).fail(r.reject):r[t[0]+"With"](this,n?[e]:arguments)})}),i=null}).promise()},then:function(t,n,r){var u=0;function l(i,o,a,s){return function(){var n=this,r=arguments,e=function(){var e,t;if(!(i<u)){if((e=a.apply(n,r))===o.promise())throw new TypeError("Thenable self-resolution");t=e&&("object"==typeof e||"function"==typeof e)&&e.then,m(t)?s?t.call(e,l(u,o,R,s),l(u,o,M,s)):(u++,t.call(e,l(u,o,R,s),l(u,o,M,s),l(u,o,R,o.notifyWith))):(a!==R&&(n=void 0,r=[e]),(s||o.resolveWith)(n,r))}},t=s?e:function(){try{e()}catch(e){S.Deferred.exceptionHook&&S.Deferred.exceptionHook(e,t.stackTrace),u<=i+1&&(a!==M&&(n=void 0,r=[e]),o.rejectWith(n,r))}};i?t():(S.Deferred.getStackHook&&(t.stackTrace=S.Deferred.getStackHook()),C.setTimeout(t))}}return S.Deferred(function(e){o[0][3].add(l(0,e,m(r)?r:R,e.notifyWith)),o[1][3].add(l(0,e,m(t)?t:R)),o[2][3].add(l(0,e,m(n)?n:M))}).promise()},promise:function(e){return null!=e?S.extend(e,a):a}},s={};return S.each(o,function(e,t){var n=t[2],r=t[5];a[t[1]]=n.add,r&&n.add(function(){i=r},o[3-e][2].disable,o[3-e][3].disable,o[0][2].lock,o[0][3].lock),n.add(t[3].fire),s[t[0]]=function(){return s[t[0]+"With"](this===s?void 0:this,arguments),this},s[t[0]+"With"]=n.fireWith}),a.promise(s),e&&e.call(s,s),s},when:function(e){var n=arguments.length,t=n,r=Array(t),i=s.call(arguments),o=S.Deferred(),a=function(t){return function(e){r[t]=this,i[t]=1<arguments.length?s.call(arguments):e,--n||o.resolveWith(r,i)}};if(n<=1&&(I(e,o.done(a(t)).resolve,o.reject,!n),"pending"===o.state()||m(i[t]&&i[t].then)))return o.then();while(t--)I(i[t],a(t),o.reject);return o.promise()}});var W=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;S.Deferred.exceptionHook=function(e,t){C.console&&C.console.warn&&e&&W.test(e.name)&&C.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},S.readyException=function(e){C.setTimeout(function(){throw e})};var F=S.Deferred();function B(){E.removeEventListener("DOMContentLoaded",B),C.removeEventListener("load",B),S.ready()}S.fn.ready=function(e){return F.then(e)["catch"](function(e){S.readyException(e)}),this},S.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--S.readyWait:S.isReady)||(S.isReady=!0)!==e&&0<--S.readyWait||F.resolveWith(E,[S])}}),S.ready.then=F.then,"complete"===E.readyState||"loading"!==E.readyState&&!E.documentElement.doScroll?C.setTimeout(S.ready):(E.addEventListener("DOMContentLoaded",B),C.addEventListener("load",B));var $=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===w(n))for(s in i=!0,n)$(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,m(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(S(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},_=/^-ms-/,z=/-([a-z])/g;function U(e,t){return t.toUpperCase()}function X(e){return e.replace(_,"ms-").replace(z,U)}var V=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function G(){this.expando=S.expando+G.uid++}G.uid=1,G.prototype={cache:function(e){var t=e[this.expando];return t||(t={},V(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[X(t)]=n;else for(r in t)i[X(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][X(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(X):(t=X(t))in r?[t]:t.match(P)||[]).length;while(n--)delete r[t[n]]}(void 0===t||S.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!S.isEmptyObject(t)}};var Y=new G,Q=new G,J=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,K=/[A-Z]/g;function Z(e,t,n){var r,i;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(K,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n="true"===(i=n)||"false"!==i&&("null"===i?null:i===+i+""?+i:J.test(i)?JSON.parse(i):i)}catch(e){}Q.set(e,t,n)}else n=void 0;return n}S.extend({hasData:function(e){return Q.hasData(e)||Y.hasData(e)},data:function(e,t,n){return Q.access(e,t,n)},removeData:function(e,t){Q.remove(e,t)},_data:function(e,t,n){return Y.access(e,t,n)},_removeData:function(e,t){Y.remove(e,t)}}),S.fn.extend({data:function(n,e){var t,r,i,o=this[0],a=o&&o.attributes;if(void 0===n){if(this.length&&(i=Q.get(o),1===o.nodeType&&!Y.get(o,"hasDataAttrs"))){t=a.length;while(t--)a[t]&&0===(r=a[t].name).indexOf("data-")&&(r=X(r.slice(5)),Z(o,r,i[r]));Y.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof n?this.each(function(){Q.set(this,n)}):$(this,function(e){var t;if(o&&void 0===e)return void 0!==(t=Q.get(o,n))?t:void 0!==(t=Z(o,n))?t:void 0;this.each(function(){Q.set(this,n,e)})},null,e,1<arguments.length,null,!0)},removeData:function(e){return this.each(function(){Q.remove(this,e)})}}),S.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=Y.get(e,t),n&&(!r||Array.isArray(n)?r=Y.access(e,t,S.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=S.queue(e,t),r=n.length,i=n.shift(),o=S._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,function(){S.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return Y.get(e,n)||Y.access(e,n,{empty:S.Callbacks("once memory").add(function(){Y.remove(e,[t+"queue",n])})})}}),S.fn.extend({queue:function(t,n){var e=2;return"string"!=typeof t&&(n=t,t="fx",e--),arguments.length<e?S.queue(this[0],t):void 0===n?this:this.each(function(){var e=S.queue(this,t,n);S._queueHooks(this,t),"fx"===t&&"inprogress"!==e[0]&&S.dequeue(this,t)})},dequeue:function(e){return this.each(function(){S.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=S.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=Y.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var ee=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,te=new RegExp("^(?:([+-])=|)("+ee+")([a-z%]*)$","i"),ne=["Top","Right","Bottom","Left"],re=E.documentElement,ie=function(e){return S.contains(e.ownerDocument,e)},oe={composed:!0};re.getRootNode&&(ie=function(e){return S.contains(e.ownerDocument,e)||e.getRootNode(oe)===e.ownerDocument});var ae=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&ie(e)&&"none"===S.css(e,"display")};function se(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return S.css(e,t,"")},u=s(),l=n&&n[3]||(S.cssNumber[t]?"":"px"),c=e.nodeType&&(S.cssNumber[t]||"px"!==l&&+u)&&te.exec(S.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)S.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,S.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ue={};function le(e,t){for(var n,r,i,o,a,s,u,l=[],c=0,f=e.length;c<f;c++)(r=e[c]).style&&(n=r.style.display,t?("none"===n&&(l[c]=Y.get(r,"display")||null,l[c]||(r.style.display="")),""===r.style.display&&ae(r)&&(l[c]=(u=a=o=void 0,a=(i=r).ownerDocument,s=i.nodeName,(u=ue[s])||(o=a.body.appendChild(a.createElement(s)),u=S.css(o,"display"),o.parentNode.removeChild(o),"none"===u&&(u="block"),ue[s]=u)))):"none"!==n&&(l[c]="none",Y.set(r,"display",n)));for(c=0;c<f;c++)null!=l[c]&&(e[c].style.display=l[c]);return e}S.fn.extend({show:function(){return le(this,!0)},hide:function(){return le(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ae(this)?S(this).show():S(this).hide()})}});var ce,fe,pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,he=/^$|^module$|\/(?:java|ecma)script/i;ce=E.createDocumentFragment().appendChild(E.createElement("div")),(fe=E.createElement("input")).setAttribute("type","radio"),fe.setAttribute("checked","checked"),fe.setAttribute("name","t"),ce.appendChild(fe),y.checkClone=ce.cloneNode(!0).cloneNode(!0).lastChild.checked,ce.innerHTML="<textarea>x</textarea>",y.noCloneChecked=!!ce.cloneNode(!0).lastChild.defaultValue,ce.innerHTML="<option></option>",y.option=!!ce.lastChild;var ge={thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function ve(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&A(e,t)?S.merge([e],n):n}function ye(e,t){for(var n=0,r=e.length;n<r;n++)Y.set(e[n],"globalEval",!t||Y.get(t[n],"globalEval"))}ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td,y.option||(ge.optgroup=ge.option=[1,"<select multiple='multiple'>","</select>"]);var me=/<|&#?\w+;/;function xe(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===w(o))S.merge(p,o.nodeType?[o]:o);else if(me.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+S.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;S.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&-1<S.inArray(o,r))i&&i.push(o);else if(l=ie(o),a=ve(f.appendChild(o),"script"),l&&ye(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}var be=/^key/,we=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,Te=/^([^.]*)(?:\.(.+)|)/;function Ce(){return!0}function Ee(){return!1}function Se(e,t){return e===function(){try{return E.activeElement}catch(e){}}()==("focus"===t)}function ke(e,t,n,r,i,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(r=r||n,n=void 0),t)ke(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=Ee;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return S().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=S.guid++)),e.each(function(){S.event.add(this,t,i,r,n)})}function Ae(e,i,o){o?(Y.set(e,i,!1),S.event.add(e,i,{namespace:!1,handler:function(e){var t,n,r=Y.get(this,i);if(1&e.isTrigger&&this[i]){if(r.length)(S.event.special[i]||{}).delegateType&&e.stopPropagation();else if(r=s.call(arguments),Y.set(this,i,r),t=o(this,i),this[i](),r!==(n=Y.get(this,i))||t?Y.set(this,i,!1):n={},r!==n)return e.stopImmediatePropagation(),e.preventDefault(),n.value}else r.length&&(Y.set(this,i,{value:S.event.trigger(S.extend(r[0],S.Event.prototype),r.slice(1),this)}),e.stopImmediatePropagation())}})):void 0===Y.get(e,i)&&S.event.add(e,i,Ce)}S.event={global:{},add:function(t,e,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Y.get(t);if(V(t)){n.handler&&(n=(o=n).handler,i=o.selector),i&&S.find.matchesSelector(re,i),n.guid||(n.guid=S.guid++),(u=v.events)||(u=v.events=Object.create(null)),(a=v.handle)||(a=v.handle=function(e){return"undefined"!=typeof S&&S.event.triggered!==e.type?S.event.dispatch.apply(t,arguments):void 0}),l=(e=(e||"").match(P)||[""]).length;while(l--)d=g=(s=Te.exec(e[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=S.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=S.event.special[d]||{},c=S.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&S.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(t,r,h,a)||t.addEventListener&&t.addEventListener(d,a)),f.add&&(f.add.call(t,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),S.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Y.hasData(e)&&Y.get(e);if(v&&(u=v.events)){l=(t=(t||"").match(P)||[""]).length;while(l--)if(d=g=(s=Te.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d){f=S.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,v.handle)||S.removeEvent(e,d,v.handle),delete u[d])}else for(d in u)S.event.remove(e,d+t[l],n,r,!0);S.isEmptyObject(u)&&Y.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=new Array(arguments.length),u=S.event.fix(e),l=(Y.get(this,"events")||Object.create(null))[u.type]||[],c=S.event.special[u.type]||{};for(s[0]=u,t=1;t<arguments.length;t++)s[t]=arguments[t];if(u.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,u)){a=S.event.handlers.call(this,u,l),t=0;while((i=a[t++])&&!u.isPropagationStopped()){u.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!u.isImmediatePropagationStopped())u.rnamespace&&!1!==o.namespace&&!u.rnamespace.test(o.namespace)||(u.handleObj=o,u.data=o.data,void 0!==(r=((S.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,s))&&!1===(u.result=r)&&(u.preventDefault(),u.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,u),u.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&1<=e.button))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?-1<S(i,this).index(l):S.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(t,e){Object.defineProperty(S.Event.prototype,t,{enumerable:!0,configurable:!0,get:m(e)?function(){if(this.originalEvent)return e(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[t]},set:function(e){Object.defineProperty(this,t,{enumerable:!0,configurable:!0,writable:!0,value:e})}})},fix:function(e){return e[S.expando]?e:new S.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Ae(t,"click",Ce),!1},trigger:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Ae(t,"click"),!0},_default:function(e){var t=e.target;return pe.test(t.type)&&t.click&&A(t,"input")&&Y.get(t,"click")||A(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},S.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},S.Event=function(e,t){if(!(this instanceof S.Event))return new S.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?Ce:Ee,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&S.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[S.expando]=!0},S.Event.prototype={constructor:S.Event,isDefaultPrevented:Ee,isPropagationStopped:Ee,isImmediatePropagationStopped:Ee,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Ce,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Ce,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Ce,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},S.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:function(e){var t=e.button;return null==e.which&&be.test(e.type)?null!=e.charCode?e.charCode:e.keyCode:!e.which&&void 0!==t&&we.test(e.type)?1&t?1:2&t?3:4&t?2:0:e.which}},S.event.addProp),S.each({focus:"focusin",blur:"focusout"},function(e,t){S.event.special[e]={setup:function(){return Ae(this,e,Se),!1},trigger:function(){return Ae(this,e),!0},delegateType:t}}),S.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,i){S.event.special[e]={delegateType:i,bindType:i,handle:function(e){var t,n=e.relatedTarget,r=e.handleObj;return n&&(n===this||S.contains(this,n))||(e.type=r.origType,t=r.handler.apply(this,arguments),e.type=i),t}}}),S.fn.extend({on:function(e,t,n,r){return ke(this,e,t,n,r)},one:function(e,t,n,r){return ke(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,S(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=Ee),this.each(function(){S.event.remove(this,e,n,t)})}});var Ne=/<script|<style|<link/i,De=/checked\s*(?:[^=]|=\s*.checked.)/i,je=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function qe(e,t){return A(e,"table")&&A(11!==t.nodeType?t:t.firstChild,"tr")&&S(e).children("tbody")[0]||e}function Le(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function He(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Oe(e,t){var n,r,i,o,a,s;if(1===t.nodeType){if(Y.hasData(e)&&(s=Y.get(e).events))for(i in Y.remove(t,"handle events"),s)for(n=0,r=s[i].length;n<r;n++)S.event.add(t,i,s[i][n]);Q.hasData(e)&&(o=Q.access(e),a=S.extend({},o),Q.set(t,a))}}function Pe(n,r,i,o){r=g(r);var e,t,a,s,u,l,c=0,f=n.length,p=f-1,d=r[0],h=m(d);if(h||1<f&&"string"==typeof d&&!y.checkClone&&De.test(d))return n.each(function(e){var t=n.eq(e);h&&(r[0]=d.call(this,e,t.html())),Pe(t,r,i,o)});if(f&&(t=(e=xe(r,n[0].ownerDocument,!1,n,o)).firstChild,1===e.childNodes.length&&(e=t),t||o)){for(s=(a=S.map(ve(e,"script"),Le)).length;c<f;c++)u=e,c!==p&&(u=S.clone(u,!0,!0),s&&S.merge(a,ve(u,"script"))),i.call(n[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,S.map(a,He),c=0;c<s;c++)u=a[c],he.test(u.type||"")&&!Y.access(u,"globalEval")&&S.contains(l,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?S._evalUrl&&!u.noModule&&S._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")},l):b(u.textContent.replace(je,""),u,l))}return n}function Re(e,t,n){for(var r,i=t?S.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||S.cleanData(ve(r)),r.parentNode&&(n&&ie(r)&&ye(ve(r,"script")),r.parentNode.removeChild(r));return e}S.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,o,a,s,u,l,c=e.cloneNode(!0),f=ie(e);if(!(y.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||S.isXMLDoc(e)))for(a=ve(c),r=0,i=(o=ve(e)).length;r<i;r++)s=o[r],u=a[r],void 0,"input"===(l=u.nodeName.toLowerCase())&&pe.test(s.type)?u.checked=s.checked:"input"!==l&&"textarea"!==l||(u.defaultValue=s.defaultValue);if(t)if(n)for(o=o||ve(e),a=a||ve(c),r=0,i=o.length;r<i;r++)Oe(o[r],a[r]);else Oe(e,c);return 0<(a=ve(c,"script")).length&&ye(a,!f&&ve(e,"script")),c},cleanData:function(e){for(var t,n,r,i=S.event.special,o=0;void 0!==(n=e[o]);o++)if(V(n)){if(t=n[Y.expando]){if(t.events)for(r in t.events)i[r]?S.event.remove(n,r):S.removeEvent(n,r,t.handle);n[Y.expando]=void 0}n[Q.expando]&&(n[Q.expando]=void 0)}}}),S.fn.extend({detach:function(e){return Re(this,e,!0)},remove:function(e){return Re(this,e)},text:function(e){return $(this,function(e){return void 0===e?S.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return Pe(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||qe(this,e).appendChild(e)})},prepend:function(){return Pe(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=qe(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return Pe(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return Pe(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(S.cleanData(ve(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return S.clone(this,e,t)})},html:function(e){return $(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!Ne.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=S.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(S.cleanData(ve(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var n=[];return Pe(this,arguments,function(e){var t=this.parentNode;S.inArray(this,n)<0&&(S.cleanData(ve(this)),t&&t.replaceChild(e,this))},n)}}),S.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,a){S.fn[e]=function(e){for(var t,n=[],r=S(e),i=r.length-1,o=0;o<=i;o++)t=o===i?this:this.clone(!0),S(r[o])[a](t),u.apply(n,t.get());return this.pushStack(n)}});var Me=new RegExp("^("+ee+")(?!px)[a-z%]+$","i"),Ie=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=C),t.getComputedStyle(e)},We=function(e,t,n){var r,i,o={};for(i in t)o[i]=e.style[i],e.style[i]=t[i];for(i in r=n.call(e),t)e.style[i]=o[i];return r},Fe=new RegExp(ne.join("|"),"i");function Be(e,t,n){var r,i,o,a,s=e.style;return(n=n||Ie(e))&&(""!==(a=n.getPropertyValue(t)||n[t])||ie(e)||(a=S.style(e,t)),!y.pixelBoxStyles()&&Me.test(a)&&Fe.test(t)&&(r=s.width,i=s.minWidth,o=s.maxWidth,s.minWidth=s.maxWidth=s.width=a,a=n.width,s.width=r,s.minWidth=i,s.maxWidth=o)),void 0!==a?a+"":a}function $e(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(l){u.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",l.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",re.appendChild(u).appendChild(l);var e=C.getComputedStyle(l);n="1%"!==e.top,s=12===t(e.marginLeft),l.style.right="60%",o=36===t(e.right),r=36===t(e.width),l.style.position="absolute",i=12===t(l.offsetWidth/3),re.removeChild(u),l=null}}function t(e){return Math.round(parseFloat(e))}var n,r,i,o,a,s,u=E.createElement("div"),l=E.createElement("div");l.style&&(l.style.backgroundClip="content-box",l.cloneNode(!0).style.backgroundClip="",y.clearCloneStyle="content-box"===l.style.backgroundClip,S.extend(y,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),o},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),s},scrollboxSize:function(){return e(),i},reliableTrDimensions:function(){var e,t,n,r;return null==a&&(e=E.createElement("table"),t=E.createElement("tr"),n=E.createElement("div"),e.style.cssText="position:absolute;left:-11111px",t.style.height="1px",n.style.height="9px",re.appendChild(e).appendChild(t).appendChild(n),r=C.getComputedStyle(t),a=3<parseInt(r.height),re.removeChild(e)),a}}))}();var _e=["Webkit","Moz","ms"],ze=E.createElement("div").style,Ue={};function Xe(e){var t=S.cssProps[e]||Ue[e];return t||(e in ze?e:Ue[e]=function(e){var t=e[0].toUpperCase()+e.slice(1),n=_e.length;while(n--)if((e=_e[n]+t)in ze)return e}(e)||e)}var Ve=/^(none|table(?!-c[ea]).+)/,Ge=/^--/,Ye={position:"absolute",visibility:"hidden",display:"block"},Qe={letterSpacing:"0",fontWeight:"400"};function Je(e,t,n){var r=te.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function Ke(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=S.css(e,n+ne[a],!0,i)),r?("content"===n&&(u-=S.css(e,"padding"+ne[a],!0,i)),"margin"!==n&&(u-=S.css(e,"border"+ne[a]+"Width",!0,i))):(u+=S.css(e,"padding"+ne[a],!0,i),"padding"!==n?u+=S.css(e,"border"+ne[a]+"Width",!0,i):s+=S.css(e,"border"+ne[a]+"Width",!0,i));return!r&&0<=o&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u}function Ze(e,t,n){var r=Ie(e),i=(!y.boxSizingReliable()||n)&&"border-box"===S.css(e,"boxSizing",!1,r),o=i,a=Be(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);if(Me.test(a)){if(!n)return a;a="auto"}return(!y.boxSizingReliable()&&i||!y.reliableTrDimensions()&&A(e,"tr")||"auto"===a||!parseFloat(a)&&"inline"===S.css(e,"display",!1,r))&&e.getClientRects().length&&(i="border-box"===S.css(e,"boxSizing",!1,r),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+Ke(e,t,n||(i?"border":"content"),o,r,a)+"px"}function et(e,t,n,r,i){return new et.prototype.init(e,t,n,r,i)}S.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Be(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=X(t),u=Ge.test(t),l=e.style;if(u||(t=Xe(s)),a=S.cssHooks[t]||S.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"===(o=typeof n)&&(i=te.exec(n))&&i[1]&&(n=se(e,t,i),o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(S.cssNumber[s]?"":"px")),y.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=X(t);return Ge.test(t)||(t=Xe(s)),(a=S.cssHooks[t]||S.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=Be(e,t,r)),"normal"===i&&t in Qe&&(i=Qe[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),S.each(["height","width"],function(e,u){S.cssHooks[u]={get:function(e,t,n){if(t)return!Ve.test(S.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?Ze(e,u,n):We(e,Ye,function(){return Ze(e,u,n)})},set:function(e,t,n){var r,i=Ie(e),o=!y.scrollboxSize()&&"absolute"===i.position,a=(o||n)&&"border-box"===S.css(e,"boxSizing",!1,i),s=n?Ke(e,u,n,a,i):0;return a&&o&&(s-=Math.ceil(e["offset"+u[0].toUpperCase()+u.slice(1)]-parseFloat(i[u])-Ke(e,u,"border",!1,i)-.5)),s&&(r=te.exec(t))&&"px"!==(r[3]||"px")&&(e.style[u]=t,t=S.css(e,u)),Je(0,t,s)}}}),S.cssHooks.marginLeft=$e(y.reliableMarginLeft,function(e,t){if(t)return(parseFloat(Be(e,"marginLeft"))||e.getBoundingClientRect().left-We(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),S.each({margin:"",padding:"",border:"Width"},function(i,o){S.cssHooks[i+o]={expand:function(e){for(var t=0,n={},r="string"==typeof e?e.split(" "):[e];t<4;t++)n[i+ne[t]+o]=r[t]||r[t-2]||r[0];return n}},"margin"!==i&&(S.cssHooks[i+o].set=Je)}),S.fn.extend({css:function(e,t){return $(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=Ie(e),i=t.length;a<i;a++)o[t[a]]=S.css(e,t[a],!1,r);return o}return void 0!==n?S.style(e,t,n):S.css(e,t)},e,t,1<arguments.length)}}),((S.Tween=et).prototype={constructor:et,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||S.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(S.cssNumber[n]?"":"px")},cur:function(){var e=et.propHooks[this.prop];return e&&e.get?e.get(this):et.propHooks._default.get(this)},run:function(e){var t,n=et.propHooks[this.prop];return this.options.duration?this.pos=t=S.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):et.propHooks._default.set(this),this}}).init.prototype=et.prototype,(et.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=S.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){S.fx.step[e.prop]?S.fx.step[e.prop](e):1!==e.elem.nodeType||!S.cssHooks[e.prop]&&null==e.elem.style[Xe(e.prop)]?e.elem[e.prop]=e.now:S.style(e.elem,e.prop,e.now+e.unit)}}}).scrollTop=et.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},S.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},S.fx=et.prototype.init,S.fx.step={};var tt,nt,rt,it,ot=/^(?:toggle|show|hide)$/,at=/queueHooks$/;function st(){nt&&(!1===E.hidden&&C.requestAnimationFrame?C.requestAnimationFrame(st):C.setTimeout(st,S.fx.interval),S.fx.tick())}function ut(){return C.setTimeout(function(){tt=void 0}),tt=Date.now()}function lt(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=ne[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function ct(e,t,n){for(var r,i=(ft.tweeners[t]||[]).concat(ft.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function ft(o,e,t){var n,a,r=0,i=ft.prefilters.length,s=S.Deferred().always(function(){delete u.elem}),u=function(){if(a)return!1;for(var e=tt||ut(),t=Math.max(0,l.startTime+l.duration-e),n=1-(t/l.duration||0),r=0,i=l.tweens.length;r<i;r++)l.tweens[r].run(n);return s.notifyWith(o,[l,n,t]),n<1&&i?t:(i||s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l]),!1)},l=s.promise({elem:o,props:S.extend({},e),opts:S.extend(!0,{specialEasing:{},easing:S.easing._default},t),originalProperties:e,originalOptions:t,startTime:tt||ut(),duration:t.duration,tweens:[],createTween:function(e,t){var n=S.Tween(o,l.opts,e,t,l.opts.specialEasing[e]||l.opts.easing);return l.tweens.push(n),n},stop:function(e){var t=0,n=e?l.tweens.length:0;if(a)return this;for(a=!0;t<n;t++)l.tweens[t].run(1);return e?(s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l,e])):s.rejectWith(o,[l,e]),this}}),c=l.props;for(!function(e,t){var n,r,i,o,a;for(n in e)if(i=t[r=X(n)],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=S.cssHooks[r])&&"expand"in a)for(n in o=a.expand(o),delete e[r],o)n in e||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);r<i;r++)if(n=ft.prefilters[r].call(l,o,c,l.opts))return m(n.stop)&&(S._queueHooks(l.elem,l.opts.queue).stop=n.stop.bind(n)),n;return S.map(c,ct,l),m(l.opts.start)&&l.opts.start.call(o,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),S.fx.timer(S.extend(u,{elem:o,anim:l,queue:l.opts.queue})),l}S.Animation=S.extend(ft,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return se(n.elem,e,te.exec(t),n),n}]},tweener:function(e,t){m(e)?(t=e,e=["*"]):e=e.match(P);for(var n,r=0,i=e.length;r<i;r++)n=e[r],ft.tweeners[n]=ft.tweeners[n]||[],ft.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ae(e),v=Y.get(e,"fxshow");for(r in n.queue||(null==(a=S._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,S.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],ot.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!v||void 0===v[r])continue;g=!0}d[r]=v&&v[r]||S.style(e,r)}if((u=!S.isEmptyObject(t))||!S.isEmptyObject(d))for(r in f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=v&&v.display)&&(l=Y.get(e,"display")),"none"===(c=S.css(e,"display"))&&(l?c=l:(le([e],!0),l=e.style.display||l,c=S.css(e,"display"),le([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===S.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1,d)u||(v?"hidden"in v&&(g=v.hidden):v=Y.access(e,"fxshow",{display:l}),o&&(v.hidden=!g),g&&le([e],!0),p.done(function(){for(r in g||le([e]),Y.remove(e,"fxshow"),d)S.style(e,r,d[r])})),u=ct(g?v[r]:0,r,p),r in v||(v[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?ft.prefilters.unshift(e):ft.prefilters.push(e)}}),S.speed=function(e,t,n){var r=e&&"object"==typeof e?S.extend({},e):{complete:n||!n&&t||m(e)&&e,duration:e,easing:n&&t||t&&!m(t)&&t};return S.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in S.fx.speeds?r.duration=S.fx.speeds[r.duration]:r.duration=S.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){m(r.old)&&r.old.call(this),r.queue&&S.dequeue(this,r.queue)},r},S.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ae).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(t,e,n,r){var i=S.isEmptyObject(t),o=S.speed(e,n,r),a=function(){var e=ft(this,S.extend({},t),o);(i||Y.get(this,"finish"))&&e.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(i,e,o){var a=function(e){var t=e.stop;delete e.stop,t(o)};return"string"!=typeof i&&(o=e,e=i,i=void 0),e&&this.queue(i||"fx",[]),this.each(function(){var e=!0,t=null!=i&&i+"queueHooks",n=S.timers,r=Y.get(this);if(t)r[t]&&r[t].stop&&a(r[t]);else for(t in r)r[t]&&r[t].stop&&at.test(t)&&a(r[t]);for(t=n.length;t--;)n[t].elem!==this||null!=i&&n[t].queue!==i||(n[t].anim.stop(o),e=!1,n.splice(t,1));!e&&o||S.dequeue(this,i)})},finish:function(a){return!1!==a&&(a=a||"fx"),this.each(function(){var e,t=Y.get(this),n=t[a+"queue"],r=t[a+"queueHooks"],i=S.timers,o=n?n.length:0;for(t.finish=!0,S.queue(this,a,[]),r&&r.stop&&r.stop.call(this,!0),e=i.length;e--;)i[e].elem===this&&i[e].queue===a&&(i[e].anim.stop(!0),i.splice(e,1));for(e=0;e<o;e++)n[e]&&n[e].finish&&n[e].finish.call(this);delete t.finish})}}),S.each(["toggle","show","hide"],function(e,r){var i=S.fn[r];S.fn[r]=function(e,t,n){return null==e||"boolean"==typeof e?i.apply(this,arguments):this.animate(lt(r,!0),e,t,n)}}),S.each({slideDown:lt("show"),slideUp:lt("hide"),slideToggle:lt("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,r){S.fn[e]=function(e,t,n){return this.animate(r,e,t,n)}}),S.timers=[],S.fx.tick=function(){var e,t=0,n=S.timers;for(tt=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||S.fx.stop(),tt=void 0},S.fx.timer=function(e){S.timers.push(e),S.fx.start()},S.fx.interval=13,S.fx.start=function(){nt||(nt=!0,st())},S.fx.stop=function(){nt=null},S.fx.speeds={slow:600,fast:200,_default:400},S.fn.delay=function(r,e){return r=S.fx&&S.fx.speeds[r]||r,e=e||"fx",this.queue(e,function(e,t){var n=C.setTimeout(e,r);t.stop=function(){C.clearTimeout(n)}})},rt=E.createElement("input"),it=E.createElement("select").appendChild(E.createElement("option")),rt.type="checkbox",y.checkOn=""!==rt.value,y.optSelected=it.selected,(rt=E.createElement("input")).value="t",rt.type="radio",y.radioValue="t"===rt.value;var pt,dt=S.expr.attrHandle;S.fn.extend({attr:function(e,t){return $(this,S.attr,e,t,1<arguments.length)},removeAttr:function(e){return this.each(function(){S.removeAttr(this,e)})}}),S.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?S.prop(e,t,n):(1===o&&S.isXMLDoc(e)||(i=S.attrHooks[t.toLowerCase()]||(S.expr.match.bool.test(t)?pt:void 0)),void 0!==n?null===n?void S.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=S.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!y.radioValue&&"radio"===t&&A(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(P);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),pt={set:function(e,t,n){return!1===t?S.removeAttr(e,n):e.setAttribute(n,n),n}},S.each(S.expr.match.bool.source.match(/\w+/g),function(e,t){var a=dt[t]||S.find.attr;dt[t]=function(e,t,n){var r,i,o=t.toLowerCase();return n||(i=dt[o],dt[o]=r,r=null!=a(e,t,n)?o:null,dt[o]=i),r}});var ht=/^(?:input|select|textarea|button)$/i,gt=/^(?:a|area)$/i;function vt(e){return(e.match(P)||[]).join(" ")}function yt(e){return e.getAttribute&&e.getAttribute("class")||""}function mt(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(P)||[]}S.fn.extend({prop:function(e,t){return $(this,S.prop,e,t,1<arguments.length)},removeProp:function(e){return this.each(function(){delete this[S.propFix[e]||e]})}}),S.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&S.isXMLDoc(e)||(t=S.propFix[t]||t,i=S.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=S.find.attr(e,"tabindex");return t?parseInt(t,10):ht.test(e.nodeName)||gt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),y.optSelected||(S.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),S.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){S.propFix[this.toLowerCase()]=this}),S.fn.extend({addClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){S(this).addClass(t.call(this,e,yt(this)))});if((e=mt(t)).length)while(n=this[u++])if(i=yt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=e[a++])r.indexOf(" "+o+" ")<0&&(r+=o+" ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},removeClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){S(this).removeClass(t.call(this,e,yt(this)))});if(!arguments.length)return this.attr("class","");if((e=mt(t)).length)while(n=this[u++])if(i=yt(n),r=1===n.nodeType&&" "+vt(i)+" "){a=0;while(o=e[a++])while(-1<r.indexOf(" "+o+" "))r=r.replace(" "+o+" "," ");i!==(s=vt(r))&&n.setAttribute("class",s)}return this},toggleClass:function(i,t){var o=typeof i,a="string"===o||Array.isArray(i);return"boolean"==typeof t&&a?t?this.addClass(i):this.removeClass(i):m(i)?this.each(function(e){S(this).toggleClass(i.call(this,e,yt(this),t),t)}):this.each(function(){var e,t,n,r;if(a){t=0,n=S(this),r=mt(i);while(e=r[t++])n.hasClass(e)?n.removeClass(e):n.addClass(e)}else void 0!==i&&"boolean"!==o||((e=yt(this))&&Y.set(this,"__className__",e),this.setAttribute&&this.setAttribute("class",e||!1===i?"":Y.get(this,"__className__")||""))})},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&-1<(" "+vt(yt(n))+" ").indexOf(t))return!0;return!1}});var xt=/\r/g;S.fn.extend({val:function(n){var r,e,i,t=this[0];return arguments.length?(i=m(n),this.each(function(e){var t;1===this.nodeType&&(null==(t=i?n.call(this,e,S(this).val()):n)?t="":"number"==typeof t?t+="":Array.isArray(t)&&(t=S.map(t,function(e){return null==e?"":e+""})),(r=S.valHooks[this.type]||S.valHooks[this.nodeName.toLowerCase()])&&"set"in r&&void 0!==r.set(this,t,"value")||(this.value=t))})):t?(r=S.valHooks[t.type]||S.valHooks[t.nodeName.toLowerCase()])&&"get"in r&&void 0!==(e=r.get(t,"value"))?e:"string"==typeof(e=t.value)?e.replace(xt,""):null==e?"":e:void 0}}),S.extend({valHooks:{option:{get:function(e){var t=S.find.attr(e,"value");return null!=t?t:vt(S.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!A(n.parentNode,"optgroup"))){if(t=S(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=S.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=-1<S.inArray(S.valHooks.option.get(r),o))&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),S.each(["radio","checkbox"],function(){S.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=-1<S.inArray(S(e).val(),t)}},y.checkOn||(S.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),y.focusin="onfocusin"in C;var bt=/^(?:focusinfocus|focusoutblur)$/,wt=function(e){e.stopPropagation()};S.extend(S.event,{trigger:function(e,t,n,r){var i,o,a,s,u,l,c,f,p=[n||E],d=v.call(e,"type")?e.type:e,h=v.call(e,"namespace")?e.namespace.split("."):[];if(o=f=a=n=n||E,3!==n.nodeType&&8!==n.nodeType&&!bt.test(d+S.event.triggered)&&(-1<d.indexOf(".")&&(d=(h=d.split(".")).shift(),h.sort()),u=d.indexOf(":")<0&&"on"+d,(e=e[S.expando]?e:new S.Event(d,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=h.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:S.makeArray(t,[e]),c=S.event.special[d]||{},r||!c.trigger||!1!==c.trigger.apply(n,t))){if(!r&&!c.noBubble&&!x(n)){for(s=c.delegateType||d,bt.test(s+d)||(o=o.parentNode);o;o=o.parentNode)p.push(o),a=o;a===(n.ownerDocument||E)&&p.push(a.defaultView||a.parentWindow||C)}i=0;while((o=p[i++])&&!e.isPropagationStopped())f=o,e.type=1<i?s:c.bindType||d,(l=(Y.get(o,"events")||Object.create(null))[e.type]&&Y.get(o,"handle"))&&l.apply(o,t),(l=u&&o[u])&&l.apply&&V(o)&&(e.result=l.apply(o,t),!1===e.result&&e.preventDefault());return e.type=d,r||e.isDefaultPrevented()||c._default&&!1!==c._default.apply(p.pop(),t)||!V(n)||u&&m(n[d])&&!x(n)&&((a=n[u])&&(n[u]=null),S.event.triggered=d,e.isPropagationStopped()&&f.addEventListener(d,wt),n[d](),e.isPropagationStopped()&&f.removeEventListener(d,wt),S.event.triggered=void 0,a&&(n[u]=a)),e.result}},simulate:function(e,t,n){var r=S.extend(new S.Event,n,{type:e,isSimulated:!0});S.event.trigger(r,null,t)}}),S.fn.extend({trigger:function(e,t){return this.each(function(){S.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return S.event.trigger(e,t,n,!0)}}),y.focusin||S.each({focus:"focusin",blur:"focusout"},function(n,r){var i=function(e){S.event.simulate(r,e.target,S.event.fix(e))};S.event.special[r]={setup:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r);t||e.addEventListener(n,i,!0),Y.access(e,r,(t||0)+1)},teardown:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r)-1;t?Y.access(e,r,t):(e.removeEventListener(n,i,!0),Y.remove(e,r))}}});var Tt=C.location,Ct={guid:Date.now()},Et=/\?/;S.parseXML=function(e){var t;if(!e||"string"!=typeof e)return null;try{t=(new C.DOMParser).parseFromString(e,"text/xml")}catch(e){t=void 0}return t&&!t.getElementsByTagName("parsererror").length||S.error("Invalid XML: "+e),t};var St=/\[\]$/,kt=/\r?\n/g,At=/^(?:submit|button|image|reset|file)$/i,Nt=/^(?:input|select|textarea|keygen)/i;function Dt(n,e,r,i){var t;if(Array.isArray(e))S.each(e,function(e,t){r||St.test(n)?i(n,t):Dt(n+"["+("object"==typeof t&&null!=t?e:"")+"]",t,r,i)});else if(r||"object"!==w(e))i(n,e);else for(t in e)Dt(n+"["+t+"]",e[t],r,i)}S.param=function(e,t){var n,r=[],i=function(e,t){var n=m(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!S.isPlainObject(e))S.each(e,function(){i(this.name,this.value)});else for(n in e)Dt(n,e[n],t,i);return r.join("&")},S.fn.extend({serialize:function(){return S.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=S.prop(this,"elements");return e?S.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!S(this).is(":disabled")&&Nt.test(this.nodeName)&&!At.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=S(this).val();return null==n?null:Array.isArray(n)?S.map(n,function(e){return{name:t.name,value:e.replace(kt,"\r\n")}}):{name:t.name,value:n.replace(kt,"\r\n")}}).get()}});var jt=/%20/g,qt=/#.*$/,Lt=/([?&])_=[^&]*/,Ht=/^(.*?):[ \t]*([^\r\n]*)$/gm,Ot=/^(?:GET|HEAD)$/,Pt=/^\/\//,Rt={},Mt={},It="*/".concat("*"),Wt=E.createElement("a");function Ft(o){return function(e,t){"string"!=typeof e&&(t=e,e="*");var n,r=0,i=e.toLowerCase().match(P)||[];if(m(t))while(n=i[r++])"+"===n[0]?(n=n.slice(1)||"*",(o[n]=o[n]||[]).unshift(t)):(o[n]=o[n]||[]).push(t)}}function Bt(t,i,o,a){var s={},u=t===Mt;function l(e){var r;return s[e]=!0,S.each(t[e]||[],function(e,t){var n=t(i,o,a);return"string"!=typeof n||u||s[n]?u?!(r=n):void 0:(i.dataTypes.unshift(n),l(n),!1)}),r}return l(i.dataTypes[0])||!s["*"]&&l("*")}function $t(e,t){var n,r,i=S.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&S.extend(!0,e,r),e}Wt.href=Tt.href,S.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Tt.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Tt.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":It,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":S.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?$t($t(e,S.ajaxSettings),t):$t(S.ajaxSettings,e)},ajaxPrefilter:Ft(Rt),ajaxTransport:Ft(Mt),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var c,f,p,n,d,r,h,g,i,o,v=S.ajaxSetup({},t),y=v.context||v,m=v.context&&(y.nodeType||y.jquery)?S(y):S.event,x=S.Deferred(),b=S.Callbacks("once memory"),w=v.statusCode||{},a={},s={},u="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(h){if(!n){n={};while(t=Ht.exec(p))n[t[1].toLowerCase()+" "]=(n[t[1].toLowerCase()+" "]||[]).concat(t[2])}t=n[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return h?p:null},setRequestHeader:function(e,t){return null==h&&(e=s[e.toLowerCase()]=s[e.toLowerCase()]||e,a[e]=t),this},overrideMimeType:function(e){return null==h&&(v.mimeType=e),this},statusCode:function(e){var t;if(e)if(h)T.always(e[T.status]);else for(t in e)w[t]=[w[t],e[t]];return this},abort:function(e){var t=e||u;return c&&c.abort(t),l(0,t),this}};if(x.promise(T),v.url=((e||v.url||Tt.href)+"").replace(Pt,Tt.protocol+"//"),v.type=t.method||t.type||v.method||v.type,v.dataTypes=(v.dataType||"*").toLowerCase().match(P)||[""],null==v.crossDomain){r=E.createElement("a");try{r.href=v.url,r.href=r.href,v.crossDomain=Wt.protocol+"//"+Wt.host!=r.protocol+"//"+r.host}catch(e){v.crossDomain=!0}}if(v.data&&v.processData&&"string"!=typeof v.data&&(v.data=S.param(v.data,v.traditional)),Bt(Rt,v,t,T),h)return T;for(i in(g=S.event&&v.global)&&0==S.active++&&S.event.trigger("ajaxStart"),v.type=v.type.toUpperCase(),v.hasContent=!Ot.test(v.type),f=v.url.replace(qt,""),v.hasContent?v.data&&v.processData&&0===(v.contentType||"").indexOf("application/x-www-form-urlencoded")&&(v.data=v.data.replace(jt,"+")):(o=v.url.slice(f.length),v.data&&(v.processData||"string"==typeof v.data)&&(f+=(Et.test(f)?"&":"?")+v.data,delete v.data),!1===v.cache&&(f=f.replace(Lt,"$1"),o=(Et.test(f)?"&":"?")+"_="+Ct.guid+++o),v.url=f+o),v.ifModified&&(S.lastModified[f]&&T.setRequestHeader("If-Modified-Since",S.lastModified[f]),S.etag[f]&&T.setRequestHeader("If-None-Match",S.etag[f])),(v.data&&v.hasContent&&!1!==v.contentType||t.contentType)&&T.setRequestHeader("Content-Type",v.contentType),T.setRequestHeader("Accept",v.dataTypes[0]&&v.accepts[v.dataTypes[0]]?v.accepts[v.dataTypes[0]]+("*"!==v.dataTypes[0]?", "+It+"; q=0.01":""):v.accepts["*"]),v.headers)T.setRequestHeader(i,v.headers[i]);if(v.beforeSend&&(!1===v.beforeSend.call(y,T,v)||h))return T.abort();if(u="abort",b.add(v.complete),T.done(v.success),T.fail(v.error),c=Bt(Mt,v,t,T)){if(T.readyState=1,g&&m.trigger("ajaxSend",[T,v]),h)return T;v.async&&0<v.timeout&&(d=C.setTimeout(function(){T.abort("timeout")},v.timeout));try{h=!1,c.send(a,l)}catch(e){if(h)throw e;l(-1,e)}}else l(-1,"No Transport");function l(e,t,n,r){var i,o,a,s,u,l=t;h||(h=!0,d&&C.clearTimeout(d),c=void 0,p=r||"",T.readyState=0<e?4:0,i=200<=e&&e<300||304===e,n&&(s=function(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}(v,T,n)),!i&&-1<S.inArray("script",v.dataTypes)&&(v.converters["text script"]=function(){}),s=function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}(v,s,T,i),i?(v.ifModified&&((u=T.getResponseHeader("Last-Modified"))&&(S.lastModified[f]=u),(u=T.getResponseHeader("etag"))&&(S.etag[f]=u)),204===e||"HEAD"===v.type?l="nocontent":304===e?l="notmodified":(l=s.state,o=s.data,i=!(a=s.error))):(a=l,!e&&l||(l="error",e<0&&(e=0))),T.status=e,T.statusText=(t||l)+"",i?x.resolveWith(y,[o,l,T]):x.rejectWith(y,[T,l,a]),T.statusCode(w),w=void 0,g&&m.trigger(i?"ajaxSuccess":"ajaxError",[T,v,i?o:a]),b.fireWith(y,[T,l]),g&&(m.trigger("ajaxComplete",[T,v]),--S.active||S.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return S.get(e,t,n,"json")},getScript:function(e,t){return S.get(e,void 0,t,"script")}}),S.each(["get","post"],function(e,i){S[i]=function(e,t,n,r){return m(t)&&(r=r||n,n=t,t=void 0),S.ajax(S.extend({url:e,type:i,dataType:r,data:t,success:n},S.isPlainObject(e)&&e))}}),S.ajaxPrefilter(function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")}),S._evalUrl=function(e,t,n){return S.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){S.globalEval(e,t,n)}})},S.fn.extend({wrapAll:function(e){var t;return this[0]&&(m(e)&&(e=e.call(this[0])),t=S(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(n){return m(n)?this.each(function(e){S(this).wrapInner(n.call(this,e))}):this.each(function(){var e=S(this),t=e.contents();t.length?t.wrapAll(n):e.append(n)})},wrap:function(t){var n=m(t);return this.each(function(e){S(this).wrapAll(n?t.call(this,e):t)})},unwrap:function(e){return this.parent(e).not("body").each(function(){S(this).replaceWith(this.childNodes)}),this}}),S.expr.pseudos.hidden=function(e){return!S.expr.pseudos.visible(e)},S.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},S.ajaxSettings.xhr=function(){try{return new C.XMLHttpRequest}catch(e){}};var _t={0:200,1223:204},zt=S.ajaxSettings.xhr();y.cors=!!zt&&"withCredentials"in zt,y.ajax=zt=!!zt,S.ajaxTransport(function(i){var o,a;if(y.cors||zt&&!i.crossDomain)return{send:function(e,t){var n,r=i.xhr();if(r.open(i.type,i.url,i.async,i.username,i.password),i.xhrFields)for(n in i.xhrFields)r[n]=i.xhrFields[n];for(n in i.mimeType&&r.overrideMimeType&&r.overrideMimeType(i.mimeType),i.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest"),e)r.setRequestHeader(n,e[n]);o=function(e){return function(){o&&(o=a=r.onload=r.onerror=r.onabort=r.ontimeout=r.onreadystatechange=null,"abort"===e?r.abort():"error"===e?"number"!=typeof r.status?t(0,"error"):t(r.status,r.statusText):t(_t[r.status]||r.status,r.statusText,"text"!==(r.responseType||"text")||"string"!=typeof r.responseText?{binary:r.response}:{text:r.responseText},r.getAllResponseHeaders()))}},r.onload=o(),a=r.onerror=r.ontimeout=o("error"),void 0!==r.onabort?r.onabort=a:r.onreadystatechange=function(){4===r.readyState&&C.setTimeout(function(){o&&a()})},o=o("abort");try{r.send(i.hasContent&&i.data||null)}catch(e){if(o)throw e}},abort:function(){o&&o()}}}),S.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),S.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return S.globalEval(e),e}}}),S.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),S.ajaxTransport("script",function(n){var r,i;if(n.crossDomain||n.scriptAttrs)return{send:function(e,t){r=S("<script>").attr(n.scriptAttrs||{}).prop({charset:n.scriptCharset,src:n.url}).on("load error",i=function(e){r.remove(),i=null,e&&t("error"===e.type?404:200,e.type)}),E.head.appendChild(r[0])},abort:function(){i&&i()}}});var Ut,Xt=[],Vt=/(=)\?(?=&|$)|\?\?/;S.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Xt.pop()||S.expando+"_"+Ct.guid++;return this[e]=!0,e}}),S.ajaxPrefilter("json jsonp",function(e,t,n){var r,i,o,a=!1!==e.jsonp&&(Vt.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&Vt.test(e.data)&&"data");if(a||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=m(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,a?e[a]=e[a].replace(Vt,"$1"+r):!1!==e.jsonp&&(e.url+=(Et.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return o||S.error(r+" was not called"),o[0]},e.dataTypes[0]="json",i=C[r],C[r]=function(){o=arguments},n.always(function(){void 0===i?S(C).removeProp(r):C[r]=i,e[r]&&(e.jsonpCallback=t.jsonpCallback,Xt.push(r)),o&&m(i)&&i(o[0]),o=i=void 0}),"script"}),y.createHTMLDocument=((Ut=E.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===Ut.childNodes.length),S.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(y.createHTMLDocument?((r=(t=E.implementation.createHTMLDocument("")).createElement("base")).href=E.location.href,t.head.appendChild(r)):t=E),o=!n&&[],(i=N.exec(e))?[t.createElement(i[1])]:(i=xe([e],t,o),o&&o.length&&S(o).remove(),S.merge([],i.childNodes)));var r,i,o},S.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return-1<s&&(r=vt(e.slice(s)),e=e.slice(0,s)),m(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),0<a.length&&S.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?S("<div>").append(S.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},S.expr.pseudos.animated=function(t){return S.grep(S.timers,function(e){return t===e.elem}).length},S.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=S.css(e,"position"),c=S(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=S.css(e,"top"),u=S.css(e,"left"),("absolute"===l||"fixed"===l)&&-1<(o+u).indexOf("auto")?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),m(t)&&(t=t.call(e,n,S.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):("number"==typeof f.top&&(f.top+="px"),"number"==typeof f.left&&(f.left+="px"),c.css(f))}},S.fn.extend({offset:function(t){if(arguments.length)return void 0===t?this:this.each(function(e){S.offset.setOffset(this,t,e)});var e,n,r=this[0];return r?r.getClientRects().length?(e=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:e.top+n.pageYOffset,left:e.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===S.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===S.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=S(e).offset()).top+=S.css(e,"borderTopWidth",!0),i.left+=S.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-S.css(r,"marginTop",!0),left:t.left-i.left-S.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===S.css(e,"position"))e=e.offsetParent;return e||re})}}),S.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,i){var o="pageYOffset"===i;S.fn[t]=function(e){return $(this,function(e,t,n){var r;if(x(e)?r=e:9===e.nodeType&&(r=e.defaultView),void 0===n)return r?r[i]:e[t];r?r.scrollTo(o?r.pageXOffset:n,o?n:r.pageYOffset):e[t]=n},t,e,arguments.length)}}),S.each(["top","left"],function(e,n){S.cssHooks[n]=$e(y.pixelPosition,function(e,t){if(t)return t=Be(e,n),Me.test(t)?S(e).position()[n]+"px":t})}),S.each({Height:"height",Width:"width"},function(a,s){S.each({padding:"inner"+a,content:s,"":"outer"+a},function(r,o){S.fn[o]=function(e,t){var n=arguments.length&&(r||"boolean"!=typeof e),i=r||(!0===e||!0===t?"margin":"border");return $(this,function(e,t,n){var r;return x(e)?0===o.indexOf("outer")?e["inner"+a]:e.document.documentElement["client"+a]:9===e.nodeType?(r=e.documentElement,Math.max(e.body["scroll"+a],r["scroll"+a],e.body["offset"+a],r["offset"+a],r["client"+a])):void 0===n?S.css(e,t,i):S.style(e,t,n,i)},s,n?e:void 0,n)}})}),S.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){S.fn[t]=function(e){return this.on(t,e)}}),S.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),S.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,n){S.fn[n]=function(e,t){return 0<arguments.length?this.on(n,null,e,t):this.trigger(n)}});var Gt=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;S.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),m(e))return r=s.call(arguments,2),(i=function(){return e.apply(t||this,r.concat(s.call(arguments)))}).guid=e.guid=e.guid||S.guid++,i},S.holdReady=function(e){e?S.readyWait++:S.ready(!0)},S.isArray=Array.isArray,S.parseJSON=JSON.parse,S.nodeName=A,S.isFunction=m,S.isWindow=x,S.camelCase=X,S.type=w,S.now=Date.now,S.isNumeric=function(e){var t=S.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},S.trim=function(e){return null==e?"":(e+"").replace(Gt,"")},"function"==typeof define&&define.amd&&define("jquery",[],function(){return S});var Yt=C.jQuery,Qt=C.$;return S.noConflict=function(e){return C.$===S&&(C.$=Qt),e&&C.jQuery===S&&(C.jQuery=Yt),S},"undefined"==typeof e&&(C.jQuery=C.$=S),S});

/*! lazysizes - v5.2.2 */

!function(e){var t=function(u,D,f){"use strict";var k,H;if(function(){var e;var t={lazyClass:"lazyload",loadedClass:"lazyloaded",loadingClass:"lazyloading",preloadClass:"lazypreload",errorClass:"lazyerror",autosizesClass:"lazyautosizes",srcAttr:"data-src",srcsetAttr:"data-srcset",sizesAttr:"data-sizes",minSize:40,customMedia:{},init:true,expFactor:1.5,hFac:.8,loadMode:2,loadHidden:true,ricTimeout:0,throttleDelay:125};H=u.lazySizesConfig||u.lazysizesConfig||{};for(e in t){if(!(e in H)){H[e]=t[e]}}}(),!D||!D.getElementsByClassName){return{init:function(){},cfg:H,noSupport:true}}var O=D.documentElement,a=u.HTMLPictureElement,P="addEventListener",$="getAttribute",q=u[P].bind(u),I=u.setTimeout,U=u.requestAnimationFrame||I,l=u.requestIdleCallback,j=/^picture$/i,r=["load","error","lazyincluded","_lazyloaded"],i={},G=Array.prototype.forEach,J=function(e,t){if(!i[t]){i[t]=new RegExp("(\\s|^)"+t+"(\\s|$)")}return i[t].test(e[$]("class")||"")&&i[t]},K=function(e,t){if(!J(e,t)){e.setAttribute("class",(e[$]("class")||"").trim()+" "+t)}},Q=function(e,t){var i;if(i=J(e,t)){e.setAttribute("class",(e[$]("class")||"").replace(i," "))}},V=function(t,i,e){var a=e?P:"removeEventListener";if(e){V(t,i)}r.forEach(function(e){t[a](e,i)})},X=function(e,t,i,a,r){var n=D.createEvent("Event");if(!i){i={}}i.instance=k;n.initEvent(t,!a,!r);n.detail=i;e.dispatchEvent(n);return n},Y=function(e,t){var i;if(!a&&(i=u.picturefill||H.pf)){if(t&&t.src&&!e[$]("srcset")){e.setAttribute("srcset",t.src)}i({reevaluate:true,elements:[e]})}else if(t&&t.src){e.src=t.src}},Z=function(e,t){return(getComputedStyle(e,null)||{})[t]},s=function(e,t,i){i=i||e.offsetWidth;while(i<H.minSize&&t&&!e._lazysizesWidth){i=t.offsetWidth;t=t.parentNode}return i},ee=function(){var i,a;var t=[];var r=[];var n=t;var s=function(){var e=n;n=t.length?r:t;i=true;a=false;while(e.length){e.shift()()}i=false};var e=function(e,t){if(i&&!t){e.apply(this,arguments)}else{n.push(e);if(!a){a=true;(D.hidden?I:U)(s)}}};e._lsFlush=s;return e}(),te=function(i,e){return e?function(){ee(i)}:function(){var e=this;var t=arguments;ee(function(){i.apply(e,t)})}},ie=function(e){var i;var a=0;var r=H.throttleDelay;var n=H.ricTimeout;var t=function(){i=false;a=f.now();e()};var s=l&&n>49?function(){l(t,{timeout:n});if(n!==H.ricTimeout){n=H.ricTimeout}}:te(function(){I(t)},true);return function(e){var t;if(e=e===true){n=33}if(i){return}i=true;t=r-(f.now()-a);if(t<0){t=0}if(e||t<9){s()}else{I(s,t)}}},ae=function(e){var t,i;var a=99;var r=function(){t=null;e()};var n=function(){var e=f.now()-i;if(e<a){I(n,a-e)}else{(l||r)(r)}};return function(){i=f.now();if(!t){t=I(n,a)}}},e=function(){var v,m,c,h,e;var y,z,g,p,C,b,A;var n=/^img$/i;var d=/^iframe$/i;var E="onscroll"in u&&!/(gle|ing)bot/.test(navigator.userAgent);var _=0;var w=0;var N=0;var M=-1;var x=function(e){N--;if(!e||N<0||!e.target){N=0}};var W=function(e){if(A==null){A=Z(D.body,"visibility")=="hidden"}return A||!(Z(e.parentNode,"visibility")=="hidden"&&Z(e,"visibility")=="hidden")};var S=function(e,t){var i;var a=e;var r=W(e);g-=t;b+=t;p-=t;C+=t;while(r&&(a=a.offsetParent)&&a!=D.body&&a!=O){r=(Z(a,"opacity")||1)>0;if(r&&Z(a,"overflow")!="visible"){i=a.getBoundingClientRect();r=C>i.left&&p<i.right&&b>i.top-1&&g<i.bottom+1}}return r};var t=function(){var e,t,i,a,r,n,s,l,o,u,f,c;var d=k.elements;if((h=H.loadMode)&&N<8&&(e=d.length)){t=0;M++;for(;t<e;t++){if(!d[t]||d[t]._lazyRace){continue}if(!E||k.prematureUnveil&&k.prematureUnveil(d[t])){R(d[t]);continue}if(!(l=d[t][$]("data-expand"))||!(n=l*1)){n=w}if(!u){u=!H.expand||H.expand<1?O.clientHeight>500&&O.clientWidth>500?500:370:H.expand;k._defEx=u;f=u*H.expFactor;c=H.hFac;A=null;if(w<f&&N<1&&M>2&&h>2&&!D.hidden){w=f;M=0}else if(h>1&&M>1&&N<6){w=u}else{w=_}}if(o!==n){y=innerWidth+n*c;z=innerHeight+n;s=n*-1;o=n}i=d[t].getBoundingClientRect();if((b=i.bottom)>=s&&(g=i.top)<=z&&(C=i.right)>=s*c&&(p=i.left)<=y&&(b||C||p||g)&&(H.loadHidden||W(d[t]))&&(m&&N<3&&!l&&(h<3||M<4)||S(d[t],n))){R(d[t]);r=true;if(N>9){break}}else if(!r&&m&&!a&&N<4&&M<4&&h>2&&(v[0]||H.preloadAfterLoad)&&(v[0]||!l&&(b||C||p||g||d[t][$](H.sizesAttr)!="auto"))){a=v[0]||d[t]}}if(a&&!r){R(a)}}};var i=ie(t);var B=function(e){var t=e.target;if(t._lazyCache){delete t._lazyCache;return}x(e);K(t,H.loadedClass);Q(t,H.loadingClass);V(t,L);X(t,"lazyloaded")};var a=te(B);var L=function(e){a({target:e.target})};var T=function(t,i){try{t.contentWindow.location.replace(i)}catch(e){t.src=i}};var F=function(e){var t;var i=e[$](H.srcsetAttr);if(t=H.customMedia[e[$]("data-media")||e[$]("media")]){e.setAttribute("media",t)}if(i){e.setAttribute("srcset",i)}};var s=te(function(t,e,i,a,r){var n,s,l,o,u,f;if(!(u=X(t,"lazybeforeunveil",e)).defaultPrevented){if(a){if(i){K(t,H.autosizesClass)}else{t.setAttribute("sizes",a)}}s=t[$](H.srcsetAttr);n=t[$](H.srcAttr);if(r){l=t.parentNode;o=l&&j.test(l.nodeName||"")}f=e.firesLoad||"src"in t&&(s||n||o);u={target:t};K(t,H.loadingClass);if(f){clearTimeout(c);c=I(x,2500);V(t,L,true)}if(o){G.call(l.getElementsByTagName("source"),F)}if(s){t.setAttribute("srcset",s)}else if(n&&!o){if(d.test(t.nodeName)){T(t,n)}else{t.src=n}}if(r&&(s||o)){Y(t,{src:n})}}if(t._lazyRace){delete t._lazyRace}Q(t,H.lazyClass);ee(function(){var e=t.complete&&t.naturalWidth>1;if(!f||e){if(e){K(t,"ls-is-cached")}B(u);t._lazyCache=true;I(function(){if("_lazyCache"in t){delete t._lazyCache}},9)}if(t.loading=="lazy"){N--}},true)});var R=function(e){if(e._lazyRace){return}var t;var i=n.test(e.nodeName);var a=i&&(e[$](H.sizesAttr)||e[$]("sizes"));var r=a=="auto";if((r||!m)&&i&&(e[$]("src")||e.srcset)&&!e.complete&&!J(e,H.errorClass)&&J(e,H.lazyClass)){return}t=X(e,"lazyunveilread").detail;if(r){re.updateElem(e,true,e.offsetWidth)}e._lazyRace=true;N++;s(e,t,r,a,i)};var r=ae(function(){H.loadMode=3;i()});var l=function(){if(H.loadMode==3){H.loadMode=2}r()};var o=function(){if(m){return}if(f.now()-e<999){I(o,999);return}m=true;H.loadMode=3;i();q("scroll",l,true)};return{_:function(){e=f.now();k.elements=D.getElementsByClassName(H.lazyClass);v=D.getElementsByClassName(H.lazyClass+" "+H.preloadClass);q("scroll",i,true);q("resize",i,true);q("pageshow",function(e){if(e.persisted){var t=D.querySelectorAll("."+H.loadingClass);if(t.length&&t.forEach){U(function(){t.forEach(function(e){if(e.complete){R(e)}})})}}});if(u.MutationObserver){new MutationObserver(i).observe(O,{childList:true,subtree:true,attributes:true})}else{O[P]("DOMNodeInserted",i,true);O[P]("DOMAttrModified",i,true);setInterval(i,999)}q("hashchange",i,true);["focus","mouseover","click","load","transitionend","animationend"].forEach(function(e){D[P](e,i,true)});if(/d$|^c/.test(D.readyState)){o()}else{q("load",o);D[P]("DOMContentLoaded",i);I(o,2e4)}if(k.elements.length){t();ee._lsFlush()}else{i()}},checkElems:i,unveil:R,_aLSL:l}}(),re=function(){var i;var n=te(function(e,t,i,a){var r,n,s;e._lazysizesWidth=a;a+="px";e.setAttribute("sizes",a);if(j.test(t.nodeName||"")){r=t.getElementsByTagName("source");for(n=0,s=r.length;n<s;n++){r[n].setAttribute("sizes",a)}}if(!i.detail.dataAttr){Y(e,i.detail)}});var a=function(e,t,i){var a;var r=e.parentNode;if(r){i=s(e,r,i);a=X(e,"lazybeforesizes",{width:i,dataAttr:!!t});if(!a.defaultPrevented){i=a.detail.width;if(i&&i!==e._lazysizesWidth){n(e,r,a,i)}}}};var e=function(){var e;var t=i.length;if(t){e=0;for(;e<t;e++){a(i[e])}}};var t=ae(e);return{_:function(){i=D.getElementsByClassName(H.autosizesClass);q("resize",t)},checkElems:t,updateElem:a}}(),t=function(){if(!t.i&&D.getElementsByClassName){t.i=true;re._();e._()}};return I(function(){H.init&&t()}),k={cfg:H,autoSizer:re,loader:e,init:t,uP:Y,aC:K,rC:Q,hC:J,fire:X,gW:s,rAF:ee}}(e,e.document,Date);e.lazySizes=t,"object"==typeof module&&module.exports&&(module.exports=t)}("undefined"!=typeof window?window:{});
"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject2 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject3 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject4 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject5 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject6 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject7 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject9 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject10 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject11 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject13 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject14 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject15 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject16 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"]),
    _templateObject17 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject18 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject19 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject20 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject21 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject22 = _taggedTemplateLiteral(["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"], ["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject24 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject25 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject26 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject27 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject28 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject29 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject30 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject31 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject32 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject33 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

function gup(b) {

	b = b.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

	var a = "[\\?&]" + b + "=([^&#]*)";
	var d = new RegExp(a);
	var c = d.exec(window.location.href);

	if (c === null) return '';else return c[1];
}
/**
 * @description This module communicates with Lychee's API
 */

var api = {

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

api.isTimeout = function (errorThrown, jqXHR) {
	if (errorThrown && errorThrown === 'Bad Request' && jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.error && jqXHR.responseJSON.error === 'Session timed out') {
		return true;
	}

	return false;
};

api.post = function (fn, params, callback) {
	var responseProgressCB = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;


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

		api.onError(api.isTimeout(errorThrown, jqXHR) ? 'Session timed out.' : 'Server error or API not found.', params, errorThrown);
	};

	var ajaxParams = {
		type: 'POST',
		url: api_url,
		data: params,
		dataType: 'json',
		success: success,
		error: error
	};

	if (responseProgressCB !== null) {
		ajaxParams.xhrFields = {
			onprogress: responseProgressCB
		};
	}

	$.ajax(ajaxParams);
};

api.get = function (url, callback) {

	loadingBar.show();

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

		api.onError(api.isTimeout(errorThrown, jqXHR) ? 'Session timed out.' : 'Server error or API not found.', {}, errorThrown);
	};

	$.ajax({
		type: 'GET',
		url: url,
		data: {},
		dataType: 'text',
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

		api.onError(api.isTimeout(errorThrown, jqXHR) ? 'Session timed out.' : 'Server error or API not found.', params, errorThrown);
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
var csrf = {};

csrf.addLaravelCSRF = function (event, jqxhr, settings) {
	if (settings.url !== lychee.updatePath) {
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
 * @description Used to view single photos with view.php
 */

// Sub-implementation of lychee -------------------------------------------------------------- //

var lychee = {};

lychee.content = $('.content');
lychee.imageview = $('#imageview');
lychee.mapview = $('#mapview');

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

lychee.getEventName = function () {

	var touchendSupport = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || navigator.vendor || window.opera) && 'ontouchend' in document.documentElement;
	return touchendSupport === true ? 'touchend' : 'click';
};

// Sub-implementation of photo -------------------------------------------------------------- //

var photo = {
	json: null
};

photo.share = function (photoID, service) {

	var url = location.toString();

	switch (service) {
		case 'twitter':
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case 'facebook':
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url));
			break;
		case 'mail':
			location.href = "mailto:?subject=&body=" + encodeURI(url);
			break;
	}
};

photo.getDirectLink = function () {

	return $('#imageview img').attr('src').replace(/"/g, '').replace(/url\(|\)$/ig, '');
};

photo.update_display_overlay = function () {
	lychee.image_overlay = !lychee.image_overlay;
	if (!lychee.image_overlay) {
		$('#image_overlay').remove();
	} else {
		$('#imageview').append(build.overlay_image(photo.json));
	}
};

photo.show = function () {

	$('#imageview').removeClass('full');
	header.dom().removeClass('header--hidden');

	return true;
};

photo.hide = function () {

	if (visible.photo() && !visible.sidebar() && !visible.contextMenu()) {

		$('#imageview').addClass('full');
		header.dom().addClass('header--hidden');

		return true;
	}

	return false;
};

photo.onresize = function () {
	// Copy of view.photo.onresize
	if (photo.json.medium === '' || !photo.json.medium2x || photo.json.medium2x === '') return;

	var imgWidth = parseInt(photo.json.medium_dim);
	var imgHeight = photo.json.medium_dim.substr(photo.json.medium_dim.lastIndexOf('x') + 1);
	var containerWidth = parseFloat($('#imageview').width(), 10);
	var containerHeight = parseFloat($('#imageview').height(), 10);

	var width = imgWidth < containerWidth ? imgWidth : containerWidth;
	var height = width * imgHeight / imgWidth;
	if (height > containerHeight) {
		width = containerHeight * imgWidth / imgHeight;
	}

	$('img#image').attr('sizes', width + 'px');
};

// Sub-implementation of contextMenu -------------------------------------------------------------- //

var contextMenu = {};

contextMenu.sharePhoto = function (photoID, e) {

	var iconClass = 'ionicons';

	var items = [{ title: build.iconic('twitter', iconClass) + 'Twitter', fn: function fn() {
			return photo.share(photoID, 'twitter');
		} }, { title: build.iconic('facebook', iconClass) + 'Facebook', fn: function fn() {
			return photo.share(photoID, 'facebook');
		} }, { title: build.iconic('envelope-closed') + 'Mail', fn: function fn() {
			return photo.share(photoID, 'mail');
		} }, { title: build.iconic('link-intact') + 'Direct Link', fn: function fn() {
			return window.open(photo.getDirectLink(), '_newtab');
		} }];

	basicContext.show(items, e.originalEvent);
};

// Main -------------------------------------------------------------- //

var loadingBar = {
	show: function show() {},
	hide: function hide() {}
};

var imageview = $('#imageview');

$(document).ready(function () {

	// set CSRF protection (Laravel)
	csrf.bind();

	// Image View
	imageview.on('click', 'img', photo.update_display_overlay);

	$(window).on('resize', photo.onresize);

	// Save ID of photo
	var photoID = gup('p');

	// Set API error handler
	api.onError = error;

	// Share
	header.dom('#button_share').on('click', function (e) {
		contextMenu.sharePhoto(photoID, e);
	});

	// Infobox
	header.dom('#button_info').on('click', sidebar.toggle);

	// Load photo
	loadPhotoInfo(photoID);
});

var loadPhotoInfo = function loadPhotoInfo(photoID) {

	var params = {
		photoID: photoID,
		password: ''
	};

	api.post('Photo::get', params, function (data) {

		if (data === 'Warning: Photo private!' || data === 'Warning: Wrong password!') {

			$('body').append(build.no_content('question-mark')).removeClass('view');
			header.dom().remove();
			return false;
		}

		photo.json = data;

		// Set title
		if (!data.title) data.title = 'Untitled';
		document.title = 'Lychee - ' + data.title;
		header.dom('.header__title').html(lychee.escapeHTML(data.title));

		// Render HTML
		imageview.html(build.imageview(data, true).html);
		imageview.find('.arrow_wrapper').remove();
		imageview.addClass('fadeIn').show();
		photo.onresize();

		// Render Sidebar
		var structure = sidebar.createStructure.photo(data);
		var html = sidebar.render(structure);

		// Fullscreen
		var timeout = null;

		$(document).bind('mousemove', function () {
			clearTimeout(timeout);
			photo.show();
			timeout = setTimeout(photo.hide, 2500);
		});
		timeout = setTimeout(photo.hide, 2500);

		sidebar.dom('.sidebar__wrapper').html(html);
		sidebar.bind();
	});
};

var error = function error(errorThrown, params, data) {

	console.error({
		description: errorThrown,
		params: params,
		response: data
	});

	loadingBar.show('error', errorThrown);
};

/**
 * @description This module is used to generate HTML-Code.
 */

var build = {};

build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';


	var html = '';

	html += lychee.html(_templateObject, classes, icon);

	return html;
};

build.divider = function (title) {

	var html = '';

	html += lychee.html(_templateObject2, title);

	return html;
};

build.editIcon = function (id) {

	var html = '';

	html += lychee.html(_templateObject3, id, build.iconic('pencil'));

	return html;
};

build.multiselect = function (top, left) {

	return lychee.html(_templateObject4, top, left);
};

build.getAlbumThumb = function (data, i) {
	var isVideo = data.types[i] && data.types[i].indexOf('video') > -1;
	var isRaw = data.types[i] && data.types[i].indexOf('raw') > -1;
	var thumb = data.thumbs[i];

	if (thumb === 'uploads/thumb/' && isVideo) {
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	if (thumb === 'uploads/thumb/' && isRaw) {
		return "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}

	thumb2x = '';
	if (data.thumbs2x) {
		if (data.thumbs2x[i]) {
			thumb2x = data.thumbs2x[i];
		}
	} else {
		// Fallback code for Lychee v3
		var _lychee$retinize = lychee.retinize(data.thumbs[i]),
		    thumb2x = _lychee$retinize.path,
		    isPhoto = _lychee$retinize.isPhoto;

		if (!isPhoto) {
			thumb2x = '';
		}
	}

	return "<span class=\"thumbimg" + (isVideo ? ' video' : '') + "\"><img class='lazyload' src='img/placeholder.png' data-src='" + thumb + "' " + (thumb2x !== '' ? 'data-srcset=\'' + thumb2x + ' 2x\'' : '') + " alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
};

build.album = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var html = '';
	var date_stamp = data.sysdate;
	var sortingAlbums = [];

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

	html += lychee.html(_templateObject5, disabled ? "disabled" : "", data.id, tabindex.get_next_tab_index(), build.getAlbumThumb(data, 2), build.getAlbumThumb(data, 1), build.getAlbumThumb(data, 0), data.title, data.title, date_stamp);

	if (album.isUploadable() && !disabled) {

		html += lychee.html(_templateObject6, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' ? 'badge--visible' : '', data.visible === '1' ? 'badge--not--hidden' : 'badge--hidden', build.iconic('eye'), data.unsorted === '1' ? 'badge--visible' : '', build.iconic('list'), data.recent === '1' ? 'badge--visible badge--list' : '', build.iconic('clock'), data.password === '1' ? 'badge--visible' : '', build.iconic('lock-locked'), data.tag_album === '1' ? 'badge--tag' : '', build.iconic('tag'));
	}

	if (data.albums && data.albums.length > 0 || data.hasOwnProperty('has_albums') && data.has_albums === '1') {
		html += lychee.html(_templateObject7, build.iconic('layers'));
	}

	html += '</div>';

	return html;
};

build.photo = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	var html = '';
	var thumbnail = '';
	var thumb2x = '';

	var isVideo = data.type && data.type.indexOf('video') > -1;
	var isRaw = data.type && data.type.indexOf('raw') > -1;
	var isLivePhoto = data.livePhotoUrl !== '' && data.livePhotoUrl !== null;

	if (data.thumbUrl === 'uploads/thumb/' && isLivePhoto) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
	}
	if (data.thumbUrl === 'uploads/thumb/' && isVideo) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
	} else if (data.thumbUrl === 'uploads/thumb/' && isRaw) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
	} else if (lychee.layout === '0') {

		if (data.hasOwnProperty('thumb2x')) {
			// Lychee v4
			thumb2x = data.thumb2x;
		} else {
			// Lychee v3
			var _lychee$retinize2 = lychee.retinize(data.thumbUrl),
			    thumb2x = _lychee$retinize2.path;
		}

		if (thumb2x !== '') {
			thumb2x = "data-srcset='" + thumb2x + " 2x'";
		}

		thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + (isLivePhoto ? ' livephoto' : '') + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.thumbUrl + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
		thumbnail += "</span>";
	} else {

		if (data.small !== '') {
			if (data.hasOwnProperty('small2x') && data.small2x !== '') {
				thumb2x = "data-srcset='" + data.small + " " + parseInt(data.small_dim, 10) + "w, " + data.small2x + " " + parseInt(data.small2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.small + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (data.medium !== '') {
			if (data.hasOwnProperty('medium2x') && data.medium2x !== '') {
				thumb2x = "data-srcset='" + data.medium + " " + parseInt(data.medium_dim, 10) + "w, " + data.medium2x + " " + parseInt(data.medium2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.medium + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.url + "' alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else {
			// Fallback for videos with no small (the case of no thumb is
			// handled at the top of this function).

			if (data.hasOwnProperty('thumb2x')) {
				// Lychee v4
				thumb2x = data.thumb2x;
			} else {
				// Lychee v3
				var _lychee$retinize3 = lychee.retinize(data.thumbUrl),
				    thumb2x = _lychee$retinize3.path;
			}

			if (thumb2x !== '') {
				thumb2x = "data-srcset='" + data.thumbUrl + " 200w, " + thumb2x + " 400w'";
			}

			thumbnail = "<span class=\"thumbimg video\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.thumbUrl + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject8, disabled ? "disabled" : "", data.album, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.takedate !== '') html += lychee.html(_templateObject9, build.iconic('camera-slr'), data.takedate);else html += lychee.html(_templateObject10, data.sysdate);

	html += "</div>";

	if (album.isUploadable()) {

		html += lychee.html(_templateObject11, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' && album.json.public !== '1' ? 'badge--visible badge--hidden' : '', build.iconic('eye'));
	}

	html += "</div>";

	return html;
};

build.overlay_image = function (data) {
	var exifHash = data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;

	// Get the stored setting for the overlay_image
	var type = lychee.image_overlay_type;
	var html = "";

	if (type && type === 'desc' && data.description !== '') {
		html = lychee.html(_templateObject12, data.title, data.description);
	} else if (type && type === 'takedate' && data.takedate !== '') {
		html = lychee.html(_templateObject13, data.title, data.takedate);
	}
	// fall back to exif data if there is no description
	else if (exifHash !== '') {

			html += lychee.html(_templateObject14, data.title, data.shutter.replace('s', 'sec'), data.aperture.replace('f/', '&fnof; / '), lychee.locale['PHOTO_ISO'], data.iso, data.focal, data.lens && data.lens !== '' ? '(' + data.lens + ')' : '');
		}

	return html;
};

build.imageview = function (data, visibleControls, autoplay) {

	var html = '';
	var thumb = '';

	if (data.type.indexOf('video') > -1) {
		html += lychee.html(_templateObject15, visibleControls === true ? '' : 'full', autoplay ? 'autoplay' : '', tabindex.get_next_tab_index(), data.url);
	} else if (data.type.indexOf('raw') > -1 && data.medium === '') {
		html += lychee.html(_templateObject16, visibleControls === true ? '' : 'full', tabindex.get_next_tab_index());
	} else {
		var img = '';

		if (data.livePhotoUrl === '' || data.livePhotoUrl === null) {
			// It's normal photo

			// See if we have the thumbnail loaded...
			$('.photo').each(function () {
				if ($(this).attr('data-id') && $(this).attr('data-id') == data.id) {
					var thumbimg = $(this).find('img');
					if (thumbimg.length > 0) {
						thumb = thumbimg[0].currentSrc ? thumbimg[0].currentSrc : thumbimg[0].src;
						return false;
					}
				}
			});

			if (data.medium !== '') {
				var medium = '';

				if (data.hasOwnProperty('medium2x') && data.medium2x !== '') {
					medium = "srcset='" + data.medium + " " + parseInt(data.medium_dim, 10) + "w, " + data.medium2x + " " + parseInt(data.medium2x_dim, 10) + "w'";
				}
				img = "<img id='image' class='" + (visibleControls === true ? '' : 'full') + "' src='" + data.medium + "' " + medium + ("  draggable='false' alt='medium' data-tabindex='" + tabindex.get_next_tab_index() + "'>");
			} else {
				img = "<img id='image' class='" + (visibleControls === true ? '' : 'full') + "' src='" + data.url + "' draggable='false' alt='big' data-tabindex='" + tabindex.get_next_tab_index() + "'>";
			}
		} else {

			if (data.medium !== '') {
				var medium_dims = data.medium_dim.split("x");
				var medium_width = medium_dims[0];
				var medium_height = medium_dims[1];
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.medium + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + medium_width + "px; height: " + medium_height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			} else {
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.url + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + data.width + "px; height: " + data.height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			}
		}

		html += lychee.html(_templateObject17, img);

		if (lychee.image_overlay) html += build.overlay_image(data);
	}

	html += "\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic('caret-left') + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic('caret-right') + "</a></div>\n\t\t\t";

	return { html: html, thumb: thumb };
};

build.no_content = function (typ) {

	var html = '';

	html += lychee.html(_templateObject18, build.iconic(typ));

	switch (typ) {
		case 'magnifying-glass':
			html += lychee.html(_templateObject19, lychee.locale['VIEW_NO_RESULT']);
			break;
		case 'eye':
			html += lychee.html(_templateObject19, lychee.locale['VIEW_NO_PUBLIC_ALBUMS']);
			break;
		case 'cog':
			html += lychee.html(_templateObject19, lychee.locale['VIEW_NO_CONFIGURATION']);
			break;
		case 'question-mark':
			html += lychee.html(_templateObject19, lychee.locale['VIEW_PHOTO_NOT_FOUND']);
			break;
	}

	html += "</div>";

	return html;
};

build.uploadModal = function (title, files) {

	var html = '';

	html += lychee.html(_templateObject20, title);

	var i = 0;

	while (i < files.length) {

		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + '...' + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject21, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

build.uploadNewFile = function (name) {

	if (name.length > 40) {
		name = name.substr(0, 17) + '...' + name.substr(name.length - 20, 20);
	}

	return lychee.html(_templateObject22, name);
};

build.tags = function (tags) {

	var html = '';
	var editable = typeof album !== 'undefined' ? album.isUploadable() : false;

	// Search is enabled if logged in (not publicMode) or public seach is enabled
	var searchable = lychee.publicMode === false || lychee.public_search === true;

	// build class_string for tag
	var a_class = 'tag';
	if (searchable) {
		a_class = a_class + ' search';
	}

	if (tags !== '') {

		tags = tags.split(',');

		tags.forEach(function (tag, index) {
			if (editable) {
				html += lychee.html(_templateObject23, a_class, tag, index, build.iconic('x'));
			} else {
				html += lychee.html(_templateObject24, a_class, tag);
			}
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

build.u2f = function (credential) {
	var html = lychee.html(_templateObject27, credential.id, credential.id, credential.id, credential.id);

	return html;
};
/**
 * @description This module takes care of the header.
 */

var header = {

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

		if (lychee.enable_contextmenu_header === false) return false;

		if (visible.photo()) contextMenu.photoTitle(album.getID(), photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom('#button_visibility').on(eventName, function (e) {
		photo.setPublic(photo.getID(), e);
	});
	header.dom('#button_share').on(eventName, function (e) {
		contextMenu.sharePhoto(photo.getID(), e);
	});

	header.dom('#button_visibility_album').on(eventName, function (e) {
		album.setPublic(album.getID(), e);
	});
	header.dom('#button_share_album').on(eventName, function (e) {
		contextMenu.shareAlbum(album.getID(), e);
	});

	header.dom('#button_signin').on(eventName, lychee.loginDialog);
	header.dom('#button_settings').on(eventName, leftMenu.open);
	header.dom('#button_info_album').on(eventName, sidebar.toggle);
	header.dom('#button_info').on(eventName, sidebar.toggle);
	header.dom('.button--map-albums').on(eventName, function () {
		lychee.gotoMap();
	});
	header.dom('#button_map_album').on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom('#button_map').on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom('.button_add').on(eventName, contextMenu.add);
	header.dom('#button_more').on(eventName, function (e) {
		contextMenu.photoMore(photo.getID(), e);
	});
	header.dom('#button_move_album').on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, 'ROOT', album.getParent() != '');
	});
	header.dom('#button_move').on(eventName, function (e) {
		contextMenu.move([photo.getID()], e, photo.setAlbum);
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
		album.getArchive([album.getID()]);
	});
	header.dom('#button_star').on(eventName, function () {
		photo.setStar([photo.getID()]);
	});
	header.dom('#button_rotate_ccwise').on(eventName, function () {
		photoeditor.rotate(photo.getID(), -1);
	});
	header.dom('#button_rotate_cwise').on(eventName, function () {
		photoeditor.rotate(photo.getID(), 1);
	});
	header.dom('#button_back_home').on(eventName, function () {
		if (!album.json.parent_id) {
			lychee.goto();
		} else {
			lychee.goto(album.getParent());
		}
	});
	header.dom('#button_back').on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom('#button_back_map').on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom('#button_fs_album_enter,#button_fs_enter').on(eventName, lychee.fullscreenEnter);
	header.dom('#button_fs_album_exit,#button_fs_exit').on(eventName, lychee.fullscreenExit).hide();

	header.dom('.header__search').on('keyup click', function () {
		if ($(this).val().length > 0) {
			lychee.goto('search/' + encodeURIComponent($(this).val()));
		} else if (search.hash !== null) {
			search.reset();
		}
	});
	header.dom('.header__clear').on(eventName, function () {
		header.dom('.header__search').focus();
		search.reset();
	});

	header.bind_back();

	return true;
};

header.bind_back = function () {

	// Event Name
	var eventName = lychee.getEventName();

	header.dom('.header__title').on(eventName, function () {
		if (lychee.landing_page_enable && visible.albums()) {
			window.location.href = '.';
		} else {
			return false;
		}
	});
};

header.show = function () {

	lychee.imageview.removeClass('full');
	header.dom().removeClass('header--hidden');

	tabindex.restoreSettings(header.dom());

	photo.updateSizeLivePhotoDuringAnimation();

	return true;
};

header.hideIfLivePhotoNotPlaying = function () {

	// Hides the header, if current live photo is not playing
	if (photo.isLivePhotoPlaying() == true) return false;
	return header.hide();
};

header.hide = function () {

	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {

		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass('full');
		header.dom().addClass('header--hidden');

		photo.updateSizeLivePhotoDuringAnimation();

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
			header.dom('.header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--public').addClass('header__toolbar--visible');
			tabindex.makeFocusable(header.dom('.header__toolbar--public'));
			tabindex.makeUnfocusable(header.dom('.header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map'));

			if (lychee.public_search) {
				var e = $('.header__search, .header__clear', '.header__toolbar--public');
				e.show();
				tabindex.makeFocusable(e);
			} else {
				var _e = $('.header__search, .header__clear', '.header__toolbar--public');
				_e.hide();
				tabindex.makeUnfocusable(_e);
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				var _e2 = $('.button--map-albums', '.header__toolbar--public');
				_e2.show();
				tabindex.makeFocusable(_e2);
			} else {
				var _e3 = $('.button--map-albums', '.header__toolbar--public');
				_e3.hide();
				tabindex.makeUnfocusable(_e3);
			}

			// Set focus on login button
			if (lychee.active_focus_on_page_load) {
				$('#button_signin').focus();
			}
			return true;

		case 'albums':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--albums').addClass('header__toolbar--visible');

			tabindex.makeFocusable(header.dom('.header__toolbar--albums'));
			tabindex.makeUnfocusable(header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map'));

			// If map is disabled, we should hide the icon
			if (lychee.map_display) {
				var _e4 = $('.button--map-albums', '.header__toolbar--albums');
				_e4.show();
				tabindex.makeFocusable(_e4);
			} else {
				var _e5 = $('.button--map-albums', '.header__toolbar--albums');
				_e5.hide();
				tabindex.makeUnfocusable(_e5);
			}

			if (lychee.enable_button_add) {
				var _e6 = $('.button_add', '.header__toolbar--albums');
				_e6.show();
				tabindex.makeFocusable(_e6);
			} else {
				var _e7 = $('.button_add', '.header__toolbar--albums');
				_e7.remove();
			}

			return true;

		case 'album':

			var albumID = album.getID();

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--album').addClass('header__toolbar--visible');

			tabindex.makeFocusable(header.dom('.header__toolbar--album'));
			tabindex.makeUnfocusable(header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map'));

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos === false && album.json.albums && album.json.albums.length === 0 || !album.isUploadable() && album.json.downloadable === '0') {
				var _e8 = $('#button_archive');
				_e8.hide();
				tabindex.makeUnfocusable(_e8);
			} else {
				var _e9 = $('#button_archive');
				_e9.show();
				tabindex.makeFocusable(_e9);
			}

			if (album.json && album.json.hasOwnProperty('share_button_visible') && album.json.share_button_visible !== '1') {
				var _e10 = $('#button_share_album');
				_e10.hide();
				tabindex.makeUnfocusable(_e10);
			} else {
				var _e11 = $('#button_share_album');
				_e11.show();
				tabindex.makeFocusable(_e11);
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e12 = $('#button_map_album');
				_e12.show();
				tabindex.makeFocusable(_e12);
			} else {
				var _e13 = $('#button_map_album');
				_e13.hide();
				tabindex.makeUnfocusable(_e13);
			}

			if (albumID === 'starred' || albumID === 'public' || albumID === 'recent') {
				$('#button_info_album, #button_trash_album, #button_visibility_album, #button_move_album').hide();
				$('.button_add, .header__divider', '.header__toolbar--album').show();
				tabindex.makeFocusable($('.button_add, .header__divider', '.header__toolbar--album'));
				tabindex.makeUnfocusable($('#button_info_album, #button_trash_album, #button_visibility_album, #button_move_album'));
			} else if (albumID === 'unsorted') {
				$('#button_info_album, #button_visibility_album, #button_move_album').hide();
				$('#button_trash_album, .button_add, .header__divider', '.header__toolbar--album').show();
				tabindex.makeFocusable($('#button_trash_album, .button_add, .header__divider', '.header__toolbar--album'));
				tabindex.makeUnfocusable($('#button_info_album, #button_visibility_album, #button_move_album'));
			} else if (album.isTagAlbum()) {
				$('#button_info_album').show();
				$('#button_move_album').hide();
				$('.button_add, .header__divider', '.header__toolbar--album').hide();
				tabindex.makeFocusable($('#button_info_album'));
				tabindex.makeUnfocusable($('#button_move_album'));
				tabindex.makeUnfocusable($('.button_add, .header__divider', '.header__toolbar--album'));
				if (album.isUploadable()) {
					$('#button_visibility_album, #button_trash_album').show();
					tabindex.makeFocusable($('#button_visibility_album, #button_trash_album'));
				} else {
					$('#button_visibility_album, #button_trash_album').hide();
					tabindex.makeUnfocusable($('#button_visibility_album, #button_trash_album'));
				}
			} else {
				$('#button_info_album').show();
				tabindex.makeFocusable($('#button_info_album'));
				if (album.isUploadable()) {
					$('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album').show();
					tabindex.makeFocusable($('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album'));
				} else {
					$('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album').hide();
					tabindex.makeUnfocusable($('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album'));
				}
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e14 = $('#button_visibility_album', '.header__toolbar--album');
				_e14.remove();
			}
			if (!lychee.enable_button_share) {
				var _e15 = $('#button_share_album', '.header__toolbar--album');
				_e15.remove();
			}
			if (!lychee.enable_button_archive) {
				var _e16 = $('#button_archive', '.header__toolbar--album');
				_e16.remove();
			}
			if (!lychee.enable_button_move) {
				var _e17 = $('#button_move_album', '.header__toolbar--album');
				_e17.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e18 = $('#button_trash_album', '.header__toolbar--album');
				_e18.remove();
			}
			if (!lychee.enable_button_fullscreen) {
				var _e19 = $('#button_fs_album_enter', '.header__toolbar--album');
				_e19.remove();
			}
			if (!lychee.enable_button_add) {
				var _e20 = $('.button_add', '.header__toolbar--album');
				_e20.remove();
			}

			return true;

		case 'photo':

			header.dom().addClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--photo').addClass('header__toolbar--visible');

			tabindex.makeFocusable(header.dom('.header__toolbar--photo'));
			tabindex.makeUnfocusable(header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map'));
			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e21 = $('#button_map');
				_e21.show();
				tabindex.makeFocusable(_e21);
			} else {
				var _e22 = $('#button_map');
				_e22.hide();
				tabindex.makeUnfocusable(_e22);
			}

			if (album.isUploadable()) {
				var _e23 = $('#button_trash, #button_move, #button_visibility, #button_star');
				_e23.show();
				tabindex.makeFocusable(_e23);
			} else {
				var _e24 = $('#button_trash, #button_move, #button_visibility, #button_star');
				_e24.hide();
				tabindex.makeUnfocusable(_e24);
			}

			if (photo.json && photo.json.hasOwnProperty('share_button_visible') && photo.json.share_button_visible !== '1') {
				var _e25 = $('#button_share');
				_e25.hide();
				tabindex.makeUnfocusable(_e25);
			} else {
				var _e26 = $('#button_share');
				_e26.show();
				tabindex.makeFocusable(_e26);
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$('#button_more').show();
			tabindex.makeFocusable($('#button_more'));
			if (!(album.isUploadable() || (photo.json.hasOwnProperty('downloadable') ? photo.json.downloadable === '1' : album.json && album.json.downloadable && album.json.downloadable === '1')) && !(photo.json.url && photo.json.url !== '')) {
				var _e27 = $('#button_more');
				_e27.hide();
				tabindex.makeUnfocusable(_e27);
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e28 = $('#button_visibility', '.header__toolbar--photo');
				_e28.remove();
			}
			if (!lychee.enable_button_share) {
				var _e29 = $('#button_share', '.header__toolbar--photo');
				_e29.remove();
			}
			if (!lychee.enable_button_move) {
				var _e30 = $('#button_move', '.header__toolbar--photo');
				_e30.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e31 = $('#button_trash', '.header__toolbar--photo');
				_e31.remove();
			}
			if (!lychee.enable_button_fullscreen) {
				var _e32 = $('#button_fs_enter', '.header__toolbar--photo');
				_e32.remove();
			}
			if (!lychee.enable_button_more) {
				var _e33 = $('#button_more', '.header__toolbar--photo');
				_e33.remove();
			}
			if (!lychee.enable_button_rotate) {
				var _e34 = $('#button_rotate_cwise', '.header__toolbar--photo');
				_e34.remove();

				_e34 = $('#button_rotate_ccwise', '.header__toolbar--photo');
				_e34.remove();
			}
			return true;
		case 'map':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--map').addClass('header__toolbar--visible');

			tabindex.makeFocusable(header.dom('.header__toolbar--map'));
			tabindex.makeUnfocusable(header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo'));
			return true;

	}

	return false;
};

// Note that the pull-down menu is now enabled not only for editable
// items but for all of public/albums/album/photo views, so 'editable' is a
// bit of a misnomer at this point...
header.setEditable = function (editable) {

	var $title = header.dom('.header__title');

	if (editable) $title.addClass('header__title--editable');else $title.removeClass('header__title--editable');

	return true;
};

header.applyTranslations = function () {

	var selector_locale = {
		'#button_signin': 'SIGN_IN',
		'#button_settings': 'SETTINGS',
		'#button_info_album': 'ABOUT_ALBUM',
		'#button_info': 'ABOUT_PHOTO',
		'.button_add': 'ADD',
		'#button_move_album': 'MOVE_ALBUM',
		'#button_move': 'MOVE',
		'#button_trash_album': 'DELETE_ALBUM',
		'#button_trash': 'DELETE',
		'#button_archive': 'DOWNLOAD_ALBUM',
		'#button_star': 'STAR_PHOTO',
		'#button_back_home': 'CLOSE_ALBUM',
		'#button_fs_album_enter': 'FULLSCREEN_ENTER',
		'#button_fs_enter': 'FULLSCREEN_ENTER',
		'#button_share': 'SHARE_PHOTO',
		'#button_share_album': 'SHARE_ALBUM'
	};

	for (var selector in selector_locale) {
		header.dom(selector).prop('title', lychee.locale[selector_locale[selector]]);
	}
};

/**
 * @description This module is used to check if elements are visible or not.
 */

var visible = {};

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

visible.mapview = function () {
	if ($('#mapview.fadeIn').length > 0) return true;
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

/**
 * @description This module takes care of the sidebar.
 */

var sidebar = {

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

	sidebar.dom('#edit_showtags').off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	sidebar.dom('#edit_tags').off(eventName).on(eventName, function () {
		photo.editTags([photo.getID()]);
	});

	sidebar.dom('#tags .tag').off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});

	sidebar.dom('#tags .tag span').off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data('index'));
	});

	sidebar.dom('#edit_license').off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setLicense(photo.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	sidebar.dom('#edit_sorting').off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	sidebar.dom('.attr_location').off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});

	return true;
};

sidebar.triggerSearch = function (search_string) {

	// If public search is diabled -> do nothing
	if (lychee.publicMode === true && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.hash = null;
	// We're either logged in or public search is allowed
	lychee.goto('search/' + encodeURIComponent(search_string));
};

sidebar.toggle = function () {

	if (visible.sidebar() || visible.sidebarbutton()) {

		header.dom('.button--info').toggleClass('active');
		lychee.content.toggleClass('content--sidebar');
		lychee.imageview.toggleClass('image--sidebar');
		if (typeof view !== 'undefined') view.album.content.justify();
		sidebar.dom().toggleClass('active');
		photo.updateSizeLivePhotoDuringAnimation();

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

sidebar.hideAttr = function (attr) {
	sidebar.dom('.attr_' + attr).closest('tr').hide();
};

sidebar.secondsToHMS = function (d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 60);

	return (h > 0 ? h.toString() + 'h' : '') + (m > 0 ? m.toString() + 'm' : '') + (s > 0 || h == 0 && m == 0 ? s.toString() + 's' : '');
};

sidebar.createStructure.photo = function (data) {

	if (data == null || data === '') return false;

	var editable = typeof album !== 'undefined' ? album.isUploadable() : false;
	var exifHash = data.takedate + data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;
	var locationHash = data.longitude + data.latitude + data.altitude;
	var structure = {};
	var _public = '';
	var isVideo = data.type && data.type.indexOf('video') > -1;
	var license = void 0;

	// Set the license string for a photo
	switch (data.license) {
		// if the photo doesn't have a license
		case 'none':
			license = '';
			break;
		// Localize All Rights Reserved
		case 'reserved':
			license = lychee.locale['PHOTO_RESERVED'];
			break;
		// Display anything else that's set
		default:
			license = data.license;
			break;
	}

	// Set value for public
	switch (data.public) {

		case '0':
			_public = lychee.locale['PHOTO_SHR_NO'];
			break;
		case '1':
			_public = lychee.locale['PHOTO_SHR_PHT'];
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
		rows: [{ title: lychee.locale['PHOTO_TITLE'], kind: 'title', value: data.title, editable: editable }, { title: lychee.locale['PHOTO_UPLOADED'], kind: 'uploaded', value: data.sysdate }, { title: lychee.locale['PHOTO_DESCRIPTION'], kind: 'description', value: data.description, editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? 'PHOTO_VIDEO' : 'PHOTO_IMAGE'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_SIZE'], kind: 'size', value: data.size }, { title: lychee.locale['PHOTO_FORMAT'], kind: 'type', value: data.type }, { title: lychee.locale['PHOTO_RESOLUTION'], kind: 'resolution', value: data.width + ' x ' + data.height }]
	};

	if (isVideo) {
		if (data.width === 0 || data.height === 0) {
			// Remove the "Resolution" line if we don't have the data.
			structure.image.rows.splice(-1, 1);
		}

		// We overload the database, storing duration (in full seconds) in
		// "aperture" and frame rate (floating point with three digits after
		// the decimal point) in "focal".
		if (data.aperture != '') {
			structure.image.rows.push({ title: lychee.locale['PHOTO_DURATION'],
				kind: 'duration', value: sidebar.secondsToHMS(data.aperture) });
		}
		if (data.focal != '') {
			structure.image.rows.push({ title: lychee.locale['PHOTO_FPS'],
				kind: 'fps', value: data.focal + ' fps' });
		}
	}

	// Always create tags section - behaviour for editing
	//tags handled when contructing the html code for tags

	structure.tags = {
		title: lychee.locale['PHOTO_TAGS'],
		type: sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable

		// Only create EXIF section when EXIF data available
	};if (exifHash !== '') {

		structure.exif = {
			title: lychee.locale['PHOTO_CAMERA'],
			type: sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale['PHOTO_CAPTURED'], kind: 'takedate', value: data.takedate }, { title: lychee.locale['PHOTO_MAKE'], kind: 'make', value: data.make }, { title: lychee.locale['PHOTO_TYPE'], kind: 'model', value: data.model }] : [{ title: lychee.locale['PHOTO_CAPTURED'], kind: 'takedate', value: data.takedate }, { title: lychee.locale['PHOTO_MAKE'], kind: 'make', value: data.make }, { title: lychee.locale['PHOTO_TYPE'], kind: 'model', value: data.model }, { title: lychee.locale['PHOTO_LENS'], kind: 'lens', value: data.lens }, { title: lychee.locale['PHOTO_SHUTTER'], kind: 'shutter', value: data.shutter }, { title: lychee.locale['PHOTO_APERTURE'], kind: 'aperture', value: data.aperture }, { title: lychee.locale['PHOTO_FOCAL'], kind: 'focal', value: data.focal }, { title: lychee.locale['PHOTO_ISO'], kind: 'iso', value: data.iso }]
		};
	} else {

		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale['PHOTO_SHARING'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_SHR_PLUBLIC'], kind: 'public', value: _public }]
	};

	structure.license = {
		title: lychee.locale['PHOTO_REUSE'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['PHOTO_LICENSE'], kind: 'license', value: license, editable: editable }]
	};

	if (locationHash !== '' && locationHash !== 0) {

		structure.location = {
			title: lychee.locale['PHOTO_LOCATION'],
			type: sidebar.types.DEFAULT,
			rows: [{ title: lychee.locale['PHOTO_LATITUDE'], kind: 'latitude', value: data.latitude ? DecimalToDegreeMinutesSeconds(data.latitude, true) : '' }, { title: lychee.locale['PHOTO_LONGITUDE'], kind: 'longitude', value: data.longitude ? DecimalToDegreeMinutesSeconds(data.longitude, false) : '' },
			// No point in displaying sub-mm precision; 10cm is more than enough.
			{ title: lychee.locale['PHOTO_ALTITUDE'], kind: 'altitude', value: data.altitude ? (Math.round(parseFloat(data.altitude) * 10) / 10).toString() + 'm' : '' }, { title: lychee.locale['PHOTO_LOCATION'], kind: 'location', value: data.location ? data.location : '' }]
		};
		if (data.imgDirection) {
			// No point in display sub-degree precision.
			structure.location.rows.push({ title: lychee.locale['PHOTO_IMGDIRECTION'], kind: 'imgDirection', value: Math.round(data.imgDirection).toString() + '°' });
		}
	} else {
		structure.location = {};
	}

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.image, structure.tags, structure.exif, structure.location, structure.license];

	if (!lychee.publicMode) {
		structure_ret.push(structure.sharing);
	}

	return structure_ret;
};

sidebar.createStructure.album = function (album) {

	var data = album.json;

	if (data == null || data === '') return false;

	var editable = album.isUploadable();
	var structure = {};
	var _public = '';
	var hidden = '';
	var downloadable = '';
	var share_button_visible = '';
	var password = '';
	var license = '';
	var sorting = '';

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

	// Set value for share_button_visible
	switch (data.share_button_visible) {

		case '0':
			share_button_visible = lychee.locale['ALBUM_SHR_NO'];
			break;
		case '1':
			share_button_visible = lychee.locale['ALBUM_SHR_YES'];
			break;
		default:
			share_button_visible = '-';
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

	// Set license string
	switch (data.license) {
		case 'none':
			license = ''; // consistency
			break;
		case 'reserved':
			license = lychee.locale['ALBUM_RESERVED'];
			break;
		default:
			license = data.license;
			break;
	}

	if (data.sorting_col === '') {
		sorting = lychee.locale['DEFAULT'];
	} else {
		sorting = data.sorting_col + ' ' + data.sorting_order;
	}

	structure.basics = {
		title: lychee.locale['ALBUM_BASICS'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_TITLE'], kind: 'title', value: data.title, editable: editable }, { title: lychee.locale['ALBUM_DESCRIPTION'], kind: 'description', value: data.description, editable: editable }]
	};

	if (album.isTagAlbum()) {
		structure.basics.rows.push({ title: lychee.locale['ALBUM_SHOW_TAGS'], kind: 'showtags', value: data.show_tags, editable: editable });
	}

	var videoCount = 0;
	$.each(data.photos, function () {
		if (this.type && this.type.indexOf('video') > -1) {
			videoCount++;
		}
	});
	structure.album = {
		title: lychee.locale['ALBUM_ALBUM'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_CREATED'], kind: 'created', value: data.sysdate }]
	};
	if (data.albums && data.albums.length > 0) {
		structure.album.rows.push({ title: lychee.locale['ALBUM_SUBALBUMS'],
			kind: 'subalbums', value: data.albums.length });
	}
	if (data.photos) {
		if (data.photos.length - videoCount > 0) {
			structure.album.rows.push({ title: lychee.locale['ALBUM_IMAGES'],
				kind: 'images',
				value: data.photos.length - videoCount });
		}
	}
	if (videoCount > 0) {
		structure.album.rows.push({ title: lychee.locale['ALBUM_VIDEOS'],
			kind: 'videos', value: videoCount });
	}

	if (data.photos) {
		structure.album.rows.push({ title: lychee.locale['ALBUM_ORDERING'], kind: 'sorting', value: sorting, editable: editable });
	}

	structure.share = {
		title: lychee.locale['ALBUM_SHARING'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_PUBLIC'], kind: 'public', value: _public }, { title: lychee.locale['ALBUM_HIDDEN'], kind: 'hidden', value: hidden }, { title: lychee.locale['ALBUM_DOWNLOADABLE'], kind: 'downloadable', value: downloadable }, { title: lychee.locale['ALBUM_SHARE_BUTTON_VISIBLE'], kind: 'share_button_visible', value: share_button_visible }, { title: lychee.locale['ALBUM_PASSWORD'], kind: 'password', value: password }]
	};

	if (data.owner != null) {
		structure.share.rows.push({ title: lychee.locale['ALBUM_OWNER'], kind: 'owner', value: data.owner });
	}

	structure.license = {
		title: lychee.locale['ALBUM_REUSE'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_LICENSE'], kind: 'license', value: license, editable: editable }]
	};

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.album, structure.license];
	if (!lychee.publicMode) {
		structure_ret.push(structure.share);
	}

	return structure_ret;
};

sidebar.has_location = function (structure) {

	if (structure == null || structure === '' || structure === false) return false;

	var _has_location = false;

	structure.forEach(function (section) {

		if (section.title == lychee.locale['PHOTO_LOCATION']) {
			_has_location = true;
		}
	});

	return _has_location;
};

sidebar.render = function (structure) {

	if (structure == null || structure === '' || structure === false) return false;

	var html = '';

	var renderDefault = function renderDefault(section) {

		var _html = '';

		_html += "\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>" + section.title + "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t ";

		if (section.title == lychee.locale['PHOTO_LOCATION']) {
			var _has_latitude = false;
			var _has_longitude = false;

			section.rows.forEach(function (row, index, object) {
				if (row.kind == 'latitude' && row.value !== '') {
					_has_latitude = true;
				}

				if (row.kind == 'longitude' && row.value !== '') {
					_has_longitude = true;
				}

				// Do not show location is not enabled
				if (row.kind == 'location' && (lychee.publicMode === true && !lychee.location_show_public || !lychee.location_show)) {
					object.splice(index, 1);
				} else {
					// Explode location string into an array to keep street, city etc separate
					if (!(row.value === '' || row.value == null)) {
						section.rows[index].value = row.value.split(',').map(function (item) {
							return item.trim();
						});
					}
				}
			});

			if (_has_latitude && _has_longitude && lychee.map_display) {
				_html += "\n\t\t\t\t\t\t <div id=\"leaflet_map_single_photo\"></div>\n\t\t\t\t\t\t ";
			}
		}

		section.rows.forEach(function (row) {

			var value = row.value;

			// show only Exif rows which have a value or if its editable
			if (!(value === '' || value == null) || row.editable === true) {

				// Wrap span-element around value for easier selecting on change
				if (Array.isArray(row.value)) {
					value = '';
					row.value.forEach(function (v) {
						if (v === '' || v == null) {
							return;
						}
						// Add separator if needed
						if (value !== '') {
							value += lychee.html(_templateObject29, row.kind);
						}
						value += lychee.html(_templateObject30, row.kind, v);
					});
				} else {
					value = lychee.html(_templateObject31, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += ' ' + build.editIcon('edit_' + row.kind);

				_html += lychee.html(_templateObject32, row.title, value);
			}
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	var renderTags = function renderTags(section) {

		var _html = '';
		var editable = '';

		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon('edit_tags');

		_html += lychee.html(_templateObject33, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	structure.forEach(function (section) {

		if (section.type === sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === sidebar.types.TAGS) html += renderTags(section);
	});

	return html;
};

function DecimalToDegreeMinutesSeconds(decimal, type) {

	var degrees = 0;
	var minutes = 0;
	var seconds = 0;
	var direction = void 0;

	//decimal must be integer or float no larger than 180;
	//type must be Boolean
	if (Math.abs(decimal) > 180 || typeof type !== "boolean") {
		return false;
	}

	//inputs OK, proceed
	//type is latitude when true, longitude when false

	//set direction; north assumed
	if (type && decimal < 0) {
		direction = 'S';
	} else if (!type && decimal < 0) {
		direction = 'W';
	} else if (!type) {
		direction = 'E';
	} else {
		direction = 'N';
	}

	//get absolute value of decimal
	var d = Math.abs(decimal);

	//get degrees
	degrees = Math.floor(d);

	//get seconds
	seconds = (d - degrees) * 3600;

	//get minutes
	minutes = Math.floor(seconds / 60);

	//reset seconds
	seconds = Math.floor(seconds - minutes * 60);

	return degrees + '° ' + minutes + '\' ' + seconds + '\" ' + direction;
}

/**
 * @description This module takes care of the map view of a full album and its sub-albums.
 */

var map_provider_layer_attribution = {
	'Wikimedia': {
		layer: 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
		attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
	},
	'OpenStreetMap.org': {
		layer: 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	'OpenStreetMap.de': {
		layer: 'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png ',
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	'OpenStreetMap.fr': {
		layer: 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ',
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	'RRZE': {
		layer: 'https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png',
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	}
};

var mapview = {
	map: null,
	photoLayer: null,
	min_lat: null,
	min_lng: null,
	max_lat: null,
	max_lng: null,
	albumID: null,
	map_provider: null
};

mapview.isInitialized = function () {
	if (mapview.map === null || mapview.photoLayer === null) {
		return false;
	}
	return true;
};

mapview.title = function (_albumID, _albumTitle) {
	switch (_albumID) {
		case 'f':
			lychee.setTitle(lychee.locale['STARRED'], false);
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
		case null:
			lychee.setTitle(lychee.locale['ALBUMS'], false);
			break;
		default:
			lychee.setTitle(_albumTitle, false);
			break;
	}
};

// Open the map view
mapview.open = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;


	// If map functionality is disabled -> do nothing
	if (!lychee.map_display || lychee.publicMode === true && !lychee.map_display_public) {
		loadingBar.show('error', lychee.locale['ERROR_MAP_DEACTIVATED']);
		return;
	}

	lychee.animate($('#mapview'), 'fadeIn');
	$('#mapview').show();
	header.setMode('map');

	mapview.albumID = albumID;

	// initialize container only once
	if (mapview.isInitialized() == false) {

		// Leaflet seaches for icon in same directoy as js file -> paths needs
		// to be overwritten
		delete L.Icon.Default.prototype._getIconUrl;
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: 'img/marker-icon-2x.png',
			iconUrl: 'img/marker-icon.png',
			shadowUrl: 'img/marker-shadow.png'
		});

		// Set initial view to (0,0)
		mapview.map = L.map('leaflet_map_full').setView([0.0, 0.0], 13);

		L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
			attribution: map_provider_layer_attribution[lychee.map_provider].attribution
		}).addTo(mapview.map);

		mapview.map_provider = lychee.map_provider;
	} else {

		if (mapview.map_provider !== lychee.map_provider) {

			// removew all layers
			mapview.map.eachLayer(function (layer) {
				mapview.map.removeLayer(layer);
			});

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(mapview.map);

			mapview.map_provider = lychee.map_provider;
		} else {

			// Mapview has already shown data -> remove only photoLayer showing photos
			mapview.photoLayer.clear();
		}

		// Reset min/max lat/lgn Values
		mapview.min_lat = null;
		mapview.max_lat = null;
		mapview.min_lng = null;
		mapview.max_lng = null;
	}

	// Define how the photos on the map should look like
	mapview.photoLayer = L.photo.cluster().on('click', function (e) {
		var photo = e.layer.photo;
		var template = "";

		// Retina version if available
		if (photo.url2x !== "") {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'srcset="{url} 1x, {url2x} 2x" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), '</span><p>{takedate}</p></div>');
		} else {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), '</span><p>{takedate}</p></div>');
		}

		e.layer.bindPopup(L.Util.template(template, photo), {
			minWidth: 400
		}).openPopup();
	});

	// Adjusts zoom and position of map to show all images
	var updateZoom = function updateZoom() {
		if (mapview.min_lat && mapview.min_lng && mapview.max_lat && mapview.max_lng) {
			var dist_lat = mapview.max_lat - mapview.min_lat;
			var dist_lng = mapview.max_lng - mapview.min_lng;
			mapview.map.fitBounds([[mapview.min_lat - 0.1 * dist_lat, mapview.min_lng - 0.1 * dist_lng], [mapview.max_lat + 0.1 * dist_lat, mapview.max_lng + 0.1 * dist_lng]]);
		} else {
			mapview.map.fitWorld();
		}
	};

	// Adds photos to the map
	var addPhotosToMap = function addPhotosToMap(album) {

		// check if empty
		if (!album.photos) return;

		var photos = [];

		album.photos.forEach(function (element, index) {
			if (element.latitude || element.longitude) {
				photos.push({
					"lat": parseFloat(element.latitude),
					"lng": parseFloat(element.longitude),
					"thumbnail": element.thumbUrl !== "uploads/thumb/" ? element.thumbUrl : "img/placeholder.png",
					"thumbnail2x": element.thumb2x,
					"url": element.small !== "" ? element.small : element.url,
					"url2x": element.small2x,
					"name": element.title,
					"takedate": element.takedate,
					"albumID": element.album,
					"photoID": element.id
				});

				// Update min/max lat/lng
				if (mapview.min_lat === null || mapview.min_lat > element.latitude) {
					mapview.min_lat = parseFloat(element.latitude);
				}
				if (mapview.min_lng === null || mapview.min_lng > element.longitude) {
					mapview.min_lng = parseFloat(element.longitude);
				}
				if (mapview.max_lat === null || mapview.max_lat < element.latitude) {
					mapview.max_lat = parseFloat(element.latitude);
				}
				if (mapview.max_lng === null || mapview.max_lng < element.longitude) {
					mapview.max_lng = parseFloat(element.longitude);
				}
			}
		});

		// Add Photos to map
		mapview.photoLayer.add(photos).addTo(mapview.map);

		// Update Zoom and Position
		updateZoom();
	};

	// Call backend, retrieve information of photos and display them
	// This function is called recursively to retrieve data for sub-albums
	// Possible enhancement could be to only have a single ajax call
	var getAlbumData = function getAlbumData(_albumID) {
		var _includeSubAlbums = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

		if (_albumID !== '' && _albumID !== null) {
			// _ablumID has been to a specific album
			var _params = {
				albumID: _albumID,
				includeSubAlbums: _includeSubAlbums,
				password: ''
			};

			api.post('Album::getPositionData', _params, function (data) {

				if (data === 'Warning: Wrong password!') {
					password.getDialog(_albumID, function () {

						_params.password = password.value;

						api.post('Album::getPositionData', _params, function (_data) {
							addPhotosToMap(_data);
							mapview.title(_albumID, _data.title);
						});
					});
				} else {
					addPhotosToMap(data);
					mapview.title(_albumID, data.title);
				}
			});
		} else {
			// AlbumID is empty -> fetch all photos of all albums
			// _ablumID has been to a specific album
			var _params2 = {
				includeSubAlbums: _includeSubAlbums,
				password: ''
			};

			api.post('Albums::getPositionData', _params2, function (data) {

				if (data === 'Warning: Wrong password!') {
					password.getDialog(_albumID, function () {

						_params2.password = password.value;

						api.post('Albums::getPositionData', _params2, function (_data) {
							addPhotosToMap(_data);
							mapview.title(_albumID, _data.title);
						});
					});
				} else {
					addPhotosToMap(data);
					mapview.title(_albumID, data.title);
				}
			});
		}
	};

	// If subalbums not being included and album.json already has all data
	// -> we can reuse it
	if (lychee.map_include_subalbums === false && album.json !== null && album.json.photos !== null) {

		addPhotosToMap(album.json);
	} else {

		// Not all needed data has been  preloaded - we need to load everything
		getAlbumData(albumID, lychee.map_include_subalbums);
	}

	// Update Zoom and Position once more (for empty map)
	updateZoom();
};

mapview.close = function () {

	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	lychee.animate($('#mapview'), 'fadeOut');
	$('#mapview').hide();
	header.setMode('album');

	// Make album focussable
	tabindex.makeFocusable(lychee.content);
};

mapview.goto = function (elem) {

	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	var photoID = elem.attr('data-id');
	var albumID = elem.attr('data-album-id');

	if (albumID == 'null') albumID = 0;

	if (album.json == null || albumID !== album.json.id) {
		album.refresh();
	}

	lychee.goto(albumID + '/' + photoID);
};

lychee.locale = {

	'USERNAME': 'username',
	'PASSWORD': 'password',
	'ENTER': 'Enter',
	'CANCEL': 'Cancel',
	'SIGN_IN': 'Sign In',
	'CLOSE': 'Close',

	'SETTINGS': 'Settings',
	'USERS': 'Users',
	'U2F': 'U2F',
	'SHARING': 'Sharing',
	'CHANGE_LOGIN': 'Change Login',
	'CHANGE_SORTING': 'Change Sorting',
	'SET_DROPBOX': 'Set Dropbox',
	'ABOUT_LYCHEE': 'About Lychee',
	'DIAGNOSTICS': 'Diagnostics',
	'DIAGNOSTICS_GET_SIZE': 'Request space usage',
	'LOGS': 'Show Logs',
	'CLEAN_LOGS': 'Clean Noise',
	'SIGN_OUT': 'Sign Out',
	'UPDATE_AVAILABLE': 'Update available!',
	'MIGRATION_AVAILABLE': 'Migration available!',
	'CHECK_FOR_UPDATE': 'Check for updates',
	'DEFAULT_LICENSE': 'Default License for new uploads:',
	'SET_LICENSE': 'Set License',
	'SET_OVERLAY_TYPE': 'Set Overlay',
	'SET_MAP_PROVIDER': 'Set OpenStreetMap tiles provider',
	'SAVE_RISK': 'Save my modifications, I accept the Risk!',
	'MORE': 'More',
	'DEFAULT': 'Default',

	'SMART_ALBUMS': 'Smart albums',
	'SHARED_ALBUMS': 'Shared albums',
	'ALBUMS': 'Albums',
	'PHOTOS': 'Pictures',
	'SEARCH_RESULTS': 'Search results',

	'RENAME': 'Rename',
	'RENAME_ALL': 'Rename All',
	'MERGE': 'Merge',
	'MERGE_ALL': 'Merge All',
	'MAKE_PUBLIC': 'Make Public',
	'SHARE_ALBUM': 'Share Album',
	'SHARE_PHOTO': 'Share Photo',
	'SHARE_WITH': 'Share with...',
	'DOWNLOAD_ALBUM': 'Download Album',
	'ABOUT_ALBUM': 'About Album',
	'DELETE_ALBUM': 'Delete Album',
	'FULLSCREEN_ENTER': 'Enter Fullscreen',
	'FULLSCREEN_EXIT': 'Exit Fullscreen',

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
	'CLOSE_MAP': 'Close Map',

	'ADD': 'Add',
	'MOVE': 'Move',
	'MOVE_ALL': 'Move All',
	'DUPLICATE': 'Duplicate',
	'DUPLICATE_ALL': 'Duplicate All',
	'COPY_TO': 'Copy to...',
	'COPY_ALL_TO': 'Copy All to...',
	'DELETE': 'Delete',
	'DELETE_ALL': 'Delete All',
	'DOWNLOAD': 'Download',
	'DOWNLOAD_MEDIUM': 'Download medium size',
	'DOWNLOAD_SMALL': 'Download small size',
	'UPLOAD_PHOTO': 'Upload Photo',
	'IMPORT_LINK': 'Import from Link',
	'IMPORT_DROPBOX': 'Import from Dropbox',
	'IMPORT_SERVER': 'Import from Server',
	'NEW_ALBUM': 'New Album',
	'NEW_TAG_ALBUM': 'New Tag Album',

	'TITLE_NEW_ALBUM': 'Enter a title for the new album:',
	'UNTITLED': 'Untilted',
	'UNSORTED': 'Unsorted',
	'STARRED': 'Starred',
	'RECENT': 'Recent',
	'PUBLIC': 'Public',
	'NUM_PHOTOS': 'Photos',

	'CREATE_ALBUM': 'Create Album',
	'CREATE_TAG_ALBUM': 'Create Tag Album',

	'STAR_PHOTO': 'Star Photo',
	'STAR': 'Star',
	'STAR_ALL': 'Star All',
	'TAGS': 'Tags',
	'TAGS_ALL': 'Tags All',
	'UNSTAR_PHOTO': 'Unstar Photo',

	'FULL_PHOTO': 'Full Photo',
	'ABOUT_PHOTO': 'About Photo',
	'DISPLAY_FULL_MAP': 'Map',
	'DIRECT_LINK': 'Direct Link',
	'DIRECT_LINKS': 'Direct Links',

	'ALBUM_ABOUT': 'About',
	'ALBUM_BASICS': 'Basics',
	'ALBUM_TITLE': 'Title',
	'ALBUM_NEW_TITLE': 'Enter a new title for this album:',
	'ALBUMS_NEW_TITLE_1': 'Enter a title for all',
	'ALBUMS_NEW_TITLE_2': 'selected albums:',
	'ALBUM_SET_TITLE': 'Set Title',
	'ALBUM_DESCRIPTION': 'Description',
	'ALBUM_SHOW_TAGS': 'Tags to show',
	'ALBUM_NEW_DESCRIPTION': 'Enter a new description for this album:',
	'ALBUM_SET_DESCRIPTION': 'Set Description',
	'ALBUM_NEW_SHOWTAGS': 'Enter tags of photos that will be visible in this album:',
	'ALBUM_SET_SHOWTAGS': 'Set tags to show',
	'ALBUM_ALBUM': 'Album',
	'ALBUM_CREATED': 'Created',
	'ALBUM_IMAGES': 'Images',
	'ALBUM_VIDEOS': 'Videos',
	'ALBUM_SHARING': 'Share',
	'ALBUM_OWNER': 'Owner',
	'ALBUM_SHR_YES': 'YES',
	'ALBUM_SHR_NO': 'No',
	'ALBUM_PUBLIC': 'Public',
	'ALBUM_PUBLIC_EXPL': 'Album can be viewed by others, subject to the restrictions below.',
	'ALBUM_FULL': 'Full size (v4 only)',
	'ALBUM_FULL_EXPL': 'Full size pictures are available',
	'ALBUM_HIDDEN': 'Hidden',
	'ALBUM_HIDDEN_EXPL': 'Only people with the direct link can view this album.',
	'ALBUM_DOWNLOADABLE': 'Downloadable',
	'ALBUM_DOWNLOADABLE_EXPL': 'Visitors of your Lychee can download this album.',
	'ALBUM_SHARE_BUTTON_VISIBLE': 'Share button is visible',
	'ALBUM_SHARE_BUTTON_VISIBLE_EXPL': 'Display social media sharing links.',
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
	'ALBUM_REUSE': "Reuse",
	'ALBUM_LICENSE': 'License',
	'ALBUM_SET_LICENSE': 'Set License',
	'ALBUM_LICENSE_HELP': 'Need help choosing?',
	'ALBUM_LICENSE_NONE': 'None',
	'ALBUM_RESERVED': 'All Rights Reserved',
	'ALBUM_SET_ORDER': 'Set Order',
	'ALBUM_ORDERING': 'Order by',

	'PHOTO_ABOUT': 'About',
	'PHOTO_BASICS': 'Basics',
	'PHOTO_TITLE': 'Title',
	'PHOTO_NEW_TITLE': 'Enter a new title for this photo:',
	'PHOTO_SET_TITLE': 'Set Title',
	'PHOTO_UPLOADED': 'Uploaded',
	'PHOTO_DESCRIPTION': 'Description',
	'PHOTO_NEW_DESCRIPTION': 'Enter a new description for this photo:',
	'PHOTO_SET_DESCRIPTION': 'Set Description',
	'PHOTO_NEW_LICENSE': 'Add a License',
	'PHOTO_SET_LICENSE': 'Set License',
	'PHOTO_REUSE': 'Reuse',
	'PHOTO_LICENSE': 'License',
	'PHOTO_LICENSE_HELP': 'Need help choosing?',
	'PHOTO_LICENSE_NONE': 'None',
	'PHOTO_RESERVED': 'All Rights Reserved',
	'PHOTO_IMAGE': 'Image',
	'PHOTO_VIDEO': 'Video',
	'PHOTO_SIZE': 'Size',
	'PHOTO_FORMAT': 'Format',
	'PHOTO_RESOLUTION': 'Resolution',
	'PHOTO_DURATION': 'Duration',
	'PHOTO_FPS': 'Frame rate',
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
	'PHOTO_LENS': 'Lens',
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
	'PHOTO_PUBLIC': 'Public',
	'PHOTO_PUBLIC_EXPL': 'Photo can be viewed by others, subject to the restrictions below.',
	'PHOTO_FULL': 'Original',
	'PHOTO_FULL_EXPL': 'Full-resolution picture is available.',
	'PHOTO_HIDDEN': 'Hidden',
	'PHOTO_HIDDEN_EXPL': 'Only people with the direct link can view this photo.',
	'PHOTO_DOWNLOADABLE': 'Downloadable',
	'PHOTO_DOWNLOADABLE_EXPL': 'Visitors of your gallery can download this photo.',
	'PHOTO_SHARE_BUTTON_VISIBLE': 'Share button is visible',
	'PHOTO_SHARE_BUTTON_VISIBLE_EXPL': 'Display social media sharing links.',
	'PHOTO_PASSWORD_PROT': 'Password protected',
	'PHOTO_PASSWORD_PROT_EXPL': 'Photo only accessible with a valid password.',
	'PHOTO_EDIT_SHARING_TEXT': 'The sharing properties of this photo will be changed to the following:',
	'PHOTO_NO_EDIT_SHARING_TEXT': 'Because this photo is located in a public album, it inherits that album\'s visibility settings.  Its current visibility is shown below for informational purposes only.',
	'PHOTO_EDIT_GLOBAL_SHARING_TEXT': 'The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.',
	'PHOTO_SHARING_CONFIRM': 'Save',
	'PHOTO_LOCATION': 'Location',
	'PHOTO_LATITUDE': 'Latitude',
	'PHOTO_LONGITUDE': 'Longitude',
	'PHOTO_ALTITUDE': 'Altitude',
	'PHOTO_IMGDIRECTION': 'Direction',

	'LOADING': 'Loading',
	'ERROR': 'Error',
	'ERROR_TEXT': 'Whoops, it looks like something went wrong. Please reload the site and try again!',
	'ERROR_DB_1': 'Unable to connect to host database because access was denied. Double-check your host, username and password and ensure that access from your current location is permitted.',
	'ERROR_DB_2': 'Unable to create the database. Double-check your host, username and password and ensure that the specified user has the rights to modify and add content to the database.',
	'ERROR_CONFIG_FILE': "Unable to save this configuration. Permission denied in <b>'data/'</b>. Please set the read, write and execute rights for others in <b>'data/'</b> and <b>'uploads/'</b>. Take a look at the readme for more information.",
	'ERROR_UNKNOWN': 'Something unexpected happened. Please try again and check your installation and server. Take a look at the readme for more information.',
	'ERROR_LOGIN': 'Unable to save login. Please try again with another username and password!',
	'ERROR_MAP_DEACTIVATED': 'Map functionality has been deactivated under settings.',
	'ERROR_SEARCH_DEACTIVATED': 'Search functionality has been deactivated under settings.',
	'SUCCESS': 'OK',
	'RETRY': 'Retry',

	'SETTINGS_WARNING': 'Changing these advanced settings can be harmful to the stability, security and performance of this application. You should only modify them if you are sure of what you are doing.',
	'SETTINGS_SUCCESS_LOGIN': 'Login Info updated.',
	'SETTINGS_SUCCESS_SORT': 'Sorting order updated.',
	'SETTINGS_SUCCESS_DROPBOX': 'Dropbox Key updated.',
	'SETTINGS_SUCCESS_LANG': 'Language updated',
	'SETTINGS_SUCCESS_LAYOUT': 'Layout updated',
	'SETTINGS_SUCCESS_IMAGE_OVERLAY': 'EXIF Overlay setting updated',
	'SETTINGS_SUCCESS_PUBLIC_SEARCH': 'Public search updated',
	'SETTINGS_SUCCESS_LICENSE': 'Default license updated',
	'SETTINGS_SUCCESS_MAP_DISPLAY': 'Map display settings updated',
	'SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC': 'Map display settings for public albums updated',
	'SETTINGS_SUCCESS_MAP_PROVIDER': 'Map provider settings updated',

	'SETTINGS_SUCCESS_CSS': 'CSS updated',
	'SETTINGS_SUCCESS_UPDATE': 'Settings updated with success',

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
	'LOGIN_PASSWORD_CONFIRM': 'Confirm Password',
	'LOGIN_CREATE': 'Create Login',

	'PASSWORD_TITLE': 'Enter your current username and password:',
	'USERNAME_CURRENT': 'Current Username',
	'PASSWORD_CURRENT': 'Current Password',
	'PASSWORD_TEXT': 'Your username and password will be changed to the following:',
	'PASSWORD_CHANGE': 'Change Login',

	'EDIT_SHARING_TITLE': 'Edit Sharing',
	'EDIT_SHARING_TEXT': 'The sharing-properties of this album will be changed to the following:',
	'SHARE_ALBUM_TEXT': 'This album will be shared with the following properties:',
	'ALBUM_SHARING_CONFIRM': 'Save',

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

	'CSS_TEXT': 'Personalize your CSS:',
	'CSS_TITLE': 'Change CSS',

	'LAYOUT_TYPE': 'Layout of photos:',
	'LAYOUT_SQUARES': 'Square thumbnails',
	'LAYOUT_JUSTIFIED': 'With aspect, justified',
	'LAYOUT_UNJUSTIFIED': 'With aspect, unjustified',
	'SET_LAYOUT': 'Change layout',
	'PUBLIC_SEARCH_TEXT': 'Public search allowed:',

	'IMAGE_OVERLAY_TEXT': 'Display image overlay by default:',

	'OVERLAY_TYPE': 'Data to use in image overlay:',
	'OVERLAY_EXIF': 'Photo EXIF data',
	'OVERLAY_DESCRIPTION': 'Photo description',
	'OVERLAY_DATE': 'Photo date taken',

	'MAP_PROVIDER': 'Provider of OpenStreetMap tiles:',
	'MAP_PROVIDER_WIKIMEDIA': 'Wikimedia',
	'MAP_PROVIDER_OSM_ORG': 'OpenStreetMap.org (no retina)',
	'MAP_PROVIDER_OSM_DE': 'OpenStreetMap.de (no retina)',
	'MAP_PROVIDER_OSM_FR': 'OpenStreetMap.fr (no retina)',
	'MAP_PROVIDER_RRZE': 'University of Erlangen, Germany (only retina)',

	'MAP_DISPLAY_TEXT': 'Enable maps (provided by OpenStreetMap):',
	'MAP_DISPLAY_PUBLIC_TEXT': 'Enable maps for public albums (provided by OpenStreetMap):',
	'MAP_INCLUDE_SUBALBUMS_TEXT': 'Include photos of subalbums on map:',
	'LOCATION_DECODING': 'Decode GPS data into location name',
	'LOCATION_SHOW': 'Show location name',
	'LOCATION_SHOW_PUBLIC': 'Show location name for public mode',

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
	'UPLOAD_IMPORT_SERVER_EMPT': 'Could not start import because the folder was empty!',

	'ABOUT_SUBTITLE': 'Self-hosted photo-management done right',
	'ABOUT_DESCRIPTION': 'is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely.',

	'URL_COPY_TO_CLIPBOARD': 'Copy to clipboard',
	'URL_COPIED_TO_CLIPBOARD': 'Copied URL to clipboard!',
	'PHOTO_DIRECT_LINKS_TO_IMAGES': 'Direct links to image files:',
	'PHOTO_MEDIUM': 'Medium',
	'PHOTO_MEDIUM_HIDPI': 'Medium HiDPI',
	'PHOTO_SMALL': 'Thumb',
	'PHOTO_SMALL_HIDPI': 'Thumb HiDPI',
	'PHOTO_THUMB': 'Square thumb',
	'PHOTO_THUMB_HIDPI': 'Square thumb HiDPI',
	'PHOTO_LIVE_VIDEO': 'Video part of live-photo',
	'PHOTO_VIEW': 'Lychee Photo View:'
};

/**
 * @description Helper class to manage tabindex
 */

var tabindex = {

	offset_for_header: 100,
	next_tab_index: 100

};

tabindex.saveSettings = function (elem) {

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		// TODO: shorter notation
		a = $(e).attr("tabindex");
		$(this).data("tabindex-saved", a);
	});
};

tabindex.restoreSettings = function (elem) {

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		// TODO: shorter notation
		a = $(e).data("tabindex-saved");
		$(e).attr("tabindex", a);
	});
};

tabindex.makeUnfocusable = function (elem) {
	var saveFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to -1 (i.e. make is not focussable)
	tmp.each(function (i, e) {
		$(e).attr("tabindex", "-1");
		// Save which element had focus before we make it unfocusable
		if (saveFocusElement && $(e).is(":focus")) {
			$(e).data("tabindex-focus", true);
			// Remove focus
			$(e).blur();
		}
	});

	// Disable input fields
	$(elem).find("input").attr("disabled", "disabled");
};

tabindex.makeFocusable = function (elem) {
	var restoreFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[data-tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		$(e).attr("tabindex", $(e).data("tabindex"));
		// restore focus elemente if wanted
		if (restoreFocusElement) {
			if ($(e).data("tabindex-focus") && lychee.active_focus_on_page_load) {
				$(e).focus();
				$(e).removeData("tabindex-focus");
			}
		}
	});

	// Enable input fields
	$(elem).find("input").removeAttr("disabled");
};

tabindex.get_next_tab_index = function () {

	tabindex.next_tab_index = tabindex.next_tab_index + 1;

	return tabindex.next_tab_index - 1;
};

tabindex.reset = function () {

	tabindex.next_tab_index = tabindex.offset_for_header;
};
(function (window, factory) {
	var basicContext = factory(window, window.document);
	window.basicContext = basicContext;
	if ((typeof module === "undefined" ? "undefined" : _typeof(module)) == 'object' && module.exports) {
		module.exports = basicContext;
	}
})(window, function l(window, document) {

	var ITEM = 'item',
	    SEPARATOR = 'separator';

	var dom = function dom() {
		var elem = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';


		return document.querySelector('.basicContext ' + elem);
	};

	var valid = function valid() {
		var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};


		var emptyItem = Object.keys(item).length === 0 ? true : false;

		if (emptyItem === true) item.type = SEPARATOR;
		if (item.type == null) item.type = ITEM;
		if (item.class == null) item.class = '';
		if (item.visible !== false) item.visible = true;
		if (item.icon == null) item.icon = null;
		if (item.title == null) item.title = 'Undefined';

		// Add disabled class when item disabled
		if (item.disabled !== true) item.disabled = false;
		if (item.disabled === true) item.class += ' basicContext__item--disabled';

		// Item requires a function when
		// it's not a separator and not disabled
		if (item.fn == null && item.type !== SEPARATOR && item.disabled === false) {

			console.warn("Missing fn for item '" + item.title + "'");
			return false;
		}

		return true;
	};

	var buildItem = function buildItem(item, num) {

		var html = '',
		    span = '';

		// Parse and validate item
		if (valid(item) === false) return '';

		// Skip when invisible
		if (item.visible === false) return '';

		// Give item a unique number
		item.num = num;

		// Generate span/icon-element
		if (item.icon !== null) span = "<span class='basicContext__icon " + item.icon + "'></span>";

		// Generate item
		if (item.type === ITEM) {

			html = "\n\t\t       <tr class='basicContext__item " + item.class + "'>\n\t\t           <td class='basicContext__data' data-num='" + item.num + "'>" + span + item.title + "</td>\n\t\t       </tr>\n\t\t       ";
		} else if (item.type === SEPARATOR) {

			html = "\n\t\t       <tr class='basicContext__item basicContext__item--separator'></tr>\n\t\t       ";
		}

		return html;
	};

	var build = function build(items) {

		var html = '';

		html += "\n\t        <div class='basicContextContainer'>\n\t            <div class='basicContext'>\n\t                <table>\n\t                    <tbody>\n\t        ";

		items.forEach(function (item, i) {
			return html += buildItem(item, i);
		});

		html += "\n\t                    </tbody>\n\t                </table>\n\t            </div>\n\t        </div>\n\t        ";

		return html;
	};

	var getNormalizedEvent = function getNormalizedEvent() {
		var e = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};


		var pos = {
			x: e.clientX,
			y: e.clientY
		};

		if (e.type === 'touchend' && (pos.x == null || pos.y == null)) {

			// We need to capture clientX and clientY from original event
			// when the event 'touchend' does not return the touch position

			var touches = e.changedTouches;

			if (touches != null && touches.length > 0) {
				pos.x = touches[0].clientX;
				pos.y = touches[0].clientY;
			}
		}

		// Position unknown
		if (pos.x == null || pos.x < 0) pos.x = 0;
		if (pos.y == null || pos.y < 0) pos.y = 0;

		return pos;
	};

	var getPosition = function getPosition(e, context) {

		// Get the click position
		var normalizedEvent = getNormalizedEvent(e);

		// Set the initial position
		var x = normalizedEvent.x,
		    y = normalizedEvent.y;

		// Get size of browser
		var browserSize = {
			width: window.innerWidth,
			height: window.innerHeight

			// Get size of context
		};var contextSize = {
			width: context.offsetWidth,
			height: context.offsetHeight

			// Fix position based on context and browser size
		};if (x + contextSize.width > browserSize.width) x = x - (x + contextSize.width - browserSize.width);
		if (y + contextSize.height > browserSize.height) y = y - (y + contextSize.height - browserSize.height);

		// Make context scrollable and start at the top of the browser
		// when context is higher than the browser
		if (contextSize.height > browserSize.height) {
			y = 0;
			context.classList.add('basicContext--scrollable');
		}

		// Calculate the relative position of the mouse to the context
		var rx = normalizedEvent.x - x,
		    ry = normalizedEvent.y - y;

		return { x: x, y: y, rx: rx, ry: ry };
	};

	var bind = function bind() {
		var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};


		if (item.fn == null) return false;
		if (item.visible === false) return false;
		if (item.disabled === true) return false;

		dom("td[data-num='" + item.num + "']").onclick = item.fn;
		dom("td[data-num='" + item.num + "']").oncontextmenu = item.fn;

		return true;
	};

	var show = function show(items, e, fnClose, fnCallback) {

		// Build context
		var html = build(items);

		// Add context to the body
		document.body.insertAdjacentHTML('beforeend', html);

		// Cache the context
		var context = dom();

		// Calculate position
		var position = getPosition(e, context);

		// Set position
		context.style.left = position.x + "px";
		context.style.top = position.y + "px";
		context.style.transformOrigin = position.rx + "px " + position.ry + "px";
		context.style.opacity = 1;

		// Close fn fallback
		if (fnClose == null) fnClose = close;

		// Bind click on background
		context.parentElement.onclick = fnClose;
		context.parentElement.oncontextmenu = fnClose;

		// Bind click on items
		items.forEach(bind);

		// Do not trigger default event or further propagation
		if (typeof e.preventDefault === 'function') e.preventDefault();
		if (typeof e.stopPropagation === 'function') e.stopPropagation();

		// Call callback when a function
		if (typeof fnCallback === 'function') fnCallback();

		return true;
	};

	var visible = function visible() {

		var elem = dom();

		return !(elem == null || elem.length === 0);
	};

	var close = function close() {

		if (visible() === false) return false;

		var container = document.querySelector('.basicContextContainer');

		container.parentElement.removeChild(container);

		return true;
	};

	return {
		ITEM: ITEM,
		SEPARATOR: SEPARATOR,
		show: show,
		visible: visible,
		close: close
	};
});