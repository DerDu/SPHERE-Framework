<?php

namespace SPHERE\Application\Reporting\Custom\Herrnhut\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createProfileList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;

                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }

                // Content
                $Item['Count'] = $CountNumber;
                $Item['Number'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Profile'] = 'ohne';

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ( $tblSubject = $tblStudentProfile[0]->getServiceTblSubject() )) {
                        $Item['Profile'] = $tblSubject->getAcronym();
                    }
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createProfileListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name");
            $export->setValue($export->getCell(2, 3), "Vorname");
            $export->setValue($export->getCell(3, 3), "Profil");

            // Settings Header
            $export = $this->setHeader($export, 2, 3, 3);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Profil Liste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(2, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(3, 2), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell(0, $Row), $PersonData['Count']);
                $export->setValue($export->getCell(1, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(3, $Row), $PersonData['Profile']);
            }

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(3, $Row))
                ->setBorderAll();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(34);

            // Center
            $export->setStyle($export->getCell(0, 4), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Weiblich:');
            $export->setValue($export->getCell(2, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Männlich:');
            $export->setValue($export->getCell(2, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Gesamt:');
            $export->setValue($export->getCell(2, $Row), count($tblPersonList));
            $Row++;
            $Row++;
            $Result = $this->countProfile($tblPersonList);
            if (!empty( $Result )) {
                $export->setValue($export->getCell(1, $Row), 'Profile:');
                $Row++;
                foreach ($Result as $Acronym => $Count) {
                    $export->setValue($export->getCell(1, $Row), $Acronym);
                    $export->setValue($export->getCell(2, $Row), $Count);
                    $Row++;
                }
            }

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
    public function createSignList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;

                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }

                // Content
                $Item['Count'] = $CountNumber;
                $Item['Number'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Empty'] = '';
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createSignListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name");
            $export->setValue($export->getCell(2, 3), "Vorname");
            $export->setValue($export->getCell(3, 3), "Unterschrift");

            // Settings Header
            $export = $this->setHeader($export, 2, 3, 3);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Unterschriften Liste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(2, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(3, 2), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell(0, $Row), $PersonData['Count']);
                $export->setValue($export->getCell(1, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(3, $Row), $PersonData['Empty']);
            }

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(3, $Row))
                ->setBorderAll();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(34);

            // Center
            $export->setStyle($export->getCell(0, 4), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Weiblich:');
            $export->setValue($export->getCell(2, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Männlich:');
            $export->setValue($export->getCell(2, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Gesamt:');
            $export->setValue($export->getCell(2, $Row), count($tblPersonList));

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
    public function createLanguageList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }

                // Content
                $Item['Count'] = $CountNumber;
                $Item['Number'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Age'] = '';
                $Item['FS1'] = $Item['FS2'] = $Item['FS3'] = $Item['FS4'] = '';

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
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            $this->setForeignLanguage($tblStudentSubject, $tblDivision, $Item);
                        }
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param TblStudentSubject $tblStudentSubject
     * @param TblDivision $tblDivision
     * @param $Item
     */
    private function setForeignLanguage(TblStudentSubject $tblStudentSubject, TblDivision $tblDivision, &$Item) {
        $tblSubject = $tblStudentSubject->getServiceTblSubject();
        if ($tblSubject && ($ranking = $tblStudentSubject->getTblStudentSubjectRanking())) {
            if (($tblLevel = $tblDivision->getTblLevel())
                && ($level = $tblLevel->getName())
            ) {
                $isSetValue = false;
                if (($fromLevel = $tblStudentSubject->getServiceTblLevelFrom())
                    && ($tillLevel = $tblStudentSubject->getServiceTblLevelTill())
                    && $fromLevel->getName()
                    && $tillLevel->getName()
                    && floatval($fromLevel->getName()) <= floatval($level)
                    && floatval($tillLevel->getName()) >= floatval($level)
                ) {
                    $isSetValue = true;
                } elseif (($fromLevel = $tblStudentSubject->getServiceTblLevelFrom())
                    && $fromLevel->getName()
                    && floatval($fromLevel->getName()) <= floatval($level)
                ) {
                    $isSetValue = true;
                } elseif (($tillLevel = $tblStudentSubject->getServiceTblLevelTill())
                    && $tillLevel->getName()
                    && floatval($tillLevel->getName()) >= floatval($level)
                ) {
                    $isSetValue = true;
                } elseif (!$tblStudentSubject->getServiceTblLevelFrom()
                    && !$tblStudentSubject->getServiceTblLevelTill()
                ) {
                    $isSetValue = true;
                }

                if ($isSetValue) {
                    $Item['FS' . $ranking->getIdentifier()] = $tblSubject->getAcronym();
                }
            }
        }
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createLanguageListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $count['FS1']['Total'] = 0;
            $count['FS2']['Total'] = 0;
            $count['FS3']['Total'] = 0;
            $count['FS4']['Total'] = 0;

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name, Vorname");
            $export->setValue($export->getCell(2, 3), "Anschrift");
            $export->setValue($export->getCell(3, 3), "Geb.-datum");
            $export->setValue($export->getCell(4, 3), "Geburtsort");
            $export->setValue($export->getCell(5, 3), "FS 1");
            $export->setValue($export->getCell(6, 3), "FS 2");
            $export->setValue($export->getCell(7, 3), "FS 3");
            $export->setValue($export->getCell(8, 3), "FS 4");

            //Settings Header
            $export = $this->setHeader($export, 3, 5, 8);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Klassenliste - Fremdsprachen');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(3, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell(0, $Row), $PersonData['Count']);
                $export->setValue($export->getCell(1, $Row), $PersonData['LastName'].', '.$PersonData['FirstName']);
                if (isset( $PersonData['StreetName'] ) && $PersonData['StreetName'] !== '' && isset( $PersonData['City'] ) && $PersonData['City'] !== '') {
                    $export->setValue($export->getCell(2, $Row),
                        ( $PersonData['District'] !== '' ? $PersonData['District'].' ' : '' ).
                        $PersonData['StreetName'].' '.$PersonData['StreetNumber'].', '.
                        $PersonData['Code'].' '.$PersonData['City']);
                }
                $export->setValue($export->getCell(3, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(4, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell(5, $Row), $PersonData['FS1']);
                $export->setValue($export->getCell(6, $Row), $PersonData['FS2']);
                $export->setValue($export->getCell(7, $Row), $PersonData['FS3']);
                $export->setValue($export->getCell(8, $Row), $PersonData['FS4']);


                for ($i = 1; $i < 5; $i++) {
                    if (isset($PersonData['FS' . $i]) && $PersonData['FS' . $i] != '') {
                        $count['FS' . $i]['Total']++;
                        if (isset($count['FS' . $i]['Subjects'][$PersonData['FS' . $i]])) {
                            $count['FS' . $i]['Subjects'][$PersonData['FS' . $i]]++;
                        } else {
                            $count['FS' . $i]['Subjects'][$PersonData['FS' . $i]] = 1;
                        }
                    }
                }
            }

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(8, $Row))
                ->setBorderAll();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(40);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(4);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(4);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(4);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(4);

            // Center
            $export->setStyle($export->getCell(0, 4), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Weiblich:');
            $export->setValue($export->getCell(2, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Männlich:');
            $export->setValue($export->getCell(2, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Gesamt:');
            $export->setValue($export->getCell(2, $Row), count($tblPersonList));

            $Row -= 3;

            foreach ($count as $ranking => $list) {
                $Row++;
                $export->setValue($export->getCell(3, $Row), 'Fremdsprache ' . str_replace('FS', '', $ranking));
                $export->setStyle($export->getCell(3, $Row), $export->getCell(4, $Row))
                    ->mergeCells()
                    ->setFontBold();
                if (isset($list['Subjects'])) {
                    ksort($list['Subjects']);
                    foreach ($list['Subjects'] as $acronym => $countValue) {
                        $Row++;
                        $tblSubject = Subject::useService()->getSubjectByAcronym($acronym);
                        $export->setValue($export->getCell(3, $Row), ($tblSubject ? $tblSubject->getName() : $acronym) . ':');
                        $export->setValue($export->getCell(4, $Row), $countValue);
                    }
                }
            }

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
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }
                // Content
                $Item['Number'] = $CountNumber;
                $Item['Count2'] = $CountNumber;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
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
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name, Vorname");
            $export->setValue($export->getCell(2, 3), "lfdNr.");
            $export->setValue($export->getCell(3, 3), "Geburtsdatum");
            $export->setValue($export->getCell(4, 3), "Geburtsort");
            $export->setValue($export->getCell(5, 3), "Wohnanschrift");

            //Settings Header
            $export = $this->setHeader($export, 4, 5, 5);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Klassenliste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(4, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
                }
                $Row++;

                $export->setValue($export->getCell(0, $Row), $PersonData['Number']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Name']);
                $export->setValue($export->getCell(2, $Row), $PersonData['Count2']);
                $export->setValue($export->getCell(3, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(4, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell(5, $Row), $PersonData['Address']);
            }

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(5, $Row))
                ->setBorderAll();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(30);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(45);

            // Center
            $export->setStyle($export->getCell(0, 3), $export->getCell(0, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(2, 3), $export->getCell(2, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Weiblich:');
            $export->setValue($export->getCell(2, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Männlich:');
            $export->setValue($export->getCell(2, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Gesamt:');
            $export->setValue($export->getCell(2, $Row), count($tblPersonList));

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
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }
                // Content
                $Item['Number'] = $CountNumber;
                $Item['Count'] = $CountNumber;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['PhoneNumbers'] = '';
                $Item['ExcelPhoneNumbers'] = '';
                $Item['Parents'] = '';
                $Item['ExcelParants'] = '';
                $Item['Email'] = '';
                $Item['ExcelEmail'] = '';
                $Item['Entrance'] = '';
                $Item['Leaving'] = '';

                $Father = null;
                $Mother = null;
                $FatherPhoneList = false;
                $MotherPhoneList = false;
                $FatherMailList = false;
                $MotherMailList = false;
                $MailListing = false;

                // Parent's
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getId() == 1) {
                            if ($Father === null) {
                                $Father = $guardian->getServiceTblPersonFrom();
                                if ($Father) {
                                    $Item['Parents'] .= $Father->getFirstName().' '.$Father->getLastName();
                                    $Item['ExcelParants'][] = $Father->getFirstName().' '.$Father->getLastName();
                                    $FatherPhoneList = Phone::useService()->getPhoneAllByPerson($Father);
                                    $FatherMailList = Mail::useService()->getMailAllByPerson($Father);
                                }
                            } else {
                                $Mother = $guardian->getServiceTblPersonFrom();
                                if ($Mother) {
                                    if ($Item['Parents'] != '') {
                                        $Item['Parents'] .= ', '.$Mother->getFirstName().' '.$Mother->getLastName();
                                    } else {
                                        $Item['Parents'] .= $Mother->getFirstName().' '.$Mother->getLastName();
                                    }
                                    $Item['ExcelParants'][] = $Mother->getFirstName().' '.$Mother->getLastName();
                                    $MotherPhoneList = Phone::useService()->getPhoneAllByPerson($Mother);
                                    $MotherMailList = Mail::useService()->getMailAllByPerson($Mother);
                                }
                            }
                        }
                    }
                }

                // PhoneNumbers
                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $MailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber(); // .' '.$phone->getTblType()->getName(); // Type Raus
//                        if ($phone->getRemark()) {
//                            $phoneNumbers[] = $phone->getRemark();    // Remark Raus
//                        }
                    }
                }
                if ($MailList) {
                    foreach ($MailList as $Mail) {
                        $MailListing[] = $Mail->getTblMail()->getAddress();
                    }
                }
                if ($FatherPhoneList) {
                    foreach ($FatherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
//                            $type = $phone->getTblType()->getName() == "Geschäftlich" ? "Geschäftl." : $phone->getTblType()->getName();
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber(); //.' '.$type; // Type Raus
//                            if ($phone->getRemark()) {
//                                $phoneNumbers[] = $phone->getRemark();    // Remark Raus
//                            }
                        }
                    }
                }
                if ($FatherMailList) {
                    /** @var TblToPerson $Mail */
                    foreach ($FatherMailList as $Mail) {
                        $MailListing[] = $Mail->getTblMail()->getAddress();
                    }
                }
                if ($MotherPhoneList) {
                    foreach ($MotherPhoneList as $phone) {
                        if ($phone->getServiceTblPerson()) {
//                            $type = $phone->getTblType()->getName() == "Geschäftlich" ? "Geschäftl." : $phone->getTblType()->getName();
                            $phoneNumbers[] = $phone->getTblPhone()->getNumber(); //.' '.$type; // Type Raus
//                            if ($phone->getRemark()) {
//                                $phoneNumbers[] = $phone->getRemark();    // Remark Raus
//                            }
                        }
                    }
                }
                if ($MotherMailList) {
                    /** @var TblToPerson $Mail */
                    foreach ($MotherMailList as $Mail) {
                        $MailListing[] = $Mail->getTblMail()->getAddress();
                    }
                }

                if (!empty( $MailListing )) {
                    $Item['Email'] = implode('<br>', $MailListing);
                    $Item['ExcelEmail'] = $MailListing;
                }

                if (!empty( $phoneNumbers )) {
                    $Item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $Item['ExcelPhoneNumbers'] = $phoneNumbers;
                }

                // Entrance & Leaving
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'); // Aufnahme
                    if ($tblStudentTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType);
                        if ($tblStudentTransfer) {
                            if ($tblStudentTransfer) {
                                $Item['Entrance'] = $tblStudentTransfer->getTransferDate();
                            }
                        }
                    }

                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'); // Abgabe
                    if ($tblStudentTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType);
                        if ($tblStudentTransfer) {
                            $Item['Leaving'] = $tblStudentTransfer->getTransferDate();
                        }
                    }
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createExtendedClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name");
            $export->setValue($export->getCell(2, 3), "Telefon-Nr.");
            $export->setValue($export->getCell(3, 3), "E-Mail");
            $export->setValue($export->getCell(4, 3), "Erziehungsberechtigte");
            $export->setValue($export->getCell(5, 3), "Zugang");
            $export->setValue($export->getCell(6, 3), "Abgang");

            //Settings Header
            $export = $this->setHeader($export, 4, 5, 6);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Erweiterte Klassenliste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(4, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
                }
                $Row++;
                $RowEmail = $RowParent = $RowPhone = $Row;
                $export->setValue($export->getCell(0, $Row), $PersonData['Count']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Name']);
                $export->setValue($export->getCell(5, $Row), $PersonData['Entrance']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Leaving']);

                if (!empty( $PersonData['ExcelPhoneNumbers'] )) {
                    foreach ($PersonData['ExcelPhoneNumbers'] as $Phone) {
                        $export->setValue($export->getCell(2, $RowPhone++), $Phone);
                    }
                }
                if (!empty( $PersonData['ExcelEmail'] )) {
                    foreach ($PersonData['ExcelEmail'] as $Mail) {
                        $export->setValue($export->getCell(3, $RowEmail++), $Mail);
                    }
                }
                if (!empty( $PersonData['ExcelParants'] )) {
                    foreach ($PersonData['ExcelParants'] as $Parent) {
                        $export->setValue($export->getCell(4, $RowParent++), $Parent);
                    }
                }

                if ($RowPhone > $Row) {
                    $Row = ( $RowPhone - 1 );
                }
                if ($RowEmail > $Row) {
                    $Row = ( $RowEmail - 1 );
                }
                if ($RowParent > $Row) {
                    $Row = ( $RowParent - 1 );
                }

                $export->setStyle($export->getCell(0, $Row), $export->getCell(6, $Row))
                    ->setBorderBottom();
            }

            // TableBorder
//            $export->setStyle($export->getCell(0, ($Start + 1)), $export->getCell(7, $Row))
//                ->setBorderAll();
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(0, $Row))
                ->setBorderLeft();
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(6, $Row))
                ->setBorderVertical();
            $export->setStyle($export->getCell(6, ( $Start + 1 )), $export->getCell(6, $Row))
                ->setBorderRight();
            $export->setStyle($export->getCell(0, $Row), $export->getCell(6, $Row))
                ->setBorderBottom();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(21);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(24);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(21);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(12);

            // Center
            $export->setStyle($export->getCell(0, 3), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Weiblich:');
            $export->setValue($export->getCell(2, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Männlich:');
            $export->setValue($export->getCell(2, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(1, $Row), 'Gesamt:');
            $export->setValue($export->getCell(2, $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    private function countProfile($tblPersonList)
    {
        $result = array();
        if (empty( $tblPersonList )) {
            return $result;
        } else {
            foreach ($tblPersonList as $tblPerson) {
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ( $tblSubject = $tblStudentProfile[0]->getServiceTblSubject() )) {
                        if (!isset( $result[$tblSubject->getAcronym()] )) {
                            $result[$tblSubject->getAcronym()] = 1;
                        } else {
                            $result[$tblSubject->getAcronym()] += 1;
                        }
                    }
                }
            }
            return $result;
        }
    }

    /**
     * @param array  $tblPersonList
     * @param string $SubjectType "Identifier"
     *
     * @return array
     */
    private function countSubject($tblPersonList, $SubjectType)
    {

        $result = array();
        if (empty( $tblPersonList )) {
            return $result;
        } else {
            $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier($SubjectType);
            foreach ($tblPersonList as $tblPerson) {
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getServiceTblSubject()) {
                                if (!isset( $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] )) {
                                    $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] = 1;
                                } else {
                                    $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] += 1;
                                }
                                // sort Subject
                                ksort($result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()]);
                            }
                        }
                        if (!empty( $result )) {
                            // sort Number of SubjectList (Wahlfach1, Wahlfach2 etc.)
                            ksort($result);
                        }
                    }
                }
            }
            return $result;
        }
    }

    /**
     * @param PhpExcel $export
     * @param          $secondColumn
     * @param          $thirdColumn
     * @param          $lastColumn
     *
     * @return PhpExcel
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     */
    private function setHeader(PhpExcel $export, $secondColumn, $thirdColumn, $lastColumn)
    {

        // Merge & Style
        $export->setStyle($export->getCell(0, 0), $export->getCell($lastColumn, 0))
            ->mergeCells()
            ->setFontSize(18)
            ->setFontBold();
        $export->setStyle($export->getCell(0, 1), $export->getCell($lastColumn, 1))
            ->mergeCells()
            ->setFontSize(14)
            ->setBorderOutline();
        $export->setStyle($export->getCell(0, 2), $export->getCell(( $secondColumn - 1 ), 2))
            ->mergeCells();
        $export->setStyle($export->getCell($secondColumn, 2), $export->getCell(( $thirdColumn - 1 ), 2))
            ->setAlignmentCenter()
            ->mergeCells();
        $export->setStyle($export->getCell($thirdColumn, 2), $export->getCell($lastColumn, 2))
            ->setAlignmentCenter()
            ->mergeCells();

        //Border
        $export->setStyle($export->getCell(0, 0), $export->getCell($lastColumn, 0))->setBorderOutline();
        $export->setStyle($export->getCell(0, 1), $export->getCell($lastColumn, 1))->setBorderOutline();
        $export->setStyle($export->getCell(0, 2), $export->getCell($lastColumn, 2))->setBorderOutline();
        $export->setStyle($export->getCell(0, 3), $export->getCell($lastColumn, 3))
            ->setBorderAll()
            ->setBorderBottom(2)
            ->setFontBold();
        return $export;
    }
}