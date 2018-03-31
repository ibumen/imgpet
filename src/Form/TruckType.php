<?php

namespace App\Form;

use App\Entity\{
    TruckOwner,
    Truck
};
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType,
    SubmitType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TruckType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('truckNumber', TextType::class, array("label" => "Truck Number: ",))
                /*->add('truckOwner', EntityType::class, array("label" => "Truck Owner: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => TruckOwner::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                                ->orderBy('t.fname', 'ASC')
                                ->orderBy('t.lname', 'ASC');
                    },
                    'choice_label' => function ($towner) {
                        return $towner->getFullName();
                    },
                    "placeholder" => "Select Truck Owner"))*/
                ->add('driverName', TextType::class, array("label" => "Driver's Name: "))
                ->add('truckStatus', ChoiceType::class, array("label" => "Status: ",
                    "required" => true,
                    "expanded" => true,
                    "attr" => array("class" => "custom-control"),
                    "choices" => TruckType::getUtilTruckStatus(),
                    'choice_label' => function ($value, $key, $index) {
                        return $value;
                    },
                    "placeholder" => false))
                ->add('Add Truck', SubmitType::class, array("label" => "AddTruck"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Truck::class,
        ));
    }

}
