<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentTransfer extends AbstractView
{

    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_SERVICE_TBL_PERSON = 'TblStudent_serviceTblPerson';

    const TBL_STUDENT_TRANSFER_ID = 'TblStudentTransfer_Id';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_COMPANY = 'TblStudentTransfer_serviceTblCompany';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_TYPE = 'TblStudentTransfer_serviceTblType';
    const TBL_STUDENT_TRANSFER_SERVICE_TBL_COURSE = 'TblStudentTransfer_serviceTblCourse';
    const TBL_STUDENT_TRANSFER_TRANSFER_DATE = 'TblStudentTransfer_TransferDate';
    const TBL_STUDENT_TRANSFER_REMARK = 'TblStudentTransfer_Remark';
    const TBL_STUDENT_TRANSFER_TBL_STUDENT = 'TblStudentTransfer_tblStudent';
    const TBL_STUDENT_TRANSFER_TBL_STUDENT_TRANSFER_TYPE = 'TblStudentTransfer_tblStudentTransferType';

    const TBL_STUDENT_TRANSFER_TYPE_ID = 'TblStudentTransferType_Id';
    const TBL_STUDENT_TRANSFER_TYPE_NAME = 'TblStudentTransferType_Name';

    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_serviceTblCourse;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_tblStudent;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransfer_tblStudentTransferType;

    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferType_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Schülertransfer';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_TRANSFER_DATE, 'Schülertransfer: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_REMARK, 'Schülertransfer: Bemerkungen');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_TYPE_NAME, 'Schülertransfer: Art');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

//        $this->addForeignView(self::TBL_STUDENT_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {

        return Student::useService();
    }

    /**
     * @return mixed
     */
    public function getTblStudentTransferType_Name()
    {

        return ( $this->TblStudentTransferType_Name === 'Process' ? 'Aktuell' : $this->TblStudentTransferType_Name );
    }

//    /**
//     * @return mixed
//     */
//    public function getTblStudentTransfer_TransferDate()
//    {
//
//        $result = '';
//        if(null !== $this->TblStudentTransfer_TransferDate){
//            $result = (new \DateTime($this->TblStudentTransfer_TransferDate))->format('d.m.Y');
//        }
//
//        return $result;
//    }
}
