<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;

//require_once( __DIR__.'/../../../../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
//AutoLoader::getNamespaceAutoLoader('Digitick\Sepa', __DIR__.'/../../../../Library/SepaXml/lib');
require_once( __DIR__.'/../../../../Library/SepaXml/vendor/autoload.php' );

/**
 * Class Sepa
 *
 * @package SPHERE\Application\Api\Billing\Sepa
 */
class Sepa implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download',
            __CLASS__.'::downloadSepa'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Credit/Download',
            __CLASS__.'::downloadSepaCredit'
        ));

    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

    /**
     * @param array $Invoice
     *
     * @return string
     */
    public function downloadSepa($Invoice = array())
    {

        $CheckboxList = array();
        if(isset($Invoice['CheckboxList'])){
            $CheckboxList = $Invoice['CheckboxList'];
        }
        $FeeList = array();
        if(isset($Invoice['Fee'])){
            $FeeList = $Invoice['Fee'];
        }
        $BasketId = $Invoice['BasketId'];
        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $directDebit = false;
        if($tblBasket){
            $directDebit = Balance::useService()->createSepaContent($tblBasket, $CheckboxList, $FeeList);
        }

        $name = $tblBasket->getName();
        $month = $tblBasket->getMonth();
        $year = $tblBasket->getYear();
        $monthString = '';
        $monthList = Invoice::useService()->getMonthList($month, $month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        if($directDebit){
            // Retrieve the resulting XML
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="Abrechnung_'.$name.'_'.$monthString.'_'.$year.'.xml"');
            return $directDebit->asXML();
        } else {
            return new Warning('XML Datei enthält keine Sepa-Lastschrift');
        }
    }

    /**
     * @param string $BasketId
     *
     * @return string
     */
    public function downloadSepaCredit($BasketId = '')
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        $customerCredit = false;
        if($tblBasket){
            $customerCredit = Balance::useService()->createSepaCreditContent($tblBasket);
        }

        $name = $tblBasket->getName();
        $month = $tblBasket->getMonth();
        $year = $tblBasket->getYear();
        $monthString = '';
        $monthList = Invoice::useService()->getMonthList($month, $month);
        if(!empty($monthList)){
            $monthString = current($monthList);
        }

        if($customerCredit){
            // Retrieve the resulting XML
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="Abrechnung_'.$name.'_'.$monthString.'_'.$year.'.xml"');
            return $customerCredit->asXML();
        } else {
            return new Warning('XML Datei enthält keine Sepa-Lastschrift');
        }
    }



}
