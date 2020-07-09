<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Standard\Person
 */
class Person
{

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Klassenliste ".$tblDivision->getDisplayName()
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
    public function downloadExtendedClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createExtendedClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createExtendedClassListExcel($PersonList, $tblPersonList);
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Erweiterte_Klassenliste ".$tblDivision->getDisplayName()
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
    public function downloadBirthdayClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createBirthdayClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Birthday_Klassenliste ".$tblDivision->getDisplayName()
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
    public function downloadMedicalInsuranceClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createMedicalInsuranceClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Krankenkasse_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadGroupList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = ReportingPerson::useService()->createGroupList($tblGroup);
            if ($PersonList) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createGroupListExcel($PersonList, $tblPersonList, $GroupId);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gruppenliste ".$tblGroup->getName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
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

        $PersonList = ReportingPerson::useService()->createInterestedPersonList();
        if ($PersonList) {
            $firstName = array();
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
            if ($tblPersonList) {
                $fileLocation = ReportingPerson::useService()->createInterestedPersonListExcel($PersonList, $tblPersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Interessentenliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadElectiveClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createElectiveClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createElectiveClassListExcel($PersonList,
                        $tblPersonList
                        , $tblDivision->getId());
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Wahlfächer_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $Option
     * @param null $PersonGroup
     *
     * @return string
     */
    public function downloadMetaDataComparison($Person = null, $Year = null, $Division = null, $Option = null, $PersonGroup = null)
    {

        $fileLocation = ReportingPerson::useService()->createMetaDataComparisonExcel($Person, $Year, $Division, $Option, $PersonGroup);
        return FileSystem::getDownload($fileLocation->getRealPath(),"Stammdatenabfrage"." ".date("Y-m-d H:i:s").".xlsx")->__toString();
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadMedicalRecordClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createMedicalRecordClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createMedicalRecordClassListExcel($PersonList, $tblPersonList);
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Krankenakte_Klassenliste ".$tblDivision->getDisplayName()
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
    public function downloadAgreementClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createAgreementClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createAgreementClassListExcel($PersonList, $tblPersonList);
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Einverständniserklärung_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $Date
     * @param null $Type
     * @param string $DivisionName
     * @param string $GroupName
     *
     * @return bool|string
     */
    public function downloadAbsenceList($Date = null, $Type = null, $DivisionName = '', $GroupName = '')
    {

        // das Datum darf keine Uhrzeit enthalten
        $dateTime = new \DateTime((new \DateTime($Date))->format('d.m.Y'));
        if (($fileLocation = ReportingPerson::useService()->createAbsenceListExcel($dateTime, $Type, $DivisionName, $GroupName))) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fehlzeiten " . $dateTime->format("Y-m-d") . ".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadClubList()
    {

        $PersonList = ReportingPerson::useService()->createClubList();
        if ($PersonList) {
            $fileLocation = ReportingPerson::useService()->createClubListExcel($PersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fördervereinsmitgliedschaft ".date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }
}
