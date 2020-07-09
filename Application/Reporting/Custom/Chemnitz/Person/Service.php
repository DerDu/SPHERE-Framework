<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Service extends Extension
{

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

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Address'] = $Item['District'] = '';
                $Item['Denomination'] = $Item['Birthday'] = $Item['Birthplace'] = '';
                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
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
                        }
                    }
                }

                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Father'] = $father !== null ? $father->getFirstSecondName() : '';
                $Item['Mother'] = $mother !== null ? $mother->getFirstSecondName() : '';

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['City'] = $tblAddress->getTblCity()->getCode()
                        .' '.$tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();

                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
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
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("2", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("3", "0"), "Name");
            $export->setValue($export->getCell("4", "0"), "Konfession");
            $export->setValue($export->getCell("5", "0"), "Straße");
            $export->setValue($export->getCell("6", "0"), "Hausnr.");
            $export->setValue($export->getCell("7", "0"), "PLZ Ort");
            $export->setValue($export->getCell("8", "0"), "Ortsteil");
            $export->setValue($export->getCell("9", "0"), "Schüler");
            $export->setValue($export->getCell("10", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("11", "0"), "Geburtsort");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Father']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Mother']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("5", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("7", $Row), $PersonData['City']);
                $export->setValue($export->getCell("8", $Row), $PersonData['District']);
                $export->setValue($export->getCell("9", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("10", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Birthplace']);

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
     * @return array
     */
    public function createStaffList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Title'] = $tblPerson->getTitle();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Code'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Division'] = '';
                $Item['Phone1'] = $Item['Phone2'] = $Item['Mail'] = '';

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
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

                    $Item['Address'] = $tblAddress->getGuiString();
                }

                // Todo JohK Unterbereich ermitteln über Gruppen
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    if (count($phoneList) > 0) {
                        $Item['Phone1'] = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    if (count($phoneList) > 1) {
                        $Item['Phone2'] = $phoneList[1]->getTblPhone()->getNumber();
                    }
                }
                $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($mailList) {
                    $Item['Mail'] = $mailList[0]->getTblMail()->getAddress();
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
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createStaffListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Titel");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Name");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Unterbereich");
            $export->setValue($export->getCell("6", "0"), "Straße");
            $export->setValue($export->getCell("7", "0"), "Hausnr.");
            $export->setValue($export->getCell("8", "0"), "PLZ");
            $export->setValue($export->getCell("9", "0"), "Ort");
            $export->setValue($export->getCell("10", "0"), "Ortsteil");
            $export->setValue($export->getCell("11", "0"), "Telefon 1");
            $export->setValue($export->getCell("12", "0"), "Telefon 2");
            $export->setValue($export->getCell("13", "0"), "Mail");

            $Row = 1;
            foreach ($PersonList as $PersonData) {
                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Title']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Division']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("9", $Row), $PersonData['City']);
                $export->setValue($export->getCell("10", $Row), $PersonData['District']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Phone1']);
                $export->setValue($export->getCell("12", $Row), $PersonData['Phone2']);
                $export->setValue($export->getCell("13", $Row), $PersonData['Mail']);

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
     * @return array
     */
    public function createSchoolFeeList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                //Sortierung
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['LastName'] = $tblPerson->getLastName();

                $Item['DebtorNumber'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['FatherSalutation'] = $Item['FatherTitle'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherTitle'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';
                $Item['Reply'] = $Item['Records'] = $Item['LastSchoolFee'] = $Item['Remarks'] = '';
                // Does'nt exist (rebuild Fakturierung)
//                if (( $tblDebtorList = Banking::useService()->getDebtorByPerson($tblPerson) )) {
//                    foreach ($tblDebtorList as $tblDebtor) {
//                        if ($Item['DebtorNumber'] === '') {
//                            $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
//                        } else {
//                            $Item['DebtorNumber'] = ' '.$tblDebtor->getDebtorNumber();
//                        }
//                    }
//                }

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

                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                                // Does'nt exist (rebuild Fakturierung)
//                            } else {
//                                if ($father === null) {
//                                    $father = $guardian->getServiceTblPersonFrom();
//                                    if (( $tblDebtorList = Banking::useService()->getDebtorByPerson($father) )) {
//
//                                        foreach ($tblDebtorList as $tblDebtor) {
//                                            if ($Item['DebtorNumber'] === '') {
//                                                $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
//                                            } else {
//                                                $Item['DebtorNumber'] = ' '.$tblDebtor->getDebtorNumber();
//                                            }
//                                        }
//                                    }
//                                } else {
//                                    $mother = $guardian->getServiceTblPersonFrom();
//                                    if (( $tblDebtorList = Banking::useService()->getDebtorByPerson($mother) )) {
//                                        foreach ($tblDebtorList as $tblDebtor) {
//                                            if ($Item['DebtorNumber'] === '') {
//                                                $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
//                                            } else {
//                                                $Item['DebtorNumber'] = ' '.$tblDebtor->getDebtorNumber();
//                                            }
//                                        }
//                                    }
//                                }
                            }
                        }
                    }
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

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createSchoolFeeListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());      //ToDO Header kürzen?
            $export->setValue($export->getCell("0", "0"), "Deb.-Nr.");
            $export->setValue($export->getCell("1", "0"), "Bescheid geschickt");
            $export->setValue($export->getCell("2", "0"), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell("3", "0"), "Titel Sorgeberechtigter 1");
            $export->setValue($export->getCell("4", "0"), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell("5", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("6", "0"), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell("7", "0"), "Titel Sorgeberechtigter 2");
            $export->setValue($export->getCell("8", "0"), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell("9", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("10", "0"), "Unterlagen eingereicht");
            $export->setValue($export->getCell("11", "0"), "SG Vorjahr");
            $export->setValue($export->getCell("12", "0"), "1.Kind");
            $export->setValue($export->getCell("13", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("14", "0"), "Klasse");
            $export->setValue($export->getCell("15", "0"), "2.Kind");
            $export->setValue($export->getCell("16", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("17", "0"), "Klasse");
            $export->setValue($export->getCell("18", "0"), "3.Kind");
            $export->setValue($export->getCell("19", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("20", "0"), "Klasse");
            $export->setValue($export->getCell("21", "0"), "4.Kind");
            $export->setValue($export->getCell("22", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("23", "0"), "Klasse");
            $export->setValue($export->getCell("24", "0"), "Bemerkungen");
            $export->setValue($export->getCell("25", "0"), "Straße");
            $export->setValue($export->getCell("26", "0"), "Hausnummer");
            $export->setValue($export->getCell("27", "0"), "PLZ");
            $export->setValue($export->getCell("28", "0"), "Ort");
            $export->setValue($export->getCell("29", "0"), "Ortsteil");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['DebtorNumber']);

                $export->setValue($export->getCell("2", $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell("3", $Row), $PersonData['FatherTitle']);
                $export->setValue($export->getCell("4", $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell("5", $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell("6", $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell("7", $Row), $PersonData['MotherTitle']);
                $export->setValue($export->getCell("8", $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell("9", $Row), $PersonData['MotherFirstName']);

                $export->setValue($export->getCell("25", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("26", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("27", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("28", $Row), $PersonData['City']);
                $export->setValue($export->getCell("29", $Row), $PersonData['District']);

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
    public function createMedicList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['City'] = $Item['Code'] = $Item['District'] = '';
                $Item['Address'] = '';

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
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

                    $Item['Address'] = $tblAddress->getGuiString();
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
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createMedicListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Hausnr.");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Wohnort");
            $export->setValue($export->getCell("7", "0"), "Ortsteil");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("3", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("6", $Row), $PersonData['City']);
                $export->setValue($export->getCell("7", $Row), $PersonData['District']);

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
     * @return array
     */
    public function createInterestedPersonList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Phone'] = '';
                $Item['PhoneGuardian'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = $Item['DivisionLevel'] = $Item['RegistrationDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = '';
                $Item['Hoard'] = 'Nein';
                $Item['FatherSalutation'] = $Item['FatherTitle'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherTitle'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';

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
                    }
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
                                $Item['Siblings'] .= $relationship->getServiceTblPersonTo()->getFullName() . ' ';
                            } else {
                                $Item['Siblings'] .= $relationship->getServiceTblPersonFrom()->getFullName() . ' ';
                            }
                        }
                    }
                    $Item['Siblings'] = trim($Item['Siblings']);
                }


                $groupList = Group::useService()->getGroupAllByPerson($tblPerson);
                if (!empty($groupList)) {
                    foreach ($groupList as $group) {
                        if ($group->getName() == 'Hort') {
                            $Item['Hoard'] = 'Ja';
                        }
                    }
                }
                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (( $tblPhone = $tblToPhone->getTblPhone() )) {
                            if ($Item['Phone'] == '') {
                                $Item['Phone'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName().' ('.$tblPhone->getNumber().' '.
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            } else {
                                $Item['Phone'] .= ', '.$tblPhone->getNumber().' '.
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            }
                        }
                    }
                    if ($Item['Phone'] != '') {
                        $Item['Phone'] .= ')';
                    }
                }

                $father = null;
                $mother = null;
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
                                        if (( $tblPhone = $tblToPhone->getTblPhone() )) {
                                            if (!isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] =
                                                    $tblPersonGuardian->getFirstName().' '.$tblPersonGuardian->getLastName().
                                                    ' ('.$tblPhone->getNumber().' '.
                                                    // modify TypeShort
                                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                                            } else {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ', '.$tblPhone->getNumber().' '.
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
                        }
                    }
                }

                if (!empty($Item['PhoneGuardian'])) {
                    $Item['PhoneGuardian'] = implode('; ', $Item['PhoneGuardian']);
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

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anmeldedatum");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Schuljahr");
            $export->setValue($export->getCell("4", "0"), "Klassenstufe");
            $export->setValue($export->getCell("5", "0"), "Schulart 1");
            $export->setValue($export->getCell("6", "0"), "Schulart 2");
            $export->setValue($export->getCell("7", "0"), "Straße");
            $export->setValue($export->getCell("8", "0"), "Hausnummer");
            $export->setValue($export->getCell("9", "0"), "PLZ");
            $export->setValue($export->getCell("10", "0"), "Ort");
            $export->setValue($export->getCell("11", "0"), "Ortsteil");
            $export->setValue($export->getCell("12", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("13", "0"), "Geburtsort");
            $export->setValue($export->getCell("14", "0"), "Staatsangeh.");
            $export->setValue($export->getCell("15", "0"), "Bekenntnis");
            $export->setValue($export->getCell("16", "0"), "Geschwister");
            $export->setValue($export->getCell("17", "0"), "Hort");
            $export->setValue($export->getCell("18", "0"), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell("19", "0"), "Titel Sorgeberechtigter 1");
            $export->setValue($export->getCell("20", "0"), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell("21", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("22", "0"), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell("23", "0"), "Titel Sorgeberechtigter 2");
            $export->setValue($export->getCell("24", "0"), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell("25", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("26", "0"), "Telefon Interessent");
            $export->setValue($export->getCell("27", "0"), "Telefon Sorgeberechtigte");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell("4", $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell("5", $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell("6", $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("8", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("10", $Row), $PersonData['City']);
                $export->setValue($export->getCell("11", $Row), $PersonData['District']);
                $export->setValue($export->getCell("12", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("13", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("14", $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell("15", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("16", $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell("17", $Row), $PersonData['Hoard']);
                $export->setValue($export->getCell("18", $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell("19", $Row), $PersonData['FatherTitle']);
                $export->setValue($export->getCell("20", $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell("21", $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell("22", $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell("23", $Row), $PersonData['MotherTitle']);
                $export->setValue($export->getCell("24", $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell("25", $Row), $PersonData['MotherFirstName']);
                $export->setValue($export->getCell("26", $Row), $PersonData['Phone']);
                $export->setValue($export->getCell("27", $Row), $PersonData['PhoneGuardian']);

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
    public function createParentTeacherConferenceList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Attendance'] = '';

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createParentTeacherConferenceListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Anwesenheit");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Attendance']);

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
     * @return false|array
     */
    public function createClubMemberList()
    {

        $tblGroup = Group::useService()->getGroupByMetaTable( TblGroup::META_TABLE_CLUB );
        $TableContent = array();
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                    $Item['FirstName'] = $tblPerson->getFirstSecondName();
                    $Item['LastName'] = $tblPerson->getLastName();
                    $Item['Salutation'] = $tblPerson->getSalutation();
                    $Item['Title'] = $tblPerson->getTitle();
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                    $Item['Address'] = '';
                    $Item['Phone'] = $Item['Mail'] = '';
                    $Item['Directorate'] = '';

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

                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                    $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                    if ($phoneList) {
                        $Item['Phone'] = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($mailList) {
                        $Item['Mail'] = $mailList[0]->getTblMail()->getAddress();
                    }

                    array_push($TableContent, $Item);
                });
            }

            return $TableContent;
        }

        return false;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClubMemberListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Titel");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Name");
            $export->setValue($export->getCell("4", "0"), "Straße");
            $export->setValue($export->getCell("5", "0"), "Hausnr.");
            $export->setValue($export->getCell("6", "0"), "PLZ");
            $export->setValue($export->getCell("7", "0"), "Ort");
            $export->setValue($export->getCell("8", "0"), "Ortsteil");
            $export->setValue($export->getCell("9", "0"), "Telefon");
            $export->setValue($export->getCell("10", "0"), "Mail");
            $export->setValue($export->getCell("11", "0"), "Vorstand");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Title']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("5", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("6", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("7", $Row), $PersonData['City']);
                $export->setValue($export->getCell("8", $Row), $PersonData['District']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Phone']);
                $export->setValue($export->getCell("10", $Row), $PersonData['Mail']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Directorate']);

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
    public function createPrintClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        $tblStudentGroup1 = Group::useService()->getGroupByMetaTable('STUDENT_GROUP_1');
        $tblStudentGroup2 = Group::useService()->getGroupByMetaTable('STUDENT_GROUP_2');
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblStudentGroup1, $tblStudentGroup2, $tblDivision, &$count) {

                $Item['Number'] = $count++;
                $Item['Education'] = '';
                $Item['ExcelName'] = '';
                $Item['Address'] = '';
                $Item['ExcelAddress'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $Item['PhoneNumbers'] = '';
                $Item['ExcelPhoneNumbers'] = '';
                $Item['Orientation'] = '';
                $Item['OrientationAndFrench'] = '';
                $Item['Advanced'] = '';
                $Item['Education'] = '';
                $Item['Group'] = '';
                $Item['Group1'] = false;
                $Item['Group2'] = false;
                $Item['Elective'] = '';
                $Item['ExcelElective'] = '';
                $Item['Integration'] = '';
                $Item['French'] = '';

                $father = null;
                $mother = null;
                $fatherPhoneList = false;
                $motherPhoneList = false;

                if ($tblStudentGroup1
                    && Group::useService()->existsGroupPerson($tblStudentGroup1, $tblPerson)){
                    $Item['Group'] .= 1;
                    $Item['Group1'] = true;
                }
                if ($tblStudentGroup2
                    && Group::useService()->existsGroupPerson($tblStudentGroup2, $tblPerson)){
                    (!empty($Item['Group']) ? $Item['Group'] .= ', 2' : $Item['Group'] = 2);
                    $Item['Group2'] = true;
                }

                $Sibling = array();
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getId() == 1) {
                            if ($father === null) {
                                $father = $guardian->getServiceTblPersonFrom();
                                if ($father) {
                                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                                }
                            } else {
                                $mother = $guardian->getServiceTblPersonFrom();
                                if ($mother) {
                                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                                }
                            }
                        }
                        if ($guardian->getTblType()->getName() == 'Geschwisterkind') {
                            if ($guardian->getServiceTblPersonFrom()->getId() != $tblPerson->getId()) {
                                if (( $tblStudent = Student::useService()->getStudentByPerson($guardian->getServiceTblPersonFrom()) )) {
                                    $DivisionDisplay = $this->getDivisionDisplayStringByPerson($guardian->getServiceTblPersonFrom(), $tblDivision);
                                    $Sibling[] = '['.$guardian->getServiceTblPersonFrom()->getFirstName().$DivisionDisplay.']';
                                }
                            } elseif (( $tblStudent = Student::useService()->getStudentByPerson($guardian->getServiceTblPersonTo()) )) {
                                if ($guardian->getServiceTblPersonTo()->getId() != $tblPerson->getId()) {
                                    $DivisionDisplay = $this->getDivisionDisplayStringByPerson($guardian->getServiceTblPersonTo(), $tblDivision);
                                    $Sibling[] = '['.$guardian->getServiceTblPersonTo()->getFirstName().$DivisionDisplay.']';
                                }
                            }
                        }
                    }
                }

                $SiblingString = '';
                if (!empty( $Sibling )) {
                    $SiblingString = implode('<br>', $Sibling);
                    $Item['ExcelSibling'] = $Sibling;
                }

                if (!( $tblAddress = Address::useService()->getAddressByPerson($tblPerson) )) {
                    $tblAddress = false;
                }
                if ($tblAddress) {
                    if ($tblAddress->getTblCity()->getDisplayDistrict() != '') {
                        $Item['ExcelAddress'][] = $tblAddress->getTblCity()->getDisplayDistrict();
                    }
                    $Item['ExcelAddress'][] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    $Item['ExcelAddress'][] = $tblAddress->getTblCity()->getCode().' '.$tblAddress->getTblCity()->getName();
                }

                if ($father) {
                    $tblAddressFather = Address::useService()->getAddressByPerson($father);
                    if ($tblAddressFather && $tblAddressFather->getId() != ( $tblAddress ? $tblAddress->getId() : null )) {
                        if (!empty($Item['ExcelAddress'])) {
                            $Item['ExcelAddress'][] = '- - - - - - -';
                        }
                        $Item['ExcelAddress'][] = '('.$father->getLastFirstName().')';
                        if ($tblAddressFather->getTblCity()->getDistrict() != '') {
                            $Item['ExcelAddress'][] = $tblAddressFather->getTblCity()->getDistrict();
                        }
                        $Item['ExcelAddress'][] = $tblAddressFather->getStreetName().' '.$tblAddressFather->getStreetNumber();
                        $Item['ExcelAddress'][] = $tblAddressFather->getTblCity()->getCode().' '.$tblAddressFather->getTblCity()->getName();
                    }
                }
                if ($mother) {
                    $tblAddressMother = Address::useService()->getAddressByPerson($mother);
                    if ($tblAddressMother && $tblAddressMother->getId() != ( $tblAddress ? $tblAddress->getId() : null )) {
                        if (!empty($Item['ExcelAddress'])) {
                            $Item['ExcelAddress'][] = '- - - - - - -';
                        }
                        $Item['ExcelAddress'][] = '('.$mother->getLastFirstName().')';
                        if ($tblAddressMother->getTblCity()->getDistrict() != '') {
                            $Item['ExcelAddress'][] = $tblAddressMother->getTblCity()->getDistrict();
                        }
                        $Item['ExcelAddress'][] = $tblAddressMother->getStreetName().' '.$tblAddressMother->getStreetNumber();
                        $Item['ExcelAddress'][] = $tblAddressMother->getTblCity()->getCode().' '.$tblAddressMother->getTblCity()->getName();
                    }
                }

                if (!empty($Item['ExcelAddress'])) {
                    $Item['Address'] = implode('<br/>', $Item['ExcelAddress']);
                }


                $Item['FatherName'] = $father ? ($tblPerson->getLastName() == $father->getLastName()
                    ? $father->getFirstSecondName() : $father->getFirstSecondName() . ' ' . $father->getLastName()) : '';
                $Item['MotherName'] = $mother ? ($tblPerson->getLastName() == $mother->getLastName()
                    ? $mother->getFirstSecondName() : $mother->getFirstSecondName() . ' ' . $mother->getLastName()) : '';

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblIntegration = Student::useService()->getStudentIntegrationById($tblStudent->getTblStudentIntegration());
                    if ($tblIntegration) {
                        if ($tblIntegration->getCoachingRequired()) {
                            $Item['Integration'] = '1';
                        }
                    }
                }

                $Item['DisplayName'] = ( $Item['Integration'] === '1'
                        ? new Bold($tblPerson->getLastFirstName())
                        : $tblPerson->getLastFirstName() )
                    . ($father || $mother ? '<br>(' . ($father ? $Item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        .( $mother ? $Item['MotherName'] : '' ).')' : '' )
                    .( $SiblingString != '' ? '<br/>'.$SiblingString : '' );

                $Item['ExcelName'][] = $tblPerson->getLastFirstName();
                if ($father || $mother) {
                    $Item['ExcelName'][] = '('.( $father ? $Item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        . ($mother ? $Item['MotherName'] : '') . ')';
                }

                if (!empty( $Sibling )) {
                    foreach ($Sibling as $Child) {
                        $Item['ExcelName'][] = $Child;
                    }
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $typeString = Phone::useService()->getPhoneTypeShort($phone);
                        $phoneNumbers[$phone->getId()] = $phone->getTblPhone()->getNumber().' '.$typeString
                            . ($phone->getRemark() ? ' ' . $phone->getRemark() : '');
                    }
                }
                if ($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            if (!isset( $phoneNumbers[$phone->getTblPhone()->getId()] )) {
                                $phoneNumbers[$phone->getTblPhone()->getId()] = $this->getPhoneGuardianString($phone);
                            }
                        }
                    }
                }
                if ($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            if (!isset( $phoneNumbers[$phone->getTblPhone()->getId()] )) {
                                $phoneNumbers[$phone->getTblPhone()->getId()] = $this->getPhoneGuardianString($phone);
                            }
                        }
                    }
                }

                if (!empty($phoneNumbers)) {
//                    $phoneNumbers = array_unique($phoneNumbers);
                    $Item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $Item['ExcelPhoneNumbers'] = $phoneNumbers;
                }

                // NK/Profil
                if ($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier(2);
                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                        $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);

                    if ($tblStudentSubject)
                    {
                        if ($tblStudentSubject->getServiceTblSubject() && $tblStudentSubject->getServiceTblSubject()->getAcronym() == 'FR')
                        {
                            $Item['French'] = 'FR';
                        }
                    }
                    $isSet = false;
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                        $isSet = true;
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        if (!$isSet) {
                            $Item['Orientation'] = $tblSubject->getAcronym();
//                            $isSet = true;
                        } else {
                            if (($tblLevel = $tblDivision->getTblLevel())
                                && (($tblType = $tblLevel->getServiceTblType()))
                                && $tblType->getName() == 'Gymnasium'
                            ) {
                                $Item['Advanced'] = $tblSubject->getAcronym();
                            }
                        }
                    }

                    if (($tblLevel = $tblDivision->getTblLevel())
                        && (($tblType = $tblLevel->getServiceTblType()))
                        && $tblType->getName() == 'Mittelschule / Oberschule'
                    ) {
                        $Item['OrientationAndFrench'] = $Item['Orientation'] .
                            (!empty($Item['Orientation']) && !empty($Item['French']) ? ', ' : '') . $Item['French'];
                    } else {
                        $Item['OrientationAndFrench'] = $Item['Orientation'];
                    }

                    if ($Item['Advanced'] && $Item['OrientationAndFrench']) {
                        $Item['OrientationAndFrench'] .= '<br/>' . $Item['Advanced'];
                    }

                    // Vertiefungskurs // Erstmal deaktiviert (04.08.2016)
//                    if (!$isSet) {
//                        $tblStudentAdvanced = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
//                            $tblStudent,
//                            Student::useService()->getStudentSubjectTypeByIdentifier('ADVANCED')
//                        );
//                        if ($tblStudentAdvanced && ($tblSubject = $tblStudentAdvanced[0]->getServiceTblSubject())) {
//                            $Item['Orientation'] = $tblSubject->getAcronym();
////                            $isSet = true;
//                        }
//                    }

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
                        if (!empty( $ElectiveList )) {
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
     * @param TblPerson   $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    private function getDivisionDisplayStringByPerson(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        $result = '';
        $YearString = $tblDivision->getServiceTblYear()->getYear();
        // Find the same Year (String Compare)
        $tblYearList = Term::useService()->getYearByName($YearString);
        if ($tblYearList) {
            $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    foreach ($tblYearList as $tblYear) {
                        if ($tblDivisionStudent->getTblDivision()) {
                            $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                            if ($divisionYear && $divisionYear->getId() == $tblYear->getId()) {
                                $tblDivision = $tblDivisionStudent->getTblDivision();
                                $result = ' '.$tblDivision->getDisplayName();
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @return string
     */
    private function getPhoneGuardianString(TblToPerson $tblToPerson){
        $typeString = Phone::useService()->getPhoneTypeShort($tblToPerson);
        $person = '';
        $tblGuardianPerson = $tblToPerson->getServiceTblPerson();
        if ($tblGuardianPerson){
            // Set Default by Salutation
            if ($tblGuardianPerson->getSalutation() == 'Herr'){
                $person = 'V.';
            } elseif ($tblGuardianPerson->getSalutation() == 'Frau'){
                $person = 'M.';
            } else {
                $person = $tblGuardianPerson->getFirstName();
            }
            //Override By found Gender
            $tblGuardianCommon = Common::useService()->getCommonByPerson($tblGuardianPerson);
            if ($tblGuardianCommon) {
                if (( $GuardianBirthDates = $tblGuardianCommon->getTblCommonBirthDates() )) {
                    if ($GuardianBirthDates->getGender() == 1) {
                        $person = 'V.';
                    } elseif ($GuardianBirthDates->getGender() == 2) {
                        $person = 'M.';
                    }
                }
            }
        }

        return $tblToPerson->getTblPhone()->getNumber().' '.$typeString.' '.$person
        . ($tblToPerson->getRemark() ? ' ' . $tblToPerson->getRemark() : '');
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     * @param $DivisionId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createPrintClassListExcel($PersonList, $tblPersonList, $DivisionId)
    {

        // get PersonList sorted by GradeBook
        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $SetIntegrationNotice = false;

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
            $export->setValue($export->getCell(2, 1), "Adresse");
            $export->setValue($export->getCell(3, 1), "Telefonnummer");
            $export->setValue($export->getCell(4, 1), "Gr");
            $export->setValue($export->getCell(5, 1), "WB/P/FR");
            $export->setValue($export->getCell(6, 1), "BG");
            $export->setValue($export->getCell(7, 1), "WF");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, 1))->setFontBold();

            $Row = 2;
            $counterStudentGroup1 = 0;
            $counterStudentGroup2 = 0;
            $orientationList = array();
            $counterFrench = 0;
            $excelElectiveList = array();
            $educationList = array();
            foreach ($PersonList as $PersonData) {
                $NameRow = $AddressRow = $PhoneRow = $ElectiveRow = $Row;

                if (!empty($PersonData['Group1'])) {
                    $counterStudentGroup1++;
                }
                if (!empty($PersonData['Group2'])) {
                    $counterStudentGroup2++;
                }
                if (!empty($PersonData['Orientation'])) {
                    if(isset($orientationList[$PersonData['Orientation']])){
                        $orientationList[$PersonData['Orientation']] += 1;
                    } else {
                        $orientationList[$PersonData['Orientation']] = 1;
                    }
                }
                if (!empty($PersonData['French']))
                {
                    $counterFrench++;
                }
                if (!empty($PersonData['Education'])) {
                    if(isset($educationList[$PersonData['Education']])){
                        $educationList[$PersonData['Education']] += 1;
                    } else {
                        $educationList[$PersonData['Education']] = 1;
                    }
                }

                if (!empty($PersonData['ExcelElective'])) {
                    foreach($PersonData['ExcelElective'] as $Elective){
                        if(isset($excelElectiveList[$Elective])){

                            $excelElectiveList[$Elective] += 1;
                        } else {
                            $excelElectiveList[$Elective] = 1;
                        }
                    }
                }


                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(4, $Row), $PersonData['Group']);
                $export->setValue($export->getCell(5, $Row), $PersonData['Orientation']);
                if (($tblLevel = $tblDivision->getTblLevel())
                    && (($tblType = $tblLevel->getServiceTblType()))
                    && $tblType->getName() == 'Mittelschule / Oberschule'
                ) {
                    $export->setValue($export->getCell(5, $Row+1), $PersonData['French']);
                } elseif ($PersonData['Advanced']) {
                    $export->setValue($export->getCell(5, $Row + 1), $PersonData['Advanced']);
                }

                $export->setValue($export->getCell(6, $Row), $PersonData['Education']);


                if (isset($PersonData['ExcelName']) && !empty($PersonData['ExcelName'])) {
                    foreach ($PersonData['ExcelName'] as $Key => $Name) {
                        if ($Key == 0) {
                            if ($PersonData['Integration'] === '1') {
                                $export->setStyle($export->getCell(0, $Row), $export->getCell(0, $Row))->setFontBold();
                                $SetIntegrationNotice = true;
                            }
                        }
                        $export->setValue($export->getCell(0, $NameRow), $Name);
                        $NameRow++;
                    }
                }
                if (isset($PersonData['ExcelAddress']) && !empty($PersonData['ExcelAddress'])) {
                    foreach ($PersonData['ExcelAddress'] as $Address) {
                        if ($Address == '- - - - - - -') {
                            $export->setStyle($export->getCell(2, $AddressRow), $export->getCell(2, $AddressRow))
                                ->setBorderTop();
                        } else {
                            $export->setValue($export->getCell(2, $AddressRow), $Address);
                            $AddressRow++;
                        }
                    }
                }
                if (isset($PersonData['ExcelPhoneNumbers']) && !empty($PersonData['ExcelPhoneNumbers'])) {
                    foreach ($PersonData['ExcelPhoneNumbers'] as $Phone) {
                        $export->setValue($export->getCell(3, $PhoneRow), $Phone);
                        $PhoneRow++;
                    }
                }
                if (isset($PersonData['ExcelElective']) && !empty($PersonData['ExcelElective'])) {
                    foreach ($PersonData['ExcelElective'] as $Elective) {
                        $export->setValue($export->getCell(7, $ElectiveRow), $Elective);
                        $ElectiveRow++;
                    }
                }

                $Row++;

                if ($NameRow > $Row) {
                    $Row = $NameRow;
                }
                if ($AddressRow > $Row) {
                    $Row = $AddressRow;
                }
                if ($PhoneRow > $Row) {
                    $Row = $PhoneRow;
                }
                if ($ElectiveRow > $Row) {
                    $Row = $ElectiveRow;
                }

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $Row - 1), $export->getCell(7, $Row - 1))->setBorderBottom();
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $Row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $Row - 1))->setBorderOutline();

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

            if ($SetIntegrationNotice) {
                $Row++;
                $export->setStyle($export->getCell(0, $Row), $export->getCell(2, $Row))->mergeCells()
                    ->setFontBold();
                $export->setValue($export->getCell(0, $Row), '*Schriftart-Fett für Kinder mit Förderbedarf');
            }

            if (!empty($custodyList)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row),
                    'Elternsprecher ' . (empty($custodyList) ? '' : ' ' . implode(', ',
                            $custodyList)));
            }

            if (!empty($counterStudentGroup1) && !empty($counterStudentGroup2)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row), 'Klassengruppen');
                $Row++;
                $export->setValue($export->getCell(0, $Row), 'Anzahl Gruppe 1:');
                $export->setValue($export->getCell(1, $Row), $counterStudentGroup1);
                $Row++;
                $export->setValue($export->getCell(0, $Row), 'Anzahl Gruppe 2:');
                $export->setValue($export->getCell(1, $Row), $counterStudentGroup2);
            }

            if (!empty($orientationList)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row), 'Wahlbereiche/Profile');
                foreach ($orientationList as $orientation => $count) {
                    $Row++;
                    $export->setValue($export->getCell(0, $Row), $orientation . ':');
                    $export->setValue($export->getCell(1, $Row), $count);
                }
            }

            if (!empty($counterFrench)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row), 'Fremdsprache FR');
                $Row++;
                $export->setValue($export->getCell(0, $Row), 'Französisch:');
                $export->setValue($export->getCell(1, $Row), $counterFrench);
            }

            if (!empty($educationList)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row), 'Bildungsgänge');
                foreach ($educationList as $education => $count) {
                    $Row++;
                    $export->setValue($export->getCell(0, $Row), $education . ':');
                    $export->setValue($export->getCell(1, $Row), $count);
                }
            }

            if (!empty($excelElectiveList)) {
                $Row += 2;
                $export->setValue($export->getCell(0, $Row), 'Wahlfächer');
                foreach ($excelElectiveList as $excelElective => $count) {
                    $Row++;
                    $export->setValue($export->getCell(0, $Row), $excelElective . ':');
                    $export->setValue($export->getCell(1, $Row), $count);
                }
            }



            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(10);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(21);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(3);
            $export->setStyle($export->getCell(5, 0), $export->getCell(6, $Row))->setColumnWidth(9);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(3.5);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(4);

            // Schriftgröße
            $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontSize(12)
                ->setFontBold()
                ->mergeCells();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $Row))->setFontSize(10);

            // Spalten zentriert
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setAlignmentCenter();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
