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

use Twig\Extension\RuntimeExtensionInterface;

class DezenasSorteadasExtensionRuntime implements RuntimeExtensionInterface
{
    public const COLOR_PRIMARY = 'primary';
    public const COLOR_SUCCESS = 'success';
    public const COLOR_DANGER = 'danger';
    public const COLOR_WARNING = 'warning';
    public const COLOR_INFO = 'info';
    public const COLOR_LIGHT = 'light';
    public const COLOR_DARK = 'dark';

    /**
     * @param array<string>      $dezenasApostadas
     * @param array<string>|null $dezenasSorteadas
     * @param string             $separador        Valor padrão ', '
     * @param string             $cor              (primary, success, danger, warning, info, light, dark)
     */
    public function dezenasSorteadas(array $dezenasApostadas, ?array $dezenasSorteadas, string $separador = ', ', string $cor = self::COLOR_SUCCESS): string
    {
        if (!\is_array($dezenasSorteadas)) {
            return implode($separador, $dezenasApostadas);
        }

        $resultado = [];

        foreach ($dezenasApostadas as $dezena) {
            if (\in_array($dezena, $dezenasSorteadas)) {
                /*
                 * <span class="badge text-bg-primary">Primary</span>
                 * <span class="badge text-bg-secondary">Secondary</span>
                 * <span class="badge text-bg-success">Success</span>
                 * <span class="badge text-bg-danger">Danger</span>
                 * <span class="badge text-bg-warning">Warning</span>
                 * <span class="badge text-bg-info">Info</span>
                 * <span class="badge text-bg-light">Light</span>
                 * <span class="badge text-bg-dark">Dark</span>
                 */
                $html = '<span class="badge text-bg-%s">%s</span>';

                $resultado[] = \sprintf($html, $cor, $dezena);

                continue;
            }

            $resultado[] = $dezena;
        }

        return implode($separador, $resultado);
    }
}
