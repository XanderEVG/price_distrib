<?php

namespace App\Repository;

use App\Common\Repository\ExtendedFind\ColumnMapper;
use App\Common\Repository\ExtendedFind\FindWithFilter;
use App\Common\Repository\ExtendedFind\FindWithFilterAndSort;
use App\Common\Repository\ExtendedFind\FindWithSort;
use App\Entity\City;
use App\Entity\Device;
use App\Entity\Product;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository //implements FindWithFilterAndSort
{
    use FindWithFilter;
    use FindWithSort;

    private string $alias = 'p';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    private function columnMaps(): array
    {
        return array(
            'city' => 'p.city_id',
            'shop' => 'p.shop_id',
            'devices' => 'd.mac',
            'mainUnit' => 'p.main_unit',
            'mainPrice' => 'p.main_price',
            'productCode' => 'p.product_code',
        );
    }

    public function findWithSortAndFilters(array $filterBy, array $orderBy, $limit = 10, $offset = 0)
    {
        $alias = $this->alias;

        $queryBuilder = $this->createQueryBuilder($alias);

        // Здесь происходит магия доктрины:
        // из за leftjoin возвращаются дубли Products с приджойнеными Devices,
        // но далее где то эти дубли склеиваются.
        // Меньше знаний - больше магии)))
        $queryBuilder->select()
               ->leftjoin(Device::class, 'd', 'WITH', "d.product = $alias.id");
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
        $queryBuilder->select("count($alias.id)")
            ->leftjoin(Device::class, 'd', 'WITH', "d.product = $alias.id");
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
            ->delete(Product::class, $alias)
            ->where($queryBuilder->expr()->in("$alias.id", ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
