class PrintessEditor {
    constructor(settings) {
        this.lastSaveDate = new Date();
        this.visible = false;
        this.calculateCurrentPrices = async (priceInfo, context) => {
            const r = await this.getPriceCategories(context);
            let basePrice = r.basePrice;
            if (priceInfo.snippetPriceCategories) {
                priceInfo.snippetPriceCategories.forEach((x) => {
                    if (x && x.amount > 0 && context.snippetPrices[x.priceCategory - 1]) {
                        basePrice += context.snippetPrices[x.priceCategory - 1].priceInCent; // * x.amount;
                    }
                });
            }
            r.price = context.formatMoney(basePrice);
            return r;
        };
        if (!settings || !settings.shopToken) {
            throw "No shop token provided";
        }
        this.Settings = {
            ...settings
        };
        this.visible = false;
        const hasUiSettings = typeof this.Settings.uiSettings !== "undefined" && this.Settings.uiSettings !== null;
        const startupSettings = {};
        if (hasUiSettings) {
            startupSettings["showAnimation"] = this.Settings.uiSettings.showStartupAnimation === true || this.Settings.uiSettings.showStartupAnimation == "true";
            if (this.Settings.uiSettings.startupLogoUrl) {
                startupSettings["imageUrl"] = this.Settings.uiSettings.startupLogoUrl;
            }
            if (this.Settings.uiSettings.startupBackgroundColor) {
                startupSettings["background"] = this.Settings.uiSettings.startupBackgroundColor;
            }
        }
        this.ClassicEditorUrl = this.getClassicEditorUrl(this.Settings.editorUrl, this.Settings.editorVersion, startupSettings) + "#" + encodeURIComponent(JSON.stringify(startupSettings));
    }
    stripEditorVersion(editorVersion) {
        editorVersion = (editorVersion ? editorVersion : "").trim();
        if (typeof editorVersion !== "undefined" && editorVersion != null) {
            if (!editorVersion) {
                editorVersion = "";
            }
            else {
                while (editorVersion.indexOf("/") == 0) {
                    editorVersion = editorVersion.substring(1);
                }
                while (editorVersion.length > 0 && editorVersion[editorVersion.length - 1] === "/") {
                    editorVersion = editorVersion.substring(0, editorVersion.length - 1);
                }
            }
        }
        return editorVersion;
    }
    sanitizeHost(host) {
        if (host) {
            host = host.trim();
            if (host.endsWith("/")) {
                return host;
            }
            return host + "/";
        }
        return host || "";
    }
    getClassicEditorUrl(editorDomain, editorVersion, startupSettings) {
        editorDomain = editorDomain || "https://editor.printess.com/";
        const hashIndex = editorDomain.indexOf("#");
        if (hashIndex > 0) {
            let json = editorDomain.substring(hashIndex + 1);
            if (json) {
                try {
                    const decodedJson = JSON.parse(decodeURIComponent(json));
                    if (decodedJson) {
                        for (let propertyName in decodedJson) {
                            if (decodedJson.hasOwnProperty(propertyName)) {
                                startupSettings[propertyName] = decodedJson[propertyName];
                            }
                        }
                    }
                }
                catch (e) {
                }
            }
            editorDomain = editorDomain.substring(0, hashIndex);
        }
        if (!editorDomain.toLowerCase().endsWith(".html")) {
            editorDomain = this.sanitizeHost(editorDomain);
            if (editorVersion) {
                editorVersion = this.stripEditorVersion(editorVersion);
                editorDomain += editorVersion + (editorVersion ? "/" : "");
            }
            editorDomain += 'printess-editor/embed.html';
        }
        if (editorDomain.toLowerCase().indexOf("https://") !== 0 && editorDomain.toLowerCase().indexOf("http://") !== 0) {
            editorDomain = "https://" + editorDomain;
        }
        return editorDomain;
    }
    getLoaderUrl(editorDomain, editorVersion, urlSettings) {
        editorDomain = editorDomain || "https://editor.printess.com/";
        const hashIndex = editorDomain.indexOf("#");
        if (hashIndex > 0) {
            let json = editorDomain.substring(hashIndex + 1);
            if (json) {
                try {
                    const decodedJson = JSON.parse(decodeURIComponent(json));
                    if (decodedJson) {
                        for (let propertyName in decodedJson) {
                            if (decodedJson.hasOwnProperty(propertyName)) {
                                urlSettings[propertyName] = decodedJson[propertyName];
                            }
                        }
                    }
                }
                catch (e) {
                }
            }
            editorDomain = editorDomain.substring(0, hashIndex);
        }
        if (editorDomain.toLocaleLowerCase().endsWith("embed.html")) {
            editorDomain = editorDomain.substring(0, editorDomain.length - "embed.html".length);
        }
        if (!editorDomain.toLowerCase().endsWith(".html")) {
            editorDomain = this.sanitizeHost(editorDomain);
            if (editorVersion) {
                editorVersion = this.stripEditorVersion(editorVersion);
                editorDomain += editorVersion + (editorVersion ? "/" : "");
            }
            if (!editorDomain.toLowerCase().endsWith("printess-editor/")) {
                editorDomain += "printess-editor/";
            }
            editorDomain += 'loader.js';
        }
        if (editorDomain.toLowerCase().indexOf("https://") !== 0 && editorDomain.toLowerCase().indexOf("http://") !== 0) {
            editorDomain = "https://" + editorDomain;
        }
        return editorDomain;
    }
    getPrintessComponent() {
        return document.querySelector("printess-component") || null;
    }
    save(callbacks) {
        let printessComponent = document.querySelector("printess-component") || null;
        if (printessComponent) {
            if (printessComponent && printessComponent.editor) {
                printessComponent.editor.api.saveAndGenerateBasketThumbnailUrl().then((result) => {
                    callbacks.onSaveAsync(result.saveToken, result.basketUrl).then(() => {
                    });
                });
            }
        }
        else {
            let iFrame = document.getElementById("printess");
            if (iFrame) {
                iFrame.contentWindow?.postMessage({ cmd: "saveAndGenerateBasketThumbnailUrl", parameters: [] }, "*");
            }
        }
    }
    static applyFormFieldMappings(formFields, mappings) {
        const ret = [];
        if (!mappings) {
            for (const ffName in formFields) {
                if (formFields.hasOwnProperty(ffName)) {
                    ret.push({
                        name: ffName,
                        value: formFields[ffName]
                    });
                }
            }
        }
        if (formFields) {
            for (const property in formFields) {
                if (formFields.hasOwnProperty(property)) {
                    const ff = formFields[property];
                    const ffName = typeof mappings[property] !== "undefined" && typeof mappings[property].formField !== "undefined" ? mappings[property].formField : property;
                    let ffValue = ff;
                    if (typeof mappings[property] !== "undefined" && typeof mappings[property].values !== "undefined" && typeof mappings[property].values[ffValue] !== "undefined") {
                        ffValue = mappings[property].values[ff];
                    }
                    ret.push({
                        name: ffName,
                        value: ffValue
                    });
                }
            }
        }
        return ret;
    }
    reverseFormFieldMapping(formFieldName, formFieldValue, mappings) {
        let name = formFieldName || "";
        let value = formFieldValue || "";
        if (mappings) {
            for (let optionName in mappings) {
                if (mappings.hasOwnProperty(optionName)) {
                    if (mappings[optionName].formField === formFieldName) {
                        name = optionName;
                        if (mappings[optionName].values) {
                            for (let optionValue in mappings[optionName].values) {
                                if (mappings[optionName].values.hasOwnProperty(optionValue)) {
                                    if (mappings[optionName].values[optionValue] === formFieldValue) {
                                        value = optionValue;
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
        return {
            name: name,
            value: value
        };
    }
    setViewportMeta() {
        const headElements = document.getElementsByTagName("head");
        if (headElements && headElements.length > 0) {
            let metaTag = headElements[0].querySelector('meta[name=viewport]');
            if (!metaTag) {
                metaTag = document.createElement("meta");
                metaTag.setAttribute("name", "viewport");
                headElements[0].appendChild(metaTag);
            }
            let content = metaTag.getAttribute("content");
            if (content) {
                if (content.indexOf("interactive-widget") < 0) {
                    content += content ? ", " : "";
                    content += "interactive-widget=resizes-content";
                    metaTag.setAttribute("content", content);
                }
            }
            else {
                metaTag.setAttribute("content", "interactive-widget=resizes-content");
            }
        }
    }
    async initializeIFrame(callbacks, context, settings) {
        const that = this;
        let iFrame = document.getElementById("printess");
        const closeTabListener = (evt) => {
            if (callbacks && typeof callbacks.onCloseTab === "function") {
                callbacks.onCloseTab(evt);
            }
        };
        const eventListener = async (evt) => {
            switch (evt.data.cmd) {
                case 'back':
                    window.removeEventListener('message', eventListener);
                    window.removeEventListener('beforeunload', closeTabListener);
                    window.removeEventListener('unload', closeTabListener);
                    iFrame.setAttribute("printessHasListener", "false");
                    if (callbacks && typeof callbacks.onBack === "function") {
                        callbacks.onBack();
                    }
                    break;
                case 'basket':
                    const addToBasket = (saveToken, thumbnailUrl) => {
                        window.removeEventListener('message', eventListener);
                        window.removeEventListener('beforeunload', closeTabListener);
                        window.removeEventListener('unload', closeTabListener);
                        iFrame.setAttribute("printessHasListener", "false");
                        if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
                            callbacks.onAddToBasketAsync(evt.data.token, evt.data.thumbnailUrl).then(() => { });
                        }
                    };
                    try {
                        if (typeof context.onAllowAddToBasket === "function") {
                            const result = context.onAllowAddToBasket(evt.data.token, evt.data.thumbnailUrl);
                            if (typeof result !== "boolean" || result === true) {
                                addToBasket(evt.data.token, evt.data.thumbnailUrl);
                            }
                        }
                        else if (typeof context.onAllowAddToBasketAsync === "function") {
                            context.onAllowAddToBasketAsync(evt.data.token, evt.data.thumbnailUrl).then((result) => {
                                if (typeof result !== "boolean" || result === true) {
                                    addToBasket(evt.data.token, evt.data.thumbnailUrl);
                                }
                            });
                        }
                        else {
                            addToBasket(evt.data.token, evt.data.thumbnailUrl);
                        }
                    }
                    catch (ex) {
                        console.error(ex);
                    }
                    break;
                case 'formFieldChanged':
                    if (callbacks && typeof callbacks.onFormFieldChangedAsync === "function") {
                        callbacks.onFormFieldChangedAsync(evt.data.n || evt.data.name, evt.data.v || evt.data.value, evt.data.ffCaption || "", evt.data.l || evt.data.label).then(() => { });
                    }
                    break;
                case 'priceChanged': {
                    if (callbacks && typeof callbacks.onPriceChangedAsync === "function") {
                        callbacks.onPriceChangedAsync(evt.data.priceInfo).then(() => { });
                    }
                    break;
                }
                case "getFormField": {
                    if (callbacks && typeof callbacks.onGetFormField === "function") {
                        callbacks.onGetFormField(evt.data.result);
                    }
                    break;
                }
                case 'save': {
                    if (callbacks && typeof callbacks.onSaveAsync === "function") {
                        callbacks.onSaveAsync(evt.data.token, evt.data.thumbnailUrl);
                    }
                    break;
                }
                case "saveAndGenerateBasketThumbnailUrl": {
                    if (callbacks && typeof callbacks.onSaveAsync === "function") {
                        callbacks.onSaveAsync(evt.data.result.saveToken, evt.data.result.basketUrl);
                    }
                    break;
                }
                case 'loaded': {
                    if (that.Settings.autoImportImageUrlsInFormFields === true) {
                        try {
                            const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings())));
                            if (!that.tempUploadImages) {
                                that.tempUploadImages = images;
                            }
                            else {
                                that.tempUploadImages = [
                                    ...that.tempUploadImages,
                                    ...images
                                ];
                            }
                            if (images.length > 0) {
                                that.uploadImageToClassicEditor(iFrame, images[0].data, images[0].name);
                            }
                        }
                        catch (e) {
                            console.error(e);
                        }
                    }
                    if (that.Settings.autoImportUserImages === true) {
                        let userId = await PrintessEditor.getUserId(context);
                        let basketId = await PrintessEditor.getOrGenerateBasketId(context);
                        if (userId || basketId) {
                            that.uploadUserImagesToClassicEditor(iFrame, basketId, userId);
                        }
                    }
                    if (callbacks && typeof callbacks.onLoadAsync === "function") {
                        callbacks.onLoadAsync(context.templateNameOrSaveToken);
                    }
                    break;
                }
                case "uploadImage": {
                    if (this.tempUploadImages && evt.data.result) {
                        const images = this.tempUploadImages;
                        if (images && images.length > 0) {
                            const currentImage = images[0];
                            const imageName = evt.data.result.name;
                            images.shift();
                            iFrame.contentWindow?.postMessage({ cmd: "setFormFieldValue", parameters: [currentImage.name, imageName] }, "*");
                            if (images.length > 0) {
                                this.uploadImageToClassicEditor(iFrame, images[0].data, images[0].name);
                            }
                        }
                    }
                    break;
                }
                default:
                    break;
            }
        };
        return new Promise((resolve) => {
            if (!iFrame) {
                const container = document.createElement('div');
                container.setAttribute('id', 'printess-container');
                container.setAttribute('style', 'display: none; position: absolute; top: 0; bottom: 0; right: 0; left: 0; width: 100%; height: 100%; z-index: 100000;');
                iFrame = document.createElement('iframe');
                iFrame.setAttribute('src', this.ClassicEditorUrl);
                iFrame.setAttribute('id', 'printess');
                iFrame.setAttribute('style', 'width:100%; heigth:100%;');
                iFrame.setAttribute('data-attached', 'false');
                iFrame.setAttribute('allow', 'xr-spatial-tracking; clipboard-read; clipboard-write;');
                iFrame.setAttribute('allowfullscreen', 'true');
                iFrame.classList.add("printess-owned");
                container.appendChild(iFrame);
                iFrame.onload = () => {
                    resolve(iFrame);
                };
                window.addEventListener('message', eventListener);
                if (settings.showAlertOnTabClose === true) {
                    window.addEventListener('beforeunload', closeTabListener);
                    window.addEventListener('unload', closeTabListener);
                }
                iFrame.setAttribute("printessHasListener", "true");
                if (window.visualViewport) {
                    window.visualViewport.addEventListener("scroll", () => {
                        // unfortunately an iframe on iOS is not able to receive the correct visual-viewport, so we forward it.
                        iFrame.contentWindow?.postMessage({ cmd: "viewportScroll", height: window.visualViewport?.height, offsetTop: window.visualViewport?.offsetTop }, "*");
                    }, { passive: true });
                }
                that.setViewportMeta();
                document.body.append(container);
            }
            else {
                if (iFrame.getAttribute("printessHasListener") !== "true") {
                    window.addEventListener('message', eventListener);
                    if (settings.showAlertOnTabClose === true) {
                        window.addEventListener('beforeunload', closeTabListener);
                        window.addEventListener('unload', closeTabListener);
                    }
                    iFrame.setAttribute("printessHasListener", "true");
                }
                resolve(iFrame);
            }
        });
    }
    getFileNameFromUrl(fileName) {
        return (fileName || "").split('#')[0].split('?')[0].split('/').pop();
    }
    getImagesInFormFields(formFields) {
        const ret = [];
        const supportedExtensions = ["png", "jpg", "gif", "webp", "svg", "heic"];
        formFields.forEach((ff) => {
            if (ff.value) {
                const lowerValue = ff.value.toLowerCase();
                if (lowerValue.startsWith("http://") || lowerValue.startsWith("https://")) {
                    const fileName = this.getFileNameFromUrl(lowerValue);
                    const fileParts = fileName.split(".");
                    if (fileParts.length > 0 && supportedExtensions.includes(fileParts[1])) {
                        ret.push(ff);
                    }
                }
            }
        });
        return ret;
    }
    async getPriceCategories(context, formFieldValues = null) {
        let price = 0;
        if (!formFieldValues) {
            formFieldValues = context.getCurrentFormFieldValues();
        }
        if (typeof context.getPriceForFormFieldsAsync === "function") {
            price = await context.getPriceForFormFieldsAsync(formFieldValues);
        }
        else if (typeof context.getPriceForFormFields === "function") {
            price = context.getPriceForFormFields(formFieldValues);
        }
        const r = {
            snippetPrices: context.snippetPrices.map((x) => x ? x.label : null),
            priceCategories: {},
            price: context.formatMoney(price),
            basePrice: price,
            productName: context.getProductName(),
            legalNotice: context.legalText,
            infoUrl: context.legalTextUrl
        };
        return r;
    }
    ;
    usePanelUi() {
        const loweruiVersion = this.Settings.uiSettings && this.Settings.uiSettings.uiVersion ? this.Settings.uiSettings.uiVersion.toLowerCase().trim() : "";
        return loweruiVersion === "bcui" || loweruiVersion === "panelui";
    }
    async onPriceChanged(priceChangedInfo, context) {
        try {
            let priceInfo = null;
            try {
                if (typeof priceChangedInfo.pageCount !== "undefined") {
                    if (context.currentPageCount !== priceChangedInfo.pageCount) {
                        context.currentPageCount = priceChangedInfo.pageCount;
                        if (context.additionalAttachParams && typeof context.additionalAttachParams["pageCountFormField"] !== "undefined") {
                            if (typeof context.onFormFieldChanged === "function") {
                                try {
                                    context.onFormFieldChanged(context.additionalAttachParams["pageCountFormField"], context.currentPageCount.toString(), "", "");
                                }
                                catch (ex) {
                                    console.error(ex);
                                }
                            }
                            if (typeof context.onFormFieldChangedAsync === "function") {
                                try {
                                    await context.onFormFieldChangedAsync(context.additionalAttachParams["pageCountFormField"], context.currentPageCount.toString(), "", "");
                                }
                                catch (ex) {
                                    console.error(ex);
                                }
                            }
                        }
                    }
                }
                if (priceChangedInfo.snippetPriceCategories && priceChangedInfo.snippetPriceCategories.length > 0) {
                    context.stickers = priceChangedInfo.snippetPriceCategories.filter((x) => context.snippetPrices && context.snippetPrices.length >= x.priceCategory).map((x) => {
                        return {
                            productVariantId: context.snippetPrices[x.priceCategory - 1].variantId,
                            quantity: x.amount
                        };
                    });
                }
                else {
                    context.stickers = [];
                }
                if (typeof priceChangedInfo.printedRecordsCount !== "undefined" && priceChangedInfo.printedRecordsCount > 0 && typeof context.propertyChanged === "function" && priceChangedInfo.hasCirculationColumn === true) {
                    context.propertyChanged("circulationRecordCount", priceChangedInfo.printedRecordsCount.toString());
                }
                priceInfo = await this.calculateCurrentPrices(priceChangedInfo, context);
            }
            catch (e) {
                console.error(e);
            }
            const iframe = document.getElementById("printess");
            if (iframe && !context.hidePricesInEditor) {
                setTimeout(() => {
                    iframe.contentWindow.postMessage({
                        cmd: "refreshPriceDisplay",
                        priceDisplay: priceInfo
                    }, "*");
                }, 0);
            }
            //BcUI
            const component = this.getPrintessComponent();
            if (component && component.editor) {
                component.editor.ui.refreshPriceDisplay(priceInfo);
            }
        }
        catch (e) {
        }
    }
    ;
    hideBcUiVersion(context, closeButtonClicked) {
        this.visible = false;
        const editor = this.getPrintessComponent();
        if (editor && editor.editor) {
            editor.editor.ui.hide();
        }
        if (typeof context.editorClosed === "function") {
            context.editorClosed(closeButtonClicked === true);
        }
        //Hide the web page scrolling
        document.body.classList.remove('hideAll');
    }
    async downloadImages(images) {
        const ret = [];
        for (let i = 0; i < images.length; ++i) {
            const response = await fetch(images[i].value);
            if (response.ok) {
                const blob = await response.blob();
                ret.push({
                    name: images[0].name,
                    data: new File([blob], this.getFileNameFromUrl(images[i].value), { type: blob.type })
                });
            }
            else {
                console.error("Unable to download image " + images[i].value + "; [" + response.status.toString() + "] " + response.statusText + ": " + await response.text());
            }
        }
        return ret;
    }
    async uploadImagesToBcUiEditor(files, editor) {
        if (files) {
            for (let i = 0; i < files.length; ++i) {
                const result = await editor.api.uploadImage(files[i].data, null, false);
                if (result) {
                    await editor.api.setFormFieldValue(files[i].name, result.name);
                }
            }
        }
    }
    async uploadUserImagesToBcUiEditor(editor, basketId = null, userId = null) {
        if (userId || basketId) {
            const result = await editor.api.importImages(userId || "", basketId);
        }
    }
    uploadImageToClassicEditor(iframe, file, formFieldName) {
        if (file) {
            iframe.contentWindow?.postMessage({ cmd: "uploadImage", parameters: [file, null, false, "ff_" + formFieldName] }, "*");
        }
    }
    uploadUserImagesToClassicEditor(iframe, basketId = null, userId = null) {
        if (basketId || userId) {
            iframe.contentWindow?.postMessage({ cmd: "importImages", parameters: [userId || "", basketId] }, "*");
        }
    }
    static generateUUID() {
        var d = new Date().getTime(); //Timestamp
        var d2 = ((typeof performance !== 'undefined') && performance.now && (performance.now() * 1000)) || 0; //Time in microseconds since page-load or 0 if unsupported
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16; //random number between 0 and 16
            if (d > 0) { //Use timestamp until depleted
                r = (d + r) % 16 | 0;
                d = Math.floor(d / 16);
            }
            else { //Use microseconds since page-load if supported
                r = (d2 + r) % 16 | 0;
                d2 = Math.floor(d2 / 16);
            }
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }
    static async getOrGenerateBasketId(context) {
        let ret = typeof context.getBasketId === "function" ? context.getBasketId() : "";
        if (!ret && typeof context.getBasketIdAsync === "function") {
            ret = await context.getBasketIdAsync() || null;
        }
        if (!ret) {
            if (!ret) {
                try {
                    ret = localStorage.getItem("printessUniqueBasketId");
                }
                catch (e) {
                    console.warn("Unable to read user id from local storage.");
                }
            }
            if (!ret) {
                ret = window["printessUniqueBasketId"];
            }
            if (!ret) {
                ret = PrintessEditor.generateUUID() + "_" + new Date().valueOf().toString();
                try {
                    localStorage.setItem("printessUniqueBasketId", ret);
                }
                catch (e) {
                    window["printessUniqueBasketId"] = ret;
                    console.warn("Unable to write user id to local storage.");
                }
            }
        }
        return ret || null;
    }
    static async getUserId(context) {
        let ret = typeof context.getUserId === "function" ? context.getUserId() : null;
        if (!ret && typeof context.getUserIdAsync === "function") {
            ret = await context.getUserIdAsync();
        }
        return ret;
    }
    async getFormFieldValue(formFieldName) {
        if (this.usePanelUi()) {
            const editor = this.getPrintessComponent();
            if (editor && editor.editor) {
                const formFieldValue = await editor.editor.api.getFormField(formFieldName);
                if (formFieldValue) {
                }
            }
        }
        else {
            document.getElementById("printess")?.contentWindow?.postMessage({ cmd: "getFormField", parameters: [formFieldName] }, "*");
        }
    }
    async showBcUiVersion(context, callbacks) {
        const that = this;
        const priceInfo = context.getPriceInfo();
        let isSaveToken = context && context.templateNameOrSaveToken && context.templateNameOrSaveToken.indexOf("st:") === 0;
        let pageCount = null;
        let formFields = null;
        let mergeTemplates = null;
        if (!isSaveToken) {
            formFields = PrintessEditor.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings());
            mergeTemplates = context.getMergeTemplates();
            if (context.additionalAttachParams && typeof context.additionalAttachParams["pageCountFormField"] !== "undefined") {
                const pageFormField = formFields.filter(x => x.name === context.additionalAttachParams["pageCountFormField"]);
                if (pageFormField && pageFormField.length > 0) {
                    let intValue = PrintessEditor.extractNumber(pageFormField[0].value);
                    if (!isNaN(intValue) && isFinite(intValue)) {
                        pageCount = intValue;
                    }
                }
            }
        }
        const startupParams = {};
        const loaderUrl = that.getLoaderUrl(this.Settings.editorUrl, this.Settings.editorVersion, startupParams);
        const printessLoader = await import(/* webpackIgnore: true */ loaderUrl);
        let printessComponent = that.getPrintessComponent();
        const closeTabListener = (evt) => {
            if (callbacks && typeof callbacks.onCloseTab === "function") {
                callbacks.onCloseTab(evt);
            }
        };
        if (this.Settings.showAlertOnTabClose === true) {
            window.addEventListener('beforeunload', closeTabListener);
            window.addEventListener('unload', closeTabListener);
        }
        if (printessComponent && printessComponent.editor) {
            printessComponent.style.display = "block";
            await printessComponent.editor.api.loadTemplateAndFormFields(context.templateNameOrSaveToken, mergeTemplates, formFields, null);
            if (!isSaveToken && pageCount !== null && pageCount > 0) {
                await printessComponent.editor.api.setBookInsidePages(pageCount);
            }
            setTimeout(async function () {
                if (context.hidePricesInEditor !== true) {
                    that.calculateCurrentPrices({}, context).then((priceChangedInfo) => {
                        printessComponent.editor.ui.refreshPriceDisplay(priceChangedInfo);
                    });
                }
                if (that.Settings.autoImportImageUrlsInFormFields === true) {
                    try {
                        const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings())));
                        await that.uploadImagesToBcUiEditor(images, printessComponent.editor);
                    }
                    catch (e) {
                        console.error(e);
                    }
                }
                if (that.Settings.autoImportUserImages === true) {
                    try {
                        let userId = await PrintessEditor.getUserId(context);
                        let basketId = await PrintessEditor.getOrGenerateBasketId(context);
                        if (userId || basketId) {
                            await that.uploadUserImagesToBcUiEditor(printessComponent.editor, basketId, userId);
                        }
                    }
                    catch (e) {
                        console.error(e);
                    }
                }
                await callbacks.onLoadAsync(context.templateNameOrSaveToken);
            }, 1000);
            printessComponent.editor.ui.show();
        }
        else {
            let theme = that.Settings.uiSettings ? (that.Settings.uiSettings.theme || "") : "";
            if (typeof theme !== "string" && theme.error) {
                theme = theme.error;
            }
            if (!theme || theme.indexOf("json not allowed") === 0) {
                theme = "DEFAULT";
            }
            const attachParams = {
                domain: that.Settings.apiDomain,
                mergeTemplates: mergeTemplates,
                formFields: formFields,
                token: that.Settings.shopToken,
                templateName: context.templateNameOrSaveToken, // "Premier Test-3",// "test Trigger Dialog",  // "price-tester", // "Premier Test", //  "Children's book", // "Label FF Test", //"test Trigger Dialog",   "test Trigger Dialog", // "Bathrobe Man", //
                //templateVersion: "publish",//"draft"
                translationKey: "auto", //"en"
                basketId: await PrintessEditor.getOrGenerateBasketId(context),
                shopUserId: await PrintessEditor.getUserId(context),
                // mobileMargin: {left: 20, right: 40, top: 30, bottom: 40},
                // allowZoomAndPan: false,
                snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
                theme: theme,
                addToBasketCallback: (token, thumbnailUrl) => {
                    const addToBasket = (saveToken, thumbnailUrl) => {
                        window.removeEventListener('beforeunload', closeTabListener);
                        window.removeEventListener('unload', closeTabListener);
                        if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
                            callbacks.onAddToBasketAsync(token, thumbnailUrl).then(() => { });
                        }
                    };
                    if (typeof context.onAllowAddToBasket === "function") {
                        const result = context.onAllowAddToBasket(token, thumbnailUrl);
                        if (typeof result !== "boolean" || result === true) {
                            addToBasket(token, thumbnailUrl);
                        }
                    }
                    else if (typeof context.onAllowAddToBasketAsync === "function") {
                        context.onAllowAddToBasketAsync(token, thumbnailUrl).then((result) => {
                            if (typeof result !== "boolean" || result === true) {
                                addToBasket(token, thumbnailUrl);
                            }
                        });
                    }
                    else {
                        addToBasket(token, thumbnailUrl);
                    }
                },
                formFieldChangedCallback: (name, value, tag, label, ffLabel) => {
                    if (callbacks && typeof callbacks.onFormFieldChangedAsync === "function") {
                        callbacks.onFormFieldChangedAsync(name, value, ffLabel, label).then(() => { });
                    }
                },
                priceChangeCallback: (priceInfo) => {
                    if (callbacks && typeof callbacks.onPriceChangedAsync === "function") {
                        callbacks.onPriceChangedAsync(priceInfo).then(() => { });
                    }
                },
                backButtonCallback: (saveToken) => {
                    window.removeEventListener('beforeunload', closeTabListener);
                    window.removeEventListener('unload', closeTabListener);
                    that.hideBcUiVersion(context, true);
                },
                saveTemplateCallback: (saveToken, type, thumbnailUrl) => {
                    if (typeof callbacks.onSaveAsync === "function") {
                        callbacks.onSaveAsync(saveToken, thumbnailUrl);
                    }
                    if (type && type === "close") {
                        that.hideBcUiVersion(context, true);
                    }
                }
            };
            if (typeof context.showSplitterGridSizeButton !== "undefined" && context.showSplitterGridSizeButton !== null) {
                attachParams["showSplitterGridSizeButton"] = context.showSplitterGridSizeButton === true || context.showSplitterGridSizeButton === "true";
            }
            if (!isSaveToken && pageCount !== null && pageCount >= 1) {
                attachParams["bookInsidePages"] = pageCount;
            }
            const globalSettings = PrintessEditor.getGlobalShopSettings();
            if (typeof globalSettings.getFormFieldProperties === "function") {
                attachParams.formFieldProperties = globalSettings.getFormFieldProperties();
            }
            const printess = await printessLoader.load(attachParams);
            printessComponent = that.getPrintessComponent();
            printessComponent.editor = printess;
            setTimeout(async function () {
                const printessComponent = that.getPrintessComponent();
                if (!printessComponent) {
                    return;
                }
                if (context.hidePricesInEditor !== true) {
                    const priceChangedInfo = await that.calculateCurrentPrices({}, context);
                    printessComponent.editor.ui.refreshPriceDisplay(priceChangedInfo);
                }
                if (that.Settings.autoImportImageUrlsInFormFields === true) {
                    try {
                        const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings())));
                        await that.uploadImagesToBcUiEditor(images, printessComponent.editor);
                    }
                    catch (e) {
                        console.error(e);
                    }
                }
                if (that.Settings.autoImportUserImages === true) {
                    try {
                        let userId = await PrintessEditor.getUserId(context);
                        let basketId = await PrintessEditor.getOrGenerateBasketId(context);
                        if (userId || basketId) {
                            await that.uploadUserImagesToBcUiEditor(printessComponent.editor, basketId, userId);
                        }
                    }
                    catch (e) {
                        console.error(e);
                    }
                }
                await callbacks.onLoadAsync(attachParams.templateName);
            }, 1000);
        }
    }
    static extractNumber(inputStr) {
        let c = "0123456789";
        function check(x) {
            return c.includes(x) ? true : false;
        }
        return parseInt([...inputStr].reduce((x, y) => (check(y) ? x + y : x), ""));
    }
    async show(context) {
        const that = this;
        this.lastSaveDate = new Date();
        let isSaveToken = context && context.templateNameOrSaveToken && context.templateNameOrSaveToken.indexOf("st:") === 0;
        this.visible = true;
        const callbacks = {
            onBack: () => {
                that.hide(context, true);
            },
            onAddToBasketAsync: async (saveToken, thumbnailUrl) => {
                let result = null;
                if (typeof context.onAddToBasketAsync === "function") {
                    result = await context.onAddToBasketAsync(saveToken, thumbnailUrl);
                }
                else {
                    result = context.onAddToBasket(saveToken, thumbnailUrl);
                }
                if (result && result.waitUntilClosingMS) {
                    setTimeout(function () {
                        if (typeof result.executeBeforeClosing === "function") {
                            result.executeBeforeClosing();
                        }
                        that.hide(context, false);
                    }, result.waitUntilClosingMS);
                }
                else {
                    if (result && typeof result.executeBeforeClosing === "function") {
                        result.executeBeforeClosing();
                    }
                    that.hide(context, false);
                }
            },
            onFormFieldChangedAsync: async (formFieldName, formFieldValue, formFieldLabel, formFieldValueLabel) => {
                const formField = that.reverseFormFieldMapping(formFieldName, formFieldValue, context.getFormFieldMappings());
                if (typeof context.onFormFieldChangedAsync === "function") {
                    await context.onFormFieldChanged(formField.name, formField.value, formFieldLabel, formFieldValueLabel);
                }
                else if (typeof context.onFormFieldChanged === "function") {
                    context.onFormFieldChanged(formField.name, formField.value, formFieldLabel, formFieldValueLabel);
                }
            },
            onPriceChangedAsync: async (priceInfo) => {
                await that.onPriceChanged(priceInfo, context);
            },
            onGetFormField: (result) => {
                if (typeof context.onGetFormField === "function") {
                    context.onGetFormField(result);
                }
            },
            onSaveAsync: async (saveToken, thumbnailUrl) => {
                that.lastSaveDate = new Date();
                if (typeof context.onSaveAsync === "function") {
                    await context.onSaveAsync(saveToken, thumbnailUrl);
                }
                else if (typeof context.onSave === "function") {
                    context.onSave(saveToken, thumbnailUrl);
                }
            },
            onLoadAsync: async (currentTemplateNameOrSaveToken) => {
                if (typeof context.onLoadAsync === "function") {
                    await context.onLoadAsync(currentTemplateNameOrSaveToken);
                }
                else if (typeof context.onLoad === "function") {
                    context.onLoad(currentTemplateNameOrSaveToken);
                }
            },
            onCloseTab: (evt) => {
                evt.preventDefault();
                evt.returnValue = '';
            }
        };
        if (context) {
            context.save = function () {
                that.save(callbacks);
            };
        }
        if (this.usePanelUi()) {
            that.showBcUiVersion(context, callbacks);
        }
        else {
            const priceInfo = context.getPriceInfo();
            let pageCount = null;
            let formFields = null;
            let mergeTemplates = null;
            if (!isSaveToken) {
                formFields = PrintessEditor.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings());
                mergeTemplates = context.getMergeTemplates();
                if (context.additionalAttachParams && typeof context.additionalAttachParams["pageCountFormField"] !== "undefined") {
                    const pageFormField = formFields.filter(x => x.name === context.additionalAttachParams["pageCountFormField"]);
                    if (pageFormField && pageFormField.length > 0) {
                        let intValue = PrintessEditor.extractNumber(pageFormField[0].value);
                        if (!isNaN(intValue) && isFinite(intValue)) {
                            pageCount = intValue;
                        }
                    }
                }
            }
            const iFrame = await this.initializeIFrame(callbacks, context, this.Settings);
            if (iFrame.getAttribute('data-attached') === "false") {
                try {
                    const attachParams = {
                        domain: that.Settings.apiDomain,
                        token: this.Settings.shopToken || "",
                        templateName: context.templateNameOrSaveToken,
                        showBuyerSide: true,
                        templateUserId: '',
                        basketId: await PrintessEditor.getOrGenerateBasketId(context),
                        shopUserId: await PrintessEditor.getUserId(context),
                        formFields: formFields,
                        snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
                        mergeTemplates: mergeTemplates
                    };
                    if (typeof context.showSplitterGridSizeButton !== "undefined" && context.showSplitterGridSizeButton !== null) {
                        attachParams["showSplitterGridSizeButton"] = context.showSplitterGridSizeButton === true || context.showSplitterGridSizeButton === "true";
                    }
                    const globalSettings = PrintessEditor.getGlobalShopSettings();
                    if (typeof globalSettings.getFormFieldProperties === "function") {
                        attachParams.formFieldProperties = globalSettings.getFormFieldProperties();
                    }
                    if (this.Settings.uiSettings && this.Settings.uiSettings.theme) {
                        attachParams["theme"] = this.Settings.uiSettings.theme;
                    }
                    if (this.Settings.attachParams) {
                        for (const paramName in this.Settings.attachParams) {
                            if (this.Settings.attachParams.hasOwnProperty(paramName)) {
                                attachParams[paramName] = this.Settings.attachParams[paramName];
                            }
                        }
                    }
                    if (context.additionalAttachParams) {
                        for (const prop in context.additionalAttachParams) {
                            if (context.additionalAttachParams.hasOwnProperty(prop)) {
                                attachParams[prop] = context.additionalAttachParams[prop];
                            }
                        }
                    }
                    if (!isSaveToken && pageCount !== null && pageCount >= 1) {
                        attachParams["bookInsidePages"] = pageCount;
                    }
                    iFrame.contentWindow.postMessage({
                        cmd: 'attach',
                        properties: attachParams
                    }, '*');
                    iFrame.setAttribute('data-attached', "true");
                    setTimeout(function () { iFrame.contentWindow.focus(); }, 0);
                }
                catch (e) {
                    console.error(e);
                }
            }
            else {
                let undef;
                const loadParams = {
                    templateNameOrToken: context.templateNameOrSaveToken,
                    mergeTemplates: mergeTemplates,
                    formFields: formFields,
                    snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
                    formFieldProperties: undef,
                    clearExchangeCaches: true
                };
                if (this.Settings.attachParams) {
                    if (this.Settings.attachParams.formFieldProperties) {
                        loadParams.formFieldProperties = this.Settings.attachParams.formFieldProperties;
                    }
                    if (typeof this.Settings.attachParams.clearExchangeCaches !== "undefined") {
                        loadParams.clearExchangeCaches = this.Settings.attachParams.clearExchangeCaches === false ? false : true;
                    }
                }
                if (context && context.additionalAttachParams) {
                    if (context.additionalAttachParams.formFieldProperties) {
                        loadParams.formFieldProperties = context.additionalAttachParams.formFieldProperties;
                    }
                    if (typeof context.additionalAttachParams.clearExchangeCaches !== "undefined") {
                        loadParams.clearExchangeCaches = context.additionalAttachParams.clearExchangeCaches === false ? false : true;
                    }
                }
                iFrame.contentWindow.postMessage({
                    cmd: "loadTemplateAndFormFields",
                    parameters: [loadParams.templateNameOrToken, loadParams.mergeTemplates, loadParams.formFields, loadParams.snippetPriceCategoryLabels, loadParams.formFieldProperties, loadParams.clearExchangeCaches]
                }, '*');
                setTimeout(function () { iFrame.contentWindow.focus(); }, 0);
                if (!isSaveToken && pageCount !== null && pageCount > 0) {
                    setTimeout(function () {
                        iFrame.contentWindow.postMessage({
                            cmd: "setPageInsidePages",
                            parameters: [pageCount]
                        }, '*');
                    }, 0);
                }
            }
        }
        //Hide the web page scrolling
        document.body.classList.add('hideAll');
        var root = document.getElementsByTagName('html');
        if (root && root.length > 0) {
            root[0].classList.add('printess-editor-open');
        }
        const iframeWrapper = document.getElementById("printess-container");
        if (iframeWrapper) {
            // iframeWrapper.style.display = "block !important";
            iframeWrapper.style.setProperty('display', 'block', 'important');
        }
        if (!this.show.saveInterval) {
            this.show.saveInterval = setInterval(function () {
                if (that.visible && context.currentSaveTimerInMinutes !== null && context.currentSaveTimerInMinutes > 0 && typeof context.onSaveTimer === "function") {
                    const timeDifferenceMs = ((new Date()).getTime() - that.lastSaveDate.getTime());
                    if (timeDifferenceMs > (context.currentSaveTimerInMinutes * 60000)) {
                        that.lastSaveDate = new Date();
                        context.onSaveTimer();
                    }
                }
            }, 30000);
        }
    }
    hide(context, closeButtonClicked) {
        this.visible = false;
        if (this.usePanelUi()) {
            const editor = this.getPrintessComponent();
            if (editor && editor.editor) {
                editor.editor.ui.hide();
            }
        }
        else {
            const iframeWrapper = document.getElementById("printess-container");
            if (iframeWrapper) {
                iframeWrapper.style.display = "none";
            }
        }
        document.body.classList.remove('hideAll');
        var root = document.getElementsByTagName('html');
        if (root && root.length > 0) {
            root[0].classList.remove('printess-editor-open');
        }
        if (typeof context.editorClosed === "function") {
            context.editorClosed(closeButtonClicked === true);
        }
    }
    static getGlobalShopSettings() {
        return (window && window["printessGlobalConfig"] ? window["printessGlobalConfig"] : {});
    }
    static getGlobalFormFields() {
        const settings = PrintessEditor.getGlobalShopSettings();
        let formFields;
        if (typeof settings.formFields != undefined && settings.formFields) {
            if (typeof settings.formFields == "function") {
                try {
                    formFields = settings.formFields();
                }
                catch (e) {
                    console.error(e);
                }
            }
            else {
                formFields = settings.formFields;
            }
        }
        return formFields || {};
    }
    static ensureScriptExecution(scriptId, methodName = null, params = null) {
        const scriptTag = document.getElementById(scriptId);
        if (scriptTag && !scriptTag.getAttribute("data-replaced")) {
            const newTag = document.createElement('script');
            newTag.setAttribute("id", scriptId);
            newTag.setAttribute("data-replaced", "true");
            newTag.type = 'text/javascript';
            newTag.text = scriptTag.text;
            scriptTag.replaceWith(newTag);
        }
        if (methodName && typeof window[methodName] === "function") {
            window[methodName].apply(null, params);
        }
    }
} function initPrintessEditor(shopToken, editorUrl, editorVersion, startupLogoUrl, showStartupAnimation, theme, startupBackgroundColor = "") {
    let editorSettings;
    if (shopToken && typeof shopToken !== "string") {
        editorSettings = {
            apiDomain: shopToken["apiDomain"] ? shopToken["apiDomain"] : null,
            shopToken: shopToken["shopToken"] ? shopToken["shopToken"] : "",
            editorUrl: shopToken["editorUrl"] ? shopToken["editorUrl"] : "",
            editorVersion: shopToken["editorVersion"] ? shopToken["editorVersion"] : "",
            attachParams: shopToken["attachParams"] ? shopToken["attachParams"] : "",
            showAlertOnTabClose: typeof shopToken["showTabClosingAlert"] !== "undefined" && shopToken["showTabClosingAlert"] !== null ? shopToken["showTabClosingAlert"] === true : false,
            uiSettings: {
                showStartupAnimation: typeof shopToken["showStartupAnimation"] !== "undefined" && shopToken["showStartupAnimation"] !== null ? shopToken["showStartupAnimation"] === true : true,
                startupBackgroundColor: shopToken["startupBackgroundColor"] || "#ffffff",
                theme: shopToken["theme"] || "",
                startupLogoUrl: shopToken["startupLogoUrl"] || "",
                uiVersion: shopToken["uiVersion"] || ""
            },
            ...shopToken,
            autoImportUserImages: shopToken["autoImportUserImages"] ? shopToken["autoImportUserImages"] === true : false
        };
    }
    else {
        editorSettings = {
            apiDomain: null,
            shopToken: shopToken || "",
            editorUrl: editorUrl,
            editorVersion: editorVersion,
            uiSettings: {
                showStartupAnimation: showStartupAnimation,
                startupBackgroundColor: startupBackgroundColor,
                startupLogoUrl: startupLogoUrl,
                theme: theme
            }
        };
    }
    return new PrintessEditor(editorSettings);
}