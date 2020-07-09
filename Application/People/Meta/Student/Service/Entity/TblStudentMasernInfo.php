<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentMasernInfo")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentMasernInfo extends Element
{

    const ATTR_META = 'Meta';
    const ATTR_TYPE = 'Type';
    const ATTR_TEXT_SHORT = 'TextShort';
    const ATTR_TEXT_LONG = 'TextLong';

    const TYPE_DOCUMENT = 'Document';
    const TYPE_CREATOR = 'Creator';

    const DOCUMENT_IDENTIFICATION = 'Identification';
    const DOCUMENT_VACCINATION_PROTECTION = 'VaccinationProtection';
    const DOCUMENT_IMMUNITY = 'Immunity';
    const DOCUMENT_CANT_VACCINATION = 'CantVaccination';

    const CREATOR_STATE = 'State';
    const CREATOR_COMMUNITY = 'Community';

    /**
     * @Column(type="string")
     */
    protected $Meta;
    /**
     * @Column(type="string")
     */
    protected $Type;
    /**
     * @Column(type="string")
     */
    protected $TextShort;
    /**
     * @Column(type="string")
     */
    protected $TextLong;

    /**
     * @return string
     */
    public function getMeta()
    {
        return $this->Meta;
    }

    /**
     * @param string $Meta
     */
    public function setMeta($Meta)
    {
        $this->Meta = $Meta;
    }

    /**
     * @return string
     */
    public function getType()
    {
        
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {
        
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getTextShort()
    {
        
        return $this->TextShort;
    }

    /**
     * @param string $TextShort
     */
    public function setTextShort($TextShort)
    {
        
        $this->TextShort = $TextShort;
    }

    /**
     * @return string
     */
    public function getTextLong()
    {
        
        return $this->TextLong;
    }

    /**
     * @param string $TextLong
     */
    public function setTextLong($TextLong)
    {
        
        $this->TextLong = $TextLong;
    }
}
