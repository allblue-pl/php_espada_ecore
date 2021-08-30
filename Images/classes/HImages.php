<?php namespace EC\Images;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HImages
{

    static public function Create($file_path)
    {
        if (!file_exists($file_path))
            throw new \Exception("File path '{$file_path}'  does not exist.");

        $mime = getimagesize($file_path)['mime'];

        if ($mime === 'image/jpeg')
            return imagecreatefromjpeg($file_path);
        if ($mime === 'image/gif')
            return imagecreatefromgif($file_path);
        if ($mime === 'image/png')
            return imagecreatefrompng($file_path);

        return null;
    }

    static public function Save($image, $dest_file_path, $quality)
    {
        $ext = mb_strtolower(pathinfo($dest_file_path, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg')
            return imagejpeg($image, $dest_file_path, $quality);
        else if ($ext === 'png') {
            $quality_Png = 9 - round(($quality / 100.0) * 9);
            imagesavealpha($image, true);
            return imagepng($image, $dest_file_path, $quality_Png);
        } else
            throw new \Exception('Unknown image extension.');
    }

    static public function Scale_ToMinSize($file_path, $dest_file_path,
            $min_width, $min_height, $quality = 75, $compress = true)
    {
        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');

        $image = self::Create($file_path);

        $image_width = imagesx($image);
        $image_height = imagesy($image);

        if ($image_width < $min_width || $image_height < $min_height) {
            $result = false;
            if (!$compress)
                $result = copy($file_path, $dest_file_path);
            else
                $result = self::Save($image, $dest_file_path, $quality);

            imagedestroy($image);
            return $result;
        }

        $width_factor = $min_width / $image_width;
        $height_factor = $min_height / $image_height;
        $factor = max($width_factor, $height_factor);

        $scaled_image = imagescale($image, $factor * $image_width,
                $factor * $image_height);
        imagedestroy($image);

        $result = self::Save($scaled_image, $dest_file_path, $quality);

        imagedestroy($scaled_image);

        ini_set('memory_limit', $memory_limit);

        return $result;
    }

    static public function Scale_ToMinSize_Image($image, $min_width, $min_height,
            $scale_up = false)
    {
        $image_width = imagesx($image);
        $image_height = imagesy($image);

        if (!$scale_up && ($image_width < $min_width || $image_height < $min_height)) {
            $t_image = imagecreatetruecolor($image_width, $image_height);
            imagecopy($t_image, $image, 0, 0, 0, 0, $image_width, $image_height);

            return $t_image;
        }

        $width_factor = $min_width / $image_width;
        $height_factor = $min_height / $image_height;
        $factor = max($width_factor, $height_factor);

        $t_image = imagescale($image, $factor * $image_width, $factor * $image_height);

        return $t_image;
    }

}
