<?php

namespace App\Form;

use App\Entity\Remote\Alias;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AliasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('domain', TextType::class, [
            'label' => false,
            'required' => false,
            //add check domains hostname
            'constraints' => [
                new Assert\Hostname(
                    message: 'The alias "{{ value }}" is not a valid domain name.',
                    requireTld: true,
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alias::class,
        ]);
    }
}