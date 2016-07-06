<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategorySubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroupCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Subject\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $hasSubjects = $this->getSubjectAll();

        $tblGroupElective = $this->createGroup('Wahlfach', '', true, 'ELECTIVE');
        $tblGroupStandard = $this->createGroup('Standardfach', '', true, 'STANDARD');

        // Wahlfach
        $tblCategoryElective = $this->createCategory('Wahlfach');
        $this->addGroupCategory($tblGroupElective, $tblCategoryElective);

        // Profil
        $tblCategory = $this->createCategory('Profil', '', true, 'PROFILE');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        if (!$hasSubjects) {
            $tblGroupOrientation = $this->createGroup('Neigungskurs', '', true, 'ORIENTATION');
            $tblGroupAdvanced = $this->createGroup('Vertiefungskurs', '', true, 'ADVANCED');

            $tblSubject = $this->createSubject('KPR', 'Künstlerisches Profil');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('SPR', 'Sprachliches Profil');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('NPR', 'Naturwissenschaftliches Profil');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('GPR', 'Geisteswissenschaftliches Profil');
            $this->addCategorySubject($tblCategory, $tblSubject);

            // Neigungskurs
            $tblCategory = $this->createCategory('Kunst und Kultur');
            $this->addGroupCategory($tblGroupOrientation, $tblCategory);
            $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
            $tblSubject = $this->createSubject('SZSP', 'Szenisches Spiel');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblCategory = $this->createCategory('Soziales und gesellschaftliches Handeln');
            $this->addGroupCategory($tblGroupOrientation, $tblCategory);
            $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
            $tblSubject = $this->createSubject('KRHA', 'Kreatives Handwerken');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblCategory = $this->createCategory('Technik');
            $this->addGroupCategory($tblGroupOrientation, $tblCategory);
            $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
            $tblSubject = $this->createSubject('TECH', 'Technik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('SCHW', 'Schrauberwerkstatt');
            $this->addCategorySubject($tblCategory, $tblSubject);


            // Muttersprache
            $tblCategory = $this->createCategory('Muttersprache');
            $this->addGroupCategory($tblGroupStandard, $tblCategory);
            $tblSubject = $this->createSubject('DE', 'Deutsch');
            $this->addCategorySubject($tblCategory, $tblSubject);
        }

        // Fremdsprache
        $tblCategory = $this->createCategory('Fremdsprachen', '', true, 'FOREIGNLANGUAGE');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);

        if (!$hasSubjects) {
            $tblSubject = $this->createSubject('EN', 'Englisch');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('LA', 'Latein');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('FR', 'Französisch');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('RU', 'Russisch');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('PO', 'Polnisch');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('SP', 'Spanisch');
            $this->addCategorySubject($tblCategory, $tblSubject);

            // Musische Fächer
            $tblCategory = $this->createCategory('Musische Fächer');
            $this->addGroupCategory($tblGroupStandard, $tblCategory);
            $tblSubject = $this->createSubject('MU', 'Musik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $this->addCategorySubject($tblCategoryElective, $tblSubject);
            $tblSubject = $this->createSubject('KU', 'Kunst');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $this->addCategorySubject($tblCategoryElective, $tblSubject);
            $tblSubject = $this->createSubject('DASP', 'Darstellendes Spiel');
            $this->addCategorySubject($tblCategory, $tblSubject);

            // Naturwissenschaften/Technik
            $tblCategory = $this->createCategory('Naturwissenschaften/Technik');
            $this->addGroupCategory($tblGroupStandard, $tblCategory);
            $tblSubject = $this->createSubject('MA', 'Mathematik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('IN', 'Informatik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('BI', 'Biologie');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('CH', 'Chemie');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('PH', 'Physik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('TE', 'Technik');
            $this->addCategorySubject($tblCategory, $tblSubject);

            // Gesellschaftswissenschaften
            $tblCategory = $this->createCategory('Gesellschaftswissenschaften');
            $this->addGroupCategory($tblGroupStandard, $tblCategory);
            $tblSubject = $this->createSubject('GE', 'Geschichte');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $this->addCategorySubject($tblCategoryElective, $tblSubject);
            $tblSubject = $this->createSubject('GEO', 'Geographie');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $this->addCategorySubject($tblCategoryElective, $tblSubject);
            $tblSubject = $this->createSubject('SOP', 'Sozialkunde/Politik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('WI', 'Wirtschaft');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('RE', 'Recht');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('ER', 'Erziehungswissenschaften');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('SPO', 'Sport');
            $this->addCategorySubject($tblCategory, $tblSubject);
        }

        // Religionsunterricht
        $tblCategory = $this->createCategory('Religionsunterricht', '', true, 'RELIGION');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        if (!$hasSubjects) {
            $tblSubject = $this->createSubject('RKA', 'Katholische Religionslehre');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('REV', 'Evangelische Religionslehre');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('ETH', 'Ethik');
            $this->addCategorySubject($tblCategory, $tblSubject);
            $tblSubject = $this->createSubject('PHI', 'Philosophie');
            $this->addCategorySubject($tblCategory, $tblSubject);
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblGroup
     */
    public function createGroup($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_IS_LOCKED  => $IsLocked,
                TblGroup::ATTR_IDENTIFIER => $Identifier
            ));
        } else {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_NAME => $Name
            ));
        }
        if (null === $Entity) {
            $Entity = new TblGroup();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblCategory
     */
    public function createCategory($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblCategory')->findOneBy(array(
                TblCategory::ATTR_IS_LOCKED  => $IsLocked,
                TblCategory::ATTR_IDENTIFIER => $Identifier,
                'EntityRemove' => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblCategory')->findOneBy(array(
                TblCategory::ATTR_NAME => $Name,
                'EntityRemove' => null
            ));
        }
        if (null === $Entity) {
            $Entity = new TblCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return TblGroupCategory
     */
    public function addGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblGroupCategory')
            ->findOneBy(array(
                TblGroupCategory::ATTR_TBL_GROUP    => $tblGroup->getId(),
                TblGroupCategory::ATTR_TBL_CATEGORY => $tblCategory->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblGroupCategory();
            $Entity->setTblGroup($tblGroup);
            $Entity->setTblCategory($tblCategory);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param        $Acronym
     * @param        $Name
     * @param string $Description
     *
     * @return TblSubject
     */
    public function createSubject($Acronym, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubject')->findOneBy(array(
            TblSubject::ATTR_ACRONYM => $Acronym,
            'EntityRemove' => null
        ));
        if (null === $Entity) {
            $Entity = new TblSubject();
            $Entity->setAcronym($Acronym);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return TblCategorySubject
     */
    public function addCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCategorySubject')
            ->findOneBy(array(
                TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblCategorySubject::ATTR_TBL_SUBJECT  => $tblSubject->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblCategorySubject();
            $Entity->setTblCategory($tblCategory);
            $Entity->setTblSubject($tblSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblSubject $tblSubject
     * @param            $Acronym
     * @param            $Name
     * @param string     $Description
     *
     * @return bool
     */
    public function updateSubject(TblSubject $tblSubject, $Acronym, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSubject $Entity */
        $Entity = $Manager->getEntityById('TblSubject', $tblSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setAcronym($Acronym);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCategory $tblCategory
     * @param             $Name
     * @param string      $Description
     *
     * @return bool
     */
    public function updateCategory(TblCategory $tblCategory, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCategory $Entity */
        $Entity = $Manager->getEntityById('TblCategory', $tblCategory->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblSubject
     */
    public function getSubjectByAcronym($Acronym)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubject', array(
            TblSubject::ATTR_ACRONYM => $Acronym
        ));
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getSubjectActiveState(TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCategorySubject $Entity */
        $Entity = $Manager->getEntity('TblCategorySubject')
            ->findOneBy(array(
                TblCategorySubject::ATTR_TBL_SUBJECT => $tblSubject->getId()
            ));
        if (null !== $Entity) {
            return true;
        }
        return false;
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function destroySubject(TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSubject')->findOneBy(array('Id' => $tblSubject->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool
     */
    public function destroyCategory(TblCategory $tblCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCategory')->findOneBy(array('Id' => $tblCategory->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function countSubjectAll()
    {

        return $this->getConnection()->getEntityManager()->getEntity('TblSubject')->count();
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup');
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return bool
     */
    public function removeGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGroupCategory $Entity */
        $Entity = $Manager->getEntity('TblGroupCategory')
            ->findOneBy(array(
                TblGroupCategory::ATTR_TBL_GROUP    => $tblGroup->getId(),
                TblGroupCategory::ATTR_TBL_CATEGORY => $tblCategory->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCategorySubject $Entity */
        $Entity = $Manager->getEntity('TblCategorySubject')
            ->findOneBy(array(
                TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblCategorySubject::ATTR_TBL_SUBJECT  => $tblSubject->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByCategory(TblCategory $tblCategory)
    {

        /** @var TblCategorySubject[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCategorySubject', array(
                TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId()
            )
        );
        if ($EntityList) {
            array_walk($EntityList, function (TblCategorySubject &$V) {
                if ($V->getTblSubject()){
                    $V = $V->getTblSubject();
                } else {
                    $V = false;
                }
            });
            $EntityList = array_filter($EntityList);
        }

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCategory[]
     */
    public function getCategoryAllByGroup(TblGroup $tblGroup)
    {

        /** @var TblGroupCategory[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGroupCategory', array(
                TblGroupCategory::ATTR_TBL_GROUP => $tblGroup->getId()
            )
        );
        if ($EntityList) {
            array_walk($EntityList, function (TblGroupCategory &$V) {

                if ($V->getTblCategory()) {
                    $V = $V->getTblCategory();
                } else {
                    $V = false;
                }
            });
            $EntityList = array_filter($EntityList);
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|null|TblGroup[]
     */
    public function getGroupAllByCategory(TblCategory $tblCategory)
    {

        /** @var TblGroupCategory[] $EntityList */
        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblGroupCategory', array(
                TblGroupCategory::ATTR_TBL_CATEGORY => $tblCategory->getId()
            )
        );
        if ($EntityList) {
            array_walk($EntityList, function (TblGroupCategory &$V) {

                $V = $V->getTblGroup();
            });
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param int $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCategory', $Id);
    }

    /**
     * @param int $Identifier
     *
     * @return bool|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCategory', array(
            TblCategory::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCategory
     */
    public function getCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCategory', array(
            TblCategory::ATTR_NAME => $Name
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSubject
     */
    public function getSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubject', $Id);
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAllHavingNoCategory()
    {

        $Exclude = $this->getConnection()->getEntityManager()->getQueryBuilder()
            ->select('NM.tblSubject')
            ->from(__NAMESPACE__.'\Entity\TblCategorySubject', 'NM')
            ->distinct()
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        $tblSubjectAll = $this->getSubjectAll();
        if ($tblSubjectAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblSubjectAll, function (TblSubject &$tblSubject) use ($Exclude) {

                if (in_array($tblSubject->getId(), $Exclude)) {
                    $tblSubject = false;
                }
            });
            $EntityList = array_filter($tblSubjectAll);
        } else {
            $EntityList = null;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubject');
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAllHavingNoGroup()
    {

        $Exclude = $this->getConnection()->getEntityManager()->getQueryBuilder()
            ->select('NM.tblCategory')
            ->from(__NAMESPACE__.'\Entity\TblGroupCategory', 'NM')
            ->distinct()
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        $tblCategoryAll = $this->getCategoryAll();
        if ($tblCategoryAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblCategoryAll, function (TblCategory &$tblCategory) use ($Exclude) {

                if (in_array($tblCategory->getId(), $Exclude)) {
                    $tblCategory = false;
                }
            });
            $EntityList = array_filter($tblCategoryAll);
        } else {
            $EntityList = null;
        }
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCategory');
    }
}
