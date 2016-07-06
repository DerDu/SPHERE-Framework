<?php
namespace SPHERE\Application\Education\Graduation;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Graduation
 *
 * @package SPHERE\Application\Education\Graduation
 */
class Graduation implements IApplicationInterface
{

    public static function registerApplication()
    {

        Gradebook::registerModule();
        Evaluation::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zensuren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));

    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Zensuren', 'Überblick');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Grade'));

        $Stage->setContent(
            '<br>'
            . new Table(new TableHead(), new TableBody(array(
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/GradeType') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Zensuren-Typ')
                    ),
                    new TableColumn(
                        'Verwaltung der Zensuren-Typen (Kopfnoten, Leistungsüberprüfung).'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Score') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Berechnungsvorschrift')
                    ),
                    new TableColumn(
                        'Verwaltung der Berechnungsvorschriften für die automatische Durchschnittsberechnung der Zensuren.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Type') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Bewertungssystem')
                    ),
                    new TableColumn(
                        'Hier werden alle verfügbaren Bewertungssysteme angezeigt.
                        Nach der Auswahl eines Bewertungssystems können dem Bewertungssystem die entsprechenden Fach-Klassen zugeordnet werden.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Notenbuch (Ansicht: Lehrer)')
                    ),
                    new TableColumn(
                        'Anzeige der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Notenbuch (Ansicht: Leitung)')
                    ),
                    new TableColumn(
                        'Anzeige aller Notenbücher.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Student/Gradebook') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Notenübersicht')
                    ),
                    new TableColumn(
                        'Anzeige der Zensuren für die Schüler und Eltern. <br>
                        Der angemeldete Schüler sieht nur seine eigenen Zensuren. <br>
                        Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Schützlinge. <br>'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Leistungsüberprüfung (Ansicht: Lehrer)')
                    ),
                    new TableColumn(
                        'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten)
                        , wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Leistungsüberprüfung (Ansicht: Leitung)')
                    ),
                    new TableColumn(
                        'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Notenaufträge (Ansicht: Lehrer)')
                    ),
                    new TableColumn(
                        'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
                         wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'
                    ),
                )) : null,
                Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster') ? new TableRow(array(
                    new TableColumn(
                        new Bold('Notenaufträge (Ansicht: Leitung)')
                    ),
                    new TableColumn(
                        'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebener Zensuren).'
                    ),
                )) : null,
            )))

        );

        return $Stage;
    }
}
