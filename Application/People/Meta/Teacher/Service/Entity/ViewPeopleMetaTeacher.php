<?php
namespace SPHERE\Application\People\Meta\Teacher\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaTeacher")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaTeacher extends AbstractView
{

    const TBL_TEACHER_ID = 'TblTeacher_Id';
    const TBL_TEACHER_SERVICE_TBL_PERSON = 'TblTeacher_serviceTblPerson';
    const TBL_TEACHER_ACRONYM = 'TblTeacher_Acronym';

    /**
     * @Column(type="string")
     */
    protected $TblTeacher_Id;
    /**
     * @Column(type="string")
     */
    protected $TblTeacher_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblTeacher_Acronym;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Lehrer';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_TEACHER_ACRONYM, 'Lehrer: Kürzel');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_TEACHER_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Teacher::useService();
    }
}
