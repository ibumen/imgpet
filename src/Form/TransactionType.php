<?php

namespace App\Form;

use App\Entity\{
    Order,
    User,
    Transaction
};
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface
};
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{
    DateTimeType,
    MoneyType,
    ChoiceType,
    SubmitType
};
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TransactionType extends AbstractType {

    use \App\Utility\Utils;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        if (isset($options['onorder'])&& $options['onorder']==false) {
            $builder->add('transDate', DateTimeType::class, array("label" => "Transaction Date: ",
                        "date_format" => "yyyy-MM-dd",
                        "date_widget" => "single_text",
                        "format" => "yyyy-MM-dd HH:mm:ss",
                        "html5" => false,
                        "time_widget" => "single_text",
                        "widget" => "single_text",
                        "attr" => array("class" => "date")
                    ))
                    ->add('order', EntityType::class, array("label" => "Order: ",
                        "attr" => array("class" => "custom-select"),
                        'class' => Order::class,
                        'query_builder' => function (EntityRepository $er) use ($options) {
                            $qb = $er->createQueryBuilder('t')
                                    ->where('t.orderStatus = :status')
                                    ->setParameter('status', 'active');
                            if (isset($options['customerid'])) {
                                $qb = $qb->innerJoin(\App\Entity\Customer::class, 'c', \Doctrine\ORM\Query\Expr\Join::WITH, '(t.customer= c)')
                                        ->andWhere('c.id=:cust')
                                        ->setParameter('cust', $options['customerid']);
                            }
                            $qb = $qb->orderBy('t.orderDate', 'DESC')
                                    ->addOrderBy('t.id', 'DESC');
                            return $qb;
                        },
                        'choice_label' => function ($order) {
                            return $order->getOid() . " (" . $order->getCustomer()->getFullName() . ")";
                        },
                        'choice_attr' => function ($order) {
                            return ["data-amtdue" => number_format($order->getAmountDue(), 2)];
                        },
                        "placeholder" => "Select Order"));
        }
        $builder->add('amountPaid', MoneyType::class, array("label" => "Amount Paid: ", "currency" => "NGN", "grouping" => true))
                ->add('paymentMethod', ChoiceType::class, array("label" => "Payment Method: ",
                    "attr" => array("class" => "custom-select"),
                    "choices" => ProductType::getUtilPaymentOptions(),
                    'choice_label' => function ($value, $key, $index) {
                        return strtoupper($value);
                    },
                    "placeholder" => false));
        if (isset($options['onorder']) && $options['onorder']==false) {
            $builder->add('Submit', SubmitType::class, array("label" => "Submit"));
        }
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Transaction::class,
            'customerid' => null,
            'onorder'=>true
        ));
    }

}
