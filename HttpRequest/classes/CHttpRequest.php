<?php namespace EC\HttpRequest;
defined('_ESPADA') or die(NO_ACCESS);

require(__DIR__ . '/../composer/vendor/autoload.php');

use E, EC;

class CHttpRequest {

    private $auth = null;
    private $headers = null;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function delete($url)
    {
        return $this->request('DELETE', $url, [
            'http_errors' => false,
        ]);
    }

    public function delete_JSON($url, array $json = [])
    {
        return $this->request('DELETE', $url, [
            'json' => $json,
            'http_errors' => false,
        ]);
    }

    public function get($url, $fields = [])
    {
        return $this->request('GET', $url);
    }

    public function get_JSON($url)
    {
        return $this->request('GET', $url, [
            'http_errors' => false,
        ]);
    }

    public function post($url, $fields = [])
    {
        return $this->request('POST', $url, [
            'form_params' => $fields,
        ]);
    }

    public function post_JSON($url, array $json)
    {
        return $this->request('POST', $url, [
            'json' => $json,
            'http_errors' => false,
        ]);
    }

    public function put_JSON($url, array $json)
    {
        return $this->request('PUT', $url, [
            'json' => $json,
            'http_errors' => false,
        ]);
    }

    public function request($type, $url, $extra = [])
    {
        if ($this->auth !== null)
            $extra['auth'] = $this->auth;
        if ($this->headers !== null)
            $extra['headers'] = $this->headers;

        return $this->client->request($type, $url, $extra);
    }

    public function setAuth(string $user, string $password)
    {
        $this->auth = [ $user, $password ];
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

}