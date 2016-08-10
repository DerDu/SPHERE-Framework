<?php
namespace SPHERE\Application\Api\Reporting\Custom\Chemnitz;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Chemnitz
 */
class Common
{

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createClassList($tblDivision);
            if ($PersonList) {

                foreach ($PersonList as $key => $row) {
                    $name[$key] = strtoupper($row['LastName']);
                    $firstName[$key] = strtoupper($row['FirstName']);
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Chemnitz Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadStaffList()
    {

        $PersonList = Person::useService()->createStaffList();

        if ($PersonList) {
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
            if ($tblPersonList) {
                $fileLocation = Person::useService()->createStaffListExcel($PersonList, $tblPersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Mitarbeiterliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param $DivisionId
     *
     * @return bool|string
     */
    public function downloadMedicList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createMedicList($tblDivision);
            if ($PersonList) {
                foreach ($PersonList as $key => $row) {
                    $name[$key] = strtoupper($row['LastName']);
                    $firstName[$key] = strtoupper($row['FirstName']);
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createMedicListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Chemnitz Arztliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createParentTeacherConferenceList($tblDivision);
            if ($PersonList) {
                foreach ($PersonList as $key => $row) {
                    $name[$key] = strtoupper($row['LastName']);
                    $firstName[$key] = strtoupper($row['FirstName']);
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createParentTeacherConferenceListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Chemnitz Elternabende ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadClubMemberList()
    {

        $PersonList = Person::useService()->createClubMemberList();

        if ($PersonList) {
            $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB);
            if ($tblGroup) {
                foreach ($PersonList as $key => $row) {
                    $name[$key] = strtoupper($row['LastName']);
                    $firstName[$key] = strtoupper($row['FirstName']);
                }
                array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createClubMemberListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Chemnitz Vereinsmitgliederliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadInterestedPersonList()
    {

        $PersonList = Person::useService()->createInterestedPersonList();
        if ($PersonList) {
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
            if ($tblPersonList) {
                $fileLocation = Person::useService()->createInterestedPersonListExcel($PersonList, $tblPersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Interessentenliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadSchoolFeeList()
    {

        $PersonList = Person::useService()->createSchoolFeeList();
        if ($PersonList) {
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
            if ($tblPersonList) {
                $fileLocation = Person::useService()->createSchoolFeeListExcel($PersonList, $tblPersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Chemnitz Schulgeldliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return string|bool
     */
    public function downloadPrintClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = Person::useService()->createPrintClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = Person::useService()->createPrintClassListExcel($PersonList, $tblPersonList, $DivisionId);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Chemnitz Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }
}
