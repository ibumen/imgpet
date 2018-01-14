<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{FileType, ChoiceType, TextType, MoneyType, SubmitType};

class ProductType extends AbstractType {
    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('productName', TextType::class, array("label"=>"Product Name: ", ))
                ->add('productCode', TextType::class, array("label"=>"Product Code: ",))
                ->add('unitPrice', MoneyType::class, array("label"=>"Unit Price: ","currency"=>"NGN", "grouping"=>true ))
                ->add('unitMetric', ChoiceType::class, array("label"=>"Metric: ", 
                                                        "attr"=> array("class"=>"custom-select"),
                                                        "choices"=> ProductType::getUtilProductQuantityMetrics(),
                                                        'choice_label' => function ($value, $key, $index) {
                                                                            return ucfirst($value);
                                                                            },
                                                        "placeholder"=> false,
                                                        ))
                ->add('Add', SubmitType::class, array("label"=>"Add"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
        ));
    }

}
