<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Image;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', null, [
                'label' => 'Автор',
            ])
            ->add('text', null, ['label' => 'Комментарий'])
            ->add('email',EmailType::class)
//            ->add('createdAt')
//            ->add('photoFilename')
//            ->add('conference')
            ->add('photoFilename', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                new Image(['maxSize' => '4096k'])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Сохранить'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
