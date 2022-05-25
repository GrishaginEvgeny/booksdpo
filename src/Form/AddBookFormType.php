<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class AddBookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,
            [
                'label' => 'Название',
            ])
            ->add('genre', TextType::class,
                [
                    'label' => 'Жанр',
                ])
            ->add('posterFile', FileType::class,
            [
                'label' => 'Постер',
                'required' => false,
                'constraints' => [ new Image([
                    'mimeTypes' => ['image/png','image/jpeg','image/jpg','image/bmp'],
                    'mimeTypesMessage' => 'Загруженная вами обложка имеет некорректное расширение',
                ])],
                'mapped' => false,

            ])
            ->add('bookFile',FileType::class,
                [
                    'label' => 'Книга',
                    'required' => false,
                    'constraints' => [ new File([
                        'mimeTypes' => ['application/pdf','application/epub+zip','application/msword','text/plain'],
                        'mimeTypesMessage' => 'Загруженный вами файл книги имеет некорректное расширение',
                    ]),
                        new NotNull(['message' => 'Вы ничего не загрузили'])]
                    ,
                    'mapped' => false,
                ])
            ->add('author', TextType::class,
                [
                    'label' => 'Автор',
                ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event)
        {
            $post = $event->getData();
            $form = $event->getForm();

            if($post){
                $form->remove('posterFile')
                    ->remove('bookFile')
                    ->add('editedPosterFile', FileType::class,
                        [
                            'label' => 'Новый постер',
                            'required' => false,
                            'constraints' => [ new Image([
                                'mimeTypes' => ['image/png','image/jpeg','image/jpg','image/bmp'],
                                'mimeTypesMessage' => 'Загруженная вами обложка имеет некорректное расширение',
                            ])],
                            'mapped' => false,
                            'help' => 'Если не хотите менять постер, оставьте его незаполненным',
                        ])
                    ->add('editedBookFile',FileType::class,
                        [
                            'label' => 'Новый файл книги',
                            'required' => false,
                            'constraints' => [ new File([
                                'mimeTypes' => ['application/pdf','application/epub+zip','application/msword','text/plain'],
                                'mimeTypesMessage' => 'Загруженный вами файл книги имеет некорректное расширение',
                            ])],
                            'mapped' => false,
                            'help' => 'Если не хотите менять файл книги, оставьте его незаполненным',
                        ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
