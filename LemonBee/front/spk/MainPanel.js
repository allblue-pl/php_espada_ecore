'use strict';

SPK.Module('eLemonBee_MainPanel', ['{{name}}'], [
function() {
    this.$fields.eText = function (text, args) {
        return SPK.$eText.get(text, args);
    };

    this.$fields.eFields.set(SPK.$eFields.get('eLemonBee_MainPanel'));

    var panels = this.$fields.eFields.panels;

    for (var i in panels) {
        var subpanel_class = 'col-sm-' + (12 / panels[i].menu.length);
        for (var j in panels[i].menu) {
            var menu_item = panels[i].menu[j];
            menu_item.class = subpanel_class;
            menu_item.uri = this.$app.parseUri(menu_item.alias, menu_item.args);
        }
    }

    this.$view = this.$layout;
}]);
