<?php

namespace App\Repository;

use App\Entity\City;
use App\Services\QuotesWiper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    private string $alias = 'c';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function get(?array $filters, ?array $sort, ?int $limit = null, ?int $start = 0)
    {
        $get_query = $this->createQueryBuilder($this->alias);
        $this->filter($get_query, $filters);
        $this->sort($get_query, $sort);

        $get_query->setMaxResults($limit);
        $get_query->setFirstResult($start);

        return $get_query->getQuery()->getResult();
    }

    public function getTotal(?array $filters)
    {
        $alias = $this->alias;
        $total_query = $this->createQueryBuilder($alias);
        $this->filter($total_query, $filters);
        $total_query->select("count($alias.id)");
        try {
            return $total_query->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    private function filter(QueryBuilder $query, ?array $filters)
    {
        $alias = $this->alias;
        foreach ($filters as $column => $value) {
            switch ($column) {
                case 'id':
                    $query->andWhere("$alias.id = :id");
                    $query->setParameter("id", $value);
                    break;

                case 'name':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.name) LIKE :name");
                    $query->setParameter("name", "%$value%");
                    break;
            }
        }
    }

    private function sort(QueryBuilder $query, ?array $sort)
    {
        $alias = $this->alias;
        foreach ($sort as $column => $dir) {
            switch ($column) {
                case 'id':
                    $query->addOrderBy("$alias.id", $dir);
                    break;

                case 'name':
                    $query->addOrderBy("$alias.name", $dir);
                    break;
            }
        }
    }

    /**
     * Удаление по  ИД.
     *
     * @param array $ids ИД.
     *
     * @return void
     */
    public function deleteById(array $ids): void
    {
        $queryBuilder = $this->createQueryBuilder('city');
        $queryBuilder
            ->delete(City::class, 'c')
            ->where($queryBuilder->expr()->in('c.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
