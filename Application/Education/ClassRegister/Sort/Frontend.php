<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.09.2016
 * Time: 08:21
 */

namespace SPHERE\Application\Education\ClassRegister\Sort;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Sort
 */
class Frontend
{

    /**
     * @param null $DivisionId
     *
     * @return string
     */
    public function frontendSortDivision($DivisionId = null)
    {

        $Stage = new Stage('Klassenbuch', 'Schüler sortieren');

        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            if (Division::useService()->sortDivisionStudentByProperty($tblDivision, 'LastFirstName', new StringGermanOrderSorter())) {
                return $Stage . new Success(
                    'Die Schüler der Klasse wurden erfolgreich sortiert.',
                    new \SPHERE\Common\Frontend\Icon\Repository\Success()
                )
                . new Redirect('/Education/ClassRegister/All/Selected', Redirect::TIMEOUT_SUCCESS,
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
            } else {
                return $Stage . new Danger(
                    'Die Schüler der Klasse konnten nicht sortiert werden.',
                    new Exclamation()
                )
                . new Redirect('/Education/ClassRegister/All/Selected', Redirect::TIMEOUT_ERROR,
                    array(
                        'DivisionId' => $tblDivision->getId()
                    )
                );
            }
        } else {

            return $Stage
            . new Danger('Klassen nicht vorhanden.', new Ban())
            . new Redirect('/Education/ClassRegister/All', Redirect::TIMEOUT_ERROR);
        }
    }
}