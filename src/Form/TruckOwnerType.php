<?php

namespace App\Form;

use App\Entity\TruckOwner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextType, SubmitType};

class TruckOwnerType extends AbstractType {
    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('fname', TextType::class, array("label"=>"First Name: ", ))
                ->add('lname', TextType::class, array("label"=>"Last Name: ", ))
                ->add('oname', TextType::class, array("label"=>"Other Names (if any): ", "required"=>false))
                ->add('title', ChoiceType::class, array("label"=>"Title: ", 
                                                        "required"=>false, 
                                                        "attr"=> array("class"=>"custom-select"),
                                                        "choices"=> TruckOwnerType::getUtilTitles(),
                                                        'choice_label' => function ($value, $key, $index) {
                                                                            return $value;
                                                                            },
                                                        "placeholder"=> "Select Title"))
                ->add('Register', SubmitType::class, array("label"=>"Register"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => TruckOwner::class,
        ));
    }

}
