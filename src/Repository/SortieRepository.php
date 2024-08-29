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

    public function findByCriteria(array $criteria, $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.site', 'site')
            ->addSelect('site');

        // Filtrer par site
        if (!empty($criteria['site'])) {
            $qb->andWhere('site.id = :site')
                ->setParameter('site', $criteria['site']->getId());
        }

        // Filtrer par recherche de texte dans le nom de la sortie
        if (!empty($criteria['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        // Filtrer par date de début et de fin
        if (!empty($criteria['dateDebut'])) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }
        if (!empty($criteria['dateFin'])) {
            $qb->andWhere('s.dateHeureFin <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        // Filtrer par état
        if (!empty($criteria['etat'])) {
            if (in_array('organisateur', $criteria['etat'])) {
                $qb->andWhere('s.organisateur = :user')
                    ->setParameter('user', $user);
            }

            if (in_array('inscrit', $criteria['etat'])) {
                $qb->innerJoin('s.inscriptions', 'i')
                    ->andWhere('i.utilisateur = :user')
                    ->setParameter('user', $user);
            }

            if (in_array('non_inscrit', $criteria['etat'])) {
                $qb->leftJoin('s.inscriptions', 'i_not')
                    ->andWhere('i_not.utilisateur IS NULL OR i_not.utilisateur != :user')
                    ->setParameter('user', $user);
            }

            if (in_array('passees', $criteria['etat'])) {
                $qb->andWhere('s.dateHeureFin < :now')
                    ->setParameter('now', new \DateTime());
            }
        }

        return $qb->getQuery()->getResult();
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
