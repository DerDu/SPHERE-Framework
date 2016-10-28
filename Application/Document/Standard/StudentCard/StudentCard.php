<?php
namespace SPHERE\Application\Document\Standard\StudentCard;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Document\Standard\StudentCard
 */
class StudentCard extends AbstractModule implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schülerkartei'))
        );
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__.'/Twig'), new Link\Name('Schülerkartei aus Twig'))
//        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Twig', __CLASS__ . '::frontendSelectPersonForTwig'
//        ));
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
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');

        $dataList = array();
        if (( $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT') )) {
            if (( $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup) )) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new External(
                            'Herunterladen',
                            'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                            new Download(),
                            array(
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Schülerkartei herunterladen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse',
                                    'Option'   => ''
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

//    /**
//     * @return Stage
//     */
//    public static function frontendSelectPersonForTwig()
//    {
//
//        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');
//
//        $dataList = array();
//        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
//            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
//                foreach ($tblPersonList as $tblPerson) {
//                    $tblAddress = $tblPerson->fetchMainAddress();
//                    $dataList[] = array(
//                        'Name' => $tblPerson->getLastFirstName(),
//                        'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
//                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
//                        'Option' => new External(
//                            'Herunterladen',
//                            'SPHERE\Application\Api\Document\Standard\StudentCard\Download',
//                            new Download(),
//                            array(
//                                'PersonId' => $tblPerson->getId()
//                            ),
//                            'Schülerkartei herunterladen'
//                        )
//                    );
//                }
//            }
//        }
//
//        $Stage->setContent(
//            new Layout(array(
//                new LayoutGroup(array(
//                    new LayoutRow(array(
//                        new LayoutColumn(array(
//                            new TableData(
//                                $dataList,
//                                null,
//                                array(
//                                    'Name' => 'Name',
//                                    'Address' => 'Adresse',
//                                    'Division' => 'Klasse',
//                                    'Option' => ''
//                                )
//                            )
//                        )),
//                    ))
//                )),
//            ))
//        );
//
//        return $Stage;
//    }
}