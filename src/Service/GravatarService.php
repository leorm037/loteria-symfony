<?php

namespace App\Service;

class GravatarService
{

    /**
     * 
     * @param string $email Endereço de e-mail cadastrado no Gravatar
     * @param int $sizePixels Altura em pixels, o valor padrão é 80px [ 1 a 2048 ]
     * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
     * @param string $r
     * @param bool $img
     * @param array<string,string> $atts
     * @return string
     */
    public static function getUrl(string $email, int $sizePixels = 80, string $d = 'mp', string $r = 'g', bool $img = false, array $atts = []): string
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$sizePixels&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }

        return $url;
    }
}
