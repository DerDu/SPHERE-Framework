<?php
namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Data;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Accounting\Banking\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Banking
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
     * @return bool|TblDebtor[]
     */
    public function getDebtorAll()
    {

        return (new Data($this->getBinding()))->getDebtorAll();
    }

    /**
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return (new Data($this->getBinding()))->getBankReferenceAll();
    }

    /**
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAll()
    {

        return (new Data($this->getBinding()))->getDebtorSelectionAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceById($Id)
    {

        return (new Data($this->getBinding()))->getBankReferenceById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionById($Id);
    }

    /**
     * @param TblPerson $Person
     *
     * @return false|TblDebtor[]
     */
    public function getDebtorByPerson(TblPerson $Person)
    {

        return (new Data($this->getBinding()))->getDebtorByPerson($Person);
    }

    /**
     * @param $DebtorNumber
     *
     * @return bool|TblDebtor
     */
    public function getDebtorByDebtorNumber($DebtorNumber)
    {

        return (new Data($this->getBinding()))->getDebtorByDebtorNumber($DebtorNumber);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBankReferenceByPerson($tblPerson);
    }

    /**
     * @param $Reference
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceByNumber($Reference)
    {

        return (new Data($this->getBinding()))->getBankReferenceByNumber($Reference);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByPerson($tblPerson);
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByBankReference(TblBankReference $tblBankReference)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByBankReference($tblBankReference);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionByPersonAndItem(TblPerson $tblPerson, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionByPersonAndItemWithoutDebtor(TblPerson $tblPerson, TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
    }

    /**
     * @param $Reference
     *
     * @return false|TblBankReference
     */
    public function getReferenceIsUsed($Reference)
    {

        return (new Data($this->getBinding()))->getReferenceIsUsed($Reference);
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return false|TblDebtorSelection
     */
    public function checkDebtorSelectionDebtor(TblDebtorSelection $tblDebtorSelection)
    {

        return (new Data($this->getBinding()))->checkDebtorSelectionDebtor($tblDebtorSelection);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Debtor
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function createDebtor(IFormInterface &$Stage = null, $Debtor, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Debtor['DebtorNumber'] ) && empty( $Debtor['DebtorNumber'] )) {
            $Stage->setError('Debtor[DebtorNumber]', 'Bitte geben sie die Debitorennummer an');
            $Error = true;
        }
        if (isset( $Debtor['DebtorNumber'] ) && Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
            $Error = true;
        }

        if (!$Error) {
            $tblPerson = Person::useService()->getPersonById($Id);
            if ($tblPerson) {
                (new Data($this->getBinding()))->createDebtor($tblPerson, $Debtor['DebtorNumber']);

                return new Success('Der Debitor ist erfasst worden')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId()));
            } else {
                return new Danger('Person nicht gefunden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPerson           $tblPerson
     * @param null                $Reference
     *
     * @return IFormInterface|string
     */
    public function createReference(
        IFormInterface &$Stage = null,
        TblPerson $tblPerson,
        $Reference = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Reference) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Reference['Reference'] ) && empty( $Reference['Reference'] )) {
            $Stage->setError('Reference[Reference]', 'Bitte geben sie eine Mandatsreferenz an');
            $Error = true;
        } else {
            if (Banking::useService()->getReferenceIsUsed($Reference['Reference'])) {
                $Stage->setError('Reference[Reference]', 'Mandatsreferenz ist schon vergeben');
                $Error = true;
            }
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createReference(
                $tblPerson,
                $Reference['Reference'],
                $Reference['ReferenceDate'],
                $Reference['BankName'],
                $Reference['Owner'],
                $Reference['IBAN'],
                $Reference['BIC']);
            return new Success('Die Mandatsreferenz ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblPerson->getId()));
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblBasket           $tblBasket
     * @param null                $Data
     *
     * @return IFormInterface|string
     */
    public function createDebtorSelection(IFormInterface &$Stage = null, TblBasket $tblBasket, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $PersonArray = array();
        if (is_array($Data)) {
            foreach ($Data as $Key => $Row) {
                if (!isset( $Row['PersonPayers'] ) || empty( $Row['PersonPayers'] )) {
                    $PersonArray[$Row['Person']] = Person::useService()->getPersonById($Row['Person']);
                    $Error = true;
                }
                if (!$Error) {

                    $tblPerson = Person::useService()->getPersonById($Row['Person']);
                    $tblPersonPayers = Person::useService()->getPersonById($Row['PersonPayers']);
                    $tblPaymentType = Balance::useService()->getPaymentTypeById($Row['Payment']);
                    $tblItem = Item::useService()->getItemById($Row['Item']);

                    (new Data ($this->getBinding()))->createDebtorSelection(
                        $tblPerson,
                        $tblPersonPayers,
                        $tblPaymentType,
                        $tblItem);
                }
            }
            if (!$Error) {
                return new Success('Daten erfasst')
                .new Redirect('/Billing/Accounting/DebtorSelection/Payment/Choose', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
            }
            if ($Error === true && !empty( $PersonArray )) {
                /** @var TblPerson $Person */
                foreach ($PersonArray as $Person) {
                    $Stage .= new Warning('Bezahler für '.$Person->getfullName().' ist noch nicht eingerichtet');
                }
            }
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDebtor           $tblDebtor
     * @param                     $Debtor
     *
     * @return IFormInterface|string
     */
    public function changeDebtor(IFormInterface &$Stage = null, TblDebtor $tblDebtor, $Debtor)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Debtor['DebtorNumber'] ) && empty( $Debtor['DebtorNumber'] )) {
            $Stage->setError('Debtor[DebtorNumber]', 'Bitte geben sie die Debitorennummer an');
            $Error = true;
        }
        if (isset( $Debtor['DebtorNumber'] ) && Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])) {
            if ($tblDebtor->getId() !== Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])->getId()) {
                $Stage->setError('Debtor[DebtorNumber]',
                    'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
                $Error = true;
            }
        }
        if (!$tblDebtor->getServiceTblPerson()) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Person nicht mehr gefunden');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateDebtor($tblDebtor, $Debtor['DebtorNumber'])) {
                return new Success('Die Debitor-Nummer ist erfasst')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblDebtor->getServiceTblPerson()->getId()));
            } else {
                return new Danger('Die Debitor-Nummer konnte nicht erfasst werden')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblDebtor->getServiceTblPerson()->getId()));
            }
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblBankReference    $tblBankReference
     * @param                     $Reference
     *
     * @return IFormInterface|string
     */
    public function changeReference(
        IFormInterface &$Stage = null,
        TblBankReference $tblBankReference,
        $Reference
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Reference) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Reference['Reference'] ) && empty( $Reference['Reference'] )) {
            $Stage->setError('Reference[Reference]', 'Bitte geben sie eine Mandatsreferenz an');
            $Error = true;
        } else {
            if (( $FindBankReference = Banking::useService()->getBankReferenceByNumber($Reference['Reference']) )) {
                if ($FindBankReference->getId() !== $tblBankReference->getId()) {
                    $Stage->setError('Reference[Reference]', 'Mandatsreferenz ist schon vergeben');
                    $Error = true;
                }
            }
        }
        if (isset( $Reference['ReferenceDate'] ) && empty( $Reference['ReferenceDate'] )) {
            $Stage->setError('Reference[ReferenceDate]', 'Bitte geben sie ein Datum an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->getBinding()))->updateReference(
                $tblBankReference,
                $Reference['Reference'],
                $Reference['ReferenceDate'],
                $Reference['Owner'],
                $Reference['BankName'],
                $Reference['IBAN'],
                $Reference['BIC']);
            if ($tblBankReference->getServiceTblPerson()) {
                return new Success('Änderungen an Informationen zur Mandatsreferenz sind erfasst')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblBankReference->getServiceTblPerson()->getId()));
            } else {
                return new Warning('Person nicht mehr gefunden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_SUCCESS);
            }

        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPerson           $tblPerson
     * @param null                $Data
     *
     * @return IFormInterface|string
     */
    public function changeDebtorSelectionPayer(IFormInterface &$Stage = null, TblPerson $tblPerson, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $PersonPayers = false;

        if (is_array($Data)) {
            // Testdurchlauf durch alle Eingaben
            foreach ($Data as $Key => $Row) {
                $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                if ($tblDebtorSelection) {
                    if (!isset( $Row['PersonPayers'] ) || empty( $Row['PersonPayers'] )) {
                        $Stage->setError('[Data]['.$Key.'][PersonPayers]', 'Bezahler benötigt!');
                        $Error = true;
                        $PersonPayers = true;
                    }
                }
            }
            if (!$Error) {
                // Durchführung nach bestehen des Testdurchlauf's
                foreach ($Data as $Key => $Row) {
                    $tblPersonPayers = Person::useService()->getPersonById($Row['PersonPayers']);
                    $tblPaymentType = Balance::useService()->getPaymentTypeById($Row['Payment']);
                    $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                    if ($tblDebtorSelection) {
                        if (!$Error) {
                            (new Data ($this->getBinding()))->changeDebtorSelection(
                                $tblDebtorSelection,
                                $tblPersonPayers,
                                $tblPaymentType,
                                null,
                                null);
                        }
                    }
                }
            }

            if (!$Error) {
                return new Success('Daten erfasst')
                .new Redirect('/Billing/Accounting/DebtorSelection/PaymentChoose', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()));
            }
            if ($Error && $PersonPayers) {
                $Stage .= new Warning('Bitte zuerst Daten zum bezahlen für die Beziehungstehenden (bezahlenden) Personen anlegen');
            }
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Data
     *
     * @return IFormInterface|string
     */
    public function changeDebtorSelectionInfo(IFormInterface &$Stage = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $Debtor = false;
        $Bank = false;

        if (is_array($Data)) {
            // Testdurchlauf durch alle Eingaben
            foreach ($Data as $Key => $Row) {
                $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                if ($tblDebtorSelection) {
                    if (!isset( $Row['Debtor'] ) || empty( $Row['Debtor'] )) {
                        $Stage->setError('[Data]['.$Key.'][Debtor]', 'Debitor benötigt!');
                        $Error = true;
                        $Debtor = true;
                    }
                    if ($tblDebtorSelection->getServiceTblPaymentType()->getName() == 'SEPA-Lastschrift') {
                        if (!isset( $Row['Reference'] ) || empty( $Row['Reference'] )) {
                            $Stage->setError('[Data]['.$Key.'][Reference]', 'Auswahl treffen!');
                            $Error = true;
                            $Bank = true;
                        }
                    }
                }
            }
            if (!$Error) {
                // Durchführung nach bestehen des Testdurchlauf's
                foreach ($Data as $Key => $Row) {
                    $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                    if ($tblDebtorSelection) {

                        if (!$Error) {

                            $tblDebtor = Banking::useService()->getDebtorById($Row['Debtor']);

                            if (isset( $Row['Reference'] )) {
                                $tblBankReference = Banking::useService()->getBankReferenceById($Row['Reference']);
                            } else {
                                $tblBankReference = null;
                            }


                            (new Data ($this->getBinding()))->updateDebtorSelection(
                                $tblDebtorSelection,
                                $tblDebtor,
                                $tblBankReference);
                        }
                    }
                }
            }

            if (!$Error) {
                return new Success('Daten erfasst')
                .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_SUCCESS);
            }
            if ($Error && $Debtor) {
                $Stage .= new Warning('Gewählte Bezahler haben keine Debitor-Nummer ausgewählt');
            }
            if ($Error && $Bank) {
                $Stage .= new Warning('Gewählte Bezahler haben keine Referenz-Nummer ausgeählt');
            }
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblBasket           $tblBasket
     * @param null                $Data
     *
     * @return IFormInterface|string
     */
    public function updateDebtorSelection(IFormInterface &$Stage = null, TblBasket $tblBasket, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        $Debtor = false;
        $Bank = false;

        if (is_array($Data)) {
            // Testdurchlauf durch alle Eingaben
            foreach ($Data as $Key => $Row) {
                $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                if ($tblDebtorSelection) {
                    if (!isset( $Row['Debtor'] ) || empty( $Row['Debtor'] )) {
                        $Stage->setError('[Data]['.$Key.'][Debtor]', 'Debitor benötigt!');
                        $Error = true;
                        $Debtor = true;
                    }
                    if ($tblDebtorSelection->getServiceTblPaymentType()->getName() == 'SEPA-Lastschrift') {
                        if (!isset( $Row['Reference'] ) || empty( $Row['Reference'] )) {
                            $Stage->setError('[Data]['.$Key.'][Reference]', 'Auswahl treffen!');
                            $Error = true;
                            $Bank = true;
                        }
                    }
                }
            }
            if (!$Error) {
                // Durchführung nach bestehen des Testdurchlauf's
                foreach ($Data as $Key => $Row) {
                    $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($Key);

                    if ($tblDebtorSelection) {

                        if (!$Error) {

                            $tblDebtor = Banking::useService()->getDebtorById($Row['Debtor']);
                            if (isset( $Row['Reference'] )) {
                                $tblBankReference = Banking::useService()->getBankReferenceById($Row['Reference']);
                            } else {
                                $tblBankReference = null;
                            }

                            (new Data ($this->getBinding()))->updateDebtorSelection(
                                $tblDebtorSelection,
                                $tblDebtor,
                                $tblBankReference);
                        }
                    }
                }
            }

            if (!$Error) {
                return new Success('Daten erfasst')
                .new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()));
            }
            if ($Error && $Debtor) {
                $Stage .= new Warning('Gewählte Bezahler haben keine Debitor-Nummer ausgewählt');
            }
            if ($Error && $Bank) {
                $Stage .= new Warning('Gewählte Bezahler haben keine Referenz-Nummer ausgeählt');
            }
        }

        return $Stage;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|string
     */
    public function removeDebtor(TblDebtor $tblDebtor)
    {

        if (null === $tblDebtor) {
            return '';
        }

        if ((new Data($this->getBinding()))->removeDebtor($tblDebtor)) {
            return new Success('Der Debitor wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS);
        }

        return new Danger('Der Debitor konnte nicht gelöscht werden')
        .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return bool|string
     */
    public function removeBankReference(TblBankReference $tblBankReference)
    {

        if (null === $tblBankReference) {
            return '';
        }

        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByBankReference($tblBankReference);
        if ($tblDebtorSelectionList) {
            foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                Banking::useService()->destroyDebtorSelection($tblDebtorSelection);
            }
        }

        return (new Data($this->getBinding()))->removeReference($tblBankReference);
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return bool
     */
    public function destroyDebtorSelection(TblDebtorSelection $tblDebtorSelection)
    {

        if (null === $tblDebtorSelection) {
            return false;
        }

        return (new Data($this->getBinding()))->destroyDebtorSelection($tblDebtorSelection);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function destroyDebtorSelectionByPerson(TblPerson $tblPerson)
    {

        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);
        $Complete = true;
        if ($tblDebtorSelectionList) {
            foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                if (!$this->destroyDebtorSelection($tblDebtorSelection)) {
                    $Complete = false;
                }
            }
        }
        return $Complete;
    }
}
