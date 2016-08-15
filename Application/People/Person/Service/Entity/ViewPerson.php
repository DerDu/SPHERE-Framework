<?php
namespace SPHERE\Application\People\Person\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Person\Person;
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
        $this->addForeignView(self::TBL_PERSON_ID, new ViewAddressToPerson(), ViewAddressToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Person::useService();
    }
}
