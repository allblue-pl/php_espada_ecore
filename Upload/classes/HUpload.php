<?php namespace EC\Upload;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HUpload {

    static public function Validate($file, $info = [], &$error = null) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_INI_SIZE ||
                    $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $error = EC\HText::_('Upload:Errors_FileTooBig');
                return false;
            }

            $error = EC\HText::_('Upload:Errors_CannotUploadFile', [ $file['error'] ]);
            return false;
        }

        if (array_key_exists('exts', $info)) {
            $path_info = pathinfo($file['name']);
            if (!in_array(mb_strtolower($path_info['extension']), $info['exts'])) {
                $error = EC\HText::_('Upload:Errors_WrongExtension');
                return false;
            }
        }

        return true;
    }

}
