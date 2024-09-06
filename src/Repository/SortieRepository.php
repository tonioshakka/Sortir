<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findNonArchived():array
    {
        $dateLimit = new \DateTime();
        $dateLimit->modify('-1 month');



        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.dateHeureDebut > :dateLimit')->setParameter('dateLimit', $dateLimit);


        return $qb->getQuery()->getResult();



    }

    public function findByCriteria(array $criteria, $user): array
{
    $dateLimit = new \DateTime();
    $dateLimit->modify('-1 month');

    $qb = $this->createQueryBuilder('s')
        ->leftJoin('s.organisateur', 'p')// Utilisez 'lieu' au lieu de 'site'
        ->leftJoin('s.participant', 'i');

//    $qb->andWhere('s.dateHeureDebut >= :dateLimit')
//        ->setParameter('dateLimit', $dateLimit);
    // Filtrer par lieu
    if (!empty($criteria['site'])) {
        $qb->andWhere('p.site = :site')
            ->setParameter('site', $criteria['site']->getId());
    }

    // Filtrer par recherche de texte dans le nom de la sortie
    if (!empty($criteria['search'])) {
        $qb->andWhere('s.nom LIKE :nom')
            ->setParameter('nom', '%' . $criteria['search'] . '%');
    }

    if (!empty($criteria['dateDebut']) && !empty($criteria['dateFin'])) {
        $qb->andWhere('s.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $criteria['dateDebut'])
            ->setParameter('dateFin', $criteria['dateFin']);
    } elseif (!empty($criteria['dateDebut'])) {
        // Si seule la date de début est fournie, vous pouvez filtrer sur la date de début uniquement
        $qb->andWhere('s.dateHeureDebut >= :dateDebut')
            ->setParameter('dateDebut', $criteria['dateDebut']);
    } elseif (!empty($criteria['dateFin'])) {
        // Si seule la date de fin est fournie, vous pouvez filtrer sur la date de fin uniquement
        $qb->andWhere('s.dateHeureDebut <= :dateFin')
            ->setParameter('dateFin', $criteria['dateFin']);
    }

    // Filtrer par état
    if (!empty($criteria['etat'])) {
        if (in_array('organisateur', $criteria['etat'])) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        if (in_array('inscrit', $criteria['etat'])) {


            $qb->andWhere('i = :user')
                ->setParameter('user', $user);
        }

        if (in_array('non_inscrit', $criteria['etat'])) {
            $qb->andWhere('p IS NULL OR p != :user')
                ->setParameter('user', $user);
        }

        if (in_array('passees', $criteria['etat'])) {
            $qb->andWhere('s.dateLimiteInscription < :now')
                ->setParameter('now', new \DateTime());
        }
    }
    $qb->andWhere('s.dateHeureDebut > :dateLimit')->setParameter('dateLimit', $dateLimit);
    return $qb->getQuery()->getResult();
}

    public function removeParticipantFromSorties($participant): void
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE FROM App\Entity\Sortie s
        WHERE :participant MEMBER OF s.participant'
        )->setParameter('participant', $participant);

        $query->execute();
    }





    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
