<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generate\ApiGenerate;
use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Api\Education\ClassRegister\ApiSortDivision;
use SPHERE\Application\Api\Education\ClassRegister\ClassRegister;
use SPHERE\Application\Api\Education\Division\StudentGroupSelect;
use SPHERE\Application\Api\Education\Division\StudentSelect;
use SPHERE\Application\Api\Education\Division\StudentStatus;
use SPHERE\Application\Api\Education\Division\SubjectSelect;
use SPHERE\Application\Api\Education\Division\ValidationFilter;
use SPHERE\Application\Api\Education\Graduation\Evaluation\ApiEvaluation;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradebook;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiMinimumGradeCount;
use SPHERE\Application\Api\Education\Prepare\Prepare;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Education
 *
 * @package SPHERE\Application\Api\Education
 */
class Education implements IApplicationInterface
{

    public static function registerApplication()
    {

        Certificate::registerModule();
        ClassRegister::registerModule();
        ApiDiary::registerApi();
        ApiSortDivision::registerApi();
        Prepare::registerModule();
        SubjectSelect::registerApi();
        StudentSelect::registerApi();
        StudentGroupSelect::registerApi();
        ValidationFilter::registerApi();
        ApiGenerate::registerApi();
        StudentStatus::registerApi();
        ApiGradebook::registerApi();
        ApiEvaluation::registerApi();
        ApiMinimumGradeCount::registerApi();
    }
}
