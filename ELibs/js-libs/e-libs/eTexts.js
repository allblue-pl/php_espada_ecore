'use strict';

const
    js0 = require('js0')
;

class eTexts_Class
{

    constructor()
    {
        this._texts = {};
    }

    get(text, args = [])
    {
        js0.args(arguments, 'string', [ Array, js0.Default ]);

        if (text in this._texts) {
            let translation = this._texts[text];
            for (let i = 0; i < args.length; i++)
                translation = translation.replace(`{${i}}`, args[i]);

            return translation;
        }

        return `#${text}#` + (args.length === 0 ? '' : ' (' + args.join(', ') + ')');
    }

    getAll()
    {
        return this._texts;
    }

    add(texts)
    {
        for (let text in texts)
            this._texts[text] = texts[text];
    }

}
module.exports = new eTexts_Class();