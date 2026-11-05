import { IShopContext, IMergeTemplate, IStickerInfo, ISnippetPrice, IFormFieldMapping, IEditorSettings, IGlobalShopSettings, ICallbackParams, PrintessEditor } from "./printessEditor";
import { PrintessSharedTools } from "./printessSharedTools";

export interface PriceFormatOptions {
  decimalSeperator: string;
  thousandsSeperator: string;
  decimals: number;
  priceFormat: string;
  currencySymbol: string;
  currencySymbolOnLeftSide: boolean;
}

export interface IPrintessSettings extends IEditorSettings {
  idsToHide: string[];
  classesToHide: string[];
  editorMode?: string | null | undefined;
  addToCartAfterCustomization?: boolean | null | undefined;
  userIsLoggedIn: boolean;
  accountPageUrl: string;
  nonce: string;
  urlToken: string;
  userMessages: Record<string, string>;
  askForNameOnResave: boolean;
  customizeButtonClasses: string;
  showPricesInEditor: boolean;
  showProductName?: boolean | null | undefined;
  priceFormatOptions?: PriceFormatOptions | null | undefined;
  cartUrl?: string;
  designNowButtonId?: string;
  hideCustomDesignNowButtonClasses?: boolean;
  writeTrackingEvents?: boolean;
}

export interface IWooCommerceAttribute {
  key: string;
  name: string;
  values: string[];
  valueKeys: string[];
  usedForVariants: boolean;
};

export interface IWooCommerceProductVariant {
  id: number;
  parentId: number;
  attributes: Record<string, string>;
  templateName: string;
  templateIsMergeTemplate?: boolean;
  regularPrice: string;
  salePrice: string;
  price: string;
  available: boolean;
}

export interface IWooCommerceProduct {
  id: number;
  name: string;
  templateName: string;
  mergeTemplates: string[];
  attributes: Record<string, IWooCommerceAttribute>;
  variants: IWooCommerceProductVariant[];
  price: number;
  redirectUrl?: string | null | undefined;
  ajaxEnabled?: boolean | null | undefined;
}

export interface IShowEditorSettings {
  templateNameOrSaveToken: string;
  mergeTemplates: IMergeTemplate[] | string | null | undefined;
  product: IWooCommerceProduct;
  basketItemId: number | undefined;
  basketItemOptions: Record<string, string> | null | undefined;
  productDefinitionId: string | null | undefined;
  outputFormat: string | null | undefined;
  outputDpi: string | null | undefined;
  quantity: number | null | undefined;
  optionValueMappings: string | null | undefined;
  productType: string | null | undefined;
  legalText: string | null | undefined;
  legalUrl: string | null | undefined;
  basketId: string | null | undefined;
  userId: string | null | undefined;
  additionalAttachParams?: Record<string, any> | null | undefined;
  enforceDisplayName: string;
  showSaveWarningAfter?: number;
  useRecordCountAsQuantity?: boolean;
}

export interface IProductPageSettings {
  product: IWooCommerceProduct;
  saveToken: string;
  customizeButtonClass: string;
  designNowButtonLabel: string;
  formSelector: string;
  mergeTemplates: string[];
  productAttributes: Record<string, string>;
  formFields: Record<string, string>;
  fromFieldMappings: Record<string, { formField: string, values: Record<string, string> }>;
  idsToHide: string[];
  classesToHide: string[];
  userIsLoggedIn: boolean;
}

export interface IWooCommerceShopContext extends IShopContext {
  designName?: string;
  designId?: number;
  cameFromSave?: boolean;
  lastSaveSaveToken?: string;
};

export type TrackingEventType = "editor_opened_new_design" | "editor_opened_save_token" | "editor_closed_without_add_to_cart" | "design_saved" | "add_to_cart_clicked";

export interface TrackingEventParameters {
  item_id: number;
  item_name: string;
  sku?: string;
  variant: string;
  designId: string;
  price: number;
  quantity: number;
  currency: string;
};

interface ISavedDesignStorageItem {
  saveToken: string
  thumbnailUrl: string
  productId: number
  displayName: string
  options: string
  designId?: number
  storageId: string
}

interface IEventAgnosticDialog extends HTMLElement {
  addEventHandler?: (event: string, callback: (evt: Event) => void) => void | null;
  removeAllEventHandlers?: () => void | null;
  eventHandlers?: Record<string, ((evt: Event) => void)[]> | null;
}

type TrackingDataLayer = {
  event: string;
  ecommerce: {
    items: TrackingEventParameters[];
  };
}

declare var dataLayer: TrackingDataLayer[];

const printessFocusListeners: AbortController[] = [];

let printessSaveReminderActive: boolean = false;

function printessTrapFocus(root: Element): void {
  const keyboardFocusableElements = root?.querySelectorAll(
    'a[href], button, input, textarea, select, details, [tabindex]'
  );

  if (keyboardFocusableElements && keyboardFocusableElements.length > 0) {
    const lastFocusableElement = keyboardFocusableElements[keyboardFocusableElements.length - 1] as HTMLElement;
    const firstFocusableElement = keyboardFocusableElements[0] as HTMLElement;

    const tabBackToLast = (keyEvent: KeyboardEvent) => {
      if (keyEvent.key === "Tab" && keyEvent.shiftKey && lastFocusableElement) {
        keyEvent.preventDefault();
        lastFocusableElement.focus();
      }
    }

    const tabToFirst = (keyEvent: KeyboardEvent) => {
      if (keyEvent.key === "Tab" && !keyEvent.shiftKey && firstFocusableElement) {
        keyEvent.preventDefault();
        firstFocusableElement.focus();
      }
    }

    printessFocusListeners.push(new AbortController);

    firstFocusableElement?.addEventListener("keydown", tabBackToLast, { signal: printessFocusListeners[printessFocusListeners.length - 1].signal });
    lastFocusableElement?.addEventListener("keydown", tabToFirst, { signal: printessFocusListeners[printessFocusListeners.length - 1].signal });

    firstFocusableElement?.focus();
  }
}

const printessFreeFocus = function (): void {
  if (printessFocusListeners.length > 0) {
    printessFocusListeners[printessFocusListeners.length - 1].abort();
    printessFocusListeners.pop();
  }
}

const addEventHelpersToDialog = (dialog: IEventAgnosticDialog): void => {
  if (dialog) {
    dialog.eventHandlers = dialog.eventHandlers || {};

    if (typeof dialog.removeAllEventHandlers !== "function") {
      dialog.removeAllEventHandlers = function (): void {
        for (const eventHandler in dialog.eventHandlers) {
          if (dialog.eventHandlers.hasOwnProperty(eventHandler) && dialog.eventHandlers[eventHandler]) {
            dialog.eventHandlers[eventHandler].forEach((x) => {
              dialog.removeEventListener(eventHandler, x);
            });
          }
        }

        dialog.eventHandlers = null;
      };
    }

    if (typeof dialog.addEventHandler !== "function") {
      dialog.addEventHandler = function (event: string, callback: (evt: Event) => void) {
        dialog.eventHandlers[event] = dialog.eventHandlers[event] || [];
        dialog.eventHandlers[event].push(callback);

        dialog.addEventListener(event, callback);
      };
    }
  }
};

const initPrintessWCEditor = function (printessSettings: IPrintessSettings) {
  const CART_FORM_SELECTOR = "form.cart";
  const designNowButtonId = printessSettings.designNowButtonId || "printess-customize-button";

  let itemUsage: Record<string, number> | null = null;

  if (typeof (<any>window)["printessWooEditor"] !== "undefined") {
    return (<any>window)["printessWooEditor"];
  }

  const getGlobalConfig = (): IGlobalShopSettings => {
    return (window && (<any>window)["printessGlobalConfig"] ? (<any>window)["printessGlobalConfig"] : {}) as IGlobalShopSettings;
  };

  const getCurrentProductOptionValues = function (product: IWooCommerceProduct, $remove_attribute_prefix = true): Record<string, string> {
    const ret: Record<string, string> = {};
    const blackList: Record<string, boolean> = { "add-to-cart": true, "product_id": true, "quantity": true, "variation_id": true };

    const globalConfig = getGlobalConfig();

    if (globalConfig && globalConfig.formFields) {
      for (const property in globalConfig.formFields) {
        if (globalConfig.formFields.hasOwnProperty(property)) {
          ret[property] = globalConfig.formFields[property];
        }
      }
    }


    const form = document.querySelector(CART_FORM_SELECTOR) as HTMLFormElement;
    const parseName = function (name: string): string {
      if ($remove_attribute_prefix && name.indexOf("attribute_") === 0) {
        name = name.substring(10, name.length);
      }

      if (product.attributes && product.attributes[name]) {
        name = product.attributes[name].name;
      }

      return name;
    }

    if (form) {
      const formData = new FormData(form);

      for (const pair of formData.entries()) {
        const name = parseName(pair[0]);

        if (name.length > 0 && name[0] !== "_" && !blackList[name] && name.indexOf("printess-") !== 0) {
          ret[name] = pair[1].toString();
        }
      }

      //Get all checkboxes that are checked
      const checkBoxes = form.querySelectorAll('input[type="checkbox"]') as any as HTMLInputElement[];

      if (checkBoxes && checkBoxes.length > 0) {
        for (let i: number = 0; i < checkBoxes.length; ++i) {
          if (typeof (checkBoxes[i] as HTMLInputElement).checked !== "undefined" && checkBoxes[i].checked === true) {
            const name = parseName(checkBoxes[i].name);

            if (name.length > 0 && name[0] !== "_") {
              ret[name] = "true";
            }
          }
        }
      }
    }

    return ret;
  };

  const removeAttributePrefix = (values: Record<string, string>): Record<string, string> => {
    const ret: Record<string,string> = {};

    for(const name in values) {
      if(values.hasOwnProperty(name)) {
        let parsed = name;
      
        if (name.indexOf("attribute_") === 0) {
          parsed = name.substring(10, name.length);
        }

        ret[parsed] = values[name];
      }
    }

    return ret;
  }

  const getAttributeLookup = (product: IWooCommerceProduct, onlyVariantSpecific: boolean): Record<string, IWooCommerceAttribute> => {
    const ret: Record<string, IWooCommerceAttribute> = {};

    if (product && product.attributes) {
      for (const attributeKey in product.attributes) {
        if (product.attributes.hasOwnProperty(attributeKey)) {
          if (!onlyVariantSpecific || product.attributes[attributeKey].usedForVariants === true) {
            ret[product.attributes[attributeKey].name] = product.attributes[attributeKey];
          }
        }
      }
    }


    return ret;
  };

  const readQuantityFromProductPage = (): number => {
    const form = document.querySelector(CART_FORM_SELECTOR) as HTMLFormElement;

    if (form) {
      const formData = new FormData(form);

      if (formData.has("quantity")) {
        const qty = parseInt(formData.get("quantity").toString());

        if (!isNaN(qty) && isFinite(qty) && qty > 0) {
          return qty;
        }
      }
    }

    return 1;
  }

  const postTrackingEvent = (evt: TrackingEventType, context: IWooCommerceShopContext, product: IWooCommerceProduct) => {
    if (!printessSettings.writeTrackingEvents) {
      return;
    }

    let variantOptions: string = "";
    const currentProductOptions = context.getCurrentFormFieldValues();

    PrintessSharedTools.forEach(currentProductOptions, (value: string, index: number, arr: Record<string, string>, key: string) => {
      if (variantOptions) {
        variantOptions += " | ";
      }

      variantOptions += key + ": " + value;
    });

    context.getPriceForFormFieldsAsync(currentProductOptions).then((price: number) => {
      const params: TrackingEventParameters = {
        item_id: product.id,
        item_name: product.name,
        variant: variantOptions,
        designId: context.templateNameOrSaveToken,
        price: price,
        quantity: readQuantityFromProductPage(),
        currency: printessSettings.priceFormatOptions?.currencySymbol || ""
      };

      if (typeof dataLayer !== "undefined" && typeof dataLayer.push === "function") {
        dataLayer.push({
          event: evt,
          ecommerce: {
            items: [params]
          }
        });
      } else {
        window.dispatchEvent(new CustomEvent("printess:" + evt, {
          detail: params
        }));
      }
    });
  }

  const getCurrentVariant = function (productOptionValues: Record<string, string>, product: IWooCommerceProduct, returnDefaultVariant: boolean = true): IWooCommerceProductVariant | null {
    let ret: IWooCommerceProductVariant | null = returnDefaultVariant ?  (product.variants ? product.variants[0] : null) : null;
    const attributeLookup = getAttributeLookup(product, true);
    const variantSpecificValues: { key: string, value: string }[] = [];
    const attributeValuesUsedInVariants: Record<string, Record<string, boolean>> = {};

    if (product.variants) {
      product.variants.forEach((variant) => {
        if (variant.attributes) {
          for (const key in variant.attributes) {
            if (variant.attributes.hasOwnProperty(key)) {
              if (!attributeValuesUsedInVariants[key]) {
                attributeValuesUsedInVariants[key] = {};
              }

              attributeValuesUsedInVariants[key][variant.attributes[key]] = true;
            }
          }
        };
      });
    }

    const mapValue = (name: string, value: string): string => {
      let mappedValue = value;
      let attribute: IWooCommerceAttribute | null = null;

      if (attributeLookup[name]) {
        attribute = attributeLookup[name];
      }

      if (!attribute) {
        for (const key in attributeLookup) {
          if (attributeLookup.hasOwnProperty(key)) {
            if (attributeLookup[key].name === key || attributeLookup[key].key === key) {
              attribute = attributeLookup[key];
              break;
            }
          }
        }

        if (attribute && attribute.valueKeys && attribute.valueKeys.length > 0) {
          for (let i = 0; i < attribute.values.length; ++i) {
            if (attribute.values[i] === value && attribute.valueKeys.length > i) {
              mappedValue = attribute.valueKeys[i];
              break;
            }
          }
        }
      }

      return mappedValue;
    };

    for (const name in productOptionValues) {
      const value = mapValue(name, productOptionValues[name]);
      if (productOptionValues.hasOwnProperty(name) && attributeLookup[name] && attributeLookup[name].usedForVariants && attributeValuesUsedInVariants[attributeLookup[name].key] && attributeValuesUsedInVariants[attributeLookup[name].key][value]) {
        if (!attributeLookup[name].valueKeys || attributeLookup[name].valueKeys.includes(value)) {
          variantSpecificValues.push({ key: attributeLookup[name].key, value: value });
        }
      }
    }

    if (product.variants) {
      let variants: IWooCommerceProductVariant[] = product.variants;

      variantSpecificValues.forEach((vSv) => {
        variants = variants.filter((variant) => {
          return variant.attributes[vSv.key] === vSv.value;
        });
      });

      if (variants.length > 0) {
        ret = variants[0];
      }
    }

    return ret;
  };

  const getInvalidVariantOption = function (productOptionValues: Record<string, string>, product: IWooCommerceProduct): string | null {
    let ret: string | null = null;
    const attributeLookup = getAttributeLookup(product, true);
    const attributeValuesUsedInVariants: Record<string, Record<string, boolean>> = {};

    if (product.variants) {
      product.variants.forEach((variant) => {
        if (variant.attributes) {
          for (const key in variant.attributes) {
            if (variant.attributes.hasOwnProperty(key)) {
              if (!attributeValuesUsedInVariants[key]) {
                attributeValuesUsedInVariants[key] = {};
              }

              attributeValuesUsedInVariants[key][variant.attributes[key]] = true;
            }
          }
        };
      });
    }

    for(const optionName in productOptionValues) {
      if(productOptionValues.hasOwnProperty(optionName)) {
        for(const attributeName in attributeLookup) {
          if(attributeLookup.hasOwnProperty(attributeName)) {
            if(optionName === attributeName || optionName === attributeLookup[attributeName].key) {
              // Custom (non-taxonomy) product attributes have no valueKeys; they cannot be checked
              // here, as in getCurrentVariant(). Without this, saving crashed on such products.
              if(attributeLookup[attributeName].valueKeys && !attributeLookup[attributeName].valueKeys.includes(productOptionValues[optionName])) {
                return optionName;
              }
            }
          }
        }
      }
    }

    return null;
  }

  const updatePrintessValues = function (saveToken: string, thumbnailUrl: string, designId: string, designName): void {
    const updateInput = function (id: string, value: string) {
      const input = document.getElementById(id) as HTMLInputElement;

      if (input) {
        input.value = value;
      }
    }

    updateInput("printess-save-token", saveToken);

    if (thumbnailUrl) {
      updateInput("printess-thumbnail-url", thumbnailUrl);
    }

    if (designId) {
      updateInput("printess-design-id", designId);
    }

    if (designName) {
      updateInput("printess-design-name", designName);
    }
  };

  const hideSaveDialog = (): void => {
    const dialog = document.getElementById("printess_overlay_background");

    if (dialog) {
      dialog.classList.remove("visible");
      printessFreeFocus();
    }
  };

  const showSaveDialog = (designName: string, saveCallback: ((designName: string) => void), cancelCallback: (() => void)): void => {
    const loggedInBlock = document.getElementById("printess_show_if_not_logged_in");
    const dialog = <IEventAgnosticDialog>document.getElementById("printess_overlay_background");
    let removeEventHandlers = () => { };

    addEventHelpersToDialog(dialog);

    const cancelMouse = (e) => {
      if (!e.srcElement || e.srcElement.nodeName.toLowerCase() !== "input" && e.srcElement.closest("div.printess_overlay_background") == null) {
        e.preventDefault();
        e.stopPropagation();
      }
    };

    const internalSaveCallback = () => {
      hideSaveDialog();
      removeEventHandlers();

      if (typeof saveCallback === "function") {
        let dName = "";

        const designNameEdit = document.getElementById("printess_designnameedit") as HTMLInputElement;

        if (designNameEdit) {
          dName = designNameEdit.value;
        }

        saveCallback(dName);
      }
    };

    const internalCancelCallback = () => {
      hideSaveDialog();
      removeEventHandlers();

      if (typeof cancelCallback === "function") {
        cancelCallback();
      }
    };

    const keyUpHandler = (e) => {
      if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        e.stopPropagation();
        internalSaveCallback();

      } else if (e.key === 'Escape' || e.keyCode === 27) {
        e.preventDefault();
        e.stopPropagation();
      }
    }

    const keyDownHandler = (e) => {
      if (e.key === 'Enter' || e.keyCode === 13) {
        // if a button is active, Enter should activate the button, not do anything else
        if (!(document.activeElement instanceof HTMLButtonElement)) {
          e.preventDefault();
          e.stopPropagation();
          internalSaveCallback();
        }

      } else if (e.key === 'Escape' || e.keyCode === 27) {
        e.preventDefault();
        e.stopPropagation();

        internalCancelCallback();
      }
    }

    removeEventHandlers = () => {
      const saveButton = document.getElementById("printess_save_design_button");

      if (saveButton) {
        saveButton.removeEventListener("click", internalSaveCallback);
      }

      const cancelButton = document.getElementById("printess_cancel_button");

      if (cancelButton) {
        cancelButton.removeEventListener("click", internalCancelCallback);
      }

      if (dialog) {
        if (typeof dialog.removeAllEventHandlers === "function") {
          dialog.removeAllEventHandlers();
        }
      }
    };

    if (loggedInBlock) {
      if (printessSettings.userIsLoggedIn) {
        loggedInBlock.classList.add("logged_in");
      } else {
        loggedInBlock.classList.remove("logged_in");
      }
    }

    let desingNameEditDiv = document.getElementById("printess_show_if_no_design_name");
    let desingNameEditDiv2 = document.getElementById("printess_show_if_design_name");

    if (desingNameEditDiv && desingNameEditDiv2) {
      if (!designName) {
        desingNameEditDiv.classList.add("visible");
        desingNameEditDiv2.classList.remove("visible");
      } else {
        desingNameEditDiv.classList.remove("visible");
        desingNameEditDiv2.classList.add("visible");
      }
    }

    const designNameEdit = document.getElementById("printess_designnameedit") as HTMLInputElement;

    if (designNameEdit) {
      designNameEdit.value = designName || "";
    }

    if (dialog) {
      dialog.addEventHandler("mousedown", cancelMouse);
      dialog.addEventHandler("mouseup", cancelMouse);
      dialog.addEventHandler("mousemove", cancelMouse);
      dialog.addEventHandler("keydown", keyDownHandler);
      dialog.addEventHandler("keyup", keyUpHandler);

      if (!dialog.getAttribute("data-initialized")) {
        document.body.appendChild(dialog);
        dialog.setAttribute("data-initialized", "true");
      }

      dialog.classList.add("visible");
    }

    const saveButton = document.getElementById("printess_save_design_button");

    if (saveButton) {
      saveButton.addEventListener("click", internalSaveCallback);
    }

    const cancelButton = document.getElementById("printess_cancel_button") as HTMLButtonElement;

    if (cancelButton) {
      cancelButton.type = "button";
      cancelButton.addEventListener("click", internalCancelCallback);
    }

    printessTrapFocus(dialog);
  };

  const postMessage = (cmd: string, properties: Record<string, any> | null | undefined): void => {
    const iFrame: HTMLIFrameElement = document.getElementById("printess") as HTMLIFrameElement;

    if (iFrame) {
      setTimeout(function () {
        iFrame.contentWindow.postMessage({
          cmd: cmd,
          properties: properties || {}
        }, "*");
      }, 0);
    }
  };

  const showInformationOverlay = (text: string): void => {
    const overlay = document.getElementById("printess_information_overlay_background");

    if (overlay) {
      const content = document.getElementById("printess_information_overlay_text");

      if (content) {
        content.innerHTML = text;
      }

      overlay.classList.add("visible");

      if (!overlay.getAttribute("data-initialized")) {
        overlay.setAttribute("data-initialized", "true");

        document.body.appendChild(overlay);
      }
    }

    printessTrapFocus(overlay);
  };

  const hideInformationOverlay = (): void => {
    const overlay = document.getElementById("printess_information_overlay_background");

    if (overlay) {
      overlay.classList.remove("visible");
      printessFreeFocus();
    }
  }

  const getStoredSaveTokens = (): string[] => {
    let storedSaveTokens: string[] = [];

    try {
      const json = localStorage.getItem("printessStoredSaveTokens") || "";

      if(json) {
        const strorageItems = <string[]>JSON.parse(json);

        if(strorageItems && Array.isArray(strorageItems)) {
          storedSaveTokens = [...storedSaveTokens, ...strorageItems];
        }
      }
    } catch(e) {

    }

    try {
      const json = sessionStorage.getItem("printessStoredSaveTokens") || "";

      if(json) {
        const strorageItems = <string[]>JSON.parse(json);

        if(strorageItems && Array.isArray(strorageItems)) {
          storedSaveTokens = [...storedSaveTokens, ...strorageItems];
        }
      }
    } catch(e) {

    }

    return storedSaveTokens;
  }

  const storeSaveToken = (item: string | string[], loadExisting: boolean = true): void => {
    let storedSaveTokens: string[] = loadExisting ? getStoredSaveTokens() : [];

    if(item) {
      if(Array.isArray(item)) {
        storedSaveTokens = [...storedSaveTokens, ...item];
      } else {
        storedSaveTokens.push(item);
      }
    }

    try {
      localStorage.setItem("printessStoredSaveTokens", JSON.stringify(storedSaveTokens));
    } catch(e) {
      try {
        sessionStorage.setItem("printessStoredSaveTokens", JSON.stringify(storedSaveTokens));
      } catch(e) {
      
      } 
    }
  }

  const removeStoredSaveToken = (saveToken: string): void => {
    const storedSaveTokens = getStoredSaveTokens();
    const index = storedSaveTokens.findIndex((x) => {
      return x === saveToken;
    });

    if(index > -1) {
      storedSaveTokens.splice(index, 1);

      storeSaveToken(storedSaveTokens, false);
    }
  }

  const getStoredSavedDesigns = (): ISavedDesignStorageItem[] => {
    let storedDesigns: ISavedDesignStorageItem[] = [];

    try {
      const json = localStorage.getItem("printessSavedDesigns") || "";

      if(json) {
        const strorageItems = <ISavedDesignStorageItem[]>JSON.parse(json);

        if(strorageItems && Array.isArray(strorageItems)) {
          storedDesigns = [...storedDesigns, ...strorageItems];
        }
      }
    } catch(e) {

    }

    try {
      const json = sessionStorage.getItem("printessSavedDesigns") || "";

      if(json) {
        const strorageItems = <ISavedDesignStorageItem[]>JSON.parse(json);

        if(strorageItems && Array.isArray(strorageItems)) {
          storedDesigns = [...storedDesigns, ...strorageItems];
        }
      }
    } catch(e) {

    }

    return storedDesigns;
  }

  const storeSavedDesign = (item: ISavedDesignStorageItem | ISavedDesignStorageItem[], loadExisting: boolean = true): void => {
    let storedDesigns: ISavedDesignStorageItem[] = loadExisting ? getStoredSavedDesigns() : [];

    if(item) {
      if(Array.isArray(item)) {
        storedDesigns = [...storedDesigns, ...item];
      } else {
        storedDesigns.push(item);
      }
    }

    try {
      localStorage.setItem("printessSavedDesigns", JSON.stringify(storedDesigns));
    } catch(e) {
      try {
        sessionStorage.setItem("printessSavedDesigns", JSON.stringify(storedDesigns));
      } catch(e) {
      
      } 
    }
  }

  const removeStoredDesign = (id: string): void => {
    const storedDesigns = getStoredSavedDesigns();
    const index = storedDesigns.findIndex((x) => {
      return x.storageId === id;
    });

    if(index > -1) {
      if(storedDesigns[index] && storedDesigns[index].saveToken) {
        removeStoredSaveToken(storedDesigns[index].saveToken);
      }

      storedDesigns.splice(index, 1);

      storeSavedDesign(storedDesigns, false);
    }
  }

  const postSavedDesign = async (item: {"saveToken": string,
      "thumbnailUrl": string,
      "productId": number,
      "displayName": string,
      "options": string,
    desingId?: number}): Promise<Response> => {
      return fetch("/index.php/wp-json/printess/v1/design/add", {
            method: "POST",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: {
              "Content-Type": "application/json",
              "X-WP-Nonce": printessSettings.nonce
            },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            body: JSON.stringify(item),
          });
  }

  const saveDesign = (saveToken: string, thumbnailUrl: string, productId: number, designName: string, designId: string | number, options: Record<string, any>, onOk: ((designName: string, designId: number) => void), onError: ((message: string) => void), retryCount = 0, storageItemId?: string ): void => {
    const body = {
      "saveToken": saveToken,
      "thumbnailUrl": thumbnailUrl,
      "productId": productId,
      "displayName": designName,
      "options": JSON.stringify(options)
    };

    if (typeof designId === "string" && designId) {
      const id = parseInt(designId);

      if (!isNaN(id) && id > 0) {
        body["designId"] = id;
      }
    } else if (typeof designId === "number" && designId) {
      if (!isNaN(designId) && designId > 0) {
        body["designId"] = designId;
      }
    }

    if(!storageItemId) {
      storageItemId = PrintessSharedTools.generateUUID();

      const storageItem: ISavedDesignStorageItem = {
        ...body,
        storageId: storageItemId
      };

      storeSavedDesign(storageItem);
    }

    //Test for debugging and not saving on servber side
    const params = new URLSearchParams(window.location.search);

    if (params.has('breakstoreddesign')) {
      onOk(designName, -1);
      return;
    }

    postSavedDesign(body).then((result) => {
      if (!result.ok) {
        if(retryCount < 5) {
          saveDesign(saveToken, thumbnailUrl, productId, designName, designId, options, onOk, onError, retryCount + 1, storageItemId);
        } else {
          console.error(result.statusText);

          if (typeof onError === "function") {
            onError((printessSettings.userMessages && printessSettings.userMessages["saveError"] ? printessSettings.userMessages["saveError"] : "There was an error while trying to save your design. Your Save Token: ") + " " + saveToken);
          }
        }
      } else {
        removeStoredDesign(storageItemId);

        result.json().then((json) => {
          if (typeof onOk === "function") {
            onOk(designName, json);
          }
        });
      }

      return result;
    }).catch(function (error) {
      if(retryCount < 5) {
        saveDesign(saveToken, thumbnailUrl, productId, designName, designId, options, onOk, onError, retryCount + 1, storageItemId);
      }
      else {
        console.log(error);

        if (typeof onError === "function") {
          onError((printessSettings.userMessages && printessSettings.userMessages["saveError"] ? printessSettings.userMessages["saveError"] : "There was an error while trying to save your design. Your Save Token:") + " " + saveToken);
        }
      }
    });
  }

  const loginAndSave = (displayName: string, productId: number, variantId: number, saveToken: string, thumbnailUrl: string, options: Record<string, any> | null = null): boolean => {
    if (!displayName || !displayName.trim()) {
      return false;
    }

    if (!printessSettings.accountPageUrl) {
      return false;
    }

    const url = printessSettings.accountPageUrl.replace("__ProductId__", "" + productId.toString())
      .replace("__SaveToken__", "" + saveToken)
      .replace("__ThumbnailUrl__", encodeURIComponent(thumbnailUrl))
      .replace("__VariantId__", "" + variantId)
      .replace("__Options__", encodeURIComponent(JSON.stringify(options ?? {})))
      .replace("__Token__", encodeURIComponent(printessSettings.urlToken || ""))
      .replace("__DisplayName__", "" + displayName.trim());


    window.location.href = url;

    return true;
  };

  const saveAdminSaveToken = (saveToken: string, thumbnail: string): void => {
    const redirectLink = document.getElementById("printess-admin-save") as HTMLLinkElement;

    const loadingMessage = document.getElementById("printess-loading-message");
    const savingMessage = document.getElementById("printess-saving-message");

    loadingMessage?.style.setProperty("display", "none");
    savingMessage?.style.setProperty("display", "block");


    if (redirectLink) {
      redirectLink.href += "&pst=" + saveToken;
      redirectLink.href += "&ptu=" + encodeURI(thumbnail);
      redirectLink.click()
    } else {
      alert("Error: Redirect link not found. Unable to save changes");
    }

  };

  const numberFormat = (number: string, decimals: number, decimalSeperator: string, thousandsSeperator: string): string => {
    //Src: http://phpjs.org/functions/number_format/
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
      prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
      sep = (typeof thousandsSeperator === 'undefined') ? ',' : thousandsSeperator,
      dec = (typeof decimalSeperator === 'undefined') ? '.' : decimalSeperator,
      s: string | string[],
      toFixedFix = function (n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
      };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
      s[1] = s[1] || '';
      s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
  };

  const mapFormFieldName = (product: IWooCommerceProduct, formFieldName: string): string => {
    let name = formFieldName = formFieldName;
    if (product && product.attributes) {
      let attribute = product.attributes[formFieldName];
      if (!attribute) {
        for (const key in product.attributes) {
          if (product.attributes.hasOwnProperty(key)) {
            if (product.attributes[key].key === name || product.attributes[key].name === name) {
              name = product.attributes[key].name;
              break;
            }
          }
        }
      }
      else {
        name = attribute.name;
      }
    }
    return name;
  };

  const createShopContext: (settings: IShowEditorSettings) => IWooCommerceShopContext = function (settings) {
    const context = {
      templateNameOrSaveToken: settings.templateNameOrSaveToken || settings.product.templateName,
      stickers: [] as IStickerInfo[],
      legalText: settings.legalText || "",
      legalTextUrl: settings.legalUrl || "",
      snippetPrices: [] as ISnippetPrice[],
      chargeEachStickerUsage: false,
      hidePricesInEditor: typeof printessSettings.showPricesInEditor !== "undefined" && printessSettings.showPricesInEditor === false,
      additionalAttachParams: settings.additionalAttachParams || null,

      getProductName: () => {
        if (typeof printessSettings.showProductName !== "undefined" && printessSettings.showProductName === false) {
          return "";
        }

        return settings.product ? settings.product.name || "" : "";
      },
      getPriceInfo: () => { return null; /*IPriceInfo*/ },
      getMergeTemplates: () => {
        let ret: IMergeTemplate[] = [];

        if (settings.mergeTemplates) {
          ret = [
            ...ret,
            ...PrintessSharedTools.parseMergeTemplate(settings.mergeTemplates as IMergeTemplate[])
          ]
        }

        if (settings.product.mergeTemplates) {
          ret = [
            ...ret,
            ...PrintessSharedTools.parseMergeTemplate(settings.product.mergeTemplates)
          ]
        }

        const currentFormFieldValues = getCurrentProductOptionValues(settings.product);
        const selectedVariant = getCurrentVariant(currentFormFieldValues, settings.product);

        if (selectedVariant && selectedVariant.templateName && selectedVariant.templateIsMergeTemplate) {
          ret = [
            ...ret,
            ...PrintessSharedTools.parseMergeTemplate(selectedVariant.templateName)
          ]
        }

        return ret;
      },
      formatMoney: (price: number) => {
        if (printessSettings.priceFormatOptions) {
          return (printessSettings.priceFormatOptions.currencySymbolOnLeftSide === true ? printessSettings.priceFormatOptions.currencySymbol : "")
            + numberFormat('' + price, 2, printessSettings.priceFormatOptions.decimalSeperator, printessSettings.priceFormatOptions.thousandsSeperator)
            + (printessSettings.priceFormatOptions.currencySymbolOnLeftSide !== true ? printessSettings.priceFormatOptions.currencySymbol : "");
        } else {
          return parseFloat("" + price).toFixed(2);
        }
      },
      onFormFieldChanged: (formFieldName: string, formFieldValue: string, formFieldLabel: string, valueLabel: string) => {
        const attributeLookup = getAttributeLookup(settings.product, false);
        let transformedName = formFieldName;

        if (attributeLookup[formFieldName]) {
          transformedName = attributeLookup[formFieldName].key;
        } else if (attributeLookup[formFieldLabel]) {
          transformedName = attributeLookup[formFieldLabel].key;
        }

        let selectedAttribute: IWooCommerceAttribute = settings.product.attributes[transformedName];

        if (!selectedAttribute) {
          for (const attributeKey in settings.product.attributes) {
            if (settings.product.attributes.hasOwnProperty(attributeKey) && settings.product.attributes[attributeKey].name === transformedName) {
              selectedAttribute = settings.product.attributes[attributeKey];
              break;
            }
          }
        }

        if (selectedAttribute && selectedAttribute.valueKeys && selectedAttribute.valueKeys.length > 0) {
          let foundValue = false;

          for (let i = 0; i < selectedAttribute.values.length; ++i) {
            if (selectedAttribute.values[i] === formFieldValue && selectedAttribute.valueKeys.length > i) {
              formFieldValue = selectedAttribute.valueKeys[i];
              foundValue = true;
              break;
            }
          }

          if (!foundValue) {
            for (let i = 0; i < selectedAttribute.values.length; ++i) {
              if (selectedAttribute.values[i] === valueLabel && selectedAttribute.valueKeys.length > i) {
                formFieldValue = selectedAttribute.valueKeys[i];
                break;
              }
            }
          }
        }

        if (formFieldName && !formFieldLabel) {
          formFieldLabel = formFieldName;
        }

        if (formFieldValue && !valueLabel) {
          valueLabel = formFieldValue;
        }

        if (context.additionalAttachParams && typeof context.additionalAttachParams["pageCountFormField"] !== "undefined" && context.additionalAttachParams["pageCountFormField"] === formFieldName) {
          if (settings.product.attributes) {
            let selectedOption: IWooCommerceAttribute = settings.product.attributes[context.additionalAttachParams["pageCountFormField"]];

            if (!selectedOption) {
              for (const attributeKey in settings.product.attributes) {
                if (settings.product.attributes.hasOwnProperty(attributeKey) && settings.product.attributes[attributeKey].name === context.additionalAttachParams["pageCountFormField"]) {
                  selectedOption = settings.product.attributes[attributeKey];
                  break;
                }
              }
            }

            if (selectedOption) {
              for (let i = 0; i < selectedOption.values.length; ++i) {
                if (PrintessEditor.extractNumber(selectedOption.values[i]).toString() === formFieldValue) {
                  formFieldValue = selectedOption.values[i];

                  if (selectedAttribute.valueKeys && selectedAttribute.valueKeys.length > i) {
                    formFieldValue = selectedOption.valueKeys[i];
                  }
                }
              }
            }
          }
        }

        //Take care of item usage
        const globalSettings = getGlobalConfig();

        if (globalSettings && globalSettings.pricePerUsageFields) {
          if (Array.isArray(globalSettings.pricePerUsageFields)) {
            const usageItem = globalSettings.pricePerUsageFields.filter(x => x.formFieldName === formFieldName);

            if (usageItem && usageItem.length > 0) {
              const amount = parseInt(formFieldValue);

              if (amount > 0) {
                if (!itemUsage) {
                  itemUsage = {};
                }

                itemUsage[usageItem[0].formFieldName] = amount;
              } else {
                if (itemUsage[usageItem[0].formFieldName]) {
                  delete itemUsage[usageItem[0].formFieldName];
                }
              }
            }
          } else {
            if (globalSettings.pricePerUsageFields[formFieldName]) {
              const amount = parseInt(formFieldValue);

              if (amount > 0) {
                if (!itemUsage) {
                  itemUsage = {};
                }

                itemUsage[formFieldName] = amount;
              } else {
                if (itemUsage[formFieldName]) {
                  delete itemUsage[formFieldName];
                }
              }
            }
          }
        }

        const radios = document.querySelectorAll(`input[type="radio"][name="attribute_` + transformedName + `"], input[type="radio"][name="` + transformedName + `"],input[type="radio"][name="attribute_` + formFieldLabel + `"], input[type="radio"][name="` + formFieldLabel + `"]`);

        if (radios && radios.length > 0) {
          radios.forEach((el) => {
            if (el.getAttribute("value") === formFieldValue || el.getAttribute("value") === valueLabel) {
              el.setAttribute('checked', true.toString());
              (el as HTMLInputElement).checked = true;
            } else {
              el.removeAttribute('checked');
              (el as HTMLInputElement).checked = false;
            }
          });

          return;
        }

        const selects = document.querySelectorAll(`select[name="attribute_` + transformedName + `"],select[name="` + transformedName + `"],select[name="attribute_` + formFieldLabel + `"],select[name="` + formFieldLabel + `"]`);

        if (selects && selects.length > 0) {
          selects.forEach((el) => {
            if ((el as HTMLSelectElement).options) {
              for (let i = 0; i < (el as HTMLSelectElement).options.length; ++i) {
                const value = (el as HTMLSelectElement).options[i].getAttribute('value');

                if (formFieldValue == value || valueLabel === value) {
                  (el as HTMLSelectElement).options[i].setAttribute('selected', true.toString());
                  (el as HTMLSelectElement).options[i].selected = true;

                  el.setAttribute('value', value);
                  (el as HTMLSelectElement).value = value;
                }
                else {
                  (el as HTMLSelectElement).options[i].removeAttribute('selected');
                  (el as HTMLSelectElement).options[i].selected = false;
                }
              }
            }
          });

          return;
        }

        //Text inputs
        const inputs = document.querySelectorAll(`input[name="attribute_` + transformedName + `"],input[name="` + transformedName + `"],input[name="attribute_` + formFieldLabel + `"],input[name="` + formFieldLabel + `"]`);
        const textInputs = ["color", "date", "datetime-local", "email", "month", "number", "tel", "text", "time", "url", "week"];

        if (inputs && inputs.length > 0) {
          inputs.forEach((el) => {
            if (textInputs.indexOf(el.getAttribute("type").toLowerCase()) > -1) {
              el.setAttribute("value", formFieldValue);
            }
          });
        }
      },
      onAllowAddToBasketAsync: (saveToken: string, thumbnailUrl): Promise<boolean> => {
        if (!settings.enforceDisplayName) {
          return new Promise((resolve: ((allow: boolean) => void), reject: (() => void)) => { resolve(true); });
        }

        return new Promise((resolve: ((allow: boolean) => void), reject: (() => void)) => {
          try {
            const enforceDisplayNameValue = (initialValue: string | null = null) => {
              this.showDialog("printess_display_name", initialValue || context.designName || "", (okClicked: boolean, value: string) => {
                value = (value || "").trim();

                if (okClicked) {
                  if (settings.enforceDisplayName === "enforce" && !value) {
                    enforceDisplayNameValue();
                  } else {
                    context.designName = value || "";

                    resolve(true);
                  }
                } else {
                  resolve(false);
                }
              });
            };

            enforceDisplayNameValue();
          } catch (ex) {
            console.error(ex);
            resolve(true);
          }
        });
      },
      onAddToBasket: (saveToken: string, thumbnailUrl: string) => {
        if(!saveToken) {
          alert("Unable to create basket item. No SaveToken received.");
          return;
        }

        if (printessSettings.editorMode === "admin") {
          saveAdminSaveToken(saveToken, thumbnailUrl);
          return;
        }

        updatePrintessValues(saveToken, thumbnailUrl, context.designId?.toString() ?? "", context.designName?.toString() ?? "");

        if (printessSettings.editorMode === "buyer" && printessSettings.addToCartAfterCustomization) {
          const cartForm = document.querySelector(CART_FORM_SELECTOR) as HTMLFormElement;
          const addToCartElement = cartForm.querySelector("[name=add-to-cart]") as HTMLInputElement;
          const currentFormFieldValues = getCurrentProductOptionValues(settings.product);
          const selectedVariant = getCurrentVariant(currentFormFieldValues, settings.product);
          const params = new URLSearchParams(window.location.search);

          //printess_show_information_overlay("<?php echo esc_html__( 'Adding item to cart.', 'printess-editor' ); ?>");

          if (selectedVariant) {
            document.getElementsByName("variation_id").forEach((el) => el.setAttribute("value", selectedVariant.id.toString()));
          }

          //remove the disabled attribute from the add to cart button
          if (addToCartElement.hasAttribute("disabled")) {
            addToCartElement.removeAttribute("disabled");
          }

          //in case this is editing of a cart item or a saved design forward to the cart
          if (cartForm && printessSettings.cartUrl && ((typeof settings.basketItemId != "undefined" && settings.basketItemId) || ((params.has("design_id") || params.has("design_name")) && (params.get("design_id") || params.get("design_name"))))) {
            cartForm.setAttribute("action", printessSettings.cartUrl);

            //we have to tell the backend to ignore redirects
            const ignoreInput = document.createElement("input") as HTMLInputElement;
            ignoreInput.setAttribute("id", "printess_ignore_redirect");
            ignoreInput.setAttribute("name", "printess_ignore_redirect");
            ignoreInput.value = "true";
            cartForm.appendChild(ignoreInput);
          } else {
            const ignoreInput = document.getElementById("printess_ignore_redirect");

            if (ignoreInput) {
              ignoreInput.remove();
            }
          }

          //Add item usage form field
          if (cartForm && itemUsage) {
            const usageInput = document.createElement("input") as HTMLInputElement;
            usageInput.setAttribute("id", "printess_item_usage");
            usageInput.setAttribute("name", "printess_item_usage");
            usageInput.value = JSON.stringify(itemUsage);
            cartForm.appendChild(usageInput);
          }

          if (settings.useRecordCountAsQuantity === true && typeof context.currentRecordCount !== null && context.currentRecordCount != null && context.currentRecordCount > 0) {
            //Add input for basket item flag that this item should not render out quantity control
            const hideQuantityInput = document.createElement("input") as HTMLInputElement;
            hideQuantityInput.setAttribute("id", "printess_hide_quantity");
            hideQuantityInput.setAttribute("name", "printess_hide_quantity");
            hideQuantityInput.value = "true";
            cartForm.appendChild(hideQuantityInput);

            const quantiyCtrl = document.querySelector('input[name="quantity"],select[name="quantity"],input[name="qty"],select[name="qty"]') as HTMLInputElement | HTMLSelectElement;

            if (quantiyCtrl) {
              quantiyCtrl.value = context.currentRecordCount.toString();
            }
          }

          try {
            const globalConfig = getGlobalConfig();

            if (globalConfig && typeof globalConfig.onAddToBasket === "function") {
              globalConfig.onAddToBasket(saveToken, thumbnailUrl);
            }
          } catch (e) {
            console.error(e);
          }

          try {
            postTrackingEvent("add_to_cart_clicked", context, settings.product);
          } catch (e) {
            console.error(e);
          }

          if (addToCartElement.type === "submit") {
            addToCartElement.click();
          } else {
            cartForm.submit();
          }

          const changePageAfterSubmit = () => {
            //Redirect to url in case ajax is enabled
            if (settings.product.ajaxEnabled === true) {
              let redirectUrl = "";

              //In case the product has a redirect url set, go to there
              if (settings.product && settings.product.redirectUrl) {
                redirectUrl = settings.product.redirectUrl;
              }

              //In case we are editing a cart item, go to the cart
              if (printessSettings.cartUrl && typeof settings.basketItemId !== "undefined" && settings.basketItemId) {
                redirectUrl = printessSettings.cartUrl;
              }

              //In case we are adding a saved design to the cart, got to the cart. (We can determine if this is a saved design from the saved design area by checkking the url)
              if (printessSettings.cartUrl && ((params.has("design_id") || params.has("design_name")) && (params.get("design_id") || params.get("design_name")))) {
                redirectUrl = printessSettings.cartUrl;
              }

              if (redirectUrl) {
                setTimeout(function () {
                  window.location.href = redirectUrl;
                }, 200);
              }
            }
          };

          if (settings.product.ajaxEnabled) {
            return {
              "executeBeforeClosing": changePageAfterSubmit,//printess_hide_information_overlay,
              "waitUntilClosingMS": 1000
            }
          } else {
            return;
          }
        }
      },
      getCurrentFormFieldValues: (): Record<string, string> => {
        return getCurrentProductOptionValues(settings.product);
      },
      getPriceForFormFieldsAsync: async (formFields: Record<string, string>): Promise<number> => {
        const variant = getCurrentVariant(formFields, settings.product);
        let ret = variant ? parseFloat(variant.price) : settings.product.price;

        const globalSetting = getGlobalConfig();

        if (itemUsage && globalSetting && globalSetting.pricePerUsageFields) {
          let lookup: Record<string, number> = {};
          if (Array.isArray(globalSetting.pricePerUsageFields)) {
            for (let i = 0; i < globalSetting.pricePerUsageFields.length; ++i) {
              lookup[globalSetting.pricePerUsageFields[i].formFieldName] = globalSetting.pricePerUsageFields[i].pricePerUsage;
            }
          } else {
            lookup = globalSetting.pricePerUsageFields;
          }

          for (const property in itemUsage) {
            if (itemUsage.hasOwnProperty(property)) {
              if (lookup[property]) {
                ret += itemUsage[property] * lookup[property];
              }
            }
          }
        }

        if (settings.useRecordCountAsQuantity === true && typeof context.currentRecordCount !== "undefined" && context.currentRecordCount !== null && context.currentRecordCount > 0) {
          ret *= context.currentRecordCount;
        }

        if (globalSetting !== null && (typeof globalSetting.getPriceForFormFields !== "undefined" || typeof globalSetting.getPriceForFormFieldsAsync !== "undefined")) {
          try {
            const params = {
              formFieldValues: formFields,
              product: settings.product,
              selectedVariant: variant,
              basketItemId: settings.basketItemId.toString(),
              pricePerUsageFields: globalSetting.pricePerUsageFields,
              itemUsage: JSON.parse(JSON.stringify(itemUsage))
            } as ICallbackParams;

            if (typeof globalSetting.getPriceForFormFieldsAsync !== "undefined") {
              const newPrice = await globalSetting.getPriceForFormFieldsAsync(formFields, ret, params);

              if (newPrice != ret) {
                ret = newPrice;
              }
            } else if (typeof globalSetting.getPriceForFormFields !== "undefined") {
              const newPrice = globalSetting.getPriceForFormFields(formFields, ret, params);

              if (newPrice != ret) {
                ret = newPrice;
              }
            }
          }
          catch (e) {

          }
        }

        return ret;
      },
      getFormFieldMappings: (): Record<string, IFormFieldMapping> => {
        let ret: Record<string, IFormFieldMapping> = {};

        if (settings.optionValueMappings && typeof settings.optionValueMappings === "string") {
          try {
            ret = JSON.parse(settings.optionValueMappings) as Record<string, IFormFieldMapping>;
          } catch (e) {
            console.error("Unable to parse form field mappings: " + e);
          }
        }

        return ret;
      },
      onSaveTimer: (): void => {
        if (typeof settings.showSaveWarningAfter !== "undefined" && settings.showSaveWarningAfter > 0 && typeof context.save === "function") {
          if (!printessSaveReminderActive) {
            this.showDialog("printess_save_reminder", "", (okClicked: boolean, value: string) => {
              value = (value || "").trim();

              if (okClicked) {
                context.save();
              }
            });
            printessSaveReminderActive = true;
          }
        }
      },
      onSave: (saveToken: string, thumbnailUrl: string): void => {
        if(!saveToken) {
          hideInformationOverlay();
          alert("Unable to save design. No SaveToken received.");
          return;
        }

        try {
          storeSaveToken(saveToken);
        } catch(ex) {
          try {

          } catch(ex) {

          }
        }

        const params = new URLSearchParams(window.location.search);
        if (params.has('breaksave')) {
          hideInformationOverlay();
          return;
        }

        context.cameFromSave = true;
        context.lastSaveSaveToken = saveToken;

        const productValues = getCurrentProductOptionValues(settings.product);
        const variant = getCurrentVariant(productValues, settings.product);

        if (settings.product.variants && settings.product.variants.length > 0) {
          const invalidVariantOptionName = getInvalidVariantOption(productValues, settings.product);

          if (invalidVariantOptionName) {
            let message = printessSettings?.userMessages && printessSettings.userMessages["unableToSaveChangesDueToInvalidVariant"] ? printessSettings.userMessages["unableToSaveChangesDueToInvalidVariant"].replace("{0}", invalidVariantOptionName) : ("Unable to save changes due to invalid value for " + invalidVariantOptionName);
            message += "\r\nSaveToken: " + saveToken;
            hideInformationOverlay();
            alert(message);
            console.log("Current SaveToken: " + saveToken);
            
            return;
          }
        }

        if (printessSettings.editorMode === "admin") {
          saveAdminSaveToken(saveToken, thumbnailUrl);
          return;
        }

        const cancelCallback = (): void => {
          removeStoredSaveToken(saveToken);
        };

        const saveDesignCallback = (designName: string): void => {
          if (!designName || !designName.trim()) {
            alert(printessSettings.userMessages && printessSettings.userMessages["noDisplayName"] ? printessSettings.userMessages["noDisplayName"] : 'Please provide a display name.');

            showSaveDialog(context.designName || "", printessSettings.userIsLoggedIn ? saveDesignCallback : loginCallback, cancelCallback);

            return;
          }

          showInformationOverlay(printessSettings.userMessages && printessSettings.userMessages["savingDesign"] ? printessSettings.userMessages["savingDesign"] : "Saving design to your list of saved designs");
          saveDesign(saveToken, thumbnailUrl, settings.product.id, designName, context.designId, getCurrentProductOptionValues(settings.product, false), (savedDesignName: string, savedDesignId: number): void => {
            hideInformationOverlay();

            context.designName = savedDesignName;
            context.designId = savedDesignId;

            try {
              const globalConfig = getGlobalConfig();

              if (globalConfig && typeof globalConfig.onSave === "function") {
                globalConfig.onSave(saveToken, thumbnailUrl);
              }
            } catch (e) {
              console.error(e);
            }

            try {
              postTrackingEvent("design_saved", context, settings.product);
            } catch (e) {
              console.error(e);
            }
          }, (message: string): void => {
            hideInformationOverlay();

            alert(message);

            showSaveDialog(context.designName, printessSettings.userIsLoggedIn ? saveDesignCallback : loginCallback, cancelCallback);
          });
        };

        const loginCallback = (designName: string): void => {
          if (!loginAndSave(designName, settings.product.id, variant ? variant.id : null, saveToken, thumbnailUrl, getCurrentProductOptionValues(settings.product, false))) {
            alert(printessSettings.userMessages && printessSettings.userMessages["noDisplayName"] ? printessSettings.userMessages["noDisplayName"] : 'Please provide a display name.');

            showSaveDialog(designName, printessSettings.userIsLoggedIn ? saveDesignCallback : loginCallback, cancelCallback);
          }
        };

        showSaveDialog(context.designName, printessSettings.userIsLoggedIn ? saveDesignCallback : loginCallback, cancelCallback);
      },
      getBasketId: (): string => { return settings.basketId; },
      getUserId: (): string => { return settings.userId; },

      editorClosed: (closeButtonClicked: boolean) => {
        editor.hide();

        context.designId = null;
        context.designName = null;

        if (closeButtonClicked) {
          updatePrintessValues("", "", "", "");
        }

        if ('buyer' === printessSettings.editorMode) { // Only buyer mode sets the token. The admin edit doesn't do anything!
          if (closeButtonClicked === true) {
            try {
              postTrackingEvent("editor_closed_without_add_to_cart", context, settings.product);
            } catch (e) {
              console.error(e);
            }
          }

          //Remove saved design info
          if (typeof URLSearchParams !== 'undefined') {

            const params = new URLSearchParams(window.location.search);

            if (closeButtonClicked && (params.has("design_id") || params.has("design_name") || params.has("printess-save-token"))) {
              setTimeout(function () {
                params.delete('design_id');
                params.delete('design_name');
                params.delete('printess-save-token');
                window.location.search = params.toString();
              }, 1000)
            }
          }
        } else {
          const msg = printessSettings.userMessages && printessSettings.userMessages["closeWindow"] ? printessSettings.userMessages["closeWindow"] : "Please close this window or tab.";

          alert(msg);
        }
      }
    } as IWooCommerceShopContext;

    //Try to find out the correct template name (selected variant & url params)
    const currentProductOptions = getCurrentProductOptionValues(settings.product);
    const currentVariant = getCurrentVariant(currentProductOptions, settings.product);

    if (currentVariant && currentVariant.templateName) {
      context.templateNameOrSaveToken = currentVariant.templateIsMergeTemplate ? settings.product.templateName : currentVariant.templateName;
    }

    //parse url settings and apply these
    const urlParams = new URLSearchParams(window.location.search);
    const urlSaveToken = urlParams.get('printess-save-token') || urlParams.get('printess_save_token');
    const designName = urlParams.get('design_name');
    const designId = urlParams.get("design_id");

    if (urlSaveToken) {
      context.templateNameOrSaveToken = urlSaveToken;
    }

    if (designName) {
      context.designName = designName;
    }

    if (designId) {
      context.designId = parseInt(designId);
    }

    return context;
  };

  const addPrintessInputs = function (parent: HTMLFormElement, saveToken: string, customizeButtonClass: string, customizeButtonLabel: string, enforceDisplayName: string): void {
    const saveTokenInput = document.createElement("input");
    const saveTokenToRemoveFromCartInput = document.createElement("input");
    const thumbnailUrlInput = document.createElement("input");
    const designIdInput = document.createElement("input");
    const designNameInput = document.createElement("input");
    const customizeButton = document.createElement("button");

    saveTokenInput.setAttribute("id", "printess-save-token");
    saveTokenInput.setAttribute("name", "printess-save-token");
    saveTokenInput.setAttribute("type", "hidden");

    saveTokenToRemoveFromCartInput.setAttribute("id", "printess-save-token-to-remove-from-cart");
    saveTokenToRemoveFromCartInput.setAttribute("name", "printess-save-token-to-remove-from-cart");
    saveTokenToRemoveFromCartInput.setAttribute("type", "hidden");
    saveTokenToRemoveFromCartInput.setAttribute("value", saveToken || "");

    thumbnailUrlInput.setAttribute("id", "printess-thumbnail-url");
    thumbnailUrlInput.setAttribute("name", "printess-thumbnail-url");
    thumbnailUrlInput.setAttribute("type", "hidden");

    designIdInput.setAttribute("id", "printess-design-id");
    designIdInput.setAttribute("name", "printess-design-id");
    designIdInput.setAttribute("type", "hidden");

    designNameInput.setAttribute("id", "printess-design-name");
    designNameInput.setAttribute("name", "printess-design-name");
    designNameInput.setAttribute("type", "hidden");

    customizeButton.setAttribute("id", designNowButtonId);
    customizeButton.setAttribute("name", "printess-customize-button");
    customizeButton.setAttribute("type", "button");
    customizeButton.setAttribute("onclick", "showPrintessEditor();");

    if (printessSettings.hideCustomDesignNowButtonClasses !== true) {
      customizeButton.classList.add("wp-element-button", "single_add_to_cart_button", "button", "alt");
    }

    if (printessSettings.customizeButtonClasses) {
      printessSettings.customizeButtonClasses.split(" ").forEach((x) => {
        x = (x || "").trim();

        if (x) {
          customizeButton.classList.add(x);
        }
      });
    }


    customizeButton.appendChild(document.createTextNode(customizeButtonLabel || "Customize"));

    if (customizeButtonClass) {
      customizeButton.classList.add(customizeButtonClass);
    }

    //Try to move the customize button into the same parent as the add to basket button...
    const addToCartButton = document.querySelector(CART_FORM_SELECTOR + " button.single_add_to_cart_button") as HTMLElement;

    if (addToCartButton && addToCartButton.parentElement) {
      addToCartButton.parentElement.appendChild(customizeButton);
    } else {
      parent.appendChild(customizeButton);
    }

    parent.appendChild(saveTokenInput);
    parent.appendChild(saveTokenToRemoveFromCartInput);
    parent.appendChild(thumbnailUrlInput);
    parent.appendChild(designIdInput);
    parent.appendChild(designNameInput);
  };

  const showCustomizeButton = function (show: boolean) {
    const customizeButton = document.getElementById(designNowButtonId);
    const addToCartButton = document.querySelector(CART_FORM_SELECTOR + " button.single_add_to_cart_button") as HTMLElement;

    if (!customizeButton) {
      return;
    }

    if (addToCartButton) {
      if (!show) {
        addToCartButton.removeAttribute("disabled");
      } else {
        addToCartButton.setAttribute("disabled", "disabled");
      }

      addToCartButton.style.display = show ? "none" : "inline-block";
    }

    customizeButton.style.display = show ? "inline-block" : "none";
  }

  const variantChangedHandler = function (product: IWooCommerceProduct) {
    let templateName = "";
    const selectedVariant: IWooCommerceProductVariant | null = getCurrentVariant(getCurrentProductOptionValues(product), product);
    const variantsHaveTemplateNames: boolean = product.variants && typeof product.variants.find((x) => x.templateName && !x.templateIsMergeTemplate) !== "undefined" ? true : false;

    if (selectedVariant != null && selectedVariant.templateName) {
      templateName = selectedVariant.templateIsMergeTemplate ? product.templateName : selectedVariant.templateName;
    }

    if (!templateName && !variantsHaveTemplateNames) {
      templateName = product.templateName
    }

    showCustomizeButton(templateName && templateName.length > 0);
  };

  const addVariantChangedHandler = function (product: IWooCommerceProduct) {
    if (window["jQuery"]) {
      window["jQuery"]("form").on("found_variation", (e, variation) => {
        variantChangedHandler(product);
      });
    } else {
      setTimeout(addVariantChangedHandler, 100);
    }
  }

  const hasUnconfiguredProductOptions = (product: IWooCommerceProduct) => {
    const attributeLookup = getAttributeLookup(product, true);
    const variantSpecificValues: { key: string, value: string }[] = [];
    const productOptionValues = getCurrentProductOptionValues(product);

    for (const name in productOptionValues) {
      if (productOptionValues.hasOwnProperty(name) && attributeLookup[name]) {
        variantSpecificValues.push({ key: attributeLookup[name].key, value: productOptionValues[name] });
      }
    }

    for (const optionName in variantSpecificValues) {
      if (variantSpecificValues.hasOwnProperty(optionName) && !variantSpecificValues[optionName].value) {
        return true;
      }
    }

    return false;
  };

  const  retryFailedSaves = async (retryCount = 0): Promise<void> => {
    let storedDesigns = getStoredSavedDesigns();

    await Promise.all(storedDesigns.map(async (design): Promise<ISavedDesignStorageItem> => {
      try {
        const response = await postSavedDesign(design);

        if(response.ok) {
          removeStoredDesign(design.storageId);
        }
      } catch(e) {
        console.error(e);
      }

      return design;
    }));

    storedDesigns = getStoredSavedDesigns();

    if(storedDesigns.length > 0 && retryCount < 5) {
      retryFailedSaves(retryCount + 1);
    }
  }

  const editor = {
    show: function (settings: IShowEditorSettings): void {
      //Clear item usage
      itemUsage = null;

      //Ensure string values in attribute values (php transforms strings consisting of numbers to numbers)
      if (settings.product && settings.product.attributes) {
        for (const key in settings.product.attributes) {
          if (settings.product.attributes.hasOwnProperty(key)) {
            const attribute = settings.product.attributes[key];

            if (attribute.values) {
              for (let i = 0; i < attribute.values.length; ++i) {
                attribute.values[i] = "" + attribute.values[i];

                if (attribute.valueKeys && attribute.valueKeys.length > i) {
                  attribute.valueKeys[i] = "" + attribute.valueKeys[i];
                }
              }
            }
          }
        }
      }

      if(printessSettings.attachParams) {
        if(!settings.additionalAttachParams) {
          settings.additionalAttachParams = {};
        }

        settings.additionalAttachParams = {
          ...printessSettings.attachParams,
          ...settings.additionalAttachParams
        };
      }

      if (settings && settings.additionalAttachParams && typeof settings.additionalAttachParams["pageCountFormField"] !== "undefined") {
        settings.additionalAttachParams = JSON.parse(JSON.stringify(settings.additionalAttachParams));
        settings.additionalAttachParams["pageCountFormField"] = mapFormFieldName(settings.product, settings.additionalAttachParams["pageCountFormField"]);
      }

      //Make sure all variant options are selected
      if (hasUnconfiguredProductOptions(settings.product)) {
        //alert("Please select some product options before adding this product to your cart.");
        return;
      }

      //in case we are editing an existing save token, we have to replace the id that should be deleted inside the basket with the current save token, not the original template name,
      // to do this we have to update the printess_save_token_to_delete input value
      if (settings && settings.templateNameOrSaveToken && settings.templateNameOrSaveToken.startsWith("st:")) {
        const input = document.getElementById("printess-save-token-to-remove-from-cart") as HTMLInputElement;

        if (input) {
          input.value = settings.templateNameOrSaveToken;
        }
      }

      const shopContext = createShopContext(settings);

      if (typeof settings.showSaveWarningAfter !== undefined && settings.showSaveWarningAfter > 0) {
        shopContext.currentSaveTimerInMinutes = settings.showSaveWarningAfter;
      }

      //Hide shop items
      if (printessSettings.idsToHide) {
        printessSettings.idsToHide.forEach((x) => {
          const e = document.getElementById(x);

          if (e) {
            e.classList.add("printess-hide");
          }
        });
      }

      if (printessSettings.classesToHide) {
        printessSettings.classesToHide.forEach((x) => {
          const e = document.getElementsByClassName(x);

          if (e && e.length) {
            for (const ele of e) {
              ele.classList.add("printess-hide");
            }
          }
        });
      }

      const globalConfig = getGlobalConfig();

      if (globalConfig && globalConfig.attachParams) {
        for (const property in globalConfig.attachParams) {
          if (globalConfig.attachParams.hasOwnProperty(property)) {
            if (!printessSettings.attachParams) {
              printessSettings.attachParams = {};
            }

            printessSettings.attachParams[property] = globalConfig.attachParams[property];
          }
        }
      }

      if (typeof window["initPrintessEditor"] === "function") {
        try {
          postTrackingEvent(shopContext.templateNameOrSaveToken ? (shopContext.templateNameOrSaveToken.startsWith("st:") ? "editor_opened_save_token" : "editor_opened_new_design") : "editor_opened_new_design", shopContext, settings.product);
        } catch (e) {
          console.error(e);
        }

        const editor = window["initPrintessEditor"](printessSettings);
        editor.show(shopContext);
      }
    },
    hide: function (): void {
      printessSettings.idsToHide.forEach((x) => {
        const e = document.getElementById(x);

        if (e) {
          e.classList.remove("printess-hide");
        }
      });

      printessSettings.classesToHide.forEach((x) => {
        const e = document.getElementsByClassName(x);

        if (e && e.length) {
          for (const ele of e) {
            ele.classList.remove("printess-hide");
          }
        }
      });
    },
    initProductPage: function (product: IWooCommerceProduct, templateNameOrSaveToken: string, customizeButtonClass: string, customizeButtonLabel: string = "Customize", openEditorCallback: (saveToken: string, basketItemId?: string | null) => void, formSelector: string = CART_FORM_SELECTOR, enforceDisplayName: string = ""): void {
      const productForm: HTMLFormElement = document.querySelector(formSelector || CART_FORM_SELECTOR) as HTMLFormElement;

      setTimeout(function() {
        retryFailedSaves();
      }, 0)

      if (productForm) {
        addPrintessInputs(productForm, templateNameOrSaveToken, customizeButtonClass, customizeButtonLabel, enforceDisplayName);

        if (templateNameOrSaveToken) {
          showCustomizeButton(true);
        }
      }

      addVariantChangedHandler(product);
      variantChangedHandler(product);

      //Open the editor on page load in case the url contains a save token or template name
      const urlParams = new URLSearchParams(window.location.search);
      const urlSaveToken = urlParams.get('printess-save-token');
      const basketItemId = urlParams.get("printess_item_key");

      if (urlSaveToken) {
        if (typeof openEditorCallback === "function") {
          openEditorCallback(urlSaveToken, basketItemId);
        }
      }
    }
  };

  window["printessWooEditor"] = editor;

  return editor;
}

function printessQueryItem<T>(itemQuery: () => T | null | undefined, callback: (element: T) => void, timeout: number = 200, maxRetires = 20, retries: number = 0): void {
  if (retries >= maxRetires) {
    return;
  }

  const element: T | null | undefined = itemQuery();

  if (element) {
    callback(element);
    return;
  }

  setTimeout(function () {
    const element: T | null | undefined = itemQuery();

    if (element) {
      callback(element);
    } else {
      printessQueryItem(itemQuery, callback, timeout, maxRetires, retries + 1);
    }
  }, timeout);
}

let previouslyFocused: Element;

function showDialog(prefix: string, initialValue: string, callback: ((okClicked: boolean, value: string) => void)) {
  const textInput = document.getElementById(prefix + "_edit") as HTMLInputElement;
  const okButton = document.getElementById(prefix + "_ok_button") as HTMLInputElement;
  const cancelButton = document.getElementById(prefix + "_cancel_button") as HTMLInputElement;
  const dlg = <IEventAgnosticDialog>document.getElementById(prefix + "_overlay_background");
  const bodyElement = document.querySelector("body") as HTMLElement;
  let hide: () => void | null = null;

  previouslyFocused = document.activeElement;

  addEventHelpersToDialog(dlg);

  const keyUpHandler = (e) => {
    if (e.key === 'Enter' || e.keyCode === 13) {
      e.preventDefault();
      e.stopPropagation();
    } else if (e.key === 'Escape' || e.keyCode === 27) {
      e.preventDefault();
      e.stopPropagation();
    }
  };

  const keyDownHandler = (e) => {
    if (e.key === 'Enter' || e.keyCode === 13) {
      const activeDomElement = document.activeElement;
      // if a button is active, Enter should activate the button, not do anything else
      if (!(activeDomElement instanceof HTMLButtonElement) && activeDomElement.id === "printess_display_name_cancel_button") {
        e.preventDefault();
        e.stopPropagation();
        hide();
        if (typeof callback === "function") {
          callback(true, textInput.value);
        }
      }
    } else if (e.key === 'Escape' || e.keyCode === 27) {
      e.preventDefault();
      e.stopPropagation();

      hide();

      if (typeof callback === "function") {
        callback(false, textInput.value);
      }
    }
  };

  hide = function () {
    if (okButton) {
      okButton.removeEventListener("click", okCallback);
    }

    if (cancelButton) {
      cancelButton.removeEventListener("click", cancelCallback);
    }

    if (dlg) {
      dlg.style.display = "none";

      if (typeof dlg.removeAllEventHandlers === "function") {
        dlg.removeAllEventHandlers();
      }

      printessFreeFocus();
    }

    if (previouslyFocused && previouslyFocused instanceof HTMLElement) {
      previouslyFocused.focus();
    }

    if (printessSaveReminderActive) {
      printessSaveReminderActive = false;
    }
  };

  const okCallback = (evt: Event) => {
    hide();

    if (typeof callback === "function") {
      callback(true, textInput?.value);
    }
  };

  const cancelCallback = (evt: Event) => {
    hide();

    if (typeof callback === "function") {
      callback(false, textInput?.value);
    }
  };

  if (textInput) {
    textInput.value = initialValue || "";
  }

  if (okButton) {
    okButton.addEventListener("click", okCallback);
  }

  if (cancelButton) {
    cancelButton.type = "button";
    cancelButton.addEventListener("click", cancelCallback);
  }

  if (bodyElement && dlg.parentElement !== bodyElement) {
    bodyElement.appendChild(dlg);
  }

  if (dlg) {
    dlg.style.display = "block";

    dlg.addEventHandler("keyup", keyUpHandler);
    dlg.addEventHandler("keydown", keyDownHandler);
  }

  printessTrapFocus(dlg);
}

function printessRegisterCheckoutFilters(registerCheckoutFilters: (pluginName: string, filters: Record<string, any>) => void): void {
  const getOrAddElement = (parent: HTMLElement, className: string, tagType?: string, additionalClass?: string): HTMLElement => {
    let ret: HTMLElement | null = (parent || document).querySelector("." + className);

    if (!ret) {
      ret = document.createElement(tagType);
      ret.classList.add(className);

      if (additionalClass) {
        ret.classList.add(additionalClass);
      }

      parent.appendChild(ret);
    }

    return ret;
  };

  registerCheckoutFilters('printess-editor', {
    cartItemClass: (defaultValue, extensions, args) => {
      let ret = defaultValue || "";

      if (extensions && extensions["printess-editor"] && extensions["printess-editor"]["saveToken"] && args["cartItem"]) {
        const className = "printess_cart_item_" + args["cartItem"]["key"];

        ret += " " + className + " ";

        //Modify item display
        setTimeout(function () {
          printessQueryItem<HTMLElement>(function (): HTMLElement | null | undefined {
            return document.querySelector("." + className);
          }, function (cartItem) {
            //Display design name
            if (extensions["printess-editor"]["designName"]) {
              let detailBlocks = cartItem ? cartItem.querySelector("ul.wc-block-components-product-details") : null;

              if (!detailBlocks) {
                const wrapper: HTMLElement | null = cartItem.querySelector(".wc-block-cart-item__wrap");

                if (wrapper) {
                  const metaData = getOrAddElement(wrapper, "wc-block-components-product-metadata", "div");
                  detailBlocks = getOrAddElement(metaData, "wc-block-components-product-details", "ul");
                }
              }
            }

            //Change thumbnail
            printessQueryItem<HTMLElement>(function (): HTMLElement | null | undefined {
              return cartItem?.querySelector(".wc-block-cart-item__image img, .wc-block-components-order-summary-item__image img");
            }, function (imageElement) {
              imageElement.classList.add("printess-thumbnail-image");
              imageElement.setAttribute("src", extensions["printess-editor"]["thumbnailUrl"]);
            }, 20, 100);

            //Read quantity
            let quantity = 1;
            printessQueryItem<HTMLElement>(function () {
              return cartItem?.querySelector(".wc-block-components-quantity-selector input");
            }, function (quantityInput) {
              if (quantityInput) {
                quantity = parseInt((quantityInput as HTMLInputElement).value);
                if (isNaN(quantity) || !isFinite(quantity) || quantity < 1) {
                  quantity = 1;
                }
              }
              if (extensions["printess-editor"]["thumbnailUrl"]) {
                let editLink = extensions["printess-editor"]["editLink"];
                if (editLink.indexOf("?") > 0) {
                  editLink = editLink.replace("?", '?qty=' + quantity + '&');
                }
                else {
                  if (editLink.indexOf("#") > 0) {
                    editLink = editLink.replace("#", '?qty=' + quantity + '#');
                  }
                  else {
                    editLink = editLink + '?qty=' + quantity;
                  }
                }
                //Add edit link
                printessQueryItem<HTMLElement>(function () {
                  return cartItem?.querySelector(".wc-block-cart-item__image a");
                }, function (thumbnailLink) {
                  thumbnailLink.setAttribute("href", editLink);
                }, 20, 100);

                printessQueryItem(function () {
                  return cartItem?.querySelector(".wc-block-cart-item__quantity");
                }, function (quantityElement) {
                  let link = quantityElement.querySelector(".printess-edit-link") as HTMLAnchorElement | null;
                  if (!link) {
                    link = document.createElement("a") as HTMLAnchorElement;
                    link.classList.add("wc-block-cart-item__remove-link");
                    link.classList.add("printess-edit-link");
                    link.innerText = "Edit";
                    const linkWrapper = document.createElement("div");
                    linkWrapper.appendChild(link);
                    quantityElement.appendChild(linkWrapper);
                  }
                  link.setAttribute("href", editLink);
                }, 20, 100);
              }
            }, 200, 20);
          }, 200, 20);
        }, 0);
      }

      return ret;
    }
  });
}

printessQueryItem<(pluginName: string, filters: Record<string, any>) => void>(function (): ((pluginName: string, filters: Record<string, any>) => void) {
  if (window["wc"] && window["wc"].blocksCheckout) {
    const { registerCheckoutFilters } = window["wc"].blocksCheckout;

    return registerCheckoutFilters;
  } else {
    return null;
  }
}, (registerCheckoutFilters) => {
  printessRegisterCheckoutFilters(registerCheckoutFilters);
}, 100, 20);