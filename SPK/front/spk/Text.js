'use strict';

SPK

.Module('eText', [], [null, {
$: {

    _Texts: {},

    add: function(file_path, texts) {
        this._texts[file_path] = texts;
    },

    create: function(prefix) {
        return function(text, args) {
            return this.Get(prefix + '.' + text, args);
        };
    },

    get: function(text, args) {
        text = String(text);

        var separator_index = text.lastIndexOf('.');

        if (separator_index === -1) {
            separator_index = text.lastIndexOf(':');

            if (separator_index === -1)
                return '#' + text + '#';
        }

        var file_path = text.substring(0, separator_index);
        var text_key = text.substring(separator_index + 1);

        if (!(file_path in this._texts))
            return '#' + text + '#';

        if (!(text_key in this._texts[file_path]))
            return '#' + text + '#';

        var t_text = this._texts[file_path][text_key];

        if (typeof args !== 'undefined') {
            for (var i = 0; i < args.length; i++)
                t_text = t_text.replace('{' + i + '}', args[i]);
        }

        return t_text;
    },

    init: function(layout) {
        layout.$fields.eText = function(text) {
            return SPK.$eText.get(text);
        };
    }

}}]);
