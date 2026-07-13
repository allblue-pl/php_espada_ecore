
export class eTexts_Class {
    #texts: {[text: string]: string};

    constructor() {
        this.#texts = {};
    }

    get(text: string, args: Array<string> = []): string {
        if (text in this.#texts) {
            let translation = this.#texts[text];
            for (let i = 0; i < args.length; i++)
                translation = translation.replace(`{${i}}`, args[i]);

            return translation;
        }

        return `#${text}#` + (args.length === 0 ? '' : ' (' + args.join(', ') + ')');
    }

    getAll(): {[text: string]: string} {
        return this.#texts;
    }

    add(texts: {[text: string]: string}): void {
        for (let text in texts)
            this.#texts[text] = texts[text];
    }

}
const eTexts = new eTexts_Class();
export default eTexts;