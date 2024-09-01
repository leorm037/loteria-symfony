<?php

$header = <<<'EOF'
    This file is part of Loteria.

    (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>

    This source file is subject to the MIT license that is bundled
    with this source code in the file LICENSE.
EOF;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'phpdoc_to_comment' => false,
        'header_comment' => ['header' => $header,],
        'protected_to_private' => false,
        'modernize_strpos' => true,
        'global_namespace_import' => [
            'import_classes' => true, 
            'import_constants' => false, 
            'import_functions' => false
        ]
    ])
    ->setFinder($finder)
;
