<?php

namespace App\Form;

use App\Entity\{
    Product,
    Loading,
    LoadingStation
};
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    FormEvent,
    FormEvents
};
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    DateTimeType,
    MoneyType,
    IntegerType,
    SubmitType,
    TextType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class LoadingType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('loadingDate', DateTimeType::class, array("label" => "Loading Date: ",
                    "date_format" => "yyyy-MM-dd",
                    "date_widget" => "single_text",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                    "html5" => false,
                    "time_widget" => "single_text",
                    "widget" => "single_text",
                    "attr" => array("class" => "date")
                ))
                ->add('loadingDepot', EntityType::class, array("label" => "Depot: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => LoadingStation::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                                ->orderBy('t.stationName', 'ASC');
                    },
                    'choice_label' => function ($station) {
                        return $station->getStationName();
                    },
                    "placeholder" => "Select Depot"))
                ->add('product', EntityType::class, array("label" => "Product: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => Product::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                                ->orderBy('t.productName', 'ASC');
                    },
                    'choice_label' => function ($product) {
                        return $product->getProductName();
                    },
                    "placeholder" => "Select Product"))
                ->add('quantity', IntegerType::class, array("label" => "Quantity: ", "scale" => 0))
                ->add('purchasePrice', MoneyType::class, array("label" => "Purchase Price: ", "currency" => "NGN", "grouping" => true));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        // ... adding the name field if needed
            $loading = $event->getData();
            $form = $event->getForm();

            if (!$loading || null === $loading->getId()) {
                $form->add('Register Loading', SubmitType::class, array("label" => "Register Loading"));
            } else {
                $form->add('Update Loading', SubmitType::class, array("label" => "Update Loading"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Loading::class,
        ));
    }

}
