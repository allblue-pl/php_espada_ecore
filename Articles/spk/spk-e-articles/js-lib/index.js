'use strict';

const
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkEFilesUpload = require('spk-e-files-upload'),
    spkForms = require('spk-forms'),
    spkMessages = require('spk-messages'),
    spocky = require('spocky'),

    $layouts = require('./$layouts')
;

export class Form extends spocky.Module
{

    get id() {
        if (this._id === null)
            throw new Error(`Article 'id' not set.`);

        return this._id;
    }
    set id(value) {
        this._id = value;

        this.introUpload.id = value;
        this.filesUpload.id = value;
        this.imagesUpload.id = value;
        this.galleryUpload.id = value;
    }

    constructor(msgs)
    { super();
        js0.args(arguments, spkMessages.Messages);

        this.l = eLibs.createLayout($layouts.Form);
        this.f = new spkForms.Form(this.l, 'Form');

        this._id = null;

        /* Files Upload */
        this.introUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Intro');
        this.l.$holders.introUpload.$view = this.introUpload;

        this.filesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Files');
        this.l.$holders.filesUpload.$view = this.filesUpload;

        this.imagesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Images');
        this.l.$holders.imagesUpload.$view = this.imagesUpload;

        this.galleryUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Gallery');
        this.l.$holders.galleryUpload.$view = this.galleryUpload;

        this.$view = this.l;
    }

    refresh()
    {
        this.introUpload.refresh();
        this.filesUpload.refresh();
        this.imagesUpload.refresh();
        this.galleryUpload.refresh();
    }

}