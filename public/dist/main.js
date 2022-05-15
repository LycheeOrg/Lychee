/*! jQuery v3.6.0 | (c) OpenJS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(C,e){"use strict";var t=[],r=Object.getPrototypeOf,s=t.slice,g=t.flat?function(e){return t.flat.call(e)}:function(e){return t.concat.apply([],e)},u=t.push,i=t.indexOf,n={},o=n.toString,v=n.hasOwnProperty,a=v.toString,l=a.call(Object),y={},m=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType&&"function"!=typeof e.item},x=function(e){return null!=e&&e===e.window},E=C.document,c={type:!0,src:!0,nonce:!0,noModule:!0};function b(e,t,n){var r,i,o=(n=n||E).createElement("script");if(o.text=e,t)for(r in c)(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function w(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?n[o.call(e)]||"object":typeof e}var f="3.6.0",S=function(e,t){return new S.fn.init(e,t)};function p(e){var t=!!e&&"length"in e&&e.length,n=w(e);return!m(e)&&!x(e)&&("array"===n||0===t||"number"==typeof t&&0<t&&t-1 in e)}S.fn=S.prototype={jquery:f,constructor:S,length:0,toArray:function(){return s.call(this)},get:function(e){return null==e?s.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=S.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return S.each(this,e)},map:function(n){return this.pushStack(S.map(this,function(e,t){return n.call(e,t,e)}))},slice:function(){return this.pushStack(s.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(S.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(S.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(0<=n&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:u,sort:t.sort,splice:t.splice},S.extend=S.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||m(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(S.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||S.isPlainObject(n)?n:{},i=!1,a[t]=S.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},S.extend({expando:"jQuery"+(f+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==o.call(e))&&(!(t=r(e))||"function"==typeof(n=v.call(t,"constructor")&&t.constructor)&&a.call(n)===l)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t,n){b(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(p(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},makeArray:function(e,t){var n=t||[];return null!=e&&(p(Object(e))?S.merge(n,"string"==typeof e?[e]:e):u.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:i.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},map:function(e,t,n){var r,i,o=0,a=[];if(p(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&a.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&a.push(i);return g(a)},guid:1,support:y}),"function"==typeof Symbol&&(S.fn[Symbol.iterator]=t[Symbol.iterator]),S.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){n["[object "+t+"]"]=t.toLowerCase()});var d=function(n){var e,d,b,o,i,h,f,g,w,u,l,T,C,a,E,v,s,c,y,S="sizzle"+1*new Date,p=n.document,k=0,r=0,m=ue(),x=ue(),A=ue(),N=ue(),j=function(e,t){return e===t&&(l=!0),0},D={}.hasOwnProperty,t=[],q=t.pop,L=t.push,H=t.push,O=t.slice,P=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},R="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",I="(?:\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",W="\\["+M+"*("+I+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+I+"))|)"+M+"*\\]",F=":("+I+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+W+")*)|.*)\\)|)",B=new RegExp(M+"+","g"),$=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),_=new RegExp("^"+M+"*,"+M+"*"),z=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),U=new RegExp(M+"|>"),X=new RegExp(F),V=new RegExp("^"+I+"$"),G={ID:new RegExp("^#("+I+")"),CLASS:new RegExp("^\\.("+I+")"),TAG:new RegExp("^("+I+"|[*])"),ATTR:new RegExp("^"+W),PSEUDO:new RegExp("^"+F),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+R+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},Y=/HTML$/i,Q=/^(?:input|select|textarea|button)$/i,J=/^h\d$/i,K=/^[^{]+\{\s*\[native \w/,Z=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,ee=/[+~]/,te=new RegExp("\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\([^\\r\\n\\f])","g"),ne=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},re=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ie=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},oe=function(){T()},ae=be(function(e){return!0===e.disabled&&"fieldset"===e.nodeName.toLowerCase()},{dir:"parentNode",next:"legend"});try{H.apply(t=O.call(p.childNodes),p.childNodes),t[p.childNodes.length].nodeType}catch(e){H={apply:t.length?function(e,t){L.apply(e,O.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function se(t,e,n,r){var i,o,a,s,u,l,c,f=e&&e.ownerDocument,p=e?e.nodeType:9;if(n=n||[],"string"!=typeof t||!t||1!==p&&9!==p&&11!==p)return n;if(!r&&(T(e),e=e||C,E)){if(11!==p&&(u=Z.exec(t)))if(i=u[1]){if(9===p){if(!(a=e.getElementById(i)))return n;if(a.id===i)return n.push(a),n}else if(f&&(a=f.getElementById(i))&&y(e,a)&&a.id===i)return n.push(a),n}else{if(u[2])return H.apply(n,e.getElementsByTagName(t)),n;if((i=u[3])&&d.getElementsByClassName&&e.getElementsByClassName)return H.apply(n,e.getElementsByClassName(i)),n}if(d.qsa&&!N[t+" "]&&(!v||!v.test(t))&&(1!==p||"object"!==e.nodeName.toLowerCase())){if(c=t,f=e,1===p&&(U.test(t)||z.test(t))){(f=ee.test(t)&&ye(e.parentNode)||e)===e&&d.scope||((s=e.getAttribute("id"))?s=s.replace(re,ie):e.setAttribute("id",s=S)),o=(l=h(t)).length;while(o--)l[o]=(s?"#"+s:":scope")+" "+xe(l[o]);c=l.join(",")}try{return H.apply(n,f.querySelectorAll(c)),n}catch(e){N(t,!0)}finally{s===S&&e.removeAttribute("id")}}}return g(t.replace($,"$1"),e,n,r)}function ue(){var r=[];return function e(t,n){return r.push(t+" ")>b.cacheLength&&delete e[r.shift()],e[t+" "]=n}}function le(e){return e[S]=!0,e}function ce(e){var t=C.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function fe(e,t){var n=e.split("|"),r=n.length;while(r--)b.attrHandle[n[r]]=t}function pe(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function de(t){return function(e){return"input"===e.nodeName.toLowerCase()&&e.type===t}}function he(n){return function(e){var t=e.nodeName.toLowerCase();return("input"===t||"button"===t)&&e.type===n}}function ge(t){return function(e){return"form"in e?e.parentNode&&!1===e.disabled?"label"in e?"label"in e.parentNode?e.parentNode.disabled===t:e.disabled===t:e.isDisabled===t||e.isDisabled!==!t&&ae(e)===t:e.disabled===t:"label"in e&&e.disabled===t}}function ve(a){return le(function(o){return o=+o,le(function(e,t){var n,r=a([],e.length,o),i=r.length;while(i--)e[n=r[i]]&&(e[n]=!(t[n]=e[n]))})})}function ye(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}for(e in d=se.support={},i=se.isXML=function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;return!Y.test(t||n&&n.nodeName||"HTML")},T=se.setDocument=function(e){var t,n,r=e?e.ownerDocument||e:p;return r!=C&&9===r.nodeType&&r.documentElement&&(a=(C=r).documentElement,E=!i(C),p!=C&&(n=C.defaultView)&&n.top!==n&&(n.addEventListener?n.addEventListener("unload",oe,!1):n.attachEvent&&n.attachEvent("onunload",oe)),d.scope=ce(function(e){return a.appendChild(e).appendChild(C.createElement("div")),"undefined"!=typeof e.querySelectorAll&&!e.querySelectorAll(":scope fieldset div").length}),d.attributes=ce(function(e){return e.className="i",!e.getAttribute("className")}),d.getElementsByTagName=ce(function(e){return e.appendChild(C.createComment("")),!e.getElementsByTagName("*").length}),d.getElementsByClassName=K.test(C.getElementsByClassName),d.getById=ce(function(e){return a.appendChild(e).id=S,!C.getElementsByName||!C.getElementsByName(S).length}),d.getById?(b.filter.ID=function(e){var t=e.replace(te,ne);return function(e){return e.getAttribute("id")===t}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n=t.getElementById(e);return n?[n]:[]}}):(b.filter.ID=function(e){var n=e.replace(te,ne);return function(e){var t="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return t&&t.value===n}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),b.find.TAG=d.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):d.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},b.find.CLASS=d.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&E)return t.getElementsByClassName(e)},s=[],v=[],(d.qsa=K.test(C.querySelectorAll))&&(ce(function(e){var t;a.appendChild(e).innerHTML="<a id='"+S+"'></a><select id='"+S+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&v.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||v.push("\\["+M+"*(?:value|"+R+")"),e.querySelectorAll("[id~="+S+"-]").length||v.push("~="),(t=C.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||v.push("\\["+M+"*name"+M+"*="+M+"*(?:''|\"\")"),e.querySelectorAll(":checked").length||v.push(":checked"),e.querySelectorAll("a#"+S+"+*").length||v.push(".#.+[+~]"),e.querySelectorAll("\\\f"),v.push("[\\r\\n\\f]")}),ce(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=C.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&v.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&v.push(":enabled",":disabled"),a.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&v.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),v.push(",.*:")})),(d.matchesSelector=K.test(c=a.matches||a.webkitMatchesSelector||a.mozMatchesSelector||a.oMatchesSelector||a.msMatchesSelector))&&ce(function(e){d.disconnectedMatch=c.call(e,"*"),c.call(e,"[s!='']:x"),s.push("!=",F)}),v=v.length&&new RegExp(v.join("|")),s=s.length&&new RegExp(s.join("|")),t=K.test(a.compareDocumentPosition),y=t||K.test(a.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},j=t?function(e,t){if(e===t)return l=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!d.sortDetached&&t.compareDocumentPosition(e)===n?e==C||e.ownerDocument==p&&y(p,e)?-1:t==C||t.ownerDocument==p&&y(p,t)?1:u?P(u,e)-P(u,t):0:4&n?-1:1)}:function(e,t){if(e===t)return l=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e==C?-1:t==C?1:i?-1:o?1:u?P(u,e)-P(u,t):0;if(i===o)return pe(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?pe(a[r],s[r]):a[r]==p?-1:s[r]==p?1:0}),C},se.matches=function(e,t){return se(e,null,null,t)},se.matchesSelector=function(e,t){if(T(e),d.matchesSelector&&E&&!N[t+" "]&&(!s||!s.test(t))&&(!v||!v.test(t)))try{var n=c.call(e,t);if(n||d.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){N(t,!0)}return 0<se(t,C,null,[e]).length},se.contains=function(e,t){return(e.ownerDocument||e)!=C&&T(e),y(e,t)},se.attr=function(e,t){(e.ownerDocument||e)!=C&&T(e);var n=b.attrHandle[t.toLowerCase()],r=n&&D.call(b.attrHandle,t.toLowerCase())?n(e,t,!E):void 0;return void 0!==r?r:d.attributes||!E?e.getAttribute(t):(r=e.getAttributeNode(t))&&r.specified?r.value:null},se.escape=function(e){return(e+"").replace(re,ie)},se.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},se.uniqueSort=function(e){var t,n=[],r=0,i=0;if(l=!d.detectDuplicates,u=!d.sortStable&&e.slice(0),e.sort(j),l){while(t=e[i++])t===e[i]&&(r=n.push(i));while(r--)e.splice(n[r],1)}return u=null,e},o=se.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=o(e)}else if(3===i||4===i)return e.nodeValue}else while(t=e[r++])n+=o(t);return n},(b=se.selectors={cacheLength:50,createPseudo:le,match:G,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(te,ne),e[3]=(e[3]||e[4]||e[5]||"").replace(te,ne),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||se.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&se.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return G.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=h(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(te,ne).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=m[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&m(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(n,r,i){return function(e){var t=se.attr(e,n);return null==t?"!="===r:!r||(t+="","="===r?t===i:"!="===r?t!==i:"^="===r?i&&0===t.indexOf(i):"*="===r?i&&-1<t.indexOf(i):"$="===r?i&&t.slice(-i.length)===i:"~="===r?-1<(" "+t.replace(B," ")+" ").indexOf(i):"|="===r&&(t===i||t.slice(0,i.length+1)===i+"-"))}},CHILD:function(h,e,t,g,v){var y="nth"!==h.slice(0,3),m="last"!==h.slice(-4),x="of-type"===e;return 1===g&&0===v?function(e){return!!e.parentNode}:function(e,t,n){var r,i,o,a,s,u,l=y!==m?"nextSibling":"previousSibling",c=e.parentNode,f=x&&e.nodeName.toLowerCase(),p=!n&&!x,d=!1;if(c){if(y){while(l){a=e;while(a=a[l])if(x?a.nodeName.toLowerCase()===f:1===a.nodeType)return!1;u=l="only"===h&&!u&&"nextSibling"}return!0}if(u=[m?c.firstChild:c.lastChild],m&&p){d=(s=(r=(i=(o=(a=c)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1])&&r[2],a=s&&c.childNodes[s];while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if(1===a.nodeType&&++d&&a===e){i[h]=[k,s,d];break}}else if(p&&(d=s=(r=(i=(o=(a=e)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1]),!1===d)while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if((x?a.nodeName.toLowerCase()===f:1===a.nodeType)&&++d&&(p&&((i=(o=a[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]=[k,d]),a===e))break;return(d-=v)===g||d%g==0&&0<=d/g}}},PSEUDO:function(e,o){var t,a=b.pseudos[e]||b.setFilters[e.toLowerCase()]||se.error("unsupported pseudo: "+e);return a[S]?a(o):1<a.length?(t=[e,e,"",o],b.setFilters.hasOwnProperty(e.toLowerCase())?le(function(e,t){var n,r=a(e,o),i=r.length;while(i--)e[n=P(e,r[i])]=!(t[n]=r[i])}):function(e){return a(e,0,t)}):a}},pseudos:{not:le(function(e){var r=[],i=[],s=f(e.replace($,"$1"));return s[S]?le(function(e,t,n,r){var i,o=s(e,null,r,[]),a=e.length;while(a--)(i=o[a])&&(e[a]=!(t[a]=i))}):function(e,t,n){return r[0]=e,s(r,null,n,i),r[0]=null,!i.pop()}}),has:le(function(t){return function(e){return 0<se(t,e).length}}),contains:le(function(t){return t=t.replace(te,ne),function(e){return-1<(e.textContent||o(e)).indexOf(t)}}),lang:le(function(n){return V.test(n||"")||se.error("unsupported lang: "+n),n=n.replace(te,ne).toLowerCase(),function(e){var t;do{if(t=E?e.lang:e.getAttribute("xml:lang")||e.getAttribute("lang"))return(t=t.toLowerCase())===n||0===t.indexOf(n+"-")}while((e=e.parentNode)&&1===e.nodeType);return!1}}),target:function(e){var t=n.location&&n.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===a},focus:function(e){return e===C.activeElement&&(!C.hasFocus||C.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:ge(!1),disabled:ge(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!b.pseudos.empty(e)},header:function(e){return J.test(e.nodeName)},input:function(e){return Q.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:ve(function(){return[0]}),last:ve(function(e,t){return[t-1]}),eq:ve(function(e,t,n){return[n<0?n+t:n]}),even:ve(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:ve(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:ve(function(e,t,n){for(var r=n<0?n+t:t<n?t:n;0<=--r;)e.push(r);return e}),gt:ve(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=b.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})b.pseudos[e]=de(e);for(e in{submit:!0,reset:!0})b.pseudos[e]=he(e);function me(){}function xe(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function be(s,e,t){var u=e.dir,l=e.next,c=l||u,f=t&&"parentNode"===c,p=r++;return e.first?function(e,t,n){while(e=e[u])if(1===e.nodeType||f)return s(e,t,n);return!1}:function(e,t,n){var r,i,o,a=[k,p];if(n){while(e=e[u])if((1===e.nodeType||f)&&s(e,t,n))return!0}else while(e=e[u])if(1===e.nodeType||f)if(i=(o=e[S]||(e[S]={}))[e.uniqueID]||(o[e.uniqueID]={}),l&&l===e.nodeName.toLowerCase())e=e[u]||e;else{if((r=i[c])&&r[0]===k&&r[1]===p)return a[2]=r[2];if((i[c]=a)[2]=s(e,t,n))return!0}return!1}}function we(i){return 1<i.length?function(e,t,n){var r=i.length;while(r--)if(!i[r](e,t,n))return!1;return!0}:i[0]}function Te(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Ce(d,h,g,v,y,e){return v&&!v[S]&&(v=Ce(v)),y&&!y[S]&&(y=Ce(y,e)),le(function(e,t,n,r){var i,o,a,s=[],u=[],l=t.length,c=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)se(e,t[r],n);return n}(h||"*",n.nodeType?[n]:n,[]),f=!d||!e&&h?c:Te(c,s,d,n,r),p=g?y||(e?d:l||v)?[]:t:f;if(g&&g(f,p,n,r),v){i=Te(p,u),v(i,[],n,r),o=i.length;while(o--)(a=i[o])&&(p[u[o]]=!(f[u[o]]=a))}if(e){if(y||d){if(y){i=[],o=p.length;while(o--)(a=p[o])&&i.push(f[o]=a);y(null,p=[],i,r)}o=p.length;while(o--)(a=p[o])&&-1<(i=y?P(e,a):s[o])&&(e[i]=!(t[i]=a))}}else p=Te(p===t?p.splice(l,p.length):p),y?y(null,t,p,r):H.apply(t,p)})}function Ee(e){for(var i,t,n,r=e.length,o=b.relative[e[0].type],a=o||b.relative[" "],s=o?1:0,u=be(function(e){return e===i},a,!0),l=be(function(e){return-1<P(i,e)},a,!0),c=[function(e,t,n){var r=!o&&(n||t!==w)||((i=t).nodeType?u(e,t,n):l(e,t,n));return i=null,r}];s<r;s++)if(t=b.relative[e[s].type])c=[be(we(c),t)];else{if((t=b.filter[e[s].type].apply(null,e[s].matches))[S]){for(n=++s;n<r;n++)if(b.relative[e[n].type])break;return Ce(1<s&&we(c),1<s&&xe(e.slice(0,s-1).concat({value:" "===e[s-2].type?"*":""})).replace($,"$1"),t,s<n&&Ee(e.slice(s,n)),n<r&&Ee(e=e.slice(n)),n<r&&xe(e))}c.push(t)}return we(c)}return me.prototype=b.filters=b.pseudos,b.setFilters=new me,h=se.tokenize=function(e,t){var n,r,i,o,a,s,u,l=x[e+" "];if(l)return t?0:l.slice(0);a=e,s=[],u=b.preFilter;while(a){for(o in n&&!(r=_.exec(a))||(r&&(a=a.slice(r[0].length)||a),s.push(i=[])),n=!1,(r=z.exec(a))&&(n=r.shift(),i.push({value:n,type:r[0].replace($," ")}),a=a.slice(n.length)),b.filter)!(r=G[o].exec(a))||u[o]&&!(r=u[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?se.error(e):x(e,s).slice(0)},f=se.compile=function(e,t){var n,v,y,m,x,r,i=[],o=[],a=A[e+" "];if(!a){t||(t=h(e)),n=t.length;while(n--)(a=Ee(t[n]))[S]?i.push(a):o.push(a);(a=A(e,(v=o,m=0<(y=i).length,x=0<v.length,r=function(e,t,n,r,i){var o,a,s,u=0,l="0",c=e&&[],f=[],p=w,d=e||x&&b.find.TAG("*",i),h=k+=null==p?1:Math.random()||.1,g=d.length;for(i&&(w=t==C||t||i);l!==g&&null!=(o=d[l]);l++){if(x&&o){a=0,t||o.ownerDocument==C||(T(o),n=!E);while(s=v[a++])if(s(o,t||C,n)){r.push(o);break}i&&(k=h)}m&&((o=!s&&o)&&u--,e&&c.push(o))}if(u+=l,m&&l!==u){a=0;while(s=y[a++])s(c,f,t,n);if(e){if(0<u)while(l--)c[l]||f[l]||(f[l]=q.call(r));f=Te(f)}H.apply(r,f),i&&!e&&0<f.length&&1<u+y.length&&se.uniqueSort(r)}return i&&(k=h,w=p),c},m?le(r):r))).selector=e}return a},g=se.select=function(e,t,n,r){var i,o,a,s,u,l="function"==typeof e&&e,c=!r&&h(e=l.selector||e);if(n=n||[],1===c.length){if(2<(o=c[0]=c[0].slice(0)).length&&"ID"===(a=o[0]).type&&9===t.nodeType&&E&&b.relative[o[1].type]){if(!(t=(b.find.ID(a.matches[0].replace(te,ne),t)||[])[0]))return n;l&&(t=t.parentNode),e=e.slice(o.shift().value.length)}i=G.needsContext.test(e)?0:o.length;while(i--){if(a=o[i],b.relative[s=a.type])break;if((u=b.find[s])&&(r=u(a.matches[0].replace(te,ne),ee.test(o[0].type)&&ye(t.parentNode)||t))){if(o.splice(i,1),!(e=r.length&&xe(o)))return H.apply(n,r),n;break}}}return(l||f(e,c))(r,t,!E,n,!t||ee.test(e)&&ye(t.parentNode)||t),n},d.sortStable=S.split("").sort(j).join("")===S,d.detectDuplicates=!!l,T(),d.sortDetached=ce(function(e){return 1&e.compareDocumentPosition(C.createElement("fieldset"))}),ce(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||fe("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),d.attributes&&ce(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||fe("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ce(function(e){return null==e.getAttribute("disabled")})||fe(R,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),se}(C);S.find=d,S.expr=d.selectors,S.expr[":"]=S.expr.pseudos,S.uniqueSort=S.unique=d.uniqueSort,S.text=d.getText,S.isXMLDoc=d.isXML,S.contains=d.contains,S.escapeSelector=d.escape;var h=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&S(e).is(n))break;r.push(e)}return r},T=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},k=S.expr.match.needsContext;function A(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var N=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function j(e,n,r){return m(n)?S.grep(e,function(e,t){return!!n.call(e,t,e)!==r}):n.nodeType?S.grep(e,function(e){return e===n!==r}):"string"!=typeof n?S.grep(e,function(e){return-1<i.call(n,e)!==r}):S.filter(n,e,r)}S.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?S.find.matchesSelector(r,e)?[r]:[]:S.find.matches(e,S.grep(t,function(e){return 1===e.nodeType}))},S.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(S(e).filter(function(){for(t=0;t<r;t++)if(S.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)S.find(e,i[t],n);return 1<r?S.uniqueSort(n):n},filter:function(e){return this.pushStack(j(this,e||[],!1))},not:function(e){return this.pushStack(j(this,e||[],!0))},is:function(e){return!!j(this,"string"==typeof e&&k.test(e)?S(e):e||[],!1).length}});var D,q=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(S.fn.init=function(e,t,n){var r,i;if(!e)return this;if(n=n||D,"string"==typeof e){if(!(r="<"===e[0]&&">"===e[e.length-1]&&3<=e.length?[null,e,null]:q.exec(e))||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof S?t[0]:t,S.merge(this,S.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:E,!0)),N.test(r[1])&&S.isPlainObject(t))for(r in t)m(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return(i=E.getElementById(r[2]))&&(this[0]=i,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):m(e)?void 0!==n.ready?n.ready(e):e(S):S.makeArray(e,this)}).prototype=S.fn,D=S(E);var L=/^(?:parents|prev(?:Until|All))/,H={children:!0,contents:!0,next:!0,prev:!0};function O(e,t){while((e=e[t])&&1!==e.nodeType);return e}S.fn.extend({has:function(e){var t=S(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(S.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&S(e);if(!k.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?-1<a.index(n):1===n.nodeType&&S.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(1<o.length?S.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?i.call(S(e),this[0]):i.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(S.uniqueSort(S.merge(this.get(),S(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),S.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return h(e,"parentNode")},parentsUntil:function(e,t,n){return h(e,"parentNode",n)},next:function(e){return O(e,"nextSibling")},prev:function(e){return O(e,"previousSibling")},nextAll:function(e){return h(e,"nextSibling")},prevAll:function(e){return h(e,"previousSibling")},nextUntil:function(e,t,n){return h(e,"nextSibling",n)},prevUntil:function(e,t,n){return h(e,"previousSibling",n)},siblings:function(e){return T((e.parentNode||{}).firstChild,e)},children:function(e){return T(e.firstChild)},contents:function(e){return null!=e.contentDocument&&r(e.contentDocument)?e.contentDocument:(A(e,"template")&&(e=e.content||e),S.merge([],e.childNodes))}},function(r,i){S.fn[r]=function(e,t){var n=S.map(this,i,e);return"Until"!==r.slice(-5)&&(t=e),t&&"string"==typeof t&&(n=S.filter(t,n)),1<this.length&&(H[r]||S.uniqueSort(n),L.test(r)&&n.reverse()),this.pushStack(n)}});var P=/[^\x20\t\r\n\f]+/g;function R(e){return e}function M(e){throw e}function I(e,t,n,r){var i;try{e&&m(i=e.promise)?i.call(e).done(t).fail(n):e&&m(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}S.Callbacks=function(r){var e,n;r="string"==typeof r?(e=r,n={},S.each(e.match(P)||[],function(e,t){n[t]=!0}),n):S.extend({},r);var i,t,o,a,s=[],u=[],l=-1,c=function(){for(a=a||r.once,o=i=!0;u.length;l=-1){t=u.shift();while(++l<s.length)!1===s[l].apply(t[0],t[1])&&r.stopOnFalse&&(l=s.length,t=!1)}r.memory||(t=!1),i=!1,a&&(s=t?[]:"")},f={add:function(){return s&&(t&&!i&&(l=s.length-1,u.push(t)),function n(e){S.each(e,function(e,t){m(t)?r.unique&&f.has(t)||s.push(t):t&&t.length&&"string"!==w(t)&&n(t)})}(arguments),t&&!i&&c()),this},remove:function(){return S.each(arguments,function(e,t){var n;while(-1<(n=S.inArray(t,s,n)))s.splice(n,1),n<=l&&l--}),this},has:function(e){return e?-1<S.inArray(e,s):0<s.length},empty:function(){return s&&(s=[]),this},disable:function(){return a=u=[],s=t="",this},disabled:function(){return!s},lock:function(){return a=u=[],t||i||(s=t=""),this},locked:function(){return!!a},fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),i||c()),this},fire:function(){return f.fireWith(this,arguments),this},fired:function(){return!!o}};return f},S.extend({Deferred:function(e){var o=[["notify","progress",S.Callbacks("memory"),S.Callbacks("memory"),2],["resolve","done",S.Callbacks("once memory"),S.Callbacks("once memory"),0,"resolved"],["reject","fail",S.Callbacks("once memory"),S.Callbacks("once memory"),1,"rejected"]],i="pending",a={state:function(){return i},always:function(){return s.done(arguments).fail(arguments),this},"catch":function(e){return a.then(null,e)},pipe:function(){var i=arguments;return S.Deferred(function(r){S.each(o,function(e,t){var n=m(i[t[4]])&&i[t[4]];s[t[1]](function(){var e=n&&n.apply(this,arguments);e&&m(e.promise)?e.promise().progress(r.notify).done(r.resolve).fail(r.reject):r[t[0]+"With"](this,n?[e]:arguments)})}),i=null}).promise()},then:function(t,n,r){var u=0;function l(i,o,a,s){return function(){var n=this,r=arguments,e=function(){var e,t;if(!(i<u)){if((e=a.apply(n,r))===o.promise())throw new TypeError("Thenable self-resolution");t=e&&("object"==typeof e||"function"==typeof e)&&e.then,m(t)?s?t.call(e,l(u,o,R,s),l(u,o,M,s)):(u++,t.call(e,l(u,o,R,s),l(u,o,M,s),l(u,o,R,o.notifyWith))):(a!==R&&(n=void 0,r=[e]),(s||o.resolveWith)(n,r))}},t=s?e:function(){try{e()}catch(e){S.Deferred.exceptionHook&&S.Deferred.exceptionHook(e,t.stackTrace),u<=i+1&&(a!==M&&(n=void 0,r=[e]),o.rejectWith(n,r))}};i?t():(S.Deferred.getStackHook&&(t.stackTrace=S.Deferred.getStackHook()),C.setTimeout(t))}}return S.Deferred(function(e){o[0][3].add(l(0,e,m(r)?r:R,e.notifyWith)),o[1][3].add(l(0,e,m(t)?t:R)),o[2][3].add(l(0,e,m(n)?n:M))}).promise()},promise:function(e){return null!=e?S.extend(e,a):a}},s={};return S.each(o,function(e,t){var n=t[2],r=t[5];a[t[1]]=n.add,r&&n.add(function(){i=r},o[3-e][2].disable,o[3-e][3].disable,o[0][2].lock,o[0][3].lock),n.add(t[3].fire),s[t[0]]=function(){return s[t[0]+"With"](this===s?void 0:this,arguments),this},s[t[0]+"With"]=n.fireWith}),a.promise(s),e&&e.call(s,s),s},when:function(e){var n=arguments.length,t=n,r=Array(t),i=s.call(arguments),o=S.Deferred(),a=function(t){return function(e){r[t]=this,i[t]=1<arguments.length?s.call(arguments):e,--n||o.resolveWith(r,i)}};if(n<=1&&(I(e,o.done(a(t)).resolve,o.reject,!n),"pending"===o.state()||m(i[t]&&i[t].then)))return o.then();while(t--)I(i[t],a(t),o.reject);return o.promise()}});var W=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;S.Deferred.exceptionHook=function(e,t){C.console&&C.console.warn&&e&&W.test(e.name)&&C.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},S.readyException=function(e){C.setTimeout(function(){throw e})};var F=S.Deferred();function B(){E.removeEventListener("DOMContentLoaded",B),C.removeEventListener("load",B),S.ready()}S.fn.ready=function(e){return F.then(e)["catch"](function(e){S.readyException(e)}),this},S.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--S.readyWait:S.isReady)||(S.isReady=!0)!==e&&0<--S.readyWait||F.resolveWith(E,[S])}}),S.ready.then=F.then,"complete"===E.readyState||"loading"!==E.readyState&&!E.documentElement.doScroll?C.setTimeout(S.ready):(E.addEventListener("DOMContentLoaded",B),C.addEventListener("load",B));var $=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===w(n))for(s in i=!0,n)$(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,m(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(S(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},_=/^-ms-/,z=/-([a-z])/g;function U(e,t){return t.toUpperCase()}function X(e){return e.replace(_,"ms-").replace(z,U)}var V=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function G(){this.expando=S.expando+G.uid++}G.uid=1,G.prototype={cache:function(e){var t=e[this.expando];return t||(t={},V(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[X(t)]=n;else for(r in t)i[X(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][X(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(X):(t=X(t))in r?[t]:t.match(P)||[]).length;while(n--)delete r[t[n]]}(void 0===t||S.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!S.isEmptyObject(t)}};var Y=new G,Q=new G,J=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,K=/[A-Z]/g;function Z(e,t,n){var r,i;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(K,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n="true"===(i=n)||"false"!==i&&("null"===i?null:i===+i+""?+i:J.test(i)?JSON.parse(i):i)}catch(e){}Q.set(e,t,n)}else n=void 0;return n}S.extend({hasData:function(e){return Q.hasData(e)||Y.hasData(e)},data:function(e,t,n){return Q.access(e,t,n)},removeData:function(e,t){Q.remove(e,t)},_data:function(e,t,n){return Y.access(e,t,n)},_removeData:function(e,t){Y.remove(e,t)}}),S.fn.extend({data:function(n,e){var t,r,i,o=this[0],a=o&&o.attributes;if(void 0===n){if(this.length&&(i=Q.get(o),1===o.nodeType&&!Y.get(o,"hasDataAttrs"))){t=a.length;while(t--)a[t]&&0===(r=a[t].name).indexOf("data-")&&(r=X(r.slice(5)),Z(o,r,i[r]));Y.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof n?this.each(function(){Q.set(this,n)}):$(this,function(e){var t;if(o&&void 0===e)return void 0!==(t=Q.get(o,n))?t:void 0!==(t=Z(o,n))?t:void 0;this.each(function(){Q.set(this,n,e)})},null,e,1<arguments.length,null,!0)},removeData:function(e){return this.each(function(){Q.remove(this,e)})}}),S.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=Y.get(e,t),n&&(!r||Array.isArray(n)?r=Y.access(e,t,S.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=S.queue(e,t),r=n.length,i=n.shift(),o=S._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,function(){S.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return Y.get(e,n)||Y.access(e,n,{empty:S.Callbacks("once memory").add(function(){Y.remove(e,[t+"queue",n])})})}}),S.fn.extend({queue:function(t,n){var e=2;return"string"!=typeof t&&(n=t,t="fx",e--),arguments.length<e?S.queue(this[0],t):void 0===n?this:this.each(function(){var e=S.queue(this,t,n);S._queueHooks(this,t),"fx"===t&&"inprogress"!==e[0]&&S.dequeue(this,t)})},dequeue:function(e){return this.each(function(){S.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=S.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=Y.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var ee=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,te=new RegExp("^(?:([+-])=|)("+ee+")([a-z%]*)$","i"),ne=["Top","Right","Bottom","Left"],re=E.documentElement,ie=function(e){return S.contains(e.ownerDocument,e)},oe={composed:!0};re.getRootNode&&(ie=function(e){return S.contains(e.ownerDocument,e)||e.getRootNode(oe)===e.ownerDocument});var ae=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&ie(e)&&"none"===S.css(e,"display")};function se(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return S.css(e,t,"")},u=s(),l=n&&n[3]||(S.cssNumber[t]?"":"px"),c=e.nodeType&&(S.cssNumber[t]||"px"!==l&&+u)&&te.exec(S.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)S.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,S.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ue={};function le(e,t){for(var n,r,i,o,a,s,u,l=[],c=0,f=e.length;c<f;c++)(r=e[c]).style&&(n=r.style.display,t?("none"===n&&(l[c]=Y.get(r,"display")||null,l[c]||(r.style.display="")),""===r.style.display&&ae(r)&&(l[c]=(u=a=o=void 0,a=(i=r).ownerDocument,s=i.nodeName,(u=ue[s])||(o=a.body.appendChild(a.createElement(s)),u=S.css(o,"display"),o.parentNode.removeChild(o),"none"===u&&(u="block"),ue[s]=u)))):"none"!==n&&(l[c]="none",Y.set(r,"display",n)));for(c=0;c<f;c++)null!=l[c]&&(e[c].style.display=l[c]);return e}S.fn.extend({show:function(){return le(this,!0)},hide:function(){return le(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ae(this)?S(this).show():S(this).hide()})}});var ce,fe,pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,he=/^$|^module$|\/(?:java|ecma)script/i;ce=E.createDocumentFragment().appendChild(E.createElement("div")),(fe=E.createElement("input")).setAttribute("type","radio"),fe.setAttribute("checked","checked"),fe.setAttribute("name","t"),ce.appendChild(fe),y.checkClone=ce.cloneNode(!0).cloneNode(!0).lastChild.checked,ce.innerHTML="<textarea>x</textarea>",y.noCloneChecked=!!ce.cloneNode(!0).lastChild.defaultValue,ce.innerHTML="<option></option>",y.option=!!ce.lastChild;var ge={thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function ve(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&A(e,t)?S.merge([e],n):n}function ye(e,t){for(var n=0,r=e.length;n<r;n++)Y.set(e[n],"globalEval",!t||Y.get(t[n],"globalEval"))}ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td,y.option||(ge.optgroup=ge.option=[1,"<select multiple='multiple'>","</select>"]);var me=/<|&#?\w+;/;function xe(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===w(o))S.merge(p,o.nodeType?[o]:o);else if(me.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+S.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;S.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&-1<S.inArray(o,r))i&&i.push(o);else if(l=ie(o),a=ve(f.appendChild(o),"script"),l&&ye(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}var be=/^([^.]*)(?:\.(.+)|)/;function we(){return!0}function Te(){return!1}function Ce(e,t){return e===function(){try{return E.activeElement}catch(e){}}()==("focus"===t)}function Ee(e,t,n,r,i,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(r=r||n,n=void 0),t)Ee(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=Te;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return S().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=S.guid++)),e.each(function(){S.event.add(this,t,i,r,n)})}function Se(e,i,o){o?(Y.set(e,i,!1),S.event.add(e,i,{namespace:!1,handler:function(e){var t,n,r=Y.get(this,i);if(1&e.isTrigger&&this[i]){if(r.length)(S.event.special[i]||{}).delegateType&&e.stopPropagation();else if(r=s.call(arguments),Y.set(this,i,r),t=o(this,i),this[i](),r!==(n=Y.get(this,i))||t?Y.set(this,i,!1):n={},r!==n)return e.stopImmediatePropagation(),e.preventDefault(),n&&n.value}else r.length&&(Y.set(this,i,{value:S.event.trigger(S.extend(r[0],S.Event.prototype),r.slice(1),this)}),e.stopImmediatePropagation())}})):void 0===Y.get(e,i)&&S.event.add(e,i,we)}S.event={global:{},add:function(t,e,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Y.get(t);if(V(t)){n.handler&&(n=(o=n).handler,i=o.selector),i&&S.find.matchesSelector(re,i),n.guid||(n.guid=S.guid++),(u=v.events)||(u=v.events=Object.create(null)),(a=v.handle)||(a=v.handle=function(e){return"undefined"!=typeof S&&S.event.triggered!==e.type?S.event.dispatch.apply(t,arguments):void 0}),l=(e=(e||"").match(P)||[""]).length;while(l--)d=g=(s=be.exec(e[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=S.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=S.event.special[d]||{},c=S.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&S.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(t,r,h,a)||t.addEventListener&&t.addEventListener(d,a)),f.add&&(f.add.call(t,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),S.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Y.hasData(e)&&Y.get(e);if(v&&(u=v.events)){l=(t=(t||"").match(P)||[""]).length;while(l--)if(d=g=(s=be.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d){f=S.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,v.handle)||S.removeEvent(e,d,v.handle),delete u[d])}else for(d in u)S.event.remove(e,d+t[l],n,r,!0);S.isEmptyObject(u)&&Y.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=new Array(arguments.length),u=S.event.fix(e),l=(Y.get(this,"events")||Object.create(null))[u.type]||[],c=S.event.special[u.type]||{};for(s[0]=u,t=1;t<arguments.length;t++)s[t]=arguments[t];if(u.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,u)){a=S.event.handlers.call(this,u,l),t=0;while((i=a[t++])&&!u.isPropagationStopped()){u.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!u.isImmediatePropagationStopped())u.rnamespace&&!1!==o.namespace&&!u.rnamespace.test(o.namespace)||(u.handleObj=o,u.data=o.data,void 0!==(r=((S.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,s))&&!1===(u.result=r)&&(u.preventDefault(),u.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,u),u.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&1<=e.button))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?-1<S(i,this).index(l):S.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(t,e){Object.defineProperty(S.Event.prototype,t,{enumerable:!0,configurable:!0,get:m(e)?function(){if(this.originalEvent)return e(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[t]},set:function(e){Object.defineProperty(this,t,{enumerable:!0,configurable:!0,writable:!0,value:e})}})},fix:function(e){return e[S.expando]?e:new S.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Se(t,"click",we),!1},trigger:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Se(t,"click"),!0},_default:function(e){var t=e.target;return pe.test(t.type)&&t.click&&A(t,"input")&&Y.get(t,"click")||A(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},S.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},S.Event=function(e,t){if(!(this instanceof S.Event))return new S.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?we:Te,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&S.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[S.expando]=!0},S.Event.prototype={constructor:S.Event,isDefaultPrevented:Te,isPropagationStopped:Te,isImmediatePropagationStopped:Te,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=we,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=we,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=we,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},S.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},S.event.addProp),S.each({focus:"focusin",blur:"focusout"},function(e,t){S.event.special[e]={setup:function(){return Se(this,e,Ce),!1},trigger:function(){return Se(this,e),!0},_default:function(){return!0},delegateType:t}}),S.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,i){S.event.special[e]={delegateType:i,bindType:i,handle:function(e){var t,n=e.relatedTarget,r=e.handleObj;return n&&(n===this||S.contains(this,n))||(e.type=r.origType,t=r.handler.apply(this,arguments),e.type=i),t}}}),S.fn.extend({on:function(e,t,n,r){return Ee(this,e,t,n,r)},one:function(e,t,n,r){return Ee(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,S(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=Te),this.each(function(){S.event.remove(this,e,n,t)})}});var ke=/<script|<style|<link/i,Ae=/checked\s*(?:[^=]|=\s*.checked.)/i,Ne=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function je(e,t){return A(e,"table")&&A(11!==t.nodeType?t:t.firstChild,"tr")&&S(e).children("tbody")[0]||e}function De(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function qe(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Le(e,t){var n,r,i,o,a,s;if(1===t.nodeType){if(Y.hasData(e)&&(s=Y.get(e).events))for(i in Y.remove(t,"handle events"),s)for(n=0,r=s[i].length;n<r;n++)S.event.add(t,i,s[i][n]);Q.hasData(e)&&(o=Q.access(e),a=S.extend({},o),Q.set(t,a))}}function He(n,r,i,o){r=g(r);var e,t,a,s,u,l,c=0,f=n.length,p=f-1,d=r[0],h=m(d);if(h||1<f&&"string"==typeof d&&!y.checkClone&&Ae.test(d))return n.each(function(e){var t=n.eq(e);h&&(r[0]=d.call(this,e,t.html())),He(t,r,i,o)});if(f&&(t=(e=xe(r,n[0].ownerDocument,!1,n,o)).firstChild,1===e.childNodes.length&&(e=t),t||o)){for(s=(a=S.map(ve(e,"script"),De)).length;c<f;c++)u=e,c!==p&&(u=S.clone(u,!0,!0),s&&S.merge(a,ve(u,"script"))),i.call(n[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,S.map(a,qe),c=0;c<s;c++)u=a[c],he.test(u.type||"")&&!Y.access(u,"globalEval")&&S.contains(l,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?S._evalUrl&&!u.noModule&&S._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")},l):b(u.textContent.replace(Ne,""),u,l))}return n}function Oe(e,t,n){for(var r,i=t?S.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||S.cleanData(ve(r)),r.parentNode&&(n&&ie(r)&&ye(ve(r,"script")),r.parentNode.removeChild(r));return e}S.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,o,a,s,u,l,c=e.cloneNode(!0),f=ie(e);if(!(y.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||S.isXMLDoc(e)))for(a=ve(c),r=0,i=(o=ve(e)).length;r<i;r++)s=o[r],u=a[r],void 0,"input"===(l=u.nodeName.toLowerCase())&&pe.test(s.type)?u.checked=s.checked:"input"!==l&&"textarea"!==l||(u.defaultValue=s.defaultValue);if(t)if(n)for(o=o||ve(e),a=a||ve(c),r=0,i=o.length;r<i;r++)Le(o[r],a[r]);else Le(e,c);return 0<(a=ve(c,"script")).length&&ye(a,!f&&ve(e,"script")),c},cleanData:function(e){for(var t,n,r,i=S.event.special,o=0;void 0!==(n=e[o]);o++)if(V(n)){if(t=n[Y.expando]){if(t.events)for(r in t.events)i[r]?S.event.remove(n,r):S.removeEvent(n,r,t.handle);n[Y.expando]=void 0}n[Q.expando]&&(n[Q.expando]=void 0)}}}),S.fn.extend({detach:function(e){return Oe(this,e,!0)},remove:function(e){return Oe(this,e)},text:function(e){return $(this,function(e){return void 0===e?S.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return He(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||je(this,e).appendChild(e)})},prepend:function(){return He(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=je(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return He(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return He(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(S.cleanData(ve(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return S.clone(this,e,t)})},html:function(e){return $(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!ke.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=S.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(S.cleanData(ve(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var n=[];return He(this,arguments,function(e){var t=this.parentNode;S.inArray(this,n)<0&&(S.cleanData(ve(this)),t&&t.replaceChild(e,this))},n)}}),S.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,a){S.fn[e]=function(e){for(var t,n=[],r=S(e),i=r.length-1,o=0;o<=i;o++)t=o===i?this:this.clone(!0),S(r[o])[a](t),u.apply(n,t.get());return this.pushStack(n)}});var Pe=new RegExp("^("+ee+")(?!px)[a-z%]+$","i"),Re=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=C),t.getComputedStyle(e)},Me=function(e,t,n){var r,i,o={};for(i in t)o[i]=e.style[i],e.style[i]=t[i];for(i in r=n.call(e),t)e.style[i]=o[i];return r},Ie=new RegExp(ne.join("|"),"i");function We(e,t,n){var r,i,o,a,s=e.style;return(n=n||Re(e))&&(""!==(a=n.getPropertyValue(t)||n[t])||ie(e)||(a=S.style(e,t)),!y.pixelBoxStyles()&&Pe.test(a)&&Ie.test(t)&&(r=s.width,i=s.minWidth,o=s.maxWidth,s.minWidth=s.maxWidth=s.width=a,a=n.width,s.width=r,s.minWidth=i,s.maxWidth=o)),void 0!==a?a+"":a}function Fe(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(l){u.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",l.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",re.appendChild(u).appendChild(l);var e=C.getComputedStyle(l);n="1%"!==e.top,s=12===t(e.marginLeft),l.style.right="60%",o=36===t(e.right),r=36===t(e.width),l.style.position="absolute",i=12===t(l.offsetWidth/3),re.removeChild(u),l=null}}function t(e){return Math.round(parseFloat(e))}var n,r,i,o,a,s,u=E.createElement("div"),l=E.createElement("div");l.style&&(l.style.backgroundClip="content-box",l.cloneNode(!0).style.backgroundClip="",y.clearCloneStyle="content-box"===l.style.backgroundClip,S.extend(y,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),o},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),s},scrollboxSize:function(){return e(),i},reliableTrDimensions:function(){var e,t,n,r;return null==a&&(e=E.createElement("table"),t=E.createElement("tr"),n=E.createElement("div"),e.style.cssText="position:absolute;left:-11111px;border-collapse:separate",t.style.cssText="border:1px solid",t.style.height="1px",n.style.height="9px",n.style.display="block",re.appendChild(e).appendChild(t).appendChild(n),r=C.getComputedStyle(t),a=parseInt(r.height,10)+parseInt(r.borderTopWidth,10)+parseInt(r.borderBottomWidth,10)===t.offsetHeight,re.removeChild(e)),a}}))}();var Be=["Webkit","Moz","ms"],$e=E.createElement("div").style,_e={};function ze(e){var t=S.cssProps[e]||_e[e];return t||(e in $e?e:_e[e]=function(e){var t=e[0].toUpperCase()+e.slice(1),n=Be.length;while(n--)if((e=Be[n]+t)in $e)return e}(e)||e)}var Ue=/^(none|table(?!-c[ea]).+)/,Xe=/^--/,Ve={position:"absolute",visibility:"hidden",display:"block"},Ge={letterSpacing:"0",fontWeight:"400"};function Ye(e,t,n){var r=te.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function Qe(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=S.css(e,n+ne[a],!0,i)),r?("content"===n&&(u-=S.css(e,"padding"+ne[a],!0,i)),"margin"!==n&&(u-=S.css(e,"border"+ne[a]+"Width",!0,i))):(u+=S.css(e,"padding"+ne[a],!0,i),"padding"!==n?u+=S.css(e,"border"+ne[a]+"Width",!0,i):s+=S.css(e,"border"+ne[a]+"Width",!0,i));return!r&&0<=o&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u}function Je(e,t,n){var r=Re(e),i=(!y.boxSizingReliable()||n)&&"border-box"===S.css(e,"boxSizing",!1,r),o=i,a=We(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);if(Pe.test(a)){if(!n)return a;a="auto"}return(!y.boxSizingReliable()&&i||!y.reliableTrDimensions()&&A(e,"tr")||"auto"===a||!parseFloat(a)&&"inline"===S.css(e,"display",!1,r))&&e.getClientRects().length&&(i="border-box"===S.css(e,"boxSizing",!1,r),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+Qe(e,t,n||(i?"border":"content"),o,r,a)+"px"}function Ke(e,t,n,r,i){return new Ke.prototype.init(e,t,n,r,i)}S.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=We(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=X(t),u=Xe.test(t),l=e.style;if(u||(t=ze(s)),a=S.cssHooks[t]||S.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"===(o=typeof n)&&(i=te.exec(n))&&i[1]&&(n=se(e,t,i),o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(S.cssNumber[s]?"":"px")),y.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=X(t);return Xe.test(t)||(t=ze(s)),(a=S.cssHooks[t]||S.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=We(e,t,r)),"normal"===i&&t in Ge&&(i=Ge[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),S.each(["height","width"],function(e,u){S.cssHooks[u]={get:function(e,t,n){if(t)return!Ue.test(S.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?Je(e,u,n):Me(e,Ve,function(){return Je(e,u,n)})},set:function(e,t,n){var r,i=Re(e),o=!y.scrollboxSize()&&"absolute"===i.position,a=(o||n)&&"border-box"===S.css(e,"boxSizing",!1,i),s=n?Qe(e,u,n,a,i):0;return a&&o&&(s-=Math.ceil(e["offset"+u[0].toUpperCase()+u.slice(1)]-parseFloat(i[u])-Qe(e,u,"border",!1,i)-.5)),s&&(r=te.exec(t))&&"px"!==(r[3]||"px")&&(e.style[u]=t,t=S.css(e,u)),Ye(0,t,s)}}}),S.cssHooks.marginLeft=Fe(y.reliableMarginLeft,function(e,t){if(t)return(parseFloat(We(e,"marginLeft"))||e.getBoundingClientRect().left-Me(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),S.each({margin:"",padding:"",border:"Width"},function(i,o){S.cssHooks[i+o]={expand:function(e){for(var t=0,n={},r="string"==typeof e?e.split(" "):[e];t<4;t++)n[i+ne[t]+o]=r[t]||r[t-2]||r[0];return n}},"margin"!==i&&(S.cssHooks[i+o].set=Ye)}),S.fn.extend({css:function(e,t){return $(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=Re(e),i=t.length;a<i;a++)o[t[a]]=S.css(e,t[a],!1,r);return o}return void 0!==n?S.style(e,t,n):S.css(e,t)},e,t,1<arguments.length)}}),((S.Tween=Ke).prototype={constructor:Ke,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||S.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(S.cssNumber[n]?"":"px")},cur:function(){var e=Ke.propHooks[this.prop];return e&&e.get?e.get(this):Ke.propHooks._default.get(this)},run:function(e){var t,n=Ke.propHooks[this.prop];return this.options.duration?this.pos=t=S.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):Ke.propHooks._default.set(this),this}}).init.prototype=Ke.prototype,(Ke.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=S.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){S.fx.step[e.prop]?S.fx.step[e.prop](e):1!==e.elem.nodeType||!S.cssHooks[e.prop]&&null==e.elem.style[ze(e.prop)]?e.elem[e.prop]=e.now:S.style(e.elem,e.prop,e.now+e.unit)}}}).scrollTop=Ke.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},S.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},S.fx=Ke.prototype.init,S.fx.step={};var Ze,et,tt,nt,rt=/^(?:toggle|show|hide)$/,it=/queueHooks$/;function ot(){et&&(!1===E.hidden&&C.requestAnimationFrame?C.requestAnimationFrame(ot):C.setTimeout(ot,S.fx.interval),S.fx.tick())}function at(){return C.setTimeout(function(){Ze=void 0}),Ze=Date.now()}function st(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=ne[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function ut(e,t,n){for(var r,i=(lt.tweeners[t]||[]).concat(lt.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function lt(o,e,t){var n,a,r=0,i=lt.prefilters.length,s=S.Deferred().always(function(){delete u.elem}),u=function(){if(a)return!1;for(var e=Ze||at(),t=Math.max(0,l.startTime+l.duration-e),n=1-(t/l.duration||0),r=0,i=l.tweens.length;r<i;r++)l.tweens[r].run(n);return s.notifyWith(o,[l,n,t]),n<1&&i?t:(i||s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l]),!1)},l=s.promise({elem:o,props:S.extend({},e),opts:S.extend(!0,{specialEasing:{},easing:S.easing._default},t),originalProperties:e,originalOptions:t,startTime:Ze||at(),duration:t.duration,tweens:[],createTween:function(e,t){var n=S.Tween(o,l.opts,e,t,l.opts.specialEasing[e]||l.opts.easing);return l.tweens.push(n),n},stop:function(e){var t=0,n=e?l.tweens.length:0;if(a)return this;for(a=!0;t<n;t++)l.tweens[t].run(1);return e?(s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l,e])):s.rejectWith(o,[l,e]),this}}),c=l.props;for(!function(e,t){var n,r,i,o,a;for(n in e)if(i=t[r=X(n)],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=S.cssHooks[r])&&"expand"in a)for(n in o=a.expand(o),delete e[r],o)n in e||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);r<i;r++)if(n=lt.prefilters[r].call(l,o,c,l.opts))return m(n.stop)&&(S._queueHooks(l.elem,l.opts.queue).stop=n.stop.bind(n)),n;return S.map(c,ut,l),m(l.opts.start)&&l.opts.start.call(o,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),S.fx.timer(S.extend(u,{elem:o,anim:l,queue:l.opts.queue})),l}S.Animation=S.extend(lt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return se(n.elem,e,te.exec(t),n),n}]},tweener:function(e,t){m(e)?(t=e,e=["*"]):e=e.match(P);for(var n,r=0,i=e.length;r<i;r++)n=e[r],lt.tweeners[n]=lt.tweeners[n]||[],lt.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ae(e),v=Y.get(e,"fxshow");for(r in n.queue||(null==(a=S._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,S.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],rt.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!v||void 0===v[r])continue;g=!0}d[r]=v&&v[r]||S.style(e,r)}if((u=!S.isEmptyObject(t))||!S.isEmptyObject(d))for(r in f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=v&&v.display)&&(l=Y.get(e,"display")),"none"===(c=S.css(e,"display"))&&(l?c=l:(le([e],!0),l=e.style.display||l,c=S.css(e,"display"),le([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===S.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1,d)u||(v?"hidden"in v&&(g=v.hidden):v=Y.access(e,"fxshow",{display:l}),o&&(v.hidden=!g),g&&le([e],!0),p.done(function(){for(r in g||le([e]),Y.remove(e,"fxshow"),d)S.style(e,r,d[r])})),u=ut(g?v[r]:0,r,p),r in v||(v[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?lt.prefilters.unshift(e):lt.prefilters.push(e)}}),S.speed=function(e,t,n){var r=e&&"object"==typeof e?S.extend({},e):{complete:n||!n&&t||m(e)&&e,duration:e,easing:n&&t||t&&!m(t)&&t};return S.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in S.fx.speeds?r.duration=S.fx.speeds[r.duration]:r.duration=S.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){m(r.old)&&r.old.call(this),r.queue&&S.dequeue(this,r.queue)},r},S.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ae).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(t,e,n,r){var i=S.isEmptyObject(t),o=S.speed(e,n,r),a=function(){var e=lt(this,S.extend({},t),o);(i||Y.get(this,"finish"))&&e.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(i,e,o){var a=function(e){var t=e.stop;delete e.stop,t(o)};return"string"!=typeof i&&(o=e,e=i,i=void 0),e&&this.queue(i||"fx",[]),this.each(function(){var e=!0,t=null!=i&&i+"queueHooks",n=S.timers,r=Y.get(this);if(t)r[t]&&r[t].stop&&a(r[t]);else for(t in r)r[t]&&r[t].stop&&it.test(t)&&a(r[t]);for(t=n.length;t--;)n[t].elem!==this||null!=i&&n[t].queue!==i||(n[t].anim.stop(o),e=!1,n.splice(t,1));!e&&o||S.dequeue(this,i)})},finish:function(a){return!1!==a&&(a=a||"fx"),this.each(function(){var e,t=Y.get(this),n=t[a+"queue"],r=t[a+"queueHooks"],i=S.timers,o=n?n.length:0;for(t.finish=!0,S.queue(this,a,[]),r&&r.stop&&r.stop.call(this,!0),e=i.length;e--;)i[e].elem===this&&i[e].queue===a&&(i[e].anim.stop(!0),i.splice(e,1));for(e=0;e<o;e++)n[e]&&n[e].finish&&n[e].finish.call(this);delete t.finish})}}),S.each(["toggle","show","hide"],function(e,r){var i=S.fn[r];S.fn[r]=function(e,t,n){return null==e||"boolean"==typeof e?i.apply(this,arguments):this.animate(st(r,!0),e,t,n)}}),S.each({slideDown:st("show"),slideUp:st("hide"),slideToggle:st("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,r){S.fn[e]=function(e,t,n){return this.animate(r,e,t,n)}}),S.timers=[],S.fx.tick=function(){var e,t=0,n=S.timers;for(Ze=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||S.fx.stop(),Ze=void 0},S.fx.timer=function(e){S.timers.push(e),S.fx.start()},S.fx.interval=13,S.fx.start=function(){et||(et=!0,ot())},S.fx.stop=function(){et=null},S.fx.speeds={slow:600,fast:200,_default:400},S.fn.delay=function(r,e){return r=S.fx&&S.fx.speeds[r]||r,e=e||"fx",this.queue(e,function(e,t){var n=C.setTimeout(e,r);t.stop=function(){C.clearTimeout(n)}})},tt=E.createElement("input"),nt=E.createElement("select").appendChild(E.createElement("option")),tt.type="checkbox",y.checkOn=""!==tt.value,y.optSelected=nt.selected,(tt=E.createElement("input")).value="t",tt.type="radio",y.radioValue="t"===tt.value;var ct,ft=S.expr.attrHandle;S.fn.extend({attr:function(e,t){return $(this,S.attr,e,t,1<arguments.length)},removeAttr:function(e){return this.each(function(){S.removeAttr(this,e)})}}),S.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?S.prop(e,t,n):(1===o&&S.isXMLDoc(e)||(i=S.attrHooks[t.toLowerCase()]||(S.expr.match.bool.test(t)?ct:void 0)),void 0!==n?null===n?void S.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=S.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!y.radioValue&&"radio"===t&&A(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(P);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),ct={set:function(e,t,n){return!1===t?S.removeAttr(e,n):e.setAttribute(n,n),n}},S.each(S.expr.match.bool.source.match(/\w+/g),function(e,t){var a=ft[t]||S.find.attr;ft[t]=function(e,t,n){var r,i,o=t.toLowerCase();return n||(i=ft[o],ft[o]=r,r=null!=a(e,t,n)?o:null,ft[o]=i),r}});var pt=/^(?:input|select|textarea|button)$/i,dt=/^(?:a|area)$/i;function ht(e){return(e.match(P)||[]).join(" ")}function gt(e){return e.getAttribute&&e.getAttribute("class")||""}function vt(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(P)||[]}S.fn.extend({prop:function(e,t){return $(this,S.prop,e,t,1<arguments.length)},removeProp:function(e){return this.each(function(){delete this[S.propFix[e]||e]})}}),S.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&S.isXMLDoc(e)||(t=S.propFix[t]||t,i=S.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=S.find.attr(e,"tabindex");return t?parseInt(t,10):pt.test(e.nodeName)||dt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),y.optSelected||(S.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),S.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){S.propFix[this.toLowerCase()]=this}),S.fn.extend({addClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){S(this).addClass(t.call(this,e,gt(this)))});if((e=vt(t)).length)while(n=this[u++])if(i=gt(n),r=1===n.nodeType&&" "+ht(i)+" "){a=0;while(o=e[a++])r.indexOf(" "+o+" ")<0&&(r+=o+" ");i!==(s=ht(r))&&n.setAttribute("class",s)}return this},removeClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){S(this).removeClass(t.call(this,e,gt(this)))});if(!arguments.length)return this.attr("class","");if((e=vt(t)).length)while(n=this[u++])if(i=gt(n),r=1===n.nodeType&&" "+ht(i)+" "){a=0;while(o=e[a++])while(-1<r.indexOf(" "+o+" "))r=r.replace(" "+o+" "," ");i!==(s=ht(r))&&n.setAttribute("class",s)}return this},toggleClass:function(i,t){var o=typeof i,a="string"===o||Array.isArray(i);return"boolean"==typeof t&&a?t?this.addClass(i):this.removeClass(i):m(i)?this.each(function(e){S(this).toggleClass(i.call(this,e,gt(this),t),t)}):this.each(function(){var e,t,n,r;if(a){t=0,n=S(this),r=vt(i);while(e=r[t++])n.hasClass(e)?n.removeClass(e):n.addClass(e)}else void 0!==i&&"boolean"!==o||((e=gt(this))&&Y.set(this,"__className__",e),this.setAttribute&&this.setAttribute("class",e||!1===i?"":Y.get(this,"__className__")||""))})},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&-1<(" "+ht(gt(n))+" ").indexOf(t))return!0;return!1}});var yt=/\r/g;S.fn.extend({val:function(n){var r,e,i,t=this[0];return arguments.length?(i=m(n),this.each(function(e){var t;1===this.nodeType&&(null==(t=i?n.call(this,e,S(this).val()):n)?t="":"number"==typeof t?t+="":Array.isArray(t)&&(t=S.map(t,function(e){return null==e?"":e+""})),(r=S.valHooks[this.type]||S.valHooks[this.nodeName.toLowerCase()])&&"set"in r&&void 0!==r.set(this,t,"value")||(this.value=t))})):t?(r=S.valHooks[t.type]||S.valHooks[t.nodeName.toLowerCase()])&&"get"in r&&void 0!==(e=r.get(t,"value"))?e:"string"==typeof(e=t.value)?e.replace(yt,""):null==e?"":e:void 0}}),S.extend({valHooks:{option:{get:function(e){var t=S.find.attr(e,"value");return null!=t?t:ht(S.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!A(n.parentNode,"optgroup"))){if(t=S(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=S.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=-1<S.inArray(S.valHooks.option.get(r),o))&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),S.each(["radio","checkbox"],function(){S.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=-1<S.inArray(S(e).val(),t)}},y.checkOn||(S.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),y.focusin="onfocusin"in C;var mt=/^(?:focusinfocus|focusoutblur)$/,xt=function(e){e.stopPropagation()};S.extend(S.event,{trigger:function(e,t,n,r){var i,o,a,s,u,l,c,f,p=[n||E],d=v.call(e,"type")?e.type:e,h=v.call(e,"namespace")?e.namespace.split("."):[];if(o=f=a=n=n||E,3!==n.nodeType&&8!==n.nodeType&&!mt.test(d+S.event.triggered)&&(-1<d.indexOf(".")&&(d=(h=d.split(".")).shift(),h.sort()),u=d.indexOf(":")<0&&"on"+d,(e=e[S.expando]?e:new S.Event(d,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=h.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:S.makeArray(t,[e]),c=S.event.special[d]||{},r||!c.trigger||!1!==c.trigger.apply(n,t))){if(!r&&!c.noBubble&&!x(n)){for(s=c.delegateType||d,mt.test(s+d)||(o=o.parentNode);o;o=o.parentNode)p.push(o),a=o;a===(n.ownerDocument||E)&&p.push(a.defaultView||a.parentWindow||C)}i=0;while((o=p[i++])&&!e.isPropagationStopped())f=o,e.type=1<i?s:c.bindType||d,(l=(Y.get(o,"events")||Object.create(null))[e.type]&&Y.get(o,"handle"))&&l.apply(o,t),(l=u&&o[u])&&l.apply&&V(o)&&(e.result=l.apply(o,t),!1===e.result&&e.preventDefault());return e.type=d,r||e.isDefaultPrevented()||c._default&&!1!==c._default.apply(p.pop(),t)||!V(n)||u&&m(n[d])&&!x(n)&&((a=n[u])&&(n[u]=null),S.event.triggered=d,e.isPropagationStopped()&&f.addEventListener(d,xt),n[d](),e.isPropagationStopped()&&f.removeEventListener(d,xt),S.event.triggered=void 0,a&&(n[u]=a)),e.result}},simulate:function(e,t,n){var r=S.extend(new S.Event,n,{type:e,isSimulated:!0});S.event.trigger(r,null,t)}}),S.fn.extend({trigger:function(e,t){return this.each(function(){S.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return S.event.trigger(e,t,n,!0)}}),y.focusin||S.each({focus:"focusin",blur:"focusout"},function(n,r){var i=function(e){S.event.simulate(r,e.target,S.event.fix(e))};S.event.special[r]={setup:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r);t||e.addEventListener(n,i,!0),Y.access(e,r,(t||0)+1)},teardown:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r)-1;t?Y.access(e,r,t):(e.removeEventListener(n,i,!0),Y.remove(e,r))}}});var bt=C.location,wt={guid:Date.now()},Tt=/\?/;S.parseXML=function(e){var t,n;if(!e||"string"!=typeof e)return null;try{t=(new C.DOMParser).parseFromString(e,"text/xml")}catch(e){}return n=t&&t.getElementsByTagName("parsererror")[0],t&&!n||S.error("Invalid XML: "+(n?S.map(n.childNodes,function(e){return e.textContent}).join("\n"):e)),t};var Ct=/\[\]$/,Et=/\r?\n/g,St=/^(?:submit|button|image|reset|file)$/i,kt=/^(?:input|select|textarea|keygen)/i;function At(n,e,r,i){var t;if(Array.isArray(e))S.each(e,function(e,t){r||Ct.test(n)?i(n,t):At(n+"["+("object"==typeof t&&null!=t?e:"")+"]",t,r,i)});else if(r||"object"!==w(e))i(n,e);else for(t in e)At(n+"["+t+"]",e[t],r,i)}S.param=function(e,t){var n,r=[],i=function(e,t){var n=m(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!S.isPlainObject(e))S.each(e,function(){i(this.name,this.value)});else for(n in e)At(n,e[n],t,i);return r.join("&")},S.fn.extend({serialize:function(){return S.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=S.prop(this,"elements");return e?S.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!S(this).is(":disabled")&&kt.test(this.nodeName)&&!St.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=S(this).val();return null==n?null:Array.isArray(n)?S.map(n,function(e){return{name:t.name,value:e.replace(Et,"\r\n")}}):{name:t.name,value:n.replace(Et,"\r\n")}}).get()}});var Nt=/%20/g,jt=/#.*$/,Dt=/([?&])_=[^&]*/,qt=/^(.*?):[ \t]*([^\r\n]*)$/gm,Lt=/^(?:GET|HEAD)$/,Ht=/^\/\//,Ot={},Pt={},Rt="*/".concat("*"),Mt=E.createElement("a");function It(o){return function(e,t){"string"!=typeof e&&(t=e,e="*");var n,r=0,i=e.toLowerCase().match(P)||[];if(m(t))while(n=i[r++])"+"===n[0]?(n=n.slice(1)||"*",(o[n]=o[n]||[]).unshift(t)):(o[n]=o[n]||[]).push(t)}}function Wt(t,i,o,a){var s={},u=t===Pt;function l(e){var r;return s[e]=!0,S.each(t[e]||[],function(e,t){var n=t(i,o,a);return"string"!=typeof n||u||s[n]?u?!(r=n):void 0:(i.dataTypes.unshift(n),l(n),!1)}),r}return l(i.dataTypes[0])||!s["*"]&&l("*")}function Ft(e,t){var n,r,i=S.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&S.extend(!0,e,r),e}Mt.href=bt.href,S.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:bt.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(bt.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":Rt,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":S.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?Ft(Ft(e,S.ajaxSettings),t):Ft(S.ajaxSettings,e)},ajaxPrefilter:It(Ot),ajaxTransport:It(Pt),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var c,f,p,n,d,r,h,g,i,o,v=S.ajaxSetup({},t),y=v.context||v,m=v.context&&(y.nodeType||y.jquery)?S(y):S.event,x=S.Deferred(),b=S.Callbacks("once memory"),w=v.statusCode||{},a={},s={},u="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(h){if(!n){n={};while(t=qt.exec(p))n[t[1].toLowerCase()+" "]=(n[t[1].toLowerCase()+" "]||[]).concat(t[2])}t=n[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return h?p:null},setRequestHeader:function(e,t){return null==h&&(e=s[e.toLowerCase()]=s[e.toLowerCase()]||e,a[e]=t),this},overrideMimeType:function(e){return null==h&&(v.mimeType=e),this},statusCode:function(e){var t;if(e)if(h)T.always(e[T.status]);else for(t in e)w[t]=[w[t],e[t]];return this},abort:function(e){var t=e||u;return c&&c.abort(t),l(0,t),this}};if(x.promise(T),v.url=((e||v.url||bt.href)+"").replace(Ht,bt.protocol+"//"),v.type=t.method||t.type||v.method||v.type,v.dataTypes=(v.dataType||"*").toLowerCase().match(P)||[""],null==v.crossDomain){r=E.createElement("a");try{r.href=v.url,r.href=r.href,v.crossDomain=Mt.protocol+"//"+Mt.host!=r.protocol+"//"+r.host}catch(e){v.crossDomain=!0}}if(v.data&&v.processData&&"string"!=typeof v.data&&(v.data=S.param(v.data,v.traditional)),Wt(Ot,v,t,T),h)return T;for(i in(g=S.event&&v.global)&&0==S.active++&&S.event.trigger("ajaxStart"),v.type=v.type.toUpperCase(),v.hasContent=!Lt.test(v.type),f=v.url.replace(jt,""),v.hasContent?v.data&&v.processData&&0===(v.contentType||"").indexOf("application/x-www-form-urlencoded")&&(v.data=v.data.replace(Nt,"+")):(o=v.url.slice(f.length),v.data&&(v.processData||"string"==typeof v.data)&&(f+=(Tt.test(f)?"&":"?")+v.data,delete v.data),!1===v.cache&&(f=f.replace(Dt,"$1"),o=(Tt.test(f)?"&":"?")+"_="+wt.guid+++o),v.url=f+o),v.ifModified&&(S.lastModified[f]&&T.setRequestHeader("If-Modified-Since",S.lastModified[f]),S.etag[f]&&T.setRequestHeader("If-None-Match",S.etag[f])),(v.data&&v.hasContent&&!1!==v.contentType||t.contentType)&&T.setRequestHeader("Content-Type",v.contentType),T.setRequestHeader("Accept",v.dataTypes[0]&&v.accepts[v.dataTypes[0]]?v.accepts[v.dataTypes[0]]+("*"!==v.dataTypes[0]?", "+Rt+"; q=0.01":""):v.accepts["*"]),v.headers)T.setRequestHeader(i,v.headers[i]);if(v.beforeSend&&(!1===v.beforeSend.call(y,T,v)||h))return T.abort();if(u="abort",b.add(v.complete),T.done(v.success),T.fail(v.error),c=Wt(Pt,v,t,T)){if(T.readyState=1,g&&m.trigger("ajaxSend",[T,v]),h)return T;v.async&&0<v.timeout&&(d=C.setTimeout(function(){T.abort("timeout")},v.timeout));try{h=!1,c.send(a,l)}catch(e){if(h)throw e;l(-1,e)}}else l(-1,"No Transport");function l(e,t,n,r){var i,o,a,s,u,l=t;h||(h=!0,d&&C.clearTimeout(d),c=void 0,p=r||"",T.readyState=0<e?4:0,i=200<=e&&e<300||304===e,n&&(s=function(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}(v,T,n)),!i&&-1<S.inArray("script",v.dataTypes)&&S.inArray("json",v.dataTypes)<0&&(v.converters["text script"]=function(){}),s=function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}(v,s,T,i),i?(v.ifModified&&((u=T.getResponseHeader("Last-Modified"))&&(S.lastModified[f]=u),(u=T.getResponseHeader("etag"))&&(S.etag[f]=u)),204===e||"HEAD"===v.type?l="nocontent":304===e?l="notmodified":(l=s.state,o=s.data,i=!(a=s.error))):(a=l,!e&&l||(l="error",e<0&&(e=0))),T.status=e,T.statusText=(t||l)+"",i?x.resolveWith(y,[o,l,T]):x.rejectWith(y,[T,l,a]),T.statusCode(w),w=void 0,g&&m.trigger(i?"ajaxSuccess":"ajaxError",[T,v,i?o:a]),b.fireWith(y,[T,l]),g&&(m.trigger("ajaxComplete",[T,v]),--S.active||S.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return S.get(e,t,n,"json")},getScript:function(e,t){return S.get(e,void 0,t,"script")}}),S.each(["get","post"],function(e,i){S[i]=function(e,t,n,r){return m(t)&&(r=r||n,n=t,t=void 0),S.ajax(S.extend({url:e,type:i,dataType:r,data:t,success:n},S.isPlainObject(e)&&e))}}),S.ajaxPrefilter(function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")}),S._evalUrl=function(e,t,n){return S.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){S.globalEval(e,t,n)}})},S.fn.extend({wrapAll:function(e){var t;return this[0]&&(m(e)&&(e=e.call(this[0])),t=S(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(n){return m(n)?this.each(function(e){S(this).wrapInner(n.call(this,e))}):this.each(function(){var e=S(this),t=e.contents();t.length?t.wrapAll(n):e.append(n)})},wrap:function(t){var n=m(t);return this.each(function(e){S(this).wrapAll(n?t.call(this,e):t)})},unwrap:function(e){return this.parent(e).not("body").each(function(){S(this).replaceWith(this.childNodes)}),this}}),S.expr.pseudos.hidden=function(e){return!S.expr.pseudos.visible(e)},S.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},S.ajaxSettings.xhr=function(){try{return new C.XMLHttpRequest}catch(e){}};var Bt={0:200,1223:204},$t=S.ajaxSettings.xhr();y.cors=!!$t&&"withCredentials"in $t,y.ajax=$t=!!$t,S.ajaxTransport(function(i){var o,a;if(y.cors||$t&&!i.crossDomain)return{send:function(e,t){var n,r=i.xhr();if(r.open(i.type,i.url,i.async,i.username,i.password),i.xhrFields)for(n in i.xhrFields)r[n]=i.xhrFields[n];for(n in i.mimeType&&r.overrideMimeType&&r.overrideMimeType(i.mimeType),i.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest"),e)r.setRequestHeader(n,e[n]);o=function(e){return function(){o&&(o=a=r.onload=r.onerror=r.onabort=r.ontimeout=r.onreadystatechange=null,"abort"===e?r.abort():"error"===e?"number"!=typeof r.status?t(0,"error"):t(r.status,r.statusText):t(Bt[r.status]||r.status,r.statusText,"text"!==(r.responseType||"text")||"string"!=typeof r.responseText?{binary:r.response}:{text:r.responseText},r.getAllResponseHeaders()))}},r.onload=o(),a=r.onerror=r.ontimeout=o("error"),void 0!==r.onabort?r.onabort=a:r.onreadystatechange=function(){4===r.readyState&&C.setTimeout(function(){o&&a()})},o=o("abort");try{r.send(i.hasContent&&i.data||null)}catch(e){if(o)throw e}},abort:function(){o&&o()}}}),S.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),S.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return S.globalEval(e),e}}}),S.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),S.ajaxTransport("script",function(n){var r,i;if(n.crossDomain||n.scriptAttrs)return{send:function(e,t){r=S("<script>").attr(n.scriptAttrs||{}).prop({charset:n.scriptCharset,src:n.url}).on("load error",i=function(e){r.remove(),i=null,e&&t("error"===e.type?404:200,e.type)}),E.head.appendChild(r[0])},abort:function(){i&&i()}}});var _t,zt=[],Ut=/(=)\?(?=&|$)|\?\?/;S.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=zt.pop()||S.expando+"_"+wt.guid++;return this[e]=!0,e}}),S.ajaxPrefilter("json jsonp",function(e,t,n){var r,i,o,a=!1!==e.jsonp&&(Ut.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&Ut.test(e.data)&&"data");if(a||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=m(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,a?e[a]=e[a].replace(Ut,"$1"+r):!1!==e.jsonp&&(e.url+=(Tt.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return o||S.error(r+" was not called"),o[0]},e.dataTypes[0]="json",i=C[r],C[r]=function(){o=arguments},n.always(function(){void 0===i?S(C).removeProp(r):C[r]=i,e[r]&&(e.jsonpCallback=t.jsonpCallback,zt.push(r)),o&&m(i)&&i(o[0]),o=i=void 0}),"script"}),y.createHTMLDocument=((_t=E.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===_t.childNodes.length),S.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(y.createHTMLDocument?((r=(t=E.implementation.createHTMLDocument("")).createElement("base")).href=E.location.href,t.head.appendChild(r)):t=E),o=!n&&[],(i=N.exec(e))?[t.createElement(i[1])]:(i=xe([e],t,o),o&&o.length&&S(o).remove(),S.merge([],i.childNodes)));var r,i,o},S.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return-1<s&&(r=ht(e.slice(s)),e=e.slice(0,s)),m(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),0<a.length&&S.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?S("<div>").append(S.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},S.expr.pseudos.animated=function(t){return S.grep(S.timers,function(e){return t===e.elem}).length},S.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=S.css(e,"position"),c=S(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=S.css(e,"top"),u=S.css(e,"left"),("absolute"===l||"fixed"===l)&&-1<(o+u).indexOf("auto")?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),m(t)&&(t=t.call(e,n,S.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):c.css(f)}},S.fn.extend({offset:function(t){if(arguments.length)return void 0===t?this:this.each(function(e){S.offset.setOffset(this,t,e)});var e,n,r=this[0];return r?r.getClientRects().length?(e=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:e.top+n.pageYOffset,left:e.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===S.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===S.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=S(e).offset()).top+=S.css(e,"borderTopWidth",!0),i.left+=S.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-S.css(r,"marginTop",!0),left:t.left-i.left-S.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===S.css(e,"position"))e=e.offsetParent;return e||re})}}),S.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,i){var o="pageYOffset"===i;S.fn[t]=function(e){return $(this,function(e,t,n){var r;if(x(e)?r=e:9===e.nodeType&&(r=e.defaultView),void 0===n)return r?r[i]:e[t];r?r.scrollTo(o?r.pageXOffset:n,o?n:r.pageYOffset):e[t]=n},t,e,arguments.length)}}),S.each(["top","left"],function(e,n){S.cssHooks[n]=Fe(y.pixelPosition,function(e,t){if(t)return t=We(e,n),Pe.test(t)?S(e).position()[n]+"px":t})}),S.each({Height:"height",Width:"width"},function(a,s){S.each({padding:"inner"+a,content:s,"":"outer"+a},function(r,o){S.fn[o]=function(e,t){var n=arguments.length&&(r||"boolean"!=typeof e),i=r||(!0===e||!0===t?"margin":"border");return $(this,function(e,t,n){var r;return x(e)?0===o.indexOf("outer")?e["inner"+a]:e.document.documentElement["client"+a]:9===e.nodeType?(r=e.documentElement,Math.max(e.body["scroll"+a],r["scroll"+a],e.body["offset"+a],r["offset"+a],r["client"+a])):void 0===n?S.css(e,t,i):S.style(e,t,n,i)},s,n?e:void 0,n)}})}),S.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){S.fn[t]=function(e){return this.on(t,e)}}),S.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),S.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,n){S.fn[n]=function(e,t){return 0<arguments.length?this.on(n,null,e,t):this.trigger(n)}});var Xt=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;S.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),m(e))return r=s.call(arguments,2),(i=function(){return e.apply(t||this,r.concat(s.call(arguments)))}).guid=e.guid=e.guid||S.guid++,i},S.holdReady=function(e){e?S.readyWait++:S.ready(!0)},S.isArray=Array.isArray,S.parseJSON=JSON.parse,S.nodeName=A,S.isFunction=m,S.isWindow=x,S.camelCase=X,S.type=w,S.now=Date.now,S.isNumeric=function(e){var t=S.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},S.trim=function(e){return null==e?"":(e+"").replace(Xt,"")},"function"==typeof define&&define.amd&&define("jquery",[],function(){return S});var Vt=C.jQuery,Gt=C.$;return S.noConflict=function(e){return C.$===S&&(C.$=Gt),e&&C.jQuery===S&&(C.jQuery=Vt),S},"undefined"==typeof e&&(C.jQuery=C.$=S),S});

/*! lazysizes - v5.3.2 */

!function(e){var t=function(u,D,f){"use strict";var k,H;if(function(){var e;var t={lazyClass:"lazyload",loadedClass:"lazyloaded",loadingClass:"lazyloading",preloadClass:"lazypreload",errorClass:"lazyerror",autosizesClass:"lazyautosizes",fastLoadedClass:"ls-is-cached",iframeLoadMode:0,srcAttr:"data-src",srcsetAttr:"data-srcset",sizesAttr:"data-sizes",minSize:40,customMedia:{},init:true,expFactor:1.5,hFac:.8,loadMode:2,loadHidden:true,ricTimeout:0,throttleDelay:125};H=u.lazySizesConfig||u.lazysizesConfig||{};for(e in t){if(!(e in H)){H[e]=t[e]}}}(),!D||!D.getElementsByClassName){return{init:function(){},cfg:H,noSupport:true}}var O=D.documentElement,i=u.HTMLPictureElement,P="addEventListener",$="getAttribute",q=u[P].bind(u),I=u.setTimeout,U=u.requestAnimationFrame||I,o=u.requestIdleCallback,j=/^picture$/i,r=["load","error","lazyincluded","_lazyloaded"],a={},G=Array.prototype.forEach,J=function(e,t){if(!a[t]){a[t]=new RegExp("(\\s|^)"+t+"(\\s|$)")}return a[t].test(e[$]("class")||"")&&a[t]},K=function(e,t){if(!J(e,t)){e.setAttribute("class",(e[$]("class")||"").trim()+" "+t)}},Q=function(e,t){var a;if(a=J(e,t)){e.setAttribute("class",(e[$]("class")||"").replace(a," "))}},V=function(t,a,e){var i=e?P:"removeEventListener";if(e){V(t,a)}r.forEach(function(e){t[i](e,a)})},X=function(e,t,a,i,r){var n=D.createEvent("Event");if(!a){a={}}a.instance=k;n.initEvent(t,!i,!r);n.detail=a;e.dispatchEvent(n);return n},Y=function(e,t){var a;if(!i&&(a=u.picturefill||H.pf)){if(t&&t.src&&!e[$]("srcset")){e.setAttribute("srcset",t.src)}a({reevaluate:true,elements:[e]})}else if(t&&t.src){e.src=t.src}},Z=function(e,t){return(getComputedStyle(e,null)||{})[t]},s=function(e,t,a){a=a||e.offsetWidth;while(a<H.minSize&&t&&!e._lazysizesWidth){a=t.offsetWidth;t=t.parentNode}return a},ee=function(){var a,i;var t=[];var r=[];var n=t;var s=function(){var e=n;n=t.length?r:t;a=true;i=false;while(e.length){e.shift()()}a=false};var e=function(e,t){if(a&&!t){e.apply(this,arguments)}else{n.push(e);if(!i){i=true;(D.hidden?I:U)(s)}}};e._lsFlush=s;return e}(),te=function(a,e){return e?function(){ee(a)}:function(){var e=this;var t=arguments;ee(function(){a.apply(e,t)})}},ae=function(e){var a;var i=0;var r=H.throttleDelay;var n=H.ricTimeout;var t=function(){a=false;i=f.now();e()};var s=o&&n>49?function(){o(t,{timeout:n});if(n!==H.ricTimeout){n=H.ricTimeout}}:te(function(){I(t)},true);return function(e){var t;if(e=e===true){n=33}if(a){return}a=true;t=r-(f.now()-i);if(t<0){t=0}if(e||t<9){s()}else{I(s,t)}}},ie=function(e){var t,a;var i=99;var r=function(){t=null;e()};var n=function(){var e=f.now()-a;if(e<i){I(n,i-e)}else{(o||r)(r)}};return function(){a=f.now();if(!t){t=I(n,i)}}},e=function(){var v,m,c,h,e;var y,z,g,p,C,b,A;var n=/^img$/i;var d=/^iframe$/i;var E="onscroll"in u&&!/(gle|ing)bot/.test(navigator.userAgent);var _=0;var w=0;var M=0;var N=-1;var L=function(e){M--;if(!e||M<0||!e.target){M=0}};var x=function(e){if(A==null){A=Z(D.body,"visibility")=="hidden"}return A||!(Z(e.parentNode,"visibility")=="hidden"&&Z(e,"visibility")=="hidden")};var W=function(e,t){var a;var i=e;var r=x(e);g-=t;b+=t;p-=t;C+=t;while(r&&(i=i.offsetParent)&&i!=D.body&&i!=O){r=(Z(i,"opacity")||1)>0;if(r&&Z(i,"overflow")!="visible"){a=i.getBoundingClientRect();r=C>a.left&&p<a.right&&b>a.top-1&&g<a.bottom+1}}return r};var t=function(){var e,t,a,i,r,n,s,o,l,u,f,c;var d=k.elements;if((h=H.loadMode)&&M<8&&(e=d.length)){t=0;N++;for(;t<e;t++){if(!d[t]||d[t]._lazyRace){continue}if(!E||k.prematureUnveil&&k.prematureUnveil(d[t])){R(d[t]);continue}if(!(o=d[t][$]("data-expand"))||!(n=o*1)){n=w}if(!u){u=!H.expand||H.expand<1?O.clientHeight>500&&O.clientWidth>500?500:370:H.expand;k._defEx=u;f=u*H.expFactor;c=H.hFac;A=null;if(w<f&&M<1&&N>2&&h>2&&!D.hidden){w=f;N=0}else if(h>1&&N>1&&M<6){w=u}else{w=_}}if(l!==n){y=innerWidth+n*c;z=innerHeight+n;s=n*-1;l=n}a=d[t].getBoundingClientRect();if((b=a.bottom)>=s&&(g=a.top)<=z&&(C=a.right)>=s*c&&(p=a.left)<=y&&(b||C||p||g)&&(H.loadHidden||x(d[t]))&&(m&&M<3&&!o&&(h<3||N<4)||W(d[t],n))){R(d[t]);r=true;if(M>9){break}}else if(!r&&m&&!i&&M<4&&N<4&&h>2&&(v[0]||H.preloadAfterLoad)&&(v[0]||!o&&(b||C||p||g||d[t][$](H.sizesAttr)!="auto"))){i=v[0]||d[t]}}if(i&&!r){R(i)}}};var a=ae(t);var S=function(e){var t=e.target;if(t._lazyCache){delete t._lazyCache;return}L(e);K(t,H.loadedClass);Q(t,H.loadingClass);V(t,B);X(t,"lazyloaded")};var i=te(S);var B=function(e){i({target:e.target})};var T=function(e,t){var a=e.getAttribute("data-load-mode")||H.iframeLoadMode;if(a==0){e.contentWindow.location.replace(t)}else if(a==1){e.src=t}};var F=function(e){var t;var a=e[$](H.srcsetAttr);if(t=H.customMedia[e[$]("data-media")||e[$]("media")]){e.setAttribute("media",t)}if(a){e.setAttribute("srcset",a)}};var s=te(function(t,e,a,i,r){var n,s,o,l,u,f;if(!(u=X(t,"lazybeforeunveil",e)).defaultPrevented){if(i){if(a){K(t,H.autosizesClass)}else{t.setAttribute("sizes",i)}}s=t[$](H.srcsetAttr);n=t[$](H.srcAttr);if(r){o=t.parentNode;l=o&&j.test(o.nodeName||"")}f=e.firesLoad||"src"in t&&(s||n||l);u={target:t};K(t,H.loadingClass);if(f){clearTimeout(c);c=I(L,2500);V(t,B,true)}if(l){G.call(o.getElementsByTagName("source"),F)}if(s){t.setAttribute("srcset",s)}else if(n&&!l){if(d.test(t.nodeName)){T(t,n)}else{t.src=n}}if(r&&(s||l)){Y(t,{src:n})}}if(t._lazyRace){delete t._lazyRace}Q(t,H.lazyClass);ee(function(){var e=t.complete&&t.naturalWidth>1;if(!f||e){if(e){K(t,H.fastLoadedClass)}S(u);t._lazyCache=true;I(function(){if("_lazyCache"in t){delete t._lazyCache}},9)}if(t.loading=="lazy"){M--}},true)});var R=function(e){if(e._lazyRace){return}var t;var a=n.test(e.nodeName);var i=a&&(e[$](H.sizesAttr)||e[$]("sizes"));var r=i=="auto";if((r||!m)&&a&&(e[$]("src")||e.srcset)&&!e.complete&&!J(e,H.errorClass)&&J(e,H.lazyClass)){return}t=X(e,"lazyunveilread").detail;if(r){re.updateElem(e,true,e.offsetWidth)}e._lazyRace=true;M++;s(e,t,r,i,a)};var r=ie(function(){H.loadMode=3;a()});var o=function(){if(H.loadMode==3){H.loadMode=2}r()};var l=function(){if(m){return}if(f.now()-e<999){I(l,999);return}m=true;H.loadMode=3;a();q("scroll",o,true)};return{_:function(){e=f.now();k.elements=D.getElementsByClassName(H.lazyClass);v=D.getElementsByClassName(H.lazyClass+" "+H.preloadClass);q("scroll",a,true);q("resize",a,true);q("pageshow",function(e){if(e.persisted){var t=D.querySelectorAll("."+H.loadingClass);if(t.length&&t.forEach){U(function(){t.forEach(function(e){if(e.complete){R(e)}})})}}});if(u.MutationObserver){new MutationObserver(a).observe(O,{childList:true,subtree:true,attributes:true})}else{O[P]("DOMNodeInserted",a,true);O[P]("DOMAttrModified",a,true);setInterval(a,999)}q("hashchange",a,true);["focus","mouseover","click","load","transitionend","animationend"].forEach(function(e){D[P](e,a,true)});if(/d$|^c/.test(D.readyState)){l()}else{q("load",l);D[P]("DOMContentLoaded",a);I(l,2e4)}if(k.elements.length){t();ee._lsFlush()}else{a()}},checkElems:a,unveil:R,_aLSL:o}}(),re=function(){var a;var n=te(function(e,t,a,i){var r,n,s;e._lazysizesWidth=i;i+="px";e.setAttribute("sizes",i);if(j.test(t.nodeName||"")){r=t.getElementsByTagName("source");for(n=0,s=r.length;n<s;n++){r[n].setAttribute("sizes",i)}}if(!a.detail.dataAttr){Y(e,a.detail)}});var i=function(e,t,a){var i;var r=e.parentNode;if(r){a=s(e,r,a);i=X(e,"lazybeforesizes",{width:a,dataAttr:!!t});if(!i.defaultPrevented){a=i.detail.width;if(a&&a!==e._lazysizesWidth){n(e,r,i,a)}}}};var e=function(){var e;var t=a.length;if(t){e=0;for(;e<t;e++){i(a[e])}}};var t=ie(e);return{_:function(){a=D.getElementsByClassName(H.autosizesClass);q("resize",t)},checkElems:t,updateElem:i}}(),t=function(){if(!t.i&&D.getElementsByClassName){t.i=true;re._();e._()}};return I(function(){H.init&&t()}),k={cfg:H,autoSizer:re,loader:e,init:t,uP:Y,aC:K,rC:Q,hC:J,fire:X,gW:s,rAF:ee}}(e,e.document,Date);e.lazySizes=t,"object"==typeof module&&module.exports&&(module.exports=t)}("undefined"!=typeof window?window:{});
/* mousetrap v1.6.5 craig.is/killing/mice */
(function(q,u,c){function v(a,b,g){a.addEventListener?a.addEventListener(b,g,!1):a.attachEvent("on"+b,g)}function z(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return n[a.which]?n[a.which]:r[a.which]?r[a.which]:String.fromCharCode(a.which).toLowerCase()}function F(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function w(a){return"shift"==a||"ctrl"==a||"alt"==a||
"meta"==a}function A(a,b){var g,d=[];var e=a;"+"===e?e=["+"]:(e=e.replace(/\+{2}/g,"+plus"),e=e.split("+"));for(g=0;g<e.length;++g){var m=e[g];B[m]&&(m=B[m]);b&&"keypress"!=b&&C[m]&&(m=C[m],d.push("shift"));w(m)&&d.push(m)}e=m;g=b;if(!g){if(!p){p={};for(var c in n)95<c&&112>c||n.hasOwnProperty(c)&&(p[n[c]]=c)}g=p[e]?"keydown":"keypress"}"keypress"==g&&d.length&&(g="keydown");return{key:m,modifiers:d,action:g}}function D(a,b){return null===a||a===u?!1:a===b?!0:D(a.parentNode,b)}function d(a){function b(a){a=
a||{};var b=!1,l;for(l in p)a[l]?b=!0:p[l]=0;b||(x=!1)}function g(a,b,t,f,g,d){var l,E=[],h=t.type;if(!k._callbacks[a])return[];"keyup"==h&&w(a)&&(b=[a]);for(l=0;l<k._callbacks[a].length;++l){var c=k._callbacks[a][l];if((f||!c.seq||p[c.seq]==c.level)&&h==c.action){var e;(e="keypress"==h&&!t.metaKey&&!t.ctrlKey)||(e=c.modifiers,e=b.sort().join(",")===e.sort().join(","));e&&(e=f&&c.seq==f&&c.level==d,(!f&&c.combo==g||e)&&k._callbacks[a].splice(l,1),E.push(c))}}return E}function c(a,b,c,f){k.stopCallback(b,
b.target||b.srcElement,c,f)||!1!==a(b,c)||(b.preventDefault?b.preventDefault():b.returnValue=!1,b.stopPropagation?b.stopPropagation():b.cancelBubble=!0)}function e(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=z(a);b&&("keyup"==a.type&&y===b?y=!1:k.handleKey(b,F(a),a))}function m(a,g,t,f){function h(c){return function(){x=c;++p[a];clearTimeout(q);q=setTimeout(b,1E3)}}function l(g){c(t,g,a);"keyup"!==f&&(y=z(g));setTimeout(b,10)}for(var d=p[a]=0;d<g.length;++d){var e=d+1===g.length?l:h(f||
A(g[d+1]).action);n(g[d],e,f,a,d)}}function n(a,b,c,f,d){k._directMap[a+":"+c]=b;a=a.replace(/\s+/g," ");var e=a.split(" ");1<e.length?m(a,e,b,c):(c=A(a,c),k._callbacks[c.key]=k._callbacks[c.key]||[],g(c.key,c.modifiers,{type:c.action},f,a,d),k._callbacks[c.key][f?"unshift":"push"]({callback:b,modifiers:c.modifiers,action:c.action,seq:f,level:d,combo:a}))}var k=this;a=a||u;if(!(k instanceof d))return new d(a);k.target=a;k._callbacks={};k._directMap={};var p={},q,y=!1,r=!1,x=!1;k._handleKey=function(a,
d,e){var f=g(a,d,e),h;d={};var k=0,l=!1;for(h=0;h<f.length;++h)f[h].seq&&(k=Math.max(k,f[h].level));for(h=0;h<f.length;++h)f[h].seq?f[h].level==k&&(l=!0,d[f[h].seq]=1,c(f[h].callback,e,f[h].combo,f[h].seq)):l||c(f[h].callback,e,f[h].combo);f="keypress"==e.type&&r;e.type!=x||w(a)||f||b(d);r=l&&"keydown"==e.type};k._bindMultiple=function(a,b,c){for(var d=0;d<a.length;++d)n(a[d],b,c)};v(a,"keypress",e);v(a,"keydown",e);v(a,"keyup",e)}if(q){var n={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",
18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},r={106:"*",107:"+",109:"-",110:".",111:"/",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},C={"~":"`","!":"1","@":"2","#":"3",$:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},B={option:"alt",command:"meta","return":"enter",
escape:"esc",plus:"+",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},p;for(c=1;20>c;++c)n[111+c]="f"+c;for(c=0;9>=c;++c)n[c+96]=c.toString();d.prototype.bind=function(a,b,c){a=a instanceof Array?a:[a];this._bindMultiple.call(this,a,b,c);return this};d.prototype.unbind=function(a,b){return this.bind.call(this,a,function(){},b)};d.prototype.trigger=function(a,b){if(this._directMap[a+":"+b])this._directMap[a+":"+b]({},a);return this};d.prototype.reset=function(){this._callbacks={};
this._directMap={};return this};d.prototype.stopCallback=function(a,b){if(-1<(" "+b.className+" ").indexOf(" mousetrap ")||D(b,this.target))return!1;if("composedPath"in a&&"function"===typeof a.composedPath){var c=a.composedPath()[0];c!==a.target&&(b=c)}return"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable};d.prototype.handleKey=function(){return this._handleKey.apply(this,arguments)};d.addKeycodes=function(a){for(var b in a)a.hasOwnProperty(b)&&(n[b]=a[b]);p=null};
d.init=function(){var a=d(u),b;for(b in a)"_"!==b.charAt(0)&&(d[b]=function(b){return function(){return a[b].apply(a,arguments)}}(b))};d.init();q.Mousetrap=d;"undefined"!==typeof module&&module.exports&&(module.exports=d);"function"===typeof define&&define.amd&&define(function(){return d})}})("undefined"!==typeof window?window:null,"undefined"!==typeof window?document:null);

(function(a){var c={},d=a.prototype.stopCallback;a.prototype.stopCallback=function(e,b,a,f){return this.paused?!0:c[a]||c[f]?!1:d.call(this,e,b,a)};a.prototype.bindGlobal=function(a,b,d){this.bind(a,b,d);if(a instanceof Array)for(b=0;b<a.length;b++)c[a[b]]=!0;else c[a]=!0};a.init()})(Mousetrap);

!function(n){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=n();else if("function"==typeof define&&define.amd)define([],n);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.basicModal=n()}}(function(){return function n(t,e,o){function l(c,s){if(!e[c]){if(!t[c]){var i="function"==typeof require&&require;if(!s&&i)return i(c,!0);if(a)return a(c,!0);var r=new Error("Cannot find module '"+c+"'");throw r.code="MODULE_NOT_FOUND",r}var u=e[c]={exports:{}};t[c][0].call(u.exports,function(n){var e=t[c][1][n];return l(e||n)},u,u.exports,n,t,e,o)}return e[c].exports}for(var a="function"==typeof require&&require,c=0;c<o.length;c++)l(o[c]);return l}({1:[function(n,t,e){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=null,l=(e.THEME={small:"basicModal__small",xclose:"basicModal__xclose"},function(){var n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return!0===(arguments.length>1&&void 0!==arguments[1]&&arguments[1])?document.querySelectorAll(".basicModal "+n):document.querySelector(".basicModal "+n)}),a=function(n,t){return null!=n&&(n.constructor===Object?Array.prototype.forEach.call(Object.keys(n),function(e){return t(n[e],e,n)}):Array.prototype.forEach.call(n,function(e,o){return t(e,o,n)}))},c=function(n){return null==n||0===Object.keys(n).length?(console.error("Missing or empty modal configuration object"),!1):(null==n.body&&(n.body=""),null==n.class&&(n.class=""),!1!==n.closable&&(n.closable=!0),null==n.buttons?(console.error("basicModal requires at least one button"),!1):null!=n.buttons.action&&(null==n.buttons.action.class&&(n.buttons.action.class=""),null==n.buttons.action.title&&(n.buttons.action.title="OK"),null==n.buttons.action.fn)?(console.error("Missing fn for action-button"),!1):null==n.buttons.cancel||(null==n.buttons.cancel.class&&(n.buttons.cancel.class=""),null==n.buttons.cancel.title&&(n.buttons.cancel.title="Cancel"),null!=n.buttons.cancel.fn)||(console.error("Missing fn for cancel-button"),!1))},s=function(n){var t="";return t+="\n\t        <div class='basicModalContainer basicModalContainer--fadeIn' data-closable='"+n.closable+"'>\n\t            <div class='basicModal basicModal--fadeIn "+n.class+"' role=\"dialog\">\n\t                <div class='basicModal__content'>\n\t                    "+n.body+"\n\t                </div>\n\t                <div class='basicModal__buttons'>\n\t        ",null!=n.buttons.cancel&&(-1===n.buttons.cancel.class.indexOf("basicModal__xclose")?t+="<a id='basicModal__cancel' class='basicModal__button "+n.buttons.cancel.class+"'>"+n.buttons.cancel.title+"</a>":t+="<div id='basicModal__cancel' class='basicModal__button "+n.buttons.cancel.class+'\' aria-label=\'close\'><svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><path d="M405 136.798l-29.798-29.798-119.202 119.202-119.202-119.202-29.798 29.798 119.202 119.202-119.202 119.202 29.798 29.798 119.202-119.202 119.202 119.202 29.798-29.798-119.202-119.202z"/></svg></div>'),null!=n.buttons.action&&(t+="<a id='basicModal__action' class='basicModal__button "+n.buttons.action.class+"'>"+n.buttons.action.title+"</a>"),t+="\n\t                </div>\n\t            </div>\n\t        </div>\n\t        "},i=e.getValues=function(){var n={},t=l("input[name]",!0),e=l("select[name]",!0);return a(t,function(t){var e=t.getAttribute("name"),o=t.value;n[e]=o}),a(e,function(t){var e=t.getAttribute("name"),o=t.options[t.selectedIndex].value;n[e]=o}),0===Object.keys(n).length?null:n},r=function(n){return null!=n.buttons.cancel&&(l("#basicModal__cancel").onclick=function(){if(!0===this.classList.contains("basicModal__button--active"))return!1;this.classList.add("basicModal__button--active"),n.buttons.cancel.fn()}),null!=n.buttons.action&&(l("#basicModal__action").onclick=function(){if(!0===this.classList.contains("basicModal__button--active"))return!1;this.classList.add("basicModal__button--active"),n.buttons.action.fn(i())}),a(l("input",!0),function(n){n.oninput=n.onblur=function(){this.classList.remove("error")}}),a(l("select",!0),function(n){n.onchange=n.onblur=function(){this.classList.remove("error")}}),!0},u=(e.show=function n(t){if(!1===c(t))return!1;if(null!=l())return b(!0),setTimeout(function(){return n(t)},301),!1;o=document.activeElement;var e=s(t);document.body.insertAdjacentHTML("beforeend",e),r(t);var a=l("input");null!=a&&a.select();var i=l("select");return null==a&&null!=i&&i.focus(),null!=t.callback&&t.callback(t),!0},e.error=function(n){d();var t=l("input[name='"+n+"']")||l("select[name='"+n+"']");if(null==t)return!1;t.classList.add("error"),"function"==typeof t.select?t.select():t.focus(),l().classList.remove("basicModal--fadeIn","basicModal--shake"),setTimeout(function(){return l().classList.add("basicModal--shake")},1)},e.visible=function(){return null!=l()}),d=(e.action=function(){var n=l("#basicModal__action");return null!=n&&(n.click(),!0)},e.cancel=function(){var n=l("#basicModal__cancel");return null!=n&&(n.click(),!0)},e.reset=function(){var n=l(".basicModal__button",!0);a(n,function(n){return n.classList.remove("basicModal__button--active")});var t=l("input",!0);a(t,function(n){return n.classList.remove("error")});var e=l("select",!0);return a(e,function(n){return n.classList.remove("error")}),!0}),b=e.close=function(){var n=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(!1===u())return!1;var t=l().parentElement;return("false"!==t.getAttribute("data-closable")||!1!==n)&&(t.classList.remove("basicModalContainer--fadeIn"),t.classList.add("basicModalContainer--fadeOut"),setTimeout(function(){return null!=t&&(null!=t.parentElement&&void t.parentElement.removeChild(t))},300),null!=o&&(o.focus(),o=null),!0)}},{}]},{},[1])(1)});
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.scrollLock=t():e.scrollLock=t()}(this,function(){return function(l){var r={};function o(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return l[e].call(t.exports,t,t.exports,o),t.l=!0,t.exports}return o.m=l,o.c=r,o.d=function(e,t,l){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:l})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(t,e){if(1&e&&(t=o(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var l=Object.create(null);if(o.r(l),Object.defineProperty(l,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)o.d(l,r,function(e){return t[e]}.bind(null,r));return l},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="",o(o.s=0)}([function(e,t,l){"use strict";l.r(t);var r=function(e){return Array.isArray(e)?e:[e]},a=function(e){return e instanceof Node},o=function(e,t){if(e&&t){e=e instanceof NodeList?e:[e];for(var l=0;l<e.length&&!0!==t(e[l],l,e.length);l++);}},n=function(e){return console.error("[scroll-lock] ".concat(e))},b=function(e){if(Array.isArray(e))return e.join(", ")},c=function(e){var t=[];return o(e,function(e){return t.push(e)}),t},h=function(e,t){var l=!(2<arguments.length&&void 0!==arguments[2])||arguments[2],r=3<arguments.length&&void 0!==arguments[3]?arguments[3]:document;if(l&&-1!==c(r.querySelectorAll(t)).indexOf(e))return e;for(;(e=e.parentElement)&&-1===c(r.querySelectorAll(t)).indexOf(e););return e},v=function(e,t){var l=2<arguments.length&&void 0!==arguments[2]?arguments[2]:document;return-1!==c(l.querySelectorAll(t)).indexOf(e)},i=function(e){if(e)return"hidden"===getComputedStyle(e).overflow},m=function(e){if(e)return!!i(e)||e.scrollTop<=0},S=function(e){if(e){if(i(e))return!0;var t=e.scrollTop;return e.scrollHeight<=t+e.offsetHeight}},y=function(e){if(e)return!!i(e)||e.scrollLeft<=0},k=function(e){if(e){if(i(e))return!0;var t=e.scrollLeft;return e.scrollWidth<=t+e.offsetWidth}};l.d(t,"disablePageScroll",function(){return d}),l.d(t,"enablePageScroll",function(){return s}),l.d(t,"getScrollState",function(){return f}),l.d(t,"clearQueueScrollLocks",function(){return p}),l.d(t,"getTargetScrollBarWidth",function(){return g}),l.d(t,"getCurrentTargetScrollBarWidth",function(){return A}),l.d(t,"getPageScrollBarWidth",function(){return G}),l.d(t,"getCurrentPageScrollBarWidth",function(){return T}),l.d(t,"addScrollableTarget",function(){return L}),l.d(t,"removeScrollableTarget",function(){return W}),l.d(t,"addScrollableSelector",function(){return x}),l.d(t,"removeScrollableSelector",function(){return F}),l.d(t,"addLockableTarget",function(){return Y}),l.d(t,"addLockableSelector",function(){return E}),l.d(t,"setFillGapMethod",function(){return O}),l.d(t,"addFillGapTarget",function(){return P}),l.d(t,"removeFillGapTarget",function(){return j}),l.d(t,"addFillGapSelector",function(){return q}),l.d(t,"removeFillGapSelector",function(){return M}),l.d(t,"refillGaps",function(){return N});var u=["padding","margin","width","max-width","none"],w={scroll:!0,queue:0,scrollableSelectors:["[data-scroll-lock-scrollable]"],lockableSelectors:["body","[data-scroll-lock-lockable]"],fillGapSelectors:["body","[data-scroll-lock-fill-gap]","[data-scroll-lock-lockable]"],fillGapMethod:u[0],startTouchY:0,startTouchX:0},d=function(e){w.queue<=0&&(w.scroll=!1,B(),X()),L(e),w.queue++},s=function(e){0<w.queue&&w.queue--,w.queue<=0&&(w.scroll=!0,C(),Q()),W(e)},f=function(){return w.scroll},p=function(){w.queue=0},g=function(e){var t=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(a(e)){var l=e.style.overflowY;t?f()||(e.style.overflowY=e.getAttribute("data-scroll-lock-saved-overflow-y-property")):e.style.overflowY="scroll";var r=A(e);return e.style.overflowY=l,r}return 0},A=function(e){if(a(e)){if(e===document.body){var t=document.documentElement.clientWidth;return window.innerWidth-t}var l=e.style.borderLeftWidth,r=e.style.borderRightWidth;e.style.borderLeftWidth="0px",e.style.borderRightWidth="0px";var o=e.offsetWidth-e.clientWidth;return e.style.borderLeftWidth=l,e.style.borderRightWidth=r,o}return 0},G=function(){var e=0<arguments.length&&void 0!==arguments[0]&&arguments[0];return g(document.body,e)},T=function(){return A(document.body)},L=function(e){e&&r(e).map(function(e){o(e,function(e){a(e)?e.setAttribute("data-scroll-lock-scrollable",""):n('"'.concat(e,'" is not a Element.'))})})},W=function(e){e&&r(e).map(function(e){o(e,function(e){a(e)?e.removeAttribute("data-scroll-lock-scrollable"):n('"'.concat(e,'" is not a Element.'))})})},x=function(e){e&&r(e).map(function(e){w.scrollableSelectors.push(e)})},F=function(e){e&&r(e).map(function(t){w.scrollableSelectors=w.scrollableSelectors.filter(function(e){return e!==t})})},Y=function(e){e&&(r(e).map(function(e){o(e,function(e){a(e)?e.setAttribute("data-scroll-lock-lockable",""):n('"'.concat(e,'" is not a Element.'))})}),f()||B())},E=function(e){e&&(r(e).map(function(e){w.lockableSelectors.push(e)}),f()||B(),q(e))},O=function(e){if(e)if(-1!==u.indexOf(e))w.fillGapMethod=e,N();else{var t=u.join(", ");n('"'.concat(e,'" method is not available!\nAvailable fill gap methods: ').concat(t,"."))}},P=function(e){e&&r(e).map(function(e){o(e,function(e){a(e)?(e.setAttribute("data-scroll-lock-fill-gap",""),w.scroll||H(e)):n('"'.concat(e,'" is not a Element.'))})})},j=function(e){e&&r(e).map(function(e){o(e,function(e){a(e)?(e.removeAttribute("data-scroll-lock-fill-gap"),w.scroll||I(e)):n('"'.concat(e,'" is not a Element.'))})})},q=function(e){e&&r(e).map(function(e){-1===w.fillGapSelectors.indexOf(e)&&(w.fillGapSelectors.push(e),w.scroll||D(e))})},M=function(e){e&&r(e).map(function(t){w.fillGapSelectors=w.fillGapSelectors.filter(function(e){return e!==t}),w.scroll||z(t)})},N=function(){w.scroll||X()},B=function(){var e=b(w.lockableSelectors);K(e)},C=function(){var e=b(w.lockableSelectors);R(e)},K=function(e){var t=document.querySelectorAll(e);o(t,function(e){U(e)})},R=function(e){var t=document.querySelectorAll(e);o(t,function(e){_(e)})},U=function(e){if(a(e)&&"true"!==e.getAttribute("data-scroll-lock-locked")){var t=window.getComputedStyle(e);e.setAttribute("data-scroll-lock-saved-overflow-y-property",t.overflowY),e.setAttribute("data-scroll-lock-saved-inline-overflow-property",e.style.overflow),e.setAttribute("data-scroll-lock-saved-inline-overflow-y-property",e.style.overflowY),e.style.overflow="hidden",e.setAttribute("data-scroll-lock-locked","true")}},_=function(e){a(e)&&"true"===e.getAttribute("data-scroll-lock-locked")&&(e.style.overflow=e.getAttribute("data-scroll-lock-saved-inline-overflow-property"),e.style.overflowY=e.getAttribute("data-scroll-lock-saved-inline-overflow-y-property"),e.removeAttribute("data-scroll-lock-saved-overflow-property"),e.removeAttribute("data-scroll-lock-saved-inline-overflow-property"),e.removeAttribute("data-scroll-lock-saved-inline-overflow-y-property"),e.removeAttribute("data-scroll-lock-locked"))},X=function(){w.fillGapSelectors.map(function(e){D(e)})},Q=function(){w.fillGapSelectors.map(function(e){z(e)})},D=function(e){var t=document.querySelectorAll(e),l=-1!==w.lockableSelectors.indexOf(e);o(t,function(e){H(e,l)})},H=function(e){var t=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(a(e)){var l;if(""===e.getAttribute("data-scroll-lock-lockable")||t)l=g(e,!0);else{var r=h(e,b(w.lockableSelectors));l=g(r,!0)}"true"===e.getAttribute("data-scroll-lock-filled-gap")&&I(e);var o=window.getComputedStyle(e);if(e.setAttribute("data-scroll-lock-filled-gap","true"),e.setAttribute("data-scroll-lock-current-fill-gap-method",w.fillGapMethod),"margin"===w.fillGapMethod){var n=parseFloat(o.marginRight);e.style.marginRight="".concat(n+l,"px")}else if("width"===w.fillGapMethod)e.style.width="calc(100% - ".concat(l,"px)");else if("max-width"===w.fillGapMethod)e.style.maxWidth="calc(100% - ".concat(l,"px)");else if("padding"===w.fillGapMethod){var c=parseFloat(o.paddingRight);e.style.paddingRight="".concat(c+l,"px")}}},z=function(e){var t=document.querySelectorAll(e);o(t,function(e){I(e)})},I=function(e){if(a(e)&&"true"===e.getAttribute("data-scroll-lock-filled-gap")){var t=e.getAttribute("data-scroll-lock-current-fill-gap-method");e.removeAttribute("data-scroll-lock-filled-gap"),e.removeAttribute("data-scroll-lock-current-fill-gap-method"),"margin"===t?e.style.marginRight="":"width"===t?e.style.width="":"max-width"===t?e.style.maxWidth="":"padding"===t&&(e.style.paddingRight="")}};"undefined"!=typeof window&&window.addEventListener("resize",function(e){N()}),"undefined"!=typeof document&&(document.addEventListener("touchstart",function(e){w.scroll||(w.startTouchY=e.touches[0].clientY,w.startTouchX=e.touches[0].clientX)}),document.addEventListener("touchmove",function(n){if(!w.scroll){var e=w.startTouchY,t=w.startTouchX,l=n.touches[0].clientY,r=n.touches[0].clientX;if(n.touches.length<2){var c=b(w.scrollableSelectors),a=e<l,i=l<e,u=t<r,d=r<t,s=e+3<l,f=l<e-3,p=t+3<r,g=r<t-3;!function e(t){var l=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(t){var r=h(t,c,!1);if(v(t,'input[type="range"]'))return!1;if(l||v(t,'textarea, [contenteditable="true"]')&&h(t,c)||v(t,c)){var o=!1;y(t)&&k(t)?(a&&m(t)||i&&S(t))&&(o=!0):m(t)&&S(t)?(u&&y(t)||d&&k(t))&&(o=!0):(s&&m(t)||f&&S(t)||p&&y(t)||g&&k(t))&&(o=!0),o&&(r?e(r,!0):n.cancelable&&n.preventDefault())}else e(r)}else n.cancelable&&n.preventDefault()}(n.target)}}},{passive:!1}),document.addEventListener("touchend",function(e){w.scroll||(w.startTouchY=0,w.startTouchX=0)}));var J={hide:function(e){n('"hide" is deprecated! Use "disablePageScroll" instead. \n https://github.com/FL3NKEY/scroll-lock#disablepagescrollscrollabletarget'),d(e)},show:function(e){n('"show" is deprecated! Use "enablePageScroll" instead. \n https://github.com/FL3NKEY/scroll-lock#enablepagescrollscrollabletarget'),s(e)},toggle:function(e){n('"toggle" is deprecated! Do not use it.'),f()?d():s(e)},getState:function(){return n('"getState" is deprecated! Use "getScrollState" instead. \n https://github.com/FL3NKEY/scroll-lock#getscrollstate'),f()},getWidth:function(){return n('"getWidth" is deprecated! Use "getPageScrollBarWidth" instead. \n https://github.com/FL3NKEY/scroll-lock#getpagescrollbarwidth'),G()},getCurrentWidth:function(){return n('"getCurrentWidth" is deprecated! Use "getCurrentPageScrollBarWidth" instead. \n https://github.com/FL3NKEY/scroll-lock#getcurrentpagescrollbarwidth'),T()},setScrollableTargets:function(e){n('"setScrollableTargets" is deprecated! Use "addScrollableTarget" instead. \n https://github.com/FL3NKEY/scroll-lock#addscrollabletargetscrollabletarget'),L(e)},setFillGapSelectors:function(e){n('"setFillGapSelectors" is deprecated! Use "addFillGapSelector" instead. \n https://github.com/FL3NKEY/scroll-lock#addfillgapselectorfillgapselector'),q(e)},setFillGapTargets:function(e){n('"setFillGapTargets" is deprecated! Use "addFillGapTarget" instead. \n https://github.com/FL3NKEY/scroll-lock#addfillgaptargetfillgaptarget'),P(e)},clearQueue:function(){n('"clearQueue" is deprecated! Use "clearQueueScrollLocks" instead. \n https://github.com/FL3NKEY/scroll-lock#clearqueuescrolllocks'),p()}},V=function(o){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{},t=Object.keys(n);"function"==typeof Object.getOwnPropertySymbols&&(t=t.concat(Object.getOwnPropertySymbols(n).filter(function(e){return Object.getOwnPropertyDescriptor(n,e).enumerable}))),t.forEach(function(e){var t,l,r;t=o,r=n[l=e],l in t?Object.defineProperty(t,l,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[l]=r})}return o}({disablePageScroll:d,enablePageScroll:s,getScrollState:f,clearQueueScrollLocks:p,getTargetScrollBarWidth:g,getCurrentTargetScrollBarWidth:A,getPageScrollBarWidth:G,getCurrentPageScrollBarWidth:T,addScrollableSelector:x,removeScrollableSelector:F,addScrollableTarget:L,removeScrollableTarget:W,addLockableSelector:E,addLockableTarget:Y,addFillGapSelector:q,removeFillGapSelector:M,addFillGapTarget:P,removeFillGapTarget:j,setFillGapMethod:O,refillGaps:N,_state:w},J);t.default=V}]).default});
/*
 * @license
 *
 * Multiselect v2.5.6
 * http://crlcu.github.io/multiselect/
 *
 * Copyright (c) 2016-2020 Adrian Crisan
 * Licensed under the MIT license (https://github.com/crlcu/multiselect/blob/master/LICENSE)
 */

if("undefined"==typeof jQuery)throw new Error("multiselect requires jQuery");!function(){"use strict";var t=jQuery.fn.jquery.split(" ")[0].split(".");if(t[0]<2&&t[1]<7)throw new Error("multiselect requires jQuery version 1.7 or higher")}(),function(t){"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)}(function(i){"use strict";var s,r=(s=i,t.prototype={init:function(){var o=this;o.undoStack=[],o.redoStack=[],o.options.keepRenderingSort&&(!(o.skipInitSort=!0)!==o.callbacks.sort&&(o.callbacks.sort={left:function(t,e){return s(t).data("position")>s(e).data("position")?1:-1},right:function(t,e){return s(t).data("position")>s(e).data("position")?1:-1}}),o.$left.attachIndex(),o.$right.each(function(t,e){s(e).attachIndex()})),"function"==typeof o.callbacks.startUp&&o.callbacks.startUp(o.$left,o.$right),o.skipInitSort||("function"==typeof o.callbacks.sort.left&&o.$left.mSort(o.callbacks.sort.left),"function"==typeof o.callbacks.sort.right&&o.$right.each(function(t,e){s(e).mSort(o.callbacks.sort.right)})),o.options.search&&o.options.search.left&&(o.options.search.$left=s(o.options.search.left),o.$left.before(o.options.search.$left)),o.options.search&&o.options.search.right&&(o.options.search.$right=s(o.options.search.right),o.$right.before(s(o.options.search.$right))),o.events(),"function"==typeof o.callbacks.afterInit&&o.callbacks.afterInit()},events:function(){var o=this;o.options.search&&o.options.search.$left&&o.options.search.$left.on("keyup",function(t){o.callbacks.fireSearch(this.value)?(o.$left.find('option:search("'+this.value+'")').mShow(),o.$left.find('option:not(:search("'+this.value+'"))').mHide(),o.$left.find("option").closest("optgroup").mHide(),o.$left.find("option:not(.hidden)").parent("optgroup").mShow()):o.$left.find("option, optgroup").mShow()}),o.options.search&&o.options.search.$right&&o.options.search.$right.on("keyup",function(t){o.callbacks.fireSearch(this.value)?(o.$right.find('option:search("'+this.value+'")').mShow(),o.$right.find('option:not(:search("'+this.value+'"))').mHide(),o.$right.find("option").closest("optgroup").mHide(),o.$right.find("option:not(.hidden)").parent("optgroup").mShow()):o.$right.find("option, optgroup").mShow()}),o.$right.closest("form").on("submit",function(t){o.options.search&&(o.options.search.$left&&o.options.search.$left.val("").trigger("keyup"),o.options.search.$right&&o.options.search.$right.val("").trigger("keyup")),o.$left.find("option").prop("selected",o.options.submitAllLeft),o.$right.find("option").prop("selected",o.options.submitAllRight)}),o.$left.on("dblclick","option",function(t){t.preventDefault();var e=o.$left.find("option:selected:not(.hidden)");e.length&&o.moveToRight(e,t)}),o.$left.on("click","optgroup",function(t){"OPTGROUP"==s(t.target).prop("tagName")&&s(this).children().prop("selected",!0)}),o.$left.on("keypress",function(t){var e;13===t.keyCode&&(t.preventDefault(),(e=o.$left.find("option:selected:not(.hidden)")).length&&o.moveToRight(e,t))}),o.$right.on("dblclick","option",function(t){t.preventDefault();var e=o.$right.find("option:selected:not(.hidden)");e.length&&o.moveToLeft(e,t)}),o.$right.on("click","optgroup",function(t){"OPTGROUP"==s(t.target).prop("tagName")&&s(this).children().prop("selected",!0)}),o.$right.on("keydown",function(t){var e;8!==t.keyCode&&46!==t.keyCode||(t.preventDefault(),(e=o.$right.find("option:selected:not(.hidden)")).length&&o.moveToLeft(e,t))}),(navigator.userAgent.match(/MSIE/i)||0<navigator.userAgent.indexOf("Trident/")||0<navigator.userAgent.indexOf("Edge/"))&&(o.$left.dblclick(function(t){o.actions.$rightSelected.trigger("click")}),o.$right.dblclick(function(t){o.actions.$leftSelected.trigger("click")})),o.actions.$rightSelected.on("click",function(t){t.preventDefault();var e=o.$left.find("option:selected:not(.hidden)");e.length&&o.moveToRight(e,t),s(this).blur()}),o.actions.$leftSelected.on("click",function(t){t.preventDefault();var e=o.$right.find("option:selected:not(.hidden)");e.length&&o.moveToLeft(e,t),s(this).blur()}),o.actions.$rightAll.on("click",function(t){t.preventDefault();var e=o.$left.children(":not(span):not(.hidden)");e.length&&o.moveToRight(e,t),s(this).blur()}),o.actions.$leftAll.on("click",function(t){t.preventDefault();var e=o.$right.children(":not(span):not(.hidden)");e.length&&o.moveToLeft(e,t),s(this).blur()}),o.actions.$undo.on("click",function(t){t.preventDefault(),o.undo(t)}),o.actions.$redo.on("click",function(t){t.preventDefault(),o.redo(t)}),o.actions.$moveUp.on("click",function(t){t.preventDefault();var e=o.$right.find(":selected:not(span):not(.hidden)");e.length&&o.moveUp(e,t),s(this).blur()}),o.actions.$moveDown.on("click",function(t){t.preventDefault();var e=o.$right.find(":selected:not(span):not(.hidden)");e.length&&o.moveDown(e,t),s(this).blur()})},moveToRight:function(t,e,o,n){var i=this;return"function"==typeof i.callbacks.moveToRight?i.callbacks.moveToRight(i,t,e,o,n):!("function"==typeof i.callbacks.beforeMoveToRight&&!o&&!i.callbacks.beforeMoveToRight(i.$left,i.$right,t))&&(i.moveFromAtoB(i.$left,i.$right,t,e,o,n),n||(i.undoStack.push(["right",t]),i.redoStack=[]),"function"!=typeof i.callbacks.sort.right||o||i.doNotSortRight||i.$right.mSort(i.callbacks.sort.right),"function"!=typeof i.callbacks.afterMoveToRight||o||i.callbacks.afterMoveToRight(i.$left,i.$right,t),i)},moveToLeft:function(t,e,o,n){var i=this;return"function"==typeof i.callbacks.moveToLeft?i.callbacks.moveToLeft(i,t,e,o,n):!("function"==typeof i.callbacks.beforeMoveToLeft&&!o&&!i.callbacks.beforeMoveToLeft(i.$left,i.$right,t))&&(i.moveFromAtoB(i.$right,i.$left,t,e,o,n),n||(i.undoStack.push(["left",t]),i.redoStack=[]),"function"!=typeof i.callbacks.sort.left||o||i.$left.mSort(i.callbacks.sort.left),"function"!=typeof i.callbacks.afterMoveToLeft||o||i.callbacks.afterMoveToLeft(i.$left,i.$right,t),i)},moveFromAtoB:function(t,c,e,o,n,i){var a=this;return"function"==typeof a.callbacks.moveFromAtoB?a.callbacks.moveFromAtoB(a,t,c,e,o,n,i):(e.each(function(t,e){var o,n,i,r,l=s(e);if(a.options.ignoreDisabled&&l.is(":disabled"))return!0;l.is("optgroup")||l.parent().is("optgroup")?(o=l.is("optgroup")?l:l.parent(),n="optgroup["+a.options.matchOptgroupBy+'="'+o.prop(a.options.matchOptgroupBy)+'"]',(i=c.find(n)).length||((i=o.clone(!0)).empty(),c.move(i)),l.is("optgroup")?(r="",a.options.ignoreDisabled&&(r=":not(:disabled)"),i.move(l.find("option"+r))):i.move(l),o.removeIfEmpty()):c.move(l)}),a)},moveUp:function(t){if("function"==typeof this.callbacks.beforeMoveUp&&!this.callbacks.beforeMoveUp(t))return!1;t.first().prev().before(t),"function"==typeof this.callbacks.afterMoveUp&&this.callbacks.afterMoveUp(t)},moveDown:function(t){if("function"==typeof this.callbacks.beforeMoveDown&&!this.callbacks.beforeMoveDown(t))return!1;t.last().next().after(t),"function"==typeof this.callbacks.afterMoveDown&&this.callbacks.afterMoveDown(t)},undo:function(t){var e=this.undoStack.pop();if(e)switch(this.redoStack.push(e),e[0]){case"left":this.moveToRight(e[1],t,!1,!0);break;case"right":this.moveToLeft(e[1],t,!1,!0)}},redo:function(t){var e=this.redoStack.pop();if(e)switch(this.undoStack.push(e),e[0]){case"left":this.moveToLeft(e[1],t,!1,!0);break;case"right":this.moveToRight(e[1],t,!1,!0)}}},t);function t(t,e){var o,n=t.prop("id");this.$left=t,this.$right=s(e.right).length?s(e.right):s("#"+n+"_to"),this.actions={$leftAll:s(e.leftAll).length?s(e.leftAll):s("#"+n+"_leftAll"),$rightAll:s(e.rightAll).length?s(e.rightAll):s("#"+n+"_rightAll"),$leftSelected:s(e.leftSelected).length?s(e.leftSelected):s("#"+n+"_leftSelected"),$rightSelected:s(e.rightSelected).length?s(e.rightSelected):s("#"+n+"_rightSelected"),$undo:s(e.undo).length?s(e.undo):s("#"+n+"_undo"),$redo:s(e.redo).length?s(e.redo):s("#"+n+"_redo"),$moveUp:s(e.moveUp).length?s(e.moveUp):s("#"+n+"_move_up"),$moveDown:s(e.moveDown).length?s(e.moveDown):s("#"+n+"_move_down")},delete e.leftAll,delete e.leftSelected,delete e.right,delete e.rightAll,delete e.rightSelected,delete e.undo,delete e.redo,delete e.moveUp,delete e.moveDown,this.options={keepRenderingSort:e.keepRenderingSort,submitAllLeft:void 0===e.submitAllLeft||e.submitAllLeft,submitAllRight:void 0===e.submitAllRight||e.submitAllRight,search:e.search,ignoreDisabled:void 0!==e.ignoreDisabled&&e.ignoreDisabled,matchOptgroupBy:void 0!==e.matchOptgroupBy?e.matchOptgroupBy:"label"},delete e.keepRenderingSort,e.submitAllLeft,e.submitAllRight,e.search,e.ignoreDisabled,e.matchOptgroupBy,this.callbacks=e,"function"==typeof this.callbacks.sort&&(o=this.callbacks.sort,this.callbacks.sort={left:o,right:o}),this.init()}i.multiselect={defaults:{startUp:function(n,t){t.find("option").each(function(t,e){var o;"OPTGROUP"==i(e).parent().prop("tagName")?(o='optgroup[label="'+i(e).parent().attr("label")+'"]',n.find(o+' option[value="'+e.value+'"]').each(function(t,e){e.remove()}),n.find(o).removeIfEmpty()):n.find('option[value="'+e.value+'"]').remove()})},afterInit:function(){return!0},beforeMoveToRight:function(t,e,o){return!0},afterMoveToRight:function(t,e,o){},beforeMoveToLeft:function(t,e,o){return!0},afterMoveToLeft:function(t,e,o){},beforeMoveUp:function(t){return!0},afterMoveUp:function(t){},beforeMoveDown:function(t){return!0},afterMoveDown:function(t){},sort:function(t,e){return"NA"==t.innerHTML||"NA"!=e.innerHTML&&t.innerHTML>e.innerHTML?1:-1},fireSearch:function(t){return 1<t.length}}};var e=window.navigator.userAgent,o=-3<e.indexOf("MSIE ")+e.indexOf("Trident/")+e.indexOf("Edge/"),n=-1<e.toLowerCase().indexOf("safari"),l=-1<e.toLowerCase().indexOf("firefox");i.fn.multiselect=function(n){return this.each(function(){var t=i(this),e=t.data("crlcu.multiselect"),o=i.extend({},i.multiselect.defaults,t.data(),"object"==typeof n&&n);e||t.data("crlcu.multiselect",e=new r(t,o))})},i.fn.move=function(t){return this.append(t).find("option").prop("selected",!1),this},i.fn.removeIfEmpty=function(){return this.children().length||this.remove(),this},i.fn.mShow=function(){return this.removeClass("hidden").show(),(o||n)&&this.each(function(t,e){i(e).parent().is("span")&&i(e).parent().replaceWith(e),i(e).show()}),l&&this.prop("disabled",!1),this},i.fn.mHide=function(){return this.addClass("hidden").hide(),(o||n)&&this.each(function(t,e){i(e).parent().is("span")||i(e).wrap("<span>").hide()}),l&&this.prop("disabled",!0),this},i.fn.mSort=function(o){return this.children().sort(o).appendTo(this),this.find("optgroup").each(function(t,e){i(e).children().sort(o).appendTo(e)}),this},i.fn.attachIndex=function(){this.children().each(function(t,e){var o=i(e);o.is("optgroup")&&o.children().each(function(t,e){i(e).data("position",t)}),o.data("position",t)})},i.expr[":"].search=function(t,e,o){var n=new RegExp(o[3].replace(/([^a-zA-Z0-9])/g,"\\$1"),"i");return i(t).text().match(n)}});
require=function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s}({1:[function(require,module,exports){/*!
 * Copyright 2016 Yahoo Inc.
 * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
 * @license
 */
var merge=require("merge");var Row=module.exports=function(params){this.top=params.top;this.left=params.left;this.width=params.width;this.spacing=params.spacing;this.targetRowHeight=params.targetRowHeight;this.targetRowHeightTolerance=params.targetRowHeightTolerance;this.minAspectRatio=this.width/params.targetRowHeight*(1-params.targetRowHeightTolerance);this.maxAspectRatio=this.width/params.targetRowHeight*(1+params.targetRowHeightTolerance);this.edgeCaseMinRowHeight=params.edgeCaseMinRowHeight;this.edgeCaseMaxRowHeight=params.edgeCaseMaxRowHeight;this.widowLayoutStyle=params.widowLayoutStyle;this.isBreakoutRow=params.isBreakoutRow;this.items=[];this.height=0};Row.prototype={addItem:function(itemData){var newItems=this.items.concat(itemData),rowWidthWithoutSpacing=this.width-(newItems.length-1)*this.spacing,newAspectRatio=newItems.reduce(function(sum,item){return sum+item.aspectRatio},0),targetAspectRatio=rowWidthWithoutSpacing/this.targetRowHeight,previousRowWidthWithoutSpacing,previousAspectRatio,previousTargetAspectRatio;if(this.isBreakoutRow){if(this.items.length===0){if(itemData.aspectRatio>=1){this.items.push(itemData);this.completeLayout(rowWidthWithoutSpacing/itemData.aspectRatio,"justify");return true}}}if(newAspectRatio<this.minAspectRatio){this.items.push(merge(itemData));return true}else if(newAspectRatio>this.maxAspectRatio){if(this.items.length===0){this.items.push(merge(itemData));this.completeLayout(rowWidthWithoutSpacing/newAspectRatio,"justify");return true}previousRowWidthWithoutSpacing=this.width-(this.items.length-1)*this.spacing;previousAspectRatio=this.items.reduce(function(sum,item){return sum+item.aspectRatio},0);previousTargetAspectRatio=previousRowWidthWithoutSpacing/this.targetRowHeight;if(Math.abs(newAspectRatio-targetAspectRatio)>Math.abs(previousAspectRatio-previousTargetAspectRatio)){this.completeLayout(previousRowWidthWithoutSpacing/previousAspectRatio,"justify");return false}else{this.items.push(merge(itemData));this.completeLayout(rowWidthWithoutSpacing/newAspectRatio,"justify");return true}}else{this.items.push(merge(itemData));this.completeLayout(rowWidthWithoutSpacing/newAspectRatio,"justify");return true}},isLayoutComplete:function(){return this.height>0},completeLayout:function(newHeight,widowLayoutStyle){var itemWidthSum=this.left,rowWidthWithoutSpacing=this.width-(this.items.length-1)*this.spacing,clampedToNativeRatio,clampedHeight,errorWidthPerItem,roundedCumulativeErrors,singleItemGeometry,centerOffset;if(typeof widowLayoutStyle==="undefined"||["justify","center","left"].indexOf(widowLayoutStyle)<0){widowLayoutStyle="left"}clampedHeight=Math.max(this.edgeCaseMinRowHeight,Math.min(newHeight,this.edgeCaseMaxRowHeight));if(newHeight!==clampedHeight){this.height=clampedHeight;clampedToNativeRatio=rowWidthWithoutSpacing/clampedHeight/(rowWidthWithoutSpacing/newHeight)}else{this.height=newHeight;clampedToNativeRatio=1}this.items.forEach(function(item){item.top=this.top;item.width=item.aspectRatio*this.height*clampedToNativeRatio;item.height=this.height;item.left=itemWidthSum;itemWidthSum+=item.width+this.spacing},this);if(widowLayoutStyle==="justify"){itemWidthSum-=this.spacing+this.left;errorWidthPerItem=(itemWidthSum-this.width)/this.items.length;roundedCumulativeErrors=this.items.map(function(item,i){return Math.round((i+1)*errorWidthPerItem)});if(this.items.length===1){singleItemGeometry=this.items[0];singleItemGeometry.width-=Math.round(errorWidthPerItem)}else{this.items.forEach(function(item,i){if(i>0){item.left-=roundedCumulativeErrors[i-1];item.width-=roundedCumulativeErrors[i]-roundedCumulativeErrors[i-1]}else{item.width-=roundedCumulativeErrors[i]}})}}else if(widowLayoutStyle==="center"){centerOffset=(this.width-itemWidthSum)/2;this.items.forEach(function(item){item.left+=centerOffset+this.spacing},this)}},forceComplete:function(fitToWidth,rowHeight){if(typeof rowHeight==="number"){this.completeLayout(rowHeight,this.widowLayoutStyle)}else{this.completeLayout(this.targetRowHeight,this.widowLayoutStyle)}},getItems:function(){return this.items}}},{merge:2}],2:[function(require,module,exports){(function(isNode){var Public=function(clone){return merge(clone===true,false,arguments)},publicName="merge";Public.recursive=function(clone){return merge(clone===true,true,arguments)};Public.clone=function(input){var output=input,type=typeOf(input),index,size;if(type==="array"){output=[];size=input.length;for(index=0;index<size;++index)output[index]=Public.clone(input[index])}else if(type==="object"){output={};for(index in input)output[index]=Public.clone(input[index])}return output};function merge_recursive(base,extend){if(typeOf(base)!=="object")return extend;for(var key in extend){if(typeOf(base[key])==="object"&&typeOf(extend[key])==="object"){base[key]=merge_recursive(base[key],extend[key])}else{base[key]=extend[key]}}return base}function merge(clone,recursive,argv){var result=argv[0],size=argv.length;if(clone||typeOf(result)!=="object")result={};for(var index=0;index<size;++index){var item=argv[index],type=typeOf(item);if(type!=="object")continue;for(var key in item){if(key==="__proto__")continue;var sitem=clone?Public.clone(item[key]):item[key];if(recursive){result[key]=merge_recursive(result[key],sitem)}else{result[key]=sitem}}}return result}function typeOf(input){return{}.toString.call(input).slice(8,-1).toLowerCase()}if(isNode){module.exports=Public}else{window[publicName]=Public}})(typeof module==="object"&&module&&typeof module.exports==="object"&&module.exports)},{}],"justified-layout":[function(require,module,exports){/*!
 * Copyright 2016 Yahoo Inc.
 * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
 * @license
 */
"use strict";var merge=require("merge"),Row=require("./row");function createNewRow(layoutConfig,layoutData){var isBreakoutRow;if(layoutConfig.fullWidthBreakoutRowCadence!==false){if((layoutData._rows.length+1)%layoutConfig.fullWidthBreakoutRowCadence===0){isBreakoutRow=true}}return new Row({top:layoutData._containerHeight,left:layoutConfig.containerPadding.left,width:layoutConfig.containerWidth-layoutConfig.containerPadding.left-layoutConfig.containerPadding.right,spacing:layoutConfig.boxSpacing.horizontal,targetRowHeight:layoutConfig.targetRowHeight,targetRowHeightTolerance:layoutConfig.targetRowHeightTolerance,edgeCaseMinRowHeight:.5*layoutConfig.targetRowHeight,edgeCaseMaxRowHeight:2*layoutConfig.targetRowHeight,rightToLeft:false,isBreakoutRow:isBreakoutRow,widowLayoutStyle:layoutConfig.widowLayoutStyle})}function addRow(layoutConfig,layoutData,row){layoutData._rows.push(row);layoutData._layoutItems=layoutData._layoutItems.concat(row.getItems());layoutData._containerHeight+=row.height+layoutConfig.boxSpacing.vertical;return row.items}function computeLayout(layoutConfig,layoutData,itemLayoutData){var laidOutItems=[],itemAdded,currentRow,nextToLastRowHeight;if(layoutConfig.forceAspectRatio){itemLayoutData.forEach(function(itemData){itemData.forcedAspectRatio=true;itemData.aspectRatio=layoutConfig.forceAspectRatio})}itemLayoutData.some(function(itemData,i){if(isNaN(itemData.aspectRatio)){throw new Error("Item "+i+" has an invalid aspect ratio")}if(!currentRow){currentRow=createNewRow(layoutConfig,layoutData)}itemAdded=currentRow.addItem(itemData);if(currentRow.isLayoutComplete()){laidOutItems=laidOutItems.concat(addRow(layoutConfig,layoutData,currentRow));if(layoutData._rows.length>=layoutConfig.maxNumRows){currentRow=null;return true}currentRow=createNewRow(layoutConfig,layoutData);if(!itemAdded){itemAdded=currentRow.addItem(itemData);if(currentRow.isLayoutComplete()){laidOutItems=laidOutItems.concat(addRow(layoutConfig,layoutData,currentRow));if(layoutData._rows.length>=layoutConfig.maxNumRows){currentRow=null;return true}currentRow=createNewRow(layoutConfig,layoutData)}}}});if(currentRow&&currentRow.getItems().length&&layoutConfig.showWidows){if(layoutData._rows.length){if(layoutData._rows[layoutData._rows.length-1].isBreakoutRow){nextToLastRowHeight=layoutData._rows[layoutData._rows.length-1].targetRowHeight}else{nextToLastRowHeight=layoutData._rows[layoutData._rows.length-1].height}currentRow.forceComplete(false,nextToLastRowHeight)}else{currentRow.forceComplete(false)}laidOutItems=laidOutItems.concat(addRow(layoutConfig,layoutData,currentRow));layoutConfig._widowCount=currentRow.getItems().length}layoutData._containerHeight=layoutData._containerHeight-layoutConfig.boxSpacing.vertical;layoutData._containerHeight=layoutData._containerHeight+layoutConfig.containerPadding.bottom;return{containerHeight:layoutData._containerHeight,widowCount:layoutConfig._widowCount,boxes:layoutData._layoutItems}}module.exports=function(input,config){var layoutConfig={};var layoutData={};var defaults={containerWidth:1060,containerPadding:10,boxSpacing:10,targetRowHeight:320,targetRowHeightTolerance:.25,maxNumRows:Number.POSITIVE_INFINITY,forceAspectRatio:false,showWidows:true,fullWidthBreakoutRowCadence:false,widowLayoutStyle:"left"};var containerPadding={};var boxSpacing={};config=config||{};layoutConfig=merge(defaults,config);containerPadding.top=!isNaN(parseFloat(layoutConfig.containerPadding.top))?layoutConfig.containerPadding.top:layoutConfig.containerPadding;containerPadding.right=!isNaN(parseFloat(layoutConfig.containerPadding.right))?layoutConfig.containerPadding.right:layoutConfig.containerPadding;containerPadding.bottom=!isNaN(parseFloat(layoutConfig.containerPadding.bottom))?layoutConfig.containerPadding.bottom:layoutConfig.containerPadding;containerPadding.left=!isNaN(parseFloat(layoutConfig.containerPadding.left))?layoutConfig.containerPadding.left:layoutConfig.containerPadding;boxSpacing.horizontal=!isNaN(parseFloat(layoutConfig.boxSpacing.horizontal))?layoutConfig.boxSpacing.horizontal:layoutConfig.boxSpacing;boxSpacing.vertical=!isNaN(parseFloat(layoutConfig.boxSpacing.vertical))?layoutConfig.boxSpacing.vertical:layoutConfig.boxSpacing;layoutConfig.containerPadding=containerPadding;layoutConfig.boxSpacing=boxSpacing;layoutData._layoutItems=[];layoutData._awakeItems=[];layoutData._inViewportItems=[];layoutData._leadingOrphans=[];layoutData._trailingOrphans=[];layoutData._containerHeight=layoutConfig.containerPadding.top;layoutData._rows=[];layoutData._orphans=[];layoutConfig._widowCount=0;return computeLayout(layoutConfig,layoutData,input.map(function(item){if(item.width&&item.height){return{aspectRatio:item.width/item.height}}else{return{aspectRatio:item}}}))}},{"./row":1,merge:2}]},{},[]);
/* @preserve
 * Leaflet 1.8.0, a JS library for interactive maps. https://leafletjs.com
 * (c) 2010-2022 Vladimir Agafonkin, (c) 2010-2011 CloudMade
 */
!function(t,i){"object"==typeof exports&&"undefined"!=typeof module?i(exports):"function"==typeof define&&define.amd?define(["exports"],i):i((t="undefined"!=typeof globalThis?globalThis:t||self).leaflet={})}(this,function(t){"use strict";function l(t){for(var i,e,n=1,o=arguments.length;n<o;n++)for(i in e=arguments[n])t[i]=e[i];return t}var R=Object.create||function(t){return N.prototype=t,new N};function N(){}function a(t,i){var e=Array.prototype.slice;if(t.bind)return t.bind.apply(t,e.call(arguments,1));var n=e.call(arguments,2);return function(){return t.apply(i,n.length?n.concat(e.call(arguments)):arguments)}}var D=0;function h(t){return"_leaflet_id"in t||(t._leaflet_id=++D),t._leaflet_id}function j(t,i,e){var n,o,s=function(){n=!1,o&&(r.apply(e,o),o=!1)},r=function(){n?o=arguments:(t.apply(e,arguments),setTimeout(s,i),n=!0)};return r}function H(t,i,e){var n=i[1],i=i[0],o=n-i;return t===n&&e?t:((t-i)%o+o)%o+i}function u(){return!1}function e(t,i){if(!1===i)return t;i=Math.pow(10,void 0===i?6:i);return Math.round(t*i)/i}function W(t){return t.trim?t.trim():t.replace(/^\s+|\s+$/g,"")}function F(t){return W(t).split(/\s+/)}function c(t,i){for(var e in Object.prototype.hasOwnProperty.call(t,"options")||(t.options=t.options?R(t.options):{}),i)t.options[e]=i[e];return t.options}function U(t,i,e){var n,o=[];for(n in t)o.push(encodeURIComponent(e?n.toUpperCase():n)+"="+encodeURIComponent(t[n]));return(i&&-1!==i.indexOf("?")?"&":"?")+o.join("&")}var V=/\{ *([\w_ -]+) *\}/g;function q(t,e){return t.replace(V,function(t,i){i=e[i];if(void 0===i)throw new Error("No value provided for variable "+t);return i="function"==typeof i?i(e):i})}var d=Array.isArray||function(t){return"[object Array]"===Object.prototype.toString.call(t)};function G(t,i){for(var e=0;e<t.length;e++)if(t[e]===i)return e;return-1}var K="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";function Y(t){return window["webkit"+t]||window["moz"+t]||window["ms"+t]}var X=0;function J(t){var i=+new Date,e=Math.max(0,16-(i-X));return X=i+e,window.setTimeout(t,e)}var $=window.requestAnimationFrame||Y("RequestAnimationFrame")||J,Q=window.cancelAnimationFrame||Y("CancelAnimationFrame")||Y("CancelRequestAnimationFrame")||function(t){window.clearTimeout(t)};function x(t,i,e){if(!e||$!==J)return $.call(window,a(t,i));t.call(i)}function r(t){t&&Q.call(window,t)}var tt={__proto__:null,extend:l,create:R,bind:a,get lastId(){return D},stamp:h,throttle:j,wrapNum:H,falseFn:u,formatNum:e,trim:W,splitWords:F,setOptions:c,getParamString:U,template:q,isArray:d,indexOf:G,emptyImageUrl:K,requestFn:$,cancelFn:Q,requestAnimFrame:x,cancelAnimFrame:r};function it(){}it.extend=function(t){function i(){c(this),this.initialize&&this.initialize.apply(this,arguments),this.callInitHooks()}var e,n=i.__super__=this.prototype,o=R(n);for(e in(o.constructor=i).prototype=o,this)Object.prototype.hasOwnProperty.call(this,e)&&"prototype"!==e&&"__super__"!==e&&(i[e]=this[e]);if(t.statics&&l(i,t.statics),t.includes){var s=t.includes;if("undefined"!=typeof L&&L&&L.Mixin){s=d(s)?s:[s];for(var r=0;r<s.length;r++)s[r]===L.Mixin.Events&&console.warn("Deprecated include of L.Mixin.Events: this property will be removed in future releases, please inherit from L.Evented instead.",(new Error).stack)}l.apply(null,[o].concat(t.includes))}return l(o,t),delete o.statics,delete o.includes,o.options&&(o.options=n.options?R(n.options):{},l(o.options,t.options)),o._initHooks=[],o.callInitHooks=function(){if(!this._initHooksCalled){n.callInitHooks&&n.callInitHooks.call(this),this._initHooksCalled=!0;for(var t=0,i=o._initHooks.length;t<i;t++)o._initHooks[t].call(this)}},i},it.include=function(t){var i=this.prototype.options;return l(this.prototype,t),t.options&&(this.prototype.options=i,this.mergeOptions(t.options)),this},it.mergeOptions=function(t){return l(this.prototype.options,t),this},it.addInitHook=function(t){var i=Array.prototype.slice.call(arguments,1),e="function"==typeof t?t:function(){this[t].apply(this,i)};return this.prototype._initHooks=this.prototype._initHooks||[],this.prototype._initHooks.push(e),this};var i={on:function(t,i,e){if("object"==typeof t)for(var n in t)this._on(n,t[n],i);else for(var o=0,s=(t=F(t)).length;o<s;o++)this._on(t[o],i,e);return this},off:function(t,i,e){if(arguments.length)if("object"==typeof t)for(var n in t)this._off(n,t[n],i);else{t=F(t);for(var o=1===arguments.length,s=0,r=t.length;s<r;s++)o?this._off(t[s]):this._off(t[s],i,e)}else delete this._events;return this},_on:function(t,i,e){if("function"!=typeof i)console.warn("wrong listener type: "+typeof i);else{this._events=this._events||{};for(var n=this._events[t],t=(n||(this._events[t]=n=[]),{fn:i,ctx:e=e===this?void 0:e}),o=n,s=0,r=o.length;s<r;s++)if(o[s].fn===i&&o[s].ctx===e)return;o.push(t)}},_off:function(t,i,e){var n,o,s;if(this._events&&(n=this._events[t]))if(1===arguments.length){if(this._firingCount)for(o=0,s=n.length;o<s;o++)n[o].fn=u;delete this._events[t]}else if(e===this&&(e=void 0),"function"!=typeof i)console.warn("wrong listener type: "+typeof i);else{for(o=0,s=n.length;o<s;o++){var r=n[o];if(r.ctx===e&&r.fn===i)return this._firingCount&&(r.fn=u,this._events[t]=n=n.slice()),void n.splice(o,1)}console.warn("listener not found")}},fire:function(t,i,e){if(!this.listens(t,e))return this;var n=l({},i,{type:t,target:this,sourceTarget:i&&i.sourceTarget||this});if(this._events){var o=this._events[t];if(o){this._firingCount=this._firingCount+1||1;for(var s=0,r=o.length;s<r;s++){var a=o[s];a.fn.call(a.ctx||this,n)}this._firingCount--}}return e&&this._propagateEvent(n),this},listens:function(t,i){"string"!=typeof t&&console.warn('"string" type argument expected');var e=this._events&&this._events[t];if(e&&e.length)return!0;if(i)for(var n in this._eventParents)if(this._eventParents[n].listens(t,i))return!0;return!1},once:function(t,i,e){if("object"==typeof t){for(var n in t)this.once(n,t[n],i);return this}var o=a(function(){this.off(t,i,e).off(t,o,e)},this);return this.on(t,i,e).on(t,o,e)},addEventParent:function(t){return this._eventParents=this._eventParents||{},this._eventParents[h(t)]=t,this},removeEventParent:function(t){return this._eventParents&&delete this._eventParents[h(t)],this},_propagateEvent:function(t){for(var i in this._eventParents)this._eventParents[i].fire(t.type,l({layer:t.target,propagatedFrom:t.target},t),!0)}},et=(i.addEventListener=i.on,i.removeEventListener=i.clearAllEventListeners=i.off,i.addOneTimeEventListener=i.once,i.fireEvent=i.fire,i.hasEventListeners=i.listens,it.extend(i));function p(t,i,e){this.x=e?Math.round(t):t,this.y=e?Math.round(i):i}var nt=Math.trunc||function(t){return 0<t?Math.floor(t):Math.ceil(t)};function _(t,i,e){return t instanceof p?t:d(t)?new p(t[0],t[1]):null==t?t:"object"==typeof t&&"x"in t&&"y"in t?new p(t.x,t.y):new p(t,i,e)}function m(t,i){if(t)for(var e=i?[t,i]:t,n=0,o=e.length;n<o;n++)this.extend(e[n])}function f(t,i){return!t||t instanceof m?t:new m(t,i)}function s(t,i){if(t)for(var e=i?[t,i]:t,n=0,o=e.length;n<o;n++)this.extend(e[n])}function g(t,i){return t instanceof s?t:new s(t,i)}function v(t,i,e){if(isNaN(t)||isNaN(i))throw new Error("Invalid LatLng object: ("+t+", "+i+")");this.lat=+t,this.lng=+i,void 0!==e&&(this.alt=+e)}function w(t,i,e){return t instanceof v?t:d(t)&&"object"!=typeof t[0]?3===t.length?new v(t[0],t[1],t[2]):2===t.length?new v(t[0],t[1]):null:null==t?t:"object"==typeof t&&"lat"in t?new v(t.lat,"lng"in t?t.lng:t.lon,t.alt):void 0===i?null:new v(t,i,e)}p.prototype={clone:function(){return new p(this.x,this.y)},add:function(t){return this.clone()._add(_(t))},_add:function(t){return this.x+=t.x,this.y+=t.y,this},subtract:function(t){return this.clone()._subtract(_(t))},_subtract:function(t){return this.x-=t.x,this.y-=t.y,this},divideBy:function(t){return this.clone()._divideBy(t)},_divideBy:function(t){return this.x/=t,this.y/=t,this},multiplyBy:function(t){return this.clone()._multiplyBy(t)},_multiplyBy:function(t){return this.x*=t,this.y*=t,this},scaleBy:function(t){return new p(this.x*t.x,this.y*t.y)},unscaleBy:function(t){return new p(this.x/t.x,this.y/t.y)},round:function(){return this.clone()._round()},_round:function(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this},floor:function(){return this.clone()._floor()},_floor:function(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this},ceil:function(){return this.clone()._ceil()},_ceil:function(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this},trunc:function(){return this.clone()._trunc()},_trunc:function(){return this.x=nt(this.x),this.y=nt(this.y),this},distanceTo:function(t){var i=(t=_(t)).x-this.x,t=t.y-this.y;return Math.sqrt(i*i+t*t)},equals:function(t){return(t=_(t)).x===this.x&&t.y===this.y},contains:function(t){return t=_(t),Math.abs(t.x)<=Math.abs(this.x)&&Math.abs(t.y)<=Math.abs(this.y)},toString:function(){return"Point("+e(this.x)+", "+e(this.y)+")"}},m.prototype={extend:function(t){return t=_(t),this.min||this.max?(this.min.x=Math.min(t.x,this.min.x),this.max.x=Math.max(t.x,this.max.x),this.min.y=Math.min(t.y,this.min.y),this.max.y=Math.max(t.y,this.max.y)):(this.min=t.clone(),this.max=t.clone()),this},getCenter:function(t){return new p((this.min.x+this.max.x)/2,(this.min.y+this.max.y)/2,t)},getBottomLeft:function(){return new p(this.min.x,this.max.y)},getTopRight:function(){return new p(this.max.x,this.min.y)},getTopLeft:function(){return this.min},getBottomRight:function(){return this.max},getSize:function(){return this.max.subtract(this.min)},contains:function(t){var i,e;return(t=("number"==typeof t[0]||t instanceof p?_:f)(t))instanceof m?(i=t.min,e=t.max):i=e=t,i.x>=this.min.x&&e.x<=this.max.x&&i.y>=this.min.y&&e.y<=this.max.y},intersects:function(t){t=f(t);var i=this.min,e=this.max,n=t.min,t=t.max,o=t.x>=i.x&&n.x<=e.x,t=t.y>=i.y&&n.y<=e.y;return o&&t},overlaps:function(t){t=f(t);var i=this.min,e=this.max,n=t.min,t=t.max,o=t.x>i.x&&n.x<e.x,t=t.y>i.y&&n.y<e.y;return o&&t},isValid:function(){return!(!this.min||!this.max)}},s.prototype={extend:function(t){var i,e,n=this._southWest,o=this._northEast;if(t instanceof v)e=i=t;else{if(!(t instanceof s))return t?this.extend(w(t)||g(t)):this;if(i=t._southWest,e=t._northEast,!i||!e)return this}return n||o?(n.lat=Math.min(i.lat,n.lat),n.lng=Math.min(i.lng,n.lng),o.lat=Math.max(e.lat,o.lat),o.lng=Math.max(e.lng,o.lng)):(this._southWest=new v(i.lat,i.lng),this._northEast=new v(e.lat,e.lng)),this},pad:function(t){var i=this._southWest,e=this._northEast,n=Math.abs(i.lat-e.lat)*t,t=Math.abs(i.lng-e.lng)*t;return new s(new v(i.lat-n,i.lng-t),new v(e.lat+n,e.lng+t))},getCenter:function(){return new v((this._southWest.lat+this._northEast.lat)/2,(this._southWest.lng+this._northEast.lng)/2)},getSouthWest:function(){return this._southWest},getNorthEast:function(){return this._northEast},getNorthWest:function(){return new v(this.getNorth(),this.getWest())},getSouthEast:function(){return new v(this.getSouth(),this.getEast())},getWest:function(){return this._southWest.lng},getSouth:function(){return this._southWest.lat},getEast:function(){return this._northEast.lng},getNorth:function(){return this._northEast.lat},contains:function(t){t=("number"==typeof t[0]||t instanceof v||"lat"in t?w:g)(t);var i,e,n=this._southWest,o=this._northEast;return t instanceof s?(i=t.getSouthWest(),e=t.getNorthEast()):i=e=t,i.lat>=n.lat&&e.lat<=o.lat&&i.lng>=n.lng&&e.lng<=o.lng},intersects:function(t){t=g(t);var i=this._southWest,e=this._northEast,n=t.getSouthWest(),t=t.getNorthEast(),o=t.lat>=i.lat&&n.lat<=e.lat,t=t.lng>=i.lng&&n.lng<=e.lng;return o&&t},overlaps:function(t){t=g(t);var i=this._southWest,e=this._northEast,n=t.getSouthWest(),t=t.getNorthEast(),o=t.lat>i.lat&&n.lat<e.lat,t=t.lng>i.lng&&n.lng<e.lng;return o&&t},toBBoxString:function(){return[this.getWest(),this.getSouth(),this.getEast(),this.getNorth()].join(",")},equals:function(t,i){return!!t&&(t=g(t),this._southWest.equals(t.getSouthWest(),i)&&this._northEast.equals(t.getNorthEast(),i))},isValid:function(){return!(!this._southWest||!this._northEast)}};var ot={latLngToPoint:function(t,i){t=this.projection.project(t),i=this.scale(i);return this.transformation._transform(t,i)},pointToLatLng:function(t,i){i=this.scale(i),t=this.transformation.untransform(t,i);return this.projection.unproject(t)},project:function(t){return this.projection.project(t)},unproject:function(t){return this.projection.unproject(t)},scale:function(t){return 256*Math.pow(2,t)},zoom:function(t){return Math.log(t/256)/Math.LN2},getProjectedBounds:function(t){if(this.infinite)return null;var i=this.projection.bounds,t=this.scale(t);return new m(this.transformation.transform(i.min,t),this.transformation.transform(i.max,t))},infinite:!(v.prototype={equals:function(t,i){return!!t&&(t=w(t),Math.max(Math.abs(this.lat-t.lat),Math.abs(this.lng-t.lng))<=(void 0===i?1e-9:i))},toString:function(t){return"LatLng("+e(this.lat,t)+", "+e(this.lng,t)+")"},distanceTo:function(t){return st.distance(this,w(t))},wrap:function(){return st.wrapLatLng(this)},toBounds:function(t){var t=180*t/40075017,i=t/Math.cos(Math.PI/180*this.lat);return g([this.lat-t,this.lng-i],[this.lat+t,this.lng+i])},clone:function(){return new v(this.lat,this.lng,this.alt)}}),wrapLatLng:function(t){var i=this.wrapLng?H(t.lng,this.wrapLng,!0):t.lng;return new v(this.wrapLat?H(t.lat,this.wrapLat,!0):t.lat,i,t.alt)},wrapLatLngBounds:function(t){var i=t.getCenter(),e=this.wrapLatLng(i),n=i.lat-e.lat,i=i.lng-e.lng;if(0==n&&0==i)return t;e=t.getSouthWest(),t=t.getNorthEast();return new s(new v(e.lat-n,e.lng-i),new v(t.lat-n,t.lng-i))}},st=l({},ot,{wrapLng:[-180,180],R:6371e3,distance:function(t,i){var e=Math.PI/180,n=t.lat*e,o=i.lat*e,s=Math.sin((i.lat-t.lat)*e/2),i=Math.sin((i.lng-t.lng)*e/2),t=s*s+Math.cos(n)*Math.cos(o)*i*i,e=2*Math.atan2(Math.sqrt(t),Math.sqrt(1-t));return this.R*e}}),rt=6378137,rt={R:rt,MAX_LATITUDE:85.0511287798,project:function(t){var i=Math.PI/180,e=this.MAX_LATITUDE,e=Math.max(Math.min(e,t.lat),-e),e=Math.sin(e*i);return new p(this.R*t.lng*i,this.R*Math.log((1+e)/(1-e))/2)},unproject:function(t){var i=180/Math.PI;return new v((2*Math.atan(Math.exp(t.y/this.R))-Math.PI/2)*i,t.x*i/this.R)},bounds:new m([-(rt=rt*Math.PI),-rt],[rt,rt])};function at(t,i,e,n){if(d(t))return this._a=t[0],this._b=t[1],this._c=t[2],void(this._d=t[3]);this._a=t,this._b=i,this._c=e,this._d=n}function ht(t,i,e,n){return new at(t,i,e,n)}at.prototype={transform:function(t,i){return this._transform(t.clone(),i)},_transform:function(t,i){return t.x=(i=i||1)*(this._a*t.x+this._b),t.y=i*(this._c*t.y+this._d),t},untransform:function(t,i){return new p((t.x/(i=i||1)-this._b)/this._a,(t.y/i-this._d)/this._c)}};var lt=l({},st,{code:"EPSG:3857",projection:rt,transformation:ht(lt=.5/(Math.PI*rt.R),.5,-lt,.5)}),ut=l({},lt,{code:"EPSG:900913"});function ct(t){return document.createElementNS("http://www.w3.org/2000/svg",t)}function dt(t,i){for(var e,n,o,s,r="",a=0,h=t.length;a<h;a++){for(e=0,n=(o=t[a]).length;e<n;e++)r+=(e?"L":"M")+(s=o[e]).x+" "+s.y;r+=i?P.svg?"z":"x":""}return r||"M0 0"}var _t=document.documentElement.style,pt="ActiveXObject"in window,mt=pt&&!document.addEventListener,n="msLaunchUri"in navigator&&!("documentMode"in document),ft=y("webkit"),gt=y("android"),vt=y("android 2")||y("android 3"),yt=parseInt(/WebKit\/([0-9]+)|$/.exec(navigator.userAgent)[1],10),yt=gt&&y("Google")&&yt<537&&!("AudioNode"in window),xt=!!window.opera,wt=!n&&y("chrome"),Pt=y("gecko")&&!ft&&!xt&&!pt,bt=!wt&&y("safari"),Lt=y("phantom"),o="OTransition"in _t,Tt=0===navigator.platform.indexOf("Win"),zt=pt&&"transition"in _t,Mt="WebKitCSSMatrix"in window&&"m11"in new window.WebKitCSSMatrix&&!vt,_t="MozPerspective"in _t,Ct=!window.L_DISABLE_3D&&(zt||Mt||_t)&&!o&&!Lt,Zt="undefined"!=typeof orientation||y("mobile"),St=Zt&&ft,kt=Zt&&Mt,Et=!window.PointerEvent&&window.MSPointerEvent,Bt=!(!window.PointerEvent&&!Et),At="ontouchstart"in window||!!window.TouchEvent,It=!window.L_NO_TOUCH&&(At||Bt),Ot=Zt&&xt,Rt=Zt&&Pt,Nt=1<(window.devicePixelRatio||window.screen.deviceXDPI/window.screen.logicalXDPI),Dt=function(){var t=!1;try{var i=Object.defineProperty({},"passive",{get:function(){t=!0}});window.addEventListener("testPassiveEventSupport",u,i),window.removeEventListener("testPassiveEventSupport",u,i)}catch(t){}return t}(),jt=!!document.createElement("canvas").getContext,Ht=!(!document.createElementNS||!ct("svg").createSVGRect),Wt=!!Ht&&((Wt=document.createElement("div")).innerHTML="<svg/>","http://www.w3.org/2000/svg"===(Wt.firstChild&&Wt.firstChild.namespaceURI));function y(t){return 0<=navigator.userAgent.toLowerCase().indexOf(t)}var P={ie:pt,ielt9:mt,edge:n,webkit:ft,android:gt,android23:vt,androidStock:yt,opera:xt,chrome:wt,gecko:Pt,safari:bt,phantom:Lt,opera12:o,win:Tt,ie3d:zt,webkit3d:Mt,gecko3d:_t,any3d:Ct,mobile:Zt,mobileWebkit:St,mobileWebkit3d:kt,msPointer:Et,pointer:Bt,touch:It,touchNative:At,mobileOpera:Ot,mobileGecko:Rt,retina:Nt,passiveEvents:Dt,canvas:jt,svg:Ht,vml:!Ht&&function(){try{var t=document.createElement("div"),i=(t.innerHTML='<v:shape adj="1"/>',t.firstChild);return i.style.behavior="url(#default#VML)",i&&"object"==typeof i.adj}catch(t){return!1}}(),inlineSvg:Wt},Ft=P.msPointer?"MSPointerDown":"pointerdown",Ut=P.msPointer?"MSPointerMove":"pointermove",Vt=P.msPointer?"MSPointerUp":"pointerup",qt=P.msPointer?"MSPointerCancel":"pointercancel",Gt={touchstart:Ft,touchmove:Ut,touchend:Vt,touchcancel:qt},Kt={touchstart:function(t,i){i.MSPOINTER_TYPE_TOUCH&&i.pointerType===i.MSPOINTER_TYPE_TOUCH&&B(i);ii(t,i)},touchmove:ii,touchend:ii,touchcancel:ii},Yt={},Xt=!1;function Jt(t,i,e){return"touchstart"!==i||Xt||(document.addEventListener(Ft,$t,!0),document.addEventListener(Ut,Qt,!0),document.addEventListener(Vt,ti,!0),document.addEventListener(qt,ti,!0),Xt=!0),Kt[i]?(e=Kt[i].bind(this,e),t.addEventListener(Gt[i],e,!1),e):(console.warn("wrong event specified:",i),L.Util.falseFn)}function $t(t){Yt[t.pointerId]=t}function Qt(t){Yt[t.pointerId]&&(Yt[t.pointerId]=t)}function ti(t){delete Yt[t.pointerId]}function ii(t,i){if(i.pointerType!==(i.MSPOINTER_TYPE_MOUSE||"mouse")){for(var e in i.touches=[],Yt)i.touches.push(Yt[e]);i.changedTouches=[i],t(i)}}var ei=200;function ni(t,e){t.addEventListener("dblclick",e);var n,o=0;function i(t){var i;1!==t.detail?n=t.detail:"mouse"===t.pointerType||t.sourceCapabilities&&!t.sourceCapabilities.firesTouchEvents||((i=Date.now())-o<=ei?2===++n&&e(function(t){var i,e,n={};for(e in t)i=t[e],n[e]=i&&i.bind?i.bind(t):i;return(t=n).type="dblclick",n.detail=2,n.isTrusted=!1,n._simulated=!0,n}(t)):n=1,o=i)}return t.addEventListener("click",i),{dblclick:e,simDblclick:i}}var oi,si,ri,ai,hi,li,ui=wi(["transform","webkitTransform","OTransform","MozTransform","msTransform"]),ci=wi(["webkitTransition","transition","OTransition","MozTransition","msTransition"]),di="webkitTransition"===ci||"OTransition"===ci?ci+"End":"transitionend";function _i(t){return"string"==typeof t?document.getElementById(t):t}function pi(t,i){var e=t.style[i]||t.currentStyle&&t.currentStyle[i];return"auto"===(e=e&&"auto"!==e||!document.defaultView?e:(t=document.defaultView.getComputedStyle(t,null))?t[i]:null)?null:e}function b(t,i,e){t=document.createElement(t);return t.className=i||"",e&&e.appendChild(t),t}function T(t){var i=t.parentNode;i&&i.removeChild(t)}function mi(t){for(;t.firstChild;)t.removeChild(t.firstChild)}function fi(t){var i=t.parentNode;i&&i.lastChild!==t&&i.appendChild(t)}function gi(t){var i=t.parentNode;i&&i.firstChild!==t&&i.insertBefore(t,i.firstChild)}function vi(t,i){if(void 0!==t.classList)return t.classList.contains(i);t=xi(t);return 0<t.length&&new RegExp("(^|\\s)"+i+"(\\s|$)").test(t)}function z(t,i){var e;if(void 0!==t.classList)for(var n=F(i),o=0,s=n.length;o<s;o++)t.classList.add(n[o]);else vi(t,i)||yi(t,((e=xi(t))?e+" ":"")+i)}function M(t,i){void 0!==t.classList?t.classList.remove(i):yi(t,W((" "+xi(t)+" ").replace(" "+i+" "," ")))}function yi(t,i){void 0===t.className.baseVal?t.className=i:t.className.baseVal=i}function xi(t){return void 0===(t=t.correspondingElement?t.correspondingElement:t).className.baseVal?t.className:t.className.baseVal}function C(t,i){if("opacity"in t.style)t.style.opacity=i;else if("filter"in t.style){var e=!1,n="DXImageTransform.Microsoft.Alpha";try{e=t.filters.item(n)}catch(t){if(1===i)return}i=Math.round(100*i),e?(e.Enabled=100!==i,e.Opacity=i):t.style.filter+=" progid:"+n+"(opacity="+i+")"}}function wi(t){for(var i=document.documentElement.style,e=0;e<t.length;e++)if(t[e]in i)return t[e];return!1}function Pi(t,i,e){i=i||new p(0,0);t.style[ui]=(P.ie3d?"translate("+i.x+"px,"+i.y+"px)":"translate3d("+i.x+"px,"+i.y+"px,0)")+(e?" scale("+e+")":"")}function Z(t,i){t._leaflet_pos=i,P.any3d?Pi(t,i):(t.style.left=i.x+"px",t.style.top=i.y+"px")}function bi(t){return t._leaflet_pos||new p(0,0)}function Li(){S(window,"dragstart",B)}function Ti(){E(window,"dragstart",B)}function zi(t){for(;-1===t.tabIndex;)t=t.parentNode;t.style&&(Mi(),li=(hi=t).style.outline,t.style.outline="none",S(window,"keydown",Mi))}function Mi(){hi&&(hi.style.outline=li,li=hi=void 0,E(window,"keydown",Mi))}function Ci(t){for(;!((t=t.parentNode).offsetWidth&&t.offsetHeight||t===document.body););return t}function Zi(t){var i=t.getBoundingClientRect();return{x:i.width/t.offsetWidth||1,y:i.height/t.offsetHeight||1,boundingClientRect:i}}ai="onselectstart"in document?(ri=function(){S(window,"selectstart",B)},function(){E(window,"selectstart",B)}):(si=wi(["userSelect","WebkitUserSelect","OUserSelect","MozUserSelect","msUserSelect"]),ri=function(){var t;si&&(t=document.documentElement.style,oi=t[si],t[si]="none")},function(){si&&(document.documentElement.style[si]=oi,oi=void 0)});pt={__proto__:null,TRANSFORM:ui,TRANSITION:ci,TRANSITION_END:di,get:_i,getStyle:pi,create:b,remove:T,empty:mi,toFront:fi,toBack:gi,hasClass:vi,addClass:z,removeClass:M,setClass:yi,getClass:xi,setOpacity:C,testProp:wi,setTransform:Pi,setPosition:Z,getPosition:bi,get disableTextSelection(){return ri},get enableTextSelection(){return ai},disableImageDrag:Li,enableImageDrag:Ti,preventOutline:zi,restoreOutline:Mi,getSizedParentNode:Ci,getScale:Zi};function S(t,i,e,n){if(i&&"object"==typeof i)for(var o in i)Ei(t,o,i[o],e);else for(var s=0,r=(i=F(i)).length;s<r;s++)Ei(t,i[s],e,n);return this}var k="_leaflet_events";function E(t,i,e,n){if(1===arguments.length)Si(t),delete t[k];else if(i&&"object"==typeof i)for(var o in i)Bi(t,o,i[o],e);else if(i=F(i),2===arguments.length)Si(t,function(t){return-1!==G(i,t)});else for(var s=0,r=i.length;s<r;s++)Bi(t,i[s],e,n);return this}function Si(t,i){for(var e in t[k]){var n=e.split(/\d/)[0];i&&!i(n)||Bi(t,n,null,null,e)}}var ki={mouseenter:"mouseover",mouseleave:"mouseout",wheel:!("onwheel"in window)&&"mousewheel"};function Ei(i,t,e,n){var o,s,r=t+h(e)+(n?"_"+h(n):"");i[k]&&i[k][r]||(s=o=function(t){return e.call(n||i,t||window.event)},!P.touchNative&&P.pointer&&0===t.indexOf("touch")?o=Jt(i,t,o):P.touch&&"dblclick"===t?o=ni(i,o):"addEventListener"in i?"touchstart"===t||"touchmove"===t||"wheel"===t||"mousewheel"===t?i.addEventListener(ki[t]||t,o,!!P.passiveEvents&&{passive:!1}):"mouseenter"===t||"mouseleave"===t?i.addEventListener(ki[t],o=function(t){t=t||window.event,Hi(i,t)&&s(t)},!1):i.addEventListener(t,s,!1):i.attachEvent("on"+t,o),i[k]=i[k]||{},i[k][r]=o)}function Bi(t,i,e,n,o){o=o||i+h(e)+(n?"_"+h(n):"");var s,r,e=t[k]&&t[k][o];e&&(!P.touchNative&&P.pointer&&0===i.indexOf("touch")?(n=t,r=e,Gt[s=i]?n.removeEventListener(Gt[s],r,!1):console.warn("wrong event specified:",s)):P.touch&&"dblclick"===i?(n=e,(r=t).removeEventListener("dblclick",n.dblclick),r.removeEventListener("click",n.simDblclick)):"removeEventListener"in t?t.removeEventListener(ki[i]||i,e,!1):t.detachEvent("on"+i,e),t[k][o]=null)}function Ai(t){return t.stopPropagation?t.stopPropagation():t.originalEvent?t.originalEvent._stopped=!0:t.cancelBubble=!0,this}function Ii(t){return Ei(t,"wheel",Ai),this}function Oi(t){return S(t,"mousedown touchstart dblclick contextmenu",Ai),t._leaflet_disable_click=!0,this}function B(t){return t.preventDefault?t.preventDefault():t.returnValue=!1,this}function Ri(t){return B(t),Ai(t),this}function Ni(t,i){if(!i)return new p(t.clientX,t.clientY);var e=Zi(i),n=e.boundingClientRect;return new p((t.clientX-n.left)/e.x-i.clientLeft,(t.clientY-n.top)/e.y-i.clientTop)}var Di=P.win&&P.chrome?2*window.devicePixelRatio:P.gecko?window.devicePixelRatio:1;function ji(t){return P.edge?t.wheelDeltaY/2:t.deltaY&&0===t.deltaMode?-t.deltaY/Di:t.deltaY&&1===t.deltaMode?20*-t.deltaY:t.deltaY&&2===t.deltaMode?60*-t.deltaY:t.deltaX||t.deltaZ?0:t.wheelDelta?(t.wheelDeltaY||t.wheelDelta)/2:t.detail&&Math.abs(t.detail)<32765?20*-t.detail:t.detail?t.detail/-32765*60:0}function Hi(t,i){var e=i.relatedTarget;if(!e)return!0;try{for(;e&&e!==t;)e=e.parentNode}catch(t){return!1}return e!==t}var mt={__proto__:null,on:S,off:E,stopPropagation:Ai,disableScrollPropagation:Ii,disableClickPropagation:Oi,preventDefault:B,stop:Ri,getMousePosition:Ni,getWheelDelta:ji,isExternalTarget:Hi,addListener:S,removeListener:E},Wi=et.extend({run:function(t,i,e,n){this.stop(),this._el=t,this._inProgress=!0,this._duration=e||.25,this._easeOutPower=1/Math.max(n||.5,.2),this._startPos=bi(t),this._offset=i.subtract(this._startPos),this._startTime=+new Date,this.fire("start"),this._animate()},stop:function(){this._inProgress&&(this._step(!0),this._complete())},_animate:function(){this._animId=x(this._animate,this),this._step()},_step:function(t){var i=+new Date-this._startTime,e=1e3*this._duration;i<e?this._runFrame(this._easeOut(i/e),t):(this._runFrame(1),this._complete())},_runFrame:function(t,i){t=this._startPos.add(this._offset.multiplyBy(t));i&&t._round(),Z(this._el,t),this.fire("step")},_complete:function(){r(this._animId),this._inProgress=!1,this.fire("end")},_easeOut:function(t){return 1-Math.pow(1-t,this._easeOutPower)}}),A=et.extend({options:{crs:lt,center:void 0,zoom:void 0,minZoom:void 0,maxZoom:void 0,layers:[],maxBounds:void 0,renderer:void 0,zoomAnimation:!0,zoomAnimationThreshold:4,fadeAnimation:!0,markerZoomAnimation:!0,transform3DLimit:8388608,zoomSnap:1,zoomDelta:1,trackResize:!0},initialize:function(t,i){i=c(this,i),this._handlers=[],this._layers={},this._zoomBoundLayers={},this._sizeChanged=!0,this._initContainer(t),this._initLayout(),this._onResize=a(this._onResize,this),this._initEvents(),i.maxBounds&&this.setMaxBounds(i.maxBounds),void 0!==i.zoom&&(this._zoom=this._limitZoom(i.zoom)),i.center&&void 0!==i.zoom&&this.setView(w(i.center),i.zoom,{reset:!0}),this.callInitHooks(),this._zoomAnimated=ci&&P.any3d&&!P.mobileOpera&&this.options.zoomAnimation,this._zoomAnimated&&(this._createAnimProxy(),S(this._proxy,di,this._catchTransitionEnd,this)),this._addLayers(this.options.layers)},setView:function(t,i,e){if((i=void 0===i?this._zoom:this._limitZoom(i),t=this._limitCenter(w(t),i,this.options.maxBounds),e=e||{},this._stop(),this._loaded&&!e.reset&&!0!==e)&&(void 0!==e.animate&&(e.zoom=l({animate:e.animate},e.zoom),e.pan=l({animate:e.animate,duration:e.duration},e.pan)),this._zoom!==i?this._tryAnimatedZoom&&this._tryAnimatedZoom(t,i,e.zoom):this._tryAnimatedPan(t,e.pan)))return clearTimeout(this._sizeTimer),this;return this._resetView(t,i),this},setZoom:function(t,i){return this._loaded?this.setView(this.getCenter(),t,{zoom:i}):(this._zoom=t,this)},zoomIn:function(t,i){return t=t||(P.any3d?this.options.zoomDelta:1),this.setZoom(this._zoom+t,i)},zoomOut:function(t,i){return t=t||(P.any3d?this.options.zoomDelta:1),this.setZoom(this._zoom-t,i)},setZoomAround:function(t,i,e){var n=this.getZoomScale(i),o=this.getSize().divideBy(2),t=(t instanceof p?t:this.latLngToContainerPoint(t)).subtract(o).multiplyBy(1-1/n),n=this.containerPointToLatLng(o.add(t));return this.setView(n,i,{zoom:e})},_getBoundsCenterZoom:function(t,i){i=i||{},t=t.getBounds?t.getBounds():g(t);var e=_(i.paddingTopLeft||i.padding||[0,0]),n=_(i.paddingBottomRight||i.padding||[0,0]),o=this.getBoundsZoom(t,!1,e.add(n));if((o="number"==typeof i.maxZoom?Math.min(i.maxZoom,o):o)===1/0)return{center:t.getCenter(),zoom:o};i=n.subtract(e).divideBy(2),n=this.project(t.getSouthWest(),o),e=this.project(t.getNorthEast(),o);return{center:this.unproject(n.add(e).divideBy(2).add(i),o),zoom:o}},fitBounds:function(t,i){if(!(t=g(t)).isValid())throw new Error("Bounds are not valid.");t=this._getBoundsCenterZoom(t,i);return this.setView(t.center,t.zoom,i)},fitWorld:function(t){return this.fitBounds([[-90,-180],[90,180]],t)},panTo:function(t,i){return this.setView(t,this._zoom,{pan:i})},panBy:function(t,i){return i=i||{},(t=_(t).round()).x||t.y?(!0===i.animate||this.getSize().contains(t)?(this._panAnim||(this._panAnim=new Wi,this._panAnim.on({step:this._onPanTransitionStep,end:this._onPanTransitionEnd},this)),i.noMoveStart||this.fire("movestart"),!1!==i.animate?(z(this._mapPane,"leaflet-pan-anim"),e=this._getMapPanePos().subtract(t).round(),this._panAnim.run(this._mapPane,e,i.duration||.25,i.easeLinearity)):(this._rawPanBy(t),this.fire("move").fire("moveend"))):this._resetView(this.unproject(this.project(this.getCenter()).add(t)),this.getZoom()),this):this.fire("moveend");var e},flyTo:function(n,o,t){if(!1===(t=t||{}).animate||!P.any3d)return this.setView(n,o,t);this._stop();var s=this.project(this.getCenter()),r=this.project(n),i=this.getSize(),a=this._zoom,h=(n=w(n),o=void 0===o?a:o,Math.max(i.x,i.y)),e=h*this.getZoomScale(a,o),l=r.distanceTo(s)||1,u=1.42,c=u*u;function d(t){t=(e*e-h*h+(t?-1:1)*c*c*l*l)/(2*(t?e:h)*c*l),t=Math.sqrt(t*t+1)-t;return t<1e-9?-18:Math.log(t)}function _(t){return(Math.exp(t)-Math.exp(-t))/2}function p(t){return(Math.exp(t)+Math.exp(-t))/2}var m=d(0);function f(t){return h*(p(m)*(_(t=m+u*t)/p(t))-_(m))/c}var g=Date.now(),v=(d(1)-m)/u,y=t.duration?1e3*t.duration:1e3*v*.8;return this._moveStart(!0,t.noMoveStart),function t(){var i=(Date.now()-g)/y,e=(1-Math.pow(1-i,1.5))*v;i<=1?(this._flyToFrame=x(t,this),this._move(this.unproject(s.add(r.subtract(s).multiplyBy(f(e)/l)),a),this.getScaleZoom(h/(i=e,h*(p(m)/p(m+u*i))),a),{flyTo:!0})):this._move(n,o)._moveEnd(!0)}.call(this),this},flyToBounds:function(t,i){t=this._getBoundsCenterZoom(t,i);return this.flyTo(t.center,t.zoom,i)},setMaxBounds:function(t){return(t=g(t)).isValid()?(this.options.maxBounds&&this.off("moveend",this._panInsideMaxBounds),this.options.maxBounds=t,this._loaded&&this._panInsideMaxBounds(),this.on("moveend",this._panInsideMaxBounds)):(this.options.maxBounds=null,this.off("moveend",this._panInsideMaxBounds))},setMinZoom:function(t){var i=this.options.minZoom;return this.options.minZoom=t,this._loaded&&i!==t&&(this.fire("zoomlevelschange"),this.getZoom()<this.options.minZoom)?this.setZoom(t):this},setMaxZoom:function(t){var i=this.options.maxZoom;return this.options.maxZoom=t,this._loaded&&i!==t&&(this.fire("zoomlevelschange"),this.getZoom()>this.options.maxZoom)?this.setZoom(t):this},panInsideBounds:function(t,i){this._enforcingBounds=!0;var e=this.getCenter(),t=this._limitCenter(e,this._zoom,g(t));return e.equals(t)||this.panTo(t,i),this._enforcingBounds=!1,this},panInside:function(t,i){var e=_((i=i||{}).paddingTopLeft||i.padding||[0,0]),n=_(i.paddingBottomRight||i.padding||[0,0]),o=this.project(this.getCenter()),t=this.project(t),s=this.getPixelBounds(),e=f([s.min.add(e),s.max.subtract(n)]),s=e.getSize();return e.contains(t)||(this._enforcingBounds=!0,n=t.subtract(e.getCenter()),e=e.extend(t).getSize().subtract(s),o.x+=n.x<0?-e.x:e.x,o.y+=n.y<0?-e.y:e.y,this.panTo(this.unproject(o),i),this._enforcingBounds=!1),this},invalidateSize:function(t){if(!this._loaded)return this;t=l({animate:!1,pan:!0},!0===t?{animate:!0}:t);var i=this.getSize(),e=(this._sizeChanged=!0,this._lastCenter=null,this.getSize()),n=i.divideBy(2).round(),o=e.divideBy(2).round(),n=n.subtract(o);return n.x||n.y?(t.animate&&t.pan?this.panBy(n):(t.pan&&this._rawPanBy(n),this.fire("move"),t.debounceMoveend?(clearTimeout(this._sizeTimer),this._sizeTimer=setTimeout(a(this.fire,this,"moveend"),200)):this.fire("moveend")),this.fire("resize",{oldSize:i,newSize:e})):this},stop:function(){return this.setZoom(this._limitZoom(this._zoom)),this.options.zoomSnap||this.fire("viewreset"),this._stop()},locate:function(t){if(t=this._locateOptions=l({timeout:1e4,watch:!1},t),!("geolocation"in navigator))return this._handleGeolocationError({code:0,message:"Geolocation not supported."}),this;var i=a(this._handleGeolocationResponse,this),e=a(this._handleGeolocationError,this);return t.watch?this._locationWatchId=navigator.geolocation.watchPosition(i,e,t):navigator.geolocation.getCurrentPosition(i,e,t),this},stopLocate:function(){return navigator.geolocation&&navigator.geolocation.clearWatch&&navigator.geolocation.clearWatch(this._locationWatchId),this._locateOptions&&(this._locateOptions.setView=!1),this},_handleGeolocationError:function(t){var i;this._container._leaflet_id&&(i=t.code,t=t.message||(1===i?"permission denied":2===i?"position unavailable":"timeout"),this._locateOptions.setView&&!this._loaded&&this.fitWorld(),this.fire("locationerror",{code:i,message:"Geolocation error: "+t+"."}))},_handleGeolocationResponse:function(t){if(this._container._leaflet_id){var i,e,n=new v(t.coords.latitude,t.coords.longitude),o=n.toBounds(2*t.coords.accuracy),s=this._locateOptions,r=(s.setView&&(i=this.getBoundsZoom(o),this.setView(n,s.maxZoom?Math.min(i,s.maxZoom):i)),{latlng:n,bounds:o,timestamp:t.timestamp});for(e in t.coords)"number"==typeof t.coords[e]&&(r[e]=t.coords[e]);this.fire("locationfound",r)}},addHandler:function(t,i){if(!i)return this;i=this[t]=new i(this);return this._handlers.push(i),this.options[t]&&i.enable(),this},remove:function(){if(this._initEvents(!0),this.options.maxBounds&&this.off("moveend",this._panInsideMaxBounds),this._containerId!==this._container._leaflet_id)throw new Error("Map container is being reused by another instance");try{delete this._container._leaflet_id,delete this._containerId}catch(t){this._container._leaflet_id=void 0,this._containerId=void 0}for(var t in void 0!==this._locationWatchId&&this.stopLocate(),this._stop(),T(this._mapPane),this._clearControlPos&&this._clearControlPos(),this._resizeRequest&&(r(this._resizeRequest),this._resizeRequest=null),this._clearHandlers(),this._loaded&&this.fire("unload"),this._layers)this._layers[t].remove();for(t in this._panes)T(this._panes[t]);return this._layers=[],this._panes=[],delete this._mapPane,delete this._renderer,this},createPane:function(t,i){i=b("div","leaflet-pane"+(t?" leaflet-"+t.replace("Pane","")+"-pane":""),i||this._mapPane);return t&&(this._panes[t]=i),i},getCenter:function(){return this._checkIfLoaded(),this._lastCenter&&!this._moved()?this._lastCenter:this.layerPointToLatLng(this._getCenterLayerPoint())},getZoom:function(){return this._zoom},getBounds:function(){var t=this.getPixelBounds();return new s(this.unproject(t.getBottomLeft()),this.unproject(t.getTopRight()))},getMinZoom:function(){return void 0===this.options.minZoom?this._layersMinZoom||0:this.options.minZoom},getMaxZoom:function(){return void 0===this.options.maxZoom?void 0===this._layersMaxZoom?1/0:this._layersMaxZoom:this.options.maxZoom},getBoundsZoom:function(t,i,e){t=g(t),e=_(e||[0,0]);var n=this.getZoom()||0,o=this.getMinZoom(),s=this.getMaxZoom(),r=t.getNorthWest(),t=t.getSouthEast(),e=this.getSize().subtract(e),t=f(this.project(t,n),this.project(r,n)).getSize(),r=P.any3d?this.options.zoomSnap:1,a=e.x/t.x,e=e.y/t.y,t=i?Math.max(a,e):Math.min(a,e),n=this.getScaleZoom(t,n);return r&&(n=Math.round(n/(r/100))*(r/100),n=i?Math.ceil(n/r)*r:Math.floor(n/r)*r),Math.max(o,Math.min(s,n))},getSize:function(){return this._size&&!this._sizeChanged||(this._size=new p(this._container.clientWidth||0,this._container.clientHeight||0),this._sizeChanged=!1),this._size.clone()},getPixelBounds:function(t,i){t=this._getTopLeftPoint(t,i);return new m(t,t.add(this.getSize()))},getPixelOrigin:function(){return this._checkIfLoaded(),this._pixelOrigin},getPixelWorldBounds:function(t){return this.options.crs.getProjectedBounds(void 0===t?this.getZoom():t)},getPane:function(t){return"string"==typeof t?this._panes[t]:t},getPanes:function(){return this._panes},getContainer:function(){return this._container},getZoomScale:function(t,i){var e=this.options.crs;return i=void 0===i?this._zoom:i,e.scale(t)/e.scale(i)},getScaleZoom:function(t,i){var e=this.options.crs,t=(i=void 0===i?this._zoom:i,e.zoom(t*e.scale(i)));return isNaN(t)?1/0:t},project:function(t,i){return i=void 0===i?this._zoom:i,this.options.crs.latLngToPoint(w(t),i)},unproject:function(t,i){return i=void 0===i?this._zoom:i,this.options.crs.pointToLatLng(_(t),i)},layerPointToLatLng:function(t){t=_(t).add(this.getPixelOrigin());return this.unproject(t)},latLngToLayerPoint:function(t){return this.project(w(t))._round()._subtract(this.getPixelOrigin())},wrapLatLng:function(t){return this.options.crs.wrapLatLng(w(t))},wrapLatLngBounds:function(t){return this.options.crs.wrapLatLngBounds(g(t))},distance:function(t,i){return this.options.crs.distance(w(t),w(i))},containerPointToLayerPoint:function(t){return _(t).subtract(this._getMapPanePos())},layerPointToContainerPoint:function(t){return _(t).add(this._getMapPanePos())},containerPointToLatLng:function(t){t=this.containerPointToLayerPoint(_(t));return this.layerPointToLatLng(t)},latLngToContainerPoint:function(t){return this.layerPointToContainerPoint(this.latLngToLayerPoint(w(t)))},mouseEventToContainerPoint:function(t){return Ni(t,this._container)},mouseEventToLayerPoint:function(t){return this.containerPointToLayerPoint(this.mouseEventToContainerPoint(t))},mouseEventToLatLng:function(t){return this.layerPointToLatLng(this.mouseEventToLayerPoint(t))},_initContainer:function(t){t=this._container=_i(t);if(!t)throw new Error("Map container not found.");if(t._leaflet_id)throw new Error("Map container is already initialized.");S(t,"scroll",this._onScroll,this),this._containerId=h(t)},_initLayout:function(){var t=this._container,i=(this._fadeAnimated=this.options.fadeAnimation&&P.any3d,z(t,"leaflet-container"+(P.touch?" leaflet-touch":"")+(P.retina?" leaflet-retina":"")+(P.ielt9?" leaflet-oldie":"")+(P.safari?" leaflet-safari":"")+(this._fadeAnimated?" leaflet-fade-anim":"")),pi(t,"position"));"absolute"!==i&&"relative"!==i&&"fixed"!==i&&(t.style.position="relative"),this._initPanes(),this._initControlPos&&this._initControlPos()},_initPanes:function(){var t=this._panes={};this._paneRenderers={},this._mapPane=this.createPane("mapPane",this._container),Z(this._mapPane,new p(0,0)),this.createPane("tilePane"),this.createPane("overlayPane"),this.createPane("shadowPane"),this.createPane("markerPane"),this.createPane("tooltipPane"),this.createPane("popupPane"),this.options.markerZoomAnimation||(z(t.markerPane,"leaflet-zoom-hide"),z(t.shadowPane,"leaflet-zoom-hide"))},_resetView:function(t,i){Z(this._mapPane,new p(0,0));var e=!this._loaded,n=(this._loaded=!0,i=this._limitZoom(i),this.fire("viewprereset"),this._zoom!==i);this._moveStart(n,!1)._move(t,i)._moveEnd(n),this.fire("viewreset"),e&&this.fire("load")},_moveStart:function(t,i){return t&&this.fire("zoomstart"),i||this.fire("movestart"),this},_move:function(t,i,e,n){void 0===i&&(i=this._zoom);var o=this._zoom!==i;return this._zoom=i,this._lastCenter=t,this._pixelOrigin=this._getNewPixelOrigin(t),n?e&&e.pinch&&this.fire("zoom",e):((o||e&&e.pinch)&&this.fire("zoom",e),this.fire("move",e)),this},_moveEnd:function(t){return t&&this.fire("zoomend"),this.fire("moveend")},_stop:function(){return r(this._flyToFrame),this._panAnim&&this._panAnim.stop(),this},_rawPanBy:function(t){Z(this._mapPane,this._getMapPanePos().subtract(t))},_getZoomSpan:function(){return this.getMaxZoom()-this.getMinZoom()},_panInsideMaxBounds:function(){this._enforcingBounds||this.panInsideBounds(this.options.maxBounds)},_checkIfLoaded:function(){if(!this._loaded)throw new Error("Set map center and zoom first.")},_initEvents:function(t){this._targets={};var i=t?E:S;i((this._targets[h(this._container)]=this)._container,"click dblclick mousedown mouseup mouseover mouseout mousemove contextmenu keypress keydown keyup",this._handleDOMEvent,this),this.options.trackResize&&i(window,"resize",this._onResize,this),P.any3d&&this.options.transform3DLimit&&(t?this.off:this.on).call(this,"moveend",this._onMoveEnd)},_onResize:function(){r(this._resizeRequest),this._resizeRequest=x(function(){this.invalidateSize({debounceMoveend:!0})},this)},_onScroll:function(){this._container.scrollTop=0,this._container.scrollLeft=0},_onMoveEnd:function(){var t=this._getMapPanePos();Math.max(Math.abs(t.x),Math.abs(t.y))>=this.options.transform3DLimit&&this._resetView(this.getCenter(),this.getZoom())},_findEventTargets:function(t,i){for(var e,n=[],o="mouseout"===i||"mouseover"===i,s=t.target||t.srcElement,r=!1;s;){if((e=this._targets[h(s)])&&("click"===i||"preclick"===i)&&this._draggableMoved(e)){r=!0;break}if(e&&e.listens(i,!0)){if(o&&!Hi(s,t))break;if(n.push(e),o)break}if(s===this._container)break;s=s.parentNode}return n=n.length||r||o||!this.listens(i,!0)?n:[this]},_isClickDisabled:function(t){for(;t!==this._container;){if(t._leaflet_disable_click)return!0;t=t.parentNode}},_handleDOMEvent:function(t){var i,e=t.target||t.srcElement;!this._loaded||e._leaflet_disable_events||"click"===t.type&&this._isClickDisabled(e)||("mousedown"===(i=t.type)&&zi(e),this._fireDOMEvent(t,i))},_mouseEvents:["click","dblclick","mouseover","mouseout","contextmenu"],_fireDOMEvent:function(t,i,e){"click"===t.type&&((a=l({},t)).type="preclick",this._fireDOMEvent(a,a.type,e));var n=this._findEventTargets(t,i);if(e){for(var o=[],s=0;s<e.length;s++)e[s].listens(i,!0)&&o.push(e[s]);n=o.concat(n)}if(n.length){"contextmenu"===i&&B(t);var r,a=n[0],h={originalEvent:t};for("keypress"!==t.type&&"keydown"!==t.type&&"keyup"!==t.type&&(r=a.getLatLng&&(!a._radius||a._radius<=10),h.containerPoint=r?this.latLngToContainerPoint(a.getLatLng()):this.mouseEventToContainerPoint(t),h.layerPoint=this.containerPointToLayerPoint(h.containerPoint),h.latlng=r?a.getLatLng():this.layerPointToLatLng(h.layerPoint)),s=0;s<n.length;s++)if(n[s].fire(i,h,!0),h.originalEvent._stopped||!1===n[s].options.bubblingMouseEvents&&-1!==G(this._mouseEvents,i))return}},_draggableMoved:function(t){return(t=t.dragging&&t.dragging.enabled()?t:this).dragging&&t.dragging.moved()||this.boxZoom&&this.boxZoom.moved()},_clearHandlers:function(){for(var t=0,i=this._handlers.length;t<i;t++)this._handlers[t].disable()},whenReady:function(t,i){return this._loaded?t.call(i||this,{target:this}):this.on("load",t,i),this},_getMapPanePos:function(){return bi(this._mapPane)||new p(0,0)},_moved:function(){var t=this._getMapPanePos();return t&&!t.equals([0,0])},_getTopLeftPoint:function(t,i){return(t&&void 0!==i?this._getNewPixelOrigin(t,i):this.getPixelOrigin()).subtract(this._getMapPanePos())},_getNewPixelOrigin:function(t,i){var e=this.getSize()._divideBy(2);return this.project(t,i)._subtract(e)._add(this._getMapPanePos())._round()},_latLngToNewLayerPoint:function(t,i,e){e=this._getNewPixelOrigin(e,i);return this.project(t,i)._subtract(e)},_latLngBoundsToNewLayerBounds:function(t,i,e){e=this._getNewPixelOrigin(e,i);return f([this.project(t.getSouthWest(),i)._subtract(e),this.project(t.getNorthWest(),i)._subtract(e),this.project(t.getSouthEast(),i)._subtract(e),this.project(t.getNorthEast(),i)._subtract(e)])},_getCenterLayerPoint:function(){return this.containerPointToLayerPoint(this.getSize()._divideBy(2))},_getCenterOffset:function(t){return this.latLngToLayerPoint(t).subtract(this._getCenterLayerPoint())},_limitCenter:function(t,i,e){if(!e)return t;var n=this.project(t,i),o=this.getSize().divideBy(2),o=new m(n.subtract(o),n.add(o)),o=this._getBoundsOffset(o,e,i);return o.round().equals([0,0])?t:this.unproject(n.add(o),i)},_limitOffset:function(t,i){if(!i)return t;var e=this.getPixelBounds(),e=new m(e.min.add(t),e.max.add(t));return t.add(this._getBoundsOffset(e,i))},_getBoundsOffset:function(t,i,e){i=f(this.project(i.getNorthEast(),e),this.project(i.getSouthWest(),e)),e=i.min.subtract(t.min),i=i.max.subtract(t.max);return new p(this._rebound(e.x,-i.x),this._rebound(e.y,-i.y))},_rebound:function(t,i){return 0<t+i?Math.round(t-i)/2:Math.max(0,Math.ceil(t))-Math.max(0,Math.floor(i))},_limitZoom:function(t){var i=this.getMinZoom(),e=this.getMaxZoom(),n=P.any3d?this.options.zoomSnap:1;return n&&(t=Math.round(t/n)*n),Math.max(i,Math.min(e,t))},_onPanTransitionStep:function(){this.fire("move")},_onPanTransitionEnd:function(){M(this._mapPane,"leaflet-pan-anim"),this.fire("moveend")},_tryAnimatedPan:function(t,i){t=this._getCenterOffset(t)._trunc();return!(!0!==(i&&i.animate)&&!this.getSize().contains(t))&&(this.panBy(t,i),!0)},_createAnimProxy:function(){var t=this._proxy=b("div","leaflet-proxy leaflet-zoom-animated");this._panes.mapPane.appendChild(t),this.on("zoomanim",function(t){var i=ui,e=this._proxy.style[i];Pi(this._proxy,this.project(t.center,t.zoom),this.getZoomScale(t.zoom,1)),e===this._proxy.style[i]&&this._animatingZoom&&this._onZoomTransitionEnd()},this),this.on("load moveend",this._animMoveEnd,this),this._on("unload",this._destroyAnimProxy,this)},_destroyAnimProxy:function(){T(this._proxy),this.off("load moveend",this._animMoveEnd,this),delete this._proxy},_animMoveEnd:function(){var t=this.getCenter(),i=this.getZoom();Pi(this._proxy,this.project(t,i),this.getZoomScale(i,1))},_catchTransitionEnd:function(t){this._animatingZoom&&0<=t.propertyName.indexOf("transform")&&this._onZoomTransitionEnd()},_nothingToAnimate:function(){return!this._container.getElementsByClassName("leaflet-zoom-animated").length},_tryAnimatedZoom:function(t,i,e){if(this._animatingZoom)return!0;if(e=e||{},!this._zoomAnimated||!1===e.animate||this._nothingToAnimate()||Math.abs(i-this._zoom)>this.options.zoomAnimationThreshold)return!1;var n=this.getZoomScale(i),n=this._getCenterOffset(t)._divideBy(1-1/n);return!(!0!==e.animate&&!this.getSize().contains(n))&&(x(function(){this._moveStart(!0,!1)._animateZoom(t,i,!0)},this),!0)},_animateZoom:function(t,i,e,n){this._mapPane&&(e&&(this._animatingZoom=!0,this._animateToCenter=t,this._animateToZoom=i,z(this._mapPane,"leaflet-zoom-anim")),this.fire("zoomanim",{center:t,zoom:i,noUpdate:n}),this._tempFireZoomEvent||(this._tempFireZoomEvent=this._zoom!==this._animateToZoom),this._move(this._animateToCenter,this._animateToZoom,void 0,!0),setTimeout(a(this._onZoomTransitionEnd,this),250))},_onZoomTransitionEnd:function(){this._animatingZoom&&(this._mapPane&&M(this._mapPane,"leaflet-zoom-anim"),this._animatingZoom=!1,this._move(this._animateToCenter,this._animateToZoom,void 0,!0),this._tempFireZoomEvent&&this.fire("zoom"),delete this._tempFireZoomEvent,this.fire("move"),this._moveEnd(!0))}});function Fi(t){return new I(t)}var Ui,I=it.extend({options:{position:"topright"},initialize:function(t){c(this,t)},getPosition:function(){return this.options.position},setPosition:function(t){var i=this._map;return i&&i.removeControl(this),this.options.position=t,i&&i.addControl(this),this},getContainer:function(){return this._container},addTo:function(t){this.remove(),this._map=t;var i=this._container=this.onAdd(t),e=this.getPosition(),t=t._controlCorners[e];return z(i,"leaflet-control"),-1!==e.indexOf("bottom")?t.insertBefore(i,t.firstChild):t.appendChild(i),this._map.on("unload",this.remove,this),this},remove:function(){return this._map&&(T(this._container),this.onRemove&&this.onRemove(this._map),this._map.off("unload",this.remove,this),this._map=null),this},_refocusOnMap:function(t){this._map&&t&&0<t.screenX&&0<t.screenY&&this._map.getContainer().focus()}}),Vi=(A.include({addControl:function(t){return t.addTo(this),this},removeControl:function(t){return t.remove(),this},_initControlPos:function(){var e=this._controlCorners={},n="leaflet-",o=this._controlContainer=b("div",n+"control-container",this._container);function t(t,i){e[t+i]=b("div",n+t+" "+n+i,o)}t("top","left"),t("top","right"),t("bottom","left"),t("bottom","right")},_clearControlPos:function(){for(var t in this._controlCorners)T(this._controlCorners[t]);T(this._controlContainer),delete this._controlCorners,delete this._controlContainer}}),I.extend({options:{collapsed:!0,position:"topright",autoZIndex:!0,hideSingleBase:!1,sortLayers:!1,sortFunction:function(t,i,e,n){return e<n?-1:n<e?1:0}},initialize:function(t,i,e){for(var n in c(this,e),this._layerControlInputs=[],this._layers=[],this._lastZIndex=0,this._handlingClick=!1,t)this._addLayer(t[n],n);for(n in i)this._addLayer(i[n],n,!0)},onAdd:function(t){this._initLayout(),this._update(),(this._map=t).on("zoomend",this._checkDisabledLayers,this);for(var i=0;i<this._layers.length;i++)this._layers[i].layer.on("add remove",this._onLayerChange,this);return this._container},addTo:function(t){return I.prototype.addTo.call(this,t),this._expandIfNotCollapsed()},onRemove:function(){this._map.off("zoomend",this._checkDisabledLayers,this);for(var t=0;t<this._layers.length;t++)this._layers[t].layer.off("add remove",this._onLayerChange,this)},addBaseLayer:function(t,i){return this._addLayer(t,i),this._map?this._update():this},addOverlay:function(t,i){return this._addLayer(t,i,!0),this._map?this._update():this},removeLayer:function(t){t.off("add remove",this._onLayerChange,this);t=this._getLayer(h(t));return t&&this._layers.splice(this._layers.indexOf(t),1),this._map?this._update():this},expand:function(){z(this._container,"leaflet-control-layers-expanded"),this._section.style.height=null;var t=this._map.getSize().y-(this._container.offsetTop+50);return t<this._section.clientHeight?(z(this._section,"leaflet-control-layers-scrollbar"),this._section.style.height=t+"px"):M(this._section,"leaflet-control-layers-scrollbar"),this._checkDisabledLayers(),this},collapse:function(){return M(this._container,"leaflet-control-layers-expanded"),this},_initLayout:function(){var t="leaflet-control-layers",i=this._container=b("div",t),e=this.options.collapsed,n=(i.setAttribute("aria-haspopup",!0),Oi(i),Ii(i),this._section=b("section",t+"-list")),o=(e&&(this._map.on("click",this.collapse,this),S(i,{mouseenter:function(){S(n,"click",B),this.expand(),setTimeout(function(){E(n,"click",B)})},mouseleave:this.collapse},this)),this._layersLink=b("a",t+"-toggle",i));o.href="#",o.title="Layers",o.setAttribute("role","button"),S(o,"click",B),S(o,"focus",this.expand,this),e||this.expand(),this._baseLayersList=b("div",t+"-base",n),this._separator=b("div",t+"-separator",n),this._overlaysList=b("div",t+"-overlays",n),i.appendChild(n)},_getLayer:function(t){for(var i=0;i<this._layers.length;i++)if(this._layers[i]&&h(this._layers[i].layer)===t)return this._layers[i]},_addLayer:function(t,i,e){this._map&&t.on("add remove",this._onLayerChange,this),this._layers.push({layer:t,name:i,overlay:e}),this.options.sortLayers&&this._layers.sort(a(function(t,i){return this.options.sortFunction(t.layer,i.layer,t.name,i.name)},this)),this.options.autoZIndex&&t.setZIndex&&(this._lastZIndex++,t.setZIndex(this._lastZIndex)),this._expandIfNotCollapsed()},_update:function(){if(!this._container)return this;mi(this._baseLayersList),mi(this._overlaysList),this._layerControlInputs=[];for(var t,i,e,n=0,o=0;o<this._layers.length;o++)e=this._layers[o],this._addItem(e),i=i||e.overlay,t=t||!e.overlay,n+=e.overlay?0:1;return this.options.hideSingleBase&&(this._baseLayersList.style.display=(t=t&&1<n)?"":"none"),this._separator.style.display=i&&t?"":"none",this},_onLayerChange:function(t){this._handlingClick||this._update();var i=this._getLayer(h(t.target)),t=i.overlay?"add"===t.type?"overlayadd":"overlayremove":"add"===t.type?"baselayerchange":null;t&&this._map.fire(t,i)},_createRadioElement:function(t,i){t='<input type="radio" class="leaflet-control-layers-selector" name="'+t+'"'+(i?' checked="checked"':"")+"/>",i=document.createElement("div");return i.innerHTML=t,i.firstChild},_addItem:function(t){var i,e=document.createElement("label"),n=this._map.hasLayer(t.layer),n=(t.overlay?((i=document.createElement("input")).type="checkbox",i.className="leaflet-control-layers-selector",i.defaultChecked=n):i=this._createRadioElement("leaflet-base-layers_"+h(this),n),this._layerControlInputs.push(i),i.layerId=h(t.layer),S(i,"click",this._onInputClick,this),document.createElement("span")),o=(n.innerHTML=" "+t.name,document.createElement("span"));return e.appendChild(o),o.appendChild(i),o.appendChild(n),(t.overlay?this._overlaysList:this._baseLayersList).appendChild(e),this._checkDisabledLayers(),e},_onInputClick:function(){var t,i,e=this._layerControlInputs,n=[],o=[];this._handlingClick=!0;for(var s=e.length-1;0<=s;s--)t=e[s],i=this._getLayer(t.layerId).layer,t.checked?n.push(i):t.checked||o.push(i);for(s=0;s<o.length;s++)this._map.hasLayer(o[s])&&this._map.removeLayer(o[s]);for(s=0;s<n.length;s++)this._map.hasLayer(n[s])||this._map.addLayer(n[s]);this._handlingClick=!1,this._refocusOnMap()},_checkDisabledLayers:function(){for(var t,i,e=this._layerControlInputs,n=this._map.getZoom(),o=e.length-1;0<=o;o--)t=e[o],i=this._getLayer(t.layerId).layer,t.disabled=void 0!==i.options.minZoom&&n<i.options.minZoom||void 0!==i.options.maxZoom&&n>i.options.maxZoom},_expandIfNotCollapsed:function(){return this._map&&!this.options.collapsed&&this.expand(),this}})),qi=I.extend({options:{position:"topleft",zoomInText:'<span aria-hidden="true">+</span>',zoomInTitle:"Zoom in",zoomOutText:'<span aria-hidden="true">&#x2212;</span>',zoomOutTitle:"Zoom out"},onAdd:function(t){var i="leaflet-control-zoom",e=b("div",i+" leaflet-bar"),n=this.options;return this._zoomInButton=this._createButton(n.zoomInText,n.zoomInTitle,i+"-in",e,this._zoomIn),this._zoomOutButton=this._createButton(n.zoomOutText,n.zoomOutTitle,i+"-out",e,this._zoomOut),this._updateDisabled(),t.on("zoomend zoomlevelschange",this._updateDisabled,this),e},onRemove:function(t){t.off("zoomend zoomlevelschange",this._updateDisabled,this)},disable:function(){return this._disabled=!0,this._updateDisabled(),this},enable:function(){return this._disabled=!1,this._updateDisabled(),this},_zoomIn:function(t){!this._disabled&&this._map._zoom<this._map.getMaxZoom()&&this._map.zoomIn(this._map.options.zoomDelta*(t.shiftKey?3:1))},_zoomOut:function(t){!this._disabled&&this._map._zoom>this._map.getMinZoom()&&this._map.zoomOut(this._map.options.zoomDelta*(t.shiftKey?3:1))},_createButton:function(t,i,e,n,o){e=b("a",e,n);return e.innerHTML=t,e.href="#",e.title=i,e.setAttribute("role","button"),e.setAttribute("aria-label",i),Oi(e),S(e,"click",Ri),S(e,"click",o,this),S(e,"click",this._refocusOnMap,this),e},_updateDisabled:function(){var t=this._map,i="leaflet-disabled";M(this._zoomInButton,i),M(this._zoomOutButton,i),this._zoomInButton.setAttribute("aria-disabled","false"),this._zoomOutButton.setAttribute("aria-disabled","false"),!this._disabled&&t._zoom!==t.getMinZoom()||(z(this._zoomOutButton,i),this._zoomOutButton.setAttribute("aria-disabled","true")),!this._disabled&&t._zoom!==t.getMaxZoom()||(z(this._zoomInButton,i),this._zoomInButton.setAttribute("aria-disabled","true"))}}),Gi=(A.mergeOptions({zoomControl:!0}),A.addInitHook(function(){this.options.zoomControl&&(this.zoomControl=new qi,this.addControl(this.zoomControl))}),I.extend({options:{position:"bottomleft",maxWidth:100,metric:!0,imperial:!0},onAdd:function(t){var i="leaflet-control-scale",e=b("div",i),n=this.options;return this._addScales(n,i+"-line",e),t.on(n.updateWhenIdle?"moveend":"move",this._update,this),t.whenReady(this._update,this),e},onRemove:function(t){t.off(this.options.updateWhenIdle?"moveend":"move",this._update,this)},_addScales:function(t,i,e){t.metric&&(this._mScale=b("div",i,e)),t.imperial&&(this._iScale=b("div",i,e))},_update:function(){var t=this._map,i=t.getSize().y/2,t=t.distance(t.containerPointToLatLng([0,i]),t.containerPointToLatLng([this.options.maxWidth,i]));this._updateScales(t)},_updateScales:function(t){this.options.metric&&t&&this._updateMetric(t),this.options.imperial&&t&&this._updateImperial(t)},_updateMetric:function(t){var i=this._getRoundNum(t);this._updateScale(this._mScale,i<1e3?i+" m":i/1e3+" km",i/t)},_updateImperial:function(t){var i,e,t=3.2808399*t;5280<t?(e=this._getRoundNum(i=t/5280),this._updateScale(this._iScale,e+" mi",e/i)):(e=this._getRoundNum(t),this._updateScale(this._iScale,e+" ft",e/t))},_updateScale:function(t,i,e){t.style.width=Math.round(this.options.maxWidth*e)+"px",t.innerHTML=i},_getRoundNum:function(t){var i=Math.pow(10,(Math.floor(t)+"").length-1),t=t/i;return i*(t=10<=t?10:5<=t?5:3<=t?3:2<=t?2:1)}})),Ki=I.extend({options:{position:"bottomright",prefix:'<a href="https://leafletjs.com" title="A JavaScript library for interactive maps">'+(P.inlineSvg?'<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="12" height="8"><path fill="#4C7BE1" d="M0 0h12v4H0z"/><path fill="#FFD500" d="M0 4h12v3H0z"/><path fill="#E0BC00" d="M0 7h12v1H0z"/></svg> ':"")+"Leaflet</a>"},initialize:function(t){c(this,t),this._attributions={}},onAdd:function(t){for(var i in(t.attributionControl=this)._container=b("div","leaflet-control-attribution"),Oi(this._container),t._layers)t._layers[i].getAttribution&&this.addAttribution(t._layers[i].getAttribution());return this._update(),t.on("layeradd",this._addAttribution,this),this._container},onRemove:function(t){t.off("layeradd",this._addAttribution,this)},_addAttribution:function(t){t.layer.getAttribution&&(this.addAttribution(t.layer.getAttribution()),t.layer.once("remove",function(){this.removeAttribution(t.layer.getAttribution())},this))},setPrefix:function(t){return this.options.prefix=t,this._update(),this},addAttribution:function(t){return t&&(this._attributions[t]||(this._attributions[t]=0),this._attributions[t]++,this._update()),this},removeAttribution:function(t){return t&&this._attributions[t]&&(this._attributions[t]--,this._update()),this},_update:function(){if(this._map){var t,i=[];for(t in this._attributions)this._attributions[t]&&i.push(t);var e=[];this.options.prefix&&e.push(this.options.prefix),i.length&&e.push(i.join(", ")),this._container.innerHTML=e.join(' <span aria-hidden="true">|</span> ')}}}),n=(A.mergeOptions({attributionControl:!0}),A.addInitHook(function(){this.options.attributionControl&&(new Ki).addTo(this)}),I.Layers=Vi,I.Zoom=qi,I.Scale=Gi,I.Attribution=Ki,Fi.layers=function(t,i,e){return new Vi(t,i,e)},Fi.zoom=function(t){return new qi(t)},Fi.scale=function(t){return new Gi(t)},Fi.attribution=function(t){return new Ki(t)},it.extend({initialize:function(t){this._map=t},enable:function(){return this._enabled||(this._enabled=!0,this.addHooks()),this},disable:function(){return this._enabled&&(this._enabled=!1,this.removeHooks()),this},enabled:function(){return!!this._enabled}})),ft=(n.addTo=function(t,i){return t.addHandler(i,this),this},{Events:i}),Yi=P.touch?"touchstart mousedown":"mousedown",Xi=et.extend({options:{clickTolerance:3},initialize:function(t,i,e,n){c(this,n),this._element=t,this._dragStartTarget=i||t,this._preventOutline=e},enable:function(){this._enabled||(S(this._dragStartTarget,Yi,this._onDown,this),this._enabled=!0)},disable:function(){this._enabled&&(Xi._dragging===this&&this.finishDrag(!0),E(this._dragStartTarget,Yi,this._onDown,this),this._enabled=!1,this._moved=!1)},_onDown:function(t){var i,e;this._enabled&&(this._moved=!1,vi(this._element,"leaflet-zoom-anim")||(t.touches&&1!==t.touches.length?Xi._dragging===this&&this.finishDrag():Xi._dragging||t.shiftKey||1!==t.which&&1!==t.button&&!t.touches||((Xi._dragging=this)._preventOutline&&zi(this._element),Li(),ri(),this._moving||(this.fire("down"),e=t.touches?t.touches[0]:t,i=Ci(this._element),this._startPoint=new p(e.clientX,e.clientY),this._startPos=bi(this._element),this._parentScale=Zi(i),e="mousedown"===t.type,S(document,e?"mousemove":"touchmove",this._onMove,this),S(document,e?"mouseup":"touchend touchcancel",this._onUp,this)))))},_onMove:function(t){var i;this._enabled&&(t.touches&&1<t.touches.length?this._moved=!0:!(i=new p((i=t.touches&&1===t.touches.length?t.touches[0]:t).clientX,i.clientY)._subtract(this._startPoint)).x&&!i.y||Math.abs(i.x)+Math.abs(i.y)<this.options.clickTolerance||(i.x/=this._parentScale.x,i.y/=this._parentScale.y,B(t),this._moved||(this.fire("dragstart"),this._moved=!0,z(document.body,"leaflet-dragging"),this._lastTarget=t.target||t.srcElement,window.SVGElementInstance&&this._lastTarget instanceof window.SVGElementInstance&&(this._lastTarget=this._lastTarget.correspondingUseElement),z(this._lastTarget,"leaflet-drag-target")),this._newPos=this._startPos.add(i),this._moving=!0,this._lastEvent=t,this._updatePosition()))},_updatePosition:function(){var t={originalEvent:this._lastEvent};this.fire("predrag",t),Z(this._element,this._newPos),this.fire("drag",t)},_onUp:function(){this._enabled&&this.finishDrag()},finishDrag:function(t){M(document.body,"leaflet-dragging"),this._lastTarget&&(M(this._lastTarget,"leaflet-drag-target"),this._lastTarget=null),E(document,"mousemove touchmove",this._onMove,this),E(document,"mouseup touchend touchcancel",this._onUp,this),Ti(),ai(),this._moved&&this._moving&&this.fire("dragend",{noInertia:t,distance:this._newPos.distanceTo(this._startPos)}),this._moving=!1,Xi._dragging=!1}});function Ji(t,i){if(!i||!t.length)return t.slice();i*=i;return t=function(t,i){var e=t.length,n=new(typeof Uint8Array!=void 0+""?Uint8Array:Array)(e);n[0]=n[e-1]=1,function t(i,e,n,o,s){var r,a,h,l=0;for(a=o+1;a<=s-1;a++)h=ee(i[a],i[o],i[s],!0),l<h&&(r=a,l=h);n<l&&(e[r]=1,t(i,e,n,o,r),t(i,e,n,r,s))}(t,n,i,0,e-1);var o,s=[];for(o=0;o<e;o++)n[o]&&s.push(t[o]);return s}(t=function(t,i){for(var e=[t[0]],n=1,o=0,s=t.length;n<s;n++)(function(t,i){var e=i.x-t.x,i=i.y-t.y;return e*e+i*i})(t[n],t[o])>i&&(e.push(t[n]),o=n);o<s-1&&e.push(t[s-1]);return e}(t,i),i)}function $i(t,i,e){return Math.sqrt(ee(t,i,e,!0))}function Qi(t,i,e,n,o){var s,r,a,h=n?Ui:ie(t,e),l=ie(i,e);for(Ui=l;;){if(!(h|l))return[t,i];if(h&l)return!1;a=ie(r=te(t,i,s=h||l,e,o),e),s===h?(t=r,h=a):(i=r,l=a)}}function te(t,i,e,n,o){var s,r,a=i.x-t.x,i=i.y-t.y,h=n.min,n=n.max;return 8&e?(s=t.x+a*(n.y-t.y)/i,r=n.y):4&e?(s=t.x+a*(h.y-t.y)/i,r=h.y):2&e?(s=n.x,r=t.y+i*(n.x-t.x)/a):1&e&&(s=h.x,r=t.y+i*(h.x-t.x)/a),new p(s,r,o)}function ie(t,i){var e=0;return t.x<i.min.x?e|=1:t.x>i.max.x&&(e|=2),t.y<i.min.y?e|=4:t.y>i.max.y&&(e|=8),e}function ee(t,i,e,n){var o=i.x,i=i.y,s=e.x-o,r=e.y-i,a=s*s+r*r;return 0<a&&(1<(a=((t.x-o)*s+(t.y-i)*r)/a)?(o=e.x,i=e.y):0<a&&(o+=s*a,i+=r*a)),s=t.x-o,r=t.y-i,n?s*s+r*r:new p(o,i)}function ne(t){return!d(t[0])||"object"!=typeof t[0][0]&&void 0!==t[0][0]}function oe(t){return console.warn("Deprecated use of _flat, please use L.LineUtil.isFlat instead."),ne(t)}gt={__proto__:null,simplify:Ji,pointToSegmentDistance:$i,closestPointOnSegment:function(t,i,e){return ee(t,i,e)},clipSegment:Qi,_getEdgeIntersection:te,_getBitCode:ie,_sqClosestPointOnSegment:ee,isFlat:ne,_flat:oe};function se(t,i,e){for(var n,o,s,r,a,h,l,u=[1,4,2,8],c=0,d=t.length;c<d;c++)t[c]._code=ie(t[c],i);for(s=0;s<4;s++){for(h=u[s],n=[],c=0,o=(d=t.length)-1;c<d;o=c++)r=t[c],a=t[o],r._code&h?a._code&h||((l=te(a,r,h,i,e))._code=ie(l,i),n.push(l)):(a._code&h&&((l=te(a,r,h,i,e))._code=ie(l,i),n.push(l)),n.push(r));t=n}return t}var vt={__proto__:null,clipPolygon:se},yt={project:function(t){return new p(t.lng,t.lat)},unproject:function(t){return new v(t.y,t.x)},bounds:new m([-180,-90],[180,90])},xt={R:6378137,R_MINOR:6356752.314245179,bounds:new m([-20037508.34279,-15496570.73972],[20037508.34279,18764656.23138]),project:function(t){var i=Math.PI/180,e=this.R,n=t.lat*i,o=this.R_MINOR/e,o=Math.sqrt(1-o*o),s=o*Math.sin(n),s=Math.tan(Math.PI/4-n/2)/Math.pow((1-s)/(1+s),o/2),n=-e*Math.log(Math.max(s,1e-10));return new p(t.lng*i*e,n)},unproject:function(t){for(var i,e=180/Math.PI,n=this.R,o=this.R_MINOR/n,s=Math.sqrt(1-o*o),r=Math.exp(-t.y/n),a=Math.PI/2-2*Math.atan(r),h=0,l=.1;h<15&&1e-7<Math.abs(l);h++)i=s*Math.sin(a),i=Math.pow((1-i)/(1+i),s/2),a+=l=Math.PI/2-2*Math.atan(r*i)-a;return new v(a*e,t.x*e/n)}},wt={__proto__:null,LonLat:yt,Mercator:xt,SphericalMercator:rt},bt=l({},st,{code:"EPSG:3395",projection:xt,transformation:ht(Pt=.5/(Math.PI*xt.R),.5,-Pt,.5)}),re=l({},st,{code:"EPSG:4326",projection:yt,transformation:ht(1/180,1,-1/180,.5)}),Lt=l({},ot,{projection:yt,transformation:ht(1,0,-1,0),scale:function(t){return Math.pow(2,t)},zoom:function(t){return Math.log(t)/Math.LN2},distance:function(t,i){var e=i.lng-t.lng,i=i.lat-t.lat;return Math.sqrt(e*e+i*i)},infinite:!0}),o=(ot.Earth=st,ot.EPSG3395=bt,ot.EPSG3857=lt,ot.EPSG900913=ut,ot.EPSG4326=re,ot.Simple=Lt,et.extend({options:{pane:"overlayPane",attribution:null,bubblingMouseEvents:!0},addTo:function(t){return t.addLayer(this),this},remove:function(){return this.removeFrom(this._map||this._mapToAdd)},removeFrom:function(t){return t&&t.removeLayer(this),this},getPane:function(t){return this._map.getPane(t?this.options[t]||t:this.options.pane)},addInteractiveTarget:function(t){return this._map._targets[h(t)]=this},removeInteractiveTarget:function(t){return delete this._map._targets[h(t)],this},getAttribution:function(){return this.options.attribution},_layerAdd:function(t){var i,e=t.target;e.hasLayer(this)&&(this._map=e,this._zoomAnimated=e._zoomAnimated,this.getEvents&&(i=this.getEvents(),e.on(i,this),this.once("remove",function(){e.off(i,this)},this)),this.onAdd(e),this.fire("add"),e.fire("layeradd",{layer:this}))}})),ae=(A.include({addLayer:function(t){if(!t._layerAdd)throw new Error("The provided object is not a Layer.");var i=h(t);return this._layers[i]||((this._layers[i]=t)._mapToAdd=this,t.beforeAdd&&t.beforeAdd(this),this.whenReady(t._layerAdd,t)),this},removeLayer:function(t){var i=h(t);return this._layers[i]&&(this._loaded&&t.onRemove(this),delete this._layers[i],this._loaded&&(this.fire("layerremove",{layer:t}),t.fire("remove")),t._map=t._mapToAdd=null),this},hasLayer:function(t){return h(t)in this._layers},eachLayer:function(t,i){for(var e in this._layers)t.call(i,this._layers[e]);return this},_addLayers:function(t){for(var i=0,e=(t=t?d(t)?t:[t]:[]).length;i<e;i++)this.addLayer(t[i])},_addZoomLimit:function(t){isNaN(t.options.maxZoom)&&isNaN(t.options.minZoom)||(this._zoomBoundLayers[h(t)]=t,this._updateZoomLevels())},_removeZoomLimit:function(t){t=h(t);this._zoomBoundLayers[t]&&(delete this._zoomBoundLayers[t],this._updateZoomLevels())},_updateZoomLevels:function(){var t,i=1/0,e=-1/0,n=this._getZoomSpan();for(t in this._zoomBoundLayers)var o=this._zoomBoundLayers[t].options,i=void 0===o.minZoom?i:Math.min(i,o.minZoom),e=void 0===o.maxZoom?e:Math.max(e,o.maxZoom);this._layersMaxZoom=e===-1/0?void 0:e,this._layersMinZoom=i===1/0?void 0:i,n!==this._getZoomSpan()&&this.fire("zoomlevelschange"),void 0===this.options.maxZoom&&this._layersMaxZoom&&this.getZoom()>this._layersMaxZoom&&this.setZoom(this._layersMaxZoom),void 0===this.options.minZoom&&this._layersMinZoom&&this.getZoom()<this._layersMinZoom&&this.setZoom(this._layersMinZoom)}}),o.extend({initialize:function(t,i){var e,n;if(c(this,i),this._layers={},t)for(e=0,n=t.length;e<n;e++)this.addLayer(t[e])},addLayer:function(t){var i=this.getLayerId(t);return this._layers[i]=t,this._map&&this._map.addLayer(t),this},removeLayer:function(t){t=t in this._layers?t:this.getLayerId(t);return this._map&&this._layers[t]&&this._map.removeLayer(this._layers[t]),delete this._layers[t],this},hasLayer:function(t){return("number"==typeof t?t:this.getLayerId(t))in this._layers},clearLayers:function(){return this.eachLayer(this.removeLayer,this)},invoke:function(t){var i,e,n=Array.prototype.slice.call(arguments,1);for(i in this._layers)(e=this._layers[i])[t]&&e[t].apply(e,n);return this},onAdd:function(t){this.eachLayer(t.addLayer,t)},onRemove:function(t){this.eachLayer(t.removeLayer,t)},eachLayer:function(t,i){for(var e in this._layers)t.call(i,this._layers[e]);return this},getLayer:function(t){return this._layers[t]},getLayers:function(){var t=[];return this.eachLayer(t.push,t),t},setZIndex:function(t){return this.invoke("setZIndex",t)},getLayerId:h})),he=ae.extend({addLayer:function(t){return this.hasLayer(t)?this:(t.addEventParent(this),ae.prototype.addLayer.call(this,t),this.fire("layeradd",{layer:t}))},removeLayer:function(t){return this.hasLayer(t)?((t=t in this._layers?this._layers[t]:t).removeEventParent(this),ae.prototype.removeLayer.call(this,t),this.fire("layerremove",{layer:t})):this},setStyle:function(t){return this.invoke("setStyle",t)},bringToFront:function(){return this.invoke("bringToFront")},bringToBack:function(){return this.invoke("bringToBack")},getBounds:function(){var t,i=new s;for(t in this._layers){var e=this._layers[t];i.extend(e.getBounds?e.getBounds():e.getLatLng())}return i}}),le=it.extend({options:{popupAnchor:[0,0],tooltipAnchor:[0,0],crossOrigin:!1},initialize:function(t){c(this,t)},createIcon:function(t){return this._createIcon("icon",t)},createShadow:function(t){return this._createIcon("shadow",t)},_createIcon:function(t,i){var e=this._getIconUrl(t);if(!e){if("icon"===t)throw new Error("iconUrl not set in Icon options (see the docs).");return null}e=this._createImg(e,i&&"IMG"===i.tagName?i:null);return this._setIconStyles(e,t),!this.options.crossOrigin&&""!==this.options.crossOrigin||(e.crossOrigin=!0===this.options.crossOrigin?"":this.options.crossOrigin),e},_setIconStyles:function(t,i){var e=this.options,n=e[i+"Size"],n=_(n="number"==typeof n?[n,n]:n),o=_("shadow"===i&&e.shadowAnchor||e.iconAnchor||n&&n.divideBy(2,!0));t.className="leaflet-marker-"+i+" "+(e.className||""),o&&(t.style.marginLeft=-o.x+"px",t.style.marginTop=-o.y+"px"),n&&(t.style.width=n.x+"px",t.style.height=n.y+"px")},_createImg:function(t,i){return(i=i||document.createElement("img")).src=t,i},_getIconUrl:function(t){return P.retina&&this.options[t+"RetinaUrl"]||this.options[t+"Url"]}});var ue=le.extend({options:{iconUrl:"marker-icon.png",iconRetinaUrl:"marker-icon-2x.png",shadowUrl:"marker-shadow.png",iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],tooltipAnchor:[16,-28],shadowSize:[41,41]},_getIconUrl:function(t){return"string"!=typeof ue.imagePath&&(ue.imagePath=this._detectIconPath()),(this.options.imagePath||ue.imagePath)+le.prototype._getIconUrl.call(this,t)},_stripUrl:function(t){function i(t,i,e){return(i=i.exec(t))&&i[e]}return(t=i(t,/^url\((['"])?(.+)\1\)$/,2))&&i(t,/^(.*)marker-icon\.png$/,1)},_detectIconPath:function(){var t=b("div","leaflet-default-icon-path",document.body),i=pi(t,"background-image")||pi(t,"backgroundImage");if(document.body.removeChild(t),i=this._stripUrl(i))return i;t=document.querySelector('link[href$="leaflet.css"]');return t?t.href.substring(0,t.href.length-"leaflet.css".length-1):""}}),ce=n.extend({initialize:function(t){this._marker=t},addHooks:function(){var t=this._marker._icon;this._draggable||(this._draggable=new Xi(t,t,!0)),this._draggable.on({dragstart:this._onDragStart,predrag:this._onPreDrag,drag:this._onDrag,dragend:this._onDragEnd},this).enable(),z(t,"leaflet-marker-draggable")},removeHooks:function(){this._draggable.off({dragstart:this._onDragStart,predrag:this._onPreDrag,drag:this._onDrag,dragend:this._onDragEnd},this).disable(),this._marker._icon&&M(this._marker._icon,"leaflet-marker-draggable")},moved:function(){return this._draggable&&this._draggable._moved},_adjustPan:function(t){var i=this._marker,e=i._map,n=this._marker.options.autoPanSpeed,o=this._marker.options.autoPanPadding,s=bi(i._icon),r=e.getPixelBounds(),a=e.getPixelOrigin(),a=f(r.min._subtract(a).add(o),r.max._subtract(a).subtract(o));a.contains(s)||(o=_((Math.max(a.max.x,s.x)-a.max.x)/(r.max.x-a.max.x)-(Math.min(a.min.x,s.x)-a.min.x)/(r.min.x-a.min.x),(Math.max(a.max.y,s.y)-a.max.y)/(r.max.y-a.max.y)-(Math.min(a.min.y,s.y)-a.min.y)/(r.min.y-a.min.y)).multiplyBy(n),e.panBy(o,{animate:!1}),this._draggable._newPos._add(o),this._draggable._startPos._add(o),Z(i._icon,this._draggable._newPos),this._onDrag(t),this._panRequest=x(this._adjustPan.bind(this,t)))},_onDragStart:function(){this._oldLatLng=this._marker.getLatLng(),this._marker.closePopup&&this._marker.closePopup(),this._marker.fire("movestart").fire("dragstart")},_onPreDrag:function(t){this._marker.options.autoPan&&(r(this._panRequest),this._panRequest=x(this._adjustPan.bind(this,t)))},_onDrag:function(t){var i=this._marker,e=i._shadow,n=bi(i._icon),o=i._map.layerPointToLatLng(n);e&&Z(e,n),i._latlng=o,t.latlng=o,t.oldLatLng=this._oldLatLng,i.fire("move",t).fire("drag",t)},_onDragEnd:function(t){r(this._panRequest),delete this._oldLatLng,this._marker.fire("moveend").fire("dragend",t)}}),de=o.extend({options:{icon:new ue,interactive:!0,keyboard:!0,title:"",alt:"Marker",zIndexOffset:0,opacity:1,riseOnHover:!1,riseOffset:250,pane:"markerPane",shadowPane:"shadowPane",bubblingMouseEvents:!1,autoPanOnFocus:!0,draggable:!1,autoPan:!1,autoPanPadding:[50,50],autoPanSpeed:10},initialize:function(t,i){c(this,i),this._latlng=w(t)},onAdd:function(t){this._zoomAnimated=this._zoomAnimated&&t.options.markerZoomAnimation,this._zoomAnimated&&t.on("zoomanim",this._animateZoom,this),this._initIcon(),this.update()},onRemove:function(t){this.dragging&&this.dragging.enabled()&&(this.options.draggable=!0,this.dragging.removeHooks()),delete this.dragging,this._zoomAnimated&&t.off("zoomanim",this._animateZoom,this),this._removeIcon(),this._removeShadow()},getEvents:function(){return{zoom:this.update,viewreset:this.update}},getLatLng:function(){return this._latlng},setLatLng:function(t){var i=this._latlng;return this._latlng=w(t),this.update(),this.fire("move",{oldLatLng:i,latlng:this._latlng})},setZIndexOffset:function(t){return this.options.zIndexOffset=t,this.update()},getIcon:function(){return this.options.icon},setIcon:function(t){return this.options.icon=t,this._map&&(this._initIcon(),this.update()),this._popup&&this.bindPopup(this._popup,this._popup.options),this},getElement:function(){return this._icon},update:function(){var t;return this._icon&&this._map&&(t=this._map.latLngToLayerPoint(this._latlng).round(),this._setPos(t)),this},_initIcon:function(){var t=this.options,i="leaflet-zoom-"+(this._zoomAnimated?"animated":"hide"),e=t.icon.createIcon(this._icon),n=!1,e=(e!==this._icon&&(this._icon&&this._removeIcon(),n=!0,t.title&&(e.title=t.title),"IMG"===e.tagName&&(e.alt=t.alt||"")),z(e,i),t.keyboard&&(e.tabIndex="0",e.setAttribute("role","button")),this._icon=e,t.riseOnHover&&this.on({mouseover:this._bringToFront,mouseout:this._resetZIndex}),this.options.autoPanOnFocus&&S(e,"focus",this._panOnFocus,this),t.icon.createShadow(this._shadow)),o=!1;e!==this._shadow&&(this._removeShadow(),o=!0),e&&(z(e,i),e.alt=""),this._shadow=e,t.opacity<1&&this._updateOpacity(),n&&this.getPane().appendChild(this._icon),this._initInteraction(),e&&o&&this.getPane(t.shadowPane).appendChild(this._shadow)},_removeIcon:function(){this.options.riseOnHover&&this.off({mouseover:this._bringToFront,mouseout:this._resetZIndex}),this.options.autoPanOnFocus&&E(this._icon,"focus",this._panOnFocus,this),T(this._icon),this.removeInteractiveTarget(this._icon),this._icon=null},_removeShadow:function(){this._shadow&&T(this._shadow),this._shadow=null},_setPos:function(t){this._icon&&Z(this._icon,t),this._shadow&&Z(this._shadow,t),this._zIndex=t.y+this.options.zIndexOffset,this._resetZIndex()},_updateZIndex:function(t){this._icon&&(this._icon.style.zIndex=this._zIndex+t)},_animateZoom:function(t){t=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center).round();this._setPos(t)},_initInteraction:function(){var t;this.options.interactive&&(z(this._icon,"leaflet-interactive"),this.addInteractiveTarget(this._icon),ce&&(t=this.options.draggable,this.dragging&&(t=this.dragging.enabled(),this.dragging.disable()),this.dragging=new ce(this),t&&this.dragging.enable()))},setOpacity:function(t){return this.options.opacity=t,this._map&&this._updateOpacity(),this},_updateOpacity:function(){var t=this.options.opacity;this._icon&&C(this._icon,t),this._shadow&&C(this._shadow,t)},_bringToFront:function(){this._updateZIndex(this.options.riseOffset)},_resetZIndex:function(){this._updateZIndex(0)},_panOnFocus:function(){var t,i,e=this._map;e&&(t=(i=this.options.icon.options).iconSize?_(i.iconSize):_(0,0),i=i.iconAnchor?_(i.iconAnchor):_(0,0),e.panInside(this._latlng,{paddingTopLeft:i,paddingBottomRight:t.subtract(i)}))},_getPopupAnchor:function(){return this.options.icon.options.popupAnchor},_getTooltipAnchor:function(){return this.options.icon.options.tooltipAnchor}});var _e=o.extend({options:{stroke:!0,color:"#3388ff",weight:3,opacity:1,lineCap:"round",lineJoin:"round",dashArray:null,dashOffset:null,fill:!1,fillColor:null,fillOpacity:.2,fillRule:"evenodd",interactive:!0,bubblingMouseEvents:!0},beforeAdd:function(t){this._renderer=t.getRenderer(this)},onAdd:function(){this._renderer._initPath(this),this._reset(),this._renderer._addPath(this)},onRemove:function(){this._renderer._removePath(this)},redraw:function(){return this._map&&this._renderer._updatePath(this),this},setStyle:function(t){return c(this,t),this._renderer&&(this._renderer._updateStyle(this),this.options.stroke&&t&&Object.prototype.hasOwnProperty.call(t,"weight")&&this._updateBounds()),this},bringToFront:function(){return this._renderer&&this._renderer._bringToFront(this),this},bringToBack:function(){return this._renderer&&this._renderer._bringToBack(this),this},getElement:function(){return this._path},_reset:function(){this._project(),this._update()},_clickTolerance:function(){return(this.options.stroke?this.options.weight/2:0)+(this._renderer.options.tolerance||0)}}),pe=_e.extend({options:{fill:!0,radius:10},initialize:function(t,i){c(this,i),this._latlng=w(t),this._radius=this.options.radius},setLatLng:function(t){var i=this._latlng;return this._latlng=w(t),this.redraw(),this.fire("move",{oldLatLng:i,latlng:this._latlng})},getLatLng:function(){return this._latlng},setRadius:function(t){return this.options.radius=this._radius=t,this.redraw()},getRadius:function(){return this._radius},setStyle:function(t){var i=t&&t.radius||this._radius;return _e.prototype.setStyle.call(this,t),this.setRadius(i),this},_project:function(){this._point=this._map.latLngToLayerPoint(this._latlng),this._updateBounds()},_updateBounds:function(){var t=this._radius,i=this._radiusY||t,e=this._clickTolerance(),t=[t+e,i+e];this._pxBounds=new m(this._point.subtract(t),this._point.add(t))},_update:function(){this._map&&this._updatePath()},_updatePath:function(){this._renderer._updateCircle(this)},_empty:function(){return this._radius&&!this._renderer._bounds.intersects(this._pxBounds)},_containsPoint:function(t){return t.distanceTo(this._point)<=this._radius+this._clickTolerance()}});var me=pe.extend({initialize:function(t,i,e){if(c(this,i="number"==typeof i?l({},e,{radius:i}):i),this._latlng=w(t),isNaN(this.options.radius))throw new Error("Circle radius cannot be NaN");this._mRadius=this.options.radius},setRadius:function(t){return this._mRadius=t,this.redraw()},getRadius:function(){return this._mRadius},getBounds:function(){var t=[this._radius,this._radiusY||this._radius];return new s(this._map.layerPointToLatLng(this._point.subtract(t)),this._map.layerPointToLatLng(this._point.add(t)))},setStyle:_e.prototype.setStyle,_project:function(){var t,i,e,n,o,s=this._latlng.lng,r=this._latlng.lat,a=this._map,h=a.options.crs;h.distance===st.distance?(n=Math.PI/180,o=this._mRadius/st.R/n,t=a.project([r+o,s]),i=a.project([r-o,s]),i=t.add(i).divideBy(2),e=a.unproject(i).lat,n=Math.acos((Math.cos(o*n)-Math.sin(r*n)*Math.sin(e*n))/(Math.cos(r*n)*Math.cos(e*n)))/n,!isNaN(n)&&0!==n||(n=o/Math.cos(Math.PI/180*r)),this._point=i.subtract(a.getPixelOrigin()),this._radius=isNaN(n)?0:i.x-a.project([e,s-n]).x,this._radiusY=i.y-t.y):(o=h.unproject(h.project(this._latlng).subtract([this._mRadius,0])),this._point=a.latLngToLayerPoint(this._latlng),this._radius=this._point.x-a.latLngToLayerPoint(o).x),this._updateBounds()}});var fe=_e.extend({options:{smoothFactor:1,noClip:!1},initialize:function(t,i){c(this,i),this._setLatLngs(t)},getLatLngs:function(){return this._latlngs},setLatLngs:function(t){return this._setLatLngs(t),this.redraw()},isEmpty:function(){return!this._latlngs.length},closestLayerPoint:function(t){for(var i=1/0,e=null,n=ee,o=0,s=this._parts.length;o<s;o++)for(var r=this._parts[o],a=1,h=r.length;a<h;a++){var l,u,c=n(t,l=r[a-1],u=r[a],!0);c<i&&(i=c,e=n(t,l,u))}return e&&(e.distance=Math.sqrt(i)),e},getCenter:function(){if(!this._map)throw new Error("Must add layer to map before using getCenter()");var t,i,e,n,o,s,r=this._rings[0],a=r.length;if(!a)return null;for(i=t=0;t<a-1;t++)i+=r[t].distanceTo(r[t+1])/2;if(0===i)return this._map.layerPointToLatLng(r[0]);for(e=t=0;t<a-1;t++)if(n=r[t],o=r[t+1],i<(e+=s=n.distanceTo(o)))return this._map.layerPointToLatLng([o.x-(s=(e-i)/s)*(o.x-n.x),o.y-s*(o.y-n.y)])},getBounds:function(){return this._bounds},addLatLng:function(t,i){return i=i||this._defaultShape(),t=w(t),i.push(t),this._bounds.extend(t),this.redraw()},_setLatLngs:function(t){this._bounds=new s,this._latlngs=this._convertLatLngs(t)},_defaultShape:function(){return ne(this._latlngs)?this._latlngs:this._latlngs[0]},_convertLatLngs:function(t){for(var i=[],e=ne(t),n=0,o=t.length;n<o;n++)e?(i[n]=w(t[n]),this._bounds.extend(i[n])):i[n]=this._convertLatLngs(t[n]);return i},_project:function(){var t=new m;this._rings=[],this._projectLatlngs(this._latlngs,this._rings,t),this._bounds.isValid()&&t.isValid()&&(this._rawPxBounds=t,this._updateBounds())},_updateBounds:function(){var t=this._clickTolerance(),t=new p(t,t);this._rawPxBounds&&(this._pxBounds=new m([this._rawPxBounds.min.subtract(t),this._rawPxBounds.max.add(t)]))},_projectLatlngs:function(t,i,e){var n,o,s=t[0]instanceof v,r=t.length;if(s){for(o=[],n=0;n<r;n++)o[n]=this._map.latLngToLayerPoint(t[n]),e.extend(o[n]);i.push(o)}else for(n=0;n<r;n++)this._projectLatlngs(t[n],i,e)},_clipPoints:function(){var t=this._renderer._bounds;if(this._parts=[],this._pxBounds&&this._pxBounds.intersects(t))if(this.options.noClip)this._parts=this._rings;else for(var i,e,n,o,s=this._parts,r=0,a=0,h=this._rings.length;r<h;r++)for(i=0,e=(o=this._rings[r]).length;i<e-1;i++)(n=Qi(o[i],o[i+1],t,i,!0))&&(s[a]=s[a]||[],s[a].push(n[0]),n[1]===o[i+1]&&i!==e-2||(s[a].push(n[1]),a++))},_simplifyPoints:function(){for(var t=this._parts,i=this.options.smoothFactor,e=0,n=t.length;e<n;e++)t[e]=Ji(t[e],i)},_update:function(){this._map&&(this._clipPoints(),this._simplifyPoints(),this._updatePath())},_updatePath:function(){this._renderer._updatePoly(this)},_containsPoint:function(t,i){var e,n,o,s,r,a,h=this._clickTolerance();if(!this._pxBounds||!this._pxBounds.contains(t))return!1;for(e=0,s=this._parts.length;e<s;e++)for(n=0,o=(r=(a=this._parts[e]).length)-1;n<r;o=n++)if((i||0!==n)&&$i(t,a[o],a[n])<=h)return!0;return!1}});fe._flat=oe;var ge=fe.extend({options:{fill:!0},isEmpty:function(){return!this._latlngs.length||!this._latlngs[0].length},getCenter:function(){if(!this._map)throw new Error("Must add layer to map before using getCenter()");var t,i,e,n,o,s,r,a,h,l=this._rings[0],u=l.length;if(!u)return null;for(t=s=r=a=0,i=u-1;t<u;i=t++)e=l[t],n=l[i],o=e.y*n.x-n.y*e.x,r+=(e.x+n.x)*o,a+=(e.y+n.y)*o,s+=3*o;return h=0===s?l[0]:[r/s,a/s],this._map.layerPointToLatLng(h)},_convertLatLngs:function(t){var t=fe.prototype._convertLatLngs.call(this,t),i=t.length;return 2<=i&&t[0]instanceof v&&t[0].equals(t[i-1])&&t.pop(),t},_setLatLngs:function(t){fe.prototype._setLatLngs.call(this,t),ne(this._latlngs)&&(this._latlngs=[this._latlngs])},_defaultShape:function(){return(ne(this._latlngs[0])?this._latlngs:this._latlngs[0])[0]},_clipPoints:function(){var t=this._renderer._bounds,i=this.options.weight,i=new p(i,i),t=new m(t.min.subtract(i),t.max.add(i));if(this._parts=[],this._pxBounds&&this._pxBounds.intersects(t))if(this.options.noClip)this._parts=this._rings;else for(var e,n=0,o=this._rings.length;n<o;n++)(e=se(this._rings[n],t,!0)).length&&this._parts.push(e)},_updatePath:function(){this._renderer._updatePoly(this,!0)},_containsPoint:function(t){var i,e,n,o,s,r,a,h,l=!1;if(!this._pxBounds||!this._pxBounds.contains(t))return!1;for(o=0,a=this._parts.length;o<a;o++)for(s=0,r=(h=(i=this._parts[o]).length)-1;s<h;r=s++)e=i[s],n=i[r],e.y>t.y!=n.y>t.y&&t.x<(n.x-e.x)*(t.y-e.y)/(n.y-e.y)+e.x&&(l=!l);return l||fe.prototype._containsPoint.call(this,t,!0)}});var ve=he.extend({initialize:function(t,i){c(this,i),this._layers={},t&&this.addData(t)},addData:function(t){var i,e,n,o=d(t)?t:t.features;if(o){for(i=0,e=o.length;i<e;i++)((n=o[i]).geometries||n.geometry||n.features||n.coordinates)&&this.addData(n);return this}var s=this.options;if(s.filter&&!s.filter(t))return this;var r=ye(t,s);return r?(r.feature=ze(t),r.defaultOptions=r.options,this.resetStyle(r),s.onEachFeature&&s.onEachFeature(t,r),this.addLayer(r)):this},resetStyle:function(t){return void 0===t?this.eachLayer(this.resetStyle,this):(t.options=l({},t.defaultOptions),this._setLayerStyle(t,this.options.style),this)},setStyle:function(i){return this.eachLayer(function(t){this._setLayerStyle(t,i)},this)},_setLayerStyle:function(t,i){t.setStyle&&("function"==typeof i&&(i=i(t.feature)),t.setStyle(i))}});function ye(t,i){var e,n,o,s,r="Feature"===t.type?t.geometry:t,a=r?r.coordinates:null,h=[],l=i&&i.pointToLayer,u=i&&i.coordsToLatLng||we;if(!a&&!r)return null;switch(r.type){case"Point":return xe(l,t,e=u(a),i);case"MultiPoint":for(o=0,s=a.length;o<s;o++)e=u(a[o]),h.push(xe(l,t,e,i));return new he(h);case"LineString":case"MultiLineString":return n=Pe(a,"LineString"===r.type?0:1,u),new fe(n,i);case"Polygon":case"MultiPolygon":return n=Pe(a,"Polygon"===r.type?1:2,u),new ge(n,i);case"GeometryCollection":for(o=0,s=r.geometries.length;o<s;o++){var c=ye({geometry:r.geometries[o],type:"Feature",properties:t.properties},i);c&&h.push(c)}return new he(h);default:throw new Error("Invalid GeoJSON object.")}}function xe(t,i,e,n){return t?t(i,e):new de(e,n&&n.markersInheritOptions&&n)}function we(t){return new v(t[1],t[0],t[2])}function Pe(t,i,e){for(var n,o=[],s=0,r=t.length;s<r;s++)n=i?Pe(t[s],i-1,e):(e||we)(t[s]),o.push(n);return o}function be(t,i){return void 0!==(t=w(t)).alt?[e(t.lng,i),e(t.lat,i),e(t.alt,i)]:[e(t.lng,i),e(t.lat,i)]}function Le(t,i,e,n){for(var o=[],s=0,r=t.length;s<r;s++)o.push(i?Le(t[s],i-1,e,n):be(t[s],n));return!i&&e&&o.push(o[0]),o}function Te(t,i){return t.feature?l({},t.feature,{geometry:i}):ze(i)}function ze(t){return"Feature"===t.type||"FeatureCollection"===t.type?t:{type:"Feature",properties:{},geometry:t}}Tt={toGeoJSON:function(t){return Te(this,{type:"Point",coordinates:be(this.getLatLng(),t)})}};function Me(t,i){return new ve(t,i)}de.include(Tt),me.include(Tt),pe.include(Tt),fe.include({toGeoJSON:function(t){var i=!ne(this._latlngs);return Te(this,{type:(i?"Multi":"")+"LineString",coordinates:Le(this._latlngs,i?1:0,!1,t)})}}),ge.include({toGeoJSON:function(t){var i=!ne(this._latlngs),e=i&&!ne(this._latlngs[0]),t=Le(this._latlngs,e?2:i?1:0,!0,t);return Te(this,{type:(e?"Multi":"")+"Polygon",coordinates:t=i?t:[t]})}}),ae.include({toMultiPoint:function(i){var e=[];return this.eachLayer(function(t){e.push(t.toGeoJSON(i).geometry.coordinates)}),Te(this,{type:"MultiPoint",coordinates:e})},toGeoJSON:function(i){var t=this.feature&&this.feature.geometry&&this.feature.geometry.type;if("MultiPoint"===t)return this.toMultiPoint(i);var e="GeometryCollection"===t,n=[];return this.eachLayer(function(t){t.toGeoJSON&&(t=t.toGeoJSON(i),e?n.push(t.geometry):"FeatureCollection"===(t=ze(t)).type?n.push.apply(n,t.features):n.push(t))}),e?Te(this,{geometries:n,type:"GeometryCollection"}):{type:"FeatureCollection",features:n}}});var zt=Me,Ce=o.extend({options:{opacity:1,alt:"",interactive:!1,crossOrigin:!1,errorOverlayUrl:"",zIndex:1,className:""},initialize:function(t,i,e){this._url=t,this._bounds=g(i),c(this,e)},onAdd:function(){this._image||(this._initImage(),this.options.opacity<1&&this._updateOpacity()),this.options.interactive&&(z(this._image,"leaflet-interactive"),this.addInteractiveTarget(this._image)),this.getPane().appendChild(this._image),this._reset()},onRemove:function(){T(this._image),this.options.interactive&&this.removeInteractiveTarget(this._image)},setOpacity:function(t){return this.options.opacity=t,this._image&&this._updateOpacity(),this},setStyle:function(t){return t.opacity&&this.setOpacity(t.opacity),this},bringToFront:function(){return this._map&&fi(this._image),this},bringToBack:function(){return this._map&&gi(this._image),this},setUrl:function(t){return this._url=t,this._image&&(this._image.src=t),this},setBounds:function(t){return this._bounds=g(t),this._map&&this._reset(),this},getEvents:function(){var t={zoom:this._reset,viewreset:this._reset};return this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},setZIndex:function(t){return this.options.zIndex=t,this._updateZIndex(),this},getBounds:function(){return this._bounds},getElement:function(){return this._image},_initImage:function(){var t="IMG"===this._url.tagName,i=this._image=t?this._url:b("img");z(i,"leaflet-image-layer"),this._zoomAnimated&&z(i,"leaflet-zoom-animated"),this.options.className&&z(i,this.options.className),i.onselectstart=u,i.onmousemove=u,i.onload=a(this.fire,this,"load"),i.onerror=a(this._overlayOnError,this,"error"),!this.options.crossOrigin&&""!==this.options.crossOrigin||(i.crossOrigin=!0===this.options.crossOrigin?"":this.options.crossOrigin),this.options.zIndex&&this._updateZIndex(),t?this._url=i.src:(i.src=this._url,i.alt=this.options.alt)},_animateZoom:function(t){var i=this._map.getZoomScale(t.zoom),t=this._map._latLngBoundsToNewLayerBounds(this._bounds,t.zoom,t.center).min;Pi(this._image,t,i)},_reset:function(){var t=this._image,i=new m(this._map.latLngToLayerPoint(this._bounds.getNorthWest()),this._map.latLngToLayerPoint(this._bounds.getSouthEast())),e=i.getSize();Z(t,i.min),t.style.width=e.x+"px",t.style.height=e.y+"px"},_updateOpacity:function(){C(this._image,this.options.opacity)},_updateZIndex:function(){this._image&&void 0!==this.options.zIndex&&null!==this.options.zIndex&&(this._image.style.zIndex=this.options.zIndex)},_overlayOnError:function(){this.fire("error");var t=this.options.errorOverlayUrl;t&&this._url!==t&&(this._url=t,this._image.src=t)},getCenter:function(){return this._bounds.getCenter()}}),Ze=Ce.extend({options:{autoplay:!0,loop:!0,keepAspectRatio:!0,muted:!1,playsInline:!0},_initImage:function(){var t="VIDEO"===this._url.tagName,i=this._image=t?this._url:b("video");if(z(i,"leaflet-image-layer"),this._zoomAnimated&&z(i,"leaflet-zoom-animated"),this.options.className&&z(i,this.options.className),i.onselectstart=u,i.onmousemove=u,i.onloadeddata=a(this.fire,this,"load"),t){for(var e=i.getElementsByTagName("source"),n=[],o=0;o<e.length;o++)n.push(e[o].src);this._url=0<e.length?n:[i.src]}else{d(this._url)||(this._url=[this._url]),!this.options.keepAspectRatio&&Object.prototype.hasOwnProperty.call(i.style,"objectFit")&&(i.style.objectFit="fill"),i.autoplay=!!this.options.autoplay,i.loop=!!this.options.loop,i.muted=!!this.options.muted,i.playsInline=!!this.options.playsInline;for(var s=0;s<this._url.length;s++){var r=b("source");r.src=this._url[s],i.appendChild(r)}}}});var Se=Ce.extend({_initImage:function(){var t=this._image=this._url;z(t,"leaflet-image-layer"),this._zoomAnimated&&z(t,"leaflet-zoom-animated"),this.options.className&&z(t,this.options.className),t.onselectstart=u,t.onmousemove=u}});var O=o.extend({options:{interactive:!1,offset:[0,0],className:"",pane:void 0},initialize:function(t,i){c(this,t),this._source=i},openOn:function(t){return(t=arguments.length?t:this._source._map).hasLayer(this)||t.addLayer(this),this},close:function(){return this._map&&this._map.removeLayer(this),this},toggle:function(t){return this._map?this.close():(arguments.length?this._source=t:t=this._source,this._prepareOpen(),this.openOn(t._map)),this},onAdd:function(t){this._zoomAnimated=t._zoomAnimated,this._container||this._initLayout(),t._fadeAnimated&&C(this._container,0),clearTimeout(this._removeTimeout),this.getPane().appendChild(this._container),this.update(),t._fadeAnimated&&C(this._container,1),this.bringToFront(),this.options.interactive&&(z(this._container,"leaflet-interactive"),this.addInteractiveTarget(this._container))},onRemove:function(t){t._fadeAnimated?(C(this._container,0),this._removeTimeout=setTimeout(a(T,void 0,this._container),200)):T(this._container),this.options.interactive&&(M(this._container,"leaflet-interactive"),this.removeInteractiveTarget(this._container))},getLatLng:function(){return this._latlng},setLatLng:function(t){return this._latlng=w(t),this._map&&(this._updatePosition(),this._adjustPan()),this},getContent:function(){return this._content},setContent:function(t){return this._content=t,this.update(),this},getElement:function(){return this._container},update:function(){this._map&&(this._container.style.visibility="hidden",this._updateContent(),this._updateLayout(),this._updatePosition(),this._container.style.visibility="",this._adjustPan())},getEvents:function(){var t={zoom:this._updatePosition,viewreset:this._updatePosition};return this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},isOpen:function(){return!!this._map&&this._map.hasLayer(this)},bringToFront:function(){return this._map&&fi(this._container),this},bringToBack:function(){return this._map&&gi(this._container),this},_prepareOpen:function(t){if(!(e=this._source)._map)return!1;if(e instanceof he){var i,e=null,n=this._source._layers;for(i in n)if(n[i]._map){e=n[i];break}if(!e)return!1;this._source=e}if(!t)if(e.getCenter)t=e.getCenter();else if(e.getLatLng)t=e.getLatLng();else{if(!e.getBounds)throw new Error("Unable to get source layer LatLng.");t=e.getBounds().getCenter()}return this.setLatLng(t),this._map&&this.update(),!0},_updateContent:function(){if(this._content){var t=this._contentNode,i="function"==typeof this._content?this._content(this._source||this):this._content;if("string"==typeof i)t.innerHTML=i;else{for(;t.hasChildNodes();)t.removeChild(t.firstChild);t.appendChild(i)}this.fire("contentupdate")}},_updatePosition:function(){var t,i,e;this._map&&(i=this._map.latLngToLayerPoint(this._latlng),t=_(this.options.offset),e=this._getAnchor(),this._zoomAnimated?Z(this._container,i.add(e)):t=t.add(i).add(e),i=this._containerBottom=-t.y,e=this._containerLeft=-Math.round(this._containerWidth/2)+t.x,this._container.style.bottom=i+"px",this._container.style.left=e+"px")},_getAnchor:function(){return[0,0]}}),ke=(A.include({_initOverlay:function(t,i,e,n){var o=i;return o instanceof t||(o=new t(n).setContent(i)),e&&o.setLatLng(e),o}}),o.include({_initOverlay:function(t,i,e,n){var o=e;return o instanceof t?(c(o,n),o._source=this):(o=i&&!n?i:new t(n,this)).setContent(e),o}}),O.extend({options:{pane:"popupPane",offset:[0,7],maxWidth:300,minWidth:50,maxHeight:null,autoPan:!0,autoPanPaddingTopLeft:null,autoPanPaddingBottomRight:null,autoPanPadding:[5,5],keepInView:!1,closeButton:!0,autoClose:!0,closeOnEscapeKey:!0,className:""},openOn:function(t){return!(t=arguments.length?t:this._source._map).hasLayer(this)&&t._popup&&t._popup.options.autoClose&&t.removeLayer(t._popup),t._popup=this,O.prototype.openOn.call(this,t)},onAdd:function(t){O.prototype.onAdd.call(this,t),t.fire("popupopen",{popup:this}),this._source&&(this._source.fire("popupopen",{popup:this},!0),this._source instanceof _e||this._source.on("preclick",Ai))},onRemove:function(t){O.prototype.onRemove.call(this,t),t.fire("popupclose",{popup:this}),this._source&&(this._source.fire("popupclose",{popup:this},!0),this._source instanceof _e||this._source.off("preclick",Ai))},getEvents:function(){var t=O.prototype.getEvents.call(this);return(void 0!==this.options.closeOnClick?this.options.closeOnClick:this._map.options.closePopupOnClick)&&(t.preclick=this.close),this.options.keepInView&&(t.moveend=this._adjustPan),t},_initLayout:function(){var t="leaflet-popup",i=this._container=b("div",t+" "+(this.options.className||"")+" leaflet-zoom-animated"),e=this._wrapper=b("div",t+"-content-wrapper",i);this._contentNode=b("div",t+"-content",e),Oi(i),Ii(this._contentNode),S(i,"contextmenu",Ai),this._tipContainer=b("div",t+"-tip-container",i),this._tip=b("div",t+"-tip",this._tipContainer),this.options.closeButton&&((e=this._closeButton=b("a",t+"-close-button",i)).setAttribute("role","button"),e.setAttribute("aria-label","Close popup"),e.href="#close",e.innerHTML='<span aria-hidden="true">&#215;</span>',S(e,"click",this.close,this))},_updateLayout:function(){var t=this._contentNode,i=t.style,e=(i.width="",i.whiteSpace="nowrap",t.offsetWidth),e=Math.min(e,this.options.maxWidth),e=(e=Math.max(e,this.options.minWidth),i.width=e+1+"px",i.whiteSpace="",i.height="",t.offsetHeight),n=this.options.maxHeight,o="leaflet-popup-scrolled";n&&n<e?(i.height=n+"px",z(t,o)):M(t,o),this._containerWidth=this._container.offsetWidth},_animateZoom:function(t){var t=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center),i=this._getAnchor();Z(this._container,t.add(i))},_adjustPan:function(t){var i,e,n,o,s,r,a,h;this.options.autoPan&&(this._map._panAnim&&this._map._panAnim.stop(),i=this._map,e=parseInt(pi(this._container,"marginBottom"),10)||0,e=this._container.offsetHeight+e,h=this._containerWidth,(n=new p(this._containerLeft,-e-this._containerBottom))._add(bi(this._container)),n=i.layerPointToContainerPoint(n),s=_(this.options.autoPanPadding),o=_(this.options.autoPanPaddingTopLeft||s),s=_(this.options.autoPanPaddingBottomRight||s),r=i.getSize(),a=0,n.x+h+s.x>r.x&&(a=n.x+h-r.x+s.x),n.x-a-o.x<(h=0)&&(a=n.x-o.x),n.y+e+s.y>r.y&&(h=n.y+e-r.y+s.y),n.y-h-o.y<0&&(h=n.y-o.y),(a||h)&&i.fire("autopanstart").panBy([a,h],{animate:t&&"moveend"===t.type}))},_getAnchor:function(){return _(this._source&&this._source._getPopupAnchor?this._source._getPopupAnchor():[0,0])}})),Ee=(A.mergeOptions({closePopupOnClick:!0}),A.include({openPopup:function(t,i,e){return this._initOverlay(ke,t,i,e).openOn(this),this},closePopup:function(t){return(t=arguments.length?t:this._popup)&&t.close(),this}}),o.include({bindPopup:function(t,i){return this._popup=this._initOverlay(ke,this._popup,t,i),this._popupHandlersAdded||(this.on({click:this._openPopup,keypress:this._onKeyPress,remove:this.closePopup,move:this._movePopup}),this._popupHandlersAdded=!0),this},unbindPopup:function(){return this._popup&&(this.off({click:this._openPopup,keypress:this._onKeyPress,remove:this.closePopup,move:this._movePopup}),this._popupHandlersAdded=!1,this._popup=null),this},openPopup:function(t){return this._popup&&this._popup._prepareOpen(t)&&this._popup.openOn(this._map),this},closePopup:function(){return this._popup&&this._popup.close(),this},togglePopup:function(){return this._popup&&this._popup.toggle(this),this},isPopupOpen:function(){return!!this._popup&&this._popup.isOpen()},setPopupContent:function(t){return this._popup&&this._popup.setContent(t),this},getPopup:function(){return this._popup},_openPopup:function(t){var i;this._popup&&this._map&&(Ri(t),i=t.layer||t.target,this._popup._source!==i||i instanceof _e?(this._popup._source=i,this.openPopup(t.latlng)):this._map.hasLayer(this._popup)?this.closePopup():this.openPopup(t.latlng))},_movePopup:function(t){this._popup.setLatLng(t.latlng)},_onKeyPress:function(t){13===t.originalEvent.keyCode&&this._openPopup(t)}}),O.extend({options:{pane:"tooltipPane",offset:[0,0],direction:"auto",permanent:!1,sticky:!1,opacity:.9},onAdd:function(t){O.prototype.onAdd.call(this,t),this.setOpacity(this.options.opacity),t.fire("tooltipopen",{tooltip:this}),this._source&&(this.addEventParent(this._source),this._source.fire("tooltipopen",{tooltip:this},!0))},onRemove:function(t){O.prototype.onRemove.call(this,t),t.fire("tooltipclose",{tooltip:this}),this._source&&(this.removeEventParent(this._source),this._source.fire("tooltipclose",{tooltip:this},!0))},getEvents:function(){var t=O.prototype.getEvents.call(this);return this.options.permanent||(t.preclick=this.close),t},_initLayout:function(){var t="leaflet-tooltip "+(this.options.className||"")+" leaflet-zoom-"+(this._zoomAnimated?"animated":"hide");this._contentNode=this._container=b("div",t)},_updateLayout:function(){},_adjustPan:function(){},_setPosition:function(t){var i,e=this._map,n=this._container,o=e.latLngToContainerPoint(e.getCenter()),e=e.layerPointToContainerPoint(t),s=this.options.direction,r=n.offsetWidth,a=n.offsetHeight,h=_(this.options.offset),l=this._getAnchor(),e="top"===s?(i=r/2,a):"bottom"===s?(i=r/2,0):(i="center"===s?r/2:"right"===s?0:"left"===s?r:e.x<o.x?(s="right",0):(s="left",r+2*(h.x+l.x)),a/2);t=t.subtract(_(i,e,!0)).add(h).add(l),M(n,"leaflet-tooltip-right"),M(n,"leaflet-tooltip-left"),M(n,"leaflet-tooltip-top"),M(n,"leaflet-tooltip-bottom"),z(n,"leaflet-tooltip-"+s),Z(n,t)},_updatePosition:function(){var t=this._map.latLngToLayerPoint(this._latlng);this._setPosition(t)},setOpacity:function(t){this.options.opacity=t,this._container&&C(this._container,t)},_animateZoom:function(t){t=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center);this._setPosition(t)},_getAnchor:function(){return _(this._source&&this._source._getTooltipAnchor&&!this.options.sticky?this._source._getTooltipAnchor():[0,0])}})),Be=(A.include({openTooltip:function(t,i,e){return this._initOverlay(Ee,t,i,e).openOn(this),this},closeTooltip:function(t){return t.close(),this}}),o.include({bindTooltip:function(t,i){return this._tooltip&&this.isTooltipOpen()&&this.unbindTooltip(),this._tooltip=this._initOverlay(Ee,this._tooltip,t,i),this._initTooltipInteractions(),this._tooltip.options.permanent&&this._map&&this._map.hasLayer(this)&&this.openTooltip(),this},unbindTooltip:function(){return this._tooltip&&(this._initTooltipInteractions(!0),this.closeTooltip(),this._tooltip=null),this},_initTooltipInteractions:function(t){var i,e;!t&&this._tooltipHandlersAdded||(i=t?"off":"on",e={remove:this.closeTooltip,move:this._moveTooltip},this._tooltip.options.permanent?e.add=this._openTooltip:(e.mouseover=this._openTooltip,e.mouseout=this.closeTooltip,e.click=this._openTooltip),this._tooltip.options.sticky&&(e.mousemove=this._moveTooltip),this[i](e),this._tooltipHandlersAdded=!t)},openTooltip:function(t){return this._tooltip&&this._tooltip._prepareOpen(t)&&this._tooltip.openOn(this._map),this},closeTooltip:function(){if(this._tooltip)return this._tooltip.close()},toggleTooltip:function(){return this._tooltip&&this._tooltip.toggle(this),this},isTooltipOpen:function(){return this._tooltip.isOpen()},setTooltipContent:function(t){return this._tooltip&&this._tooltip.setContent(t),this},getTooltip:function(){return this._tooltip},_openTooltip:function(t){!this._tooltip||!this._map||this._map.dragging&&this._map.dragging.moving()||(this._tooltip._source=t.layer||t.target,this.openTooltip(this._tooltip.options.sticky?t.latlng:void 0))},_moveTooltip:function(t){var i=t.latlng;this._tooltip.options.sticky&&t.originalEvent&&(t=this._map.mouseEventToContainerPoint(t.originalEvent),t=this._map.containerPointToLayerPoint(t),i=this._map.layerPointToLatLng(t)),this._tooltip.setLatLng(i)}}),le.extend({options:{iconSize:[12,12],html:!1,bgPos:null,className:"leaflet-div-icon"},createIcon:function(t){var t=t&&"DIV"===t.tagName?t:document.createElement("div"),i=this.options;return i.html instanceof Element?(mi(t),t.appendChild(i.html)):t.innerHTML=!1!==i.html?i.html:"",i.bgPos&&(i=_(i.bgPos),t.style.backgroundPosition=-i.x+"px "+-i.y+"px"),this._setIconStyles(t,"icon"),t},createShadow:function(){return null}}));le.Default=ue;var Ae=o.extend({options:{tileSize:256,opacity:1,updateWhenIdle:P.mobile,updateWhenZooming:!0,updateInterval:200,zIndex:1,bounds:null,minZoom:0,maxZoom:void 0,maxNativeZoom:void 0,minNativeZoom:void 0,noWrap:!1,pane:"tilePane",className:"",keepBuffer:2},initialize:function(t){c(this,t)},onAdd:function(){this._initContainer(),this._levels={},this._tiles={},this._resetView()},beforeAdd:function(t){t._addZoomLimit(this)},onRemove:function(t){this._removeAllTiles(),T(this._container),t._removeZoomLimit(this),this._container=null,this._tileZoom=void 0},bringToFront:function(){return this._map&&(fi(this._container),this._setAutoZIndex(Math.max)),this},bringToBack:function(){return this._map&&(gi(this._container),this._setAutoZIndex(Math.min)),this},getContainer:function(){return this._container},setOpacity:function(t){return this.options.opacity=t,this._updateOpacity(),this},setZIndex:function(t){return this.options.zIndex=t,this._updateZIndex(),this},isLoading:function(){return this._loading},redraw:function(){var t;return this._map&&(this._removeAllTiles(),(t=this._clampZoom(this._map.getZoom()))!==this._tileZoom&&(this._tileZoom=t,this._updateLevels()),this._update()),this},getEvents:function(){var t={viewprereset:this._invalidateAll,viewreset:this._resetView,zoom:this._resetView,moveend:this._onMoveEnd};return this.options.updateWhenIdle||(this._onMove||(this._onMove=j(this._onMoveEnd,this.options.updateInterval,this)),t.move=this._onMove),this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},createTile:function(){return document.createElement("div")},getTileSize:function(){var t=this.options.tileSize;return t instanceof p?t:new p(t,t)},_updateZIndex:function(){this._container&&void 0!==this.options.zIndex&&null!==this.options.zIndex&&(this._container.style.zIndex=this.options.zIndex)},_setAutoZIndex:function(t){for(var i,e=this.getPane().children,n=-t(-1/0,1/0),o=0,s=e.length;o<s;o++)i=e[o].style.zIndex,e[o]!==this._container&&i&&(n=t(n,+i));isFinite(n)&&(this.options.zIndex=n+t(-1,1),this._updateZIndex())},_updateOpacity:function(){if(this._map&&!P.ielt9){C(this._container,this.options.opacity);var t,i=+new Date,e=!1,n=!1;for(t in this._tiles){var o,s=this._tiles[t];s.current&&s.loaded&&(o=Math.min(1,(i-s.loaded)/200),C(s.el,o),o<1?e=!0:(s.active?n=!0:this._onOpaqueTile(s),s.active=!0))}n&&!this._noPrune&&this._pruneTiles(),e&&(r(this._fadeFrame),this._fadeFrame=x(this._updateOpacity,this))}},_onOpaqueTile:u,_initContainer:function(){this._container||(this._container=b("div","leaflet-layer "+(this.options.className||"")),this._updateZIndex(),this.options.opacity<1&&this._updateOpacity(),this.getPane().appendChild(this._container))},_updateLevels:function(){var t=this._tileZoom,i=this.options.maxZoom;if(void 0!==t){for(var e in this._levels)e=Number(e),this._levels[e].el.children.length||e===t?(this._levels[e].el.style.zIndex=i-Math.abs(t-e),this._onUpdateLevel(e)):(T(this._levels[e].el),this._removeTilesAtZoom(e),this._onRemoveLevel(e),delete this._levels[e]);var n=this._levels[t],o=this._map;return n||((n=this._levels[t]={}).el=b("div","leaflet-tile-container leaflet-zoom-animated",this._container),n.el.style.zIndex=i,n.origin=o.project(o.unproject(o.getPixelOrigin()),t).round(),n.zoom=t,this._setZoomTransform(n,o.getCenter(),o.getZoom()),u(n.el.offsetWidth),this._onCreateLevel(n)),this._level=n}},_onUpdateLevel:u,_onRemoveLevel:u,_onCreateLevel:u,_pruneTiles:function(){if(this._map){var t,i,e,n=this._map.getZoom();if(n>this.options.maxZoom||n<this.options.minZoom)this._removeAllTiles();else{for(t in this._tiles)(e=this._tiles[t]).retain=e.current;for(t in this._tiles)(e=this._tiles[t]).current&&!e.active&&(i=e.coords,this._retainParent(i.x,i.y,i.z,i.z-5)||this._retainChildren(i.x,i.y,i.z,i.z+2));for(t in this._tiles)this._tiles[t].retain||this._removeTile(t)}}},_removeTilesAtZoom:function(t){for(var i in this._tiles)this._tiles[i].coords.z===t&&this._removeTile(i)},_removeAllTiles:function(){for(var t in this._tiles)this._removeTile(t)},_invalidateAll:function(){for(var t in this._levels)T(this._levels[t].el),this._onRemoveLevel(Number(t)),delete this._levels[t];this._removeAllTiles(),this._tileZoom=void 0},_retainParent:function(t,i,e,n){var t=Math.floor(t/2),i=Math.floor(i/2),e=e-1,o=new p(+t,+i),o=(o.z=e,this._tileCoordsToKey(o)),o=this._tiles[o];return o&&o.active?o.retain=!0:(o&&o.loaded&&(o.retain=!0),n<e&&this._retainParent(t,i,e,n))},_retainChildren:function(t,i,e,n){for(var o=2*t;o<2*t+2;o++)for(var s=2*i;s<2*i+2;s++){var r=new p(o,s),r=(r.z=e+1,this._tileCoordsToKey(r)),r=this._tiles[r];r&&r.active?r.retain=!0:(r&&r.loaded&&(r.retain=!0),e+1<n&&this._retainChildren(o,s,e+1,n))}},_resetView:function(t){t=t&&(t.pinch||t.flyTo);this._setView(this._map.getCenter(),this._map.getZoom(),t,t)},_animateZoom:function(t){this._setView(t.center,t.zoom,!0,t.noUpdate)},_clampZoom:function(t){var i=this.options;return void 0!==i.minNativeZoom&&t<i.minNativeZoom?i.minNativeZoom:void 0!==i.maxNativeZoom&&i.maxNativeZoom<t?i.maxNativeZoom:t},_setView:function(t,i,e,n){var o=Math.round(i),o=void 0!==this.options.maxZoom&&o>this.options.maxZoom||void 0!==this.options.minZoom&&o<this.options.minZoom?void 0:this._clampZoom(o),s=this.options.updateWhenZooming&&o!==this._tileZoom;n&&!s||(this._tileZoom=o,this._abortLoading&&this._abortLoading(),this._updateLevels(),this._resetGrid(),void 0!==o&&this._update(t),e||this._pruneTiles(),this._noPrune=!!e),this._setZoomTransforms(t,i)},_setZoomTransforms:function(t,i){for(var e in this._levels)this._setZoomTransform(this._levels[e],t,i)},_setZoomTransform:function(t,i,e){var n=this._map.getZoomScale(e,t.zoom),i=t.origin.multiplyBy(n).subtract(this._map._getNewPixelOrigin(i,e)).round();P.any3d?Pi(t.el,i,n):Z(t.el,i)},_resetGrid:function(){var t=this._map,i=t.options.crs,e=this._tileSize=this.getTileSize(),n=this._tileZoom,o=this._map.getPixelWorldBounds(this._tileZoom);o&&(this._globalTileRange=this._pxBoundsToTileRange(o)),this._wrapX=i.wrapLng&&!this.options.noWrap&&[Math.floor(t.project([0,i.wrapLng[0]],n).x/e.x),Math.ceil(t.project([0,i.wrapLng[1]],n).x/e.y)],this._wrapY=i.wrapLat&&!this.options.noWrap&&[Math.floor(t.project([i.wrapLat[0],0],n).y/e.x),Math.ceil(t.project([i.wrapLat[1],0],n).y/e.y)]},_onMoveEnd:function(){this._map&&!this._map._animatingZoom&&this._update()},_getTiledPixelBounds:function(t){var i=this._map,e=i._animatingZoom?Math.max(i._animateToZoom,i.getZoom()):i.getZoom(),e=i.getZoomScale(e,this._tileZoom),t=i.project(t,this._tileZoom).floor(),i=i.getSize().divideBy(2*e);return new m(t.subtract(i),t.add(i))},_update:function(t){var i=this._map;if(i){var e=this._clampZoom(i.getZoom());if(void 0===t&&(t=i.getCenter()),void 0!==this._tileZoom){var n,i=this._getTiledPixelBounds(t),o=this._pxBoundsToTileRange(i),s=o.getCenter(),r=[],i=this.options.keepBuffer,a=new m(o.getBottomLeft().subtract([i,-i]),o.getTopRight().add([i,-i]));if(!(isFinite(o.min.x)&&isFinite(o.min.y)&&isFinite(o.max.x)&&isFinite(o.max.y)))throw new Error("Attempted to load an infinite number of tiles");for(n in this._tiles){var h=this._tiles[n].coords;h.z===this._tileZoom&&a.contains(new p(h.x,h.y))||(this._tiles[n].current=!1)}if(1<Math.abs(e-this._tileZoom))this._setView(t,e);else{for(var l=o.min.y;l<=o.max.y;l++)for(var u=o.min.x;u<=o.max.x;u++){var c,d=new p(u,l);d.z=this._tileZoom,this._isValidTile(d)&&((c=this._tiles[this._tileCoordsToKey(d)])?c.current=!0:r.push(d))}if(r.sort(function(t,i){return t.distanceTo(s)-i.distanceTo(s)}),0!==r.length){this._loading||(this._loading=!0,this.fire("loading"));for(var _=document.createDocumentFragment(),u=0;u<r.length;u++)this._addTile(r[u],_);this._level.el.appendChild(_)}}}}},_isValidTile:function(t){var i=this._map.options.crs;if(!i.infinite){var e=this._globalTileRange;if(!i.wrapLng&&(t.x<e.min.x||t.x>e.max.x)||!i.wrapLat&&(t.y<e.min.y||t.y>e.max.y))return!1}if(!this.options.bounds)return!0;i=this._tileCoordsToBounds(t);return g(this.options.bounds).overlaps(i)},_keyToBounds:function(t){return this._tileCoordsToBounds(this._keyToTileCoords(t))},_tileCoordsToNwSe:function(t){var i=this._map,e=this.getTileSize(),n=t.scaleBy(e),e=n.add(e);return[i.unproject(n,t.z),i.unproject(e,t.z)]},_tileCoordsToBounds:function(t){t=this._tileCoordsToNwSe(t),t=new s(t[0],t[1]);return t=this.options.noWrap?t:this._map.wrapLatLngBounds(t)},_tileCoordsToKey:function(t){return t.x+":"+t.y+":"+t.z},_keyToTileCoords:function(t){var t=t.split(":"),i=new p(+t[0],+t[1]);return i.z=+t[2],i},_removeTile:function(t){var i=this._tiles[t];i&&(T(i.el),delete this._tiles[t],this.fire("tileunload",{tile:i.el,coords:this._keyToTileCoords(t)}))},_initTile:function(t){z(t,"leaflet-tile");var i=this.getTileSize();t.style.width=i.x+"px",t.style.height=i.y+"px",t.onselectstart=u,t.onmousemove=u,P.ielt9&&this.options.opacity<1&&C(t,this.options.opacity)},_addTile:function(t,i){var e=this._getTilePos(t),n=this._tileCoordsToKey(t),o=this.createTile(this._wrapCoords(t),a(this._tileReady,this,t));this._initTile(o),this.createTile.length<2&&x(a(this._tileReady,this,t,null,o)),Z(o,e),this._tiles[n]={el:o,coords:t,current:!0},i.appendChild(o),this.fire("tileloadstart",{tile:o,coords:t})},_tileReady:function(t,i,e){i&&this.fire("tileerror",{error:i,tile:e,coords:t});var n=this._tileCoordsToKey(t);(e=this._tiles[n])&&(e.loaded=+new Date,this._map._fadeAnimated?(C(e.el,0),r(this._fadeFrame),this._fadeFrame=x(this._updateOpacity,this)):(e.active=!0,this._pruneTiles()),i||(z(e.el,"leaflet-tile-loaded"),this.fire("tileload",{tile:e.el,coords:t})),this._noTilesToLoad()&&(this._loading=!1,this.fire("load"),P.ielt9||!this._map._fadeAnimated?x(this._pruneTiles,this):setTimeout(a(this._pruneTiles,this),250)))},_getTilePos:function(t){return t.scaleBy(this.getTileSize()).subtract(this._level.origin)},_wrapCoords:function(t){var i=new p(this._wrapX?H(t.x,this._wrapX):t.x,this._wrapY?H(t.y,this._wrapY):t.y);return i.z=t.z,i},_pxBoundsToTileRange:function(t){var i=this.getTileSize();return new m(t.min.unscaleBy(i).floor(),t.max.unscaleBy(i).ceil().subtract([1,1]))},_noTilesToLoad:function(){for(var t in this._tiles)if(!this._tiles[t].loaded)return!1;return!0}});var Ie=Ae.extend({options:{minZoom:0,maxZoom:18,subdomains:"abc",errorTileUrl:"",zoomOffset:0,tms:!1,zoomReverse:!1,detectRetina:!1,crossOrigin:!1,referrerPolicy:!1},initialize:function(t,i){this._url=t,(i=c(this,i)).detectRetina&&P.retina&&0<i.maxZoom&&(i.tileSize=Math.floor(i.tileSize/2),i.zoomReverse?(i.zoomOffset--,i.minZoom++):(i.zoomOffset++,i.maxZoom--),i.minZoom=Math.max(0,i.minZoom)),"string"==typeof i.subdomains&&(i.subdomains=i.subdomains.split("")),this.on("tileunload",this._onTileRemove)},setUrl:function(t,i){return this._url===t&&void 0===i&&(i=!0),this._url=t,i||this.redraw(),this},createTile:function(t,i){var e=document.createElement("img");return S(e,"load",a(this._tileOnLoad,this,i,e)),S(e,"error",a(this._tileOnError,this,i,e)),!this.options.crossOrigin&&""!==this.options.crossOrigin||(e.crossOrigin=!0===this.options.crossOrigin?"":this.options.crossOrigin),"string"==typeof this.options.referrerPolicy&&(e.referrerPolicy=this.options.referrerPolicy),e.alt="",e.setAttribute("role","presentation"),e.src=this.getTileUrl(t),e},getTileUrl:function(t){var i={r:P.retina?"@2x":"",s:this._getSubdomain(t),x:t.x,y:t.y,z:this._getZoomForUrl()};return this._map&&!this._map.options.crs.infinite&&(t=this._globalTileRange.max.y-t.y,this.options.tms&&(i.y=t),i["-y"]=t),q(this._url,l(i,this.options))},_tileOnLoad:function(t,i){P.ielt9?setTimeout(a(t,this,null,i),0):t(null,i)},_tileOnError:function(t,i,e){var n=this.options.errorTileUrl;n&&i.getAttribute("src")!==n&&(i.src=n),t(e,i)},_onTileRemove:function(t){t.tile.onload=null},_getZoomForUrl:function(){var t=this._tileZoom,i=this.options.maxZoom;return(t=this.options.zoomReverse?i-t:t)+this.options.zoomOffset},_getSubdomain:function(t){t=Math.abs(t.x+t.y)%this.options.subdomains.length;return this.options.subdomains[t]},_abortLoading:function(){var t,i,e;for(t in this._tiles)this._tiles[t].coords.z!==this._tileZoom&&((e=this._tiles[t].el).onload=u,e.onerror=u,e.complete||(e.src=K,i=this._tiles[t].coords,T(e),delete this._tiles[t],this.fire("tileabort",{tile:e,coords:i})))},_removeTile:function(t){var i=this._tiles[t];if(i)return i.el.setAttribute("src",K),Ae.prototype._removeTile.call(this,t)},_tileReady:function(t,i,e){if(this._map&&(!e||e.getAttribute("src")!==K))return Ae.prototype._tileReady.call(this,t,i,e)}});function Oe(t,i){return new Ie(t,i)}var Re=Ie.extend({defaultWmsParams:{service:"WMS",request:"GetMap",layers:"",styles:"",format:"image/jpeg",transparent:!1,version:"1.1.1"},options:{crs:null,uppercase:!1},initialize:function(t,i){this._url=t;var e,n=l({},this.defaultWmsParams);for(e in i)e in this.options||(n[e]=i[e]);var t=(i=c(this,i)).detectRetina&&P.retina?2:1,o=this.getTileSize();n.width=o.x*t,n.height=o.y*t,this.wmsParams=n},onAdd:function(t){this._crs=this.options.crs||t.options.crs,this._wmsVersion=parseFloat(this.wmsParams.version);var i=1.3<=this._wmsVersion?"crs":"srs";this.wmsParams[i]=this._crs.code,Ie.prototype.onAdd.call(this,t)},getTileUrl:function(t){var i=this._tileCoordsToNwSe(t),e=this._crs,e=f(e.project(i[0]),e.project(i[1])),i=e.min,e=e.max,i=(1.3<=this._wmsVersion&&this._crs===re?[i.y,i.x,e.y,e.x]:[i.x,i.y,e.x,e.y]).join(","),e=Ie.prototype.getTileUrl.call(this,t);return e+U(this.wmsParams,e,this.options.uppercase)+(this.options.uppercase?"&BBOX=":"&bbox=")+i},setParams:function(t,i){return l(this.wmsParams,t),i||this.redraw(),this}});Ie.WMS=Re,Oe.wms=function(t,i){return new Re(t,i)};var Ne=o.extend({options:{padding:.1},initialize:function(t){c(this,t),h(this),this._layers=this._layers||{}},onAdd:function(){this._container||(this._initContainer(),this._zoomAnimated&&z(this._container,"leaflet-zoom-animated")),this.getPane().appendChild(this._container),this._update(),this.on("update",this._updatePaths,this)},onRemove:function(){this.off("update",this._updatePaths,this),this._destroyContainer()},getEvents:function(){var t={viewreset:this._reset,zoom:this._onZoom,moveend:this._update,zoomend:this._onZoomEnd};return this._zoomAnimated&&(t.zoomanim=this._onAnimZoom),t},_onAnimZoom:function(t){this._updateTransform(t.center,t.zoom)},_onZoom:function(){this._updateTransform(this._map.getCenter(),this._map.getZoom())},_updateTransform:function(t,i){var e=this._map.getZoomScale(i,this._zoom),n=this._map.getSize().multiplyBy(.5+this.options.padding),o=this._map.project(this._center,i),n=n.multiplyBy(-e).add(o).subtract(this._map._getNewPixelOrigin(t,i));P.any3d?Pi(this._container,n,e):Z(this._container,n)},_reset:function(){for(var t in this._update(),this._updateTransform(this._center,this._zoom),this._layers)this._layers[t]._reset()},_onZoomEnd:function(){for(var t in this._layers)this._layers[t]._project()},_updatePaths:function(){for(var t in this._layers)this._layers[t]._update()},_update:function(){var t=this.options.padding,i=this._map.getSize(),e=this._map.containerPointToLayerPoint(i.multiplyBy(-t)).round();this._bounds=new m(e,e.add(i.multiplyBy(1+2*t)).round()),this._center=this._map.getCenter(),this._zoom=this._map.getZoom()}}),De=Ne.extend({options:{tolerance:0},getEvents:function(){var t=Ne.prototype.getEvents.call(this);return t.viewprereset=this._onViewPreReset,t},_onViewPreReset:function(){this._postponeUpdatePaths=!0},onAdd:function(){Ne.prototype.onAdd.call(this),this._draw()},_initContainer:function(){var t=this._container=document.createElement("canvas");S(t,"mousemove",this._onMouseMove,this),S(t,"click dblclick mousedown mouseup contextmenu",this._onClick,this),S(t,"mouseout",this._handleMouseOut,this),t._leaflet_disable_events=!0,this._ctx=t.getContext("2d")},_destroyContainer:function(){r(this._redrawRequest),delete this._ctx,T(this._container),E(this._container),delete this._container},_updatePaths:function(){if(!this._postponeUpdatePaths){for(var t in this._redrawBounds=null,this._layers)this._layers[t]._update();this._redraw()}},_update:function(){var t,i,e,n;this._map._animatingZoom&&this._bounds||(Ne.prototype._update.call(this),t=this._bounds,i=this._container,e=t.getSize(),n=P.retina?2:1,Z(i,t.min),i.width=n*e.x,i.height=n*e.y,i.style.width=e.x+"px",i.style.height=e.y+"px",P.retina&&this._ctx.scale(2,2),this._ctx.translate(-t.min.x,-t.min.y),this.fire("update"))},_reset:function(){Ne.prototype._reset.call(this),this._postponeUpdatePaths&&(this._postponeUpdatePaths=!1,this._updatePaths())},_initPath:function(t){this._updateDashArray(t);t=(this._layers[h(t)]=t)._order={layer:t,prev:this._drawLast,next:null};this._drawLast&&(this._drawLast.next=t),this._drawLast=t,this._drawFirst=this._drawFirst||this._drawLast},_addPath:function(t){this._requestRedraw(t)},_removePath:function(t){var i=t._order,e=i.next,i=i.prev;e?e.prev=i:this._drawLast=i,i?i.next=e:this._drawFirst=e,delete t._order,delete this._layers[h(t)],this._requestRedraw(t)},_updatePath:function(t){this._extendRedrawBounds(t),t._project(),t._update(),this._requestRedraw(t)},_updateStyle:function(t){this._updateDashArray(t),this._requestRedraw(t)},_updateDashArray:function(t){if("string"==typeof t.options.dashArray){for(var i,e=t.options.dashArray.split(/[, ]+/),n=[],o=0;o<e.length;o++){if(i=Number(e[o]),isNaN(i))return;n.push(i)}t.options._dashArray=n}else t.options._dashArray=t.options.dashArray},_requestRedraw:function(t){this._map&&(this._extendRedrawBounds(t),this._redrawRequest=this._redrawRequest||x(this._redraw,this))},_extendRedrawBounds:function(t){var i;t._pxBounds&&(i=(t.options.weight||0)+1,this._redrawBounds=this._redrawBounds||new m,this._redrawBounds.extend(t._pxBounds.min.subtract([i,i])),this._redrawBounds.extend(t._pxBounds.max.add([i,i])))},_redraw:function(){this._redrawRequest=null,this._redrawBounds&&(this._redrawBounds.min._floor(),this._redrawBounds.max._ceil()),this._clear(),this._draw(),this._redrawBounds=null},_clear:function(){var t,i=this._redrawBounds;i?(t=i.getSize(),this._ctx.clearRect(i.min.x,i.min.y,t.x,t.y)):(this._ctx.save(),this._ctx.setTransform(1,0,0,1,0,0),this._ctx.clearRect(0,0,this._container.width,this._container.height),this._ctx.restore())},_draw:function(){var t,i,e=this._redrawBounds;this._ctx.save(),e&&(i=e.getSize(),this._ctx.beginPath(),this._ctx.rect(e.min.x,e.min.y,i.x,i.y),this._ctx.clip()),this._drawing=!0;for(var n=this._drawFirst;n;n=n.next)t=n.layer,(!e||t._pxBounds&&t._pxBounds.intersects(e))&&t._updatePath();this._drawing=!1,this._ctx.restore()},_updatePoly:function(t,i){if(this._drawing){var e,n,o,s,r=t._parts,a=r.length,h=this._ctx;if(a){for(h.beginPath(),e=0;e<a;e++){for(n=0,o=r[e].length;n<o;n++)s=r[e][n],h[n?"lineTo":"moveTo"](s.x,s.y);i&&h.closePath()}this._fillStroke(h,t)}}},_updateCircle:function(t){var i,e,n,o;this._drawing&&!t._empty()&&(i=t._point,e=this._ctx,n=Math.max(Math.round(t._radius),1),1!=(o=(Math.max(Math.round(t._radiusY),1)||n)/n)&&(e.save(),e.scale(1,o)),e.beginPath(),e.arc(i.x,i.y/o,n,0,2*Math.PI,!1),1!=o&&e.restore(),this._fillStroke(e,t))},_fillStroke:function(t,i){var e=i.options;e.fill&&(t.globalAlpha=e.fillOpacity,t.fillStyle=e.fillColor||e.color,t.fill(e.fillRule||"evenodd")),e.stroke&&0!==e.weight&&(t.setLineDash&&t.setLineDash(i.options&&i.options._dashArray||[]),t.globalAlpha=e.opacity,t.lineWidth=e.weight,t.strokeStyle=e.color,t.lineCap=e.lineCap,t.lineJoin=e.lineJoin,t.stroke())},_onClick:function(t){for(var i,e,n=this._map.mouseEventToLayerPoint(t),o=this._drawFirst;o;o=o.next)(i=o.layer).options.interactive&&i._containsPoint(n)&&(("click"===t.type||"preclick"===t.type)&&this._map._draggableMoved(i)||(e=i));this._fireEvent(!!e&&[e],t)},_onMouseMove:function(t){var i;!this._map||this._map.dragging.moving()||this._map._animatingZoom||(i=this._map.mouseEventToLayerPoint(t),this._handleMouseHover(t,i))},_handleMouseOut:function(t){var i=this._hoveredLayer;i&&(M(this._container,"leaflet-interactive"),this._fireEvent([i],t,"mouseout"),this._hoveredLayer=null,this._mouseHoverThrottled=!1)},_handleMouseHover:function(t,i){if(!this._mouseHoverThrottled){for(var e,n,o=this._drawFirst;o;o=o.next)(e=o.layer).options.interactive&&e._containsPoint(i)&&(n=e);n!==this._hoveredLayer&&(this._handleMouseOut(t),n&&(z(this._container,"leaflet-interactive"),this._fireEvent([n],t,"mouseover"),this._hoveredLayer=n)),this._fireEvent(!!this._hoveredLayer&&[this._hoveredLayer],t),this._mouseHoverThrottled=!0,setTimeout(a(function(){this._mouseHoverThrottled=!1},this),32)}},_fireEvent:function(t,i,e){this._map._fireDOMEvent(i,e||i.type,t)},_bringToFront:function(t){var i,e,n=t._order;n&&(i=n.next,e=n.prev,i&&((i.prev=e)?e.next=i:i&&(this._drawFirst=i),n.prev=this._drawLast,(this._drawLast.next=n).next=null,this._drawLast=n,this._requestRedraw(t)))},_bringToBack:function(t){var i,e,n=t._order;n&&(i=n.next,(e=n.prev)&&((e.next=i)?i.prev=e:e&&(this._drawLast=e),n.prev=null,n.next=this._drawFirst,this._drawFirst.prev=n,this._drawFirst=n,this._requestRedraw(t)))}});function je(t){return P.canvas?new De(t):null}var He=function(){try{return document.namespaces.add("lvml","urn:schemas-microsoft-com:vml"),function(t){return document.createElement("<lvml:"+t+' class="lvml">')}}catch(t){}return function(t){return document.createElement("<"+t+' xmlns="urn:schemas-microsoft.com:vml" class="lvml">')}}(),Mt={_initContainer:function(){this._container=b("div","leaflet-vml-container")},_update:function(){this._map._animatingZoom||(Ne.prototype._update.call(this),this.fire("update"))},_initPath:function(t){var i=t._container=He("shape");z(i,"leaflet-vml-shape "+(this.options.className||"")),i.coordsize="1 1",t._path=He("path"),i.appendChild(t._path),this._updateStyle(t),this._layers[h(t)]=t},_addPath:function(t){var i=t._container;this._container.appendChild(i),t.options.interactive&&t.addInteractiveTarget(i)},_removePath:function(t){var i=t._container;T(i),t.removeInteractiveTarget(i),delete this._layers[h(t)]},_updateStyle:function(t){var i=t._stroke,e=t._fill,n=t.options,o=t._container;o.stroked=!!n.stroke,o.filled=!!n.fill,n.stroke?(i=i||(t._stroke=He("stroke")),o.appendChild(i),i.weight=n.weight+"px",i.color=n.color,i.opacity=n.opacity,n.dashArray?i.dashStyle=d(n.dashArray)?n.dashArray.join(" "):n.dashArray.replace(/( *, *)/g," "):i.dashStyle="",i.endcap=n.lineCap.replace("butt","flat"),i.joinstyle=n.lineJoin):i&&(o.removeChild(i),t._stroke=null),n.fill?(e=e||(t._fill=He("fill")),o.appendChild(e),e.color=n.fillColor||n.color,e.opacity=n.fillOpacity):e&&(o.removeChild(e),t._fill=null)},_updateCircle:function(t){var i=t._point.round(),e=Math.round(t._radius),n=Math.round(t._radiusY||e);this._setPath(t,t._empty()?"M0 0":"AL "+i.x+","+i.y+" "+e+","+n+" 0,23592600")},_setPath:function(t,i){t._path.v=i},_bringToFront:function(t){fi(t._container)},_bringToBack:function(t){gi(t._container)}},We=P.vml?He:ct,Fe=Ne.extend({_initContainer:function(){this._container=We("svg"),this._container.setAttribute("pointer-events","none"),this._rootGroup=We("g"),this._container.appendChild(this._rootGroup)},_destroyContainer:function(){T(this._container),E(this._container),delete this._container,delete this._rootGroup,delete this._svgSize},_update:function(){var t,i,e;this._map._animatingZoom&&this._bounds||(Ne.prototype._update.call(this),i=(t=this._bounds).getSize(),e=this._container,this._svgSize&&this._svgSize.equals(i)||(this._svgSize=i,e.setAttribute("width",i.x),e.setAttribute("height",i.y)),Z(e,t.min),e.setAttribute("viewBox",[t.min.x,t.min.y,i.x,i.y].join(" ")),this.fire("update"))},_initPath:function(t){var i=t._path=We("path");t.options.className&&z(i,t.options.className),t.options.interactive&&z(i,"leaflet-interactive"),this._updateStyle(t),this._layers[h(t)]=t},_addPath:function(t){this._rootGroup||this._initContainer(),this._rootGroup.appendChild(t._path),t.addInteractiveTarget(t._path)},_removePath:function(t){T(t._path),t.removeInteractiveTarget(t._path),delete this._layers[h(t)]},_updatePath:function(t){t._project(),t._update()},_updateStyle:function(t){var i=t._path,t=t.options;i&&(t.stroke?(i.setAttribute("stroke",t.color),i.setAttribute("stroke-opacity",t.opacity),i.setAttribute("stroke-width",t.weight),i.setAttribute("stroke-linecap",t.lineCap),i.setAttribute("stroke-linejoin",t.lineJoin),t.dashArray?i.setAttribute("stroke-dasharray",t.dashArray):i.removeAttribute("stroke-dasharray"),t.dashOffset?i.setAttribute("stroke-dashoffset",t.dashOffset):i.removeAttribute("stroke-dashoffset")):i.setAttribute("stroke","none"),t.fill?(i.setAttribute("fill",t.fillColor||t.color),i.setAttribute("fill-opacity",t.fillOpacity),i.setAttribute("fill-rule",t.fillRule||"evenodd")):i.setAttribute("fill","none"))},_updatePoly:function(t,i){this._setPath(t,dt(t._parts,i))},_updateCircle:function(t){var i=t._point,e=Math.max(Math.round(t._radius),1),n="a"+e+","+(Math.max(Math.round(t._radiusY),1)||e)+" 0 1,0 ",i=t._empty()?"M0 0":"M"+(i.x-e)+","+i.y+n+2*e+",0 "+n+2*-e+",0 ";this._setPath(t,i)},_setPath:function(t,i){t._path.setAttribute("d",i)},_bringToFront:function(t){fi(t._path)},_bringToBack:function(t){gi(t._path)}});function Ue(t){return P.svg||P.vml?new Fe(t):null}P.vml&&Fe.include(Mt),A.include({getRenderer:function(t){t=(t=t.options.renderer||this._getPaneRenderer(t.options.pane)||this.options.renderer||this._renderer)||(this._renderer=this._createRenderer());return this.hasLayer(t)||this.addLayer(t),t},_getPaneRenderer:function(t){if("overlayPane"===t||void 0===t)return!1;var i=this._paneRenderers[t];return void 0===i&&(i=this._createRenderer({pane:t}),this._paneRenderers[t]=i),i},_createRenderer:function(t){return this.options.preferCanvas&&je(t)||Ue(t)}});var Ve=ge.extend({initialize:function(t,i){ge.prototype.initialize.call(this,this._boundsToLatLngs(t),i)},setBounds:function(t){return this.setLatLngs(this._boundsToLatLngs(t))},_boundsToLatLngs:function(t){return[(t=g(t)).getSouthWest(),t.getNorthWest(),t.getNorthEast(),t.getSouthEast()]}});Fe.create=We,Fe.pointsToPath=dt,ve.geometryToLayer=ye,ve.coordsToLatLng=we,ve.coordsToLatLngs=Pe,ve.latLngToCoords=be,ve.latLngsToCoords=Le,ve.getFeature=Te,ve.asFeature=ze,A.mergeOptions({boxZoom:!0});var _t=n.extend({initialize:function(t){this._map=t,this._container=t._container,this._pane=t._panes.overlayPane,this._resetStateTimeout=0,t.on("unload",this._destroy,this)},addHooks:function(){S(this._container,"mousedown",this._onMouseDown,this)},removeHooks:function(){E(this._container,"mousedown",this._onMouseDown,this)},moved:function(){return this._moved},_destroy:function(){T(this._pane),delete this._pane},_resetState:function(){this._resetStateTimeout=0,this._moved=!1},_clearDeferredResetState:function(){0!==this._resetStateTimeout&&(clearTimeout(this._resetStateTimeout),this._resetStateTimeout=0)},_onMouseDown:function(t){if(!t.shiftKey||1!==t.which&&1!==t.button)return!1;this._clearDeferredResetState(),this._resetState(),ri(),Li(),this._startPoint=this._map.mouseEventToContainerPoint(t),S(document,{contextmenu:Ri,mousemove:this._onMouseMove,mouseup:this._onMouseUp,keydown:this._onKeyDown},this)},_onMouseMove:function(t){this._moved||(this._moved=!0,this._box=b("div","leaflet-zoom-box",this._container),z(this._container,"leaflet-crosshair"),this._map.fire("boxzoomstart")),this._point=this._map.mouseEventToContainerPoint(t);var t=new m(this._point,this._startPoint),i=t.getSize();Z(this._box,t.min),this._box.style.width=i.x+"px",this._box.style.height=i.y+"px"},_finish:function(){this._moved&&(T(this._box),M(this._container,"leaflet-crosshair")),ai(),Ti(),E(document,{contextmenu:Ri,mousemove:this._onMouseMove,mouseup:this._onMouseUp,keydown:this._onKeyDown},this)},_onMouseUp:function(t){1!==t.which&&1!==t.button||(this._finish(),this._moved&&(this._clearDeferredResetState(),this._resetStateTimeout=setTimeout(a(this._resetState,this),0),t=new s(this._map.containerPointToLatLng(this._startPoint),this._map.containerPointToLatLng(this._point)),this._map.fitBounds(t).fire("boxzoomend",{boxZoomBounds:t})))},_onKeyDown:function(t){27===t.keyCode&&(this._finish(),this._clearDeferredResetState(),this._resetState())}}),Ct=(A.addInitHook("addHandler","boxZoom",_t),A.mergeOptions({doubleClickZoom:!0}),n.extend({addHooks:function(){this._map.on("dblclick",this._onDoubleClick,this)},removeHooks:function(){this._map.off("dblclick",this._onDoubleClick,this)},_onDoubleClick:function(t){var i=this._map,e=i.getZoom(),n=i.options.zoomDelta,e=t.originalEvent.shiftKey?e-n:e+n;"center"===i.options.doubleClickZoom?i.setZoom(e):i.setZoomAround(t.containerPoint,e)}})),Zt=(A.addInitHook("addHandler","doubleClickZoom",Ct),A.mergeOptions({dragging:!0,inertia:!0,inertiaDeceleration:3400,inertiaMaxSpeed:1/0,easeLinearity:.2,worldCopyJump:!1,maxBoundsViscosity:0}),n.extend({addHooks:function(){var t;this._draggable||(t=this._map,this._draggable=new Xi(t._mapPane,t._container),this._draggable.on({dragstart:this._onDragStart,drag:this._onDrag,dragend:this._onDragEnd},this),this._draggable.on("predrag",this._onPreDragLimit,this),t.options.worldCopyJump&&(this._draggable.on("predrag",this._onPreDragWrap,this),t.on("zoomend",this._onZoomEnd,this),t.whenReady(this._onZoomEnd,this))),z(this._map._container,"leaflet-grab leaflet-touch-drag"),this._draggable.enable(),this._positions=[],this._times=[]},removeHooks:function(){M(this._map._container,"leaflet-grab"),M(this._map._container,"leaflet-touch-drag"),this._draggable.disable()},moved:function(){return this._draggable&&this._draggable._moved},moving:function(){return this._draggable&&this._draggable._moving},_onDragStart:function(){var t,i=this._map;i._stop(),this._map.options.maxBounds&&this._map.options.maxBoundsViscosity?(t=g(this._map.options.maxBounds),this._offsetLimit=f(this._map.latLngToContainerPoint(t.getNorthWest()).multiplyBy(-1),this._map.latLngToContainerPoint(t.getSouthEast()).multiplyBy(-1).add(this._map.getSize())),this._viscosity=Math.min(1,Math.max(0,this._map.options.maxBoundsViscosity))):this._offsetLimit=null,i.fire("movestart").fire("dragstart"),i.options.inertia&&(this._positions=[],this._times=[])},_onDrag:function(t){var i,e;this._map.options.inertia&&(i=this._lastTime=+new Date,e=this._lastPos=this._draggable._absPos||this._draggable._newPos,this._positions.push(e),this._times.push(i),this._prunePositions(i)),this._map.fire("move",t).fire("drag",t)},_prunePositions:function(t){for(;1<this._positions.length&&50<t-this._times[0];)this._positions.shift(),this._times.shift()},_onZoomEnd:function(){var t=this._map.getSize().divideBy(2),i=this._map.latLngToLayerPoint([0,0]);this._initialWorldOffset=i.subtract(t).x,this._worldWidth=this._map.getPixelWorldBounds().getSize().x},_viscousLimit:function(t,i){return t-(t-i)*this._viscosity},_onPreDragLimit:function(){var t,i;this._viscosity&&this._offsetLimit&&(t=this._draggable._newPos.subtract(this._draggable._startPos),i=this._offsetLimit,t.x<i.min.x&&(t.x=this._viscousLimit(t.x,i.min.x)),t.y<i.min.y&&(t.y=this._viscousLimit(t.y,i.min.y)),t.x>i.max.x&&(t.x=this._viscousLimit(t.x,i.max.x)),t.y>i.max.y&&(t.y=this._viscousLimit(t.y,i.max.y)),this._draggable._newPos=this._draggable._startPos.add(t))},_onPreDragWrap:function(){var t=this._worldWidth,i=Math.round(t/2),e=this._initialWorldOffset,n=this._draggable._newPos.x,o=(n-i+e)%t+i-e,n=(n+i+e)%t-i-e,t=Math.abs(o+e)<Math.abs(n+e)?o:n;this._draggable._absPos=this._draggable._newPos.clone(),this._draggable._newPos.x=t},_onDragEnd:function(t){var i,e,n,o,s=this._map,r=s.options,a=!r.inertia||t.noInertia||this._times.length<2;s.fire("dragend",t),a?s.fire("moveend"):(this._prunePositions(+new Date),t=this._lastPos.subtract(this._positions[0]),a=(this._lastTime-this._times[0])/1e3,i=r.easeLinearity,a=(t=t.multiplyBy(i/a)).distanceTo([0,0]),e=Math.min(r.inertiaMaxSpeed,a),t=t.multiplyBy(e/a),n=e/(r.inertiaDeceleration*i),(o=t.multiplyBy(-n/2).round()).x||o.y?(o=s._limitOffset(o,s.options.maxBounds),x(function(){s.panBy(o,{duration:n,easeLinearity:i,noMoveStart:!0,animate:!0})})):s.fire("moveend"))}})),St=(A.addInitHook("addHandler","dragging",Zt),A.mergeOptions({keyboard:!0,keyboardPanDelta:80}),n.extend({keyCodes:{left:[37],right:[39],down:[40],up:[38],zoomIn:[187,107,61,171],zoomOut:[189,109,54,173]},initialize:function(t){this._map=t,this._setPanDelta(t.options.keyboardPanDelta),this._setZoomDelta(t.options.zoomDelta)},addHooks:function(){var t=this._map._container;t.tabIndex<=0&&(t.tabIndex="0"),S(t,{focus:this._onFocus,blur:this._onBlur,mousedown:this._onMouseDown},this),this._map.on({focus:this._addHooks,blur:this._removeHooks},this)},removeHooks:function(){this._removeHooks(),E(this._map._container,{focus:this._onFocus,blur:this._onBlur,mousedown:this._onMouseDown},this),this._map.off({focus:this._addHooks,blur:this._removeHooks},this)},_onMouseDown:function(){var t,i,e;this._focused||(e=document.body,t=document.documentElement,i=e.scrollTop||t.scrollTop,e=e.scrollLeft||t.scrollLeft,this._map._container.focus(),window.scrollTo(e,i))},_onFocus:function(){this._focused=!0,this._map.fire("focus")},_onBlur:function(){this._focused=!1,this._map.fire("blur")},_setPanDelta:function(t){for(var i=this._panKeys={},e=this.keyCodes,n=0,o=e.left.length;n<o;n++)i[e.left[n]]=[-1*t,0];for(n=0,o=e.right.length;n<o;n++)i[e.right[n]]=[t,0];for(n=0,o=e.down.length;n<o;n++)i[e.down[n]]=[0,t];for(n=0,o=e.up.length;n<o;n++)i[e.up[n]]=[0,-1*t]},_setZoomDelta:function(t){for(var i=this._zoomKeys={},e=this.keyCodes,n=0,o=e.zoomIn.length;n<o;n++)i[e.zoomIn[n]]=t;for(n=0,o=e.zoomOut.length;n<o;n++)i[e.zoomOut[n]]=-t},_addHooks:function(){S(document,"keydown",this._onKeyDown,this)},_removeHooks:function(){E(document,"keydown",this._onKeyDown,this)},_onKeyDown:function(t){if(!(t.altKey||t.ctrlKey||t.metaKey)){var i,e=t.keyCode,n=this._map;if(e in this._panKeys)n._panAnim&&n._panAnim._inProgress||(i=this._panKeys[e],t.shiftKey&&(i=_(i).multiplyBy(3)),n.panBy(i),n.options.maxBounds&&n.panInsideBounds(n.options.maxBounds));else if(e in this._zoomKeys)n.setZoom(n.getZoom()+(t.shiftKey?3:1)*this._zoomKeys[e]);else{if(27!==e||!n._popup||!n._popup.options.closeOnEscapeKey)return;n.closePopup()}Ri(t)}}})),kt=(A.addInitHook("addHandler","keyboard",St),A.mergeOptions({scrollWheelZoom:!0,wheelDebounceTime:40,wheelPxPerZoomLevel:60}),n.extend({addHooks:function(){S(this._map._container,"wheel",this._onWheelScroll,this),this._delta=0},removeHooks:function(){E(this._map._container,"wheel",this._onWheelScroll,this)},_onWheelScroll:function(t){var i=ji(t),e=this._map.options.wheelDebounceTime,i=(this._delta+=i,this._lastMousePos=this._map.mouseEventToContainerPoint(t),this._startTime||(this._startTime=+new Date),Math.max(e-(+new Date-this._startTime),0));clearTimeout(this._timer),this._timer=setTimeout(a(this._performZoom,this),i),Ri(t)},_performZoom:function(){var t=this._map,i=t.getZoom(),e=this._map.options.zoomSnap||0,n=(t._stop(),this._delta/(4*this._map.options.wheelPxPerZoomLevel)),n=4*Math.log(2/(1+Math.exp(-Math.abs(n))))/Math.LN2,e=e?Math.ceil(n/e)*e:n,n=t._limitZoom(i+(0<this._delta?e:-e))-i;this._delta=0,this._startTime=null,n&&("center"===t.options.scrollWheelZoom?t.setZoom(i+n):t.setZoomAround(this._lastMousePos,i+n))}})),Et=(A.addInitHook("addHandler","scrollWheelZoom",kt),A.mergeOptions({tapHold:P.touchNative&&P.safari&&P.mobile,tapTolerance:15}),n.extend({addHooks:function(){S(this._map._container,"touchstart",this._onDown,this)},removeHooks:function(){E(this._map._container,"touchstart",this._onDown,this)},_onDown:function(t){var i;clearTimeout(this._holdTimeout),1===t.touches.length&&(i=t.touches[0],this._startPos=this._newPos=new p(i.clientX,i.clientY),this._holdTimeout=setTimeout(a(function(){this._cancel(),this._isTapValid()&&(S(document,"touchend",B),S(document,"touchend touchcancel",this._cancelClickPrevent),this._simulateEvent("contextmenu",i))},this),600),S(document,"touchend touchcancel contextmenu",this._cancel,this),S(document,"touchmove",this._onMove,this))},_cancelClickPrevent:function t(){E(document,"touchend",B),E(document,"touchend touchcancel",t)},_cancel:function(){clearTimeout(this._holdTimeout),E(document,"touchend touchcancel contextmenu",this._cancel,this),E(document,"touchmove",this._onMove,this)},_onMove:function(t){t=t.touches[0];this._newPos=new p(t.clientX,t.clientY)},_isTapValid:function(){return this._newPos.distanceTo(this._startPos)<=this._map.options.tapTolerance},_simulateEvent:function(t,i){t=new MouseEvent(t,{bubbles:!0,cancelable:!0,view:window,screenX:i.screenX,screenY:i.screenY,clientX:i.clientX,clientY:i.clientY});t._simulated=!0,i.target.dispatchEvent(t)}})),Bt=(A.addInitHook("addHandler","tapHold",Et),A.mergeOptions({touchZoom:P.touch,bounceAtZoomLimits:!0}),n.extend({addHooks:function(){z(this._map._container,"leaflet-touch-zoom"),S(this._map._container,"touchstart",this._onTouchStart,this)},removeHooks:function(){M(this._map._container,"leaflet-touch-zoom"),E(this._map._container,"touchstart",this._onTouchStart,this)},_onTouchStart:function(t){var i,e,n=this._map;!t.touches||2!==t.touches.length||n._animatingZoom||this._zooming||(i=n.mouseEventToContainerPoint(t.touches[0]),e=n.mouseEventToContainerPoint(t.touches[1]),this._centerPoint=n.getSize()._divideBy(2),this._startLatLng=n.containerPointToLatLng(this._centerPoint),"center"!==n.options.touchZoom&&(this._pinchStartLatLng=n.containerPointToLatLng(i.add(e)._divideBy(2))),this._startDist=i.distanceTo(e),this._startZoom=n.getZoom(),this._moved=!1,this._zooming=!0,n._stop(),S(document,"touchmove",this._onTouchMove,this),S(document,"touchend touchcancel",this._onTouchEnd,this),B(t))},_onTouchMove:function(t){if(t.touches&&2===t.touches.length&&this._zooming){var i=this._map,e=i.mouseEventToContainerPoint(t.touches[0]),n=i.mouseEventToContainerPoint(t.touches[1]),o=e.distanceTo(n)/this._startDist;if(this._zoom=i.getScaleZoom(o,this._startZoom),!i.options.bounceAtZoomLimits&&(this._zoom<i.getMinZoom()&&o<1||this._zoom>i.getMaxZoom()&&1<o)&&(this._zoom=i._limitZoom(this._zoom)),"center"===i.options.touchZoom){if(this._center=this._startLatLng,1==o)return}else{e=e._add(n)._divideBy(2)._subtract(this._centerPoint);if(1==o&&0===e.x&&0===e.y)return;this._center=i.unproject(i.project(this._pinchStartLatLng,this._zoom).subtract(e),this._zoom)}this._moved||(i._moveStart(!0,!1),this._moved=!0),r(this._animRequest);n=a(i._move,i,this._center,this._zoom,{pinch:!0,round:!1});this._animRequest=x(n,this,!0),B(t)}},_onTouchEnd:function(){this._moved&&this._zooming?(this._zooming=!1,r(this._animRequest),E(document,"touchmove",this._onTouchMove,this),E(document,"touchend touchcancel",this._onTouchEnd,this),this._map.options.zoomAnimation?this._map._animateZoom(this._center,this._map._limitZoom(this._zoom),!0,this._map.options.zoomSnap):this._map._resetView(this._center,this._map._limitZoom(this._zoom))):this._zooming=!1}})),qe=(A.addInitHook("addHandler","touchZoom",Bt),A.BoxZoom=_t,A.DoubleClickZoom=Ct,A.Drag=Zt,A.Keyboard=St,A.ScrollWheelZoom=kt,A.TapHold=Et,A.TouchZoom=Bt,t.Bounds=m,t.Browser=P,t.CRS=ot,t.Canvas=De,t.Circle=me,t.CircleMarker=pe,t.Class=it,t.Control=I,t.DivIcon=Be,t.DivOverlay=O,t.DomEvent=mt,t.DomUtil=pt,t.Draggable=Xi,t.Evented=et,t.FeatureGroup=he,t.GeoJSON=ve,t.GridLayer=Ae,t.Handler=n,t.Icon=le,t.ImageOverlay=Ce,t.LatLng=v,t.LatLngBounds=s,t.Layer=o,t.LayerGroup=ae,t.LineUtil=gt,t.Map=A,t.Marker=de,t.Mixin=ft,t.Path=_e,t.Point=p,t.PolyUtil=vt,t.Polygon=ge,t.Polyline=fe,t.Popup=ke,t.PosAnimation=Wi,t.Projection=wt,t.Rectangle=Ve,t.Renderer=Ne,t.SVG=Fe,t.SVGOverlay=Se,t.TileLayer=Ie,t.Tooltip=Ee,t.Transformation=at,t.Util=tt,t.VideoOverlay=Ze,t.bind=a,t.bounds=f,t.canvas=je,t.circle=function(t,i,e){return new me(t,i,e)},t.circleMarker=function(t,i){return new pe(t,i)},t.control=Fi,t.divIcon=function(t){return new Be(t)},t.extend=l,t.featureGroup=function(t,i){return new he(t,i)},t.geoJSON=Me,t.geoJson=zt,t.gridLayer=function(t){return new Ae(t)},t.icon=function(t){return new le(t)},t.imageOverlay=function(t,i,e){return new Ce(t,i,e)},t.latLng=w,t.latLngBounds=g,t.layerGroup=function(t,i){return new ae(t,i)},t.map=function(t,i){return new A(t,i)},t.marker=function(t,i){return new de(t,i)},t.point=_,t.polygon=function(t,i){return new ge(t,i)},t.polyline=function(t,i){return new fe(t,i)},t.popup=function(t,i){return new ke(t,i)},t.rectangle=function(t,i){return new Ve(t,i)},t.setOptions=c,t.stamp=h,t.svg=Ue,t.svgOverlay=function(t,i,e){return new Se(t,i,e)},t.tileLayer=Oe,t.tooltip=function(t,i){return new Ee(t,i)},t.transformation=ht,t.version="1.8.0",t.videoOverlay=function(t,i,e){return new Ze(t,i,e)},window.L);t.noConflict=function(){return window.L=qe,this},window.L=t});
//# sourceMappingURL=leaflet.js.map
(function() {
    // save these original methods before they are overwritten
    var proto_initIcon = L.Marker.prototype._initIcon;
    var proto_setPos = L.Marker.prototype._setPos;

    var oldIE = (L.DomUtil.TRANSFORM === 'msTransform');

    L.Marker.addInitHook(function () {
        var iconOptions = this.options.icon && this.options.icon.options;
        var iconAnchor = iconOptions && this.options.icon.options.iconAnchor;
        if (iconAnchor) {
            iconAnchor = (iconAnchor[0] + 'px ' + iconAnchor[1] + 'px');
        }
        this.options.rotationOrigin = this.options.rotationOrigin || iconAnchor || 'center bottom' ;
        this.options.rotationAngle = this.options.rotationAngle || 0;

        // Ensure marker keeps rotated during dragging
        this.on('drag', function(e) { e.target._applyRotation(); });
    });

    L.Marker.include({
        _initIcon: function() {
            proto_initIcon.call(this);
        },

        _setPos: function (pos) {
            proto_setPos.call(this, pos);
            this._applyRotation();
        },

        _applyRotation: function () {
            if(this.options.rotationAngle) {
                this._icon.style[L.DomUtil.TRANSFORM+'Origin'] = this.options.rotationOrigin;

                if(oldIE) {
                    // for IE 9, use the 2D rotation
                    this._icon.style[L.DomUtil.TRANSFORM] = 'rotate(' + this.options.rotationAngle + 'deg)';
                } else {
                    // for modern browsers, prefer the 3D accelerated version
                    this._icon.style[L.DomUtil.TRANSFORM] += ' rotateZ(' + this.options.rotationAngle + 'deg)';
                }
            }
        },

        setRotationAngle: function(angle) {
            this.options.rotationAngle = angle;
            this.update();
            return this;
        },

        setRotationOrigin: function(origin) {
            this.options.rotationOrigin = origin;
            this.update();
            return this;
        }
    });
})();

// http://spin.js.org/#v2.3.2
!function(a,b){"object"==typeof module&&module.exports?module.exports=b():"function"==typeof define&&define.amd?define(b):a.Spinner=b()}(this,function(){"use strict";function a(a,b){var c,d=document.createElement(a||"div");for(c in b)d[c]=b[c];return d}function b(a){for(var b=1,c=arguments.length;c>b;b++)a.appendChild(arguments[b]);return a}function c(a,b,c,d){var e=["opacity",b,~~(100*a),c,d].join("-"),f=.01+c/d*100,g=Math.max(1-(1-a)/b*(100-f),a),h=j.substring(0,j.indexOf("Animation")).toLowerCase(),i=h&&"-"+h+"-"||"";return m[e]||(k.insertRule("@"+i+"keyframes "+e+"{0%{opacity:"+g+"}"+f+"%{opacity:"+a+"}"+(f+.01)+"%{opacity:1}"+(f+b)%100+"%{opacity:"+a+"}100%{opacity:"+g+"}}",k.cssRules.length),m[e]=1),e}function d(a,b){var c,d,e=a.style;if(b=b.charAt(0).toUpperCase()+b.slice(1),void 0!==e[b])return b;for(d=0;d<l.length;d++)if(c=l[d]+b,void 0!==e[c])return c}function e(a,b){for(var c in b)a.style[d(a,c)||c]=b[c];return a}function f(a){for(var b=1;b<arguments.length;b++){var c=arguments[b];for(var d in c)void 0===a[d]&&(a[d]=c[d])}return a}function g(a,b){return"string"==typeof a?a:a[b%a.length]}function h(a){this.opts=f(a||{},h.defaults,n)}function i(){function c(b,c){return a("<"+b+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',c)}k.addRule(".spin-vml","behavior:url(#default#VML)"),h.prototype.lines=function(a,d){function f(){return e(c("group",{coordsize:k+" "+k,coordorigin:-j+" "+-j}),{width:k,height:k})}function h(a,h,i){b(m,b(e(f(),{rotation:360/d.lines*a+"deg",left:~~h}),b(e(c("roundrect",{arcsize:d.corners}),{width:j,height:d.scale*d.width,left:d.scale*d.radius,top:-d.scale*d.width>>1,filter:i}),c("fill",{color:g(d.color,a),opacity:d.opacity}),c("stroke",{opacity:0}))))}var i,j=d.scale*(d.length+d.width),k=2*d.scale*j,l=-(d.width+d.length)*d.scale*2+"px",m=e(f(),{position:"absolute",top:l,left:l});if(d.shadow)for(i=1;i<=d.lines;i++)h(i,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(i=1;i<=d.lines;i++)h(i);return b(a,m)},h.prototype.opacity=function(a,b,c,d){var e=a.firstChild;d=d.shadow&&d.lines||0,e&&b+d<e.childNodes.length&&(e=e.childNodes[b+d],e=e&&e.firstChild,e=e&&e.firstChild,e&&(e.opacity=c))}}var j,k,l=["webkit","Moz","ms","O"],m={},n={lines:12,length:7,width:5,radius:10,scale:1,corners:1,color:"#000",opacity:.25,rotate:0,direction:1,speed:1,trail:100,fps:20,zIndex:2e9,className:"spinner",top:"50%",left:"50%",shadow:!1,hwaccel:!1,position:"absolute"};if(h.defaults={},f(h.prototype,{spin:function(b){this.stop();var c=this,d=c.opts,f=c.el=a(null,{className:d.className});if(e(f,{position:d.position,width:0,zIndex:d.zIndex,left:d.left,top:d.top}),b&&b.insertBefore(f,b.firstChild||null),f.setAttribute("role","progressbar"),c.lines(f,c.opts),!j){var g,h=0,i=(d.lines-1)*(1-d.direction)/2,k=d.fps,l=k/d.speed,m=(1-d.opacity)/(l*d.trail/100),n=l/d.lines;!function o(){h++;for(var a=0;a<d.lines;a++)g=Math.max(1-(h+(d.lines-a)*n)%l*m,d.opacity),c.opacity(f,a*d.direction+i,g,d);c.timeout=c.el&&setTimeout(o,~~(1e3/k))}()}return c},stop:function(){var a=this.el;return a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=void 0),this},lines:function(d,f){function h(b,c){return e(a(),{position:"absolute",width:f.scale*(f.length+f.width)+"px",height:f.scale*f.width+"px",background:b,boxShadow:c,transformOrigin:"left",transform:"rotate("+~~(360/f.lines*k+f.rotate)+"deg) translate("+f.scale*f.radius+"px,0)",borderRadius:(f.corners*f.scale*f.width>>1)+"px"})}for(var i,k=0,l=(f.lines-1)*(1-f.direction)/2;k<f.lines;k++)i=e(a(),{position:"absolute",top:1+~(f.scale*f.width/2)+"px",transform:f.hwaccel?"translate3d(0,0,0)":"",opacity:f.opacity,animation:j&&c(f.opacity,f.trail,l+k*f.direction,f.lines)+" "+1/f.speed+"s linear infinite"}),f.shadow&&b(i,e(h("#000","0 0 4px #000"),{top:"2px"})),b(d,b(i,h(g(f.color,k),"0 0 1px rgba(0,0,0,.1)")));return d},opacity:function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)}}),"undefined"!=typeof document){k=function(){var c=a("style",{type:"text/css"});return b(document.getElementsByTagName("head")[0],c),c.sheet||c.styleSheet}();var o=e(a("group"),{behavior:"url(#default#VML)"});!d(o,"transform")&&o.adj?i():j=d(o,"animation")}return h});
/**
 * Copyright (C) 2011-2012 Pavel Shramov
 * Copyright (C) 2013-2017 Maxime Petazzoni <maxime.petazzoni@bulix.org>
 * All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * Thanks to Pavel Shramov who provided the initial implementation and Leaflet
 * integration. Original code was at https://github.com/shramov/leaflet-plugins.
 *
 * It was then cleaned-up and modified to record and make available more
 * information about the GPX track while it is being parsed so that the result
 * can be used to display additional information about the track that is
 * rendered on the Leaflet map.
 */

var L = L || require('leaflet');

var _MAX_POINT_INTERVAL_MS = 15000;
var _SECOND_IN_MILLIS = 1000;
var _MINUTE_IN_MILLIS = 60 * _SECOND_IN_MILLIS;
var _HOUR_IN_MILLIS = 60 * _MINUTE_IN_MILLIS;
var _DAY_IN_MILLIS = 24 * _HOUR_IN_MILLIS;

var _GPX_STYLE_NS = 'http://www.topografix.com/GPX/gpx_style/0/2';

var _DEFAULT_MARKER_OPTS = {
  startIconUrl: 'pin-icon-start.png',
  endIconUrl: 'pin-icon-end.png',
  shadowUrl: 'pin-shadow.png',
  wptIcons: [],
  wptIconsType: [],
  wptIconUrls : {
    '': 'pin-icon-wpt.png',
  },
  wptIconTypeUrls : {
    '': 'pin-icon-wpt.png',
  },
  pointMatchers: [],
  iconSize: [33, 45],
  shadowSize: [50, 50],
  iconAnchor: [16, 45],
  shadowAnchor: [16, 47],
  clickable: false
};
var _DEFAULT_POLYLINE_OPTS = {
  color: 'blue'
};
var _DEFAULT_GPX_OPTS = {
  parseElements: ['track', 'route', 'waypoint'],
  joinTrackSegments: true
};

L.GPX = L.FeatureGroup.extend({
  initialize: function(gpx, options) {
    options.max_point_interval = options.max_point_interval || _MAX_POINT_INTERVAL_MS;
    options.marker_options = this._merge_objs(
      _DEFAULT_MARKER_OPTS,
      options.marker_options || {});
    options.polyline_options = options.polyline_options || {};
    options.gpx_options = this._merge_objs(
      _DEFAULT_GPX_OPTS,
      options.gpx_options || {});

    L.Util.setOptions(this, options);

    // Base icon class for track pins.
    L.GPXTrackIcon = L.Icon.extend({ options: options.marker_options });

    this._gpx = gpx;
    this._layers = {};
    this._init_info();

    if (gpx) {
      this._parse(gpx, options, this.options.async);
    }
  },

  get_duration_string: function(duration, hidems) {
    var s = '';

    if (duration >= _DAY_IN_MILLIS) {
      s += Math.floor(duration / _DAY_IN_MILLIS) + 'd ';
      duration = duration % _DAY_IN_MILLIS;
    }

    if (duration >= _HOUR_IN_MILLIS) {
      s += Math.floor(duration / _HOUR_IN_MILLIS) + ':';
      duration = duration % _HOUR_IN_MILLIS;
    }

    var mins = Math.floor(duration / _MINUTE_IN_MILLIS);
    duration = duration % _MINUTE_IN_MILLIS;
    if (mins < 10) s += '0';
    s += mins + '\'';

    var secs = Math.floor(duration / _SECOND_IN_MILLIS);
    duration = duration % _SECOND_IN_MILLIS;
    if (secs < 10) s += '0';
    s += secs;

    if (!hidems && duration > 0) s += '.' + Math.round(Math.floor(duration)*1000)/1000;
    else s += '"';

    return s;
  },

  get_duration_string_iso: function(duration, hidems) {
    var s = this.get_duration_string(duration, hidems);
    return s.replace("'",':').replace('"','');
  },

  // Public methods
  to_miles:            function(v) { return v / 1.60934; },
  to_ft:               function(v) { return v * 3.28084; },
  m_to_km:             function(v) { return v / 1000; },
  m_to_mi:             function(v) { return v / 1609.34; },
  ms_to_kmh:           function(v) { return v * 3.6; },
  ms_to_mih:           function(v) { return v / 1609.34 * 3600; },

  get_name:            function() { return this._info.name; },
  get_desc:            function() { return this._info.desc; },
  get_author:          function() { return this._info.author; },
  get_copyright:       function() { return this._info.copyright; },
  get_distance:        function() { return this._info.length; },
  get_distance_imp:    function() { return this.to_miles(this.m_to_km(this.get_distance())); },

  get_start_time:      function() { return this._info.duration.start; },
  get_end_time:        function() { return this._info.duration.end; },
  get_moving_time:     function() { return this._info.duration.moving; },
  get_total_time:      function() { return this._info.duration.total; },

  get_moving_pace:     function() { return this.get_moving_time() / this.m_to_km(this.get_distance()); },
  get_moving_pace_imp: function() { return this.get_moving_time() / this.get_distance_imp(); },

  get_moving_speed:    function() { return this.m_to_km(this.get_distance()) / (this.get_moving_time() / (3600 * 1000)) ; },
  get_moving_speed_imp:function() { return this.to_miles(this.m_to_km(this.get_distance())) / (this.get_moving_time() / (3600 * 1000)) ; },

  get_total_speed:     function() { return this.m_to_km(this.get_distance()) / (this.get_total_time() / (3600 * 1000)); },
  get_total_speed_imp: function() { return this.to_miles(this.m_to_km(this.get_distance())) / (this.get_total_time() / (3600 * 1000)); },

  get_elevation_gain:     function() { return this._info.elevation.gain; },
  get_elevation_loss:     function() { return this._info.elevation.loss; },
  get_elevation_gain_imp: function() { return this.to_ft(this.get_elevation_gain()); },
  get_elevation_loss_imp: function() { return this.to_ft(this.get_elevation_loss()); },
  get_elevation_data:     function() {
    var _this = this;
    return this._info.elevation._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_km, null,
        function(a, b) { return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' m'; });
      });
  },
  get_elevation_data_imp: function() {
    var _this = this;
    return this._info.elevation._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_mi, _this.to_ft,
        function(a, b) { return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' ft'; });
      });
  },
  get_elevation_max:      function() { return this._info.elevation.max; },
  get_elevation_min:      function() { return this._info.elevation.min; },
  get_elevation_max_imp:  function() { return this.to_ft(this.get_elevation_max()); },
  get_elevation_min_imp:  function() { return this.to_ft(this.get_elevation_min()); },

  get_speed_data:         function() {
    var _this = this;
    return this._info.speed._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_km, _this.ms_to_kmh,
        function(a, b) { return a.toFixed(2) + ' km, ' + b.toFixed(2) + ' km/h'; });
      });
  },
  get_speed_data_imp: function() {
    var _this = this;
    return this._info.speed._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_mi, _this.ms_to_mih,
        function(a, b) { return a.toFixed(2) + ' mi, ' + b.toFixed(2) + ' mi/h'; });
      });
  },
  get_speed_max:          function() { return this.m_to_km(this._info.speed.max) * 3600; },
  get_speed_max_imp:      function() { return this.to_miles(this.get_speed_max()); },

  get_average_hr:         function() { return this._info.hr.avg; },
  get_average_temp:         function() { return this._info.atemp.avg; },
  get_average_cadence:         function() { return this._info.cad.avg; },
  get_heartrate_data:     function() {
    var _this = this;
    return this._info.hr._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_km, null,
        function(a, b) { return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' bpm'; });
      });
  },
  get_heartrate_data_imp: function() {
    var _this = this;
    return this._info.hr._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_mi, null,
        function(a, b) { return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' bpm'; });
      });
  },
  get_cadence_data:     function() {
    var _this = this;
    return this._info.cad._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_km, null,
        function(a, b) { return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' rpm'; });
      });
  },
  get_temp_data:     function() {
    var _this = this;
    return this._info.atemp._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_km, null,
        function(a, b) { return a.toFixed(2) + ' km, ' + b.toFixed(0) + ' degrees'; });
      });
  },
  get_cadence_data_imp:     function() {
    var _this = this;
    return this._info.cad._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_mi, null,
        function(a, b) { return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' rpm'; });
      });
  },
  get_temp_data_imp:     function() {
    var _this = this;
    return this._info.atemp._points.map(
      function(p) { return _this._prepare_data_point(p, _this.m_to_mi, null,
        function(a, b) { return a.toFixed(2) + ' mi, ' + b.toFixed(0) + ' degrees'; });
      });
  },

  reload: function() {
    this._init_info();
    this.clearLayers();
    this._parse(this._gpx, this.options, this.options.async);
  },

  // Private methods
  _merge_objs: function(a, b) {
    var _ = {};
    for (var attr in a) { _[attr] = a[attr]; }
    for (var attr in b) { _[attr] = b[attr]; }
    return _;
  },

  _prepare_data_point: function(p, trans1, trans2, trans_tooltip) {
    var r = [trans1 && trans1(p[0]) || p[0], trans2 && trans2(p[1]) || p[1]];
    r.push(trans_tooltip && trans_tooltip(r[0], r[1]) || (r[0] + ': ' + r[1]));
    return r;
  },

  _init_info: function() {
    this._info = {
      name: null,
      length: 0.0,
      elevation: {gain: 0.0, loss: 0.0, max: 0.0, min: Infinity, _points: []},
      speed : {max: 0.0, _points: []},
      hr: {avg: 0, _total: 0, _points: []},
      duration: {start: null, end: null, moving: 0, total: 0},
      atemp: {avg: 0, _total: 0, _points: []},
      cad: {avg: 0, _total: 0, _points: []}
    };
  },

  _load_xml: function(url, cb, options, async) {
    if (async == undefined) async = this.options.async;
    if (options == undefined) options = this.options;

    var req = new window.XMLHttpRequest();
    req.open('GET', url, async);
    try {
      req.overrideMimeType('text/xml'); // unsupported by IE
    } catch(e) {}
    req.onreadystatechange = function() {
      if (req.readyState != 4) return;
      if(req.status == 200) cb(req.responseXML, options);
    };
    req.send(null);
  },

  _parse: function(input, options, async) {
    var _this = this;
    var cb = function(gpx, options) {
      var layers = _this._parse_gpx_data(gpx, options);
      if (!layers) {
        _this.fire('error', { err: 'No parseable layers of type(s) ' + JSON.stringify(options.gpx_options.parseElements) });
        return;
      }
      _this.addLayer(layers);
      _this.fire('loaded', { layers: layers, element: gpx });
    }
    if (input.substr(0,1)==='<') { // direct XML has to start with a <
      var parser = new DOMParser();
      if (async) {
        setTimeout(function() {
          cb(parser.parseFromString(input, "text/xml"), options);
        });
      } else {
        cb(parser.parseFromString(input, "text/xml"), options);
      }
    } else {
      this._load_xml(input, cb, options, async);
    }
  },

  _parse_gpx_data: function(xml, options) {
    var i, t, l, el, layers = [];

    var name = xml.getElementsByTagName('name');
    if (name.length > 0) {
      this._info.name = name[0].textContent;
    }
    var desc = xml.getElementsByTagName('desc');
    if (desc.length > 0) {
      this._info.desc = desc[0].textContent;
    }
    var author = xml.getElementsByTagName('author');
    if (author.length > 0) {
      this._info.author = author[0].textContent;
    }
    var copyright = xml.getElementsByTagName('copyright');
    if (copyright.length > 0) {
      this._info.copyright = copyright[0].textContent;
    }

    var parseElements = options.gpx_options.parseElements;
    if (parseElements.indexOf('route') > -1) {
      // routes are <rtept> tags inside <rte> sections
      var routes = xml.getElementsByTagName('rte');
      for (i = 0; i < routes.length; i++) {
        layers = layers.concat(this._parse_segment(routes[i], options, {}, 'rtept'));
      }
    }

    if (parseElements.indexOf('track') > -1) {
      // tracks are <trkpt> tags in one or more <trkseg> sections in each <trk>
      var tracks = xml.getElementsByTagName('trk');
      for (i = 0; i < tracks.length; i++) {
        var track = tracks[i];
        var polyline_options = this._extract_styling(track);

        if (options.gpx_options.joinTrackSegments) {
          layers = layers.concat(this._parse_segment(track, options, polyline_options, 'trkpt'));
        } else {
          var segments = track.getElementsByTagName('trkseg');
          for (j = 0; j < segments.length; j++) {
            layers = layers.concat(this._parse_segment(segments[j], options, polyline_options, 'trkpt'));
          }
        }
      }
    }

    this._info.hr.avg = Math.round(this._info.hr._total / this._info.hr._points.length);
    this._info.cad.avg = Math.round(this._info.cad._total / this._info.cad._points.length);
    this._info.atemp.avg = Math.round(this._info.atemp._total / this._info.atemp._points.length);

    // parse waypoints and add markers for each of them
    if (parseElements.indexOf('waypoint') > -1) {
      el = xml.getElementsByTagName('wpt');
      for (i = 0; i < el.length; i++) {
        var ll = new L.LatLng(
            el[i].getAttribute('lat'),
            el[i].getAttribute('lon'));

        var nameEl = el[i].getElementsByTagName('name');
        var name = nameEl.length > 0 ? nameEl[0].textContent : '';

        var descEl = el[i].getElementsByTagName('desc');
        var desc = descEl.length > 0 ? descEl[0].textContent : '';

        var symEl = el[i].getElementsByTagName('sym');
        var symKey = symEl.length > 0 ? symEl[0].textContent : null;

        var typeEl = el[i].getElementsByTagName('type');
        var typeKey = typeEl.length > 0 ? typeEl[0].textContent : null;

        /*
         * Add waypoint marker based on the waypoint symbol key.
         *
         * First look for a configured icon for that symKey. If not found, look
         * for a configured icon URL for that symKey and build an icon from it.
         * If none of those match, look through the point matchers for a match
         * on the waypoint's name.
         *
         * Otherwise, fall back to the default icon if one was configured, or
         * finally to the default icon URL, if one was configured.
         */
        var wptIcons = options.marker_options.wptIcons;
        var wptIconUrls = options.marker_options.wptIconUrls;
        var wptIconsType = options.marker_options.wptIconsType;
        var wptIconTypeUrls = options.marker_options.wptIconTypeUrls;
        var ptMatchers = options.marker_options.pointMatchers || [];
        var symIcon;
        if (wptIcons && symKey && wptIcons[symKey]) {
          symIcon = wptIcons[symKey];
        } else if (wptIconsType && typeKey && wptIconsType[typeKey]) {
          symIcon = wptIconsType[typeKey];
        } else if (wptIconUrls && symKey && wptIconUrls[symKey]) {
          symIcon = new L.GPXTrackIcon({iconUrl: wptIconUrls[symKey]});
        } else if (wptIconTypeUrls && typeKey && wptIconTypeUrls[typeKey]) {
          symIcon = new L.GPXTrackIcon({iconUrl: wptIconTypeUrls[typeKey]});
        } else if (ptMatchers.length > 0) {
          for (var j = 0; j < ptMatchers.length; j++) {
            if (ptMatchers[j].regex.test(name)) {
              symIcon = ptMatchers[j].icon;
              break;
            }
          }
        } else if (wptIcons && wptIcons['']) {
          symIcon = wptIcons[''];
        } else if (wptIconUrls && wptIconUrls['']) {
          symIcon = new L.GPXTrackIcon({iconUrl: wptIconUrls['']});
        }

        if (!symIcon) {
          console.log(
            'No waypoint icon could be matched for symKey=%s,typeKey=%s,name=%s on waypoint %o',
            symKey, typeKey, name, el[i]);
          continue;
        }

        var marker = new L.Marker(ll, {
          clickable: options.marker_options.clickable,
          title: name,
          icon: symIcon,
          type: 'waypoint'
        });
        marker.bindPopup("<b>" + name + "</b>" + (desc.length > 0 ? '<br>' + desc : '')).openPopup();
        this.fire('addpoint', { point: marker, point_type: 'waypoint', element: el[i] });
        layers.push(marker);
      }
    }

    if (layers.length > 1) {
       return new L.FeatureGroup(layers);
    } else if (layers.length == 1) {
      return layers[0];
    }
  },

  _parse_segment: function(line, options, polyline_options, tag) {
    var el = line.getElementsByTagName(tag);
    if (!el.length) return [];

    var coords = [];
    var markers = [];
    var layers = [];
    var last = null;

    for (var i = 0; i < el.length; i++) {
      var _, ll = new L.LatLng(
        el[i].getAttribute('lat'),
        el[i].getAttribute('lon'));
      ll.meta = { time: null, ele: null, hr: null, cad: null, atemp: null, speed: null };

      _ = el[i].getElementsByTagName('time');
      if (_.length > 0) {
        ll.meta.time = new Date(Date.parse(_[0].textContent));
      } else {
        ll.meta.time = new Date('1970-01-01T00:00:00');
      }
      var time_diff = last != null ? Math.abs(ll.meta.time - last.meta.time) : 0;

      _ = el[i].getElementsByTagName('ele');
      if (_.length > 0) {
        ll.meta.ele = parseFloat(_[0].textContent);
      } else {
        // If the point doesn't have an <ele> tag, assume it has the same
        // elevation as the point before it (if it had one).
        ll.meta.ele = last != null ? last.meta.ele : null;
      }
      var ele_diff = last != null ? ll.meta.ele - last.meta.ele : 0;
      var dist_3d = last != null ? this._dist3d(last, ll) : 0;

      _ = el[i].getElementsByTagName('speed');
      if (_.length > 0) {
        ll.meta.speed = parseFloat(_[0].textContent);
      } else {
        // speed in meter per second
        ll.meta.speed = time_diff > 0 ? 1000.0 * dist_3d / time_diff : 0;
      }

      _ = el[i].getElementsByTagName('name');
      if (_.length > 0) {
        var name = _[0].textContent;
        var ptMatchers = options.marker_options.pointMatchers || [];

        for (var j = 0; j < ptMatchers.length; j++) {
          if (ptMatchers[j].regex.test(name)) {
            markers.push({ label: name, coords: ll, icon: ptMatchers[j].icon, element: el[i] });
            break;
          }
        }
      }

      _ = el[i].getElementsByTagNameNS('*', 'hr');
      if (_.length > 0) {
        ll.meta.hr = parseInt(_[0].textContent);
        this._info.hr._points.push([this._info.length, ll.meta.hr]);
        this._info.hr._total += ll.meta.hr;
      }

      _ = el[i].getElementsByTagNameNS('*', 'cad');
      if (_.length > 0) {
        ll.meta.cad = parseInt(_[0].textContent);
        this._info.cad._points.push([this._info.length, ll.meta.cad]);
        this._info.cad._total += ll.meta.cad;
      }

      _ = el[i].getElementsByTagNameNS('*', 'atemp');
      if (_.length > 0) {
        ll.meta.atemp = parseInt(_[0].textContent);
        this._info.atemp._points.push([this._info.length, ll.meta.atemp]);
        this._info.atemp._total += ll.meta.atemp;
      }

      if (ll.meta.ele > this._info.elevation.max) {
        this._info.elevation.max = ll.meta.ele;
      }
      if (ll.meta.ele < this._info.elevation.min) {
        this._info.elevation.min = ll.meta.ele;
      }
      this._info.elevation._points.push([this._info.length, ll.meta.ele]);

      if (ll.meta.speed > this._info.speed.max) {
        this._info.speed.max = ll.meta.speed;
      }
      this._info.speed._points.push([this._info.length, ll.meta.speed]);

      if ((last == null) && (this._info.duration.start == null)) {
        this._info.duration.start = ll.meta.time;
      }
      this._info.duration.end = ll.meta.time;
      this._info.duration.total += time_diff;
      if (time_diff < options.max_point_interval) {
        this._info.duration.moving += time_diff;
      }

      this._info.length += dist_3d;

      if (ele_diff > 0) {
        this._info.elevation.gain += ele_diff;
      } else {
        this._info.elevation.loss += Math.abs(ele_diff);
      }

      last = ll;
      coords.push(ll);
    }

    // add track
    var l = new L.Polyline(coords, this._extract_styling(line, polyline_options, options.polyline_options));
    this.fire('addline', { line: l, element: line });
    layers.push(l);

    if (options.marker_options.startIcon || options.marker_options.startIconUrl) {
      // add start pin
      var marker = new L.Marker(coords[0], {
        clickable: options.marker_options.clickable,
        icon: options.marker_options.startIcon || new L.GPXTrackIcon({iconUrl: options.marker_options.startIconUrl})
      });
      this.fire('addpoint', { point: marker, point_type: 'start', element: el[0] });
      layers.push(marker);
    }

    if (options.marker_options.endIcon || options.marker_options.endIconUrl) {
      // add end pin
      var marker = new L.Marker(coords[coords.length-1], {
        clickable: options.marker_options.clickable,
        icon: options.marker_options.endIcon || new L.GPXTrackIcon({iconUrl: options.marker_options.endIconUrl})
      });
      this.fire('addpoint', { point: marker, point_type: 'end', element: el[el.length-1] });
      layers.push(marker);
    }

    // add named markers
    for (var i = 0; i < markers.length; i++) {
      var marker = new L.Marker(markers[i].coords, {
        clickable: options.marker_options.clickable,
        title: markers[i].label,
        icon: markers[i].icon
      });
      this.fire('addpoint', { point: marker, point_type: 'label', element: markers[i].element });
      layers.push(marker);
    }

    return layers;
  },

  _extract_styling: function(el, base, overrides) {
    var style = this._merge_objs(_DEFAULT_POLYLINE_OPTS, base);
    var e = el.getElementsByTagNameNS(_GPX_STYLE_NS, 'line');
    if (e.length > 0) {
      var _ = e[0].getElementsByTagName('color');
      if (_.length > 0) style.color = '#' + _[0].textContent;
      var _ = e[0].getElementsByTagName('opacity');
      if (_.length > 0) style.opacity = _[0].textContent;
      var _ = e[0].getElementsByTagName('weight');
      if (_.length > 0) style.weight = _[0].textContent;
      var _ = e[0].getElementsByTagName('linecap');
      if (_.length > 0) style.lineCap = _[0].textContent;
      var _ = e[0].getElementsByTagName('linejoin');
      if (_.length > 0) style.lineJoin = _[0].textContent;
      var _ = e[0].getElementsByTagName('dasharray');
      if (_.length > 0) style.dashArray = _[0].textContent;
      var _ = e[0].getElementsByTagName('dashoffset');
      if (_.length > 0) style.dashOffset = _[0].textContent;
    }
    return this._merge_objs(style, overrides)
  },

  _dist2d: function(a, b) {
    var R = 6371000;
    var dLat = this._deg2rad(b.lat - a.lat);
    var dLon = this._deg2rad(b.lng - a.lng);
    var r = Math.sin(dLat/2) *
      Math.sin(dLat/2) +
      Math.cos(this._deg2rad(a.lat)) *
      Math.cos(this._deg2rad(b.lat)) *
      Math.sin(dLon/2) *
      Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(r), Math.sqrt(1-r));
    var d = R * c;
    return d;
  },

  _dist3d: function(a, b) {
    var planar = this._dist2d(a, b);
    var height = Math.abs(b.meta.ele - a.meta.ele);
    return Math.sqrt(Math.pow(planar, 2) + Math.pow(height, 2));
  },

  _deg2rad: function(deg) {
    return deg * Math.PI / 180;
  }
});

if (typeof module === 'object' && typeof module.exports === 'object') {
  module.exports = L;
} else if (typeof define === 'function' && define.amd) {
  define(L);
}

!function(n,i){"function"==typeof define&&define.amd?define(["leaflet","spin.js"],function(i,t){n(i,t)}):"object"==typeof exports?module.exports=function(i,t){return void 0===i&&(i=require("leaflet")),void 0===t&&(t=require("spin.js")),n(i,t),i}:void 0!==i&&i.L&&i.Spinner&&n(i.L,i.Spinner)}(function(n,i){var t={spin:function(n,t){n?(this._spinner||(this._spinner=new i(t).spin(this._container),this._spinning=0),this._spinning++):--this._spinning<=0&&this._spinner&&(this._spinner.stop(),this._spinner=null)}},e=function(){this.on("layeradd",function(n){n.layer.loading&&this.spin(!0),"function"==typeof n.layer.on&&(n.layer.on("data:loading",function(){this.spin(!0)},this),n.layer.on("data:loaded",function(){this.spin(!1)},this))},this),this.on("layerremove",function(n){n.layer.loading&&this.spin(!1),"function"==typeof n.layer.on&&(n.layer.off("data:loaded"),n.layer.off("data:loading"))},this)}
n.Map.include(t),n.Map.addInitHook(e)},window)

!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?t(exports):"function"==typeof define&&define.amd?define(["exports"],t):t(((e=e||self).Leaflet=e.Leaflet||{},e.Leaflet.markercluster={}))}(this,function(e){"use strict";var t=L.MarkerClusterGroup=L.FeatureGroup.extend({options:{maxClusterRadius:80,iconCreateFunction:null,clusterPane:L.Marker.prototype.options.pane,spiderfyOnEveryZoom:!1,spiderfyOnMaxZoom:!0,showCoverageOnHover:!0,zoomToBoundsOnClick:!0,singleMarkerMode:!1,disableClusteringAtZoom:null,removeOutsideVisibleBounds:!0,animate:!0,animateAddingMarkers:!1,spiderfyShapePositions:null,spiderfyDistanceMultiplier:1,spiderLegPolylineOptions:{weight:1.5,color:"#222",opacity:.5},chunkedLoading:!1,chunkInterval:200,chunkDelay:50,chunkProgress:null,polygonOptions:{}},initialize:function(e){L.Util.setOptions(this,e),this.options.iconCreateFunction||(this.options.iconCreateFunction=this._defaultIconCreateFunction),this._featureGroup=L.featureGroup(),this._featureGroup.addEventParent(this),this._nonPointGroup=L.featureGroup(),this._nonPointGroup.addEventParent(this),this._inZoomAnimation=0,this._needsClustering=[],this._needsRemoving=[],this._currentShownBounds=null,this._queue=[],this._childMarkerEventHandlers={dragstart:this._childMarkerDragStart,move:this._childMarkerMoved,dragend:this._childMarkerDragEnd};var t=L.DomUtil.TRANSITION&&this.options.animate;L.extend(this,t?this._withAnimation:this._noAnimation),this._markerCluster=t?L.MarkerCluster:L.MarkerClusterNonAnimated},addLayer:function(e){if(e instanceof L.LayerGroup)return this.addLayers([e]);if(!e.getLatLng)return this._nonPointGroup.addLayer(e),this.fire("layeradd",{layer:e}),this;if(!this._map)return this._needsClustering.push(e),this.fire("layeradd",{layer:e}),this;if(this.hasLayer(e))return this;this._unspiderfy&&this._unspiderfy(),this._addLayer(e,this._maxZoom),this.fire("layeradd",{layer:e}),this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons();var t=e,i=this._zoom;if(e.__parent)for(;t.__parent._zoom>=i;)t=t.__parent;return this._currentShownBounds.contains(t.getLatLng())&&(this.options.animateAddingMarkers?this._animationAddLayer(e,t):this._animationAddLayerNonAnimated(e,t)),this},removeLayer:function(e){return e instanceof L.LayerGroup?this.removeLayers([e]):(e.getLatLng?this._map?e.__parent&&(this._unspiderfy&&(this._unspiderfy(),this._unspiderfyLayer(e)),this._removeLayer(e,!0),this.fire("layerremove",{layer:e}),this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),e.off(this._childMarkerEventHandlers,this),this._featureGroup.hasLayer(e)&&(this._featureGroup.removeLayer(e),e.clusterShow&&e.clusterShow())):(!this._arraySplice(this._needsClustering,e)&&this.hasLayer(e)&&this._needsRemoving.push({layer:e,latlng:e._latlng}),this.fire("layerremove",{layer:e})):(this._nonPointGroup.removeLayer(e),this.fire("layerremove",{layer:e})),this)},addLayers:function(n,s){if(!L.Util.isArray(n))return this.addLayer(n);var o,a=this._featureGroup,h=this._nonPointGroup,l=this.options.chunkedLoading,u=this.options.chunkInterval,_=this.options.chunkProgress,d=n.length,p=0,c=!0;if(this._map){var f=(new Date).getTime(),m=L.bind(function(){var e=(new Date).getTime();for(this._map&&this._unspiderfy&&this._unspiderfy();p<d;p++){if(l&&p%200==0){var t=(new Date).getTime()-e;if(u<t)break}if((o=n[p])instanceof L.LayerGroup)c&&(n=n.slice(),c=!1),this._extractNonGroupLayers(o,n),d=n.length;else if(o.getLatLng){if(!this.hasLayer(o)&&(this._addLayer(o,this._maxZoom),s||this.fire("layeradd",{layer:o}),o.__parent&&2===o.__parent.getChildCount())){var i=o.__parent.getAllChildMarkers(),r=i[0]===o?i[1]:i[0];a.removeLayer(r)}}else h.addLayer(o),s||this.fire("layeradd",{layer:o})}_&&_(p,d,(new Date).getTime()-f),p===d?(this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),this._topClusterLevel._recursivelyAddChildrenToMap(null,this._zoom,this._currentShownBounds)):setTimeout(m,this.options.chunkDelay)},this);m()}else for(var e=this._needsClustering;p<d;p++)(o=n[p])instanceof L.LayerGroup?(c&&(n=n.slice(),c=!1),this._extractNonGroupLayers(o,n),d=n.length):o.getLatLng?this.hasLayer(o)||e.push(o):h.addLayer(o);return this},removeLayers:function(e){var t,i,r=e.length,n=this._featureGroup,s=this._nonPointGroup,o=!0;if(!this._map){for(t=0;t<r;t++)(i=e[t])instanceof L.LayerGroup?(o&&(e=e.slice(),o=!1),this._extractNonGroupLayers(i,e),r=e.length):(this._arraySplice(this._needsClustering,i),s.removeLayer(i),this.hasLayer(i)&&this._needsRemoving.push({layer:i,latlng:i._latlng}),this.fire("layerremove",{layer:i}));return this}if(this._unspiderfy){this._unspiderfy();var a=e.slice(),h=r;for(t=0;t<h;t++)(i=a[t])instanceof L.LayerGroup?(this._extractNonGroupLayers(i,a),h=a.length):this._unspiderfyLayer(i)}for(t=0;t<r;t++)(i=e[t])instanceof L.LayerGroup?(o&&(e=e.slice(),o=!1),this._extractNonGroupLayers(i,e),r=e.length):i.__parent?(this._removeLayer(i,!0,!0),this.fire("layerremove",{layer:i}),n.hasLayer(i)&&(n.removeLayer(i),i.clusterShow&&i.clusterShow())):(s.removeLayer(i),this.fire("layerremove",{layer:i}));return this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),this._topClusterLevel._recursivelyAddChildrenToMap(null,this._zoom,this._currentShownBounds),this},clearLayers:function(){return this._map||(this._needsClustering=[],this._needsRemoving=[],delete this._gridClusters,delete this._gridUnclustered),this._noanimationUnspiderfy&&this._noanimationUnspiderfy(),this._featureGroup.clearLayers(),this._nonPointGroup.clearLayers(),this.eachLayer(function(e){e.off(this._childMarkerEventHandlers,this),delete e.__parent},this),this._map&&this._generateInitialClusters(),this},getBounds:function(){var e=new L.LatLngBounds;this._topClusterLevel&&e.extend(this._topClusterLevel._bounds);for(var t=this._needsClustering.length-1;0<=t;t--)e.extend(this._needsClustering[t].getLatLng());return e.extend(this._nonPointGroup.getBounds()),e},eachLayer:function(e,t){var i,r,n,s=this._needsClustering.slice(),o=this._needsRemoving;for(this._topClusterLevel&&this._topClusterLevel.getAllChildMarkers(s),r=s.length-1;0<=r;r--){for(i=!0,n=o.length-1;0<=n;n--)if(o[n].layer===s[r]){i=!1;break}i&&e.call(t,s[r])}this._nonPointGroup.eachLayer(e,t)},getLayers:function(){var t=[];return this.eachLayer(function(e){t.push(e)}),t},getLayer:function(t){var i=null;return t=parseInt(t,10),this.eachLayer(function(e){L.stamp(e)===t&&(i=e)}),i},hasLayer:function(e){if(!e)return!1;var t,i=this._needsClustering;for(t=i.length-1;0<=t;t--)if(i[t]===e)return!0;for(t=(i=this._needsRemoving).length-1;0<=t;t--)if(i[t].layer===e)return!1;return!(!e.__parent||e.__parent._group!==this)||this._nonPointGroup.hasLayer(e)},zoomToShowLayer:function(e,t){var i=this._map;"function"!=typeof t&&(t=function(){});var r=function(){!i.hasLayer(e)&&!i.hasLayer(e.__parent)||this._inZoomAnimation||(this._map.off("moveend",r,this),this.off("animationend",r,this),i.hasLayer(e)?t():e.__parent._icon&&(this.once("spiderfied",t,this),e.__parent.spiderfy()))};e._icon&&this._map.getBounds().contains(e.getLatLng())?t():e.__parent._zoom<Math.round(this._map._zoom)?(this._map.on("moveend",r,this),this._map.panTo(e.getLatLng())):(this._map.on("moveend",r,this),this.on("animationend",r,this),e.__parent.zoomToBounds())},onAdd:function(e){var t,i,r;if(this._map=e,!isFinite(this._map.getMaxZoom()))throw"Map has no maxZoom specified";for(this._featureGroup.addTo(e),this._nonPointGroup.addTo(e),this._gridClusters||this._generateInitialClusters(),this._maxLat=e.options.crs.projection.MAX_LATITUDE,t=0,i=this._needsRemoving.length;t<i;t++)(r=this._needsRemoving[t]).newlatlng=r.layer._latlng,r.layer._latlng=r.latlng;for(t=0,i=this._needsRemoving.length;t<i;t++)r=this._needsRemoving[t],this._removeLayer(r.layer,!0),r.layer._latlng=r.newlatlng;this._needsRemoving=[],this._zoom=Math.round(this._map._zoom),this._currentShownBounds=this._getExpandedVisibleBounds(),this._map.on("zoomend",this._zoomEnd,this),this._map.on("moveend",this._moveEnd,this),this._spiderfierOnAdd&&this._spiderfierOnAdd(),this._bindEvents(),i=this._needsClustering,this._needsClustering=[],this.addLayers(i,!0)},onRemove:function(e){e.off("zoomend",this._zoomEnd,this),e.off("moveend",this._moveEnd,this),this._unbindEvents(),this._map._mapPane.className=this._map._mapPane.className.replace(" leaflet-cluster-anim",""),this._spiderfierOnRemove&&this._spiderfierOnRemove(),delete this._maxLat,this._hideCoverage(),this._featureGroup.remove(),this._nonPointGroup.remove(),this._featureGroup.clearLayers(),this._map=null},getVisibleParent:function(e){for(var t=e;t&&!t._icon;)t=t.__parent;return t||null},_arraySplice:function(e,t){for(var i=e.length-1;0<=i;i--)if(e[i]===t)return e.splice(i,1),!0},_removeFromGridUnclustered:function(e,t){for(var i=this._map,r=this._gridUnclustered,n=Math.floor(this._map.getMinZoom());n<=t&&r[t].removeObject(e,i.project(e.getLatLng(),t));t--);},_childMarkerDragStart:function(e){e.target.__dragStart=e.target._latlng},_childMarkerMoved:function(e){if(!this._ignoreMove&&!e.target.__dragStart){var t=e.target._popup&&e.target._popup.isOpen();this._moveChild(e.target,e.oldLatLng,e.latlng),t&&e.target.openPopup()}},_moveChild:function(e,t,i){e._latlng=t,this.removeLayer(e),e._latlng=i,this.addLayer(e)},_childMarkerDragEnd:function(e){var t=e.target.__dragStart;delete e.target.__dragStart,t&&this._moveChild(e.target,t,e.target._latlng)},_removeLayer:function(e,t,i){var r=this._gridClusters,n=this._gridUnclustered,s=this._featureGroup,o=this._map,a=Math.floor(this._map.getMinZoom());t&&this._removeFromGridUnclustered(e,this._maxZoom);var h,l=e.__parent,u=l._markers;for(this._arraySplice(u,e);l&&(l._childCount--,l._boundsNeedUpdate=!0,!(l._zoom<a));)t&&l._childCount<=1?(h=l._markers[0]===e?l._markers[1]:l._markers[0],r[l._zoom].removeObject(l,o.project(l._cLatLng,l._zoom)),n[l._zoom].addObject(h,o.project(h.getLatLng(),l._zoom)),this._arraySplice(l.__parent._childClusters,l),l.__parent._markers.push(h),h.__parent=l.__parent,l._icon&&(s.removeLayer(l),i||s.addLayer(h))):l._iconNeedsUpdate=!0,l=l.__parent;delete e.__parent},_isOrIsParent:function(e,t){for(;t;){if(e===t)return!0;t=t.parentNode}return!1},fire:function(e,t,i){if(t&&t.layer instanceof L.MarkerCluster){if(t.originalEvent&&this._isOrIsParent(t.layer._icon,t.originalEvent.relatedTarget))return;e="cluster"+e}L.FeatureGroup.prototype.fire.call(this,e,t,i)},listens:function(e,t){return L.FeatureGroup.prototype.listens.call(this,e,t)||L.FeatureGroup.prototype.listens.call(this,"cluster"+e,t)},_defaultIconCreateFunction:function(e){var t=e.getChildCount(),i=" marker-cluster-";return i+=t<10?"small":t<100?"medium":"large",new L.DivIcon({html:"<div><span>"+t+"</span></div>",className:"marker-cluster"+i,iconSize:new L.Point(40,40)})},_bindEvents:function(){var e=this._map,t=this.options.spiderfyOnMaxZoom,i=this.options.showCoverageOnHover,r=this.options.zoomToBoundsOnClick,n=this.options.spiderfyOnEveryZoom;(t||r||n)&&this.on("clusterclick clusterkeypress",this._zoomOrSpiderfy,this),i&&(this.on("clustermouseover",this._showCoverage,this),this.on("clustermouseout",this._hideCoverage,this),e.on("zoomend",this._hideCoverage,this))},_zoomOrSpiderfy:function(e){var t=e.layer,i=t;if("clusterkeypress"!==e.type||!e.originalEvent||13===e.originalEvent.keyCode){for(;1===i._childClusters.length;)i=i._childClusters[0];i._zoom===this._maxZoom&&i._childCount===t._childCount&&this.options.spiderfyOnMaxZoom?t.spiderfy():this.options.zoomToBoundsOnClick&&t.zoomToBounds(),this.options.spiderfyOnEveryZoom&&t.spiderfy(),e.originalEvent&&13===e.originalEvent.keyCode&&this._map._container.focus()}},_showCoverage:function(e){var t=this._map;this._inZoomAnimation||(this._shownPolygon&&t.removeLayer(this._shownPolygon),2<e.layer.getChildCount()&&e.layer!==this._spiderfied&&(this._shownPolygon=new L.Polygon(e.layer.getConvexHull(),this.options.polygonOptions),t.addLayer(this._shownPolygon)))},_hideCoverage:function(){this._shownPolygon&&(this._map.removeLayer(this._shownPolygon),this._shownPolygon=null)},_unbindEvents:function(){var e=this.options.spiderfyOnMaxZoom,t=this.options.showCoverageOnHover,i=this.options.zoomToBoundsOnClick,r=this.options.spiderfyOnEveryZoom,n=this._map;(e||i||r)&&this.off("clusterclick clusterkeypress",this._zoomOrSpiderfy,this),t&&(this.off("clustermouseover",this._showCoverage,this),this.off("clustermouseout",this._hideCoverage,this),n.off("zoomend",this._hideCoverage,this))},_zoomEnd:function(){this._map&&(this._mergeSplitClusters(),this._zoom=Math.round(this._map._zoom),this._currentShownBounds=this._getExpandedVisibleBounds())},_moveEnd:function(){if(!this._inZoomAnimation){var e=this._getExpandedVisibleBounds();this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),this._zoom,e),this._topClusterLevel._recursivelyAddChildrenToMap(null,Math.round(this._map._zoom),e),this._currentShownBounds=e}},_generateInitialClusters:function(){var e=Math.ceil(this._map.getMaxZoom()),t=Math.floor(this._map.getMinZoom()),i=this.options.maxClusterRadius,r=i;"function"!=typeof i&&(r=function(){return i}),null!==this.options.disableClusteringAtZoom&&(e=this.options.disableClusteringAtZoom-1),this._maxZoom=e,this._gridClusters={},this._gridUnclustered={};for(var n=e;t<=n;n--)this._gridClusters[n]=new L.DistanceGrid(r(n)),this._gridUnclustered[n]=new L.DistanceGrid(r(n));this._topClusterLevel=new this._markerCluster(this,t-1)},_addLayer:function(e,t){var i,r,n=this._gridClusters,s=this._gridUnclustered,o=Math.floor(this._map.getMinZoom());for(this.options.singleMarkerMode&&this._overrideMarkerIcon(e),e.on(this._childMarkerEventHandlers,this);o<=t;t--){i=this._map.project(e.getLatLng(),t);var a=n[t].getNearObject(i);if(a)return a._addChild(e),void(e.__parent=a);if(a=s[t].getNearObject(i)){var h=a.__parent;h&&this._removeLayer(a,!1);var l=new this._markerCluster(this,t,a,e);n[t].addObject(l,this._map.project(l._cLatLng,t)),a.__parent=l;var u=e.__parent=l;for(r=t-1;r>h._zoom;r--)u=new this._markerCluster(this,r,u),n[r].addObject(u,this._map.project(a.getLatLng(),r));return h._addChild(u),void this._removeFromGridUnclustered(a,t)}s[t].addObject(e,i)}this._topClusterLevel._addChild(e),e.__parent=this._topClusterLevel},_refreshClustersIcons:function(){this._featureGroup.eachLayer(function(e){e instanceof L.MarkerCluster&&e._iconNeedsUpdate&&e._updateIcon()})},_enqueue:function(e){this._queue.push(e),this._queueTimeout||(this._queueTimeout=setTimeout(L.bind(this._processQueue,this),300))},_processQueue:function(){for(var e=0;e<this._queue.length;e++)this._queue[e].call(this);this._queue.length=0,clearTimeout(this._queueTimeout),this._queueTimeout=null},_mergeSplitClusters:function(){var e=Math.round(this._map._zoom);this._processQueue(),this._zoom<e&&this._currentShownBounds.intersects(this._getExpandedVisibleBounds())?(this._animationStart(),this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),this._zoom,this._getExpandedVisibleBounds()),this._animationZoomIn(this._zoom,e)):this._zoom>e?(this._animationStart(),this._animationZoomOut(this._zoom,e)):this._moveEnd()},_getExpandedVisibleBounds:function(){return this.options.removeOutsideVisibleBounds?L.Browser.mobile?this._checkBoundsMaxLat(this._map.getBounds()):this._checkBoundsMaxLat(this._map.getBounds().pad(1)):this._mapBoundsInfinite},_checkBoundsMaxLat:function(e){var t=this._maxLat;return void 0!==t&&(e.getNorth()>=t&&(e._northEast.lat=1/0),e.getSouth()<=-t&&(e._southWest.lat=-1/0)),e},_animationAddLayerNonAnimated:function(e,t){if(t===e)this._featureGroup.addLayer(e);else if(2===t._childCount){t._addToMap();var i=t.getAllChildMarkers();this._featureGroup.removeLayer(i[0]),this._featureGroup.removeLayer(i[1])}else t._updateIcon()},_extractNonGroupLayers:function(e,t){var i,r=e.getLayers(),n=0;for(t=t||[];n<r.length;n++)(i=r[n])instanceof L.LayerGroup?this._extractNonGroupLayers(i,t):t.push(i);return t},_overrideMarkerIcon:function(e){return e.options.icon=this.options.iconCreateFunction({getChildCount:function(){return 1},getAllChildMarkers:function(){return[e]}})}});L.MarkerClusterGroup.include({_mapBoundsInfinite:new L.LatLngBounds(new L.LatLng(-1/0,-1/0),new L.LatLng(1/0,1/0))}),L.MarkerClusterGroup.include({_noAnimation:{_animationStart:function(){},_animationZoomIn:function(e,t){this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this.fire("animationend")},_animationZoomOut:function(e,t){this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this.fire("animationend")},_animationAddLayer:function(e,t){this._animationAddLayerNonAnimated(e,t)}},_withAnimation:{_animationStart:function(){this._map._mapPane.className+=" leaflet-cluster-anim",this._inZoomAnimation++},_animationZoomIn:function(n,s){var o,a=this._getExpandedVisibleBounds(),h=this._featureGroup,e=Math.floor(this._map.getMinZoom());this._ignoreMove=!0,this._topClusterLevel._recursively(a,n,e,function(e){var t,i=e._latlng,r=e._markers;for(a.contains(i)||(i=null),e._isSingleParent()&&n+1===s?(h.removeLayer(e),e._recursivelyAddChildrenToMap(null,s,a)):(e.clusterHide(),e._recursivelyAddChildrenToMap(i,s,a)),o=r.length-1;0<=o;o--)t=r[o],a.contains(t._latlng)||h.removeLayer(t)}),this._forceLayout(),this._topClusterLevel._recursivelyBecomeVisible(a,s),h.eachLayer(function(e){e instanceof L.MarkerCluster||!e._icon||e.clusterShow()}),this._topClusterLevel._recursively(a,n,s,function(e){e._recursivelyRestoreChildPositions(s)}),this._ignoreMove=!1,this._enqueue(function(){this._topClusterLevel._recursively(a,n,e,function(e){h.removeLayer(e),e.clusterShow()}),this._animationEnd()})},_animationZoomOut:function(e,t){this._animationZoomOutSingle(this._topClusterLevel,e-1,t),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e,this._getExpandedVisibleBounds())},_animationAddLayer:function(e,t){var i=this,r=this._featureGroup;r.addLayer(e),t!==e&&(2<t._childCount?(t._updateIcon(),this._forceLayout(),this._animationStart(),e._setPos(this._map.latLngToLayerPoint(t.getLatLng())),e.clusterHide(),this._enqueue(function(){r.removeLayer(e),e.clusterShow(),i._animationEnd()})):(this._forceLayout(),i._animationStart(),i._animationZoomOutSingle(t,this._map.getMaxZoom(),this._zoom)))}},_animationZoomOutSingle:function(t,i,r){var n=this._getExpandedVisibleBounds(),s=Math.floor(this._map.getMinZoom());t._recursivelyAnimateChildrenInAndAddSelfToMap(n,s,i+1,r);var o=this;this._forceLayout(),t._recursivelyBecomeVisible(n,r),this._enqueue(function(){if(1===t._childCount){var e=t._markers[0];this._ignoreMove=!0,e.setLatLng(e.getLatLng()),this._ignoreMove=!1,e.clusterShow&&e.clusterShow()}else t._recursively(n,r,s,function(e){e._recursivelyRemoveChildrenFromMap(n,s,i+1)});o._animationEnd()})},_animationEnd:function(){this._map&&(this._map._mapPane.className=this._map._mapPane.className.replace(" leaflet-cluster-anim","")),this._inZoomAnimation--,this.fire("animationend")},_forceLayout:function(){L.Util.falseFn(document.body.offsetWidth)}}),L.markerClusterGroup=function(e){return new L.MarkerClusterGroup(e)};var i=L.MarkerCluster=L.Marker.extend({options:L.Icon.prototype.options,initialize:function(e,t,i,r){L.Marker.prototype.initialize.call(this,i?i._cLatLng||i.getLatLng():new L.LatLng(0,0),{icon:this,pane:e.options.clusterPane}),this._group=e,this._zoom=t,this._markers=[],this._childClusters=[],this._childCount=0,this._iconNeedsUpdate=!0,this._boundsNeedUpdate=!0,this._bounds=new L.LatLngBounds,i&&this._addChild(i),r&&this._addChild(r)},getAllChildMarkers:function(e,t){e=e||[];for(var i=this._childClusters.length-1;0<=i;i--)this._childClusters[i].getAllChildMarkers(e,t);for(var r=this._markers.length-1;0<=r;r--)t&&this._markers[r].__dragStart||e.push(this._markers[r]);return e},getChildCount:function(){return this._childCount},zoomToBounds:function(e){for(var t,i=this._childClusters.slice(),r=this._group._map,n=r.getBoundsZoom(this._bounds),s=this._zoom+1,o=r.getZoom();0<i.length&&s<n;){s++;var a=[];for(t=0;t<i.length;t++)a=a.concat(i[t]._childClusters);i=a}s<n?this._group._map.setView(this._latlng,s):n<=o?this._group._map.setView(this._latlng,o+1):this._group._map.fitBounds(this._bounds,e)},getBounds:function(){var e=new L.LatLngBounds;return e.extend(this._bounds),e},_updateIcon:function(){this._iconNeedsUpdate=!0,this._icon&&this.setIcon(this)},createIcon:function(){return this._iconNeedsUpdate&&(this._iconObj=this._group.options.iconCreateFunction(this),this._iconNeedsUpdate=!1),this._iconObj.createIcon()},createShadow:function(){return this._iconObj.createShadow()},_addChild:function(e,t){this._iconNeedsUpdate=!0,this._boundsNeedUpdate=!0,this._setClusterCenter(e),e instanceof L.MarkerCluster?(t||(this._childClusters.push(e),e.__parent=this),this._childCount+=e._childCount):(t||this._markers.push(e),this._childCount++),this.__parent&&this.__parent._addChild(e,!0)},_setClusterCenter:function(e){this._cLatLng||(this._cLatLng=e._cLatLng||e._latlng)},_resetBounds:function(){var e=this._bounds;e._southWest&&(e._southWest.lat=1/0,e._southWest.lng=1/0),e._northEast&&(e._northEast.lat=-1/0,e._northEast.lng=-1/0)},_recalculateBounds:function(){var e,t,i,r,n=this._markers,s=this._childClusters,o=0,a=0,h=this._childCount;if(0!==h){for(this._resetBounds(),e=0;e<n.length;e++)i=n[e]._latlng,this._bounds.extend(i),o+=i.lat,a+=i.lng;for(e=0;e<s.length;e++)(t=s[e])._boundsNeedUpdate&&t._recalculateBounds(),this._bounds.extend(t._bounds),i=t._wLatLng,r=t._childCount,o+=i.lat*r,a+=i.lng*r;this._latlng=this._wLatLng=new L.LatLng(o/h,a/h),this._boundsNeedUpdate=!1}},_addToMap:function(e){e&&(this._backupLatlng=this._latlng,this.setLatLng(e)),this._group._featureGroup.addLayer(this)},_recursivelyAnimateChildrenIn:function(e,n,t){this._recursively(e,this._group._map.getMinZoom(),t-1,function(e){var t,i,r=e._markers;for(t=r.length-1;0<=t;t--)(i=r[t])._icon&&(i._setPos(n),i.clusterHide())},function(e){var t,i,r=e._childClusters;for(t=r.length-1;0<=t;t--)(i=r[t])._icon&&(i._setPos(n),i.clusterHide())})},_recursivelyAnimateChildrenInAndAddSelfToMap:function(t,i,r,n){this._recursively(t,n,i,function(e){e._recursivelyAnimateChildrenIn(t,e._group._map.latLngToLayerPoint(e.getLatLng()).round(),r),e._isSingleParent()&&r-1===n?(e.clusterShow(),e._recursivelyRemoveChildrenFromMap(t,i,r)):e.clusterHide(),e._addToMap()})},_recursivelyBecomeVisible:function(e,t){this._recursively(e,this._group._map.getMinZoom(),t,null,function(e){e.clusterShow()})},_recursivelyAddChildrenToMap:function(r,n,s){this._recursively(s,this._group._map.getMinZoom()-1,n,function(e){if(n!==e._zoom)for(var t=e._markers.length-1;0<=t;t--){var i=e._markers[t];s.contains(i._latlng)&&(r&&(i._backupLatlng=i.getLatLng(),i.setLatLng(r),i.clusterHide&&i.clusterHide()),e._group._featureGroup.addLayer(i))}},function(e){e._addToMap(r)})},_recursivelyRestoreChildPositions:function(e){for(var t=this._markers.length-1;0<=t;t--){var i=this._markers[t];i._backupLatlng&&(i.setLatLng(i._backupLatlng),delete i._backupLatlng)}if(e-1===this._zoom)for(var r=this._childClusters.length-1;0<=r;r--)this._childClusters[r]._restorePosition();else for(var n=this._childClusters.length-1;0<=n;n--)this._childClusters[n]._recursivelyRestoreChildPositions(e)},_restorePosition:function(){this._backupLatlng&&(this.setLatLng(this._backupLatlng),delete this._backupLatlng)},_recursivelyRemoveChildrenFromMap:function(e,t,i,r){var n,s;this._recursively(e,t-1,i-1,function(e){for(s=e._markers.length-1;0<=s;s--)n=e._markers[s],r&&r.contains(n._latlng)||(e._group._featureGroup.removeLayer(n),n.clusterShow&&n.clusterShow())},function(e){for(s=e._childClusters.length-1;0<=s;s--)n=e._childClusters[s],r&&r.contains(n._latlng)||(e._group._featureGroup.removeLayer(n),n.clusterShow&&n.clusterShow())})},_recursively:function(e,t,i,r,n){var s,o,a=this._childClusters,h=this._zoom;if(t<=h&&(r&&r(this),n&&h===i&&n(this)),h<t||h<i)for(s=a.length-1;0<=s;s--)(o=a[s])._boundsNeedUpdate&&o._recalculateBounds(),e.intersects(o._bounds)&&o._recursively(e,t,i,r,n)},_isSingleParent:function(){return 0<this._childClusters.length&&this._childClusters[0]._childCount===this._childCount}});L.Marker.include({clusterHide:function(){var e=this.options.opacity;return this.setOpacity(0),this.options.opacity=e,this},clusterShow:function(){return this.setOpacity(this.options.opacity)}}),L.DistanceGrid=function(e){this._cellSize=e,this._sqCellSize=e*e,this._grid={},this._objectPoint={}},L.DistanceGrid.prototype={addObject:function(e,t){var i=this._getCoord(t.x),r=this._getCoord(t.y),n=this._grid,s=n[r]=n[r]||{},o=s[i]=s[i]||[],a=L.Util.stamp(e);this._objectPoint[a]=t,o.push(e)},updateObject:function(e,t){this.removeObject(e),this.addObject(e,t)},removeObject:function(e,t){var i,r,n=this._getCoord(t.x),s=this._getCoord(t.y),o=this._grid,a=o[s]=o[s]||{},h=a[n]=a[n]||[];for(delete this._objectPoint[L.Util.stamp(e)],i=0,r=h.length;i<r;i++)if(h[i]===e)return h.splice(i,1),1===r&&delete a[n],!0},eachObject:function(e,t){var i,r,n,s,o,a,h=this._grid;for(i in h)for(r in o=h[i])for(n=0,s=(a=o[r]).length;n<s;n++)e.call(t,a[n])&&(n--,s--)},getNearObject:function(e){var t,i,r,n,s,o,a,h,l=this._getCoord(e.x),u=this._getCoord(e.y),_=this._objectPoint,d=this._sqCellSize,p=null;for(t=u-1;t<=u+1;t++)if(n=this._grid[t])for(i=l-1;i<=l+1;i++)if(s=n[i])for(r=0,o=s.length;r<o;r++)a=s[r],((h=this._sqDist(_[L.Util.stamp(a)],e))<d||h<=d&&null===p)&&(d=h,p=a);return p},_getCoord:function(e){var t=Math.floor(e/this._cellSize);return isFinite(t)?t:e},_sqDist:function(e,t){var i=t.x-e.x,r=t.y-e.y;return i*i+r*r}},L.QuickHull={getDistant:function(e,t){var i=t[1].lat-t[0].lat;return(t[0].lng-t[1].lng)*(e.lat-t[0].lat)+i*(e.lng-t[0].lng)},findMostDistantPointFromBaseLine:function(e,t){var i,r,n,s=0,o=null,a=[];for(i=t.length-1;0<=i;i--)r=t[i],0<(n=this.getDistant(r,e))&&(a.push(r),s<n&&(s=n,o=r));return{maxPoint:o,newPoints:a}},buildConvexHull:function(e,t){var i=[],r=this.findMostDistantPointFromBaseLine(e,t);return r.maxPoint?i=(i=i.concat(this.buildConvexHull([e[0],r.maxPoint],r.newPoints))).concat(this.buildConvexHull([r.maxPoint,e[1]],r.newPoints)):[e[0]]},getConvexHull:function(e){var t,i=!1,r=!1,n=!1,s=!1,o=null,a=null,h=null,l=null,u=null,_=null;for(t=e.length-1;0<=t;t--){var d=e[t];(!1===i||d.lat>i)&&(i=(o=d).lat),(!1===r||d.lat<r)&&(r=(a=d).lat),(!1===n||d.lng>n)&&(n=(h=d).lng),(!1===s||d.lng<s)&&(s=(l=d).lng)}return u=r!==i?(_=a,o):(_=l,h),[].concat(this.buildConvexHull([_,u],e),this.buildConvexHull([u,_],e))}},L.MarkerCluster.include({getConvexHull:function(){var e,t,i=this.getAllChildMarkers(),r=[];for(t=i.length-1;0<=t;t--)e=i[t].getLatLng(),r.push(e);return L.QuickHull.getConvexHull(r)}}),L.MarkerCluster.include({_2PI:2*Math.PI,_circleFootSeparation:25,_circleStartAngle:0,_spiralFootSeparation:28,_spiralLengthStart:11,_spiralLengthFactor:5,_circleSpiralSwitchover:9,spiderfy:function(){if(this._group._spiderfied!==this&&!this._group._inZoomAnimation){var e,t=this.getAllChildMarkers(null,!0),i=this._group._map.latLngToLayerPoint(this._latlng);this._group._unspiderfy(),e=(this._group._spiderfied=this)._group.options.spiderfyShapePositions?this._group.options.spiderfyShapePositions(t.length,i):t.length>=this._circleSpiralSwitchover?this._generatePointsSpiral(t.length,i):(i.y+=10,this._generatePointsCircle(t.length,i)),this._animationSpiderfy(t,e)}},unspiderfy:function(e){this._group._inZoomAnimation||(this._animationUnspiderfy(e),this._group._spiderfied=null)},_generatePointsCircle:function(e,t){var i,r,n=this._group.options.spiderfyDistanceMultiplier*this._circleFootSeparation*(2+e)/this._2PI,s=this._2PI/e,o=[];for(n=Math.max(n,35),o.length=e,i=0;i<e;i++)r=this._circleStartAngle+i*s,o[i]=new L.Point(t.x+n*Math.cos(r),t.y+n*Math.sin(r))._round();return o},_generatePointsSpiral:function(e,t){var i,r=this._group.options.spiderfyDistanceMultiplier,n=r*this._spiralLengthStart,s=r*this._spiralFootSeparation,o=r*this._spiralLengthFactor*this._2PI,a=0,h=[];for(i=h.length=e;0<=i;i--)i<e&&(h[i]=new L.Point(t.x+n*Math.cos(a),t.y+n*Math.sin(a))._round()),n+=o/(a+=s/n+5e-4*i);return h},_noanimationUnspiderfy:function(){var e,t,i=this._group,r=i._map,n=i._featureGroup,s=this.getAllChildMarkers(null,!0);for(i._ignoreMove=!0,this.setOpacity(1),t=s.length-1;0<=t;t--)e=s[t],n.removeLayer(e),e._preSpiderfyLatlng&&(e.setLatLng(e._preSpiderfyLatlng),delete e._preSpiderfyLatlng),e.setZIndexOffset&&e.setZIndexOffset(0),e._spiderLeg&&(r.removeLayer(e._spiderLeg),delete e._spiderLeg);i.fire("unspiderfied",{cluster:this,markers:s}),i._ignoreMove=!1,i._spiderfied=null}}),L.MarkerClusterNonAnimated=L.MarkerCluster.extend({_animationSpiderfy:function(e,t){var i,r,n,s,o=this._group,a=o._map,h=o._featureGroup,l=this._group.options.spiderLegPolylineOptions;for(o._ignoreMove=!0,i=0;i<e.length;i++)s=a.layerPointToLatLng(t[i]),r=e[i],n=new L.Polyline([this._latlng,s],l),a.addLayer(n),r._spiderLeg=n,r._preSpiderfyLatlng=r._latlng,r.setLatLng(s),r.setZIndexOffset&&r.setZIndexOffset(1e6),h.addLayer(r);this.setOpacity(.3),o._ignoreMove=!1,o.fire("spiderfied",{cluster:this,markers:e})},_animationUnspiderfy:function(){this._noanimationUnspiderfy()}}),L.MarkerCluster.include({_animationSpiderfy:function(e,t){var i,r,n,s,o,a,h=this,l=this._group,u=l._map,_=l._featureGroup,d=this._latlng,p=u.latLngToLayerPoint(d),c=L.Path.SVG,f=L.extend({},this._group.options.spiderLegPolylineOptions),m=f.opacity;for(void 0===m&&(m=L.MarkerClusterGroup.prototype.options.spiderLegPolylineOptions.opacity),c?(f.opacity=0,f.className=(f.className||"")+" leaflet-cluster-spider-leg"):f.opacity=m,l._ignoreMove=!0,i=0;i<e.length;i++)r=e[i],a=u.layerPointToLatLng(t[i]),n=new L.Polyline([d,a],f),u.addLayer(n),r._spiderLeg=n,c&&(o=(s=n._path).getTotalLength()+.1,s.style.strokeDasharray=o,s.style.strokeDashoffset=o),r.setZIndexOffset&&r.setZIndexOffset(1e6),r.clusterHide&&r.clusterHide(),_.addLayer(r),r._setPos&&r._setPos(p);for(l._forceLayout(),l._animationStart(),i=e.length-1;0<=i;i--)a=u.layerPointToLatLng(t[i]),(r=e[i])._preSpiderfyLatlng=r._latlng,r.setLatLng(a),r.clusterShow&&r.clusterShow(),c&&((s=(n=r._spiderLeg)._path).style.strokeDashoffset=0,n.setStyle({opacity:m}));this.setOpacity(.3),l._ignoreMove=!1,setTimeout(function(){l._animationEnd(),l.fire("spiderfied",{cluster:h,markers:e})},200)},_animationUnspiderfy:function(e){var t,i,r,n,s,o,a=this,h=this._group,l=h._map,u=h._featureGroup,_=e?l._latLngToNewLayerPoint(this._latlng,e.zoom,e.center):l.latLngToLayerPoint(this._latlng),d=this.getAllChildMarkers(null,!0),p=L.Path.SVG;for(h._ignoreMove=!0,h._animationStart(),this.setOpacity(1),i=d.length-1;0<=i;i--)(t=d[i])._preSpiderfyLatlng&&(t.closePopup(),t.setLatLng(t._preSpiderfyLatlng),delete t._preSpiderfyLatlng,o=!0,t._setPos&&(t._setPos(_),o=!1),t.clusterHide&&(t.clusterHide(),o=!1),o&&u.removeLayer(t),p&&(s=(n=(r=t._spiderLeg)._path).getTotalLength()+.1,n.style.strokeDashoffset=s,r.setStyle({opacity:0})));h._ignoreMove=!1,setTimeout(function(){var e=0;for(i=d.length-1;0<=i;i--)(t=d[i])._spiderLeg&&e++;for(i=d.length-1;0<=i;i--)(t=d[i])._spiderLeg&&(t.clusterShow&&t.clusterShow(),t.setZIndexOffset&&t.setZIndexOffset(0),1<e&&u.removeLayer(t),l.removeLayer(t._spiderLeg),delete t._spiderLeg);h._animationEnd(),h.fire("unspiderfied",{cluster:a,markers:d})},200)}}),L.MarkerClusterGroup.include({_spiderfied:null,unspiderfy:function(){this._unspiderfy.apply(this,arguments)},_spiderfierOnAdd:function(){this._map.on("click",this._unspiderfyWrapper,this),this._map.options.zoomAnimation&&this._map.on("zoomstart",this._unspiderfyZoomStart,this),this._map.on("zoomend",this._noanimationUnspiderfy,this),L.Browser.touch||this._map.getRenderer(this)},_spiderfierOnRemove:function(){this._map.off("click",this._unspiderfyWrapper,this),this._map.off("zoomstart",this._unspiderfyZoomStart,this),this._map.off("zoomanim",this._unspiderfyZoomAnim,this),this._map.off("zoomend",this._noanimationUnspiderfy,this),this._noanimationUnspiderfy()},_unspiderfyZoomStart:function(){this._map&&this._map.on("zoomanim",this._unspiderfyZoomAnim,this)},_unspiderfyZoomAnim:function(e){L.DomUtil.hasClass(this._map._mapPane,"leaflet-touching")||(this._map.off("zoomanim",this._unspiderfyZoomAnim,this),this._unspiderfy(e))},_unspiderfyWrapper:function(){this._unspiderfy()},_unspiderfy:function(e){this._spiderfied&&this._spiderfied.unspiderfy(e)},_noanimationUnspiderfy:function(){this._spiderfied&&this._spiderfied._noanimationUnspiderfy()},_unspiderfyLayer:function(e){e._spiderLeg&&(this._featureGroup.removeLayer(e),e.clusterShow&&e.clusterShow(),e.setZIndexOffset&&e.setZIndexOffset(0),this._map.removeLayer(e._spiderLeg),delete e._spiderLeg)}}),L.MarkerClusterGroup.include({refreshClusters:function(e){return e?e instanceof L.MarkerClusterGroup?e=e._topClusterLevel.getAllChildMarkers():e instanceof L.LayerGroup?e=e._layers:e instanceof L.MarkerCluster?e=e.getAllChildMarkers():e instanceof L.Marker&&(e=[e]):e=this._topClusterLevel.getAllChildMarkers(),this._flagParentsIconsNeedUpdate(e),this._refreshClustersIcons(),this.options.singleMarkerMode&&this._refreshSingleMarkerModeMarkers(e),this},_flagParentsIconsNeedUpdate:function(e){var t,i;for(t in e)for(i=e[t].__parent;i;)i._iconNeedsUpdate=!0,i=i.__parent},_refreshSingleMarkerModeMarkers:function(e){var t,i;for(t in e)i=e[t],this.hasLayer(i)&&i.setIcon(this._overrideMarkerIcon(i))}}),L.Marker.include({refreshIconOptions:function(e,t){var i=this.options.icon;return L.setOptions(i,e),this.setIcon(i),t&&this.__parent&&this.__parent._group.refreshClusters(this),this}}),e.MarkerClusterGroup=t,e.MarkerCluster=i,Object.defineProperty(e,"__esModule",{value:!0})});
//# sourceMappingURL=leaflet.markercluster.js.map
/*!
 * Copyright (c) 2017 Apple Inc. All rights reserved.
 * 
 * # LivePhotosKit JS License
 * 
 * **IMPORTANT:** This Apple LivePhotosKit software is supplied to you by Apple
 * Inc. ("Apple") in consideration of your agreement to the following terms, and
 * your use, reproduction, or installation of this Apple software constitutes
 * acceptance of these terms. If you do not agree with these terms, please do not
 * use, reproduce or install this Apple software.
 * 
 * This Apple LivePhotosKit software is supplied to you by Apple Inc. ("Apple") in
 * consideration of your agreement to the following terms, and your use,
 * reproduction, or installation of this Apple software constitutes acceptance of
 * these terms. If you do not agree with these terms, please do not use, reproduce
 * or install this Apple software.
 * 
 * This software is licensed to you only for use with LivePhotos that you are
 * authorized or legally permitted to embed or display on your website. 
 * 
 * The LivePhotosKit Software is only licensed and intended for the purposes set
 * forth above and may not be used for other purposes or in other contexts without
 * Apple's prior written permission. For the sake of clarity, you may not and
 * agree not to or enable others to, modify or create derivative works of the
 * LivePhotosKit Software.
 * 
 * Neither the name, trademarks, service marks or logos of Apple Inc. may be used
 * to endorse or promote products, services without specific prior written
 * permission from Apple. Except as expressly stated in this notice, no other
 * rights or licenses, express or implied, are granted by Apple herein.
 * 
 * The LivePhotosKit Software is provided by Apple on an "AS IS" basis. APPLE
 * MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE
 * IMPLIED WARRANTIES OF NON-INFRINGEMENT, MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE, REGARDING THE LIVEPHOTOSKIT SOFTWARE OR ITS USE AND
 * OPERATION ALONE OR IN COMBINATION WITH YOUR PRODUCTS, SYSTEMS, OR SERVICES.
 * APPLE DOES NOT WARRANT THAT THE LIVEPHOTOSKIT SOFTWARE WILL MEET YOUR
 * REQUIREMENTS, THAT THE OPERATION OF THE LIVEPHOTOSKIT SOFTWARE WILL BE
 * UNINTERRUPTED OR ERROR-FREE, THAT DEFECTS IN THE LIVEPHOTOSKIT SOFTWARE WILL BE
 * CORRECTED, OR THAT THE LIVEPHOTOSKIT SOFTWARE WILL BE COMPATIBLE WITH FUTURE
 * APPLE PRODUCTS, SOFTWARE OR SERVICES. NO ORAL OR WRITTEN INFORMATION OR ADVICE
 * GIVEN BY APPLE OR AN APPLE AUTHORIZED REPRESENTATIVE WILL CREATE A WARRANTY. 
 * 
 * IN NO EVENT SHALL APPLE BE LIABLE FOR ANY DIRECT, SPECIAL, INDIRECT, INCIDENTAL
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) RELATING TO OR ARISING IN ANY WAY OUT OF THE USE, REPRODUCTION,
 * OR INSTALLATION, OF THE LIVEPHOTOSKIT SOFTWARE BY YOU OR OTHERS, HOWEVER CAUSED
 * AND WHETHER UNDER THEORY OF CONTRACT, TORT (INCLUDING NEGLIGENCE), STRICT
 * LIABILITY OR OTHERWISE, EVEN IF APPLE HAS BEEN ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE. SOME JURISDICTIONS DO NOT ALLOW THE LIMITATION OF LIABILITY FOR
 * PERSONAL INJURY, OR OF INCIDENTAL OR CONSEQUENTIAL DAMAGES, SO THIS LIMITATION
 * MAY NOT APPLY TO YOU. In no event shall Apple's total liability to you for all
 * damages (other than as may be required by applicable law in cases involving
 * personal injury) exceed the amount of fifty dollars ($50.00). The foregoing
 * limitations will apply even if the above stated remedy fails of its essential
 * purpose. 
 * 
 * 
 * **ACKNOWLEDGEMENTS:**
 * https://cdn.apple-livephotoskit.com/lpk/1/acknowledgements.txt
 * 
 * v1.5.6
 */
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.LivePhotosKit=t():e.LivePhotosKit=t()}(this,function(){return function(e){function t(i){if(r[i])return r[i].exports;var n=r[i]={i:i,l:!1,exports:{}};return e[i].call(n.exports,n,n.exports,t),n.l=!0,n.exports}var r={};return t.m=e,t.c=r,t.i=function(e){return e},t.d=function(e,r,i){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:i})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=25)}([function(e,t,r){"use strict";function i(e){if(e){var t=e.staticMembers;t&&n.call(this,t),n.call(this.prototype,e)}}function n(e){for(var t in e)if(e.hasOwnProperty(t)&&"staticMembers"!==t){var r=Object.getOwnPropertyDescriptor(e,t);r.get||r.set?Object.defineProperty(this,t,r):a.call(this,t,e[t])}}function a(e,t){var r=this[e];return r instanceof Function&&t instanceof Function?o.call(this,e,t,r):F.instanceOrKindOf(t,F.Metadata)?s.call(this,e,t):void(this[e]=t)}function o(e,t,r){this[e]=function(){var e=this._super;this._super=r;var i=t.apply(this,arguments);return this._super=e,i}}function s(e,t){this.hasOwnProperty("_metadatas")||(this._metadatas=Object.create(this._metadatas)),(t.isLPKClass?t.sharedInstance:t).registerOnDefinition(this,e)}function u(e){var t=this["_callbacksFor_"+e];if(t){var r=void 0;if(arguments.length>1){r=F.arrayPool.get();for(var i=1,n=arguments.length;i<n;i++)r[i-1]=arguments[i]}var a=void 0;if(a=this._triggerPauseStack){var o=a[a.length-1];if("IS_FLUSHING"!==o[o.length-1])return void o.push(this,e,r)}for(var s=0,u=t.length;s<u;s++){var l=t[s];l&&l.apply(this,r)}r&&F.arrayPool.ret(r)}}function l(e,t){if(-1!==e.indexOf("."))return c.call(this,e,t);var r="_callbackToIndexMapFor_"+e,i=this.hasOwnProperty(r)?this[r]:this[r]=this[r]?new w.a(this[r]):new w.a;if(void 0===i.get(t)){var n="_callbacksFor_"+e,a=this.hasOwnProperty(n)?this[n]:this[n]=this[n]?this[n].slice():[];i.set(t,a.length),a.push(t)}}function d(e,t){if(-1!==e.indexOf("."))return h.call(this,e,t);var r="_callbackToIndexMapFor_"+e,i=this[r];if(i&&void 0!==i.get(t)){var n=this.hasOwnProperty(r)?this[r]:this[r]=this[r]?new w.a(this[r]):new w.a,a="_callbacksFor_"+e;(this.hasOwnProperty(a)?this[a]:this[a]=this[a]?this[a].slice():[])[n.get(t)]=null,n.delete(t)}}function c(e,t,r){var i="_"+e+"_"+F.guidFor(t)+"_"+F.guidFor(r),n="_chainListenerMaintenanceCallback_for"+i;if(!this[n]){var a=e.indexOf("."),o=-1!==a,s=e.substring(a+1),u=s.substring(0,(s.indexOf(".")+1||s.length+1)-1),d=e.substring(0,-1===a?e.length:a),p="_chainListenerPreviousStoredValue_for"+i,f=function(e){var i=r||this;if(u&&o){var n=this[d],a=this[p];n!==a&&(this[p]=n,a&&a.isLPKObservable&&h.call(a,s,t,i),n&&n.isLPKObservable&&c.call(n,s,t,i))}e||t.call(i)};l.call(this,d,f),this.isInitialized&&f.call(this,!0),this[n]=f}}function h(e,t,r){var i="_"+e+"_"+F.guidFor(t)+"_"+F.guidFor(r),n="_chainListenerMaintenanceCallback_for"+i,a=this[n];if(a){var o=e.indexOf("."),s=e.substring(0,-1===o?e.length:o);d.call(this,s,a);var u="_chainListenerPreviousStoredValue_for"+i,l=this[u];if(l&&l.isLPKObservable){this[u]=void 0;var c=e.substring(o+1),p=r||this;h.call(l,c,t,p)}this[n]=void 0}}function p(e,t){l.apply(this,arguments),this.isInitialized&&t.call(this)}function f(e,t){d.apply(this,arguments)}function v(){var e=this._nextObserverId=(this._nextObserverId||0)+1,t="_runtimeObserver"+e,r=F.observer.apply(F.observer,arguments);return r.registerOnDefinition(this,t),r}function y(e){var t=this._metadatas[e];t&&t.invalidateForObject&&t.invalidateForObject(this)}function m(e){return F.resolvePropertyPathFromObject(this,e)}function g(e,t){var r=e.lastIndexOf(".");if(-1===r)return void(this[e]=t);var i=F.resolvePropertyPathFromObject(this,e.slice(0,r));i&&(i[e.slice(r+1)]=t)}function b(){for(var e=F.objectPool.get(),t=0,r=arguments.length;t<r;t++){var i=arguments[t];e[i]=this.getPath(i)}return e}function _(){var e=arguments.length,t=arguments[e-1],r=F.arrayPool.get();if(e>1)for(var i=0;i<e-1;i++)r[i]=arguments[i];else for(var n in t)Object.prototype.hasOwnProperty.call(t,n)&&r.push(n);F.Object.prototype.pauseNotifications();for(var a=0,o=r.length;a<o;a++){var s=r[a];this.setPath(s,t[s])}F.Object.prototype.resumeNotifications(),F.arrayPool.ret(r)}function P(){(this.hasOwnProperty("_triggerPauseStack")?this._triggerPauseStack:this._triggerPauseStack=F.arrayPool.get()).push(F.arrayPool.get())}function k(){var e=this.hasOwnProperty("_triggerPauseStack")&&this._triggerPauseStack;if(!e)throw"Unmatched `resumeNotifications` call. Cannot over-resume notifications.";var t=e[e.length-1];T(t),e.pop(),F.arrayPool.ret(t),e.length||(delete this._triggerPauseStack,F.arrayPool.ret(e))}function T(e){e.push("IS_FLUSHING");for(var t=F.objectPool.get(),r=0,i=e.length-1;r<i;r+=3){var n=e[r],a=e[r+1],o=e[r+2],s=F.guidFor(n)+":"+a,u=t[s];void 0!==u&&(e[u]=null),t[s]=o?void 0:r}F.objectPool.ret(t);for(var l=0,d=e.length-1;l<d;l+=3){var c=e[l];if(c){var h=e[l+1],p=e[l+2];p?(p.unshift(h),c.trigger.apply(c,p),F.arrayPool.ret(p)):c.trigger(h)}}}var x=r(47),S=r(20),w=r(19),O=r(44),C="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},F={mixin:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),i=1;i<t;i++)r[i-1]=arguments[i];for(var n=1,a=arguments.length;n<a;n++){var o=arguments[n];if(o)for(var s in o)o.hasOwnProperty(s)&&(e[s]=o[s])}},instanceOf:function(e,t){return!!e&&(e.constructor===t||F.kindOf(e.constructor,t))},kindOf:function(e,t){if(!(e&&e.isLPKClass&&t&&t.isLPKClass))return!1;do{if(e===t)return!0}while(e=e.superclass);return!1},instanceOrKindOf:function(e,t){return F.instanceOf(e,t)||F.kindOf(e,t)},isHash:function(e){var t=void 0;return!("object"!==(void 0===e?"undefined":C(e))||null===e||(t=e.constructor)&&t!==Object||e.isLPKObject||e.isLPKClass)},arrayPool:x.a,mapPool:x.b,objectPool:x.c,canvasPool:x.d,String:O.a,Array:{mapIntoArray:function(e,t,r){var i=e.length;r.length!==i&&(r.length=i);for(var n=0;n<i;n++)r[n]=t(e[n],n);return r}},resolvePropertyPathFromObject:function(e,t){for(var r=t.indexOf("."),i=0,n=e;-1!==r;){if(!(n=n[t.substring(i,r)]))return;i=r+1,r=t.indexOf(".",i)}return n[t.substring(i)]},InequalityTests:{DEFAULT:function(e,t,r){return e!==t||Array.isArray(e)||F.isHash(e)},STRICT:function(e,t,r){return e!==t},NAN_AWARE:function(e,t,r){return!Object.is(e,t)}},guidFor:S.a,reusableObject:{},emptyArray:[],Object:null,Metadata:null,observer:null,MetadataExtension:null,metadataExtension:null,AccumulatorMetadata:null,accumulator:null,ObserverMetadata:null,Property:null,property:null,ObservableProperty:null,observableProperty:null,ProxyProperty:null,proxyProperty:null,boundFunction:null};F.Object={staticMembers:{isLPKClass:!0,isLPKObservable:!0,isInitialized:!0,create:function(){for(var e=arguments.length,t=Array(e),r=0;r<e;r++)t[r]=arguments[r];var i=Object.create(this.prototype);return i.constructor=this,i.init.apply(i,arguments),i},extend:function(){for(var e=arguments.length,t=Array(e),r=0;r<e;r++)t[r]=arguments[r];var n=Object.create(this);return n.prototype=Object.create(n.prototype),Array.prototype.forEach.call(arguments,i,n),n.superclass=this,n.init(),n},init:function(){},isClassFor:function(e){var t=e&&e.constructor;return!(!t||!this.isSuperclassOf(t))},isSuperclassOf:function(e){var t=this;if(!(e&&e.isLPKClass&&t&&t.isLPKClass))return!1;do{if(e===t)return!0}while(e=e.superclass);return!1},_super:null,_metadatas:{},trigger:u,_listen:l,_observe:p,_unlisten:d,_unobserve:f,observe:v,propertyChanged:y,pauseNotifications:P,resumeNotifications:k,getPath:m,setPath:g,getMultiple:b,setMultiple:_},isLPKObject:!0,isLPKObservable:!0,isInitialized:!1,_metadatas:{},init:function(e){F.mixin(this,e),this._awakenPropertiesWithAccessors()},_awakenPropertiesWithAccessors:function(){var e=F.arrayPool.get(),t=this._metadatas;do{e.push(t)}while(t=Object.getPrototypeOf(t));for(var r,i=F.arrayPool.get(),n=F.arrayPool.get(),a=F.arrayPool.get(),o=F.mapPool.get(),s=e.length-1;r=e[s];s--){for(var u in r)if(r.hasOwnProperty(u)){var l=r[u];if(l.hasAccessors){var d=this[u];if(void 0!==d){var c=o.get(l);void 0!==c&&(i[c]=n[c]=void 0),o.set(l,i.length),i.push(l),n.push(d)}}}a.push(i.length)}this.isInitialized=!0,F.Object.prototype.pauseNotifications();for(var h=0,p=n.length;h<p;h++){var f=n[h];void 0!==f&&i[h].awakenForObjectWithValue(this,f)}F.Object.prototype.resumeNotifications(),F.arrayPool.ret(e),F.arrayPool.ret(i),F.arrayPool.ret(n),F.arrayPool.ret(a),F.mapPool.ret(o)},_super:null,trigger:u,_listen:l,_observe:p,_unlisten:d,_unobserve:f,observe:v,propertyChanged:y,pauseNotifications:P,resumeNotifications:k,getPath:m,setPath:g,getMultiple:b,setMultiple:_},F.Object.staticMembers.prototype=F.Object,F.Object=F.Object.staticMembers,delete F.Object.prototype.staticMembers,Object.defineProperty(F.Object,"sharedInstance",{get:function(){return this.hasOwnProperty("_internalValue_for_sharedInstance")||(this._internalValue_for_sharedInstance=this.create()),this._internalValue_for_sharedInstance},set:function(){throw"Cannot write to a read-only property."}}),F.Metadata=F.Object.extend({registerOnDefinition:function(e,t){e._metadatas[t]=this},unregisterFromDefinition:function(e,t){e._metadatas[t]=void 0}}),F.MetadataExtension=F.Metadata.extend({init:function(e){this._super(),this._extensionParams=e},registerOnDefinition:function(e,t){e._metadatas[t].constructor.extend(this._extensionParams).sharedInstance.registerOnDefinition(e,t)}}),F.metadataExtension=F.MetadataExtension.create.bind(F.MetadataExtension),F.AccumulatorMetadata=F.Metadata.extend({init:function(e){this._super(),this.value=e},registerOnDefinition:function(e,t){var r=e[t];e[t]=this.accumulateValues(e,r,this.value)},accumulateValues:function(e,t,r){if(Array.isArray(t)&&Array.isArray(r))return t.concat(r);if(t&&t.isLPKClass&&F.isHash(r))return t.extend(r);if(r instanceof Function)return r.call(e,t);throw"Cannot use accumulators this way yet."}}),F.accumulator=F.AccumulatorMetadata.create.bind(F.AccumulatorMetadata),F.ObserverMetadata=F.Metadata.extend({staticMembers:{_collector:[]},dependencies:[],observerCallback:function(){throw"Must specify `observerCallback` on observers."},decideRequiresUpdate:F.InequalityTests.DEFAULT,init:function(){if(F.isHash(arguments[0]))this._super.apply(this,arguments);else{var e=Array.prototype.slice.call(arguments),t=e.pop();this._super({dependencies:e,observerCallback:t})}},registerOnDefinition:function(e,t){this._super.apply(this,arguments),this.keyOnObject=t;var r=this;this._invokeObserverCallbackIfNeeded=this._invokeObserverCallbackIfNeeded||(this._invokeObserverCallbackIfNeeded=function(){var e=r.dependencies,i=r.observerCallback,n=r.constructor._collector;n.length=e.length;var a=!1;r._shouldForceNextInvocationOfObserverCallback&&(r._shouldForceNextInvocationOfObserverCallback=!1,a=!0);for(var o=0;o<e.length;o++){var s=e[o],u=this.getPath(s),l="_lastValueSeenByObserver_"+t+"_forDependency_"+s,d=this[l];this[l]=u,!a&&r.decideRequiresUpdate(u,d,s)&&(a=!0),n[o]=u}a&&i.apply(this,n),n.length=0}),e[this.keyOnObject]=e[this.keyOnObject]||(e[this.keyOnObject]=function(){r._shouldForceNextInvocationOfObserverCallback=!0,r._invokeObserverCallbackIfNeeded.call(this)});for(var i=this.dependencies,n=0;n<i.length;n++)e.isInitialized?e._observe(i[n],this._invokeObserverCallbackIfNeeded):e._listen(i[n],this._invokeObserverCallbackIfNeeded)},unregisterFromDefinition:function(e,t){this._super.apply(this,arguments);for(var r=this.dependencies,i=0;i<r.length;i++)e._unlisten(r[i],this._invokeObserverCallbackIfNeeded)}}),F.observer=F.ObserverMetadata.create.bind(F.ObserverMetadata),F.Property=F.Metadata.extend({staticMembers:{extend:function(e){return 1!==arguments.length||F.isHash(e)?this._super.apply(this,arguments):this._super({defaultValue:e})}},hasAccessors:!0,readOnly:!1,defaultValue:void 0,get:null,set:null,keyOnObject:null,storageKeyOnObject:null,_defaultGetter:function(e){return e},_defaultSetter:function(e){return e},_internalGetterForObject:function(e){return this.get.call(e,e[this.storageKeyOnObject])},_internalSetterForObject:function(e,t){e[this.storageKeyOnObject]=this.set.call(e,t)},init:function(e){var t=this;if(1!==arguments.length||F.isHash(e)?this._super.apply(this,arguments):this._super({defaultValue:e}),this.get||(this.get=this._defaultGetter),this.readOnly){if(this.set)throw"Cannot include both `readOnly` and `set`.";this.set=function(e){if(t._isAwakeningInReadOnlyMode)return t._isAwakeningInReadOnlyMode=!1,e;throw"Cannot write to a read-only property"}}else this.set||(this.set=this._defaultSetter)},registerOnDefinition:function(e,t){this._super.apply(this,arguments),this.keyOnObject=t;var r=this.storageKeyOnObject="_internalValue_for_"+t;this._internalGetterForObject=this._internalGetterForObject.bind(this),this._internalSetterForObject=this._internalSetterForObject.bind(this);var i=this;Object.defineProperty(e,t,{get:function(){return this.isInitialized?i._internalGetterForObject(this):this[r]},set:function(e){if(!this.isInitialized)return void(this[r]=e);i._internalSetterForObject(this,e)}}),e[t]=this.defaultValue},unregisterFromDefinition:function(e,t){F.reusableObject[t]=void 0;var r=Object.getOwnPropertyDescriptor(F.reusableObject,t);delete F.reusableObject[t],Object.defineProperty(e,t,r),delete e["_internalValue_for_"+t],this._super.apply(this,arguments)},awakenForObjectWithValue:function(e,t){this.readOnly&&(this._isAwakeningInReadOnlyMode=!0),t instanceof Function&&(t=t.call(e)),e[this.keyOnObject]=t}}),F.property=F.Property.extend.bind(F.Property),F.ObservableProperty=F.Property.extend({isCacheable:!1,dependencies:[],writeDependencies:[],decideRequiresUpdate:F.InequalityTests.DEFAULT,_internalSetterForObject:function(e){this._super.apply(this,arguments),this.invalidateForObject(e)},_internalGetterForObject:function(e){if(!this.isCacheable)return this._super.apply(this,arguments);var t=this.cacheExistenceKeyOnObject;if(e[t])return e[this.cacheStorageKeyOnObject];var r=this._super.apply(this,arguments);return e[t]=!0,e[this.cacheStorageKeyOnObject]=r,r},invalidateForObject:function(e){e[this.cacheExistenceKeyOnObject]=!1,e[this.cacheStorageKeyOnObject]=void 0,e.trigger(this.keyOnObject)},registerOnDefinition:function(e,t){this._super.apply(this,arguments);var r=this;this.cacheStorageKeyOnObject="_cacheStorageForProperty_"+t,this.cacheExistenceKeyOnObject="_cacheExistenceForProperty_"+t,F.observer({dependencies:this.dependencies,observerCallback:function(){r.invalidateForObject(this)},decideRequiresUpdate:this.decideRequiresUpdate}).registerOnDefinition(e,"_dependencyObserverForProperty_"+t),this.didChange&&F.observer({dependencies:[t],observerCallback:this.didChange,decideRequiresUpdate:this.decideRequiresUpdate}).registerOnDefinition(e,"_didChangeObserverForProperty_"+t)}}),F.observableProperty=F.ObservableProperty.extend.bind(F.ObservableProperty),F.ProxyProperty=F.ObservableProperty.extend({proxyPath:null,decode:function(e){return e},encode:function(e){return e},init:function(e){var t="string"==typeof e?e:e.proxyPath;if(!t)throw"A proxyPath must be configured on a ProxyProperty.";this.dependencies=[t];var r=this;this.get=function(){return r.decode(this.getPath(r.proxyPath))},e&&e.readOnly||(this.set=function(e){this.setPath(r.proxyPath,r.encode(e))}),"string"==typeof e?this._super({proxyPath:e}):this._super.apply(this,arguments)}}),F.proxyProperty=F.ProxyProperty.create.bind(F.ProxyProperty),F.boundFunction=function(e){return F.property(function(){return e.bind(this)})},t.a=F},function(e,t,r){"use strict";r.d(t,"a",function(){return i});var i={default:"full",FULL:"full",HINT:"hint",LOOP:"loop"}},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var n=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),a=navigator.userAgent.toLowerCase(),o=/\sedge\//.test(a),s=function(){function e(){i(this,e)}return n(e,null,[{key:"isEdge",get:function(){return o}},{key:"isChrome",get:function(){return e._isChrome}},{key:"isSafari",get:function(){return e._isSafari}},{key:"isFirefox",get:function(){return e._isFirefox}},{key:"isIE",get:function(){return e._isIE}},{key:"isIOS",get:function(){if(e._isIOS)return!0;var t=window.top,r=t.document;return!(!e._macLike||!("ontouchstart"in t||"createTouch"in r))}}]),e}();t.a=s,s._isChrome=!s.isEdge&&/chrome/.test(a),s._isSafari=!s.isEdge&&!s.isChrome&&/safari/.test(a),s._isFirefox=!s.isEdge&&!s.isChrome&&!s.isSafari&&/firefox/.test(a),s._isIE=!s.isEdge&&!s.isChrome&&!s.isSafari&&!s.isFirefox&&/trident|msie/.test(a),s._isIOS=!!navigator.userAgent.match(/\b(iPad|iPhone|iPod)\b.*\bOS \d+_\d+/i),s._macLike=/mac/i.test(navigator.userAgent)&&!/like mac/i.test(navigator.userAgent)},function(e,t,r){"use strict";var i=r(10),n=r(1);r.d(t,"a",function(){return a});var a={_mappingToLocalizedStrings:{live:"Live",get bounce(){return i.a.getString("VideoEffects.Badge.Title.Bounce")},get exposure(){return i.a.getString("VideoEffects.Badge.Title.LongExposure")},get loop(){return i.a.getString("VideoEffects.Badge.Title.Loop")}},_mappingToPlaybackStyle:{bounce:n.a.LOOP,exposure:n.a.FULL,live:n.a.FULL,loop:n.a.LOOP},default:"live",BOUNCE:"bounce",EXPOSURE:"exposure",LIVE:"live",LOOP:"loop",toBadgeText:function(e){return this.toLocalizedString(e).toLocaleUpperCase()},toLocalizedString:function(e){return this._mappingToLocalizedStrings[e||a.default]},toPlaybackStyle:function(e){return this._mappingToPlaybackStyle[e||n.a.default]}}},function(e,t,r){"use strict";function i(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}var n=r(0),a=r(7),o=r(39),s=r(38),u=r(40),l=r(9),d=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var i in r)Object.prototype.hasOwnProperty.call(r,i)&&(e[i]=r[i])}return e},c=n.a.Object.extend({layerName:null,renderLayerClass_dom:null,renderLayerMixin:{},init:function(e){this._super(),this.renderLayerMixin=e},getRenderLayer:function(e,t){return this["renderLayerClass_"+e.approach].extend(this.renderLayerMixin,{layerName:this.layerName}).create(e,t)}}),h=c.extend({renderLayerClass_dom:o.a}),p=c.extend({renderLayerClass_dom:s.a}),f=c.extend({renderLayerClass_dom:u.a}),v=n.a.Object.extend({staticMembers:{PhotoIngredient:h,InterpolatedVideoIngredient:p,VideoIngredient:f,computedStyle:a.a.computedStyle,getRecipeFromPlaybackStyle:function(e){return this._recipesByPlaybackStyle[e]},registerRecipeWithPlaybackStyle:function(e,t){this._recipesByPlaybackStyle=d({},this._recipesByPlaybackStyle,i({},t,e))}},correspondingPlaybackStyle:null,get name(){return"recipe_for_playbackStyle_"+this.correspondingPlaybackStyle},minimumShortenedDuration:0,spontaneousFinishDuration:0,ingredients:null,requiresInterpolation:!1,init:function(e){this.ingredients=[],this._super();for(var t in e)if(e.hasOwnProperty(t)){var r=e[t];this[t]=r,c.isClassFor(r)&&(r.layerName=t,this.ingredients.push(r))}this.correspondingPlaybackStyle&&v.registerRecipeWithPlaybackStyle(this,this.correspondingPlaybackStyle)},getRenderLayers:function(e){for(var t,r=[],i=0;t=this.ingredients[i];i++)t.isDisabled||r.push(t.getRenderLayer(e,this));return r},beginFinishingPlaybackEarly:function(e){if(!e.isPlaying)return void(e.wantsToPlay=!1);e.duration=Math.min(e.duration,Math.max(this.minimumShortenedDuration,e.currentTime+this.spontaneousFinishDuration))},calculateAnimationDuration:function(e,t,r){return t||0},continuePlayback:function(e){e.currentTime<e.duration?e._rafID=requestAnimationFrame(e._nextFrame.bind(e)):(e.stop(),e.dispatchEvent(r.i(l.f)()))},register:function(){},requestMoreCompatibleRecipe:function(){return this}});t.a=v},function(e,t,r){"use strict";var i=r(12),n={debug:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),n=1;n<t;n++)r[n-1]=arguments[n];i.a&&console.debug.apply(console,arguments)},log:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),n=1;n<t;n++)r[n-1]=arguments[n];i.a&&console.log.apply(console,arguments)},info:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),n=1;n<t;n++)r[n-1]=arguments[n];i.a&&console.info.apply(console,arguments)},warn:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),i=1;i<t;i++)r[i-1]=arguments[i];console.warn.apply(console,arguments)},error:function(e){for(var t=arguments.length,r=Array(t>1?t-1:0),i=1;i<t;i++)r[i-1]=arguments[i];console.error.apply(console,arguments)}};t.a=n},function(e,t,r){"use strict";r.d(t,"a",function(){return i});var i;!function(e){e[e.FAILED_TO_DOWNLOAD_RESOURCE=0]="FAILED_TO_DOWNLOAD_RESOURCE",e[e.PHOTO_FAILED_TO_LOAD=1]="PHOTO_FAILED_TO_LOAD",e[e.VIDEO_FAILED_TO_LOAD=2]="VIDEO_FAILED_TO_LOAD"}(i||(i={}))},function(e,t,r){"use strict";var i=r(8),n=r(0),a=i.a.extend({_hasBeenDisposed:!1,staticMembers:{computedStyle:function(e){return e._lpk_isComputedStyle=!0,e}},recipe:n.a.observableProperty(),renderer:n.a.observableProperty(),duration:n.a.proxyProperty("renderer.duration"),canRender:n.a.observableProperty(!1),computedStyles:n.a.property(function(){return[]}),displayWidth:0,displayHeight:0,shouldLoop:!1,init:function(e,t){this.renderer=e,this.recipe=t,this._super();for(var r in this){var i=this[r];i&&i._lpk_isComputedStyle&&this.computedStyles.push({styleKey:r,getter:i})}},updateSize:function(e,t){if(!arguments.length)return this.updateSize(this.displayWidth,this.displayHeight);this.displayWidth=e,this.displayHeight=t},setUpForRender:function(){},tearDownFromRender:function(){this.reduceMemoryFootprint()},reduceMemoryFootprint:function(){},_canRenderDidChange:n.a.observer("canRender","renderer._lastRecipe",function(e,t){var r=this.recipe,i=this.renderer;this._hasBeenDisposed||(!e||!t||t!==r&&i._hasInitialized?this.tearDownFromRender():(i._hasInitialized=!0,this.setUpForRender(),this.updateSize()))}),prepareToRenderAtTime:function(){return!0},canRenderAtTime:function(){return!0},renderAtTime:function(e){if(!arguments.length)return this.renderAtTime(this._lastRenderedTime);this._lastRenderedTime=e;for(var t,r=0;t=this.computedStyles[r];r++)t.value=t.getter.call(this,e);this.renderStyles(this.computedStyles)},renderStyles:function(e){},dispose:function(){this.detach(),this._hasBeenDisposed=!0}});t.a=a},function(e,t,r){"use strict";var i=r(0),n=i.a.Object.extend({element:null,_lpk_isView:!0,tagName:"div",eventDispatchingElement:null,init:function(e){e?this.element=e:this.tagName&&(this.element=document.createElement(this.tagName)),this._super()},attachInto:function(e,t,r){if(!this.element)return void(this.parentView=e);t||(t=e.element),r&&r._lpk_isView&&(r=r.element),r?t.insertBefore(this.element,r):t.appendChild(this.element),this.parentView=e},detach:function(){this.element&&this.element.parentNode&&this.element.parentNode.removeChild(this.element),this.parentView=null},dispatchEvent:function(e){var t=this.eventDispatchingElement||this.element;if(!t)throw"Cannot dispatch an event from a view with no DOM element.";t.dispatchEvent.call(t,e)},parentView:i.a.observableProperty(null)});t.a=n},function(e,t,r){"use strict";function i(e){return function(t){return new CustomEvent(e,{detail:t})}}var n=r(14);r.d(t,"a",function(){return a}),r.d(t,"b",function(){return o}),r.d(t,"f",function(){return s}),r.d(t,"e",function(){return u}),r.d(t,"c",function(){return l}),r.d(t,"d",function(){return d}),function(){function e(e,t){t=t||{bubbles:!1,cancelable:!1,detail:void 0};var r=document.createEvent("CustomEvent");return r.initCustomEvent(e,t.bubbles,t.cancelable,t.detail),r}if("function"==typeof window.CustomEvent)return!1;e.prototype=window.Event.prototype,window.CustomEvent=e}();var a=i(n.a.LIVEPHOTOSKIT_LOADED),o=i("canplay"),s=i("ended"),u=i("error"),l=i("photoload"),d=i("videoload")},function(e,t,r){"use strict";var i=r(0),n=["ar-sa","ca-cs","cs-cz","da-dk","nl-nl","pt-br","pt-pt","no-no","el-gr","en-us","fi-fi","fr-fr","de-de","he-il","hr-hr","hu-hu","id-id","it-it","ja-jp","ko-kr","ms-my","pl-pl","ro-ro","ru-ru","zh-cn","zh-tw","sk-sk","es-es","sv-se","th-th","tr-tr","uk-ua","vi-vi"],a={"es-419":"es-es",pt:"pt-pt",no:"no-no",nb:"no-no",nn:"no-no",zh:"zh-cn","zh-Hans":"zh-cn","zh-Hant":"zh-tw","zh-HK":"zh-tw","zh-MO":"zh-tw","zh-SG":"zh-cn"},o={};for(var s in a){var u=a[s],l=o[u]||[];o[u]=[].concat(function(e){if(Array.isArray(e)){for(var t=0,r=Array(e.length);t<e.length;t++)r[t]=e[t];return r}return Array.from(e)}(l),[s])}for(var d={},c=0;c<n.length;c++){var h=n[c];try{var p=r(21)("./"+h+".lproj/strings.json"),f=h.split("-")[0],v=o[h];if(d[h]=p,d[f]||(d[f]=p),v)for(var y in v)d[y]=p}catch(e){}}var m=function(e){var t=g.locale,r=g.strings;return(r[t]||r["en-us"])[e]||""},g=i.a.Object.extend({locale:i.a.observableProperty({get:function(e){return e||window.navigator.language},set:function(e){return e}}),getString:m,strings:d}).create();t.a=g},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function n(e){var t=parseFloat(e);if(+t===t)return t;var r="true"===e||"false"!==e&&void 0;return!!r===r?r:e}var a=r(27),o=r(0),s=r(5),u=r(1),l=r(3),d=r(12);r.d(t,"a",function(){return _}),r.d(t,"b",function(){return P}),r.d(t,"c",function(){return k});var c=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var i in r)Object.prototype.hasOwnProperty.call(r,i)&&(e[i]=r[i])}return e},h="property",p={play:"method",pause:"method",stop:"method",toggle:"method",beginFinishingPlaybackEarly:"method",isPlaying:"read_only_property",wantsToPlay:"read_only_property",currentTime:"read_only_property",renderedTime:"read_only_property",duration:"read_only_property",autoplay:h,caption:h,playbackStyle:h,preloadedEffectType:h,updateSize:"method",photoWidth:"read_only_property",photoHeight:"read_only_property",videoWidth:"read_only_property",videoHeight:"read_only_property",effectType:h,proactivelyLoadsVideo:h,photoSrc:h,photoMimeType:h,videoSrc:h,videoMimeType:h,metadataVideoSrc:h,photo:h,video:h,photoTime:h,frameTimes:h,videoRotation:h,canPlay:"read_only_property",loadProgress:"read_only_property",errors:"read_only_property",showsNativeControls:h,observe:"method"};delete p.observe;var f=function(){var e=[];for(var t in p)p.hasOwnProperty(t)&&e.push(t);return e}(),v={},y=f.map(function(e){var t="data-"+o.a.String.hyphenate(e);return v[t]=e,t}),m=f.map(function(e){return p[e]}),g={enumerable:!1,configurable:!1,writable:!1},b={enumerable:!1,configurable:!1},_=function(e,t){if(!e)return s.a.warn("LivePhotosKit.augmentElementAsPlayer requires a target element to augment."),null;if("IMG"===e.tagName){var r=document.createElement("div"),i=e.parentNode,_=e.getAttribute("src"),P=e.getAttribute("photo-src")||_;e.removeAttribute("src"),e.setAttribute("data-photo-src",P);for(var k=e.attributes,x=0;x<k.length;x++){var S=k[x],w=S.nodeName,O=S.value;r.setAttribute(w,O)}i.insertBefore(r,e),i.removeChild(e),e=r}var C=void 0,F=void 0,R=e;if(R.__isLPKPlayer__)return R;g.value=!0,Object.defineProperty(R,"__isLPKPlayer__",g);var L=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};d.a&&!e.videoSrc&&e.photoSrc?s.a.warn("Changing a `photoSrc` independent of its `videoSrc` can result in unexpected behavior"):d.a&&e.videoSrc&&!e.photoSrc&&s.a.warn("Changing a `videoSrc` independent of its `photoSrc` can result in unexpected behavior");var t=F?{photoSrc:F.photo,videoSrc:F.videoSrc,effectType:F.effectType,autoplay:F.autoplay,proactivelyLoadsVideo:F.proactivelyLoadsVideo}:{},r=c({},t,e),i=(r.photoSrc,r.videoSrc,r.effectType),n=r.autoplay,f=r.proactivelyLoadsVideo;C=o.a.objectPool.get(),r.preloadedEffectType=i,r.autoplay=!1!==n;var v=i||l.a.default;l.a.toPlaybackStyle(v)===u.a.LOOP&&r.autoplay&&(d.a&&!f&&s.a.warn("When using a looping asset you should set `proactivelyLoadsVideo` to `true` unless `autoplay` is also set to `false`"),r.proactivelyLoadsVideo=!0);for(var y in r)Object.prototype.hasOwnProperty.call(r,y)&&(p[y]===h?C[y]=r[y]:s.a.warn("LivePhotosKit.Player: Initial configuration for `"+y+"` was ignored, because the property is not a writable property."));if(F)for(var m in C){var g=C[m];F[m]=g}else F=a.a.create(R,C);o.a.objectPool.ret(C),C=null};R.setProperties=L,R.setProperties(t);for(var E,A,I=0;(E=f[I])&&(A=m[I]);I++)!function(e,t,r){"method"===r?(g.value=F[t].bind(F),Object.defineProperty(R,t,g)):(b.set=r===h?function(e){F[t]=e}:function(){},b.get=function(){return F[t]},Object.defineProperty(R,t,b))}(0,E,A);g.value=function(){var e=arguments.length,t=arguments[e-1];if(e<1||!(t instanceof Function))throw new Error("Invalid arguments passed to `observe`. Form: key, [key, …], callback.");for(var r=o.a.arrayPool.get(),i=0,n=e;i<n;i++)r[i]=arguments[i];for(var a=0,s=e-1;a<s;a++){var u=p[r[a]];if(u!==h&&"read_only_property"!==u)throw new Error("Can't observe non-observable property '"+r[a]+"'.")}r[e-1]=t.bind(this);var l=F.observe.apply(F,r);return o.a.arrayPool.ret(r),new T(l,F)},Object.defineProperty(R,"observe",g);for(var E,A,D,I=0;(E=f[I])&&(A=m[I])&&(D=y[I]);I++)if(A===h){var M=R.getAttribute(D);M&&("effectType"===E?F.preloadedEffectType=n(M):F[E]=n(M))}var j=R.setAttribute;g.value=function(e,t){var r=v[e];if(!r)return void j.apply(this,arguments);this[r]=n(t)},Object.defineProperty(R,"setAttribute",g);var U=R.removeAttribute;g.value=function(e){var t=v[e];if(!t)return U.apply(this,arguments);this[t]=null},Object.defineProperty(R,"removeAttribute",g);for(var V,N,B,z=0;(V=f[z])&&(N=m[z])&&(B=y[z]);z++){(function(e,t,r,i){if(r!==h&&"read_only_property"!==r)return"continue";R.observe(t,function(e){void 0===e||null===e||"string"!=typeof e&&+e!==e&&"boolean"!=typeof e?U.call(this,i):j.call(this,i,String(e))})})(0,V,N,B)}return""!==R.getAttribute("data-live-photo")&&R.setAttribute("data-live-photo",""),g.value=F,Object.defineProperty(R,"__internalLPKPlayer__",g),g.value=void 0,b.set=b.get=void 0,R},P=function(e){var t=document.createElement("div");return _(t,e)},k=function(e,t){if(arguments.length>=3||"string"==typeof arguments[0]&&"string"==typeof arguments[1])throw new Error("LivePhotosKit.Player: Creating a new Player using arguments of the form 'photoSrc, videoSrc, [targetElement, [options]]' is no longer supported. Instead, use the new signature, '[targetElement, [options]]");return s.a.warn("The `LivePhotosKit.Player` method will be deprecated in an upcoming release. Please use the `LivePhotosKit.augementElementAsPlayer` or `LivePhotosKit.createPlayer` methods, instead."),e?_(e,t):P(t)},T=function e(t,r){i(this,e),this.fire=function(){r[t.keyOnObject]()},this.disconnect=function(){t.unregisterFromDefinition(r)},this.connect=function(){t.registerOnDefinition(r)}}},function(e,t,r){"use strict";var i=/_lpk_debug=true/i;t.a=i.test(window.location.search)||i.test(window.location.hash)},function(e,t,r){"use strict";var i={setUpForRender:function(){this.attachInto(this.renderer)},tearDownFromRender:function(){this.detach(),this._super()},renderStyles:function(e){for(var t,r=this.element,i=r.style,n=0;t=e[n];n++){var a=t,o=a.styleKey,s=a.value;i[o]!==s&&(i[o]=s)}}};t.a=i},function(e,t,r){"use strict";var i=r(55),n=r(56),a=r(57);t.a={APP_NAME:"LivePhotosKit",BUILD_NUMBER:i.a,MASTERING_NUMBER:n.a,FEEDBACK_URL_PREFIX:"https://feedbackws.icloud.com",LIVEPHOTOSKIT_LOADED:"livephotoskitloaded",URL_PREFIX:"https://cdn.apple-livephotoskit.com",VERSION:a.a}},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var n=r(3),a=r(50),o=r(18),s=r(10),u=r(1);r.d(t,"a",function(){return c});var l=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),d={element:null,label:"",labelPadding:6,leftPadding:5,height:25,backgroundColor:"rgba(255, 255, 255, 0.7)",itemColor:"rgb(0, 0, 0)",fontSize:9,borderRadius:5,dottedRadius:8.5,innerRadius:5.25,zIndex:4,shouldAnimateProgressRing:!0,progressRingAnimationSpeed:300,shouldAddEventListeners:!0,effectType:null,playbackStyle:null,configurePlayAction:r.i(a.a)(),configureStopAction:r.i(a.a)()},c=function(){function e(){var t=this,r=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};i(this,e),this._setInstanceProps(r),this._createCanvas(),this.redraw(),this._addEventListeners(),s.a.observe("locale",function(){return t.updateBadgeText()})}return l(e,[{key:"attachPlayerInstance",value:function(e){e.attachBadgeView(this),this.updateBadgeText(e.effectType)}},{key:"redraw",value:function(){var e=this.progress;e>0&&this.shouldAnimateProgressRing?this._animateProgressRing():this._redraw(e)}},{key:"reset",value:function(){var e=this._requestedFrame;e&&cancelAnimationFrame(e),this._progress=0,this._previousProgress=0,this.redraw()}},{key:"appendTo",value:function(e){e.appendChild(this.element)}},{key:"updateAriaLabel",value:function(){var e=n.a.toLocalizedString(this.effectType),t=s.a.getString("VideoEffects.Badge");this.element.setAttribute("aria-label",t+": "+e)}},{key:"updateBadgeText",value:function(e){e?this.effectType=e:e=this.effectType,this.label=e?n.a.toBadgeText(e):"",this.playbackStyle=n.a.toPlaybackStyle(e),this.updateAriaLabel(),this._redraw()}},{key:"_createCanvas",value:function(){var e=this.element;if(e){if("canvas"!==e.tagName.toLowerCase())throw new Error("Backing element for LivePhotoBadge needs to be an HTMLCanvasElement.")}else e=this.element=document.createElement("canvas");e.setAttribute("role","button"),this.updateAriaLabel(),e.classList.add("lpk-badge"),this._context=e.getContext("2d")}},{key:"_setCanvasSize",value:function(){var e=this.element,t=o.a(),r=this.height,i=this.width;e.height=r*t,e.width=i*t,e.style.height=r+"px",e.style.width=i+"px"}},{key:"_setInstanceProps",value:function(e){var t={};for(var r in d)t.hasOwnProperty.call(d,r)&&(this[r]=e.hasOwnProperty(r)?e[r]:d[r]);this.defaultProps=d}},{key:"_redraw",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0,t=(this.element,this.label),r=t.toLowerCase()||n.a.default;this._setCanvasSize(),this._context.clearRect(0,0,this.width,this.height),this._drawBackground(),this._drawLabel(),this.shouldShowError||(this._drawInnerCircle(),n.a.toPlaybackStyle(r)!==u.a.LOOP?this._drawPlayArrow():this._drawLoopCircle()),this.shouldShowError?(this._drawProgressRing(1),this._drawErrorSlash()):this.progress>0?this._drawProgressRing(e):this._drawDottedCircle()}},{key:"_drawBackground",value:function(){var e=o.a(),t=this._context,r=this.borderRadius*e,i=this.width*e,n=this.height*e;t.beginPath(),t.moveTo(r,0),t.lineTo(i-r,0),t.quadraticCurveTo(i,0,i,r),t.lineTo(i,n-r),t.quadraticCurveTo(i,n,i-r,n),t.lineTo(r,n),t.quadraticCurveTo(0,n,0,n-r),t.lineTo(0,r),t.quadraticCurveTo(0,0,r,0),t.closePath(),t.fillStyle=this.backgroundColor,t.fill()}},{key:"_drawDottedCircle",value:function(){for(var t=e.numberOfDots,r=this.dottedRadius*o.a(),i=0;i<t;i++){var n=this.x0+r*Math.cos(2*Math.PI*i/t),a=this.y0+r*Math.sin(2*Math.PI*i/t);this._drawDot(n,a)}}},{key:"_drawDot",value:function(e,t){var r=this._context,i=1===o.a()?1:1.25;r.beginPath(),r.arc(e,t,i,0,2*Math.PI),r.fillStyle=this.itemColor,r.fill()}},{key:"_drawInnerCircle",value:function(){var e=o.a(),t=this._context,r=this.innerRadius*e;t.beginPath(),t.arc(this.x0,this.y0,r,0,2*Math.PI),t.lineWidth=1===e?1.25:1.5,t.strokeStyle=this.itemColor,t.stroke()}},{key:"_drawPlayArrow",value:function(){var e=o.a(),t=this._context,r=5*e,i=4*e,n=this.x0+.5*e,a=this.y0;t.beginPath(),t.moveTo(n-i/2,a-r/2),t.lineTo(n+i/2,a),t.lineTo(n-i/2,a+r/2),t.fillStyle=this.itemColor,t.fill()}},{key:"_drawLoopCircle",value:function(){var e=o.a(),t=this._context,r=2*e;t.beginPath(),t.arc(this.x0,this.y0,r,0,2*Math.PI),t.fillStyle=this.itemColor,t.fill()}},{key:"_drawLabel",value:function(){var e=o.a(),t=this._context,r=(this.leftPadding+2*this.dottedRadius+this.labelPadding)*e,i=(this.height/2+4.5)*e;t.fillStyle=this.itemColor,t.font=this.fontStyle,t.fillText(this.label,r,i)}},{key:"_drawProgressRing",value:function(e){var t=o.a(),r=this._context,i=this.dottedRadius*t,n=2*Math.PI*.75,a=(.75+e)*(2*Math.PI);r.beginPath(),r.arc(this.x0,this.y0,i,n,a,!1),r.lineWidth=1.5*t,r.strokeStyle=this.itemColor,r.stroke()}},{key:"_drawErrorSlash",value:function(){var e=o.a(),t=this._context,r=this.dottedRadius*e,i=r*Math.sqrt(2)/2;t.beginPath(),t.moveTo(this.x0+i,this.y0+i),t.lineTo(this.x0-i,this.y0-i),t.lineWidth=1.5*e,t.strokeStyle=this.itemColor,t.stroke()}},{key:"_animateProgressRing",value:function(){var e=this,t=this.progress,r=this._previousProgress||0,i=Math.abs(t-r),n=t<r;n&&(t=r,r=this.progress);for(var a=i*this.progressRingAnimationSpeed,o=a/(1e3/60),s=i/o,u=[],l=0,d=o;l<d;l++)u.push(r+l*s);u[u.length-1]=t,n&&u.reverse();!function t(){var r=u.shift();e._redraw(r),u.length&&(e._requestedFrame=window.requestAnimationFrame(t)),1===r&&window.setTimeout(function(){e.progress=0},.25*e.progressRingAnimationSpeed)}()}},{key:"_addEventListeners",value:function(){var e=this,t=this.element,r=void 0;t.addEventListener("mouseup",function(){r=window.setTimeout(function(){e.configurePlayAction()},0)}),t.addEventListener("mouseenter",function(){r=window.setTimeout(function(){e.configurePlayAction()},0)}),t.addEventListener("mouseleave",function(){r&&window.clearTimeout(r),e.configureStopAction()})}},{key:"width",get:function(){var e=this._context;if(!e)return 0;var t=this.dottedRadius,r=this.fontStyle,i=this.label,n=this.labelPadding,a=this.leftPadding;e.font=r;var s=e.measureText(i);this._textMetrics=s;var u=i.length>0?s.width:0;return this._width=(u>2?a:-2)+2*t+2*n+Math.ceil(u/o.a())}},{key:"fontStyle",get:function(){return this.fontSize*o.a()+'pt/1 system, -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica'}},{key:"x0",get:function(){return(this.dottedRadius+this.leftPadding)*o.a()}},{key:"y0",get:function(){return this.height/2*o.a()}},{key:"progress",set:function(e){"number"==typeof e&&(this._previousProgress=this._progress,this._progress=e,this.redraw())},get:function(){return this._progress}},{key:"shouldShowError",set:function(e){this._shouldShowError=!!e,this._redraw(this.progress)},get:function(){return this._shouldShowError}}],[{key:"numberOfDots",get:function(){return 1===o.a()?17:26}}]),e}()},function(e,t,r){"use strict";var i=r(30),n=r(0),a=r(6),o=i.a.extend({mimeType:n.a.observableProperty({dependencies:["_mimeTypeFromXHR"],get:function(e){return this._mimeTypeFromXHR||e||null}}),_mimeTypeFromXHR:n.a.observableProperty(),requiresMimeTypeForRawArrayBufferSrc:!0,exposedMimeTypeKeyForErrorStrings:"mimeType",exposedSrcKeyForErrorStrings:"src",abortCurrentLoad:function(){this.__xhr&&(this._detachXHR(),this._xhr.abort()),this._mimeTypeFromXHR=null,this.abortCurrentSecondaryLoad()},loadSrc:function(e){if("string"==typeof e){this._mimeTypeFromXHR=null,this._attachXHR();var t=this._xhr;t.open("GET",e),t.responseType="arraybuffer",t.send(null)}else if(e instanceof ArrayBuffer){if(!this.mimeType&&this.requiresMimeTypeForRawArrayBufferSrc)throw new Error("MIME Type must be assigned to `"+this.exposedMimeTypeKeyForErrorStrings+"` prior to assigning a raw ArrayBuffer to `"+this.exposedSrcKeyForErrorStrings+"`.");this.beginSecondaryLoad(e,this.mimeType)}},get _xhr(){var e=this.__xhr;return e||(e=this.__xhr=new XMLHttpRequest),e},_detachXHR:function(){var e=this._xhr;e.removeEventListener("progress",this._xhrProgress),e.removeEventListener("readystatechange",this._xhrReadyStateChanged)},_attachXHR:function(){var e=this._xhr;e.addEventListener("progress",this._xhrProgress),e.addEventListener("readystatechange",this._xhrReadyStateChanged)},_xhrReadyStateChanged:function(){if("loading"===this.state){if(this._xhr.readyState>=2&&200!==this._xhr.status){var e=new Error("Failed to download resource from URL assigned to '"+this.exposedSrcKeyForErrorStrings+"'.");return e.errCode=a.a.FAILED_TO_DOWNLOAD_RESOURCE,this.loadDidFail(e)}return 4===this._xhr.readyState&&200===this._xhr.status?this._xhrLoadDidFinish():void 0}},_xhrProgress:function(e){if(e&&e.total){var t=(+e.loaded||0)/e.total;+t===t&&(this.progress=Math.max(0,Math.min(1,t)))}},_xhrLoadDidFinish:function(){this._mimeTypeFromXHR=this._xhr.getResponseHeader("Content-Type"),this.beginSecondaryLoad(this._xhr.response,this.mimeType)},beginSecondaryLoad:function(e,t){this._defaultSecondaryLoadTimeout=setTimeout(this.loadDidSucceed.bind(this,e),0)},abortCurrentSecondaryLoad:function(){this._defaultSecondaryLoadTimeout&&(clearTimeout(this._defaultSecondaryLoadTimeout),this._defaultSecondaryLoadTimeout=null)},init:function(){this._xhrReadyStateChanged=this._xhrReadyStateChanged.bind(this),this._xhrProgress=this._xhrProgress.bind(this),this._super()}});t.a=o},function(e,t,r){"use strict";var i=r(2);t.a=i.a.isEdge||i.a.isIE},function(e,t,r){"use strict";function i(){u.forEach(function(e){return e()})}function n(e){u.push(e)}function a(){return window.devicePixelRatio}function o(){return Math.ceil(a())}t.b=n,t.a=o;var s=void 0,u=[];!function(){window.matchMedia&&(s=window.matchMedia("only screen and (-webkit-min-device-pixel-ratio:1.3),only screen and (-o-min-device-pixel-ratio:13/10),only screen and (min-resolution:120dpi)"),s.addListener(i))}()},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var n=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),a=function(){function e(t){var r=this;i(this,e),this._k=[],this._v=[],t&&t._k.forEach(function(e){r.set(e,t.get(e))})}return n(e,[{key:"_indexOfKey",value:function(e){return this._k.indexOf(e)}},{key:"get",value:function(e){var t=this._indexOfKey(e);return-1===t?void 0:this._v[t]}},{key:"set",value:function(e,t){var r=this._indexOfKey(e);return-1===r&&(r=this._k.push(e)-1),this._v[r]=t,this}},{key:"delete",value:function(e){var t=this._indexOfKey(e);return-1!==t&&(this._k.splice(t,1),this._v.splice(t,1),!0)}},{key:"clear",value:function(){this._k.length>0&&(this._k.length=0,this._v.length=0)}}]),e}();t.a=a},function(e,t,r){"use strict";function i(e){if(null===e)return"_null";if(void 0===e)return"_undefined";if(e.hasOwnProperty("_LPKGUID"))return e._LPKGUID;var t=void 0===e?"undefined":n(e);switch(t){case"number":Object.is(e,-0)&&(e="-0");case"string":case"boolean":return t+e;case"object":case"function":o++;var r=t+o;return a.value=r,Object.defineProperty(e,"_LPKGUID",a),r;default:throw"unrecognized object type"}}t.a=i;var n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},a={value:"",enumerable:!1,writable:!1,configurable:!1},o=0},function(e,t,r){function i(e){return r(n(e))}function n(e){var t=a[e];if(!(t+1))throw new Error("Cannot find module '"+e+"'.");return t}var a={"./en-us.lproj/strings.json":22};i.keys=function(){return Object.keys(a)},i.resolve=n,e.exports=i,i.id=21},function(e,t){e.exports={"VideoEffects.Badge":"Badge","VideoEffects.Badge.Title.Loop":"Loop","VideoEffects.Badge.Title.Bounce":"Bounce","VideoEffects.Badge.Title.LongExposure":"Long Exposure"}},function(e,t,r){"use strict";var i=r(28),n=r(32),a=r(34),o=r(37),s=r(35),u=r(4),l=r(0),d=r(8),c=r(5),h=r(1);a.a.register(),o.a.register(),s.a.register();var p=d.a.extend({approach:"",autoplay:!0,caption:"",_hasInitialized:!1,_lastRecipe:null,recipe:l.a.observableProperty({get:function(){var e=u.a.getRecipeFromPlaybackStyle(this.playbackStyle);return this._setRecipe(e),e},set:function(e){this._setRecipe(e)}}),_setRecipe:function(e){e&&e!==this._lastRecipe&&(this._lastRecipe=e,this.setUpRenderLayers())},requestMoreCompatibleRecipe:function(){this.recipe=this.recipe.requestMoreCompatibleRecipe()},duration:l.a.observableProperty({dependencies:["recipe","provider.videoDuration","provider.photoTime"],get:function(e){var t=this.recipe,r=this.provider,i=r.photoTime,n=r.videoDuration;return t?t.calculateAnimationDuration(e,n,i):0}}),displayWidth:0,displayHeight:0,get backingWidth(){return Math.round(this.displayWidth*devicePixelRatio)},get backingHeight(){return Math.round(this.displayHeight*devicePixelRatio)},get renderLayerWidth(){return this.displayWidth},get renderLayerHeight(){return this.displayHeight},get videoWidth(){return this.videoDecoder.videoWidth},get videoHeight(){return this.videoDecoder.videoHeight},photoWidth:l.a.proxyProperty("photo.width"),photoHeight:l.a.proxyProperty("photo.height"),photo:l.a.proxyProperty("provider.photo"),video:l.a.proxyProperty("provider.video"),photoTime:l.a.proxyProperty("provider.photoTime"),frameTimes:l.a.proxyProperty("provider.frameTimes"),effectType:l.a.proxyProperty("provider.effectType"),preloadedEffectType:l.a.proxyProperty("provider.preloadedEffectType"),playbackStyle:l.a.proxyProperty("provider.playbackStyle"),currentTime:l.a.observableProperty({defaultValue:0,dependencies:["duration"],get:function(e){return Math.min(this.duration||0,Math.max(0,e||0))},didChange:function(e){this.prepareToRenderAtTime(e)}}),canRenderCurrentTime:l.a.observableProperty({readOnly:!0,dependencies:["currentTime"],get:function(){return this.canRenderAtTime(this.currentTime)}}),_currentTimeRenderObserver:l.a.observer("currentTime","canRenderCurrentTime",function(e,t){t&&(this.renderedTime=e)}),renderedTime:l.a.observableProperty({defaultValue:0,didChange:function(e){this.renderAtTime(e),this.currentTime=e}}),areAllRenderLayersPrepared:l.a.observableProperty({defaultValue:!1}),isFullyPreparedForPlayback:l.a.observableProperty({readOnly:!0,dependencies:["video","areAllRenderLayersPrepared","photoTime","frameTimes","playbackStyle"],get:function(){return Boolean(this.video&&this.areAllRenderLayersPrepared&&(this.photoTime||this.playbackStyle!==h.a.HINT)&&Array.isArray(this.frameTimes))}}),cannotRenderDueToMissingPhotoTimeOrFrameTimes:l.a.observableProperty({readOnly:!0,dependencies:["video","areAllRenderLayersPrepared","photoTime","frameTimes","playbackStyle"],get:function(){return Boolean(this.video&&this.areAllRenderLayersPrepared&&(!this.photoTime&&this.playbackStyle===h.a.HINT||!Array.isArray(this.frameTimes)))}}),renderLayers:l.a.property(function(){return[]}),videoDecoder:l.a.observableProperty(function(){return this._videoDecoderClass.create({owner:this})}),_videoDecoderClass:i.a.extend({owner:l.a.observableProperty(),provider:l.a.proxyProperty("owner.provider")}),provider:l.a.observableProperty(function(){return n.a.create()}),init:function(){this._super(),this.element.className=((this.element.className||"")+" lpk-live-photo-renderer").trim(),this.element.style.position="absolute",this.element.style.overflow="hidden",this.element.style.textAlign="left"},updateSize:function(e,t){if(!arguments.length)return void(this.displayWidth&&this.displayHeight&&this.updateSize(this.displayWidth,this.displayHeight));this.displayWidth=e=Math.round(e),this.displayHeight=t=Math.round(t),this.element.style.width=e+"px",this.element.style.height=t+"px";for(var r,i=0;r=this.renderLayers[i];i++)r.updateSize(this.renderLayerWidth,this.renderLayerHeight)},_imageOrVideoDidEnterOrLeave:l.a.observer("videoDecoder.canProvideFrames","photo",function(){this.prepareToRenderAtTime(this.currentTime)}),prepareToRenderAtTime:l.a.boundFunction(function(e){this.propertyChanged("canRenderCurrentTime");for(var t,r=!0,i=0;t=this.renderLayers[i];i++)r=t.prepareToRenderAtTime(e)&&r;this.areAllRenderLayersPrepared=r}),canRenderAtTime:function(e){if(0===e)return!0;if(!this.duration&&e)return!1;for(var t,r=!0,i="",n=0;t=this.renderLayers[n];n++)t.canRenderAtTime(e)||(r=!1,i+=(i?", ":"Cannot render; waiting for ")+t.layerName);return i&&c.a.log(i+"."),r},renderAtTime:function(e){if(this.duration)for(var t,r=0;t=this.renderLayers[r];r++)t.renderAtTime(e)},getNewRenderLayers:function(){return this.recipe.getRenderLayers(this)},setUpRenderLayers:function(){var e=this.renderLayers;e&&this._cleanUpRenderLayers(e),this.renderLayers=this.getNewRenderLayers(),this.updateSize(),this.currentTime=0,this.prepareToRenderAtTime(0)},_cleanUpRenderLayers:function(e){for(var t,r=0;t=e[r];r++)t.dispose(),t.tearDownFromRender()},reduceMemoryFootprint:function(){for(var e,t=0;e=this.renderLayers[t];t++)e.reduceMemoryFootprint()},_clearRetainedFramesWhenNecessary:l.a.observer("provider.videoRotation","provider.frameTimes",function(){this.reduceMemoryFootprint(),this.prepareToRenderAtTime(this.currentTime)})});t.a=p},function(e,t,r){"use strict";var i=r(23),n=i.a.extend({approach:"dom"});t.a=n},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=r(14),n=r(9),a=r(10),o=r(11);r.d(t,"augmentElementAsPlayer",function(){return o.a}),r.d(t,"createPlayer",function(){return o.b}),r.d(t,"Player",function(){return o.c});var s=r(6);r.d(t,"Errors",function(){return s.a});var u=r(15);r.d(t,"LivePhotoBadge",function(){return u.a});var l=r(1);r.d(t,"PlaybackStyle",function(){return l.a}),r.d(t,"Localization",function(){return d}),r.d(t,"BUILD_NUMBER",function(){return c}),r.d(t,"MASTERING_NUMBER",function(){return h}),r.d(t,"VERSION",function(){return p}),r.d(t,"LIVEPHOTOSKIT_LOADED",function(){return f});var d={get locale(){return a.a.locale},set locale(e){a.a.locale=e}},c=i.a.BUILD_NUMBER,h=i.a.MASTERING_NUMBER,p=i.a.VERSION,f=i.a.LIVEPHOTOSKIT_LOADED,v="undefined"!=typeof window&&"undefined"!=typeof document;if(v){var y=window.document;setTimeout(function(){return y.dispatchEvent(r.i(n.a)())});if(y.styleSheets&&document.head){for(var m=null,g=null,b=0;b<y.styleSheets.length;++b)try{var _=y.styleSheets[b];if(_.cssRules)for(var P=0;P<_.cssRules.length;++P){var k=_.cssRules[P];if(-1!==k.cssText.indexOf(".lpk-live-photo-player")){m=k;break}}if(m){g=_;break}}catch(e){if("SecurityError"!==e.name&&"Access is denied."!==e.message.substring(0,17)&&"Member not found."!==e.message.substring(0,17))throw e}if(!g){var T=document.createElement("style");T.type="text/css",document.head.appendChild(T),g=T.sheet}var x="user-select: none;-khtml-user-select: none; -moz-user-select: none;-ms-user-select: none;-webkit-touch-callout: none; -webkit-user-select: none;";g.addRule?g.addRule(".lpk-live-photo-player",x):g.insertRule&&g.insertRule(".lpk-live-photo-player {"+x+"}",0)}}if(v&&document.querySelectorAll instanceof Function){var S=function(){F=!0,Array.prototype.forEach.call(document.querySelectorAll("[data-live-photo]"),function(e){return r.i(o.a)(e)})},w=function(){!F&&O&&C&&S()},O=/interactive|complete|loaded/.test(document.readyState),C=!!window.LivePhotosKit,F=!1;O||document.addEventListener("DOMContentLoaded",function(){O=!0,w()}),C||document.addEventListener("livephotoskitloaded",function(){C=!0,w()}),w()}},function(e,t,r){"use strict";var i=r(8),n=r(18),a=r(15);r.d(t,"a",function(){return o});var o=i.a.extend({badgeView:null,init:function(){var e=this;this._super(),this._createBadgeView(),n.b(function(){return e.badgeView.redraw()})},_createBadgeView:function(){this.badgeView=new a.a,this.element.appendChild(this.badgeView.element);var e=this.badgeView.element.style;e.top="10px",e.left="10px",e.position="absolute",e.zIndex=4},updateToRendererLayout:function(e,t,r,i){var n=this.badgeView,a=n.element.style;a.left=e+10+"px",a.top=t+10+"px",a.right=""},redraw:function(){this.badgeView.redraw()}})},function(e,t,r){"use strict";function i(e){if(Array.isArray(e)){for(var t=0,r=Array(e.length);t<e.length;t++)r[t]=e[t];return r}return Array.from(e)}function n(e){if(!e)return!1;try{e.appendChild(f),e.removeChild(f)}catch(e){return!1}return!0}var a=r(8),o=r(24),s=r(26),u=r(54),l=r(0),d=r(9),c=r(53),h=r(1),p=a.a.extend({staticMembers:{activeInstance:l.a.observableProperty(null)},renderer:l.a.observableProperty(function(){return o.a.create()}),showsNativeControls:l.a.observableProperty(!0),isPlaying:l.a.observableProperty(!1),wantsToPlay:l.a.observableProperty({defaultValue:!1,didChange:function(e){e&&(this.constructor.activeInstance=this)}}),canPlay:l.a.observableProperty({readOnly:!0,dependencies:["isPlaying","renderer.isFullyPreparedForPlayback"],get:function(){return this.isPlaying||this.renderer.isFullyPreparedForPlayback},didChange:function(e){e&&(this._hasHadCanPlay=!0,this.dispatchEvent(r.i(d.b)()),this.wantsToPlay&&this.play())}}),_generateErrorIfPlayedWithoutNecessaryMetadata:l.a.observer("renderer.cannotRenderDueToMissingPhotoTimeOrFrameTimes","wantsToPlay",function(e){e&&this.wantsToPlay&&(this._cannotRenderDueToMissingPhotoTimeOrFrameTimesError=new Error("The `photoTime` and/or `frameTimes` values are missing. Provide them directly (or via `metadataVideoSrc`) if they cannot be parsed from the video.")),e||(this._cannotRenderDueToMissingPhotoTimeOrFrameTimesError=null)}),_cannotRenderDueToMissingPhotoTimeOrFrameTimesError:l.a.observableProperty(),_playerErrors:l.a.observableProperty({readOnly:!0,dependencies:["_cannotRenderDueToMissingPhotoTimeOrFrameTimesError"],get:function(){var e=this._cannotRenderDueToMissingPhotoTimeOrFrameTimesError;return e?[e]:null}}),_captionChanged:l.a.observer("caption",function(e){var t="";e&&(t=": "+e),this.element.setAttribute("aria-label","Live Photo"+t)}),_effectTypeChanged:l.a.observer("effectType",function(e){this.badgeView&&e&&this.updateBadgeText()}),errors:l.a.observableProperty({readOnly:!0,dependencies:["provider.errors","_playerErrors"],get:function(){var e=this.provider.errors,t=this._playerErrors,r=[];return e&&e.length&&r.push.apply(r,i(e)),t&&t.length&&r.push.apply(r,i(t)),r}}),lastError:l.a.observableProperty({dependencies:["provider.lastError"],get:function(e){return e||this.provider.lastError||null},didChange:function(e){e&&(this.throwError(e),this.stop())}}),playbackRate:l.a.proxyProperty("renderer.videoDecoder.playbackRate"),currentTime:l.a.proxyProperty("renderer.currentTime"),renderedTime:l.a.proxyProperty("renderer.renderedTime"),duration:l.a.proxyProperty("renderer.duration"),videoWidth:l.a.proxyProperty("renderer.videoWidth"),videoHeight:l.a.proxyProperty("renderer.videoHeight"),photoWidth:l.a.proxyProperty("renderer.photoWidth"),photoHeight:l.a.proxyProperty("renderer.photoHeight"),recipe:l.a.proxyProperty("renderer.recipe"),requiresInterpolation:l.a.proxyProperty("renderer.recipe.requiresInterpolation"),effectType:l.a.proxyProperty("provider.effectType"),preloadedEffectType:l.a.proxyProperty("renderer.preloadedEffectType"),playbackStyle:l.a.proxyProperty("renderer.playbackStyle"),provider:l.a.proxyProperty("renderer.provider"),proactivelyLoadsVideo:l.a.proxyProperty("provider.proactivelyLoadsVideo"),metadataVideoSrc:l.a.proxyProperty("provider.metadataVideoSrc"),photoMimeType:l.a.proxyProperty("provider.photoMimeType"),photoSrc:l.a.proxyProperty("provider.photoSrc"),photo:l.a.proxyProperty("provider.photo"),videoMimeType:l.a.proxyProperty("provider.videoMimeType"),videoSrc:l.a.proxyProperty("provider.videoSrc"),video:l.a.proxyProperty("provider.video"),photoTime:l.a.proxyProperty("provider.photoTime"),frameTimes:l.a.proxyProperty("provider.frameTimes"),videoRotation:l.a.proxyProperty("provider.videoRotation"),loadProgress:l.a.proxyProperty("provider.progress"),autoplay:l.a.proxyProperty("renderer.autoplay"),caption:l.a.proxyProperty("renderer.caption"),_isZeroSizeWarningLogged:l.a.observableProperty(!1),_renderWhenPossible:l.a.observer("renderer.video","renderer.photo",function(){if(this._cannotRenderDueToMissingPhotoTimeOrFrameTimesError=null,this.updateSize(!0),!this._isZeroSizeWarningLogged){var e=this.element.getBoundingClientRect();0!==e.width&&0!==e.height||(console.warn("The LivePhotosKit Player located at position ("+e.left+", "+e.top+") in the viewport has either a zero width or zero height (or both) and will not render. To fix this, ensure that the Player has a style that will yield a non-zero width and height."),this._isZeroSizeWarningLogged=!0)}}),attachBadgeView:function(e){var t=this;this.badgeView=e,this.updateBadgeText(),e.configurePlayAction(function(){return t.play()}),e.configureStopAction(function(){return t.beginFinishingPlaybackEarly()})},updateBadgeText:function(){this.badgeView.updateBadgeText(this.effectType)},nativeControls:l.a.observableProperty({readOnly:!0,dependencies:["showsNativeControls"],get:function(){var e=this;return this.showsNativeControls?this._nativeControls_cachedValue||(this._nativeControls_cachedValue=s.a.extend({owner:l.a.observableProperty(this),_slurpProgress:l.a.observer("owner.provider.progress",function(e){this.badgeView&&(this.badgeView.progress=e)}),_slurpError:l.a.observer("owner.errors",function(e){this.badgeView&&(this.badgeView.shouldShowError=!!e&&e.length>0)}),init:function(){this._super.apply(this,arguments),e.attachBadgeView(this.badgeView)}}).create()):null},didChange:function(e){this._nativeControls_previousValue&&this._nativeControls_previousValue.detach(),this._nativeControls_previousValue=e,e&&e.attachInto(this)}}),init:function(e,t){var i=this;if(e&&!n(e))throw"Any pre-existing element provided for use as a LivePhotosKit.Player must be able to append child DOM nodes.";e&&e.childNodes.length&&(e.innerHTML="");for(var a in t)Object.prototype.hasOwnProperty.call(t,a)&&(this[a]=t[a]);this._super(e);switch(this.element.className.indexOf("lpk-live-photo-player")<0&&(this.element.className=this.element.className+" lpk-live-photo-player"),this.element.setAttribute("role","image"),r.i(c.a)(this.element,"position")||this.element.style.position){case"absolute":case"fixed":case"relative":break;default:this.element.style.position="relative"}switch(r.i(c.a)(this.element,"display")||this.element.style.display){case"block":case"inline-block":case"table":case"table-caption":case"table-column-group":case"table-header-group":case"table-footer-group":case"table-row-group":case"table-cell":case"table-column":case"table-row":break;default:this.element.style.display="inline-block"}this.renderer.attachInto(this),this.renderer.eventDispatchingElement=this.element,window.addEventListener("resize",this.updateSize),"ontouchstart"in document.documentElement&&(this.addEventListener("touchstart",function(){return i.play()},!1),this.addEventListener("touchend",function(){return i.beginFinishingPlaybackEarly()},!1))},play:function(){if(!this.isPlaying){var e=this.provider;e.video||(e.needsLoadedVideoForPlayback=!0),this.wantsToPlay=!0,this.canPlay&&(this.isPlaying=!0,this._lastFrameNow=Date.now(),this._nextFrame())}return this.isPlaying},pause:function(){this.isPlaying=!1,this.wantsToPlay=!1,this._cancelNextFrame()},stop:function(){this.pause(),this.currentTime=0,this.renderer.duration=NaN},toggle:function(){this.wantsToPlay?this.pause():this.play()},beginFinishingPlaybackEarly:function(){this.recipe.beginFinishingPlaybackEarly(this)},_stopWhenAnotherPlayerStarts:l.a.observer("_constructor.activeInstance",function(e){e&&e!==this&&(this.stop(),this.renderer.reduceMemoryFootprint())}),_constructor:l.a.observableProperty(function(){return p}),_stopPlaybackWhenItemsLoadOrUnload:l.a.observer("video","photo",function(){!this.isPlaying||this.playbackStyle===h.a.LOOP&&this.autoplay||this.stop()}),addEventListener:function(e,t,r){var i=this.element;i.addEventListener.call(i,e,t,r)},removeEventListener:function(e,t,r){var i=this.element;i.removeEventListener.call(i,e,t,r)},_nextFrame:function(){var e=Date.now(),t=(e-this._lastFrameNow)*this.playbackRate;this._lastFrameNow=e,this.currentTime===this.renderedTime&&(this.currentTime+=t/1e3),this.recipe&&this.recipe.continuePlayback(this)},_cancelNextFrame:function(){cancelAnimationFrame(this._rafID)},updateSize:l.a.boundFunction(function(e,t){if(this.photoWidth&&this.photoHeight){var i=!0===e?void 0:e,n=!0===e?e:void 0;if(isNaN(i)||isNaN(t)?(i=this.element.offsetWidth,t=this.element.offsetHeight):(i=Math.round(i),t=Math.round(t),this.element.style.width=i+"px",this.element.style.height=t+"px"),i&&t){if(!(this._lastUpdateChangeToken!==(this._lastUpdateChangeToken=i+":"+t))&&!n)return!1;var a=r.i(u.a)(this.photoWidth,this.photoHeight,i,t),o=Math.ceil(a.height),s=Math.ceil(a.width),l=Math.floor(i/2-s/2),d=Math.round(t/2-o/2),c=this.renderer;c.element.style.top=d+"px",c.element.style.left=l+"px",c.updateSize(s,o),this.displayWidth=i,this.displayHeight=t,this.nativeControls&&this.nativeControls.updateToRendererLayout(l,d,s,o)}}}),_dispatchPhotoLoadEventOnNewPhoto:l.a.observer("photo",function(e){e&&this.dispatchEvent(r.i(d.c)())}),_dispatchVideoLoadEventOnNewVideo:l.a.observer("video",function(e){e&&this.dispatchEvent(r.i(d.d)())}),throwError:function(e){this.dispatchEvent(r.i(d.e)({error:e,errorCode:e.errCode}))}}),f=document.createElement("div");t.a=p},function(e,t,r){"use strict";function i(){f=!1}function n(){}function a(e,t){return-(e.importance-t.importance)||e.number-t.number}function o(e,t){for(var r=0,i=e.length,n=0;n<i-r;n++)if(e[n]===t&&(r++,n--),r){var a=n+1;e[a]=e[a+r]}return e.length-=r,e}var s=r(52),u=r(29),l=r(0),d=r(17),c=r(2),h=-1!==location.href.indexOf("_lpk_play_to_decode=true")||-1===location.href.indexOf("_lpk_play_to_decode=false")&&(!c.a.isSafari&&!c.a.isIOS),p=-1!==location.href.indexOf("_lpk_persistent_frames=true")||(location.href.indexOf("_lpk_persistent_frames=false"),!1),f=!0,v=l.a.Object.extend({id:l.a.property(function(){return r.i(s.a)()}),provider:l.a.observableProperty(),video:l.a.proxyProperty("provider.video"),duration:l.a.proxyProperty("provider.videoDuration"),videoRotation:l.a.proxyProperty("provider.videoRotation"),frameTimes:l.a.proxyProperty("provider.frameTimes"),requiresInterpolation:l.a.proxyProperty("renderer.recipe.requiresInterpolation"),get frameCount(){var e=this.frameTimes;return e?e.length:0},shouldPrepareToSeek:!1,canProvideFrames:l.a.observableProperty({readOnly:!0,dependencies:["video"],get:function(){return!!this.video}}),videoWidth:l.a.observableProperty({readOnly:!0,dependencies:["video","videoRotation"],get:function(){var e=this.video;if(e)return this.videoRotation%180==0?e.videoWidth:e.videoHeight}}),videoHeight:l.a.observableProperty({readOnly:!0,dependencies:["video","videoRotation"],get:function(){var e=this.video;if(e)return this.videoRotation%180==0?e.videoHeight:e.videoWidth}}),playbackRate:l.a.observableProperty(1),_sendPlaybackRateToVideo:l.a.observer("playbackRate","video",function(e,t){t&&(t.playbackRate=e)}),_pendingFrames:l.a.property(function(){return[]}),init:function(){this._largeCanvas=document.createElement("canvas"),this._largeContext=this._largeCanvas.getContext("2d"),this._super.apply(this,arguments)},_handleVideoChange:l.a.observer("video",function(){var e=this.video,t=this._lastVideo;if(this._lastVideo=e,e!==t&&(t&&this.cleanUpOldVideo(t),e&&this.setUpNewVideo(e),e&&p))for(var r=d.a?1:0;this.frameTimes&&r<this.frameTimes.length;r++)this.getFrame(r).retain()}),setUpNewVideo:function(e){e.addEventListener("seeked",this._seeked),e.muted=!0},cleanUpOldVideo:function(e){e.removeEventListener("seeked",this._seeked),e.playbackRate=1,e.muted=!1,e.pause(),this._stopSeekingEntirely()},fractionalIndexForTime:function(e){if(e=Math.min(this.duration,Math.max(0,e)),isNaN(e))return e;var t=this.frameTimes,r=t.length,i=void 0;for(i=0;i<r&&t[i]<e;i++);i&&i--;var n=t[i],a=t[i+1];return a?i+Math.min(1,(e-n)/(a-n)):i},timeForFractionalIndex:function(e){if(e<=0)return 0;if(e>=this.frameTimes.length)return this.duration;var t=0|e,r=Math.ceil(e);if(t===r)return this.frameTimes[t];var i=this.frameTimes[t],n=r<this.frameTimes.length?this.frameTimes[r]:this.duration;return n?i+(e-t)*(n-i):i},getFrame:function(e,t){if(!this.frameTimes)throw"Attempted to get frame before ready.";if(isNaN(this.frameTimes[e]))throw"Frame number "+e+" is is not a frame in the video.";var r=u.a.getCached(this,e);return isNaN(t)||(r.importance=Math.max(r.importance,t)),r.readyState||(this._pendingFrames.push(r),this._pendingFrames.sort(a),r.didPend(),this._isSeeking||this._scheduleArtificialSeek()),r},peekFrame:function(e){var t=u.a.peekCached(this,e);return t&&2===t.readyState&&!t.lacksOwnPixelData?t:null},getNearestDecodedFrame:function(e){for(var t=Math.max(e,this.frameTimes.length-1-e),r=-1;++r<=t;){var i=this.peekFrame(e+r)||this.peekFrame(e-r);if(i)return i}return null},_scheduleArtificialSeek:function(){this._artificialSeekTimeout||(this._artificialSeekTimeout=setTimeout(this._seeked),this._artificialSeekRAFId=requestAnimationFrame(this._seeked))},_unscheduleArtificialSeek:function(){this._artificialSeekTimeout&&(clearTimeout(this._artificialSeekTimeout),this._artificialSeekTimeout=null,cancelAnimationFrame(this._artificialSeekRAFId),this._artificialSeekRAFId=null)},_stopSeekingEntirely:function(){this._unscheduleArtificialSeek(),this._isPlaying=!1,this._isSeeking=!1,this._expectedNextSeenFrameNumber=NaN,this.video&&this.video.pause()},_seeked:l.a.boundFunction(function(e){var t=this._pendingFrames;if((0!==t.length||this.requiresInterpolation)&&!(e instanceof Event&&e.target!==this.video)&&(e instanceof Event||this.video)){this._unscheduleArtificialSeek(),this._isSeeking=!1;var r=this.fractionalIndexForTime(this.video.currentTime);r|=0;for(var a,o=NaN,s=0;a=t[s];s++)if(a.number===r){o=r,a.didDecode(),this._removePendingFrame(a);break}var u=this._expectedNextSeenFrameNumber;if(this._expectedNextSeenFrameNumber=NaN,u<o)for(var l,d=0;l=t[d];d++)l.number>=u&&l.number<o&&(l.didGetSkipped(),this._removePendingFrame(l),d--);var c=t[0];if(!c)return this._isPlaying&&this.requiresInterpolation&&(this._isPlaying=!1,this.video.pause()),void(this._expectedNextSeenFrameNumber=NaN);var p=c.number>r&&c.number<=r+2&&f;if(h||(p=!1),p){if(!this._isPlaying){this._isPlaying=!0;try{var v=this.video.play();v&&v.then instanceof Function&&v.then(n,i)}catch(e){f=!1}}this._expectedNextSeenFrameNumber=c.number,this._scheduleArtificialSeek()}else this._isPlaying&&(this._isPlaying=!1,this.video.pause()),this._expectedNextSeenFrameNumber=NaN,this.video.currentTime=c.time+1e-4,this._isSeeking=!0}}),_frameWillDispose:function(e){this._removePendingFrame(e)},_removePendingFrame:function(e){o(this._pendingFrames,e),this._pendingFrames.length||this._unscheduleArtificialSeek()}});t.a=v},function(e,t,r){"use strict";function i(e){e.container=document.createElement("div"),e.container.frame=e,e.container.innerHTML='<div style="position:absolute; left:0; right:0; top:0; bottom:0; text-align:center; line-height:30px; color:white; text-shadow: black 0px 0px 4px,black 0px 0px 4px,black 0px 0px 4px,black 0px 0px 4px; font-family:HelveticaNeue-Light;"></div>',e.textBox=e.container.lastChild,e.container.insertBefore(e.image,e.textBox),e.image.style.position="absolute",e.container.style.cssText="position:relative; display:inline-block; border: 1px solid black;";var t=e._debug_aspect||(e._debug_aspect=e.videoDecoder&&(e.videoDecoder.videoWidth>e.videoDecoder.videoHeight?"landscape":"portrait"));e.container.style.width=e.image.style.width="landscape"===t?"40px":"30px",e.container.style.height=e.image.style.height="landscape"===t?"30px":"40px",document.body.appendChild(e.container)}var n=r(12),a=r(48),o=r(5),s=r(0),u=r(46),l=r(2);r.d(t,"a",function(){return d});var d=s.a.Object.extend(u.a,a.a,{staticMembers:{getPoolingCacheKey:function(e,t){return"f"+t+"_in_"+e.id}},container:null,image:null,_context:null,number:-1,time:-1,importance:0,videoDecoder:null,readyState:0,_poolingCacheKey:null,_debugShowInDOM:n.a,lacksOwnPixelData:!1,_postDispose:function(){this.image.width=this.image.height=0},get backingFrame(){return this.lacksOwnPixelData?this.videoDecoder.getNearestDecodedFrame(this.number)||this:this},init:function(){this._postDispose=this._postDispose.bind(this);var e=this.image=document.createElement("canvas");this._context=this.image.getContext("2d"),this._super(),this._debugShowInDOM?i(this):h&&(h.appendChild(e),e.style.cssText="position: absolute; top: 0px; width:1px; height: 1px; display: inline-block;",e.style.left=c+++"px")},initFromPool:function(e,t){clearTimeout(this._postDisposalTimeout),this.videoDecoder=e,this.number=t,this.time=e.frameTimes[t],this._debugShowInDOM&&(this.textBox.innerHTML=this.number)},dispose:function(){this.resetReadiness(),this.videoDecoder._frameWillDispose(this),this.number=this.time=-1,this.importance=0,this.videoDecoder=null,this.readyState=0,this.lacksOwnPixelData=!1,this._postDisposalTimeout=setTimeout(this._postDispose,3e3),this.constructor._disposeInstance(this),this._debugShowInDOM&&(this.textBox.innerHTML="x",this.textBox.style.color="#FF0000",this._context.clearRect(0,0,this.image.width,this.image.height))},didPend:function(){this.readyState=1,this._debugShowInDOM&&(this.textBox.style.color="#FF8800")},didDecode:function(){this.obtainPixelData(),this.readyState=2,this.resolveReadiness(this),this._debugShowInDOM&&(this.textBox.style.color="#00FF00")},obtainPixelData:function(){var e=this.image,t=this._context,r=this.videoDecoder,i=r.videoRotation,n=r.videoWidth,a=r.videoHeight,o=i%180==0?n:a,s=i%180==0?a:n;e.width===n&&e.height===a||(e.width=n,e.height=a),l.a.isFirefox&&t.getImageData(0,0,1,1);for(var u=0;u<i;u+=90)t.translate(u%180?a:n,0),t.rotate(Math.PI/2);t.drawImage(r.video,0,0,o,s),t.setTransform(1,0,0,1,0,0)},didGetSkipped:function(){o.a.log("    Skipped decoding frame "+this.number+". Using nearest frame instead."),this.lacksOwnPixelData=!0,this.readyState=2,this.resolveReadiness(this),this._debugShowInDOM&&(this._context.fillStyle="red",this._context.fillRect(0,0,this.image.width,this.image.height),this.textBox.style.color="#00FF00")}}),c=0,h=function(){if(!l.a.isIE&&!l.a.isEdge)return null;var e=document.createElement("div");return e.style.cssText="top: 0px; left: 0px; width: 50px; height: 1px; overflow: hidden; position: absolute; z-index: 100000; opacity: 0.001; pointer-events: none;",document.body?document.body.appendChild(e):setTimeout(function(){return document.body.appendChild(e)},0),e}()},function(e,t,r){"use strict";var i=r(0),n=i.a.Object.extend({state:i.a.observableProperty("unloaded"),validateResult:function(e){return!!e},loadSrc:function(e){},abortCurrentLoad:function(){},isSrcLoadAllowed:i.a.observableProperty(!0),_loadSrcOnceAllowed:i.a.observer("isSrcLoadAllowed",function(e){if(e){if("unloaded"===this.state){var t=this.src;t&&(this.state="loading",this.loadSrc(t))}}}),src:i.a.observableProperty({didChange:function(e){this._isWritingSrcDueToWritingOfResult||(this._isWritingResultDueToWritingOfSrc=!0,this.result=null,this._isWritingResultDueToWritingOfSrc=!1,this.abortCurrentLoad(),e&&this.isSrcLoadAllowed?(this.state="loading",this.loadSrc(e)):this.state="unloaded")}}),result:i.a.observableProperty({decideRequiresUpdate:function(){return!0},didChange:function(e){if(!this._isWritingResultDueToWritingOfSrc){var t=this.validateResult(e)?"loaded":e||this._isWritingResultDueToError?"errored":"unloaded";this._isWritingResultDueToLoadOfSrc||this._isWritingResultDueToError||(this._isWritingSrcDueToWritingOfResult=!0,this.src=null,this._isWritingSrcDueToWritingOfResult=!1,this.abortCurrentLoad(),this.state="unloaded"),this.state=t}}}),error:i.a.observableProperty(null),_clearErrorOnceNoLongerErrored:i.a.observer("state",function(e){"errored"!==e&&(this.error=null)}),progress:i.a.observableProperty({defaultValue:0,dependencies:["state"],get:function(e){return"loading"===this.state?Math.min(1,Math.max(0,+e||0)):"loaded"===this.state?1:0}}),_clearProgressOnceUnloaded:i.a.observer("state",function(e){"unloaded"===e&&(this.progress=0)}),loadDidFail:function(e){"loading"===this.state&&(this._isWritingResultDueToError=!0,this.result=null,this._isWritingResultDueToError=!1,this.error=e)},loadDidSucceed:function(e){"loading"===this.state&&(this._isWritingResultDueToLoadOfSrc=!0,this.result=e,this._isWritingResultDueToLoadOfSrc=!1,this.error=null)}});t.a=n},function(e,t,r){"use strict";function i(e,t,r){var i=e.getContext("2d"),n=7===r||8===r?270:3===r||4===r?180:5===r||6===r?90:0,a=t.naturalWidth,o=t.naturalHeight,s=n%180==0?a:o,u=n%180==0?o:a,l=2===r||4===r?-1:1,d=5===r||7===r?-1:1;e.width===s&&e.height===u||(e.width=s,e.height=u);for(var c=0;c<n;c+=90)i.translate(c%180?u:s,0),i.rotate(Math.PI/2);i.scale(l,d),i.translate((l-1)/2*a,(d-1)/2*o),i.drawImage(t,0,0),i.setTransform(1,0,0,1,0,0)}var n=r(16),a=r(51),o=r(6),s=r(3),u=[],l={},d=n.a.extend({mimeType:"image/jpeg",beginSecondaryLoad:function(e,t){var i=r.i(a.a)(e);this._exifOrientationInLastLoadedBuffer=i.orientation||NaN,3===i.photosRenderEffect&&(this.effectType=s.a.EXPOSURE),u[0]=e,l.type=t,this._internalImage.src=this._internalImageSrc=URL.createObjectURL(new Blob(u,l)),(window.photoBuffers||(window.photoBuffers=[])).push(e)},abortCurrentSecondaryLoad:function(){this.__internalImage&&this._internalImage.removeAttribute("src"),this._internalImageSrc&&(URL.revokeObjectURL(this._internalImageSrc),this._internalImageSrc=null),this._exifOrientationInLastLoadedBuffer=null,this.effectType=null},get _internalImage(){var e=this.__internalImage;return e||(e=this.__internalImage=new Image,e.addEventListener("load",this._internalImageLoadDidSucceed.bind(this)),e.addEventListener("error",function(e){this.loadDidFail(e)}.bind(this))),e},get _internalCanvas(){return this.__internalCanvas||(this.__internalCanvas=document.createElement("canvas"))},loadDidFail:function(e){if(e)return e.errCode=o.a.PHOTO_FAILED_TO_LOAD,this._super(e)},_internalImageLoadDidSucceed:function(){var e=this._internalImage;1!==this._exifOrientationInLastLoadedBuffer&&(i(this._internalCanvas,this._internalImage,this._exifOrientationInLastLoadedBuffer),e=this._internalCanvas),this.loadDidSucceed(e)}});t.a=d},function(e,t,r){"use strict";var i=r(0),n=r(33),a=r(31),o=r(3),s=r(1),u=i.a.Object.extend({videoSrc:i.a.proxyProperty("_videoProvider.src"),videoMimeType:i.a.proxyProperty("_videoProvider.mimeType"),video:i.a.proxyProperty("_videoProvider.result"),effectType:i.a.observableProperty({dependencies:["_photoProvider.effectType","_videoProvider.effectType","preloadedEffectType"],get:function(){return this._photoProvider.effectType||this._videoProvider.effectType||this.preloadedEffectType||o.a.default},didChange:function(e){this.setPlaybackStyle(e)}}),preloadedEffectType:i.a.observableProperty({get:function(e){return e},set:function(e){return this._photoProvider.effectType=null,this._videoProvider.effectType=null,e}}),setPlaybackStyle:function(e){this.effectType=e,this.playbackStyle=o.a.toPlaybackStyle(e)},photoSrc:i.a.proxyProperty("_photoProvider.src"),photoMimeType:i.a.proxyProperty("_photoProvider.mimeType"),photo:i.a.proxyProperty("_photoProvider.result"),playbackStyle:i.a.observableProperty(s.a.default),metadataVideoSrc:i.a.proxyProperty("_metadataVideoProvider.src"),progress:i.a.observableProperty({readOnly:!0,dependencies:["_photoProvider.progress","_videoProvider.state","_videoProvider.progress","_metadataVideoProvider.state","_metadataVideoProvider.progress"],get:function(){var e=this._videoProvider,t=this._metadataVideoProvider,r=e.state,i=t.state,n="unloaded"!==r||"unloaded"!==i?.75:0,a=n?"unloaded"===i?e.progress:(e.progress+t.progress)/2:0;return this._photoProvider.progress*(1-n)+a*n}}),photoTime:i.a.observableProperty({dependencies:["_metadataVideoProvider.photoTime","_videoProvider.photoTime"],get:function(e){return+e===e?e:this._metadataVideoProvider.photoTime||this._videoProvider.photoTime||null}}),videoRotation:i.a.observableProperty({dependencies:["_videoProvider.videoRotation"],get:function(e){if(+e===e)return e;var t=this._videoProvider.videoRotation;return+t===t?t:null},set:function(e){return 90*Math.round(e/90)%360|0}}),frameTimes:i.a.observableProperty({dependencies:["_metadataVideoProvider.frameTimes","_videoProvider.frameTimes"],get:function(e){return e||(this._metadataVideoProvider.frameTimes||this._videoProvider.frameTimes)},set:function(e){if(!e)return null;if("string"==typeof e){var t=i.a.arrayPool.get();i.a.String.splitIntoArray(e,",",t),i.a.Array.mapIntoArray(t,function(e){return Number(e.trim())},t),e=t}for(var r=Array.isArray(e),n=r&&e.length>=2,a=0,o=e.length;a<o&&n;a++){var s=e[a];+s!==s&&(n=!1)}if(!n)throw new Error("If frameTimes is provided, it must be provided as an Array or comma-delimited string containing numbers.");return e}}),videoDuration:i.a.observableProperty({readOnly:!0,dependencies:["frameTimes"],get:function(){return this.frameTimes?this.frameTimes[this.frameTimes.length-1]:void 0}}),proactivelyLoadsVideo:i.a.observableProperty(!1),needsLoadedVideoForPlayback:i.a.observableProperty(!1),_reset_needsLoadedVideoForPlayback_whenAppropriate:i.a.observer("video",function(){this.needsLoadedVideoForPlayback=!1}),isVideoLoadAllowed:i.a.observableProperty({readOnly:!0,dependencies:["proactivelyLoadsVideo","needsLoadedVideoForPlayback"],get:function(){return this.proactivelyLoadsVideo||this.needsLoadedVideoForPlayback}}),errors:i.a.observableProperty({readOnly:!0,dependencies:["_videoProvider.error","_photoProvider.error"],get:function(){return[this._photoProvider.error,this._videoProvider.error].filter(function(e){return!!e})}}),lastError:i.a.observableProperty(),_lastPhotoError:i.a.observableProperty({readOnly:!0,dependencies:["_photoProvider.error"],get:function(){return this._photoProvider.error},didChange:function(e){this.lastError=e}}),_lastVideoError:i.a.observableProperty({readOnly:!0,dependencies:["_videoProvider.error"],get:function(){return this._videoProvider.error},didChange:function(e){this.lastError=e}}),_metadataVideoProvider:i.a.observableProperty(function(){return n.a.extend({requiresMimeTypeForRawArrayBufferSrc:!1,actuallyProvidesResultingVideoFromSecondaryLoad:!1}).create()}),_videoProvider:i.a.observableProperty(function(){return n.a.extend({owner:i.a.observableProperty(this),isSrcLoadAllowed:i.a.proxyProperty("owner.isVideoLoadAllowed"),exposedSrcKeyForErrorStrings:"videoSrc",exposedMimeTypeKeyForErrorStrings:"videoMimeType"}).create()}),_photoProvider:i.a.observableProperty(function(){return a.a.extend({exposedSrcKeyForErrorStrings:"photoSrc",exposedMimeTypeKeyForErrorStrings:"photoMimeType"}).create()})});t.a=u},function(e,t,r){"use strict";function i(e){var t=[0,0,0,0,0,0,0,0,1];t[0]=n(e.slice(0,4),16,16),t[1]=n(e.slice(4,8),16,16),t[3]=n(e.slice(12,16),16,16),t[4]=n(e.slice(16,20),16,16);for(var r=0;r<m.length;r++){if(a(m[r],t))return 90*r}return 0}function n(e,t,r){var i=e[0]<<24|e[1]<<16|e[2]<<8|e[3];return Math.abs(i>>r)*(0!=(i&1<<t+r-1)?-1:1)}function a(e,t){if(e===t)return!0;if(!e||!t)return!1;var r=e.length;if(r!==t.length)return!1;for(var i=0;i<r;i++)if(e[i]!==t[i])return!1;return!0}var o=r(16),s=r(45),u=r(0),l=r(5),d=r(6),c=r(2),h=r(3),p=[],f={},v=o.a.extend({__internalVideo:null,_internalVideoSRC:null,photoTime:u.a.observableProperty(),playbackStyle:u.a.observableProperty(null),effectType:u.a.observableProperty(null),frameTimes:u.a.observableProperty(),videoRotation:u.a.observableProperty(),actuallyProvidesResultingVideoFromSecondaryLoad:!0,_clearMetadataPropertiesOnUnload:u.a.observer("state",function(e){"loaded"!==e&&(this.photoTime=this.frameTimes=this.videoRotation=void 0)}),beginSecondaryLoad:function(e,t){this.preprocessAndAttemptToReadMetadataFromBuffer(e),this.actuallyProvidesResultingVideoFromSecondaryLoad&&(p[0]=e,f.type="video/quicktime"===t?"video/mp4":t,"application/octet-stream"===t&&(l.a.warn('Encountered a Content-Type of "application/octet-stream" for the file obtained for `videoSrc`.Some browsers may be unable to use the video with this MIME type. If the video portion of the Player is not functioning, make sure the proper MIME type is being provided with the response,or provide the proper MIME type manually by assigning it to the `videoMimeType` property on the Player instance. For now, an attempt will be made to treat the video’s bytes as "video/mp4", but playback is not guaranteed unless either of these corrective steps are taken.'),t="video/mp4"),this._internalVideo.src=this._internalVideoSRC=URL.createObjectURL(new Blob(p,f)),c.a.isIOS&&this._internalVideo.load())},abortCurrentSecondaryLoad:function(){this.__internalVideo&&(this.__internalVideo.pause(),this.__internalVideo.removeAttribute("src"),this.__internalVideo.load(),c.a.isIOS&&(this.__internalVideo=null),this._internalVideoSRC&&(URL.revokeObjectURL(this._internalVideoSRC),this._internalVideoSRC=null),this.effectType=null)},get _internalVideo(){var e=this.__internalVideo;return e||(e=this.__internalVideo=document.createElement("video"),e.addEventListener("canplay",this.loadDidSucceed.bind(this)),e.addEventListener("error",this.loadDidFail.bind(this)),e.volume=0),e},loadDidFail:function(e){if(e)return e.errCode=d.a.VIDEO_FAILED_TO_LOAD,this._super(e);var t=this.__internalVideo;return t.error.errCode=d.a.VIDEO_FAILED_TO_LOAD,t?this._super(t.error):t},loadDidSucceed:function(){var e=this.__internalVideo;return e?this._super(e):e},preprocessAndAttemptToReadMetadataFromBuffer:function(e){var t=void 0,r=void 0;try{t=new s.a(new s.b(e)),t.read()}catch(e){t=null}if(!t)return this.photoTime=null,this.frameTimes=null,void(this.videoRotation=0);var n=(r=t.tracks)&&(r=r.vide)&&(r=r[0])&&r.getAllSampleSeconds&&r.getAllSampleSeconds();n&&(this.frameTimes=n);var a=void 0;try{a=t.tracks.meta[0].timeToSeconds(t.tracks.meta[0].trak.edts.elst.editList[0].trackDuration)}catch(e){}a&&(this.photoTime=a);var o=new Uint8Array(e),u=(r=t.tracks)&&(r=r.soun)&&(r=r[0])&&(r=r.trak)&&r.offset;if(u){var l="free";o[u+4]=l.charCodeAt(0),o[u+5]=l.charCodeAt(1),o[u+6]=l.charCodeAt(2),o[u+7]=l.charCodeAt(3)}var d=(r=t.tracks)&&(r=r.vide)&&(r=r[0])&&(r=r.trak)&&(r=r.tkhd)&&(r=r.offset)&&r+48,c=0;if(d){for(var p=[],f=0;f<y.length;f++)p[f]=o[d+f];if(c=i(p))for(var v=0;v<y.length;v++)o[d+v]=y[v]}this.videoRotation=c;var m=t.metaData.keys&&t.metaData.keys.keyList.get("com.apple.photos.variation-identifier"),g=void 0;if(m>0)switch(t.metaData.values.items[m]){case 1:g=h.a.LOOP;break;case 2:g=h.a.BOUNCE;break;case 3:g=h.a.EXPOSURE}this.effectType=g}}),y=[0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,64,0,0,0],m=[[1,0,0,0,1,0,0,0,1],[0,1,0,-1,0,0,0,0,1],[-1,0,0,0,-1,0,0,0,1],[0,-1,0,1,0,0,0,0,1]];t.a=v},function(e,t,r){"use strict";var i=r(4),n=r(1),a=r(2),o=a.a.isSafari,s=i.a.create({correspondingPlaybackStyle:n.a.FULL,get minimumShortenedDuration(){return this.enterDuration+this.exitDuration+.01},get spontaneousFinishDuration(){return this.exitDuration},enterDuration:1/3,exitDuration:.5,videoBeginTime:.15,zoomScaleFactor:1.075,blurRadius:5,blurRadiusStep:.2,requiresInterpolation:!0,quantizeRadius:function(e){return this.blurRadiusStep?Math.round(e/this.blurRadiusStep)*this.blurRadiusStep:e},easeInOut:function(e){return e<0?0:e>1?1:.5-.5*Math.cos(e*Math.PI)},calculateAnimationDuration:function(e,t,r){var i=t?t+this.videoBeginTime+this.exitDuration:0;return Math.max(0,Math.min(e||1/0,i))},getEntranceExitParameter:function(e,t){return Math.min(Math.max(0,Math.min(1,1-this.easeInOut((e-(t-this.exitDuration))/this.exitDuration))),1-Math.max(0,Math.min(1,1-this.easeInOut(e/this.enterDuration))))||0},getTransform:function(e,t,r,i){var n=arguments.length>4&&void 0!==arguments[4]?arguments[4]:1,a=arguments.length>5&&void 0!==arguments[5]?arguments[5]:1,o=arguments.length>6&&void 0!==arguments[6]?arguments[6]:1,s=1+(this.zoomScaleFactor-1)*this.getEntranceExitParameter(e,t),u=-(s-1)/2*r,l=-(s-1)/2*i,d=Math.round(u*devicePixelRatio)/devicePixelRatio,c=Math.round(l*devicePixelRatio)/devicePixelRatio;return Math.abs(s-n)<1e-5?"translate3d("+d+"px, "+c+"px, 0) scale3d("+a+", "+o+", 1)":u||l||s?"translate3d("+u+"px, "+l+"px, 0) scale3d("+s+", "+s+", 1)":"translate3d(0, 0, 0)"},photo:i.a.PhotoIngredient.create({opacity:i.a.computedStyle(function(e){if(e<this.recipe.enterDuration)return(1-this.recipe.easeInOut(e/this.recipe.enterDuration)).toString();if(e<this.renderer.duration-this.recipe.exitDuration)return"0";var t=this.recipe.easeInOut((e-(this.renderer.duration-this.recipe.exitDuration))/this.recipe.exitDuration);return 1!==t?t:"1"}),display:i.a.computedStyle(function(e){return e>this.recipe.enterDuration&&e<this.renderer.duration-this.recipe.exitDuration?"none":""}),filter:i.a.computedStyle(function(e){if(!o)return"";var t=this.recipe,r=t.blurRadius*t.getEntranceExitParameter(e,this.renderer.duration);return r?"blur("+t.quantizeRadius(r)+"px)":""}),transform:i.a.computedStyle(function(e){return this.recipe.getTransform(e,this.renderer.duration,this.displayWidth,this.displayHeight)}),zIndex:i.a.computedStyle(function(){return 3})}),video:i.a.InterpolatedVideoIngredient.create({get backingScaleFactor(){return this.recipe.zoomScaleFactor},lookaheadTime:.01+7/15,videoTimeAtTime:function(e){var t=Math.max(0,Math.min(this.videoDuration,e-this.recipe.videoBeginTime));return Math.min(t,this.renderer.duration-this.recipe.exitDuration-this.recipe.videoBeginTime)},prepareVideoFramesFromTime:function(e){this.retainFramesForTime(e,e+this.lookaheadTime)},display:i.a.computedStyle(function(e){return 0===e||e===this.renderer.duration?"none":""}),transform:i.a.computedStyle(function(e){return this.recipe.getTransform(e,this.renderer.duration,this.displayWidth,this.displayHeight,this.backingScaleFactor,this.backingScaleX,this.backingScaleY)}),zIndex:i.a.computedStyle(function(){return 1})})});t.a=s},function(e,t,r){"use strict";var i=r(4),n=r(1),a=i.a.create({correspondingPlaybackStyle:n.a.HINT,minimumShortenedDuration:.9,get spontaneousFinishDuration(){return this.exitBlurDuration},exitBlurDuration:.75,bottomVideoRevealBeginTime:.1,zoomScaleFactor:1.075,blurRadius:7.5,blurRadiusStep:1,requiresInterpolation:!0,quantizeRadius:function(e){return Math.round(e/this.blurRadiusStep)*this.blurRadiusStep},tween:function(e){return e<0?0:e>1?1:.5-.5*Math.cos(e*Math.PI)},calculateAnimationDuration:function(e,t,r){var i=t?t-r+this.exitBlurDuration:0;return Math.max(0,Math.min(e||1/0,i))},photo:i.a.PhotoIngredient.create({hideDuration:.06,get returnDuration(){return this.recipe.exitBlurDuration},opacity:i.a.computedStyle(function(e){if(e<this.hideDuration)return(1-this.recipe.tween(e/this.hideDuration)).toString();if(e<this.renderer.duration-this.returnDuration)return"0";var t=this.recipe.tween((e-(this.renderer.duration-this.returnDuration))/this.returnDuration);return 1!==t?t.toString():""}),display:i.a.computedStyle(function(e){return e>this.hideDuration&&e<this.renderer.duration-this.returnDuration?"none":""}),webkitFilter:i.a.computedStyle(function(e){if(e<this.renderer.duration-this.returnDuration)return"";var t=this.recipe.blurRadius*(1-this.recipe.tween((e-(this.renderer.duration-this.returnDuration))/this.returnDuration));return t?"blur("+this.recipe.quantizeRadius(t)+"px)":""}),transform:i.a.computedStyle(function(e){if(e<this.renderer.duration-this.returnDuration)return"translateZ(0)";var t=1+(this.recipe.zoomScaleFactor-1)*(1-this.recipe.tween((e-(this.renderer.duration-this.returnDuration))/this.returnDuration));return"translate3d("+-(t-1)/2*this.displayWidth+"px, "+-(t-1)/2*this.displayHeight+"px, 0) scale3d("+t+", "+t+", 1)"}),zIndex:i.a.computedStyle(function(){return 3})}),video:i.a.InterpolatedVideoIngredient.create({get backingScaleFactor(){return this.recipe.zoomScaleFactor},scaleInDuration:.15,get blurOutDuration(){return this.recipe.exitBlurDuration},lookaheadTime:.01+7/15,videoTimeAtTime:function(e){return Math.min(this.videoDuration,e+this.renderer.photoTime)},prepareVideoFramesFromTime:function(e){this.retainFramesForTime(e,e+this.lookaheadTime)},display:i.a.computedStyle(function(e){return e&&e!==this.renderer.duration?"":"none"}),transform:i.a.computedStyle(function(e){var t=1+(this.recipe.zoomScaleFactor-1)*Math.max(0,Math.min(1,1-this.recipe.tween((e-(this.renderer.duration-this.blurOutDuration))/this.blurOutDuration)-(1-this.recipe.tween(e/this.scaleInDuration)))),r=-(t-1)/2*this.displayWidth,i=-(t-1)/2*this.displayHeight,n=Math.round(r*devicePixelRatio)/devicePixelRatio,a=Math.round(i*devicePixelRatio)/devicePixelRatio;return Math.abs(t-this.backingScaleFactor)<1e-5?"translate3d("+n+"px, "+a+"px, 0) scale3d("+this.backingScaleX+", "+this.backingScaleY+", 1)":"translate3d("+r+"px, "+i+"px, 0) scale3d("+t+", "+t+", 1)"}),webkitFilter:i.a.computedStyle(function(e){var t=this.recipe.blurRadius*this.recipe.tween((e-(this.renderer.duration-this.blurOutDuration))/this.blurOutDuration);return t?"blur("+this.recipe.quantizeRadius(t)+"px)":""}),zIndex:i.a.computedStyle(function(){return 1})})});t.a=a},function(e,t,r){"use strict";var i=r(4),n=r(2),a=(n.a.isSafari,i.a.create({requiresInterpolation:!0,photo:i.a.PhotoIngredient.create({display:i.a.computedStyle(function(e){return this.isPlaying||e>0?"none":""})}),video:i.a.InterpolatedVideoIngredient.create({lookaheadTime:.01+7/15,videoTimeAtTime:function(e){return e%this.renderer.duration},prepareVideoFramesFromTime:function(e){this.retainFramesForTime(e,e+this.lookaheadTime)},display:i.a.computedStyle(function(e){return""})}),beginFinishingPlaybackEarly:function(e){e.autoplay||(e.isPlaying?e.pause():e.wantsToPlay=!1)},continuePlayback:function(e){var t=e.currentTime,r=e.duration;t>=r&&(e.currentTime=t%r),e._rafID=requestAnimationFrame(e._nextFrame.bind(e))}}));t.a=a},function(e,t,r){"use strict";var i=r(4),n=r(36),a=r(1);n.a.register();var o=i.a.create({correspondingPlaybackStyle:a.a.LOOP,photo:i.a.PhotoIngredient.create({display:i.a.computedStyle(function(e){return this.isPlaying||e>0?"none":""})}),video:i.a.VideoIngredient.create({display:i.a.computedStyle(function(e){return""})}),beginFinishingPlaybackEarly:function(e){e.autoplay||(e.isPlaying?e.pause():e.wantsToPlay=!1)},continuePlayback:function(e){var t=e.currentTime,r=e.duration;t>=r&&(e.currentTime=t%r),e._rafID=requestAnimationFrame(e._nextFrame.bind(e))},requestMoreCompatibleRecipe:function(e){return i.a.registerRecipeWithPlaybackStyle(n.a,this.correspondingPlaybackStyle),n.a}});t.a=o},function(e,t,r){"use strict";var i=r(0),n=r(41),a=r(1),o=r(13),s=n.a.extend(o.a,{_loCanvas:null,_hiCanvas:null,backingScaleFactor:1,setUpForRender:function(){var e=this.element,t=(this.isPlaying,this.renderer),r=t.autoplay,n=t.parentView,o=t.playbackStyle,s=t.video;if(!this._loCanvas||!this._hiCanvas){e.innerHTML&&(e.innerHTML="");var u=this._loCanvas=i.a.canvasPool.get(),l=this._hiCanvas=i.a.canvasPool.get();u._context=u.getContext("2d"),l._context=l.getContext("2d"),u.style.cssText=l.style.cssText="position: absolute; left: 0; top: 0; width: 100%; height: 100%; transform: translateZ(0);",e.appendChild(u),e.appendChild(l),this._swapCanvases()}e.className="lpk-render-layer lpk-video",e.style.position="absolute",e.style.transformOrigin="0 0",e.style.zIndex=1,this._super(),o===a.a.LOOP&&(this.shouldLoop=!0),this.shouldLoop&&requestAnimationFrame(function(){s.currentTime=-1,r&&n.play()}),window.test=this},updateSize:function(e,t){if(!arguments.length)return this._super();this._super(e,t);var r=Math.ceil(e*this.backingScaleFactor),i=Math.ceil(t*this.backingScaleFactor);this.backingScaleX=r/e,this.backingScaleY=i/t,this.element.style.width=r+"px",this.element.style.height=i+"px",this._loCanvas&&this._hiCanvas&&(this._loCanvas.width=this._hiCanvas.width=r*devicePixelRatio,this._loCanvas.height=this._hiCanvas.height=i*devicePixelRatio,this._loCanvas._drawnFrameNumber=this._hiCanvas._drawnFrameNumber=-1,this.renderAtTime())},renderAtTime:function(e){if(!arguments.length)return this._super();this._super(e);var t=this.backingScaleX,r=this.backingScaleY;1===t&&1===r||(this.element.style.transform+=" scale3d("+1/t+", "+1/r+", 1)")},renderFramePair:function(e,t,r){(e&&this._hiCanvas._drawnFrameNumber===e.number||t&&this._loCanvas._drawnFrameNumber===t.number)&&this._swapCanvases(),this._putFrameInCanvasIfNeeded(e,this._loCanvas),this._putFrameInCanvasIfNeeded(t,this._hiCanvas),t&&(this._hiCanvas.style.opacity=r)},_swapCanvases:function(){var e=this._hiCanvas;this._hiCanvas=this._loCanvas,this._loCanvas=e,this._loCanvas.style.opacity="",this._loCanvas.style.zIndex=1,this._hiCanvas.style.zIndex=2},_putFrameInCanvasIfNeeded:function(e,t){t._drawnFrameNumber!==(t._drawnFrameNumber=e?e.number:-1)&&(t.setAttribute("data-frame-number",t._drawnFrameNumber.toString()),e?t._context.drawImage(e.image,0,0,t.width,t.height):t._context.clearRect(0,0,t.width,t.height))},dispose:function(){this._super(),this._loCanvas&&i.a.canvasPool.ret(this._loCanvas),this._hiCanvas&&i.a.canvasPool.ret(this._hiCanvas)},tearDownFromRender:function(){var e=this.renderer,t=e.parentView;this.shouldLoop=!1,t&&t.stop(),this._clearAllRetainedFrames(),this._super()}});t.a=s},function(e,t,r){"use strict";var i=r(42),n=r(13),a=r(49),o=i.a.extend(n.a,{tagName:"canvas",get _canvas(){return this.element},get _context(){return this.__context||(this.__context=this._canvas.getContext("2d"))},init:function(){this._super.apply(this,arguments),this.element.className="lpk-render-layer lpk-photo",this.element.style.position="absolute",this.element.style.width=this.element.style.height="100%",this.element.style.transformOrigin="0 0",this.element.style.zIndex=2},tearDownFromRender:function(){this._super(),this._canvas.width=this._canvas.height=0},updateSize:function(e,t){if(!arguments.length)return this._super();this._super(e,t);var i=Math.ceil(e*devicePixelRatio),n=Math.ceil(t*devicePixelRatio),o=this.photo,s=this._canvas;this._lastPhoto===(this._lastPhoto=o)&&s.width===i&&s.height===n||(s.width=i,s.height=n,o&&r.i(a.a)(this._context,o,0,0,i,n))}});t.a=o},function(e,t,r){"use strict";var i=r(0),n=r(2),a=r(13),o=r(43),s=o.a.extend(a.a,{_isPlayingChanged:i.a.observer("isPlaying",function(e){this._video&&(e?(this.duration=1/0,this.play()):this.pause())}),_isVisible:!1,applyStyles:function(){var e=this.element,t=this.video,r=this.videoRotation,i=t.videoHeight,n=t.videoWidth,a=1;[90,270].indexOf(r)>=0&&(a=n/i);var o="\n                height: 100%;\n                position: absolute;\n                width: 100%;\n                -moz-transform: scale("+a+") rotate("+r+"deg);\n                -webkit-transform: scale("+a+") rotate("+r+"deg);\n                -o-transform: scale("+a+") rotate("+r+"deg);\n                -ms-transform: scale("+a+") rotate("+r+"deg);\n                transform: scale("+a+") rotate("+r+"deg);\n                z-index: 1;\n            ";e.setAttribute("style",o),e.className="lpk-render-layer lpk-video",t.style.height="100%",t.style.width="100%"},cleanupElement:function(){var e=this.element,t=this.renderer,r=this._video,i=t.parentView;e.innerHtml&&(e.innerHtml=""),r&&(r.loop=!1,r.muted=!1,r.removeEventListener("pause",this.playIfPlaying)),i&&i.stop(),delete this._video},pause:function(){var e=this._isVisible,t=this._video;e&&t.pause()},play:function(){if(this._isVisible){var e=this._video,t=e.play();t?t.catch(this._handlePlayFailure):n.a.isIE||n.a.isEdge||(e.pause(),setTimeout(this._handlePlayFailure))}},_handlePlayFailure:i.a.boundFunction(function(){this.renderer.requestMoreCompatibleRecipe()}),playIfPlaying:i.a.boundFunction(function(){var e=this.isPlaying,t=this._video;if(e&&t.paused){var r=t.play();r&&r.catch(function(){})}}),setUpForRender:function(){var e=this.element,t=(this.isPlaying,this.renderer),r=t.autoplay,i=t.parentView,n=t.video;this.cleanupElement(),e.appendChild(n),this.applyStyles(),n.loop=!0,n.muted=!0,this._video=n,this._isVisible=!0,this._super(),r&&(n.addEventListener("pause",this.playIfPlaying),i.play())},tearDownFromRender:function(){this.cleanupElement(),this._isVisible=!1,this._super()}});t.a=s},function(e,t,r){"use strict";function i(e){e.retain()}function n(e){e.release()}var a=r(0),o=r(7),s=r(17),u=o.a.extend({videoDecoder:a.a.proxyProperty("renderer.videoDecoder"),videoDuration:a.a.proxyProperty("videoDecoder.duration"),canRender:a.a.proxyProperty({readOnly:!0,proxyPath:"videoDecoder.canProvideFrames"}),init:function(){this._super.apply(this,arguments);var e=this.layerName,t=this.recipe;this._framePrepIDKey=t.name+"_"+e+"_framePrepID"},videoTimeAtTime:function(e){return e},_videoTimeAtTime:function(e){return isNaN(e)?e:this.videoTimeAtTime(e)},prepareToRenderAtTime:function(e){var t=this._currentPrepID=++l;if(!this.canRender)return!1;this.prepareVideoFramesFromTime(e);for(var r,i=this._retainedFrames,n=0,a=0;r=i[a];a++)2!==r.readyState&&(r[this._framePrepIDKey]=t,r.onReadyOrFail(this._frameDidPrepare),n++);return this._preppingFrameCount=n,!n},reduceMemoryFootprint:function(){this._super(),this._clearAllRetainedFrames()},_clearAllRetainedFrames:function(){this._clearExtraRetainedFrames(),this._clearRetainedInstantaneousFrames()},_clearExtraRetainedFrames:function(){var e=this._retainedFrames;e&&(e.forEach(n),e.length=0)},_clearRetainedInstantaneousFrames:function(){this._retainedLoFrame&&this._retainedLoFrame.release(),this._retainedHiFrame&&this._retainedHiFrame.release(),this._retainedLoFrame=this._retainedHiFrame=null},_frameDidPrepare:a.a.boundFunction(function(e){e[this._framePrepIDKey]===this._currentPrepID&&(e[this._framePrepIDKey]=void 0,--this._preppingFrameCount||this.renderer.prepareToRenderAtTime(this.renderer.currentTime))}),prepareVideoFramesFromTime:function(e){this.retainFramesForTime(e)},canRenderAtTime:function(e){if("none"===this.display(e))return!0;if(!this.canRender)return!1;for(var t,r=!0,i=this.requiredFramesForTime(e),n=0;t=i[n];n++)r=r&&2===t.readyState,t.retain().release();return r},renderAtTime:function(e){if(!arguments.length)return this._super();if("none"===this.display(e))return this._clearRetainedInstantaneousFrames(),this._super(e);var t=this._videoTimeAtTime(e),r=this.requiredFramesForVideoTime(t),i=r[0]||null,n=r[1]||null;if(i&&i.retain(),n&&n.retain(),this._clearRetainedInstantaneousFrames(),this._retainedLoFrame=i,this._retainedHiFrame=n,i&&(i=i.backingFrame),n&&(n=n.backingFrame),i&&n&&i.number>n.number){var a=i;n=i,i=a}i===n&&(n=null);var o=!i||n?this.videoDecoder.fractionalIndexForTime(t):i.frameNumber,s=o-(0|o);this.renderFramePair(i,n,s),this._super(e)},renderFramePair:function(){},requiredFramesForVideoTime:function(e,t,r){isNaN(t)&&(t=e);var i=this.videoDecoder,n=this.videoDuration,a=i.frameCount,o=d;if(o.length=0,t<0||e>n||isNaN(e)||isNaN(t))return o;var u=Math.max(0,Math.floor(i.fractionalIndexForTime(e))),l=Math.min(i.frameCount,Math.ceil(i.fractionalIndexForTime(t))),c=l<u,h=c?a-1:l;if(u===h-1){var p=i.frameTimes;p[h]-p[u]<1/30+.001&&(h=u)}for(var f=u;f<=h;f++)s.a&&0===f||(o.push(i.getFrame(f,r)),f+1===a&&c&&(f=-1,h=l));return o},requiredFramesForTime:function(e,t,r){return this.requiredFramesForVideoTime(this._videoTimeAtTime(e),this._videoTimeAtTime(t),r)},retainFramesForVideoTime:function(e,t,r){void 0===t&&(t=e);var a=this.lookaheadTime,o=this.shouldLoop,s=this.requiredFramesForVideoTime(e,t,r),u=this._retainedFrames||(this._retainedFrames=[]);s.forEach(i);for(var l=u.length-1;l>=0;l--){var d=u[l],c=d.time;(!o||c>a/2)&&(n(d),u.splice(l,1))}u.push.apply(u,s)},retainFramesForTime:function(e,t,r){return this.retainFramesForVideoTime(this._videoTimeAtTime(e),this._videoTimeAtTime(t),r)},dispose:function(){this.retainFramesForVideoTime(NaN),this._super()}}),l=1,d=[];t.a=u},function(e,t,r){"use strict";var i=r(7),n=r(0),a=i.a.extend({isPlaying:n.a.proxyProperty({readOnly:!0,proxyPath:"renderer.parentView.isPlaying"}),photo:n.a.proxyProperty({readOnly:!0,proxyPath:"renderer.photo"}),canRender:n.a.proxyProperty("photo"),canRenderAtTime:function(e){var t=this.photo;return!("none"!==this.display(e)&&(!t||t instanceof Image&&!t.complete))}});t.a=a},function(e,t,r){"use strict";var i=r(7),n=r(0),a=i.a.extend({canRender:n.a.proxyProperty({readOnly:!0,proxyPath:"video"}),isPlaying:n.a.proxyProperty({readOnly:!0,proxyPath:"renderer.parentView.isPlaying"}),video:n.a.proxyProperty({readOnly:!0,proxyPath:"renderer.video"}),videoRotation:n.a.proxyProperty({readOnly:!0,proxyPath:"renderer.provider.videoRotation"})});t.a=a},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function n(e){var t=r.i(o.a)(e),i=l.get(t);if(i)return i;var n=e.map(function(e){if("i"===e[0]&&h(e[1]))return"I"+e.substring(1)});return e=e.concat(n.filter(function(e){return!!e})),i=new RegExp(e.join("|"),"g"),l.set(t,i),i}function a(e,t){var r=e.charCodeAt(0),i=t.charCodeAt(0),n=new Map;return function(e){var t=n.get(e);if(void 0!==t)return t;var a=e.charCodeAt(0);return t=a>=r&&a<=i,n.set(e,t),t}}var o=r(20),s=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),u=["iOS","iPhone","iPad","iPod","WebKit"],l=new Map,d=a("0","9"),c=a("a","z"),h=a("A","Z"),p=function(e){return c(e)||h(e)},f=function(e){return p(e)||d(e)},v=new Map,y=function(){function e(){i(this,e)}return s(e,null,[{key:"splitIntoArray",value:function(e,t,r){for(var i=e.indexOf(t),n=0,a=t.length;-1!==i;)r.push(e.substring(n,i)),n=i+a,i=e.indexOf(t,n);r.push(e.substring(n))}},{key:"hyphenate",value:function(t,i,n){if(!t)return"";i||null===i||!1===i||(i=u);var a=i||n?r.i(o.a)(i)+"#"+t+"#"+!!n:t,s=v.get(a);if(s)return s;if(Array.isArray(i)){var l=e.hyphenateCarefully(t,i,n);return v.set(a,l),l}var y=t.length,m="",g=void 0,b=void 0,_=void 0,P=void 0,k=void 0;for(g=0;g<y;g++)b=t[g-1],_=t[g],P=_.toLowerCase(),k=t[g+1],b&&f(_)&&f(b)&&(!h(b)&&h(_)||d(b)&&p(_)||k&&h(b)&&h(_)&&c(k))?m+="-"+P:m+=P;return v.set(a,m),m}},{key:"hyphenateCarefully",value:function(t,r,i){var a=n(r),o=t.match(a);if(!o||!o.length)return e.hyphenate(t,null,i);for(var s=t.split(a),u=e.hyphenate(s[0]),l=0,d=o.length;l<d;l++){var c=o[l];i||(c=c.toLowerCase());var h=s[l+1];h&&(h=e.hyphenate(h,null,i)),u+=(u&&"-")+c,h&&(u+=(p(h[0])?"-":"")+h)}return u}}]),e}();t.a=y},function(e,t,r){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function n(e,t){e||a(t)}function a(e){throw e}var o=r(5);r.d(t,"b",function(){return u}),r.d(t,"a",function(){return l});var s=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),u=(function(){function e(t,r){i(this,e),this.w=t,this.h=r}s(e,[{key:"toString",value:function(){return"("+this.w+", "+this.h+")"}},{key:"getHalfSize",value:function(){return new e(this.w>>>1,this.h>>>1)}},{key:"length",value:function(){return this.w*this.h}}])}(),function(){function e(t,r,n){i(this,e),this.bytes=new Uint8Array(t),this.start=r||0,this.pos=this.start,this.end=r+n||this.bytes.length}return s(e,[{key:"readU8Array",value:function(e){if(this.pos>this.end-e)return null;var t=this.bytes.subarray(this.pos,this.pos+e);return this.pos+=e,t}},{key:"readU32Array",value:function(e,t,r){if(t=t||1,this.pos>this.end-e*t*4)return null;if(1===t){for(var i=new Uint32Array(e),n=0;n<e;n++)i[n]=this.readU32();return i}for(var a=new Array(e),o=0;o<e;o++){var s=null;if(r){s={};for(var u=0;u<t;u++)s[r[u]]=this.readU32()}else{s=new Uint32Array(t);for(var l=0;l<t;l++)s[l]=this.readU32()}a[o]=s}return a}},{key:"read8",value:function(){return this.readU8()<<24>>24}},{key:"readU8",value:function(){return this.pos>=this.end?null:this.bytes[this.pos++]}},{key:"read16",value:function(){return this.readU16()<<16>>16}},{key:"readU16",value:function(){if(this.pos>=this.end-1)return null;var e=this.bytes[this.pos+0]<<8|this.bytes[this.pos+1];return this.pos+=2,e}},{key:"read24",value:function(){return this.readU24()<<8>>8}},{key:"readU24",value:function(){var e=this.pos,t=this.bytes;if(e>this.end-3)return null;var r=t[e+0]<<16|t[e+1]<<8|t[e+2];return this.pos+=3,r}},{key:"peek32",value:function(e){var t=this.pos,r=this.bytes;if(t>this.end-4)return null;var i=r[t+0]<<24|r[t+1]<<16|r[t+2]<<8|r[t+3];return e&&(this.pos+=4),i}},{key:"read32",value:function(){return this.peek32(!0)}},{key:"readU32",value:function(){return this.peek32(!0)>>>0}},{key:"read4CC",value:function(){var e=this.pos;if(e>this.end-4)return null;for(var t="",r=0;r<4;r++)t+=String.fromCharCode(this.bytes[e+r]);return this.pos+=4,t}},{key:"readFP16",value:function(){return this.read32()/65536}},{key:"readFP8",value:function(){return this.read16()/256}},{key:"readISO639",value:function(){for(var e=this.readU16(),t="",r=0;r<3;r++){var i=e>>>5*(2-r)&31;t+=String.fromCharCode(i+96)}return t}},{key:"readUTF8",value:function(e){for(var t="",r=0;r<e;r++)t+=String.fromCharCode(this.readU8());return t}},{key:"readPString",value:function(e){var t=this.readU8();n(t<=e);var r=this.readUTF8(t);return this.reserved(e-t-1,0),r}},{key:"skip",value:function(e){this.seek(this.pos+e)}},{key:"reserved",value:function(e,t){for(var r=0;r<e;r++)n(this.readU8()===t)}},{key:"seek",value:function(e){(e<0||e>this.end)&&a("Index out of bounds (bounds: [0, "+this.end+"], index: "+e+")."),this.pos=e}},{key:"subStream",value:function(t,r){return new e(this.bytes.buffer,t,r)}},{key:"uint",value:function(e){for(var t=this.position,r=t+e,i=0,n=t;n<r;n++)i<<=8,i|=255&this.readU8();return i}},{key:"length",get:function(){return this.end-this.start}},{key:"position",get:function(){return this.pos}},{key:"remaining",get:function(){return this.end-this.pos}}]),e}()),l=function(){function e(t){i(this,e),this.stream=t,this.tracks={},this.metaData={}}return s(e,[{key:"getPath",value:function(e){for(var t=e.split("."),r=this,i=0,n=t.length;i<n;i++)r=r?r[t[i]]:void 0;return r}},{key:"readBoxes",value:function(e,t){for(;e.peek32();){var r=this.readBox(e);if(r.type in t){var i=t[r.type];Array.isArray(i)||(t[r.type]=[i]),t[r.type].push(r)}else t[r.type]=r}}},{key:"readBox",value:function(e){function t(){o.version=e.readU8(),o.flags=e.readU24()}function r(){return o.size-(e.position-o.offset)}function i(){e.skip(r())}function a(){var t=e.subStream(e.position,r());s.readBoxes(t,o),e.skip(t.length)}var o={offset:e.position},s=this;!function(){o.size=e.readU32(),o.type=e.read4CC()}();var u=void 0,l=void 0,c=void 0;switch(o.type){case"ftyp":var h=o;h.name="File Type Box",h.majorBrand=e.read4CC(),h.minorVersion=e.readU32(),h.compatibleBrands=new Array((h.size-16)/4);for(var p=0;p<h.compatibleBrands.length;p++)h.compatibleBrands[p]=e.read4CC();break;case"moov":o.name="Movie Box",a();break;case"mvhd":var f=o;f.name="Movie Header Box",t(),n(0==f.version),f.creationTime=e.readU32(),f.modificationTime=e.readU32(),f.timeScale=e.readU32(),f.duration=e.readU32(),f.rate=e.readFP16(),f.volume=e.readFP8(),e.skip(10),f.matrix=e.readU32Array(9),e.skip(24),f.nextTrackId=e.readU32();break;case"trak":var v=o;v.name="Track Box",a();var y=new d(this,v),m=v.mdia&&v.mdia.hdlr&&v.mdia.hdlr.handlerType||"unknownHandlerType",g=this.tracks;(g[m]||(g[m]=[])).push(y),g[v.tkhd.trackId]=y;break;case"tkhd":var b=o;b.name="Track Header Box",t(),n(0==b.version),b.creationTime=e.readU32(),b.modificationTime=e.readU32(),b.trackId=e.readU32(),e.skip(4),b.duration=e.readU32(),e.skip(8),b.layer=e.readU16(),b.alternateGroup=e.readU16(),b.volume=e.readFP8(),e.skip(2),b.matrix=e.readU32Array(9),b.width=e.readFP16(),b.height=e.readFP16();break;case"edts":o.name="Edit Atom",a();break;case"elst":var _=o;_.name="Edit List Atom",t(),_.editList=[],c=e.readU32();for(var P=0;P<c;P++)_.editList.push({trackDuration:e.read32(),mediaTime:e.read32(),mediaRate:e.readU32()});n(!r()),i();break;case"mdia":o.name="Media Box",a();break;case"mdhd":var k=o;k.name="Media Header Box",t(),n(0==k.version),k.creationTime=e.readU32(),k.modificationTime=e.readU32(),k.timeScale=e.readU32(),k.duration=e.readU32(),k.language=e.readISO639(),e.skip(2);break;case"hdlr":var T=o;T.name="Handler Reference Box",t(),e.skip(4),T.handlerType=e.read4CC(),e.skip(12),l=T.size-32,l>0&&(T.name=e.readUTF8(l));break;case"minf":o.name="Media Information Box",a();break;case"stbl":o.name="Sample Table Box",a();break;case"stsd":var x=o;x.name="Sample Description Box",t(),x.sd=[],e.readU32(),a();break;case"avc1":var S=o;e.reserved(6,0),S.dataReferenceIndex=e.readU16(),n(0==e.readU16()),n(0==e.readU16()),e.readU32(),e.readU32(),e.readU32(),S.width=e.readU16(),S.height=e.readU16(),S.horizontalResolution=e.readFP16(),S.verticalResolution=e.readFP16(),n(0==e.readU32()),S.frameCount=e.readU16(),S.compressorName=e.readPString(32),S.depth=e.readU16(),n(65535==e.readU16()),a();break;case"mp4a":var w=o;if(e.reserved(6,0),w.dataReferenceIndex=e.readU16(),w.version=e.readU16(),0!==w.version){i();break}e.skip(2),e.skip(4),w.channelCount=e.readU16(),w.sampleSize=e.readU16(),w.compressionId=e.readU16(),w.packetSize=e.readU16(),w.sampleRate=e.readU32()>>>16,a();break;case"esds":o.name="Elementary Stream Descriptor",t(),i();break;case"avcC":var O=o;O.name="AVC Configuration Box",O.configurationVersion=e.readU8(),O.avcProfileIndicaation=e.readU8(),O.profileCompatibility=e.readU8(),O.avcLevelIndication=e.readU8(),O.lengthSizeMinusOne=3&e.readU8(),n(3==O.lengthSizeMinusOne,"TODO"),u=31&e.readU8(),O.sps=[];for(var C=0;C<u;C++)O.sps.push(e.readU8Array(e.readU16()));u=31&e.readU8(),O.pps=[];for(var F=0;F<u;F++)O.pps.push(e.readU8Array(e.readU16()));i();break;case"btrt":var R=o;R.name="Bit Rate Box",R.bufferSizeDb=e.readU32(),R.maxBitrate=e.readU32(),R.avgBitrate=e.readU32();break;case"stts":var L=o;L.name="Decoding Time to Sample Box",t(),L.table=e.readU32Array(e.readU32(),2,["count","delta"]);break;case"stss":var E=o;E.name="Sync Sample Box",t(),E.samples=e.readU32Array(e.readU32());break;case"stsc":var A=o;A.name="Sample to Chunk Box",t(),A.table=e.readU32Array(e.readU32(),3,["firstChunk","samplesPerChunk","sampleDescriptionId"]);break;case"stsz":var I=o;I.name="Sample Size Box",t(),I.sampleSize=e.readU32(),u=e.readU32(),0==I.sampleSize&&(I.table=e.readU32Array(u));break;case"stco":var D=o;D.name="Chunk Offset Box",t(),D.table=e.readU32Array(e.readU32());break;case"smhd":var M=o;M.name="Sound Media Header Box",t(),M.balance=e.readFP8(),e.reserved(2,0);break;case"mdat":var j=o;j.name="Media Data Box",n(j.size>=8,"Cannot parse large media data yet."),j.data=e.readU8Array(r());break;case"mebx":o.name="Mebx",a();break;case"meta":o.name="Metadata",a();break;case"keys":var U=o;U.name="Metadata Item Keys",t();var V=U.keyCount=e.read32(),N=U.offset-U.size;U.keyList=new Map;for(var B=1;B<=V;B++){var z=e.read32()-8;z<1||z>N||(e.skip(4),U.keyList.set(e.readUTF8(z),B))}this.metaData.keys=U;break;case"ilst":var H=o;H.name="Metadata Item List",H.items=[];for(var K=H.offset+H.size;e.position<K;){var W=(e.position,e.readU32(),e.readU32()),q=e.readU32()-16,G=(e.read4CC(),e.readU8()),X=e.readU24(),Z=(e.readU16(),e.readU16(),void 0);Z=0===G&&1!==X?e.uint(q):e.readUTF8(q),H.items[W]=Z}this.metaData.values=H;break;default:i()}return o}},{key:"read",value:function(){var e=(new Date).getTime();this.file={},this.readBoxes(this.stream,this.file),o.a.info("Parsed stream in "+((new Date).getTime()-e)+" ms")}},{key:"traceSamples",value:function(){var e=this.tracks[1],t=this.tracks[2];o.a.info("Video Samples: "+e.getSampleCount()),o.a.info("Audio Samples: "+t.getSampleCount());for(var r=0,i=0,n=0;n<100;n++){var a=e.sampleToOffset(r),s=t.sampleToOffset(i),u=e.sampleToSize(r,1),l=t.sampleToSize(i,1);a<s?(o.a.info("V Sample "+r+" Offset : "+a+", Size : "+u),r++):(o.a.info("A Sample "+i+" Offset : "+s+", Size : "+l),i++)}}}]),e}(),d=function(){function e(t,r){i(this,e),this.file=t,this.trak=r}return s(e,[{key:"getSampleSizeTable",value:function(){return this.trak.mdia.minf.stbl.stsz.table}},{key:"getSampleCount",value:function(){return this.getSampleSizeTable().length}},{key:"sampleToSize",value:function(e,t){for(var r=this.getSampleSizeTable(),i=0,n=e;n<e+t;n++)i+=r[n];return i}},{key:"sampleToChunk",value:function(e){var t=this.trak.mdia.minf.stbl.stsc.table;if(1===t.length){var r=t[0];return n(1===r.firstChunk),{index:e/r.samplesPerChunk,offset:e%r.samplesPerChunk}}for(var i=0,a=0;a<t.length;a++){var o=t[a];if(a>0){var s=t[a-1],u=o.firstChunk-s.firstChunk,l=s.samplesPerChunk*u;if(!(e>=l))return{index:i+Math.floor(e/s.samplesPerChunk),offset:e%s.samplesPerChunk};if(e-=l,a===t.length-1)return{index:i+u+Math.floor(e/o.samplesPerChunk),offset:e%o.samplesPerChunk};i+=u}}n(!1)}},{key:"chunkToOffset",value:function(e){return this.trak.mdia.minf.stbl.stco.table[e]}},{key:"sampleToOffset",value:function(e){var t=this.sampleToChunk(e);return this.chunkToOffset(t.index)+this.sampleToSize(e-t.offset,t.offset)}},{key:"timeToSample",value:function(e){for(var t=this.trak.mdia.minf.stbl.stts.table,r=0,i=0;i<t.length;i++){var n=t[i].count*t[i].delta;if(!(e>=n))return r+Math.floor(e/t[i].delta);e-=n,r+=t[i].count}}},{key:"sampleToTime",value:function(e){for(var t=this.trak.mdia.minf.stbl.stts.table,r=0,i=0,n=0;n<t.length&&r<e;n++){var a=t[n],o=Math.min(e-r,a.count);r+=o,i+=o*a.delta}return i}},{key:"getTotalTime",value:function(){for(var e=this.trak.mdia.minf.stbl.stts.table,t=0,r=0;r<e.length;r++)t+=e[r].count*e[r].delta;return n(this.trak.mdia.mdhd.duration===t),this.trak.mdia.mdhd.duration}},{key:"getTotalTimeInSeconds",value:function(){return this.timeToSeconds(this.getTotalTime())}},{key:"getTimeScale",value:function(){return this.trak.mdia.mdhd.timeScale}},{key:"timeToSeconds",value:function(e){return e/this.getTimeScale()}},{key:"secondsToTime",value:function(e){return e*this.getTimeScale()}},{key:"secondsToSample",value:function(e){return this.timeToSample(this.secondsToTime(e))}},{key:"sampleToSeconds",value:function(e){return this.timeToSeconds(this.sampleToTime(e))}},{key:"getAllSampleTimes",value:function(){for(var e=[],t=this.getSampleCount(),r=0;r<t;r++)e.push(this.sampleToTime(r));return e}},{key:"getAllSampleSeconds",value:function(){for(var e=[],t=this.getSampleCount(),r=0;r<t;r++)e.push(this.sampleToSeconds(r));return e}},{key:"getSampleNALUnits",value:function(e){for(var t=this.file.stream.bytes,r=this.sampleToOffset(e),i=r+this.sampleToSize(e,1),n=[];i-r>0;){var a=new u(t.buffer,r).readU32();n.push(t.subarray(r+4,r+a+4)),r=r+a+4}return n}}]),e}()},function(e,t,r){"use strict";var i={staticMembers:{_pool:null,_cache:null,init:function(){this._pool=[],this._cache={},this._super()},getPoolingCacheKey:function(){throw"Must implement `getPoolingCacheKey` to use PoolCaching."},getCached:function(){var e=this.getPoolingCacheKey.apply(this,arguments),t=this._cache[e];return t||(t=this._cache[e]=this._pool.pop()||this.create(),t._poolingCacheKey=e,t.initFromPool.apply(t,arguments)),t},peekCached:function(){var e=this.getPoolingCacheKey.apply(this,arguments);return this._cache[e]||null},_disposeInstance:function(e){delete this._cache[e._poolingCacheKey],e._poolingCacheKey=void 0,e._poolingLifecycleCount=1+(0|e._poolingLifecycleCount),this._pool.push(e)}},dispose:function(){},_poolingCacheKey:null,initFromPool:function(){},_retainCount:0,retain:function(){return this._retainCount++,this},release:function(){return this._retainCount--,this._retainCount||this.dispose(),this}};t.a=i},function(e,t,r){"use strict";function i(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function n(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var o=r(19);r.d(t,"a",function(){return d}),r.d(t,"b",function(){return h}),r.d(t,"c",function(){return f}),r.d(t,"d",function(){return y});var s=function(){function e(e,t){for(var r=0;r<t.length;r++){var i=t[r];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,r,i){return r&&e(t.prototype,r),i&&e(t,i),t}}(),u=function(){function e(){a(this,e),this._a=[]}return s(e,[{key:"get",value:function(){return this._a.pop()||this.create()}},{key:"ret",value:function(e){this.prepare(e),this._a.push(e)}}]),e}(),l=function(e){function t(){return a(this,t),i(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return n(t,e),s(t,[{key:"create",value:function(){return[]}},{key:"prepare",value:function(e){e.length=0}}]),t}(u),d=new l,c=function(e){function t(){return a(this,t),i(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return n(t,e),s(t,[{key:"create",value:function(){return new o.a}},{key:"prepare",value:function(e){e.clear()}}]),t}(u),h=new c,p=function(e){function t(){return a(this,t),i(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return n(t,e),s(t,[{key:"create",value:function(){return Object.create(null)}},{key:"prepare",value:function(e){var t=e;for(var r in t)delete t[r]}}]),t}(u),f=new p,v=function(e){function t(){return a(this,t),i(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return n(t,e),s(t,[{key:"create",value:function(){return document.createElement("canvas")}},{key:"prepare",value:function(e){var t=e.parentNode;t&&t.removeChild(e),e.width=e.height=0}}]),t}(u),y=new v},function(e,t,r){"use strict";function i(e,t){return e.apply(void 0,t)}var n={_readiness:"pending",_resolutionArgs:null,_fulfillmentCallbacks:null,_rejectionCallbacks:null,onReadyOrFail:function(e,t){this._resolveIfAlreadyResolved(e,t,!1)||this._pendCallbacks(e,t)},onReadyOrFailSync:function(e,t){this._resolveIfAlreadyResolved(e,t,!0)||this._pendCallbacks(e,t)},_resolveIfAlreadyResolved:function(e,t,r){var n=this._resolutionArgs;return"success"===this._readiness&&e?(r?i(e,n):setTimeout(i,0,e,n),!0):!("failure"!==this._readiness||!t)&&(r?i(t,n):setTimeout(i,0,t,n),!0)},_pendCallbacks:function(e,t){e&&(this._fulfillmentCallbacks||(this._fulfillmentCallbacks=[])).push(e),t&&(this._rejectionCallbacks||(this._rejectionCallbacks=[])).push(t)},clearReadinessCallbacks:function(){this._fulfillmentCallbacks&&(this._fulfillmentCallbacks.length=0),this._rejectionCallbacks&&(this._rejectionCallbacks.length=0)},resetReadiness:function(){this._readiness="pending",this._resolutionArgs&&(this._resolutionArgs.length=0),this.clearReadinessCallbacks()},resolveReadiness:function(){"pending"===this._readiness&&(this._readiness="success",(this._resolutionArgs=this._resolutionArgs||[]).push.apply(this._resolutionArgs,arguments),this._invokeCallbacks(this._fulfillmentCallbacks),this.clearReadinessCallbacks())},rejectReadiness:function(){"pending"===this._readiness&&(this._readiness="failure",(this._resolutionArgs=this._resolutionArgs||[]).push.apply(this._resolutionArgs,arguments),this._invokeCallbacks(this._rejectionCallbacks),this.clearReadinessCallbacks())},_invokeCallbacks:function(e){if(e){for(var t,r=0;t=e[r];r++)t.apply(void 0,this._resolutionArgs);e.length=0}}};t.a=n},function(e,t,r){"use strict";function i(){for(var e=1;e<arguments.length;e++)this[e-1]=arguments[e];return this}function n(e){return e&&"IMG"===e.tagName&&/^data:image\/svg|\.svg\?|\.svg$/.test(e.src)}var a=document.createElement("canvas"),o=a.getContext("2d"),s=document.createElement("canvas"),u=s.getContext("2d");a.width=s.width=a.height=s.height=0;var l=!1,d=!1,c=function(e,t){for(var r=arguments.length,c=Array(r>2?r-2:0),p=2;p<r;p++)c[p-2]=arguments[p];if(n(t))return e.drawImage.apply(e,i.apply(h,arguments)),!0;var f=l,v=d;l=d=!1;var y=t instanceof HTMLImageElement?t.naturalWidth:t.width,m=t instanceof HTMLImageElement?t.naturalHeight:t.height;if(!y||!m)throw"Source image provided to drawImageSmooth was not loaded, or otherwise had no dimensions.";var g=void 0,b=void 0,_=void 0,P=void 0,k=void 0,T=void 0,x=void 0,S=void 0;switch(arguments.length-1){case 3:g=0,b=0,_=y,P=m,k=arguments[2],T=arguments[3],x=y,S=m;break;case 5:g=0,b=0,_=y,P=m,k=arguments[2],T=arguments[3],x=arguments[4],S=arguments[5];break;case 9:g=arguments[2],b=arguments[3],_=arguments[4],P=arguments[5],k=arguments[6],T=arguments[7],x=arguments[8],S=arguments[9]}var w=x/_,O=S/P;if(w&&O){var C=Math.max(w,O),F=Math.pow(2,Math.ceil(Math.log(1.1*C)/Math.log(2)));if(F>=1)return e.drawImage.apply(e,i.apply(h,arguments)),!0;var R=void 0;if(f){R="_cachedSmoothDownsample_from"+g+","+b+","+_+","+P+"@"+F+"x";var L=t[R];if(L)return e.drawImage(L,0,0,L.width,L.height,k,T,x,S),!0}if(v)return e.drawImage.apply(e,i.apply(h,arguments)),!1;var E=1,A=_,I=P,D=Math.max(Math.pow(2,Math.ceil(Math.log(A)/Math.log(2))),a.width),M=Math.max(Math.pow(2,Math.ceil(Math.log(I)/Math.log(2))),a.height);for(a.width===D&&a.height===M||(a.width=s.width=D,a.height=s.height=M),o.drawImage(t,g,b,_,P,0,0,_,P);E>F;){u.drawImage(a,0,0,A,I,0,0,A=Math.ceil(A/2),I=Math.ceil(I/2)),o.clearRect(0,0,A,I);var j=a;a=s,s=j;var U=o;o=u,u=U,E/=2}if(f){var V=document.createElement("canvas");V.width=A,V.height=I,V.getContext("2d").drawImage(a,0,0),t[R]=V}return e.drawImage(a,0,0,A,I,k,T,x,S),o.clearRect(0,0,_,P),u.clearRect(0,0,_,P),!0}};c.usingCache=function(){return l=!0,this},c.avoidingWorkIf=function(e){return d=e,this};var h=[];t.a=c},function(e,t,r){"use strict";function i(){var e="_callbacksForEventHandler"+ ++n;return function(t){var r=this[e]||(this[e]=[]);if("function"==typeof t)return r.push(t);if(r)for(var i=0,n=r.length;i<n;++i)r[i](t)}}t.a=i;var n=1},function(e,t,r){"use strict";function i(e){var t=e&&new DataView(e),r={};return t&&n(t)&&a(t,r),r}function n(e){return 65496===e.getUint16(0)}function a(e,t){for(var r=e.byteLength,i=2;i<r;){var n=e.getUint16(i);65505===n&&o(e,i+4,t),65472!==n&&65474!==n||s(e,i+5,t),i+=2+e.getUint16(i+2)}return t}function o(e,t,r){if(1165519206!==e.getUint32(t))return r;var i=t+6,n=void 0,a=e.getUint16(i);if(18761===a)n=!0;else{if(19789!==a)return r;n=!1}for(var o=i+e.getUint32(i+4,n),s=e.getUint16(o,n),l=0;l<s;l++){var d=o+12*l+2,h=e.getUint16(d,n);if(274===h&&(r.orientation=e.getUint16(d+8,n)),34665===h){u(e,i,c(e,d,n),n,r)}}return r}function s(e,t,r){return r.height=e.getUint16(t),r.width=e.getUint16(t+2),r}function u(e,t,r,i,n){var a=d(e,t+r,i),o=void 0;if(37500 in a){o=a[37500];l(e,t+e.getUint32(o+8),n)}return n}function l(e,t,r){for(var i="",n=0;n<9;n++)i+=String.fromCharCode(e.getUint8(t+n));if("Apple iOS"!==i)return r;var a=18761===e.getUint16(t+9+3),o=d(e,t+9+3+2,a);return 27 in o&&(r.photosRenderEffect=c(e,o[27],a)),r}function d(e,t,r){for(var i=e.getUint16(t,r),n=t+2,a={},o=0;o<i;o++){var s=n+12*o;a[e.getUint16(s,r)]=s}return a}function c(e,t,r){var i=e.getUint16(t+2,r),n=e.getUint32(t+4,r),a=e.getUint32(t+8,r),o=[];switch(i){case 3:if(1===n)return e.getUint16(t+8,r);if(2===n)return[e.getUint16(t+8,r),e.getUint16(t+10,r)];for(var s=0;s<n;s++)o[s]=e.getUint16(a+4*s,r);return o;case 4:if(1===n)return a;for(var u=0;u<n;u++)o[u]=e.getUint32(a+4*u,r);return o;case 9:if(1===n)return e.getInt32(t+8,r);for(var l=0;l<n;l++)o[l]=e.getInt32(a+4*l,r);return o;default:return null}}t.a=i},function(e,t,r){"use strict";function i(){for(var e=n()+n();e.length<16;)e+=n();return e.slice(0,16)}function n(){return Math.random().toString(16).substring(2)}t.a=i},function(e,t,r){"use strict";function i(e,t){var r=e,i=r._computedStyle;i||(i=r._computedStyle=document.defaultView.getComputedStyle(e,null));var a=i.getPropertyValue(t);return!a&&n.get(t)&&(a=n.get(t)(i)||a),a}t.a=i;var n=function(){var e=new Map;return e.set("font",function(e){for(var t="",r=0,i=a.length;r<i;r++){var n=a[r],o=e.getPropertyValue("font-"+n);if(o&&(t&&(t+=" "),t+=o,"size"===n)){var s=e.getPropertyValue("line-height");s&&(t+="/"+s)}}return t}),e}(),a=["style","variant","weight","size","family"]},function(e,t,r){"use strict";function i(e,t,r,i,n,a,o){var s=void 0;if("string"==typeof e){var u=e;"fit"===u&&(s=!1),"cover"===u&&(s=!0)}else s=!!e;var l=arguments.length;6!==l&&(o=l-1);var d=void 0,c=void 0,h=void 0,p=void 0,f=void 0;if(2===o||3===o){var v=t,y=r;c=v.height,d=v.width,p=y.height,h=y.width,f=i}else d=t,c=r,h=i,p=n,f=a;var m=d/c,g=h/p,b=s?m<g:m>g;return f=f||{},f.width=b?h:p*m,f.height=b?h/m:p,f}function n(e,t,r,n,a){return i(!1,e,t,r,n,a,arguments.length)}t.a=n},function(e,t,r){"use strict";t.a="current"},function(e,t,r){"use strict";t.a="Mcurrent"},function(e,t,r){"use strict";t.a="1.5.6"}])});
//# sourceMappingURL=resources/livephotoskit.js.map
/*
 jquery-qrcode v0.14.0 - https://larsjung.de/jquery-qrcode/ */
'use strict';let G=null;class H{}H.render=function(w,B){G(w,B)};self.QrCreator=H;
(function(w){function B(t,c,a,e){var b={},h=w(a,c);h.u(t);h.J();e=e||0;var r=h.h(),d=h.h()+2*e;b.text=t;b.level=c;b.version=a;b.O=d;b.a=function(b,a){b-=e;a-=e;return 0>b||b>=r||0>a||a>=r?!1:h.a(b,a)};return b}function C(t,c,a,e,b,h,r,d,g,x){function u(b,a,f,c,d,r,g){b?(t.lineTo(a+r,f+g),t.arcTo(a,f,c,d,h)):t.lineTo(a,f)}r?t.moveTo(c+h,a):t.moveTo(c,a);u(d,e,a,e,b,-h,0);u(g,e,b,c,b,0,-h);u(x,c,b,c,a,h,0);u(r,c,a,e,a,0,h)}function z(t,c,a,e,b,h,r,d,g,x){function u(b,a,c,d){t.moveTo(b+c,a);t.lineTo(b,
a);t.lineTo(b,a+d);t.arcTo(b,a,b+c,a,h)}r&&u(c,a,h,h);d&&u(e,a,-h,h);g&&u(e,b,-h,-h);x&&u(c,b,h,-h)}function A(t,c){var a=c.fill;if("string"===typeof a)t.fillStyle=a;else{var e=a.type,b=a.colorStops;a=a.position.map((b)=>Math.round(b*c.size));if("linear-gradient"===e)var h=t.createLinearGradient.apply(t,a);else if("radial-gradient"===e)h=t.createRadialGradient.apply(t,a);else throw Error("Unsupported fill");b.forEach(([b,a])=>{h.addColorStop(b,a)});t.fillStyle=h}}function y(t,c){a:{var a=c.text,e=
c.v,b=c.N,h=c.K,r=c.P;b=Math.max(1,b||1);for(h=Math.min(40,h||40);b<=h;b+=1)try{var d=B(a,e,b,r);break a}catch(J){}d=void 0}if(!d)return null;a=t.getContext("2d");c.background&&(a.fillStyle=c.background,a.fillRect(c.left,c.top,c.size,c.size));e=d.O;h=c.size/e;a.beginPath();for(r=0;r<e;r+=1)for(b=0;b<e;b+=1){var g=a,x=c.left+b*h,u=c.top+r*h,p=r,q=b,f=d.a,k=x+h,m=u+h,D=p-1,E=p+1,n=q-1,l=q+1,y=Math.floor(Math.min(.5,Math.max(0,c.R))*h),v=f(p,q),I=f(D,n),w=f(D,q);D=f(D,l);var F=f(p,l);l=f(E,l);q=f(E,
q);E=f(E,n);p=f(p,n);x=Math.round(x);u=Math.round(u);k=Math.round(k);m=Math.round(m);v?C(g,x,u,k,m,y,!w&&!p,!w&&!F,!q&&!F,!q&&!p):z(g,x,u,k,m,y,w&&p&&I,w&&F&&D,q&&F&&l,q&&p&&E)}A(a,c);a.fill();return t}var v={minVersion:1,maxVersion:40,ecLevel:"L",left:0,top:0,size:200,fill:"#000",background:null,text:"no text",radius:.5,quiet:0};G=function(t,c){var a={};Object.assign(a,v,t);a.N=a.minVersion;a.K=a.maxVersion;a.v=a.ecLevel;a.left=a.left;a.top=a.top;a.size=a.size;a.fill=a.fill;a.background=a.background;
a.text=a.text;a.R=a.radius;a.P=a.quiet;if(c instanceof HTMLCanvasElement){if(c.width!==a.size||c.height!==a.size)c.width=a.size,c.height=a.size;c.getContext("2d").clearRect(0,0,c.width,c.height);y(c,a)}else t=document.createElement("canvas"),t.width=a.size,t.height=a.size,a=y(t,a),c.appendChild(a)}})(function(){function w(c){var a=C.s(c);return{S:function(){return 4},b:function(){return a.length},write:function(c){for(var b=0;b<a.length;b+=1)c.put(a[b],8)}}}function B(){var c=[],a=0,e={B:function(){return c},
c:function(b){return 1==(c[Math.floor(b/8)]>>>7-b%8&1)},put:function(b,h){for(var a=0;a<h;a+=1)e.m(1==(b>>>h-a-1&1))},f:function(){return a},m:function(b){var h=Math.floor(a/8);c.length<=h&&c.push(0);b&&(c[h]|=128>>>a%8);a+=1}};return e}function C(c,a){function e(b,h){for(var a=-1;7>=a;a+=1)if(!(-1>=b+a||d<=b+a))for(var c=-1;7>=c;c+=1)-1>=h+c||d<=h+c||(r[b+a][h+c]=0<=a&&6>=a&&(0==c||6==c)||0<=c&&6>=c&&(0==a||6==a)||2<=a&&4>=a&&2<=c&&4>=c?!0:!1)}function b(b,a){for(var f=d=4*c+17,k=Array(f),m=0;m<
f;m+=1){k[m]=Array(f);for(var p=0;p<f;p+=1)k[m][p]=null}r=k;e(0,0);e(d-7,0);e(0,d-7);f=y.G(c);for(k=0;k<f.length;k+=1)for(m=0;m<f.length;m+=1){p=f[k];var q=f[m];if(null==r[p][q])for(var n=-2;2>=n;n+=1)for(var l=-2;2>=l;l+=1)r[p+n][q+l]=-2==n||2==n||-2==l||2==l||0==n&&0==l}for(f=8;f<d-8;f+=1)null==r[f][6]&&(r[f][6]=0==f%2);for(f=8;f<d-8;f+=1)null==r[6][f]&&(r[6][f]=0==f%2);f=y.w(h<<3|a);for(k=0;15>k;k+=1)m=!b&&1==(f>>k&1),r[6>k?k:8>k?k+1:d-15+k][8]=m,r[8][8>k?d-k-1:9>k?15-k:14-k]=m;r[d-8][8]=!b;if(7<=
c){f=y.A(c);for(k=0;18>k;k+=1)m=!b&&1==(f>>k&1),r[Math.floor(k/3)][k%3+d-8-3]=m;for(k=0;18>k;k+=1)m=!b&&1==(f>>k&1),r[k%3+d-8-3][Math.floor(k/3)]=m}if(null==g){b=t.I(c,h);f=B();for(k=0;k<x.length;k+=1)m=x[k],f.put(4,4),f.put(m.b(),y.f(4,c)),m.write(f);for(k=m=0;k<b.length;k+=1)m+=b[k].j;if(f.f()>8*m)throw Error("code length overflow. ("+f.f()+">"+8*m+")");for(f.f()+4<=8*m&&f.put(0,4);0!=f.f()%8;)f.m(!1);for(;!(f.f()>=8*m);){f.put(236,8);if(f.f()>=8*m)break;f.put(17,8)}var u=0;m=k=0;p=Array(b.length);
q=Array(b.length);for(n=0;n<b.length;n+=1){var v=b[n].j,w=b[n].o-v;k=Math.max(k,v);m=Math.max(m,w);p[n]=Array(v);for(l=0;l<p[n].length;l+=1)p[n][l]=255&f.B()[l+u];u+=v;l=y.C(w);v=z(p[n],l.b()-1).l(l);q[n]=Array(l.b()-1);for(l=0;l<q[n].length;l+=1)w=l+v.b()-q[n].length,q[n][l]=0<=w?v.c(w):0}for(l=f=0;l<b.length;l+=1)f+=b[l].o;f=Array(f);for(l=u=0;l<k;l+=1)for(n=0;n<b.length;n+=1)l<p[n].length&&(f[u]=p[n][l],u+=1);for(l=0;l<m;l+=1)for(n=0;n<b.length;n+=1)l<q[n].length&&(f[u]=q[n][l],u+=1);g=f}b=g;f=
-1;k=d-1;m=7;p=0;a=y.F(a);for(q=d-1;0<q;q-=2)for(6==q&&--q;;){for(n=0;2>n;n+=1)null==r[k][q-n]&&(l=!1,p<b.length&&(l=1==(b[p]>>>m&1)),a(k,q-n)&&(l=!l),r[k][q-n]=l,--m,-1==m&&(p+=1,m=7));k+=f;if(0>k||d<=k){k-=f;f=-f;break}}}var h=A[a],r=null,d=0,g=null,x=[],u={u:function(b){b=w(b);x.push(b);g=null},a:function(b,a){if(0>b||d<=b||0>a||d<=a)throw Error(b+","+a);return r[b][a]},h:function(){return d},J:function(){for(var a=0,h=0,c=0;8>c;c+=1){b(!0,c);var d=y.D(u);if(0==c||a>d)a=d,h=c}b(!1,h)}};return u}
function z(c,a){if("undefined"==typeof c.length)throw Error(c.length+"/"+a);var e=function(){for(var b=0;b<c.length&&0==c[b];)b+=1;for(var r=Array(c.length-b+a),d=0;d<c.length-b;d+=1)r[d]=c[d+b];return r}(),b={c:function(b){return e[b]},b:function(){return e.length},multiply:function(a){for(var h=Array(b.b()+a.b()-1),c=0;c<b.b();c+=1)for(var g=0;g<a.b();g+=1)h[c+g]^=v.i(v.g(b.c(c))+v.g(a.c(g)));return z(h,0)},l:function(a){if(0>b.b()-a.b())return b;for(var c=v.g(b.c(0))-v.g(a.c(0)),h=Array(b.b()),
g=0;g<b.b();g+=1)h[g]=b.c(g);for(g=0;g<a.b();g+=1)h[g]^=v.i(v.g(a.c(g))+c);return z(h,0).l(a)}};return b}C.s=function(c){for(var a=[],e=0;e<c.length;e++){var b=c.charCodeAt(e);128>b?a.push(b):2048>b?a.push(192|b>>6,128|b&63):55296>b||57344<=b?a.push(224|b>>12,128|b>>6&63,128|b&63):(e++,b=65536+((b&1023)<<10|c.charCodeAt(e)&1023),a.push(240|b>>18,128|b>>12&63,128|b>>6&63,128|b&63))}return a};var A={L:1,M:0,Q:3,H:2},y=function(){function c(b){for(var a=0;0!=b;)a+=1,b>>>=1;return a}var a=[[],[6,18],
[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50],[6,30,54],[6,32,58],[6,34,62],[6,26,46,66],[6,26,48,70],[6,26,50,74],[6,30,54,78],[6,30,56,82],[6,30,58,86],[6,34,62,90],[6,28,50,72,94],[6,26,50,74,98],[6,30,54,78,102],[6,28,54,80,106],[6,32,58,84,110],[6,30,58,86,114],[6,34,62,90,118],[6,26,50,74,98,122],[6,30,54,78,102,126],[6,26,52,78,104,130],[6,30,56,82,108,134],[6,34,60,86,112,138],[6,30,58,86,114,142],[6,34,62,90,118,146],[6,30,54,78,102,126,150],[6,24,50,76,102,128,154],
[6,28,54,80,106,132,158],[6,32,58,84,110,136,162],[6,26,54,82,110,138,166],[6,30,58,86,114,142,170]],e={w:function(b){for(var a=b<<10;0<=c(a)-c(1335);)a^=1335<<c(a)-c(1335);return(b<<10|a)^21522},A:function(b){for(var a=b<<12;0<=c(a)-c(7973);)a^=7973<<c(a)-c(7973);return b<<12|a},G:function(b){return a[b-1]},F:function(b){switch(b){case 0:return function(b,a){return 0==(b+a)%2};case 1:return function(b){return 0==b%2};case 2:return function(b,a){return 0==a%3};case 3:return function(b,a){return 0==
(b+a)%3};case 4:return function(b,a){return 0==(Math.floor(b/2)+Math.floor(a/3))%2};case 5:return function(b,a){return 0==b*a%2+b*a%3};case 6:return function(b,a){return 0==(b*a%2+b*a%3)%2};case 7:return function(b,a){return 0==(b*a%3+(b+a)%2)%2};default:throw Error("bad maskPattern:"+b);}},C:function(b){for(var a=z([1],0),c=0;c<b;c+=1)a=a.multiply(z([1,v.i(c)],0));return a},f:function(b,a){if(4!=b||1>a||40<a)throw Error("mode: "+b+"; type: "+a);return 10>a?8:16},D:function(b){for(var a=b.h(),c=0,
d=0;d<a;d+=1)for(var g=0;g<a;g+=1){for(var e=0,t=b.a(d,g),p=-1;1>=p;p+=1)if(!(0>d+p||a<=d+p))for(var q=-1;1>=q;q+=1)0>g+q||a<=g+q||(0!=p||0!=q)&&t==b.a(d+p,g+q)&&(e+=1);5<e&&(c+=3+e-5)}for(d=0;d<a-1;d+=1)for(g=0;g<a-1;g+=1)if(e=0,b.a(d,g)&&(e+=1),b.a(d+1,g)&&(e+=1),b.a(d,g+1)&&(e+=1),b.a(d+1,g+1)&&(e+=1),0==e||4==e)c+=3;for(d=0;d<a;d+=1)for(g=0;g<a-6;g+=1)b.a(d,g)&&!b.a(d,g+1)&&b.a(d,g+2)&&b.a(d,g+3)&&b.a(d,g+4)&&!b.a(d,g+5)&&b.a(d,g+6)&&(c+=40);for(g=0;g<a;g+=1)for(d=0;d<a-6;d+=1)b.a(d,g)&&!b.a(d+
1,g)&&b.a(d+2,g)&&b.a(d+3,g)&&b.a(d+4,g)&&!b.a(d+5,g)&&b.a(d+6,g)&&(c+=40);for(g=e=0;g<a;g+=1)for(d=0;d<a;d+=1)b.a(d,g)&&(e+=1);return c+=Math.abs(100*e/a/a-50)/5*10}};return e}(),v=function(){for(var c=Array(256),a=Array(256),e=0;8>e;e+=1)c[e]=1<<e;for(e=8;256>e;e+=1)c[e]=c[e-4]^c[e-5]^c[e-6]^c[e-8];for(e=0;255>e;e+=1)a[c[e]]=e;return{g:function(b){if(1>b)throw Error("glog("+b+")");return a[b]},i:function(b){for(;0>b;)b+=255;for(;256<=b;)b-=255;return c[b]}}}(),t=function(){function c(b,c){switch(c){case A.L:return a[4*
(b-1)];case A.M:return a[4*(b-1)+1];case A.Q:return a[4*(b-1)+2];case A.H:return a[4*(b-1)+3]}}var a=[[1,26,19],[1,26,16],[1,26,13],[1,26,9],[1,44,34],[1,44,28],[1,44,22],[1,44,16],[1,70,55],[1,70,44],[2,35,17],[2,35,13],[1,100,80],[2,50,32],[2,50,24],[4,25,9],[1,134,108],[2,67,43],[2,33,15,2,34,16],[2,33,11,2,34,12],[2,86,68],[4,43,27],[4,43,19],[4,43,15],[2,98,78],[4,49,31],[2,32,14,4,33,15],[4,39,13,1,40,14],[2,121,97],[2,60,38,2,61,39],[4,40,18,2,41,19],[4,40,14,2,41,15],[2,146,116],[3,58,36,
2,59,37],[4,36,16,4,37,17],[4,36,12,4,37,13],[2,86,68,2,87,69],[4,69,43,1,70,44],[6,43,19,2,44,20],[6,43,15,2,44,16],[4,101,81],[1,80,50,4,81,51],[4,50,22,4,51,23],[3,36,12,8,37,13],[2,116,92,2,117,93],[6,58,36,2,59,37],[4,46,20,6,47,21],[7,42,14,4,43,15],[4,133,107],[8,59,37,1,60,38],[8,44,20,4,45,21],[12,33,11,4,34,12],[3,145,115,1,146,116],[4,64,40,5,65,41],[11,36,16,5,37,17],[11,36,12,5,37,13],[5,109,87,1,110,88],[5,65,41,5,66,42],[5,54,24,7,55,25],[11,36,12,7,37,13],[5,122,98,1,123,99],[7,73,
45,3,74,46],[15,43,19,2,44,20],[3,45,15,13,46,16],[1,135,107,5,136,108],[10,74,46,1,75,47],[1,50,22,15,51,23],[2,42,14,17,43,15],[5,150,120,1,151,121],[9,69,43,4,70,44],[17,50,22,1,51,23],[2,42,14,19,43,15],[3,141,113,4,142,114],[3,70,44,11,71,45],[17,47,21,4,48,22],[9,39,13,16,40,14],[3,135,107,5,136,108],[3,67,41,13,68,42],[15,54,24,5,55,25],[15,43,15,10,44,16],[4,144,116,4,145,117],[17,68,42],[17,50,22,6,51,23],[19,46,16,6,47,17],[2,139,111,7,140,112],[17,74,46],[7,54,24,16,55,25],[34,37,13],[4,
151,121,5,152,122],[4,75,47,14,76,48],[11,54,24,14,55,25],[16,45,15,14,46,16],[6,147,117,4,148,118],[6,73,45,14,74,46],[11,54,24,16,55,25],[30,46,16,2,47,17],[8,132,106,4,133,107],[8,75,47,13,76,48],[7,54,24,22,55,25],[22,45,15,13,46,16],[10,142,114,2,143,115],[19,74,46,4,75,47],[28,50,22,6,51,23],[33,46,16,4,47,17],[8,152,122,4,153,123],[22,73,45,3,74,46],[8,53,23,26,54,24],[12,45,15,28,46,16],[3,147,117,10,148,118],[3,73,45,23,74,46],[4,54,24,31,55,25],[11,45,15,31,46,16],[7,146,116,7,147,117],
[21,73,45,7,74,46],[1,53,23,37,54,24],[19,45,15,26,46,16],[5,145,115,10,146,116],[19,75,47,10,76,48],[15,54,24,25,55,25],[23,45,15,25,46,16],[13,145,115,3,146,116],[2,74,46,29,75,47],[42,54,24,1,55,25],[23,45,15,28,46,16],[17,145,115],[10,74,46,23,75,47],[10,54,24,35,55,25],[19,45,15,35,46,16],[17,145,115,1,146,116],[14,74,46,21,75,47],[29,54,24,19,55,25],[11,45,15,46,46,16],[13,145,115,6,146,116],[14,74,46,23,75,47],[44,54,24,7,55,25],[59,46,16,1,47,17],[12,151,121,7,152,122],[12,75,47,26,76,48],
[39,54,24,14,55,25],[22,45,15,41,46,16],[6,151,121,14,152,122],[6,75,47,34,76,48],[46,54,24,10,55,25],[2,45,15,64,46,16],[17,152,122,4,153,123],[29,74,46,14,75,47],[49,54,24,10,55,25],[24,45,15,46,46,16],[4,152,122,18,153,123],[13,74,46,32,75,47],[48,54,24,14,55,25],[42,45,15,32,46,16],[20,147,117,4,148,118],[40,75,47,7,76,48],[43,54,24,22,55,25],[10,45,15,67,46,16],[19,148,118,6,149,119],[18,75,47,31,76,48],[34,54,24,34,55,25],[20,45,15,61,46,16]],e={I:function(b,a){var e=c(b,a);if("undefined"==
typeof e)throw Error("bad rs block @ typeNumber:"+b+"/errorCorrectLevel:"+a);b=e.length/3;a=[];for(var d=0;d<b;d+=1)for(var g=e[3*d],h=e[3*d+1],t=e[3*d+2],p=0;p<g;p+=1){var q=t,f={};f.o=h;f.j=q;a.push(f)}return a}};return e}();return C}());
//# sourceMappingURL=qr-creator.min.js.map

L.Photo = L.FeatureGroup.extend({
	options: {
		icon: {
			iconSize: [40, 40],
		},
	},

	initialize: function (photos, options) {
		L.setOptions(this, options);
		L.FeatureGroup.prototype.initialize.call(this, photos);
	},

	addLayers: function (photos) {
		if (photos) {
			for (var i = 0, len = photos.length; i < len; i++) {
				this.addLayer(photos[i]);
			}
		}
		return this;
	},

	addLayer: function (photo) {
		L.FeatureGroup.prototype.addLayer.call(this, this.createMarker(photo));
	},

	createMarker: function (photo) {
		var marker = L.marker(photo, {
			icon: L.divIcon(
				L.extend(
					{
						html:
							'<img src="' +
							photo.thumbnail +
							'" ' +
							(photo.thumbnail2x !== "" ? 'srcset="' + photo.thumbnail + " 1x, " + photo.thumbnail2x + ' 2x"' : "") +
							"></img>​",
						className: "leaflet-marker-photo",
					},
					photo,
					this.options.icon
				)
			),
			title: photo.caption || "",
		});
		marker.photo = photo;
		return marker;
	},
});

L.photo = function (photos, options) {
	return new L.Photo(photos, options);
};

if (L.MarkerClusterGroup) {
	L.Photo.Cluster = L.MarkerClusterGroup.extend({
		options: {
			featureGroup: L.photo,
			maxClusterRadius: 100,
			showCoverageOnHover: false,
			iconCreateFunction: function (cluster) {
				return new L.DivIcon(
					L.extend(
						{
							className: "leaflet-marker-photo",
							html:
								'<img src="' +
								cluster.getAllChildMarkers()[0].photo.thumbnail +
								'" ' +
								(cluster.getAllChildMarkers()[0].photo.thumbnail2x !== ""
									? 'srcset="' +
									  cluster.getAllChildMarkers()[0].photo.thumbnail +
									  " 1x, " +
									  cluster.getAllChildMarkers()[0].photo.thumbnail2x +
									  ' 2x"'
									: "") +
								"></img>​<b>" +
								cluster.getChildCount() +
								"</b>",
						},
						this.icon
					)
				);
			},
			icon: {
				iconSize: [40, 40],
			},
		},

		initialize: function (options) {
			options = L.Util.setOptions(this, options);
			L.MarkerClusterGroup.prototype.initialize.call(this);
			this._photos = options.featureGroup(null, options);
		},

		add: function (photos) {
			this.addLayer(this._photos.addLayers(photos));
			return this;
		},

		clear: function () {
			this._photos.clearLayers();
			this.clearLayers();
		},
	});

	L.photo.cluster = function (options) {
		return new L.Photo.Cluster(options);
	};
}

"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'></p>"], ["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'></p>"]),
    _templateObject2 = _taggedTemplateLiteral(["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"], ["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"]),
    _templateObject3 = _taggedTemplateLiteral(["\n\t\t\t<p>", "\n\t\t\t\t<input\n\t\t\t\t\tclass='text'\n\t\t\t\t\tname='show_tags'\n\t\t\t\t\ttype='text'\n\t\t\t\t\tminlength='1'\n\t\t\t\t\tplaceholder='Tags'\n\t\t\t\t\tvalue='$", "'\n\t\t\t\t>\n\t\t\t</p>"], ["\n\t\t\t<p>", "\n\t\t\t\t<input\n\t\t\t\t\tclass='text'\n\t\t\t\t\tname='show_tags'\n\t\t\t\t\ttype='text'\n\t\t\t\t\tminlength='1'\n\t\t\t\t\tplaceholder='Tags'\n\t\t\t\t\tvalue='$", "'\n\t\t\t\t>\n\t\t\t</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"]),
    _templateObject5 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject6 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject7 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"], ["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"]),
    _templateObject9 = _taggedTemplateLiteral(["\n\t\t<div><p>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingCol\" name=\"sortingCol\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='created_at'>", "</option>\n\t\t\t\t\t<option value='taken_at'>", "</option>\n\t\t\t\t\t<option value='title'>", "</option>\n\t\t\t\t\t<option value='description'>", "</option>\n\t\t\t\t\t<option value='is_public'>", "</option>\n\t\t\t\t\t<option value='is_starred'>", "</option>\n\t\t\t\t\t<option value='type'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingOrder\" name=\"sortingOrder\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='ASC'>", "</option>\n\t\t\t\t\t<option value='DESC'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t</p></div>"], ["\n\t\t<div><p>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingCol\" name=\"sortingCol\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='created_at'>", "</option>\n\t\t\t\t\t<option value='taken_at'>", "</option>\n\t\t\t\t\t<option value='title'>", "</option>\n\t\t\t\t\t<option value='description'>", "</option>\n\t\t\t\t\t<option value='is_public'>", "</option>\n\t\t\t\t\t<option value='is_starred'>", "</option>\n\t\t\t\t\t<option value='type'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingOrder\" name=\"sortingOrder\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='ASC'>", "</option>\n\t\t\t\t\t<option value='DESC'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t</p></div>"]),
    _templateObject10 = _taggedTemplateLiteral(["\n\t\t<form>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='grants_full_photo'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='requires_link'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_downloadable'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_share_button_visible'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='has_password'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t</div>\n\t\t\t<div class='hr'><hr></div>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_nsfw'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t</form>\n\t"], ["\n\t\t<form>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='grants_full_photo'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='requires_link'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_downloadable'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_share_button_visible'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='has_password'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t</div>\n\t\t\t<div class='hr'><hr></div>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_nsfw'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t</form>\n\t"]),
    _templateObject11 = _taggedTemplateLiteral(["<div class='choice'>\n\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t</label>\n\t\t\t\t\t\t<p></p>\n\t\t\t\t\t</div>"], ["<div class='choice'>\n\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t</label>\n\t\t\t\t\t\t<p></p>\n\t\t\t\t\t</div>"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t<div id='qr-code' class='downloads'></div>\n\t"], ["\n\t\t<div id='qr-code' class='downloads'></div>\n\t"]),
    _templateObject13 = _taggedTemplateLiteral(["<p>", " '$", "' ", " '$", "'?</p>"], ["<p>", " '$", "' ", " '$", "'?</p>"]),
    _templateObject14 = _taggedTemplateLiteral(["<p>", " '$", "'?</p>"], ["<p>", " '$", "'?</p>"]),
    _templateObject15 = _taggedTemplateLiteral(["<p>", " '$", "' ", "</p>"], ["<p>", " '$", "' ", "</p>"]),
    _templateObject16 = _taggedTemplateLiteral(["<p>", " $", " ", "</p>"], ["<p>", " $", " ", "</p>"]),
    _templateObject17 = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject18 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject19 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject20 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject21 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'\n\t\t\t\tdraggable='", "'\n\t\t\t\t", ">\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'\n\t\t\t\tdraggable='", "'\n\t\t\t\t", ">\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject22 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject24 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'\n\t\t\tdraggable='true'\n\t\t\tondragstart='lychee.startDrag(event)'\n\t\t\tondragend='lychee.endDrag(event)'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'\n\t\t\tdraggable='true'\n\t\t\tondragstart='lychee.startDrag(event)'\n\t\t\tondragend='lychee.endDrag(event)'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject25 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject26 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject27 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject28 = _taggedTemplateLiteral(["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"], ["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"]),
    _templateObject29 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject30 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"]),
    _templateObject31 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject32 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject33 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject34 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject35 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject36 = _taggedTemplateLiteral(["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"], ["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"]),
    _templateObject37 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject38 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject39 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject40 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject41 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject42 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "' alt=\"thumbnail\">\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "' alt=\"thumbnail\">\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject43 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject44 = _taggedTemplateLiteral(["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"], ["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"]),
    _templateObject45 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_notifications\" data-tabindex=\"-1\">", "", " </a>\n\t\t"], ["\n\t\t<a class=\"linkMenu\" id=\"button_notifications\" data-tabindex=\"-1\">", "", " </a>\n\t\t"]),
    _templateObject46 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_users\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_u2f\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\" data-tabindex=\"-1\">", "", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_users\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_u2f\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\" data-tabindex=\"-1\">", "", "</a>"]),
    _templateObject47 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_logs\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\" data-tabindex=\"21\">", "", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_logs\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\" data-tabindex=\"21\">", "", "</a>"]),
    _templateObject48 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_update\"  data-tabindex=\"-1\">", "", "</a>\n\t\t"], ["\n\t\t<a class=\"linkMenu\" id=\"button_update\"  data-tabindex=\"-1\">", "", "</a>\n\t\t"]),
    _templateObject49 = _taggedTemplateLiteral(["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "], ["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "]),
    _templateObject50 = _taggedTemplateLiteral(["\n\t\t\t<a class='signInKeyLess' id='signInKeyLess'>", "</a>\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off' data-tabindex='", "'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "' data-tabindex='", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "' data-tabindex='-1'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"], ["\n\t\t\t<a class='signInKeyLess' id='signInKeyLess'>", "</a>\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off' data-tabindex='", "'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "' data-tabindex='", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "' data-tabindex='-1'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"]),
    _templateObject51 = _taggedTemplateLiteral(["<link data-prefetch rel=\"prefetch\" href=\"", "\">"], ["<link data-prefetch rel=\"prefetch\" href=\"", "\">"]),
    _templateObject52 = _taggedTemplateLiteral(["<p>", " '", "'", "</p>"], ["<p>", " '", "'", "</p>"]),
    _templateObject53 = _taggedTemplateLiteral(["<p>", " ", " ", "</p>"], ["<p>", " ", " ", "</p>"]),
    _templateObject54 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='$", "'>"]),
    _templateObject55 = _taggedTemplateLiteral(["<p>", " ", " ", " ", "</p>"], ["<p>", " ", " ", " ", "</p>"]),
    _templateObject56 = _taggedTemplateLiteral(["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject57 = _taggedTemplateLiteral(["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='grants_full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='requires_link' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='has_password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='grants_full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='requires_link' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='has_password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject58 = _taggedTemplateLiteral(["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"], ["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"]),
    _templateObject59 = _taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"], ["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"]),
    _templateObject60 = _taggedTemplateLiteral(["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"], ["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"]),
    _templateObject61 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"]),
    _templateObject62 = _taggedTemplateLiteral(["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"], ["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"]),
    _templateObject63 = _taggedTemplateLiteral(["\n\t\t\t<div class='downloads'>\n\t\t"], ["\n\t\t\t<div class='downloads'>\n\t\t"]),
    _templateObject64 = _taggedTemplateLiteral(["\n\t\t\t</div>\n\t\t"], ["\n\t\t\t</div>\n\t\t"]),
    _templateObject65 = _taggedTemplateLiteral(["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"]),
    _templateObject66 = _taggedTemplateLiteral(["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"], ["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"]),
    _templateObject67 = _taggedTemplateLiteral(["\n\t\t</div>\n\t\t</div>\n\t"], ["\n\t\t</div>\n\t\t</div>\n\t"]),
    _templateObject68 = _taggedTemplateLiteral(["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"], ["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"]),
    _templateObject69 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject70 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject71 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject72 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject73 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject74 = _taggedTemplateLiteral(["<h1>", "</h1>"], ["<h1>", "</h1>"]),
    _templateObject75 = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject76 = _taggedTemplateLiteral(["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete_imported' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='import_via_symlink' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skip_duplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resync_metadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete_imported' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='import_via_symlink' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skip_duplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resync_metadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject77 = _taggedTemplateLiteral(["url(\"", "\")"], ["url(\"", "\")"]),
    _templateObject78 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]),
    _templateObject79 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t</div>\n\t\t\t</div>"], ["\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t</div>\n\t\t\t</div>"]),
    _templateObject80 = _taggedTemplateLiteral(["\n\t\t\t\t<div class=\"setSorting\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_column\" name=\"sorting_albums_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='max_taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='min_taken_at'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_order\" name=\"sorting_albums_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_column\" name=\"sorting_photos_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_starred'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='type'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t  \t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t  \t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_order\" name=\"sorting_photos_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t\t<div class=\"setSorting\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_column\" name=\"sorting_albums_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='max_taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='min_taken_at'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_order\" name=\"sorting_albums_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_column\" name=\"sorting_photos_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_starred'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='type'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t  \t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t  \t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_order\" name=\"sorting_photos_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject81 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"]),
    _templateObject82 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t<p class=\"warning\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"], ["\n\t\t\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t<p class=\"warning\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"]),
    _templateObject83 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t\t\t\t</div>"]),
    _templateObject84 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t\t\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t\t\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t</div>"]),
    _templateObject85 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t\t\t\t\t</div>"]),
    _templateObject86 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"Clean_Noise\" class=\"basicModal__button\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</div>\n\t\t\t<pre class=\"logs_diagnostics_view\"></pre>"], ["\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"Clean_Noise\" class=\"basicModal__button\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</div>\n\t\t\t<pre class=\"logs_diagnostics_view\"></pre>"]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

/**
 * @description This module communicates with Lychee's API
 */

/**
 * @callback APISuccessCB
 * @param {Object} data the decoded JSON response
 * @returns {void}
 */

/**
 * @callback APIErrorCB
 * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee exception
 * @returns {boolean}
 */

/**
 * @callback APIProgressCB
 * @param {ProgressEvent} event the progress event
 * @returns {void}
 */

/**
 * The main API object
 */
var api = {
	/**
  * Global, default error handler
  *
  * @type {?APIErrorCB}
  */
	onError: null
};

/**
 * Checks whether the returned error is probably due to an expired HTTP session.
 *
 * There seem to be two variants how an expired session may be reported:
 *
 *  1. The web-application has already been loaded, is fully initialized
 *     and a user tries to navigate to another part of the gallery.
 *     In this case, the AJAX request sends the previous, expired CSRF token
 *     and the backend responds with a 419 status code.
 *  2. The user completely reloads the website (e.g. typically be hitting
 *     F5 in most browsers).
 *     In this case, the CSRF token is re-generated by the backend, so no
 *     CSRF mismatch occurs, but the user is no longer authenticated. and the
 *     backend responds with a 401 status code.
 *
 * Note, case 2 also happens if a user directly navigates to a link
 * of the form `#/album-id/` or `#/album-id/photo-id` unless the album is
 * public, but password protected.
 * In that case, the backend also sends a 401 status code, but with a
 * special "Password Required" exception which is handled specially in
 * `album.js`.
 *
 * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
 * @param {?LycheeException} lycheeException the Lychee exception
 *
 * @returns {boolean}
 */
api.hasSessionExpired = function (jqXHR, lycheeException) {
	return jqXHR.status === 419 && !!lycheeException && lycheeException.exception.endsWith("SessionExpiredException") || jqXHR.status === 401 && !!lycheeException && lycheeException.exception.endsWith("UnauthenticatedException");
};

/**
 *
 * @param {string} fn
 * @param {Object} params
 * @param {?APISuccessCB} successCallback
 * @param {?APIProgressCB} responseProgressCB
 * @param {?APIErrorCB} errorCallback
 * @returns {void}
 */
api.post = function (fn, params) {
	var successCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	var responseProgressCB = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	var errorCallback = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;

	loadingBar.show();

	/**
  * The success handler
  * @param {Object} data the decoded JSON object of the response
  */
	var successHandler = function successHandler(data) {
		setTimeout(loadingBar.hide, 100);
		if (successCallback) successCallback(data);
	};

	/**
  * The error handler
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  */
	var errorHandler = function errorHandler(jqXHR) {
		/**
   * @type {?LycheeException}
   */
		var lycheeException = jqXHR.responseJSON;

		if (errorCallback) {
			var isHandled = errorCallback(jqXHR, params, lycheeException);
			if (isHandled) {
				setTimeout(loadingBar.hide, 100);
				return;
			}
		}
		// Call global error handler for unhandled errors
		api.onError(jqXHR, params, lycheeException);
	};

	var ajaxParams = {
		type: "POST",
		url: "api/" + fn,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		headers: {
			"X-XSRF-TOKEN": csrf.getCSRFCookieValue()
		},
		success: successHandler,
		error: errorHandler
	};

	if (responseProgressCB !== null) {
		ajaxParams.xhrFields = {
			onprogress: responseProgressCB
		};
	}

	$.ajax(ajaxParams);
};

/**
 *
 * @param {string} url
 * @param {APISuccessCB} callback
 * @returns {void}
 */
api.getCSS = function (url, callback) {
	loadingBar.show();

	/**
  * The success handler
  * @param {Object} data the decoded JSON object of the response
  */
	var successHandler = function successHandler(data) {
		setTimeout(loadingBar.hide, 100);

		callback(data);
	};

	/**
  * The error handler
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  */
	var errorHandler = function errorHandler(jqXHR) {
		api.onError(jqXHR, {}, null);
	};

	$.ajax({
		type: "GET",
		url: url,
		data: {},
		dataType: "text",
		headers: {
			"X-XSRF-TOKEN": csrf.getCSRFCookieValue()
		},
		success: successHandler,
		error: errorHandler
	});
};

var csrf = {};

/**
 * Returns the value of the CSRF token.
 *
 * Inspired by https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie#example_2_get_a_sample_cookie_named_test2
 *
 * @returns {?string}
 */
csrf.getCSRFCookieValue = function () {
	var cookie = document.cookie.split(";").find(function (row) {
		return (/^\s*(X-)?[XC]SRF-TOKEN\s*=/.test(row)
		);
	});
	// We must remove all '%3D' from the end of the string.
	// Background:
	// The actual binary value of the CSFR value is encoded in Base64.
	// If the length of original, binary value is not a multiple of 3 bytes,
	// the encoding gets padded with `=` on the right; i.e. there might be
	// zero, one or two `=` at the end of the encoded value.
	// If the value is sent from the server to the client as part of a cookie,
	// the `=` character is URL-encoded as `%3D`, because `=` is already used
	// to separate a cookie key from its value.
	// When we send back the value to the server as part of an AJAX request,
	// Laravel expects an unpadded value.
	// Hence, we must remove the `%3D`.
	return cookie ? cookie.split("=")[1].trim().replaceAll("%3D", "") : null;
};

(function ($) {
	var Swipe = function Swipe(el) {
		var self = this;

		this.el = $(el);
		this.pos = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 } };
		this.startTime = null;

		el.on("touchstart", function (e) {
			self.touchStart(e);
		});
		el.on("touchmove", function (e) {
			self.touchMove(e);
		});
		el.on("touchend", function () {
			self.swipeEnd();
		});
		el.on("mousedown", function (e) {
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

			this.el.on("mousemove", function (_e) {
				self.mouseMove(_e);
			});
			this.el.on("mouseup", function () {
				self.mouseUp();
			});
		},

		mouseMove: function mouseMove(e) {
			this.swipeMove(e, e.pageX, e.pageY);
		},

		mouseUp: function mouseUp(e) {
			this.swipeEnd(e);

			this.el.off("mousemove");
			this.el.off("mouseup");
		},

		swipeStart: function swipeStart(e, x, y) {
			this.pos.start.x = x;
			this.pos.start.y = y;
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.startTime = new Date().getTime();

			this.trigger("swipeStart", e);
		},

		swipeMove: function swipeMove(e, x, y) {
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.trigger("swipeMove", e);
		},

		swipeEnd: function swipeEnd(e) {
			this.trigger("swipeEnd", e);
		},

		trigger: function trigger(e, originalEvent) {
			var self = this;

			var event = $.Event(e),
			    x = self.pos.start.x - self.pos.end.x,
			    y = self.pos.end.y - self.pos.start.y,
			    radians = Math.atan2(y, x),
			    direction = "up",
			    distance = Math.round(Math.sqrt(Math.pow(x, 2) + Math.pow(y, 2))),
			    angle = Math.round(radians * 180 / Math.PI),
			    speed = Math.round(distance / (new Date().getTime() - self.startTime) * 1000);

			if (angle < 0) {
				angle = 360 - Math.abs(angle);
			}

			if (angle <= 45 && angle >= 0 || angle <= 360 && angle >= 315) {
				direction = "left";
			} else if (angle >= 135 && angle <= 225) {
				direction = "right";
			} else if (angle > 45 && angle < 135) {
				direction = "down";
			}

			event.originalEvent = originalEvent;

			event.swipe = {
				x: x,
				y: y,
				direction: direction,
				distance: distance,
				angle: angle,
				speed: speed
			};

			$(self.el).trigger(event);
		}
	};

	$.fn.swipe = function () {
		// let swipe = new Swipe(this);
		new Swipe(this);

		return this;
	};
})(jQuery);

/**
 * @description Takes care of every action an album can handle and execute.
 */

var album = {
	/** @type {(?Album|?TagAlbum|?SearchAlbum)} */
	json: null
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isSmartID = function (id) {
	return id === SmartAlbumID.UNSORTED || id === SmartAlbumID.STARRED || id === SmartAlbumID.PUBLIC || id === SmartAlbumID.RECENT;
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isSearchID = function (id) {
	return id === SearchAlbumID;
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isModelID = function (id) {
	return typeof id === "string" && /^[-_0-9a-zA-Z]{24}$/.test(id);
};

/**
 * @returns {?string}
 */
album.getParentID = function () {
	if (album.json === null || album.isSmartID(album.json.id) || album.isSearchID(album.json.id) || !album.json.parent_id) {
		return null;
	}
	return album.json.parent_id;
};

/**
 * @returns {?string} the album ID
 */
album.getID = function () {
	/** @type {?string} */
	var id = null;

	// this is a Lambda
	var isID = function isID(_id) {
		return album.isSmartID(_id) || /*album.isSearchID(_id) || */album.isModelID(_id);
	};

	if (_photo3.json) id = _photo3.json.album_id;else if (album.json) id = album.json.id;else if (mapview.albumID) id = mapview.albumID;

	// Search
	if (isID(id) === false) id = $(".album:hover, .album.active").attr("data-id");
	if (isID(id) === false) id = $(".photo:hover, .photo.active").attr("data-album-id");

	if (isID(id) === true) return id;else return null;
};

/**
 * @returns {boolean}
 */
album.isTagAlbum = function () {
	return album.json && album.json.is_tag_album && album.json.is_tag_album === true;
};

/**
 * @param {?string} photoID
 * @returns {?Photo} the photo model
 */
album.getByID = function (photoID) {
	if (photoID == null || !album.json || !album.json.photos) {
		loadingBar.show("error", "Error: Album json not found !");
		return null;
	}

	var i = 0;
	while (i < album.json.photos.length) {
		if (album.json.photos[i].id === photoID) {
			return album.json.photos[i];
		}
		i++;
	}

	loadingBar.show("error", "Error: photo " + photoID + " not found !");
	return null;
};

/**
 * Returns the sub-album of the current album by ID, if found.
 *
 * Note: If the current album is the special {@link SearchAlbum}, then
 * also {@link TagAlbum} may be returned as a "sub album".
 *
 * @param {?string} albumID
 * @returns {(?Album|?TagAlbum)} the sub-album model
 */
album.getSubByID = function (albumID) {
	// The special `SearchAlbum`  may also contain `TagAlbum` as sub-albums
	if (albumID == null || !album.json || !album.json.albums && !album.json.tag_albums) {
		loadingBar.show("error", "Error: Album json not found!");
		return null;
	}

	var subAlbum = album.json.albums ? album.json.albums.find(function (a) {
		return a.id === albumID;
	}) : null;
	if (subAlbum) {
		return subAlbum;
	}

	var subTagAlbum = album.json.tag_albums ? album.json.tag_albums.find(function (a) {
		return a.id === albumID;
	}) : null;
	if (subTagAlbum) {
		return subTagAlbum;
	}

	loadingBar.show("error", "Error: album " + albumID + " not found!");
	return null;
};

/**
 * @param {string} photoID
 * @returns {void}
 */
album.deleteByID = function (photoID) {
	if (photoID == null || !album.json || !album.json.photos) {
		loadingBar.show("error", "Error: Album json not found !");
		return;
	}

	$.each(album.json.photos, function (i) {
		if (album.json.photos[i].id === photoID) {
			album.json.photos.splice(i, 1);
			return false;
		}
	});
};

/**
 * @param {string} albumID
 * @returns {boolean}
 */
album.deleteSubByID = function (albumID) {
	if (albumID == null || !album.json || !album.json.albums) {
		loadingBar.show("error", "Error: Album json not found !");
		return false;
	}

	var deleted = false;

	$.each(album.json.albums, function (i) {
		if (album.json.albums[i].id === albumID) {
			album.json.albums.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

/**
 * @callback AlbumLoadedCB
 * @param {boolean} accessible - `true`, if the album has successfully been
 *                                loaded and parsed; `false`, if the album is
 *                                private or public, but unlocked
 * @returns {void}
 */

/**
 * @param {string} albumID
 * @param {?AlbumLoadedCB} [albumLoadedCB=null]
 *
 * @returns {void}
 */
album.load = function (albumID) {
	var albumLoadedCB = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	/**
  * @param {Album} data
  */
	var processAlbum = function processAlbum(data) {
		album.json = data;

		if (albumLoadedCB === null) {
			lychee.animate(lychee.content, "contentZoomOut");
		}
		var waitTime = 300;

		// Skip delay when we have a callback `albumLoadedCB`
		// Skip delay when opening a blank Lychee
		if (albumLoadedCB) waitTime = 0;
		if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;

		setTimeout(function () {
			view.album.init();

			if (albumLoadedCB === null) {
				lychee.animate(lychee.content, "contentZoomIn");
				header.setMode("album");
			}

			tabindex.makeFocusable(lychee.content);
			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, waitTime);
	};

	/**
  * @param {Album} data
  */
	var successHandler = function successHandler(data) {
		processAlbum(data);

		tabindex.makeFocusable(lychee.content);

		if (lychee.active_focus_on_page_load) {
			// Put focus on first element - either album or photo
			var first_album = $(".album:first");
			if (first_album.length !== 0) {
				first_album.focus();
			} else {
				var first_photo = $(".photo:first");
				if (first_photo.length !== 0) {
					first_photo.focus();
				}
			}
		}

		if (albumLoadedCB) albumLoadedCB(true);
	};

	/**
  * @param {XMLHttpRequest} jqXHR
  * @param {Object} params the original JSON parameters of the request
  * @param {?LycheeException} lycheeException the Lychee exception
  * @returns {boolean}
  */
	var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
		if (jqXHR.status !== 401 && jqXHR.status !== 403) {
			// Any other error then unauthenticated or unauthorized
			// shall be handled by the global error handler.
			return false;
		}

		if (lycheeException.exception.endsWith("PasswordRequiredException")) {
			// If a password is required, then try to unlock the album
			// and in case of success, try again to load album with same
			// parameters
			password.getDialog(albumID, function () {
				albums.refresh();
				album.load(albumID, albumLoadedCB);
			});
			return true;
		} else if (albumLoadedCB) {
			// In case we could not successfully load and unlock the album,
			// but we have a callback, we call that and consider the error
			// handled.
			// Note: This case occurs for a single public photo on an
			// otherwise non-public album.
			album.json = null;
			albumLoadedCB(false);
			return true;
		} else {
			// In any other case, let the global error handler deal with the
			// problem.
			return false;
		}
	};

	api.post("Album::get", { albumID: albumID }, successHandler, null, errorHandler);
};

/**
 * Creates a new album.
 *
 * The method optionally calls the provided callback after the new album
 * has been created and passes the ID of the newly created album plus the
 * provided `IDs`.
 *
 * Actually, the callback should enclose all additional parameter it needs.
 * The parameter `IDs` is not needed by this method itself.
 * TODO: Refactor callbacks.
 * Also see comments for {@link TargetAlbumSelectedCB} and
 * {@link contextMenu.move}.
 *
 * @param {string[]}              [IDs=null]      some IDs which are passed on to the callback
 * @param {TargetAlbumSelectedCB} [callback=null] called upon successful creation of the album
 *
 * @returns {void}
 */
album.add = function () {
	var IDs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	/**
  * @param {{title: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		// let title = data.title;

		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var params = {
			title: data.title,
			parent_id: null
		};

		if (visible.albums() || album.isSmartID(album.json.id) || album.isSearchID(album.json.id)) {
			params.parent_id = null;
		} else if (visible.album()) {
			params.parent_id = album.json.id;
		} else if (visible.photo()) {
			params.parent_id = _photo3.json.album_id;
		}

		api.post("Album::add", params,
		/** @param {Album} _data */
		function (_data) {
			if (IDs != null && callback != null) {
				callback(IDs, _data.id, false); // we do not confirm
			} else {
				albums.refresh();
				lychee.goto(_data.id);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject, lychee.locale["TITLE_NEW_ALBUM"], lychee.locale["UNTITLED"]),
		buttons: {
			action: {
				title: lychee.locale["CREATE_ALBUM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @returns {void}
 */
album.addByTags = function () {
	/** @param {{title: string, tags: string}} data */
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}
		if (!data.tags.trim()) {
			basicModal.error("tags");
			return;
		}

		basicModal.close();

		api.post("Album::addByTags", {
			title: data.title,
			tags: data.tags.split(",")
		},
		/** @param {TagAlbum} _data */
		function (_data) {
			albums.refresh();
			lychee.goto(_data.id);
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject2, lychee.locale["TITLE_NEW_ALBUM"], lychee.locale["UNTITLED"]),
		buttons: {
			action: {
				title: lychee.locale["CREATE_TAG_ALBUM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setShowTags = function (albumID) {
	/** @param {{show_tags: string}} data */
	var action = function action(data) {
		if (!data.show_tags.trim()) {
			basicModal.error("show_tags");
			return;
		}
		var new_show_tags = data.show_tags.split(",").map(function (tag) {
			return tag.trim();
		}).filter(function (tag) {
			return tag !== "" && tag.indexOf(",") === -1;
		}).sort();

		basicModal.close();

		if (visible.album()) {
			album.json.show_tags = new_show_tags;
			view.album.show_tags();
		}

		api.post("Album::setShowTags", {
			albumID: albumID,
			show_tags: new_show_tags
		}, function () {
			return album.reload();
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject3, lychee.locale["ALBUM_NEW_SHOWTAGS"], album.json.show_tags.sort().join(", ")),
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_SHOWTAGS"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 *
 * @param {string[]} albumIDs
 * @returns {boolean}
 */
album.setTitle = function (albumIDs) {
	var oldTitle = "";

	if (albumIDs.length === 1) {
		// Get old title if only one album is selected
		if (album.json) {
			if (album.getID() === albumIDs[0]) {
				oldTitle = album.json.title;
			} else oldTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!oldTitle) {
			var a = albums.getByID(albumIDs[0]);
			if (a) oldTitle = a.title;
		}
	}

	/** @param {{title: string}} data */
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title;

		if (visible.album()) {
			if (albumIDs.length === 1 && album.getID() === albumIDs[0]) {
				// Rename only one album

				album.json.title = newTitle;
				view.album.title();

				var _a = albums.getByID(albumIDs[0]);
				if (_a) _a.title = newTitle;
			} else {
				albumIDs.forEach(function (id) {
					album.getSubByID(id).title = newTitle;
					view.album.content.titleSub(id);

					var a = albums.getByID(id);
					if (a) a.title = newTitle;
				});
			}
		} else if (visible.albums()) {
			// Rename all albums

			albumIDs.forEach(function (id) {
				var a = albums.getByID(id);
				if (a) a.title = newTitle;
				view.albums.content.title(id);
			});
		}

		api.post("Album::setTitle", {
			albumIDs: albumIDs,
			title: newTitle
		});
	};

	var inputHTML = lychee.html(_templateObject4, lychee.locale["ALBUM_TITLE"], oldTitle);

	var dialogHTML = albumIDs.length === 1 ? lychee.html(_templateObject5, lychee.locale["ALBUM_NEW_TITLE"], inputHTML) : lychee.html(_templateObject6, lychee.locale["ALBUMS_NEW_TITLE_1"], albumIDs.length, lychee.locale["ALBUMS_NEW_TITLE_2"], inputHTML);

	basicModal.show({
		body: dialogHTML,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_TITLE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setDescription = function (albumID) {
	var oldDescription = album.json.description ? album.json.description : "";

	/** @param {{description: string}} data */
	var action = function action(data) {
		var description = data.description ? data.description : null;

		basicModal.close();

		if (visible.album()) {
			album.json.description = description;
			view.album.description();
		}

		api.post("Album::setDescription", {
			albumID: albumID,
			description: description
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject7, lychee.locale["ALBUM_NEW_DESCRIPTION"], lychee.locale["ALBUM_DESCRIPTION"], oldDescription),
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_DESCRIPTION"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string} photoID
 * @returns {void}
 */
album.toggleCover = function (photoID) {
	album.json.cover_id = album.json.cover_id === photoID ? null : photoID;

	var params = {
		albumID: album.json.id,
		photoID: album.json.cover_id
	};

	api.post("Album::setCover", params, function () {
		view.album.content.cover(photoID);
		if (!album.getParentID()) {
			albums.refresh();
		}
	});
};

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setLicense = function (albumID) {
	var callback = function callback() {
		$("select#license").val(album.json.license === "" ? "none" : album.json.license);
	};

	/** @param {{license: string}} data */
	var action = function action(data) {
		basicModal.close();

		api.post("Album::setLicense", {
			albumID: albumID,
			license: data.license
		}, function () {
			if (visible.album()) {
				album.json.license = data.license;
				view.album.license();
			}
		});
	};

	var msg = lychee.html(_templateObject8, lychee.locale["ALBUM_LICENSE"], lychee.locale["ALBUM_LICENSE_NONE"], lychee.locale["ALBUM_RESERVED"], lychee.locale["ALBUM_LICENSE_HELP"]);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_LICENSE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setSorting = function (albumID) {
	var callback = function callback() {
		if (album.json.sorting) {
			$("select#sortingCol").val(album.json.sorting.column);
			$("select#sortingOrder").val(album.json.sorting.order);
		} else {
			$("select#sortingCol").val("");
			$("select#sortingOrder").val("");
		}
	};

	/** @param {{sortingCol: string, sortingOrder: string}} data */
	var action = function action(data) {
		basicModal.close();

		api.post("Album::setSorting", {
			albumID: albumID,
			sorting_column: data.sortingCol,
			sorting_order: data.sortingOrder
		}, function () {
			if (visible.album()) {
				album.reload();
			}
		});
	};

	var msg = lychee.html(_templateObject9, lychee.locale["SORT_PHOTO_BY_1"], lychee.locale["SORT_PHOTO_SELECT_1"], lychee.locale["SORT_PHOTO_SELECT_2"], lychee.locale["SORT_PHOTO_SELECT_3"], lychee.locale["SORT_PHOTO_SELECT_4"], lychee.locale["SORT_PHOTO_SELECT_5"], lychee.locale["SORT_PHOTO_SELECT_6"], lychee.locale["SORT_PHOTO_SELECT_7"], lychee.locale["SORT_PHOTO_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_PHOTO_BY_3"]);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_ORDER"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * Sets the accessibility attributes of an album.
 *
 * @param {string} albumID
 * @returns {void}
 */
album.setProtectionPolicy = function (albumID) {
	var action = function action(data) {
		albums.refresh();

		// TODO: If the modal dialog would provide us with proper boolean values for the checkboxes as part of `data` the same way as it does for text inputs, then we would not need these slow and awkward jQeury selectors
		album.json.is_nsfw = $('.basicModal .switch input[name="is_nsfw"]:checked').length === 1;
		album.json.is_public = $('.basicModal .switch input[name="is_public"]:checked').length === 1;
		album.json.grants_full_photo = $('.basicModal .choice input[name="grants_full_photo"]:checked').length === 1;
		album.json.requires_link = $('.basicModal .choice input[name="requires_link"]:checked').length === 1;
		album.json.is_downloadable = $('.basicModal .choice input[name="is_downloadable"]:checked').length === 1;
		album.json.is_share_button_visible = $('.basicModal .choice input[name="is_share_button_visible"]:checked').length === 1;
		album.json.has_password = $('.basicModal .choice input[name="has_password"]:checked').length === 1;
		var newPassword = $('.basicModal .choice input[name="passwordtext"]').val() || null;

		// Modal input has been processed, now it can be closed
		basicModal.close();

		// Set data and refresh view
		if (visible.album()) {
			view.album.nsfw();
			view.album.public();
			view.album.requiresLink();
			view.album.downloadable();
			view.album.shareButtonVisible();
			view.album.password();
		}

		var params = {
			albumID: albumID,
			grants_full_photo: album.json.grants_full_photo,
			is_public: album.json.is_public,
			is_nsfw: album.json.is_nsfw,
			requires_link: album.json.requires_link,
			is_downloadable: album.json.is_downloadable,
			is_share_button_visible: album.json.is_share_button_visible
		};
		if (album.json.has_password) {
			if (newPassword) {
				// We send the password only if there's been a change; that way the
				// server will keep the current password if it wasn't changed.
				params.password = newPassword;
			}
		} else {
			params.password = null;
		}

		api.post("Album::setProtectionPolicy", params);
	};

	var msg = lychee.html(_templateObject10, lychee.locale["ALBUM_PUBLIC"], lychee.locale["ALBUM_PUBLIC_EXPL"], build.iconic("check"), lychee.locale["ALBUM_FULL"], lychee.locale["ALBUM_FULL_EXPL"], build.iconic("check"), lychee.locale["ALBUM_HIDDEN"], lychee.locale["ALBUM_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["ALBUM_DOWNLOADABLE"], lychee.locale["ALBUM_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_PASSWORD_PROT"], lychee.locale["ALBUM_PASSWORD_PROT_EXPL"], lychee.locale["PASSWORD"], lychee.locale["ALBUM_NSFW"], lychee.locale["ALBUM_NSFW_EXPL"]);

	var dialogSetupCB = function dialogSetupCB() {
		// TODO: If the modal dialog would provide this callback with proper jQuery objects for all input/select/choice elements, then we would not need these jQuery selectors
		$('.basicModal .switch input[name="is_public"]').prop("checked", album.json.is_public);
		$('.basicModal .switch input[name="is_nsfw"]').prop("checked", album.json.is_nsfw);
		if (album.json.is_public) {
			$(".basicModal .choice input").attr("disabled", false);
			// Initialize options based on album settings.
			$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", album.json.grants_full_photo);
			$('.basicModal .choice input[name="requires_link"]').prop("checked", album.json.requires_link);
			$('.basicModal .choice input[name="is_downloadable"]').prop("checked", album.json.is_downloadable);
			$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", album.json.is_share_button_visible);
			$('.basicModal .choice input[name="has_password"]').prop("checked", album.json.has_password);
			if (album.json.has_password) {
				$('.basicModal .choice input[name="passwordtext"]').show();
			}
		} else {
			$(".basicModal .choice input").attr("disabled", true);
			// Initialize options based on global settings.
			$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", lychee.grants_full_photo);
			$('.basicModal .choice input[name="requires_link"]').prop("checked", false);
			$('.basicModal .choice input[name="is_downloadable"]').prop("checked", lychee.is_downloadable);
			$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", lychee.is_share_button_visible);
			$('.basicModal .choice input[name="has_password"]').prop("checked", false);
			$('.basicModal .choice input[name="passwordtext"]').hide();
		}

		$('.basicModal .switch input[name="is_public"]').on("change", function () {
			$(".basicModal .choice input").attr("disabled", $(this).prop("checked") !== true);
		});

		$('.basicModal .choice input[name="has_password"]').on("change", function () {
			if ($(this).prop("checked") === true) {
				$('.basicModal .choice input[name="passwordtext"]').show().focus();
			} else {
				$('.basicModal .choice input[name="passwordtext"]').hide();
			}
		});
	};

	basicModal.show({
		body: msg,
		callback: dialogSetupCB,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SHARING_CONFIRM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * Lets a user update the sharing settings of an album.
 *
 * @param {string} albumID
 * @returns {void}
 */
album.shareUsers = function (albumID) {
	var action = function action(data) {
		basicModal.close();

		/** @type {number[]} */
		var sharingToAdd = [];
		/** @type {number[]} */
		var sharingToDelete = [];
		$(".basicModal .choice input").each(function (_, input) {
			var $input = $(input);
			if ($input.is(":checked")) {
				if ($input.data("sharingId") === undefined) {
					// Input is checked but has no sharing id => new share to create
					sharingToAdd.push(Number.parseInt(input.name));
				}
			} else {
				var sharingId = $input.data("sharingId");
				if (sharingId !== undefined) {
					// Input is not checked but has a sharing id => existing share to remove
					sharingToDelete.push(Number.parseInt(sharingId));
				}
			}
		});

		if (sharingToDelete.length > 0) {
			api.post("Sharing::delete", {
				shareIDs: sharingToDelete
			});
		}
		if (sharingToAdd.length > 0) {
			api.post("Sharing::add", {
				albumIDs: [albumID],
				userIDs: sharingToAdd
			});
		}
	};

	var msg = "<form id=\"sharing_people_form\"><p>" + lychee.locale["WAIT_FETCH_DATA"] + "</p></form>";

	var dialogSetupCB = function dialogSetupCB() {
		/** @param {SharingInfo} data */
		var successCallback = function successCallback(data) {
			var sharingForm = $("#sharing_people_form");
			sharingForm.empty();
			if (data.users.length !== 0) {
				sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_LONG_MESSAGE"] + "</p>");
				// Fill with the list of users
				data.users.forEach(function (user) {
					sharingForm.append(lychee.html(_templateObject11, user.id, build.iconic("check"), user.username));
				});
				data.shared.filter(function (val) {
					return val.album_id === albumID;
				}).forEach(function (sharing) {
					// Check all the shares that already exist, and store their sharing id on the element
					var elem = $(".basicModal .choice input[name=\"" + sharing.user_id + "\"]");
					elem.prop("checked", true);
					elem.data("sharingId", sharing.id);
				});
			} else {
				sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_NO_USERS"] + "</p>");
			}
		};

		api.post("Sharing::list", {}, successCallback);
	};

	basicModal.show({
		body: msg,
		callback: dialogSetupCB,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SHARING_CONFIRM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * Toggles the NSFW attribute of the currently loaded album.
 *
 * @returns {void}
 */
album.toggleNSFW = function () {
	album.json.is_nsfw = !album.json.is_nsfw;

	view.album.nsfw();

	api.post("Album::setNSFW", {
		albumID: album.json.id,
		is_nsfw: album.json.is_nsfw
	}, function () {
		return albums.refresh();
	});
};

/**
 * @param {string} service - either `"twitter"`, `"facebook"` or `"mail"`
 * @returns {void}
 */
album.share = function (service) {
	if (album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
		return;
	}

	var url = location.href;

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(album.json.title));
			break;
		case "mail":
			location.href = "mailto:?subject=" + encodeURI(album.json.title) + "&body=" + encodeURI(url);
			break;
	}
};

/**
 * @returns {void}
 */
album.qrCode = function () {
	if (album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
		return;
	}

	var msg = lychee.html(_templateObject12);

	basicModal.show({
		body: msg,
		callback: function callback() {
			qrcode = $("#qr-code");
			QrCreator.render({
				text: location.href,
				radius: 0.0,
				ecLevel: "H",
				fill: "#000000",
				background: "#FFFFFF",
				size: qrcode.width()
			}, qrcode[0]);
		},
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} albumIDs
 * @returns {void}
 */
album.getArchive = function (albumIDs) {
	location.href = "api/Album::getArchive?albumIDs=" + albumIDs.join();
};

/**
 * @param {string[]} albumIDs
 * @param {?string} albumID
 * @param {string} op1
 * @param {string} op2
 * @param {string} ops
 * @returns {string} the HTML content of the dialog
 */
album.buildMessage = function (albumIDs, albumID, op1, op2, ops) {
	var targetTitle = lychee.locale["UNTITLED"];
	var sourceTitle = lychee.locale["UNTITLED"];
	var msg = "";

	// Get title of target album
	if (albumID === null) {
		targetTitle = lychee.locale["ROOT"];
	} else {
		var targetAlbum = albums.getByID(albumID) || album.getSubByID(albumID);
		if (targetAlbum) {
			targetTitle = targetAlbum.title;
		}
	}

	if (albumIDs.length === 1) {
		// Get title of the unique source album
		var sourceAlbum = albums.getByID(albumIDs[0]) || album.getSubByID(albumIDs[0]);
		if (sourceAlbum) {
			sourceTitle = sourceAlbum.title;
		}

		msg = lychee.html(_templateObject13, lychee.locale[op1], sourceTitle, lychee.locale[op2], targetTitle);
	} else {
		msg = lychee.html(_templateObject14, lychee.locale[ops], targetTitle);
	}

	return msg;
};

/**
 * @param {string[]} albumIDs
 * @returns {void}
 */
album.delete = function (albumIDs) {
	var action = {};
	var cancel = {};
	var msg = "";

	action.fn = function () {
		basicModal.close();

		api.post("Album::delete", {
			albumIDs: albumIDs
		}, function () {
			if (visible.albums()) {
				albumIDs.forEach(function (id) {
					view.albums.content.delete(id);
					albums.deleteByID(id);
				});
			} else if (visible.album()) {
				albums.refresh();
				if (albumIDs.length === 1 && album.getID() === albumIDs[0]) {
					lychee.goto(album.getParentID());
				} else {
					albumIDs.forEach(function (id) {
						album.deleteSubByID(id);
						view.album.content.deleteSub(id);
					});
				}
			}
		});
	};

	if (albumIDs.length === 1 && albumIDs[0] === "unsorted") {
		action.title = lychee.locale["CLEAR_UNSORTED"];
		cancel.title = lychee.locale["KEEP_UNSORTED"];

		msg = "<p>" + lychee.locale["DELETE_UNSORTED_CONFIRM"] + "</p>";
	} else if (albumIDs.length === 1) {
		var albumTitle = "";

		action.title = lychee.locale["DELETE_ALBUM_QUESTION"];
		cancel.title = lychee.locale["KEEP_ALBUM"];

		// Get title
		if (album.json) {
			if (album.getID() === albumIDs[0]) {
				albumTitle = album.json.title;
			} else albumTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!albumTitle) {
			var a = albums.getByID(albumIDs[0]);
			if (a) albumTitle = a.title;
		}

		// Fallback for album without a title
		if (!albumTitle) albumTitle = lychee.locale["UNTITLED"];

		msg = lychee.html(_templateObject15, lychee.locale["DELETE_ALBUM_CONFIRMATION_1"], albumTitle, lychee.locale["DELETE_ALBUM_CONFIRMATION_2"]);
	} else {
		action.title = lychee.locale["DELETE_ALBUMS_QUESTION"];
		cancel.title = lychee.locale["KEEP_ALBUMS"];

		msg = lychee.html(_templateObject16, lychee.locale["DELETE_ALBUMS_CONFIRMATION_1"], albumIDs.length, lychee.locale["DELETE_ALBUMS_CONFIRMATION_2"]);
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: "red"
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} albumIDs
 * @param {string} albumID
 * @param {boolean} confirm
 */
album.merge = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();

		api.post("Album::merge", {
			albumID: albumID,
			albumIDs: albumIDs
		}, function () {
			return album.reload();
		});
	};

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, "ALBUM_MERGE_1", "ALBUM_MERGE_2", "ALBUMS_MERGE"),
			buttons: {
				action: {
					title: lychee.locale["MERGE_ALBUM"],
					fn: action,
					class: "red"
				},
				cancel: {
					title: lychee.locale["DONT_MERGE"],
					fn: basicModal.close
				}
			}
		});
	} else {
		action();
	}
};

/**
 * @param {string[]} albumIDs source IDs
 * @param {string}   albumID  target ID
 * @param {boolean}  confirm  show confirmation dialog?
 */
album.setAlbum = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();

		api.post("Album::move", {
			albumID: albumID,
			albumIDs: albumIDs
		}, function () {
			return album.reload();
		});
	};

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, "ALBUM_MOVE_1", "ALBUM_MOVE_2", "ALBUMS_MOVE"),
			buttons: {
				action: {
					title: lychee.locale["MOVE_ALBUMS"],
					fn: action,
					class: "red"
				},
				cancel: {
					title: lychee.locale["NOT_MOVE_ALBUMS"],
					fn: basicModal.close
				}
			}
		});
	} else {
		action();
	}
};

/**
 * @returns {void}
 */
album.apply_nsfw_filter = function () {
	if (lychee.nsfw_visible) {
		$('.album[data-nsfw="1"]').show();
	} else {
		$('.album[data-nsfw="1"]').hide();
	}
};

/**
 * Determines whether the user can upload to the currently active album.
 *
 * For special cases of no album / smart album / etc. we return true.
 * It's only for regular, non-matching albums that we return false.
 *
 * @returns {boolean}
 */
album.isUploadable = function () {
	if (lychee.admin) {
		return true;
	}
	if (lychee.publicMode || !lychee.may_upload) {
		return false;
	}

	// TODO: Comparison of numeric user IDs (instead of names) should be more robust
	return album.json === null || !album.json.owner_name || album.json.owner_name === lychee.username;
};

/**
 * @param {Photo} data
 */
album.updatePhoto = function (data) {
	/**
  * @param {?SizeVariant} src
  * @returns {?SizeVariant}
  */
	var deepCopySizeVariant = function deepCopySizeVariant(src) {
		if (src === undefined || src === null) return null;
		return {
			type: src.type,
			url: src.url,
			width: src.width,
			height: src.height,
			filesize: src.filesize
		};
	};

	if (album.json && album.json.photos) {
		var _photo2 = album.json.photos.find(function (p) {
			return p.id === data.id;
		});

		// Deep copy size variants
		_photo2.size_variants = {
			thumb: deepCopySizeVariant(data.size_variants.thumb),
			thumb2x: deepCopySizeVariant(data.size_variants.thumb2x),
			small: deepCopySizeVariant(data.size_variants.small),
			small2x: deepCopySizeVariant(data.size_variants.small2x),
			medium: deepCopySizeVariant(data.size_variants.medium),
			medium2x: deepCopySizeVariant(data.size_variants.medium2x),
			original: deepCopySizeVariant(data.size_variants.original)
		};
		view.album.content.updatePhoto(_photo2);
		albums.refresh();
	}
};

/**
 * @returns {void}
 */
album.reload = function () {
	var albumID = album.getID();

	album.refresh();
	albums.refresh();

	if (visible.album()) lychee.goto(albumID);else lychee.goto();
};

/**
 * @returns {void}
 */
album.refresh = function () {
	album.json = null;
};

/**
 * @returns {void}
 */
album.deleteTrack = function () {
	album.json.track_url = null;

	api.post("Album::deleteTrack", {
		albumID: album.json.id
	});
};

/**
 * @description Takes care of every action albums can handle and execute.
 */

var albums = {
	/** @type {?Albums} */
	json: null
};

/**
 * @returns {void}
 */
albums.load = function () {
	var startTime = new Date().getTime();

	lychee.animate(lychee.content, "contentZoomOut");

	/**
  * @param {Albums} data
  */
	var successCallback = function successCallback(data) {
		// Smart Albums
		if (data.smart_albums.length > 0) albums.localizeSmartAlbums(data.smart_albums);

		albums.json = data;

		// Skip delay when opening a blank Lychee
		var skipDelay = !visible.albums() && !visible.photo() && !visible.album() || visible.album() && lychee.content.html() === "";
		// Calculate delay
		var durationTime = new Date().getTime() - startTime;
		var waitTime = durationTime > 300 || skipDelay ? 0 : 300 - durationTime;

		setTimeout(function () {
			header.setMode("albums");
			view.albums.init();
			lychee.animate(lychee.content, "contentZoomIn");

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}

			setTimeout(function () {
				lychee.footer_show();
			}, 300);
		}, waitTime);
	};

	if (albums.json === null) {
		api.post("Albums::get", {}, successCallback);
	} else {
		setTimeout(function () {
			header.setMode("albums");
			view.albums.init();
			lychee.animate(lychee.content, "contentZoomIn");

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, 300);
	}
};

/**
 * @param {(Album|TagAlbum|SmartAlbum)} album
 * @returns {void}
 */
albums.parse = function (album) {
	if (!album.thumb) {
		album.thumb = {
			id: "",
			thumb: album.has_password ? "img/password.svg" : "img/no_images.svg",
			type: "image/svg+xml",
			thumb2x: null
		};
	}
};

/**
 * Normalizes the built-in smart albums.
 *
 * @param {SmartAlbums} data
 * @returns {void}
 */
albums.localizeSmartAlbums = function (data) {
	if (data.unsorted) {
		data.unsorted.title = lychee.locale["UNSORTED"];
	}

	if (data.starred) {
		data.starred.title = lychee.locale["STARRED"];
	}

	if (data.public) {
		data.public.title = lychee.locale["PUBLIC"];
		// TODO: Why do we need to set these two attributes? What component relies upon them, what happens if we don't set them? Is it legacy?
		data.public.is_public = true;
		data.public.requires_link = true;
	}

	if (data.recent) {
		data.recent.title = lychee.locale["RECENT"];
	}
};

/**
 * @param {?string} albumID
 * @returns {boolean}
 */
albums.isShared = function (albumID) {
	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var found = false;

	/**
  * @this {Album}
  * @returns {boolean}
  */
	var func = function func() {
		if (this.id === albumID) {
			found = true;
			return false; // stop the loop
		}
		if (this.albums) {
			$.each(this.albums, func);
		}
	};

	if (albums.json.shared_albums !== null) $.each(albums.json.shared_albums, func);

	return found;
};

/**
 * @param {?string} albumID
 * @returns {(null|Album|TagAlbum|SmartAlbum)}
 */
albums.getByID = function (albumID) {
	if (albumID == null) return null;
	if (!albums.json) return null;
	if (!albums.json.albums) return null;

	if (albums.json.smart_albums.hasOwnProperty(albumID)) {
		return albums.json.smart_albums[albumID];
	}

	var result = albums.json.tag_albums.find(function (tagAlbum) {
		return tagAlbum.id === albumID;
	});
	if (result) {
		return result;
	}

	result = albums.json.albums.find(function (album) {
		return album.id === albumID;
	});
	if (result) {
		return result;
	}

	result = albums.json.shared_albums.find(function (album) {
		return album.id === albumID;
	});
	if (result) {
		return result;
	}

	return null;
};

/**
 * Deletes a top-level album by ID from the cached JSON for albums.
 *
 * The method is called by {@link album.delete} after a top-level album has
 * successfully been deleted at the server-side.
 *
 * @param {?string} albumID
 * @returns {void}
 */
albums.deleteByID = function (albumID) {
	if (albumID == null) return;
	if (!albums.json) return;
	if (!albums.json.albums) return;

	var idx = albums.json.albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.albums.splice(idx, 1);

	if (idx !== -1) return;

	idx = albums.json.shared_albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.shared_albums.splice(idx, 1);

	if (idx !== -1) return;

	idx = albums.json.tag_albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.tag_albums.splice(idx, 1);
};

/**
 * @returns {void}
 */
albums.refresh = function () {
	albums.json = null;
};

//noinspection HtmlUnknownTarget

/**
 * @description This module is used to generate HTML-Code.
 */

var build = {};

/**
 * @param {string} icon
 * @param {string} [classes=""]
 *
 * @returns {string}
 */
build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";

	return lychee.html(_templateObject17, classes, icon);
};

/**
 * @param {string} title
 * @returns {string}
 */
build.divider = function (title) {
	return lychee.html(_templateObject18, title);
};

/**
 * @param {string} id
 * @returns {string}
 */
build.editIcon = function (id) {
	return lychee.html(_templateObject19, id, build.iconic("pencil"));
};

/**
 * @param {number} top
 * @param {number} left
 * @returns {string}
 */
build.multiselect = function (top, left) {
	return lychee.html(_templateObject20, top, left);
};

/**
 * Returns HTML for the thumb of an album.
 *
 * @param {(Album|TagAlbum)} data
 *
 * @returns {string}
 */
build.getAlbumThumb = function (data) {
	var isVideo = data.thumb.type && data.thumb.type.indexOf("video") > -1;
	var isRaw = data.thumb.type && data.thumb.type.indexOf("raw") > -1;
	var thumb = data.thumb.thumb;
	var thumb2x = data.thumb.thumb2x;

	if (thumb === "uploads/thumb/" && isVideo) {
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	if (thumb === "uploads/thumb/" && isRaw) {
		return "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}

	return "<span class=\"thumbimg" + (isVideo ? " video" : "") + "\"><img class='lazyload' src='img/placeholder.png' data-src='" + thumb + "' " + (thumb2x !== null ? "data-srcset='" + thumb2x + " 2x'" : "") + " alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
};

/**
 * @param {(Album|TagAlbum|SmartAlbum)} data
 * @param {boolean}                     disabled
 *
 * @returns {string} HTML for the album
 */
build.album = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var formattedCreationTs = lychee.locale.printMonthYear(data.created_at);
	var formattedMinTs = lychee.locale.printMonthYear(data.min_taken_at);
	var formattedMaxTs = lychee.locale.printMonthYear(data.max_taken_at);
	var subtitle = formattedCreationTs;

	// check setting album_subtitle_type:
	// takedate: date range (min/max_takedate from EXIF; if missing defaults to creation)
	// creation: creation date of album
	// description: album description
	// default: any other type defaults to old style setting subtitles based of album sorting
	switch (lychee.album_subtitle_type) {
		case "description":
			subtitle = data.description ? data.description : "";
			break;
		case "takedate":
			if (formattedMinTs !== "" || formattedMaxTs !== "") {
				// either min_taken_at or max_taken_at is set
				subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
				subtitle = "<span title='Camera Date'>" + build.iconic("camera-slr") + "</span>" + subtitle;
				break;
			}
		// fall through
		case "creation":
			break;
		case "oldstyle":
		default:
			if (lychee.sorting_albums && data.min_taken_at && data.max_taken_at) {
				if (lychee.sorting_albums.column === "max_taken_at" || lychee.sorting_albums.column === "min_taken_at") {
					if (formattedMinTs !== "" && formattedMaxTs !== "") {
						subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
					} else if (formattedMinTs !== "" && lychee.sorting_albums.column === "min_taken_at") {
						subtitle = formattedMinTs;
					} else if (formattedMaxTs !== "" && lychee.sorting_albums.column === "max_taken_at") {
						subtitle = formattedMaxTs;
					}
				}
			}
	}

	var html = lychee.html(_templateObject21, disabled ? "disabled" : "", data.is_nsfw && lychee.nsfw_blur ? "blurred" : "", data.id, data.is_nsfw ? "1" : "0", tabindex.get_next_tab_index(), album.isSmartID(data.id) || data.is_tag_album ? "false" : "true", album.isSmartID(data.id) || data.is_tag_album ? "" : "ondragstart='lychee.startDrag(event)'\n\t\t\t\tondragover='lychee.overDrag(event)'\n\t\t\t\tondragleave='lychee.leaveDrag(event)'\n\t\t\t\tondragend='lychee.endDrag(event)'\n\t\t\t\tondrop='lychee.finishDrag(event)'", build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject22, data.is_nsfw ? "badge--nsfw" : "", build.iconic("warning"), data.id === SmartAlbumID.STARRED ? "badge--star" : "", build.iconic("star"), data.id === SmartAlbumID.RECENT ? "badge--visible badge--list" : "", build.iconic("clock"), data.id === SmartAlbumID.PUBLIC || data.is_public ? "badge--visible" : "", data.requires_link ? "badge--hidden" : "badge--not--hidden", build.iconic("eye"), data.id === SmartAlbumID.UNSORTED ? "badge--visible" : "", build.iconic("list"), data.has_password ? "badge--visible" : "", build.iconic("lock-locked"), data.is_tag_album ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.has_albums) {
		html += lychee.html(_templateObject23, build.iconic("layers"));
	}

	html += "</div>";

	return html;
};

/**
 * @param {Photo}   data
 * @param {boolean} disabled
 *
 * @returns {string} HTML for the photo
 */
build.photo = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var html = "";
	var thumbnail = "";
	var thumb2x = "";
	// Note, album.json might not be loaded, if
	//  a) the photo is a single public photo in a private album
	//  b) the photo is part of a search result
	var isCover = album.json && album.json.cover_id === data.id;

	var isVideo = data.type && data.type.indexOf("video") > -1;
	var isRaw = data.type && data.type.indexOf("raw") > -1;
	var isLivePhoto = data.live_photo_url !== "" && data.live_photo_url !== null;

	if (data.size_variants.thumb === null) {
		if (isLivePhoto) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
		if (isVideo) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		} else if (isRaw) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
	} else if (lychee.layout === 0) {
		if (data.size_variants.thumb2x !== null) {
			thumb2x = data.size_variants.thumb2x.url;
		}

		if (thumb2x !== "") {
			thumb2x = "data-srcset='" + thumb2x + " 2x'";
		}

		thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
		thumbnail += "</span>";
	} else {
		if (data.size_variants.small !== null) {
			if (data.size_variants.small2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.small.url + " " + data.size_variants.small.width + "w, " + data.size_variants.small2x.url + " " + data.size_variants.small2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.small.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (data.size_variants.medium !== null) {
			if (data.size_variants.medium2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.medium.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.original.url + "' alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else {
			// Fallback for videos with no small (the case of no thumb is
			// handled at the top of this function).

			if (data.size_variants.thumb2x !== null) {
				thumb2x = data.size_variants.thumb2x.url;
			}

			if (thumb2x !== "") {
				thumb2x = "data-srcset='" + data.size_variants.thumb.url + " " + data.size_variants.thumb.width + "w, " + thumb2x + " " + data.size_variants.thumb2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg video\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject24, disabled ? "disabled" : "", data.album_id, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject25, build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject26, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		// Note, `album.json` might be null, if the photo is displayed as
		// part of a search result and therefore the actual parent album
		// is not loaded. (The "parent" album is the virtual "search album"
		// in this case).
		// This also means that the displayed variant of the public badge of
		// a photo depends on the availability of the parent album.
		// This seems to be an undesired but unavoidable side effect.
		html += lychee.html(_templateObject27, data.is_starred ? "badge--star" : "", build.iconic("star"), data.is_public && album.json && !album.json.is_public ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	html += "</div>";

	return html;
};

/**
 * @param {Photo} data
 * @param {string} overlay_type
 * @param {boolean} [next=false]
 *
 * @returns {string}
 */
build.check_overlay_type = function (data, overlay_type) {
	var next = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	var types = ["desc", "date", "exif", "none"];
	var idx = types.indexOf(overlay_type);
	if (idx < 0) return "none";
	if (next) idx++;
	var exifHash = data.make + data.model + data.shutter + data.iso + (data.type.indexOf("video") !== 0 ? data.aperture + data.focal : "");

	for (var i = 0; i < types.length; i++) {
		var type = types[(idx + i) % types.length];
		if (type === "date" || type === "none") return type;
		if (type === "desc" && data.description && data.description !== "") return type;
		if (type === "exif" && exifHash !== "") return type;
	}
};

/**
 * @param {Photo} data
 * @returns {string}
 */
build.overlay_image = function (data) {
	var overlay = "";
	switch (build.check_overlay_type(data, lychee.image_overlay_type)) {
		case "desc":
			overlay = data.description;
			break;
		case "date":
			if (data.taken_at != null) overlay = "<a><span title='Camera Date'>" + build.iconic("camera-slr") + "</span>" + lychee.locale.printDateTime(data.taken_at) + "</a>";else overlay = lychee.locale.printDateTime(data.created_at);
			break;
		case "exif":
			var exifHash = data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;
			if (exifHash !== "") {
				if (data.shutter && data.shutter !== "") overlay = data.shutter.replace("s", "sec");
				if (data.aperture && data.aperture !== "") {
					if (overlay !== "") overlay += " at ";
					overlay += data.aperture.replace("f/", "&fnof; / ");
				}
				if (data.iso && data.iso !== "") {
					if (overlay !== "") overlay += ", ";
					overlay += lychee.locale["PHOTO_ISO"] + " " + data.iso;
				}
				if (data.focal && data.focal !== "") {
					if (overlay !== "") overlay += "<br>";
					overlay += data.focal + (data.lens && data.lens !== "" ? " (" + data.lens + ")" : "");
				}
			}
			break;
		case "none":
		default:
			return "";
	}

	return lychee.html(_templateObject28, data.title ? data.title : lychee.locale["UNTITLED"]) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
};

/**
 * @param {Photo} data
 * @param {boolean} areControlsVisible
 * @param {boolean} autoplay
 * @returns {{thumb: string, html: string}}
 */
build.imageview = function (data, areControlsVisible, autoplay) {
	var html = "";
	var thumb = "";

	if (data.type.indexOf("video") > -1) {
		html += lychee.html(_templateObject29, areControlsVisible ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.size_variants.original.url);
	} else if (data.type.indexOf("raw") > -1 && data.size_variants.medium === null) {
		html += lychee.html(_templateObject30, areControlsVisible ? "" : "full", tabindex.get_next_tab_index());
	} else {
		var img = "";

		if (data.live_photo_url === "" || data.live_photo_url === null) {
			// It's normal photo

			// See if we have the thumbnail loaded...
			$(".photo").each(function () {
				if ($(this).attr("data-id") && $(this).attr("data-id") === data.id) {
					var thumbimg = $(this).find("img");
					if (thumbimg.length > 0) {
						thumb = thumbimg[0].currentSrc ? thumbimg[0].currentSrc : thumbimg[0].src;
						return false;
					}
				}
			});

			if (data.size_variants.medium !== null) {
				var medium = "";

				if (data.size_variants.medium2x !== null) {
					medium = "srcset='" + data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w'";
				}
				img = "<img id='image' class='" + (areControlsVisible ? "" : "full") + "' src='" + data.size_variants.medium.url + "' " + medium + ("  draggable='false' alt='medium' data-tabindex='" + tabindex.get_next_tab_index() + "'>");
			} else {
				img = "<img id='image' class='" + (areControlsVisible ? "" : "full") + "' src='" + data.size_variants.original.url + "' draggable='false' alt='big' data-tabindex='" + tabindex.get_next_tab_index() + "'>";
			}
		} else {
			if (data.size_variants.medium !== null) {
				var medium_width = data.size_variants.medium.width;
				var medium_height = data.size_variants.medium.height;
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.size_variants.medium.url + "' data-video-src='" + data.live_photo_url + "'  style='width: " + medium_width + "px; height: " + medium_height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			} else {
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.size_variants.original.url + "' data-video-src='" + data.live_photo_url + "'  style='width: " + data.size_variants.original.width + "px; height: " + data.size_variants.original.height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			}
		}

		html += lychee.html(_templateObject31, img);
	}

	html += build.overlay_image(data) + ("\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic("caret-left") + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic("caret-right") + "</a></div>\n\t\t\t");

	return { html: html, thumb: thumb };
};

/**
 * @param {string} type - either `"magnifying-glass"`, `"eye"`, `"cog"` or `"question-marks"`
 * @returns {string}
 */
build.no_content = function (type) {
	var html = "";

	html += lychee.html(_templateObject32, build.iconic(type));

	switch (type) {
		case "magnifying-glass":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_RESULT"]);
			break;
		case "eye":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_PUBLIC_ALBUMS"]);
			break;
		case "cog":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_CONFIGURATION"]);
			break;
		case "question-mark":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_PHOTO_NOT_FOUND"]);
			break;
	}

	html += "</div>";

	return html;
};

/**
 * @param {string}                                           title the title of the dialog
 * @param {(FileList|File[]|DropboxFile[]|{name: string}[])} files a list of file entries to be shown in the dialog
 * @returns {string}                                                the HTML fragment for the dialog
 */
build.uploadModal = function (title, files) {
	var html = "";

	html += lychee.html(_templateObject34, title);

	var i = 0;

	while (i < files.length) {
		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + "..." + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject35, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

/**
 * Builds the HTML snippet for a row in the upload dialog.
 *
 * @param {string} name
 * @returns {string}
 */
build.uploadNewFile = function (name) {
	if (name.length > 40) {
		name = name.substring(0, 17) + "..." + name.substring(name.length - 20, name.length);
	}

	return lychee.html(_templateObject36, name);
};

/**
 * @param {string[]} tags
 * @returns {string}
 */
build.tags = function (tags) {
	var html = "";
	var editable = album.isUploadable();

	// Search is enabled if logged in (not publicMode) or public search is enabled
	var searchable = !lychee.publicMode || lychee.public_search;

	// build class_string for tag
	var a_class = searchable ? "tag search" : "tag";

	if (tags.length !== 0) {
		tags.forEach(function (tag, index) {
			if (editable) {
				html += lychee.html(_templateObject37, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject38, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject39, lychee.locale["NO_TAGS"]);
	}

	return html;
};

/**
 * @param {User} user
 * @returns {string}
 */
build.user = function (user) {
	return lychee.html(_templateObject40, user.id, user.id, user.username, user.id, user.id);
};

/**
 * @param {WebAuthnCredential} credential
 * @returns {string}
 */
build.u2f = function (credential) {
	return lychee.html(_templateObject41, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
};

/**
 * @description This module is used for the context menu.
 */

var contextMenu = {};

/**
 * @param {jQuery.Event} e
 */
contextMenu.add = function (e) {
	var items = [{ title: build.iconic("image") + lychee.locale["UPLOAD_PHOTO"], fn: function fn() {
			return $("#upload_files").click();
		} }, {}, { title: build.iconic("link-intact") + lychee.locale["IMPORT_LINK"], fn: function fn() {
			return upload.start.url();
		} }, { title: build.iconic("dropbox", "ionicons") + lychee.locale["IMPORT_DROPBOX"], fn: function fn() {
			return upload.start.dropbox();
		} }, { title: build.iconic("terminal") + lychee.locale["IMPORT_SERVER"], fn: function fn() {
			return upload.start.server();
		} }, {}, { title: build.iconic("folder") + lychee.locale["NEW_ALBUM"], fn: function fn() {
			return album.add();
		} }];

	if (visible.albums()) {
		items.push({ title: build.iconic("tags") + lychee.locale["NEW_TAG_ALBUM"], fn: function fn() {
				return album.addByTags();
			} });
	} else if (album.isSmartID(album.getID()) || album.isSearchID(album.getID())) {
		// remove Import and New album if smart album or search results
		items.splice(1);
	}

	if (!lychee.admin) {
		// remove import from dropbox and server if not admin
		items.splice(3, 2);
	} else if (!lychee.dropboxKey || lychee.dropboxKey === "") {
		// remove import from dropbox if dropboxKey not set
		items.splice(3, 1);
	}

	if (visible.album() && album.isUploadable()) {
		// prepend further buttons if menu bar is reduced on small screens
		var albumID = album.getID();
		if (album.isTagAlbum()) {
			// For tag albums the context menu is normally not used.
			items = [];
		}
		if (albumID.length === 24 || albumID === SmartAlbumID.UNSORTED) {
			if (albumID !== SmartAlbumID.UNSORTED) {
				var button_visibility_album = $("#button_visibility_album");
				if (button_visibility_album && button_visibility_album.css("display") === "none") {
					items.unshift({
						title: build.iconic("eye") + lychee.locale["VISIBILITY_ALBUM"],
						visible: lychee.enable_button_visibility,
						fn: function fn() {
							return album.setProtectionPolicy(albumID);
						}
					});
				}
			}
			var button_trash_album = $("#button_trash_album");
			if (button_trash_album && button_trash_album.css("display") === "none") {
				items.unshift({
					title: build.iconic("trash") + lychee.locale["DELETE_ALBUM"],
					visible: lychee.enable_button_trash,
					fn: function fn() {
						return album.delete([albumID]);
					}
				});
			}
			if (albumID !== SmartAlbumID.UNSORTED) {
				if (!album.isTagAlbum()) {
					var button_move_album = $("#button_move_album");
					if (button_move_album && button_move_album.css("display") === "none") {
						items.unshift({
							title: build.iconic("folder") + lychee.locale["MOVE_ALBUM"],
							visible: lychee.enable_button_move,
							fn: function fn(event) {
								return contextMenu.move([albumID], event, album.setAlbum, "ROOT", album.getParentID() !== null);
							}
						});
					}
				}
				var button_nsfw_album = $("#button_nsfw_album");
				if (button_nsfw_album && button_nsfw_album.css("display") === "none") {
					items.unshift({
						title: build.iconic("warning") + lychee.locale["ALBUM_MARK_NSFW"],
						visible: true,
						fn: function fn() {
							return album.toggleNSFW();
						}
					});
				}
			}
			if (!album.isSmartID(albumID) && lychee.map_display) {
				// display track add button if it's a regular album
				items.push({}, { title: build.iconic("location") + lychee.locale["UPLOAD_TRACK"], fn: function fn() {
						return $("#upload_track_file").click();
					} });
				if (album.json.track_url) {
					items.push({ title: build.iconic("trash") + lychee.locale["DELETE_TRACK"], fn: album.deleteTrack });
				}
			}
		}
	}

	basicContext.show(items, e.originalEvent);

	upload.notify();
};

/**
 * @param {string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.album = function (albumID, e) {
	// Notice for 'Merge':
	// fn must call basicContext.close() first,
	// in order to keep the selection

	if (album.isSmartID(albumID) || album.isSearchID(albumID)) return;

	// Show merge-item when there's more than one album
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return album.setTitle([albumID]);
		} }, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE"],
		visible: showMerge,
		fn: function fn() {
			basicContext.close();
			contextMenu.move([albumID], e, album.merge, "ROOT", false);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE"],
		visible: true,
		fn: function fn() {
			basicContext.close();
			contextMenu.move([albumID], e, album.setAlbum, "ROOT");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE"], fn: function fn() {
			return album.delete([albumID]);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], fn: function fn() {
			return album.getArchive([albumID]);
		} }];

	if (visible.album()) {
		// not top level
		var myalbum = album.getSubByID(albumID);
		if (myalbum.thumb.id) {
			var coverActive = myalbum.thumb.id === album.json.cover_id;
			// prepend context menu item
			items.unshift({
				title: build.iconic("folder-cover", coverActive ? "active" : "") + lychee.locale[coverActive ? "REMOVE_COVER" : "SET_COVER"],
				fn: function fn() {
					return album.toggleCover(myalbum.thumb.id);
				}
			});
		}
	}

	$('.album[data-id="' + albumID + '"]').addClass("active");

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * Handles drop event of an album onto an album and shows context menu to let the user pick the actions.
 *
 * @param {string} sourceAlbumID source album (which is being dragged)
 * @param {string} targetAlbumID target album (where it is dropped)
 * @param {DragEvent} e
 *
 * @returns {void}
 */
contextMenu.albumDrop = function (sourceAlbumID, targetAlbumID, e) {
	var items = [{
		title: build.iconic("collapse-left") + lychee.locale["MERGE"],
		fn: function fn() {
			album.merge([sourceAlbumID], targetAlbumID);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE"],
		visible: true,
		fn: function fn() {
			basicContext.close();
			album.setAlbum([sourceAlbumID], targetAlbumID);
		}
	}];

	basicContext.show(items, e, contextMenu.close);
};

/**
 * @param {string[]} albumIDs
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.albumMulti = function (albumIDs, e) {
	multiselect.stopResize();

	// Automatically merge selected albums when albumIDs contains more than one album
	// Show list of albums otherwise
	var autoMerge = albumIDs.length > 1;

	// Show merge-item when there's more than one album
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME_ALL"], fn: function fn() {
			return album.setTitle(albumIDs);
		} }, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE_ALL"],
		visible: showMerge && autoMerge,
		fn: function fn() {
			var albumID = albumIDs.shift();
			album.merge(albumIDs, albumID);
		}
	}, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE"],
		visible: showMerge && !autoMerge,
		fn: function fn() {
			basicContext.close();
			contextMenu.move(albumIDs, e, album.merge, "ROOT", false);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE_ALL"],
		visible: true,
		fn: function fn() {
			basicContext.close();
			contextMenu.move(albumIDs, e, album.setAlbum, "ROOT");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE_ALL"], fn: function fn() {
			return album.delete(albumIDs);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD_ALL"], fn: function fn() {
			return album.getArchive(albumIDs);
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @callback ContextMenuActionCB
 *
 * @param {(Photo|Album)} entity
 */

/**
 * @callback ContextMenuEventCB
 *
 * @param {jQuery.Event} [e]
 * @returns {void}
 */

/**
 * @param {(Photo|Album)[]} lists
 * @param {string[]} exclude list of IDs to exclude
 * @param {ContextMenuActionCB} action
 * @param {?string} [parentID=null] parentID
 * @param {number} [layer=0]
 *
 * @returns {{title: string, disabled: boolean, fn: ContextMenuEventCB}[]}
 */
contextMenu.buildList = function (lists, exclude, action) {
	var parentID = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	var layer = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;

	var items = [];

	lists.forEach(function (item) {
		if ((layer !== 0 || item.parent_id) && item.parent_id !== parentID) return;

		var thumb = "img/no_cover.svg";
		if (item.thumb && item.thumb.thumb) {
			if (item.thumb.thumb === "uploads/thumb/") {
				if (item.thumb.type && item.thumb.type.indexOf("video") > -1) {
					thumb = "img/play-icon.png";
				}
			} else {
				thumb = item.thumb.thumb;
			}
		} else if (item.size_variants) {
			if (item.size_variants.thumb === null) {
				if (item.type && item.type.indexOf("video") > -1) {
					thumb = "img/play-icon.png";
				}
			} else {
				thumb = item.size_variants.thumb.url;
			}
		}

		if (!item.title) item.title = lychee.locale["UNTITLED"];

		var prefix = layer > 0 ? "&nbsp;&nbsp;".repeat(layer - 1) + "└ " : "";

		var html = lychee.html(_templateObject42, prefix, thumb, item.title);

		items.push({
			title: html,
			disabled: exclude.findIndex(function (id) {
				return id === item.id;
			}) !== -1,
			fn: function fn() {
				return action(item);
			}
		});

		if (item.albums && item.albums.length > 0) {
			items = items.concat(contextMenu.buildList(item.albums, exclude, action, item.id, layer + 1));
		} else {
			// Fallback for flat tree representation.  Should not be
			// needed anymore but shouldn't hurt either.
			items = items.concat(contextMenu.buildList(lists, exclude, action, item.id, layer + 1));
		}
	});

	return items;
};

/**
 * @param {?string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.albumTitle = function (albumID, e) {
	api.post("Albums::tree", {}, function (data) {
		var items = [];

		items = items.concat({ title: lychee.locale["ROOT"], disabled: albumID === null, fn: function fn() {
				return lychee.goto();
			} });

		if (data.albums && data.albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.albums, albumID !== null ? [albumID] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (data.shared_albums && data.shared_albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.shared_albums, albumID !== null ? [albumID] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (albumID !== null && !album.isSmartID(albumID) && !album.isSearchID(albumID) && album.isUploadable()) {
			if (items.length > 0) {
				items.unshift({});
			}

			items.unshift({ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
					return album.setTitle([albumID]);
				} });
		}

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.photo = function (photoID, e) {
	var coverActive = photoID === album.json.cover_id;

	var isPhotoStarred = album.getByID(photoID).is_starred;

	var items = [{
		title: build.iconic("star") + (isPhotoStarred ? lychee.locale["UNSTAR"] : lychee.locale["STAR"]),
		fn: function fn() {
			return _photo3.setStar([photoID], !isPhotoStarred);
		}
	}, { title: build.iconic("tag") + lychee.locale["TAGS"], fn: function fn() {
			return _photo3.editTags([photoID]);
		} },
	// for future work, use a list of all the ancestors.
	{
		title: build.iconic("folder-cover", coverActive ? "active" : "") + lychee.locale[coverActive ? "REMOVE_COVER" : "SET_COVER"],
		fn: function fn() {
			return album.toggleCover(photoID);
		}
	}, {}, { title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo3.setTitle([photoID]);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo3.copyTo);
		}
	},
	// Notice for 'Move':
	// fn must call basicContext.close() first,
	// in order to keep the selection
	{
		title: build.iconic("folder") + lychee.locale["MOVE"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo3.setAlbum);
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE"], fn: function fn() {
			return _photo3.delete([photoID]);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], fn: function fn() {
			return _photo3.getArchive([photoID]);
		} }];
	if (album.isSmartID(album.getID()) || album.isSearchID(album.getID) || album.isTagAlbum()) {
		// Cover setting not supported for smart or tag albums and search results.
		items.splice(2, 1);
	}

	$('.photo[data-id="' + photoID + '"]').addClass("active");

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string} photoID
 * @param {string} albumID
 * @param {DragEvent} e
 *
 * @returns {void}
 */
contextMenu.photoDrop = function (photoID, albumID, e) {
	var items = [{
		title: build.iconic("folder") + lychee.locale["MOVE"],
		fn: function fn() {
			_photo3.setAlbum([photoID], albumID);
		}
	}];

	$('.photo[data-id="' + photoID + '"]').addClass("active");

	basicContext.show(items, e, contextMenu.close);
};

/**
 * @param {string[]} photoIDs
 * @param {jQuery.Event} e
 */
contextMenu.photoMulti = function (photoIDs, e) {
	multiselect.stopResize();

	var arePhotosStarred = false;
	var arePhotosNotStarred = false;
	photoIDs.forEach(function (id) {
		if (album.getByID(id).is_starred) {
			arePhotosStarred = true;
		} else {
			arePhotosNotStarred = true;
		}
	});

	var items = [
	// Only show the star/unstar menu item when the selected photos are
	// consistently either all starred or all not starred.
	{
		title: build.iconic("star") + (arePhotosNotStarred ? lychee.locale["STAR_ALL"] : lychee.locale["UNSTAR_ALL"]),
		visible: !(arePhotosStarred && arePhotosNotStarred),
		fn: function fn() {
			return _photo3.setStar(photoIDs, arePhotosNotStarred);
		}
	}, { title: build.iconic("tag") + lychee.locale["TAGS_ALL"], fn: function fn() {
			return _photo3.editTags(photoIDs);
		} }, {}, { title: build.iconic("pencil") + lychee.locale["RENAME_ALL"], fn: function fn() {
			return _photo3.setTitle(photoIDs);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_ALL_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo3.copyTo);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE_ALL"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo3.setAlbum);
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE_ALL"], fn: function fn() {
			return _photo3.delete(photoIDs);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD_ALL"], fn: function fn() {
			return _photo3.getArchive(photoIDs, "FULL");
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string} albumID
 * @param {string} photoID
 * @param {jQuery.Event} e
 */
contextMenu.photoTitle = function (albumID, photoID, e) {
	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo3.setTitle([photoID]);
		} }];

	// Note: We can also have a photo without its parent album being loaded
	// if the photo is a public photo within a private album
	var photos = album.json ? album.json.photos : [];

	if (photos.length > 0) {
		items.push({});

		items = items.concat(contextMenu.buildList(photos, [photoID], function (a) {
			return lychee.goto(albumID + "/" + a.id);
		}));
	}

	if (!album.isUploadable()) {
		// Remove Rename and the spacer.
		items.splice(0, 2);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 */
contextMenu.photoMore = function (photoID, e) {
	// Show download-item when
	// a) We are allowed to upload to the album
	// b) the photo is explicitly marked as downloadable (v4-only)
	// c) or, the album is explicitly marked as downloadable

	var showDownload = album.isUploadable() || _photo3.json.is_downloadable;
	var showFull = !!(_photo3.json.size_variants.original.url && _photo3.json.size_variants.original.url !== "");

	var items = [{ title: build.iconic("fullscreen-enter") + lychee.locale["FULL_PHOTO"], visible: showFull, fn: function fn() {
			return window.open(_photo3.getDirectLink());
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], visible: showDownload, fn: function fn() {
			return _photo3.getArchive([photoID]);
		} }];
	if (album.isUploadable()) {
		// prepend further buttons if menu bar is reduced on small screens
		var button_visibility = $("#button_visibility");
		if (button_visibility && button_visibility.css("display") === "none") {
			items.unshift({
				title: build.iconic("eye") + lychee.locale["VISIBILITY_PHOTO"],
				visible: lychee.enable_button_visibility,
				fn: function fn() {
					return _photo3.setProtectionPolicy(_photo3.getID());
				}
			});
		}
		var button_trash = $("#button_trash");
		if (button_trash && button_trash.css("display") === "none") {
			items.unshift({
				title: build.iconic("trash") + lychee.locale["DELETE"],
				visible: lychee.enable_button_trash,
				fn: function fn() {
					return _photo3.delete([_photo3.getID()]);
				}
			});
		}
		var button_move = $("#button_move");
		if (button_move && button_move.css("display") === "none") {
			items.unshift({
				title: build.iconic("folder") + lychee.locale["MOVE"],
				visible: lychee.enable_button_move,
				fn: function fn(event) {
					return contextMenu.move([_photo3.getID()], event, _photo3.setAlbum);
				}
			});
		}
		/* The condition below is copied from view.photo.header() */
		if (!(_photo3.json.type && (_photo3.json.type.indexOf("video") === 0 || _photo3.json.type === "raw") || _photo3.json.live_photo_url !== "" && _photo3.json.live_photo_url !== null)) {
			var button_rotate_cwise = $("#button_rotate_cwise");
			if (button_rotate_cwise && button_rotate_cwise.css("display") === "none") {
				items.unshift({
					title: build.iconic("clockwise") + lychee.locale["PHOTO_EDIT_ROTATECWISE"],
					visible: lychee.enable_button_move,
					fn: function fn() {
						return photoeditor.rotate(_photo3.getID(), 1);
					}
				});
			}
			var button_rotate_ccwise = $("#button_rotate_ccwise");
			if (button_rotate_ccwise && button_rotate_ccwise.css("display") === "none") {
				items.unshift({
					title: build.iconic("counterclockwise") + lychee.locale["PHOTO_EDIT_ROTATECCWISE"],
					visible: lychee.enable_button_move,
					fn: function fn() {
						return photoeditor.rotate(_photo3.getID(), -1);
					}
				});
			}
		}
	}

	basicContext.show(items, e.originalEvent);
};

/**
 * @param {Album[]} albums
 * @param {string} albumID
 *
 * @returns {string[]}
 */
contextMenu.getSubIDs = function (albums, albumID) {
	var ids = [albumID];

	albums.forEach(function (album) {
		if (album.parent_id === albumID) {
			ids = ids.concat(contextMenu.getSubIDs(albums, album.id));
		}

		if (album.albums && album.albums.length > 0) {
			ids = ids.concat(contextMenu.getSubIDs(album.albums, albumID));
		}
	});

	return ids;
};

/**
 * @callback TargetAlbumSelectedCB
 *
 * Called by {@link contextMenu.move} after the user has selected a target ID.
 * In most cases, {@link album.setAlbum} or {@link photo.setAlbum} are
 * directly used as the callback.
 * This design decision is the only reason, why this callback gets more
 * parameters than the selected target ID.
 * The parameter signature of this callback matches {@link album.setAlbum}.
 *
 * However, the callback should actually enclose all other parameters it
 * needs and only receive the target ID.
 *
 * TODO: Re-factor callbacks.
 *
 * @param {string[]} IDs      the source IDs
 * @param {?string} targetID  the ID of the target album
 * @param {boolean} [confirm] indicates whether the callback shall show a
 *                            confirmation dialog to the user for whatever to
 *                            callback is going to do
 * @returns {void}
 */

/**
 * Shows the context menu with the album tree and allows the user to select a target album.
 *
 * **ATTENTION:** The name `move` of this method is very badly chosen.
 * The method does not move anything, but only shows the menu and reports
 * the selected album.
 * In particular, the method is used by any operation which needs a target
 * album (i.e. merge, copy-to, etc.)
 *
 * TODO: Find a better name for this function.
 *
 * The method calls the provided callback after the user has selected a
 * target album and passes the ID of the target album together with the
 * source `IDs` and the event `e` to the callback.
 *
 * TODO: Actually the callbacks should enclose all additional parameters (e.g., `IDs`) they need. Refactor the callbacks.
 *
 * The name of the root node in the context menu may be provided by the caller
 * depending on the use-case.
 * Keep in mind, that the root album is not visible to the user during normal
 * browsing.
 * Photos on the root level are stashed away into a virtual album called
 * "Unsorted".
 * Albums on the root level are shown as siblings, but the root node itself
 * is invisible.
 * So the user actually sees a forest.
 * Hence, the root node should be named differently to meet the user's
 * expectations.
 * When the user moves/copies/merges photos, then the root node should be
 * called "Unsorted".
 * When the user moves/copies/merges albums, then the root node should be
 * called "Root".
 *
 * @param {string[]} IDs - IDs of source objects (either album or photo IDs)
 * @param {jQuery.Event} e - Some (?) event
 * @param {TargetAlbumSelectedCB} callback - to be called after the user has selected a target ID
 * @param {string} [kind=UNSORTED] - Name of root album; either "UNSORTED" or "ROOT"
 * @param {boolean} [display_root=true] - Whether the root (aka unsorted) album shall be shown
 */
contextMenu.move = function (IDs, e, callback) {
	var kind = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : "UNSORTED";
	var display_root = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;

	var items = [];

	api.post("Albums::tree", {}, function (data) {
		var addItems = function addItems(albums) {
			// Disable all children
			// It's not possible to move us into them
			var i = void 0,
			    s = void 0;
			var exclude = [];
			for (i = 0; i < IDs.length; i++) {
				var sub = contextMenu.getSubIDs(albums, IDs[i]);
				for (s = 0; s < sub.length; s++) {
					exclude.push(sub[s]);
				}
			}
			if (visible.album()) {
				// For merging, don't exclude the parent.
				// For photo copy, don't exclude the current album.
				if (callback !== album.merge && callback !== _photo3.copyTo) {
					exclude.push(album.getID());
				}
				if (IDs.length === 1 && IDs[0] === album.getID() && album.getParentID() && callback === album.setAlbum) {
					// If moving the current album, exclude its parent.
					exclude.push(album.getParentID());
				}
			} else if (visible.photo()) {
				exclude.push(_photo3.json.album_id);
			}
			items = items.concat(contextMenu.buildList(albums, exclude.concat(IDs), function (a) {
				return callback(IDs, a.id);
			}));
		};

		if (data.albums && data.albums.length > 0) {
			// items = items.concat(contextMenu.buildList(data.albums, [ album.getID() ], (a) => callback(IDs, a.id))); //photo.setAlbum

			addItems(data.albums);
		}

		if (data.shared_albums && data.shared_albums.length > 0 && lychee.admin) {
			items = items.concat({});
			addItems(data.shared_albums);
		}

		// Show Unsorted when unsorted is not the current album
		if (display_root && album.getID() !== "unsorted" && !visible.albums()) {
			items.unshift({});
			items.unshift({ title: lychee.locale[kind], fn: function fn() {
					return callback(IDs, null);
				} });
		}

		// Don't allow to move the current album to a newly created subalbum
		// (creating a cycle).
		if (IDs.length !== 1 || IDs[0] !== (album.json ? album.json.id : null) || callback !== album.setAlbum) {
			items.unshift({});
			items.unshift({ title: lychee.locale["NEW_ALBUM"], fn: function fn() {
					return album.add(IDs, callback);
				} });
		}

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.sharePhoto = function (photoID, e) {
	if (!_photo3.json.is_share_button_visible) {
		return;
	}

	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return _photo3.share(photoID, "twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return _photo3.share(photoID, "facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return _photo3.share(photoID, "mail");
		} }, { title: build.iconic("dropbox", iconClass) + "Dropbox", visible: lychee.admin === true, fn: function fn() {
			return _photo3.share(photoID, "dropbox");
		} }, { title: build.iconic("link-intact") + lychee.locale["DIRECT_LINKS"], fn: function fn() {
			return _photo3.showDirectLinks(photoID);
		} }, { title: build.iconic("grid-two-up") + lychee.locale["QR_CODE"], fn: function fn() {
			return _photo3.qrCode(photoID);
		} }];

	basicContext.show(items, e.originalEvent);
};

/**
 * @param {string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.shareAlbum = function (albumID, e) {
	if (!album.json.is_share_button_visible) {
		return;
	}

	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return album.share("twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return album.share("facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return album.share("mail");
		} }, {
		title: build.iconic("link-intact") + lychee.locale["DIRECT_LINK"],
		fn: function fn() {
			var url = lychee.getBaseUrl() + "r/" + albumID;
			if (album.json.has_password) {
				// Copy the url with prefilled password param
				url += "?password=";
			}
			navigator.clipboard.writeText(url).then(function () {
				return loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
			});
		}
	}, { title: build.iconic("grid-two-up") + lychee.locale["QR_CODE"], fn: function fn() {
			return album.qrCode();
		} }];

	basicContext.show(items, e.originalEvent);
};

/**
 * @returns {void}
 */
contextMenu.close = function () {
	if (!visible.contextMenu()) return;

	basicContext.close();

	multiselect.clearSelection();
	if (visible.multiselect()) {
		multiselect.close();
	}
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
contextMenu.config = function (e) {
	var items = [{ title: build.iconic("cog") + lychee.locale["SETTINGS"], fn: settings.open }];
	if (lychee.new_photos_notification) {
		items.push({ title: build.iconic("bell") + lychee.locale["NOTIFICATIONS"], fn: notifications.load });
	}
	if (lychee.admin) {
		items.push({ title: build.iconic("person") + lychee.locale["USERS"], fn: users.list });
	}
	items.push({ title: build.iconic("key") + lychee.locale["U2F"], fn: u2f.list });
	items.push({ title: build.iconic("cloud") + lychee.locale["SHARING"], fn: sharing.list });
	if (lychee.admin) {
		items.push({
			title: build.iconic("align-left") + lychee.locale["LOGS"],
			fn: function fn() {
				view.logs.init();
			}
		});
		items.push({
			title: build.iconic("wrench") + lychee.locale["DIAGNOSTICS"],
			fn: function fn() {
				view.diagnostics.init();
			}
		});
		if (lychee.update_available) {
			items.push({ title: build.iconic("timer") + lychee.locale["UPDATE_AVAILABLE"], fn: view.update.init });
		}
	}
	items.push({ title: build.iconic("info") + lychee.locale["ABOUT_LYCHEE"], fn: lychee.aboutDialog });
	items.push({ title: build.iconic("account-logout") + lychee.locale["SIGN_OUT"], fn: lychee.logout });

	basicContext.show(items, e.originalEvent);
};

/**
 * @description This module takes care of the header.
 */

/**
 * @namespace
 * @property {jQuery} _dom
 */
var header = {
	_dom: $(".header")
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
header.dom = function (selector) {
	if (selector == null || selector === "") return header._dom;
	return header._dom.find(selector);
};

/**
 * @returns {void}
 */
header.bind = function () {
	// Event Name
	var eventName = lychee.getEventName();

	header.dom(".header__title").on(eventName, function (e) {
		if ($(this).hasClass("header__title--editable") === false) return false;

		if (lychee.enable_contextmenu_header === false) return false;

		if (visible.photo()) contextMenu.photoTitle(album.getID(), _photo3.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function () {
		_photo3.setProtectionPolicy(_photo3.getID());
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(_photo3.getID(), e);
	});

	header.dom("#button_visibility_album").on(eventName, function () {
		album.setProtectionPolicy(album.getID());
	});

	header.dom("#button_sharing_album_users").on(eventName, function () {
		album.shareUsers(album.getID());
	});

	header.dom("#button_share_album").on(eventName, function (e) {
		contextMenu.shareAlbum(album.getID(), e);
	});

	header.dom("#button_signin").on(eventName, lychee.loginDialog);
	header.dom("#button_settings").on(eventName, function (e) {
		if ($(".leftMenu").css("display") === "none") {
			// left menu disabled on small screens
			contextMenu.config(e);
		} else {
			// standard left menu
			leftMenu.open();
		}
	});
	header.dom("#button_close_config").on(eventName, function () {
		tabindex.makeFocusable(header.dom());
		tabindex.makeFocusable(lychee.content);
		tabindex.makeUnfocusable(leftMenu._dom);
		multiselect.bind();
		lychee.load();
	});
	header.dom("#button_info_album").on(eventName, function () {
		_sidebar.toggle(true);
	});
	header.dom("#button_info").on(eventName, function () {
		_sidebar.toggle(true);
	});
	header.dom(".button--map-albums").on(eventName, function () {
		lychee.gotoMap();
	});
	header.dom("#button_map_album").on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom("#button_map").on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom(".button_add").on(eventName, contextMenu.add);
	header.dom("#button_more").on(eventName, function (e) {
		contextMenu.photoMore(_photo3.getID(), e);
	});
	header.dom("#button_move_album").on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParentID() != null);
	});
	header.dom("#button_nsfw_album").on(eventName, function () {
		album.toggleNSFW();
	});
	header.dom("#button_move").on(eventName, function (e) {
		contextMenu.move([_photo3.getID()], e, _photo3.setAlbum);
	});
	header.dom(".header__hostedwith").on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom("#button_trash_album").on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom("#button_trash").on(eventName, function () {
		_photo3.delete([_photo3.getID()]);
	});
	header.dom("#button_archive").on(eventName, function () {
		album.getArchive([album.getID()]);
	});
	header.dom("#button_star").on(eventName, function () {
		_photo3.toggleStar();
	});
	header.dom("#button_rotate_ccwise").on(eventName, function () {
		photoeditor.rotate(_photo3.getID(), -1);
	});
	header.dom("#button_rotate_cwise").on(eventName, function () {
		photoeditor.rotate(_photo3.getID(), 1);
	});
	header.dom("#button_back_home").on(eventName, function () {
		if (!album.json.parent_id) {
			lychee.goto();
		} else {
			lychee.goto(album.getParentID());
		}
	});
	header.dom("#button_back").on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom("#button_back_map").on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom("#button_fs_album_enter,#button_fs_enter").on(eventName, lychee.fullscreenEnter);
	header.dom("#button_fs_album_exit,#button_fs_exit").on(eventName, lychee.fullscreenExit).hide();

	header.dom(".header__search").on("keyup click", function () {
		if ($(this).val().length > 0) {
			lychee.goto("search/" + encodeURIComponent($(this).val()));
		} else if (search.json !== null) {
			search.reset();
		}
	});
	header.dom(".header__clear").on(eventName, function () {
		search.reset();
	});

	header.bind_back();
};

/**
 * @returns {void}
 */
header.bind_back = function () {
	// Event Name
	var eventName = lychee.getEventName();

	header.dom(".header__title").on(eventName, function () {
		if (lychee.landing_page_enable && visible.albums()) {
			window.location.href = ".";
		} else {
			return false;
		}
	});
};

/**
 * @returns {void}
 */
header.show = function () {
	lychee.imageview.removeClass("full");
	header.dom().removeClass("header--hidden");

	tabindex.restoreSettings(header.dom());

	_photo3.updateSizeLivePhotoDuringAnimation();
};

/**
 * @returns {void}
 */
header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (!_photo3.isLivePhotoPlaying()) header.hide();
};

/**
 * @returns {void}
 */
header.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {
		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass("full");
		header.dom().addClass("header--hidden");

		_photo3.updateSizeLivePhotoDuringAnimation();
	}
};

/**
 * @param {string} title
 * @returns {void}
 */
header.setTitle = function (title) {
	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject43, title, build.iconic("caret-bottom"));

	$title.html(html);
};

/**
 *
 * @param {string} mode either one out of `"public"`, `"albums"`, `"album"`,
 *                      `"photo"`, `"map"` or `"config"`
 * @returns {void}
 */
header.setMode = function (mode) {
	if (mode === "albums" && lychee.publicMode === true) mode = "public";

	switch (mode) {
		case "public":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--public").addClass("header__toolbar--visible");
			tabindex.makeFocusable(header.dom(".header__toolbar--public"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			if (lychee.public_search) {
				var e = $(".header__search, .header__clear", ".header__toolbar--public");
				e.show();
				tabindex.makeFocusable(e);
			} else {
				var _e2 = $(".header__search, .header__clear", ".header__toolbar--public");
				_e2.hide();
				tabindex.makeUnfocusable(_e2);
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				var _e3 = $(".button--map-albums", ".header__toolbar--public");
				_e3.show();
				tabindex.makeFocusable(_e3);
			} else {
				var _e4 = $(".button--map-albums", ".header__toolbar--public");
				_e4.hide();
				tabindex.makeUnfocusable(_e4);
			}

			// Set focus on login button
			if (lychee.active_focus_on_page_load) {
				$("#button_signin").focus();
			}
			return;

		case "albums":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--albums").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--albums"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			// If map is disabled, we should hide the icon
			if (lychee.map_display) {
				var _e5 = $(".button--map-albums", ".header__toolbar--albums");
				_e5.show();
				tabindex.makeFocusable(_e5);
			} else {
				var _e6 = $(".button--map-albums", ".header__toolbar--albums");
				_e6.hide();
				tabindex.makeUnfocusable(_e6);
			}

			if (lychee.enable_button_add && lychee.may_upload) {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.show();
				tabindex.makeFocusable(_e7);
			} else {
				var _e8 = $(".button_add", ".header__toolbar--albums");
				_e8.remove();
			}

			return;

		case "album":
			var albumID = album.getID();

			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--album").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--album"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos.length === 0 && album.json.albums && album.json.albums.length === 0 || !album.isUploadable() && !album.json.is_downloadable) {
				var _e9 = $("#button_archive");
				_e9.hide();
				tabindex.makeUnfocusable(_e9);
			} else {
				var _e10 = $("#button_archive");
				_e10.show();
				tabindex.makeFocusable(_e10);
			}

			if (album.json && album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
				var _e11 = $("#button_share_album");
				_e11.hide();
				tabindex.makeUnfocusable(_e11);
			} else {
				var _e12 = $("#button_share_album");
				_e12.show();
				tabindex.makeFocusable(_e12);
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e13 = $("#button_map_album");
				_e13.show();
				tabindex.makeFocusable(_e13);
			} else {
				var _e14 = $("#button_map_album");
				_e14.hide();
				tabindex.makeUnfocusable(_e14);
			}

			if (albumID === SmartAlbumID.STARRED || albumID === SmartAlbumID.PUBLIC || albumID === SmartAlbumID.RECENT) {
				$("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				if (album.isUploadable()) {
					$(".button_add, .header__divider", ".header__toolbar--album").show();
					tabindex.makeFocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				} else {
					$(".button_add, .header__divider", ".header__toolbar--album").hide();
					tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				}
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (albumID === SmartAlbumID.UNSORTED) {
				$("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				$("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album").show();
				tabindex.makeFocusable($("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album"));
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (album.isTagAlbum()) {
				$("#button_info_album").show();
				if (_sidebar.keepSidebarVisible() && !visible.sidebar()) _sidebar.toggle(false);
				$("#button_move_album").hide();
				$(".button_add, .header__divider", ".header__toolbar--album").hide();
				tabindex.makeFocusable($("#button_info_album"));
				tabindex.makeUnfocusable($("#button_move_album"));
				tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				if (album.isUploadable()) {
					$("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album").show();
					tabindex.makeFocusable($("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album"));
					if ($("#button_visibility_album").is(":hidden")) {
						// This can happen with narrow screens.  In that
						// case we re-enable the add button which will
						// contain the overflow items.
						$(".button_add, .header__divider", ".header__toolbar--album").show();
						tabindex.makeFocusable($(".button_add, .header__divider", ".header__toolbar--album"));
					}
				} else {
					$("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album").hide();
					tabindex.makeUnfocusable($("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album"));
				}
			} else {
				$("#button_info_album").show();
				if (_sidebar.keepSidebarVisible() && !visible.sidebar()) _sidebar.toggle(false);
				tabindex.makeFocusable($("#button_info_album"));
				if (album.isUploadable()) {
					$("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album").show();
					tabindex.makeFocusable($("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album"));
				} else {
					$("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album").hide();
					tabindex.makeUnfocusable($("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album"));
				}
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e15 = $("#button_visibility_album", "#button_sharing_album_users", ".header__toolbar--album");
				_e15.remove();
			}
			if (!lychee.enable_button_share) {
				var _e16 = $("#button_share_album", ".header__toolbar--album");
				_e16.remove();
			}
			if (!lychee.enable_button_archive) {
				var _e17 = $("#button_archive", ".header__toolbar--album");
				_e17.remove();
			}
			if (!lychee.enable_button_move) {
				var _e18 = $("#button_move_album", ".header__toolbar--album");
				_e18.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e19 = $("#button_trash_album", ".header__toolbar--album");
				_e19.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e20 = $("#button_fs_album_enter", ".header__toolbar--album");
				_e20.remove();
			}
			if (!lychee.enable_button_add) {
				var _e21 = $(".button_add", ".header__toolbar--album");
				_e21.remove();
			}

			return;

		case "photo":
			header.dom().addClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--photo").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--photo"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map, .header__toolbar--config"));
			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e22 = $("#button_map");
				_e22.show();
				tabindex.makeFocusable(_e22);
			} else {
				var _e23 = $("#button_map");
				_e23.hide();
				tabindex.makeUnfocusable(_e23);
			}

			if (album.isUploadable()) {
				var _e24 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e24.show();
				tabindex.makeFocusable(_e24);
			} else {
				var _e25 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e25.hide();
				tabindex.makeUnfocusable(_e25);
			}

			if (_photo3.json && _photo3.json.hasOwnProperty("is_share_button_visible") && !_photo3.json.is_share_button_visible) {
				var _e26 = $("#button_share");
				_e26.hide();
				tabindex.makeUnfocusable(_e26);
			} else {
				var _e27 = $("#button_share");
				_e27.show();
				tabindex.makeFocusable(_e27);
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$("#button_more").show();
			tabindex.makeFocusable($("#button_more"));
			if (!(album.isUploadable() || (_photo3.json.hasOwnProperty("is_downloadable") ? _photo3.json.is_downloadable : album.json && album.json.is_downloadable)) && !(_photo3.json.size_variants.original.url && _photo3.json.size_variants.original.url !== "")) {
				var _e28 = $("#button_more");
				_e28.hide();
				tabindex.makeUnfocusable(_e28);
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e29 = $("#button_visibility", ".header__toolbar--photo");
				_e29.remove();
			}
			if (!lychee.enable_button_share) {
				var _e30 = $("#button_share", ".header__toolbar--photo");
				_e30.remove();
			}
			if (!lychee.enable_button_move) {
				var _e31 = $("#button_move", ".header__toolbar--photo");
				_e31.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e32 = $("#button_trash", ".header__toolbar--photo");
				_e32.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e33 = $("#button_fs_enter", ".header__toolbar--photo");
				_e33.remove();
			}
			if (!lychee.enable_button_more) {
				var _e34 = $("#button_more", ".header__toolbar--photo");
				_e34.remove();
			}
			if (!lychee.enable_button_rotate) {
				var _e35 = $("#button_rotate_cwise", ".header__toolbar--photo");
				_e35.remove();

				_e35 = $("#button_rotate_ccwise", ".header__toolbar--photo");
				_e35.remove();
			}
			return;
		case "map":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--map").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--map"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config"));
			return;
		case "config":
			header.dom().addClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--config").addClass("header__toolbar--visible");
			return;
	}
};

/**
 * Note that the pull-down menu is now enabled not only for editable
 * items but for all of public/albums/album/photo views, so 'editable' is a
 * bit of a misnomer at this point...
 *
 * @param {boolean} editable
 * @returns {void}
 */
header.setEditable = function (editable) {
	var $title = header.dom(".header__title");

	if (editable) $title.addClass("header__title--editable");else $title.removeClass("header__title--editable");
};

/**
 * @description This module is used for bindings.
 */

$(document).ready(function () {
	$("#sensitive_warning").hide();

	// Event Name
	var eventName = lychee.getEventName();

	// Set API error handler
	api.onError = lychee.handleAPIError;

	$("html").css("visibility", "visible");

	// Multiselect
	multiselect.bind();

	// Header
	header.bind();

	// Image View
	lychee.imageview.on(eventName, ".arrow_wrapper--previous", function () {
		return _photo3.previous(false);
	}).on(eventName, ".arrow_wrapper--next", function () {
		return _photo3.next(false);
	}).on(eventName, "img, #livephoto", function () {
		return _photo3.cycle_display_overlay();
	});

	// Keyboard
	Mousetrap.addKeycodes({
		18: "ContextMenu",
		179: "play_pause",
		227: "rewind",
		228: "forward"
	});

	Mousetrap.bind(["l"], function () {
		lychee.loginDialog();
		return false;
	}).bind(["k"], function () {
		u2f.login();
		return false;
	}).bind(["left"], function () {
		if (visible.photo() && (!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			$("#imageview a#previous").click();
			return false;
		}
		return true;
	}).bind(["right"], function () {
		if (visible.photo() && (!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			$("#imageview a#next").click();
			return false;
		}
		return true;
	}).bind(["u"], function () {
		if (!visible.photo() && album.isUploadable()) {
			$("#upload_files").click();
			return false;
		}
	}).bind(["n"], function () {
		if (!visible.photo() && album.isUploadable()) {
			album.add();
			return false;
		}
	}).bind(["s"], function () {
		if (visible.photo() && album.isUploadable()) {
			header.dom("#button_star").click();
			return false;
		} else if (visible.albums()) {
			header.dom(".header__search").focus();
			return false;
		}
	}).bind(["r"], function () {
		if (album.isUploadable()) {
			if (visible.album()) {
				album.setTitle([album.getID()]);
				return false;
			} else if (visible.photo()) {
				_photo3.setTitle([_photo3.getID()]);
				return false;
			}
		}
	}).bind(["h"], function () {
		lychee.nsfw_visible = !lychee.nsfw_visible;
		album.apply_nsfw_filter();
		return false;
	}).bind(["d"], function () {
		if (album.isUploadable()) {
			if (visible.photo()) {
				_photo3.setDescription(_photo3.getID());
				return false;
			} else if (visible.album()) {
				album.setDescription(album.getID());
				return false;
			}
		}
	}).bind(["t"], function () {
		if (visible.photo() && album.isUploadable()) {
			_photo3.editTags([_photo3.getID()]);
			return false;
		}
	}).bind(["i", "ContextMenu"], function () {
		if (!visible.multiselect()) {
			_sidebar.toggle(true);
			return false;
		}
	}).bind(["command+backspace", "ctrl+backspace"], function () {
		if (album.isUploadable()) {
			if (visible.photo() && basicModal.visible() === false) {
				_photo3.delete([_photo3.getID()]);
				return false;
			} else if (visible.album() && basicModal.visible() === false) {
				album.delete([album.getID()]);
				return false;
			}
		}
	}).bind(["command+a", "ctrl+a"], function () {
		if (visible.album() && basicModal.visible() === false) {
			multiselect.selectAll();
			return false;
		} else if (visible.albums() && basicModal.visible() === false) {
			multiselect.selectAll();
			return false;
		}
	}).bind(["o"], function () {
		if (visible.photo()) {
			_photo3.cycle_display_overlay();
			return false;
		}
	}).bind(["f"], function () {
		if (visible.album() || visible.photo()) {
			lychee.fullscreenToggle();
			return false;
		}
	});

	Mousetrap.bind(["play_pause"], function () {
		// If it's a video, we toggle play/pause
		var video = $("video");

		if (video.length !== 0) {
			if (video[0].paused) {
				video[0].play();
			} else {
				video[0].pause();
			}
		}
	});

	Mousetrap.bindGlobal("enter", function () {
		if (basicModal.visible() === true) {
			// check if any of the input fields is focussed
			// apply action, other do nothing
			if ($(".basicModal__content input").is(":focus")) {
				basicModal.action();
				return false;
			}
		} else if (visible.photo() && !lychee.header_auto_hide && ($("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			if (visible.header()) {
				header.hide();
			} else {
				header.show();
			}
			return false;
		}
		var clicked = false;
		$(":focus").each(function () {
			if (!$(this).is("input") && !$(this).is("textarea")) {
				$(this).click();
				clicked = true;
			}
		});
		if (clicked) {
			return false;
		}
	});

	// Prevent 'esc keyup' event to trigger 'go back in history'
	// and 'alt keyup' to show a webapp context menu for Fire TV
	Mousetrap.bindGlobal(["esc", "ContextMenu"], function () {
		return false;
	}, "keyup");

	Mousetrap.bindGlobal(["esc", "command+up"], function () {
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.config() || visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album() && !album.json.parent_id) lychee.goto();else if (visible.album()) lychee.goto(album.getParentID());else if (visible.albums() && search.json !== null) search.reset();else if (visible.mapview()) mapview.close();else if (visible.albums() && lychee.enable_close_tab_on_esc) {
			window.open("", "_self").close();
		}
		return false;
	});

	$(document)
	// Fullscreen on mobile
	.on("touchend", "#imageview #image", function () {
		// prevent triggering event 'mousemove'
		// why? this also prevents 'click' from firing which results in unexpected behaviour
		// unable to reproduce problems arising from 'mousemove' on iOS devices
		//			e.preventDefault();

		if (typeof swipe.obj === null || Math.abs(swipe.offsetX) <= 5 && Math.abs(swipe.offsetY) <= 5) {
			// Toggle header only if we're not moving to next/previous photo;
			// In this case, swipe.preventNextHeaderToggle is set to true
			if (!swipe.preventNextHeaderToggle) {
				if (visible.header()) {
					header.hide();
				} else {
					header.show();
				}
			}

			// For next 'touchend', behave again as normal and toggle header
			swipe.preventNextHeaderToggle = false;
		}
	});
	$("#imageview")
	// Swipe on mobile
	.swipe().on("swipeStart", function () {
		if (visible.photo()) swipe.start($("#imageview #image, #imageview #livephoto"));
	}).swipe().on("swipeMove",
	/** @param {jQuery.Event} e */function (e) {
		if (visible.photo()) swipe.move(e.swipe);
	}).swipe().on("swipeEnd",
	/** @param {jQuery.Event} e */function (e) {
		if (visible.photo()) swipe.stop(e.swipe, _photo3.previous, _photo3.next);
	});

	// Document
	$(document)
	// Navigation
	.on("click", ".album",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.albumClick(e, $(this));
	}).on("click", ".photo",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.photoClick(e, $(this));
	})
	// Context Menu
	.on("contextmenu", ".photo",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.photoContextMenu(e, $(this));
	}).on("contextmenu", ".album",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.albumContextMenu(e, $(this));
	})
	// Upload
	.on("change", "#upload_files", function () {
		basicModal.close();
		upload.start.local(this.files);
	}).on("change", "#upload_track_file", function () {
		basicModal.close();
		upload.uploadTrack(this.files);
	})
	// Drag and Drop upload
	.on("dragover", function () {
		return false;
	}, false).on("drop",
	/** @param {jQuery.Event} e */function (e) {
		if (album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && !visible.config() && (visible.album() || visible.albums())) {
			// Detect if dropped item is a file or a link
			if (e.originalEvent.dataTransfer.files.length > 0) {
				upload.start.local(e.originalEvent.dataTransfer.files);
			} else if (e.originalEvent.dataTransfer.getData("Text").length > 3 && !e.originalEvent.dataTransfer.getData("Text").startsWith("photo-") && // block drag and drop from albums/photos in web UI
			!e.originalEvent.dataTransfer.getData("Text").startsWith("album-")) {
				upload.start.url(e.originalEvent.dataTransfer.getData("Text"));
			}
		}

		return false;
	})
	// click on thumbnail on map
	.on("click", ".image-leaflet-popup", function () {
		mapview.goto($(this));
	})
	// Paste upload
	.on("paste",
	/** @param {jQuery.Event} e */function (e) {
		if (e.originalEvent.clipboardData.items) {
			var items = e.originalEvent.clipboardData.items;
			var filesToUpload = [];

			// Search clipboard items for an image
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1 || items[i].type.indexOf("video") !== -1) {
					filesToUpload.push(items[i].getAsFile());
				}
			}

			// We perform the check so deep because we don't want to
			// prevent the paste from working in text input fields, etc.
			if (filesToUpload.length > 0 && album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && !visible.config() && (visible.album() || visible.albums())) {
				upload.start.local(filesToUpload);

				return false;
			} else {
				return true;
			}
		}
	});

	// Fullscreen
	if (lychee.fullscreenAvailable()) $(document).on("fullscreenchange mozfullscreenchange webkitfullscreenchange msfullscreenchange", lychee.fullscreenUpdate);

	$("#sensitive_warning").on("click", view.album.nsfw_warning.next);

	/**
  * @param {number} scrollPos
  * @returns {void}
  */
	var rememberScrollPage = function rememberScrollPage(scrollPos) {
		if (visible.albums() && !visible.search() || visible.album()) {
			var urls = JSON.parse(localStorage.getItem("scroll"));
			if (urls == null || urls.length < 1) {
				urls = {};
			}

			var urlWindow = window.location.href;
			var urlScroll = scrollPos;

			urls[urlWindow] = urlScroll;

			if (urlScroll < 1) {
				delete urls[urlWindow];
			}

			localStorage.setItem("scroll", JSON.stringify(urls));
		}
	};

	$(window)
	// resize
	.on("resize", function () {
		if (visible.album()) view.album.content.justify(album.json ? album.json.photos : []);
		if (visible.search()) view.album.content.justify(search.json.photos);
		if (visible.photo()) view.photo.onresize();
	})
	// remember scroll positions
	.on("scroll", function () {
		var topScroll = $(window).scrollTop();
		rememberScrollPage(topScroll);
	});

	// Init
	lychee.init();
});

/**
 * @description This module is used for the context menu.
 */

/**
 * @namespace
 * @property {jQuery} _dom
 */
var leftMenu = {
	_dom: $(".leftMenu")
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
leftMenu.dom = function (selector) {
	if (selector == null || selector === "") return leftMenu._dom;
	return leftMenu._dom.find(selector);
};

/**
 * Note: on mobile we use a context menu instead; please make sure that
 * contextMenu.config is kept in sync with any changes here!
 *
 * @returns {void}
 */
leftMenu.build = function () {
	var html = lychee.html(_templateObject44, lychee.locale["CLOSE"], lychee.locale["SETTINGS"]);
	if (lychee.new_photos_notification) {
		html += lychee.html(_templateObject45, build.iconic("bell"), lychee.locale["NOTIFICATIONS"]);
	}
	html += lychee.html(_templateObject46, build.iconic("person"), lychee.locale["USERS"], build.iconic("key"), lychee.locale["U2F"], build.iconic("cloud"), lychee.locale["SHARING"]);
	html += lychee.html(_templateObject47, build.iconic("align-left"), lychee.locale["LOGS"], build.iconic("wrench"), lychee.locale["DIAGNOSTICS"], build.iconic("info"), lychee.locale["ABOUT_LYCHEE"], build.iconic("account-logout"), lychee.locale["SIGN_OUT"]);
	if (lychee.update_available) {
		html += lychee.html(_templateObject48, build.iconic("timer"), lychee.locale["UPDATE_AVAILABLE"]);
	}
	leftMenu._dom.html(html);
};

/** Set the width of the side navigation to 250px and the left margin of the page content to 250px
 *
 * @returns {void}
 */
leftMenu.open = function () {
	leftMenu._dom.addClass("leftMenu__visible");
	lychee.content.addClass("leftMenu__open");
	lychee.footer.addClass("leftMenu__open");
	header.dom(".header__title").addClass("leftMenu__open");
	loadingBar.dom().addClass("leftMenu__open");

	// Make background unfocusable
	tabindex.makeUnfocusable(header.dom());
	tabindex.makeUnfocusable(lychee.content);
	tabindex.makeFocusable(leftMenu._dom);
	$("#button_signout").focus();

	multiselect.unbind();
};

/**
 * Set the width of the side navigation to 0 and the left margin of the page content to 0
 *
 * @returns {void}
 */
leftMenu.close = function () {
	leftMenu._dom.removeClass("leftMenu__visible");
	lychee.content.removeClass("leftMenu__open");
	lychee.footer.removeClass("leftMenu__open");
	$(".content").removeClass("leftMenu__open");
	header.dom(".header__title").removeClass("leftMenu__open");
	loadingBar.dom().removeClass("leftMenu__open");

	tabindex.makeFocusable(header.dom());
	tabindex.makeFocusable(lychee.content);
	tabindex.makeUnfocusable(leftMenu._dom);

	multiselect.bind();
	lychee.load();
};

/**
 * @returns {void}
 */
leftMenu.bind = function () {
	// Event Name
	var eventName = lychee.getEventName();

	leftMenu.dom("#button_settings_close").on(eventName, leftMenu.close);
	leftMenu.dom("#text_settings_close").on(eventName, leftMenu.close);
	leftMenu.dom("#button_settings_open").on(eventName, settings.open);
	leftMenu.dom("#button_signout").on(eventName, lychee.logout);
	leftMenu.dom("#button_logs").on(eventName, leftMenu.Logs);
	leftMenu.dom("#button_diagnostics").on(eventName, leftMenu.Diagnostics);
	leftMenu.dom("#button_about").on(eventName, lychee.aboutDialog);
	leftMenu.dom("#button_notifications").on(eventName, leftMenu.Notifications);
	leftMenu.dom("#button_users").on(eventName, leftMenu.Users);
	leftMenu.dom("#button_u2f").on(eventName, leftMenu.u2f);
	leftMenu.dom("#button_sharing").on(eventName, leftMenu.Sharing);
	leftMenu.dom("#button_update").on(eventName, leftMenu.Update);
};

/**
 * @returns {void}
 */
leftMenu.Logs = function () {
	view.logs.init();
};

/**
 * @returns {void}
 */
leftMenu.Diagnostics = function () {
	view.diagnostics.init();
};

/**
 * @returns {void}
 */
leftMenu.Update = function () {
	view.update.init();
};

/**
 * @returns {void}
 */
leftMenu.Notifications = function () {
	notifications.load();
};

/**
 * @returns {void}
 */
leftMenu.Users = function () {
	users.list();
};

/**
 * @returns {void}
 */
leftMenu.u2f = function () {
	u2f.list();
};

/**
 * @returns {void}
 */
leftMenu.Sharing = function () {
	sharing.list();
};

/**
 * @description This module is used to show and hide the loading bar.
 */

var loadingBar = {
	/** @type {?string} */
	status: null,
	/** @type {jQuery} */
	_dom: $("#loading")
};

/**
 * @param {string} [selector=""]
 * @returns {jQuery}
 */
loadingBar.dom = function (selector) {
	if (selector == null || selector === "") return loadingBar._dom;
	return loadingBar._dom.find(selector);
};

/**
 * @param {?string} status the status, either `null`, `"error"` or `"success"`
 * @param {?string} errorText the error text to show
 * @returns {void}
 */
loadingBar.show = function (status, errorText) {
	if (status === "error") {
		// Set status
		loadingBar.status = "error";

		// Parse text
		if (errorText) errorText = errorText.replace("<br>", "");
		if (!errorText) errorText = lychee.locale["ERROR_TEXT"];

		// Move header down
		if (visible.header()) header.dom().addClass("header--error");

		// Also move down the dark background
		if (basicModal.visible()) {
			$(".basicModalContainer").addClass("basicModalContainer--error");
			$(".basicModal").addClass("basicModal--error");
		}

		// Modify loading
		loadingBar.dom().removeClass("loading uploading error success").html("<h1>" + lychee.locale["ERROR"] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 3000);

		return;
	}

	if (status === "success") {
		// Set status
		loadingBar.status = "success";

		// Parse text
		if (errorText) errorText = errorText.replace("<br>", "");
		if (!errorText) errorText = lychee.locale["ERROR_TEXT"];

		// Move header down
		if (visible.header()) header.dom().addClass("header--error");

		// Also move down the dark background
		if (basicModal.visible()) {
			$(".basicModalContainer").addClass("basicModalContainer--error");
			$(".basicModal").addClass("basicModal--error");
		}

		// Modify loading
		loadingBar.dom().removeClass("loading uploading error success").html("<h1>" + lychee.locale["SUCCESS"] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 2000);

		return;
	}

	if (loadingBar.status === null) {
		// Set status
		loadingBar.status = lychee.locale["LOADING"];

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			// Move header down
			if (visible.header()) header.dom().addClass("header--loading");

			// Modify loading
			loadingBar.dom().removeClass("loading uploading error").html("").addClass("loading").show();
		}, 1000);
	}
};

/**
 * @param {boolean} force
 * @returns {void}
 */
loadingBar.hide = function (force) {
	if (loadingBar.status !== "error" && loadingBar.status !== "success" && loadingBar.status != null || force) {
		// Remove status
		loadingBar.status = null;

		// Move header up
		header.dom().removeClass("header--error header--loading");
		// Also move up the dark background
		$(".basicModalContainer").removeClass("basicModalContainer--error");
		$(".basicModal").removeClass("basicModal--error");

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

var lychee = {
	title: document.title,
	version: "",
	versionCode: "", // not really needed anymore

	updatePath: "https://LycheeOrg.github.io/update.json",
	updateURL: "https://github.com/LycheeOrg/Lychee/releases",
	website: "https://LycheeOrg.github.io",

	publicMode: false,
	viewMode: false,
	full_photo: true,
	downloadable: false,
	public_photos_hidden: true,
	share_button_visible: false,
	/**
  * Enable admin mode (multi-user)
  * @type boolean
  */
	admin: false,
	/**
  * Enable possibility to upload (multi-user)
  * @type boolean
  */
	may_upload: false,
	/**
  * Locked user (multi-user)
  * @type boolean
  */
	is_locked: false,
	/** @type {?string} */
	username: null,
	/**
  * Values:
  *
  *  - `0`: Use default, "square" layout.
  *  - `1`: Use Flickr-like "justified" layout.
  *  - `2`: Use Google-like "unjustified" layout
  *
  * @type {number}
  */
	layout: 1,
	/**
  * Display search in public mode.
  * @type boolean
  */
	public_search: false,
	/**
  * Overlay display type
  * @type {string}
  */
	image_overlay_type: "exif",
	/**
  * Image overlay type default type
  * @type {string}
  */
	image_overlay_type_default: "exif",
	/**
  * Display photo coordinates on map
  * @type boolean
  */
	map_display: false,
	/**
  * Display photos of public album on map (user not logged in)
  * @type boolean
  */
	map_display_public: false,
	/**
  * Use the GPS direction data on displayed maps
  * @type boolean
  */
	map_display_direction: true,
	/**
  * Provider of OSM Tiles
  * @type {string}
  */
	map_provider: "Wikimedia",
	/**
  * Include photos of subalbums on map
  * @type boolean
  */
	map_include_subalbums: false,
	/**
  * Retrieve location name from GPS data
  * @type boolean
  */
	location_decoding: false,
	/**
  * Caching mode for GPS data decoding
  * @type {string}
  */
	location_decoding_caching_type: "Harddisk",
	/**
  * Show location name
  * @type boolean
  */
	location_show: false,
	/**
  * Show location name for public albums
  * @type boolean
  */
	location_show_public: false,
	/**
  * Tolerance for navigating when swiping images to the left and right on mobile
  * @type {number}
  */
	swipe_tolerance_x: 150,
	/**
  * Tolerance for navigating when swiping images up and down
  * @type {number}
  */
	swipe_tolerance_y: 250,

	/**
  * Is landing page enabled?
  * @type boolean
  */
	landing_page_enabled: false,
	delete_imported: false,
	import_via_symlink: false,
	skip_duplicates: false,

	nsfw_visible: true,
	nsfw_visible_saved: true,
	nsfw_blur: false,
	nsfw_warning: false,

	album_subtitle_type: "oldstyle",

	upload_processing_limit: 4,

	// this is device specific config, in this case default is Desktop.
	header_auto_hide: true,
	active_focus_on_page_load: false,
	enable_button_visibility: true,
	enable_button_share: true,
	enable_button_archive: true,
	enable_button_move: true,
	enable_button_trash: true,
	enable_button_fullscreen: true,
	enable_button_download: true,
	enable_button_add: true,
	enable_button_more: true,
	enable_button_rotate: true,
	enable_close_tab_on_esc: false,
	enable_tabindex: false,
	enable_contextmenu_header: true,
	hide_content_during_imgview: false,
	device_type: "desktop",

	checkForUpdates: true,
	/**
  * The most recent, available Lychee version encoded as an integer, e.g. 040506
  * @type {number}
  */
	update_json: 0,
	update_available: false,
	new_photos_notification: false,
	/** @type {?SortingCriterion} */
	sorting_photos: null,
	/** @type {?SortingCriterion} */
	sorting_albums: null,
	/**
  * The absolute path of the server-side installation directory of Lychee, e.g. `/var/www/lychee`
  * @type {string}
  */
	location: "",

	lang: "",
	/** @type {string[]} */
	lang_available: [],

	dropbox: false,
	dropboxKey: "",

	content: $(".content"),
	imageview: $("#imageview"),
	footer: $("#footer"),

	/** @type {Locale} */
	locale: {},

	nsfw_unlocked_albums: []
};

/**
 * @returns {string}
 */
lychee.diagnostics = function () {
	return "/Diagnostics";
};

/**
 * @returns {string}
 */
lychee.logs = function () {
	return "/Logs";
};

/**
 * @returns {void}
 */
lychee.aboutDialog = function () {
	var msg = lychee.html(_templateObject49, lychee.version, lychee.updateURL, lychee.locale["UPDATE_AVAILABLE"], lychee.locale["ABOUT_SUBTITLE"], lychee.website, lychee.locale["ABOUT_DESCRIPTION"]);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});

	if (lychee.checkForUpdates) lychee.getUpdate();
};

/**
 * @param {boolean} isFirstInitialization must be set to `false` if called
 *                                        for re-initialization to prevent
 *                                        multiple registrations of global
 *                                        event handlers
 * @returns {void}
 */
lychee.init = function () {
	var isFirstInitialization = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	lychee.adjustContentHeight();

	api.post("Session::init", {},
	/** @param {InitializationData} data */
	function (data) {
		lychee.parseInitializationData(data);

		if (data.status === 2) {
			// Logged in
			leftMenu.build();
			leftMenu.bind();
			lychee.setMode("logged_in");

			// Show dialog when there is no username and password
			// TODO: Refactor this. At least rename the flag `login` to something more understandable like `isAdminUserConfigured`, but rather re-factor the whole logic, i.e. the initial user should be created as part of the installation routine.
			// In particular it is completely insane to build the UI as if the admin user was successfully authenticated.
			// This might leak confidential photos to anybody if the DB is filled
			// with photos and the admin password reset to `null`.
			if (data.config.login === false) settings.createLogin();
		} else if (data.status === 1) {
			lychee.setMode("public");
		} else {
			loadingBar.show("error", "Error: Unexpected status");
			return;
		}

		if (isFirstInitialization) {
			$(window).on("popstate", function () {
				var autoplay = history.state && history.state.hasOwnProperty("autoplay") ? history.state.autoplay : true;
				lychee.load(autoplay);
			});
			lychee.load();
		}
	});
};

/**
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parseInitializationData = function (data) {
	lychee.update_json = data.update_json;
	lychee.update_available = data.update_available;

	// TODO: Let the backend report the version as a proper object with properties for major, minor and patch level
	lychee.versionCode = data.config.version;
	if (lychee.versionCode !== "") {
		var digits = lychee.versionCode.match(/.{1,2}/g);
		lychee.version = parseInt(digits[0]).toString() + "." + parseInt(digits[1]).toString() + "." + parseInt(digits[2]).toString();
	}

	// we copy the locale that exists only.
	// This ensures forward and backward compatibility.
	// e.g. if the front localization is unfinished in a language
	// or if we need to change some locale string
	for (var key in data.locale) {
		lychee.locale[key] = data.locale[key];
	}

	// Check status
	// 0 = No configuration
	// 1 = Logged out
	// 2 = Logged in
	if (data.status === 2) {
		// Logged in
		lychee.parsePublicInitializationData(data);
		lychee.parseProtectedInitializationData(data);

		lychee.may_upload = data.admin || data.may_upload;
		lychee.admin = data.admin;
		lychee.is_locked = data.is_locked;
		lychee.username = data.username;
	} else if (data.status === 1) {
		lychee.parsePublicInitializationData(data);
	} else {
		// should not happen.
	}
};

/**
 * Parses the configuration settings which are always available.
 *
 * TODO: If configuration management is re-factored on the backend, remember to use proper types in the first place
 *
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parsePublicInitializationData = function (data) {
	lychee.sorting_photos = data.config.sorting_photos;
	lychee.sorting_albums = data.config.sorting_albums;
	lychee.album_subtitle_type = data.config.album_subtitle_type || "oldstyle";
	lychee.checkForUpdates = data.config.check_for_updates;
	lychee.layout = Number.parseInt(data.config.layout, 10) || 1;
	lychee.landing_page_enable = data.config.landing_page_enable === "1";
	lychee.public_search = data.config.public_search === "1";
	lychee.image_overlay_type = data.config.image_overlay_type || "exif";
	lychee.image_overlay_type_default = lychee.image_overlay_type;
	lychee.map_display = data.config.map_display === "1";
	lychee.map_display_public = data.config.map_display_public === "1";
	lychee.map_display_direction = data.config.map_display_direction === "1";
	lychee.map_provider = data.config.map_provider || "Wikimedia";
	lychee.map_include_subalbums = data.config.map_include_subalbums === "1";
	lychee.location_show = data.config.location_show === "1";
	lychee.location_show_public = data.config.location_show_public === "1";
	lychee.swipe_tolerance_x = Number.parseInt(data.config.swipe_tolerance_x, 10) || 150;
	lychee.swipe_tolerance_y = Number.parseInt(data.config.swipe_tolerance_y, 10) || 250;

	lychee.nsfw_visible = data.config.nsfw_visible === "1";
	lychee.nsfw_visible_saved = lychee.nsfw_visible;
	lychee.nsfw_blur = data.config.nsfw_blur === "1";
	lychee.nsfw_warning = data.config.nsfw_warning === "1";

	lychee.header_auto_hide = data.config_device.header_auto_hide;
	lychee.active_focus_on_page_load = data.config_device.active_focus_on_page_load;
	lychee.enable_button_visibility = data.config_device.enable_button_visibility;
	lychee.enable_button_share = data.config_device.enable_button_share;
	lychee.enable_button_archive = data.config_device.enable_button_archive;
	lychee.enable_button_move = data.config_device.enable_button_move;
	lychee.enable_button_trash = data.config_device.enable_button_trash;
	lychee.enable_button_fullscreen = data.config_device.enable_button_fullscreen;
	lychee.enable_button_download = data.config_device.enable_button_download;
	lychee.enable_button_add = data.config_device.enable_button_add;
	lychee.enable_button_more = data.config_device.enable_button_more;
	lychee.enable_button_rotate = data.config_device.enable_button_rotate;
	lychee.enable_close_tab_on_esc = data.config_device.enable_close_tab_on_esc;
	lychee.enable_tabindex = data.config_device.enable_tabindex;
	lychee.enable_contextmenu_header = data.config_device.enable_contextmenu_header;
	lychee.hide_content_during_imgview = data.config_device.hide_content_during_imgview;
	lychee.device_type = data.config_device.device_type || "desktop"; // we set default as Desktop
};

/**
 * Parses the configuration settings which are only available, if a user is authenticated.
 *
 * TODO: If configuration management is re-factored on the backend, remember to use proper types in the first place
 *
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parseProtectedInitializationData = function (data) {
	lychee.dropboxKey = data.config.dropbox_key || "";
	lychee.location = data.config.location || "";
	lychee.checkForUpdates = data.config.check_for_updates === "1";
	lychee.lang = data.config.lang || "";
	lychee.lang_available = data.config.lang_available || [];
	lychee.location_decoding = data.config.location_decoding === "1";
	lychee.default_license = data.config.default_license || "none";
	lychee.css = data.config.css || "";
	lychee.full_photo = data.config.full_photo === "1";
	lychee.downloadable = data.config.downloadable === "1";
	lychee.public_photos_hidden = data.config.public_photos_hidden === "1";
	lychee.share_button_visible = data.config.share_button_visible === "1";
	lychee.delete_imported = data.config.delete_imported === "1";
	lychee.import_via_symlink = data.config.import_via_symlink === "1";
	lychee.skip_duplicates = data.config.skip_duplicates === "1";
	lychee.editor_enabled = data.config.editor_enabled === "1";
	lychee.new_photos_notification = data.config.new_photos_notification === "1";
	lychee.upload_processing_limit = Number.parseInt(data.config.upload_processing_limit, 10) || 4;
};

/**
 * @param {{username: string, password: string}} data
 * @returns {void}
 */
lychee.login = function (data) {
	if (!data.username.trim()) {
		basicModal.error("username");
		return;
	}
	if (!data.password.trim()) {
		basicModal.error("password");
		return;
	}

	api.post("Session::login", data, function () {
		return window.location.reload();
	}, null, function (jqXHR) {
		if (jqXHR.status === 401) {
			basicModal.error("password");
			return true;
		} else {
			return false;
		}
	});
};

/**
 * @returns {void}
 */
lychee.loginDialog = function () {
	// Make background unfocusable
	tabindex.makeUnfocusable(header.dom());
	tabindex.makeUnfocusable(lychee.content);
	tabindex.makeUnfocusable(lychee.imageview);

	var msg = lychee.html(_templateObject50, build.iconic("key"), lychee.locale["USERNAME"], tabindex.get_next_tab_index(), lychee.locale["PASSWORD"], tabindex.get_next_tab_index(), lychee.version, lychee.updateURL, lychee.locale["UPDATE_AVAILABLE"]);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["SIGN_IN"],
				fn: lychee.login,
				attributes: [["data-tabindex", tabindex.get_next_tab_index()]]
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close,
				attributes: [["data-tabindex", tabindex.get_next_tab_index()]]
			}
		}
	});

	// This feels awkward, because this hooks into the modal dialog in some
	// unpredictable way.
	// It would be better to have a checkbox for password-less login in the
	// dialog and then let the action handler of the modal dialog, i.e.
	// `lychee.login` handle both cases.
	// TODO: Refactor this.
	$("#signInKeyLess").on("click", u2f.login);

	if (lychee.checkForUpdates) lychee.getUpdate();

	tabindex.makeFocusable($(".basicModal"));
};

/**
 * @returns {void}
 */
lychee.logout = function () {
	api.post("Session::logout", {}, function () {
		return window.location.reload();
	});
};

/**
 * @param {?string} [url=null]
 * @param {boolean} [autoplay=true]
 *
 * @returns {void}
 */
lychee.goto = function () {
	var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	url = "#" + (url !== null ? url : "");
	history.pushState({ autoplay: autoplay }, null, url);
	lychee.load(autoplay);
};

/**
 * @param {?string} [albumID=null]
 * @param {boolean} [autoplay=true]
 *
 * @returns {void}
 */
lychee.gotoMap = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	// If map functionality is disabled -> go to album
	if (!lychee.map_display) {
		loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
		return;
	}
	lychee.goto("map/" + (albumID !== null ? albumID : ""), autoplay);
};

/**
 * Triggers a reload, if the given IDs are in legacy format.
 *
 * If any of the IDs is in legacy format, the method first translates the IDs
 * into the modern format via an AJAX call to the backend and then triggers
 * an asynchronous reloading of the page with the resolved, modern IDs.
 * The function returns `true` in this case.
 *
 * If the IDs are already in modern format (and thus neither a translation
 * nor a reloading is required), the function returns `false`.
 * In this case this function is basically a no-op.
 *
 * @param {?string} albumID  the album ID
 * @param {?string} photoID  the photo ID
 * @param {boolean} autoplay indicates whether playback should start
 *                           automatically, if the indicated photo is a video
 *
 * @returns {boolean} `true`, if any of the IDs has been in legacy format
 *                   and an asynchronous reloading has been scheduled
 */
lychee.reloadIfLegacyIDs = function (albumID, photoID, autoplay) {
	/** @param {?string} id the inspected ID */
	var isLegacyID = function isLegacyID(id) {
		// The legacy IDs were pure numeric values. We exclude values which
		// have 24 digits, because these could also be modern IDs.
		// A modern IDs is a 24 character long, base64 encoded value and thus
		// could also match 24 digits by accident.
		return id && id.length !== 24 && parseInt(id, 10).toString() === id;
	};

	if (!isLegacyID(albumID) && !isLegacyID(photoID)) {
		// this function is a no-op if neither ID is in legacy format
		return false;
	}

	/**
  * Callback to be called asynchronously which executes the actual reloading.
  *
  * @param {?string} newAlbumID
  * @param {?string} newPhotoID
  *
  * @returns {void}
  */
	var reloadWithNewIDs = function reloadWithNewIDs(newAlbumID, newPhotoID) {
		var newUrl = "";
		if (newAlbumID) {
			newUrl += newAlbumID;
			newUrl += newPhotoID ? "/" + newPhotoID : "";
		}
		lychee.goto(newUrl, autoplay);
	};

	// We have to deal with three cases:
	//  1. the album and photo ID need to be translated
	//  2. only the album ID needs to be translated
	//  3. only the photo ID needs to be translated
	var params = {};
	if (isLegacyID(albumID)) params.albumID = parseInt(albumID, 10);
	if (isLegacyID(photoID)) params.photoID = parseInt(photoID, 10);
	api.post("Legacy::translateLegacyModelIDs", params, function (data) {
		reloadWithNewIDs(data.hasOwnProperty("albumID") ? data.albumID : albumID, data.hasOwnProperty("photoID") ? data.photoID : photoID);
	});

	return true;
};

/**
 * @param {boolean} [autoplay=true]
 * @returns {void}
 */
lychee.load = function () {
	var autoplay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	var hash = document.location.hash.replace("#", "").split("/");
	var albumID = hash[0];
	var photoID = hash[1];

	contextMenu.close();
	multiselect.close();
	tabindex.reset();

	if (albumID && photoID) {
		if (albumID === "map") {
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
				return;
			}
			$(".no_content").remove();
			// show map
			// albumID has been stored in photoID due to URL format #map/albumID
			albumID = photoID;

			// Trash data
			_photo3.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			if (album.json && albumID === album.json.id) {
				view.album.title();
			}
			mapview.open(albumID);
			lychee.footer_hide();
		} else if (albumID === "search") {
			// Search has been triggered
			var search_string = decodeURIComponent(photoID);

			if (search_string.trim() === "") {
				// do nothing on "only space" search strings
				return;
			}
			// If public search is disabled -> do nothing
			if (lychee.publicMode === true && !lychee.public_search) {
				loadingBar.show("error", lychee.locale["ERROR_SEARCH_DEACTIVATED"]);
				return;
			}

			header.dom(".header__search").val(search_string);
			search.find(search_string);

			lychee.footer_show();
		} else {
			if (lychee.reloadIfLegacyIDs(albumID, photoID, autoplay)) {
				return;
			}

			$(".no_content").remove();
			// Show photo

			// Trash data
			_photo3.json = null;

			/**
    * @param {boolean} isParentAlbumAccessible
    * @returns {void}
    */
			var loadPhoto = function loadPhoto(isParentAlbumAccessible) {
				if (!isParentAlbumAccessible) {
					lychee.setMode("view");
				}
				_photo3.load(photoID, albumID, autoplay);

				// Make imageview focusable
				tabindex.makeFocusable(lychee.imageview);

				// Make thumbnails unfocusable and store which element had focus
				tabindex.makeUnfocusable(lychee.content, true);

				// hide contentview if requested
				if (lychee.hide_content_during_imgview) lychee.content.hide();

				lychee.footer_hide();
			};

			// Load Photo
			// If we don't have an album or the wrong album load the album
			// first and let the album loader load the photo afterwards or
			// load the photo directly.
			if (lychee.content.html() === "" || album.json === null || album.json.id !== albumID || header.dom(".header__search").length && header.dom(".header__search").val().length !== 0) {
				lychee.content.hide();
				album.load(albumID, loadPhoto);
			} else {
				loadPhoto(true);
			}
		}
	} else if (albumID) {
		if (albumID === "map") {
			$(".no_content").remove();
			// Show map of all albums
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
				return;
			}

			// Trash data
			_photo3.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			mapview.open();
			lychee.footer_hide();
		} else if (albumID === "search") {
			// search string is empty -> do nothing
		} else {
			if (lychee.reloadIfLegacyIDs(albumID, photoID, autoplay)) {
				return;
			}

			$(".no_content").remove();
			// Trash data
			_photo3.json = null;

			// Show Album
			if (visible.photo()) {
				view.photo.hide();
				tabindex.makeUnfocusable(lychee.imageview);
			}
			if (visible.mapview()) mapview.close();
			if (visible.sidebar() && (album.isSmartID(albumID) || album.isSearchID(albumID))) _sidebar.toggle(false);
			$("#sensitive_warning").hide();
			if (album.json && albumID === album.json.id) {
				view.album.title();
				lychee.content.show();
				tabindex.makeFocusable(lychee.content, true);
				// If the album was loaded in the background (when content is
				// hidden), scrolling may not have worked.
				view.album.content.restoreScroll();
			} else {
				album.load(albumID);
			}
			lychee.footer_show();
		}
	} else {
		$(".no_content").remove();

		// Trash data
		search.json = null;
		album.json = null;
		_photo3.json = null;

		// Hide sidebar
		if (visible.sidebar()) _sidebar.toggle(false);

		// Show Albums
		if (visible.photo()) {
			view.photo.hide();
			tabindex.makeUnfocusable(lychee.imageview);
		}
		if (visible.mapview()) mapview.close();
		$("#sensitive_warning").hide();
		lychee.content.show();
		lychee.footer_show();
		albums.load();
	}
};

/**
 * @returns {void}
 */
lychee.getUpdate = function () {
	// console.log(lychee.update_available);
	// console.log(lychee.update_json);

	if (lychee.update_json !== 0) {
		if (lychee.update_available) {
			$(".version span").show();
		}
	} else {
		/**
   * @param {{lychee: {version: number}}} data
   */
		var success = function success(data) {
			if (data.lychee.version > parseInt(lychee.versionCode)) $(".version span").show();
		};

		$.ajax({
			url: lychee.updatePath,
			success: success
		});
	}
};

/**
 * Sets the title of the browser window and the title shown in the header bar.
 *
 * The window title is prefixed by the value of the configuration setting
 * `lychee.title`.
 *
 * If both, the prefix `lychee.title` and the given title, are not empty,
 * they are seperated by an en-dash.
 *
 * @param {string} [title=""]
 * @param {boolean} [editable=false]
 */
lychee.setTitle = function () {
	var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";
	var editable = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	document.title = lychee.title + (lychee.title && title ? " – " : "") + title;
	header.setEditable(editable);
	header.setTitle(title);
};

/**
 * @param {string} mode - one out of: `public`, `view`, `logged_in`
 */
lychee.setMode = function (mode) {
	if (lychee.is_locked) {
		$("#button_settings_open").remove();
	}
	if (!lychee.may_upload) {
		$("#button_sharing").remove();

		$(document).off("click", ".header__title--editable").off("touchend", ".header__title--editable").off("contextmenu", ".photo").off("contextmenu", ".album").off("drop");

		Mousetrap.unbind(["u"]).unbind(["s"]).unbind(["n"]).unbind(["r"]).unbind(["d"]).unbind(["t"]).unbind(["command+backspace", "ctrl+backspace"]).unbind(["command+a", "ctrl+a"]);
	}
	if (!lychee.admin) {
		$("#button_users, #button_logs, #button_diagnostics").remove();
	}

	if (mode === "logged_in") {
		// After login the keyboard short-cuts to login by password (l) and
		// by key (k) are not required anymore, so we unbind them.
		Mousetrap.unbind(["l"]).unbind(["k"]);

		// The code searches by class, so remove the other instance.
		$(".header__search, .header__clear", ".header__toolbar--public").remove();

		if (!lychee.editor_enabled) {
			$("#button_rotate_cwise").remove();
			$("#button_rotate_ccwise").remove();
		}
		return;
	} else {
		$(".header__search, .header__clear", ".header__toolbar--albums").remove();
		$("#button_rotate_cwise").remove();
		$("#button_rotate_ccwise").remove();
	}

	$("#button_settings, .header__divider, .leftMenu").remove();

	if (mode === "public") {
		lychee.publicMode = true;
	} else if (mode === "view") {
		Mousetrap.unbind(["esc", "command+up"]);

		$("#button_back, a#next, a#previous").remove();
		$(".no_content").remove();

		lychee.publicMode = true;
		lychee.viewMode = true;
	}

	// just mak
	header.bind_back();
};

/**
 * @param {jQuery} obj
 * @param {string} animation
 *
 * @returns {void}
 */
lychee.animate = function (obj, animation) {
	var animations = [["fadeIn", "fadeOut"], ["contentZoomIn", "contentZoomOut"]];

	for (var i = 0; i < animations.length; i++) {
		for (var x = 0; x < animations[i].length; x++) {
			if (animations[i][x] === animation) {
				obj.removeClass(animations[i][0] + " " + animations[i][1]).addClass(animation);
				return;
			}
		}
	}
};

/**
 * @callback DropboxLoadedCB
 * @returns {void}
 */

/**
 * Ensures that the Dropbox Chooser JS component is loaded and calls the
 * provided callback after loading.
 *
 * See {@link Dropbox}
 *
 * @param {DropboxLoadedCB} callback
 */
lychee.loadDropbox = function (callback) {
	if (!lychee.dropboxKey) {
		loadingBar.show("error", "Error: Dropbox key not set");
		return;
	}

	// If the dropbox component has already been loaded, immediately call
	// the callback; otherwise load the component first and call callback
	// on success.
	if (lychee.dropbox) {
		callback();
	} else {
		loadingBar.show();

		var g = document.createElement("script");
		var s = document.getElementsByTagName("script")[0];

		g.src = "https://www.dropbox.com/static/api/1/dropins.js";
		g.id = "dropboxjs";
		g.type = "text/javascript";
		g.async = true;
		g.setAttribute("data-app-key", lychee.dropboxKey);
		g.onload = g.onreadystatechange = function () {
			var rs = this.readyState;
			if (rs && rs !== "complete" && rs !== "loaded") return;
			lychee.dropbox = true;
			loadingBar.hide();
			callback();
		};
		s.parentNode.insertBefore(g, s);
	}
};

/**
 * @returns {string}
 */
lychee.getEventName = function () {
	if (lychee.device_type === "mobile") {
		return "touchend";
	}
	return "click";
};

/**
 * DON'T USE THIS METHOD.
 *
 * TODO: Find all invocations of this method and nuke them.
 *
 * This method does not cover all potentially dangerous characters and this
 * method should not be required on the first place.
 * jQuery and even native JS has better methods for this in the year 2022!
 *
 * @param {string} [html=""]
 * @returns {string}
 */
lychee.escapeHTML = function () {
	var html = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

	// Ensure that html is a string
	html += "";

	// Escape all critical characters
	html = html.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/`/g, "&#96;");

	return html;
};

/**
 * Creates a HTML string with some fancy variable substitution.
 *
 * Actually, this method should not be required in the year 2022.
 * jQuery and even native JS should probably provide a suitable alternative.
 * But this method is used so ubiquitous that it might be difficult to get
 * rid of it.
 *
 * TODO: Try it nonetheless.
 *
 * @param literalSections
 * @param substs
 * @returns {string}
 */
lychee.html = function (literalSections) {
	// Use raw literal sections: we don’t want
	// backslashes (\n etc.) to be interpreted
	var raw = literalSections.raw;
	var result = "";

	for (var _len = arguments.length, substs = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
		substs[_key - 1] = arguments[_key];
	}

	substs.forEach(function (subst, i) {
		// Retrieve the literal section preceding
		// the current substitution
		var lit = raw[i];

		// If the substitution is preceded by a dollar sign,
		// we escape special characters in it
		if (lit.slice(-1) === "$") {
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

/**
 * @param {XMLHttpRequest} jqXHR
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee Exception
 * @returns {boolean}
 */
lychee.handleAPIError = function (jqXHR, params, lycheeException) {
	if (api.hasSessionExpired(jqXHR, lycheeException)) {
		loadingBar.show("error", "Session expired.");
		setTimeout(function () {
			lychee.goto();
			window.location.reload();
		}, 3000);
	} else {
		var msg = jqXHR.statusText + (lycheeException ? " - " + lycheeException.message : "");
		loadingBar.show("error", msg);
		console.error("The server returned an error response", {
			description: msg,
			params: params,
			response: lycheeException
		});
	}
	return true;
};

/**
 * @returns {void}
 */
lychee.fullscreenEnter = function () {
	var elem = document.documentElement;
	if (elem.requestFullscreen) {
		elem.requestFullscreen();
	} else if (elem.mozRequestFullScreen) {
		/* Firefox */
		elem.mozRequestFullScreen();
	} else if (elem.webkitRequestFullscreen) {
		/* Chrome, Safari and Opera */
		elem.webkitRequestFullscreen();
	} else if (elem.msRequestFullscreen) {
		/* IE/Edge */
		elem.msRequestFullscreen();
	}
};

/**
 * @returns {void}
 */
lychee.fullscreenExit = function () {
	if (document.exitFullscreen) {
		document.exitFullscreen();
	} else if (document.mozCancelFullScreen) {
		/* Firefox */
		document.mozCancelFullScreen();
	} else if (document.webkitExitFullscreen) {
		/* Chrome, Safari and Opera */
		document.webkitExitFullscreen();
	} else if (document.msExitFullscreen) {
		/* IE/Edge */
		document.msExitFullscreen();
	}
};

/**
 * @returns {void}
 */
lychee.fullscreenToggle = function () {
	if (lychee.fullscreenStatus()) {
		lychee.fullscreenExit();
	} else {
		lychee.fullscreenEnter();
	}
};

/**
 * @returns {boolean}
 */
lychee.fullscreenStatus = function () {
	var elem = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
	return !!elem;
};

/**
 * @returns {boolean}
 */
lychee.fullscreenAvailable = function () {
	return document.fullscreenEnabled || document.mozFullscreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled;
};

/**
 * @returns {void}
 */
lychee.fullscreenUpdate = function () {
	if (lychee.fullscreenStatus()) {
		$("#button_fs_album_enter,#button_fs_enter").hide();
		$("#button_fs_album_exit,#button_fs_exit").show();
	} else {
		$("#button_fs_album_enter,#button_fs_enter").show();
		$("#button_fs_album_exit,#button_fs_exit").hide();
	}
};

/**
 * @returns {void}
 */
lychee.footer_show = function () {
	setTimeout(function () {
		lychee.footer.removeClass("hide_footer");
	}, 200);
};

/**
 * @returns {void}
 */
lychee.footer_hide = function () {
	lychee.footer.addClass("hide_footer");
};

/**
 * Sets the height of the content area.
 *
 * Because the height of the footer can vary, we need to set some
 * dimensions dynamically, at startup.
 *
 * @returns {void}
 */
lychee.adjustContentHeight = function () {
	if (lychee.footer.length > 0) {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + " - " + lychee.footer.outerHeight() + "px)");
		$("#container").css("padding-bottom", lychee.footer.outerHeight());
	} else {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + ")");
	}
};

/**
 * @returns {string}
 */
lychee.getBaseUrl = function () {
	if (location.href.includes("index.html")) {
		return location.href.replace("index.html" + location.hash, "");
	} else if (location.href.includes("gallery#")) {
		return location.href.replace("gallery" + location.hash, "");
	} else {
		return location.href.replace(location.hash, "");
	}
};

/**
 * drag album to another one
 * @param {DragEvent} ev
 * @returns {void}
 */
lychee.startDrag = function (ev) {
	/** @type ?HTMLDivElement */
	var div = ev.target.closest("div.album,div.photo");
	if (!div) return;
	var type = div.classList.contains("album") ? "album" : "photo";
	ev.dataTransfer.setData("text/plain", type + "-" + div.dataset.id);
};

/**
 * drop album
 * @param {DragEvent} ev
 * @returns {void}
 */
lychee.finishDrag = function (ev) {
	ev.preventDefault();

	/** @type string */
	var data = ev.dataTransfer.getData("text/plain");
	/** @type string */
	var targetId = ev.target.closest("div.album").dataset.id;
	if (!targetId || data.substring(6) === targetId) return;

	if (data.startsWith("photo-")) {
		// photo is dragged
		contextMenu.photoDrop(data.substring(6), targetId, ev);
	} else {
		// album is dragged
		contextMenu.albumDrop(data.substring(6), targetId, ev);
	}
};

/**
 * Album drag-over callback
 * @param {DragEvent} ev
 * @returns {void}
 */
lychee.overDrag = function (ev) {
	ev.preventDefault();
	/** @type ?HTMLDivElement */
	var div = ev.target.closest("div.album");
	if (div) {
		div.classList.add("album__dragover");
	}
};

/**
 * Album drag-leave callback
 * @param {DragEvent} ev
 * @returns {void}
 */
lychee.leaveDrag = function (ev) {
	/** @type ?HTMLDivElement */
	var div = ev.target.closest("div.album");
	if (div) {
		div.classList.remove("album__dragover");
	}
};

/**
 * drag-end callback
 * @param {DragEvent} ev
 * @returns {void}
 */
lychee.endDrag = function (ev) {
	$("div.album").removeClass("album__dragover");
};

/**
 * @typedef {Object.<string, string>} Locale
 * @property {function} printFilesizeLocalized
 * @property {function} printDateTime
 * @property {function} printMonthYear
 */

lychee.locale = {
	USERNAME: "username",
	PASSWORD: "password",
	ENTER: "Enter",
	CANCEL: "Cancel",
	SIGN_IN: "Sign In",
	CLOSE: "Close",

	SETTINGS: "Settings",
	USERS: "Users",
	U2F: "U2F",
	NOTIFICATIONS: "Notifications",
	SHARING: "Sharing",
	CHANGE_LOGIN: "Change Login",
	CHANGE_SORTING: "Change Sorting",
	SET_DROPBOX: "Set Dropbox",
	ABOUT_LYCHEE: "About Lychee",
	DIAGNOSTICS: "Diagnostics",
	DIAGNOSTICS_GET_SIZE: "Request space usage",
	LOGS: "Show Logs",
	CLEAN_LOGS: "Clean Noise",
	SIGN_OUT: "Sign Out",
	UPDATE_AVAILABLE: "Update available!",
	MIGRATION_AVAILABLE: "Migration available!",
	CHECK_FOR_UPDATE: "Check for updates",
	DEFAULT_LICENSE: "Default License for new uploads:",
	SET_LICENSE: "Set License",
	SET_OVERLAY_TYPE: "Set Overlay",
	SET_MAP_PROVIDER: "Set OpenStreetMap tiles provider",
	SAVE_RISK: "Save my modifications, I accept the Risk!",
	MORE: "More",
	DEFAULT: "Default",

	SMART_ALBUMS: "Smart albums",
	SHARED_ALBUMS: "Shared albums",
	ALBUMS: "Albums",
	PHOTOS: "Pictures",
	SEARCH_RESULTS: "Search results",

	RENAME: "Rename",
	RENAME_ALL: "Rename All",
	MERGE: "Merge",
	MERGE_ALL: "Merge All",
	MAKE_PUBLIC: "Make Public",
	SHARE_ALBUM: "Share Album",
	SHARE_PHOTO: "Share Photo",
	SHARE_WITH: "Share with...",
	DOWNLOAD_ALBUM: "Download Album",
	ABOUT_ALBUM: "About Album",
	DELETE_ALBUM: "Delete Album",
	FULLSCREEN_ENTER: "Enter Fullscreen",
	FULLSCREEN_EXIT: "Exit Fullscreen",

	SHARING_ALBUM_USERS: "Share this album with users",
	SHARING_ALBUM_USERS_LONG_MESSAGE: "Select the users to share this album with",
	WAIT_FETCH_DATA: "Please wait while we get the data...",
	SHARING_ALBUM_USERS_NO_USERS: "There are no users to share the album with",

	DELETE_ALBUM_QUESTION: "Delete Album and Photos",
	KEEP_ALBUM: "Keep Album",
	DELETE_ALBUM_CONFIRMATION_1: "Are you sure you want to delete the album",
	DELETE_ALBUM_CONFIRMATION_2: "and all of the photos it contains? This action can't be undone!",

	DELETE_ALBUMS_QUESTION: "Delete Albums and Photos",
	KEEP_ALBUMS: "Keep Albums",
	DELETE_ALBUMS_CONFIRMATION_1: "Are you sure you want to delete all",
	DELETE_ALBUMS_CONFIRMATION_2: "selected albums and all of the photos they contain? This action can't be undone!",

	DELETE_UNSORTED_CONFIRM: "Are you sure you want to delete all photos from 'Unsorted'?<br>This action can't be undone!",
	CLEAR_UNSORTED: "Clear Unsorted",
	KEEP_UNSORTED: "Keep Unsorted",

	EDIT_SHARING: "Edit Sharing",
	MAKE_PRIVATE: "Make Private",

	CLOSE_ALBUM: "Close Album",
	CLOSE_PHOTO: "Close Photo",
	CLOSE_MAP: "Close Map",

	ADD: "Add",
	MOVE: "Move",
	MOVE_ALL: "Move All",
	DUPLICATE: "Duplicate",
	DUPLICATE_ALL: "Duplicate All",
	COPY_TO: "Copy to...",
	COPY_ALL_TO: "Copy All to...",
	DELETE: "Delete",
	DELETE_ALL: "Delete All",
	DOWNLOAD: "Download",
	DOWNLOAD_MEDIUM: "Download medium size",
	DOWNLOAD_SMALL: "Download small size",
	UPLOAD_PHOTO: "Upload Photo",
	IMPORT_LINK: "Import from Link",
	IMPORT_DROPBOX: "Import from Dropbox",
	IMPORT_SERVER: "Import from Server",
	NEW_ALBUM: "New Album",
	NEW_TAG_ALBUM: "New Tag Album",
	UPLOAD_TRACK: "Upload track",
	DELETE_TRACK: "Delete track",

	TITLE_NEW_ALBUM: "Enter a title for the new album:",
	UNTITLED: "Untitled",
	UNSORTED: "Unsorted",
	STARRED: "Starred",
	RECENT: "Recent",
	PUBLIC: "Public",
	NUM_PHOTOS: "Photos",

	CREATE_ALBUM: "Create Album",
	CREATE_TAG_ALBUM: "Create Tag Album",

	STAR_PHOTO: "Star Photo",
	STAR: "Star",
	UNSTAR: "Unstar",
	STAR_ALL: "Star Selected",
	UNSTAR_ALL: "Unstar Selected",
	TAGS: "Tags",
	TAGS_ALL: "Tags All",
	UNSTAR_PHOTO: "Unstar Photo",

	FULL_PHOTO: "Full Photo",
	ABOUT_PHOTO: "About Photo",
	DISPLAY_FULL_MAP: "Map",
	DIRECT_LINK: "Direct Link",
	DIRECT_LINKS: "Direct Links",
	QR_CODE: "QR Code",

	ALBUM_ABOUT: "About",
	ALBUM_BASICS: "Basics",
	ALBUM_TITLE: "Title",
	ALBUM_NEW_TITLE: "Enter a new title for this album:",
	ALBUMS_NEW_TITLE_1: "Enter a title for all",
	ALBUMS_NEW_TITLE_2: "selected albums:",
	ALBUM_SET_TITLE: "Set Title",
	ALBUM_DESCRIPTION: "Description",
	ALBUM_SHOW_TAGS: "Tags to show",
	ALBUM_NEW_DESCRIPTION: "Enter a new description for this album:",
	ALBUM_SET_DESCRIPTION: "Set Description",
	ALBUM_NEW_SHOWTAGS: "Enter tags of photos that will be visible in this album:",
	ALBUM_SET_SHOWTAGS: "Set tags to show",
	ALBUM_ALBUM: "Album",
	ALBUM_CREATED: "Created",
	ALBUM_IMAGES: "Images",
	ALBUM_VIDEOS: "Videos",
	ALBUM_SHARING: "Share",
	ALBUM_OWNER: "Owner",
	ALBUM_SHR_YES: "YES",
	ALBUM_SHR_NO: "No",
	ALBUM_PUBLIC: "Public",
	ALBUM_PUBLIC_EXPL: "Album can be viewed by others, subject to the restrictions below.",
	ALBUM_FULL: "Full size (v4 only)",
	ALBUM_FULL_EXPL: "Full size pictures are available",
	ALBUM_HIDDEN: "Hidden",
	ALBUM_HIDDEN_EXPL: "Only people with the direct link can view this album.",
	ALBUM_MARK_NSFW: "Mark album as sensitive",
	ALBUM_UNMARK_NSFW: "Unmark album as sensitive",
	ALBUM_NSFW: "Sensitive",
	ALBUM_NSFW_EXPL: "Album contains sensitive content.",
	ALBUM_DOWNLOADABLE: "Downloadable",
	ALBUM_DOWNLOADABLE_EXPL: "Visitors of your Lychee can download this album.",
	ALBUM_SHARE_BUTTON_VISIBLE: "Share button is visible",
	ALBUM_SHARE_BUTTON_VISIBLE_EXPL: "Display social media sharing links.",
	ALBUM_PASSWORD: "Password",
	ALBUM_PASSWORD_PROT: "Password protected",
	ALBUM_PASSWORD_PROT_EXPL: "Album only accessible with a valid password.",
	ALBUM_PASSWORD_REQUIRED: "This album is protected by a password. Enter the password below to view the photos of this album:",
	ALBUM_MERGE_1: "Are you sure you want to merge the album",
	ALBUM_MERGE_2: "into the album",
	ALBUMS_MERGE: "Are you sure you want to merge all selected albums into the album",
	MERGE_ALBUM: "Merge Albums",
	DONT_MERGE: "Don't Merge",
	ALBUM_MOVE_1: "Are you sure you want to move the album",
	ALBUM_MOVE_2: "into the album",
	ALBUMS_MOVE: "Are you sure you want to move all selected albums into the album",
	MOVE_ALBUMS: "Move Albums",
	NOT_MOVE_ALBUMS: "Don't Move",
	ROOT: "Root",
	ALBUM_REUSE: "Reuse",
	ALBUM_LICENSE: "License",
	ALBUM_SET_LICENSE: "Set License",
	ALBUM_LICENSE_HELP: "Need help choosing?",
	ALBUM_LICENSE_NONE: "None",
	ALBUM_RESERVED: "All Rights Reserved",
	ALBUM_SET_ORDER: "Set Order",
	ALBUM_ORDERING: "Order by",

	PHOTO_ABOUT: "About",
	PHOTO_BASICS: "Basics",
	PHOTO_TITLE: "Title",
	PHOTO_NEW_TITLE: "Enter a new title for this photo:",
	PHOTO_SET_TITLE: "Set Title",
	PHOTO_UPLOADED: "Uploaded",
	PHOTO_DESCRIPTION: "Description",
	PHOTO_NEW_DESCRIPTION: "Enter a new description for this photo:",
	PHOTO_SET_DESCRIPTION: "Set Description",
	PHOTO_NEW_LICENSE: "Add a License",
	PHOTO_SET_LICENSE: "Set License",
	PHOTO_REUSE: "Reuse",
	PHOTO_LICENSE: "License",
	PHOTO_LICENSE_HELP: "Need help choosing?",
	PHOTO_LICENSE_NONE: "None",
	PHOTO_RESERVED: "All Rights Reserved",
	PHOTO_IMAGE: "Image",
	PHOTO_VIDEO: "Video",
	PHOTO_SIZE: "Size",
	PHOTO_FORMAT: "Format",
	PHOTO_RESOLUTION: "Resolution",
	PHOTO_DURATION: "Duration",
	PHOTO_FPS: "Frame rate",
	PHOTO_TAGS: "Tags",
	PHOTO_NOTAGS: "No Tags",
	PHOTO_NEW_TAGS: "Enter your tags for this photo. You can add multiple tags by separating them with a comma:",
	PHOTO_NEW_TAGS_1: "Enter your tags for all",
	PHOTO_NEW_TAGS_2: "selected photos. Existing tags will be overwritten. You can add multiple tags by separating them with a comma:",
	PHOTO_SET_TAGS: "Set Tags",
	PHOTO_CAMERA: "Camera",
	PHOTO_CAPTURED: "Captured",
	PHOTO_MAKE: "Make",
	PHOTO_TYPE: "Type/Model",
	PHOTO_LENS: "Lens",
	PHOTO_SHUTTER: "Shutter Speed",
	PHOTO_APERTURE: "Aperture",
	PHOTO_FOCAL: "Focal Length",
	PHOTO_ISO: "ISO",
	PHOTO_SHARING: "Sharing",
	PHOTO_SHR_PLUBLIC: "Public",
	PHOTO_SHR_ALB: "Yes (Album)",
	PHOTO_SHR_PHT: "Yes (Photo)",
	PHOTO_SHR_NO: "No",
	PHOTO_DELETE: "Delete Photo",
	PHOTO_KEEP: "Keep Photo",
	PHOTO_DELETE_1: "Are you sure you want to delete the photo",
	PHOTO_DELETE_2: "? This action can't be undone!",
	PHOTO_DELETE_ALL_1: "Are you sure you want to delete all",
	PHOTO_DELETE_ALL_2: "selected photo? This action can't be undone!",
	PHOTOS_NEW_TITLE_1: "Enter a title for all",
	PHOTOS_NEW_TITLE_2: "selected photos:",
	PHOTO_MAKE_PRIVATE_ALBUM: "This photo is located in a public album. To make this photo private or public, edit the visibility of the associated album.",
	PHOTO_SHOW_ALBUM: "Show Album",
	PHOTO_PUBLIC: "Public",
	PHOTO_PUBLIC_EXPL: "Photo can be viewed by others, subject to the restrictions below.",
	PHOTO_FULL: "Original",
	PHOTO_FULL_EXPL: "Full-resolution picture is available.",
	PHOTO_HIDDEN: "Hidden",
	PHOTO_HIDDEN_EXPL: "Only people with the direct link can view this photo.",
	PHOTO_DOWNLOADABLE: "Downloadable",
	PHOTO_DOWNLOADABLE_EXPL: "Visitors of your gallery can download this photo.",
	PHOTO_SHARE_BUTTON_VISIBLE: "Share button is visible",
	PHOTO_SHARE_BUTTON_VISIBLE_EXPL: "Display social media sharing links.",
	PHOTO_PASSWORD_PROT: "Password protected",
	PHOTO_PASSWORD_PROT_EXPL: "Photo only accessible with a valid password.",
	PHOTO_EDIT_SHARING_TEXT: "The sharing properties of this photo will be changed to the following:",
	PHOTO_NO_EDIT_SHARING_TEXT: "Because this photo is located in a public album, it inherits that album's visibility settings.  Its current visibility is shown below for informational purposes only.",
	PHOTO_EDIT_GLOBAL_SHARING_TEXT: "The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.",
	PHOTO_SHARING_CONFIRM: "Save",
	PHOTO_LOCATION: "Location",
	PHOTO_LATITUDE: "Latitude",
	PHOTO_LONGITUDE: "Longitude",
	PHOTO_ALTITUDE: "Altitude",
	PHOTO_IMGDIRECTION: "Direction",

	LOADING: "Loading",
	ERROR: "Error",
	ERROR_TEXT: "Whoops, it looks like something went wrong. Please reload the site and try again!",
	ERROR_DB_1: "Unable to connect to host database because access was denied. Double-check your host, username and password and ensure that access from your current location is permitted.",
	ERROR_DB_2: "Unable to create the database. Double-check your host, username and password and ensure that the specified user has the rights to modify and add content to the database.",
	ERROR_CONFIG_FILE: "Unable to save this configuration. Permission denied in <b>'data/'</b>. Please set the read, write and execute rights for others in <b>'data/'</b> and <b>'uploads/'</b>. Take a look at the readme for more information.",
	ERROR_UNKNOWN: "Something unexpected happened. Please try again and check your installation and server. Take a look at the readme for more information.",
	ERROR_LOGIN: "Unable to save login. Please try again with another username and password!",
	ERROR_MAP_DEACTIVATED: "Map functionality has been deactivated under settings.",
	ERROR_SEARCH_DEACTIVATED: "Search functionality has been deactivated under settings.",
	SUCCESS: "OK",
	RETRY: "Retry",

	SETTINGS_WARNING: "Changing these advanced settings can be harmful to the stability, security and performance of this application. You should only modify them if you are sure of what you are doing.",
	SETTINGS_SUCCESS_LOGIN: "Login Info updated.",
	SETTINGS_SUCCESS_SORT: "Sorting order updated.",
	SETTINGS_SUCCESS_DROPBOX: "Dropbox Key updated.",
	SETTINGS_SUCCESS_LANG: "Language updated",
	SETTINGS_SUCCESS_LAYOUT: "Layout updated",
	SETTINGS_SUCCESS_IMAGE_OVERLAY: "EXIF Overlay setting updated",
	SETTINGS_SUCCESS_PUBLIC_SEARCH: "Public search updated",
	SETTINGS_SUCCESS_LICENSE: "Default license updated",
	SETTINGS_SUCCESS_MAP_DISPLAY: "Map display settings updated",
	SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC: "Map display settings for public albums updated",
	SETTINGS_SUCCESS_MAP_PROVIDER: "Map provider settings updated",

	U2F_NOT_SUPPORTED: "U2F not supported. Sorry.",
	U2F_NOT_SECURE: "Environment not secured. U2F not available.",
	U2F_REGISTER_KEY: "Register new device.",
	U2F_REGISTRATION_SUCCESS: "Registration successful!",
	U2F_AUTHENTIFICATION_SUCCESS: "Authentication successful!",
	U2F_CREDENTIALS: "Credentials",
	U2F_CREDENTIALS_DELETED: "Credentials deleted!",

	NEW_PHOTOS_NOTIFICATION: "Send new photos notification emails.",
	SETTINGS_SUCCESS_NEW_PHOTOS_NOTIFICATION: "New photos notification updated",
	USER_EMAIL_INSTRUCTION: "Add your email below to enable receiving email notifications.<br />To stop receiving emails, simply remove your email below.",

	SETTINGS_SUCCESS_CSS: "CSS updated",
	SETTINGS_SUCCESS_UPDATE: "Settings updated with success",

	DB_INFO_TITLE: "Enter your database connection details below:",
	DB_INFO_HOST: "Database Host (optional)",
	DB_INFO_USER: "Database Username",
	DB_INFO_PASSWORD: "Database Password",
	DB_INFO_TEXT: "Lychee will create its own database. If required, you can enter the name of an existing database instead:",
	DB_NAME: "Database Name (optional)",
	DB_PREFIX: "Table prefix (optional)",
	DB_CONNECT: "Connect",

	LOGIN_TITLE: "Enter a username and password for your installation:",
	LOGIN_USERNAME: "New Username",
	LOGIN_PASSWORD: "New Password",
	LOGIN_PASSWORD_CONFIRM: "Confirm Password",
	LOGIN_CREATE: "Create Login",

	PASSWORD_TITLE: "Enter your current username and password:",
	USERNAME_CURRENT: "Current Username",
	PASSWORD_CURRENT: "Current Password",
	PASSWORD_TEXT: "Your username and password will be changed to the following:",
	PASSWORD_CHANGE: "Change Login",

	EDIT_SHARING_TITLE: "Edit Sharing",
	EDIT_SHARING_TEXT: "The sharing-properties of this album will be changed to the following:",
	SHARE_ALBUM_TEXT: "This album will be shared with the following properties:",
	ALBUM_SHARING_CONFIRM: "Save",

	SORT_ALBUM_BY_1: "Sort albums by",
	SORT_ALBUM_BY_2: "in an",
	SORT_ALBUM_BY_3: "order.",

	SORT_ALBUM_SELECT_1: "Creation Time",
	SORT_ALBUM_SELECT_2: "Title",
	SORT_ALBUM_SELECT_3: "Description",
	SORT_ALBUM_SELECT_4: "Public",
	SORT_ALBUM_SELECT_5: "Latest Take Date",
	SORT_ALBUM_SELECT_6: "Oldest Take Date",

	SORT_PHOTO_BY_1: "Sort photos by",
	SORT_PHOTO_BY_2: "in an",
	SORT_PHOTO_BY_3: "order.",

	SORT_PHOTO_SELECT_1: "Upload Time",
	SORT_PHOTO_SELECT_2: "Take Date",
	SORT_PHOTO_SELECT_3: "Title",
	SORT_PHOTO_SELECT_4: "Description",
	SORT_PHOTO_SELECT_5: "Public",
	SORT_PHOTO_SELECT_6: "Star",
	SORT_PHOTO_SELECT_7: "Photo Format",

	SORT_ASCENDING: "Ascending",
	SORT_DESCENDING: "Descending",
	SORT_CHANGE: "Change Sorting",

	DROPBOX_TITLE: "Set Dropbox Key",
	DROPBOX_TEXT: "In order to import photos from your Dropbox, you need a valid drop-ins app key from <a href='https://www.dropbox.com/developers/apps/create'>their website</a>. Generate yourself a personal key and enter it below:",

	LANG_TEXT: "Change Lychee language for:",
	LANG_TITLE: "Change Language",

	CSS_TEXT: "Personalize your CSS:",
	CSS_TITLE: "Change CSS",

	LAYOUT_TYPE: "Layout of photos:",
	LAYOUT_SQUARES: "Square thumbnails",
	LAYOUT_JUSTIFIED: "With aspect, justified",
	LAYOUT_UNJUSTIFIED: "With aspect, unjustified",
	SET_LAYOUT: "Change layout",
	PUBLIC_SEARCH_TEXT: "Public search allowed:",

	IMAGE_OVERLAY_TEXT: "Display image overlay by default:",

	OVERLAY_TYPE: "Photo overlay:",
	OVERLAY_NONE: "None",
	OVERLAY_EXIF: "EXIF data",
	OVERLAY_DESCRIPTION: "Description",
	OVERLAY_DATE: "Date taken",

	MAP_PROVIDER: "Provider of OpenStreetMap tiles:",
	MAP_PROVIDER_WIKIMEDIA: "Wikimedia",
	MAP_PROVIDER_OSM_ORG: "OpenStreetMap.org (no retina)",
	MAP_PROVIDER_OSM_DE: "OpenStreetMap.de (no retina)",
	MAP_PROVIDER_OSM_FR: "OpenStreetMap.fr (no retina)",
	MAP_PROVIDER_RRZE: "University of Erlangen, Germany (only retina)",

	MAP_DISPLAY_TEXT: "Enable maps (provided by OpenStreetMap):",
	MAP_DISPLAY_PUBLIC_TEXT: "Enable maps for public albums (provided by OpenStreetMap):",
	MAP_INCLUDE_SUBALBUMS_TEXT: "Include photos of subalbums on map:",
	LOCATION_DECODING: "Decode GPS data into location name",
	LOCATION_SHOW: "Show location name",
	LOCATION_SHOW_PUBLIC: "Show location name for public mode",

	NSFW_VISIBLE_TEXT_1: "Make Sensitive albums visible by default.",
	NSFW_VISIBLE_TEXT_2: "If the album is public, it is still accessible, just hidden from the view and <b>can be revealed by pressing <kbd>H</kbd></b>.",
	SETTINGS_SUCCESS_NSFW_VISIBLE: "Default sensitive album visibility updated with success.",

	VIEW_NO_RESULT: "No results",
	VIEW_NO_PUBLIC_ALBUMS: "No public albums",
	VIEW_NO_CONFIGURATION: "No configuration",
	VIEW_PHOTO_NOT_FOUND: "Photo not found",

	NO_TAGS: "No Tags",

	UPLOAD_MANAGE_NEW_PHOTOS: "You can now manage your new photo(s).",
	UPLOAD_COMPLETE: "Upload complete",
	UPLOAD_COMPLETE_FAILED: "Failed to upload one or more photos.",
	UPLOAD_IMPORTING: "Importing",
	UPLOAD_IMPORTING_URL: "Importing URL",
	UPLOAD_UPLOADING: "Uploading",
	UPLOAD_FINISHED: "Finished",
	UPLOAD_PROCESSING: "Processing",
	UPLOAD_FAILED: "Failed",
	UPLOAD_FAILED_ERROR: "Upload failed. Server returned an error!",
	UPLOAD_FAILED_WARNING: "Upload failed. Server returned a warning!",
	UPLOAD_SKIPPED: "Skipped",
	UPLOAD_UPDATED: "Updated",
	UPLOAD_IMPORT_SKIPPED_DUPLICATE: "This photo has been skipped because it's already in your library.",
	UPLOAD_IMPORT_RESYNCED_DUPLICATE: "This photo has been skipped because it's already in your library, but its metadata has been updated.",
	UPLOAD_ERROR_CONSOLE: "Please take a look at the console of your browser for further details.",
	UPLOAD_UNKNOWN: "Server returned an unknown response. Please take a look at the console of your browser for further details.",
	UPLOAD_ERROR_UNKNOWN: "Upload failed. Server returned an unkown error!",
	UPLOAD_ERROR_POSTSIZE: "Upload failed. The PHP post_max_size limit is too small!",
	UPLOAD_ERROR_FILESIZE: "Upload failed. The PHP upload_max_filesize limit is too small!",
	UPLOAD_IN_PROGRESS: "Lychee is currently uploading!",
	UPLOAD_IMPORT_WARN_ERR: "The import has been finished, but returned warnings or errors. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_COMPLETE: "Import complete",
	UPLOAD_IMPORT_INSTR: "Please enter the direct link to a photo to import it:",
	UPLOAD_IMPORT: "Import",
	UPLOAD_IMPORT_SERVER: "Importing from server",
	UPLOAD_IMPORT_SERVER_FOLD: "Folder empty or no readable files to process. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_SERVER_INSTR: "This action will import all photos, folders and sub-folders which are located in the following directory. The <b>original files will be deleted</b> after the import when possible.",
	UPLOAD_ABSOLUTE_PATH: "Absolute path to directory",
	UPLOAD_IMPORT_SERVER_EMPT: "Could not start import because the folder was empty!",

	ABOUT_SUBTITLE: "Self-hosted photo-management done right",
	ABOUT_DESCRIPTION: "is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely.",

	URL_COPY_TO_CLIPBOARD: "Copy to clipboard",
	URL_COPIED_TO_CLIPBOARD: "Copied URL to clipboard!",
	PHOTO_DIRECT_LINKS_TO_IMAGES: "Direct links to image files:",
	PHOTO_MEDIUM: "Medium",
	PHOTO_MEDIUM_HIDPI: "Medium HiDPI",
	PHOTO_SMALL: "Thumb",
	PHOTO_SMALL_HIDPI: "Thumb HiDPI",
	PHOTO_THUMB: "Square thumb",
	PHOTO_THUMB_HIDPI: "Square thumb HiDPI",
	PHOTO_LIVE_VIDEO: "Video part of live-photo",
	PHOTO_VIEW: "Lychee Photo View:",

	ERROR_GPX: "Error loading GPX file: ",

	/**
  * Formats a number representing a filesize in bytes as a localized string
  * @param {!number} filesize
  * @returns {string} A formatted and localized string
  */
	printFilesizeLocalized: function printFilesizeLocalized(filesize) {
		var suffix = [" B", " kB", " MB", " GB"];
		var i = 0;
		// Sic! We check if the number is larger than 1000 but divide by 1024 by intention
		// We aim at a number which has at most 3 non-decimal digits, i.e. the result shall be in the interval
		// [1000/1024, 1000) = [0.977, 1000)  (lower bound included, upper bound excluded)
		while (filesize >= 1000.0 && i < suffix.length) {
			filesize = filesize / 1024.0;
			i++;
		}

		// The number of decimal digits is anti-proportional to the number of non-decimal digits
		// In total, there shall always be three digits
		if (filesize >= 100.0) {
			filesize = Math.round(filesize);
		} else if (filesize >= 10.0) {
			filesize = Math.round(filesize * 10.0) / 10.0;
		} else {
			filesize = Math.round(filesize * 100.0) / 100.0;
		}

		return Number(filesize).toLocaleString() + suffix[i];
	},

	/**
  * Converts a JSON encoded date/time into a localized string relative to
  * the original timezone
  *
  * The localized string uses the JS "medium" verbosity.
  * The precise definition of "medium verbosity" depends on the current locale, but for Western languages this
  * means that the date portion is fully printed with digits (e.g. something like 03/30/2021 for English,
  * 30/03/2021 for French and 30.03.2021 for German), and that the time portion is printed with a resolution of
  * seconds with two digits for all parts either in 24h or 12h scheme (e.g. something like 02:24:13pm for English
  * and 14:24:13 for French/German).
  *
  * @param {?string} jsonDateTime
  * @returns {string} A formatted and localized time
  */
	printDateTime: function printDateTime(jsonDateTime) {
		if (typeof jsonDateTime !== "string" || jsonDateTime === "") return "";

		// Unfortunately, the built-in JS Date object is rather dumb.
		// It is only required to support the timezone of the runtime
		// environment and UTC.
		// Moreover, the method `toLocalString` may or may not convert
		// the represented time to the timezone of the runtime environment
		// before formatting it as a string.
		// However, we want to keep the printed time in the original timezone,
		// because this facilitates human interaction with a photo.
		// To this end we apply a "dirty" trick here.
		// We first cut off any explicit timezone indication from the JSON
		// string and only pass a date/time of the form `YYYYMMDDThhmmss` to
		// `Date`.
		// `Date` is required to interpret those time values according to the
		// local timezone (see [MDN Web Docs - Date Time String Format](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse#date_time_string_format)).
		// Most likely, the resulting `Date` object will represent the
		// wrong instant in time (given in seconds since epoch), but we only
		// want to call `toLocalString` which is fine and don't do any time
		// arithmetics.
		// Then we add the original timezone to the string manually.
		var splitDateTime = /^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([,.]\d{1,6})?)([-Z+])(\d{2}:\d{2})?$/.exec(jsonDateTime);
		// The capturing groups are:
		//  - 0: the whole string
		//  - 1: the whole date/time segment incl. fractional seconds
		//  - 2: the fractional seconds (if present)
		//  - 3: the timezone separator, i.e. "Z", "-" or "+" (if present)
		//  - 4: the absolute timezone offset without the sign (if present)
		console.assert(splitDateTime.length === 5, "'jsonDateTime' is not formatted acc. to ISO 8601; passed string was: " + jsonDateTime);
		var locale = "default"; // use the user's browser settings
		var format = { dateStyle: "medium", timeStyle: "medium" };
		var result = new Date(splitDateTime[1]).toLocaleString(locale, format);
		if (splitDateTime[3] === "Z" || splitDateTime[4] === "00:00") {
			result += " UTC";
		} else {
			result += " UTC" + splitDateTime[3] + splitDateTime[4];
		}
		return result;
	},

	/**
  * Converts a JSON encoded date/time into a localized string which only displays month and year.
  *
  * The month is printed as a shortened word with 3/4 letters, the year is printed with 4 digits (e.g. something like
  * "Aug 2020" in English or "Août 2020" in French).
  *
  * @param {?string} jsonDateTime
  * @returns {string} A formatted and localized month and year
  */
	printMonthYear: function printMonthYear(jsonDateTime) {
		if (typeof jsonDateTime !== "string" || jsonDateTime === "") return "";
		var locale = "default"; // use the user's browser settings
		var format = { month: "short", year: "numeric" };
		return new Date(jsonDateTime).toLocaleDateString(locale, format);
	}
};

/**
 * @description This module takes care of the map view of a full album and its sub-albums.
 */

/**
 * @typedef MapProvider
 * @property {string} layer - URL pattern for map tile
 * @property {string} attribution - HTML with attribution
 */

var map_provider_layer_attribution = {
	/**
  * @type {MapProvider}
  */
	Wikimedia: {
		layer: "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png",
		attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.org": {
		layer: "https://{s}.tile.osm.org/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.de": {
		layer: "https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.fr": {
		layer: "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	RRZE: {
		layer: "https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	}
};

var mapview = {
	/** @type {?L.Map} */
	map: null,
	photoLayer: null,
	trackLayer: null,
	/** @type {(?LatLngBounds|?number[][])} */
	bounds: null,
	/** @type {?string} */
	albumID: null,
	/** @type {?string} */
	map_provider: null
};

/**
 * @typedef MapPhotoEntry
 *
 * @property {number} [lat] - latitude
 * @property {number} [lng] - longitude
 * @property {string} [thumbnail] - URL to the thumbnail
 * @property {string} [thumbnail2x] - URL to the high-res thumbnail
 * @property {string} url - URL to the small size-variant; quite a misnomer
 * @property {string} url2x - URL to the small, high-res size-variant; quite a misnomer
 * @property {string} name - the title of the photo
 * @property {string} taken_at - the takedate of the photo, formatted as a locale string
 * @property {string} albumID - the album ID
 * @property {string} photoID - the photo ID
 */

/**
 * @returns {boolean}
 */
mapview.isInitialized = function () {
	return !(mapview.map === null || mapview.photoLayer === null);
};

/**
 * @param {?string} _albumID
 * @param {string} _albumTitle
 *
 * @returns {void}
 */
mapview.title = function (_albumID, _albumTitle) {
	switch (_albumID) {
		case SmartAlbumID.STARRED:
			lychee.setTitle(lychee.locale["STARRED"], false);
			break;
		case SmartAlbumID.PUBLIC:
			lychee.setTitle(lychee.locale["PUBLIC"], false);
			break;
		case SmartAlbumID.RECENT:
			lychee.setTitle(lychee.locale["RECENT"], false);
			break;
		case SmartAlbumID.UNSORTED:
			lychee.setTitle(lychee.locale["UNSORTED"], false);
			break;
		case null:
			lychee.setTitle(lychee.locale["ALBUMS"], false);
			break;
		default:
			lychee.setTitle(_albumTitle ? _albumTitle : lychee.locale["UNTITLED"], false);
			break;
	}
};

/**
 * Opens the map view
 *
 * @param {?string} [albumID=null]
 * @returns {void}
 */
mapview.open = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	// If map functionality is disabled -> do nothing
	if (!lychee.map_display || lychee.publicMode === true && !lychee.map_display_public) {
		loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
		return;
	}

	lychee.animate($("#mapview"), "fadeIn");
	$("#mapview").show();
	header.setMode("map");

	mapview.albumID = albumID;

	// initialize container only once
	if (!mapview.isInitialized()) {
		// Leaflet searches for icon in same directory as js file -> paths need
		// to be overwritten
		delete L.Icon.Default.prototype._getIconUrl;
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: "img/marker-icon-2x.png",
			iconUrl: "img/marker-icon.png",
			shadowUrl: "img/marker-shadow.png"
		});

		// Set initial view to (0,0)
		mapview.map = L.map("leaflet_map_full").setView([0.0, 0.0], 13);

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
			// Mapview has already shown data -> remove only photoLayer and trackLayer showing photos and tracks
			mapview.photoLayer.clear();
			if (mapview.trackLayer !== null) {
				mapview.map.removeLayer(mapview.trackLayer);
			}
		}

		// Reset bounds
		mapview.bounds = null;
	}

	// Define how the photos on the map should look like
	mapview.photoLayer = L.photo.cluster().on("click", function (e) {
		/** @type {MapPhotoEntry} */
		var photo = {
			photoID: e.layer.photo.photoID,
			albumID: e.layer.photo.albumID,
			name: e.layer.photo.name,
			url: e.layer.photo.url,
			url2x: e.layer.photo.url2x,
			taken_at: lychee.locale.printDateTime(e.layer.photo.taken_at)
		};
		var template = "";

		// Retina version if available
		if (photo.url2x !== "") {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'srcset="{url} 1x, {url2x} 2x" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
		} else {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
		}

		e.layer.bindPopup(L.Util.template(template, photo), {
			minWidth: 400
		}).openPopup();
	});

	// Adjusts zoom and position of map to show all images
	var updateZoom = function updateZoom() {
		if (mapview.bounds) {
			mapview.map.fitBounds(mapview.bounds);
		} else {
			mapview.map.fitWorld();
		}
	};

	/**
  * Adds photos to the map.
  *
  * @param {(Album|TagAlbum|PositionData)} album
  *
  * @returns {void}
  */
	var addContentsToMap = function addContentsToMap(album) {
		// check if empty
		if (!album.photos) return;

		/** @type {MapPhotoEntry[]} */
		var photos = [];

		/** @type {?number} */
		var min_lat = null;
		/** @type {?number} */
		var min_lng = null;
		/** @type {?number} */
		var max_lat = null;
		/** @type {?number} */
		var max_lng = null;

		album.photos.forEach(
		/** @param {Photo} element */function (element) {
			if (element.latitude || element.longitude) {
				photos.push({
					lat: element.latitude,
					lng: element.longitude,
					thumbnail: element.size_variants.thumb !== null ? element.size_variants.thumb.url : "img/placeholder.png",
					thumbnail2x: element.size_variants.thumb2x !== null ? element.size_variants.thumb2x.url : null,
					url: element.size_variants.small !== null ? element.size_variants.small.url : element.url,
					url2x: element.size_variants.small2x !== null ? element.size_variants.small2x.url : null,
					name: element.title,
					taken_at: element.taken_at,
					albumID: element.album_id,
					photoID: element.id
				});

				// Update min/max lat/lng
				if (min_lat === null || min_lat > element.latitude) {
					min_lat = element.latitude;
				}
				if (min_lng === null || min_lng > element.longitude) {
					min_lng = element.longitude;
				}
				if (max_lat === null || max_lat < element.latitude) {
					max_lat = element.latitude;
				}
				if (max_lng === null || max_lng < element.longitude) {
					max_lng = element.longitude;
				}
			}
		});

		// Add Photos to map
		mapview.photoLayer.add(photos).addTo(mapview.map);

		if (photos.length > 0) {
			// update map bounds
			var dist_lat = max_lat - min_lat;
			var dist_lng = max_lng - min_lng;
			mapview.bounds = [[min_lat - 0.1 * dist_lat, min_lng - 0.1 * dist_lng], [max_lat + 0.1 * dist_lat, max_lng + 0.1 * dist_lng]];
		}

		// add track
		if (album.track_url) {
			mapview.trackLayer = new L.GPX(album.track_url, {
				async: true,
				marker_options: {
					startIconUrl: null,
					endIconUrl: null,
					shadowUrl: null
				}
			}).on("error", function (e) {
				lychee.error(lycche.locale["ERROR_GPX"] + e.err);
			}).on("loaded", function (e) {
				if (photos.length === 0) {
					// no photos, update map bound to center track
					mapview.bounds = e.target.getBounds();
					updateZoom();
				}
			});
			mapview.trackLayer.addTo(mapview.map);
		}

		// Update Zoom and Position
		updateZoom();
	};

	/**
  * Calls backend, retrieves information about photos and displays them.
  *
  * This function is called recursively to retrieve data for sub-albums.
  * Possible enhancement could be to only have a single ajax call.
  *
  * @param {?string} _albumID
  * @param {boolean} [_includeSubAlbums=true]
  */
	var getAlbumData = function getAlbumData(_albumID) {
		var _includeSubAlbums = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

		/**
   * @param {PositionData} data
   */
		var successHandler = function successHandler(data) {
			addContentsToMap(data);
			mapview.title(_albumID, data.title);
		};

		if (_albumID !== "" && _albumID !== null) {
			// _albumID has been specified
			var params = {
				albumID: _albumID,
				includeSubAlbums: _includeSubAlbums
			};

			api.post("Album::getPositionData", params, successHandler);
		} else {
			// AlbumID is empty -> fetch all photos of all albums
			api.post("Albums::getPositionData", {}, successHandler);
		}
	};

	// If sub-albums are not requested and album.json already has all data,
	// we reuse it
	if (lychee.map_include_subalbums === false && album.json !== null && album.json.photos !== null) {
		addContentsToMap(album.json);
	} else {
		// Not all needed data has been  preloaded - we need to load everything
		getAlbumData(albumID, lychee.map_include_subalbums);
	}

	// Update Zoom and Position once more (for empty map)
	updateZoom();
};

/**
 * @returns {void}
 */
mapview.close = function () {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	lychee.animate($("#mapview"), "fadeOut");
	$("#mapview").hide();
	header.setMode("album");

	// Make album focusable
	tabindex.makeFocusable(lychee.content);
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
mapview.goto = function (elem) {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	var photoID = elem.attr("data-id");
	var albumID = elem.attr("data-album-id");

	if (albumID === "null") albumID = "unsorted";

	lychee.goto(albumID + "/" + photoID);
};

/**
 * @description Select multiple albums or photos.
 */

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
var isSelectKeyPressed = function isSelectKeyPressed(e) {
	return e.metaKey || e.ctrlKey;
};

var multiselect = {
	/** @type {string[]} */
	ids: [],
	albumsSelected: 0,
	photosSelected: 0,
	/** @type {?jQuery} */
	lastClicked: null
};

/**
 * @typedef SelectionPosition
 *
 * @property {number} top
 * @property {number} right
 * @property {number} bottom
 * @property {number} left
 */

/**
 * @type {?SelectionPosition}
 */
multiselect.position = null;

multiselect.bind = function () {
	$(".content").on("mousedown", function (e) {
		if (e.which === 1) multiselect.show(e);
	});

	return true;
};

/**
 * @returns {void}
 */
multiselect.unbind = function () {
	$(".content").off("mousedown");
};

/**
 * @param {string} id
 * @returns {{position: number, selected: boolean}}
 */
multiselect.isSelected = function (id) {
	var pos = multiselect.ids.indexOf(id);

	return {
		selected: pos !== -1,
		position: pos
	};
};

/**
 * @param {jQuery} object
 * @param {string} id
 */
multiselect.toggleItem = function (object, id) {
	if (album.isSmartID(id) || album.isSearchID(id)) return;

	var selected = multiselect.isSelected(id).selected;

	if (selected === false) multiselect.addItem(object, id);else multiselect.removeItem(object, id);
};

/**
 * @param {jQuery} object
 * @param {string} id
 */
multiselect.addItem = function (object, id) {
	if (album.isSmartID(id) || album.isSearchID(id)) return;
	if (!lychee.admin && albums.isShared(id)) return;
	if (multiselect.isSelected(id).selected === true) return;

	var isAlbum = object.hasClass("album");

	if (isAlbum && multiselect.photosSelected > 0 || !isAlbum && multiselect.albumsSelected > 0) {
		loadingBar.show("error", "Please select either albums or photos!");
		return;
	}

	multiselect.ids.push(id);
	multiselect.select(object);

	if (isAlbum) {
		multiselect.albumsSelected++;
	} else {
		multiselect.photosSelected++;
	}

	multiselect.lastClicked = object;
};

/**
 * @param {jQuery} object
 * @param {string} id
 */
multiselect.removeItem = function (object, id) {
	var _multiselect$isSelect = multiselect.isSelected(id),
	    selected = _multiselect$isSelect.selected,
	    position = _multiselect$isSelect.position;

	if (selected === false) return;

	multiselect.ids.splice(position, 1);
	multiselect.deselect(object);

	var isAlbum = object.hasClass("album");

	if (isAlbum) {
		multiselect.albumsSelected--;
	} else {
		multiselect.photosSelected--;
	}

	multiselect.lastClicked = object;
};

/**
 * @param {jQuery.Event} e
 * @param {jQuery} albumObj
 *
 * @returns {void}
 */
multiselect.albumClick = function (e, albumObj) {
	var id = albumObj.attr("data-id");

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (albumObj.hasClass("disabled")) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(albumObj, id);
		} else {
			if (multiselect.albumsSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (albumObj.prevAll(".album").toArray().includes(multiselect.lastClicked[0])) {
					albumObj.prevUntil(multiselect.lastClicked, ".album").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				} else if (albumObj.nextAll(".album").toArray().includes(multiselect.lastClicked[0])) {
					albumObj.nextUntil(multiselect.lastClicked, ".album").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				}
			}

			multiselect.addItem(albumObj, id);
		}
	} else {
		lychee.goto(id);
	}
};

/**
 * @param {jQuery.Event} e
 * @param {jQuery} photoObj
 *
 * @returns {void}
 */
multiselect.photoClick = function (e, photoObj) {
	var id = photoObj.attr("data-id");

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (photoObj.hasClass("disabled")) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(photoObj, id);
		} else {
			if (multiselect.photosSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (photoObj.prevAll(".photo").toArray().includes(multiselect.lastClicked[0])) {
					photoObj.prevUntil(multiselect.lastClicked, ".photo").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				} else if (photoObj.nextAll(".photo").toArray().includes(multiselect.lastClicked[0])) {
					photoObj.nextUntil(multiselect.lastClicked, ".photo").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				}
			}

			multiselect.addItem(photoObj, id);
		}
	} else {
		lychee.goto(album.getID() + "/" + id);
	}
};

/**
 * @param {jQuery.Event} e
 * @param {jQuery} albumObj
 *
 * @returns {void}
 */
multiselect.albumContextMenu = function (e, albumObj) {
	var id = albumObj.attr("data-id");
	var selected = multiselect.isSelected(id).selected;

	if (albumObj.hasClass("disabled")) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.albumMulti(multiselect.ids, e);
	} else {
		contextMenu.album(id, e);
	}
};

/**
 * @param {jQuery.Event} e
 * @param {jQuery} photoObj
 *
 * @returns {void}
 */
multiselect.photoContextMenu = function (e, photoObj) {
	var id = photoObj.attr("data-id");
	var selected = multiselect.isSelected(id).selected;

	if (photoObj.hasClass("disabled")) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.photoMulti(multiselect.ids, e);
	} else if (visible.album() || visible.search()) {
		contextMenu.photo(id, e);
	} else if (visible.photo()) {
		// should not happen... but you never know...
		contextMenu.photo(_photo3.getID(), e);
	} else {
		loadingBar.show("error", "Could not find what you want.");
	}
};

/**
 * @returns {void}
 */
multiselect.clearSelection = function () {
	multiselect.deselect($(".photo.active, .album.active"));
	multiselect.ids = [];
	multiselect.albumsSelected = 0;
	multiselect.photosSelected = 0;
	multiselect.lastClicked = null;
};

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
multiselect.show = function (e) {
	if (!album.isUploadable()) return false;
	if (!visible.albums() && !visible.album()) return false;
	if ($(".album:hover, .photo:hover").length !== 0) return false;
	if (visible.search()) return false;
	if (visible.multiselect()) $("#multiselect").remove();

	_sidebar.setSelectable(false);

	if (!isSelectKeyPressed(e) && !e.shiftKey) {
		multiselect.clearSelection();
	}

	multiselect.position = {
		top: e.pageY,
		right: $(document).width() - e.pageX,
		bottom: $(document).height() - e.pageY,
		left: e.pageX
	};

	$("body").append(build.multiselect(multiselect.position.top, multiselect.position.left));

	$(document).on("mousemove", multiselect.resize).on("mouseup", function (_e) {
		if (_e.which === 1) {
			multiselect.getSelection(_e);
		}
	});
};

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
multiselect.resize = function (e) {
	if (multiselect.position === null) return false;

	// Default CSS
	var newCSS = {
		top: null,
		bottom: null,
		height: null,
		left: null,
		right: null,
		width: null
	};

	if (e.pageY >= multiselect.position.top) {
		newCSS.top = multiselect.position.top;
		newCSS.bottom = "inherit";
		newCSS.height = Math.min(e.pageY, $(document).height() - 3) - multiselect.position.top;
	} else {
		newCSS.top = "inherit";
		newCSS.bottom = multiselect.position.bottom;
		newCSS.height = multiselect.position.top - Math.max(e.pageY, 2);
	}

	if (e.pageX >= multiselect.position.left) {
		newCSS.right = "inherit";
		newCSS.left = multiselect.position.left;
		newCSS.width = Math.min(e.pageX, $(document).width() - 3) - multiselect.position.left;
	} else {
		newCSS.right = multiselect.position.right;
		newCSS.left = "inherit";
		newCSS.width = multiselect.position.left - Math.max(e.pageX, 2);
	}

	// Updated all CSS properties at once
	$("#multiselect").css(newCSS);
};

/**
 * @returns {void}
 */
multiselect.stopResize = function () {
	if (multiselect.position !== null) $(document).off("mousemove mouseup");
};

/**
 * @returns {null|{top: number, left: number, width: number, height: number}}
 */
multiselect.getSize = function () {
	if (!visible.multiselect()) return null;

	var $elem = $("#multiselect");
	var offset = $elem.offset();

	return {
		top: offset.top,
		left: offset.left,
		width: parseFloat($elem.css("width")),
		height: parseFloat($elem.css("height"))
	};
};

/**
 * TODO: This method is called **`get...`** but it doesn't get anything.
 *
 * @param {jQuery.Event} e
 * @returns {void}
 */
multiselect.getSelection = function (e) {
	var size = multiselect.getSize();

	if (visible.contextMenu()) return;
	if (!visible.multiselect()) return;

	$(".photo, .album").each(function () {
		// We select if there's even a slightest overlap.  Overlap between
		// an object and the selection occurs if the left edge of the
		// object is to the left of the right edge of the selection *and*
		// the right edge of the object is to the right of the left edge of
		// the selection; analogous for top/bottom.
		if ($(this).offset().left < size.left + size.width && $(this).offset().left + $(this).width() > size.left && $(this).offset().top < size.top + size.height && $(this).offset().top + $(this).height() > size.top) {
			var id = $(this).attr("data-id");

			if (isSelectKeyPressed(e)) {
				multiselect.toggleItem($(this), id);
			} else {
				multiselect.addItem($(this), id);
			}
		}
	});

	multiselect.hide();
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
multiselect.select = function (elem) {
	elem.addClass("selected");
	elem.addClass("active");
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
multiselect.deselect = function (elem) {
	elem.removeClass("selected");
	elem.removeClass("active");
};

/**
 * Note, identical to {@link multiselect.close}
 * @returns {void}
 */
multiselect.hide = function () {
	_sidebar.setSelectable(true);
	multiselect.stopResize();
	multiselect.position = null;
	lychee.animate($("#multiselect"), "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

/**
 * Note, identical to {@link multiselect.hide}
 * @returns {void}
 */
multiselect.close = function () {
	_sidebar.setSelectable(true);
	multiselect.stopResize();
	multiselect.position = null;
	lychee.animate($("#multiselect"), "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

/**
 * @returns {void}
 */
multiselect.selectAll = function () {
	if (!album.isUploadable()) return;
	if (visible.search()) return;
	if (!visible.albums() && !visible.album) return;
	if (visible.multiselect()) $("#multiselect").remove();

	_sidebar.setSelectable(false);

	multiselect.clearSelection();

	$(".photo").each(function () {
		multiselect.addItem($(this), $(this).attr("data-id"));
	});

	if (multiselect.photosSelected === 0) {
		// There are no pictures.  Try albums then.
		$(".album").each(function () {
			multiselect.addItem($(this), $(this).attr("data-id"));
		});
	}
};

var notifications = {
	/** @type {?EMailData} */
	json: null
};

/**
 * @param {EMailData} params
 * @returns {void}
 */
notifications.update = function (params) {
	if (params.email && params.email.length > 1) {
		var regexp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		if (!regexp.test(String(params.email).toLowerCase())) {
			loadingBar.show("error", "Not a valid email address.");
			return;
		}
	}

	api.post("User::setEmail", params, function () {
		loadingBar.show("success", "Email updated!");
	});
};

notifications.load = function () {
	api.post("User::getEmail", {},
	/** @param {EMailData} data */function (data) {
		notifications.json = data;
		view.notifications.init();
	});
};

/**
 * @description Controls the access to password-protected albums and photos.
 */

var password = {};

/**
 * @callback UnlockSuccessCB
 * @returns {void}
 */

/**
 * Shows the "album unlock"-dialog, tries to unlock the album and calls
 * the provided callback in case of success.
 *
 * @param {string} albumID - the ID of the album which shall be unlocked
 * @param {UnlockSuccessCB} callback - called in case of success
 */
password.getDialog = function (albumID, callback) {
	/**
  * @typedef UnlockDialogResult
  * @property {string} password
  */

	/** @param {UnlockDialogResult} data */
	var action = function action(data) {
		var params = {
			albumID: albumID,
			password: data.password
		};

		api.post("Album::unlock", params, function () {
			basicModal.close();
			callback();
		}, null, function (jqXHR, params2, lycheeException) {
			if ((jqXHR.status === 401 || jqXHR.status === 403) && lycheeException.message.includes("Password is invalid")) {
				basicModal.error("password");
				return true;
			}
			basicModal.close();
			return false;
		});
	};

	var cancel = function cancel() {
		basicModal.close();
		if (!visible.albums() && !visible.album()) lychee.goto();
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["ALBUM_PASSWORD_REQUIRED"] + "\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale["PASSWORD"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["ENTER"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: cancel
			}
		}
	});
};

/**
 * @description Takes care of every action a photo can handle and execute.
 */

var _photo3 = {
	/** @type {?Photo} */
	json: null,
	cache: null,
	/** @type {?boolean} indicates whether the browser supports prefetching of images; `null` if support hasn't been determined yet */
	supportsPrefetch: null,
	/** @type {?LivePhotosKit.Player} */
	livePhotosObject: null
};

/**
 * @returns {?string} - the photo ID
 */
_photo3.getID = function () {
	var id = _photo3.json ? _photo3.json.id : $(".photo:hover, .photo.active").attr("data-id");
	id = typeof id === "string" && /^[-_0-9a-zA-Z]{24}$/.test(id) ? id : null;

	return id;
};

/**
 *
 * @param {string} photoID
 * @param {string} albumID
 * @param {boolean} autoplay - automatically start playback, if the photo is a video or live photo
 *
 * @returns {void}
 */
_photo3.load = function (photoID, albumID, autoplay) {
	/**
  * @param {Photo} data
  * @returns {void}
  */
	var successHandler = function successHandler(data) {
		_photo3.json = data;
		// TODO: `photo.json.original_album_id` is set only, but never read; do we need it?
		_photo3.json.original_album_id = _photo3.json.album_id;
		// TODO: Why do we overwrite the true album ID of a photo, by the externally provided one? I guess we need it, because the album which the user came from might also be a smart album or a tag album. However, in this case I would prefer to leave the `album_id  untouched (don't rename it to `original_album_id`) and call this one `effective_album_id` instead.
		_photo3.json.album_id = albumID;

		if (!visible.photo()) view.photo.show();
		view.photo.init(autoplay);
		lychee.imageview.show();

		if (!lychee.hide_content_during_imgview) {
			setTimeout(function () {
				lychee.content.show();
				tabindex.makeUnfocusable(lychee.content);
			}, 300);
		}
	};

	api.post("Photo::get", {
		photoID: photoID
	}, successHandler);
};

/**
 * @returns {boolean}
 */
_photo3.hasExif = function () {
	return !!_photo3.json.make || !!_photo3.json.model || !!_photo3.json.shutter || !!_photo3.json.aperture || !!_photo3.json.focal || !!_photo3.json.iso;
};

/**
 * @returns {boolean}
 */
_photo3.hasTakestamp = function () {
	return !!_photo3.json.taken_at;
};

/**
 * @returns {boolean}
 */
_photo3.hasDesc = function () {
	return !!_photo3.json.description;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhoto = function () {
	return !!_photo3.json && // In case it's called, but not initialized
	!!_photo3.json.live_photo_url;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhotoInitialized = function () {
	return !!_photo3.livePhotosObject;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhotoPlaying = function () {
	return _photo3.isLivePhotoInitialized() && _photo3.livePhotosObject.isPlaying;
};

/**
 * @returns {void}
 */
_photo3.cycle_display_overlay = function () {
	var oldType = build.check_overlay_type(_photo3.json, lychee.image_overlay_type);
	var newType = build.check_overlay_type(_photo3.json, oldType, true);
	if (oldType !== newType) {
		lychee.image_overlay_type = newType;
		$("#image_overlay").remove();
		var newOverlay = build.overlay_image(_photo3.json);
		if (newOverlay !== "") lychee.imageview.append(newOverlay);
	}
};

/**
 * Preloads the next and previous photos for better response time
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.preloadNextPrev = function (photoID) {
	if (!album.json || !album.json.photos) return;

	var photo = album.getByID(photoID);
	if (!photo) return;

	var imgs = $("img#image");
	// TODO: consider replacing the test for "@2x." by a simple comparison to photo.size_variants.medium2x.url.
	var isUsing2xCurrently = imgs.length > 0 && imgs[0].currentSrc !== null && imgs[0].currentSrc.includes("@2x.");

	$("head [data-prefetch]").remove();

	/**
  * @param {string} preloadID
  * @returns {void}
  */
	var preload = function preload(preloadID) {
		var preloadPhoto = album.getByID(preloadID);
		var href = "";

		if (preloadPhoto.size_variants.medium != null) {
			href = preloadPhoto.size_variants.medium.url;
			if (preloadPhoto.size_variants.medium2x != null && isUsing2xCurrently) {
				// If the currently displayed image uses the 2x variant,
				// chances are that so will the next one.
				href = preloadPhoto.size_variants.medium2x.url;
			}
		} else if (preloadPhoto.type && preloadPhoto.type.indexOf("video") === -1) {
			// Preload the original size, but only if it's not a video
			href = preloadPhoto.size_variants.original.url;
		}

		if (href !== "") {
			if (photo.supportsPrefetch === null) {
				/**
     * Copied from https://www.smashingmagazine.com/2016/02/preload-what-is-it-good-for/
     *
     * TODO: This method should not be defined dynamically, but defined and executed upon initialization once
     *
     * @param {DOMTokenList} tokenList
     * @param {string} token
     * @returns {boolean}
     */
				var DOMTokenListSupports = function DOMTokenListSupports(tokenList, token) {
					try {
						if (!tokenList || !tokenList.supports) {
							return false;
						}
						return tokenList.supports(token);
					} catch (e) {
						if (e instanceof TypeError) {
							console.log("The DOMTokenList doesn't have a supported tokens list");
						} else {
							console.error("That shouldn't have happened");
						}
						return false;
					}
				};
				photo.supportsPrefetch = DOMTokenListSupports(document.createElement("link").relList, "prefetch");
			}

			if (photo.supportsPrefetch) {
				$("head").append(lychee.html(_templateObject51, href));
			} else {
				// According to https://caniuse.com/#feat=link-rel-prefetch,
				// as of mid-2019 it's mainly Safari (both on desktop and mobile)
				new Image().src = href;
			}
		}
	};

	if (photo.next_photo_id) {
		preload(photo.next_photo_id);
	}
	if (photo.previous_photo_id) {
		preload(photo.previous_photo_id);
	}
};

/**
 * @param {number} [animationDuration=300]
 * @param {number} [pauseBetweenUpdated=10]
 * @returns {void}
 */
_photo3.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuration = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (_photo3.isLivePhotoInitialized()) {
			_photo3.livePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuration);
};

/**
 * @param {boolean} animate
 * @returns {void}
 */
_photo3.previous = function (animate) {
	var curPhoto = _photo3.getID() !== null && album.json ? album.getByID(_photo3.getID()) : null;
	if (!curPhoto || !curPhoto.previous_photo_id) return;

	var delay = animate ? 200 : 0;

	if (animate) {
		$("#imageview #image").css({
			WebkitTransform: "translateX(100%)",
			MozTransform: "translateX(100%)",
			transform: "translateX(100%)",
			opacity: 0
		});
	}

	setTimeout(function () {
		_photo3.livePhotosObject = null;
		lychee.goto(album.getID() + "/" + curPhoto.previous_photo_id, false);
	}, delay);
};

/**
 * @param {boolean} animate
 * @returns {void}
 */
_photo3.next = function (animate) {
	var curPhoto = _photo3.getID() !== null && album.json ? album.getByID(_photo3.getID()) : null;
	if (!curPhoto || !curPhoto.next_photo_id) return;

	var delay = animate ? 200 : 0;

	if (animate === true) {
		$("#imageview #image").css({
			WebkitTransform: "translateX(-100%)",
			MozTransform: "translateX(-100%)",
			transform: "translateX(-100%)",
			opacity: 0
		});
	}

	setTimeout(function () {
		_photo3.livePhotosObject = null;
		lychee.goto(album.getID() + "/" + curPhoto.next_photo_id, false);
	}, delay);
};

/**
 * @param {string[]} photoIDs
 * @returns {boolean}
 */
_photo3.delete = function (photoIDs) {
	var action = {};
	var cancel = {};
	var msg = "";
	var photoTitle = "";

	if (photoIDs.length === 1) {
		// Get title if only one photo is selected
		if (visible.photo()) photoTitle = _photo3.json.title;else photoTitle = album.getByID(photoIDs[0]).title;

		// Fallback for photos without a title
		if (!photoTitle) photoTitle = lychee.locale["UNTITLED"];
	}

	action.fn = function () {
		var nextPhotoID = null;
		var previousPhotoID = null;

		basicModal.close();

		photoIDs.forEach(function (id, index) {
			// Change reference for the next and previous photo
			var curPhoto = album.getByID(id);
			if (curPhoto.next_photo_id !== null || curPhoto.previous_photo_id !== null) {
				nextPhotoID = curPhoto.next_photo_id;
				previousPhotoID = curPhoto.previous_photo_id;

				if (previousPhotoID !== null) {
					album.getByID(previousPhotoID).next_photo_id = nextPhotoID;
				}
				if (nextPhotoID !== null) {
					album.getByID(nextPhotoID).previous_photo_id = previousPhotoID;
				}
			}

			album.deleteByID(id);
			view.album.content.delete(id, index === photoIDs.length - 1);
		});

		albums.refresh();

		// Go to next photo if there is a next photo and
		// next photo is not the current one. Also try the previous one.
		// Show album otherwise.
		if (visible.photo()) {
			if (nextPhotoID !== null && nextPhotoID !== _photo3.getID()) {
				lychee.goto(album.getID() + "/" + nextPhotoID);
			} else if (previousPhotoID !== null && previousPhotoID !== _photo3.getID()) {
				lychee.goto(album.getID() + "/" + previousPhotoID);
			} else {
				lychee.goto(album.getID());
			}
		} else if (!visible.albums()) {
			lychee.goto(album.getID());
		}

		api.post("Photo::delete", { photoIDs: photoIDs });
	};

	if (photoIDs.length === 1) {
		action.title = lychee.locale["PHOTO_DELETE"];
		cancel.title = lychee.locale["PHOTO_KEEP"];

		msg = lychee.html(_templateObject52, lychee.locale["PHOTO_DELETE_1"], photoTitle, lychee.locale["PHOTO_DELETE_2"]);
	} else {
		action.title = lychee.locale["PHOTO_DELETE"];
		cancel.title = lychee.locale["PHOTO_KEEP"];

		msg = lychee.html(_templateObject53, lychee.locale["PHOTO_DELETE_ALL_1"], photoIDs.length, lychee.locale["PHOTO_DELETE_ALL_2"]);
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: "red"
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

/**
 *
 * @param {string[]} photoIDs
 * @returns {void}
 */
_photo3.setTitle = function (photoIDs) {
	var oldTitle = "";
	var msg = "";

	if (photoIDs.length === 1) {
		// Get old title if only one photo is selected
		if (_photo3.json) oldTitle = _photo3.json.title;else if (album.json) oldTitle = album.getByID(photoIDs[0]).title;
	}

	/**
  * @param {{title: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title ? data.title : null;

		if (visible.photo()) {
			_photo3.json.title = newTitle;
			view.photo.title();
		}

		photoIDs.forEach(function (id) {
			// TODO: The line below looks suspicious: It is inconsistent to the code some lines above.
			album.getByID(id).title = newTitle;
			view.album.content.title(id);
		});

		api.post("Photo::setTitle", {
			photoIDs: photoIDs,
			title: newTitle
		});
	};

	var input = lychee.html(_templateObject54, oldTitle);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject5, lychee.locale["PHOTO_NEW_TITLE"], input);else msg = lychee.html(_templateObject55, lychee.locale["PHOTOS_NEW_TITLE_1"], photoIDs.length, lychee.locale["PHOTOS_NEW_TITLE_2"], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_TITLE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 *
 * @param {string[]} photoIDs IDs of photos to be copied
 * @param {?string} albumID ID of destination album; `null` means root album
 * @returns {void}
 */
_photo3.copyTo = function (photoIDs, albumID) {
	api.post("Photo::duplicate", {
		photoIDs: photoIDs,
		albumID: albumID
	}, function () {
		return album.reload();
	});
};

/**
 * @param {string[]} photoIDs
 * @param {string} albumID
 * @returns {void}
 */
_photo3.setAlbum = function (photoIDs, albumID) {
	var nextPhotoID = null;
	var previousPhotoID = null;

	photoIDs.forEach(function (id, index) {
		// Change reference for the next and previous photo
		var curPhoto = album.getByID(id);
		if (curPhoto.next_photo_id !== null || curPhoto.previous_photo_id !== null) {
			nextPhotoID = curPhoto.next_photo_id;
			previousPhotoID = curPhoto.previous_photo_id;

			if (previousPhotoID !== null) {
				album.getByID(previousPhotoID).next_photo_id = nextPhotoID;
			}
			if (nextPhotoID !== null) {
				album.getByID(nextPhotoID).previous_photo_id = previousPhotoID;
			}
		}

		album.deleteByID(id);
		view.album.content.delete(id, index === photoIDs.length - 1);
	});

	albums.refresh();

	// Go to next photo if there is a next photo and
	// next photo is not the current one. Also try the previous one.
	// Show album otherwise.
	if (visible.photo()) {
		if (nextPhotoID !== null && nextPhotoID !== _photo3.getID()) {
			lychee.goto(album.getID() + "/" + nextPhotoID);
		} else if (previousPhotoID !== null && previousPhotoID !== _photo3.getID()) {
			lychee.goto(album.getID() + "/" + previousPhotoID);
		} else {
			lychee.goto(album.getID());
		}
	}

	api.post("Photo::setAlbum", {
		photoIDs: photoIDs,
		albumID: albumID
	}, function () {
		// We only really need to do anything here if the destination
		// is a (possibly nested) subalbum of the current album; but
		// since we have no way of figuring it out (albums.json is
		// null), we need to reload.
		if (visible.album()) {
			album.reload();
		}
	});
};

/**
 * Toggles the star-property of the currently visible photo.
 *
 * @returns {void}
 */
_photo3.toggleStar = function () {
	_photo3.json.is_starred = !_photo3.json.is_starred;
	view.photo.star();
	albums.refresh();

	api.post("Photo::setStar", {
		photoIDs: [_photo3.json.id],
		is_starred: _photo3.json.is_starred
	});
};

/**
 * Sets the star-property of the given photos.
 *
 * @param {string[]} photoIDs
 * @param {boolean} isStarred
 * @returns {void}
 */
_photo3.setStar = function (photoIDs, isStarred) {
	photoIDs.forEach(function (id) {
		album.getByID(id).is_starred = isStarred;
		view.album.content.star(id);
	});

	albums.refresh();

	api.post("Photo::setStar", {
		photoIDs: photoIDs,
		is_starred: isStarred
	});
};

/**
 * Edits the protection policy of a photo.
 *
 * This method is a misnomer, it does not only set the policy, it also creates
 * and handles the edit dialog
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setProtectionPolicy = function (photoID) {
	var msg_switch = lychee.html(_templateObject56, lychee.locale["PHOTO_PUBLIC"], lychee.locale["PHOTO_PUBLIC_EXPL"]);

	var msg_choices = lychee.html(_templateObject57, build.iconic("check"), lychee.locale["PHOTO_FULL"], lychee.locale["PHOTO_FULL_EXPL"], build.iconic("check"), lychee.locale["PHOTO_HIDDEN"], lychee.locale["PHOTO_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["PHOTO_DOWNLOADABLE"], lychee.locale["PHOTO_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE"], lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_PASSWORD_PROT"], lychee.locale["PHOTO_PASSWORD_PROT_EXPL"]);

	if (_photo3.json.is_public === 2) {
		// Public album. We can't actually change anything, but we will
		// display the current settings.

		var msg = lychee.html(_templateObject58, lychee.locale["PHOTO_NO_EDIT_SHARING_TEXT"], msg_switch, msg_choices);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="is_public"]').prop("checked", true);
		if (album.json) {
			if (album.json.grants_full_photo) {
				$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", true);
			}
			// Photos in public albums are never hidden as such.  It's the
			// album that's hidden.  Or is that distinction irrelevant to end
			// users?
			if (album.json.is_downloadable) {
				$('.basicModal .choice input[name="is_downloadable"]').prop("checked", true);
			}
			if (album.json.has_password) {
				$('.basicModal .choice input[name="has_password"]').prop("checked", true);
			}
		}

		$(".basicModal .switch input").attr("disabled", true);
		$(".basicModal .switch .label").addClass("label--disabled");
	} else {
		// Private album -- each photo can be shared individually.

		var _msg = lychee.html(_templateObject59, msg_switch, lychee.locale["PHOTO_EDIT_GLOBAL_SHARING_TEXT"], msg_choices);

		// TODO: Actually, the action handler receives an object with values of all input fields. There is no need to run use a jQuery-selector
		var action = function action() {
			/**
    * Note: `newIsPublic` must be of type `number`, because `photo.is_public` is a number, too
    * @type {number}
    */
			var newIsPublic = $('.basicModal .switch input[name="is_public"]:checked').length;

			if (newIsPublic !== _photo3.json.is_public) {
				if (visible.photo()) {
					_photo3.json.is_public = newIsPublic;
					view.photo.public();
				}

				album.getByID(photoID).is_public = newIsPublic;
				view.album.content.public(photoID);

				albums.refresh();

				api.post("Photo::setPublic", {
					photoID: photoID,
					is_public: newIsPublic !== 0
				});
			}

			basicModal.close();
		};

		basicModal.show({
			body: _msg,
			buttons: {
				action: {
					title: lychee.locale["PHOTO_SHARING_CONFIRM"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="is_public"]').on("click", function () {
			if ($(this).prop("checked") === true) {
				if (lychee.full_photo) {
					$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", true);
				}
				if (lychee.public_photos_hidden) {
					$('.basicModal .choice input[name="requires_link"]').prop("checked", true);
				}
				if (lychee.downloadable) {
					$('.basicModal .choice input[name="is_downloadable"]').prop("checked", true);
				}
				if (lychee.share_button_visible) {
					$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", true);
				}
				// Photos shared individually can't be password-protected.
			} else {
				$(".basicModal .choice input").prop("checked", false);
			}
		});

		if (_photo3.json.is_public === 1) {
			$('.basicModal .switch input[name="is_public"]').click();
		}
	}
};

/**
 * Edits the description of a photo.
 *
 * This method is a misnomer, it does not only set the description, it also creates and handles the edit dialog
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setDescription = function (photoID) {
	var oldDescription = _photo3.json.description ? _photo3.json.description : "";

	/**
  * @param {{description: string}} data
  */
	var action = function action(data) {
		basicModal.close();

		var description = data.description ? data.description : null;

		if (visible.photo()) {
			_photo3.json.description = description;
			view.photo.description();
		}

		api.post("Photo::setDescription", {
			photoID: photoID,
			description: description
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject60, lychee.locale["PHOTO_NEW_DESCRIPTION"], lychee.locale["PHOTO_DESCRIPTION"], oldDescription),
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_DESCRIPTION"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} photoIDs
 * @returns {void}
 */
_photo3.editTags = function (photoIDs) {
	/** @type {string[]} */
	var oldTags = [];

	// Get tags
	if (visible.photo()) oldTags = _photo3.json.tags.sort();else if (visible.album() && photoIDs.length === 1) oldTags = album.getByID(photoIDs[0]).tags.sort();else if (visible.search() && photoIDs.length === 1) oldTags = album.getByID(photoIDs[0]).tags.sort();else if (visible.album() && photoIDs.length > 1) {
		oldTags = album.getByID(photoIDs[0]).tags.sort();
		var areIdentical = photoIDs.every(function (id) {
			var oldTags2 = album.getByID(id).tags.sort();
			if (oldTags.length !== oldTags2.length) return false;
			for (var tagIdx = 0; tagIdx !== oldTags.length; tagIdx++) {
				if (oldTags[tagIdx] !== oldTags2[tagIdx]) return false;
			}
			return true;
		});
		if (!areIdentical) oldTags = [];
	}

	/**
  * @param {{tags: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		basicModal.close();
		var newTags = data.tags.split(",").map(function (tag) {
			return tag.trim();
		}).filter(function (tag) {
			return tag !== "" && tag.indexOf(",") === -1;
		}).sort();
		_photo3.setTags(photoIDs, newTags);
	};

	var input = lychee.html(_templateObject61, oldTags.join(", "));

	var msg = photoIDs.length === 1 ? lychee.html(_templateObject5, lychee.locale["PHOTO_NEW_TAGS"], input) : lychee.html(_templateObject55, lychee.locale["PHOTO_NEW_TAGS_1"], photoIDs.length, lychee.locale["PHOTO_NEW_TAGS_2"], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_TAGS"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} photoIDs
 * @param {string[]} tags
 * @returns {void}
 */
_photo3.setTags = function (photoIDs, tags) {
	if (visible.photo()) {
		_photo3.json.tags = tags;
		view.photo.tags();
	}

	photoIDs.forEach(function (id) {
		album.getByID(id).tags = tags;
	});

	api.post("Photo::setTags", {
		photoIDs: photoIDs,
		tags: tags
	}, function () {
		// If we have any tag albums, force a refresh.
		if (albums.json && albums.json.tag_albums.length !== 0) {
			albums.refresh();
		}
	});
};

/**
 * Deletes the tag at the given index from the photo.
 *
 * @param {string} photoID
 * @param {number} index
 */
_photo3.deleteTag = function (photoID, index) {
	_photo3.json.tags.splice(index, 1);
	_photo3.setTags([photoID], _photo3.json.tags);
};

/**
 * @param {string} photoID
 * @param {string} service - one out of `"twitter"`, `"facebook"`, `"mail"` or `"dropbox"`
 * @returns {void}
 */
_photo3.share = function (photoID, service) {
	if (!_photo3.json.is_share_button_visible) {
		return;
	}

	var url = _photo3.getViewLink(photoID);

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(_photo3.json.title));
			break;
		case "mail":
			location.href = "mailto:?subject=" + encodeURI(_photo3.json.title) + "&body=" + encodeURI(url);
			break;
		case "dropbox":
			lychee.loadDropbox(function () {
				var filename = _photo3.json.title + "." + _photo3.getDirectLink().split(".").pop();
				Dropbox.save(_photo3.getDirectLink(), filename);
			});
			break;
	}
};

/**
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setLicense = function (photoID) {
	/**
  * @param {{license: string}} data
  */
	var action = function action(data) {
		basicModal.close();
		var license = data.license;

		var params = {
			photoID: photoID,
			license: license
		};

		api.post("Photo::setLicense", params, function () {
			// update the photo JSON and reload the license in the sidebar
			_photo3.json.license = params.license;
			view.photo.license();
		});
	};

	var msg = lychee.html(_templateObject8, lychee.locale["PHOTO_LICENSE"], lychee.locale["PHOTO_LICENSE_NONE"], lychee.locale["PHOTO_RESERVED"], lychee.locale["PHOTO_LICENSE_HELP"]);

	basicModal.show({
		body: msg,
		callback: function callback() {
			$("select#license").val(_photo3.json.license === "" ? "none" : _photo3.json.license);
		},
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_LICENSE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} photoIDs
 * @param {?string} [kind=null] - the type of size variant; one out of
 *                                `"FULL"`, `"MEDIUM2X"`, `"MEDIUM"`,
 *                                `"SMALL2X"`, `"SMALL"`, `"THUMB2X"` or
 *                                `"THUMB"`,
 * @returns {void}
 */
_photo3.getArchive = function (photoIDs) {
	var kind = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	if (photoIDs.length === 1 && kind === null) {
		// For a single photo, allow to pick the kind via a dialog box.

		var myPhoto = void 0;

		if (_photo3.json && _photo3.json.id === photoIDs[0]) {
			myPhoto = _photo3.json;
		} else {
			myPhoto = album.getByID(photoIDs[0]);
		}

		/**
   * @param {string} id - the ID of the button, same semantics as "kind"
   * @param {string} label - the caption on the button
   * @returns {string} - HTML
   */
		var buildButton = function buildButton(id, label) {
			return lychee.html(_templateObject62, id, lychee.locale["DOWNLOAD"], build.iconic("cloud-download"), label);
		};

		var msg = lychee.html(_templateObject63);

		if (myPhoto.size_variants.original.url) {
			msg += buildButton("FULL", lychee.locale["PHOTO_FULL"] + " (" + myPhoto.size_variants.original.width + "x" + myPhoto.size_variants.original.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.original.filesize) + ")");
		}
		if (myPhoto.live_photo_url !== null) {
			msg += buildButton("LIVEPHOTOVIDEO", "" + lychee.locale["PHOTO_LIVE_VIDEO"]);
		}
		if (myPhoto.size_variants.medium2x !== null) {
			msg += buildButton("MEDIUM2X", lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + myPhoto.size_variants.medium2x.width + "x" + myPhoto.size_variants.medium2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.medium2x.filesize) + ")");
		}
		if (myPhoto.size_variants.medium !== null) {
			msg += buildButton("MEDIUM", lychee.locale["PHOTO_MEDIUM"] + " (" + myPhoto.size_variants.medium.width + "x" + myPhoto.size_variants.medium.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.medium.filesize) + ")");
		}
		if (myPhoto.size_variants.small2x !== null) {
			msg += buildButton("SMALL2X", lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + myPhoto.size_variants.small2x.width + "x" + myPhoto.size_variants.small2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.small2x.filesize) + ")");
		}
		if (myPhoto.size_variants.small !== null) {
			msg += buildButton("SMALL", lychee.locale["PHOTO_SMALL"] + " (" + myPhoto.size_variants.small.width + "x" + myPhoto.size_variants.small.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.small.filesize) + ")");
		}
		if (myPhoto.size_variants.thumb2x !== null) {
			msg += buildButton("THUMB2X", lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + myPhoto.size_variants.thumb2x.width + "x" + myPhoto.size_variants.thumb2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.thumb2x.filesize) + ")");
		}
		if (myPhoto.size_variants.thumb !== null) {
			msg += buildButton("THUMB", lychee.locale["PHOTO_THUMB"] + " (" + myPhoto.size_variants.thumb.width + "x" + myPhoto.size_variants.thumb.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.thumb.filesize) + ")");
		}

		msg += lychee.html(_templateObject64);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		$(".downloads .basicModal__button").on(lychee.getEventName(), function () {
			var kind = this.id;
			basicModal.close();
			_photo3.getArchive(photoIDs, kind);
		});
	} else {
		location.href = "api/Photo::getArchive?photoIDs=" + photoIDs.join() + "&kind=" + kind;
	}
};

/**
 * Shows a dialog to share the view URL via a QR code.
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.qrCode = function (photoID) {
	/** @type {?Photo} */
	var myPhoto = _photo3.json && _photo3.json.id === photoID ? _photo3.json : album.getByID(photoID);

	if (myPhoto == null) {
		lychee.error("Error: photo " + photoID + " not found !");
		return;
	}

	var msg = lychee.html(_templateObject12);

	basicModal.show({
		body: msg,
		callback: function callback() {
			qrcode = $("#qr-code");
			QrCreator.render({
				text: _photo3.getViewLink(myPhoto.id),
				radius: 0.0,
				ecLevel: "H",
				fill: "#000000",
				background: "#FFFFFF",
				size: qrcode.width()
			}, qrcode[0]);
		},
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @returns {string}
 */
_photo3.getDirectLink = function () {
	return _photo3.json && _photo3.json.size_variants && _photo3.json.size_variants.original && _photo3.json.size_variants.original.url ? _photo3.json.size_variants.original.url : "";
};

/**
 * @param {string} photoID
 * @returns {string}
 */
_photo3.getViewLink = function (photoID) {
	return lychee.getBaseUrl() + "view?p=" + photoID;
};

/**
 * @param photoID
 * @returns {void}
 */
_photo3.showDirectLinks = function (photoID) {
	if (!_photo3.json || _photo3.json.id !== photoID) {
		return;
	}

	/**
  * @param {string} label
  * @param {string} url
  * @returns {string} - HTML
  */
	var buildLine = function buildLine(label, url) {
		return lychee.html(_templateObject65, label, url, lychee.locale["URL_COPY_TO_CLIPBOARD"], build.iconic("copy", "ionicons"));
	};

	var msg = lychee.html(_templateObject66, buildLine(lychee.locale["PHOTO_VIEW"], _photo3.getViewLink(photoID)), lychee.locale["PHOTO_DIRECT_LINKS_TO_IMAGES"]);

	if (_photo3.json.size_variants.original.url) {
		msg += buildLine(lychee.locale["PHOTO_FULL"] + " (" + _photo3.json.size_variants.original.width + "x" + _photo3.json.size_variants.original.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.original.url);
	}
	if (_photo3.json.size_variants.medium2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + _photo3.json.size_variants.medium2x.width + "x" + _photo3.json.size_variants.medium2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.medium2x.url);
	}
	if (_photo3.json.size_variants.medium !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM"] + " (" + _photo3.json.size_variants.medium.width + "x" + _photo3.json.size_variants.medium.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.medium.url);
	}
	if (_photo3.json.size_variants.small2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + _photo3.json.size_variants.small2x.width + "x" + _photo3.json.size_variants.small2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.small2x.url);
	}
	if (_photo3.json.size_variants.small !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL"] + " (" + _photo3.json.size_variants.small.width + "x" + _photo3.json.size_variants.small.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.small.url);
	}
	if (_photo3.json.size_variants.thumb2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + _photo3.json.size_variants.thumb2x.width + "x" + _photo3.json.size_variants.thumb2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.thumb2x.url);
	}
	if (_photo3.json.size_variants.thumb !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB"] + " (" + _photo3.json.size_variants.thumb.width + "x" + _photo3.json.size_variants.thumb.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.thumb.url);
	}
	if (_photo3.json.live_photo_url) {
		msg += buildLine(" " + lychee.locale["PHOTO_LIVE_VIDEO"] + " ", lychee.getBaseUrl() + _photo3.json.live_photo_url);
	}

	msg += lychee.html(_templateObject67);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});

	// Ensure that no input line is selected on opening.
	$(".basicModal input:focus").blur();

	$(".directLinks .basicModal__button").on(lychee.getEventName(), function () {
		navigator.clipboard.writeText($(this).prev().val()).then(function () {
			return loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
		});
	});
};

/**
 * @description Takes care of every action a photoeditor can handle and execute.
 */

photoeditor = {};

/**
 * @param {string} photoID
 * @param {number} direction - either `1` or `-1`
 * @returns {void}
 */
photoeditor.rotate = function (photoID, direction) {
	api.post("PhotoEditor::rotate", {
		photoID: photoID,
		direction: direction
	},
	/** @param {Photo} data */
	function (data) {
		_photo3.json = data;
		// TODO: `photo.json.original_album_id` is set only, but never read; do we need it?
		_photo3.json.original_album_id = _photo3.json.album_id;
		if (album.json) {
			// TODO: Why do we overwrite the true album ID of a photo, by the externally provided one? I guess we need it, because the album which the user came from might also be a smart album or a tag album. However, in this case I would prefer to leave the `album_id  untouched (don't rename it to `original_album_id`) and call this one `effective_album_id` instead.
			_photo3.json.album_id = album.json.id;
		}

		var image = $("img#image");
		if (_photo3.json.size_variants.medium2x !== null) {
			image.prop("srcset", _photo3.json.size_variants.medium.url + " " + _photo3.json.size_variants.medium.width + "w, " + _photo3.json.size_variants.medium2x.url + " " + _photo3.json.size_variants.medium2x.width + "w");
		} else {
			image.prop("srcset", "");
		}
		image.prop("src", _photo3.json.size_variants.medium !== null ? _photo3.json.size_variants.medium.url : _photo3.json.size_variants.original.url);
		view.photo.onresize();
		view.photo.sidebar();
		album.updatePhoto(data);
	});
};

/**
 * @description Searches through your photos and albums.
 */

/**
 * The ID of the search album
 *
 * Constant `'search'`.
 *
 * @type {string}
 */
var SearchAlbumID = "search";

/**
 * @typedef SearchAlbum
 *
 * A "virtual" album which holds the search results in a form which is
 * mostly compatible with the other album types, i.e.
 * {@link Album}, {@link TagAlbum} and {@link SmartAlbum}.
 *
 * @property {string}  id                       - always equals `SearchAlbumID`
 * @property {string}  title                    - always equals `lychee.locale["SEARCH_RESULTS"]`
 * @property {Photo[]} photos                   - the found photos
 * @property {Album[]} albums                   - the found albums
 * @property {TagAlbum[]} tag_albums            - the found tag albums
 * @property {?Thumb}  thumb                    - always `null`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_public                - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_downloadable          - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_share_button_visible  - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 */

/**
 * The search object
 */
var search = {
	/** @type {?SearchResult} */
	json: null
};

/**
 * @param {string} term
 * @returns {void}
 */
search.find = function (term) {
	if (term.trim() === "") return;

	/** @param {SearchResult} data */
	var successHandler = function successHandler(data) {
		// Do nothing, if search result is identical to previous result
		if (search.json && search.json.checksum === data.checksum) {
			return;
		}

		search.json = data;

		// Create and assign a `SearchAlbum`
		album.json = {
			id: SearchAlbumID,
			title: lychee.locale["SEARCH_RESULTS"],
			photos: search.json.photos,
			albums: search.json.albums,
			tag_albums: search.json.tag_albums,
			thumb: null,
			is_public: false,
			is_downloadable: false,
			is_share_button_visible: false
		};

		var albumsData = "";
		var photosData = "";

		// Build HTML for album
		search.json.tag_albums.forEach(function (album) {
			albums.parse(album);
			albumsData += build.album(album);
		});
		search.json.albums.forEach(function (album) {
			albums.parse(album);
			albumsData += build.album(album);
		});

		// Build HTML for photo
		search.json.photos.forEach(function (photo) {
			photosData += build.photo(photo);
		});

		var albums_divider = lychee.locale["ALBUMS"];
		var photos_divider = lychee.locale["PHOTOS"];

		if (albumsData !== "") albums_divider += " (" + (search.json.tag_albums.length + search.json.albums.length) + ")";
		if (photosData !== "") {
			photos_divider += " (" + search.json.photos.length + ")";
			if (lychee.layout === 1) {
				photosData = '<div class="justified-layout">' + photosData + "</div>";
			} else if (lychee.layout === 2) {
				photosData = '<div class="unjustified-layout">' + photosData + "</div>";
			}
		}

		// 1. No albums and photos
		// 2. Only photos
		// 3. Only albums
		// 4. Albums and photos
		var html = albumsData === "" && photosData === "" ? "" : albumsData === "" ? build.divider(photos_divider) + photosData : photosData === "" ? build.divider(albums_divider) + albumsData : build.divider(albums_divider) + albumsData + build.divider(photos_divider) + photosData;

		$(".no_content").remove();
		lychee.animate($(".content"), "contentZoomOut");

		setTimeout(function () {
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			if (visible.mapview()) mapview.close();

			header.setMode("albums");

			if (html === "") {
				lychee.content.html("");
				$("body").append(build.no_content("magnifying-glass"));
			} else {
				lychee.content.html(html);
				// Here we exploit the layout method of an album although
				// the search result is not a proper album.
				// It would be much better to have a component like
				// `view.photos` (note the plural form) which takes care of
				// all photo listings independent of the surrounding "thing"
				// (i.e. regular album, tag album, search result)
				view.album.content.justify(search.json.photos);
				lychee.animate(lychee.content, "contentZoomIn");
			}
			lychee.setTitle(lychee.locale["SEARCH_RESULTS"], false);

			$(window).scrollTop(0);
		}, 300);
	};

	/** @returns {void} */
	var timeoutHandler = function timeoutHandler() {
		if (header.dom(".header__search").val().length !== 0) {
			api.post("Search::run", { term: term }, successHandler);
		} else {
			search.reset();
		}
	};

	clearTimeout($(window).data("timeout"));
	$(window).data("timeout", setTimeout(timeoutHandler, 250));
};

search.reset = function () {
	header.dom(".header__search").val("");
	$(".no_content").remove();

	if (search.json !== null) {
		// Trash data
		album.json = null;
		_photo3.json = null;
		search.json = null;

		lychee.animate($(".divider"), "fadeOut");
		lychee.goto();
	}
};

/**
 * @description Lets you change settings.
 */

var settings = {};

/**
 * @returns {void}
 */
settings.open = function () {
	view.settings.init();
};

settings.createLogin = function () {
	/**
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  * @param {Object} params the original JSON parameters of the request
  * @param {?LycheeException} lycheeException the Lychee exception
  * @returns {boolean}
  */
	var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
		var htmlBody = "<p>" + lychee.locale["ERROR_LOGIN"] + "</p>";
		htmlBody += lycheeException ? "<p>" + lycheeException.message + "</p>" : "";
		basicModal.show({
			body: htmlBody,
			buttons: {
				action: {
					title: lychee.locale["RETRY"],
					fn: function fn() {
						return settings.createLogin();
					}
				}
			}
		});
		return true;
	};

	/**
  * @typedef SetLoginDialogResult
  *
  * @property {string} username
  * @property {string} password
  * @property {string} confirm
  */

	/**
  * @param {SetLoginDialogResult} data
  * @returns {void}
  */
	var action = function action(data) {
		var username = data.username;
		var password = data.password;
		var confirm = data.confirm;

		if (!username.trim()) {
			basicModal.error("username");
			return;
		}

		if (!password.trim()) {
			basicModal.error("password");
			return;
		}

		if (password !== confirm) {
			basicModal.error("confirm");
			return;
		}

		basicModal.close();

		var params = {
			username: username,
			password: password
		};

		api.post("Settings::setLogin", params, null, null, errorHandler);
	};

	var msg = "\n\t\t<p>\n\t\t\t" + lychee.locale["LOGIN_TITLE"] + "\n\t\t\t<input name='username' class='text' type='text' placeholder='" + lychee.locale["LOGIN_USERNAME"] + "' value=''>\n\t\t\t<input name='password' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD"] + "' value=''>\n\t\t\t<input name='confirm' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD_CONFIRM"] + "' value=''>\n\t\t</p>";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["LOGIN_CREATE"],
				fn: action
			}
		}
	});
};

/**
 * A dictionary of (name,value)-pairs of the form.
 *
 * @typedef SettingsFormData
 *
 * @type {Object.<string, (string|number|Array)>}
 */

/**
 * From https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
 *
 * @param {string} formSelector
 * @returns {SettingsFormData}
 */
settings.getValues = function (formSelector) {
	var values = {};

	/** @type {?NodeListOf<HTMLInputElement>} */
	var inputElements = document.querySelectorAll(formSelector + " input[name]");

	// Get value from all inputs
	inputElements.forEach(function (inputElement) {
		switch (inputElement.type) {
			case "checkbox":
			case "radio":
				values[inputElement.name] = inputElement.checked;
				break;
			case "number":
			case "range":
				values[inputElement.name] = parseInt(inputElement.value, 10);
				break;
			case "file":
				values[inputElement.name] = inputElement.files;
				break;
			default:
				switch (inputElement.getAttribute("inputmode")) {
					case "numeric":
						values[inputElement.name] = parseInt(inputElement.value, 10);
						break;
					case "decimal":
						values[inputElement.name] = parseFloat(inputElement.value);
						break;
					default:
						values[inputElement.name] = inputElement.value;
				}
		}
	});

	/** @type {?NodeListOf<HTMLSelectElement>} */
	var selectElements = document.querySelectorAll(formSelector + " select[name]");

	// Get name of selected option from all selects
	selectElements.forEach(function (selectElement) {
		values[selectElement.name] = selectElement.selectedIndex !== -1 ? selectElement.options[selectElement.selectedIndex].value : null;
	});

	return values;
};

/**
 * @callback SettingClickCB
 *
 * @param {SettingsFormData} formData
 * @returns {void}
 */

/**
 * From https://github.com/electerious/basicModal/blob/master/src/scripts/main.js.
 *
 * @param {string} inputSelector
 * @param {string} formSelector
 * @param {SettingClickCB} settingClickCB
 */
settings.bind = function (inputSelector, formSelector, settingClickCB) {
	$(inputSelector).on("click", function () {
		settingClickCB(settings.getValues(formSelector));
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLogin = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		$("input[name=username]").addClass("error");
		return;
	} else {
		$("input[name=username]").removeClass("error");
	}

	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		$("input[name=password]").addClass("error");
		return;
	} else {
		$("input[name=password]").removeClass("error");
	}

	if (params.password !== params.confirm) {
		loadingBar.show("error", "new password does not match.");
		$("input[name=confirm]").addClass("error");
		return;
	} else {
		$("input[name=confirm]").removeClass("error");
	}

	api.post("Settings::setLogin", params, function () {
		$("input[name]").removeClass("error");
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LOGIN"]);
		view.settings.content.clearLogin();
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeSorting = function (params) {
	api.post("Settings::setSorting", params, function () {
		lychee.sorting_albums.column = params["sorting_albums_column"];
		lychee.sorting_albums.order = params["sorting_albums_order"];
		lychee.sorting_photos.column = params["sorting_photos_column"];
		lychee.sorting_photos.order = params["sorting_photos_order"];
		albums.refresh();
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_SORT"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeDropboxKey = function (params) {
	// if params.key == "" key is cleared
	api.post("Settings::setDropboxKey", params, function () {
		lychee.dropboxKey = params.key;
		// if (callback) lychee.loadDropbox(callback)
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_DROPBOX"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLang = function (params) {
	api.post("Settings::setLang", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LANG"]);
		lychee.init();
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setDefaultLicense = function (params) {
	api.post("Settings::setDefaultLicense", params, function () {
		lychee.default_license = params.license;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LICENSE"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setLayout = function (params) {
	api.post("Settings::setLayout", params, function () {
		lychee.layout = params.layout;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LAYOUT"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changePublicSearch = function (params) {
	api.post("Settings::setPublicSearch", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_PUBLIC_SEARCH"]);
		lychee.public_search = params.public_search;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setOverlayType = function (params) {
	api.post("Settings::setOverlayType", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_IMAGE_OVERLAY"]);
		lychee.image_overlay_type = params.image_overlay_type;
		lychee.image_overlay_type_default = params.image_overlay_type;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapDisplay = function (params) {
	api.post("Settings::setMapDisplay", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.map_display = params.map_display;
		// Map functionality is disabled
		// -> map for public albums also needs to be disabled
		if (!lychee.map_display && lychee.map_display_public) {
			$("#MapDisplayPublic").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapDisplayPublic = function (params) {
	api.post("Settings::setMapDisplayPublic", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC"]);
		lychee.map_display_public = params.map_display_public;
		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.map_display_public && !lychee.map_display) {
			$("#MapDisplay").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setMapProvider = function (params) {
	api.post("Settings::setMapProvider", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_PROVIDER"]);
		lychee.map_provider = params.map_provider;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapIncludeSubAlbums = function (params) {
	api.post("Settings::setMapIncludeSubAlbums", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.map_include_subalbums = params.map_include_subalbums;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationDecoding = function (params) {
	api.post("Settings::setLocationDecoding", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_decoding = params.location_decoding;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeNSFWVisible = function (params) {
	api.post("Settings::setNSFWVisible", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_NSFW_VISIBLE"]);
		lychee.nsfw_visible = params.nsfw_visible;
		lychee.nsfw_visible_saved = lychee.nsfw_visible;
	});
};

//TODO : later
// lychee.nsfw_blur = (data.config.nsfw_blur && data.config.nsfw_blur === '1') || false;
// lychee.nsfw_warning = (data.config.nsfw_warning && data.config.nsfw_warning === '1') || false;
// lychee.nsfw_warning_text = data.config.nsfw_warning_text || '<b>Sensitive content</b><br><p>This album contains sensitive content which some people may find offensive or disturbing.</p>';

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationShow = function (params) {
	api.post("Settings::setLocationShow", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_show = params.location_show;
		// Don't show location
		// -> location for public albums also needs to be disabled
		if (!lychee.location_show && lychee.location_show_public) {
			$("#LocationShowPublic").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationShowPublic = function (params) {
	api.post("Settings::setLocationShowPublic", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_show_public = params.location_show_public;
		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.location_show_public && !lychee.location_show) {
			$("#LocationShow").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeNewPhotosNotification = function (params) {
	api.post("Settings::setNewPhotosNotification", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_NEW_PHOTOS_NOTIFICATION"]);
		lychee.new_photos_notification = params.new_photos_notification;
	});
};

/**
 * @returns {void}
 */
settings.changeCSS = function () {
	var params = {
		css: $("#css").val()
	};
	api.post("Settings::setCSS", params, function () {
		lychee.css = params.css;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_CSS"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.save = function (params) {
	api.post("Settings::saveAll", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_UPDATE"]);
		view.full_settings.init();
		// re-read settings
		lychee.init(false);
	});
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
settings.save_enter = function (e) {
	// We only handle "enter"
	if (e.which !== 13) return;

	// show confirmation box
	$(":focus").blur();

	var action = {};
	var cancel = {};

	action.title = lychee.locale["ENTER"];
	action.msg = lychee.html(_templateObject68, lychee.locale["SAVE_RISK"]);

	cancel.title = lychee.locale["CANCEL"];

	action.fn = function () {
		settings.save(settings.getValues("#fullSettings"));
		basicModal.close();
	};

	basicModal.show({
		body: action.msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: "red"
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

var sharing = {
	/** @type {?SharingInfo} */
	json: null
};

/**
 * @returns {void}
 */
sharing.add = function () {
	var params = {
		/** @type {string[]} */
		albumIDs: [],
		/** @type {number[]} */
		userIDs: []
	};

	$("#albums_list_to option").each(function () {
		params.albumIDs.push(this.value);
	});

	$("#user_list_to option").each(function () {
		params.userIDs.push(Number.parseInt(this.value, 10));
	});

	if (params.albumIDs.length === 0) {
		loadingBar.show("error", "Select an album to share!");
		return;
	}
	if (params.userIDs.length === 0) {
		loadingBar.show("error", "Select a user to share with!");
		return;
	}

	api.post("Sharing::add", params, function () {
		loadingBar.show("success", "Sharing updated!");
		sharing.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
sharing.delete = function () {
	var params = {
		/** @type {number[]} */
		shareIDs: []
	};

	$('input[name="remove_id"]:checked').each(function () {
		params.shareIDs.push(Number.parseInt(this.value, 10));
	});

	if (params.shareIDs.length === 0) {
		loadingBar.show("error", "Select a sharing to remove!");
		return;
	}
	api.post("Sharing::delete", params, function () {
		loadingBar.show("success", "Sharing removed!");
		sharing.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
sharing.list = function () {
	api.post("Sharing::list", {},
	/** @param {SharingInfo} data */
	function (data) {
		sharing.json = data;
		view.sharing.init();
	});
};

/**
 * @description This module takes care of the sidebar.
 */

/**
 * @namespace
 */
var _sidebar = {
	/** @type {jQuery} */
	_dom: $(".sidebar"),
	types: {
		DEFAULT: 0,
		TAGS: 1
	},
	createStructure: {}
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
_sidebar.dom = function (selector) {
	if (selector == null || selector === "") return _sidebar._dom;
	return _sidebar._dom.find(selector);
};

/**
 * This function should be called after building and appending
 * the sidebars content to the DOM.
 * This function can be called multiple times, therefore
 * event handlers should be removed before binding a new one.
 *
 * @returns {void}
 */
_sidebar.bind = function () {
	var eventName = lychee.getEventName();

	_sidebar.dom("#edit_title").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setTitle([_photo3.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	_sidebar.dom("#edit_description").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setDescription(_photo3.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	_sidebar.dom("#edit_showtags").off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	_sidebar.dom("#edit_tags").off(eventName).on(eventName, function () {
		_photo3.editTags([_photo3.getID()]);
	});

	_sidebar.dom("#tags .tag").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});

	_sidebar.dom("#tags .tag span").off(eventName).on(eventName, function () {
		_photo3.deleteTag(_photo3.getID(), $(this).data("index"));
	});

	_sidebar.dom("#edit_license").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setLicense(_photo3.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	_sidebar.dom("#edit_sorting").off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	_sidebar.dom(".attr_location").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});
};

/**
 * @param {string} search_string
 * @returns {void}
 */
_sidebar.triggerSearch = function (search_string) {
	// If public search is disabled -> do nothing
	if (lychee.publicMode && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.json = null;
	// We're either logged in or public search is allowed
	lychee.goto("search/" + encodeURIComponent(search_string));
};

/**
 * @returns {boolean}
 */
_sidebar.keepSidebarVisible = function () {
	var v = sessionStorage.getItem("keepSidebarVisible");
	return v !== null ? v === "true" : false;
};

/**
 * @param {boolean} is_user_initiated - indicates if the user requested to
 *                                      toggle and hence the new state shall
 *                                      be saved in session storage
 * @returns {void}
 */
_sidebar.toggle = function (is_user_initiated) {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		if (typeof view !== "undefined") view.album.content.justify(album.json ? album.json.photos : []);
		_sidebar.dom().toggleClass("active");
		_photo3.updateSizeLivePhotoDuringAnimation();

		if (is_user_initiated) sessionStorage.setItem("keepSidebarVisible", visible.sidebar() ? "true" : "false");
	}
};

/**
 * Attributes/Values inside the sidebar are selectable by default.
 * Selection needs to be deactivated to prevent an unwanted selection
 * while using multiselect.
 *
 * @param {boolean} [selectable=true]
 * @returns {void}
 */
_sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	if (selectable) _sidebar.dom().removeClass("notSelectable");else _sidebar.dom().addClass("notSelectable");
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @param {?string} value - a `null` value is replaced by the empty string
 * @param {boolean} [dangerouslySetInnerHTML=false]
 *
 * @returns {void}
 */
_sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (!attr) return;
	if (!value) value = "";

	// TODO: Don't use our home-brewed `escapeHTML` method; use `jQuery#text` instead
	// Escape value
	if (!dangerouslySetInnerHTML) value = lychee.escapeHTML(value);

	_sidebar.dom(".attr_" + attr).html(value);
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @returns {void}
 */
_sidebar.hideAttr = function (attr) {
	_sidebar.dom(".attr_" + attr).closest("tr").hide();
};

/**
 * Converts integer seconds into "hours:minutes:seconds".
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {(number|string)} d
 * @returns {string}
 */
_sidebar.secondsToHMS = function (d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 60);

	return (h > 0 ? h.toString() + "h" : "") + (m > 0 ? m.toString() + "m" : "") + (s > 0 || h === 0 && m === 0 ? s.toString() + "s" : "");
};

/**
 * @typedef Section
 *
 * @property {string}       title
 * @property {number}       type
 * @property {SectionRow[]} rows
 */

/**
 * @typedef SectionRow
 *
 * @property {string}            title
 * @property {string}            kind
 * @property {(string|string[])} value
 * @property {boolean}           [editable]
 */

/**
 * @param {?Photo} data
 * @returns {Section[]}
 */
_sidebar.createStructure.photo = function (data) {
	if (!data) return [];

	var editable = typeof album !== "undefined" ? album.isUploadable() : false;
	var hasExif = !!data.taken_at || !!data.make || !!data.model || !!data.shutter || !!data.aperture || !!data.focal || !!data.iso;
	// Attributes for geo-position are nullable floats.
	// The geo-position 0°00'00'', 0°00'00'' at zero altitude is very unlikely
	// but valid (it's south of the coast of Ghana in the Atlantic)
	// So we must not calculate the sum and compare for zero.
	var hasLocation = data.longitude !== null || data.latitude !== null || data.altitude !== null;
	var structure = {};
	var isPublic = "";
	var isVideo = data.type && data.type.indexOf("video") > -1;
	var license = void 0;

	// Set the license string for a photo
	switch (data.license) {
		// if the photo doesn't have a license
		case "none":
			license = "";
			break;
		// Localize All Rights Reserved
		case "reserved":
			license = lychee.locale["PHOTO_RESERVED"];
			break;
		// Display anything else that's set
		default:
			license = data.license;
			break;
	}

	// Set value for public
	switch (data.is_public) {
		case 0:
			isPublic = lychee.locale["PHOTO_SHR_NO"];
			break;
		case 1:
			isPublic = lychee.locale["PHOTO_SHR_PHT"];
			break;
		case 2:
			isPublic = lychee.locale["PHOTO_SHR_ALB"];
			break;
		default:
			isPublic = "-";
			break;
	}

	structure.basics = {
		title: lychee.locale["PHOTO_BASICS"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at) }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SIZE"], kind: "size", value: lychee.locale.printFilesizeLocalized(data.size_variants.original.filesize) }, { title: lychee.locale["PHOTO_FORMAT"], kind: "type", value: data.type }, {
			title: lychee.locale["PHOTO_RESOLUTION"],
			kind: "resolution",
			value: data.size_variants.original.width + " x " + data.size_variants.original.height
		}]
	};

	if (isVideo) {
		if (data.size_variants.original.width === 0 || data.size_variants.original.height === 0) {
			// Remove the "Resolution" line if we don't have the data.
			structure.image.rows.splice(-1, 1);
		}

		// We overload the database, storing duration (in full seconds) in
		// "aperture" and frame rate (floating point with three digits after
		// the decimal point) in "focal".
		if (data.aperture) {
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: _sidebar.secondsToHMS(data.aperture) });
		}
		if (data.focal) {
			structure.image.rows.push({ title: lychee.locale["PHOTO_FPS"], kind: "fps", value: data.focal + " fps" });
		}
	}

	// Always create tags section - behaviour for editing
	// tags handled when constructing the html code for tags

	// TODO: IDE warns, that `value` is not property and `rows` is missing; the tags should actually be stored in a row for consistency
	// TODO: Consider to NOT call `build.tags` here, but simply pass the plain JSON. `build.tags` should be called in `sidebar.render` below
	structure.tags = {
		title: lychee.locale["PHOTO_TAGS"],
		type: _sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	// Only create EXIF section when EXIF data available
	if (hasExif) {
		structure.exif = {
			title: lychee.locale["PHOTO_CAMERA"],
			type: _sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }] : [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }, { title: lychee.locale["PHOTO_LENS"], kind: "lens", value: data.lens }, { title: lychee.locale["PHOTO_SHUTTER"], kind: "shutter", value: data.shutter }, { title: lychee.locale["PHOTO_APERTURE"], kind: "aperture", value: data.aperture }, { title: lychee.locale["PHOTO_FOCAL"], kind: "focal", value: data.focal }, { title: lychee.locale["PHOTO_ISO"], kind: "iso", value: data.iso }]
		};
	} else {
		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale["PHOTO_SHARING"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: isPublic }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (hasLocation) {
		structure.location = {
			title: lychee.locale["PHOTO_LOCATION"],
			type: _sidebar.types.DEFAULT,
			rows: [{
				title: lychee.locale["PHOTO_LATITUDE"],
				kind: "latitude",
				value: data.latitude ? DecimalToDegreeMinutesSeconds(data.latitude, true) : ""
			}, {
				title: lychee.locale["PHOTO_LONGITUDE"],
				kind: "longitude",
				value: data.longitude ? DecimalToDegreeMinutesSeconds(data.longitude, false) : ""
			},
			// No point in displaying sub-mm precision; 10cm is more than enough.
			{
				title: lychee.locale["PHOTO_ALTITUDE"],
				kind: "altitude",
				value: data.altitude ? (Math.round(data.altitude * 10) / 10).toString() + "m" : ""
			}, {
				title: lychee.locale["PHOTO_LOCATION"],
				kind: "location",
				// Explode location string into an array to keep street, city etc. separate
				// TODO: We should consider to keep the components apart on the server-side and send an structured object to the front-end.
				value: data.location ? data.location.split(",").map(function (item) {
					return item.trim();
				}) : ""
			}]
		};
		if (data.img_direction !== null) {
			// No point in display sub-degree precision.
			structure.location.rows.push({
				title: lychee.locale["PHOTO_IMGDIRECTION"],
				kind: "imgDirection",
				value: Math.round(data.img_direction).toString() + "°"
			});
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

/**
 * @param {(Album|TagAlbum|SmartAlbum)} data
 * @returns {Section[]}
 */
_sidebar.createStructure.album = function (data) {
	if (!data) return [];

	var editable = album.isUploadable();
	var structure = {};
	var isPublic = data.is_public ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var requiresLink = data.requires_link ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var isDownloadable = data.is_downloadable ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var isShareButtonVisible = data.is_share_button_visible ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var hasPassword = data.has_password ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var license = "";
	var sorting = "";

	// Set license string
	switch (data.license) {
		case "none":
			license = ""; // consistency
			break;
		case "reserved":
			license = lychee.locale["ALBUM_RESERVED"];
			break;
		default:
			license = data.license;
			break;
	}

	if (!lychee.publicMode) {
		if (!data.sorting) {
			sorting = lychee.locale["DEFAULT"];
		} else {
			sorting = data.sorting.column + " " + data.sorting.order;
		}
	}

	structure.basics = {
		title: lychee.locale["ALBUM_BASICS"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["ALBUM_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	if (album.isTagAlbum()) {
		structure.basics.rows.push({ title: lychee.locale["ALBUM_SHOW_TAGS"], kind: "showtags", value: data.show_tags, editable: editable });
	}

	var videoCount = data.photos.reduce(function (count, photo) {
		return count + (photo.type.indexOf("video") > -1 ? 1 : 0);
	}, 0);

	structure.album = {
		title: lychee.locale["ALBUM_ALBUM"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_CREATED"], kind: "created", value: lychee.locale.printDateTime(data.created_at) }]
	};
	if (data.albums && data.albums.length > 0) {
		structure.album.rows.push({ title: lychee.locale["ALBUM_SUBALBUMS"], kind: "subalbums", value: data.albums.length });
	}
	if (data.photos) {
		if (data.photos.length - videoCount > 0) {
			structure.album.rows.push({ title: lychee.locale["ALBUM_IMAGES"], kind: "images", value: data.photos.length - videoCount });
		}
	}
	if (videoCount > 0) {
		structure.album.rows.push({ title: lychee.locale["ALBUM_VIDEOS"], kind: "videos", value: videoCount });
	}

	if (data.photos && sorting !== "") {
		structure.album.rows.push({ title: lychee.locale["ALBUM_ORDERING"], kind: "sorting", value: sorting, editable: editable });
	}

	structure.share = {
		title: lychee.locale["ALBUM_SHARING"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: isPublic }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: requiresLink }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: isDownloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: isShareButtonVisible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: hasPassword }]
	};

	if (data.owner_name) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner_name });
	}

	structure.license = {
		title: lychee.locale["ALBUM_REUSE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.album, structure.license];
	if (!lychee.publicMode) {
		structure_ret.push(structure.share);
	}

	return structure_ret;
};

/**
 * @param {Section[]} structure
 * @returns {boolean} - true if the passed structure contains a "location" section
 */
_sidebar.has_location = function (structure) {
	var _has_location = false;

	structure.forEach(function (section) {
		if (section.title === lychee.locale["PHOTO_LOCATION"]) {
			_has_location = true;
		}
	});

	return _has_location;
};

/**
 * @param {Section[]} structure
 * @returns {string} - HTML
 */
_sidebar.render = function (structure) {
	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderDefault = function renderDefault(section) {
		var _html = "\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>" + section.title + "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t ";

		if (section.title === lychee.locale["PHOTO_LOCATION"]) {
			var _has_latitude = section.rows.findIndex(function (row) {
				return row.kind === "latitude" && row.value;
			}) !== -1;
			var _has_longitude = section.rows.findIndex(function (row) {
				return row.kind === "longitude" && row.value;
			}) !== -1;
			var idxLocation = section.rows.findIndex(function (row) {
				return row.kind === "location";
			});
			// Do not show location if not enabled
			if (idxLocation !== -1 && (lychee.publicMode === true && !lychee.location_show_public || !lychee.location_show)) {
				section.rows.splice(idxLocation, 1);
			}
			// Show map if we have coordinates
			if (_has_latitude && _has_longitude && lychee.map_display) {
				_html += "\n\t\t\t\t\t\t <div id=\"leaflet_map_single_photo\"></div>\n\t\t\t\t\t\t ";
			}
		}

		section.rows.forEach(function (row) {
			var value = row.value;

			// show only rows which have a value or are editable
			if (!(value === "" || value == null) || row.editable === true) {
				// Wrap span-element around value for easier selecting on change
				if (Array.isArray(row.value)) {
					value = row.value.reduce(
					/**
      * @param {string} prev
      * @param {string} cur
      */
					function (prev, cur) {
						// Add separator if needed
						if (prev !== "") {
							prev += lychee.html(_templateObject69, row.kind);
						}
						return prev + lychee.html(_templateObject70, row.kind, cur);
					}, "");
				} else {
					value = lychee.html(_templateObject71, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += " " + build.editIcon("edit_" + row.kind);

				_html += lychee.html(_templateObject72, row.title, value);
			}
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderTags = function renderTags(section) {
		var _html = "";
		var editable = "";

		// TODO: IDE warns me that the `Section` has no properties `editable` nor `value`; cause of the problem is that the section `tags` is built differently, see above
		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon("edit_tags");

		_html += lychee.html(_templateObject73, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	var html = "";

	structure.forEach(function (section) {
		if (section.type === _sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === _sidebar.types.TAGS) html += renderTags(section);
	});

	return html;
};

/**
 * Converts a decimal degree into integer degree, minutes and seconds.
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {number}  decimal
 * @param {boolean} type    - indicates if the passed decimal indicates a
 *                            latitude (`true`) or a longitude (`false`)
 * @returns {string}
 */
function DecimalToDegreeMinutesSeconds(decimal, type) {
	var d = Math.abs(decimal);
	var degrees = 0;
	var minutes = 0;
	var seconds = 0;
	var direction = void 0;

	// absolute value of decimal must be smaller than 180;
	if (d > 180) {
		return "";
	}

	// set direction; north assumed
	if (type && decimal < 0) {
		direction = "S";
	} else if (!type && decimal < 0) {
		direction = "W";
	} else if (!type) {
		direction = "E";
	} else {
		direction = "N";
	}

	//get degrees
	degrees = Math.floor(d);

	//get seconds
	seconds = (d - degrees) * 3600;

	//get minutes
	minutes = Math.floor(seconds / 60);

	//reset seconds
	seconds = Math.floor(seconds - minutes * 60);

	return degrees + "° " + minutes + "' " + seconds + '" ' + direction;
}

/**
 * @description Swipes and moves an object.
 */

var swipe = {
	/** @type {?jQuery} */
	obj: null,
	/** @type {number} */
	offsetX: 0,
	/** @type {number} */
	offsetY: 0,
	/** @type {boolean} */
	preventNextHeaderToggle: false
};

/**
 * @param {jQuery} obj
 * @returns {void}
 */
swipe.start = function (obj) {
	swipe.obj = obj;
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
swipe.move = function (e) {
	if (swipe.obj === null) {
		return;
	}

	if (Math.abs(e.x) > Math.abs(e.y)) {
		swipe.offsetX = -1 * e.x;
		swipe.offsetY = 0.0;
	} else {
		swipe.offsetX = 0.0;
		swipe.offsetY = +1 * e.y;
	}

	var value = "translate(" + swipe.offsetX + "px, " + swipe.offsetY + "px)";
	swipe.obj.css({
		WebkitTransform: value,
		MozTransform: value,
		transform: value
	});
};

/**
 * @callback SwipeStoppedCB
 *
 * Find a better name for that, but I have no idea what this callback is
 * supposed to do.
 *
 * @param {boolean} animate
 * @returns {void}
 */

/**
 * @param {{x: number, y: number, direction: number, distance: number, angle: number, speed: number, }} e
 * @param {SwipeStoppedCB} left
 * @param {SwipeStoppedCB} right
 * @returns {void}
 */
swipe.stop = function (e, left, right) {
	// Only execute once
	if (swipe.obj === null) {
		return;
	}

	if (e.y <= -lychee.swipe_tolerance_y) {
		lychee.goto(album.getID());
	} else if (e.y >= lychee.swipe_tolerance_y) {
		lychee.goto(album.getID());
	} else if (e.x <= -lychee.swipe_tolerance_x) {
		left(true);

		// 'touchend' will be called after 'swipeEnd'
		// in case of moving to next image, we want to skip
		// the toggling of the header
		swipe.preventNextHeaderToggle = true;
	} else if (e.x >= lychee.swipe_tolerance_x) {
		right(true);

		// 'touchend' will be called after 'swipeEnd'
		// in case of moving to next image, we want to skip
		// the toggling of the header
		swipe.preventNextHeaderToggle = true;
	} else {
		var value = "translate(0px, 0px)";
		swipe.obj.css({
			WebkitTransform: value,
			MozTransform: value,
			transform: value
		});
	}

	swipe.obj = null;
	swipe.offsetX = 0;
	swipe.offsetY = 0;
};

/**
 * @description Helper class to manage tabindex
 */

var tabindex = {
	offset_for_header: 100,
	next_tab_index: 100
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
tabindex.saveSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	// TODO @Hallenser: What did you intended by the TODO above? It seems as if the jQuery selector is already as short as possible?
	var tmp = elem.find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focusable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  * @this {Element} - identical to `e`
  */
	function (i, e) {
		// TODO: shorter notation
		// TODO @Hallenser: What do you intended by the TODO `short notation`? Moreover: Why do we use `this` and `e`? They refer to the identical instance of a HTML element.
		var a = $(e).attr("tabindex");
		$(this).data("tabindex-saved", a);
	});
};

tabindex.restoreSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	// TODO @Hallenser: What did you intended by the TODO above? It seems as if the jQuery selector is already as short as possible?
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  * @this {Element} - identical to `e`
  */
	function (i, e) {
		// TODO: shorter notation
		// TODO @Hallenser: What do you intended by the TODO `short notation`? Moreover: Why do we use `this` and `e`? They refer to the identical instance of a HTML element.
		var a = $(e).data("tabindex-saved");
		$(e).attr("tabindex", a);
	});
};

/**
 * @param {jQuery} elem
 * @param {boolean} [saveFocusElement=false]
 * @returns {void}
 */
tabindex.makeUnfocusable = function (elem) {
	var saveFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = elem.find("[tabindex]");

	// iterate over all elements and set tabindex to -1 (i.e. make is not focussable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  */
	function (i, e) {
		$(e).attr("tabindex", "-1");
		// Save which element had focus before we make it unfocusable
		if (saveFocusElement && $(e).is(":focus")) {
			$(e).data("tabindex-focus", true);
			// Remove focus
			$(e).blur();
		}
	});

	// Disable input fields
	elem.find("input").attr("disabled", "disabled");
};

/**
 * @param {jQuery} elem
 * @param {boolean} [restoreFocusElement=false]
 * @returns {void}
 */
tabindex.makeFocusable = function (elem) {
	var restoreFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = elem.find("[data-tabindex]");

	// iterate over all elements and set tabindex to stored value
	tmp.each(
	/**
  * @param {number} i
  * @param {Element} e
  */
	function (i, e) {
		$(e).attr("tabindex", $(e).data("tabindex"));
		// restore focus element if wanted
		if (restoreFocusElement) {
			if ($(e).data("tabindex-focus") && lychee.active_focus_on_page_load) {
				$(e).focus();
				$(e).removeData("tabindex-focus");
			}
		}
	});

	// Enable input fields
	elem.find("input").removeAttr("disabled");
};

/**
 * @returns {number}
 */
tabindex.get_next_tab_index = function () {
	tabindex.next_tab_index = tabindex.next_tab_index + 1;

	return tabindex.next_tab_index - 1;
};

/**
 * @returns {void}
 */
tabindex.reset = function () {
	tabindex.next_tab_index = tabindex.offset_for_header;
};

var u2f = {
	/** @type {?WebAuthnCredential[]} */
	json: null
};

/**
 * @returns {boolean}
 */
u2f.is_available = function () {
	if (!window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1") {
		var msg = lychee.html(_templateObject74, lychee.locale["U2F_NOT_SECURE"]);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		return false;
	}
	return true;
};

/**
 * @returns {void}
 */
u2f.login = function () {
	if (!u2f.is_available()) {
		return;
	}

	new Larapass({
		login: "/api/WebAuthn::login",
		loginOptions: "/api/WebAuthn::login/gen"
	}).login({
		user_id: 0 // for now it is only available to Admin user via a secret key shortcut.
	}).then(function () {
		loadingBar.show("success", lychee.locale["U2F_AUTHENTIFICATION_SUCCESS"]);
		window.location.reload();
	}).catch(function () {
		return loadingBar.show("error", "Something went wrong!");
	});
};

/**
 * @returns {void}
 */
u2f.register = function () {
	if (!u2f.is_available()) {
		return;
	}

	var larapass = new Larapass({
		register: "/api/WebAuthn::register",
		registerOptions: "/api/WebAuthn::register/gen"
	});
	if (Larapass.supportsWebAuthn()) {
		larapass.register().then(function () {
			loadingBar.show("success", lychee.locale["U2F_REGISTRATION_SUCCESS"]);
			u2f.list(); // reload credential list
		}).catch(function () {
			return loadingBar.show("error", "Something went wrong!");
		});
	} else {
		loadingBar.show("error", lychee.locale["U2F_NOT_SUPPORTED"]);
	}
};

/**
 * @param {{id: string}} params - ID of WebAuthn credential
 */
u2f.delete = function (params) {
	api.post("WebAuthn::delete", params, function () {
		loadingBar.show("success", lychee.locale["U2F_CREDENTIALS_DELETED"]);
		u2f.list(); // reload credential list
	});
};

u2f.list = function () {
	api.post("WebAuthn::list", {},
	/** @param {WebAuthnCredential[]} data*/
	function (data) {
		u2f.json = data;
		view.u2f.init();
	});
};

/**
 * @description Takes care of every action an album can handle and execute.
 */

var upload = {};

var choiceDeleteSelector = '.basicModal .choice input[name="delete_imported"]';
var choiceSymlinkSelector = '.basicModal .choice input[name="import_via_symlink"]';
var choiceDuplicateSelector = '.basicModal .choice input[name="skip_duplicates"]';
var choiceResyncSelector = '.basicModal .choice input[name="resync_metadata"]';
var actionSelector = ".basicModal #basicModal__action";
var cancelSelector = ".basicModal #basicModal__cancel";
var firstRowStatusSelector = ".basicModal .rows .row .status";
var firstRowNoticeSelector = ".basicModal .rows .row p.notice";

var nRowStatusSelector = function nRowStatusSelector(row) {
	return ".basicModal .rows .row:nth-child(" + row + ") .status";
};

var showCloseButton = function showCloseButton() {
	$(actionSelector).show();
	// re-activate cancel button to close modal panel if needed
	$(cancelSelector).removeClass("basicModal__button--active").hide();
};

/**
 * @param {string} title
 * @param {(FileList|File[]|DropboxFile[]|{name: string}[])} files
 * @param {ModalDialogReadyCB} run_callback
 * @param {?ModalDialogButtonCB} cancel_callback
 */
upload.show = function (title, files, run_callback) {
	var cancel_callback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	basicModal.show({
		body: build.uploadModal(title, files),
		buttons: {
			action: {
				title: lychee.locale["CLOSE"],
				class: "hidden",
				fn: function fn() {
					if ($(actionSelector).is(":visible")) basicModal.close();
				}
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				class: "red hidden",
				fn: function fn() {
					// close modal if close button is displayed
					if ($(actionSelector).is(":visible")) basicModal.close();
					if (cancel_callback) {
						$(cancelSelector).addClass("busy");
						cancel_callback();
					}
				}
			}
		},
		callback: run_callback
	});
};

upload.notify = function (title, text) {
	if (text == null || text === "") text = lychee.locale["UPLOAD_MANAGE_NEW_PHOTOS"];

	if (!window.webkitNotifications) return false;

	if (window.webkitNotifications.checkPermission() !== 0) window.webkitNotifications.requestPermission();

	if (window.webkitNotifications.checkPermission() === 0 && title) {
		var popup = window.webkitNotifications.createNotification("", title, text);
		popup.show();
	}
};

upload.start = {
	/**
  * @param {(FileList|File[])} files
  */
	local: function local(files) {
		if (files.length <= 0) return;

		var albumID = album.getID();
		var hasErrorOccurred = false;
		var hasWarningOccurred = false;
		/**
   * The number of requests which are "on the fly", i.e. for which a
   * response has not yet completely been received.
   *
   * Note, that Lychee supports a restricted kind of "parallelism"
   * which is limited by the configuration option
   * `lychee.upload_processing_limit`:
   * While always only a single file is uploaded at once, upload of the
   * next file already starts after transmission of the previous file
   * has been finished, the response to the previous file might still be
   * outstanding as the uploaded file is processed at the server-side.
   *
   * @type {number}
   */
		var outstandingResponsesCount = 0;
		/**
   * The latest (aka highest) index of a file which is being or has
   * been uploaded to the server.
   *
   * @type {number}
   */
		var latestFileIdx = 0;
		/**
   * Semaphore whether a file is currently being uploaded.
   *
   * This is used as a semaphore to serialize the upload transmissions
   * between several instances of the method {@link process}.
   *
   * @type {boolean}
   */
		var isUploadRunning = false;
		/**
   * Semaphore whether a further upload shall be cancelled on the next
   * occasion.
   *
   * @type {boolean}
   */
		var shallCancelUpload = false;

		/**
   * This callback is invoked when the last file has been processed.
   *
   * It closes the modal dialog or shows the close button and
   * reloads the album.
   */
		var finish = function finish() {
			window.onbeforeunload = null;

			$("#upload_files").val("");

			if (!hasErrorOccurred && !hasWarningOccurred) {
				// Success
				basicModal.close();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			} else if (!hasErrorOccurred && hasWarningOccurred) {
				// Warning
				showCloseButton();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			} else {
				// Error
				showCloseButton();
				if (shallCancelUpload) {
					$(".basicModal .rows .row:nth-child(n+" + (latestFileIdx + 2).toString() + ") .status").html(lychee.locale["UPLOAD_CANCELLED"]).addClass("warning");
				}
				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
			}

			album.reload();
		};

		/**
   * Processes the upload and response for a single file.
   *
   * Note that up to `lychee.upload_processing_limit` "instances" of
   * this method can be "alive" simultaneously.
   * The parameter `fileIdx` is limited by `latestFileIdx`.
   *
   * @param {number} fileIdx the index of the file being processed
   */
		var process = function process(fileIdx) {
			/**
    * The upload progress of the file with index `fileIdx` so far.
    *
    * @type {number}
    */
			var uploadProgress = 0;

			/**
    * A function to be called when the upload has transmitted more data.
    *
    * This method updates the upload percentage counter in the dialog.
    *
    * If the progress equals 100%, i.e. if the upload has been
    * completed, this method
    *
    *  - unsets the semaphore for a running upload,
    *  - scrolls the dialog such that the file with index `fileIdx`
    *    becomes visible, and
    *  - changes the status text to "Upload processing".
    *
    * After the current upload has reached 100%, this method starts a
    * new upload, if
    *
    *  - there are more files to be uploaded,
    *  - no other upload is currently running, and
    *  - the number of outstanding responses does not exceed the
    *    processing limit of Lychee.
    *
    * @param {ProgressEvent} e
    * @this XMLHttpRequest
    */
			var onUploadProgress = function onUploadProgress(e) {
				if (e.lengthComputable !== true) return;

				// Calculate progress
				var progress = e.loaded / e.total * 100 | 0;

				// Set progress when progress has changed
				if (progress > uploadProgress) {
					uploadProgress = progress;
					/** @type {?jQuery} */
					var jqStatusMsg = $(nRowStatusSelector(fileIdx + 1));
					jqStatusMsg.html(uploadProgress + "%");

					if (progress >= 100) {
						jqStatusMsg.html(lychee.locale["UPLOAD_PROCESSING"]);
						isUploadRunning = false;
						var scrollPos = 0;
						if (fileIdx + 1 > 4) scrollPos = (fileIdx + 1 - 4) * 40;
						$(".basicModal .rows").scrollTop(scrollPos);

						// Start a new upload, if there are still pending
						// files
						if (!isUploadRunning && !shallCancelUpload && (outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && latestFileIdx + 1 < files.length) {
							latestFileIdx++;
							process(latestFileIdx);
						}
					}
				}
			};

			/**
    * A function to be called when a response has been received.
    *
    * This method updates the status of the affected file.
    *
    * @this XMLHttpRequest
    */
			var onLoaded = function onLoaded() {
				/** @type {?LycheeException} */
				var lycheeException = this.status >= 400 ? this.response : null;
				var errorText = "";
				var statusText = void 0;
				var statusClass = void 0;

				switch (this.status) {
					case 200:
					case 201:
					case 204:
						statusText = lychee.locale["UPLOAD_FINISHED"];
						statusClass = "success";
						break;
					case 409:
						statusText = lychee.locale["UPLOAD_SKIPPED"];
						errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
						hasWarningOccurred = true;
						statusClass = "warning";
						break;
					case 413:
						statusText = lychee.locale["UPLOAD_FAILED"];
						errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
						hasErrorOccurred = true;
						statusClass = "error";
						break;
					default:
						statusText = lychee.locale["UPLOAD_FAILED"];
						errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
						hasErrorOccurred = true;
						statusClass = "error";
						break;
				}

				$(nRowStatusSelector(fileIdx + 1)).html(statusText).addClass(statusClass);

				if (statusClass === "error") {
					api.onError(this, { albumID: albumID }, lycheeException);
				}

				if (errorText !== "") {
					$(".basicModal .rows .row:nth-child(" + (fileIdx + 1) + ") p.notice").html(errorText).show();
				}
			};

			/**
    * A function to be called when any response has been received
    * (after specific success and error callbacks have been executed)
    *
    * This method starts a new upload, if
    *
    *  - there are more files to be uploaded,
    *  - no other upload is currently running, and
    *  - the number of outstanding responses does not exceed the
    *    processing limit of Lychee.
    *
    * This method calls {@link finish}, if
    *
    *  - the process shall be cancelled or no more files are left for processing,
    *  - no upload is running anymore, and
    *  - no response is outstanding
    *
    * @this XMLHttpRequest
    */
			var onComplete = function onComplete() {
				outstandingResponsesCount--;

				if (!isUploadRunning && !shallCancelUpload && (outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && latestFileIdx + 1 < files.length) {
					latestFileIdx++;
					process(latestFileIdx);
				}

				if ((shallCancelUpload || latestFileIdx + 1 === files.length) && !isUploadRunning && outstandingResponsesCount === 0) {
					finish();
				}
			};

			var formData = new FormData();
			var xhr = new XMLHttpRequest();

			// For form data, a `null` value is indicated by the empty
			// string `""`. Form data falsely converts the value `null` to the
			// literal string `"null"`.
			formData.append("albumID", albumID ? albumID : "");
			formData.append("file", files[fileIdx]);

			// We must not use the `onload` event of the `XMLHttpRequestUpload`
			// object.
			// Instead, we only use the `onprogress` event and check within
			// the event handler if the progress counter reached 100%.
			// The reason is that `upload.onload` is not immediately called
			// after the browser has completed the upload (as the name
			// suggests), but only after the browser has already received the
			// response header.
			// For our purposes this is too late, as this way we would never
			// show the "processing" status, during which the backend has
			// received the upload, but has not yet started to send a response.
			xhr.upload.onprogress = onUploadProgress;
			xhr.onload = onLoaded;
			xhr.onloadend = onComplete;
			xhr.responseType = "json";
			xhr.open("POST", "api/Photo::add");
			xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
			xhr.setRequestHeader("Accept", "application/json");

			outstandingResponsesCount++;
			isUploadRunning = true;
			xhr.send(formData);
		};

		window.onbeforeunload = function () {
			return lychee.locale["UPLOAD_IN_PROGRESS"];
		};

		upload.show(lychee.locale["UPLOAD_UPLOADING"], files, function () {
			// Upload first file
			$(cancelSelector).show();
			process(0);
		}, function () {
			shallCancelUpload = true;
			hasErrorOccurred = true;
		});
	},

	/**
  * @param {string} preselectedUrl
  */
	url: function url() {
		var preselectedUrl = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

		var albumID = album.getID();

		/**
   * @typedef UrlDialogResult
   * @property {string} url
   */

		/** @param {UrlDialogResult} data */
		var action = function action(data) {
			var runImport = function runImport() {
				$(firstRowStatusSelector).html(lychee.locale["UPLOAD_IMPORTING"]);

				var successHandler = function successHandler() {
					// Same code as in import.dropbox()
					basicModal.close();
					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);
					album.reload();
				};

				/**
     * @param {XMLHttpRequest} jqXHR
     * @param {Object} params
     * @param {?LycheeException} lycheeException
     * @returns {boolean}
     */
				var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
					// Same code as in import.dropbox()
					var errorText = void 0;
					var statusText = void 0;
					var statusClass = void 0;

					switch (jqXHR.status) {
						case 409:
							statusText = lychee.locale["UPLOAD_SKIPPED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "warning";
							break;
						default:
							statusText = lychee.locale["UPLOAD_FAILED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "error";
							break;
					}

					$(firstRowNoticeSelector).html(errorText).show();
					$(firstRowStatusSelector).html(statusText).addClass(statusClass);
					// Show close button
					$(".basicModal #basicModal__action.hidden").show();
					upload.notify(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]);
					album.reload();
					return true;
				};

				// In theory, the backend is prepared to download a list of
				// URLs (note that `data.url`) is wrapped into an array.
				// However, we need a better dialog which allows input of a
				// list of URLs.
				// Another problem which already exists even for a single
				// URL concerns timeouts.
				// Below, we transmit a single HTTP request which must respond
				// within about 500ms either with a success or error response.
				// Otherwise, JS assumes that the AJAX request just timed out.
				// But the server, first need to download the image from the
				// specified URL, process it and then generate a HTTP response.
				// Probably, it would be much better to use a streamed
				// response here like we already have for imports from the
				// local server.
				// This way, the server could also report its own progress of
				// downloading the images.
				// TODO: Use a streamed response (see description above).
				api.post("Import::url", {
					urls: [data.url],
					albumID: albumID
				}, successHandler, null, errorHandler);
			};

			if (data.url && data.url.trim().length > 3) {
				basicModal.close();
				upload.show(lychee.locale["UPLOAD_IMPORTING_URL"], [{ name: data.url }], runImport);
			} else basicModal.error("link");
		};

		basicModal.show({
			body: lychee.html(_templateObject75) + lychee.locale["UPLOAD_IMPORT_INSTR"] + (" <input class='text' name='url' type='text' placeholder='https://' value='" + preselectedUrl + "'></p>"),
			buttons: {
				action: {
					title: lychee.locale["UPLOAD_IMPORT"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});
	},

	server: function server() {
		var albumID = album.getID();

		var importDialogSetupCB = function importDialogSetupCB() {
			var $delete = $(choiceDeleteSelector);
			var $symlinks = $(choiceSymlinkSelector);
			var $duplicates = $(choiceDuplicateSelector);
			var $resync = $(choiceResyncSelector);

			if (lychee.delete_imported) {
				$delete.prop("checked", true);
				$symlinks.prop("checked", false).prop("disabled", true);
			} else {
				if (lychee.import_via_symlink) {
					$symlinks.prop("checked", true);
					$delete.prop("checked", false).prop("disabled", true);
				}
			}
			if (lychee.skip_duplicates) {
				$duplicates.prop("checked", true);
				if (lychee.resync_metadata) $resync.prop("checked", true);
			} else {
				$resync.prop("disabled", true);
			}
		};

		/**
   * @typedef ServerImportDialogResult
   * @property {string} path
   * @property {boolean} delete_imported
   * @property {boolean} import_via_symlink
   * @property {boolean} skip_duplicates
   * @property {boolean} resync_metadata
   */

		/** @param {ServerImportDialogResult} data */
		var action = function action(data) {
			if (!data.path.trim()) {
				basicModal.error("path");
				return;
			} else {
				// Consolidate `data` before we close the modal dialog
				// TODO: We should fix the modal dialog to properly return the values of all input fields, incl. check boxes
				data.delete_imported = !!$(choiceDeleteSelector).prop("checked");
				data.import_via_symlink = !!$(choiceSymlinkSelector).prop("checked");
				data.skip_duplicates = !!$(choiceDuplicateSelector).prop("checked");
				data.resync_metadata = !!$(choiceResyncSelector).prop("checked");
				basicModal.close();
			}

			var isUploadCancelled = false;

			var cancelUpload = function cancelUpload() {
				if (!isUploadCancelled) {
					api.post("Import::serverCancel", {}, function () {
						isUploadCancelled = true;
					});
				}
			};

			var runUpload = function runUpload() {
				$(cancelSelector).show();

				// Variables holding state across the invocations of
				// processIncremental().
				var jqRows = $(".basicModal .rows");
				var lastReadIdx = 0;
				var currentPath = null;
				var jqCurrentRow = null; // the jQuery object of the current row
				var encounteredProblems = false;
				var topSkip = 0;

				/**
     * Worker function invoked from both the response progress
     * callback and the completion callback.
     *
     * @param {(ImportProgressReport|ImportEventReport)[]} reports
     */
				var processIncremental = function processIncremental(reports) {
					reports.slice(lastReadIdx).forEach(function (report) {
						if (report.type === "progress") {
							if (currentPath !== report.path) {
								// New directory. Add a new line to the dialog box at the end
								currentPath = report.path;
								jqCurrentRow = $(build.uploadNewFile(currentPath)).appendTo(jqRows);
								topSkip += jqCurrentRow.outerHeight();
							}

							if (report.progress !== 100) {
								$(".status", jqCurrentRow).text("" + report.progress + "%");
							} else {
								// Final status report for this directory.
								$(".status", jqCurrentRow).text(lychee.locale["UPLOAD_FINISHED"]).addClass("success");
							}
						} else if (report.type === "event") {
							var jqEventRow = void 0;
							if (jqCurrentRow) {
								if (currentPath !== report.path) {
									// If we already have a current row (for
									// progress reports) and the event does
									// not refer to that directory, we
									// insert the event row _before_ the
									// current row, so that the progress
									// report stays in sight.
									jqEventRow = $(build.uploadNewFile(report.path || "General")).insertBefore(jqCurrentRow);
									topSkip += jqEventRow.outerHeight();
								} else {
									// The problem is with the directory
									// itself, so alter its existing line.
									jqEventRow = jqCurrentRow;
								}
							} else {
								// If we do not have a current row yet, we
								// simply append it to the list of rows
								// (this might happen if the event occurs
								// before the first progress report)
								jqEventRow = $(build.uploadNewFile(report.path || "General")).appendTo(jqRows);
								topSkip += jqEventRow.outerHeight();
							}

							var severityClass = "";
							var statusText = "";
							var noteText = "";

							switch (report.severity) {
								case "debug":
								case "info":
									break;
								case "notice":
								case "warning":
									severityClass = "warning";
									break;
								case "error":
								case "critical":
								case "emergency":
									severityClass = "error";
									break;
							}

							switch (report.subtype) {
								case "mem_limit":
									statusText = lychee.locale["UPLOAD_WARNING"];
									noteText = lychee.locale["UPLOAD_IMPORT_LOW_MEMORY_EXPL"];
									break;
								case "FileOperationException":
								case "MediaFileOperationException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_FAILED"];
									break;
								case "MediaFileUnsupportedException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_UNSUPPORTED"];
									break;
								case "InvalidDirectoryException":
									statusText = lychee.locale["UPLOAD_FAILED"];
									noteText = lychee.locale["UPLOAD_IMPORT_NOT_A_DIRECTORY"];
									break;
								case "ReservedDirectoryException":
									statusText = lychee.locale["UPLOAD_FAILED"];
									noteText = lychee.locale["UPLOAD_IMPORT_PATH_RESERVED"];
									break;
								case "PhotoSkippedException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_SKIPPED_DUPLICATE"];
									break;
								case "PhotoResyncedException":
									statusText = lychee.locale["UPLOAD_UPDATED"];
									noteText = lychee.locale["UPLOAD_IMPORT_RESYNCED_DUPLICATE"];
									break;
								case "ImportCancelledException":
									statusText = lychee.locale["UPLOAD_CANCELLED"];
									noteText = lychee.locale["UPLOAD_IMPORT_CANCELLED"];
									break;
								default:
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = report.message;
									break;
							}

							$(".status", jqEventRow).text(statusText).addClass(severityClass);
							$(".notice", jqEventRow).text(noteText).show();

							encounteredProblems = true;
						}
					}); // forEach (resp)
					lastReadIdx = reports.length;
					$(jqRows).scrollTop(topSkip);
				}; // processIncremental

				/**
     * @param {ImportReport[]} reports
     */
				var successHandler = function successHandler(reports) {
					// reports is already JSON-parsed.
					processIncremental(reports);

					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"], encounteredProblems ? lychee.locale["UPLOAD_COMPLETE_FAILED"] : null);

					album.reload();

					if (encounteredProblems) showCloseButton();else basicModal.close();
				};

				/**
     * @this {XMLHttpRequest}
     */
				var progressHandler = function progressHandler() {
					/** @type {string} */
					var response = this.response;
					/** @type {ImportReport[]} */
					var reports = [];
					// We received a possibly partial response.
					// We must ensure that the last object in the
					// array is complete and terminate the array.
					while (response.length > 2 && reports.length === 0) {
						// Search the last '}', assume that this terminates
						// the last JSON object, cut the string and terminate
						// the array with `]`.
						var fixedResponse = response.substring(0, response.lastIndexOf("}") + 1) + "]";
						try {
							// If the assumption is wrong and the last found
							// '}'  does not terminate the last object, then
							// `JSON.parse` will fail and tell us where the
							// problem occurred.
							reports = JSON.parse(fixedResponse);
						} catch (e) {
							if (e instanceof SyntaxError) {
								var errorPos = e.columnNumber;
								var lastBrace = response.lastIndexOf("}");
								var cutResponse = errorPos < lastBrace ? errorPos : lastBrace;
								response = response.substring(0, cutResponse);
							} else {
								// Something else went wrong
								upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);

								album.reload();

								showCloseButton();

								return;
							}
						}
					}
					// The rest of the work is the same as for the full
					// response.
					processIncremental(reports);
				};

				var params = {
					albumID: albumID,
					path: data.path,
					delete_imported: data.delete_imported,
					import_via_symlink: data.import_via_symlink,
					skip_duplicates: data.skip_duplicates,
					resync_metadata: data.resync_metadata
				};

				api.post("Import::server", params, successHandler, progressHandler);
			};

			upload.show(lychee.locale["UPLOAD_IMPORT_SERVER"], [], runUpload, cancelUpload);
		}; // action

		var msg = lychee.html(_templateObject76, lychee.locale["UPLOAD_IMPORT_SERVER_INSTR"], lychee.locale["UPLOAD_ABSOLUTE_PATH"], lychee.location, build.iconic("check"), lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS"], lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK"], lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES"], lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA"], lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA_EXPL"]);

		basicModal.show({
			body: msg,
			callback: importDialogSetupCB,
			buttons: {
				action: {
					title: lychee.locale["UPLOAD_IMPORT"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});
	},

	dropbox: function dropbox() {
		var albumID = album.getID();

		/**
   * @param {DropboxFile[]} files
   */
		var action = function action(files) {
			var runImport = function runImport() {
				var successHandler = function successHandler() {
					// Same code as in import.url()
					basicModal.close();
					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);
					album.reload();
				};

				/**
     * @param {XMLHttpRequest} jqXHR
     * @param {Object} params
     * @param {?LycheeException} lycheeException
     * @returns {boolean}
     */
				var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
					// Same code as in import.url()
					var errorText = void 0;
					var statusText = void 0;
					var statusClass = void 0;

					switch (jqXHR.status) {
						case 409:
							statusText = lychee.locale["UPLOAD_SKIPPED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "warning";
							break;
						default:
							statusText = lychee.locale["UPLOAD_FAILED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "error";
							break;
					}

					$(firstRowNoticeSelector).html(errorText).show();
					$(firstRowStatusSelector).html(statusText).addClass(statusClass);
					// Show close button
					$(".basicModal #basicModal__action.hidden").show();
					upload.notify(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]);
					album.reload();
					return true;
				};

				$(firstRowStatusSelector).html(lychee.locale["UPLOAD_IMPORTING"]);

				// TODO: Use a streamed response; see long comment in `import.url()` for the reasons
				api.post("Import::url", {
					urls: files.map(function (file) {
						return file.link;
					}),
					albumID: albumID
				}, successHandler, null, errorHandler);
			};

			files.forEach(function (file) {
				return file.name = file.link;
			});
			upload.show("Importing from Dropbox", files, runImport);
		};

		lychee.loadDropbox(function () {
			Dropbox.choose({
				linkType: "direct",
				multiselect: true,
				success: action
			});
		});
	}
};

upload.check = function () {
	var $delete = $(choiceDeleteSelector);
	var $symlinks = $(choiceSymlinkSelector);

	if ($delete.prop("checked")) {
		$symlinks.prop("checked", false).prop("disabled", true);
	} else {
		$symlinks.prop("disabled", false);
		if ($symlinks.prop("checked")) {
			$delete.prop("checked", false).prop("disabled", true);
		} else {
			$delete.prop("disabled", false);
		}
	}

	var $duplicates = $(choiceDuplicateSelector);
	var $resync = $(choiceResyncSelector);

	if ($duplicates.prop("checked")) {
		$resync.prop("disabled", false);
	} else {
		$resync.prop("checked", false).prop("disabled", true);
	}
};

/**
 * @param {(FileList|File[])} files
 *
 * @returns {void}
 */
upload.uploadTrack = function (files) {
	var albumID = album.getID();
	if (files.length <= 0 || albumID === null) return;

	var runUpload = function runUpload() {
		/**
   * A function to be called when a response has been received.
   *
   * It closes the modal dialog or shows the close button and
   * reloads the album.
   *
   * @this XMLHttpRequest
   */
		var finish = function finish() {
			/** @type {?LycheeException} */
			var lycheeException = this.status >= 400 ? this.response : null;
			var errorText = "";
			var statusText = void 0;
			var statusClass = void 0;

			$("#upload_track_file").val("");

			switch (this.status) {
				case 200:
				case 201:
				case 204:
					statusText = lychee.locale["UPLOAD_FINISHED"];
					statusClass = "success";
					break;
				case 413:
					statusText = lychee.locale["UPLOAD_FAILED"];
					errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
					statusClass = "error";
					break;
				default:
					statusText = lychee.locale["UPLOAD_FAILED"];
					errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
					statusClass = "error";
					break;
			}

			$(firstRowStatusSelector).html(statusText).addClass(statusClass);

			if (errorText !== "") {
				$(firstRowNoticeSelector).html(errorText).show();

				api.onError(this, { albumID: albumID }, lycheeException);
				showCloseButton();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
			} else {
				basicModal.close();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			}

			album.reload();
		}; // finish

		$(firstRowStatusSelector).html(lychee.locale["UPLOAD_UPLOADING"]);

		var formData = new FormData();
		var xhr = new XMLHttpRequest();

		formData.append("albumID", albumID);
		formData.append("file", files[0]);

		xhr.onload = finish;
		xhr.responseType = "json";
		xhr.open("POST", "api/Album::setTrack");
		xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
		xhr.setRequestHeader("Accept", "application/json");

		xhr.send(formData);
	}; // runUpload

	upload.show(lychee.locale["UPLOAD_UPLOADING"], files, runUpload);
};

var users = {
	/** @type {?User[]} */
	json: null
};

/**
 * Updates a user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link build.user}.
 *
 * @param {{id: number, username: string, password: string, may_upload: boolean, is_locked: boolean}} params
 * @returns {void}
 */
users.update = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return;
	}

	// If the password is empty, then the password shall not be changed.
	// In this case, the password must not be an attribute of the object at
	// all.
	// An existing, but empty password, would indicate to clear the password.
	if (params.password.length === 0) {
		delete params.password;
	}

	api.post("User::save", params, function () {
		loadingBar.show("success", "User updated!");
		users.list(); // reload user list
	});
};

/**
 * Creates a new user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link view.users.content}.
 *
 * @param {{id: string, username: string, password: string, may_upload: boolean, is_locked: boolean}} params
 * @returns {void}
 */
users.create = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return;
	}
	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		return;
	}

	api.post("User::create", params, function () {
		loadingBar.show("success", "User created!");
		users.list(); // reload user list
	});
};

/**
 * Deletes a user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link build.user}.
 *
 * @param {{id: number}} params
 * @returns {boolean}
 */
users.delete = function (params) {
	api.post("User::delete", params, function () {
		loadingBar.show("success", "User deleted!");
		users.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
users.list = function () {
	api.post("User::list", {},
	/** @param {User[]} data */
	function (data) {
		users.json = data;
		view.users.init();
	});
};

/**
 * @description Responsible to reflect data changes to the UI.
 */

var view = {};

view.albums = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.albums.title();
		view.albums.content.init();
	},

	/** @returns {void} */
	title: function title() {
		if (lychee.landing_page_enable) {
			lychee.setTitle("", false);
		} else {
			lychee.setTitle(lychee.locale["ALBUMS"], false);
		}
	},

	content: {
		/** @returns {void} */
		init: function init() {
			var smartData = "";
			var tagAlbumsData = "";
			var albumsData = "";
			var sharedData = "";

			// Smart Albums
			if (lychee.publicMode === false && (albums.json.smart_albums.public || albums.json.smart_albums.recent || albums.json.smart_albums.starred || albums.json.smart_albums.unsorted || albums.json.tag_albums.length > 0)) {
				smartData = build.divider(lychee.locale["SMART_ALBUMS"]);
			}
			if (albums.json.smart_albums.unsorted) {
				albums.parse(albums.json.smart_albums.unsorted);
				smartData += build.album(albums.json.smart_albums.unsorted);
			}
			if (albums.json.smart_albums.public) {
				albums.parse(albums.json.smart_albums.public);
				smartData += build.album(albums.json.smart_albums.public);
			}
			if (albums.json.smart_albums.starred) {
				albums.parse(albums.json.smart_albums.starred);
				smartData += build.album(albums.json.smart_albums.starred);
			}
			if (albums.json.smart_albums.recent) {
				albums.parse(albums.json.smart_albums.recent);
				smartData += build.album(albums.json.smart_albums.recent);
			}

			// Tag albums
			tagAlbumsData += albums.json.tag_albums.reduce(function (html, tagAlbum) {
				albums.parse(tagAlbum);
				return html + build.album(tagAlbum);
			}, "");

			// Albums
			if (lychee.publicMode === false && albums.json.albums.length > 0) albumsData = build.divider(lychee.locale["ALBUMS"]);
			albumsData += albums.json.albums.reduce(function (html, album) {
				albums.parse(album);
				return html + build.album(album);
			}, "");

			var current_owner = "";
			// Shared
			sharedData += albums.json.shared_albums.reduce(function (html, album) {
				albums.parse(album);
				if (current_owner !== album.owner_name && lychee.publicMode === false) {
					html += build.divider(album.owner_name);
					current_owner = album.owner_name;
				}
				return html + build.album(album, !lychee.admin);
			}, "");

			if (smartData === "" && tagAlbumsData === "" && albumsData === "" && sharedData === "") {
				lychee.content.html("");
				$("body").append(build.no_content("eye"));
			} else {
				lychee.content.html(smartData + tagAlbumsData + albumsData + sharedData);
			}

			album.apply_nsfw_filter();

			// Restore scroll position
			var urls = JSON.parse(localStorage.getItem("scroll"));
			var urlWindow = window.location.href;
			$(window).scrollTop(urls != null && urls[urlWindow] ? urls[urlWindow] : 0);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		title: function title(albumID) {
			var album = albums.getByID(albumID);
			var title = album.title ? album.title : lychee.locale["UNTITLED"];

			$('.album[data-id="' + albumID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		delete: function _delete(albumID) {
			$('.album[data-id="' + albumID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (albums.json.albums.length <= 0) lychee.content.find(".divider:last-child").remove();
			});
		}
	}
};

view.album = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.album.sidebar();
		view.album.title();
		view.album.public();
		view.album.nsfw();
		view.album.nsfw_warning.init();
		view.album.content.init();

		// TODO: `init` is not a property of the Album JSON; this is a property of the view. Consider to move it to `view.album.isInitialized`
		album.json.init = true;
	},

	/** @returns {void} */
	title: function title() {
		if ((visible.album() || !album.json.init) && !visible.photo()) {
			switch (album.getID()) {
				case SmartAlbumID.STARRED:
					lychee.setTitle(lychee.locale["STARRED"], true);
					break;
				case SmartAlbumID.PUBLIC:
					lychee.setTitle(lychee.locale["PUBLIC"], true);
					break;
				case SmartAlbumID.RECENT:
					lychee.setTitle(lychee.locale["RECENT"], true);
					break;
				case SmartAlbumID.UNSORTED:
					lychee.setTitle(lychee.locale["UNSORTED"], true);
					break;
				default:
					if (album.json.init) _sidebar.changeAttr("title", album.json.title);
					lychee.setTitle(album.json.title, true);
					break;
			}
		}
	},

	nsfw_warning: {
		/** @returns {void} */
		init: function init() {
			if (!lychee.nsfw_warning) {
				$("#sensitive_warning").hide();
				return;
			}

			if (album.json.is_nsfw && !lychee.nsfw_unlocked_albums.includes(album.json.id)) {
				$("#sensitive_warning").show();
			} else {
				$("#sensitive_warning").hide();
			}
		},

		/** @returns {void} */
		next: function next() {
			lychee.nsfw_unlocked_albums.push(album.json.id);
			$("#sensitive_warning").hide();
		}
	},

	content: {
		/** @returns {void} */
		init: function init() {
			var photosData = "";
			var albumsData = "";
			var html = "";

			if (album.json.albums) {
				album.json.albums.forEach(function (_album) {
					albums.parse(_album);
					albumsData += build.album(_album, !album.isUploadable());
				});
			}
			if (album.json.photos) {
				// Build photos
				album.json.photos.forEach(function (_photo) {
					photosData += build.photo(_photo, !album.isUploadable());
				});
			}

			if (photosData !== "") {
				if (lychee.layout === 1) {
					photosData = '<div class="justified-layout">' + photosData + "</div>";
				} else if (lychee.layout === 2) {
					photosData = '<div class="unjustified-layout">' + photosData + "</div>";
				}
			}

			if (albumsData !== "" && photosData !== "") {
				html = build.divider(lychee.locale["ALBUMS"]);
			}
			html += albumsData;
			if (albumsData !== "" && photosData !== "") {
				html += build.divider(lychee.locale["PHOTOS"]);
			}
			html += photosData;

			// Add photos to view
			lychee.content.html(html);
			album.apply_nsfw_filter();

			view.album.content.justify(album.json ? album.json.photos : []);

			view.album.content.restoreScroll();
		},

		/** @returns {void} */
		restoreScroll: function restoreScroll() {
			// Restore scroll position
			var urls = JSON.parse(localStorage.getItem("scroll"));
			var urlWindow = window.location.href;
			$(window).scrollTop(urls != null && urls[urlWindow] ? urls[urlWindow] : 0);
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		title: function title(photoID) {
			var photo = album.getByID(photoID);
			var title = photo.title ? photo.title : lychee.locale["UNTITLED"];

			$('.photo[data-id="' + photoID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		titleSub: function titleSub(albumID) {
			var album = album.getSubByID(albumID);
			var title = album.title ? album.title : lychee.locale["UNTITLED"];

			$('.album[data-id="' + albumID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		star: function star(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-star');

			if (album.getByID(photoID).is_starred) $badge.addClass("badge--star");else $badge.removeClass("badge--star");
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		public: function _public(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-share');

			if (album.getByID(photoID).is_public === 1) $badge.addClass("badge--visible badge--hidden");else $badge.removeClass("badge--visible badge--hidden");
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		cover: function cover(photoID) {
			$(".album .icn-cover").removeClass("badge--cover");
			$(".photo .icn-cover").removeClass("badge--cover");

			if (album.json.cover_id === photoID) {
				var badge = $('.photo[data-id="' + photoID + '"] .icn-cover');
				if (badge.length > 0) {
					badge.addClass("badge--cover");
				} else {
					$.each(album.json.albums, function () {
						if (this.thumb.id === photoID) {
							$('.album[data-id="' + this.id + '"] .icn-cover').addClass("badge--cover");
							return false;
						}
					});
				}
			}
		},

		/**
   * @param {Photo} data
   * @returns {void}
   */
		updatePhoto: function updatePhoto(data) {
			var src = void 0,
			    srcset = "";

			// This mimicks the structure of build.photo
			if (lychee.layout === 0) {
				src = data.size_variants.thumb.url;
				if (data.size_variants.thumb2x !== null) {
					srcset = data.size_variants.thumb2x.url + " 2x";
				}
			} else {
				if (data.size_variants.small !== null) {
					src = data.size_variants.small.url;
					if (data.size_variants.small2x !== null) {
						srcset = data.size_variants.small.url + " " + data.size_variants.small.width + "w, " + data.size_variants.small2x.url + " " + data.size_variants.small2x.width + "w";
					}
				} else if (data.size_variants.medium !== null) {
					src = data.size_variants.medium.url;
					if (data.size_variants.medium2x !== null) {
						srcset = data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w";
					}
				} else if (!data.type || data.type.indexOf("video") !== 0) {
					src = data.size_variants.original.url;
				} else {
					src = data.size_variants.thumb.url;
					if (data.size_variants.thumb2x !== null) {
						srcset = data.size_variants.thumb.url + " " + data.size_variants.thumb.width + "w, " + data.size_variants.thumb2x.url + " " + data.size_variants.thumb2x.width + "w";
					}
				}
			}

			$('.photo[data-id="' + data.id + '"] > span.thumbimg > img').attr("data-src", src).attr("data-srcset", srcset).addClass("lazyload");

			view.album.content.justify(album.json ? album.json.photos : []);
		},

		/**
   * @param {string} photoID
   * @param {boolean} [justify=false]
   * @returns {void}
   */
		delete: function _delete(photoID) {
			var justify = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

			$('.photo[data-id="' + photoID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				// Only when search is not active
				if (album.json) {
					if (visible.sidebar()) {
						var videoCount = 0;
						$.each(album.json.photos, function () {
							if (this.type && this.type.indexOf("video") > -1) {
								videoCount++;
							}
						});
						if (album.json.photos.length - videoCount > 0) {
							_sidebar.changeAttr("images", (album.json.photos.length - videoCount).toString());
						} else {
							_sidebar.hideAttr("images");
						}
						if (videoCount > 0) {
							_sidebar.changeAttr("videos", videoCount.toString());
						} else {
							_sidebar.hideAttr("videos");
						}
					}
					if (album.json.photos.length <= 0) {
						lychee.content.find(".divider").remove();
					}
					if (justify) {
						view.album.content.justify(album.json ? album.json.photos : []);
					}
				}
			});
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		deleteSub: function deleteSub(albumID) {
			$('.album[data-id="' + albumID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (album.json) {
					if (album.json.albums.length <= 0) {
						lychee.content.find(".divider").remove();
					}
					if (visible.sidebar()) {
						if (album.json.albums.length > 0) {
							_sidebar.changeAttr("subalbums", album.json.albums.length.toString());
						} else {
							_sidebar.hideAttr("subalbums");
						}
					}
				}
			});
		},

		/**
   * Lays out the photos inside an album or a search result.
   *
   * This method is a misnomer, because it does not necessarily
   * create a justified layout, but the configured layout as specified
   * by `lychee.layout` which can also be a non-justified layout.
   *
   * Also note that this method is bastardized by `search.find`.
   * Hence, this method would better not be part of `view.album.content`,
   * because it is not exclusively used for an album.
   *
   * @param {Photo[]} photos - the photos to be laid out
   *
   * @returns {void}
   */
		justify: function justify(photos) {
			if (photos.length === 0) return;
			if (lychee.layout === 1) {
				var containerWidth = parseFloat($(".justified-layout").width());
				if (containerWidth === 0) {
					// Triggered on Reload in photo view.
					containerWidth = $(window).width() - parseFloat($(".justified-layout").css("margin-left")) - parseFloat($(".justified-layout").css("margin-right")) - parseFloat($(".content").css("padding-right"));
				}
				/** @type {number[]} */
				var ratio = photos.map(function (_photo) {
					var height = _photo.size_variants.original.height;
					var width = _photo.size_variants.original.width;
					var ratio = height > 0 ? width / height : 1;
					// If there is no small and medium size variants for videos,
					// we have to fall back to square thumbs
					return _photo.type && _photo.type.indexOf("video") !== -1 && _photo.size_variants.small === null && _photo.size_variants.medium === null ? 1 : ratio;
				});

				var layoutGeometry = require("justified-layout")(ratio, {
					containerWidth: containerWidth,
					containerPadding: 0,
					// boxSpacing: {
					//     horizontal: 42,
					//     vertical: 150
					// },
					targetRowHeight: parseFloat($(".photo").css("--lychee-default-height"))
				});
				// if (lychee.admin) console.log(layoutGeometry);
				$(".justified-layout").css("height", layoutGeometry.containerHeight + "px");
				$(".justified-layout > div").each(function (i) {
					if (!layoutGeometry.boxes[i]) {
						// Race condition in search.find -- window content
						// and `photos` can get out of sync as search
						// query is being modified.
						return false;
					}
					$(this).css("top", layoutGeometry.boxes[i].top);
					$(this).css("width", layoutGeometry.boxes[i].width);
					$(this).css("height", layoutGeometry.boxes[i].height);
					$(this).css("left", layoutGeometry.boxes[i].left);

					var imgs = $(this).find(".thumbimg > img");
					if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
						imgs[0].setAttribute("sizes", layoutGeometry.boxes[i].width + "px");
					}
				});
			} else if (lychee.layout === 2) {
				var _containerWidth = parseFloat($(".unjustified-layout").width());
				if (_containerWidth === 0) {
					// Triggered on Reload in photo view.
					_containerWidth = $(window).width() - parseFloat($(".unjustified-layout").css("margin-left")) - parseFloat($(".unjustified-layout").css("margin-right")) - parseFloat($(".content").css("padding-right"));
				}
				// For whatever reason, the calculation of margin is
				// super-slow in Firefox (tested with 68), so we make sure to
				// do it just once, outside the loop.  Height doesn't seem to
				// be affected, but we do it the same way for consistency.
				var margin = parseFloat($(".photo").css("margin-right"));
				var origHeight = parseFloat($(".photo").css("max-height"));
				$(".unjustified-layout > div").each(function (i) {
					if (!photos[i]) {
						// Race condition in search.find -- window content
						// and `photos` can get out of sync as search
						// query is being modified.
						return false;
					}
					var ratio = photos[i].size_variants.original.height > 0 ? photos[i].size_variants.original.width / photos[i].size_variants.original.height : 1;
					if (photos[i].type && photos[i].type.indexOf("video") > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (photos[i].size_variants.small === null && photos[i].size_variants.medium === null) {
							ratio = 1;
						}
					}

					var height = origHeight;
					var width = height * ratio;
					var imgs = $(this).find(".thumbimg > img");

					if (width > _containerWidth - margin) {
						width = _containerWidth - margin;
						height = width / ratio;
					}

					$(this).css("width", width + "px");
					$(this).css("height", height + "px");
					if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
						imgs[0].setAttribute("sizes", width + "px");
					}
				});
			}
		}
	},

	/**
  * @returns {void}
  */
	description: function description() {
		_sidebar.changeAttr("description", album.json.description ? album.json.description : "");
	},

	/**
  * @returns {void}
  */
	show_tags: function show_tags() {
		_sidebar.changeAttr("show_tags", album.json.show_tags.join(", "));
	},

	/**
  * @returns {void}
  */
	license: function license() {
		var license = void 0;
		switch (album.json.license) {
			case "none":
				// TODO: If we do not use `"none"` as a literal string, we should convert `license` to a nullable DB attribute and use `null` for none to be consistent which everything else
				license = ""; // none is displayed as - thus is empty.
				break;
			case "reserved":
				license = lychee.locale["ALBUM_RESERVED"];
				break;
			default:
				license = album.json.license;
				// console.log('default');
				break;
		}

		_sidebar.changeAttr("license", license);
	},

	/**
  * @returns {void}
  */
	public: function _public() {
		$("#button_visibility_album, #button_sharing_album_users").removeClass("active--not-hidden active--hidden");

		if (album.json.is_public) {
			if (album.json.requires_link) {
				$("#button_visibility_album, #button_sharing_album_users").addClass("active--hidden");
			} else {
				$("#button_visibility_album, #button_sharing_album_users").addClass("active--not-hidden");
			}

			$(".photo .iconic-share").remove();

			if (album.json.init) _sidebar.changeAttr("public", lychee.locale["ALBUM_SHR_YES"]);
		} else {
			if (album.json.init) _sidebar.changeAttr("public", lychee.locale["ALBUM_SHR_NO"]);
		}
	},

	/**
  * @returns {void}
  */
	requiresLink: function requiresLink() {
		if (album.json.requires_link) _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	nsfw: function nsfw() {
		if (album.json.is_nsfw) {
			// Sensitive
			$("#button_nsfw_album").addClass("active").attr("title", lychee.locale["ALBUM_UNMARK_NSFW"]);
		} else {
			// Not Sensitive
			$("#button_nsfw_album").removeClass("active").attr("title", lychee.locale["ALBUM_MARK_NSFW"]);
		}
	},

	/**
  * @returns {void}
  */
	downloadable: function downloadable() {
		if (album.json.is_downloadable) _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	shareButtonVisible: function shareButtonVisible() {
		if (album.json.is_share_button_visible) _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	password: function password() {
		if (album.json.has_password) _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	sidebar: function sidebar() {
		if ((visible.album() || album.json && !album.json.init) && !visible.photo()) {
			var structure = _sidebar.createStructure.album(album.json);
			var html = _sidebar.render(structure);

			_sidebar.dom(".sidebar__wrapper").html(html);
			_sidebar.bind();
		}
	}
};

view.photo = {
	/**
  * @param {boolean} autoplay
  * @returns {void}
  */
	init: function init(autoplay) {
		multiselect.clearSelection();

		view.photo.sidebar();
		view.photo.title();
		view.photo.star();
		view.photo.public();
		view.photo.header();
		view.photo.photo(autoplay);

		// TODO: `init` is not a property of the Photo JSON; this is a property of the view. Consider to move it to `view.photo.isInitialized`
		_photo3.json.init = true;
	},

	/**
  * @returns {void}
  */
	show: function show() {
		// Change header
		lychee.content.addClass("view");
		header.setMode("photo");

		// Make body not scrollable
		// use bodyScrollLock package to enable locking on iOS
		// Simple overflow: hidden not working on iOS Safari
		// Only the info pane needs scrolling
		// Touch event for swiping of photo still work

		scrollLock.disablePageScroll($(".sidebar__wrapper").get());

		// Fullscreen
		var timeout = null;
		$(document).bind("mousemove", function () {
			clearTimeout(timeout);
			// For live Photos: header animation only if LivePhoto is not playing
			if (!_photo3.isLivePhotoPlaying() && lychee.header_auto_hide) {
				header.show();
				timeout = setTimeout(header.hideIfLivePhotoNotPlaying, 2500);
			}
		});

		// we also put this timeout to enable it by default when you directly click on a picture.
		if (lychee.header_auto_hide) {
			setTimeout(header.hideIfLivePhotoNotPlaying, 2500);
		}

		lychee.animate(lychee.imageview, "fadeIn");
	},

	/**
  * @returns {void}
  */
	hide: function hide() {
		header.show();

		lychee.content.removeClass("view");
		header.setMode("album");

		// Make body scrollable
		scrollLock.enablePageScroll($(".sidebar__wrapper").get());

		// Disable Fullscreen
		$(document).unbind("mousemove");
		if ($("video").length) {
			$("video")[$("video").length - 1].pause();
		}

		// Hide Photo
		lychee.animate(lychee.imageview, "fadeOut");
		setTimeout(function () {
			lychee.imageview.hide();
			view.album.sidebar();
		}, 300);
	},

	/**
  * @returns {void}
  */
	title: function title() {
		if (_photo3.json.init) _sidebar.changeAttr("title", _photo3.json.title ? _photo3.json.title : "");
		lychee.setTitle(_photo3.json.title ? _photo3.json.title : lychee.locale["UNTITLED"], true);
	},

	/**
  * @returns {void}
  */
	description: function description() {
		if (_photo3.json.init) _sidebar.changeAttr("description", _photo3.json.description ? _photo3.json.description : "");
	},

	/**
  * @returns {void}
  */
	license: function license() {
		var license = void 0;

		// Process key to display correct string
		switch (_photo3.json.license) {
			case "none":
				// TODO: If we do not use `"none"` as a literal string, we should convert `license` to a nullable DB attribute and use `null` for none to be consistent which everything else
				license = ""; // none is displayed as - thus is empty (uniformity of the display).
				break;
			case "reserved":
				license = lychee.locale["PHOTO_RESERVED"];
				break;
			default:
				license = _photo3.json.license;
				break;
		}

		// Update the sidebar if the photo is visible
		if (_photo3.json.init) _sidebar.changeAttr("license", license);
	},

	/**
  * @returns {void}
  */
	star: function star() {
		if (_photo3.json.is_starred) {
			// Starred
			$("#button_star").addClass("active").attr("title", lychee.locale["UNSTAR_PHOTO"]);
		} else {
			// Unstarred
			$("#button_star").removeClass("active").attr("title", lychee.locale["STAR_PHOTO"]);
		}
	},

	/**
  * @returns {void}
  */
	public: function _public() {
		$("#button_visibility").removeClass("active--hidden active--not-hidden");

		if (_photo3.json.is_public === 1 || _photo3.json.is_public === 2) {
			// Photo public
			if (_photo3.json.is_public === 1) {
				$("#button_visibility").addClass("active--hidden");
			} else {
				$("#button_visibility").addClass("active--not-hidden");
			}

			if (_photo3.json.init) _sidebar.changeAttr("public", lychee.locale["PHOTO_SHR_YES"]);
		} else {
			// Photo private
			if (_photo3.json.init) _sidebar.changeAttr("public", lychee.locale["PHOTO_SHR_NO"]);
		}
	},

	/**
  * @returns {void}
  */
	tags: function tags() {
		_sidebar.changeAttr("tags", build.tags(_photo3.json.tags), true);
		_sidebar.bind();
	},

	/**
  * @param {boolean} autoplay
  * @returns {void}
  */
	photo: function photo(autoplay) {
		var ret = build.imageview(_photo3.json, visible.header(), autoplay);
		lychee.imageview.html(ret.html);
		tabindex.makeFocusable(lychee.imageview);

		// Init Live Photo if needed
		if (_photo3.isLivePhoto()) {
			// Package gives warning that function will be remove and
			// shoud be replaced by LivePhotosKit.augementElementAsPlayer
			// But, LivePhotosKit.augementElementAsPlayer is not yet available
			_photo3.livePhotosObject = LivePhotosKit.Player(document.getElementById("livephoto"));
		}

		view.photo.onresize();

		var $nextArrow = lychee.imageview.find("a#next");
		var $previousArrow = lychee.imageview.find("a#previous");
		var photoID = _photo3.getID();
		/** @type {?Photo} */
		var photoInAlbum = album.json && album.json.photos ? album.getByID(photoID) : null;
		/** @type {?Photo} */
		var nextPhotoInAlbum = photoInAlbum && photoInAlbum.next_photo_id ? album.getByID(photoInAlbum.next_photo_id) : null;
		/** @type {?Photo} */
		var prevPhotoInAlbum = photoInAlbum && photoInAlbum.previous_photo_id ? album.getByID(photoInAlbum.previous_photo_id) : null;

		var img = $("img#image");
		if (img.length > 0) {
			if (!img[0].complete || img[0].currentSrc !== null && img[0].currentSrc === "") {
				// Image is still loading.  Display the thumb version in the
				// background.
				if (ret.thumb !== "") {
					img.css("background-image", lychee.html(_templateObject77, ret.thumb));
				}

				// Don't preload next/prev until the requested image is
				// fully loaded.
				img.on("load", function () {
					_photo3.preloadNextPrev(_photo3.getID());
				});
			} else {
				_photo3.preloadNextPrev(_photo3.getID());
			}
		}

		if (nextPhotoInAlbum === null || lychee.viewMode === true) {
			$nextArrow.hide();
		} else {
			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var thumbUrl = "img/placeholder.png";
			if (nextPhotoInAlbum.size_variants.thumb !== null) {
				thumbUrl = nextPhotoInAlbum.size_variants.thumb.url;
			} else if (nextPhotoInAlbum.type.indexOf("video") > -1) {
				thumbUrl = "img/play-icon.png";
			}
			$nextArrow.css("background-image", lychee.html(_templateObject78, thumbUrl));
		}

		if (prevPhotoInAlbum === null || lychee.viewMode === true) {
			$previousArrow.hide();
		} else {
			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var _thumbUrl = "img/placeholder.png";
			if (prevPhotoInAlbum.size_variants.thumb !== null) {
				_thumbUrl = prevPhotoInAlbum.size_variants.thumb.url;
			} else if (prevPhotoInAlbum.type.indexOf("video") > -1) {
				_thumbUrl = "img/play-icon.png";
			}
			$previousArrow.css("background-image", lychee.html(_templateObject78, _thumbUrl));
		}
	},

	/**
  * @returns {void}
  */
	sidebar: function sidebar() {
		var structure = _sidebar.createStructure.photo(_photo3.json);
		var html = _sidebar.render(structure);
		var has_location = !!(_photo3.json.latitude && _photo3.json.longitude);

		_sidebar.dom(".sidebar__wrapper").html(html);
		_sidebar.bind();

		if (has_location && lychee.map_display) {
			// Leaflet searches for icon in same directory as js file -> paths needs
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: "img/marker-icon-2x.png",
				iconUrl: "img/marker-icon.png",
				shadowUrl: "img/marker-shadow.png"
			});

			var myMap = L.map("leaflet_map_single_photo").setView([_photo3.json.latitude, _photo3.json.longitude], 13);

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(myMap);

			if (!lychee.map_display_direction || !_photo3.json.img_direction) {
				// Add Marker to map, direction is not set
				L.marker([_photo3.json.latitude, _photo3.json.longitude]).addTo(myMap);
			} else {
				// Add Marker, direction has been set
				var viewDirectionIcon = L.icon({
					iconUrl: "img/view-angle-icon.png",
					iconRetinaUrl: "img/view-angle-icon-2x.png",
					iconSize: [100, 58], // size of the icon
					iconAnchor: [50, 49] // point of the icon which will correspond to marker's location
				});
				var marker = L.marker([_photo3.json.latitude, _photo3.json.longitude], { icon: viewDirectionIcon }).addTo(myMap);
				marker.setRotationAngle(_photo3.json.img_direction);
			}
		}
	},

	/**
  * @returns {void}
  */
	header: function header() {
		/* Note: the condition below is duplicated in contextMenu.photoMore() */
		if (_photo3.json.type && (_photo3.json.type.indexOf("video") === 0 || _photo3.json.type === "raw") || _photo3.json.live_photo_url !== "" && _photo3.json.live_photo_url !== null) {
			$("#button_rotate_cwise, #button_rotate_ccwise").hide();
		} else {
			$("#button_rotate_cwise, #button_rotate_ccwise").show();
		}
	},

	/**
  * @returns {void}
  */
	onresize: function onresize() {
		if (!_photo3.json || _photo3.json.size_variants.medium === null || _photo3.json.size_variants.medium2x === null) return;

		// Calculate the width of the image in the current window without
		// borders and set 'sizes' to it.
		var imgWidth = _photo3.json.size_variants.medium.width;
		var imgHeight = _photo3.json.size_variants.medium.height;
		var containerWidth = $(window).outerWidth();
		var containerHeight = $(window).outerHeight();

		// Image can be no larger than its natural size, but it can be
		// smaller depending on the size of the window.
		var width = imgWidth < containerWidth ? imgWidth : containerWidth;
		var height = width * imgHeight / imgWidth;
		if (height > containerHeight) {
			width = containerHeight * imgWidth / imgHeight;
		}

		$("img#image").attr("sizes", width + "px");
	}
};

view.settings = {
	/**
  * @returns {void}
  */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.settings.title();
		header.setMode("config");
		view.settings.content.init();
	},

	/**
  * @returns {void}
  */
	title: function title() {
		lychee.setTitle(lychee.locale["SETTINGS"], false);
	},

	/**
  * @returns {void}
  */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		/**
   * @returns {void}
   */
		init: function init() {
			view.settings.clearContent();
			view.settings.content.setLogin();
			if (lychee.admin) {
				view.settings.content.setSorting();
				view.settings.content.setDropboxKey();
				view.settings.content.setLang();
				view.settings.content.setDefaultLicense();
				view.settings.content.setLayout();
				view.settings.content.setPublicSearch();
				view.settings.content.setOverlayType();
				view.settings.content.setMapDisplay();
				view.settings.content.setNSFWVisible();
				view.settings.content.setNotification();
				view.settings.content.setCSS();
				view.settings.content.moreButton();
			}
		},

		/**
   * @returns {void}
   */
		setLogin: function setLogin() {
			var msg = lychee.html(_templateObject79, lychee.locale["PASSWORD_TITLE"], lychee.locale["USERNAME_CURRENT"], lychee.locale["PASSWORD_CURRENT"], lychee.locale["PASSWORD_TEXT"], lychee.locale["LOGIN_USERNAME"], lychee.locale["LOGIN_PASSWORD"], lychee.locale["LOGIN_PASSWORD_CONFIRM"], lychee.locale["PASSWORD_CHANGE"]);

			$(".settings_view").append(msg);

			settings.bind("#basicModal__action_password_change", ".setLogin", settings.changeLogin);
		},

		/**
   * @returns {void}
   */
		clearLogin: function clearLogin() {
			$("input[name=oldUsername], input[name=oldPassword], input[name=username], input[name=password], input[name=confirm]").val("");
		},

		/**
   * Renders the area of the settings related to sorting
   *
   * TODO: Note, the method is a misnomer.
   * It does not **set** any sorting, see {@link settings.changeSorting}
   * for that.
   * This method only creates the HTML GUI.
   *
   * @returns {void}
   */
		setSorting: function setSorting() {
			var msg = lychee.html(_templateObject80, lychee.locale["SORT_ALBUM_BY_1"], lychee.locale["SORT_ALBUM_SELECT_1"], lychee.locale["SORT_ALBUM_SELECT_2"], lychee.locale["SORT_ALBUM_SELECT_3"], lychee.locale["SORT_ALBUM_SELECT_4"], lychee.locale["SORT_ALBUM_SELECT_5"], lychee.locale["SORT_ALBUM_SELECT_6"], lychee.locale["SORT_ALBUM_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_ALBUM_BY_3"], lychee.locale["SORT_PHOTO_BY_1"], lychee.locale["SORT_PHOTO_SELECT_1"], lychee.locale["SORT_PHOTO_SELECT_2"], lychee.locale["SORT_PHOTO_SELECT_3"], lychee.locale["SORT_PHOTO_SELECT_4"], lychee.locale["SORT_PHOTO_SELECT_5"], lychee.locale["SORT_PHOTO_SELECT_6"], lychee.locale["SORT_PHOTO_SELECT_7"], lychee.locale["SORT_PHOTO_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_PHOTO_BY_3"], lychee.locale["SORT_CHANGE"]);

			$(".settings_view").append(msg);

			if (lychee.sorting_albums) {
				$(".setSorting select#settings_albums_sorting_column").val(lychee.sorting_albums.column);
				$(".setSorting select#settings_albums_sorting_order").val(lychee.sorting_albums.order);
			}

			if (lychee.sorting_photos) {
				$(".setSorting select#settings_photos_sorting_column").val(lychee.sorting_photos.column);
				$(".setSorting select#settings_photos_sorting_order").val(lychee.sorting_photos.order);
			}

			settings.bind("#basicModal__action_sorting_change", ".setSorting", settings.changeSorting);
		},

		/**
   * @returns {void}
   */
		setDropboxKey: function setDropboxKey() {
			var msg = "\n\t\t\t<div class=\"setDropBox\">\n\t\t\t  <p>" + lychee.locale["DROPBOX_TEXT"] + "\n\t\t\t  <input class='text' name='key' type='text' placeholder='Dropbox API Key' value='" + lychee.dropboxKey + "'>\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<a id=\"basicModal__action_dropbox_change\" class=\"basicModal__button\">" + lychee.locale["DROPBOX_TITLE"] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_dropbox_change", ".setDropBox", settings.changeDropboxKey);
		},

		/**
   * @returns {void}
   */
		setLang: function setLang() {
			var msg = "\n\t\t\t\t<div class=\"setLang\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t" + lychee.locale["LANG_TEXT"] + "\n\t\t\t  \t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_lang\" name=\"lang\">\n\t\t\t\t\t\t\t\t" + lychee.lang_available.reduce(function (html, lang_av) {
				return html + (lychee.lang === lang_av ? "<option selected>" : "<option>") + lang_av + "</option>";
			}, "") + "\n\t\t\t\t\t\t\t</select>\n\t\t\t  \t\t\t</span>\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<a id=\"basicModal__action_set_lang\" class=\"basicModal__button\">" + lychee.locale["LANG_TITLE"] + "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_set_lang", ".setLang", settings.changeLang);
		},

		/**
   * @returns {void}
   */
		setDefaultLicense: function setDefaultLicense() {
			var msg = "\n\t\t\t<div class=\"setDefaultLicense\">\n\t\t\t<p>" + lychee.locale["DEFAULT_LICENSE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["PHOTO_LICENSE_NONE"] + "</option>\n\t\t\t\t\t<option value=\"reserved\">" + lychee.locale["PHOTO_RESERVED"] + "</option>\n\t\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<br />\n\t\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">" + lychee.locale["PHOTO_LICENSE_HELP"] + "</a>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_license\" class=\"basicModal__button\">" + lychee.locale["SET_LICENSE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#license").val(lychee.default_license === "" ? "none" : lychee.default_license);
			settings.bind("#basicModal__action_set_license", ".setDefaultLicense", settings.setDefaultLicense);
		},

		/**
   * @returns {void}
   */
		setLayout: function setLayout() {
			var msg = "\n\t\t\t<div class=\"setLayout\">\n\t\t\t<p>" + lychee.locale["LAYOUT_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"layout\" id=\"layout\">\n\t\t\t\t\t<option value=\"0\">" + lychee.locale["LAYOUT_SQUARES"] + "</option>\n\t\t\t\t\t<option value=\"1\">" + lychee.locale["LAYOUT_JUSTIFIED"] + "</option>\n\t\t\t\t\t<option value=\"2\">" + lychee.locale["LAYOUT_UNJUSTIFIED"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_layout\" class=\"basicModal__button\">" + lychee.locale["SET_LAYOUT"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#layout").val(lychee.layout);
			settings.bind("#basicModal__action_set_layout", ".setLayout", settings.setLayout);
		},

		/**
   * @returns {void}
   */
		setPublicSearch: function setPublicSearch() {
			var msg = "\n\t\t\t<div class=\"setPublicSearch\">\n\t\t\t<p>" + lychee.locale["PUBLIC_SEARCH_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"PublicSearch\" type=\"checkbox\" name=\"public_search\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.public_search) $("#PublicSearch").click();

			settings.bind("#PublicSearch", ".setPublicSearch", settings.changePublicSearch);
		},

		/**
   * @returns {void}
   */
		setNSFWVisible: function setNSFWVisible() {
			var msg = "\n\t\t\t<div class=\"setNSFWVisible\">\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_1"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"NSFWVisible\" type=\"checkbox\" name=\"nsfw_visible\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label></p>\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_2"] + "\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.nsfw_visible_saved) {
				$("#NSFWVisible").click();
			}

			settings.bind("#NSFWVisible", ".setNSFWVisible", settings.changeNSFWVisible);
		},
		// TODO: extend to the other settings.

		/**
   * @returns {void}
   */
		setOverlayType: function setOverlayType() {
			var msg = "\n\t\t\t<div class=\"setOverlayType\">\n\t\t\t<p>" + lychee.locale["OVERLAY_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"image_overlay_type\" id=\"ImgOverlayType\">\n\t\t\t\t\t<option value=\"exif\">" + lychee.locale["OVERLAY_EXIF"] + "</option>\n\t\t\t\t\t<option value=\"desc\">" + lychee.locale["OVERLAY_DESCRIPTION"] + "</option>\n\t\t\t\t\t<option value=\"date\">" + lychee.locale["OVERLAY_DATE"] + "</option>\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["OVERLAY_NONE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_overlay_type\" class=\"basicModal__button\">" + lychee.locale["SET_OVERLAY_TYPE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#ImgOverlayType").val(!lychee.image_overlay_type_default ? "exif" : lychee.image_overlay_type_default);
			settings.bind("#basicModal__action_set_overlay_type", ".setOverlayType", settings.setOverlayType);
		},

		/**
   * @returns {void}
   */
		setMapDisplay: function setMapDisplay() {
			var msg = "\n\t\t\t<div class=\"setMapDisplay\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapDisplay\" type=\"checkbox\" name=\"map_display\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display) $("#MapDisplay").click();

			settings.bind("#MapDisplay", ".setMapDisplay", settings.changeMapDisplay);

			msg = "\n\t\t\t<div class=\"setMapDisplayPublic\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_PUBLIC_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"MapDisplayPublic\" type=\"checkbox\" name=\"map_display_public\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display_public) $("#MapDisplayPublic").click();

			settings.bind("#MapDisplayPublic", ".setMapDisplayPublic", settings.changeMapDisplayPublic);

			msg = "\n\t\t\t<div class=\"setMapProvider\">\n\t\t\t<p>" + lychee.locale["MAP_PROVIDER"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"map_provider\" id=\"MapProvider\">\n\t\t\t\t\t<option value=\"Wikimedia\">" + lychee.locale["MAP_PROVIDER_WIKIMEDIA"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.org\">" + lychee.locale["MAP_PROVIDER_OSM_ORG"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.de\">" + lychee.locale["MAP_PROVIDER_OSM_DE"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.fr\">" + lychee.locale["MAP_PROVIDER_OSM_FR"] + "</option>\n\t\t\t\t\t<option value=\"RRZE\">" + lychee.locale["MAP_PROVIDER_RRZE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_map_provider\" class=\"basicModal__button\">" + lychee.locale["SET_MAP_PROVIDER"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#MapProvider").val(!lychee.map_provider ? "Wikimedia" : lychee.map_provider);
			settings.bind("#basicModal__action_set_map_provider", ".setMapProvider", settings.setMapProvider);

			msg = "\n\t\t\t<div class=\"setMapIncludeSubAlbums\">\n\t\t\t<p>" + lychee.locale["MAP_INCLUDE_SUBALBUMS_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapIncludeSubAlbums\" type=\"checkbox\" name=\"map_include_subalbums\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_include_subalbums) $("#MapIncludeSubAlbums").click();

			settings.bind("#MapIncludeSubAlbums", ".setMapIncludeSubAlbums", settings.changeMapIncludeSubAlbums);

			msg = "\n\t\t\t<div class=\"setLocationDecoding\">\n\t\t\t<p>" + lychee.locale["LOCATION_DECODING"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationDecoding\" type=\"checkbox\" name=\"location_decoding\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_decoding) $("#LocationDecoding").click();

			settings.bind("#LocationDecoding", ".setLocationDecoding", settings.changeLocationDecoding);

			msg = "\n\t\t\t<div class=\"setLocationShow\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationShow\" type=\"checkbox\" name=\"location_show\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show) $("#LocationShow").click();

			settings.bind("#LocationShow", ".setLocationShow", settings.changeLocationShow);

			msg = "\n\t\t\t<div class=\"setLocationShowPublic\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW_PUBLIC"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"LocationShowPublic\" type=\"checkbox\" name=\"location_show_public\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show_public) $("#LocationShowPublic").click();

			settings.bind("#LocationShowPublic", ".setLocationShowPublic", settings.changeLocationShowPublic);
		},

		/**
   * @returns {void}
   */
		setNotification: function setNotification() {
			var msg = "\n\t\t\t<div class=\"setNewPhotosNotification\">\n\t\t\t<p>" + lychee.locale["NEW_PHOTOS_NOTIFICATION"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"NewPhotosNotification\" type=\"checkbox\" name=\"new_photos_notification\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.new_photos_notification) $("#NewPhotosNotification").click();

			settings.bind("#NewPhotosNotification", ".setNewPhotosNotification", settings.changeNewPhotosNotification);
		},

		/**
   * @returns {void}
   */
		setCSS: function setCSS() {
			var msg = "\n\t\t\t<div class=\"setCSS\">\n\t\t\t<p>" + lychee.locale["CSS_TEXT"] + "</p>\n\t\t\t<textarea id=\"css\"></textarea>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_css\" class=\"basicModal__button\">" + lychee.locale["CSS_TITLE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			var css_addr = $($("link")[1]).attr("href");

			api.getCSS(css_addr, function (data) {
				$("#css").html(data);
			});

			settings.bind("#basicModal__action_set_css", ".setCSS", settings.changeCSS);
		},

		/**
   * @returns {void}
   */
		moreButton: function moreButton() {
			var msg = lychee.html(_templateObject81, lychee.locale["MORE"]);

			$(".settings_view").append(msg);

			$("#basicModal__action_more").on("click", view.full_settings.init);
		}
	}
};

view.full_settings = {
	/**
  * @returns {void}
  */
	init: function init() {
		multiselect.clearSelection();

		view.full_settings.title();
		view.full_settings.content.init();
	},

	/**
  * @returns {void}
  */
	title: function title() {
		lychee.setTitle("Full Settings", false);
	},

	/**
  * @returns {void}
  */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		init: function init() {
			view.full_settings.clearContent();

			api.post("Settings::getAll", {},
			/** @param {ConfigSetting[]} data */
			function (data) {
				var msg = lychee.html(_templateObject82, lychee.locale["SETTINGS_WARNING"]);

				var prev = "";
				data.forEach(function (_config) {
					if (_config.cat && prev !== _config.cat) {
						msg += lychee.html(_templateObject83, _config.cat);
						prev = _config.cat;
					}
					// prevent 'null' string for empty values
					var val = _config.value ? _config.value : "";
					msg += lychee.html(_templateObject84, _config.key, _config.key, val);
				});

				msg += lychee.html(_templateObject85, lychee.locale["SAVE_RISK"]);

				$(".settings_view").append(msg);

				settings.bind("#FullSettingsSave_button", "#fullSettings", settings.save);

				$("#fullSettings").on("keypress", function (e) {
					settings.save_enter(e);
				});
			});
		}
	}
};

view.notifications = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.notifications.title();
		header.setMode("config");
		view.notifications.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Notifications", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.notifications.clearContent();

			var html = "\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t<p>" + lychee.locale["USER_EMAIL_INSTRUCTION"] + "</p>\n\t\t\t\t</div><div class=\"setLogin\">\n\t\t\t\t\t<p id=\"UserUpdate\">\n\t\t\t\t\t\tEnter your email address:\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\tname=\"email\" class=\"text\" type=\"text\"\n\t\t\t\t\t\t\tplaceholder=\"email@example.com\"\n\t\t\t\t\t\t\tvalue=\"" + (notifications.json && notifications.json.email ? notifications.json.email : "") + "\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<a id=\"UserUpdate_button\" class=\"basicModal__button\">Save</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>";

			$(".settings_view").append(html);
			settings.bind("#UserUpdate_button", "#UserUpdate", notifications.update);
		}
	}
};

view.users = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.users.title();
		header.setMode("config");
		view.users.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Users", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="users_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.users.clearContent();

			if (users.json.length === 0) {
				$(".users_view").append('<div class="users_view_line" style="margin-bottom: 50px;"><p style="text-align: center">User list is empty!</p></div>');
			}

			var html = "\n\t\t\t\t<div class=\"users_view_line\"><p>\n\t\t\t\t\t<span class=\"text\">username</span>\n\t\t\t\t\t<span class=\"text\">new password</span>\n\t\t\t\t\t<span class=\"text_icon\" title=\"Allow uploads\">\n\t\t\t\t\t\t" + build.iconic("data-transfer-upload") + "\n\t\t\t\t\t</span>\n\t\t\t\t\t<span class=\"text_icon\" title=\"Restricted account\">\n\t\t\t\t\t\t" + build.iconic("lock-locked") + "\n\t\t\t\t\t</span>\n\t\t\t\t</p></div>";

			$(".users_view").append(html);

			users.json.forEach(function (_user) {
				$(".users_view").append(build.user(_user));
				// TODO: Instead of binding an event handler to each input element it would be much more efficient, to bind a single event handler to the common parent view, let the event bubble up the DOM tree and use the `originalElement` property of the event to get the input element which caused the event.
				settings.bind("#UserUpdate" + _user.id, "#UserData" + _user.id, users.update);
				settings.bind("#UserDelete" + _user.id, "#UserData" + _user.id, users.delete);
				if (_user.may_upload) {
					$("#UserData" + _user.id + ' .choice input[name="may_upload"]').click();
				}
				if (_user.is_locked) {
					$("#UserData" + _user.id + ' .choice input[name="is_locked"]').click();
				}
			});

			html = "\n\t\t\t\t<div class=\"users_view_line\" " + (users.json.length === 0 ? 'style="padding-top: 0px;"' : "") + ">\n\t\t\t\t\t<p id=\"UserCreate\">\n\t\t\t\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"\" placeholder=\"new username\" />\n\t\t\t\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t\t\t\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t\t\t\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</p>\n\t\t\t\t\t<a id=\"UserCreate_button\"  class=\"basicModal__button basicModal__button_CREATE\">Create</a>\n\t\t\t\t</div>";
			$(".users_view").append(html);
			settings.bind("#UserCreate_button", "#UserCreate", users.create);
		}
	}
};

view.sharing = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.sharing.title();
		header.setMode("config");
		view.sharing.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Sharing", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="sharing_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.sharing.clearContent();

			if (sharing.json.shared.length === 0) {
				$(".sharing_view").append('<div class="sharing_view_line" style="margin-bottom: 50px;"><p style="text-align: center">Sharing list is empty!</p></div>');
			}

			var albumOptions = sharing.json.albums.reduce(function (acc, _album) {
				return acc + ("<option value=\"" + _album.id + "\">" + _album.title + "</option>");
			}, "");

			var userOptions = sharing.json.users.reduce(function (acc, _user) {
				return acc + ("<option value=\"" + _user.id + "\">" + _user.username + "</option>");
			}, "");

			var sharingOptions = sharing.json.shared.reduce(function (acc, _shareInfo) {
				return acc + ("\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t<span class=\"text\">" + _shareInfo.title + "</span>\n\t\t\t\t\t\t\t<span class=\"text\">" + _shareInfo.username + "</span>\n\t\t\t\t\t\t\t<span class=\"choice\">\n\t\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"remove_id\" value=\"" + _shareInfo.id + "\"/>\n\t\t\t\t\t\t\t\t\t<span class=\"checkbox\">\n\t\t\t\t\t\t\t\t\t\t<svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</p>");
			}, "");

			var html = "\n\t\t\t\t<div class=\"sharing_view_line\"><p>Share</p></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"from\" id=\"albums_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">\n\t\t\t\t\t\t\t" + albumOptions + "\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightAll\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-forward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightSelected\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-right") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftSelected\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-left") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftAll\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-backward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"to\" id=\"albums_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sharing_view_line\"><p class=\"with\">with</p></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"from\" id=\"user_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">\n\t\t\t\t\t\t\t" + userOptions + "\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_rightAll\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-forward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_rightSelected\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-right") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_leftSelected\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-left") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_leftAll\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-backward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"to\" id=\"user_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sharing_view_line\"><a id=\"Share_button\" class=\"basicModal__button\">Share</a></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t" + sharingOptions + "\n\t\t\t\t</div>";

			if (sharing.json.shared.length !== 0) {
				html += '<div class="sharing_view_line"><a id="Remove_button"  class="basicModal__button">Remove</a></div>';
			}

			$(".sharing_view").append(html);

			$("#albums_list").multiselect();
			$("#user_list").multiselect();
			$("#Share_button").on("click", sharing.add).on("mouseenter", function () {
				$("#albums_list_to, #user_list_to").addClass("borderBlue");
			}).on("mouseleave", function () {
				$("#albums_list_to, #user_list_to").removeClass("borderBlue");
			});

			$("#Remove_button").on("click", sharing.delete);
		}
	}
};

view.logs = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.logs.title();
		header.setMode("config");
		view.logs.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Logs", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		var html = lychee.html(_templateObject86, lychee.locale["CLEAN_LOGS"]);
		lychee.content.html(html);

		$("#Clean_Noise").on("click", function () {
			api.post("Logs::clearNoise", {}, view.logs.init);
		});
	},

	content: {
		/** @returns {void} */
		init: function init() {
			/**
    * @param {LogEntry[]} logEntries
    * @returns {void}
    */
			var successHandler = function successHandler(logEntries) {
				/**
     * TODO: Consider moving this method to `lychee.locale`
     * @param {Date} datetime
     * @returns {string}
     */
				var formatDateTime = function formatDateTime(datetime) {
					return "" + datetime.getUTCFullYear() + "-" + String(datetime.getUTCMonth() + 1).padStart(2, "0") + "-" + String(datetime.getUTCDate()).padStart(2, "0") + " " + String(datetime.getUTCHours()).padStart(2, "0") + ":" + String(datetime.getUTCMinutes()).padStart(2, "0") + ":" + String(datetime.getUTCSeconds()).padStart(2, "0") + " UTC";
				};
				var html = logEntries.reduce(function (acc, logEntry) {
					return acc + formatDateTime(new Date(logEntry.created_at)) + " -- " + logEntry.type.padEnd(7) + " -- " + logEntry.function + " -- " + logEntry.line + " -- " + logEntry.text + "\n";
				}, "<pre>") + "</pre>";
				$(".logs_diagnostics_view").html(html);
			};

			view.logs.clearContent();
			api.post("Logs::list", {}, successHandler);
		}
	}
};

view.diagnostics = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.diagnostics.title();
		header.setMode("config");
		view.diagnostics.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Diagnostics", false);
	},

	/**
  * @param {number} update - The update status: `0`: not on master branch;
  *                          `1`: up-to-date; `2`: not up-to-date;
  *                          `3`: requires migration
  * @returns {void}
  */
	clearContent: function clearContent(update) {
		var html = "";

		if (update === 2) {
			html = view.diagnostics.button("", lychee.locale["UPDATE_AVAILABLE"]);
		} else if (update === 3) {
			html = view.diagnostics.button("", lychee.locale["MIGRATION_AVAILABLE"]);
		} else if (update > 0) {
			html = view.diagnostics.button("Check_", lychee.locale["CHECK_FOR_UPDATE"]);
		}

		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	/**
  * @param {string} type
  * @param {string} locale
  * @returns {string} - HTML
  */
	button: function button(type, locale) {
		return "\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"" + type + "Update_Lychee\" class=\"basicModal__button\">" + locale + "</a>\n\t\t\t</div>";
	},

	/** @returns {string} */
	bind: function bind() {
		$("#Update_Lychee").on("click", view.diagnostics.call_apply_update);
		$("#Check_Update_Lychee").on("click", view.diagnostics.call_check_update);
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.diagnostics.clearContent(0);
			api.post("Diagnostics::get", {}, view.diagnostics.content.parseResponse);
		},

		/**
   * @param {DiagnosticInfo} data
   * @returns {void}
   */
		parseResponse: function parseResponse(data) {
			view.diagnostics.clearContent(data.update);
			var html = "";

			html += view.diagnostics.content.block("error", "Diagnostics", data.errors);
			html += view.diagnostics.content.block("sys", "System Information", data.infos);
			html += '<a id="Get_Size_Lychee" class="basicModal__button button_left">';
			html += '<svg class="iconic"><use xlink:href="#reload"></use></svg>';
			html += lychee.html(_templateObject31, lychee.locale["DIAGNOSTICS_GET_SIZE"]);
			html += "</a>";
			html += view.diagnostics.content.block("conf", "Config Information", data.configs);

			$(".logs_diagnostics_view").html(html);

			view.diagnostics.bind();

			$("#Get_Size_Lychee").on("click", view.diagnostics.call_get_size);
		},

		/**
   * @param {string} id
   * @param {string} title
   * @param {string[]} arr
   * @returns {string} - HTML
   */
		block: function block(id, title, arr) {
			var html = "";
			html += '<pre id="content_diag_' + id + '">\n\n\n\n';
			html += "    " + title + "\n";
			html += "    ".padEnd(title.length, "-") + "\n";
			html += arr.reduce(function (acc, line) {
				return acc + "    " + line + "\n";
			}, "");
			html += "</pre>\n";
			return html;
		}
	},

	/** @returns {void} */
	call_check_update: function call_check_update() {
		api.post("Update::check", {},
		/** @param {{updateStatus: string}} data */
		function (data) {
			loadingBar.show("success", data.updateStatus);
			$("#Check_Update_Lychee").remove();
		});
	},

	/** @returns {void} */
	call_apply_update: function call_apply_update() {
		api.post("Update::apply", {},
		/** @param {{updateMsgs: string[]}} data */
		function (data) {
			var html = view.preify(data.updateMsgs, "");
			$("#Update_Lychee").remove();
			$(html).prependTo(".logs_diagnostics_view");
		});
	},

	/** @returns {void} */
	call_get_size: function call_get_size() {
		api.post("Diagnostics::getSize", {},
		/** @param {string[]} data */
		function (data) {
			var html = view.preify(data, "");
			$("#Get_Size_Lychee").remove();
			$(html).appendTo("#content_diag_sys");
		});
	}
};

view.update = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.update.title();
		header.setMode("config");
		view.update.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Update", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		var html = '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	content: {
		init: function init() {
			view.update.clearContent();

			// code duplicate
			api.post("Update::apply", {},
			/** @param {{updateMsgs: string[]}} data */
			function (data) {
				var html = view.preify(data.updateMsgs, "");
				lychee.content.html(html);
			});
		}
	}
};

/**
 * @param {string[]} data
 * @param {string} cssClass
 * @returns {string} - HTML which wraps `data` into a `<pre>`-tag
 */
view.preify = function (data, cssClass) {
	return data.reduce(function (acc, line) {
		return acc + "    " + line + "\n";
	}, '<pre class="' + cssClass + '">') + "</pre>";
};

view.u2f = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.u2f.title();
		header.setMode("config");
		view.u2f.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle(lychee.locale["U2F"], false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="u2f_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.u2f.clearContent();

			if (u2f.json.length === 0) {
				$(".u2f_view").append('<div class="u2f_view_line"><p class="single">Credentials list is empty!</p></div>');
			} else {
				var _html2 = "\n\t\t\t\t\t<div class=\"u2f_view_line\"><p><span class=\"text\">\n\t\t\t\t\t\t" + lychee.locale["U2F_CREDENTIALS"] + "\n\t\t\t\t\t</span></p></div>";

				$(".u2f_view").append(_html2);

				u2f.json.forEach(function (credential) {
					// TODO: Don't query the DOM for the same element in each loop iteration
					$(".u2f_view").append(build.u2f(credential));
					settings.bind("#CredentialDelete" + credential.id, "#CredentialData" + credential.id, u2f.delete);
				});
			}

			var html = "\n\t\t\t\t<div class=\"u2f_view_line\">\n\t\t\t\t\t<a id=\"RegisterU2FButton\"  class=\"basicModal__button basicModal__button_CREATE\">\n\t\t\t\t\t\t" + lychee.locale["U2F_REGISTER_KEY"] + "\n\t\t\t\t\t</a>\n\t\t\t\t</div>";
			$(".u2f_view").append(html);
			$("#RegisterU2FButton").on("click", u2f.register);
		}
	}
};

/**
 * @description This module is used to check if elements are visible or not.
 */

var visible = {};

/** @returns {boolean} */
visible.albums = function () {
	return !!header.dom(".header__toolbar--public").hasClass("header__toolbar--visible") || !!header.dom(".header__toolbar--albums").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.album = function () {
	return !!header.dom(".header__toolbar--album").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.photo = function () {
	return $("#imageview.fadeIn").length > 0;
};

/** @returns {boolean} */
visible.mapview = function () {
	return $("#mapview.fadeIn").length > 0;
};

/** @returns {boolean} */
visible.config = function () {
	return !!header.dom(".header__toolbar--config").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.search = function () {
	return search.json !== null;
};

/** @returns {boolean} */
visible.sidebar = function () {
	return !!_sidebar.dom().hasClass("active");
};

/** @returns {boolean} */
visible.sidebarbutton = function () {
	return visible.photo() || visible.album() && $("#button_info_album:visible").length > 0;
};

/** @returns {boolean} */
visible.header = function () {
	return !header.dom().hasClass("header--hidden");
};

/** @returns {boolean} */
visible.contextMenu = function () {
	return basicContext.visible();
};

/** @returns {boolean} */
visible.multiselect = function () {
	return $("#multiselect").length > 0;
};

/** @returns {boolean} */
visible.leftMenu = function () {
	return !!leftMenu.dom().hasClass("leftMenu__visible");
};

/**
 * @typedef {Object} LycheeException
 * @property {string} message     the message of the exception
 * @property {string} exception   the (base) name of the exception class; in developer mode the backend reports the full class name, in productive mode only the base name
 * @property {string} [file]      the file name where the exception has been thrown; only in developer mode
 * @property {number} [line]      the line number where the exception has been thrown; only in developer mode
 * @property {Array} [trace]      the backtrace; only in developer mode
 * @property {?LycheeException} [previous_exception] the previous exception, if any; only in developer mode
 */

/**
 * @typedef Photo
 *
 * @property {string}       id
 * @property {string}       title
 * @property {?string}      description
 * @property {string[]}     tags
 * @property {number}       is_public
 * @property {?string}      type
 * @property {?string}      iso
 * @property {?string}      aperture
 * @property {?string}      make
 * @property {?string}      model
 * @property {?string}      lens
 * @property {?string}      shutter
 * @property {?string}      focal
 * @property {?number}      latitude
 * @property {?number}      longitude
 * @property {?number}      altitude
 * @property {?number}      img_direction
 * @property {?string}      location
 * @property {?string}      taken_at
 * @property {?string}      taken_at_orig_tz
 * @property {boolean}      is_starred
 * @property {?string}      live_photo_url
 * @property {?string}      album_id
 * @property {string}       checksum
 * @property {string}       license
 * @property {string}       created_at
 * @property {string}       updated_at
 * @property {?string}      live_photo_content_id
 * @property {?string}      live_photo_checksum
 * @property {SizeVariants} size_variants
 * @property {boolean}      is_downloadable
 * @property {boolean}      is_share_button_visible
 * @property {?string}      [next_photo_id]
 * @property {?string}      [previous_photo_id]
 */

/**
 * @typedef SizeVariants
 *
 * @property {SizeVariant}  original
 * @property {?SizeVariant} medium2x
 * @property {?SizeVariant} medium
 * @property {?SizeVariant} small2x
 * @property {?SizeVariant} small
 * @property {?SizeVariant} thumb2x
 * @property {?SizeVariant} thumb
 */

/**
 * @typedef SizeVariant
 *
 * @property {number} type
 * @property {string} url
 * @property {number} width
 * @property {number} height
 * @property {number} filesize
 */

/**
 * @typedef SortingCriterion
 *
 * @property {string} column
 * @property {string} order
 */

/**
 * @typedef Album
 *
 * @property {string}  id
 * @property {string}  parent_id
 * @property {string}  created_at
 * @property {string}  updated_at
 * @property {string}  title
 * @property {?string} description
 * @property {string}  license
 * @property {Photo[]} photos
 * @property {Album[]} [albums]
 * @property {?string} cover_id
 * @property {?Thumb}  thumb
 * @property {string}  [owner_name] optional, only shown in authenticated mode
 * @property {boolean} is_public
 * @property {boolean} is_downloadable
 * @property {boolean} is_share_button_visible
 * @property {boolean} is_nsfw
 * @property {boolean} grants_full_photo
 * @property {boolean} requires_link
 * @property {boolean} has_password
 * @property {boolean} has_albums
 * @property {?string} min_taken_at
 * @property {?string} max_taken_at
 * @property {?SortingCriterion} sorting
 */

/**
 * @typedef TagAlbum
 *
 * @property {string}   id
 * @property {string}   created_at
 * @property {string}   updated_at
 * @property {string}   title
 * @property {?string}  description
 * @property {string[]} show_tags
 * @property {Photo[]}  photos
 * @property {?Thumb}   thumb
 * @property {string}   [owner_name] optional, only shown in authenticated mode
 * @property {boolean}  is_public
 * @property {boolean}  is_downloadable
 * @property {boolean}  is_share_button_visible
 * @property {boolean}  is_nsfw
 * @property {boolean}  grants_full_photo
 * @property {boolean}  requires_link
 * @property {boolean}  has_password
 * @property {?string}  min_taken_at
 * @property {?string}  max_taken_at
 * @property {?SortingCriterion}  sorting
 * @property {boolean}  is_tag_album always true
 */

/**
 * @typedef SmartAlbum
 *
 * @property {string}  id
 * @property {string}  title
 * @property {Photo[]} photos
 * @property {?Thumb}  thumb
 * @property {boolean} is_public
 * @property {boolean} is_downloadable
 * @property {boolean} is_share_button_visible
 */

/**
 * @typedef Thumb
 *
 * @property {string}  id
 * @property {string}  type
 * @property {string}  thumb
 * @property {?string} thumb2x
 */

/**
 * @typedef SharingInfo
 *
 * DTO returned by `Sharing::list`
 *
 * @property {{id: number, album_id: string, user_id: number, username: string, title: string}[]} shared
 * @property {{id: string, title: string}[]}                                                      albums
 * @property {{id: number, username: string}[]}                                                   users
 */

/**
 * @typedef SearchResult
 *
 * DTO returned by `Search::run`
 *
 * @property {Album[]}    albums
 * @property {TagAlbum[]} tag_albums
 * @property {Photo[]}    photos
 * @property {string}     checksum - checksum of the search result to
 *                                   efficiently determine if the result has
 *                                   changed since the last time
 */

/**
 * @typedef Albums
 *
 * @property {SmartAlbums} smart_albums
 * @property {TagAlbum[]}  tag_albums
 * @property {Album[]}     albums
 * @property {Album[]}     shared_albums
 */

/**
 * @typedef SmartAlbums
 *
 * @property {?SmartAlbum} unsorted
 * @property {?SmartAlbum} starred
 * @property {?SmartAlbum} public
 * @property {?SmartAlbum} recent
 */

/**
 * The IDs of the built-in, smart albums.
 *
 * @type {Readonly<{RECENT: string, STARRED: string, PUBLIC: string, UNSORTED: string}>}
 */
var SmartAlbumID = Object.freeze({
	UNSORTED: "unsorted",
	STARRED: "starred",
	PUBLIC: "public",
	RECENT: "recent"
});

/**
 * @typedef User
 *
 * @property {number}  id
 * @property {string}  username
 * @property {?string} email
 * @property {boolean} may_upload
 * @property {boolean} is_locked
 */

/**
 * @typedef WebAuthnCredential
 *
 * @property {string} id
 */

/**
 * @typedef PositionData
 *
 * @property {?string} id - album ID
 * @property {?string} title - album title
 * @property {Photo[]} photos
 * @property {?string} track_url - URL to GPX track
 */

/**
 * @typedef EMailData
 *
 * @property {?string} email
 */

/**
 * @typedef ConfigSetting
 *
 * @property {number} id
 * @property {string} key
 * @property {?string} value - TODO: this should have the correct type depending on `type_range`
 * @property {string} cat
 * @property {string} type_range
 * @property {number} confidentiality - `0`: public setting, `2`: informational, `3`: admin only
 * @property {string} description
 */

/**
 * @typedef LogEntry
 *
 * @property {number} id
 * @property {string} created_at
 * @property {string} updated_at
 * @property {string} type
 * @property {string} function
 * @property {number} line
 * @property {string} text
 */

/**
 * @typedef DiagnosticInfo
 *
 * @property {string[]} errors
 * @property {string[]} infos
 * @property {string[]} configs
 * @property {number} update - `0`: not on master branch; `1`: up-to-date; `2`: not up-to-date; `3`: requires migration
 */

/**
 * @typedef FrameSettings
 *
 * @property {number} refresh
 */

/**
 * @typedef InitializationData
 *
 * @property {number} status - `1`: unauthenticated, `2`: authenticated
 * @property {boolean} admin
 * @property {boolean} may_upload
 * @property {boolean} is_locked
 * @property {number} update_json - version number of latest available update
 * @property {boolean} update_available
 * @property {Object.<string, string>} locale
 * @property {string} [username] - only if user is not the admin; TODO: Change that
 * @property {ConfigurationData} config
 * @property {DeviceConfiguration} config_device
 */

/**
 * @typedef ConfigurationData
 *
 * @property {string}   album_subtitle_type
 * @property {string}   check_for_updates       - actually a boolean
 * @property {string}   [default_license]
 * @property {string}   [delete_imported]       - actually a boolean
 * @property {string}   downloadable            - actually a boolean
 * @property {string}   [dropbox_key]
 * @property {string}   editor_enabled          - actually a boolean
 * @property {string}   full_photo              - actually a boolean
 * @property {string}   image_overlay_type
 * @property {string}   landing_page_enable     - actually a boolean
 * @property {string}   lang
 * @property {string[]} lang_available
 * @property {string}   layout                  - actually a number: `0`, `1` or `2`
 * @property {string}   [location]
 * @property {string}   location_decoding       - actually a boolean
 * @property {string}   location_show           - actually a boolean
 * @property {string}   location_show_public    - actually a boolean
 * @property {string}   map_display             - actually a boolean
 * @property {string}   map_display_direction   - actually a boolean
 * @property {string}   map_display_public      - actually a boolean
 * @property {string}   map_include_subalbums   - actually a boolean
 * @property {string}   map_provider
 * @property {string}   new_photos_notification - actually a boolean
 * @property {string}   nsfw_blur               - actually a boolean
 * @property {string}   nsfw_visible            - actually a boolean
 * @property {string}   nsfw_warning            - actually a boolean
 * @property {string}   nsfw_warning_admin      - actually a boolean
 * @property {string}   public_photos_hidden    - actually a boolean
 * @property {string}   public_search           - actually a boolean
 * @property {string}   share_button_visible    - actually a boolean
 * @property {string}   [skip_duplicates]       - actually a boolean
 * @property {SortingCriterion} sorting_albums
 * @property {SortingCriterion} sorting_photos
 * @property {string}   swipe_tolerance_x       - actually a number
 * @property {string}   swipe_tolerance_y       - actually a number
 * @property {string}   upload_processing_limit - actually a number
 * @property {string}   version                 - actually a number
 */

/**
 * @typedef DeviceConfiguration
 *
 * @property {string}  device_type
 * @property {boolean} header_auto_hide
 * @property {boolean} active_focus_on_page_load
 * @property {boolean} enable_button_visibility
 * @property {boolean} enable_button_share
 * @property {boolean} enable_button_archive
 * @property {boolean} enable_button_move
 * @property {boolean} enable_button_trash
 * @property {boolean} enable_button_fullscreen
 * @property {boolean} enable_button_download
 * @property {boolean} enable_button_add
 * @property {boolean} enable_button_more
 * @property {boolean} enable_button_rotate
 * @property {boolean} enable_close_tab_on_esc
 * @property {boolean} enable_contextmenu_header
 * @property {boolean} hide_content_during_imgview
 * @property {boolean} enable_tabindex
 */

/**
 * The JSON object for incremental reports sent by the
 * back-end within a streamed response.
 *
 * @typedef ImportReport
 *
 * @property {string} type - indicates the type of report;
 *                           `'progress'`: {@link ImportProgressReport},
 *                           `'event'`: {@link ImportEventReport}
 */

/**
 * The JSON object for cumulative progress reports sent by the
 * back-end within a streamed response.
 *
 * @typedef ImportProgressReport
 *
 * @property {string} type - `'progress'`
 * @property {string} path
 * @property {number} progress
 */

/**
 * The JSON object for events sent by the back-end within a streamed response.
 *
 * @typedef ImportEventReport
 *
 * @property {string} type - `'event'`
 * @property {string} subtype - the subtype of event; equals the base name of the exception class which caused this event on the back-end
 * @property {number} severity - either `'debug'`, `'info'`, `'notice'`, `'warning'`, `'error'`, `'critical'` or `'emergency'`
 * @property {?string} path - the path to the affected file or directory
 * @property {string} message - a message text
 */

(function (window, factory) {
	var basicContext = factory(window, window.document);
	window.basicContext = basicContext;
	if ((typeof module === "undefined" ? "undefined" : _typeof(module)) == "object" && module.exports) {
		module.exports = basicContext;
	}
})(window, function l(window, document) {
	var ITEM = "item",
	    SEPARATOR = "separator";

	var dom = function dom() {
		var elem = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

		return document.querySelector(".basicContext " + elem);
	};

	var valid = function valid() {
		var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		var emptyItem = Object.keys(item).length === 0 ? true : false;

		if (emptyItem === true) item.type = SEPARATOR;
		if (item.type == null) item.type = ITEM;
		if (item.class == null) item.class = "";
		if (item.visible !== false) item.visible = true;
		if (item.icon == null) item.icon = null;
		if (item.title == null) item.title = "Undefined";

		// Add disabled class when item disabled
		if (item.disabled !== true) item.disabled = false;
		if (item.disabled === true) item.class += " basicContext__item--disabled";

		// Item requires a function when
		// it's not a separator and not disabled
		if (item.fn == null && item.type !== SEPARATOR && item.disabled === false) {
			console.warn("Missing fn for item '" + item.title + "'");
			return false;
		}

		return true;
	};

	var buildItem = function buildItem(item, num) {
		var html = "",
		    span = "";

		// Parse and validate item
		if (valid(item) === false) return "";

		// Skip when invisible
		if (item.visible === false) return "";

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
		var html = "";

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

		if (e.type === "touchend" && (pos.x == null || pos.y == null)) {
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

		var container = document.querySelector(".basicContextContainer");

		// Get size of browser
		var browserSize = {
			width: container.offsetWidth,
			height: container.offsetHeight
		};

		// Get size of context
		var contextSize = {
			width: context.offsetWidth,
			height: context.offsetHeight
		};

		// Fix position based on context and browser size
		if (x + contextSize.width > browserSize.width) x = x - (x + contextSize.width - browserSize.width);
		if (y + contextSize.height > browserSize.height) y = y - (y + contextSize.height - browserSize.height);

		// Make context scrollable and start at the top of the browser
		// when context is higher than the browser
		if (contextSize.height > browserSize.height) {
			y = 0;
			context.classList.add("basicContext--scrollable");
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
		document.body.insertAdjacentHTML("beforeend", html);

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
		if (typeof e.preventDefault === "function") e.preventDefault();
		if (typeof e.stopPropagation === "function") e.stopPropagation();

		// Call callback when a function
		if (typeof fnCallback === "function") fnCallback();

		return true;
	};

	var visible = function visible() {
		var elem = dom();

		return !(elem == null || elem.length === 0);
	};

	var close = function close() {
		if (visible() === false) return false;

		var container = document.querySelector(".basicContextContainer");

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