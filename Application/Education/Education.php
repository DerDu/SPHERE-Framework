<?php
namespace SPHERE\Application\Education;

use SPHERE\Application\Education\Certificate\Certificate;
use SPHERE\Application\Education\ClassRegister\ClassRegister;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Graduation\Graduation;
use SPHERE\Application\Education\Lesson\Lesson;
use SPHERE\Application\Education\School\School;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Frontend\Icon\Repository\Education as EducationIcon;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Education
 *
 * @package SPHERE\Application\Education
 */
class Education implements IClusterInterface
{

    public static function registerCluster()
    {

        School::registerApplication();
        Lesson::registerApplication();
        Graduation::registerApplication();
        Certificate::registerApplication();
        ClassRegister::registerApplication();
        Diary::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Bildung'), new Link\Icon(new EducationIcon()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Bildung');

        return $Stage;
    }
}
