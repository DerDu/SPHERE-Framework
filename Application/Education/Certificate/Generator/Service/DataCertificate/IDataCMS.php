<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataCMS
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('CMS');
        if ($tblConsumerCertificate){
            self::setCmsGsHjOneTwo($Data, $tblConsumerCertificate);
            self::setCmsGsHjOneTwoExt($Data, $tblConsumerCertificate);
            self::setCmsGsJOneTwo($Data, $tblConsumerCertificate);
            self::setCmsGsJOneTwoExt($Data, $tblConsumerCertificate);
            self::setCmsGsHj($Data, $tblConsumerCertificate);
            self::setCmsGsHjExt($Data, $tblConsumerCertificate);
            self::setCmsGsJ($Data, $tblConsumerCertificate);
            self::setCmsGsJExt($Data, $tblConsumerCertificate);
            self::setCmsMsHj($Data, $tblConsumerCertificate);
            self::setCmsMsHjExt($Data, $tblConsumerCertificate);
            self::setCmsMsHjZ($Data, $tblConsumerCertificate);
            self::setCmsMsHjZExt($Data, $tblConsumerCertificate);
            self::setCmsMsJ($Data, $tblConsumerCertificate);
            self::setCmsMsJExt($Data, $tblConsumerCertificate);
            self::setCmsMsHjBeiblatt($Data, $tblConsumerCertificate);
            self::setCmsMsJBeiblatt($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsHjOneTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-2',
            'CMS\CmsGsHjOneTwo', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2600);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsHjOneTwoExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation (2 Seiten)',
            'Klasse 1-2',
            'CMS\CmsGsHjOneTwoExt', $tblConsumerCertificate, false, true, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3600);
            }
            // Begrenzung des Bemerkungsfelds der 2.ten Seite
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3700);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsJOneTwo(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-2',
            'CMS\CmsGsJOneTwo', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2600);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsJOneTwoExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis (2 Seiten)', 'Klasse 1-2',
            'CMS\CmsGsJOneTwoExt', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '1'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '2'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3600);
            }
            // Begrenzung des Bemerkungsfelds der 2.ten Seite
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3500);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 3-4',
            'CMS\CmsGsHj', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2100);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsHjExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation (2 Seiten)',
            'Klasse 3-4',
            'CMS\CmsGsHjExt', $tblConsumerCertificate, false, true, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypePrimary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2900);
            }
            // Begrenzung des Bemerkungsfelds der 2.ten Seite
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3700);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Jahreszeugnis Grundschule ', 'Klasse 3-4',
            'CMS\CmsGsJ', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1900);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsGsJExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Jahreszeugnis Grundschule (2 Seiten)', 'Klasse 3-4',
            'CMS\CmsGsJExt', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypePrimary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '3'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypePrimary(), '4'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2900);
            }
            // Begrenzung des Bemerkungsfelds der 2.ten Seite
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3500);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-9',
            'CMS\CmsMsHj', $tblConsumerCertificate, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 900);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsHjExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahresinformation (2 Seiten)',
            'Klasse 5-9',
            'CMS\CmsMsHjExt', $tblConsumerCertificate, false, true, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary(),
                    null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1800);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3700);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsHjZ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 9-10',
            'CMS\CmsMsHjZ', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 900);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsHjZExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Halbjahreszeugnis (2 Seiten)', 'Klasse 9-10',
            'CMS\CmsMsHjZExt', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(),
                    $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '10'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1800);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3700);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-9',
            'CMS\CmsMsJ', $tblConsumerCertificate);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 700);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsJExt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Oberschule Jahreszeugnis (2 Seiten)', 'Klasse 5-9',
            'CMS\CmsMsJExt', $tblConsumerCertificate, false, false, true);
        if ($tblCertificate){
            if ($Data->getTblSchoolTypeSecondary()){
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(),
                    $Data->getTblSchoolTypeSecondary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)){
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '5'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '6'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '7'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '8'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($Data->getTblSchoolTypeSecondary(), '9'))){
                        $Data->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 1800);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'SecondRemark';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 3500);
            }
            // Kopfnoten
            if (!$Data->getCertificateGradeAll($tblCertificate)){
                $Data->setCertificateGradeAllStandard($tblCertificate);
            }
            if (!$Data->getCertificateSubjectAll($tblCertificate)){
                $Data->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $Data->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                $Data->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                $Data->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                $Data->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $Data->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $Data->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                $Data->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                $Data->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                $Data->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                $Data->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                $Data->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                $Data->setCertificateSubject($tblCertificate, 'RE/e', 2, 5, false);
                $Data->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                $Data->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                $Data->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                $Data->setCertificateSubject($tblCertificate, 'CH', 2, 9);
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsHjBeiblatt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $Data->createCertificate('Oberschule Halbjahresinformation Beiblatt', 'ab Klasse 8', 'CMS\CmsMsHjBeiblatt',
            $tblConsumerCertificate, false, true, false, $Data->getTblCertificateTypeHalfYear(),
            $Data->getTblSchoolTypeSecondary(), null, true);
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setCmsMsJBeiblatt(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $Data->createCertificate('Oberschule Jahreszeugnis Beiblatt', 'ab Klasse 8', 'CMS\CmsMsJBeiblatt',
            $tblConsumerCertificate, false, false, false, $Data->getTblCertificateTypeYear(),
            $Data->getTblSchoolTypeSecondary(), null, true);
    }
}