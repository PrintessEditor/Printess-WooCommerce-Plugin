import { IMergeTemplate } from "./printessEditor"

export interface ICreateSaveTokenParams {
  templateName: string
  contentTemplateName?: string
  usePublishedVersion: boolean
  autoMerge?: boolean
  mergeTemplates?: IMergeTemplate[],
  shopInfo: {
    basketId: string
    userId: string
    userDisplay?: string
  },
  formFields?: Record<string, string>
}

export interface ICreateSaveTokenResponse {
  saveToken: string;
  thumbnailUrl: string;
}

export interface IServiceSettings {
  apiDomain: string;
  serviceToken: string;
}

export interface ITagRequest extends IServiceSettings {
  includeGlobalTags?: boolean;
}

export interface ITag {
  tag: string
  layout: number
  group: number
}

export class PrintessSharedTools {
  public static async createSaveToken(shopToken: string, params: ICreateSaveTokenParams): Promise<ICreateSaveTokenResponse | null> {
    const response = await fetch('https://api.printess.com/production/savetoken/create', {
      method: 'POST',
      redirect: "follow",
      headers: {
        "Content-Type": 'application/json',
        "Authorization": `Bearer ${shopToken}`,
      },
      body: JSON.stringify(params)
    });

    if (!response.ok) {
      console.error("Unable to create save token: [" + response.status + "] " + response.statusText);
      return null;
    }

    return (await response.json()) as ICreateSaveTokenResponse;
  }

  public static decodeHTML(encoded: string): string {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = encoded;
    return textarea.value;
  }

  public static parseMergeTemplate(template: string[] | IMergeTemplate[] | IMergeTemplate | string | null | undefined, globalMergeModeOverwrite?: string | null): IMergeTemplate[] {
    let ret: IMergeTemplate[] = [];

    if (template) {
      if (Array.isArray(template)) {
        template.forEach((x) => {
          ret = [
            ...ret,
            ...PrintessSharedTools.parseMergeTemplate(x)
          ];
        });
      } else {
        if (typeof template !== "string") {
          ret.push(template as IMergeTemplate);
        } else {
          try {
            let deserialized: IMergeTemplate[] | IMergeTemplate | string | null = JSON.parse(template);

            ret = [
              ...ret,
              ...PrintessSharedTools.parseMergeTemplate(deserialized)
            ];
          } catch {
            try {
              let deserialized: IMergeTemplate[] | IMergeTemplate | string | null = JSON.parse(PrintessSharedTools.decodeHTML(template));

              ret = [
                ...ret,
                ...PrintessSharedTools.parseMergeTemplate(deserialized)
              ];
            } catch {
              ret.push({
                templateName: template
              });
            }
          }
        }
      }
    }

    if(globalMergeModeOverwrite) {
      ret.forEach((mergeTemplate) => {
        mergeTemplate.mergeMode = globalMergeModeOverwrite;
      });
    }

    return ret;
  }

  public static sleepAsync(timeoutMs: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, timeoutMs));
  }

  public static queryItem<T>(itemQuery: () => T | null | undefined, callback: (element: T) => void, failedCallback: (() => void) | null = null, timeout: number = 200, maxRetires = 20, retries: number = 0): void {
    if (retries >= maxRetires) {
      if (typeof failedCallback === "function") {
        failedCallback();
      }
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
        PrintessSharedTools.queryItem(itemQuery, callback, failedCallback, timeout, maxRetires, retries + 1);
      }
    }, timeout);
  }

  public static async queryItemAsync<T>(itemQuery: () => T | null | undefined, timeout: number = 50, maxRetires = 20): Promise<T> {
    return new Promise((resolve, reject) => PrintessSharedTools.queryItem(itemQuery, resolve, reject, timeout, maxRetires));
  }

  public static forEach<T>(items: T[] | Record<string, T> | null | undefined, callback: (item: T, index?: number, array?: T[] | Record<string, T>, key?: string) => void): void {
    if (!items || typeof callback !== "function") {
      return;
    }

    if (Array.isArray(items)) {
      items.forEach(callback);
    } else {
      let index = 0;
      for (const prop in items) {
        if (items.hasOwnProperty(prop)) {
          callback(items[prop], index, items, prop);

          ++index;
        }
      }
    }
  }

  public static map<T, U>(items: T[] | Record<string, T> | null | undefined, callback: (item: T, index: number, array: T[] | Record<string, T>, key?: string) => U): U[] {
    if (!items || typeof callback !== "function") {
      return [];
    }

    if (Array.isArray(items)) {
      return items.map(callback);
    } else {
      const ret: U[] = [];

      let index = 0;
      for (const prop in items) {
        if (items.hasOwnProperty(prop)) {
          ret.push(callback(items[prop], index, items, prop));

          ++index;
        }
      }

      return ret;
    }
  }

  public static filter<T>(items: Record<string, T>, callback: (value: T, list?: Record<string, T>, key?: string, index?: number) => boolean): Record<string, T> {
    const ret: Record<string, T> = {};

    PrintessSharedTools.forEach(items, (value: T, index?: number, arr?: Record<string, T> | T[], key?: string) => {
      if (callback(value, items, key, index) === true) {
        ret[key || ""] = value;
      }
    });

    return ret;
  }

  public static filterAsArray<T>(items: Record<string, T>, callback: (value: T, list?: Record<string, T>, key?: string, index?: number) => boolean): { key: string, value: T }[] {
    const ret: { key: string, value: T }[] = [];

    PrintessSharedTools.forEach(items, (value: T, index?: number, arr?: Record<string, T> | T[], key?: string) => {
      if (callback(value, items, key, index) === true) {
        ret.push({
          key: key || "",
          value: value
        });
      }
    });

    return ret;
  }

  public static ensureHttpProtocol(domain: string | IServiceSettings): string {
    let ret: string = ((typeof domain !== "string" ? domain.apiDomain : domain) || "").trim();

    if (ret.indexOf("http://") !== 0 && ret.indexOf("https://")) {
      if (ret.length > 1 && ret[0] === "/" && ret[1] === "/") {
        ret = ret.substring(2);
      } else if (ret.length > 0 && ret[0] === "/") {
        ret = ret.substring(1);
      }

      ret = "https://" + ret;
    }

    return ret;
  }

  public static urlConcat(url: string, ...parts: string[]): string {
    let ret = url || "";
    const filteredParts = (parts || []).filter(x => x ? true : false);

    filteredParts.forEach((part: string): void => {
      if (part.length > 1) {
        if (ret && ret[ret.length - 1] !== "/") {
          ret += "/";
        }

        ret += part[0] !== "/" ? part : part.substring(1);
      } else if (part[0] !== "/") {
        if (ret && ret[ret.length - 1] !== "/") {
          ret += "/";
        }

        ret += part;
      }
    });

    return ret;
  }

  public static sanitizeHost(host?: string): string {
    if (host) {
      host = host.trim();

      if (host.endsWith("/")) {
        return host;
      }

      return host + "/";
    }

    return host || "";
  }

  public static async postJsonAsync<T, U>(url: string, params: T, token?: string, additionalHeaders?: Record<string, string>): Promise<U> {
    const headers: Record<string, string> = {
      "Content-Type": 'application/json'
    }

    if (token) {
      headers["Authorization"] = "Bearer " + token;
    }

    if (additionalHeaders) {
      PrintessSharedTools.forEach(additionalHeaders, (value: string, index?: number | undefined, headers?: Record<string, string> | string[], key?: string): void => {
        if (key && value) {
          (<Record<string, string>>headers)[key] = value;
        }
      });
    }

    const response = await fetch('https://api.printess.com/shop/savetoken/split', {
      method: 'POST',
      redirect: "follow",
      headers: headers,
      body: JSON.stringify(params)
    });

    if (!response.ok) {
      throw `Unable to post json to ${url}: [${response.status}]: ${response.statusText}`;
    }

    return await response.json() as U;
  }

  public loadTagsAsync(settings: ITagRequest): Promise<ITag[]> {
    return PrintessSharedTools.postJsonAsync<{ includeGlobal: boolean }, ITag[]>(PrintessSharedTools.urlConcat(PrintessSharedTools.ensureHttpProtocol(settings), "snippets/tags/load"), { includeGlobal: settings.includeGlobalTags === true }, settings.serviceToken);
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

  public static getFileNameFromUrl(fileName: string): string {
    return (fileName || "").split('#')[0].split('?')[0].split('/').pop() || "";
  }

  public static async downloadImages(images: {name: string, value: string}[] | Record<string, string>): Promise<{ name: string, data: File }[]> {
    const ret: { name: string, data: File }[] = [];

    if (Array.isArray(images)) {
      for (let i = 0; i < images.length; ++i) {
        const response = await fetch(images[i].value as string);

        if (response.ok) {
          const blob = await response.blob();
          ret.push({
            name: images[i].name,
            data: new File([blob], this.getFileNameFromUrl(images[i].value as string), { type: blob.type })
          })
        } else {
          console.error("Unable to download image " + images[i].value + "; [" + response.status.toString() + "] " + response.statusText + ": " + await response.text());
        }
      }
    } else {
      for (const fileName in images) {
        if (images.hasOwnProperty(fileName)) {
          const fileUrl = images[fileName];
          const response = await fetch(fileUrl as string);

          if (response.ok) {
            const blob = await response.blob();
            ret.push({
              name: fileName,
              data: new File([blob], this.getFileNameFromUrl(fileUrl as string), { type: blob.type })
            })
          } else {
            console.error("Unable to download image " + images[fileName] + "; [" + response.status.toString() + "] " + response.statusText + ": " + await response.text());
          }
        }
      }
    }

    return ret;
  }
}