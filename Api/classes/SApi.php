<?php namespace EC\Api;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class SApi extends E\Site {

    private $actionName = '';

    private $api = null;

    public function __construct()
    {
        parent::__construct();

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

        if ($result instanceof CResult) {
            // header('ContentType: text/json');
            $raw = $result->getJSON();
            if ($result->isCompressed()) {
                $raw = gzencode($raw);
                header('Content-Encoding: gzip');
            }
            
            $this->setRootL(E\Layout::_('Basic:raw', [
                'raw' => $raw,
            ]));
        } else if ($result instanceof CResult_Bytes) {
            echo "ABApi" . "\r\n";

            echo (int)$result->getResult() . "\r\n";

            $message = $result->getMessage();
            echo mb_strlen($message) . "\r\n";
            echo $message . "\r\n";

            if (ob_get_contents())
			    ob_clean();
		    flush();

            foreach ($result->getBytes() as $byteInfo) {
                echo $byteInfo['name'] . "\r\n";
                if (!is_string($byteInfo['bytes'])) {
                    throw new \Exception("Bytes must be string. Found: {$byteInfo['name']} ->" . 
                            print_r($byteInfo['bytes'], true));
                }

                echo mb_strlen($byteInfo['bytes']) . "\r\n";
                echo $byteInfo['bytes'] . "\r\n";

                flush();
            }

            $this->setRootL(E\Layout::_('Basic:raw', [
                'raw' => '',
            ]));
        }
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
            $apiArgs = json_decode($post_args['json'], true);
            if ($apiArgs === null)
                return CResult::Failure('Cannot decode json arg.');
        } else
            $apiArgs = [];

        foreach ($post_args as $post_arg_name => $post_arg_value) {
            if ($post_arg_name === 'json')
                continue;

            // foreach ($post_args as $post_arg_name => $post_arg_value) {
                if (array_key_exists($post_arg_name, $apiArgs)) {
                    return CResult::Failure("Arg `{$post_arg_name}` already " .
                            ' set in `json`.');
                }
                $apiArgs[$post_arg_name] = $post_arg_value;
            // }
        }

        /* Debug with GET. */
        $apiArgs['_debug'] = false;
        $apiArgs['_test'] = false;
        if (EDEBUG) {
            if (E\Args::Get_Exists('_debug')) {
               $getArgs = E\Args::Get_All();
                foreach ($getArgs as $getArgName => $getArgValue)
                    $apiArgs[$getArgName] = $getArgValue;
            }
        }

        $result = $this->api->getResult($this->actionName, $apiArgs);

        if ($result === null)
            return CResult::Failure('Result cannot be null.');

        $notices = E\Notice::GetAll();
        foreach ($notices as $notice)
            $result->debug('Notice: ' . $notice['message'] . implode("; ", $notice['stack']));

        return $result;
    }

}
