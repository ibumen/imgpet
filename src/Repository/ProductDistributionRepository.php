<?php

namespace App\Repository;

use App\Entity\ProductDistribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductDistributionRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, ProductDistribution::class);
    }

    public function getDeliveryLocation($order) {
        $result = $this->createQueryBuilder('o')
                        ->where('o.order=:order')
                        ->setParameter('order', $order)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
        if(count($result)){
            return $result[0]->getLocation();
        }
        return "";
    }

    /*
      public function findBySomething($value)
      {
      return $this->createQueryBuilder('p')
      ->where('p.something = :value')->setParameter('value', $value)
      ->orderBy('p.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */
}
