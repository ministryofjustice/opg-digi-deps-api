<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;

/**
 * ClientRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClientRepository extends EntityRepository
{

    /**
     * Search Clients
     *
     * @param string $query Search query
     * @param string $orderBy field to order by
     * @param string $sortOrder order of field order ASC|DESC
     * @param int $limit number of results to return
     * @param string $offset
     *
     * @return Client[]|array
     */
    public function searchClients($query = '', $orderBy = 'lastname', $sortOrder = 'ASC', $limit = 100, $offset = 'id')
    {

        $qb = $this->createQueryBuilder('c');
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->leftJoin('c.users', 'u')
            ->where('u.roleName = \'' . User::ROLE_LAY_DEPUTY . '\'');
        $qb->orderBy('c.' . $orderBy, $sortOrder);

        // to do filter by LAY_DEPUTY

        if ($query) {
            if (preg_match('/^[0-9t]{8}$/i', $query)) { // case number
                $qb->andWhere('lower(c.caseNumber) = :cn');
                $qb->setParameter('cn', strtolower($query));
            } else { // client.lastname
                $qb->andWhere('lower(c.lastname) LIKE :qLike ');
                $qb->setParameter('qLike', '%' . strtolower($query) . '%');
            }
        }

        $clients = $qb->getQuery()->getResult(); /* @var $clients Client[] */

        return $clients;
    }
}
