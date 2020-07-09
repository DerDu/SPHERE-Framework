<?php
namespace SPHERE\Application\Api\Billing;

use SPHERE\Application\Api\Billing\Accounting\ApiBankAccount;
use SPHERE\Application\Api\Billing\Accounting\ApiBankReference;
use SPHERE\Application\Api\Billing\Accounting\ApiCauser;
use SPHERE\Application\Api\Billing\Accounting\ApiCreditor;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtor;
use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Billing\Balance\BalanceDownload;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketRepayment;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketRepaymentAddPerson;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketVerification;
use SPHERE\Application\Api\Billing\Datev\Datev;
use SPHERE\Application\Api\Billing\Inventory\ApiDocument;
use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Api\Billing\Inventory\Import;
use SPHERE\Application\Api\Billing\Invoice\ApiInvoiceIsPaid;
use SPHERE\Application\Api\Billing\Invoice\InvoiceDownload;
use SPHERE\Application\Api\Billing\Sepa\ApiSepa;
use SPHERE\Application\Api\Billing\Sepa\Sepa;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Billing
 */
class Billing implements IApplicationInterface
{

    public static function registerApplication()
    {

        ApiSetting::registerApi();
        ApiItem::registerApi();
        ApiCauser::registerApi();
        ApiCreditor::registerApi();
        ApiDebtor::registerApi();
        ApiDebtorSelection::registerApi();
        ApiBankAccount::registerApi();
        ApiBankReference::registerApi();
        ApiBasket::registerApi();
        ApiBasketRepayment::registerApi();
        ApiBasketRepaymentAddPerson::registerApi();
        ApiBasketVerification::registerApi();
        ApiInvoiceIsPaid::registerApi();
        BalanceDownload::registerModule();
        ApiSepa::registerApi();
        Sepa::registerModule();
        ApiDocument::registerApi();
        Datev::registerModule();
        InvoiceDownload::registerModule();
        Import::registerModule();
    }
}
