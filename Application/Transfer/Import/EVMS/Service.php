<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.09.2019
 * Time: 16:30
 */

namespace SPHERE\Application\Transfer\Import\EVMS;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
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
 * @package SPHERE\Application\Transfer\Import\EVMS
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
                    'Klasse' => null,
                    'Gruppe' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Geb.-Datum' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Strasse' => null,
                    'Ortsteil' => null,
                    'Name Mutter' => null,
                    'Vorname Mutter' => null,
                    'Name Vater' => null,
                    'Vorname Vater' => null,
                    'Telefon Mutter' => null,
                    'Telefon Vater' => null,
                    'Telefon Oma' => null,
                    'Geb.-ort' => null,
                    'von GS kommend' => null,
                    'eingeschult' => null,
                    'w/m' => null,
                    'Konfess' => null,
                    'Email Schüler' => null,
                    'Telefon' => null,
                    'Bemerkung 1' => null,
                    'Bemerkung 2' => null,
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
                    $tblSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');
                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                    $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
                    $tblGroupStudentArchive = Group::useService()->insertGroup('Ehemalige Schüler');
                    $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
                    $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                    $tblCompanyGroupCommon = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                    $tblCompanyGroupSchool = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');

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

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            if ('Abgänger' == trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY)))) {
                                $isArchive = true;
                            } else {
                                $isArchive = false;
                            }

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => $tblGroupCommon,
                                    1 => $isArchive ? $tblGroupStudentArchive : $tblGroupStudent,
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                if ($isArchive
                                    && ($group = trim($Document->getValue($Document->getCell($Location['Gruppe'], $RunY))))
                                    && ($tblGroup = Group::useService()->insertGroup($group))
                                ) {
                                    Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['w/m'], $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geb.-Datum'],
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
                                if (($remark1 = trim($Document->getValue($Document->getCell($Location['Bemerkung 1'], $RunY))))) {
                                    $remark = $remark1;
                                }
                                if (($remark2 = trim($Document->getValue($Document->getCell($Location['Bemerkung 2'], $RunY))))) {
                                    $remark = $remark ? $remark . ', ' . $remark2 : $remark2;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geb.-ort'], $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfess'], $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                if (!$isArchive) {
                                    $division = trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY)));

                                    if ($division) {
                                        $pos = strpos($division, ' ');
                                        if ($pos !== false) {
                                            $level = trim(substr($division, 0, $pos));
                                            $division = trim(substr($division, $pos + 1));

                                            if (($tblLevel = Division::useService()->insertLevel($tblSchoolType, $level))
                                                && ($tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel, $division))
                                            ) {
                                                Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                            } else {
                                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                            }
                                        }
                                    }
                                }

                                // Address
                                $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                                list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                $streetNumber = str_replace(' ', '', $streetNumber);
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                if (($primarySchool = trim($Document->getValue($Document->getCell($Location['von GS kommend'], $RunY))))) {
                                    if (!($tblCompany = Company::useService()->getCompanyByName($primarySchool, ''))) {
                                        if (($tblCompany = Company::useService()->insertCompany($primarySchool))) {
                                            \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                                $tblCompanyGroupCommon, $tblCompany
                                            );
                                            \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                                $tblCompanyGroupSchool, $tblCompany
                                            );
                                        }
                                    }
                                } else {
                                    $tblCompany = false;
                                }
                                if (($date = trim($Document->getValue($Document->getCell($Location['eingeschult'], $RunY))))) {
                                    $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($date));
                                } else {
                                    $entryDate = false;
                                }
                                if ($tblCompany || $entryDate) {
                                    if (($tblStudent = Student::useService()->insertStudent($tblPerson, ''))) {
                                        if ($tblCompany) {
                                            Student::useService()->insertStudentTransfer(
                                                $tblStudent,
                                                $tblStudentTransferTypeArrive,
                                                $tblCompany,
                                                null,
                                                null,
                                                '',
                                                ''
                                            );
                                        }

                                        if ($entryDate) {
                                            Student::useService()->insertStudentTransfer(
                                                $tblStudent,
                                                $tblStudentTransferTypeEnrollment,
                                                null,
                                                null,
                                                null,
                                                $entryDate,
                                                ''
                                            );
                                        }
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                if ($motherLastName != '') {
                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
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
                                                $gender,
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
                                            '',
                                            $genderSetting == 'Weiblich' ? 1 : 2
                                        );

                                        if ($streetName !== '' && $streetNumber !== ''
                                            && $studentCityCode && $cityName
                                        ) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonMother, $streetName, $streetNumber, $studentCityCode,
                                                $cityName, $cityDistrict, ''
                                            );
                                        }

                                        $importService->insertPrivatePhone($tblPersonMother, 'Telefon Mutter', $RunY);

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 1 : 2
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                if ($fatherLastName != '') {
                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $studentCityCode
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
                                            '',
                                            $genderSetting == 'Weiblich' ? 2 : 1
                                        );

                                        if ($streetName !== '' && $streetNumber !== ''
                                            && $studentCityCode && $cityName
                                        ) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonFather, $streetName, $streetNumber, $studentCityCode,
                                                $cityName, $cityDistrict, ''
                                            );
                                        }

                                        $importService->insertPrivatePhone($tblPersonFather, 'Telefon Vater', $RunY);

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            $genderSetting == 'Weiblich' ? 2 : 1
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                $importService->insertPrivatePhone($tblPerson, 'Telefon Oma', $RunY, 'Oma');
                                $importService->insertPrivatePhone($tblPerson, 'Telefon', $RunY);
                                $importService->insertPrivateMail($tblPerson, 'Email Schüler', $RunY);
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

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createStaffsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {

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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Anrede' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Email' => null,
                    'Tel.:' => null,
                    'Geb.Dat.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Adresse' => null,
                    'OT' => null,
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
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    $tblStaffGroup = Group::useService()->getGroupByMetaTable('STAFF');
                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {
                            // Address
                            $cityCode = $importService->formatZipCode('PLZ', $RunY);

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExits);
                                Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);

                                $importService->insertBusinessMail($tblPersonExits, 'Email', $RunY);
                                $importService->insertPrivatePhone($tblPersonExits, 'Tel.:', $RunY);

                                $countStaffExists++;
                            } else {
                                $groupArray = array();
                                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                                $groupArray[] = $tblStaffGroup;
                                $groupArray[] = $tblTeacherGroup;

                                $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                                if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                    $tblSalutation = Person::useService()->getSalutationById(1);
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($salutation == 'Frau') {
                                    $tblSalutation = Person::useService()->getSalutationById(2);
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $tblSalutation = false;
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $tblPerson = Person::useService()->insertPerson(
                                    $tblSalutation ? $tblSalutation : null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupArray
                                );

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geb.Dat.'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } else {
                                        $birthday = '';
                                    }

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        $birthday,
                                        '',
                                        $gender,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    // Address
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                    $cityDistrict = trim($Document->getValue($Document->getCell($Location['OT'], $RunY)));
                                    list($streetName, $streetNumber) = $importService->splitStreet('Adresse', $RunY);
                                    $streetNumber = str_replace(' ', '', $streetNumber);

                                    if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, $cityDistrict, ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                    }

                                    $importService->insertBusinessMail($tblPerson, 'Email', $RunY);
                                    $importService->insertPrivatePhone($tblPerson, 'Tel.:', $RunY);
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStaff . ' Mitarbeiter erfolgreich angelegt.') .
                        ($countStaffExists > 0 ?
                            new Warning($countStaffExists . ' Mitarbeiter exisistieren bereits.') : '')
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

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createCompaniesFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {
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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Art' => null,
                    'Schulname' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Adresse' => null,
                    'OT' => null,
                    'Anrede' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Telefon' => null,
                    'Fax' => null,
                    'ehem. Bezeichg.' => null
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $tblGroupCommon = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON');
                $tblGroupSchool = \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL');

                $tblTypeCommon = Relationship::useService()->getTypeByName('Schulleiter');

                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                $groupArray[] = Group::useService()->getGroupByMetaTable('COMPANY_CONTACT');

                $importService = new ImportService($Location, $Document);

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countNewCompany = 0;
                    $countEditCompany = 0;
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $name = trim($Document->getValue($Document->getCell($Location['Schulname'], $RunY)));
                        if ($name != '') {
                            $oldName = trim($Document->getValue($Document->getCell($Location['ehem. Bezeichg.'], $RunY)));
                            if (!($tblCompany = Company::useService()->getCompanyByName($oldName, ''))) {
                                if (($tblCompany = Company::useService()->insertCompany($name))) {
                                    $countNewCompany++;

                                    \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                        $tblGroupCommon,
                                        $tblCompany
                                    );
                                    \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                        $tblGroupSchool,
                                        $tblCompany
                                    );
                                }
                            } else {
                                $countEditCompany++;
                                Company::useService()->updateCompanyWithoutForm($tblCompany, $name);
                            }

                            if ($tblCompany) {
                                // Address
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $cityCode = $importService->formatZipCode('PLZ', $RunY);
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['OT'], $RunY)));;

                                list($streetName, $streetNumber) = $importService->splitStreet('Adresse', $RunY);

                                Address::useService()->insertAddressToCompany(
                                    $tblCompany,
                                    $streetName,
                                    $streetNumber,
                                    $cityCode,
                                    $cityName,
                                    $cityDistrict,
                                    ''
                                );

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                        $RunY)))) != ''
                                ) {
                                    $tblType = Phone::useService()->getTypeById(3);
                                    if (0 === strpos($Number, '01')) {
                                        $tblType = Phone::useService()->getTypeById(4);
                                    }
                                    Phone::useService()->insertPhoneToCompany
                                    (
                                        $tblCompany,
                                        $Number,
                                        $tblType,
                                        ''
                                    );
                                }

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Fax'],
                                        $RunY)))) != ''
                                ) {
                                    $tblType = Phone::useService()->getTypeById(8);
                                    Phone::useService()->insertPhoneToCompany
                                    (
                                        $tblCompany,
                                        $Number,
                                        $tblType,
                                        ''
                                    );
                                }
                            }

                            $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                            $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                            if ($firstName !== '' && $lastName !== '') {
                                $tblPersonExits = Person::useService()->getPersonByName($firstName, $lastName);
                                if ($tblPersonExits) {
                                    $error[] = 'Zeile: ' . ($RunY + 1)
                                        . ' (' . $lastName . ', ' . $firstName . ') '
                                        . ' Der Ansprechpartner wurde nicht angelegt, da schon eine Person mit gleichen Namen existiert.';

                                    $tblPerson = $tblPersonExits;
                                } else {
                                    $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                                    if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                        $tblSalutation = Person::useService()->getSalutationById(1);
                                    } elseif ($salutation == 'Frau') {
                                        $tblSalutation = Person::useService()->getSalutationById(2);
                                    } else {
                                        $tblSalutation = false;
                                    }

                                    $tblPerson = Person::useService()->insertPerson(
                                        $tblSalutation ? $tblSalutation : null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        $groupArray
                                    );
                                }

                                if ($tblPerson) {
                                    Relationship::useService()->addCompanyRelationshipToPerson(
                                        $tblCompany,
                                        $tblPerson,
                                        $tblTypeCommon
                                    );
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countNewCompany . ' Firmen erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countEditCompany . ' Firmen erfolgreich umbenannt.')
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