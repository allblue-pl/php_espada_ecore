<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HApp
{

    static public function Authenticate(EC\MDatabase $db, $app_id,
            $authentication_hash)
    {
        $info_row = (new TInfos($db))->row_Where([
            [ 'Id', '=', $app_id ],
            [ 'Active', '=', true]
        ]);

        if ($info_row === null)
            return null;
        if (!EC\HHash::CheckPassword($authentication_hash,
                $info_row['AuthenticationHash']))
            return null;

        return $info_row;
    }

    static public function CreateAppInfo(EC\MDatabase $db, $user_id)
    {
        if ($db->transaction_IsAutocommit())
            throw new \Exception('Transaction required.');

        $table = new TInfos($db);

        $authentication_hash = EC\HHash::Generate(256);

        if (!($table->update([[
            'Id' => null,
            'User_Id' => $user_id,
            'AuthenticationHash' => EC\HHash::GetPassword($authentication_hash),
            'TablesIds' => []
                ]])))
            return null;

        return [
            'id' => $db->getInsertedId(),
            'authenticationHash' => $authentication_hash
        ];
    }

}
