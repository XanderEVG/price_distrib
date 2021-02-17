<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends ServiceEntityRepository
{
    private string $alias = 'd';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function get(?array $filters, ?array $sort, ?int $limit = null, ?int $start = 0, ?int $shop_id = null)
    {
        $get_query = $this->createQueryBuilder($this->alias);
        $this->filter($get_query, $filters);
        if ($shop_id) {
            $alias = $this->alias;
            $get_query->andWhere("($alias.shop_id = :shop_id or $alias.shop_id is null)");
            $get_query->setParameter("shop_id", $shop_id,ParameterType::INTEGER);
        }

        $this->sort($get_query, $sort);


        $get_query->setMaxResults($limit);
        $get_query->setFirstResult($start);

        return $get_query->getQuery()->getResult();
    }

    public function getTotal(?array $filters, ?int $shop_id = null)
    {
        $alias = $this->alias;
        $total_query = $this->createQueryBuilder($alias);
        $this->filter($total_query, $filters);
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

                case 'mac':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.mac) LIKE :mac");
                    $query->setParameter("mac", "%$value%");
                    break;

                case 'shop':
                    $value = mb_strtolower($value);
                    $query->andWhere("$alias.shop_id in :shop_idx");
                    $query->setParameter("shop_idx", "$value");
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

                case 'mac':
                    $query->addOrderBy("$alias.mac", $dir);
                    break;

                case 'shop':
                    $query->addOrderBy("$alias.shop_id", $dir);
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
        $queryBuilder = $this->createQueryBuilder('device');
        $queryBuilder
            ->delete(Shop::class, 'd')
            ->where($queryBuilder->expr()->in('d.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
