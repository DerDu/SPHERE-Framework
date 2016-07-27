<?php
namespace SPHERE\Application\Reporting\Dynamic;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Dynamic
 *
 * @package SPHERE\Application\Reporting\Dynamic
 */
class Dynamic implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Flexible Auswertung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendCreateFilter'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Setup', __NAMESPACE__.'\Frontend::frontendSetupFilter'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Filter', __NAMESPACE__.'\Frontend::frontendRunFilter'
        ));
    }

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Flexible Auswertung');
        $Stage->setMessage( 'Auswertung erstellen, wählen oder bearbeiten' );
        $Stage->addButton(
            new Standard('Neue Auswertung erstellen', '')
        );

        $AvailableViewList = array(
            new ViewPeopleGroupMember(),
            new ViewPerson(),
        );

        $Panel = array();
        /** @var AbstractView $AvailableView */
        foreach ($AvailableViewList as $Index => $AvailableView) {

            $Panel[] = array(
                'Filter' => new Panel($AvailableView->getViewGuiName(), array(
                    new Muted(new Small(implode(', ', $AvailableView->getNameDefinitionList())))
                ), Panel::PANEL_TYPE_INFO, array(
                        new Form(new FormGroup(new FormRow(new FormColumn(array(
                            (new TextField( 'Filter-'.$Index, '', '' ))->setDefaultValue( $AvailableView->getViewClassName(), true ),
                            new Primary('Hinzufügen', new ChevronLeft())
                        )))))
                    )
                )
            );
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new Form(new FormGroup(new FormRow(new FormColumn(array(
                            new Panel('Filter: Zusammenstellung', new Warning( 'Es wurden noch keine Filter gewählt' ), Panel::PANEL_TYPE_DEFAULT, array(
                                new TextField( 'FilterName', 'Name der Auswertung', 'Name der Auswertung' ),
                                new CheckBox( 'FilterPublic', 'Öffentlich (Jeder kann diese Auswertung benutzen)', 1 ),
                                new Primary('Speichern', new Save())
                            ) )
                        ))))), 8),
                        new LayoutColumn(array(
                            new TableData(
                                $Panel
                            )
                        ), 4)
                    ))
                )
            )
        );

        return $Stage;
    }
}
