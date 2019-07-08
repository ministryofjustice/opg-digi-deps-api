<?php

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClientRepositoryTest extends KernelTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var ClientRepository */
    private $repository;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(Client::class);
    }

    /**
     * @test
     */
    public function clientIsAttachedButNotToThisDeputyReturnsFalseIfClientNotExist()
    {
        $result = $this->repository->clientIsAttachedButNotToThisDeputy('case-num', 'dep-num');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function clientIsAttachedButNotToThisDeputyReturnsFalseIfClientExistButHasNoDeputy()
    {
        $this->persistClientWithCaseNumber('case-num-alpha');
        $this->entityManager->flush();

        $result = $this->entityManager
            ->getRepository(Client::class)
            ->clientIsAttachedButNotToThisDeputy('case-num-alpha', 'dep-num');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function clientIsAttachedButNotToThisDeputyReturnsFalseIfClientExistAndAttachedToGivenDeputy()
    {
        $client = $this->persistClientWithCaseNumber('case-num-beta');
        $deputy = $this->persistDeputyWithDeputyNumber('dep-num-alpha');

        $client->addUser($deputy);
        $this->entityManager->flush();

        $result = $this->entityManager
            ->getRepository(Client::class)
            ->clientIsAttachedButNotToThisDeputy('case-num-beta', 'dep-num-alpha');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function clientIsAttachedButNotToThisDeputyReturnsRowIfClientExistAndAttachedButNotToGivenDeputy()
    {
        $this->persistDeputyWithDeputyNumber('dep-num-beta');

        $client = $this->persistClientWithCaseNumber('case-num-charlie');
        $deputyAttachedTo = $this->persistDeputyWithDeputyNumber('dep-num-charlie');

        $client->addUser($deputyAttachedTo);
        $this->entityManager->flush();

        $result = $this->entityManager
            ->getRepository(Client::class)
            ->clientIsAttachedButNotToThisDeputy('case-num-charlie', 'dep-num-beta');

        $this->assertInternalType('array', $result);
        $this->assertEquals('dep-num-charlie', $result['deputy_no']);
    }

    /**
     * @param $caseNumber
     * @return Client
     * @throws \Doctrine\ORM\ORMException
     */
    private function persistClientWithCaseNumber($caseNumber)
    {
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);

        return $client;
    }

    /**
     * @param $deputyNumber
     * @return User
     * @throws \Doctrine\ORM\ORMException
     */
    private function persistDeputyWithDeputyNumber($deputyNumber)
    {
        $user = new User();
        $user->setFirstname('firstname');
        $user->setPassword('password');
        $user->setEmail(sprintf('email%s', $deputyNumber));
        $user->setCoDeputyClientConfirmed(false);
        $user->setDeputyNo($deputyNumber);

        $this->entityManager->persist($user);

        return $user;
    }
}
