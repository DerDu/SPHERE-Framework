<?php
namespace SPHERE\Application\Contact\Mail\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewMailToCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewMailToCompany extends AbstractView
{

    const TBL_TO_COMPANY_ID = 'TblToCompany_Id';
    const TBL_TO_COMPANY_SERVICE_TBL_COMPANY = 'TblToCompany_serviceTblCompany';
    const TBL_TO_COMPANY_REMARK = 'TblToCompany_Remark';
    const TBL_TO_COMPANY_TBL_MAIL = 'TblToCompany_tblMail';
    const TBL_TO_COMPANY_TBL_TYPE = 'TblToCompany_tblType';

    const TBL_MAIL_ID = 'TblMail_Id';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';

    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';

    /**
     * @Column(type="string")
     */
    protected $TblToCompany_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_tblMail;
    /**
     * @Column(type="string")
     */
    protected $TblToCompany_tblType;

    /**
     * @Column(type="string")
     */
    protected $TblMail_Id;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;

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

    /** (position for order)
     * @Column(type="string")
     */
    protected $TblToCompany_Remark;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Kontakt E-Mail (Firma)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'E-Mail: Adresse');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'E-Mail: Typ');
        $this->setNameDefinition(self::TBL_TO_COMPANY_REMARK, 'E-Mail: Bemerkung');
    }

    public function loadDisableDefinition()
    {

        $this->setDisableDefinition(self::TBL_TYPE_DESCRIPTION);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, new ViewCompany(), ViewCompany::TBL_COMPANY_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {

        return Mail::useService();
    }
}
