<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Creation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('description')
            ->add('thumbnailFile', FileType::class, [
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'divisor' => 100,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->autoDateTimeSold(...))
        ;
    }

    public function autoDateTimeSold(PostSubmitEvent $event):void
    {
        $data = $event->getData();
        if(!($data instanceof Creation)){
            return;
        }
        $data->setUpdatedAt(new \DateTimeImmutable());
        $data->setSold(false);
        if(!$data->getId()){
            $data->setCreatedAt(new \DateTimeImmutable());
        }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Creation::class,
        ]);
    }
}
