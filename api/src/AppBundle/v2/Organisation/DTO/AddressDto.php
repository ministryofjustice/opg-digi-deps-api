<?php

namespace AppBundle\v2\Organisation\DTO;

class AddressDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $address1;

    /** @var string */
    private $address2;

    /** @var string */
    private $address3;

    /** @var string */
    private $address4;

    /** @var string */
    private $address5;

    /** @var string */
    private $country;

    /** @var string */
    private $postcode;

    /** @var string */
    private $email1;

    /** @var string */
    private $email2;

    /** @var string */
    private $email3;

    /** @var integer */
    private $deputyAddressNo;

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
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @return int
     */
    public function getDeputyAddressNo()
    {
        return $this->deputyAddressNo;
    }


    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $address1
     *
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @param string $address2
     *
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @param string $address3
     *
     * @return $this
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
        return $this;
    }

    /**
     * @param string $address4
     *
     * @return $this
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;
        return $this;
    }

    /**
     * @param string $address5
     *
     * @return $this
     */
    public function setAddress5($address5)
    {
        $this->address5 = $address5;
        return $this;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @param string $postcode
     *
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @param string $email1
     *
     * @return $this
     */
    public function setEmail1($email1)
    {
        $this->email1 = $email1;
        return $this;
    }

    /**
     * @param string $email2
     *
     * @return $this
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;
        return $this;
    }

    /**
     * @param string $email3
     *
     * @return $this
     */
    public function setEmail3($email3)
    {
        $this->email3 = $email3;
        return $this;
    }

    /**
     * @param int $deputyAddressNo
     *
     * @return $this
     */
    public function setDeputyAddressNo($deputyAddressNo)
    {
        $this->deputyAddressNo = $deputyAddressNo;
        return $this;
    }
}
