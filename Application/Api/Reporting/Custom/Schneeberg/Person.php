<?php
/**
 * Created by PhpStorm.
 * User: kauschke
 * Date: 16.08.2016
 * Time: 15:25
 */

namespace SPHERE\Application\Api\Reporting\Custom\Schneeberg;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Reporting\Custom\Schneeberg\Person\Person as SchneebergPerson;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Custom\Schneeberg
 */
class Person
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
            $PersonList = SchneebergPerson::useService()->createClassList($tblDivision);
            if ($PersonList) {

                $fileLocation = SchneebergPerson::useService()->createClassListExcel($PersonList);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Klassenliste " . $tblDivision->getDisplayName()
                    . " " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }

        return false;
    }
}