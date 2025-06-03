'use strict';

SPK

.Module('eTables', [], [ null, {
$: {

    _tables: {},

    add: function(name, info) {
        this._tables[name] = info;
    },

    get: function(name) {
        if (!(name in this._tables)) {
            throw new Error('Table `' + name +
                    '` does not exist.');
        }

        return this._tables[name];
    }

}}]);
