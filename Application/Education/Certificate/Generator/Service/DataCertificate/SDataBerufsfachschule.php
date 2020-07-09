<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;

/**
 * Class SDataBerufsfachschule
 * @package SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate
 */
class SDataBerufsfachschule
{

    /**
     * @param Data $Data
     */
    public static function setCertificateStandard(Data $Data)
    {

        self::setBfsHj($Data);
        self::setBfsJ(($Data));
    }

    /**
     * @param Data $Data
     */
    private static function setBfsHj(Data $Data)
    {

        $tblCertificate = $Data->createCertificate('Berufsfachschule Halbjahresinformation', '',
            'BfsHj');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, true);
                // fortlaufend muss das ergänzt werden können. (einsteigende Schulen haben noch nicht alle Klassenstufen)
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '1'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '2'))) {
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 900);
            }
        }
    }

    /**
     * @param Data $Data
     */
    private static function setBfsJ(Data $Data)
    {
        $tblCertificate = $Data->createCertificate('Berufsfachschule Jahreszeugnis', '',
            'BfsJ');
        if ($tblCertificate) {
            if ($Data->getTblSchoolTypeBerufsfachschule()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypeBerufsfachschule(), null, false);
                // fortlaufend muss das ergänzt werden können. (einsteigende Schulen haben noch nicht alle Klassenstufen)
//                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '1'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeBerufsfachschule(), '2'))) {
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
//                }
            }
            // Begrenzung Eingabefelder
            // Begrenzung RemarkWithoutTeam
            $Var = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $Var)) {
                $Data->createCertificateField($tblCertificate, $Var, 300);
            }
        }
    }

}