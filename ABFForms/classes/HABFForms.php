<?php namespace EC\CForms;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CCForms
{

    static private $Initialized = false;

    static public function Create(EC\MSPK $abf, $form_name, $form_info)
    {
        self::Init($abf);

        $form = self::ParseInfo($form_info);
        $form_json = json_encode($form);

        $abf->addAppScript(
            "SPK,\$eForms.add('{$form_name}', {$form_json})"
        );
    }

    static public function Init(EC\MSPK $abf)
    {
        if (self::$Initialized)
            return;
        self::$Initialized = true;

        $abf->addTexts('SPKTables');
    }

    static public function ParseInfo($form_info)
    {
        $form = [
            'apiUri' => [
                'submit' => null,
                'refresh' => null
            ],

            'buttons' => [],

            'fields' => []
        ];

        foreach ($table_info as $prop_name => $prop) {
            if (!array_key_exists($prop_name, $table))
                throw new \Exception("Property `{$prop_name}` doesn't exist.");

            $table[$prop_name] = $prop;
        }
    }

}
