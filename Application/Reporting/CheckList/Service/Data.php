<?php


namespace SPHERE\Application\Reporting\CheckList\Service;

use Doctrine\ORM\AbstractQuery;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblElementType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblObjectType;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\CheckList\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createObjectType('Einzel-Person', 'PERSON');
        $this->createObjectType('Einzel-Institution', 'COMPANY');
        $this->createObjectType('Personengruppe', 'PERSONGROUP');
        $this->createObjectType('Institutionengruppe', 'COMPANYGROUP');
        $this->createObjectType('Klassen', 'DIVISIONGROUP');

        $this->createElementType('CheckBox', 'CHECKBOX');
        $this->createElementType('Datum', 'DATE');
        $this->createElementType('Text', 'TEXT');
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblObjectType
     */
    public function createObjectType(
        $Name,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblObjectType')
            ->findOneBy(array(
                TblObjectType::ATTR_NAME       => $Name,
                TblObjectType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblObjectType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    public function updateObjectType(TblObjectType $tblObjectType, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblObjectType $Entity */
        $Entity = $Manager->getEntityById('TblObjectType', $tblObjectType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblElementType
     */
    public function createElementType(
        $Name,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblElementType')
            ->findOneBy(array(
                TblElementType::ATTR_NAME       => $Name,
                TblElementType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblElementType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblList', $Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblList
     */
    public function getListByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblList',
            array(TblList::ATTR_NAME => $Name));
    }

    /**
     * @return bool|TblList[]
     */
    public function getListAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblList');
    }

    /**
     * @param $Id
     *
     * @return bool|TblObjectType
     */
    public function getObjectTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblObjectType', $Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblObjectType
     */
    public function getObjectTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblObjectType',
            array(
                TblObjectType::ATTR_IDENTIFIER => $Identifier
            ));
    }

    /**
     * @return false|TblObjectType[]
     */
    public function getObjectTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblObjectType');
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     *
     * @return bool|Element[]
     */
    public function getObjectAllByListAndObjectType(TblList $tblList, TblObjectType $tblObjectType)
    {

        $tblListObjectList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectList',
            array(
                TblListObjectList::ATTR_TBL_LIST        => $tblList->getId(),
                TblListObjectList::ATTR_TBL_OBJECT_TYPE => $tblObjectType->getId()
            ));

        $returnList = array();
        if ($tblListObjectList) {
            /** @var TblListObjectList $item */
            foreach ($tblListObjectList as $item) {
                if ($item->getServiceTblObject()) {
                    $returnList[] = $item->getServiceTblObject();
                }
            }
        }

        return empty( $returnList ) ? false : $returnList;
    }

    /**
     * @param TblObjectType $tblObjectType
     * @param TblList       $tblList
     * @param               $ObjectId
     *
     * @return bool|TblListObjectList
     */
    public function getObjectByObjectTypeAndListAndId(TblObjectType $tblObjectType, TblList $tblList, $ObjectId)
    {

        $tblListObjectElementList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(
                TblListObjectElementList::ATTR_TBL_LIST           => $tblList->getId(),
                TblListObjectElementList::ATTR_TBL_OBJECT_TYPE    => $tblObjectType->getId(),
                TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT => $ObjectId
            ));
        $result = '';
        if ($tblListObjectElementList) {
            /** @var TblListObjectList $tblListObjectElement */
            foreach ($tblListObjectElementList as $tblListObjectElement) {
                $result = $tblListObjectElement->getServiceTblObject();
            }
        }
        return ( $result == '' ) ? false : $result;
    }

    /**
     * @param TblList            $tblList
     * @param TblListElementList $tblListElementList
     * @param TblObjectType      $tblObjectType
     * @param                    $ObjectId
     *
     * @return false|TblListObjectElementList
     */
    public function getListObjectElementListByListAndListElementListAndObjectTypeAndObjectId(
        TblList $tblList,
        TblListElementList $tblListElementList,
        TblObjectType $tblObjectType,
        $ObjectId
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(
                TblListObjectElementList::ATTR_TBL_LIST              => $tblList->getId(),
                TblListObjectElementList::ATTR_TBL_LIST_ELEMENT_LIST => $tblListElementList->getId(),
                TblListObjectElementList::ATTR_TBL_OBJECT_TYPE       => $tblObjectType->getId(),
                TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT    => $ObjectId
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblListElementList
     */
    public function getListElementListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListElementList',
            $Id);
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListElementList[]
     */
    public function getListElementListByList(TblList $tblList)
    {

        $TempList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListElementList',
            array(TblListElementList::ATTR_TBL_LIST => $tblList->getId()), array('EntityCreate' => self::ORDER_ASC));
        $EntityList = array();
        if (!empty ( $TempList )) {

            // ist Check-List sortiert
            $isSorted = false;
            /** @var TblListElementList $tblListElementList */
            foreach ($TempList as $tblListElementList) {
                if ($tblListElementList->getSortOrder() !== null) {
                    $isSorted = true;
                    break;
                }
            }

            if ($isSorted) {
                $TempList = $this->getSorter($TempList)->sortObjectBy('SortOrder');
                /** @var TblListElementList $tblListElementList */
                if ($TempList) {
                    foreach ($TempList as $tblListElementList) {
                        array_push($EntityList, $tblListElementList);
                    }
                }
            }

            if (!$isSorted) {
                $EntityList = $TempList;
            }
        }
        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param $Id
     *
     * @return bool|TblListObjectList
     */
    public function getListObjectListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListObjectList',
            $Id);
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListObjectList[]
     */
    public function getListObjectListByList(TblList $tblList)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListObjectList',
            array(TblListObjectList::ATTR_TBL_LIST => $tblList->getId()));
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
     *
     * @return bool|TblListObjectList
     */
    public function getListObjectListByListAndObjectTypeAndObject(
        TblList $tblList,
        TblObjectType $tblObjectType,
        Element $tblObject
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectList',
            array(
                TblListObjectList::ATTR_TBL_LIST           => $tblList->getId(),
                TblListObjectList::ATTR_TBL_OBJECT_TYPE    => $tblObjectType->getId(),
                TblListObjectList::ATTR_SERVICE_TBL_OBJECT => $tblObject->getId()
            ));
    }

    /**
     * @param TblList $tblList
     *
     * @return array|bool
     */
    public function getListObjectListContentByList(TblList $tblList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblListElementList = new TblListObjectElementList();

        $query = $queryBuilder->select('lel.serviceTblObject as ObjectId, lel.tblListElementList as ListElementListId, lel.Value as Value')
            ->from($tblListElementList->getEntityFullName(), 'lel')
            ->where($queryBuilder->expr()->eq('lel.tblList', '?1'))
            ->setParameter(1, $tblList->getId())
            ->getQuery();
        $ListObjectList = $query->getResult(AbstractQuery::HYDRATE_ARRAY);

        return !empty($ListObjectList) ? $ListObjectList : false;
    }

    /**
     * @param TblList $tblList
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByList(TblList $tblList)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(TblListObjectElementList::ATTR_TBL_LIST => $tblList->getId()));
    }

    /**
     * @param TblList $tblList
     * @param int     $ObjectId
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByListAndObjectId(TblList $tblList, $ObjectId)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(
                TblListObjectElementList::ATTR_TBL_LIST => $tblList->getId(),
                TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT => $ObjectId
            )
        );
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByListAndObjectTypeAndListElementListAndObject(
        TblList $tblList,
        TblObjectType $tblObjectType,
        Element $tblObject
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(
                TblListObjectElementList::ATTR_TBL_LIST           => $tblList->getId(),
                TblListObjectElementList::ATTR_TBL_OBJECT_TYPE    => $tblObjectType->getId(),
                TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT => $tblObject->getId()
            ));
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $ObjectId
     *
     * @return bool|TblListObjectElementList[]
     */
    public function getListObjectElementListByListAndObjectTypeAndListElementListAndObjectId(
        TblList $tblList,
        TblObjectType $tblObjectType,
        $ObjectId
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblListObjectElementList',
            array(
                TblListObjectElementList::ATTR_TBL_LIST           => $tblList->getId(),
                TblListObjectElementList::ATTR_TBL_OBJECT_TYPE    => $tblObjectType->getId(),
                TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT => $ObjectId
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblElementType
     */
    public function getElementTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType',
            $Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblElementType
     */
    public function getElementTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType',
            array(
                TblElementType::ATTR_IDENTIFIER => $Identifier
            ));
    }

    /**
     * @return false|TblElementType[]
     */
    public function getElementTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType');
    }

    /**
     * @param TblList $tblList
     *
     * @return false|int|Element
     */
    public function countListElementListByList(TblList $tblList)
    {

        $result = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListElementList',
            array(TblListElementList::ATTR_TBL_LIST => $tblList->getId()));

        return $result ? $result : 0;
    }

    /**
     * @param TblList $tblList
     *
     * @return int
     */
    public function countListObjectListByList(TblList $tblList)
    {

        // Todo GCK getCachedCountBy anpassen --> ignorieren von removed entities bei Verknüpfungstabelle
//        $result = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListObjectList',
//            array(TblListObjectList::ATTR_TBL_LIST => $tblList->getId()));
//
//        return $result ? $result : 0;

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListObjectList',
            array(
                TblListObjectList::ATTR_TBL_LIST => $tblList->getId()
            ));

        if ($EntityList){
            $count = 0;
            /** @var TblListObjectList $item */
            foreach ($EntityList as &$item){
                if ($item->getServiceTblObject()) {
                    $count++;
                }
            }
            return $count;
        } else {
            return 0;
        }
    }

    /**
     * @param        $Name
     * @param string $Description
     *
     * @return TblList
     */
    public function createList(
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblList')
            ->findOneBy(array(
                TblList::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblList();
            $Entity->setName($Name);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblList $tblList
     * @param $Name
     * @param $Description
     * @return bool
     */
    public function updateList(
        TblList $tblList,
        $Name,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblList $Entity */
        $Entity = $Manager->getEntityById('TblList', $tblList->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblListElementList $tblListElementList
     * @param string             $ElementName
     *
     * @return bool
     */
    public function updateListElementList(
        TblListElementList $tblListElementList,
        $ElementName
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblListElementList $Entity */
        $Entity = $Manager->getEntityById('TblListElementList', $tblListElementList->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($ElementName);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblList        $tblList
     * @param TblElementType $tblElementType
     * @param                $Name
     *
     * @return TblListElementList
     */
    public function addElementToList(
        TblList $tblList,
        TblElementType $tblElementType,
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblListElementList();
        $Entity->setTblList($tblList);
        $Entity->setTblElementType($tblElementType);
        $Entity->setName($Name);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblListElementList $TblListElementList
     *
     * @return bool
     */
    public function removeElementFromList(TblListElementList $TblListElementList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblListElementList $Entity */
        $Entity = $Manager->getEntityById('TblListElementList', $TblListElementList->getId());
        if (null !== $Entity) {
            $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListObjectElementList',
                array(
                    TblListObjectElementList::ATTR_TBL_LIST_ELEMENT_LIST => $Entity->getId()
                )
            );
            if ($EntityList) {
                foreach ($EntityList as $item){
                    Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $item);
                    $Manager->killEntity($item);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblList       $tblList
     * @param TblObjectType $tblObjectType
     * @param Element       $tblObject
     *
     * @return TblListElementList
     */
    public function addObjectToList(
        TblList $tblList,
        TblObjectType $tblObjectType,
        $tblObject
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblListObjectList')->findOneBy(array(
            TblListObjectList::ATTR_TBL_LIST           => $tblList->getId(),
            TblListObjectList::ATTR_TBL_OBJECT_TYPE    => $tblObjectType->getId(),
            TblListObjectList::ATTR_SERVICE_TBL_OBJECT => $tblObject->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblListObjectList();
            $Entity->setTblList($tblList);
            $Entity->setServiceTblObject($tblObject);
            $Entity->setTblObjectType($tblObjectType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        /** @var TblListElementList $Entity */
        return $Entity;
    }

    /**
     * @param TblListObjectList $TblListObjectList
     *
     * @return bool
     */
    public function removeObjectFromList(TblListObjectList $TblListObjectList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblListObjectList $Entity */
        $Entity = $Manager->getEntityById('TblListObjectList', $TblListObjectList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblListElementList $tblListElementList
     * @param integer            $SortOrder
     *
     * @return bool
     */
    public function updateListElementListSortOrder(TblListElementList $tblListElementList, $SortOrder)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblListElementList $Entity */
        $Entity = $Manager->getEntityById('TblListElementList', $tblListElementList->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSortOrder($SortOrder);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblList            $tblList
     * @param TblObjectType      $tblObjectType
     * @param TblListElementList $tblListElementList
     * @param Element            $tblObject
     * @param                    $Value
     *
     * @return TblListObjectElementList
     */
    public function updateObjectElementToList(
        TblList $tblList,
        TblObjectType $tblObjectType,
        TblListElementList $tblListElementList,
        Element $tblObject,
        $Value
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblListObjectElementList $Entity */
        $Entity = $Manager->getEntity('TblListObjectElementList')->findOneBy(array(
            TblListObjectElementList::ATTR_TBL_LIST              => $tblList->getId(),
            TblListObjectElementList::ATTR_TBL_OBJECT_TYPE       => $tblObjectType->getId(),
            TblListObjectElementList::ATTR_TBL_LIST_ELEMENT_LIST => $tblListElementList->getId(),
            TblListObjectElementList::ATTR_SERVICE_TBL_OBJECT    => $tblObject->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblListObjectElementList();
            $Entity->setTblList($tblList);
            $Entity->setTblObjectType($tblObjectType);
            $Entity->setTblListElementList($tblListElementList);
            $Entity->setServiceTblObject($tblObject);
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        } else {
            $Protocol = clone $Entity;
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
    }

    /**
     * @param TblList $tblList
     *
     * @return bool
     */
    public function destroyList(TblList $tblList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblListObjectElementList')
            ->findBy(array(TblListObjectElementList::ATTR_TBL_LIST => $tblList->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $EntityItems = $Manager->getEntity('TblListElementList')
            ->findBy(array(TblListElementList::ATTR_TBL_LIST => $tblList->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $EntityItems = $Manager->getEntity('TblListObjectList')
            ->findBy(array(TblListObjectList::ATTR_TBL_LIST => $tblList->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        /** @var TblList $Entity */
        $Entity = $Manager->getEntityById('TblList', $tblList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
