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

        let pager = new abPager.Pager();
        let lb = new spkLemonBee.System(pager);
        
        let lbSetup = eLibs.eFields.get('lbSetup');

        pager.base(lbSetup.uris.base);

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

