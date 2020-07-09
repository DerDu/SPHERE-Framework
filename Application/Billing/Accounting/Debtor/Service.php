<?php
namespace SPHERE\Application\Billing\Accounting\Debtor;

use SPHERE\Application\Billing\Accounting\Debtor\Service\Data;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorNumber;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Debtor
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param string         $GroupId
     * @param string         $Direction
     *
     * @return IFormInterface|string
     */
    public function directRoute(IFormInterface &$Form, $GroupId = null, $Direction = '')
    {

        /**
         * Skip to Frontend
         */
        if(null === $GroupId){
            return $Form;
        }
        if('0' === $GroupId){
            $Form->setError('GroupId', 'Bitte wählen Sie eine Gruppe aus');
            return $Form;
        }
        // Optisch lädt nur die richtige Seite
        if(($tblGroup = Group::useService()->getGroupById($GroupId))){
            if($tblGroup->getMetaTable() !== '' && $Direction == 'left'){
                return 'Lädt...'
                    .(new ProgressBar(0, 100, 0, 12))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
                    .new RedirectScript('/Billing/Accounting/Debtor/View', 0, array('GroupId' => $GroupId));
            }
            if($tblGroup->getMetaTable() === '' && $Direction == 'right'){
                return 'Lädt...'
                    .(new ProgressBar(0, 100, 0, 12))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
                    .new RedirectScript('/Billing/Accounting/Debtor/View', 0, array('GroupId' => $GroupId));
            }
        }
        return $Form;
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorNumberById($Id);
    }

    /**
     * @param $Number
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberByNumber($Number)
    {

        return (new Data($this->getBinding()))->getDebtorNumberByNumber($Number);
    }

    /**
 * @return int
 */
    public function getDebtorMaxNumber()
    {

        $result = 0;
        if(($tblDebtorNumberList = (new Data($this->getBinding()))->getDebtorNumberAll())) {
            foreach($tblDebtorNumberList as $tblDebtorNumber){
                if(is_numeric($tblDebtorNumber->getDebtorNumber()) && $tblDebtorNumber->getDebtorNumber() > $result){
                    $result = $tblDebtorNumber->getDebtorNumber();
                }
            }
        }
        return $result;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtorNumber[]
     */
    public function getDebtorNumberByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDebtorNumberByPerson($tblPerson);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return (new Data($this->getBinding()))->getBankAccountById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankAccount[]
     */
    public function getBankAccountAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBankAccountAllByPerson($tblPerson);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceById($Id)
    {

        return (new Data($this->getBinding()))->getBankReferenceById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBankReferenceByPerson($tblPerson);
    }

    /**
     * @param $ReferenceNumber
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceByReference($ReferenceNumber)
    {

        return (new Data($this->getBinding()))->getBankReferenceByReference($ReferenceNumber);
    }

    /**
     * @return int
     */
    public function getBankReferenceMaxNumber()
    {

        $result = 0;
        if(($tblBankReferenceList = (new Data($this->getBinding()))->getBankReferenceAll())) {
            foreach($tblBankReferenceList as $tblBankReference){
                if(is_numeric($tblBankReference->getReferenceNumber()) && $tblBankReference->getReferenceNumber() > $result){
                    $result = $tblBankReference->getReferenceNumber();
                }
            }
        }
        return $result;
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorPeriodType
     */
    public function getDebtorPeriodTypeById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorPeriodTypeById($Id);
    }

    /**
     * @param $Name
     *
     * @return false|TblDebtorPeriodType
     */
    public function getDebtorPeriodTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getDebtorPeriodTypeByName($Name);
    }

    /**
     * @return TblDebtorPeriodType[]|false
     */
    public function getDebtorPeriodTypeAll()
    {

        return (new Data($this->getBinding()))->getDebtorPeriodTypeAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionById($Id);
    }

    /**
     * @param TblPerson $tblPersonCauser
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPersonCauser(TblPerson $tblPersonCauser)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByPersonCauser($tblPersonCauser);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByItem($tblItem);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionFindTestByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionFindTestByItem($tblItem);
    }

    /**
     * @param TblPerson $tblPersonCauser
     * @param TblItem   $tblItem
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPersonCauserAndItem(TblPerson $tblPersonCauser, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByPersonCauserAndItem($tblPersonCauser, $tblItem);
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAllByBankReference(TblBankReference $tblBankReference)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionAllByBankReference($tblBankReference);
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAllByBankAccount(TblBankAccount $tblBankAccount)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionAllByBankAccount($tblBankAccount);
    }

    /**
     * @return false|TblBankAccount
     */
    public function getBankAccountAll()
    {

        return (new Data($this->getBinding()))->getBankAccountAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return (new Data($this->getBinding()))->getBankReferenceAll();
    }

    /**
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionAll()
    {

        return (new Data($this->getBinding()))->getDebtorSelectionAll();
    }

    /**
     * @return string
     */
    public function getDebtorSelectionCount()
    {

        return (new Data($this->getBinding()))->getDebtorSelectionCount();
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Number
     *
     * @return null|TblDebtorNumber
     */
    public function createDebtorNumber(TblPerson $tblPerson, $Number)
    {

        if($DebtorCountSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DEBTOR_NUMBER_COUNT)){
            $count = $DebtorCountSetting->getValue();
            // get the right length of DebtorNumber
            substr($Number, 0, $count);
            $Number = str_pad($Number, $count, '0', STR_PAD_LEFT);
        }

        return (new Data($this->getBinding()))->createDebtorNumber($tblPerson, $Number);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Owner
     * @param string    $BankName
     * @param string    $IBAN
     * @param string    $BIC
     *
     * @return null|TblBankAccount
     */
    public function createBankAccount(TblPerson $tblPerson, $Owner = '', $BankName = '', $IBAN = '', $BIC = '')
    {

        $IBAN = str_replace(' ', '', $IBAN);
        $BIC = str_replace(' ', '', $BIC);
        $IBAN = strtoupper($IBAN);
        return (new Data($this->getBinding()))->createBankAccount($tblPerson, $BankName, $IBAN, $BIC, $Owner);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $ReferenceNumber
     * @param string    $Description
     * @param string    $ReferenceDate
     *
     * @return null|TblBankReference
     */
    public function createBankReference(TblPerson $tblPerson, $ReferenceNumber = '', $Description = '', $ReferenceDate = '')
    {

        return (new Data($this->getBinding()))->createBankReference($tblPerson, $ReferenceNumber, $Description, $ReferenceDate);
    }

    /**
     * @param TblPerson             $tblPersonCauser
     * @param TblPerson             $tblPerson
     * @param TblPaymentType        $tblPaymentType
     * @param TblItem               $tblItem
     * @param TblDebtorPeriodType   $tblDebtorPeriodType
     * @param string                $FromDate
     * @param string|null           $ToDate
     * @param TblItemVariant|null   $tblItemVariant
     * @param string                $Value
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return null|TblDebtorSelection
     */
    public function createDebtorSelection(TblPerson $tblPersonCauser, TblPerson $tblPerson,
        TblPaymentType $tblPaymentType, TblItem $tblItem, TblDebtorPeriodType $tblDebtorPeriodType, $FromDate, $ToDate = null,
        TblItemVariant $tblItemVariant = null, $Value = '0', TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null
    ){

        $Value = str_replace(',', '.', $Value);
        // nicht benötigte Informationen entfernen
        if($tblPaymentType->getName() != 'SEPA-Lastschrift'){
            $tblBankAccount = null;
            $tblBankReference = null;
        }
        return (new Data($this->getBinding()))->createDebtorSelection($tblPersonCauser, $tblPerson, $tblPaymentType,
            $tblItem, $tblDebtorPeriodType, $FromDate, $ToDate, $tblItemVariant, $Value, $tblBankAccount, $tblBankReference);
    }

    /**
     * @param TblDebtorNumber $tblDebtorNumber
     * @param string          $Number
     *
     * @return bool
     */
    public function changeDebtorNumber(TblDebtorNumber $tblDebtorNumber, $Number = '')
    {

        if($DebtorCountSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DEBTOR_NUMBER_COUNT)){
            $count = $DebtorCountSetting->getValue();
            // get the right length of DebtorNumber
            substr($Number, 0, $count);
            $Number = str_pad($Number, $count, '0', STR_PAD_LEFT);
        }

        return (new Data($this->getBinding()))->updateDebtorNumber($tblDebtorNumber, $Number);
    }

    /**
     * @param TblBankAccount $tblBankAccount
     * @param string         $Owner
     * @param string         $BankName
     * @param string         $IBAN
     * @param string         $BIC
     *
     * @return bool
     */
    public function changeBankAccount(TblBankAccount $tblBankAccount, $Owner = '', $BankName = '', $IBAN = '', $BIC = ''
    ){

        $IBAN = str_replace(' ', '', $IBAN);
        $BIC = str_replace(' ', '', $BIC);
        $IBAN = strtoupper($IBAN);
        return (new Data($this->getBinding()))->updateBankAccount($tblBankAccount, $BankName, $IBAN, $BIC, $Owner);
    }

    /**
     * @param TblBankReference $tblBankReference
     * @param string           $ReferenceNumber
     * @param string           $Description
     * @param string           $ReferenceDate
     *
     * @return bool
     */
    public function changeBankReference(TblBankReference $tblBankReference, $ReferenceNumber = '', $Description = '', $ReferenceDate = '')
    {

        return (new Data($this->getBinding()))->updateBankReference($tblBankReference, $ReferenceNumber, $Description,
            $ReferenceDate);
    }

    /**
     * @param TblDebtorSelection    $tblDebtorSelection
     * @param TblPerson             $tblPerson
     * @param TblPaymentType        $tblPaymentType
     * @param TblDebtorPeriodType   $tblDebtorPeriodType
     * @param string                $FromDate
     * @param string|null           $ToDate
     * @param TblItemVariant|null   $tblItemVariant
     * @param string                $Value
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool
     */
    public function changeDebtorSelection(TblDebtorSelection $tblDebtorSelection, TblPerson $tblPerson,
        TblPaymentType $tblPaymentType, TblDebtorPeriodType $tblDebtorPeriodType, $FromDate, $ToDate = '',TblItemVariant $tblItemVariant = null,
        $Value = '0', TblBankAccount $tblBankAccount = null, TblBankReference $tblBankReference = null
    ){

        $Value = str_replace(',', '.', $Value);

        //Pflichtfeld
        $FromDate = new \DateTime($FromDate);
        // (kein Pflichtfeld)
        // hiermit kann das ToDate wieder entfernt werden
        if('' === $ToDate){
            $ToDate = null;
        } else {
            $ToDate = new \DateTime($ToDate);
        }

        // nicht benötigte Informationen entfernen
        switch($tblPaymentType->getName()){
            case 'Bar':
                $tblBankAccount = null;
                $tblBankReference = null;
            break;
            case 'SEPA-Überweisung':
                $tblBankReference = null;
        }


        return (new Data($this->getBinding()))->updateDebtorSelection($tblDebtorSelection, $tblPerson, $tblPaymentType,
            $tblDebtorPeriodType, $FromDate, $ToDate, $tblItemVariant, $Value, $tblBankAccount, $tblBankReference);
    }

    /**
     * @param TblDebtorNumber $tblDebtorNumber
     *
     * @return bool
     */
    public function removeDebtorNumber(TblDebtorNumber $tblDebtorNumber)
    {

        return (new Data($this->getBinding()))->removeDebtorNumber($tblDebtorNumber);
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return bool
     */
    public function removeBankAccount(TblBankAccount $tblBankAccount)
    {

        return (new Data($this->getBinding()))->removeBankAccount($tblBankAccount);
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return bool
     */
    public function removeBankReference(TblBankReference $tblBankReference)
    {

        return (new Data($this->getBinding()))->removeBankReference($tblBankReference);
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return bool
     */
    public function removeDebtorSelection(TblDebtorSelection $tblDebtorSelection)
    {

        return (new Data($this->getBinding()))->removeDebtorSelection($tblDebtorSelection);
    }
}
