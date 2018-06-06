<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Users.
 *
 * @ORM\Table(name="dd_user", indexes={@ORM\Index(name="deputy_no_idx", columns={"deputy_no"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserRepository")
 */
class User implements UserInterface
{
    use AddressTrait;

    const TOKEN_EXPIRE_HOURS = 48;

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_DOCUMENT_MANAGE = 'ROLE_DOCUMENT_MANAGE';
    const ROLE_DEPUTY = 'ROLE_DEPUTY';
    const ROLE_LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    const ROLE_AD = 'ROLE_AD';
    const ROLE_CASE_MANAGER = 'ROLE_CASE_MANAGER';

    const ROLE_ORG_NAMED = 'ROLE_ORG_NAMED';
    const ROLE_ORG_ADMIN = 'ROLE_ORG_ADMIN';
    const ROLE_ORG_TEAM_MEMBER = 'ROLE_ORG_TEAM_MEMBER';

    const ROLE_PA = 'ROLE_PA';
    const ROLE_PA_NAMED = 'ROLE_PA_NAMED';
    const ROLE_PA_ADMIN = 'ROLE_PA_ADMIN';
    const ROLE_PA_TEAM_MEMBER = 'ROLE_PA_TEAM_MEMBER';

    const ROLE_PROF = 'ROLE_PROF';
    const ROLE_PROF_NAMED = 'ROLE_PROF_NAMED';
    const ROLE_PROF_ADMIN = 'ROLE_PROF_ADMIN';
    const ROLE_PROF_TEAM_MEMBER = 'ROLE_PROF_TEAM_MEMBER';

    public static $depTypeIdToUserRole = [
        //PA
        23 => self::ROLE_PA_NAMED,
        //PROFESSIONAL
        21 => self::ROLE_PROF_NAMED,
        26 => self::ROLE_PROF_NAMED,
        63 => self::ROLE_PROF_NAMED,
    ];

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"user", "report-submitted-by", "user-id"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"user-clients"})
     * @JMS\Type("array")
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Client", mappedBy="users", cascade={"persist"})
     */
    private $clients;

    /**
     * @JMS\Type("ArrayCollection<AppBundle\Entity\Team>")
     * @JMS\Groups({"user-teams"})
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Team", inversedBy="members", cascade={"persist"}, fetch="EAGER")
     *
     * @var ArrayCollection
     */
    private $teams;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({ "user", "report-submitted-by", "user-name"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @JMS\Type("string")
     * @JMS\Groups({ "user", "report-submitted-by", "user-name"})
     */
    private $lastname;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     * @JMS\Exclude
     */
    private $password;

    /**
     * @var string
     * @JMS\Groups({"user", "report-submitted-by"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false, unique=true)
     */
    private $email;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="active", type="boolean", nullable=true, options = { "default": false })
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=100, nullable=true)
     */
    private $salt;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    private $registrationDate;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="registration_token", type="string", length=100, nullable=true)
     */
    private $registrationToken;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="token_date", type="datetime", nullable=true)
     */
    private $tokenDate;

    /**
     * @var string ROLE_
     *             see roles in Role class
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "report-submitted-by", "user-rolename"})
     *
     * @ORM\Column(name="role_name", type="string", length=50, nullable=true)
     */
    private $roleName;

    /**
     * This id is supplied to GA for UserID tracking. It is an md5 of the user id,
     * does not get stored in the database.
     *
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     */
    private $gaTrackingId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "report-submitted-by"})
     * @ORM\Column(name="phone_main", type="string", length=20, nullable=true)
     */
    private $phoneMain;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "report-submitted-by"})
     * @ORM\Column(name="phone_alternative", type="string", length=20, nullable=true)
     */
    private $phoneAlternative;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="last_logged_in", type="datetime", nullable=true)
     */
    private $lastLoggedIn;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=true)
     */
    private $deputyNo;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user", "user-login"})
     *
     * @ORM\Column(name="odr_enabled", type="boolean", nullable=true, options = { "default": false })
     */
    private $ndrEnabled;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="ad_managed", type="boolean", nullable=true, options = { "default": false })
     */
    private $adManaged;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user"})
     * @ORM\Column(name="job_title", type="string", length=150, nullable=true)
     *
     * @var string
     */
    private $jobTitle;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="agree_terms_use", type="boolean", nullable=true, options = { "default": false })
     */
    private $agreeTermsUse;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="agree_terms_use_date", type="datetime", nullable=true)
     */
    private $agreeTermsUseDate;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"user"})
     *
     * @ORM\Column(name="codeputy_client_confirmed", type="boolean", nullable=false, options = { "default": false })
     */
    private $coDeputyClientConfirmed;

    /**
     * Constructor.
     */
    public function __construct($coDeputyClientConfirmed = false)
    {
        $this->clients = new ArrayCollection();
        $this->password = '';
        $this->teams = new ArrayCollection();
        $this->setCoDeputyClientConfirmed($coDeputyClientConfirmed);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->setRegistrationToken('');

        return $this;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set salt.
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set registrationDate.
     *
     * @param \DateTime $registrationDate
     *
     * @return User
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate.
     *
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Set registrationToken.
     *
     * @return User
     */
    public function recreateRegistrationToken()
    {
        $this->setRegistrationToken('digideps' . date('dmY') . time(true) . rand(17, 999917));
        $this->setTokenDate(new \DateTime());

        return $this;
    }

    /**
     * Set registrationToken.
     *
     * @param string $registrationToken
     *
     * @return User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * Get registrationToken.
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set tokenDate.
     *
     * @param \DateTime $tokenDate
     *
     * @return User
     */
    public function setTokenDate($tokenDate)
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    /**
     * Get tokenDate.
     *
     * @return \DateTime
     */
    public function getTokenDate()
    {
        return $this->tokenDate;
    }

    /**
     * Add clients.
     *
     * @param Client $client
     *
     * @return User
     */
    public function addClient(Client $client)
    {
        $client->addUser($this);
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    /**
     * Remove clients.
     *
     * @param Client $clients
     */
    public function removeClient(Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    /**
     * Get clients.
     *
     * @return Client[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Get client by case number, case insensitive.
     *
     * @return Client
     */
    public function getClientByCaseNumber($caseNumber)
    {
        return $this->getClients()->filter(function ($client) use ($caseNumber) {
            return $client->getCaseNumber() == strtolower($caseNumber);
        })->first();
    }

    /**
     * @return Team[]
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("team_names")
     * @JMS\Groups({"team-names"})
     *
     * @return mixed
     */
    public function getTeamNames()
    {
        $ret = [];
        foreach ($this->getTeams() as $team) {
            $ret[$team->getId()] = $team->getTeamName();
        }

        asort($ret);

        return $ret;
    }

    /**
     * Get users in all the teams the user belongs to
     *
     * @return User[] array indexed by user id
     */
    public function getMembersInAllTeams()
    {
        $ret = [];
        foreach ($this->getTeams() as $team) { /* @var $team Team */
            foreach ($team->getMembers() as $member) {
                $ret[$member->getId()] = $member;
            }
        }

        return $ret;
    }

    /**
     * @param $teams
     *
     * @return $this
     */
    public function setTeams($teams)
    {
        $this->teams = $teams;

        return $this;
    }

    /**
     * Add a team
     *
     * @param Team $team
     *
     * @return $this
     */
    public function addTeam(Team $team)
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    /**
     * Add a team
     *
     * @param ArrayCollection|Team $teams Collection being added
     *
     * @return $this
     */
    public function addTeams(ArrayCollection $teams)
    {
        $this->teams = new ArrayCollection(
            array_merge(
                $this->teams->toArray(),
                $teams->toArray()
            )
        );

        return $this;
    }

    /**
     * Remove a team from the collection
     *
     * @param mixed $team collection being removed
     *
     * @return $this
     */
    public function removeTeam($team)
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param string $roleName ROLE_.*
     *
     * @return User
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        //return $this->salt;
        return;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return [$this->roleName];
    }

    public function eraseCredentials()
    {
    }

    /**
     * Get gaTrackingId.
     *
     * @return string $gaTrackingId
     */
    public function getGaTrackingId()
    {
        if (!empty($this->gaTrackingId)) {
            return $this->gaTrackingId;
        }
        $this->gaTrackingId = md5($this->id);

        return $this->gaTrackingId;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @return string
     */
    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    /**
     * @return string
     */
    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
    }

    /**
     * @param $address1
     *
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @param $address2
     *
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @param $address3
     *
     * @return $this
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * @param $addressPostcode
     *
     * @return $this
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    /**
     * @param $addressCountry
     *
     * @return $this
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    /**
     * @param $phoneMain
     *
     * @return $this
     */
    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    /**
     * @param $phoneAlternative
     *
     * @return $this
     */
    public function setPhoneAlternative($phoneAlternative)
    {
        $this->phoneAlternative = $phoneAlternative;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoggedIn()
    {
        return $this->lastLoggedIn;
    }

    /**
     * @param \DateTime $lastLoggedIn
     */
    public function setLastLoggedIn(\DateTime $lastLoggedIn = null)
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    /**
     * convert 7 into 00000007
     *
     * @param $deputyNo
     *
     * @return string
     */
    public static function padDeputyNumber($deputyNo)
    {
        return str_pad($deputyNo, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $deputyNo
     */
    public function setDeputyNo($deputyNo)
    {
        $this->deputyNo = $deputyNo;

        return $this;
    }

    /**
     * Return Id of the client (if it has details)
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("id_of_client_with_details")
     * @JMS\Groups({"user"})
     * @JMS\Type("integer")
     */
    public function getIdOfClientWithDetails()
    {
        return $this->getFirstClient() && $this->getFirstClient()->hasDetails()
            ? $this->getFirstClient()->getId()
            : null;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user-login"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("active_report_id")
     */
    public function getActiveReportId()
    {
        $client = $this->getFirstClient() ? $this->getFirstClient() : null;
        if (!$client) {
            return;
        }

        if ($client->getUnsubmittedReports()->count() === 1) {
            return $client->getUnsubmittedReports()->first()->getId();
        }

        return;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user-login"})
     * @JMS\Type("integer")
     * @JMS\SerializedName("number_of_reports")
     */
    public function getNumberOfReports()
    {
        return $this->getFirstClient() ? count($this->getFirstClient()->getReports()) : 0;
    }

    /**
     * @return null|Client
     */
    public function getFirstClient()
    {
        $clients = $this->getClients();
        if (count($clients) === 0) {
            return;
        }

        return $clients->first();
    }

    /**
     * @return bool
     */
    public function getNdrEnabled()
    {
        return $this->ndrEnabled;
    }

    /**
     * @param bool $ndrEnabled
     */
    public function setNdrEnabled($ndrEnabled)
    {
        $this->ndrEnabled = $ndrEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdManaged()
    {
        return $this->adManaged;
    }

    /**
     * @param bool $adManaged
     */
    public function setAdManaged($adManaged)
    {
        $this->adManaged = $adManaged;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return User
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"user", "user-teamname"})
     * @JMS\Type("string")
     * @JMS\SerializedName("pa_team_name")
     */
    public function getTeamName()
    {
        if ($this->getTeams()->isEmpty()) {
            return null;
        }

        return $this->getTeams()->first()->getTeamName();
    }

    /**
     * @return bool
     */
    public function getAgreeTermsUse()
    {
        return $this->agreeTermsUse;
    }

    /**
     * @param bool $agreeTermsUse
     */
    public function setAgreeTermsUse($agreeTermsUse)
    {
        $this->agreeTermsUse = $agreeTermsUse;
        $this->agreeTermsUseDate = new \DateTime('now');
    }

    /**
     * @return \DateTime
     */
    public function getAgreeTermsUseDate()
    {
        return $this->agreeTermsUseDate;
    }

    /**
     * @return bool
     */
    public function getCoDeputyClientConfirmed()
    {
        return $this->coDeputyClientConfirmed;
    }

    /**
     * @param bool $coDeputyClientConfirmed
     */
    public function setCoDeputyClientConfirmed($coDeputyClientConfirmed = false)
    {
        $this->coDeputyClientConfirmed = $coDeputyClientConfirmed;
        return $this;
    }

    /**
     * Return true if the client has other users
     *
     * @JMS\VirtualProperty
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_co_deputy")
     * @JMS\Groups({"user"})
     *
     * @return bool
     */
    public function isCoDeputy()
    {
        $isCoDeputy = false;
        if ($this->isLayDeputy()) {
            $client = $this->getFirstClient();
            if (!empty($client)) {
                $isCoDeputy = count($client->getUsers()) > 1;
            }
        }
        return $isCoDeputy;
    }

    /**
     * Is a PA (any role)?
     *
     * @return bool
     */
    public function isPaDeputy()
    {
        return $this->isPaNamedDeputy() ||$this->isPaAdministrator() || $this->isPaTeamMember();
    }

    /**
     * Is a Professional Deputy (any role)?
     *
     * @return bool
     */
    public function isProfDeputy()
    {
        return $this->isProfNamedDeputy() || $this->isProfAdministrator() || $this->isProfTeamMember();
    }

    /**
     * Is Organisation Named deputy?
     *
     * @return bool
     */
    public function isOrgNamedDeputy()
    {
        return $this->isPaNamedDeputy() || $this->isProfNamedDeputy();
    }

    /**
     * Is PA Named deputy?
     *
     * @return bool
     */
    public function isPaNamedDeputy()
    {
        return $this->getRoleName() === self::ROLE_PA_NAMED;
    }

    /**
     * Is PA Named deputy?
     *
     * @return bool
     */
    public function isProfNamedDeputy()
    {
        return $this->getRoleName() === self::ROLE_PROF_NAMED;
    }

    /**
     * @return bool
     */
    public function isLayDeputy()
    {
        return $this->getRoleName() === self::ROLE_LAY_DEPUTY;
    }

    /**
     * Is PA Administrator?
     *
     * @return bool
     */
    public function isPaAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PA_ADMIN]);
    }

    /**
     * Is user a Professional Administrator?
     *
     * @return bool
     */
    public function isProfAdministrator()
    {
        return in_array($this->roleName, [self::ROLE_PROF_ADMIN]);
    }

    /**
     * Is Organisation Administrator?
     *
     * @return bool
     */
    public function isOrgAdministrator()
    {
        return $this->isPaAdministrator() || $this->isProfAdministrator();
    }

    /**
     * Is PA Team member?
     *
     * @return bool
     */
    public function isPaTeamMember()
    {
        return $this->getRoleName() === self::ROLE_PA_TEAM_MEMBER;
    }

    /**
     * Is Professional Team member?
     *
     * @return bool
     */
    public function isProfTeamMember()
    {
        return $this->getRoleName() === self::ROLE_PROF_TEAM_MEMBER;
    }

    /**
     * @return bool
     */
    public function isOrgNamedOrAdmin()
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator();
    }

    /**
     * Is user an organisation Team Member?
     *
     * @return bool
     */
    public function isOrgTeamMember()
    {
        return $this->isPaTeamMember() || $this->isProfTeamMember();
    }

    /**
     * Is user an Organisation deputy? Any role. PA or Org.
     *
     * @return bool
     */
    public function isDeputyOrg()
    {
        return $this->isOrgNamedDeputy() || $this->isOrgAdministrator() || $this->isOrgTeamMember();
    }

    /**
     * @deprecated ID shouldn't be used anymore anywhere
     *
     * @param int $id
     *
     * @return string
     */
    public static function roleIdToName($id)
    {
        foreach ([
                     self::ROLE_ADMIN              => ['OPG Admin', 1],
                     self::ROLE_LAY_DEPUTY         => ['Lay Deputy', 2],
                     'ROLE_PROFESSIONAL_DEPUTY'    => ['Professional Deputy', 3],
                     'ROLE_LOCAL_AUTHORITY_DEPUTY' => ['Local Authority Deputy', 4],
                     self::ROLE_AD                 => ['Assisted Digital', 5],
                     self::ROLE_PA_NAMED                 => ['Pa', 6],
                     self::ROLE_PA_ADMIN           => ['Pa Admin', 7],
                     self::ROLE_PA_TEAM_MEMBER     => ['Pa Team member', 8],
                 ] as $name => $row) {
            if ($row[1] == $id) {
                return $name;
            }
        }
    }

    /**
     * Set role to team member
     */
    public function setDefaultRoleIfEmpty()
    {
        if (empty($this->getRoleName())) {
            if ($this->isProfDeputy()) {
                $this->setRoleName(User::ROLE_PROF_TEAM_MEMBER);
            } elseif ($this->isPaDeputy()) {
                $this->setRoleName(User::ROLE_PA_TEAM_MEMBER);
            }
        }
    }
}
