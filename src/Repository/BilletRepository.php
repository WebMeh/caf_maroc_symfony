<?php

namespace App\Repository;

use App\Entity\Billet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Billet>
 */
class BilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billet::class);
    }

    // Méthode pour trouver les billets en attente
    public function findBilletsEnAttente(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->getQuery()
            ->getResult();
    }

    // Méthode pour trouver les billets vendus
    public function findBilletsVendus(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.statut = :statut')
            ->setParameter('statut', 'approuvé')
            ->getQuery()
            ->getResult();
    }

    // Caculer le nombre total des billets vendus
    public function countBilletsVendus(): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.statut = :statut')
            ->setParameter('statut', 'approuvé')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Méthode pour calculer nombre de billet vendus pour un matche
    public function countBilletsVendusParMatch(): array
    {
        return $this->createQueryBuilder('b')
            ->select('m.id AS match_id, team1.name AS team1_name, team2.name AS team2_name, m.date AS date, m.stadium as stad, COUNT(b.id) AS billets_vendus')
            ->join('b.matche', 'm')
            ->join('m.team1', 'team1')
            ->join('m.team2', 'team2')
            ->where('b.statut = :statut')  // Seuls les billets approuvés
            ->setParameter('statut', 'approuvé')
            ->groupBy('m.id, team1.name, team2.name, m.date')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Billet[] Returns an array of Billet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Billet
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
