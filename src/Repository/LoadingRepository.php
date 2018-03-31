<?php

namespace App\Repository;

use App\Entity\Loading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LoadingRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Loading::class);
    }

    public function findOneByDateRecorded() {
        return $this->createQueryBuilder('c')
                        ->orderBy('c.dateRecorded', 'DESC')
                        ->addOrderBy('c.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    public function deleteLoading($loadingid) {
        $query = $this->getEntityManager()->createQuery("DELETE \App\Entity\Loading o where (o.id = :ld1 AND NOT EXISTS(SELECT t from \App\Entity\LoadingRecord t where t.loading = :ld2 ))");
        $query = $query->setParameter('ld1', $loadingid)->setParameter('ld2', $loadingid);
        return $query->execute();
    }

    public function getQuantityUnallocated($loading) {
        $query1 = $this->getEntityManager()->createQuery("SELECT SUM(o.quantity) as totalquantity from \App\Entity\LoadingRecord o WHERE (o.loading = :loading)");
            $query1 = $query1->setParameter('loading', $loading);
        $result = $query1->getResult();
        return $loading->getQuantity()-$result[0]['totalquantity'];
    }

    public function countLoadingFromStation($station){
        $query1 = $this->getEntityManager()->createQuery("SELECT COUNT(o) as totalrecs from \App\Entity\Loading o WHERE (o.loadingDepot = :station)");
            $query1 = $query1->setParameter('station', $station);
        $result = $query1->getResult();
        return $result[0]['totalrecs'];
    }
    /*
      public function findBySomething($value)
      {
      return $this->createQueryBuilder('o')
      ->where('o.something = :value')->setParameter('value', $value)
      ->orderBy('o.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */
}
