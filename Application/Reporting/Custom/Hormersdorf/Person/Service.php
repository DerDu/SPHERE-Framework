<?php

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;

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
        if (!empty( $tblPersonList )) {

            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['ExcelNameRow2'] = '';
                $Item['Address'] = '';
                $Item['ExcelAddressRow1'] = '';
                $Item['ExcelAddressRow2'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $Item['PhoneNumbers'] = '';
                $Item['ExcelPhoneNumbers'] = '';

                $father = null;
                $mother = null;
                $fatherPhoneList = false;
                $motherPhoneList = false;
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
                    }
                }

                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $Item['FatherName'] = $father ? ( $tblPerson->getLastName() == $father->getLastName()
                    ? $father->getFirstSecondName() : $father->getFirstSecondName().' '.$father->getLastName() ) : '';
                $Item['MotherName'] = $mother ? ( $tblPerson->getLastName() == $mother->getLastName()
                    ? $mother->getFirstSecondName() : $mother->getFirstSecondName().' '.$mother->getLastName() ) : '';
                $Item['DisplayName'] = $tblPerson->getLastFirstName()
                    .( $father !== null || $mother !== null ? '<br>('.( $mother !== null ? $Item['MotherName']
                            .( $father !== null ? ', ' : '' ) : '' )
                        .( $father !== null ? $Item['FatherName'] : '' ).')' : '' );

                $Item['ExcelNameRow1'] = $tblPerson->getLastFirstName();
                if ($father !== null || $mother !== null) {
                    $Item['ExcelNameRow2'] = '('.( $mother !== null ? $Item['MotherName']
                            .( $father !== null ? ', ' : '' ) : '' )
                        .( $father !== null ? $Item['FatherName'] : '' ).')';
                }

                if ($address !== null) {
                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().'<br>'.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName() . ' ' .
                        $address->getTblAddress()->getTblCity()->getDistrict();
                    $Item['ExcelAddressRow1'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber();
                    $Item['ExcelAddressRow2'] = $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
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
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber().' '.$phone->getTblType()->getName()
                            .( $phone->getRemark() !== '' ? ' '.$phone->getRemark() : '' );
                    }
                }
                if ($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $phone->getTblType()->getName() . ' '
                                . $phone->getServiceTblPerson()->getFullName() . ($phone->getRemark() !== '' ? ' ' . $phone->getRemark() : '');
                        }
                    }
                }
                if ($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber() . ' ' . $phone->getTblType()->getName() . ' '
                                . $phone->getServiceTblPerson()->getFullName() . ($phone->getRemark() !== '' ? ' ' . $phone->getRemark() : '');
                        }
                    }
                }

                if (!empty( $phoneNumbers )) {
                    $Item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $Item['ExcelPhoneNumbers'] = $phoneNumbers;
                }
                // ToDo JohK zusammenfassung am Ende

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Geb.-Datum");
            $export->setValue($export->getCell("2", "0"), "Adresse");
            $export->setValue($export->getCell("3", "0"), "Telefonnummer");

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $rowPerson = $Row;
                $export->setValue($export->getCell("0", $Row), $PersonData['ExcelNameRow1']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("2", $Row), $PersonData['ExcelAddressRow1']);

                $Row++;
                $export->setValue($export->getCell("0", $Row), $PersonData['ExcelNameRow2']);
                $export->setValue($export->getCell("2", $Row), $PersonData['ExcelAddressRow2']);
                $Row++;

                if (!empty( $PersonData['ExcelPhoneNumbers'] )) {
                    foreach ($PersonData['ExcelPhoneNumbers'] as $phone) {
                        $export->setValue($export->getCell("3", $rowPerson++), $phone);
                    }
                }

                if ($rowPerson > $Row) {
                    $Row = $rowPerson;
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

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Stand '.date("d.m.Y"));

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
        if (!empty( $tblPersonList )) {

            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Birthday'] = '';
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Geburtstag");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Birthday']);

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