<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberation;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Liberation
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Liberation extends Student
{

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentLiberationCategory
     */
    public function createStudentLiberationCategory($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentLiberationCategory')->findOneBy(array(
            TblStudentLiberationCategory::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentLiberationCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudentLiberationCategory $tblStudentLiberationCategory
     * @param string $Name
     *
     * @param string $Description
     * @return TblStudentLiberationType
     */
    public function createStudentLiberationType(
        TblStudentLiberationCategory $tblStudentLiberationCategory,
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentLiberationType')->findOneBy(array(
            TblStudentLiberationType::ATTR_TBL_STUDENT_LIBERATION_CATEGORY => $tblStudentLiberationCategory->getId(),
            TblStudentLiberationType::ATTR_NAME                           => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentLiberationType();
            $Entity->setTblStudentLiberationCategory($tblStudentLiberationCategory);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentLiberation[]
     */
    public function getStudentLiberationAllByStudent(TblStudent $tblStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberation', array(
                TblStudentLiberation::ATTR_TBL_STUDENT => $tblStudent->getId()
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberation
     */
    public function getStudentLiberationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberation', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberationType
     */
    public function getStudentLiberationTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberationType', $Id
        );
    }

    /**
     * @return bool|TblStudentLiberationType[]
     */
    public function getStudentLiberationTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberationType'
        );
    }

    /**
     * @param TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return bool|TblStudentLiberationType[]
     */
    public function getStudentLiberationTypeAllByCategory(TblStudentLiberationCategory $tblStudentLiberationCategory)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberationType', array(
                TblStudentLiberationType::ATTR_TBL_STUDENT_LIBERATION_CATEGORY => $tblStudentLiberationCategory->getId()
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLiberationCategory
     */
    public function getStudentLiberationCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberationCategory', $Id
        );
    }

    /**
     * @return bool|TblStudentLiberationCategory[]
     */
    public function getStudentLiberationCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentLiberationCategory'
        );
    }

    /**
     * @param TblStudent              $tblStudent
     * @param TblStudentLiberationType $tblStudentLiberationType
     *
     * @return TblStudentLiberation
     */
    public function addStudentLiberation(
        TblStudent $tblStudent,
        TblStudentLiberationType $tblStudentLiberationType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblStudentLiberation $Entity */
        $Entity = $Manager->getEntity('TblStudentLiberation')->findOneBy(array(
            TblStudentLiberation::ATTR_TBL_STUDENT                => $tblStudent->getId(),
            TblStudentLiberation::ATTR_TBL_STUDENT_LIBERATION_TYPE => $tblStudentLiberationType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblStudentLiberation();
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentLiberationType($tblStudentLiberationType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentLiberation $tblStudentLiberation
     *
     * @return bool
     */
    public function removeStudentLiberation(TblStudentLiberation $tblStudentLiberation)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentLiberation $Entity */
        $Entity = $Manager->getEntityById('TblStudentLiberation', $tblStudentLiberation->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudentLiberationCategory $tblStudentLiberationCategory
     * @param $Name
     *
     * @return bool
     */
    public function updateStudentLiberationCategory(
        TblStudentLiberationCategory $tblStudentLiberationCategory,
        $Name
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentLiberationCategory $Entity */
        $Entity = $Manager->getEntityById('TblStudentLiberationCategory', $tblStudentLiberationCategory->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;

            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentLiberationCategory
     */
    public function getStudentLiberationCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentLiberationCategory', array(
            TblStudentLiberationCategory::ATTR_NAME => $Name
        ));
    }
}
