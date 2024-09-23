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

use App\Entity\Bolao;
use App\Repository\BolaoRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BolaoSelecionarType extends AbstractType
{
    public function __construct(
            private BolaoRepository $bolaoRepository
    )
    {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $bolao = $options['bolao'];
        
        $builder
            ->add('bolao', ChoiceType::class, [
                'label' => 'Selecione um Bolão',
                'choices' => $this->bolaoRepository->findByBolaoComApostadoresDiferenteDoBolaoAtual($bolao),
                'mapped' => false,
                'required' => true,
                'choice_value' => 'uuid',
                'choice_label' => 'nome',
                'multiple' => false,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'bolao' => null,
        ]);
    }
}
