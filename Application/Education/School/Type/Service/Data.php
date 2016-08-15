<?php
namespace SPHERE\Application\Education\School\Type\Service;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\School\Type\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createType('Berufliches Gymnasium', '');
        $this->createType('Berufsfachschule', '');
        $this->createType('Berufsschule', '');
        $this->createType('Fachoberschule', '');
        $this->createType('Fachschule', '');
        $this->createType('Grundschule', '');
        $this->createType('Gymnasium', '');
        $this->createType('Mittelschule / Oberschule', '');
        $this->createType('allgemein bildende Förderschule', '');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return null|object|TblType
     */
    public function createType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__,$this->getConnection()->getEntityManager(),'TblType',array('Name'=>self::ORDER_ASC));
    }
}
