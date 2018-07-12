'use strict';

const
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkEFilesUpload = require('spk-e-files-upload'),
    spkForms = require('spk-forms'),
    spkMessages = require('spk-messages'),
    spkTinyMCE = require('spk-tinymce'),
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

        if (!eLibs.eFields.exists('eArticles'))
            throw new Error(`'HArticles::Init' not called.`);

        this.l = eLibs.createLayout($layouts.Form);
        this.f = new spkForms.Form(this.l, 'Form');

        this.editor = new spkTinyMCE.Editor(this.l, this.f.fields.Content_Raw.elem,
                eLibs.eField('eArticles').spkTinyMCEPkgUri);

        this._id = null;

        let insertImage = (file) => {
            this._insertImage(file);
        };
        let insertFile = (file) => {
            this._insertFile(file);
        };

        /* Files Upload */
        this.introUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Intro', 
                insertImage);
        this.l.$holders.introUpload.$view = this.introUpload;

        this.filesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Files');
        this.l.$holders.filesUpload.$view = this.filesUpload;

        this.imagesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Images',
                insertImage);
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


    _insertFile(file)
    {
        this.editor.setHtml(this.editor.getHtml() + '\r\n' + 
                `<img src="${file.uri}" />`);
    }

    _insertImage(file)
    {
        this.editor.setHtml(this.editor.getHtml() + '\r\n' + 
                `<img src="${file.uri}" />`);
    }

}