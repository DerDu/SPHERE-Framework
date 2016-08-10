<?php
namespace SPHERE\Application\Api\Billing\Invoice;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Billing\Invoice
 */
class Invoice implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __NAMESPACE__.'\Invoice::downloadPrepareInvoice'
        ));

        // ToDO Datev + SFirm
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Datev/Download', __NAMESPACE__.'\Datev\Datev::downloadInvoiceAllDatev'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Sfirm/Download', __NAMESPACE__.'\Sfirm\Sfirm::downloadInvoiceAllSfirm'
//        ));
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
     * @param $Filter
     *
     * @return bool|string
     */
    public function downloadPrepareInvoice(
        $Filter
    ) {

        $Filter = json_decode($Filter);

        if (!empty( $Filter->Error )) {
            return new Warning('Übergabe nicht auswertbar');
        }
        $Filter = current($Filter->Data);

        $Status = ( $Filter->Status == 1 ? 'Offene Rechnungen' : ( $Filter->Status == 2 ? 'Bezahlte Rechnungen' : 'Stornierte Rechnungen' ) );


        $tblInvoiceList = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->getInvoiceListByDate($Filter->DateFrom, $Filter->DateTo, $Filter->Status);

        $TableHeader = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->getHeader($Filter);

        $TableContent = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->createInvoiceListByPrepare(
//            $TableHeader,
            $tblInvoiceList,
            $Filter->PersonFrom,
            $Filter->StudentNumber,
            $Filter->IBAN,
            $Filter->BIC,
            $Filter->Client,
            $Filter->BankName,
            $Filter->Owner,
            $Filter->Billers,
            $Filter->SchoolIBAN,
            $Filter->SchoolBIC,
            $Filter->SchoolBankName,
            $Filter->SchoolOwner,
            true);
        if ($TableContent) {
            $fileLocation = \SPHERE\Application\Transfer\Export\Invoice\Invoice::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    $Status." ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

}
