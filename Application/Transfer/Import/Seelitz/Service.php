<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.09.2016
 * Time: 09:33
 */

namespace SPHERE\Application\Transfer\Import\Seelitz;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as GroupCompany;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
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
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Seelitz
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
                    'Gruppe' => null,
                    'Geschl.' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'ggf. Ortsteil' => null,
                    'Telefon' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Vorname Mutter' => null,
                    'Name Mutter' => null,
                    'Vorname Vater' => null,
                    'Name Vater' => null,
                    'Geschwister' => null,
                    'angemeldet am' => null,
                    'Anmeldung bestätigt' => null,
                    'Email' => null,
                    'Staatsangeh.' => null,
                    'Religion' => null,
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
                    $countProspect = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $tblGroupProspect = Group::useService()->getGroupByMetaTable('PROSPECT');

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Prospect
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => $tblGroupProspect,
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countProspect++;

                                $tblGroup = false;
                                $group = trim($Document->getValue($Document->getCell($Location['Gruppe'], $RunY)));
                                if ($group !== '' && !($tblGroup = (Group::useService()->getGroupByName($group)))) {
                                    $tblGroup = Group::useService()->createGroupFromImport($group);
                                }

                                if ($tblGroup) {
                                    Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschl.'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
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

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Staatsangeh.'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Religion'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule

                                // Address
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['ggf. Ortsteil'],
                                    $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                    $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $cityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                if ($fatherLastName !== '' && $fatherFirstName !== '') {

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $cityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $tblSalutation = Person::useService()->getSalutationById(1);
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;

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

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonFather,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Vaters wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Vater'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                if ($motherLastName !== '' && $motherFirstName !== '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $cityCode
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
                                            'Mutter'
                                        );

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonMother,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Mutter wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Mutter'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
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

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    $mailAddressList = explode(',', $mailAddress);
                                    foreach ($mailAddressList as $addressItem) {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            trim($addressItem),
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['angemeldet am'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $day = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $day = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Abgang_am Datum: ' . $ex->getMessage();
                                    }
                                } else {
                                    $day = '';
                                }

                                if ($group !== '' && (($pos = strpos($group, ' ')) !== false)) {
                                    $year = substr($group, $pos + 1);
                                    $reservationYear = '20' . str_replace('-', '/', $year);
                                } else {
                                    $reservationYear = '';
                                }

                                $remark = '';
                                if (($siblings = trim($Document->getValue($Document->getCell($Location['Geschwister'],
                                        $RunY)))) && $siblings !== '-'
                                ) {
                                    $remark = 'Geschwister: ' . $siblings;
                                }
                                if (($confirmation = trim($Document->getValue($Document->getCell($Location['Anmeldung bestätigt'],
                                    $RunY))))
                                ) {
                                    $remark = trim($remark . ' Anmeldung bestätigt: ' . $confirmation . ' ');
                                }

                                Prospect::useService()->insertMeta(
                                    $tblPerson, $day, '', '', $reservationYear, '', $tblSchoolType, null, $remark
                                );
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countProspect . ' Schüler erfolgreich angelegt.') .
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

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
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
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Ortsteil' => null,
                    'Geburtsdatum' => null,
                    'Telefon' => null,
                    'Telefon mobil' => null,
                    'Mail' => null,
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
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    $tblStaffGroup = Group::useService()->getGroupByMetaTable('STAFF');
//                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {

                            $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'],
                                $RunY)));

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, 
                                da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExits);
//                                Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);

                                $countStaffExists++;

                            } else {

                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    '',
                                    $firstName,
                                    '',
                                    $lastName,
                                    array(
                                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                        1 => $tblStaffGroup,
//                                        2 => $tblTeacherGroup
                                    )
                                );

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
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
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    // Address
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                        $RunY)));
                                    $StreetName = trim($Document->getValue($Document->getCell($Location['Straße'],
                                        $RunY)));
                                    $StreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                        $RunY)));
                                    $district = trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                        $RunY)));
                                    if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $district, ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Person wurde nicht angelegt, 
                                        da sie keine vollständige Adresse besitzt.';
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
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

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon mobil'],
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

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['Mail'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countStaff . ' Mitarbeiter erfolgreich angelegt.') .
                        ($countStaffExists > 0 ?
                            new Warning($countStaffExists . ' Mitarbeiter exisistieren bereits.') : '')
                        . (empty($error) ? '' : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                ))
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
                    'Schuljahr' => null,
                    'Klasse' => null,
                    'Geschlecht' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'ggf. Ortsteil' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Bekenntnis' => null,
                    'Vorname Mutter' => null,
                    'Name Mutter' => null,
                    'Vorname Vater' => null,
                    'Name Vater' => null,
                    'Beruf Mutter' => null,
                    'Beruf Vater' => null,
                    'Telefon' => null,
                    'Email-Adresse' => null,
                    'Hortkind' => null,
                    'Beförderung früh' => null,
                    'Beförderung nachm.' => null,
                    'Nr. M. Ulbricht' => null,
                    'aufgenommen am' => null,
                    'aufgenommen von' => null,
                    'abgegangen am' => null,
                    'abgegangen nach' => null,
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

                    if (!($tblGroupFormerStudents = (Group::useService()->getGroupByName('Ehemalige Schüler')))) {
                        $tblGroupFormerStudents = Group::useService()->createGroupFromImport('Ehemalige Schüler');
                    }
                    if (!($tblGroupDayCare = (Group::useService()->getGroupByName('Hort')))) {
                        $tblGroupDayCare = Group::useService()->createGroupFromImport('Hort');
                    }

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $year = trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY)));
                            if ($year == 16) {
                                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                            } else {
                                $tblGroup = $tblGroupFormerStudents;
                            }

                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => $tblGroup,
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
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

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Bekenntnis'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule

                                // division
                                $tblDivision = false;
                                $division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                    $RunY)));
                                if ($division !== '') {
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
                                            $tblLevel = Division::useService()->insertLevel($tblSchoolType, $division);
                                            if ($tblLevel) {
                                                $tblDivision = Division::useService()->insertDivision(
                                                    $tblYear,
                                                    $tblLevel,
                                                    ''
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
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['ggf. Ortsteil'],
                                    $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                    $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $cityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));


                                if ($fatherLastName !== '' && $fatherFirstName !== '') {

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $cityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $tblSalutation = Person::useService()->getSalutationById(1);
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;

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

                                            Custody::useService()->insertMeta(
                                                $tblPersonFather,
                                                trim($Document->getValue($Document->getCell($Location['Beruf Vater'],
                                                    $RunY))),
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

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonFather,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Vaters wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Vater'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));

                                if ($motherLastName !== '' && $motherFirstName !== '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $cityCode
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

                                            Custody::useService()->insertMeta(
                                                $tblPersonMother,
                                                trim($Document->getValue($Document->getCell($Location['Beruf Mutter'],
                                                    $RunY))),
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

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonMother,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Mutter wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Mutter'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
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

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Email-Adresse'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    $mailAddressList = explode(',', $mailAddress);
                                    foreach ($mailAddressList as $addressItem) {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            trim($addressItem),
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }

                                if (trim($Document->getValue($Document->getCell($Location['Hortkind'],
                                        $RunY))) !== ''
                                ) {
                                    Group::useService()->addGroupPerson($tblGroupDayCare, $tblPerson);
                                }

                                /*
                                 * student
                                 */
                                $earlyTransport = trim($Document->getValue($Document->getCell($Location['Beförderung früh'],
                                    $RunY)));
                                $lateTransport = trim($Document->getValue($Document->getCell($Location['Beförderung nachm.'],
                                    $RunY)));
                                $tblStudent = Student::useService()->insertStudent(
                                    $tblPerson,
                                    trim($Document->getValue($Document->getCell($Location['Nr. M. Ulbricht'],
                                        $RunY))),
                                    null,
                                    Student::useService()->insertStudentTransport(
                                        '',
                                        '',
                                        '',
                                        trim(($earlyTransport == 'x' ? 'Beförderung früh ' : '')
                                            . ($lateTransport == 'x' ? 'Beförderung nachm.' : ''))
                                    ),
                                    null,
                                    null,
                                    null,
                                    null
                                );
                                if ($tblStudent) {

                                    // Schülertransfer
                                    $day = trim($Document->getValue($Document->getCell($Location['aufgenommen am'],
                                        $RunY)));
                                    if ($day !== '') {
                                        try {
                                            $arriveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        } catch (\Exception $ex) {
                                            $arriveDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Aufnahme_am Datum: ' . $ex->getMessage();
                                        }

                                    } else {
                                        $arriveDate = '';
                                    }
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['aufgenommen von'],
                                        $RunY)));
                                    if ($company !== '' && $company !== '?' && $company !== '-') {
                                        $arriveSchool = Company::useService()->insertCompany($company);
                                        if ($arriveSchool) {
                                            GroupCompany::useService()->addGroupCompany(
                                                GroupCompany::useService()->getGroupByMetaTable('COMMON'),
                                                $arriveSchool
                                            );

                                            if (strpos($company, 'Kiga') !== false) {
                                                GroupCompany::useService()->addGroupCompany(
                                                    GroupCompany::useService()->getGroupByMetaTable('NURSERY'),
                                                    $arriveSchool
                                                );
                                            } else {
                                                GroupCompany::useService()->addGroupCompany(
                                                    GroupCompany::useService()->getGroupByMetaTable('SCHOOL'),
                                                    $arriveSchool
                                                );
                                            }
                                        }
                                    }
                                    $tblLastSchoolType = false;
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        $arriveSchool ? $arriveSchool : null,
                                        $tblLastSchoolType ? $tblLastSchoolType : null,
                                        null,
                                        $arriveDate,
                                        ''
                                    );

                                    $day = trim($Document->getValue($Document->getCell($Location['abgegangen am'],
                                        $RunY)));
                                    if ($day !== '') {
                                        try {
                                            $leaveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        } catch (\Exception $ex) {
                                            $leaveDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Schüler_Abgang_am Datum: ' . $ex->getMessage();
                                        }
                                    } else {
                                        $leaveDate = '';
                                    }
                                    $leaveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['abgegangen nach'],
                                        $RunY)));
                                    if ($company !== '' && $company !== '?' && $company !== '-') {
                                        $leaveSchool = Company::useService()->insertCompany($company);
                                        if ($leaveSchool) {
                                            GroupCompany::useService()->addGroupCompany(
                                                GroupCompany::useService()->getGroupByMetaTable('COMMON'),
                                                $leaveSchool
                                            );

                                            if (strpos($company, 'Kiga') !== false) {
                                                GroupCompany::useService()->addGroupCompany(
                                                    GroupCompany::useService()->getGroupByMetaTable('NURSERY'),
                                                    $leaveSchool
                                                );
                                            } else {
                                                GroupCompany::useService()->addGroupCompany(
                                                    GroupCompany::useService()->getGroupByMetaTable('SCHOOL'),
                                                    $leaveSchool
                                                );
                                            }
                                        }
                                    }
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        $leaveSchool ? $leaveSchool : null,
                                        null,
                                        null,
                                        $leaveDate,
                                        ''
                                    );

                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        $tblSchoolType ? $tblSchoolType : null,
                                        null,
                                        null,
                                        ''
                                    );
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

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createKindergartenFromFile(
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
                    'Einschulung' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'ggf. Ortsteil' => null,
                    'Geburtsdatum' => null,
                    'Vorname Mutter' => null,
                    'Name Mutter' => null,
                    'Vorname Vater' => null,
                    'Name Vater' => null,
                    'Krippe' => null,
                    'Kiga' => null,
                    'Telefon' => null,
                    'Email-Adresse Mutter' => null,
                    'Email-Adresse Vater' => null,
                    'aufgenommen am' => null,
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
                    $countProspect = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    if (!($tblGroupKrippe = (Group::useService()->getGroupByName('Krippe')))) {
                        $tblGroupKrippe = Group::useService()->createGroupFromImport('Krippe');
                    }
                    if (!($tblGroupKiga = (Group::useService()->getGroupByName('Kiga')))) {
                        $tblGroupKiga = Group::useService()->createGroupFromImport('Kiga');
                    }

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Kiga
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {

                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON')
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countProspect++;

                                $group = trim($Document->getValue($Document->getCell($Location['Krippe'], $RunY)));
                                if ($group !== '') {
                                    Group::useService()->addGroupPerson($tblGroupKrippe, $tblPerson);
                                }
                                $group = trim($Document->getValue($Document->getCell($Location['Kiga'], $RunY)));
                                if ($group !== '') {
                                    Group::useService()->addGroupPerson($tblGroupKiga, $tblPerson);
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

                                $remark = trim($Document->getValue($Document->getCell($Location['Einschulung'],
                                    $RunY)));
                                if ($remark !== '') {
                                    $remark = 'Einschulung: ' . $remark;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['aufgenommen am'],
                                    $RunY)));
                                if ($day !== '') {
                                    try {
                                        $day = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                        $remark = trim($remark . ' aufgenommen am: ' . $day);
                                    } catch (\Exception $ex) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Geburtsdatum: ' . $ex->getMessage();
                                    }
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    '',
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    $remark
                                );

                                // Address
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityDistrict = trim($Document->getValue($Document->getCell($Location['ggf. Ortsteil'],
                                    $RunY)));
                                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'],
                                    $RunY)));
                                $streetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                    $RunY)));
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $cityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $cityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                if ($fatherLastName !== '' && $fatherFirstName !== '') {

                                    $tblPersonFatherExists = Person::useService()->existsPerson(
                                        $fatherFirstName,
                                        $fatherLastName,
                                        $cityCode
                                    );

                                    if (!$tblPersonFatherExists) {
                                        $tblSalutation = Person::useService()->getSalutationById(1);
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;

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

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonFather,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse des Vaters wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $mailAddress = trim($Document->getValue($Document->getCell($Location['Email-Adresse Vater'],
                                            $RunY)));
                                        if ($mailAddress != '') {
                                            $mailAddressList = explode(',', $mailAddress);
                                            foreach ($mailAddressList as $addressItem) {
                                                Mail::useService()->insertMailToPerson(
                                                    $tblPersonFather,
                                                    trim($addressItem),
                                                    Mail::useService()->getTypeById(1),
                                                    ''
                                                );
                                            }
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Vater'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                if ($motherLastName !== '' && $motherFirstName !== '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $cityCode
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
                                            'Mutter'
                                        );

                                        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonMother,
                                                $streetName,
                                                $streetNumber,
                                                $cityCode,
                                                $cityName,
                                                $cityDistrict,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Mutter wurde nicht angelegt, da keine vollständige Adresse hinterlegt ist.';
                                        }

                                        $mailAddress = trim($Document->getValue($Document->getCell($Location['Email-Adresse Mutter'],
                                            $RunY)));
                                        if ($mailAddress != '') {
                                            $mailAddressList = explode(',', $mailAddress);
                                            foreach ($mailAddressList as $addressItem) {
                                                Mail::useService()->insertMailToPerson(
                                                    $tblPersonMother,
                                                    trim($addressItem),
                                                    Mail::useService()->getTypeById(1),
                                                    ''
                                                );
                                            }
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            'Mutter'
                                        );

                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden Person verknüpft';

                                        $countMotherExists++;
                                    }
                                }

                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    if (0 !== strpos($phoneNumber, '0')) {
                                        $phoneNumber = '0' . $phoneNumber;
                                    }
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
                            }
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden ' . $countProspect . ' Personen erfolgreich angelegt.') .
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