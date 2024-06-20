<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Loteria;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoteriaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $loterias = [
            [
                'nome' => 'Mega-Sena',
                'apiUrl' => 'https://servicebus2.caixa.gov.br/portaldeloterias/api/megasena',
            ],
        ];

        foreach ($loterias as $item) {
            $loteria = new Loteria();

            $loteria
                    ->setNome($item['nome'])
                    ->setApiUrl($item['apiUrl'])
            ;

            $manager->persist($loteria);
        }

        $manager->flush();
    }
}
