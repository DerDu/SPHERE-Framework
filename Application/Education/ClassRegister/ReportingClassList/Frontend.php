<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.09.2016
 * Time: 09:09
 */

namespace SPHERE\Application\Education\ClassRegister\ReportingClassList;

use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\ReportingClassList
 */
class Frontend
{

    /**
     * @param null $DivisionId
     * @param string $BasicRoute
     *
     * @return string
     */
    public function frontendDivisionList($DivisionId = null, $BasicRoute = '/Education/ClassRegister/Teacher')
    {

        $Stage = new Stage('Klassenbuch', 'Klassenliste');
        $Stage->addButton(new Standard(
            'Zurück', $BasicRoute . '/Selected', new ChevronLeft(), array('DivisionId' => $DivisionId)
        ));

        ReportingPerson::useFrontend()->showClassList($Stage, $DivisionId);

        return $Stage;
    }
}