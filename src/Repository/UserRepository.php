<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, User::class);
    }

    public function hasRecord($user) {

        $loading = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\Loading::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.createdBy = t)')
                ->where('o.createdBy = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();

        $loadingrecord = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\LoadingRecord::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.createdBy = t OR o.finishedBy= t)')
                ->where('o.createdBy = :usr')->setParameter('usr', $user)
                ->orWhere('o.finishedBy = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        $order = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\Order::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.closedBy = t OR o.salesPerson= t)')
                ->where('o.closedBy = :usr')->setParameter('usr', $user)
                ->orWhere('o.salesPerson = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        $productdistribution = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\ProductDistribution::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.createdBy = t)')
                ->where('o.createdBy = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        $refund = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\Refund::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.committedBy = t)')
                ->where('o.committedBy = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        $transaction = $this->createQueryBuilder('t')
                ->innerJoin(\App\Entity\Transaction::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(o.committedBy = t)')
                ->where('o.committedBy = :usr')->setParameter('usr', $user)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        //echo $loading[0]->getUsername();
        //exit();
        if (count($transaction) || count($refund) || count($productdistribution) || count($order) || count($loadingrecord) || count($loading)) {
            return true;
        }
        return false;
    }

    /*
      public function findBySomething($value)
      {
      return $this->createQueryBuilder('u')
      ->where('u.something = :value')->setParameter('value', $value)
      ->orderBy('u.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */
}
