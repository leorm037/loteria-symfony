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

use App\Entity\Apostador;
use App\Repository\ApostadorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApostadorSelecionarType extends AbstractType
{
    public function __construct(
        private ApostadorRepository $apostadorRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $bolaoSelecionado = $options['bolaoSelecionado'];

        $builder
                ->add('apostador', ChoiceType::class, [
                    'label' => 'Selecione os apostadores',
                    'choices' => $this->apostadorRepository->findByBolaoParaSelecionarParaImportar($bolaoSelecionado),
                    'mapped' => false,
                    'required' => true,
                    'choice_value' => 'uuid',
                    'choice_label' => function (?Apostador $apostador): string {
                        return ($apostador->getEmail()) ? \sprintf('%s (%s)', $apostador->getNome(), $apostador->getEmail()) : $apostador->getNome();
                    },
                    'multiple' => true,
                    'expanded' => true,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'bolaoSelecionado' => null,
        ]);
    }
}
