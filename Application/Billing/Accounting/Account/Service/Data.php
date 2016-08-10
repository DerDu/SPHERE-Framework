<?php

namespace SPHERE\Application\Billing\Accounting\Account\Service;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKey;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountKeyType;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccountType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Accounting\Account\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createKeyType('U', 'Umsatzsteuer');
        $this->createKey('01.01.2007', '19', '01.01.2030', 'Mehrwertsteuer', '3',
            $this->getAccountKeyTypeById('1')
        );
        $this->createType('Erlöskonto', 'Dient zum erfassen des Erlöses');
        $this->createType('Umsatzsteuer', 'Dient zum erfassen der Umsatzsteuer');
    }

    /**
     * @param $Id
     *
     * @return false|TblAccountKeyType
     */
    public function getAccountKeyTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountKeyType', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return false|TblAccountType
     */
    public function getAccountTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountType', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccountKey
     */
    public function getAccountKeyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountKey', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblAccount
     */
    public function getAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount', $Id);
    }

    /**
     * @return false|TblAccountKey[]
     */
    public function getKeyValueAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountKey');
    }

    /**
     * @return false|TblAccountType[]
     */
    public function getTypeValueAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountType');
    }

    /**
     * @return false|TblAccountKey[]
     */
    public function getAccountKeyAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountKey');
    }

    /**
     * @return false|TblAccountType[]
     */
    public function getAccountTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccountType');
    }

    /**
     * @return false|TblAccount[]
     */
    public function getAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount');
    }

    /**
     * @param bool $IsActive
     *
     * @return false|TblAccount[]
     */
    public function getAccountAllByActiveState($IsActive = true)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount',
            array(TblAccount::ATTR_IS_ACTIVE => $IsActive));
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountKeyType|null|object
     */
    public function createKeyType($Name, $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountKeyType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountKeyType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param                   $ValidFrom
     * @param                   $Value
     * @param                   $ValidTo
     * @param                   $Description
     * @param                   $Code
     * @param TblAccountKeyType $tblAccountKeyType
     *
     * @return TblAccountKey|null|object
     */
    public function createKey($ValidFrom, $Value, $ValidTo, $Description, $Code, TblAccountKeyType $tblAccountKeyType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountKey')->findOneBy(array(
            'ValidFrom'         => new \DateTime($ValidFrom),
            'Value'             => $Value,
            'ValidTo'           => new \DateTime($ValidTo),
            'Description'       => $Description,
            'Code'              => $Code,
            'tblAccountKeyType' => $tblAccountKeyType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblAccountKey();
            $Entity->setValidFrom(new \DateTime($ValidFrom));
            $Entity->setValue($Value);
            $Entity->setValidTo(new \DateTime($ValidTo));
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setTblAccountKeyType($tblAccountKeyType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Number
     * @param $Description
     * @param $isActive
     * @param $Key
     * @param $Type
     *
     * @return TblAccount
     */
    public function createAccount($Number, $Description, $isActive, TblAccountKey $Key, TblAccountType $Type)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblAccount();

        $Entity->setDescription($Description);
        $Entity->setActive($isActive);
        $Entity->setNumber($Number);
        $Entity->setTblAccountKey($Key);
        $Entity->setTblAccountType($Type);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblAccountType|null|object
     */
    public function createType($Name, $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccountType')->findOneBy(array(
            'Name'        => $Name,
            'Description' => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblAccountType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblAccount     $tblAccount
     * @param                $Description
     * @param                $Number
     * @param                $IsActive
     * @param TblAccountKey  $tblAccountKey
     * @param TblAccountType $tblAccountType
     *
     * @return bool
     */
    public function updateAccount(
        TblAccount $tblAccount,
        $Description,
        $Number,
        $IsActive,
        TblAccountKey $tblAccountKey,
        TblAccountType $tblAccountType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Entity->setNumber($Number);
            $Entity->setActive($IsActive);
            $Entity->setTblAccountKey($tblAccountKey);
            $Entity->setTblAccountType($tblAccountType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function updateActivateAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setActive('1');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function updateDeactivateAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setActive('0');

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }
}
