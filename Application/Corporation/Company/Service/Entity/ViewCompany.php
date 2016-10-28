<?php
namespace SPHERE\Application\Corporation\Company\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToCompany;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewCompany extends AbstractView
{

    const TBL_COMPANY_ID = 'TblCompany_Id';
    const TBL_COMPANY_NAME = 'TblCompany_Name';
    const TBL_COMPANY_EXTENDED_NAME = 'TblCompany_ExtendedName';
    const TBL_COMPANY_DESCRIPTION = 'TblCompany_Description';

    /**
     * @Column(type="string")
     */
    protected $TblCompany_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Firmendaten';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_COMPANY_NAME, 'Firma: Name');
        $this->setNameDefinition(self::TBL_COMPANY_EXTENDED_NAME, 'Firma: Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_DESCRIPTION, 'Firma: Beschreibung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_COMPANY_ID, new ViewRelationshipToCompany(), ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY);
        $this->addForeignView(self::TBL_COMPANY_ID, new ViewAddressToCompany(), ViewAddressToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY);
        $this->addForeignView(self::TBL_COMPANY_ID, new ViewMailToCompany(), ViewMailToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY);
        $this->addForeignView(self::TBL_COMPANY_ID, new ViewPhoneToCompany(), ViewPhoneToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY);
//        $this->addForeignView(self::TBL_PERSON_ID, new ViewRelationshipToPerson(), ViewRelationshipToPerson::TBL_TO_PERSON_SERVICE_TBL_PERSON_FROM);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Company::useService();
    }
}
