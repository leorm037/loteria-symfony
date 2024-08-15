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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ApostadorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('nome', TextType::class, [
                    'label' => 'Nome',
                    'required' => true,
                    'attr' => [
                        'autofocus' => true,
                    ],
                ])
                ->add('email', EmailType::class, [
                    'label' => 'E-mail',
                    'required' => false,
                ])
                ->add('cotaPaga', ChoiceType::class, [
                    'label' => 'Cota paga',
                    'required' => true,
                    'choices' => [
                        'Sim' => true,
                        'Não' => false,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'label_attr' => [
                        'class' => 'radio-inline',
                    ],
                ])
                ->add('cotaQuantidade', IntegerType::class, [
                    'label' => 'Quantidade de cotas',
                    'required' => true,
                    'attr' => [
                        'class' => 'text-end',
                    ],
                    'constraints' => [
                        new GreaterThanOrEqual([
                            'value' => 1,
                            'message' => 'A quantidade de cotas deve ser igual ou maior que 1.',
                        ]),
                    ],
                ])
                ->add('arquivoComprovanteJpg', FileType::class, [
                    'label' => 'Comprovante de pagamente',
                    'help' => 'Pagamento da cota é confirmado quando um comprovante é enviado.',
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'accept' => 'image/jpeg',
                    ],
                    'constraints' => [
                        new File([
                            'maxSize' => '10240k',
                            'mimeTypes' => [
                                'image/jpeg',
                            ],
                            'mimeTypesMessage' => 'Selecione o comprovante em formato Jpg.',
                        ]),
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Apostador::class,
        ]);
    }
}
