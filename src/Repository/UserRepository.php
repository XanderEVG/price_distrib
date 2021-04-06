<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Services\QuotesWiper;

use App\Common\Repository\ExtendedFind\ColumnMapper;
use App\Common\Repository\ExtendedFind\FindWithFilter;
use App\Common\Repository\ExtendedFind\FindWithFilterAndSort;
use App\Common\Repository\ExtendedFind\FindWithSort;
/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, FindWithFilterAndSort
{
    use FindWithFilter;
    use FindWithSort;

    private string $alias = 'u';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
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
            ->delete(User::class, $alias)
            ->where($queryBuilder->expr()->in("$alias.id", ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
