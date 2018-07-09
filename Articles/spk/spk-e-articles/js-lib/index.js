'use strict';

const
    eLibs = require('e-libs'),
    spkForms = require('spk-forms'),
    spocky = require('spocky'),

    $layouts = require('./$layouts')
;

export class Form extends spocky.Module
{

    constructor()
    { super();
        this.l = eLibs.createLayout($layouts.Form);
        this.f = new spkForms.Form(this.l, 'Form');

        this.$view = this.l;
    }

}