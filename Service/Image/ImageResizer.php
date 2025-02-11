<?php

namespace Tpf\Service\Image;

class ImageResizer
{

    public static function resize($filename, $w, $h)
    {
        $size_img = getimagesize($filename);
        $dest_img = imagecreatetruecolor($w, $h);
        imagealphablending($dest_img, false);
        imagesavealpha($dest_img, true);
        $src_img = ImageIo::createImage($filename, $size_img[2]);
        $d = $size_img[0] < $size_img[1] ? $size_img[0] : $size_img[1];
        if (!imagecopyresampled($dest_img, $src_img, 0, 0, ($size_img[0] - $d) / 2, ($size_img[1] - $d) / 2, $w, $h, $d, $d)) return false;
        unlink($filename);

        ImageIo::exportImage($dest_img, substr($filename, 0, strrpos($filename, ".")) . ".png");
        imagedestroy($dest_img);
        imagedestroy($src_img);

        return true;
    }

    public static function resizeAndCrop($filename, $w, $h)
    {
        $size_img = getimagesize($filename);
        $ratio = $size_img[0] / $size_img[1];
        $target_ratio = $w / $h;
        if ($w > $size_img[0] || $h > $size_img[1]) {
            $x = $y = 0;
            $w1 = ($w > $size_img[0]) ? $w : $target_ratio * $h;
            $h1 = ($h > $size_img[1]) ? $h : $w / $target_ratio;
            if ($h > $size_img[1]) {
                $x = ($size_img[0] - $size_img[1] * $target_ratio) / 2;
                $h = $size_img[1];
            }
            if ($w > $size_img[0]) {
                $y = ($size_img[1] - $size_img[0] / $target_ratio) / 2;
                $w = $size_img[0];
            }
        } else {
            if ($size_img[0] > $size_img[1]) {
                $x = ($size_img[0] - $size_img[1] * $target_ratio) / 2;
                $y = 0;
                $w1 = $w;
                $h1 = $h;
                $w = $size_img[1] * $target_ratio;
                $h = $size_img[1];
            } else {
                $x = 0;
                $y = ($size_img[1] - $size_img[0] / $target_ratio) / 2;
                $w1 = $w;
                $h1 = $h;
                $w = $size_img[0];
                $h = $size_img[0] / $target_ratio;
            }
        }
        $dest_img = imagecreatetruecolor($w1, $h1);
        imagealphablending($dest_img, false);
        imagesavealpha($dest_img, true);
        $src_img = ImageIo::createImage($filename, $size_img[2]);
        if (!imagecopyresampled($dest_img, $src_img, 0, 0, $x, $y, $w1, $h1, $w, $h)) return false;
        unlink($filename);

        ImageIo::exportImage($dest_img, substr($filename, 0, strrpos($filename, ".")) . ".png");
        imagedestroy($dest_img);
        imagedestroy($src_img);

        return true;
    }

    public static function resizeAndCropSaveAspect($filename, $x, $y, $size, $new_size)
    {
        $size_img = getimagesize($filename);
        $dest_img = imagecreatetruecolor($new_size, $new_size);
        imagealphablending($dest_img, false);
        imagesavealpha($dest_img, true);
        $src_img = ImageIo::createImage($filename, $size_img[2]);
        if (!imagecopyresampled($dest_img, $src_img, 0, 0, $x, $y, $new_size, $new_size, $size, $size)) return false;
        unlink($filename);

        ImageIo::exportImage($dest_img, substr($filename, 0, strrpos($filename, ".")) . ".png");
        imagedestroy($dest_img);
        imagedestroy($src_img);

        return true;
    }
}