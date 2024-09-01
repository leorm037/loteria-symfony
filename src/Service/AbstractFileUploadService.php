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

use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class AbstractFileUploadService
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
    ) {
    }

    public function getTargetDirectory(): ?string
    {
        $dateTime = new DateTime();

        $dateTimeDirectory = $dateTime->format('Y/m/d');

        return $this->targetDirectory.'/'.$dateTimeDirectory;
    }

    public function upload(UploadedFile $file): ?string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename)->lower();

        $filename = $safeFilename.'_'.uniqid().'.'.$file->getClientOriginalExtension();

        try {
            $file->move($this->getTargetDirectory(), $filename);
        } catch (FileException $e) {
            $this->logger->error(
                'Erro ao tentar mover o arquivo '.$originalFilename, [
                    'fileOriginal' => $file->getFileInfo(),
                    'targetDirectory' => $this->getTargetDirectory(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                ]
            );

            return null;
        }

        return $this->getTargetDirectory().'/'.$filename;
    }

    public function delete(?string $filename): bool
    {
        try {
            return unlink($filename);
        } catch (Exception $e) {
            $mensagem = \sprintf('Arquivo "%s" não encontrador', $filename);

            $this->logger->error($mensagem, $e->getTrace());

            return true;
        }
    }
}
