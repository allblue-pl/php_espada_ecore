
'use strict';

const
    abCookies = require('ab-cookies')
;


export function init()
{
    if (abCookies.get('eCookiesPolicy_Displayed'))
        return;

    let modal = new bootstrap.Modal(document.getElementById('ECookiesPolicy_Modal'));
    
    let close = () => {
        abCookies.set('eCookiesPolicy_Displayed', true);
        modal.hide();
    }

    document.getElementById('eCookiesPolicy_Close').addEventListener('click', (evt) => {
        evt.preventDefault();
        close();
    });

    document.getElementById('eCookiesPolicy_Agree').addEventListener('click', (evt) => {
        evt.preventDefault();
        close();
    });
    
    modal.show();
}