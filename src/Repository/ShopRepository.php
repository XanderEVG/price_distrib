<?php

namespace App\Repository;

use App\Common\Repository\ExtendedFind\ColumnMapper;
use App\Common\Repository\ExtendedFind\FindWithFilter;
use App\Common\Repository\ExtendedFind\FindWithFilterAndSort;
use App\Common\Repository\ExtendedFind\FindWithSort;
use App\Entity\City;
use App\Entity\Shop;
use App\Services\QuotesWiper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Shop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shop[]    findAll()
 * @method Shop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopRepository extends ServiceEntityRepository implements FindWithFilterAndSort
{
    use FindWithFilter;
    use FindWithSort;

    private string $alias = 's';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    private function columnMaps(): array
    {
        return array();
    }

    public function findWithSortAndFilters(array $filterBy, array $orderBy, $limit = 10, $offset = 0)
    {
        $alias = $this->alias;

        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->select();
        $filterBy = ColumnMapper::mapColumns($filterBy, $this->columnMaps(), $alias);
        $orderBy = ColumnMapper::mapColumns($orderBy, $this->columnMaps(), $alias);
        $queryBuilder = $this->addFiltersToQuery($queryBuilder, $filterBy, []);
        $queryBuilder = $this->addSortToQuery($queryBuilder, $orderBy);
        $queryBuilder->setMaxResults($limit)->setFirstResult($offset);

        return $queryBuilder->getQuery()->getResult();
    }

    public function countWithFilters(array $filterBy)
    {
        $alias = $this->alias;

        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->select("count($alias.id)");
        $filterBy = ColumnMapper::mapColumns($filterBy, $this->columnMaps(), $alias);
        $queryBuilder = $this->addFiltersToQuery($queryBuilder, $filterBy, []);
        return $queryBuilder->getQuery()->getSingleScalarResult();
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
        $alias = $this->alias;

        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder
            ->delete(Shop::class, $alias)
            ->where($queryBuilder->expr()->in("$alias.id", ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
