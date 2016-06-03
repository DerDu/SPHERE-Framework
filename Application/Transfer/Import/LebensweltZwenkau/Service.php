<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.06.2016
 * Time: 14:23
 */

namespace SPHERE\Application\Transfer\Import\LebensweltZwenkau;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\People\Group\Group as PersonGroup;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service extends Extension
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createPersonsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'PNr' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geburtsname' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Straße' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Konfession' => null,
                    'Aufnahmedatum' => null,
                    'Klassengruppe' => null,
                    'Klassenstufe' => null,
                    'Telefonnumer privat' => null,
                    'E-Mail' => null,
                    'Aufnahmedatum 2' => null,
                    'Mitgliedsnummer' => null,
                    'VKS1' => null,
                    'VKS2' => null,
                    'VKS3' => null,
                    'M' => null,
                    'V' => null,
                    'Mitglied' => null,
                    'Mitarbeiter' => null,
                    'Spender' => null,
                    'agm' => null,
                    'ags' => null,
                    'esp' => null,
                    'Inst' => null,
                    'Geschlecht' => null,
                    'Nationalität' => null,
                    'Abgangsdatum' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countPerson = 0;
                    $countCompany = 0;

                    // create/get schoolYear
                    $year = 15;
                    $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
                    if ($tblYear) {
                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                        if (!$tblPeriodList) {
                            // firstTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '1. Halbjahr',
                                '01.08.20' . $year,
                                '31.01.20' . ($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }

                            // secondTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '2. Halbjahr',
                                '01.02.20' . ($year + 1),
                                '31.07.20' . ($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }
                        }
                    }

                    $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule

                    // create Groups
                    $tblFormerClubGroup = PersonGroup::useService()->insertGroup('Ehemalige Vereinsmitglieder');
                    $tblFormerDonorGroup = PersonGroup::useService()->insertGroup('Ehemalige Spender');
                    $tblFormerStudentGroup = PersonGroup::useService()->insertGroup('Ehemalige Schüler');
                    $tblDonorGroup = PersonGroup::useService()->insertGroup('Spender');
                    $tblEagleGroup = PersonGroup::useService()->insertGroup('Adler');
                    $tblDolphinGroup = PersonGroup::useService()->insertGroup('Delfin');
                    $tblTigerGroup = PersonGroup::useService()->insertGroup('Tiger');

                    $tblCustodyGroup = PersonGroup::useService()->getGroupByMetaTable('CUSTODY');

                    $error = array();
                    $tblPersonList = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        list($countPerson, $countCompany, $RunY, $tblPersonList, $error) = $this->processRow(
                            $Document,
                            $Location,
                            $RunY,
                            $countPerson,
                            $countCompany,
                            $tblYear,
                            $tblFormerClubGroup,
                            $tblSchoolType,
                            $tblFormerDonorGroup,
                            $tblFormerStudentGroup,
                            $tblDonorGroup,
                            $tblEagleGroup,
                            $tblDolphinGroup,
                            $tblTigerGroup,
                            $tblCustodyGroup,
                            $error,
                            $tblPersonList
                        );

                    }

                    return
                        new Success('Es wurden ' . $countPerson . ' Personen erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countCompany . ' Firmen erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    private function processRow(
        PhpExcel $Document,
        $Location,
        $RunY,
        $countPerson,
        $countCompany,
        $tblYear,
        $tblFormerClubGroup,
        $tblSchoolType,
        $tblFormerDonorGroup,
        $tblFormerStudentGroup,
        $tblDonorGroup,
        $tblEagleGroup,
        $tblDolphinGroup,
        $tblTigerGroup,
        $tblCustodyGroup,
        $error,
        $tblPersonList
    ) {
        set_time_limit(300);

        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
        if ($firstName !== '' && $lastName !== '') {
            /*
             * Person
             */
            $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
            $division = trim($Document->getValue($Document->getCell($Location['Klassengruppe'],
                $RunY)));
            $tblSalutation = false;
            if ($division !== '') {
                $tblSalutation = Person::useService()->getSalutationById(3);  // Schüler
            } elseif ($gender !== '') {
                if (strtolower($gender) == 'männlich') {
                    $tblSalutation = Person::useService()->getSalutationById(1);
                } elseif (strtolower($gender) == 'weiblich') {
                    $tblSalutation = Person::useService()->getSalutationById(2);
                }
            }
            $tblPerson = Person::useService()->insertPerson(
                $tblSalutation ? $tblSalutation : null,
                '',
                $firstName,
                '',
                $lastName,
                array(
                    0 => PersonGroup::useService()->getGroupByMetaTable('COMMON'),
                )
            );
            if ($tblPerson) {
                $countPerson++;
                $personNumber = trim($Document->getValue($Document->getCell($Location['PNr'], $RunY)));
                if ($personNumber !== '') {
                    $tblPersonList[$personNumber] = $tblPerson->getId();
                }

                /*
                 * Common
                 */
                $birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                    $RunY)));
                if ($birthday !== '') {
                    $birthdayDate = date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                } else {
                    $birthdayDate = '';
                }
                if (strtolower($gender) == 'männlich') {
                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                } elseif (strtolower($gender) == 'weiblich') {
                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                } else {
                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                }
                Common::useService()->insertMeta(
                    $tblPerson,
                    $birthdayDate,
                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                    $gender,
                    trim($Document->getValue($Document->getCell($Location['Nationalität'], $RunY))),
                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                    '',
                    ''
                );

                /*
                 * Custody
                 */
                $father = trim($Document->getValue($Document->getCell($Location['V'],
                    $RunY)));
                $mother = trim($Document->getValue($Document->getCell($Location['M'],
                    $RunY)));
                if ($mother === '1') {
                    $remark = 'Mutter';
                } elseif ($father === '1') {
                    $remark = 'Vater';
                } else {
                    $remark = '';
                }
                $custody1 = trim($Document->getValue($Document->getCell($Location['VKS1'],
                    $RunY)));
                if ($custody1 !== '') {
                    if (isset($tblPersonList[$custody1])) {
                        Relationship::useService()->insertRelationshipToPerson(
                            $tblPerson,
                            Person::useService()->getPersonById($tblPersonList[$custody1]),
                            Relationship::useService()->getTypeById(1), // Sorgeberechtigt
                            $remark
                        );
                        PersonGroup::useService()->addGroupPerson($tblCustodyGroup, $tblPerson);
                    } else {
                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Beziehung konnte nicht hinzugefügt werden, da die Person nicht gefunden wurde.';
                    }
                }
                $custody2 = trim($Document->getValue($Document->getCell($Location['VKS2'],
                    $RunY)));
                if ($custody2 !== '') {
                    if (isset($tblPersonList[$custody2])) {
                        Relationship::useService()->insertRelationshipToPerson(
                            $tblPerson,
                            Person::useService()->getPersonById($tblPersonList[$custody2]),
                            Relationship::useService()->getTypeById(1), // Sorgeberechtigt
                            $remark
                        );
                        PersonGroup::useService()->addGroupPerson($tblCustodyGroup, $tblPerson);
                    } else {
                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Beziehung konnte nicht hinzugefügt werden, da die Person nicht gefunden wurde.';
                    }
                }
                $custody3 = trim($Document->getValue($Document->getCell($Location['VKS3'],
                    $RunY)));
                if ($custody3 !== '') {
                    if (isset($tblPersonList[$custody3])) {
                        Relationship::useService()->insertRelationshipToPerson(
                            $tblPerson,
                            Person::useService()->getPersonById($tblPersonList[$custody3]),
                            Relationship::useService()->getTypeById(1), // Sorgeberechtigt
                            $remark
                        );
                        PersonGroup::useService()->addGroupPerson($tblCustodyGroup, $tblPerson);
                    } else {
                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Beziehung konnte nicht hinzugefügt werden, da die Person nicht gefunden wurde.';
                    }
                }

                /*
                 * Address
                 */
                $streetName = '';
                $streetNumber = '';
                $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                    $RunY)));
                if (preg_match_all('!\d+!', $street, $matches)) {
                    $pos = strpos($street, $matches[0][0]);
                    if ($pos !== null) {
                        $streetName = trim(substr($street, 0, $pos));
                        $streetNumber = trim(substr($street, $pos));
                    }
                }
                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                $cityDistrict = '';
                $pos = strpos($cityName, " OT ");
                if ($pos !== false) {
                    $cityDistrict = trim(substr($cityName, $pos));
                    $cityName = trim(substr($cityName, 0, $pos));
                }
                $zipCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                if ($streetName !== '' && $streetNumber !== '' && $cityName !== '' && $zipCode !== '') {
                    Address::useService()->insertAddressToPerson(
                        $tblPerson,
                        $streetName,
                        $streetNumber,
                        $zipCode,
                        $cityName,
                        $cityDistrict,
                        ''
                    );
                } else {
                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse konnte nicht zur Person hinzugefügt werden.';
                }

                /*
                 * Division && Student
                 */
                $level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'],
                    $RunY)));
                if ($division !== '' && $level !== '') {
                    $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                    if ($tblLevel && $tblYear) {
                        if ($division == 'Adler'){
                            PersonGroup::useService()->addGroupPerson($tblEagleGroup, $tblPerson);
                        } elseif ($division == 'Tiger'){
                            PersonGroup::useService()->addGroupPerson($tblTigerGroup, $tblPerson);
                        } elseif ($division == 'Delfin'){
                            PersonGroup::useService()->addGroupPerson($tblDolphinGroup, $tblPerson);
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Gruppe (Klassengruppe) nicht gefunden.';
                        }

                        $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel, '');
                        if ($tblDivision) {
                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                            PersonGroup::useService()->addGroupPerson(
                                PersonGroup::useService()->getGroupByMetaTable('STUDENT'),
                                $tblPerson
                            );

                            $arriveDate = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum'],
                                $RunY)));
                            if ($arriveDate !== '') {
                                $arriveDate = date('Y-m-d',
                                    \PHPExcel_Shared_Date::ExcelToPHP($arriveDate));
                                $tblStudent = Student::useService()->insertStudent(
                                    $tblPerson, ''
                                );
                                if ($tblStudent) {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        null,
                                        null,
                                        $arriveDate,
                                        ''
                                    );
                                }
                            }
                        }
                    }
                }

                /*
                 * Groups
                 */
                $formerClubMember = trim($Document->getValue($Document->getCell($Location['agm'],
                    $RunY)));
                if ($formerClubMember === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        $tblFormerClubGroup,
                        $tblPerson
                    );

                    $clubNumber = trim($Document->getValue($Document->getCell($Location['Mitgliedsnummer'],
                        $RunY)));
                    $clubEntryDate = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum 2'],
                        $RunY)));
                    if ($clubEntryDate !== '') {
                        $clubEntryDate = date('Y-m-d',
                            \PHPExcel_Shared_Date::ExcelToPHP($clubEntryDate));
                    } else {
                        $clubEntryDate = '';
                    }
                    $clubExitDate = trim($Document->getValue($Document->getCell($Location['Abgangsdatum'],
                        $RunY)));
                    $remark = '';
                    if ($clubExitDate !== '') {
                        $clubExitDate = date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($clubExitDate));
                        if (!$clubExitDate) {
                            $clubExitDate = '';
                            $remark = trim($Document->getValue($Document->getCell($Location['Abgangsdatum'],
                                $RunY)));
                        }
                    } else {
                        $clubExitDate = '';
                    }
                    Club::useService()->insertMeta(
                        $tblPerson,
                        $clubNumber,
                        $clubEntryDate,
                        $clubExitDate,
                        $remark
                    );
                }
                $formerStudent = trim($Document->getValue($Document->getCell($Location['ags'],
                    $RunY)));
                if ($formerStudent === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        $tblFormerStudentGroup,
                        $tblPerson
                    );

                    $tblStudent = Student::useService()->insertStudent(
                        $tblPerson, ''
                    );

                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum'],
                        $RunY)));
                    if ($arriveDate !== '') {
                        $arriveDate = date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($arriveDate));

                        if ($tblStudent) {
                            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                            Student::useService()->insertStudentTransfer(
                                $tblStudent,
                                $tblStudentTransferType,
                                null,
                                null,
                                null,
                                $arriveDate,
                                ''
                            );
                        }
                    }

                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Abgangsdatum'],
                        $RunY)));
                    if ($leaveDate !== '') {
                        $leaveDate = date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($leaveDate));

                        if ($tblStudent) {
                            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
                            Student::useService()->insertStudentTransfer(
                                $tblStudent,
                                $tblStudentTransferType,
                                null,
                                null,
                                null,
                                $leaveDate,
                                ''
                            );
                        }
                    }
                }
                $formerDonor = trim($Document->getValue($Document->getCell($Location['esp'],
                    $RunY)));
                if ($formerDonor === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        $tblFormerDonorGroup,
                        $tblPerson
                    );
                }
                $clubMember = trim($Document->getValue($Document->getCell($Location['Mitglied'],
                    $RunY)));
                if ($clubMember === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        PersonGroup::useService()->getGroupByMetaTable('CLUB'),
                        $tblPerson
                    );

                    $clubNumber = trim($Document->getValue($Document->getCell($Location['Mitgliedsnummer'],
                        $RunY)));
                    $clubEntryDate = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum 2'],
                        $RunY)));
                    if ($clubEntryDate !== '') {
                        $clubEntryDate = date('Y-m-d',
                            \PHPExcel_Shared_Date::ExcelToPHP($clubEntryDate));
                    } else {
                        $clubEntryDate = '';
                    }
                    Club::useService()->insertMeta(
                        $tblPerson,
                        $clubNumber,
                        $clubEntryDate
                    );
                }
                $staffMember = trim($Document->getValue($Document->getCell($Location['Mitarbeiter'],
                    $RunY)));
                if ($staffMember === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        PersonGroup::useService()->getGroupByMetaTable('STAFF'),
                        $tblPerson
                    );
                }
                $donorMember = trim($Document->getValue($Document->getCell($Location['Spender'],
                    $RunY)));
                if ($donorMember === '1') {
                    PersonGroup::useService()->addGroupPerson(
                        $tblDonorGroup,
                        $tblPerson
                    );
                }

                /*
                 * Phone
                 */
                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefonnumer privat'],
                    $RunY)));
                if ($phoneNumber !== '') {
                    $phoneNumberList = explode(';', $phoneNumber);
                    foreach ($phoneNumberList as $phone) {
                        $phone = trim($phone);
                        $tblType = Phone::useService()->getTypeById(1);
                        if (0 === strpos($phone, '01')) {
                            $tblType = Phone::useService()->getTypeById(2);
                        }
                        Phone::useService()->insertPhoneToPerson(
                            $tblPerson,
                            $phone,
                            $tblType,
                            ''
                        );
                    }
                }

                /*
                 * Mail
                 */
                $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail'],
                    $RunY)));
                if ($mailAddress !== '') {
                    $mailAddressList = explode(';', $mailAddress);
                    foreach ($mailAddressList as $address) {
                        $address = trim($address);
                        $tblType = Mail::useService()->getTypeById(1);
                        Mail::useService()->insertMailToPerson(
                            $tblPerson,
                            $address,
                            $tblType,
                            ''
                        );
                    }
                }
            }
        } elseif ($lastName !== '' && $firstName === '') {
            /*
             * Company
             */
            $tblCompany = Company::useService()->insertCompany(
                $lastName,
                trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
            );
            if ($tblCompany) {

                $countCompany++;

                CompanyGroup::useService()->addGroupCompany(
                    CompanyGroup::useService()->getGroupByMetaTable('COMMON'),
                    $tblCompany
                );

                /*
                 * Address
                 */
                $streetName = '';
                $streetNumber = '';
                $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                    $RunY)));
                if (preg_match_all('!\d+!', $street, $matches)) {
                    $pos = strpos($street, $matches[0][0]);
                    if ($pos !== null) {
                        $streetName = trim(substr($street, 0, $pos));
                        $streetNumber = trim(substr($street, $pos));
                    }
                }
                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                $cityDistrict = '';
                $pos = strpos($cityName, " OT ");
                if ($pos !== false) {
                    $cityDistrict = trim(substr($cityName, $pos));
                    $cityName = trim(substr($cityName, 0, $pos));
                }
                $zipCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                if ($streetName !== '' && $streetNumber !== '' && $cityName !== '' && $zipCode !== '') {
                    Address::useService()->insertAddressToCompany(
                        $tblCompany,
                        $streetName,
                        $streetNumber,
                        $zipCode,
                        $cityName,
                        $cityDistrict,
                        ''
                    );
                } else {
                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse konnte nicht zur Firma hinzugefügt werden.';
                }

                /*
                 * Phone
                 */
                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefonnumer privat'],
                    $RunY)));
                if ($phoneNumber !== '') {
                    Phone::useService()->insertPhoneToCompany(
                        $tblCompany,
                        $phoneNumber,
                        Phone::useService()->getTypeById(1),
                        trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
                    );
                }

                /*
                 * Mail
                 */
                $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail'],
                    $RunY)));
                if ($mailAddress !== '') {
                    Mail::useService()->insertMailToCompany(
                        $tblCompany,
                        $mailAddress,
                        Mail::useService()->getTypeById(2),
                        trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
                    );
                }
            } else {
                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Firma wurde nicht hinzugefügt.';
            }

        } else {
            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person/Firma wurde nicht hinzugefügt.';
        }

        return array(
            $countPerson,
            $countCompany,
            $RunY,
            $tblPersonList,
            $error
        );
    }

}