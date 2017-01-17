<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateField;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $tblCertificateTypeHalfYear = $this->createCertificateType('Halbjahresinformation/Halbjahreszeugnis',
            'HALF_YEAR');
        $tblCertificateTypeYear = $this->createCertificateType('Jahreszeugnis/Abschlusszeugnis', 'YEAR');
        $tblCertificateTypeGradeInformation = $this->createCertificateType('Noteninformation', 'GRADE_INFORMATION');
        $tblCertificateTypeRecommendation = $this->createCertificateType('Bildungsempfehlung', 'RECOMMENDATION');
        $tblCertificateTypeLeave = $this->createCertificateType('Abgangszeugnis', 'LEAVE');

        $tblSchoolTypePrimary = Type::useService()->getTypeByName('Grundschule');
        $tblSchoolTypeSecondary = Type::useService()->getTypeByName('Mittelschule / Oberschule');
        $tblSchoolTypeGym = Type::useService()->getTypeByName('Gymnasium');

        $tblCourseMain = Course::useService()->getCourseByName('Hauptschule');
        $tblCourseReal = Course::useService()->getCourseByName('Realschule');

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'BeGs');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypePrimary);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', 'Mittelschule Klasse 5-6', 'BeMi');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypeSecondary);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInformation');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary, null, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 1200);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true,
                Student::useService()->getStudentLiberationCategoryById(1));
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
            'GsHjOneInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary, null, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $this->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }

        $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', '', 'GsJa');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true,
                Student::useService()->getStudentLiberationCategoryById(1));
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $this->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }

        $tblCertificate = $this->createCertificate('Gymnasium Abgangszeugnis', 'Hauptschulabschluss Klasse 9',
            'GymAbgHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeLeave, $tblSchoolTypeGym);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }

        $tblCertificate = $this->createCertificate('Gymnasium Abgangszeugnis', 'Realschulabschluss Klasse 10',
            'GymAbgRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeLeave, $tblSchoolTypeGym);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }

        $tblCertificate = $this->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeGym, null, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeGym);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '11'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '12'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeGym);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 200);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abgangszeugnis', 'Hauptschule', 'MsAbgHs');
        if ($tblCertificate && $tblCourseMain) {
            $this->updateCertificate($tblCertificate, $tblCertificateTypeLeave, $tblSchoolTypeSecondary,
                $tblCourseMain);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abgangszeugnis', 'Realschule', 'MsAbgRs');
        if ($tblCertificate && $tblCourseReal) {
            $this->updateCertificate($tblCertificate, $tblCertificateTypeLeave, $tblSchoolTypeSecondary,
                $tblCourseReal);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'MsAbsHsQ');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseMain, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary, null, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseReal, true);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        // wird aktuell nicht benötigt
        // $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        if (($tblCertificate = $this->getCertificateByCertificateClassName('MsHj'))) {
            $this->destroyCertificate($tblCertificate);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Realschule', 'MsJRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
        }

        /*
         * Noteninformation
         */
        $tblCertificate = $this->createCertificate('Noteninformation', '',
            'GradeInformation', null, true);
        if ($tblCertificate) {
            $this->updateCertificate($tblCertificate, $tblCertificateTypeGradeInformation, null, null, true);
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                && $tblConsumer->getAcronym() == 'ESZC'
            ) {
                $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BIO', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'TC', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'RELI', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            } else {
                $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BI', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'IN', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'REV', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            }
        }

        // Alt-Last löschen
        if (($tblCertificate = $this->getCertificateByCertificateClassName('GsJ'))) {
            $this->destroyCertificate($tblCertificate);
        }

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer) {
//            if ($tblConsumer->getAcronym() == 'ESZC' || $tblConsumer->getAcronym() == 'DEMO') {
//                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESZC');
//                if ($tblConsumerCertificate) {
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Klassenstufe 4', 'ESZC\CheBeGs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Gymnasium', 'ESZC\CheBeGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Mittelschule', 'ESZC\CheBeMi', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Hauptschule', 'ESZC\CheHjInfoHs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Klasse 5-6', 'ESZC\CheHjInfo', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Realschule', 'ESZC\CheHjInfoRs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Gymnasium', 'ESZC\CheHjGymInfo', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Gymnasium', 'ESZC\CheHjGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Hauptschule', 'ESZC\CheHjHs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Klasse 5-6', 'ESZC\CheHj', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Realschule', 'ESZC\CheHjRs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Jahreszeugnis', 'Mittelschule', 'ESZC\CheJ', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Jahreszeugnis', 'Gymnasium', 'ESZC\CheJGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Jahreszeugnis', 'Grundschule Klasse 2-4', 'ESZC\CheJGs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
//                    }
//
//                    $this->createCertificate(
//                        'Jahreszeugnis', 'Grundschule Klasse 1', 'ESZC\CheJGsOne', $tblConsumerCertificate
//                    );
//
//                    $tblCertificate = $this->createCertificate(
//                        'Habljahresinformation', 'Grundschule Klasse 2-4', 'ESZC\CheHjInfoGs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
//                    }
//
//                    $this->createCertificate(
//                        'Habljahresinformation', 'Grundschule Klasse 1', 'ESZC\CheHjInfoGsOne', $tblConsumerCertificate
//                    );
//                }
//            }

            // Alt-Last löschen
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeMi'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHj'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjGymInfo'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjHs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfo'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoGs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoGsOne'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoHs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoRs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjRs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJ'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGsOne'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGym'))) {
                $this->destroyCertificate($tblCertificate);
            }

            if ($tblConsumer->getAcronym() == 'EVSC' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSC');
                if ($tblConsumerCertificate) {

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Primarstufe', 'EVSC\CosHjPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SACH', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WERK', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Sekundarstufe', 'EVSC\CosHjSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahreszeugnis', 'Sekundarstufe', 'EVSC\CosHjZSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Primarstufe', 'EVSC\CosJPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SACH', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WERK', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Sekundarstufe', 'EVSC\CosJSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'FESH' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('FESH');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '', 'FESH\HorHj', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $this->createCertificate(
                        'Halbjahresinformation', '1. Klasse', 'FESH\HorHjOne', $tblConsumerCertificate
                    );

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '', 'FESH\HorJ', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $this->createCertificate(
                        'Jahreszeugnis', '1. Klasse', 'FESH\HorJOne', $tblConsumerCertificate
                    );
                }
            }

            if ($tblConsumer->getAcronym() == 'EVSR' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSR');
                if ($tblConsumerCertificate) {

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '', 'EVSR\RadebeulJahreszeugnis', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Einschätzungfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 170);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 600);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '', 'EVSR\RadebeulHalbjahresinformation', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Einschätzungfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 170);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 600);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Bildungsempfehlung', 'Klassenstufe 4', 'EVSR\RadebeulBildungsempfehlung',
                        $tblConsumerCertificate
                    );
                    if ($tblCertificate){
                        $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypePrimary);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Kinderbrief', '', 'EVSR\RadebeulKinderbrief', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                    }

                    $tblCertificate = $this->createCertificate(
                        'Lernentwicklungsbericht', '', 'EVSR\RadebeulLernentwicklungsbericht', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Certificate
     * @param TblConsumer|null $tblConsumer
     * @param bool $IsGradeInformation
     * @param bool $IsInformation
     *
     * @return null|object|TblCertificate
     */
    public function createCertificate(
        $Name,
        $Description,
        $Certificate,
        TblConsumer $tblConsumer = null,
        $IsGradeInformation = false,
        $IsInformation = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array(
            TblCertificate::ATTR_CERTIFICATE => $Certificate
        ));

        if (null === $Entity) {
            $Entity = new TblCertificate();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCertificate($Certificate);
            $Entity->setServiceTblConsumer($tblConsumer);
            $Entity->setIsGradeInformation($IsGradeInformation);
            $Entity->setIsInformation($IsInformation);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblGradeType $tblGradeType
     *
     * @return null|object|TblCertificateGrade
     */
    public function createCertificateGrade(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblGradeType $tblGradeType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array(
            TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateGrade::ATTR_LANE => $LaneIndex,
            TblCertificateGrade::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateGrade();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblGradeType($tblGradeType);
            $Entity->setEssential(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function updateCertificateGrade(TblCertificateGrade $tblCertificateGrade, TblGradeType $tblGradeType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateGrade $Entity */
        $Entity = $Manager->getEntityById('TblCertificateGrade', $tblCertificateGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblGradeType($tblGradeType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     * @param null|TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return TblCertificateSubject
     */
    public function createCertificateSubject(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblSubject $tblSubject,
        $IsEssential = false,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array(
            TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateSubject::ATTR_LANE => $LaneIndex,
            TblCertificateSubject::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateSubject();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblStudentLiberationCategory($tblStudentLiberationCategory);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     * @param null|TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return bool
     */
    public function updateCertificateSubject(
        TblCertificateSubject $tblCertificateSubject,
        TblSubject $tblSubject,
        $IsEssential = false,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblStudentLiberationCategory($tblStudentLiberationCategory);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function removeCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return false|TblCertificate[]
     */
    public function getTemplateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate');
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null)
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }


    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificate', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_CERTIFICATE => $Class
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateSubject', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(TblCertificate $tblCertificate, TblSubject $tblSubject)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateSubject',
            array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::ATTR_LANE => $LaneIndex,
                TblCertificateSubject::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateGrade::ATTR_LANE => $LaneIndex,
                TblCertificateGrade::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $SubjectAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     * @param bool|true $IsEssential
     * @param TblStudentLiberationCategory|null $tblStudentLiberationCategory
     */
    private function setCertificateSubject(
        TblCertificate $tblCertificate,
        $SubjectAcronym,
        $LaneIndex,
        $LaneRanking,
        $IsEssential = true,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        // Chemnitz abweichende Fächer
        if ($SubjectAcronym == 'DE' || $SubjectAcronym == 'D') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('DE');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('D');
            }
        } elseif ($SubjectAcronym == 'BI' || $SubjectAcronym == 'BIO') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('BI');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('BIO');
            }
        } elseif ($SubjectAcronym == 'REV' || $SubjectAcronym == 'RELI') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('RELI');
            }
        } elseif (($tblSubject = Subject::useService()->getSubjectByAcronym($SubjectAcronym))) {

        }

        if ($tblSubject){
            $this->createCertificateSubject($tblCertificate, $LaneIndex, $LaneRanking, $tblSubject,
                $IsEssential, $tblStudentLiberationCategory);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $GradeTypeAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     */
    private function setCertificateGrade(
        TblCertificate $tblCertificate,
        $GradeTypeAcronym,
        $LaneIndex,
        $LaneRanking
    ) {

        if (($tblGradeType = Gradebook::useService()->getGradeTypeByCode($GradeTypeAcronym))) {
            $this->createCertificateGrade($tblCertificate, $LaneIndex, $LaneRanking, $tblGradeType);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     */
    private function setCertificateGradeAllStandard(
        TblCertificate $tblCertificate
    ) {
        $this->setCertificateGrade($tblCertificate, 'KBE', 1, 1);
        $this->setCertificateGrade($tblCertificate, 'KFL', 1, 2);

        $this->setCertificateGrade($tblCertificate, 'KMI', 2, 1);
        $this->setCertificateGrade($tblCertificate, 'KOR', 2, 2);
    }


    /**
     * @param $Identifier
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            array(
                TblCertificateType::ATTR_IDENTIFIER => strtoupper($Identifier)
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            $Id
        );
    }

    /**
     * @return false|TblCertificateType[]
     */
    public function getCertificateTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType');
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return null|TblCertificateType
     */
    public function createCertificateType($Name, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateType')
            ->findOneBy(array(TblCertificateType::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblCertificateType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblCertificateType|null $tblCertificateType
     * @param TblType|null $tblSchoolType
     * @param TblCourse|null $tblCourse
     * @param bool $IsInformation
     *
     * @return bool
     */
    public function updateCertificate(
        TblCertificate $tblCertificate,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null,
        TblCourse $tblCourse = null,
        $IsInformation = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntityById('TblCertificate', $tblCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setTblCertificateType($tblCertificateType);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setIsInformation($IsInformation);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblLevel $tblLevel
     *
     * @return TblCertificateLevel
     */
    public function createCertificateLevel(TblCertificate $tblCertificate, TblLevel $tblLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateLevel')
            ->findOneBy(array(
                    TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                    TblCertificateLevel::SERVICE_TBL_LEVEL => $tblLevel->getId()
                )
            );

        if (null === $Entity) {
            $Entity = new TblCertificateLevel();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setServiceTblLevel($tblLevel);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool
     */
    public function destroyCertificate(TblCertificate $tblCertificate)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array('Id' => $tblCertificate->getId()));
        if (null !== $Entity) {
            // Foreign-Key Verknüpfungen löschen
            if (($tblCertificateGradeList = $this->getCertificateGradeAll($Entity))) {
                foreach ($tblCertificateGradeList as $tblCertificateGrade) {
                    $this->destroyCertificateGrade($tblCertificateGrade);
                }
            }
            if (($tblCertificateSubjectList = $this->getCertificateSubjectAll($Entity))) {
                foreach ($tblCertificateSubjectList as $tblCertificateSubject) {
                    $this->destroyCertificateSubject($tblCertificateSubject);
                }
            }
            if (($tblCertificateLevelList = $this->getCertificateLevelAllByCertificate($Entity))){
                foreach ($tblCertificateLevelList as $tblCertificateLevel) {
                    $this->destroyCertificateLevel($tblCertificateLevel);
                }
            }
            if (($tblCertificateFieldList = $this->getCertificateFieldAllByCertificate($Entity))){
                foreach ($tblCertificateFieldList as $tblCertificateField) {
                    $this->destroyCertificateField($tblCertificateField);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     *
     * @return bool
     */
    public function destroyCertificateGrade(TblCertificateGrade $tblCertificateGrade)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array('Id' => $tblCertificateGrade->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function destroyCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array('Id' => $tblCertificateSubject->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|Entity\TblCertificate[]
     */
    public function getCertificateAllBy(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => ($tblCertificateType ? $tblCertificateType->getId() : null),
                TblCertificate::SERVICE_TBL_SCHOOL_TYPE => ($tblSchoolType ? $tblSchoolType->getId() : null),
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateLevel[]
     */
    public function getCertificateLevelAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateLevel', array(
                TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     *
     * @return false|integer
     */
    public function getCharCountByCertificateAndField(TblCertificate $tblCertificate, $FieldName)
    {

        $tblCertificateField = $this->getCertificateFieldByCertificateAndField(
            $tblCertificate, $FieldName
        );

        if ($tblCertificateField) {
            // 1 Zeile (100 Zeichen) für Arbeitsgemeinschaften abziehen
            if ($FieldName == 'Remark'){
                $count = $tblCertificateField->getCharCount();
                return  $count > 100 ? $count - 100 : $count;
            } else {
                return $tblCertificateField->getCharCount();
            }
        }

        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $FieldName
     * @return false|TblCertificateField
     */
    public function getCertificateFieldByCertificateAndField(TblCertificate $tblCertificate, $FieldName)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateField', array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            )
        );
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param integer $CharCount
     *
     * @return TblCertificateField
     */
    public function createCertificateField(TblCertificate $tblCertificate, $FieldName, $CharCount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')
            ->findOneBy(array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            ));

        if (null === $Entity) {
            $Entity = new TblCertificateField();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setFieldName($FieldName);
            $Entity->setCharCount($CharCount);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificateLevel $tblCertificateLevel
     *
     * @return bool
     */
    public function destroyCertificateLevel(TblCertificateLevel $tblCertificateLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateLevel')->findOneBy(array('Id' => $tblCertificateLevel->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateField $tblCertificateField
     *
     * @return bool
     */
    public function destroyCertificateField(TblCertificateField $tblCertificateField)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')->findOneBy(array('Id' => $tblCertificateField->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|\SPHERE\System\Database\Fitting\Element[]
     */
    public function getCertificateFieldAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateField',
            array(TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId())
        );
    }

    /**
     * @param TblCertificateType $tblCertificateType
     *
     * @return false|TblCertificate[]
     */
    public function getCertificateAllByType(
        TblCertificateType $tblCertificateType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => $tblCertificateType->getId()
            )
        );
    }
}
