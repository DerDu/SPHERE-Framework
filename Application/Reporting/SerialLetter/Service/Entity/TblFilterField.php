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
 * @Table(name="tblFilterField")
 * @Cache(usage="READ_ONLY")
 */
class TblFilterField extends Element
{

    const ATTR_FIELD = 'Field';
    const ATTR_VALUE = 'Value';
    const ATTR_FILTER_NUMBER = 'FilterNumber';
    const ATTR_TBL_FILTER_CATEGORY = 'tblFilterCategory';
    const ATTR_TBL_SERIAL_LETTER = 'tblSerialLetter';

    /**
     * @Column(type="string")
     */
    protected $Field;
    /**
     * @Column(type="string")
     */
    protected $Value;
    /**
     * @Column(type="integer")
     */
    protected $FilterNumber;
    /**
     * @Column(type="bigint")
     */
    protected $tblFilterCategory;
    /**
     * @Column(type="bigint")
     */
    protected $tblSerialLetter;

    /**
     * @return string
     */
    public function getField()
    {

        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {

        $this->Field = $Field;
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return integer
     */
    public function getFilterNumber()
    {

        return $this->FilterNumber;
    }

    /**
     * @param integer $FilterNumber
     */
    public function setFilterNumber($FilterNumber)
    {

        $this->FilterNumber = $FilterNumber;
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
     * @param TblFilterCategory $tblFilterCategory
     */
    public function setFilterCategory(TblFilterCategory $tblFilterCategory)
    {

        $this->tblFilterCategory = ( null === $tblFilterCategory ? null : $tblFilterCategory->getId() );
    }

    /**
     * @return bool|TblSerialLetter
     */
    public function getTblSerialLetter()
    {

        if (null === $this->tblSerialLetter) {
            return false;
        } else {
            return SerialLetter::useService()->getSerialLetterById($this->tblSerialLetter);
        }
    }

    /**
     * @param null|TblSerialLetter $tblSerialLetter
     */
    public function setTblSerialLetter(TblSerialLetter $tblSerialLetter = null)
    {

        $this->tblSerialLetter = ( null === $tblSerialLetter ? null : $tblSerialLetter->getId() );
    }
}