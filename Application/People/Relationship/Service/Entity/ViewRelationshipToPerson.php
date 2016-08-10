<?php
namespace SPHERE\Application\People\Relationship\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewRelationshipToPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewRelationshipToPerson extends AbstractView
{

    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPersonFrom;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPersonTo;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_tblType;

    /**
     * @Column(type="string")
     */
    protected $TblType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblType_IsLocked;
    /**
     * @Column(type="string")
     */
    protected $TblType_IsBidirectional;
    /**
     * @Column(type="string")
     */
    protected $TblType_tblGroup;

    /**
     * @Column(type="string")
     */
    protected $TblGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Description;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {
        // TODO: Implement loadNameDefinition() method.
    }
}
