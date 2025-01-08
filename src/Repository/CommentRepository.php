<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpParser\Node\Expr\Array_;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Comment::class);
    }

    public function paginatedCommentFiltered(int $page, bool|null $validated, string|null $name): PaginationInterface
    {
        $qb = $this->createQueryBuilder('c');
        if ($validated !== null) {
            if ($validated !== true) {
                $qb->andWhere('c.validated = false');
            }
            if ($validated === true) {
                $qb->andWhere('c.validated = true');
            }
        }
        $qb->orderBy('c.createdAt', 'DESC');
        $qb->leftJoin('c.user', 'u');
        if ($name) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.username', ':name'),
                $qb->expr()->like('u.email', ':name')
            ))
                ->setParameter('name', '%' . $name . '%');
        }
        $qb->getQuery();

        return $this->paginator->paginate(
            $qb,
            $page,
            5,
            [
                'distinct' => true,
                'sortfieldAllowList' => [
                    'c.createdAt',
                    'c.validated',
                    'u.username',
                    'u.email',
                ]
            ]
        );
    }

    public function commentsValidated(bool|null $validated): array
    {
        $qb = $this->createQueryBuilder('c');
        if ($validated !== null) {
            if ($validated !== true) {
                $qb->andWhere('c.validated = false');
            }
        }
        return $qb->getQuery()->getResult();
    }

}
