<?php
namespace SPHERE\Application\Api\Reporting\CheckList;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Response;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\CheckList\CheckList as CheckListApp;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class CheckList
 *
 * @package SPHERE\Application\Api\Reporting\CheckList
 */
class CheckList implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __NAMESPACE__.'\CheckList::downloadCheckList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Reorder', __CLASS__.'::reorderCheckList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param null $ListId
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOption1Id
     * @param null $SchoolOption2Id
     *
     * @return bool|string
     */
    public function downloadCheckList(
        $ListId = null,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOption1Id = null,
        $SchoolOption2Id = null
    )
    {

        $tblList = CheckListApp::useService()->getListById($ListId);
        if ($tblList) {
            $fileLocation = CheckListApp::useService()
                ->createCheckListExcel($tblList, $YearPersonId, $LevelPersonId, $SchoolOption1Id, $SchoolOption2Id);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Check-List ".$tblList->getName()." ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null|array $Reorder
     * @param null|array $Additional
     *
     * @return Response
     */
    public function reorderCheckList($Reorder = null, $Additional = null)
    {

        if ($Additional
            && $Reorder
            && isset( $Additional['Id'] )
            && ( $tblList = CheckListApp::useService()->getListById($Additional['Id']) )
        ) {

            $tblListElementList = CheckListApp::useService()->getListElementListByList($tblList);
            if ($tblListElementList) {
                // update SortOrder for deleted Person etc.
                $count = 1;
                foreach ($tblListElementList as $tblListElement) {
                    CheckListApp::useService()->updateListElementListSortOrder($tblListElement, $count++);
                }
                foreach ($Reorder as $item) {
                    if (isset( $item['pre'] ) && isset( $item['post'] )) {
                        $pre = $item['pre'] - 1;
                        $post = $item['post'];

                        if (isset( $tblListElementList[$pre] )) {
                            CheckListApp::useService()->updateListElementListSortOrder($tblListElementList[$pre], $post);
                        }
                    }
                }
            }
            return ( new Response() )->addData(new Success().' Die Sortierung der Check-Liste wurde erfolgreich aktualisiert.');
        }
        return ( new Response() )->addError('Fehler!', new HazardSign().' Die Sortierung der Check-Liste konnte nicht aktualisiert werden.', 0);
    }
}
