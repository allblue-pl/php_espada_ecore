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

export class Form extends spocky.Module {

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

    get content() {
        return this.editor.getHtml();
    }

    get mediaId() {
        if (this._mediaId === null)
            throw new Error(`Article 'id' not set.`);

        return this._mediaId;
    }
    set mediaId(value) {
        this._mediaId = value;

        this.introUpload.id = value;
        this.filesUpload.id = value;
        this.imagesUpload.id = value;
        this.galleryUpload.id = value;

        this.refreshMedia();
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
        this._mediaId = null;

        let insertImage = (file) => {
            this.insertImage(file);
        };
        let insertFile = (file) => {
            this.insertFile(file);
        };

        /* Files Upload */
        this.introUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Intro', 
                eLibs.eText('Adm:filesUpload_Intro'), insertImage);
        this.l.$holders.introUpload.$view = this.introUpload;

        this.filesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Files',
                eLibs.eText('Adm:filesUpload_Files'), insertFile);
        this.l.$holders.filesUpload.$view = this.filesUpload;

        this.imagesUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Images',
                eLibs.eText('Adm:filesUpload_Images'), insertImage);
        this.l.$holders.imagesUpload.$view = this.imagesUpload;

        this.galleryUpload = new spkEFilesUpload.FilesUpload(msgs, 'eArticles_Gallery',
                eLibs.eText('Adm:filesUpload_Gallery'), insertImage);
        this.l.$holders.galleryUpload.$view = this.galleryUpload;

        this.$view = this.l;
    }

    getValues() {
        let fields = this.f.getValues();
        fields.Content_Raw = this.content;

        return fields;
    }

    insertFile(file)
    {
        this.editor.insertHtml_AtCursor(`<ul><li><a href="${file.uri}">${file.id}</a></li></ul>`);
    }null

    insertImage(file)
    {
        this.editor.insertHtml_AtCursor(`<img src="${file.uri}" />`);
    }

    refreshMedia()
    {
        if (this._mediaId === null)
            throw new Error('Media id not set.');

        this.introUpload.refresh();
        this.filesUpload.refresh();
        this.imagesUpload.refresh();
        this.galleryUpload.refresh();
    }

    setValues(values, ignoreNotExisting = false)
    {
        this.f.setValues(values, ignoreNotExisting);
        if ('Content_Raw' in values)
            this.editor.setHtml(values.Content_Raw);
    }

}