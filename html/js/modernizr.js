/*! modernizr 3.3.1 (Custom Build) | MIT *
 * http://modernizr.com/download/?-csshyphens_softhyphens_softhyphensfind-setclasses !*/
!function(e,t,n){function i(e,t){return typeof e===t}function o(){var e,t,n,o,r,a,s;for(var l in k)if(k.hasOwnProperty(l)){if(e=[],t=k[l],t.name&&(e.push(t.name.toLowerCase()),t.options&&t.options.aliases&&t.options.aliases.length))for(n=0;n<t.options.aliases.length;n++)e.push(t.options.aliases[n].toLowerCase());for(o=i(t.fn,"function")?t.fn():t.fn,r=0;r<e.length;r++)a=e[r],s=a.split("."),1===s.length?Modernizr[s[0]]=o:(!Modernizr[s[0]]||Modernizr[s[0]]instanceof Boolean||(Modernizr[s[0]]=new Boolean(Modernizr[s[0]])),Modernizr[s[0]][s[1]]=o),v.push((o?"":"no-")+s.join("-"))}}function r(e){var t=C.className,n=Modernizr._config.classPrefix||"";if(x&&(t=t.baseVal),Modernizr._config.enableJSClass){var i=new RegExp("(^|\\s)"+n+"no-js(\\s|$)");t=t.replace(i,"$1"+n+"js$2")}Modernizr._config.enableClasses&&(t+=" "+n+e.join(" "+n),x?C.className.baseVal=t:C.className=t)}function a(){return"function"!=typeof t.createElement?t.createElement(arguments[0]):x?t.createElementNS.call(t,"http://www.w3.org/2000/svg",arguments[0]):t.createElement.apply(t,arguments)}function s(e,t){if("object"==typeof e)for(var n in e)T(e,n)&&s(n,e[n]);else{e=e.toLowerCase();var i=e.split("."),o=Modernizr[i[0]];if(2==i.length&&(o=o[i[1]]),"undefined"!=typeof o)return Modernizr;t="function"==typeof t?t():t,1==i.length?Modernizr[i[0]]=t:(!Modernizr[i[0]]||Modernizr[i[0]]instanceof Boolean||(Modernizr[i[0]]=new Boolean(Modernizr[i[0]])),Modernizr[i[0]][i[1]]=t),r([(t&&0!=t?"":"no-")+i.join("-")]),Modernizr._trigger(e,t)}return Modernizr}function l(e,t){return!!~(""+e).indexOf(t)}function u(e){return e.replace(/([a-z])-([a-z])/g,function(e,t,n){return t+n.toUpperCase()}).replace(/^-/,"")}function d(e,t){return function(){return e.apply(t,arguments)}}function f(e,t,n){var o;for(var r in e)if(e[r]in t)return n===!1?e[r]:(o=t[e[r]],i(o,"function")?d(o,n||t):o);return!1}function c(e){return e.replace(/([A-Z])/g,function(e,t){return"-"+t.toLowerCase()}).replace(/^ms-/,"-ms-")}function p(){var e=t.body;return e||(e=a(x?"svg":"body"),e.fake=!0),e}function m(e,n,i,o){var r,s,l,u,d="modernizr",f=a("div"),c=p();if(parseInt(i,10))for(;i--;)l=a("div"),l.id=o?o[i]:d+(i+1),f.appendChild(l);return r=a("style"),r.type="text/css",r.id="s"+d,(c.fake?c:f).appendChild(r),c.appendChild(f),r.styleSheet?r.styleSheet.cssText=e:r.appendChild(t.createTextNode(e)),f.id=d,c.fake&&(c.style.background="",c.style.overflow="hidden",u=C.style.overflow,C.style.overflow="hidden",C.appendChild(c)),s=n(f,e),c.fake?(c.parentNode.removeChild(c),C.style.overflow=u,C.offsetHeight):f.parentNode.removeChild(f),!!s}function h(t,i){var o=t.length;if("CSS"in e&&"supports"in e.CSS){for(;o--;)if(e.CSS.supports(c(t[o]),i))return!0;return!1}if("CSSSupportsRule"in e){for(var r=[];o--;)r.push("("+c(t[o])+":"+i+")");return r=r.join(" or "),m("@supports ("+r+") { #modernizr { position: absolute; } }",function(e){return"absolute"==getComputedStyle(e,null).position})}return n}function b(e,t,o,r){function s(){f&&(delete P.style,delete P.modElem)}if(r=i(r,"undefined")?!1:r,!i(o,"undefined")){var d=h(e,o);if(!i(d,"undefined"))return d}for(var f,c,p,m,b,y=["modernizr","tspan"];!P.style;)f=!0,P.modElem=a(y.shift()),P.style=P.modElem.style;for(p=e.length,c=0;p>c;c++)if(m=e[c],b=P.style[m],l(m,"-")&&(m=u(m)),P.style[m]!==n){if(r||i(o,"undefined"))return s(),"pfx"==t?m:!0;try{P.style[m]=o}catch(g){}if(P.style[m]!=b)return s(),"pfx"==t?m:!0}return s(),!1}function y(e,t,n,o,r){var a=e.charAt(0).toUpperCase()+e.slice(1),s=(e+" "+j.join(a+" ")+a).split(" ");return i(t,"string")||i(t,"undefined")?b(s,t,o,r):(s=(e+" "+E.join(a+" ")+a).split(" "),f(s,t,n))}function g(e,t,i){return y(e,n,n,t,i)}var v=[],k=[],w={_version:"3.3.1",_config:{classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0},_q:[],on:function(e,t){var n=this;setTimeout(function(){t(n[e])},0)},addTest:function(e,t,n){k.push({name:e,fn:t,options:n})},addAsyncTest:function(e){k.push({name:null,fn:e})}},Modernizr=function(){};Modernizr.prototype=w,Modernizr=new Modernizr;var C=t.documentElement,x="svg"===C.nodeName.toLowerCase(),_=w._config.usePrefixes?" -webkit- -moz- -o- -ms- ".split(" "):["",""];w._prefixes=_;var T;!function(){var e={}.hasOwnProperty;T=i(e,"undefined")||i(e.call,"undefined")?function(e,t){return t in e&&i(e.constructor.prototype[t],"undefined")}:function(t,n){return e.call(t,n)}}(),w._l={},w.on=function(e,t){this._l[e]||(this._l[e]=[]),this._l[e].push(t),Modernizr.hasOwnProperty(e)&&setTimeout(function(){Modernizr._trigger(e,Modernizr[e])},0)},w._trigger=function(e,t){if(this._l[e]){var n=this._l[e];setTimeout(function(){var e,i;for(e=0;e<n.length;e++)(i=n[e])(t)},0),delete this._l[e]}},Modernizr._q.push(function(){w.addTest=s});var S="Moz O ms Webkit",j=w._config.usePrefixes?S.split(" "):[];w._cssomPrefixes=j;var E=w._config.usePrefixes?S.toLowerCase().split(" "):[];w._domPrefixes=E;var q={elem:a("modernizr")};Modernizr._q.push(function(){delete q.elem});var P={style:q.elem.style};Modernizr._q.unshift(function(){delete P.style}),w.testAllProps=y,w.testAllProps=g,Modernizr.addAsyncTest(function(){function n(){function o(){try{var e=a("div"),n=a("span"),i=e.style,o=0,r=0,s=!1,l=t.body.firstElementChild||t.body.firstChild;return e.appendChild(n),n.innerHTML="Bacon ipsum dolor sit amet jerky velit in culpa hamburger et. Laborum dolor proident, enim dolore duis commodo et strip steak. Salami anim et, veniam consectetur dolore qui tenderloin jowl velit sirloin. Et ad culpa, fatback cillum jowl ball tip ham hock nulla short ribs pariatur aute. Pig pancetta ham bresaola, ut boudin nostrud commodo flank esse cow tongue culpa. Pork belly bresaola enim pig, ea consectetur nisi. Fugiat officia turkey, ea cow jowl pariatur ullamco proident do laborum velit sausage. Magna biltong sint tri-tip commodo sed bacon, esse proident aliquip. Ullamco ham sint fugiat, velit in enim sed mollit nulla cow ut adipisicing nostrud consectetur. Proident dolore beef ribs, laborum nostrud meatball ea laboris rump cupidatat labore culpa. Shankle minim beef, velit sint cupidatat fugiat tenderloin pig et ball tip. Ut cow fatback salami, bacon ball tip et in shank strip steak bresaola. In ut pork belly sed mollit tri-tip magna culpa veniam, short ribs qui in andouille ham consequat. Dolore bacon t-bone, velit short ribs enim strip steak nulla. Voluptate labore ut, biltong swine irure jerky. Cupidatat excepteur aliquip salami dolore. Ball tip strip steak in pork dolor. Ad in esse biltong. Dolore tenderloin exercitation ad pork loin t-bone, dolore in chicken ball tip qui pig. Ut culpa tongue, sint ribeye dolore ex shank voluptate hamburger. Jowl et tempor, boudin pork chop labore ham hock drumstick consectetur tri-tip elit swine meatball chicken ground round. Proident shankle mollit dolore. Shoulder ut duis t-bone quis reprehenderit. Meatloaf dolore minim strip steak, laboris ea aute bacon beef ribs elit shank in veniam drumstick qui. Ex laboris meatball cow tongue pork belly. Ea ball tip reprehenderit pig, sed fatback boudin dolore flank aliquip laboris eu quis. Beef ribs duis beef, cow corned beef adipisicing commodo nisi deserunt exercitation. Cillum dolor t-bone spare ribs, ham hock est sirloin. Brisket irure meatloaf in, boudin pork belly sirloin ball tip. Sirloin sint irure nisi nostrud aliqua. Nostrud nulla aute, enim officia culpa ham hock. Aliqua reprehenderit dolore sunt nostrud sausage, ea boudin pork loin ut t-bone ham tempor. Tri-tip et pancetta drumstick laborum. Ham hock magna do nostrud in proident. Ex ground round fatback, venison non ribeye in.",t.body.insertBefore(e,l),i.cssText="position:absolute;top:0;left:0;width:5em;text-align:justify;text-justification:newspaper;",o=n.offsetHeight,r=n.offsetWidth,i.cssText="position:absolute;top:0;left:0;width:5em;text-align:justify;text-justification:newspaper;"+_.join("hyphens:auto; "),s=n.offsetHeight!=o||n.offsetWidth!=r,t.body.removeChild(e),e.removeChild(n),s}catch(u){return!1}}function r(e,n){try{var i=a("div"),o=a("span"),r=i.style,s=0,l=!1,u=!1,d=!1,f=t.body.firstElementChild||t.body.firstChild;return r.cssText="position:absolute;top:0;left:0;overflow:visible;width:1.25em;",i.appendChild(o),t.body.insertBefore(i,f),o.innerHTML="mm",s=o.offsetHeight,o.innerHTML="m"+e+"m",u=o.offsetHeight>s,n?(o.innerHTML="m<br />m",s=o.offsetWidth,o.innerHTML="m"+e+"m",d=o.offsetWidth>s):d=!0,u===!0&&d===!0&&(l=!0),t.body.removeChild(i),i.removeChild(o),l}catch(c){return!1}}function l(n){try{var i,o=a("input"),r=a("div"),s="lebowski",l=!1,u=t.body.firstElementChild||t.body.firstChild;r.innerHTML=s+n+s,t.body.insertBefore(r,u),t.body.insertBefore(o,r),o.setSelectionRange?(o.focus(),o.setSelectionRange(0,0)):o.createTextRange&&(i=o.createTextRange(),i.collapse(!0),i.moveEnd("character",0),i.moveStart("character",0),i.select());try{e.find?l=e.find(s+s):(i=e.self.document.body.createTextRange(),l=i.findText(s+s))}catch(d){l=!1}return t.body.removeChild(r),t.body.removeChild(o),l}catch(d){return!1}}return t.body||t.getElementsByTagName("body")[0]?(s("csshyphens",function(){if(!g("hyphens","auto",!0))return!1;try{return o()}catch(e){return!1}}),s("softhyphens",function(){try{return r("&#173;",!0)&&r("&#8203;",!1)}catch(e){return!1}}),void s("softhyphensfind",function(){try{return l("&#173;")&&l("&#8203;")}catch(e){return!1}})):void setTimeout(n,i)}var i=300;setTimeout(n,i)}),o(),r(v),delete w.addTest,delete w.addAsyncTest;for(var B=0;B<Modernizr._q.length;B++)Modernizr._q[B]();e.Modernizr=Modernizr}(window,document);
