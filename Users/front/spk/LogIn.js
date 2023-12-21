
SPK.Module('eUsers_LogIn', ['eUsers_LogIn'],
[function() {
    this.mNotifications = SPK.$abNotifications.create('abNotifications');
    this.$holders.notifications.$view = this.mNotifications;

    this.eFields = SPK.$eFields.get('eUsers_LogIn');

    this.$fields.eText = function(text, args) {
        return SPK.$eText.get(text, args);
    };

    var self = this;

    /* Log In */
    this.$elems.form.addEventListener('submit', function(evt) {
        evt.preventDefault();
        self.logIn();
    });

    /* Clear Error */
    var clear_error = function() {
        self.clearError();
    };

    this.$elems.login.addEventListener('change', clear_error);
    this.$elems.password.addEventListener('change', clear_error);

    this.$view = this.$layout;
}, {

    clearError: function() {
        this.$fields.error.set({
            show: false,
            message: ''
        });
    },

    logIn: function() {
        this.mNotifications.startLoading(
                SPK.$eText.get('Users:LogIn_LogIn_Loading'));

        var fields = {
            login: this.$elems.login.value,
            password: this.$elems.password.value
        };

        var self = this;
        SPK.$abApi.json(this.eFields.uris.api, fields, function(result) {
            if (result.isSuccess())
                window.location = self.eFields.uris.redirect;
            else if (result.isFailure()) {
                self.$fields.error.set({
                    show: true,
                    message: SPK.$eText.get('Users:LogIn_LogIn_Failed')
                });
                self.mNotifications.finishLoading();
            } else {
                self.$fields.error.set({
                    show: true,
                    message: SPK.$eText.get('Users:LogIn_LogIn_Error')
                });
                self.mNotifications.finishLoading();
            }
        });
    }

}]);
