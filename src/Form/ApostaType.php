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

use App\Entity\Aposta;
use App\Form\DataTransformer\ApostaArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApostaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('dezenas', TextType::class, [
                    'label' => 'Dezenas',
                    'required' => true,
                    'help' => 'Separe as dezenas por vírgula.',
                ])
        ;

        $builder->get('dezenas')
                ->addModelTransformer(new ApostaArrayToStringTransformer())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Aposta::class,
            'error_mapping' => [
                'quantidadeDezenasMenor' => 'dezenas',
                'quantidadeDezenasMaior' => 'dezenas',
                'dezenasForaDoIntervalo' => 'dezenas',
            ],
        ]);
    }
}
