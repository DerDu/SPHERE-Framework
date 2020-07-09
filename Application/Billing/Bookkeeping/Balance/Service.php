<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Facade\CustomerCreditFacade;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceCreditor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
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

    public function getPriceString($Value)
    {

        return number_format($Value, 2, ',', '').' €';
    }

    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentTypeById($Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return (new Data($this->getBinding()))->getPaymentTypeAll();
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getPaymentTypeByName($Name);
    }

    public function getPersonListByInvoiceTime($Year, $From, $To)
    {

        $tblPersonList = array();
        $PersonIdList = (new Data($this->getBinding()))->getPersonIdListByInvoiceTime($Year, $From, $To);
        if(!empty($PersonIdList)){
            foreach($PersonIdList as $IdArray){
                $PersonId = $IdArray['PersonCauserId'];
                if(($tblPerson = Person::useService()->getPersonById($PersonId))){
                    $tblPersonList[] = $tblPerson;
                }
            }
        }

        return !empty($tblPersonList) ? $tblPersonList : false;
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Year
     * @param string  $BasketTypeId
     * @param string  $MonthFrom
     * @param string  $MonthTo
     * @param string  $DivisionId
     * @param string  $GroupId
     * @param array   $PriceList
     *
     * @return array
     */
    public function getPriceListByItemAndYear(
        TblItem $tblItem,
        $Year,
        $BasketTypeId = '',
        $MonthFrom = '1',
        $MonthTo = '12',
        $DivisionId = '0',
        $GroupId = '0',
        $PriceList = array()
    ){

        $tblBasketTypeA = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
        $tblBasketTypeB = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_GUTSCHRIFT);
        $ResultList = $this->getPriceList($tblItem, $Year, $BasketTypeId, $MonthFrom, $MonthTo);
        if($ResultList){
            foreach($ResultList as $Key => $RowContent) {
                $PersonDebtorId = isset($RowContent['PersonDebtorId']) ? $RowContent['PersonDebtorId'] : false;
                $PersonCauserId = isset($RowContent['PeronCauserId']) ? $RowContent['PeronCauserId'] : false;
                $timeString = isset($RowContent['Year']) && isset($RowContent['Month']) ? $RowContent['Year'].'/'.$RowContent['Month'] : false;
                if($PersonDebtorId && $PersonCauserId && $timeString){
                    if(isset($RowContent['IsPaid']) && $RowContent['IsPaid'] && $BasketTypeId != '-1'){
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'];
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Price'][$timeString] = $RowContent['Value'];
                    } elseif(isset($RowContent['IsPaid']) && $RowContent['IsPaid'] && $BasketTypeId == '-1') {
                        if($RowContent['BasketTypeId'] == $tblBasketTypeA->getId()){
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'];
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Price'][$timeString] = $RowContent['Value'];
                        }
                        if($RowContent['BasketTypeId'] == $tblBasketTypeB->getId()){
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'] * -1;
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['PriceSub'][$timeString] = $RowContent['Value'];
                        }
                    } else {
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['PriceMissing'][$timeString] = $RowContent['Value'];
                    }
                }
            }
        }

        if(!empty($PriceList)){
            foreach($PriceList as &$DebtorContent) {
                foreach($DebtorContent as &$CauserContent) {
                    foreach($CauserContent as &$ItemContent) {
                        if (isset($ItemContent['Sum'])){
                            $ItemContent['Sum'] = array_sum($ItemContent['Sum']);
                        } else {
                            $ItemContent['Sum'] = 0;
                        }
                    }
                }
            }
        }

        // use only division matched Person's
        if(!empty($PriceList) && $DivisionId !== '0' && ($tblDivision = Division::useService()->getDivisionById($DivisionId))){
            $tblPersonList = Division::useService()->getPersonAllByDivisionList(array($tblDivision));
            foreach($PriceList as &$DebtorList){
                foreach($DebtorList as $CauserId => &$Content){
                    $tblPersonCauser = Person::useService()->getPersonById($CauserId);
                    if(!in_array($tblPersonCauser, $tblPersonList)){
                        $Content = false;
                    }
                }
                // remove mismatched Student
                $DebtorList = array_filter($DebtorList);
            }
            // remove empty DebtorList
            $PriceList = array_filter($PriceList);
        }

        // use only division matched Person's
        if(!empty($PriceList) && $GroupId !== '0' && ($tblGroup = Group::useService()->getGroupById($GroupId))){
            foreach($PriceList as &$DebtorList){
                foreach($DebtorList as $CauserId => &$Content){
                    if (($tblPersonCauser = Person::useService()->getPersonById($CauserId))
                        && !Group::useService()->existsGroupPerson($tblGroup, $tblPersonCauser)
                    ) {
                        $Content = false;
                    }
                }
                // remove mismatched Student
                $DebtorList = array_filter($DebtorList);
            }
            // remove empty DebtorList
            $PriceList = array_filter($PriceList);
        }

        return $PriceList;
    }

    /**
     * @param array $PriceList
     *
     * @return array
     */
    public function getTableContentByItemPriceList($PriceList = array())
    {

        $tableContent = array();
        if(!empty($PriceList)){
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
                    foreach($CauserList as $CauserId => $ItemContent) {
                        $this->fillColumnRowItemPriceList($tableContent, $tblPersonDebtor, $CauserId, $ItemContent);
                    }
                }
            }
        }
        return $tableContent;
    }

    /**
     * @param array     $tableContent
     * @param TblPerson $tblPersonDebtor
     * @param string    $CauserId
     * @param array     $ItemContent
     */
    private function fillColumnRowItemPriceList(&$tableContent, TblPerson $tblPersonDebtor, $CauserId, $ItemContent)
    {

        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))){
            $item['Debtor'] = $tblPersonDebtor->getLastFirstName();
            $item['Causer'] = $tblPersonCauser->getLastFirstName();
            $item['Info'] = '';
            $item['Summary'] = 0;
            foreach ($ItemContent as $ItemId => $Value){
                $MonthOpenList = array();
                $MonthList = array();
                $MonthSubList = array();
                $item['Summary'] += $Value['Sum'];
                $item['Value'] = Balance::useService()->getPriceString($Value['Sum']);
                if(($tblItem = Item::useService()->getItemById($ItemId))){
                    $item['Id'.$tblItem->getId()] = Balance::useService()->getPriceString($Value['Sum']);

                    if(isset($Value['PriceMissing'])){
                        foreach($Value['PriceMissing'] as $Time => $PriceMissing) {
                            $MonthOpenList[] = new DangerText(Balance::useService()->getPriceString($PriceMissing).' ('.$Time.')');
                        }
                        $item['Info'] = new DangerText(new ToolTip(new EyeOpen(), 'Offene Posten'));
                    }
                    if(isset($Value['Price'])){
                        foreach($Value['Price'] as $Time => $Price) {
                            $MonthList[] = Balance::useService()->getPriceString($Price).' ('.$Time.')';
                        }
                    }
                    if(isset($Value['PriceSub'])){
                        foreach($Value['PriceSub'] as $Time => $PriceSub) {
                            $MonthSubList[] = new DangerText('-'.Balance::useService()->getPriceString($PriceSub)).' ('.$Time.') ';
                        }
                        $item['Info'] .= new ToolTip(new EyeOpen(), 'enthält Gutschriften');
                    }
                    $ToolTipMonthPrice = '';
                    if(!empty($MonthOpenList)){
                        $ToolTipMonthPrice .= new Bold('Offene Posten<br/>').implode('<br/>',
                                $MonthOpenList).new Ruler();
                    }

                    $ToolTipMonthPrice .= new Bold('Bezahlt<br/>').implode('<br/>', $MonthList);
                    if(!empty($MonthSubList)){
                        $ToolTipMonthPrice .= new Ruler().new Bold('Gutschrift<br/>').implode('<br/>', $MonthSubList);
                    }

                    $item['Id'.$tblItem->getId()] .= '&nbsp;&nbsp;&nbsp;'
                        .(new ToolTip(new Info(), htmlspecialchars($ToolTipMonthPrice)))->enableHtml();
                    $item['Value'] .= '&nbsp;&nbsp;&nbsp;'
                        .(new ToolTip(new Info(), htmlspecialchars($ToolTipMonthPrice)))->enableHtml();
                }
            }
            $item['Summary'] = Balance::useService()->getPriceString($item['Summary']);

            array_push($tableContent, $item);
        }
    }

    /**
     * @param array $PersonList
     *
     * @return array $PersonList
     */
    private function sortPersonListByDebtorName($PersonList = array())
    {

        $lastName = array();
        $firstName = array();
        if(!empty($PersonList)){
            foreach($PersonList as $Key => $row) {

                $lastName[$Key] = strtoupper($row['DebtorLastName']);
                $firstName[$Key] = strtoupper($row['DebtorFirstName']);
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $PersonList);
        }
        return $PersonList;
    }

    /**
     * @param array $PriceList
     * @param array TblItem[] $tblItemList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createBalanceListExcel($PriceList, $tblItemList = array())
    {

        $PersonList = array();
        if(!empty($PriceList)){
            foreach($PriceList as $DebtorId => $CauserList) {
                if(($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))){
                    foreach($CauserList as $CauserId => $ItemContent) {
                        if(($tblPersonCauser = Person::useService()->getPersonById($CauserId))){
                            $Item = array();
                            $Item['Value'] = '';
                            // Debtor
                            $Item['DebtorSalutation'] = $tblPersonDebtor->getSalutation();
                            $Item['DebtorTitle'] = $tblPersonDebtor->getTitle();
                            $Item['DebtorFirstName'] = $tblPersonDebtor->getFirstName();
                            $Item['DebtorLastName'] = $tblPersonDebtor->getLastName();
                            // Causer
                            $Item['CauserFirstName'] = $tblPersonCauser->getFirstName();
                            $Item['CauserLastName'] = $tblPersonCauser->getLastName();

                            // Gesamt
                            $Summary = 0;
                            foreach($ItemContent as $ItemId => $value){
                                if(($tblItem = Item::useService()->getItemById($ItemId))){
                                    $Item['Id'.$tblItem->getId()] = Balance::useService()->getPriceString($value['Sum']);
                                    // Gesammt addieren
                                    $Summary += $value['Sum'];
                                }
                            }
                            $Summary = Balance::useService()->getPriceString($Summary);
                            $Item['Summary'] = $Summary;

                            $Item['Street'] = $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                            if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))){
                                $Item['StreetName'] = $tblAddress->getStreetName();
                                $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                                $Item['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                                $Item['Code'] = $tblAddress->getTblCity()->getCode();
                                $Item['City'] = $tblAddress->getTblCity()->getName();
                                $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                            }
                            array_push($PersonList, $Item);
                        }
                    }
                }
            }
        }
        $PersonList = $this->sortPersonListByDebtorName($PersonList);

        if(!empty($PersonList)){

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;

            $export->setValue($export->getCell($column++, $row), "Anrede Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Titel Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Vorname Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Nachname Beitragszahler");
            $export->setValue($export->getCell($column++, $row), "Vorname Beitragsverursacher");
            $export->setValue($export->getCell($column++, $row), "Nachname Beitragsverursacher");
            $export->setValue($export->getCell($column++, $row), "Straße");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Stadt");
            $export->setValue($export->getCell($column++, $row), "Ortsteil");
            foreach($tblItemList as $tblItem){
                $export->setValue($export->getCell($column++, $row), $tblItem->getName());
            }
            $export->setValue($export->getCell($column, $row), "Gesamt");

            foreach($PersonList as $PersonData) {
                $column = 0;
                $row++;
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorSalutation']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorTitle']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorFirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['DebtorLastName']);

                $export->setValue($export->getCell($column++, $row), $PersonData['CauserFirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['CauserLastName']);

                $export->setValue($export->getCell($column++, $row), $PersonData['Street']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
                $export->setValue($export->getCell($column++, $row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $row), $PersonData['District']);

                foreach($tblItemList as $tblItem){
                    if(isset($PersonData['Id'.$tblItem->getId()])){
                        $export->setValue($export->getCell($column++, $row), $PersonData['Id'.$tblItem->getId()]);
                    } else {
                        $export->setValue($export->getCell($column++, $row), '');
                    }
                }
                $export->setValue($export->getCell($column, $row), $PersonData['Summary']);
            }

            //Column width
//            $column = 0;
//            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Year
     * @param string  $BasketTypeId
     * @param string  $MonthFrom
     * @param string  $MonthTo
     *
     * @return array|bool
     */
    public function getPriceList(TblItem $tblItem, $Year, $BasketTypeId, $MonthFrom, $MonthTo)
    {

        return (new Data($this->getBinding()))->getPriceList($tblItem, $Year, $BasketTypeId, $MonthFrom, $MonthTo);
    }

    /**
     * @param TblItem   $tblItem
     * @param string    $Year
     * @param string    $MonthFrom
     * @param string    $MonthTo
     * @param TblPerson $tblPerson
     * @param string    $BasketTypeId
     * @param array     $PriceList
     *
     * @return array
     */
    public function getPriceListByItemAndPerson(TblItem $tblItem, $Year, $MonthFrom, $MonthTo, TblPerson $tblPerson,
        $BasketTypeId = '', $PriceList = array())
    {
        $ResultList = (new Data($this->getBinding()))->getPriceListByPerson($tblItem, $Year, $MonthFrom, $MonthTo,
            $tblPerson, $BasketTypeId);
        $tblBasketTypeA = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
        $tblBasketTypeB = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_GUTSCHRIFT);

        if ($ResultList){
            foreach ($ResultList as $Key => $RowContent) {
                $PersonDebtorId = isset($RowContent['PersonDebtorId']) ? $RowContent['PersonDebtorId'] : false;
                $PersonCauserId = isset($RowContent['PeronCauserId']) ? $RowContent['PeronCauserId'] : false;
                $timeString = isset($RowContent['Year']) && isset($RowContent['Month']) ? $RowContent['Year'].'/'.$RowContent['Month'] : false;
                if ($PersonDebtorId && $PersonCauserId && $timeString){
                    if (isset($RowContent['IsPaid']) && $RowContent['IsPaid'] && $BasketTypeId != '-1'){
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'];
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Price'][$timeString] = $RowContent['Value'];
                    } elseif (isset($RowContent['IsPaid']) && $RowContent['IsPaid'] && $BasketTypeId == '-1') {
                        if ($RowContent['BasketTypeId'] == $tblBasketTypeA->getId()){
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'];
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Price'][$timeString] = $RowContent['Value'];
                        }
                        if ($RowContent['BasketTypeId'] == $tblBasketTypeB->getId()){
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['Sum'][] = $RowContent['Value'] * -1;
                            $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['PriceSub'][$timeString] = $RowContent['Value'];
                        }
                    } else {
                        $PriceList[$PersonDebtorId][$PersonCauserId][$tblItem->getId()]['PriceMissing'][$timeString] = $RowContent['Value'];
                    }
                }
            }
        }
        return $PriceList;
    }

    /**
     * @param $PriceList
     *
     * @return array $PriceList
     */
    public function getSummaryByItemPrice($PriceList)
    {

        if(!empty($PriceList)){
            foreach($PriceList as &$DebtorContent) {
                foreach($DebtorContent as &$CauserContent) {
                    foreach($CauserContent as &$ItemContent) {
                        if (isset($ItemContent['Sum'])){
                            $ItemContent['Sum'] = array_sum($ItemContent['Sum']);
                        } else {
                            $ItemContent['Sum'] = 0;
                        }
                    }
                }
            }
        }
        return $PriceList;
    }

    /**
     * @param string $Year
     * @param string $Month
     *
     * @return array|bool
     */
    public function getPriceSummaryByMonth($Year, $Month)
    {

        return (new Data($this->getBinding()))->getPriceSummaryByMonth($Year, $Month);
    }

    /**
     * @param string $Year
     *
     * @return array|bool
     */
    public function getPriceSummaryByYear($Year)
    {

        return (new Data($this->getBinding()))->getPriceSummaryByYear($Year);
    }

    public function createDatevCsv(TblBasket $tblBasket)
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByBasket($tblBasket);
        if(!$tblInvoiceList){
            return false;
        }
        $direction = 'S';
        if(($tblBasketType = $tblBasket->getTblBasketType())){
            switch ($tblBasketType->getName()) {
                case TblBasketType::IDENT_ABRECHNUNG:
                    $direction = 'S';
                    break;
                case TblBasketType::IDENT_AUSZAHLUNG:
                case TblBasketType::IDENT_GUTSCHRIFT:
                    $direction = 'H';
                    break;
            }
        }

        if ($tblInvoiceList) {
            $now = new \DateTime();
            $milliseconds = round(microtime(true) * 1000);
            $milliseconds = substr($milliseconds, -3, 3);
            $time = $now->format('Ymdhis').$milliseconds;
            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            // Auswahl des Trennzeichen's
            $export->setDelimiter(';');
            $YearBegin = $tblBasket->getBillYear().'0101';
            $BookingFrom = $tblBasket->getBillYearMonth();
            $BookingTo = $tblBasket->getBillYearMonth(true);

            $ConsultNumber = '1';
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_CONSULT_NUMBER))){
                $ConsultNumber = $tblSetting->getValue();
            }
            $ClientNumber = '1';
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_CLIENT_NUMBER))){
                $ClientNumber = $tblSetting->getValue();
            }
            $NumberLength = '8';
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH))){
                $NumberLength = $tblSetting->getValue();
            }
            $Acronym = '';
            if(($tblAccount = Account::useService()->getAccountBySession())){
                $PersonList = Account::useService()->getPersonAllByAccount($tblAccount);
                if($PersonList){
                    /** @var TblPerson $tblPerson */
                    $tblPerson = current($PersonList);
                    $Acronym = substr($tblPerson->getFirstName(), 0, 1).substr($tblPerson->getLastName(), 0, 1);
                }
            }

            // decision FibuKonto
            $IsFibuDebtorNumber = false;
            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DATEV))){
                if(($tblSetting->getValue())){
                    if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR))){
                        if(($tblSetting->getValue())){
                            $IsFibuDebtorNumber = true;
                        }
                    }
                }
            }

            $row = 0;
            $export->setValue($export->getCell("0", $row), "EXTF");
            $export->setValue($export->getCell("1", $row), "510");
            $export->setValue($export->getCell("2", $row), "21");
            $export->setValue($export->getCell("3", $row), "Buchungsstapel");
            $export->setValue($export->getCell("4", $row), "7");
            $export->setValue($export->getCell("5", $row), $time);
            $export->setValue($export->getCell("6", $row), "");     // muss leer sein
            $export->setValue($export->getCell("7", $row), "RE");
            $export->setValue($export->getCell("8", $row), "");     // Export User -> leer lassen!
            $export->setValue($export->getCell("9", $row), "");     // muss leer sein
            $export->setValue($export->getCell("10", $row), $ConsultNumber);   // Beraternummer über Option kann bis zu 7 Stellen
            $export->setValue($export->getCell("11", $row), $ClientNumber);   // Mandantennummer über Option  Schulart bedingt unterschiedlich bis zu 5 Stellen
            $export->setValue($export->getCell("12", $row), $YearBegin);// WJ-Beginn Aktuelle Jahr vorne ziehen
            $export->setValue($export->getCell("13", $row), $NumberLength);   // "Sachkonten Nummernlänge" über Option 4 bis 8 stellig
            $export->setValue($export->getCell("14", $row), $BookingFrom);// Buchungsstapel von xxxx0101
            $export->setValue($export->getCell("15", $row), $BookingTo);// Buchungsstapel bis xxxx01xx
            $export->setValue($export->getCell("16", $row), "");    // darf leer sein (z.B. Rechnung vom März) Bezeichnung
            $export->setValue($export->getCell("17", $row), $Acronym);    // Diktatkürzel -> Initialen der am Account verknüpften Personen Vorname, Nachname (z.b. JK)
            $export->setValue($export->getCell("18", $row), "1");   // Buchungstyp 1 = Finanzbuchführung 2 = Jahresabschluss
            $export->setValue($export->getCell("19", $row), "0");   // Rechnungslegungszweck (0 oder leer)
            $export->setValue($export->getCell("20", $row), "0");   // Festschreibung 0 = keine Festschreibung 1 = Festschreibung
            $export->setValue($export->getCell("21", $row++), "EUR"); // Währungskennzeichen

            $export->setValue($export->getCell("0", $row), "Umsatz (ohne Soll-/Haben-Kennzeichen)");
            $export->setValue($export->getCell("1", $row), "Soll-/Haben-Kennzeichen");
            $export->setValue($export->getCell("2", $row), "WKZ Umsatz");
            $export->setValue($export->getCell("3", $row), "Kurs");
            $export->setValue($export->getCell("4", $row), "Basisumsatz");
            $export->setValue($export->getCell("5", $row), "WKZ Basisumsatz");
            $export->setValue($export->getCell("6", $row), "Konto");
            $export->setValue($export->getCell("7", $row), utf8_decode("Gegenkonto (ohne BU-Schlüssel)"));
            $export->setValue($export->getCell("8", $row), utf8_decode("BU-Schlüssel"));
            $export->setValue($export->getCell("9", $row), "Belegdatum");
            $export->setValue($export->getCell("10", $row), "Belegfeld 1");
            $export->setValue($export->getCell("11", $row), "Belegfeld 2");
            $export->setValue($export->getCell("12", $row), "Skonto");
            $export->setValue($export->getCell("13", $row), "Buchungstext");
            $export->setValue($export->getCell("14", $row), "Postensperre");
            $export->setValue($export->getCell("15", $row), "Diverse Adressnummer");
            $export->setValue($export->getCell("16", $row), utf8_decode("Geschäftspartnerbank"));
            $export->setValue($export->getCell("17", $row), "Sachverhalt");
            $export->setValue($export->getCell("18", $row), "Zinssperre");
            $export->setValue($export->getCell("19", $row), "Beleglink");
            $export->setValue($export->getCell("20", $row), "Beleginfo - Art 1");
            $export->setValue($export->getCell("21", $row), "Beleginfo - Inhalt 1");
            $export->setValue($export->getCell("22", $row), "Beleginfo - Art 2");
            $export->setValue($export->getCell("23", $row), "Beleginfo - Inhalt 2");
            $export->setValue($export->getCell("24", $row), "Beleginfo - Art 3");
            $export->setValue($export->getCell("25", $row), "Beleginfo - Inhalt 3");
            $export->setValue($export->getCell("26", $row), "Beleginfo - Art 4");
            $export->setValue($export->getCell("27", $row), "Beleginfo - Inhalt 4");
            $export->setValue($export->getCell("28", $row), "Beleginfo - Art 5");
            $export->setValue($export->getCell("29", $row), "Beleginfo - Inhalt 5");
            $export->setValue($export->getCell("30", $row), "Beleginfo - Art 6");
            $export->setValue($export->getCell("31", $row), "Beleginfo - Inhalt 6");
            $export->setValue($export->getCell("32", $row), "Beleginfo - Art 7");
            $export->setValue($export->getCell("33", $row), "Beleginfo - Inhalt 7");
            $export->setValue($export->getCell("34", $row), "Beleginfo - Art 8");
            $export->setValue($export->getCell("35", $row), "Beleginfo - Inhalt 8");
            $export->setValue($export->getCell("36", $row), "KOST1 - Kostenstelle");
            $export->setValue($export->getCell("37", $row), "KOST2 - Kostenstelle");
            $export->setValue($export->getCell("38", $row), "KOST-Menge");
            $export->setValue($export->getCell("39", $row), "EU-Mitgliedstaat u. USt-IdNr.");
            $export->setValue($export->getCell("40", $row), "EU-Steuersatz");
            $export->setValue($export->getCell("41", $row), "Abw. Versteuerungsart");
            $export->setValue($export->getCell("42", $row), "Sachverhalt L+L");
            $export->setValue($export->getCell("43", $row), utf8_decode("Funktionsergänzung L+L"));
            $export->setValue($export->getCell("44", $row), "BU 49 Hauptfunktionstyp");
            $export->setValue($export->getCell("45", $row), "BU 49 Hauptfunktionsnummer(");
            $export->setValue($export->getCell("46", $row), utf8_decode("BU 49 Funktionsergänzung"));
            $export->setValue($export->getCell("47", $row), "Zusatzinformation - Art 1");
            $export->setValue($export->getCell("48", $row), "Zusatzinformation - Inhalt 1");
            $export->setValue($export->getCell("49", $row), "Zusatzinformation - Art 2");
            $export->setValue($export->getCell("50", $row), "Zusatzinformation - Inhalt 2");
            $export->setValue($export->getCell("51", $row), "Zusatzinformation - Art 3");
            $export->setValue($export->getCell("52", $row), "Zusatzinformation - Inhalt 3");
            $export->setValue($export->getCell("53", $row), "Zusatzinformation - Art 4");
            $export->setValue($export->getCell("54", $row), "Zusatzinformation - Inhalt 4");
            $export->setValue($export->getCell("55", $row), "Zusatzinformation - Art 5");
            $export->setValue($export->getCell("56", $row), "Zusatzinformation - Inhalt 5");
            $export->setValue($export->getCell("57", $row), "Zusatzinformation - Art 6");
            $export->setValue($export->getCell("58", $row), "Zusatzinformation - Inhalt 6");
            $export->setValue($export->getCell("59", $row), "Zusatzinformation - Art 7");
            $export->setValue($export->getCell("60", $row), "Zusatzinformation - Inhalt 7");
            $export->setValue($export->getCell("61", $row), "Zusatzinformation - Art 8");
            $export->setValue($export->getCell("62", $row), "Zusatzinformation - Inhalt 8");
            $export->setValue($export->getCell("63", $row), "Zusatzinformation - Art 9");
            $export->setValue($export->getCell("64", $row), "Zusatzinformation - Inhalt 9");
            $export->setValue($export->getCell("65", $row), "Zusatzinformation - Art 10");
            $export->setValue($export->getCell("66", $row), "Zusatzinformation - Inhalt 10");
            $export->setValue($export->getCell("67", $row), "Zusatzinformation - Art 11");
            $export->setValue($export->getCell("68", $row), "Zusatzinformation - Inhalt 11");
            $export->setValue($export->getCell("69", $row), "Zusatzinformation - Art 12");
            $export->setValue($export->getCell("70", $row), "Zusatzinformation - Inhalt 12");
            $export->setValue($export->getCell("71", $row), "Zusatzinformation - Art 13");
            $export->setValue($export->getCell("72", $row), "Zusatzinformation - Inhalt 13");
            $export->setValue($export->getCell("73", $row), "Zusatzinformation - Art 14");
            $export->setValue($export->getCell("74", $row), "Zusatzinformation - Inhalt 14");
            $export->setValue($export->getCell("75", $row), "Zusatzinformation - Art 15");
            $export->setValue($export->getCell("76", $row), "Zusatzinformation - Inhalt 15");
            $export->setValue($export->getCell("77", $row), "Zusatzinformation - Art 16");
            $export->setValue($export->getCell("78", $row), "Zusatzinformation - Inhalt 16");
            $export->setValue($export->getCell("79", $row), "Zusatzinformation - Art 17");
            $export->setValue($export->getCell("80", $row), "Zusatzinformation - Inhalt 17");
            $export->setValue($export->getCell("81", $row), "Zusatzinformation - Art 18");
            $export->setValue($export->getCell("82", $row), "Zusatzinformation - Inhalt 18");
            $export->setValue($export->getCell("83", $row), "Zusatzinformation - Art 19");
            $export->setValue($export->getCell("84", $row), "Zusatzinformation - Inhalt 19");
            $export->setValue($export->getCell("85", $row), "Zusatzinformation - Art 20");
            $export->setValue($export->getCell("86", $row), "Zusatzinformation - Inhalt 20");
            $export->setValue($export->getCell("87", $row), utf8_decode("Stück"));
            $export->setValue($export->getCell("88", $row), "Gewicht");
            $export->setValue($export->getCell("89", $row), "Zahlweise");
            $export->setValue($export->getCell("90", $row), "Forderungsart");
            $export->setValue($export->getCell("91", $row), "Veranlagungsjahr");
            $export->setValue($export->getCell("92", $row), utf8_decode("Zugeordnete Fälligkeit"));
            $export->setValue($export->getCell("93", $row), "Skontotyp");
            $export->setValue($export->getCell("94", $row), "Auftragsnummer");
            $export->setValue($export->getCell("95", $row), "Buchungstyp");
            $export->setValue($export->getCell("96", $row), utf8_decode("USt-Schlüssel (Anzahlungen)"));
            $export->setValue($export->getCell("97", $row), "EU-Mitgliedstaat (Anzahlungen)");
            $export->setValue($export->getCell("98", $row), "Sachverhalt L+L (Anzahlungen)");
            $export->setValue($export->getCell("99", $row), "EU-Steuersatz (Anzahlungen)");
            $export->setValue($export->getCell("100", $row), utf8_decode("Erlöskonto (Anzahlungen)"));
            $export->setValue($export->getCell("101", $row), "Herkunft-Kz");
            $export->setValue($export->getCell("102", $row), "Leerfeld");
            $export->setValue($export->getCell("103", $row), "KOST-Datum");
            $export->setValue($export->getCell("104", $row), "SEPA-Mandatsreferenz");
            $export->setValue($export->getCell("105", $row), "Skontosperre");   // 0
            $export->setValue($export->getCell("106", $row), "Gesellschaftername");
            $export->setValue($export->getCell("107", $row), "Beteiligtennummer");
            $export->setValue($export->getCell("108", $row), "Identifikationsnummer");
            $export->setValue($export->getCell("109", $row), "Zeichnernummer");
            $export->setValue($export->getCell("110", $row), "Postensperre bis");
            $export->setValue($export->getCell("111", $row), "Bezeichnung SoBil-Sachverhalt");
            $export->setValue($export->getCell("112", $row), "Kennzeichen SoBil-Sachverhalt");
            $export->setValue($export->getCell("113", $row), "Festschreibung"); // 0
            $export->setValue($export->getCell("114", $row), "Leistungsdatum");
            $export->setValue($export->getCell("115", $row), "Datum Zuord. Steuerperiode");


            foreach ($tblInvoiceList as $tblInvoice) {
                if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice))){
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                        $FibuAccount = '';
                        $FibuToAccount = '';
                        $Kost1 = '';
                        $Kost2 = '';
                        $BuKey = '0';
                        $Summary = $tblInvoiceItemDebtor->getSummaryPriceInt();
                        $Summary = str_replace(',', '', $Summary);
                        $Summary = str_replace('.', ',', $Summary);
                        /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                        if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                            $bookingText = $this->getBookingText($tblInvoiceItemDebtor, $tblItem->getDatevRemark());
                            if($IsFibuDebtorNumber){
                                $FibuAccount = $tblInvoiceItemDebtor->getDebtorNumber();
                            } else {
                                $FibuAccount = $tblItem->getFibuAccount();
                            }

                            $FibuToAccount = $tblItem->getFibuToAccount();
                            $Kost1 = $tblItem->getKost1();
                            $Kost2 = $tblItem->getKost2();
                            $BuKey = $tblItem->getBuKey();
                        } else {
                            $bookingText = $tblInvoiceItemDebtor->getName();
                            // Was, mit fehlenden Fibu-Daten?
                        }
                        // Datev darf nur 60 Zeichen im Buchuntstext verarbeiten
                        $bookingText = substr($bookingText, 0, 60);

                        $row++;
                        $export->setValue($export->getCell("0", $row), $Summary);// Umsatz
                        $export->setValue($export->getCell("1", $row), $direction);// Soll / Haben Kennzeichen
                        $export->setValue($export->getCell("2", $row), '!EUR');// Dreistelliger ISO-Code der Währung
                        $export->setValue($export->getCell("3", $row), '');// Kurs (Test: utf8_decode($tblInvoice->getServiceTblPersonCauser()->getLastFirstName()))
                        $export->setValue($export->getCell("4", $row), '');// Basisumsatz
                        $export->setValue($export->getCell("5", $row), '');// WKZ Basisumsatz
                        $export->setValue($export->getCell("6", $row), $FibuAccount);// Fibu-Konto
                        $export->setValue($export->getCell("7", $row), $FibuToAccount);// Fibu-Gegenkonto (ohne BU-Schlüssel)
                        $export->setValue($export->getCell("8", $row), $BuKey);// BU-Schlüssel 3(Umsatzsteuer) oder 9
                        $export->setValue($export->getCell("9", $row), $tblInvoice->getBillTime('dm'));// Belegdatum Format? (3108)
                        $export->setValue($export->getCell("10", $row), $tblInvoice->getInvoiceNumber());// Belegfeld 1
                        $export->setValue($export->getCell("11", $row), '');// Belegfeld 2
                        $export->setValue($export->getCell("12", $row), '');// Skonto
                        $export->setValue($export->getCell("13", $row), utf8_decode($bookingText));// Buchungstext (60 Zeichen)
                        $export->setValue($export->getCell("14", $row), '0');// Postensperre (0/1)
                        $export->setValue($export->getCell("15", $row), '');// Diverse Adressnummer (9 Zeichen)
                        $export->setValue($export->getCell("16", $row), '');// Geschäftspartnerbank
                        $export->setValue($export->getCell("17", $row), '');// Sachverhalt
                        $export->setValue($export->getCell("18", $row), '');// Zinssperre
                        $export->setValue($export->getCell("19", $row), '');// Beleglink
                        $export->setValue($export->getCell("20", $row), '');// Beleginfo - Art 1
                        $export->setValue($export->getCell("21", $row), '');// Beleginfo - Inhalt 1
                        $export->setValue($export->getCell("22", $row), '');// Beleginfo - Art 2
                        $export->setValue($export->getCell("23", $row), '');// Beleginfo - Inhalt 2
                        $export->setValue($export->getCell("24", $row), '');// Beleginfo - Art 3
                        $export->setValue($export->getCell("25", $row), '');// Beleginfo - Inhalt 3
                        $export->setValue($export->getCell("26", $row), '');// Beleginfo - Art 4
                        $export->setValue($export->getCell("27", $row), '');// Beleginfo - Inhalt 4
                        $export->setValue($export->getCell("28", $row), '');// Beleginfo - Art 5
                        $export->setValue($export->getCell("29", $row), '');// Beleginfo - Inhalt 5
                        $export->setValue($export->getCell("30", $row), '');// Beleginfo - Art 6
                        $export->setValue($export->getCell("31", $row), '');// Beleginfo - Inhalt 6
                        $export->setValue($export->getCell("32", $row), '');// Beleginfo - Art 7
                        $export->setValue($export->getCell("33", $row), '');// Beleginfo - Inhalt 7
                        $export->setValue($export->getCell("34", $row), '');// Beleginfo - Art 8
                        $export->setValue($export->getCell("35", $row), '');// Beleginfo - Inhalt 8
                        $export->setValue($export->getCell("36", $row), $Kost1);// KOST1 - Kostenstelle
                        $export->setValue($export->getCell("37", $row), $Kost2);// KOST2 - Kostenstelle
                        $export->setValue($export->getCell("38", $row), '');// KOST-Menge
                        $export->setValue($export->getCell("39", $row), '');// EU-Mitgliedstaat u. USt-IdNr.
                        $export->setValue($export->getCell("40", $row), '');// EU-Steuersatz
                        $export->setValue($export->getCell("41", $row), '');// Abw. Versteuerungsart
                        $export->setValue($export->getCell("42", $row), '');// Sachverhalt L+L
                        $export->setValue($export->getCell("43", $row), '');// Funktionsergänzung L+L
                        $export->setValue($export->getCell("44", $row), '');// BU 49 Hauptfunktionstyp
                        $export->setValue($export->getCell("45", $row), '');// BU 49 Hauptfunktionsnummer
                        $export->setValue($export->getCell("46", $row), '');// BU 49 Funktionsergänzung
                        $export->setValue($export->getCell("47", $row), '');// Zusatzinformation - Art 1
                        $export->setValue($export->getCell("48", $row), '');// Zusatzinformation - Inhalt 1
                        $export->setValue($export->getCell("49", $row), '');// Zusatzinformation - Art 2
                        $export->setValue($export->getCell("50", $row), '');// Zusatzinformation - Inhalt 2
                        $export->setValue($export->getCell("51", $row), '');// Zusatzinformation - Art 3
                        $export->setValue($export->getCell("52", $row), '');// Zusatzinformation - Inhalt 3
                        $export->setValue($export->getCell("53", $row), '');// Zusatzinformation - Art 4
                        $export->setValue($export->getCell("54", $row), '');// Zusatzinformation - Inhalt 4
                        $export->setValue($export->getCell("55", $row), '');// Zusatzinformation - Art 5
                        $export->setValue($export->getCell("56", $row), '');// Zusatzinformation - Inhalt 5
                        $export->setValue($export->getCell("57", $row), '');// Zusatzinformation - Art 6
                        $export->setValue($export->getCell("58", $row), '');// Zusatzinformation - Inhalt 6
                        $export->setValue($export->getCell("59", $row), '');// Zusatzinformation - Art 7
                        $export->setValue($export->getCell("60", $row), '');// Zusatzinformation - Inhalt 7
                        $export->setValue($export->getCell("61", $row), '');// Zusatzinformation - Art 8
                        $export->setValue($export->getCell("62", $row), '');// Zusatzinformation - Inhalt 8
                        $export->setValue($export->getCell("63", $row), '');// Zusatzinformation - Art 9
                        $export->setValue($export->getCell("64", $row), '');// Zusatzinformation - Inhalt 9
                        $export->setValue($export->getCell("65", $row), '');// Zusatzinformation - Art 10
                        $export->setValue($export->getCell("66", $row), '');// Zusatzinformation - Inhalt 10
                        $export->setValue($export->getCell("67", $row), '');// Zusatzinformation - Art 11
                        $export->setValue($export->getCell("68", $row), '');// Zusatzinformation - Inhalt 11
                        $export->setValue($export->getCell("69", $row), '');// Zusatzinformation - Art 12
                        $export->setValue($export->getCell("70", $row), '');// Zusatzinformation - Inhalt 12
                        $export->setValue($export->getCell("71", $row), '');// Zusatzinformation - Art 13
                        $export->setValue($export->getCell("72", $row), '');// Zusatzinformation - Inhalt 13
                        $export->setValue($export->getCell("73", $row), '');// Zusatzinformation - Art 14
                        $export->setValue($export->getCell("74", $row), '');// Zusatzinformation - Inhalt 14
                        $export->setValue($export->getCell("75", $row), '');// Zusatzinformation - Art 15
                        $export->setValue($export->getCell("76", $row), '');// Zusatzinformation - Inhalt 15
                        $export->setValue($export->getCell("77", $row), '');// Zusatzinformation - Art 16
                        $export->setValue($export->getCell("78", $row), '');// Zusatzinformation - Inhalt 16
                        $export->setValue($export->getCell("79", $row), '');// Zusatzinformation - Art 17
                        $export->setValue($export->getCell("80", $row), '');// Zusatzinformation - Inhalt 17
                        $export->setValue($export->getCell("81", $row), '');// Zusatzinformation - Art 18
                        $export->setValue($export->getCell("82", $row), '');// Zusatzinformation - Inhalt 18
                        $export->setValue($export->getCell("83", $row), '');// Zusatzinformation - Art 19
                        $export->setValue($export->getCell("84", $row), '');// Zusatzinformation - Inhalt 19
                        $export->setValue($export->getCell("85", $row), '');// Zusatzinformation - Art 20
                        $export->setValue($export->getCell("86", $row), '');// Zusatzinformation - Inhalt 20
                        $export->setValue($export->getCell("87", $row), '');// Stück
                        $export->setValue($export->getCell("88", $row), '');// Gewicht
                        $export->setValue($export->getCell("89", $row), '');// Zahlweise
                        $export->setValue($export->getCell("90", $row), '');// Forderungsart
                        $export->setValue($export->getCell("91", $row), '');// Veranlagungsjahr
                        $export->setValue($export->getCell("92", $row), '');// Zugeordnete Fälligkeit (Datum)
                        $export->setValue($export->getCell("93", $row), '');// Skontotyp
                        $export->setValue($export->getCell("94", $row), '');// Auftragsnummer
                        $export->setValue($export->getCell("95", $row), '');// Buchungstyp
                        $export->setValue($export->getCell("96", $row), '');// USt-Schlüssel
                        $export->setValue($export->getCell("97", $row), '');// EU-Mitgliedsstaat
                        $export->setValue($export->getCell("98", $row), '');// Sachverhalt L+L
                        $export->setValue($export->getCell("99", $row), '');// EU-Steuersatz
                        $export->setValue($export->getCell("100", $row), '');// Erlöskonto
                        $export->setValue($export->getCell("101", $row), '');// Herkunf-Kz
                        $export->setValue($export->getCell("102", $row), '');// Leerfeld
                        $export->setValue($export->getCell("103", $row), '');// KOST-Datum
                        $export->setValue($export->getCell("104", $row), '');// SEPA-Mandatsreferenz
                        $export->setValue($export->getCell("105", $row), '0');// Skontosperre
                        $export->setValue($export->getCell("106", $row), '');// Gesellschaftlername
                        $export->setValue($export->getCell("107", $row), '');// Beteiligtennummer
                        $export->setValue($export->getCell("108", $row), '');// Identifikationsnummer
                        $export->setValue($export->getCell("109", $row), '');// Zeichnernummer
                        $export->setValue($export->getCell("110", $row), '');// Postensperre bis
                        $export->setValue($export->getCell("111", $row), '');// Bezeichnung SoBil-Sachverhalt
                        $export->setValue($export->getCell("112", $row), '');// Kennzeichen SoBil-Buchung
                        $export->setValue($export->getCell("113", $row), '0');// Festschreibung 0 = Keine Festschreibung 1 = Festschreibung
                        $export->setValue($export->getCell("114", $row), '');// Leistungsdatum
                        $export->setValue($export->getCell("115", $row), '');// Datum Zuord. Steuerperiode
                    }
                }
            }

            // Angabe der in Excel höchsten Spalte, die in der CSV abgebildet werden soll (erste Zeile / Header)
            $export->setHeadColumnLimitCsv('AE');
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            Basket::useService()->changeBasketDoneDatev($tblBasket);

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param array     $CheckboxList
     * @param array     $FeeList
     *
     * @return bool|CustomerDirectDebitFacade
     */
    public function createSepaContent(TblBasket $tblBasket, $CheckboxList = array(), $FeeList = array())
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByBasket($tblBasket);
        if(!$tblInvoiceList){
            return false;
        }

        $SepaPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
        $InvoiceCount = 0;

        if($tblInvoiceList){
            $currentTblInvoice = current($tblInvoiceList);
            $tblInvoiceCreditor = $currentTblInvoice->getTblInvoiceCreditor();
            //Set the custom header (Spanish banks example) information
            $header = new GroupHeader($tblBasket->getId().' '.date('Y-m-d-H-i-s'), $tblInvoiceCreditor->getOwner());
            $header->setInitiatingPartyId($tblInvoiceCreditor->getIBAN());

            $directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');

            $combinedItemDebtorList = array();

            // Bearbeitung der in der Abrechnung liegenden Posten
            foreach($tblInvoiceList as $tblInvoice){
                $PaymentId = $tblInvoice->getInvoiceNumber().'-';
                $countSepaPayment = 0;

                $tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice);
                // Dient nur der SEPA-Prüfung
                if($tblInvoiceItemDebtorList){
                    // Nur Sepa-Zahlungen, die nicht in offene Posten sind
                    foreach($tblInvoiceItemDebtorList as &$tblInvoiceItemDebtorCheck){
                        if(($tblPaymentType = $tblInvoiceItemDebtorCheck->getServiceTblPaymentType())){
                            if($tblPaymentType->getId() == $SepaPaymentType){
                                if($tblInvoiceItemDebtorCheck->getIsPaid()){
                                    $countSepaPayment++;
                                } else {
                                    // Offene posten ignorieren
                                    $tblInvoiceItemDebtorCheck = false;
                                }
                            } else {
                                // Zahlung mit anderem Zahlungstyp als SEPA-Lastschrift wird ignoriert
                                $tblInvoiceItemDebtorCheck = false;
                            }
                        } else {
                            // Zahlung ohne Zahlungstyp wird ignoriert
                            $tblInvoiceItemDebtorCheck = false;
                        }
                    }
                }
                if($countSepaPayment == 0){
                    // überspringt rechnungen ohne Sepa-Lastschrift
                    continue;
                }
                // entfernen der false Werte
                $tblInvoiceItemDebtorList = array_filter($tblInvoiceItemDebtorList);
                $InvoiceCount++;
                $this->addPaymentInfo($directDebit, $tblInvoice, $PaymentId, $tblInvoiceCreditor);

                if(!empty($tblInvoiceItemDebtorList)){
                    $item = array();
                    /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
                    foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
                        $Ref = $tblInvoiceItemDebtor->getBankReference();

                        // Offene posten ignorieren
                        if(!$tblInvoiceItemDebtor->getIsPaid()){
                            continue;
                        }

                        $item[$Ref]['PaymentId'] = $PaymentId;
                        if(!isset($item[$Ref]['ReferenceDate'])){
                            $item[$Ref]['ReferenceDate'] = '';
                            if(($tblBankReference = $tblInvoiceItemDebtor->getServiceTblBankReference())){
                                $item[$Ref]['ReferenceDate'] = $tblBankReference->getReferenceDate();
                            }
                            if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                                $item[$Ref]['BookingText'] = $this->getBookingText($tblInvoiceItemDebtor, $tblItem->getSepaRemark());
                            } else {
                                $item[$Ref]['BookingText'] = $tblInvoiceItemDebtor->getName();
                            }
                            $item[$Ref]['Price'] = $tblInvoiceItemDebtor->getSummaryPriceInt();
                            $item[$Ref]['IBAN'] = $tblInvoiceItemDebtor->getIBAN();
                            $item[$Ref]['BIC'] = $tblInvoiceItemDebtor->getBIC();
                            $item[$Ref]['Owner'] = $tblInvoiceItemDebtor->getOwner();
                            $item[$Ref]['BankReference'] = $Ref;
                            $item[$Ref]['ItemName'] = $tblInvoiceItemDebtor->getName();
                            $item[$Ref]['ItemNameAndPrice'] = $tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');

                        } else {
                            if(isset($item[$Ref]['ItemName'])){
                                $item[$Ref]['ItemName'] .= ', '.$tblInvoiceItemDebtor->getName();
                            } else {
                                $item[$Ref]['ItemName'] = $tblInvoiceItemDebtor->getName();
                            }
                            if(isset($item[$Ref]['ItemNameAndPrice'])){
                                $item[$Ref]['ItemNameAndPrice'] .= ', '.$tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');
                            } else {
                                $item[$Ref]['ItemNameAndPrice'] = $tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');
                            }

                            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_REMARK))){
                                // allgemeinen Buchungstext verwenden
                                $item[$Ref]['BookingText'] = $this->getBookingText($tblInvoiceItemDebtor, $tblSetting->getValue(), $item[$Ref]['ItemName'], $item[$Ref]['ItemNameAndPrice']);
                            }
                            if(isset($item[$Ref]['ItemName'])){
                                $item[$Ref]['Price'] = $item[$Ref]['Price'] + $tblInvoiceItemDebtor->getSummaryPriceInt();
                            } else {
                                $item[$Ref]['Price'] = $tblInvoiceItemDebtor->getSummaryPriceInt();
                            }
                        }
                    }
                    array_push($combinedItemDebtorList, $item);
//                    $this->addTransfer($directDebit, $tblInvoiceItemDebtorList, $PaymentId);
                }
            }
            // can't create without List
            if(empty($directDebit) && empty($combinedItemDebtorList)){
                return false;
            }
            $this->addCombinedTransfer($directDebit, $combinedItemDebtorList);

            $listPaidStatus = array();
            // Bearbeitung der Offenen Posten
            if(!empty($CheckboxList)){
                $CombOpenList = array();
                foreach($CheckboxList as $tblInvoiceItemDebtorId){
                    $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($tblInvoiceItemDebtorId);
                    if($tblInvoiceItemDebtor){
                        $listPaidStatus[] = $tblInvoiceItemDebtor;
                        $tblInvoice = $tblInvoiceItemDebtor->getTblInvoice();
                        $PaymentId = $tblInvoice->getInvoiceNumber().'-';
                        $Ref = $tblInvoiceItemDebtor->getBankReference();

                        // nur Offene posten erlauben
                        if($tblInvoiceItemDebtor->getIsPaid()){
                            continue;
                        }

                        // Sum Price and Fee
                        $Price = $tblInvoiceItemDebtor->getSummaryPriceInt();
                        if(isset($FeeList[$tblInvoiceItemDebtorId])){
                            $Fee = $FeeList[$tblInvoiceItemDebtorId];
                            $Fee = round(str_replace(',', '.', $Fee), 2);
                            $Price = (float)$tblInvoiceItemDebtor->getSummaryPriceInt() + $Fee;
                        }

                        if(!isset($CombOpenList[$Ref]['PaymentId'])){
                            $CombOpenList[$Ref]['PaymentId'] = $PaymentId;
                            $CombOpenList[$Ref]['ReferenceDate'] = '';
//                            if(($tblBankReference = $tblInvoiceItemDebtor->getServiceTblBankReference())){
//                                $CombOpenList[$Ref]['ReferenceDate'] = $tblBankReference->getReferenceDate();
//                            }
                            if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                                $CombOpenList[$Ref]['BookingText'] = $this->getBookingText($tblInvoiceItemDebtor, $tblItem->getSepaRemark());
                            } else {
                                $CombOpenList[$Ref]['BookingText'] = $tblInvoiceItemDebtor->getName();
                            }
                            $CombOpenList[$Ref]['Price'] = $Price;
                            $CombOpenList[$Ref]['IBAN'] = $tblInvoiceItemDebtor->getIBAN();
                            $CombOpenList[$Ref]['BIC'] = $tblInvoiceItemDebtor->getBIC();
                            $CombOpenList[$Ref]['Owner'] = $tblInvoiceItemDebtor->getOwner();
                            $CombOpenList[$Ref]['BankReference'] = $Ref;
                            $CombOpenList[$Ref]['ItemName'] = $tblInvoiceItemDebtor->getName();
                            $CombOpenList[$Ref]['ItemNameAndPrice'] = $tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');
                            $CombOpenList[$Ref]['tblInvoice'] = $tblInvoice;
                        } else {
                            if(isset($CombOpenList[$Ref]['ItemName'])){
                                $CombOpenList[$Ref]['ItemName'] .= ', '.$tblInvoiceItemDebtor->getName();
                            } else {
                                $CombOpenList[$Ref]['ItemName'] = $tblInvoiceItemDebtor->getName();
                            }
                            if(isset($CombOpenList[$Ref]['ItemNameAndPrice'])){
                                $CombOpenList[$Ref]['ItemNameAndPrice'] .= ', '.$tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');
                            } else {
                                $CombOpenList[$Ref]['ItemNameAndPrice'] = $tblInvoiceItemDebtor->getName(). ' '.$tblInvoiceItemDebtor->getPriceString('EUR');
                            }

                            if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_REMARK))){
                                // allgemeinen Buchungstext verwenden
                                $CombOpenList[$Ref]['BookingText'] = $this->getBookingText($tblInvoiceItemDebtor, $tblSetting->getValue(), $CombOpenList[$Ref]['ItemName'], $CombOpenList[$Ref]['ItemNameAndPrice']);
                            }
                            if(isset($CombOpenList[$Ref]['ItemName'])){
                                $CombOpenList[$Ref]['Price'] = (float)$CombOpenList[$Ref]['Price'] + $Price;
                            } else {
                                $CombOpenList[$Ref]['Price'] = $tblInvoiceItemDebtor->getSummaryPriceInt();
                            }
                        }

//                        $this->addTransfer($directDebit, array($tblInvoiceItemDebtor), $PaymentId, true, $Fee);
                    }
                }

                foreach($CombOpenList as $Ref){
                    $this->addPaymentInfo($directDebit, $Ref['tblInvoice'], $Ref['PaymentId'], $tblInvoiceCreditor);
                }
                $this->addCombinedTransfer($directDebit, array($CombOpenList));
            }
        }


        if($InvoiceCount == 0 && !isset($directDebit)){
            return false;
        }
        Basket::useService()->changeBasketDoneSepa($tblBasket);
        // offene Posten als Bezahlt markieren
        if(!empty($listPaidStatus)){
            foreach($listPaidStatus as $tblInvoiceItemDebtorPaid){
                Invoice::useService()->changeInvoiceItemDebtorIsPaid($tblInvoiceItemDebtorPaid, true);
            }
        }

        return $directDebit;
    }

    /**
     * @param TblInvoiceItemDebtor $tblInvoiceItemDebtor
     * @param string               $bookingText
     * @param string               $ItemCombinedName
     * @param string               $ItemCombinedNameAndPrice
     *
     * @return mixed|string|string[]|null
     */
    private function getBookingText(TblInvoiceItemDebtor $tblInvoiceItemDebtor, $bookingText = '', $ItemCombinedName = '', $ItemCombinedNameAndPrice = '')
    {

        $ItemName = $ItemCombinedName;
        $ItemNameAndPrice = $ItemCombinedNameAndPrice;
        if($ItemName == ''){
            if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                $Price = $tblInvoiceItemDebtor->getPriceString('EUR');
                $ItemName = $tblItem->getName();
                $ItemNameAndPrice = $tblItem->getName(). ' '.$Price;
            }
        }

        if($bookingText){
            $tblInvoice = $tblInvoiceItemDebtor->getTblInvoice();
            $CreditorId = '';
            $RefNumber = $tblInvoiceItemDebtor->getBankReference();
            $DebtorNumber = $tblInvoiceItemDebtor->getDebtorNumber();
            $CauserName = $tblInvoice->getLastName();
            $CauserFirstName = $tblInvoice->getFirstName();
            $TimeString = $tblInvoice->getYear().'.'.$tblInvoice->getMonth(true);
            if(($tblInvoiceCreditor = $tblInvoice->getTblInvoiceCreditor())){
                $CreditorId = $tblInvoiceCreditor->getCreditorId();
            }

            $bookingText = str_ireplace('[GID]', $CreditorId, $bookingText);
            $bookingText = str_ireplace('[SN]', $RefNumber, $bookingText);
            $bookingText = str_ireplace('[BVN]', $CauserName, $bookingText);
            $bookingText = str_ireplace('[BVV]', $CauserFirstName, $bookingText);
            $bookingText = str_ireplace('[BA]', $ItemName, $bookingText);
            $bookingText = str_ireplace('[BAEP]', $ItemNameAndPrice, $bookingText);
            $bookingText = str_ireplace('[DEB]', $DebtorNumber, $bookingText);
            $bookingText = str_ireplace('[BAM]', $TimeString, $bookingText);
            return $bookingText;
        }

        return $bookingText;
    }

    /**
     * @param CustomerDirectDebitFacade $directDebit
     * @param TblInvoice                $tblInvoice
     * @param                           $PaymentId
     * @param TblInvoiceCreditor        $tblInvoiceCreditor
     */
    private function addPaymentInfo(CustomerDirectDebitFacade $directDebit, TblInvoice $tblInvoice, $PaymentId, TblInvoiceCreditor $tblInvoiceCreditor)
    {

        $directDebit->addPaymentInfo($PaymentId, array(
            'id'                    => $PaymentId,
            'dueDate'               => new \DateTime($tblInvoice->getTargetTime()), // optional. Otherwise default period is used
            'creditorName'          => $tblInvoiceCreditor->getOwner(),
            'creditorAccountIBAN'   => $tblInvoiceCreditor->getIBAN(),
            'creditorAgentBIC'      => $tblInvoiceCreditor->getBIC(),
            'seqType'               => PaymentInformation::S_RECURRING,
            // Element dient der Angabe, um was für eine SEPA Lastschrift es sich handelt:
            //» SEPA OOFF = einmalige SEPA Lastschrift
            //» SEPA RCUR = fortfolgende SEPA Lastschrift -> für uns default
            // die anderen werden nicht mehr benutzt (FRST, FNAL)
            'creditorId'            => ($tblInvoiceCreditor->getCreditorId() ? $tblInvoiceCreditor->getCreditorId() : 'ERROR'), // 18 Stellen lang und beginnt immer mit "DE"
            'localInstrumentCode'   => 'CORE' // default. optional.
            // Element dient der Unterscheidung zwischen den einzelenen SEPA Lastschriften:
            //» SEPA CORE Lastschrift (aktueller Standard -> COR1 ist deprecated)
        ));
    }

    // Erstmal deaktiviert
//    /**
//     * @param CustomerDirectDebitFacade $directDebit
//     * @param array                     $tblInvoiceItemDebtorList
//     * @param string                    $PaymentId
//     * @param bool                      $doPaidInvoice
//     * @param int                       $Fee
//     */
//    private function addTransfer(CustomerDirectDebitFacade $directDebit, $tblInvoiceItemDebtorList, $PaymentId,
//        $doPaidInvoice = false, $Fee = 0)
//    {
//
//        /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
//        foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor){
//            if(!$doPaidInvoice){
//                // Offene posten ignorieren
//                if(!$tblInvoiceItemDebtor->getIsPaid()){
//                    continue;
//                }
//            }
//
//            $ReferenceDate = '';
//            if(($tblBankReference = $tblInvoiceItemDebtor->getServiceTblBankReference())){
//                $ReferenceDate = $tblBankReference->getReferenceDate();
//            }
//
//            if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
//                $bookingText = $this->getBookingText($tblInvoiceItemDebtor, $tblItem->getSepaRemark());
//            } else {
//                $bookingText = $tblInvoiceItemDebtor->getName();
//            }
//
//            $Price = $tblInvoiceItemDebtor->getSummaryPriceInt();
//            if($doPaidInvoice){
//                $Fee = str_replace(',', '.', $Fee);
//                $Fee = round($Fee, 2);
//                $Price = (float)$tblInvoiceItemDebtor->getSummaryPriceInt() + $Fee;
//            }
//
//            // create a payment, it's possible to create multiple payments,
//            // "firstPayment" is the identifier for the transactions
//            // Add a Single Transaction to the named payment
//            if($tblInvoiceItemDebtor->getBIC()){
//                $directDebit->addTransfer($PaymentId, array(
//                    'amount'                => $Price,
//                    'debtorIban'            => $tblInvoiceItemDebtor->getIBAN(),
//                    'debtorBic'             => $tblInvoiceItemDebtor->getBIC(), // mit BIC
//                    'debtorName'            => $tblInvoiceItemDebtor->getOwner(), // Vor / Zuname
//                    'debtorMandate'         => $tblInvoiceItemDebtor->getBankReference(),
//                    'debtorMandateSignDate' => $ReferenceDate,
//                    'remittanceInformation' => $bookingText,
//                    //            'endToEndId'            => 'Invoice-No X' // optional, if you want to provide additional structured info
//                ));
//            } else {
//                $directDebit->addTransfer($PaymentId, array(
//                    'amount'                => $Price,
//                    'debtorIban'            => $tblInvoiceItemDebtor->getIBAN(),
//                    'debtorName'            => $tblInvoiceItemDebtor->getOwner(), // Vor / Zuname
//                    'debtorMandate'         => $tblInvoiceItemDebtor->getBankReference(),
//                    'debtorMandateSignDate' => $ReferenceDate,
//                    'remittanceInformation' => $bookingText,
//                    //            'endToEndId'            => 'Invoice-No X' // optional, if you want to provide additional structured info
//                ));
//            }
//        }
//    }

    /**
     * @param CustomerDirectDebitFacade $directDebit
     * @param array                     $combinedItemDebtorList
     */
    private function addCombinedTransfer(CustomerDirectDebitFacade $directDebit, $combinedItemDebtorList = array())
    {

        /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
        foreach($combinedItemDebtorList as $ReferenceGroup){
            foreach($ReferenceGroup as $Content){
                $ReferenceDate = $Content['ReferenceDate'];
                $bookingText = $Content['BookingText'];
                $Price = $Content['Price'];
                $IBAN = $Content['IBAN'];
                $BIC = $Content['BIC'];
                $Owner = $Content['Owner'];
                $BankReference = $Content['BankReference'];

                $PaymentId = $Content['PaymentId'];

                // create a payment, it's possible to create multiple payments,
                // "firstPayment" is the identifier for the transactions
                // Add a Single Transaction to the named payment
                if($BIC){
                    $directDebit->addTransfer($PaymentId, array(
                        'amount'                => $Price,
                        'debtorIban'            => $IBAN,
                        'debtorBic'             => $BIC, // mit BIC
                        'debtorName'            => $Owner,
                        'debtorMandate'         => $BankReference,
                        'debtorMandateSignDate' => $ReferenceDate,
                        'remittanceInformation' => $bookingText,
                    ));
                } else {
                    $directDebit->addTransfer($PaymentId, array(
                        'amount'                => $Price,
                        'debtorIban'            => $IBAN,
                        'debtorName'            => $Owner,
                        'debtorMandate'         => $BankReference,
                        'debtorMandateSignDate' => $ReferenceDate,
                        'remittanceInformation' => $bookingText,
                    ));
                }
            }
        }
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|CustomerCreditFacade
     */
    public function createSepaCreditContent(TblBasket $tblBasket)
    {

        $tblInvoiceList = Invoice::useService()->getInvoiceByBasket($tblBasket);
        if(!$tblInvoiceList){
            return false;
        }

        $InvoiceCount = 0;

        if($tblInvoiceList){
            $currentTblInvoice = current($tblInvoiceList);
            $tblInvoiceCreditor = $currentTblInvoice->getTblInvoiceCreditor();

            //Set the initial information
            $customerCredit = TransferFileFacadeFactory::createCustomerCredit($tblBasket->getId().' '.date('Y-m-d-H-i-s'), $tblInvoiceCreditor->getOwner());

            // Bearbeitung der in der Abrechnung liegenden Posten
            foreach($tblInvoiceList as $tblInvoice){
                $PaymentId = $tblInvoice->getInvoiceNumber().'-';
                $countSepaPayment = 0;

                $tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByInvoice($tblInvoice);
                // Dient nur der SEPA-Prüfung
                // Gutschriften sollen anhand vorhandener Kontodaten gewählt werden
                $usedTblInvoiceItemDebtorList = array();
                if($tblInvoiceItemDebtorList){
                    foreach ($tblInvoiceItemDebtorList as &$tblInvoiceItemDebtorCheck) {
                        if ($tblInvoiceItemDebtorCheck->getServiceTblBankAccount()){
                            $usedTblInvoiceItemDebtorList[] = $tblInvoiceItemDebtorCheck;
                            $countSepaPayment++;
                        }
                    }
                }
                if(count($usedTblInvoiceItemDebtorList) == 0){
                    // überspringt rechnungen ohne Kontoinformation
                    continue;
                }
//                // entfernen der false Werte
//                $tblInvoiceItemDebtorList = array_filter($tblInvoiceItemDebtorList);
                $InvoiceCount++;
                if(!empty($usedTblInvoiceItemDebtorList)){
                    $this->addCompanyPayment($customerCredit, $tblInvoice, $PaymentId, $tblInvoiceCreditor);
                    $this->addCompanyTransfer($customerCredit, $usedTblInvoiceItemDebtorList, $PaymentId);
                }
            }
        }

        if($InvoiceCount == 0 || !isset($customerCredit)){
            return false;
        }

        Basket::useService()->changeBasketDoneSepa($tblBasket);

        return $customerCredit;
    }

    private function addCompanyPayment(CustomerCreditFacade $customerCredit, TblInvoice $tblInvoice, $PaymentId, TblInvoiceCreditor $tblInvoiceCreditor)
    {

        // create a payment, it's possible to create multiple payments,
        // "firstPayment" is the identifier for the transactions
        $customerCredit->addPaymentInfo($PaymentId.'-', array(
            'id'                      => $tblInvoice->getInvoiceNumber(),
            'debtorName'              => $tblInvoiceCreditor->getOwner(),
            'debtorAccountIBAN'       => $tblInvoiceCreditor->getIBAN(),
            'debtorAgentBIC'          => $tblInvoiceCreditor->getBIC(),
        ));
    }

    private function addCompanyTransfer(CustomerCreditFacade $customerCredit, $tblInvoiceItemDebtorList, $PaymentId)
    {

        /** @var TblInvoiceItemDebtor $tblInvoiceItemDebtor */
        foreach($tblInvoiceItemDebtorList as $tblInvoiceItemDebtor) {

            if(($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())){
                $bookingText = $this->getBookingText($tblInvoiceItemDebtor, $tblItem->getSepaRemark());
            } else {
                $bookingText = $tblInvoiceItemDebtor->getName();
            }

            // Offene posten ignorieren
            if (!$tblInvoiceItemDebtor->getIsPaid()){
                continue;
            }
            // Add a Single Transaction to the named payment
            $customerCredit->addTransfer($PaymentId.'-', array(
                'amount'                => $tblInvoiceItemDebtor->getSummaryPriceInt(),
                'creditorIban'          => $tblInvoiceItemDebtor->getIBAN(),
                'creditorBic'           => $tblInvoiceItemDebtor->getBIC(),
                'creditorName'          => $tblInvoiceItemDebtor->getOwner(),
                'remittanceInformation' => $bookingText
            ));
        }

    }
}