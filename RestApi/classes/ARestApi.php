<?php namespace EC\RestApi;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class ARestApi {
    private $site = null;
    private $actions_DELETE = null;
    private $actions_GET = null;
    private $actions_POST = null;
    private $actions_PUT = null;

    public function __construct(SRestApi $site) {
        $this->site = $site;

        $this->actions_DELETE = [];
        $this->actions_GET = [];
        $this->actions_POST = [];
        $this->actions_PUT = [];
    }

    public function getAction_DELETE($actionName) {
        if (!array_key_exists($actionName, $this->actions_DELETE))
            return null;

        return $this->actions_DELETE[$actionName];
    }

    public function getAction_GET($actionName) {
        if (!array_key_exists($actionName, $this->actions_GET))
            return null;

        return $this->actions_GET[$actionName];
    }

    public function getAction_POST($actionName) {
        if (!array_key_exists($actionName, $this->actions_POST))
            return null;

        return $this->actions_POST[$actionName];
    }

    public function getAction_PUT($actionName) {
        if (!array_key_exists($actionName, $this->actions_PUT))
            return null;

        return $this->actions_PUT[$actionName];
    }

    public function getResult($actionName) {
        $action = null;

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'DELETE') {
            $action = $this->getAction_DELETE($actionName);
        } else if ($method === 'GET') {
            $action = $this->getAction_GET($actionName);
        } else if ($method === 'POST') {
            $action = $this->getAction_POST($actionName);
        } else if ($method === 'PUT') {
            $action = $this->getAction_PUT($actionName);
        } else {
            return CResult::Error(500, [
                'error' => 'Unknown request method.',
            ]);
        }

        if ($action === null) {
            return CResult::Error(500, [ 'error' => "Method '{$method}'" .
                    " action `{$actionName}` does not exist." ]);
        }

        $urlArgs = [];
        foreach ($_GET as $argName => $arg)
            $urlArgs[$argName] = $arg;
        $apiArgs = [];
        $putFP = fopen('php://input', 'r');
        $putData = '';
        while($data = fread($putFP, 1024))
            $putData .= $data;
        fclose($putFP);

        $apiArgs = json_decode($putData, true);

        try {
            $result = call_user_func([ $this, $action['fn'] ], $urlArgs, $apiArgs);

            if ($result === null)
                return CResult::Error(500, [ 'error' => 'Result cannot be null.' ]);
        } catch (\Exception $e) {
            if (!EDEBUG) {
                E\Exception::NotifyListeners($e);
                return CResult::Error(500, [ 'error' => INTERNAL_ERROR_MESSAGE ]);
            }

            throw $e;
        }

        return $result;
    }

    protected function action_DELETE($name, $fn, $argInfos = []) {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->actions_DELETE[$name] = [
            'argInfos' => $argInfos,
            'fn' => $fn
        ];
    }

    protected function action_GET($name, $fn, $argInfos = []) {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->actions_GET[$name] = [
            'argInfos' => $argInfos,
            'fn' => $fn
        ];
    }

    protected function action_POST($name, $fn, $argInfos = []) {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->actions_POST[$name] = [
            'argInfos' => $argInfos,
            'fn' => $fn
        ];
    }

    protected function action_PUT($name, $fn, $argInfos = []) {
        if (!method_exists($this, $fn))
            throw new \Exception("Action method `$fn` does not exist.");

        $this->actions_PUT[$name] = [
            'argInfos' => $argInfos,
            'fn' => $fn
        ];
    }

    protected function getSite() {
        return $this->site;
    }
}
