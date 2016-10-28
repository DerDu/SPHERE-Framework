<?php
namespace SPHERE\Application\Corporation\Company\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Company\Service
 */
class Data extends AbstractData
{

    /**
     * @return false|ViewCompany[]
     */
    public function viewCompany()
    {

        return $this->getCachedEntityList(
            __METHOD__, $this->getConnection()->getEntityManager(), 'ViewCompany'
        );
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param string $Name
     * @param string $ExtendedName
     * @param string $Description
     *
     * @return null|object|TblCompany
     */
    public function createCompany($Name, $ExtendedName = '', $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCompany')->findOneBy(array(
            TblCompany::ATTR_NAME => $Name,
            'EntityRemove' => null
        ));
        if (null === $Entity) {
            $Entity = new TblCompany();
            $Entity->setName($Name);
            $Entity->setExtendedName($ExtendedName);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param string     $Name
     * @param string     $ExtendedName
     * @param string     $Description
     *
     * @return bool
     */
    public function updateCompany(
        TblCompany $tblCompany,
        $Name,
        $ExtendedName = '',
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setExtendedName($ExtendedName);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany');
    }

    /**
     * @return int
     */
    public function countCompanyAll()
    {

        return $this->getConnection()->getEntityManager()->getEntity('TblCompany')->count();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany', $Id);
    }

    /**
     * @param string $Description
     *
     * @return bool|TblCompany
     */
    public function getCompanyByDescription($Description)
    {

        $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany',
            array(TblCompany::ATTR_DESCRIPTION => $Description));

        if ($list) {
            if (count($list) === 1) {
                return $list[0];
            }
        }

        return false;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function destroyCompany(TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }
}
