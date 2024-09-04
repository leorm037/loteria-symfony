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

use App\Entity\Loteria;
use App\Form\DataTransformer\ApostaArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('nome', TextType::class, [
                ])
                ->add('slugUrl', TextType::class, [
                    'label' => 'Abreviação',
                    'required' => false,
                    'help' => 'Campo gerado automaticamente',
                    'disabled' => true,
                ])
                ->add('apiUrl', TextType::class, [
                    'label' => 'Endereço da API',
                    'required' => true,
                ])
                ->add('aposta', TextType::class, [
                    'label' => 'Apostas',
                    'required' => true,
                    'help' => 'Quantidade de dezenas permitidas em uma única aposta',
                ])
                ->add('dezenas', TextType::class, [
                    'label' => 'Dezenas',
                    'required' => true,
                ])
        ;

        $builder->get('aposta')->addModelTransformer(new ApostaArrayToStringTransformer());
        $builder->get('dezenas')->addModelTransformer(new ApostaArrayToStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loteria::class,
        ]);
    }
}
