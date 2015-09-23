<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Service\ApiClient;
use AppBundle\Entity\User;
use Symfony\Component\Validator\ConstraintViolationList;

class AddUsersFromFixturesCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fixtures')
            ->setDescription('Add data from fixtures')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixtures = (array) $this->getContainer()->getParameter('fixtures');
        foreach ($fixtures as $email => $data) {
            $this->addSingleUser($output, ['email'=>$email] + $data);
        }
    }

}