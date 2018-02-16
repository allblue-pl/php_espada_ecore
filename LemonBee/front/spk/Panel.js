
'use strict';

SPK.Module('eLemonBee_Panel', ['{{name}}'], [
function() {
    this.$public = this;

    this.$fields.eText = function(text, args) {
        return SPK.$eText.get(text, args);
    };

    var e_fields = SPK.$eFields.get(this.$name);
    this.mNotifications = SPK.$abNotifications.create();

    this.$holders.notifications.$view = this.mNotifications;

    var self = this;
    this.$app.onPageChange(function(page) {
        if (!(page.name in e_fields.views)) {
            var view = e_fields.views[e_fields.defaultViewName];
            self.$app.setPage(view.name, view.args);
        }

        var view = e_fields.views[page.name];

        self.$fields.title = view.title;
        self.$fields.faIcon = view.faIcon;
        self.$fields.image = view.image;

        if (!('$' + view.moduleName in SPK))
            console.warn('Module `' + view.moduleName + '` does not exist');
        else {
            var module = SPK['$' + view.moduleName].create(self);
            self.$holders.content.$view = module;
        }
    });

    this.$layout.$elems.back.addEventListener('click', function(evt) {
        evt.preventDefault();
        self.onBack();
    });

    this.$view = this.$layout;
}, {

    onBack: function()
    {
        window.history.back();
    }

}]);
