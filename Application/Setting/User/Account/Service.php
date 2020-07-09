<?php
namespace SPHERE\Application\Setting\User\Account;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\User\Account\Service\Data;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Application\Setting\User\Account\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Filter\Link\Pile;

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
     * @param $Id
     *
     * @return bool|TblUserAccount
     */
    public function getUserAccountById($Id)
    {

        return ( new Data($this->getBinding()) )->getUserAccountById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getUserAccountByPerson($tblPerson);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getUserAccountByAccount($tblAccount);
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return ( new Data($this->getBinding()) )->getUserAccountAll();
    }

    /**
     * @param string $Type
     *
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAllByType($Type)
    {

        return (new Data($this->getBinding()))->getUserAccountAllByType($Type);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByTime(DateTime $dateTime)
    {

        return (new Data($this->getBinding()))->getUserAccountByTime($dateTime);
    }

    /**
     * @param DateTime $groupByTime
     * @param DateTime $exportDate
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByLastExport(DateTime $groupByTime, DateTime $exportDate)
    {

        return (new Data($this->getBinding()))->getUserAccountByLastExport($groupByTime, $exportDate);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return false|array(TblUserAccount[])
     */
    public function getUserAccountByTimeGroupLimitList(DateTime $dateTime)
    {

        $tblUserAccountList = (new Data($this->getBinding()))->getUserAccountByTime($dateTime);
        $UserAccountArray = false;
        if($tblUserAccountList){
            foreach($tblUserAccountList as $tblUserAccount){
                $UserAccountArray[$tblUserAccount->getGroupByCount()][] = $tblUserAccount;
            }
        }
        return $UserAccountArray;

    }

    /**
     * @param DateTime $dateTime
     * @param int       $groupCount
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByTimeAndCount(DateTime $dateTime, $groupCount)
    {

        return (new Data($this->getBinding()))->getUserAccountByTimeAndCount($dateTime, $groupCount);
    }

    /**
     * @param false|array $tblUserAccountAll
     *
     * @return array|bool result[GroupByTime][]
     * result[GroupByTime][]
     */
    public function getGroupOfUserAccountList($tblUserAccountAll)
    {

        $result = array();
        if ($tblUserAccountAll && !empty($tblUserAccountAll)) {
            foreach ($tblUserAccountAll as $tblUserAccount) {
                $result[$tblUserAccount->getGroupByTime()][] = $tblUserAccount;
            }
        }
        return !empty($result) ? $result : false;
    }

    /**
     * @param IFormInterface $Form
     * @param TblUserAccount $tblUserAccount
     * @param array|null     $Data
     * @param string         $Path
     *
     * @return string|Form
     */
    public function generatePdfControl(IFormInterface $Form, TblUserAccount $tblUserAccount, $Data = null, $Path = '/Setting/User')
    {

        if($Data === null){
            return $Form;
        }

        $changePath = '/Setting/User/Account/Password/Generation';

        $SchoolPanel = new Panel('Schule', array(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Name', 4),
                    new LayoutColumn($Data['CompanyName'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Namenszusatz', 4),
                    new LayoutColumn($Data['CompanyExtendedName'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Ortsteil', 4),
                    new LayoutColumn($Data['CompanyDistrict'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Straße', 4),
                    new LayoutColumn($Data['CompanyStreet'], 8)
                )),
            ))),
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('Ort', 4),
                    new LayoutColumn($Data['CompanyCity'], 8)
                ))
            ))),
        ));

        $ContactPersonPanel = new Panel('Kontakt', array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Telefon: ', 4),
                new LayoutColumn($Data['Phone'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Fax: ', 4),
                new LayoutColumn($Data['Fax'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('E-Mail: ', 4),
                new LayoutColumn($Data['Mail'], 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Internet: ', 4),
                new LayoutColumn($Data['Web'], 8),
            )))),
        ));
        $SignerPanel = new Panel('Ort/Datum', array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Ort: ', 4),
                new LayoutColumn(($Data['Place'] ? $Data['Place'] : ''), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Datum: ', 4),
                new LayoutColumn(($Data['Date'] ? $Data['Date'] : (new DateTime())->format('d.m.Y')), 8),
            )))),
        ));

        return
        new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $SchoolPanel
                    , 4),
                    new LayoutColumn(
                        $ContactPersonPanel
                    , 4),
                    new LayoutColumn(
                        $SignerPanel
                    , 4),
                )),
                new LayoutRow(
                    new LayoutColumn(array(
                        (new External('Passwort generieren & herunterladen', '\Api\Document\Standard\PasswordChange\Create'
                            , null, array('Data' => $Data), false, External::STYLE_BUTTON_PRIMARY))
                            ->setRedirect($Path, Redirect::TIMEOUT_SUCCESS),
                        new Standard('Nein', $changePath, new Disable(), array('Id' => $tblUserAccount->getId(), 'Path' => $Path))
                        )
                    )
                )
            ))
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsParent
     *
     * @return mixed
     */
    public function getCompanySchoolByPerson(TblPerson $tblPerson, $IsParent = false)
    {

        $tblCompany = false;
        if($IsParent){
            $tblRelationshipType = Relationship::useService()->getTypeByName( TblType::IDENTIFIER_GUARDIAN );
            if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))){
                foreach($tblRelationshipList as $tblRelationship){  //ToDO Mehrer Schüler auswahl nach "höherer Bildungsgang"
                    if(($tblPersonStudent = $tblRelationship->getServiceTblPersonTo())){
                        if(($tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPersonStudent))){
                            if(($tblSchoolType = Type::useService()->getTypeByName($tblDivision->getTypeName()))){
                                if(($tblSchoolCompany = School::useService()->getSchoolByType($tblSchoolType))){
                                    $tblSchoolCompany = current($tblSchoolCompany);
                                    $tblCompany = $tblSchoolCompany->getServiceTblCompany();
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if(($tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPerson))){
                if(($tblSchoolType = Type::useService()->getTypeByName($tblDivision->getTypeName()))){
                    if(($tblSchoolCompany = School::useService()->getSchoolByType($tblSchoolType))){
                        $tblSchoolCompany = current($tblSchoolCompany);
                        $tblCompany = $tblSchoolCompany->getServiceTblCompany();
                    }
                }
            }
        }
        return $tblCompany;
    }

    /**
     * @param string $Type
     *
     * @return bool|TblUserAccount[]
     */
    public function countUserAccountAllByType($Type)
    {

        return (new Data($this->getBinding()))->countUserAccountAllByType($Type);
    }

    /**
     * @param null $FilterGroup
     * @param null $FilterStudent
     * @param null $FilterYear
     * @param bool $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getStudentFilterResultList(
        $FilterGroup = null,
        $FilterStudent = null,
//        $FilterPerson = null,
        $FilterYear = null,
        &$IsTimeout = false
    ) {

        // use every time Group "STUDENT"
        $FilterGroup['TblGroup_Id'] = Group::useService()->getGroupByMetaTable('STUDENT')->getId();

        // Database Join with foreign Key
        $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
        $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        if ($FilterGroup) {
            // Preparation FilterGroup
            array_walk($FilterGroup, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterGroup = array_filter($FilterGroup);
        } else {
            $FilterGroup = array();
        }
        // Preparation FilterPerson
//        if ($FilterPerson) {
//            // Preparation FilterPerson
//            array_walk($FilterPerson, function (&$Input) {
//
//                if (!is_array($Input)) {
//                    if (!empty($Input)) {
//                        $Input = explode(' ', $Input);
//                        $Input = array_filter($Input);
//                    } else {
//                        $Input = false;
//                    }
//                }
//            });
//            $FilterPerson = array_filter($FilterPerson);
//        } else {
        $FilterPerson = array();
//        }

        // Preparation $FilterStudent
        if (isset($FilterStudent)) {
            array_walk($FilterStudent, function (&$Input) {
                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterStudent = array_filter($FilterStudent);
        } else {
            $FilterStudent = array();
        }
        // Preparation $FilterYear
        if (isset($FilterYear)) {
            array_walk($FilterYear, function (&$Input) {
                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterYear = array_filter($FilterYear);
        } else {
            $FilterYear = array();
        }

        $Result = $Pile->searchPile(array(
            0 => $FilterGroup,
            1 => $FilterPerson,
            2 => $FilterStudent,
            3 => $FilterYear
        ));
        // get Timeout status
        $IsTimeout = $Pile->isTimeout();

        return ( !empty($Result) ? $Result : false );
    }

    /**
     * @param null $FilterGroup
     * @param null $FilterPerson
     * @param bool $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getPersonFilterResultList(
        $FilterGroup = null,
        $FilterPerson = null,
        &$IsTimeout = false
    ) {

        // Database Join with foreign Key
        $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );

        if ($FilterGroup) {
            // Preparation FilterGroup
            array_walk($FilterGroup, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterGroup = array_filter($FilterGroup);
        } else {
            $FilterGroup = array();
        }
        // Preparation FilterPerson
        if ($FilterPerson) {
            array_walk($FilterPerson, function (&$Input) {

                if (!is_array($Input)) {
                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                }
            });
            $FilterPerson = array_filter($FilterPerson);
        } else {
            $FilterPerson = array();
        }

        $Result = $Pile->searchPile(array(
            0 => $FilterGroup,
            1 => $FilterPerson
        ));
        // get Timeout status
        $IsTimeout = $Pile->isTimeout();

        return (!empty($Result) ? $Result : false);
    }

    /**
     * @param TblUserAccount[] $tblUserAccountList
     *
     * @return bool|DateTime
     */
    public function getLastExport($tblUserAccountList)
    {
        $BiggestDate = false;
        if(is_array($tblUserAccountList)){
            /** @var TblUserAccount $tblUserAccount */
            foreach($tblUserAccountList as $tblUserAccount){
                if(($Date = $tblUserAccount->getExportDate())) {
                    if(!$BiggestDate || $BiggestDate <= $Date){
                        $BiggestDate = $Date;
                    }
                }
            }
        }
        return $BiggestDate;
    }

    /**
     * @param array $tblUserAccountList
     *
     * @return array
     */
    public function getExcelData($tblUserAccountList = array())
    {

        $result = array();
        if (!empty($tblUserAccountList)) {

            // set flag IsExport
            $this->updateDownloadBulk($tblUserAccountList);

            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$result) {
                $tblPerson = $tblUserAccount->getServiceTblPerson();
                $tblAccount = $tblUserAccount->getServiceTblAccount();

                $item['Salutation'] = '';
                $item['Title'] = '';
                $item['FirstName'] = '';
                $item['SecondName'] = '';
                $item['LastName'] = '';
                $item['Gender'] = '';
                $item['AccountName'] = '';
                $item['Password'] = $tblUserAccount->getUserPassword();
                $item['StreetName'] = '';
                $item['StreetNumber'] = '';
                $item['CityCode'] = '';
                $item['CityName'] = '';
                $item['District'] = '';
                $item['State'] = '';
                $item['Nation'] = '';
                $item['Country'] = '';

                if($tblPerson){
                    $item['Salutation'] = $tblPerson->getSalutation();
                    $item['Title'] = $tblPerson->getTitle();
                    $item['FirstName'] = $tblPerson->getFirstName();
                    $item['SecondName'] = $tblPerson->getSecondName();
                    $item['LastName'] = $tblPerson->getLastName();
                    $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                    if ($tblCommon) {
                        $tblBirthDates = $tblCommon->getTblCommonBirthDates();
                        if ($tblBirthDates) {
                            $tblGender = $tblBirthDates->getTblCommonGender();
                            if ($tblGender) {
                                $item['Gender'] = substr($tblGender->getName(), 0, 1);
                            }
                        }
                    }
                    $tblAddress = $tblPerson->fetchMainAddress();
                    if ($tblAddress) {
                        $item['StreetName'] = $tblAddress->getStreetName();
                        $item['StreetNumber'] = $tblAddress->getStreetNumber();
                        $tblCity = $tblAddress->getTblCity();
                        if ($tblCity) {
                            $item['CityCode'] = $tblCity->getCode();
                            $item['CityName'] = $tblCity->getName();
                            $item['District'] = $tblCity->getDistrict();
                        }
                        $tblState = $tblAddress->getTblState();
                        if ($tblState) {
                            $item['State'] = $tblState->getName();
                        }
                        $item['Nation'] = $tblAddress->getNation();
                        $item['Country'] = $tblAddress->getCounty();
                    }
                }
                if($tblAccount){
                    $item['AccountName'] = $tblAccount->getUsername();
                }

                array_push($result, $item);
            });
        }
        return $result;
    }

    /**
     * @param array $result
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($result = array())
    {

        if (!empty($result)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Titel");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "2. Vorn.");
            $export->setValue($export->getCell("4", "0"), "Name");
            $export->setValue($export->getCell("5", "0"), "Geschlecht");
            $export->setValue($export->getCell("6", "0"), "Account");
            $export->setValue($export->getCell("7", "0"), "Passwort");
            $export->setValue($export->getCell("8", "0"), "Straße");
            $export->setValue($export->getCell("9", "0"), "Str.Nr.");
            $export->setValue($export->getCell("10", "0"), "PLZ");
            $export->setValue($export->getCell("11", "0"), "Stadt");
            $export->setValue($export->getCell("12", "0"), "Ortsteil");
            $export->setValue($export->getCell("13", "0"), "Bundesland");
            $export->setValue($export->getCell("14", "0"), "Land");

            $export->setStyle($export->getCell(0, 0), $export->getCell(14, 0))
                ->setFontBold();

            $Row = 0;

            foreach ($result as $Data) {
                $Row++;

                $export->setValue($export->getCell("0", $Row), $Data['Salutation']);
                $export->setValue($export->getCell("1", $Row), $Data['Title']);
                $export->setValue($export->getCell("2", $Row), $Data['FirstName']);
                $export->setValue($export->getCell("3", $Row), $Data['SecondName']);
                $export->setValue($export->getCell("4", $Row), $Data['LastName']);
                $export->setValue($export->getCell("5", $Row), $Data['Gender']);
                $export->setValue($export->getCell("6", $Row), $Data['AccountName']);
                $export->setValue($export->getCell("7", $Row), $Data['Password']);
                $export->setValue($export->getCell("8", $Row), $Data['StreetName']);
                $export->setValue($export->getCell("9", $Row), $Data['StreetNumber']);
                $export->setValue($export->getCell("10", $Row), $Data['CityCode']);
                $export->setValue($export->getCell("11", $Row), $Data['CityName']);
                $export->setValue($export->getCell("12", $Row), $Data['District']);
                $export->setValue($export->getCell("13", $Row), $Data['Nation']);
                $export->setValue($export->getCell("14", $Row), $Data['Country']);
            }

            //Column width
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(9);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(22);
            $export->setStyle($export->getCell(9, 0), $export->getCell(9, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell(11, 0), $export->getCell(11, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(12, 0), $export->getCell(12, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(13, 0), $export->getCell(13, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(14, 0), $export->getCell(14, $Row))->setColumnWidth(12);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param array          $PersonIdArray
     * @param string         $AccountType S = Student, C = Custody
     *
     * @return array
     * result['Time']
     * result['AddressMissCount']
     * result['AccountExistCount']
     * result['SuccessCount']
     */
    public function createAccount(/*IFormInterface $form, */
        $PersonIdArray = array(),
        $AccountType = 'S'
    )
    {

//        $IsMissingAddress = false;
        $TimeStamp = new DateTime('now');

        $successCount = 0;
        $accountExistCount = 0;
        $accountError = 0;

        $GroupByCount = 1;
        $CountAccount = 0;

        $tblAccountSession = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getAccountBySession();
        $result = array();
        foreach ($PersonIdArray as $PersonId) {
            if ($CountAccount % 30 == 0
                && $CountAccount != 0) {
                $GroupByCount++;
            }
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if ($tblPerson) {
                // ignore Person with Account
                if (AccountGatekeeper::useService()->getAccountAllByPerson($tblPerson, true)) {
                    continue;
                }
                // ignore Person without Main Address
                $tblAddress = $tblPerson->fetchMainAddress();
                if (!$tblAddress) {
                    $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                        ': Hauptadresse fehlt';
                    $accountError++;
                    continue;
                }
                // ignore without Consumer
                $tblConsumer = Consumer::useService()->getConsumerBySession();
                if ($tblConsumer == '') {
                    continue;
                }
                $name = $this->generateUserName($tblPerson, $tblConsumer, $AccountType, $result);
                if(!$name){
                    $accountError++;
                    continue;
                }
                $password = $this->generatePassword(8, 0, 2, 1);
                if (($tblAccountList = AccountGatekeeper::useService()->getAccountAllByPerson($tblPerson, true))) {
                    $IsUserExist = false;
                    foreach ($tblAccountList as $tblAccount) {
                        // ignore System Accounts (Support)
                        if ($tblAccount->getServiceTblIdentification()->getName() == 'System') {
                            continue;
                        }
                        $IsUserExist = true;
                    }
                    if ($IsUserExist) {
                        $accountExistCount++;
                        continue;
                    }
                }

                $tblAccount = AccountGatekeeper::useService()->insertAccount($name, $password, null, $tblConsumer);


                if ($tblAccount) {
                    $tblIdentification = AccountGatekeeper::useService()->getIdentificationByName('UserCredential');
                    AccountGatekeeper::useService()->addAccountAuthentication($tblAccount,
                        $tblIdentification);
                    $tblRole = Access::useService()->getRoleByName('Einstellungen: Benutzer (Schüler/Eltern)');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountGatekeeper::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    $tblRole = Access::useService()->getRoleByName('Bildung: Zensurenübersicht (Schüler/Eltern)');
                    if ($tblRole && !$tblRole->isSecure()) {
                        AccountGatekeeper::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                    AccountGatekeeper::useService()->addAccountPerson($tblAccount, $tblPerson);
                    $successCount++;

                    if ($AccountType == 'S') {
                        $Type = TblUserAccount::VALUE_TYPE_STUDENT;
                    } elseif ($AccountType == 'C') {
                        $Type = TblUserAccount::VALUE_TYPE_CUSTODY;
                    } else {
                        // default setting
                        $Type = TblUserAccount::VALUE_TYPE_STUDENT;
                    }
                    // add tblUserAccount
                    if($this->createUserAccount($tblAccount, $tblPerson, $TimeStamp, $password, $Type, $GroupByCount, $tblAccountSession)){
                        $CountAccount++;
                    }
                }
            }
        }

        $result['Time'] = $TimeStamp->format('d.m.Y H:i:s');
        $result['AccountExistCount'] = $accountExistCount;
        $result['SuccessCount'] = $successCount;
        $result['AccountError'] = $accountError;
        return $result;
//        return new Layout(
//            new LayoutGroup(
//                new LayoutRow(array(
//                    new LayoutColumn(
//                        ($IsMissingAddress
//                            ? new Warning($errorCount.' Personen ohne Hauptadresse ignoriert ('.$successCount.
//                                ' Benutzerzugänge zu Personen mit Gültiger Hauptadresse wurden erfolgreich angelegt).'
//                                . new Container('Weiter zum '.new Standard('Export', '/Setting/User/Account/Export', null,
//                                    array('Time' => $TimeStamp->format('d.m.Y H:i:s')))))
//                            : new Success($successCount.' Benutzer wurden erfolgreich angelegt.'
//                                . new Container('Weiter zum '.new Standard('Export', '/Setting/User/Account/Export', null,
//                                    array('Time' => $TimeStamp->format('d.m.Y H:i:s')))))
//                        )
//                    ),
////                    new LayoutColumn(
////                        ($AccountType == 'S'
////                            ? new Redirect('/Setting/User/Account/Student/Add',
////                                ($IsMissingAddress
////                                    ? Redirect::TIMEOUT_ERROR
////                                    : Redirect::TIMEOUT_SUCCESS
////                                ))
////                            : ($AccountType == 'C'
////                                ? new Redirect('/Setting/User/Account/Custody/Add',
////                                    ($IsMissingAddress
////                                        ? Redirect::TIMEOUT_ERROR
////                                        : Redirect::TIMEOUT_SUCCESS
////                                    ))
////                                : ''
////                            )
////                        )
////                    )
//                ))
//            )
//        );
    }

    /**
     * @param int $completeLength number all filled up with (abcdefghjkmnpqrstuvwxyz)
     * @param int $specialLength number of (!$%&=?*-:;.,+_)
     * @param int $numberLength number of (123456789)
     * @param int $capitalLetter number of (ABCDEFGHJKMNPQRSTUVWXYZ)
     *
     * @return string
     */
    public function generatePassword($completeLength = 8, $specialLength = 0, $numberLength = 2, $capitalLetter = 1)
    {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $secureCapitalChars = strtoupper($secureChars);
        $return = '';

        $count = $completeLength - $specialLength - $numberLength - $capitalLetter;
        if ($count > 0) {
            // get normal characters
            $temp = str_shuffle($secureChars);
            $return = substr($temp, 0, $count);
        }
        if ($capitalLetter > 0) {
            // get special characters
            $temp = str_shuffle($secureCapitalChars);
            $return .= substr($temp, 0, $capitalLetter);
        }
        if ($specialLength > 0) {
            // get special characters
            $temp = str_shuffle($specialChars);
            $return .= substr($temp, 0, $specialLength);
        }
        if ($numberLength > 0) {
            // get numbers
            $temp = str_shuffle($numberChars);
            $return .= substr($temp, 0, $numberLength);
        }
        // Random
        $return = str_shuffle($return);

        return $return;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblPerson  $tblPerson
     * @param DateTime  $TimeStamp
     * @param string     $userPassword
     * @param string     $Type STUDENT|CUSTODY
     * @param int        $GroupByCount
     * @param TblAccount $tblAccountSession
     *
     * @return false|TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        DateTime $TimeStamp,
        $userPassword,
        $Type,
        $GroupByCount,
        TblAccount $tblAccountSession
    ) {

        return ( new Data($this->getBinding()) )->createUserAccount(
            $tblAccount,
            $tblPerson,
            $TimeStamp,
            $userPassword,
            $Type,
            $GroupByCount,
            $tblAccountSession);
    }

    /**
     * @param TblPerson|null   $tblPerson
     * @param TblConsumer|null $tblConsumer
     * @param string           $AccountType
     * @param array            $result
     *
     * @return string|bool
     */
    public function generateUserName(TblPerson $tblPerson = null, TblConsumer $tblConsumer = null, $AccountType = 'S', &$result = array())
    {

        mb_internal_encoding("UTF-8");

        $FirstName = mb_substr($tblPerson->getFirstName(), 0, 2);
        $LastName = mb_substr($tblPerson->getLastName(), 0, 2);

        if($AccountType == 'S'){
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                    $randNumber = $tblCommonBirthDates->getBirthday('d');
                }
            }
            if(!isset($randNumber) || !$randNumber){
                $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                    ': Geburtsdatum fehlt';
                return false;
            }
        }
        // cut string with UTF8 encoding

        $UserName = $tblConsumer->getAcronym().'-'.$FirstName.$LastName;

        // Rand 1 - 99 with leading 0 if number < 10
        if($AccountType == 'C'){
            $randNumber = rand(32, 99);
        }

        $randNumber = str_pad($randNumber, 2, '0', STR_PAD_LEFT);

        $UserNamePrepare = $UserName.$randNumber;

        if($AccountType == 'C'){
            // find existing UserName?
            $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNamePrepare);
            if (!$tblAccount) {
                return $UserNamePrepare;
            } else {
                $i = 0;
                while ($tblAccount && $i <= 100) {
                    $i++;
                    $randNumber = rand(32, 99);
                    $randNumber = str_pad($randNumber, 2, '0', STR_PAD_LEFT);
                    $UserNameMod = $UserName.$randNumber;
                    $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNameMod);
                    if (!$tblAccount) {
                        return  $UserNameMod;
                    }
                }
                // no free AccountName
                $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                    ': kein freier Benutzeraccount '.$UserName.'XX';
                return false;
            }
        } elseif($AccountType == 'S'){
            $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNamePrepare);
            if (!$tblAccount) {
                $UserName = $UserNamePrepare;
            } else {
                // second try
                $FirstName2 = mb_substr($tblPerson->getFirstName(), 0, 3);
                $LastName2 = mb_substr($tblPerson->getLastName(), 0, 3);
                $UserName = $tblConsumer->getAcronym().'-'.$FirstName2.$LastName2;
                $UserNameMod = $UserName.$randNumber;
                $tblAccount = AccountGatekeeper::useService()->getAccountByUsername($UserNameMod);
                if (!$tblAccount) {
                    return $UserNameMod;
                } else {
                    $result[$tblPerson->getId()] = 'Person '.$tblPerson->getLastFirstName().
                        ': Benutzeraccount '.$UserName.$randNumber.' existiert bereits';
                    return false;
                }
            }
        }


        return $UserName;
    }

    /**
     * @param TblUserAccount[] $tblUserAccountList
     *
     * @return bool
     */
    public function updateDownloadBulk($tblUserAccountList)
    {

        $ExportDate = new DateTime();
        $UserName = '';
        $tblAccount = AccountGatekeeper::useService()->getAccountBySession();
        if ($tblAccount) {
            $UserName = $tblAccount->getUsername();
        }
        return (new Data($this->getBinding()))->updateDownloadBulk($tblUserAccountList, $ExportDate, $UserName);
    }

    /**
     * @param TblUserAccount $tblUserAccount
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        return ( new Data($this->getBinding()) )->removeUserAccount($tblUserAccount);
    }

    /**
     * @param DateTime $GroupByTime
     *
     * @return bool
     */
    public function clearPassword(DateTime $GroupByTime)
    {

        $tblUserAccountList = $this->getUserAccountByTime($GroupByTime);
        if ($tblUserAccountList) {
            return (new Data($this->getBinding()))->updateUserAccountClearPassword($tblUserAccountList);
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Password
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function changePassword(TblUserAccount $tblUserAccount, $Password)
    {

        return (new Data($this->getBinding()))->updateUserAccountChangePassword($tblUserAccount, $Password);
    }

    public function changeUpdateDate(TblUserAccount $tblUserAccount, $Type)
    {

        $UserName = '';
        if(($tblAccount = \SPHERE\Application\Setting\Authorization\Account\Account::useService()->getAccountBySession())){
            $UserName = $tblAccount->getUsername();
        }

        return (new Data($this->getBinding()))->changeUpdateDate($tblUserAccount, $UserName, $Type);
    }
}