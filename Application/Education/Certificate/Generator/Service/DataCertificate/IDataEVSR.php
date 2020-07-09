<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataEVSR
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSR');
        if ($tblConsumerCertificate){
            self::setRadebeulOsHalbjahresinformation($Data, $tblConsumerCertificate);
            self::setRadebeulOsJahreszeugnis($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setRadebeulOsHalbjahresinformation(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', '',
            'EVSR\RadebeulOsHalbjahresinformation',
            $tblConsumerCertificate, false, true, false, $Data->getTblCertificateTypeHalfYear(),
            $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate){
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'CH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTD', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 8);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setRadebeulOsJahreszeugnis(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', '', 'EVSR\RadebeulOsJahreszeugnis',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeYear(),
            $Data->getTblSchoolTypeSecondary());
        if ($tblCertificate){
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'CH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'WTD', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 6);
                $Data->setCertificateSubject($tblCertificate, 'INF', 2, 7);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 8);
            }
        }
    }
}