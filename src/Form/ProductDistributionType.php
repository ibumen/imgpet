<?php

namespace App\Form;

use App\Entity\{
    Loading,
    LoadingRecord,
    ProductDistribution,
    FuelStation,
    Order
};
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    FormEvent,
    FormEvents,
    CallbackTransformer
};
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    DateTimeType,
    MoneyType,
    IntegerType,
    SubmitType,
    TextType,
    TextareaType,
    HiddenType,
    ChoiceType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductDistributionType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->setAction($options['action'])
                ->add('deliveryDate', DateTimeType::class, array("label" => "Delivery Date (Not earlier than " . $options['loadingrecord']->getDeliveryDate()->format('d-m-Y H:i:sa') . "): ",
                    "date_format" => "yyyy-MM-dd",
                    "date_widget" => "single_text",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                    "html5" => false,
                    "time_widget" => "single_text",
                    "widget" => "single_text",
                    "attr" => array("class" => "date"),
                    "required" => true,
                ))
                ->add('order', EntityType::class, array("label" => "Delivery on Order: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => Order::class,
                    'query_builder' => function (EntityRepository $er) use ($options) {
                        $qb = $er->createQueryBuilder('t')
                                ->where('t.orderDeliveryStatus <> :status')
                                ->andWhere('t.product = :prod')
                                ->setParameter('status', 'delivered')
                                ->setParameter('prod', $options['loadingrecord']->getLoading()->getProduct());
                        $qb = $qb->orderBy('t.orderDate', 'DESC')
                                ->addOrderBy('t.id', 'DESC');
                        return $qb;
                    },
                    'choice_label' => function ($order) {
                        return $order->getOid() . "/" . $order->getCustomer()->getFullName() . "/" . $order->getOrderDate()->format('d-m-Y') . "/" . number_format($order->getQuantity() - $order->getQuantityDelivered()) . $order->getProduct()->getUnitMetric();
                    },
                    'choice_attr' => function ($order) use ($options) {
                        return ["data-qty" => ($order->getQuantity() - $order->getQuantityDelivered()),
                            "data-loc" => ((trim($options['repo']->getDeliveryLocation($order)) !=="")?($options['repo']->getDeliveryLocation($order)):($order->getDeliveryLocation()))];
                    },
                    "placeholder" => "Select Order"))
                ->add('quantityDelivered', IntegerType::class, array("label" => "Delivered Quantity", "label_attr" => array("id" => "maxq"), "scale" => 0,
                    'attr' => array("data-max" => ($options['loadingrecord']->getDeliveredQuantity() - $options['loadingrecord']->getDistributedQuantity()))))
                ->add('fromstation', ChoiceType::class, array("expanded" => true,
                    "multiple" => true,
                    "choices" => array("Select from list of fuel stations" => false),
                    "mapped" => false,
                    "required" => false,
                    "label" => false
                ))
                ->add('locationname', EntityType::class, array("mapped" => false, "label" => "Delivery Location: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => FuelStation::class,
                    'query_builder' => function (EntityRepository $er) use ($options) {
                        $qb = $er->createQueryBuilder('t');
                        $qb = $qb->orderBy('t.stationName', 'DESC');
                        return $qb;
                    },
                    'choice_label' => function ($station) {
                        return $station->getStationName();
                    },
                    'choice_attr' => function ($station) {
                        return ["data-station" => $station->getStationName()];
                    },
                    "placeholder" => "Select Location"))
                ->add('location', TextType::class, array("label" => "Delivery Location", "required" => true))
                ->add('loadingRecord', HiddenType::class, array("data" => $options['loadingrecord']->getId()))
                ->add('addentry', SubmitType::class, array("label" => "Add Entry"));

        $builder->get('location')
                ->addModelTransformer(new CallbackTransformer(
                        function ($data) {
                    // transform the array to a string
                    return trim($data);
                }, function ($data) {
                    // transform the string back to an array
                    return trim($data);
                }
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => ProductDistribution::class,
            'attr' => ['id' => 'newdistfrm'],
            'loadingrecord' => null,
            'action' => "",
            'repo'=> null,
        ));
    }

}
