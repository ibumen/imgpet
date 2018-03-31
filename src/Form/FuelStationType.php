<?php

namespace App\Form;

use App\Entity\FuelStation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{TextType, SubmitType};

class FuelStationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('stationName', TextType::class, array("label"=>"Station Name: ", ))
                ->add('Add', SubmitType::class, array("label"=>"Add Station"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => FuelStation::class,
        ));
    }

}
