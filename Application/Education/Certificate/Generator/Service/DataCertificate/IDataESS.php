<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataESS
{
    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESS');
        if ($tblConsumerCertificate){
            self::setEssGsHjOne($Data, $tblConsumerCertificate);
            self::setEssGsJOne($Data, $tblConsumerCertificate);
            self::setEssGsHjTwo($Data, $tblConsumerCertificate);
            self::setEssGsJTwo($Data, $tblConsumerCertificate);
            self::setEssGsHjThree($Data, $tblConsumerCertificate);
            self::setEssGsJThree($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsHjOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Halbjahresinformation', '1. Klasse', 'ESS\EssGsHjOne', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2300);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsJOne(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Jahreszeugnis', '1. Klasse', 'ESS\EssGsJOne', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, false);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1600);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsHjTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Halbjahresinformation', '2. Klasse', 'ESS\EssGsHjTwo', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 820);
            }
            $FieldName = 'TechnicalRating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1200);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsJTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Jahreszeugnis', '2. Klasse', 'ESS\EssGsJTwo', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, false);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 820);
            }
            $FieldName = 'TechnicalRating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1050);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsHjThree(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Halbjahresinformation', '3. Klasse', 'ESS\EssGsHjThree', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
                // Begrenzung des Bemerkungsfelds
                $FieldName = 'Rating';
                if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                    $Data->createCertificateField($tblCertificate, $FieldName, 270);
                }
            }
            // Kopfnoten Setzen
            if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            //Fächer setzen
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
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEssGsJThree(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate(
            'Jahreszeugnis', '3. Klasse', 'ESS\EssGsJThree', $tblConsumerCertificate
        );
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, false);
                if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                    $Data->createCertificateLevel($tblCertificate, $tblLevel);
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Rating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 430);
            }
            $FieldName = 'TechnicalRating';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 450);
            }

            // Kopfnoten Setzen
            if ($tblCertificate && !$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            //Fächer setzen
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
    }
}