<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CsvUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/casrec")
 */
class CasRecController extends RestController
{
    /**
     * @Route("/truncate")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function truncateTable(Request $request)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->query('TRUNCATE TABLE casrec');

        return ['truncated'=>true];
    }

    /**
     * Bulk insert
     * To call multiple times in chunks of maximum 10k records, otherwise failing deu to memory reasons.
     * Currently used from admin area via a ajax uploader and multiple requests (after an initial truncate)
     *
     * @Route("/bulk-add")
     * @Method({"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addBulk(Request $request)
    {
        $casrecService = $this->get('casrec_service');

        ini_set('memory_limit', '1024M');

        $data = CsvUploader::decompressData($request->getContent());

        $ret = $casrecService->addBulk($data);

        // TEST writes casrec data to csv.
        $casrecService->saveCsv(EntityDir\CasRec::STATS_FILE_PATH);

        return $ret;
    }

    /**
     * Verify Deputy & Client last names, Postcode, and Case Number
     *
     * @Route("/verify")
     * @Method({"POST"})
     */
    public function verify(Request $request)
    {
        $clientData = $this->deserializeBodyContent($request);
        $user = $this->getUser();

        $casrecVerified = $this->container->get('opg_digideps.casrec_verification_service')
            ->validate($clientData['case_number'], $clientData['lastname'], $user->getLastname(), $user->getAddressPostcode()
            );

        return ['verified' => $casrecVerified];
    }

    /**
     * @Route("/count")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function userCount()
    {
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb->select('count(c.id)');
        $qb->from('AppBundle\Entity\CasRec', 'c');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Return CSV file created on the fly
     *
     * @Route("/stats.csv")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getStatsCsv(Request $request)
    {
        // create CSV if not added by the cron, or the "regenerated" param is true
        if (!file_exists(EntityDir\CasRec::STATS_FILE_PATH) || $request->get('regenerate')) {
            $this->get('casrec_service')->saveCsv(EntityDir\CasRec::STATS_FILE_PATH);
        }

        $response = new BinaryFileResponse(EntityDir\CasRec::STATS_FILE_PATH);

        return $response;
    }
}
