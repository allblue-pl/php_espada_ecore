<?php namespace EC\QRCodes;
defined('_ESPADA') or die(NO_ACCESS);

require(__DIR__ . '/../3rdparty/phpqrcode/qrlib.php');

use E, EC;

class HQRCodes
{

    static public function Generate(string $text, $filePath, $size = 1)
    {
        \QRcode::png($text, $filePath, QR_ECLEVEL_L, $size);
    }

}