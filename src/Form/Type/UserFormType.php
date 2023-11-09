<?php

namespace App\Entity;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
    $builder
      ->add('userName', TextType::class)
      ->add('password', TextType::class)
      ->add('email', TextType::class)
      ->add('firstName', TextType::class)
      ->add('lastName', TextType::class);
   }

   public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function getName()
    {
        return '';
    }

}
