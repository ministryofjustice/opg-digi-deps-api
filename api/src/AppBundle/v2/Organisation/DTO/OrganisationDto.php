<?php

namespace AppBundle\v2\Organisation\DTO;

class OrganisationDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $organisationName;

    /** @var string */
    private $emailDomain;

    /** @var array */
    private $deputies;

    /** @var int */
    private $deputiesCount = 0;

    /** @var array */
    private $addresses;

    /** @var int */
    private $addressCount = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrganisationtName()
    {
        return $this->organisationName;
    }

    /**
     * @return string
     */
    public function getEmailDomain()
    {
        return $this->emailDomain;
    }

    /**
     * @return int
     */
    public function getDeputiesCount()
    {
        return $this->deputiesCount;
    }

    /**
     * @return array
     */
    public function getDeputies()
    {
        return $this->deputies;
    }

    /**
     * @return int
     */
    public function getAddressCount()
    {
        return $this->addressCount;
    }


    /**
     * @return array
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param $id
     *
     * @return OrganisationDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $organisationName
     *
     * @return OrganisationDto
     */
    public function setOrganisationName($organisationName)
    {
        $this->organisationName = $organisationName;
        return $this;
    }

    /**
     * @param string $emailDomain
     *
     * @return OrganisationDto
     */
    public function setEmailDomain($emailDomain)
    {
        $this->emailDomain = $emailDomain;
        return $this;
    }

    /**
     * @param string $deputies
     *
     * @return OrganisationDto
     */
    public function setDeputies($deputies)
    {
        $this->deputies = $deputies;
        return $this;
    }

    /**
     * @param int $deputiesCount
     *
     * @return OrganisationDto
     */
    public function setDeputiesCount($deputiesCount)
    {
        $this->deputiesCount = $deputiesCount;
        return $this;
    }

    /**
     * @param array $addresses
     *
     * @return OrganisationDto
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @param int $addressCount
     *
     * @return OrganisationDto
     */
    public function setAddressCount($addressCount)
    {
        $this->addressCount = $addressCount;
        return $this;
    }




}
