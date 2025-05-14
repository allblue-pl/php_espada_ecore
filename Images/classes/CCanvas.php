<?php namespace EC\Images;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class CCanvas {

    private $image = null;

    public function __construct($image)
    {
        $this->image = $image;
    }

    public function image($file_path, $coords, array $size = null)
    {
        $t_image = HImages::Create($file_path);
        if ($t_image === null)
            throw new \Exception('Cannot create image.');

        $t_width = imagesx($t_image);
        $t_height = imagesy($t_image);

        $t_x = 0;
        $t_y = 0;

        if ($size !== null) {
            $s_image = HImages::Scale_ToMinSize_Image($t_image, $size[0], $size[1], true);
            imagedestroy($t_image); $t_image = $s_image;

            $t_width = imagesx($t_image);
            $t_height = imagesy($t_image);

            $t_x = $t_width > $size[0] ? ($t_width - $size[0]) / 2 : 0;
            $t_y = $t_height > $size[1] ? ($t_height - $size[1]) / 2 : 0;

            $t_width = min($t_width, $size[0]);
            $t_height = min($t_height, $size[1]);
        }

        imagecopy($this->image, $t_image, $coords[0], $coords[1], $t_x, $t_y,
                $t_width, $t_height);

        return $this;
    }

    public function text($text, $font_path, $font_size, $color, array $coords)
    {
        imagettftext($this->image, $font_size, 0,
                $coords[0], $coords[1] + $font_size,
                $color, $font_path, $text);

        return $this;
    }

}
