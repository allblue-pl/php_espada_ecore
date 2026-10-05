<?php namespace EC\Images;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HImages {

    static public function Create($filePath) {
        if (!file_exists($filePath))
            throw new \Exception("File path '{$filePath}'  does not exist.");

        $mime = getimagesize($filePath)['mime'];

        if ($mime === 'image/jpeg')
            return imagecreatefromjpeg($filePath);
        if ($mime === 'image/gif')
            return imagecreatefromgif($filePath);
        if ($mime === 'image/png')
            return imagecreatefrompng($filePath);
        if ($mime === 'image/webp')
            return imagecreatefromwebp($filePath);

        return null;
    }

    static public function Save($image, $destFilePath, $quality) {
        $ext = mb_strtolower(pathinfo($destFilePath, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg')
            return imagejpeg($image, $destFilePath, $quality);
        else if ($ext === 'png') {
            $quality_Png = 9 - (int)round(($quality / 100.0) * 9);
            imagesavealpha($image, true);
            return imagepng($image, $destFilePath, $quality_Png);
        } else if ($ext === 'webp') {
            imagesavealpha($image, true);
            return imagewebp($image, $destFilePath, $quality);
        } else
            throw new \Exception('Unknown image extension.');
    }

    static public function Scale_ToMinSize($filePath, $destFilePath,
            $min_width, $min_height, $quality = 75, $compress = true) {
        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');

        $ext = pathinfo($destFilePath, PATHINFO_EXTENSION);
        $image = self::Create($filePath);

        if ($ext === 'jpg' || $ext === 'jpeg') {
            try {
                $exif = exif_read_data($filePath);
                if (!empty($exif['Orientation'])) {
                    $image_Source = $image;
                    switch ($exif['Orientation']) {
                        case 3:
                            $image = imagerotate($image_Source, 180, 0);
                            break;
                        case 6:
                            $image = imagerotate($image_Source, -90, 0);
                            break;
                        case 8:
                            $image = imagerotate($image_Source, 90, 0);
                            break;
                        default:
                            $image = $image_Source;
                    }
                    
                    if ($image !== $image_Source)
                        unset($image_Source);
                }
            } catch (\Exception $e) {
                // Do nothing.
            }
        }

        $image_width = imagesx($image);
        $image_height = imagesy($image);

        if ($image_width < $min_width || $image_height < $min_height) {
            $result = false;
            if (!$compress)
                $result = copy($filePath, $destFilePath);
            else
                $result = self::Save($image, $destFilePath, $quality);

            unset($image);
            return $result;
        }

        $width_factor = $min_width / $image_width;
        $height_factor = $min_height / $image_height;
        $factor = max($width_factor, $height_factor);

        $scaled_image = imagescale($image, (int)($factor * $image_width),
                (int)($factor * $image_height));
        if ($scaled_image === false)
            throw new \Exception('Cannot scale image.');
        unset($image);

        $result = self::Save($scaled_image, $destFilePath, $quality);

        unset($scaled_image);

        ini_set('memory_limit', $memory_limit);

        return $result;
    }

    static public function Scale_ToMinSize_Image($image, $min_width, $min_height,
            $scale_up = false) {
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

        $t_image = imagescale($image, (int)($factor * $image_width), 
                (int)($factor * $image_height));

        return $t_image;
    }

}
