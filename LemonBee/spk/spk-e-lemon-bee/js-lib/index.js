'use strict';

const
    abDate = require('ab-date'),
    abPager = require('ab-pager'),
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkForms = require('spk-forms'),
    spkLemonBee = require('spk-lemon-bee'),
    spkMessages = require('spk-messages'),
    spkTables = require('spk-tables'),
    spocky = require('spocky'),
    webABApi = require('web-ab-api'),

    $layouts = require('./$layouts')
;

spocky.ext(new spkForms.Ext());

export class Site extends spocky.Module {

    constructor() {
        super();

        this.l = new $layouts.Main();

        this.msgs = new spkMessages.Messages();
        this.l.$holders.msgs.$view = this.msgs;

        let lbSetup = eLibs.eFields.get('lbSetup');
        let base = '/';
        if ('uris' in lbSetup) {
        if ('base' in lbSetup.uris)
                base = lbSetup.uris.base;
        }

        let pager = new abPager.Pager(base);
        let lb = new spkLemonBee.System(pager, this.msgs);

        lb.setup({
            actions: {
                changePassword_Async: async(oldPassword, newPassword) => {
                    let result = await webABApi.json_Async(
                            lbSetup.uris['userApi'] + 'change-password', 
                            { Password: oldPassword, NewPassword: newPassword });

                    if (result.isSuccess()) {
                        return {
                            success: true,
                            message: result.data.message,
                        }
                    } else {
                        return {
                            success: false,
                            message: result.data.message,
                        };
                    }
                },
                logIn_Async: async (login, password) => {
                    let result = await webABApi.json_Async(
                            lbSetup.uris['userApi'] + 'log-in', 
                            { login: login, password: password });

                    return {
                        user: result.isSuccess() ? {
                            loggedIn: result.data.user.login !== null,
                            login: result.data.user.login === null ? 
                                    '' : result.data.user.login,
                            permissions: result.data.user.permissions,
                        } : null,
                        error: result.isSuccess() ? null : result.data.message,
                    };
                },
                logOut_Async: async () => {
                    let result = await webABApi.json_Async(
                            lbSetup.uris['userApi'] + 'log-out', {});

                    if (result.isSuccess()) {
                        return {
                            success: true,
                            error: null,
                        };
                    }

                    return {
                        success: false,
                        error: result.data.message,
                    };
                },
            },

            aliases: {
                account: 'account',
                main: '',
                logIn: 'log-in',
            },
            images: lbSetup.images,
            panels: this.createPanels(),

            textFn: (text) => {
                return eLibs.eText('LemonBee:' + text);
            },
            uris: {
                package: '',
            },
<<<<<<< Updated upstream

            settings: {
                hasRemindPassword: false,
            }
=======
>>>>>>> Stashed changes
        });

        lb.setUser(lbSetup.user);

        lb.init();
        this.pager.init();

        this.l.$holders.content.$view = lb.module;

<<<<<<< Updated upstream
=======
        this.l.$holders.content.$view = lb.module;

>>>>>>> Stashed changes
        this.$view = this.l;
    }

    createPanels()
    {
        return [];
    }

}

export function init(modulePath, debug)
{
    spocky.setDebug(debug);
    spkForms.setDebug(debug);
    webABApi.setDebug(debug);

    spkForms.setLang(eLibs.eLang.code.substring(0, 2));
    spkMessages.setTextFn((text) => {
        return eLibs.eText('LemonBee:SPKMessages_' + text);
    });

    let spk = new spocky.App()
        .config(($app, $cfg) => {
            $cfg.container('site', require(modulePath).Site);
        });

    spk.init(debug); 
}

