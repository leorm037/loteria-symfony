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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BolaoDTO::class,
        ]);
    }
}
