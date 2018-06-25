'use strict';

const
    spocky = require('spocky')
;

export default class Edit extends spocky.Layout {

    static get Content() {
        return [["div",{"class":["row"]},["div",{"class":["col-sm-8"]},["h1",{"class":["panel-title"]},"$eText('Adm:titles_Article')"]],["div",{"class":["col-sm-4 text-right"]},["a",{"_elem":["save"],"href":[],"class":["btn btn-primary"]},["i",{"class":["fa fa-save i-left"]}],"$texts.buttons_Edit"]]],["spk-form-field",{"form":["Article"],"name":["Title"],"type":["Input"],"input-type":["text"],"label":["$eText('Articles:labels_Title')"],"placeholder":["$eText('Articles:labels_Title')","..."]}],["spk-form-field",{"form":["Article"],"name":["Publish"],"type":["DateTime"],"label":["$eText('Articles:labels_Publish')"],"placeholder":["$eText('Articles:labels_Publish')","..."]}]];
    }


    constructor()
    {
        super(Edit.Content);
    }

}
