'use strict';


export const eFields = require('./eFields');
export const eTexts = require('./eTexts');

export function createLayout(layoutClass) {
    let l = new layoutClass();
    l.$fields.eField = (field) => {
        return 'Not implemented yet.';
    };
    l.$fields.eText = (text) => {
        return this.eText(text);
    };

    return l;
}

export function eField(fieldName) {
    return eFields.get(fieldName);
}

export function eText(text, args = []) {
    return eTexts.get(text, args);
}