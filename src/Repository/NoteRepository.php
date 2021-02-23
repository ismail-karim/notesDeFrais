<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Note;
use App\Entity\RechercheNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    public function findWithPagination(RechercheNote $rechercheNote, $idUser) : Query
    {
        $req = $this->createQueryBuilder('n')
                    ->orderBy('n.dateCreation', 'DESC')
                    ->andWhere('n.user = :user')
                    ->setParameter('user', $idUser);

        if($rechercheNote->getDate())
        {
            $req = $req->andWhere('n.dateCreation = :date')
                ->setParameter('date', $rechercheNote->getDate());
        }
        if($rechercheNote->getEtat())
        {
            $req = $req->andWhere('n.etat = :etat')
                ->setParameter('etat', $rechercheNote->getEtat());
        }

           return  $req->getQuery();
    }

    public function findNoteByEtat($etat)
    {
        return $this->createQueryBuilder('n')
            ->join('n.etat', 'e')
            ->andWhere('e.libelle = :val')
            ->setParameter('val', $etat)->getQuery()->getResult();
    }
    public function findNbNoteByEtat($etat)
    {
        $req =  $this->createQueryBuilder('n')
            ->select('COUNT(n) as nombre')
            ->join('n.etat', 'e')
            ->andWhere('e.libelle = :val')
            ->setParameter('val', $etat)
            ->getQuery()
            ->getSingleScalarResult();
        return intval($req);
    }

    public function findAllNotesNb($Encours, $valider, $rem, $refuser)
    {
        $req = $this->createQueryBuilder('n')
                ->select('COUNT(n) as nombre')
                ->join('n.etat', 'e')
                ->andWhere('e.libelle = :val1')
                ->setParameter('val1', $Encours)
                ->andWhere('e.libelle = :val2')
                ->setParameter('val2', $valider)
                ->andWhere('e.libelle = :val3')
                ->setParameter('val3', $rem)
                ->andWhere('e.libelle = :val4')
                ->setParameter('val4', $refuser)
                ->getQuery()->getSingleScalarResult();
        return intval($req);
    }

    public function findAllNotesByEtat($ids)
    {
        return $this->createQueryBuilder('n')
            ->join('n.etat', 'e')
            ->orderBy('n.dateCreation', 'DESC')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getResult();
    }
    
    // /**
    //  * @return Note[] Returns an array of Note objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Note
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

