
'use strict';

const
    abCookies = require('ab-cookies'),
    js0 = require('js0')
;

class eCookiesPolicy_Class {

    constructor() {
        this._listeners_OnClose = [];
    }

    addListener_OnClose(listenerFn) {
        js0.args(arguments, 'function');

        this._listeners_OnClose.push(listenerFn);
    }

    init() {
        if (abCookies.get('eCookiesPolicy_Displayed') || 
                abCookies.get('eCookiesPolicy_Accepted')) {
            for (let listenerFn of this._listeners_OnClose)
                listenerFn();

            return;
        }
    
        let modal = new bootstrap.Modal(document.getElementById('ECookiesPolicy_Modal'));
    
        document.getElementById('eCookiesPolicy_Close').addEventListener('click', (evt) => {
            evt.preventDefault();

            abCookies.set('eCookiesPolicy_Displayed', true);
            modal.hide();

            for (let listenerFn of this._listeners_OnClose)
                listenerFn();

            close();
        });
    
        document.getElementById('eCookiesPolicy_Agree').addEventListener('click', (evt) => {
            evt.preventDefault();

            abCookies.set('eCookiesPolicy_Accepted', true, {
                expires: 30 * 24 * 60 * 60,
            });
            modal.hide();

            for (let listenerFn of this._listeners_OnClose)
                listenerFn();

            close();
        });
        
        modal.show();
    }

}
export default eCookiesPolicy = new eCookiesPolicy_Class();