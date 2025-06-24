<?php

namespace App\Form;

use App\Entity\Remote\Site;
use App\Entity\Remote\Alias;
use App\Entity\Remote\Profile;
use App\Entity\Remote\Platform;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('domain')
            // ->add('aliases')
            ->add('platform')
            ->add('install_profile')
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'French' => 'FR',
                    'English' => 'EN',
                    'Spanish' => 'ES',
                ],
            ])
        ;

        // On ajoute le champ install_profile en fonction de la plateforme choisie

        // see: https://symfony.com/doc/6.4/form/dynamic_form_modification.html#form-events-submitted-data

        $formModifier = function (FormInterface $form, ?Platform $platform = null): void {
            $installProfiles = null === $platform ? [] : $platform->getProfiles();

            $form->add('install_profile', EntityType::class, [
                'class' => Profile::class,
                'choice_label' => 'name',
                'choices' => $installProfiles,
            ]);
        };

        $builder->addEventListener(
            FormEvents::POST_SET_DATA, 
            function (FormEvent $event) use ($formModifier) : void {
                // this would be your entity, i.e. Site
                /** @var Site $data */
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getPlatform());
            }
        );

        $builder->get('platform')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) : void {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $platform = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback function!
                $formModifier($event->getForm()->getParent(), $platform);
            }
        );

        // by default, action does not appear in the <form> tag
        // you can set this value by passing the controller route
        $builder->setAction($options['action']);

        $builder->add(
            'save',
            SubmitType::class,
            ['label' => 'Create Site']
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}
