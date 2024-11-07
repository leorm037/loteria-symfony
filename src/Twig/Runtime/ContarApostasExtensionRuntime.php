<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Twig\Runtime;

use App\Entity\Aposta;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\RuntimeExtensionInterface;

class ContarApostasExtensionRuntime implements RuntimeExtensionInterface
{

    public function __construct()
    {
        // Inject dependencies if needed
    }

    /**
     * 
     * @param Collection<int,Aposta> $apostas
     * @param int $quantidadeDezenas Exemplo: "6 acertos"
     * @return int
     */
    public function contarApostas(Collection $apostas, string $quantidadeDezenas): int
    {
        $arrayDezenas = explode(' ', $quantidadeDezenas);
        $quantidade = intval($arrayDezenas[0]);
        
        $quantidadeApostas = 0;

        /** @var Aposta $aposta */
        foreach ($apostas as $aposta) {
            if (isset($aposta) && $aposta->getQuantidadeAcertos() === $quantidade) {
                $quantidadeApostas++;
            }
        }

        return $quantidadeApostas;
    }
}
