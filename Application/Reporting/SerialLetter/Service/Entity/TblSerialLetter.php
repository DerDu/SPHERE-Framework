<?php
namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
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
    protected $tblFilterCategory;

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
     * @return bool|TblFilterCategory
     */
    public function getFilterCategory()
    {

        if (null === $this->tblFilterCategory) {
            return false;
        } else {
            return SerialLetter::useService()->getFilterCategoryById($this->tblFilterCategory);
        }
    }

    /**
     * @param TblFilterCategory|null $tblFilterCategory
     */
    public function setFilterCategory(TblFilterCategory $tblFilterCategory = null)
    {

        $this->tblFilterCategory = ( null === $tblFilterCategory ? null : $tblFilterCategory->getId() );
    }
}