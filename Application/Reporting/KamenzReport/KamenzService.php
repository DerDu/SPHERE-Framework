<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 07:33
 */

namespace SPHERE\Application\Reporting\KamenzReport;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;

/**
 * Class KamenzService
 *
 * @package SPHERE\Application\Reporting\KamenzReport
 */
class KamenzService
{

    /**
     * @param TblType $tblSchoolType
     * @param array $summary
     *
     * @return TableData
     */
    public static function validate(TblType $tblSchoolType, &$summary = array())
    {
        if (($tblSetting = Consumer::useService()->getSetting(
                'Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel'))
            && $tblSetting->getValue()
        ) {
            $firstForeignLanguageLevel = $tblSetting->getValue();
        } else {
            $firstForeignLanguageLevel = 1;
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education','Lesson','Subject', 'HasOrientationSubjects'))
            && $tblSetting->getValue()
        ) {
            $hasOrientationSubjects = $tblSetting->getValue();
        } else {
            $hasOrientationSubjects = false;
        }

        $count['Gender'] = 0;
        $count['Birthday'] = 0;
        $count['Religion'] = 0;
        $count['Orientation'] = 0;
        $count['Profile'] = 0;
        $count['Student'] = 0;
        $count['Nationality'] = 0;
        $count['ForeignLanguage1'] = 0;
        $count['ForeignLanguage2'] = 0;
        $count['SchoolEnrollmentType'] = 0;
        $count['SchoolAttendanceStartDate'] = 0;
        $studentList = array();
        if (($tblCurrentYearList = Term::useService()->getYearByNow())) {
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && !$tblLevel->getIsChecked()
                            && ($tblType = $tblLevel->getServiceTblType())
                            && ($tblType->getId() == $tblSchoolType->getId())
                        ) {

                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                                foreach ($tblPersonList as $tblPerson) {
                                    if (!isset($studentList[$tblPerson->getId()])) {
                                        $count['Student']++;
                                        $gender = false;
                                        $birthday = false;
                                        $nationality = '';
                                        $tblStudent = $tblPerson->getStudent();
                                        if (($tblCommon = $tblPerson->getCommon())) {
                                            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                                                if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                                                    $gender = $tblGender->getName();
                                                }
                                                if (($birthdayDate = $tblCommonBirthDates->getBirthday())) {
                                                    $birthday = $birthdayDate;
                                                }
                                            }
                                            if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                                                $nationality = $tblCommonInformation->getNationality();
                                            }
                                        }

                                        if (!$gender) {
                                            $gender = new Warning('Keine Geschlecht hinterlegt.', new Exclamation());
                                            $count['Gender']++;
                                        }
                                        if (!$birthday) {
                                            $birthday = new Warning('Kein Geburtsdatum hinterlegt.', new Exclamation());
                                            $count['Birthday']++;
                                        }

                                        if ($tblStudent) {
                                            $hasMigrationBackground = $tblStudent->getHasMigrationBackground() ? 'ja' : 'nein';
                                            $isInPreparationDivisionForMigrants = $tblStudent->isInPreparationDivisionForMigrants()
                                                ? 'ja' : 'nein';
                                            if ($tblStudent->getHasMigrationBackground()
                                                && $nationality == ''
                                            ) {
                                                $nationality = new Warning('Kein Staatsangehörigkeit hinterlegt.',
                                                    new Exclamation());
                                                $count['Nationality']++;
                                            }
                                        } else {
                                            $hasMigrationBackground = 'nein';
                                            $isInPreparationDivisionForMigrants = 'nein';
                                        }

                                        $foreignLanguage1 = '';
                                        $foreignLanguages = self::getForeignLanguages($tblPerson);
                                        if (isset($foreignLanguages[1])) {
                                            $foreignLanguage1 = $foreignLanguages[1];
                                        } else {
                                            if (floatval($tblLevel->getName()) >= floatval($firstForeignLanguageLevel)) {
                                                $count['ForeignLanguage1']++;
                                                $foreignLanguage1 = new Warning('Keine 1. Fremdsprache hinterlegt.',
                                                    new Exclamation());
                                            }
                                        }

                                        if (isset($foreignLanguages[2])) {
                                            $foreignLanguage2 = $foreignLanguages[2];
                                        } elseif ($tblSchoolType->getName() == 'Gymnasium'
                                            && preg_match('!(0?(6|7|8|9|10))!is', $tblLevel->getName())
                                        ) {
                                            $count['ForeignLanguage2']++;
                                            $foreignLanguage2 = new Warning('Keine 2. Fremdsprache hinterlegt.',
                                                new Exclamation());
                                        } else {
                                            $foreignLanguage2 = '';
                                        }

                                        $studentList[$tblPerson->getId()] = array(
                                            'Division' => $tblDivision->getDisplayName(),
                                            'Name' => $tblPerson->getLastFirstName(),
                                            'Gender' => $gender,
                                            'Birthday' => $birthday,
                                            'ForeignLanguage1' => $foreignLanguage1,
                                            'ForeignLanguage2' => $foreignLanguage2,
                                            'ForeignLanguage3' => isset($foreignLanguages[3]) ? $foreignLanguages[3] : '',
                                            'ForeignLanguage4' => isset($foreignLanguages[4]) ? $foreignLanguages[4] : '',
                                            'Religion' => self::getReligion($tblPerson, $count),
                                            'Nationality' => $nationality,
                                            'HasMigrationBackground' => $hasMigrationBackground,
                                            'IsInPreparationDivisionForMigrants' => $isInPreparationDivisionForMigrants,
                                            'Option' => new Standard(
                                                '',
                                                '/People/Person',
                                                new Person(),
                                                array(
                                                    'Id' => $tblPerson->getId()
                                                ),
                                                'Zur Person wechseln'
                                            )
                                        );

                                        if ($hasOrientationSubjects) {
                                            if (($tblSchoolType->getName() == 'Mittelschule / Oberschule')) {
                                                if (($orientation = self::getOrientation($tblPerson))) {
                                                    $studentList[$tblPerson->getId()]['Orientation'] = $orientation;
                                                } elseif (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())
                                                    && !isset($foreignLanguages[2])
                                                ) {
                                                    $count['Orientation']++;
                                                    $studentList[$tblPerson->getId()]['Orientation']
                                                        = new Warning('Kein Neigungskurs/2.FS hinterlegt.',
                                                        new Exclamation());
                                                }
                                            }
                                        }

                                        if (($tblSchoolType->getName() == 'Gymnasium')) {
                                            if (($profile = self::getProfile($tblPerson))) {
                                                $studentList[$tblPerson->getId()]['Profile'] = $profile;
                                            } elseif (preg_match('!(0?(8|9|10))!is', $tblLevel->getName())) {
                                                $count['Profile']++;
                                                $studentList[$tblPerson->getId()]['Profile']
                                                    = new Warning('Kein Profil hinterlegt.',
                                                    new Exclamation());
                                            }
                                        }

                                        if ($tblSchoolType->getName() == 'Grundschule') {
                                            if ($tblStudent
                                                && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT'))
                                                && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                                                    $tblStudent, $tblStudentTransferType
                                                ))
                                                && ($tblSchoolEnrollmentType =$tblStudentTransfer->getTblStudentSchoolEnrollmentType())
                                            ) {
                                                $studentList[$tblPerson->getId()]['SchoolEnrollmentType']
                                                    = $tblSchoolEnrollmentType->getName();
                                            } else {
                                                $studentList[$tblPerson->getId()]['SchoolEnrollmentType']
                                                    =  new Warning('Keine Einschulungsart hinterlegt.',
                                                    new Exclamation());
                                                $count['SchoolEnrollmentType']++;
                                            }

                                            if ($tblStudent
                                                && $tblStudent->getSchoolAttendanceStartDate()
                                            ) {
                                                $studentList[$tblPerson->getId()]['SchoolAttendanceStartDate']
                                                    = $tblStudent->getSchoolAttendanceStartDate();
                                            } else {
                                                $studentList[$tblPerson->getId()]['SchoolAttendanceStartDate']
                                                    =  new Warning('Keine Schulpflicht beginnt am hinterlegt.',
                                                    new Exclamation());
                                                $count['SchoolAttendanceStartDate']++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        array_unshift($summary, new Info($count['Student'] . ' Schüler besuchen die/das ' . $tblSchoolType->getName() . '.'));
        $summary = self::setSummary($summary, $count);

        $columns = array(
            'Division' => 'Klasse',
            'Name' => 'Name',
            'Gender' => 'Geschlecht',
            'Birthday' => 'Geburtsdatum',
            'ForeignLanguage1' => '1. FS',
            'ForeignLanguage2' => '2. FS',
            'ForeignLanguage3' => '3. FS',
            'ForeignLanguage4' => '4. FS',
            'Religion' => 'Religion',
            'Nationality' => 'Staatsangehörigkeit',
            'HasMigrationBackground' => 'Herkunftssprache ist nicht oder nicht ausschließlich Deutsch',
//            'IsInPreparationDivisionForMigrants' => 'Besucht Vorbereitungsklasse für Migranten'
        );

        if (($tblSchoolType->getName() == 'Mittelschule / Oberschule')) {
            $columns['Orientation'] = 'Neigungskurs';
        }

        if (($tblSchoolType->getName() == 'Grundschule')) {
            $columns['SchoolAttendanceStartDate'] = 'Schulpflicht beginnt am';
            $columns['SchoolEnrollmentType'] = 'Einschulungsart';
        }

        if (($tblSchoolType->getName() == 'Gymnasium')) {
            $columns['Profile'] = 'Profil';
        }

        $columns['Option'] = '';

        return new TableData(
            $studentList,
            new Title('Schüler in einer aktuellen Klasse (Schulart: ' . $tblSchoolType->getName() . ')'),
            $columns,
            array(
                'paging' => false,
                'iDisplayLength' => -1,
                'order' => array(array(0, 'asc'), array(1, 'asc')),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => array(3,12)),
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    private static function getForeignLanguages(TblPerson $tblPerson)
    {
        $subjects = array();
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {

            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                ) {

                    $subjects[$tblStudentSubjectRanking->getIdentifier()] = $tblSubject->getAcronym();
                }
            }
        }

        return $subjects;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $count
     *
     * @return string
     */
    private static function getReligion(TblPerson $tblPerson, &$count)
    {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && (($religion = self::getSubjectByStudentSubjectType($tblPerson, $tblStudentSubjectType)))
        ) {
            return $religion;
        }

        $count['Religion']++;
        return new Warning('Keine Religion hinterlegt.', new Exclamation());
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getOrientation(TblPerson $tblPerson)
    {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && (($subject = self::getSubjectByStudentSubjectType($tblPerson, $tblStudentSubjectType)))
        ) {
            return $subject;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getProfile(TblPerson $tblPerson)
    {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && (($subject = self::getSubjectByStudentSubjectType($tblPerson, $tblStudentSubjectType)))
        ) {
            return $subject;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSubjectType $tblStudentSubjectType
     *
     * @return bool|string
     */
    private static function getSubjectByStudentSubjectType(
        TblPerson $tblPerson,
        TblStudentSubjectType $tblStudentSubjectType
    ) {

        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                return $tblSubject->getAcronym();
            }
        }

        return false;
    }

    /**
     * @param int $count
     *
     * @return bool|TableData
     */
    public static function getStudentsWithoutDivision(&$count = 0)
    {

        $personList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblPersonList = Group::useService()->getMemberAllByGroup($tblGroup))
        ) {

            foreach ($tblPersonList as $tblMember) {
                $hasDivision = false;
                if (($tblPerson = $tblMember->getServiceTblPerson())) {
                    if (($tblPersonDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson))) {
                        foreach ($tblPersonDivisionList as $tblDivisionItem) {
                            if (($tblLevel = $tblDivisionItem->getTblLevel())
                                && !$tblLevel->getIsChecked()
                            ) {
                                $hasDivision = true;
                                break;
                            }
                        }
                    }

                    if (!$hasDivision) {
                        $count++;
                        $gender = '';
                        $birthday = '';
                        if (($tblCommon = $tblPerson->getCommon())) {
                            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                                if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                                    $gender = $tblGender->getName();
                                }
                                if (($birthdayDate = $tblCommonBirthDates->getBirthday())) {
                                    $birthday = $birthdayDate;
                                }
                            }
                        }

                        $personList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Gender' => $gender,
                            'Birthday' => $birthday,
                            'Address' => (($tblAddress = $tblPerson->fetchMainAddress())
                                ? $tblAddress->getGuiString() : '')
                        );
                    }
                }
            }
        }

        if (empty($personList)) {
            return false;
        } else {
            return new TableData(
                $personList,
                new Title('Schüler ohne Klasse im aktuellen Schuljahr'),
                array(
                    'Name' => 'Name',
                    'Gender' => 'Geschlecht',
                    'Birthday' => 'Geburtstag',
                    'Address' => 'Adresse'
                ),
                array(
                    'columnDefs' => array(
                        array('type' => 'de_date', 'targets' => 2),
                    ),
//                    'paging' => false,
//                    'iDisplayLength' => -1,
                    'responsive' => false
                )
            );
        }
    }

    /**
     * @param $summary
     * @param $count
     *
     * @return array
     */
    private static function setSummary(&$summary, $count)
    {

        if ($count['Gender'] > 0) {
            $summary[] = new Warning($count['Gender'] . ' Schüler/n ist kein Geschlecht zugeordnet.'
                , new Exclamation());
        }
        if ($count['Birthday'] > 0) {
            $summary[] = new Warning($count['Birthday'] . ' Schüler/n ist kein Geburtsdatum zugeordnet.'
                , new Exclamation());
        }
        if ($count['ForeignLanguage1'] > 0) {
            $summary[] = new Warning($count['ForeignLanguage1'] . ' Schüler/n ist keine 1. Fremdsprache zugeordnet.'
                , new Exclamation());
        }
        if ($count['ForeignLanguage2'] > 0) {
            $summary[] = new Warning($count['ForeignLanguage2'] . ' Schüler/n ist keine 2. Fremdsprache zugeordnet.'
                , new Exclamation());
        }
        if ($count['Religion'] > 0) {
            $summary[] = new Warning($count['Religion'] . ' Schüler/n ist keine Religion zugeordnet.'
                , new Exclamation());
        }
        if ($count['Orientation'] > 0) {
            $summary[] = new Warning($count['Orientation'] . ' Schüler/n ist kein Neigungskurs/2.FS zugeordnet.'
                , new Exclamation());
        }
        if ($count['Profile'] > 0) {
            $summary[] = new Warning($count['Profile'] . ' Schüler/n ist kein Profil zugeordnet.'
                , new Exclamation());
        }
        if ($count['Nationality'] > 0) {
            $summary[] = new Warning($count['Nationality'] . ' Schüler/n mit Migrationshintergrund ist keine Staatsangehörigkeit zugeordnet.'
                , new Exclamation());
        }
        if ($count['SchoolAttendanceStartDate'] > 0) {
            $summary[] = new Warning($count['SchoolAttendanceStartDate'] . ' Schüler/n ist keine Schulpflicht beginnt am zugeordnet.'
                , new Exclamation());
        }
        if ($count['SchoolEnrollmentType'] > 0) {
            $summary[] = new Warning($count['SchoolEnrollmentType'] . ' Schüler/n ist keine Einschulungsart zugeordnet.'
                , new Exclamation());
        }

        return $summary;
    }
}