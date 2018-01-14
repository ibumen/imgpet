<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    FileType,
    ChoiceType,
    TextType,
    SubmitType
};

class UserType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('username', TextType::class, array("label" => "Username: ",))
                ->add('plainPassword', TextType::class, array("label" => "Password: ", "empty_data" => "password"))
                ->add('fname', TextType::class, array("label" => "First Name: ",))
                ->add('lname', TextType::class, array("label" => "Last Name: ",))
                ->add('oname', TextType::class, array("label" => "Other Names (if any): ", "required" => false))
                ->add('title', ChoiceType::class, array("label" => "Title: ",
                    "required" => false,
                    "attr" => array("class" => "custom-select"),
                    "choices" => UserType::getUtilTitles(),
                    'choice_label' => function ($value, $key, $index) {
                        return $value;
                    },
                    "placeholder" => "Select Title"))
                /* ->add('profilepix', FileType::class, array("label"=>"Upload Photo: ", "required"=>false)) */
                ->add('roles', ChoiceType::class, array("label" => "Role: ",
                    "placeholder" => "Assign Role",
                    "multiple"=> true,
                    "attr" => array("class" => "custom-select"),
                    "choices" => UserType::getUtilUserRoles(),
                    'choice_label' => function ($value, $key, $index) {
                        //var_dump($value); exit();
                        $v = str_replace("_"," ",$value);
                        $v = substr($v, 5);
                        return strtoupper($v);
                    }))
                ->add('status', ChoiceType::class, array("label" => "Status: ",
                    "placeholder" => false,
                    "attr" => array("class" => "custom-control"),
                    "required" => false,
                    "choices" => UserType::getUtilUserStatus(),
                    'choice_label' => function ($value, $key, $index) {
                        return $value;
                    },
                    "expanded" => true))
                ->add('Register', SubmitType::class, array("label" => "Register"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }

}
