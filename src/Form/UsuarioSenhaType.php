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

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class UsuarioSenhaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('plainPassword', RepeatedType::class, [
                    'required' => true,
                    'options' => [
                        'attr' => [
                            'autocomplete' => 'new-password',
                        ],
                    ],
                    'mapped' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'A confirmação de senha não corresponde com a senha.',
                    'first_options' => [
                        'label' => 'Senha',
                        'attr' => [
                            'autofocus' => true,
                        ],
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Por favor, informe um senha',
                            ]),
                            new Length([
                                'min' => 6,
                                'minMessage' => 'Sua senha deve ter no mínimo {{ limit }} caracteres',
                                // max length allowed by Symfony for security reasons
                                'max' => 4096,
                            ]),
                            new PasswordStrength(),
                            new NotCompromisedPassword(),
                        ],
                    ],
                    'second_options' => ['label' => 'Confirmar senha'],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
