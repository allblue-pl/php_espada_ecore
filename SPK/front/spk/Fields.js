'use strict';

SPK

.Module('eFields', [], [null, {
$: {

    _fields: {},

    get: function(fields_name)
    {
        if (!(fields_name in this._fields)) {
            throw new Error('Fields `' + fields_name +
                    '` does not exist.');
        }

        return this._fields[fields_name];
    },

    set: function(fields_name, fields)
    {
        this._fields[fields_name] = fields;
    }

}}]);
