<?php
namespace SPHERE\Application\Education\School\Course;

use SPHERE\Application\Education\School\Course\Service\Data;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Course\Service\Setup;
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
}
