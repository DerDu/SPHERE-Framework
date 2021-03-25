<?php
namespace SPHERE\Application\Education\Lesson\Course;

use SPHERE\Application\Education\School\Course\Course as CourseModul;
use SPHERE\Application\Education\School\Course\Service;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Briefcase;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Course
 *
 * @package SPHERE\Application\Education\School\Course
 */
class Course implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\Course\Course'), new Link\Name('Ausbildung'), new Link\Icon(new Briefcase()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\Course\SubjectArea'), new Link\Name('Fachrichtung'), new Link\Icon(new Book()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ .'/Course', __NAMESPACE__.'\Frontend::frontendCourse'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ .'/SubjectArea', __NAMESPACE__.'\Frontend::frontendSubjectArea'
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
     * @return Service
     */
    public static function useService()
    {
        return CourseModul::useService();
    }

}
