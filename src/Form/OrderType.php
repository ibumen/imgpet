<?php

namespace App\Form;

use App\Entity\{
    Order,
    Customer,
    Product
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
    ChoiceType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrderType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('orderDate', DateTimeType::class, array("label" => "Order Date: ",
                    "date_format" => "yyyy-MM-dd",
                    "date_widget" => "single_text",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                    "html5" => false,
                    "time_widget" => "single_text",
                    "widget" => "single_text",
                    "attr"=> array("class"=>"date")
                ))
                ->add('customer', EntityType::class, array("label" => "Customer/Agent: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => Customer::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                                ->where('t.status = :status')
                                ->orderBy('t.fname', 'ASC')
                                ->addOrderBy('t.lname', 'ASC')
                                ->setParameter('status', 'active');
                    },
                    'choice_label' => function ($customer) {
                        return $customer->getFullName();
                    },
                    "placeholder" => "Select Customer/Agent"))
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
                    'choice_attr' => function ($product) {
                        return ['data-price' => $product->getUnitPrice()];
                    },
                    "placeholder" => "Select Product"))
                ->add('unitPrice', MoneyType::class, array("label" => "Unit Price: ", "currency" => "NGN", "grouping" => true))
                ->add('quantity', IntegerType::class, array("label" => "Quantity: ", "scale" => 0));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
// ... adding the name field if needed
            $order = $event->getData();
            $form = $event->getForm();

            if (!$order || null === $order->getId()) {
                $form->add('maketrans', ChoiceType::class, array("expanded"=>true,
                    "multiple"=>true,
                    "choices"=> array("Include Transaction"=>true),
                    "mapped"=>false,
                    "required"=>false,
                    "label"=>false
                    ))
                ->add('Transaction', TransactionType::class, array("mapped"=>false,
                    "required"=>false,
                    "label"=>false))
                ->add('Place Order', SubmitType::class, array("label" => "Place Order"));
            } else {
                $form->add('Modify Order', SubmitType::class, array("label" => "Modify Order"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
        ));
    }

}
