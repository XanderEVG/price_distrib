<?php

namespace App\Repository;

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
class ProductRepository extends ServiceEntityRepository
{
    private string $alias = 'p';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function get(?array $filters, ?array $sort, ?int $limit = null, ?int $start = 0, ?int $city_id = null, ?int $shop_id = null)
    {
        $get_query = $this->createQueryBuilder($this->alias);
        $this->filter($get_query, $filters);
        if ($city_id) {
            $get_query->andWhere($this->alias.".city_id = :city_id");
            $get_query->setParameter("city_id", $city_id,ParameterType::INTEGER);
        }

        if ($shop_id) {
            $get_query->andWhere($this->alias.".shop_id = :shop_id");
            $get_query->setParameter("shop_id", $shop_id,ParameterType::INTEGER);
        }

        $this->sort($get_query, $sort);


        $get_query->setMaxResults($limit);
        $get_query->setFirstResult($start);

        return $get_query->getQuery()->getResult();
    }

    public function getTotal(?array $filters, ?int $city_id = null, ?int $shop_id = null)
    {
        $alias = $this->alias;
        $total_query = $this->createQueryBuilder($alias);
        $this->filter($total_query, $filters);
        if ($city_id) {
            $total_query->andWhere($this->alias.".city_id = :city_id");
            $total_query->setParameter("city_id", $city_id,ParameterType::INTEGER);
        }

        if ($shop_id) {
            $total_query->andWhere($this->alias.".shop_id = :shop_id");
            $total_query->setParameter("shop_id", $shop_id,ParameterType::INTEGER);
        }
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

                case 'main_unit':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.main_unit) LIKE :main_unit");
                    $query->setParameter("main_unit", "%$value%");
                    break;

                case 'second_unit':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.second_unit) LIKE :second_unit");
                    $query->setParameter("second_unit", "%$value%");
                    break;

                case 'main_price':
                    $query->andWhere("$alias.main_price) = :main_price");
                    $query->setParameter("main_price", $value);
                    break;

                case 'second_price':
                    $query->andWhere("$alias.second_price) = :second_price");
                    $query->setParameter("second_price", $value);
                    break;

                case 'city':
                    $value = mb_strtolower($value);
                    $query->andWhere("$alias.city in :city_idx");
                    $query->setParameter("city_idx", "$value");
                    break;

                case 'shop':
                    $value = mb_strtolower($value);
                    $query->andWhere("$alias.shop in :shop_idx");
                    $query->setParameter("shop_idx", "$value");
                    break;

                case 'device':
                    //$value = mb_strtolower($value);
                    //$query->andWhere("$alias.device in :device_idx");
                    //$query->setParameter("device_idx", "$value");
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

                case 'main_unit':
                    $query->addOrderBy("$alias.main_unit", $dir);
                    break;

                case 'second_unit':
                    $query->addOrderBy("$alias.second_unit", $dir);
                    break;

                case 'main_price':
                    $query->addOrderBy("$alias.main_price", $dir);
                    break;

                case 'second_price':
                    $query->addOrderBy("$alias.second_price", $dir);
                    break;

                case 'city':
                    $query->addOrderBy("$alias.city", $dir);
                    break;

                case 'shop':
                    $query->addOrderBy("$alias.shop", $dir);
                    break;

                case 'device':
                    //$query->addOrderBy("$alias.device_id", $dir);
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
        $queryBuilder = $this->createQueryBuilder('product');
        $queryBuilder
            ->delete(Shop::class, 'p')
            ->where($queryBuilder->expr()->in('p.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
