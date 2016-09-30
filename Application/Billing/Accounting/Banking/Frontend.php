<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title as TitleTable;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Banking
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBanking()
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitoren');
        $Stage->setDescription('Übersicht');
//        $Stage->setMessage('Zeigt die verfügbaren Debitoren an');
//        $Stage->addButton(
//            new Standard('Debitor anlegen', '/Billing/Accounting/Banking/Person', new Plus())
//        );
//        new Backward();
        $TableContent = array();
        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContent) {


                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = new WarningText('Nicht hinterlegt');
                if ($tblAddress) {
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
                $Item['Debtor'] = '';
                if ($tblDebtorList) {
                    $Item['Debtor'] = new SuccessText(count($tblDebtorList).'x Vorhanden');
                }
                $ReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
                $Item['Reference'] = '';
                if ($ReferenceList) {
                    $Item['Reference'] = new SuccessText(count($ReferenceList).'x Vorhanden');
                }

                $Item['Option'] =
                    ( new Standard('', '/Billing/Accounting/Banking/View',
                        new Equalizer(), array(
                            'Id' => $tblPerson->getId()
                        ), 'Debitor/Mandatsreferenz') )->__toString();

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Person'    => 'Person',
                                    'Address'   => 'Adresse',
                                    'Debtor'    => 'Debitor-Nummer',
                                    'Reference' => 'Mandatsreferenz',
                                    'Option'    => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen / '.new Edit().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Debtor
     * @param null $Reference
     *
     * @return Stage|string
     */
    public function frontendBankingView($Id = null, $Debtor = null, $Reference = null)
    {

        $Stage = new Stage('Debitor', 'Anlegen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Auf die Person konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }


        $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
        if ($tblDebtorList) {
            $TableContentDebtor = array();
            array_walk($tblDebtorList, function (TblDebtor $tblDebtor) use (&$TableContentDebtor, $tblPerson) {
                $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                $Item['Option'] = new Standard('', '/Billing/Accounting/Banking/Debtor/Change', new Edit(),
                    array('Id'       => $tblPerson->getId(),     //ToDO Edit and Remove available?
                          'DebtorId' => $tblDebtor->getId()));
//                    .new Standard('', '/Billing/Accounting/Banking/Debtor/Remove', new Disable(), array('Id' => $tblPerson->getId(),
//                                                               'DebtorId' => $tblDebtor->getId()));
                array_push($TableContentDebtor, $Item);
            });
        }
        $tblReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
        if ($tblReferenceList) {
            $TableContentReference = array();
            array_walk($tblReferenceList, function (TblBankReference $tblBankReference) use (&$TableContentReference, $tblPerson) {
                $Item['Reference'] = $tblBankReference->getReference();
                $Item['ReferenceDate'] = $tblBankReference->getReferenceDate();
                $Item['Owner'] = $tblBankReference->getOwner();
                $Item['BankName'] = $tblBankReference->getBankName();
                $Item['IBAN'] = $tblBankReference->getIBANFrontend();
                $Item['BIC'] = $tblBankReference->getBICFrontend();
                $Item['Option'] = new Standard('', '/Billing/Accounting/Banking/Reference/Change', new Edit(),
                        array('Id'              => $tblPerson->getId(),
                              'BankReferenceId' => $tblBankReference->getId()), 'Bearbeiten')
                    .new Standard('', '/Billing/Accounting/Banking/Reference/Remove', new Disable(),
                        array('Id'              => $tblPerson->getId(),
                              'BankReferenceId' => $tblBankReference->getId()));
                array_push($TableContentReference, $Item);
            });
        }

        $FormReference = $this->formReference();
        $FormReference->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $FormDebtor = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutPersonPanel($tblPerson, true)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht Debitornummer(n)'),
                            ( empty( $TableContentDebtor ) ? new Warning('Keine Debitor-Nummer vergeben') :
                                new TableData($TableContentDebtor, null,
                                    array('DebtorNumber' => 'Debitoren-Nummer',
                                          'Option'       => ''
                                    )
                                ) )
                        ), 3),
                        new LayoutColumn(array(
                            new Title(new ListingTable().' Übersicht Referenz(en)'),
                            ( empty( $TableContentReference ) ? new Warning('Keine Mandats-Referenzen vergeben') :
                                new TableData($TableContentReference, null,
                                    array('Reference'     => 'Mandatsreferenz-Nummer',
                                          'ReferenceDate' => 'Gültig ab:',
                                          'Owner'         => 'Kontoinhaber',
                                          'BankName'      => 'Name der Bank',
                                          'IBAN'          => 'IBAN',
                                          'BIC'           => 'BIC',
                                          'Option'        => ''
                                    )
                                ) )
                        ), 9)
                    ))
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new PlusSign().' Hinzufügen'),
                            new Well(
                                Banking::useService()->createDebtor(
                                    $FormDebtor, $Debtor, $Id
                                ))
                        ), 3),
                        new LayoutColumn(array(
                            new Title(new PlusSign().' Hinzufügen'),
                            new Well(
                                Banking::useService()->createReference(
                                    $FormReference, $tblPerson, $Reference)
                            )
                        ), 9)
                    ))
                )
            )
        );
        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $thirdPanel
     *
     * @return Layout
     */
    public function layoutPersonPanel(TblPerson $tblPerson, $thirdPanel = false)
    {

        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        if ($thirdPanel) {
            $Button = '';
            $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
            if ($tblPaymentType) {
                $Button .= new Standard($tblPaymentType->getName(), '/Billing/Accounting/DebtorSelection/Payment', null,
                    array('Id'            => $tblPerson->getId(),
                          'PaymentTypeId' => $tblPaymentType->getId()));
            }
            $tblPaymentType = Balance::useService()->getPaymentTypeByName('Bar');
            if ($tblPaymentType) {
                $Button .= new Standard($tblPaymentType->getName(), '/Billing/Accounting/DebtorSelection/Payment', null,
                    array('Id'            => $tblPerson->getId(),
                          'PaymentTypeId' => $tblPaymentType->getId()));
            }
//            $tblPaymentTypeAll = Balance::useService()->getPaymentTypeAll();
//            if($tblPaymentTypeAll){
//                foreach($tblPaymentTypeAll as $tblPaymentType){
//                    $Button .= new Standard($tblPaymentType->getName(), '/Billing/Accounting/Banking/Payment', null,
//                        array('Id' => $tblPerson->getId(),
//                              'PaymentTypeId' => $tblPaymentType->getId()));
//                }
//            }
            $PersonPanel = new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                                , 4),
                            new LayoutColumn(
                                new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                    new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                                , 4),
                            new LayoutColumn(
                                new Panel('Zuweisen einer Zahlungs-Option',
                                    $Button,
                                    Panel::PANEL_TYPE_SUCCESS)
                                , 4),
                        )
                    ), new Title(new PersonIcon().' Person')
                )
            );
        } else {
            $PersonPanel = new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                                , 6),
                            new LayoutColumn(
                                new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                    new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                                , 6)
                        )
                    ), new Title(new PersonIcon().' Person')
                )
            );
        }

        return $PersonPanel;
    }

    /**
     * @param null $Id
     * @param null $DebtorId
     * @param null $Debtor
     *
     * @return Stage|string
     */
    public function frontendChangeBanking($Id = null, $DebtorId = null, $Debtor = null)
    {

        $Stage = new Stage('Debitor', 'Bearbeiten');

        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }

        $tblDebtor = $DebtorId === null ? false : Banking::useService()->getDebtorById($DebtorId);
        if (!$tblDebtor) {
            $Stage->setContent(new Warning('Auf den Debitor konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(), array('Id' => $tblPerson->getId())));
//        $Stage->addButton(new Backward(true));

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['DebtorNumber'] = $tblDebtor->getDebtorNumber();
            $Global->savePost();
        }

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutPersonPanel($tblPerson)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Banking::useService()->changeDebtor(
                                    $Form, $tblDebtor, $Debtor
                                ))
                            , 6)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    public function formDebtor()
    {

        return new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new Panel('Debitor', array(new TextField('Debtor[DebtorNumber]', '', 'Debitor-Nummer')),
                            Panel::PANEL_TYPE_INFO)
                    )
                )
            )
        );
    }

    /**
     * @return Form
     */
    public function formReference()
    {

        return new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Mandatsreferenz-Nummer'))
                                , Panel::PANEL_TYPE_INFO)
                            , 6),
                        new FormColumn(
                            new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time()))
                                , Panel::PANEL_TYPE_INFO)
                            , 6)
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title(new Edit().' Mandatsreferenz')
                ),
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Informationen', array(
                                new TextField('Reference[Owner]', '', 'Kontoinhaber'),
                                new TextField('Reference[BankName]', '', 'Bankname')
                            ), Panel::PANEL_TYPE_INFO)
                            , 6),
                        new FormColumn(
                            new Panel('Zuordnung',
                                array(new TextField('Reference[IBAN]', '', 'IBAN'),
                                    new TextField('Reference[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                            , 6),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title(new PlusSign().' Konto eintragen')
                )
            )
        );
    }

    /**
     * @return Form
     */
    public function formAccount()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Account[Owner]', '', 'Kontoinhaber'),
                            new TextField('Account[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 5),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Account[IBAN]', '', 'IBAN'),
                                new TextField('Account[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 5)
                ))
            )
        );
    }

    /**
     * @param null $Id
     * @param      $BankReferenceId
     * @param null $Reference
     *
     * @return Stage|string
     */
    public function frontendChangeBankReference($Id = null, $BankReferenceId, $Reference = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Bearbeiten');

        $tblPerson = Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }

        $tblBankReference = $BankReferenceId === null ? false : Banking::useService()->getBankReferenceById($BankReferenceId);
        if (!$tblBankReference) {
            $Stage->setContent(new Warning('Mandatsreferenz nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblPerson->getId()));
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(),
            array('Id' => $tblPerson->getId())));
//        $Stage->addButton(new Backward());

        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(
                            new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    )
                ), new Title(new PersonIcon().' Person')
            )
        );

        $Global = $this->getGlobal();
        if ($Reference === null) {
            $Global->POST['Reference']['Reference'] = $tblBankReference->getReference();
            $Global->POST['Reference']['ReferenceDate'] = $tblBankReference->getReferenceDate();
            $Global->POST['Reference']['Owner'] = $tblBankReference->getOwner();
            $Global->POST['Reference']['BankName'] = $tblBankReference->getBankName();
            $Global->POST['Reference']['IBAN'] = $tblBankReference->getIBAN();
            $Global->POST['Reference']['BIC'] = $tblBankReference->getBIC();
            $Global->savePost();
        }

        $Form = new Form(array(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Mandatsreferenz-Nummer'))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6)
                )), new \SPHERE\Common\Frontend\Form\Repository\Title(new Edit().' Mandatsreferenz')
            ),
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Reference[Owner]', '', 'Kontoinhaber'),
                            new TextField('Reference[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Reference[IBAN]', '', 'IBAN'),
                                new TextField('Reference[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 6)
                )), new \SPHERE\Common\Frontend\Form\Repository\Title(new PlusSign().' Konto eintragen')
            )
        ));
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->changeReference(
                                $Form, $tblBankReference, $Reference)
                        ), 12)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $DebtorId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendRemoveDebtor($Id = null, $DebtorId = null, $Confirm = false)
    {
        $Stage = new Stage('Debtornummer', 'Entfernen');
        $tblPerson = Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Danger('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }
        $tblDebtor = $Id === null ? false : Banking::useService()->getDebtorById($DebtorId);
        if (!$tblDebtor) {
            $Stage->setContent(new Danger('Debitornummer nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblPerson->getId()));
        }

        $PersonPanel = '';
        if ($tblPerson) {

//            $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
//            if ($tblDebtorList) {
//                foreach ($tblDebtorList as $tblDebtorOne) {
//                    $DebtorContent[] = $tblDebtorOne->getDebtorNumber();
//                }
//            }

            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            $PersonPanel = new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                                , 6),
                            new LayoutColumn(
                                new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                    new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                                , 6)
//                        ,
//                            new LayoutColumn(( !empty( $DebtorContent ) ) ?
//                                new Panel('Mandatsreferenzen', $DebtorContent, Panel::PANEL_TYPE_SUCCESS)
//                                : null
//                                , 4),
                        )
                    ), new Title(new PersonIcon().' Person')
                )
            );
        }

        $Content = array();
        $Content[] = 'Debitornummer: '.$tblDebtor->getDebtorNumber();
        if (!$Confirm) {
            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Debtornummer wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/Banking/Debtor/Remove', new Ok(),
                            array('Id' => $Id, 'DebtorId' => $tblDebtor->getId(), 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/Banking/View', new Disable(),
                            array('Id' => $tblPerson->getId()))
                    )
                ))))
            );
        } else {

            // Destroy Debtor
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->removeDebtor($tblDebtor)
                            ? new Success('Debitornummer entfernt')
                            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()))
                            : new Danger('Debitornummer konnte nicht entfernt werden')
                            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()))
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $BankReferenceId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendRemoveBankReference($Id = null, $BankReferenceId = null, $Confirm = false)
    {

        $Stage = new Stage('Mandatsreferenz', 'Entfernen');

        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Danger('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }

        $tblBankReference = $BankReferenceId === null ? false : Banking::useService()->getBankReferenceById($BankReferenceId);
        if (!$tblBankReference) {
            $Stage->setContent(new Danger('Mandatsreferenz nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()));
        }

        $PersonPanel = '';
        if ($tblPerson) {

            $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
            if ($tblBankReferenceList) {
                foreach ($tblBankReferenceList as $tblBankReferenceOne) {
                    $ReferenceContent[] = $tblBankReferenceOne->getReference().new PullRight($tblBankReferenceOne->getReferenceDate());
                }
            }

            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            $PersonPanel = new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                                , 6),
                            new LayoutColumn(
                                new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                    new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                                , 6)
                        )
                    ), new Title(new PersonIcon().' Person')
                )
            );
        }

        $Content = array();
        $Content[] = 'Mandatsreferenz: '.$tblBankReference->getReference();
        $Content[] = 'Datum: '.$tblBankReference->getReferenceDate();
        if (!$Confirm) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(), array('Id' => $tblPerson->getId())));
//            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Mandatsreferenz wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/Banking/Reference/Remove', new Ok(),
                            array('Id' => $tblPerson->getId(), 'BankReferenceId' => $tblBankReference->getId(), 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/Banking/View', new Disable(), array('Id' => $tblPerson->getId()))
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->removeBankReference($tblBankReference)
                            ? new Success('Mandatsreferenz entfernt')
                            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()))
                            : new Danger('Mandatsreferenz konnte nicht entfernt werden')
                            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()))
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $PaymentTypeId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendPayment($Id = null, $PaymentTypeId = null, $Data = null)
    {
        $Stage = new Stage('Zahlungsoption', 'Festlegen');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
            $Stage->setContent(new Warning('Person nicht gefunden!'));
            return $Stage;
        }
        $tblPaymentType = $PaymentTypeId === null ? false : Balance::useService()->getPaymentTypeById($PaymentTypeId);
        if (!$tblPaymentType) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(),
                array('Id' => $tblPerson->getId())));
            $Stage->setContent(new Warning('Bezahlart nicht gefunden!'));
            return $Stage;
        }

        $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        $tblPersonList = array();
        if ($tblRelationshipList) {
            foreach ($tblRelationshipList as $tblRelationship) {
                if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                    $tblPersonList[] = $tblRelationship->getServiceTblPersonTo();
                }
                if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                    $tblPersonList[] = $tblRelationship->getServiceTblPersonFrom();
                }
            }
        }
        $tblPersonList[] = $tblPerson;
        $TableContent = array();
        if (!empty( $tblPersonList )) {
            /** @var TblPerson $Person */
            foreach ($tblPersonList as $Person) {
                $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($Person);
                if ($tblDebtorSelectionList) {
                    array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent) {
                        $Item['Name'] = '';
                        $Item['Item'] = '';
                        $Item['Payer'] = '';
                        $Item['Payment'] = '';
                        $Item['Debtor'] = '';
                        $Item['Reference'] = '';
                        if ($tblDebtorSelection->getServiceTblPerson()) {
                            $Item['Name'] = $tblDebtorSelection->getServiceTblPerson()->getFullName();
                        }
                        if ($tblDebtorSelection->getServiceTblInventoryItem()) {
                            $Item['Item'] = $tblDebtorSelection->getServiceTblInventoryItem()->getName();
                        }
                        if ($tblDebtorSelection->getServiceTblPersonPayers()) {
                            $Item['Payer'] = $tblDebtorSelection->getServiceTblPersonPayers()->getLastFirstName();
                        }
                        if ($tblDebtorSelection->getServiceTblPaymentType()) {
                            $Item['Payment'] = $tblDebtorSelection->getServiceTblPaymentType()->getName();
                        }
                        if ($tblDebtorSelection->getTblDebtor()) {
                            $Item['Debtor'] = $tblDebtorSelection->getTblDebtor()->getDebtorNumber();
                        }
                        if ($tblDebtorSelection->getTblBankReference()) {
                            $Item['Reference'] = $tblDebtorSelection->getTblBankReference()->getReference();
                        }
                        array_push($TableContent, $Item);
                    });
                }
            }
        }

        $Stage->setMessage('Einstellungsebene um für eine Person einen bestimmten Artikel zu bezahlen.</br>
            Hierbei werden auch die Debitor-Nr. und bei SEPA-Lastschrift die Mandatsreferenz benötigt.</br>
            '.new WarningText(new Bold('Vorhandene Zahlungseinstellungen werden überschrieben!')));
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(), array('Id' => $Id)));

        $form = $this->formFromPerson($tblPerson, $tblPaymentType);
        $form->appendFormButton(new Primary('Speichern', new Save()));
        $form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');


        $Stage->setContent(
//            $this->layoutPersonPanel($tblPerson).
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Title(new Pencil().' Bezahler: '.new Bold($tblPerson->getLastFirstName()).'&nbsp;&nbsp;&nbsp;&nbsp;
                                                     Bezahlart: '.new Bold($tblPaymentType->getName()))
                            .new Well(
                                Banking::useService()->createDebtorSelectionComplete(
                                    $form, $tblPerson, $tblPaymentType, $Data)
                            ))
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, new TitleTable('Übersicht', 'vorhandener Bezahleinstellungen'),
                                array('Name'      => 'Leistungsbezieher',
                                      'Item'      => 'Artikel',
                                      'Payer'     => 'Bezahler',
                                      'Payment'   => 'Bezahlart',
                                      'Debtor'    => 'Debitor-Nr.',
                                      'Reference' => 'Mandatsreferenz',
                                )
                            )
                        )
                    )
                ),
            ))
        );

        return $Stage;
    }


    /**
     * @param TblPerson      $tblPerson
     * @param TblPaymentType $tblPaymentType
     *
     * @return Form
     */
    private function formFromPerson(TblPerson $tblPerson, TblPaymentType $tblPaymentType)
    {

        $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        $tblPersonList = array();
        if ($tblRelationshipList) {
            foreach ($tblRelationshipList as $tblRelationship) {
                if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                    $tblPersonList[] = $tblRelationship->getServiceTblPersonTo();
                }
                if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                    $tblPersonList[] = $tblRelationship->getServiceTblPersonFrom();
                }
            }
        }

        $tblPersonList[] = $tblPerson;

        $tblItemAll = Item::useService()->getItemAll();
        $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);

        if ($tblPaymentType->getName() !== 'SEPA-Lastschrift') {
            $tblBankReferenceList = null;
        }

        if ($tblPersonList) {
            array_walk($tblPersonList, function (TblPerson &$tblPerson) {

//                // alle Hauptadressen
//                $tblAddressToPersonList = Address::useService()->getAddressAllByPersonAndType($tblPerson,
//                    Address::useService()->getTypeById(1));
//                if ($tblAddressToPersonList) {
//                    /** @var \SPHERE\Application\Contact\Address\Service\Entity\TblToPerson $tblAddressToPerson */
//                    $tblAddressToPerson = reset($tblAddressToPersonList);
//                } else {
//                    $tblAddressToPerson = false;
//                }

                $tblPerson = array(
                    'Person' => new RadioBox('Data[Person]', $tblPerson->getFullName(), $tblPerson->getId()),
//                    'Address' => $tblAddressToPerson ? $tblAddressToPerson->getTblAddress()->getGuiString() : ''
                );
            });
            $tblPersonList = array_filter($tblPersonList);
        }
        // Person Panel
        $PanelPerson = new Panel('für folgende Person '.new PersonIcon(),
            array(new TableData($tblPersonList, null,
                array('Person' => 'Person wählen',
//                      'Address' => 'Adresse'
                ))
            ), Panel::PANEL_TYPE_INFO);

        if ($tblItemAll) {
            array_walk($tblItemAll, function (TblItem &$tblItem) {
                $tblItem = array(
                    'Item' => new RadioBox('Data[Item]', $tblItem->getName(), $tblItem->getId()));
            });
            $tblItemAll = array_filter($tblItemAll);
        }
        // Person Panel
        $PanelItem = new Panel('für folgenden Artikel '.new CommodityItem(),
            array(new TableData($tblItemAll, null, array('Item' => 'Artiekl wählen')),
            ), Panel::PANEL_TYPE_INFO);

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        $PanelPerson
                    ), 4),
                    new FormColumn(array(
                        $PanelItem
                    ), 4),
                    new FormColumn(array(
                        new Panel('Debitor-Nr',
                            ( $tblDebtorList === false
                                ? new Danger('Fehlende Debitor-Nr! Bitte zuerst eintragen.')
                                : array(new SelectBox('Data[Debtor]', '',
                                    array('{{ DebtorNumber }}' => $tblDebtorList), new TileBig()))
                            )
                            , Panel::PANEL_TYPE_INFO
                        ),
                        new Panel('Mandatsreferenz',
                            ( $tblBankReferenceList === false
                                ? new Danger('Fehlende Mandatsreferenz! Bitte zuerst eintragen.')
                                : ( $tblBankReferenceList === null
                                    ? new Success('Keine Mandatsreferenz benötigt')
                                    : array(new SelectBox('Data[Reference]', '',
                                        array('{{ Reference }} - {{ Owner }}' => $tblBankReferenceList), new TileBig()))
                                )
                            )
                            , Panel::PANEL_TYPE_INFO
                        ),
                    ), 4),
                ))
            )
        );
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPaymentSelection($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new WarningText('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $Global = $this->getGlobal();

        $TableContent = array();

        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerificationList) {
            array_walk($tblBasketVerificationList, function (TblBasketVerification $tblBasketVerification) use (&$TableContent, &$Global, &$Data) {

                $tblPerson = $tblBasketVerification->getServiceTblPerson();
                $tblItem = $tblBasketVerification->getServiceTblItem();

                if (!Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem)) {
                    $Item['Person'] = $tblBasketVerification->getServiceTblPerson()->getFullName();
                    $Item['SiblingRank'] = '';
                    $Item['SchoolType'] = '';
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                            if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                                $Item['SiblingRank'] = $tblSiblingRank->getName();
                            }
                        }

                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if ($tblTransferType) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblType = $tblStudentTransfer->getServiceTblType();
                                if ($tblType) {
                                    $Item['SchoolType'] = $tblType->getName();
                                }
                            }
                        }
                    }

                    $PaymentPerson = array();
                    $tblRelationShipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationShipList) {
                        array_walk($tblRelationShipList, function (TblToPerson $tblRelationShip) use (&$PaymentPerson) {
                            /** filter Type of Relationship that is unable to pay */
                            if ($tblRelationShip->getTblType()->getName() !== 'Arzt' &&
                                $tblRelationShip->getTblType()->getName() !== 'Geschwisterkind'
                            ) {
                                $tblPerson = $tblRelationShip->getServiceTblPersonFrom();
                                if ($tblPerson) {
                                    $tblDebtor = Banking::useService()->getDebtorAllByPerson($tblPerson);
                                    if ($tblDebtor) {
                                        $PaymentPerson[] = $tblPerson;
                                    }
                                }
                            }
                        });
                    }
                    $tblDebtor = Banking::useService()->getDebtorAllByPerson($tblPerson);
                    if ($tblDebtor) {
                        $PaymentPerson[] = $tblPerson;
                    }

                    $Item['Item'] = $tblItem->getName();
                    $Item['Value'] = $tblBasketVerification->getSummaryPrice();
                    $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                    if (!empty( $PaymentPerson )) {
                        $Item['SelectPayers'] = new SelectBox('Data['.$tblBasketVerification->getId().'][PersonPayers]', '', array(
                            '{{ FullName }}' => $PaymentPerson));
                    } else {
                        $Item['SelectPayers'] = new WarningText('Bezahler anlegen!');
                    }

                    if (!isset( $Data )) {
                        $Global->POST['Data'][$tblBasketVerification->getId()]['Payment'] = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
                    }
                    $tblPaymentType = Balance::useService()->getPaymentTypeAll();
                    $Item['SelectPayType'] = new SelectBox('Data['.$tblBasketVerification->getId().'][Payment]', '', array(
                        '{{ Name }}'
                        => $tblPaymentType
                    ));
                    if ($Data !== null) {
                        $Data[$tblBasketVerification->getId()]['Person'] = $tblPerson->getId();
                        $Data[$tblBasketVerification->getId()]['Item'] = $tblBasketVerification->getServiceTblItem()->getId();
                    } else {
                        if (!empty( $PaymentPerson ) && count($PaymentPerson) == 1) {
                            /** @var TblPerson[] $PaymentPerson */
                            $Global->POST['Data'][$tblBasketVerification->getId()]['PersonPayers'] = $PaymentPerson[0]->getId();
                        }
                    }
                    array_push($TableContent, $Item);
                }

            });
        }

        $Global->savePost();

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'        => 'Person',
                            'SiblingRank'   => 'Geschwister',
                            'SchoolType'    => 'Schulart',
                            'Item'          => 'Artikel',
                            'Value'         => 'Gesamtpreis',
                            'ItemType'      => 'Typ',
                            'SelectPayers'  => 'Bezahler',
                            'SelectPayType' => 'Typ'
                        ), null) // array("bPaginate" => false)
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern und Weiter', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->createDebtorSelection(
                                    $Form, $tblBasket, $Data
                                ) : new Success('Artikelbezogene Bezahler sind bekannt.')
                                .new Warning('Drücken Sie '.
                                    new Standard('Weiter', '/Billing/Accounting/DebtorSelection/Payment/Choose', new Setup(), array('Id' => $tblBasket->getId()))
                                    .' um die Debitor-Nummer / Mandatsreferenz auzuwählen')
//                                .new Redirect('/Billing/Accounting/DebtorSelection/Payment/Choose', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId()))
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPaymentChoose($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new WarningText('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()
            , array('Id' => $tblBasket->getId())));
//        $Stage->addButton(new Backward(true));

        $TableContent = array();
        $tblDebtorSelectionList = array();
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerificationList) {
            foreach ($tblBasketVerificationList as $tblBasketVerification) {
                $tblPerson = $tblBasketVerification->getServiceTblPerson();
                $tblItem = $tblBasketVerification->getServiceTblItem();
                $tblDebtorSelectionList[] = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            }
        }

        $Global = $this->getGlobal();

        if (!empty( $tblDebtorSelectionList )) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                $tblPaymentTypeSelection = $tblDebtorSelection->getServiceTblPaymentType();
                if (Banking::useService()->checkDebtorSelectionDebtor($tblDebtorSelection)) {   //Prüfung auf vorhandene Zuweisungen
                    $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                    $tblPersonPayers = $tblDebtorSelection->getServiceTblPersonPayers();
                    $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();
                    $Item['Person'] = $tblPerson->getFullName();
                    $Item['PersonPayers'] = $tblPersonPayers->getFullName();
                    $Item['Item'] = $tblItem->getName();
                    $Item['Reference'] = new Warning('Der Debitor besitzt keine Mandatsreferenz<br/>(Fakturierung/Buchhaltung/Debitoren)');
                    $Item['Payment'] = $tblDebtorSelection->getServiceTblPaymentType()->getName();

                    $tblBankReferenceList = array();
                    if ($tblPaymentType->getId() == $tblPaymentTypeSelection->getId()) {
                        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                        if ($tblBankReferenceList) {
                            $Item['Reference'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Reference]', '',
                                array('{{ Reference }}' => $tblBankReferenceList)
                            );
                        }
                    } else {
                        $Item['Reference'] = new Info('Bezahlart benötigt keine Referenz');
                    }

                    $DebtorArray = array();
//                    if (Banking::useService()->getDebtorAllByPerson($tblPerson)) {
//                        $DebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
//                        $DebtorArray = array_merge($DebtorArray, $DebtorList);
//                    }
                    if (Banking::useService()->getDebtorAllByPerson($tblPersonPayers)) {
                        $DebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonPayers);
                        $DebtorArray = array_merge($DebtorArray, $DebtorList);
                    }

                    if (!empty( $DebtorArray )) {
                        $Item['Debtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                            array(
                                '{{ DebtorNumber }}' => $DebtorArray));
//                                    '{{ DebtorNumber }} - {{ ServiceTblPerson.FullName }}' => $DebtorArray));
                    } else {
                        $Item['Debtor'] = new DangerText('Debitor benötigt!');
                    }

                    if ($tblBankReferenceList && count($tblBankReferenceList) == 1) {
                        $Global->POST['Data'][$tblDebtorSelection->getId()]['Reference'] = $tblBankReferenceList[0]->getId();
                    }
                    if (!empty( $DebtorArray ) && count($DebtorArray) == 1) {
                        /** @var TblDebtor[] $DebtorArray */
                        $Global->POST['Data'][$tblDebtorSelection->getId()]['Debtor'] = $DebtorArray[0]->getId();
                    }

                    if ($Data !== null) {
                        $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                        $Data[$tblDebtorSelection->getId()]['Item'] = $tblItem->getId();
                    }
                    array_push($TableContent, $Item);
                }
//                }
            });
        }
        $Global->savePost();

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'       => 'Person',
                            'PersonPayers' => 'Bezahler',
                            'Item'         => 'Artikel',
                            'Payment'      => 'Bezahlart',
                            'Debtor'       => 'Debitor-Nummer',
                            'Reference'    => 'Mandatsreferenz-Nummer',
                        ), null) // array("bPaginate" => false))
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->updateDebtorSelection(
                                    $Form, $tblBasket, $Data)
                                : new Success('Debitoren der Bezahler sind bekannt.')
                                .new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId())) )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDebtorSelection()
    {

        $Stage = new Stage('Bezahler', 'Übersicht');
        $tblDebtorSelectionAll = Banking::useService()->getDebtorSelectionAll();
        $PersonIdList = array();
        $tblPersonList = array();
        $TableContent = array();
//        new Backward();

        if ($tblDebtorSelectionAll) {
            foreach ($tblDebtorSelectionAll as $tblDebtorSelection) {
                $PersonIdList[] = $tblDebtorSelection->getServiceTblPerson()->getId();
            }
            $PersonIdList = array_unique($PersonIdList);
        }
        if (!empty( $PersonIdList )) {
            foreach ($PersonIdList as $PersonId) {
                $tblPersonList[] = Person::useService()->getPersonById($PersonId);
            }
        }
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['Name'] = $tblPerson->getLastFirstName().' '.new Muted(new Small('('.$tblPerson->getSalutation().')'));
                $Item['ItemPayer'] = '';
                $Item['Status'] = 'test';
                $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);
                if (!empty( $tblDebtorSelectionList )) {
                    $ItemPayer = array();
                    $Status = array();
                    foreach ($tblDebtorSelectionList as $tblDebtorSelection) {

                        if (( $tblItem = $tblDebtorSelection->getServiceTblInventoryItem() )) {
                            $ItemPayer[] = $tblItem->getName()
                                .' - '.$tblDebtorSelection->getServiceTblPersonPayers()->getLastFirstName();
                        } else {
                            $ItemPayer[] = 'Fehlt'
                                .' - '.$tblDebtorSelection->getServiceTblPersonPayers()->getLastFirstName();
                        }

                        if ($tblDebtorSelection->getTblDebtor() === false || $tblDebtorSelection->getTblBankReference() === false) {
                            if ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'Bar') {
                                $Status[] = new SuccessText(new Check().' Bar');
                            } elseif ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'SEPA-Überweisung') {
                                $Status[] = new SuccessText(new Check().' SEPA-Überweisung');
                            } else {
                                $Status[] = new WarningText(new Unchecked().' Offen');
                            }
                        } else {
                            $Status[] = new SuccessText(new Check().' OK');
                        }

                    }
                    $Item['ItemPayer'] = new Listing($ItemPayer);
                    $Item['Status'] = new Listing($Status);
                }
                $Item['Option'] = new Standard('', '/Billing/Accounting/DebtorSelection/PaymentSelection', new Edit(),
                        array('Id' => $tblPerson->getId()), 'Bearbeiten')
                    .new Standard('', '/Billing/Accounting/DebtorSelection/Person/Destroy', new Remove(),
                        array('Id' => $tblPerson->getId()), 'Zuweisungen entfernen');

                array_push($TableContent, $Item);
            });
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new CogWheels().' Zahlungszuweisungen'),
                            ( empty( $TableContent ) ? new Warning('Keine Zuweisungen vorhanden. Zuweisungen werden automatisch erstellt sobald ein Warenkorb fakturiert wird.') :
                                new TableData($TableContent, null,
                                    array('Name'      => 'Name',
                                          'ItemPayer' => 'Item - Bezahler',
                                          'Status'    => 'Status',
                                          'Option'    => '',
                                    )) )
                        ))
                    )
                )
            )
        );
        return $Stage;

    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDebtorPaymentSelection($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new WarningText('Person nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/DebtorSelection', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $Global = $this->getGlobal();
        $TableContent = array();
        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);

        if ($tblDebtorSelectionList) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();

                $Item['Person'] = $tblDebtorSelection->getServiceTblPerson()->getFullName();
                $Item['SiblingRank'] = '';
                $Item['SchoolType'] = '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                        if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                            $Item['SiblingRank'] = $tblSiblingRank->getName();
                        }
                    }

                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblType = $tblStudentTransfer->getServiceTblType();
                            if ($tblType) {
                                $Item['SchoolType'] = $tblType->getName();
                            }
                        }
                    }
                }

                $PaymentPerson = array();
                $tblRelationShipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblRelationShipList) {
                    array_walk($tblRelationShipList, function (TblToPerson $tblRelationShip) use (&$PaymentPerson) {

                        $tblPerson = $tblRelationShip->getServiceTblPersonFrom();
                        if ($tblPerson) {
                            /** filter Type of Relationship that is unable to pay */
                            if ($tblRelationShip->getTblType()->getName() !== 'Arzt' &&
                                $tblRelationShip->getTblType()->getName() !== 'Geschwisterkind'
                            ) {
                                $tblDebtor = Banking::useService()->getDebtorAllByPerson($tblPerson);
                                if ($tblDebtor) {
                                    $PaymentPerson[] = $tblPerson;
                                }
                            }
                        }
                    });
                }
                $tblDebtor = Banking::useService()->getDebtorAllByPerson($tblPerson);
                if ($tblDebtor) {
                    $PaymentPerson[] = $tblPerson;
                }

                $Item['Item'] = $tblItem->getName();
                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                if (!empty( $PaymentPerson )) {
                    $Item['SelectPayers'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][PersonPayers]', '', array(
                        '{{ FullName }}'
                        => $PaymentPerson
                    ));
                } else {
                    $Item['SelectPayers'] = new WarningText('Bezahler anlegen!');
                }

                if (!isset( $Data )) {
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Payment'] = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
                }
                $tblPaymentType = Balance::useService()->getPaymentTypeAll();
                $Item['SelectPayType'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Payment]', '', array(
                    '{{ Name }}'
                    => $tblPaymentType
                ));
                if ($Data !== null) {
                    $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                    $Data[$tblDebtorSelection->getId()]['Item'] = $tblDebtorSelection->getServiceTblInventoryItem()->getId();
                }

                $Item['Option'] = new Standard('', '/Billing/Accounting/DebtorSelection/Destroy', new Remove(),
                    array('Id'          => $tblPerson->getId(),
                          'SelectionId' => $tblDebtorSelection->getId())
                    , 'Zuweisung entfernen');

                $Global->POST['Data'][$tblDebtorSelection->getId()]['PersonPayers'] = $tblDebtorSelection->getServiceTblPersonPayers()->getId();
                $Global->POST['Data'][$tblDebtorSelection->getId()]['Payment'] = $tblDebtorSelection->getServiceTblPaymentType()->getId();

                array_push($TableContent, $Item);
            });
        }

        if (null === $Data) {
            $Global->savePost();
        }

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(array(
                        new TableData(
                            $TableContent, new TitleTable('Bezahler / Bezahlart'), array(
                            'Person'        => 'Person',
                            'SiblingRank'   => 'Geschwister',
                            'SchoolType'    => 'Schulart',
                            'Item'          => 'Artikel',
                            'ItemType'      => 'Typ',
                            'SelectPayers'  => 'Bezahler',
                            'SelectPayType' => 'Typ',
                            'Option'        => ''
                        ), null) // array("bPaginate" => false))
                    ))
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern und Weiter', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Banking::useService()->changeDebtorSelectionPayer(
                                $Form, $tblPerson, $Data
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDebtorPaymentChoose($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new WarningText('Person nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/DebtorSelection/PaymentSelection', new ChevronLeft()
            , array('Id' => $tblPerson->getId())));
//        $Stage->addButton(new Backward());

        $Global = $this->getGlobal();

        $TableContent = array();
        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);

        if (!empty( $tblDebtorSelectionList )) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                $tblPaymentTypeSelection = $tblDebtorSelection->getServiceTblPaymentType();

                $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                $tblPersonPayers = $tblDebtorSelection->getServiceTblPersonPayers();
                $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();
                $Item['Person'] = $tblPerson->getFullName();
                $Item['PersonPayers'] = $tblPersonPayers->getFullName();
                $Item['Item'] = $tblItem->getName();
                $Item['Reference'] = new Warning('Der Debitor besitzt keine Mandatsreferenz<br/>(Fakturierung/Buchhaltung/Debitoren)');
                $Item['Payment'] = $tblPaymentTypeSelection->getName();

                $tblBankReferenceList = array();
                if ($tblPaymentType->getId() == $tblPaymentTypeSelection->getId()) {
                    $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                    if ($tblBankReferenceList) {
                        $Item['Reference'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Reference]', '',
                            array('{{ Reference }} - Besitzer: {{ Owner }}' => $tblBankReferenceList)
                        );
                    }
                } else {
                    $Item['Reference'] = new Info('Bezahlart benötigt keine Referenz');
                }

                $DebtorArray = array();
//                if (Banking::useService()->getDebtorAllByPerson($tblPerson)) {
//                    $DebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
//                    $DebtorArray = array_merge($DebtorArray, $DebtorList);
//                }
                if (Banking::useService()->getDebtorAllByPerson($tblPersonPayers)) {
                    $DebtorList = Banking::useService()->getDebtorAllByPerson($tblPersonPayers);
                    $DebtorArray = array_merge($DebtorArray, $DebtorList);
                }

                if (!empty( $DebtorArray )) {
                    $Item['SelectDebtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                        array('{{ DebtorNumber }}' => $DebtorArray)
                    );
                } else {
                    $Item['SelectDebtor'] = new DangerText('Debitor benötigt!');
                }

                if (( $tblRef = $tblDebtorSelection->getTblBankReference() )) {
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Reference'] = $tblRef->getId();
                } elseif ($tblBankReferenceList && count($tblBankReferenceList) == 1 &&
                    $tblPaymentType->getId() == $tblPaymentTypeSelection->getId()
                ) {
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Reference'] = $tblBankReferenceList[0]->getId();
                }
                if (( $tblDeb = $tblDebtorSelection->getTblDebtor() )) {
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Debtor'] = $tblDeb->getId();
                } elseif (!empty( $DebtorArray ) && count($DebtorArray) == 1) {
                    /** @var TblDebtor[] $DebtorArray */
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Debtor'] = $DebtorArray[0]->getId();
                }

                if ($Data !== null) {
                    $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                    $Data[$tblDebtorSelection->getId()]['Item'] = $tblItem->getId();
                }
                array_push($TableContent, $Item);
            });
        }

        $Global->savePost();

        $Form = new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, new TitleTable('Debitor-Nummer / Referenz-Nummer'), array(
                            'Person'       => 'Person',
                            'PersonPayers' => 'Bezahler',
                            'Item'         => 'Artikel',
                            'Payment'      => 'Bezahlart',
                            'SelectDebtor' => 'Debitor-Nummer',
                            'Reference'    => 'Mandatsreferenz-Nummer',
                        ), null) // array("bPaginate" => false))
                    )
                ),
            ))
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->changeDebtorSelectionInfo(
                                    $Form, $Data)
                                : new Success('Debitoren der Bezahler sind bekannt.')
                                .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_SUCCESS)
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $SelectionId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyDebtorSelection($Id = null, $SelectionId = null, $Confirm = false)
    {

        $Stage = new Stage('Zahlungseinstellungen', 'Entfernen');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($SelectionId);
        if (!$tblDebtorSelection) {
            $Stage->setContent(new Warning('Zuweisung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Information', 'Automatisierung für '.$tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                    )
                )
            )
        );

        $Content = array();
        if ($tblDebtorSelection->getServiceTblInventoryItem()) {
            $Content[] = 'Artikel: '.$tblDebtorSelection->getServiceTblInventoryItem()->getName();
        }
        if ($tblDebtorSelection->getServiceTblPersonPayers()) {
            $Content[] = 'Bezahler: '.$tblDebtorSelection->getServiceTblPersonPayers()->getFullName();
        }
        if (!$Confirm) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/DebtorSelection/PaymentSelection', new ChevronLeft(),
                array('Id' => $tblPerson->getId())));
//            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Zuweisung wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/DebtorSelection/Destroy', new Ok(),
                            array('Id'          => $tblPerson->getId(),
                                  'SelectionId' => $tblDebtorSelection->getId(),
                                  'Confirm'     => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/DebtorSelection/PaymentSelection', new Disable(),
                            array('Id' => $tblPerson->getId()))
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->destroyDebtorSelection($tblDebtorSelection)
                            ? new Success('Zuweisung entfernt')
                            .new Redirect('/Billing/Accounting/DebtorSelection/PaymentSelection', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblPerson->getId()))
                            : new Danger('Zuweisung konnte nicht entfernt werden')
                            .new Redirect('/Billing/Accounting/DebtorSelection/PaymentSelection', Redirect::TIMEOUT_ERROR,
                                array('Id' => $tblPerson->getId()))
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyDebtorSelectionByPerson($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Zahlungseinstellungen', 'Entfernen');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Information', 'Automatisierung für '.$tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                    )
                )
            )
        );

        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);
        $Content = array();
        if ($tblDebtorSelectionList) {
            foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                if ($tblDebtorSelection->getServiceTblPersonPayers() && $tblDebtorSelection->getServiceTblInventoryItem()) {
                    $Content[] = $tblDebtorSelection->getServiceTblPersonPayers()->getFullName().' - '
                        .$tblDebtorSelection->getServiceTblInventoryItem()->getName();
                } elseif ($tblDebtorSelection->getServiceTblPersonPayers()) {
                    $Content[] = $tblDebtorSelection->getServiceTblPersonPayers();
                } elseif ($tblDebtorSelection->getServiceTblInventoryItem()) {
                    $Content[] = $tblDebtorSelection->getServiceTblInventoryItem()->getName();
                }
            }
        }
        if (!$Confirm) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/DebtorSelection', new ChevronLeft()));
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Zuweisung wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/DebtorSelection/Person/Destroy', new Ok(),
                            array('Id'      => $tblPerson->getId(),
                                  'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/DebtorSelection', new Disable())
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->destroyDebtorSelectionByPerson($tblPerson)
                            ? new Success('Zuweisungen entfernt')
                            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_SUCCESS)
                            : new Danger('Es konnten nicht alle Zuweisung entfernt werden')
                            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR)
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

}
