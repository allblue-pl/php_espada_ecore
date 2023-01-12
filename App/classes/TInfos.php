<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TInfos extends _TInfos
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'a_i');

        $this->addColumns_Ref(new EC\TUsers($db), [
            'Active' => [ 'u_u.Active', 'Active' ]
        ]);

        $this->setJoin(
            'INNER JOIN Users_Users AS u_u' .
            ' ON u_u.Id = a_i.User_Id'
        );

        /* Parsers */
        $this->setColumnParser('Data', [
            'out' => function($row, $name, $value) {
                $json = json_decode($value, true);
                if ($json === null) {
                    return [
                        $name => [
                            'stdObj' => true,
                        ],
                    ];
                }

                if (!array_key_exists('data', $json)) {
                    return [
                        $name => [
                            'stdObj' => true,
                        ],
                    ];
                }

                return [
                    $name => json_decode($value, true)['data']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'data' => $value ]);
            }
        ]);
    }

}
