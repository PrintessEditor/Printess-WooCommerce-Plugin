export interface IPriceInfo {
  snippetPrices: string[];
  priceCategories: Record<string, string>,
  price: string,
  productName: string,
  legalNotice: string,
  infoUrl: string
};

export interface IStickerInfo {
  productVariantId: number;
  quantity: number;
};

export interface ISnippetPriceUsage {
  priceCategory: number;
  amount: number;
};

export interface IPriceChangedInfo {
  pageCount?: number | null | undefined,
  priceCategories: string[],
  priceRelevantFormFields: Record<string, Record<string, string>>;
  snippetPriceCategories: ISnippetPriceUsage[];
  testModeEnabled: boolean;
  printedRecordsCount?: number;
  pureRecordsCount?: number;
  hasCirculationColumn?: boolean;

};

export interface ISnippetPrice {
  "label": string,
  "priceInCent": number,
  "variantId": number
}

export interface IMergeTemplate {
  "templateName"?: string;
  "mergeMode"?: string | null | undefined;
  "documentName"?: string | null | undefined;
  "spreadIndex"?: number | null | undefined;
  "templateVersion"?: string | null | undefined;
}

export interface IFormField {
  name: string;
  value: string;
}
export interface IFormFieldMapping {
  formField: string;
  values: Record<string, string>;
}

export interface IUISettings {
  uiVersion?: string;
  startupLogoUrl?: string;
  showStartupAnimation: boolean;
  startupBackgroundColor: string;
  theme: string;
}

export interface IEditorSettings {
  uiSettings?: IUISettings;
  shopToken?: string;
  editorUrl?: string;
  editorVersion?: string;
  showAlertOnTabClose?: boolean;
  attachParams?: Record<string, any>;
  apiDomain?: string;
  autoImportImageUrlsInFormFields?: boolean;
  autoImportUserImages?: boolean;
  translationKey?: string;
}

export interface IPricePerUsageEntry {
  formFieldName: string;
  pricePerUsage: number;
}

export interface ICallbackParams extends Record<string, any> {
  product: Record<string, any>,
  selectedVariant: Record<string, any>,
  basketItemId: string,
  formFieldValues: Record<string, string>
}

export interface ISaveTokenCallbackParams extends ICallbackParams {
  previousSaveToken?: string,
  initialSaveToken?: string,
}

export interface ICustomLoader {
  onShowLoader(): void;
  onHideLoader(): void;
}

export interface IGlobalShopSettings {
  onAddToBasket?: (saveToken: string, thumbnailUrl: string, params?: ISaveTokenCallbackParams) => void,
  onSave?: (saveToken: string, thumbnailUrl: string, params?: ISaveTokenCallbackParams) => void,
  onLoad?: (currentTemplateNameOrSaveToken: string, params?: ISaveTokenCallbackParams) => void,
  formFields?: Record<string, string> | (() => Record<string, string>),
  filterFormFields?: (formFieldValues: Record<string, string>) => Record<string, string>,
  onFormFieldChanged?: (formFieldName: string, formFieldValue: string, formFieldLabel: string, forshowmFieldValueLabel: string, params: ICallbackParams) => void,
  getPriceForFormFields?: (formFields: Record<string, string>, printessCalculated: number, params: ICallbackParams) => number,
  getPriceForFormFieldsAsync?: (formFields: Record<string, string>, printessCalculated: number, params: ICallbackParams) => Promise<number>;
  attachParams?: Record<string, any>,
  pricePerUsageFields: Record<string, number> | IPricePerUsageEntry[];
  onValidateEditorOpen?: (settings: Record<string, any>) => boolean;
  getShopUserId?: () => string | null;
  getShopUserIdAsync?: () => Promise<string | null>;
  getBasketId?: () => string | null;
  getBasketIdAsync?: () => Promise<string | null>;
  getFormFieldProperties?: () => [Record<string, any>];
  customLoader?: ICustomLoader;

  onAllowAddToBasket?: (saveToken: string, thumbnailUrl: string) => boolean,
  onAllowAddToBasketAsync?: (saveToken: string, thumbnailUrl: string) => Promise<boolean>
}

export interface IAddToBasketParameters {
  pageCount?: number
  minPages?: number
  additionalPages?: number
}

export interface IShopContext {
  templateNameOrSaveToken: string;
  stickers: IStickerInfo[];
  legalText: string;
  legalTextUrl: string;
  snippetPrices: ISnippetPrice[];
  chargeEachStickerUsage: boolean;
  hidePricesInEditor?: boolean;
  showSplitterGridSizeButton?: boolean | string | undefined | null;
  additionalAttachParams?: Record<string, any> | undefined | null;
  currentPageCount?: number | null | undefined;
  currentSaveTimerInMinutes?: number;
  currentRecordCount?: number;

  getProductName: () => string;
  getPriceInfo: () => IPriceInfo | null;
  getMergeTemplates: () => IMergeTemplate[];
  formatMoney: (price: number) => string;

  onFormFieldChanged?: (formField: string, value: string, formFieldLabel: string, valueLabel: string) => void;
  onFormFieldChangedAsync?: (formField: string, value: string, formFieldLabel: string, valueLabel: string) => Promise<void>;
  onAllowAddToBasket?: (saveToken: string, thumbnailUrl: string) => boolean,
  onAllowAddToBasketAsync?: (saveToken: string, thumbnailUrl: string) => Promise<boolean>,
  onAddToBasket?: (saveToken: string, thumbnailUrl: string, params?: IAddToBasketParameters) => void;
  onAddToBasketAsync?: (saveToken: string, thumbnailUrl: string, params?: IAddToBasketParameters) => Promise<void>;
  onSave?: null | undefined | ((saveToken: string, thumbnailUrl: string) => void);
  onSaveAsync?: null | undefined | ((saveToken: string, thumbnailUrl: string) => Promise<void>);
  onLoad?: null | undefined | ((currentTemplateNameOrSaveToken: string) => void);
  onLoadAsync?: null | undefined | ((currentTemplateNameOrSaveToken: string) => Promise<void>);

  getCurrentFormFieldValues: () => Record<string, string>;
  getPriceForFormFields?: (formFields: Record<string, string>) => number;
  getPriceForFormFieldsAsync?: (formFields: Record<string, string>) => Promise<number>;
  getFormFieldMappings: () => Record<string, IFormFieldMapping>;

  getBasketId?: () => string | null;
  getBasketIdAsync?: () => Promise<string | null>;
  getUserId?: () => string | null;
  getUserIdAsync?: () => Promise<string | null>;
  onSaveTimer?: () => void;

  editorClosed?: (closeButtonClicked: boolean) => void;

  propertyChanged?: (propertyName: string, propertyValue: string) => void;

  onGetFormField?: (result: { value: null | string | number | Array<Record<string, any>>, list?: Array<{ key: string, label?: string, description?: string, imageId?: string }> } | undefined) => void;

  save?: () => void;
  setFormFieldValue?: (formFieldName: string, formFieldValue: string) => void;
}

interface IEditorCallbacks {
  onBack: () => void;
  onAddToBasketAsync: (saveToken: string, thumbnailUrl: string, params?: IAddToBasketParameters) => Promise<void>;
  onFormFieldChangedAsync: (formFieldName: string, formFieldValue: string, formFieldCaption: string, formFieldCaptionLabel) => Promise<void>;
  onPriceChangedAsync: (priceInfo: IPriceChangedInfo) => Promise<void>;
  onSaveAsync: (saveToken: string, thumbnailUrl: string) => Promise<void>;
  onCloseTab: (evt) => void;
  onLoadAsync: (currentTemplateNameOrSaveToken: string) => Promise<void>;
  onGetFormField: (result: { value: null | string | number | Array<Record<string, any>>, list?: Array<{ key: string, label?: string, description?: string, imageId?: string }> } | undefined) => void;
}

export class PrintessEditor {
  private Settings: IEditorSettings;
  private ClassicEditorUrl: string;
  private lastSaveDate = new Date();
  private static visible: boolean = false;
  private static currentContext: IShopContext;

  constructor(settings: IEditorSettings) {
    if (!settings || !settings.shopToken) {
      throw "No shop token provided";
    }

    this.Settings = {
      ...settings
    };

    const hasUiSettings = typeof this.Settings.uiSettings !== "undefined" && this.Settings.uiSettings !== null;
    const startupSettings = {};

    if (hasUiSettings) {
      startupSettings["showAnimation"] = this.Settings.uiSettings.showStartupAnimation === true || (this.Settings.uiSettings.showStartupAnimation as any) == "true";

      if (this.Settings.uiSettings.startupLogoUrl) {
        startupSettings["imageUrl"] = this.Settings.uiSettings.startupLogoUrl;
      }

      if (this.Settings.uiSettings.startupBackgroundColor) {
        startupSettings["background"] = this.Settings.uiSettings.startupBackgroundColor;
      }
    }

    this.ClassicEditorUrl = this.getClassicEditorUrl(this.Settings.editorUrl, this.Settings.editorVersion, startupSettings) + "#" + encodeURIComponent(JSON.stringify(startupSettings));
  }

  private stripEditorVersion(editorVersion: string): string {
    editorVersion = (editorVersion ? editorVersion : "").trim();
    if (typeof editorVersion !== "undefined" && editorVersion != null) {
      if (!editorVersion) {
        editorVersion = "";
      } else {
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

  private sanitizeHost(host?: string): string {
    if (host) {
      host = host.trim();

      if (host.endsWith("/")) {
        return host;
      }

      return host + "/";
    }

    return host || "";
  }

  private getClassicEditorUrl(editorDomain: string, editorVersion: string, startupSettings: Record<string, any>): string {
    editorDomain = editorDomain || "https://editor.printess.com/";

    const hashIndex = editorDomain.indexOf("#");

    if (hashIndex > 0) {
      let json = editorDomain.substring(hashIndex + 1);

      if (json) {
        try {
          const decodedJson = JSON.parse(decodeURIComponent(json)) as Record<string, any>;

          if (decodedJson) {
            for (let propertyName in decodedJson) {
              if (decodedJson.hasOwnProperty(propertyName)) {
                startupSettings[propertyName] = decodedJson[propertyName];
              }
            }
          }
        } catch (e) {

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

  private getLoaderUrl(editorDomain: string, editorVersion: string, urlSettings: Record<string, any>) {
    editorDomain = editorDomain || "https://editor.printess.com/";

    const hashIndex = editorDomain.indexOf("#");

    if (hashIndex > 0) {
      let json = editorDomain.substring(hashIndex + 1);

      if (json) {
        try {
          const decodedJson = JSON.parse(decodeURIComponent(json)) as Record<string, any>;

          if (decodedJson) {
            for (let propertyName in decodedJson) {
              if (decodedJson.hasOwnProperty(propertyName)) {
                urlSettings[propertyName] = decodedJson[propertyName];
              }
            }
          }
        } catch (e) {

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

  private getPrintessComponent(): HTMLElement | null {
    return document.querySelector("printess-component") || null;
  }

  private save(callbacks: IEditorCallbacks): void {
    let printessComponent = document.querySelector("printess-component") || null

    if (printessComponent) {
      if (printessComponent && (printessComponent as any).editor) {
        (printessComponent as any).editor.api.saveAndGenerateBasketThumbnailUrl().then((result) => {
          callbacks.onSaveAsync(result.saveToken, result.basketUrl).then(() => {

          });
        });
      }
    } else {
      let iFrame: HTMLIFrameElement = document.getElementById("printess") as HTMLIFrameElement;

      if (iFrame) {
        iFrame.contentWindow?.postMessage({ cmd: "saveAndGenerateBasketThumbnailUrl", parameters: [] }, "*");
      }
    }
  }

  public static applyFormFieldMappings(formFields: Record<string, string>, mappings: Record<string, IFormFieldMapping>): IFormField[] {
    const ret: IFormField[] = [];

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

  private reverseFormFieldMapping(formFieldName: string, formFieldValue: string, mappings: Record<string, IFormFieldMapping>): { name: string, value: string } {
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

  private setViewportMeta() {
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
      } else {
        metaTag.setAttribute("content", "interactive-widget=resizes-content");
      }
    }
  }

  private async initializeIFrame(callbacks: IEditorCallbacks, settings: IEditorSettings): Promise<HTMLIFrameElement> {
    const that = this;
    let iFrame: HTMLIFrameElement = document.getElementById("printess") as HTMLIFrameElement;

    const closeTabListener = (evt): void => {
      if (callbacks && typeof callbacks.onCloseTab === "function") {
        callbacks.onCloseTab(evt);
      }
    }

    const eventListener = async (evt): Promise<void> => {
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
          const addToBasket = (saveToken: string, thumbnailUrl: string): void => {
            if (!saveToken) {
              setTimeout(function () {
                alert("It seems like the system was unable to save your changes. Please try again.");
              }, 0);

              return;
            }

            window.removeEventListener('message', eventListener);
            window.removeEventListener('beforeunload', closeTabListener);
            window.removeEventListener('unload', closeTabListener);
            iFrame.setAttribute("printessHasListener", "false");

            if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
              callbacks.onAddToBasketAsync(evt.data.token, evt.data.thumbnailUrl).then(() => { });
            }
          };

          try {
            if (typeof PrintessEditor.currentContext.onAllowAddToBasket === "function") {
              const result = PrintessEditor.currentContext.onAllowAddToBasket(evt.data.token, evt.data.thumbnailUrl);

              if (typeof result !== "boolean" || result === true) {
                addToBasket(evt.data.token, evt.data.thumbnailUrl);
              }
            } else if (typeof PrintessEditor.currentContext.onAllowAddToBasketAsync === "function") {
              PrintessEditor.currentContext.onAllowAddToBasketAsync(evt.data.token, evt.data.thumbnailUrl).then((result: Boolean) => {
                if (typeof result !== "boolean" || result === true) {
                  addToBasket(evt.data.token, evt.data.thumbnailUrl);
                }
              });
            } else {
              addToBasket(evt.data.token, evt.data.thumbnailUrl);
            }
          } catch (ex) {
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
            callbacks.onPriceChangedAsync(evt.data.priceInfo as IPriceChangedInfo).then(() => { });
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
              const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(PrintessEditor.currentContext.getCurrentFormFieldValues(), PrintessEditor.currentContext.getFormFieldMappings())));

              if (!(that as any).tempUploadImages) {
                (that as any).tempUploadImages = images;
              } else {
                (that as any).tempUploadImages = [
                  ...(that as any).tempUploadImages,
                  ...images
                ];
              }

              if (images.length > 0) {
                that.uploadImageToClassicEditor(iFrame, images[0].data, images[0].name);
              }
            } catch (e) {
              console.error(e);
            }
          }

          if (that.Settings.autoImportUserImages === true) {
            let userId = await PrintessEditor.getUserId();
            let basketId = await PrintessEditor.getOrGenerateBasketId();

            if (userId || basketId) {
              that.uploadUserImagesToClassicEditor(iFrame, basketId, userId);
            }
          }

          if (callbacks && typeof callbacks.onLoadAsync === "function") {
            callbacks.onLoadAsync(PrintessEditor.currentContext.templateNameOrSaveToken);
          }

          break;
        }
        case "uploadImage": {
          if ((this as any).tempUploadImages && evt.data.result) {
            const images = (this as any).tempUploadImages;

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

    return new Promise<HTMLIFrameElement>((resolve) => {
      if (!iFrame) {
        const container = document.createElement('div');
        container.setAttribute('id', 'printess-container');
        container.setAttribute('style', 'display: none; position: absolute; top: 0; bottom: 0; right: 0; left: 0; width: 100%; height: 100%; z-index: 100000;');
        iFrame = document.createElement('iframe') as HTMLIFrameElement;
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
      } else {
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

  private getFileNameFromUrl(fileName: string): string {
    return (fileName || "").split('#')[0].split('?')[0].split('/').pop();
  }

  private getImagesInFormFields(formFields: IFormField[]): IFormField[] {
    const ret: IFormField[] = [];
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

  private async getPriceCategories(formFieldValues: Record<string, string> | null = null): Promise<any> {
    let price: number = 0;

    if (!formFieldValues) {
      formFieldValues = PrintessEditor.currentContext.getCurrentFormFieldValues();
    }

    if (typeof PrintessEditor.currentContext.getPriceForFormFieldsAsync === "function") {
      price = await PrintessEditor.currentContext.getPriceForFormFieldsAsync(formFieldValues);
    } else if (typeof PrintessEditor.currentContext.getPriceForFormFields === "function") {
      price = PrintessEditor.currentContext.getPriceForFormFields(formFieldValues);
    }

    const r = {
      snippetPrices: PrintessEditor.currentContext.snippetPrices.map((x) => x ? x.label : null),
      priceCategories: {},
      price: PrintessEditor.currentContext.formatMoney(price),
      basePrice: price,
      productName: PrintessEditor.currentContext.getProductName(),
      legalNotice: PrintessEditor.currentContext.legalText,
      infoUrl: PrintessEditor.currentContext.legalTextUrl
    };

    return r;
  };

  private calculateCurrentPrices = async (priceInfo: IPriceChangedInfo): Promise<any> => {
    const r = await this.getPriceCategories();
    let basePrice = r.basePrice;

    if (priceInfo.snippetPriceCategories) {
      priceInfo.snippetPriceCategories.forEach((x) => {
        if (x && x.amount > 0 && PrintessEditor.currentContext.snippetPrices[x.priceCategory - 1]) {
          basePrice += PrintessEditor.currentContext.snippetPrices[x.priceCategory - 1].priceInCent;// * x.amount;
        }
      });
    }

    r.price = PrintessEditor.currentContext.formatMoney(basePrice);

    return r;
  }

  private usePanelUi(): boolean {
    const loweruiVersion = this.Settings.uiSettings && this.Settings.uiSettings.uiVersion ? this.Settings.uiSettings.uiVersion.toLowerCase().trim() : "";
    return loweruiVersion === "bcui" || loweruiVersion === "panelui";
  }

  private async onPriceChanged(priceChangedInfo: IPriceChangedInfo): Promise<void> {
    try {
      let priceInfo: IPriceInfo | null = null;

      try {
        if (typeof priceChangedInfo.pureRecordsCount !== undefined && priceChangedInfo.pureRecordsCount != null && priceChangedInfo.pureRecordsCount > 0) {
          PrintessEditor.currentContext.currentRecordCount = priceChangedInfo.pureRecordsCount;
        }

        if (typeof priceChangedInfo.pageCount !== "undefined") {
          if (PrintessEditor.currentContext.currentPageCount !== priceChangedInfo.pageCount) {
            PrintessEditor.currentContext.currentPageCount = priceChangedInfo.pageCount;

            if (PrintessEditor.currentContext.additionalAttachParams && typeof PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"] !== "undefined") {
              if (typeof PrintessEditor.currentContext.onFormFieldChanged === "function") {
                try {
                  PrintessEditor.currentContext.onFormFieldChanged(PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"], PrintessEditor.currentContext.currentPageCount.toString(), "", "");
                } catch (ex) {
                  console.error(ex);
                }
              }

              if (typeof PrintessEditor.currentContext.onFormFieldChangedAsync === "function") {
                try {
                  await PrintessEditor.currentContext.onFormFieldChangedAsync(PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"], PrintessEditor.currentContext.currentPageCount.toString(), "", "");
                } catch (ex) {
                  console.error(ex);
                }
              }
            }
          }
        }

        if (priceChangedInfo.snippetPriceCategories && priceChangedInfo.snippetPriceCategories.length > 0) {
          PrintessEditor.currentContext.stickers = priceChangedInfo.snippetPriceCategories.filter((x) => PrintessEditor.currentContext.snippetPrices && PrintessEditor.currentContext.snippetPrices.length >= x.priceCategory).map((x) => {
            return {
              productVariantId: PrintessEditor.currentContext.snippetPrices[x.priceCategory - 1].variantId,
              quantity: x.amount
            };
          });
        } else {
          PrintessEditor.currentContext.stickers = [];
        }

        if (typeof priceChangedInfo.printedRecordsCount !== "undefined" && priceChangedInfo.printedRecordsCount > 0 && typeof PrintessEditor.currentContext.propertyChanged === "function" && priceChangedInfo.hasCirculationColumn === true) {
          PrintessEditor.currentContext.propertyChanged("circulationRecordCount", priceChangedInfo.printedRecordsCount.toString());
        }

        priceInfo = await this.calculateCurrentPrices(priceChangedInfo) as IPriceInfo;
      } catch (e) {
        console.error(e);
      }

      if (!PrintessEditor.currentContext.hidePricesInEditor) {
        const iframe = document.getElementById("printess");

        if (iframe) {
          setTimeout(() => {
            (iframe as any).contentWindow.postMessage({
              cmd: "refreshPriceDisplay",
              priceDisplay: priceInfo
            }, "*");
          }, 0);
        }

        //BcUI
        const component = this.getPrintessComponent();
        if (component && (component as any).editor) {
          (component as any).editor.ui.refreshPriceDisplay(priceInfo);
        }
      }
    } catch (e) {

    }
  };

  private hideBcUiVersion(closeButtonClicked: boolean): void {
    if (!PrintessEditor.visible) {
      return;
    }

    try {
      const editor = this.getPrintessComponent();
      if (editor && (editor as any).editor) {
        (editor as any).editor.ui.hide();
      }

      if (typeof PrintessEditor.currentContext.editorClosed === "function") {
        PrintessEditor.currentContext.editorClosed(closeButtonClicked === true);
      }

      //Hide the web page scrolling
      document.body.classList.remove('hideAll');
    } finally {
      PrintessEditor.visible = false;
    }
  }

  public async downloadImages(images: IFormField[]): Promise<{ name: string, data: File }[]> {
    const ret: { name: string, data: File }[] = [];

    for (let i = 0; i < images.length; ++i) {
      const response = await fetch(images[i].value);

      if (response.ok) {
        const blob = await response.blob();
        ret.push({
          name: images[0].name,
          data: new File([blob], this.getFileNameFromUrl(images[i].value), { type: blob.type })
        })
      } else {
        console.error("Unable to download image " + images[i].value + "; [" + response.status.toString() + "] " + response.statusText + ": " + await response.text());
      }
    }

    return ret;
  }

  public async uploadImagesToBcUiEditor(files: { name: string, data: File }[], editor: any): Promise<void> {
    if (files) {
      for (let i = 0; i < files.length; ++i) {
        const result = await editor.api.uploadImage(files[i].data, null, false);

        if (result) {
          await editor.api.setFormFieldValue(files[i].name, result.name);
        }
      }
    }
  }

  public async uploadUserImagesToBcUiEditor(editor: any, basketId: string | null = null, userId: string | null = null): Promise<void> {
    if (userId || basketId) {
      const result = await editor.api.importImages(userId || "", basketId);
    }
  }

  public uploadImageToClassicEditor(iframe: HTMLIFrameElement, file: File, formFieldName: string): void {
    if (file) {
      iframe.contentWindow?.postMessage({ cmd: "uploadImage", parameters: [file, null, false, "ff_" + formFieldName] }, "*");
    }
  }

  public uploadUserImagesToClassicEditor(iframe: HTMLIFrameElement, basketId: string | null = null, userId: string | null = null): void {
    if (basketId || userId) {
      iframe.contentWindow?.postMessage({ cmd: "importImages", parameters: [userId || "", basketId] }, "*");
    }
  }

  public setFormFieldValueOnClassicEditor(iframe: HTMLIFrameElement, formFieldName: string, formFieldValue: string) {
    const formFields: Record<string, string> = {};
    formFields[formFieldName] = formFieldValue;

    const mappedFormFields = PrintessEditor.applyFormFieldMappings(formFields, PrintessEditor.currentContext.getFormFieldMappings());

    if (mappedFormFields.length > 0) {
      iframe.contentWindow?.postMessage({ cmd: "setFormFieldValue", parameters: [mappedFormFields[0].name, mappedFormFields[0].value] }, "*");
    }
  }

  public setFormFieldValueOnBcUi(editor: any, formFieldName: string, formFieldValue: string) {
    const formFields: Record<string, string> = {};
    formFields[formFieldName] = formFieldValue;

    const mappedFormFields = PrintessEditor.applyFormFieldMappings(formFields, PrintessEditor.currentContext.getFormFieldMappings());

    if (mappedFormFields.length > 0) {
      editor.api.setFormFieldValue(mappedFormFields[0].name, mappedFormFields[0].value);
    }
  }

  public static generateUUID(): string { // Public Domain/MIT
    var d = new Date().getTime();//Timestamp
    var d2 = ((typeof performance !== 'undefined') && performance.now && (performance.now() * 1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16;//random number between 0 and 16
      if (d > 0) {//Use timestamp until depleted
        r = (d + r) % 16 | 0;
        d = Math.floor(d / 16);
      } else {//Use microseconds since page-load if supported
        r = (d2 + r) % 16 | 0;
        d2 = Math.floor(d2 / 16);
      }
      return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
  }

  public static async getOrGenerateBasketId(): Promise<string | null> {
    let ret: string | null = null;

    if (PrintessEditor.currentContext) {
      ret = typeof PrintessEditor.currentContext.getBasketId === "function" ? PrintessEditor.currentContext.getBasketId() : "";

      if (!ret && typeof PrintessEditor.currentContext.getBasketIdAsync === "function") {
        ret = await PrintessEditor.currentContext.getBasketIdAsync() || null;
      }
    }

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

    return ret || null;
  }

  public static async getUserId(): Promise<string | null> {
    let ret: string | null = null;

    if (PrintessEditor.currentContext) {
      ret = typeof PrintessEditor.currentContext.getUserId === "function" ? PrintessEditor.currentContext.getUserId() : null;

      if (!ret && typeof PrintessEditor.currentContext.getUserIdAsync === "function") {
        ret = await PrintessEditor.currentContext.getUserIdAsync();
      }
    }

    return ret;
  }

  public async getFormFieldValue(formFieldName: string): Promise<void> {
    if (this.usePanelUi()) {
      const editor = this.getPrintessComponent();
      if (editor && (editor as any).editor) {
        const formFieldValue = await (editor as any).editor.api.getFormField(formFieldName);

        if (formFieldValue) {

        }
      }
    } else {
      (document.getElementById("printess") as HTMLIFrameElement)?.contentWindow?.postMessage({ cmd: "getFormField", parameters: [formFieldName] }, "*");
    }
  }

  private static closeAllHtmlDialogs() {
    document.querySelectorAll(":not(.printess-owned) dialog,dialog:not(.printess-owned)").forEach((x: HTMLDialogElement) => {
      if (x && !x.classList.contains("printess-owned") && typeof x.close === "function") {
        try {
          x.close();
        } catch (ex) {
          console.error(ex);
        }
      }
    });
  }

  private async showBcUiVersion(context: IShopContext, callbacks: IEditorCallbacks): Promise<void> {
    const that = this;
    const priceInfo = PrintessEditor.currentContext.getPriceInfo();
    let isSaveToken = PrintessEditor.currentContext.templateNameOrSaveToken && PrintessEditor.currentContext.templateNameOrSaveToken.indexOf("st:") === 0;
    let pageCount: number | null = null;
    let useCustomLoader = false;

    let formFields: IFormField[] | null = null;
    let mergeTemplates: IMergeTemplate[] | null = null;

    PrintessEditor.closeAllHtmlDialogs();


    if (!isSaveToken) {
      formFields = PrintessEditor.applyFormFieldMappings(PrintessEditor.currentContext.getCurrentFormFieldValues(), PrintessEditor.currentContext.getFormFieldMappings());
      mergeTemplates = PrintessEditor.currentContext.getMergeTemplates();

      if (PrintessEditor.currentContext.additionalAttachParams && typeof PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"] !== "undefined") {
        const pageFormField = formFields.filter(x => x.name === PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"]);

        if (pageFormField && pageFormField.length > 0) {
          let intValue = PrintessEditor.extractNumber(pageFormField[0].value);

          if (!isNaN(intValue) && isFinite(intValue)) {
            pageCount = intValue;
          }
        }
      }
    }

    const globalSettings = PrintessEditor.getGlobalShopSettings();

    if (globalSettings && globalSettings.customLoader && typeof globalSettings.customLoader.onShowLoader === "function") {
      try {
        globalSettings.customLoader.onShowLoader();

        useCustomLoader = true;
      } catch (e) {
        console.error(e);
      }
    }

    const startupParams: Record<string, any> = {};
    const loaderUrl = that.getLoaderUrl(this.Settings.editorUrl, this.Settings.editorVersion, startupParams);
    const printessLoader = await import(/* webpackIgnore: true */loaderUrl);

    let printessComponent = that.getPrintessComponent();

    const closeTabListener = (evt): void => {
      if (callbacks && typeof callbacks.onCloseTab === "function") {
        callbacks.onCloseTab(evt);
      }
    }

    if (this.Settings.showAlertOnTabClose === true) {
      window.addEventListener('beforeunload', closeTabListener);
      window.addEventListener('unload', closeTabListener);
    }

    if (printessComponent && (printessComponent as any).editor) {
      printessComponent.style.display = "block";

      await (printessComponent as any).editor.api.loadTemplateAndFormFields(PrintessEditor.currentContext.templateNameOrSaveToken, mergeTemplates, formFields, null, null, true);

      context.setFormFieldValue = (formFieldName: string, formFieldValue: string): void => {
        that.setFormFieldValueOnBcUi((printessComponent as any).editor, formFieldName, formFieldValue);
      };

      if (!isSaveToken && pageCount !== null && pageCount > 0) {
        await (printessComponent as any).editor.api.setBookInsidePages(pageCount);
      }

      setTimeout(async function () {
        if (PrintessEditor.currentContext.hidePricesInEditor !== true) {
          that.calculateCurrentPrices({} as IPriceChangedInfo).then((priceChangedInfo) => {
            (printessComponent as any).editor.ui.refreshPriceDisplay(priceChangedInfo);
          });
        }

        if (that.Settings.autoImportImageUrlsInFormFields === true) {
          try {
            const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(PrintessEditor.currentContext.getCurrentFormFieldValues(), PrintessEditor.currentContext.getFormFieldMappings())));
            await that.uploadImagesToBcUiEditor(images, (printessComponent as any).editor);
          } catch (e) {
            console.error(e);
          }
        }

        if (that.Settings.autoImportUserImages === true) {
          try {
            let userId = await PrintessEditor.getUserId();
            let basketId = await PrintessEditor.getOrGenerateBasketId();

            if (userId || basketId) {
              await that.uploadUserImagesToBcUiEditor((printessComponent as any).editor, basketId, userId);
            }
          } catch (e) {
            console.error(e);
          }
        }

        await callbacks.onLoadAsync(PrintessEditor.currentContext.templateNameOrSaveToken);

        if (globalSettings && globalSettings.customLoader && typeof globalSettings.customLoader.onHideLoader === "function") {
          try {
            globalSettings.customLoader.onHideLoader();
          } catch (e) {
            console.error(e);
          }
        }
      }, 1000);

      (printessComponent as any).editor.ui.show();
    } else {
      let theme = that.Settings.uiSettings ? (that.Settings.uiSettings.theme || "") : "";

      if (typeof theme !== "string" && (theme as any).error) {
        theme = (theme as any).error;

      }

      if (!theme || theme.indexOf("json not allowed") === 0) {
        theme = "DEFAULT";
      }

      const attachParams = {
        suppressLoadingAnimation: useCustomLoader,
        domain: that.Settings.apiDomain,
        mergeTemplates: mergeTemplates,
        formFields: formFields,
        token: that.Settings.shopToken,
        templateName: PrintessEditor.currentContext.templateNameOrSaveToken, // "Premier Test-3",// "test Trigger Dialog",  // "price-tester", // "Premier Test", //  "Children's book", // "Label FF Test", //"test Trigger Dialog",   "test Trigger Dialog", // "Bathrobe Man", //
        //templateVersion: "publish",//"draft"
        translationKey: that.Settings.translationKey || "auto",//"en"
        basketId: await PrintessEditor.getOrGenerateBasketId(),
        shopUserId: "" + (await PrintessEditor.getUserId()),
        // mobileMargin: {left: 20, right: 40, top: 30, bottom: 40},
        // allowZoomAndPan: false,
        snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
        theme: theme,
        skipExchangeStateApplication: true,

        addToBasketCallback: (token, thumbnailUrl) => {
          const addToBasketParams: IAddToBasketParameters = {

          };

          const editor = (that.getPrintessComponent() as any)?.editor;

          if(editor) {
            const pageInfo = editor.api.getAllDocsAndSpreads(true).find(x => x.isBook === true);

            if(pageInfo) {
              if (typeof pageInfo.pageCount !== undefined && pageInfo.pageCount > 0) {
                addToBasketParams.pageCount = pageInfo.pageCount;
                addToBasketParams.additionalPages = Math.abs(pageInfo.pageCount - pageInfo.minPages);
                addToBasketParams.minPages = pageInfo.minPages;
              }
            }
          }



          const addToBasket = (saveToken: string, thumbnailUrl: string): void => {
            if (!saveToken) {
              setTimeout(function () {
                alert("It seems like the system was unable to save your changes. Please try again.");
              }, 0);

              return;
            }

            window.removeEventListener('beforeunload', closeTabListener);
            window.removeEventListener('unload', closeTabListener);

            if (callbacks && typeof callbacks.onAddToBasketAsync === "function") {
              callbacks.onAddToBasketAsync(token, thumbnailUrl, addToBasketParams).then(() => { });
            }
          };

          if (typeof PrintessEditor.currentContext.onAllowAddToBasket === "function") {
            const result = PrintessEditor.currentContext.onAllowAddToBasket(token, thumbnailUrl);

            if (typeof result !== "boolean" || result === true) {
              addToBasket(token, thumbnailUrl);
            }
          } else if (typeof PrintessEditor.currentContext.onAllowAddToBasketAsync === "function") {
            PrintessEditor.currentContext.onAllowAddToBasketAsync(token, thumbnailUrl).then((result: Boolean) => {
              if (typeof result !== "boolean" || result === true) {
                addToBasket(token, thumbnailUrl);
              }
            });
          } else {
            addToBasket(token, thumbnailUrl);
          }
        },
        formFieldChangedCallback: (name: string, value: string, tag: string, label: string, ffLabel: string): void => {
          if (callbacks && typeof callbacks.onFormFieldChangedAsync === "function") {
            callbacks.onFormFieldChangedAsync(name, value, ffLabel, label).then(() => { });
          }
        },
        priceChangeCallback: (priceInfo: IPriceChangedInfo): void => {
          if (callbacks && typeof callbacks.onPriceChangedAsync === "function") {
            callbacks.onPriceChangedAsync(priceInfo).then(() => { });
          }
        },
        backButtonCallback: (saveToken: string): void => {
          window.removeEventListener('beforeunload', closeTabListener);
          window.removeEventListener('unload', closeTabListener);

          that.hideBcUiVersion(true);
        },
        saveTemplateCallback: (saveToken: string, type: "save" | "close", thumbnailUrl: string): void => {
          if (typeof callbacks.onSaveAsync === "function") {
            callbacks.onSaveAsync(saveToken, thumbnailUrl);
          }

          if (type && type === "close") {
            that.hideBcUiVersion(true);
          }
        },
        loadingDoneCallback: (): void => {
          if (globalSettings && globalSettings.customLoader && typeof globalSettings.customLoader.onHideLoader === "function") {
            try {
              globalSettings.customLoader.onHideLoader();
            } catch (e) {
              console.error(e);
            }
          }
        }
      };

      if(context.additionalAttachParams) {
        for(const property in context.additionalAttachParams) {
          if(context.additionalAttachParams.hasOwnProperty(property)) {
            attachParams[property] = context.additionalAttachParams[property];
          }
        }
      }

      if (typeof PrintessEditor.currentContext.showSplitterGridSizeButton !== "undefined" && PrintessEditor.currentContext.showSplitterGridSizeButton !== null) {
        attachParams["showSplitterGridSizeButton"] = PrintessEditor.currentContext.showSplitterGridSizeButton === true || PrintessEditor.currentContext.showSplitterGridSizeButton === "true";
      }

      if (!isSaveToken && pageCount !== null && pageCount >= 1) {
        attachParams["bookInsidePages"] = pageCount;
      }

      const globalSettings = PrintessEditor.getGlobalShopSettings();

      if (typeof globalSettings.getFormFieldProperties === "function") {
        (attachParams as any).formFieldProperties = globalSettings.getFormFieldProperties();
      }



      const printess = await printessLoader.load(attachParams);
      printessComponent = that.getPrintessComponent();

      (printessComponent as any).editor = printess;

      context.setFormFieldValue = (formFieldName: string, formFieldValue: string): void => {
        that.setFormFieldValueOnBcUi((printessComponent as any).editor, formFieldName, formFieldValue);
      };

      setTimeout(async function () {
        const printessComponent = that.getPrintessComponent();

        if (!printessComponent) {
          return;
        }

        if (PrintessEditor.currentContext.hidePricesInEditor !== true) {
          const priceChangedInfo = await that.calculateCurrentPrices({} as IPriceChangedInfo);
          (printessComponent as any).editor.ui.refreshPriceDisplay(priceChangedInfo);
        }

        if (that.Settings.autoImportImageUrlsInFormFields === true) {
          try {
            const images = await that.downloadImages(that.getImagesInFormFields(PrintessEditor.applyFormFieldMappings(PrintessEditor.currentContext.getCurrentFormFieldValues(), PrintessEditor.currentContext.getFormFieldMappings())));
            await that.uploadImagesToBcUiEditor(images, (printessComponent as any).editor);
          } catch (e) {
            console.error(e);
          }
        }

        if (that.Settings.autoImportUserImages === true) {
          try {
            let userId = await PrintessEditor.getUserId();
            let basketId = await PrintessEditor.getOrGenerateBasketId();

            if (userId || basketId) {
              await that.uploadUserImagesToBcUiEditor((printessComponent as any).editor, basketId, userId);
            }
          } catch (e) {
            console.error(e);
          }
        }

        await callbacks.onLoadAsync(attachParams.templateName);
      }, 1000);
    }
  }

  public static extractNumber(inputStr: string): number {
    let c = "0123456789";

    function check(x) {
      return c.includes(x) ? true : false;
    }

    return parseInt([...inputStr].reduce((x, y) => (check(y) ? x + y : x), ""));
  }

  public async show(shopContext: IShopContext): Promise<boolean> {
    if (PrintessEditor.visible) {
      return false;
    }

    PrintessEditor.visible = true;

    PrintessEditor.currentContext = shopContext;

    const that = this;
    this.lastSaveDate = new Date();
    let isSaveToken = shopContext && shopContext.templateNameOrSaveToken && shopContext.templateNameOrSaveToken.indexOf("st:") === 0;

    PrintessEditor.closeAllHtmlDialogs();

    const callbacks: IEditorCallbacks = {
      onBack: (): void => {
        that.hide(true);
      },
      onAddToBasketAsync: async (saveToken: string, thumbnailUrl: string, params?: IAddToBasketParameters): Promise<void> => {
        if (!saveToken) {
          setTimeout(function () {
            alert("It seems like the system was unable to save your changes. Please try again.");
          }, 0);

          return;
        }

        let result: any = null;

        if (typeof PrintessEditor.currentContext.onAddToBasketAsync === "function") {
          result = await PrintessEditor.currentContext.onAddToBasketAsync(saveToken, thumbnailUrl, params) as any;
        } else {
          result = PrintessEditor.currentContext.onAddToBasket(saveToken, thumbnailUrl, params) as any;
        }

        if (result && (result as any).waitUntilClosingMS) {
          setTimeout(function () {
            if (typeof (result as any).executeBeforeClosing === "function") {
              (result as any).executeBeforeClosing();
            }

            that.hide(false);
          }, (result as any).waitUntilClosingMS);
        } else {
          if (result && typeof (result as any).executeBeforeClosing === "function") {
            (result as any).executeBeforeClosing();
          }

          that.hide(false);
        }
      },
      onFormFieldChangedAsync: async (formFieldName: string, formFieldValue: string, formFieldLabel: string, formFieldValueLabel): Promise<void> => {
        const formField = that.reverseFormFieldMapping(formFieldName, formFieldValue, PrintessEditor.currentContext.getFormFieldMappings());

        if (typeof PrintessEditor.currentContext.onFormFieldChangedAsync === "function") {
          await PrintessEditor.currentContext.onFormFieldChanged(formField.name, formField.value, formFieldLabel, formFieldValueLabel);
        } else if (typeof PrintessEditor.currentContext.onFormFieldChanged === "function") {
          PrintessEditor.currentContext.onFormFieldChanged(formField.name, formField.value, formFieldLabel, formFieldValueLabel);
        }
      },
      onPriceChangedAsync: async (priceInfo: IPriceChangedInfo): Promise<void> => {
        await that.onPriceChanged(priceInfo);
      },
      onGetFormField: (result: { value: null | string | number | Array<Record<string, any>>, list?: Array<{ key: string, label?: string, description?: string, imageId?: string }> } | undefined): void => {
        if (typeof PrintessEditor.currentContext.onGetFormField === "function") {
          PrintessEditor.currentContext.onGetFormField(result);
        }
      },
      onSaveAsync: async (saveToken: string, thumbnailUrl: string): Promise<void> => {
        that.lastSaveDate = new Date();

        if (typeof PrintessEditor.currentContext.onSaveAsync === "function") {
          await PrintessEditor.currentContext.onSaveAsync(saveToken, thumbnailUrl);
        } else if (typeof PrintessEditor.currentContext.onSave === "function") {
          PrintessEditor.currentContext.onSave(saveToken, thumbnailUrl);
        }
      },
      onLoadAsync: async (currentTemplateNameOrSaveToken: string): Promise<void> => {
        if (typeof PrintessEditor.currentContext.onLoadAsync === "function") {
          await PrintessEditor.currentContext.onLoadAsync(currentTemplateNameOrSaveToken);
        } else if (typeof PrintessEditor.currentContext.onLoad === "function") {
          PrintessEditor.currentContext.onLoad(currentTemplateNameOrSaveToken);
        }
      },
      onCloseTab: (evt) => {
        evt.preventDefault();
        evt.returnValue = '';
      }
    };


    PrintessEditor.currentContext.save = function () {
      that.save(callbacks);
    }

    if (this.usePanelUi()) {
      await that.showBcUiVersion(shopContext, callbacks);
    } else {
      const priceInfo = PrintessEditor.currentContext.getPriceInfo();
      let pageCount: number | null = null;

      let formFields: IFormField[] | null = null;
      let mergeTemplates: IMergeTemplate[] | null = null;

      if (!isSaveToken) {
        formFields = PrintessEditor.applyFormFieldMappings(PrintessEditor.currentContext.getCurrentFormFieldValues(), PrintessEditor.currentContext.getFormFieldMappings());
        mergeTemplates = PrintessEditor.currentContext.getMergeTemplates();

        if (PrintessEditor.currentContext.additionalAttachParams && typeof PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"] !== "undefined") {
          const pageFormField = formFields.filter(x => x.name === PrintessEditor.currentContext.additionalAttachParams["pageCountFormField"]);

          if (pageFormField && pageFormField.length > 0) {
            let intValue = PrintessEditor.extractNumber(pageFormField[0].value);

            if (!isNaN(intValue) && isFinite(intValue)) {
              pageCount = intValue;
            }
          }
        }
      }

      const iFrame = await this.initializeIFrame(callbacks, this.Settings);

      if (iFrame.getAttribute('data-attached') === "false") {
        try {
          const attachParams = {
            domain: that.Settings.apiDomain,
            token: this.Settings.shopToken || "",
            templateName: PrintessEditor.currentContext.templateNameOrSaveToken,
            showBuyerSide: true,
            templateUserId: '',
            basketId: await PrintessEditor.getOrGenerateBasketId(),
            shopUserId: await PrintessEditor.getUserId(),
            formFields: formFields,
            snippetPriceCategoryLabels: priceInfo && priceInfo.snippetPrices ? priceInfo.snippetPrices : null,
            mergeTemplates: mergeTemplates,
            skipExchangeStateApplication: true,
            translationKey: that.Settings.translationKey || "auto",//"en"
          };

          if (typeof PrintessEditor.currentContext.showSplitterGridSizeButton !== "undefined" && PrintessEditor.currentContext.showSplitterGridSizeButton !== null) {
            attachParams["showSplitterGridSizeButton"] = PrintessEditor.currentContext.showSplitterGridSizeButton === true || PrintessEditor.currentContext.showSplitterGridSizeButton === "true";
          }

          const globalSettings = PrintessEditor.getGlobalShopSettings();

          if (typeof globalSettings.getFormFieldProperties === "function") {
            (attachParams as any).formFieldProperties = globalSettings.getFormFieldProperties();
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

          if (PrintessEditor.currentContext.additionalAttachParams) {
            for (const prop in PrintessEditor.currentContext.additionalAttachParams) {
              if (PrintessEditor.currentContext.additionalAttachParams.hasOwnProperty(prop)) {
                attachParams[prop] = PrintessEditor.currentContext.additionalAttachParams[prop];
              }
            }
          }

          if (!isSaveToken && pageCount !== null && pageCount >= 1) {
            attachParams["bookInsidePages"] = pageCount;
          }

          iFrame.contentWindow!.postMessage({
            cmd: 'attach',
            properties: attachParams
          }, '*');

          iFrame.setAttribute('data-attached', "true");
          setTimeout(function () { iFrame.contentWindow.focus(); }, 0);
        } catch (e) {
          console.error(e);
        }
      } else {
        let undef;
        const loadParams = {
          templateNameOrToken: PrintessEditor.currentContext.templateNameOrSaveToken,
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

        if (PrintessEditor.currentContext && PrintessEditor.currentContext.additionalAttachParams) {
          if (PrintessEditor.currentContext.additionalAttachParams.formFieldProperties) {
            loadParams.formFieldProperties = PrintessEditor.currentContext.additionalAttachParams.formFieldProperties;
          }

          if (typeof PrintessEditor.currentContext.additionalAttachParams.clearExchangeCaches !== "undefined") {
            loadParams.clearExchangeCaches = PrintessEditor.currentContext.additionalAttachParams.clearExchangeCaches === false ? false : true;
          }
        }

        iFrame.contentWindow!.postMessage({
          cmd: "loadTemplateAndFormFields",
          parameters: [loadParams.templateNameOrToken, loadParams.mergeTemplates, loadParams.formFields, loadParams.snippetPriceCategoryLabels, loadParams.formFieldProperties, loadParams.clearExchangeCaches]
        }, '*');

        shopContext.setFormFieldValue = (formFieldName: string, formFieldValue: string) => {
          that.setFormFieldValueOnClassicEditor(iFrame, formFieldName, formFieldValue);
        };

        setTimeout(function () { iFrame.contentWindow.focus(); }, 0);

        if (!isSaveToken && pageCount !== null && pageCount > 0) {
          setTimeout(function () {
            iFrame.contentWindow!.postMessage({
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

    const iframeWrapper = document.getElementById("printess-container") as HTMLDivElement;

    if (iframeWrapper) {
      // iframeWrapper.style.display = "block !important";
      iframeWrapper.style.setProperty('display', 'block', 'important');
    }

    if (!(this.show as any).saveInterval) {
      (this.show as any).saveInterval = setInterval(function () {
        if (PrintessEditor.visible && PrintessEditor.currentContext.currentSaveTimerInMinutes !== null && PrintessEditor.currentContext.currentSaveTimerInMinutes > 0 && typeof PrintessEditor.currentContext.onSaveTimer === "function") {
          const timeDifferenceMs = ((new Date()).getTime() - that.lastSaveDate.getTime());

          if (timeDifferenceMs > (PrintessEditor.currentContext.currentSaveTimerInMinutes * 60000)) {
            that.lastSaveDate = new Date();

            PrintessEditor.currentContext.onSaveTimer();
          }
        }
      }, 30000);
    }

    return true;
  }

  private hide(closeButtonClicked: boolean) {
    if (!PrintessEditor.visible) {
      return;
    }

    PrintessEditor.visible = false;

    if (this.usePanelUi()) {
      const editor = this.getPrintessComponent();

      if (editor && (editor as any).editor) {
        (editor as any).editor.ui.hide();
      }
    } else {
      const iframeWrapper = document.getElementById("printess-container") as HTMLDivElement;

      if (iframeWrapper) {
        iframeWrapper.style.display = "none";
      }
    }

    document.body.classList.remove('hideAll');

    var root = document.getElementsByTagName('html');

    if (root && root.length > 0) {
      root[0].classList.remove('printess-editor-open');
    }

    if (typeof PrintessEditor.currentContext.editorClosed === "function") {
      PrintessEditor.currentContext.editorClosed(closeButtonClicked === true);
    }
  }

  public static getGlobalShopSettings(): IGlobalShopSettings {
    return (window && window["printessGlobalConfig"] ? window["printessGlobalConfig"] : {}) as IGlobalShopSettings;
  }

  public static getGlobalFormFields(): Record<string, string> {
    const settings = PrintessEditor.getGlobalShopSettings();
    let formFields: Record<string, string> | null;

    if (typeof settings.formFields != undefined && settings.formFields) {
      if (typeof settings.formFields == "function") {
        try {
          formFields = settings.formFields();
        } catch (e) {
          console.error(e);
        }
      }
      else {
        formFields = settings.formFields;
      }
    }

    return formFields || {};
  }

  public static ensureScriptExecution(scriptId: string, methodName: string | null = null, params: any[] | null = null) {
    const scriptTag = document.getElementById(scriptId) as HTMLScriptElement;

    if (scriptTag && !scriptTag.getAttribute("data-replaced")) {
      const newTag = document.createElement('script') as HTMLScriptElement;
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
}

export function initPrintessEditor(shopToken: string | Record<string, any> | IEditorSettings, editorUrl: string | undefined, editorVersion: string | undefined,
  startupLogoUrl: string | undefined, showStartupAnimation: boolean | undefined, theme: string | undefined, startupBackgroundColor: string | undefined = ""): PrintessEditor {

  let editorSettings: IEditorSettings;

  if (shopToken && typeof shopToken !== "string") {
    editorSettings = {
      apiDomain: (shopToken as any)["apiDomain"] ? (shopToken as any)["apiDomain"] : null,
      shopToken: (shopToken as any)["shopToken"] ? (shopToken as any)["shopToken"] : "",
      editorUrl: (shopToken as any)["editorUrl"] ? (shopToken as any)["editorUrl"] : "",
      editorVersion: (shopToken as any)["editorVersion"] ? (shopToken as any)["editorVersion"] : "",
      attachParams: (shopToken as any)["attachParams"] ? (shopToken as any)["attachParams"] : "",
      showAlertOnTabClose: typeof (shopToken as any)["showTabClosingAlert"] !== "undefined" && (shopToken as any)["showTabClosingAlert"] !== null ? (shopToken as any)["showTabClosingAlert"] === true : false,
      translationKey: (shopToken as any)["translationKey"] ? (shopToken as any)["translationKey"] : "",
      uiSettings: {
        showStartupAnimation: typeof (shopToken as any)["showStartupAnimation"] !== "undefined" && (shopToken as any)["showStartupAnimation"] !== null ? (shopToken as any)["showStartupAnimation"] === true : true,
        startupBackgroundColor: (shopToken as any)["startupBackgroundColor"] || "#ffffff",
        theme: (shopToken as any)["theme"] || "",
        startupLogoUrl: (shopToken as any)["startupLogoUrl"] || "",
        uiVersion: (shopToken as any)["uiVersion"] || ""
      },
      ...shopToken,
      autoImportUserImages: (shopToken as any)["autoImportUserImages"] ? (shopToken as any)["autoImportUserImages"] === true : false
    };
  } else {
    editorSettings = {
      apiDomain: null,
      shopToken: shopToken as string || "",
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