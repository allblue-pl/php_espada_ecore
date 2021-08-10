<?php namespace EC\Config;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC, EC\Sys,
    EC\Api\CArgs, EC\Api\CResult;

class ASettings extends EC\Api\ABasic
{

    public function __construct(EC\SApi $site, array $apiArgs)
    {
        parent::__construct($site, $apiArgs['userType'], [ 'Sys_Settings' ]);

        $this->db = $site->m->db;

        $this->actionR('get', 'action_Get', [
            'names' => true,
        ]);
        $this->actionR('set', 'action_Set', [
            'settings' => true,

            'carouselImages_1' => false,
            'carouselImages_2' => false,
            'carouselImages_3' => false,
        ]);
    }

    public function action_Get(CArgs $args)
    {
        $settings = [];
        foreach ($args->names as $name)
            $settings[$name] = EC\HConfig::DB_Get($this->db, $name);

        return CResult::Success()
            ->add('settings', $settings);
    }

    public function action_Set(CArgs $args)
    {
        $this->db->transaction_Start();

        for ($i = 1; $i <= 3; $i++) {
            if (!$args->isset("carouselImages_{$i}"))
                continue;
            if ($args->get("carouselImages_{$i}") === 'null') 
                continue;

            if (!EC\HUpload::Validate($args->get("carouselImages_{$i}"), [
                'exts' => [ 'jpg', 'jpeg' ],
            ], $upload_error)) {
                return CResult::Failure('Cannot upload photo.')
                    ->add('errorMessage', $upload_error);
            }

            $headerPath = E\Path::Media('Web', "header{$i}.jpg");

            EC\HImages::Scale_ToMinSize($args->get("carouselImages_{$i}")['tmp_name'],
                    $headerPath, 1920, 1080);
        }

        foreach ($args->settings as $name => $value) {
            if (!EC\HConfig::DB_Set($this->db, $name, $value)) {
                $this->db->transaction_Finish(false);
                return CResult::Failure('Cannot update settings.');
            }
        }

        if (!$this->db->transaction_Finish($this->db))
            return CResult::Failure('Cannot commit.');

        return CResult::Success(EC\HText::_('Successes_Updated'));
    }

}