<?php namespace EC\App;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Database\MDatabase;
use EC\Hash\HHash;

class HApp {

    static public function Authenticate(MDatabase $db, $app_id,
            $authentication_hash) {
        $info_row = (new TInfos($db))->row_Where([
            [ 'Id', '=', $app_id ],
            [ 'Active', '=', true]
        ]);

        if ($info_row === null)
            return null;
        if (!HHash::CheckPassword($authentication_hash,
                $info_row['AuthenticationHash']))
            return null;

        return $info_row;
    }

    static public function CreateAppInfo(MDatabase $db, $userId, $data = []) {
        if ($db->transaction_IsAutocommit())
            throw new \Exception('Transaction required.');

        $table = new TInfos($db);

        $authentication_hash = HHash::Generate(256);

        if (!($table->update([[
            'Id' => null,
            'User_Id' => $userId,
            'AuthenticationHash' => HHash::GetPassword($authentication_hash),
            'Data' => (Object)$data,
                ]])))
            return null;

        return [
            'id' => $table->getLastInsertedId(),
            'authenticationHash' => $authentication_hash
        ];
    }

}
