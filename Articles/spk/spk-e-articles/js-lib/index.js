'use strict';

const
    eLibs = require('e-libs'),
    spkForms = require('spk-forms'),
    spocky = require('spocky'),

    $layouts = require('./$layouts')
;

export class Edit extends spocky.Module
{

    constructor()
    { super();
        this.l = eLibs.createLayout($layouts.Edit);
        this.f = new spkForms.Form(this.l, 'Article');

        this.$view = this.l;
    }

}