<?php
namespace SPHERE\Application\Api\Billing\Invoice\Datev;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Transfer\Export\Invoice\Invoice;

/**
 * Class Datev
 *
 * @package SPHERE\Application\Api\Billing\Invoice\Datev
 */
class Datev
{

    /**
     * @return bool|string
     */
    public function downloadInvoiceAllDatev()
    {

        $TableHeader = array('StudentNumber' => 'Schülernummer',
                             'Item'          => 'Artikel',
                             'ItemPrice'     => 'Summe',
                             'Reference'     => 'Mandat',
                             'BillDate'      => 'Belegdatum',
                             'Date'          => 'Fälligkeitsdatum',
                             'BookingText'   => 'Buchungstext');
        $TableContent = Invoice::useService()->createInvoiceListDatev($TableHeader);
        if ($TableContent) {
            $fileLocation = Invoice::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Datev ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }
}
