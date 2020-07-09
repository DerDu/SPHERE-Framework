<?php
namespace SPHERE\Application\Api\Reporting;

use SPHERE\Application\Api\Reporting\CheckList\CheckList;
use SPHERE\Application\Api\Reporting\Custom\Custom;
use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Application\Api\Reporting\DeclarationBasis\DeclarationBasis;
use SPHERE\Application\Api\Reporting\SerialLetter\ApiSerialLetter;
use SPHERE\Application\Api\Reporting\SerialLetter\SerialLetter;
use SPHERE\Application\Api\Reporting\Standard\ApiStandard;
use SPHERE\Application\Api\Reporting\Standard\Standard;
use SPHERE\Application\Api\Reporting\Univention\Univention;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Reporting
 */
class Reporting implements IApplicationInterface
{

    public static function registerApplication()
    {

        Custom::registerModule();
        Standard::registerModule();
        ApiStandard::registerApi();
        CheckList::registerModule();
        ApiSerialLetter::registerApi();
        SerialLetter::registerModule();
        ApiIndividual::registerApi();
        ApiIndividual::registerModule();
        DeclarationBasis::registerModule();
        Univention::registerModule();
    }
}
