'use strict';

const
    abDate = require('ab-date'),
    abPager = require('ab-pager'),
    eLibs = require('e-libs'),
    js0 = require('js0'),
    spkForms = require('spk-forms'),
    spkLemonBee = require('spk-lemon-bee'),
    spocky = require('spocky'),
    webABApi = require('web-ab-api')
;

spocky.ext(new spkForms.Ext());


const spk = new spocky.App()
    .config(($app, $cfg) => {
        $cfg.container('site', Site);
    });

class Site extends spocky.Module {

    constructor() {
        super();

        let lbSetup = eLibs.eFields.get('lbSetup');
        let base = '/';
        if ('uris' in lbSetup) {
        if ('base' in lbSetup.uris)
                base = lbSetup.uris.base;
        }

        let pager = new abPager.Pager(base);
        let lb = new spkLemonBee.System(pager);

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
                            { Login: login, Password: password });

                    return {
                        user: {
                            loggedIn: result.data.user.login !== null,
                            login: result.data.user.login === null ? 
                                    '' : result.data.user.login,
                            permissions: result.data.user.permissions,
                        },
                        error: result.isSuccess() ? null : result.data.message,
                    };
                },
                logOut_Async: async () => {
                    let result = await webABApi.json_Async(
                            lbSetup.uris['userApi'] + 'log-out', {});

                    if (result.success) {
                        // app.setUser(null);
                        // this.lb.setPanels([]);
                    }
                },
            },

            aliases: {
                account: 'account',
                main: '',
                logIn: 'log-in',
            },
            images: lbSetup['images'],
            panels: [],

            textFn: (text) => {
                return eLibs.eText('LemonBee:' + text);
            },
            uris: {
                package: '',
            }
        });

        lb.setUser(lbSetup.user);

        lb.init();
        pager.init();

        this.$view = lb.module;
    }

}

export function init(debug)
{
    spocky.setDebug(debug);
    spkForms.setDebug(debug);
    webABApi.setDebug(debug);

    spkForms.setLang(eLibs.eLang.code.substring(0, 2));

    spk.init(debug); 
}

