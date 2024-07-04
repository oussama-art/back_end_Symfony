<?php

namespace App\Repository;

use App\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * @return Token[] Returns an array of Token objects
     */
    public function findActiveTokensByUser($userId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :userId')
            ->andWhere('t.expired = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    // Add more custom methods as needed for your application
}
