/*! jQuery v3.6.1 | (c) OpenJS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(C,e){"use strict";var t=[],r=Object.getPrototypeOf,s=t.slice,g=t.flat?function(e){return t.flat.call(e)}:function(e){return t.concat.apply([],e)},u=t.push,i=t.indexOf,n={},o=n.toString,y=n.hasOwnProperty,a=y.toString,l=a.call(Object),v={},m=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType&&"function"!=typeof e.item},x=function(e){return null!=e&&e===e.window},E=C.document,c={type:!0,src:!0,nonce:!0,noModule:!0};function b(e,t,n){var r,i,o=(n=n||E).createElement("script");if(o.text=e,t)for(r in c)(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function w(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?n[o.call(e)]||"object":typeof e}var f="3.6.1",S=function(e,t){return new S.fn.init(e,t)};function p(e){var t=!!e&&"length"in e&&e.length,n=w(e);return!m(e)&&!x(e)&&("array"===n||0===t||"number"==typeof t&&0<t&&t-1 in e)}S.fn=S.prototype={jquery:f,constructor:S,length:0,toArray:function(){return s.call(this)},get:function(e){return null==e?s.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=S.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return S.each(this,e)},map:function(n){return this.pushStack(S.map(this,function(e,t){return n.call(e,t,e)}))},slice:function(){return this.pushStack(s.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(S.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(S.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(0<=n&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:u,sort:t.sort,splice:t.splice},S.extend=S.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||m(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(S.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||S.isPlainObject(n)?n:{},i=!1,a[t]=S.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},S.extend({expando:"jQuery"+(f+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==o.call(e))&&(!(t=r(e))||"function"==typeof(n=y.call(t,"constructor")&&t.constructor)&&a.call(n)===l)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t,n){b(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(p(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},makeArray:function(e,t){var n=t||[];return null!=e&&(p(Object(e))?S.merge(n,"string"==typeof e?[e]:e):u.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:i.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},map:function(e,t,n){var r,i,o=0,a=[];if(p(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&a.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&a.push(i);return g(a)},guid:1,support:v}),"function"==typeof Symbol&&(S.fn[Symbol.iterator]=t[Symbol.iterator]),S.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){n["[object "+t+"]"]=t.toLowerCase()});var d=function(n){var e,d,b,o,i,h,f,g,w,u,l,T,C,a,E,y,s,c,v,S="sizzle"+1*new Date,p=n.document,k=0,r=0,m=ue(),x=ue(),A=ue(),N=ue(),j=function(e,t){return e===t&&(l=!0),0},D={}.hasOwnProperty,t=[],q=t.pop,L=t.push,H=t.push,O=t.slice,P=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},R="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",I="(?:\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",W="\\["+M+"*("+I+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+I+"))|)"+M+"*\\]",F=":("+I+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+W+")*)|.*)\\)|)",$=new RegExp(M+"+","g"),B=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),_=new RegExp("^"+M+"*,"+M+"*"),z=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),U=new RegExp(M+"|>"),X=new RegExp(F),V=new RegExp("^"+I+"$"),G={ID:new RegExp("^#("+I+")"),CLASS:new RegExp("^\\.("+I+")"),TAG:new RegExp("^("+I+"|[*])"),ATTR:new RegExp("^"+W),PSEUDO:new RegExp("^"+F),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+R+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},Y=/HTML$/i,Q=/^(?:input|select|textarea|button)$/i,J=/^h\d$/i,K=/^[^{]+\{\s*\[native \w/,Z=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,ee=/[+~]/,te=new RegExp("\\\\[\\da-fA-F]{1,6}"+M+"?|\\\\([^\\r\\n\\f])","g"),ne=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},re=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ie=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},oe=function(){T()},ae=be(function(e){return!0===e.disabled&&"fieldset"===e.nodeName.toLowerCase()},{dir:"parentNode",next:"legend"});try{H.apply(t=O.call(p.childNodes),p.childNodes),t[p.childNodes.length].nodeType}catch(e){H={apply:t.length?function(e,t){L.apply(e,O.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function se(t,e,n,r){var i,o,a,s,u,l,c,f=e&&e.ownerDocument,p=e?e.nodeType:9;if(n=n||[],"string"!=typeof t||!t||1!==p&&9!==p&&11!==p)return n;if(!r&&(T(e),e=e||C,E)){if(11!==p&&(u=Z.exec(t)))if(i=u[1]){if(9===p){if(!(a=e.getElementById(i)))return n;if(a.id===i)return n.push(a),n}else if(f&&(a=f.getElementById(i))&&v(e,a)&&a.id===i)return n.push(a),n}else{if(u[2])return H.apply(n,e.getElementsByTagName(t)),n;if((i=u[3])&&d.getElementsByClassName&&e.getElementsByClassName)return H.apply(n,e.getElementsByClassName(i)),n}if(d.qsa&&!N[t+" "]&&(!y||!y.test(t))&&(1!==p||"object"!==e.nodeName.toLowerCase())){if(c=t,f=e,1===p&&(U.test(t)||z.test(t))){(f=ee.test(t)&&ve(e.parentNode)||e)===e&&d.scope||((s=e.getAttribute("id"))?s=s.replace(re,ie):e.setAttribute("id",s=S)),o=(l=h(t)).length;while(o--)l[o]=(s?"#"+s:":scope")+" "+xe(l[o]);c=l.join(",")}try{return H.apply(n,f.querySelectorAll(c)),n}catch(e){N(t,!0)}finally{s===S&&e.removeAttribute("id")}}}return g(t.replace(B,"$1"),e,n,r)}function ue(){var r=[];return function e(t,n){return r.push(t+" ")>b.cacheLength&&delete e[r.shift()],e[t+" "]=n}}function le(e){return e[S]=!0,e}function ce(e){var t=C.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function fe(e,t){var n=e.split("|"),r=n.length;while(r--)b.attrHandle[n[r]]=t}function pe(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function de(t){return function(e){return"input"===e.nodeName.toLowerCase()&&e.type===t}}function he(n){return function(e){var t=e.nodeName.toLowerCase();return("input"===t||"button"===t)&&e.type===n}}function ge(t){return function(e){return"form"in e?e.parentNode&&!1===e.disabled?"label"in e?"label"in e.parentNode?e.parentNode.disabled===t:e.disabled===t:e.isDisabled===t||e.isDisabled!==!t&&ae(e)===t:e.disabled===t:"label"in e&&e.disabled===t}}function ye(a){return le(function(o){return o=+o,le(function(e,t){var n,r=a([],e.length,o),i=r.length;while(i--)e[n=r[i]]&&(e[n]=!(t[n]=e[n]))})})}function ve(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}for(e in d=se.support={},i=se.isXML=function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;return!Y.test(t||n&&n.nodeName||"HTML")},T=se.setDocument=function(e){var t,n,r=e?e.ownerDocument||e:p;return r!=C&&9===r.nodeType&&r.documentElement&&(a=(C=r).documentElement,E=!i(C),p!=C&&(n=C.defaultView)&&n.top!==n&&(n.addEventListener?n.addEventListener("unload",oe,!1):n.attachEvent&&n.attachEvent("onunload",oe)),d.scope=ce(function(e){return a.appendChild(e).appendChild(C.createElement("div")),"undefined"!=typeof e.querySelectorAll&&!e.querySelectorAll(":scope fieldset div").length}),d.attributes=ce(function(e){return e.className="i",!e.getAttribute("className")}),d.getElementsByTagName=ce(function(e){return e.appendChild(C.createComment("")),!e.getElementsByTagName("*").length}),d.getElementsByClassName=K.test(C.getElementsByClassName),d.getById=ce(function(e){return a.appendChild(e).id=S,!C.getElementsByName||!C.getElementsByName(S).length}),d.getById?(b.filter.ID=function(e){var t=e.replace(te,ne);return function(e){return e.getAttribute("id")===t}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n=t.getElementById(e);return n?[n]:[]}}):(b.filter.ID=function(e){var n=e.replace(te,ne);return function(e){var t="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return t&&t.value===n}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),b.find.TAG=d.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):d.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},b.find.CLASS=d.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&E)return t.getElementsByClassName(e)},s=[],y=[],(d.qsa=K.test(C.querySelectorAll))&&(ce(function(e){var t;a.appendChild(e).innerHTML="<a id='"+S+"'></a><select id='"+S+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&y.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||y.push("\\["+M+"*(?:value|"+R+")"),e.querySelectorAll("[id~="+S+"-]").length||y.push("~="),(t=C.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||y.push("\\["+M+"*name"+M+"*="+M+"*(?:''|\"\")"),e.querySelectorAll(":checked").length||y.push(":checked"),e.querySelectorAll("a#"+S+"+*").length||y.push(".#.+[+~]"),e.querySelectorAll("\\\f"),y.push("[\\r\\n\\f]")}),ce(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=C.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&y.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&y.push(":enabled",":disabled"),a.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&y.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),y.push(",.*:")})),(d.matchesSelector=K.test(c=a.matches||a.webkitMatchesSelector||a.mozMatchesSelector||a.oMatchesSelector||a.msMatchesSelector))&&ce(function(e){d.disconnectedMatch=c.call(e,"*"),c.call(e,"[s!='']:x"),s.push("!=",F)}),y=y.length&&new RegExp(y.join("|")),s=s.length&&new RegExp(s.join("|")),t=K.test(a.compareDocumentPosition),v=t||K.test(a.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},j=t?function(e,t){if(e===t)return l=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!d.sortDetached&&t.compareDocumentPosition(e)===n?e==C||e.ownerDocument==p&&v(p,e)?-1:t==C||t.ownerDocument==p&&v(p,t)?1:u?P(u,e)-P(u,t):0:4&n?-1:1)}:function(e,t){if(e===t)return l=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e==C?-1:t==C?1:i?-1:o?1:u?P(u,e)-P(u,t):0;if(i===o)return pe(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?pe(a[r],s[r]):a[r]==p?-1:s[r]==p?1:0}),C},se.matches=function(e,t){return se(e,null,null,t)},se.matchesSelector=function(e,t){if(T(e),d.matchesSelector&&E&&!N[t+" "]&&(!s||!s.test(t))&&(!y||!y.test(t)))try{var n=c.call(e,t);if(n||d.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){N(t,!0)}return 0<se(t,C,null,[e]).length},se.contains=function(e,t){return(e.ownerDocument||e)!=C&&T(e),v(e,t)},se.attr=function(e,t){(e.ownerDocument||e)!=C&&T(e);var n=b.attrHandle[t.toLowerCase()],r=n&&D.call(b.attrHandle,t.toLowerCase())?n(e,t,!E):void 0;return void 0!==r?r:d.attributes||!E?e.getAttribute(t):(r=e.getAttributeNode(t))&&r.specified?r.value:null},se.escape=function(e){return(e+"").replace(re,ie)},se.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},se.uniqueSort=function(e){var t,n=[],r=0,i=0;if(l=!d.detectDuplicates,u=!d.sortStable&&e.slice(0),e.sort(j),l){while(t=e[i++])t===e[i]&&(r=n.push(i));while(r--)e.splice(n[r],1)}return u=null,e},o=se.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=o(e)}else if(3===i||4===i)return e.nodeValue}else while(t=e[r++])n+=o(t);return n},(b=se.selectors={cacheLength:50,createPseudo:le,match:G,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(te,ne),e[3]=(e[3]||e[4]||e[5]||"").replace(te,ne),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||se.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&se.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return G.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=h(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(te,ne).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=m[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&m(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(n,r,i){return function(e){var t=se.attr(e,n);return null==t?"!="===r:!r||(t+="","="===r?t===i:"!="===r?t!==i:"^="===r?i&&0===t.indexOf(i):"*="===r?i&&-1<t.indexOf(i):"$="===r?i&&t.slice(-i.length)===i:"~="===r?-1<(" "+t.replace($," ")+" ").indexOf(i):"|="===r&&(t===i||t.slice(0,i.length+1)===i+"-"))}},CHILD:function(h,e,t,g,y){var v="nth"!==h.slice(0,3),m="last"!==h.slice(-4),x="of-type"===e;return 1===g&&0===y?function(e){return!!e.parentNode}:function(e,t,n){var r,i,o,a,s,u,l=v!==m?"nextSibling":"previousSibling",c=e.parentNode,f=x&&e.nodeName.toLowerCase(),p=!n&&!x,d=!1;if(c){if(v){while(l){a=e;while(a=a[l])if(x?a.nodeName.toLowerCase()===f:1===a.nodeType)return!1;u=l="only"===h&&!u&&"nextSibling"}return!0}if(u=[m?c.firstChild:c.lastChild],m&&p){d=(s=(r=(i=(o=(a=c)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1])&&r[2],a=s&&c.childNodes[s];while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if(1===a.nodeType&&++d&&a===e){i[h]=[k,s,d];break}}else if(p&&(d=s=(r=(i=(o=(a=e)[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===k&&r[1]),!1===d)while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if((x?a.nodeName.toLowerCase()===f:1===a.nodeType)&&++d&&(p&&((i=(o=a[S]||(a[S]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]=[k,d]),a===e))break;return(d-=y)===g||d%g==0&&0<=d/g}}},PSEUDO:function(e,o){var t,a=b.pseudos[e]||b.setFilters[e.toLowerCase()]||se.error("unsupported pseudo: "+e);return a[S]?a(o):1<a.length?(t=[e,e,"",o],b.setFilters.hasOwnProperty(e.toLowerCase())?le(function(e,t){var n,r=a(e,o),i=r.length;while(i--)e[n=P(e,r[i])]=!(t[n]=r[i])}):function(e){return a(e,0,t)}):a}},pseudos:{not:le(function(e){var r=[],i=[],s=f(e.replace(B,"$1"));return s[S]?le(function(e,t,n,r){var i,o=s(e,null,r,[]),a=e.length;while(a--)(i=o[a])&&(e[a]=!(t[a]=i))}):function(e,t,n){return r[0]=e,s(r,null,n,i),r[0]=null,!i.pop()}}),has:le(function(t){return function(e){return 0<se(t,e).length}}),contains:le(function(t){return t=t.replace(te,ne),function(e){return-1<(e.textContent||o(e)).indexOf(t)}}),lang:le(function(n){return V.test(n||"")||se.error("unsupported lang: "+n),n=n.replace(te,ne).toLowerCase(),function(e){var t;do{if(t=E?e.lang:e.getAttribute("xml:lang")||e.getAttribute("lang"))return(t=t.toLowerCase())===n||0===t.indexOf(n+"-")}while((e=e.parentNode)&&1===e.nodeType);return!1}}),target:function(e){var t=n.location&&n.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===a},focus:function(e){return e===C.activeElement&&(!C.hasFocus||C.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:ge(!1),disabled:ge(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!b.pseudos.empty(e)},header:function(e){return J.test(e.nodeName)},input:function(e){return Q.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:ye(function(){return[0]}),last:ye(function(e,t){return[t-1]}),eq:ye(function(e,t,n){return[n<0?n+t:n]}),even:ye(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:ye(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:ye(function(e,t,n){for(var r=n<0?n+t:t<n?t:n;0<=--r;)e.push(r);return e}),gt:ye(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=b.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})b.pseudos[e]=de(e);for(e in{submit:!0,reset:!0})b.pseudos[e]=he(e);function me(){}function xe(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function be(s,e,t){var u=e.dir,l=e.next,c=l||u,f=t&&"parentNode"===c,p=r++;return e.first?function(e,t,n){while(e=e[u])if(1===e.nodeType||f)return s(e,t,n);return!1}:function(e,t,n){var r,i,o,a=[k,p];if(n){while(e=e[u])if((1===e.nodeType||f)&&s(e,t,n))return!0}else while(e=e[u])if(1===e.nodeType||f)if(i=(o=e[S]||(e[S]={}))[e.uniqueID]||(o[e.uniqueID]={}),l&&l===e.nodeName.toLowerCase())e=e[u]||e;else{if((r=i[c])&&r[0]===k&&r[1]===p)return a[2]=r[2];if((i[c]=a)[2]=s(e,t,n))return!0}return!1}}function we(i){return 1<i.length?function(e,t,n){var r=i.length;while(r--)if(!i[r](e,t,n))return!1;return!0}:i[0]}function Te(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Ce(d,h,g,y,v,e){return y&&!y[S]&&(y=Ce(y)),v&&!v[S]&&(v=Ce(v,e)),le(function(e,t,n,r){var i,o,a,s=[],u=[],l=t.length,c=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)se(e,t[r],n);return n}(h||"*",n.nodeType?[n]:n,[]),f=!d||!e&&h?c:Te(c,s,d,n,r),p=g?v||(e?d:l||y)?[]:t:f;if(g&&g(f,p,n,r),y){i=Te(p,u),y(i,[],n,r),o=i.length;while(o--)(a=i[o])&&(p[u[o]]=!(f[u[o]]=a))}if(e){if(v||d){if(v){i=[],o=p.length;while(o--)(a=p[o])&&i.push(f[o]=a);v(null,p=[],i,r)}o=p.length;while(o--)(a=p[o])&&-1<(i=v?P(e,a):s[o])&&(e[i]=!(t[i]=a))}}else p=Te(p===t?p.splice(l,p.length):p),v?v(null,t,p,r):H.apply(t,p)})}function Ee(e){for(var i,t,n,r=e.length,o=b.relative[e[0].type],a=o||b.relative[" "],s=o?1:0,u=be(function(e){return e===i},a,!0),l=be(function(e){return-1<P(i,e)},a,!0),c=[function(e,t,n){var r=!o&&(n||t!==w)||((i=t).nodeType?u(e,t,n):l(e,t,n));return i=null,r}];s<r;s++)if(t=b.relative[e[s].type])c=[be(we(c),t)];else{if((t=b.filter[e[s].type].apply(null,e[s].matches))[S]){for(n=++s;n<r;n++)if(b.relative[e[n].type])break;return Ce(1<s&&we(c),1<s&&xe(e.slice(0,s-1).concat({value:" "===e[s-2].type?"*":""})).replace(B,"$1"),t,s<n&&Ee(e.slice(s,n)),n<r&&Ee(e=e.slice(n)),n<r&&xe(e))}c.push(t)}return we(c)}return me.prototype=b.filters=b.pseudos,b.setFilters=new me,h=se.tokenize=function(e,t){var n,r,i,o,a,s,u,l=x[e+" "];if(l)return t?0:l.slice(0);a=e,s=[],u=b.preFilter;while(a){for(o in n&&!(r=_.exec(a))||(r&&(a=a.slice(r[0].length)||a),s.push(i=[])),n=!1,(r=z.exec(a))&&(n=r.shift(),i.push({value:n,type:r[0].replace(B," ")}),a=a.slice(n.length)),b.filter)!(r=G[o].exec(a))||u[o]&&!(r=u[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?se.error(e):x(e,s).slice(0)},f=se.compile=function(e,t){var n,y,v,m,x,r,i=[],o=[],a=A[e+" "];if(!a){t||(t=h(e)),n=t.length;while(n--)(a=Ee(t[n]))[S]?i.push(a):o.push(a);(a=A(e,(y=o,m=0<(v=i).length,x=0<y.length,r=function(e,t,n,r,i){var o,a,s,u=0,l="0",c=e&&[],f=[],p=w,d=e||x&&b.find.TAG("*",i),h=k+=null==p?1:Math.random()||.1,g=d.length;for(i&&(w=t==C||t||i);l!==g&&null!=(o=d[l]);l++){if(x&&o){a=0,t||o.ownerDocument==C||(T(o),n=!E);while(s=y[a++])if(s(o,t||C,n)){r.push(o);break}i&&(k=h)}m&&((o=!s&&o)&&u--,e&&c.push(o))}if(u+=l,m&&l!==u){a=0;while(s=v[a++])s(c,f,t,n);if(e){if(0<u)while(l--)c[l]||f[l]||(f[l]=q.call(r));f=Te(f)}H.apply(r,f),i&&!e&&0<f.length&&1<u+v.length&&se.uniqueSort(r)}return i&&(k=h,w=p),c},m?le(r):r))).selector=e}return a},g=se.select=function(e,t,n,r){var i,o,a,s,u,l="function"==typeof e&&e,c=!r&&h(e=l.selector||e);if(n=n||[],1===c.length){if(2<(o=c[0]=c[0].slice(0)).length&&"ID"===(a=o[0]).type&&9===t.nodeType&&E&&b.relative[o[1].type]){if(!(t=(b.find.ID(a.matches[0].replace(te,ne),t)||[])[0]))return n;l&&(t=t.parentNode),e=e.slice(o.shift().value.length)}i=G.needsContext.test(e)?0:o.length;while(i--){if(a=o[i],b.relative[s=a.type])break;if((u=b.find[s])&&(r=u(a.matches[0].replace(te,ne),ee.test(o[0].type)&&ve(t.parentNode)||t))){if(o.splice(i,1),!(e=r.length&&xe(o)))return H.apply(n,r),n;break}}}return(l||f(e,c))(r,t,!E,n,!t||ee.test(e)&&ve(t.parentNode)||t),n},d.sortStable=S.split("").sort(j).join("")===S,d.detectDuplicates=!!l,T(),d.sortDetached=ce(function(e){return 1&e.compareDocumentPosition(C.createElement("fieldset"))}),ce(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||fe("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),d.attributes&&ce(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||fe("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ce(function(e){return null==e.getAttribute("disabled")})||fe(R,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),se}(C);S.find=d,S.expr=d.selectors,S.expr[":"]=S.expr.pseudos,S.uniqueSort=S.unique=d.uniqueSort,S.text=d.getText,S.isXMLDoc=d.isXML,S.contains=d.contains,S.escapeSelector=d.escape;var h=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&S(e).is(n))break;r.push(e)}return r},T=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},k=S.expr.match.needsContext;function A(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var N=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function j(e,n,r){return m(n)?S.grep(e,function(e,t){return!!n.call(e,t,e)!==r}):n.nodeType?S.grep(e,function(e){return e===n!==r}):"string"!=typeof n?S.grep(e,function(e){return-1<i.call(n,e)!==r}):S.filter(n,e,r)}S.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?S.find.matchesSelector(r,e)?[r]:[]:S.find.matches(e,S.grep(t,function(e){return 1===e.nodeType}))},S.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(S(e).filter(function(){for(t=0;t<r;t++)if(S.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)S.find(e,i[t],n);return 1<r?S.uniqueSort(n):n},filter:function(e){return this.pushStack(j(this,e||[],!1))},not:function(e){return this.pushStack(j(this,e||[],!0))},is:function(e){return!!j(this,"string"==typeof e&&k.test(e)?S(e):e||[],!1).length}});var D,q=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(S.fn.init=function(e,t,n){var r,i;if(!e)return this;if(n=n||D,"string"==typeof e){if(!(r="<"===e[0]&&">"===e[e.length-1]&&3<=e.length?[null,e,null]:q.exec(e))||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof S?t[0]:t,S.merge(this,S.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:E,!0)),N.test(r[1])&&S.isPlainObject(t))for(r in t)m(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return(i=E.getElementById(r[2]))&&(this[0]=i,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):m(e)?void 0!==n.ready?n.ready(e):e(S):S.makeArray(e,this)}).prototype=S.fn,D=S(E);var L=/^(?:parents|prev(?:Until|All))/,H={children:!0,contents:!0,next:!0,prev:!0};function O(e,t){while((e=e[t])&&1!==e.nodeType);return e}S.fn.extend({has:function(e){var t=S(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(S.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&S(e);if(!k.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?-1<a.index(n):1===n.nodeType&&S.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(1<o.length?S.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?i.call(S(e),this[0]):i.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(S.uniqueSort(S.merge(this.get(),S(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),S.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return h(e,"parentNode")},parentsUntil:function(e,t,n){return h(e,"parentNode",n)},next:function(e){return O(e,"nextSibling")},prev:function(e){return O(e,"previousSibling")},nextAll:function(e){return h(e,"nextSibling")},prevAll:function(e){return h(e,"previousSibling")},nextUntil:function(e,t,n){return h(e,"nextSibling",n)},prevUntil:function(e,t,n){return h(e,"previousSibling",n)},siblings:function(e){return T((e.parentNode||{}).firstChild,e)},children:function(e){return T(e.firstChild)},contents:function(e){return null!=e.contentDocument&&r(e.contentDocument)?e.contentDocument:(A(e,"template")&&(e=e.content||e),S.merge([],e.childNodes))}},function(r,i){S.fn[r]=function(e,t){var n=S.map(this,i,e);return"Until"!==r.slice(-5)&&(t=e),t&&"string"==typeof t&&(n=S.filter(t,n)),1<this.length&&(H[r]||S.uniqueSort(n),L.test(r)&&n.reverse()),this.pushStack(n)}});var P=/[^\x20\t\r\n\f]+/g;function R(e){return e}function M(e){throw e}function I(e,t,n,r){var i;try{e&&m(i=e.promise)?i.call(e).done(t).fail(n):e&&m(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}S.Callbacks=function(r){var e,n;r="string"==typeof r?(e=r,n={},S.each(e.match(P)||[],function(e,t){n[t]=!0}),n):S.extend({},r);var i,t,o,a,s=[],u=[],l=-1,c=function(){for(a=a||r.once,o=i=!0;u.length;l=-1){t=u.shift();while(++l<s.length)!1===s[l].apply(t[0],t[1])&&r.stopOnFalse&&(l=s.length,t=!1)}r.memory||(t=!1),i=!1,a&&(s=t?[]:"")},f={add:function(){return s&&(t&&!i&&(l=s.length-1,u.push(t)),function n(e){S.each(e,function(e,t){m(t)?r.unique&&f.has(t)||s.push(t):t&&t.length&&"string"!==w(t)&&n(t)})}(arguments),t&&!i&&c()),this},remove:function(){return S.each(arguments,function(e,t){var n;while(-1<(n=S.inArray(t,s,n)))s.splice(n,1),n<=l&&l--}),this},has:function(e){return e?-1<S.inArray(e,s):0<s.length},empty:function(){return s&&(s=[]),this},disable:function(){return a=u=[],s=t="",this},disabled:function(){return!s},lock:function(){return a=u=[],t||i||(s=t=""),this},locked:function(){return!!a},fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),i||c()),this},fire:function(){return f.fireWith(this,arguments),this},fired:function(){return!!o}};return f},S.extend({Deferred:function(e){var o=[["notify","progress",S.Callbacks("memory"),S.Callbacks("memory"),2],["resolve","done",S.Callbacks("once memory"),S.Callbacks("once memory"),0,"resolved"],["reject","fail",S.Callbacks("once memory"),S.Callbacks("once memory"),1,"rejected"]],i="pending",a={state:function(){return i},always:function(){return s.done(arguments).fail(arguments),this},"catch":function(e){return a.then(null,e)},pipe:function(){var i=arguments;return S.Deferred(function(r){S.each(o,function(e,t){var n=m(i[t[4]])&&i[t[4]];s[t[1]](function(){var e=n&&n.apply(this,arguments);e&&m(e.promise)?e.promise().progress(r.notify).done(r.resolve).fail(r.reject):r[t[0]+"With"](this,n?[e]:arguments)})}),i=null}).promise()},then:function(t,n,r){var u=0;function l(i,o,a,s){return function(){var n=this,r=arguments,e=function(){var e,t;if(!(i<u)){if((e=a.apply(n,r))===o.promise())throw new TypeError("Thenable self-resolution");t=e&&("object"==typeof e||"function"==typeof e)&&e.then,m(t)?s?t.call(e,l(u,o,R,s),l(u,o,M,s)):(u++,t.call(e,l(u,o,R,s),l(u,o,M,s),l(u,o,R,o.notifyWith))):(a!==R&&(n=void 0,r=[e]),(s||o.resolveWith)(n,r))}},t=s?e:function(){try{e()}catch(e){S.Deferred.exceptionHook&&S.Deferred.exceptionHook(e,t.stackTrace),u<=i+1&&(a!==M&&(n=void 0,r=[e]),o.rejectWith(n,r))}};i?t():(S.Deferred.getStackHook&&(t.stackTrace=S.Deferred.getStackHook()),C.setTimeout(t))}}return S.Deferred(function(e){o[0][3].add(l(0,e,m(r)?r:R,e.notifyWith)),o[1][3].add(l(0,e,m(t)?t:R)),o[2][3].add(l(0,e,m(n)?n:M))}).promise()},promise:function(e){return null!=e?S.extend(e,a):a}},s={};return S.each(o,function(e,t){var n=t[2],r=t[5];a[t[1]]=n.add,r&&n.add(function(){i=r},o[3-e][2].disable,o[3-e][3].disable,o[0][2].lock,o[0][3].lock),n.add(t[3].fire),s[t[0]]=function(){return s[t[0]+"With"](this===s?void 0:this,arguments),this},s[t[0]+"With"]=n.fireWith}),a.promise(s),e&&e.call(s,s),s},when:function(e){var n=arguments.length,t=n,r=Array(t),i=s.call(arguments),o=S.Deferred(),a=function(t){return function(e){r[t]=this,i[t]=1<arguments.length?s.call(arguments):e,--n||o.resolveWith(r,i)}};if(n<=1&&(I(e,o.done(a(t)).resolve,o.reject,!n),"pending"===o.state()||m(i[t]&&i[t].then)))return o.then();while(t--)I(i[t],a(t),o.reject);return o.promise()}});var W=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;S.Deferred.exceptionHook=function(e,t){C.console&&C.console.warn&&e&&W.test(e.name)&&C.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},S.readyException=function(e){C.setTimeout(function(){throw e})};var F=S.Deferred();function $(){E.removeEventListener("DOMContentLoaded",$),C.removeEventListener("load",$),S.ready()}S.fn.ready=function(e){return F.then(e)["catch"](function(e){S.readyException(e)}),this},S.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--S.readyWait:S.isReady)||(S.isReady=!0)!==e&&0<--S.readyWait||F.resolveWith(E,[S])}}),S.ready.then=F.then,"complete"===E.readyState||"loading"!==E.readyState&&!E.documentElement.doScroll?C.setTimeout(S.ready):(E.addEventListener("DOMContentLoaded",$),C.addEventListener("load",$));var B=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===w(n))for(s in i=!0,n)B(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,m(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(S(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},_=/^-ms-/,z=/-([a-z])/g;function U(e,t){return t.toUpperCase()}function X(e){return e.replace(_,"ms-").replace(z,U)}var V=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function G(){this.expando=S.expando+G.uid++}G.uid=1,G.prototype={cache:function(e){var t=e[this.expando];return t||(t={},V(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[X(t)]=n;else for(r in t)i[X(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][X(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(X):(t=X(t))in r?[t]:t.match(P)||[]).length;while(n--)delete r[t[n]]}(void 0===t||S.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!S.isEmptyObject(t)}};var Y=new G,Q=new G,J=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,K=/[A-Z]/g;function Z(e,t,n){var r,i;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(K,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n="true"===(i=n)||"false"!==i&&("null"===i?null:i===+i+""?+i:J.test(i)?JSON.parse(i):i)}catch(e){}Q.set(e,t,n)}else n=void 0;return n}S.extend({hasData:function(e){return Q.hasData(e)||Y.hasData(e)},data:function(e,t,n){return Q.access(e,t,n)},removeData:function(e,t){Q.remove(e,t)},_data:function(e,t,n){return Y.access(e,t,n)},_removeData:function(e,t){Y.remove(e,t)}}),S.fn.extend({data:function(n,e){var t,r,i,o=this[0],a=o&&o.attributes;if(void 0===n){if(this.length&&(i=Q.get(o),1===o.nodeType&&!Y.get(o,"hasDataAttrs"))){t=a.length;while(t--)a[t]&&0===(r=a[t].name).indexOf("data-")&&(r=X(r.slice(5)),Z(o,r,i[r]));Y.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof n?this.each(function(){Q.set(this,n)}):B(this,function(e){var t;if(o&&void 0===e)return void 0!==(t=Q.get(o,n))?t:void 0!==(t=Z(o,n))?t:void 0;this.each(function(){Q.set(this,n,e)})},null,e,1<arguments.length,null,!0)},removeData:function(e){return this.each(function(){Q.remove(this,e)})}}),S.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=Y.get(e,t),n&&(!r||Array.isArray(n)?r=Y.access(e,t,S.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=S.queue(e,t),r=n.length,i=n.shift(),o=S._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,function(){S.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return Y.get(e,n)||Y.access(e,n,{empty:S.Callbacks("once memory").add(function(){Y.remove(e,[t+"queue",n])})})}}),S.fn.extend({queue:function(t,n){var e=2;return"string"!=typeof t&&(n=t,t="fx",e--),arguments.length<e?S.queue(this[0],t):void 0===n?this:this.each(function(){var e=S.queue(this,t,n);S._queueHooks(this,t),"fx"===t&&"inprogress"!==e[0]&&S.dequeue(this,t)})},dequeue:function(e){return this.each(function(){S.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=S.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=Y.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var ee=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,te=new RegExp("^(?:([+-])=|)("+ee+")([a-z%]*)$","i"),ne=["Top","Right","Bottom","Left"],re=E.documentElement,ie=function(e){return S.contains(e.ownerDocument,e)},oe={composed:!0};re.getRootNode&&(ie=function(e){return S.contains(e.ownerDocument,e)||e.getRootNode(oe)===e.ownerDocument});var ae=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&ie(e)&&"none"===S.css(e,"display")};function se(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return S.css(e,t,"")},u=s(),l=n&&n[3]||(S.cssNumber[t]?"":"px"),c=e.nodeType&&(S.cssNumber[t]||"px"!==l&&+u)&&te.exec(S.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)S.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,S.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ue={};function le(e,t){for(var n,r,i,o,a,s,u,l=[],c=0,f=e.length;c<f;c++)(r=e[c]).style&&(n=r.style.display,t?("none"===n&&(l[c]=Y.get(r,"display")||null,l[c]||(r.style.display="")),""===r.style.display&&ae(r)&&(l[c]=(u=a=o=void 0,a=(i=r).ownerDocument,s=i.nodeName,(u=ue[s])||(o=a.body.appendChild(a.createElement(s)),u=S.css(o,"display"),o.parentNode.removeChild(o),"none"===u&&(u="block"),ue[s]=u)))):"none"!==n&&(l[c]="none",Y.set(r,"display",n)));for(c=0;c<f;c++)null!=l[c]&&(e[c].style.display=l[c]);return e}S.fn.extend({show:function(){return le(this,!0)},hide:function(){return le(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ae(this)?S(this).show():S(this).hide()})}});var ce,fe,pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,he=/^$|^module$|\/(?:java|ecma)script/i;ce=E.createDocumentFragment().appendChild(E.createElement("div")),(fe=E.createElement("input")).setAttribute("type","radio"),fe.setAttribute("checked","checked"),fe.setAttribute("name","t"),ce.appendChild(fe),v.checkClone=ce.cloneNode(!0).cloneNode(!0).lastChild.checked,ce.innerHTML="<textarea>x</textarea>",v.noCloneChecked=!!ce.cloneNode(!0).lastChild.defaultValue,ce.innerHTML="<option></option>",v.option=!!ce.lastChild;var ge={thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function ye(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&A(e,t)?S.merge([e],n):n}function ve(e,t){for(var n=0,r=e.length;n<r;n++)Y.set(e[n],"globalEval",!t||Y.get(t[n],"globalEval"))}ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td,v.option||(ge.optgroup=ge.option=[1,"<select multiple='multiple'>","</select>"]);var me=/<|&#?\w+;/;function xe(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===w(o))S.merge(p,o.nodeType?[o]:o);else if(me.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+S.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;S.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&-1<S.inArray(o,r))i&&i.push(o);else if(l=ie(o),a=ye(f.appendChild(o),"script"),l&&ve(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}var be=/^([^.]*)(?:\.(.+)|)/;function we(){return!0}function Te(){return!1}function Ce(e,t){return e===function(){try{return E.activeElement}catch(e){}}()==("focus"===t)}function Ee(e,t,n,r,i,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(r=r||n,n=void 0),t)Ee(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=Te;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return S().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=S.guid++)),e.each(function(){S.event.add(this,t,i,r,n)})}function Se(e,i,o){o?(Y.set(e,i,!1),S.event.add(e,i,{namespace:!1,handler:function(e){var t,n,r=Y.get(this,i);if(1&e.isTrigger&&this[i]){if(r.length)(S.event.special[i]||{}).delegateType&&e.stopPropagation();else if(r=s.call(arguments),Y.set(this,i,r),t=o(this,i),this[i](),r!==(n=Y.get(this,i))||t?Y.set(this,i,!1):n={},r!==n)return e.stopImmediatePropagation(),e.preventDefault(),n&&n.value}else r.length&&(Y.set(this,i,{value:S.event.trigger(S.extend(r[0],S.Event.prototype),r.slice(1),this)}),e.stopImmediatePropagation())}})):void 0===Y.get(e,i)&&S.event.add(e,i,we)}S.event={global:{},add:function(t,e,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=Y.get(t);if(V(t)){n.handler&&(n=(o=n).handler,i=o.selector),i&&S.find.matchesSelector(re,i),n.guid||(n.guid=S.guid++),(u=y.events)||(u=y.events=Object.create(null)),(a=y.handle)||(a=y.handle=function(e){return"undefined"!=typeof S&&S.event.triggered!==e.type?S.event.dispatch.apply(t,arguments):void 0}),l=(e=(e||"").match(P)||[""]).length;while(l--)d=g=(s=be.exec(e[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=S.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=S.event.special[d]||{},c=S.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&S.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(t,r,h,a)||t.addEventListener&&t.addEventListener(d,a)),f.add&&(f.add.call(t,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),S.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,y=Y.hasData(e)&&Y.get(e);if(y&&(u=y.events)){l=(t=(t||"").match(P)||[""]).length;while(l--)if(d=g=(s=be.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d){f=S.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,y.handle)||S.removeEvent(e,d,y.handle),delete u[d])}else for(d in u)S.event.remove(e,d+t[l],n,r,!0);S.isEmptyObject(u)&&Y.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=new Array(arguments.length),u=S.event.fix(e),l=(Y.get(this,"events")||Object.create(null))[u.type]||[],c=S.event.special[u.type]||{};for(s[0]=u,t=1;t<arguments.length;t++)s[t]=arguments[t];if(u.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,u)){a=S.event.handlers.call(this,u,l),t=0;while((i=a[t++])&&!u.isPropagationStopped()){u.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!u.isImmediatePropagationStopped())u.rnamespace&&!1!==o.namespace&&!u.rnamespace.test(o.namespace)||(u.handleObj=o,u.data=o.data,void 0!==(r=((S.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,s))&&!1===(u.result=r)&&(u.preventDefault(),u.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,u),u.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&1<=e.button))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?-1<S(i,this).index(l):S.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(t,e){Object.defineProperty(S.Event.prototype,t,{enumerable:!0,configurable:!0,get:m(e)?function(){if(this.originalEvent)return e(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[t]},set:function(e){Object.defineProperty(this,t,{enumerable:!0,configurable:!0,writable:!0,value:e})}})},fix:function(e){return e[S.expando]?e:new S.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Se(t,"click",we),!1},trigger:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&Se(t,"click"),!0},_default:function(e){var t=e.target;return pe.test(t.type)&&t.click&&A(t,"input")&&Y.get(t,"click")||A(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},S.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},S.Event=function(e,t){if(!(this instanceof S.Event))return new S.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?we:Te,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&S.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[S.expando]=!0},S.Event.prototype={constructor:S.Event,isDefaultPrevented:Te,isPropagationStopped:Te,isImmediatePropagationStopped:Te,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=we,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=we,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=we,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},S.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},S.event.addProp),S.each({focus:"focusin",blur:"focusout"},function(t,e){S.event.special[t]={setup:function(){return Se(this,t,Ce),!1},trigger:function(){return Se(this,t),!0},_default:function(e){return Y.get(e.target,t)},delegateType:e}}),S.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,i){S.event.special[e]={delegateType:i,bindType:i,handle:function(e){var t,n=e.relatedTarget,r=e.handleObj;return n&&(n===this||S.contains(this,n))||(e.type=r.origType,t=r.handler.apply(this,arguments),e.type=i),t}}}),S.fn.extend({on:function(e,t,n,r){return Ee(this,e,t,n,r)},one:function(e,t,n,r){return Ee(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,S(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=Te),this.each(function(){S.event.remove(this,e,n,t)})}});var ke=/<script|<style|<link/i,Ae=/checked\s*(?:[^=]|=\s*.checked.)/i,Ne=/^\s*<!\[CDATA\[|\]\]>\s*$/g;function je(e,t){return A(e,"table")&&A(11!==t.nodeType?t:t.firstChild,"tr")&&S(e).children("tbody")[0]||e}function De(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function qe(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Le(e,t){var n,r,i,o,a,s;if(1===t.nodeType){if(Y.hasData(e)&&(s=Y.get(e).events))for(i in Y.remove(t,"handle events"),s)for(n=0,r=s[i].length;n<r;n++)S.event.add(t,i,s[i][n]);Q.hasData(e)&&(o=Q.access(e),a=S.extend({},o),Q.set(t,a))}}function He(n,r,i,o){r=g(r);var e,t,a,s,u,l,c=0,f=n.length,p=f-1,d=r[0],h=m(d);if(h||1<f&&"string"==typeof d&&!v.checkClone&&Ae.test(d))return n.each(function(e){var t=n.eq(e);h&&(r[0]=d.call(this,e,t.html())),He(t,r,i,o)});if(f&&(t=(e=xe(r,n[0].ownerDocument,!1,n,o)).firstChild,1===e.childNodes.length&&(e=t),t||o)){for(s=(a=S.map(ye(e,"script"),De)).length;c<f;c++)u=e,c!==p&&(u=S.clone(u,!0,!0),s&&S.merge(a,ye(u,"script"))),i.call(n[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,S.map(a,qe),c=0;c<s;c++)u=a[c],he.test(u.type||"")&&!Y.access(u,"globalEval")&&S.contains(l,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?S._evalUrl&&!u.noModule&&S._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")},l):b(u.textContent.replace(Ne,""),u,l))}return n}function Oe(e,t,n){for(var r,i=t?S.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||S.cleanData(ye(r)),r.parentNode&&(n&&ie(r)&&ve(ye(r,"script")),r.parentNode.removeChild(r));return e}S.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,o,a,s,u,l,c=e.cloneNode(!0),f=ie(e);if(!(v.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||S.isXMLDoc(e)))for(a=ye(c),r=0,i=(o=ye(e)).length;r<i;r++)s=o[r],u=a[r],void 0,"input"===(l=u.nodeName.toLowerCase())&&pe.test(s.type)?u.checked=s.checked:"input"!==l&&"textarea"!==l||(u.defaultValue=s.defaultValue);if(t)if(n)for(o=o||ye(e),a=a||ye(c),r=0,i=o.length;r<i;r++)Le(o[r],a[r]);else Le(e,c);return 0<(a=ye(c,"script")).length&&ve(a,!f&&ye(e,"script")),c},cleanData:function(e){for(var t,n,r,i=S.event.special,o=0;void 0!==(n=e[o]);o++)if(V(n)){if(t=n[Y.expando]){if(t.events)for(r in t.events)i[r]?S.event.remove(n,r):S.removeEvent(n,r,t.handle);n[Y.expando]=void 0}n[Q.expando]&&(n[Q.expando]=void 0)}}}),S.fn.extend({detach:function(e){return Oe(this,e,!0)},remove:function(e){return Oe(this,e)},text:function(e){return B(this,function(e){return void 0===e?S.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return He(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||je(this,e).appendChild(e)})},prepend:function(){return He(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=je(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return He(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return He(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(S.cleanData(ye(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return S.clone(this,e,t)})},html:function(e){return B(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!ke.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=S.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(S.cleanData(ye(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var n=[];return He(this,arguments,function(e){var t=this.parentNode;S.inArray(this,n)<0&&(S.cleanData(ye(this)),t&&t.replaceChild(e,this))},n)}}),S.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,a){S.fn[e]=function(e){for(var t,n=[],r=S(e),i=r.length-1,o=0;o<=i;o++)t=o===i?this:this.clone(!0),S(r[o])[a](t),u.apply(n,t.get());return this.pushStack(n)}});var Pe=new RegExp("^("+ee+")(?!px)[a-z%]+$","i"),Re=/^--/,Me=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=C),t.getComputedStyle(e)},Ie=function(e,t,n){var r,i,o={};for(i in t)o[i]=e.style[i],e.style[i]=t[i];for(i in r=n.call(e),t)e.style[i]=o[i];return r},We=new RegExp(ne.join("|"),"i"),Fe="[\\x20\\t\\r\\n\\f]",$e=new RegExp("^"+Fe+"+|((?:^|[^\\\\])(?:\\\\.)*)"+Fe+"+$","g");function Be(e,t,n){var r,i,o,a,s=Re.test(t),u=e.style;return(n=n||Me(e))&&(a=n.getPropertyValue(t)||n[t],s&&(a=a.replace($e,"$1")),""!==a||ie(e)||(a=S.style(e,t)),!v.pixelBoxStyles()&&Pe.test(a)&&We.test(t)&&(r=u.width,i=u.minWidth,o=u.maxWidth,u.minWidth=u.maxWidth=u.width=a,a=n.width,u.width=r,u.minWidth=i,u.maxWidth=o)),void 0!==a?a+"":a}function _e(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(l){u.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",l.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",re.appendChild(u).appendChild(l);var e=C.getComputedStyle(l);n="1%"!==e.top,s=12===t(e.marginLeft),l.style.right="60%",o=36===t(e.right),r=36===t(e.width),l.style.position="absolute",i=12===t(l.offsetWidth/3),re.removeChild(u),l=null}}function t(e){return Math.round(parseFloat(e))}var n,r,i,o,a,s,u=E.createElement("div"),l=E.createElement("div");l.style&&(l.style.backgroundClip="content-box",l.cloneNode(!0).style.backgroundClip="",v.clearCloneStyle="content-box"===l.style.backgroundClip,S.extend(v,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),o},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),s},scrollboxSize:function(){return e(),i},reliableTrDimensions:function(){var e,t,n,r;return null==a&&(e=E.createElement("table"),t=E.createElement("tr"),n=E.createElement("div"),e.style.cssText="position:absolute;left:-11111px;border-collapse:separate",t.style.cssText="border:1px solid",t.style.height="1px",n.style.height="9px",n.style.display="block",re.appendChild(e).appendChild(t).appendChild(n),r=C.getComputedStyle(t),a=parseInt(r.height,10)+parseInt(r.borderTopWidth,10)+parseInt(r.borderBottomWidth,10)===t.offsetHeight,re.removeChild(e)),a}}))}();var ze=["Webkit","Moz","ms"],Ue=E.createElement("div").style,Xe={};function Ve(e){var t=S.cssProps[e]||Xe[e];return t||(e in Ue?e:Xe[e]=function(e){var t=e[0].toUpperCase()+e.slice(1),n=ze.length;while(n--)if((e=ze[n]+t)in Ue)return e}(e)||e)}var Ge=/^(none|table(?!-c[ea]).+)/,Ye={position:"absolute",visibility:"hidden",display:"block"},Qe={letterSpacing:"0",fontWeight:"400"};function Je(e,t,n){var r=te.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function Ke(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=S.css(e,n+ne[a],!0,i)),r?("content"===n&&(u-=S.css(e,"padding"+ne[a],!0,i)),"margin"!==n&&(u-=S.css(e,"border"+ne[a]+"Width",!0,i))):(u+=S.css(e,"padding"+ne[a],!0,i),"padding"!==n?u+=S.css(e,"border"+ne[a]+"Width",!0,i):s+=S.css(e,"border"+ne[a]+"Width",!0,i));return!r&&0<=o&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u}function Ze(e,t,n){var r=Me(e),i=(!v.boxSizingReliable()||n)&&"border-box"===S.css(e,"boxSizing",!1,r),o=i,a=Be(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);if(Pe.test(a)){if(!n)return a;a="auto"}return(!v.boxSizingReliable()&&i||!v.reliableTrDimensions()&&A(e,"tr")||"auto"===a||!parseFloat(a)&&"inline"===S.css(e,"display",!1,r))&&e.getClientRects().length&&(i="border-box"===S.css(e,"boxSizing",!1,r),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+Ke(e,t,n||(i?"border":"content"),o,r,a)+"px"}function et(e,t,n,r,i){return new et.prototype.init(e,t,n,r,i)}S.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Be(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=X(t),u=Re.test(t),l=e.style;if(u||(t=Ve(s)),a=S.cssHooks[t]||S.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"===(o=typeof n)&&(i=te.exec(n))&&i[1]&&(n=se(e,t,i),o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(S.cssNumber[s]?"":"px")),v.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=X(t);return Re.test(t)||(t=Ve(s)),(a=S.cssHooks[t]||S.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=Be(e,t,r)),"normal"===i&&t in Qe&&(i=Qe[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),S.each(["height","width"],function(e,u){S.cssHooks[u]={get:function(e,t,n){if(t)return!Ge.test(S.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?Ze(e,u,n):Ie(e,Ye,function(){return Ze(e,u,n)})},set:function(e,t,n){var r,i=Me(e),o=!v.scrollboxSize()&&"absolute"===i.position,a=(o||n)&&"border-box"===S.css(e,"boxSizing",!1,i),s=n?Ke(e,u,n,a,i):0;return a&&o&&(s-=Math.ceil(e["offset"+u[0].toUpperCase()+u.slice(1)]-parseFloat(i[u])-Ke(e,u,"border",!1,i)-.5)),s&&(r=te.exec(t))&&"px"!==(r[3]||"px")&&(e.style[u]=t,t=S.css(e,u)),Je(0,t,s)}}}),S.cssHooks.marginLeft=_e(v.reliableMarginLeft,function(e,t){if(t)return(parseFloat(Be(e,"marginLeft"))||e.getBoundingClientRect().left-Ie(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),S.each({margin:"",padding:"",border:"Width"},function(i,o){S.cssHooks[i+o]={expand:function(e){for(var t=0,n={},r="string"==typeof e?e.split(" "):[e];t<4;t++)n[i+ne[t]+o]=r[t]||r[t-2]||r[0];return n}},"margin"!==i&&(S.cssHooks[i+o].set=Je)}),S.fn.extend({css:function(e,t){return B(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=Me(e),i=t.length;a<i;a++)o[t[a]]=S.css(e,t[a],!1,r);return o}return void 0!==n?S.style(e,t,n):S.css(e,t)},e,t,1<arguments.length)}}),((S.Tween=et).prototype={constructor:et,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||S.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(S.cssNumber[n]?"":"px")},cur:function(){var e=et.propHooks[this.prop];return e&&e.get?e.get(this):et.propHooks._default.get(this)},run:function(e){var t,n=et.propHooks[this.prop];return this.options.duration?this.pos=t=S.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):et.propHooks._default.set(this),this}}).init.prototype=et.prototype,(et.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=S.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){S.fx.step[e.prop]?S.fx.step[e.prop](e):1!==e.elem.nodeType||!S.cssHooks[e.prop]&&null==e.elem.style[Ve(e.prop)]?e.elem[e.prop]=e.now:S.style(e.elem,e.prop,e.now+e.unit)}}}).scrollTop=et.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},S.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},S.fx=et.prototype.init,S.fx.step={};var tt,nt,rt,it,ot=/^(?:toggle|show|hide)$/,at=/queueHooks$/;function st(){nt&&(!1===E.hidden&&C.requestAnimationFrame?C.requestAnimationFrame(st):C.setTimeout(st,S.fx.interval),S.fx.tick())}function ut(){return C.setTimeout(function(){tt=void 0}),tt=Date.now()}function lt(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=ne[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function ct(e,t,n){for(var r,i=(ft.tweeners[t]||[]).concat(ft.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function ft(o,e,t){var n,a,r=0,i=ft.prefilters.length,s=S.Deferred().always(function(){delete u.elem}),u=function(){if(a)return!1;for(var e=tt||ut(),t=Math.max(0,l.startTime+l.duration-e),n=1-(t/l.duration||0),r=0,i=l.tweens.length;r<i;r++)l.tweens[r].run(n);return s.notifyWith(o,[l,n,t]),n<1&&i?t:(i||s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l]),!1)},l=s.promise({elem:o,props:S.extend({},e),opts:S.extend(!0,{specialEasing:{},easing:S.easing._default},t),originalProperties:e,originalOptions:t,startTime:tt||ut(),duration:t.duration,tweens:[],createTween:function(e,t){var n=S.Tween(o,l.opts,e,t,l.opts.specialEasing[e]||l.opts.easing);return l.tweens.push(n),n},stop:function(e){var t=0,n=e?l.tweens.length:0;if(a)return this;for(a=!0;t<n;t++)l.tweens[t].run(1);return e?(s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l,e])):s.rejectWith(o,[l,e]),this}}),c=l.props;for(!function(e,t){var n,r,i,o,a;for(n in e)if(i=t[r=X(n)],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=S.cssHooks[r])&&"expand"in a)for(n in o=a.expand(o),delete e[r],o)n in e||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);r<i;r++)if(n=ft.prefilters[r].call(l,o,c,l.opts))return m(n.stop)&&(S._queueHooks(l.elem,l.opts.queue).stop=n.stop.bind(n)),n;return S.map(c,ct,l),m(l.opts.start)&&l.opts.start.call(o,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),S.fx.timer(S.extend(u,{elem:o,anim:l,queue:l.opts.queue})),l}S.Animation=S.extend(ft,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return se(n.elem,e,te.exec(t),n),n}]},tweener:function(e,t){m(e)?(t=e,e=["*"]):e=e.match(P);for(var n,r=0,i=e.length;r<i;r++)n=e[r],ft.tweeners[n]=ft.tweeners[n]||[],ft.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ae(e),y=Y.get(e,"fxshow");for(r in n.queue||(null==(a=S._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,S.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],ot.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!y||void 0===y[r])continue;g=!0}d[r]=y&&y[r]||S.style(e,r)}if((u=!S.isEmptyObject(t))||!S.isEmptyObject(d))for(r in f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=y&&y.display)&&(l=Y.get(e,"display")),"none"===(c=S.css(e,"display"))&&(l?c=l:(le([e],!0),l=e.style.display||l,c=S.css(e,"display"),le([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===S.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1,d)u||(y?"hidden"in y&&(g=y.hidden):y=Y.access(e,"fxshow",{display:l}),o&&(y.hidden=!g),g&&le([e],!0),p.done(function(){for(r in g||le([e]),Y.remove(e,"fxshow"),d)S.style(e,r,d[r])})),u=ct(g?y[r]:0,r,p),r in y||(y[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?ft.prefilters.unshift(e):ft.prefilters.push(e)}}),S.speed=function(e,t,n){var r=e&&"object"==typeof e?S.extend({},e):{complete:n||!n&&t||m(e)&&e,duration:e,easing:n&&t||t&&!m(t)&&t};return S.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in S.fx.speeds?r.duration=S.fx.speeds[r.duration]:r.duration=S.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){m(r.old)&&r.old.call(this),r.queue&&S.dequeue(this,r.queue)},r},S.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ae).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(t,e,n,r){var i=S.isEmptyObject(t),o=S.speed(e,n,r),a=function(){var e=ft(this,S.extend({},t),o);(i||Y.get(this,"finish"))&&e.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(i,e,o){var a=function(e){var t=e.stop;delete e.stop,t(o)};return"string"!=typeof i&&(o=e,e=i,i=void 0),e&&this.queue(i||"fx",[]),this.each(function(){var e=!0,t=null!=i&&i+"queueHooks",n=S.timers,r=Y.get(this);if(t)r[t]&&r[t].stop&&a(r[t]);else for(t in r)r[t]&&r[t].stop&&at.test(t)&&a(r[t]);for(t=n.length;t--;)n[t].elem!==this||null!=i&&n[t].queue!==i||(n[t].anim.stop(o),e=!1,n.splice(t,1));!e&&o||S.dequeue(this,i)})},finish:function(a){return!1!==a&&(a=a||"fx"),this.each(function(){var e,t=Y.get(this),n=t[a+"queue"],r=t[a+"queueHooks"],i=S.timers,o=n?n.length:0;for(t.finish=!0,S.queue(this,a,[]),r&&r.stop&&r.stop.call(this,!0),e=i.length;e--;)i[e].elem===this&&i[e].queue===a&&(i[e].anim.stop(!0),i.splice(e,1));for(e=0;e<o;e++)n[e]&&n[e].finish&&n[e].finish.call(this);delete t.finish})}}),S.each(["toggle","show","hide"],function(e,r){var i=S.fn[r];S.fn[r]=function(e,t,n){return null==e||"boolean"==typeof e?i.apply(this,arguments):this.animate(lt(r,!0),e,t,n)}}),S.each({slideDown:lt("show"),slideUp:lt("hide"),slideToggle:lt("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,r){S.fn[e]=function(e,t,n){return this.animate(r,e,t,n)}}),S.timers=[],S.fx.tick=function(){var e,t=0,n=S.timers;for(tt=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||S.fx.stop(),tt=void 0},S.fx.timer=function(e){S.timers.push(e),S.fx.start()},S.fx.interval=13,S.fx.start=function(){nt||(nt=!0,st())},S.fx.stop=function(){nt=null},S.fx.speeds={slow:600,fast:200,_default:400},S.fn.delay=function(r,e){return r=S.fx&&S.fx.speeds[r]||r,e=e||"fx",this.queue(e,function(e,t){var n=C.setTimeout(e,r);t.stop=function(){C.clearTimeout(n)}})},rt=E.createElement("input"),it=E.createElement("select").appendChild(E.createElement("option")),rt.type="checkbox",v.checkOn=""!==rt.value,v.optSelected=it.selected,(rt=E.createElement("input")).value="t",rt.type="radio",v.radioValue="t"===rt.value;var pt,dt=S.expr.attrHandle;S.fn.extend({attr:function(e,t){return B(this,S.attr,e,t,1<arguments.length)},removeAttr:function(e){return this.each(function(){S.removeAttr(this,e)})}}),S.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?S.prop(e,t,n):(1===o&&S.isXMLDoc(e)||(i=S.attrHooks[t.toLowerCase()]||(S.expr.match.bool.test(t)?pt:void 0)),void 0!==n?null===n?void S.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=S.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!v.radioValue&&"radio"===t&&A(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(P);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),pt={set:function(e,t,n){return!1===t?S.removeAttr(e,n):e.setAttribute(n,n),n}},S.each(S.expr.match.bool.source.match(/\w+/g),function(e,t){var a=dt[t]||S.find.attr;dt[t]=function(e,t,n){var r,i,o=t.toLowerCase();return n||(i=dt[o],dt[o]=r,r=null!=a(e,t,n)?o:null,dt[o]=i),r}});var ht=/^(?:input|select|textarea|button)$/i,gt=/^(?:a|area)$/i;function yt(e){return(e.match(P)||[]).join(" ")}function vt(e){return e.getAttribute&&e.getAttribute("class")||""}function mt(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(P)||[]}S.fn.extend({prop:function(e,t){return B(this,S.prop,e,t,1<arguments.length)},removeProp:function(e){return this.each(function(){delete this[S.propFix[e]||e]})}}),S.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&S.isXMLDoc(e)||(t=S.propFix[t]||t,i=S.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=S.find.attr(e,"tabindex");return t?parseInt(t,10):ht.test(e.nodeName)||gt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),v.optSelected||(S.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),S.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){S.propFix[this.toLowerCase()]=this}),S.fn.extend({addClass:function(t){var e,n,r,i,o,a;return m(t)?this.each(function(e){S(this).addClass(t.call(this,e,vt(this)))}):(e=mt(t)).length?this.each(function(){if(r=vt(this),n=1===this.nodeType&&" "+yt(r)+" "){for(o=0;o<e.length;o++)i=e[o],n.indexOf(" "+i+" ")<0&&(n+=i+" ");a=yt(n),r!==a&&this.setAttribute("class",a)}}):this},removeClass:function(t){var e,n,r,i,o,a;return m(t)?this.each(function(e){S(this).removeClass(t.call(this,e,vt(this)))}):arguments.length?(e=mt(t)).length?this.each(function(){if(r=vt(this),n=1===this.nodeType&&" "+yt(r)+" "){for(o=0;o<e.length;o++){i=e[o];while(-1<n.indexOf(" "+i+" "))n=n.replace(" "+i+" "," ")}a=yt(n),r!==a&&this.setAttribute("class",a)}}):this:this.attr("class","")},toggleClass:function(t,n){var e,r,i,o,a=typeof t,s="string"===a||Array.isArray(t);return m(t)?this.each(function(e){S(this).toggleClass(t.call(this,e,vt(this),n),n)}):"boolean"==typeof n&&s?n?this.addClass(t):this.removeClass(t):(e=mt(t),this.each(function(){if(s)for(o=S(this),i=0;i<e.length;i++)r=e[i],o.hasClass(r)?o.removeClass(r):o.addClass(r);else void 0!==t&&"boolean"!==a||((r=vt(this))&&Y.set(this,"__className__",r),this.setAttribute&&this.setAttribute("class",r||!1===t?"":Y.get(this,"__className__")||""))}))},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&-1<(" "+yt(vt(n))+" ").indexOf(t))return!0;return!1}});var xt=/\r/g;S.fn.extend({val:function(n){var r,e,i,t=this[0];return arguments.length?(i=m(n),this.each(function(e){var t;1===this.nodeType&&(null==(t=i?n.call(this,e,S(this).val()):n)?t="":"number"==typeof t?t+="":Array.isArray(t)&&(t=S.map(t,function(e){return null==e?"":e+""})),(r=S.valHooks[this.type]||S.valHooks[this.nodeName.toLowerCase()])&&"set"in r&&void 0!==r.set(this,t,"value")||(this.value=t))})):t?(r=S.valHooks[t.type]||S.valHooks[t.nodeName.toLowerCase()])&&"get"in r&&void 0!==(e=r.get(t,"value"))?e:"string"==typeof(e=t.value)?e.replace(xt,""):null==e?"":e:void 0}}),S.extend({valHooks:{option:{get:function(e){var t=S.find.attr(e,"value");return null!=t?t:yt(S.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!A(n.parentNode,"optgroup"))){if(t=S(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=S.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=-1<S.inArray(S.valHooks.option.get(r),o))&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),S.each(["radio","checkbox"],function(){S.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=-1<S.inArray(S(e).val(),t)}},v.checkOn||(S.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),v.focusin="onfocusin"in C;var bt=/^(?:focusinfocus|focusoutblur)$/,wt=function(e){e.stopPropagation()};S.extend(S.event,{trigger:function(e,t,n,r){var i,o,a,s,u,l,c,f,p=[n||E],d=y.call(e,"type")?e.type:e,h=y.call(e,"namespace")?e.namespace.split("."):[];if(o=f=a=n=n||E,3!==n.nodeType&&8!==n.nodeType&&!bt.test(d+S.event.triggered)&&(-1<d.indexOf(".")&&(d=(h=d.split(".")).shift(),h.sort()),u=d.indexOf(":")<0&&"on"+d,(e=e[S.expando]?e:new S.Event(d,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=h.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:S.makeArray(t,[e]),c=S.event.special[d]||{},r||!c.trigger||!1!==c.trigger.apply(n,t))){if(!r&&!c.noBubble&&!x(n)){for(s=c.delegateType||d,bt.test(s+d)||(o=o.parentNode);o;o=o.parentNode)p.push(o),a=o;a===(n.ownerDocument||E)&&p.push(a.defaultView||a.parentWindow||C)}i=0;while((o=p[i++])&&!e.isPropagationStopped())f=o,e.type=1<i?s:c.bindType||d,(l=(Y.get(o,"events")||Object.create(null))[e.type]&&Y.get(o,"handle"))&&l.apply(o,t),(l=u&&o[u])&&l.apply&&V(o)&&(e.result=l.apply(o,t),!1===e.result&&e.preventDefault());return e.type=d,r||e.isDefaultPrevented()||c._default&&!1!==c._default.apply(p.pop(),t)||!V(n)||u&&m(n[d])&&!x(n)&&((a=n[u])&&(n[u]=null),S.event.triggered=d,e.isPropagationStopped()&&f.addEventListener(d,wt),n[d](),e.isPropagationStopped()&&f.removeEventListener(d,wt),S.event.triggered=void 0,a&&(n[u]=a)),e.result}},simulate:function(e,t,n){var r=S.extend(new S.Event,n,{type:e,isSimulated:!0});S.event.trigger(r,null,t)}}),S.fn.extend({trigger:function(e,t){return this.each(function(){S.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return S.event.trigger(e,t,n,!0)}}),v.focusin||S.each({focus:"focusin",blur:"focusout"},function(n,r){var i=function(e){S.event.simulate(r,e.target,S.event.fix(e))};S.event.special[r]={setup:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r);t||e.addEventListener(n,i,!0),Y.access(e,r,(t||0)+1)},teardown:function(){var e=this.ownerDocument||this.document||this,t=Y.access(e,r)-1;t?Y.access(e,r,t):(e.removeEventListener(n,i,!0),Y.remove(e,r))}}});var Tt=C.location,Ct={guid:Date.now()},Et=/\?/;S.parseXML=function(e){var t,n;if(!e||"string"!=typeof e)return null;try{t=(new C.DOMParser).parseFromString(e,"text/xml")}catch(e){}return n=t&&t.getElementsByTagName("parsererror")[0],t&&!n||S.error("Invalid XML: "+(n?S.map(n.childNodes,function(e){return e.textContent}).join("\n"):e)),t};var St=/\[\]$/,kt=/\r?\n/g,At=/^(?:submit|button|image|reset|file)$/i,Nt=/^(?:input|select|textarea|keygen)/i;function jt(n,e,r,i){var t;if(Array.isArray(e))S.each(e,function(e,t){r||St.test(n)?i(n,t):jt(n+"["+("object"==typeof t&&null!=t?e:"")+"]",t,r,i)});else if(r||"object"!==w(e))i(n,e);else for(t in e)jt(n+"["+t+"]",e[t],r,i)}S.param=function(e,t){var n,r=[],i=function(e,t){var n=m(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!S.isPlainObject(e))S.each(e,function(){i(this.name,this.value)});else for(n in e)jt(n,e[n],t,i);return r.join("&")},S.fn.extend({serialize:function(){return S.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=S.prop(this,"elements");return e?S.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!S(this).is(":disabled")&&Nt.test(this.nodeName)&&!At.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=S(this).val();return null==n?null:Array.isArray(n)?S.map(n,function(e){return{name:t.name,value:e.replace(kt,"\r\n")}}):{name:t.name,value:n.replace(kt,"\r\n")}}).get()}});var Dt=/%20/g,qt=/#.*$/,Lt=/([?&])_=[^&]*/,Ht=/^(.*?):[ \t]*([^\r\n]*)$/gm,Ot=/^(?:GET|HEAD)$/,Pt=/^\/\//,Rt={},Mt={},It="*/".concat("*"),Wt=E.createElement("a");function Ft(o){return function(e,t){"string"!=typeof e&&(t=e,e="*");var n,r=0,i=e.toLowerCase().match(P)||[];if(m(t))while(n=i[r++])"+"===n[0]?(n=n.slice(1)||"*",(o[n]=o[n]||[]).unshift(t)):(o[n]=o[n]||[]).push(t)}}function $t(t,i,o,a){var s={},u=t===Mt;function l(e){var r;return s[e]=!0,S.each(t[e]||[],function(e,t){var n=t(i,o,a);return"string"!=typeof n||u||s[n]?u?!(r=n):void 0:(i.dataTypes.unshift(n),l(n),!1)}),r}return l(i.dataTypes[0])||!s["*"]&&l("*")}function Bt(e,t){var n,r,i=S.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&S.extend(!0,e,r),e}Wt.href=Tt.href,S.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Tt.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Tt.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":It,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":S.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?Bt(Bt(e,S.ajaxSettings),t):Bt(S.ajaxSettings,e)},ajaxPrefilter:Ft(Rt),ajaxTransport:Ft(Mt),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var c,f,p,n,d,r,h,g,i,o,y=S.ajaxSetup({},t),v=y.context||y,m=y.context&&(v.nodeType||v.jquery)?S(v):S.event,x=S.Deferred(),b=S.Callbacks("once memory"),w=y.statusCode||{},a={},s={},u="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(h){if(!n){n={};while(t=Ht.exec(p))n[t[1].toLowerCase()+" "]=(n[t[1].toLowerCase()+" "]||[]).concat(t[2])}t=n[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return h?p:null},setRequestHeader:function(e,t){return null==h&&(e=s[e.toLowerCase()]=s[e.toLowerCase()]||e,a[e]=t),this},overrideMimeType:function(e){return null==h&&(y.mimeType=e),this},statusCode:function(e){var t;if(e)if(h)T.always(e[T.status]);else for(t in e)w[t]=[w[t],e[t]];return this},abort:function(e){var t=e||u;return c&&c.abort(t),l(0,t),this}};if(x.promise(T),y.url=((e||y.url||Tt.href)+"").replace(Pt,Tt.protocol+"//"),y.type=t.method||t.type||y.method||y.type,y.dataTypes=(y.dataType||"*").toLowerCase().match(P)||[""],null==y.crossDomain){r=E.createElement("a");try{r.href=y.url,r.href=r.href,y.crossDomain=Wt.protocol+"//"+Wt.host!=r.protocol+"//"+r.host}catch(e){y.crossDomain=!0}}if(y.data&&y.processData&&"string"!=typeof y.data&&(y.data=S.param(y.data,y.traditional)),$t(Rt,y,t,T),h)return T;for(i in(g=S.event&&y.global)&&0==S.active++&&S.event.trigger("ajaxStart"),y.type=y.type.toUpperCase(),y.hasContent=!Ot.test(y.type),f=y.url.replace(qt,""),y.hasContent?y.data&&y.processData&&0===(y.contentType||"").indexOf("application/x-www-form-urlencoded")&&(y.data=y.data.replace(Dt,"+")):(o=y.url.slice(f.length),y.data&&(y.processData||"string"==typeof y.data)&&(f+=(Et.test(f)?"&":"?")+y.data,delete y.data),!1===y.cache&&(f=f.replace(Lt,"$1"),o=(Et.test(f)?"&":"?")+"_="+Ct.guid+++o),y.url=f+o),y.ifModified&&(S.lastModified[f]&&T.setRequestHeader("If-Modified-Since",S.lastModified[f]),S.etag[f]&&T.setRequestHeader("If-None-Match",S.etag[f])),(y.data&&y.hasContent&&!1!==y.contentType||t.contentType)&&T.setRequestHeader("Content-Type",y.contentType),T.setRequestHeader("Accept",y.dataTypes[0]&&y.accepts[y.dataTypes[0]]?y.accepts[y.dataTypes[0]]+("*"!==y.dataTypes[0]?", "+It+"; q=0.01":""):y.accepts["*"]),y.headers)T.setRequestHeader(i,y.headers[i]);if(y.beforeSend&&(!1===y.beforeSend.call(v,T,y)||h))return T.abort();if(u="abort",b.add(y.complete),T.done(y.success),T.fail(y.error),c=$t(Mt,y,t,T)){if(T.readyState=1,g&&m.trigger("ajaxSend",[T,y]),h)return T;y.async&&0<y.timeout&&(d=C.setTimeout(function(){T.abort("timeout")},y.timeout));try{h=!1,c.send(a,l)}catch(e){if(h)throw e;l(-1,e)}}else l(-1,"No Transport");function l(e,t,n,r){var i,o,a,s,u,l=t;h||(h=!0,d&&C.clearTimeout(d),c=void 0,p=r||"",T.readyState=0<e?4:0,i=200<=e&&e<300||304===e,n&&(s=function(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}(y,T,n)),!i&&-1<S.inArray("script",y.dataTypes)&&S.inArray("json",y.dataTypes)<0&&(y.converters["text script"]=function(){}),s=function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}(y,s,T,i),i?(y.ifModified&&((u=T.getResponseHeader("Last-Modified"))&&(S.lastModified[f]=u),(u=T.getResponseHeader("etag"))&&(S.etag[f]=u)),204===e||"HEAD"===y.type?l="nocontent":304===e?l="notmodified":(l=s.state,o=s.data,i=!(a=s.error))):(a=l,!e&&l||(l="error",e<0&&(e=0))),T.status=e,T.statusText=(t||l)+"",i?x.resolveWith(v,[o,l,T]):x.rejectWith(v,[T,l,a]),T.statusCode(w),w=void 0,g&&m.trigger(i?"ajaxSuccess":"ajaxError",[T,y,i?o:a]),b.fireWith(v,[T,l]),g&&(m.trigger("ajaxComplete",[T,y]),--S.active||S.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return S.get(e,t,n,"json")},getScript:function(e,t){return S.get(e,void 0,t,"script")}}),S.each(["get","post"],function(e,i){S[i]=function(e,t,n,r){return m(t)&&(r=r||n,n=t,t=void 0),S.ajax(S.extend({url:e,type:i,dataType:r,data:t,success:n},S.isPlainObject(e)&&e))}}),S.ajaxPrefilter(function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")}),S._evalUrl=function(e,t,n){return S.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){S.globalEval(e,t,n)}})},S.fn.extend({wrapAll:function(e){var t;return this[0]&&(m(e)&&(e=e.call(this[0])),t=S(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(n){return m(n)?this.each(function(e){S(this).wrapInner(n.call(this,e))}):this.each(function(){var e=S(this),t=e.contents();t.length?t.wrapAll(n):e.append(n)})},wrap:function(t){var n=m(t);return this.each(function(e){S(this).wrapAll(n?t.call(this,e):t)})},unwrap:function(e){return this.parent(e).not("body").each(function(){S(this).replaceWith(this.childNodes)}),this}}),S.expr.pseudos.hidden=function(e){return!S.expr.pseudos.visible(e)},S.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},S.ajaxSettings.xhr=function(){try{return new C.XMLHttpRequest}catch(e){}};var _t={0:200,1223:204},zt=S.ajaxSettings.xhr();v.cors=!!zt&&"withCredentials"in zt,v.ajax=zt=!!zt,S.ajaxTransport(function(i){var o,a;if(v.cors||zt&&!i.crossDomain)return{send:function(e,t){var n,r=i.xhr();if(r.open(i.type,i.url,i.async,i.username,i.password),i.xhrFields)for(n in i.xhrFields)r[n]=i.xhrFields[n];for(n in i.mimeType&&r.overrideMimeType&&r.overrideMimeType(i.mimeType),i.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest"),e)r.setRequestHeader(n,e[n]);o=function(e){return function(){o&&(o=a=r.onload=r.onerror=r.onabort=r.ontimeout=r.onreadystatechange=null,"abort"===e?r.abort():"error"===e?"number"!=typeof r.status?t(0,"error"):t(r.status,r.statusText):t(_t[r.status]||r.status,r.statusText,"text"!==(r.responseType||"text")||"string"!=typeof r.responseText?{binary:r.response}:{text:r.responseText},r.getAllResponseHeaders()))}},r.onload=o(),a=r.onerror=r.ontimeout=o("error"),void 0!==r.onabort?r.onabort=a:r.onreadystatechange=function(){4===r.readyState&&C.setTimeout(function(){o&&a()})},o=o("abort");try{r.send(i.hasContent&&i.data||null)}catch(e){if(o)throw e}},abort:function(){o&&o()}}}),S.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),S.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return S.globalEval(e),e}}}),S.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),S.ajaxTransport("script",function(n){var r,i;if(n.crossDomain||n.scriptAttrs)return{send:function(e,t){r=S("<script>").attr(n.scriptAttrs||{}).prop({charset:n.scriptCharset,src:n.url}).on("load error",i=function(e){r.remove(),i=null,e&&t("error"===e.type?404:200,e.type)}),E.head.appendChild(r[0])},abort:function(){i&&i()}}});var Ut,Xt=[],Vt=/(=)\?(?=&|$)|\?\?/;S.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Xt.pop()||S.expando+"_"+Ct.guid++;return this[e]=!0,e}}),S.ajaxPrefilter("json jsonp",function(e,t,n){var r,i,o,a=!1!==e.jsonp&&(Vt.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&Vt.test(e.data)&&"data");if(a||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=m(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,a?e[a]=e[a].replace(Vt,"$1"+r):!1!==e.jsonp&&(e.url+=(Et.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return o||S.error(r+" was not called"),o[0]},e.dataTypes[0]="json",i=C[r],C[r]=function(){o=arguments},n.always(function(){void 0===i?S(C).removeProp(r):C[r]=i,e[r]&&(e.jsonpCallback=t.jsonpCallback,Xt.push(r)),o&&m(i)&&i(o[0]),o=i=void 0}),"script"}),v.createHTMLDocument=((Ut=E.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===Ut.childNodes.length),S.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(v.createHTMLDocument?((r=(t=E.implementation.createHTMLDocument("")).createElement("base")).href=E.location.href,t.head.appendChild(r)):t=E),o=!n&&[],(i=N.exec(e))?[t.createElement(i[1])]:(i=xe([e],t,o),o&&o.length&&S(o).remove(),S.merge([],i.childNodes)));var r,i,o},S.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return-1<s&&(r=yt(e.slice(s)),e=e.slice(0,s)),m(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),0<a.length&&S.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?S("<div>").append(S.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},S.expr.pseudos.animated=function(t){return S.grep(S.timers,function(e){return t===e.elem}).length},S.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=S.css(e,"position"),c=S(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=S.css(e,"top"),u=S.css(e,"left"),("absolute"===l||"fixed"===l)&&-1<(o+u).indexOf("auto")?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),m(t)&&(t=t.call(e,n,S.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):c.css(f)}},S.fn.extend({offset:function(t){if(arguments.length)return void 0===t?this:this.each(function(e){S.offset.setOffset(this,t,e)});var e,n,r=this[0];return r?r.getClientRects().length?(e=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:e.top+n.pageYOffset,left:e.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===S.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===S.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=S(e).offset()).top+=S.css(e,"borderTopWidth",!0),i.left+=S.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-S.css(r,"marginTop",!0),left:t.left-i.left-S.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===S.css(e,"position"))e=e.offsetParent;return e||re})}}),S.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,i){var o="pageYOffset"===i;S.fn[t]=function(e){return B(this,function(e,t,n){var r;if(x(e)?r=e:9===e.nodeType&&(r=e.defaultView),void 0===n)return r?r[i]:e[t];r?r.scrollTo(o?r.pageXOffset:n,o?n:r.pageYOffset):e[t]=n},t,e,arguments.length)}}),S.each(["top","left"],function(e,n){S.cssHooks[n]=_e(v.pixelPosition,function(e,t){if(t)return t=Be(e,n),Pe.test(t)?S(e).position()[n]+"px":t})}),S.each({Height:"height",Width:"width"},function(a,s){S.each({padding:"inner"+a,content:s,"":"outer"+a},function(r,o){S.fn[o]=function(e,t){var n=arguments.length&&(r||"boolean"!=typeof e),i=r||(!0===e||!0===t?"margin":"border");return B(this,function(e,t,n){var r;return x(e)?0===o.indexOf("outer")?e["inner"+a]:e.document.documentElement["client"+a]:9===e.nodeType?(r=e.documentElement,Math.max(e.body["scroll"+a],r["scroll"+a],e.body["offset"+a],r["offset"+a],r["client"+a])):void 0===n?S.css(e,t,i):S.style(e,t,n,i)},s,n?e:void 0,n)}})}),S.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){S.fn[t]=function(e){return this.on(t,e)}}),S.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),S.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,n){S.fn[n]=function(e,t){return 0<arguments.length?this.on(n,null,e,t):this.trigger(n)}});var Gt=/^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g;S.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),m(e))return r=s.call(arguments,2),(i=function(){return e.apply(t||this,r.concat(s.call(arguments)))}).guid=e.guid=e.guid||S.guid++,i},S.holdReady=function(e){e?S.readyWait++:S.ready(!0)},S.isArray=Array.isArray,S.parseJSON=JSON.parse,S.nodeName=A,S.isFunction=m,S.isWindow=x,S.camelCase=X,S.type=w,S.now=Date.now,S.isNumeric=function(e){var t=S.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},S.trim=function(e){return null==e?"":(e+"").replace(Gt,"$1")},"function"==typeof define&&define.amd&&define("jquery",[],function(){return S});var Yt=C.jQuery,Qt=C.$;return S.noConflict=function(e){return C.$===S&&(C.$=Qt),e&&C.jQuery===S&&(C.jQuery=Yt),S},"undefined"==typeof e&&(C.jQuery=C.$=S),S});

/*! lazysizes - v5.3.2 */

!function(e){var t=function(u,D,f){"use strict";var k,H;if(function(){var e;var t={lazyClass:"lazyload",loadedClass:"lazyloaded",loadingClass:"lazyloading",preloadClass:"lazypreload",errorClass:"lazyerror",autosizesClass:"lazyautosizes",fastLoadedClass:"ls-is-cached",iframeLoadMode:0,srcAttr:"data-src",srcsetAttr:"data-srcset",sizesAttr:"data-sizes",minSize:40,customMedia:{},init:true,expFactor:1.5,hFac:.8,loadMode:2,loadHidden:true,ricTimeout:0,throttleDelay:125};H=u.lazySizesConfig||u.lazysizesConfig||{};for(e in t){if(!(e in H)){H[e]=t[e]}}}(),!D||!D.getElementsByClassName){return{init:function(){},cfg:H,noSupport:true}}var O=D.documentElement,i=u.HTMLPictureElement,P="addEventListener",$="getAttribute",q=u[P].bind(u),I=u.setTimeout,U=u.requestAnimationFrame||I,o=u.requestIdleCallback,j=/^picture$/i,r=["load","error","lazyincluded","_lazyloaded"],a={},G=Array.prototype.forEach,J=function(e,t){if(!a[t]){a[t]=new RegExp("(\\s|^)"+t+"(\\s|$)")}return a[t].test(e[$]("class")||"")&&a[t]},K=function(e,t){if(!J(e,t)){e.setAttribute("class",(e[$]("class")||"").trim()+" "+t)}},Q=function(e,t){var a;if(a=J(e,t)){e.setAttribute("class",(e[$]("class")||"").replace(a," "))}},V=function(t,a,e){var i=e?P:"removeEventListener";if(e){V(t,a)}r.forEach(function(e){t[i](e,a)})},X=function(e,t,a,i,r){var n=D.createEvent("Event");if(!a){a={}}a.instance=k;n.initEvent(t,!i,!r);n.detail=a;e.dispatchEvent(n);return n},Y=function(e,t){var a;if(!i&&(a=u.picturefill||H.pf)){if(t&&t.src&&!e[$]("srcset")){e.setAttribute("srcset",t.src)}a({reevaluate:true,elements:[e]})}else if(t&&t.src){e.src=t.src}},Z=function(e,t){return(getComputedStyle(e,null)||{})[t]},s=function(e,t,a){a=a||e.offsetWidth;while(a<H.minSize&&t&&!e._lazysizesWidth){a=t.offsetWidth;t=t.parentNode}return a},ee=function(){var a,i;var t=[];var r=[];var n=t;var s=function(){var e=n;n=t.length?r:t;a=true;i=false;while(e.length){e.shift()()}a=false};var e=function(e,t){if(a&&!t){e.apply(this,arguments)}else{n.push(e);if(!i){i=true;(D.hidden?I:U)(s)}}};e._lsFlush=s;return e}(),te=function(a,e){return e?function(){ee(a)}:function(){var e=this;var t=arguments;ee(function(){a.apply(e,t)})}},ae=function(e){var a;var i=0;var r=H.throttleDelay;var n=H.ricTimeout;var t=function(){a=false;i=f.now();e()};var s=o&&n>49?function(){o(t,{timeout:n});if(n!==H.ricTimeout){n=H.ricTimeout}}:te(function(){I(t)},true);return function(e){var t;if(e=e===true){n=33}if(a){return}a=true;t=r-(f.now()-i);if(t<0){t=0}if(e||t<9){s()}else{I(s,t)}}},ie=function(e){var t,a;var i=99;var r=function(){t=null;e()};var n=function(){var e=f.now()-a;if(e<i){I(n,i-e)}else{(o||r)(r)}};return function(){a=f.now();if(!t){t=I(n,i)}}},e=function(){var v,m,c,h,e;var y,z,g,p,C,b,A;var n=/^img$/i;var d=/^iframe$/i;var E="onscroll"in u&&!/(gle|ing)bot/.test(navigator.userAgent);var _=0;var w=0;var M=0;var N=-1;var L=function(e){M--;if(!e||M<0||!e.target){M=0}};var x=function(e){if(A==null){A=Z(D.body,"visibility")=="hidden"}return A||!(Z(e.parentNode,"visibility")=="hidden"&&Z(e,"visibility")=="hidden")};var W=function(e,t){var a;var i=e;var r=x(e);g-=t;b+=t;p-=t;C+=t;while(r&&(i=i.offsetParent)&&i!=D.body&&i!=O){r=(Z(i,"opacity")||1)>0;if(r&&Z(i,"overflow")!="visible"){a=i.getBoundingClientRect();r=C>a.left&&p<a.right&&b>a.top-1&&g<a.bottom+1}}return r};var t=function(){var e,t,a,i,r,n,s,o,l,u,f,c;var d=k.elements;if((h=H.loadMode)&&M<8&&(e=d.length)){t=0;N++;for(;t<e;t++){if(!d[t]||d[t]._lazyRace){continue}if(!E||k.prematureUnveil&&k.prematureUnveil(d[t])){R(d[t]);continue}if(!(o=d[t][$]("data-expand"))||!(n=o*1)){n=w}if(!u){u=!H.expand||H.expand<1?O.clientHeight>500&&O.clientWidth>500?500:370:H.expand;k._defEx=u;f=u*H.expFactor;c=H.hFac;A=null;if(w<f&&M<1&&N>2&&h>2&&!D.hidden){w=f;N=0}else if(h>1&&N>1&&M<6){w=u}else{w=_}}if(l!==n){y=innerWidth+n*c;z=innerHeight+n;s=n*-1;l=n}a=d[t].getBoundingClientRect();if((b=a.bottom)>=s&&(g=a.top)<=z&&(C=a.right)>=s*c&&(p=a.left)<=y&&(b||C||p||g)&&(H.loadHidden||x(d[t]))&&(m&&M<3&&!o&&(h<3||N<4)||W(d[t],n))){R(d[t]);r=true;if(M>9){break}}else if(!r&&m&&!i&&M<4&&N<4&&h>2&&(v[0]||H.preloadAfterLoad)&&(v[0]||!o&&(b||C||p||g||d[t][$](H.sizesAttr)!="auto"))){i=v[0]||d[t]}}if(i&&!r){R(i)}}};var a=ae(t);var S=function(e){var t=e.target;if(t._lazyCache){delete t._lazyCache;return}L(e);K(t,H.loadedClass);Q(t,H.loadingClass);V(t,B);X(t,"lazyloaded")};var i=te(S);var B=function(e){i({target:e.target})};var T=function(e,t){var a=e.getAttribute("data-load-mode")||H.iframeLoadMode;if(a==0){e.contentWindow.location.replace(t)}else if(a==1){e.src=t}};var F=function(e){var t;var a=e[$](H.srcsetAttr);if(t=H.customMedia[e[$]("data-media")||e[$]("media")]){e.setAttribute("media",t)}if(a){e.setAttribute("srcset",a)}};var s=te(function(t,e,a,i,r){var n,s,o,l,u,f;if(!(u=X(t,"lazybeforeunveil",e)).defaultPrevented){if(i){if(a){K(t,H.autosizesClass)}else{t.setAttribute("sizes",i)}}s=t[$](H.srcsetAttr);n=t[$](H.srcAttr);if(r){o=t.parentNode;l=o&&j.test(o.nodeName||"")}f=e.firesLoad||"src"in t&&(s||n||l);u={target:t};K(t,H.loadingClass);if(f){clearTimeout(c);c=I(L,2500);V(t,B,true)}if(l){G.call(o.getElementsByTagName("source"),F)}if(s){t.setAttribute("srcset",s)}else if(n&&!l){if(d.test(t.nodeName)){T(t,n)}else{t.src=n}}if(r&&(s||l)){Y(t,{src:n})}}if(t._lazyRace){delete t._lazyRace}Q(t,H.lazyClass);ee(function(){var e=t.complete&&t.naturalWidth>1;if(!f||e){if(e){K(t,H.fastLoadedClass)}S(u);t._lazyCache=true;I(function(){if("_lazyCache"in t){delete t._lazyCache}},9)}if(t.loading=="lazy"){M--}},true)});var R=function(e){if(e._lazyRace){return}var t;var a=n.test(e.nodeName);var i=a&&(e[$](H.sizesAttr)||e[$]("sizes"));var r=i=="auto";if((r||!m)&&a&&(e[$]("src")||e.srcset)&&!e.complete&&!J(e,H.errorClass)&&J(e,H.lazyClass)){return}t=X(e,"lazyunveilread").detail;if(r){re.updateElem(e,true,e.offsetWidth)}e._lazyRace=true;M++;s(e,t,r,i,a)};var r=ie(function(){H.loadMode=3;a()});var o=function(){if(H.loadMode==3){H.loadMode=2}r()};var l=function(){if(m){return}if(f.now()-e<999){I(l,999);return}m=true;H.loadMode=3;a();q("scroll",o,true)};return{_:function(){e=f.now();k.elements=D.getElementsByClassName(H.lazyClass);v=D.getElementsByClassName(H.lazyClass+" "+H.preloadClass);q("scroll",a,true);q("resize",a,true);q("pageshow",function(e){if(e.persisted){var t=D.querySelectorAll("."+H.loadingClass);if(t.length&&t.forEach){U(function(){t.forEach(function(e){if(e.complete){R(e)}})})}}});if(u.MutationObserver){new MutationObserver(a).observe(O,{childList:true,subtree:true,attributes:true})}else{O[P]("DOMNodeInserted",a,true);O[P]("DOMAttrModified",a,true);setInterval(a,999)}q("hashchange",a,true);["focus","mouseover","click","load","transitionend","animationend"].forEach(function(e){D[P](e,a,true)});if(/d$|^c/.test(D.readyState)){l()}else{q("load",l);D[P]("DOMContentLoaded",a);I(l,2e4)}if(k.elements.length){t();ee._lsFlush()}else{a()}},checkElems:a,unveil:R,_aLSL:o}}(),re=function(){var a;var n=te(function(e,t,a,i){var r,n,s;e._lazysizesWidth=i;i+="px";e.setAttribute("sizes",i);if(j.test(t.nodeName||"")){r=t.getElementsByTagName("source");for(n=0,s=r.length;n<s;n++){r[n].setAttribute("sizes",i)}}if(!a.detail.dataAttr){Y(e,a.detail)}});var i=function(e,t,a){var i;var r=e.parentNode;if(r){a=s(e,r,a);i=X(e,"lazybeforesizes",{width:a,dataAttr:!!t});if(!i.defaultPrevented){a=i.detail.width;if(a&&a!==e._lazysizesWidth){n(e,r,i,a)}}}};var e=function(){var e;var t=a.length;if(t){e=0;for(;e<t;e++){i(a[e])}}};var t=ie(e);return{_:function(){a=D.getElementsByClassName(H.autosizesClass);q("resize",t)},checkElems:t,updateElem:i}}(),t=function(){if(!t.i&&D.getElementsByClassName){t.i=true;re._();e._()}};return I(function(){H.init&&t()}),k={cfg:H,autoSizer:re,loader:e,init:t,uP:Y,aC:K,rC:Q,hC:J,fire:X,gW:s,rAF:ee}}(e,e.document,Date);e.lazySizes=t,"object"==typeof module&&module.exports&&(module.exports=t)}("undefined"!=typeof window?window:{});
/*! sprintf-js v1.1.2 | Copyright (c) 2007-present, Alexandru Mărășteanu <hello@alexei.ro> | BSD-3-Clause */
!function(){"use strict";var g={not_string:/[^s]/,not_bool:/[^t]/,not_type:/[^T]/,not_primitive:/[^v]/,number:/[diefg]/,numeric_arg:/[bcdiefguxX]/,json:/[j]/,not_json:/[^j]/,text:/^[^\x25]+/,modulo:/^\x25{2}/,placeholder:/^\x25(?:([1-9]\d*)\$|\(([^)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-gijostTuvxX])/,key:/^([a-z_][a-z_\d]*)/i,key_access:/^\.([a-z_][a-z_\d]*)/i,index_access:/^\[(\d+)\]/,sign:/^[+-]/};function y(e){return function(e,t){var r,n,i,s,a,o,p,c,l,u=1,f=e.length,d="";for(n=0;n<f;n++)if("string"==typeof e[n])d+=e[n];else if("object"==typeof e[n]){if((s=e[n]).keys)for(r=t[u],i=0;i<s.keys.length;i++){if(null==r)throw new Error(y('[sprintf] Cannot access property "%s" of undefined value "%s"',s.keys[i],s.keys[i-1]));r=r[s.keys[i]]}else r=s.param_no?t[s.param_no]:t[u++];if(g.not_type.test(s.type)&&g.not_primitive.test(s.type)&&r instanceof Function&&(r=r()),g.numeric_arg.test(s.type)&&"number"!=typeof r&&isNaN(r))throw new TypeError(y("[sprintf] expecting number but found %T",r));switch(g.number.test(s.type)&&(c=0<=r),s.type){case"b":r=parseInt(r,10).toString(2);break;case"c":r=String.fromCharCode(parseInt(r,10));break;case"d":case"i":r=parseInt(r,10);break;case"j":r=JSON.stringify(r,null,s.width?parseInt(s.width):0);break;case"e":r=s.precision?parseFloat(r).toExponential(s.precision):parseFloat(r).toExponential();break;case"f":r=s.precision?parseFloat(r).toFixed(s.precision):parseFloat(r);break;case"g":r=s.precision?String(Number(r.toPrecision(s.precision))):parseFloat(r);break;case"o":r=(parseInt(r,10)>>>0).toString(8);break;case"s":r=String(r),r=s.precision?r.substring(0,s.precision):r;break;case"t":r=String(!!r),r=s.precision?r.substring(0,s.precision):r;break;case"T":r=Object.prototype.toString.call(r).slice(8,-1).toLowerCase(),r=s.precision?r.substring(0,s.precision):r;break;case"u":r=parseInt(r,10)>>>0;break;case"v":r=r.valueOf(),r=s.precision?r.substring(0,s.precision):r;break;case"x":r=(parseInt(r,10)>>>0).toString(16);break;case"X":r=(parseInt(r,10)>>>0).toString(16).toUpperCase()}g.json.test(s.type)?d+=r:(!g.number.test(s.type)||c&&!s.sign?l="":(l=c?"+":"-",r=r.toString().replace(g.sign,"")),o=s.pad_char?"0"===s.pad_char?"0":s.pad_char.charAt(1):" ",p=s.width-(l+r).length,a=s.width&&0<p?o.repeat(p):"",d+=s.align?l+r+a:"0"===o?l+a+r:a+l+r)}return d}(function(e){if(p[e])return p[e];var t,r=e,n=[],i=0;for(;r;){if(null!==(t=g.text.exec(r)))n.push(t[0]);else if(null!==(t=g.modulo.exec(r)))n.push("%");else{if(null===(t=g.placeholder.exec(r)))throw new SyntaxError("[sprintf] unexpected placeholder");if(t[2]){i|=1;var s=[],a=t[2],o=[];if(null===(o=g.key.exec(a)))throw new SyntaxError("[sprintf] failed to parse named argument key");for(s.push(o[1]);""!==(a=a.substring(o[0].length));)if(null!==(o=g.key_access.exec(a)))s.push(o[1]);else{if(null===(o=g.index_access.exec(a)))throw new SyntaxError("[sprintf] failed to parse named argument key");s.push(o[1])}t[2]=s}else i|=2;if(3===i)throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported");n.push({placeholder:t[0],param_no:t[1],keys:t[2],sign:t[3],pad_char:t[4],align:t[5],width:t[6],precision:t[7],type:t[8]})}r=r.substring(t[0].length)}return p[e]=n}(e),arguments)}function e(e,t){return y.apply(null,[e].concat(t||[]))}var p=Object.create(null);"undefined"!=typeof exports&&(exports.sprintf=y,exports.vsprintf=e),"undefined"!=typeof window&&(window.sprintf=y,window.vsprintf=e,"function"==typeof define&&define.amd&&define(function(){return{sprintf:y,vsprintf:e}}))}();
//# sourceMappingURL=sprintf.min.js.map

!function(t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):("undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this).basicContext=t()}(function(){return function o(i,c,l){function r(n,t){if(!c[n]){if(!i[n]){var e="function"==typeof require&&require;if(!t&&e)return e(n,!0);if(a)return a(n,!0);throw(t=new Error("Cannot find module '"+n+"'")).code="MODULE_NOT_FOUND",t}e=c[n]={exports:{}},i[n][0].call(e.exports,function(t){return r(i[n][1][t]||t)},e,e.exports,o,i,c,l)}return c[n].exports}for(var a="function"==typeof require&&require,t=0;t<l.length;t++)r(l[t]);return r}({1:[function(t,n,e){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.visible=e.show=e.close=e.SEPARATOR=e.ITEM=void 0;function y(){var t=0<arguments.length&&void 0!==arguments[0]?arguments[0]:{};return null!=t.fn&&(!1!==t.visible&&(!0!==t.disabled&&(b("td[data-num='".concat(t.num,"']")).onclick=t.fn,b("td[data-num='".concat(t.num,"']")).oncontextmenu=t.fn,!0)))}function o(){var t=b();return!(null==t||0===t.length)}var i="item",c=(e.ITEM=i,"separator"),b=(e.SEPARATOR=c,function(){return document.querySelector(".basicContext "+(0<arguments.length&&void 0!==arguments[0]?arguments[0]:""))}),x=function(t,n){var e="",o="";return!1===function(){var t=0<arguments.length&&void 0!==arguments[0]?arguments[0]:{};return!0==(0===Object.keys(t).length)&&(t.type=c),null==t.type&&(t.type=i),null==t.class&&(t.class=""),!1!==t.visible&&(t.visible=!0),null==t.icon&&(t.icon=null),null==t.title&&(t.title="Undefined"),!0!==t.disabled&&(t.disabled=!1),!0===t.disabled&&(t.class+=" basicContext__item--disabled"),null!=t.fn||t.type===c||!1!==t.disabled||(console.warn("Missing fn for item '".concat(t.title,"'")),!1)}(t)||!1===t.visible?"":(t.num=n,null!==t.icon&&(o="<span class='basicContext__icon ".concat(t.icon,"'></span>")),t.type===i?e="\n\t\t\t\t <tr class='basicContext__item ".concat(t.class,"'>\n\t\t\t\t\t\t <td class='basicContext__data' data-num='").concat(t.num,"'>").concat(o).concat(t.title,"</td>\n\t\t\t\t </tr>\n\t\t\t\t "):t.type===c&&(e="\n\t\t\t\t <tr class='basicContext__item basicContext__item--separator'></tr>\n\t\t\t\t "),e)},v=function(){var t=0<arguments.length&&void 0!==arguments[0]?arguments[0]:{},n={x:t.clientX,y:t.clientY};return"touchend"!==t.type||null!=n.x&&null!=n.y||null!=(t=t.changedTouches)&&0<t.length&&(n.x=t[0].clientX,n.y=t[0].clientY),(null==n.x||n.x<0)&&(n.x=0),(null==n.y||n.y<0)&&(n.y=0),n},m=(e.show=function(t,n,e,o){i="",i+="\n\t\t\t\t<div class='basicContextContainer'>\n\t\t\t\t\t\t<div class='basicContext'>\n\t\t\t\t\t\t\t\t<table>\n\t\t\t\t\t\t\t\t\t\t<tbody>\n\t\t\t\t",t.forEach(function(t,n){return i+=x(t,n)});var i,c,l,r,a,s,u,f,d=i+="\n\t\t\t\t\t\t\t\t\t\t</tbody>\n\t\t\t\t\t\t\t\t</table>\n\t\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t",d=(document.body.insertAdjacentHTML("beforeend",d),b()),p=(l=d,c=v(c=n),r=c.x,a=c.y,s=document.querySelector(".basicContextContainer"),p=s.offsetWidth,s=s.offsetHeight,u=l.offsetWidth,f=l.offsetHeight,p<r+u&&(r-=r+u-p),s<a+f&&(a-=a+f-s),s<f&&(a=0,l.classList.add("basicContext--scrollable")),u=c.x-r,{x:r,y:a,rx:u,ry:c.y-a});return d.style.left="".concat(p.x,"px"),d.style.top="".concat(p.y,"px"),d.style.transformOrigin="".concat(p.rx,"px ").concat(p.ry,"px"),d.style.opacity=1,d.parentElement.onclick=e=null==e?m:e,d.parentElement.oncontextmenu=e,t.forEach(y),"function"==typeof n.preventDefault&&n.preventDefault(),"function"==typeof n.stopPropagation&&n.stopPropagation(),"function"==typeof o&&o(),!0},e.visible=o,function(){var t;return!1!==o()&&((t=document.querySelector(".basicContextContainer")).parentElement.removeChild(t),!0)});e.close=m},{}]},{},[1])(1)});
"use strict";

var _templateObject = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject2 = _taggedTemplateLiteral(["<div class='divider'><h1>$", "</h1></div>"], ["<div class='divider'><h1>$", "</h1></div>"]),
    _templateObject3 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject4 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject5 = _taggedTemplateLiteral(["<span title='", "'>", "</span>$", ""], ["<span title='", "'>", "</span>$", ""]),
    _templateObject6 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'\n\t\t\t\tdraggable='", "'\n\t\t\t\t", ">\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'\n\t\t\t\tdraggable='", "'\n\t\t\t\t", ">\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject7 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject9 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'\n\t\t\tdraggable='", "'\n\t\t\tondragstart='lychee.startDrag(event)'\n\t\t\tondragend='lychee.endDrag(event)'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'\n\t\t\tdraggable='", "'\n\t\t\tondragstart='lychee.startDrag(event)'\n\t\t\tondragend='lychee.endDrag(event)'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject10 = _taggedTemplateLiteral(["<a><span title='", "'>", "</span>", "</a>"], ["<a><span title='", "'>", "</span>", "</a>"]),
    _templateObject11 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject13 = _taggedTemplateLiteral(["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"], ["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"]),
    _templateObject14 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject15 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"]),
    _templateObject16 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject17 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject18 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject19 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject20 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject21 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject22 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"", "\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"", "\" />\n\t\t\t<span class=\"choice\" title=\"", "\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"", "\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"", "\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"", "\" />\n\t\t\t<span class=\"choice\" title=\"", "\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"", "\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"string\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"string\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject24 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject25 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>$", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>$", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t "]),
    _templateObject26 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject27 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject28 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject29 = _taggedTemplateLiteral(["<tr><td>$", "</td><td>", "</td></tr>"], ["<tr><td>$", "</td><td>", "</td></tr>"]),
    _templateObject30 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>$", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>$", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]);

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
	return cookie ? cookie.split("=")[1].trim().replace(/%3D/g, "") : null;
};

/**
 * @description Used as an alternative `main` to view single photos with `view.php`
 *
 * Note, the build script picks a subset of the JS files to build a variant
 * of the JS code for the special "view mode".
 * As this variant does not include all JS files, some objects are missing.
 * Hence, we must partially re-implement these objects to the extent which is
 * required by the methods we call.
 *
 * This approach is very tedious and error-prone, because we actually
 * duplicate code.
 * Also, it is not documented nor obvious why these "subset implementations"
 * are necessary.
 * Ideally, the full code base would be used all the time independent of
 * the users entry point.
 *
 * TODO: Find out why we actually need this approach. Re-implementing different variants of the same objects is very error-prone.
 */

// Sub-implementation of lychee -------------------------------------------------------------- //

var lychee = {};

lychee.content = $(".content");
lychee.imageview = $("#imageview");
lychee.mapview = $("#mapview");
lychee.locale = {};

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
 * @returns {string} - either `"touchend"` or `"click"`
 */
lychee.getEventName = function () {
	var touchendSupport = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || navigator.vendor || window.opera) && "ontouchend" in document.documentElement;
	return touchendSupport === true ? "touchend" : "click";
};

// Sub-implementation of photo -------------------------------------------------------------- //

var photo = {
	/** @type {?Photo} */
	json: null,
	/** @type {?LivePhotosKit.Player} */
	livePhotosObject: null
};

/**
 * @param {string} photoID
 * @param {string} service - one out of `"twitter"`, `"facebook"`, `"mail"` or `"dropbox"`
 * @returns {void}
 */
photo.share = function (photoID, service) {
	var url = location.toString();

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url));
			break;
		case "mail":
			location.href = "mailto:?subject=&body=" + encodeURI(url);
			break;
	}
};

/**
 * @returns {string}
 */
photo.getDirectLink = function () {
	return $("#imageview img").attr("src").replace(/"/g, "").replace(/url\(|\)$/gi, "");
};

/**
 * @returns {void}
 */
photo.show = function () {
	$("#imageview").removeClass("full");
	header.dom().removeClass("header--hidden");
};

/**
 * @returns {void}
 */
photo.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu()) {
		$("#imageview").addClass("full");
		header.dom().addClass("header--hidden");
	}
};

/**
 * @param {number} [animationDuration=300]
 * @param {number} [pauseBetweenUpdated=10]
 * @returns {void}
 */
photo.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuration = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (photo.isLivePhotoInitialized()) {
			photo.livePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuration);
};

/**
 * @returns {boolean}
 */
photo.isLivePhotoInitialized = function () {
	return !!photo.livePhotosObject;
};

/**
 * @returns {void}
 */
photo.onresize = function () {
	// Copy of view.photo.onresize
	if (photo.json.size_variants.medium === null || photo.json.size_variants.medium2x === null) return;

	var imgWidth = photo.json.size_variants.medium.width;
	var imgHeight = photo.json.size_variants.medium.height;
	var containerWidth = parseFloat($("#imageview").width());
	var containerHeight = parseFloat($("#imageview").height());

	var width = imgWidth < containerWidth ? imgWidth : containerWidth;
	var height = width * imgHeight / imgWidth;
	if (height > containerHeight) {
		width = containerHeight * imgWidth / imgHeight;
	}

	$("img#image").attr("sizes", width + "px");
};

// Sub-implementation of contextMenu -------------------------------------------------------------- //

var contextMenu = {};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 * @returns {void}
 */
contextMenu.sharePhoto = function (photoID, e) {
	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return photo.share(photoID, "twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return photo.share(photoID, "facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return photo.share(photoID, "mail");
		} }, { title: build.iconic("link-intact") + "Direct Link", fn: function fn() {
			return window.open(photo.getDirectLink(), "_newtab");
		} }];

	basicContext.show(items, e.originalEvent);
};

// Sub-implementation of photo -------------------------------------------------------------- //

var album = {
	json: null
};

album.isUploadable = function () {
	return false;
};

// Main -------------------------------------------------------------- //

var loadingBar = {
	show: function show() {},
	hide: function hide() {}
};

/**
 * @type {jQuery}
 */
var imageview = $("#imageview");

$(document).ready(function () {
	// Image View
	$(window).on("resize", photo.onresize);

	// Save ID of photo
	var queryParams = new URLSearchParams(document.location.search);
	var photoID = queryParams.get("p");

	// Set API error handler
	api.onError = handleAPIError;

	// Share
	header.dom("#button_share").on("click", function (e) {
		contextMenu.sharePhoto(photoID, e);
	});

	// Infobox
	header.dom("#button_info").on("click", function () {
		sidebar.toggle(true);
	});

	// Load photo
	loadPhotoInfo(photoID);
});

/**
 * TODO: This method is global for no particular reason. In case we ever clean up the view mode, this should be fixed, too.
 * @param {string} photoID
 */
var loadPhotoInfo = function loadPhotoInfo(photoID) {
	var params = {
		photoID: photoID
	};

	api.post("Photo::get", params,
	/** @param {Photo} data */
	function (data) {
		photo.json = data;

		// Set title
		var _title = data.title ? data.title : lychee.locale["UNTITLED"];
		// TODO: Actually the prefix should not be a hard-coded, but the value of `lychee.title`. However, I am unsure whether we load the configuration options in view mode.
		document.title = "Lychee – " + _title;
		header.dom(".header__title").text(_title);

		if (photo.json.hasOwnProperty("is_share_button_visible") && !photo.json.is_share_button_visible) {
			$("#button_share").hide();
		}

		// Render HTML
		imageview.html(build.imageview(data, true, false).html);
		imageview.find(".arrow_wrapper").remove();
		imageview.addClass("fadeIn").show();
		photo.onresize();

		// Render Sidebar
		var structure = sidebar.createStructure.photo(data);
		var html = sidebar.render(structure);

		// Fullscreen
		var timeout = null;

		$(document).bind("mousemove", function () {
			clearTimeout(timeout);
			photo.show();
			timeout = setTimeout(photo.hide, 2500);
		});
		timeout = setTimeout(photo.hide, 2500);

		sidebar.dom(".sidebar__wrapper").html(html);
		sidebar.bind();
	});
};

/**
 * @param {XMLHttpRequest} jqXHR
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee Exception
 * @returns {boolean}
 */
var handleAPIError = function handleAPIError(jqXHR, params, lycheeException) {
	var msg = jqXHR.statusText + (lycheeException ? " - " + lycheeException.message : "");
	loadingBar.show("error", msg);
	console.error("The server returned an error response", {
		description: msg,
		params: params,
		response: lycheeException
	});
	return true;
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

	return lychee.html(_templateObject, classes, icon);
};

/**
 * @param {string} title
 * @returns {string}
 */
build.divider = function (title) {
	return lychee.html(_templateObject2, title);
};

/**
 * @param {string} id
 * @returns {string}
 */
build.editIcon = function (id) {
	return lychee.html(_templateObject3, id, build.iconic("pencil"));
};

/**
 * @param {number} top
 * @param {number} left
 * @returns {string}
 */
build.multiselect = function (top, left) {
	return lychee.html(_templateObject4, top, left);
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
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false'></span>";
	}
	if (thumb === "uploads/thumb/" && isRaw) {
		return "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false'></span>";
	}

	return "<span class=\"thumbimg" + (isVideo ? " video" : "") + "\"><img class='lazyload' src='img/placeholder.png' data-src='" + thumb + "' " + (thumb2x !== null ? "data-srcset='" + thumb2x + " 2x'" : "") + " alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false'></span>";
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
	// The condition below is faulty wrt. to two issues:
	//
	//  a) The condition only checks whether the owning/current album is
	//     uploadable (aka "editable"), but it does not check whether the
	//     album at hand whose icon is built is editable.
	//     But this is of similar importance.
	//     Currently, we only check whether the album at hand is a smart
	//     album or tag album which are always considered non-editable.
	//     But this is only half of the story.
	//     For example, a regular album might still be non-editable, if the
	//     current user is not the owner of that album.
	//  b) This method is not only called if the owning/current album is a
	//     proper album, but also for the root view.
	//     However, `album.isUploadable` should not be called for the root
	//     view.
	//
	// Moreover, we have to distinguish between "drag" and "drop".
	// Doing so would also solve the problems above:
	//
	// - "Drag": If the current child album at hand can be dragged (away)
	//   is mostly determined by the user's rights on the parent album.
	//   Instead of (erroneously) using `album.isUploadable()` for that
	//   (even for the root view), the "right to drag" should be passed to
	//   this method as a parameter very much like `disabled` such that this
	//   method can be used for both regular albums and the root view.
	// - "Drop": If something (e.g. a photo) can be dropped onto the child
	//   album at hand is independent of the user's rights on the containing
	//   album.
	//   Whether the child album supports the drop event depends on the type
	//   of the album (i.e. it must not be a smart or tag album), but also
	//   on the ownership of the album.
	var disableDragDrop = !album.isUploadable() || disabled || album.isSmartID(data.id) || data.is_tag_album;
	var subtitle = formattedCreationTs;

	// check setting album_subtitle_type:
	// takedate: date range (min/max_takedate from EXIF; if missing defaults to creation)
	// creation: creation date of album
	// description: album description
	// default: any other type defaults to old style setting subtitles based of album sorting
	switch (lychee.album_subtitle_type) {
		case "description":
			subtitle = data.description ? lychee.escapeHTML(data.description) : "";
			break;
		case "takedate":
			if (formattedMinTs !== "" || formattedMaxTs !== "") {
				// either min_taken_at or max_taken_at is set
				subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
				subtitle = lychee.html(_templateObject5, lychee.locale["CAMERA_DATE"], build.iconic("camera-slr"), subtitle);
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

	var html = lychee.html(_templateObject6, disabled ? "disabled" : "", data.is_nsfw && lychee.nsfw_blur ? "blurred" : "", data.id, data.is_nsfw ? "1" : "0", tabindex.get_next_tab_index(), disableDragDrop ? "false" : "true", disableDragDrop ? "" : "ondragstart='lychee.startDrag(event)'\n\t\t\t\tondragover='lychee.overDrag(event)'\n\t\t\t\tondragleave='lychee.leaveDrag(event)'\n\t\t\t\tondragend='lychee.endDrag(event)'\n\t\t\t\tondrop='lychee.finishDrag(event)'", build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject7, data.is_nsfw ? "badge--nsfw" : "", build.iconic("warning"), data.id === SmartAlbumID.STARRED ? "badge--star" : "", build.iconic("star"), data.id === SmartAlbumID.RECENT ? "badge--visible badge--list" : "", build.iconic("clock"), data.id === SmartAlbumID.PUBLIC || data.is_public ? "badge--visible" : "", data.requires_link ? "badge--hidden" : "badge--not--hidden", build.iconic("eye"), data.id === SmartAlbumID.UNSORTED ? "badge--visible" : "", build.iconic("list"), data.has_password ? "badge--visible" : "", build.iconic("lock-locked"), data.is_tag_album ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.has_albums) {
		html += lychee.html(_templateObject8, build.iconic("layers"));
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
			thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
		if (isVideo) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		} else if (isRaw) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
	} else if (lychee.layout === 0) {
		if (data.size_variants.thumb2x !== null) {
			thumb2x = data.size_variants.thumb2x.url;
		}

		if (thumb2x !== "") {
			thumb2x = "data-srcset='" + thumb2x + " 2x'";
		}

		thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + (" alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' >");
		thumbnail += "</span>";
	} else {
		if (data.size_variants.small !== null) {
			if (data.size_variants.small2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.small.url + " " + data.size_variants.small.width + "w, " + data.size_variants.small2x.url + " " + data.size_variants.small2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.small.url + "' " + thumb2x + (" alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' >");
			thumbnail += "</span>";
		} else if (data.size_variants.medium !== null) {
			if (data.size_variants.medium2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.medium.url + "' " + thumb2x + (" alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' >");
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.original.url + "' alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' >";
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
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + (" alt='" + lychee.locale["PHOTO_THUMBNAIL"] + "' data-overlay='false' draggable='false' >");
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject9, disabled ? "disabled" : "", data.album_id, data.id, tabindex.get_next_tab_index(), !album.isUploadable() || disabled ? "false" : "true", thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject10, lychee.locale["CAMERA_DATE"], build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject11, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		// Note, `album.json` might be null, if the photo is displayed as
		// part of a search result and therefore the actual parent album
		// is not loaded. (The "parent" album is the virtual "search album"
		// in this case).
		// This also means that the displayed variant of the public badge of
		// a photo depends on the availability of the parent album.
		// This seems to be an undesired but unavoidable side effect.
		html += lychee.html(_templateObject12, data.is_starred ? "badge--star" : "", build.iconic("star"), data.is_public && album.json && !album.json.is_public ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
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
			overlay = lychee.escapeHTML(data.description);
			break;
		case "date":
			if (data.taken_at != null) overlay = "<a><span title='" + lychee.locale["CAMERA_DATE"] + "'>" + build.iconic("camera-slr") + "</span>" + lychee.locale.printDateTime(data.taken_at) + "</a>";else overlay = lychee.locale.printDateTime(data.created_at);
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
					overlay += sprintf(lychee.locale["PHOTO_ISO"], data.iso);
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

	return lychee.html(_templateObject13, data.title ? data.title : lychee.locale["UNTITLED"]) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
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
		html += lychee.html(_templateObject14, areControlsVisible ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.size_variants.original.url);
	} else if (data.type.indexOf("raw") > -1 && data.size_variants.medium === null) {
		html += lychee.html(_templateObject15, areControlsVisible ? "" : "full", tabindex.get_next_tab_index());
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

		html += lychee.html(_templateObject16, img);
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

	html += lychee.html(_templateObject17, build.iconic(type));

	switch (type) {
		case "magnifying-glass":
			html += lychee.html(_templateObject18, lychee.locale["VIEW_NO_RESULT"]);
			break;
		case "eye":
			html += lychee.html(_templateObject18, lychee.locale["VIEW_NO_PUBLIC_ALBUMS"]);
			break;
		case "cog":
			html += lychee.html(_templateObject18, lychee.locale["VIEW_NO_CONFIGURATION"]);
			break;
		case "question-mark":
			html += lychee.html(_templateObject18, lychee.locale["VIEW_PHOTO_NOT_FOUND"]);
			break;
	}

	html += "</div>";

	return html;
};

/**
 * @param {string[]} tags
 * @returns {string} return safe HTMl code
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
				html += lychee.html(_templateObject19, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject20, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject21, lychee.locale["NO_TAGS"]);
	}

	return html;
};

/**
 * @param {User} user
 * @returns {string}
 */
build.user = function (user) {
	return lychee.html(_templateObject22, user.id, user.id, user.username, lychee.locale["USERNAME"], lychee.locale["NEW_PASSWORD"], lychee.locale["ALLOW_UPLOADS"], lychee.locale["RESTRICTED_ACCOUNT"], user.id, user.id);
};

/**
 * @param {WebAuthnCredential} credential
 * @returns {string}
 */
build.u2f = function (credential) {
	return lychee.html(_templateObject23, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
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

		if (visible.photo()) contextMenu.photoTitle(album.getID(), photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function () {
		photo.setProtectionPolicy(photo.getID());
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(photo.getID(), e);
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
		sidebar.toggle(true);
	});
	header.dom("#button_info").on(eventName, function () {
		sidebar.toggle(true);
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
		contextMenu.photoMore(photo.getID(), e);
	});
	header.dom("#button_move_album").on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParentID() != null);
	});
	header.dom("#button_nsfw_album").on(eventName, function () {
		album.toggleNSFW();
	});
	header.dom("#button_move").on(eventName, function (e) {
		contextMenu.move([photo.getID()], e, photo.setAlbum);
	});
	header.dom(".header__hostedwith").on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom("#button_trash_album").on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom("#button_trash").on(eventName, function () {
		photo.delete([photo.getID()]);
	});
	header.dom("#button_archive").on(eventName, function () {
		album.getArchive([album.getID()]);
	});
	header.dom("#button_star").on(eventName, function () {
		photo.toggleStar();
	});
	header.dom("#button_rotate_ccwise").on(eventName, function () {
		photoeditor.rotate(photo.getID(), -1);
	});
	header.dom("#button_rotate_cwise").on(eventName, function () {
		photoeditor.rotate(photo.getID(), 1);
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
			lychee.goto(SearchAlbumIDPrefix + "/" + encodeURIComponent($(this).val()));
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

	photo.updateSizeLivePhotoDuringAnimation();
};

/**
 * @returns {void}
 */
header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (!photo.isLivePhotoPlaying()) header.hide();
};

/**
 * @returns {void}
 */
header.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.isVisible() === false) {
		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass("full");
		header.dom().addClass("header--hidden");

		photo.updateSizeLivePhotoDuringAnimation();
	}
};

/**
 * @param {string} title
 * @returns {void}
 */
header.setTitle = function (title) {
	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject24, title, build.iconic("caret-bottom"));

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
				var _e = $(".header__search, .header__clear", ".header__toolbar--public");
				_e.hide();
				tabindex.makeUnfocusable(_e);
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				var _e2 = $(".button--map-albums", ".header__toolbar--public");
				_e2.show();
				tabindex.makeFocusable(_e2);
			} else {
				var _e3 = $(".button--map-albums", ".header__toolbar--public");
				_e3.hide();
				tabindex.makeUnfocusable(_e3);
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
				var _e4 = $(".button--map-albums", ".header__toolbar--albums");
				_e4.show();
				tabindex.makeFocusable(_e4);
			} else {
				var _e5 = $(".button--map-albums", ".header__toolbar--albums");
				_e5.hide();
				tabindex.makeUnfocusable(_e5);
			}

			if (lychee.enable_button_add && lychee.rights.may_upload) {
				var _e6 = $(".button_add", ".header__toolbar--albums");
				_e6.show();
				tabindex.makeFocusable(_e6);
			} else {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.remove();
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
				var _e8 = $("#button_archive");
				_e8.hide();
				tabindex.makeUnfocusable(_e8);
			} else {
				var _e9 = $("#button_archive");
				_e9.show();
				tabindex.makeFocusable(_e9);
			}

			if (album.json && album.json.is_share_button_visible === false && (
			// The owner of an album (or the admin) shall always see
			// the share button and be unaffected by the settings of
			// the album
			lychee.user === null || lychee.user.username !== album.json.owner_name) && !lychee.rights.is_admin) {
				var _e10 = $("#button_share_album");
				_e10.hide();
				tabindex.makeUnfocusable(_e10);
			} else {
				var _e11 = $("#button_share_album");
				_e11.show();
				tabindex.makeFocusable(_e11);
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e12 = $("#button_map_album");
				_e12.show();
				tabindex.makeFocusable(_e12);
			} else {
				var _e13 = $("#button_map_album");
				_e13.hide();
				tabindex.makeUnfocusable(_e13);
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
				if (sidebar.keepSidebarVisible() && !visible.sidebar()) sidebar.toggle(false);
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
				if (sidebar.keepSidebarVisible() && !visible.sidebar()) sidebar.toggle(false);
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
				var _e14 = $("#button_visibility_album", "#button_sharing_album_users", ".header__toolbar--album");
				_e14.remove();
			}
			if (!lychee.enable_button_share) {
				var _e15 = $("#button_share_album", ".header__toolbar--album");
				_e15.remove();
			}
			if (!lychee.enable_button_archive) {
				var _e16 = $("#button_archive", ".header__toolbar--album");
				_e16.remove();
			}
			if (!lychee.enable_button_move) {
				var _e17 = $("#button_move_album", ".header__toolbar--album");
				_e17.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e18 = $("#button_trash_album", ".header__toolbar--album");
				_e18.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e19 = $("#button_fs_album_enter", ".header__toolbar--album");
				_e19.remove();
			}
			if (!lychee.enable_button_add) {
				var _e20 = $(".button_add", ".header__toolbar--album");
				_e20.remove();
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
				var _e21 = $("#button_map");
				_e21.show();
				tabindex.makeFocusable(_e21);
			} else {
				var _e22 = $("#button_map");
				_e22.hide();
				tabindex.makeUnfocusable(_e22);
			}

			if (album.isUploadable()) {
				var _e23 = $("#button_trash, #button_move, #button_visibility, #button_star, #button_rotate_cwise, #button_rotate_ccwise");
				_e23.show();
				tabindex.makeFocusable(_e23);
			} else {
				var _e24 = $("#button_trash, #button_move, #button_visibility, #button_star, #button_rotate_cwise, #button_rotate_ccwise");
				_e24.hide();
				tabindex.makeUnfocusable(_e24);
			}

			if (photo.json && photo.json.hasOwnProperty("is_share_button_visible") && !photo.json.is_share_button_visible) {
				var _e25 = $("#button_share");
				_e25.hide();
				tabindex.makeUnfocusable(_e25);
			} else {
				var _e26 = $("#button_share");
				_e26.show();
				tabindex.makeFocusable(_e26);
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$("#button_more").show();
			tabindex.makeFocusable($("#button_more"));
			if (!(album.isUploadable() || (photo.json.hasOwnProperty("is_downloadable") ? photo.json.is_downloadable : album.json && album.json.is_downloadable)) && !(photo.json.size_variants.original.url && photo.json.size_variants.original.url !== "")) {
				var _e27 = $("#button_more");
				_e27.hide();
				tabindex.makeUnfocusable(_e27);
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e28 = $("#button_visibility", ".header__toolbar--photo");
				_e28.remove();
			}
			if (!lychee.enable_button_share) {
				var _e29 = $("#button_share", ".header__toolbar--photo");
				_e29.remove();
			}
			if (!lychee.enable_button_move) {
				var _e30 = $("#button_move", ".header__toolbar--photo");
				_e30.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e31 = $("#button_trash", ".header__toolbar--photo");
				_e31.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e32 = $("#button_fs_enter", ".header__toolbar--photo");
				_e32.remove();
			}
			if (!lychee.enable_button_more) {
				var _e33 = $("#button_more", ".header__toolbar--photo");
				_e33.remove();
			}
			if (!lychee.enable_button_rotate) {
				var _e34 = $("#button_rotate_cwise", ".header__toolbar--photo");
				_e34.remove();

				_e34 = $("#button_rotate_ccwise", ".header__toolbar--photo");
				_e34.remove();
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
	return visible.albums() && album.json !== null && album.isSearchID(album.json.id);
};

/** @returns {boolean} */
visible.sidebar = function () {
	return !!sidebar.dom().hasClass("active");
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
 * @description This module takes care of the sidebar.
 */

/**
 * @namespace
 */
var sidebar = {
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
sidebar.dom = function (selector) {
	if (selector == null || selector === "") return sidebar._dom;
	return sidebar._dom.find(selector);
};

/**
 * This function should be called after building and appending
 * the sidebars content to the DOM.
 * This function can be called multiple times, therefore
 * event handlers should be removed before binding a new one.
 *
 * @returns {void}
 */
sidebar.bind = function () {
	var eventName = lychee.getEventName();

	sidebar.dom("#edit_title").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setTitle([photo.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	sidebar.dom("#edit_description").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setDescription(photo.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	sidebar.dom("#edit_uploaded").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setCreatedAt(photo.getID());
	});

	sidebar.dom("#edit_showtags").off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	sidebar.dom("#edit_tags").off(eventName).on(eventName, function () {
		photo.editTags([photo.getID()]);
	});

	sidebar.dom("#tags .tag").off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});

	sidebar.dom("#tags .tag span").off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data("index"));
	});

	sidebar.dom("#edit_license").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setLicense(photo.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	sidebar.dom("#edit_sorting").off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	sidebar.dom(".attr_location").off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});
};

/**
 * @param {string} search_string
 * @returns {void}
 */
sidebar.triggerSearch = function (search_string) {
	// If public search is disabled -> do nothing
	if (lychee.publicMode && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.json = null;
	// We're either logged in or public search is allowed
	lychee.goto(SearchAlbumIDPrefix + "/" + encodeURIComponent(search_string));
};

/**
 * @returns {boolean}
 */
sidebar.keepSidebarVisible = function () {
	var v = sessionStorage.getItem("keepSidebarVisible");
	return v !== null ? v === "true" : false;
};

/**
 * @param {boolean} is_user_initiated - indicates if the user requested to
 *                                      toggle and hence the new state shall
 *                                      be saved in session storage
 * @returns {void}
 */
sidebar.toggle = function (is_user_initiated) {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		setTimeout(function () {
			return view.album.content.justify();
		}, 0);
		sidebar.dom().toggleClass("active");
		if (photo.updateSizeLivePhotoDuringAnimation) photo.updateSizeLivePhotoDuringAnimation();

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
sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	if (selectable) sidebar.dom().removeClass("notSelectable");else sidebar.dom().addClass("notSelectable");
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @param {?string} value - a `null` value is replaced by the empty string
 * @param {boolean} [dangerouslySetInnerHTML=false]
 *
 * @returns {void}
 */
sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (!attr) return;
	if (!value) value = "";

	// TODO: Don't use our home-brewed `escapeHTML` method; use `jQuery#text` instead
	// Escape value
	if (!dangerouslySetInnerHTML) value = lychee.escapeHTML(value);

	sidebar.dom(".attr_" + attr).html(value);
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @returns {void}
 */
sidebar.hideAttr = function (attr) {
	sidebar.dom(".attr_" + attr).closest("tr").hide();
};

/**
 * Converts integer seconds into "hours:minutes:seconds".
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {(number|string)} d
 * @returns {string}
 */
sidebar.secondsToHMS = function (d) {
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
sidebar.createStructure.photo = function (data) {
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
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at), editable: editable }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: sidebar.types.DEFAULT,
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
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: sidebar.secondsToHMS(data.aperture) });
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
		type: sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	// Only create EXIF section when EXIF data available
	if (hasExif) {
		structure.exif = {
			title: lychee.locale["PHOTO_CAMERA"],
			type: sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }] : [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }, { title: lychee.locale["PHOTO_LENS"], kind: "lens", value: data.lens }, { title: lychee.locale["PHOTO_SHUTTER"], kind: "shutter", value: data.shutter }, { title: lychee.locale["PHOTO_APERTURE"], kind: "aperture", value: data.aperture }, { title: lychee.locale["PHOTO_FOCAL"], kind: "focal", value: data.focal }, { title: sprintf(lychee.locale["PHOTO_ISO"], ""), kind: "iso", value: data.iso }]
		};
	} else {
		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale["PHOTO_SHARING"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SHR_PUBLIC"], kind: "public", value: isPublic }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (hasLocation) {
		structure.location = {
			title: lychee.locale["PHOTO_LOCATION"],
			type: sidebar.types.DEFAULT,
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
sidebar.createStructure.album = function (data) {
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
		type: sidebar.types.DEFAULT,
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
		type: sidebar.types.DEFAULT,
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
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: isPublic }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: requiresLink }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: isDownloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: isShareButtonVisible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: hasPassword }]
	};

	if (data.owner_name) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner_name });
	}

	structure.license = {
		title: lychee.locale["ALBUM_REUSE"],
		type: sidebar.types.DEFAULT,
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
sidebar.has_location = function (structure) {
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
sidebar.render = function (structure) {
	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderDefault = function renderDefault(section) {
		var _html = lychee.html(_templateObject25, section.title);

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
			var rawValue = row.value;

			// don't show rows which are empty and cannot be edited
			if ((rawValue === "" || rawValue == null) && row.editable === false) {
				return;
			}

			/** @type {string} */
			var htmlValue = void 0;
			// Wrap span-element around value for easier selecting on change
			if (Array.isArray(rawValue)) {
				htmlValue = rawValue.reduce(
				/**
     * @param {string} prev
     * @param {string} cur
     */
				function (prev, cur) {
					// Add separator if needed
					if (prev !== "") {
						prev += lychee.html(_templateObject26, row.kind);
					}
					return prev + lychee.html(_templateObject27, row.kind, cur);
				}, "");
			} else {
				htmlValue = lychee.html(_templateObject28, row.kind, rawValue);
			}

			// Add edit-icon to the value when editable
			if (row.editable === true) htmlValue += " " + build.editIcon("edit_" + row.kind);

			_html += lychee.html(_templateObject29, row.title, htmlValue);
		});

		_html += "</table>";

		return _html;
	};

	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderTags = function renderTags(section) {
		// TODO: IDE warns me that the `Section` has no properties `editable` nor `value`; cause of the problem is that the section `tags` is built differently, see above
		// Add edit-icon to the value when editable
		var htmlEditable = section.editable === true ? build.editIcon("edit_tags") : "";

		// Note: In case of tags `section.value` already contains proper
		// HTML (with each tag wrapped into a `<span>`-element), because
		// `section.value` is the result of `build.renderTags`.
		return lychee.html(_templateObject30, section.title, section.title.toLowerCase(), section.value, htmlEditable);
	};

	var html = "";

	structure.forEach(function (section) {
		if (section.type === sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === sidebar.types.TAGS) html += renderTags(section);
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
		attribution: "&copy; <a href=\"https://osm.org/copyright\">" + lychee.locale["OSM_CONTRIBUTORS"] + "</a>"
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.de": {
		layer: "https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png ",
		attribution: "&copy; <a href=\"https://osm.org/copyright\">" + lychee.locale["OSM_CONTRIBUTORS"] + "</a>"
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.fr": {
		layer: "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ",
		attribution: "&copy; <a href=\"https://osm.org/copyright\">" + lychee.locale["OSM_CONTRIBUTORS"] + "</a>"
	},
	/**
  * @type {MapProvider}
  */
	RRZE: {
		layer: "https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png",
		attribution: "&copy; <a href=\"https://osm.org/copyright\">" + lychee.locale["OSM_CONTRIBUTORS"] + "</a>"
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
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'srcset="{url} 1x, {url2x} 2x" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + lychee.locale["CAMERA_DATE"] + '">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
		} else {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="' + lychee.locale["CAMERA_DATE"] + '">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
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
 * @typedef {Object.<string, string>} Locale
 * @property {function} printFilesizeLocalized
 * @property {function} printDateTime
 * @property {function} printMonthYear
 */

lychee.locale = {
	USERNAME: "Username",
	PASSWORD: "Password",
	ENTER: "Enter",
	CANCEL: "Cancel",
	SIGN_IN: "Sign In",
	CLOSE: "Close",
	SETTINGS: "Settings",
	SEARCH: "Search …",
	MORE: "More",
	DEFAULT: "Default",
	GALLERY: "Gallery",

	USERS: "Users",
	CREATE: "Create",
	REMOVE: "Remove",
	SHARE: "Share",
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
	CLEAR: "Clear",
	SIGN_OUT: "Sign Out",
	UPDATE_AVAILABLE: "Update available!",
	MIGRATION_AVAILABLE: "Migration available!",
	CHECK_FOR_UPDATE: "Check for updates",
	DEFAULT_LICENSE: "Default license for new uploads:",
	SET_LICENSE: "Set License",
	SET_OVERLAY_TYPE: "Set Overlay",
	SET_MAP_PROVIDER: "Set OpenStreetMap tiles provider",
	FULL_SETTINGS: "Full Settings",
	UPDATE: "Update",
	RESET: "Reset",
	DISABLE_TOKEN_TOOLTIP: "Disable",
	ENABLE_TOKEN: "Enable API token",
	DISABLED_TOKEN_STATUS_MSG: "Disabled",
	TOKEN_BUTTON: "API Token ...",
	TOKEN_NOT_AVAILABLE: "You have already viewed this token.",
	TOKEN_WAIT: "Wait ...",

	SMART_ALBUMS: "Smart albums",
	SHARED_ALBUMS: "Shared albums",
	ALBUMS: "Albums",
	PHOTOS: "Pictures",
	SEARCH_RESULTS: "Search results",

	RENAME: "Rename",
	RENAME_ALL: "Rename Selected",
	MERGE: "Merge",
	MERGE_ALL: "Merge Selected",
	MAKE_PUBLIC: "Make Public",
	SHARE_ALBUM: "Share Album",
	SHARE_PHOTO: "Share Photo",
	VISIBILITY_ALBUM: "Album Visibility",
	VISIBILITY_PHOTO: "Photo Visibility",
	DOWNLOAD_ALBUM: "Download Album",
	ABOUT_ALBUM: "About Album",
	DELETE_ALBUM: "Delete Album",
	MOVE_ALBUM: "Move Album",
	FULLSCREEN_ENTER: "Enter Fullscreen",
	FULLSCREEN_EXIT: "Exit Fullscreen",

	SHARING_ALBUM_USERS: "Share this album with users",
	WAIT_FETCH_DATA: "Please wait while we get the data …",
	SHARING_ALBUM_USERS_NO_USERS: "There are no users to share the album with",
	SHARING_ALBUM_USERS_LONG_MESSAGE: "Select the users to share this album with",

	DELETE_ALBUM_QUESTION: "Delete Album and Photos",
	KEEP_ALBUM: "Keep Album",
	DELETE_ALBUM_CONFIRMATION: "Are you sure you want to delete the album “%s” and all of the photos it contains? This action can’t be undone!",

	DELETE_TAG_ALBUM_QUESTION: "Delete Album",
	DELETE_TAG_ALBUM_CONFIRMATION: "Are you sure you want to delete the album “%s” (any photos inside will not be deleted)? This action can’t be undone!",

	DELETE_ALBUMS_QUESTION: "Delete Albums and Photos",
	KEEP_ALBUMS: "Keep Albums",
	DELETE_ALBUMS_CONFIRMATION: "Are you sure you want to delete all %d selected albums and all of the photos they contain? This action can’t be undone!",

	DELETE_UNSORTED_CONFIRM: "Are you sure you want to delete all photos from “Unsorted”? This action can’t be undone!",
	CLEAR_UNSORTED: "Clear Unsorted",
	KEEP_UNSORTED: "Keep Unsorted",

	EDIT_SHARING: "Edit Sharing",
	MAKE_PRIVATE: "Make Private",

	CLOSE_ALBUM: "Close Album",
	CLOSE_PHOTO: "Close Photo",
	CLOSE_MAP: "Close Map",

	ADD: "Add",
	MOVE: "Move",
	MOVE_ALL: "Move Selected",
	DUPLICATE: "Duplicate",
	DUPLICATE_ALL: "Duplicate Selected",
	COPY_TO: "Copy to …",
	COPY_ALL_TO: "Copy Selected to …",
	DELETE: "Delete",
	SAVE: "Save",
	DELETE_ALL: "Delete Selected",
	DOWNLOAD: "Download",
	DOWNLOAD_ALL: "Download Selected",
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
	TAG: "Tag",
	TAG_ALL: "Tag Selected",
	UNSTAR_PHOTO: "Unstar Photo",
	SET_COVER: "Set Album Cover",
	REMOVE_COVER: "Remove Album Cover",

	FULL_PHOTO: "Open Original",
	ABOUT_PHOTO: "About Photo",
	DISPLAY_FULL_MAP: "Map",
	DIRECT_LINK: "Direct Link",
	DIRECT_LINKS: "Direct Links",
	QR_CODE: "QR Code",

	ALBUM_ABOUT: "About",
	ALBUM_BASICS: "Basics",
	ALBUM_TITLE: "Title",
	ALBUM_NEW_TITLE: "Enter a new title for this album:",
	ALBUMS_NEW_TITLE: "Enter a title for all %d selected albums:",
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
	ALBUM_SUBALBUMS: "Subalbums",
	ALBUM_SHARING: "Share",
	ALBUM_SHR_YES: "YES",
	ALBUM_SHR_NO: "No",
	ALBUM_PUBLIC: "Public",
	ALBUM_PUBLIC_EXPL: "Anonymous users can access this album, subject to the restrictions below.",
	ALBUM_FULL: "Original",
	ALBUM_FULL_EXPL: "Anonymous users can behold full-resolution photos.",
	ALBUM_HIDDEN: "Hidden",
	ALBUM_HIDDEN_EXPL: "Anonymous users need a direct link to access this album.",
	ALBUM_MARK_NSFW: "Mark album as sensitive",
	ALBUM_UNMARK_NSFW: "Unmark album as sensitive",
	ALBUM_NSFW: "Sensitive",
	ALBUM_NSFW_EXPL: "Album contains sensitive content.",
	ALBUM_DOWNLOADABLE: "Downloadable",
	ALBUM_DOWNLOADABLE_EXPL: "Anonymous users can download this album.",
	ALBUM_SHARE_BUTTON_VISIBLE: "Share button is visible",
	ALBUM_SHARE_BUTTON_VISIBLE_EXPL: "Anonymous users can see social media sharing links.",
	ALBUM_PASSWORD: "Password",
	ALBUM_PASSWORD_PROT: "Password protected",
	ALBUM_PASSWORD_PROT_EXPL: "Anonymous users need a shared password to access this album.",
	ALBUM_PASSWORD_REQUIRED: "This album is protected by a password. Enter the password below to view the photos of this album:",
	ALBUM_MERGE: "Are you sure you want to merge the album “%1$s” into the album “%2$s”?",
	ALBUMS_MERGE: "Are you sure you want to merge all selected albums into the album “%s”?",
	MERGE_ALBUM: "Merge Albums",
	DONT_MERGE: "Don’t Merge",
	ALBUM_MOVE: "Are you sure you want to move the album “%1$s” into the album “%2$s”?",
	ALBUMS_MOVE: "Are you sure you want to move all selected albums into the album “%s”?",
	MOVE_ALBUMS: "Move Albums",
	NOT_MOVE_ALBUMS: "Don’t Move",
	ROOT: "Albums",
	ALBUM_REUSE: "Reuse",
	ALBUM_LICENSE: "License",
	ALBUM_SET_LICENSE: "Set License",
	ALBUM_LICENSE_HELP: "Need help choosing?",
	ALBUM_LICENSE_NONE: "None",
	ALBUM_RESERVED: "All Rights Reserved",
	ALBUM_SET_ORDER: "Set Order",
	ALBUM_ORDERING: "Order by",
	ALBUM_OWNER: "Owner",

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
	PHOTO_LICENSE: "License",
	PHOTO_LICENSE_HELP: "Need help choosing?",
	PHOTO_REUSE: "Reuse",
	PHOTO_LICENSE_NONE: "None",
	PHOTO_RESERVED: "All Rights Reserved",
	PHOTO_LATITUDE: "Latitude",
	PHOTO_LONGITUDE: "Longitude",
	PHOTO_ALTITUDE: "Altitude",
	PHOTO_IMGDIRECTION: "Direction",
	PHOTO_LOCATION: "Location",
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
	PHOTOS_NEW_TAGS: "Enter your tags for all %d selected photos. Existing tags will be overwritten. You can add multiple tags by separating them with a comma:",
	PHOTO_SET_TAGS: "Set Tags",
	TAGS_OVERRIDE_INFO: "If this is unchecked, the tags will be added to the existing tags of the photo.",
	PHOTO_CAMERA: "Camera",
	PHOTO_CAPTURED: "Captured",
	PHOTO_MAKE: "Make",
	PHOTO_TYPE: "Type/Model",
	PHOTO_LENS: "Lens",
	PHOTO_SHUTTER: "Shutter Speed",
	PHOTO_APERTURE: "Aperture",
	PHOTO_FOCAL: "Focal Length",
	PHOTO_ISO: "ISO %s",
	PHOTO_SHARING: "Sharing",
	PHOTO_SHR_PUBLIC: "Public",
	PHOTO_SHR_ALB: "Yes (Album)",
	PHOTO_SHR_PHT: "Yes (Photo)",
	PHOTO_SHR_NO: "No",
	PHOTO_DELETE: "Delete Photo",
	PHOTO_KEEP: "Keep Photo",
	PHOTO_DELETE_CONFIRMATION: "Are you sure you want to delete the photo “%s”? This action can’t be undone!",
	PHOTO_DELETE_ALL: "Are you sure you want to delete all %d selected photo? This action can’t be undone!",
	PHOTOS_NEW_TITLE: "Enter a title for all %d selected photos:",
	PHOTO_MAKE_PRIVATE_ALBUM: "This photo is located in a public album. To make this photo private or public, edit the visibility of the associated album.",
	PHOTO_SHOW_ALBUM: "Show Album",
	PHOTO_PUBLIC: "Public",
	PHOTO_PUBLIC_EXPL: "Anonymous users can view this photo, subject to the restrictions below.",
	PHOTO_FULL: "Original",
	PHOTO_FULL_EXPL: "Anonymous users can behold full-resolution photo.",
	PHOTO_HIDDEN: "Hidden",
	PHOTO_HIDDEN_EXPL: "Anonymous users need a direct link to view this photo.",
	PHOTO_DOWNLOADABLE: "Downloadable",
	PHOTO_DOWNLOADABLE_EXPL: "Anonymous users may download this photo.",
	PHOTO_SHARE_BUTTON_VISIBLE: "Share button is visible",
	PHOTO_SHARE_BUTTON_VISIBLE_EXPL: "Anonymous users can see social media sharing links.",
	PHOTO_PASSWORD_PROT: "Password protected",
	PHOTO_PASSWORD_PROT_EXPL: "Anonymous users need a shared password to view this photo.",
	PHOTO_EDIT_SHARING_TEXT: "The sharing properties of this photo will be changed to the following:",
	PHOTO_NO_EDIT_SHARING_TEXT: "Because this photo is located in a public album, it inherits that album’s visibility settings.  Its current visibility is shown below for informational purposes only.",
	PHOTO_EDIT_GLOBAL_SHARING_TEXT: "The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.",
	PHOTO_NEW_CREATED_AT: "Enter the upload date for this photo. mm/dd/yyyy, hh:mm [am/pm]",
	PHOTO_SET_CREATED_AT: "Set upload date",

	LOADING: "Loading",
	ERROR: "Error",
	ERROR_TEXT: "Whoops, it looks like something went wrong. Please reload the site and try again!",
	ERROR_UNKNOWN: "Something unexpected happened. Please try again and check your installation and server. Take a look at the readme for more information.",
	ERROR_LOGIN: "Unable to save login. Please try again with another username and password!",
	ERROR_MAP_DEACTIVATED: "Map functionality has been deactivated under settings.",
	ERROR_SEARCH_DEACTIVATED: "Search functionality has been deactivated under settings.",
	SUCCESS: "OK",
	RETRY: "Retry",
	OVERRIDE: "Override",

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
	SETTINGS_SUCCESS_CSS: "CSS updated",
	SETTINGS_SUCCESS_UPDATE: "Settings updated successfully",
	SETTINGS_DROPBOX_KEY: "Dropbox API Key",
	SETTINGS_ADVANCED_WARNING_EXPL: "Changing these advanced settings can be harmful to the stability, security and performance of this application. You should only modify them if you are sure of what you are doing.",
	SETTINGS_ADVANCED_SAVE: "Save my modifications, I accept the risk!",

	U2F_NOT_SUPPORTED: "U2F not supported. Sorry.",
	U2F_NOT_SECURE: "Environment not secured. U2F not available.",
	U2F_REGISTER_KEY: "Register new device.",
	U2F_REGISTRATION_SUCCESS: "Registration successful!",
	U2F_AUTHENTIFICATION_SUCCESS: "Authentication successful!",
	U2F_CREDENTIALS: "Credentials",
	U2F_CREDENTIALS_DELETED: "Credentials deleted!",

	NEW_PHOTOS_NOTIFICATION: "Send new photos notification emails.",
	SETTINGS_SUCCESS_NEW_PHOTOS_NOTIFICATION: "New photos notification updated",
	USER_EMAIL_INSTRUCTION: "Add your email below to enable receiving email notifications. To stop receiving emails, simply remove your email below.",

	LOGIN_TITLE: "Enter a username and password for your installation:",
	LOGIN_USERNAME: "New Username",
	LOGIN_PASSWORD: "New Password",
	LOGIN_PASSWORD_CONFIRM: "Confirm Password",
	LOGIN_CREATE: "Create Login",

	PASSWORD_TITLE: "Enter your current password:",
	PASSWORD_CURRENT: "Current Password",
	PASSWORD_TEXT: "Your username and password will be changed to the following:",
	PASSWORD_CHANGE: "Change Login",

	EDIT_SHARING_TITLE: "Edit Sharing",
	EDIT_SHARING_TEXT: "The sharing properties of this album will be changed to the following:",
	SHARE_ALBUM_TEXT: "This album will be shared with the following properties:",

	SORT_DIALOG_ATTRIBUTE_LABEL: "Attribute",
	SORT_DIALOG_ORDER_LABEL: "Order",

	SORT_ALBUM_BY: "Sort albums by %1$s in an %2$s order.",

	SORT_ALBUM_SELECT_1: "Creation Time",
	SORT_ALBUM_SELECT_2: "Title",
	SORT_ALBUM_SELECT_3: "Description",
	SORT_ALBUM_SELECT_4: "Public",
	SORT_ALBUM_SELECT_5: "Latest Take Date",
	SORT_ALBUM_SELECT_6: "Oldest Take Date",

	SORT_PHOTO_BY: "Sort photos by %1$s in an %2$s order.",

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

	CSS_TEXT: "Personalize CSS:",
	CSS_TITLE: "Change CSS",
	PUBLIC_SEARCH_TEXT: "Public search allowed:",
	OVERLAY_TYPE: "Photo overlay:",
	OVERLAY_NONE: "None",
	OVERLAY_EXIF: "EXIF data",
	OVERLAY_DESCRIPTION: "Description",
	OVERLAY_DATE: "Date taken",
	MAP_DISPLAY_TEXT: "Enable maps (provided by OpenStreetMap):",
	MAP_DISPLAY_PUBLIC_TEXT: "Enable maps for public albums (provided by OpenStreetMap):",
	MAP_PROVIDER: "Provider of OpenStreetMap tiles:",
	MAP_PROVIDER_WIKIMEDIA: "Wikimedia",
	MAP_PROVIDER_OSM_ORG: "OpenStreetMap.org (no HiDPI)",
	MAP_PROVIDER_OSM_DE: "OpenStreetMap.de (no HiDPI)",
	MAP_PROVIDER_OSM_FR: "OpenStreetMap.fr (no HiDPI)",
	MAP_PROVIDER_RRZE: "University of Erlangen, Germany (only HiDPI)",
	MAP_INCLUDE_SUBALBUMS_TEXT: "Include photos of subalbums on map:",
	LOCATION_DECODING: "Decode GPS data into location name",
	LOCATION_SHOW: "Show location name",
	LOCATION_SHOW_PUBLIC: "Show location name for public mode",

	LAYOUT_TYPE: "Layout of photos:",
	LAYOUT_SQUARES: "Square thumbnails",
	LAYOUT_JUSTIFIED: "With aspect, justified",
	LAYOUT_UNJUSTIFIED: "With aspect, unjustified",
	SET_LAYOUT: "Change layout",

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
	UPLOAD_FAILED_ERROR: "Upload failed. The server returned an error!",
	UPLOAD_FAILED_WARNING: "Upload failed. The server returned a warning!",
	UPLOAD_CANCELLED: "Cancelled",
	UPLOAD_SKIPPED: "Skipped",
	UPLOAD_UPDATED: "Updated",
	UPLOAD_GENERAL: "General",
	UPLOAD_IMPORT_SKIPPED_DUPLICATE: "This photo has been skipped because it’s already in your library.",
	UPLOAD_IMPORT_RESYNCED_DUPLICATE: "This photo has been skipped because it’s already in your library, but its metadata has been updated.",
	UPLOAD_ERROR_CONSOLE: "Please take a look at the console of your browser for further details.",
	UPLOAD_UNKNOWN: "Server returned an unknown response. Please take a look at the console of your browser for further details.",
	UPLOAD_ERROR_UNKNOWN: "Upload failed. The server returned an unknown error!",
	UPLOAD_ERROR_POSTSIZE: "Upload failed. The PHP post_max_size may be too small! Otherwise check the FAQ.",
	UPLOAD_ERROR_FILESIZE: "Upload failed. The PHP upload_max_filesize may be too small! Otherwise check the FAQ.",
	UPLOAD_IN_PROGRESS: "Lychee is currently uploading!",
	UPLOAD_IMPORT_WARN_ERR: "The import has been finished, but returned warnings or errors. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_COMPLETE: "Import complete",
	UPLOAD_IMPORT_INSTR: "Please enter the direct link to a photo to import it:",
	UPLOAD_IMPORT: "Import",
	UPLOAD_IMPORT_SERVER: "Importing from server",
	UPLOAD_IMPORT_SERVER_FOLD: "Folder empty or no readable files to process. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_SERVER_INSTR: "Import all photos, folders, and sub-folders located in the folders with the following absolute paths (on the server). Paths are space-separated, use \\ to escape a space in a path.",
	UPLOAD_ABSOLUTE_PATH: "Absolute path to directories, space separated",
	UPLOAD_IMPORT_SERVER_EMPT: "Could not start import because the folder was empty!",
	UPLOAD_IMPORT_DELETE_ORIGINALS: "Delete originals",
	UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL: "Original files will be deleted after the import when possible.",
	UPLOAD_IMPORT_VIA_SYMLINK: "Symbolic links",
	UPLOAD_IMPORT_VIA_SYMLINK_EXPL: "Import files using symbolic links to originals.",
	UPLOAD_IMPORT_SKIP_DUPLICATES: "Skip duplicates",
	UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL: "Existing media files are skipped.",
	UPLOAD_IMPORT_RESYNC_METADATA: "Re-sync metadata",
	UPLOAD_IMPORT_RESYNC_METADATA_EXPL: "Update metadata of existing media files.",
	UPLOAD_IMPORT_LOW_MEMORY_EXPL: "The import process on the server is approaching the memory limit and may end up being terminated prematurely.",
	UPLOAD_WARNING: "Warning",
	UPLOAD_IMPORT_NOT_A_DIRECTORY: "The given path is not a readable directory!",
	UPLOAD_IMPORT_PATH_RESERVED: "The given path is a reserved path of Lychee!",
	UPLOAD_IMPORT_FAILED: "Could not import the file!",
	UPLOAD_IMPORT_UNSUPPORTED: "Unsupported file type!",
	UPLOAD_IMPORT_CANCELLED: "Import cancelled",

	ABOUT_SUBTITLE: "Self-hosted photo-management done right",
	ABOUT_DESCRIPTION: "<a target='_blank' href='%s'>Lychee</a> is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely.",
	FOOTER_COPYRIGHT: "All images on this website are subject to copyright by %1$s &copy; %2$s",
	HOSTED_WITH_LYCHEE: "Hosted with Lychee",

	URL_COPY_TO_CLIPBOARD: "Copy to clipboard",
	URL_COPIED_TO_CLIPBOARD: "Copied URL to clipboard!",
	PHOTO_DIRECT_LINKS_TO_IMAGES: "Direct links to image files:",
	PHOTO_MEDIUM: "Medium",
	PHOTO_MEDIUM_HIDPI: "Medium HiDPI",
	PHOTO_SMALL: "Thumb",
	PHOTO_SMALL_HIDPI: "Thumb HiDPI",
	PHOTO_THUMB: "Square thumb",
	PHOTO_THUMB_HIDPI: "Square thumb HiDPI",
	PHOTO_THUMBNAIL: "Photo thumbnail",
	PHOTO_LIVE_VIDEO: "Video part of live-photo",
	PHOTO_VIEW: "Lychee Photo View:",

	PHOTO_EDIT_ROTATECWISE: "Rotate clockwise",
	PHOTO_EDIT_ROTATECCWISE: "Rotate counter-clockwise",

	ERROR_GPX: "Error loading GPX file: ",
	ERROR_EITHER_ALBUMS_OR_PHOTOS: "Please select either albums or photos!",
	ERROR_COULD_NOT_FIND: "Could not find what you want.",
	ERROR_INVALID_EMAIL: "Not a valid email address.",
	EMAIL_SUCCESS: "Email updated!",
	ERROR_PHOTO_NOT_FOUND: "Error: photo %s not found!",
	ERROR_EMPTY_USERNAME: "new username cannot be empty.",
	ERROR_PASSWORD_DOES_NOT_MATCH: "new password does not match.",
	ERROR_EMPTY_PASSWORD: "new password cannot be empty.",
	ERROR_SELECT_ALBUM: "Select an album to share!",
	ERROR_SELECT_USER: "Select a user to share with!",
	ERROR_SELECT_SHARING: "Select a sharing to remove!",
	SHARING_SUCCESS: "Sharing updated!",
	SHARING_REMOVED: "Sharing removed!",
	USER_CREATED: "User created!",
	USER_DELETED: "User deleted!",
	USER_UPDATED: "User updated!",
	ENTER_EMAIL: "Enter your email address:",
	ERROR_ALBUM_JSON_NOT_FOUND: "Error: Album JSON not found!",
	ERROR_ALBUM_NOT_FOUND: "Error: album %s not found",
	ERROR_DROPBOX_KEY: "Error: Dropbox key not set",
	ERROR_SESSION: "Session expired.",
	CAMERA_DATE: "Camera date",
	NEW_PASSWORD: "new password",
	ALLOW_UPLOADS: "Allow uploads",
	RESTRICTED_ACCOUNT: "Restricted account",
	OSM_CONTRIBUTORS: "OpenStreetMap contributors",

	dateTimeFormatter: new Intl.DateTimeFormat("default", { dateStyle: "medium", timeStyle: "medium" }),

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
		var result = lychee.locale.dateTimeFormatter.format(new Date(splitDateTime[1]));
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
 * @property {Photo[]} [photos]
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
 * @property {boolean} has_token
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
 * @property {?User} user
 * @property {{is_admin: boolean, is_locked: boolean, may_upload: boolean}} rights
 * @property {number} update_json - version number of latest available update
 * @property {boolean} update_available
 * @property {Object.<string, string>} locale
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
 * @property {string}   version                 - a string of 6 digits without separating dots, i.e. version 4.6.3 is reported as `'040603'`
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