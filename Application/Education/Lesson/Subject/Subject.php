<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Subject
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Subject implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Fächer'),
                new Link\Icon(new Listing()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Category', __NAMESPACE__.'\Frontend::frontendCreateCategory'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Category', __NAMESPACE__.'\Frontend::frontendChangeCategory'
        )->setParameterDefault('Id', null)
            ->setParameterDefault('Category', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Category', __NAMESPACE__.'\Frontend::frontendDestroyCategory'
        )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Category', __NAMESPACE__.'\Frontend::frontendLinkCategory'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Person', __NAMESPACE__.'\Frontend::frontendLinkPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Person/Add', __NAMESPACE__.'\Frontend::frontendSubjectPersonAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Subject', __NAMESPACE__.'\Frontend::frontendCreateSubject'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change/Subject', __NAMESPACE__.'\Frontend::frontendChangeSubject'
        )->setParameterDefault('Id', null)
            ->setParameterDefault('Subject', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy/Subject', __NAMESPACE__.'\Frontend::frontendDestroySubject'
        )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Subject', __NAMESPACE__.'\Frontend::frontendLinkSubject'
        ));
    }

    /**
     * @return Frontend
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

        $Stage = new Stage('Fächer', 'Dashboard');

        $Stage->addButton(new Standard('Fächer', __NAMESPACE__.'\Create\Subject', new Education(), null,
            'Erstellen/Bearbeiten'));
        $Stage->addButton(new Standard('Kategorien', __NAMESPACE__.'\Create\Category', new MoreItems(), null,
            'Erstellen/Bearbeiten'));

        $tblGroupAll = $this->useService()->getGroupAll();
        $Content = array();

        array_push($Content, new LayoutRow(array(
            new LayoutColumn(array(
                new Title(new Italic(new Bold('Unzugeordnet'))),
            ))
        )));

        $tblUnusedSubjectAll = $this->useService()->getSubjectAllHavingNoCategory();
        if ($tblUnusedSubjectAll) {
            array_walk($tblUnusedSubjectAll, function (TblSubject &$tblSubject) {

                $tblSubject = new Bold($tblSubject->getAcronym()).' - '
                    .$tblSubject->getName().' '
                    .new Small(new Muted($tblSubject->getDescription()));
            });
        } else {
            $tblUnusedSubjectAll = new Enable().' '.new Success('Keine unzugeordneten Fächer');
        }
        $tblUnusedCategoryAll = $this->useService()->getCategoryAllHavingNoGroup();
        if ($tblUnusedCategoryAll) {
            array_walk($tblUnusedCategoryAll, function (TblCategory &$tblCategory) {

                $tblCategory = new Bold($tblCategory->getName()).' - '
                    .new Small(new Muted($tblCategory->getDescription()));
            });
        } else {
            $tblUnusedCategoryAll = new Enable().' '.new Success('Keine unzugeordneten Kategorien');
        }

        array_push($Content, new LayoutRow(array(
            new LayoutColumn(new Panel('Kategorien', $tblUnusedCategoryAll), 6),
            new LayoutColumn(new Panel('Fächer', $tblUnusedSubjectAll), 6),
        )));

        // Payload
        array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$Content) {

            array_push($Content, new LayoutRow(array(
                new LayoutColumn(array(
                    new Title('Gruppe: '.new Bold($tblGroup->getName()), $tblGroup->getDescription()),
                    new Standard('Zuweisen von Kategorien', __NAMESPACE__.'\Link\Category', new Transfer(),
                        array('Id' => $tblGroup->getId())
                    ),
                    ( $tblGroup->getName() == 'Neigungskurs' ?
                        new Standard('Zuweisen von Personen', __NAMESPACE__.'\Link\Person', new Transfer(),
                            array('Id' => $tblGroup->getId())
                        ) : '' ),
                    ( $tblGroup->getName() == 'Wahlfach' ?
                        new Standard('Zuweisen von Personen', __NAMESPACE__.'\Link\Person', new Transfer(),
                            array('Id' => $tblGroup->getId())
                        ) : '' )
                ))
            )));
            $tblCategoryAll = $this->useService()->getCategoryAllByGroup($tblGroup);
            if ($tblCategoryAll) {
                array_walk($tblCategoryAll, function (TblCategory $tblCategory) use (&$Content, $tblGroup) {

                    $tblSubjectAll = $this->useService()->getSubjectAllByCategory($tblCategory);
                    if (is_array($tblSubjectAll)) {
                        array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {

                            $tblSubject = new Bold($tblSubject->getAcronym()).' - '
                                .$tblSubject->getName().' '
                                .new Small(new Muted($tblSubject->getDescription()));
                        });
                    }

                    $Height = floor(( ( count($tblSubjectAll) + 2 ) / 3 ) + 1);
                    Main::getDispatcher()->registerWidget($tblGroup->getIdentifier(),
                        new Panel(
                            $tblCategory->getName().' '.$tblCategory->getDescription(),
                            $tblSubjectAll,
                            ( $tblCategory->isLocked() ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT ),
                            new Standard('Zuweisen von Fächern', __NAMESPACE__.'\Link\Subject', new Transfer(),
                                array('Id' => $tblCategory->getId())
                            )
                        )
                        , 2, ( $Height ? $Height : $Height + 2 ));
                });
            }
            array_push($Content, new LayoutRow(array(
                new LayoutColumn(Main::getDispatcher()->fetchDashboard($tblGroup->getIdentifier()))
            )));
        });

        $Stage->setContent(
            new Layout(new LayoutGroup($Content))
        );
        return $Stage;
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Subject', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
