<?php
namespace SPHERE\Application\Reporting\Custom\Schneeberg\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Schneeberg\Person
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
        if (!empty( $tblPersonList )) {

            $count = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Birthday'] = '';
                $Item['Street'] = '';
                $Item['ZipCode'] = '';
                $Item['City'] = '';
                $Item['Parents'] = '';
                $Item['Phone'] = '';
                $Item['PhoneMother'] = '';
                $Item['PhoneFather'] = '';
                $Item['Photo'] = '';

                $Item['Number'] = ++$count;

                $father = false;
                $mother = false;
                $fatherPhoneList = false;
                $motherPhoneList = false;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getId() == 1 && $guardian->getServiceTblPersonFrom()) {
                            if ($guardian->getServiceTblPersonFrom()->getTblSalutation()) {
                                if ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1) {
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
                            } else {
                                if (!$father) {
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
                }

                if (( $address = $tblPerson->fetchMainAddress() )) {
                    $Item['Street'] = $address->getStreetName() . ' ' . $address->getStreetNumber();
                    if ($address->getTblCity()) {
                        $Item['ZipCode'] = $address->getTblCity()->getCode();
                        $Item['City'] = $address->getTblCity()->getDisplayName();
                    }
                }

                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                if ($father && $mother){
                    $Item['Parents'] = $mother->getFirstSecondName() .
                        ($father->getLastName() == $mother->getLastName() ? '' : ' ' . $mother->getLastName())
                        . ' & ' . $father->getFirstSecondName() . ' ' . $father->getLastName();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }

                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                    }
                    if (!empty($phoneNumbers)){
                        $Item['Phone'] = implode(', ', $phoneNumbers);
                    }
                }

                $phoneNumbers = array();
                if ($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                    }
                    if (!empty($phoneNumbers)){
                        $Item['PhoneFather'] = implode(', ', $phoneNumbers);
                    }
                }

                $phoneNumbers = array();
                if ($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                    }
                    if (!empty($phoneNumbers)){
                        $Item['PhoneMother'] = implode(', ', $phoneNumbers);
                    }
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent){
                    $tblStudentAgreementAllByStudent = Student::useService()->getStudentAgreementAllByStudent($tblStudent);
                    if ($tblStudentAgreementAllByStudent){
                        foreach ($tblStudentAgreementAllByStudent as $tblStudentAgreement){
                            if ($tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId() == 1 ){
                                $Item['Photo'] = 'X';
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
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))->setFontBold();

            $row = 0;
            $column = 1;
            $export->setValue($export->getCell($column++, $row), 'Name');
            $export->setValue($export->getCell($column++, $row), 'Vorname');
            $export->setValue($export->getCell($column++, $row), 'Geburtstag');
            $export->setValue($export->getCell($column++, $row), 'Straße');
            $export->setValue($export->getCell($column++, $row), 'PLZ');
            $export->setValue($export->getCell($column++, $row), 'Ort');
            $export->setValue($export->getCell($column++, $row), 'Eltern');
            $export->setValue($export->getCell($column++, $row), 'Telefon privat');
            $export->setValue($export->getCell($column++, $row), 'Mutter');
            $export->setValue($export->getCell($column++, $row), 'Vater');
            $export->setValue($export->getCell($column, $row), 'FOTO');

            foreach ($PersonList as $PersonData) {
                $row++;
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Street']);
                $export->setValue($export->getCell($column++, $row), $PersonData['ZipCode']);
                $export->setValue($export->getCell($column++, $row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Parents']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Phone']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMother']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFather']);
                $export->setValue($export->getCell($column, $row), $PersonData['Photo']);
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}