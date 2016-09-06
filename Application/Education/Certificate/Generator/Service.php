<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->getCertificateAllByConsumer($tblConsumer);
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return (new Data($this->getBinding()))->getCertificateAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        return (new Data($this->getBinding()))->getCertificateById($Id);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return (new Data($this->getBinding()))->getCertificateByCertificateClassName($Class);

    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate)
    {

        return (new Data($this->getBinding()))->getCertificateSubjectAll($tblCertificate);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {

        return (new Data($this->getBinding()))->getCertificateGradeAll($tblCertificate);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblCertificate      $tblCertificate
     * @param array               $GradeList
     * @param array               $SubjectList
     *
     * @return IFormInterface|string
     */
    public function createCertificateSetting(
        IFormInterface $Form,
        TblCertificate $tblCertificate,
        $GradeList,
        $SubjectList
    ) {

        /**
         * Skip to Frontend
         */
        if (empty( $GradeList ) && empty( $SubjectList )) {
            return $Form;
        }

        $Error = array();

        // Kopf-Noten
        foreach ($GradeList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (( $tblGradeType = Gradebook::useService()->getGradeTypeById($Field['GradeType']) )) {
                    $tblCertificateGrade = Generator::useService()->getCertificateGradeByIndex(
                        $tblCertificate, $LaneIndex, $LaneRanking
                    );
                    if ($tblCertificateGrade) {
                        // Update
                        (new Data($this->getBinding()))->updateCertificateGrade($tblCertificateGrade, $tblGradeType);
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateGrade($tblCertificate, $LaneIndex,
                            $LaneRanking, $tblGradeType);
                    }
                } else {
                    if ($Field['GradeType'] > 0) {
                        array_push($Error,
                            'Eine Notenangabe an der Position '.$LaneIndex.':'.$LaneRanking.' konnte nicht gespeichert werden'
                        );
                    }
                }
            }
        }

        // Fach-Noten
        foreach ($SubjectList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (( $tblSubject = Subject::useService()->getSubjectById($Field['Subject']) )) {
                    $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                        $tblCertificate, $LaneIndex, $LaneRanking
                    );
                    if ($tblCertificateSubject) {
                        // Update
                        (new Data($this->getBinding()))->updateCertificateSubject($tblCertificateSubject,
                            $tblSubject,
                            ( ( isset( $Field['IsEssential'] ) && $Field['IsEssential'] ) ? true : false ),
                            ( ( isset( $Field['Liberation'] ) && $Field['Liberation'] )
                                ? ( Student::useService()->getStudentLiberationCategoryById( $Field['Liberation'] )
                                    ? Student::useService()->getStudentLiberationCategoryById( $Field['Liberation'] )
                                    : null
                                )
                                : null
                            )
                        );
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateSubject($tblCertificate,
                            $LaneIndex, $LaneRanking, $tblSubject,
                            ( ( isset( $Field['IsEssential'] ) && $Field['IsEssential'] ) ? true : false ),
                            ( ( isset( $Field['Liberation'] ) && $Field['Liberation'] )
                                ? ( Student::useService()->getStudentLiberationCategoryById( $Field['Liberation'] )
                                    ? Student::useService()->getStudentLiberationCategoryById( $Field['Liberation'] )
                                    : null
                                )
                                : null
                            )
                        );
                    }
                } else {
                    if ($Field['Subject'] > 0) {
                        array_push($Error,
                            'Eine Fachangabe an der Position '.$LaneIndex.':'.$LaneRanking.' konnte nicht gespeichert werden'
                        );
                    } else {
                        if(($tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                            $tblCertificate, $LaneIndex, $LaneRanking
                        ))) {
                            (new Data($this->getBinding()))->removeCertificateSubject($tblCertificateSubject);
                        }
                    }
                }
            }
        }

        if (empty( $Error )) {
            return new Success(new Enable().' Die Einstellungen wurden gespeichert')
            .new Redirect('/Education/Certificate/Setting', Redirect::TIMEOUT_SUCCESS);
        } else {
            // TODO Show $Error List
            return new Danger(new Disable().' Eine oder mehrere Einstellungen wurden nicht gespeichert!')
            .new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_ERROR,
                array('Certificate' => $tblCertificate->getId()));
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int            $LaneIndex
     * @param int            $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return (new Data($this->getBinding()))->getCertificateGradeByIndex($tblCertificate, $LaneIndex, $LaneRanking);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int            $LaneIndex
     * @param int            $LaneRanking
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return (new Data($this->getBinding()))->getCertificateSubjectByIndex($tblCertificate, $LaneIndex, $LaneRanking);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(TblCertificate $tblCertificate, TblSubject $tblSubject)
    {

        return (new Data($this->getBinding()))->getCertificateSubjectBySubject($tblCertificate, $tblSubject);
    }
}
