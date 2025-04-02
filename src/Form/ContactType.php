<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'empty_data' => '',
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Jean Dupont',
                ]
            ])
            ->add('email', EmailType::class,[
                'empty_data' => '',
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@exemple.fr',
                ]
            ])
            ->add('message', TextareaType::class,[
                'empty_data' => '',
                'label' => 'Message',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Message',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
        ]);
    }
}
