<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigUtils extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('getimagesize', [$this, 'getimagesize'])
        ];
    }

    public function getimagesize($image): bool|array
    {

        $imagesInfo = exif_read_data($image);
        $size = getimagesize($image);

        if(empty($imagesInfo['Orientation'])) {
            $imageSize[] = $size[0];
            $imageSize[] = $size[1];
        }else{
            $imageSize[] = $size[1];
            $imageSize[] = $size[0];
        }

        return $imageSize;

    }

}