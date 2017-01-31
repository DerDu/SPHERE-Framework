<?php
namespace SPHERE\Application\Education\School\Type;

use SPHERE\Application\Education\School\Type\Service\Data;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\School\Type
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
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getTypeByName($Name);
    }
}
