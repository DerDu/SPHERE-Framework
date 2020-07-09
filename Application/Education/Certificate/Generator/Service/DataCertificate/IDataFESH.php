<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataFESH
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('FESH');
        if ($tblConsumerCertificate){
            self::setHorHj($Data, $tblConsumerCertificate);
            self::setHorHjOne($Data, $tblConsumerCertificate);
            self::setHorJ($Data, $tblConsumerCertificate);
            self::setHorJOne($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setHorHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Halbjahresinformation', '', 'FESH\HorHj', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)){
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)){
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 5);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setHorHjOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Halbjahresinformation', '1. Klasse', 'FESH\HorHjOne', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
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
    private static function setHorJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Jahreszeugnis', '', 'FESH\HorJ', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)){
            $Data->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$Data->getCertificateSubjectAll($tblCertificate)){
            $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $Data->setCertificateSubject($tblCertificate, 'EN', 1, 5);

            $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $Data->setCertificateSubject($tblCertificate, 'WE', 2, 2);
            $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 3);
            $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setHorJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Jahreszeugnis', '1. Klasse', 'FESH\HorJOne', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
    }
}