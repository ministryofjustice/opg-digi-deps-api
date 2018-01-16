<?php

namespace AppBundle\Command;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @deprecated use fixtures command instead. if removed,
 * move login into subclass FixturesCommand
 *
 * @codeCoverageIgnore
 */
class AddSingleUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:add-user')
            ->setDescription('Add single user from ')
            ->addArgument('email', null, InputOption::VALUE_REQUIRED)
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED)
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED)
            ->addOption('role', null, InputOption::VALUE_REQUIRED)
            ->addOption('roleName', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
            ->addOption('enable-odr', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [
            'firstname' => $input->getOption('firstname'),
            'lastname' => $input->getOption('lastname'),
            'roleId' => $input->getOption('role'),
            'password' => $input->getOption('password'),
            'email' => $input->getArgument('email'),
            'odrEnabled' => $input->getOption('enable-odr'),
        ];
        if (count(array_filter($data)) !== count($data)) {
            throw new \RuntimeException('Missing params');
        }

        $this->addSingleUser($output, $data, ['flush' => true]);
    }

    /**
     * @param OutputInterface $output
     * @param string          $email
     * @param array           $data   keys: firstname lastname roleId password odrEnabled
     */
    protected function addSingleUser(OutputInterface $output, array $data, array $options)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $userRepo = $em->getRepository('AppBundle\Entity\User');
        $email = $data['email'];

        $output->write("User $email: ");

        /**
         * create User entity
         */
        if ($userRepo->findBy(['email' => $email])) {
            $output->writeln('skip.');

            return;
        }
        $user = (new User())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($email)
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setOdrEnabled(!empty($data['odrEnabled']))
            ->setCoDeputyClientConfirmed(
                isset($data['codeputyClientConfirmed']) ?
                    (bool) $data['codeputyClientConfirmed'] :
                    false
            )
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressCountry('GB')
        ;

        if (isset($data['deputyPostcode'])) {
            $user->setAddressPostcode($data['deputyPostcode']);
        }

        if (isset($data['roleId']) && !empty($data['roleId'])) { //deprecated
            $user->setRoleName(User::roleIdToName($data['roleId']));
        } elseif (isset($data['roleName']) && !empty($data['roleName'])) {
            $user->setRoleName($data['roleName']);
        } else {
            $output->write('roleId or roleName must be defined');
            return;
        }

        $user->setPassword($this->encodePassword($user, $data['password']));

        $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
        if ($violations->count()) {
            $output->writeln("error: $violations");

            return;
        }
        $em->persist($user);

        /**
         * Deputy:
         * Add CASREC entry + Client
         */
        if ($data['roleName'] != User::ROLE_ADMIN) {
            $casRecEntity = $casRecEntity = new CasRec($this->extractDataToRow($data));
            $em->persist($casRecEntity);

            // add client
            $client = new Client();
            $client
                ->setCaseNumber($data['caseNumber'])
                ->setFirstname('John')
                ->setLastname($data['clientSurname'])
                ->setPhone('022222222222222')
                ->setAddress('Victoria road')
                ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));

            $em->persist($client);
            $user->addClient($client);

            if (!$client->getOdr()) {
                $odr = new Odr($client);
                $em->persist($odr);
            }

        }

        
        if ($options['flush']) {
            $em->flush();
        }

        $output->writeln('created.');
    }

    /**
     * Method to convert user fixture data into Casrec CSV data required by constructor
     *
     * @param $data
     *
     * @return mixed
     */
    private function extractDataToRow($data)
    {
        $row['Case'] = $data['caseNumber'];
        $row['Surname'] = $data['clientSurname'];
        $row['Deputy No'] = $data['deputyNo'];
        $row['Dep Surname'] = $data['lastname'];
        $row['Dep Postcode'] = $data['deputyPostcode'];
        $row['Typeofrep'] = $data['typeOfReport'];
        $row['Corref'] = $data['corref'];

        return $row;
    }

    /**
     * @param User   $user
     * @param string $passwordPlain
     *
     * @return string encoded password
     */
    protected function encodePassword(User $user, $passwordPlain)
    {
        return $this->getContainer()->get('security.encoder_factory')
            ->getEncoder($user)
            ->encodePassword($passwordPlain, $user->getSalt());
    }
}
