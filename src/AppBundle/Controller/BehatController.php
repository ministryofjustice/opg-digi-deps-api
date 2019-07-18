<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller only used from BEHAT
 *
 * @codeCoverageIgnore
 * @Route("/behat")
 */
class BehatController extends RestController
{
    private function securityChecks()
    {
        if (!$this->container->getParameter('behat_controller_enabled')) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/client/{caseNumber}")
     * @Method({"PUT"})
     */
    public function clientEditAction(Request $request, $caseNumber)
    {
        $this->securityChecks();

        /* @var $client Client */
        $client = $this->findEntityBy(Client::class, ['caseNumber' => $caseNumber]);

        $data = $this->deserializeBodyContent($request);
        if (array_key_exists('current_report_type', $data)) {
            $report = $client->getCurrentReport();
            $report->setType($data['current_report_type']);
            $this->get('em')->flush($report);
        }

        if (array_key_exists('new_deputy_email', $data)) {
            $newDeputy = $this->findEntityBy(User::class, ['email' => $data['new_deputy_email']]);
            if (!$newDeputy instanceof User) {
                throw new \RuntimeException('Cannot re-assign client to new deputy: ' . $data['new_deputy_email'] .
                    ' User not found');
            }
            $existingClient = $newDeputy->getFirstClient();
            $newDeputy->removeClient($existingClient);
            $existingDeputies = $client->getUsers();
            foreach ($existingDeputies as $existingDeputy)
            {
                $client->removeUser($existingDeputy);
            }

            $client->addUser($newDeputy);
            $this->get('em')->flush($client);
        }
    }

    /**
     * @Route("/report/{reportId}")
     * @Method({"PUT"})
     */
    public function reportEditAction(Request $request, $reportId)
    {
        $this->securityChecks();

        $report = $this->findEntityBy(Report::class, $reportId);

        $data = $this->deserializeBodyContent($request);

        if (!empty($data['type'])) {
            $report->setType($data['type']);
        }

        if (array_key_exists('submitted', $data)) {
            $report->setSubmitted($data['submitted']);
            $report->setSubmitDate($data['submitted'] ? new \DateTime() : null);
        }

        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
        }

        $this->get('em')->flush($report);

        return true;
    }

    /**
     * @Route("/user/{email}")
     * @Method({"PUT"})
     */
    public function editUser(Request $request, $email)
    {
        $this->securityChecks();

        $data = $this->deserializeBodyContent($request);
        $user = $this->findEntityBy(User::class, ['email' => $email]);

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }

        $this->get('em')->flush($user);

        return 'done';
    }
}
