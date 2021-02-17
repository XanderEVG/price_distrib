<?php

namespace App\Repository;

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
class ShopRepository extends ServiceEntityRepository
{
    private string $alias = 's';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function get(?array $filters, ?array $sort, ?int $limit = null, ?int $start = 0, ?int $city_id = null)
    {
        $get_query = $this->createQueryBuilder($this->alias);
        $this->filter($get_query, $filters);
        if ($city_id) {
            $get_query->andWhere($this->alias.".city_id = :city_id");
            $get_query->setParameter("city_id", $city_id,ParameterType::INTEGER);
        }

        $this->sort($get_query, $sort);


        $get_query->setMaxResults($limit);
        $get_query->setFirstResult($start);

        return $get_query->getQuery()->getResult();
    }

    public function getTotal(?array $filters, ?int $city_id = null)
    {
        $alias = $this->alias;
        $total_query = $this->createQueryBuilder($alias);
        $this->filter($total_query, $filters);
        if ($city_id) {
            $total_query->andWhere($this->alias.".city_id = :city_id");
            $total_query->setParameter("city_id", $city_id,ParameterType::INTEGER);
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

                case 'address':
                    $query->addOrderBy("lower($alias.address) LIKE :address");
                    $query->setParameter("address", "%$value%");
                    break;

                case 'city':
                    $value = mb_strtolower($value);
                    $query->andWhere("$alias.city_id in :city_idx");
                    $query->setParameter("city_idx", "$value");
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

                case 'address':
                    $query->addOrderBy("$alias.address", $dir);
                    break;

                case 'city':
                    $query->addOrderBy("$alias.city_id", $dir);
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
        $queryBuilder = $this->createQueryBuilder('shop');
        $queryBuilder
            ->delete(Shop::class, 's')
            ->where($queryBuilder->expr()->in('s.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
