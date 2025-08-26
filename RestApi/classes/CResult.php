<?php namespace EC\RestApi;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CResult {

    static public function Success(array $json = null) {
        return new CResult(200, $json);
    }

    static public function Error(int $statusCode = 500, array $json = null) {
        return new CResult($statusCode, $json);
    }


    private $statusCode = 0;
    private $json = null;
    
    public function __construct(int $statusCode, ?array $json) {
        $this->statusCode = $statusCode;
        $this->json = $json;
    }

    public function getJSON() {
        // $json = mb_convert_encoding($this->outputs, 'UTF-8', 'UTF-8');

        if ($this->json === null)
            return '';

        $this->escapeNonUTF($this->json);

        $json_Str = json_encode($this->json);
        if ($json_Str == null) {
            throw new \Exception('Cannot parse Api\CResult `outputs`: ' .
                    json_last_error_msg());
        }

        return $json_Str;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }


    private function escapeNonUTF(array &$json) {   
        array_walk_recursive($json, function(&$item) {
            if (is_string($item))
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        });
    }

}
