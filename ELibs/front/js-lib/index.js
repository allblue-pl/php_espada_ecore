'use strict';


export const eFields = require('./eFields');
export const eTexts = require('./eTexts');

export function eField(fieldName) {
    return eFields.get(fieldName);
}

export function eText(text, args = []) {
    return eTexts.get(text, args);
}