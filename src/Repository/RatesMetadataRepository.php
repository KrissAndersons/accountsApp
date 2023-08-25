<?php

namespace App\Repository;

use App\Entity\RatesMetadata;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RatesMetadata>
 *
 * @method RatesMetadata|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatesMetadata|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatesMetadata[]    findAll()
 * @method RatesMetadata[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatesMetadataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatesMetadata::class);
    }

//    /**
//     * @return RatesMetadata[] Returns an array of RatesMetadata objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RatesMetadata
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
