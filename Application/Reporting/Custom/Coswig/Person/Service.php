<?php

namespace SPHERE\Application\Reporting\Custom\Coswig\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class Service
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

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $CountNumber++;
                // Content
                $Item['Number'] = $CountNumber;
//                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['ExcelStreet'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['PhoneNumbersPrivate'] = $Item['ExcelPhoneNumbersPrivate'] = '';
                $Item['PhoneNumbersBusiness'] = $Item['ExcelPhoneNumbersBusiness'] = '';
                $Item['MailAddress'] = $Item['ExcelMailAddress'] = '';

                // Birthday
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
                // Address
                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['ExcelStreet'] = $address->getTblAddress()->getStreetName().' '.$address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                    if ($Item['District'] !== '') {
                        $Pre = substr($Item['District'], 0, 2);
                        if ($Pre != 'OT') {
                            $Item['District'] = 'OT '.$Item['District'];
                        }
                    }
//                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
//                        $address->getTblAddress()->getStreetNumber().', '.
//                        $address->getTblAddress()->getTblCity()->getCode().' '.
//                        $address->getTblAddress()->getTblCity()->getName();
                }

                // PhoneNumbers
                $phoneNumbersPrivate = array();
                $phoneNumbersBusiness = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getName() == 'Privat') {
                            $phoneNumbersPrivate[] = $phone->getTblPhone()->getNumber();
                        } elseif ($phone->getTblType()->getName() == 'Geschäftlich') {
                            $phoneNumbersBusiness[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                    if (!empty( $phoneNumbersPrivate )) {
                        $Item['PhoneNumbersPrivate'] = implode('<br>', $phoneNumbersPrivate);
                        $Item['ExcelPhoneNumbersPrivate'] = $phoneNumbersPrivate;
                    }
                    if (!empty( $phoneNumbersBusiness )) {
                        $Item['PhoneNumbersBusiness'] = implode('<br>', $phoneNumbersBusiness);
                        $Item['ExcelPhoneNumbersBusiness'] = $phoneNumbersBusiness;
                    }
                }

                // E-Mail
                $ContactMailList = array();
                $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblMailList) {
                    foreach ($tblMailList as $tblMail) {
                        if ($tblMail->getTblMail()) {
                            $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                        }
                    }
                    if (!empty( $ContactMailList )) {
                        $Item['MailAddress'] = implode('<br>', $ContactMailList);
                        $Item['ExcelMailAddress'] = $ContactMailList;
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
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 0), "Nachname");
            $export->setValue($export->getCell(1, 0), "Vorname");
            $export->setValue($export->getCell(2, 0), "Geb.-Datum");
            $export->setValue($export->getCell(3, 0), "Straße");
            $export->setValue($export->getCell(4, 0), "PLZ");
            $export->setValue($export->getCell(5, 0), "Ort");
            $export->setValue($export->getCell(6, 0), "Tel. Privat");
            $export->setValue($export->getCell(7, 0), "Tel. Geschäftlich");
            $export->setValue($export->getCell(8, 0), "E-Mail");

            // Table Head
            $export->setStyle($export->getCell(0, 0), $export->getCell(8, 0))
                ->setFontBold()
                ->setBorderAll()
                ->setBorderBottom(2);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, 0))
                ->setFontSize(10);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, 0))
                ->setFontSize(10);


            $Row = 0;
            foreach ($PersonList as $PersonData) {
                $Row++;
                // set border for each Person
                $export->setStyle($export->getCell(0, $Row), $export->getCell(8, $Row))
                    ->setBorderTop();
                $PhonePRow = $PhoneBRow = $MailRow = $DistrictRow = $Row;
                $export->setValue($export->getCell(0, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(1, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(2, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(3, $Row), $PersonData['ExcelStreet']);
                $export->setValue($export->getCell(4, $Row), $PersonData['Code']);
                $export->setValue($export->getCell(5, $Row), $PersonData['City']);

                if (is_array($PersonData['ExcelPhoneNumbersPrivate'])) {
                    foreach ($PersonData['ExcelPhoneNumbersPrivate'] as $PhonePrivate) {
                        $export->setValue($export->getCell(6, $PhonePRow++), $PhonePrivate);
                    }
                }
                if (is_array($PersonData['ExcelPhoneNumbersBusiness'])) {
                    foreach ($PersonData['ExcelPhoneNumbersBusiness'] as $PhoneBusiness) {
                        $export->setValue($export->getCell(7, $PhoneBRow++), $PhoneBusiness);
                    }
                }
                if (is_array($PersonData['ExcelMailAddress'])) {
                    foreach ($PersonData['ExcelMailAddress'] as $Mail) {
                        $export->setValue($export->getCell(8, $MailRow++), $Mail);
                    }
                }
                if (isset( $PersonData['District'] ) && $PersonData['District'] !== '') {
                    $DistrictRow = $DistrictRow + 1;
                    $export->setValue($export->getCell(5, $DistrictRow++), $PersonData['District']);
                }

                if ($Row < ( $PhonePRow - 1 )) {
                    $Row = ( $PhonePRow - 1 );
                }
                if ($Row < ( $PhoneBRow - 1 )) {
                    $Row = ( $PhoneBRow - 1 );
                }
                if ($Row < ( $MailRow - 1 )) {
                    $Row = ( $MailRow - 1 );
                }
                if ($Row < ( $DistrictRow - 1 )) {
                    $Row = ( $DistrictRow - 1 );
                }
            }

            // Table Border
            $export->setStyle($export->getCell(0, 1), $export->getCell(8, $Row))
                ->setFontSize(9)
                ->setBorderVertical()
                ->setBorderLeft()
                ->setBorderRight()
                ->setBorderBottom();
//                ->setBorderAll();

            // Column Width
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(10);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(11);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(13);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(13);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(26);

            $Row++;
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Stand '.date("d.m.Y"));
            $export->setStyle($export->getCell(0, $Row), $export->getCell(1, $Row))->mergeCells();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}