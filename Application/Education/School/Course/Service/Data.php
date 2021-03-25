<?php
namespace SPHERE\Application\Education\School\Course\Service;

use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\School\Course\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {
        $this->createCourse('Hauptschule');
        $this->createCourse('Realschule');
        $this->createCourse('Gymnasium');

        $this->createSchoolDiploma('Abgangszeugnis der allgemeinbildenden Schule');
        $this->createSchoolDiploma('Allgemeine Hochschulreife');
        $this->createSchoolDiploma('Hauptschulabschluss oder gleichwertiger Abschluss');
        $this->createSchoolDiploma('Qualifizierter Hauptschulabschluss oder gleichwertiger Abschluss');
        $this->createSchoolDiploma('Realschulabschluss oder gleichwertiger Abschluss');
        $this->createSchoolDiploma('Sonstiger allgemeinbildender Abschluss eines anderen Bundeslandes bzw. Staates');

        $this->createTechnicalDiploma('Abschlusszeugnis');
        $this->createTechnicalDiploma('Abschlusszeugnis+ zusätzlich zuerkannte Fachhochschulreife');
        $this->createTechnicalDiploma('Abschlusszeugnis + zusätzlich zuerkannter Hauptschulabschluss');
        $this->createTechnicalDiploma('Abschlusszeugnis+ zusätzlich zuerkannter qualifizierter beruflicher Bildungsabschluss/ mittlerer Schulabschluss');
        $this->createTechnicalDiploma('Noch kein Abschluss an einer berufsbildenden Schule');
        $this->createTechnicalDiploma('Sonstiger berufsbildender Abschluss eines anderen Bundeslandes bzw. Staates');
        $this->createTechnicalDiploma('Zeugnis');
        $this->createTechnicalDiploma('Zeugnis+ zusätzlich zuerkannter Hauptschulabschluss');
        $this->createTechnicalDiploma('Zeugnis der allgemeinen Hochschulreife');
        $this->createTechnicalDiploma('Zeugnis der Fachhochschulreife');
        $this->createTechnicalDiploma('Zeugnis (entspricht dem Vermerk „mit Erfolg besucht"+ zusätzlich zuerkannter Hauptschulabschluss)');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return null|object|TblCourse
     */
    public function createCourse($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCourse')
            ->findOneBy(array(TblCourse::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblCourse();
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
     * @return bool|TblCourse
     */
    public function getCourseById($Id)
    {
        /** @var TblCourse $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCourse', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCourse
     */
    public function getCourseByName($Name)
    {
        /** @var TblCourse $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCourse')
            ->findOneBy(array(TblCourse::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCourse[]
     */
    public function getCourseAll()
    {
        /** @var TblCourse $Entity */
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCourse');
    }

    /**
     * @param $Name
     *
     * @return TblSchoolDiploma
     */
    public function createSchoolDiploma($Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSchoolDiploma')
            ->findOneBy(array(TblSchoolDiploma::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblSchoolDiploma();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchoolDiploma
     */
    public function getSchoolDiplomaById($Id)
    {
        /** @var TblSchoolDiploma $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblSchoolDiploma', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSchoolDiploma[]
     */
    public function getSchoolDiplomaAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchoolDiploma');
    }

    /**
     * @param $Name
     *
     * @return TblTechnicalDiploma
     */
    public function createTechnicalDiploma($Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTechnicalDiploma')
            ->findOneBy(array(TblTechnicalDiploma::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblTechnicalDiploma();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalDiploma
     */
    public function getTechnicalDiplomaById($Id)
    {
        /** @var TblTechnicalDiploma $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTechnicalDiploma', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblTechnicalDiploma[]
     */
    public function getTechnicalDiplomaAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTechnicalDiploma');
    }

    /**
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return TblTechnicalCourse
     */
    public function createTechnicalCourse($Name, $GenderMaleName, $GenderFemaleName)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTechnicalCourse')
            ->findOneBy(array(TblTechnicalCourse::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblTechnicalCourse();
            $Entity->setName($Name);
            $Entity->setGenderMaleName($GenderMaleName);
            $Entity->setGenderFemaleName($GenderFemaleName);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalCourse
     */
    public function getTechnicalCourseById($Id)
    {
        /** @var TblTechnicalCourse $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTechnicalCourse', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblTechnicalCourse[]
     */
    public function getTechnicalCourseAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTechnicalCourse');
    }

    /**
     * @param TblTechnicalCourse $tblTechnicalCourse
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return bool
     */
    public function updateTechnicalCourse(
        TblTechnicalCourse $tblTechnicalCourse,
        $Name,
        $GenderMaleName,
        $GenderFemaleName
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTechnicalCourse $Entity */
        $Entity = $Manager->getEntityById('TblTechnicalCourse', $tblTechnicalCourse->getId());

        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setGenderMaleName($GenderMaleName);
            $Entity->setGenderFemaleName($GenderFemaleName);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param string $Acronym
     * @param string $Name
     *
     * @return TblTechnicalSubjectArea
     */
    public function createTechnicalSubjectArea($Acronym, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblTechnicalSubjectArea')
            ->findOneBy(array(TblTechnicalSubjectArea::ATTR_ACRONYM => $Acronym));

        if (null === $Entity) {
            $Entity = new TblTechnicalSubjectArea();
            $Entity->setAcronym($Acronym);
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalSubjectArea
     */
    public function getTechnicalSubjectAreaById($Id)
    {
        /** @var TblTechnicalSubjectArea $Entity */
        $Entity = $this->getEntityManager()->getEntityById('TblTechnicalSubjectArea', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Acronym
     *
     * @return false|TblTechnicalSubjectArea
     */
    public function getTechnicalSubjectAreaByAcronym($Acronym)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTechnicalSubjectArea', array(
            TblTechnicalSubjectArea::ATTR_ACRONYM => $Acronym
        ));
    }

    /**
     * @return bool|TblTechnicalSubjectArea[]
     */
    public function getTechnicalSubjectAreaAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblTechnicalSubjectArea');
    }

    /**
     * @param TblTechnicalSubjectArea $tblTechnicalSubjectArea
     * @param string $Acronym
     * @param string $Name
     *
     * @return bool
     */
    public function updateTechnicalSubjectArea(
        TblTechnicalSubjectArea $tblTechnicalSubjectArea,
        $Acronym,
        $Name
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTechnicalSubjectArea $Entity */
        $Entity = $Manager->getEntityById('TblTechnicalSubjectArea', $tblTechnicalSubjectArea->getId());

        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setAcronym($Acronym);
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }
}
