<?php
namespace SPHERE\Application\Education\School\Course;

use SPHERE\Application\Education\School\Course\Service\Data;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Education\School\Course\Service\Setup;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\School\Course
 */
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
     * @return bool|TblCourse[]
     */
    public function getCourseAll()
    {

        return (new Data($this->getBinding()))->getCourseAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCourse
     */
    public function getCourseById($Id)
    {

        return (new Data($this->getBinding()))->getCourseById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCourse
     */
    public function getCourseByName($Name)
    {

        return (new Data($this->getBinding()))->getCourseByName($Name);
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchoolDiploma
     */
    public function getSchoolDiplomaById($Id)
    {
        return (new Data($this->getBinding()))->getSchoolDiplomaById($Id);
    }

    /**
     * @return bool|TblSchoolDiploma[]
     */
    public function getSchoolDiplomaAll()
    {
        return (new Data($this->getBinding()))->getSchoolDiplomaAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalDiploma
     */
    public function getTechnicalDiplomaById($Id)
    {
        return (new Data($this->getBinding()))->getTechnicalDiplomaById($Id);
    }

    /**
     * @return bool|TblTechnicalDiploma[]
     */
    public function getTechnicalDiplomaAll()
    {
        return (new Data($this->getBinding()))->getTechnicalDiplomaAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalCourse
     */
    public function getTechnicalCourseById($Id)
    {
        return (new Data($this->getBinding()))->getTechnicalCourseById($Id);
    }

    /**
     * @return bool|TblTechnicalCourse[]
     */
    public function getTechnicalCourseAll()
    {
        return (new Data($this->getBinding()))->getTechnicalCourseAll();
    }

    /**
     * @param $Data
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @return false|Form
     */
    public function checkFormTechnicalCourse(
        $Data,
        TblTechnicalCourse $tblTechnicalCourse = null
    ) {
        $error = false;

        $form = \SPHERE\Application\Education\Lesson\Course\Course::useFrontend()
            ->formTechnicalCourse($tblTechnicalCourse ? $tblTechnicalCourse->getId() : null);
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        } else {
            $form->setSuccess('Data[Name]');
        }

        return $error ? $form : false;
    }
    
    /**
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return TblTechnicalCourse
     */
    public function createTechnicalCourse($Name, $GenderMaleName, $GenderFemaleName)
    {
        return (new Data($this->getBinding()))->createTechnicalCourse($Name, $GenderMaleName, $GenderFemaleName);
    }

    /**
     * @param TblTechnicalCourse $tblTechnicalCourse
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return bool
     */
    public function updateTechnicalCourse(
        TblTechnicalCourse $tblTechnicalCourse,
        $Name,
        $GenderMaleName,
        $GenderFemaleName
    ) {
        return (new Data($this->getBinding()))->updateTechnicalCourse($tblTechnicalCourse, $Name, $GenderMaleName, $GenderFemaleName);
    }

    /**
     * @param string $Acronym
     * @param string $Name
     *
     * @return TblTechnicalSubjectArea
     */
    public function createTechnicalSubjectArea($Acronym, $Name)
    {
        return (new Data($this->getBinding()))->createTechnicalSubjectArea($Acronym, $Name);
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalSubjectArea
     */
    public function getTechnicalSubjectAreaById($Id)
    {
        return (new Data($this->getBinding()))->getTechnicalSubjectAreaById($Id);
    }

    /**
     * @param $Acronym
     *
     * @return false|TblTechnicalSubjectArea
     */
    public function getTechnicalSubjectAreaByAcronym($Acronym)
    {
        return (new Data($this->getBinding()))->getTechnicalSubjectAreaByAcronym($Acronym);
    }

    /**
     * @return bool|TblTechnicalSubjectArea[]
     */
    public function getTechnicalSubjectAreaAll()
    {
        return (new Data($this->getBinding()))->getTechnicalSubjectAreaAll();
    }

    /**
     * @param TblTechnicalSubjectArea $tblTechnicalSubjectArea
     * @param string $Acronym
     * @param string $Name
     *
     * @return bool
     */
    public function updateTechnicalSubjectArea(
        TblTechnicalSubjectArea $tblTechnicalSubjectArea,
        $Acronym,
        $Name
    ) {
        return (new Data($this->getBinding()))->updateTechnicalSubjectArea($tblTechnicalSubjectArea, $Acronym, $Name);
    }

    /**
     * @param $Data
     * @param TblTechnicalSubjectArea|null $tblTechnicalSubjectArea
     * @return false|Form
     */
    public function checkFormTechnicalSubjectArea(
        $Data,
        TblTechnicalSubjectArea $tblTechnicalSubjectArea = null
    ) {
        $error = false;

        $form = \SPHERE\Application\Education\Lesson\Course\Course::useFrontend()
            ->formTechnicalSubjectArea($tblTechnicalSubjectArea ? $tblTechnicalSubjectArea->getId() : null);

        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        } else {
            $form->setSuccess('Data[Name]');
        }

        if (isset($Data['Acronym']) && empty($Data['Acronym'])) {
            $form->setError('Data[Acronym]', 'Bitte geben Sie einen Kürzel an');
            $error = true;
        } elseif (!$tblTechnicalSubjectArea && $this->getTechnicalSubjectAreaByAcronym($Data['Acronym'])) {
            $form->setError('Data[Acronym]', 'Diese Kürzel exisitiert bereits, bitte geben Sie ein anderes Kürzel an');
            $error = true;
        } elseif ($tblTechnicalSubjectArea
            && ($temp = $this->getTechnicalSubjectAreaByAcronym($Data['Acronym']))
            && $temp->getId() != $tblTechnicalSubjectArea->getId()
        ) {
            $form->setError('Data[Acronym]', 'Diese Kürzel exisitiert bereits, bitte geben Sie ein anderes Kürzel an');
            $error = true;
        } else {
            $form->setSuccess('Data[Acronym]');
        }

        return $error ? $form : false;
    }
}
