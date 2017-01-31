<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Generator\Service\Setup;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
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
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAll()
    {

        return (new Data($this->getBinding()))->getGradeInformationTemplateAll();
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->getGradeInformationTemplateAllByConsumer($tblConsumer);
    }

    /**
     * @return false|TblCertificate[]
     */
    public function getTemplateAll()
    {

        return (new Data($this->getBinding()))->getTemplateAll();
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->getTemplateAllByConsumer($tblConsumer);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblCertificate $tblCertificate
     * @param array $GradeList
     * @param array $SubjectList
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
        if (empty($GradeList) && empty($SubjectList)) {
            return $Form;
        }

        $Error = array();

        // Kopf-Noten
        foreach ($GradeList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (($tblGradeType = Gradebook::useService()->getGradeTypeById($Field['GradeType']))) {
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
                            'Eine Notenangabe an der Position ' . $LaneIndex . ':' . $LaneRanking . ' konnte nicht gespeichert werden'
                        );
                    }
                }
            }
        }

        // Fach-Noten
        foreach ($SubjectList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (($tblSubject = Subject::useService()->getSubjectById($Field['Subject']))) {
                    $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                        $tblCertificate, $LaneIndex, $LaneRanking
                    );
                    if ($tblCertificateSubject) {
                        // Update
                        (new Data($this->getBinding()))->updateCertificateSubject($tblCertificateSubject,
                            $tblSubject,
                            ((isset($Field['IsEssential']) && $Field['IsEssential']) ? true : false),
                            ((isset($Field['Liberation']) && $Field['Liberation'])
                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
                                    : null
                                )
                                : null
                            )
                        );
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateSubject($tblCertificate,
                            $LaneIndex, $LaneRanking, $tblSubject,
                            ((isset($Field['IsEssential']) && $Field['IsEssential']) ? true : false),
                            ((isset($Field['Liberation']) && $Field['Liberation'])
                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
                                    : null
                                )
                                : null
                            )
                        );
                    }
                } else {
                    if ($Field['Subject'] > 0) {
                        array_push($Error,
                            'Eine Fachangabe an der Position ' . $LaneIndex . ':' . $LaneRanking . ' konnte nicht gespeichert werden'
                        );
                    } else {
                        if (($tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                            $tblCertificate, $LaneIndex, $LaneRanking
                        ))
                        ) {
                            (new Data($this->getBinding()))->removeCertificateSubject($tblCertificateSubject);
                        }
                    }
                }
            }
        }

        if (empty($Error)) {
            return new Success(new Enable() . ' Die Einstellungen wurden gespeichert')
            . new Redirect('/Education/Certificate/Setting', Redirect::TIMEOUT_SUCCESS);
        } else {
            // TODO Show $Error List
            return new Danger(new Disable() . ' Eine oder mehrere Einstellungen wurden nicht gespeichert!')
            . new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_ERROR,
                array('Certificate' => $tblCertificate->getId()));
        }
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

        return (new Data($this->getBinding()))->getCertificateGradeByIndex($tblCertificate, $LaneIndex, $LaneRanking);
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

    /**
     * @param $Identifier
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getCertificateTypeByIdentifier($Identifier);
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeById($Id)
    {

        return (new Data($this->getBinding()))->getCertificateTypeById($Id);
    }

    /**
     * @return false|TblCertificateType[]
     */
    public function getCertificateTypeAll()
    {

        return (new Data($this->getBinding()))->getCertificateTypeAll();
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|Service\Entity\TblCertificate[]
     */
    public function getCertificateAllBy(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        return (new Data($this->getBinding()))->getCertificateAllBy($tblConsumer, $tblCertificateType, $tblSchoolType);
    }


    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateLevel[]
     */
    public function getCertificateLevelAllByCertificate(TblCertificate $tblCertificate)
    {

        return (new Data($this->getBinding()))->getCertificateLevelAllByCertificate($tblCertificate);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param bool $HasTeamInRemark
     *
     * @return false|int
     */
    public function getCharCountByCertificateAndField(TblCertificate $tblCertificate, $FieldName, $HasTeamInRemark)
    {

        return (new Data($this->getBinding()))->getCharCountByCertificateAndField($tblCertificate, $FieldName, $HasTeamInRemark);
    }

    /**
     * @param TblCertificateType $tblCertificateType
     *
     * @return false|TblCertificate[]
     */
    public function getCertificateAllByType(
        TblCertificateType $tblCertificateType
    ) {

        return (new Data($this->getBinding()))->getCertificateAllByType($tblCertificateType);
    }

    /**
     * @return array
     */
    public function getFormField()
    {

        return array(
            'Content.Input.Remark' => 'TextArea',
            'Content.Input.Rating' => 'TextArea',
            'Content.Input.Survey' => 'TextArea',
            'Content.Input.Deepening' => 'TextField',
            'Content.Input.SchoolType' => 'SelectBox',
            'Content.Input.Type' => 'SelectBox',
            'Content.Input.DateCertifcate' => 'DatePicker',
            'Content.Input.DateConference' => 'DatePicker',
            'Content.Input.Transfer' => 'SelectBox',
            'Content.Input.TeamExtra' => 'TextField'
        );
    }

    /**
     * @return array
     */
    public function getFormLabel()
    {

        return array(
            'Content.Input.Remark' => 'Bemerkungen',
            'Content.Input.Rating' => 'Einschätzung',
            'Content.Input.Survey' => 'Gutachten',
            'Content.Input.Deepening' => 'Vertiefungsrichtung',
            'Content.Input.SchoolType' => 'Ausbildung fortsetzen',
            'Content.Input.Type' => 'Bezieht sich auf',
            'Content.Input.DateCertifcate' => 'Datum des Zeugnisses',
            'Content.Input.DateConference' => 'Datum der Konferenz',
            'Content.Input.Transfer' => 'Versetzungsvermerk',
            'Content.Input.TeamExtra' => 'Arbeitsgemeinschaften'
        );
    }
}
