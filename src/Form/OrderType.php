<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('address', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Adresse',
                    'class' => 'form-control',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        ]),
                ]
            ])
            ->add('zipcode', IntegerType::class,[
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Code postal',
                    'class' => 'form-control',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 5,
                        'max' => 5,
                        ]),
                ]
            ])
            ->add('city', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Ville',
                    'class' => 'form-control',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        ]),
                ]
            ])
            ->add('instruction');
    }

    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }

}