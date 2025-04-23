class PrintessEditor {
    constructor(settings) {
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
    applyFormFieldMappings(formFields, mappings) {
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
        const eventListener = (evt) => {
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
                    window.removeEventListener('message', eventListener);
                    window.removeEventListener('beforeunload', closeTabListener);
                    window.removeEventListener('unload', closeTabListener);
                    iFrame.setAttribute("printessHasListener", "false");
                    if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
                        callbacks.onAddToBasketAsync(evt.data.token, evt.data.thumbnailUrl).then(() => { });
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
                case 'renderFirstPageImage': {
                    if (callbacks && typeof callbacks.onRenderedFirstPageImageAsync === "function") {
                        callbacks.onRenderedFirstPageImageAsync(evt.data.result);
                    }
                    break;
                }
                case 'save': {
                    if (callbacks && typeof callbacks.onSaveAsync === "function") {
                        callbacks.onSaveAsync(evt.data.token);
                    }
                    break;
                }
                case 'loaded': {
                    if (this.Settings.autoImportImageUrlsInFormFields === true) {
                        try {
                            this.downloadImages(this.getImagesInFormFields(that.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings()))).then((images) => {
                                if (!this.tempUploadImages) {
                                    this.tempUploadImages = images;
                                }
                                else {
                                    this.tempUploadImages = [
                                        ...this.tempUploadImages,
                                        ...images
                                    ];
                                }
                                if (images.length > 0) {
                                    this.uploadImageToClassicEditor(iFrame, images[0].data, images[0].name);
                                }
                            });
                        }
                        catch (e) {
                            console.error(e);
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
    uploadImageToClassicEditor(iframe, file, formFieldName) {
        if (file) {
            iframe.contentWindow?.postMessage({ cmd: "uploadImage", parameters: [file, null, false, "ff_" + formFieldName] }, "*");
        }
    }
    async showBcUiVersion(context, callbacks) {
        const that = this;
        const priceInfo = context.getPriceInfo();
        let isSaveToken = context && context.templateNameOrSaveToken && context.templateNameOrSaveToken.indexOf("st:") === 0;
        let formFields = null;
        let mergeTemplates = null;
        if (!isSaveToken) {
            formFields = that.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings());
            mergeTemplates = context.getMergeTemplates();
        }
        const startupParams = {};
        const loaderUrl = that.getLoaderUrl(this.Settings.editorUrl, this.Settings.editorVersion, startupParams);
        const printessLoader = await import(/* webpackIgnore: true */ loaderUrl);
        let printessComponent = that.getPrintessComponent();
        if (printessComponent && printessComponent.editor) {
            printessComponent.style.display = "block";
            context.renderFirstPageImageAsync = async (maxThumbnailWidth, maxThumbnailHeight) => {
                const url = await printessComponent.editor.api.renderFirstPageImage("thumbnail.png", undefined, maxThumbnailWidth, maxThumbnailHeight);
                if (context && typeof context.onRenderFirstPageImageAsync === "function") {
                    await context.onRenderFirstPageImageAsync(url);
                }
                else if (context && typeof context.onRenderFirstPageImage === "function") {
                    context.onRenderFirstPageImage(url);
                }
            };
            await printessComponent.editor.api.loadTemplateAndFormFields(context.templateNameOrSaveToken, mergeTemplates, formFields, null);
            printessComponent.editor.ui.show();
        }
        else {
            const attachParams = {
                domain: that.Settings.apiDomain,
                mergeTemplates: mergeTemplates,
                formFields: formFields,
                token: that.Settings.shopToken,
                templateName: context.templateNameOrSaveToken,
                //templateVersion: "publish",//"draft"
                translationKey: "",
                basketId: typeof context.getBasketId === "function" ? context.getBasketId() || 'Some-Unique-Basket-Or-Session-Id' : 'Some-Unique-Basket-Or-Session-Id',
                shopUserId: typeof context.getUserId === "function" ? context.getUserId() || 'Some-Unique-Basket-Or-Session-Id' : 'Some-Unique-Shop-User-Id',
                // mobileMargin: {left: 20, right: 40, top: 30, bottom: 40},
                // allowZoomAndPan: false,
                snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
                theme: that.Settings.uiSettings ? (that.Settings.uiSettings.theme || "") : "",
                addToBasketCallback: (token, thumbnailUrl) => {
                    if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
                        callbacks.onAddToBasketAsync(token, thumbnailUrl).then(() => { });
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
                    that.hideBcUiVersion(context, true);
                },
                saveTemplateCallback: (saveToken, type) => {
                    if (typeof callbacks.onSaveAsync === "function") {
                        callbacks.onSaveAsync(saveToken);
                    }
                    if (type && type === "close") {
                        that.hideBcUiVersion(context, true);
                    }
                },
                loadTemplateCallback: (param) => {
                    if (this.Settings.autoImportImageUrlsInFormFields === true) {
                        try {
                            this.downloadImages(this.getImagesInFormFields(that.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings()))).then((images) => {
                                this.uploadImagesToBcUiEditor(images, printessComponent.editor).then((x) => {
                                });
                            });
                        }
                        catch (e) {
                            console.error(e);
                        }
                    }
                    callbacks.onLoadAsync(attachParams.templateName);
                }
            };
            const printess = await printessLoader.load(attachParams);
            printessComponent = that.getPrintessComponent();
            if (printessComponent) {
                printessComponent.editor = printess;
            }
        }
    }
    async show(context) {
        const that = this;
        let isSaveToken = context && context.templateNameOrSaveToken && context.templateNameOrSaveToken.indexOf("st:") === 0;
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
            onRenderedFirstPageImageAsync: async (result) => {
                if (typeof context.onRenderFirstPageImageAsync === "function") {
                    await context.onRenderFirstPageImageAsync(result);
                }
                else if (typeof context.onRenderFirstPageImage === "function") {
                    context.onRenderFirstPageImage(result);
                }
            },
            onSaveAsync: async (saveToken) => {
                if (typeof context.onSaveAsync === "function") {
                    await context.onSaveAsync(saveToken, "");
                }
                else if (typeof context.onSave === "function") {
                    context.onSave(saveToken, "");
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
        if (this.usePanelUi()) {
            that.showBcUiVersion(context, callbacks);
        }
        else {
            const priceInfo = context.getPriceInfo();
            let formFields = null;
            let mergeTemplates = null;
            if (!isSaveToken) {
                formFields = this.applyFormFieldMappings(context.getCurrentFormFieldValues(), context.getFormFieldMappings());
                mergeTemplates = context.getMergeTemplates();
            }
            const iFrame = await this.initializeIFrame(callbacks, context, this.Settings);
            context.renderFirstPageImageAsync = (maxThumbnailWidth, maxThumbnailHeight) => {
                setTimeout(function () {
                    iFrame.contentWindow.postMessage({
                        cmd: "renderFirstPageImage",
                        properties: {}
                    }, "*");
                }, 0);
                return Promise.resolve();
            };
            if (iFrame.getAttribute('data-attached') === "false") {
                try {
                    const attachParams = {
                        domain: that.Settings.apiDomain,
                        token: this.Settings.shopToken || "",
                        templateName: context.templateNameOrSaveToken,
                        showBuyerSide: true,
                        templateUserId: '',
                        basketId: typeof context.getBasketId === "function" ? context.getBasketId() || 'Some-Unique-Basket-Or-Session-Id' : 'Some-Unique-Basket-Or-Session-Id',
                        shopUserId: typeof context.getUserId === "function" ? context.getUserId() || 'Some-Unique-Basket-Or-Session-Id' : 'Some-Unique-Shop-User-Id',
                        formFields: formFields,
                        snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
                        mergeTemplates: mergeTemplates
                    };
                    if (typeof context.showSplitterGridSizeButton !== "undefined" && context.showSplitterGridSizeButton !== null) {
                        attachParams["showSplitterGridSizeButton"] = context.showSplitterGridSizeButton === true || context.showSplitterGridSizeButton === "true";
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
                    if (typeof context.additionalAttachParams !== "undefined" || context.additionalAttachParams !== null) {
                        for (const prop in context.additionalAttachParams) {
                            if (context.additionalAttachParams.hasOwnProperty(prop)) {
                                attachParams[prop] = context.additionalAttachParams[prop];
                            }
                        }
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
    }
    hide(context, closeButtonClicked) {
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
}function initPrintessEditor(shopToken, editorUrl, editorVersion, startupLogoUrl, showStartupAnimation, theme, startupBackgroundColor = "") {
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