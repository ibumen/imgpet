<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomerRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Customer::class);
    }

    public function findOneByDateReg() {
        return $this->createQueryBuilder('c')
                        ->orderBy('c.dateReg', 'DESC')
                        ->addOrderBy('c.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    /*
      public function findBySomething($value)
      {
      return $this->createQueryBuilder('c')
      ->where('c.something = :value')->setParameter('value', $value)
      ->orderBy('c.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */
}
