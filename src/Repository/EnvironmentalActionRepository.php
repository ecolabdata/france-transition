<?php

namespace App\Repository;

use App\Entity\EnvironmentalAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EnvironmentalAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnvironmentalAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnvironmentalAction[]    findAll()
 * @method EnvironmentalAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnvironmentalActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnvironmentalAction::class);
    }

    public function findAllWithCategory()
    {
        return $this->createQueryBuilder('environmentalAction')
            ->select('environmentalAction', 'category')
            ->join('environmentalAction.category', 'category')
            ->getQuery()->getResult()
        ;
    }
}
