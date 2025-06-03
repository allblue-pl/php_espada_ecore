'use strict';

SPK

.Module('eUsers_ChangePassword', ['eUsers_ChangePassword'],
[function() {
    this.mNotifications = SPK.$abNotifications.create();

    this.eFields = SPK.$eFields.get(this.$name);
    this.$fields.eText = function(text, args) {
        return SPK.$eText.get(text, args);
    };

    this.$fields.set({
        newPassword: {
            class: '',
            error: {
                show: false,
                message: ''
            }
        },
        oldPassword: {
            class:  '',
            error: {
                show: false,
                message: ''
            }
        },

        error: {
            class: '',
            show: false,
            message: ''
        }
    });

    this.createElems();

    this.$view = this.$layout;
}, {

    checkNewPassword: function() {
        if (this.$elems.newPasswordConfirmation.value === '') {
            this.clearNewPasswordError();
            this.$fields.newPassword.set({
                class: ''
            });
        }

        if (this.$elems.newPasswordConfirmation.value ===
                this.$elems.newPassword.value)
            this.clearNewPasswordError();
        else {
            this.$fields.newPassword.set({
                class: 'has-error',
                error: {
                    show: true,
                    message: SPK.$eText.get('Users:ChangePassword' +
                            '_PasswordsDoNotMatch')
                }
            });
        }
    },

    clearNewPasswordError: function() {
        this.$fields.newPassword.set({
            class: '',
            error: {
                show: '',
                message: ''
            }
        });
    },

    clearOldPasswordError: function() {
        this.$fields.oldPassword.set({
            class: '',
            error: {
                show: '',
                message: ''
            }
        });
    },

    createElems: function() {
        var elems = this.$elems;
        var self = this;

        elems.oldPassword.addEventListener('change', function(evt) {
            self.clearOldPasswordError();
        });

        elems.newPassword.addEventListener('change', function(evt) {
            if (elems.newPasswordConfirmation.value !== '')
                self.checkNewPassword();
        });

        elems.newPasswordConfirmation.addEventListener('change',
                function(evt) {
            self.checkNewPassword();
        });

        elems.form.addEventListener('submit', function(evt) {
            evt.preventDefault();

            self.changePassword();
        })
    },

    changePassword: function() {
        if (this.$elems.newPassword.value !==
                this.$elems.newPasswordConfirmation.value)
            return;

        this.mNotifications.startLoading(
                SPK.$eText.get('Users:ChangePassword_Loading'));

        var fields = {
            oldPassword: this.$elems.oldPassword.value,
            newPassword: this.$elems.newPassword.value
        }

        var self = this;
        SPK.$abApi.json(this.eFields.apiUris.changePassword, fields,
                function(result) {
            console.log(result);

            if (result.isSuccess()) {
                self.$elems.oldPassword.value = '';
                self.$elems.newPassword.value = '';
                self.$elems.newPasswordConfirmation.value = '';

                self.$fields.set({
                    error: {
                        class: 'has-success',
                        show: true,
                        message: SPK.$eText.get('Users:ChangePassword_Succeeded')
                    }
                });

                self.mNotifications.finishLoading();
            } else if (result.isFailure()) {
                if (!('error' in result.data)) {
                    console.warn(result);

                    self.$fields.error.set({
                        show: true,
                        message: SPK.$eText.get('Users:ChangePassword_Failed')
                    });
                } else if (result.data.error.type == 'wrongPassword') {
                    self.$fields.oldPassword.set({
                        error: {
                            class: 'has-error',
                            show: true,
                            message: SPK.$eText.get('Users:ChangePassword' +
                                    '_WrongPassword')
                        }
                    });
                } else if (result.data.error.type ='wrongPasswordFormat') {
                    self.$fields.error.set({
                        show: true,
                        message: SPK.$eText.get('Users:ChangePassword' +
                                '_WrongPasswordFormat')
                    });
                } else {
                    console.warn(result);

                    self.$fields.error.set({
                        show: true,
                        message: SPK.$eText.get('Users:ChangePassword_Failed')
                    });
                }

                self.mNotifications.finishLoading();
            } else {
                console.warn(result);

                self.$fields.error.set({
                    show: true,
                    message: SPK.$eText.get('Users:ChangePassword_Failed')
                });

                self.mNotifications.finishLoading();
            }
        });
    }

}]);
