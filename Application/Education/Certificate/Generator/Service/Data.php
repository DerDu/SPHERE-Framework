<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataCMS;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataCSW;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEMSP;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataESBD;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataESRL;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataESS;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataESZC;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEVAMTL;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEVGSM;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEVSC;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEVSR;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataEZSH;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataFELS;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataFESH;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\IDataLWSZ;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\SDataBerufsfachschule;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\SDataGym;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\SDataPrimary;
use SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate\SDataSecondary;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateField;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateInformation;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateReferenceForLanguages;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
 */
class Data extends AbstractData
{

    private $tblCertificateTypeHalfYear;
    private $tblCertificateTypeYear;
    private $tblCertificateTypeGradeInformation;
    private $tblCertificateTypeRecommendation;
    private $tblCertificateTypeLeave;
    private $tblCertificateTypeDiploma;
    private $tblCertificateTypeMidTermCourse;
    private $tblSchoolTypePrimary;
    private $tblSchoolTypeSecondary;
    private $tblSchoolTypeGym;
    private $tblSchoolTypeBerufsfachschule;
    private $tblCourseMain;
    private $tblCourseReal;
    private $tblConsumer;

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeHalfYear()
    {
        return $this->tblCertificateTypeHalfYear;
    }

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeYear()
    {
        return $this->tblCertificateTypeYear;
    }

    /**
     * @return mixed
     */
    public function getTblCertificateTypeGradeInformation()
    {
        return $this->tblCertificateTypeGradeInformation;
    }

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeRecommendation()
    {
        return $this->tblCertificateTypeRecommendation;
    }

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeLeave()
    {
        return $this->tblCertificateTypeLeave;
    }

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeDiploma()
    {
        return $this->tblCertificateTypeDiploma;
    }

    /**
     * @return TblCertificateType
     */
    public function getTblCertificateTypeMidTermCourse()
    {
        return $this->tblCertificateTypeMidTermCourse;
    }

    /**
     * @return TblType
     */
    public function getTblSchoolTypePrimary()
    {
        return $this->tblSchoolTypePrimary;
    }

    /**
     * @return TblType
     */
    public function getTblSchoolTypeSecondary()
    {
        return $this->tblSchoolTypeSecondary;
    }

    /**
     * @return TblType
     */
    public function getTblSchoolTypeGym()
    {
        return $this->tblSchoolTypeGym;
    }

    /**
     * @return TblType
     */
    public function getTblSchoolTypeBerufsfachschule()
    {
        return $this->tblSchoolTypeBerufsfachschule;
    }

    /**
     * @return mixed
     */
    public function getTblCourseMain()
    {
        return $this->tblCourseMain;
    }

    /**
     * @return TblCourse
     */
    public function getTblCourseReal()
    {
        return $this->tblCourseReal;
    }

    /**
     * @return TblConsumer
     */
    public function getTblConsumer()
    {
        return $this->tblConsumer;
    }

    public function setupDatabaseContent()
    {

        // Informationen der Zeugnisse
        $this->tblCertificateTypeHalfYear = $this->createCertificateType('Halbjahresinformation/Halbjahreszeugnis', 'HALF_YEAR');
        $this->tblCertificateTypeYear = $this->createCertificateType('Jahreszeugnis', 'YEAR');
        $this->tblCertificateTypeGradeInformation = $this->createCertificateType('Noteninformation', 'GRADE_INFORMATION');
        $this->tblCertificateTypeRecommendation = $this->createCertificateType('Bildungsempfehlung', 'RECOMMENDATION');
        $this->tblCertificateTypeLeave = $this->createCertificateType('Abgangszeugnis', 'LEAVE');
        $this->tblCertificateTypeDiploma = $this->createCertificateType('Abschlusszeugnis', 'DIPLOMA');
        $this->tblCertificateTypeMidTermCourse = $this->createCertificateType('Kurshalbjahreszeugnis', 'MID_TERM_COURSE');
        $this->tblSchoolTypePrimary = Type::useService()->getTypeByName('Grundschule');
        $this->tblSchoolTypeSecondary = Type::useService()->getTypeByName('Mittelschule / Oberschule');
        $this->tblSchoolTypeGym = Type::useService()->getTypeByName('Gymnasium');
        $this->tblSchoolTypeBerufsfachschule = Type::useService()->getTypeByName('Berufsfachschule');
        $this->tblCourseMain = Course::useService()->getCourseByName('Hauptschule');
        $this->tblCourseReal = Course::useService()->getCourseByName('Realschule');
        $tblConsumer = $this->tblConsumer = Consumer::useService()->getConsumerBySession();

        $this->setCertificateGradeInformation($tblConsumer);

        if ($tblConsumer) {
            if ($tblConsumer->getAcronym() == 'ESZC') {
                IDataESZC::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EVSC') {
                IDataEVSC::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'FESH') {
                IDataFESH::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EVSR') {
                IDataEVSR::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'ESS') {
                IDataESS::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EVAMTL') {
                IDataEVAMTL::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'ESRL') {
                IDataESRL::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'CMS') {
                IDataCMS::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EZSH') {
                IDataEZSH::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'CSW') {
                IDataCSW::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EVGSM') {
                IDataEVGSM::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'ESBD') { //  || $tblConsumer->getAcronym() == 'REF' // local Test
                IDataESBD::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'FELS') {
                IDataFELS::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'LWSZ') {
                IDataLWSZ::setCertificateIndividually($this);
            }
            if ($tblConsumer->getAcronym() == 'EMSP') {
                IDataEMSP::setCertificateIndividually($this);
            }
        }
    }

    /**
     * @param $Type
     *
     * @return bool
     */
    public function insertCertificate($Type)
    {

        // Informationen der Zeugnisse
        $this->tblCertificateTypeHalfYear = $this->createCertificateType('Halbjahresinformation/Halbjahreszeugnis', 'HALF_YEAR');
        $this->tblCertificateTypeYear = $this->createCertificateType('Jahreszeugnis', 'YEAR');
        $this->tblCertificateTypeGradeInformation = $this->createCertificateType('Noteninformation', 'GRADE_INFORMATION');
        $this->tblCertificateTypeRecommendation = $this->createCertificateType('Bildungsempfehlung', 'RECOMMENDATION');
        $this->tblCertificateTypeLeave = $this->createCertificateType('Abgangszeugnis', 'LEAVE');
        $this->tblCertificateTypeDiploma = $this->createCertificateType('Abschlusszeugnis', 'DIPLOMA');
        $this->tblCertificateTypeMidTermCourse = $this->createCertificateType('Kurshalbjahreszeugnis', 'MID_TERM_COURSE');
        $this->tblSchoolTypePrimary = Type::useService()->getTypeByName('Grundschule');
        $this->tblSchoolTypeSecondary = Type::useService()->getTypeByName('Mittelschule / Oberschule');
        $this->tblSchoolTypeGym = Type::useService()->getTypeByName('Gymnasium');
        $this->tblSchoolTypeBerufsfachschule = Type::useService()->getTypeByName('Berufsfachschule');
        $this->tblCourseMain = Course::useService()->getCourseByName('Hauptschule');
        $this->tblCourseReal = Course::useService()->getCourseByName('Realschule');

        switch ($Type) {
            case TblCertificate::CERTIFICATE_TYPE_PRIMARY :
                SDataPrimary::setCertificateStandard($this);
                return true;
            break;
            case TblCertificate::CERTIFICATE_TYPE_SECONDARY :
                SDataSecondary::setCertificateStandard($this);
                return true;
            break;
            case TblCertificate::CERTIFICATE_TYPE_GYM :
                SDataGym::setCertificateStandard($this);
                return true;
            break;
            case TblCertificate::CERTIFICATE_TYPE_BERUFSFACHSCHULE :
                SDataBerufsfachschule::setCertificateStandard($this);
                return true;
            break;
        }
        return false;
    }

    /**
     * @param TblConsumer        $tblConsumer
     */
    private function setCertificateGradeInformation(TblConsumer $tblConsumer)
    {

        /*
         * Noteninformation
         */
        $tblCertificate = $this->createCertificate('Noteninformation', '',
            'GradeInformation', null, true);
        if ($tblCertificate) {
            $this->updateCertificate($tblCertificate, $this->tblCertificateTypeGradeInformation, null, null, true);
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            if ($tblConsumer->getAcronym() == 'ESZC') {
                $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BIO', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'TC', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'RE/e', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            } else {
                $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BIO', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'INF', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'RE/e', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            }
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Certificate
     * @param TblConsumer|null $tblConsumer
     * @param bool $IsGradeInformation
     * @param bool $IsInformation
     * @param bool $IsChosenDefault
     * @param TblCertificateType|null $tblCertificateType
     * @param TblType|null $tblSchoolType
     * @param TblCourse|null $tblCourse
     * @param bool $IsIgnoredForAutoSelect
     *
     * @return TblCertificate
     */
    public function createCertificate(
        $Name,
        $Description,
        $Certificate,
        TblConsumer $tblConsumer = null,
        $IsGradeInformation = false,
        $IsInformation = false,
        $IsChosenDefault = false,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null,
        TblCourse $tblCourse = null,
        $IsIgnoredForAutoSelect = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array(
            TblCertificate::ATTR_CERTIFICATE => $Certificate
        ));

        if (null === $Entity) {
            $Entity = new TblCertificate();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCertificate($Certificate);
            $Entity->setServiceTblConsumer($tblConsumer);
            $Entity->setIsGradeInformation($IsGradeInformation);
            $Entity->setIsInformation($IsInformation);
            $Entity->setIsChosenDefault($IsChosenDefault);
            $Entity->setTblCertificateType($tblCertificateType);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setIsIgnoredForAutoSelect($IsIgnoredForAutoSelect);
            // Standard, kann wenn benötigt als Variable in die Funktion, noch kein bedarf dazu
            $Entity->setIsGradeVerbal(false);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblGradeType $tblGradeType
     *
     * @return null|object|TblCertificateGrade
     */
    public function createCertificateGrade(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblGradeType $tblGradeType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array(
            TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateGrade::ATTR_LANE => $LaneIndex,
            TblCertificateGrade::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateGrade();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblGradeType($tblGradeType);
            $Entity->setEssential(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function updateCertificateGrade(TblCertificateGrade $tblCertificateGrade, TblGradeType $tblGradeType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateGrade $Entity */
        $Entity = $Manager->getEntityById('TblCertificateGrade', $tblCertificateGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblGradeType($tblGradeType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return TblCertificateSubject
     */
    public function createCertificateSubject(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array(
            TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateSubject::ATTR_LANE => $LaneIndex,
            TblCertificateSubject::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateSubject();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return bool
     */
    public function updateCertificateSubject(
        TblCertificateSubject $tblCertificateSubject,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function removeCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return false|TblCertificate[]
     */
    public function getTemplateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate');
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null)
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }


    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        /** @var TblCertificate $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificate', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_CERTIFICATE => $Class
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectById($Id)
    {

        /** @var TblCertificateSubject $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateSubject', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(TblCertificate $tblCertificate, TblSubject $tblSubject)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateSubject',
            array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::ATTR_LANE => $LaneIndex,
                TblCertificateSubject::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateGrade::ATTR_LANE => $LaneIndex,
                TblCertificateGrade::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeById($Id)
    {

        /** @var TblCertificateGrade $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $SubjectAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     * @param bool|true $IsEssential
     */
    public function setCertificateSubject(
        TblCertificate $tblCertificate,
        $SubjectAcronym,
        $LaneIndex,
        $LaneRanking,
        $IsEssential = true
    ) {

        // abweichende Fächer
        if ($SubjectAcronym == 'DE' || $SubjectAcronym == 'D' || $SubjectAcronym == 'DEU') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('DE');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('D');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('DEU');
            }
        } elseif ($SubjectAcronym == 'EN' || $SubjectAcronym == 'ENG') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('ENG');
            }
        } elseif ($SubjectAcronym == 'BI' || $SubjectAcronym == 'BIO') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('BIO');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('BI');
            }
        } elseif ($SubjectAcronym == 'REV' || $SubjectAcronym == 'RELI' || $SubjectAcronym == 'REE' || $SubjectAcronym == 'RE/e') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('RE/e');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('RELI');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('REE');
            }
        } elseif ($SubjectAcronym == 'REK' || $SubjectAcronym == 'RE/k') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('RE/k');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('REK');
            }
        } elseif ($SubjectAcronym == 'IN' || $SubjectAcronym == 'INFO' || $SubjectAcronym == 'INF') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('INF');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('IN');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('INFO');
            }
        } elseif ($SubjectAcronym == 'WK' || $SubjectAcronym == 'WE') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('WE');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('WK');
            }
        } else {
            $tblSubject = Subject::useService()->getSubjectByAcronym($SubjectAcronym);
        }

        if ($tblSubject){
            $this->createCertificateSubject($tblCertificate, $LaneIndex, $LaneRanking, $tblSubject,
                $IsEssential);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $GradeTypeAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     */
    public function setCertificateGrade(
        TblCertificate $tblCertificate,
        $GradeTypeAcronym,
        $LaneIndex,
        $LaneRanking
    ) {

        if (($tblGradeType = Gradebook::useService()->getGradeTypeByCode($GradeTypeAcronym))) {
            $this->createCertificateGrade($tblCertificate, $LaneIndex, $LaneRanking, $tblGradeType);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     */
    public function setCertificateGradeAllStandard(
        TblCertificate $tblCertificate
    ) {
        $this->setCertificateGrade($tblCertificate, 'KBE', 1, 1);
        $this->setCertificateGrade($tblCertificate, 'KFL', 1, 2);

        $this->setCertificateGrade($tblCertificate, 'KMI', 2, 1);
        $this->setCertificateGrade($tblCertificate, 'KOR', 2, 2);
    }


    /**
     * @param $Identifier
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            array(
                TblCertificateType::ATTR_IDENTIFIER => strtoupper($Identifier)
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            $Id
        );
    }

    /**
     * @return false|TblCertificateType[]
     */
    public function getCertificateTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType', array(
            TblCertificateType::ATTR_NAME => self::ORDER_ASC
        ));
    }

    /**
     * @param $Name
     * @param $Identifier
     * @param bool $IsAutomaticallyApproved
     *
     * @return null|TblCertificateType
     */
    public function createCertificateType($Name, $Identifier, $IsAutomaticallyApproved = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateType')
            ->findOneBy(array(TblCertificateType::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblCertificateType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));
            $Entity->setAutomaticallyApproved($IsAutomaticallyApproved);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblCertificateType|null $tblCertificateType
     * @param TblType|null $tblSchoolType
     * @param TblCourse|null $tblCourse
     * @param bool $IsInformation (Halbjahres Information)
     * @param bool $IsIgnoredForAutoSelect
     *
     * @return bool
     */
    public function updateCertificate(
        TblCertificate $tblCertificate,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null,
        TblCourse $tblCourse = null,
        $IsInformation = false,
        $IsIgnoredForAutoSelect = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntityById('TblCertificate', $tblCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setTblCertificateType($tblCertificateType);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setIsInformation($IsInformation);
            $Entity->setIsIgnoredForAutoSelect($IsIgnoredForAutoSelect);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $Name
     * @param $Description
     *
     * @return bool
     */
    public function updateCertificateName(
        TblCertificate $tblCertificate,
        $Name,
        $Description
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntityById('TblCertificate', $tblCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setName($Name);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblLevel $tblLevel
     *
     * @return TblCertificateLevel
     */
    public function createCertificateLevel(TblCertificate $tblCertificate, TblLevel $tblLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateLevel')
            ->findOneBy(array(
                    TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                    TblCertificateLevel::SERVICE_TBL_LEVEL => $tblLevel->getId()
                )
            );

        if (null === $Entity) {
            $Entity = new TblCertificateLevel();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setServiceTblLevel($tblLevel);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool
     */
    public function destroyCertificate(TblCertificate $tblCertificate)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array('Id' => $tblCertificate->getId()));
        if (null !== $Entity) {
            // Foreign-Key Verknüpfungen löschen
            if (($tblCertificateGradeList = $this->getCertificateGradeAll($Entity))) {
                foreach ($tblCertificateGradeList as $tblCertificateGrade) {
                    $this->destroyCertificateGrade($tblCertificateGrade);
                }
            }
            if (($tblCertificateSubjectList = $this->getCertificateSubjectAll($Entity))) {
                foreach ($tblCertificateSubjectList as $tblCertificateSubject) {
                    $this->destroyCertificateSubject($tblCertificateSubject);
                }
            }
            if (($tblCertificateLevelList = $this->getCertificateLevelAllByCertificate($Entity))){
                foreach ($tblCertificateLevelList as $tblCertificateLevel) {
                    $this->destroyCertificateLevel($tblCertificateLevel);
                }
            }
            if (($tblCertificateFieldList = $this->getCertificateFieldAllByCertificate($Entity))){
                foreach ($tblCertificateFieldList as $tblCertificateField) {
                    $this->destroyCertificateField($tblCertificateField);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     *
     * @return bool
     */
    public function destroyCertificateGrade(TblCertificateGrade $tblCertificateGrade)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array('Id' => $tblCertificateGrade->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function destroyCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array('Id' => $tblCertificateSubject->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     *
     * @return bool|Entity\TblCertificate[]
     */
    public function getCertificateAllByConsumerAndCertificateType(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => ($tblCertificateType ? $tblCertificateType->getId() : null),
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|Entity\TblCertificate[]
     */
    public function getCertificateAllBy(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => ($tblCertificateType ? $tblCertificateType->getId() : null),
                TblCertificate::SERVICE_TBL_SCHOOL_TYPE => ($tblSchoolType ? $tblSchoolType->getId() : null),
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|Entity\TblCertificate[]
     */
    public function getCertificateAllForAutoSelect(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => ($tblCertificateType ? $tblCertificateType->getId() : null),
                TblCertificate::SERVICE_TBL_SCHOOL_TYPE => ($tblSchoolType ? $tblSchoolType->getId() : null),
                TblCertificate::ATTR_IS_IGNORED_FOR_AUTO_SELECT => false
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateLevel[]
     */
    public function getCertificateLevelAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateLevel', array(
                TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param bool $HasTeamInRemark
     *
     * @return false|int
     */
    public function getCharCountByCertificateAndField(TblCertificate $tblCertificate, $FieldName, $HasTeamInRemark = true)
    {

        $tblCertificateField = $this->getCertificateFieldByCertificateAndField(
            $tblCertificate, $FieldName
        );

        if ($tblCertificateField) {
            // 3 Zeile (300 Zeichen) für Arbeitsgemeinschaften und Abstand abziehen
            if ($FieldName == 'Remark' && $HasTeamInRemark){
                $count = $tblCertificateField->getCharCount();
                return  $count > 300 ? $count - 300 : $count;
                // Abstand abziehen
            } elseif ($FieldName == 'Remark'){
                $count = $tblCertificateField->getCharCount();
                return  $count > 100 ? $count - 100 : $count;
            } else {
                return $tblCertificateField->getCharCount();
            }
        }

        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $FieldName
     * @return false|TblCertificateField
     */
    public function getCertificateFieldByCertificateAndField(TblCertificate $tblCertificate, $FieldName)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateField', array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            )
        );
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param integer $CharCount
     *
     * @return TblCertificateField
     */
    public function createCertificateField(TblCertificate $tblCertificate, $FieldName, $CharCount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')
            ->findOneBy(array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            ));

        if (null === $Entity) {
            $Entity = new TblCertificateField();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setFieldName($FieldName);
            $Entity->setCharCount($CharCount);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificateLevel $tblCertificateLevel
     *
     * @return bool
     */
    public function destroyCertificateLevel(TblCertificateLevel $tblCertificateLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateLevel')->findOneBy(array('Id' => $tblCertificateLevel->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateField $tblCertificateField
     *
     * @return bool
     */
    public function destroyCertificateField(TblCertificateField $tblCertificateField)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')->findOneBy(array('Id' => $tblCertificateField->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateField[]
     */
    public function getCertificateFieldAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateField',
            array(TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId())
        );
    }

    /**
     * @param TblCertificateType $tblCertificateType
     *
     * @return false|TblCertificate[]
     */
    public function getCertificateAllByType(
        TblCertificateType $tblCertificateType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => $tblCertificateType->getId()
            )
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblCertificateGrade',
            array(
                TblCertificateGrade::SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId()
            )
        ) ? true : false;
    }

    /**
     * @param TblCertificateType $tblCertificateType
     * @param $Identifier
     * @param $Name
     * @param $IsAutomaticallyApproved
     *
     * @return bool
     */
    public function updateCertificateType(
        TblCertificateType $tblCertificateType,
        $Identifier,
        $Name,
        $IsAutomaticallyApproved
    ) {

        $Manager = $this->getEntityManager();
        /** @var TblCertificateType $Entity */
        $Entity = $Manager->getEntityById('TblCertificateType', $tblCertificateType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Entity->setAutomaticallyApproved($IsAutomaticallyApproved);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblLevel $tblLevel
     *
     * @return false|TblCertificateLevel
     */
    public function getCertificateLevelBy(TblCertificate $tblCertificate, TblLevel $tblLevel)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblCertificateLevel', array(
            TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateLevel::SERVICE_TBL_LEVEL => $tblLevel->getId()
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateReferenceForLanguages[]
     */
    public function getCertificateReferenceForLanguagesAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblCertificateReferenceForLanguages', array(
           TblCertificateReferenceForLanguages::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $languageRanking
     *
     * @return false|TblCertificateReferenceForLanguages
     */
    public function getCertificateReferenceForLanguagesByCertificateAndRanking(TblCertificate $tblCertificate, $languageRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblCertificateReferenceForLanguages', array(
            TblCertificateReferenceForLanguages::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateReferenceForLanguages::ATTR_LANGUAGE_RANKING => $languageRanking
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $languageRanking
     * @param $toLevel10
     * @param $afterBasicCourse
     * @param $afterAdvancedCourse
     *
     * @return TblCertificateReferenceForLanguages
     */
    public function createCertificateReferenceForLanguages(
        TblCertificate $tblCertificate,
        $languageRanking,
        $toLevel10,
        $afterBasicCourse,
        $afterAdvancedCourse
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateReferenceForLanguages')
            ->findOneBy(array(
                TblCertificateReferenceForLanguages::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateReferenceForLanguages::ATTR_LANGUAGE_RANKING => $languageRanking
            ));

        if (null === $Entity) {
            $Entity = new TblCertificateReferenceForLanguages();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLanguageRanking($languageRanking);
            $Entity->setToLevel10($toLevel10);
            $Entity->setAfterBasicCourse($afterBasicCourse);
            $Entity->setAfterAdvancedCourse($afterAdvancedCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificateReferenceForLanguages $tblCertificateReferenceForLanguages
     * @param $toLevel10
     * @param $afterBasicCourse
     * @param $afterAdvancedCourse
     *
     * @return bool
     */
    public function updateCertificateReferenceForLanguages(
        TblCertificateReferenceForLanguages $tblCertificateReferenceForLanguages,
        $toLevel10,
        $afterBasicCourse,
        $afterAdvancedCourse
    ) {

        $Manager = $this->getEntityManager();
        /** @var TblCertificateReferenceForLanguages $Entity */
        $Entity = $Manager->getEntityById('TblCertificateReferenceForLanguages', $tblCertificateReferenceForLanguages->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setToLevel10($toLevel10);
            $Entity->setAfterBasicCourse($afterBasicCourse);
            $Entity->setAfterAdvancedCourse($afterAdvancedCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $fieldName
     * @param integer $page
     * @return TblCertificateInformation|object|null
     */
    public function createCertificateInformation(
        TblCertificate $tblCertificate,
        $fieldName,
        $page
    ) {

        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateInformation')
            ->findOneBy(array(
                TblCertificateInformation::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateInformation::ATTR_FIELD_NAME => $fieldName
            ));

        if (null === $Entity) {
            $Entity = new TblCertificateInformation();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setFieldName($fieldName);
            $Entity->setPage($page);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $fieldName
     *
     * @return false|TblCertificateInformation
     */
    public function getCertificateInformationByField(
        TblCertificate $tblCertificate,
        $fieldName
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblCertificateInformation', array(
            TblCertificateInformation::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateInformation::ATTR_FIELD_NAME => $fieldName
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateInformation[]
     */
    public function getCertificateInformationListByCertificate(
        TblCertificate $tblCertificate
    ) {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblCertificateInformation', array(
            TblCertificateInformation::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
        ), array(TblCertificateInformation::ATTR_PAGE => self::ORDER_ASC));
    }
}
