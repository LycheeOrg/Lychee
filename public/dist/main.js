/*! jQuery v3.4.1 | (c) JS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(C,e){"use strict";var t=[],E=C.document,r=Object.getPrototypeOf,s=t.slice,g=t.concat,u=t.push,i=t.indexOf,n={},o=n.toString,v=n.hasOwnProperty,a=v.toString,l=a.call(Object),y={},m=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType},x=function(e){return null!=e&&e===e.window},c={type:!0,src:!0,nonce:!0,noModule:!0};function b(e,t,n){var r,i,o=(n=n||E).createElement("script");if(o.text=e,t)for(r in c)(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function w(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?n[o.call(e)]||"object":typeof e}var f="3.4.1",k=function(e,t){return new k.fn.init(e,t)},p=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;function d(e){var t=!!e&&"length"in e&&e.length,n=w(e);return!m(e)&&!x(e)&&("array"===n||0===t||"number"==typeof t&&0<t&&t-1 in e)}k.fn=k.prototype={jquery:f,constructor:k,length:0,toArray:function(){return s.call(this)},get:function(e){return null==e?s.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=k.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return k.each(this,e)},map:function(n){return this.pushStack(k.map(this,function(e,t){return n.call(e,t,e)}))},slice:function(){return this.pushStack(s.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(0<=n&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:u,sort:t.sort,splice:t.splice},k.extend=k.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||m(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(k.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||k.isPlainObject(n)?n:{},i=!1,a[t]=k.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},k.extend({expando:"jQuery"+(f+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==o.call(e))&&(!(t=r(e))||"function"==typeof(n=v.call(t,"constructor")&&t.constructor)&&a.call(n)===l)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t){b(e,{nonce:t&&t.nonce})},each:function(e,t){var n,r=0;if(d(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},trim:function(e){return null==e?"":(e+"").replace(p,"")},makeArray:function(e,t){var n=t||[];return null!=e&&(d(Object(e))?k.merge(n,"string"==typeof e?[e]:e):u.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:i.call(t,e,n)},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},map:function(e,t,n){var r,i,o=0,a=[];if(d(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&a.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&a.push(i);return g.apply([],a)},guid:1,support:y}),"function"==typeof Symbol&&(k.fn[Symbol.iterator]=t[Symbol.iterator]),k.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){n["[object "+t+"]"]=t.toLowerCase()});var h=function(n){var e,d,b,o,i,h,f,g,w,u,l,T,C,a,E,v,s,c,y,k="sizzle"+1*new Date,m=n.document,S=0,r=0,p=ue(),x=ue(),N=ue(),A=ue(),D=function(e,t){return e===t&&(l=!0),0},j={}.hasOwnProperty,t=[],q=t.pop,L=t.push,H=t.push,O=t.slice,P=function(e,t){for(var n=0,r=e.length;n<r;n++)if(e[n]===t)return n;return-1},R="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",M="[\\x20\\t\\r\\n\\f]",I="(?:\\\\.|[\\w-]|[^\0-\\xa0])+",W="\\["+M+"*("+I+")(?:"+M+"*([*^$|!~]?=)"+M+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+I+"))|)"+M+"*\\]",$=":("+I+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+W+")*)|.*)\\)|)",F=new RegExp(M+"+","g"),B=new RegExp("^"+M+"+|((?:^|[^\\\\])(?:\\\\.)*)"+M+"+$","g"),_=new RegExp("^"+M+"*,"+M+"*"),z=new RegExp("^"+M+"*([>+~]|"+M+")"+M+"*"),U=new RegExp(M+"|>"),X=new RegExp($),V=new RegExp("^"+I+"$"),G={ID:new RegExp("^#("+I+")"),CLASS:new RegExp("^\\.("+I+")"),TAG:new RegExp("^("+I+"|[*])"),ATTR:new RegExp("^"+W),PSEUDO:new RegExp("^"+$),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+M+"*(even|odd|(([+-]|)(\\d*)n|)"+M+"*(?:([+-]|)"+M+"*(\\d+)|))"+M+"*\\)|)","i"),bool:new RegExp("^(?:"+R+")$","i"),needsContext:new RegExp("^"+M+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+M+"*((?:-\\d)?\\d*)"+M+"*\\)|)(?=[^-]|$)","i")},Y=/HTML$/i,Q=/^(?:input|select|textarea|button)$/i,J=/^h\d$/i,K=/^[^{]+\{\s*\[native \w/,Z=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,ee=/[+~]/,te=new RegExp("\\\\([\\da-f]{1,6}"+M+"?|("+M+")|.)","ig"),ne=function(e,t,n){var r="0x"+t-65536;return r!=r||n?t:r<0?String.fromCharCode(r+65536):String.fromCharCode(r>>10|55296,1023&r|56320)},re=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,ie=function(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e},oe=function(){T()},ae=be(function(e){return!0===e.disabled&&"fieldset"===e.nodeName.toLowerCase()},{dir:"parentNode",next:"legend"});try{H.apply(t=O.call(m.childNodes),m.childNodes),t[m.childNodes.length].nodeType}catch(e){H={apply:t.length?function(e,t){L.apply(e,O.call(t))}:function(e,t){var n=e.length,r=0;while(e[n++]=t[r++]);e.length=n-1}}}function se(t,e,n,r){var i,o,a,s,u,l,c,f=e&&e.ownerDocument,p=e?e.nodeType:9;if(n=n||[],"string"!=typeof t||!t||1!==p&&9!==p&&11!==p)return n;if(!r&&((e?e.ownerDocument||e:m)!==C&&T(e),e=e||C,E)){if(11!==p&&(u=Z.exec(t)))if(i=u[1]){if(9===p){if(!(a=e.getElementById(i)))return n;if(a.id===i)return n.push(a),n}else if(f&&(a=f.getElementById(i))&&y(e,a)&&a.id===i)return n.push(a),n}else{if(u[2])return H.apply(n,e.getElementsByTagName(t)),n;if((i=u[3])&&d.getElementsByClassName&&e.getElementsByClassName)return H.apply(n,e.getElementsByClassName(i)),n}if(d.qsa&&!A[t+" "]&&(!v||!v.test(t))&&(1!==p||"object"!==e.nodeName.toLowerCase())){if(c=t,f=e,1===p&&U.test(t)){(s=e.getAttribute("id"))?s=s.replace(re,ie):e.setAttribute("id",s=k),o=(l=h(t)).length;while(o--)l[o]="#"+s+" "+xe(l[o]);c=l.join(","),f=ee.test(t)&&ye(e.parentNode)||e}try{return H.apply(n,f.querySelectorAll(c)),n}catch(e){A(t,!0)}finally{s===k&&e.removeAttribute("id")}}}return g(t.replace(B,"$1"),e,n,r)}function ue(){var r=[];return function e(t,n){return r.push(t+" ")>b.cacheLength&&delete e[r.shift()],e[t+" "]=n}}function le(e){return e[k]=!0,e}function ce(e){var t=C.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function fe(e,t){var n=e.split("|"),r=n.length;while(r--)b.attrHandle[n[r]]=t}function pe(e,t){var n=t&&e,r=n&&1===e.nodeType&&1===t.nodeType&&e.sourceIndex-t.sourceIndex;if(r)return r;if(n)while(n=n.nextSibling)if(n===t)return-1;return e?1:-1}function de(t){return function(e){return"input"===e.nodeName.toLowerCase()&&e.type===t}}function he(n){return function(e){var t=e.nodeName.toLowerCase();return("input"===t||"button"===t)&&e.type===n}}function ge(t){return function(e){return"form"in e?e.parentNode&&!1===e.disabled?"label"in e?"label"in e.parentNode?e.parentNode.disabled===t:e.disabled===t:e.isDisabled===t||e.isDisabled!==!t&&ae(e)===t:e.disabled===t:"label"in e&&e.disabled===t}}function ve(a){return le(function(o){return o=+o,le(function(e,t){var n,r=a([],e.length,o),i=r.length;while(i--)e[n=r[i]]&&(e[n]=!(t[n]=e[n]))})})}function ye(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}for(e in d=se.support={},i=se.isXML=function(e){var t=e.namespaceURI,n=(e.ownerDocument||e).documentElement;return!Y.test(t||n&&n.nodeName||"HTML")},T=se.setDocument=function(e){var t,n,r=e?e.ownerDocument||e:m;return r!==C&&9===r.nodeType&&r.documentElement&&(a=(C=r).documentElement,E=!i(C),m!==C&&(n=C.defaultView)&&n.top!==n&&(n.addEventListener?n.addEventListener("unload",oe,!1):n.attachEvent&&n.attachEvent("onunload",oe)),d.attributes=ce(function(e){return e.className="i",!e.getAttribute("className")}),d.getElementsByTagName=ce(function(e){return e.appendChild(C.createComment("")),!e.getElementsByTagName("*").length}),d.getElementsByClassName=K.test(C.getElementsByClassName),d.getById=ce(function(e){return a.appendChild(e).id=k,!C.getElementsByName||!C.getElementsByName(k).length}),d.getById?(b.filter.ID=function(e){var t=e.replace(te,ne);return function(e){return e.getAttribute("id")===t}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n=t.getElementById(e);return n?[n]:[]}}):(b.filter.ID=function(e){var n=e.replace(te,ne);return function(e){var t="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return t&&t.value===n}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&E){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),b.find.TAG=d.getElementsByTagName?function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):d.qsa?t.querySelectorAll(e):void 0}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){while(n=o[i++])1===n.nodeType&&r.push(n);return r}return o},b.find.CLASS=d.getElementsByClassName&&function(e,t){if("undefined"!=typeof t.getElementsByClassName&&E)return t.getElementsByClassName(e)},s=[],v=[],(d.qsa=K.test(C.querySelectorAll))&&(ce(function(e){a.appendChild(e).innerHTML="<a id='"+k+"'></a><select id='"+k+"-\r\\' msallowcapture=''><option selected=''></option></select>",e.querySelectorAll("[msallowcapture^='']").length&&v.push("[*^$]="+M+"*(?:''|\"\")"),e.querySelectorAll("[selected]").length||v.push("\\["+M+"*(?:value|"+R+")"),e.querySelectorAll("[id~="+k+"-]").length||v.push("~="),e.querySelectorAll(":checked").length||v.push(":checked"),e.querySelectorAll("a#"+k+"+*").length||v.push(".#.+[+~]")}),ce(function(e){e.innerHTML="<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>";var t=C.createElement("input");t.setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),e.querySelectorAll("[name=d]").length&&v.push("name"+M+"*[*^$|!~]?="),2!==e.querySelectorAll(":enabled").length&&v.push(":enabled",":disabled"),a.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&v.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),v.push(",.*:")})),(d.matchesSelector=K.test(c=a.matches||a.webkitMatchesSelector||a.mozMatchesSelector||a.oMatchesSelector||a.msMatchesSelector))&&ce(function(e){d.disconnectedMatch=c.call(e,"*"),c.call(e,"[s!='']:x"),s.push("!=",$)}),v=v.length&&new RegExp(v.join("|")),s=s.length&&new RegExp(s.join("|")),t=K.test(a.compareDocumentPosition),y=t||K.test(a.contains)?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)while(t=t.parentNode)if(t===e)return!0;return!1},D=t?function(e,t){if(e===t)return l=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)===(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!d.sortDetached&&t.compareDocumentPosition(e)===n?e===C||e.ownerDocument===m&&y(m,e)?-1:t===C||t.ownerDocument===m&&y(m,t)?1:u?P(u,e)-P(u,t):0:4&n?-1:1)}:function(e,t){if(e===t)return l=!0,0;var n,r=0,i=e.parentNode,o=t.parentNode,a=[e],s=[t];if(!i||!o)return e===C?-1:t===C?1:i?-1:o?1:u?P(u,e)-P(u,t):0;if(i===o)return pe(e,t);n=e;while(n=n.parentNode)a.unshift(n);n=t;while(n=n.parentNode)s.unshift(n);while(a[r]===s[r])r++;return r?pe(a[r],s[r]):a[r]===m?-1:s[r]===m?1:0}),C},se.matches=function(e,t){return se(e,null,null,t)},se.matchesSelector=function(e,t){if((e.ownerDocument||e)!==C&&T(e),d.matchesSelector&&E&&!A[t+" "]&&(!s||!s.test(t))&&(!v||!v.test(t)))try{var n=c.call(e,t);if(n||d.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){A(t,!0)}return 0<se(t,C,null,[e]).length},se.contains=function(e,t){return(e.ownerDocument||e)!==C&&T(e),y(e,t)},se.attr=function(e,t){(e.ownerDocument||e)!==C&&T(e);var n=b.attrHandle[t.toLowerCase()],r=n&&j.call(b.attrHandle,t.toLowerCase())?n(e,t,!E):void 0;return void 0!==r?r:d.attributes||!E?e.getAttribute(t):(r=e.getAttributeNode(t))&&r.specified?r.value:null},se.escape=function(e){return(e+"").replace(re,ie)},se.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},se.uniqueSort=function(e){var t,n=[],r=0,i=0;if(l=!d.detectDuplicates,u=!d.sortStable&&e.slice(0),e.sort(D),l){while(t=e[i++])t===e[i]&&(r=n.push(i));while(r--)e.splice(n[r],1)}return u=null,e},o=se.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=o(e)}else if(3===i||4===i)return e.nodeValue}else while(t=e[r++])n+=o(t);return n},(b=se.selectors={cacheLength:50,createPseudo:le,match:G,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(te,ne),e[3]=(e[3]||e[4]||e[5]||"").replace(te,ne),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||se.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&se.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return G.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&X.test(n)&&(t=h(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(te,ne).toLowerCase();return"*"===e?function(){return!0}:function(e){return e.nodeName&&e.nodeName.toLowerCase()===t}},CLASS:function(e){var t=p[e+" "];return t||(t=new RegExp("(^|"+M+")"+e+"("+M+"|$)"))&&p(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(n,r,i){return function(e){var t=se.attr(e,n);return null==t?"!="===r:!r||(t+="","="===r?t===i:"!="===r?t!==i:"^="===r?i&&0===t.indexOf(i):"*="===r?i&&-1<t.indexOf(i):"$="===r?i&&t.slice(-i.length)===i:"~="===r?-1<(" "+t.replace(F," ")+" ").indexOf(i):"|="===r&&(t===i||t.slice(0,i.length+1)===i+"-"))}},CHILD:function(h,e,t,g,v){var y="nth"!==h.slice(0,3),m="last"!==h.slice(-4),x="of-type"===e;return 1===g&&0===v?function(e){return!!e.parentNode}:function(e,t,n){var r,i,o,a,s,u,l=y!==m?"nextSibling":"previousSibling",c=e.parentNode,f=x&&e.nodeName.toLowerCase(),p=!n&&!x,d=!1;if(c){if(y){while(l){a=e;while(a=a[l])if(x?a.nodeName.toLowerCase()===f:1===a.nodeType)return!1;u=l="only"===h&&!u&&"nextSibling"}return!0}if(u=[m?c.firstChild:c.lastChild],m&&p){d=(s=(r=(i=(o=(a=c)[k]||(a[k]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===S&&r[1])&&r[2],a=s&&c.childNodes[s];while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if(1===a.nodeType&&++d&&a===e){i[h]=[S,s,d];break}}else if(p&&(d=s=(r=(i=(o=(a=e)[k]||(a[k]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]||[])[0]===S&&r[1]),!1===d)while(a=++s&&a&&a[l]||(d=s=0)||u.pop())if((x?a.nodeName.toLowerCase()===f:1===a.nodeType)&&++d&&(p&&((i=(o=a[k]||(a[k]={}))[a.uniqueID]||(o[a.uniqueID]={}))[h]=[S,d]),a===e))break;return(d-=v)===g||d%g==0&&0<=d/g}}},PSEUDO:function(e,o){var t,a=b.pseudos[e]||b.setFilters[e.toLowerCase()]||se.error("unsupported pseudo: "+e);return a[k]?a(o):1<a.length?(t=[e,e,"",o],b.setFilters.hasOwnProperty(e.toLowerCase())?le(function(e,t){var n,r=a(e,o),i=r.length;while(i--)e[n=P(e,r[i])]=!(t[n]=r[i])}):function(e){return a(e,0,t)}):a}},pseudos:{not:le(function(e){var r=[],i=[],s=f(e.replace(B,"$1"));return s[k]?le(function(e,t,n,r){var i,o=s(e,null,r,[]),a=e.length;while(a--)(i=o[a])&&(e[a]=!(t[a]=i))}):function(e,t,n){return r[0]=e,s(r,null,n,i),r[0]=null,!i.pop()}}),has:le(function(t){return function(e){return 0<se(t,e).length}}),contains:le(function(t){return t=t.replace(te,ne),function(e){return-1<(e.textContent||o(e)).indexOf(t)}}),lang:le(function(n){return V.test(n||"")||se.error("unsupported lang: "+n),n=n.replace(te,ne).toLowerCase(),function(e){var t;do{if(t=E?e.lang:e.getAttribute("xml:lang")||e.getAttribute("lang"))return(t=t.toLowerCase())===n||0===t.indexOf(n+"-")}while((e=e.parentNode)&&1===e.nodeType);return!1}}),target:function(e){var t=n.location&&n.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===a},focus:function(e){return e===C.activeElement&&(!C.hasFocus||C.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:ge(!1),disabled:ge(!0),checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!b.pseudos.empty(e)},header:function(e){return J.test(e.nodeName)},input:function(e){return Q.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:ve(function(){return[0]}),last:ve(function(e,t){return[t-1]}),eq:ve(function(e,t,n){return[n<0?n+t:n]}),even:ve(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:ve(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:ve(function(e,t,n){for(var r=n<0?n+t:t<n?t:n;0<=--r;)e.push(r);return e}),gt:ve(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=b.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})b.pseudos[e]=de(e);for(e in{submit:!0,reset:!0})b.pseudos[e]=he(e);function me(){}function xe(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function be(s,e,t){var u=e.dir,l=e.next,c=l||u,f=t&&"parentNode"===c,p=r++;return e.first?function(e,t,n){while(e=e[u])if(1===e.nodeType||f)return s(e,t,n);return!1}:function(e,t,n){var r,i,o,a=[S,p];if(n){while(e=e[u])if((1===e.nodeType||f)&&s(e,t,n))return!0}else while(e=e[u])if(1===e.nodeType||f)if(i=(o=e[k]||(e[k]={}))[e.uniqueID]||(o[e.uniqueID]={}),l&&l===e.nodeName.toLowerCase())e=e[u]||e;else{if((r=i[c])&&r[0]===S&&r[1]===p)return a[2]=r[2];if((i[c]=a)[2]=s(e,t,n))return!0}return!1}}function we(i){return 1<i.length?function(e,t,n){var r=i.length;while(r--)if(!i[r](e,t,n))return!1;return!0}:i[0]}function Te(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function Ce(d,h,g,v,y,e){return v&&!v[k]&&(v=Ce(v)),y&&!y[k]&&(y=Ce(y,e)),le(function(e,t,n,r){var i,o,a,s=[],u=[],l=t.length,c=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)se(e,t[r],n);return n}(h||"*",n.nodeType?[n]:n,[]),f=!d||!e&&h?c:Te(c,s,d,n,r),p=g?y||(e?d:l||v)?[]:t:f;if(g&&g(f,p,n,r),v){i=Te(p,u),v(i,[],n,r),o=i.length;while(o--)(a=i[o])&&(p[u[o]]=!(f[u[o]]=a))}if(e){if(y||d){if(y){i=[],o=p.length;while(o--)(a=p[o])&&i.push(f[o]=a);y(null,p=[],i,r)}o=p.length;while(o--)(a=p[o])&&-1<(i=y?P(e,a):s[o])&&(e[i]=!(t[i]=a))}}else p=Te(p===t?p.splice(l,p.length):p),y?y(null,t,p,r):H.apply(t,p)})}function Ee(e){for(var i,t,n,r=e.length,o=b.relative[e[0].type],a=o||b.relative[" "],s=o?1:0,u=be(function(e){return e===i},a,!0),l=be(function(e){return-1<P(i,e)},a,!0),c=[function(e,t,n){var r=!o&&(n||t!==w)||((i=t).nodeType?u(e,t,n):l(e,t,n));return i=null,r}];s<r;s++)if(t=b.relative[e[s].type])c=[be(we(c),t)];else{if((t=b.filter[e[s].type].apply(null,e[s].matches))[k]){for(n=++s;n<r;n++)if(b.relative[e[n].type])break;return Ce(1<s&&we(c),1<s&&xe(e.slice(0,s-1).concat({value:" "===e[s-2].type?"*":""})).replace(B,"$1"),t,s<n&&Ee(e.slice(s,n)),n<r&&Ee(e=e.slice(n)),n<r&&xe(e))}c.push(t)}return we(c)}return me.prototype=b.filters=b.pseudos,b.setFilters=new me,h=se.tokenize=function(e,t){var n,r,i,o,a,s,u,l=x[e+" "];if(l)return t?0:l.slice(0);a=e,s=[],u=b.preFilter;while(a){for(o in n&&!(r=_.exec(a))||(r&&(a=a.slice(r[0].length)||a),s.push(i=[])),n=!1,(r=z.exec(a))&&(n=r.shift(),i.push({value:n,type:r[0].replace(B," ")}),a=a.slice(n.length)),b.filter)!(r=G[o].exec(a))||u[o]&&!(r=u[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?se.error(e):x(e,s).slice(0)},f=se.compile=function(e,t){var n,v,y,m,x,r,i=[],o=[],a=N[e+" "];if(!a){t||(t=h(e)),n=t.length;while(n--)(a=Ee(t[n]))[k]?i.push(a):o.push(a);(a=N(e,(v=o,m=0<(y=i).length,x=0<v.length,r=function(e,t,n,r,i){var o,a,s,u=0,l="0",c=e&&[],f=[],p=w,d=e||x&&b.find.TAG("*",i),h=S+=null==p?1:Math.random()||.1,g=d.length;for(i&&(w=t===C||t||i);l!==g&&null!=(o=d[l]);l++){if(x&&o){a=0,t||o.ownerDocument===C||(T(o),n=!E);while(s=v[a++])if(s(o,t||C,n)){r.push(o);break}i&&(S=h)}m&&((o=!s&&o)&&u--,e&&c.push(o))}if(u+=l,m&&l!==u){a=0;while(s=y[a++])s(c,f,t,n);if(e){if(0<u)while(l--)c[l]||f[l]||(f[l]=q.call(r));f=Te(f)}H.apply(r,f),i&&!e&&0<f.length&&1<u+y.length&&se.uniqueSort(r)}return i&&(S=h,w=p),c},m?le(r):r))).selector=e}return a},g=se.select=function(e,t,n,r){var i,o,a,s,u,l="function"==typeof e&&e,c=!r&&h(e=l.selector||e);if(n=n||[],1===c.length){if(2<(o=c[0]=c[0].slice(0)).length&&"ID"===(a=o[0]).type&&9===t.nodeType&&E&&b.relative[o[1].type]){if(!(t=(b.find.ID(a.matches[0].replace(te,ne),t)||[])[0]))return n;l&&(t=t.parentNode),e=e.slice(o.shift().value.length)}i=G.needsContext.test(e)?0:o.length;while(i--){if(a=o[i],b.relative[s=a.type])break;if((u=b.find[s])&&(r=u(a.matches[0].replace(te,ne),ee.test(o[0].type)&&ye(t.parentNode)||t))){if(o.splice(i,1),!(e=r.length&&xe(o)))return H.apply(n,r),n;break}}}return(l||f(e,c))(r,t,!E,n,!t||ee.test(e)&&ye(t.parentNode)||t),n},d.sortStable=k.split("").sort(D).join("")===k,d.detectDuplicates=!!l,T(),d.sortDetached=ce(function(e){return 1&e.compareDocumentPosition(C.createElement("fieldset"))}),ce(function(e){return e.innerHTML="<a href='#'></a>","#"===e.firstChild.getAttribute("href")})||fe("type|href|height|width",function(e,t,n){if(!n)return e.getAttribute(t,"type"===t.toLowerCase()?1:2)}),d.attributes&&ce(function(e){return e.innerHTML="<input/>",e.firstChild.setAttribute("value",""),""===e.firstChild.getAttribute("value")})||fe("value",function(e,t,n){if(!n&&"input"===e.nodeName.toLowerCase())return e.defaultValue}),ce(function(e){return null==e.getAttribute("disabled")})||fe(R,function(e,t,n){var r;if(!n)return!0===e[t]?t.toLowerCase():(r=e.getAttributeNode(t))&&r.specified?r.value:null}),se}(C);k.find=h,k.expr=h.selectors,k.expr[":"]=k.expr.pseudos,k.uniqueSort=k.unique=h.uniqueSort,k.text=h.getText,k.isXMLDoc=h.isXML,k.contains=h.contains,k.escapeSelector=h.escape;var T=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&k(e).is(n))break;r.push(e)}return r},S=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},N=k.expr.match.needsContext;function A(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var D=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function j(e,n,r){return m(n)?k.grep(e,function(e,t){return!!n.call(e,t,e)!==r}):n.nodeType?k.grep(e,function(e){return e===n!==r}):"string"!=typeof n?k.grep(e,function(e){return-1<i.call(n,e)!==r}):k.filter(n,e,r)}k.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?k.find.matchesSelector(r,e)?[r]:[]:k.find.matches(e,k.grep(t,function(e){return 1===e.nodeType}))},k.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(k(e).filter(function(){for(t=0;t<r;t++)if(k.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)k.find(e,i[t],n);return 1<r?k.uniqueSort(n):n},filter:function(e){return this.pushStack(j(this,e||[],!1))},not:function(e){return this.pushStack(j(this,e||[],!0))},is:function(e){return!!j(this,"string"==typeof e&&N.test(e)?k(e):e||[],!1).length}});var q,L=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(k.fn.init=function(e,t,n){var r,i;if(!e)return this;if(n=n||q,"string"==typeof e){if(!(r="<"===e[0]&&">"===e[e.length-1]&&3<=e.length?[null,e,null]:L.exec(e))||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof k?t[0]:t,k.merge(this,k.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:E,!0)),D.test(r[1])&&k.isPlainObject(t))for(r in t)m(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return(i=E.getElementById(r[2]))&&(this[0]=i,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):m(e)?void 0!==n.ready?n.ready(e):e(k):k.makeArray(e,this)}).prototype=k.fn,q=k(E);var H=/^(?:parents|prev(?:Until|All))/,O={children:!0,contents:!0,next:!0,prev:!0};function P(e,t){while((e=e[t])&&1!==e.nodeType);return e}k.fn.extend({has:function(e){var t=k(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(k.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&k(e);if(!N.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?-1<a.index(n):1===n.nodeType&&k.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(1<o.length?k.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?i.call(k(e),this[0]):i.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(k.uniqueSort(k.merge(this.get(),k(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),k.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return T(e,"parentNode")},parentsUntil:function(e,t,n){return T(e,"parentNode",n)},next:function(e){return P(e,"nextSibling")},prev:function(e){return P(e,"previousSibling")},nextAll:function(e){return T(e,"nextSibling")},prevAll:function(e){return T(e,"previousSibling")},nextUntil:function(e,t,n){return T(e,"nextSibling",n)},prevUntil:function(e,t,n){return T(e,"previousSibling",n)},siblings:function(e){return S((e.parentNode||{}).firstChild,e)},children:function(e){return S(e.firstChild)},contents:function(e){return"undefined"!=typeof e.contentDocument?e.contentDocument:(A(e,"template")&&(e=e.content||e),k.merge([],e.childNodes))}},function(r,i){k.fn[r]=function(e,t){var n=k.map(this,i,e);return"Until"!==r.slice(-5)&&(t=e),t&&"string"==typeof t&&(n=k.filter(t,n)),1<this.length&&(O[r]||k.uniqueSort(n),H.test(r)&&n.reverse()),this.pushStack(n)}});var R=/[^\x20\t\r\n\f]+/g;function M(e){return e}function I(e){throw e}function W(e,t,n,r){var i;try{e&&m(i=e.promise)?i.call(e).done(t).fail(n):e&&m(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}k.Callbacks=function(r){var e,n;r="string"==typeof r?(e=r,n={},k.each(e.match(R)||[],function(e,t){n[t]=!0}),n):k.extend({},r);var i,t,o,a,s=[],u=[],l=-1,c=function(){for(a=a||r.once,o=i=!0;u.length;l=-1){t=u.shift();while(++l<s.length)!1===s[l].apply(t[0],t[1])&&r.stopOnFalse&&(l=s.length,t=!1)}r.memory||(t=!1),i=!1,a&&(s=t?[]:"")},f={add:function(){return s&&(t&&!i&&(l=s.length-1,u.push(t)),function n(e){k.each(e,function(e,t){m(t)?r.unique&&f.has(t)||s.push(t):t&&t.length&&"string"!==w(t)&&n(t)})}(arguments),t&&!i&&c()),this},remove:function(){return k.each(arguments,function(e,t){var n;while(-1<(n=k.inArray(t,s,n)))s.splice(n,1),n<=l&&l--}),this},has:function(e){return e?-1<k.inArray(e,s):0<s.length},empty:function(){return s&&(s=[]),this},disable:function(){return a=u=[],s=t="",this},disabled:function(){return!s},lock:function(){return a=u=[],t||i||(s=t=""),this},locked:function(){return!!a},fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),i||c()),this},fire:function(){return f.fireWith(this,arguments),this},fired:function(){return!!o}};return f},k.extend({Deferred:function(e){var o=[["notify","progress",k.Callbacks("memory"),k.Callbacks("memory"),2],["resolve","done",k.Callbacks("once memory"),k.Callbacks("once memory"),0,"resolved"],["reject","fail",k.Callbacks("once memory"),k.Callbacks("once memory"),1,"rejected"]],i="pending",a={state:function(){return i},always:function(){return s.done(arguments).fail(arguments),this},"catch":function(e){return a.then(null,e)},pipe:function(){var i=arguments;return k.Deferred(function(r){k.each(o,function(e,t){var n=m(i[t[4]])&&i[t[4]];s[t[1]](function(){var e=n&&n.apply(this,arguments);e&&m(e.promise)?e.promise().progress(r.notify).done(r.resolve).fail(r.reject):r[t[0]+"With"](this,n?[e]:arguments)})}),i=null}).promise()},then:function(t,n,r){var u=0;function l(i,o,a,s){return function(){var n=this,r=arguments,e=function(){var e,t;if(!(i<u)){if((e=a.apply(n,r))===o.promise())throw new TypeError("Thenable self-resolution");t=e&&("object"==typeof e||"function"==typeof e)&&e.then,m(t)?s?t.call(e,l(u,o,M,s),l(u,o,I,s)):(u++,t.call(e,l(u,o,M,s),l(u,o,I,s),l(u,o,M,o.notifyWith))):(a!==M&&(n=void 0,r=[e]),(s||o.resolveWith)(n,r))}},t=s?e:function(){try{e()}catch(e){k.Deferred.exceptionHook&&k.Deferred.exceptionHook(e,t.stackTrace),u<=i+1&&(a!==I&&(n=void 0,r=[e]),o.rejectWith(n,r))}};i?t():(k.Deferred.getStackHook&&(t.stackTrace=k.Deferred.getStackHook()),C.setTimeout(t))}}return k.Deferred(function(e){o[0][3].add(l(0,e,m(r)?r:M,e.notifyWith)),o[1][3].add(l(0,e,m(t)?t:M)),o[2][3].add(l(0,e,m(n)?n:I))}).promise()},promise:function(e){return null!=e?k.extend(e,a):a}},s={};return k.each(o,function(e,t){var n=t[2],r=t[5];a[t[1]]=n.add,r&&n.add(function(){i=r},o[3-e][2].disable,o[3-e][3].disable,o[0][2].lock,o[0][3].lock),n.add(t[3].fire),s[t[0]]=function(){return s[t[0]+"With"](this===s?void 0:this,arguments),this},s[t[0]+"With"]=n.fireWith}),a.promise(s),e&&e.call(s,s),s},when:function(e){var n=arguments.length,t=n,r=Array(t),i=s.call(arguments),o=k.Deferred(),a=function(t){return function(e){r[t]=this,i[t]=1<arguments.length?s.call(arguments):e,--n||o.resolveWith(r,i)}};if(n<=1&&(W(e,o.done(a(t)).resolve,o.reject,!n),"pending"===o.state()||m(i[t]&&i[t].then)))return o.then();while(t--)W(i[t],a(t),o.reject);return o.promise()}});var $=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;k.Deferred.exceptionHook=function(e,t){C.console&&C.console.warn&&e&&$.test(e.name)&&C.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},k.readyException=function(e){C.setTimeout(function(){throw e})};var F=k.Deferred();function B(){E.removeEventListener("DOMContentLoaded",B),C.removeEventListener("load",B),k.ready()}k.fn.ready=function(e){return F.then(e)["catch"](function(e){k.readyException(e)}),this},k.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--k.readyWait:k.isReady)||(k.isReady=!0)!==e&&0<--k.readyWait||F.resolveWith(E,[k])}}),k.ready.then=F.then,"complete"===E.readyState||"loading"!==E.readyState&&!E.documentElement.doScroll?C.setTimeout(k.ready):(E.addEventListener("DOMContentLoaded",B),C.addEventListener("load",B));var _=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===w(n))for(s in i=!0,n)_(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,m(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(k(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},z=/^-ms-/,U=/-([a-z])/g;function X(e,t){return t.toUpperCase()}function V(e){return e.replace(z,"ms-").replace(U,X)}var G=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function Y(){this.expando=k.expando+Y.uid++}Y.uid=1,Y.prototype={cache:function(e){var t=e[this.expando];return t||(t={},G(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[V(t)]=n;else for(r in t)i[V(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][V(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(V):(t=V(t))in r?[t]:t.match(R)||[]).length;while(n--)delete r[t[n]]}(void 0===t||k.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!k.isEmptyObject(t)}};var Q=new Y,J=new Y,K=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,Z=/[A-Z]/g;function ee(e,t,n){var r,i;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(Z,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n="true"===(i=n)||"false"!==i&&("null"===i?null:i===+i+""?+i:K.test(i)?JSON.parse(i):i)}catch(e){}J.set(e,t,n)}else n=void 0;return n}k.extend({hasData:function(e){return J.hasData(e)||Q.hasData(e)},data:function(e,t,n){return J.access(e,t,n)},removeData:function(e,t){J.remove(e,t)},_data:function(e,t,n){return Q.access(e,t,n)},_removeData:function(e,t){Q.remove(e,t)}}),k.fn.extend({data:function(n,e){var t,r,i,o=this[0],a=o&&o.attributes;if(void 0===n){if(this.length&&(i=J.get(o),1===o.nodeType&&!Q.get(o,"hasDataAttrs"))){t=a.length;while(t--)a[t]&&0===(r=a[t].name).indexOf("data-")&&(r=V(r.slice(5)),ee(o,r,i[r]));Q.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof n?this.each(function(){J.set(this,n)}):_(this,function(e){var t;if(o&&void 0===e)return void 0!==(t=J.get(o,n))?t:void 0!==(t=ee(o,n))?t:void 0;this.each(function(){J.set(this,n,e)})},null,e,1<arguments.length,null,!0)},removeData:function(e){return this.each(function(){J.remove(this,e)})}}),k.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=Q.get(e,t),n&&(!r||Array.isArray(n)?r=Q.access(e,t,k.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=k.queue(e,t),r=n.length,i=n.shift(),o=k._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,function(){k.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return Q.get(e,n)||Q.access(e,n,{empty:k.Callbacks("once memory").add(function(){Q.remove(e,[t+"queue",n])})})}}),k.fn.extend({queue:function(t,n){var e=2;return"string"!=typeof t&&(n=t,t="fx",e--),arguments.length<e?k.queue(this[0],t):void 0===n?this:this.each(function(){var e=k.queue(this,t,n);k._queueHooks(this,t),"fx"===t&&"inprogress"!==e[0]&&k.dequeue(this,t)})},dequeue:function(e){return this.each(function(){k.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=k.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=Q.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var te=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,ne=new RegExp("^(?:([+-])=|)("+te+")([a-z%]*)$","i"),re=["Top","Right","Bottom","Left"],ie=E.documentElement,oe=function(e){return k.contains(e.ownerDocument,e)},ae={composed:!0};ie.getRootNode&&(oe=function(e){return k.contains(e.ownerDocument,e)||e.getRootNode(ae)===e.ownerDocument});var se=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&oe(e)&&"none"===k.css(e,"display")},ue=function(e,t,n,r){var i,o,a={};for(o in t)a[o]=e.style[o],e.style[o]=t[o];for(o in i=n.apply(e,r||[]),t)e.style[o]=a[o];return i};function le(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return k.css(e,t,"")},u=s(),l=n&&n[3]||(k.cssNumber[t]?"":"px"),c=e.nodeType&&(k.cssNumber[t]||"px"!==l&&+u)&&ne.exec(k.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)k.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,k.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ce={};function fe(e,t){for(var n,r,i,o,a,s,u,l=[],c=0,f=e.length;c<f;c++)(r=e[c]).style&&(n=r.style.display,t?("none"===n&&(l[c]=Q.get(r,"display")||null,l[c]||(r.style.display="")),""===r.style.display&&se(r)&&(l[c]=(u=a=o=void 0,a=(i=r).ownerDocument,s=i.nodeName,(u=ce[s])||(o=a.body.appendChild(a.createElement(s)),u=k.css(o,"display"),o.parentNode.removeChild(o),"none"===u&&(u="block"),ce[s]=u)))):"none"!==n&&(l[c]="none",Q.set(r,"display",n)));for(c=0;c<f;c++)null!=l[c]&&(e[c].style.display=l[c]);return e}k.fn.extend({show:function(){return fe(this,!0)},hide:function(){return fe(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){se(this)?k(this).show():k(this).hide()})}});var pe=/^(?:checkbox|radio)$/i,de=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,he=/^$|^module$|\/(?:java|ecma)script/i,ge={option:[1,"<select multiple='multiple'>","</select>"],thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function ve(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&A(e,t)?k.merge([e],n):n}function ye(e,t){for(var n=0,r=e.length;n<r;n++)Q.set(e[n],"globalEval",!t||Q.get(t[n],"globalEval"))}ge.optgroup=ge.option,ge.tbody=ge.tfoot=ge.colgroup=ge.caption=ge.thead,ge.th=ge.td;var me,xe,be=/<|&#?\w+;/;function we(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===w(o))k.merge(p,o.nodeType?[o]:o);else if(be.test(o)){a=a||f.appendChild(t.createElement("div")),s=(de.exec(o)||["",""])[1].toLowerCase(),u=ge[s]||ge._default,a.innerHTML=u[1]+k.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;k.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&-1<k.inArray(o,r))i&&i.push(o);else if(l=oe(o),a=ve(f.appendChild(o),"script"),l&&ye(a),n){c=0;while(o=a[c++])he.test(o.type||"")&&n.push(o)}return f}me=E.createDocumentFragment().appendChild(E.createElement("div")),(xe=E.createElement("input")).setAttribute("type","radio"),xe.setAttribute("checked","checked"),xe.setAttribute("name","t"),me.appendChild(xe),y.checkClone=me.cloneNode(!0).cloneNode(!0).lastChild.checked,me.innerHTML="<textarea>x</textarea>",y.noCloneChecked=!!me.cloneNode(!0).lastChild.defaultValue;var Te=/^key/,Ce=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,Ee=/^([^.]*)(?:\.(.+)|)/;function ke(){return!0}function Se(){return!1}function Ne(e,t){return e===function(){try{return E.activeElement}catch(e){}}()==("focus"===t)}function Ae(e,t,n,r,i,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(r=r||n,n=void 0),t)Ae(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=Se;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return k().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=k.guid++)),e.each(function(){k.event.add(this,t,i,r,n)})}function De(e,i,o){o?(Q.set(e,i,!1),k.event.add(e,i,{namespace:!1,handler:function(e){var t,n,r=Q.get(this,i);if(1&e.isTrigger&&this[i]){if(r.length)(k.event.special[i]||{}).delegateType&&e.stopPropagation();else if(r=s.call(arguments),Q.set(this,i,r),t=o(this,i),this[i](),r!==(n=Q.get(this,i))||t?Q.set(this,i,!1):n={},r!==n)return e.stopImmediatePropagation(),e.preventDefault(),n.value}else r.length&&(Q.set(this,i,{value:k.event.trigger(k.extend(r[0],k.Event.prototype),r.slice(1),this)}),e.stopImmediatePropagation())}})):void 0===Q.get(e,i)&&k.event.add(e,i,ke)}k.event={global:{},add:function(t,e,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Q.get(t);if(v){n.handler&&(n=(o=n).handler,i=o.selector),i&&k.find.matchesSelector(ie,i),n.guid||(n.guid=k.guid++),(u=v.events)||(u=v.events={}),(a=v.handle)||(a=v.handle=function(e){return"undefined"!=typeof k&&k.event.triggered!==e.type?k.event.dispatch.apply(t,arguments):void 0}),l=(e=(e||"").match(R)||[""]).length;while(l--)d=g=(s=Ee.exec(e[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=k.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=k.event.special[d]||{},c=k.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&k.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(t,r,h,a)||t.addEventListener&&t.addEventListener(d,a)),f.add&&(f.add.call(t,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),k.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=Q.hasData(e)&&Q.get(e);if(v&&(u=v.events)){l=(t=(t||"").match(R)||[""]).length;while(l--)if(d=g=(s=Ee.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d){f=k.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,v.handle)||k.removeEvent(e,d,v.handle),delete u[d])}else for(d in u)k.event.remove(e,d+t[l],n,r,!0);k.isEmptyObject(u)&&Q.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=k.event.fix(e),u=new Array(arguments.length),l=(Q.get(this,"events")||{})[s.type]||[],c=k.event.special[s.type]||{};for(u[0]=s,t=1;t<arguments.length;t++)u[t]=arguments[t];if(s.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,s)){a=k.event.handlers.call(this,s,l),t=0;while((i=a[t++])&&!s.isPropagationStopped()){s.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!s.isImmediatePropagationStopped())s.rnamespace&&!1!==o.namespace&&!s.rnamespace.test(o.namespace)||(s.handleObj=o,s.data=o.data,void 0!==(r=((k.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,u))&&!1===(s.result=r)&&(s.preventDefault(),s.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,s),s.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&1<=e.button))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?-1<k(i,this).index(l):k.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(t,e){Object.defineProperty(k.Event.prototype,t,{enumerable:!0,configurable:!0,get:m(e)?function(){if(this.originalEvent)return e(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[t]},set:function(e){Object.defineProperty(this,t,{enumerable:!0,configurable:!0,writable:!0,value:e})}})},fix:function(e){return e[k.expando]?e:new k.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&De(t,"click",ke),!1},trigger:function(e){var t=this||e;return pe.test(t.type)&&t.click&&A(t,"input")&&De(t,"click"),!0},_default:function(e){var t=e.target;return pe.test(t.type)&&t.click&&A(t,"input")&&Q.get(t,"click")||A(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},k.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},k.Event=function(e,t){if(!(this instanceof k.Event))return new k.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?ke:Se,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&k.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[k.expando]=!0},k.Event.prototype={constructor:k.Event,isDefaultPrevented:Se,isPropagationStopped:Se,isImmediatePropagationStopped:Se,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=ke,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=ke,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=ke,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},k.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:function(e){var t=e.button;return null==e.which&&Te.test(e.type)?null!=e.charCode?e.charCode:e.keyCode:!e.which&&void 0!==t&&Ce.test(e.type)?1&t?1:2&t?3:4&t?2:0:e.which}},k.event.addProp),k.each({focus:"focusin",blur:"focusout"},function(e,t){k.event.special[e]={setup:function(){return De(this,e,Ne),!1},trigger:function(){return De(this,e),!0},delegateType:t}}),k.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,i){k.event.special[e]={delegateType:i,bindType:i,handle:function(e){var t,n=e.relatedTarget,r=e.handleObj;return n&&(n===this||k.contains(this,n))||(e.type=r.origType,t=r.handler.apply(this,arguments),e.type=i),t}}}),k.fn.extend({on:function(e,t,n,r){return Ae(this,e,t,n,r)},one:function(e,t,n,r){return Ae(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,k(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=Se),this.each(function(){k.event.remove(this,e,n,t)})}});var je=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi,qe=/<script|<style|<link/i,Le=/checked\s*(?:[^=]|=\s*.checked.)/i,He=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;function Oe(e,t){return A(e,"table")&&A(11!==t.nodeType?t:t.firstChild,"tr")&&k(e).children("tbody")[0]||e}function Pe(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function Re(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Me(e,t){var n,r,i,o,a,s,u,l;if(1===t.nodeType){if(Q.hasData(e)&&(o=Q.access(e),a=Q.set(t,o),l=o.events))for(i in delete a.handle,a.events={},l)for(n=0,r=l[i].length;n<r;n++)k.event.add(t,i,l[i][n]);J.hasData(e)&&(s=J.access(e),u=k.extend({},s),J.set(t,u))}}function Ie(n,r,i,o){r=g.apply([],r);var e,t,a,s,u,l,c=0,f=n.length,p=f-1,d=r[0],h=m(d);if(h||1<f&&"string"==typeof d&&!y.checkClone&&Le.test(d))return n.each(function(e){var t=n.eq(e);h&&(r[0]=d.call(this,e,t.html())),Ie(t,r,i,o)});if(f&&(t=(e=we(r,n[0].ownerDocument,!1,n,o)).firstChild,1===e.childNodes.length&&(e=t),t||o)){for(s=(a=k.map(ve(e,"script"),Pe)).length;c<f;c++)u=e,c!==p&&(u=k.clone(u,!0,!0),s&&k.merge(a,ve(u,"script"))),i.call(n[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,k.map(a,Re),c=0;c<s;c++)u=a[c],he.test(u.type||"")&&!Q.access(u,"globalEval")&&k.contains(l,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?k._evalUrl&&!u.noModule&&k._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")}):b(u.textContent.replace(He,""),u,l))}return n}function We(e,t,n){for(var r,i=t?k.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||k.cleanData(ve(r)),r.parentNode&&(n&&oe(r)&&ye(ve(r,"script")),r.parentNode.removeChild(r));return e}k.extend({htmlPrefilter:function(e){return e.replace(je,"<$1></$2>")},clone:function(e,t,n){var r,i,o,a,s,u,l,c=e.cloneNode(!0),f=oe(e);if(!(y.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||k.isXMLDoc(e)))for(a=ve(c),r=0,i=(o=ve(e)).length;r<i;r++)s=o[r],u=a[r],void 0,"input"===(l=u.nodeName.toLowerCase())&&pe.test(s.type)?u.checked=s.checked:"input"!==l&&"textarea"!==l||(u.defaultValue=s.defaultValue);if(t)if(n)for(o=o||ve(e),a=a||ve(c),r=0,i=o.length;r<i;r++)Me(o[r],a[r]);else Me(e,c);return 0<(a=ve(c,"script")).length&&ye(a,!f&&ve(e,"script")),c},cleanData:function(e){for(var t,n,r,i=k.event.special,o=0;void 0!==(n=e[o]);o++)if(G(n)){if(t=n[Q.expando]){if(t.events)for(r in t.events)i[r]?k.event.remove(n,r):k.removeEvent(n,r,t.handle);n[Q.expando]=void 0}n[J.expando]&&(n[J.expando]=void 0)}}}),k.fn.extend({detach:function(e){return We(this,e,!0)},remove:function(e){return We(this,e)},text:function(e){return _(this,function(e){return void 0===e?k.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return Ie(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||Oe(this,e).appendChild(e)})},prepend:function(){return Ie(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=Oe(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return Ie(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return Ie(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(k.cleanData(ve(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return k.clone(this,e,t)})},html:function(e){return _(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!qe.test(e)&&!ge[(de.exec(e)||["",""])[1].toLowerCase()]){e=k.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(k.cleanData(ve(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var n=[];return Ie(this,arguments,function(e){var t=this.parentNode;k.inArray(this,n)<0&&(k.cleanData(ve(this)),t&&t.replaceChild(e,this))},n)}}),k.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,a){k.fn[e]=function(e){for(var t,n=[],r=k(e),i=r.length-1,o=0;o<=i;o++)t=o===i?this:this.clone(!0),k(r[o])[a](t),u.apply(n,t.get());return this.pushStack(n)}});var $e=new RegExp("^("+te+")(?!px)[a-z%]+$","i"),Fe=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=C),t.getComputedStyle(e)},Be=new RegExp(re.join("|"),"i");function _e(e,t,n){var r,i,o,a,s=e.style;return(n=n||Fe(e))&&(""!==(a=n.getPropertyValue(t)||n[t])||oe(e)||(a=k.style(e,t)),!y.pixelBoxStyles()&&$e.test(a)&&Be.test(t)&&(r=s.width,i=s.minWidth,o=s.maxWidth,s.minWidth=s.maxWidth=s.width=a,a=n.width,s.width=r,s.minWidth=i,s.maxWidth=o)),void 0!==a?a+"":a}function ze(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(u){s.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",u.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",ie.appendChild(s).appendChild(u);var e=C.getComputedStyle(u);n="1%"!==e.top,a=12===t(e.marginLeft),u.style.right="60%",o=36===t(e.right),r=36===t(e.width),u.style.position="absolute",i=12===t(u.offsetWidth/3),ie.removeChild(s),u=null}}function t(e){return Math.round(parseFloat(e))}var n,r,i,o,a,s=E.createElement("div"),u=E.createElement("div");u.style&&(u.style.backgroundClip="content-box",u.cloneNode(!0).style.backgroundClip="",y.clearCloneStyle="content-box"===u.style.backgroundClip,k.extend(y,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),o},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),a},scrollboxSize:function(){return e(),i}}))}();var Ue=["Webkit","Moz","ms"],Xe=E.createElement("div").style,Ve={};function Ge(e){var t=k.cssProps[e]||Ve[e];return t||(e in Xe?e:Ve[e]=function(e){var t=e[0].toUpperCase()+e.slice(1),n=Ue.length;while(n--)if((e=Ue[n]+t)in Xe)return e}(e)||e)}var Ye=/^(none|table(?!-c[ea]).+)/,Qe=/^--/,Je={position:"absolute",visibility:"hidden",display:"block"},Ke={letterSpacing:"0",fontWeight:"400"};function Ze(e,t,n){var r=ne.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function et(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(u+=k.css(e,n+re[a],!0,i)),r?("content"===n&&(u-=k.css(e,"padding"+re[a],!0,i)),"margin"!==n&&(u-=k.css(e,"border"+re[a]+"Width",!0,i))):(u+=k.css(e,"padding"+re[a],!0,i),"padding"!==n?u+=k.css(e,"border"+re[a]+"Width",!0,i):s+=k.css(e,"border"+re[a]+"Width",!0,i));return!r&&0<=o&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u}function tt(e,t,n){var r=Fe(e),i=(!y.boxSizingReliable()||n)&&"border-box"===k.css(e,"boxSizing",!1,r),o=i,a=_e(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);if($e.test(a)){if(!n)return a;a="auto"}return(!y.boxSizingReliable()&&i||"auto"===a||!parseFloat(a)&&"inline"===k.css(e,"display",!1,r))&&e.getClientRects().length&&(i="border-box"===k.css(e,"boxSizing",!1,r),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+et(e,t,n||(i?"border":"content"),o,r,a)+"px"}function nt(e,t,n,r,i){return new nt.prototype.init(e,t,n,r,i)}k.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=_e(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=V(t),u=Qe.test(t),l=e.style;if(u||(t=Ge(s)),a=k.cssHooks[t]||k.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"===(o=typeof n)&&(i=ne.exec(n))&&i[1]&&(n=le(e,t,i),o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(k.cssNumber[s]?"":"px")),y.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=V(t);return Qe.test(t)||(t=Ge(s)),(a=k.cssHooks[t]||k.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=_e(e,t,r)),"normal"===i&&t in Ke&&(i=Ke[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),k.each(["height","width"],function(e,u){k.cssHooks[u]={get:function(e,t,n){if(t)return!Ye.test(k.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?tt(e,u,n):ue(e,Je,function(){return tt(e,u,n)})},set:function(e,t,n){var r,i=Fe(e),o=!y.scrollboxSize()&&"absolute"===i.position,a=(o||n)&&"border-box"===k.css(e,"boxSizing",!1,i),s=n?et(e,u,n,a,i):0;return a&&o&&(s-=Math.ceil(e["offset"+u[0].toUpperCase()+u.slice(1)]-parseFloat(i[u])-et(e,u,"border",!1,i)-.5)),s&&(r=ne.exec(t))&&"px"!==(r[3]||"px")&&(e.style[u]=t,t=k.css(e,u)),Ze(0,t,s)}}}),k.cssHooks.marginLeft=ze(y.reliableMarginLeft,function(e,t){if(t)return(parseFloat(_e(e,"marginLeft"))||e.getBoundingClientRect().left-ue(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),k.each({margin:"",padding:"",border:"Width"},function(i,o){k.cssHooks[i+o]={expand:function(e){for(var t=0,n={},r="string"==typeof e?e.split(" "):[e];t<4;t++)n[i+re[t]+o]=r[t]||r[t-2]||r[0];return n}},"margin"!==i&&(k.cssHooks[i+o].set=Ze)}),k.fn.extend({css:function(e,t){return _(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=Fe(e),i=t.length;a<i;a++)o[t[a]]=k.css(e,t[a],!1,r);return o}return void 0!==n?k.style(e,t,n):k.css(e,t)},e,t,1<arguments.length)}}),((k.Tween=nt).prototype={constructor:nt,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||k.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(k.cssNumber[n]?"":"px")},cur:function(){var e=nt.propHooks[this.prop];return e&&e.get?e.get(this):nt.propHooks._default.get(this)},run:function(e){var t,n=nt.propHooks[this.prop];return this.options.duration?this.pos=t=k.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):nt.propHooks._default.set(this),this}}).init.prototype=nt.prototype,(nt.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=k.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){k.fx.step[e.prop]?k.fx.step[e.prop](e):1!==e.elem.nodeType||!k.cssHooks[e.prop]&&null==e.elem.style[Ge(e.prop)]?e.elem[e.prop]=e.now:k.style(e.elem,e.prop,e.now+e.unit)}}}).scrollTop=nt.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},k.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},k.fx=nt.prototype.init,k.fx.step={};var rt,it,ot,at,st=/^(?:toggle|show|hide)$/,ut=/queueHooks$/;function lt(){it&&(!1===E.hidden&&C.requestAnimationFrame?C.requestAnimationFrame(lt):C.setTimeout(lt,k.fx.interval),k.fx.tick())}function ct(){return C.setTimeout(function(){rt=void 0}),rt=Date.now()}function ft(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=re[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function pt(e,t,n){for(var r,i=(dt.tweeners[t]||[]).concat(dt.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function dt(o,e,t){var n,a,r=0,i=dt.prefilters.length,s=k.Deferred().always(function(){delete u.elem}),u=function(){if(a)return!1;for(var e=rt||ct(),t=Math.max(0,l.startTime+l.duration-e),n=1-(t/l.duration||0),r=0,i=l.tweens.length;r<i;r++)l.tweens[r].run(n);return s.notifyWith(o,[l,n,t]),n<1&&i?t:(i||s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l]),!1)},l=s.promise({elem:o,props:k.extend({},e),opts:k.extend(!0,{specialEasing:{},easing:k.easing._default},t),originalProperties:e,originalOptions:t,startTime:rt||ct(),duration:t.duration,tweens:[],createTween:function(e,t){var n=k.Tween(o,l.opts,e,t,l.opts.specialEasing[e]||l.opts.easing);return l.tweens.push(n),n},stop:function(e){var t=0,n=e?l.tweens.length:0;if(a)return this;for(a=!0;t<n;t++)l.tweens[t].run(1);return e?(s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l,e])):s.rejectWith(o,[l,e]),this}}),c=l.props;for(!function(e,t){var n,r,i,o,a;for(n in e)if(i=t[r=V(n)],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=k.cssHooks[r])&&"expand"in a)for(n in o=a.expand(o),delete e[r],o)n in e||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);r<i;r++)if(n=dt.prefilters[r].call(l,o,c,l.opts))return m(n.stop)&&(k._queueHooks(l.elem,l.opts.queue).stop=n.stop.bind(n)),n;return k.map(c,pt,l),m(l.opts.start)&&l.opts.start.call(o,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),k.fx.timer(k.extend(u,{elem:o,anim:l,queue:l.opts.queue})),l}k.Animation=k.extend(dt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return le(n.elem,e,ne.exec(t),n),n}]},tweener:function(e,t){m(e)?(t=e,e=["*"]):e=e.match(R);for(var n,r=0,i=e.length;r<i;r++)n=e[r],dt.tweeners[n]=dt.tweeners[n]||[],dt.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&se(e),v=Q.get(e,"fxshow");for(r in n.queue||(null==(a=k._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,k.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],st.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!v||void 0===v[r])continue;g=!0}d[r]=v&&v[r]||k.style(e,r)}if((u=!k.isEmptyObject(t))||!k.isEmptyObject(d))for(r in f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=v&&v.display)&&(l=Q.get(e,"display")),"none"===(c=k.css(e,"display"))&&(l?c=l:(fe([e],!0),l=e.style.display||l,c=k.css(e,"display"),fe([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===k.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1,d)u||(v?"hidden"in v&&(g=v.hidden):v=Q.access(e,"fxshow",{display:l}),o&&(v.hidden=!g),g&&fe([e],!0),p.done(function(){for(r in g||fe([e]),Q.remove(e,"fxshow"),d)k.style(e,r,d[r])})),u=pt(g?v[r]:0,r,p),r in v||(v[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?dt.prefilters.unshift(e):dt.prefilters.push(e)}}),k.speed=function(e,t,n){var r=e&&"object"==typeof e?k.extend({},e):{complete:n||!n&&t||m(e)&&e,duration:e,easing:n&&t||t&&!m(t)&&t};return k.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in k.fx.speeds?r.duration=k.fx.speeds[r.duration]:r.duration=k.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){m(r.old)&&r.old.call(this),r.queue&&k.dequeue(this,r.queue)},r},k.fn.extend({fadeTo:function(e,t,n,r){return this.filter(se).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(t,e,n,r){var i=k.isEmptyObject(t),o=k.speed(e,n,r),a=function(){var e=dt(this,k.extend({},t),o);(i||Q.get(this,"finish"))&&e.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(i,e,o){var a=function(e){var t=e.stop;delete e.stop,t(o)};return"string"!=typeof i&&(o=e,e=i,i=void 0),e&&!1!==i&&this.queue(i||"fx",[]),this.each(function(){var e=!0,t=null!=i&&i+"queueHooks",n=k.timers,r=Q.get(this);if(t)r[t]&&r[t].stop&&a(r[t]);else for(t in r)r[t]&&r[t].stop&&ut.test(t)&&a(r[t]);for(t=n.length;t--;)n[t].elem!==this||null!=i&&n[t].queue!==i||(n[t].anim.stop(o),e=!1,n.splice(t,1));!e&&o||k.dequeue(this,i)})},finish:function(a){return!1!==a&&(a=a||"fx"),this.each(function(){var e,t=Q.get(this),n=t[a+"queue"],r=t[a+"queueHooks"],i=k.timers,o=n?n.length:0;for(t.finish=!0,k.queue(this,a,[]),r&&r.stop&&r.stop.call(this,!0),e=i.length;e--;)i[e].elem===this&&i[e].queue===a&&(i[e].anim.stop(!0),i.splice(e,1));for(e=0;e<o;e++)n[e]&&n[e].finish&&n[e].finish.call(this);delete t.finish})}}),k.each(["toggle","show","hide"],function(e,r){var i=k.fn[r];k.fn[r]=function(e,t,n){return null==e||"boolean"==typeof e?i.apply(this,arguments):this.animate(ft(r,!0),e,t,n)}}),k.each({slideDown:ft("show"),slideUp:ft("hide"),slideToggle:ft("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,r){k.fn[e]=function(e,t,n){return this.animate(r,e,t,n)}}),k.timers=[],k.fx.tick=function(){var e,t=0,n=k.timers;for(rt=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||k.fx.stop(),rt=void 0},k.fx.timer=function(e){k.timers.push(e),k.fx.start()},k.fx.interval=13,k.fx.start=function(){it||(it=!0,lt())},k.fx.stop=function(){it=null},k.fx.speeds={slow:600,fast:200,_default:400},k.fn.delay=function(r,e){return r=k.fx&&k.fx.speeds[r]||r,e=e||"fx",this.queue(e,function(e,t){var n=C.setTimeout(e,r);t.stop=function(){C.clearTimeout(n)}})},ot=E.createElement("input"),at=E.createElement("select").appendChild(E.createElement("option")),ot.type="checkbox",y.checkOn=""!==ot.value,y.optSelected=at.selected,(ot=E.createElement("input")).value="t",ot.type="radio",y.radioValue="t"===ot.value;var ht,gt=k.expr.attrHandle;k.fn.extend({attr:function(e,t){return _(this,k.attr,e,t,1<arguments.length)},removeAttr:function(e){return this.each(function(){k.removeAttr(this,e)})}}),k.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?k.prop(e,t,n):(1===o&&k.isXMLDoc(e)||(i=k.attrHooks[t.toLowerCase()]||(k.expr.match.bool.test(t)?ht:void 0)),void 0!==n?null===n?void k.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=k.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!y.radioValue&&"radio"===t&&A(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(R);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),ht={set:function(e,t,n){return!1===t?k.removeAttr(e,n):e.setAttribute(n,n),n}},k.each(k.expr.match.bool.source.match(/\w+/g),function(e,t){var a=gt[t]||k.find.attr;gt[t]=function(e,t,n){var r,i,o=t.toLowerCase();return n||(i=gt[o],gt[o]=r,r=null!=a(e,t,n)?o:null,gt[o]=i),r}});var vt=/^(?:input|select|textarea|button)$/i,yt=/^(?:a|area)$/i;function mt(e){return(e.match(R)||[]).join(" ")}function xt(e){return e.getAttribute&&e.getAttribute("class")||""}function bt(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(R)||[]}k.fn.extend({prop:function(e,t){return _(this,k.prop,e,t,1<arguments.length)},removeProp:function(e){return this.each(function(){delete this[k.propFix[e]||e]})}}),k.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&k.isXMLDoc(e)||(t=k.propFix[t]||t,i=k.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=k.find.attr(e,"tabindex");return t?parseInt(t,10):vt.test(e.nodeName)||yt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),y.optSelected||(k.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),k.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){k.propFix[this.toLowerCase()]=this}),k.fn.extend({addClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){k(this).addClass(t.call(this,e,xt(this)))});if((e=bt(t)).length)while(n=this[u++])if(i=xt(n),r=1===n.nodeType&&" "+mt(i)+" "){a=0;while(o=e[a++])r.indexOf(" "+o+" ")<0&&(r+=o+" ");i!==(s=mt(r))&&n.setAttribute("class",s)}return this},removeClass:function(t){var e,n,r,i,o,a,s,u=0;if(m(t))return this.each(function(e){k(this).removeClass(t.call(this,e,xt(this)))});if(!arguments.length)return this.attr("class","");if((e=bt(t)).length)while(n=this[u++])if(i=xt(n),r=1===n.nodeType&&" "+mt(i)+" "){a=0;while(o=e[a++])while(-1<r.indexOf(" "+o+" "))r=r.replace(" "+o+" "," ");i!==(s=mt(r))&&n.setAttribute("class",s)}return this},toggleClass:function(i,t){var o=typeof i,a="string"===o||Array.isArray(i);return"boolean"==typeof t&&a?t?this.addClass(i):this.removeClass(i):m(i)?this.each(function(e){k(this).toggleClass(i.call(this,e,xt(this),t),t)}):this.each(function(){var e,t,n,r;if(a){t=0,n=k(this),r=bt(i);while(e=r[t++])n.hasClass(e)?n.removeClass(e):n.addClass(e)}else void 0!==i&&"boolean"!==o||((e=xt(this))&&Q.set(this,"__className__",e),this.setAttribute&&this.setAttribute("class",e||!1===i?"":Q.get(this,"__className__")||""))})},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&-1<(" "+mt(xt(n))+" ").indexOf(t))return!0;return!1}});var wt=/\r/g;k.fn.extend({val:function(n){var r,e,i,t=this[0];return arguments.length?(i=m(n),this.each(function(e){var t;1===this.nodeType&&(null==(t=i?n.call(this,e,k(this).val()):n)?t="":"number"==typeof t?t+="":Array.isArray(t)&&(t=k.map(t,function(e){return null==e?"":e+""})),(r=k.valHooks[this.type]||k.valHooks[this.nodeName.toLowerCase()])&&"set"in r&&void 0!==r.set(this,t,"value")||(this.value=t))})):t?(r=k.valHooks[t.type]||k.valHooks[t.nodeName.toLowerCase()])&&"get"in r&&void 0!==(e=r.get(t,"value"))?e:"string"==typeof(e=t.value)?e.replace(wt,""):null==e?"":e:void 0}}),k.extend({valHooks:{option:{get:function(e){var t=k.find.attr(e,"value");return null!=t?t:mt(k.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!A(n.parentNode,"optgroup"))){if(t=k(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=k.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=-1<k.inArray(k.valHooks.option.get(r),o))&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),k.each(["radio","checkbox"],function(){k.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=-1<k.inArray(k(e).val(),t)}},y.checkOn||(k.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})}),y.focusin="onfocusin"in C;var Tt=/^(?:focusinfocus|focusoutblur)$/,Ct=function(e){e.stopPropagation()};k.extend(k.event,{trigger:function(e,t,n,r){var i,o,a,s,u,l,c,f,p=[n||E],d=v.call(e,"type")?e.type:e,h=v.call(e,"namespace")?e.namespace.split("."):[];if(o=f=a=n=n||E,3!==n.nodeType&&8!==n.nodeType&&!Tt.test(d+k.event.triggered)&&(-1<d.indexOf(".")&&(d=(h=d.split(".")).shift(),h.sort()),u=d.indexOf(":")<0&&"on"+d,(e=e[k.expando]?e:new k.Event(d,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=h.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:k.makeArray(t,[e]),c=k.event.special[d]||{},r||!c.trigger||!1!==c.trigger.apply(n,t))){if(!r&&!c.noBubble&&!x(n)){for(s=c.delegateType||d,Tt.test(s+d)||(o=o.parentNode);o;o=o.parentNode)p.push(o),a=o;a===(n.ownerDocument||E)&&p.push(a.defaultView||a.parentWindow||C)}i=0;while((o=p[i++])&&!e.isPropagationStopped())f=o,e.type=1<i?s:c.bindType||d,(l=(Q.get(o,"events")||{})[e.type]&&Q.get(o,"handle"))&&l.apply(o,t),(l=u&&o[u])&&l.apply&&G(o)&&(e.result=l.apply(o,t),!1===e.result&&e.preventDefault());return e.type=d,r||e.isDefaultPrevented()||c._default&&!1!==c._default.apply(p.pop(),t)||!G(n)||u&&m(n[d])&&!x(n)&&((a=n[u])&&(n[u]=null),k.event.triggered=d,e.isPropagationStopped()&&f.addEventListener(d,Ct),n[d](),e.isPropagationStopped()&&f.removeEventListener(d,Ct),k.event.triggered=void 0,a&&(n[u]=a)),e.result}},simulate:function(e,t,n){var r=k.extend(new k.Event,n,{type:e,isSimulated:!0});k.event.trigger(r,null,t)}}),k.fn.extend({trigger:function(e,t){return this.each(function(){k.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return k.event.trigger(e,t,n,!0)}}),y.focusin||k.each({focus:"focusin",blur:"focusout"},function(n,r){var i=function(e){k.event.simulate(r,e.target,k.event.fix(e))};k.event.special[r]={setup:function(){var e=this.ownerDocument||this,t=Q.access(e,r);t||e.addEventListener(n,i,!0),Q.access(e,r,(t||0)+1)},teardown:function(){var e=this.ownerDocument||this,t=Q.access(e,r)-1;t?Q.access(e,r,t):(e.removeEventListener(n,i,!0),Q.remove(e,r))}}});var Et=C.location,kt=Date.now(),St=/\?/;k.parseXML=function(e){var t;if(!e||"string"!=typeof e)return null;try{t=(new C.DOMParser).parseFromString(e,"text/xml")}catch(e){t=void 0}return t&&!t.getElementsByTagName("parsererror").length||k.error("Invalid XML: "+e),t};var Nt=/\[\]$/,At=/\r?\n/g,Dt=/^(?:submit|button|image|reset|file)$/i,jt=/^(?:input|select|textarea|keygen)/i;function qt(n,e,r,i){var t;if(Array.isArray(e))k.each(e,function(e,t){r||Nt.test(n)?i(n,t):qt(n+"["+("object"==typeof t&&null!=t?e:"")+"]",t,r,i)});else if(r||"object"!==w(e))i(n,e);else for(t in e)qt(n+"["+t+"]",e[t],r,i)}k.param=function(e,t){var n,r=[],i=function(e,t){var n=m(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!k.isPlainObject(e))k.each(e,function(){i(this.name,this.value)});else for(n in e)qt(n,e[n],t,i);return r.join("&")},k.fn.extend({serialize:function(){return k.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=k.prop(this,"elements");return e?k.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!k(this).is(":disabled")&&jt.test(this.nodeName)&&!Dt.test(e)&&(this.checked||!pe.test(e))}).map(function(e,t){var n=k(this).val();return null==n?null:Array.isArray(n)?k.map(n,function(e){return{name:t.name,value:e.replace(At,"\r\n")}}):{name:t.name,value:n.replace(At,"\r\n")}}).get()}});var Lt=/%20/g,Ht=/#.*$/,Ot=/([?&])_=[^&]*/,Pt=/^(.*?):[ \t]*([^\r\n]*)$/gm,Rt=/^(?:GET|HEAD)$/,Mt=/^\/\//,It={},Wt={},$t="*/".concat("*"),Ft=E.createElement("a");function Bt(o){return function(e,t){"string"!=typeof e&&(t=e,e="*");var n,r=0,i=e.toLowerCase().match(R)||[];if(m(t))while(n=i[r++])"+"===n[0]?(n=n.slice(1)||"*",(o[n]=o[n]||[]).unshift(t)):(o[n]=o[n]||[]).push(t)}}function _t(t,i,o,a){var s={},u=t===Wt;function l(e){var r;return s[e]=!0,k.each(t[e]||[],function(e,t){var n=t(i,o,a);return"string"!=typeof n||u||s[n]?u?!(r=n):void 0:(i.dataTypes.unshift(n),l(n),!1)}),r}return l(i.dataTypes[0])||!s["*"]&&l("*")}function zt(e,t){var n,r,i=k.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&k.extend(!0,e,r),e}Ft.href=Et.href,k.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Et.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Et.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":$t,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":k.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?zt(zt(e,k.ajaxSettings),t):zt(k.ajaxSettings,e)},ajaxPrefilter:Bt(It),ajaxTransport:Bt(Wt),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var c,f,p,n,d,r,h,g,i,o,v=k.ajaxSetup({},t),y=v.context||v,m=v.context&&(y.nodeType||y.jquery)?k(y):k.event,x=k.Deferred(),b=k.Callbacks("once memory"),w=v.statusCode||{},a={},s={},u="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(h){if(!n){n={};while(t=Pt.exec(p))n[t[1].toLowerCase()+" "]=(n[t[1].toLowerCase()+" "]||[]).concat(t[2])}t=n[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return h?p:null},setRequestHeader:function(e,t){return null==h&&(e=s[e.toLowerCase()]=s[e.toLowerCase()]||e,a[e]=t),this},overrideMimeType:function(e){return null==h&&(v.mimeType=e),this},statusCode:function(e){var t;if(e)if(h)T.always(e[T.status]);else for(t in e)w[t]=[w[t],e[t]];return this},abort:function(e){var t=e||u;return c&&c.abort(t),l(0,t),this}};if(x.promise(T),v.url=((e||v.url||Et.href)+"").replace(Mt,Et.protocol+"//"),v.type=t.method||t.type||v.method||v.type,v.dataTypes=(v.dataType||"*").toLowerCase().match(R)||[""],null==v.crossDomain){r=E.createElement("a");try{r.href=v.url,r.href=r.href,v.crossDomain=Ft.protocol+"//"+Ft.host!=r.protocol+"//"+r.host}catch(e){v.crossDomain=!0}}if(v.data&&v.processData&&"string"!=typeof v.data&&(v.data=k.param(v.data,v.traditional)),_t(It,v,t,T),h)return T;for(i in(g=k.event&&v.global)&&0==k.active++&&k.event.trigger("ajaxStart"),v.type=v.type.toUpperCase(),v.hasContent=!Rt.test(v.type),f=v.url.replace(Ht,""),v.hasContent?v.data&&v.processData&&0===(v.contentType||"").indexOf("application/x-www-form-urlencoded")&&(v.data=v.data.replace(Lt,"+")):(o=v.url.slice(f.length),v.data&&(v.processData||"string"==typeof v.data)&&(f+=(St.test(f)?"&":"?")+v.data,delete v.data),!1===v.cache&&(f=f.replace(Ot,"$1"),o=(St.test(f)?"&":"?")+"_="+kt+++o),v.url=f+o),v.ifModified&&(k.lastModified[f]&&T.setRequestHeader("If-Modified-Since",k.lastModified[f]),k.etag[f]&&T.setRequestHeader("If-None-Match",k.etag[f])),(v.data&&v.hasContent&&!1!==v.contentType||t.contentType)&&T.setRequestHeader("Content-Type",v.contentType),T.setRequestHeader("Accept",v.dataTypes[0]&&v.accepts[v.dataTypes[0]]?v.accepts[v.dataTypes[0]]+("*"!==v.dataTypes[0]?", "+$t+"; q=0.01":""):v.accepts["*"]),v.headers)T.setRequestHeader(i,v.headers[i]);if(v.beforeSend&&(!1===v.beforeSend.call(y,T,v)||h))return T.abort();if(u="abort",b.add(v.complete),T.done(v.success),T.fail(v.error),c=_t(Wt,v,t,T)){if(T.readyState=1,g&&m.trigger("ajaxSend",[T,v]),h)return T;v.async&&0<v.timeout&&(d=C.setTimeout(function(){T.abort("timeout")},v.timeout));try{h=!1,c.send(a,l)}catch(e){if(h)throw e;l(-1,e)}}else l(-1,"No Transport");function l(e,t,n,r){var i,o,a,s,u,l=t;h||(h=!0,d&&C.clearTimeout(d),c=void 0,p=r||"",T.readyState=0<e?4:0,i=200<=e&&e<300||304===e,n&&(s=function(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}(v,T,n)),s=function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}(v,s,T,i),i?(v.ifModified&&((u=T.getResponseHeader("Last-Modified"))&&(k.lastModified[f]=u),(u=T.getResponseHeader("etag"))&&(k.etag[f]=u)),204===e||"HEAD"===v.type?l="nocontent":304===e?l="notmodified":(l=s.state,o=s.data,i=!(a=s.error))):(a=l,!e&&l||(l="error",e<0&&(e=0))),T.status=e,T.statusText=(t||l)+"",i?x.resolveWith(y,[o,l,T]):x.rejectWith(y,[T,l,a]),T.statusCode(w),w=void 0,g&&m.trigger(i?"ajaxSuccess":"ajaxError",[T,v,i?o:a]),b.fireWith(y,[T,l]),g&&(m.trigger("ajaxComplete",[T,v]),--k.active||k.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return k.get(e,t,n,"json")},getScript:function(e,t){return k.get(e,void 0,t,"script")}}),k.each(["get","post"],function(e,i){k[i]=function(e,t,n,r){return m(t)&&(r=r||n,n=t,t=void 0),k.ajax(k.extend({url:e,type:i,dataType:r,data:t,success:n},k.isPlainObject(e)&&e))}}),k._evalUrl=function(e,t){return k.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){k.globalEval(e,t)}})},k.fn.extend({wrapAll:function(e){var t;return this[0]&&(m(e)&&(e=e.call(this[0])),t=k(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(n){return m(n)?this.each(function(e){k(this).wrapInner(n.call(this,e))}):this.each(function(){var e=k(this),t=e.contents();t.length?t.wrapAll(n):e.append(n)})},wrap:function(t){var n=m(t);return this.each(function(e){k(this).wrapAll(n?t.call(this,e):t)})},unwrap:function(e){return this.parent(e).not("body").each(function(){k(this).replaceWith(this.childNodes)}),this}}),k.expr.pseudos.hidden=function(e){return!k.expr.pseudos.visible(e)},k.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},k.ajaxSettings.xhr=function(){try{return new C.XMLHttpRequest}catch(e){}};var Ut={0:200,1223:204},Xt=k.ajaxSettings.xhr();y.cors=!!Xt&&"withCredentials"in Xt,y.ajax=Xt=!!Xt,k.ajaxTransport(function(i){var o,a;if(y.cors||Xt&&!i.crossDomain)return{send:function(e,t){var n,r=i.xhr();if(r.open(i.type,i.url,i.async,i.username,i.password),i.xhrFields)for(n in i.xhrFields)r[n]=i.xhrFields[n];for(n in i.mimeType&&r.overrideMimeType&&r.overrideMimeType(i.mimeType),i.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest"),e)r.setRequestHeader(n,e[n]);o=function(e){return function(){o&&(o=a=r.onload=r.onerror=r.onabort=r.ontimeout=r.onreadystatechange=null,"abort"===e?r.abort():"error"===e?"number"!=typeof r.status?t(0,"error"):t(r.status,r.statusText):t(Ut[r.status]||r.status,r.statusText,"text"!==(r.responseType||"text")||"string"!=typeof r.responseText?{binary:r.response}:{text:r.responseText},r.getAllResponseHeaders()))}},r.onload=o(),a=r.onerror=r.ontimeout=o("error"),void 0!==r.onabort?r.onabort=a:r.onreadystatechange=function(){4===r.readyState&&C.setTimeout(function(){o&&a()})},o=o("abort");try{r.send(i.hasContent&&i.data||null)}catch(e){if(o)throw e}},abort:function(){o&&o()}}}),k.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),k.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return k.globalEval(e),e}}}),k.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),k.ajaxTransport("script",function(n){var r,i;if(n.crossDomain||n.scriptAttrs)return{send:function(e,t){r=k("<script>").attr(n.scriptAttrs||{}).prop({charset:n.scriptCharset,src:n.url}).on("load error",i=function(e){r.remove(),i=null,e&&t("error"===e.type?404:200,e.type)}),E.head.appendChild(r[0])},abort:function(){i&&i()}}});var Vt,Gt=[],Yt=/(=)\?(?=&|$)|\?\?/;k.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Gt.pop()||k.expando+"_"+kt++;return this[e]=!0,e}}),k.ajaxPrefilter("json jsonp",function(e,t,n){var r,i,o,a=!1!==e.jsonp&&(Yt.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&Yt.test(e.data)&&"data");if(a||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=m(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,a?e[a]=e[a].replace(Yt,"$1"+r):!1!==e.jsonp&&(e.url+=(St.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return o||k.error(r+" was not called"),o[0]},e.dataTypes[0]="json",i=C[r],C[r]=function(){o=arguments},n.always(function(){void 0===i?k(C).removeProp(r):C[r]=i,e[r]&&(e.jsonpCallback=t.jsonpCallback,Gt.push(r)),o&&m(i)&&i(o[0]),o=i=void 0}),"script"}),y.createHTMLDocument=((Vt=E.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===Vt.childNodes.length),k.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(y.createHTMLDocument?((r=(t=E.implementation.createHTMLDocument("")).createElement("base")).href=E.location.href,t.head.appendChild(r)):t=E),o=!n&&[],(i=D.exec(e))?[t.createElement(i[1])]:(i=we([e],t,o),o&&o.length&&k(o).remove(),k.merge([],i.childNodes)));var r,i,o},k.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return-1<s&&(r=mt(e.slice(s)),e=e.slice(0,s)),m(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),0<a.length&&k.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?k("<div>").append(k.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},k.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){k.fn[t]=function(e){return this.on(t,e)}}),k.expr.pseudos.animated=function(t){return k.grep(k.timers,function(e){return t===e.elem}).length},k.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=k.css(e,"position"),c=k(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=k.css(e,"top"),u=k.css(e,"left"),("absolute"===l||"fixed"===l)&&-1<(o+u).indexOf("auto")?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),m(t)&&(t=t.call(e,n,k.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):c.css(f)}},k.fn.extend({offset:function(t){if(arguments.length)return void 0===t?this:this.each(function(e){k.offset.setOffset(this,t,e)});var e,n,r=this[0];return r?r.getClientRects().length?(e=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:e.top+n.pageYOffset,left:e.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===k.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===k.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=k(e).offset()).top+=k.css(e,"borderTopWidth",!0),i.left+=k.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-k.css(r,"marginTop",!0),left:t.left-i.left-k.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===k.css(e,"position"))e=e.offsetParent;return e||ie})}}),k.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,i){var o="pageYOffset"===i;k.fn[t]=function(e){return _(this,function(e,t,n){var r;if(x(e)?r=e:9===e.nodeType&&(r=e.defaultView),void 0===n)return r?r[i]:e[t];r?r.scrollTo(o?r.pageXOffset:n,o?n:r.pageYOffset):e[t]=n},t,e,arguments.length)}}),k.each(["top","left"],function(e,n){k.cssHooks[n]=ze(y.pixelPosition,function(e,t){if(t)return t=_e(e,n),$e.test(t)?k(e).position()[n]+"px":t})}),k.each({Height:"height",Width:"width"},function(a,s){k.each({padding:"inner"+a,content:s,"":"outer"+a},function(r,o){k.fn[o]=function(e,t){var n=arguments.length&&(r||"boolean"!=typeof e),i=r||(!0===e||!0===t?"margin":"border");return _(this,function(e,t,n){var r;return x(e)?0===o.indexOf("outer")?e["inner"+a]:e.document.documentElement["client"+a]:9===e.nodeType?(r=e.documentElement,Math.max(e.body["scroll"+a],r["scroll"+a],e.body["offset"+a],r["offset"+a],r["client"+a])):void 0===n?k.css(e,t,i):k.style(e,t,n,i)},s,n?e:void 0,n)}})}),k.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,n){k.fn[n]=function(e,t){return 0<arguments.length?this.on(n,null,e,t):this.trigger(n)}}),k.fn.extend({hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),k.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)}}),k.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),m(e))return r=s.call(arguments,2),(i=function(){return e.apply(t||this,r.concat(s.call(arguments)))}).guid=e.guid=e.guid||k.guid++,i},k.holdReady=function(e){e?k.readyWait++:k.ready(!0)},k.isArray=Array.isArray,k.parseJSON=JSON.parse,k.nodeName=A,k.isFunction=m,k.isWindow=x,k.camelCase=V,k.type=w,k.now=Date.now,k.isNumeric=function(e){var t=k.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},"function"==typeof define&&define.amd&&define("jquery",[],function(){return k});var Qt=C.jQuery,Jt=C.$;return k.noConflict=function(e){return C.$===k&&(C.$=Jt),e&&C.jQuery===k&&(C.jQuery=Qt),k},e||(C.jQuery=C.$=k),k});

/*! lazysizes - v4.1.8 */
!function(a,b){var c=b(a,a.document);a.lazySizes=c,"object"==typeof module&&module.exports&&(module.exports=c)}(window,function(a,b){"use strict";if(b.getElementsByClassName){var c,d,e=b.documentElement,f=a.Date,g=a.HTMLPictureElement,h="addEventListener",i="getAttribute",j=a[h],k=a.setTimeout,l=a.requestAnimationFrame||k,m=a.requestIdleCallback,n=/^picture$/i,o=["load","error","lazyincluded","_lazyloaded"],p={},q=Array.prototype.forEach,r=function(a,b){return p[b]||(p[b]=new RegExp("(\\s|^)"+b+"(\\s|$)")),p[b].test(a[i]("class")||"")&&p[b]},s=function(a,b){r(a,b)||a.setAttribute("class",(a[i]("class")||"").trim()+" "+b)},t=function(a,b){var c;(c=r(a,b))&&a.setAttribute("class",(a[i]("class")||"").replace(c," "))},u=function(a,b,c){var d=c?h:"removeEventListener";c&&u(a,b),o.forEach(function(c){a[d](c,b)})},v=function(a,d,e,f,g){var h=b.createEvent("Event");return e||(e={}),e.instance=c,h.initEvent(d,!f,!g),h.detail=e,a.dispatchEvent(h),h},w=function(b,c){var e;!g&&(e=a.picturefill||d.pf)?(c&&c.src&&!b[i]("srcset")&&b.setAttribute("srcset",c.src),e({reevaluate:!0,elements:[b]})):c&&c.src&&(b.src=c.src)},x=function(a,b){return(getComputedStyle(a,null)||{})[b]},y=function(a,b,c){for(c=c||a.offsetWidth;c<d.minSize&&b&&!a._lazysizesWidth;)c=b.offsetWidth,b=b.parentNode;return c},z=function(){var a,c,d=[],e=[],f=d,g=function(){var b=f;for(f=d.length?e:d,a=!0,c=!1;b.length;)b.shift()();a=!1},h=function(d,e){a&&!e?d.apply(this,arguments):(f.push(d),c||(c=!0,(b.hidden?k:l)(g)))};return h._lsFlush=g,h}(),A=function(a,b){return b?function(){z(a)}:function(){var b=this,c=arguments;z(function(){a.apply(b,c)})}},B=function(a){var b,c=0,e=d.throttleDelay,g=d.ricTimeout,h=function(){b=!1,c=f.now(),a()},i=m&&g>49?function(){m(h,{timeout:g}),g!==d.ricTimeout&&(g=d.ricTimeout)}:A(function(){k(h)},!0);return function(a){var d;(a=!0===a)&&(g=33),b||(b=!0,d=e-(f.now()-c),d<0&&(d=0),a||d<9?i():k(i,d))}},C=function(a){var b,c,d=99,e=function(){b=null,a()},g=function(){var a=f.now()-c;a<d?k(g,d-a):(m||e)(e)};return function(){c=f.now(),b||(b=k(g,d))}};!function(){var b,c={lazyClass:"lazyload",loadedClass:"lazyloaded",loadingClass:"lazyloading",preloadClass:"lazypreload",errorClass:"lazyerror",autosizesClass:"lazyautosizes",srcAttr:"data-src",srcsetAttr:"data-srcset",sizesAttr:"data-sizes",minSize:40,customMedia:{},init:!0,expFactor:1.5,hFac:.8,loadMode:2,loadHidden:!0,ricTimeout:0,throttleDelay:125};d=a.lazySizesConfig||a.lazysizesConfig||{};for(b in c)b in d||(d[b]=c[b]);a.lazySizesConfig=d,k(function(){d.init&&F()})}();var D=function(){var g,l,m,o,p,y,D,F,G,H,I,J,K=/^img$/i,L=/^iframe$/i,M="onscroll"in a&&!/(gle|ing)bot/.test(navigator.userAgent),N=0,O=0,P=0,Q=-1,R=function(a){P--,(!a||P<0||!a.target)&&(P=0)},S=function(a){return null==J&&(J="hidden"==x(b.body,"visibility")),J||"hidden"!=x(a.parentNode,"visibility")&&"hidden"!=x(a,"visibility")},T=function(a,c){var d,f=a,g=S(a);for(F-=c,I+=c,G-=c,H+=c;g&&(f=f.offsetParent)&&f!=b.body&&f!=e;)(g=(x(f,"opacity")||1)>0)&&"visible"!=x(f,"overflow")&&(d=f.getBoundingClientRect(),g=H>d.left&&G<d.right&&I>d.top-1&&F<d.bottom+1);return g},U=function(){var a,f,h,j,k,m,n,p,q,r,s,t,u=c.elements;if((o=d.loadMode)&&P<8&&(a=u.length)){for(f=0,Q++,r=!d.expand||d.expand<1?e.clientHeight>500&&e.clientWidth>500?500:370:d.expand,c._defEx=r,s=r*d.expFactor,t=d.hFac,J=null,O<s&&P<1&&Q>2&&o>2&&!b.hidden?(O=s,Q=0):O=o>1&&Q>1&&P<6?r:N;f<a;f++)if(u[f]&&!u[f]._lazyRace)if(M)if((p=u[f][i]("data-expand"))&&(m=1*p)||(m=O),q!==m&&(y=innerWidth+m*t,D=innerHeight+m,n=-1*m,q=m),h=u[f].getBoundingClientRect(),(I=h.bottom)>=n&&(F=h.top)<=D&&(H=h.right)>=n*t&&(G=h.left)<=y&&(I||H||G||F)&&(d.loadHidden||S(u[f]))&&(l&&P<3&&!p&&(o<3||Q<4)||T(u[f],m))){if(aa(u[f]),k=!0,P>9)break}else!k&&l&&!j&&P<4&&Q<4&&o>2&&(g[0]||d.preloadAfterLoad)&&(g[0]||!p&&(I||H||G||F||"auto"!=u[f][i](d.sizesAttr)))&&(j=g[0]||u[f]);else aa(u[f]);j&&!k&&aa(j)}},V=B(U),W=function(a){var b=a.target;if(b._lazyCache)return void delete b._lazyCache;R(a),s(b,d.loadedClass),t(b,d.loadingClass),u(b,Y),v(b,"lazyloaded")},X=A(W),Y=function(a){X({target:a.target})},Z=function(a,b){try{a.contentWindow.location.replace(b)}catch(c){a.src=b}},$=function(a){var b,c=a[i](d.srcsetAttr);(b=d.customMedia[a[i]("data-media")||a[i]("media")])&&a.setAttribute("media",b),c&&a.setAttribute("srcset",c)},_=A(function(a,b,c,e,f){var g,h,j,l,o,p;(o=v(a,"lazybeforeunveil",b)).defaultPrevented||(e&&(c?s(a,d.autosizesClass):a.setAttribute("sizes",e)),h=a[i](d.srcsetAttr),g=a[i](d.srcAttr),f&&(j=a.parentNode,l=j&&n.test(j.nodeName||"")),p=b.firesLoad||"src"in a&&(h||g||l),o={target:a},s(a,d.loadingClass),p&&(clearTimeout(m),m=k(R,2500),u(a,Y,!0)),l&&q.call(j.getElementsByTagName("source"),$),h?a.setAttribute("srcset",h):g&&!l&&(L.test(a.nodeName)?Z(a,g):a.src=g),f&&(h||l)&&w(a,{src:g})),a._lazyRace&&delete a._lazyRace,t(a,d.lazyClass),z(function(){var b=a.complete&&a.naturalWidth>1;p&&!b||(b&&s(a,"ls-is-cached"),W(o),a._lazyCache=!0,k(function(){"_lazyCache"in a&&delete a._lazyCache},9))},!0)}),aa=function(a){var b,c=K.test(a.nodeName),e=c&&(a[i](d.sizesAttr)||a[i]("sizes")),f="auto"==e;(!f&&l||!c||!a[i]("src")&&!a.srcset||a.complete||r(a,d.errorClass)||!r(a,d.lazyClass))&&(b=v(a,"lazyunveilread").detail,f&&E.updateElem(a,!0,a.offsetWidth),a._lazyRace=!0,P++,_(a,b,f,e,c))},ba=function(){if(!l){if(f.now()-p<999)return void k(ba,999);var a=C(function(){d.loadMode=3,V()});l=!0,d.loadMode=3,V(),j("scroll",function(){3==d.loadMode&&(d.loadMode=2),a()},!0)}};return{_:function(){p=f.now(),c.elements=b.getElementsByClassName(d.lazyClass),g=b.getElementsByClassName(d.lazyClass+" "+d.preloadClass),j("scroll",V,!0),j("resize",V,!0),a.MutationObserver?new MutationObserver(V).observe(e,{childList:!0,subtree:!0,attributes:!0}):(e[h]("DOMNodeInserted",V,!0),e[h]("DOMAttrModified",V,!0),setInterval(V,999)),j("hashchange",V,!0),["focus","mouseover","click","load","transitionend","animationend","webkitAnimationEnd"].forEach(function(a){b[h](a,V,!0)}),/d$|^c/.test(b.readyState)?ba():(j("load",ba),b[h]("DOMContentLoaded",V),k(ba,2e4)),c.elements.length?(U(),z._lsFlush()):V()},checkElems:V,unveil:aa}}(),E=function(){var a,c=A(function(a,b,c,d){var e,f,g;if(a._lazysizesWidth=d,d+="px",a.setAttribute("sizes",d),n.test(b.nodeName||""))for(e=b.getElementsByTagName("source"),f=0,g=e.length;f<g;f++)e[f].setAttribute("sizes",d);c.detail.dataAttr||w(a,c.detail)}),e=function(a,b,d){var e,f=a.parentNode;f&&(d=y(a,f,d),e=v(a,"lazybeforesizes",{width:d,dataAttr:!!b}),e.defaultPrevented||(d=e.detail.width)&&d!==a._lazysizesWidth&&c(a,f,e,d))},f=function(){var b,c=a.length;if(c)for(b=0;b<c;b++)e(a[b])},g=C(f);return{_:function(){a=b.getElementsByClassName(d.autosizesClass),j("resize",g)},checkElems:g,updateElem:e}}(),F=function(){F.i||(F.i=!0,E._(),D._())};return c={cfg:d,autoSizer:E,loader:D,init:F,uP:w,aC:s,rC:t,hC:r,fire:v,gW:y,rAF:z}}});
/* mousetrap v1.6.3 craig.is/killing/mice */
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
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.scrollLock=t():e.scrollLock=t()}(this,function(){return function(l){var o={};function r(e){if(o[e])return o[e].exports;var t=o[e]={i:e,l:!1,exports:{}};return l[e].call(t.exports,t,t.exports,r),t.l=!0,t.exports}return r.m=l,r.c=o,r.d=function(e,t,l){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:l})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var l=Object.create(null);if(r.r(l),Object.defineProperty(l,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)r.d(l,o,function(e){return t[e]}.bind(null,o));return l},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=0)}([function(e,t,l){"use strict";l.r(t);var o=function(e){return Array.isArray(e)?e:[e]},a=function(e){return e instanceof Node},r=function(e,t){if(e&&t){e=e instanceof NodeList?e:[e];for(var l=0;l<e.length&&!0!==t(e[l],l,e.length);l++);}},n=function(e){return console.error("[scroll-lock] ".concat(e))},h=function(e){if(Array.isArray(e))return e.join(", ")},c=function(e){var t=[];return r(e,function(e){return t.push(e)}),t},v=function(e,t){var l=!(2<arguments.length&&void 0!==arguments[2])||arguments[2],o=3<arguments.length&&void 0!==arguments[3]?arguments[3]:document;if(l&&-1!==c(o.querySelectorAll(t)).indexOf(e))return e;for(;(e=e.parentElement)&&-1===c(o.querySelectorAll(t)).indexOf(e););return e},b=function(e,t){var l=2<arguments.length&&void 0!==arguments[2]?arguments[2]:document;return-1!==c(l.querySelectorAll(t)).indexOf(e)},i=function(e){if(e)return"hidden"===getComputedStyle(e).overflow},S=function(e){if(e)return!!i(e)||e.scrollTop<=0},m=function(e){if(e){if(i(e))return!0;var t=e.scrollTop;return e.scrollHeight<=t+e.offsetHeight}},y=function(e){if(e)return!!i(e)||e.scrollLeft<=0},k=function(e){if(e){if(i(e))return!0;var t=e.scrollLeft;return e.scrollWidth<=t+e.offsetWidth}};l.d(t,"disablePageScroll",function(){return d}),l.d(t,"enablePageScroll",function(){return s}),l.d(t,"getScrollState",function(){return f}),l.d(t,"clearQueueScrollLocks",function(){return p}),l.d(t,"getTargetScrollBarWidth",function(){return g}),l.d(t,"getCurrentTargetScrollBarWidth",function(){return w}),l.d(t,"getPageScrollBarWidth",function(){return G}),l.d(t,"getCurrentPageScrollBarWidth",function(){return F}),l.d(t,"addScrollableTarget",function(){return T}),l.d(t,"removeScrollableTarget",function(){return P}),l.d(t,"addScrollableSelector",function(){return O}),l.d(t,"removeScrollableSelector",function(){return W}),l.d(t,"addLockableTarget",function(){return Y}),l.d(t,"addLockableSelector",function(){return x}),l.d(t,"setFillGapMethod",function(){return E}),l.d(t,"addFillGapTarget",function(){return j}),l.d(t,"removeFillGapTarget",function(){return M}),l.d(t,"addFillGapSelector",function(){return q}),l.d(t,"removeFillGapSelector",function(){return C}),l.d(t,"refillGaps",function(){return A});var u=["padding","margin","width","max-width","none"],L={scroll:!0,queue:0,scrollableSelectors:["[data-scroll-lock-scrollable]"],lockableSelectors:["body","[data-scroll-lock-lockable]"],fillGapSelectors:["body","[data-scroll-lock-fill-gap]","[data-scroll-lock-lockable]"],fillGapMethod:u[0],startTouchY:0,startTouchX:0},d=function(e){L.queue<=0&&(L.scroll=!1,N(),I()),T(e),L.queue++},s=function(e){0<L.queue&&L.queue--,L.queue<=0&&(L.scroll=!0,B(),X()),P(e)},f=function(){return L.scroll},p=function(){L.queue=0},g=function(e){var t=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(a(e)){var l=e.style.overflowY;t?f()||(e.style.overflowY=e.dataset.scrollLockSavedOverflowYProperty):e.style.overflowY="scroll";var o=w(e);return e.style.overflowY=l,o}return 0},w=function(e){if(a(e)){if(e===document.body){var t=document.documentElement.clientWidth;return window.innerWidth-t}var l=e.style.borderLeftWidth,o=e.style.borderRightWidth;e.style.borderLeftWidth="0px",e.style.borderRightWidth="0px";var r=e.offsetWidth-e.clientWidth;return e.style.borderLeftWidth=l,e.style.borderRightWidth=o,r}return 0},G=function(){var e=0<arguments.length&&void 0!==arguments[0]&&arguments[0];return g(document.body,e)},F=function(){return w(document.body)},T=function(e){e&&o(e).map(function(e){r(e,function(e){a(e)?e.dataset.scrollLockScrollable="":n('"'.concat(e,'" is not a Element.'))})})},P=function(e){e&&o(e).map(function(e){r(e,function(e){a(e)?delete e.dataset.scrollLockScrollable:n('"'.concat(e,'" is not a Element.'))})})},O=function(e){e&&o(e).map(function(e){L.scrollableSelectors.push(e)})},W=function(e){e&&o(e).map(function(t){L.scrollableSelectors=L.scrollableSelectors.filter(function(e){return e!==t})})},Y=function(e){e&&(o(e).map(function(e){r(e,function(e){a(e)?e.dataset.scrollLockLockable="":n('"'.concat(e,'" is not a Element.'))})}),f()||N())},x=function(e){e&&(o(e).map(function(e){L.lockableSelectors.push(e)}),f()||N(),q(e))},E=function(e){if(e)if(-1!==u.indexOf(e))L.fillGapMethod=e,A();else{var t=u.join(", ");n('"'.concat(e,'" method is not available!\nAvailable fill gap methods: ').concat(t,"."))}},j=function(e){e&&o(e).map(function(e){r(e,function(e){a(e)?(e.dataset.scrollLockFillGap="",L.scroll||D(e)):n('"'.concat(e,'" is not a Element.'))})})},M=function(e){e&&o(e).map(function(e){r(e,function(e){a(e)?(delete e.dataset.scrollLockFillGap,L.scroll||z(e)):n('"'.concat(e,'" is not a Element.'))})})},q=function(e){e&&o(e).map(function(e){L.fillGapSelectors.push(e),L.scroll||Q(e)})},C=function(e){e&&o(e).map(function(t){L.fillGapSelectors=L.fillGapSelectors.filter(function(e){return e!==t}),L.scroll||H(t)})},A=function(){L.scroll||I()},N=function(){var e=h(L.lockableSelectors);K(e)},B=function(){var e=h(L.lockableSelectors);R(e)},K=function(e){var t=document.querySelectorAll(e);r(t,function(e){U(e)})},R=function(e){var t=document.querySelectorAll(e);r(t,function(e){_(e)})},U=function(e){if(a(e)&&"true"!==e.dataset.scrollLockLocked){var t=window.getComputedStyle(e);e.dataset.scrollLockSavedOverflowYProperty=t.overflowY,e.dataset.scrollLockSavedInlineOverflowProperty=e.style.overflow,e.dataset.scrollLockSavedInlineOverflowYProperty=e.style.overflowY,e.style.overflow="hidden",e.dataset.scrollLockLocked="true"}},_=function(e){a(e)&&"true"===e.dataset.scrollLockLocked&&(e.style.overflow=e.dataset.scrollLockSavedInlineOverflowProperty,e.style.overflowY=e.dataset.scrollLockSavedInlineOverflowYProperty,delete e.dataset.scrollLockSavedOverflowYProperty,delete e.dataset.scrollLockSavedInlineOverflowProperty,delete e.dataset.scrollLockSavedInlineOverflowYProperty,delete e.dataset.scrollLockLocked)},I=function(){L.fillGapSelectors.map(function(e){Q(e)})},X=function(){L.fillGapSelectors.map(function(e){H(e)})},Q=function(e){var t=document.querySelectorAll(e),l=-1!==L.lockableSelectors.indexOf(e);r(t,function(e){D(e,l)})},D=function(e){var t=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(a(e)){var l;if(""===e.dataset.scrollLockLockable||t)l=g(e,!0);else{var o=v(e,h(L.lockableSelectors));l=g(o,!0)}"true"===e.dataset.scrollLockFilledGap&&z(e);var r=window.getComputedStyle(e);if(e.dataset.scrollLockFilledGap="true",e.dataset.scrollLockCurrentFillGapMethod=L.fillGapMethod,"margin"===L.fillGapMethod){var n=parseFloat(r.marginRight);e.style.marginRight="".concat(n+l,"px")}else if("width"===L.fillGapMethod)e.style.width="calc(100% - ".concat(l,"px)");else if("max-width"===L.fillGapMethod)e.style.maxWidth="calc(100% - ".concat(l,"px)");else if("padding"===L.fillGapMethod){var c=parseFloat(r.paddingRight);e.style.paddingRight="".concat(c+l,"px")}}},H=function(e){var t=document.querySelectorAll(e);r(t,function(e){z(e)})},z=function(e){if(a(e)&&"true"===e.dataset.scrollLockFilledGap){var t=e.dataset.scrollLockCurrentFillGapMethod;delete e.dataset.scrollLockFilledGap,delete e.dataset.scrollLockCurrentFillGapMethod,"margin"===t?e.style.marginRight="":"width"===t?e.style.width="":"max-width"===t?e.style.maxWidth="":"padding"===t&&(e.style.paddingRight="")}};"undefined"!=typeof window&&window.addEventListener("resize",function(e){A()}),"undefined"!=typeof document&&(document.addEventListener("touchstart",function(e){L.scroll||(L.startTouchY=e.touches[0].clientY,L.startTouchX=e.touches[0].clientX)}),document.addEventListener("touchmove",function(n){if(!L.scroll){var e=L.startTouchY,t=L.startTouchX,l=n.touches[0].clientY,o=n.touches[0].clientX;if(n.touches.length<2){var c=h(L.scrollableSelectors),a=e<l,i=l<e,u=t<o,d=o<t,s=e+3<l,f=l<e-3,p=t+3<o,g=o<t-3;!function e(t){var l=1<arguments.length&&void 0!==arguments[1]&&arguments[1];if(t){var o=v(t,c,!1);if(b(t,'input[type="range"]'))return!1;if(l||b(t,'textarea, [contenteditable="true"]')&&v(t,c)||b(t,c)){var r=!1;y(t)&&k(t)?(a&&S(t)||i&&m(t))&&(r=!0):S(t)&&m(t)?(u&&y(t)||d&&k(t))&&(r=!0):(s&&S(t)||f&&m(t)||p&&y(t)||g&&k(t))&&(r=!0),r&&(o?e(o,!0):n.preventDefault())}else e(o)}else n.preventDefault()}(n.target)}}},{passive:!1}),document.addEventListener("touchend",function(e){L.scroll||(L.startTouchY=0,L.startTouchX=0)}));var J={hide:function(e){n('"hide" is deprecated! Use "disablePageScroll" instead. \n https://github.com/FL3NKEY/scroll-lock#disablepagescrollscrollabletarget'),d(e)},show:function(e){n('"show" is deprecated! Use "enablePageScroll" instead. \n https://github.com/FL3NKEY/scroll-lock#enablepagescrollscrollabletarget'),s(e)},toggle:function(e){n('"toggle" is deprecated! Do not use it.'),f()?d():s(e)},getState:function(){return n('"getState" is deprecated! Use "getScrollState" instead. \n https://github.com/FL3NKEY/scroll-lock#getscrollstate'),f()},getWidth:function(){return n('"getWidth" is deprecated! Use "getPageScrollBarWidth" instead. \n https://github.com/FL3NKEY/scroll-lock#getpagescrollbarwidth'),G()},getCurrentWidth:function(){return n('"getCurrentWidth" is deprecated! Use "getCurrentPageScrollBarWidth" instead. \n https://github.com/FL3NKEY/scroll-lock#getcurrentpagescrollbarwidth'),F()},setScrollableTargets:function(e){n('"setScrollableTargets" is deprecated! Use "addScrollableTarget" instead. \n https://github.com/FL3NKEY/scroll-lock#addscrollabletargetscrollabletarget'),T(e)},setFillGapSelectors:function(e){n('"setFillGapSelectors" is deprecated! Use "addFillGapSelector" instead. \n https://github.com/FL3NKEY/scroll-lock#addfillgapselectorfillgapselector'),q(e)},setFillGapTargets:function(e){n('"setFillGapTargets" is deprecated! Use "addFillGapTarget" instead. \n https://github.com/FL3NKEY/scroll-lock#addfillgaptargetfillgaptarget'),j(e)},clearQueue:function(){n('"clearQueue" is deprecated! Use "clearQueueScrollLocks" instead. \n https://github.com/FL3NKEY/scroll-lock#clearqueuescrolllocks'),p()}},V=function(r){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{},t=Object.keys(n);"function"==typeof Object.getOwnPropertySymbols&&(t=t.concat(Object.getOwnPropertySymbols(n).filter(function(e){return Object.getOwnPropertyDescriptor(n,e).enumerable}))),t.forEach(function(e){var t,l,o;t=r,o=n[l=e],l in t?Object.defineProperty(t,l,{value:o,enumerable:!0,configurable:!0,writable:!0}):t[l]=o})}return r}({disablePageScroll:d,enablePageScroll:s,getScrollState:f,clearQueueScrollLocks:p,getTargetScrollBarWidth:g,getCurrentTargetScrollBarWidth:w,getPageScrollBarWidth:G,getCurrentPageScrollBarWidth:F,addScrollableSelector:O,removeScrollableSelector:W,addScrollableTarget:T,removeScrollableTarget:P,addLockableSelector:x,addLockableTarget:Y,addFillGapSelector:q,removeFillGapSelector:C,addFillGapTarget:j,removeFillGapTarget:M,setFillGapMethod:E,refillGaps:A,_state:L},J);t.default=V}]).default});
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
 * Leaflet 1.6.0, a JS library for interactive maps. http://leafletjs.com
 * (c) 2010-2019 Vladimir Agafonkin, (c) 2010-2011 CloudMade
 */
!function(t,i){"object"==typeof exports&&"undefined"!=typeof module?i(exports):"function"==typeof define&&define.amd?define(["exports"],i):i(t.L={})}(this,function(t){"use strict";var i=Object.freeze;function h(t){var i,e,n,o;for(e=1,n=arguments.length;e<n;e++)for(i in o=arguments[e])t[i]=o[i];return t}Object.freeze=function(t){return t};var s=Object.create||function(t){return e.prototype=t,new e};function e(){}function a(t,i){var e=Array.prototype.slice;if(t.bind)return t.bind.apply(t,e.call(arguments,1));var n=e.call(arguments,2);return function(){return t.apply(i,n.length?n.concat(e.call(arguments)):arguments)}}var n=0;function u(t){return t._leaflet_id=t._leaflet_id||++n,t._leaflet_id}function o(t,i,e){var n,o,s,r;return r=function(){n=!1,o&&(s.apply(e,o),o=!1)},s=function(){n?o=arguments:(t.apply(e,arguments),setTimeout(r,i),n=!0)}}function r(t,i,e){var n=i[1],o=i[0],s=n-o;return t===n&&e?t:((t-o)%s+s)%s+o}function l(){return!1}function c(t,i){var e=Math.pow(10,void 0===i?6:i);return Math.round(t*e)/e}function _(t){return t.trim?t.trim():t.replace(/^\s+|\s+$/g,"")}function d(t){return _(t).split(/\s+/)}function p(t,i){for(var e in t.hasOwnProperty("options")||(t.options=t.options?s(t.options):{}),i)t.options[e]=i[e];return t.options}function m(t,i,e){var n=[];for(var o in t)n.push(encodeURIComponent(e?o.toUpperCase():o)+"="+encodeURIComponent(t[o]));return(i&&-1!==i.indexOf("?")?"&":"?")+n.join("&")}var f=/\{ *([\w_-]+) *\}/g;function g(t,n){return t.replace(f,function(t,i){var e=n[i];if(void 0===e)throw new Error("No value provided for variable "+t);return"function"==typeof e&&(e=e(n)),e})}var v=Array.isArray||function(t){return"[object Array]"===Object.prototype.toString.call(t)};function y(t,i){for(var e=0;e<t.length;e++)if(t[e]===i)return e;return-1}var x="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";function w(t){return window["webkit"+t]||window["moz"+t]||window["ms"+t]}var P=0;function b(t){var i=+new Date,e=Math.max(0,16-(i-P));return P=i+e,window.setTimeout(t,e)}var T=window.requestAnimationFrame||w("RequestAnimationFrame")||b,z=window.cancelAnimationFrame||w("CancelAnimationFrame")||w("CancelRequestAnimationFrame")||function(t){window.clearTimeout(t)};function M(t,i,e){if(!e||T!==b)return T.call(window,a(t,i));t.call(i)}function C(t){t&&z.call(window,t)}var S=(Object.freeze||Object)({freeze:i,extend:h,create:s,bind:a,lastId:n,stamp:u,throttle:o,wrapNum:r,falseFn:l,formatNum:c,trim:_,splitWords:d,setOptions:p,getParamString:m,template:g,isArray:v,indexOf:y,emptyImageUrl:x,requestFn:T,cancelFn:z,requestAnimFrame:M,cancelAnimFrame:C});function E(){}E.extend=function(t){function i(){this.initialize&&this.initialize.apply(this,arguments),this.callInitHooks()}var e=i.__super__=this.prototype,n=s(e);for(var o in(n.constructor=i).prototype=n,this)this.hasOwnProperty(o)&&"prototype"!==o&&"__super__"!==o&&(i[o]=this[o]);return t.statics&&(h(i,t.statics),delete t.statics),t.includes&&(function(t){if("undefined"==typeof L||!L||!L.Mixin)return;t=v(t)?t:[t];for(var i=0;i<t.length;i++)t[i]===L.Mixin.Events&&console.warn("Deprecated include of L.Mixin.Events: this property will be removed in future releases, please inherit from L.Evented instead.",(new Error).stack)}(t.includes),h.apply(null,[n].concat(t.includes)),delete t.includes),n.options&&(t.options=h(s(n.options),t.options)),h(n,t),n._initHooks=[],n.callInitHooks=function(){if(!this._initHooksCalled){e.callInitHooks&&e.callInitHooks.call(this),this._initHooksCalled=!0;for(var t=0,i=n._initHooks.length;t<i;t++)n._initHooks[t].call(this)}},i},E.include=function(t){return h(this.prototype,t),this},E.mergeOptions=function(t){return h(this.prototype.options,t),this},E.addInitHook=function(t){var i=Array.prototype.slice.call(arguments,1),e="function"==typeof t?t:function(){this[t].apply(this,i)};return this.prototype._initHooks=this.prototype._initHooks||[],this.prototype._initHooks.push(e),this};var Z={on:function(t,i,e){if("object"==typeof t)for(var n in t)this._on(n,t[n],i);else for(var o=0,s=(t=d(t)).length;o<s;o++)this._on(t[o],i,e);return this},off:function(t,i,e){if(t)if("object"==typeof t)for(var n in t)this._off(n,t[n],i);else for(var o=0,s=(t=d(t)).length;o<s;o++)this._off(t[o],i,e);else delete this._events;return this},_on:function(t,i,e){this._events=this._events||{};var n=this._events[t];n||(n=[],this._events[t]=n),e===this&&(e=void 0);for(var o={fn:i,ctx:e},s=n,r=0,a=s.length;r<a;r++)if(s[r].fn===i&&s[r].ctx===e)return;s.push(o)},_off:function(t,i,e){var n,o,s;if(this._events&&(n=this._events[t]))if(i){if(e===this&&(e=void 0),n)for(o=0,s=n.length;o<s;o++){var r=n[o];if(r.ctx===e&&r.fn===i)return r.fn=l,this._firingCount&&(this._events[t]=n=n.slice()),void n.splice(o,1)}}else{for(o=0,s=n.length;o<s;o++)n[o].fn=l;delete this._events[t]}},fire:function(t,i,e){if(!this.listens(t,e))return this;var n=h({},i,{type:t,target:this,sourceTarget:i&&i.sourceTarget||this});if(this._events){var o=this._events[t];if(o){this._firingCount=this._firingCount+1||1;for(var s=0,r=o.length;s<r;s++){var a=o[s];a.fn.call(a.ctx||this,n)}this._firingCount--}}return e&&this._propagateEvent(n),this},listens:function(t,i){var e=this._events&&this._events[t];if(e&&e.length)return!0;if(i)for(var n in this._eventParents)if(this._eventParents[n].listens(t,i))return!0;return!1},once:function(t,i,e){if("object"==typeof t){for(var n in t)this.once(n,t[n],i);return this}var o=a(function(){this.off(t,i,e).off(t,o,e)},this);return this.on(t,i,e).on(t,o,e)},addEventParent:function(t){return this._eventParents=this._eventParents||{},this._eventParents[u(t)]=t,this},removeEventParent:function(t){return this._eventParents&&delete this._eventParents[u(t)],this},_propagateEvent:function(t){for(var i in this._eventParents)this._eventParents[i].fire(t.type,h({layer:t.target,propagatedFrom:t.target},t),!0)}};Z.addEventListener=Z.on,Z.removeEventListener=Z.clearAllEventListeners=Z.off,Z.addOneTimeEventListener=Z.once,Z.fireEvent=Z.fire,Z.hasEventListeners=Z.listens;var k=E.extend(Z);function B(t,i,e){this.x=e?Math.round(t):t,this.y=e?Math.round(i):i}var A=Math.trunc||function(t){return 0<t?Math.floor(t):Math.ceil(t)};function I(t,i,e){return t instanceof B?t:v(t)?new B(t[0],t[1]):null==t?t:"object"==typeof t&&"x"in t&&"y"in t?new B(t.x,t.y):new B(t,i,e)}function O(t,i){if(t)for(var e=i?[t,i]:t,n=0,o=e.length;n<o;n++)this.extend(e[n])}function R(t,i){return!t||t instanceof O?t:new O(t,i)}function N(t,i){if(t)for(var e=i?[t,i]:t,n=0,o=e.length;n<o;n++)this.extend(e[n])}function D(t,i){return t instanceof N?t:new N(t,i)}function j(t,i,e){if(isNaN(t)||isNaN(i))throw new Error("Invalid LatLng object: ("+t+", "+i+")");this.lat=+t,this.lng=+i,void 0!==e&&(this.alt=+e)}function W(t,i,e){return t instanceof j?t:v(t)&&"object"!=typeof t[0]?3===t.length?new j(t[0],t[1],t[2]):2===t.length?new j(t[0],t[1]):null:null==t?t:"object"==typeof t&&"lat"in t?new j(t.lat,"lng"in t?t.lng:t.lon,t.alt):void 0===i?null:new j(t,i,e)}B.prototype={clone:function(){return new B(this.x,this.y)},add:function(t){return this.clone()._add(I(t))},_add:function(t){return this.x+=t.x,this.y+=t.y,this},subtract:function(t){return this.clone()._subtract(I(t))},_subtract:function(t){return this.x-=t.x,this.y-=t.y,this},divideBy:function(t){return this.clone()._divideBy(t)},_divideBy:function(t){return this.x/=t,this.y/=t,this},multiplyBy:function(t){return this.clone()._multiplyBy(t)},_multiplyBy:function(t){return this.x*=t,this.y*=t,this},scaleBy:function(t){return new B(this.x*t.x,this.y*t.y)},unscaleBy:function(t){return new B(this.x/t.x,this.y/t.y)},round:function(){return this.clone()._round()},_round:function(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this},floor:function(){return this.clone()._floor()},_floor:function(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this},ceil:function(){return this.clone()._ceil()},_ceil:function(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this},trunc:function(){return this.clone()._trunc()},_trunc:function(){return this.x=A(this.x),this.y=A(this.y),this},distanceTo:function(t){var i=(t=I(t)).x-this.x,e=t.y-this.y;return Math.sqrt(i*i+e*e)},equals:function(t){return(t=I(t)).x===this.x&&t.y===this.y},contains:function(t){return t=I(t),Math.abs(t.x)<=Math.abs(this.x)&&Math.abs(t.y)<=Math.abs(this.y)},toString:function(){return"Point("+c(this.x)+", "+c(this.y)+")"}},O.prototype={extend:function(t){return t=I(t),this.min||this.max?(this.min.x=Math.min(t.x,this.min.x),this.max.x=Math.max(t.x,this.max.x),this.min.y=Math.min(t.y,this.min.y),this.max.y=Math.max(t.y,this.max.y)):(this.min=t.clone(),this.max=t.clone()),this},getCenter:function(t){return new B((this.min.x+this.max.x)/2,(this.min.y+this.max.y)/2,t)},getBottomLeft:function(){return new B(this.min.x,this.max.y)},getTopRight:function(){return new B(this.max.x,this.min.y)},getTopLeft:function(){return this.min},getBottomRight:function(){return this.max},getSize:function(){return this.max.subtract(this.min)},contains:function(t){var i,e;return(t="number"==typeof t[0]||t instanceof B?I(t):R(t))instanceof O?(i=t.min,e=t.max):i=e=t,i.x>=this.min.x&&e.x<=this.max.x&&i.y>=this.min.y&&e.y<=this.max.y},intersects:function(t){t=R(t);var i=this.min,e=this.max,n=t.min,o=t.max,s=o.x>=i.x&&n.x<=e.x,r=o.y>=i.y&&n.y<=e.y;return s&&r},overlaps:function(t){t=R(t);var i=this.min,e=this.max,n=t.min,o=t.max,s=o.x>i.x&&n.x<e.x,r=o.y>i.y&&n.y<e.y;return s&&r},isValid:function(){return!(!this.min||!this.max)}},N.prototype={extend:function(t){var i,e,n=this._southWest,o=this._northEast;if(t instanceof j)e=i=t;else{if(!(t instanceof N))return t?this.extend(W(t)||D(t)):this;if(i=t._southWest,e=t._northEast,!i||!e)return this}return n||o?(n.lat=Math.min(i.lat,n.lat),n.lng=Math.min(i.lng,n.lng),o.lat=Math.max(e.lat,o.lat),o.lng=Math.max(e.lng,o.lng)):(this._southWest=new j(i.lat,i.lng),this._northEast=new j(e.lat,e.lng)),this},pad:function(t){var i=this._southWest,e=this._northEast,n=Math.abs(i.lat-e.lat)*t,o=Math.abs(i.lng-e.lng)*t;return new N(new j(i.lat-n,i.lng-o),new j(e.lat+n,e.lng+o))},getCenter:function(){return new j((this._southWest.lat+this._northEast.lat)/2,(this._southWest.lng+this._northEast.lng)/2)},getSouthWest:function(){return this._southWest},getNorthEast:function(){return this._northEast},getNorthWest:function(){return new j(this.getNorth(),this.getWest())},getSouthEast:function(){return new j(this.getSouth(),this.getEast())},getWest:function(){return this._southWest.lng},getSouth:function(){return this._southWest.lat},getEast:function(){return this._northEast.lng},getNorth:function(){return this._northEast.lat},contains:function(t){t="number"==typeof t[0]||t instanceof j||"lat"in t?W(t):D(t);var i,e,n=this._southWest,o=this._northEast;return t instanceof N?(i=t.getSouthWest(),e=t.getNorthEast()):i=e=t,i.lat>=n.lat&&e.lat<=o.lat&&i.lng>=n.lng&&e.lng<=o.lng},intersects:function(t){t=D(t);var i=this._southWest,e=this._northEast,n=t.getSouthWest(),o=t.getNorthEast(),s=o.lat>=i.lat&&n.lat<=e.lat,r=o.lng>=i.lng&&n.lng<=e.lng;return s&&r},overlaps:function(t){t=D(t);var i=this._southWest,e=this._northEast,n=t.getSouthWest(),o=t.getNorthEast(),s=o.lat>i.lat&&n.lat<e.lat,r=o.lng>i.lng&&n.lng<e.lng;return s&&r},toBBoxString:function(){return[this.getWest(),this.getSouth(),this.getEast(),this.getNorth()].join(",")},equals:function(t,i){return!!t&&(t=D(t),this._southWest.equals(t.getSouthWest(),i)&&this._northEast.equals(t.getNorthEast(),i))},isValid:function(){return!(!this._southWest||!this._northEast)}};var H,F={latLngToPoint:function(t,i){var e=this.projection.project(t),n=this.scale(i);return this.transformation._transform(e,n)},pointToLatLng:function(t,i){var e=this.scale(i),n=this.transformation.untransform(t,e);return this.projection.unproject(n)},project:function(t){return this.projection.project(t)},unproject:function(t){return this.projection.unproject(t)},scale:function(t){return 256*Math.pow(2,t)},zoom:function(t){return Math.log(t/256)/Math.LN2},getProjectedBounds:function(t){if(this.infinite)return null;var i=this.projection.bounds,e=this.scale(t);return new O(this.transformation.transform(i.min,e),this.transformation.transform(i.max,e))},infinite:!(j.prototype={equals:function(t,i){return!!t&&(t=W(t),Math.max(Math.abs(this.lat-t.lat),Math.abs(this.lng-t.lng))<=(void 0===i?1e-9:i))},toString:function(t){return"LatLng("+c(this.lat,t)+", "+c(this.lng,t)+")"},distanceTo:function(t){return U.distance(this,W(t))},wrap:function(){return U.wrapLatLng(this)},toBounds:function(t){var i=180*t/40075017,e=i/Math.cos(Math.PI/180*this.lat);return D([this.lat-i,this.lng-e],[this.lat+i,this.lng+e])},clone:function(){return new j(this.lat,this.lng,this.alt)}}),wrapLatLng:function(t){var i=this.wrapLng?r(t.lng,this.wrapLng,!0):t.lng;return new j(this.wrapLat?r(t.lat,this.wrapLat,!0):t.lat,i,t.alt)},wrapLatLngBounds:function(t){var i=t.getCenter(),e=this.wrapLatLng(i),n=i.lat-e.lat,o=i.lng-e.lng;if(0==n&&0==o)return t;var s=t.getSouthWest(),r=t.getNorthEast();return new N(new j(s.lat-n,s.lng-o),new j(r.lat-n,r.lng-o))}},U=h({},F,{wrapLng:[-180,180],R:6371e3,distance:function(t,i){var e=Math.PI/180,n=t.lat*e,o=i.lat*e,s=Math.sin((i.lat-t.lat)*e/2),r=Math.sin((i.lng-t.lng)*e/2),a=s*s+Math.cos(n)*Math.cos(o)*r*r,h=2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));return this.R*h}}),V=6378137,q={R:V,MAX_LATITUDE:85.0511287798,project:function(t){var i=Math.PI/180,e=this.MAX_LATITUDE,n=Math.max(Math.min(e,t.lat),-e),o=Math.sin(n*i);return new B(this.R*t.lng*i,this.R*Math.log((1+o)/(1-o))/2)},unproject:function(t){var i=180/Math.PI;return new j((2*Math.atan(Math.exp(t.y/this.R))-Math.PI/2)*i,t.x*i/this.R)},bounds:(H=V*Math.PI,new O([-H,-H],[H,H]))};function G(t,i,e,n){if(v(t))return this._a=t[0],this._b=t[1],this._c=t[2],void(this._d=t[3]);this._a=t,this._b=i,this._c=e,this._d=n}function K(t,i,e,n){return new G(t,i,e,n)}G.prototype={transform:function(t,i){return this._transform(t.clone(),i)},_transform:function(t,i){return i=i||1,t.x=i*(this._a*t.x+this._b),t.y=i*(this._c*t.y+this._d),t},untransform:function(t,i){return i=i||1,new B((t.x/i-this._b)/this._a,(t.y/i-this._d)/this._c)}};var Y,X=h({},U,{code:"EPSG:3857",projection:q,transformation:(Y=.5/(Math.PI*q.R),K(Y,.5,-Y,.5))}),J=h({},X,{code:"EPSG:900913"});function $(t){return document.createElementNS("http://www.w3.org/2000/svg",t)}function Q(t,i){var e,n,o,s,r,a,h="";for(e=0,o=t.length;e<o;e++){for(n=0,s=(r=t[e]).length;n<s;n++)h+=(n?"L":"M")+(a=r[n]).x+" "+a.y;h+=i?Zt?"z":"x":""}return h||"M0 0"}var tt=document.documentElement.style,it="ActiveXObject"in window,et=it&&!document.addEventListener,nt="msLaunchUri"in navigator&&!("documentMode"in document),ot=Bt("webkit"),st=Bt("android"),rt=Bt("android 2")||Bt("android 3"),at=parseInt(/WebKit\/([0-9]+)|$/.exec(navigator.userAgent)[1],10),ht=st&&Bt("Google")&&at<537&&!("AudioNode"in window),ut=!!window.opera,lt=Bt("chrome"),ct=Bt("gecko")&&!ot&&!ut&&!it,_t=!lt&&Bt("safari"),dt=Bt("phantom"),pt="OTransition"in tt,mt=0===navigator.platform.indexOf("Win"),ft=it&&"transition"in tt,gt="WebKitCSSMatrix"in window&&"m11"in new window.WebKitCSSMatrix&&!rt,vt="MozPerspective"in tt,yt=!window.L_DISABLE_3D&&(ft||gt||vt)&&!pt&&!dt,xt="undefined"!=typeof orientation||Bt("mobile"),wt=xt&&ot,Pt=xt&&gt,Lt=!window.PointerEvent&&window.MSPointerEvent,bt=!(ot||!window.PointerEvent&&!Lt),Tt=!window.L_NO_TOUCH&&(bt||"ontouchstart"in window||window.DocumentTouch&&document instanceof window.DocumentTouch),zt=xt&&ut,Mt=xt&&ct,Ct=1<(window.devicePixelRatio||window.screen.deviceXDPI/window.screen.logicalXDPI),St=function(){var t=!1;try{var i=Object.defineProperty({},"passive",{get:function(){t=!0}});window.addEventListener("testPassiveEventSupport",l,i),window.removeEventListener("testPassiveEventSupport",l,i)}catch(t){}return t},Et=!!document.createElement("canvas").getContext,Zt=!(!document.createElementNS||!$("svg").createSVGRect),kt=!Zt&&function(){try{var t=document.createElement("div");t.innerHTML='<v:shape adj="1"/>';var i=t.firstChild;return i.style.behavior="url(#default#VML)",i&&"object"==typeof i.adj}catch(t){return!1}}();function Bt(t){return 0<=navigator.userAgent.toLowerCase().indexOf(t)}var At=(Object.freeze||Object)({ie:it,ielt9:et,edge:nt,webkit:ot,android:st,android23:rt,androidStock:ht,opera:ut,chrome:lt,gecko:ct,safari:_t,phantom:dt,opera12:pt,win:mt,ie3d:ft,webkit3d:gt,gecko3d:vt,any3d:yt,mobile:xt,mobileWebkit:wt,mobileWebkit3d:Pt,msPointer:Lt,pointer:bt,touch:Tt,mobileOpera:zt,mobileGecko:Mt,retina:Ct,passiveEvents:St,canvas:Et,svg:Zt,vml:kt}),It=Lt?"MSPointerDown":"pointerdown",Ot=Lt?"MSPointerMove":"pointermove",Rt=Lt?"MSPointerUp":"pointerup",Nt=Lt?"MSPointerCancel":"pointercancel",Dt=["INPUT","SELECT","OPTION"],jt={},Wt=!1,Ht=0;function Ft(t,i,e,n){return"touchstart"===i?function(t,i,e){var n=a(function(t){if("mouse"!==t.pointerType&&t.MSPOINTER_TYPE_MOUSE&&t.pointerType!==t.MSPOINTER_TYPE_MOUSE){if(!(Dt.indexOf(t.target.tagName)<0))return;ji(t)}Gt(t,i)});t["_leaflet_touchstart"+e]=n,t.addEventListener(It,n,!1),Wt||(document.documentElement.addEventListener(It,Ut,!0),document.documentElement.addEventListener(Ot,Vt,!0),document.documentElement.addEventListener(Rt,qt,!0),document.documentElement.addEventListener(Nt,qt,!0),Wt=!0)}(t,e,n):"touchmove"===i?function(t,i,e){function n(t){(t.pointerType!==t.MSPOINTER_TYPE_MOUSE&&"mouse"!==t.pointerType||0!==t.buttons)&&Gt(t,i)}t["_leaflet_touchmove"+e]=n,t.addEventListener(Ot,n,!1)}(t,e,n):"touchend"===i&&function(t,i,e){function n(t){Gt(t,i)}t["_leaflet_touchend"+e]=n,t.addEventListener(Rt,n,!1),t.addEventListener(Nt,n,!1)}(t,e,n),this}function Ut(t){jt[t.pointerId]=t,Ht++}function Vt(t){jt[t.pointerId]&&(jt[t.pointerId]=t)}function qt(t){delete jt[t.pointerId],Ht--}function Gt(t,i){for(var e in t.touches=[],jt)t.touches.push(jt[e]);t.changedTouches=[t],i(t)}var Kt=Lt?"MSPointerDown":bt?"pointerdown":"touchstart",Yt=Lt?"MSPointerUp":bt?"pointerup":"touchend",Xt="_leaflet_";function Jt(t,o,i){var s,r,a=!1;function e(t){var i;if(bt){if(!nt||"mouse"===t.pointerType)return;i=Ht}else i=t.touches.length;if(!(1<i)){var e=Date.now(),n=e-(s||e);r=t.touches?t.touches[0]:t,a=0<n&&n<=250,s=e}}function n(t){if(a&&!r.cancelBubble){if(bt){if(!nt||"mouse"===t.pointerType)return;var i,e,n={};for(e in r)i=r[e],n[e]=i&&i.bind?i.bind(r):i;r=n}r.type="dblclick",r.button=0,o(r),s=null}}return t[Xt+Kt+i]=e,t[Xt+Yt+i]=n,t[Xt+"dblclick"+i]=o,t.addEventListener(Kt,e,!!St&&{passive:!1}),t.addEventListener(Yt,n,!!St&&{passive:!1}),t.addEventListener("dblclick",o,!1),this}function $t(t,i){var e=t[Xt+Kt+i],n=t[Xt+Yt+i],o=t[Xt+"dblclick"+i];return t.removeEventListener(Kt,e,!!St&&{passive:!1}),t.removeEventListener(Yt,n,!!St&&{passive:!1}),nt||t.removeEventListener("dblclick",o,!1),this}var Qt,ti,ii,ei,ni,oi=xi(["transform","webkitTransform","OTransform","MozTransform","msTransform"]),si=xi(["webkitTransition","transition","OTransition","MozTransition","msTransition"]),ri="webkitTransition"===si||"OTransition"===si?si+"End":"transitionend";function ai(t){return"string"==typeof t?document.getElementById(t):t}function hi(t,i){var e=t.style[i]||t.currentStyle&&t.currentStyle[i];if((!e||"auto"===e)&&document.defaultView){var n=document.defaultView.getComputedStyle(t,null);e=n?n[i]:null}return"auto"===e?null:e}function ui(t,i,e){var n=document.createElement(t);return n.className=i||"",e&&e.appendChild(n),n}function li(t){var i=t.parentNode;i&&i.removeChild(t)}function ci(t){for(;t.firstChild;)t.removeChild(t.firstChild)}function _i(t){var i=t.parentNode;i&&i.lastChild!==t&&i.appendChild(t)}function di(t){var i=t.parentNode;i&&i.firstChild!==t&&i.insertBefore(t,i.firstChild)}function pi(t,i){if(void 0!==t.classList)return t.classList.contains(i);var e=vi(t);return 0<e.length&&new RegExp("(^|\\s)"+i+"(\\s|$)").test(e)}function mi(t,i){if(void 0!==t.classList)for(var e=d(i),n=0,o=e.length;n<o;n++)t.classList.add(e[n]);else if(!pi(t,i)){var s=vi(t);gi(t,(s?s+" ":"")+i)}}function fi(t,i){void 0!==t.classList?t.classList.remove(i):gi(t,_((" "+vi(t)+" ").replace(" "+i+" "," ")))}function gi(t,i){void 0===t.className.baseVal?t.className=i:t.className.baseVal=i}function vi(t){return t.correspondingElement&&(t=t.correspondingElement),void 0===t.className.baseVal?t.className:t.className.baseVal}function yi(t,i){"opacity"in t.style?t.style.opacity=i:"filter"in t.style&&function(t,i){var e=!1,n="DXImageTransform.Microsoft.Alpha";try{e=t.filters.item(n)}catch(t){if(1===i)return}i=Math.round(100*i),e?(e.Enabled=100!==i,e.Opacity=i):t.style.filter+=" progid:"+n+"(opacity="+i+")"}(t,i)}function xi(t){for(var i=document.documentElement.style,e=0;e<t.length;e++)if(t[e]in i)return t[e];return!1}function wi(t,i,e){var n=i||new B(0,0);t.style[oi]=(ft?"translate("+n.x+"px,"+n.y+"px)":"translate3d("+n.x+"px,"+n.y+"px,0)")+(e?" scale("+e+")":"")}function Pi(t,i){t._leaflet_pos=i,yt?wi(t,i):(t.style.left=i.x+"px",t.style.top=i.y+"px")}function Li(t){return t._leaflet_pos||new B(0,0)}if("onselectstart"in document)Qt=function(){ki(window,"selectstart",ji)},ti=function(){Ai(window,"selectstart",ji)};else{var bi=xi(["userSelect","WebkitUserSelect","OUserSelect","MozUserSelect","msUserSelect"]);Qt=function(){if(bi){var t=document.documentElement.style;ii=t[bi],t[bi]="none"}},ti=function(){bi&&(document.documentElement.style[bi]=ii,ii=void 0)}}function Ti(){ki(window,"dragstart",ji)}function zi(){Ai(window,"dragstart",ji)}function Mi(t){for(;-1===t.tabIndex;)t=t.parentNode;t.style&&(Ci(),ni=(ei=t).style.outline,t.style.outline="none",ki(window,"keydown",Ci))}function Ci(){ei&&(ei.style.outline=ni,ni=ei=void 0,Ai(window,"keydown",Ci))}function Si(t){for(;!((t=t.parentNode).offsetWidth&&t.offsetHeight||t===document.body););return t}function Ei(t){var i=t.getBoundingClientRect();return{x:i.width/t.offsetWidth||1,y:i.height/t.offsetHeight||1,boundingClientRect:i}}var Zi=(Object.freeze||Object)({TRANSFORM:oi,TRANSITION:si,TRANSITION_END:ri,get:ai,getStyle:hi,create:ui,remove:li,empty:ci,toFront:_i,toBack:di,hasClass:pi,addClass:mi,removeClass:fi,setClass:gi,getClass:vi,setOpacity:yi,testProp:xi,setTransform:wi,setPosition:Pi,getPosition:Li,disableTextSelection:Qt,enableTextSelection:ti,disableImageDrag:Ti,enableImageDrag:zi,preventOutline:Mi,restoreOutline:Ci,getSizedParentNode:Si,getScale:Ei});function ki(t,i,e,n){if("object"==typeof i)for(var o in i)Ii(t,o,i[o],e);else for(var s=0,r=(i=d(i)).length;s<r;s++)Ii(t,i[s],e,n);return this}var Bi="_leaflet_events";function Ai(t,i,e,n){if("object"==typeof i)for(var o in i)Oi(t,o,i[o],e);else if(i)for(var s=0,r=(i=d(i)).length;s<r;s++)Oi(t,i[s],e,n);else{for(var a in t[Bi])Oi(t,a,t[Bi][a]);delete t[Bi]}return this}function Ii(i,t,e,n){var o=t+u(e)+(n?"_"+u(n):"");if(i[Bi]&&i[Bi][o])return this;var s=function(t){return e.call(n||i,t||window.event)},r=s;bt&&0===t.indexOf("touch")?Ft(i,t,s,o):!Tt||"dblclick"!==t||bt&&lt?"addEventListener"in i?"mousewheel"===t?i.addEventListener("onwheel"in i?"wheel":"mousewheel",s,!!St&&{passive:!1}):"mouseenter"===t||"mouseleave"===t?(s=function(t){t=t||window.event,Yi(i,t)&&r(t)},i.addEventListener("mouseenter"===t?"mouseover":"mouseout",s,!1)):("click"===t&&st&&(s=function(t){!function(t,i){var e=t.timeStamp||t.originalEvent&&t.originalEvent.timeStamp,n=Vi&&e-Vi;if(n&&100<n&&n<500||t.target._simulatedClick&&!t._simulated)return Wi(t);Vi=e,i(t)}(t,r)}),i.addEventListener(t,s,!1)):"attachEvent"in i&&i.attachEvent("on"+t,s):Jt(i,s,o),i[Bi]=i[Bi]||{},i[Bi][o]=s}function Oi(t,i,e,n){var o=i+u(e)+(n?"_"+u(n):""),s=t[Bi]&&t[Bi][o];if(!s)return this;bt&&0===i.indexOf("touch")?function(t,i,e){var n=t["_leaflet_"+i+e];"touchstart"===i?t.removeEventListener(It,n,!1):"touchmove"===i?t.removeEventListener(Ot,n,!1):"touchend"===i&&(t.removeEventListener(Rt,n,!1),t.removeEventListener(Nt,n,!1))}(t,i,o):!Tt||"dblclick"!==i||bt&&lt?"removeEventListener"in t?"mousewheel"===i?t.removeEventListener("onwheel"in t?"wheel":"mousewheel",s,!!St&&{passive:!1}):t.removeEventListener("mouseenter"===i?"mouseover":"mouseleave"===i?"mouseout":i,s,!1):"detachEvent"in t&&t.detachEvent("on"+i,s):$t(t,o),t[Bi][o]=null}function Ri(t){return t.stopPropagation?t.stopPropagation():t.originalEvent?t.originalEvent._stopped=!0:t.cancelBubble=!0,Ki(t),this}function Ni(t){return Ii(t,"mousewheel",Ri),this}function Di(t){return ki(t,"mousedown touchstart dblclick",Ri),Ii(t,"click",Gi),this}function ji(t){return t.preventDefault?t.preventDefault():t.returnValue=!1,this}function Wi(t){return ji(t),Ri(t),this}function Hi(t,i){if(!i)return new B(t.clientX,t.clientY);var e=Ei(i),n=e.boundingClientRect;return new B((t.clientX-n.left)/e.x-i.clientLeft,(t.clientY-n.top)/e.y-i.clientTop)}var Fi=mt&&lt?2*window.devicePixelRatio:ct?window.devicePixelRatio:1;function Ui(t){return nt?t.wheelDeltaY/2:t.deltaY&&0===t.deltaMode?-t.deltaY/Fi:t.deltaY&&1===t.deltaMode?20*-t.deltaY:t.deltaY&&2===t.deltaMode?60*-t.deltaY:t.deltaX||t.deltaZ?0:t.wheelDelta?(t.wheelDeltaY||t.wheelDelta)/2:t.detail&&Math.abs(t.detail)<32765?20*-t.detail:t.detail?t.detail/-32765*60:0}var Vi,qi={};function Gi(t){qi[t.type]=!0}function Ki(t){var i=qi[t.type];return qi[t.type]=!1,i}function Yi(t,i){var e=i.relatedTarget;if(!e)return!0;try{for(;e&&e!==t;)e=e.parentNode}catch(t){return!1}return e!==t}var Xi=(Object.freeze||Object)({on:ki,off:Ai,stopPropagation:Ri,disableScrollPropagation:Ni,disableClickPropagation:Di,preventDefault:ji,stop:Wi,getMousePosition:Hi,getWheelDelta:Ui,fakeStop:Gi,skipped:Ki,isExternalTarget:Yi,addListener:ki,removeListener:Ai}),Ji=k.extend({run:function(t,i,e,n){this.stop(),this._el=t,this._inProgress=!0,this._duration=e||.25,this._easeOutPower=1/Math.max(n||.5,.2),this._startPos=Li(t),this._offset=i.subtract(this._startPos),this._startTime=+new Date,this.fire("start"),this._animate()},stop:function(){this._inProgress&&(this._step(!0),this._complete())},_animate:function(){this._animId=M(this._animate,this),this._step()},_step:function(t){var i=+new Date-this._startTime,e=1e3*this._duration;i<e?this._runFrame(this._easeOut(i/e),t):(this._runFrame(1),this._complete())},_runFrame:function(t,i){var e=this._startPos.add(this._offset.multiplyBy(t));i&&e._round(),Pi(this._el,e),this.fire("step")},_complete:function(){C(this._animId),this._inProgress=!1,this.fire("end")},_easeOut:function(t){return 1-Math.pow(1-t,this._easeOutPower)}}),$i=k.extend({options:{crs:X,center:void 0,zoom:void 0,minZoom:void 0,maxZoom:void 0,layers:[],maxBounds:void 0,renderer:void 0,zoomAnimation:!0,zoomAnimationThreshold:4,fadeAnimation:!0,markerZoomAnimation:!0,transform3DLimit:8388608,zoomSnap:1,zoomDelta:1,trackResize:!0},initialize:function(t,i){i=p(this,i),this._handlers=[],this._layers={},this._zoomBoundLayers={},this._sizeChanged=!0,this._initContainer(t),this._initLayout(),this._onResize=a(this._onResize,this),this._initEvents(),i.maxBounds&&this.setMaxBounds(i.maxBounds),void 0!==i.zoom&&(this._zoom=this._limitZoom(i.zoom)),i.center&&void 0!==i.zoom&&this.setView(W(i.center),i.zoom,{reset:!0}),this.callInitHooks(),this._zoomAnimated=si&&yt&&!zt&&this.options.zoomAnimation,this._zoomAnimated&&(this._createAnimProxy(),ki(this._proxy,ri,this._catchTransitionEnd,this)),this._addLayers(this.options.layers)},setView:function(t,i,e){if((i=void 0===i?this._zoom:this._limitZoom(i),t=this._limitCenter(W(t),i,this.options.maxBounds),e=e||{},this._stop(),this._loaded&&!e.reset&&!0!==e)&&(void 0!==e.animate&&(e.zoom=h({animate:e.animate},e.zoom),e.pan=h({animate:e.animate,duration:e.duration},e.pan)),this._zoom!==i?this._tryAnimatedZoom&&this._tryAnimatedZoom(t,i,e.zoom):this._tryAnimatedPan(t,e.pan)))return clearTimeout(this._sizeTimer),this;return this._resetView(t,i),this},setZoom:function(t,i){return this._loaded?this.setView(this.getCenter(),t,{zoom:i}):(this._zoom=t,this)},zoomIn:function(t,i){return t=t||(yt?this.options.zoomDelta:1),this.setZoom(this._zoom+t,i)},zoomOut:function(t,i){return t=t||(yt?this.options.zoomDelta:1),this.setZoom(this._zoom-t,i)},setZoomAround:function(t,i,e){var n=this.getZoomScale(i),o=this.getSize().divideBy(2),s=(t instanceof B?t:this.latLngToContainerPoint(t)).subtract(o).multiplyBy(1-1/n),r=this.containerPointToLatLng(o.add(s));return this.setView(r,i,{zoom:e})},_getBoundsCenterZoom:function(t,i){i=i||{},t=t.getBounds?t.getBounds():D(t);var e=I(i.paddingTopLeft||i.padding||[0,0]),n=I(i.paddingBottomRight||i.padding||[0,0]),o=this.getBoundsZoom(t,!1,e.add(n));if((o="number"==typeof i.maxZoom?Math.min(i.maxZoom,o):o)===1/0)return{center:t.getCenter(),zoom:o};var s=n.subtract(e).divideBy(2),r=this.project(t.getSouthWest(),o),a=this.project(t.getNorthEast(),o);return{center:this.unproject(r.add(a).divideBy(2).add(s),o),zoom:o}},fitBounds:function(t,i){if(!(t=D(t)).isValid())throw new Error("Bounds are not valid.");var e=this._getBoundsCenterZoom(t,i);return this.setView(e.center,e.zoom,i)},fitWorld:function(t){return this.fitBounds([[-90,-180],[90,180]],t)},panTo:function(t,i){return this.setView(t,this._zoom,{pan:i})},panBy:function(t,i){if(i=i||{},!(t=I(t).round()).x&&!t.y)return this.fire("moveend");if(!0!==i.animate&&!this.getSize().contains(t))return this._resetView(this.unproject(this.project(this.getCenter()).add(t)),this.getZoom()),this;if(this._panAnim||(this._panAnim=new Ji,this._panAnim.on({step:this._onPanTransitionStep,end:this._onPanTransitionEnd},this)),i.noMoveStart||this.fire("movestart"),!1!==i.animate){mi(this._mapPane,"leaflet-pan-anim");var e=this._getMapPanePos().subtract(t).round();this._panAnim.run(this._mapPane,e,i.duration||.25,i.easeLinearity)}else this._rawPanBy(t),this.fire("move").fire("moveend");return this},flyTo:function(n,o,t){if(!1===(t=t||{}).animate||!yt)return this.setView(n,o,t);this._stop();var s=this.project(this.getCenter()),r=this.project(n),i=this.getSize(),a=this._zoom;n=W(n),o=void 0===o?a:o;var h=Math.max(i.x,i.y),u=h*this.getZoomScale(a,o),l=r.distanceTo(s)||1,c=1.42,_=c*c;function e(t){var i=(u*u-h*h+(t?-1:1)*_*_*l*l)/(2*(t?u:h)*_*l),e=Math.sqrt(i*i+1)-i;return e<1e-9?-18:Math.log(e)}function d(t){return(Math.exp(t)-Math.exp(-t))/2}function p(t){return(Math.exp(t)+Math.exp(-t))/2}var m=e(0);function f(t){return h*(p(m)*function(t){return d(t)/p(t)}(m+c*t)-d(m))/_}var g=Date.now(),v=(e(1)-m)/c,y=t.duration?1e3*t.duration:1e3*v*.8;return this._moveStart(!0,t.noMoveStart),function t(){var i=(Date.now()-g)/y,e=function(t){return 1-Math.pow(1-t,1.5)}(i)*v;i<=1?(this._flyToFrame=M(t,this),this._move(this.unproject(s.add(r.subtract(s).multiplyBy(f(e)/l)),a),this.getScaleZoom(h/function(t){return h*(p(m)/p(m+c*t))}(e),a),{flyTo:!0})):this._move(n,o)._moveEnd(!0)}.call(this),this},flyToBounds:function(t,i){var e=this._getBoundsCenterZoom(t,i);return this.flyTo(e.center,e.zoom,i)},setMaxBounds:function(t){return(t=D(t)).isValid()?(this.options.maxBounds&&this.off("moveend",this._panInsideMaxBounds),this.options.maxBounds=t,this._loaded&&this._panInsideMaxBounds(),this.on("moveend",this._panInsideMaxBounds)):(this.options.maxBounds=null,this.off("moveend",this._panInsideMaxBounds))},setMinZoom:function(t){var i=this.options.minZoom;return this.options.minZoom=t,this._loaded&&i!==t&&(this.fire("zoomlevelschange"),this.getZoom()<this.options.minZoom)?this.setZoom(t):this},setMaxZoom:function(t){var i=this.options.maxZoom;return this.options.maxZoom=t,this._loaded&&i!==t&&(this.fire("zoomlevelschange"),this.getZoom()>this.options.maxZoom)?this.setZoom(t):this},panInsideBounds:function(t,i){this._enforcingBounds=!0;var e=this.getCenter(),n=this._limitCenter(e,this._zoom,D(t));return e.equals(n)||this.panTo(n,i),this._enforcingBounds=!1,this},panInside:function(t,i){var e=I((i=i||{}).paddingTopLeft||i.padding||[0,0]),n=I(i.paddingBottomRight||i.padding||[0,0]),o=this.getCenter(),s=this.project(o),r=this.project(t),a=this.getPixelBounds(),h=a.getSize().divideBy(2),u=R([a.min.add(e),a.max.subtract(n)]);if(!u.contains(r)){this._enforcingBounds=!0;var l=s.subtract(r),c=I(r.x+l.x,r.y+l.y);(r.x<u.min.x||r.x>u.max.x)&&(c.x=s.x-l.x,0<l.x?c.x+=h.x-e.x:c.x-=h.x-n.x),(r.y<u.min.y||r.y>u.max.y)&&(c.y=s.y-l.y,0<l.y?c.y+=h.y-e.y:c.y-=h.y-n.y),this.panTo(this.unproject(c),i),this._enforcingBounds=!1}return this},invalidateSize:function(t){if(!this._loaded)return this;t=h({animate:!1,pan:!0},!0===t?{animate:!0}:t);var i=this.getSize();this._sizeChanged=!0,this._lastCenter=null;var e=this.getSize(),n=i.divideBy(2).round(),o=e.divideBy(2).round(),s=n.subtract(o);return s.x||s.y?(t.animate&&t.pan?this.panBy(s):(t.pan&&this._rawPanBy(s),this.fire("move"),t.debounceMoveend?(clearTimeout(this._sizeTimer),this._sizeTimer=setTimeout(a(this.fire,this,"moveend"),200)):this.fire("moveend")),this.fire("resize",{oldSize:i,newSize:e})):this},stop:function(){return this.setZoom(this._limitZoom(this._zoom)),this.options.zoomSnap||this.fire("viewreset"),this._stop()},locate:function(t){if(t=this._locateOptions=h({timeout:1e4,watch:!1},t),!("geolocation"in navigator))return this._handleGeolocationError({code:0,message:"Geolocation not supported."}),this;var i=a(this._handleGeolocationResponse,this),e=a(this._handleGeolocationError,this);return t.watch?this._locationWatchId=navigator.geolocation.watchPosition(i,e,t):navigator.geolocation.getCurrentPosition(i,e,t),this},stopLocate:function(){return navigator.geolocation&&navigator.geolocation.clearWatch&&navigator.geolocation.clearWatch(this._locationWatchId),this._locateOptions&&(this._locateOptions.setView=!1),this},_handleGeolocationError:function(t){var i=t.code,e=t.message||(1===i?"permission denied":2===i?"position unavailable":"timeout");this._locateOptions.setView&&!this._loaded&&this.fitWorld(),this.fire("locationerror",{code:i,message:"Geolocation error: "+e+"."})},_handleGeolocationResponse:function(t){var i=new j(t.coords.latitude,t.coords.longitude),e=i.toBounds(2*t.coords.accuracy),n=this._locateOptions;if(n.setView){var o=this.getBoundsZoom(e);this.setView(i,n.maxZoom?Math.min(o,n.maxZoom):o)}var s={latlng:i,bounds:e,timestamp:t.timestamp};for(var r in t.coords)"number"==typeof t.coords[r]&&(s[r]=t.coords[r]);this.fire("locationfound",s)},addHandler:function(t,i){if(!i)return this;var e=this[t]=new i(this);return this._handlers.push(e),this.options[t]&&e.enable(),this},remove:function(){if(this._initEvents(!0),this._containerId!==this._container._leaflet_id)throw new Error("Map container is being reused by another instance");try{delete this._container._leaflet_id,delete this._containerId}catch(t){this._container._leaflet_id=void 0,this._containerId=void 0}var t;for(t in void 0!==this._locationWatchId&&this.stopLocate(),this._stop(),li(this._mapPane),this._clearControlPos&&this._clearControlPos(),this._resizeRequest&&(C(this._resizeRequest),this._resizeRequest=null),this._clearHandlers(),this._loaded&&this.fire("unload"),this._layers)this._layers[t].remove();for(t in this._panes)li(this._panes[t]);return this._layers=[],this._panes=[],delete this._mapPane,delete this._renderer,this},createPane:function(t,i){var e=ui("div","leaflet-pane"+(t?" leaflet-"+t.replace("Pane","")+"-pane":""),i||this._mapPane);return t&&(this._panes[t]=e),e},getCenter:function(){return this._checkIfLoaded(),this._lastCenter&&!this._moved()?this._lastCenter:this.layerPointToLatLng(this._getCenterLayerPoint())},getZoom:function(){return this._zoom},getBounds:function(){var t=this.getPixelBounds();return new N(this.unproject(t.getBottomLeft()),this.unproject(t.getTopRight()))},getMinZoom:function(){return void 0===this.options.minZoom?this._layersMinZoom||0:this.options.minZoom},getMaxZoom:function(){return void 0===this.options.maxZoom?void 0===this._layersMaxZoom?1/0:this._layersMaxZoom:this.options.maxZoom},getBoundsZoom:function(t,i,e){t=D(t),e=I(e||[0,0]);var n=this.getZoom()||0,o=this.getMinZoom(),s=this.getMaxZoom(),r=t.getNorthWest(),a=t.getSouthEast(),h=this.getSize().subtract(e),u=R(this.project(a,n),this.project(r,n)).getSize(),l=yt?this.options.zoomSnap:1,c=h.x/u.x,_=h.y/u.y,d=i?Math.max(c,_):Math.min(c,_);return n=this.getScaleZoom(d,n),l&&(n=Math.round(n/(l/100))*(l/100),n=i?Math.ceil(n/l)*l:Math.floor(n/l)*l),Math.max(o,Math.min(s,n))},getSize:function(){return this._size&&!this._sizeChanged||(this._size=new B(this._container.clientWidth||0,this._container.clientHeight||0),this._sizeChanged=!1),this._size.clone()},getPixelBounds:function(t,i){var e=this._getTopLeftPoint(t,i);return new O(e,e.add(this.getSize()))},getPixelOrigin:function(){return this._checkIfLoaded(),this._pixelOrigin},getPixelWorldBounds:function(t){return this.options.crs.getProjectedBounds(void 0===t?this.getZoom():t)},getPane:function(t){return"string"==typeof t?this._panes[t]:t},getPanes:function(){return this._panes},getContainer:function(){return this._container},getZoomScale:function(t,i){var e=this.options.crs;return i=void 0===i?this._zoom:i,e.scale(t)/e.scale(i)},getScaleZoom:function(t,i){var e=this.options.crs;i=void 0===i?this._zoom:i;var n=e.zoom(t*e.scale(i));return isNaN(n)?1/0:n},project:function(t,i){return i=void 0===i?this._zoom:i,this.options.crs.latLngToPoint(W(t),i)},unproject:function(t,i){return i=void 0===i?this._zoom:i,this.options.crs.pointToLatLng(I(t),i)},layerPointToLatLng:function(t){var i=I(t).add(this.getPixelOrigin());return this.unproject(i)},latLngToLayerPoint:function(t){return this.project(W(t))._round()._subtract(this.getPixelOrigin())},wrapLatLng:function(t){return this.options.crs.wrapLatLng(W(t))},wrapLatLngBounds:function(t){return this.options.crs.wrapLatLngBounds(D(t))},distance:function(t,i){return this.options.crs.distance(W(t),W(i))},containerPointToLayerPoint:function(t){return I(t).subtract(this._getMapPanePos())},layerPointToContainerPoint:function(t){return I(t).add(this._getMapPanePos())},containerPointToLatLng:function(t){var i=this.containerPointToLayerPoint(I(t));return this.layerPointToLatLng(i)},latLngToContainerPoint:function(t){return this.layerPointToContainerPoint(this.latLngToLayerPoint(W(t)))},mouseEventToContainerPoint:function(t){return Hi(t,this._container)},mouseEventToLayerPoint:function(t){return this.containerPointToLayerPoint(this.mouseEventToContainerPoint(t))},mouseEventToLatLng:function(t){return this.layerPointToLatLng(this.mouseEventToLayerPoint(t))},_initContainer:function(t){var i=this._container=ai(t);if(!i)throw new Error("Map container not found.");if(i._leaflet_id)throw new Error("Map container is already initialized.");ki(i,"scroll",this._onScroll,this),this._containerId=u(i)},_initLayout:function(){var t=this._container;this._fadeAnimated=this.options.fadeAnimation&&yt,mi(t,"leaflet-container"+(Tt?" leaflet-touch":"")+(Ct?" leaflet-retina":"")+(et?" leaflet-oldie":"")+(_t?" leaflet-safari":"")+(this._fadeAnimated?" leaflet-fade-anim":""));var i=hi(t,"position");"absolute"!==i&&"relative"!==i&&"fixed"!==i&&(t.style.position="relative"),this._initPanes(),this._initControlPos&&this._initControlPos()},_initPanes:function(){var t=this._panes={};this._paneRenderers={},this._mapPane=this.createPane("mapPane",this._container),Pi(this._mapPane,new B(0,0)),this.createPane("tilePane"),this.createPane("shadowPane"),this.createPane("overlayPane"),this.createPane("markerPane"),this.createPane("tooltipPane"),this.createPane("popupPane"),this.options.markerZoomAnimation||(mi(t.markerPane,"leaflet-zoom-hide"),mi(t.shadowPane,"leaflet-zoom-hide"))},_resetView:function(t,i){Pi(this._mapPane,new B(0,0));var e=!this._loaded;this._loaded=!0,i=this._limitZoom(i),this.fire("viewprereset");var n=this._zoom!==i;this._moveStart(n,!1)._move(t,i)._moveEnd(n),this.fire("viewreset"),e&&this.fire("load")},_moveStart:function(t,i){return t&&this.fire("zoomstart"),i||this.fire("movestart"),this},_move:function(t,i,e){void 0===i&&(i=this._zoom);var n=this._zoom!==i;return this._zoom=i,this._lastCenter=t,this._pixelOrigin=this._getNewPixelOrigin(t),(n||e&&e.pinch)&&this.fire("zoom",e),this.fire("move",e)},_moveEnd:function(t){return t&&this.fire("zoomend"),this.fire("moveend")},_stop:function(){return C(this._flyToFrame),this._panAnim&&this._panAnim.stop(),this},_rawPanBy:function(t){Pi(this._mapPane,this._getMapPanePos().subtract(t))},_getZoomSpan:function(){return this.getMaxZoom()-this.getMinZoom()},_panInsideMaxBounds:function(){this._enforcingBounds||this.panInsideBounds(this.options.maxBounds)},_checkIfLoaded:function(){if(!this._loaded)throw new Error("Set map center and zoom first.")},_initEvents:function(t){this._targets={};var i=t?Ai:ki;i((this._targets[u(this._container)]=this)._container,"click dblclick mousedown mouseup mouseover mouseout mousemove contextmenu keypress keydown keyup",this._handleDOMEvent,this),this.options.trackResize&&i(window,"resize",this._onResize,this),yt&&this.options.transform3DLimit&&(t?this.off:this.on).call(this,"moveend",this._onMoveEnd)},_onResize:function(){C(this._resizeRequest),this._resizeRequest=M(function(){this.invalidateSize({debounceMoveend:!0})},this)},_onScroll:function(){this._container.scrollTop=0,this._container.scrollLeft=0},_onMoveEnd:function(){var t=this._getMapPanePos();Math.max(Math.abs(t.x),Math.abs(t.y))>=this.options.transform3DLimit&&this._resetView(this.getCenter(),this.getZoom())},_findEventTargets:function(t,i){for(var e,n=[],o="mouseout"===i||"mouseover"===i,s=t.target||t.srcElement,r=!1;s;){if((e=this._targets[u(s)])&&("click"===i||"preclick"===i)&&!t._simulated&&this._draggableMoved(e)){r=!0;break}if(e&&e.listens(i,!0)){if(o&&!Yi(s,t))break;if(n.push(e),o)break}if(s===this._container)break;s=s.parentNode}return n.length||r||o||!Yi(s,t)||(n=[this]),n},_handleDOMEvent:function(t){if(this._loaded&&!Ki(t)){var i=t.type;"mousedown"!==i&&"keypress"!==i&&"keyup"!==i&&"keydown"!==i||Mi(t.target||t.srcElement),this._fireDOMEvent(t,i)}},_mouseEvents:["click","dblclick","mouseover","mouseout","contextmenu"],_fireDOMEvent:function(t,i,e){if("click"===t.type){var n=h({},t);n.type="preclick",this._fireDOMEvent(n,n.type,e)}if(!t._stopped&&(e=(e||[]).concat(this._findEventTargets(t,i))).length){var o=e[0];"contextmenu"===i&&o.listens(i,!0)&&ji(t);var s={originalEvent:t};if("keypress"!==t.type&&"keydown"!==t.type&&"keyup"!==t.type){var r=o.getLatLng&&(!o._radius||o._radius<=10);s.containerPoint=r?this.latLngToContainerPoint(o.getLatLng()):this.mouseEventToContainerPoint(t),s.layerPoint=this.containerPointToLayerPoint(s.containerPoint),s.latlng=r?o.getLatLng():this.layerPointToLatLng(s.layerPoint)}for(var a=0;a<e.length;a++)if(e[a].fire(i,s,!0),s.originalEvent._stopped||!1===e[a].options.bubblingMouseEvents&&-1!==y(this._mouseEvents,i))return}},_draggableMoved:function(t){return(t=t.dragging&&t.dragging.enabled()?t:this).dragging&&t.dragging.moved()||this.boxZoom&&this.boxZoom.moved()},_clearHandlers:function(){for(var t=0,i=this._handlers.length;t<i;t++)this._handlers[t].disable()},whenReady:function(t,i){return this._loaded?t.call(i||this,{target:this}):this.on("load",t,i),this},_getMapPanePos:function(){return Li(this._mapPane)||new B(0,0)},_moved:function(){var t=this._getMapPanePos();return t&&!t.equals([0,0])},_getTopLeftPoint:function(t,i){return(t&&void 0!==i?this._getNewPixelOrigin(t,i):this.getPixelOrigin()).subtract(this._getMapPanePos())},_getNewPixelOrigin:function(t,i){var e=this.getSize()._divideBy(2);return this.project(t,i)._subtract(e)._add(this._getMapPanePos())._round()},_latLngToNewLayerPoint:function(t,i,e){var n=this._getNewPixelOrigin(e,i);return this.project(t,i)._subtract(n)},_latLngBoundsToNewLayerBounds:function(t,i,e){var n=this._getNewPixelOrigin(e,i);return R([this.project(t.getSouthWest(),i)._subtract(n),this.project(t.getNorthWest(),i)._subtract(n),this.project(t.getSouthEast(),i)._subtract(n),this.project(t.getNorthEast(),i)._subtract(n)])},_getCenterLayerPoint:function(){return this.containerPointToLayerPoint(this.getSize()._divideBy(2))},_getCenterOffset:function(t){return this.latLngToLayerPoint(t).subtract(this._getCenterLayerPoint())},_limitCenter:function(t,i,e){if(!e)return t;var n=this.project(t,i),o=this.getSize().divideBy(2),s=new O(n.subtract(o),n.add(o)),r=this._getBoundsOffset(s,e,i);return r.round().equals([0,0])?t:this.unproject(n.add(r),i)},_limitOffset:function(t,i){if(!i)return t;var e=this.getPixelBounds(),n=new O(e.min.add(t),e.max.add(t));return t.add(this._getBoundsOffset(n,i))},_getBoundsOffset:function(t,i,e){var n=R(this.project(i.getNorthEast(),e),this.project(i.getSouthWest(),e)),o=n.min.subtract(t.min),s=n.max.subtract(t.max);return new B(this._rebound(o.x,-s.x),this._rebound(o.y,-s.y))},_rebound:function(t,i){return 0<t+i?Math.round(t-i)/2:Math.max(0,Math.ceil(t))-Math.max(0,Math.floor(i))},_limitZoom:function(t){var i=this.getMinZoom(),e=this.getMaxZoom(),n=yt?this.options.zoomSnap:1;return n&&(t=Math.round(t/n)*n),Math.max(i,Math.min(e,t))},_onPanTransitionStep:function(){this.fire("move")},_onPanTransitionEnd:function(){fi(this._mapPane,"leaflet-pan-anim"),this.fire("moveend")},_tryAnimatedPan:function(t,i){var e=this._getCenterOffset(t)._trunc();return!(!0!==(i&&i.animate)&&!this.getSize().contains(e))&&(this.panBy(e,i),!0)},_createAnimProxy:function(){var t=this._proxy=ui("div","leaflet-proxy leaflet-zoom-animated");this._panes.mapPane.appendChild(t),this.on("zoomanim",function(t){var i=oi,e=this._proxy.style[i];wi(this._proxy,this.project(t.center,t.zoom),this.getZoomScale(t.zoom,1)),e===this._proxy.style[i]&&this._animatingZoom&&this._onZoomTransitionEnd()},this),this.on("load moveend",this._animMoveEnd,this),this._on("unload",this._destroyAnimProxy,this)},_destroyAnimProxy:function(){li(this._proxy),this.off("load moveend",this._animMoveEnd,this),delete this._proxy},_animMoveEnd:function(){var t=this.getCenter(),i=this.getZoom();wi(this._proxy,this.project(t,i),this.getZoomScale(i,1))},_catchTransitionEnd:function(t){this._animatingZoom&&0<=t.propertyName.indexOf("transform")&&this._onZoomTransitionEnd()},_nothingToAnimate:function(){return!this._container.getElementsByClassName("leaflet-zoom-animated").length},_tryAnimatedZoom:function(t,i,e){if(this._animatingZoom)return!0;if(e=e||{},!this._zoomAnimated||!1===e.animate||this._nothingToAnimate()||Math.abs(i-this._zoom)>this.options.zoomAnimationThreshold)return!1;var n=this.getZoomScale(i),o=this._getCenterOffset(t)._divideBy(1-1/n);return!(!0!==e.animate&&!this.getSize().contains(o))&&(M(function(){this._moveStart(!0,!1)._animateZoom(t,i,!0)},this),!0)},_animateZoom:function(t,i,e,n){this._mapPane&&(e&&(this._animatingZoom=!0,this._animateToCenter=t,this._animateToZoom=i,mi(this._mapPane,"leaflet-zoom-anim")),this.fire("zoomanim",{center:t,zoom:i,noUpdate:n}),setTimeout(a(this._onZoomTransitionEnd,this),250))},_onZoomTransitionEnd:function(){this._animatingZoom&&(this._mapPane&&fi(this._mapPane,"leaflet-zoom-anim"),this._animatingZoom=!1,this._move(this._animateToCenter,this._animateToZoom),M(function(){this._moveEnd(!0)},this))}});function Qi(t){return new te(t)}var te=E.extend({options:{position:"topright"},initialize:function(t){p(this,t)},getPosition:function(){return this.options.position},setPosition:function(t){var i=this._map;return i&&i.removeControl(this),this.options.position=t,i&&i.addControl(this),this},getContainer:function(){return this._container},addTo:function(t){this.remove(),this._map=t;var i=this._container=this.onAdd(t),e=this.getPosition(),n=t._controlCorners[e];return mi(i,"leaflet-control"),-1!==e.indexOf("bottom")?n.insertBefore(i,n.firstChild):n.appendChild(i),this._map.on("unload",this.remove,this),this},remove:function(){return this._map&&(li(this._container),this.onRemove&&this.onRemove(this._map),this._map.off("unload",this.remove,this),this._map=null),this},_refocusOnMap:function(t){this._map&&t&&0<t.screenX&&0<t.screenY&&this._map.getContainer().focus()}});$i.include({addControl:function(t){return t.addTo(this),this},removeControl:function(t){return t.remove(),this},_initControlPos:function(){var n=this._controlCorners={},o="leaflet-",s=this._controlContainer=ui("div",o+"control-container",this._container);function t(t,i){var e=o+t+" "+o+i;n[t+i]=ui("div",e,s)}t("top","left"),t("top","right"),t("bottom","left"),t("bottom","right")},_clearControlPos:function(){for(var t in this._controlCorners)li(this._controlCorners[t]);li(this._controlContainer),delete this._controlCorners,delete this._controlContainer}});var ie=te.extend({options:{collapsed:!0,position:"topright",autoZIndex:!0,hideSingleBase:!1,sortLayers:!1,sortFunction:function(t,i,e,n){return e<n?-1:n<e?1:0}},initialize:function(t,i,e){for(var n in p(this,e),this._layerControlInputs=[],this._layers=[],this._lastZIndex=0,this._handlingClick=!1,t)this._addLayer(t[n],n);for(n in i)this._addLayer(i[n],n,!0)},onAdd:function(t){this._initLayout(),this._update(),(this._map=t).on("zoomend",this._checkDisabledLayers,this);for(var i=0;i<this._layers.length;i++)this._layers[i].layer.on("add remove",this._onLayerChange,this);return this._container},addTo:function(t){return te.prototype.addTo.call(this,t),this._expandIfNotCollapsed()},onRemove:function(){this._map.off("zoomend",this._checkDisabledLayers,this);for(var t=0;t<this._layers.length;t++)this._layers[t].layer.off("add remove",this._onLayerChange,this)},addBaseLayer:function(t,i){return this._addLayer(t,i),this._map?this._update():this},addOverlay:function(t,i){return this._addLayer(t,i,!0),this._map?this._update():this},removeLayer:function(t){t.off("add remove",this._onLayerChange,this);var i=this._getLayer(u(t));return i&&this._layers.splice(this._layers.indexOf(i),1),this._map?this._update():this},expand:function(){mi(this._container,"leaflet-control-layers-expanded"),this._section.style.height=null;var t=this._map.getSize().y-(this._container.offsetTop+50);return t<this._section.clientHeight?(mi(this._section,"leaflet-control-layers-scrollbar"),this._section.style.height=t+"px"):fi(this._section,"leaflet-control-layers-scrollbar"),this._checkDisabledLayers(),this},collapse:function(){return fi(this._container,"leaflet-control-layers-expanded"),this},_initLayout:function(){var t="leaflet-control-layers",i=this._container=ui("div",t),e=this.options.collapsed;i.setAttribute("aria-haspopup",!0),Di(i),Ni(i);var n=this._section=ui("section",t+"-list");e&&(this._map.on("click",this.collapse,this),st||ki(i,{mouseenter:this.expand,mouseleave:this.collapse},this));var o=this._layersLink=ui("a",t+"-toggle",i);o.href="#",o.title="Layers",Tt?(ki(o,"click",Wi),ki(o,"click",this.expand,this)):ki(o,"focus",this.expand,this),e||this.expand(),this._baseLayersList=ui("div",t+"-base",n),this._separator=ui("div",t+"-separator",n),this._overlaysList=ui("div",t+"-overlays",n),i.appendChild(n)},_getLayer:function(t){for(var i=0;i<this._layers.length;i++)if(this._layers[i]&&u(this._layers[i].layer)===t)return this._layers[i]},_addLayer:function(t,i,e){this._map&&t.on("add remove",this._onLayerChange,this),this._layers.push({layer:t,name:i,overlay:e}),this.options.sortLayers&&this._layers.sort(a(function(t,i){return this.options.sortFunction(t.layer,i.layer,t.name,i.name)},this)),this.options.autoZIndex&&t.setZIndex&&(this._lastZIndex++,t.setZIndex(this._lastZIndex)),this._expandIfNotCollapsed()},_update:function(){if(!this._container)return this;ci(this._baseLayersList),ci(this._overlaysList),this._layerControlInputs=[];var t,i,e,n,o=0;for(e=0;e<this._layers.length;e++)n=this._layers[e],this._addItem(n),i=i||n.overlay,t=t||!n.overlay,o+=n.overlay?0:1;return this.options.hideSingleBase&&(t=t&&1<o,this._baseLayersList.style.display=t?"":"none"),this._separator.style.display=i&&t?"":"none",this},_onLayerChange:function(t){this._handlingClick||this._update();var i=this._getLayer(u(t.target)),e=i.overlay?"add"===t.type?"overlayadd":"overlayremove":"add"===t.type?"baselayerchange":null;e&&this._map.fire(e,i)},_createRadioElement:function(t,i){var e='<input type="radio" class="leaflet-control-layers-selector" name="'+t+'"'+(i?' checked="checked"':"")+"/>",n=document.createElement("div");return n.innerHTML=e,n.firstChild},_addItem:function(t){var i,e=document.createElement("label"),n=this._map.hasLayer(t.layer);t.overlay?((i=document.createElement("input")).type="checkbox",i.className="leaflet-control-layers-selector",i.defaultChecked=n):i=this._createRadioElement("leaflet-base-layers_"+u(this),n),this._layerControlInputs.push(i),i.layerId=u(t.layer),ki(i,"click",this._onInputClick,this);var o=document.createElement("span");o.innerHTML=" "+t.name;var s=document.createElement("div");return e.appendChild(s),s.appendChild(i),s.appendChild(o),(t.overlay?this._overlaysList:this._baseLayersList).appendChild(e),this._checkDisabledLayers(),e},_onInputClick:function(){var t,i,e=this._layerControlInputs,n=[],o=[];this._handlingClick=!0;for(var s=e.length-1;0<=s;s--)t=e[s],i=this._getLayer(t.layerId).layer,t.checked?n.push(i):t.checked||o.push(i);for(s=0;s<o.length;s++)this._map.hasLayer(o[s])&&this._map.removeLayer(o[s]);for(s=0;s<n.length;s++)this._map.hasLayer(n[s])||this._map.addLayer(n[s]);this._handlingClick=!1,this._refocusOnMap()},_checkDisabledLayers:function(){for(var t,i,e=this._layerControlInputs,n=this._map.getZoom(),o=e.length-1;0<=o;o--)t=e[o],i=this._getLayer(t.layerId).layer,t.disabled=void 0!==i.options.minZoom&&n<i.options.minZoom||void 0!==i.options.maxZoom&&n>i.options.maxZoom},_expandIfNotCollapsed:function(){return this._map&&!this.options.collapsed&&this.expand(),this},_expand:function(){return this.expand()},_collapse:function(){return this.collapse()}}),ee=te.extend({options:{position:"topleft",zoomInText:"+",zoomInTitle:"Zoom in",zoomOutText:"&#x2212;",zoomOutTitle:"Zoom out"},onAdd:function(t){var i="leaflet-control-zoom",e=ui("div",i+" leaflet-bar"),n=this.options;return this._zoomInButton=this._createButton(n.zoomInText,n.zoomInTitle,i+"-in",e,this._zoomIn),this._zoomOutButton=this._createButton(n.zoomOutText,n.zoomOutTitle,i+"-out",e,this._zoomOut),this._updateDisabled(),t.on("zoomend zoomlevelschange",this._updateDisabled,this),e},onRemove:function(t){t.off("zoomend zoomlevelschange",this._updateDisabled,this)},disable:function(){return this._disabled=!0,this._updateDisabled(),this},enable:function(){return this._disabled=!1,this._updateDisabled(),this},_zoomIn:function(t){!this._disabled&&this._map._zoom<this._map.getMaxZoom()&&this._map.zoomIn(this._map.options.zoomDelta*(t.shiftKey?3:1))},_zoomOut:function(t){!this._disabled&&this._map._zoom>this._map.getMinZoom()&&this._map.zoomOut(this._map.options.zoomDelta*(t.shiftKey?3:1))},_createButton:function(t,i,e,n,o){var s=ui("a",e,n);return s.innerHTML=t,s.href="#",s.title=i,s.setAttribute("role","button"),s.setAttribute("aria-label",i),Di(s),ki(s,"click",Wi),ki(s,"click",o,this),ki(s,"click",this._refocusOnMap,this),s},_updateDisabled:function(){var t=this._map,i="leaflet-disabled";fi(this._zoomInButton,i),fi(this._zoomOutButton,i),!this._disabled&&t._zoom!==t.getMinZoom()||mi(this._zoomOutButton,i),!this._disabled&&t._zoom!==t.getMaxZoom()||mi(this._zoomInButton,i)}});$i.mergeOptions({zoomControl:!0}),$i.addInitHook(function(){this.options.zoomControl&&(this.zoomControl=new ee,this.addControl(this.zoomControl))});var ne=te.extend({options:{position:"bottomleft",maxWidth:100,metric:!0,imperial:!0},onAdd:function(t){var i="leaflet-control-scale",e=ui("div",i),n=this.options;return this._addScales(n,i+"-line",e),t.on(n.updateWhenIdle?"moveend":"move",this._update,this),t.whenReady(this._update,this),e},onRemove:function(t){t.off(this.options.updateWhenIdle?"moveend":"move",this._update,this)},_addScales:function(t,i,e){t.metric&&(this._mScale=ui("div",i,e)),t.imperial&&(this._iScale=ui("div",i,e))},_update:function(){var t=this._map,i=t.getSize().y/2,e=t.distance(t.containerPointToLatLng([0,i]),t.containerPointToLatLng([this.options.maxWidth,i]));this._updateScales(e)},_updateScales:function(t){this.options.metric&&t&&this._updateMetric(t),this.options.imperial&&t&&this._updateImperial(t)},_updateMetric:function(t){var i=this._getRoundNum(t),e=i<1e3?i+" m":i/1e3+" km";this._updateScale(this._mScale,e,i/t)},_updateImperial:function(t){var i,e,n,o=3.2808399*t;5280<o?(i=o/5280,e=this._getRoundNum(i),this._updateScale(this._iScale,e+" mi",e/i)):(n=this._getRoundNum(o),this._updateScale(this._iScale,n+" ft",n/o))},_updateScale:function(t,i,e){t.style.width=Math.round(this.options.maxWidth*e)+"px",t.innerHTML=i},_getRoundNum:function(t){var i=Math.pow(10,(Math.floor(t)+"").length-1),e=t/i;return i*(e=10<=e?10:5<=e?5:3<=e?3:2<=e?2:1)}}),oe=te.extend({options:{position:"bottomright",prefix:'<a href="https://leafletjs.com" title="A JS library for interactive maps">Leaflet</a>'},initialize:function(t){p(this,t),this._attributions={}},onAdd:function(t){for(var i in(t.attributionControl=this)._container=ui("div","leaflet-control-attribution"),Di(this._container),t._layers)t._layers[i].getAttribution&&this.addAttribution(t._layers[i].getAttribution());return this._update(),this._container},setPrefix:function(t){return this.options.prefix=t,this._update(),this},addAttribution:function(t){return t&&(this._attributions[t]||(this._attributions[t]=0),this._attributions[t]++,this._update()),this},removeAttribution:function(t){return t&&this._attributions[t]&&(this._attributions[t]--,this._update()),this},_update:function(){if(this._map){var t=[];for(var i in this._attributions)this._attributions[i]&&t.push(i);var e=[];this.options.prefix&&e.push(this.options.prefix),t.length&&e.push(t.join(", ")),this._container.innerHTML=e.join(" | ")}}});$i.mergeOptions({attributionControl:!0}),$i.addInitHook(function(){this.options.attributionControl&&(new oe).addTo(this)});te.Layers=ie,te.Zoom=ee,te.Scale=ne,te.Attribution=oe,Qi.layers=function(t,i,e){return new ie(t,i,e)},Qi.zoom=function(t){return new ee(t)},Qi.scale=function(t){return new ne(t)},Qi.attribution=function(t){return new oe(t)};var se=E.extend({initialize:function(t){this._map=t},enable:function(){return this._enabled||(this._enabled=!0,this.addHooks()),this},disable:function(){return this._enabled&&(this._enabled=!1,this.removeHooks()),this},enabled:function(){return!!this._enabled}});se.addTo=function(t,i){return t.addHandler(i,this),this};var re,ae={Events:Z},he=Tt?"touchstart mousedown":"mousedown",ue={mousedown:"mouseup",touchstart:"touchend",pointerdown:"touchend",MSPointerDown:"touchend"},le={mousedown:"mousemove",touchstart:"touchmove",pointerdown:"touchmove",MSPointerDown:"touchmove"},ce=k.extend({options:{clickTolerance:3},initialize:function(t,i,e,n){p(this,n),this._element=t,this._dragStartTarget=i||t,this._preventOutline=e},enable:function(){this._enabled||(ki(this._dragStartTarget,he,this._onDown,this),this._enabled=!0)},disable:function(){this._enabled&&(ce._dragging===this&&this.finishDrag(),Ai(this._dragStartTarget,he,this._onDown,this),this._enabled=!1,this._moved=!1)},_onDown:function(t){if(!t._simulated&&this._enabled&&(this._moved=!1,!pi(this._element,"leaflet-zoom-anim")&&!(ce._dragging||t.shiftKey||1!==t.which&&1!==t.button&&!t.touches||((ce._dragging=this)._preventOutline&&Mi(this._element),Ti(),Qt(),this._moving)))){this.fire("down");var i=t.touches?t.touches[0]:t,e=Si(this._element);this._startPoint=new B(i.clientX,i.clientY),this._parentScale=Ei(e),ki(document,le[t.type],this._onMove,this),ki(document,ue[t.type],this._onUp,this)}},_onMove:function(t){if(!t._simulated&&this._enabled)if(t.touches&&1<t.touches.length)this._moved=!0;else{var i=t.touches&&1===t.touches.length?t.touches[0]:t,e=new B(i.clientX,i.clientY)._subtract(this._startPoint);(e.x||e.y)&&(Math.abs(e.x)+Math.abs(e.y)<this.options.clickTolerance||(e.x/=this._parentScale.x,e.y/=this._parentScale.y,ji(t),this._moved||(this.fire("dragstart"),this._moved=!0,this._startPos=Li(this._element).subtract(e),mi(document.body,"leaflet-dragging"),this._lastTarget=t.target||t.srcElement,window.SVGElementInstance&&this._lastTarget instanceof SVGElementInstance&&(this._lastTarget=this._lastTarget.correspondingUseElement),mi(this._lastTarget,"leaflet-drag-target")),this._newPos=this._startPos.add(e),this._moving=!0,C(this._animRequest),this._lastEvent=t,this._animRequest=M(this._updatePosition,this,!0)))}},_updatePosition:function(){var t={originalEvent:this._lastEvent};this.fire("predrag",t),Pi(this._element,this._newPos),this.fire("drag",t)},_onUp:function(t){!t._simulated&&this._enabled&&this.finishDrag()},finishDrag:function(){for(var t in fi(document.body,"leaflet-dragging"),this._lastTarget&&(fi(this._lastTarget,"leaflet-drag-target"),this._lastTarget=null),le)Ai(document,le[t],this._onMove,this),Ai(document,ue[t],this._onUp,this);zi(),ti(),this._moved&&this._moving&&(C(this._animRequest),this.fire("dragend",{distance:this._newPos.distanceTo(this._startPos)})),this._moving=!1,ce._dragging=!1}});function _e(t,i){if(!i||!t.length)return t.slice();var e=i*i;return t=function(t,i){var e=t.length,n=new(typeof Uint8Array!=void 0+""?Uint8Array:Array)(e);n[0]=n[e-1]=1,function t(i,e,n,o,s){var r,a,h,u=0;for(a=o+1;a<=s-1;a++)h=ge(i[a],i[o],i[s],!0),u<h&&(r=a,u=h);n<u&&(e[r]=1,t(i,e,n,o,r),t(i,e,n,r,s))}(t,n,i,0,e-1);var o,s=[];for(o=0;o<e;o++)n[o]&&s.push(t[o]);return s}(t=function(t,i){for(var e=[t[0]],n=1,o=0,s=t.length;n<s;n++)r=t[n],a=t[o],void 0,h=a.x-r.x,u=a.y-r.y,i<h*h+u*u&&(e.push(t[n]),o=n);var r,a,h,u;o<s-1&&e.push(t[s-1]);return e}(t,e),e)}function de(t,i,e){return Math.sqrt(ge(t,i,e,!0))}function pe(t,i,e,n,o){var s,r,a,h=n?re:fe(t,e),u=fe(i,e);for(re=u;;){if(!(h|u))return[t,i];if(h&u)return!1;a=fe(r=me(t,i,s=h||u,e,o),e),s===h?(t=r,h=a):(i=r,u=a)}}function me(t,i,e,n,o){var s,r,a=i.x-t.x,h=i.y-t.y,u=n.min,l=n.max;return 8&e?(s=t.x+a*(l.y-t.y)/h,r=l.y):4&e?(s=t.x+a*(u.y-t.y)/h,r=u.y):2&e?(s=l.x,r=t.y+h*(l.x-t.x)/a):1&e&&(s=u.x,r=t.y+h*(u.x-t.x)/a),new B(s,r,o)}function fe(t,i){var e=0;return t.x<i.min.x?e|=1:t.x>i.max.x&&(e|=2),t.y<i.min.y?e|=4:t.y>i.max.y&&(e|=8),e}function ge(t,i,e,n){var o,s=i.x,r=i.y,a=e.x-s,h=e.y-r,u=a*a+h*h;return 0<u&&(1<(o=((t.x-s)*a+(t.y-r)*h)/u)?(s=e.x,r=e.y):0<o&&(s+=a*o,r+=h*o)),a=t.x-s,h=t.y-r,n?a*a+h*h:new B(s,r)}function ve(t){return!v(t[0])||"object"!=typeof t[0][0]&&void 0!==t[0][0]}function ye(t){return console.warn("Deprecated use of _flat, please use L.LineUtil.isFlat instead."),ve(t)}var xe=(Object.freeze||Object)({simplify:_e,pointToSegmentDistance:de,closestPointOnSegment:function(t,i,e){return ge(t,i,e)},clipSegment:pe,_getEdgeIntersection:me,_getBitCode:fe,_sqClosestPointOnSegment:ge,isFlat:ve,_flat:ye});function we(t,i,e){var n,o,s,r,a,h,u,l,c,_=[1,4,2,8];for(o=0,u=t.length;o<u;o++)t[o]._code=fe(t[o],i);for(r=0;r<4;r++){for(l=_[r],n=[],o=0,s=(u=t.length)-1;o<u;s=o++)a=t[o],h=t[s],a._code&l?h._code&l||((c=me(h,a,l,i,e))._code=fe(c,i),n.push(c)):(h._code&l&&((c=me(h,a,l,i,e))._code=fe(c,i),n.push(c)),n.push(a));t=n}return t}var Pe,Le=(Object.freeze||Object)({clipPolygon:we}),be={project:function(t){return new B(t.lng,t.lat)},unproject:function(t){return new j(t.y,t.x)},bounds:new O([-180,-90],[180,90])},Te={R:6378137,R_MINOR:6356752.314245179,bounds:new O([-20037508.34279,-15496570.73972],[20037508.34279,18764656.23138]),project:function(t){var i=Math.PI/180,e=this.R,n=t.lat*i,o=this.R_MINOR/e,s=Math.sqrt(1-o*o),r=s*Math.sin(n),a=Math.tan(Math.PI/4-n/2)/Math.pow((1-r)/(1+r),s/2);return n=-e*Math.log(Math.max(a,1e-10)),new B(t.lng*i*e,n)},unproject:function(t){for(var i,e=180/Math.PI,n=this.R,o=this.R_MINOR/n,s=Math.sqrt(1-o*o),r=Math.exp(-t.y/n),a=Math.PI/2-2*Math.atan(r),h=0,u=.1;h<15&&1e-7<Math.abs(u);h++)i=s*Math.sin(a),i=Math.pow((1-i)/(1+i),s/2),a+=u=Math.PI/2-2*Math.atan(r*i)-a;return new j(a*e,t.x*e/n)}},ze=(Object.freeze||Object)({LonLat:be,Mercator:Te,SphericalMercator:q}),Me=h({},U,{code:"EPSG:3395",projection:Te,transformation:(Pe=.5/(Math.PI*Te.R),K(Pe,.5,-Pe,.5))}),Ce=h({},U,{code:"EPSG:4326",projection:be,transformation:K(1/180,1,-1/180,.5)}),Se=h({},F,{projection:be,transformation:K(1,0,-1,0),scale:function(t){return Math.pow(2,t)},zoom:function(t){return Math.log(t)/Math.LN2},distance:function(t,i){var e=i.lng-t.lng,n=i.lat-t.lat;return Math.sqrt(e*e+n*n)},infinite:!0});F.Earth=U,F.EPSG3395=Me,F.EPSG3857=X,F.EPSG900913=J,F.EPSG4326=Ce,F.Simple=Se;var Ee=k.extend({options:{pane:"overlayPane",attribution:null,bubblingMouseEvents:!0},addTo:function(t){return t.addLayer(this),this},remove:function(){return this.removeFrom(this._map||this._mapToAdd)},removeFrom:function(t){return t&&t.removeLayer(this),this},getPane:function(t){return this._map.getPane(t?this.options[t]||t:this.options.pane)},addInteractiveTarget:function(t){return this._map._targets[u(t)]=this},removeInteractiveTarget:function(t){return delete this._map._targets[u(t)],this},getAttribution:function(){return this.options.attribution},_layerAdd:function(t){var i=t.target;if(i.hasLayer(this)){if(this._map=i,this._zoomAnimated=i._zoomAnimated,this.getEvents){var e=this.getEvents();i.on(e,this),this.once("remove",function(){i.off(e,this)},this)}this.onAdd(i),this.getAttribution&&i.attributionControl&&i.attributionControl.addAttribution(this.getAttribution()),this.fire("add"),i.fire("layeradd",{layer:this})}}});$i.include({addLayer:function(t){if(!t._layerAdd)throw new Error("The provided object is not a Layer.");var i=u(t);return this._layers[i]||((this._layers[i]=t)._mapToAdd=this,t.beforeAdd&&t.beforeAdd(this),this.whenReady(t._layerAdd,t)),this},removeLayer:function(t){var i=u(t);return this._layers[i]&&(this._loaded&&t.onRemove(this),t.getAttribution&&this.attributionControl&&this.attributionControl.removeAttribution(t.getAttribution()),delete this._layers[i],this._loaded&&(this.fire("layerremove",{layer:t}),t.fire("remove")),t._map=t._mapToAdd=null),this},hasLayer:function(t){return!!t&&u(t)in this._layers},eachLayer:function(t,i){for(var e in this._layers)t.call(i,this._layers[e]);return this},_addLayers:function(t){for(var i=0,e=(t=t?v(t)?t:[t]:[]).length;i<e;i++)this.addLayer(t[i])},_addZoomLimit:function(t){!isNaN(t.options.maxZoom)&&isNaN(t.options.minZoom)||(this._zoomBoundLayers[u(t)]=t,this._updateZoomLevels())},_removeZoomLimit:function(t){var i=u(t);this._zoomBoundLayers[i]&&(delete this._zoomBoundLayers[i],this._updateZoomLevels())},_updateZoomLevels:function(){var t=1/0,i=-1/0,e=this._getZoomSpan();for(var n in this._zoomBoundLayers){var o=this._zoomBoundLayers[n].options;t=void 0===o.minZoom?t:Math.min(t,o.minZoom),i=void 0===o.maxZoom?i:Math.max(i,o.maxZoom)}this._layersMaxZoom=i===-1/0?void 0:i,this._layersMinZoom=t===1/0?void 0:t,e!==this._getZoomSpan()&&this.fire("zoomlevelschange"),void 0===this.options.maxZoom&&this._layersMaxZoom&&this.getZoom()>this._layersMaxZoom&&this.setZoom(this._layersMaxZoom),void 0===this.options.minZoom&&this._layersMinZoom&&this.getZoom()<this._layersMinZoom&&this.setZoom(this._layersMinZoom)}});var Ze=Ee.extend({initialize:function(t,i){var e,n;if(p(this,i),this._layers={},t)for(e=0,n=t.length;e<n;e++)this.addLayer(t[e])},addLayer:function(t){var i=this.getLayerId(t);return this._layers[i]=t,this._map&&this._map.addLayer(t),this},removeLayer:function(t){var i=t in this._layers?t:this.getLayerId(t);return this._map&&this._layers[i]&&this._map.removeLayer(this._layers[i]),delete this._layers[i],this},hasLayer:function(t){return!!t&&(t in this._layers||this.getLayerId(t)in this._layers)},clearLayers:function(){return this.eachLayer(this.removeLayer,this)},invoke:function(t){var i,e,n=Array.prototype.slice.call(arguments,1);for(i in this._layers)(e=this._layers[i])[t]&&e[t].apply(e,n);return this},onAdd:function(t){this.eachLayer(t.addLayer,t)},onRemove:function(t){this.eachLayer(t.removeLayer,t)},eachLayer:function(t,i){for(var e in this._layers)t.call(i,this._layers[e]);return this},getLayer:function(t){return this._layers[t]},getLayers:function(){var t=[];return this.eachLayer(t.push,t),t},setZIndex:function(t){return this.invoke("setZIndex",t)},getLayerId:function(t){return u(t)}}),ke=Ze.extend({addLayer:function(t){return this.hasLayer(t)?this:(t.addEventParent(this),Ze.prototype.addLayer.call(this,t),this.fire("layeradd",{layer:t}))},removeLayer:function(t){return this.hasLayer(t)?(t in this._layers&&(t=this._layers[t]),t.removeEventParent(this),Ze.prototype.removeLayer.call(this,t),this.fire("layerremove",{layer:t})):this},setStyle:function(t){return this.invoke("setStyle",t)},bringToFront:function(){return this.invoke("bringToFront")},bringToBack:function(){return this.invoke("bringToBack")},getBounds:function(){var t=new N;for(var i in this._layers){var e=this._layers[i];t.extend(e.getBounds?e.getBounds():e.getLatLng())}return t}}),Be=E.extend({options:{popupAnchor:[0,0],tooltipAnchor:[0,0]},initialize:function(t){p(this,t)},createIcon:function(t){return this._createIcon("icon",t)},createShadow:function(t){return this._createIcon("shadow",t)},_createIcon:function(t,i){var e=this._getIconUrl(t);if(!e){if("icon"===t)throw new Error("iconUrl not set in Icon options (see the docs).");return null}var n=this._createImg(e,i&&"IMG"===i.tagName?i:null);return this._setIconStyles(n,t),n},_setIconStyles:function(t,i){var e=this.options,n=e[i+"Size"];"number"==typeof n&&(n=[n,n]);var o=I(n),s=I("shadow"===i&&e.shadowAnchor||e.iconAnchor||o&&o.divideBy(2,!0));t.className="leaflet-marker-"+i+" "+(e.className||""),s&&(t.style.marginLeft=-s.x+"px",t.style.marginTop=-s.y+"px"),o&&(t.style.width=o.x+"px",t.style.height=o.y+"px")},_createImg:function(t,i){return(i=i||document.createElement("img")).src=t,i},_getIconUrl:function(t){return Ct&&this.options[t+"RetinaUrl"]||this.options[t+"Url"]}});var Ae=Be.extend({options:{iconUrl:"marker-icon.png",iconRetinaUrl:"marker-icon-2x.png",shadowUrl:"marker-shadow.png",iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],tooltipAnchor:[16,-28],shadowSize:[41,41]},_getIconUrl:function(t){return Ae.imagePath||(Ae.imagePath=this._detectIconPath()),(this.options.imagePath||Ae.imagePath)+Be.prototype._getIconUrl.call(this,t)},_detectIconPath:function(){var t=ui("div","leaflet-default-icon-path",document.body),i=hi(t,"background-image")||hi(t,"backgroundImage");return document.body.removeChild(t),i=null===i||0!==i.indexOf("url")?"":i.replace(/^url\(["']?/,"").replace(/marker-icon\.png["']?\)$/,"")}}),Ie=se.extend({initialize:function(t){this._marker=t},addHooks:function(){var t=this._marker._icon;this._draggable||(this._draggable=new ce(t,t,!0)),this._draggable.on({dragstart:this._onDragStart,predrag:this._onPreDrag,drag:this._onDrag,dragend:this._onDragEnd},this).enable(),mi(t,"leaflet-marker-draggable")},removeHooks:function(){this._draggable.off({dragstart:this._onDragStart,predrag:this._onPreDrag,drag:this._onDrag,dragend:this._onDragEnd},this).disable(),this._marker._icon&&fi(this._marker._icon,"leaflet-marker-draggable")},moved:function(){return this._draggable&&this._draggable._moved},_adjustPan:function(t){var i=this._marker,e=i._map,n=this._marker.options.autoPanSpeed,o=this._marker.options.autoPanPadding,s=Li(i._icon),r=e.getPixelBounds(),a=e.getPixelOrigin(),h=R(r.min._subtract(a).add(o),r.max._subtract(a).subtract(o));if(!h.contains(s)){var u=I((Math.max(h.max.x,s.x)-h.max.x)/(r.max.x-h.max.x)-(Math.min(h.min.x,s.x)-h.min.x)/(r.min.x-h.min.x),(Math.max(h.max.y,s.y)-h.max.y)/(r.max.y-h.max.y)-(Math.min(h.min.y,s.y)-h.min.y)/(r.min.y-h.min.y)).multiplyBy(n);e.panBy(u,{animate:!1}),this._draggable._newPos._add(u),this._draggable._startPos._add(u),Pi(i._icon,this._draggable._newPos),this._onDrag(t),this._panRequest=M(this._adjustPan.bind(this,t))}},_onDragStart:function(){this._oldLatLng=this._marker.getLatLng(),this._marker.closePopup().fire("movestart").fire("dragstart")},_onPreDrag:function(t){this._marker.options.autoPan&&(C(this._panRequest),this._panRequest=M(this._adjustPan.bind(this,t)))},_onDrag:function(t){var i=this._marker,e=i._shadow,n=Li(i._icon),o=i._map.layerPointToLatLng(n);e&&Pi(e,n),i._latlng=o,t.latlng=o,t.oldLatLng=this._oldLatLng,i.fire("move",t).fire("drag",t)},_onDragEnd:function(t){C(this._panRequest),delete this._oldLatLng,this._marker.fire("moveend").fire("dragend",t)}}),Oe=Ee.extend({options:{icon:new Ae,interactive:!0,keyboard:!0,title:"",alt:"",zIndexOffset:0,opacity:1,riseOnHover:!1,riseOffset:250,pane:"markerPane",shadowPane:"shadowPane",bubblingMouseEvents:!1,draggable:!1,autoPan:!1,autoPanPadding:[50,50],autoPanSpeed:10},initialize:function(t,i){p(this,i),this._latlng=W(t)},onAdd:function(t){this._zoomAnimated=this._zoomAnimated&&t.options.markerZoomAnimation,this._zoomAnimated&&t.on("zoomanim",this._animateZoom,this),this._initIcon(),this.update()},onRemove:function(t){this.dragging&&this.dragging.enabled()&&(this.options.draggable=!0,this.dragging.removeHooks()),delete this.dragging,this._zoomAnimated&&t.off("zoomanim",this._animateZoom,this),this._removeIcon(),this._removeShadow()},getEvents:function(){return{zoom:this.update,viewreset:this.update}},getLatLng:function(){return this._latlng},setLatLng:function(t){var i=this._latlng;return this._latlng=W(t),this.update(),this.fire("move",{oldLatLng:i,latlng:this._latlng})},setZIndexOffset:function(t){return this.options.zIndexOffset=t,this.update()},getIcon:function(){return this.options.icon},setIcon:function(t){return this.options.icon=t,this._map&&(this._initIcon(),this.update()),this._popup&&this.bindPopup(this._popup,this._popup.options),this},getElement:function(){return this._icon},update:function(){if(this._icon&&this._map){var t=this._map.latLngToLayerPoint(this._latlng).round();this._setPos(t)}return this},_initIcon:function(){var t=this.options,i="leaflet-zoom-"+(this._zoomAnimated?"animated":"hide"),e=t.icon.createIcon(this._icon),n=!1;e!==this._icon&&(this._icon&&this._removeIcon(),n=!0,t.title&&(e.title=t.title),"IMG"===e.tagName&&(e.alt=t.alt||"")),mi(e,i),t.keyboard&&(e.tabIndex="0"),this._icon=e,t.riseOnHover&&this.on({mouseover:this._bringToFront,mouseout:this._resetZIndex});var o=t.icon.createShadow(this._shadow),s=!1;o!==this._shadow&&(this._removeShadow(),s=!0),o&&(mi(o,i),o.alt=""),this._shadow=o,t.opacity<1&&this._updateOpacity(),n&&this.getPane().appendChild(this._icon),this._initInteraction(),o&&s&&this.getPane(t.shadowPane).appendChild(this._shadow)},_removeIcon:function(){this.options.riseOnHover&&this.off({mouseover:this._bringToFront,mouseout:this._resetZIndex}),li(this._icon),this.removeInteractiveTarget(this._icon),this._icon=null},_removeShadow:function(){this._shadow&&li(this._shadow),this._shadow=null},_setPos:function(t){this._icon&&Pi(this._icon,t),this._shadow&&Pi(this._shadow,t),this._zIndex=t.y+this.options.zIndexOffset,this._resetZIndex()},_updateZIndex:function(t){this._icon&&(this._icon.style.zIndex=this._zIndex+t)},_animateZoom:function(t){var i=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center).round();this._setPos(i)},_initInteraction:function(){if(this.options.interactive&&(mi(this._icon,"leaflet-interactive"),this.addInteractiveTarget(this._icon),Ie)){var t=this.options.draggable;this.dragging&&(t=this.dragging.enabled(),this.dragging.disable()),this.dragging=new Ie(this),t&&this.dragging.enable()}},setOpacity:function(t){return this.options.opacity=t,this._map&&this._updateOpacity(),this},_updateOpacity:function(){var t=this.options.opacity;this._icon&&yi(this._icon,t),this._shadow&&yi(this._shadow,t)},_bringToFront:function(){this._updateZIndex(this.options.riseOffset)},_resetZIndex:function(){this._updateZIndex(0)},_getPopupAnchor:function(){return this.options.icon.options.popupAnchor},_getTooltipAnchor:function(){return this.options.icon.options.tooltipAnchor}});var Re=Ee.extend({options:{stroke:!0,color:"#3388ff",weight:3,opacity:1,lineCap:"round",lineJoin:"round",dashArray:null,dashOffset:null,fill:!1,fillColor:null,fillOpacity:.2,fillRule:"evenodd",interactive:!0,bubblingMouseEvents:!0},beforeAdd:function(t){this._renderer=t.getRenderer(this)},onAdd:function(){this._renderer._initPath(this),this._reset(),this._renderer._addPath(this)},onRemove:function(){this._renderer._removePath(this)},redraw:function(){return this._map&&this._renderer._updatePath(this),this},setStyle:function(t){return p(this,t),this._renderer&&(this._renderer._updateStyle(this),this.options.stroke&&t&&t.hasOwnProperty("weight")&&this._updateBounds()),this},bringToFront:function(){return this._renderer&&this._renderer._bringToFront(this),this},bringToBack:function(){return this._renderer&&this._renderer._bringToBack(this),this},getElement:function(){return this._path},_reset:function(){this._project(),this._update()},_clickTolerance:function(){return(this.options.stroke?this.options.weight/2:0)+this._renderer.options.tolerance}}),Ne=Re.extend({options:{fill:!0,radius:10},initialize:function(t,i){p(this,i),this._latlng=W(t),this._radius=this.options.radius},setLatLng:function(t){var i=this._latlng;return this._latlng=W(t),this.redraw(),this.fire("move",{oldLatLng:i,latlng:this._latlng})},getLatLng:function(){return this._latlng},setRadius:function(t){return this.options.radius=this._radius=t,this.redraw()},getRadius:function(){return this._radius},setStyle:function(t){var i=t&&t.radius||this._radius;return Re.prototype.setStyle.call(this,t),this.setRadius(i),this},_project:function(){this._point=this._map.latLngToLayerPoint(this._latlng),this._updateBounds()},_updateBounds:function(){var t=this._radius,i=this._radiusY||t,e=this._clickTolerance(),n=[t+e,i+e];this._pxBounds=new O(this._point.subtract(n),this._point.add(n))},_update:function(){this._map&&this._updatePath()},_updatePath:function(){this._renderer._updateCircle(this)},_empty:function(){return this._radius&&!this._renderer._bounds.intersects(this._pxBounds)},_containsPoint:function(t){return t.distanceTo(this._point)<=this._radius+this._clickTolerance()}});var De=Ne.extend({initialize:function(t,i,e){if("number"==typeof i&&(i=h({},e,{radius:i})),p(this,i),this._latlng=W(t),isNaN(this.options.radius))throw new Error("Circle radius cannot be NaN");this._mRadius=this.options.radius},setRadius:function(t){return this._mRadius=t,this.redraw()},getRadius:function(){return this._mRadius},getBounds:function(){var t=[this._radius,this._radiusY||this._radius];return new N(this._map.layerPointToLatLng(this._point.subtract(t)),this._map.layerPointToLatLng(this._point.add(t)))},setStyle:Re.prototype.setStyle,_project:function(){var t=this._latlng.lng,i=this._latlng.lat,e=this._map,n=e.options.crs;if(n.distance===U.distance){var o=Math.PI/180,s=this._mRadius/U.R/o,r=e.project([i+s,t]),a=e.project([i-s,t]),h=r.add(a).divideBy(2),u=e.unproject(h).lat,l=Math.acos((Math.cos(s*o)-Math.sin(i*o)*Math.sin(u*o))/(Math.cos(i*o)*Math.cos(u*o)))/o;!isNaN(l)&&0!==l||(l=s/Math.cos(Math.PI/180*i)),this._point=h.subtract(e.getPixelOrigin()),this._radius=isNaN(l)?0:h.x-e.project([u,t-l]).x,this._radiusY=h.y-r.y}else{var c=n.unproject(n.project(this._latlng).subtract([this._mRadius,0]));this._point=e.latLngToLayerPoint(this._latlng),this._radius=this._point.x-e.latLngToLayerPoint(c).x}this._updateBounds()}});var je=Re.extend({options:{smoothFactor:1,noClip:!1},initialize:function(t,i){p(this,i),this._setLatLngs(t)},getLatLngs:function(){return this._latlngs},setLatLngs:function(t){return this._setLatLngs(t),this.redraw()},isEmpty:function(){return!this._latlngs.length},closestLayerPoint:function(t){for(var i,e,n=1/0,o=null,s=ge,r=0,a=this._parts.length;r<a;r++)for(var h=this._parts[r],u=1,l=h.length;u<l;u++){var c=s(t,i=h[u-1],e=h[u],!0);c<n&&(n=c,o=s(t,i,e))}return o&&(o.distance=Math.sqrt(n)),o},getCenter:function(){if(!this._map)throw new Error("Must add layer to map before using getCenter()");var t,i,e,n,o,s,r,a=this._rings[0],h=a.length;if(!h)return null;for(i=t=0;t<h-1;t++)i+=a[t].distanceTo(a[t+1])/2;if(0===i)return this._map.layerPointToLatLng(a[0]);for(n=t=0;t<h-1;t++)if(o=a[t],s=a[t+1],i<(n+=e=o.distanceTo(s)))return r=(n-i)/e,this._map.layerPointToLatLng([s.x-r*(s.x-o.x),s.y-r*(s.y-o.y)])},getBounds:function(){return this._bounds},addLatLng:function(t,i){return i=i||this._defaultShape(),t=W(t),i.push(t),this._bounds.extend(t),this.redraw()},_setLatLngs:function(t){this._bounds=new N,this._latlngs=this._convertLatLngs(t)},_defaultShape:function(){return ve(this._latlngs)?this._latlngs:this._latlngs[0]},_convertLatLngs:function(t){for(var i=[],e=ve(t),n=0,o=t.length;n<o;n++)e?(i[n]=W(t[n]),this._bounds.extend(i[n])):i[n]=this._convertLatLngs(t[n]);return i},_project:function(){var t=new O;this._rings=[],this._projectLatlngs(this._latlngs,this._rings,t),this._bounds.isValid()&&t.isValid()&&(this._rawPxBounds=t,this._updateBounds())},_updateBounds:function(){var t=this._clickTolerance(),i=new B(t,t);this._pxBounds=new O([this._rawPxBounds.min.subtract(i),this._rawPxBounds.max.add(i)])},_projectLatlngs:function(t,i,e){var n,o,s=t[0]instanceof j,r=t.length;if(s){for(o=[],n=0;n<r;n++)o[n]=this._map.latLngToLayerPoint(t[n]),e.extend(o[n]);i.push(o)}else for(n=0;n<r;n++)this._projectLatlngs(t[n],i,e)},_clipPoints:function(){var t=this._renderer._bounds;if(this._parts=[],this._pxBounds&&this._pxBounds.intersects(t))if(this.options.noClip)this._parts=this._rings;else{var i,e,n,o,s,r,a,h=this._parts;for(n=i=0,o=this._rings.length;i<o;i++)for(e=0,s=(a=this._rings[i]).length;e<s-1;e++)(r=pe(a[e],a[e+1],t,e,!0))&&(h[n]=h[n]||[],h[n].push(r[0]),r[1]===a[e+1]&&e!==s-2||(h[n].push(r[1]),n++))}},_simplifyPoints:function(){for(var t=this._parts,i=this.options.smoothFactor,e=0,n=t.length;e<n;e++)t[e]=_e(t[e],i)},_update:function(){this._map&&(this._clipPoints(),this._simplifyPoints(),this._updatePath())},_updatePath:function(){this._renderer._updatePoly(this)},_containsPoint:function(t,i){var e,n,o,s,r,a,h=this._clickTolerance();if(!this._pxBounds||!this._pxBounds.contains(t))return!1;for(e=0,s=this._parts.length;e<s;e++)for(n=0,o=(r=(a=this._parts[e]).length)-1;n<r;o=n++)if((i||0!==n)&&de(t,a[o],a[n])<=h)return!0;return!1}});je._flat=ye;var We=je.extend({options:{fill:!0},isEmpty:function(){return!this._latlngs.length||!this._latlngs[0].length},getCenter:function(){if(!this._map)throw new Error("Must add layer to map before using getCenter()");var t,i,e,n,o,s,r,a,h,u=this._rings[0],l=u.length;if(!l)return null;for(s=r=a=0,t=0,i=l-1;t<l;i=t++)e=u[t],n=u[i],o=e.y*n.x-n.y*e.x,r+=(e.x+n.x)*o,a+=(e.y+n.y)*o,s+=3*o;return h=0===s?u[0]:[r/s,a/s],this._map.layerPointToLatLng(h)},_convertLatLngs:function(t){var i=je.prototype._convertLatLngs.call(this,t),e=i.length;return 2<=e&&i[0]instanceof j&&i[0].equals(i[e-1])&&i.pop(),i},_setLatLngs:function(t){je.prototype._setLatLngs.call(this,t),ve(this._latlngs)&&(this._latlngs=[this._latlngs])},_defaultShape:function(){return ve(this._latlngs[0])?this._latlngs[0]:this._latlngs[0][0]},_clipPoints:function(){var t=this._renderer._bounds,i=this.options.weight,e=new B(i,i);if(t=new O(t.min.subtract(e),t.max.add(e)),this._parts=[],this._pxBounds&&this._pxBounds.intersects(t))if(this.options.noClip)this._parts=this._rings;else for(var n,o=0,s=this._rings.length;o<s;o++)(n=we(this._rings[o],t,!0)).length&&this._parts.push(n)},_updatePath:function(){this._renderer._updatePoly(this,!0)},_containsPoint:function(t){var i,e,n,o,s,r,a,h,u=!1;if(!this._pxBounds||!this._pxBounds.contains(t))return!1;for(o=0,a=this._parts.length;o<a;o++)for(s=0,r=(h=(i=this._parts[o]).length)-1;s<h;r=s++)e=i[s],n=i[r],e.y>t.y!=n.y>t.y&&t.x<(n.x-e.x)*(t.y-e.y)/(n.y-e.y)+e.x&&(u=!u);return u||je.prototype._containsPoint.call(this,t,!0)}});var He=ke.extend({initialize:function(t,i){p(this,i),this._layers={},t&&this.addData(t)},addData:function(t){var i,e,n,o=v(t)?t:t.features;if(o){for(i=0,e=o.length;i<e;i++)((n=o[i]).geometries||n.geometry||n.features||n.coordinates)&&this.addData(n);return this}var s=this.options;if(s.filter&&!s.filter(t))return this;var r=Fe(t,s);return r?(r.feature=Xe(t),r.defaultOptions=r.options,this.resetStyle(r),s.onEachFeature&&s.onEachFeature(t,r),this.addLayer(r)):this},resetStyle:function(t){return void 0===t?this.eachLayer(this.resetStyle,this):(t.options=h({},t.defaultOptions),this._setLayerStyle(t,this.options.style),this)},setStyle:function(i){return this.eachLayer(function(t){this._setLayerStyle(t,i)},this)},_setLayerStyle:function(t,i){t.setStyle&&("function"==typeof i&&(i=i(t.feature)),t.setStyle(i))}});function Fe(t,i){var e,n,o,s,r="Feature"===t.type?t.geometry:t,a=r?r.coordinates:null,h=[],u=i&&i.pointToLayer,l=i&&i.coordsToLatLng||Ve;if(!a&&!r)return null;switch(r.type){case"Point":return Ue(u,t,e=l(a),i);case"MultiPoint":for(o=0,s=a.length;o<s;o++)e=l(a[o]),h.push(Ue(u,t,e,i));return new ke(h);case"LineString":case"MultiLineString":return n=qe(a,"LineString"===r.type?0:1,l),new je(n,i);case"Polygon":case"MultiPolygon":return n=qe(a,"Polygon"===r.type?1:2,l),new We(n,i);case"GeometryCollection":for(o=0,s=r.geometries.length;o<s;o++){var c=Fe({geometry:r.geometries[o],type:"Feature",properties:t.properties},i);c&&h.push(c)}return new ke(h);default:throw new Error("Invalid GeoJSON object.")}}function Ue(t,i,e,n){return t?t(i,e):new Oe(e,n&&n.markersInheritOptions&&n)}function Ve(t){return new j(t[1],t[0],t[2])}function qe(t,i,e){for(var n,o=[],s=0,r=t.length;s<r;s++)n=i?qe(t[s],i-1,e):(e||Ve)(t[s]),o.push(n);return o}function Ge(t,i){return i="number"==typeof i?i:6,void 0!==t.alt?[c(t.lng,i),c(t.lat,i),c(t.alt,i)]:[c(t.lng,i),c(t.lat,i)]}function Ke(t,i,e,n){for(var o=[],s=0,r=t.length;s<r;s++)o.push(i?Ke(t[s],i-1,e,n):Ge(t[s],n));return!i&&e&&o.push(o[0]),o}function Ye(t,i){return t.feature?h({},t.feature,{geometry:i}):Xe(i)}function Xe(t){return"Feature"===t.type||"FeatureCollection"===t.type?t:{type:"Feature",properties:{},geometry:t}}var Je={toGeoJSON:function(t){return Ye(this,{type:"Point",coordinates:Ge(this.getLatLng(),t)})}};function $e(t,i){return new He(t,i)}Oe.include(Je),De.include(Je),Ne.include(Je),je.include({toGeoJSON:function(t){var i=!ve(this._latlngs);return Ye(this,{type:(i?"Multi":"")+"LineString",coordinates:Ke(this._latlngs,i?1:0,!1,t)})}}),We.include({toGeoJSON:function(t){var i=!ve(this._latlngs),e=i&&!ve(this._latlngs[0]),n=Ke(this._latlngs,e?2:i?1:0,!0,t);return i||(n=[n]),Ye(this,{type:(e?"Multi":"")+"Polygon",coordinates:n})}}),Ze.include({toMultiPoint:function(i){var e=[];return this.eachLayer(function(t){e.push(t.toGeoJSON(i).geometry.coordinates)}),Ye(this,{type:"MultiPoint",coordinates:e})},toGeoJSON:function(n){var t=this.feature&&this.feature.geometry&&this.feature.geometry.type;if("MultiPoint"===t)return this.toMultiPoint(n);var o="GeometryCollection"===t,s=[];return this.eachLayer(function(t){if(t.toGeoJSON){var i=t.toGeoJSON(n);if(o)s.push(i.geometry);else{var e=Xe(i);"FeatureCollection"===e.type?s.push.apply(s,e.features):s.push(e)}}}),o?Ye(this,{geometries:s,type:"GeometryCollection"}):{type:"FeatureCollection",features:s}}});var Qe=$e,tn=Ee.extend({options:{opacity:1,alt:"",interactive:!1,crossOrigin:!1,errorOverlayUrl:"",zIndex:1,className:""},initialize:function(t,i,e){this._url=t,this._bounds=D(i),p(this,e)},onAdd:function(){this._image||(this._initImage(),this.options.opacity<1&&this._updateOpacity()),this.options.interactive&&(mi(this._image,"leaflet-interactive"),this.addInteractiveTarget(this._image)),this.getPane().appendChild(this._image),this._reset()},onRemove:function(){li(this._image),this.options.interactive&&this.removeInteractiveTarget(this._image)},setOpacity:function(t){return this.options.opacity=t,this._image&&this._updateOpacity(),this},setStyle:function(t){return t.opacity&&this.setOpacity(t.opacity),this},bringToFront:function(){return this._map&&_i(this._image),this},bringToBack:function(){return this._map&&di(this._image),this},setUrl:function(t){return this._url=t,this._image&&(this._image.src=t),this},setBounds:function(t){return this._bounds=D(t),this._map&&this._reset(),this},getEvents:function(){var t={zoom:this._reset,viewreset:this._reset};return this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},setZIndex:function(t){return this.options.zIndex=t,this._updateZIndex(),this},getBounds:function(){return this._bounds},getElement:function(){return this._image},_initImage:function(){var t="IMG"===this._url.tagName,i=this._image=t?this._url:ui("img");mi(i,"leaflet-image-layer"),this._zoomAnimated&&mi(i,"leaflet-zoom-animated"),this.options.className&&mi(i,this.options.className),i.onselectstart=l,i.onmousemove=l,i.onload=a(this.fire,this,"load"),i.onerror=a(this._overlayOnError,this,"error"),!this.options.crossOrigin&&""!==this.options.crossOrigin||(i.crossOrigin=!0===this.options.crossOrigin?"":this.options.crossOrigin),this.options.zIndex&&this._updateZIndex(),t?this._url=i.src:(i.src=this._url,i.alt=this.options.alt)},_animateZoom:function(t){var i=this._map.getZoomScale(t.zoom),e=this._map._latLngBoundsToNewLayerBounds(this._bounds,t.zoom,t.center).min;wi(this._image,e,i)},_reset:function(){var t=this._image,i=new O(this._map.latLngToLayerPoint(this._bounds.getNorthWest()),this._map.latLngToLayerPoint(this._bounds.getSouthEast())),e=i.getSize();Pi(t,i.min),t.style.width=e.x+"px",t.style.height=e.y+"px"},_updateOpacity:function(){yi(this._image,this.options.opacity)},_updateZIndex:function(){this._image&&void 0!==this.options.zIndex&&null!==this.options.zIndex&&(this._image.style.zIndex=this.options.zIndex)},_overlayOnError:function(){this.fire("error");var t=this.options.errorOverlayUrl;t&&this._url!==t&&(this._url=t,this._image.src=t)}}),en=tn.extend({options:{autoplay:!0,loop:!0,keepAspectRatio:!0},_initImage:function(){var t="VIDEO"===this._url.tagName,i=this._image=t?this._url:ui("video");if(mi(i,"leaflet-image-layer"),this._zoomAnimated&&mi(i,"leaflet-zoom-animated"),this.options.className&&mi(i,this.options.className),i.onselectstart=l,i.onmousemove=l,i.onloadeddata=a(this.fire,this,"load"),t){for(var e=i.getElementsByTagName("source"),n=[],o=0;o<e.length;o++)n.push(e[o].src);this._url=0<e.length?n:[i.src]}else{v(this._url)||(this._url=[this._url]),!this.options.keepAspectRatio&&i.style.hasOwnProperty("objectFit")&&(i.style.objectFit="fill"),i.autoplay=!!this.options.autoplay,i.loop=!!this.options.loop;for(var s=0;s<this._url.length;s++){var r=ui("source");r.src=this._url[s],i.appendChild(r)}}}});var nn=tn.extend({_initImage:function(){var t=this._image=this._url;mi(t,"leaflet-image-layer"),this._zoomAnimated&&mi(t,"leaflet-zoom-animated"),this.options.className&&mi(t,this.options.className),t.onselectstart=l,t.onmousemove=l}});var on=Ee.extend({options:{offset:[0,7],className:"",pane:"popupPane"},initialize:function(t,i){p(this,t),this._source=i},onAdd:function(t){this._zoomAnimated=t._zoomAnimated,this._container||this._initLayout(),t._fadeAnimated&&yi(this._container,0),clearTimeout(this._removeTimeout),this.getPane().appendChild(this._container),this.update(),t._fadeAnimated&&yi(this._container,1),this.bringToFront()},onRemove:function(t){t._fadeAnimated?(yi(this._container,0),this._removeTimeout=setTimeout(a(li,void 0,this._container),200)):li(this._container)},getLatLng:function(){return this._latlng},setLatLng:function(t){return this._latlng=W(t),this._map&&(this._updatePosition(),this._adjustPan()),this},getContent:function(){return this._content},setContent:function(t){return this._content=t,this.update(),this},getElement:function(){return this._container},update:function(){this._map&&(this._container.style.visibility="hidden",this._updateContent(),this._updateLayout(),this._updatePosition(),this._container.style.visibility="",this._adjustPan())},getEvents:function(){var t={zoom:this._updatePosition,viewreset:this._updatePosition};return this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},isOpen:function(){return!!this._map&&this._map.hasLayer(this)},bringToFront:function(){return this._map&&_i(this._container),this},bringToBack:function(){return this._map&&di(this._container),this},_prepareOpen:function(t,i,e){if(i instanceof Ee||(e=i,i=t),i instanceof ke)for(var n in t._layers){i=t._layers[n];break}if(!e)if(i.getCenter)e=i.getCenter();else{if(!i.getLatLng)throw new Error("Unable to get source layer LatLng.");e=i.getLatLng()}return this._source=i,this.update(),e},_updateContent:function(){if(this._content){var t=this._contentNode,i="function"==typeof this._content?this._content(this._source||this):this._content;if("string"==typeof i)t.innerHTML=i;else{for(;t.hasChildNodes();)t.removeChild(t.firstChild);t.appendChild(i)}this.fire("contentupdate")}},_updatePosition:function(){if(this._map){var t=this._map.latLngToLayerPoint(this._latlng),i=I(this.options.offset),e=this._getAnchor();this._zoomAnimated?Pi(this._container,t.add(e)):i=i.add(t).add(e);var n=this._containerBottom=-i.y,o=this._containerLeft=-Math.round(this._containerWidth/2)+i.x;this._container.style.bottom=n+"px",this._container.style.left=o+"px"}},_getAnchor:function(){return[0,0]}}),sn=on.extend({options:{maxWidth:300,minWidth:50,maxHeight:null,autoPan:!0,autoPanPaddingTopLeft:null,autoPanPaddingBottomRight:null,autoPanPadding:[5,5],keepInView:!1,closeButton:!0,autoClose:!0,closeOnEscapeKey:!0,className:""},openOn:function(t){return t.openPopup(this),this},onAdd:function(t){on.prototype.onAdd.call(this,t),t.fire("popupopen",{popup:this}),this._source&&(this._source.fire("popupopen",{popup:this},!0),this._source instanceof Re||this._source.on("preclick",Ri))},onRemove:function(t){on.prototype.onRemove.call(this,t),t.fire("popupclose",{popup:this}),this._source&&(this._source.fire("popupclose",{popup:this},!0),this._source instanceof Re||this._source.off("preclick",Ri))},getEvents:function(){var t=on.prototype.getEvents.call(this);return(void 0!==this.options.closeOnClick?this.options.closeOnClick:this._map.options.closePopupOnClick)&&(t.preclick=this._close),this.options.keepInView&&(t.moveend=this._adjustPan),t},_close:function(){this._map&&this._map.closePopup(this)},_initLayout:function(){var t="leaflet-popup",i=this._container=ui("div",t+" "+(this.options.className||"")+" leaflet-zoom-animated"),e=this._wrapper=ui("div",t+"-content-wrapper",i);if(this._contentNode=ui("div",t+"-content",e),Di(e),Ni(this._contentNode),ki(e,"contextmenu",Ri),this._tipContainer=ui("div",t+"-tip-container",i),this._tip=ui("div",t+"-tip",this._tipContainer),this.options.closeButton){var n=this._closeButton=ui("a",t+"-close-button",i);n.href="#close",n.innerHTML="&#215;",ki(n,"click",this._onCloseButtonClick,this)}},_updateLayout:function(){var t=this._contentNode,i=t.style;i.width="",i.whiteSpace="nowrap";var e=t.offsetWidth;e=Math.min(e,this.options.maxWidth),e=Math.max(e,this.options.minWidth),i.width=e+1+"px",i.whiteSpace="",i.height="";var n=t.offsetHeight,o=this.options.maxHeight,s="leaflet-popup-scrolled";o&&o<n?(i.height=o+"px",mi(t,s)):fi(t,s),this._containerWidth=this._container.offsetWidth},_animateZoom:function(t){var i=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center),e=this._getAnchor();Pi(this._container,i.add(e))},_adjustPan:function(){if(this.options.autoPan){this._map._panAnim&&this._map._panAnim.stop();var t=this._map,i=parseInt(hi(this._container,"marginBottom"),10)||0,e=this._container.offsetHeight+i,n=this._containerWidth,o=new B(this._containerLeft,-e-this._containerBottom);o._add(Li(this._container));var s=t.layerPointToContainerPoint(o),r=I(this.options.autoPanPadding),a=I(this.options.autoPanPaddingTopLeft||r),h=I(this.options.autoPanPaddingBottomRight||r),u=t.getSize(),l=0,c=0;s.x+n+h.x>u.x&&(l=s.x+n-u.x+h.x),s.x-l-a.x<0&&(l=s.x-a.x),s.y+e+h.y>u.y&&(c=s.y+e-u.y+h.y),s.y-c-a.y<0&&(c=s.y-a.y),(l||c)&&t.fire("autopanstart").panBy([l,c])}},_onCloseButtonClick:function(t){this._close(),Wi(t)},_getAnchor:function(){return I(this._source&&this._source._getPopupAnchor?this._source._getPopupAnchor():[0,0])}});$i.mergeOptions({closePopupOnClick:!0}),$i.include({openPopup:function(t,i,e){return t instanceof sn||(t=new sn(e).setContent(t)),i&&t.setLatLng(i),this.hasLayer(t)?this:(this._popup&&this._popup.options.autoClose&&this.closePopup(),this._popup=t,this.addLayer(t))},closePopup:function(t){return t&&t!==this._popup||(t=this._popup,this._popup=null),t&&this.removeLayer(t),this}}),Ee.include({bindPopup:function(t,i){return t instanceof sn?(p(t,i),(this._popup=t)._source=this):(this._popup&&!i||(this._popup=new sn(i,this)),this._popup.setContent(t)),this._popupHandlersAdded||(this.on({click:this._openPopup,keypress:this._onKeyPress,remove:this.closePopup,move:this._movePopup}),this._popupHandlersAdded=!0),this},unbindPopup:function(){return this._popup&&(this.off({click:this._openPopup,keypress:this._onKeyPress,remove:this.closePopup,move:this._movePopup}),this._popupHandlersAdded=!1,this._popup=null),this},openPopup:function(t,i){return this._popup&&this._map&&(i=this._popup._prepareOpen(this,t,i),this._map.openPopup(this._popup,i)),this},closePopup:function(){return this._popup&&this._popup._close(),this},togglePopup:function(t){return this._popup&&(this._popup._map?this.closePopup():this.openPopup(t)),this},isPopupOpen:function(){return!!this._popup&&this._popup.isOpen()},setPopupContent:function(t){return this._popup&&this._popup.setContent(t),this},getPopup:function(){return this._popup},_openPopup:function(t){var i=t.layer||t.target;this._popup&&this._map&&(Wi(t),i instanceof Re?this.openPopup(t.layer||t.target,t.latlng):this._map.hasLayer(this._popup)&&this._popup._source===i?this.closePopup():this.openPopup(i,t.latlng))},_movePopup:function(t){this._popup.setLatLng(t.latlng)},_onKeyPress:function(t){13===t.originalEvent.keyCode&&this._openPopup(t)}});var rn=on.extend({options:{pane:"tooltipPane",offset:[0,0],direction:"auto",permanent:!1,sticky:!1,interactive:!1,opacity:.9},onAdd:function(t){on.prototype.onAdd.call(this,t),this.setOpacity(this.options.opacity),t.fire("tooltipopen",{tooltip:this}),this._source&&this._source.fire("tooltipopen",{tooltip:this},!0)},onRemove:function(t){on.prototype.onRemove.call(this,t),t.fire("tooltipclose",{tooltip:this}),this._source&&this._source.fire("tooltipclose",{tooltip:this},!0)},getEvents:function(){var t=on.prototype.getEvents.call(this);return Tt&&!this.options.permanent&&(t.preclick=this._close),t},_close:function(){this._map&&this._map.closeTooltip(this)},_initLayout:function(){var t="leaflet-tooltip "+(this.options.className||"")+" leaflet-zoom-"+(this._zoomAnimated?"animated":"hide");this._contentNode=this._container=ui("div",t)},_updateLayout:function(){},_adjustPan:function(){},_setPosition:function(t){var i=this._map,e=this._container,n=i.latLngToContainerPoint(i.getCenter()),o=i.layerPointToContainerPoint(t),s=this.options.direction,r=e.offsetWidth,a=e.offsetHeight,h=I(this.options.offset),u=this._getAnchor();t="top"===s?t.add(I(-r/2+h.x,-a+h.y+u.y,!0)):"bottom"===s?t.subtract(I(r/2-h.x,-h.y,!0)):"center"===s?t.subtract(I(r/2+h.x,a/2-u.y+h.y,!0)):"right"===s||"auto"===s&&o.x<n.x?(s="right",t.add(I(h.x+u.x,u.y-a/2+h.y,!0))):(s="left",t.subtract(I(r+u.x-h.x,a/2-u.y-h.y,!0))),fi(e,"leaflet-tooltip-right"),fi(e,"leaflet-tooltip-left"),fi(e,"leaflet-tooltip-top"),fi(e,"leaflet-tooltip-bottom"),mi(e,"leaflet-tooltip-"+s),Pi(e,t)},_updatePosition:function(){var t=this._map.latLngToLayerPoint(this._latlng);this._setPosition(t)},setOpacity:function(t){this.options.opacity=t,this._container&&yi(this._container,t)},_animateZoom:function(t){var i=this._map._latLngToNewLayerPoint(this._latlng,t.zoom,t.center);this._setPosition(i)},_getAnchor:function(){return I(this._source&&this._source._getTooltipAnchor&&!this.options.sticky?this._source._getTooltipAnchor():[0,0])}});$i.include({openTooltip:function(t,i,e){return t instanceof rn||(t=new rn(e).setContent(t)),i&&t.setLatLng(i),this.hasLayer(t)?this:this.addLayer(t)},closeTooltip:function(t){return t&&this.removeLayer(t),this}}),Ee.include({bindTooltip:function(t,i){return t instanceof rn?(p(t,i),(this._tooltip=t)._source=this):(this._tooltip&&!i||(this._tooltip=new rn(i,this)),this._tooltip.setContent(t)),this._initTooltipInteractions(),this._tooltip.options.permanent&&this._map&&this._map.hasLayer(this)&&this.openTooltip(),this},unbindTooltip:function(){return this._tooltip&&(this._initTooltipInteractions(!0),this.closeTooltip(),this._tooltip=null),this},_initTooltipInteractions:function(t){if(t||!this._tooltipHandlersAdded){var i=t?"off":"on",e={remove:this.closeTooltip,move:this._moveTooltip};this._tooltip.options.permanent?e.add=this._openTooltip:(e.mouseover=this._openTooltip,e.mouseout=this.closeTooltip,this._tooltip.options.sticky&&(e.mousemove=this._moveTooltip),Tt&&(e.click=this._openTooltip)),this[i](e),this._tooltipHandlersAdded=!t}},openTooltip:function(t,i){return this._tooltip&&this._map&&(i=this._tooltip._prepareOpen(this,t,i),this._map.openTooltip(this._tooltip,i),this._tooltip.options.interactive&&this._tooltip._container&&(mi(this._tooltip._container,"leaflet-clickable"),this.addInteractiveTarget(this._tooltip._container))),this},closeTooltip:function(){return this._tooltip&&(this._tooltip._close(),this._tooltip.options.interactive&&this._tooltip._container&&(fi(this._tooltip._container,"leaflet-clickable"),this.removeInteractiveTarget(this._tooltip._container))),this},toggleTooltip:function(t){return this._tooltip&&(this._tooltip._map?this.closeTooltip():this.openTooltip(t)),this},isTooltipOpen:function(){return this._tooltip.isOpen()},setTooltipContent:function(t){return this._tooltip&&this._tooltip.setContent(t),this},getTooltip:function(){return this._tooltip},_openTooltip:function(t){var i=t.layer||t.target;this._tooltip&&this._map&&this.openTooltip(i,this._tooltip.options.sticky?t.latlng:void 0)},_moveTooltip:function(t){var i,e,n=t.latlng;this._tooltip.options.sticky&&t.originalEvent&&(i=this._map.mouseEventToContainerPoint(t.originalEvent),e=this._map.containerPointToLayerPoint(i),n=this._map.layerPointToLatLng(e)),this._tooltip.setLatLng(n)}});var an=Be.extend({options:{iconSize:[12,12],html:!1,bgPos:null,className:"leaflet-div-icon"},createIcon:function(t){var i=t&&"DIV"===t.tagName?t:document.createElement("div"),e=this.options;if(e.html instanceof Element?(ci(i),i.appendChild(e.html)):i.innerHTML=!1!==e.html?e.html:"",e.bgPos){var n=I(e.bgPos);i.style.backgroundPosition=-n.x+"px "+-n.y+"px"}return this._setIconStyles(i,"icon"),i},createShadow:function(){return null}});Be.Default=Ae;var hn=Ee.extend({options:{tileSize:256,opacity:1,updateWhenIdle:xt,updateWhenZooming:!0,updateInterval:200,zIndex:1,bounds:null,minZoom:0,maxZoom:void 0,maxNativeZoom:void 0,minNativeZoom:void 0,noWrap:!1,pane:"tilePane",className:"",keepBuffer:2},initialize:function(t){p(this,t)},onAdd:function(){this._initContainer(),this._levels={},this._tiles={},this._resetView(),this._update()},beforeAdd:function(t){t._addZoomLimit(this)},onRemove:function(t){this._removeAllTiles(),li(this._container),t._removeZoomLimit(this),this._container=null,this._tileZoom=void 0},bringToFront:function(){return this._map&&(_i(this._container),this._setAutoZIndex(Math.max)),this},bringToBack:function(){return this._map&&(di(this._container),this._setAutoZIndex(Math.min)),this},getContainer:function(){return this._container},setOpacity:function(t){return this.options.opacity=t,this._updateOpacity(),this},setZIndex:function(t){return this.options.zIndex=t,this._updateZIndex(),this},isLoading:function(){return this._loading},redraw:function(){return this._map&&(this._removeAllTiles(),this._update()),this},getEvents:function(){var t={viewprereset:this._invalidateAll,viewreset:this._resetView,zoom:this._resetView,moveend:this._onMoveEnd};return this.options.updateWhenIdle||(this._onMove||(this._onMove=o(this._onMoveEnd,this.options.updateInterval,this)),t.move=this._onMove),this._zoomAnimated&&(t.zoomanim=this._animateZoom),t},createTile:function(){return document.createElement("div")},getTileSize:function(){var t=this.options.tileSize;return t instanceof B?t:new B(t,t)},_updateZIndex:function(){this._container&&void 0!==this.options.zIndex&&null!==this.options.zIndex&&(this._container.style.zIndex=this.options.zIndex)},_setAutoZIndex:function(t){for(var i,e=this.getPane().children,n=-t(-1/0,1/0),o=0,s=e.length;o<s;o++)i=e[o].style.zIndex,e[o]!==this._container&&i&&(n=t(n,+i));isFinite(n)&&(this.options.zIndex=n+t(-1,1),this._updateZIndex())},_updateOpacity:function(){if(this._map&&!et){yi(this._container,this.options.opacity);var t=+new Date,i=!1,e=!1;for(var n in this._tiles){var o=this._tiles[n];if(o.current&&o.loaded){var s=Math.min(1,(t-o.loaded)/200);yi(o.el,s),s<1?i=!0:(o.active?e=!0:this._onOpaqueTile(o),o.active=!0)}}e&&!this._noPrune&&this._pruneTiles(),i&&(C(this._fadeFrame),this._fadeFrame=M(this._updateOpacity,this))}},_onOpaqueTile:l,_initContainer:function(){this._container||(this._container=ui("div","leaflet-layer "+(this.options.className||"")),this._updateZIndex(),this.options.opacity<1&&this._updateOpacity(),this.getPane().appendChild(this._container))},_updateLevels:function(){var t=this._tileZoom,i=this.options.maxZoom;if(void 0!==t){for(var e in this._levels)this._levels[e].el.children.length||e===t?(this._levels[e].el.style.zIndex=i-Math.abs(t-e),this._onUpdateLevel(e)):(li(this._levels[e].el),this._removeTilesAtZoom(e),this._onRemoveLevel(e),delete this._levels[e]);var n=this._levels[t],o=this._map;return n||((n=this._levels[t]={}).el=ui("div","leaflet-tile-container leaflet-zoom-animated",this._container),n.el.style.zIndex=i,n.origin=o.project(o.unproject(o.getPixelOrigin()),t).round(),n.zoom=t,this._setZoomTransform(n,o.getCenter(),o.getZoom()),n.el.offsetWidth,this._onCreateLevel(n)),this._level=n}},_onUpdateLevel:l,_onRemoveLevel:l,_onCreateLevel:l,_pruneTiles:function(){if(this._map){var t,i,e=this._map.getZoom();if(e>this.options.maxZoom||e<this.options.minZoom)this._removeAllTiles();else{for(t in this._tiles)(i=this._tiles[t]).retain=i.current;for(t in this._tiles)if((i=this._tiles[t]).current&&!i.active){var n=i.coords;this._retainParent(n.x,n.y,n.z,n.z-5)||this._retainChildren(n.x,n.y,n.z,n.z+2)}for(t in this._tiles)this._tiles[t].retain||this._removeTile(t)}}},_removeTilesAtZoom:function(t){for(var i in this._tiles)this._tiles[i].coords.z===t&&this._removeTile(i)},_removeAllTiles:function(){for(var t in this._tiles)this._removeTile(t)},_invalidateAll:function(){for(var t in this._levels)li(this._levels[t].el),this._onRemoveLevel(t),delete this._levels[t];this._removeAllTiles(),this._tileZoom=void 0},_retainParent:function(t,i,e,n){var o=Math.floor(t/2),s=Math.floor(i/2),r=e-1,a=new B(+o,+s);a.z=+r;var h=this._tileCoordsToKey(a),u=this._tiles[h];return u&&u.active?u.retain=!0:(u&&u.loaded&&(u.retain=!0),n<r&&this._retainParent(o,s,r,n))},_retainChildren:function(t,i,e,n){for(var o=2*t;o<2*t+2;o++)for(var s=2*i;s<2*i+2;s++){var r=new B(o,s);r.z=e+1;var a=this._tileCoordsToKey(r),h=this._tiles[a];h&&h.active?h.retain=!0:(h&&h.loaded&&(h.retain=!0),e+1<n&&this._retainChildren(o,s,e+1,n))}},_resetView:function(t){var i=t&&(t.pinch||t.flyTo);this._setView(this._map.getCenter(),this._map.getZoom(),i,i)},_animateZoom:function(t){this._setView(t.center,t.zoom,!0,t.noUpdate)},_clampZoom:function(t){var i=this.options;return void 0!==i.minNativeZoom&&t<i.minNativeZoom?i.minNativeZoom:void 0!==i.maxNativeZoom&&i.maxNativeZoom<t?i.maxNativeZoom:t},_setView:function(t,i,e,n){var o=this._clampZoom(Math.round(i));(void 0!==this.options.maxZoom&&o>this.options.maxZoom||void 0!==this.options.minZoom&&o<this.options.minZoom)&&(o=void 0);var s=this.options.updateWhenZooming&&o!==this._tileZoom;n&&!s||(this._tileZoom=o,this._abortLoading&&this._abortLoading(),this._updateLevels(),this._resetGrid(),void 0!==o&&this._update(t),e||this._pruneTiles(),this._noPrune=!!e),this._setZoomTransforms(t,i)},_setZoomTransforms:function(t,i){for(var e in this._levels)this._setZoomTransform(this._levels[e],t,i)},_setZoomTransform:function(t,i,e){var n=this._map.getZoomScale(e,t.zoom),o=t.origin.multiplyBy(n).subtract(this._map._getNewPixelOrigin(i,e)).round();yt?wi(t.el,o,n):Pi(t.el,o)},_resetGrid:function(){var t=this._map,i=t.options.crs,e=this._tileSize=this.getTileSize(),n=this._tileZoom,o=this._map.getPixelWorldBounds(this._tileZoom);o&&(this._globalTileRange=this._pxBoundsToTileRange(o)),this._wrapX=i.wrapLng&&!this.options.noWrap&&[Math.floor(t.project([0,i.wrapLng[0]],n).x/e.x),Math.ceil(t.project([0,i.wrapLng[1]],n).x/e.y)],this._wrapY=i.wrapLat&&!this.options.noWrap&&[Math.floor(t.project([i.wrapLat[0],0],n).y/e.x),Math.ceil(t.project([i.wrapLat[1],0],n).y/e.y)]},_onMoveEnd:function(){this._map&&!this._map._animatingZoom&&this._update()},_getTiledPixelBounds:function(t){var i=this._map,e=i._animatingZoom?Math.max(i._animateToZoom,i.getZoom()):i.getZoom(),n=i.getZoomScale(e,this._tileZoom),o=i.project(t,this._tileZoom).floor(),s=i.getSize().divideBy(2*n);return new O(o.subtract(s),o.add(s))},_update:function(t){var i=this._map;if(i){var e=this._clampZoom(i.getZoom());if(void 0===t&&(t=i.getCenter()),void 0!==this._tileZoom){var n=this._getTiledPixelBounds(t),o=this._pxBoundsToTileRange(n),s=o.getCenter(),r=[],a=this.options.keepBuffer,h=new O(o.getBottomLeft().subtract([a,-a]),o.getTopRight().add([a,-a]));if(!(isFinite(o.min.x)&&isFinite(o.min.y)&&isFinite(o.max.x)&&isFinite(o.max.y)))throw new Error("Attempted to load an infinite number of tiles");for(var u in this._tiles){var l=this._tiles[u].coords;l.z===this._tileZoom&&h.contains(new B(l.x,l.y))||(this._tiles[u].current=!1)}if(1<Math.abs(e-this._tileZoom))this._setView(t,e);else{for(var c=o.min.y;c<=o.max.y;c++)for(var _=o.min.x;_<=o.max.x;_++){var d=new B(_,c);if(d.z=this._tileZoom,this._isValidTile(d)){var p=this._tiles[this._tileCoordsToKey(d)];p?p.current=!0:r.push(d)}}if(r.sort(function(t,i){return t.distanceTo(s)-i.distanceTo(s)}),0!==r.length){this._loading||(this._loading=!0,this.fire("loading"));var m=document.createDocumentFragment();for(_=0;_<r.length;_++)this._addTile(r[_],m);this._level.el.appendChild(m)}}}}},_isValidTile:function(t){var i=this._map.options.crs;if(!i.infinite){var e=this._globalTileRange;if(!i.wrapLng&&(t.x<e.min.x||t.x>e.max.x)||!i.wrapLat&&(t.y<e.min.y||t.y>e.max.y))return!1}if(!this.options.bounds)return!0;var n=this._tileCoordsToBounds(t);return D(this.options.bounds).overlaps(n)},_keyToBounds:function(t){return this._tileCoordsToBounds(this._keyToTileCoords(t))},_tileCoordsToNwSe:function(t){var i=this._map,e=this.getTileSize(),n=t.scaleBy(e),o=n.add(e);return[i.unproject(n,t.z),i.unproject(o,t.z)]},_tileCoordsToBounds:function(t){var i=this._tileCoordsToNwSe(t),e=new N(i[0],i[1]);return this.options.noWrap||(e=this._map.wrapLatLngBounds(e)),e},_tileCoordsToKey:function(t){return t.x+":"+t.y+":"+t.z},_keyToTileCoords:function(t){var i=t.split(":"),e=new B(+i[0],+i[1]);return e.z=+i[2],e},_removeTile:function(t){var i=this._tiles[t];i&&(li(i.el),delete this._tiles[t],this.fire("tileunload",{tile:i.el,coords:this._keyToTileCoords(t)}))},_initTile:function(t){mi(t,"leaflet-tile");var i=this.getTileSize();t.style.width=i.x+"px",t.style.height=i.y+"px",t.onselectstart=l,t.onmousemove=l,et&&this.options.opacity<1&&yi(t,this.options.opacity),st&&!rt&&(t.style.WebkitBackfaceVisibility="hidden")},_addTile:function(t,i){var e=this._getTilePos(t),n=this._tileCoordsToKey(t),o=this.createTile(this._wrapCoords(t),a(this._tileReady,this,t));this._initTile(o),this.createTile.length<2&&M(a(this._tileReady,this,t,null,o)),Pi(o,e),this._tiles[n]={el:o,coords:t,current:!0},i.appendChild(o),this.fire("tileloadstart",{tile:o,coords:t})},_tileReady:function(t,i,e){i&&this.fire("tileerror",{error:i,tile:e,coords:t});var n=this._tileCoordsToKey(t);(e=this._tiles[n])&&(e.loaded=+new Date,this._map._fadeAnimated?(yi(e.el,0),C(this._fadeFrame),this._fadeFrame=M(this._updateOpacity,this)):(e.active=!0,this._pruneTiles()),i||(mi(e.el,"leaflet-tile-loaded"),this.fire("tileload",{tile:e.el,coords:t})),this._noTilesToLoad()&&(this._loading=!1,this.fire("load"),et||!this._map._fadeAnimated?M(this._pruneTiles,this):setTimeout(a(this._pruneTiles,this),250)))},_getTilePos:function(t){return t.scaleBy(this.getTileSize()).subtract(this._level.origin)},_wrapCoords:function(t){var i=new B(this._wrapX?r(t.x,this._wrapX):t.x,this._wrapY?r(t.y,this._wrapY):t.y);return i.z=t.z,i},_pxBoundsToTileRange:function(t){var i=this.getTileSize();return new O(t.min.unscaleBy(i).floor(),t.max.unscaleBy(i).ceil().subtract([1,1]))},_noTilesToLoad:function(){for(var t in this._tiles)if(!this._tiles[t].loaded)return!1;return!0}});var un=hn.extend({options:{minZoom:0,maxZoom:18,subdomains:"abc",errorTileUrl:"",zoomOffset:0,tms:!1,zoomReverse:!1,detectRetina:!1,crossOrigin:!1},initialize:function(t,i){this._url=t,(i=p(this,i)).detectRetina&&Ct&&0<i.maxZoom&&(i.tileSize=Math.floor(i.tileSize/2),i.zoomReverse?(i.zoomOffset--,i.minZoom++):(i.zoomOffset++,i.maxZoom--),i.minZoom=Math.max(0,i.minZoom)),"string"==typeof i.subdomains&&(i.subdomains=i.subdomains.split("")),st||this.on("tileunload",this._onTileRemove)},setUrl:function(t,i){return this._url===t&&void 0===i&&(i=!0),this._url=t,i||this.redraw(),this},createTile:function(t,i){var e=document.createElement("img");return ki(e,"load",a(this._tileOnLoad,this,i,e)),ki(e,"error",a(this._tileOnError,this,i,e)),!this.options.crossOrigin&&""!==this.options.crossOrigin||(e.crossOrigin=!0===this.options.crossOrigin?"":this.options.crossOrigin),e.alt="",e.setAttribute("role","presentation"),e.src=this.getTileUrl(t),e},getTileUrl:function(t){var i={r:Ct?"@2x":"",s:this._getSubdomain(t),x:t.x,y:t.y,z:this._getZoomForUrl()};if(this._map&&!this._map.options.crs.infinite){var e=this._globalTileRange.max.y-t.y;this.options.tms&&(i.y=e),i["-y"]=e}return g(this._url,h(i,this.options))},_tileOnLoad:function(t,i){et?setTimeout(a(t,this,null,i),0):t(null,i)},_tileOnError:function(t,i,e){var n=this.options.errorTileUrl;n&&i.getAttribute("src")!==n&&(i.src=n),t(e,i)},_onTileRemove:function(t){t.tile.onload=null},_getZoomForUrl:function(){var t=this._tileZoom,i=this.options.maxZoom;return this.options.zoomReverse&&(t=i-t),t+this.options.zoomOffset},_getSubdomain:function(t){var i=Math.abs(t.x+t.y)%this.options.subdomains.length;return this.options.subdomains[i]},_abortLoading:function(){var t,i;for(t in this._tiles)this._tiles[t].coords.z!==this._tileZoom&&((i=this._tiles[t].el).onload=l,i.onerror=l,i.complete||(i.src=x,li(i),delete this._tiles[t]))},_removeTile:function(t){var i=this._tiles[t];if(i)return ht||i.el.setAttribute("src",x),hn.prototype._removeTile.call(this,t)},_tileReady:function(t,i,e){if(this._map&&(!e||e.getAttribute("src")!==x))return hn.prototype._tileReady.call(this,t,i,e)}});function ln(t,i){return new un(t,i)}var cn=un.extend({defaultWmsParams:{service:"WMS",request:"GetMap",layers:"",styles:"",format:"image/jpeg",transparent:!1,version:"1.1.1"},options:{crs:null,uppercase:!1},initialize:function(t,i){this._url=t;var e=h({},this.defaultWmsParams);for(var n in i)n in this.options||(e[n]=i[n]);var o=(i=p(this,i)).detectRetina&&Ct?2:1,s=this.getTileSize();e.width=s.x*o,e.height=s.y*o,this.wmsParams=e},onAdd:function(t){this._crs=this.options.crs||t.options.crs,this._wmsVersion=parseFloat(this.wmsParams.version);var i=1.3<=this._wmsVersion?"crs":"srs";this.wmsParams[i]=this._crs.code,un.prototype.onAdd.call(this,t)},getTileUrl:function(t){var i=this._tileCoordsToNwSe(t),e=this._crs,n=R(e.project(i[0]),e.project(i[1])),o=n.min,s=n.max,r=(1.3<=this._wmsVersion&&this._crs===Ce?[o.y,o.x,s.y,s.x]:[o.x,o.y,s.x,s.y]).join(","),a=un.prototype.getTileUrl.call(this,t);return a+m(this.wmsParams,a,this.options.uppercase)+(this.options.uppercase?"&BBOX=":"&bbox=")+r},setParams:function(t,i){return h(this.wmsParams,t),i||this.redraw(),this}});un.WMS=cn,ln.wms=function(t,i){return new cn(t,i)};var _n=Ee.extend({options:{padding:.1,tolerance:0},initialize:function(t){p(this,t),u(this),this._layers=this._layers||{}},onAdd:function(){this._container||(this._initContainer(),this._zoomAnimated&&mi(this._container,"leaflet-zoom-animated")),this.getPane().appendChild(this._container),this._update(),this.on("update",this._updatePaths,this)},onRemove:function(){this.off("update",this._updatePaths,this),this._destroyContainer()},getEvents:function(){var t={viewreset:this._reset,zoom:this._onZoom,moveend:this._update,zoomend:this._onZoomEnd};return this._zoomAnimated&&(t.zoomanim=this._onAnimZoom),t},_onAnimZoom:function(t){this._updateTransform(t.center,t.zoom)},_onZoom:function(){this._updateTransform(this._map.getCenter(),this._map.getZoom())},_updateTransform:function(t,i){var e=this._map.getZoomScale(i,this._zoom),n=Li(this._container),o=this._map.getSize().multiplyBy(.5+this.options.padding),s=this._map.project(this._center,i),r=this._map.project(t,i).subtract(s),a=o.multiplyBy(-e).add(n).add(o).subtract(r);yt?wi(this._container,a,e):Pi(this._container,a)},_reset:function(){for(var t in this._update(),this._updateTransform(this._center,this._zoom),this._layers)this._layers[t]._reset()},_onZoomEnd:function(){for(var t in this._layers)this._layers[t]._project()},_updatePaths:function(){for(var t in this._layers)this._layers[t]._update()},_update:function(){var t=this.options.padding,i=this._map.getSize(),e=this._map.containerPointToLayerPoint(i.multiplyBy(-t)).round();this._bounds=new O(e,e.add(i.multiplyBy(1+2*t)).round()),this._center=this._map.getCenter(),this._zoom=this._map.getZoom()}}),dn=_n.extend({getEvents:function(){var t=_n.prototype.getEvents.call(this);return t.viewprereset=this._onViewPreReset,t},_onViewPreReset:function(){this._postponeUpdatePaths=!0},onAdd:function(){_n.prototype.onAdd.call(this),this._draw()},_initContainer:function(){var t=this._container=document.createElement("canvas");ki(t,"mousemove",this._onMouseMove,this),ki(t,"click dblclick mousedown mouseup contextmenu",this._onClick,this),ki(t,"mouseout",this._handleMouseOut,this),this._ctx=t.getContext("2d")},_destroyContainer:function(){C(this._redrawRequest),delete this._ctx,li(this._container),Ai(this._container),delete this._container},_updatePaths:function(){if(!this._postponeUpdatePaths){for(var t in this._redrawBounds=null,this._layers)this._layers[t]._update();this._redraw()}},_update:function(){if(!this._map._animatingZoom||!this._bounds){_n.prototype._update.call(this);var t=this._bounds,i=this._container,e=t.getSize(),n=Ct?2:1;Pi(i,t.min),i.width=n*e.x,i.height=n*e.y,i.style.width=e.x+"px",i.style.height=e.y+"px",Ct&&this._ctx.scale(2,2),this._ctx.translate(-t.min.x,-t.min.y),this.fire("update")}},_reset:function(){_n.prototype._reset.call(this),this._postponeUpdatePaths&&(this._postponeUpdatePaths=!1,this._updatePaths())},_initPath:function(t){this._updateDashArray(t);var i=(this._layers[u(t)]=t)._order={layer:t,prev:this._drawLast,next:null};this._drawLast&&(this._drawLast.next=i),this._drawLast=i,this._drawFirst=this._drawFirst||this._drawLast},_addPath:function(t){this._requestRedraw(t)},_removePath:function(t){var i=t._order,e=i.next,n=i.prev;e?e.prev=n:this._drawLast=n,n?n.next=e:this._drawFirst=e,delete t._order,delete this._layers[u(t)],this._requestRedraw(t)},_updatePath:function(t){this._extendRedrawBounds(t),t._project(),t._update(),this._requestRedraw(t)},_updateStyle:function(t){this._updateDashArray(t),this._requestRedraw(t)},_updateDashArray:function(t){if("string"==typeof t.options.dashArray){var i,e,n=t.options.dashArray.split(/[, ]+/),o=[];for(e=0;e<n.length;e++){if(i=Number(n[e]),isNaN(i))return;o.push(i)}t.options._dashArray=o}else t.options._dashArray=t.options.dashArray},_requestRedraw:function(t){this._map&&(this._extendRedrawBounds(t),this._redrawRequest=this._redrawRequest||M(this._redraw,this))},_extendRedrawBounds:function(t){if(t._pxBounds){var i=(t.options.weight||0)+1;this._redrawBounds=this._redrawBounds||new O,this._redrawBounds.extend(t._pxBounds.min.subtract([i,i])),this._redrawBounds.extend(t._pxBounds.max.add([i,i]))}},_redraw:function(){this._redrawRequest=null,this._redrawBounds&&(this._redrawBounds.min._floor(),this._redrawBounds.max._ceil()),this._clear(),this._draw(),this._redrawBounds=null},_clear:function(){var t=this._redrawBounds;if(t){var i=t.getSize();this._ctx.clearRect(t.min.x,t.min.y,i.x,i.y)}else this._ctx.clearRect(0,0,this._container.width,this._container.height)},_draw:function(){var t,i=this._redrawBounds;if(this._ctx.save(),i){var e=i.getSize();this._ctx.beginPath(),this._ctx.rect(i.min.x,i.min.y,e.x,e.y),this._ctx.clip()}this._drawing=!0;for(var n=this._drawFirst;n;n=n.next)t=n.layer,(!i||t._pxBounds&&t._pxBounds.intersects(i))&&t._updatePath();this._drawing=!1,this._ctx.restore()},_updatePoly:function(t,i){if(this._drawing){var e,n,o,s,r=t._parts,a=r.length,h=this._ctx;if(a){for(h.beginPath(),e=0;e<a;e++){for(n=0,o=r[e].length;n<o;n++)s=r[e][n],h[n?"lineTo":"moveTo"](s.x,s.y);i&&h.closePath()}this._fillStroke(h,t)}}},_updateCircle:function(t){if(this._drawing&&!t._empty()){var i=t._point,e=this._ctx,n=Math.max(Math.round(t._radius),1),o=(Math.max(Math.round(t._radiusY),1)||n)/n;1!=o&&(e.save(),e.scale(1,o)),e.beginPath(),e.arc(i.x,i.y/o,n,0,2*Math.PI,!1),1!=o&&e.restore(),this._fillStroke(e,t)}},_fillStroke:function(t,i){var e=i.options;e.fill&&(t.globalAlpha=e.fillOpacity,t.fillStyle=e.fillColor||e.color,t.fill(e.fillRule||"evenodd")),e.stroke&&0!==e.weight&&(t.setLineDash&&t.setLineDash(i.options&&i.options._dashArray||[]),t.globalAlpha=e.opacity,t.lineWidth=e.weight,t.strokeStyle=e.color,t.lineCap=e.lineCap,t.lineJoin=e.lineJoin,t.stroke())},_onClick:function(t){for(var i,e,n=this._map.mouseEventToLayerPoint(t),o=this._drawFirst;o;o=o.next)(i=o.layer).options.interactive&&i._containsPoint(n)&&!this._map._draggableMoved(i)&&(e=i);e&&(Gi(t),this._fireEvent([e],t))},_onMouseMove:function(t){if(this._map&&!this._map.dragging.moving()&&!this._map._animatingZoom){var i=this._map.mouseEventToLayerPoint(t);this._handleMouseHover(t,i)}},_handleMouseOut:function(t){var i=this._hoveredLayer;i&&(fi(this._container,"leaflet-interactive"),this._fireEvent([i],t,"mouseout"),this._hoveredLayer=null,this._mouseHoverThrottled=!1)},_handleMouseHover:function(t,i){if(!this._mouseHoverThrottled){for(var e,n,o=this._drawFirst;o;o=o.next)(e=o.layer).options.interactive&&e._containsPoint(i)&&(n=e);n!==this._hoveredLayer&&(this._handleMouseOut(t),n&&(mi(this._container,"leaflet-interactive"),this._fireEvent([n],t,"mouseover"),this._hoveredLayer=n)),this._hoveredLayer&&this._fireEvent([this._hoveredLayer],t),this._mouseHoverThrottled=!0,setTimeout(L.bind(function(){this._mouseHoverThrottled=!1},this),32)}},_fireEvent:function(t,i,e){this._map._fireDOMEvent(i,e||i.type,t)},_bringToFront:function(t){var i=t._order;if(i){var e=i.next,n=i.prev;e&&((e.prev=n)?n.next=e:e&&(this._drawFirst=e),i.prev=this._drawLast,(this._drawLast.next=i).next=null,this._drawLast=i,this._requestRedraw(t))}},_bringToBack:function(t){var i=t._order;if(i){var e=i.next,n=i.prev;n&&((n.next=e)?e.prev=n:n&&(this._drawLast=n),i.prev=null,i.next=this._drawFirst,this._drawFirst.prev=i,this._drawFirst=i,this._requestRedraw(t))}}});function pn(t){return Et?new dn(t):null}var mn=function(){try{return document.namespaces.add("lvml","urn:schemas-microsoft-com:vml"),function(t){return document.createElement("<lvml:"+t+' class="lvml">')}}catch(t){return function(t){return document.createElement("<"+t+' xmlns="urn:schemas-microsoft.com:vml" class="lvml">')}}}(),fn={_initContainer:function(){this._container=ui("div","leaflet-vml-container")},_update:function(){this._map._animatingZoom||(_n.prototype._update.call(this),this.fire("update"))},_initPath:function(t){var i=t._container=mn("shape");mi(i,"leaflet-vml-shape "+(this.options.className||"")),i.coordsize="1 1",t._path=mn("path"),i.appendChild(t._path),this._updateStyle(t),this._layers[u(t)]=t},_addPath:function(t){var i=t._container;this._container.appendChild(i),t.options.interactive&&t.addInteractiveTarget(i)},_removePath:function(t){var i=t._container;li(i),t.removeInteractiveTarget(i),delete this._layers[u(t)]},_updateStyle:function(t){var i=t._stroke,e=t._fill,n=t.options,o=t._container;o.stroked=!!n.stroke,o.filled=!!n.fill,n.stroke?(i||(i=t._stroke=mn("stroke")),o.appendChild(i),i.weight=n.weight+"px",i.color=n.color,i.opacity=n.opacity,n.dashArray?i.dashStyle=v(n.dashArray)?n.dashArray.join(" "):n.dashArray.replace(/( *, *)/g," "):i.dashStyle="",i.endcap=n.lineCap.replace("butt","flat"),i.joinstyle=n.lineJoin):i&&(o.removeChild(i),t._stroke=null),n.fill?(e||(e=t._fill=mn("fill")),o.appendChild(e),e.color=n.fillColor||n.color,e.opacity=n.fillOpacity):e&&(o.removeChild(e),t._fill=null)},_updateCircle:function(t){var i=t._point.round(),e=Math.round(t._radius),n=Math.round(t._radiusY||e);this._setPath(t,t._empty()?"M0 0":"AL "+i.x+","+i.y+" "+e+","+n+" 0,23592600")},_setPath:function(t,i){t._path.v=i},_bringToFront:function(t){_i(t._container)},_bringToBack:function(t){di(t._container)}},gn=kt?mn:$,vn=_n.extend({getEvents:function(){var t=_n.prototype.getEvents.call(this);return t.zoomstart=this._onZoomStart,t},_initContainer:function(){this._container=gn("svg"),this._container.setAttribute("pointer-events","none"),this._rootGroup=gn("g"),this._container.appendChild(this._rootGroup)},_destroyContainer:function(){li(this._container),Ai(this._container),delete this._container,delete this._rootGroup,delete this._svgSize},_onZoomStart:function(){this._update()},_update:function(){if(!this._map._animatingZoom||!this._bounds){_n.prototype._update.call(this);var t=this._bounds,i=t.getSize(),e=this._container;this._svgSize&&this._svgSize.equals(i)||(this._svgSize=i,e.setAttribute("width",i.x),e.setAttribute("height",i.y)),Pi(e,t.min),e.setAttribute("viewBox",[t.min.x,t.min.y,i.x,i.y].join(" ")),this.fire("update")}},_initPath:function(t){var i=t._path=gn("path");t.options.className&&mi(i,t.options.className),t.options.interactive&&mi(i,"leaflet-interactive"),this._updateStyle(t),this._layers[u(t)]=t},_addPath:function(t){this._rootGroup||this._initContainer(),this._rootGroup.appendChild(t._path),t.addInteractiveTarget(t._path)},_removePath:function(t){li(t._path),t.removeInteractiveTarget(t._path),delete this._layers[u(t)]},_updatePath:function(t){t._project(),t._update()},_updateStyle:function(t){var i=t._path,e=t.options;i&&(e.stroke?(i.setAttribute("stroke",e.color),i.setAttribute("stroke-opacity",e.opacity),i.setAttribute("stroke-width",e.weight),i.setAttribute("stroke-linecap",e.lineCap),i.setAttribute("stroke-linejoin",e.lineJoin),e.dashArray?i.setAttribute("stroke-dasharray",e.dashArray):i.removeAttribute("stroke-dasharray"),e.dashOffset?i.setAttribute("stroke-dashoffset",e.dashOffset):i.removeAttribute("stroke-dashoffset")):i.setAttribute("stroke","none"),e.fill?(i.setAttribute("fill",e.fillColor||e.color),i.setAttribute("fill-opacity",e.fillOpacity),i.setAttribute("fill-rule",e.fillRule||"evenodd")):i.setAttribute("fill","none"))},_updatePoly:function(t,i){this._setPath(t,Q(t._parts,i))},_updateCircle:function(t){var i=t._point,e=Math.max(Math.round(t._radius),1),n="a"+e+","+(Math.max(Math.round(t._radiusY),1)||e)+" 0 1,0 ",o=t._empty()?"M0 0":"M"+(i.x-e)+","+i.y+n+2*e+",0 "+n+2*-e+",0 ";this._setPath(t,o)},_setPath:function(t,i){t._path.setAttribute("d",i)},_bringToFront:function(t){_i(t._path)},_bringToBack:function(t){di(t._path)}});function yn(t){return Zt||kt?new vn(t):null}kt&&vn.include(fn),$i.include({getRenderer:function(t){var i=t.options.renderer||this._getPaneRenderer(t.options.pane)||this.options.renderer||this._renderer;return i||(i=this._renderer=this._createRenderer()),this.hasLayer(i)||this.addLayer(i),i},_getPaneRenderer:function(t){if("overlayPane"===t||void 0===t)return!1;var i=this._paneRenderers[t];return void 0===i&&(i=this._createRenderer({pane:t}),this._paneRenderers[t]=i),i},_createRenderer:function(t){return this.options.preferCanvas&&pn(t)||yn(t)}});var xn=We.extend({initialize:function(t,i){We.prototype.initialize.call(this,this._boundsToLatLngs(t),i)},setBounds:function(t){return this.setLatLngs(this._boundsToLatLngs(t))},_boundsToLatLngs:function(t){return[(t=D(t)).getSouthWest(),t.getNorthWest(),t.getNorthEast(),t.getSouthEast()]}});vn.create=gn,vn.pointsToPath=Q,He.geometryToLayer=Fe,He.coordsToLatLng=Ve,He.coordsToLatLngs=qe,He.latLngToCoords=Ge,He.latLngsToCoords=Ke,He.getFeature=Ye,He.asFeature=Xe,$i.mergeOptions({boxZoom:!0});var wn=se.extend({initialize:function(t){this._map=t,this._container=t._container,this._pane=t._panes.overlayPane,this._resetStateTimeout=0,t.on("unload",this._destroy,this)},addHooks:function(){ki(this._container,"mousedown",this._onMouseDown,this)},removeHooks:function(){Ai(this._container,"mousedown",this._onMouseDown,this)},moved:function(){return this._moved},_destroy:function(){li(this._pane),delete this._pane},_resetState:function(){this._resetStateTimeout=0,this._moved=!1},_clearDeferredResetState:function(){0!==this._resetStateTimeout&&(clearTimeout(this._resetStateTimeout),this._resetStateTimeout=0)},_onMouseDown:function(t){if(!t.shiftKey||1!==t.which&&1!==t.button)return!1;this._clearDeferredResetState(),this._resetState(),Qt(),Ti(),this._startPoint=this._map.mouseEventToContainerPoint(t),ki(document,{contextmenu:Wi,mousemove:this._onMouseMove,mouseup:this._onMouseUp,keydown:this._onKeyDown},this)},_onMouseMove:function(t){this._moved||(this._moved=!0,this._box=ui("div","leaflet-zoom-box",this._container),mi(this._container,"leaflet-crosshair"),this._map.fire("boxzoomstart")),this._point=this._map.mouseEventToContainerPoint(t);var i=new O(this._point,this._startPoint),e=i.getSize();Pi(this._box,i.min),this._box.style.width=e.x+"px",this._box.style.height=e.y+"px"},_finish:function(){this._moved&&(li(this._box),fi(this._container,"leaflet-crosshair")),ti(),zi(),Ai(document,{contextmenu:Wi,mousemove:this._onMouseMove,mouseup:this._onMouseUp,keydown:this._onKeyDown},this)},_onMouseUp:function(t){if((1===t.which||1===t.button)&&(this._finish(),this._moved)){this._clearDeferredResetState(),this._resetStateTimeout=setTimeout(a(this._resetState,this),0);var i=new N(this._map.containerPointToLatLng(this._startPoint),this._map.containerPointToLatLng(this._point));this._map.fitBounds(i).fire("boxzoomend",{boxZoomBounds:i})}},_onKeyDown:function(t){27===t.keyCode&&this._finish()}});$i.addInitHook("addHandler","boxZoom",wn),$i.mergeOptions({doubleClickZoom:!0});var Pn=se.extend({addHooks:function(){this._map.on("dblclick",this._onDoubleClick,this)},removeHooks:function(){this._map.off("dblclick",this._onDoubleClick,this)},_onDoubleClick:function(t){var i=this._map,e=i.getZoom(),n=i.options.zoomDelta,o=t.originalEvent.shiftKey?e-n:e+n;"center"===i.options.doubleClickZoom?i.setZoom(o):i.setZoomAround(t.containerPoint,o)}});$i.addInitHook("addHandler","doubleClickZoom",Pn),$i.mergeOptions({dragging:!0,inertia:!rt,inertiaDeceleration:3400,inertiaMaxSpeed:1/0,easeLinearity:.2,worldCopyJump:!1,maxBoundsViscosity:0});var Ln=se.extend({addHooks:function(){if(!this._draggable){var t=this._map;this._draggable=new ce(t._mapPane,t._container),this._draggable.on({dragstart:this._onDragStart,drag:this._onDrag,dragend:this._onDragEnd},this),this._draggable.on("predrag",this._onPreDragLimit,this),t.options.worldCopyJump&&(this._draggable.on("predrag",this._onPreDragWrap,this),t.on("zoomend",this._onZoomEnd,this),t.whenReady(this._onZoomEnd,this))}mi(this._map._container,"leaflet-grab leaflet-touch-drag"),this._draggable.enable(),this._positions=[],this._times=[]},removeHooks:function(){fi(this._map._container,"leaflet-grab"),fi(this._map._container,"leaflet-touch-drag"),this._draggable.disable()},moved:function(){return this._draggable&&this._draggable._moved},moving:function(){return this._draggable&&this._draggable._moving},_onDragStart:function(){var t=this._map;if(t._stop(),this._map.options.maxBounds&&this._map.options.maxBoundsViscosity){var i=D(this._map.options.maxBounds);this._offsetLimit=R(this._map.latLngToContainerPoint(i.getNorthWest()).multiplyBy(-1),this._map.latLngToContainerPoint(i.getSouthEast()).multiplyBy(-1).add(this._map.getSize())),this._viscosity=Math.min(1,Math.max(0,this._map.options.maxBoundsViscosity))}else this._offsetLimit=null;t.fire("movestart").fire("dragstart"),t.options.inertia&&(this._positions=[],this._times=[])},_onDrag:function(t){if(this._map.options.inertia){var i=this._lastTime=+new Date,e=this._lastPos=this._draggable._absPos||this._draggable._newPos;this._positions.push(e),this._times.push(i),this._prunePositions(i)}this._map.fire("move",t).fire("drag",t)},_prunePositions:function(t){for(;1<this._positions.length&&50<t-this._times[0];)this._positions.shift(),this._times.shift()},_onZoomEnd:function(){var t=this._map.getSize().divideBy(2),i=this._map.latLngToLayerPoint([0,0]);this._initialWorldOffset=i.subtract(t).x,this._worldWidth=this._map.getPixelWorldBounds().getSize().x},_viscousLimit:function(t,i){return t-(t-i)*this._viscosity},_onPreDragLimit:function(){if(this._viscosity&&this._offsetLimit){var t=this._draggable._newPos.subtract(this._draggable._startPos),i=this._offsetLimit;t.x<i.min.x&&(t.x=this._viscousLimit(t.x,i.min.x)),t.y<i.min.y&&(t.y=this._viscousLimit(t.y,i.min.y)),t.x>i.max.x&&(t.x=this._viscousLimit(t.x,i.max.x)),t.y>i.max.y&&(t.y=this._viscousLimit(t.y,i.max.y)),this._draggable._newPos=this._draggable._startPos.add(t)}},_onPreDragWrap:function(){var t=this._worldWidth,i=Math.round(t/2),e=this._initialWorldOffset,n=this._draggable._newPos.x,o=(n-i+e)%t+i-e,s=(n+i+e)%t-i-e,r=Math.abs(o+e)<Math.abs(s+e)?o:s;this._draggable._absPos=this._draggable._newPos.clone(),this._draggable._newPos.x=r},_onDragEnd:function(t){var i=this._map,e=i.options,n=!e.inertia||this._times.length<2;if(i.fire("dragend",t),n)i.fire("moveend");else{this._prunePositions(+new Date);var o=this._lastPos.subtract(this._positions[0]),s=(this._lastTime-this._times[0])/1e3,r=e.easeLinearity,a=o.multiplyBy(r/s),h=a.distanceTo([0,0]),u=Math.min(e.inertiaMaxSpeed,h),l=a.multiplyBy(u/h),c=u/(e.inertiaDeceleration*r),_=l.multiplyBy(-c/2).round();_.x||_.y?(_=i._limitOffset(_,i.options.maxBounds),M(function(){i.panBy(_,{duration:c,easeLinearity:r,noMoveStart:!0,animate:!0})})):i.fire("moveend")}}});$i.addInitHook("addHandler","dragging",Ln),$i.mergeOptions({keyboard:!0,keyboardPanDelta:80});var bn=se.extend({keyCodes:{left:[37],right:[39],down:[40],up:[38],zoomIn:[187,107,61,171],zoomOut:[189,109,54,173]},initialize:function(t){this._map=t,this._setPanDelta(t.options.keyboardPanDelta),this._setZoomDelta(t.options.zoomDelta)},addHooks:function(){var t=this._map._container;t.tabIndex<=0&&(t.tabIndex="0"),ki(t,{focus:this._onFocus,blur:this._onBlur,mousedown:this._onMouseDown},this),this._map.on({focus:this._addHooks,blur:this._removeHooks},this)},removeHooks:function(){this._removeHooks(),Ai(this._map._container,{focus:this._onFocus,blur:this._onBlur,mousedown:this._onMouseDown},this),this._map.off({focus:this._addHooks,blur:this._removeHooks},this)},_onMouseDown:function(){if(!this._focused){var t=document.body,i=document.documentElement,e=t.scrollTop||i.scrollTop,n=t.scrollLeft||i.scrollLeft;this._map._container.focus(),window.scrollTo(n,e)}},_onFocus:function(){this._focused=!0,this._map.fire("focus")},_onBlur:function(){this._focused=!1,this._map.fire("blur")},_setPanDelta:function(t){var i,e,n=this._panKeys={},o=this.keyCodes;for(i=0,e=o.left.length;i<e;i++)n[o.left[i]]=[-1*t,0];for(i=0,e=o.right.length;i<e;i++)n[o.right[i]]=[t,0];for(i=0,e=o.down.length;i<e;i++)n[o.down[i]]=[0,t];for(i=0,e=o.up.length;i<e;i++)n[o.up[i]]=[0,-1*t]},_setZoomDelta:function(t){var i,e,n=this._zoomKeys={},o=this.keyCodes;for(i=0,e=o.zoomIn.length;i<e;i++)n[o.zoomIn[i]]=t;for(i=0,e=o.zoomOut.length;i<e;i++)n[o.zoomOut[i]]=-t},_addHooks:function(){ki(document,"keydown",this._onKeyDown,this)},_removeHooks:function(){Ai(document,"keydown",this._onKeyDown,this)},_onKeyDown:function(t){if(!(t.altKey||t.ctrlKey||t.metaKey)){var i,e=t.keyCode,n=this._map;if(e in this._panKeys)n._panAnim&&n._panAnim._inProgress||(i=this._panKeys[e],t.shiftKey&&(i=I(i).multiplyBy(3)),n.panBy(i),n.options.maxBounds&&n.panInsideBounds(n.options.maxBounds));else if(e in this._zoomKeys)n.setZoom(n.getZoom()+(t.shiftKey?3:1)*this._zoomKeys[e]);else{if(27!==e||!n._popup||!n._popup.options.closeOnEscapeKey)return;n.closePopup()}Wi(t)}}});$i.addInitHook("addHandler","keyboard",bn),$i.mergeOptions({scrollWheelZoom:!0,wheelDebounceTime:40,wheelPxPerZoomLevel:60});var Tn=se.extend({addHooks:function(){ki(this._map._container,"mousewheel",this._onWheelScroll,this),this._delta=0},removeHooks:function(){Ai(this._map._container,"mousewheel",this._onWheelScroll,this)},_onWheelScroll:function(t){var i=Ui(t),e=this._map.options.wheelDebounceTime;this._delta+=i,this._lastMousePos=this._map.mouseEventToContainerPoint(t),this._startTime||(this._startTime=+new Date);var n=Math.max(e-(+new Date-this._startTime),0);clearTimeout(this._timer),this._timer=setTimeout(a(this._performZoom,this),n),Wi(t)},_performZoom:function(){var t=this._map,i=t.getZoom(),e=this._map.options.zoomSnap||0;t._stop();var n=this._delta/(4*this._map.options.wheelPxPerZoomLevel),o=4*Math.log(2/(1+Math.exp(-Math.abs(n))))/Math.LN2,s=e?Math.ceil(o/e)*e:o,r=t._limitZoom(i+(0<this._delta?s:-s))-i;this._delta=0,this._startTime=null,r&&("center"===t.options.scrollWheelZoom?t.setZoom(i+r):t.setZoomAround(this._lastMousePos,i+r))}});$i.addInitHook("addHandler","scrollWheelZoom",Tn),$i.mergeOptions({tap:!0,tapTolerance:15});var zn=se.extend({addHooks:function(){ki(this._map._container,"touchstart",this._onDown,this)},removeHooks:function(){Ai(this._map._container,"touchstart",this._onDown,this)},_onDown:function(t){if(t.touches){if(ji(t),this._fireClick=!0,1<t.touches.length)return this._fireClick=!1,void clearTimeout(this._holdTimeout);var i=t.touches[0],e=i.target;this._startPos=this._newPos=new B(i.clientX,i.clientY),e.tagName&&"a"===e.tagName.toLowerCase()&&mi(e,"leaflet-active"),this._holdTimeout=setTimeout(a(function(){this._isTapValid()&&(this._fireClick=!1,this._onUp(),this._simulateEvent("contextmenu",i))},this),1e3),this._simulateEvent("mousedown",i),ki(document,{touchmove:this._onMove,touchend:this._onUp},this)}},_onUp:function(t){if(clearTimeout(this._holdTimeout),Ai(document,{touchmove:this._onMove,touchend:this._onUp},this),this._fireClick&&t&&t.changedTouches){var i=t.changedTouches[0],e=i.target;e&&e.tagName&&"a"===e.tagName.toLowerCase()&&fi(e,"leaflet-active"),this._simulateEvent("mouseup",i),this._isTapValid()&&this._simulateEvent("click",i)}},_isTapValid:function(){return this._newPos.distanceTo(this._startPos)<=this._map.options.tapTolerance},_onMove:function(t){var i=t.touches[0];this._newPos=new B(i.clientX,i.clientY),this._simulateEvent("mousemove",i)},_simulateEvent:function(t,i){var e=document.createEvent("MouseEvents");e._simulated=!0,i.target._simulatedClick=!0,e.initMouseEvent(t,!0,!0,window,1,i.screenX,i.screenY,i.clientX,i.clientY,!1,!1,!1,!1,0,null),i.target.dispatchEvent(e)}});Tt&&!bt&&$i.addInitHook("addHandler","tap",zn),$i.mergeOptions({touchZoom:Tt&&!rt,bounceAtZoomLimits:!0});var Mn=se.extend({addHooks:function(){mi(this._map._container,"leaflet-touch-zoom"),ki(this._map._container,"touchstart",this._onTouchStart,this)},removeHooks:function(){fi(this._map._container,"leaflet-touch-zoom"),Ai(this._map._container,"touchstart",this._onTouchStart,this)},_onTouchStart:function(t){var i=this._map;if(t.touches&&2===t.touches.length&&!i._animatingZoom&&!this._zooming){var e=i.mouseEventToContainerPoint(t.touches[0]),n=i.mouseEventToContainerPoint(t.touches[1]);this._centerPoint=i.getSize()._divideBy(2),this._startLatLng=i.containerPointToLatLng(this._centerPoint),"center"!==i.options.touchZoom&&(this._pinchStartLatLng=i.containerPointToLatLng(e.add(n)._divideBy(2))),this._startDist=e.distanceTo(n),this._startZoom=i.getZoom(),this._moved=!1,this._zooming=!0,i._stop(),ki(document,"touchmove",this._onTouchMove,this),ki(document,"touchend",this._onTouchEnd,this),ji(t)}},_onTouchMove:function(t){if(t.touches&&2===t.touches.length&&this._zooming){var i=this._map,e=i.mouseEventToContainerPoint(t.touches[0]),n=i.mouseEventToContainerPoint(t.touches[1]),o=e.distanceTo(n)/this._startDist;if(this._zoom=i.getScaleZoom(o,this._startZoom),!i.options.bounceAtZoomLimits&&(this._zoom<i.getMinZoom()&&o<1||this._zoom>i.getMaxZoom()&&1<o)&&(this._zoom=i._limitZoom(this._zoom)),"center"===i.options.touchZoom){if(this._center=this._startLatLng,1==o)return}else{var s=e._add(n)._divideBy(2)._subtract(this._centerPoint);if(1==o&&0===s.x&&0===s.y)return;this._center=i.unproject(i.project(this._pinchStartLatLng,this._zoom).subtract(s),this._zoom)}this._moved||(i._moveStart(!0,!1),this._moved=!0),C(this._animRequest);var r=a(i._move,i,this._center,this._zoom,{pinch:!0,round:!1});this._animRequest=M(r,this,!0),ji(t)}},_onTouchEnd:function(){this._moved&&this._zooming?(this._zooming=!1,C(this._animRequest),Ai(document,"touchmove",this._onTouchMove),Ai(document,"touchend",this._onTouchEnd),this._map.options.zoomAnimation?this._map._animateZoom(this._center,this._map._limitZoom(this._zoom),!0,this._map.options.zoomSnap):this._map._resetView(this._center,this._map._limitZoom(this._zoom))):this._zooming=!1}});$i.addInitHook("addHandler","touchZoom",Mn),$i.BoxZoom=wn,$i.DoubleClickZoom=Pn,$i.Drag=Ln,$i.Keyboard=bn,$i.ScrollWheelZoom=Tn,$i.Tap=zn,$i.TouchZoom=Mn,Object.freeze=i,t.version="1.6.0",t.Control=te,t.control=Qi,t.Browser=At,t.Evented=k,t.Mixin=ae,t.Util=S,t.Class=E,t.Handler=se,t.extend=h,t.bind=a,t.stamp=u,t.setOptions=p,t.DomEvent=Xi,t.DomUtil=Zi,t.PosAnimation=Ji,t.Draggable=ce,t.LineUtil=xe,t.PolyUtil=Le,t.Point=B,t.point=I,t.Bounds=O,t.bounds=R,t.Transformation=G,t.transformation=K,t.Projection=ze,t.LatLng=j,t.latLng=W,t.LatLngBounds=N,t.latLngBounds=D,t.CRS=F,t.GeoJSON=He,t.geoJSON=$e,t.geoJson=Qe,t.Layer=Ee,t.LayerGroup=Ze,t.layerGroup=function(t,i){return new Ze(t,i)},t.FeatureGroup=ke,t.featureGroup=function(t){return new ke(t)},t.ImageOverlay=tn,t.imageOverlay=function(t,i,e){return new tn(t,i,e)},t.VideoOverlay=en,t.videoOverlay=function(t,i,e){return new en(t,i,e)},t.SVGOverlay=nn,t.svgOverlay=function(t,i,e){return new nn(t,i,e)},t.DivOverlay=on,t.Popup=sn,t.popup=function(t,i){return new sn(t,i)},t.Tooltip=rn,t.tooltip=function(t,i){return new rn(t,i)},t.Icon=Be,t.icon=function(t){return new Be(t)},t.DivIcon=an,t.divIcon=function(t){return new an(t)},t.Marker=Oe,t.marker=function(t,i){return new Oe(t,i)},t.TileLayer=un,t.tileLayer=ln,t.GridLayer=hn,t.gridLayer=function(t){return new hn(t)},t.SVG=vn,t.svg=yn,t.Renderer=_n,t.Canvas=dn,t.canvas=pn,t.Path=Re,t.CircleMarker=Ne,t.circleMarker=function(t,i){return new Ne(t,i)},t.Circle=De,t.circle=function(t,i,e){return new De(t,i,e)},t.Polyline=je,t.polyline=function(t,i){return new je(t,i)},t.Polygon=We,t.polygon=function(t,i){return new We(t,i)},t.Rectangle=xn,t.rectangle=function(t,i){return new xn(t,i)},t.Map=$i,t.map=function(t,i){return new $i(t,i)};var Cn=window.L;t.noConflict=function(){return window.L=Cn,this},window.L=t});
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
!function(n,i){"function"==typeof define&&define.amd?define(["leaflet"],function(i){n(i)}):"object"==typeof exports?module.exports=function(i){return void 0===i&&(i=require("leaflet")),n(i),i}:"undefined"!=typeof i&&i.L&&n(i.L)}(function(n){var i={spin:function(n,i){n?(this._spinner||(this._spinner=new Spinner(i).spin(this._container),this._spinning=0),this._spinning++):(this._spinning--,this._spinning<=0&&this._spinner&&(this._spinner.stop(),this._spinner=null))}},t=function(){this.on("layeradd",function(n){n.layer.loading&&this.spin(!0),"function"==typeof n.layer.on&&(n.layer.on("data:loading",function(){this.spin(!0)},this),n.layer.on("data:loaded",function(){this.spin(!1)},this))},this),this.on("layerremove",function(n){n.layer.loading&&this.spin(!1),"function"==typeof n.layer.on&&(n.layer.off("data:loaded"),n.layer.off("data:loading"))},this)}
n.Map.include(i),n.Map.addInitHook(t)},window)

!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?t(exports):"function"==typeof define&&define.amd?define(["exports"],t):t((e.Leaflet=e.Leaflet||{},e.Leaflet.markercluster=e.Leaflet.markercluster||{}))}(this,function(e){"use strict";var t=L.MarkerClusterGroup=L.FeatureGroup.extend({options:{maxClusterRadius:80,iconCreateFunction:null,clusterPane:L.Marker.prototype.options.pane,spiderfyOnMaxZoom:!0,showCoverageOnHover:!0,zoomToBoundsOnClick:!0,singleMarkerMode:!1,disableClusteringAtZoom:null,removeOutsideVisibleBounds:!0,animate:!0,animateAddingMarkers:!1,spiderfyDistanceMultiplier:1,spiderLegPolylineOptions:{weight:1.5,color:"#222",opacity:.5},chunkedLoading:!1,chunkInterval:200,chunkDelay:50,chunkProgress:null,polygonOptions:{}},initialize:function(e){L.Util.setOptions(this,e),this.options.iconCreateFunction||(this.options.iconCreateFunction=this._defaultIconCreateFunction),this._featureGroup=L.featureGroup(),this._featureGroup.addEventParent(this),this._nonPointGroup=L.featureGroup(),this._nonPointGroup.addEventParent(this),this._inZoomAnimation=0,this._needsClustering=[],this._needsRemoving=[],this._currentShownBounds=null,this._queue=[],this._childMarkerEventHandlers={dragstart:this._childMarkerDragStart,move:this._childMarkerMoved,dragend:this._childMarkerDragEnd};var t=L.DomUtil.TRANSITION&&this.options.animate;L.extend(this,t?this._withAnimation:this._noAnimation),this._markerCluster=t?L.MarkerCluster:L.MarkerClusterNonAnimated},addLayer:function(e){if(e instanceof L.LayerGroup)return this.addLayers([e]);if(!e.getLatLng)return this._nonPointGroup.addLayer(e),this.fire("layeradd",{layer:e}),this;if(!this._map)return this._needsClustering.push(e),this.fire("layeradd",{layer:e}),this;if(this.hasLayer(e))return this;this._unspiderfy&&this._unspiderfy(),this._addLayer(e,this._maxZoom),this.fire("layeradd",{layer:e}),this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons();var t=e,i=this._zoom;if(e.__parent)for(;t.__parent._zoom>=i;)t=t.__parent;return this._currentShownBounds.contains(t.getLatLng())&&(this.options.animateAddingMarkers?this._animationAddLayer(e,t):this._animationAddLayerNonAnimated(e,t)),this},removeLayer:function(e){return e instanceof L.LayerGroup?this.removeLayers([e]):e.getLatLng?this._map?e.__parent?(this._unspiderfy&&(this._unspiderfy(),this._unspiderfyLayer(e)),this._removeLayer(e,!0),this.fire("layerremove",{layer:e}),this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),e.off(this._childMarkerEventHandlers,this),this._featureGroup.hasLayer(e)&&(this._featureGroup.removeLayer(e),e.clusterShow&&e.clusterShow()),this):this:(!this._arraySplice(this._needsClustering,e)&&this.hasLayer(e)&&this._needsRemoving.push({layer:e,latlng:e._latlng}),this.fire("layerremove",{layer:e}),this):(this._nonPointGroup.removeLayer(e),this.fire("layerremove",{layer:e}),this)},addLayers:function(e,t){if(!L.Util.isArray(e))return this.addLayer(e);var i,n=this._featureGroup,r=this._nonPointGroup,s=this.options.chunkedLoading,o=this.options.chunkInterval,a=this.options.chunkProgress,h=e.length,l=0,u=!0;if(this._map){var _=(new Date).getTime(),d=L.bind(function(){for(var c=(new Date).getTime();h>l;l++){if(s&&0===l%200){var p=(new Date).getTime()-c;if(p>o)break}if(i=e[l],i instanceof L.LayerGroup)u&&(e=e.slice(),u=!1),this._extractNonGroupLayers(i,e),h=e.length;else if(i.getLatLng){if(!this.hasLayer(i)&&(this._addLayer(i,this._maxZoom),t||this.fire("layeradd",{layer:i}),i.__parent&&2===i.__parent.getChildCount())){var f=i.__parent.getAllChildMarkers(),m=f[0]===i?f[1]:f[0];n.removeLayer(m)}}else r.addLayer(i),t||this.fire("layeradd",{layer:i})}a&&a(l,h,(new Date).getTime()-_),l===h?(this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),this._topClusterLevel._recursivelyAddChildrenToMap(null,this._zoom,this._currentShownBounds)):setTimeout(d,this.options.chunkDelay)},this);d()}else for(var c=this._needsClustering;h>l;l++)i=e[l],i instanceof L.LayerGroup?(u&&(e=e.slice(),u=!1),this._extractNonGroupLayers(i,e),h=e.length):i.getLatLng?this.hasLayer(i)||c.push(i):r.addLayer(i);return this},removeLayers:function(e){var t,i,n=e.length,r=this._featureGroup,s=this._nonPointGroup,o=!0;if(!this._map){for(t=0;n>t;t++)i=e[t],i instanceof L.LayerGroup?(o&&(e=e.slice(),o=!1),this._extractNonGroupLayers(i,e),n=e.length):(this._arraySplice(this._needsClustering,i),s.removeLayer(i),this.hasLayer(i)&&this._needsRemoving.push({layer:i,latlng:i._latlng}),this.fire("layerremove",{layer:i}));return this}if(this._unspiderfy){this._unspiderfy();var a=e.slice(),h=n;for(t=0;h>t;t++)i=a[t],i instanceof L.LayerGroup?(this._extractNonGroupLayers(i,a),h=a.length):this._unspiderfyLayer(i)}for(t=0;n>t;t++)i=e[t],i instanceof L.LayerGroup?(o&&(e=e.slice(),o=!1),this._extractNonGroupLayers(i,e),n=e.length):i.__parent?(this._removeLayer(i,!0,!0),this.fire("layerremove",{layer:i}),r.hasLayer(i)&&(r.removeLayer(i),i.clusterShow&&i.clusterShow())):(s.removeLayer(i),this.fire("layerremove",{layer:i}));return this._topClusterLevel._recalculateBounds(),this._refreshClustersIcons(),this._topClusterLevel._recursivelyAddChildrenToMap(null,this._zoom,this._currentShownBounds),this},clearLayers:function(){return this._map||(this._needsClustering=[],this._needsRemoving=[],delete this._gridClusters,delete this._gridUnclustered),this._noanimationUnspiderfy&&this._noanimationUnspiderfy(),this._featureGroup.clearLayers(),this._nonPointGroup.clearLayers(),this.eachLayer(function(e){e.off(this._childMarkerEventHandlers,this),delete e.__parent},this),this._map&&this._generateInitialClusters(),this},getBounds:function(){var e=new L.LatLngBounds;this._topClusterLevel&&e.extend(this._topClusterLevel._bounds);for(var t=this._needsClustering.length-1;t>=0;t--)e.extend(this._needsClustering[t].getLatLng());return e.extend(this._nonPointGroup.getBounds()),e},eachLayer:function(e,t){var i,n,r,s=this._needsClustering.slice(),o=this._needsRemoving;for(this._topClusterLevel&&this._topClusterLevel.getAllChildMarkers(s),n=s.length-1;n>=0;n--){for(i=!0,r=o.length-1;r>=0;r--)if(o[r].layer===s[n]){i=!1;break}i&&e.call(t,s[n])}this._nonPointGroup.eachLayer(e,t)},getLayers:function(){var e=[];return this.eachLayer(function(t){e.push(t)}),e},getLayer:function(e){var t=null;return e=parseInt(e,10),this.eachLayer(function(i){L.stamp(i)===e&&(t=i)}),t},hasLayer:function(e){if(!e)return!1;var t,i=this._needsClustering;for(t=i.length-1;t>=0;t--)if(i[t]===e)return!0;for(i=this._needsRemoving,t=i.length-1;t>=0;t--)if(i[t].layer===e)return!1;return!(!e.__parent||e.__parent._group!==this)||this._nonPointGroup.hasLayer(e)},zoomToShowLayer:function(e,t){"function"!=typeof t&&(t=function(){});var i=function(){!e._icon&&!e.__parent._icon||this._inZoomAnimation||(this._map.off("moveend",i,this),this.off("animationend",i,this),e._icon?t():e.__parent._icon&&(this.once("spiderfied",t,this),e.__parent.spiderfy()))};e._icon&&this._map.getBounds().contains(e.getLatLng())?t():e.__parent._zoom<Math.round(this._map._zoom)?(this._map.on("moveend",i,this),this._map.panTo(e.getLatLng())):(this._map.on("moveend",i,this),this.on("animationend",i,this),e.__parent.zoomToBounds())},onAdd:function(e){this._map=e;var t,i,n;if(!isFinite(this._map.getMaxZoom()))throw"Map has no maxZoom specified";for(this._featureGroup.addTo(e),this._nonPointGroup.addTo(e),this._gridClusters||this._generateInitialClusters(),this._maxLat=e.options.crs.projection.MAX_LATITUDE,t=0,i=this._needsRemoving.length;i>t;t++)n=this._needsRemoving[t],n.newlatlng=n.layer._latlng,n.layer._latlng=n.latlng;for(t=0,i=this._needsRemoving.length;i>t;t++)n=this._needsRemoving[t],this._removeLayer(n.layer,!0),n.layer._latlng=n.newlatlng;this._needsRemoving=[],this._zoom=Math.round(this._map._zoom),this._currentShownBounds=this._getExpandedVisibleBounds(),this._map.on("zoomend",this._zoomEnd,this),this._map.on("moveend",this._moveEnd,this),this._spiderfierOnAdd&&this._spiderfierOnAdd(),this._bindEvents(),i=this._needsClustering,this._needsClustering=[],this.addLayers(i,!0)},onRemove:function(e){e.off("zoomend",this._zoomEnd,this),e.off("moveend",this._moveEnd,this),this._unbindEvents(),this._map._mapPane.className=this._map._mapPane.className.replace(" leaflet-cluster-anim",""),this._spiderfierOnRemove&&this._spiderfierOnRemove(),delete this._maxLat,this._hideCoverage(),this._featureGroup.remove(),this._nonPointGroup.remove(),this._featureGroup.clearLayers(),this._map=null},getVisibleParent:function(e){for(var t=e;t&&!t._icon;)t=t.__parent;return t||null},_arraySplice:function(e,t){for(var i=e.length-1;i>=0;i--)if(e[i]===t)return e.splice(i,1),!0},_removeFromGridUnclustered:function(e,t){for(var i=this._map,n=this._gridUnclustered,r=Math.floor(this._map.getMinZoom());t>=r&&n[t].removeObject(e,i.project(e.getLatLng(),t));t--);},_childMarkerDragStart:function(e){e.target.__dragStart=e.target._latlng},_childMarkerMoved:function(e){if(!this._ignoreMove&&!e.target.__dragStart){var t=e.target._popup&&e.target._popup.isOpen();this._moveChild(e.target,e.oldLatLng,e.latlng),t&&e.target.openPopup()}},_moveChild:function(e,t,i){e._latlng=t,this.removeLayer(e),e._latlng=i,this.addLayer(e)},_childMarkerDragEnd:function(e){var t=e.target.__dragStart;delete e.target.__dragStart,t&&this._moveChild(e.target,t,e.target._latlng)},_removeLayer:function(e,t,i){var n=this._gridClusters,r=this._gridUnclustered,s=this._featureGroup,o=this._map,a=Math.floor(this._map.getMinZoom());t&&this._removeFromGridUnclustered(e,this._maxZoom);var h,l=e.__parent,u=l._markers;for(this._arraySplice(u,e);l&&(l._childCount--,l._boundsNeedUpdate=!0,!(l._zoom<a));)t&&l._childCount<=1?(h=l._markers[0]===e?l._markers[1]:l._markers[0],n[l._zoom].removeObject(l,o.project(l._cLatLng,l._zoom)),r[l._zoom].addObject(h,o.project(h.getLatLng(),l._zoom)),this._arraySplice(l.__parent._childClusters,l),l.__parent._markers.push(h),h.__parent=l.__parent,l._icon&&(s.removeLayer(l),i||s.addLayer(h))):l._iconNeedsUpdate=!0,l=l.__parent;delete e.__parent},_isOrIsParent:function(e,t){for(;t;){if(e===t)return!0;t=t.parentNode}return!1},fire:function(e,t,i){if(t&&t.layer instanceof L.MarkerCluster){if(t.originalEvent&&this._isOrIsParent(t.layer._icon,t.originalEvent.relatedTarget))return;e="cluster"+e}L.FeatureGroup.prototype.fire.call(this,e,t,i)},listens:function(e,t){return L.FeatureGroup.prototype.listens.call(this,e,t)||L.FeatureGroup.prototype.listens.call(this,"cluster"+e,t)},_defaultIconCreateFunction:function(e){var t=e.getChildCount(),i=" marker-cluster-";return i+=10>t?"small":100>t?"medium":"large",new L.DivIcon({html:"<div><span>"+t+"</span></div>",className:"marker-cluster"+i,iconSize:new L.Point(40,40)})},_bindEvents:function(){var e=this._map,t=this.options.spiderfyOnMaxZoom,i=this.options.showCoverageOnHover,n=this.options.zoomToBoundsOnClick;(t||n)&&this.on("clusterclick",this._zoomOrSpiderfy,this),i&&(this.on("clustermouseover",this._showCoverage,this),this.on("clustermouseout",this._hideCoverage,this),e.on("zoomend",this._hideCoverage,this))},_zoomOrSpiderfy:function(e){for(var t=e.layer,i=t;1===i._childClusters.length;)i=i._childClusters[0];i._zoom===this._maxZoom&&i._childCount===t._childCount&&this.options.spiderfyOnMaxZoom?t.spiderfy():this.options.zoomToBoundsOnClick&&t.zoomToBounds(),e.originalEvent&&13===e.originalEvent.keyCode&&this._map._container.focus()},_showCoverage:function(e){var t=this._map;this._inZoomAnimation||(this._shownPolygon&&t.removeLayer(this._shownPolygon),e.layer.getChildCount()>2&&e.layer!==this._spiderfied&&(this._shownPolygon=new L.Polygon(e.layer.getConvexHull(),this.options.polygonOptions),t.addLayer(this._shownPolygon)))},_hideCoverage:function(){this._shownPolygon&&(this._map.removeLayer(this._shownPolygon),this._shownPolygon=null)},_unbindEvents:function(){var e=this.options.spiderfyOnMaxZoom,t=this.options.showCoverageOnHover,i=this.options.zoomToBoundsOnClick,n=this._map;(e||i)&&this.off("clusterclick",this._zoomOrSpiderfy,this),t&&(this.off("clustermouseover",this._showCoverage,this),this.off("clustermouseout",this._hideCoverage,this),n.off("zoomend",this._hideCoverage,this))},_zoomEnd:function(){this._map&&(this._mergeSplitClusters(),this._zoom=Math.round(this._map._zoom),this._currentShownBounds=this._getExpandedVisibleBounds())},_moveEnd:function(){if(!this._inZoomAnimation){var e=this._getExpandedVisibleBounds();this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),this._zoom,e),this._topClusterLevel._recursivelyAddChildrenToMap(null,Math.round(this._map._zoom),e),this._currentShownBounds=e}},_generateInitialClusters:function(){var e=Math.ceil(this._map.getMaxZoom()),t=Math.floor(this._map.getMinZoom()),i=this.options.maxClusterRadius,n=i;"function"!=typeof i&&(n=function(){return i}),null!==this.options.disableClusteringAtZoom&&(e=this.options.disableClusteringAtZoom-1),this._maxZoom=e,this._gridClusters={},this._gridUnclustered={};for(var r=e;r>=t;r--)this._gridClusters[r]=new L.DistanceGrid(n(r)),this._gridUnclustered[r]=new L.DistanceGrid(n(r));this._topClusterLevel=new this._markerCluster(this,t-1)},_addLayer:function(e,t){var i,n,r=this._gridClusters,s=this._gridUnclustered,o=Math.floor(this._map.getMinZoom());for(this.options.singleMarkerMode&&this._overrideMarkerIcon(e),e.on(this._childMarkerEventHandlers,this);t>=o;t--){i=this._map.project(e.getLatLng(),t);var a=r[t].getNearObject(i);if(a)return a._addChild(e),e.__parent=a,void 0;if(a=s[t].getNearObject(i)){var h=a.__parent;h&&this._removeLayer(a,!1);var l=new this._markerCluster(this,t,a,e);r[t].addObject(l,this._map.project(l._cLatLng,t)),a.__parent=l,e.__parent=l;var u=l;for(n=t-1;n>h._zoom;n--)u=new this._markerCluster(this,n,u),r[n].addObject(u,this._map.project(a.getLatLng(),n));return h._addChild(u),this._removeFromGridUnclustered(a,t),void 0}s[t].addObject(e,i)}this._topClusterLevel._addChild(e),e.__parent=this._topClusterLevel},_refreshClustersIcons:function(){this._featureGroup.eachLayer(function(e){e instanceof L.MarkerCluster&&e._iconNeedsUpdate&&e._updateIcon()})},_enqueue:function(e){this._queue.push(e),this._queueTimeout||(this._queueTimeout=setTimeout(L.bind(this._processQueue,this),300))},_processQueue:function(){for(var e=0;e<this._queue.length;e++)this._queue[e].call(this);this._queue.length=0,clearTimeout(this._queueTimeout),this._queueTimeout=null},_mergeSplitClusters:function(){var e=Math.round(this._map._zoom);this._processQueue(),this._zoom<e&&this._currentShownBounds.intersects(this._getExpandedVisibleBounds())?(this._animationStart(),this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),this._zoom,this._getExpandedVisibleBounds()),this._animationZoomIn(this._zoom,e)):this._zoom>e?(this._animationStart(),this._animationZoomOut(this._zoom,e)):this._moveEnd()},_getExpandedVisibleBounds:function(){return this.options.removeOutsideVisibleBounds?L.Browser.mobile?this._checkBoundsMaxLat(this._map.getBounds()):this._checkBoundsMaxLat(this._map.getBounds().pad(1)):this._mapBoundsInfinite},_checkBoundsMaxLat:function(e){var t=this._maxLat;return void 0!==t&&(e.getNorth()>=t&&(e._northEast.lat=1/0),e.getSouth()<=-t&&(e._southWest.lat=-1/0)),e},_animationAddLayerNonAnimated:function(e,t){if(t===e)this._featureGroup.addLayer(e);else if(2===t._childCount){t._addToMap();var i=t.getAllChildMarkers();this._featureGroup.removeLayer(i[0]),this._featureGroup.removeLayer(i[1])}else t._updateIcon()},_extractNonGroupLayers:function(e,t){var i,n=e.getLayers(),r=0;for(t=t||[];r<n.length;r++)i=n[r],i instanceof L.LayerGroup?this._extractNonGroupLayers(i,t):t.push(i);return t},_overrideMarkerIcon:function(e){var t=e.options.icon=this.options.iconCreateFunction({getChildCount:function(){return 1},getAllChildMarkers:function(){return[e]}});return t}});L.MarkerClusterGroup.include({_mapBoundsInfinite:new L.LatLngBounds(new L.LatLng(-1/0,-1/0),new L.LatLng(1/0,1/0))}),L.MarkerClusterGroup.include({_noAnimation:{_animationStart:function(){},_animationZoomIn:function(e,t){this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this.fire("animationend")},_animationZoomOut:function(e,t){this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this.fire("animationend")},_animationAddLayer:function(e,t){this._animationAddLayerNonAnimated(e,t)}},_withAnimation:{_animationStart:function(){this._map._mapPane.className+=" leaflet-cluster-anim",this._inZoomAnimation++},_animationZoomIn:function(e,t){var i,n=this._getExpandedVisibleBounds(),r=this._featureGroup,s=Math.floor(this._map.getMinZoom());this._ignoreMove=!0,this._topClusterLevel._recursively(n,e,s,function(s){var o,a=s._latlng,h=s._markers;for(n.contains(a)||(a=null),s._isSingleParent()&&e+1===t?(r.removeLayer(s),s._recursivelyAddChildrenToMap(null,t,n)):(s.clusterHide(),s._recursivelyAddChildrenToMap(a,t,n)),i=h.length-1;i>=0;i--)o=h[i],n.contains(o._latlng)||r.removeLayer(o)}),this._forceLayout(),this._topClusterLevel._recursivelyBecomeVisible(n,t),r.eachLayer(function(e){e instanceof L.MarkerCluster||!e._icon||e.clusterShow()}),this._topClusterLevel._recursively(n,e,t,function(e){e._recursivelyRestoreChildPositions(t)}),this._ignoreMove=!1,this._enqueue(function(){this._topClusterLevel._recursively(n,e,s,function(e){r.removeLayer(e),e.clusterShow()}),this._animationEnd()})},_animationZoomOut:function(e,t){this._animationZoomOutSingle(this._topClusterLevel,e-1,t),this._topClusterLevel._recursivelyAddChildrenToMap(null,t,this._getExpandedVisibleBounds()),this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds,Math.floor(this._map.getMinZoom()),e,this._getExpandedVisibleBounds())},_animationAddLayer:function(e,t){var i=this,n=this._featureGroup;n.addLayer(e),t!==e&&(t._childCount>2?(t._updateIcon(),this._forceLayout(),this._animationStart(),e._setPos(this._map.latLngToLayerPoint(t.getLatLng())),e.clusterHide(),this._enqueue(function(){n.removeLayer(e),e.clusterShow(),i._animationEnd()})):(this._forceLayout(),i._animationStart(),i._animationZoomOutSingle(t,this._map.getMaxZoom(),this._zoom)))}},_animationZoomOutSingle:function(e,t,i){var n=this._getExpandedVisibleBounds(),r=Math.floor(this._map.getMinZoom());e._recursivelyAnimateChildrenInAndAddSelfToMap(n,r,t+1,i);var s=this;this._forceLayout(),e._recursivelyBecomeVisible(n,i),this._enqueue(function(){if(1===e._childCount){var o=e._markers[0];this._ignoreMove=!0,o.setLatLng(o.getLatLng()),this._ignoreMove=!1,o.clusterShow&&o.clusterShow()}else e._recursively(n,i,r,function(e){e._recursivelyRemoveChildrenFromMap(n,r,t+1)});s._animationEnd()})},_animationEnd:function(){this._map&&(this._map._mapPane.className=this._map._mapPane.className.replace(" leaflet-cluster-anim","")),this._inZoomAnimation--,this.fire("animationend")},_forceLayout:function(){L.Util.falseFn(document.body.offsetWidth)}}),L.markerClusterGroup=function(e){return new L.MarkerClusterGroup(e)};var i=L.MarkerCluster=L.Marker.extend({options:L.Icon.prototype.options,initialize:function(e,t,i,n){L.Marker.prototype.initialize.call(this,i?i._cLatLng||i.getLatLng():new L.LatLng(0,0),{icon:this,pane:e.options.clusterPane}),this._group=e,this._zoom=t,this._markers=[],this._childClusters=[],this._childCount=0,this._iconNeedsUpdate=!0,this._boundsNeedUpdate=!0,this._bounds=new L.LatLngBounds,i&&this._addChild(i),n&&this._addChild(n)},getAllChildMarkers:function(e,t){e=e||[];for(var i=this._childClusters.length-1;i>=0;i--)this._childClusters[i].getAllChildMarkers(e);for(var n=this._markers.length-1;n>=0;n--)t&&this._markers[n].__dragStart||e.push(this._markers[n]);return e},getChildCount:function(){return this._childCount},zoomToBounds:function(e){for(var t,i=this._childClusters.slice(),n=this._group._map,r=n.getBoundsZoom(this._bounds),s=this._zoom+1,o=n.getZoom();i.length>0&&r>s;){s++;var a=[];for(t=0;t<i.length;t++)a=a.concat(i[t]._childClusters);i=a}r>s?this._group._map.setView(this._latlng,s):o>=r?this._group._map.setView(this._latlng,o+1):this._group._map.fitBounds(this._bounds,e)},getBounds:function(){var e=new L.LatLngBounds;return e.extend(this._bounds),e},_updateIcon:function(){this._iconNeedsUpdate=!0,this._icon&&this.setIcon(this)},createIcon:function(){return this._iconNeedsUpdate&&(this._iconObj=this._group.options.iconCreateFunction(this),this._iconNeedsUpdate=!1),this._iconObj.createIcon()},createShadow:function(){return this._iconObj.createShadow()},_addChild:function(e,t){this._iconNeedsUpdate=!0,this._boundsNeedUpdate=!0,this._setClusterCenter(e),e instanceof L.MarkerCluster?(t||(this._childClusters.push(e),e.__parent=this),this._childCount+=e._childCount):(t||this._markers.push(e),this._childCount++),this.__parent&&this.__parent._addChild(e,!0)},_setClusterCenter:function(e){this._cLatLng||(this._cLatLng=e._cLatLng||e._latlng)},_resetBounds:function(){var e=this._bounds;e._southWest&&(e._southWest.lat=1/0,e._southWest.lng=1/0),e._northEast&&(e._northEast.lat=-1/0,e._northEast.lng=-1/0)},_recalculateBounds:function(){var e,t,i,n,r=this._markers,s=this._childClusters,o=0,a=0,h=this._childCount;if(0!==h){for(this._resetBounds(),e=0;e<r.length;e++)i=r[e]._latlng,this._bounds.extend(i),o+=i.lat,a+=i.lng;for(e=0;e<s.length;e++)t=s[e],t._boundsNeedUpdate&&t._recalculateBounds(),this._bounds.extend(t._bounds),i=t._wLatLng,n=t._childCount,o+=i.lat*n,a+=i.lng*n;this._latlng=this._wLatLng=new L.LatLng(o/h,a/h),this._boundsNeedUpdate=!1}},_addToMap:function(e){e&&(this._backupLatlng=this._latlng,this.setLatLng(e)),this._group._featureGroup.addLayer(this)},_recursivelyAnimateChildrenIn:function(e,t,i){this._recursively(e,this._group._map.getMinZoom(),i-1,function(e){var i,n,r=e._markers;for(i=r.length-1;i>=0;i--)n=r[i],n._icon&&(n._setPos(t),n.clusterHide())},function(e){var i,n,r=e._childClusters;for(i=r.length-1;i>=0;i--)n=r[i],n._icon&&(n._setPos(t),n.clusterHide())})},_recursivelyAnimateChildrenInAndAddSelfToMap:function(e,t,i,n){this._recursively(e,n,t,function(r){r._recursivelyAnimateChildrenIn(e,r._group._map.latLngToLayerPoint(r.getLatLng()).round(),i),r._isSingleParent()&&i-1===n?(r.clusterShow(),r._recursivelyRemoveChildrenFromMap(e,t,i)):r.clusterHide(),r._addToMap()})},_recursivelyBecomeVisible:function(e,t){this._recursively(e,this._group._map.getMinZoom(),t,null,function(e){e.clusterShow()})},_recursivelyAddChildrenToMap:function(e,t,i){this._recursively(i,this._group._map.getMinZoom()-1,t,function(n){if(t!==n._zoom)for(var r=n._markers.length-1;r>=0;r--){var s=n._markers[r];i.contains(s._latlng)&&(e&&(s._backupLatlng=s.getLatLng(),s.setLatLng(e),s.clusterHide&&s.clusterHide()),n._group._featureGroup.addLayer(s))}},function(t){t._addToMap(e)})},_recursivelyRestoreChildPositions:function(e){for(var t=this._markers.length-1;t>=0;t--){var i=this._markers[t];i._backupLatlng&&(i.setLatLng(i._backupLatlng),delete i._backupLatlng)}if(e-1===this._zoom)for(var n=this._childClusters.length-1;n>=0;n--)this._childClusters[n]._restorePosition();else for(var r=this._childClusters.length-1;r>=0;r--)this._childClusters[r]._recursivelyRestoreChildPositions(e)},_restorePosition:function(){this._backupLatlng&&(this.setLatLng(this._backupLatlng),delete this._backupLatlng)},_recursivelyRemoveChildrenFromMap:function(e,t,i,n){var r,s;this._recursively(e,t-1,i-1,function(e){for(s=e._markers.length-1;s>=0;s--)r=e._markers[s],n&&n.contains(r._latlng)||(e._group._featureGroup.removeLayer(r),r.clusterShow&&r.clusterShow())},function(e){for(s=e._childClusters.length-1;s>=0;s--)r=e._childClusters[s],n&&n.contains(r._latlng)||(e._group._featureGroup.removeLayer(r),r.clusterShow&&r.clusterShow())})},_recursively:function(e,t,i,n,r){var s,o,a=this._childClusters,h=this._zoom;if(h>=t&&(n&&n(this),r&&h===i&&r(this)),t>h||i>h)for(s=a.length-1;s>=0;s--)o=a[s],o._boundsNeedUpdate&&o._recalculateBounds(),e.intersects(o._bounds)&&o._recursively(e,t,i,n,r)},_isSingleParent:function(){return this._childClusters.length>0&&this._childClusters[0]._childCount===this._childCount}});L.Marker.include({clusterHide:function(){var e=this.options.opacity;return this.setOpacity(0),this.options.opacity=e,this},clusterShow:function(){return this.setOpacity(this.options.opacity)}}),L.DistanceGrid=function(e){this._cellSize=e,this._sqCellSize=e*e,this._grid={},this._objectPoint={}},L.DistanceGrid.prototype={addObject:function(e,t){var i=this._getCoord(t.x),n=this._getCoord(t.y),r=this._grid,s=r[n]=r[n]||{},o=s[i]=s[i]||[],a=L.Util.stamp(e);this._objectPoint[a]=t,o.push(e)},updateObject:function(e,t){this.removeObject(e),this.addObject(e,t)},removeObject:function(e,t){var i,n,r=this._getCoord(t.x),s=this._getCoord(t.y),o=this._grid,a=o[s]=o[s]||{},h=a[r]=a[r]||[];for(delete this._objectPoint[L.Util.stamp(e)],i=0,n=h.length;n>i;i++)if(h[i]===e)return h.splice(i,1),1===n&&delete a[r],!0},eachObject:function(e,t){var i,n,r,s,o,a,h,l=this._grid;for(i in l){o=l[i];for(n in o)for(a=o[n],r=0,s=a.length;s>r;r++)h=e.call(t,a[r]),h&&(r--,s--)}},getNearObject:function(e){var t,i,n,r,s,o,a,h,l=this._getCoord(e.x),u=this._getCoord(e.y),_=this._objectPoint,d=this._sqCellSize,c=null;for(t=u-1;u+1>=t;t++)if(r=this._grid[t])for(i=l-1;l+1>=i;i++)if(s=r[i])for(n=0,o=s.length;o>n;n++)a=s[n],h=this._sqDist(_[L.Util.stamp(a)],e),(d>h||d>=h&&null===c)&&(d=h,c=a);return c},_getCoord:function(e){var t=Math.floor(e/this._cellSize);return isFinite(t)?t:e},_sqDist:function(e,t){var i=t.x-e.x,n=t.y-e.y;return i*i+n*n}},function(){L.QuickHull={getDistant:function(e,t){var i=t[1].lat-t[0].lat,n=t[0].lng-t[1].lng;return n*(e.lat-t[0].lat)+i*(e.lng-t[0].lng)},findMostDistantPointFromBaseLine:function(e,t){var i,n,r,s=0,o=null,a=[];for(i=t.length-1;i>=0;i--)n=t[i],r=this.getDistant(n,e),r>0&&(a.push(n),r>s&&(s=r,o=n));return{maxPoint:o,newPoints:a}},buildConvexHull:function(e,t){var i=[],n=this.findMostDistantPointFromBaseLine(e,t);return n.maxPoint?(i=i.concat(this.buildConvexHull([e[0],n.maxPoint],n.newPoints)),i=i.concat(this.buildConvexHull([n.maxPoint,e[1]],n.newPoints))):[e[0]]},getConvexHull:function(e){var t,i=!1,n=!1,r=!1,s=!1,o=null,a=null,h=null,l=null,u=null,_=null;for(t=e.length-1;t>=0;t--){var d=e[t];(i===!1||d.lat>i)&&(o=d,i=d.lat),(n===!1||d.lat<n)&&(a=d,n=d.lat),(r===!1||d.lng>r)&&(h=d,r=d.lng),(s===!1||d.lng<s)&&(l=d,s=d.lng)}n!==i?(_=a,u=o):(_=l,u=h);var c=[].concat(this.buildConvexHull([_,u],e),this.buildConvexHull([u,_],e));return c}}}(),L.MarkerCluster.include({getConvexHull:function(){var e,t,i=this.getAllChildMarkers(),n=[];for(t=i.length-1;t>=0;t--)e=i[t].getLatLng(),n.push(e);return L.QuickHull.getConvexHull(n)}}),L.MarkerCluster.include({_2PI:2*Math.PI,_circleFootSeparation:25,_circleStartAngle:0,_spiralFootSeparation:28,_spiralLengthStart:11,_spiralLengthFactor:5,_circleSpiralSwitchover:9,spiderfy:function(){if(this._group._spiderfied!==this&&!this._group._inZoomAnimation){var e,t=this.getAllChildMarkers(null,!0),i=this._group,n=i._map,r=n.latLngToLayerPoint(this._latlng);this._group._unspiderfy(),this._group._spiderfied=this,t.length>=this._circleSpiralSwitchover?e=this._generatePointsSpiral(t.length,r):(r.y+=10,e=this._generatePointsCircle(t.length,r)),this._animationSpiderfy(t,e)}},unspiderfy:function(e){this._group._inZoomAnimation||(this._animationUnspiderfy(e),this._group._spiderfied=null)},_generatePointsCircle:function(e,t){var i,n,r=this._group.options.spiderfyDistanceMultiplier*this._circleFootSeparation*(2+e),s=r/this._2PI,o=this._2PI/e,a=[];for(s=Math.max(s,35),a.length=e,i=0;e>i;i++)n=this._circleStartAngle+i*o,a[i]=new L.Point(t.x+s*Math.cos(n),t.y+s*Math.sin(n))._round();return a},_generatePointsSpiral:function(e,t){var i,n=this._group.options.spiderfyDistanceMultiplier,r=n*this._spiralLengthStart,s=n*this._spiralFootSeparation,o=n*this._spiralLengthFactor*this._2PI,a=0,h=[];for(h.length=e,i=e;i>=0;i--)e>i&&(h[i]=new L.Point(t.x+r*Math.cos(a),t.y+r*Math.sin(a))._round()),a+=s/r+5e-4*i,r+=o/a;return h},_noanimationUnspiderfy:function(){var e,t,i=this._group,n=i._map,r=i._featureGroup,s=this.getAllChildMarkers(null,!0);for(i._ignoreMove=!0,this.setOpacity(1),t=s.length-1;t>=0;t--)e=s[t],r.removeLayer(e),e._preSpiderfyLatlng&&(e.setLatLng(e._preSpiderfyLatlng),delete e._preSpiderfyLatlng),e.setZIndexOffset&&e.setZIndexOffset(0),e._spiderLeg&&(n.removeLayer(e._spiderLeg),delete e._spiderLeg);i.fire("unspiderfied",{cluster:this,markers:s}),i._ignoreMove=!1,i._spiderfied=null}}),L.MarkerClusterNonAnimated=L.MarkerCluster.extend({_animationSpiderfy:function(e,t){var i,n,r,s,o=this._group,a=o._map,h=o._featureGroup,l=this._group.options.spiderLegPolylineOptions;for(o._ignoreMove=!0,i=0;i<e.length;i++)s=a.layerPointToLatLng(t[i]),n=e[i],r=new L.Polyline([this._latlng,s],l),a.addLayer(r),n._spiderLeg=r,n._preSpiderfyLatlng=n._latlng,n.setLatLng(s),n.setZIndexOffset&&n.setZIndexOffset(1e6),h.addLayer(n);this.setOpacity(.3),o._ignoreMove=!1,o.fire("spiderfied",{cluster:this,markers:e})},_animationUnspiderfy:function(){this._noanimationUnspiderfy()}}),L.MarkerCluster.include({_animationSpiderfy:function(e,t){var i,n,r,s,o,a,h=this,l=this._group,u=l._map,_=l._featureGroup,d=this._latlng,c=u.latLngToLayerPoint(d),p=L.Path.SVG,f=L.extend({},this._group.options.spiderLegPolylineOptions),m=f.opacity;for(void 0===m&&(m=L.MarkerClusterGroup.prototype.options.spiderLegPolylineOptions.opacity),p?(f.opacity=0,f.className=(f.className||"")+" leaflet-cluster-spider-leg"):f.opacity=m,l._ignoreMove=!0,i=0;i<e.length;i++)n=e[i],a=u.layerPointToLatLng(t[i]),r=new L.Polyline([d,a],f),u.addLayer(r),n._spiderLeg=r,p&&(s=r._path,o=s.getTotalLength()+.1,s.style.strokeDasharray=o,s.style.strokeDashoffset=o),n.setZIndexOffset&&n.setZIndexOffset(1e6),n.clusterHide&&n.clusterHide(),_.addLayer(n),n._setPos&&n._setPos(c);for(l._forceLayout(),l._animationStart(),i=e.length-1;i>=0;i--)a=u.layerPointToLatLng(t[i]),n=e[i],n._preSpiderfyLatlng=n._latlng,n.setLatLng(a),n.clusterShow&&n.clusterShow(),p&&(r=n._spiderLeg,s=r._path,s.style.strokeDashoffset=0,r.setStyle({opacity:m}));this.setOpacity(.3),l._ignoreMove=!1,setTimeout(function(){l._animationEnd(),l.fire("spiderfied",{cluster:h,markers:e})},200)},_animationUnspiderfy:function(e){var t,i,n,r,s,o,a=this,h=this._group,l=h._map,u=h._featureGroup,_=e?l._latLngToNewLayerPoint(this._latlng,e.zoom,e.center):l.latLngToLayerPoint(this._latlng),d=this.getAllChildMarkers(null,!0),c=L.Path.SVG;for(h._ignoreMove=!0,h._animationStart(),this.setOpacity(1),i=d.length-1;i>=0;i--)t=d[i],t._preSpiderfyLatlng&&(t.closePopup(),t.setLatLng(t._preSpiderfyLatlng),delete t._preSpiderfyLatlng,o=!0,t._setPos&&(t._setPos(_),o=!1),t.clusterHide&&(t.clusterHide(),o=!1),o&&u.removeLayer(t),c&&(n=t._spiderLeg,r=n._path,s=r.getTotalLength()+.1,r.style.strokeDashoffset=s,n.setStyle({opacity:0})));h._ignoreMove=!1,setTimeout(function(){var e=0;for(i=d.length-1;i>=0;i--)t=d[i],t._spiderLeg&&e++;for(i=d.length-1;i>=0;i--)t=d[i],t._spiderLeg&&(t.clusterShow&&t.clusterShow(),t.setZIndexOffset&&t.setZIndexOffset(0),e>1&&u.removeLayer(t),l.removeLayer(t._spiderLeg),delete t._spiderLeg);h._animationEnd(),h.fire("unspiderfied",{cluster:a,markers:d})},200)}}),L.MarkerClusterGroup.include({_spiderfied:null,unspiderfy:function(){this._unspiderfy.apply(this,arguments)},_spiderfierOnAdd:function(){this._map.on("click",this._unspiderfyWrapper,this),this._map.options.zoomAnimation&&this._map.on("zoomstart",this._unspiderfyZoomStart,this),this._map.on("zoomend",this._noanimationUnspiderfy,this),L.Browser.touch||this._map.getRenderer(this)},_spiderfierOnRemove:function(){this._map.off("click",this._unspiderfyWrapper,this),this._map.off("zoomstart",this._unspiderfyZoomStart,this),this._map.off("zoomanim",this._unspiderfyZoomAnim,this),this._map.off("zoomend",this._noanimationUnspiderfy,this),this._noanimationUnspiderfy()
},_unspiderfyZoomStart:function(){this._map&&this._map.on("zoomanim",this._unspiderfyZoomAnim,this)},_unspiderfyZoomAnim:function(e){L.DomUtil.hasClass(this._map._mapPane,"leaflet-touching")||(this._map.off("zoomanim",this._unspiderfyZoomAnim,this),this._unspiderfy(e))},_unspiderfyWrapper:function(){this._unspiderfy()},_unspiderfy:function(e){this._spiderfied&&this._spiderfied.unspiderfy(e)},_noanimationUnspiderfy:function(){this._spiderfied&&this._spiderfied._noanimationUnspiderfy()},_unspiderfyLayer:function(e){e._spiderLeg&&(this._featureGroup.removeLayer(e),e.clusterShow&&e.clusterShow(),e.setZIndexOffset&&e.setZIndexOffset(0),this._map.removeLayer(e._spiderLeg),delete e._spiderLeg)}}),L.MarkerClusterGroup.include({refreshClusters:function(e){return e?e instanceof L.MarkerClusterGroup?e=e._topClusterLevel.getAllChildMarkers():e instanceof L.LayerGroup?e=e._layers:e instanceof L.MarkerCluster?e=e.getAllChildMarkers():e instanceof L.Marker&&(e=[e]):e=this._topClusterLevel.getAllChildMarkers(),this._flagParentsIconsNeedUpdate(e),this._refreshClustersIcons(),this.options.singleMarkerMode&&this._refreshSingleMarkerModeMarkers(e),this},_flagParentsIconsNeedUpdate:function(e){var t,i;for(t in e)for(i=e[t].__parent;i;)i._iconNeedsUpdate=!0,i=i.__parent},_refreshSingleMarkerModeMarkers:function(e){var t,i;for(t in e)i=e[t],this.hasLayer(i)&&i.setIcon(this._overrideMarkerIcon(i))}}),L.Marker.include({refreshIconOptions:function(e,t){var i=this.options.icon;return L.setOptions(i,e),this.setIcon(i),t&&this.__parent&&this.__parent._group.refreshClusters(this),this}}),e.MarkerClusterGroup=t,e.MarkerCluster=i});
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
L.Photo = L.FeatureGroup.extend({
	options: {
		icon: {
			iconSize: [40, 40]
		}
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
			icon: L.divIcon(L.extend({
				html: '<img src="' + photo.thumbnail + '" ' + (photo.thumbnail2x!=='' ? 'srcset="' + photo.thumbnail + ' 1x, ' + photo.thumbnail2x + ' 2x"' : '' )+ '></img>​',
				className: 'leaflet-marker-photo'
			}, photo, this.options.icon)),
			title: photo.caption || ''
		});
		marker.photo = photo;
		return marker;
	}
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
			iconCreateFunction: function(cluster) {
				return new L.DivIcon(L.extend({
					className: 'leaflet-marker-photo',
					html: '<img src="' + cluster.getAllChildMarkers()[0].photo.thumbnail + '" ' + (cluster.getAllChildMarkers()[0].photo.thumbnail2x!=='' ? 'srcset="' + cluster.getAllChildMarkers()[0].photo.thumbnail + ' 1x, ' + cluster.getAllChildMarkers()[0].photo.thumbnail2x + ' 2x"' : '' )+ '></img>​<b>' + cluster.getChildCount() + '</b>'
				}, this.icon));
		   	},
			icon: {
				iconSize: [40, 40]
			}
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
		}

	});

	L.photo.cluster = function (options) {
		return new L.Photo.Cluster(options);
	};

}

"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<p>", " <input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='Untitled'></p>"], ["<p>", " <input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='Untitled'></p>"]),
    _templateObject2 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='$", "' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='$", "' value='$", "'>"]),
    _templateObject3 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject5 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"]),
    _templateObject6 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"], ["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"]),
    _templateObject7 = _taggedTemplateLiteral(["\n\t\t\t<form>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='full_photo'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='hidden'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='share_button_visible'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='password'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"], ["\n\t\t\t<form>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='full_photo'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='hidden'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='share_button_visible'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='password'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"]),
    _templateObject8 = _taggedTemplateLiteral(["?albumIDs=", ""], ["?albumIDs=", ""]),
    _templateObject9 = _taggedTemplateLiteral(["<p>", " '$", "' ", " '$", "'?</p>"], ["<p>", " '$", "' ", " '$", "'?</p>"]),
    _templateObject10 = _taggedTemplateLiteral(["<p>", " '$", "'?</p>"], ["<p>", " '$", "'?</p>"]),
    _templateObject11 = _taggedTemplateLiteral(["<p>", " '$", "' ", "</p>"], ["<p>", " '$", "' ", "</p>"]),
    _templateObject12 = _taggedTemplateLiteral(["<p>", " $", " ", "</p>"], ["<p>", " $", " ", "</p>"]),
    _templateObject13 = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject14 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject15 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject16 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject17 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject18 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject19 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject20 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject21 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject22 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject23 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject24 = _taggedTemplateLiteral(["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject25 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject26 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject27 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' ", "><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' ", "><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject28 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big'>"]),
    _templateObject29 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject30 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject31 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject32 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject33 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject34 = _taggedTemplateLiteral(["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"], ["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"]),
    _templateObject35 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject36 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject37 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject38 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject39 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject40 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject41 = _taggedTemplateLiteral(["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" >&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"], ["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" >&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"]),
    _templateObject42 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_users\">", " ", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\">", " ", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_users\">", " ", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\">", " ", "</a>"]),
    _templateObject43 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_logs\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\">", " ", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_logs\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\">", " ", "</a>"]),
    _templateObject44 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_update\">", " ", "</a>\n\t\t"], ["\n\t\t<a class=\"linkMenu\" id=\"button_update\">", " ", "</a>\n\t\t"]),
    _templateObject45 = _taggedTemplateLiteral(["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "], ["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "]),
    _templateObject46 = _taggedTemplateLiteral(["\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"], ["\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"]),
    _templateObject47 = _taggedTemplateLiteral(["<link data-prefetch rel=\"prefetch\" href=\"", "\">"], ["<link data-prefetch rel=\"prefetch\" href=\"", "\">"]),
    _templateObject48 = _taggedTemplateLiteral(["<p>", " '", "' ", "</p>"], ["<p>", " '", "' ", "</p>"]),
    _templateObject49 = _taggedTemplateLiteral(["<p>", " ", " ", "</p>"], ["<p>", " ", " ", "</p>"]),
    _templateObject50 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"]),
    _templateObject51 = _taggedTemplateLiteral(["<p>", " ", " ", " ", "</p>"], ["<p>", " ", " ", " ", "</p>"]),
    _templateObject52 = _taggedTemplateLiteral(["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject53 = _taggedTemplateLiteral(["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='hidden' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='hidden' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject54 = _taggedTemplateLiteral(["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"], ["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"]),
    _templateObject55 = _taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"], ["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"]),
    _templateObject56 = _taggedTemplateLiteral(["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"], ["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"]),
    _templateObject57 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"]),
    _templateObject58 = _taggedTemplateLiteral(["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"], ["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"]),
    _templateObject59 = _taggedTemplateLiteral(["\n\t\t\t<div class='downloads'>\n\t\t"], ["\n\t\t\t<div class='downloads'>\n\t\t"]),
    _templateObject60 = _taggedTemplateLiteral(["\n\t\t\t</div>\n\t\t"], ["\n\t\t\t</div>\n\t\t"]),
    _templateObject61 = _taggedTemplateLiteral(["?photoIDs=", "&kind=", ""], ["?photoIDs=", "&kind=", ""]),
    _templateObject62 = _taggedTemplateLiteral(["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"]),
    _templateObject63 = _taggedTemplateLiteral(["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"], ["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"]),
    _templateObject64 = _taggedTemplateLiteral(["\n\t\t</div>\n\t\t</div>\n\t"], ["\n\t\t</div>\n\t\t</div>\n\t"]),
    _templateObject65 = _taggedTemplateLiteral(["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"], ["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"]),
    _templateObject66 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject67 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject68 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject69 = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject70 = _taggedTemplateLiteral(["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t"]),
    _templateObject71 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='delete'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='delete'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject72 = _taggedTemplateLiteral(["url(\"", "\")"], ["url(\"", "\")"]),
    _templateObject73 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]),
    _templateObject74 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"]),
    _templateObject75 = _taggedTemplateLiteral(["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject76 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"]),
    _templateObject77 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject78 = _taggedTemplateLiteral(["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"], ["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"]),
    _templateObject79 = _taggedTemplateLiteral(["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"], ["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"]),
    _templateObject80 = _taggedTemplateLiteral(["<a id=\"Check_Update_Lychee\" class=\"basicModal__button\">", "</a>"], ["<a id=\"Check_Update_Lychee\" class=\"basicModal__button\">", "</a>"]),
    _templateObject81 = _taggedTemplateLiteral(["<a id=\"Update_Lychee\" class=\"basicModal__button\">", "</a>"], ["<a id=\"Update_Lychee\" class=\"basicModal__button\">", "</a>"]);

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
csrf = {};

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

	if (album.json == null || album.isSmartID(album.json.id) === true || !album.json.parent_id || album.json.parent_id === 0) return '';
	return album.json.parent_id;
};

album.getID = function () {

	var id = null;

	var isID = function isID(id) {
		if (id === '0' || id === 'f' || id === 's' || id === 'r') return true;
		return $.isNumeric(id);
	};

	if (photo.json) id = photo.json.album;else if (album.json) id = album.json.id;else if (mapview.albumID) id = mapview.albumID;

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

album.getSubByID = function (albumID) {

	// Function returns the JSON of a subalbum

	if (albumID == null || !album.json || !album.json.albums) {
		lychee.error('Error: Album json not found!');
		return undefined;
	}

	var i = 0;
	while (i < album.json.albums.length) {
		if (parseInt(album.json.albums[i].id) === parseInt(albumID)) {
			return album.json.albums[i];
		}
		i++;
	}

	lychee.error('Error: album ' + albumID + ' not found!');
	return undefined;
};

// noinspection DuplicatedCode
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

// noinspection DuplicatedCode
album.deleteSubByID = function (albumID) {

	if (albumID == null || !album.json || !album.json.albums) {
		lychee.error('Error: Album json not found !');
		return false;
	}

	var deleted = false;

	$.each(album.json.albums, function (i) {

		if (parseInt(album.json.albums[i].id) === parseInt(albumID)) {
			album.json.albums.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

album.load = function (albumID) {
	var refresh = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	var params = {
		albumID: albumID,
		password: ''
	};

	var processData = function processData(data) {

		if (data === 'Warning: Wrong password!') {
			// User hit Cancel at the password prompt
			return false;
		}

		if (data === 'Warning: Album private!') {

			if (document.location.hash.replace('#', '').split('/')[1] !== undefined) {
				// Display photo only
				lychee.setMode('view');
				lychee.footer_hide();
			} else {
				// Album not public
				lychee.content.show();
				lychee.footer_show();
				if (!visible.albums() && !visible.album()) lychee.goto();
			}
			return false;
		}

		album.json = data;

		if (refresh === false) {
			lychee.animate('.content', 'contentZoomOut');
		}
		var waitTime = 300;

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
	};

	api.post('Album::get', params, function (data) {

		if (data === 'Warning: Wrong password!') {
			password.getDialog(albumID, function () {

				params.password = password.value;

				api.post('Album::get', params, function (data) {
					albums.refresh();
					processData(data);
				});
			});
		} else {
			processData(data);
		}
	});
};

album.parse = function () {

	if (!album.json.title) album.json.title = lychee.locale['UNTITLED'];
};

album.add = function () {
	var IDs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;


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
				if (IDs != null && callback != null) {
					callback(IDs, data, false); // we do not confirm
				} else {
					albums.refresh();
					lychee.goto(data);
				}
			} else {
				lychee.error(null, params, data);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject, lychee.locale['TITLE_NEW_ALBUM']),
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

album.setTitle = function (albumIDs) {

	var oldTitle = '';
	var msg = '';

	if (!albumIDs) return false;
	if (!albumIDs instanceof Array) albumIDs = [albumIDs];

	if (albumIDs.length === 1) {

		// Get old title if only one album is selected
		if (album.json) {
			if (parseInt(album.getID()) === parseInt(albumIDs[0])) {
				oldTitle = album.json.title;
			} else oldTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!oldTitle && albums.json) oldTitle = albums.getByID(albumIDs[0]).title;
	}

	var action = function action(data) {

		basicModal.close();

		var newTitle = data.title;

		if (visible.album()) {

			if (albumIDs.length === 1 && parseInt(album.getID()) === parseInt(albumIDs[0])) {
				// Rename only one album

				album.json.title = newTitle;
				view.album.title();

				if (albums.json) albums.getByID(albumIDs[0]).title = newTitle;
			} else {
				albumIDs.forEach(function (id) {
					album.getSubByID(id).title = newTitle;
					view.album.content.titleSub(id);

					if (albums.json) albums.getByID(id).title = newTitle;
				});
			}
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

	var input = lychee.html(_templateObject2, lychee.locale['ALBUM_TITLE'], oldTitle);

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
		body: lychee.html(_templateObject5, lychee.locale['ALBUM_NEW_DESCRIPTION'], lychee.locale['ALBUM_DESCRIPTION'], oldDescription),
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

album.setLicense = function (albumID) {

	var callback = function callback() {
		$('select#license').val(album.json.license === '' ? 'none' : album.json.license);
		return false;
	};

	var action = function action(data) {

		var license = data.license;

		basicModal.close();

		var params = {
			albumID: albumID,
			license: license
		};

		api.post('Album::setLicense', params, function (data) {

			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				if (visible.album()) {
					album.json.license = params.license;
					view.album.license();
				}
			}
		});
	};

	var msg = lychee.html(_templateObject6, lychee.locale['ALBUM_LICENSE'], lychee.locale['ALBUM_LICENSE_NONE'], lychee.locale['ALBUM_RESERVED'], lychee.locale['ALBUM_LICENSE_HELP']);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale['ALBUM_SET_LICENSE'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

album.setPublic = function (albumID, e) {

	var password = '';

	if (!basicModal.visible()) {

		var msg = lychee.html(_templateObject7, lychee.locale['ALBUM_PUBLIC'], lychee.locale['ALBUM_PUBLIC_EXPL'], build.iconic('check'), lychee.locale['ALBUM_FULL'], lychee.locale['ALBUM_FULL_EXPL'], build.iconic('check'), lychee.locale['ALBUM_HIDDEN'], lychee.locale['ALBUM_HIDDEN_EXPL'], build.iconic('check'), lychee.locale['ALBUM_DOWNLOADABLE'], lychee.locale['ALBUM_DOWNLOADABLE_EXPL'], build.iconic('check'), lychee.locale['ALBUM_SHARE_BUTTON_VISIBLE'], lychee.locale['ALBUM_SHARE_BUTTON_VISIBLE_EXPL'], build.iconic('check'), lychee.locale['ALBUM_PASSWORD_PROT'], lychee.locale['ALBUM_PASSWORD_PROT_EXPL'], lychee.locale['PASSWORD']);

		basicModal.show({
			body: msg,
			buttons: {
				action: {
					title: lychee.locale['ALBUM_SHARING_CONFIRM'],
					// Call setPublic function without showing the modal
					fn: function fn() {
						return album.setPublic(albumID, e);
					}
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').on('click', function () {
			if ($(this).prop('checked') === true) {
				$('.basicModal .choice input').attr('disabled', false);

				if (album.json.public === '1') {
					// Initialize options based on album settings.
					if (album.json.full_photo !== null && album.json.full_photo === '1') $('.basicModal .choice input[name="full_photo"]').prop('checked', true);
					if (album.json.visible === '0') $('.basicModal .choice input[name="hidden"]').prop('checked', true);
					if (album.json.downloadable === '1') $('.basicModal .choice input[name="downloadable"]').prop('checked', true);
					if (album.json.share_button_visible === '1') $('.basicModal .choice input[name="share_button_visible"]').prop('checked', true);
					if (album.json.password === '1') {
						$('.basicModal .choice input[name="password"]').prop('checked', true);
						$('.basicModal .choice input[name="passwordtext"]').show();
					}
				} else {
					// Initialize options based on global settings.
					if (lychee.full_photo) {
						$('.basicModal .choice input[name="full_photo"]').prop('checked', true);
					}
					if (lychee.downloadable) {
						$('.basicModal .choice input[name="downloadable"]').prop('checked', true);
					}
					if (lychee.share_button_visible) {
						$('.basicModal .choice input[name="share_button_visible"]').prop('checked', true);
					}
				}
			} else {
				$('.basicModal .choice input').prop('checked', false).attr('disabled', true);
				$('.basicModal .choice input[name="passwordtext"]').hide();
			}
		});

		if (album.json.public === '1') {
			$('.basicModal .switch input[name="public"]').click();
		} else {
			$('.basicModal .choice input').attr('disabled', true);
		}

		$('.basicModal .choice input[name="password"]').on('change', function () {

			if ($(this).prop('checked') === true) $('.basicModal .choice input[name="passwordtext"]').show().focus();else $('.basicModal .choice input[name="passwordtext"]').hide();
		});

		return true;
	}

	albums.refresh();

	// Set public
	if ($('.basicModal .switch input[name="public"]:checked').length === 1) album.json.public = '1';else album.json.public = '0';

	// Set full photo
	if ($('.basicModal .choice input[name="full_photo"]:checked').length === 1) album.json.full_photo = '1';else album.json.full_photo = '0';

	// Set visible
	if ($('.basicModal .choice input[name="hidden"]:checked').length === 1) album.json.visible = '0';else album.json.visible = '1';

	// Set downloadable
	if ($('.basicModal .choice input[name="downloadable"]:checked').length === 1) album.json.downloadable = '1';else album.json.downloadable = '0';

	// Set share_button_visible
	if ($('.basicModal .choice input[name="share_button_visible"]:checked').length === 1) album.json.share_button_visible = '1';else album.json.share_button_visible = '0';

	// Set password
	var oldPassword = album.json.password;
	if ($('.basicModal .choice input[name="password"]:checked').length === 1) {
		password = $('.basicModal .choice input[name="passwordtext"]').val();
		album.json.password = '1';
	} else {
		password = '';
		album.json.password = '0';
	}

	// Modal input has been processed, now it can be closed
	basicModal.close();

	// Set data and refresh view
	if (visible.album()) {

		view.album.public();
		view.album.hidden();
		view.album.downloadable();
		view.album.shareButtonVisible();
		view.album.password();
	}

	var params = {
		albumID: albumID,
		full_photo: album.json.full_photo,
		public: album.json.public,
		visible: album.json.visible,
		downloadable: album.json.downloadable,
		share_button_visible: album.json.share_button_visible
	};
	if (oldPassword !== album.json.password || password.length > 0) {
		// We send the password only if there's been a change; that way the
		// server will keep the current password if it wasn't changed.
		params.password = password;
	}

	api.post('Album::setPublic', params, function (data) {

		if (data !== true) lychee.error(null, params, data);
	});
};

album.share = function (service) {

	if (album.json.hasOwnProperty('share_button_visible') && album.json.share_button_visible !== '1') {
		return;
	}

	var url = location.href;

	switch (service) {
		case 'twitter':
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case 'facebook':
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(album.json.title));
			break;
		case 'mail':
			location.href = "mailto:?subject=" + encodeURI(album.json.title) + "&body=" + encodeURI(url);
			break;
	}
};

album.getArchive = function (albumIDs) {

	var link = '';

	// double check with API_V2 this will not work...
	if (lychee.api_V2) {
		location.href = api.get_url('Album::getArchive') + lychee.html(_templateObject8, albumIDs.join());
	} else {
		var url = api.path + "?function=Album::getArchive&albumID=" + albumIDs[0];

		if (location.href.indexOf('index.html') > 0) link = location.href.replace(location.hash, '').replace('index.html', url);else link = location.href.replace(location.hash, '') + url;

		if (lychee.publicMode === true) link += "&password=" + encodeURIComponent(password.value);

		location.href = link;
	}
};

album.buildMessage = function (albumIDs, albumID, op1, op2, ops) {

	var title = '';
	var sTitle = '';
	var msg = '';

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	// Get title of first album
	if (parseInt(albumID, 10) === 0) {
		title = lychee.locale['ROOT'];
	} else if (albums.json) title = albums.getByID(albumID).title;

	// Fallback for first album without a title
	if (title === '') title = lychee.locale['UNTITLED'];

	if (albumIDs.length === 1) {

		// Get title of second album
		if (albums.json) sTitle = albums.getByID(albumIDs[0]).title;

		// Fallback for second album without a title
		if (sTitle === '') sTitle = lychee.locale['UNTITLED'];

		msg = lychee.html(_templateObject9, lychee.locale[op1], sTitle, lychee.locale[op2], title);
	} else {

		msg = lychee.html(_templateObject10, lychee.locale[ops], title);
	}

	return msg;
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
			} else if (visible.album()) {

				albums.refresh();
				if (albumIDs.length === 1 && parseInt(album.getID()) === parseInt(albumIDs[0])) {
					lychee.goto(album.getParent());
				} else {
					albumIDs.forEach(function (id) {
						album.deleteSubByID(id);
						view.album.content.deleteSub(id);
					});
				}
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
		if (album.json) {
			if (parseInt(album.getID()) === parseInt(albumIDs[0])) {
				albumTitle = album.json.title;
			} else albumTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!albumTitle && albums.json) albumTitle = albums.getByID(albumIDs).title;

		// Fallback for album without a title
		if (albumTitle === '') albumTitle = lychee.locale['UNTITLED'];

		msg = lychee.html(_templateObject11, lychee.locale['DELETE_ALBUM_CONFIRMATION_1'], albumTitle, lychee.locale['DELETE_ALBUM_CONFIRMATION_2']);
	} else {

		action.title = lychee.locale['DELETE_ALBUMS_QUESTION'];
		cancel.title = lychee.locale['KEEP_ALBUMS'];

		msg = lychee.html(_templateObject12, lychee.locale['DELETE_ALBUMS_CONFIRMATION_1'], albumIDs.length, lychee.locale['DELETE_ALBUMS_CONFIRMATION_2']);
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

album.merge = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;


	var action = function action() {

		basicModal.close();
		albumIDs.unshift(albumID);

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

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, 'ALBUM_MERGE_1', 'ALBUM_MERGE_2', 'ALBUMS_MERGE'),
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
	} else {
		action();
	}
};

album.setAlbum = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;


	var action = function action() {

		basicModal.close();
		albumIDs.unshift(albumID);

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post('Album::move', params, function (data) {

			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				album.reload();
			}
		});
	};

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, 'ALBUM_MOVE_1', 'ALBUM_MOVE_2', 'ALBUMS_MOVE'),
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
	} else {
		action();
	}
};

album.isUploadable = function () {
	if (lychee.admin) {
		return true;
	}
	if (lychee.publicMode || !lychee.upload) {
		return false;
	}

	// For special cases of no album / smart album / etc. we return true.
	// It's only for regular non-matching albums that we return false.
	if (album.json === null || !album.json.owner) {
		return true;
	}

	return album.json.owner === lychee.username;
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
			if (data.smartalbums != null) albums._createSmartAlbums(data.smartalbums);

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
				setTimeout(function () {
					lychee.footer_show();
				}, 300);
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

	var i = void 0;
	for (i = 0; i < 3; i++) {
		if (!album.thumbs[i]) {
			album.thumbs[i] = album.password === '1' ? 'img/password.svg' : 'img/no_images.svg';
		}
	}
};

albums._createSmartAlbums = function (data) {

	if (data.unsorted) {
		data.unsorted = {
			id: 0,
			title: lychee.locale['UNSORTED'],
			sysdate: data.unsorted.num + ' ' + lychee.locale['NUM_PHOTOS'],
			unsorted: '1',
			thumbs: data.unsorted.thumbs,
			thumbs2x: data.unsorted.thumbs2x ? data.unsorted.thumbs2x : null,
			types: data.unsorted.types
		};
	}

	if (data.starred) {
		data.starred = {
			id: 'f',
			title: lychee.locale['STARRED'],
			sysdate: data.starred.num + ' ' + lychee.locale['NUM_PHOTOS'],
			star: '1',
			thumbs: data.starred.thumbs,
			thumbs2x: data.starred.thumbs2x ? data.starred.thumbs2x : null,
			types: data.starred.types
		};
	}

	if (data.public) {
		data.public = {
			id: 's',
			title: lychee.locale['PUBLIC'],
			sysdate: data.public.num + ' ' + lychee.locale['NUM_PHOTOS'],
			public: '1',
			thumbs: data.public.thumbs,
			thumbs2x: data.public.thumbs2x ? data.public.thumbs2x : null,
			visible: '0',
			types: data.public.types
		};
	}

	if (data.recent) {
		data.recent = {
			id: 'r',
			title: lychee.locale['RECENT'],
			sysdate: data.recent.num + ' ' + lychee.locale['NUM_PHOTOS'],
			recent: '1',
			thumbs: data.recent.thumbs,
			thumbs2x: data.recent.thumbs2x ? data.recent.thumbs2x : null,
			types: data.recent.types
		};
	}
};

albums.isShared = function (albumID) {

	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var found = false;

	var func = function func() {
		if (parseInt(this.id, 10) === parseInt(albumID, 10)) {
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
		if (this.albums) {
			$.each(this.albums, func);
		}
	};

	$.each(albums.json.albums, func);

	if (json === undefined && albums.json.shared_albums !== null) $.each(albums.json.shared_albums, func);

	return json;
};

albums.deleteByID = function (albumID) {

	// Function returns the JSON of an album
	// This function is only ever invoked for top-level albums so it
	// doesn't need to descend down the albums tree.

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
 * @description This module is used to generate HTML-Code.
 */

build = {};

build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';


	var html = '';

	html += lychee.html(_templateObject13, classes, icon);

	return html;
};

build.divider = function (title) {

	var html = '';

	html += lychee.html(_templateObject14, title);

	return html;
};

build.editIcon = function (id) {

	var html = '';

	html += lychee.html(_templateObject15, id, build.iconic('pencil'));

	return html;
};

build.multiselect = function (top, left) {

	return lychee.html(_templateObject16, top, left);
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

	html += lychee.html(_templateObject17, disabled ? "disabled" : "", data.id, build.getAlbumThumb(data, 2), build.getAlbumThumb(data, 1), build.getAlbumThumb(data, 0), data.title, data.title, date_stamp);

	if (album.isUploadable() && !disabled) {

		html += lychee.html(_templateObject18, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' ? 'badge--visible' : '', data.visible === '1' ? 'badge--not--hidden' : 'badge--hidden', build.iconic('eye'), data.unsorted === '1' ? 'badge--visible' : '', build.iconic('list'), data.recent === '1' ? 'badge--visible badge--list' : '', build.iconic('clock'), data.password === '1' ? 'badge--visible' : '', build.iconic('lock-locked'));
	}

	if (data.albums && data.albums.length > 0 || data.hasOwnProperty('has_albums') && data.has_albums === '1') {
		html += lychee.html(_templateObject19, build.iconic('layers'));
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
		thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	if (data.thumbUrl === 'uploads/thumb/' && isVideo) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	} else if (data.thumbUrl === 'uploads/thumb/' && isRaw) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
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
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.thumbUrl + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
		thumbnail += "</span>";
	} else {

		if (data.small !== '') {
			if (data.hasOwnProperty('small2x') && data.small2x !== '') {
				thumb2x = "data-srcset='" + data.small + " " + parseInt(data.small_dim, 10) + "w, " + data.small2x + " " + parseInt(data.small2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.small + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
			thumbnail += "</span>";
		} else if (data.medium !== '') {
			if (data.hasOwnProperty('medium2x') && data.medium2x !== '') {
				thumb2x = "data-srcset='" + data.medium + " " + parseInt(data.medium_dim, 10) + "w, " + data.medium2x + " " + parseInt(data.medium2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.medium + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? ' livephoto' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.url + "' alt='Photo thumbnail' data-overlay='false' draggable='false'>";
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
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.thumbUrl + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject20, disabled ? "disabled" : "", data.album, data.id, thumbnail, data.title, data.title);

	if (data.cameraDate === '1') html += lychee.html(_templateObject21, build.iconic('camera-slr'), data.takedate);else html += lychee.html(_templateObject22, data.sysdate);

	html += "</div>";

	if (album.isUploadable()) {

		html += lychee.html(_templateObject23, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' && album.json.public !== '1' ? 'badge--visible badge--hidden' : '', build.iconic('eye'));
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
		html = lychee.html(_templateObject24, data.title, data.description);
	} else if (type && type === 'takedate' && data.takedate !== '') {
		html = lychee.html(_templateObject25, data.title, data.takedate);
	}
	// fall back to exif data if there is no description
	else if (exifHash !== '') {

			html += lychee.html(_templateObject26, data.title, data.shutter.replace('s', 'sec'), data.aperture.replace('f/', '&fnof; / '), lychee.locale['PHOTO_ISO'], data.iso, data.focal, data.lens && data.lens !== '' ? '(' + data.lens + ')' : '');
		}

	return html;
};

build.imageview = function (data, visibleControls, autoplay) {

	var html = '';
	var thumb = '';

	if (data.type.indexOf('video') > -1) {
		html += lychee.html(_templateObject27, visibleControls === true ? '' : 'full', autoplay ? 'autoplay' : '', data.url);
	} else if (data.type.indexOf('raw') > -1) {
		html += lychee.html(_templateObject28, visibleControls === true ? '' : 'full');
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
				img = "<img id='image' class='" + (visibleControls === true ? '' : 'full') + "' src='" + data.medium + "' " + medium + "  draggable='false' alt='medium'>";
			} else {
				img = "<img id='image' class='" + (visibleControls === true ? '' : 'full') + "' src='" + data.url + "' draggable='false' alt='big'>";
			}
		} else {

			if (data.medium !== '') {
				medium_dims = data.medium_dim.split("x");
				medium_width = medium_dims[0];
				medium_height = medium_dims[1];
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.medium + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + medium_width + "px; height: " + medium_height + "px'></div>";
			} else {
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.url + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + data.width + "px; height: " + data.height + "px'></div>";
			}
		}

		html += lychee.html(_templateObject29, img);

		if (lychee.image_overlay) html += build.overlay_image(data);
	}

	html += "\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic('caret-left') + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic('caret-right') + "</a></div>\n\t\t\t";

	return { html: html, thumb: thumb };
};

build.no_content = function (typ) {

	var html = '';

	html += lychee.html(_templateObject30, build.iconic(typ));

	switch (typ) {
		case 'magnifying-glass':
			html += lychee.html(_templateObject31, lychee.locale['VIEW_NO_RESULT']);
			break;
		case 'eye':
			html += lychee.html(_templateObject31, lychee.locale['VIEW_NO_PUBLIC_ALBUMS']);
			break;
		case 'cog':
			html += lychee.html(_templateObject31, lychee.locale['VIEW_NO_CONFIGURATION']);
			break;
		case 'question-mark':
			html += lychee.html(_templateObject31, lychee.locale['VIEW_PHOTO_NOT_FOUND']);
			break;
	}

	html += "</div>";

	return html;
};

build.uploadModal = function (title, files) {

	var html = '';

	html += lychee.html(_templateObject32, title);

	var i = 0;

	while (i < files.length) {

		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + '...' + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject33, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

build.uploadNewFile = function (name) {

	if (name.length > 40) {
		name = name.substr(0, 17) + '...' + name.substr(name.length - 20, 20);
	}

	return lychee.html(_templateObject34, name);
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
				html += lychee.html(_templateObject35, a_class, tag, index, build.iconic('x'));
			} else {
				html += lychee.html(_templateObject36, a_class, tag);
			}
		});
	} else {

		html = lychee.html(_templateObject37, lychee.locale['NO_TAGS']);
	}

	return html;
};

build.user = function (user) {
	var html = lychee.html(_templateObject38, user.id, user.id, user.username, user.id, user.id);

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
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
			return album.setTitle([albumID]);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE'], visible: showMerge, fn: function fn() {
			basicContext.close();contextMenu.move([albumID], e, album.merge, 'ROOT', false);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE'], visible: lychee.sub_albums, fn: function fn() {
			basicContext.close();contextMenu.move([albumID], e, album.setAlbum, 'ROOT');
		} },
	// { title: build.iconic('cloud') + lychee.locale['SHARE_WITH'],    visible: lychee.api_V2 && lychee.upload,   fn: () => alert('ho')},
	{ title: build.iconic('trash') + lychee.locale['DELETE'], fn: function fn() {
			return album.delete([albumID]);
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD'], fn: function fn() {
			return album.getArchive([albumID]);
		} }];

	$('.album[data-id="' + albumID + '"]').addClass('active');

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.albumMulti = function (albumIDs, e) {

	multiselect.stopResize();

	// Automatically merge selected albums when albumIDs contains more than one album
	// Show list of albums otherwise
	var autoMerge = albumIDs.length > 1;

	// Show merge-item when there's more than one album
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME_ALL'], fn: function fn() {
			return album.setTitle(albumIDs);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE_ALL'], visible: showMerge && autoMerge, fn: function fn() {
			var albumID = albumIDs.shift();album.merge(albumIDs, albumID);
		} }, { title: build.iconic('collapse-left') + lychee.locale['MERGE'], visible: showMerge && !autoMerge, fn: function fn() {
			basicContext.close();contextMenu.move(albumIDs, e, album.merge, 'ROOT', false);
		} }, { title: build.iconic('folder') + lychee.locale['MOVE_ALL'], visible: lychee.sub_albums, fn: function fn() {
			basicContext.close();contextMenu.move(albumIDs, e, album.setAlbum, 'ROOT');
		} }, { title: build.iconic('trash') + lychee.locale['DELETE_ALL'], fn: function fn() {
			return album.delete(albumIDs);
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD_ALL'], fn: function fn() {
			return album.getArchive(albumIDs);
		} }];

	if (!lychee.api_V2) {
		items.splice(-1);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.buildList = function (lists, exclude, action) {
	var parent = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
	var layer = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;


	var find = function find(excl, id) {
		var i = void 0;
		for (i = 0; i < excl.length; i++) {
			if (parseInt(excl[i], 10) === parseInt(id, 10)) return true;
		}
		return false;
	};

	var items = [];

	var i = 0;
	while (i < lists.length) {
		if (layer === 0 && !lists[i].parent_id || lists[i].parent_id === parent) {
			(function () {

				var item = lists[i];

				var thumb = 'img/no_cover.svg';
				if (item.thumbs && item.thumbs[0]) {
					if (item.thumbs[0] === 'uploads/thumb/' && item.types[0] && item.types[0].indexOf('video') > -1) {
						thumb = 'img/play-icon.png';
					} else {
						thumb = item.thumbs[0];
					}
				} else if (item.thumbUrl) {
					if (item.thumbUrl === 'uploads/thumb/' && item.type.indexOf('video') > -1) {
						thumb = 'img/play-icon.png';
					} else {
						thumb = item.thumbUrl;
					}
				}

				if (item.title === '') item.title = lychee.locale['UNTITLED'];

				var prefix = layer > 0 ? '&nbsp;&nbsp;'.repeat(layer - 1) + '└ ' : '';

				var html = lychee.html(_templateObject39, prefix, thumb, item.title);

				items.push({
					title: html,
					disabled: find(exclude, item.id),
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
			})();
		}

		i++;
	}

	return items;
};

contextMenu.albumTitle = function (albumID, e) {

	api.post('Albums::get', {}, function (data) {

		var items = [];

		items = items.concat({ title: lychee.locale['ROOT'], disabled: albumID === false, fn: function fn() {
				return lychee.goto();
			} });

		if (data.albums && data.albums.length > 0) {

			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.albums, albumID !== false ? [parseInt(albumID, 10)] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (data.shared_albums && data.shared_albums.length > 0) {

			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.shared_albums, albumID !== false ? [parseInt(albumID, 10)] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (albumID !== false && !album.isSmartID(albumID) && album.isUploadable()) {
			if (items.length > 0) {
				items.unshift({});
			}

			items.unshift({ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
					return album.setTitle([albumID]);
				} });
		}

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
		} }, { title: build.iconic('layers') + lychee.locale['COPY_TO'], fn: function fn() {
			basicContext.close();contextMenu.move([photoID], e, photo.copyTo, 'UNSORTED');
		} }, { title: build.iconic('folder') + lychee.locale['MOVE'], fn: function fn() {
			basicContext.close();contextMenu.move([photoID], e, photo.setAlbum, 'UNSORTED');
		} }, { title: build.iconic('trash') + lychee.locale['DELETE'], fn: function fn() {
			return photo.delete([photoID]);
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD'], fn: function fn() {
			return photo.getArchive([photoID]);
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
		} }, { title: build.iconic('layers') + lychee.locale['COPY_ALL_TO'], fn: function fn() {
			basicContext.close();contextMenu.move(photoIDs, e, photo.copyTo, 'UNSORTED');
		} }, { title: build.iconic('folder') + lychee.locale['MOVE_ALL'], fn: function fn() {
			basicContext.close();contextMenu.move(photoIDs, e, photo.setAlbum, 'UNSORTED');
		} }, { title: build.iconic('trash') + lychee.locale['DELETE_ALL'], fn: function fn() {
			return photo.delete(photoIDs);
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD_ALL'], fn: function fn() {
			return photo.getArchive(photoIDs, 'FULL');
		} }];

	if (!lychee.api_V2) {
		items.splice(-1);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoTitle = function (albumID, photoID, e) {

	var items = [{ title: build.iconic('pencil') + lychee.locale['RENAME'], fn: function fn() {
			return photo.setTitle([photoID]);
		} }];

	var data = album.json;

	if (data.photos !== false && data.photos.length > 0) {

		items.push({});

		items = items.concat(contextMenu.buildList(data.photos, [photoID], function (a) {
			return lychee.goto(albumID + '/' + a.id);
		}));
	}

	if (!album.isUploadable()) {
		// Remove Rename and the spacer.
		items.splice(0, 2);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoMore = function (photoID, e) {

	// Show download-item when
	// a) We are allowed to upload to the album
	// b) the photo is explicitly marked as downloadable (v4-only)
	// c) or, the album is explicitly marked as downloadable
	var showDownload = album.isUploadable() || (photo.json.hasOwnProperty('downloadable') ? photo.json.downloadable === '1' : album.json && album.json.downloadable && album.json.downloadable === '1');
	var showFull = photo.json.url && photo.json.url !== '';

	var items = [{ title: build.iconic('fullscreen-enter') + lychee.locale['FULL_PHOTO'], visible: !!showFull, fn: function fn() {
			return window.open(photo.getDirectLink());
		} }, { title: build.iconic('cloud-download') + lychee.locale['DOWNLOAD'], visible: !!showDownload, fn: function fn() {
			return photo.getArchive([photoID]);
		} }];

	basicContext.show(items, e.originalEvent);
};

contextMenu.getSubIDs = function (albums, albumID) {

	var ids = [parseInt(albumID, 10)];
	var a = void 0,
	    id = void 0;

	for (a = 0; a < albums.length; a++) {
		if (parseInt(albums[a].parent_id, 10) === parseInt(albumID, 10)) {
			ids = ids.concat(contextMenu.getSubIDs(albums, albums[a].id));
		}

		if (albums[a].albums && albums[a].albums.length > 0) {
			ids = ids.concat(contextMenu.getSubIDs(albums[a].albums, albumID));
		}
	}

	return ids;
};

contextMenu.move = function (IDs, e, callback) {
	var kind = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'UNSORTED';
	var display_root = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;


	var items = [];

	api.post('Albums::get', {}, function (data) {

		addItems = function addItems(albums) {

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
				if (callback !== album.merge && callback !== photo.copyTo) {
					exclude.push(album.getID().toString());
				}
				if (IDs.length === 1 && IDs[0] === album.getID() && album.getParent() && callback === album.setAlbum) {
					// If moving the current album, exclude its parent.
					exclude.push(album.getParent().toString());
				}
			} else if (visible.photo()) {
				exclude.push(photo.json.album.toString());
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
		if (display_root && album.getID() !== '0' && !visible.albums()) {

			items.unshift({});
			items.unshift({ title: lychee.locale[kind], fn: function fn() {
					return callback(IDs, 0);
				} });
		}

		// Don't allow to move the current album to a newly created subalbum
		// (creating a cycle).
		if (IDs.length !== 1 || IDs[0] !== (album.json ? album.json.id : null) || callback !== album.setAlbum) {
			items.unshift({});
			items.unshift({ title: lychee.locale['NEW_ALBUM'], fn: function fn() {
					return album.add(IDs, callback);
				} });
		}

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.sharePhoto = function (photoID, e) {

	// v4+ only
	if (photo.json.hasOwnProperty('share_button_visible') && photo.json.share_button_visible !== '1') {
		return;
	}

	var iconClass = 'ionicons';

	var items = [{ title: build.iconic('twitter', iconClass) + 'Twitter', fn: function fn() {
			return photo.share(photoID, 'twitter');
		} }, { title: build.iconic('facebook', iconClass) + 'Facebook', fn: function fn() {
			return photo.share(photoID, 'facebook');
		} }, { title: build.iconic('envelope-closed') + 'Mail', fn: function fn() {
			return photo.share(photoID, 'mail');
		} }, { title: build.iconic('dropbox', iconClass) + 'Dropbox', visible: lychee.admin === true, fn: function fn() {
			return photo.share(photoID, 'dropbox');
		} }, { title: build.iconic('link-intact') + lychee.locale['DIRECT_LINKS'], fn: function fn() {
			return photo.showDirectLinks(photoID);
		} }];

	basicContext.show(items, e.originalEvent);
};

contextMenu.shareAlbum = function (albumID, e) {

	// v4+ only
	if (album.json.hasOwnProperty('share_button_visible') && album.json.share_button_visible !== '1') {
		return;
	}

	var iconClass = 'ionicons';

	var items = [{ title: build.iconic('twitter', iconClass) + 'Twitter', fn: function fn() {
			return album.share('twitter');
		} }, { title: build.iconic('facebook', iconClass) + 'Facebook', fn: function fn() {
			return album.share('facebook');
		} }, { title: build.iconic('envelope-closed') + 'Mail', fn: function fn() {
			return album.share('mail');
		} }, { title: build.iconic('link-intact') + lychee.locale['DIRECT_LINK'], fn: function fn() {
			if (lychee.clipboardCopy(location.href)) loadingBar.show('success', lychee.locale['URL_COPIED_TO_CLIPBOARD']);
		} }];

	basicContext.show(items, e.originalEvent);
};

contextMenu.close = function () {

	if (!visible.contextMenu()) return false;

	basicContext.close();

	multiselect.clearSelection();
	if (visible.multiselect()) {
		multiselect.close();
	}
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
	var html = lychee.html(_templateObject40, title, build.iconic('caret-bottom'));

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
			if (lychee.public_search) {
				$('.header__search, .header__clear', '.header__toolbar--public').show();
			} else {
				$('.header__search, .header__clear', '.header__toolbar--public').hide();
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				$('.button--map-albums', '.header__toolbar--public').show();
			} else {
				$('.button--map-albums', '.header__toolbar--public').hide();
			}

			return true;

		case 'albums':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--albums').addClass('header__toolbar--visible');

			// If map is disabled, we should hide the icon
			if (lychee.map_display) {
				$('.button--map-albums', '.header__toolbar--albums').show();
			} else {
				$('.button--map-albums', '.header__toolbar--albums').hide();
			}

			return true;

		case 'album':

			var albumID = album.getID();

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--album').addClass('header__toolbar--visible');

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos === false || !album.isUploadable() && album.json.downloadable === '0') {
				$('#button_archive').hide();
			} else {
				$('#button_archive').show();
			}

			if (album.json && album.json.hasOwnProperty('share_button_visible') && album.json.share_button_visible !== '1') {
				$('#button_share_album').hide();
			} else {
				$('#button_share_album').show();
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				$('#button_map_album').show();
			} else {
				$('#button_map_album').hide();
			}

			if (albumID === 's' || albumID === 'f' || albumID === 'r') {
				$('#button_info_album, #button_trash_album, #button_visibility_album, #button_move_album').hide();
				$('.button_add, .header__divider', '.header__toolbar--album').show();
			} else if (albumID === '0') {
				$('#button_info_album, #button_visibility_album, #button_move_album').hide();
				$('#button_trash_album, .button_add, .header__divider', '.header__toolbar--album').show();
			} else {
				$('#button_info_album, #button_visibility_album').show();
				if (album.isUploadable()) {
					$('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album').show();
				} else {
					$('#button_trash_album, #button_move_album, #button_visibility_album, .button_add, .header__divider', '.header__toolbar--album').hide();
				}
			}

			return true;

		case 'photo':

			header.dom().addClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--photo').addClass('header__toolbar--visible');

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				$('#button_map').show();
			} else {
				$('#button_map').hide();
			}

			if (album.isUploadable()) {
				$('#button_trash, #button_move, #button_visibility, #button_star').show();
			} else {
				$('#button_trash, #button_move, #button_visibility, #button_star').hide();
			}

			if (photo.json && photo.json.hasOwnProperty('share_button_visible') && photo.json.share_button_visible !== '1') {
				$('#button_share').hide();
			} else {
				$('#button_share').show();
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$('#button_more').show();
			if (!(album.isUploadable() || (photo.json.hasOwnProperty('downloadable') ? photo.json.downloadable === '1' : album.json && album.json.downloadable && album.json.downloadable === '1')) && !(photo.json.url && photo.json.url !== '')) {
				$('#button_more').hide();
			}

			return true;
		case 'map':

			header.dom().removeClass('header--view');
			header.dom('.header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--map').addClass('header__toolbar--visible');

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
 * @description This module is used for bindings.
 */

$(document).ready(function () {

	// Event Name
	var eventName = lychee.getEventName();

	// set CSRF protection (Laravel)
	csrf.bind();

	// Set API error handler
	api.onError = lychee.error;

	$('html').css('visibility', 'visible');

	// Multiselect
	multiselect.bind();

	// Header
	header.bind();

	// Image View
	lychee.imageview.on(eventName, '.arrow_wrapper--previous', photo.previous).on(eventName, '.arrow_wrapper--next', photo.next).on('click', 'img, #livephoto', photo.update_display_overlay);

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
		if (!visible.photo() && album.isUploadable()) {
			$('#upload_files').click();return false;
		}
	}).bind(['n'], function () {
		if (!visible.photo() && album.isUploadable()) {
			album.add();return false;
		}
	}).bind(['s'], function () {
		if (visible.photo() && album.isUploadable()) {
			header.dom('#button_star').click();return false;
		} else if (visible.albums()) {
			header.dom('.header__search').focus();return false;
		}
	}).bind(['r'], function () {
		if (album.isUploadable()) {
			if (visible.album()) {
				album.setTitle(album.getID());return false;
			} else if (visible.photo()) {
				photo.setTitle([photo.getID()]);return false;
			}
		}
	}).bind(['d'], function () {
		if (album.isUploadable()) {
			if (visible.photo()) {
				photo.setDescription(photo.getID());return false;
			} else if (visible.album()) {
				album.setDescription(album.getID());return false;
			}
		}
	}).bind(['t'], function () {
		if (visible.photo() && album.isUploadable()) {
			photo.editTags([photo.getID()]);return false;
		}
	}).bind(['i'], function () {
		if (!visible.multiselect()) {
			sidebar.toggle();return false;
		}
	}).bind(['command+backspace', 'ctrl+backspace'], function () {
		if (album.isUploadable()) {
			if (visible.photo() && basicModal.visible() === false) {
				photo.delete([photo.getID()]);return false;
			} else if (visible.album() && basicModal.visible() === false) {
				album.delete([album.getID()]);return false;
			}
		}
	}).bind(['command+a', 'ctrl+a'], function () {
		if (visible.album() && basicModal.visible() === false) {
			multiselect.selectAll();return false;
		} else if (visible.albums() && basicModal.visible() === false) {
			multiselect.selectAll();return false;
		}
	}).bind(['o'], function () {
		if (visible.photo()) {
			photo.update_overlay_type();return false;
		}
	}).bind(['f'], function () {
		if (visible.album() || visible.photo()) {
			lychee.fullscreenToggle();return false;
		}
	});

	Mousetrap.bindGlobal('enter', function () {
		if (basicModal.visible() === true) basicModal.action();
	});

	Mousetrap.bindGlobal(['esc', 'command+up'], function () {
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album() && !album.json.parent_id) lychee.goto();else if (visible.album()) lychee.goto(album.getParent());else if (visible.albums() && search.hash !== null) search.reset();else if (visible.mapview()) mapview.close();
		return false;
	});

	if (eventName === 'touchend') {

		$(document)

		// Fullscreen on mobile
		.on('touchend', '#imageview #image', function (e) {
			if (swipe.obj == null || swipe.offsetX >= -5 && swipe.offsetX <= 5) {
				if (visible.header()) header.hide(e);else header.show();
			}
		});
		$('#imageview')
		// Swipe on mobile
		.swipe().on('swipeStart', function () {
			if (visible.photo()) swipe.start($('#imageview #image, #imageview #livephoto'));
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

	// Upload
	.on('change', '#upload_files', function () {
		basicModal.close();upload.start.local(this.files);
	})

	// Drag and Drop upload
	.on('dragover', function () {
		return false;
	}, false).on('drop', function (e) {

		if (!album.isUploadable() || visible.contextMenu() || basicModal.visible() || visible.leftMenu() || !(visible.album() || visible.albums())) {
			return false;
		}

		// Detect if dropped item is a file or a link
		if (e.originalEvent.dataTransfer.files.length > 0) upload.start.local(e.originalEvent.dataTransfer.files);else if (e.originalEvent.dataTransfer.getData('Text').length > 3) upload.start.url(e.originalEvent.dataTransfer.getData('Text'));

		return false;
	})

	// click on thumbnail on map
	.on('click', '.image-leaflet-popup', function (e) {
		mapview.goto($(this));
	})

	// Paste upload
	.on('paste', function (e) {
		if (e.originalEvent.clipboardData.items) {
			var items = e.originalEvent.clipboardData.items;
			var filesToUpload = [];

			// Search clipboard items for an image
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf('image') !== -1 || items[i].type.indexOf('video') !== -1) {
					filesToUpload.push(items[i].getAsFile());
				}
			}

			if (filesToUpload.length > 0) {
				// We perform the check so deep because we don't want to
				// prevent the paste from working in text input fields, etc.
				if (album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && (visible.album() || visible.albums())) {
					upload.start.local(filesToUpload);
				}

				return false;
			}
		}
	})

	// Fullscreen
	.on('fullscreenchange mozfullscreenchange webkitfullscreenchange msfullscreenchange', lychee.fullscreenUpdate);

	$(window)
	// resize
	.on('resize', function () {
		if (visible.album() || visible.search()) view.album.content.justify();
		if (visible.photo()) view.photo.onresize();
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
	var html = lychee.html(_templateObject41, lychee.locale['CLOSE'], lychee.locale['SETTINGS']);
	if (lychee.api_V2) {
		html += lychee.html(_templateObject42, build.iconic('person'), lychee.locale['USERS'], build.iconic('cloud'), lychee.locale['SHARING']);
	}
	html += lychee.html(_templateObject43, build.iconic('align-left'), lychee.locale['LOGS'], build.iconic('wrench'), lychee.locale['DIAGNOSTICS'], build.iconic('info'), lychee.locale['ABOUT_LYCHEE'], build.iconic('account-logout'), lychee.locale['SIGN_OUT']);
	if (lychee.api_V2 && lychee.update_available) {
		html += lychee.html(_templateObject44, build.iconic('timer'), lychee.locale['UPDATE_AVAILABLE']);
	}
	leftMenu._dom.html(html);
};

/* Set the width of the side navigation to 250px and the left margin of the page content to 250px */
leftMenu.open = function () {
	leftMenu._dom.addClass('leftMenu__visible');
	lychee.content.addClass('leftMenu__open');
	lychee.footer.addClass('leftMenu__open');
	header.dom('.header__title').addClass('leftMenu__open');
	loadingBar.dom().addClass('leftMenu__open');

	multiselect.unbind();
};

/* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
leftMenu.close = function () {
	leftMenu._dom.removeClass('leftMenu__visible');
	lychee.content.removeClass('leftMenu__open');
	lychee.footer.removeClass('leftMenu__open');
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
	leftMenu.dom('#text_settings_close').on(eventName, leftMenu.close);
	leftMenu.dom('#button_settings_open').on(eventName, settings.open);
	leftMenu.dom('#button_signout').on(eventName, lychee.logout);
	leftMenu.dom('#button_logs').on(eventName, leftMenu.Logs);
	leftMenu.dom('#button_diagnostics').on(eventName, leftMenu.Diagnostics);
	leftMenu.dom('#button_about').on(eventName, lychee.aboutDialog);

	if (lychee.api_V2) {
		leftMenu.dom('#button_users').on(eventName, leftMenu.Users);
		leftMenu.dom('#button_sharing').on(eventName, leftMenu.Sharing);
		leftMenu.dom('#button_update').on(eventName, leftMenu.Update);
	}

	return true;
};

leftMenu.Logs = function () {
	if (lychee.api_V2) {
		view.logs.init();
	} else {
		window.open(lychee.logs());
	}
};

leftMenu.Diagnostics = function () {
	if (lychee.api_V2) {
		view.diagnostics.init();
	} else {
		window.open(lychee.diagnostics());
	}
};

leftMenu.Update = function () {
	view.update.init();
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

		// Also move down the dark background
		if (basicModal.visible()) {
			$('.basicModalContainer').addClass('basicModalContainer--error');
			$('.basicModal').addClass('basicModal--error');
		}

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

		// Also move down the dark background
		if (basicModal.visible()) {
			$('.basicModalContainer').addClass('basicModalContainer--error');
			$('.basicModal').addClass('basicModal--error');
		}

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
		// Also move up the dark background
		$('.basicModalContainer').removeClass('basicModalContainer--error');
		$('.basicModal').removeClass('basicModal--error');

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
	version: '',
	versionCode: '', // not really needed anymore

	updatePath: 'https://LycheeOrg.github.io/update.json',
	updateURL: 'https://github.com/LycheeOrg/Lychee/releases',
	website: 'https://LycheeOrg.github.io',

	publicMode: false,
	viewMode: false,
	full_photo: true,
	downloadable: false,
	share_button_visible: false, // enable only v4+
	api_V2: false, // enable api_V2
	sub_albums: false, // enable sub_albums features
	admin: false, // enable admin mode (multi-user)
	upload: false, // enable possibility to upload (multi-user)
	lock: false, // locked user (multi-user)
	username: null,
	layout: '1', // 0: Use default, "square" layout. 1: Use Flickr-like "justified" layout. 2: Use Google-like "unjustified" layout
	public_search: false, // display Search in publicMode
	image_overlay: false, // display Overlay like in Lightroom
	image_overlay_default: false, // display Overlay like in Lightroom by default
	image_overlay_type: 'exif', // current Overlay display type
	image_overlay_type_default: 'exif', // image overlay type default type
	map_display: false, // display photo coordinates on map
	map_display_public: false, // display photos of public album on map (user not logged in)
	map_provider: 'Wikimedia', // Provider of OSM Tiles
	map_include_subalbums: false, // include photos of subalbums on map
	landing_page_enabled: false, // is landing page enabled ?
	delete_imported: false,

	checkForUpdates: '1',
	update_json: 0,
	update_available: false,
	sortingPhotos: '',
	sortingAlbums: '',
	location: '',

	lang: '',
	lang_available: {},

	dropbox: false,
	dropboxKey: '',

	content: $('.content'),
	imageview: $('#imageview'),
	footer: $('#footer'),

	locale: {}
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

lychee.aboutDialog = function () {

	var msg = lychee.html(_templateObject45, lychee.version, lychee.updateURL, lychee.locale['UPDATE_AVAILABLE'], lychee.locale['ABOUT_SUBTITLE'], lychee.website, lychee.locale['ABOUT_DESCRIPTION']);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale['CLOSE'],
				fn: basicModal.close
			}
		}
	});

	if (lychee.checkForUpdates === '1') lychee.getUpdate();
};

lychee.init = function () {

	lychee.adjustContentHeight();

	api.post('Session::init', {}, function (data) {

		lychee.api_V2 = data.api_V2 || false;

		if (data.status === 0) {

			// No configuration

			lychee.setMode('public');

			header.dom().hide();
			lychee.content.hide();
			$('body').append(build.no_content('cog'));
			settings.createConfig();

			return true;
		}

		lychee.sub_albums = data.sub_albums || false;
		lychee.update_json = data.update_json;
		lychee.update_available = data.update_available;
		lychee.landing_page_enable = data.config.landing_page_enable && data.config.landing_page_enable === '1' || false;

		if (lychee.api_V2) {
			lychee.versionCode = data.config.version;
		} else {
			lychee.versionCode = data.config.version.slice(7, data.config.version.length);
		}
		if (lychee.versionCode !== '') {
			var digits = lychee.versionCode.match(/.{1,2}/g);
			lychee.version = parseInt(digits[0]).toString() + '.' + parseInt(digits[1]).toString() + '.' + parseInt(digits[2]).toString();
		}

		// we copy the locale that exists only.
		// This ensure forward and backward compatibility.
		// e.g. if the front localization is unfished in a language
		// or if we need to change some locale string
		for (var key in data.locale) {
			lychee.locale[key] = data.locale[key];
		}

		if (!lychee.api_V2) {
			// Apply translations to the header
			header.applyTranslations();
		}

		// Check status
		// 0 = No configuration
		// 1 = Logged out
		// 2 = Logged in
		if (data.status === 2) {

			// Logged in

			lychee.sortingPhotos = data.config.sorting_Photos || data.config.sortingPhotos || '';
			lychee.sortingAlbums = data.config.sorting_Albums || data.config.sortingAlbums || '';
			lychee.dropboxKey = data.config.dropbox_key || data.config.dropboxKey || '';
			lychee.location = data.config.location || '';
			lychee.checkForUpdates = data.config.check_for_updates || data.config.checkForUpdates || '1';
			lychee.lang = data.config.lang || '';
			lychee.lang_available = data.config.lang_available || {};
			lychee.layout = data.config.layout || '1';
			lychee.public_search = data.config.public_search && data.config.public_search === '1' || false;
			lychee.image_overlay_default = data.config.image_overlay && data.config.image_overlay === '1' || false;
			lychee.image_overlay = lychee.image_overlay_default;
			lychee.image_overlay_type = !data.config.image_overlay_type ? 'exif' : data.config.image_overlay_type;
			lychee.image_overlay_type_default = lychee.image_overlay_type;
			lychee.map_display = data.config.map_display && data.config.map_display === '1' || false;
			lychee.map_display_public = data.config.map_display_public && data.config.map_display_public === '1' || false;
			lychee.map_provider = !data.config.map_provider ? 'Wikimedia' : data.config.map_provider;
			lychee.map_include_subalbums = data.config.map_include_subalbums && data.config.map_include_subalbums === '1' || false;
			lychee.default_license = data.config.default_license || 'none';
			lychee.css = data.config.css || '';
			lychee.full_photo = data.config.full_photo == null || data.config.full_photo === '1';
			lychee.downloadable = data.config.downloadable && data.config.downloadable === '1' || false;
			lychee.share_button_visible = data.config.share_button_visible && data.config.share_button_visible === '1' || false;
			lychee.delete_imported = data.config.delete_imported && data.config.delete_imported === '1';

			lychee.upload = !lychee.api_V2;
			lychee.admin = !lychee.api_V2;

			// leftMenu
			leftMenu.build();
			leftMenu.bind();

			if (lychee.api_V2) {
				lychee.upload = data.admin || data.upload;
				lychee.admin = data.admin;
				lychee.lock = data.lock;
				lychee.username = data.username;
			}
			lychee.setMode('logged_in');

			// Show dialog when there is no username and password
			if (data.config.login === false) settings.createLogin();
		} else if (data.status === 1) {

			// Logged out

			// TODO remove sortingPhoto once the v4 is out
			lychee.sortingPhotos = data.config.sorting_Photos || data.config.sortingPhotos || '';
			lychee.sortingAlbums = data.config.sorting_Albums || data.config.sortingAlbums || '';
			lychee.checkForUpdates = data.config.check_for_updates || data.config.checkForUpdates || '1';
			lychee.layout = data.config.layout || '1';
			lychee.public_search = data.config.public_search && data.config.public_search === '1' || false;
			lychee.image_overlay = data.config.image_overlay && data.config.image_overlay === '1' || false;
			lychee.image_overlay_type = !data.config.image_overlay_type ? 'exif' : data.config.image_overlay_type;
			lychee.image_overlay_type_default = lychee.image_overlay_type;
			lychee.map_display = data.config.map_display && data.config.map_display === '1' || false;
			lychee.map_display_public = data.config.map_display_public && data.config.map_display_public === '1' || false;
			lychee.map_provider = !data.config.map_provider ? 'Wikimedia' : data.config.map_provider;
			lychee.map_include_subalbums = data.config.map_include_subalbums && data.config.map_include_subalbums === '1' || false;

			// console.log(lychee.full_photo);
			lychee.setMode('public');
		} else {
			// should not happen.
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

	var msg = lychee.html(_templateObject46, lychee.locale['USERNAME'], lychee.locale['PASSWORD'], lychee.version, lychee.updateURL, lychee.locale['UPDATE_AVAILABLE']);

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
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;


	if (url === false) url = '';

	url = '#' + url;

	history.pushState(null, null, url);
	lychee.load(autoplay);
};

lychee.gotoMap = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;


	// If map functionality is disabled -> go to album
	if (!lychee.map_display) {
		loadingBar.show('error', lychee.locale['ERROR_MAP_DEACTIVATED']);
		return;
	}
	lychee.goto('map/' + albumID, autoplay);
};

lychee.load = function () {
	var autoplay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;


	var albumID = '';
	var photoID = '';
	var hash = document.location.hash.replace('#', '').split('/');

	contextMenu.close();
	multiselect.close();

	if (hash[0] != null) albumID = hash[0];
	if (hash[1] != null) photoID = hash[1];

	if (albumID && photoID) {

		if (albumID == 'map') {

			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show('error', lychee.locale['ERROR_MAP_DEACTIVATED']);
				return;
			}
			$('.no_content').remove();
			// show map
			// albumID has been stored in photoID due to URL format #map/albumID
			albumID = photoID;

			// Trash data
			photo.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) sidebar.toggle();
			if (album.json && albumID === album.json.id) {
				view.album.title();
			}
			mapview.open(albumID);
			lychee.footer_hide();
		} else if (albumID == 'search') {

			// Search has been triggered
			search_string = decodeURIComponent(photoID);

			if (search_string.trim() === "") {
				// do nothing on "only space" search strings
				return;
			}
			// If public search is diabled -> do nothing
			if (lychee.publicMode === true && !lychee.public_search) {
				loadingBar.show('error', lychee.locale['ERROR_SEARCH_DEACTIVATED']);
				return;
			}

			header.dom('.header__search').val(search_string);
			search.find(search_string);

			lychee.footer_show();
		} else {
			$('.no_content').remove();
			// Show photo

			// Trash data
			photo.json = null;

			// Show Photo
			if (lychee.content.html() === '' || album.json == null || header.dom('.header__search').length && header.dom('.header__search').val().length !== 0) {
				lychee.content.hide();
				album.load(albumID, true);
			}
			photo.load(photoID, albumID, autoplay);
			lychee.footer_hide();
		}
	} else if (albumID) {

		if (albumID == 'map') {

			$('.no_content').remove();
			// Show map of all albums
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show('error', lychee.locale['ERROR_MAP_DEACTIVATED']);
				return;
			}

			// Trash data
			photo.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) sidebar.toggle();
			mapview.open();
			lychee.footer_hide();
		} else if (albumID == 'search') {
			// search string is empty -> do nothing
		} else {

			$('.no_content').remove();
			// Trash data
			photo.json = null;

			// Show Album
			if (visible.photo()) view.photo.hide();
			if (visible.mapview()) mapview.close();
			if (visible.sidebar() && (albumID === '0' || albumID === 'f' || albumID === 's' || albumID === 'r')) sidebar.toggle();
			if (album.json && albumID === album.json.id) view.album.title();else album.load(albumID);
			lychee.footer_show();
		}
	} else {

		$('.no_content').remove();
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
		if (visible.mapview()) mapview.close();
		lychee.content.show();
		lychee.footer_show();
		albums.load();
	}
};

lychee.getUpdate = function () {

	// console.log(lychee.update_available);
	// console.log(lychee.update_json);

	if (lychee.update_json !== 0) {
		if (lychee.update_available) {
			$('.version span').show();
		}
	} else {
		var success = function success(data) {
			if (data.lychee.version > parseInt(lychee.versionCode)) $('.version span').show();
		};

		$.ajax({
			url: lychee.updatePath,
			success: success
		});
	}
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
		$('#button_sharing').remove();

		$(document).off('click', '.header__title--editable').off('touchend', '.header__title--editable').off('contextmenu', '.photo').off('contextmenu', '.album').off('drop');

		Mousetrap.unbind(['u']).unbind(['s']).unbind(['n']).unbind(['r']).unbind(['d']).unbind(['t']).unbind(['command+backspace', 'ctrl+backspace']).unbind(['command+a', 'ctrl+a']);
	}
	if (!lychee.admin) {
		$('#button_users, #button_logs, #button_diagnostics').remove();
	}

	if (mode === 'logged_in') {
		// The code searches by class, so remove the other instance.
		$('.header__search, .header__clear', '.header__toolbar--public').remove();
		return;
	} else {
		$('.header__search, .header__clear', '.header__toolbar--albums').remove();
	}

	$('#button_settings, .header__divider, .leftMenu').remove();

	if (mode === 'public') {

		lychee.publicMode = true;
	} else if (mode === 'view') {

		Mousetrap.unbind(['esc', 'command+up']);

		$('#button_back, a#next, a#previous').remove();
		$('.no_content').remove();

		lychee.publicMode = true;
		lychee.viewMode = true;
	}

	// just mak
	header.bind_back();
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


	loadingBar.show('error', errorThrown);

	if (errorThrown === 'Session timed out.') {
		setTimeout(function () {
			lychee.goto();
			window.location.reload();
		}, 3000);
	} else {
		console.error({
			description: errorThrown,
			params: params,
			response: data
		});
	}
};

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

lychee.fullscreenToggle = function () {
	if (lychee.fullscreenStatus()) {
		lychee.fullscreenExit();
	} else {
		lychee.fullscreenEnter();
	}
};

lychee.fullscreenStatus = function () {
	var elem = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
	return elem ? true : false;
};

lychee.fullscreenUpdate = function () {
	if (lychee.fullscreenStatus()) {
		$('#button_fs_album_enter,#button_fs_enter').hide();
		$('#button_fs_album_exit,#button_fs_exit').show();
	} else {
		$('#button_fs_album_enter,#button_fs_enter').show();
		$('#button_fs_album_exit,#button_fs_exit').hide();
	}
};

lychee.footer_show = function () {
	setTimeout(function () {
		lychee.footer.removeClass('hide_footer');
	}, 200);
};

lychee.footer_hide = function () {
	lychee.footer.addClass('hide_footer');
};

// Because the height of the footer can vary, we need to set some
// dimensions dynamically, at startup.
lychee.adjustContentHeight = function () {
	if (lychee.footer.length > 0) {
		lychee.content.css('min-height', 'calc(100vh - ' + lychee.content.css('padding-top') + ' - ' + lychee.content.css('padding-bottom') + ' - ' + lychee.footer.outerHeight() + 'px)');
		$('#container').css('padding-bottom', lychee.footer.outerHeight());
	} else {
		lychee.content.css('min-height', 'calc(100vh - ' + lychee.content.css('padding-top') + ' - ' + lychee.content.css('padding-bottom') + ')');
	}
};

lychee.getBaseUrl = function () {
	if (location.href.indexOf('index.html') > 0) {
		return location.href.replace('index.html' + location.hash, '');
	} else if (location.href.indexOf('gallery#') > 0) {
		return location.href.replace('gallery' + location.hash, '');
	} else {
		return location.href.replace(location.hash, '');
	}
};

// Copied from https://github.com/feross/clipboard-copy/blob/9eba597c774feed48301fef689099599d612387c/index.js
lychee.clipboardCopy = function (text) {

	// Use the Async Clipboard API when available. Requires a secure browsing
	// context (i.e. HTTPS)
	if (navigator.clipboard) {
		return navigator.clipboard.writeText(text).catch(function (err) {
			throw err !== undefined ? err : new DOMException('The request is not allowed', 'NotAllowedError');
		});
	}

	// ...Otherwise, use document.execCommand() fallback

	// Put the text to copy into a <span>
	var span = document.createElement('span');
	span.textContent = text;

	// Preserve consecutive spaces and newlines
	span.style.whiteSpace = 'pre';

	// Add the <span> to the page
	document.body.appendChild(span);

	// Make a selection object representing the range of text selected by the user
	var selection = window.getSelection();
	var range = window.document.createRange();
	selection.removeAllRanges();
	range.selectNode(span);
	selection.addRange(range);

	// Copy text to the clipboard
	var success = false;

	try {
		success = window.document.execCommand('copy');
	} catch (err) {
		console.log('error', err);
	}

	// Cleanup
	selection.removeAllRanges();
	window.document.body.removeChild(span);

	return success;
	// ? Promise.resolve()
	// : Promise.reject(new DOMException('The request is not allowed', 'NotAllowedError'))
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
	'SHARING': 'Sharing',
	'CHANGE_LOGIN': 'Change Login',
	'CHANGE_SORTING': 'Change Sorting',
	'SET_DROPBOX': 'Set Dropbox',
	'ABOUT_LYCHEE': 'About Lychee',
	'DIAGNOSTICS': 'Diagnostics',
	'LOGS': 'Show Logs',
	'CLEAN_LOGS': 'Clean Noise',
	'SIGN_OUT': 'Sign Out',
	'UPDATE_AVAILABLE': 'Update available!',
	'CHECK_FOR_UPDATE': 'Check for updates',
	'DEFAULT_LICENSE': 'Default License for new uploads:',
	'SET_LICENSE': 'Set License',
	'SET_OVERLAY_TYPE': 'Set Overlay',
	'SET_MAP_PROVIDER': 'Set OpenStreetMap tiles provider',
	'SAVE_RISK': 'Save my modifications, I accept the Risk!',
	'MORE': 'More',

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

	'TITLE_NEW_ALBUM': 'Enter a title for the new album:',
	'UNTITLED': 'Untilted',
	'UNSORTED': 'Unsorted',
	'STARRED': 'Starred',
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
	'ALBUM_NEW_DESCRIPTION': 'Enter a new description for this album:',
	'ALBUM_SET_DESCRIPTION': 'Set Description',
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
 * @description This module takes care of the map view of a full album and its sub-albums.
 */

map_provider_layer_attribution = {
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

mapview = {
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
	updateZoom = function updateZoom() {
		if (mapview.min_lat && mapview.min_lng && mapview.max_lat && mapview.max_lng) {
			var dist_lat = mapview.max_lat - mapview.min_lat;
			var dist_lng = mapview.max_lng - mapview.min_lng;
			mapview.map.fitBounds([[mapview.min_lat - 0.1 * dist_lat, mapview.min_lng - 0.1 * dist_lng], [mapview.max_lat + 0.1 * dist_lat, mapview.max_lng + 0.1 * dist_lng]]);
		} else {
			mapview.map.fitWorld();
		}
	};

	// Adds photos to the map
	addPhotosToMap = function addPhotosToMap(album) {

		// check if empty
		if (!album.photos) return;

		photos = [];

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
	getAlbumData = function getAlbumData(_albumID) {
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

						api.post('Album::getPositionData', _params, function (data) {
							addPhotosToMap(data);
							mapview.title(_albumID, data.title);
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

						api.post('Albums::getPositionData', _params2, function (data) {
							addPhotosToMap(data);
							mapview.title(_albumID, data.title);
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

/**
 * @description Select multiple albums or photos.
 */

var isSelectKeyPressed = function isSelectKeyPressed(e) {

	return e.metaKey || e.ctrlKey;
};

multiselect = {

	ids: [],
	albumsSelected: 0,
	photosSelected: 0,
	lastClicked: null

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

multiselect.unbind = function () {

	$('.content').off('mousedown');
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
	if (!lychee.admin && albums.isShared(id)) return;
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

	multiselect.lastClicked = object;
};

multiselect.removeItem = function (object, id) {
	var _multiselect$isSelect = multiselect.isSelected(id),
	    selected = _multiselect$isSelect.selected,
	    position = _multiselect$isSelect.position;

	if (selected === false) return;

	multiselect.ids.splice(position, 1);
	multiselect.deselect(object);

	var isAlbum = object.hasClass('album');

	if (isAlbum) {
		multiselect.albumsSelected--;
	} else {
		multiselect.photosSelected--;
	}

	multiselect.lastClicked = object;
};

multiselect.albumClick = function (e, albumObj) {

	var id = albumObj.attr('data-id');

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (albumObj.hasClass('disabled')) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(albumObj, id);
		} else {
			if (multiselect.albumsSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (albumObj.prevAll('.album').toArray().includes(multiselect.lastClicked[0])) {
					albumObj.prevUntil(multiselect.lastClicked, '.album').each(function () {
						multiselect.addItem($(this), $(this).attr('data-id'));
					});
				} else if (albumObj.nextAll('.album').toArray().includes(multiselect.lastClicked[0])) {
					albumObj.nextUntil(multiselect.lastClicked, '.album').each(function () {
						multiselect.addItem($(this), $(this).attr('data-id'));
					});
				}
			}

			multiselect.addItem(albumObj, id);
		}
	} else {
		lychee.goto(id);
	}
};

multiselect.photoClick = function (e, photoObj) {

	var id = photoObj.attr('data-id');

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (photoObj.hasClass('disabled')) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(photoObj, id);
		} else {
			if (multiselect.photosSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (photoObj.prevAll('.photo').toArray().includes(multiselect.lastClicked[0])) {
					photoObj.prevUntil(multiselect.lastClicked, '.photo').each(function () {
						multiselect.addItem($(this), $(this).attr('data-id'));
					});
				} else if (photoObj.nextAll('.photo').toArray().includes(multiselect.lastClicked[0])) {
					photoObj.nextUntil(multiselect.lastClicked, '.photo').each(function () {
						multiselect.addItem($(this), $(this).attr('data-id'));
					});
				}
			}

			multiselect.addItem(photoObj, id);
		}
	} else {
		lychee.goto(album.getID() + '/' + id);
	}
};

multiselect.albumContextMenu = function (e, albumObj) {

	var id = albumObj.attr('data-id');
	var selected = multiselect.isSelected(id).selected;

	if (albumObj.hasClass('disabled')) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.albumMulti(multiselect.ids, e);
	} else {
		contextMenu.album(id, e);
	}
};

multiselect.photoContextMenu = function (e, photoObj) {

	var id = photoObj.attr('data-id');
	var selected = multiselect.isSelected(id).selected;

	if (photoObj.hasClass('disabled')) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.photoMulti(multiselect.ids, e);
	} else if (visible.album() || visible.search()) {
		contextMenu.photo(id, e);
	} else if (visible.photo()) {
		// should not happen... but you never know...
		contextMenu.photo(photo.getID(), e);
	} else {
		lychee.error('Could not find what you want.');
	}
};

multiselect.clearSelection = function () {

	multiselect.deselect('.photo.active, .album.active');
	multiselect.ids = [];
	multiselect.albumsSelected = 0;
	multiselect.photosSelected = 0;
	multiselect.lastClicked = null;
};

multiselect.show = function (e) {

	if (!album.isUploadable()) return false;
	if (!visible.albums() && !visible.album()) return false;
	if ($('.album:hover, .photo:hover').length !== 0) return false;
	if (visible.search()) return false;
	if (visible.multiselect()) $('#multiselect').remove();

	sidebar.setSelectable(false);

	if (!isSelectKeyPressed(e) && !e.shiftKey) {
		multiselect.clearSelection();
	}

	multiselect.position.top = e.pageY;
	multiselect.position.right = $(document).width() - e.pageX;
	multiselect.position.bottom = $(document).height() - e.pageY;
	multiselect.position.left = e.pageX;

	$('body').append(build.multiselect(multiselect.position.top, multiselect.position.left));

	$(document).on('mousemove', multiselect.resize).on('mouseup', function (e) {
		if (e.which === 1) multiselect.getSelection(e);
	});
};

multiselect.resize = function (e) {

	if (multiselect.position.top === null || multiselect.position.right === null || multiselect.position.bottom === null || multiselect.position.left === null) return false;

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
		newCSS.bottom = 'inherit';
		newCSS.height = Math.min(e.pageY, $(document).height() - 3) - multiselect.position.top;
	} else {

		newCSS.top = 'inherit';
		newCSS.bottom = multiselect.position.bottom;
		newCSS.height = multiselect.position.top - Math.max(e.pageY, 2);
	}

	if (e.pageX >= multiselect.position.left) {

		newCSS.right = 'inherit';
		newCSS.left = multiselect.position.left;
		newCSS.width = Math.min(e.pageX, $(document).width() - 3) - multiselect.position.left;
	} else {

		newCSS.right = multiselect.position.right;
		newCSS.left = 'inherit';
		newCSS.width = multiselect.position.left - Math.max(e.pageX, 2);
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
		width: parseFloat($elem.css('width'), 10),
		height: parseFloat($elem.css('height'), 10)
	};
};

multiselect.getSelection = function (e) {
	var size = multiselect.getSize();

	if (visible.contextMenu()) return false;
	if (!visible.multiselect()) return false;

	$('.photo, .album').each(function () {

		// We select if there's even a slightest overlap.  Overlap between
		// an object and the selection occurs if the left edge of the
		// object is to the left of the right edge of the selection *and*
		// the right edge of the object is to the right of the left edge of
		// the selection; analogous for top/bottom.
		if ($(this).offset().left < size.left + size.width && $(this).offset().left + $(this).width() > size.left && $(this).offset().top < size.top + size.height && $(this).offset().top + $(this).height() > size.top) {

			var id = $(this).attr('data-id');

			if (isSelectKeyPressed(e)) {
				multiselect.toggleItem($(this), id);
			} else {
				multiselect.addItem($(this), id);
			}
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

	if (!album.isUploadable()) return false;
	if (visible.search()) return false;
	if (!visible.albums() && !visible.album) return false;
	if (visible.multiselect()) $('#multiselect').remove();

	sidebar.setSelectable(false);

	multiselect.clearSelection();

	$('.photo').each(function () {
		multiselect.addItem($(this), $(this).attr('data-id'));
	});

	if (multiselect.photosSelected === 0) {
		// There are no pictures.  Try albums then.
		$('.album').each(function () {
			multiselect.addItem($(this), $(this).attr('data-id'));
		});
	}
};

/**
 * @description Controls the access to password-protected albums and photos.
 */

password = {

	value: ''

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
		if (!visible.albums() && !visible.album()) lychee.goto();
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
	cache: null,
	supportsPrefetch: null,
	LivePhotosObject: null

};

photo.getID = function () {

	var id = null;

	if (photo.json) id = photo.json.id;else id = $('.photo:hover, .photo.active').attr('data-id');

	if ($.isNumeric(id) === true) return id;else return false;
};

photo.load = function (photoID, albumID, autoplay) {

	var checkContent = function checkContent() {
		if (album.json != null && album.json.photos) photo.load(photoID, albumID, autoplay);else setTimeout(checkContent, 100);
	};

	var checkPasswd = function checkPasswd() {
		if (password.value !== '') photo.load(photoID, albumID, autoplay);else setTimeout(checkPasswd, 200);
	};

	// we need to check the album.json.photos because otherwise the script is too fast and this raise an error.
	if (album.json == null || album.json.photos == null) {
		checkContent();
		return false;
	}

	var params = {
		photoID: photoID,
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
		photo.json.original_album = photo.json.album;
		photo.json.album = albumID;

		if (!visible.photo()) view.photo.show();
		view.photo.init(autoplay);
		lychee.imageview.show();

		setTimeout(function () {
			lychee.content.show();
		}, 300);
	});
};

photo.hasExif = function () {
	var exifHash = photo.json.make + photo.json.model + photo.json.shutter + photo.json.aperture + photo.json.focal + photo.json.iso;

	return exifHash !== '';
};

photo.hasTakedate = function () {
	return photo.json.takedate && photo.json.takedate !== '';
};

photo.hasDesc = function () {
	return photo.json.description && photo.json.description !== '';
};

photo.isLivePhoto = function () {
	if (!photo.json) return false; // In case it's called, but not initialized
	return photo.json.livePhotoUrl && photo.json.livePhotoUrl !== '';
};

photo.isLivePhotoInitizalized = function () {
	return photo.LivePhotosObject !== null;
};

photo.isLivePhotoPlaying = function () {
	if (photo.isLivePhotoInitizalized() === false) return false;
	return photo.LivePhotosObject.isPlaying;
};

photo.update_overlay_type = function () {
	// Only run if the overlay is showing
	if (!lychee.image_overlay) {
		return false;
	} else {
		// console.log('Current ' + lychee.image_overlay_type);
		var types = ['exif', 'desc', 'takedate'];

		var i = types.indexOf(lychee.image_overlay_type);
		var j = (i + 1) % types.length;
		var cont = true;
		while (i !== j && cont) {
			if (types[j] === 'desc' && photo.hasDesc()) cont = false;else if (types[j] === 'takedate' && photo.hasTakedate()) cont = false;else if (types[j] === 'exif' && photo.hasExif()) cont = false;else j = (j + 1) % types.length;
		}

		if (i !== j) {
			lychee.image_overlay_type = types[j];
			$('#image_overlay').remove();
			lychee.imageview.append(build.overlay_image(photo.json));
		} else {
			// console.log('no other data found, displaying ' + types[j]);
		}
	}
};

photo.update_display_overlay = function () {
	lychee.image_overlay = !lychee.image_overlay;
	if (!lychee.image_overlay) {
		$('#image_overlay').remove();
	} else {
		lychee.imageview.append(build.overlay_image(photo.json));
	}
};

// Preload the next and previous photos for better response time
photo.preloadNextPrev = function (photoID) {
	if (album.json && album.json.photos && album.getByID(photoID)) {

		var previousPhotoID = album.getByID(photoID).previousPhoto;
		var nextPhotoID = album.getByID(photoID).nextPhoto;
		var current2x = null;

		$('head [data-prefetch]').remove();

		var preload = function preload(preloadID) {
			var preloadPhoto = album.getByID(preloadID);
			var href = '';

			if (preloadPhoto.medium != null && preloadPhoto.medium !== '') {
				href = preloadPhoto.medium;

				if (preloadPhoto.medium2x && preloadPhoto.medium2x !== '') {
					if (current2x === null) {
						var imgs = $('img#image');
						current2x = imgs.length > 0 && imgs[0].currentSrc !== null && imgs[0].currentSrc.includes('@2x.');
					}
					if (current2x) {
						// If the currently displayed image uses the 2x variant,
						// chances are that so will the next one.
						href = preloadPhoto.medium2x;
					}
				}
			} else if (preloadPhoto.type && preloadPhoto.type.indexOf('video') === -1) {
				// Preload the original size, but only if it's not a video
				href = preloadPhoto.url;
			}

			if (href !== '') {
				if (photo.supportsPrefetch === null) {
					// Copied from https://www.smashingmagazine.com/2016/02/preload-what-is-it-good-for/
					var DOMTokenListSupports = function DOMTokenListSupports(tokenList, token) {
						if (!tokenList || !tokenList.supports) {
							return null;
						}
						try {
							return tokenList.supports(token);
						} catch (e) {
							if (e instanceof TypeError) {
								console.log('The DOMTokenList doesn\'t have a supported tokens list');
							} else {
								console.error('That shouldn\'t have happened');
							}
						}
					};
					photo.supportsPrefetch = DOMTokenListSupports(document.createElement('link').relList, 'prefetch');
				}

				if (photo.supportsPrefetch) {
					$('head').append(lychee.html(_templateObject47, href));
				} else {
					// According to https://caniuse.com/#feat=link-rel-prefetch,
					// as of mid-2019 it's mainly Safari (both on desktop and mobile)
					new Image().src = href;
				}
			}
		};

		if (nextPhotoID && nextPhotoID !== '') {
			preload(nextPhotoID);
		}
		if (previousPhotoID && previousPhotoID !== '') {
			preload(previousPhotoID);
		}
	}
};

photo.parse = function () {

	if (!photo.json.title) photo.json.title = lychee.locale['UNTITLED'];
};

photo.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuraction = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (photo.isLivePhotoInitizalized()) {
			photo.LivePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuraction);
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
			photo.LivePhotosObject = null;
			lychee.goto(album.getID() + '/' + album.getByID(photo.getID()).previousPhoto, false);
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
			photo.LivePhotosObject = null;
			lychee.goto(album.getID() + '/' + album.getByID(photo.getID()).nextPhoto, false);
		}, delay);
	}
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

		var nextPhoto = '';
		var previousPhoto = '';

		basicModal.close();

		photoIDs.forEach(function (id, index) {

			// Change reference for the next and previous photo
			if (album.getByID(id).nextPhoto !== '' || album.getByID(id).previousPhoto !== '') {

				nextPhoto = album.getByID(id).nextPhoto;
				previousPhoto = album.getByID(id).previousPhoto;

				if (previousPhoto !== '') {
					album.getByID(previousPhoto).nextPhoto = nextPhoto;
				}
				if (nextPhoto !== '') {
					album.getByID(nextPhoto).previousPhoto = previousPhoto;
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
			if (nextPhoto !== '' && nextPhoto !== photo.getID()) {
				lychee.goto(album.getID() + '/' + nextPhoto);
			} else if (previousPhoto !== '' && previousPhoto !== photo.getID()) {
				lychee.goto(album.getID() + '/' + previousPhoto);
			} else {
				lychee.goto(album.getID());
			}
		} else if (!visible.albums()) {
			lychee.goto(album.getID());
		}

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

		msg = lychee.html(_templateObject48, lychee.locale['PHOTO_DELETE_1'], photoTitle, lychee.locale['PHOTO_DELETE_2']);
	} else {

		action.title = lychee.locale['PHOTO_DELETE'];
		cancel.title = lychee.locale['PHOTO_KEEP'];

		msg = lychee.html(_templateObject49, lychee.locale['PHOTO_DELETE_ALL_1'], photoIDs.length, lychee.locale['PHOTO_DELETE_ALL_2']);
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

		photoIDs.forEach(function (id) {
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

	var input = lychee.html(_templateObject50, oldTitle);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject3, lychee.locale['PHOTO_NEW_TITLE'], input);else msg = lychee.html(_templateObject51, lychee.locale['PHOTOS_NEW_TITLE_1'], photoIDs.length, lychee.locale['PHOTOS_NEW_TITLE_2'], input);

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

photo.copyTo = function (photoIDs, albumID) {

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	var params = {
		photoIDs: photoIDs.join(),
		albumID: albumID
	};

	api.post('Photo::duplicate', params, function (data) {

		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			if (lychee.api_V2 || albumID === album.getID()) {
				album.reload();
			} else {
				// Lychee v3 does not support the albumID argument to
				// Photo::duplicate so we need to do it manually, which is
				// imperfect, as it moves the source photos, not the duplicates.
				photo.setAlbum(photoIDs, albumID);
			}
		}
	});
};

photo.setAlbum = function (photoIDs, albumID) {

	var nextPhoto = '';
	var previousPhoto = '';

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	photoIDs.forEach(function (id, index) {

		// Change reference for the next and previous photo
		if (album.getByID(id).nextPhoto !== '' || album.getByID(id).previousPhoto !== '') {

			nextPhoto = album.getByID(id).nextPhoto;
			previousPhoto = album.getByID(id).previousPhoto;

			if (previousPhoto !== '') {
				album.getByID(previousPhoto).nextPhoto = nextPhoto;
			}
			if (nextPhoto !== '') {
				album.getByID(nextPhoto).previousPhoto = previousPhoto;
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
		if (nextPhoto !== '' && nextPhoto !== photo.getID()) {
			lychee.goto(album.getID() + '/' + nextPhoto);
		} else if (previousPhoto !== '' && previousPhoto !== photo.getID()) {
			lychee.goto(album.getID() + '/' + previousPhoto);
		} else {
			lychee.goto(album.getID());
		}
	} else if (!visible.albums()) {
		lychee.goto(album.getID());
	}

	var params = {
		photoIDs: photoIDs.join(),
		albumID: albumID
	};

	api.post('Photo::setAlbum', params, function (data) {

		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			// We only really need to do anything here if the destination
			// is a (possibly nested) subalbum of the current album; but
			// since we have no way of figuring it out (albums.json is
			// null), we need to reload.
			if (visible.album()) {
				album.reload();
			} else {
				// We're most likely in photo view.  We still need to
				// refresh the album but we don't want to reload it
				// since that would switch the view being displayed.
				album.refresh();
			}
		}
	});
};

photo.setStar = function (photoIDs) {

	if (!photoIDs) return false;

	if (visible.photo()) {
		photo.json.star = photo.json.star === '0' ? '1' : '0';
		view.photo.star();
	}

	photoIDs.forEach(function (id) {
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

	var msg_switch = lychee.html(_templateObject52, lychee.locale['PHOTO_PUBLIC'], lychee.locale['PHOTO_PUBLIC_EXPL']);

	var msg_choices = lychee.html(_templateObject53, build.iconic('check'), lychee.locale['PHOTO_FULL'], lychee.locale['PHOTO_FULL_EXPL'], build.iconic('check'), lychee.locale['PHOTO_HIDDEN'], lychee.locale['PHOTO_HIDDEN_EXPL'], build.iconic('check'), lychee.locale['PHOTO_DOWNLOADABLE'], lychee.locale['PHOTO_DOWNLOADABLE_EXPL'], build.iconic('check'), lychee.locale['PHOTO_SHARE_BUTTON_VISIBLE'], lychee.locale['PHOTO_SHARE_BUTTON_VISIBLE_EXPL'], build.iconic('check'), lychee.locale['PHOTO_PASSWORD_PROT'], lychee.locale['PHOTO_PASSWORD_PROT_EXPL']);

	if (photo.json.public === '2') {
		// Public album. We can't actually change anything but we will
		// display the current settings.

		var msg = lychee.html(_templateObject54, lychee.locale['PHOTO_NO_EDIT_SHARING_TEXT'], msg_switch, msg_choices);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale['CLOSE'],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').prop('checked', true);
		if (album.json) {
			if (album.json.full_photo !== null && album.json.full_photo === '1') {
				$('.basicModal .choice input[name="full_photo"]').prop('checked', true);
			}
			// Photos in public albums are never hidden as such.  It's the
			// album that's hidden.  Or is that distinction irrelevant to end
			// users?
			if (album.json.downloadable === '1') {
				$('.basicModal .choice input[name="downloadable"]').prop('checked', true);
			}
			if (album.json.password === '1') {
				$('.basicModal .choice input[name="password"]').prop('checked', true);
			}
		}

		$('.basicModal .switch input').attr('disabled', true);
		$('.basicModal .switch .label').addClass('label--disabled');
	} else {
		// Private album -- each photo can be shared individually.

		var _msg = lychee.html(_templateObject55, msg_switch, lychee.locale['PHOTO_EDIT_GLOBAL_SHARING_TEXT'], msg_choices);

		var action = function action() {

			var newPublic = $('.basicModal .switch input[name="public"]:checked').length === 1 ? '1' : '0';

			if (newPublic !== photo.json.public) {
				if (visible.photo()) {
					photo.json.public = newPublic;
					view.photo.public();
				}

				album.getByID(photoID).public = newPublic;
				view.album.content.public(photoID);

				albums.refresh();

				// Photo::setPublic simply flips the current state.
				// Ugly API but effective...
				api.post('Photo::setPublic', { photoID: photoID }, function (data) {

					if (data !== true) lychee.error(null, params, data);
				});
			}

			basicModal.close();
		};

		basicModal.show({
			body: _msg,
			buttons: {
				action: {
					title: lychee.locale['PHOTO_SHARING_CONFIRM'],
					fn: action
				},
				cancel: {
					title: lychee.locale['CANCEL'],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').on('click', function () {
			if ($(this).prop('checked') === true) {
				if (lychee.full_photo) {
					$('.basicModal .choice input[name="full_photo"]').prop('checked', true);
				}
				// Photos shared individually are always hidden.
				$('.basicModal .choice input[name="hidden"]').prop('checked', true);
				if (lychee.downloadable) {
					$('.basicModal .choice input[name="downloadable"]').prop('checked', true);
				}
				// Photos shared individually are always hidden.
				$('.basicModal .choice input[name="hidden"]').prop('checked', true);
				if (lychee.share_button_visible) {
					$('.basicModal .choice input[name="share_button_visible"]').prop('checked', true);
				}
				// Photos shared individually can't be password-protected.
			} else {
				$('.basicModal .choice input').prop('checked', false);
			}
		});

		if (photo.json.public === '1') {
			$('.basicModal .switch input[name="public"]').click();
		}
	}

	return true;
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
		body: lychee.html(_templateObject56, lychee.locale['PHOTO_NEW_DESCRIPTION'], lychee.locale['PHOTO_DESCRIPTION'], oldDescription),
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
		photoIDs.forEach(function (id) {
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

	var input = lychee.html(_templateObject57, oldTags);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject3, lychee.locale['PHOTO_NEW_TAGS'], input);else msg = lychee.html(_templateObject51, lychee.locale['PHOTO_NEW_TAGS_1'], photoIDs.length, lychee.locale['PHOTO_NEW_TAGS_2'], input);

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

	if (photo.json.hasOwnProperty('share_button_visible') && photo.json.share_button_visible !== '1') {
		return;
	}

	var url = photo.getViewLink(photoID);

	switch (service) {
		case 'twitter':
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case 'facebook':
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(photo.json.title));
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

photo.setLicense = function (photoID) {

	var callback = function callback() {
		$('select#license').val(photo.json.license === '' ? 'none' : photo.json.license);
		return false;
	};

	var action = function action(data) {

		basicModal.close();
		var license = data.license;

		var params = {
			photoID: photoID,
			license: license
		};

		api.post('Photo::setLicense', params, function (data) {

			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				// update the photo JSON and reload the license in the sidebar
				photo.json.license = params.license;
				view.photo.license();
			}
		});
	};

	var msg = lychee.html(_templateObject6, lychee.locale['PHOTO_LICENSE'], lychee.locale['PHOTO_LICENSE_NONE'], lychee.locale['PHOTO_RESERVED'], lychee.locale['PHOTO_LICENSE_HELP']);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale['PHOTO_SET_LICENSE'],
				fn: action
			},
			cancel: {
				title: lychee.locale['CANCEL'],
				fn: basicModal.close
			}
		}
	});
};

photo.getArchive = function (photoIDs) {
	var kind = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;


	if (photoIDs.length === 1 && kind === null) {
		// For a single photo, allow to pick the kind via a dialog box.

		var myPhoto = void 0;

		if (photo.json && photo.json.id === photoIDs[0]) {
			myPhoto = photo.json;
		} else {
			myPhoto = album.getByID(photoIDs[0]);
		}

		var buildButton = function buildButton(id, label) {
			return lychee.html(_templateObject58, id, lychee.locale['DOWNLOAD'], build.iconic('cloud-download'), label);
		};

		var msg = lychee.html(_templateObject59);

		if (myPhoto.url) {
			msg += buildButton('FULL', lychee.locale['PHOTO_FULL'] + " (" + myPhoto.width + "x" + myPhoto.height + ", " + myPhoto.size + ")");
		}
		if (myPhoto.livePhotoUrl !== '') {
			msg += buildButton('LIVEPHOTOVIDEO', "" + lychee.locale['PHOTO_LIVE_VIDEO']);
		}
		if (myPhoto.hasOwnProperty('medium2x') && myPhoto.medium2x !== '') {
			msg += buildButton('MEDIUM2X', lychee.locale['PHOTO_MEDIUM_HIDPI'] + " (" + myPhoto.medium2x_dim + ")");
		}
		if (myPhoto.medium !== '') {
			msg += buildButton('MEDIUM', lychee.locale['PHOTO_MEDIUM'] + " " + (myPhoto.hasOwnProperty('medium_dim') ? '(' + myPhoto.medium_dim + ')' : ''));
		}
		if (myPhoto.hasOwnProperty('small2x') && myPhoto.small2x !== '') {
			msg += buildButton('SMALL2X', lychee.locale['PHOTO_SMALL_HIDPI'] + " (" + myPhoto.small2x_dim + ")");
		}
		if (myPhoto.small !== '') {
			msg += buildButton('SMALL', lychee.locale['PHOTO_SMALL'] + " " + (myPhoto.hasOwnProperty('small_dim') ? '(' + myPhoto.small_dim + ')' : ''));
		}
		if (lychee.api_V2) {
			if (myPhoto.hasOwnProperty('thumb2x') && myPhoto.thumb2x !== '') {
				msg += buildButton('THUMB2X', lychee.locale['PHOTO_THUMB_HIDPI'] + " (400x400)");
			}
			if (myPhoto.thumbUrl !== '') {
				msg += buildButton('THUMB', lychee.locale['PHOTO_THUMB'] + " (200x200)");
			}
		}

		msg += lychee.html(_templateObject60);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale['CLOSE'],
					fn: basicModal.close
				}
			}
		});

		$('.downloads .basicModal__button').on(lychee.getEventName(), function () {
			kind = this.id;
			basicModal.close();
			photo.getArchive(photoIDs, kind);
		});

		return true;
	}

	var link = void 0;

	if (lychee.api_V2) {
		location.href = api.get_url('Photo::getArchive') + lychee.html(_templateObject61, photoIDs.join(), kind);
	} else {
		var url = api.path + "?function=Photo::getArchive&photoID=" + photoIDs[0] + "&kind=" + kind;

		link = lychee.getBaseUrl() + url;

		if (lychee.publicMode === true) link += "&password=" + encodeURIComponent(password.value);

		location.href = link;
	}
};

photo.getDirectLink = function () {

	var url = '';

	if (photo.json && photo.json.url && photo.json.url !== '') url = photo.json.url;

	return url;
};

photo.getViewLink = function (photoID) {

	var url = 'view.php?p=' + photoID;
	if (lychee.api_V2) {
		url = 'view?p=' + photoID;
	}

	return lychee.getBaseUrl() + url;
};

photo.showDirectLinks = function (photoID) {
	if (!photo.json || photo.json.id != photoID) {
		return;
	}

	var buildLine = function buildLine(label, url) {
		return lychee.html(_templateObject62, label, url, lychee.locale['URL_COPY_TO_CLIPBOARD'], build.iconic('copy', 'ionicons'));
	};

	var msg = lychee.html(_templateObject63, buildLine(lychee.locale['PHOTO_VIEW'], photo.getViewLink(photoID)), lychee.locale['PHOTO_DIRECT_LINKS_TO_IMAGES']);

	if (photo.json.url) {
		msg += buildLine(lychee.locale['PHOTO_FULL'] + " (" + photo.json.width + "x" + photo.json.height + ")", lychee.getBaseUrl() + photo.json.url);
	}
	if (photo.json.hasOwnProperty('medium2x') && photo.json.medium2x !== '') {
		msg += buildLine(lychee.locale['PHOTO_MEDIUM_HIDPI'] + " (" + photo.json.medium2x_dim + ")", lychee.getBaseUrl() + photo.json.medium2x);
	}
	if (photo.json.medium !== '') {
		msg += buildLine(lychee.locale['PHOTO_MEDIUM'] + " " + (photo.json.hasOwnProperty('medium_dim') ? '(' + photo.json.medium_dim + ')' : ''), lychee.getBaseUrl() + photo.json.medium);
	}
	if (photo.json.hasOwnProperty('small2x') && photo.json.small2x !== '') {
		msg += buildLine(lychee.locale['PHOTO_SMALL_HIDPI'] + " (" + photo.json.small2x_dim + ")", lychee.getBaseUrl() + photo.json.small2x);
	}
	if (photo.json.small !== '') {
		msg += buildLine(lychee.locale['PHOTO_SMALL'] + " " + (photo.json.hasOwnProperty('small_dim') ? '(' + photo.json.small_dim + ')' : ''), lychee.getBaseUrl() + photo.json.small);
	}
	if (photo.json.hasOwnProperty('thumb2x') && photo.json.thumb2x !== '') {
		msg += buildLine(lychee.locale['PHOTO_THUMB_HIDPI'] + " (400x400)", lychee.getBaseUrl() + photo.json.thumb2x);
	} else if (!lychee.api_V2) {
		var _lychee$retinize4 = lychee.retinize(photo.json.thumbUrl),
		    thumb2x = _lychee$retinize4.path;

		msg += buildLine(lychee.locale['PHOTO_THUMB_HIDPI'] + " (400x400)", lychee.getBaseUrl() + thumb2x);
	}
	if (photo.json.thumbUrl !== '') {
		msg += buildLine(" " + lychee.locale['PHOTO_THUMB'] + " (200x200)", lychee.getBaseUrl() + photo.json.thumbUrl);
	}
	if (photo.json.livePhotoUrl !== '') {
		msg += buildLine(" " + lychee.locale['PHOTO_LIVE_VIDEO'] + " ", lychee.getBaseUrl() + photo.json.livePhotoUrl);
	}

	msg += lychee.html(_templateObject64);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale['CLOSE'],
				fn: basicModal.close
			}
		}
	});

	// Ensure that no input line is selected on opening.
	$('.basicModal input:focus').blur();

	$('.directLinks .basicModal__button').on(lychee.getEventName(), function () {
		if (lychee.clipboardCopy($(this).prev().val())) {
			loadingBar.show('success', lychee.locale['URL_COPIED_TO_CLIPBOARD']);
		}
	});
};

/**
 * @description Searches through your photos and albums.
 */

search = {

	hash: null

};

search.find = function (term) {

	if (term.trim() === '') return false;

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

				var albums_divider = lychee.locale['ALBUMS'];
				var photos_divider = lychee.locale['PHOTOS'];

				if (albumsData !== '') albums_divider += ' (' + data.albums.length + ')';
				if (photosData !== '') {
					photos_divider += ' (' + data.photos.length + ')';
					if (lychee.layout === '1') {
						photosData = '<div class="justified-layout">' + photosData + '</div>';
					} else if (lychee.layout === '2') {
						photosData = '<div class="unjustified-layout">' + photosData + '</div>';
					}
				}

				// 1. No albums and photos
				// 2. Only photos
				// 3. Only albums
				// 4. Albums and photos
				if (albumsData === '' && photosData === '') html = 'error';else if (albumsData === '') html = build.divider(photos_divider) + photosData;else if (photosData === '') html = build.divider(albums_divider) + albumsData;else html = build.divider(albums_divider) + albumsData + build.divider(photos_divider) + photosData;

				// Only refresh view when search results are different
				if (search.hash !== data.hash) {

					$('.no_content').remove();

					lychee.animate('.content', 'contentZoomOut');

					search.hash = data.hash;

					setTimeout(function () {
						if (visible.photo()) view.photo.hide();
						if (visible.sidebar()) sidebar.toggle();
						if (visible.mapview()) mapview.close();

						header.setMode('albums');

						if (html === 'error') {
							lychee.content.html('');
							$('body').append(build.no_content('magnifying-glass'));
						} else {
							lychee.content.html(html);
							view.album.content.justify();
							lychee.animate(lychee.content, 'contentZoomIn');
						}
						lychee.setTitle(lychee.locale['SEARCH_RESULTS'], false);
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
		var confirm = data.confirm;

		if (username.length < 1) {
			basicModal.error('username');
			return false;
		}

		if (password.length < 1) {
			basicModal.error('password');
			return false;
		}

		if (password !== confirm) {
			basicModal.error('confirm');
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

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['LOGIN_TITLE'] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale['LOGIN_USERNAME'] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD'] + "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD_CONFIRM'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

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

	if (params.password !== params.confirm) {
		loadingBar.show('error', 'new password does not match.');
		$('input[name=confirm]').addClass('error');
		return false;
	} else {
		$('input[name=confirm]').removeClass('error');
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
			lychee.init();
		} else lychee.error(null, params, data);
	});
};

settings.setDefaultLicense = function (params) {

	api.post('Settings::setDefaultLicense', params, function (data) {
		if (data === true) {
			lychee.default_license = params.license;
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_LICENSE']);
		} else lychee.error(null, params, data);
	});
};

settings.setLayout = function (params) {

	api.post('Settings::setLayout', params, function (data) {
		if (data === true) {
			lychee.layout = params.layout;
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_LAYOUT']);
		} else lychee.error(null, params, data);
	});
};

settings.changePublicSearch = function () {
	var params = {};
	if ($('#PublicSearch:checked').length === 1) {
		params.public_search = '1';
	} else {
		params.public_search = '0';
	}
	api.post('Settings::setPublicSearch', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_PUBLIC_SEARCH']);
			lychee.public_search = params.public_search === '1';
		} else lychee.error(null, params, data);
	});
};

settings.changeImageOverlay = function () {
	var params = {};
	if ($('#ImageOverlay:checked').length === 1) {
		params.image_overlay = '1';

		// enable image_overlay_type
		$('select#ImgOverlayType').attr('disabled', false);
	} else {
		params.image_overlay = '0';

		// disable image_overlay_type
		$('select#ImgOverlayType').attr('disabled', true);
	}
	api.post('Settings::setImageOverlay', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_IMAGE_OVERLAY']);
			lychee.image_overlay_default = params.image_overlay === '1';
			lychee.image_overlay = lychee.image_overlay_default;
		} else lychee.error(null, params, data);
	});
};

settings.setOverlayType = function () {
	// validate the input
	var params = {};
	if ($('#ImageOverlay:checked') && $('#ImgOverlayType').val() === "exif") {
		params.image_overlay_type = 'exif';
	} else if ($('#ImageOverlay:checked') && $('#ImgOverlayType').val() === "desc") {
		params.image_overlay_type = 'desc';
	} else if ($('#ImageOverlay:checked') && $('#ImgOverlayType').val() === "takedate") {
		params.image_overlay_type = 'takedate';
	} else {
		params.image_overlay_type = 'exif';
		console.log('Error - default used');
	}

	api.post('Settings::setOverlayType', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_IMAGE_OVERLAY']);
			lychee.image_overlay_type = params.image_overlay_type;
			lychee.image_overlay_type_default = params.image_overlay_type;
		} else lychee.error(null, params, data);
	});
};

settings.changeMapDisplay = function () {
	var params = {};
	if ($('#MapDisplay:checked').length === 1) {
		params.map_display = '1';
	} else {
		params.map_display = '0';
	}
	api.post('Settings::setMapDisplay', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_MAP_DISPLAY']);
			lychee.map_display = params.map_display === '1';
		} else lychee.error(null, params, data);
	});
	// Map functionality is disabled
	// -> map for public albums also needs to be disabled
	if (lychee.map_display_public === true) {
		$('#MapDisplayPublic').click();
	}
};

settings.changeMapDisplayPublic = function () {
	var params = {};
	if ($('#MapDisplayPublic:checked').length === 1) {
		params.map_display_public = '1';

		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.map_display === false) {
			$('#MapDisplay').click();
		}
	} else {
		params.map_display_public = '0';
	}
	api.post('Settings::setMapDisplayPublic', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC']);
			lychee.map_display_public = params.map_display_public === '1';
		} else lychee.error(null, params, data);
	});
};

settings.setMapProvider = function () {
	// validate the input
	var params = {};
	params.map_provider = $('#MapProvider').val();

	api.post('Settings::setMapProvider', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_MAP_PROVIDER']);
			lychee.map_provider = params.map_provider;
		} else lychee.error(null, params, data);
	});
};

settings.changeMapIncludeSubalbums = function () {
	var params = {};
	if ($('#MapIncludeSubalbums:checked').length === 1) {
		params.map_include_subalbums = '1';
	} else {
		params.map_include_subalbums = '0';
	}
	api.post('Settings::setMapIncludeSubalbums', params, function (data) {
		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_MAP_DISPLAY']);
			lychee.map_include_subalbums = params.map_include_subalbums === '1';
		} else lychee.error(null, params, data);
	});
};

settings.changeCSS = function () {

	var params = {};
	params.css = $('#css').val();

	api.post('Settings::setCSS', params, function (data) {

		if (data === true) {
			lychee.css = params.css;
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_CSS']);
		} else lychee.error(null, params, data);
	});
};

settings.save = function (params) {

	api.post('Settings::saveAll', params, function (data) {

		if (data === true) {
			loadingBar.show('success', lychee.locale['SETTINGS_SUCCESS_UPDATE']);
			view.full_settings.init();
			// lychee.init();
		} else lychee.error('Check the Logs', params, data);
	});
};

settings.save_enter = function (e) {
	if (e.which === 13) {
		// show confirmation box
		$(':focus').blur();

		var action = {};
		var cancel = {};

		action.title = lychee.locale['ENTER'];
		action.msg = lychee.html(_templateObject65, lychee.locale['SAVE_RISK']);

		cancel.title = lychee.locale['CANCEL'];

		action.fn = function () {
			settings.save(settings.getValues('#fullSettings'));
			basicModal.close();
		};

		basicModal.show({
			body: action.msg,
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
	}
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

	if (params.albumIDs === '') {
		loadingBar.show('error', 'Select an album to share!');
		return false;
	}
	if (params.UserIDs === '') {
		loadingBar.show('error', 'Select a user to share with!');
		return false;
	}

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

	$('input[name="remove_id"]:checked').each(function () {
		if (params.ShareIDs !== '') params.ShareIDs += ',';
		params.ShareIDs += this.value;
	});

	if (params.ShareIDs === '') {
		loadingBar.show('error', 'Select a sharing to remove!');
		return false;
	}
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

	sidebar.dom('#tags .tag').off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});

	sidebar.dom('#tags .tag span').off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data('index'));
	});

	sidebar.dom('#edit_license').off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setLicense(photo.getID());else if (visible.album()) album.setLicense(album.getID());
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
			{ title: lychee.locale['PHOTO_ALTITUDE'], kind: 'altitude', value: data.altitude ? (Math.round(parseFloat(data.altitude) * 10) / 10).toString() + 'm' : '' }]
		};
		if (data.imgDirection) {
			// No point in display sub-degree precision.
			structure.location.rows.push({ title: lychee.locale['PHOTO_IMGDIRECTION'], kind: 'imgDirection', value: Math.round(data.imgDirection).toString() + '°' });
		}
	} else {
		structure.location = {};
	}

	// Construct all parts of the structure
	structure = [structure.basics, structure.image, structure.tags, structure.exif, structure.location, structure.sharing, structure.license];

	return structure;
};

sidebar.createStructure.album = function (data) {

	if (data == null || data === '') return false;

	var editable = album.isUploadable();
	var structure = {};
	var _public = '';
	var hidden = '';
	var downloadable = '';
	var share_button_visible = '';
	var password = '';
	var license = '';

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

	structure.basics = {
		title: lychee.locale['ALBUM_BASICS'],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale['ALBUM_TITLE'], kind: 'title', value: data.title, editable: editable }, { title: lychee.locale['ALBUM_DESCRIPTION'], kind: 'description', value: data.description, editable: editable }]
	};

	videoCount = 0;
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
	structure = [structure.basics, structure.album, structure.share, structure.license];

	return structure;
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

			section.rows.forEach(function (row) {
				if (row.kind == 'latitude' && row.value !== '') {
					_has_latitude = true;
				}

				if (row.kind == 'longitude' && row.value !== '') {
					_has_longitude = true;
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
				value = lychee.html(_templateObject66, row.kind, value);

				// Add edit-icon to the value when editable
				if (row.editable === true) value += ' ' + build.editIcon('edit_' + row.kind);

				_html += lychee.html(_templateObject67, row.title, value);
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

		_html += lychee.html(_templateObject68, section.title, section.title.toLowerCase(), section.value, editable);

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
	var direction = 'X';

	//decimal must be integer or float no larger than 180;
	//type must be Boolean
	if (Math.abs(decimal) > 180 || !(typeof type === "boolean")) {
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
};

/**
 * @description Swipes and moves an object.
 */

swipe = {

	obj: null,
	tolerance_X: 150,
	tolerance_Y: 250,
	offsetX: 0,
	offsetY: 0

};

swipe.start = function (obj, tolerance_X, tolerance_Y) {

	if (obj) swipe.obj = obj;
	if (tolerance_X) swipe.tolerance_X = tolerance_X;
	if (tolerance_Y) swipe.tolerance_Y = tolerance_Y;

	return true;
};

swipe.move = function (e) {

	if (swipe.obj === null) return false;

	if (Math.abs(e.x) > Math.abs(e.y)) {
		swipe.offsetX = -1 * e.x;
		swipe.offsetY = 0.0;
	} else {
		swipe.offsetX = 0.0;
		swipe.offsetY = +1 * e.y;
	}

	swipe.obj.css({
		'WebkitTransform': 'translate(' + swipe.offsetX + 'px, ' + swipe.offsetY + 'px)',
		'MozTransform': 'translate(' + swipe.offsetX + 'px, ' + swipe.offsetY + 'px)',
		'transform': 'translate(' + swipe.offsetX + 'px, ' + swipe.offsetY + 'px)'
	});
};

swipe.stop = function (e, left, right) {

	// Only execute once
	if (swipe.obj == null) return false;

	if (e.y <= -swipe.tolerance_Y) {

		lychee.goto(album.getID());
	} else if (e.y >= swipe.tolerance_Y) {

		lychee.goto(album.getID());
	} else if (e.x <= -swipe.tolerance_X) {

		left(true);
	} else if (e.x >= swipe.tolerance_X) {

		right(true);
	} else {

		swipe.obj.css({
			WebkitTransform: 'translate(0px, 0px)',
			MozTransform: 'translate(0px, 0px)',
			transform: 'translate(0px, 0px)'
		});
	}

	swipe.obj = null;
	swipe.offsetX = 0;
	swipe.offsetY = 0;
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
			body: lychee.html(_templateObject69) + lychee.locale['UPLOAD_IMPORT_INSTR'] + (" <input class='text' name='link' type='text' placeholder='http://' value='" + _url + "'></p>"),
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

			var delete_imported = $('.basicModal .choice input[name="delete"]').prop('checked') ? '1' : '0';

			upload.show(lychee.locale['UPLOAD_IMPORT_SERVER'], files, function () {

				$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_IMPORTING']);

				var params = {
					albumID: albumID,
					path: data.path,
					delete_imported: delete_imported
				};

				if (lychee.api_V2 === false) {
					api.post('Import::server', params, function (data) {

						albums.refresh();
						upload.notify(lychee.locale['UPLOAD_IMPORT_COMPLETE']);

						if (data === 'Notice: Import only contained albums!') {

							// No error, but the folder only contained albums

							// Go back to the album overview to show the imported albums
							if (visible.albums()) lychee.load();else album.reload();

							basicModal.close();

							return true;
						} else if (data === 'Warning: Folder empty or no readable files to process!') {

							// Error because the import could not start

							$('.basicModal .rows .row p.notice').html(lychee.locale['UPLOAD_IMPORT_SERVER_FOLD']).show();

							$('.basicModal .rows .row .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');

							// Log error
							lychee.error(lychee.locale['UPLOAD_IMPORT_SERVER_EMPT'], params, data);
						} else {
							if (data !== true) {

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
						}

						// Show close button
						$('.basicModal #basicModal__action.hidden').show();
					});
				} else {
					// Variables holding state across the invocations of
					// processIncremental().
					var lastReadIdx = 0;
					var currentDir = data.path;
					var encounteredProblems = false;
					var rowCount = 1;

					// Worker function invoked from both the response progress
					// callback and the completion callback.
					var processIncremental = function processIncremental(jsonResponse) {
						// Skip the part that we've already processed during
						// the previous invocation(s).
						var newResponse = jsonResponse.substring(lastReadIdx);
						// Because of all the potential buffering along the way,
						// we can't be sure if the last line is complete.  For
						// that reason, our custom protocol terminates every
						// line with the newline character, including the last
						// line.
						var lastNewline = newResponse.lastIndexOf('\n');
						if (lastNewline === -1) {
							// No valid input data to process.
							return;
						}
						if (lastNewline !== newResponse.length - 1) {
							// Last line is not newline-terminated, so it
							// must be incomplete.  Strip it; it will be
							// handled during the next invocation.
							newResponse = newResponse.substring(0, lastNewline + 1);
						}
						// Advance the counter past the last valid character.
						lastReadIdx += newResponse.length;

						newResponse.split('\n').forEach(function (resp) {
							var matches = resp.match(/^Status: (.*): (\d+)$/);
							if (matches !== null) {
								if (matches[2] !== '100') {
									if (currentDir !== matches[1]) {
										// New directory.  Add a new line to
										// the dialog box.
										currentDir = matches[1];
										$('.basicModal .rows').append(build.uploadNewFile(currentDir));
										rowCount++;
									}
									$('.basicModal .rows .row:last-child .status').html(matches[2] + '%');
								} else {
									// Final status report for this directory.
									$('.basicModal .rows .row:last-child .status').html(lychee.locale['UPLOAD_FINISHED']).addClass('success');
								}
							} else if ((matches = resp.match(/^Problem: (.*): ([^:]*)$/)) !== null) {
								var rowSelector = void 0;
								if (currentDir !== matches[1]) {
									$('.basicModal .rows .row:last-child').before(build.uploadNewFile(matches[1]));
									rowCount++;
									rowSelector = '.basicModal .rows .row:nth-last-child(2)';
								} else {
									// The problem is with the directory
									// itself, so alter its existing line.
									rowSelector = '.basicModal .rows .row:last-child';
								}
								if (matches[2] === 'Given path is not a directory' || matches[2] === 'Given path is reserved') {
									$(rowSelector + ' .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');
								} else {
									$(rowSelector + ' .status').html(lychee.locale['UPLOAD_SKIPPED']).addClass('warning');
								}
								$(rowSelector + ' .notice').html(matches[2] === 'Given path is not a directory' ? lychee.locale['UPLOAD_IMPORT_NOT_A_DIRECTORY'] : matches[2] === 'Given path is reserved' ? lychee.locale['UPLOAD_IMPORT_PATH_RESERVED'] : matches[2] === 'Could not read file' ? lychee.locale['UPLOAD_IMPORT_UNREADABLE'] : matches[2] === 'Could not import file' ? lychee.locale['UPLOAD_IMPORT_FAILED'] : matches[2] === 'Unsupported file type' ? lychee.locale['UPLOAD_IMPORT_UNSUPPORTED'] : matches[2] === 'Could not create album' ? lychee.locale['UPLOAD_IMPORT_ALBUM_FAILED'] : matches[2]).show();
								encounteredProblems = true;
							} else if (resp === 'Warning: Approaching memory limit') {
								$('.basicModal .rows .row:last-child').before(build.uploadNewFile(lychee.locale['UPLOAD_IMPORT_LOW_MEMORY']));
								rowCount++;
								$('.basicModal .rows .row:nth-last-child(2) .status').html(lychee.locale['UPLOAD_WARNING']).addClass('warning');
								$('.basicModal .rows .row:nth-last-child(2) .notice').html(lychee.locale['UPLOAD_IMPORT_LOW_MEMORY_EXPL']).show();
							}
							$('.basicModal .rows').scrollTop((rowCount - 1) * 40);
						}); // forEach (resp)
					}; // processIncremental

					api.post('Import::server', params, function (data) {
						// data is already JSON-parsed.
						processIncremental(data);

						albums.refresh();

						upload.notify(lychee.locale['UPLOAD_IMPORT_COMPLETE'], encounteredProblems ? lychee.locale['UPLOAD_COMPLETE_FAILED'] : null);

						if (album.getID() === false) lychee.goto('0');else album.load(albumID);

						if (encounteredProblems) {
							// Show close button
							$('.basicModal #basicModal__action.hidden').show();
						} else {
							basicModal.close();
						}
					}, function (event) {
						// We received a possibly partial response.
						// We need to begin by terminating the data with a
						// '"' so that it can be JSON-parsed.
						var response = this.response;
						if (response.length > 0) {
							if (response.substring(this.response.length - 1) === '"') {
								// This might be either a terminating '"'
								// or it may come from, say, a filename, in
								// which case it would be escaped.
								if (response.length > 1) {
									if (response.substring(this.response.length - 2) === '\"') {
										response += '"';
									}
									// else it's a complete response,
									// requiring no termination from us.
								} else {
									// The response is just '"'.
									response += '"';
								}
							} else {
								// This should be the most common case for
								// partial responses.
								response += '"';
							}
						}
						// Parse the response as JSON.  This will remove
						// the surrounding '"' characters, unescape any '"'
						// from the middle, and translate '\n' sequences into
						// newlines.
						var jsonResponse = void 0;
						try {
							jsonResponse = JSON.parse(response);
						} catch (e) {
							// Most likely a SyntaxError due to something
							// that went wrong on the server side.
							$('.basicModal .rows .row:last-child .status').html(lychee.locale['UPLOAD_FAILED']).addClass('error');

							albums.refresh();
							upload.notify(lychee.locale['UPLOAD_COMPLETE'], lychee.locale['UPLOAD_COMPLETE_FAILED']);

							if (album.getID() === false) lychee.goto('0');else album.load(albumID);

							// Show close button
							$('.basicModal #basicModal__action.hidden').show();

							return;
						}
						// The rest of the work is the same as for the full
						// response.
						processIncremental(jsonResponse);
					}); // api.post
				} // lychee.api_V2
			}); // upload.show
		}; // action

		var msg = lychee.html(_templateObject70, lychee.locale['UPLOAD_IMPORT_SERVER_INSTR'], lychee.locale['UPLOAD_ABSOLUTE_PATH'], lychee.location);
		if (lychee.api_V2) {
			msg += lychee.html(_templateObject71, build.iconic('check'), lychee.locale['UPLOAD_IMPORT_DELETE_ORIGINALS'], lychee.locale['UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL']);
		}

		basicModal.show({
			body: msg,
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

		if (lychee.delete_imported) {
			$('.basicModal .choice input[name="delete"]').prop('checked', true);
		}
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

	if (params.username.length < 1) {
		loadingBar.show('error', 'new username cannot be empty.');
		return false;
	}

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

	if (params.username.length < 1) {
		loadingBar.show('error', 'new username cannot be empty.');
		return false;
	}
	if (params.password.length < 1) {
		loadingBar.show('error', 'new password cannot be empty.');
		return false;
	}

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

		multiselect.clearSelection();

		view.albums.title();
		view.albums.content.init();
	},

	title: function title() {

		if (lychee.landing_page_enable) {
			if (lychee.title !== 'Lychee v4') {
				lychee.setTitle(lychee.title, false);
			} else {
				lychee.setTitle(lychee.locale['ALBUMS'], false);
			}
		} else {
			lychee.setTitle(lychee.locale['ALBUMS'], false);
		}
	},

	content: {

		scrollPosition: 0,

		init: function init() {

			var smartData = '';
			var albumsData = '';
			var sharedData = '';

			// Smart Albums
			if (albums.json.smartalbums != null) {

				if (lychee.publicMode === false) {
					smartData = build.divider(lychee.locale['SMART_ALBUMS']);
				}
				if (albums.json.smartalbums.unsorted) {
					albums.parse(albums.json.smartalbums.unsorted);
					smartData += build.album(albums.json.smartalbums.unsorted);
				}
				if (albums.json.smartalbums.public) {
					albums.parse(albums.json.smartalbums.public);
					smartData += build.album(albums.json.smartalbums.public);
				}
				if (albums.json.smartalbums.starred) {
					albums.parse(albums.json.smartalbums.starred);
					smartData += build.album(albums.json.smartalbums.starred);
				}
				if (albums.json.smartalbums.recent) {
					albums.parse(albums.json.smartalbums.recent);
					smartData += build.album(albums.json.smartalbums.recent);
				}
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
				var current_owner = '';
				var i = 0;
				// Shared
				if (albums.json.shared_albums && albums.json.shared_albums.length !== 0) {

					for (i = 0; i < albums.json.shared_albums.length; ++i) {
						var alb = albums.json.shared_albums[i];
						if (!alb.parent_id || alb.parent_id === 0) {
							albums.parse(alb);
							if (current_owner !== alb.owner && lychee.publicMode === false) {
								sharedData += build.divider(alb.owner);
								current_owner = alb.owner;
							}
							sharedData += build.album(alb, !lychee.admin);
						}
					}
					// $.each(albums.json.shared_albums, function() {
					// 	if(!this.parent_id || this.parent_id === 0) {
					// 		albums.parse(this);
					// 		sharedData += build.album(this, true)
					// 	}
					// });
					//
					// // Add divider
					// if (lychee.publicMode===false) sharedData = build.divider(lychee.locale['SHARED_ALBUMS']) + sharedData
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

		multiselect.clearSelection();

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
					lychee.setTitle(lychee.locale['STARRED'], true);
					break;
				case 's':
					lychee.setTitle(lychee.locale['PUBLIC'], true);
					break;
				case 'r':
					lychee.setTitle(lychee.locale['RECENT'], true);
					break;
				case '0':
					lychee.setTitle(lychee.locale['UNSORTED'], true);
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
			var albumsData = '';
			var html = '';

			if (album.json.albums && album.json.albums !== false) {
				$.each(album.json.albums, function () {
					albums.parse(this);
					albumsData += build.album(this, !album.isUploadable());
				});
			}
			if (album.json.photos && album.json.photos !== false) {

				// Build photos
				$.each(album.json.photos, function () {
					photosData += build.photo(this, !album.isUploadable());
				});
			}

			if (photosData !== '') {
				if (lychee.layout === '1') {
					photosData = '<div class="justified-layout">' + photosData + '</div>';
				} else if (lychee.layout === '2') {
					photosData = '<div class="unjustified-layout">' + photosData + '</div>';
				}
			}

			if (albumsData !== '' && photosData !== '') {
				html = build.divider(lychee.locale['ALBUMS']);
			}
			html += albumsData;
			if (albumsData !== '' && photosData !== '') {
				html += build.divider(lychee.locale['PHOTOS']);
			}
			html += photosData;

			// Save and reset scroll position
			view.albums.content.scrollPosition = $(document).scrollTop();
			requestAnimationFrame(function () {
				return $(document).scrollTop(0);
			});

			// Add photos to view
			lychee.content.html(html);
			view.album.content.justify();
		},

		title: function title(photoID) {

			var title = album.getByID(photoID).title;

			title = lychee.escapeHTML(title);

			$('.photo[data-id="' + photoID + '"] .overlay h1').html(title).attr('title', title);
		},

		titleSub: function titleSub(albumID) {

			var title = album.getSubByID(albumID).title;

			title = lychee.escapeHTML(title);

			$('.album[data-id="' + albumID + '"] .overlay h1').html(title).attr('title', title);
		},

		star: function star(photoID) {

			var $badge = $('.photo[data-id="' + photoID + '"] .icn-star');

			if (album.getByID(photoID).star === '1') $badge.addClass('badge--star');else $badge.removeClass('badge--star');
		},

		public: function _public(photoID) {

			var $badge = $('.photo[data-id="' + photoID + '"] .icn-share');

			if (album.getByID(photoID).public === '1') $badge.addClass('badge--visible badge--hidden');else $badge.removeClass('badge--visible badge--hidden');
		},

		delete: function _delete(photoID) {
			var justify = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


			$('.photo[data-id="' + photoID + '"]').css('opacity', 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				// Only when search is not active
				if (album.json) {
					if (visible.sidebar()) {
						videoCount = 0;
						$.each(album.json.photos, function () {
							if (this.type && this.type.indexOf('video') > -1) {
								videoCount++;
							}
						});
						if (album.json.photos.length - videoCount > 0) {
							sidebar.changeAttr('images', album.json.photos.length - videoCount);
						} else {
							sidebar.hideAttr('images');
						}
						if (videoCount > 0) {
							sidebar.changeAttr('videos', videoCount);
						} else {
							sidebar.hideAttr('videos');
						}
					}
					if (album.json.photos.length <= 0) {
						lychee.content.find('.divider').remove();
					}
					if (justify) {
						view.album.content.justify();
					}
				}
			});
		},

		deleteSub: function deleteSub(albumID) {

			$('.album[data-id="' + albumID + '"]').css('opacity', 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (album.json) {
					if (album.json.albums.length <= 0) {
						lychee.content.find('.divider').remove();
					}
					if (visible.sidebar()) {
						if (album.json.albums.length > 0) {
							sidebar.changeAttr('subalbums', album.json.albums.length);
						} else {
							sidebar.hideAttr('subalbums');
						}
					}
				}
			});
		},

		justify: function justify() {
			if (!album.json || !album.json.photos || album.json.photos === false) return;
			if (lychee.layout === '1') {
				var containerWidth = parseFloat($('.justified-layout').width(), 10);
				if (containerWidth == 0) {
					// Triggered on Reload in photo view.
					containerWidth = $(window).width() - parseFloat($('.justified-layout').css('margin-left'), 10) - parseFloat($('.justified-layout').css('margin-right'), 10) - parseFloat($('.content').css('padding-right'), 10);
				}
				var ratio = [];
				$.each(album.json.photos, function (i) {
					ratio[i] = this.height > 0 ? this.width / this.height : 1;
					if (this.type && this.type.indexOf('video') > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (this.small === '' && this.medium === '') {
							ratio[i] = 1;
						}
					}
				});
				var layoutGeometry = require('justified-layout')(ratio, {
					containerWidth: containerWidth,
					containerPadding: 0
				});
				// if (lychee.admin) console.log(layoutGeometry);
				$('.justified-layout').css('height', layoutGeometry.containerHeight + 'px');
				$('.justified-layout > div').each(function (i) {
					if (!layoutGeometry.boxes[i]) {
						// Race condition in search.find -- window content
						// and album.json can get out of sync as search
						// query is being modified.
						return false;
					}
					$(this).css('top', layoutGeometry.boxes[i].top);
					$(this).css('width', layoutGeometry.boxes[i].width);
					$(this).css('height', layoutGeometry.boxes[i].height);
					$(this).css('left', layoutGeometry.boxes[i].left);

					var imgs = $(this).find(".thumbimg > img");
					if (imgs.length > 0 && imgs[0].getAttribute('data-srcset')) {
						imgs[0].setAttribute('sizes', layoutGeometry.boxes[i].width + 'px');
					}
				});
			} else if (lychee.layout === '2') {
				var _containerWidth = parseFloat($('.unjustified-layout').width(), 10);
				if (_containerWidth == 0) {
					// Triggered on Reload in photo view.
					_containerWidth = $(window).width() - parseFloat($('.unjustified-layout').css('margin-left'), 10) - parseFloat($('.unjustified-layout').css('margin-right'), 10) - parseFloat($('.content').css('padding-right'), 10);
				}
				// For whatever reason, the calculation of margin is
				// super-slow in Firefox (tested with 68), so we make sure to
				// do it just once, outside the loop.  Height doesn't seem to
				// be affected, but we do it the same way for consistency.
				var margin = parseFloat($('.photo').css('margin-right'), 10);
				var origHeight = parseFloat($('.photo').css('max-height'), 10);
				$('.unjustified-layout > div').each(function (i) {
					if (!album.json.photos[i]) {
						// Race condition in search.find -- window content
						// and album.json can get out of sync as search
						// query is being modified.
						return false;
					}
					var ratio = album.json.photos[i].height > 0 ? album.json.photos[i].width / album.json.photos[i].height : 1;
					if (album.json.photos[i].type && album.json.photos[i].type.indexOf('video') > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (album.json.photos[i].small === '' && album.json.photos[i].medium === '') {
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

					$(this).css('width', width + 'px');
					$(this).css('height', height + 'px');
					if (imgs.length > 0 && imgs[0].getAttribute('data-srcset')) {
						imgs[0].setAttribute('sizes', width + 'px');
					}
				});
			}
		}

	},

	description: function description() {

		sidebar.changeAttr('description', album.json.description);
	},

	license: function (_license) {
		function license() {
			return _license.apply(this, arguments);
		}

		license.toString = function () {
			return _license.toString();
		};

		return license;
	}(function () {

		switch (album.json.license) {
			case 'none':
				license = ''; // none is displayed as - thus is empty.
				break;
			case 'reserved':
				license = lychee.locale['ALBUM_RESERVED'];
				break;
			default:
				license = album.json.license;
				// console.log('default');
				break;
		}

		sidebar.changeAttr('license', license);
	}),

	public: function _public() {

		$('#button_visibility_album').removeClass('active--not-hidden active--hidden');

		if (album.json.public === '1') {

			if (album.json.visible === '0') {
				$('#button_visibility_album').addClass('active--hidden');
			} else {
				$('#button_visibility_album').addClass('active--not-hidden');
			}

			$('.photo .iconic-share').remove();

			if (album.json.init) sidebar.changeAttr('public', lychee.locale['ALBUM_SHR_YES']);
		} else {

			if (album.json.init) sidebar.changeAttr('public', lychee.locale['ALBUM_SHR_NO']);
		}
	},

	hidden: function hidden() {

		if (album.json.visible === '1') sidebar.changeAttr('hidden', lychee.locale['ALBUM_SHR_NO']);else sidebar.changeAttr('hidden', lychee.locale['ALBUM_SHR_YES']);
	},

	downloadable: function downloadable() {

		if (album.json.downloadable === '1') sidebar.changeAttr('downloadable', lychee.locale['ALBUM_SHR_YES']);else sidebar.changeAttr('downloadable', lychee.locale['ALBUM_SHR_NO']);
	},

	shareButtonVisible: function shareButtonVisible() {

		if (album.json.share_button_visible === '1') sidebar.changeAttr('share_button_visible', lychee.locale['ALBUM_SHR_YES']);else sidebar.changeAttr('share_button_visible', lychee.locale['ALBUM_SHR_NO']);
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
			var _html2 = sidebar.render(structure);

			sidebar.dom('.sidebar__wrapper').html(_html2);
			sidebar.bind();
		}
	})

};

view.photo = {

	init: function init(autoplay) {

		multiselect.clearSelection();

		photo.parse();

		view.photo.sidebar();
		view.photo.title();
		view.photo.star();
		view.photo.public();
		view.photo.photo(autoplay);

		photo.json.init = 1;
	},

	show: function show() {

		// Change header
		lychee.content.addClass('view');
		header.setMode('photo');

		// Make body not scrollable
		// use bodyScrollLock package to enable locking on iOS
		// Simple overflow: hidden not working on iOS Safari
		// Only the info pane needs scrolling
		// Touch event for swiping of photo still work

		scrollLock.disablePageScroll($('.sidebar__wrapper').get());

		// Fullscreen
		var timeout = null;
		$(document).bind('mousemove', function () {
			clearTimeout(timeout);
			// For live Photos: header animtion only if LivePhoto is not playing
			if (!photo.isLivePhotoPlaying()) {
				header.show();
				timeout = setTimeout(header.hideIfLivePhotoNotPlaying, 2500);
			}
		});

		// we also put this timeout to enable it by default when you directly click on a picture.
		setTimeout(header.hideIfLivePhotoNotPlaying, 2500);

		lychee.animate(lychee.imageview, 'fadeIn');
	},

	hide: function hide() {

		header.show();

		lychee.content.removeClass('view');
		header.setMode('album');

		// Make body scrollable
		scrollLock.enablePageScroll($('.sidebar__wrapper').get());

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

	license: function license() {
		var license = void 0;

		// Process key to display correct string
		switch (album.json.license) {
			case 'none':
				license = ''; // none is displayed as - thus is empty (uniformity of the display).
				break;
			case 'reserved':
				license = lychee.locale['PHOTO_RESERVED'];
				break;
			default:
				license = photo.json.license;
				break;
		}

		// Update the sidebar if the photo is visible
		if (photo.json.init) sidebar.changeAttr('license', license);
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

		$('#button_visibility').removeClass('active--hidden active--not-hidden');

		if (photo.json.public === '1' || photo.json.public === '2') {

			// Photo public
			if (photo.json.public === '1') {
				$('#button_visibility').addClass('active--hidden');
			} else {
				$('#button_visibility').addClass('active--not-hidden');
			}

			if (photo.json.init) sidebar.changeAttr('public', lychee.locale['PHOTO_SHR_YES']);
		} else {

			// Photo private
			if (photo.json.init) sidebar.changeAttr('public', 'No');
		}
	},

	tags: function tags() {

		sidebar.changeAttr('tags', build.tags(photo.json.tags), true);
		sidebar.bind();
	},

	photo: function (_photo) {
		function photo(_x34) {
			return _photo.apply(this, arguments);
		}

		photo.toString = function () {
			return _photo.toString();
		};

		return photo;
	}(function (autoplay) {

		var ret = build.imageview(photo.json, visible.header(), autoplay);
		lychee.imageview.html(ret.html);

		// Init Live Photo if needed
		if (photo.isLivePhoto()) {
			// Package gives warning that function will be remove and
			// shoud be replaced by LivePhotosKit.augementElementAsPlayer
			// But, LivePhotosKit.augementElementAsPlayer is not yet available
			photo.LivePhotosObject = LivePhotosKit.Player(document.getElementById('livephoto'));
		}

		view.photo.onresize();

		var $nextArrow = lychee.imageview.find('a#next');
		var $previousArrow = lychee.imageview.find('a#previous');
		var photoID = photo.getID();
		var hasNext = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).nextPhoto != null && album.getByID(photoID).nextPhoto !== '';
		var hasPrevious = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).previousPhoto != null && album.getByID(photoID).previousPhoto !== '';

		var img = $('img#image');
		if (img.length > 0) {
			if (!img[0].complete || img[0].currentSrc !== null && img[0].currentSrc === '') {
				// Image is still loading.  Display the thumb version in the
				// background.
				if (ret.thumb !== '') {
					img.css('background-image', lychee.html(_templateObject72, ret.thumb));
				}

				// Don't preload next/prev until the requested image is
				// fully loaded.
				img.on('load', function () {
					photo.preloadNextPrev(photo.getID());
				});
			} else {
				photo.preloadNextPrev(photo.getID());
			}
		}

		if (hasNext === false || lychee.viewMode === true) {

			$nextArrow.hide();
		} else {

			var nextPhotoID = album.getByID(photoID).nextPhoto;
			var nextPhoto = album.getByID(nextPhotoID);

			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var thumbUrl = nextPhoto.thumbUrl;

			if (thumbUrl === 'uploads/thumb/' && nextPhoto.type.indexOf('video') > -1) {
				thumbUrl = 'img/play-icon.png';
			}
			$nextArrow.css('background-image', lychee.html(_templateObject73, thumbUrl));
		}

		if (hasPrevious === false || lychee.viewMode === true) {

			$previousArrow.hide();
		} else {

			var previousPhotoID = album.getByID(photoID).previousPhoto;
			var previousPhoto = album.getByID(previousPhotoID);

			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var _thumbUrl = previousPhoto.thumbUrl;

			if (_thumbUrl === 'uploads/thumb/' && previousPhoto.type.indexOf('video') > -1) {
				_thumbUrl = 'img/play-icon.png';
			}
			$previousArrow.css('background-image', lychee.html(_templateObject73, _thumbUrl));
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
		var has_location = photo.json.latitude && photo.json.longitude ? true : false;

		sidebar.dom('.sidebar__wrapper').html(html);
		sidebar.bind();

		if (has_location && lychee.map_display) {
			// Leaflet seaches for icon in same directoy as js file -> paths needs
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: 'img/marker-icon-2x.png',
				iconUrl: 'img/marker-icon.png',
				shadowUrl: 'img/marker-shadow.png'
			});

			var mymap = L.map('leaflet_map_single_photo').setView([photo.json.latitude, photo.json.longitude], 13);

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(mymap);

			if (!photo.json.imgDirection || photo.json.imgDirection === '') {
				// Add Marker to map, direction is not set
				var marker = L.marker([photo.json.latitude, photo.json.longitude]).addTo(mymap);
			} else {
				// Add Marker, direction has been set
				var viewDirectionIcon = L.icon({
					iconUrl: 'img/view-angle-icon.png',
					iconRetinaUrl: 'img/view-angle-icon-2x.png',
					iconSize: [100, 58], // size of the icon
					iconAnchor: [50, 49] // point of the icon which will correspond to marker's location
				});
				var marker = L.marker([photo.json.latitude, photo.json.longitude], { icon: viewDirectionIcon }).addTo(mymap);
				marker.setRotationAngle(photo.json.imgDirection);
			}
		}
	}),

	onresize: function onresize() {
		if (!photo.json || photo.json.medium === '' || !photo.json.medium2x || photo.json.medium2x === '') return;

		// Calculate the width of the image in the current window without
		// borders and set 'sizes' to it.
		var imgWidth = parseInt(photo.json.medium_dim);
		var imgHeight = photo.json.medium_dim.substr(photo.json.medium_dim.lastIndexOf('x') + 1);
		var containerWidth = $(window).outerWidth();
		var containerHeight = $(window).outerHeight();

		// Image can be no larger than its natural size, but it can be
		// smaller depending on the size of the window.
		var width = imgWidth < containerWidth ? imgWidth : containerWidth;
		var height = width * imgHeight / imgWidth;
		if (height > containerHeight) {
			width = containerHeight * imgWidth / imgHeight;
		}

		$('img#image').attr('sizes', width + 'px');
	}

};

view.settings = {

	init: function init() {

		multiselect.clearSelection();

		view.settings.title();
		view.settings.content.init();
	},

	title: function title() {

		lychee.setTitle(lychee.locale['SETTINGS'], false);
	},

	clearContent: function clearContent() {
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
				view.settings.content.setDefaultLicense();
				view.settings.content.setLayout();
				view.settings.content.setPublicSearch();
				view.settings.content.setOverlay();
				view.settings.content.setOverlayType();
				view.settings.content.setMapDisplay();
				view.settings.content.setCSS();
				view.settings.content.moreButton();
			}
		},

		setLogin: function setLogin() {
			var msg = "\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['PASSWORD_TITLE'] + "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='" + lychee.locale['USERNAME_CURRENT'] + "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='" + lychee.locale['PASSWORD_CURRENT'] + "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale['PASSWORD_TEXT'] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale['LOGIN_USERNAME'] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD'] + "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='" + lychee.locale['LOGIN_PASSWORD_CONFIRM'] + "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">" + lychee.locale['PASSWORD_CHANGE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			settings.bind('#basicModal__action_password_change', '.setLogin', settings.changeLogin);
		},

		clearLogin: function clearLogin() {
			$('input[name=oldUsername], input[name=oldPassword], input[name=username], input[name=password], input[name=confirm]').val('');
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
		},

		setDefaultLicense: function setDefaultLicense() {
			var msg = "\n\t\t\t<div class=\"setDefaultLicense\">\n\t\t\t<p>" + lychee.locale['DEFAULT_LICENSE'] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t\t<option value=\"none\">" + lychee.locale['PHOTO_LICENSE_NONE'] + "</option>\n\t\t\t\t\t<option value=\"reserved\">" + lychee.locale['PHOTO_RESERVED'] + "</option>\n\t\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t\t<option value=\"CC-BY\">CC Attribution 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<br />\n\t\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">" + lychee.locale['PHOTO_LICENSE_HELP'] + "</a>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_license\" class=\"basicModal__button\">" + lychee.locale['SET_LICENSE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$('select#license').val(lychee.default_license === '' ? 'none' : lychee.default_license);
			settings.bind('#basicModal__action_set_license', '.setDefaultLicense', settings.setDefaultLicense);
		},

		setLayout: function setLayout() {
			var msg = "\n\t\t\t<div class=\"setLayout\">\n\t\t\t<p>" + lychee.locale['LAYOUT_TYPE'] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"layout\" id=\"layout\">\n\t\t\t\t\t<option value=\"0\">" + lychee.locale['LAYOUT_SQUARES'] + "</option>\n\t\t\t\t\t<option value=\"1\">" + lychee.locale['LAYOUT_JUSTIFIED'] + "</option>\n\t\t\t\t\t<option value=\"2\">" + lychee.locale['LAYOUT_UNJUSTIFIED'] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_layout\" class=\"basicModal__button\">" + lychee.locale['SET_LAYOUT'] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$('select#layout').val(lychee.layout);
			settings.bind('#basicModal__action_set_layout', '.setLayout', settings.setLayout);
		},

		setPublicSearch: function setPublicSearch() {
			var msg = "\n\t\t\t<div class=\"setPublicSearch\">\n\t\t\t<p>" + lychee.locale['PUBLIC_SEARCH_TEXT'] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"PublicSearch\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.public_search) $('#PublicSearch').click();

			settings.bind('#PublicSearch', '.setPublicSearch', settings.changePublicSearch);
		},

		setOverlay: function setOverlay() {
			var msg = "\n\t\t\t<div class=\"setOverlay\">\n\t\t\t<p>" + lychee.locale['IMAGE_OVERLAY_TEXT'] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"ImageOverlay\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.image_overlay_default) $('#ImageOverlay').click();

			settings.bind('#ImageOverlay', '.setOverlay', settings.changeImageOverlay);
		},

		setOverlayType: function setOverlayType() {
			var msg = "\n\t\t\t<div class=\"setOverlayType\">\n\t\t\t<p>" + lychee.locale['OVERLAY_TYPE'] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"OverlayType\" id=\"ImgOverlayType\">\n\t\t\t\t\t<option value=\"exif\">" + lychee.locale['OVERLAY_EXIF'] + "</option>\n\t\t\t\t\t<option value=\"desc\">" + lychee.locale['OVERLAY_DESCRIPTION'] + "</option>\n\t\t\t\t\t<option value=\"takedate\">" + lychee.locale['OVERLAY_DATE'] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_overlay_type\" class=\"basicModal__button\">" + lychee.locale['SET_OVERLAY_TYPE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			// Enable based on image_overlay setting
			if (!lychee.image_overlay) $('select#ImgOverlayType').attr('disabled', true);

			$('select#ImgOverlayType').val(!lychee.image_overlay_type_default ? 'exif' : lychee.image_overlay_type_default);
			settings.bind('#basicModal__action_set_overlay_type', '.setOverlayType', settings.setOverlayType);
		},

		setMapDisplay: function setMapDisplay() {
			var msg = "\n\t\t\t<div class=\"setMapDisplay\">\n\t\t\t<p>" + lychee.locale['MAP_DISPLAY_TEXT'] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapDisplay\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display) $('#MapDisplay').click();

			settings.bind('#MapDisplay', '.setMapDisplay', settings.changeMapDisplay);

			msg = "\n\t\t\t<div class=\"setMapDisplayPublic\">\n\t\t\t<p>" + lychee.locale['MAP_DISPLAY_PUBLIC_TEXT'] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"MapDisplayPublic\" type=\"checkbox\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display_public) $('#MapDisplayPublic').click();

			settings.bind('#MapDisplayPublic', '.setMapDisplayPublic', settings.changeMapDisplayPublic);

			msg = "\n\t\t\t<div class=\"setMapProvider\">\n\t\t\t<p>" + lychee.locale['MAP_PROVIDER'] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"MapProvider\" id=\"MapProvider\">\n\t\t\t\t\t<option value=\"Wikimedia\">" + lychee.locale['MAP_PROVIDER_WIKIMEDIA'] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.org\">" + lychee.locale['MAP_PROVIDER_OSM_ORG'] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.de\">" + lychee.locale['MAP_PROVIDER_OSM_DE'] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.fr\">" + lychee.locale['MAP_PROVIDER_OSM_FR'] + "</option>\n\t\t\t\t\t<option value=\"RRZE\">" + lychee.locale['MAP_PROVIDER_RRZE'] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_map_provider\" class=\"basicModal__button\">" + lychee.locale['SET_MAP_PROVIDER'] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$('select#MapProvider').val(!lychee.map_provider ? 'Wikimedia' : lychee.map_provider);
			settings.bind('#basicModal__action_set_map_provider', '.setMapProvider', settings.setMapProvider);

			msg = "\n\t\t\t<div class=\"setMapIncludeSubalbums\">\n\t\t\t<p>" + lychee.locale['MAP_INCLUDE_SUBALBUMS_TEXT'] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapIncludeSubalbums\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_include_subalbums) $('#MapIncludeSubalbums').click();

			settings.bind('#MapIncludeSubalbums', '.setMapIncludeSubalbums', settings.changeMapIncludeSubalbums);
		},

		setCSS: function setCSS() {
			var msg = "\n\t\t\t<div class=\"setCSS\">\n\t\t\t<p>" + lychee.locale['CSS_TEXT'] + "</p>\n\t\t\t<textarea id=\"css\"></textarea>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_css\" class=\"basicModal__button\">" + lychee.locale['CSS_TITLE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			api.get('dist/user.css', function (data) {
				$("#css").html(data);
			});

			settings.bind('#basicModal__action_set_css', '.setCSS', settings.changeCSS);
		},

		moreButton: function moreButton() {
			var msg = lychee.html(_templateObject74, lychee.locale['MORE']);

			$(".settings_view").append(msg);

			$("#basicModal__action_more").on('click', view.full_settings.init);
		}

	}

};

view.full_settings = {

	init: function init() {

		multiselect.clearSelection();

		view.full_settings.title();
		view.full_settings.content.init();
	},

	title: function title() {

		lychee.setTitle('Full Settings', false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {

		init: function init() {
			view.full_settings.clearContent();

			api.post('Settings::getAll', {}, function (data) {

				var msg = lychee.html(_templateObject75, lychee.locale['SETTINGS_WARNING']);

				var prev = '';
				$.each(data, function () {

					if (this.cat && prev !== this.cat) {
						msg += lychee.html(_templateObject76, this.cat);
						prev = this.cat;
					}

					msg += lychee.html(_templateObject77, this.key, this.key, this.value);
				});

				msg += lychee.html(_templateObject78, lychee.locale['SAVE_RISK']);
				$(".settings_view").append(msg);

				settings.bind('#FullSettingsSave_button', '#fullSettings', settings.save);

				$('#fullSettings').on('keypress', function (e) {
					settings.save_enter(e);
				});
			});
		}

	}

};

view.users = {
	init: function init() {

		multiselect.clearSelection();

		view.users.title();
		view.users.content.init();
	},

	title: function title() {

		lychee.setTitle('Users', false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="users_view"></div>');
	},

	content: {

		init: function init() {

			view.users.clearContent();

			if (users.json.length === 0) {
				$(".users_view").append('<div class="users_view_line" style="margin-bottom: 50px;"><p style="text-align: center">User list is empty!</p></div>');
			}

			var html = '';

			html += '<div class="users_view_line">' + '<p>' + '<span class="text">username</span>' + '<span class="text">new password</span>' + '<span class="text_icon" title="Allow uploads">' + build.iconic('data-transfer-upload') + '</span>' + '<span class="text_icon" title="Restricted account">' + build.iconic('lock-locked') + '</span>' + '</p>' + '</div>';

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
			html += '>' + '<p id="UserCreate">' + '<input class="text" name="username" type="text" value="" placeholder="new username" /> ' + '<input class="text" name="password" type="text" placeholder="new password" /> ' + '<span class="choice" title="Allow uploads">' + '<label>' + '<input type="checkbox" name="upload" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + '</label>' + '</span> ' + '<span class="choice" title="Restricted account">' + '<label>' + '<input type="checkbox" name="lock" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + '</label>' + '</span>' + '</p> ' + '<a id="UserCreate_button"  class="basicModal__button basicModal__button_CREATE">Create</a>' + '</div>';
			$(".users_view").append(html);
			settings.bind('#UserCreate_button', '#UserCreate', users.create);
		}
	}
};

view.sharing = {
	init: function init() {

		multiselect.clearSelection();

		view.sharing.title();
		view.sharing.content.init();
	},

	title: function title() {

		lychee.setTitle('Sharing', false);
	},

	clearContent: function clearContent() {
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

			html += "</select>\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic('media-skip-forward') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic('chevron-right') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic('chevron-left') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic('media-skip-backward') + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"albums_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";

			html += "\n\t\t\t<div class=\"sharing_view_line\"><p class=\"with\">with</p></div>\n\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"from\" id=\"user_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">";

			$.each(sharing.json.users, function () {
				html += "<option value=\"" + this.id + "\">" + this.username + "</option>";
			});

			html += "</select>\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic('media-skip-forward') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic('chevron-right') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic('chevron-left') + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic('media-skip-backward') + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"user_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";
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

view.logs = {
	init: function init() {

		multiselect.clearSelection();

		view.logs.title();
		view.logs.content.init();
	},

	title: function title() {

		lychee.setTitle('Logs', false);
	},

	clearContent: function clearContent() {
		var html = '';
		if (lychee.api_V2) {
			html += lychee.html(_templateObject79, lychee.locale['CLEAN_LOGS']);
		}
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);

		$("#Clean_Noise").on('click', function () {
			api.post_raw('Logs::clearNoise', {}, function () {
				view.logs.init();
			});
		});
	},

	content: {
		init: function init() {
			view.logs.clearContent();
			api.post_raw('Logs', {}, function (data) {
				$(".logs_diagnostics_view").html(data);
			});
		}
	}
};

view.diagnostics = {
	init: function init() {

		multiselect.clearSelection();

		view.diagnostics.title('Diagnostics');
		view.diagnostics.content.init();
	},

	title: function title() {

		lychee.setTitle('Diagnostics', false);
	},

	clearContent: function clearContent(update) {
		var html = '';
		if (update > 0) {
			html += '<div class="clear_logs_update">';
			html += lychee.html(_templateObject80, lychee.locale['CHECK_FOR_UPDATE']);
		}
		if (update === 2) {
			html += lychee.html(_templateObject81, lychee.locale['UPDATE_AVAILABLE']);
		}
		if (update > 0) {
			html += '</div>';
		}
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	bind: function bind() {
		$("#Update_Lychee").on('click', function () {
			api.post('Update::Apply', [], function (data) {
				html = view.preify(data, '');
				$("#Update_Lychee").remove();
				$(html).prependTo(".logs_diagnostics_view");
			});
		});

		$("#Check_Update_Lychee").on('click', function () {
			api.post('Update::Check', [], function (data) {
				loadingBar.show('success', data);
				$("#Check_Update_Lychee").remove();
			});
		});
	},

	content: {
		init: function init() {
			view.diagnostics.clearContent(false);

			if (lychee.api_V2) {
				api.post('Diagnostics', {}, function (data) {
					view.diagnostics.clearContent(data.update);
					var html = '';
					var i = void 0;
					html += '<pre>\n\n\n\n';
					html += '    Diagnostics\n' + '    -----------\n';
					for (i = 0; i < data.errors.length; i++) {
						html += '    ' + data.errors[i] + '\n';
					}
					html += '\n' + '    System Information\n' + '    ------------------\n';
					for (i = 0; i < data.infos.length; i++) {
						html += '    ' + data.infos[i] + '\n';
					}
					html += '\n' + '    Config Information\n' + '    ------------------\n';
					for (i = 0; i < data.configs.length; i++) {
						html += '    ' + data.configs[i] + '\n';
					}
					html += '</pre>';

					$(".logs_diagnostics_view").html(html);

					view.diagnostics.bind();
				});
			} else {
				api.post_raw('Diagnostics', {}, function (data) {
					$(".logs_diagnostics_view").html(data);
				});
			}
		}
	}

};

view.update = {
	init: function init() {

		multiselect.clearSelection();

		view.update.title();
		view.update.content.init();
	},

	title: function title() {

		lychee.setTitle('Update', false);
	},

	clearContent: function clearContent() {
		var html = '';
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	content: {
		init: function init() {
			view.update.clearContent();

			// code duplicate
			api.post('Update::Apply', [], function (data) {
				html = view.preify(data, 'logs_diagnostics_view');
				lychee.content.html(html);
			});
		}
	}
};

view.preify = function (data, css) {
	html = '<pre class="' + css + '">';
	if (Array.isArray(data)) {
		for (var i = 0; i < data.length; i++) {
			html += '    ' + data[i] + '\n';
		}
	} else {
		html += '    ' + data;
	}
	html += '</pre>';

	return html;
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

		if (elem == null || elem.length === 0) return false;else return true;
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