<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Image;
use App\Form\DataTransformer\FilenameToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fileName', FileType::class, [
            'label' => 'Image (.png or .jpg)',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PNG or JPG file',
                ]),
            ],
            'invalid_message' => 'That is not a valid filename',
        ])
            ->add('enregistrer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'Enregistrer',
                'row_attr' => ['class' => 'd-grid']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
