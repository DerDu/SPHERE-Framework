<?php

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
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

                                if ($streetName && $streetNumber && $zipCode && $city){
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
                        new Success('Es wurden ' . $countCompany . ' Firmen erfolgreich angelegt.');
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
                    'Schüler_Klasse' => null,
                    'Schüler_Geschlecht' => null,
                    'Schüler_Staatsangehörigkeit' => null,
                    'Schüler_Straße' => null,
                    'Schüler_Plz' => null,
                    'Schüler_Wohnort' => null,
                    'Schüler_Ortsteil' => null,
                    'Schüler_Landkreis' => null,
                    'Schüler_Bundesland' => null,
                    'Schüler_Geburtsdatum' => null,
                    'Schüler_Geburtsort' => null,
                    'Schüler_Geschwister' => null,
                    'Schüler_Integr_Förderschüler' => null,
                    'Schüler_Konfession' => null,
                    'Schüler_Einschulung_am' => null,
                    'Schüler_Aufnahme_am' => null,
                    'Schüler_abgebende_Schule_ID' => null,
                    'Schüler_Abgang_am' => null,
                    'Schüler_Wiederholungen_Hinweise' => null,
                    'Schüler_Krankenversicherung_bei' => null,
                    'Schüler_Krankenkasse' => null,
                    'Schüler_Förderbedarf' => null,
                    'Schüler_Förderschwerpunkt' => null,
                    'Kommunikation_Telefon1' => null,
                    'Kommunikation_Telefon2' => null,
                    'Kommunikation_Telefon3' => null,
                    'Kommunikation_Telefon4' => null,
                    'Kommunikation_Telefon5' => null,
                    'Kommunikation_Email' => null,
                    'Sorgeberechtigter1_Titel' => null,
                    'Sorgeberechtigter1_Name' => null,
                    'Sorgeberechtigter1_Vorname' => null,
                    'Sorgeberechtigter1_Geschlecht' => null,
                    'Sorgeberechtigter1_Straße' => null,
                    'Sorgeberechtigter1_Plz' => null,
                    'Sorgeberechtigter1_Wohnort' => null,
                    'Sorgeberechtigter1_Ortsteil' => null,
                    'Sorgeberechtigter2_Titel' => null,
                    'Sorgeberechtigter2_Name' => null,
                    'Sorgeberechtigter2_Vorname' => null,
                    'Sorgeberechtigter2_Geschlecht' => null,
                    'Sorgeberechtigter2_Straße' => null,
                    'Sorgeberechtigter2_Plz' => null,
                    'Sorgeberechtigter2_Wohnort' => null,
                    'Sorgeberechtigter2_Ortsteil' => null,
                    'Fächer_Bildungsgang' => null,
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
                    'Zusatzfeld5' => null,
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
                                )
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte nicht angelegt werden.';
                            } else {
                                $countStudent++;

                                // Stammgruppe
                                $mainGroup = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'],
                                    $RunY)));
                                $mainGroup = $mainGroup !== '' ? Group::useService()->insertGroup($mainGroup) : false;
                                if ($mainGroup) {
                                    Group::useService()->addGroupPerson($mainGroup, $tblPerson);
                                }

                                // Mentorengruppe
                                $mentorGroup = trim($Document->getValue($Document->getCell($Location['Zusatzfeld3'],
                                    $RunY)));
                                $mentorGroup = $mentorGroup !== '' ? Group::useService()->insertGroup('Mentorengruppe ' . $mentorGroup) : false;
                                if ($mentorGroup) {
                                    Group::useService()->addGroupPerson($mentorGroup, $tblPerson);
                                }

                                $gender = trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
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
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsdatum'],
                                        $RunY))),
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

                                $schoolType = trim($Document->getValue($Document->getCell($Location['Zusatzfeld2'],
                                    $RunY)));
                                if ($schoolType == 'Gym') {
                                    $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium
                                } elseif ($schoolType == 'OS') {
                                    $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                                } elseif ($schoolType === '') {
                                    $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                } else {
                                    $tblSchoolType = false;
                                }

                                // division
                                $tblDivision = false;
                                $year = 15;
                                $division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                    $RunY)));
                                if ($division !== '') {
                                    if ($division == '0') {
                                        $year = 16;
                                        $division = '1';
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
                                $studentCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Schüler_Plz'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $studentCityName = trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY)));
                                $studentCityDistrict = trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
                                    $RunY)));
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
                                $county = trim($Document->getValue($Document->getCell($Location['Schüler_Landkreis'],
                                    $RunY)));
                                if (trim($Document->getValue($Document->getCell($Location['Schüler_Bundesland'],
                                        $RunY))) == 'SN'
                                ) {
                                    $tblState = Address::useService()->getStateByName('Sachsen');
                                } else {
                                    $tblState = false;
                                }
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $studentCityCode && $studentCityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $studentCityCode, $studentCityName,
                                        $studentCityDistrict, '', $county, '', $tblState ? $tblState : null
                                    );
                                }

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);
                                // Father
                                $tblPersonFather = null;
                                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Name'],
                                    $RunY)));
                                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Vorname'],
                                    $RunY)));

                                $fatherCityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Plz'],
                                        $RunY))),
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
                                    trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Plz'],
                                        $RunY))),
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
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter2_Ortsteil'],
                                                $RunY))),
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
                                            trim($Document->getValue($Document->getCell($Location['Sorgeberechtigter1_Ortsteil'],
                                                $RunY))),
                                            ''
                                        );
                                    }
                                }

                                for ($i = 1; $i <= 5; $i++) {
                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon' . $i],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }

                                        if (($pos = stripos($phoneNumber, ' '))) {
                                            $remark = substr($phoneNumber, $pos + 1);
                                            $phoneNumber = substr($phoneNumber, 0, $pos);
                                        } else {
                                            $remark = '';
                                        }

                                        if ($i == 3) {
                                            if ($tblPersonMother) {
                                                Phone::useService()->insertPhoneToPerson(
                                                    $tblPersonMother,
                                                    $phoneNumber,
                                                    $tblType,
                                                    $remark
                                                );
                                            }
                                        } elseif ($i == 4) {
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

                                // Versicherungsstatus passt nicht zu unserem Status
                                $insurance = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenkasse'],
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
                                    $enrollmentDate = trim($Document->getValue($Document->getCell($Location['Schüler_Einschulung_am'],
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
                                    $arriveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Aufnahme_am'],
                                        $RunY)));
                                    $arriveSchool = null;
                                    $company = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                        $RunY)));
                                    if ($company != '') {
                                        $arriveSchool = Company::useService()->getCompanyByDescription($company);
                                    }
                                    if ($arriveDate !== '' && date_create($arriveDate) !== false) {

                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            $arriveSchool ? $arriveSchool : null,
                                            null,
                                            null,
                                            $arriveDate,
                                            ''
                                        );
                                    }
                                    $leaveDate = trim($Document->getValue($Document->getCell($Location['Schüler_Abgang_am'],
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

                                    $tblCourse = null;
                                    if (($course = trim($Document->getValue($Document->getCell($Location['Fächer_Bildungsgang'],
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
                                        if ($focus === 'GE') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Geistige Entwicklung');
                                            Student::useService()->addStudentFocus($tblStudent,
                                                $tblStudentFocusType);
                                        } elseif ($focus === 'LE'
                                            || $focus === 'Lernen'
                                        ) {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Lernen');
                                            Student::useService()->addStudentFocus($tblStudent,
                                                $tblStudentFocusType);
                                        } elseif ($focus === 'SPR') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Sprache');
                                            Student::useService()->addStudentFocus($tblStudent, $tblStudentFocusType);
                                        } elseif ($focus === 'emot./soz. Entwicklung') {
                                            $tblStudentFocusType = Student::useService()->getStudentFocusTypeByName('Sozial-emotionale Entwicklung');
                                            Student::useService()->addStudentFocus($tblStudent, $tblStudentFocusType);
                                        }
                                    }

                                    $integration = trim($Document->getValue($Document->getCell($Location['Schüler_Förderbedarf'],
                                        $RunY)));
                                    if ($integration !== '') {
                                        if (strpos($integration, 'Dyskalkulie') !== false) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Dyskalkulie');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                        if (strpos($integration, 'ADHS') !== false) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('ADS / ADHS');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                        if (strpos($integration, 'Autismus') !== false) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Autismus');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                        if (strpos($integration, 'Konzentr.-störung') !== false
                                            || strpos($integration, 'Konzentrationsstörung') !== false
                                        ) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Konzentrationsstörung');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                        if (strpos($integration, 'KB') !== false) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('Körperliche Beeinträchtigung');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                        if (strpos($integration, 'LRS') !== false
                                            || strpos($integration, 'Rechtschreibschwäche') !== false
                                        ) {
                                            $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeByName('LRS');
                                            Student::useService()->addStudentDisorder($tblStudent,
                                                $tblStudentDisorderType);
                                        }
                                    }

                                    /*
                                     * photo agreement
                                     */
                                    $photo = trim($Document->getValue($Document->getCell($Location['Zusatzfeld5'],
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
