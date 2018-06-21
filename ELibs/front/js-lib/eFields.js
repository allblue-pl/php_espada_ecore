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

    get(fieldName)
    {
        if (!(fieldName in this._fields)) {
            throw new Error('Field `' + fieldName +
                    '` does not exist.');
        }

        return this._fields[fieldName];
    }

    exists(fieldName)
    {
        return fieldName in this._fields;
    }

    set(fieldName, fieldValue)
    {
        this._fields[fieldName] = fieldValue;
    }

}
module.exports = new eFields_Class();