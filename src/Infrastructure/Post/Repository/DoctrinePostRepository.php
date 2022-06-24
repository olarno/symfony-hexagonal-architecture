<?php

namespace App\Infrastructure\Post\Repository;

use App\Domain\Post\Post as PostDomain;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Infrastructure\Post\Doctrine\Post;
use App\Infrastructure\Post\Doctrine\PostDoctrineParser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\UuidV6;

/**
 * @extends ServiceEntityRepository<\App\Infrastructure\Post\Doctrine\Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoctrinePostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    private PostDoctrineParser $doctrineParser;

    /**
     * @param ManagerRegistry $registry
     * @param \App\Infrastructure\Post\Doctrine\PostDoctrineParser $doctrineParser
     */
    public function __construct(ManagerRegistry $registry, PostDoctrineParser $doctrineParser)
    {
        parent::__construct($registry, Post::class);
        $this->doctrineParser = $doctrineParser;
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(PostDomain $post): void
    {
        $postDoctrine = $this->doctrineParser->toDoctrine($post);
        $this->add($postDoctrine, true);
    }

    public function add(Post $entity, bool $flush = false): Post
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $entity;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneById(string $id): ?PostDomain
    {
        /** @var ?\App\Infrastructure\Post\Doctrine\Post $postDoctrine */
        $postDoctrine = $this
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', UuidV6::fromString($id)->toBinary())
            ->getQuery()
            ->getOneOrNullResult();

        return is_null($postDoctrine) ? null : $this->doctrineParser->toDomain($postDoctrine);
    }
}