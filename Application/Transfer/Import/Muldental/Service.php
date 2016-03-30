<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.03.2016
 * Time: 10:40
 */

namespace SPHERE\Application\Transfer\Import\Muldental;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(
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
                    'Kl.' => null,
                    'Stammgruppe' => null,
                    'Schulart' => null,
                    'Geschlecht' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Wohnort' => null,
                    'Ortsteil' => null,
                    'Landkreis' => null,
                    'E-Mail Mutter' => null,
                    'privat Mutter und Vater' => null,
                    'dienstlich Mutter' => null,
                    'Mutter' => null,
                    'Vater' => null,
                    'Geburtsd.' => null,
                    'Geburtsort' => null,
                    'Krankenkasse' => null,
                    'Name Mutter' => null,
                    'Name Vater' => null,
                    'Fotoerlaubis abgegeben' => null,
                    'Fotoerlaubnis Einzelbestandteile' => null,
                    'Bildungsgang' => null,
                    'FS1' => null,
                    'FS2' => null,
                    'Neigungskurs OS' => null,
                    'Neigungskursbereich' => null,
                    'Religionsunterricht' => null,
                    'Tel. 5 Schüler(Bem. Aus freien Feldern)' => null,
                    'Schulabgang am' => null,
                    'Aufnahme am' => null,
                    'von welcher Schule ID' => null,
                    'auf welche Schule_ID' => null,
                    'Einschulung am' => null,
                    'Geschw.' => null,
                    'Schüler_Integr_Förderschüler' => null,
                    'Konfession' => null,
                    'Staat' => null,
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
                    $countStudent = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $mainGroup = trim($Document->getValue($Document->getCell($Location['Stammgruppe'], $RunY)));
                            $mainGroup = $mainGroup !== '' ? Group::useService()->insertGroup($mainGroup) : false;  // ToDo JohK Gruppenlehrer Mentor in Beschreibung

                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                // Stammgruppe
                                if ($mainGroup) {
                                    Group::useService()->addGroupPerson($mainGroup, $tblPerson);
                                }

                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                $cityName = trim($Document->getValue($Document->getCell($Location['Wohnort'], $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                    $RunY)));
                                // ToDo JohK Landkreis

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsd.'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Staat'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // division
                                $tblDivision = false;
                                $year = 15;
                                $division = trim($Document->getValue($Document->getCell($Location['Kl.'],
                                    $RunY)));
                                $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
                                $tblSchoolType = false;
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

                                    $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                    if ($division > 4) {
                                        $type = trim($Document->getValue($Document->getCell($Location['Schulart'],
                                            $RunY)));
                                        if ($type == 'OS') {
                                            $tblSchoolType = Type::useService()->getTypeById(8); // Mittelschule / Oberschule
                                        } elseif ($type == 'Gym') {
                                            $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                        } else {
                                            $tblSchoolType = false;
                                        }
                                    }
                                    if ($tblSchoolType) {
                                        $tblLevel = Division::useService()->insertLevel($tblSchoolType, $division);
                                        if ($tblLevel) {
                                            $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                                '');
                                        }
                                    }
                                }

                                if ($tblDivision) {
                                    Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Father
                                $tblPersonFather = null;
                                $fatherFullName = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));
//                                $pos = strrpos($fatherFullName, ' ');
//                                if ($pos === false) {
//                                    if ($fatherFullName != '') {
//                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da der Name des Vaters nicht getrennt werden konnte (Enthält kein Leerzeichen).';
//                                    }
//                                } else {
//                                    $firstName = trim(substr($fatherFullName, 0, $pos));
//                                    $lastName = trim(substr($fatherFullName, $pos));
                                $lastName = $fatherFullName;

                                $tblPersonFatherExists = Person::useService()->existsPerson(
                                    '',
                                    $lastName,
                                    $cityCode
                                );

                                if (!$tblPersonFatherExists) {
                                    $tblPersonFather = Person::useService()->insertPerson(
                                        Person::useService()->getSalutationById(1),
                                        '',
                                        '',
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                        )
                                    );

                                    if ($tblPersonFather) {
                                        Common::useService()->insertMeta(
                                            $tblPersonFather,
                                            '',
                                            '',
                                            TblCommonBirthDates::VALUE_GENDER_MALE,
                                            '',
                                            '',
                                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                            '',
                                            ''
                                        );
                                    }

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonFather,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $countFather++;
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonFatherExists,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                    $countFatherExists++;
                                }
//                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherFullName = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));
//                                $pos = strrpos($motherFullName, ' ');
//                                if ($pos === false) {
//                                    if ($motherFullName != '') {
//                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da der Name der Mutter nicht getrennt werden konnte (Enthält kein Leerzeichen).';
//                                    }
//                                } else {
//                                    $firstName = trim(substr($motherFullName, 0, $pos));
//                                    $lastName = trim(substr($motherFullName, $pos));

                                $lastName = $motherFullName;

                                $tblPersonMotherExists = Person::useService()->existsPerson(
                                    '',
                                    $lastName,
                                    $cityCode
                                );

                                if (!$tblPersonMotherExists) {
                                    $tblPersonMother = Person::useService()->insertPerson(
                                        Person::useService()->getSalutationById(2),
                                        '',
                                        '',
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                        )
                                    );

                                    if ($tblPersonMother) {
                                        Common::useService()->insertMeta(
                                            $tblPersonMother,
                                            '',
                                            '',
                                            TblCommonBirthDates::VALUE_GENDER_FEMALE,
                                            '',
                                            '',
                                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                            '',
                                            ''
                                        );
                                    }

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonMother,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $countMother++;
                                } else {

                                    Relationship::useService()->insertRelationshipToPerson(
                                        $tblPersonMotherExists,
                                        $tblPerson,
                                        $tblRelationshipTypeCustody,
                                        ''
                                    );

                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                    $countMotherExists++;
                                }
//                                }

                                // Addresses
                                $StreetName = '';
                                $StreetNumber = '';
                                $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));
                                    }
                                }
                                Address::useService()->insertAddressToPerson(
                                    $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                );
                                if ($tblPersonFather !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonFather, $StreetName, $StreetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }
                                if ($tblPersonMother !== null) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPersonMother, $StreetName, $StreetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }

                                if ($tblPersonMother !== null) {
                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail Mutter'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPersonMother,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['privat Mutter und Vater'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }

                                    if ($tblPersonMother) {
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPersonMother,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }

                                    if ($tblPersonFather) {
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPersonFather,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['dienstlich Mutter'],
                                    $RunY)));
                                if ($phoneNumber != '' && $tblPersonMother) {
                                    $tblType = Phone::useService()->getTypeById(3);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(4);
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPersonMother,
                                        $phoneNumber,
                                        $tblType,
                                        ''
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Mutter'],
                                    $RunY)));
                                if ($phoneNumber != '' && $tblPersonMother) {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPersonMother,
                                        $phoneNumber,
                                        $tblType,
                                        ''
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Vater'],
                                    $RunY)));
                                if ($phoneNumber != '' && $tblPersonFather) {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPersonFather,
                                        $phoneNumber,
                                        $tblType,
                                        ''
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Tel. 5 Schüler(Bem. Aus freien Feldern)'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        ''
                                    );
                                }

                                /*
                                 * student
                                 */
                                $sibling = trim($Document->getValue($Document->getCell($Location['Geschw.'],
                                    $RunY)));
                                $tblSiblingRank = false;
                                if ($sibling !== '') {
                                    if ($sibling == '0') {
                                        // do nothing
                                    } elseif ($sibling == '1') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(1);
                                    } elseif ($sibling == '2') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(2);
                                    } elseif ($sibling == '3') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(3);
                                    } elseif ($sibling == '4') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(4);
                                    } elseif ($sibling == '5') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(5);
                                    } elseif ($sibling == '6') {
                                        $tblSiblingRank = Relationship::useService()->getSiblingRankById(6);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                    }
                                }

                                if ($tblSiblingRank) {
                                    $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                                } else {
                                    $tblStudentBilling = null;
                                }

                                $coachingRequired = (trim($Document->getValue($Document->getCell($Location['Schüler_Integr_Förderschüler'],
                                        $RunY))) == 'Ja');
                                if ($coachingRequired) {
                                    $tblStudentIntegration = Student::useService()->insertStudentIntegration(
                                        null,
                                        null,
                                        null,
                                        null,
                                        null,
                                        true
                                    );
                                } else {
                                    $tblStudentIntegration = null;
                                }

                                $insurance = trim($Document->getValue($Document->getCell($Location['Krankenkasse'],
                                    $RunY)));
                                if ($insurance) {
                                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                        '',
                                        '',
                                        $insurance
                                    );
                                } else {
                                    $tblStudentMedicalRecord = null;
                                }

                                $tblStudent = Student::useService()->insertStudent($tblPerson, '',
                                    $tblStudentMedicalRecord, null,
                                    $tblStudentBilling, null, null, $tblStudentIntegration);
                                if ($tblStudent) {

                                    // Schülertransfer
                                    $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Einschulung am'],
                                        $RunY)));
                                    if ($enrollmentDate !== '' && date_create($enrollmentDate) !== false) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            null,
                                            $enrollmentDate,
                                            ''
                                        );
                                    }
                                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Aufnahme am'],
                                        $RunY)));
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['von welcher Schule ID'],
                                        $RunY)));
                                    if ($company != '' && ($tblCompany = Company::useService()->insertCompany($company))
                                    ) {
                                        $arriveSchool = $tblCompany;
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                    }
                                    if ($arriveDate !== '' && date_create($arriveDate) !== false) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            $arriveSchool,
                                            null,
                                            null,
                                            $arriveDate,
                                            ''
                                        );
                                    }
                                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Schulabgang am'],
                                        $RunY)));
                                    if ($leaveDate !== '' && date_create($leaveDate) !== false) {
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
                                    $currentSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['auf welche Schule_ID'],
                                        $RunY)));
                                    if ($company !== '' && ($tblCompany = Company::useService()->insertCompany($company))
                                    ) {
                                        $currentSchool = $tblCompany;
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                        $tblCompanyGroup = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');
                                        \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany($tblCompanyGroup,
                                            $tblCompany);
                                    }
                                    $tblCourse = null;
                                    if (($course = trim($Document->getValue($Document->getCell($Location['Bildungsgang'],
                                        $RunY))))
                                    ) {
                                        if ($course == 'HS') {
                                            $tblCourse = Course::useService()->getCourseById(1); // Hauptschule
                                        } elseif ($course == 'GY') {
                                            $tblCourse = Course::useService()->getCourseById(3); // Gymnasium
                                        } elseif ($course == 'RS' || $course == 'ORS') {
                                            $tblCourse = Course::useService()->getCourseById(2); // Realschule
                                        } elseif ($course == '') {
                                            // do nothing
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bildungsgang nicht gefunden.';
                                        }
                                    }
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        $currentSchool,
                                        $tblSchoolType ? $tblSchoolType : null,
                                        $tblCourse ? $tblCourse : null,
                                        null,
                                        ''
                                    );

                                    // Todo Johk richtige Zuordnung, was ist mit 8 Katalog?
                                    // photo agreement
                                    $photo = trim($Document->getValue($Document->getCell($Location['Fotoerlaubnis Einzelbestandteile'],
                                        $RunY)));
                                    if ($photo !== '') {
                                        if (strpos($photo, '1') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(2);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '2') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(1);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '3') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(7);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '4') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(5);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '5') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(3);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '6') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(4);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                        if (strpos($photo, '7') !== false) {
                                            $tblStudentAgreementType = Student::useService()->getStudentAgreementTypeById(6);
                                            Student::useService()->insertStudentAgreement($tblStudent,
                                                $tblStudentAgreementType);
                                        }
                                    }

                                    /*
                                     * Fächer
                                     */
                                    $subjectReligion = trim($Document->getValue($Document->getCell($Location['Religionsunterricht'],
                                        $RunY)));
                                    $tblSubject = false;
                                    if ($subjectReligion !== '') {
                                        if ($subjectReligion === 'ETH') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('ETH');
                                        } elseif ($subjectReligion === 'RE/e') {
                                            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
                                        }
                                        if ($tblSubject) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('Religion'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubject
                                            );
                                        }
                                    }

                                    for ($i = 1; $i <= 2; $i++) {
                                        $subjectLanguage = trim($Document->getValue($Document->getCell($Location['FS' . $i],
                                            $RunY)));
                                        $tblSubject = false;
                                        if ($subjectLanguage !== '') {
                                            if ($subjectLanguage === 'EN' || $subjectLanguage === 'Englisch') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                            } elseif ($subjectLanguage === 'FR' || $subjectLanguage === 'Französisch') {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('FR');
                                            }
                                            if ($tblSubject) {
                                                Student::useService()->addStudentSubject(
                                                    $tblStudent,
                                                    Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                    Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                    $tblSubject
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '');
//                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
//                            new Panel(
//                                'Fehler',
//                                $error,
//                                Panel::PANEL_TYPE_DANGER
//                            )
//                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClubMembersFromFile(
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
                    'ID' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Eintritt' => null
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
                    $countClubMember = 0;
                    $countClubMemberExists = 0;
                    $error = array();

                    $tblGroupClubMember = Group::useService()->insertGroup('Schulverein');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        if ($firstName !== '') {
                            $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                            $cityCode = str_pad(
                                trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                5,
                                "0",
                                STR_PAD_LEFT
                            );
                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP(
                                trim($Document->getValue($Document->getCell($Location['Eintritt'],
                                    $RunY)))));
                            $clubNumber = trim($Document->getValue($Document->getCell($Location['ID'],
                                $RunY)));
                            $remark = ($entryDate !== '' ? 'Eintritt: ' . $entryDate : '') .
                                ($clubNumber !== '' ? ' Mitgliedsnummer: ' . $clubNumber : '');

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblGroupClubMember, $tblPersonExits);
                                $tblCommon = Common::useService()->getCommonByPerson($tblPersonExits);
                                if ($tblCommon) {
                                    Common::useService()->updateCommon($tblCommon, $remark);
                                }
                                $countClubMemberExists++;

                            } else {

                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    array(
                                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                        1 => $tblGroupClubMember
                                    )
                                );

                                if ($tblPerson !== false) {
                                    $countClubMember++;

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        '',
                                        '',
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        $remark
                                    );

                                    // Address
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                        $RunY)));
                                    $cityDistrict = '';
                                    $pos = strpos($cityName, " OT ");
                                    if ($pos !== false) {
                                        $cityDistrict = trim(substr($cityName, $pos + 4));
                                        $cityName = trim(substr($cityName, 0, $pos));
                                    }
                                    $StreetName = '';
                                    $StreetNumber = '';
                                    $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $Street, $matches)) {
                                        $pos = strpos($Street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $StreetName = trim(substr($Street, 0, $pos));
                                            $StreetNumber = trim(substr($Street, $pos));
                                        }
                                    }
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                    );
                                }
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countClubMember . ' Schulverein-Mitglieder erfolgreich angelegt.') .
                        ($countClubMemberExists > 0 ?
                            new Warning($countClubMemberExists . ' Schulverein-Mitglieder exisistieren bereits.') : '');

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}