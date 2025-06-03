'use strict';

const
    eLibs = require('e-libs')
;

class eUsers_Class {

    get ChangePassword() {
        return require('./ChangePassword');
    }


    constructor() {
        this.eFields = null;
    }

    init() {
        if (!eLibs.eFields.exists('eUsers'))
            throw new Error(`'HUsers::InitSPK' not called.`);

        this.eFields = eLibs.eFields.get('eUsers');
    }

}
export default eUsers = new eUsers_Class();