<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataCSW
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('CSW');
        if ($tblConsumerCertificate){
            self::setCswMsHalbjahresinformation($Data, $tblConsumerCertificate);
            self::setCswMsJahreszeugnis($Data, $tblConsumerCertificate);
            self::setCswGsOneHjInfo($Data, $tblConsumerCertificate);
            self::setCswGsJOne($Data, $tblConsumerCertificate);
            self::setCswGsHjInfo($Data, $tblConsumerCertificate);
            self::setCswGsJ($Data, $tblConsumerCertificate);
            self::setCswMsHalbjahresinformationFsLernen($Data, $tblConsumerCertificate);
            self::setCswMsJFsLernen($Data, $tblConsumerCertificate);
        }

    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswMsHalbjahresinformation(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-9',
            'CSW\CswMsHalbjahresinformation', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(), null, true);
                // Muss Updatefähig sein, wenn es nicht alle Jahre von Anfang an gibt.
//                            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
//                            }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswMsJahreszeugnis(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-9',
            'CSW\CswMsJahreszeugnis', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypeSecondary(), null, false);
                // Muss Updatefähig sein, wenn es nicht alle Jahre von Anfang an gibt.
//                            if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
//                            }
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswGsOneHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
            'CSW\CswGsOneHjInfo', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
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

            $Data->createCertificateInformation($tblCertificate, 'StudentLetter', 2);
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswGsJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'CSW\CswGsJOne', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))) {
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

            $Data->createCertificateInformation($tblCertificate, 'StudentLetter', 2);
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswGsHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', '', 'CSW\CswGsHjInfo', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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

            $Data->createCertificateInformation($tblCertificate, 'StudentLetter', 2);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswGsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', '', 'CSW\CswGsJ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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

            $Data->createCertificateInformation($tblCertificate, 'StudentLetter', 2);
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
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswMsHalbjahresinformationFsLernen(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Mittelschule Halbjahresinformation', 'Förderschwerpunkt Lernen',
            'CSW\CswMsHalbjahresinformationFsLernen', $tblConsumerCertificate);
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
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCswMsJFsLernen(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Mittelschule Jahreszeugnis', 'Förderschwerpunkt Lernen',
            'CSW\CswMsJFsLernen', $tblConsumerCertificate);
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
}