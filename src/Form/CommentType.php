<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Creation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('comment')
            ->addEventListener(FormEvents::POST_SUBMIT, $this->autoDateTime(...))
        ;
    }

    public function autoDateTime(PostSubmitEvent $event):void
    {
        $data = $event->getData();
        if(!($data instanceof Comment)){
            return;
        }
        if(!$data->getId()){
            $data->setCreatedAt(new \DateTimeImmutable());
            $data->setValidated(false);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
