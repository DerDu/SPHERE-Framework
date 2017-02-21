<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Import implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Show', __NAMESPACE__.'/Frontend::frontendLectureshipShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Edit', __NAMESPACE__.'/Frontend::frontendLectureshipEdit'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Destroy', __NAMESPACE__.'/Frontend::frontendLectureshipDestroy'
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
        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        $Stage->setMessage('Daten importieren');
        $tblYearAll = Term::useService()->getYearAll();
        if (!$tblYearAll) {
            $tblYearAll = array();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Well(array(
                            new Title('Lehraufträge', 'importieren'),
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            ( new SelectBox('tblYear', 'Schuljahr auswählen', array(
                                                '{{ Year }} {{ Description }}' => $tblYearAll
                                            )) )->setRequired()
                                        )
                                    ),
                                    new FormRow(
                                        new FormColumn(
                                            ( new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null, array('showPreview' => false)) )->setRequired()
                                        )
                                    ),
                                )),
                                new Primary('Importieren'),
                                new Link\Route(__NAMESPACE__.'/Lectureship')
                            )
                        )), 6),
                    )),
                ))
            )
        );

        return $Stage;
    }
}