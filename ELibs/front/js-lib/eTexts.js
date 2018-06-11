'use strict';


class eTexts_Class
{

    constructor()
    {
        this._texts = {};
    }

    get(text)
    {
        if (text in this._texts)
            return this._texts[text];

        return `#${text}#`;
    }

    add(texts)
    {
        for (let text in texts)
            this._texts[text] = texts[text];
    }

}
module.exports = new eTexts_Class();