'use strict';

SPK

.Module('eTask', [], [null, {
$: {

    timeout: 1000,

    start: function(api_uri, api_args, destroy_on_finish, fn)
    { var $this = this;
        var task_hash = null;

        var refresh = function(api_args) {
            api_args.task = {
                hash: task_hash,
                destroyOnFinish: destroy_on_finish
            };

            SPK.$abApi.json(api_uri, api_args, function(result) {
                var task = null;
                if (result.isSuccess()) {
                    task = result.data.task;
                    task_hash = task.hash;
                }

                if (!fn(task, result))
                    return false;

                setTimeout(function() {
                    refresh({});
                });
            });
        };
        refresh(api_args);
    },

    refresh: function(api_uri, task_hash, destroy_on_finish, fn)
    { var $this = this;

        var check_task = function() {
            SPK.$abApi.json(api_uri, {
                task: {
                    hash: task_hash,
                    destroyOnFinish: destroy_on_finish
                }
            }, function(result) {
                if (result.isSuccess()) {
                    if (fn(result.data.task.finished, result.data.task.info, null))
                        setTimeout(check_task, $this.timeout);
                } else {
                    console.warn('Error on task refresh.');
                    setTimeout(check_task, $this.timeout);
                }
            });
        };

        setTimeout(check_task, $this.timeout);
    }

}}]);
