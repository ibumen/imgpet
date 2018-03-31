<?php

namespace App\Form;

use App\Entity\LoadingStation;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface, FormEvent, FormEvents};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    SubmitType
};

class LoadingStationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('stationName', TextType::class, array("label" => "Loading Depot Name: ",));
        

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
// ... adding the name field if needed
            $station = $event->getData();
            $form = $event->getForm();

            if (!$station || null === $station->getId()) {
                $form->add('Add', SubmitType::class, array("label" => "Add Depot"));
            } else {
                $form->add('modifyDepot', SubmitType::class, array("label" => "Modify Depot"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => LoadingStation::class,
        ));
    }

}
