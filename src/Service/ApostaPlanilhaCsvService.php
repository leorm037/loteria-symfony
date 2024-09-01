<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ApostaPlanilhaCsvService extends AbstractFileUploadService
{
    public function __construct(
        string $targetDirectory,
        SluggerInterface $slugger,
        LoggerInterface $logger,
    ) {
        parent::__construct($targetDirectory, $slugger, $logger);
    }
}
