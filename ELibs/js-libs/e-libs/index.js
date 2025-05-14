'use strict';


class eLibs_Class {

    get eField() {
        return (fieldName) => {
            return this.eFields.get(fieldName);
        }
    }

    get eFields() {
        return require('./eFields');
    }

    get eLang() {
        return this.eFields.get('eLang');
    }

    get eText() {
        return (text, args = []) => {
            return this.eTexts.get(text, args);
        }
    }

    get eTexts() {
        return require('./eTexts');
    }


    createLayout(layoutClass) {
        let l = new layoutClass();
        l.$fields.eField = (fieldName) => {
            return this._getField(fieldName);
        };
        l.$fields.eText = (text) => {
            return this.eText(text);
        };

        return l;
    }


    _getField(fieldName)
    {
        let fieldName_Parts = fieldName.split('.');

        if (!this.eFields.exists(fieldName_Parts[0]))
            return '#FieldNotSet#';

        let path = fieldName_Parts[0];
        let base = this.eFields.get(fieldName_Parts[0]);
        for (let i = 1; i < fieldName_Parts.length; i++) {
            path = path + '.' + fieldName_Parts[i];
            if (!(fieldName_Parts[i] in base))
                return `#FieldPartNotSet(${path})#`;

            base = base[fieldName_Parts[i]];
        }

        return base;
    }

}
export default eLibs = new eLibs_Class();