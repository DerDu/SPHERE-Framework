<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:05
 */

namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Service extends Extension
{

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
     * @param TblDivision $tblDivision
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createParentTeacherConferenceListExcel(TblDivision $tblDivision, $PersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("3", $row++), "EVANGELISCHE");
            $export->setValue($export->getCell("3", $row++), "GRUNDSCHULE");
            $export->setValue($export->getCell("3", $row++), "RADEBEUL");

            $export->setValue($export->getCell("0", $row++), "Anwesenheitsliste Elternabend");
            $export->setValue($export->getCell("0", $row++), "Datum:");
            $export->setValue($export->getCell("0", $row++), "Thema:");
            $row++;
            $export->setValue($export->getCell("0", $row++), "Klasse: " . $tblDivision->getDisplayName());
            $row++;
            $headerRow = $row;
            $export->setValue($export->getCell("0", $row), "lfdNr.");
            $export->setValue($export->getCell("1", $row), "Name");
            $export->setValue($export->getCell("2", $row), "Vorname");
            $export->setValue($export->getCell("3", $row), "Unterschrift");
            // Gittertrennlinie
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
            // Zentriert
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setAlignmentCenter();
            $row++;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $row), $PersonData['Attendance']);

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
                // Zentriert
                $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setAlignmentCenter();
                $row++;
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderOutline();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $row))->setColumnWidth(30);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array $countArray
     *
     * @return array
     */
    public function createDenominationList(&$countArray)
    {

        $TableContent = array();
        $countArray = array(
            'All' => 0,
            'RK' => 0,
            'EV' => 0,
            'KEINE' => 0
        );
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            if ($tblPersonList) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
                $count = 1;
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    $countArray['All'] = $count;
                    $Item['Number'] = $count++;
                    $Item['LastName'] = $tblPerson->getLastName();
                    $Item['FirstName'] = $tblPerson->getFirstSecondName();

                    if (($tblCommon = $tblPerson->getCommon())
                        && $tblCommon->getTblCommonInformation()
                    ) {
                        $denomination = trim($tblCommon->getTblCommonInformation()->getDenomination());
                        $Item['Denomination'] = $denomination;
                        if (isset($countArray[strtoupper($denomination)])) {
                            $countArray[strtoupper($denomination)]++;
                        } else {
                            $countArray['KEINE']++;
                        }
                    } else {
                        $Item['Denomination'] = '';
                        $countArray['KEINE']++;
                    }

                    array_push($TableContent, $Item);
                }
            }
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $countArray
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createDenominationListExcel($PersonList, $countArray)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row))->setColumnWidth(8);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $row))->setColumnWidth(26);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $row))->setColumnWidth(30);

            $export->setValue($export->getCell(0, $row), "Religionszugehörigkeit");
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $row++;

            $export->setValue($export->getCell(0, $row),
                "Evangelische Grundschule Radebeul Staatlich genehmigte Ersatzschule                       "
                . date('d.m.Y'));
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();

            $export->setStyle($export->getCell(0, 0), $export->getCell(3, $row))->setBorderOutline(2);

            foreach ($PersonList as $PersonData) {
                $row++;

                $export->setValue($export->getCell(0, $row), $PersonData['Number']);
                $export->setValue($export->getCell(1, $row), $PersonData['LastName']);
                $export->setValue($export->getCell(2, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell(3, $row), $PersonData['Denomination']);

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
            }

            $row++;
            $export->setValue($export->getCell("0", $row),
                "   Schüler:    " . $countArray['All']
                . "             Evangelisch:    " . $countArray['EV']
                . "             Katholisch:    " . $countArray['RK']
                . "             ohne Angabe:    " . $countArray['KEINE']
            );
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderAll();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createPhoneList(TblGroup $tblGroup)
    {

        $TableContent = array();

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {

                $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, ' ');
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();

                $Item['PhoneHome'] = '';
                $Item['PhoneMotherMobile'] = '';
                $Item['PhoneMotherBusiness'] = '';
                $Item['PhoneFatherMobile'] = '';
                $Item['PhoneFatherBusiness'] = '';
                $Item['PhoneEmergency'] = '';
                $Item['Birthday'] = '';

                $phoneList = array();
                $phoneEmergencyList = array();
                if (($tblPersonToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                    foreach ($tblPersonToPhoneList as $tblToPerson) {
                        if ($tblToPerson->getTblType()->getName() == 'Privat'
                            && $tblToPerson->getTblType()->getDescription() == 'Festnetz'
                        ) {
                            $phoneList[] = $tblToPerson->getTblPhone()->getNumber();
                        }
                        if ($tblToPerson->getTblType()->getName() == 'Notfall') {
                            $phoneEmergencyList[] = ($tblToPerson->getRemark()
                                    ? $tblToPerson->getRemark() . ' '
                                    : '')
                                . $tblToPerson->getTblPhone()->getNumber();
                        }
                    }
                }
                if (!empty($phoneList)) {
                    $Item['PhoneHome'] = implode('; ', $phoneList);
                }
                if (!empty($phoneEmergencyList)) {
                    $Item['PhoneEmergency'] = implode('; ', $phoneEmergencyList);
                }

                if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                    foreach ($tblRelationshipList as $tblToPerson) {
                        if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                            && $tblToPerson->getServiceTblPersonTo()
                            && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                            && $tblToPerson->getServiceTblPersonTo()->getId() == $tblPerson->getId()
                        ) {
                            $phoneMotherMobileList = array();
                            $phoneMotherBusinessList = array();
                            if (trim(strtoupper($tblToPerson->getRemark())) == 'MUTTER') {
                                if (($tblFromPersonToPhoneList = Phone::useService()->getPhoneAllByPerson($tblFromPerson))) {
                                    foreach ($tblFromPersonToPhoneList as $tblToMother) {
                                        if ($tblToMother->getTblType()->getName() == 'Privat'
                                            && $tblToMother->getTblType()->getDescription() == 'Mobil'
                                        ) {
                                            $phoneMotherMobileList[] = $tblToMother->getTblPhone()->getNumber();
                                        }
                                        if ($tblToMother->getTblType()->getName() == 'Geschäftlich') {
                                            $phoneMotherBusinessList[] = $tblToMother->getTblPhone()->getNumber();
                                        }
                                    }
                                }
                            }
                            if (!empty($phoneMotherMobileList)) {
                                $Item['PhoneMotherMobile'] = implode('; ', $phoneMotherMobileList);
                            }
                            if (!empty($phoneMotherBusinessList)) {
                                $Item['PhoneMotherBusiness'] = implode('; ', $phoneMotherBusinessList);
                            }

                            $phoneFatherMobileList = array();
                            $phoneFatherBusinessList = array();
                            if (trim(strtoupper($tblToPerson->getRemark())) == 'VATER') {
                                if (($tblFromPersonToPhoneList = Phone::useService()->getPhoneAllByPerson($tblFromPerson))) {
                                    foreach ($tblFromPersonToPhoneList as $tblToFather) {
                                        if ($tblToFather->getTblType()->getName() == 'Privat'
                                            && $tblToFather->getTblType()->getDescription() == 'Mobil'
                                        ) {
                                            $phoneFatherMobileList[] = $tblToFather->getTblPhone()->getNumber();
                                        }
                                        if ($tblToFather->getTblType()->getName() == 'Geschäftlich') {
                                            $phoneFatherBusinessList[] = $tblToFather->getTblPhone()->getNumber();
                                        }
                                    }
                                }
                            }
                            if (!empty($phoneFatherMobileList)) {
                                $Item['PhoneFatherMobile'] = implode('; ', $phoneFatherMobileList);
                            }
                            if (!empty($phoneFatherBusinessList)) {
                                $Item['PhoneFatherBusiness'] = implode('; ', $phoneFatherBusinessList);
                            }
                        }
                    }
                }

                if ($tblPerson->getCommon()
                    && ($tblCommonBirthDates = $tblPerson->getCommon()->getTblCommonBirthDates())
                ) {
                    $Item['Birthday'] = $tblCommonBirthDates->getBirthday();
                }

                array_push($TableContent, $Item);
            }
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createPhoneListExcel($PersonList)
    {

        if (!empty($PersonList)) {

            $division = array();
            $lastName = array();
            $firstName = array();
            foreach ($PersonList as $key => $row) {
                $division[$key] = strtoupper($row['Division']);
                $lastName[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($division, SORT_ASC, $lastName, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell($column++, $row), 'Klasse');
            $export->setValue($export->getCell($column++, $row), 'Nachname');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column++, $row), 'Zuhause');
            $export->setValue($export->getCell($column++, $row), 'Mutter Handy');
            $export->setValue($export->getCell($column++, $row), 'Vater Handy');
            $export->setValue($export->getCell($column++, $row), 'Mutter dienstl.');
            $export->setValue($export->getCell($column++, $row), 'Vater dienstl.');
            $export->setValue($export->getCell($column++, $row), 'Notfall');
            $export->setValue($export->getCell($column, $row), 'Geb.-Datum');

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontSize(10);
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setAlignmentCenter();

            foreach ($PersonList as $PersonData) {
                $row++;
                $column = 0;

                $export->setValue($export->getCell($column++, $row), $PersonData['Division']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneHome']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMotherMobile']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFatherMobile']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMotherBusiness']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFatherBusiness']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneEmergency']);
                $export->setValue($export->getCell($column, $row), $PersonData['Birthday']);

                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();
                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontSize(10);
            }

            $column = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(9);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12.5);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(10);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createKindergartenList(TblGroup $tblGroup)
    {

        $TableContent = array();

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if ($tblPersonList) {
            $count = 1;
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $Item['Number'] = $count++;
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['Kindergarten'] = '';
                $Item['Birthday'] = '';

                if (($tblStudent = $tblPerson->getStudent())) {
                    if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                        && (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType)))
                    ) {
                        if (($tblCompany = $tblTransfer->getServiceTblCompany())
                            && ($tblNurseryGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('NURSERY'))
                            && \SPHERE\Application\Corporation\Group\Group::useService()->existsGroupCompany($tblNurseryGroup,
                                $tblCompany)
                        ) {
                            $Item['Kindergarten'] = $tblCompany->getDisplayName();
                        }
                        elseif (($remark = $tblTransfer->getRemark())
                            && ($pos = strpos($remark, 'Kita:')) !== false
                        ) {
                            $startPos = $pos + strlen('Kita:');
                            if (($pos2 = strpos($remark, 'Staatliche Schule:')) !== false
                                && $pos2 > $pos
                            ) {
                                $Item['Kindergarten'] = trim(substr($remark, $startPos, $pos2 - $startPos));
                            } else {
                                $Item['Kindergarten'] = trim(substr($remark, $startPos));
                            }
                        }
                    }
                }

                if ($tblPerson->getCommon()
                    && ($tblCommonBirthDates = $tblPerson->getCommon()->getTblCommonBirthDates())
                ) {
                    $Item['Birthday'] = $tblCommonBirthDates->getBirthday();
                }

                array_push($TableContent, $Item);
            }
        }

        return $TableContent;
    }

    /**
     * @param TblGroup $tblGroup
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createKindergartenListExcel(TblGroup $tblGroup, $PersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell(0, $row), 'Klassenliste - Kinderhaus');
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $row++;
            $export->setValue($export->getCell(0, $row), 'Klasse: ' . $tblGroup->getName());
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $row++;
            $export->setValue($export->getCell(3, $row), date('d.m.Y'));
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setAlignmentRight();

            $export->setStyle($export->getCell(0, 0), $export->getCell(3, $row))->setBorderOutline();

            $row++;
            $row++;
            $export->setValue($export->getCell($column++, $row), 'lfdNr.');
            $export->setValue($export->getCell($column++, $row), 'Name');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column++, $row), 'Geburtstag');
            $export->setValue($export->getCell($column, $row), 'Kinderhaus');

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();

            foreach ($PersonList as $PersonData) {
                $row++;
                $column = 0;

                $export->setValue($export->getCell($column, $row), $PersonData['Number']);
                $export->setStyle($export->getCell(0, $row), $export->getCell($column++, $row))->setAlignmentCenter();
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column, $row), $PersonData['Kindergarten']);

                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();
            }

            $column = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(50);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createRegularSchoolList(TblGroup $tblGroup)
    {

        $TableContent = array();

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if ($tblPersonList) {
            $count = 1;
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $Item['Number'] = $count++;
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['RegularSchool'] = '';

                if (($tblStudent = $tblPerson->getStudent())) {
                    if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                        && (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType)))
                    ) {
                        if (($remark = $tblTransfer->getRemark())
                            && ($pos = strpos($remark, 'Staatliche Schule:')) !== false
                        ) {
                            $startPos = $pos + strlen('Staatliche Schule:');
                            if (($pos2 = strpos($remark, 'Kita:')) !== false
                                && $pos2 > $pos
                            ) {
                                $Item['RegularSchool'] = trim(substr($remark, $startPos, $pos2 - $startPos));
                            } else {
                                $Item['RegularSchool'] = trim(substr($remark, $startPos));
                            }
                        }
                    }
                }

                array_push($TableContent, $Item);
            }
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createRegularSchoolListExcel($PersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell(0, $row), 'Stammschulenübersicht');
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
            $row++;
            $export->setValue($export->getCell(0, $row),
                'Evangelische Grundschule Radebeul Staatlich genehmigte Ersatzschule                 '
                . date('d.m.Y')
            );
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
            $export->setStyle($export->getCell(0, 0), $export->getCell(3, $row))->setBorderOutline();
            $row++;
            $export->setValue($export->getCell($column++, $row), 'Nr.');
            $export->setValue($export->getCell($column++, $row), 'Name');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column, $row), 'Stammschule');

            foreach ($PersonList as $PersonData) {
                $row++;
                $column = 0;

                $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column, $row), $PersonData['RegularSchool']);

                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();
            }

            $column = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(44);

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
    public function createDiseaseList(TblGroup $tblGroup)
    {

        $TableContent = array();

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if ($tblPersonList) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, ' ');
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['Disease'] = '';

                if (($tblStudent = $tblPerson->getStudent())
                 && ($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                ) {
                   $Item['Disease'] = $tblMedicalRecord->getDisease();
                }

                array_push($TableContent, $Item);
            }
        }

        return $TableContent;
    }

    /**
     * @param TblGroup $tblGroup
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createDiseaseListExcel(TblGroup $tblGroup, $PersonList)
    {

        if (!empty($PersonList)) {

            $division = array();
            $lastName = array();
            $firstName = array();
            foreach ($PersonList as $key => $row) {
                $division[$key] = strtoupper($row['Division']);
                $lastName[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($division, SORT_ASC, $lastName, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $row = 0;
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell(0, $row), 'Klassenliste - Allergie');
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->mergeCells();
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->setFontBold();
            $row++;
            $export->setValue($export->getCell(0, $row), 'Klasse: ' . $tblGroup->getName());
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->mergeCells();
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->setFontSize(14);
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->setFontBold();
            $row++;
            $export->setValue($export->getCell(2, $row), date('d.m.Y'));
            $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->setAlignmentRight();

            $export->setStyle($export->getCell(0, 0), $export->getCell(2, $row))->setBorderOutline();

            $row++;
            $row++;
            $export->setValue($export->getCell($column++, $row), 'Klasse');
            $export->setValue($export->getCell($column++, $row), 'Name');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column, $row), 'Allergie');

            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();

            foreach ($PersonList as $PersonData) {
                $row++;
                $column = 0;

                $export->setValue($export->getCell($column++, $row), $PersonData['Division']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column, $row), $PersonData['Disease']);

                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setBorderAll();
            }

            $column = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(60);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}