var App=function(){var t,e,o,n,r=new XMLHttpRequest,s="",a=[],l=[],u="http://api.forismatic.com/api/1.0/?method=getQuote&lang=en&format=jsonp&jsonp=App.getNewQuote",i=document.getElementById("js-quote-text"),c=document.getElementById("js-quote-author"),d=document.getElementById("js-quote-link"),g=document.getElementById("js-quote-source"),m=document.getElementById("js-font-link"),f=document.getElementById("js-new-font"),h=document.getElementById("js-new-quote"),p=document.getElementById("js-new-colors"),b=document.getElementById("js-color-one"),y=document.getElementById("js-color-two"),v=!1,A=function(){t=document.styleSheets[2],e=document.body.getAttribute("data-font"),o=document.body.getAttribute("data-colorone"),n=document.body.getAttribute("data-colortwo"),f.addEventListener("click",function(t){t.preventDefault(),f.setAttribute("data-loading",""),0===l.length?(s="font",E()):l.length>0&&T()}),h.addEventListener("click",function(t){t.preventDefault(),h.setAttribute("data-loading",""),j(u)}),p.addEventListener("click",function(t){t.preventDefault(),p.setAttribute("data-loading",""),s="colors",E()})},w=function(t,e){return t=Math.ceil(t),e=Math.floor(e),Math.floor(Math.random()*(e-t))+t},j=function(t){var e=document.createElement("script"),o=document.scripts[0];e.src=t,o.parentNode.insertBefore(e,o)},E=function(){r&&(r.open("GET","ajax-getdata.php?function="+s,!0),r.onreadystatechange=q,r.send())},q=function(){if(r.readyState===XMLHttpRequest.DONE)if(200===r.status){var t=JSON.parse(r.responseText.replace("\\'","'"));switch(s){case"font":l=t.items,T();break;case"colors":a=t[0],I();break;default:alert("No request type set.")}}else console.log("There was a problem with the request.")},L=function(){var t="http://"+window.location.hostname+"/src/?",r="c1="+o+"&c2="+n,s="&quote="+i.innerHTML+"&author="+c.innerHTML+"&id="+d.getAttribute("href").slice(d.getAttribute("href").length-11,d.getAttribute("href").length-1),a="&font="+e,l=encodeURI(t+r+s+a);g.setAttribute("href",l)},M=function(t,e){WebFontConfig={google:{families:[t+":"+e]},timeout:2e3},j("https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js")},B=function(t){v||(v=!0,M(e)),i.innerHTML=t.quoteText,t.quoteAuthor.length>0?c.innerHTML=t.quoteAuthor:c.innerHTML="Anonymous",d.setAttribute("href",t.quoteLink),L(),h.removeAttribute("data-loading")},I=function(){o=a.color_one,n=a.color_two,b.innerHTML=o,y.innerHTML=n,t.cssRules[1].style.color=o,t.cssRules[2].style.backgroundColor=o,t.cssRules[3].style.color=n,t.cssRules[4].style.borderColor=n,t.cssRules[5].style.backgroundColor=n,o=o.slice(1),n=n.slice(1);var e=document.getElementById("js-colors-vote-link");e.setAttribute("href","http://randoma11y.com/#/?hex="+o+"&compare="+n),L(),p.removeAttribute("data-loading")},T=function(){var o=w(0,l.length),n="regular",r=!1;e=l[o].family,console.log(e);for(var s=0;s<l[o].variants.length;s++)"regular"===l[o].variants[s]&&(r=!0);r||(n=l[o].variants[0]),M(e,n),t.cssRules[0].style.fontFamily=e,m.setAttribute("href","https://fonts.google.com/specimen/"+e),m.innerHTML=e,L(),f.removeAttribute("data-loading")};return{init:A,getNewQuote:B,getNewColors:I,getNewFont:T}}();