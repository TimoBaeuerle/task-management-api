<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): void {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function remove(Task $task): void {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }

    public function findTasksByParameters(?string $status = null, ?int $page = null, ?int $limit = null): array {
        $queryBuilder = $this->createQueryBuilder('t');

        //Add status to query
        if ($status) {
            $queryBuilder->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        //Add pagination to query
        if ($page && $limit) {
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        $query = $queryBuilder->getQuery();
        $tasks = $query->getResult();

        //Return paginated results
        if ($page && $limit) {
            $total = count($tasks);
            return [
                'data' => $tasks,
                'total' => $total,
                'current_page' => $page,
                'limit' => $limit
            ];
        }

        return $tasks;
    }
}
