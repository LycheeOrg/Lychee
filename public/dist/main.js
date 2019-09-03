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
!function(e,t){if("function"==typeof define&&define.amd)define(["exports"],t);else if("undefined"!=typeof exports)t(exports);else{var o={};t(o),e.bodyScrollLock=o}}(this,function(exports){"use strict";function r(e){if(Array.isArray(e)){for(var t=0,o=Array(e.length);t<e.length;t++)o[t]=e[t];return o}return Array.from(e)}Object.defineProperty(exports,"__esModule",{value:!0});var l=!1;if("undefined"!=typeof window){var e={get passive(){l=!0}};window.addEventListener("testPassive",null,e),window.removeEventListener("testPassive",null,e)}var d="undefined"!=typeof window&&window.navigator&&window.navigator.platform&&/iP(ad|hone|od)/.test(window.navigator.platform),c=[],u=!1,a=-1,s=void 0,v=void 0,f=function(t){return c.some(function(e){return!(!e.options.allowTouchMove||!e.options.allowTouchMove(t))})},m=function(e){var t=e||window.event;return!!f(t.target)||(1<t.touches.length||(t.preventDefault&&t.preventDefault(),!1))},o=function(){setTimeout(function(){void 0!==v&&(document.body.style.paddingRight=v,v=void 0),void 0!==s&&(document.body.style.overflow=s,s=void 0)})};exports.disableBodyScroll=function(i,e){if(d){if(!i)return void console.error("disableBodyScroll unsuccessful - targetElement must be provided when calling disableBodyScroll on IOS devices.");if(i&&!c.some(function(e){return e.targetElement===i})){var t={targetElement:i,options:e||{}};c=[].concat(r(c),[t]),i.ontouchstart=function(e){1===e.targetTouches.length&&(a=e.targetTouches[0].clientY)},i.ontouchmove=function(e){var t,o,n,r;1===e.targetTouches.length&&(o=i,r=(t=e).targetTouches[0].clientY-a,!f(t.target)&&(o&&0===o.scrollTop&&0<r?m(t):(n=o)&&n.scrollHeight-n.scrollTop<=n.clientHeight&&r<0?m(t):t.stopPropagation()))},u||(document.addEventListener("touchmove",m,l?{passive:!1}:void 0),u=!0)}}else{n=e,setTimeout(function(){if(void 0===v){var e=!!n&&!0===n.reserveScrollBarGap,t=window.innerWidth-document.documentElement.clientWidth;e&&0<t&&(v=document.body.style.paddingRight,document.body.style.paddingRight=t+"px")}void 0===s&&(s=document.body.style.overflow,document.body.style.overflow="hidden")});var o={targetElement:i,options:e||{}};c=[].concat(r(c),[o])}var n},exports.clearAllBodyScrollLocks=function(){d?(c.forEach(function(e){e.targetElement.ontouchstart=null,e.targetElement.ontouchmove=null}),u&&(document.removeEventListener("touchmove",m,l?{passive:!1}:void 0),u=!1),c=[],a=-1):(o(),c=[])},exports.enableBodyScroll=function(t){if(d){if(!t)return void console.error("enableBodyScroll unsuccessful - targetElement must be provided when calling enableBodyScroll on IOS devices.");t.ontouchstart=null,t.ontouchmove=null,c=c.filter(function(e){return e.targetElement!==t}),u&&0===c.length&&(document.removeEventListener("touchmove",m,l?{passive:!1}:void 0),u=!1)}else(c=c.filter(function(e){return e.targetElement!==t})).length||o()}});

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
"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='$", "' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='$", "' value='$", "'>"]),
    _templateObject2 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject3 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"]),
    _templateObject5 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"], ["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"]),
    _templateObject6 = _taggedTemplateLiteral(["?albumIDs=", ""], ["?albumIDs=", ""]),
    _templateObject7 = _taggedTemplateLiteral(["<p>", " '$", "' ", " '$", "'?</p>"], ["<p>", " '$", "' ", " '$", "'?</p>"]),
    _templateObject8 = _taggedTemplateLiteral(["<p>", " '$", "'?</p>"], ["<p>", " '$", "'?</p>"]),
    _templateObject9 = _taggedTemplateLiteral(["<p>", " '$", "' ", "</p>"], ["<p>", " '$", "' ", "</p>"]),
    _templateObject10 = _taggedTemplateLiteral(["<p>", " $", " ", "</p>"], ["<p>", " $", " ", "</p>"]),
    _templateObject11 = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject12 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject13 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject14 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject15 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", "' data-id='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>$", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject16 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject17 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject18 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject19 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject20 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject21 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject22 = _taggedTemplateLiteral(["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t\t<div id=\"image_overlay\">\n\t\t\t\t\t\t<h1>$", "</h1>\n\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\">\n\t\t\t\t<h1>$", "</h1>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject24 = _taggedTemplateLiteral(["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div id=\"image_overlay\"><h1>$", "</h1>\n\t\t\t<p>", " at ", ", ", " ", "<br>\n\t\t\t", " ", "</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject25 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autoplay><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autoplay><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject26 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject27 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject28 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject29 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject30 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject31 = _taggedTemplateLiteral(["<a class='tag'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='tag'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject32 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject33 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject34 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject35 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject36 = _taggedTemplateLiteral(["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" >&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"], ["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" >&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"]),
    _templateObject37 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_users\">", " ", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\">", " ", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_users\">", " ", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\">", " ", "</a>"]),
    _templateObject38 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_logs\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\">", " ", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_logs\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\">", " ", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\">", " ", "</a>"]),
    _templateObject39 = _taggedTemplateLiteral(["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "], ["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "]),
    _templateObject40 = _taggedTemplateLiteral(["\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "'>", "</a><span></p>\n\t\t\t"], ["\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "'>", "</a><span></p>\n\t\t\t"]),
    _templateObject41 = _taggedTemplateLiteral(["<p>", " '", "' ", "</p>"], ["<p>", " '", "' ", "</p>"]),
    _templateObject42 = _taggedTemplateLiteral(["<p>", " ", " ", "</p>"], ["<p>", " ", " ", "</p>"]),
    _templateObject43 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='50' placeholder='Title' value='$", "'>"]),
    _templateObject44 = _taggedTemplateLiteral(["<p>", " ", " ", " ", "</p>"], ["<p>", " ", " ", " ", "</p>"]),
    _templateObject45 = _taggedTemplateLiteral(["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"], ["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"]),
    _templateObject46 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"]),
    _templateObject47 = _taggedTemplateLiteral(["?photoIDs=", "&kind=", ""], ["?photoIDs=", "&kind=", ""]),
    _templateObject48 = _taggedTemplateLiteral(["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"], ["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"]),
    _templateObject49 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject50 = _taggedTemplateLiteral(["\n\t\t\t\t\t <tr>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t </tr>\n\t\t\t\t\t "], ["\n\t\t\t\t\t <tr>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t </tr>\n\t\t\t\t\t "]),
    _templateObject51 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject52 = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject53 = _taggedTemplateLiteral(["url(\"", "\")"], ["url(\"", "\")"]),
    _templateObject54 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]),
    _templateObject55 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"]),
    _templateObject56 = _taggedTemplateLiteral(["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject57 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"]),
    _templateObject58 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject59 = _taggedTemplateLiteral(["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"], ["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"]),
    _templateObject60 = _taggedTemplateLiteral(["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"], ["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"]),
    _templateObject61 = _taggedTemplateLiteral(["<div class=\"clear_logs_update\"><a id=\"Update_Lychee\" class=\"basicModal__button\">", "</a></div>"], ["<div class=\"clear_logs_update\"><a id=\"Update_Lychee\" class=\"basicModal__button\">", "</a></div>"]);

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
		dataType: 'json',
		success: success,
		error: error
	});
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

album.hasSub = function (albumID) {

	// Return true if the current album has albumID as its descendant

	if (albumID == null || !album.json || !album.json.albums) {
		return false;
	}

	var ret = false;

	var func = function func() {
		if (parseInt(this.id, 10) === parseInt(albumID, 10)) {
			ret = true;
			return false;
		}
		if (this.albums) {
			$.each(this.albums, func);
		}
	};

	$.each(album.json.albums, func);

	return ret;
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

	var input = lychee.html(_templateObject, lychee.locale['ALBUM_TITLE'], oldTitle);

	if (albumIDs.length === 1) msg = lychee.html(_templateObject2, lychee.locale['ALBUM_NEW_TITLE'], input);else msg = lychee.html(_templateObject3, lychee.locale['ALBUMS_NEW_TITLE_1'], albumIDs.length, lychee.locale['ALBUMS_NEW_TITLE_2'], input);

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
		body: lychee.html(_templateObject4, lychee.locale['ALBUM_NEW_DESCRIPTION'], lychee.locale['ALBUM_DESCRIPTION'], oldDescription),
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

	var msg = lychee.html(_templateObject5, lychee.locale['ALBUM_LICENSE'], lychee.locale['ALBUM_LICENSE_NONE'], lychee.locale['ALBUM_RESERVED'], lychee.locale['ALBUM_LICENSE_HELP']);

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

		var msg = "\n\t\t\t<form>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t" + lychee.locale['ALBUM_PUBLIC'] + ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>" + lychee.locale['ALBUM_PUBLIC_EXPL'] + "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='full_photo'>\n\t\t\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t<span class='label'>" + lychee.locale['ALBUM_FULL'] + "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>" + lychee.locale['ALBUM_FULL_EXPL'] + "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='hidden'>\n\t\t\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t<span class='label'>" + lychee.locale['ALBUM_HIDDEN'] + "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>" + lychee.locale['ALBUM_HIDDEN_EXPL'] + "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t<span class='label'>" + lychee.locale['ALBUM_DOWNLOADABLE'] + "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>" + lychee.locale['ALBUM_DOWNLOADABLE_EXPL'] + "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='password'>\n\t\t\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t\t\t<span class='label'>" + lychee.locale['ALBUM_PASSWORD_PROT'] + "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>" + lychee.locale['ALBUM_PASSWORD_PROT_EXPL'] + "</p>\n\t\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='" + lychee.locale['PASSWORD'] + "' value=''>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t";

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
		view.album.password();
	}

	var params = {
		albumID: albumID,
		full_photo: album.json.full_photo,
		public: album.json.public,
		visible: album.json.visible,
		downloadable: album.json.downloadable
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

album.getArchive = function (albumIDs) {

	var link = '';

	// double check with API_V2 this will not work...
	if (lychee.api_V2) {
		location.href = api.get_url('Album::getArchive') + lychee.html(_templateObject6, albumIDs.join());
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

		msg = lychee.html(_templateObject7, lychee.locale[op1], sTitle, lychee.locale[op2], title);
	} else {

		msg = lychee.html(_templateObject8, lychee.locale[ops], title);
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

		msg = lychee.html(_templateObject9, lychee.locale['DELETE_ALBUM_CONFIRMATION_1'], albumTitle, lychee.locale['DELETE_ALBUM_CONFIRMATION_2']);
	} else {

		action.title = lychee.locale['DELETE_ALBUMS_QUESTION'];
		cancel.title = lychee.locale['KEEP_ALBUMS'];

		msg = lychee.html(_templateObject10, lychee.locale['DELETE_ALBUMS_CONFIRMATION_1'], albumIDs.length, lychee.locale['DELETE_ALBUMS_CONFIRMATION_2']);
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

	html += lychee.html(_templateObject11, classes, icon);

	return html;
};

build.divider = function (title) {

	var html = '';

	html += lychee.html(_templateObject12, title);

	return html;
};

build.editIcon = function (id) {

	var html = '';

	html += lychee.html(_templateObject13, id, build.iconic('pencil'));

	return html;
};

build.multiselect = function (top, left) {

	return lychee.html(_templateObject14, top, left);
};

build.getAlbumThumb = function (data, i) {
	var isVideo = data.types[i] && data.types[i].indexOf('video') > -1;
	var thumb = data.thumbs[i];

	if (thumb === 'uploads/thumb/' && isVideo) {
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
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

	html += lychee.html(_templateObject15, disabled ? "disabled" : "", data.id, build.getAlbumThumb(data, 2), build.getAlbumThumb(data, 1), build.getAlbumThumb(data, 0), data.title, data.title, date_stamp);

	if (album.isUploadable() && !disabled) {

		html += lychee.html(_templateObject16, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' ? 'badge--visible' : '', data.visible === '1' ? 'badge--not--hidden' : 'badge--hidden', build.iconic('eye'), data.unsorted === '1' ? 'badge--visible' : '', build.iconic('list'), data.recent === '1' ? 'badge--visible badge--list' : '', build.iconic('clock'), data.password === '1' ? 'badge--visible' : '', build.iconic('lock-locked'));
	}

	if (data.albums && data.albums.length > 0 || data.hasOwnProperty('has_albums') && data.has_albums === '1') {
		html += lychee.html(_templateObject17, build.iconic('layers'));
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
	if (data.thumbUrl === 'uploads/thumb/' && isVideo) {
		thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
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

		thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.thumbUrl + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
		thumbnail += "</span>";
	} else {

		if (data.small !== '') {
			if (data.hasOwnProperty('small2x') && data.small2x !== '') {
				thumb2x = "data-srcset='" + data.small + " " + parseInt(data.small_dim, 10) + "w, " + data.small2x + " " + parseInt(data.small2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.small + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
			thumbnail += "</span>";
		} else if (data.medium !== '') {
			if (data.hasOwnProperty('medium2x') && data.medium2x !== '') {
				thumb2x = "data-srcset='" + data.medium + " " + parseInt(data.medium_dim, 10) + "w, " + data.medium2x + " " + parseInt(data.medium2x_dim, 10) + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? ' video' : '') + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.medium + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false'>";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg\">";
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

	html += lychee.html(_templateObject18, disabled ? "disabled" : "", data.album, data.id, thumbnail, data.title, data.title);

	if (data.cameraDate === '1') html += lychee.html(_templateObject19, build.iconic('camera-slr'), data.takedate);else html += lychee.html(_templateObject20, data.sysdate);

	html += "</div>";

	if (album.isUploadable()) {

		html += lychee.html(_templateObject21, data.star === '1' ? 'badge--star' : '', build.iconic('star'), data.public === '1' && album.json.public !== '1' ? 'badge--visible badge--hidden' : '', build.iconic('eye'));
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
		html = lychee.html(_templateObject22, data.title, data.description);
	} else if (type && type === 'takedate' && data.takedate !== '') {
		html = lychee.html(_templateObject23, data.title, data.takedate);
	}
	// fall back to exif data if there is no description
	else if (exifHash !== '') {

			html += lychee.html(_templateObject24, data.title, data.shutter.replace('s', 'sec'), data.aperture.replace('f/', '&fnof; / '), lychee.locale['PHOTO_ISO'], data.iso, data.focal, data.lens && data.lens !== '' ? '(' + data.lens + ')' : '');
		}

	return html;
};

build.imageview = function (data, visibleControls) {

	var html = '';
	var thumb = '';

	if (data.type.indexOf('video') > -1) {
		html += lychee.html(_templateObject25, visibleControls === true ? '' : 'full', data.url);
	} else {
		var img = '';

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
		html += lychee.html(_templateObject26, img);

		if (lychee.image_overlay) html += build.overlay_image(data);
	}

	html += "\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic('caret-left') + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic('caret-right') + "</a></div>\n\t\t\t";

	return { html: html, thumb: thumb };
};

build.no_content = function (typ) {

	var html = '';

	html += lychee.html(_templateObject27, build.iconic(typ));

	switch (typ) {
		case 'magnifying-glass':
			html += lychee.html(_templateObject28, lychee.locale['VIEW_NO_RESULT']);
			break;
		case 'eye':
			html += lychee.html(_templateObject28, lychee.locale['VIEW_NO_PUBLIC_ALBUMS']);
			break;
		case 'cog':
			html += lychee.html(_templateObject28, lychee.locale['VIEW_NO_CONFIGURATION']);
			break;
		case 'question-mark':
			html += lychee.html(_templateObject28, lychee.locale['VIEW_PHOTO_NOT_FOUND']);
			break;
	}

	html += "</div>";

	return html;
};

build.uploadModal = function (title, files) {

	var html = '';

	html += lychee.html(_templateObject29, title);

	var i = 0;

	while (i < files.length) {

		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + '...' + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject30, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

build.tags = function (tags) {

	var html = '';

	if (tags !== '') {

		tags = tags.split(',');

		tags.forEach(function (tag, index) {
			html += lychee.html(_templateObject31, tag, index, build.iconic('x'));
		});
	} else {

		html = lychee.html(_templateObject32, lychee.locale['NO_TAGS']);
	}

	return html;
};

build.user = function (user) {
	var html = lychee.html(_templateObject33, user.id, user.id, user.username, user.id, user.id);

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

				var html = lychee.html(_templateObject34, prefix, thumb, item.title);

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
		} }, { title: build.iconic('layers') + lychee.locale['DUPLICATE'], fn: function fn() {
			return photo.duplicate([photoID]);
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
		} }, { title: build.iconic('layers') + lychee.locale['DUPLICATE_ALL'], fn: function fn() {
			return photo.duplicate(photoIDs);
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
				if (callback !== album.merge) {
					// For merging, don't exclude the parent.
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

		items.unshift({});
		items.unshift({ title: lychee.locale['NEW_ALBUM'], fn: function fn() {
				return album.add(IDs, callback);
			} });

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.sharePhoto = function (photoID, e) {

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
	header.dom('#button_fs_album_enter,#button_fs_enter').on(eventName, lychee.fullscreenEnter);
	header.dom('#button_fs_album_exit,#button_fs_exit').on(eventName, lychee.fullscreenExit).hide();

	header.dom('.header__search').on('keyup click', function () {
		search.find($(this).val());
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
	var html = lychee.html(_templateObject35, title, build.iconic('caret-bottom'));

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
			if (lychee.public_search) {
				$('.header__search, .header__clear', '.header__toolbar--public').show();
			} else {
				$('.header__search, .header__clear', '.header__toolbar--public').hide();
			}

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

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos === false || !album.isUploadable() && album.json.downloadable === '0') {
				$('#button_archive').hide();
			} else {
				$('#button_archive').show();
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
			header.dom('.header__toolbar--public, .header__toolbar--albums, .header__toolbar--album').removeClass('header__toolbar--visible');
			header.dom('.header__toolbar--photo').addClass('header__toolbar--visible');
			if (album.isUploadable()) {
				$('#button_trash, #button_move, #button_visibility, #button_star').show();
			} else {
				$('#button_trash, #button_move, #button_visibility, #button_star').hide();
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$('#button_more').show();
			if (!(album.isUploadable() || (photo.json.hasOwnProperty('downloadable') ? photo.json.downloadable === '1' : album.json && album.json.downloadable && album.json.downloadable === '1')) && !(photo.json.url && photo.json.url !== '')) {
				$('#button_more').hide();
			}

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
	lychee.imageview.on(eventName, '.arrow_wrapper--previous', photo.previous).on(eventName, '.arrow_wrapper--next', photo.next).on('click', 'img', photo.update_display_overlay);

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
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album() && !album.json.parent_id) lychee.goto();else if (visible.album()) lychee.goto(album.getParent());else if (visible.albums() && search.hash !== null) search.reset();
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

	// Upload
	.on('change', '#upload_files', function () {
		basicModal.close();upload.start.local(this.files);
	})

	// Drag and Drop upload
	.on('dragover', function () {
		return false;
	}, false).on('drop', function (e) {

		if (!album.isUploadable()) {
			return false;
		}

		// Close open overlays or views which are correlating with the upload
		if (visible.photo()) lychee.goto(album.getID());
		if (visible.contextMenu()) contextMenu.close();

		// Detect if dropped item is a file or a link
		if (e.originalEvent.dataTransfer.files.length > 0) upload.start.local(e.originalEvent.dataTransfer.files);else if (e.originalEvent.dataTransfer.getData('Text').length > 3) upload.start.url(e.originalEvent.dataTransfer.getData('Text'));

		return false;
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
	var html = lychee.html(_templateObject36, lychee.locale['CLOSE'], lychee.locale['SETTINGS']);
	if (lychee.api_V2) {
		html += lychee.html(_templateObject37, build.iconic('person'), lychee.locale['USERS'], build.iconic('cloud'), lychee.locale['SHARING']);
	}
	html += lychee.html(_templateObject38, build.iconic('align-left'), lychee.locale['LOGS'], build.iconic('wrench'), lychee.locale['DIAGNOSTICS'], build.iconic('info'), lychee.locale['ABOUT_LYCHEE'], build.iconic('account-logout'), lychee.locale['SIGN_OUT']);
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
	landing_page_enabled: false, // is landing page enabled ?

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

	var msg = lychee.html(_templateObject39, lychee.version, lychee.updateURL, lychee.locale['UPDATE_AVAILABLE'], lychee.locale['ABOUT_SUBTITLE'], lychee.website, lychee.locale['ABOUT_DESCRIPTION']);

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
			lychee.default_license = data.config.default_license || 'none';
			lychee.css = data.config.css || '';
			lychee.full_photo = data.config.full_photo == null || data.config.full_photo === '1';
			lychee.downloadable = data.config.downloadable && data.config.downloadable === '1' || false;

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

			lychee.sortingPhotos = data.config.sorting_Photos || data.config.sortingPhotos || '';
			lychee.sortingAlbums = data.config.sorting_Albums || data.config.sortingAlbums || '';
			lychee.checkForUpdates = data.config.check_for_updates || data.config.checkForUpdates || '1';
			lychee.layout = data.config.layout || '1';
			lychee.public_search = data.config.public_search && data.config.public_search === '1' || false;
			lychee.image_overlay = data.config.image_overlay && data.config.image_overlay === '1' || false;
			lychee.image_overlay_type = !data.config.image_overlay_type ? 'exif' : data.config.image_overlay_type;
			lychee.image_overlay_type_default = lychee.image_overlay_type;

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

	var msg = lychee.html(_templateObject40, lychee.locale['USERNAME'], lychee.locale['PASSWORD'], lychee.version, lychee.updateURL, lychee.locale['UPDATE_AVAILABLE']);

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
		lychee.footer_hide();
	} else if (albumID) {

		// Trash data
		photo.json = null;

		// Show Album
		if (visible.photo()) view.photo.hide();
		if (visible.sidebar() && (albumID === '0' || albumID === 'f' || albumID === 's' || albumID === 'r')) sidebar.toggle();
		if (album.json && albumID === album.json.id) view.album.title();else album.load(albumID);
		lychee.footer_show();
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
	'DEFAULT_LICENSE': 'Default License for new uploads:',
	'SET_LICENSE': 'Set License',
	'SET_OVERLAY_TYPE': 'Set Overlay',
	'SAVE_RISK': 'Save my modifications, I accept the Risk!',
	'MORE': 'More',

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
	'PHOTO_PASSWORD_PROT': 'Password protected',
	'PHOTO_PASSWORD_PROT_EXPL': 'Photo only accessible with a valid password.',
	'PHOTO_EDIT_SHARING_TEXT': 'The sharing properties of this photo will be changed to the following:',
	'PHOTO_NO_EDIT_SHARING_TEXT': 'Because this photo is located in a public album, it inherits that album\'s visibility settings.  Its current visibility is shown below for informational purposes only.',
	'PHOTO_EDIT_GLOBAL_SHARING_TEXT': 'The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.',
	'PHOTO_SHARING_CONFIRM': 'Save',

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

	'SETTINGS_WARNING': 'Changing these advanced settings can be harmful to the stability, security and performance of this application. You should only modify them if you are sure of what you are doing.',
	'SETTINGS_SUCCESS_LOGIN': 'Login Info updated.',
	'SETTINGS_SUCCESS_SORT': 'Sorting order updated.',
	'SETTINGS_SUCCESS_DROPBOX': 'Dropbox Key updated.',
	'SETTINGS_SUCCESS_LANG': 'Language updated',
	'SETTINGS_SUCCESS_LAYOUT': 'Layout updated',
	'SETTINGS_SUCCESS_IMAGE_OVERLAY': 'EXIF Overlay setting updated',
	'SETTINGS_SUCCESS_PUBLIC_SEARCH': 'Public search updated',
	'SETTINGS_SUCCESS_LICENSE': 'Default license updated',
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
	'PHOTO_VIEW': 'Lychee Photo View:'
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
	supportsPrefetch: null

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
		view.photo.init();
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
				$('head').append("<link data-prefetch rel=\"prefetch\" href=\"" + href + "\">");
			} else {
				// According to https://caniuse.com/#feat=link-rel-prefetch,
				// as of mid-2019 it's mainly Safari (both on desktop and mobile)
				new Image().src = href;
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
	var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;


	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	albums.refresh();

	var params = {
		photoIDs: photoIDs.join()
	};

	api.post('Photo::duplicate', params, function (data) {

		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			album.load(album.getID());
			if (callback != null) {
				callback();
			}
		}
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

		msg = lychee.html(_templateObject41, lychee.locale['PHOTO_DELETE_1'], photoTitle, lychee.locale['PHOTO_DELETE_2']);
	} else {

		action.title = lychee.locale['PHOTO_DELETE'];
		cancel.title = lychee.locale['PHOTO_KEEP'];

		msg = lychee.html(_templateObject42, lychee.locale['PHOTO_DELETE_ALL_1'], photoIDs.length, lychee.locale['PHOTO_DELETE_ALL_2']);
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

	var input = lychee.html(_templateObject43, oldTitle);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject2, lychee.locale['PHOTO_NEW_TITLE'], input);else msg = lychee.html(_templateObject44, lychee.locale['PHOTOS_NEW_TITLE_1'], photoIDs.length, lychee.locale['PHOTOS_NEW_TITLE_2'], input);

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

	var action = function action() {
		photo.setAlbum(photoIDs, albumID);
	};
	photo.duplicate(photoIDs, action);
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
			if (album.hasSub(albumID)) {
				// If we moved photos to a subalbum of the currently
				// displayed album, that may change the subalbum thumbs
				// being displayed so we need to reload.
				if (visible.album()) {
					album.reload();
				} else {
					// We're most likely in photo view.  We still need to
					// refresh the album but we don't want to reload it
					// since that would switch the view being displayed.
					album.refresh();
				}
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

	var msg_switch = "\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>" + lychee.locale['PHOTO_PUBLIC'] + ":</span>\n\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>" + lychee.locale['PHOTO_PUBLIC_EXPL'] + "</p>\n\t\t</div>\n\t";

	var msg_choices = "\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='full_photo' disabled>\n\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t<span class='label'>" + lychee.locale['PHOTO_FULL'] + "</span>\n\t\t\t</label>\n\t\t\t<p>" + lychee.locale['PHOTO_FULL_EXPL'] + "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='hidden' disabled>\n\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t<span class='label'>" + lychee.locale['PHOTO_HIDDEN'] + "</span>\n\t\t\t</label>\n\t\t\t<p>" + lychee.locale['PHOTO_HIDDEN_EXPL'] + "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='downloadable' disabled>\n\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t<span class='label'>" + lychee.locale['PHOTO_DOWNLOADABLE'] + "</span>\n\t\t\t</label>\n\t\t\t<p>" + lychee.locale['PHOTO_DOWNLOADABLE_EXPL'] + "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='password' disabled>\n\t\t\t\t<span class='checkbox'>" + build.iconic('check') + "</span>\n\t\t\t\t<span class='label'>" + lychee.locale['PHOTO_PASSWORD_PROT'] + "</span>\n\t\t\t</label>\n\t\t\t<p>" + lychee.locale['PHOTO_PASSWORD_PROT_EXPL'] + "</p>\n\t\t</div>\n\t";

	if (photo.json.public === '2') {
		// Public album. We can't actually change anything but we will
		// display the current settings.

		var msg = "\n\t\t\t<p class='less'>" + lychee.locale['PHOTO_NO_EDIT_SHARING_TEXT'] + "</p>\n\t\t\t" + msg_switch + "\n\t\t\t" + msg_choices + "\n\t\t";

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

		var _msg = "\n\t\t\t" + msg_switch + "\n\t\t\t<p class='photoPublic'>" + lychee.locale['PHOTO_EDIT_GLOBAL_SHARING_TEXT'] + "</p>\n\t\t\t" + msg_choices + "\n\t\t";

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
		body: lychee.html(_templateObject45, lychee.locale['PHOTO_NEW_DESCRIPTION'], lychee.locale['PHOTO_DESCRIPTION'], oldDescription),
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

	var input = lychee.html(_templateObject46, oldTags);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject2, lychee.locale['PHOTO_NEW_TAGS'], input);else msg = lychee.html(_templateObject44, lychee.locale['PHOTO_NEW_TAGS_1'], photoIDs.length, lychee.locale['PHOTO_NEW_TAGS_2'], input);

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

	var msg = lychee.html(_templateObject5, lychee.locale['PHOTO_LICENSE'], lychee.locale['PHOTO_LICENSE_NONE'], lychee.locale['PHOTO_RESERVED'], lychee.locale['PHOTO_LICENSE_HELP']);

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
			return "\n\t\t\t\t<a class='basicModal__button' id='" + id + "' title='" + lychee.locale['DOWNLOAD'] + "'>\n\t\t\t\t\t" + build.iconic('cloud-download') + label + "\n\t\t\t\t</a>\n\t\t\t";
		};

		var msg = "\n\t\t\t<div class='downloads'>\n\t\t";

		if (myPhoto.url) {
			msg += buildButton('FULL', lychee.locale['PHOTO_FULL'] + " (" + myPhoto.width + "x" + myPhoto.height + ", " + myPhoto.size + ")");
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

		msg += "\n\t\t\t</div>\n\t\t";

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
		location.href = api.get_url('Photo::getArchive') + lychee.html(_templateObject47, photoIDs.join(), kind);
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
		return "\n\t\t\t<p>\n\t\t\t\t" + label + "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='" + url + "'>\n\t\t\t\t<a class='basicModal__button' title='" + lychee.locale['URL_COPY_TO_CLIPBOARD'] + "'>\n\t\t\t\t\t" + build.iconic('copy', 'ionicons') + "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t";
	};

	var msg = "\n\t\t<div class='directLinks'>\n\t\t\t" + buildLine(lychee.locale['PHOTO_VIEW'], photo.getViewLink(photoID)) + "\n\t\t\t<p class='less'>\n\t\t\t\t" + lychee.locale['PHOTO_DIRECT_LINKS_TO_IMAGES'] + "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t";

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

	msg += "\n\t\t</div>\n\t\t</div>\n\t";

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

						if (html === 'error') {
							lychee.content.html('');
							$('body').append(build.no_content('magnifying-glass'));
						} else {
							lychee.content.html(html);
							view.album.content.justify();
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
		action.msg = lychee.html(_templateObject48, lychee.locale['SAVE_RISK']);

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

	sidebar.dom('#tags .tag span').off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data('index'));
	});

	sidebar.dom('#edit_license').off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setLicense(photo.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	return true;
};

sidebar.toggle = function () {

	if (visible.sidebar() || visible.sidebarbutton()) {

		header.dom('.button--info').toggleClass('active');
		lychee.content.toggleClass('content--sidebar');
		lychee.imageview.toggleClass('image--sidebar');
		if (typeof view !== 'undefined') view.album.content.justify();
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

	// Only create tags section when the photo is editable
	if (editable) {

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
	if (exifHash !== '') {

		structure.exif = {
			title: lychee.locale['PHOTO_CAMERA'],
			type: sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale['PHOTO_CAPTURED'], kind: 'takedate', value: data.takedate }] : [{ title: lychee.locale['PHOTO_CAPTURED'], kind: 'takedate', value: data.takedate }, { title: lychee.locale['PHOTO_MAKE'], kind: 'make', value: data.make }, { title: lychee.locale['PHOTO_TYPE'], kind: 'model', value: data.model }, { title: lychee.locale['PHOTO_LENS'], kind: 'lens', value: data.lens }, { title: lychee.locale['PHOTO_SHUTTER'], kind: 'shutter', value: data.shutter }, { title: lychee.locale['PHOTO_APERTURE'], kind: 'aperture', value: data.aperture }, { title: lychee.locale['PHOTO_FOCAL'], kind: 'focal', value: data.focal }, { title: lychee.locale['PHOTO_ISO'], kind: 'iso', value: data.iso }]
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

	// Construct all parts of the structure
	structure = [structure.basics, structure.image, structure.tags, structure.exif, structure.sharing, structure.license];

	return structure;
};

sidebar.createStructure.album = function (data) {

	if (data == null || data === '') return false;

	var editable = album.isUploadable();
	var structure = {};
	var _public = '';
	var hidden = '';
	var downloadable = '';
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
		rows: [{ title: lychee.locale['ALBUM_PUBLIC'], kind: 'public', value: _public }, { title: lychee.locale['ALBUM_HIDDEN'], kind: 'hidden', value: hidden }, { title: lychee.locale['ALBUM_DOWNLOADABLE'], kind: 'downloadable', value: downloadable }, { title: lychee.locale['ALBUM_PASSWORD'], kind: 'password', value: password }]
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
			value = lychee.html(_templateObject49, row.kind, value);

			// Add edit-icon to the value when editable
			if (row.editable === true) value += ' ' + build.editIcon('edit_' + row.kind);

			_html += lychee.html(_templateObject50, row.title, value);
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	var renderTags = function renderTags(section) {

		var _html = '';
		var editable = '';

		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon('edit_tags');

		_html += lychee.html(_templateObject51, section.title, section.title.toLowerCase(), section.value, editable);

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
			body: lychee.html(_templateObject52) + lychee.locale['UPLOAD_IMPORT_INSTR'] + (" <input class='text' name='link' type='text' placeholder='http://' value='" + _url + "'></p>"),
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
			body: lychee.html(_templateObject52) + lychee.locale['UPLOAD_IMPORT_SERVER_INSTR'] + " <input class='text' name='path' type='text' maxlength='100' placeholder='" + lychee.locale['UPLOAD_ABSOLUTE_PATH'] + ("' value='" + lychee.location + "uploads/import/'></p>"),
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


			if (album.json && album.json.num) {
				album.json.num--;
			}
			$('.photo[data-id="' + photoID + '"]').css('opacity', 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				// Only when search is not active
				if (album.json) {
					if (album.json.num) {
						view.album.num();
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
				if (album.json && album.json.albums.length <= 0) lychee.content.find('.divider').remove();
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

	num: function num() {

		sidebar.changeAttr('images', album.json.num);
	},

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

		multiselect.clearSelection();

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
		// use bodyScrollLock package to enable locking on iOS
		// Simple overflow: hidden not working on iOS Safari
		bodyScrollLock.disableBodyScroll(lychee.imageview);

		// Fullscreen
		var timeout = null;
		$(document).bind('mousemove', function () {
			clearTimeout(timeout);
			header.show();
			timeout = setTimeout(header.hide, 2500);
		});

		// we also put this timeout to enable it by default when you directly click on a picture.
		setTimeout(header.hide, 2500);

		lychee.animate(lychee.imageview, 'fadeIn');
	},

	hide: function hide() {

		header.show();

		lychee.content.removeClass('view');
		header.setMode('album');

		// Make body scrollable
		bodyScrollLock.enableBodyScroll(lychee.imageview);

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
		function photo() {
			return _photo.apply(this, arguments);
		}

		photo.toString = function () {
			return _photo.toString();
		};

		return photo;
	}(function () {

		var ret = build.imageview(photo.json, visible.header());
		lychee.imageview.html(ret.html);
		view.photo.onresize();

		var $nextArrow = lychee.imageview.find('a#next');
		var $previousArrow = lychee.imageview.find('a#previous');
		var photoID = photo.getID();
		var hasNext = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).nextPhoto != null && album.getByID(photoID).nextPhoto !== '';
		var hasPrevious = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).previousPhoto != null && album.getByID(photoID).previousPhoto !== '';

		var img = $('img#image');
		if (img.length > 0) {
			if (!img[0].complete) {
				// Image is still loading.  Display the thumb version in the
				// background.
				if (ret.thumb !== '') {
					img.css('background-image', lychee.html(_templateObject53, ret.thumb));
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

			$nextArrow.css('background-image', lychee.html(_templateObject54, nextPhoto.thumbUrl));
		}

		if (hasPrevious === false || lychee.viewMode === true) {

			$previousArrow.hide();
		} else {

			var previousPhotoID = album.getByID(photoID).previousPhoto;
			var previousPhoto = album.getByID(previousPhotoID);

			$previousArrow.css('background-image', lychee.html(_templateObject54, previousPhoto.thumbUrl));
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

		lychee.setTitle('Settings', false);
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

		setCSS: function setCSS() {
			var msg = "\n\t\t\t<div class=\"setCSS\">\n\t\t\t<p>" + lychee.locale['CSS_TEXT'] + "</p>\n\t\t\t<textarea id=\"css\"></textarea>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_css\" class=\"basicModal__button\">" + lychee.locale['CSS_TITLE'] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			api.get('dist/user.css', function (data) {
				$("#css").html(data);
			});

			settings.bind('#basicModal__action_set_css', '.setCSS', settings.changeCSS);
		},

		moreButton: function moreButton() {
			var msg = lychee.html(_templateObject55, lychee.locale['MORE']);

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

				var msg = lychee.html(_templateObject56, lychee.locale['SETTINGS_WARNING']);

				var prev = '';
				$.each(data, function () {

					if (this.cat && prev !== this.cat) {
						msg += lychee.html(_templateObject57, this.cat);
						prev = this.cat;
					}

					msg += lychee.html(_templateObject58, this.key, this.key, this.value);
				});

				msg += lychee.html(_templateObject59, lychee.locale['SAVE_RISK']);
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
			html += lychee.html(_templateObject60, lychee.locale['CLEAN_LOGS']);
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
		if (update === 1) {
			html += lychee.html(_templateObject61, lychee.locale['UPDATE_AVAILABLE']);
		}
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);

		$("#Update_Lychee").on('click', function () {
			api.get('api/Update', function (data) {
				var data_json = void 0;
				try {
					data_json = JSON.parse(data);
				} catch (e) {
					data_json = "JSON error. Check the console logs.";
					console.log(data);
				}
				html = '<pre>';
				if (Array.isArray(data_json)) {
					for (var i = 0; i < data_json.length; i++) {
						html += '    ' + data_json[i] + '\n';
					}
				} else {
					html += '    ' + data_json;
				}
				html += '</pre>';
				$(html).prependTo(".logs_diagnostics_view");
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
				});
			} else {
				api.post_raw('Diagnostics', {}, function (data) {
					$(".logs_diagnostics_view").html(data);
				});
			}
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