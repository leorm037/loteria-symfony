<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Form;

use App\DTO\ApostaImportarDTO;
use App\Repository\LoteriaRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ApostaImportarType extends AbstractType {

    public function __construct(
            private LoteriaRepository $loteriaRepository
    ) {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
                ->add('bolao', HiddenType::class)
                ->add('arquivoPlanilhaCsv', FileType::class, [
                    'label' => 'Planilhas com os jogos',
                    'help' => 'Cada linha deve ser uma aposta e as dezenas devem ser separadas por ponto e vírgula.',
                    'required' => true,
                    'attr' => [
                        'accept' => 'text/csv,text/plain',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '10240k',
                            'mimeTypes' => [
                                'text/csv',
                                'text/plain',
                            ],
                            'mimeTypesMessage' => 'Selecione um arquivo de planilha no formato CSV',
                                ]),
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => ApostaImportarDTO::class,
        ]);
    }
}
