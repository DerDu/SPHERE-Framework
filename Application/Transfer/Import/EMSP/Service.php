<?php

namespace SPHERE\Application\Transfer\Import\EMSP;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\Transfer\Import\Service as ImportService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\EMSP
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
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
                    'ImportId' => null,
                    'Person: Vorname' => null,
                    'Person: Nachname' => null,
                    'Person: Geburtstag' => null,
                    'Person: Geburtsort' => null,
                    'Person: Geschlecht' => null,
                    'Person: Staatsangehörigkeit' => null,
                    'Person: Konfession' => null,
                    'Grunddaten: Schulpflichtbeginn' => null,
                    'Grunddaten: Migrationshintergrund' => null,
                    'Grunddaten: Besucht Vorbereitungsklasse für Migranten' => null,
                    'Einschulung: Datum' => null,
                    'Einschulung: Einschulungsart' => null,
                    'Allgemeines: Krankheiten / Allergien' => null,
                    'Allgemeines: Medikamente' => null,
                    'Allgemeines: Behandelnder Arzt' => null,
                    'Allgemeines: Versicherungsstatus' => null,
                    'Allgemeines: Krankenkasse' => null,
                    'Allgemeines: Erlaubnis Schülername' => null,
                    'Allgemeines: Erlaubnis Schülerbild' => null,
                    'Bildung: Klassenstufe' => null,
                    'Bildung: Klassengruppe' => null,
                    'Hauptadresse: Straße' => null,
                    'Hauptadresse: Hausnummer' => null,
                    'Hauptadresse: Postleitzahl' => null,
                    'Hauptadresse: Ort' => null,
                    'Hauptadresse: Ortsteil' => null,
                    'Person: Telefon' => null,
                    'Adresse gemeinsam' => null,

                    'S1: Anrede' => null,
                    'S1: Titel' => null,
                    'S1: Vorname' => null,
                    'S1: Nachname' => null,
                    'S1: Straße' => null,
                    'S1: Hausnummer' => null,
                    'S1: PLZ' => null,
                    'S1: Ort' => null,
                    'S1: Ortsteil' => null,
                    'S1: Arbeitsstelle' => null,
                    'S1: Alleinerziehend' => null,
                    'S1: Telefon Fax Geschäftlich' => null,
                    'S1: Telefon Fax Privat' => null,
                    'S1: Telefon Privat Mobil' => null,
                    'S1: E-Mail Privat' => null,

                    'S2: Anrede' => null,
                    'S2: Titel' => null,
                    'S2: Vorname' => null,
                    'S2: Nachname' => null,
                    'S2: Straße' => null,
                    'S2: Hausnummer' => null,
                    'S2: PLZ' => null,
                    'S2: Ort' => null,
                    'S2: Ortsteil' => null,
                    'S2: Arbeitsstelle' => null,
                    'S2: Alleinerziehend' => null,
                    'S2: Telefon Fax Privat' => null,
                    'S2: Telefon Geschäftlich Festnetz' => null,
                    'S2: Telefon Geschäftlich Mobil' => null,
                    'S2: E-Mail Privat' => null,

                    'Abholberechtigte Vorname, Nachname' => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $importService = new ImportService($Location, $Document);

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStudent = 0;
                    $countMother = 0;
                    $countMotherExists = 0;
                    $countFather = 0;
                    $countFatherExists = 0;

                    $error = array();

                    $tblYear = $importService->insertSchoolYear(19);

                    $tblRelationshipTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblSchoolType = Type::useService()->getTypeByName('Grundschule');
                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                    $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
                    $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                    $tblStudentAgreementCategoryPhoto = Student::useService()->getStudentAgreementCategoryById(1);
                    $tblStudentAgreementCategoryName = Student::useService()->getStudentAgreementCategoryById(2);
                    $tblStudentSchoolEnrollmentTypePremature = Student::useService()->getStudentSchoolEnrollmentTypeByIdentifier('PREMATURE');
                    $tblStudentSchoolEnrollmentTypeRegular = Student::useService()->getStudentSchoolEnrollmentTypeByIdentifier('REGULAR');
                    $tblStudentSchoolEnrollmentTypePostponed = Student::useService()->getStudentSchoolEnrollmentTypeByIdentifier('POSTPONED');

                    if (($tblSetting = Consumer::useService()->getSetting(
                            'People', 'Person', 'Relationship', 'GenderOfS1'
                        ))
                        && ($value = $tblSetting->getValue())
                    ) {
                        if (($genderSetting = Common::useService()->getCommonGenderById($value))) {
                            $genderSetting = $genderSetting->getName();
                        }
                    } else {
                        $genderSetting = '';
                    }

                    $studentGroups = array(
                        0 => $tblGroupCommon,
                        1 => $tblGroupStudent,
                    );

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Person: Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Person: Nachname'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            $importId = trim($Document->getValue($Document->getCell($Location['ImportId'], $RunY)));

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                $studentGroups,
                                '',
                                $importId
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Person: Geschlecht'],
                                    $RunY)));
                                if ($gender == 'Männlich') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'Weiblich') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Person: Geburtstag'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $birthday = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $birthday = '';
                                }

                                $remark = '';
                                if (($remark1 = trim($Document->getValue($Document->getCell($Location['Abholberechtigte Vorname, Nachname'],
                                    $RunY))))) {
                                    $remark = 'Abholberechtigte: ' . $remark1;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Person: Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Person: Staatsangehörigkeit'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Person: Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                $level = trim($Document->getValue($Document->getCell($Location['Bildung: Klassenstufe'],
                                    $RunY)));
                                $division = trim($Document->getValue($Document->getCell($Location['Bildung: Klassengruppe'],
                                    $RunY)));
                                if ($level && $division) {
                                    if (($tblLevel = Division::useService()->insertLevel($tblSchoolType, $level))
                                        && ($tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                            $division))
                                    ) {
                                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                }

                                // Address
                                $studentCityCode = $importService->formatZipCode('Hauptadresse: Postleitzahl', $RunY);
                                $cityName = trim($Document->getValue($Document->getCell($Location['Hauptadresse: Ort'],
                                    $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Hauptadresse: Ortsteil'],
                                    $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Hauptadresse: Straße'],
                                    $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['Hauptadresse: Hausnummer'],
                                    $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                $importService->insertPrivatePhone($tblPerson, 'Person: Telefon', $RunY);

                                $insuranceState = trim($Document->getValue($Document->getCell($Location['Allgemeines: Versicherungsstatus'],
                                    $RunY)));
                                if ($insuranceState == 'Vater' || $insuranceState == 'Mutter') {
                                    $insuranceState = 'Familie ' . $insuranceState;
                                }
                                if (!($tblStudentInsuranceState = Student::useService()->getStudentInsuranceStateByName($insuranceState))) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Versicherungsstatus wurde nicht gefunden.';
                                }

                                $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                    trim($Document->getValue($Document->getCell($Location['Allgemeines: Krankheiten / Allergien'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Allgemeines: Medikamente'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Allgemeines: Krankenkasse'],
                                        $RunY))),
                                    $tblStudentInsuranceState ? $tblStudentInsuranceState : 0,
                                    trim($Document->getValue($Document->getCell($Location['Allgemeines: Behandelnder Arzt'],
                                        $RunY)))
                                );

                                if (($date = trim($Document->getValue($Document->getCell($Location['Grunddaten: Schulpflichtbeginn'],
                                    $RunY))))) {
                                    $schoolDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($date));
                                } else {
                                    $schoolDate = '';
                                }

                                if (($tblStudent = Student::useService()->insertStudent(
                                    $tblPerson,
                                    '',
                                    $tblStudentMedicalRecord ? $tblStudentMedicalRecord : null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    $schoolDate,
                                    trim($Document->getValue($Document->getCell($Location['Grunddaten: Migrationshintergrund'],
                                        $RunY))) == 'ja',
                                    trim($Document->getValue($Document->getCell($Location['Grunddaten: Besucht Vorbereitungsklasse für Migranten'],
                                        $RunY))) == 'ja'
                                ))) {
                                    $importService->setStudentAgreement(
                                        'Allgemeines: Erlaubnis Schülername',
                                        $RunY,
                                        $tblStudent,
                                        $tblStudentAgreementCategoryName
                                    );
                                    $importService->setStudentAgreement(
                                        'Allgemeines: Erlaubnis Schülerbild',
                                        $RunY,
                                        $tblStudent,
                                        $tblStudentAgreementCategoryPhoto
                                    );

                                    if (($date = trim($Document->getValue($Document->getCell($Location['Einschulung: Datum'],
                                        $RunY))))) {
                                        $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($date));
                                    } else {
                                        $entryDate = '';
                                    }

                                    $tblStudentSchoolEnrollmentType = null;
                                    if (($enrollmentType = trim($Document->getValue($Document->getCell($Location['Einschulung: Einschulungsart'],
                                        $RunY))))) {
                                        switch ($enrollmentType) {
                                            case 'vorzeitige Einschulung':
                                                $tblStudentSchoolEnrollmentType = $tblStudentSchoolEnrollmentTypePremature;
                                                break;
                                            case 'fristgemäße Einschulung':
                                                $tblStudentSchoolEnrollmentType = $tblStudentSchoolEnrollmentTypeRegular;
                                                break;
                                            case 'Einschulung nach Zurückstellung':
                                                $tblStudentSchoolEnrollmentType = $tblStudentSchoolEnrollmentTypePostponed;
                                                break;
                                            default:
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Einschulungsart nicht gefunden';
                                        }
                                    }

                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferTypeEnrollment,
                                        null,
                                        null,
                                        null,
                                        $entryDate,
                                        '',
                                        null,
                                        $tblStudentSchoolEnrollmentType
                                    );
                                }

                                $isAddressCopied = trim($Document->getValue($Document->getCell($Location['Adresse gemeinsam'],
                                        $RunY))) == 'ja';

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['S1: Nachname'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['S1: Vorname'],
                                    $RunY)));
                                if ($motherLastName != '') {
                                    $motherCityCode = trim($Document->getValue($Document->getCell($Location['S1: PLZ'],
                                        $RunY)));

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $motherCityCode == '' ? $studentCityCode : $motherCityCode
                                    );
                                    if (!$tblPersonMotherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                        $tblSalutation = Person::useService()->getSalutationById(2);

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['S1: Titel'],
                                                $RunY))),
                                            $motherFirstName,
                                            '',
                                            $motherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            ),
                                            '',
                                            $importId . '_S1'
                                        );

                                        if ($tblPersonMother) {
                                            Common::useService()->insertMeta(
                                                $tblPersonMother,
                                                '',
                                                '',
                                                $gender,
                                                '',
                                                '',
                                                TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                                '',
                                                ''
                                            );

                                            Custody::useService()->insertMeta(
                                                $tblPersonMother,
                                                '',
                                                trim($Document->getValue($Document->getCell($Location['S1: Arbeitsstelle'],
                                                    $RunY))),
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMother,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 1 : 2,
                                            trim($Document->getValue($Document->getCell($Location['S1: Alleinerziehend'],
                                                $RunY))) == 'ja'
                                        );

                                        if ($isAddressCopied) {
                                            if ($streetName !== '' && $streetNumber !== ''
                                                && $studentCityCode && $cityName
                                            ) {
                                                Address::useService()->insertAddressToPerson(
                                                    $tblPersonMother, $streetName, $streetNumber, $studentCityCode,
                                                    $cityName, $cityDistrict, ''
                                                );
                                            }
                                        }

                                        if ($motherCityCode) {
                                            // Address
                                            $cityNameMother = trim($Document->getValue($Document->getCell($Location['S1: Ort'],
                                                $RunY)));
                                            $cityDistrictMother = trim($Document->getValue($Document->getCell($Location['S1: Ortsteil'],
                                                $RunY)));
                                            $streetNameMother = trim($Document->getValue($Document->getCell($Location['S1: Straße'],
                                                $RunY)));
                                            $streetNumberMother = trim($Document->getValue($Document->getCell($Location['S1: Hausnummer'],
                                                $RunY)));
                                            if ($streetNameMother !== '' && $streetNumberMother !== ''
                                                && $motherCityCode && $cityNameMother
                                            ) {
                                                Address::useService()->insertAddressToPerson(
                                                    $tblPersonMother, $streetNameMother, $streetNumberMother,
                                                    $motherCityCode, $cityNameMother, $cityDistrictMother, ''
                                                );
                                            } else {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse bei S1 ist nicht vollständig.';
                                            }
                                        }

                                        $importService->insertBusinessFax($tblPersonMother,
                                            'S1: Telefon Fax Geschäftlich', $RunY);
                                        $importService->insertPrivateFax($tblPersonMother, 'S1: Telefon Fax Privat',
                                            $RunY);
                                        $importService->insertPrivatePhone($tblPersonMother, 'S1: Telefon Privat Mobil',
                                            $RunY);
                                        $importService->insertPrivateMail($tblPersonMother, 'S1: E-Mail Privat', $RunY);

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 1 : 2,
                                            trim($Document->getValue($Document->getCell($Location['S1: Alleinerziehend'],
                                                $RunY))) == 'ja'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['S2: Nachname'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['S2: Vorname'],
                                    $RunY)));
                                if ($fatherLastName != '') {
                                    $fatherCityCode = trim($Document->getValue($Document->getCell($Location['S2: PLZ'],
                                        $RunY)));

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $fatherCityCode == '' ? $studentCityCode : $fatherCityCode
                                    );
                                    if (!$tblPersonFatherExists) {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                        $tblSalutation = Person::useService()->getSalutationById(1);

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['S2: Titel'],
                                                $RunY))),
                                            $fatherFirstName,
                                            '',
                                            $fatherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            ),
                                            '',
                                            $importId . '_S2'
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

                                            Custody::useService()->insertMeta(
                                                $tblPersonFather,
                                                '',
                                                trim($Document->getValue($Document->getCell($Location['S2: Arbeitsstelle'],
                                                    $RunY))),
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFather,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 2 : 1,
                                            trim($Document->getValue($Document->getCell($Location['S2: Alleinerziehend'],
                                                $RunY))) == 'ja'
                                        );

                                        if ($isAddressCopied) {
                                            if ($streetName !== '' && $streetNumber !== ''
                                                && $studentCityCode && $cityName
                                            ) {
                                                Address::useService()->insertAddressToPerson(
                                                    $tblPersonFather, $streetName, $streetNumber, $studentCityCode,
                                                    $cityName, $cityDistrict, ''
                                                );
                                            }
                                        }

                                        if ($fatherCityCode) {
                                            // Address
                                            $cityNameFather = trim($Document->getValue($Document->getCell($Location['S2: Ort'],
                                                $RunY)));
                                            $cityDistrictFather = trim($Document->getValue($Document->getCell($Location['S2: Ortsteil'],
                                                $RunY)));
                                            $streetNameFather = trim($Document->getValue($Document->getCell($Location['S2: Straße'],
                                                $RunY)));
                                            $streetNumberFather = trim($Document->getValue($Document->getCell($Location['S2: Hausnummer'],
                                                $RunY)));
                                            if ($streetNameFather !== '' && $streetNumberFather !== ''
                                                && $fatherCityCode && $cityNameFather
                                            ) {
                                                Address::useService()->insertAddressToPerson(
                                                    $tblPersonFather, $streetNameFather, $streetNumberFather,
                                                    $fatherCityCode, $cityNameFather, $cityDistrictFather, ''
                                                );
                                            } else {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse bei S2 ist nicht vollständig.';
                                            }
                                        }

                                        $importService->insertPrivateFax($tblPersonFather,
                                            'S2: Telefon Fax Privat', $RunY);
                                        $importService->insertBusinessPhone($tblPersonFather, 'S2: Telefon Geschäftlich Festnetz',
                                            $RunY);
                                        $importService->insertBusinessPhone($tblPersonFather, 'S2: Telefon Geschäftlich Mobil',
                                            $RunY);
                                        $importService->insertPrivateMail($tblPersonFather, 'S2: E-Mail Privat', $RunY);

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 2 : 1,
                                            trim($Document->getValue($Document->getCell($Location['S2: Alleinerziehend'],
                                                $RunY))) == 'ja'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '')
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
}