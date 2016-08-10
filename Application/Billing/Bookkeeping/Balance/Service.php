<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblPayment
     */
    public function getPaymentById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentById($Id);
    }

    /**
     * @return bool|TblPayment[]
     */
    public function getPaymentAll()
    {

        return (new Data($this->getBinding()))->getPaymentAll();
    }


    /**
     * @param $Id
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentTypeById($Id);
    }

    /**
     * @return false|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return (new Data($this->getBinding()))->getPaymentTypeAll();
    }

    /**
     * @param $Name
     *
     * @return false|TblPaymentType
     */
    public function getPaymentTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getPaymentTypeByName($Name);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return false|Service\Entity\TblPayment[]
     */
    public function getPaymentAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getPaymentAllByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return int
     */
    public function getPaidFromInvoice(TblInvoice $tblInvoice)
    {

        $result = 0;
        $tblPaymentList = Balance::useService()->getPaymentAllByInvoice($tblInvoice);
        if ($tblPaymentList) {
            /** @var TblPayment $tblPayment */
            foreach ($tblPaymentList as $tblPayment) {
                $result += $tblPayment->getValue();
            }
        }
        return $result;
    }

    /**
     * @param $Value
     *
     * @return string
     */
    public function getPriceString($Value)
    {

        $Value = number_format($Value, 2);
        return str_replace('.', ',', $Value).' €';
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblInvoice          $tblInvoice
     * @param                     $Payment
     *
     * @return IFormInterface|string
     */
    public function createPayment(IFormInterface &$Stage = null, TblInvoice $tblInvoice, $Payment)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Payment
        ) {
            return $Stage;
        }

        $Error = false;

        if (!isset( $Payment['Value'] ) && empty( $Payment['Value'] )) {
            $Stage->setError('Payment[Value]', 'Bitte geben Sie einen Betrag an');
            $Error = true;
        } else {
            $Payment['Value'] = str_replace(',', '.', $Payment['Value']);
            if (!is_numeric($Payment['Value'])) {
                $Stage->setError('Payment[Value]', 'Bitte geben Sie einen Zahl als Betrag an');
                $Error = true;
            }
        }
        if (!( $tblPaymentType = Balance::useService()->getPaymentTypeById($Payment['Payment']) )) {
            $Stage->setError('Payment[Payment]', 'Bitte geben Sie ein Bezahlart an');
            $Error = true;
        }
        if (!$Error) {
            if (empty( $Payment['Purpose'] )) {
                $Purpose = 'kein Eintrag';
            } else {
                $Purpose = $Payment['Purpose'];
            }
            $tblPaymentType = Balance::useService()->getPaymentTypeById($Payment['Payment']);

            $tblPayment = (new Data($this->getBinding()))->createPayment(
                $tblPaymentType, $Payment['Value'], $Purpose
            );
            (new Data($this->getBinding()))->createInvoicePayment($tblInvoice, $tblPayment);

            $InvoicePrice = Invoice::useService()->getInvoicePrice($tblInvoice);
            $PaidMoney = Balance::useService()->getPaidFromInvoice($tblInvoice);

            if ($InvoicePrice == $PaidMoney) {
                Invoice::useService()->changeInvoiceIsPaid($tblInvoice, true);
                return new Success('Rechnung wurde vollständig bezahlt')
                .new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_SUCCESS);
            } elseif ($InvoicePrice < $PaidMoney) {
                return new Danger('Rechnung enthält mehr Geld als gefordert')
                .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_ERROR
                    , array('Id' => $tblInvoice->getId()));
            }

            return new Success('Teil der Rechnung wurde bezahlt')
            .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_SUCCESS
                , array('Id' => $tblInvoice->getId()));
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblInvoice          $tblInvoice
     * @param TblPayment          $tblPayment
     * @param                     $Payment
     *
     * @return IFormInterface|string
     */
    public function changePayment(IFormInterface &$Stage = null, TblInvoice $tblInvoice, TblPayment $tblPayment, $Payment)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Payment
        ) {
            return $Stage;
        }

        $Error = false;

        if (!isset( $Payment['Value'] ) && empty( $Payment['Value'] )) {
            $Stage->setError('Payment[Value]', 'Bitte geben Sie einen Betrag an');
            $Error = true;
        } else {
            $Payment['Value'] = str_replace(',', '.', $Payment['Value']);
            if (!is_numeric($Payment['Value'])) {
                $Stage->setError('Payment[Value]', 'Bitte geben Sie einen Zahl als Betrag an');
                $Error = true;
            }
        }
        if (!( $tblPaymentType = Balance::useService()->getPaymentTypeById($Payment['Payment']) )) {
            $Stage->setError('Payment[Payment]', 'Bitte geben Sie ein Bezahlart an');
            $Error = true;
        }

        if (!$Error) {
            if (empty( $Payment['Purpose'] )) {
                $Purpose = 'kein Eintrag';
            } else {
                $Purpose = $Payment['Purpose'];
            }
            $tblPaymentType = Balance::useService()->getPaymentTypeById($Payment['Payment']);

            (new Data($this->getBinding()))->changePayment(
                $tblPayment, $tblPaymentType, $Payment['Value'], $Purpose
            );

            $InvoicePrice = Invoice::useService()->getInvoicePrice($tblInvoice);
            $PaidMoney = Balance::useService()->getPaidFromInvoice($tblInvoice);

            if ($InvoicePrice == $PaidMoney) {
                Invoice::useService()->changeInvoiceIsPaid($tblInvoice, true);
                return new Success('Rechnung wurde vollständig bezahlt')
                .new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_SUCCESS);
            } elseif ($InvoicePrice < $PaidMoney) {
                return new Danger('Rechnung enthält mehr Geld als gefordert')
                .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_ERROR
                    , array('Id' => $tblInvoice->getId()));
            }

            return new Success('Teil der Rechnung wurde bezahlt')
            .new Redirect('/Billing/Bookkeeping/Balance/View', Redirect::TIMEOUT_SUCCESS
                , array('Id' => $tblInvoice->getId()));
        }
        return $Stage;
    }
}