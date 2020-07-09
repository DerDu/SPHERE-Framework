<?php

namespace SPHERE\Application\Transfer\Import\MLS;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Prospect\Prospect;
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
 * @package SPHERE\Application\Transfer\Import\MLS
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
                    'Name' => null,
//                    'Nachname' => null,
//                    'Vorname' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Geschlecht' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Straße' => null,
                    'Titel Mutter' => null,
                    'Nachname Mutter' => null,
                    'Vorname Mutter' => null,
                    'Titel Vater' => null,
                    'Nachname Vater' => null,
                    'Vorname Vater' => null,
                    'Hort' => null
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

                    $studentGroups = array(
                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                        1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                    );
                    $nurseryGroup = Group::useService()->insertGroup('Hort');

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
//                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
//                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        list($firstName, $lastName) = $importService->splitPersonName('Name', $RunY);
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                $studentGroups
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                if (trim($Document->getValue($Document->getCell($Location['Hort'], $RunY))) == 'x') {
                                    Group::useService()->addGroupPerson($nurseryGroup, $tblPerson);
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
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

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    $gender,
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $level = '';
                                $division = '';
                                $levelDivision = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                    $RunY)));
                                if (strlen($levelDivision) == 2) {
                                    $pos = 1;
                                    $level = substr($levelDivision, 0, $pos);
                                    $division = trim(substr($levelDivision, $pos));
                                }
                                if ($level !== '' && $tblYear && $tblSchoolType) {
                                    $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                                    if ($tblLevel) {
                                        $tblDivision = Division::useService()->insertDivision(
                                            $tblYear,
                                            $tblLevel,
                                            $division
                                        );

                                        if ($tblDivision) {
                                            Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                        }
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                }

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                list($streetName, $streetNumber) = $importService->splitStreet('Straße', $RunY);
                                if ($streetName !== '' && $streetNumber !== '' && $studentCityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Vater'],
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
                                            trim($Document->getValue($Document->getCell($Location['Titel Vater'], $RunY))),
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

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                        );

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

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));

                                if ($motherLastName == '' && $fatherLastName != '') {
                                    $motherLastName = $fatherLastName;
                                }

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
                                            trim($Document->getValue($Document->getCell($Location['Titel Mutter'], $RunY))),
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

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                        );

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
                    'Name' => null,
                    'Vorname' => null,
                    'Geb.Dat.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Adresse' => null
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
//                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

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
//                                Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);

                                $countStaffExists++;
                            } else {
                                $groupArray = array();
                                $groupArray[] = Group::useService()->getGroupByMetaTable('COMMON');
                                $groupArray[] = $tblStaffGroup;
//                                $groupArray[] = $tblTeacherGroup;

                                $tblPerson = Person::useService()->insertPerson(
                                    null,
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
                                        '',
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    // Address
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                    list($streetName, $streetNumber) = $importService->splitStreet('Adresse', $RunY);
                                    $streetNumber = str_replace(' ', '', $streetNumber);

                                    if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, '', ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                    }
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
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createInterestedPersonsFromFile(
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
                    'Schuljahr' => null,
                    'Nachname Sorg1' => null,
                    'Vorname Sorg1' => null,
                    'Nachname Sorg2' => null,
                    'Vorname Sorg2' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Straße' => null,
                    'HausNr.' => null,
                    'Telefon' => null,
                    'Vorname' => null,
                    'Nachname' => null,
                    'Bemerkung 1' => null,
                    'Geburtsdatum' => null,
                    'Anmeldung' => null,
                    'Bemerkung 2' => null,
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
                    $countMother = 0;
                    $countMotherExists = 0;
                    $countFather = 0;
                    $countFatherExists = 0;

                    $error = array();

                    $tblRelationshipTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblSchoolType = Type::useService()->getTypeByName('Grundschule');

                    $importService = new ImportService($Location, $Document);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        if ($lastName == '') {
                            $lastName = trim($Document->getValue($Document->getCell($Location['Nachname Sorg1'], $RunY)));
                        }

                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT'),
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Interessent konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
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
                                if (($remark1 = trim($Document->getValue($Document->getCell($Location['Bemerkung 1'], $RunY)))) != '') {
                                    $remark .= $remark1 . " \n";
                                }
                                if (($remark2 = trim($Document->getValue($Document->getCell($Location['Bemerkung 2'], $RunY)))) != '') {
                                    $remark .= $remark2 . " \n";
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    '',
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                $dayReservation = trim($Document->getValue($Document->getCell($Location['Anmeldung'], $RunY)));
                                if ($dayReservation !== '') {
                                    try {
                                        $reservationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($dayReservation));
                                    } catch (\Exception $ex) {
                                        $reservationDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Anmeldedatum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $reservationDate = '';
                                }

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    $reservationDate,
                                    '',
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY))),
                                    '1',
                                    $tblSchoolType,
                                    null,
                                    ''
                                );

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['HausNr.'], $RunY)));
                                if ($streetName !== '' && $streetNumber !== '' && $studentCityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Sorg1'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Sorg1'],
                                    $RunY)));
                                if ($motherLastName != '') {
                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonMotherExists) {
                                        $tblPersonMother = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $motherFirstName,
                                            '',
                                            $motherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMother,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            null
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                        );

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            null
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Sorg1 wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Nachname Sorg2'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Sorg2'],
                                    $RunY)));
                                if ($fatherLastName == '') {
                                    $fatherLastName = $motherLastName;
                                }

                                if ($fatherLastName != '') {
                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $studentCityCode
                                    );
                                    if (!$tblPersonFatherExists) {
                                        $tblPersonFather = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $fatherFirstName,
                                            '',
                                            $fatherLastName,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFather,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            null
                                        );

                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather, $streetName, $streetNumber, $studentCityCode, $cityName, '', ''
                                        );

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            '',
                                            null
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Sorg2 wurde nicht angelegt,
                                            da schon eine Person mit gleichen Namen und gleicher PLZ existiert.
                                            Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                $phone = trim($Document->getValue($Document->getCell($Location['Telefon'], $RunY)));
                                $importService->insertPrivatePhoneWithSeparator($tblPerson, 'Telefon', $RunY, ';');
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countStudent . ' Interessenten erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Sorg2 erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Sorg2 exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Sorg1 erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Sorg1 exisistieren bereits.') : '')
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