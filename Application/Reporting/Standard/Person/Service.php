<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Service extends Extension
{

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @param                     $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getGroup(IFormInterface $Stage = null, $Select = null, $Redirect)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblGroup = Group::useService()->getGroupById($Select['Group']);
        if ($tblGroup){
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
        if (!empty( $tblPersonList )) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $Item['Number'] = $count++;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();

                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName().' '.
                        $Item['District'];
                } else {
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                    $Item['Address'] = '';
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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Konfession");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Straße");
            $export->setValue($export->getCell("7", "0"), "Hausnr.");
            $export->setValue($export->getCell("8", "0"), "PLZ");
            $export->setValue($export->getCell("9", "0"), "Ort");
            $export->setValue($export->getCell("10", "0"), "Ortsteil");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("9", $Row), $PersonData['City']);
                $export->setValue($export->getCell("10", $Row), $PersonData['District']);

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
    public function createExtendedClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty( $tblPersonList )) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['Guardian1'] = '';
                $Item['Guardian2'] = '';
                $Item['PhoneGuardian1'] = '';
                $Item['PhoneGuardian2'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $Item['Name'] = $tblPerson->getLastFirstName();
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
                        $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    }
                }
                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName().' '.
                        $Item['District'];
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }
                $Guardian1 = null;
                $Guardian2 = null;
                unset($phoneListGuardian1);
                unset($phoneListGuardian2);
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    $Count = 0;
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($Count === 0) {
                                if ($guardian->getServiceTblPersonFrom()) {
                                    $Guardian1 = $guardian->getServiceTblPersonFrom();
                                }
                            }
                            if ($Count === 1) {
                                if ($guardian->getServiceTblPersonFrom()) {
                                    $Guardian2 = $guardian->getServiceTblPersonFrom();
                                }
                            }
                            $Count = $Count + 1;
                        }
                    }
                }
                if (isset( $Guardian1 )) {
                    $Item['Guardian1'] = $Guardian1->getFullName();
                    $Guardian1PhoneList = Phone::useService()->getPhoneAllByPerson($Guardian1);
                    if ($Guardian1PhoneList) {
                        foreach ($Guardian1PhoneList as $Guardian1Phone) {
                            if ($Guardian1Phone->getTblType()->getName() === 'Privat' && $Guardian1Phone->getTblType()->getDescription() === 'Mobil') {
                                $phoneListGuardian1[] = $Guardian1Phone->getTblPhone()->getNumber();
                            }
                        }
                        foreach ($Guardian1PhoneList as $Guardian1Phone) {
                            if ($Guardian1Phone->getTblType()->getName() === 'Privat') {
                                $phoneListGuardian1[] = $Guardian1Phone->getTblPhone()->getNumber();
                            }
                        }
                        if (isset($phoneListGuardian1)) {
                            $phoneListGuardian1 = array_unique($phoneListGuardian1);
                        }
                    }
                }
                if (isset( $Guardian2 )) {
                    $Item['Guardian2'] = $Guardian2->getFullName();
                    $Guardian2PhoneList = Phone::useService()->getPhoneAllByPerson($Guardian2);
                    if ($Guardian2PhoneList) {
                        foreach ($Guardian2PhoneList as $Guardian2Phone) {
                            if ($Guardian2Phone->getTblType()->getName() === 'Privat' && $Guardian2Phone->getTblType()->getDescription() === 'Mobil') {
                                $phoneListGuardian2[] = $Guardian2Phone->getTblPhone()->getNumber();
                            }
                        }
                        foreach ($Guardian2PhoneList as $Guardian2Phone) {
                            if ($Guardian2Phone->getTblType()->getName() === 'Privat') {
                                $phoneListGuardian2[] = $Guardian2Phone->getTblPhone()->getNumber();
                            }
                        }
                        if (isset($phoneListGuardian2)) {
                            $phoneListGuardian2 = array_unique($phoneListGuardian2);
                        }
                    }
                }

                if (isset($phoneListGuardian1[0])) {
                    $Item['PhoneGuardian1'] = $phoneListGuardian1[0];
                    if (isset($phoneListGuardian2[0])) {
                        if ($phoneListGuardian2[0] === $phoneListGuardian1[0] && isset( $phoneListGuardian1[0] )) {
                            if (isset($phoneListGuardian2[1])) {
                                $Item['PhoneGuardian2'] = $phoneListGuardian2[1];
                            }
                        } else {
                            if (isset($phoneListGuardian2[0])) {
                                $Item['PhoneGuardian2'] = $phoneListGuardian2[0];
                            }
                        }
                    }
                } else {
                    $tblPerson->PhoneGuardian1 = '';
                    if (isset($phoneListGuardian2[0])) {
                        $Item['PhoneGuardian2'] = $phoneListGuardian2[0];
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
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createExtendedClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Geschlecht");
            $export->setValue($export->getCell("3", "0"), "Adresse");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Sorgeberechtigter 1");
            $export->setValue($export->getCell("7", "0"), "Tel. Sorgeber. 1");
            $export->setValue($export->getCell("8", "0"), "Sorgeberechtigter 2");
            $export->setValue($export->getCell("9", "0"), "Tel. Sorgeber. 2");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Gender']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("6", $Row), $PersonData['Guardian1']);
                $export->setValue($export->getCell("7", $Row), $PersonData['PhoneGuardian1']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Guardian2']);
                $export->setValue($export->getCell("9", $Row), $PersonData['PhoneGuardian2']);

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
    public function createBirthdayClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();

        $All = 0;

        if (!empty( $tblPersonList )) {
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

                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName().' '.
                        $Item['District'];
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
                            $Item['Age'] = ( $now->format('Y') - 1 ) - $birthDate->format('Y');
                        }
                    }
                }
                array_push($TableContent, $Item);
            });
        }
        if (!empty( $TableContent )) {
            foreach ($TableContent as $key => $row) {
                $month[$key] = substr($row['Birthday'], 3, 2);
                $day[$key] = substr($row['Birthday'], 0, 2);
                $year[$key] = substr($row['Birthday'], 6, 4);
            }
            array_multisort($month, SORT_ASC, $day, SORT_ASC, $day, SORT_DESC, $TableContent);


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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createBirthdayClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

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

        if (!empty( $tblPersonList )) {

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
                        $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    }
                }
                $Item['Name'] = $tblPerson->getLastFirstName();
                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber() . '<br/>' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName().' '.
                        $Item['District'];
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday().'<br/>'.$common->getTblCommonBirthDates()->getBirthplace();
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
                $Item['Guardian'] = $Guardian1.'<br/>'.$Guardian2;

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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createMedicalInsuranceClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

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

        if (!empty( $tblPersonList )) {

            foreach ($tblPersonList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);

            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {

                $All++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Number'] = $All;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $Item['PhoneNumber'] = '';
                $Item['MobilPhoneNumber'] = '';
                $Item['Mail'] = '';
                if (($addressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $tblPerson->Gender = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $tblPerson->Gender = 'weiblich';
                        }
                    }
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName().' '.
                        $Item['District'];
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

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createGroupListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Anrede");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Nachname");
            $export->setValue($export->getCell("4", "0"), "Geburtstag");
            $export->setValue($export->getCell("5", "0"), "Anschrift");
            $export->setValue($export->getCell("6", "0"), "Telefon Festnetz");
            $export->setValue($export->getCell("7", "0"), "Telefon Mobil");
            $export->setValue($export->getCell("8", "0"), "E-mail");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("6", $Row), $PersonData['PhoneNumber']);
                $export->setValue($export->getCell("7", $Row), $PersonData['MobilPhoneNumber']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Mail']);

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
}
