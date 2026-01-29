<?php

namespace App\Form;

use App\Entity\TaskWithPriority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskWithPriorityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter task title',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Enter task description',
                    'rows' => 4,
                    'class' => 'form-control'
                ]
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priority',
                'choices' => TaskWithPriority::PRIORITIES,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice;
                },
                'placeholder' => 'Select priority',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskWithPriority::class,
        ]);
    }
}
