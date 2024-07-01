<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Service;

use App\Entity\Concurso;
use App\Entity\Loteria;
use App\Factory\ConcursoFactory;
use Exception;

/**
 * Description of ConcursoSorteioService
 *
 * @author leona
 */
class ConcursoSorteioService
{
    public static function getConcurso(Loteria $loteria, int $numero = null): ?Concurso
    {
        $json = self::getJson($loteria, $numero);

        return ConcursoFactory::buildFromJson($loteria, $json);
    }

    private static function getJson(Loteria $loteria, int $numero = null): string
    {
        $handle = curl_init($loteria->getApiUrl().'/'.$numero);

        curl_setopt($handle, \CURLOPT_TIMEOUT, 5);
        curl_setopt($handle, \CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, \CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handle, \CURLOPT_SSL_VERIFYPEER, 0);
        //        curl_setopt($handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');

        $json = curl_exec($handle);

        $error = curl_error($handle);

        if (!empty($error)) {
            throw new Exception($error);
        }

        curl_close($handle);

        $validade = json_decode($json);

        if (isset($validade->message)) {
            throw new Exception(sprintf('Não foi possível encontrar o sorteio %s da %s.', $numero, $loteria->getNome()));
        }

        return $json;
    }
}
