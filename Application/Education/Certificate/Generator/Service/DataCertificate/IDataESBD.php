<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataESBD
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESBD');

        // set necessary Level for Certificate if not exist (important vor new Consumer without full level range on setup)
        // maybe remove after update on live
        Division::useService()->insertLevel($Data->getTblSchoolTypePrimary(), '1');
        Division::useService()->insertLevel($Data->getTblSchoolTypePrimary(), '2');
        Division::useService()->insertLevel($Data->getTblSchoolTypePrimary(), '3');
        Division::useService()->insertLevel($Data->getTblSchoolTypePrimary(), '4');

        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '5');
        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '6');
        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '7');
        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '8');
        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '9');
        Division::useService()->insertLevel($Data->getTblSchoolTypeSecondary(), '10');

        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '5');
        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '6');
        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '7');
        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '8');
        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '9');
        Division::useService()->insertLevel($Data->getTblSchoolTypeGym(), '10');

        if ($tblConsumerCertificate){
            self::setEsbdGsHjInformation($Data, $tblConsumerCertificate);
            self::setEsbdGsHjOneInfo($Data, $tblConsumerCertificate);
            self::setEsbdGsJa($Data, $tblConsumerCertificate);
            self::setEsbdGsJaFocusText($Data, $tblConsumerCertificate);
            self::setEsbdGsJOne($Data, $tblConsumerCertificate);
            self::setEsbdGymHjInfo($Data, $tblConsumerCertificate);
            self::setEsbdGymHj($Data, $tblConsumerCertificate);
            self::setEsbdGymJ($Data, $tblConsumerCertificate);
            self::setEsbdMsHjInfo($Data, $tblConsumerCertificate);
            self::setEsbdMsHj($Data, $tblConsumerCertificate);
            self::setEsbdMsJ($Data, $tblConsumerCertificate);
            self::setEsbdGymKurshalbjahreszeugnis($Data, $tblConsumerCertificate);
            self::setEsbdMsHjInfoFsGeistigeEntwicklung($Data, $tblConsumerCertificate);
            self::setEsbdMsHjFsGeistigeEntwicklung($Data, $tblConsumerCertificate);
            self::setEsbdMsJFsGeistigeEntwicklung($Data, $tblConsumerCertificate);
            self::setEsbdMsHjInfoFsLernen($Data, $tblConsumerCertificate);
            self::setEsbdMsHjFsLernen($Data, $tblConsumerCertificate);
            self::setEsbdMsJFsLernen($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGsHjInformation(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 4', 'ESBD\EsbdGsHjInformation', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
//            // Begrenzung des Bemerkungsfeld
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 1200);
//            }
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
    private static function setEsbdGsHjOneInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-3',
            'ESBD\EsbdGsHjOneInfo', $tblConsumerCertificate);
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
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGsJa(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 4', 'ESBD\EsbdGsJa', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
//            // Begrenzung des Bemerkungsfelds
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 700);
//            }
//            // Begrenzung des Einschätzungfelds
//            $FieldName = 'Rating';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 600);
//            }
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
    private static function setEsbdGsJaFocusText(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis (Corona-Vorlage) ', 'Klasse 4', 'ESBD\EsbdGsJaCorona', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
////            // Begrenzung des Bemerkungsfelds
//            $FieldName = 'Remark';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
//            }
            // Begrenzung des Einschätzungfelds // Die Zeichenzählung lassen wir erstmal weg, da Schule Workaround über eine Wordvorlage hat.
//            $FieldName = 'Rating';
//            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $Data->createCertificateField($tblCertificate, $FieldName, 2100);
//            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)) {

            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'EN', 2, 2);
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGsJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-3', 'ESBD\EsbdGsJOne', $tblConsumerCertificate);
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
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGymHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahresinformation', '', 'ESBD\EsbdGymHjInfo', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym(), null, true);
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
//                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGymHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Halbjahreszeugnis', '', 'ESBD\EsbdGymHj', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeGym());
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeGym(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
//                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 600);
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGymJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Jahreszeugnis', '', 'ESBD\EsbdGymJ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeGym()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeGym());
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
//                }
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

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-9', 'ESBD\EsbdMsHjInfo', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true);
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
//                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 9/10', 'ESBD\EsbdMsHj', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true);
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
//                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5 - 9', 'ESBD\EsbdMsJ', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary());
                // Update muss erneut ausführbar sein
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
//                }
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

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
//                $Data->setCertificateGradeAllStandard($tblCertificate);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdGymKurshalbjahreszeugnis(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Gymnasium Kurshalbjahreszeugnis', '', 'ESBD\EsbdGymKurshalbjahreszeugnis', $tblConsumerCertificate);
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

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
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
     * @param Data           $Data
     * @param TblCertificate $tblCertificate
     */
    private static function setESBDSecondPageLength(Data $Data, TblCertificate $tblCertificate)
    {

        $FieldNameYou = 'DialoguesWithYou';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldNameYou)) {
            $Data->createCertificateInformation($tblCertificate, $FieldNameYou, 2);
            $Data->createCertificateField($tblCertificate, $FieldNameYou, 1200);
        }
        $FieldNameParent = 'DialoguesWithParent';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldNameParent)) {
            $Data->createCertificateInformation($tblCertificate, $FieldNameParent, 2);
            $Data->createCertificateField($tblCertificate, $FieldNameParent, 1200);
        }
        $FieldNameUs = 'DialoguesWithUs';
        if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldNameUs)) {
            $Data->createCertificateInformation($tblCertificate, $FieldNameUs, 2);
            $Data->createCertificateField($tblCertificate, $FieldNameUs, 1200);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHjInfoFsGeistigeEntwicklung(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt geistige Entwicklung',
            'ESBD\EsbdMsHjInfoFsGeistigeEntwicklung', $tblConsumerCertificate
        );
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true, true);

                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
                }
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHjFsGeistigeEntwicklung(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt geistige Entwicklung',
            'ESBD\EsbdMsHjFsGeistigeEntwicklung', $tblConsumerCertificate
        );
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, false, true);

                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsJFsGeistigeEntwicklung(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt geistige Entwicklung',
            'ESBD\EsbdMsJFsGeistigeEntwicklung', $tblConsumerCertificate
        );
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(), null, false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
                }
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHjInfoFsLernen(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Förderschwerpunkt Lernen', 'ESBD\EsbdMsHjInfoFsLernen', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, true, true);
                // Update muss erneut ausführbar sein
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsHjFsLernen(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Förderschwerpunkt Lernen', 'ESBD\EsbdMsHjFsLernen', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeSecondary(), null, false, true);
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

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
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
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEsbdMsJFsLernen(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Förderschwerpunkt Lernen', 'ESBD\EsbdMsJFsLernen', $tblConsumerCertificate);
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeSecondary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeSecondary(), null, false, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
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

            // Seite 2
            // Begrenzung der Dialoge
            self::setESBDSecondPageLength($Data, $tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)) {
            $Data->setCertificateGrade($tblCertificate, 'PN', 1, 1);
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