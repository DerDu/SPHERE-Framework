<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

class SDataPrimary
{
    public static function setCertificateStandard(Data $Data)
    {

        self::setGsHjOneInfo($Data);
        self::setGsHjInformation($Data);
        self::setGsJOne($Data);
        self::setGsJa($Data);
        self::setBeGs($Data);
    }

    /**
     * @param Data $Data
     */
    private static function setGsHjOneInfo(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
            'GsHjOneInfo');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsJOne(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsHjInformation(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInformation');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1200);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {

            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setGsJa(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', '', 'GsJa');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {

            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBeGs(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'BeGs');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeRecommendation(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if ($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4')) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        // SSW-1981 Deaktivierung Bildungsempfehlung Klasse 5/6
        if (($tblCertificate = $Data->getCertificateByCertificateClassName('BeMi'))) {
            $Data->destroyCertificate($tblCertificate);
        }
    }


}