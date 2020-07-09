<?php


namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;


use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataGym
{

    public static function setCertificateStandard(Data $Data)
    {

        self::setGymHjInfo($Data);
        self::setGymHj($Data);
        self::setGymJ($Data);
        self::setGymKurshalbjahreszeugnis($Data);
        self::setGymAbitur($Data);
        self::setGymAbgSekI($Data);
        self::setGymAbgSekII($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setGymHjInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

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
    private static function setGymHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

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
    private static function setGymJ(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeGym());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '5'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '6'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '7'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '8'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '9'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 200);
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
            // 1,3 freilassen für Fremdsprache
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $Data->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $Data->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

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
    private static function setGymKurshalbjahreszeugnis(Data $Data)
    {

        // Kurshalbjahreszeugnis
        $tblCertificate = $Data->createCertificate('Gymnasium Kurshalbjahreszeugnis', '', 'GymKurshalbjahreszeugnis');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym() && $Data->getTblCertificateTypeMidTermCourse()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeMidTermCourse(), $Data->getTblSchoolTypeGym(),
                    null);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 270);
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $row = 1;
            $Data->setCertificateSubject($tblCertificate, 'DE', $row, 1);
            $Data->setCertificateSubject($tblCertificate, 'SOR', $row, 2);

            $Data->setCertificateSubject($tblCertificate, 'EN', $row, 3, false);
            $Data->setCertificateSubject($tblCertificate, 'EN2', $row, 4, false);
            $Data->setCertificateSubject($tblCertificate, 'FR', $row, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'RU', $row, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'LA', $row, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'SPA', $row, 8, false);

            $Data->setCertificateSubject($tblCertificate, 'KU', $row, 9, false);
            $Data->setCertificateSubject($tblCertificate, 'MU', $row, 10, false);
            $Data->setCertificateSubject($tblCertificate, 'GE', $row, 11);
            $Data->setCertificateSubject($tblCertificate, 'GEO', $row, 12);
            $Data->setCertificateSubject($tblCertificate, 'GRW', $row, 13);

            $row = 2;
            $Data->setCertificateSubject($tblCertificate, 'MA', $row, 1);
            $Data->setCertificateSubject($tblCertificate, 'BIO', $row, 2);
            $Data->setCertificateSubject($tblCertificate, 'CH', $row, 3);
            $Data->setCertificateSubject($tblCertificate, 'PH', $row, 4);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', $row, 5, false);
            $Data->setCertificateSubject($tblCertificate, 'RE/k', $row, 6, false);
            $Data->setCertificateSubject($tblCertificate, 'ETH', $row, 7, false);
            $Data->setCertificateSubject($tblCertificate, 'SPO', $row, 8);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbitur(Data $Data)
    {

        if (($tblCertificate = $Data->createCertificate('Gymnasium Abschlusszeugnis', 'Abitur', 'GymAbitur',
            null, false, false, false, $Data->getTblCertificateTypeDiploma(), $Data->getTblSchoolTypeGym()))
        ) {
            // SSW-531 Rename
            if ($tblCertificate->getName() == 'Gymnasium Abitur') {
                $Data->updateCertificateName($tblCertificate, 'Gymnasium Abschlusszeugnis', 'Abitur');
            }

            if (!$Data->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate)) {
                $Data->createCertificateReferenceForLanguages($tblCertificate, 1, 'B2', 'B2+', 'C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 2, 'B1+', 'B2', 'B2+ - C1');
                $Data->createCertificateReferenceForLanguages($tblCertificate, 3, 'B1 - B1+', 'B2', 'B2+ - C1');
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbgSekI(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe I', 'GymAbgSekI',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
        if ($tblCertificate) {
            if (!$Data->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $Data->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $column++;
                $Data->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GRW', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'GEO', $row, $column);

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
                $Data->setCertificateSubject($tblCertificate, 'TC', $row, $column++);
                $Data->setCertificateSubject($tblCertificate, 'INF', $row, $column);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGymAbgSekII(Data $Data)
    {

        $Data->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe II', 'GymAbgSekII',
            null, false, false, false, $Data->getTblCertificateTypeLeave(), $Data->getTblSchoolTypeGym());
    }
}