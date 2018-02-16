<?php namespace EC\Users;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TUsers extends Database\TTable
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'Users_Users', 'uu');

        /* Columns - Table */
        $this->addColumns([
            'Id'            => new Database\FInt(true, 11),
            'LoginHash'     => new Database\FVarchar(true, 256),
            'EmailHash'     => new Database\FVarchar(true, 256),
            'PasswordHash'  => new Database\FVarchar(true, 256),
            'Groups'        => new Database\FVarchar(true, 128),
            'Active'        => new Database\FBool(true)
        ]);
        /* Columns - Extra */
        $this->addColumns_Extra([
            'Groups_Permissions' => [ null, null ]
        ]);

        $this->setColumnParser('Groups', [
            'out' => function($row, $name, $value) {
                $groups = explode(',', $row[$name]);

                return [
                    $name => $groups,
                    $name . '_Permissions' => HPermissions::Get_FromGroups($groups)
                ];
            },
            'in' => function($row, $name, $value) {
                if (!is_array($value))
                    throw new \Exception('`groups` column must be an array.');

                return implode(',', $value);
            }
        ]);
    }

}
