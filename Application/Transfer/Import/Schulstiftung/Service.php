<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.01.2017
 * Time: 08:33
 */

namespace SPHERE\Application\Transfer\Import\Schulstiftung;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 * @package SPHERE\Application\Transfer\Import\Schulstiftung
 */
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
                    'Schüler - ID' => null,
                    'Schuljahr' => null,
                    'Klassenstufe' => null,
                    'Klassengruppe' => null,
                    'Schulart' => null,
                    'Bildungsgang' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Vorname 2' => null,
                    'Geschlecht' => null,
                    'Geschwister ID 1' => null,
                    'Geschwister ID 2' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Konfession' => null,
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Telefon Festnetz' => null,
                    'Telefon Notfall' => null,
                    'Email-Adresse' => null,
                    'Name Sorg 1' => null,
                    'Vorname Sorg 1' => null,
                    'Straße Sorg 1' => null,
                    'Hausnr. Sorg 1' => null,
                    'PLZ Sorg 1' => null,
                    'Ort Sorg 1' => null,
                    'Name Sorg 2' => null,
                    'Vorname Sorg 2' => null,
                    'Straße Sorg 2' => null,
                    'Hausnr. Sorg 2' => null,
                    'PLZ Sorg 2' => null,
                    'Ort Sorg 2' => null,
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
                    $studentList = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                trim($Document->getValue($Document->getCell($Location['Vorname 2'], $RunY))),
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT')
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $id = trim($Document->getValue($Document->getCell($Location['Schüler - ID'],
                                    $RunY)));
                                if ($id !== '') {
                                    $studentList[$id] = $tblPerson;
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                    $RunY)));
                                if ($birthday !== '') {
                                    if (strpos($birthday,'/')) {
                                        $dateString = explode('/', $birthday);
                                        if (count($dateString) == 3) {
                                            $birthday = $dateString[1] . '.' . $dateString[0] . '.' . $dateString[2];
                                        } else {
                                            $birthday == '';
                                        }
                                    } else {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                                    }
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // division
                                $tblDivision = false;
                                $year = trim($Document->getValue($Document->getCell($Location['Schuljahr'],
                                    $RunY)));
                                $level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'],
                                    $RunY)));
                                $schoolType = trim($Document->getValue($Document->getCell($Location['Schulart'],
                                    $RunY)));
                                if ($schoolType == 'Grundschule') {
                                    $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                } elseif ($schoolType == 'Gymnasium') {
                                    $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                } elseif ($schoolType == 'Oberschule') {
                                    $tblSchoolType = Type::useService()->getTypeById(8); // Mittelschule
                                } else {
                                    $tblSchoolType = false;
                                }
                                $division = trim($Document->getValue($Document->getCell($Location['Klassengruppe'],
                                    $RunY)));
                                if (!$tblSchoolType) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Schulart wurde nicht gefunden.';
                                }
                                if ($level !== '' && $division !== '' && $year !== '' && $tblSchoolType) {

                                    if (strlen($year) == 7) {
                                        $year = substr($year, 2, 2);
                                    }
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

                                        if ($tblSchoolType) {
                                            $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                                            if ($tblLevel) {
                                                $tblDivision = Division::useService()->insertDivision(
                                                    $tblYear,
                                                    $tblLevel,
                                                    $division
                                                );
                                            }
                                        }
                                    }

                                    if ($tblDivision) {
                                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                    }
                                }

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $studentCityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $studentCityDistrict = '';
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                    $RunY)));

                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $studentCityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $studentCityName,
                                        $studentCityDistrict, ''
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name Sorg 2'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Sorg 2'],
                                    $RunY)));

                                $fatherCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ Sorg 2'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                if ($fatherLastName != '') {

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $fatherCityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                        $tblSalutation = Person::useService()->getSalutationById(1);

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            '',
                                            $fatherFirstName,
                                            '',
                                            $fatherLastName,
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
                                                $gender,
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
                                            'Vater'
                                        );

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Vater'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte2 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name Sorg 1'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Sorg 1'],
                                    $RunY)));
                                $motherCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ Sorg 1'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                if ($motherLastName != '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $motherCityCode
                                    );

                                    if (!$tblPersonMotherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                        $tblSalutation = Person::useService()->getSalutationById(2);

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            '',
                                            $motherFirstName,
                                            '',
                                            $motherLastName,
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
                                                $gender ? $gender : null,
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
                                            'Mutter'
                                        );

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Mutter'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorgeberechtigte1 wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                if ($tblPersonFather !== null) {
                                    $fatherStreetName = trim($Document->getValue($Document->getCell($Location['Straße Sorg 2'],
                                        $RunY)));
                                    $fatherStreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr. Sorg 2'],
                                        $RunY)));
                                    $fatherCityName = trim($Document->getValue($Document->getCell($Location['Ort Sorg 2'],
                                        $RunY)));
                                    if ($streetName !== '' && $streetNumber !== '') {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather,
                                            $fatherStreetName,
                                            $fatherStreetNumber,
                                            $fatherCityCode,
                                            $fatherCityName,
                                            '',
                                            ''
                                        );
                                    }
                                }
                                if ($tblPersonMother !== null) {
                                    $motherStreetName = trim($Document->getValue($Document->getCell($Location['Straße Sorg 1'],
                                        $RunY)));
                                    $motherStreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr. Sorg 1'],
                                        $RunY)));
                                    $motherCityName = trim($Document->getValue($Document->getCell($Location['Ort Sorg 1'],
                                        $RunY)));
                                    if ($streetName !== '' && $streetNumber !== '') {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother,
                                            $motherStreetName,
                                            $motherStreetNumber,
                                            $motherCityCode,
                                            $motherCityName,
                                            '',
                                            ''
                                        );
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon Festnetz'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $tblType = Phone::useService()->getTypeById(1);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(2);
                                    }
                                    $remark = '';

                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon Notfall'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $tblType = Phone::useService()->getTypeById(5);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(6);
                                    }
                                    $remark = '';

                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $phoneNumber,
                                        $tblType,
                                        $remark
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email-Adresse'],
                                    $RunY)));
                                if ($mailAddress != '' && $tblPerson) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                /*
                                 * student
                                 */
                                $sibling = trim($Document->getValue($Document->getCell($Location['Geschwister ID 1'],
                                    $RunY)));
                                if ($sibling !== '' && $tblPerson) {
                                    if (isset($studentList[$sibling])) {
                                        /** @var TblPerson $tblPersonTo */
                                        $tblPersonTo = $studentList[$sibling];
                                        Relationship::useService()->insertRelationshipToPerson($tblPerson, $tblPersonTo,
                                            Relationship::useService()->getTypeByName('Geschwisterkind'), '');
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                    }
                                }
                                $sibling = trim($Document->getValue($Document->getCell($Location['Geschwister ID 2'],
                                    $RunY)));
                                if ($sibling !== '' && $tblPerson) {
                                    if (isset($studentList[$sibling])) {
                                        /** @var TblPerson $tblPersonTo */
                                        $tblPersonTo = $studentList[$sibling];
                                        Relationship::useService()->insertRelationshipToPerson($tblPerson, $tblPersonTo,
                                            Relationship::useService()->getTypeByName('Geschwisterkind'), '');
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Geschwisterkind konnte nicht angelegt werden.';
                                    }
                                }

                                $tblStudent = Student::useService()->insertStudent($tblPerson, $id);
                                if ($tblStudent) {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    if ($tblStudentTransferType
                                        && ($tblCourse = Course::useService()->getCourseByName(
                                            trim($Document->getValue($Document->getCell($Location['Bildungsgang'],
                                                $RunY)))
                                        ))
                                    ) {
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            $tblSchoolType ? $tblSchoolType : null,
                                            $tblCourse,
                                            '',
                                            ''
                                        );
                                    }
                                }
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Sorgeberechtigte2 erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Sorgeberechtigte2 exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Sorgeberechtigte1 erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Sorgeberechtigte1 exisistieren bereits.') : '')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

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