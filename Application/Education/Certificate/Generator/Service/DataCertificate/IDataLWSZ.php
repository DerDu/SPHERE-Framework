<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataLWSZ
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('LWSZ');
        if ($tblConsumerCertificate){
            self::setLwszGsOneHjInfo($Data, $tblConsumerCertificate);
            self::setLwszGsJOne($Data, $tblConsumerCertificate);
            self::setLwszGsHjInfo($Data, $tblConsumerCertificate);
            self::setLwszGsJ($Data, $tblConsumerCertificate);
        }

    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setLwszGsOneHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-2',
            'LWSZ\LwszGsOneHjInfo', $tblConsumerCertificate);
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
    private static function setLwszGsJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-2', 'LWSZ\LwszGsJOne', $tblConsumerCertificate);
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
    private static function setLwszGsHjInfo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 3-4', 'LWSZ\LwszGsHjInfo', $tblConsumerCertificate);
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
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'REV', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
        }
    }
    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setLwszGsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 3-4', 'LWSZ\LwszGsJ', $tblConsumerCertificate);
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
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'REV', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 4);
        }
    }
}