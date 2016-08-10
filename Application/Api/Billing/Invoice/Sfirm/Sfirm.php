<?php
namespace SPHERE\Application\Api\Billing\Invoice\Sfirm;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Transfer\Export\Invoice\Invoice;

/**
 * Class Sfirm
 *
 * @package SPHERE\Application\Api\Billing\Invoice\Sfirm
 */
class Sfirm
{

    /**
     * @return bool|string
     */
    public function downloadInvoiceAllSfirm()
    {

        $TableHeader = array('Date'          => 'Fälligkeitsdatum',
                             'IBAN'          => 'IBAN',
                             'BIC'           => 'BIC',
                             'CreateDate'    => 'Signaturdatum',
                             'Reference'     => 'Mandatsreferenz',
                             'Bank'          => 'Name der Bank',
                             'Client'        => 'Mandant',
                             'DebtorNumber'  => 'Debitoren-Nr.',
                             'InvoiceNumber' => 'Beleg-Nr.',
                             'BookingText'   => 'Buchungstext',
                             'Owner'         => 'Konto-Inhaber',
                             'Item'          => 'Artikel',
                             'ItemPrice'     => 'Einzelpreis',
                             'Quantity'      => 'Anzahl',
                             'Sum'           => 'Summe',
        );
        $TableContent = Invoice::useService()->createInvoiceListSfirm($TableHeader);
        if ($TableContent) {
            $fileLocation = Invoice::useService()->createInvoiceListExcel($TableContent, $TableHeader);
            if ($fileLocation) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "SFirm ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }
}
