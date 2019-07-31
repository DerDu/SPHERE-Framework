<?php

namespace SPHERE\Application\Reporting\Standard\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Service extends Extension
{

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param                     $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getGroup(IFormInterface $Stage = null, $Select = null, $Redirect = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblGroup = Group::useService()->getGroupById($Select['Group']);
        if ($tblGroup) {
            return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
                'GroupId' => $tblGroup->getId(),
            ));
        } else {
            $Stage->setError('Select[Group]', 'Bitte wählen Sie eine Gruppe aus');

            return $Stage;
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }

                $Item['Number'] = $count++;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Gender'] = '';
                $Item['Phone'] = '';
                $Item['ExcelPhone'] = '';
                $Item['Mail'] = '';
                $Item['ExcelMail'] = '';
                $Item['ExcelMailPrivate'] = '';
                $Item['ExcelMailBusiness'] = '';

                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                } else {
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                    $Item['Address'] = '';
                }

                //Gender
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                    $Item['Gender'] = $tblCommonGender->getName();
                }

                //Phone
                $tblPhoneList = array();
                $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPersonPhoneList) {
                    $key = 'Sort_1_' . $tblPerson->getId();
                    foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                        $tblPhone = $tblToPersonPhone->getTblPhone();
                        if ($tblPhone) {
                            if (isset($tblPhoneList[$key])) {
                                $tblPhoneList[$key] = $tblPhoneList[$key] . ', '
                                    . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                            } else {
                                $tblPhoneList[$key] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' ('
                                    . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                            }
                        }
                    }
                    if (isset($tblPhoneList[$key])) {
                        $tblPhoneList[$key] .= ')';
                    }
                }

                //Mail
                $tblMailList = array();
                $mailBusinessList = array();
                $mailPrivateList = array();
                $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblToPersonMailList) {
                    $key = 'Sort_1_' . $tblPerson->getId();
                    foreach ($tblToPersonMailList as $tblToPersonMail) {
                        $tblMail = $tblToPersonMail->getTblMail();
                        if ($tblMail) {
                            if (isset($tblMailList[$key])) {
                                $tblMailList[$key] = $tblMailList[$key] . ', '
                                    . $tblMail->getAddress();
                            } else {
                                $tblMailList[$key] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' ('
                                    . $tblMail->getAddress();
                            }

                            // für die Excel-Trennung der  Emailadressen nach Type
                            if ($tblToPersonMail->getTblType()->getName() == 'Privat') {
                                $mailPrivateList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                            } else {
                                $mailBusinessList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                            }
                        }
                    }
                    if (isset($tblMailList[$key])) {
                        $tblMailList[$key] .= ')';
                    }
                }

                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                $tblToPersonGuardianList = array();
                if ($tblType) {
                    $GuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                        $tblType);
                    if($GuardianList){
                        $tblToPersonGuardianList = $GuardianList;
                    }
                }
                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_AUTHORIZED);
                if ($tblType) {
                    $AuthorizedList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                        $tblType);
                    if($AuthorizedList){
                        $tblToPersonGuardianList = array_merge($tblToPersonGuardianList, $AuthorizedList);
                    }
                }
                if (!empty($tblToPersonGuardianList)) {
                    foreach ($tblToPersonGuardianList as $tblToPersonGuardian) {
                        if (($tblPersonGuardian = $tblToPersonGuardian->getServiceTblPersonFrom())) {
                            // Für die Sortierung der Telefonnummern und Mailadressen
                            // Schüler zuerst, dann Mutter, dann Vater, dann Bevollmächtigter
                            $genderString = $tblPersonGuardian->getGenderString();
                            if ($genderString == 'Weiblich') {
                                $isFemale = true;
                            } elseif ($genderString == 'Männlich') {
                                $isFemale = false;
                            } else {
                                if ($tblPersonGuardian->getSalutation() == 'Frau') {
                                    $isFemale = true;
                                } elseif ($tblPersonGuardian->getSalutation() == 'Herr') {
                                    $isFemale = false;
                                } else {
                                    $isFemale = false;
                                }
                            }

                            $pre = '';
                            if ($tblToPersonGuardian->getTblType()->getName() == TblType::IDENTIFIER_AUTHORIZED) {
                                $pre = 'Bev. ';

                                if ($isFemale) {
                                    $key = 'Sort_4_' . $tblPersonGuardian->getId();
                                } else {
                                    $key = 'Sort_5_' . $tblPersonGuardian->getId();
                                }
                            } else {
                                if ($isFemale) {
                                    $key = 'Sort_2_' . $tblPersonGuardian->getId();
                                } else {
                                    $key = 'Sort_3_' . $tblPersonGuardian->getId();
                                }
                            }

                            //Phone Guardian
                            $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                            if ($tblToPersonPhoneList) {
                                foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                                    $tblPhone = $tblToPersonPhone->getTblPhone();
                                    if ($tblPhone) {
                                        if (isset($tblPhoneList[$key])) {
                                            $tblPhoneList[$key] = $tblPhoneList[$key] . ', '
                                                . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                                        } else {
                                            $tblPhoneList[$key] = $pre . $tblPersonGuardian->getFirstName() . ' ' .
                                                $tblPersonGuardian->getLastName() . ' ('
                                                . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                                        }
                                    }
                                }
                                if (isset($tblPhoneList[$key])) {
                                    $tblPhoneList[$key] .= ')';
                                }
                            }

                            //Mail Guardian
                            $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
                            if ($tblToPersonMailList) {
                                foreach ($tblToPersonMailList as $tblToPersonMail) {
                                    $tblMail = $tblToPersonMail->getTblMail();
                                    if ($tblMail) {
                                        if (isset($tblMailList[$key])) {
                                            $tblMailList[$key] = $tblMailList[$key] . ', '
                                                . $tblMail->getAddress();
                                        } else {
                                            $tblMailList[$key] = $pre . $tblPersonGuardian->getFirstName() . ' ' .
                                                $tblPersonGuardian->getLastName() . ' ('
                                                . $tblMail->getAddress();
                                        }

                                        // für die Excel-Trennung der  Emailadressen nach Type
                                        if ($tblToPersonMail->getTblType() == 'Privat') {
                                            $mailPrivateList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                                        } else {
                                            $mailBusinessList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                                        }
                                    }
                                }
                                if (isset($tblMailList[$key])) {
                                    $tblMailList[$key] .= ')';
                                }
                            }
                        }
                    }
                }

                // Insert PhoneList
                if (!empty($tblPhoneList)) {
                    ksort($tblPhoneList);
                    $Item['Phone'] .= implode('<br>', $tblPhoneList);
                    $Item['ExcelPhone'] = $tblPhoneList;
                }
                // Insert MailList
                if (!empty($tblMailList)) {
                    ksort($tblMailList);
                    $Item['Mail'] .= implode('<br>', $tblMailList);
                    $Item['ExcelMail'] = $tblMailList;

                    if (!empty($mailPrivateList)) {
                        ksort($mailPrivateList);
                        $Item['ExcelMailPrivate'] = implode('; ', $mailPrivateList);
                    }

                    if (!empty($mailBusinessList)) {
                        ksort($mailBusinessList);
                        $Item['ExcelMailBusiness'] = implode('; ', $mailBusinessList);
                    }
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $Item['Denomination'] = $Item['Birthday'] = $Item['Birthplace'] = '';
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geschlecht");
            $export->setValue($export->getCell("3", "0"), "Konfession");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Ortsteil");
            $export->setValue($export->getCell("7", "0"), "Straße");
            $export->setValue($export->getCell("8", "0"), "Hausnr.");
            $export->setValue($export->getCell("9", "0"), "PLZ");
            $export->setValue($export->getCell("10", "0"), "Ort");
            $export->setValue($export->getCell("11", "0"), "Telefon");
            $export->setValue($export->getCell("12", "0"), "E-Mail");
            $export->setValue($export->getCell("13", "0"), "E-Mail Privat");
            $export->setValue($export->getCell("14", "0"), "E-Mail Geschäftlich");

            $export->setStyle($export->getCell(0, 0), $export->getCell(14, 0))
                ->setFontBold();

            $Row = 0;

            // Strich nach dem Header
            $export->setStyle($export->getCell(0, $Row), $export->getCell(14, $Row))
                ->setBorderBottom();

            foreach ($PersonList as $PersonData) {
                $Row++;
                $phoneRow = $mailRow = $Row;

                $export->setValue($export->getCell("0", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Gender']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("6", $Row), $PersonData['District']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("8", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("10", $Row), $PersonData['City']);
                $export->setValue($export->getCell("13", $Row), $PersonData['ExcelMailPrivate']);
                $export->setValue($export->getCell("14", $Row), $PersonData['ExcelMailBusiness']);

                if (is_array($PersonData['ExcelPhone'])) {
                    foreach ($PersonData['ExcelPhone'] as $Phone) {
                        $export->setValue($export->getCell(11, $phoneRow++), $Phone);
                    }
                }
                if (is_array($PersonData['ExcelMail'])) {
                    foreach ($PersonData['ExcelMail'] as $Mail) {
                        $export->setValue($export->getCell(12, $mailRow++), $Mail);
                    }
                }
                // get row to the same high as highest PhoneRow or MailRow
                if ($Row < ($phoneRow - 1)) {
                    $Row = ($phoneRow - 1);
                }
                if ($Row < ($mailRow - 1)) {
                    $Row = ($mailRow - 1);
                }

                // Strich nach jedem Schüler
                $export->setStyle($export->getCell(0, $Row), $export->getCell(14, $Row))
                    ->setBorderBottom();
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(50);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(50);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(40);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $Row))->setColumnWidth(50);

            $Row++;
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            // Legende
            $Row = $Row - 2;
            $export->setValue($export->getCell("11", $Row), 'Abkürzungen Telefon:');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'p = Privat');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'g = Geschäftlich');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'n = Notfall');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'f = Fax');

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createExtendedClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['Guardian1'] = $Item['PhoneGuardian1'] = $Item['PhoneGuardian1Excel'] = '';
                $Item['Guardian2'] = $Item['PhoneGuardian2'] = $Item['PhoneGuardian2Excel'] = '';
                $Item['Guardian3'] = $Item['PhoneGuardian3'] = $Item['PhoneGuardian3Excel'] = '';
                $Item['Authorized'] = $Item['PhoneAuthorized'] = $Item['PhoneAuthorizedExcel'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() == 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() == 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $Item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }
                // Guardian 1
                $tblPersonG1 = false;
                // Guardian 2
                $tblPersonG2 = false;
                // Guardian 3
                $tblPersonG3 = false;
                // Authorized
                $tblPersonA = false;
                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblToPersonList) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt' && $tblToPerson->getServiceTblPersonFrom()) {
                            switch ($tblToPerson->getRanking()) {
                                case 1: $tblPersonG1 = $tblToPerson->getServiceTblPersonFrom(); break;
                                case 2: $tblPersonG2 = $tblToPerson->getServiceTblPersonFrom(); break;
                                case 3: $tblPersonG3 = $tblToPerson->getServiceTblPersonFrom(); break;
                            }
                        } elseif($tblToPerson->getTblType()->getName() == 'Bevollmächtigt' && $tblToPerson->getServiceTblPersonFrom()){
                            $tblPersonA = $tblToPerson->getServiceTblPersonFrom();
                        }
                    }
                }
                if ($tblPersonG1) {
                    $Item['Guardian1'] = $tblPersonG1->getFullName();
                    $Item['PhoneGuardian1'] = $this->getPhoneList($tblPersonG1);
                    $Item['PhoneGuardian1Excel'] = $this->getPhoneList($tblPersonG1, true);
                }
                if ($tblPersonG2) {
                    $Item['Guardian2'] = $tblPersonG2->getFullName();
                    $Item['PhoneGuardian2'] = $this->getPhoneList($tblPersonG2);
                    $Item['PhoneGuardian2Excel'] = $this->getPhoneList($tblPersonG2, true);
                }
                if ($tblPersonG3) {
                    $Item['Guardian3'] = $tblPersonG3->getFullName();
                    $Item['PhoneGuardian3'] = $this->getPhoneList($tblPersonG3);
                    $Item['PhoneGuardian3Excel'] = $this->getPhoneList($tblPersonG3, true);
                }
                if($tblPersonA){
                    $Item['Authorized'] = $tblPersonA->getFullName();
                    $Item['PhoneAuthorized'] = $this->getPhoneList($tblPersonA);
                    $Item['PhoneAuthorizedExcel'] = $this->getPhoneList($tblPersonA, true);
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsExcel
     *
     * @return string
     */
    private function getPhoneList(TblPerson $tblPerson, $IsExcel = false)
    {

        $tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson);

        $phoneList = array();

        if ($tblToPersonList) {
            $privateList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Privat'){
                    $privateList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $companyList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Geschäftlich'){
                    $companyList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $secureList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Notfall'){
                    $secureList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $faxList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Fax'){
                    $faxList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $phoneList = array_merge($privateList, $companyList, $secureList, $faxList);
        }
        if(!empty($phoneList)){
            return implode(', ', $phoneList);
        }
        return '';
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     */
    public function createExtendedClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $IsAuthorized = false;
            $TempList = $PersonList;

            foreach($TempList as $Row){
                if($Row['Authorized']){
                    $IsAuthorized = true;
                    break;
                }
            }

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, "0"), "#");
            $export->setValue($export->getCell($column++, "0"), "Schülernummer");
            $export->setValue($export->getCell($column++, "0"), "Name");
            $export->setValue($export->getCell($column++, "0"), "Vorname");
            $export->setValue($export->getCell($column++, "0"), "Geschlecht");
            $export->setValue($export->getCell($column++, "0"), "Adresse");
            $export->setValue($export->getCell($column++, "0"), "Straße");
            $export->setValue($export->getCell($column++, "0"), "Str.Nr");
            $export->setValue($export->getCell($column++, "0"), "PLZ");
            $export->setValue($export->getCell($column++, "0"), "Ort");
            $export->setValue($export->getCell($column++, "0"), "Ortsteil");
            $export->setValue($export->getCell($column++, "0"), "Geburtsdatum");
            $export->setValue($export->getCell($column++, "0"), "Geburtsort");
            $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 1");
            $export->setValue($export->getCell($column++, "0"), "Tel. Sorgeber. 1");
            $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 2");
            $export->setValue($export->getCell($column++, "0"), "Tel. Sorgeber. 2");
            $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 3");
            $export->setValue($export->getCell($column, "0"), "Tel. Sorgeber. 3");
            if($IsAuthorized){
                $column++;
                $export->setValue($export->getCell($column++, "0"), "Bevollmächtigt");
                $export->setValue($export->getCell($column, "0"), "Tel. Bevollmächtigt");
            }

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $column = 0;
                $export->setValue($export->getCell($column++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Gender']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Address']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Code']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['District']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian1']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardian1Excel']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian2']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardian2Excel']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian3']);
                $export->setValue($export->getCell($column, $Row), $PersonData['PhoneGuardian3Excel']);
                if($IsAuthorized){
                    $column++;
                    $export->setValue($export->getCell($column++, $Row), $PersonData['Authorized']);
                    $export->setValue($export->getCell($column, $Row), $PersonData['PhoneAuthorizedExcel']);
                }

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            // Legende
            $Row = $Row - 2;
            $column = 14;
            $export->setValue($export->getCell($column, $Row), 'Abkürzungen Telefon:');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'p = Privat');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'g = Geschäftlich');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'n = Notfall');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'f = Fax');

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createBirthdayClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();

        $All = 0;

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {
                //                $All++;
                //                $Item['Number'] = $All;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Age'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                }

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $birthDate = (new \DateTime($common->getTblCommonBirthDates()->getBirthday()));
                    $now = new \DateTime();
                    if ($birthDate->format('Y.m') != $now->format('Y.m')) {
                        if (($birthDate->format('m.d')) <= ($now->format('m.d'))) {
                            $Item['Age'] = $now->format('Y') - $birthDate->format('Y');
                        } else {
                            $Item['Age'] = ($now->format('Y') - 1) - $birthDate->format('Y');
                        }
                    }
                }
                array_push($TableContent, $Item);
            });
        }
        if (!empty($TableContent)) {

            $day = array();
            $month = array();
            $year = array();
            foreach ($TableContent as $key => $row) {
                $month[$key] = substr($row['Birthday'], 3, 2);
                $day[$key] = substr($row['Birthday'], 0, 2);
                $year[$key] = substr($row['Birthday'], 6, 4);
            }
            array_multisort($month, SORT_ASC, $day, SORT_ASC, $year, SORT_DESC, $TableContent);

            array_walk($TableContent, function (&$Row) use (&$All) {
                $All++;
                $Row['Number'] = $All;
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createBirthdayClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Alter");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Age']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createMedicalInsuranceClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['MedicalInsurance'] = '';
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }

                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        if ($tblStudent->getTblStudentMedicalRecord()) {
                            $Item['MedicalInsurance'] = $tblStudent->getTblStudentMedicalRecord()->getInsurance();
                        }
                        $Item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }
                }
                $Item['Name'] = $tblPerson->getLastFirstName();
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday() . '<br/>' . $common->getTblCommonBirthDates()->getBirthplace();
                }

                $Guardian1 = null;
                $Guardian2 = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    $Count = 0;
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($Count === 0) {
                                $Guardian1 = $guardian->getServiceTblPersonFrom();
                            }
                            if ($Count === 1) {
                                $Guardian2 = $guardian->getServiceTblPersonFrom();
                            }
                            $Count++;
                        }
                    }
                }

                $phoneListGuardian = array();
                if ($Guardian1) {
                    $PhoneListGuardian1 = Phone::useService()->getPhoneAllByPerson($Guardian1);
                    if ($PhoneListGuardian1) {
                        foreach ($PhoneListGuardian1 as $PhoneGuardian1) {
                            $phoneListGuardian[] = $PhoneGuardian1->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian1 = $Guardian1->getFullName();
                } else {
                    $Guardian1 = '';
                }
                if ($Guardian2) {
                    $PhoneListGuardian2 = Phone::useService()->getPhoneAllByPerson($Guardian2);
                    if ($PhoneListGuardian2) {
                        foreach ($PhoneListGuardian2 as $PhoneGuardian2) {
                            $phoneListGuardian[] = $PhoneGuardian2->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian2 = $Guardian2->getFullName();
                } else {
                    $Guardian2 = '';
                }
                $Item['Guardian'] = $Guardian1 . '<br/>' . $Guardian2;

                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $phoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneArray[] = $phone->getTblPhone()->getNumber();
                    }
                }
                if (count($phoneArray) >= 1) {
                    $phoneString = implode('<br/>', $phoneArray);
                } else {
                    $phoneString = '';
                }
                $Item['PhoneNumber'] = $phoneString;
                $phoneListGuardian = array_unique($phoneListGuardian);
                if (count($phoneListGuardian) >= 1) {
                    $phoneGuardianString = implode('<br/>', $phoneListGuardian);
                } else {
                    $phoneGuardianString = '';
                }
                $Item['PhoneGuardianNumber'] = $phoneGuardianString;

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createMedicalInsuranceClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "1"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Krankenkasse");
            $export->setValue($export->getCell("5", "0"), "1. Sorgeberechtigter");
            $export->setValue($export->getCell("5", "1"), "2. Sorgeberechtigter");
            $export->setValue($export->getCell("6", "0"), "Telefon");
            $export->setValue($export->getCell("6", "1"), "Schüler");
            $export->setValue($export->getCell("7", "0"), "Telefon");
            $export->setValue($export->getCell("7", "1"), "Sorgeberechtigte");

            $Row = 2;

            foreach ($PersonList as $PersonData) {
                $Name = explode('<br/>', $PersonData['Name']);
                $Address = explode('<br/>', $PersonData['Address']);
                $Birthday = explode('<br/>', $PersonData['Birthday']);
                $KK = explode('<br/>', $PersonData['MedicalInsurance']);
                $Guardian = explode('<br/>', $PersonData['Guardian']);
                $PhoneNumber = explode('<br/>', $PersonData['PhoneNumber']);
                $PhoneGuardianNumber = explode('<br/>', $PersonData['PhoneGuardianNumber']);

                $count = count($Name);
                if (count($Address) > $count) {
                    $count = count($Address);
                }
                if (count($KK) > $count) {
                    $count = count($KK);
                }
                if (count($Guardian) > $count) {
                    $count = count($Guardian);
                }
                if (count($PhoneNumber) > $count) {
                    $count = count($PhoneNumber);
                }
                if (count($PhoneGuardianNumber) > $count) {
                    $count = count($PhoneGuardianNumber);
                }

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                for ($i = 0; $i < $count; $i++) {
                    if (isset($Name[$i])) {
                        $export->setValue($export->getCell("1", $Row), $Name[$i]);
                    }
                    if (isset($Address[$i])) {
                        $export->setValue($export->getCell("2", $Row), $Address[$i]);
                    }
                    if (isset($Birthday[$i])) {
                        $export->setValue($export->getCell("3", $Row), $Birthday[$i]);
                    }
                    if (isset($KK[$i])) {
                        $export->setValue($export->getCell("4", $Row), $KK[$i]);
                    }
                    if (isset($Guardian[$i])) {
                        $export->setValue($export->getCell("5", $Row), $Guardian[$i]);
                    }
                    if (isset($PhoneNumber[$i])) {
                        $export->setValue($export->getCell("6", $Row), $PhoneNumber[$i]);
                    }
                    if (isset($PhoneGuardianNumber[$i])) {
                        $export->setValue($export->getCell("7", $Row), $PhoneGuardianNumber[$i]);
                    }
                    $Row++;
                }
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());

            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All, $tblGroup) {

                $All++;
                $Item['Title'] = $tblPerson->getTitle();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Number'] = $All;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $Item['BirthdaySort'] = '';
                $Item['BirthdayYearSort'] = '';
                $Item['PhoneNumber'] = '';
                $Item['MobilPhoneNumber'] = '';
                $Item['Mail'] = '';
                $Item['BirthPlace'] = '';
                $Item['Gender'] = '';
                $Item['Nationality'] = '';
                $Item['Religion'] = '';
                $Item['ParticipationWillingness'] = '';
                $Item['ParticipationActivities'] = '';
                $Item['RemarkFrontend'] = '';
                $Item['RemarkExcel'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['RemarkExcel'] = $tblCommon->getRemark();
                    $Item['RemarkFrontend'] = nl2br($tblCommon->getRemark());
                    if (($tblBirthdates = $tblCommon->getTblCommonBirthDates())) {
                        $Item['Birthday'] = $tblBirthdates->getBirthday();
                        if ($Item['Birthday'] != '') {
                            $Year = substr($Item['Birthday'], 6, 4);
                            $Month = substr($Item['Birthday'], 3, 2);
                            $Day = substr($Item['Birthday'], 0, 2);
                            if (is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdaySort'] = $Month * 100 + $Day;
                            }
                            if (is_numeric($Year) && is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdayYearSort'] = ($Year * 10000) + ($Month * 100) + $Day;
                            }
                        }
                        $Item['BirthPlace'] = $tblBirthdates->getBirthplace();
                        if (($tblGender = $tblBirthdates->getTblCommonGender())) {
                            $Item['Gender'] = $tblGender->getName();
                        }
                    }
                    if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                        $Item['Nationality'] = $tblCommonInformation->getNationality();
                        $Item['Religion'] = $tblCommonInformation->getDenomination();
                        $Item['ParticipationActivities'] = $tblCommonInformation->getAssistanceActivity();
                        if ($tblCommonInformation->isAssistance()) {
                            $Item['ParticipationWillingness'] = 'ja';
                        } else {
                            $Item['ParticipationWillingness'] = 'nein';

                        }
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);

                $phoneArray = array();
                $mobilePhoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getDescription() === 'Festnetz') {
                            $phoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                        if ($phone->getTblType()->getDescription() === 'Mobil') {
                            $mobilePhoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                }
                if (count($phoneArray) >= 1) {
                    $Item['PhoneNumber'] = implode(', ', $phoneArray);
                }
                if (count($mobilePhoneArray) >= 1) {
                    $Item['MobilPhoneNumber'] = implode(', ', $mobilePhoneArray);
                }
                $mailAddressList = Mail::useService()->getMailAllByPerson($tblPerson);
                $mailList = array();
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $mailList[] = $mailAddress->getTblMail()->getAddress();
                    }
                }
                if (count($mailList) >= 1) {
                    $Item['Mail'] = $mailList[0];
                }

                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $Item['ReservationDate'] = '';
                    $Item['InterviewDate'] = '';
                    $Item['TrialDate'] = '';
                    $Item['ReservationYear'] = '';
                    $Item['ReservationDivision'] = '';
                    $Item['SchoolTypeA'] = '';
                    $Item['SchoolTypeB'] = '';
                    if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                        if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                            $Item['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                            $Item['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $Item['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            $Item['SchoolTypeA'] = ($tblProspectReservation->getServiceTblTypeOptionA() ? $tblProspectReservation->getServiceTblTypeOptionA()->getName() : '');
                            $Item['SchoolTypeB'] = ($tblProspectReservation->getServiceTblTypeOptionB() ? $tblProspectReservation->getServiceTblTypeOptionB()->getName() : '');
                        }
                    }
                }

                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $Item['Identifier'] = '';
                    $Item['School'] = '';
                    $Item['SchoolCourse'] = '';
                    $Item['SchoolType'] = '';
                    $Item['PictureSchoolWriting'] = '';
                    $Item['PicturePublication'] = '';
                    $Item['PictureWeb'] = '';
                    $Item['PictureFacebook'] = '';
                    $Item['PicturePrint'] = '';
                    $Item['PictureFilm'] = '';
                    $Item['PictureAdd'] = '';
                    $Item['NameSchoolWriting'] = '';
                    $Item['NamePublication'] = '';
                    $Item['NameWeb'] = '';
                    $Item['NameFacebook'] = '';
                    $Item['NamePrint'] = '';
                    $Item['NameFilm'] = '';
                    $Item['NameAdd'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $tblDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson);
                        if ($tblDivisionList) {
                            foreach ($tblDivisionList as $tblDivision) {
                                if ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                                    $Item['SchoolType'] = $tblDivision->getTypeName();
                                }
                            }
                        }
                        $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson);
                        $Item['Identifier'] = $tblStudent->getIdentifierComplete();
                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if (($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblStudentTransferType))) {
                            $Item['School'] = ($tblStudentTransfer->getServiceTblCompany()
                                ? $tblStudentTransfer->getServiceTblCompany()->getDisplayName()
                                : '');
                            $Item['SchoolCourse'] = (Student::useService()->getCourseByStudent($tblStudent)
                                ? Student::useService()->getCourseByStudent($tblStudent)->getName()
                                : '');
                        }
                        $tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent);
                        if ($tblAgreementList) {
                            $MarkValue = 'Ja';
                            foreach ($tblAgreementList as $tblAgreement) {
                                $tblAgreementType = $tblAgreement->getTblStudentAgreementType();
                                $CategoryString = $tblAgreementType->getTblStudentAgreementCategory()->getName();
                                switch ($tblAgreementType->getName()) {
                                    case 'in Schulschriften':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureSchoolWriting'] = $MarkValue
                                            : $Item['NameSchoolWriting'] = $MarkValue);
                                        break;
                                    case 'in Veröffentlichungen':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PicturePublication'] = $MarkValue
                                            : $Item['NamePublication'] = $MarkValue);
                                        break;
                                    case 'auf Internetpräsenz':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureWeb'] = $MarkValue
                                            : $Item['NameWeb'] = $MarkValue);
                                        break;
                                    case 'auf Facebookseite':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureFacebook'] = $MarkValue
                                            : $Item['NameFacebook'] = $MarkValue);
                                        break;
                                    case 'für Druckpresse':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PicturePrint'] = $MarkValue
                                            : $Item['NamePrint'] = $MarkValue);
                                        break;
                                    case 'durch Ton/Video/Film':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureFilm'] = $MarkValue
                                            : $Item['NameFilm'] = $MarkValue);
                                        break;
                                    case 'für Werbung in eigener Sache':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureAdd'] = $MarkValue
                                            : $Item['NameAdd'] = $MarkValue);
                                        break;
                                }
                            }
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $Item['Occupation'] = '';
                    $Item['Employment'] = '';
                    $Item['Remark'] = '';
                    if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
                        $Item['Occupation'] = $tblCustody->getOccupation();
                        $Item['Employment'] = $tblCustody->getEmployment();
                        $Item['Remark'] = $tblCustody->getRemark();
                    }
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $Item['TeacherAcronym'] = '';
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                        $Item['TeacherAcronym'] = $tblTeacher->getAcronym();
                    }
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $Item['ClubIdentifier'] = '';
                    $Item['EntryDate'] = '';
                    $Item['ExitDate'] = '';
                    $Item['ClubRemark'] = '';
                    if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                        $Item['ClubIdentifier'] = $tblClub->getIdentifier();
                        $Item['EntryDate'] = $tblClub->getEntryDate();
                        $Item['ExitDate'] = $tblClub->getExitDate();
                        $Item['ClubRemark'] = $tblClub->getRemark();
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     * @param int   $GroupId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createGroupListExcel($PersonList, $tblPersonList, $GroupId)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (!empty($PersonList) && $tblGroup) {
            $ColumnStandard = array(
                'Number'                   => 'lfd. Nr.',
                'Salutation'               => 'Anrede',
                'Title'                    => 'Titel',
                'FirstName'                => 'Vorname',
                'LastName'                 => 'Nachname',
                'StreetName'               => 'Straße',
                'StreetNumber'             => 'Str.Nr',
                'Code'                     => 'PLZ',
                'City'                     => 'Ort',
                'District'                 => 'Ortsteil',
                'PhoneNumber'              => 'Telefon Festnetz',
                'MobilPhoneNumber'         => 'Telefon Mobil',
                'Mail'                     => 'E-mail',
                'Birthday'                 => 'Geburtstag',
                'BirthdaySort'             => 'Sortierung Geburtstag',
                'BirthdayYearSort'         => 'Sortierung Geburtsdatum',
                'BirthPlace'               => 'Geburtsort',
                'Gender'                   => 'Geschlecht',
                'Nationality'              => 'Staatsangehörigkeit',
                'Religion'                 => 'Konfession',
                'ParticipationWillingness' => 'Mitarbeitsbereitschaft',
                'ParticipationActivities'  => 'Mitarbeitsbereitschaft - Tätigkeiten',
                'RemarkExcel'              => 'Bemerkungen'
            );
            $ColumnCustom = array();

            if ($tblGroup->getMetaTable() == 'PROSPECT') {
                $ColumnCustom = array(
                    'ReservationDate'     => 'Eingangsdatum',
                    'InterviewDate'       => 'Aufnahmegespräch',
                    'TrialDate'           => 'Schnuppertag',
                    'ReservationYear'     => 'Voranmeldung Schuljahr',
                    'ReservationDivision' => 'Voranmeldung Stufe',
                    'SchoolTypeA'         => 'Voranmeldung Schulart A',
                    'SchoolTypeB'         => 'Voranmeldung Schulart B'
                );
            }
            if ($tblGroup->getMetaTable() == 'STUDENT') {
                $ColumnCustom = array(
                    'Identifier'           => 'Schülernummer',
                    'School'               => 'Schule',
                    'SchoolType'           => 'Schulart',
                    'SchoolCourse'         => 'Bildungsgang',
                    'Division'             => 'aktuelle Klasse',
                    'PictureSchoolWriting' => 'Einverständnis Foto Schulschriften',
                    'PicturePublication'   => 'Einverständnis Foto Veröffentlichungen',
                    'PictureWeb'           => 'Einverständnis Foto Internetpräsenz',
                    'PictureFacebook'      => 'Einverständnis Foto Facebookseite',
                    'PicturePrint'         => 'Einverständnis Foto Druckpresse',
                    'PictureFilm'          => 'Einverständnis Foto Ton/Video/Film',
                    'PictureAdd'           => 'Einverständnis Foto Werbung in eigener Sache',
                    'NameSchoolWriting'    => 'Einverständnis Name Schulschriften',
                    'NamePublication'      => 'Einverständnis Name Veröffentlichungen',
                    'NameWeb'              => 'Einverständnis Name Internetpräsenz',
                    'NameFacebook'         => 'Einverständnis Name Facebookseite',
                    'NamePrint'            => 'Einverständnis Name Druckpresse',
                    'NameFilm'             => 'Einverständnis Name Ton/Video/Film',
                    'NameAdd'              => 'Einverständnis Name Werbung in eigener Sache',
                );
            }
            if ($tblGroup->getMetaTable() == 'CUSTODY') {
                $ColumnCustom = array(
                    'Occupation' => 'Beruf',
                    'Employment' => 'Arbeitsstelle',
                    'Remark'     => 'Bemerkung Sorgeberechtigter',
                );
            }
            if ($tblGroup->getMetaTable() == 'TEACHER') {
                $ColumnCustom = array(
                    'TeacherAcronym' => 'Lehrerkürzel',
                );
            }
            if ($tblGroup->getMetaTable() == 'CLUB') {
                $ColumnCustom = array(
                    'ClubIdentifier' => 'Mitgliedsnummer',
                    'EntryDate'      => 'Eintrittsdatum',
                    'ExitDate'       => 'Austrittsdatum',
                    'ClubRemark'     => 'Bemerkung Vereinsmitglied',
                );
            }


            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Row = 0;
//            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
//                ->mergeCells()->setAlignmentCenter();
            $export->setValue($export->getCell(0, 0), 'Gruppenliste ' . $tblGroup->getName());

            if (!empty($tblGroup->getDescription())) {
                $Row++;
//                $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 1), $tblGroup->getDescription());
            }

            if (!empty($tblGroup->getRemark())) {
                $Row++;
//                $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 2), $tblGroup->getRemark());
            }

            $Row += 2;

            $Column = 0;
            foreach ($ColumnStandard as $Key => $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
                $Column++;
            }
            foreach ($ColumnCustom as $Key => $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
//                $export->setStyle($export->getCell($Column, $Row))->setWrapText();
                $Column++;
            }

            $Row++;

            foreach ($PersonList as $PersonData) {
                $Column = 0;
                foreach ($ColumnStandard as $Key => $Value) {
                    if (isset($PersonData[$Key])) {
                        // handle value as numeric
                        if ($Key == 'Number'
                            || $Key == 'BirthdaySort'
                            || $Key == 'BirthdayYearSort') {
                            // don't display if empty
                            if ($PersonData[$Key] != '') {
                                $export->setValue($export->getCell($Column, $Row), $PersonData[$Key],
                                    \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            }
                        } else {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                            if ($Key == 'RemarkExcel') {
                                $export->setStyle($export->getCell($Column, $Row))->setWrapText()
                                    ->setAlignmentMiddle();
                            }
                        }
                    }
                    $Column++;
                }
                if (!empty($ColumnCustom)) {
                    foreach ($ColumnCustom as $Key => $Value) {
                        if (isset($PersonData[$Key])) {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                        }
                        $Column++;
                    }
                }

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row),
                'Weiblich: '.Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row),
                'Männlich: '.Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt: '.count($tblPersonList));;

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblToPersonPhone $tblToPerson
     *
     * @return string
     */
    public function getShortTypeByTblToPersonPhone(TblToPersonPhone $tblToPerson)
    {

        $result = '';
        $tblType = $tblToPerson->getTblType();
        if ($tblType) {
            switch ($tblType->getName()) {
                case 'Privat':
                    $result = 'p';
                    break;
                case 'Geschäftlich':
                    $result = 'g';
                    break;
                case 'Notfall':
                    $result = 'n';
                    break;
                case 'Fax':
                    $result = 'f';
                    break;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function createInterestedPersonList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('PROSPECT'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Phone'] = $Item['PhoneSimple'] = '';
                $Item['PhoneGuardian'] = $Item['PhoneGuardianSimple'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = '';
                $Item['DivisionLevel'] = '';
                $Item['RegistrationDate'] = '';
                $Item['InterviewDate'] = '';
                $Item['TrialDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = array();
                $Item['FatherSalutation'] = $Item['FatherTitle'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherTitle'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';
                $Item['Remark'] = $Item['RemarkExcel'] = '';
                $Item['MailGuardian'] = $Item['ExcelMailGuardian'] = $Item['ExcelMailGuardianSimple'] = '';

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $Item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $Item['TypeOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $Item['TypeOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $Item['DivisionLevel'] = $tblProspectReservation->getReservationDivision();
                        }
                    }
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                        $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }

                    $Item['Remark'] = nl2br($tblProspect->getRemark());
                    $Item['RemarkExcel'] = $tblProspect->getRemark();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $Item['Nationality'] = $common->getTblCommonInformation()->getNationality();
                }

                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $relationship */
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getServiceTblPersonFrom() && $relationship->getServiceTblPersonTo()
                            && $relationship->getTblType()->getName() == 'Geschwisterkind'
                        ) {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonTo()->getFullName();
                            } else {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonFrom()->getFullName();
                            }
                        }
                    }
                    if (!empty($Item['Siblings'])) {
                        $Item['Siblings'] = implode(', ', $Item['Siblings']);
                    }
                }
                if (empty($Item['Siblings'])) {
                    $Item['Siblings'] = '';
                }

                $PhoneListSimple = array();
                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                            $PhoneListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                            if ($Item['Phone'] == '') {
                                $Item['Phone'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            } else {
                                $Item['Phone'] .= ', ' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            }
                        }
                    }
                    if ($Item['Phone'] != '') {
                        $Item['Phone'] .= ')';
                    }
                }

                if (!empty($PhoneListSimple)) {
                    $Item['PhoneSimple'] = implode('; ', $PhoneListSimple);
                }

                $father = null;
                $mother = null;
                $PhoneGuardianListSimple = array();
                $MailListSimple = array();
                $tblMailList = array();
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            // get PhoneNumber by Guardian
                            $tblPersonGuardian = $guardian->getServiceTblPersonFrom();
                            if ($tblPersonGuardian) {
                                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                                if ($tblToPhoneList) {
                                    foreach ($tblToPhoneList as $tblToPhone) {
                                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                                            $PhoneGuardianListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                                            if (!isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] =
                                                    $tblPersonGuardian->getFirstName() . ' ' . $tblPersonGuardian->getLastName() .
                                                    ' (' . $tblPhone->getNumber() . ' ' .
                                                    // modify TypeShort
                                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                                            } else {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ', ' . $tblPhone->getNumber() . ' ' .
                                                    // modify TypeShort
                                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                                            }
                                        }
                                    }
                                }
                                if (isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                                    $Item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ')';
                                }
                            }

                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            } else {
                                if ($father === null) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } else {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            }

                            //Mail Guardian
                            $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
                            if ($tblToPersonMailList) {
                                foreach ($tblToPersonMailList as $tblToPersonMail) {
                                    $tblMail = $tblToPersonMail->getTblMail();
                                    if ($tblMail) {
                                        $MailListSimple[$tblMail->getId()] = $tblMail->getAddress();
                                        if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                            $tblMailList[$tblPersonGuardian->getId()] = $tblMailList[$tblPersonGuardian->getId()].', '
                                                .$tblMail->getAddress();
                                        } else {
                                            $tblMailList[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName().' '.
                                                $tblPersonGuardian->getLastName().' ('
                                                .$tblMail->getAddress();
                                        }
                                    }
                                }
                                if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                    $tblMailList[$tblPersonGuardian->getId()] .= ')';
                                }
                            }
                        }
                    }
                }

                if (is_array($Item['PhoneGuardian']) && !empty($Item['PhoneGuardian'])) {
                    $Item['PhoneGuardian'] = implode('; ', $Item['PhoneGuardian']);
                }
                if (!empty($PhoneGuardianListSimple)) {
                    $Item['PhoneGuardianSimple'] = implode('; ', $PhoneGuardianListSimple);
                }

                if ($father !== null) {
                    $Item['FatherSalutation'] = $father->getSalutation();
                    $Item['FatherTitle'] = $father->getTitle();
                    $Item['FatherLastName'] = $father->getLastName();
                    $Item['FatherFirstName'] = $father->getFirstSecondName();
                    $Item['Father'] = $father->getFullName();
                }
                if ($mother !== null) {
                    $Item['MotherSalutation'] = $mother->getSalutation();
                    $Item['MotherTitle'] = $mother->getTitle();
                    $Item['MotherLastName'] = $mother->getLastName();
                    $Item['MotherFirstName'] = $mother->getFirstSecondName();
                    $Item['Mother'] = $mother->getFullName();
                }

                // Insert MailList
                if (!empty($tblMailList)) {
                    $Item['MailGuardian'] .= implode('<br>', $tblMailList);
                    $Item['ExcelMailGuardian'] = implode('; ', $tblMailList);
                }
                // Insert MailListSimple
                if (!empty($MailListSimple)) {
                    $Item['ExcelMailGuardianSimple'] = implode('; ', $MailListSimple);
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 0), "Anmeldedatum");
            $export->setValue($export->getCell(1, 0), "Aufnahmegespräch");
            $export->setValue($export->getCell(2, 0), "Schnuppertag");
            $export->setValue($export->getCell(3, 0), "Vorname");
            $export->setValue($export->getCell(4, 0), "Name");
            $export->setValue($export->getCell(5, 0), "Schuljahr");
            $export->setValue($export->getCell(6, 0), "Klassenstufe");
            $export->setValue($export->getCell(7, 0), "Schulart 1");
            $export->setValue($export->getCell(8, 0), "Schulart 2");
            $export->setValue($export->getCell(9, 0), "Straße");
            $export->setValue($export->getCell(10, 0), "Hausnummer");
            $export->setValue($export->getCell(11, 0), "PLZ");
            $export->setValue($export->getCell(12, 0), "Ort");
            $export->setValue($export->getCell(13, 0), "Ortsteil");
            $export->setValue($export->getCell(14, 0), "Geburtsdatum");
            $export->setValue($export->getCell(15, 0), "Geburtsort");
            $export->setValue($export->getCell(16, 0), "Staatsangeh.");
            $export->setValue($export->getCell(17, 0), "Bekenntnis");
            $export->setValue($export->getCell(18, 0), "Geschwister");
            $export->setValue($export->getCell(19, 0), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell(20, 0), "Titel Sorgeberechtigter 1");
            $export->setValue($export->getCell(21, 0), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell(22, 0), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell(23, 0), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell(24, 0), "Titel Sorgeberechtigter 1");
            $export->setValue($export->getCell(25, 0), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell(26, 0), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell(27, 0), "Telefon Interessent");
            $export->setValue($export->getCell(28, 0), "Telefon Interessent Kurz");
            $export->setValue($export->getCell(29, 0), "Telefon Sorgeberechtigte");
            $export->setValue($export->getCell(30, 0), "Telefon Sorgeberechtigte Kurz");
            $export->setValue($export->getCell(31, 0), "E-Mail Sorgeberechtigte");
            $export->setValue($export->getCell(32, 0), "E-Mail Sorgeberechtigte Kurz");
            $export->setValue($export->getCell(33, 0), "Bemerkung");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell(0, $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell(1, $Row), $PersonData['InterviewDate']);
                $export->setValue($export->getCell(2, $Row), $PersonData['TrialDate']);
                $export->setValue($export->getCell(3, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(4, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(5, $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell(6, $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell(7, $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell(8, $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell(9, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell(10, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell(11, $Row), $PersonData['Code']);
                $export->setValue($export->getCell(12, $Row), $PersonData['City']);
                $export->setValue($export->getCell(13, $Row), $PersonData['District']);
                $export->setValue($export->getCell(14, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(15, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell(16, $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell(17, $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell(18, $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell(19, $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell(20, $Row), $PersonData['FatherTitle']);
                $export->setValue($export->getCell(21, $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell(22, $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell(23, $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell(24, $Row), $PersonData['MotherTitle']);
                $export->setValue($export->getCell(25, $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell(26, $Row), $PersonData['MotherFirstName']);
                $export->setValue($export->getCell(27, $Row), $PersonData['Phone']);
                $export->setValue($export->getCell(28, $Row), $PersonData['PhoneSimple']);
                $export->setValue($export->getCell(29, $Row), $PersonData['PhoneGuardian']);
                $export->setValue($export->getCell(30, $Row), $PersonData['PhoneGuardianSimple']);
                $export->setValue($export->getCell(31, $Row), $PersonData['ExcelMailGuardian']);
                $export->setValue($export->getCell(32, $Row), $PersonData['ExcelMailGuardianSimple']);
                $export->setValue($export->getCell(33, $Row), $PersonData['RemarkExcel']);

                // WrapText
                $export->setStyle($export->getCell(33, $Row))->setWrapText();
                $Row++;
            }

            $export->setStyle($export->getCell(33, 0))->setColumnWidth(50);

            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createElectiveClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivision, &$count) {

                $Item['Number'] = $count++;
                $Item['Name'] = '';
                $Item['Birthday'] = '';
                $Item['Education'] = '';
                $Item['ForeignLanguage1'] = '';
                $Item['ForeignLanguage2'] = '';
                $Item['ForeignLanguage3'] = '';
                $Item['Profile'] = '';
                $Item['Orientation'] = '';
                $Item['Religion'] = '';
                $Item['Elective'] = '';
                $Item['ExcelElective'] = '';

                $Item['Name'] = $tblPerson->getLastFirstName();
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['Birthday'] = $tblCommon->getTblCommonBirthDates()->getBirthday();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblPerson->getId() == 15) {
                            echo new Code(print_r($tblStudentSubject, true));
                        }

                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject()) && ($tblDivisionLevel = $tblDivision->getTblLevel())) {

                            $Item['ForeignLanguage' . $i] = $tblSubject->getAcronym();

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                                && ($LevelFrom = Division::useService()->getLevelById($tblLevelFrom->getId())->getName())
                                && (is_numeric($LevelFrom)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() < $LevelFrom) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }
                            if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill()) &&
                                ($LevelTill = Division::useService()->getLevelById($tblLevelTill->getId())->getName())
                                && (is_numeric($LevelTill)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() > $LevelTill) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }

                            /* Use the following block to show the starting/ending division foreach foreign language */

                            //                            if (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))
                            //                                && ($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
                            //                                /** @var TblLevel $LevelFrom, $LevelTill */
                            //                                $Item['ForeignLanguage'.$i] .= ' (von Klasse ' . $LevelFrom->getName() . ' bis ' . $LevelTill->getName() . ')';
                            //                            } elseif (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))) {
                            //                            $Item['ForeignLanguage'.$i] .= ' (seit Klasse ' . $LevelFrom->getName() . ')';
                            //                            } elseif (($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
                            //                                $Item['ForeignLanguage'.$i] .= ' (bis Klasse ' . $LevelTill->getName() . ')';
                            //                            }

                        }
                    }
                    if ($tblPerson->getId() == 15) {
                        exit;
                    }

                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Religion'] = $tblSubject->getAcronym();
                    }

                    // Bildungsgang
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            if ($tblCourse) {
                                if ($tblCourse->getName() == 'Gymnasium') {
                                    $Item['Education'] = 'GY';
                                } elseif ($tblCourse->getName() == 'Hauptschule') {
                                    $Item['Education'] = 'HS';
                                } elseif ($tblCourse->getName() == 'Realschule') {
                                    $Item['Education'] = 'RS';
                                } else {
                                    $Item['Education'] = $tblCourse->getName();
                                }
                            }
                        }
                    }

                    // Wahlfach
                    $tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE')
                    );
                    $ElectiveList = array();
                    if ($tblStudentElectiveList) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if ($tblStudentElective->getServiceTblSubject()) {
                                $tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking();
                                if ($tblSubjectRanking) {
                                    $ElectiveList[$tblStudentElective->getTblStudentSubjectRanking()->getIdentifier()] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                } else {
                                    $ElectiveList[] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                }
                            }
                        }
                        if (!empty($ElectiveList)) {
                            ksort($ElectiveList);
                        }
                        if (!empty($ElectiveList)) {
                            $Item['Elective'] = implode('<br/>', $ElectiveList);
                            foreach ($ElectiveList as $Elective) {
                                $Item['ExcelElective'][] = $Elective;
                            }
                        }
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     * @param       $DivisionId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createElectiveClassListExcel($PersonList, $tblPersonList, $DivisionId)
    {

        // get PersonList sorted by GradeBook
        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $custodyList = array();
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                $tblDivisionCustodyList = Division::useService()->getCustodyAllByDivision($tblDivision);
                if ($tblDivisionCustodyList) {
                    foreach ($tblDivisionCustodyList as $tblPerson) {
                        $custodyList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $teacherList = array();
                $tblDivisionTeacherAll = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblDivisionTeacherAll) {
                    foreach ($tblDivisionTeacherAll as $tblPerson) {
                        $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontBold();
                $export->setValue($export->getCell(0, 0),
                    "Klasse " . $tblDivision->getDisplayName() . (empty($teacherList) ? '' : ' ' . implode(', ',
                            $teacherList)));
            }

            // Header
            $export->setValue($export->getCell(0, 1), "Name");
            $export->setValue($export->getCell(1, 1), "Geb.-Datum");
            $export->setValue($export->getCell(2, 1), "Bildungsgang");
            $export->setValue($export->getCell(3, 1), "FS 1");
            $export->setValue($export->getCell(4, 1), "FS 2");
            $export->setValue($export->getCell(5, 1), "FS 3");
            $export->setValue($export->getCell(6, 1), "Profil");
            $export->setValue($export->getCell(7, 1), "Neigungskurs");
            $export->setValue($export->getCell(8, 1), "Religion");
            $export->setValue($export->getCell(9, 1), "Wahlfächer");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, 1))->setFontBold();

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $ElectiveRow = $Row;

                $export->setValue($export->getCell(0, $Row), $PersonData['Name']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(2, $Row), $PersonData['Education']);
                $export->setValue($export->getCell(3, $Row), $PersonData['ForeignLanguage1']);
                $export->setValue($export->getCell(4, $Row), $PersonData['ForeignLanguage2']);
                $export->setValue($export->getCell(5, $Row), $PersonData['ForeignLanguage3']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Profile']);
                $export->setValue($export->getCell(7, $Row), $PersonData['Orientation']);
                $export->setValue($export->getCell(8, $Row), $PersonData['Religion']);

                if (isset($PersonData['ExcelElective']) && !empty($PersonData['ExcelElective'])) {
                    foreach ($PersonData['ExcelElective'] as $Elective) {
                        $export->setValue($export->getCell(9, $ElectiveRow), $Elective);
                        $ElectiveRow++;
                    }
                }

                $Row++;
                if ($ElectiveRow > $Row) {
                    $Row = $ElectiveRow;
                }

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $Row - 1), $export->getCell(9, $Row - 1))->setBorderBottom();
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, $Row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, $Row - 1))->setBorderOutline();

            // Personenanzahl
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(5, 0), $export->getCell(6, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(8, 0), $export->getCell(7, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(9, 0), $export->getCell(7, $Row))->setColumnWidth(8);
            //
            //            // Schriftgröße
            //            $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontSize(12)
            //                ->setFontBold()
            //                ->mergeCells();
            //            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $Row))->setFontSize(10);
            //
            //            // Spalten zentriert
            //            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setAlignmentCenter();
            //            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setAlignmentCenter();
            //            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setAlignmentCenter();
            //            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setAlignmentCenter();
            //            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setAlignmentCenter();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param $Person
     * @param $Year
     * @param $Division
     * @param $PersonGroup
     *
     * @return array
     */
    public function getStudentFilterResult($Person, $Year, $Division, $PersonGroup)
    {

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = array();

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });

            $FilterYear = array_filter($Year);
            if (isset($Person) && $Person) {
                array_walk($Person, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterPerson = array_filter($Person);
            } else {
                $FilterPerson = array();
            }
            if (isset($PersonGroup) && $PersonGroup) {
                array_walk($PersonGroup, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterPersonGroup = array_filter($PersonGroup);
            } else {
                $FilterPersonGroup = array();
            }
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterDivision = array_filter($Division);
            } else {
                $FilterDivision = array();
            }

            $Result = $Pile->searchPile(array(
                0 => $FilterPerson,
                1 => $FilterPersonGroup,
                2 => $FilterDivision,
                3 => $FilterYear
            ));
        }

        return $Result;
    }

    /**
     * @param array $Result
     * @param null  $Option
     * @param null  $PersonGroup
     *
     * @return array
     */
    public function getStudentTableContent($Result, $Option = null, $PersonGroup = null)
    {

        $SearchResult = array();
        if (!empty($Result)) {

            $PersonGroupName = '';
            if($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID] != '0' && ($tblPersonGroup = Group::useService()->getGroupById($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID]))){
                $PersonGroupName = $tblPersonGroup->getName();
            }


            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[0]->__toArray();
//                /** @var ViewPeopleGroupMember $DataGroup */
//                $DataGroup = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                /** @var ViewYear $Year */
                $Year = $Row[3]->__toArray();

                $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                // ignor existing Accounts (By Person)
                if ($tblPerson) {
                    /** @noinspection PhpUndefinedFieldInspection */

                    $DataPerson['PersonGroup'] = $PersonGroupName;

                    $DataPerson['Division'] = '';
                    if (($tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']))) {
                        // jahrgangsübergreifende Klassen ignorieren
                        if (($tblLevel = $tblDivision->getTblLevel()) && $tblLevel->getIsChecked()) {
                            continue;
                        }
                        // inaktive ignorieren
                        if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson))
                            && ($tblDivisionStudent->isInActive())
                        ) {
                            continue;
                        }

                        /** @var TblDivision $tblDivision */
                        $DataPerson['Division'] = $tblDivision->getDisplayName();
                    }

                    $DataPerson['StudentNumber'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }

                    $DataPerson['FirstName'] = '';
                    $DataPerson['LastName'] = '';
                    $DataPerson['FirstName'] = $tblPerson->getFirstName();
                    if (!empty($tblPerson->getSecondName())) {
                        $DataPerson['FirstName'] .= ' ' . $tblPerson->getSecondName();
                    }
                    $DataPerson['LastName'] = $tblPerson->getLastName();

                    $DataPerson['Gender'] = '';
                    $DataPerson['Birthday'] = '';
                    $DataPerson['BirthPlace'] = '';
                    $DataPerson['Religion'] = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                            if (!empty($tblCommonBirthDates->getBirthday())) {
                                $DataPerson['Birthday'] = $tblCommonBirthDates->getBirthday();
                            }
                            if (!empty($tblCommonBirthDates->getBirthplace())) {
                                $DataPerson['BirthPlace'] = $tblCommonBirthDates->getBirthplace();
                            }
                            if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                                $DataPerson['Gender'] = $tblCommonGender->getName();
                            }
                        }
                        if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                            $DataPerson['Religion'] = $tblCommonInformation->getDenomination();
                        }
                    }

                    $DataPerson['Address'] = '';
                    $DataPerson['Street'] = '';
                    $DataPerson['HouseNumber'] = '';
                    $DataPerson['CityCode'] = '';
                    $DataPerson['City'] = '';
                    $DataPerson['District'] = '';
                    if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                        $DataPerson['Address'] = $tblAddress->getGuiString();
                        if (($tblCity = $tblAddress->getTblCity())) {
                            $DataPerson['Street'] = $tblAddress->getStreetName();
                            $DataPerson['HouseNumber'] = $tblAddress->getStreetNumber();
                            $DataPerson['CityCode'] = $tblCity->getCode();
                            $DataPerson['City'] = $tblCity->getName();
                            $DataPerson['District'] = $tblCity->getDisplayDistrict();
                        }
                    }

                    $DataPerson['Insurance'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                            $DataPerson['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                        }
                    }

                    $DataPerson['PhoneFixedPrivate'] = '';
                    $DataPerson['PhoneFixedWork'] = '';
                    $DataPerson['PhoneFixedEmergency'] = '';
                    $DataPerson['PhoneMobilePrivate'] = '';
                    $DataPerson['PhoneMobileWork'] = '';
                    $DataPerson['PhoneMobileEmergency'] = '';
                    if (($tblPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                        foreach ($tblPhoneAll as $tblToPerson) {
                            /** @var TblToPerson $tblToPerson */
                            if (($tblPhoneType = $tblToPerson->getTblType())
                            && ($PhoneDescription = $tblPhoneType->getDescription())
                            && ($PhoneName = $tblPhoneType->getName())
                            && ($tblPhone = $tblToPerson->getTblPhone())) {
                                if ($PhoneDescription == 'Festnetz') {
                                    switch ($PhoneName) {
                                        case 'Privat':
                                            if (empty($DataPerson['PhoneFixedPrivate'])) {
                                                $DataPerson['PhoneFixedPrivate'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedPrivate'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneFixedWork'])) {
                                                $DataPerson['PhoneFixedWork'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedWork'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneFixedEmergency'])) {
                                                $DataPerson['PhoneFixedEmergency'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : 'TEst');
                                            } else {
                                                $DataPerson['PhoneFixedEmergency'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                } elseif ($PhoneDescription == 'Mobil') {
                                    switch ($PhoneName) {
                                        case 'Privat':
                                            if (empty($DataPerson['PhoneMobilePrivate'])) {
                                                $DataPerson['PhoneMobilePrivate'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobilePrivate'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneMobileWork'])) {
                                                $DataPerson['PhoneMobileWork'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileWork'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneMobileEmergency'])) {
                                                $DataPerson['PhoneMobileEmergency'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileEmergency'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    $DataPerson['Sibling_1'] = '';
                    $DataPerson['Sibling_2'] = '';
                    $DataPerson['Sibling_3'] = '';

                    $DataPerson['Custody_1_Salutation'] = '';
                    $DataPerson['Custody_1_Title'] = '';
                    $DataPerson['Custody_1_FirstName'] = '';
                    $DataPerson['Custody_1_LastName'] = '';
                    $DataPerson['Custody_1_Address'] = '';
                    $DataPerson['Custody_1_Street'] = '';
                    $DataPerson['Custody_1_HouseNumber'] = '';
                    $DataPerson['Custody_1_CityCode'] = '';
                    $DataPerson['Custody_1_City'] = '';
                    $DataPerson['Custody_1_District'] = '';
                    $DataPerson['Custody_1_PhoneFixedPrivate'] = '';
                    $DataPerson['Custody_1_PhoneFixedWork'] = '';
                    $DataPerson['Custody_1_PhoneFixedEmergency'] = '';
                    $DataPerson['Custody_1_PhoneMobilePrivate'] = '';
                    $DataPerson['Custody_1_PhoneMobileWork'] = '';
                    $DataPerson['Custody_1_PhoneMobileEmergency'] = '';
                    $DataPerson['Custody_1_Mail_Private'] = '';
                    $DataPerson['Custody_1_Mail_Work'] = '';

                    $DataPerson['Custody_2_Salutation'] = '';
                    $DataPerson['Custody_2_Title'] = '';
                    $DataPerson['Custody_2_FirstName'] = '';
                    $DataPerson['Custody_2_LastName'] = '';
                    $DataPerson['Custody_2_Address'] = '';
                    $DataPerson['Custody_2_Street'] = '';
                    $DataPerson['Custody_2_HouseNumber'] = '';
                    $DataPerson['Custody_2_CityCode'] = '';
                    $DataPerson['Custody_2_City'] = '';
                    $DataPerson['Custody_2_District'] = '';
                    $DataPerson['Custody_2_PhoneFixedPrivate'] = '';
                    $DataPerson['Custody_2_PhoneFixedWork'] = '';
                    $DataPerson['Custody_2_PhoneFixedEmergency'] = '';
                    $DataPerson['Custody_2_PhoneMobilePrivate'] = '';
                    $DataPerson['Custody_2_PhoneMobileWork'] = '';
                    $DataPerson['Custody_2_PhoneMobileEmergency'] = '';
                    $DataPerson['Custody_2_Mail_Private'] = '';
                    $DataPerson['Custody_2_Mail_Work'] = '';

                    $DataPerson['Custody_3_Salutation'] = '';
                    $DataPerson['Custody_3_Title'] = '';
                    $DataPerson['Custody_3_FirstName'] = '';
                    $DataPerson['Custody_3_LastName'] = '';
                    $DataPerson['Custody_3_Address'] = '';
                    $DataPerson['Custody_3_Street'] = '';
                    $DataPerson['Custody_3_HouseNumber'] = '';
                    $DataPerson['Custody_3_CityCode'] = '';
                    $DataPerson['Custody_3_City'] = '';
                    $DataPerson['Custody_3_District'] = '';
                    $DataPerson['Custody_3_PhoneFixedPrivate'] = '';
                    $DataPerson['Custody_3_PhoneFixedWork'] = '';
                    $DataPerson['Custody_3_PhoneFixedEmergency'] = '';
                    $DataPerson['Custody_3_PhoneMobilePrivate'] = '';
                    $DataPerson['Custody_3_PhoneMobileWork'] = '';
                    $DataPerson['Custody_3_PhoneMobileEmergency'] = '';
                    $DataPerson['Custody_3_Mail_Private'] = '';
                    $DataPerson['Custody_3_Mail_Work'] = '';

                    if (($tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                        foreach ($tblRelationshipAll as $tblToPerson) {
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $tblToPerson */
                            if (($tblType = $tblToPerson->getTblType())) {
                                if ($tblType->getName() == 'Geschwisterkind') {
                                    if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom()) && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())) {
                                        if ($tblPersonFrom->getId() !== $tblPerson->getId()) {
                                            $tblPersonSibling = $tblPersonFrom;
                                        } elseif ($tblPersonTo->getId() !== $tblPerson->getId()) {
                                            $tblPersonSibling = $tblPersonTo;
                                        }
                                        if (!empty($tblPersonSibling)) {
                                            $SiblingString = $tblPersonSibling->getLastName() . ', ' . $tblPersonSibling->getFirstName();
                                            if (!empty($tblPersonSibling->getSecondName())) {
                                                $SiblingString .= ' ' . $tblPersonSibling->getSecondName();
                                            }
                                            if (($tblYear = Term::useService()->getYearById($Year[ViewYear::TBL_YEAR_ID]))) {
                                                if (($SiblingDivision = Student::useService()->getMainDivisionByPersonAndYear($tblPersonSibling, $tblYear))) {
                                                    $SiblingString .= ' (' . $SiblingDivision->getDisplayName() . ')';
                                                } else {
                                                    if ($Option) {
                                                        $SiblingString .= ' (Ehemalig)';
                                                    } else {
                                                        $SiblingString = '';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (empty($DataPerson['Sibling_1']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_1'] = $SiblingString;
                                    } elseif (empty($DataPerson['Sibling_2']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_2'] = $SiblingString;
                                    } elseif (empty($DataPerson['Sibling_3']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_3'] = $SiblingString;
                                    }
                                }
                            }
                        }
                    }
                    $tblRelationshipCustodyList = array();
                    if(($tblType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))){
                        $tblRelationshipCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                            $tblType);
                    }

                    if(!empty($tblRelationshipCustodyList)){
                        for($i = 1; $i <= 3; $i++) {
                            $tblRelationshipCustody = false;
                            foreach($tblRelationshipCustodyList as $tblRelationshipCustodyControl) {
                                if($tblRelationshipCustodyControl->getRanking() == $i || $i == 1
                                    && $tblRelationshipCustodyControl->getRanking() === null){
                                    $tblRelationshipCustody = $tblRelationshipCustodyControl;
                                    break;
                                }
                            }

                            if($tblRelationshipCustody
                                && ($tblPersonCustody = $tblRelationshipCustody->getServiceTblPersonFrom())){
                                $DataPerson['Custody_'.$i.'_Salutation'] = $tblPersonCustody->getSalutation();
                                $DataPerson['Custody_'.$i.'_Title'] = $tblPersonCustody->getTitle();
                                $DataPerson['Custody_'.$i.'_FirstName'] = $tblPersonCustody->getFirstName();
                                if($tblPersonCustody->getSecondName()){
                                    $DataPerson['Custody_'.$i.'_FirstName'] .= ' '.$tblPersonCustody->getSecondName();
                                }
                                $DataPerson['Custody_'.$i.'_LastName'] = $tblPersonCustody->getLastName();

                                if(($tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody))){
                                    $DataPerson['Custody_'.$i.'_Address'] = $tblAddressCustody->getGuiString();
                                    if(($tblCityCustody = $tblAddressCustody->getTblCity())){
                                        $DataPerson['Custody_'.$i.'_Street'] = $tblAddressCustody->getStreetName();
                                        $DataPerson['Custody_'.$i.'_HouseNumber'] = $tblAddressCustody->getStreetNumber();
                                        $DataPerson['Custody_'.$i.'_CityCode'] = $tblCityCustody->getCode();
                                        $DataPerson['Custody_'.$i.'_City'] = $tblCityCustody->getName();
                                        $DataPerson['Custody_'.$i.'_District'] = $tblCityCustody->getDisplayDistrict();
                                    }
                                }

                                if(($tblPhoneAllCustody = Phone::useService()->getPhoneAllByPerson($tblPersonCustody))){
                                    foreach($tblPhoneAllCustody as $tblToPersonCustody) {
                                        /** @var TblToPerson $tblToPersonCustody */
                                        if(($tblPhoneTypeCustody = $tblToPersonCustody->getTblType())
                                            && ($PhoneDescriptionCustody = $tblPhoneTypeCustody->getDescription())
                                            && ($PhoneNameCustody = $tblPhoneTypeCustody->getName())
                                            && ($tblPhoneCustody = $tblToPersonCustody->getTblPhone())){
                                            if($PhoneDescriptionCustody == 'Festnetz'){
                                                switch($PhoneNameCustody) {
                                                    case 'Privat':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneFixedPrivate'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneFixedPrivate'] .= ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneFixedPrivate'] = $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                    case 'Geschäftlich':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneFixedWork'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneFixedWork'] .= ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneFixedWork'] .= $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                    case 'Notfall':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneFixedEmergency'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneFixedEmergency'] .= ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneFixedEmergency'] .= $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                }
                                            } elseif($PhoneDescriptionCustody == 'Mobil') {
                                                switch($PhoneNameCustody) {
                                                    case 'Privat':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneMobilePrivate'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneMobilePrivate'] .= ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneMobilePrivate'] .= $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                    case 'Geschäftlich':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneMobileWork'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneMobileWork'] .= ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneMobileWork'] .= $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                    case 'Notfall':
                                                        if(!empty($DataPerson['Custody_'.$i.'_PhoneMobileEmergency'])){
                                                            $DataPerson['Custody_'.$i.'_PhoneMobileEmergency'] = ', ';
                                                        }
                                                        $DataPerson['Custody_'.$i.'_PhoneMobileEmergency'] .= $tblPhoneCustody->getNumber()
                                                            .(!empty($tblToPersonCustody->getRemark()) ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                                        break;
                                                }
                                            }
                                        }
                                    }
                                }
                                if(($tblMailAllCustody = Mail::useService()->getMailAllByPerson($tblPersonCustody))){
                                    foreach($tblMailAllCustody as $tblToPersonMailCustody) {
                                        if(($tblTypeMailCustody = $tblToPersonMailCustody->getTblType())
                                            && ($tblMailCustody = $tblToPersonMailCustody->getTblMail())){
                                            if($tblTypeMailCustody->getName() == 'Privat'){
                                                $DataPerson['Custody_'.$i.'_Mail_Private'] = $tblMailCustody->getAddress();
                                            } elseif($tblTypeMailCustody->getName() == 'Geschäftlich') {
                                                $DataPerson['Custody_'.$i.'_Mail_Work'] = $tblMailCustody->getAddress();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // ignore duplicated Person
                    if ($DataPerson['TblPerson_Id']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $Option
     * @param null $PersonGroup
     *
     * @return FilePointer
     */
    public function createMetaDataComparisonExcel($Person = null, $Year = null, $Division = null, $Option = null, $PersonGroup = null)
    {

        $Result = $this->getStudentFilterResult($Person, $Year, $Division, $PersonGroup);

        $TableContent = $this->getStudentTableContent($Result, $Option, $PersonGroup);

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $PersonGroupName = '';
        if($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID] != '0'
            && $tblPersonGroup = Group::useService()->getGroupById($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID])){
            $PersonGroupName = $tblPersonGroup->getName();
        }

        $Row = 0;
        $Column = 0;

        $export->setValue($export->getCell($Column++, $Row), "Klasse");
        $export->setValue($export->getCell($Column++, $Row), "Schülernummer");
        $export->setValue($export->getCell($Column++, $Row), "Vorname");
        $export->setValue($export->getCell($Column++, $Row), "Nachname");
        $export->setValue($export->getCell($Column++, $Row), "Geschlecht");
        $export->setValue($export->getCell($Column++, $Row), "Geburtstag");
        $export->setValue($export->getCell($Column++, $Row), "Geburtsort");
        $export->setValue($export->getCell($Column++, $Row), "Straße");
        $export->setValue($export->getCell($Column++, $Row), "Hausnummer");
        $export->setValue($export->getCell($Column++, $Row), "PLZ");
        $export->setValue($export->getCell($Column++, $Row), "Wohnort");
        $export->setValue($export->getCell($Column++, $Row), "Ortsteil");
        $export->setValue($export->getCell($Column++, $Row), "Krankenkasse");
        $export->setValue($export->getCell($Column++, $Row), "Religion");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Privat)");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Geschäftl.)");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Notfall)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Privat)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Geschäftl.)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Notfall)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Notfall)");
        if($PersonGroupName){
            $export->setValue($export->getCell($Column++, $Row), "Personengruppe");
        }

        $export->setValue($export->getCell($Column++, $Row), "Geschwister1");
        $export->setValue($export->getCell($Column++, $Row), "Geschwister2");
        $export->setValue($export->getCell($Column++, $Row), "Geschwister3");

        // 3 Sorgeberechtigte
        for($i = 1; $i <= 3; $i++){
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Anrede");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Titel");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Nachname");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Straße");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Hausnummer");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." PLZ");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Wohnort");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Ortsteil");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Festnetz (Privat)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Festnetz (Geschäftl.)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Festnetz (Notfall)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Mobil (Privat)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Mobil (Geschäftl.)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Mobil (Notfall)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Mail (Privat)");
            $export->setValue($export->getCell($Column++, $Row), "Sorg".$i." Mail (Geschäftl.)");
        }

        foreach ($TableContent as $PersonData) {
            $Row++;
            $Column = 0;

            $export->setValue($export->getCell($Column++, $Row), $PersonData['Division']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['FirstName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['LastName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Gender']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['BirthPlace']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Street']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['HouseNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['CityCode']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['City']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['District']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Insurance']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Religion']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedEmergency']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobilePrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileEmergency']);
            if($PersonGroupName){
                $export->setValue($export->getCell($Column++, $Row), $PersonGroupName);
            }

            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_1']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_2']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_3']);

            // 3 Sorgeberechtigte
            for($j = 1; $j <= 3; $j++) {
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_Salutation']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_Title']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_LastName']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_Street']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_HouseNumber']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_CityCode']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_City']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_District']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneFixedPrivate']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneFixedWork']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneFixedEmergency']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneMobilePrivate']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneMobileWork']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_PhoneMobileEmergency']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_Mail_Private']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Custody_'.$j.'_Mail_Work']);
            }
        }

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createMedicalRecordClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['StudentNumber'] = '';
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Disease'] = '';
                $Item['Medication'] = '';
                $Item['AttendingDoctor'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        $Item['Birthday'] = $tblBirhdates->getBirthday();
                    }
                }

                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    if(($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                        $Item['Disease'] = $tblMedicalRecord->getDisease();
                        $Item['Medication'] = $tblMedicalRecord->getMedication();
                        $Item['AttendingDoctor'] = $tblMedicalRecord->getAttendingDoctor();
                    }
                }

                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                if ($tblAddress) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createMedicalRecordClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("4", "0"), "Krankheiten/Allergie");
            $export->setValue($export->getCell("5", "0"), "Medikamente");
            $export->setValue($export->getCell("6", "0"), "Behandelnder Arzt");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Disease']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Medication']);
                $export->setValue($export->getCell("6", $Row), $PersonData['AttendingDoctor']);
                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createAgreementClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['StudentNumber'] = '';
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Name1'] = 'Nein';    // Schulschriften
                $Item['Name2'] = 'Nein';    // Veröffentlichungen
                $Item['Name3'] = 'Nein';    // Internetpräsenz
                $Item['Name4'] = 'Nein';    // Facebookseite
                $Item['Name5'] = 'Nein';    // Druckpresse
                $Item['Name6'] = 'Nein';    // Ton/Video/Film
                $Item['Name7'] = 'Nein';    // Werbung in eigener Sache
                $Item['Picture1'] = 'Nein'; // Schulschriften
                $Item['Picture2'] = 'Nein'; // Veröffentlichungen
                $Item['Picture3'] = 'Nein'; // Internetpräsenz
                $Item['Picture4'] = 'Nein'; // Facebookseite
                $Item['Picture5'] = 'Nein'; // Druckpresse
                $Item['Picture6'] = 'Nein'; // Ton/Video/Film
                $Item['Picture7'] = 'Nein'; // Werbung in eigener Sache

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        $Item['Birthday'] = $tblBirhdates->getBirthday();
                    }
                }

                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    if($tblStudentAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent)){
                        foreach($tblStudentAgreementList as $tblStudentAgreement){
                            $tblStudentAgreementType = $tblStudentAgreement->getTblStudentAgreementType();
                            $tblStudentAgreementCategory = $tblStudentAgreementType->getTblStudentAgreementCategory();
                            if($tblStudentAgreementType && $tblStudentAgreementCategory){
                                if($tblStudentAgreementCategory->getName() == 'Foto des Schülers') {
                                    switch($tblStudentAgreement->getTblStudentAgreementType()->getName()){
                                        case 'in Schulschriften':
                                            $Item['Picture1'] = 'Ja';
                                        break;
                                        case 'in Veröffentlichungen':
                                            $Item['Picture2'] = 'Ja';
                                        break;
                                        case 'auf Internetpräsenz':
                                            $Item['Picture3'] = 'Ja';
                                        break;
                                        case 'auf Facebookseite':
                                            $Item['Picture4'] = 'Ja';
                                        break;
                                        case 'für Druckpresse':
                                            $Item['Picture5'] = 'Ja';
                                        break;
                                        case 'durch Ton/Video/Film':
                                            $Item['Picture6'] = 'Ja';
                                        break;
                                        case 'für Werbung in eigener Sache':
                                            $Item['Picture7'] = 'Ja';
                                        break;
                                    }
                                } else {
                                    switch($tblStudentAgreement->getTblStudentAgreementType()->getName()){
                                        case 'in Schulschriften':
                                            $Item['Name1'] = 'Ja';
                                        break;
                                        case 'in Veröffentlichungen':
                                            $Item['Name2'] = 'Ja';
                                        break;
                                        case 'auf Internetpräsenz':
                                            $Item['Name3'] = 'Ja';
                                        break;
                                        case 'auf Facebookseite':
                                            $Item['Name4'] = 'Ja';
                                        break;
                                        case 'für Druckpresse':
                                            $Item['Name5'] = 'Ja';
                                        break;
                                        case 'durch Ton/Video/Film':
                                            $Item['Name6'] = 'Ja';
                                        break;
                                        case 'für Werbung in eigener Sache':
                                            $Item['Name7'] = 'Ja';
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                if ($tblAddress) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createAgreementClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Column = 0;
            $Row = 1;
            $export->setValue($export->getCell($Column++, $Row), "Schülernummer");
            $export->setValue($export->getCell($Column++, $Row), "Name, Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Anschrift");
            $export->setValue($export->getCell($Column++, $Row), "Geburtsdatum");
            $export->setValue($export->getCell($Column, $Row - 1), "Namentliche Erwähnung des Schülers");
            //Header für Namentliche Erwähnung
            $export->setStyle($export->getCell($Column, $Row - 1), $export->getCell($Column + 6, $Row - 1))->mergeCells();

            $export->setValue($export->getCell($Column++, $Row), "Schulschriften");
            $export->setValue($export->getCell($Column++, $Row), "Veröffentlichungen");
            $export->setValue($export->getCell($Column++, $Row), "Internetpräsenz");
            $export->setValue($export->getCell($Column++, $Row), "Facebookseite");
            $export->setValue($export->getCell($Column++, $Row), "Druckpresse");
            $export->setValue($export->getCell($Column++, $Row), "Ton/Video/Film");
            $export->setValue($export->getCell($Column++, $Row), "Werbung in eigener Sache");
            $export->setValue($export->getCell($Column, $Row - 1), "Foto des Schülers");
            //Header für Foto
            $export->setStyle($export->getCell($Column, $Row - 1), $export->getCell($Column + 6, $Row - 1))->mergeCells();

            $export->setValue($export->getCell($Column++, $Row), "Schulschriften");
            $export->setValue($export->getCell($Column++, $Row), "Veröffentlichungen");
            $export->setValue($export->getCell($Column++, $Row), "Internetpräsenz");
            $export->setValue($export->getCell($Column++, $Row), "Facebookseite");
            $export->setValue($export->getCell($Column++, $Row), "Druckpresse");
            $export->setValue($export->getCell($Column++, $Row), "Ton/Video/Film");
            $export->setValue($export->getCell($Column, $Row), "Werbung in eigener Sache");

            $Row = 2;

            foreach ($PersonList as $PersonData) {
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Address']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name1']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name2']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name3']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name4']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name5']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name6']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name7']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture1']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture2']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture3']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture4']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture5']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Picture6']);
                $export->setValue($export->getCell($Column, $Row), $PersonData['Picture7']);
                $Row++;
            }

            // Style Test
//            $export->setStyle($export->getCell(4, 0), $export->getCell(10, $Row -1))->setBorderOutline();
//            $export->setStyle($export->getCell(4, 2), $export->getCell(10, $Row -1))->setBorderVertical();
//            $export->setStyle($export->getCell(11, 0), $export->getCell(17, $Row -1))->setBorderOutline();
//            $export->setStyle($export->getCell(11, 2), $export->getCell(17, $Row -1))->setBorderVertical();

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param \DateTime $dateTime
     * @param null $Type
     * @param string $DivisionName
     *
     * @return bool|FilePointer
     */
    public function createAbsenceListExcel(\DateTime $dateTime, $Type = null, $DivisionName = '')
    {

        if ($Type != null) {
            $tblType = Type::useService()->getTypeById($Type);
        } else {
            $tblType = false;
        }

        if ($DivisionName != '') {
            $divisionList = Division::useService()->getDivisionAllByName($DivisionName);
            if (!empty($divisionList)) {
                $absenceList = Absence::useService()->getAbsenceAllByDay($dateTime, $tblType ? $tblType : null, $divisionList);
            } else {
                $absenceList = array();
            }
        } else {
            $absenceList = Absence::useService()->getAbsenceAllByDay($dateTime, $tblType ? $tblType : null);
        }

        if (!empty($absenceList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell(0, 0), $dateTime->format('d.m.Y'));

            $column = 0;
            $row = 1;
            $export->setValue($export->getCell($column++, $row), "Schulart");
            $export->setValue($export->getCell($column++, $row), "Klasse");
            $export->setValue($export->getCell($column++, $row), "Schüler");
            $export->setValue($export->getCell($column++, $row), "Zeitraum");
            $export->setValue($export->getCell($column++, $row), "Status");
            $export->setValue($export->getCell($column, $row), "Bemerkung");

            // header bold
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();

            $row++;

            foreach ($absenceList as $absence) {
                $column = 0;

                $export->setValue($export->getCell($column++, $row), $absence['Type']);
                $export->setValue($export->getCell($column++, $row), $absence['Division']);
                $export->setValue($export->getCell($column++, $row), $absence['Person']);
                $export->setValue($export->getCell($column++, $row), $absence['DateSpan']);
                $export->setValue($export->getCell($column++, $row), $absence['Status']);
                $export->setValue($export->getCell($column, $row), $absence['Remark']);

                $row++;
            }

            // Spaltenbreite
            $column = 0;
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(10);
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(22);
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 1), $export->getCell($column, $row))->setColumnWidth(25);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}
