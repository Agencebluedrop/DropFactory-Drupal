<?php

namespace App\Form;

use App\Entity\Remote\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class SiteEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('domain', null, [
                'disabled' => true,
                'label' => 'Domain',
            ])
            ->add('aliases', CollectionType::class, [
                'entry_type' => AliasType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, //so that Symfony uses $site->addAlias and $site->removeAlias
                'prototype' => true,
                'label' => 'Aliases',
                'required' => false,
            ])
            ->add('htpasswd', FormType::class, [
                'label' => 'HTTP authentication (htpasswd)',
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->get('htpasswd')
            ->add('username', TextType::class, [
                'label' => 'Username',
                'required' => false,
            ])
            ->add('password', TextType::class, [
                'label' => 'Password',
                'required' => false,
            ])
        ;

        // by default, action does not appear in the <form> tag
        // you can set this value by passing the controller route
        $builder->setAction($options['action']);

        $builder->add(
            'save',
            SubmitType::class,
            ['label' => 'Edit Site']
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}
