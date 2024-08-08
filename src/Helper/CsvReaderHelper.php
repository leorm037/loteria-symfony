<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Helper;

class CsvReaderHelper
{
    private \SplFileObject $file;

    public function __construct(string $filePath, string $delimiter = ';')
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo \"{$filePath}\" não encontrado.");
        }

        $this->file = new \SplFileObject($filePath);
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setCsvControl($delimiter);
    }

    /** @return \Iterator */
    public function getIterator()
    {
        foreach ($this->file as $row) {
            yield $row;
        }
    }

    public function eof(): bool
    {
        return $this->file->eof();
    }
}
