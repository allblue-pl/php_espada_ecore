'use strict';


class eFields_Class
{

    constructor()
    {
        this._fields = {};
    }

    add(fields)
    {
        for (let fieldName in fields)
            this.set(fieldName, fields[fieldName]);
    }

    get(fieldsName)
    {
        if (!(fieldsName in this._fields)) {
            throw new Error('Fields `' + fieldsName +
                    '` does not exist.');
        }

        return this._fields[fieldsName];
    }

    set(fieldsName, fields)
    {
        this._fields[fieldsName] = fields;
    }

}
module.exports = new eFields_Class();