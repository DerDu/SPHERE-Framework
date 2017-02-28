<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Import extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        // load if Lectureship exist (by Account)
        $PanelContent = array();
        $PanelContent[] = 'Lehraufträge importieren: '.new Standard('', '/Transfer/Untis/Import/Lectureship/Prepare', new Upload()
                , array(), 'Hochladen und bearbeiten');
        $tblUntisImportLectureship = Import::useService()->getUntisImportLectureshipAll(true);
        if ($tblUntisImportLectureship) {
            $PanelContent[] = 'Lehraufträge bearbeiten: '
                .new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array(), 'Import bearbeiten')
                .new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array(), 'Import löschen');
        }

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Untis-Import:', $PanelContent
                                , Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    ),
                ))
            )
        );

        return $Stage;
    }
}