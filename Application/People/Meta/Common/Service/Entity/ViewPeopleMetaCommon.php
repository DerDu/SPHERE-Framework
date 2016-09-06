<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaCommon")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaCommon extends AbstractView
{

    /**
     * @Column(type="string")
     */
    protected $TblCommon_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;

    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonBirthDates;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthday;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthplace;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Gender;

    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonInformation;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition('TblCommon_Remark', 'Meta-Common Bemerkungen');

        $this->setNameDefinition('TblCommonBirthDates_Gender', 'Meta-Common Geschlecht');
        $this->setNameDefinition('TblCommonBirthDates_Birthplace', 'Meta-Common Geburtsort');
        $this->setNameDefinition('TblCommonBirthDates_Birthday', 'Meta-Common Geburtsdatum');

        $this->setNameDefinition('TblCommonInformation_Nationality', 'Meta-Common Nationalität');
        $this->setNameDefinition('TblCommonInformation_Denomination', 'Meta-Common Konfession');
        $this->setNameDefinition('TblCommonInformation_AssistanceActivity', 'Meta-Common Aktivitäten');
        $this->setNameDefinition('TblCommonInformation_IsAssistance', 'Meta-Common Mitarbeitsbereitschaft');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }
}
