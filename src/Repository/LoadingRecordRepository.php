<?php

namespace App\Repository;

use App\Entity\LoadingRecord;
use App\Entity\Loading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LoadingRecordRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, LoadingRecord::class);
    }

    public function findOneByDateRecorded() {
        return $this->createQueryBuilder('c')
                        ->orderBy('c.dateRecorded', 'DESC')
                        ->addOrderBy('c.id', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getResult();
    }

    public function findByDateOfLoading() {
        return $this->createQueryBuilder('c')
                        ->innerJoin(\App\Entity\Loading::class, 'o', \Doctrine\ORM\Query\Expr\Join::WITH, '(c.loading = o)')
                        ->orderBy('o.loadingDate', ((isset($order) && $order == 'ASC') ? ('ASC') : ('DESC')))
                        ->addOrderBy('c.id', ((isset($order) && $order == 'ASC') ? ('ASC') : ('DESC')))
                        ->getQuery()
                        ->getResult();
    }

    public function isUniqueTruckNo($truckno, $id = null) {
        $builder = $this->createQueryBuilder('o')
                ->where('o.truckNo=:tno');
        if (isset($id)) {
            $builder->andWhere('o.id != :id')
                    ->setParameter('id', $id);
        }
        $result = $builder->setParameter('tno', $truckno)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        if (count($result)) {
            return false;
        } else {
            return true;
        }
    }

    public function isUniqueMeterTicket($ticket, $id = null) {
        $builder = $this->createQueryBuilder('o')
                ->where('o.meterTicket=:ticket');
        if (isset($id)) {
            $builder->andWhere('o.id != :id')
                    ->setParameter('id', $id);
        }
        $result = $builder->setParameter('ticket', $ticket)
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        if (count($result)) {
            return false;
        } else {
            return true;
        }
    }

    public function isConsistentQuantity($quantity, $loadingquantity, $lid, $id = null) {

        $query1 = $this->getEntityManager()->createQuery("SELECT SUM(o.quantity) as totalquantity from \App\Entity\LoadingRecord o WHERE o.loading = :lid "
                . ((isset($id)) ? (" AND (o.id <> :id)") : ("")));
        $query1 = $query1->setParameter('lid', $lid);
        
        if (isset($id)) {
            $query1 = $query1->setParameter('id', $id);
        }
        //echo $query1->getSQL(); echo $lid->getId();exit();
        $result = $query1->getResult();
        $totalquantity = $result[0]['totalquantity'];
        //echo $totalquantity."--".$quantity; exit;
        if (($totalquantity + $quantity) > $loadingquantity) {
            return false;
        }
        return true;
    }
    
    public function undoDelivery($loadingrecordid){
        $query = $this->getEntityManager()->createQuery("UPDATE \App\Entity\LoadingRecord o SET o.deliveryDate = NULL, o.deliveredQuantity = NULL, o.loadingStatus = 'loading', o.logisticsPaid=0, o.finishingRemark = NULL, o.finishedBy = NULL where (o.id = :ld1)");
        $query = $query->setParameter('ld1', $loadingrecordid);
        return $query->execute();
    }

    public function deleteLoadingRecord($loadingid) {
        $query = $this->getEntityManager()->createQuery("DELETE \App\Entity\LoadingRecord o where (o.id = :ld1 AND o.loadingStatus='loading' )");
        $query = $query->setParameter('ld1', $loadingid);
        return $query->execute();
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
