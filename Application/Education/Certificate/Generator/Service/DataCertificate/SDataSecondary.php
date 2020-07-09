<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataSecondary
{

    public static function setCertificateStandard(Data $Data)
    {

        // wird aktuell nicht benötigt
        // $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        if (($tblCertificate = $Data->getCertificateByCertificateClassName('MsHj'))) {
            $Data->destroyCertificate($tblCertificate);
        }

        self::setCertificateBeSOFS($Data);
        self::setMsHjInfoHs($Data);
        self::setMsHjInfo($Data);
        self::setMsHjInfoRs($Data);
        self::setMsHjInfoFsLernen($Data);
        self::setMsHjFsLernen($Data);
        self::setMsJFsLernen($Data);
        self::setMsHjInfoFsGeistigeEntwicklung($Data);
        self::setMsHjFsGeistigeEntwicklung($Data);
        self::setMsJFsGeistigeEntwicklung($Data);
        self::setMsHjHs($Data);
        self::setMsHjRs($Data);
        self::setMsJHs($Data);
        self::setMsJ($Data);
        self::setMsJRs($Data);
        self::setMsAbsHs($Data);
        self::setMsAbsHsQ($Data);
        self::setMsAbsRs($Data);
        self::setMsAbg($Data);
        self::setMsAbgLernen($Data);
        self::setMsAbgLernenHs($Data);
        self::setMsAbgGeistigeEntwicklung($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setCertificateBeSOFS(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal(), true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt Lernen', 'MsHjInfoFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt Lernen', 'MsHjFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJFsLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt Lernen', 'MsJFsLernen');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjInfoFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt geistige Entwicklung', 'MsHjInfoFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, true, true);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsHjFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJFsGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsJFsGeistigeEntwicklung');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    null, false, true);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsHjRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJ(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsJRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Realschule', 'MsJRs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsHsQ(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'MsAbsHsQ');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseMain()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseMain());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbsRs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GK', 1, 7);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
            $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
        }
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary() && $Data->getTblCourseReal()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeSecondary(),
                    $Data->getTblCourseReal());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbg(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', '', 'MsAbg',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GK', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', $row, $column);

                $row = 2;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbgLernen(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt Lernen', 'MsAbgLernen',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GK', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', $row, $column);

                $row = 2;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbgLernenHs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt Lernen + Hauptschulbildungsgang', 'MsAbgLernenHs',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {

            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GK', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', $row, $column);

                $row = 2;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setMsAbgGeistigeEntwicklung(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Abgangszeugnis', 'Förderschwerpunkt geistige Entwicklung', 'MsAbgGeistigeEntwicklung',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GK', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'WTH', $row, $column);

                $row = 2;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
            }
        }
    }
}