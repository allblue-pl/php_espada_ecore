<?php namespace EC\Tasks;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HApi {

    static public function Parse(EC\MDatabase $db, $user_id = null, $task_arg = [],
            &$error = null) {
        if (!self::ValidateArg($task_arg, $error))
            return null;

        if ($task_arg['hash'] !== null) {
            $task = HTasks::Get($db, $task_arg['hash'], $user_id);
            if ($task === null) {
                $error = 'Task does not exist.';
                return null;
            }
        } else {
            $task = HTasks::Create($db, $user_id);
            if ($task === null) {
                $error = 'Cannot create task.';
                return null;
            }
        }

        return $task;
    }

    static public function Update(EC\MDatabase $db, CTask $task, $task_arg) {
        if (!self::ValidateArg($task_arg, $error))
            return null;

        if ($task->isFinished()) {
            if ($task_arg['destroyOnFinish'])
                $task->destroy();
        }

        return $task->update($db);
    }


    static private function ValidateArg($task_arg, &$error) {
        if (!is_array($task_arg)) {
            $error = 'Arg `task`must be an object.';
            return false;
        }

        if (!array_key_exists('hash', $task_arg)) {
            $error = 'Task `hash` not set.';
            return false;
        }
        if (!array_key_exists('destroyOnFinish', $task_arg)) {
            $error = 'Task `destroyOnFinish` not set.';
            return false;
        }

        return true;
    }

}
