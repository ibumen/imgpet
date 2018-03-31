<?php

namespace App\Form;

use App\Entity\{
    Truck, 
    Expense    
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
    SubmitType,
    TextareaType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ExpenseType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('dateOfExpense', DateTimeType::class, array("label" => "Date of Expense: ",
                    "date_format" => "yyyy-MM-dd",
                    "date_widget" => "single_text",
                    "format" => "yyyy-MM-dd HH:mm:ss",
                    "html5" => false,
                    "time_widget" => "single_text",
                    "widget" => "single_text",
                    "attr" => array("class" => "date")
                ))
                ->add('amount', MoneyType::class, array("label" => "Amount Expended: ", "currency" => "NGN", "grouping" => true))
                ->add('reason', TextareaType::class, array("label" => "Reason for Expense", "required" => true));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
// ... adding the name field if needed
            $expense = $event->getData();
            $form = $event->getForm();

            if (!$expense || null === $expense->getId()) {
                        $form->add('addExpense', SubmitType::class, array("label" => "Add Expense"));
            } else {
                $form->add('modifyExpense', SubmitType::class, array("label" => "Modify Expense"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Expense::class,
        ));
    }

}
