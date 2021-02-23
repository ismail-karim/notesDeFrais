<?php

namespace App\Repository;

use App\Entity\RechercheNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RechercheNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method RechercheNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method RechercheNote[]    findAll()
 * @method RechercheNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechercheNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RechercheNote::class);
    }

    // /**
    //  * @return RechercheNote[] Returns an array of RechercheNote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RechercheNote
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
