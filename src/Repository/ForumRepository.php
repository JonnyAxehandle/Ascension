<?php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Forum|null find($id, $lockMode = null, $lockVersion = null)
 * @method Forum|null findOneBy(array $criteria, array $orderBy = null)
 * @method Forum[]    findAll()
 * @method Forum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    /**
     * @return Forum[]
     */
    public function findCategories(): array
    {
        return $this->findBy([
            'Parent' => null
        ]);
    }

    /**
     * @return Forum[]
     */
    public function getIndex(): array
    {
        return $this->getEntityManager()->createQuery(<<<DQL
            SELECT category , forums , channel , lastThread , lastPost
            FROM \App\Entity\Forum category
            JOIN category.Children forums
            JOIN forums.Channel channel
            LEFT JOIN channel.Threads lastThread
            WITH lastThread = FIRST(
                SELECT thread
                FROM \App\Entity\Thread thread
                WHERE thread.Channel = channel
                ORDER BY thread.id DESC
            )
            LEFT JOIN lastThread.Posts lastPost
            WITH lastPost = FIRST(
                SELECT post
                FROM \App\Entity\Post post
                WHERE post.Thread = lastThread
                ORDER BY post.id DESC
            )
            WHERE category.Parent IS NULL
        DQL)->execute();
    }

    // /**
    //  * @return Forum[] Returns an array of Forum objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Forum
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
