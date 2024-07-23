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

use App\Entity\ArquivoTipo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArquivoTipoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $arquivosTipos = [
            ['Comprovante de apostas em PDF','i-filetype-pdf'],
            ['Comprovante de apostas em PNG','bi bi-filetype-png'],
            ['Planilha de apostas em CSV','bi bi-filetype-csv'],
        ];

        foreach ($arquivosTipos as $item) {
            $arquivoTipo = new ArquivoTipo();
            
            $arquivoTipo->setNome($item[0]);
            $arquivoTipo->setIcone($item[1]);
            
            $manager->persist($arquivoTipo);
        }

        $manager->flush();
    }
}
