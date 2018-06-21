'use strict';

const
    abPager = require('ab-pager'),
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkForms = require('spk-forms'),
    spkLemonBee = require('spk-lemon-bee'),
    spocky = require('spocky'),
    webABApi = require('web-ab-api')
;

spocky.ext(new spkForms.Ext());


const spk = new spocky.Site()
    .config(($app, $cfg) => {
        $cfg.container('site', Site);
    });

class Site extends spocky.Module {

    constructor() {
        super();

        let lbSetup = eLibs.eFields.get('lbSetup');
        let base = '/';
        if ('uris' in lbSetup) {
            if ('base' in lbSetup.uris)
                base = lbSetup.uris.base;
        }

        let pager = new abPager.Pager(base);
        let lb = new spkLemonBee.System(pager);

        lb.setup(lbSetup);

        lb.init();
        pager.init();

        this.$view = lb.module;
    }

}

export function init(debug)
{
    spocky.setDebug(debug);
    spkForms.setDebug(debug);
    webABApi.setDebug(debug);

    spk.init(debug); 
}

