<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:53
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblSerialLetter")
 * @Cache(usage="READ_ONLY")
 */
class TblSerialLetter extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_SERVICE_TBL_GROUP = 'serviceTblGroup';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Description;

     /**
      * @Column(type="bigint")
      */
    protected $serviceTblGroup;

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
     * @return bool|TblGroup
     */
    public function getServiceTblGroup()
    {

        if (null === $this->serviceTblGroup) {
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroup);
        }
    }

    /**
     * @param TblGroup|null $tblGroup
     */
    public function setServiceTblGroup($tblGroup)
    {

        $this->serviceTblGroup = ( null === $tblGroup ? null : $tblGroup->getId() );
    }
}