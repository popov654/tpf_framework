<?php

namespace Tpf\Service\Image;

class ImageIo
{

    public static function createImage(string $filename, int $type): object
    {
        switch ($type) {
            case IMAGETYPE_GIF:
                $src_img = imagecreatefromgif($filename);
                break;
            case IMAGETYPE_JPEG:
                $src_img = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                $src_img = imagecreatefrompng($filename);
                break;
            case IMAGETYPE_BMP:
                if (!function_exists('imagecreatefrombmp')) {
                    throw new \Exception("Unsupported image type");
                }
                $src_img = imagecreatefrombmp($filename);
                break;
            case 18:
                if (!function_exists('imagecreatefromwebp')) {
                    throw new \Exception("Unsupported image type");
                }
                $src_img = imagecreatefromwebp($filename);
                break;
            default:
                throw new \Exception("Unsupported image type");
        }
        
        return $src_img;
    }

    public static function exportImage(object $image, string $filename): bool
    {
        global $TPF_CONFIG;

        $format = strtolower($TPF_CONFIG['images']['format'] ?? 'png');

        $res = false;

        switch ($format) {
            case 'gif':
                $res = imagegif($filename);
                break;
            case 'jpg':
            case 'jpeg':
                $res = imagejpeg($filename);
                break;
            case 'png':
                $res = imagepng($filename);
                break;
            case 'bmp':
                if (!function_exists('imagebmp')) {
                    throw new \Exception("Unsupported image type");
                }
                $res = imagebmp($filename);
                break;
            case 'webp':
                if (!function_exists('imagewebp')) {
                    throw new \Exception("Unsupported image type");
                }
                $res = imagewebp($filename);
                break;
            case 'avif':
                if (!function_exists('imageavif')) {
                    throw new \Exception("Unsupported image type");
                }
                $res = imageavif($filename);
                break;
            default:
                throw new \Exception("Unsupported image type");
        }

        return $res;
    }
}