<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\AbstractReport;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="report_submission")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ReportSubmissionRepository")
 */
class ReportSubmission
{
    const REMOVE_FILES_WHEN_OLDER_THAN = '-30 days';

    // createdBy is the user who submitted the report
    // createdOn = date where the report (or documents-only) get submitted
    use CreationAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"report-submission", "report-submission-id"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_submission_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @JMS\Type("AppBundle\Entity\Report\Report")
     *
     * @JMS\Groups({"report-submission"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="reportSubmissions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var Odr
     *
     * @JMS\Type("AppBundle\Entity\Odr\Odr")
     *
     * @JMS\Groups({"report-submission"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr")
     * @ORM\JoinColumn(name="ndr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Document>")
     * @JMS\Groups({"report-submission", "report-submission-documents"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Document", mappedBy="reportSubmission")
     * @ORM\JoinColumn(name="report_submission_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ORM\OrderBy({"createdBy"="ASC"})
     */
    private $documents;

    /**
     * @var User
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="archived_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private $archivedBy;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"report-submission"})
     * @ORM\Column(name="downloadable", type="boolean", options={ "default": true}, nullable=false)
     */
    private $downloadable;

    /**
     * ReportSubmission constructor.
     *
     * @param Report\Odr $report
     * @param User   $createdBy
     */
    public function __construct($report, User $createdBy)
    {
        if ($report instanceof Report) {
            $this->report = $report;
            $this->report->addReportSubmission($this);// double-link for UNIT test purposes
        } else if ($report instanceof Odr) {
            $this->ndr = $report;
        } else {
            throw new \InvalidArgumentException(__METHOD__.' first argumnt should be a Report or an Ndr');
        }
        $this->documents = new ArrayCollection();
        $this->createdBy = $createdBy;
        $this->downloadable = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ReportSubmission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return Odr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Report $report
     *
     * @return ReportSubmission
     */
    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return ArrayCollection|Document[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document $document
     *
     * @return $this
     */
    public function addDocument(Document $document)
    {
        if (!$this->documents->contains(($document))) {
            $this->documents->add($document);
        }

        return $this;
    }

    /**
     * @return User
     */
    public function getArchivedBy()
    {
        return $this->archivedBy;
    }

    /**
     * @param User|null $archivedBy
     *
     * @return ReportSubmission
     */
    public function setArchivedBy(User $archivedBy = null)
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @param mixed $downloadable
     *
     * @return ReportSubmission
     */
    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;

        return $this;
    }
}
