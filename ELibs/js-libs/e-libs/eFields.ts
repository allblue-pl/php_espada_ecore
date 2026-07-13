
export class eFields_Class {
    #fields: {[fieldName: string]: any};

    constructor() {
        this.#fields = {};
    }

    add(fields: {[fieldName: string]: any}): void {
        for (let fieldName in fields)
            this.set(fieldName, fields[fieldName]);
    }

    get(fieldName: string): any {
        if (!(fieldName in this.#fields)) {
            throw new Error('Field `' + fieldName +
                    '` does not exist.');
        }

        return this.#fields[fieldName];
    }

    get_FromPath(fieldName: string): any {
        let fieldName_Parts = fieldName.split('.');

        if (!this.exists(fieldName_Parts[0]))
            return '#FieldNotSet#';

        let path = fieldName_Parts[0];
        let base = this.get(fieldName_Parts[0]);
        for (let i = 1; i < fieldName_Parts.length; i++) {
            path = path + '.' + fieldName_Parts[i];
            if (!(fieldName_Parts[i] in base))
                return `#FieldPartNotSet(${path})#`;

            base = base[fieldName_Parts[i]];
        }

        return base;
    }

    exists(fieldName: string): boolean {
        return fieldName in this.#fields;
    }

    set(fieldName: string, fieldValue: any): void {
        this.#fields[fieldName] = fieldValue;
    }
}
const eFields = new eFields_Class();
export default eFields;