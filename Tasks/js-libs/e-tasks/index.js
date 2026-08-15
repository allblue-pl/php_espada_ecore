import webABApi from "web-ab-api";

export class eTasks_Class {
    constructor() {
        this.timeout = 1000;
    }

    start(apiUri, apiArgs, destroyOnFinish, fn) {
        let task_hash = null;

        let refresh =(api_args) => {
            api_args.task = {
                hash: task_hash,
                destroyOnFinish: destroyOnFinish
            };

            webABApi.json(apiUri, api_args, (result) => {
                let task = null;
                if (result.isSuccess()) {
                    task = result.data.task;
                    task_hash = task.hash;
                }

                if (!fn(task, result))
                    return false;

                setTimeout(() => {
                    refresh(api_args);
                }, this.timeout);
            });
        };
        refresh(apiArgs);
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
const eTasks = new eTasks_Class();
export default eTasks;