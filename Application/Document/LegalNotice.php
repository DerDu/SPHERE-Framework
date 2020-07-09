<?php
namespace SPHERE\Application\Document;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IClusterInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

class LegalNotice implements IClusterInterface, IApplicationInterface, IModuleInterface
{

    public static function registerCluster()
    {

        self::registerApplication();
    }

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\LegalNotice', __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Impressum', 'alle Angaben nach §5 TMG');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'Schulstiftung der Ev.-Luth. Landeskirche Sachsens',
                                'Franklinstr. 22',
                                '01069 Dresden'
                            )),
                        ))
                    ), new Title(
                        'Herausgeber'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'rechtsfähige kirchliche Stiftung des bürgerlichen Rechts'
                            )),
                        ))
                    ), new Title(
                        'Art der Stiftung:'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'Volker Schmidt und Martin Herold'
                            )),
                        ))
                    ), new Title(
                        'Vorstand:'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'Evangelisch-Lutherisches Landeskirchenamt Sachsens'
                            )),
                        ))
                    ), new Title(
                        'Aufsichtsbehörde:'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'Tel.: 0351/47933060',
                                'Fax: 0351/479330699',
                                'schulstiftung@evlks.de',
                            )),
                        ))
                    ), new Title(
                        'Kontakt:'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Listing(array(
                                'Bitte wenden Sie sich für den Support der Schulsoftware direkt an die entsprechenden
                                Ansprechpartner in Ihrer Schule.',
                            )),
                        ))
                    ), new Title(
                        'Support:'
                    )
                ),
            ))
        );
        return $Stage;
    }
}
