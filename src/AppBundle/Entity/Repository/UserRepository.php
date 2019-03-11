<?php

namespace AppBundle\Entity\Repository;

class UserRepository extends AbstractEntityRepository
{
    public function findUserArrayById($id)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT u, c, r FROM AppBundle\Entity\User u LEFT JOIN u.clients c LEFT JOIN c.reports r WHERE u.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return count($result) === 0 ? null : $result[0];
    }
}
