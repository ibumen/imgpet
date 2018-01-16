<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TransactionRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Transaction::class);
    }

    public function getAmtPaidBefore($tid, $oid) {
        return $this->createQueryBuilder('t')->select('sum(t.amountPaid) as sumamt')
                        ->where('t.id < :tid')
                        ->andWhere('t.order= :oid')
                        ->setParameter('tid', $tid)
                        ->setParameter('oid', $oid)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    public function getAmtPaidAfter($tid, $oid) {
        return $this->createQueryBuilder('t')->select('sum(t.amountPaid) as sumamt')
                        ->where('t.id <= :tid')
                        ->andWhere('t.order= :oid')
                        ->setParameter('tid', $tid)
                        ->setParameter('oid', $oid)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    public function findOneByDateRecorded() {
        return $this->createQueryBuilder('t')
                        ->orderBy('t.dateRecorded', 'DESC')
                        ->addOrderBy('t.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    public function findLastTransaction($id = null, $on = 'customer') {

        $qbuilder = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\Order::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(t.order = o)')
                ->innerJoin(\App\Entity\Customer::class, 'c', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.customer= c)')
                ->orderBy('t.dateRecorded', 'DESC')
                ->addOrderBy('t.id', 'DESC')
                ->setMaxResults(1);

        if (isset($id)) {
            if ($on == 'order')
                $qbuilder = $qbuilder->where('o.id = :ord')->setParameter('ord', $id);
            else
                $qbuilder = $qbuilder->where('c.id = :cust')->setParameter('cust', $id);
        }
//echo $qbuilder->getQuery()->getSQL(); exit;
        return $qbuilder->getQuery()->getResult();
    }

    /*
      public function findBySomething($value)
      {
      return $this->createQueryBuilder('t')
      ->where('t.something = :value')->setParameter('value', $value)
      ->orderBy('t.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */
}
