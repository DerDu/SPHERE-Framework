<?php
namespace SPHERE\Application\People\Person\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewPerson extends AbstractView
{

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
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition('TblSalutation_Salutation', 'Person Anrede');
        $this->setNameDefinition('TblPerson_Title', 'Person Titel');
        $this->setNameDefinition('TblPerson_FirstName', 'Person Vorname');
        $this->setNameDefinition('TblPerson_SecondName', 'Person Zweitname');
        $this->setNameDefinition('TblPerson_LastName', 'Person Nachname');
        $this->setNameDefinition('TblPerson_BirthName', 'Person Geburtsname');
    }
}
