<?php

namespace SPHERE\Application\Document\Generator\Service\Kamenz;

use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class KamenzReportService
 *
 * @package SPHERE\Application\Document\Generator\Service\Kamenz
 */
class KamenzReportService
{

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportOsContent(
        $Content
    ) {

        $tblCurrentYearList = false;
        $tblPastYearList = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Mittelschule / Oberschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countOrientationArray = array();
            $countDivisionStudentArray = array();
            $countDivisionStudentArrayForSecondarySchool = array();

            $tblTransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = count($tblPersonList);

                                foreach ($tblPersonList as $tblPerson) {

                                    $isInPreparationDivisionForMigrants = false;
                                    $tblStudent = $tblPerson->getStudent();

                                    if ($tblStudent
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if ($tblStudent
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    $birthDay = false;
                                    self::countStudentLevels($tblPerson, $tblLevel, $gender, $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                        $countMigrantsArray,
                                        $countMigrantsNationalityArray);

                                    $tblCourse = false;
                                    if ($tblStudent) {
                                        self::countForeignLanguages($tblStudent, $tblLevel, $tblKamenzSchoolType, $Content, $gender,
                                            $isInPreparationDivisionForMigrants, $countForeignSubjectArray,
                                            $countSecondForeignSubjectArray);

                                        $countReligionArray = self::countReligion($tblStudent, $tblLevel,
                                            $countReligionArray);

                                        if (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())) {
                                            $countOrientationArray = self::countOrientation($tblStudent, $tblLevel,
                                                $gender, $countOrientationArray);
                                        }

                                        if ($tblTransferTypeProcess
                                            && ($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferTypeProcess))
                                        ) {
                                            $tblCourse = $tblTransfer->getServiceTblCourse();
                                        }

                                        self::countDivisionStudentsForSecondarySchool($countDivisionStudentArrayForSecondarySchool,
                                            $tblDivision, $tblLevel, $tblCourse ? $tblCourse : null, $gender, $isInPreparationDivisionForMigrants);

                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }

                                    self::setRepeatersOs($tblPerson, $tblLevel, $tblDivision, $Content, $gender);

                                    self::setStudentFocus($tblPerson, $tblLevel, $Content, $gender,
                                        $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants);

                                    // get last Level
                                    self::setSchoolTypeLastYear($Content, $tblPastYearList, $tblPerson, $tblCourse,
                                        $tblLevel, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType);
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray,
                $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setOrientation($Content, $countOrientationArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
            self::setDivisionStudentsForSecondarySchool($Content, $countDivisionStudentArrayForSecondarySchool);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGsContent(
        $Content
    ) {

        $tblCurrentYearList = false;
        $tblPastYearList = false;
        $year = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Grundschule');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList, $year);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countSecondForeignSubjectArray = array();
            $countReligionArray = array();
            $countDivisionStudentArray = array();
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {
                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {

                                if (isset($Content['E01']['Division']['L' . $tblLevel->getName()])) {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()]++;
                                } else {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()] = 1;
                                }
                                if (isset($Content['E01']['Division']['TotalCount'])) {
                                    $Content['E01']['Division']['TotalCount']++;
                                } else {
                                    $Content['E01']['Division']['TotalCount'] = 1;
                                }

                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = count($tblPersonList);

                                foreach ($tblPersonList as $tblPerson) {

                                    $isInPreparationDivisionForMigrants = false;
                                    $tblStudent = $tblPerson->getStudent();

                                    if ($tblStudent
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if ($tblStudent
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    $birthDay = false;
                                    self::countStudentLevels($tblPerson, $tblLevel, $gender, $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                        $countMigrantsArray,
                                        $countMigrantsNationalityArray);

                                    self::setDivisionStudents($Content, $tblPerson, $tblLevel, $tblDivision, $gender,
                                        $isInPreparationDivisionForMigrants, $tblKamenzSchoolType);

                                    if ($tblStudent) {
                                        self::countForeignLanguages($tblStudent, $tblLevel, $tblKamenzSchoolType, $Content, $gender,
                                            $isInPreparationDivisionForMigrants, $countForeignSubjectArray,
                                            $countSecondForeignSubjectArray);

                                        $countReligionArray = self::countReligion($tblStudent, $tblLevel,
                                            $countReligionArray);

                                        // Schulanfänger
                                        self::setNewSchoolStarter(
                                            $Content,
                                            $tblPerson,
                                            $tblStudent,
                                            $tblLevel,
                                            $tblDivision,
                                            $gender,
                                            $birthDay,
                                            $year
                                        );

                                        self::setStudentFocus($tblPerson, $tblLevel, $Content, $gender,
                                            $hasMigrationBackground,
                                            $isInPreparationDivisionForMigrants);
                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray,
                $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
        }

        return $Content;
    }

    /**
     * @param array $Content
     *
     * @return array
     */
    public static function setKamenzReportGymContent(
        $Content
    ) {

        $tblCurrentYearList = false;
        $tblPastYearList = false;

        $tblKamenzSchoolType = Type::useService()->getTypeByName('Gymnasium');

        self::setYears($Content, $tblCurrentYearList, $tblPastYearList);

        self::setRepeatersFromCertificates($Content, $tblPastYearList, $tblKamenzSchoolType);

        /**
         * B
         */
        self::setGraduate($tblPastYearList, $Content, $tblKamenzSchoolType);

        if ($tblCurrentYearList) {
            $countArray = array();
            $countMigrantsArray = array();
            $countMigrantsNationalityArray = array();
            $countForeignSubjectArray = array();
            $countForeignSubjectMatrix = array();
            $countSecondForeignSubjectArray = array();
            $countProfileArray = array();
            $countReligionArray = array();
            $countDivisionStudentArray = array();
            $countAdvancedCourseArray = array();
            $countBasisCourseArray = array();
            $personAdvancedCourseList = array();
            /** @var TblYear[] $tblCurrentYearList */
            foreach ($tblCurrentYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    foreach ($tblDivisionList as $tblDivision) {
                        if (($tblLevel = $tblDivision->getTblLevel())
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                            && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        ) {

                            self::countCourses($tblLevel, $tblDivision, $countAdvancedCourseArray,
                                $countBasisCourseArray, $personAdvancedCourseList);

                            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {

                                if (isset($Content['E01']['Division']['L' . $tblLevel->getName()])) {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()]++;
                                } else {
                                    $Content['E01']['Division']['L' . $tblLevel->getName()] = 1;
                                }
                                if (intval($tblLevel->getName()) < 11) {
                                    if (isset($Content['E01']['Division']['TotalCount'])) {
                                        $Content['E01']['Division']['TotalCount']++;
                                    } else {
                                        $Content['E01']['Division']['TotalCount'] = 1;
                                    }
                                }

                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = count($tblPersonList);

                                foreach ($tblPersonList as $tblPerson) {

                                    $isInPreparationDivisionForMigrants = false;
                                    $tblStudent = $tblPerson->getStudent();

                                    if ($tblStudent
                                        && $tblStudent->isInPreparationDivisionForMigrants()
                                    ) {
                                        $isInPreparationDivisionForMigrants = true;
                                    }

                                    $hasMigrationBackground = false;
                                    if ($tblStudent
                                        && $tblStudent->getHasMigrationBackground()
                                    ) {
                                        $hasMigrationBackground = true;
                                    }

                                    $gender = false;
                                    $birthDay = false;
                                    self::countStudentLevels($tblPerson, $tblLevel, $gender, $hasMigrationBackground,
                                        $isInPreparationDivisionForMigrants, $birthDay, $countArray,
                                        $countMigrantsArray,
                                        $countMigrantsNationalityArray);

                                    self::setDivisionStudents($Content, $tblPerson, $tblLevel, $tblDivision, $gender,
                                        $isInPreparationDivisionForMigrants, $tblKamenzSchoolType);

                                    self::setRepeatersGym($tblPerson, $tblLevel, $tblDivision, $Content, $gender);

                                    if ($tblStudent) {
                                        self::countForeignLanguages($tblStudent, $tblLevel, $tblKamenzSchoolType, $Content, $gender,
                                            $isInPreparationDivisionForMigrants, $countForeignSubjectArray,
                                            $countSecondForeignSubjectArray);

                                        $countReligionArray = self::countReligion($tblStudent, $tblLevel,
                                            $countReligionArray);

                                        $countProfileArray = self::countProfile($tblStudent, $tblLevel, $gender,
                                            $countProfileArray);

                                        self::setStudentFocus($tblPerson, $tblLevel, $Content, $gender,
                                            $hasMigrationBackground,
                                            $isInPreparationDivisionForMigrants);

                                        if (intval($tblLevel->getName()) < 11) {
                                            $countForeignSubjectMatrix = self::countForeignLanguagesMatrix($tblPerson,
                                                $tblLevel, $countForeignSubjectMatrix);
                                        }

                                    } else {
                                        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
                                        } else {
                                            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
                                        }
                                    }

                                    // get last Level
                                    self::setSchoolTypeLastYear($Content, $tblPastYearList, $tblPerson, null,
                                        $tblLevel, $gender, $isInPreparationDivisionForMigrants, $tblKamenzSchoolType);
                                }
                            } else {
                                $countDivisionStudentArray[$tblDivision->getId()][$tblLevel->getName()] = 0;
                            }
                        }
                    }
                }
            }

            self::setStudentLevels($Content, $countArray, $countMigrantsArray, $countMigrantsNationalityArray);
            self::setForeignLanguages($Content, $countForeignSubjectArray, $countSecondForeignSubjectArray,
                $tblKamenzSchoolType);
            self::setReligion($Content, $countReligionArray);
            self::setProfile($Content, $countProfileArray);
            self::setCourses($Content, $countAdvancedCourseArray, $countBasisCourseArray);
            self::setDivisionFrequency($Content, $countDivisionStudentArray);
            self::setForeignLanguagesMatrix($Content, $countForeignSubjectMatrix);
            self::setCoursesMatrix($Content, $personAdvancedCourseList);
        }

        return $Content;
    }

    /**
     * @param $Content
     * @param $tblCurrentYearList
     * @param $tblPastYearList
     * @param bool $currentYear
     */
    private static function setYears(
        &$Content,
        &$tblCurrentYearList,
        &$tblPastYearList,
        &$currentYear = false
    ) {
        // SchoolYears
        if (($tblCurrentYearList = Term::useService()->getYearByNow())) {
            $tblCurrentYear = reset($tblCurrentYearList);
            $currentYearName = $tblCurrentYear->getName();

            if ($currentYearName
                && ($pos = strpos($currentYearName, '/'))
            ) {
                $year[0] = substr($currentYearName, 0, $pos);
                $year[1] = substr($currentYearName, $pos + 1);

                $currentYear = $year[0];

                $pastYearName = (string)($year[0] - 1) . '/' . (string)($year[1] - 1);
                $tblPastYearList = Term::useService()->getYearByName($pastYearName);
                $Content['SchoolYear']['Current'] = $currentYearName;
                $Content['SchoolYear']['Past'] = $pastYearName;
                $Content['Year']['Current'] = $currentYear;
            }
        }
    }

    /**
     * @param $Content
     * @param $tblPastYearList
     * @param TblType $tblKamenzSchoolType
     */
    private static function setRepeatersFromCertificates(&$Content, $tblPastYearList, TblType $tblKamenzSchoolType)
    {

        if ($tblPastYearList) {
            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblGeneratePrepareList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGeneratePrepareList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'YEAR' || $tblCertificateType->getIdentifier() == 'DIPLOMA')
                            && ($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))
                        ) {
                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                                    && ($tblLevel = $tblDivision->getTblLevel())
                                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                                    && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
                                ) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if ($tblPrepareStudent->isPrinted()
                                            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ((($tblPrepareInformationTransfer = Prepare::useService()->getPrepareInformationBy(
                                                        $tblPrepare, $tblPerson, 'Transfer'
                                                    ))
                                                    && (strpos($tblPrepareInformationTransfer->getValue(),
                                                            'nicht versetzt') !== false))
                                                || (($tblPrepareInformationIndividualTransfer = Prepare::useService()->getPrepareInformationBy(
                                                        $tblPrepare, $tblPerson, 'IndividualTransfer'
                                                    ))
                                                    && (strpos($tblPrepareInformationIndividualTransfer->getValue(),
                                                            'nicht versetzt') !== false))
                                            )
                                        ) {

                                            $gender = 'x';
                                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                                && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                                                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                                && ($birthDay = $tblCommonBirthDates->getBirthday())
                                            ) {

                                                if ($tblCommonGender->getName() == 'Männlich') {
                                                    $gender = 'm';
                                                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                                                    $gender = 'w';
                                                }
                                            }

                                            if ($tblKamenzSchoolType->getName() == 'Mittelschule / Oberschule') {
                                                $course = 'NoCourse';
                                                if ($tblLevel->getName() == '5' || $tblLevel->getName() == '6') {

                                                } else {
                                                    if (($tblStudent = $tblPerson->getStudent())
                                                        && ($tblCourse = $tblStudent->getCourse())
                                                    ) {
                                                        if ($tblCourse->getName() == 'Hauptschule') {
                                                            $course = 'HS';
                                                        } elseif ($tblCourse->getName() == 'Realschule') {
                                                            $course = 'RS';
                                                        }
                                                    }
                                                }

                                                if (isset($Content['C01'][$course]['L' . $tblLevel->getName()][$gender])) {
                                                    $Content['C01'][$course]['L' . $tblLevel->getName()][$gender]++;
                                                } else {
                                                    $Content['C01'][$course]['L' . $tblLevel->getName()][$gender] = 1;
                                                }

                                                if (isset($Content['C01'][$course]['TotalCount'][$gender])) {
                                                    $Content['C01'][$course]['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['C01'][$course]['TotalCount'][$gender] = 1;
                                                }

                                            } else {
                                                if (isset($Content['C01']['L' . $tblLevel->getName()][$gender])) {
                                                    $Content['C01']['L' . $tblLevel->getName()][$gender]++;
                                                } else {
                                                    $Content['C01']['L' . $tblLevel->getName()][$gender] = 1;
                                                }

                                                if (isset($Content['C01']['TotalCount'][$gender])) {
                                                    $Content['C01']['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['C01']['TotalCount'][$gender] = 1;
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
        }
    }

    /**
     * F01. Integrierte Schüler mit sonderpädagogischem Förderbedarf im Schuljahr 2016/17 nach
     * Förderschwerpunkten und Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param $Content
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     */
    private static function setStudentFocus(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        &$Content,
        $gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants
    ) {

        if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
            && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
            && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
        ) {

            $name = preg_replace('/[^a-zA-Z]/', '', $tblSupportFocusType->getName());

            /**
             * Schüler
             */
            if (isset($Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender])) {
                $Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['F01'][$name]['Student']['L' . $tblLevel->getName()][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender])) {
                    $Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender]++;
                } else {
                    $Content['F01'][$name]['Student']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                }
            }
            if (isset($Content['F01'][$name]['Student']['TotalCount'][$gender])) {
                $Content['F01'][$name]['Student']['TotalCount'][$gender]++;
            } else {
                $Content['F01'][$name]['Student']['TotalCount'][$gender] = 1;
            }
            if (isset($Content['F01']['TotalCount']['Student']['TotalCount'][$gender])) {
                $Content['F01']['TotalCount']['Student']['TotalCount'][$gender]++;
            } else {
                $Content['F01']['TotalCount']['Student']['TotalCount'][$gender] = 1;
            }

            /**
             * Schüler mit Migrationshintergrund
             */
            if ($hasMigrationBackground) {
                if (isset($Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender])) {
                    $Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender]++;
                } else {
                    $Content['F01'][$name]['HasMigrationBackground']['L' . $tblLevel->getName()][$gender] = 1;
                }
                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender])) {
                        $Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender]++;
                    } else {
                        $Content['F01'][$name]['HasMigrationBackground']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                    }
                }
                if (isset($Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content['F01'][$name]['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
                if (isset($Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender])) {
                    $Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender]++;
                } else {
                    $Content['F01']['TotalCount']['HasMigrationBackground']['TotalCount'][$gender] = 1;
                }
            }

            /**
             * Schüler mit gutachterl. best. Autismus
             */
            if (($tblSpecialList = Student::useService()->getSpecialByPerson($tblPerson))) {

                $hasAutism = false;
                foreach ($tblSpecialList as $tblSpecial) {
                    if (($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
                        foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                            if ($tblSpecialDisorderType->getName() == 'Störungen aus dem Autismusspektrum') {
                                $hasAutism = true;
                                break;
                            }
                        }
                    }

                    if ($hasAutism) {
                        break;
                    }
                }

                if ($hasAutism) {
                    if (isset($Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender])) {
                        $Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender]++;
                    } else {
                        $Content['F01'][$name]['Autism']['L' . $tblLevel->getName()][$gender] = 1;
                    }
                    if ($isInPreparationDivisionForMigrants) {
                        if (isset($Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender])) {
                            $Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender]++;
                        } else {
                            $Content['F01'][$name]['Autism']['IsInPreparationDivisionForMigrants'][$gender] = 1;
                        }
                    }
                    if (isset($Content['F01'][$name]['Autism']['TotalCount'][$gender])) {
                        $Content['F01'][$name]['Autism']['TotalCount'][$gender]++;
                    } else {
                        $Content['F01'][$name]['Autism']['TotalCount'][$gender] = 1;
                    }
                    if (isset($Content['F01']['TotalCount']['Autism']['TotalCount'][$gender])) {
                        $Content['F01']['TotalCount']['Autism']['TotalCount'][$gender]++;
                    } else {
                        $Content['F01']['TotalCount']['Autism']['TotalCount'][$gender] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param TblType $tblType
     * @param $Content
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     */
    private static function countForeignLanguages(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        TblType $tblType,
        &$Content,
        $gender,
        $isInPreparationDivisionForMigrants,
        &$countForeignSubjectArray,
        &$countSecondForeignSubjectArray
    ) {
        /**
         * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
         */
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))) {
            if ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            )
            ) {
                $countForeignSubjectsByStudent = 0;
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                    ) {
                        // #SSW-1596 Abgeschlossene und noch nicht begonne Fremdsprachen ignorieren
                        if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                            && intval($tblLevelFrom->getName()) > intval($tblLevel->getName())
                        ) {
                            continue;
                        }
                        if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())
                            && intval($tblLevelTill->getName()) < intval($tblLevel->getName())
                        ) {
                            continue;
                        }

                        $countForeignSubjectsByStudent++;

                        if ($tblType->getName() == 'Mittelschule / Oberschule') {
                            // bei Mittelschule nur 1. Fremdsprache
                            if ($tblStudentSubjectRanking->getIdentifier() == 1) {
                                if (isset($countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()])) {
                                    $countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()]++;
                                } else {
                                    $countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()] = 1;
                                }
                            }
                        } else {
                            if (intval($tblLevel->getName()) < 11) {
                                if (isset($countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()])) {
                                    $countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()]++;
                                } else {
                                    $countForeignSubjectArray[$tblSubject->getName()][$tblLevel->getName()] = 1;
                                }
                            }
                        }

                        if ($tblStudentSubjectRanking->getIdentifier() == 2) {
                            /**
                             * E11. Schüler in der zweiten FREMDSPRACHE - abschlussorientiert im Schuljahr nach Klassenstufen
                             */
                            if ($gender) {
                                if (isset($countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender])) {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender]++;
                                } else {
                                    $countSecondForeignSubjectArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] = 1;
                                }
                            }
                        }
                    }
                }
            } else {
                $countForeignSubjectsByStudent = 0;
            }

            /**
             * E04.1 Schüler im Schuljahr nach der Anzahl der derzeit erlernten Fremdsprachen und Klassenstufen
             */
            if ($countForeignSubjectsByStudent > 4) {
                $countForeignSubjectsByStudent = 4;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()]++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['L' . $tblLevel->getName()] = 1;
            }
            if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'])) {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount']++;
            } else {
                $Content['E04_1']['F' . $countForeignSubjectsByStudent]['TotalCount'] = 1;
            }
            if (isset($Content['E04_1']['TotalCount']['L' . $tblLevel->getName()])) {
                $Content['E04_1']['TotalCount']['L' . $tblLevel->getName()]++;
            } else {
                $Content['E04_1']['TotalCount']['L' . $tblLevel->getName()] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration'])) {
                    $Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration']++;
                } else {
                    $Content['E04_1']['F' . $countForeignSubjectsByStudent]['Migration'] = 1;
                }

                if (isset($Content['E04_1']['TotalCount']['Migration'])) {
                    $Content['E04_1']['TotalCount']['Migration']++;
                } else {
                    $Content['E04_1']['TotalCount']['Migration'] = 1;
                }
            }
        }
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
     * @param TblLevel $tblLevel
     * @param $countForeignSubjectMatrix
     *
     * @return array
     */
    private static function countForeignLanguagesMatrix(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        $countForeignSubjectMatrix
    ) {
        /**
         * E15. Schüler in Sprachenfolgen im Schuljahr nach Klassenstufen
         */
        $subjects = self::getForeignLanguages($tblPerson);
        if (!empty($subjects)) {
            $identifier = '';
            ksort($subjects);
            foreach ($subjects as $ranking => $acronym) {
                $identifier .= $acronym;
            }

            $count = count($subjects);
            if (!isset($countForeignSubjectMatrix[$count][$identifier])) {
                foreach ($subjects as $ranking => $acronym) {
                    $countForeignSubjectMatrix[$count][$identifier]['Subjects'][$ranking] = $acronym;
                }
            }

            if (isset($countForeignSubjectMatrix[$count][$identifier]['Levels'][$tblLevel->getName()])) {
                $countForeignSubjectMatrix[$count][$identifier]['Levels'][$tblLevel->getName()]++;
            } else {
                $countForeignSubjectMatrix[$count][$identifier]['Levels'][$tblLevel->getName()] = 1;
            }
        }

        return $countForeignSubjectMatrix;
    }

    /**
     * @param $Content
     * @param $countForeignSubjectArray
     * @param $countSecondForeignSubjectArray
     * @param TblType $tblSchoolType
     */
    private static function setForeignLanguages(
        &$Content,
        $countForeignSubjectArray,
        $countSecondForeignSubjectArray,
        TblType $tblSchoolType
    ) {
        /**
         * E04 Schüler mit der ersten Fremdsprache im Schuljahr nach Klassenstufen
         */
        ksort($countForeignSubjectArray);
        $count = 0;
        foreach ($countForeignSubjectArray as $subjectName => $levelArray) {
            $Content['E04']['S' . $count]['TotalCount'] = 0;
            $Content['E04']['S' . $count]['SubjectName'] = $subjectName;
            foreach ($levelArray as $level => $value) {
                $Content['E04']['S' . $count]['L' . $level] = $value;
                $Content['E04']['S' . $count]['TotalCount'] += $value;
            }

            $count++;
        }

        if ($tblSchoolType->getName() == 'Mittelschule / Oberschule') {
            /**
             * E11 Schüler in der zweiten FREMDSPRACHE - abschlussorientiert im Schuljahr nach Klassenstufen
             */
            ksort($countSecondForeignSubjectArray);
            $count = 0;
            foreach ($countSecondForeignSubjectArray as $acronym => $levelArray) {
                $Content['E11']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                    ? $tblSubject->getName() : '';
                foreach ($levelArray as $level => $genderArray) {
                    foreach ($genderArray as $gender => $value) {
                        $Content['E11']['S' . $count]['L' . $level][$gender] = $value;

                        if (isset($Content['E11']['TotalCount']['L' . $level][$gender])) {
                            $Content['E11']['TotalCount']['L' . $level][$gender] += $value;
                        } else {
                            $Content['E11']['TotalCount']['L' . $level][$gender] = $value;
                        }

                        if (isset($Content['E11']['S' . $count]['TotalCount'][$gender])) {
                            $Content['E11']['S' . $count]['TotalCount'][$gender] += $value;
                        } else {
                            $Content['E11']['S' . $count]['TotalCount'][$gender] = $value;
                        }
                    }
                }

                $count++;
            }
        }
    }

    /**
     * @param $Content
     * @param $countForeignSubjectMatrix
     */
    private static function setForeignLanguagesMatrix(
        &$Content,
        $countForeignSubjectMatrix
    ) {
        /**
         * E15. Schüler in Sprachenfolgen im Schuljahr nach Klassenstufen
         */
        ksort($countForeignSubjectMatrix);
        $count = 0;
        foreach ($countForeignSubjectMatrix as $counter => $identifierArray) {
            ksort($identifierArray);
            foreach ($identifierArray as $identifier => $array) {
                if (isset($array['Subjects'])) {
                    foreach ($array['Subjects'] as $ranking => $acronym) {
                        $Content['E15']['S' . $count]['N' . $ranking] =
                            ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                                ? $tblSubject->getName()
                                : '';
                    }
                }
                if (isset($array['Levels'])) {
                    foreach ($array['Levels'] as $level => $value) {
                        $Content['E15']['S' . $count]['L' . $level] = $value;

                        if (isset($Content['E15']['TotalCount']['L' . $level])) {
                            $Content['E15']['TotalCount']['L' . $level] += $value;
                        } else {
                            $Content['E15']['TotalCount']['L' . $level] = $value;
                        }
                    }
                }

                $count++;
            }
        }
    }

    /**
     * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $countReligionArray
     *
     * @return array
     */
    private static function countReligion(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        $countReligionArray
    ) {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {

            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                if (isset($countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()])) {
                    $countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()]++;
                } else {
                    $countReligionArray[$tblSubject->getAcronym()][$tblLevel->getName()] = 1;
                }
            }

            return $countReligionArray;
        }

        if (isset($countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()])) {
            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()]++;
        } else {
            $countReligionArray['ZZ_Keine_Teilnahme'][$tblLevel->getName()] = 1;
        }

        return $countReligionArray;
    }

    /**
     * @param $Content
     * @param $countReligionArray
     */
    private static function setReligion(
        &$Content,
        $countReligionArray
    ) {
        /**
         * E05 Schüler im Ethik- bzw. Religionsunterricht im Schuljahr nach Klassenstufen
         */
        ksort($countReligionArray);
        $count = 0;
        foreach ($countReligionArray as $acronym => $levelArray) {
            $Content['E05']['S' . $count]['TotalCount'] = 0;
            $Content['E05']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : 'Keine Teilnahme';
            foreach ($levelArray as $level => $value) {
                if (intval($level) < 11) {
                    $Content['E05']['S' . $count]['L' . $level] = $value;
                    $Content['E05']['S' . $count]['TotalCount'] += $value;
                    if (isset($Content['E05']['TotalCount']['L' . $level])) {
                        $Content['E05']['TotalCount']['L' . $level] += $value;
                    } else {
                        $Content['E05']['TotalCount']['L' . $level] = $value;
                    }
                }
            }

            $count++;
        }
    }

    /**
     * E08. Wiederholer im Schuljahr 2016/17 nach Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $Content
     * @param $gender
     */
    private static function setRepeatersOs(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        &$Content,
        $gender
    ) {

        if (($tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            /**@var TblDivisionStudent $tblDivisionStudent * */
            foreach ($tblDivisionStudentAllByPerson as $tblDivisionStudent) {
                if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                    && $tblDivisionTemp->getId() != $tblDivision->getId()
                    && ($tblTempLevel = $tblDivisionTemp->getTblLevel())
                    && $tblLevel->getId() == $tblTempLevel->getId()
                ) {
                    if ($gender) {
                        if (isset($Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender])) {
                            $Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender]++;
                        } else {
                            $Content['E08']['WithoutCourse']['L' . $tblLevel->getName()][$gender] = 1;
                        }

                        if (isset($Content['E08']['WithoutCourse']['TotalCount'][$gender])) {
                            $Content['E08']['WithoutCourse']['TotalCount'][$gender]++;
                        } else {
                            $Content['E08']['WithoutCourse']['TotalCount'][$gender] = 1;
                        }
                    }

                    break;
                }
            }
        }
    }

    /**
     * E08. Wiederholer im Schuljahr 2016/17 nach Klassenstufen
     *
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $Content
     * @param $gender
     */
    private static function setRepeatersGym(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        &$Content,
        $gender
    ) {

        if (($tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            /**@var TblDivisionStudent $tblDivisionStudent * */
            foreach ($tblDivisionStudentAllByPerson as $tblDivisionStudent) {
                if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                    && $tblDivisionTemp->getId() != $tblDivision->getId()
                    && ($tblTempLevel = $tblDivisionTemp->getTblLevel())
                    && $tblLevel->getId() == $tblTempLevel->getId()
                ) {
                    if ($gender) {
                        if (isset($Content['E08']['L' . $tblLevel->getName()][$gender])) {
                            $Content['E08']['L' . $tblLevel->getName()][$gender]++;
                        } else {
                            $Content['E08']['L' . $tblLevel->getName()][$gender] = 1;
                        }

                        if (isset($Content['E08']['TotalCount'][$gender])) {
                            $Content['E08']['TotalCount'][$gender]++;
                        } else {
                            $Content['E08']['TotalCount'][$gender] = 1;
                        }
                    }

                    break;
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    private static function hasRepeaters(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision
    ) {

        if (($tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            /**@var TblDivisionStudent $tblDivisionStudent * */
            foreach ($tblDivisionStudentAllByPerson as $tblDivisionStudent) {
                if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                    && $tblDivisionTemp->getId() != $tblDivision->getId()
                    && ($tblTempLevel = $tblDivisionTemp->getTblLevel())
                    && $tblLevel->getId() == $tblTempLevel->getId()
                ) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
     *
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $countOrientationArray
     *
     * @return array
     */
    private static function countOrientation(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        $gender,
        $countOrientationArray
    ) {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {

                if ($gender) {
                    $name = '';
                    if (($startPos = strpos($tblSubject->getName(), '(')) !== false
                        && ($endPos = strpos($tblSubject->getName(), ')')) !== false
                    ) {
                        $name = substr($tblSubject->getName(), $startPos + 1, $endPos - ($startPos + 1));
                    }

                    if ($name != '') {
                        if (isset($countOrientationArray[$name][$tblLevel->getName()][$gender])) {
                            $countOrientationArray[$name][$tblLevel->getName()][$gender]++;
                        } else {
                            $countOrientationArray[$name][$tblLevel->getName()][$gender] = 1;
                        }
                    }
                }
            }
        }

        return $countOrientationArray;
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $countProfileArray
     *
     * @return array
     */
    private static function countProfile(
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        $gender,
        $countProfileArray
    ) {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                if (intval($tblLevel->getName()) < 11 && $gender) {
                    if (isset($countProfileArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender])) {
                        $countProfileArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender]++;
                    } else {
                        $countProfileArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] = 1;
                    }
                }
            }
        }

        return $countProfileArray;
    }

    /**
     * @param $Content
     * @param $countOrientationArray
     */
    private static function setOrientation(
        &$Content,
        $countOrientationArray
    ) {
        /**
         * E12 Schüler im NEIGUNGSKURSBEREICH im Schuljahr nach Klassenstufen
         */
        ksort($countOrientationArray);
        $count = 0;
        foreach ($countOrientationArray as $name => $levelArray) {
            $Content['E12']['S' . $count]['SubjectName'] = $name;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {

                    $Content['E12']['S' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E12']['TotalCount']['L' . $level][$gender])) {
                        $Content['E12']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E12']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    if (isset($Content['E12']['S' . $count]['TotalCount'][$gender])) {
                        $Content['E12']['S' . $count]['TotalCount'][$gender] += $value;
                    } else {
                        $Content['E12']['S' . $count]['TotalCount'][$gender] = $value;
                    }
                }
            }

            $count++;
        }
    }

    /**
     * @param $Content
     * @param $countProfileArray
     */
    private static function setProfile(
        &$Content,
        $countProfileArray
    ) {

        ksort($countProfileArray);
        $countLanguageProfile = -1;
        $countOthers = -1;
        foreach ($countProfileArray as $acronym => $levelArray) {
            if (($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))) {
                $name = $tblSubject->getName();
                if (strpos(strtolower($name), 'sprach') !== false) {
                    $countLanguageProfile++;
                    $count = $countLanguageProfile;
                    $section = 'E11';
                } else {
                    $countOthers++;
                    $count = $countOthers;
                    $section = 'E12';
                }

                $Content[$section]['S' . $count]['SubjectName'] = $name;
                foreach ($levelArray as $level => $genderArray) {
                    foreach ($genderArray as $gender => $value) {

                        $Content[$section]['S' . $count]['L' . $level][$gender] = $value;

                        if (isset($Content[$section]['TotalCount']['L' . $level][$gender])) {
                            $Content[$section]['TotalCount']['L' . $level][$gender] += $value;
                        } else {
                            $Content[$section]['TotalCount']['L' . $level][$gender] = $value;
                        }

                        if (isset($Content[$section]['S' . $count]['TotalCount'][$gender])) {
                            $Content[$section]['S' . $count]['TotalCount'][$gender] += $value;
                        } else {
                            $Content[$section]['S' . $count]['TotalCount'][$gender] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $hasMigrationBackground
     * @param $isInPreparationDivisionForMigrants
     * @param $birthDay
     * @param $countArray
     * @param $countMigrantsArray
     * @param $countMigrantsNationalityArray
     */
    private static function countStudentLevels(
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        &$gender,
        $hasMigrationBackground,
        $isInPreparationDivisionForMigrants,
        &$birthDay,
        &$countArray,
        &$countMigrantsArray,
        &$countMigrantsNationalityArray
    ) {
        /**
         * E02  Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
            if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                $nationality = $tblCommonInformation->getNationality();
            } else {
                $nationality = false;
            }

            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
            ) {

                if ($tblCommonGender->getName() == 'Männlich') {
                    $gender = 'm';
                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                    $gender = 'w';
                } else {
                    $gender = 'x';
                }

                if (($birthDay = $tblCommonBirthDates->getBirthday())) {
                    $birthDayDate = new \DateTime($birthDay);
                    if ($birthDayDate) {
                        $birthYear = $birthDayDate->format('Y');

                        if (isset($countArray[$birthYear][$tblLevel->getName()][$gender])) {
                            $countArray[$birthYear][$tblLevel->getName()][$gender]++;
                        } else {
                            $countArray[$birthYear][$tblLevel->getName()][$gender] = 1;
                        }

                        if ($isInPreparationDivisionForMigrants) {
                            if (isset($countArray[$birthYear]['Migration'][$gender])) {
                                $countArray[$birthYear]['Migration'][$gender]++;
                            } else {
                                $countArray[$birthYear]['Migration'][$gender] = 1;
                            }
                        }

                        /**
                         * E02.1 Darunter Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach Geburtsjahren und Klassenstufen
                         */
                        if ($hasMigrationBackground) {
                            if (isset($countMigrantsArray[$birthYear][$tblLevel->getName()][$gender])) {
                                $countMigrantsArray[$birthYear][$tblLevel->getName()][$gender]++;
                            } else {
                                $countMigrantsArray[$birthYear][$tblLevel->getName()][$gender] = 1;
                            }

                            /**
                             * E03. Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach dem Land der Staatsangehörigkeit und Klassenstufen
                             */
                            if ($nationality) {
                                if (isset($countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender])) {
                                    $countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender]++;
                                } else {
                                    $countMigrantsNationalityArray[$nationality][$tblLevel->getName()][$gender] = 1;
                                }
                            }

                            if ($isInPreparationDivisionForMigrants) {
                                if (isset($countMigrantsArray[$birthYear]['Migration'][$gender])) {
                                    $countMigrantsArray[$birthYear]['Migration'][$gender]++;
                                } else {
                                    $countMigrantsArray[$birthYear]['Migration'][$gender] = 1;
                                }

                                if ($nationality) {
                                    if (isset($countMigrantsNationalityArray[$nationality]['Migration'][$gender])) {
                                        $countMigrantsNationalityArray[$nationality]['Migration'][$gender]++;
                                    } else {
                                        $countMigrantsNationalityArray[$nationality]['Migration'][$gender] = 1;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $countArray
     * @param $countMigrantsArray
     * @param $countMigrantsNationalityArray
     */
    private static function setStudentLevels(
        &$Content,
        $countArray,
        $countMigrantsArray,
        $countMigrantsNationalityArray
    ) {
        /**
         * E02  Schüler im Schuljahr 2016/2017 nach Geburtsjahren und Klassenstufen
         */
        ksort($countArray);
        $count = 0;
        $Content['E02']['TotalCount']['m'] = 0;
        $Content['E02']['TotalCount']['w'] = 0;
        foreach ($countArray as $year => $levelArray) {
            $Content['E02']['Y' . $count]['YearName'] = $year;
            $Content['E02']['Y' . $count]['m'] = 0;
            $Content['E02']['Y' . $count]['w'] = 0;
            $Content['E02']['Y' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E02']['Y' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E02']['TotalCount']['L' . $level][$gender])) {
                        $Content['E02']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E02']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E02']['Y' . $count][$gender] += $value;
                    $Content['E02']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }

        /**
         * E02.1 Darunter Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach Geburtsjahren und Klassenstufen
         */
        ksort($countMigrantsArray);
        $count = 0;
        $Content['E02_1']['TotalCount']['m'] = 0;
        $Content['E02_1']['TotalCount']['w'] = 0;
        foreach ($countMigrantsArray as $year => $levelArray) {
            $Content['E02_1']['Y' . $count]['YearName'] = $year;
            $Content['E02_1']['Y' . $count]['m'] = 0;
            $Content['E02_1']['Y' . $count]['w'] = 0;
            $Content['E02_1']['Y' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E02_1']['Y' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E02_1']['TotalCount']['L' . $level][$gender])) {
                        $Content['E02_1']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E02_1']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E02_1']['Y' . $count][$gender] += $value;
                    $Content['E02_1']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }

        /**
         * E03. Schüler mit Migrationshintergrund im Schuljahr 2016/17 nach dem Land der Staatsangehörigkeit und Klassenstufen
         */
        ksort($countMigrantsNationalityArray);
        $count = 0;
        $Content['E03']['TotalCount']['m'] = 0;
        $Content['E03']['TotalCount']['w'] = 0;
        foreach ($countMigrantsNationalityArray as $nation => $levelArray) {
            $Content['E03']['N' . $count]['NationalityName'] = $nation;
            $Content['E03']['N' . $count]['m'] = 0;
            $Content['E03']['N' . $count]['w'] = 0;
            $Content['E03']['N' . $count]['x'] = 0;
            foreach ($levelArray as $level => $genderArray) {
                foreach ($genderArray as $gender => $value) {
                    $Content['E03']['N' . $count]['L' . $level][$gender] = $value;

                    if (isset($Content['E03']['TotalCount']['L' . $level][$gender])) {
                        $Content['E03']['TotalCount']['L' . $level][$gender] += $value;
                    } else {
                        $Content['E03']['TotalCount']['L' . $level][$gender] = $value;
                    }

                    $Content['E03']['N' . $count][$gender] += $value;
                    $Content['E03']['TotalCount'][$gender] += $value;
                }
            }

            $count++;
        }
    }

    /**
     * @param $Content
     * @param $countDivisionStudentArray
     */
    private static function setDivisionFrequency(
        &$Content,
        $countDivisionStudentArray
    ) {
        /**
         * G01. Klassenfrequenz im Schuljahr zum Stichtag 02. September
         */
        $levelCount = array();
        foreach ($countDivisionStudentArray as $levelArray) {
            foreach ($levelArray as $level => $value) {
                if (isset($levelCount[$level])) {
                    $levelCount[$level]++;
                } else {
                    $levelCount[$level] = 1;
                }
                $Content['G01']['D' . $levelCount[$level]]['L' . $level] = $value;

                if (isset($Content['G01']['L' . $level]['TotalCount'])) {
                    $Content['G01']['L' . $level]['TotalCount'] += $value;
                } else {
                    $Content['G01']['L' . $level]['TotalCount'] = $value;
                }
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param TblStudent $tblStudent
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $gender
     * @param $birthDay
     * @param $year
     */
    private static function setNewSchoolStarter(
        &$Content,
        TblPerson $tblPerson,
        TblStudent $tblStudent,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        $gender,
        $birthDay,
        $year
    ) {
        if ($tblLevel->getName() == '1' || $tblLevel->getName() == '01') {
            if (!self::hasRepeaters($tblPerson, $tblLevel, $tblDivision)) {
                if (isset($Content['D01']['NewSchoolStarter'][$gender])) {
                    $Content['D01']['NewSchoolStarter'][$gender]++;
                } else {
                    $Content['D01']['NewSchoolStarter'][$gender] = 1;
                }

                if ($tblStudent) {
                    if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        ))
                        && ($tblArriveCompany = $tblStudentTransfer->getServiceTblCompany())
                        && ($tblGroup = Group::useService()->getGroupByMetaTable('NURSERY'))
                        && Group::useService()->existsGroupCompany($tblGroup,
                            $tblArriveCompany)
                    ) {
                        if (isset($Content['D01']['Nursery'][$gender])) {
                            $Content['D01']['Nursery'][$gender]++;
                        } else {
                            $Content['D01']['Nursery'][$gender] = 1;
                        }
                    }

                    if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT'))
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                            $tblStudent, $tblStudentTransferType
                        ))
                        && ($tblSchoolEnrollmentType = $tblStudentTransfer->getTblStudentSchoolEnrollmentType())
                    ) {
                        if ($tblSchoolEnrollmentType->getIdentifier() == 'PREMATURE') {
                            if (isset($Content['D01']['Premature'][$gender])) {
                                $Content['D01']['Premature'][$gender]++;
                            } else {
                                $Content['D01']['Premature'][$gender] = 1;
                            }
                        } elseif ($tblSchoolEnrollmentType->getIdentifier() == 'REGULAR') {
                            if (isset($Content['D01']['Regular']['Total'][$gender])) {
                                $Content['D01']['Regular']['Total'][$gender]++;
                            } else {
                                $Content['D01']['Regular']['Total'][$gender] = 1;
                            }

                            $date = false;
                            if ($tblStudent->getSchoolAttendanceStartDate()) {
                                $date = new \DateTime($tblStudent->getSchoolAttendanceStartDate());
                            } elseif ($birthDay) {
                                $date = new \DateTime($birthDay);
                                $date->add(new \DateInterval('P6Y'));
                            }

                            if ($date) {
                                if ($year <= $date->format('Y') && $date->format('m') > 6) {
                                    if (isset($Content['D01']['Regular']['Second'][$gender])) {
                                        $Content['D01']['Regular']['Second'][$gender]++;
                                    } else {
                                        $Content['D01']['Regular']['Second'][$gender] = 1;
                                    }
                                } else {
                                    if (isset($Content['D01']['Regular']['First'][$gender])) {
                                        $Content['D01']['Regular']['First'][$gender]++;
                                    } else {
                                        $Content['D01']['Regular']['First'][$gender] = 1;
                                    }
                                }
                            }
                        } elseif ($tblSchoolEnrollmentType->getIdentifier() == 'POSTPONED') {
                            if (isset($Content['D01']['Postponed'][$gender])) {
                                $Content['D01']['Postponed'][$gender]++;
                            } else {
                                $Content['D01']['Postponed'][$gender] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param TblPerson $tblPerson
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param TblType $tblSchoolType
     */
    private static function setDivisionStudents(
        &$Content,
        TblPerson $tblPerson,
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        $gender,
        $isInPreparationDivisionForMigrants,
        TblType $tblSchoolType
    ) {
        if ($gender) {
            if (isset($Content['E01']['Student']['L' . $tblLevel->getName()][$gender])) {
                $Content['E01']['Student']['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E01']['Student']['L' . $tblLevel->getName()][$gender] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E01']['Student']['Migration'][$gender])) {
                    $Content['E01']['Student']['Migration'][$gender]++;
                } else {
                    $Content['E01']['Student']['Migration'][$gender] = 1;
                }
            }

            if (isset($Content['E01']['Student']['TotalCount'][$gender])) {
                $Content['E01']['Student']['TotalCount'][$gender]++;
            } else {
                $Content['E01']['Student']['TotalCount'][$gender] = 1;
            }

            if ($tblSchoolType->getName() == 'Grundschule') {
                /**
                 * E07
                 */
                if (($tblLevel->getName() == '1' || $tblLevel->getName() == '01')
                    && !self::hasRepeaters($tblPerson, $tblLevel, $tblDivision)
                ) {
                    $identifier = 'NewSchoolStarter';
                } else {
                    $identifier = 'PrimarySchool';
                }

                if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                    $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
                } else {
                    $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
                }

                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                        $Content['E07'][$identifier]['Migration'][$gender]++;
                    } else {
                        $Content['E07'][$identifier]['Migration'][$gender] = 1;
                    }
                }

                if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                    $Content['E07'][$identifier]['TotalCount'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
                }

                /**
                 * TotalCount
                 */
                $identifier = 'TotalCount';
                if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                    $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
                } else {
                    $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
                }
                if ($isInPreparationDivisionForMigrants) {
                    if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                        $Content['E07'][$identifier]['Migration'][$gender]++;
                    } else {
                        $Content['E07'][$identifier]['Migration'][$gender] = 1;
                    }
                }
                if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                    $Content['E07'][$identifier]['TotalCount'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
                }
            }
        }
    }

    /**
     * @param $countDivisionStudents
     * @param TblDivision $tblDivision
     * @param TblLevel $tblLevel
     * @param TblCourse|null $tblCourse
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     */
    private static function countDivisionStudentsForSecondarySchool(
        &$countDivisionStudents,
        TblDivision $tblDivision,
        TblLevel $tblLevel,
        TblCourse $tblCourse = null,
        $gender,
        $isInPreparationDivisionForMigrants
    ) {
        if ($gender) {
            if ($tblLevel->getName() == '5' || $tblLevel->getName() == '6') {
                $course = 'NoCourse';
            }
            elseif ($tblCourse == null) {
                $course = 'NoCourse';
            } elseif ($tblCourse->getName() == 'Hauptschule') {
                $course = 'HS';
            } elseif ($tblCourse->getName() == 'Realschule') {
                $course = 'RS';
            } else {
                $course = 'NoCourse';
            }

            if (isset($countDivisionStudents['Division'][$tblDivision->getId()][$course][$gender])) {
                $countDivisionStudents['Division'][$tblDivision->getId()][$course][$gender]++;
            } else {
                $countDivisionStudents['Division'][$tblDivision->getId()][$course][$gender] = 1;
            }

            if ($isInPreparationDivisionForMigrants) {
                if (isset($countDivisionStudents['Migrants'][$tblDivision->getId()][$course][$gender])) {
                    $countDivisionStudents['Migrants'][$tblDivision->getId()][$course][$gender]++;
                } else {
                    $countDivisionStudents['Migrants'][$tblDivision->getId()][$course][$gender] = 1;
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $countDivisionStudents
     */
    private static function setDivisionStudentsForSecondarySchool(
        &$Content,
        $countDivisionStudents
    ) {

        if (isset($countDivisionStudents['Division'])) {
            foreach ($countDivisionStudents['Division'] as $divisionId => $courseArray) {
                if (($tblDivision = Division::useService()->getDivisionById($divisionId))
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($level = $tblLevel->getName())
                ) {
                    $courseString = 'Error';
                    if (isset($courseArray['RS']) && isset($courseArray['HS'])) {
                        $isMixed = true;
                    } else {
                        if (isset($courseArray['RS'])) {
                            $courseString = 'RS';
                        } elseif (isset($courseArray['HS'])) {
                            $courseString = 'HS';
                        } elseif (isset($courseArray['NoCourse'])) {
                            $courseString = 'NoCourse';
                        }
                        $isMixed = false;
                    }

                    foreach ($courseArray as $course => $genderArray) {
                        foreach ($genderArray as $gender => $count) {
                            if (isset( $Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['L' . $level][$gender])) {
                                $Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['L' . $level][$gender] += $count;
                            } else {
                                $Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['L' . $level][$gender] = $count;
                            }

                            if (isset($Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['TotalCount'][$gender])) {
                                $Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['TotalCount'][$gender] += $count;
                            } else {
                                $Content['E01'][$course][$isMixed ? 'Mixed' : 'Pure']['TotalCount'][$gender] = $count;
                            }
                        }
                    }

                    if (isset($Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level])) {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level]++;
                    } else {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['L' . $level] = 1;
                    }

                    if (isset($Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount'])) {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount']++;
                    } else {
                        $Content['E01K'][$isMixed ? 'Mixed' : $courseString]['TotalCount'] = 1;
                    }
                }
            }
        }
    }

    /**
     * @param $tblPastYearList
     * @param $Content
     * @param TblType $tblKamenzSchoolType
     */
    private static function setGraduate(
        $tblPastYearList,
        &$Content,
        TblType $tblKamenzSchoolType
    ) {
        if ($tblPastYearList) {
            $countArray = array();

            // Abgangszeugnisse
            if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllBy(true, true))) {
                foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                    $isLeave = false;
                    $tblLevel = false;
                    if (($tblDivision = $tblLeaveStudent->getServiceTblDivision())
                        && ($tblLevel = $tblDivision->getTblLevel())
                        && ($tblSchoolType = $tblLevel->getServiceTblType())
                        && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                        && ($tblYear = $tblDivision->getServiceTblYear())
                    ) {
                        /** @var TblYear $tblPastYear */
                        foreach ($tblPastYearList as $tblPastYear) {
                            if ($tblPastYear->getId() == $tblYear->getId()) {
                                $isLeave = true;
                                break;
                            }
                        }
                    }

                    if ($isLeave
                        && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                        && $tblLevel
                    ) {
                        $levelName = $tblLevel->getName();

                        $hasMigrationBackground = false;
                        if (($tblStudent = $tblPerson->getStudent())
                            && $tblStudent->getHasMigrationBackground()
                        ) {
                            $hasMigrationBackground = true;
                        }

                        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                            && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                            && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                            && ($birthDay = $tblCommonBirthDates->getBirthday())
                        ) {

                            if ($tblCommonGender->getName() == 'Männlich') {
                                $gender = 'm';
                            } elseif ($tblCommonGender->getName() == 'Weiblich') {
                                $gender = 'w';
                            } else {
                                $gender = 'x';
                            }

                            $birthDayDate = new \DateTime($birthDay);
                            if ($birthDayDate) {
                                $birthYear = $birthDayDate->format('Y');
                            } else {
                                $birthYear = false;
                            }

                            if (isset($Content['B01']['Leave']['L' . $levelName][$gender])) {
                                $Content['B01']['Leave']['L' . $levelName][$gender]++;
                            } else {
                                $Content['B01']['Leave']['L' . $levelName][$gender] = 1;
                            }
                            if (isset($Content['B01']['Leave']['TotalCount'][$gender])) {
                                $Content['B01']['Leave']['TotalCount'][$gender]++;
                            } else {
                                $Content['B01']['Leave']['TotalCount'][$gender] = 1;
                            }
                            if (isset($Content['B01']['TotalCount']['L' . $levelName][$gender])) {
                                $Content['B01']['TotalCount']['L' . $levelName][$gender]++;
                            } else {
                                $Content['B01']['TotalCount']['L' . $levelName][$gender] = 1;
                            }
                            if (isset($Content['B01']['TotalCount'][$gender])) {
                                $Content['B01']['TotalCount'][$gender] += 1;
                            } else {
                                $Content['B01']['TotalCount'][$gender] = 1;
                            }

                            /**
                             * B02
                             */
                            if ($birthYear) {
                                if (isset($countArray[$birthYear]['Leave'][$gender])) {
                                    $countArray[$birthYear]['Leave'][$gender]++;
                                } else {
                                    $countArray[$birthYear]['Leave'][$gender] = 1;
                                }
                            }

                            /**
                             * B01.1
                             */
                            if ($hasMigrationBackground) {
                                if (isset($Content['B01_1']['Leave']['L' . $levelName][$gender])) {
                                    $Content['B01_1']['Leave']['L' . $levelName][$gender]++;
                                } else {
                                    $Content['B01_1']['Leave']['L' . $levelName][$gender] = 1;
                                }
                                if (isset($Content['B01_1']['Leave']['TotalCount'][$gender])) {
                                    $Content['B01_1']['Leave']['TotalCount'][$gender]++;
                                } else {
                                    $Content['B01_1']['Leave']['TotalCount'][$gender] = 1;
                                }
                                if (isset($Content['B01_1']['TotalCount']['L' . $levelName][$gender])) {
                                    $Content['B01_1']['TotalCount']['L' . $levelName][$gender]++;
                                } else {
                                    $Content['B01_1']['TotalCount']['L' . $levelName][$gender] = 1;
                                }
                                if (isset($Content['B01_1']['TotalCount'][$gender])) {
                                    $Content['B01_1']['TotalCount'][$gender] += 1;
                                } else {
                                    $Content['B01_1']['TotalCount'][$gender] = 1;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblPastYear))) {
                    foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && (
//                              $tblCertificateType->getIdentifier() == 'LEAVE' ||
                                $tblCertificateType->getIdentifier() == 'DIPLOMA')
                            && (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate)))
                        ) {

                            foreach ($tblPrepareList as $tblPrepare) {
                                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                                    && ($tblLevel = $tblDivision->getTblLevel())
                                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                                    && $tblSchoolType->getId() == $tblKamenzSchoolType->getId()
                                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
                                ) {
                                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                        if ($tblPrepareStudent->isPrinted()
                                            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        ) {

                                            $levelName = $tblLevel->getName();
                                            $certificate = $tblCertificate->getCertificate();
                                            if ($certificate == 'MsAbsHsQ') {
                                                $certificate = 'MsAbsHs';
                                            }

                                            $hasMigrationBackground = false;
                                            if (($tblStudent = $tblPerson->getStudent())
                                                && $tblStudent->getHasMigrationBackground()
                                            ) {
                                                $hasMigrationBackground = true;
                                            }

                                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                                && (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()))
                                                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                                && ($birthDay = $tblCommonBirthDates->getBirthday())
                                            ) {

                                                if ($tblCommonGender->getName() == 'Männlich') {
                                                    $gender = 'm';
                                                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                                                    $gender = 'w';
                                                } else {
                                                    $gender = 'x';
                                                }

                                                $birthDayDate = new \DateTime($birthDay);
                                                if ($birthDayDate) {
                                                    $birthYear = $birthDayDate->format('Y');
                                                } else {
                                                    $birthYear = false;
                                                }

                                                if (isset($Content['B01'][$certificate]['L' . $levelName][$gender])) {
                                                    $Content['B01'][$certificate]['L' . $levelName][$gender]++;
                                                } else {
                                                    $Content['B01'][$certificate]['L' . $levelName][$gender] = 1;
                                                }
                                                if (isset($Content['B01'][$certificate]['TotalCount'][$gender])) {
                                                    $Content['B01'][$certificate]['TotalCount'][$gender]++;
                                                } else {
                                                    $Content['B01'][$certificate]['TotalCount'][$gender] = 1;
                                                }
                                                if (isset($Content['B01']['TotalCount']['L' . $levelName][$gender])) {
                                                    $Content['B01']['TotalCount']['L' . $levelName][$gender]++;
                                                } else {
                                                    $Content['B01']['TotalCount']['L' . $levelName][$gender] = 1;
                                                }

                                                if (isset($Content['B01']['TotalCount'][$gender])) {
                                                    $Content['B01']['TotalCount'][$gender] += 1;
                                                } else {
                                                    $Content['B01']['TotalCount'][$gender] = 1;
                                                }

                                                /**
                                                 * B02
                                                 */
                                                if ($birthYear) {
                                                    if (isset($countArray[$birthYear][$certificate][$gender])) {
                                                        $countArray[$birthYear][$certificate][$gender]++;
                                                    } else {
                                                        $countArray[$birthYear][$certificate][$gender] = 1;
                                                    }
                                                }

                                                /**
                                                 * B01.1
                                                 */
                                                if ($hasMigrationBackground) {
                                                    if (isset($Content['B01_1'][$certificate]['L' . $levelName][$gender])) {
                                                        $Content['B01_1'][$certificate]['L' . $levelName][$gender]++;
                                                    } else {
                                                        $Content['B01_1'][$certificate]['L' . $levelName][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01_1'][$certificate]['TotalCount'][$gender])) {
                                                        $Content['B01_1'][$certificate]['TotalCount'][$gender]++;
                                                    } else {
                                                        $Content['B01_1'][$certificate]['TotalCount'][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01_1']['TotalCount']['L' . $levelName][$gender])) {
                                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender]++;
                                                    } else {
                                                        $Content['B01_1']['TotalCount']['L' . $levelName][$gender] = 1;
                                                    }
                                                    if (isset($Content['B01_1']['TotalCount'][$gender])) {
                                                        $Content['B01_1']['TotalCount'][$gender] += 1;
                                                    } else {
                                                        $Content['B01_1']['TotalCount'][$gender] = 1;
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
            }

            /**
             * B02. Absolventen/Abgänger aus dem Schuljahr 2015/16 nach Geburtsjahren und Abschlussarten
             */
            ksort($countArray);
            $count = 0;
            $Content['B02']['TotalCount']['m'] = 0;
            $Content['B02']['TotalCount']['w'] = 0;
            foreach ($countArray as $year => $certificateArray) {
                $Content['B02']['Y' . $count]['YearName'] = $year;
                $Content['B02']['Y' . $count]['m'] = 0;
                $Content['B02']['Y' . $count]['w'] = 0;
                $Content['B02']['Y' . $count]['x'] = 0;
                foreach ($certificateArray as $levelName => $genderArray) {
                    foreach ($genderArray as $gender => $value) {
                        $Content['B02']['Y' . $count][$levelName][$gender] = $value;

                        if (isset($Content['B02']['TotalCount'][$levelName][$gender])) {
                            $Content['B02']['TotalCount'][$levelName][$gender] += $value;
                        } else {
                            $Content['B02']['TotalCount'][$levelName][$gender] = $value;
                        }

                        $Content['B02']['Y' . $count][$gender] += $value;
                        $Content['B02']['TotalCount'][$gender] += $value;
                    }
                }

                $count++;
            }
        }
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblDivision $tblDivision
     * @param $countAdvancedCourseArray
     * @param $countBasicCourseArray
     * @param $personAdvancedCourseList
     */
    private static function countCourses(
        TblLevel $tblLevel,
        TblDivision $tblDivision,
        &$countAdvancedCourseArray,
        &$countBasicCourseArray,
        &$personAdvancedCourseList
    ) {
        if (preg_match('!(11|12)!is', $tblLevel->getName())) {
            if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                    if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                        && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                    ) {
                        if ($tblSubjectGroup->isAdvancedCourse()) {
                            if (isset($countAdvancedCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()])) {
                                $countAdvancedCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()]++;
                            } else {
                                $countAdvancedCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()] = 1;
                            }

                            /**
                             * E18. Schüler in Leistungskursen im Schuljahr nach Jahrgangsstufen
                             */
                            $tblSubjectStudentsList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
                            if ($tblSubjectStudentsList) {
                                foreach ($tblSubjectStudentsList as $tblSubjectStudent) {
                                    if ($tblSubjectStudent->getServiceTblPerson()) {
                                        if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())) {

                                            if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                                $personAdvancedCourseList[$tblLevel->getName()][$tblPerson->getId()][0]
                                                    = $tblSubject->getAcronym();
                                            } else {
                                                $personAdvancedCourseList[$tblLevel->getName()][$tblPerson->getId()][1]
                                                    = $tblSubject->getAcronym();
                                            }
                                        }
                                    }
                                }
                            }

                        } else {
                            if (($tblStudentSubjectList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject))) {
                                $count = array();
                                $count['m'] = $count['w'] = $count['x'] = 0;
                                foreach ($tblStudentSubjectList as $tblSubjectStudent) {
                                    if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())
                                        && ($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                                        && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                                        && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                                    ) {
                                        if ($tblCommonGender->getName() == 'Männlich') {
                                            $gender = 'm';
                                        } elseif ($tblCommonGender->getName() == 'Weiblich') {
                                            $gender = 'w';
                                        } else {
                                            $gender = 'x';
                                        }

                                        $count[$gender]++;
                                    }
                                }

                                $countBasicCourseArray = self::countGenderCourses($tblSubject, $tblLevel, $count,
                                    $countBasicCourseArray);
                            }

                            if (isset($countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()]['CoursesCount'])) {
                                $countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()]['CoursesCount']++;
                            } else {
                                $countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()]['CoursesCount'] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $Content
     * @param $personAdvancedCourseList
     */
    private static function setCoursesMatrix(
        &$Content,
        $personAdvancedCourseList
    ) {

        $countAdvancedCourseList = array();
        foreach ($personAdvancedCourseList as $level => $personArray) {
            foreach ($personArray as $personId => $subjects) {
                $identifier = '';
                ksort($subjects);
                foreach ($subjects as $ranking => $acronym) {
                    $identifier .= $acronym;
                }

                $gender = 'x';
                if (($tblPerson = Person::useService()->getPersonById($personId))
                    && ($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    if ($tblCommonGender->getName() == 'Männlich') {
                        $gender = 'm';
                    } elseif ($tblCommonGender->getName() == 'Weiblich') {
                        $gender = 'w';
                    }
                }

                $count = count($subjects);
                if (!isset($countAdvancedCourseList[$count][$identifier])) {
                    foreach ($subjects as $ranking => $acronym) {
                        $countAdvancedCourseList[$count][$identifier]['Subjects'][$ranking] = $acronym;
                    }
                }

                if (isset($countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender])) {
                    $countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender]++;
                } else {
                    $countAdvancedCourseList[$count][$identifier]['Levels'][$level][$gender] = 1;
                }
            }
        }

        ksort($countAdvancedCourseList);
        $count = 0;
        foreach ($countAdvancedCourseList as $counter => $identifierArray) {
            ksort($identifierArray);
            foreach ($identifierArray as $identifier => $array) {
                if (isset($array['Subjects'])) {
                    foreach ($array['Subjects'] as $ranking => $acronym) {
                        $Content['E18']['S' . $count]['N' . $ranking] =
                            ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                                ? $tblSubject->getName()
                                : '';
                    }
                }
                if (isset($array['Levels'])) {
                    foreach ($array['Levels'] as $level => $genderArray) {
                        foreach ($genderArray as $gender => $value) {
                            $Content['E18']['S' . $count]['L' . $level][$gender] = $value;

                            if (isset($Content['E18']['TotalCount']['L' . $level][$gender])) {
                                $Content['E18']['TotalCount']['L' . $level][$gender] += $value;
                            } else {
                                $Content['E18']['TotalCount']['L' . $level][$gender] = $value;
                            }
                        }
                    }
                }

                $count++;
            }
        }
    }

    /**
     * @param TblSubject $tblSubject
     * @param TblLevel $tblLevel
     * @param $count
     * @param $countBasicCourseArray
     *
     * @return array
     */
    private static function countGenderCourses(
        TblSubject $tblSubject,
        TblLevel $tblLevel,
        $count,
        $countBasicCourseArray
    ) {

        foreach ($count as $gender => $value) {
            if (isset($countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender])) {
                $countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] += $value;
            } else {
                $countBasicCourseArray[$tblSubject->getAcronym()][$tblLevel->getName()][$gender] = $value;
            }
        }

        return $countBasicCourseArray;
    }

    /**
     * @param $Content
     * @param $countAdvancedCourseArray
     * @param $countBasisCourseArray
     */
    private static function setCourses(
        &$Content,
        $countAdvancedCourseArray,
        $countBasisCourseArray
    ) {
        /**
         * E16. Schüler in Grundkursen an dieser Schule im Schuljahr 2016/2017 nach Jahrgangsstufen
         */
        ksort($countBasisCourseArray);
        $count = 0;
        foreach ($countBasisCourseArray as $acronym => $levelArray) {
            $subjectName = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            if ($subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                $subjectName = 'GRW';
            }
            $Content['E16']['S' . $count]['SubjectName'] = $subjectName;
            foreach ($levelArray as $level => $valueArray) {
                foreach ($valueArray as $identifier => $value) {
                    $Content['E16']['S' . $count]['L' . $level][$identifier] = $value;

                    if (isset($Content['E16']['TotalCount']['L' . $level][$identifier])) {
                        $Content['E16']['TotalCount']['L' . $level][$identifier] += $value;
                    } else {
                        $Content['E16']['TotalCount']['L' . $level][$identifier] = $value;
                    }
                }
            }

            $count++;
        }

        /**
         * E17. Anzahl der Leistungskurse an dieser Schule im Schuljahr 2016/2017 nach Jahrgangsstufen
         */
        ksort($countAdvancedCourseArray);
        $count = 0;
        foreach ($countAdvancedCourseArray as $acronym => $levelArray) {
            $Content['E17']['S' . $count]['SubjectName'] = ($tblSubject = Subject::useService()->getSubjectByAcronym($acronym))
                ? $tblSubject->getName() : '';
            foreach ($levelArray as $level => $value) {
                $Content['E17']['S' . $count]['L' . $level] = $value;

                if (isset($Content['E17']['S' . $count]['TotalCount'])) {
                    $Content['E17']['S' . $count]['TotalCount'] += $value;
                } else {
                    $Content['E17']['S' . $count]['TotalCount'] = $value;
                }

                if (isset($Content['E17']['TotalCount']['L' . $level])) {
                    $Content['E17']['TotalCount']['L' . $level] += $value;
                } else {
                    $Content['E17']['TotalCount']['L' . $level] = $value;
                }

                if (isset($Content['E17']['TotalCount']['TotalCount'])) {
                    $Content['E17']['TotalCount']['TotalCount'] += $value;
                } else {
                    $Content['E17']['TotalCount']['TotalCount'] = $value;
                }
            }

            $count++;
        }
    }

    /**
     * @param $Content
     * @param $tblPastYearList
     * @param TblPerson $tblPerson
     * @param $tblCourse
     * @param TblLevel $tblLevel
     * @param $gender
     * @param $isInPreparationDivisionForMigrants
     * @param TblType $tblSchoolType
     */
    private static function setSchoolTypeLastYear(
        &$Content,
        $tblPastYearList,
        TblPerson $tblPerson,
        $tblCourse,
        TblLevel $tblLevel,
        $gender,
        $isInPreparationDivisionForMigrants,
        TblType $tblSchoolType
    ) {

        $tblLevelLastYear = false;
        if ($tblPastYearList && is_array($tblPastYearList)) {
            foreach ($tblPastYearList as $tblPastYear) {
                if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                            && ($tblYearTemp = $tblDivisionTemp->getServiceTblYear())
                            && $tblYearTemp->getId() == $tblPastYear->getId()
                            && ($tblLevelTemp = $tblDivisionTemp->getTblLevel())
                            && !$tblLevelTemp->getIsChecked()
                        ) {
                            $tblLevelLastYear = $tblLevelTemp;
                            break;
                        }
                    }

                    if ($tblLevelLastYear) {
                        break;
                    }
                }
            }
        }

        $identifier = false;
        if ($tblLevelLastYear
            && ($tblSchoolTypeLastYear = $tblLevelLastYear->getServiceTblType())
        ) {

            if ($tblSchoolTypeLastYear->getName() == 'Grundschule') {
                $identifier = 'PrimarySchool';
            } elseif ($tblSchoolTypeLastYear->getName() == 'Gymnasium') {
                $identifier = 'GrammarSchool';
            } elseif ($tblSchoolTypeLastYear->getName() == 'Mittelschule / Oberschule') {
                // führende Nullen entfernen
                $level = ltrim($tblLevelLastYear->getName(), '0');

                if ($tblSchoolType->getName() == 'Mittelschule / Oberschule') {
                    /** @var TblCourse $tblCourse */
                    if ($level != '5' && $level != '6' && $tblCourse) {
                        if ($tblCourse->getName() == 'Hauptschule') {
                            $identifier = 'SecondarySchoolHs';
                        } elseif ($tblCourse->getName() == 'Realschule') {
                            $identifier = 'SecondarySchoolRs';
                        }
                    } else {
                        $identifier = 'SecondarySchool';
                    }
                } else {
                    $identifier = 'SecondarySchool';
                }
            }
        } else {
            $identifier = 'Unknown';
            // aus der Schülerakte bestimmen SSW-322
            if (($tblStudent = $tblPerson->getStudent())
                && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                && ($tblStudentTransfer = Student::useService()->getStudentTransferByType(
                    $tblStudent, $tblStudentTransferType
                ))
            ) {
                if (($tblSchoolTypeTransfer = $tblStudentTransfer->getServiceTblType())) {
                    $useUnknown = false;
                    // SSW-396 Datum überprüfen z.B. für Schüler im Auslandssemester
                    if (($date = $tblStudentTransfer->getTransferDate())) {
                        $date = new \DateTime($date);
                        $now = new \DateTime('now');

                        if ($date->format('Y') != $now->format('Y')) {
                            $useUnknown = true;
                        }
                    }

                    if ($useUnknown) {
                        $identifier = 'Unknown';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Grundschule') {
                        $identifier = 'PrimarySchool';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Gymnasium') {
                        $identifier = 'GrammarSchool';
                    } elseif ($tblSchoolTypeTransfer->getName() == 'Mittelschule / Oberschule') {
                        $identifier = 'SecondarySchool';
                        if ($tblSchoolType->getName() == 'Mittelschule / Oberschule') {
                            if (($tblCourseTransfer = $tblStudentTransfer->getServiceTblCourse())) {
                                if($tblCourseTransfer->getName() == 'Hauptschule') {
                                    $identifier = 'SecondarySchoolHs';
                                } elseif ($tblCourseTransfer->getName() == 'Realschule') {
                                    $identifier = 'SecondarySchoolRs';
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($identifier) {
            if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                    $Content['E07'][$identifier]['Migration'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['Migration'][$gender] = 1;
                }
            }
            if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                $Content['E07'][$identifier]['TotalCount'][$gender]++;
            } else {
                $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
            }

            /**
             * TotalCount
             */
            $identifier = 'TotalCount';
            if (isset($Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender])) {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender]++;
            } else {
                $Content['E07'][$identifier]['L' . $tblLevel->getName()][$gender] = 1;
            }
            if ($isInPreparationDivisionForMigrants) {
                if (isset($Content['E07'][$identifier]['Migration'][$gender])) {
                    $Content['E07'][$identifier]['Migration'][$gender]++;
                } else {
                    $Content['E07'][$identifier]['Migration'][$gender] = 1;
                }
            }
            if (isset($Content['E07'][$identifier]['TotalCount'][$gender])) {
                $Content['E07'][$identifier]['TotalCount'][$gender]++;
            } else {
                $Content['E07'][$identifier]['TotalCount'][$gender] = 1;
            }
        }
    }
}