<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Organisation
 *
 * @ORM\Table(name="address")
 * @ORM\Entity()
 */
class Address
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"address", "address-id"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="address_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("integer")
     * @ORM\Column(name="deputyAddressNo", type="integer", length=10, nullable=true)
     */
    private $deputyAddressNo;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="address1", type="string", length=100, nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="email1", type="string", length=100, nullable=true)
     */
    private $email1;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="email2", type="string", length=100, nullable=true)
     */
    private $email2;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="email3", type="string", length=100, nullable=true)
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="address2", type="string", length=100, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="address3", type="string", length=100, nullable=true)
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="address4", type="string", length=100, nullable=true)
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="address5", type="string", length=100, nullable=true)
     */
    private $address5;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="postcode", type="string", length=8, nullable=true)
     */
    private $postcode;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     * @ORM\Column(name="country", type="string", length=10, nullable=true)
     */
    private $country;

    /**
     * Address constructor.
     *
     * @param array $address
     */
    public function __construct(array $address)
    {
        $this->address1 = array_key_exists('address1', $address) ? $address['address1'] : null;
        $this->address2 = array_key_exists('address2', $address) ? $address['address2'] : null;
        $this->address3 = array_key_exists('address3', $address) ? $address['address3'] : null;
        $this->address4 = array_key_exists('address4', $address) ? $address['address4'] : null;
        $this->address5 = array_key_exists('address5', $address) ? $address['address5'] : null;
        $this->postcode = array_key_exists('postcode', $address) ? $address['postcode'] : null;
        $this->country = array_key_exists('country', $address) ? $address['country'] : null;
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
     * @return Address
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getDeputyAddressNo()
    {
        return $this->deputyAddressNo;
    }

    /**
     * @param integer $deputyAddressNo
     *
     * @return Address
     */
    public function setDeputyAddressNo($deputyAddressNo)
    {
        $this->deputyAddressNo = $deputyAddressNo;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return Address
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return Address
     */
    public function setEmail1($email1)
    {
        $this->email1 = $email1;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return Address
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return Address
     */
    public function setEmail3($email3)
    {
        $this->email3 = $email3;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return Address
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     *
     * @return Address
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     *
     * @return Address
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     *
     * @return Address
     */
    public function setAddress5($address5)
    {
        $this->address5 = $address5;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     *
     * @return Address
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


}
