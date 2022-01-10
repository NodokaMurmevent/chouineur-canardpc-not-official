<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('excerpt')
            ->add('link')
            ->add('guid')
            ->add('chouineurs')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('isFreeContent')
            ->add('is404')
            ->add('imageUrl')
            ->add('imageALaUne')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
