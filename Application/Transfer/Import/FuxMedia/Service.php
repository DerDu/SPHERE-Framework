<?php


namespace SPHERE\Application\Transfer\Import\FuxMedia;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\FuxMedia\Service\Person;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\Transfer\Import\Service as ImportService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia
 */
class Service
{

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param string              $Redirect
     *
     * @return IFormInterface|Redirect|string
     */
    public function getTypeAndYear(IFormInterface $Stage = null, $Select = null, $Redirect = '')
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $Error = false;
        if (!(Type::useService()->getTypeById($Select['Type'])) && !isset($Select['UseTypeFromImport'])) {
            $Error = true;
            $Stage .= new Warning('Schulart nicht gefunden');
        }
        if (!(Term::useService()->getYearById($Select['Year']))) {
            $Error = true;
            $Stage .= new Warning('Schuljahr nicht gefunden');
        }
        if ($Error) {
            return $Stage;
        }

        return new Redirect($Redirect, 0, array(
            'TypeId' => $Select['Type'],
            'YearId' => $Select['Year'],
            'UseTypeFromImport' => isset($Select['UseTypeFromImport'])
        ));
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $TypeId
     * @param null $YearId
     * @param null $UseTypeFromImport
     *
     * @return IFormInterface|Danger|string
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $TypeId = null,
        $YearId = null,
        $UseTypeFromImport = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File || $TypeId === null || $YearId === null) {
            return $Form;
        }

        if (null !== $File) {
            ini_set('memory_limit', '2G');

            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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

                $error = array();
                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Schüler_Schülernummer'               => null,
                    'Schüler_Name'                        => null,
                    'Schüler_Vorname'                     => null,
                    'Schüler_Klasse'                      => null,
                    'Schüler_Klassenstufe'                => null,
                    'Schüler_Geschlecht'                  => null,
                    'Schüler_Staatsangehörigkeit'         => null,
                    'Schüler_Straße'                      => null,
                    'Schüler_Plz'                         => null,
                    'Schüler_Wohnort'                     => null,
                    'Schüler_Ortsteil'                    => null,
                    'Schüler_Landkreis'                   => null,
                    'Schüler_Geburtsdatum'                => null,
                    'Schüler_Geburtsort'                  => null,
                    'Schüler_Konfession'                  => null,
                    'Schüler_Einschulung_am'              => null,
                    'Schüler_Aufnahme_am'                 => null,
                    'Schüler_Abgang_am'                   => null,
                    'Schüler_abgebende_Schule_ID'         => null,
                    'Schüler_aufnehmende_Schule_ID'       => null,
                    'Schüler_Schließfach_Schlüsselnummer' => null,
                    'Schüler_Schließfachnummer'           => null,
                    'Schüler_Krankenkasse'                => null,
                    'Sorgeberechtigter1_Name'             => null,
                    'Sorgeberechtigter1_Vorname'          => null,
                    'Sorgeberechtigter1_Straße'           => null,
                    'Sorgeberechtigter1_Plz'              => null,
                    'Sorgeberechtigter1_Wohnort'          => null,
                    'Sorgeberechtigter1_Ortsteil'         => null,
                    'Sorgeberechtigter2_Name'             => null,
                    'Sorgeberechtigter2_Vorname'          => null,
                    'Sorgeberechtigter2_Straße'           => null,
                    'Sorgeberechtigter2_Plz'              => null,
                    'Sorgeberechtigter2_Wohnort'          => null,
                    'Sorgeberechtigter2_Ortsteil'         => null,
                    'Kommunikation_Telefon1'              => null,
                    'Kommunikation_Telefon2'              => null,
                    'Kommunikation_Telefon3'              => null,
                    'Kommunikation_Telefon4'              => null,
                    'Kommunikation_Telefon5'              => null,
                    'Kommunikation_Telefon6'              => null,
                    'Kommunikation_Telefon7'              => null,
                    'Kommunikation_Telefon8'              => null,
                    'Kommunikation_Telefon9'              => null,
                    'Kommunikation_Telefon10'             => null,
                    'Kommunikation_Telefon11'             => null,
                    'Kommunikation_Telefon12'             => null,
                    'Kommunikation_Fax'                   => null,
                    'Kommunikation_Email'                 => null,
                    'Kommunikation_Email1'                => null,
                    'Kommunikation_Email2'                => null,
                    'Kommunikation_Email3'                => null,
                    'Kommunikation_Email4'                => null,
                    'Beförderung_Fahrschüler'             => null,
                    'Beförderung_Fahrtroute'              => null,
                    'Beförderung_Einsteigestelle'         => null,
                    'Beförderung_Verkehrsmittel'          => null,
                    'Fächer_Religionsunterricht'          => null,
                    'Fächer_Fremdsprache1'                => null,
                    'Fächer_Fremdsprache2'                => null,
                    'Fächer_Fremdsprache3'                => null,
                    'Fächer_Fremdsprache4'                => null,
                    'Fächer_Arbeitsgemeinschaft1'         => null,
                    'Fächer_Arbeitsgemeinschaft2'         => null,
                    'Fächer_Arbeitsgemeinschaft3'         => null,
                    'Fächer_Arbeitsgemeinschaft4'         => null,
                    'Zusatzfeld1'                         => null,
//                    'Zusatzfeld10'                        => null,

                    'Schüler_Fotoerlaubnis'               => null,
                    'Schüler_Geschwister'                 => null,
                    'Schüler_letzte_Schulart'             => null,
                    'Schüler_Krankheiten'                 => null,
                    'Schüler_Medikamente'                 => null,
                    'Schüler_Behinderung_Hinweise'        => null,
                    'Schüler_Krankenversicherung_bei'     => null,
                    'Schüler_allgemeine_Bemerkungen'      => null,
                    'Beförderung_Hinweise'                => null,
                    'Schüler_Schulabschluss'              => null,
                    'Fächer_Fremdsprache1_von'            => null,
                    'Fächer_Fremdsprache1_bis'            => null,
                    'Fächer_Fremdsprache2_von'            => null,
                    'Fächer_Fremdsprache2_bis'            => null,
                    'Fächer_Fremdsprache3_von'            => null,
                    'Fächer_Fremdsprache3_bis'            => null,
                    'Fächer_Fremdsprache4_von'            => null,
                    'Fächer_Fremdsprache4_bis'            => null,
                    'Sorgeberechtigter_Status'            => null,
                    'Sorgeberechtigter1_Titel'            => null,
                    'Sorgeberechtigter1_Geschlecht'       => null,
                    'Sorgeberechtigter1_GO'               => null,
                    'Sorgeberechtigter1_GD'               => null,
                    'Sorgeberechtigter2_Status'           => null,
                    'Sorgeberechtigter2_Titel'            => null,
                    'Sorgeberechtigter2_Geschlecht'       => null,
                    'Sorgeberechtigter2_GO'               => null,
                    'Sorgeberechtigter2_GD'               => null,
                    'Fächer_Profilfach'                   => null,
                    'Fächer_Neigungskurs'                 => null,

                    'Fächer_Bildungsgang'                 => null,
                    'Fächer_letzter_Bildungsgang'         => null,
                    'Fächer_Sportbefreiung'               => null,
                    'Schüler_Schulart'                    => null,
                    'Schüler_Vorbildung'                  => null,
                    'Schüler_Abschlussjahr_ABS'           => null,
                    'Schüler_Bafoeg'                      => null,
                    'Schüler_Bafoeg_Beantragungsjahr'     => null,
                    'Schüler_Bafoeg_Amt'                  => null,
                    'Schüler_Fachrichtung'                => null,
                );

                $OptionalLocation = array(
                    'Sorgeberechtigter1_Beruf'            => null,
                    'Sorgeberechtigter2_Beruf'            => null,
                    'Sorgeberechtigter3_Name'             => null,
                    'Sorgeberechtigter3_Vorname'          => null,
                    'Sorgeberechtigter3_Straße'           => null,
                    'Sorgeberechtigter3_Plz'              => null,
                    'Sorgeberechtigter3_Wohnort'          => null,
                    'Sorgeberechtigter3_Ortsteil'         => null,
                    'Sorgeberechtigter3_Status'           => null,
                    'Sorgeberechtigter3_Titel'            => null,
                    'Sorgeberechtigter3_Geschlecht'       => null,
                    'Sorgeberechtigter3_GO'               => null,
                    'Sorgeberechtigter3_GD'               => null,
                    'Sorgeberechtigter3_Beruf'            => null,
                    'Sorgeberechtigter4_Name'             => null,
                    'Sorgeberechtigter4_Vorname'          => null,
                    'Sorgeberechtigter4_Straße'           => null,
                    'Sorgeberechtigter4_Plz'              => null,
                    'Sorgeberechtigter4_Wohnort'          => null,
                    'Sorgeberechtigter4_Ortsteil'         => null,
                    'Sorgeberechtigter4_Status'           => null,
                    'Sorgeberechtigter4_Titel'            => null,
                    'Sorgeberechtigter4_Geschlecht'       => null,
                    'Sorgeberechtigter4_GO'               => null,
                    'Sorgeberechtigter4_GD'               => null,
                    'Sorgeberechtigter4_Beruf'            => null,
                    'Zusatzfeld10'                        => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }

                    if (array_key_exists($Value, $OptionalLocation)) {
                        $OptionalLocation[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $Location = array_merge($Location, $OptionalLocation);

                    $importService = new ImportService($Location, $Document);

                    $countStudent = 0;
                    $countCustody = 0;
                    $countCustodyExists = 0;

                    $tblTypeParameter = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    $tblStudentAgreementCategoryPhoto = Student::useService()->getStudentAgreementCategoryById(1);
                    $tblStudentAgreementCategoryName = Student::useService()->getStudentAgreementCategoryById(2);

                    $tblCommonGenderMale = Common::useService()->getCommonGenderByName('Männlich');
                    $tblCommonGenderFemale = Common::useService()->getCommonGenderByName('Weiblich');

                    if (($tblConsumer = Consumer::useService()->getConsumerBySession())) {
                        $consumerAcronym = $tblConsumer->getAcronym();
                    } else {
                        $consumerAcronym = '';
                    }

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);

                        $isHofa = false;
                        if ($UseTypeFromImport) {
                            $type = trim($Document->getValue($Document->getCell($Location['Schüler_Schulart'], $RunY)));
                            switch ($type) {
                                case 'BGY+HOFA': $isHofa = true;
                                case 'BGY': $tblType = Type::useService()->getTypeByShortName('BGy'); break;
                                case 'FOS2': $type = 'FOS';
                                default:
                                    $tblType = Type::useService()->getTypeByShortName($type); break;
                            }

                            if (!$tblType) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Schulart:' . $type . ' konnte nicht gefunden werden.';
                            }
                        } else {
                            $tblType = $tblTypeParameter;
                        }

                        // Student
                        $tblPerson = $this->usePeoplePerson()->insertPerson(
                            $this->usePeoplePerson()->getSalutationById(3),   //Schüler
                            '',
                            trim($Document->getValue($Document->getCell($Location['Schüler_Vorname'], $RunY))),
                            '',
                            trim($Document->getValue($Document->getCell($Location['Schüler_Name'], $RunY))),
                            array(
                                0 => Group::useService()->getGroupById(1),           //Personendaten
                                1 => Group::useService()->getGroupById(3)            //Schüler
                            ),
                            '',
                            ($tblType ? $tblType->getShortName() : 'XX') . '_Zeile_' . ($RunY + 1)
                        );

                        if ($tblPerson !== false) {
                            $countStudent++;

                            if ($isHofa && ($tblGroupHofa = Group::useService()->insertGroup('Hotelmanagementschule'))) {
                                Group::useService()->addGroupPerson($tblGroupHofa, $tblPerson);
                            }

                            // Student Common
                            Common::useService()->insertMeta(
                                $tblPerson,
                                $importService->formatDateString('Schüler_Geburtsdatum', $RunY, $error),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geburtsort'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Geschlecht'],
                                    $RunY))) == 'm' ? $tblCommonGenderMale : $tblCommonGenderFemale,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Staatsangehörigkeit'],
                                    $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Konfession'], $RunY))),
                                0,
                                '',
                                trim($Document->getValue($Document->getCell($Location['Schüler_allgemeine_Bemerkungen'], $RunY)))
                            );

                            // Student Address
                            if (trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                    $RunY))) != ''
                            ) {
                                $Street = trim($Document->getValue($Document->getCell($Location['Schüler_Straße'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));
                                        $cityCodeStudent = $importService->formatZipCode('Schüler_Plz', $RunY);

                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson,
                                            $StreetName,
                                            $StreetNumber,
                                            $cityCodeStudent,
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Wohnort'],
                                                $RunY))),
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Ortsteil'],
                                                $RunY))),
                                            '',
                                            trim($Document->getValue($Document->getCell($Location['Schüler_Landkreis'],
                                                $RunY)))
                                        );

                                    }
                                }
                            }

                            // Division
                            if (( $Level = trim($Document->getValue($Document->getCell($Location['Schüler_Klassenstufe'],
                                    $RunY))) ) != ''
                            ) {
                                if ($tblType) {
                                    $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                                    if ($tblLevel) {
                                        $Division = trim($Document->getValue($Document->getCell($Location['Schüler_Klasse'],
                                            $RunY)));
                                        if ($Division != '') {
                                            if (($pos = strpos($Division, $Level)) !== false) {
                                                if (strlen($Division) > (($start = $pos + strlen($Level)))) {
                                                    $Division = substr($Division, $start);
                                                } else {
                                                    $Division = '';
                                                }
                                            }
                                            $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                                $Division);
                                            if ($tblDivision) {
                                                Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                                            }
                                        }
                                    }
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' da kein Schulart gefunden wurde, kann die Klassestufe: '
                                        . $Level . ' nicht angelegt werden. Der Schüler wurde keiner Klasse zugewiesen';
                                }
                            }

                            // Schülerakte
                            $studentNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schülernummer'],
                                $RunY)));
                            $tblStudentLocker = null;
                            $LockerNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schließfachnummer'],
                                $RunY)));
                            if ($consumerAcronym == 'EVOSG' || $consumerAcronym == 'EVOS') {
                                $LockerLocation = trim($Document->getValue($Document->getCell($Location['Zusatzfeld1'],
                                    $RunY)));
                            } else {
                                $LockerLocation = '';
                            }
                            $KeyNumber = trim($Document->getValue($Document->getCell($Location['Schüler_Schließfach_Schlüsselnummer'],
                                $RunY)));
                            if ($consumerAcronym == 'EVOSG' || $consumerAcronym == 'EVOS') {
                                $CombinationLockNumber = $this->getValue('Zusatzfeld10', $Location, $Document, $RunY);
                            } else {
                                $CombinationLockNumber = '';
                            }
                            if ($LockerNumber !== '' || $KeyNumber !== '') {
                                $tblStudentLocker = Student::useService()->insertStudentLocker(
                                    $LockerNumber,
                                    $LockerLocation,
                                    $KeyNumber,
                                    $CombinationLockNumber
                                );
                            }

                            $disease = '';
                            $disease1 = trim($Document->getValue($Document->getCell($Location['Schüler_Krankheiten'],
                                $RunY)));
                            if ($disease1 != '') {
                                $disease = 'Krankheiten: ' . $disease1;
                            }
                            $medication = trim($Document->getValue($Document->getCell($Location['Schüler_Medikamente'],
                                $RunY)));
                            $disease2 = trim($Document->getValue($Document->getCell($Location['Schüler_Behinderung_Hinweise'],
                                $RunY)));
                            if ($disease2 != '') {
                                $disease .= ($disease == '' ? '' : " \n") . 'Behinderung Hinweise: ' . $disease2;
                            }

                            $insurance = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenkasse'],
                                $RunY)));

                            $insuranceState = trim($Document->getValue($Document->getCell($Location['Schüler_Krankenversicherung_bei'],
                                $RunY)));
                            if ($insuranceState != '') {
                                if ($insuranceState == '1') {
                                    // Familie Mutter
                                    $insuranceState = 5;
                                } elseif ($insuranceState == '2') {
                                    // Familie Vater
                                    $insuranceState = 4;
                                } else {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Krankenversicherung_bei:' . $insuranceState
                                        . ' konnte nicht angelegt werden.';
                                }
                            }

                            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord(
                                $disease,
                                $medication,
                                $insurance,
                                $insuranceState
                            );

                            $tblStudentTransport = null;
                            $route = trim($Document->getValue($Document->getCell($Location['Beförderung_Fahrtroute'],
                                $RunY)));
                            if ($route == '') {
                                $route = trim($Document->getValue($Document->getCell($Location['Beförderung_Verkehrsmittel'],
                                    $RunY)));
                            }
                            $stationEntrance = trim($Document->getValue($Document->getCell($Location['Beförderung_Einsteigestelle'],
                                $RunY)));
                            $transportRemark = trim($Document->getValue($Document->getCell($Location['Beförderung_Hinweise'],
                                $RunY)));
                            $isDriverStudent = trim($Document->getValue($Document->getCell($Location['Beförderung_Fahrschüler'],
                                $RunY)));
                            if ($route !== '' || $stationEntrance !== '' || $transportRemark != '') {
                                $tblStudentTransport = Student::useService()->insertStudentTransport(
                                    $route,
                                    $stationEntrance,
                                    '',
                                    $transportRemark ? 'Beförderung Hinweise: ' . $transportRemark : '',
                                    $isDriverStudent == '1'
                                );
                            }

                            $sibling = trim($Document->getValue($Document->getCell($Location['Schüler_Geschwister'],
                                $RunY)));
                            $tblSiblingRank = false;
                            if ($sibling !== '' && $sibling != '0') {
                                if (!($tblSiblingRank = Relationship::useService()->getSiblingRankById(intval($sibling)))) {
                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Geschwister konnte nicht angelegt werden.';
                                }
                            }

                            if ($tblSiblingRank) {
                                $tblStudentBilling = Student::useService()->insertStudentBilling($tblSiblingRank);
                            } else {
                                $tblStudentBilling = null;
                            }

                            $preDiploma = trim($Document->getValue($Document->getCell($Location['Schüler_Vorbildung'], $RunY)));
                            switch ($preDiploma) {
                                case '10. Klasse GYM':
                                case '10.KlasseGYM':
                                    $tblSchoolDiploma = Course::useService()->getSchoolDiplomaById(1);
                                    $tblSchoolType = Type::useService()->getTypeByShortName('Gy');
                                    break;
                                case 'Abitur':
                                    $tblSchoolDiploma = Course::useService()->getSchoolDiplomaById(2);
                                    $tblSchoolType = Type::useService()->getTypeByShortName('Gy');
                                    break;
                                case 'Realschule':
                                    $tblSchoolDiploma = Course::useService()->getSchoolDiplomaById(5);
                                    $tblSchoolType = Type::useService()->getTypeByShortName('OS');
                                    break;
                                default:
                                    $tblSchoolDiploma = null;
                                    $tblSchoolType = null;
                                    if ($preDiploma != '') {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Vorbildung:' . $preDiploma
                                            . ' konnte nicht angelegt werden.';
                                    }
                            }

                            $subjectArea = trim($Document->getValue($Document->getCell($Location['Schüler_Fachrichtung'], $RunY)));
                            if ($subjectArea != '') {
                                $tblTechnicalSubjectArea = Course::useService()->createTechnicalSubjectArea($subjectArea, $subjectArea);
                            } else {
                                $tblTechnicalSubjectArea = null;
                            }

                            $tblStudentTechnicalSchool = Student::useService()->insertStudentTechnicalSchool(
                                '',
                                '',
                                '',
                                null,
                                $tblSchoolDiploma ? $tblSchoolDiploma : null,
                                $tblSchoolType ? $tblSchoolType : null,
                                null,
                                null,
                                null,
                                null,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Abschlussjahr_ABS'], $RunY))),
                                '',
                                $tblTechnicalSubjectArea ? $tblTechnicalSubjectArea : null,
                                trim($Document->getValue($Document->getCell($Location['Schüler_Bafoeg'], $RunY))) == '1',
                                trim($Document->getValue($Document->getCell($Location['Schüler_Bafoeg_Beantragungsjahr'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Schüler_Bafoeg_Amt'], $RunY)))
                            );

                            $tblStudentBaptism = null;
                            $tblStudentIntegration = null;
                            $tblStudent = Student::useService()->insertStudent(
                                $tblPerson,
                                $studentNumber,
                                $tblStudentMedicalRecord,
                                $tblStudentTransport,
                                $tblStudentBilling,
                                $tblStudentLocker,
                                $tblStudentBaptism,
                                $tblStudentIntegration,
                                null,
                                '',
                                false,
                                false,
                                $tblStudentTechnicalSchool
                            );

                            if ($tblStudent) {
                                $importService->setStudentAgreement(
                                    'Schüler_Fotoerlaubnis',
                                    $RunY,
                                    $tblStudent,
                                    $tblStudentAgreementCategoryName,
                                    '1'
                                );
                                $importService->setStudentAgreement(
                                    'Schüler_Fotoerlaubnis',
                                    $RunY,
                                    $tblStudent,
                                    $tblStudentAgreementCategoryPhoto,
                                    '1'
                                );

                                // Schülertransfer
                                $enrollmentDate = $importService->formatDateString('Schüler_Einschulung_am', $RunY, $error);
                                if ($enrollmentDate !== '') {
                                    $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
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

                                $diploma = trim($Document->getValue($Document->getCell($Location['Schüler_Schulabschluss'],
                                    $RunY)));
                                if ($diploma != '') {
                                    switch ($diploma) {
                                        case 'HSR': $tblCourse = Course::useService()->getCourseByName('Gymnasium'); break;
                                        case 'RSA': $tblCourse = Course::useService()->getCourseByName('Realschule'); break;
                                        case 'HSA': $tblCourse = Course::useService()->getCourseByName('Hauptschule'); break;
                                        default: $tblCourse = false;
                                    }

                                    if ($tblCourse) {
                                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                                        Student::useService()->insertStudentTransfer(
                                            $tblStudent,
                                            $tblStudentTransferType,
                                            null,
                                            null,
                                            $tblCourse
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_Schulabschluss:' . $diploma . ' nicht gefunden.';
                                    }
                                } else {
                                    $course = trim($Document->getValue($Document->getCell($Location['Fächer_Bildungsgang'],
                                        $RunY)));
                                    if ($course != '') {
                                        switch ($course) {
                                            case 'HS': $tblCourse = Course::useService()->getCourseByName('Hauptschule'); break;
                                            case 'RS': $tblCourse = Course::useService()->getCourseByName('Realschule'); break;
                                            default: $tblCourse = false;
                                        }

                                        $lastCourse = trim($Document->getValue($Document->getCell($Location['Fächer_letzter_Bildungsgang'],
                                            $RunY)));
                                        if ($tblCourse) {
                                            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Process');
                                            Student::useService()->insertStudentTransfer(
                                                $tblStudent,
                                                $tblStudentTransferType,
                                                null,
                                                null,
                                                $tblCourse,
                                                '',
                                                $lastCourse ? 'Letzter Bildungsgang: ' . $lastCourse : ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Bildungsgang:' . $course . ' nicht gefunden.';
                                        }
                                    }
                                }


                                $arriveDate = $importService->formatDateString('Schüler_Aufnahme_am', $RunY, $error);

                                $arriveTypeShort = trim($Document->getValue($Document->getCell($Location['Schüler_letzte_Schulart'],
                                    $RunY)));
                                $arriveType = false;
                                if ($arriveTypeShort != '') {
                                    if ($arriveTypeShort == 'GY') {
                                        $arriveTypeShort = 'Gy';
                                    }
                                    if (!($arriveType = Type::useService()->getTypeByShortName($arriveTypeShort))) {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_letzte_Schulart:' . $arriveTypeShort . ' nicht gefunden.';
                                    }
                                }

                                $arriveSchool = null;
                                $arriveSchoolId = trim($Document->getValue($Document->getCell($Location['Schüler_abgebende_Schule_ID'],
                                    $RunY)));
                                if ($arriveSchoolId != '') {
                                    if (($companyList = Company::useService()->getCompanyListByImportId($arriveSchoolId))) {
                                        if (count($companyList) == 1) {
                                            $arriveSchool = current($companyList);
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_abgebende_Schule_ID:' . $arriveSchoolId
                                                . ' es wurde mehr als eine Schule mit dieser ID gefunden, die abgebende Schule wurde nicht zugewiesen';
                                        }
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_abgebende_Schule_ID:' . $arriveSchoolId . ' nicht gefunden.';
                                    }
                                }

                                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                                Student::useService()->insertStudentTransfer(
                                    $tblStudent,
                                    $tblStudentTransferType,
                                    $arriveSchool ? $arriveSchool : null,
                                    $arriveType ? $arriveType : null,
                                    null,
                                    $arriveDate,
                                    ''
                                );

                                $leaveSchool = null;
                                $leaveSchoolId = trim($Document->getValue($Document->getCell($Location['Schüler_aufnehmende_Schule_ID'],
                                    $RunY)));
                                if ($leaveSchoolId != '') {
                                    if (($companyList = Company::useService()->getCompanyListByImportId($leaveSchoolId))) {
                                        if (count($companyList) == 1) {
                                            $leaveSchool = current($companyList);
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_aufnehmende_Schule_ID:' . $leaveSchoolId
                                                . ' es wurde mehr als eine Schule mit dieser ID gefunden, die aufnehmende Schule wurde nicht zugewiesen';
                                        }
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Schüler_aufnehmende_Schule_ID:' . $leaveSchoolId . ' nicht gefunden.';
                                    }
                                }

                                $leaveDate = $importService->formatDateString('Schüler_Abgang_am', $RunY, $error);
                                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
                                Student::useService()->insertStudentTransfer(
                                    $tblStudent,
                                    $tblStudentTransferType,
                                    $leaveSchool ? $leaveSchool : null,
                                    null,
                                    null,
                                    $leaveDate,
                                    ''
                                );

                                // Fächer
                                $subjectReligion = trim($Document->getValue($Document->getCell($Location['Fächer_Religionsunterricht'],
                                    $RunY)));
                                if ($subjectReligion !== '') {
                                    if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectReligion))) {
                                        Student::useService()->addStudentSubject(
                                            $tblStudent,
                                            Student::useService()->getStudentSubjectTypeByIdentifier('Religion'),
                                            Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                            $tblSubject
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Religionsunterricht:' . $subjectReligion . ' nicht gefunden.';
                                    }
                                }

                                // Profilfach
                                if ($tblType && $tblType->getShortName() == 'Gy') {
                                    $profile = trim($Document->getValue($Document->getCell($Location['Fächer_Profilfach'],
                                        $RunY)));
                                    if ($profile != '') {
                                        if (($tblSubjectProfile = Subject::useService()->getSubjectByAcronym($profile))) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                                $tblSubjectProfile
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Profilfach:' . $profile . ' nicht gefunden.';
                                        }
                                    }
                                }

                                // Neigungskurs
                                $subjectOrientation = trim($Document->getValue($Document->getCell($Location['Fächer_Neigungskurs'],
                                    $RunY)));
                                if ($subjectOrientation != '') {
                                    if (($tblSubjectOrientation = Subject::useService()->insertSubject($subjectOrientation, $subjectOrientation))) {
                                        Student::useService()->addStudentSubject(
                                            $tblStudent,
                                            Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'),
                                            Student::useService()->getStudentSubjectRankingByIdentifier('1'),
                                            $tblSubjectOrientation
                                        );
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Neigungskurs:' . $subjectOrientation . ' konnte nicht angelegt werden.';
                                    }
                                }

                                // Arbeitsgemeinschaften
                                for ($i = 1; $i < 5; $i++) {
                                    $subjectTeam = trim($Document->getValue($Document->getCell($Location['Fächer_Arbeitsgemeinschaft' . $i],
                                        $RunY)));
                                    if ($subjectTeam != '' && $subjectTeam != '--') {
                                        if (($tblSubjectTeam = Subject::useService()->insertSubject($subjectTeam, $subjectTeam))) {
                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier((string) $i),
                                                $tblSubjectTeam
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Arbeitsgemeinschaft:' . $subjectTeam . ' konnte nicht angelegt werden.';
                                        }
                                    }
                                }

                                for ($i = 1; $i < 5; $i++) {
                                    $subjectLanguage = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i],
                                        $RunY)));
                                    if ($subjectLanguage !== '') {
                                        $tblSubject = Subject::useService()->getSubjectByAcronym($subjectLanguage);
                                        if ($tblSubject) {
                                            $levelFrom = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i.'_von'],
                                                $RunY)));
                                            $tblLevelFrom = false;
                                            if ($levelFrom != '') {
                                                $level = intval($levelFrom);
                                                if ($tblType && $level > 0 && $level < 13) {
                                                    $tblLevelFrom = Division::useService()->insertLevel($tblType, $level);
                                                } else {
                                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . '_von:' . $levelFrom . ' nicht gefunden.';
                                                }
                                            }

                                            $levelTill = trim($Document->getValue($Document->getCell($Location['Fächer_Fremdsprache'.$i.'_bis'],
                                                $RunY)));
                                            $tblLevelTill = false;
                                            if ($levelTill != '') {
                                                $level = intval($levelTill);
                                                if ($tblType && $level > 0 && $level < 13) {
                                                    $tblLevelTill = Division::useService()->insertLevel($tblType, $level);
                                                } else {
                                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . '_bis:' . $levelTill . ' nicht gefunden.';
                                                }
                                            }

                                            Student::useService()->addStudentSubject(
                                                $tblStudent,
                                                Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'),
                                                Student::useService()->getStudentSubjectRankingByIdentifier($i),
                                                $tblSubject,
                                                $tblLevelFrom ? $tblLevelFrom : null,
                                                $tblLevelTill ? $tblLevelTill : null
                                            );
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Fremdsprache' . $i . ':' . $subjectLanguage . ' nicht gefunden.';
                                        }
                                    }
                                }

                                $liberation = trim($Document->getValue($Document->getCell($Location['Fächer_Sportbefreiung'],
                                    $RunY)));
                                if ($liberation != '') {
                                    switch ($liberation) {
                                        case '0': // nicht befreit
                                            $tblStudentLiberationType = Student::useService()->getStudentLiberationTypeById(1); break;
                                        case '1': // vollbefreit
                                            $tblStudentLiberationType = Student::useService()->getStudentLiberationTypeById(3); break;
                                        case '2': // teilbefreit
                                            $tblStudentLiberationType = Student::useService()->getStudentLiberationTypeById(2); break;
                                        default: $error[] = 'Zeile: ' . ($RunY + 1) . ' Fächer_Sportbefreiung:' . $liberation . ' nicht gefunden.';
                                            $tblStudentLiberationType = false;
                                    }

                                    if ($tblStudentLiberationType) {
                                        Student::useService()->addStudentLiberation($tblStudent, $tblStudentLiberationType);
                                    }
                                }
                            }

                            // Sorgeberechtigte
                            $personList = array();
                            for ($i = 1; $i < 5; $i++) {
                                $tblPersonCustody = $this->setCustody(
                                    $i,
                                    $tblPerson,
                                    $tblType ? $tblType->getShortName() : 'XX',
                                    $Document,
                                    $Location,
                                    $RunY,
                                    $error,
                                    $importService,
                                    $tblCommonGenderMale,
                                    $tblCommonGenderFemale,
                                    $countCustody,
                                    $countCustodyExists,
                                    $consumerAcronym
                                );

                                if ($tblPersonCustody) {
                                    $personList['S' . $i]  = $tblPersonCustody;
                                }
                            }

                            // Contact
                            for ($i = 1; $i < 13; $i++) {
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Telefon'.$i],
                                    $RunY)));
                                if ($phoneNumber != '') {
                                    $remarkPhone = '';
                                    if ($consumerAcronym == 'HOGA') {
                                        switch ($i) {
                                            case 1: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                break;
                                            case 2: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(3);
                                                break;
                                            case 3: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 4: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 5: $tblPersonContact = isset($personList['S3']) ? $personList['S3'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                break;
                                            case 6: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(3);
                                                break;
                                            case 7: $tblPersonContact = isset($personList['S4']) ? $personList['S4'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                break;
                                            case 8: $tblPersonContact = isset($personList['S3']) ? $personList['S3'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 9: $tblPersonContact = isset($personList['S4']) ? $personList['S4'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 10: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                break;
                                            case 11: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            default: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Telefon' . $i . ':' . $phoneNumber
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } elseif ($consumerAcronym == 'EOSL') {
                                        switch ($i) {
                                            case 1: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                break;
                                            case 2: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                $remarkPhone = 'sonstige';
                                                break;
                                            case 3: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 4: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(2);
                                                break;
                                            case 5: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(3);
                                                break;
                                            case 6: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                $tblPhoneType = Phone::useService()->getTypeById(3);
                                                break;
                                            default: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                }
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Telefon' . $i . ':' . $phoneNumber
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } elseif ($consumerAcronym == 'EVOSG' || $consumerAcronym == 'EVOS') {
                                        switch ($i) {
                                            case 1: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                $remarkPhone = 'Festnetz Eltern';
                                                break;
                                            case 2: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                $remarkPhone = 'Großeltern, Onkel, Tante, Geschwister usw. / oder Fax';
                                                break;
                                            case 3: $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                } else {
                                                    $tblPhoneType = Phone::useService()->getTypeById(3);
                                                }
                                                $remarkPhone = 'Festnetz Arbeit und/oder Handy';
                                                break;
                                            case 4: $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                } else {
                                                    $tblPhoneType = Phone::useService()->getTypeById(3);
                                                }
                                                $remarkPhone = 'Festnetz Arbeit und/oder Handy';
                                                break;
                                            case 5: $tblPersonContact = $tblPerson;
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                } else {
                                                    $tblPhoneType = Phone::useService()->getTypeById(1);
                                                }
                                                $remarkPhone = 'zusätzliche Nummer von Eltern oder Tante, Geschwister, …';
                                                break;
                                            case 6: $tblPersonContact = $tblPerson;
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                } else {
                                                    $tblPhoneType = Phone::useService()->getTypeById(1);
                                                }
                                                $remarkPhone = 'zusätzliche Nummer Lebensgefährte/in, Großeltern, …';
                                                break;
                                            default: $tblPersonContact = $tblPerson;
                                                $tblPhoneType = Phone::useService()->getTypeById(1);
                                                if (0 === strpos($phoneNumber, '01')) {
                                                    $tblPhoneType = Phone::useService()->getTypeById(2);
                                                }
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Telefon' . $i . ':' . $phoneNumber
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } else {
                                        $tblPersonContact = $tblPerson;
                                        $tblPhoneType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblPhoneType = Phone::useService()->getTypeById(2);
                                        }
                                    }

                                    if (0 === strpos($phoneNumber, '1')) {
                                        $phoneNumber = '0' . $phoneNumber;
                                    }

                                    Phone::useService()->insertPhoneToPerson($tblPersonContact, $phoneNumber, $tblPhoneType, $remarkPhone);
                                }
                            }

                            $FaxNumber = trim($Document->getValue($Document->getCell($Location['Kommunikation_Fax'],
                                $RunY)));
                            if ($FaxNumber != '') {
                                Phone::useService()->insertPhoneToPerson($tblPerson, $FaxNumber,
                                    Phone::useService()->getTypeById(7), '');
                            }

                            for ($i = 0; $i < 5; $i++) {
                                $mailAddress = trim($Document->getValue($Document->getCell($Location['Kommunikation_Email' . ($i == 0 ? '' : $i)],
                                    $RunY)));
                                if ($mailAddress != '') {
                                    if ($consumerAcronym == 'HOGA') {
                                        switch ($i) {
                                            case 1:
                                                $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                break;
                                            case 2:
                                                $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                break;
                                            case 3:
                                                $tblPersonContact = isset($personList['S3']) ? $personList['S3'] : false;
                                                break;
                                            case 4:
                                                $tblPersonContact = isset($personList['S4']) ? $personList['S4'] : false;
                                                break;
                                            default:
                                                $tblPersonContact = $tblPerson;
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Email' . ($i == 0 ? '' : $i) . ':' . $mailAddress
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } elseif ($consumerAcronym == 'EOSL') {
                                        switch ($i) {
                                            case 1:
                                                $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                break;
                                            case 2:
                                                $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                break;
                                            default:
                                                $tblPersonContact = $tblPerson;
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Email' . ($i == 0 ? '' : $i) . ':' . $mailAddress
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } else if ($consumerAcronym == 'EVOSG' || $consumerAcronym == 'EVOS') {
                                        switch ($i) {
                                            case 1:
                                                $tblPersonContact = isset($personList['S1']) ? $personList['S1'] : false;
                                                break;
                                            case 2:
                                                $tblPersonContact = isset($personList['S2']) ? $personList['S2'] : false;
                                                break;
                                            case 3:
                                                $tblPersonContact = isset($personList['S3']) ? $personList['S3'] : false;
                                                break;
                                            default:
                                                $tblPersonContact = $tblPerson;
                                        }

                                        if (!$tblPersonContact) {
                                            $tblPersonContact = $tblPerson;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Kommunikation_Email' . ($i == 0 ? '' : $i) . ':' . $mailAddress
                                                . ' zugehöriger Sorgeberechtigter nicht vorhanden, der Kontakt wurde dem Schüler zugewiesen.';
                                        }
                                    } else {
                                        $tblPersonContact = $tblPerson;
                                    }

                                    Mail::useService()->insertMailToPerson($tblPersonContact, $mailAddress,
                                        Mail::useService()->getTypeById(1), '');
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.').
                        new Success('Es wurden '.($countCustody).' Sorgeberechtigte erfolgreich angelegt.').
                        ( $countCustodyExists > 0 ?
                            new Warning($countCustodyExists.' Sorgeberechtigte exisistieren bereits.') : '' )
                        . new Panel(
                            'Fehler',
                            $error,
                            Panel::PANEL_TYPE_DANGER
                        );
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param $ranking
     * @param TblPerson $tblPerson
     * @param $typeShortName
     * @param $Document
     * @param $Location
     * @param $RunY
     * @param $error
     * @param $importService
     * @param $tblCommonGenderMale
     * @param $tblCommonGenderFemale
     * @param $countAdd
     * @param $countExists
     * @param $consumerAcronym
     *
     * @return TblPerson|bool
     */
    private function setCustody(
        $ranking,
        TblPerson $tblPerson,
        $typeShortName,
        $Document,
        $Location,
        $RunY,
        $error,
        $importService,
        $tblCommonGenderMale,
        $tblCommonGenderFemale,
        &$countAdd,
        &$countExists,
        $consumerAcronym
    ) {

        $tblPersonCustody = null;
        $CustodyFirstName = $this->getValue('Sorgeberechtigter' . $ranking . '_Vorname', $Location, $Document, $RunY);
        $CustodyLastName = $this->getValue('Sorgeberechtigter' . $ranking . '_Name', $Location, $Document, $RunY);
        $cityCode = $importService->formatZipCode('Sorgeberechtigter' . $ranking . '_Plz', $RunY);
        if ($CustodyLastName !== '') {
            $status = $this->getValue('Sorgeberechtigter' . ($ranking == 1 ? '' : $ranking) . '_Status', $Location, $Document, $RunY);
            $isSingleParent = false;

            if ($consumerAcronym == 'HOGA') {
                // Beziehungstyp
                switch ($status) {
                    case 'FAM':
                    case 'ELT':
                    case 'NMU':
                    case 'NVA':
                        $tblRelationShipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                        $relationShipRanking = $ranking;
                        break;
                    default:
                        $tblRelationShipType = Relationship::useService()->getTypeByName('Notfallkontakt');
                        $relationShipRanking = null;
                }
                // alleinerziehend
                switch ($status) {
                    case 'AER':
                        $isSingleParent = true;
                        break;
                    default:
                        $isSingleParent = false;
                }
            } elseif ($consumerAcronym == 'EVOSG' || $consumerAcronym == 'EVOS') {
                if ($ranking < 3) {
                    $tblRelationShipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $relationShipRanking = $ranking;
                } else {
                    $tblRelationShipType = Relationship::useService()->getTypeByName('Notfallkontakt');
                    $relationShipRanking = null;
                }
            } else {
                $tblRelationShipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                $relationShipRanking = $ranking;
            }

            $tblPersonCustodyExists = $this->usePeoplePerson()->getPersonExists(
                $CustodyFirstName,
                $CustodyLastName,
                $cityCode
            );
            if (!$tblPersonCustodyExists) {
                $gender = strtolower($this->getValue('Sorgeberechtigter' . $ranking . '_Geschlecht', $Location, $Document, $RunY));
                switch ($gender) {
                    case 'm': $tblCommonGender = $tblCommonGenderMale;
                        $tblSalutation = \SPHERE\Application\People\Person\Person::useService()->getSalutationById(1);
                        break;
                    case 'w': $tblCommonGender = $tblCommonGenderFemale;
                        $tblSalutation = \SPHERE\Application\People\Person\Person::useService()->getSalutationById(2);
                        break;
                    default: $tblCommonGender = false;
                        $tblSalutation = false;
                }

                $tblPersonCustody = $this->usePeoplePerson()->insertPerson(
                    $tblSalutation ? $tblSalutation : null,
                    $this->getValue('Sorgeberechtigter' . $ranking . '_Titel', $Location, $Document, $RunY),
                    $CustodyFirstName,
                    '',
                    $CustodyLastName,
                    array(
                        0 => Group::useService()->getGroupById(1),          //Personendaten
                        1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                    ),
                    '',
                    $typeShortName . '_Zeile_' . ($RunY + 1) . '_S' . $ranking
                );

                Common::useService()->insertMeta(
                    $tblPersonCustody,
                    $importService->formatDateString('Sorgeberechtigter' . $ranking . '_GD', $RunY, $error),
                    $this->getValue('Sorgeberechtigter' . $ranking . '_GO', $Location, $Document, $RunY),
                    $tblCommonGender ? $tblCommonGender : null,
                    '',
                    '',
                    0,
                    '',
                    ''
                );

                $occupation = $this->getValue('Sorgeberechtigter' . $ranking . '_Beruf', $Location, $Document, $RunY);
                if ($occupation) {
                    Custody::useService()->insertMeta($tblPersonCustody, $occupation, '', '');
                }

                Relationship::useService()->insertRelationshipToPerson(
                    $tblPersonCustody,
                    $tblPerson,
                    $tblRelationShipType,
                    $status,
                    $relationShipRanking,
                    $isSingleParent
                );

                // Sorgeberechtigter1 Address
                $city = $this->getValue('Sorgeberechtigter' . $ranking . '_Wohnort', $Location, $Document, $RunY);
                if ($city != '') {
                    $Street = $this->getValue('Sorgeberechtigter' . $ranking . '_Straße', $Location, $Document, $RunY);
                    if (preg_match_all('!\d+!', $Street, $matches)) {
                        $pos = strpos($Street, $matches[0][0]);
                        if ($pos !== null) {
                            $StreetName = trim(substr($Street, 0, $pos));
                            $StreetNumber = trim(substr($Street, $pos));

                            Address::useService()->insertAddressToPerson(
                                $tblPersonCustody,
                                $StreetName,
                                $StreetNumber,
                                $cityCode,
                                $city,
                                $this->getValue('Sorgeberechtigter' . $ranking . '_Ortsteil', $Location, $Document, $RunY),
                                ''
                            );

                        }
                    }
                }

                $countAdd++;

                return $tblPersonCustody;
            } else {

                Relationship::useService()->insertRelationshipToPerson(
                    $tblPersonCustodyExists,
                    $tblPerson,
                    $tblRelationShipType,
                    $status,
                    $relationShipRanking,
                    $isSingleParent
                );

                $countExists++;

                return $tblPersonCustodyExists;
            }
        }

        return false;
    }

    /**
     * @param string $columnName
     * @param $Location
     * @param $Document
     * @param $RunY
     *
     * @return string
     */
    private function getValue($columnName, $Location, $Document, $RunY)
    {
        if ($Location[$columnName] !== null) {
            return trim($Document->getValue($Document->getCell($Location[$columnName], $RunY)));
        }

        return '';
    }

    /**
     * @return Person
     */
    public static function usePeoplePerson()
    {

        return new Person(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/../../../People/Person/Service/Entity', 'SPHERE\Application\People\Person\Service\Entity'
        );
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|String
     */
    public function createTeachersFromFile(
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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'Lehrerkürzel' => null,
                    'Name'         => null,
                    'Vorname'      => null,
                    'Anrede'       => null,
                    'Geschlecht'   => null,
                    'Straße'       => null,
                    'Plz'          => null,
                    'Wohnort'      => null,
                    'Ortsteil'     => null,
                    'Geburtsdatum' => null,
                    'Geburtsort'   => null,
                    'Geburtsname'  => null,
                    'Staatsangehörigkeit' => null,
                    'Konfession'   => null,
                    'Telefon1'     => null,
                    'Telefon2'     => null,
                    'Fax'          => null,
                    'EMail'        => null,
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
                    $countTeacher = 0;
                    $tblCommonGenderMale = Common::useService()->getCommonGenderByName('Männlich');
                    $tblCommonGenderFemale = Common::useService()->getCommonGenderByName('Weiblich');

                    $importService = new ImportService($Location, $Document);
                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($lastName) {

                            $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'],
                                $RunY))) == 'm' ? $tblCommonGenderMale : $tblCommonGenderFemale;

                            $tblPerson = $this->usePeoplePerson()->insertPerson(
                                $this->usePeoplePerson()->getSalutationById($gender),
                                '',
                                trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY))),
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupById(1),           //Personendaten
                                    1 => Group::useService()->getGroupById(5),         //Mitarbeiter
                                    2 => Group::useService()->getGroupByMetaTable('TEACHER')
                                ),
                                trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)))
                            );

                            if ($tblPerson !== false) {
                                $countTeacher++;

                                // Teacher Common
                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $importService->formatDateString('Geburtsdatum', $RunY, $error),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'],
                                        $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Konfession'],
                                        $RunY))),
                                    0,
                                    '',
                                    ''
                                );

                                // Teacher Meta
                                $acronym = trim($Document->getValue($Document->getCell($Location['Lehrerkürzel'],
                                    $RunY)));
                                if ($acronym != '') {
                                    Teacher::useService()->insertTeacher($tblPerson, $acronym);
                                }

                                // Teacher Address
                                if (trim($Document->getValue($Document->getCell($Location['Wohnort'],
                                        $RunY))) != ''
                                ) {
                                    $Street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $Street, $matches)) {
                                        $pos = strpos($Street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $StreetName = trim(substr($Street, 0, $pos));
                                            $StreetNumber = trim(substr($Street, $pos));

                                            Address::useService()->insertAddressToPerson(
                                                $tblPerson,
                                                $StreetName,
                                                $StreetNumber,
                                                $importService->formatZipCode('Plz', $RunY),
                                                trim($Document->getValue($Document->getCell($Location['Wohnort'],
                                                    $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Ortsteil'],
                                                    $RunY))),
                                                ''
                                            );

                                        }
                                    }
                                }

                                // Teacher Contact
                                for ($i = 1; $i < 3; $i++) {
                                    $PhoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'.$i],
                                        $RunY)));
                                    if ($PhoneNumber != '') {
                                        Phone::useService()->insertPhoneToPerson($tblPerson, $PhoneNumber,
                                            Phone::useService()->getTypeById(1), '');
                                    }
                                }
                                $FaxNumber = trim($Document->getValue($Document->getCell($Location['Fax'],
                                    $RunY)));
                                if ($FaxNumber != '') {
                                    Phone::useService()->insertPhoneToPerson($tblPerson, $FaxNumber,
                                        Phone::useService()->getTypeById(7), '');
                                }
                                $MailAddress = trim($Document->getValue($Document->getCell($Location['EMail'],
                                    $RunY)));
                                if ($MailAddress != '') {
                                    Mail::useService()->insertMailToPerson($tblPerson, $MailAddress,
                                        Mail::useService()->getTypeById(1), '');
                                }
                            }
                        }
                    }
                    return
                        new Success('Es wurden '.$countTeacher.' Lehrer erfolgreich angelegt.')
                            . (empty($error)
                                ? ''
                                : new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                )
                            );
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     * @param null                $TypeId
     * @param null                $YearId
     *
     * @return IFormInterface|String
     */
    public function createDivisionsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $TypeId = null,
        $YearId = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File || $TypeId === null || $YearId === null) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'Klasse'                            => null,
                    'Klassenstufe'                      => null,
                    'Klassenlehrer_kurz'                => null,
                    'Stellvertreter_Klassenlehrer_kurz' => null,
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
                    $countDivision = 0;
                    $countAddDivisionTeacher = 0;
                    $countTeacherNotExists = 0;

                    $tblType = Type::useService()->getTypeById($TypeId);
                    $tblYear = Term::useService()->getYearById($YearId);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        if (( $Level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'],
                                $RunY))) ) != ''
                        ) {
                            $tblLevel = Division::useService()->insertLevel($tblType, $Level);
                            if ($tblLevel) {
                                $Division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                                    $RunY)));
                                if ($Division != '') {
                                    if (( $pos = strpos($Division, $Level) ) !== false) {
                                        if (strlen($Division) > ( ( $start = $pos + strlen($Level) ) )) {
                                            $Division = substr($Division, $start);
                                        }
                                    }
                                    $tblDivision = Division::useService()->insertDivision($tblYear, $tblLevel,
                                        $Division);
                                    if ($tblDivision) {

                                        $countDivision++;
                                        $teacherCode = trim($Document->getValue($Document->getCell($Location['Klassenlehrer_kurz'],
                                            $RunY)));
                                        if ($teacherCode !== '') {
                                            $tblPerson = $this->usePeoplePerson()->getTeacherByRemark($teacherCode);
                                            if ($tblPerson) {
                                                Division::useService()->insertDivisionTeacher($tblDivision, $tblPerson);
                                                $countAddDivisionTeacher++;
                                            } else {
                                                $countTeacherNotExists++;
                                            }
                                        }
                                        $teacherCode = trim($Document->getValue($Document->getCell($Location['Stellvertreter_Klassenlehrer_kurz'],
                                            $RunY)));
                                        if ($teacherCode !== '') {
                                            $tblPerson = $this->usePeoplePerson()->getTeacherByRemark($teacherCode);
                                            if ($tblPerson) {
                                                Division::useService()->insertDivisionTeacher($tblDivision, $tblPerson);
                                                $countAddDivisionTeacher++;
                                            } else {
                                                $countTeacherNotExists++;
                                            }
                                        }
                                    }
                                }
                            }

                        }
                    }
                    return
                        new Success('Es wurden '.$countDivision.' Klassen erfolgreich angelegt.').
                        new Success('Es wurden '.$countAddDivisionTeacher.' Klassenlehrer und Stellvertreter erfolgreich zugeordnet.').
                        ( $countTeacherNotExists > 0 ?
                            new Warning($countTeacherNotExists.' Lehrer nicht gefunden.') : '' );
                } else {
                    return new Warning(json_encode($Location))
                    . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|Success|string
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
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
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
                    'Einrichtungsname'   => null,
                    'Straße'   => null,
                    'Plz'   => null,
                    'Ort'   => null,
                    'Ortsteil'   => null
                );

                $OptionalLocation = array(
                    'Telefon' => null,
                    'Telefax'   => null,
                    'EMail_Adresse'   => null,
                    'Internet_Adresse'   => null,
                );

                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }

                    if (array_key_exists($Value, $OptionalLocation)) {
                        $OptionalLocation[$Value] = $RunX;
                    }
                }

                $importService = new ImportService($Location, $Document);

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countCompany = 0;
                    $Location = array_merge($Location, $OptionalLocation);

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $companyName = trim($Document->getValue($Document->getCell($Location['Einrichtungsname'], $RunY)));

                        if ($companyName) {
                            $importId = trim($Document->getValue($Document->getCell($Location['E.nummer'], $RunY)));
                            if (($tblCompany = Company::useService()->getCompanyByName($companyName, ''))) {
                                Company::useService()->updateCompanyImportId($tblCompany, $importId);
                            } else {
                                $tblCompany = Company::useService()->insertCompany(
                                    $companyName,
                                    '',
                                    '',
                                    $importId
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

                                    list($streetName, $streetNumber) = $importService->splitStreet('Straße', $RunY);
                                    $cityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                    $cityCode = $importService->formatZipCode('Plz', $RunY);

                                    if ($streetName != '' && $streetNumber != '' && $cityName != '' && $cityCode != '') {
                                        Address::useService()->insertAddressToCompany(
                                            $tblCompany,
                                            $streetName,
                                            $streetNumber,
                                            $cityCode,
                                            $cityName,
                                            trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY))),
                                            ''
                                        );
                                    }

                                    $phoneNumber = $this->getValue('Telefon', $Location, $Document, $RunY);
                                    if ($phoneNumber != '') {
                                        Phone::useService()->insertPhoneToCompany($tblCompany, $phoneNumber, Phone::useService()->getTypeById(3), '');
                                    }

                                    $faxNumber = $this->getValue('Telefax', $Location, $Document, $RunY);
                                    if ($faxNumber != '') {
                                        Phone::useService()->insertPhoneToCompany($tblCompany, $faxNumber, Phone::useService()->getTypeById(8), '');
                                    }

                                    $mailAddress = $this->getValue('EMail_Adresse', $Location, $Document, $RunY);
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToCompany($tblCompany, $mailAddress, Mail::useService()->getTypeById(2), '');
                                    }

                                    $web = $this->getValue('Internet_Adresse', $Location, $Document, $RunY);
                                    if ($web != '') {
                                        Web::useService()->insertWebToCompany($tblCompany, $web, Web::useService()->getTypeById(2), '');
                                    }
                                }
                            }
                        }
                    }

                    return new Success('Es wurden '.$countCompany.' Institutionen erfolgreich angelegt.');
                } else {
                    return new Info(json_encode($Location))
                        . new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}
