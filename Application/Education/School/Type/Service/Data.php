<?php
namespace SPHERE\Application\Education\School\Type\Service;

use SPHERE\Application\Education\School\Type\Service\Entity\TblCategory;
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
        $tblCategoryCommon = $this->createCategory(TblCategory::COMMON, 'Allgemeinbildende Schulen');
        $tblCategoryTechnical = $this->createCategory(TblCategory::TECHNICAL, 'Berufsbildende Schulen');
        $tblCategorySecondCourse = $this->createCategory(TblCategory::SECOND_COURSE, 'Schulen des zweiten Bildungsweges');

        $this->createType('Berufliches Gymnasium', 'BGy', $tblCategoryTechnical, true);
        $this->createType('Berufsfachschule', 'BFS', $tblCategoryTechnical, true);
        $this->createType('Berufsschule', 'BS', $tblCategoryTechnical, true);
        $this->createType('Fachoberschule', 'FOS', $tblCategoryTechnical, true);
        $this->createType('Fachschule', 'FS', $tblCategoryTechnical, true);
        $this->createType('Grundschule', 'GS', $tblCategoryCommon, true);
        $this->createType('Gymnasium', 'Gy', $tblCategoryCommon, true);
        $this->createType('Mittelschule / Oberschule', 'OS', $tblCategoryCommon, true);
        $this->createType('Förderschule', 'FöS', $tblCategoryCommon, true);

        $this->createType('Gemeinschaftsschule', '', $tblCategoryCommon, true);

        $this->createType('Abendoberschule', '', $tblCategorySecondCourse, true);
        $this->createType('Abendgymnasium', '', $tblCategorySecondCourse, true);
        $this->createType('Kolleg', '', $tblCategorySecondCourse, true);

//        $this->createType('Hotelmanagementschule', 'HOFA', $tblCategoryTechnical, true);

        /**
         * Kamenz BFS, FS
         */
        $this->createType('Sonstige allgemeinbildende Schulart eines anderen Bundeslandes bzw. Staates', '', $tblCategoryCommon, false);
        $this->createType('Freie Waldorfschule', '', $tblCategoryCommon, false);

        $this->createType('Berufsschule (berufsbildende Förderschule)', '', $tblCategoryTechnical, false);
        if (($tblType = $this->createType('Berufsgrundbildungsjahr', 'BGJ', $tblCategoryTechnical, true))) {
            $this->updateTypeOnce($tblType, 'BGJ', $tblCategoryTechnical, true);
        }
        $this->createType('Berufsgrundbildungsjahr (berufsbildende Förderschule)', '', $tblCategoryTechnical, false);
        if (($tblType = $this->createType('Berufsvorbereitungsjahr', 'BVJ', $tblCategoryTechnical, true))) {
            $this->updateTypeOnce($tblType, 'BVJ', $tblCategoryTechnical, true);
        }
        $this->createType('Berufsvorbereitungsjahr (berufsbildende Förderschule)', '', $tblCategoryTechnical, false);
        $this->createType('BvB', '', $tblCategoryTechnical, false);
        $this->createType('BvB – rehaspezifisch', '', $tblCategoryTechnical, false);
        $this->createType('Einstiegsqualifizierung Jugendlicher', '', $tblCategoryTechnical, false);
        $this->createType('Fachoberschule (berufsbildende Förderschule)', '', $tblCategoryTechnical, false);
        $this->createType('Sonstige berufsbildende Schulart eines anderen Bundeslandes bzw. Staates', '', $tblCategoryTechnical, false);
    }

    /**
     * @param string $Name
     * @param string $ShortName
     * @param TblCategory $tblCategory
     * @param boolean $IsBasic
     * @param string $Description
     *
     * @return null|object|TblType
     */
    public function createType($Name, $ShortName, TblCategory $tblCategory, $IsBasic, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setShortName($ShortName);
            $Entity->setIsBasic($IsBasic);
            $Entity->setTblCategory($tblCategory);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblType $tblType
     * @param string $ShortName
     * @param TblCategory $tblCategory
     * @param boolean $IsBasic
     */
    public function updateTypeOnce(TblType $tblType, $ShortName, TblCategory $tblCategory, $IsBasic)
    {
        $this->updateType(
            $tblType,
            $tblType->getName(),
            $ShortName,
            $tblCategory,
            $IsBasic,
            $tblType->getDescription()
        );
    }

    /**
     * @param TblType $tblType
     * @param string $Name
     * @param string $ShortName
     * @param TblCategory $tblCategory
     * @param boolean $IsBasic
     * @param string $Description
     *
     * @return bool
     */
    public function updateType(TblType $tblType, $Name, $ShortName, TblCategory $tblCategory, $IsBasic, $Description)
    {
        $Manager = $this->getEntityManager();

        /** @var TblType $Entity */
        $Entity = $Manager->getEntityById('TblType', $tblType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setShortName($ShortName);
            $Entity->setIsBasic($IsBasic);
            $Entity->setTblCategory($tblCategory);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {
        /** @var TblType $Entity */
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
        /** @var TblType $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $ShortName
     *
     * @return bool|TblType
     */
    public function getTypeByShortName($ShortName)
    {
        /** @var TblType $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_SHORT_NAME => $ShortName));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeBasicAll()
    {
        return $this->getCachedEntityListBy(__METHOD__,$this->getConnection()->getEntityManager(),'TblType',
            array(TblType::ATTR_IS_BASIC => true), array('Name'=>self::ORDER_ASC)
        );
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|TblType[]
     */
    public function getTypeAllByCategory(TblCategory $tblCategory)
    {
        return $this->getCachedEntityListBy(__METHOD__,$this->getConnection()->getEntityManager(),'TblType',
            array(TblType::TBL_CATEGORY => $tblCategory->getId()), array('Name'=>self::ORDER_ASC));
    }

    /**
     * @param $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {
        /** @var TblCategory $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCategory', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Identifier
     *
     * @return false|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {
        /** @var TblCategory $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCategory')
            ->findOneBy(array(TblCategory::ATTR_IDENTIFIER => $Identifier));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Identifier
     * @param $Name
     *
     * @return object|TblCategory
     */
    public function createCategory($Identifier, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCategory')
            ->findOneBy(array(TblCategory::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblCategory();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }
}
