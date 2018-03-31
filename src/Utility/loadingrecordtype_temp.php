<?php

namespace App\Utility;

use App\Entity\{
    Loading,
    LoadingRecord
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
    TextType,
    TextareaType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class loadingrecordtype_temp extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('deliveryDate', DateTimeType::class, array("label" => "Delivery Date (If Delivered): ",
                    "date_format" => "yyyy-MM-dd",
                    "date_widget" => "single_text",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                    "html5" => false,
                    "time_widget" => "single_text",
                    "widget" => "single_text",
                    "attr" => array("class" => "date")
                ))
                ->add('truckNo', TextType::class, array("label" => "Truck No.: "))
                ->add('driverName', TextType::class, array("label" => "Driver Name: "))
                ->add('meterTicket', TextType::class, array("label" => "Meter Ticket: "))
                ->add('loading', EntityType::class, array("label" => "Loading: ",
                    "attr" => array("class" => "custom-select"),
                    'class' => Loading::class,
                    'query_builder' => function (EntityRepository $er) use ($options) {
                        $qb = $er->createQueryBuilder('t')
                                ->where('t.loadingStatus = :status')
                                ->setParameter('status', 'loading');
                        $qb = $qb->orderBy('t.loadingDate', 'DESC')
                                ->addOrderBy('t.id', 'DESC');
                        return $qb;
                    },
                    'choice_label' => function ($loading) {
                        return $loading->getLid() . " (" . $loading->getLoadingDate()->Format('Y-m-d h:i:s') . ")";
                    },
                    'choice_attr' => function ($loading) {
                        return ["data-lid" => $loading->getLid()];
                    },
                    "placeholder" => "Select Loading",
                    "disabled" => $options['postloading']))
                ->add('quantity', IntegerType::class, array("label" => "Quantity: ", "scale" => 0))
                ->add('deliveredQuantity', IntegerType::class, array("label" => "Delivered Quantity: ", "scale" => 0))
                ->add('costOfLogistics', MoneyType::class, array("label" => "Cost of Logistics: ", "currency" => "NGN", "grouping" => true))
                ->add('logisticsPaid', MoneyType::class, array("label" => "Payment for Logistics: ", "currency" => "NGN", "grouping" => true));
        if (isset($options['closingremark']) && $options['closingremark'] != 'none') {
            $builder->add('finishingRemark', TextareaType::class, array("label" => "Closing Remark:", "required" => false));
        }


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            // ... adding the name field if needed
            $loadingrecord = $event->getData();
            $form = $event->getForm();

            if (!$loadingrecord || null === $loadingrecord->getId()) {
                $form->add('Add Entry', SubmitType::class, array("label" => "Add Entry"));
            } else {
                $form->add('Update Entry', SubmitType::class, array("label" => "Update Entry"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => LoadingRecord::class,
            'closingremark' => null,
            'postloading' => FALSE,
        ));
    }

}
