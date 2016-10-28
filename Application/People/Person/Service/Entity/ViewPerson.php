<?php
namespace SPHERE\Application\People\Person\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewSubjectTeacher;
use SPHERE\Application\People\Meta\Club\Service\Entity\ViewPeopleMetaClub;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Custody\Service\Entity\ViewPeopleMetaCustody;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\ViewPeopleMetaTeacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewPerson extends AbstractView
{

    const TBL_SALUTATION_ID = 'TblSalutation_Id';
    const TBL_SALUTATION_SALUTATION = 'TblSalutation_Salutation';
    const TBL_SALUTATION_IS_LOCKED = 'TblSalutation_IsLocked';

    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_PERSON_TITLE = 'TblPerson_Title';
    const TBL_PERSON_FIRST_NAME = 'TblPerson_FirstName';
    const TBL_PERSON_SECOND_NAME = 'TblPerson_SecondName';
    const TBL_PERSON_LAST_NAME = 'TblPerson_LastName';
    const TBL_PERSON_BIRTH_NAME = 'TblPerson_BirthName';

    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_IsLocked;

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Personendaten';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION, 'Person: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE, 'Person: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME, 'Person: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME, 'Person: Zweitname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME, 'Person: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME, 'Person: Geburtsname');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_PERSON_ID, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewRelationshipFromPerson(), ViewRelationshipFromPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_TO);

        $this->addForeignView(self::TBL_PERSON_ID, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewMailToPerson(), ViewMailToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPhoneToPerson(), ViewPhoneToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);

        $this->addForeignView(self::TBL_PERSON_ID, new ViewStudent(), ViewStudent::TBL_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPeopleMetaClub(), ViewPeopleMetaClub::TBL_CLUB_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPeopleMetaCommon(), ViewPeopleMetaCommon::TBL_COMMON_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPeopleMetaCustody(), ViewPeopleMetaCustody::TBL_CUSTODY_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPeopleMetaProspect(), ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewPeopleMetaTeacher(), ViewPeopleMetaTeacher::TBL_TEACHER_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TEACHER_SERVICE_TBL_PERSON);
        $this->addForeignView(self::TBL_PERSON_ID, new ViewSubjectTeacher(), ViewSubjectTeacher::TBL_SUBJECT_TEACHER_SERVICE_TBL_PERSON);   // ToDO macht ohne Jahr wenig Sinn
        $this->addForeignView(self::TBL_PERSON_ID, new ViewAbsence(), ViewAbsence::TBL_ABSENCE_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Person::useService();
    }
}
