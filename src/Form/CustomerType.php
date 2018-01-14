<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    FormEvent,
    FormEvents
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType,
    TextType,
    SubmitType,
    TelType,
    TextareaType,
    EmailType
};

class CustomerType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('fname', TextType::class, array("label" => "First Name: ",))
                ->add('lname', TextType::class, array("label" => "Last Name: ",))
                ->add('oname', TextType::class, array("label" => "Other Names (if any): ", "required" => false))
                ->add('title', ChoiceType::class, array("label" => "Title: ",
                    "required" => false,
                    "attr" => array("class" => "custom-select"),
                    "choices" => CustomerType::getUtilTitles(),
                    'choice_label' => function ($value, $key, $index) {
                        return $value;
                    },
                    "placeholder" => "Select Title"))
                ->add('email', EmailType::class, array("label" => "Email: ", "required" => false))
                ->add('mobileNo', TelType::class, array("label" => "Mobile No.: ", "required" => false))
                ->add('address', TextareaType::class, array("label" => "Address: ", "required" => false))
                ->add('status', ChoiceType::class, array("label" => "Status: ",
                    "placeholder" => false,
                    "attr" => array("class" => "custom-control"),
                    "required" => false,
                    "choices" => CustomerType::getUtilCustomerStatus(),
                    'choice_label' => function ($value, $key, $index) {
                        return $value;
                    },
                    "expanded" => true));
                

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
// ... adding the name field if needed
            $customer = $event->getData();
            $form = $event->getForm();

            if (!$customer || null === $customer->getId()) {
                $form->add('Register', SubmitType::class, array("label" => "Register"));
            }else{
                $form->add('Modify', SubmitType::class, array("label" => "Modify"));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Customer::class,
        ));
    }

}
