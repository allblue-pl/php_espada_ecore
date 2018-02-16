<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class AApi
{

    private $site = null;

    public function __construct(EC\SApi $site)
    {
        $this->site = $site;
    }

    public function getResult($action_name, $args)
    {
        if (!isset($this->actions[$action_name])) {
            return EC\Api\CResult::Failure("Action `{$action_name}`" .
                    ' does not exist.');
        }

        $action = $this->actions[$action_name];

        if (EDEBUG)
            $action['argInfos']['_test'] = false;

        $api_args = new CArgs($action['argInfos']);

        foreach ($action['argInfos'] as $arg_name => $required) {
            if (array_key_exists($arg_name, $args))
                $api_args->$arg_name = $args[$arg_name];
            else if ($required)
                return EC\Api\CResult::Failure("`{$arg_name}` not set.");
        }

        try {
            return call_user_func([ $this, $action['fn'] ], $api_args);
        } catch (\Exception $e) {
            if (!EDEBUG) {
                E\Exception::NotifyListeners($e);
                return CResult::Error(INTERNAL_ERROR_MESSAGE);
            }

            throw $e;
        }
    }

    protected function action($name, $fn, $arg_infos = [])
    {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->actions[$name] = [
            'argInfos' => $arg_infos,
            'fn' => $fn
        ];
    }

    protected function getSite()
    {
        return $this->site;
    }

}
