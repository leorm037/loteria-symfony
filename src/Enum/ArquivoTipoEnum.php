<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Enum;

enum ArquivoTipoEnum: string
{
    case APOSTA_COMPROVANTE = 'Comprovante de apostas';
    case APOSTA_PLANILHA = 'Planilha de apostas';
}
