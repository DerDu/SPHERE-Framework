<?php
namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasket")
 * @Cache(usage="READ_ONLY")
 */
class TblBasket extends Element
{

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getCreateDate()
    {

        if (null === $this->EntityCreate) {
            return false;
        }
        /** @var \DateTime $CreateDate */
        $CreateDate = $this->EntityCreate;
        if ($CreateDate instanceof \DateTime) {
            return $CreateDate->format('d.m.Y H:i:s');
        } else {
            return (string)$CreateDate;
        }
    }
}
