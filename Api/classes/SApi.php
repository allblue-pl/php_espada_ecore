<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class SApi extends E\Site
{

    private $apiName = '';
    private $actionName = '';

    private $api = null;

    public function __construct()
    {
        parent::__construct();

        $this->setRootL(E\Layout::_('Basic:raw'));

        $this->parseArgs(E\Args::Uri('_extra'));
    }

    public function api(AApi $api)
    {
        $this->api = $api;
    }

    protected function _initialize()
    {
        parent::_initialize();

        $result = $this->getResult();

        $result_json = $result->getJSON();

        $this->getRootL()->setFields([
            'raw' => $result->getJSON()
        ]);
    }

    private function parseArgs($args)
    {
        if (count($args) >= 1)
            $this->actionName = $args[0];
    }

    private function getResult()
    {
        if ($this->actionName === '') {
            $uri = E\Uri::Current();
            return CResult::Failure("Api `action` not set: `{$uri}`.");
        }

        if ($this->api === null)
            return CResult::Failure('Unknown `api`.');

        if (!E\Args::Post_ValidateSize()) {
            return CResult::Failure('File size too big.')
                ->add('errorMessage', EC\HText::_('Api:Errors_PostSizeExceeded'));
        }

        $post_args = E\Args::Post_All();
        if (array_key_exists('json', $post_args)) {
            $api_args = json_decode($post_args['json'], true);
            if ($api_args === null)
                return CResult::Failure('Cannot parse json.');
        } else
            $api_args = [];

        foreach ($post_args as $post_arg_name => $post_arg_value) {
            if ($post_arg_name === 'json')
                continue;

            // foreach ($post_args as $post_arg_name => $post_arg_value) {
                if (array_key_exists($post_arg_name, $api_args)) {
                    return CResult::Failure("Arg `{$post_arg_name}` already " .
                            ' set in `json`.');
                }
                $api_args[$post_arg_name] = $post_arg_value;
            // }
        }

        /* Debug with GET. */
        if (EDEBUG) {
            if (E\Args::Get_Exists('_debug')) {
                $get_args = E\Args::Get_All();
                foreach ($get_args as $get_arg_name => $get_arg_value)
                    $api_args[$get_arg_name] = $get_arg_value;
            }
        }

        $result = $this->api->getResult($this->actionName, $api_args);

        if ($result === null)
            return CResult::Failure('Result cannot be null.');

        $notices = E\Notice::GetAll();
        foreach ($notices as $notice)
            $result->debug('Notice: ' . $notice['message'] . implode("; ", $notice['stack']));

        return $result;
    }

}
