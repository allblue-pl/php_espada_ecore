<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HResult {
    static public function Failure(array $data = []): array {
        $data['_type'] = 1;
        return self::ParseResult($data);
    }

    static public function Success(array $data = []): array {
        $data['_type'] = 0;
        return self::ParseResult($data);
    }


    static private function ParseResult(array $data): array {
        if (!array_key_exists('_message', $data))
            $data['_message'] = '';
        if (!EDEBUG) {
            if (array_key_exists('_debug', $data))
                unset($data['_debug']);
        }

        return $data;
    }
}