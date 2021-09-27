'use strict';

const
    abStrings = require('ab-strings'),
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkFileUpload = require('spk-file-upload'),
    spocky = require('spocky'),
    webABApi = require('web-ab-api')
;

export class FilesUpload extends spocky.Module
{

    get id() {
        if (this._id === null)
            throw new Error(`'id' not set.`);

        return this._id;
    }
    set id(value) {
        this._id = value;
    }


    constructor(msgs, categoryName, title, onInsertFn = null, fileExts = '*')
    { super();
        js0.args(arguments, require('spk-messages').Messages, 'string', 'string',
                [ 'function', js0.Null, js0.Default ], [ 'string', js0.Default ]);

        this._id = null;
        this._onInsertFn = onInsertFn;

        if (!eLibs.eFields.exists('eFilesUpload'))
            throw new Error('FilesUpload not initialized.');

        this.eFields = eLibs.eFields.get('eFilesUpload');

        if (!(categoryName in this.eFields.categories))
            throw new Error(`Category '${categoryName}' does not exist.`);

        this.categoryName = categoryName;
        this.category = this.eFields.categories[categoryName];

        this.msgs = msgs;
        this.apiUri = this.eFields.apiUri;

        this._liveUpload = new spkFileUpload.LiveUpload(title, this.category.type, {
            onCopy: (file) => {
                navigator.clipboard.writeText(file.uri).then(() => {
                    this.msgs.showMessage_Success(
                            eLibs.eText('FilesUpload:Texts_Copied'));
                });
            },
            onDelete: (file) => {
                this._files_Delete(file);
            },
            onInsert: (file) => {
                if (this._onInsertFn !== null)
                    this._onInsertFn(file);
            },
            onUpload: (files) => {
                this._files_Upload(files);
            },
                }, this.category.type === 'image' ? 
                '.jpg, .jpeg, .png, .gif' : fileExts, eLibs.eFields.get('eFilesUpload').texts);
        this._liveUpload.showLoading();

        this.$view = this._liveUpload;
    }

    refresh()
    {
        this._liveUpload.showLoading();
        this._liveUpload.deleteAllFiles();
        webABApi.json(this.apiUri + 'list', { 
            categoryName: this.categoryName,                     
            id: this.id,
                }, (result) => {
            this._liveUpload.hideLoading();

            if (result.isSuccess()) {
                let fileInfos = result.data.files;

                for (let fileInfo of fileInfos) {
                    this._liveUpload.setFile({
                        id: this._getFileId(fileInfo.fileName),
                        title: fileInfo.fileName,
                        uri: fileInfo.uri,
                        imgUri: this.category.type === 'image' && fileInfo.uri !== null ? 
                                fileInfo.uri : eLibs.eField('eFilesUpload').uris.file,
                    });
                }
                // this._liveUpload.setFile({
                //     id: fileId,
                //     title: fileId,
                //     uri: result.data.uri,
                // });
            } else
                this.msgs.showMessage_Failure(result.messsage);
        });
    }


    _escapeFileName(fileName)
    {
        js0.args(arguments, 'string');

        fileName = fileName.toLowerCase();
        fileName = abStrings.escapeLangChars(fileName);
        fileName = fileName.replace(/ /g, '-', fileName);
        fileName = abStrings.escapeToAllowedChars(fileName, 'a-zA-Z0-9._-');
        fileName = abStrings.removeDoubles(fileName, '-');

        return fileName;
    }

    _files_Delete(file)
    {
        this._liveUpload.deleteFile(file.id);

        webABApi.json(this.apiUri + 'delete', { 
            categoryName: this.categoryName,                     
            id: this.id,
            fileName: this.category['multiple'] ? file.id : null,
                }, (result) => {
            if (result.isSuccess()) {
                
            } else {
                this._liveUpload.setFile({
                    id: file.id,
                    title: file.title,
                    uri: file.uri,
                    imgUri: this.category.type === 'image' ? 
                            file.uri : eLibs.eField('eFilesUpload').uris.file,
                });

                this.msgs.showMessage_Failure(result.message);
            }
        });
    }

    _files_Upload(files)
    {
        let files_Valid = [];
        let fileNames_Invalid = [];
        for (var i = 0; i < files.length; i++) {
            var file = files[i];

            if (this.category.type === 'image') {
                if (file.type.match(/image.*/))
                    files_Valid.push(file);
                else
                    fileNames_Invalid.push(file.name);
            } else
                files_Valid.push(file);
        }

        if (fileNames_Invalid.length > 0) {
            this.msgs.showMessage_Failure(eLibs.eTexts.get(
                    'FilesUpload:errors_WrongImageFormat', 
                    [ fileNames_Invalid.join(', ') ]));
        }
        
        for (let file of files_Valid) {
            this._liveUpload.setFile({
                id: this._getFileId(file.name),
                title: this._escapeFileName(file.name),
                uri: '',
                imgUri: eLibs.eField('eFilesUpload').uris.loading,
            });

            webABApi.upload(`${this.apiUri}upload`, { 
                    categoryName: this.categoryName,                     
                    id: this.id,
                    fileName: this._escapeFileName(file.name), 
                    }, { file: file }, (result) => {
                if (result.isSuccess()) {
                    let fileId = this._getFileId(result.data.fileInfo.fileName);
                    if (fileId !== this._getFileId(file.name))
                        this._liveUpload.deleteFile(this._getFileId(file.name));

                    this._liveUpload.setFile({
                        id: this._getFileId(fileId),
                        title: this._escapeFileName(file.name),
                        uri: result.data.fileInfo.uri,
                        imgUri: this.category.type === 'image' && 
                                result.data.fileInfo.uri !== null ? 
                                result.data.fileInfo.uri : 
                                eLibs.eField('eFilesUpload').uris.file,
                    });
                } else {
                    this._liveUpload.deleteFile(this._getFileId(file.name));

                    this.msgs.showMessage_Failure(result.message);
                }
            });
        }
    }

    _getFileId(fileBaseName)
    {
        return this.category.multiple ? this._escapeFileName(fileBaseName) : 0;
    }

}