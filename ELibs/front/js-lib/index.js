'use strict';


class eLibs_Class
{

    get eFields() {
        return require('./eFields');
    }

    get eLang() {
        return this.eFields.get('eLang');
    }

    get eTexts() {
        return require('./eTexts');
    }


    createLayout(layoutClass) {
        let l = new layoutClass();
        l.$fields.eField = (field) => {
            return 'Not implemented yet.';
        };
        l.$fields.eText = (text) => {
            return this.eText(text);
        };

        return l;
    }

    eField(fieldName) {
        return this.eFields.get(fieldName);
    }

    eText(text, args = []) {
        return this.eTexts.get(text, args);
    }

}
export default eLibs = new eLibs_Class();