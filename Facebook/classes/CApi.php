<?php namespace EC\Facebook;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class CApi extends EC\Api\CBasicApi {

    private $facebook = null;

    public function __construct($site, $args) {
        parent::__construct($site);

        $this->facebook = new CFacebook($site->modules->config);

        $this->action('comment', [
            'hash' => true,
            'action' => true,
            'commentId' => true,
            'message' => true,
            'href' => true
        ], 'action_Comment');
    }

    protected function action_Comment($args) {
        if ($args['hash'] !== $this->facebook->getCommentHash($args['href']))
            return Api\CResult::Failure('Wrong hash.');

        return Api\CResult::Success();
    }

}
