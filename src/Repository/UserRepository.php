<?php

namespace App\Repository;

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

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
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

    public function get(?array $filters, ?array $sort, ?int $limit = null, ?int $start = 0)
    {
        $get_query = $this->createQueryBuilder('u');
        $this->filter($get_query, $filters);
        $this->sort($get_query, $sort);

        $get_query->setMaxResults(500);
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

                case 'username':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.username) LIKE :username");
                    $query->setParameter("username", "%$value%");
                    break;

                case 'email':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.email) LIKE :email");
                    $query->setParameter("email", "%$value%");
                    break;

                case 'roles':
                    $value = mb_strtolower($value);
                    $query->andWhere("lower($alias.roles) LIKE :roles");
                    $query->setParameter("roles", "%$value%");
                    break;

                /*case 'cities':
                    $query->andWhere("$alias.fio = :fio");
                    $query->setParameter("id", $value);
                    break;

                case 'shops':
                    $query->andWhere("$alias.fio = :fio");
                    $query->setParameter("id", $value);
                    break;*/
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

                case 'username':
                    $query->addOrderBy("$alias.username", $dir);
                    break;

                case 'email':
                    $query->addOrderBy("$alias.email", $dir);
                    break;

                case 'roles':
                    $query->addOrderBy("$alias.roles", $dir);
                    break;

                /*case 'cities':
                    $query->addOrderBy("$alias.id", $dir);
                    break;

                case 'shops':
                    $query->addOrderBy("$alias.id", $dir);
                    break;*/
            }
        }
    }
}
