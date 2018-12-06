<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_DOCUMENTS];

    /**
     * @Route("/document/{reportType}/{reportId}", requirements={
     *     "reportId":"\d+",
     *     "reportType" = "(report|ndr)"
     * })
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportType, $reportId)
    {
        if ($reportType === 'report') {
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        } else {
            $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $reportId);
        }

        $this->denyAccessIfReportDoesNotBelongToUser($report);

        // hydrate and persist
        $data = $this->deserializeBodyContent($request, [
            'file_name' => 'notEmpty',
            'storage_reference' => 'notEmpty'
        ]);
        $document = new EntityDir\Report\Document($report);
        $document->setCreatedBy($this->getUser());
        $document->setFileName($data['file_name']);
        $document->setStorageReference($data['storage_reference']);
        $document->setIsReportPdf($data['is_report_pdf']);
        if (!$document->isAdminDocument()) {
            // only set flag to yes if document being added is a deputy Document (and not auto-generated)
            $report->setWishToProvideDocumentation('yes');
        }
        $this->persistAndFlush($document);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $document->getId()];
    }

    /**
     * GET document by id
     *
     * @Route("/document/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['documents'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        /* @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);

        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        return $document;
    }

    /**
     * Soft Delete document.
     * Accessible only from deputy area
     *
     * @Method({"DELETE"})
     * @Route("/document/{id}")
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     *
     * @return array
     */
    public function softDelete($id)
    {
        /** @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);
        $report = $document->getReport();

        // enable if the check above is removed and the note is available for editing for the whole team
        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        $this->getEntityManager()->remove($document);
        $this->getEntityManager()->flush();

        // update yesno question to null if its the last document to be removed
        if (count($report->getDeputyDocuments()) == 0) {
            $report->setWishToProvideDocumentation(null);
        }

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * Hard Delete
     * Currently only accessed by admin area cron (no user login needed)
     * Throw exception if a non-soft deleted document is asked for deletiong
     *
     * @Method({"DELETE"})
     * @Route("/document/hard-delete/{id}")
     *
     * @param int $id
     */
    public function hardDelete(Request $request, $id)
    {
        if (!$this->getAuthService()->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException('Endpoint only accessible from ADMIN container.', 403);
        }

        /* @var $repo EntityDir\Repository\DocumentRepository */
        $repo = $this->getRepository(EntityDir\Report\Document::class);
        /* @var $document EntityDir\Report\Document */
        $document = $repo->findUnfilteredOneBy(['id'=>$id]);
        if (!$document->getDeletedAt()) {
            throw new \RuntimeException("Can't hard delete document $id, as it's not soft-deleted");
        }

        $this->getEntityManager()->remove($document);
        $this->getEntityManager()->flush();

        return $id;
    }

    /**
     * GET soft-documents
     * Currently only accessed by admin area cron (no user login needed)
     *
     * @Route("/document/soft-deleted")
     * @Method({"GET"})
     */
    public function getSoftDeletedDocuments(Request $request)
    {
        if (!$this->getAuthService()->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException('Endpoint only accessible from ADMIN container.', 403);
        }

        $this->setJmsSerialiserGroups(['document-id', 'document-storage-reference']);

        /* @var $repo EntityDir\Repository\DocumentRepository */
        $repo = $this->getRepository(EntityDir\Report\Document::class);

        return $repo->retrieveSoftDeleted();
    }
}
