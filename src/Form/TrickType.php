<?php

namespace App\Form;

use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'title'])
            ->add('slug', TextType::class, [
                'label' => 'slug'])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
                ])
            ->add('enregistrer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'Enregistrer',
                'row_attr' => ['class' => 'd-grid']
            ])

        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
