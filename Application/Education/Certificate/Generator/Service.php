<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateInformation;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateReferenceForLanguages;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Generator\Service\Setup;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
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
                            isset($Field['IsEssential'])
//                            , ((isset($Field['Liberation']) && $Field['Liberation'])
//                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    : null
//                                )
//                                : null
//                            )
                        );
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateSubject($tblCertificate,
                            $LaneIndex, $LaneRanking, $tblSubject,
                            isset($Field['IsEssential'])
//                            , ((isset($Field['Liberation']) && $Field['Liberation'])
//                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    : null
//                                )
//                                : null
//                            )
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
            . new Redirect('/Education/Certificate/Setting/Template', Redirect::TIMEOUT_SUCCESS);
        } else {
            // TODO Show $Error List
            return new Danger(new Disable() . ' Eine oder mehrere Einstellungen wurden nicht gespeichert!')
            . new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_ERROR,
                array('Certificate' => $tblCertificate->getId()));
        }
    }

    /**
     * @param string $Type
     *
     * @return string
     */
    public function insertCertificate($Type = '')
    {

        if((new Data($this->getBinding()))->insertCertificate($Type)){
            return new Success('Installation der Zeugnisvorlagen erfolgreich!').
                new Redirect('/Education/Certificate/Setting/Implement', Redirect::TIMEOUT_SUCCESS);
        }
        return new Danger('Installation der Zeugnisvorlagen ist fehlgeschlagen').
            new Redirect('/Education/Certificate/Setting/Implement', Redirect::TIMEOUT_ERROR);
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
     *
     * @return bool|Service\Entity\TblCertificate[]
     */
    public function getCertificateAllByConsumerAndCertificateType(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null
    ) {

        return (new Data($this->getBinding()))->getCertificateAllByConsumerAndCertificateType($tblConsumer, $tblCertificateType);
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
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllForAutoSelect(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        // SSW-939 - Noteninformation Zuweisung Vorlage
        // für die Noteninformation ist keine Schulart angegeben, deswegen wird keine Vorlage gefunden
        if ($tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
            && ($tblCertificate = Setting::useService()->getCertificateByCertificateClassName('GradeInformation'))
        ) {
            return array(0 => $tblCertificate);
        } else {
            return (new Data($this->getBinding()))->getCertificateAllForAutoSelect($tblConsumer, $tblCertificateType, $tblSchoolType);
        }
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
            'Content.Input.Remark'              => 'TextArea',
            'Content.Input.SecondRemark'        => 'TextArea',
            'Content.Input.RemarkWithoutTeam'   => 'TextArea',
            'Content.Input.Rating'              => 'TextArea',
            'Content.Input.TechnicalRating'     => 'TextArea',
            'Content.Input.Survey'              => 'TextArea',
            'Content.Input.Deepening'           => 'TextField',
            'Content.Input.SchoolType'          => 'SelectBox',
            'Content.Input.Type'                => 'SelectBox',
            'Content.Input.DateCertifcate'      => 'DatePicker',
            'Content.Input.DateConference'      => 'DatePicker',
            'Content.Input.DateConsulting'      => 'DatePicker',
            'Content.Input.Transfer'            => 'SelectBox',
            'Content.Input.IndividualTransfer'  => 'TextField',
            'Content.Input.TeamExtra'           => 'TextField',
            'Content.Input.GTA'                 => 'TextArea',
            'Content.Input.BellSubject'         => 'TextField',
            'Content.Input.PerformanceGroup'    => 'TextField',
            'Content.Input.Arrangement'         => 'TextArea',
            'Content.Input.Support'             => 'TextArea',
            'Content.Input.DivisionName'        => 'TextField',
            'Content.Input.StudentLetter'       => 'TextArea',
            'Content.Input.DialoguesWithYou'    => 'TextArea',
            'Content.Input.DialoguesWithParent' => 'TextArea',
            'Content.Input.DialoguesWithUs'     => 'TextArea',
            // Berufsfachschule
            'Content.Input.BsDestination'       => 'TextField',
            'Content.Input.Operation1'          => 'TextField',
            'Content.Input.OperationTime1'      => 'TextField',
            'Content.Input.Operation2'          => 'TextField',
            'Content.Input.OperationTime2'      => 'TextField',
            'Content.Input.Operation3'          => 'TextField',
            'Content.Input.OperationTime3'      => 'TextField'
        );
    }

    /**
     * @return array
     */
    public function getFormFieldKeyList()
    {
        $formFieldList = $this->getFormField();
        $list = array();
        foreach ($formFieldList as $key => $formField) {
            $tempArray = explode('.', $key);
            $tempValue = end($tempArray);
            $list[$tempValue] = $tempValue;
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getFormLabel()
    {

        return array(
            'Content.Input.Remark'              => 'Bemerkungen',
            'Content.Input.SecondRemark'        => 'Bemerkung Seite 2',
            'Content.Input.RemarkWithoutTeam'   => 'Bemerkungen',
            'Content.Input.Rating'              => 'Einschätzung',
            'Content.Input.TechnicalRating'     => 'Fachliche Einschätzung',
            'Content.Input.Survey'              => 'Gutachten',
            'Content.Input.Deepening'           => 'Vertiefungsrichtung',
            'Content.Input.SchoolType'          => 'Ausbildung fortsetzen',
            'Content.Input.Type'                => 'Bezieht sich auf',
            'Content.Input.DateCertifcate'      => 'Datum des Zeugnisses',
            'Content.Input.DateConference'      => 'Datum der Klassenkonferenz',
            'Content.Input.DateConsulting'      => 'Datum der Bildungsberatung',
            'Content.Input.Transfer'            => 'Versetzungsvermerk',
            'Content.Input.IndividualTransfer'  => 'Versetzungsvermerk',
            'Content.Input.TeamExtra'           => 'Teilnahme an zusätzlichen schulischen Veranstaltungen',
            'Content.Input.GTA'                 => 'GTA',
            'Content.Input.BellSubject'         => 'Thema BELL',
            'Content.Input.PerformanceGroup'    => 'Leistungsgruppe',
            'Content.Input.Arrangement'         => 'Besonderes Engagement',
            'Content.Input.Support'             => 'Inklusive Unterrichtung',
            'Content.Input.DivisionName'        => 'Klasse',
            'Content.Input.StudentLetter'       => 'Schülerbrief',
            'Content.Input.DialoguesWithYou'    => 'Im Dialog mit dir',
            'Content.Input.DialoguesWithParent' => 'Im Dialog mit deinen Eltern',
            'Content.Input.DialoguesWithUs'     => 'Im Dialog mit uns',
            // Berufsfachschule
            'Content.Input.BsDestination'       => 'Berufsfachschule für ...',
            'Content.Input.Operation1'          => 'Einsatzgebiet 1',
            'Content.Input.OperationTime1'      => 'Einsatzgebiet Dauer in Wochen 1',
            'Content.Input.Operation2'          => 'Einsatzgebiet 2',
            'Content.Input.OperationTime2'      => 'Einsatzgebiet Dauer in Wochen 2',
            'Content.Input.Operation3'          => 'Einsatzgebiet 3',
            'Content.Input.OperationTime3'      => 'Einsatzgebiet Dauer in Wochen 3'
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param IFormInterface $Form
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateCertificateType(
        IFormInterface $Form,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        if (($tblCertificateTypeAll = $this->getCertificateTypeAll())) {
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if (isset($Data[$tblCertificateType->getId()]) && !$tblCertificateType->isAutomaticallyApproved()) {
                    (new Data($this->getBinding()))->updateCertificateType($tblCertificateType,
                        $tblCertificateType->getIdentifier(), $tblCertificateType->getName(), true);
                } elseif (!isset($Data[$tblCertificateType->getId()]) && $tblCertificateType->isAutomaticallyApproved()) {
                    (new Data($this->getBinding()))->updateCertificateType($tblCertificateType,
                        $tblCertificateType->getIdentifier(), $tblCertificateType->getName(), false);
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Setting/Approval', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateReferenceForLanguages[]
     */
    public function getCertificateReferenceForLanguagesAllByCertificate(TblCertificate $tblCertificate)
    {

        return (new Data($this->getBinding()))->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $languageRanking
     *
     * @return false|TblCertificateReferenceForLanguages
     */
    public function getCertificateReferenceForLanguagesByCertificateAndRanking(TblCertificate $tblCertificate, $languageRanking)
    {

        return (new Data($this->getBinding()))->getCertificateReferenceForLanguagesByCertificateAndRanking($tblCertificate, $languageRanking);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCertificate $tblCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateCertificateReferenceForLanguages(
        IFormInterface $Form,
        TblCertificate $tblCertificate,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        foreach ($Data as $ranking => $array) {
            if (($tblCertificateReferenceForLanguages = $this->getCertificateReferenceForLanguagesByCertificateAndRanking($tblCertificate, $ranking))) {
                (new Data($this->getBinding()))->updateCertificateReferenceForLanguages(
                    $tblCertificateReferenceForLanguages,
                    $array['ToLevel10'],
                    $array['AfterBasicCourse'],
                    $array['AfterAdvancedCourse']
                );
            } else {
                (new Data($this->getBinding()))->createCertificateReferenceForLanguages(
                    $tblCertificate,
                    $ranking,
                    $array['ToLevel10'],
                    $array['AfterBasicCourse'],
                    $array['AfterAdvancedCourse']
                );
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_SUCCESS, array('Certificate' => $tblCertificate->getId()));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblStudentSubject $tblStudentSubject
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    public function getReferenceForLanguageByStudent(
        TblCertificate $tblCertificate,
        TblStudentSubject $tblStudentSubject,
        TblPerson $tblPerson,
        TblDivision $tblDivision
    ) {
        $reference = '';
        if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
            $identifier = 'ToLevel10';
            // bei z.B: EN2 kann in der Schülerakte trotzdem das normale EN eingestellt sein
            if (($tblSubjectList = Subject::useService()->getSubjectAllByName($tblSubject->getName()))) {
                $foundSubject = false;
                foreach ($tblSubjectList as $tblSubject) {
                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                        $tblDivision, $tblSubject
                    ))) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (Division::useService()->exitsSubjectStudent($tblDivisionSubject,
                                $tblPerson)
                                && $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()
                            ) {
                                $identifier = $tblSubjectGroup->isAdvancedCourse() ? 'AfterAdvancedCourse' : 'AfterBasicCourse';
                                $foundSubject = true;
                                break;
                            }
                        }
                    }

                    if ($foundSubject) {
                        break;
                    }
                }
            }

            if (($tblCertificateReferenceForLanguages = $this->getCertificateReferenceForLanguagesByCertificateAndRanking(
                $tblCertificate,
                $tblStudentSubject->getTblStudentSubjectRanking()->getId()
            ))) {
                switch ($identifier) {
                    case 'ToLevel10': $reference = $tblCertificateReferenceForLanguages->getToLevel10(); break;
                    case 'AfterBasicCourse': $reference = $tblCertificateReferenceForLanguages->getAfterBasicCourse(); break;
                    case 'AfterAdvancedCourse': $reference = $tblCertificateReferenceForLanguages->getAfterAdvancedCourse(); break;
                }
            }
        }

        return $reference;
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
        return (new Data($this->getBinding()))->getCertificateInformationByField($tblCertificate, $fieldName);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateInformation[]
     */
    public function getCertificateInformationListByCertificate(
        TblCertificate $tblCertificate
    ) {
        return (new Data($this->getBinding()))->getCertificateInformationListByCertificate($tblCertificate);
    }
}
