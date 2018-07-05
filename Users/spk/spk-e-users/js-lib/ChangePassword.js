'use strict';

const
    eLibs = require('e-libs'),
    spocky = require('spocky'),
    webABApi = require('web-ab-api'),

    eUsers = require('.'),
    $layouts = require('./$layouts')
;

export default class ChangePassword extends spocky.Module
{

    constructor()
    { super();
        eUsers.init();

        this.l = eLibs.createLayout($layouts.ChangePassword);

        this.l.$elems.form.addEventListener('submit', (evt) => {
            evt.preventDefault();
            this.submit();
        });

        let onChange = (evt) => {
            this.clearErrorMessage();
        };
        this.l.$elems.password.addEventListener('input', onChange);
        this.l.$elems.newPassword.addEventListener('input', onChange);
        this.l.$elems.newPassword_Repeat.addEventListener('input', onChange);

        this.clearErrorMessage();

        this.$view = this.l;
    }

    clearErrorMessage()
    {
        this.l.$fields.message = null;
        this.l.$fields.messageType = 'dark';
    }

    setMessage_Error(message)
    {
        this.l.$fields.message = message;
        this.l.$fields.messageType = 'danger';
    }

    setMessage_Success(message)
    {
        this.l.$fields.message = message;
        this.l.$fields.messageType = 'success';
    }

    submit()
    {
        if (this.l.$elems.newPassword.value === '') {
            this.setMessage_Error(eLibs.eText('Users:errors_EmptyNewPassword'));
            return;
        } else if (this.l.$elems.newPassword.value !== this.l.$elems.newPassword_Repeat.value) {
            this.setMessage_Error(eLibs.eText('Users:errors_PasswordsDoNotMatch'));
            return;
        }

        // let uris = eLibs.eFields.get('web').uris;

        webABApi.json(eUsers.eFields.userApiUri + 'change-password', {
            Password: this.l.$elems.password.value,
            NewPassword: this.l.$elems.newPassword.value,
                }, (result) => {
            console.log(result);

            if (result.isSuccess()) {
                this.setMessage_Success(eLibs.eText('Users:successes_PasswordChanged'));
            } else
                this.setMessage_Error(result.message);
        });
    }

}