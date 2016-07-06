<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 04.07.2016
 * Time: 08:29
 */

namespace SPHERE\Application\Transfer\Import\Radebeul;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Radebeul
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @return IFormInterface|Danger|Success|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createCompaniesFromFile(
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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
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
                    'E.nummer' => null,
                    'Einrichtungsname' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Ort' => null,
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
                    $countCompany = 0;

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $companyName = trim($Document->getValue($Document->getCell($Location['Einrichtungsname'],
                            $RunY)));

                        if ($companyName) {
                            $tblCompany = Company::useService()->insertCompany(
                                $companyName,
                                trim($Document->getValue($Document->getCell($Location['E.nummer'], $RunY)))
                            );
                            if ($tblCompany) {
                                $countCompany++;

                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON'),
                                    $tblCompany
                                );
                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('SCHOOL'),
                                    $tblCompany
                                );

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
                                $zipCode = trim($Document->getValue($Document->getCell($Location['Plz'],
                                    $RunY)));
                                $city = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));

                                if ($streetName && $streetNumber && $zipCode && $city) {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany,
                                        $streetName,
                                        $streetNumber,
                                        $zipCode,
                                        $city,
                                        '',
                                        ''
                                    );
                                }
                            }
                        }
                    }
                    return
                        new Success('Es wurden ' . $countCompany . ' Schulen erfolgreich angelegt.');
                } else {
                    Debugger::screenDump($Location);
                    return new Info(json_encode($Location)) .
                    new Danger(
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
                    'Schüler_Name' => null,
                    'Schüler_Vorname' => null,
                    'Schüler_Stammgruppe' => null,
                    'Schüler_Klassenstufe' => null,
                    'Schüler_Geschlecht' => null,
                    'Schüler_Staatsangehörigkeit' => null,
                    'Schüler_Straße' => null,
                    'Schüler_Plz' => null,
                    'Schüler_Wohnort' => null,
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Geschwister' => null,
                    'Schüler_Integr_Förderschüler' => null,
                    'Schüler_Konfession' => null,
                    'Schüler_Einschulung_am' => null,
                    'Schüler_Aufnahme_am' => null,
                    'Schüler_abgebende_Schule_ID' => null,
                    'Schüler_aufnehmende_Schule_ID' => null,
                    'Schüler_Abgang_am' => null,
                    'Schüler_Schulpflicht_beginnt_am' => null,
                    'Schüler_allgemeine_Bemerkungen' => null,
                    'Schüler_letzte_Schulart' => null,
                    'Schüler_Förderbedarf' => null,
                    'Schüler_Förderschwerpunkt' => null,
                    'Kommunikation_Telefon1' => null,
                    'Kommunikation_Telefon2' => null,
                    'Kommunikation_Telefon3' => null,
                    'Kommunikation_Telefon4' => null,
                    'Kommunikation_Telefon5' => null,
                    'Kommunikation_Telefon6' => null,
                    'Kommunikation_Email1' => null,
                    'Kommunikation_Email2' => null,
                    'Sorgeberechtigter1_Titel' => null,
                    'Sorgeberechtigter1_Name' => null,
                    'Sorgeberechtigter1_Vorname' => null,
                    'Sorgeberechtigter1_Geschlecht' => null,
                    'Sorgeberechtigter1_Straße' => null,
                    'Sorgeberechtigter1_Plz' => null,
                    'Sorgeberechtigter1_Wohnort' => null,
                    'Sorgeberechtigter2_Titel' => null,
                    'Sorgeberechtigter2_Name' => null,
                    'Sorgeberechtigter2_Vorname' => null,
                    'Sorgeberechtigter2_Geschlecht' => null,
                    'Sorgeberechtigter2_Straße' => null,
                    'Sorgeberechtigter2_Plz' => null,
                    'Sorgeberechtigter2_Wohnort' => null,
                    'Fächer_Religionsunterricht' => null,
                    'Fächer_Fremdsprache1' => null,
                    'Fächer_Fremdsprache1_von' => null,
                    'Fächer_Fremdsprache1_bis' => null,
                    'Fächer_Fremdsprache2' => null,
                    'Fächer_Fremdsprache2_von' => null,
                    'Fächer_Fremdsprache2_bis' => null,
                    'Zusatzfeld1' => null,
                    'Zusatzfeld2' => null,
                    'Zusatzfeld3' => null,
                    'Zusatzfeld4' => null,
                    'Zusatzfeld10' => null,
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

                    $tblGroupHoard = Group::useService()->insertGroup('Hort');

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Schüler_Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Schüler_Name'], $RunY)));
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
                                    1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                                    2 => $tblGroupHoard
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $birthday = trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsdatum'],
                                    $RunY)));
                                if ($birthday !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule

                                // division
                                $tblDivision = false;
                                $year = 16;
                                $division = trim($Document->getValue($Document->getCell($Location['Schüler_Klassenstufe'],
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

                                // Jahrgangsübergreifende Stammgruppe
                                $tblDivision = false;
                                $year = 16;
                                $division = trim($Document->getValue($Document->getCell($Location['Schüler_Stammgruppe'],
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
                                            $tblLevel = Division::useService()->insertLevel($tblSchoolType, $division,
                                                '', true);
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
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse (Stammgruppe) zugeordnet werden.';
                                    }
                                }

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $studentCityName = trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY)));
                                $studentCityDistrict = '';
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }

                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $studentCityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $studentCityName,
                                        $studentCityDistrict, ''
                                    );
                                }

                                $occupationFather = '';
                                $occupationMother = '';
                                $occupation = trim($Document->getValue($Document->getCell($Location['Zusatzfeld10'],
                                    $RunY)));
                                if ($occupation !== '') {
                                    $pos = strpos($occupation, ',');
                                    if ($pos !== false) {
                                        $occupationMother = trim(substr($occupation, 0, $pos));
                                        $occupationFather = trim(substr($occupation, $pos + 1));
                                    } else {
                                        $occupationMother = $occupation;
                                    }
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Name'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Vorname'],
                                    $RunY)));

                                $fatherCityCode = str_pad(
                                    trim(str_replace('D-', '',
                                        $Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Plz'],
                                            $RunY)))),
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
                                        $gender = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Geschlecht'],
                                            $RunY)));
                                        if ($gender == 'm') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                            $tblSalutation = Person::useService()->getSalutationById(1);
                                        } elseif ($gender == 'w') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                            $tblSalutation = Person::useService()->getSalutationById(2);
                                        } else {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                            $tblSalutation = null;
                                        }

                                        $tblPersonFather = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Titel'],
                                                $RunY))),
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

                                            if ($occupationFather !== '') {
                                                Custody::useService()->insertMeta(
                                                    $tblPersonFather,
                                                    $occupationFather,
                                                    '',
                                                    ''
                                                );
                                            }
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
                                $motherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Name'],
                                    $RunY)));
                                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Vorname'],
                                    $RunY)));
                                $motherCityCode = str_pad(
                                    trim(str_replace('D-', '',
                                        $Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Plz'],
                                            $RunY)))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );

                                if ($motherLastName != '') {

                                    $tblPersonMotherExists = Person::useService()->existsPerson(
                                        $motherFirstName,
                                        $motherLastName,
                                        $studentCityCode
                                    );

                                    if (!$tblPersonMotherExists) {
                                        $gender = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Geschlecht'],
                                            $RunY)));
                                        if ($gender == 'm') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                            $tblSalutation = Person::useService()->getSalutationById(1);
                                        } elseif ($gender == 'w') {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                            $tblSalutation = Person::useService()->getSalutationById(2);
                                        } else {
                                            $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                            $tblSalutation = null;
                                        }

                                        $tblPersonMother = Person::useService()->insertPerson(
                                            $tblSalutation,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Titel'],
                                                $RunY))),
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

                                            if ($occupationMother !== '') {
                                                Custody::useService()->insertMeta(
                                                    $tblPersonMother,
                                                    $occupationMother,
                                                    '',
                                                    ''
                                                );
                                            }
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
                                    $streetName = '';
                                    $streetNumber = '';
                                    $street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $street, $matches)) {
                                        $pos = strpos($street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $streetName = trim(substr($street, 0, $pos));
                                            $streetNumber = trim(substr($street, $pos));
                                        }
                                    }

                                    if ($streetName !== '' && $streetNumber !== '') {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonFather,
                                            $streetName,
                                            $streetNumber,
                                            $fatherCityCode,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Wohnort'],
                                                $RunY))),
                                            '',
                                            ''
                                        );
                                    }
                                }
                                if ($tblPersonMother !== null) {
                                    $streetName = '';
                                    $streetNumber = '';
                                    $street = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $street, $matches)) {
                                        $pos = strpos($street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $streetName = trim(substr($street, 0, $pos));
                                            $streetNumber = trim(substr($street, $pos));
                                        }
                                    }

                                    if ($streetName !== '' && $streetNumber !== '') {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPersonMother,
                                            $streetName,
                                            $streetNumber,
                                            $motherCityCode,
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Wohnort'],
                                                $RunY))),
                                            '',
                                            ''
                                        );
                                    }
                                }

                                for ($i = 1; $i <= 5; $i++) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon' . $i],
                                        $RunY)));
                                    if ($phoneNumber !== '' && $phoneNumber !== '---') {
                                        if ($i == 2 || $i == 5) {
                                            $tblType = Phone::useService()->getTypeById(3);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(4);
                                            }
                                        } else {
                                            $tblType = Phone::useService()->getTypeById(1);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(2);
                                            }
                                        }

                                        $remark = '';
                                        if ($i == 2 || $i == 3) {
                                            if ($tblPersonMother) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonMother,
                                                    $phoneNumber,
                                                    $tblType,
                                                    $remark
                                                );
                                            }
                                        } elseif ($i == 4 || $i == 5) {
                                            if ($tblPersonFather) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonFather,
                                                    $phoneNumber,
                                                    $tblType,
                                                    $remark
                                                );
                                            }
                                        } else {
                                            Phone::useService()->insertPhoneToPerson(
                                                $tblPerson,
                                                $phoneNumber,
                                                $tblType,
                                                $remark
                                            );
                                        }
                                    }
                                }

                                // Notfall
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Zusatzfeld2'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(5);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(6);
                                    }

                                    $phoneNumberList = explode(';', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $pos = strpos($phone, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phone, 0, $pos));
                                            $phone = trim(substr($phone, $pos + 1));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email1'],
                                    $RunY)));
                                if ($mailAddress != '' && $tblPersonMother) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPersonMother,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email2'],
                                    $RunY)));
                                if ($mailAddress != '' && $tblPersonFather) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPersonFather,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                /*
                                 * student
                                 */
                                $sibling = trim($Document->getValue($Document->getCell($Location['Schüler_Geschwister'],
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
                                $tblStudentBilling = null;
                                if ($tblSiblingRank) {
                                    $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                                } else {
                                    $tblStudentBilling = null;
                                }

                                $coachingRequired = (trim($Document->getValue($Document->getCell($Location['Schüler_Integr_Förderschüler'],
                                        $RunY))) == '1');
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

                                // Allergien
                                $disease = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'],
                                    $RunY)));
                                if ($disease) {
                                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                        $disease,
                                        '',
                                        ''
                                    );
                                } else {
                                    $tblStudentMedicalRecord = null;
                                }

                                // Schulpflicht
                                $schoolAttendanceStartDate = trim($Document->getValue($Document->getCell($Location['Schüler_Schulpflicht_beginnt_am'],
                                    $RunY)));
                                if ($schoolAttendanceStartDate !== '') {
                                    $schoolAttendanceStartDate = date('d.m.Y',
                                        \PHPExcel_Shared_Date::ExcelToPHP($schoolAttendanceStartDate));
                                }

                                $tblStudent = Student::useService()->insertStudent($tblPerson, '',
                                    $tblStudentMedicalRecord, null,
                                    $tblStudentBilling, null, null, $tblStudentIntegration, $schoolAttendanceStartDate);
                                if ($tblStudent) {

                                    // Schülertransfer
                                    $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Schüler_Einschulung_am'],
                                        $RunY)));
                                    if ($enrollmentDate !== '') {
                                        $enrollmentDate = date('d.m.Y',
                                            \PHPExcel_Shared_Date::ExcelToPHP($enrollmentDate));

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
                                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Aufnahme_am'],
                                        $RunY)));
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $arriveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    if ($arriveDate !== '') {
                                        $arriveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($arriveDate));
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                        $remark = trim($Document->getValue($Document->getCell($Location['Schüler_allgemeine_Bemerkungen'],
                                            $RunY)));
                                        if ($remark !== '') {
                                            $remark = 'Staatliche Schule: ' . $remark;
                                        }
                                        $nursery = trim($Document->getValue($Document->getCell($Location['Zusatzfeld3'],
                                            $RunY)));
                                        if ($nursery !== '') {
                                            $tblCompany = Company::useService()->insertCompany($nursery);
                                            if ($tblCompany) {
                                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('COMMON'),
                                                    $tblCompany
                                                );
                                                \SPHERE\Application\Corporation\Group\Group::useService()->addGroupCompany(
                                                    \SPHERE\Application\Corporation\Group\Group::useService()->getGroupByMetaTable('NURSERY'),
                                                    $tblCompany
                                                );
                                            }
                                        } else {
                                            $tblCompany = false;
                                        }
                                        if ($arriveSchool) {
                                            $remark = trim($remark . ' ' . 'Kita: ' . $tblCompany->getName());
                                        } else {
                                            $arriveSchool = $tblCompany;
                                        }

                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            $arriveSchool ? $arriveSchool : null,
                                            null,
                                            null,
                                            $arriveDate,
                                            $remark
                                        );
                                    }
                                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Abgang_am'],
                                        $RunY)));
                                    $leaveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_aufnehmende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $leaveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    if ($leaveDate !== '') {
                                        $leaveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($leaveDate));
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
                                    }

                                    $tblCourse = null;
//                                    if (($course = trim($Document->getValue($Document->getCell($Location['Fächer_Bildungsgang'],
//                                        $RunY))))
//                                    ) {
//                                        if ($course == 'HS') {
//                                            $tblCourse = Course::useService()->getCourseById(1); // Hauptschule
//                                        } elseif ($course == 'GY') {
//                                            $tblCourse = Course::useService()->getCourseById(3); // Gymnasium
//                                        } elseif ($course == 'RS' || $course == 'ORS') {
//                                            $tblCourse = Course::useService()->getCourseById(2); // Realschule
//                                        } elseif ($course == '') {
//                                            // do nothing
//                                        } else {
//                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Bildungsgang nicht gefunden.';
//                                        }
//                                    }
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                    Student::useService()->insertStudentTransfer(
                                        $tblStudent,
                                        $tblStudentTransferType,
                                        null,
                                        $tblSchoolType ? $tblSchoolType : null,
                                        $tblCourse ? $tblCourse : null,
                                        null,
                                        ''
                                    );

                                    /*
                                     * Fächer
                                     */
                                    // Religion
                                    $subjectReligion = trim($Document->getValue($Document->getCell($Location['Fächer_Religionsunterricht'],
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
                                                Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubject
                                            );
                                        }
                                    }

                                    // Fremdsprachen
                                    for ($i = 1; $i <= 2; $i++) {
                                        $subjectLanguage = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i],
                                            $RunY)));
                                        $tblSubject = false;
                                        if ($subjectLanguage !== '') {
                                            if ($subjectLanguage === 'EN'
                                                || $subjectLanguage === 'Englisch'
                                            ) {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                            } elseif ($subjectLanguage === 'FR'
                                                || $subjectLanguage === 'Fra'
                                                || $subjectLanguage === 'Französisch'
                                            ) {
                                                $tblSubject = Subject::useService()->getSubjectByAcronym('FR');
                                            }
                                            if ($tblSubject) {
                                                $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                                                $tblFromLevel = false;
                                                $fromLevel = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i . '_von'],
                                                    $RunY)));
                                                if ($fromLevel !== '') {
                                                    $tblFromLevel = Division::useService()->insertLevel(
                                                        $tblSchoolType,
                                                        $fromLevel
                                                    );
                                                }

                                                $tblToLevel = false;
                                                $toLevel = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache' . $i . '_bis'],
                                                    $RunY)));
                                                if ($toLevel !== '') {
                                                    $tblToLevel = Division::useService()->insertLevel(
                                                        $tblSchoolType,
                                                        $toLevel
                                                    );
                                                }

                                                Student::useService()->addStudentSubject(
                                                    $tblStudent,
                                                    Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                    Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                    $tblSubject,
                                                    $tblFromLevel ? $tblFromLevel : null,
                                                    $tblToLevel ? $tblToLevel : null
                                                );
                                            }
                                        }
                                    }

                                    /*
                                     * Förderung
                                     */
                                    $focus = trim($Document->getValue($Document->getCell($Location['Schüler_Förderschwerpunkt'],
                                        $RunY)));
                                    if ($focus !== '') {
                                        if ($focus === 'LE') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Lernen');
                                            if ($tblStudentFocusType) {
                                                Student::useService()->addStudentFocus($tblStudent,
                                                    $tblStudentFocusType);
                                            }
                                        } elseif ($focus === 'HÖ') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Hören');
                                            if ($tblStudentFocusType) {
                                                Student::useService()->addStudentFocus($tblStudent,
                                                    $tblStudentFocusType);
                                            }
                                        }
                                    }

                                    $integration = trim($Document->getValue($Document->getCell($Location['Schüler_Förderbedarf'],
                                        $RunY)));
                                    if ($integration !== '') {
                                        if (strpos($integration, 'Hören') !== false) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Gehörschwierigkeiten');
                                            if ($tblStudentDisorderType) {
                                                Student::useService()->addStudentDisorder($tblStudent,
                                                    $tblStudentDisorderType);
                                            }
                                        }
                                    }

                                    /*
                                     * photo agreement
                                     */
                                    $photo = trim($Document->getValue($Document->getCell($Location['Zusatzfeld4'],
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
                    'Mitglieds_Nr' => null,
                    'Aktiv' => null,
                    'Anrede' => null,
                    'Titel' => null,
                    'Vorname' => null,
                    'Nachname' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Ort' => null,
                    'Geburtsdatum' => null,
                    'Telefon_privat' => null,
                    'Telefon_dienstlich' => null,
                    'Telefax' => null,
                    'Handy_1' => null,
                    'Handy_2' => null,
                    'Email' => null,
                    'Eintritt' => null,
                    'Austritt' => null,
                    'Kündigung' => null,
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

                    $tblGroupClubMember = Group::useService()->getGroupByMetaTable('CLUB');
                    $tblGroupActive = Group::useService()->insertGroup('aktive Vereinsmitglieder');
                    $tblGroupInActive = Group::useService()->insertGroup('ausgetretene Vereinsmitglieder');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {
                            $cityCode = str_pad(
                                trim($Document->getValue($Document->getCell($Location['Plz'], $RunY))),
                                5,
                                "0",
                                STR_PAD_LEFT
                            );
                            if (count($cityCode) > 5) {
                                $cityCode = substr($cityCode, 0, 5);
                            }
                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            $entryDate = trim($Document->getValue($Document->getCell($Location['Eintritt'],
                                $RunY)));
                            if ($entryDate !== '') {
                                $entryDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($entryDate));;
                            }
                            $exitDate = trim($Document->getValue($Document->getCell($Location['Austritt'],
                                $RunY)));
                            if ($exitDate !== '') {
                                $exitDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($exitDate));;
                            }
                            $remark = trim($Document->getValue($Document->getCell($Location['Aktiv'],
                                $RunY)));
                            $quitDate = trim($Document->getValue($Document->getCell($Location['Kündigung'],
                                $RunY)));
                            if ($quitDate !== '') {
                                $remark .= ' Kündigung: ' . date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($quitDate));
                            }

                            $isActive = (trim($Document->getValue($Document->getCell($Location['Aktiv'],
                                    $RunY))) == 'aktiv');

                            $clubNumber = trim($Document->getValue($Document->getCell($Location['Mitglieds_Nr'],
                                $RunY)));

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblGroupClubMember, $tblPersonExits);
                                $countClubMemberExists++;

                                if ($isActive) {
                                    Club::useService()->insertMeta($tblPersonExits, $clubNumber, $entryDate);
                                    Group::useService()->addGroupPerson($tblGroupActive, $tblPersonExits);
                                } else {
                                    Club::useService()->insertMeta($tblPersonExits, $clubNumber, $entryDate, $exitDate,
                                        $remark);
                                    Group::useService()->addGroupPerson($tblGroupInActive, $tblPersonExits);
                                }

                            } else {

                                $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'],
                                    $RunY)));
                                if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                    $tblSalutation = Person::useService()->getSalutationById(1);
                                } elseif ($salutation == 'Frau') {
                                    $tblSalutation = Person::useService()->getSalutationById(2);
                                } else {
                                    $tblSalutation = false;
                                }

                                $tblPerson = Person::useService()->insertPerson(
                                    $tblSalutation ? $tblSalutation : null,
                                    trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                    $firstName,
                                    '',
                                    $lastName,
                                    array(
                                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                        1 => $tblGroupClubMember
                                    )
                                );

                                if ($tblPerson !== false) {
                                    if ($isActive) {
                                        Club::useService()->insertMeta($tblPerson, $clubNumber, $entryDate);
                                        Group::useService()->addGroupPerson($tblGroupActive, $tblPerson);
                                    } else {
                                        Club::useService()->insertMeta($tblPerson, $clubNumber, $entryDate, $exitDate,
                                            $remark);
                                        Group::useService()->addGroupPerson($tblGroupInActive, $tblPerson);
                                    }

                                    $countClubMember++;

                                    $birthday = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($birthday !== '') {

                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
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
                                    }

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

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon_privat'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }

                                        $pos = strpos($phoneNumber, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phoneNumber, $pos + 1));
                                            $phoneNumber = trim(substr($phoneNumber, 0, $pos));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon_dienstlich'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(3);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(4);
                                        }

                                        $pos = strpos($phoneNumber, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phoneNumber, $pos + 1));
                                            $phoneNumber = trim(substr($phoneNumber, 0, $pos));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefax'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(7);

                                        $pos = strpos($phoneNumber, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phoneNumber, $pos + 1));
                                            $phoneNumber = trim(substr($phoneNumber, 0, $pos));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Handy_1'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }

                                        $pos = strpos($phoneNumber, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phoneNumber, $pos + 1));
                                            $phoneNumber = trim(substr($phoneNumber, 0, $pos));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Handy_2'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }

                                        $pos = strpos($phoneNumber, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phoneNumber, $pos + 1));
                                            $phoneNumber = trim(substr($phoneNumber, 0, $pos));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['Email'],
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
                    'Lehrerkürzel' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Anrede' => null,
                    'Titel' => null,
                    'Geschlecht' => null,
                    'Straße' => null,
                    'Plz' => null,
                    'Wohnort' => null,
                    'Ortsteil' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Geburtsname' => null,
                    'Staatsangehörigkeit' => null,
                    'Konfession' => null,
                    'Telefon1' => null,
                    'Telefon2' => null,
                    'EMail' => null,
                    'Zusatzfeld1' => null,
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
                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {

                            $cityCode = str_pad(
                                trim($Document->getValue($Document->getCell($Location['Plz'], $RunY))),
                                5,
                                "0",
                                STR_PAD_LEFT
                            );

                            $tblPersonExits = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );

                            $acronym = trim($Document->getValue($Document->getCell($Location['Lehrerkürzel'], $RunY)));
                            $remark = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'], $RunY)));

                            if ($tblPersonExits) {

                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';

                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExits);
                                Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExits);

                                if ($acronym !== '') {
                                    Teacher::useService()->insertTeacher($tblPersonExits, $acronym);
                                }

                                $countStaffExists++;

                            } else {

                                $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'],
                                    $RunY)));
                                if ($salutation == 'Herr' || $salutation == 'Herrn') {
                                    $tblSalutation = Person::useService()->getSalutationById(1);
                                } elseif ($salutation == 'Frau') {
                                    $tblSalutation = Person::useService()->getSalutationById(2);
                                } else {
                                    $tblSalutation = false;
                                }

                                $tblPerson = Person::useService()->insertPerson(
                                    $tblSalutation ? $tblSalutation : null,
                                    trim($Document->getValue($Document->getCell($Location['Titel'], $RunY))),
                                    $firstName,
                                    '',
                                    $lastName,
                                    array(
                                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                        1 => $tblStaffGroup,
                                        2 => $tblTeacherGroup
                                    ),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
                                );

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    if ($acronym !== '') {
                                        Teacher::useService()->insertTeacher($tblPerson, $acronym);
                                    }

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
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
                                        trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                            $RunY))),
                                        $gender,
                                        trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'],
                                            $RunY))),
                                        trim($Document->getValue($Document->getCell($Location['Konfession'],
                                            $RunY))),
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        $remark
                                    );

                                    // Address
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
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Wohnort'],
                                        $RunY)));
                                    $cityDistrict = trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                        $RunY)));

                                    if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict,
                                            ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Adresse der Person wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon1'],
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

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon2'],
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

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['EMail'],
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
    public function createFormerStudentsFromFile(
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
                    'Schüler_Name' => null,
                    'Schüler_Vorname' => null,
                    'Schüler_Geschlecht' => null,
                    'Schüler_Staatsangehörigkeit' => null,
                    'Schüler_Straße' => null,
                    'Schüler_Plz' => null,
                    'Schüler_Wohnort' => null,
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Konfession' => null,
                    'Schüler_Einschulung_am' => null,
                    'Schüler_Aufnahme_am' => null,
                    'Schüler_abgebende_Schule_ID' => null,
                    'Schüler_aufnehmende_Schule_ID' => null,
                    'Schüler_Abgang_am' => null,
                    'Schüler_Schulpflicht_beginnt_am' => null,
                    'Schüler_allgemeine_Bemerkungen' => null,
                    'Schüler_letzte_Schulart' => null,
                    'Kommunikation_Telefon1' => null,
                    'Kommunikation_Telefon2' => null,
                    'Kommunikation_Telefon3' => null,
                    'Kommunikation_Telefon4' => null,
                    'Kommunikation_Telefon5' => null,
                    'Kommunikation_Email' => null,
                    'Kommunikation_Email1' => null,
                    'Kommunikation_Email2' => null,
                    'Zusatzfeld1' => null,
                    'Zusatzfeld2' => null,
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

                    $tblFormerStudentGroup = Group::useService()->insertGroup('Ehemalige Schüler');

                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Schüler_Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Schüler_Name'], $RunY)));
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
                                    1 => $tblFormerStudentGroup
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                $gender = trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY)));
                                if ($gender == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($gender == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $birthday = trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsdatum'],
                                    $RunY)));
                                if ($birthday !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
                                }

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // Address
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $studentCityName = trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY)));
                                $studentCityDistrict = '';
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }

                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $studentCityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $studentCityName,
                                        $studentCityDistrict, ''
                                    );
                                }

                                for ($i = 1; $i <= 5; $i++) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon' . $i],
                                        $RunY)));
                                    if ($phoneNumber !== '' && $phoneNumber !== '---') {
                                        if ($i == 2 || $i == 5) {
                                            $tblType = Phone::useService()->getTypeById(3);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(4);
                                            }
                                        } else {
                                            $tblType = Phone::useService()->getTypeById(1);
                                            if (0 === strpos($phoneNumber, '01')) {
                                                $tblType = Phone::useService()->getTypeById(2);
                                            }
                                        }

                                        $remark = '';

                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                // Notfall
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Zusatzfeld2'],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $tblType = Phone::useService()->getTypeById(5);
                                    if (0 === strpos($phoneNumber, '01')) {
                                        $tblType = Phone::useService()->getTypeById(6);
                                    }

                                    $phoneNumberList = explode(';', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $pos = strpos($phone, " ");
                                        if ($pos !== false) {
                                            $remark = trim(substr($phone, 0, $pos));
                                            $phone = trim(substr($phone, $pos + 1));
                                        } else {
                                            $remark = '';
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            $remark
                                        );
                                    }
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email1'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email2'],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mailAddress,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }

                                $tblStudentBilling = null;

                                // Allergien
                                $disease = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'],
                                    $RunY)));
                                if ($disease) {
                                    $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                        $disease,
                                        '',
                                        ''
                                    );
                                } else {
                                    $tblStudentMedicalRecord = null;
                                }

                                // Schulpflicht
                                $schoolAttendanceStartDate = trim($Document->getValue($Document->getCell($Location['Schüler_Schulpflicht_beginnt_am'],
                                    $RunY)));
                                if ($schoolAttendanceStartDate !== '') {
                                    $schoolAttendanceStartDate = date('d.m.Y',
                                        \PHPExcel_Shared_Date::ExcelToPHP($schoolAttendanceStartDate));
                                }

                                $tblStudent = Student::useService()->insertStudent($tblPerson, '',
                                    $tblStudentMedicalRecord, null,
                                    $tblStudentBilling, null, null, null, $schoolAttendanceStartDate);
                                if ($tblStudent) {

                                    // Schülertransfer
                                    $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Schüler_Einschulung_am'],
                                        $RunY)));
                                    if ($enrollmentDate !== '') {
                                        $enrollmentDate = date('d.m.Y',
                                            \PHPExcel_Shared_Date::ExcelToPHP($enrollmentDate));

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
                                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Aufnahme_am'],
                                        $RunY)));
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $arriveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    if ($arriveDate !== '') {
                                        $arriveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($arriveDate));
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                        $remark = trim($Document->getValue($Document->getCell($Location['Schüler_allgemeine_Bemerkungen'],
                                            $RunY)));
                                        if ($remark !== '') {
                                            $remark = 'Staatliche Schule: ' . $remark;
                                        }

                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            $arriveSchool ? $arriveSchool : null,
                                            null,
                                            null,
                                            $arriveDate,
                                            $remark
                                        );
                                    }
                                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Abgang_am'],
                                        $RunY)));
                                    $leaveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_aufnehmende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $leaveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    if ($leaveDate !== '') {
                                        $leaveDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($leaveDate));
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