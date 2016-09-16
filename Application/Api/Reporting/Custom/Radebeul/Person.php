<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.09.2016
 * Time: 16:28
 */

namespace SPHERE\Application\Api\Reporting\Custom\Radebeul;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Reporting\Custom\Radebeul\Person\Person as RadebeulPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Radebeul
 */
class Person
{

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadParentTeacherConferenceList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = RadebeulPerson::useService()->createParentTeacherConferenceList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = RadebeulPerson::useService()->createParentTeacherConferenceListExcel($tblDivision,
                        $PersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Radebeul Anwesenheitsliste für Elternabende " . $tblDivision->getDisplayName()
                        . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function downloadDenominationList()
    {

        $countArray = array();
        $PersonList = RadebeulPerson::useService()->createDenominationList($countArray);
        if ($PersonList) {
            $fileLocation = RadebeulPerson::useService()->createDenominationListExcel($PersonList, $countArray);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Radebeul Religionszugehörigkeit " . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadPhoneList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = RadebeulPerson::useService()->createPhoneList($tblGroup);
            if ($PersonList) {
                $fileLocation = RadebeulPerson::useService()->createPhoneListExcel($PersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Radebeul Telefonliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadKindergartenList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = RadebeulPerson::useService()->createKindergartenList($tblGroup);
            if ($PersonList) {
                $fileLocation = RadebeulPerson::useService()->createKindergartenListExcel($tblGroup, $PersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Radebeul Kinderhausliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadRegularSchoolList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = RadebeulPerson::useService()->createRegularSchoolList($tblGroup);
            if ($PersonList) {
                $fileLocation = RadebeulPerson::useService()->createRegularSchoolListExcel($PersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Radebeul Stammschulenliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadDiseaseList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = RadebeulPerson::useService()->createDiseaseList($tblGroup);
            if ($PersonList) {
                $fileLocation = RadebeulPerson::useService()->createDiseaseListExcel($tblGroup, $PersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Radebeul Allergieliste " . $tblGroup->getName()
                    . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }
}