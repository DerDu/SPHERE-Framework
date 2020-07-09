<?php

namespace SPHERE\Application\Transfer\Import\FSE;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
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
 * @package SPHERE\Application\Transfer\Import\FSE
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createMembersFromFile(
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
                    'Titel' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Strasse' => null,
                    'Ort' => null,
                    'PLZ' => null,
                    'Fax' => null,
                    'email' => null,
                    'Antragsdatum' => null,
                    'Aufnahmedatum' => null,
                    'Geburtsdatum' => null,
                    'Geschlecht' => null
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
                    $countMembers = 0;
                    $error = array();

                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');

                    $groups = array(
                        0 => $tblGroupCommon
                    );

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            $tblPerson = Person::useService()->insertPerson(
                                null,
                                trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                $firstName,
                                '',
                                $lastName,
                                $groups,
                                '',
                                trim($Document->getValue($Document->getCell($Location['ID'], $RunY)))
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person konnte nicht angelegt werden.';
                            } else {
                                $countMembers++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                                if ($gender == '2') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == '1') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                    $RunY)));
                                if ($day !== '' && $day !== '0000-00-00') {
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
                                $day1 = trim($Document->getValue($Document->getCell($Location['Antragsdatum'], $RunY)));
                                if ($day1 !== '' && $day1 !== '0000-00-00') {
                                    try {
                                        $applicationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day1));
                                        $remark = 'Antragsdatum: ' . $applicationDate;
                                    } catch (\Exception $ex) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Antragsdatum: ' . $ex->getMessage();
                                    }
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
                                    $remark
                                );

                                // Address
                                $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
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

                                // Contact
                                $importService->insertPrivateFax($tblPerson, 'Fax', $RunY);
                                $importService->insertPrivateMail($tblPerson, 'email', $RunY);
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countMembers . ' Personen erfolgreich angelegt.')
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
    public function createMemberStaffsFromFile(
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
                    'Titel' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Strasse' => null,
                    'Ort' => null,
                    'PLZ' => null,
                    'Tel' => null,
                    'Fax' => null,
                    'mobil' => null,
                    'email' => null,
                    'Antragsdatum' => null,
                    'Aufnahmedatum' => null,
                    'Geburtsdatum' => null,
                    'Geschlecht' => null,
                    'Status' => null,
                    'Stat_text' => null,
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
                    $countMembers = 0;
                    $countCompanies = 0;
                    $error = array();

                    $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                    $tblGroupStaff = Group::useService()->getGroupByMetaTable('STAFF');

                    $tblCompanyGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');

                    $groupsPerson = array(
                        0 => $tblGroupCommon,
                        1 => $tblGroupStaff,
                    );

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        $status = trim($Document->getValue($Document->getCell($Location['Status'], $RunY)));
                        if ($status == 9) {
                            $title = trim($Document->getValue($Document->getCell($Location['Titel'], $RunY)));
                            $name = ($title !== '' ? $title . ' ' : '')
                                . ($lastName !== '' ? $lastName . ' ' : '');
                            if (($tblCompany = Company::useService()->insertCompany(
                                trim($name), '', $firstName
                            ))) {
                                $countCompanies++;
                                CompanyGroup::useService()->addGroupCompany($tblCompanyGroupCommon, $tblCompany);
                                $groupName = trim($Document->getValue($Document->getCell($Location['Stat_text'], $RunY)));
                                if ($groupName !== '') {
                                    if (($tblCompanyGroup = CompanyGroup::useService()->createGroupFromImport($groupName))) {
                                        CompanyGroup::useService()->addGroupCompany($tblCompanyGroup, $tblCompany);
                                    }
                                }

                                // Address
                                $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
                                list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                $streetNumber = str_replace(' ', '', $streetNumber);
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $cityName
                                ) {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany, $streetName, $streetNumber, $studentCityCode, $cityName,
                                        $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse ist nicht vollständig.';
                                }
                            }
                        } else {
                            if ($firstName === '' || $lastName === '') {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                            } else {
                                $tblPerson = Person::useService()->insertPerson(
                                    null,
                                    trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                    $firstName,
                                    '',
                                    $lastName,
                                    $groupsPerson,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['ID'], $RunY)))
                                );

                                if ($tblPerson === false) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person konnte nicht angelegt werden.';
                                } else {
                                    $countMembers++;

                                    $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                        $RunY)));
                                    if ($gender == '2') {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                    } elseif ($gender == '1') {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                    } else {
                                        $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                    }

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($day !== '' && $day !== '0000-00-00') {
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
                                        '',
                                        $gender,
                                        '',
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        ''
                                    );

                                    // Address
                                    $studentCityCode = $importService->formatZipCode('PLZ', $RunY);
                                    list($cityName, $cityDistrict) = $importService->splitCity('Ort', $RunY);
                                    list($streetName, $streetNumber) = $importService->splitStreet('Strasse', $RunY);
                                    $streetNumber = str_replace(' ', '', $streetNumber);
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

                                    // Club
                                    $applicationDate = '';
                                    $day1 = trim($Document->getValue($Document->getCell($Location['Antragsdatum'],
                                        $RunY)));
                                    if ($day1 !== '' && $day1 !== '0000-00-00') {
                                        try {
                                            $applicationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day1));
                                        } catch (\Exception $ex) {
                                            $applicationDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Antragsdatum: ' . $ex->getMessage();
                                        }
                                    }

                                    $entryDate = '';
                                    $day2 = trim($Document->getValue($Document->getCell($Location['Aufnahmedatum'],
                                        $RunY)));
                                    if ($day2 !== '' && $day2 !== '0000-00-00') {
                                        try {
                                            $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day2));
                                        } catch (\Exception $ex) {
                                            $entryDate = '';
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Aufnahmedatum: ' . $ex->getMessage();
                                        }

                                    }

                                    if ($applicationDate !== '' or $entryDate !== '') {
                                        if ($applicationDate !== '') {
                                            $remark = 'Antragsdatum: ' . $applicationDate;
                                        } else {
                                            $remark = '';
                                        }

                                        Club::useService()->insertMeta(
                                            $tblPerson,
                                            '',
                                            $entryDate,
                                            '',
                                            $remark
                                        );
                                    }

                                    // Contact
                                    $importService->insertPrivatePhone($tblPerson, 'Tel', $RunY);
                                    $importService->insertPrivatePhone($tblPerson, 'mobil', $RunY);
                                    $importService->insertPrivateFax($tblPerson, 'Fax', $RunY);
                                    $importService->insertPrivateMail($tblPerson, 'email', $RunY);

                                    $groupName = trim($Document->getValue($Document->getCell($Location['Stat_text'], $RunY)));
                                    if ($groupName !== '') {
                                        if (($tblGroup = Group::useService()->insertGroup(
                                            $groupName
                                        ))) {
                                            Group::useService()->addGroupPerson(
                                                $tblGroup,
                                                $tblPerson
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countMembers . ' Personen erfolgreich angelegt.')
                        . new Success('Es wurden ' . $countCompanies . ' Firmen erfolgreich angelegt.')
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
                    'ID' => null,
                    'Schulname' => null,
                    'PLZ' => null,
                    'Ort' => null,
                    'Adresse' => null,
                    'OT' => null,
                    'Anrede' => null,
                    'Nachname' => null,
                    'Vorname' => null,
                    'Telefon' => null,
                    'Fax' => null
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                $tblGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');
                $tblGroupSchool = CompanyGroup::useService()->getGroupByMetaTable('SCHOOL');

                $tblTypeCommon = Relationship::useService()->getTypeByName('Allgemein');

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
                            if (($tblCompany = Company::useService()->insertCompany(
                                $name,
                                '',
                                '',
                                trim($Document->getValue($Document->getCell($Location['ID'], $RunY)))
                            ))) {
                                $countNewCompany++;

                                CompanyGroup::useService()->addGroupCompany(
                                    $tblGroupCommon,
                                    $tblCompany
                                );
                                CompanyGroup::useService()->addGroupCompany(
                                    $tblGroupSchool,
                                    $tblCompany
                                );
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
                    'ID' => null,
                    'A_ID_Schueler' => null,
                    'A_ID_Mutter' => null,
                    'A_ID_Vater' => null,
                    'Zugang_Datum' => null,
                    'Geburtsort' => null,
                    'Staatsangeh' => null,
                    'Krankenkasse' => null,
                    'Versichertenname' => null,
                    'Besonderheiten' => null,
                    'Religion' => null,
                    'Taetigkeit_Mutter' => null,
                    'Firma_Mutter' => null,
                    'Tel_d_Mutter' => null,
                    'Taetigkeit_Vater' => null,
                    'Firma_Vater' => null,
                    'Tel_d_Vater' => null,
                    'Tel_er_privat' => null,
                    'Tel_er_tag' => null,
                    'Tel_tag_wer' => null,
                    'Tel_er_mobil' => null,
                    'Tel_mobil_wer' => null,
                    'Tel_er_sonst' => null,
                    'abgebende_Schule' => null,
                    'Schuljahr' => null,
                    'Klassenstufe' => null,
                    'Zusatz' => null,
                    'Mit_ID' => null,
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

                    $tblYearOld = $importService->insertSchoolYear(18);
                    $tblYear = $importService->insertSchoolYear(19);

                    $tblRelationshipTypeCustody = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');
                    $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
                    $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
                    $tblGroupStudentArchive = Group::useService()->insertGroup('Ehemalige Schüler');
                    $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

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
                        $studentId = trim($Document->getValue($Document->getCell($Location['A_ID_Schueler'], $RunY)));
                        if (($tblPerson = Person::useService()->getPersonByImportId($studentId))) {
                            $yearId = trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY)));
                            if ($yearId == 17) {
                                $tblGroupToAdd = $tblGroupStudentArchive;
                                $tblYearDivision = $tblYearOld;
                            } else {
                                $tblGroupToAdd = $tblGroupStudent;
                                $tblYearDivision = $tblYear;
                            }

                            Group::useService()->addGroupPerson($tblGroupToAdd, $tblPerson);

                            $religion = trim($Document->getValue($Document->getCell($Location['Religion'], $RunY)));
                            if ($religion == '1') {
                                $religion = 'Religion';
                            } elseif ($religion == '2') {
                                $religion = 'Ethik';
                            } else {
                                $religion = '';
                            }

                            $remark = '';
                            if (($tblCommon = $tblPerson->getCommon())) {
                                $remark = $tblCommon->getRemark();
                            }

                            $special = trim($Document->getValue($Document->getCell($Location['Besonderheiten'], $RunY)));
                            if ($special !== '') {
                                $remark = ($remark ? $remark . " \n"  : '') . 'Besonderheiten: ' . $special;
                            }

                            $insuranceName = trim($Document->getValue($Document->getCell($Location['Versichertenname'], $RunY)));
                            if ($insuranceName !== '') {
                                $remark = ($remark ? $remark . " \n"  : '') . 'Versichertenname: ' . $insuranceName;
                            }

                            if (($tblCommon)) {
                                Common::useService()->updateCommon($tblCommon, $remark);
                                if (($tblCommonBirthDates  = $tblCommon->getTblCommonBirthDates())) {
                                    Common::useService()->updateCommonBirthDates(
                                        $tblCommonBirthDates,
                                        $tblCommonBirthDates->getBirthday(),
                                        trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                        $tblCommonBirthDates->getTblCommonGender() ? $tblCommonBirthDates->getTblCommonGender()->getId() : 0
                                    );
                                }
                                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                                    Common::useService()->updateCommonInformation(
                                        $tblCommonInformation,
                                        trim($Document->getValue($Document->getCell($Location['Staatsangeh'], $RunY))),
                                        '',
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        ''
                                    );
                                }
                            }

                            $level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'], $RunY)));
                            $division = trim($Document->getValue($Document->getCell($Location['Zusatz'], $RunY)));
                            if ($level !== '') {
                                if (($tblLevel = Division::useService()->insertLevel($tblSchoolType, $level))
                                    && ($tblDivision = Division::useService()->insertDivision($tblYearDivision, $tblLevel, $division))
                                ) {
                                    Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);

                                    $teacherId = trim($Document->getValue($Document->getCell($Location['Mit_ID'], $RunY)));
                                    if ($teacherId
                                        && !Division::useService()->getDivisionTeacherAllByDivision($tblDivision)
                                    ) {
                                        if (($tblTeacher = Person::useService()->getPersonByImportId($teacherId))) {
                                            Division::useService()->addDivisionTeacher(
                                                $tblDivision,
                                                $tblTeacher,
                                                ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Lehrer mit der ID=' . $teacherId . ' wurde nicht gefunden';
                                        }
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                }
                            }

                            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                '',
                                '',
                                trim($Document->getValue($Document->getCell($Location['Krankenkasse'], $RunY)))
                            );

                            if (($tblStudent = Student::useService()->insertStudent(
                                $tblPerson,
                                trim($Document->getValue($Document->getCell($Location['ID'], $RunY))),
                                $tblStudentMedicalRecord
                            ))) {
                                $tblCompany = false;
                                $schoolId = trim($Document->getValue($Document->getCell($Location['abgebende_Schule'],
                                    $RunY)));
                                if ($schoolId) {
                                    if (!($tblCompany = Company::useService()->getCompanyByImportId($schoolId))) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Abgebende Schule mit der ID=' . $schoolId . ' wurde nicht gefunden';
                                    }
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Zugang_Datum'],
                                    $RunY)));
                                if ($day !== '' && $day !== '0000-00-00') {
                                    try {
                                        $arriveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } catch (\Exception $ex) {
                                        $arriveDate = '';
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Ungültiges Zugang_Datum: ' . $ex->getMessage();
                                    }

                                } else {
                                    $arriveDate = '';
                                }

                                Student::useService()->insertStudentTransfer(
                                    $tblStudent,
                                    $tblStudentTransferTypeArrive,
                                    $tblCompany ? $tblCompany : null,
                                    null,
                                    null,
                                    $arriveDate,
                                    ''
                                );

                                if ($religion != ''){
                                    if ($religion == 'Ethik') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('ETH');
                                    } elseif ($religion == 'Religion') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym('RE/E');
                                    } else {
                                        $tblSubject = false;
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Fach-Religion nicht gefunden: ' . $religion;
                                    }

                                    if ($tblSubject
                                        && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
                                        && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1'))
                                    ) {
                                        Student::useService()->addStudentSubject($tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking, $tblSubject);
                                    }
                                }
                            }

                            // Mutter
                            $motherId = trim($Document->getValue($Document->getCell($Location['A_ID_Mutter'], $RunY)));
                            if (($tblPersonMother = Person::useService()->getPersonByImportId($motherId))) {
                                Group::useService()->addGroupPerson($tblGroupCustody, $tblPersonMother);
                                Relationship::useService()->insertRelationshipToPerson(
                                    $tblPersonMother,
                                    $tblPerson,
                                    $tblRelationshipTypeCustody,
                                    '',
                                    $genderSetting == 'Weiblich' ? 1 : 2
                                );

                                // Anrede
                                Person::useService()->updateSalutation($tblPersonMother, Person::useService()->getSalutationByName('Frau'));

                                if (($tblCommon = $tblPersonMother->getCommon())) {
                                    if (($tblCommonBirthDates  = $tblCommon->getTblCommonBirthDates())) {
                                        Common::useService()->updateCommonBirthDates(
                                            $tblCommonBirthDates,
                                            $tblCommonBirthDates->getBirthday(),
                                            $tblCommonBirthDates->getBirthplace(),
                                            2
                                        );
                                    }
                                }

                                Custody::useService()->insertMeta(
                                    $tblPersonMother,
                                    trim($Document->getValue($Document->getCell($Location['Taetigkeit_Mutter'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Firma_Mutter'], $RunY))),
                                    ''
                                );

                                $importService->insertPrivatePhone($tblPersonMother, 'Tel_d_Mutter', $RunY, '');
                            } else {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Mutter mit der ID=' . $motherId . ' wurde nicht gefunden';
                            }

                            // Vater
                            $fatherId = trim($Document->getValue($Document->getCell($Location['A_ID_Vater'], $RunY)));
                            if (($tblPersonFather = Person::useService()->getPersonByImportId($fatherId))) {
                                Group::useService()->addGroupPerson($tblGroupCustody, $tblPersonFather);
                                Relationship::useService()->insertRelationshipToPerson(
                                    $tblPersonFather,
                                    $tblPerson,
                                    $tblRelationshipTypeCustody,
                                    '',
                                    $genderSetting == 'Weiblich' ? 2 : 1
                                );

                                // Anrede
                                Person::useService()->updateSalutation($tblPersonFather, Person::useService()->getSalutationByName('Herr'));

                                if (($tblCommon = $tblPersonFather->getCommon())) {
                                    if (($tblCommonBirthDates  = $tblCommon->getTblCommonBirthDates())) {
                                        Common::useService()->updateCommonBirthDates(
                                            $tblCommonBirthDates,
                                            $tblCommonBirthDates->getBirthday(),
                                            $tblCommonBirthDates->getBirthplace(),
                                            1
                                        );
                                    }
                                }

                                Custody::useService()->insertMeta(
                                    $tblPersonFather,
                                    trim($Document->getValue($Document->getCell($Location['Taetigkeit_Vater'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Firma_Vater'], $RunY))),
                                    ''
                                );

                                $importService->insertPrivatePhone($tblPersonFather, 'Tel_d_Vater', $RunY, '');
                            } else {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Vater mit der ID=' . $fatherId . ' wurde nicht gefunden';
                            }

                            $importService->insertPrivatePhone($tblPerson, 'Tel_er_privat', $RunY);
                            $importService->insertBusinessPhone($tblPerson, 'Tel_er_tag', $RunY,
                                trim($Document->getValue($Document->getCell($Location['Tel_tag_wer'], $RunY)))
                            );
                            $importService->insertPrivatePhone($tblPerson, 'Tel_er_mobil', $RunY,
                                trim($Document->getValue($Document->getCell($Location['Tel_mobil_wer'], $RunY)))
                            );
                            $importService->insertPrivatePhone($tblPerson, 'Tel_er_sonst', $RunY, 'Sonstige');
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler mit der ID=' . $studentId . ' wurde nicht gefunden';
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