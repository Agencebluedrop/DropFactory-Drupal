<?php

namespace App\Form;

use App\Entity\Remote\Platform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('gitRepositoryURL')
            ->add('gitRepositoryBranch')
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'Create Platform']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Platform::class,
        ]);
    }
}
