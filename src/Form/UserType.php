<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $input = "mt-1 focus:ring-imago-green focus:border-imago-green block w-full shadow-sm sm:text-sm border-gray-300 rounded-md";
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'attr' => ['class' => "mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"],
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [                   
                    'Utilisateur' => "ROLE_USER",                   
                    'Editeur' => "ROLE_ADMIN",                   
                    'Administrateur' => "ROLE_SUPER_ADMIN",                   
                ],
     
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                "label"=> false,
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'first_options'  => ['label' => 'admin.entity.password','attr' => ['class' => $input]],
                'second_options' => ['label' => 'admin.entity.repeatPassword','attr' => ['class' => $input]],
            ])  
        ;

        $builder->get('roles')
        ->addModelTransformer(new CallbackTransformer(
            function ($rolesArray) {
                 // transform the array to a string
                 return count($rolesArray)? $rolesArray[0]: null;
            },
            function ($rolesString) {
                 // transform the string back to an array
                 return [$rolesString];
            }
    ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
