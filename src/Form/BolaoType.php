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

use App\DTO\BolaoDTO;
use App\Repository\LoteriaRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BolaoType extends AbstractType
{

    public function __construct(
            private LoteriaRepository $loteriaRepository
    )
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('loteria', ChoiceType::class, [
                    'label' => 'Loteria',
                    'choices' => $this->loteriaRepository->findAllOrderByNome(),
                    'choice_value' => 'uuid',
                    'choice_label' => 'nome',
                    'placeholder' => 'Selecione uma Loteria',
                    'required' => true,
                ])
                ->add('concursoNumero', IntegerType::class, [
                    'label' => 'Número do Concurso',
                    'required' => true,
                ])
                ->add('nome', TextType::class, [
                    'label' => 'Nome do bolão',
                    'required' => true,
                ])
                ->add('arquivoPlanilhaCsv', FileType::class, [
                    'label' => 'Planilhas com os jogos',
                    'help' => 'Cada linha deve ser uma aposta e as dezenas devem ser separadas por ponto e vírgula.',
                    'mapped' => false,
                    'required' => false,
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
                ->add('arquivoComprovantePdf', FileType::class, [
                    'label' => 'Comprovantes dos jogos',
                    'help' => 'O arquivo PDF deve ter as imagens dos comprovantes.',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'accept' => 'application/pdf,application/x-pdf,image/png',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '10240k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                                'image/png'
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
            'data_class' => BolaoDTO::class,
        ]);
    }
}
