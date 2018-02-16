<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class TInfos extends EC\Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'App_Infos', 'i');

        $this->setColumns([
            'Id'        => new EC\Database\FInt(true, 11),
            'User_Id'   => new EC\Database\FInt(true, 11),

            'AuthenticationHash'    => new EC\Database\FVarchar(true, 256),
            'TablesIds'              => new EC\Database\FText(true, 'medium'),
        ]);
        $this->addColumns_Ref(new EC\TUsers($db), [
            'Active' => [ 'u_u.Active', 'Active' ]
        ]);

        $this->setJoin(
            'INNER JOIN Users_Users AS u_u' .
            ' ON u_u.Id = i.User_Id'
        );

        /* Parsers */
        $this->setColumnParser('TablesIds', [
            'out' => function($row, $name, $value) {
                return [
                    $name => json_decode($value, true)['TablesIds']
                ];
            },
            'in' => function($row, $name, $value) {
                return json_encode([ 'TablesIds' => $value ]);
            }
        ]);
    }

}
