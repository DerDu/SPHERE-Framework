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

    /**
     * @param TblType $tblType
     *
     * @return string
     */
    public function getSchoolTypeString(TblType $tblType){
        $Short = '';
        switch ($tblType->getName()){
            case 'Berufliches Gymnasium':
                $Short = 'BGYM';
                break;
            case 'Berufsfachschule':
                $Short = 'BFS';
                break;
            case 'Berufsschule':
                $Short = 'BS';
                break;
            case 'Fachoberschule':
                $Short = 'FOS';
                break;
            case 'Fachschule':
                $Short = 'FS';
                break;
            case 'Grundschule':
                $Short = 'GS';
                break;
            case 'Gymnasium':
                $Short = 'GYM';
                break;
            case 'Mittelschule / Oberschule':
                $Short = 'OS';
                break;
            case 'allgemein bildende Förderschule':
                $Short = 'ABFS';
                break;
        }
        return $Short;
    }
}
