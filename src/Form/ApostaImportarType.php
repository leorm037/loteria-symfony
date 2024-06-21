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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ApostaImportarType extends AbstractType
{
    public function __construct(
        private LoteriaRepository $loteriaRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('loteria', ChoiceType::class, [
                    'label' => 'Loteria',
                    'choices' => $this->loteriaRepository->getAll(),
                    'choice_value' => 'uuid',
                    'choice_label' => 'nome',
                    'placeholder' => 'Selecione uma loteria',
                    'required' => true,
                ])
                ->add('numero', IntegerType::class, [
                    'label' => 'Número do concurso',
                    'required' => true,
                ])
                ->add('arquivoPlanilhaCsv', FileType::class, [
                    'label' => 'Planilha CSV com as apostas',
                    'help' => 'Cada linha deve ser uma aposta e as dezenas devem ser separadas por vírgula.',
                    'required' => true,
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
                ->add('arquivoComprovantePdf', FileType::class, [
                    'label' => 'Arquivo PDF com as apostas',
                    'help' => 'O arquivo PDF deve ter as imagens dos comprovantes.',
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '10240k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                            ],
                            'mimeTypesMessage' => 'Selecione um arquivo no formato PDF com os comprovantes.',
                        ]),
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApostaImportarDTO::class,
        ]);
    }
}
