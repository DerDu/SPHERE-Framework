<?php
namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class ClassRegister
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ClassRegister extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Reorder', __CLASS__ . '::reorderDivision'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param null|array $Reorder
     * @param null|array $Additional
     *
     * @return Response
     */
    public function reorderDivision($Reorder = null, $Additional = null)
    {

        if ($Additional
            && $Reorder
            && isset($Additional['DivisionId'])
            && ($tblDivision = Division::useService()->getDivisionById($Additional['DivisionId']))
        ) {

            $tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByDivision($tblDivision);
            if ($tblDivisionStudentAll) {
                // update SortOrder for deleted Person etc.
                $count = 1;
                foreach ($tblDivisionStudentAll as $tblDivisionStudent) {
                    Division::useService()->updateDivisionStudentSortOrder($tblDivisionStudent, $count++);
                }
                foreach ($Reorder as $item) {
                    if (isset($item['pre']) && isset($item['post'])) {
                        $pre = $item['pre'] - 1;
                        $post = $item['post'];

                        if (isset($tblDivisionStudentAll[$pre])) {
                            Division::useService()->updateDivisionStudentSortOrder($tblDivisionStudentAll[$pre], $post);
                        }
                    }
                }
            }
            return (new Response())->addData( new Success().' Die Sortierung der Schüler wurde erfolgreich aktualisiert.');
        }
        return (new Response())->addError( 'Fehler!', new HazardSign().' Die Sortierung der Schüler konnte nicht aktualisiert werden.', 0);
    }
}
