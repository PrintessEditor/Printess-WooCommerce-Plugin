﻿class PrintessEditor{constructor(e){if(this.calculateCurrentPrices=(e,t)=>{var i=this.getPriceCategories(t);let n=t.getPriceForFormFields(t.getCurrentFormFieldValues());return e.snippetPriceCategories&&e.snippetPriceCategories.forEach(e=>{e&&0<e.amount&&t.snippetPrices[e.priceCategory-1]&&(n+=t.snippetPrices[e.priceCategory-1].priceInCent)}),i.price=t.formatMoney(n),i},!e||!e.shopToken)throw"No shop token provided";this.Settings={...e};e={};void 0!==this.Settings.uiSettings&&null!==this.Settings.uiSettings&&(e.showAnimation=!0===this.Settings.uiSettings.showStartupAnimation||"true"==this.Settings.uiSettings.showStartupAnimation,this.Settings.uiSettings.startupLogoUrl&&(e.imageUrl=this.Settings.uiSettings.startupLogoUrl),this.Settings.uiSettings.startupBackgroundColor)&&(e.background=this.Settings.uiSettings.startupBackgroundColor),this.ClassicEditorUrl=this.getClassicEditorUrl(this.Settings.editorUrl,this.Settings.editorVersion,e)+"#"+encodeURIComponent(JSON.stringify(e))}stripEditorVersion(e){if(void 0!==(e=(e||"").trim())&&null!=e)if(e){for(;0==e.indexOf("/");)e=e.substring(1);for(;0<e.length&&"/"===e[e.length-1];)e=e.substring(0,e.length-1)}else e="";return e}sanitizeHost(e){return e?(e=e.trim()).endsWith("/")?e:e+"/":e||""}getClassicEditorUrl(e,t,i){var n=(e=e||"https://editor.printess.com/").indexOf("#");if(0<n){var s=e.substring(n+1);if(s)try{var o=JSON.parse(decodeURIComponent(s));if(o)for(var r in o)o.hasOwnProperty(r)&&(i[r]=o[r])}catch(e){}e=e.substring(0,n)}return e.toLowerCase().endsWith(".html")||(e=this.sanitizeHost(e),t&&(e+=(t=this.stripEditorVersion(t))+(t?"/":"")),e+="printess-editor/embed.html"),e=0!==e.toLowerCase().indexOf("https://")&&0!==e.toLowerCase().indexOf("http://")?"https://"+e:e}getLoaderUrl(e,t,i){var n=(e=e||"https://editor.printess.com/").indexOf("#");if(0<n){var s=e.substring(n+1);if(s)try{var o=JSON.parse(decodeURIComponent(s));if(o)for(var r in o)o.hasOwnProperty(r)&&(i[r]=o[r])}catch(e){}e=e.substring(0,n)}return(e=e.toLocaleLowerCase().endsWith("embed.html")?e.substring(0,e.length-"embed.html".length):e).toLowerCase().endsWith(".html")||(e=this.sanitizeHost(e),t&&(e+=(t=this.stripEditorVersion(t))+(t?"/":"")),e.toLowerCase().endsWith("printess-editor/")||(e+="printess-editor/"),e+="loader.js"),e=0!==e.toLowerCase().indexOf("https://")&&0!==e.toLowerCase().indexOf("http://")?"https://"+e:e}getPrintessComponent(){return document.querySelector("printess-component")||null}applyFormFieldMappings(t,i){var n=[];if(!i)for(const e in t)t.hasOwnProperty(e)&&n.push({name:e,value:t[e]});if(t)for(const r in t)if(t.hasOwnProperty(r)){var s=t[r],o=void 0!==i[r]&&void 0!==i[r].formField?i[r].formField:r;let e=s;void 0!==i[r]&&void 0!==i[r].values&&void 0!==i[r].values[e]&&(e=i[r].values[s]),n.push({name:o,value:e})}return n}reverseFormFieldMapping(e,t,i){let n=e||"",s=t||"";if(i)for(var o in i)if(i.hasOwnProperty(o)&&i[o].formField===e){if(i[n=o].values)for(var r in i[o].values)if(i[o].values.hasOwnProperty(r)&&i[o].values[r]===t){s=r;break}break}return{name:n,value:s}}setViewportMeta(){var i=document.getElementsByTagName("head");if(i&&0<i.length){let e=i[0].querySelector("meta[name=viewport]"),t=(e||((e=document.createElement("meta")).setAttribute("name","viewport"),i[0].appendChild(e)),e.getAttribute("content"));t?t.indexOf("interactive-widget")<0&&(t=t+(t?", ":"")+"interactive-widget=resizes-content",e.setAttribute("content",t)):e.setAttribute("content","interactive-widget=resizes-content")}}async initializeIFrame(t,e,i){const n=this;let s=document.getElementById("printess");const o=e=>{t&&"function"==typeof t.onCloseTab&&t.onCloseTab(e)},r=e=>{switch(e.data.cmd){case"back":window.removeEventListener("message",r),window.removeEventListener("beforeunload",o),window.removeEventListener("unload",o),s.setAttribute("printessHasListener","false"),t&&"function"==typeof t.onBack&&t.onBack();break;case"basket":window.removeEventListener("message",r),window.removeEventListener("beforeunload",o),window.removeEventListener("unload",o),s.setAttribute("printessHasListener","false"),t&&"function"==typeof t.onAddToBasket&&t.onAddToBasket(e.data.token,e.data.thumbnailUrl);break;case"formFieldChanged":t&&"function"==typeof t.onFormFieldChanged&&t.onFormFieldChanged(e.data.n||e.data.name,e.data.v||e.data.value,e.data.ffCaption||"",e.data.l||e.data.label);break;case"priceChanged":t&&"function"==typeof t.onPriceChanged&&t.onPriceChanged(e.data.priceInfo);break;case"renderFirstPageImage":t&&"function"==typeof t.onRenderedFirstPageImage&&t.onRenderedFirstPageImage(e.data.result);break;case"save":t&&"function"==typeof t.onSave&&t.onSave(e.data.token)}};return new Promise(e=>{var t;s?("true"!==s.getAttribute("printessHasListener")&&(window.addEventListener("message",r),!0===i.showAlertOnTabClose&&(window.addEventListener("beforeunload",o),window.addEventListener("unload",o)),s.setAttribute("printessHasListener","true")),e(s)):((t=document.createElement("div")).setAttribute("id","printess-container"),t.setAttribute("style","display: none; position: absolute; top: 0; bottom: 0; right: 0; left: 0; width: 100%; height: 100%; z-index: 100000;"),(s=document.createElement("iframe")).setAttribute("src",this.ClassicEditorUrl),s.setAttribute("id","printess"),s.setAttribute("style","width:100%; heigth:100%;"),s.setAttribute("data-attached","false"),s.setAttribute("allow","xr-spatial-tracking; clipboard-read; clipboard-write;"),s.setAttribute("allowfullscreen","true"),t.appendChild(s),s.onload=()=>{e(s)},window.addEventListener("message",r),!0===i.showAlertOnTabClose&&(window.addEventListener("beforeunload",o),window.addEventListener("unload",o)),s.setAttribute("printessHasListener","true"),window.visualViewport&&window.visualViewport.addEventListener("scroll",()=>{s.contentWindow?.postMessage({cmd:"viewportScroll",height:window.visualViewport?.height,offsetTop:window.visualViewport?.offsetTop},"*")},{passive:!0}),n.setViewportMeta(),document.body.append(t))})}getPriceCategories(e){return{snippetPrices:e.snippetPrices.map(e=>e?e.label:null),priceCategories:{},price:e.formatMoney(e.getPriceForFormFields(e.getCurrentFormFieldValues())),productName:e.getProductName(),legalNotice:e.legalText,infoUrl:e.legalTextUrl}}onPriceChanged(t,i){try{const n=document.getElementById("printess");let e=null;try{t.snippetPriceCategories&&0<t.snippetPriceCategories.length?i.stickers=t.snippetPriceCategories.filter(e=>i.snippetPrices&&i.snippetPrices.length>=e.priceCategory).map(e=>({productVariantId:i.snippetPrices[e.priceCategory-1].variantId,quantity:e.amount})):i.stickers=[],e=this.calculateCurrentPrices(t,i)}catch(e){console.error(e)}n&&!i.hidePricesInEditor&&setTimeout(()=>{n.contentWindow.postMessage({cmd:"refreshPriceDisplay",priceDisplay:e},"*")},0)}catch(e){}}hideBcUiVersion(e,t){var i=this.getPrintessComponent();i&&i.editor&&i.editor.ui.hide(),"function"==typeof e.editorClosed&&e.editorClosed(!0===t)}async showBcUiVersion(t,o){const i=this;var e=t.getPriceInfo();let n=null,s=null;n=i.applyFormFieldMappings(t.getCurrentFormFieldValues(),t.getFormFieldMappings()),s=t.getMergeTemplates();var r=i.getLoaderUrl(this.Settings.editorUrl,this.Settings.editorVersion,{}),r=await import(r);let a=i.getPrintessComponent();a&&a.editor?(a.style.display="block",await a.editor.api.loadTemplateAndFormFields(t.templateNameOrSaveToken,s,n,null),a.editor.ui.show()):(e={domain:i.Settings.apiDomain,mergeTemplates:s,formFields:n,token:i.Settings.shopToken,templateName:t.templateNameOrSaveToken,translationKey:"",basketId:"function"==typeof t.getBasketId&&t.getBasketId()||"Some-Unique-Basket-Or-Session-Id",shopUserId:"function"==typeof t.getUserId?t.getUserId()||"Some-Unique-Basket-Or-Session-Id":"Some-Unique-Shop-User-Id",snippetPriceCategoryLabels:e&&e.snippetPrices?e.snippetPrices:null,theme:i.Settings.uiSettings&&i.Settings.uiSettings.theme||"",addToBasketCallback:(e,t)=>{o&&"function"==typeof o.onAddToBasket&&o.onAddToBasket(e,t)},formFieldChangedCallback:(e,t,i,n,s)=>{o&&"function"==typeof o.onFormFieldChanged&&o.onFormFieldChanged(e,t,s,n)},priceChangeCallback:e=>{o&&"function"==typeof o.onPriceChanged&&o.onPriceChanged(e)},backButtonCallback:e=>{i.hideBcUiVersion(t,!0)},saveTemplateCallback:(e,t)=>{"function"==typeof o.onSave&&o.onSave(e)}},r=await r.load(e),(a=i.getPrintessComponent())&&(a.editor=r))}async show(s){const o=this;let i=!1;s&&s.templateNameOrSaveToken&&(i=0===s.templateNameOrSaveToken.indexOf("st:")&&90===s.templateNameOrSaveToken.length);var n={onBack:()=>{o.hide(s,!0)},onAddToBasket:(e,t)=>{const i=s.onAddToBasket(e,t);i&&i.waitUntilClosingMS?setTimeout(function(){"function"==typeof i.executeBeforeClosing&&i.executeBeforeClosing(),o.hide(s,!1)},i.waitUntilClosingMS):(i&&"function"==typeof i.executeBeforeClosing&&i.executeBeforeClosing(),o.hide(s,!1))},onFormFieldChanged:(e,t,i,n)=>{e=o.reverseFormFieldMapping(e,t,s.getFormFieldMappings());"function"==typeof s.onFormFieldChanged&&s.onFormFieldChanged(e.name,e.value,i,n)},onPriceChanged:e=>{o.onPriceChanged(e,s)},onRenderedFirstPageImage:e=>{"function"==typeof s.onRenderFirstPageImage&&s.onRenderFirstPageImage(e)},onSave:e=>{"function"==typeof s.onSave&&s.onSave(e,"")},onCloseTab:e=>{e.preventDefault(),e.returnValue=""}};if(this.Settings.uiSettings&&"bcui"===this.Settings.uiSettings.uiVersion)o.showBcUiVersion(s,n);else{var r=s.getPriceInfo();let e=null,t=null;i||(e=this.applyFormFieldMappings(s.getCurrentFormFieldValues(),s.getFormFieldMappings()),t=s.getMergeTemplates());n=await this.initializeIFrame(n,s,this.Settings);if("false"===n.getAttribute("data-attached"))try{var a={domain:o.Settings.apiDomain,token:this.Settings.shopToken||"",templateName:s.templateNameOrSaveToken,showBuyerSide:!0,templateUserId:"",basketId:"function"==typeof s.getBasketId&&s.getBasketId()||"Some-Unique-Basket-Or-Session-Id",shopUserId:"function"==typeof s.getUserId?s.getUserId()||"Some-Unique-Basket-Or-Session-Id":"Some-Unique-Shop-User-Id",formFields:e,snippetPriceCategoryLabels:r&&r.snippetPrices?r.snippetPrices:null,mergeTemplates:t};if(void 0!==s.showSplitterGridSizeButton&&null!==s.showSplitterGridSizeButton&&(a.showSplitterGridSizeButton=!0===s.showSplitterGridSizeButton||"true"===s.showSplitterGridSizeButton),this.Settings.uiSettings&&this.Settings.uiSettings.theme&&(a.theme=this.Settings.uiSettings.theme),this.Settings.attachParams)for(const d in this.Settings.attachParams)this.Settings.attachParams.hasOwnProperty(d)&&(a[d]=this.Settings.attachParams[d]);if(void 0!==s.additionalAttachParams||null!==s.additionalAttachParams)for(const l in s.additionalAttachParams)s.additionalAttachParams.hasOwnProperty(l)&&(a[l]=s.additionalAttachParams[l]);n.contentWindow.postMessage({cmd:"attach",properties:a},"*"),n.setAttribute("data-attached","true")}catch(e){console.error(e)}else n.contentWindow.postMessage({cmd:"loadTemplateAndFormFields",parameters:[s.templateNameOrSaveToken,t,e,void 0]},"*")}document.body.classList.add("hideAll");r=document.getElementsByTagName("html"),r&&0<r.length&&r[0].classList.add("printessEditorOpen"),n=document.getElementById("printess-container");n&&n.style.setProperty("display","block","important")}hide(e,t){this.Settings.uiSettings&&"bcui"===this.Settings.uiSettings.uiVersion?(i=this.getPrintessComponent())&&i.editor&&i.editor.ui.hide():(i=document.getElementById("printess-container"))&&(i.style.display="none"),document.body.classList.remove("hideAll");var i=document.getElementsByTagName("html");i&&0<i.length&&i[0].classList.remove("printessEditorOpen"),"function"==typeof e.editorClosed&&e.editorClosed(!0===t)}}function initPrintessEditor(e,t,i,n,s,o,r=""){let a;return a=e&&"string"!=typeof e?{apiDomain:e.apiDomain||null,shopToken:e.shopToken||"",editorUrl:e.editorUrl||"",editorVersion:e.editorVersion||"",attachParams:e.attachParams||"",showAlertOnTabClose:void 0!==e.showTabClosingAlert&&null!==e.showTabClosingAlert&&!0===e.showTabClosingAlert,uiSettings:{showStartupAnimation:void 0===e.showStartupAnimation||null===e.showStartupAnimation||!0===e.showStartupAnimation,startupBackgroundColor:e.startupBackgroundColor||"#ffffff",theme:e.theme||"",startupLogoUrl:e.startupLogoUrl||"",uiVersion:e.uiVersion||""},...e}:{apiDomain:null,shopToken:e||"",editorUrl:t,editorVersion:i,uiSettings:{showStartupAnimation:s,startupBackgroundColor:r,startupLogoUrl:n,theme:o}},new PrintessEditor(a)};