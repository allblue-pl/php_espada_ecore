'use strict';

const
    webABApi = require('web-ab-api')
;


class eTasks_Class {

    constructor() {
        this.timeout = 1000;
    }

    start(api_uri, api_args, destroy_on_finish, fn) {
        let task_hash = null;

        let refresh = function(api_args) {
            api_args.task = {
                hash: task_hash,
                destroyOnFinish: destroy_on_finish
            };

            webABApi.json(api_uri, api_args, function(result) {
                let task = null;
                if (result.isSuccess()) {
                    task = result.data.task;
                    task_hash = task.hash;
                }

                if (!fn(task, result))
                    return false;

                setTimeout(function() {
                    refresh(api_args);
                });
            });
        };
        refresh(api_args);
    }

    // refresh(api_uri, task_hash, destroy_on_finish, fn)
    // {
    //     let check_task = function() {
    //         webABApi.json(api_uri, {
    //             task: {
    //                 hash: task_hash,
    //                 destroyOnFinish: destroy_on_finish
    //             }
    //         }, function(result) {
    //             if (result.isSuccess()) {
    //                 if (fn(result.data.task.finished, result.data.task.info, null))
    //                     setTimeout(check_task, this.timeout);
    //             } else {
    //                 console.warn('Error on task refresh.');
    //                 setTimeout(check_task, this.timeout);
    //             }
    //         });
    //     };

    //     setTimeout(check_task, this.timeout);
    // }

}
export default eTasks = new eTasks_Class();