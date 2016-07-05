<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Basket
     *
     * @return Stage
     */
    public function frontendBasketList($Basket = null)
    {

        $Stage = new Stage('Warenkorb', 'Erstellung');
        $Stage->setMessage('Zeigt alle vorhandenen Warenkörbe an');
//        new Backward();
        $tblBasketAll = Basket::useService()->getBasketAll();

        $TableContent = array();
        if (!empty( $tblBasketAll )) {
            array_walk($tblBasketAll, function (TblBasket &$tblBasket) use (&$TableContent) {

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName();
                $Item['CreateDate'] = $tblBasket->getCreateDate();

                $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);

                $Item['Option'] =
                    (new Standard('', '/Billing/Bookkeeping/Basket/Change',
                        new Edit(), array(
                            'Id' => $tblBasket->getId()
                        ), 'Name bearbeiten'))->__toString().
                    ( !$tblBasketVerification ?
                        (new Standard('', '/Billing/Bookkeeping/Basket/Content',
                            new Listing(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Warenkorb füllen'))->__toString() :

                        (new Standard('', '/Billing/Bookkeeping/Basket/Verification',
                            new Equalizer(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Berechnung bearbeiten'))->__toString() ).
                    ( !$tblBasketVerification ?
                        (new Standard('', '/Billing/Bookkeeping/Basket/Destroy',
                            new Remove(), array(
                                'Id' => $tblBasket->getId()
                            ), 'Löschen'))->__toString() : null );
                array_push($TableContent, $Item);
            });
        }
        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Warenkorb',
                            new TextField('Basket[Name]', 'Name', 'Name', new Conversation()),
                            Panel::PANEL_TYPE_INFO), 6),
                    new FormColumn(
                        new Panel('Warenkorb',
                            new TextArea('Basket[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO), 6),
                )),
            ))
        ));
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Number'     => 'Nummer',
                                    'Name'       => 'Name',
                                    'CreateDate' => 'Erstellt am',
                                    'Option'     => ''
                                )
                            )
                        )
                    ), new Title(new ListingTable().' Übersicht')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Basket::useService()->createBasket($Form, $Basket)
                        ), 12)
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Basket
     *
     * @return Stage
     */
    public function frontendChangeBasket($Id = null, $Basket = null)
    {

        $Stage = new Stage('Warenkorb', 'Bearbeiten');
        $Stage->setMessage('Der Name des Warenkorbs ist Teil des Buchungstextes');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket',
//            new ChevronLeft()
//        ));
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
//        $Stage->addButton(new Backward());

        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Der Warenkorb konnte nicht abgerufen werden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Basket'] )) {
            $Global->POST['Basket']['Name'] = $tblBasket->getName();
            $Global->POST['Basket']['Description'] = $tblBasket->getDescription();
            $Global->savePost();
        }

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Warenkorb', new TextField('Basket[Name]', 'Name', 'Name', new Conversation()),
                            Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges', new TextArea('Basket[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            ))
        ));
        $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);

        $Content = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                $Content[] = $tblBasketItem->getServiceTblItem()->getName();
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Warenkorb', array(
                                'Nummer: '.$tblBasket->getId(),
                                'Name: '.$tblBasket->getName(),
                                'Beschreibung: '.$tblBasket->getDescription(),
                                'Datum: '.$tblBasket->getCreateDate()
                            ), Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(
                            new Panel('Artikel', $Content,
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6)
                    ))
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Basket::useService()->changeBasket($Form, $tblBasket, $Basket)
                        ), 12)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null       $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyBasket($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Warenkorb', 'Löschen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch bei aktiver Berechnung
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Item = array();
            $tblBasketItemAll = Basket::useService()->getBasketItemAllByBasket($tblBasket);
            if ($tblBasketItemAll) {
                foreach ($tblBasketItemAll as $tblBasketItem) {
                    $Item[] = new Muted(new Small($tblBasketItem->getServiceTblItem()->getName()));
//                            .'<br/>
//                            Preis: '.$tblItem->getServiceBillingCommodityItem()->getTblItem()->getPriceString();
                }
            }
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
//            $Stage->addButton(new Backward());
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Diesen Warenkorb mit dem Namen "<b>'.$tblBasket->getName().'</b>" wirklich löschen?',
                        array(
                            $tblBasket->getName().' erstellt am: '.$tblBasket->getCreateDate(),
                            new Panel('Artikel', $Item),
                        ),
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Bookkeeping/Basket/Destroy', new Ok(),
                            array('Id' => $Id, 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Bookkeeping/Basket', new Disable()
                        )
                    )
                    , 6))))
            );
        } else {

            // Destroy Basket
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Basket::useService()->destroyBasket($tblBasket)
                            ? new Success('Der Warenkorb wurde gelöscht')
                            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_SUCCESS)
                            : new Danger('Der Warenkorb konnte nicht gelöscht werden')
                            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR)
                        )
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return Layout
     */
    public function layoutBasket(TblBasket $tblBasket)
    {

        $ItemCount = 0;
        $PersonCount = 0;
        if (Basket::useService()->getBasketItemAllByBasket($tblBasket)) {
            $ItemCount = count(Basket::useService()->getBasketItemAllByBasket($tblBasket));
        }
        if (Basket::useService()->getPersonAllByBasket($tblBasket)) {
            $PersonCount = count(Basket::useService()->getPersonAllByBasket($tblBasket));
        }
        $ItemUsed = 0;
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $ItemUsed = count($tblBasketVerification);
        }

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(new Title('Warenkorb Informationen')),
                    new LayoutColumn(
                        new Panel('Warenkorb', array(
                                'Nummer: '.$tblBasket->getId()
                            , 'Name: '.$tblBasket->getName()
                            , 'Beschreibung: '.$tblBasket->getDescription())
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                    new LayoutColumn(
                        new Panel('Information', array(
                                'Artikel: '.$ItemCount
                            , 'Personen: '.$PersonCount
                            , 'Zuweisungen: '.$ItemUsed)
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),

                ))
            )
        );
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendBasketContent($Id = null)
    {

        $Stage = new Stage('Warenkorb', 'Zusammenstellung');
        $Stage->setMessage('Enthaltene Artikel und Personen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
//        $Stage->addButton(new Backward());

        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketItem = Basket::useService()->getBasketItemAllByBasket($tblBasket);
        $tblBasketPerson = Basket::useService()->getBasketPersonAllByBasket($tblBasket);

        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
//        $Stage->addButton(new Standard('Artikel hinzufügen/entfernen', '/Billing/Bookkeeping/Basket/Item/Select', null,
//            array('Id' => $tblBasket->getId())));
//        $Stage->addButton(new Standard('Personen hinzufügen/entfernen', '/Billing/Bookkeeping/Basket/Person/Select', null,
//            array('Id' => $tblBasket->getId())));

        $TableItemContent = array();
        if ($tblBasketItem) {
            array_walk($tblBasketItem, function (TblBasketItem $tblBasketItem) use (&$TableItemContent) {

                $tblItem = $tblBasketItem->getServiceTblItem();
                $Item['Name'] = '';
                $Item['Description'] = '';
                $Item['Calculation'] = '';
                if ($tblItem) {
                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDescription();
                    $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                    if (is_array($tblCalculationList)) {
                        $Item['Calculation'] = count($tblCalculationList) - 1;
                    }
                }
//                $Item['Option'] = new Standard('', '/Billing/Bookkeeping/Basket/Item/Remove', new Disable(), array('Id' => $tblBasketItem->getId()));
                array_push($TableItemContent, $Item);
            });
        }

        $TablePersonContent = array();
        if ($tblBasketPerson) {
            array_walk($tblBasketPerson, function (TblBasketPerson $tblBasketPerson) use (&$TablePersonContent) {

                $tblPerson = $tblBasketPerson->getServiceTblPerson();
                $Item['Name'] = '';
                $Item['Address'] = new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                if ($tblPerson) {
                    $Item['Name'] = $tblPerson->getFullName();
                    if (Address::useService()->getAddressByPerson($tblPerson)) {
                        $Item['Address'] = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
                    }
                }
//                $Item['Option'] = new Standard('', '/Billing/Bookkeeping/Basket/Person/Remove', new Disable(), array('Id' => $tblBasketPerson->getId()));
                array_push($TablePersonContent, $Item);
            });
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new Title('Artikel'),
                                (new Standard('Artikel bearbeiten', '/Billing/Bookkeeping/Basket/Item/Select', null,
                                    array('Id' => $tblBasket->getId()))),
                                ( empty( $TableItemContent ) ? new Warning('Keine Artikel im Warenkorb') :
                                    new TableData($TableItemContent, null,
                                        array('Name'        => 'Artikel',
                                              'Description' => 'Beschreibung',
                                              'Calculation' => 'Anzahl Bedingungen',
//                                      'Option'      => '',
                                        ), array("bPaginate" => false)) ))
                            , 6),
                        new LayoutColumn(array(
                                new Title('Personen'),
                                new Standard('Personen bearbeiten', '/Billing/Bookkeeping/Basket/Person/Select', null,
                                    array('Id' => $tblBasket->getId())),
                                ( empty( $TablePersonContent ) ? new Warning('Keine Personen im Warenkorb') :
                                    new TableData($TablePersonContent, null,
                                        array('Name'    => 'Name',
                                              'Address' => 'Adresse',
//                                      'Option'  => '',
                                        ), array("bPaginate" => false)) ))
                            , 6)
                    ))
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Info('Sind alle Artikel und Personen im Warenkorb angelegt, kann mit der Zuweisung der Kosten begonnen werden.
                            Danach kann der Preis und die Anzahl, der durch die Bedingungen zugewiesenen Artikel, für jede Person beliebig angepasst werden.<br/>'.
                                new Standard('Warenkorb berechnen '.new ChevronRight(), '/Billing/Bookkeeping/Basket/Calculation', null,
                                    array('Id' => $tblBasket->getId())))
                            , 6)
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendItemSelect($Id = null)
    {

        $Stage = new Stage('Artikel', 'Auswahl');
        $Stage->setMessage('Einzelne Artikel oder ganze Artikelgruppen auswählen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Content', new ChevronLeft(), array('Id' => $Id)));
        }
        //        $Stage->addButton(new Backward());
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $tblBasketItem = Basket::useService()->getBasketItemAllByBasket($tblBasket);

        $tblItemUsed = array();
        $TableItemContent = array();
        if ($tblBasketItem) {
            foreach ($tblBasketItem as $singleItem) {
                $tblItemUsed[] = $singleItem->getServiceTblItem();
            }

            array_walk($tblBasketItem, function (TblBasketItem $tblBasketItem) use (&$TableItemContent) {

                $tblItem = $tblBasketItem->getServiceTblItem();
                $Item['Name'] = '';
                $Item['Description'] = '';
                $Item['Type'] = $tblItem->getTblItemType()->getName();
                $Item['Calculation'] = '';
                if ($tblItem) {
                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDescription();
                    $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                    if (is_array($tblCalculationList)) {
                        $Item['Calculation'] = count($tblCalculationList) - 1;
                    }
                }
                $Item['Option'] = new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen', '/Billing/Bookkeeping/Basket/Item/Remove', new Minus(),
                    array('Id' => $tblBasketItem->getId()), 'Entfernt diesen Artikel aus dem Warenkorb');
                array_push($TableItemContent, $Item);
            });
        }

        $tblItemAll = Item::useService()->getItemAll();
        /*Warenkorb abziehen*/
        if (!empty( $tblItemUsed )) {
            $tblItemAll = array_udiff($tblItemAll, $tblItemUsed,
                function (TblItem $ObjectA, TblItem $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        $TableItemAddContent = array();
        if ($tblItemAll) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableItemAddContent, $tblBasket) {

                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDescription();
                $Item['Type'] = $tblItem->getTblItemType()->getName();
                $Item['Option'] = new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen', '/Billing/Bookkeeping/Basket/Item/Add', new Plus(),
                    array('Id'     => $tblBasket->getId(),
                          'ItemId' => $tblItem->getId()), 'Fügt diesen Artikel dem Warenkorb hinzu');

                array_push($TableItemAddContent, $Item);
            });
        }

        $tblCommodityUnused = Basket::useService()->getUnusedCommodityByBasket($tblBasket);

        $TableCommodityAddContent = array();
        if ($tblCommodityUnused) {
            array_walk($tblCommodityUnused, function (TblCommodity $tblCommodity) use (&$TableCommodityAddContent, $tblBasket) {

                $Item['Name'] = $tblCommodity->getName();
                $Item['Description'] = $tblCommodity->getDescription();
                $Item['Item'] = '';
                $tblItemList = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                $ItemArray = array();
                if ($tblItemList) {
                    foreach ($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName().' - '.$tblItem->getTblItemType()->getName();
                    }
                    $Item['Item'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($ItemArray);
                }

                $Item['Option'] = new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen', '/Billing/Bookkeeping/Basket/Commodity/Add', new Plus(),
                    array('Id'          => $tblBasket->getId(),
                          'CommodityId' => $tblCommodity->getId()), 'Fügt diese Artikel dem Warenkorb hinzu');

                array_push($TableCommodityAddContent, $Item);
            });
        }


        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new Listing().' im Warenkorb'),
                            ( empty( $TableItemContent ) ? new Warning('Keine Artikel im Warenkorb') :
                                new TableData($TableItemContent, null, array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'Type'        => 'Typ',
                                    'Calculation' => 'Anzahl Bedingungen',
                                    'Option'      => '',
                                ), array("bPaginate" => false)) )
                        ), 6),
                        new LayoutColumn(array(
                                new Title(new PlusSign().' Hinzufügen von Artikeln'),
                                ( empty( $TableItemAddContent ) ? new Warning('Keine Artikel die dem Warenkorb noch hinzugefügt werden können') :
                                    new TableData($TableItemAddContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Artikel')
                                        , array('Name'        => 'Artikel',
                                                'Description' => 'Beschreibung',
                                                'Type'        => 'Typ',
                                                'Option'      => '',
                                        )) ),
                                new Title(new PlusSign().' Hinzufügen von Leistungen'),
                                ( empty( $TableCommodityAddContent ) ? new Warning('Es sind keine Leistungen vorhanden die dem Warenkorb noch hinzugefügt werden können') :
                                    new TableData($TableCommodityAddContent, new \SPHERE\Common\Frontend\Table\Repository\Title('Artikelgruppen')
                                        , array('Name'        => 'Artikel',
                                                'Description' => 'Beschreibung',
                                                'Item'        => 'Artikel - Typ',
                                                'Option'      => '',
                                        )) )
                            )
                            , 6),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $CommodityId
     *
     * @return Stage
     */
    public function frontendAddBasketCommodity($Id = null, $CommodityId = null)
    {

        $Stage = new Stage('Warenkorb', 'Artikelgruppe hinzufügen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblCommodity = $CommodityId === null ? false : Commodity::useService()->getCommodityById($CommodityId);
        if (!$tblCommodity) {
            $Stage->setContent(new Warning('Leistung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Item/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->addCommodityToBasket($tblBasket, $tblCommodity));
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $ItemId
     *
     * @return Stage
     */
    public function frontendAddBasketItem($Id = null, $ItemId = null)
    {

        $Stage = new Stage('Warenkorb', 'Artikel Hinzufügen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblItem = $ItemId === null ? false : Item::useService()->getItemById($ItemId);
        if (!$tblItem) {
            $Stage->setContent(new Warning('Artikel nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Item/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->addItemToBasket($tblBasket, $tblItem));
        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendRemoveBasketItem($Id = null)
    {

        $Stage = new Stage('Warenkorb', 'Artikel Entfernen');
        $tblBasketItem = $Id === null ? false : Basket::useService()->getBasketItemById($Id);
        if (!$tblBasketItem) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        if (!$tblBasketItem->getTblBasket()) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasket = $tblBasketItem->getTblBasket();
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $Stage->setContent(Basket::useService()->removeBasketItem($tblBasketItem));
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $DataAddPerson
     * @param null $DataRemovePerson
     * @param null $Filter
     * @param null $FilterGroupId
     * @param null $FilterDivisionId
     *
     * @return Stage|string
     */
    public function frontendBasketPersonSelect(
        $Id = null,
        $DataAddPerson = null,
        $DataRemovePerson = null,
        $Filter = null,
        $FilterGroupId = null,
        $FilterDivisionId = null
    ) {

        $Stage = new Stage('Warenkorb', 'Personen Auswählen');
        $Stage->setMessage('Bitte wählen Sie Personen zur Fakturierung aus');

        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Content', new ChevronLeft(), array('Id' => $Id)));
        }
//        $Stage->addButton(new Backward());

        $tblFilterGroup = Group::useService()->getGroupById($FilterGroupId);
        $tblFilterDivision = Division::useService()->getDivisionById($FilterDivisionId);

        // Set Filter Post
        if ($Filter == null && ( $tblFilterGroup || $tblFilterDivision )) {
            $GLOBAL = $this->getGlobal();
            $GLOBAL->POST['Filter']['Group'] = $tblFilterGroup ? $tblFilterGroup->getId() : 0;
            $GLOBAL->POST['Filter']['Division'] = $tblFilterDivision ? $tblFilterDivision->getId() : 0;

            $GLOBAL->savePost();
        }

        $tblPersonList = Basket::useService()->getPersonAllByBasket($tblBasket);
        $tblPersonAll = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('COMMON'));

        // filter
        if ($tblFilterGroup || $tblFilterDivision) {
            //ToDO use Filter from Groups should I copy it to Basket Service?
            $tblPersonAll = Group::useService()->filterPersonListByGroupAndDivision(
                $tblPersonAll,
                $tblFilterGroup ? $tblFilterGroup : null,
                $tblFilterDivision ? $tblFilterDivision : null
            );
        }

        if ($tblPersonList && $tblPersonAll) {
            $tblPersonAll = array_udiff($tblPersonAll, $tblPersonList,
                function (TblPerson $tblPersonA, TblPerson $tblPersonB) {

                    return $tblPersonA->getId() - $tblPersonB->getId();
                }
            );
        }

        if ($tblPersonList) {
            $tempList = array();
            foreach ($tblPersonList as $personListPerson) {
                $tempList[] = $this->setPersonData($personListPerson, 'DataRemovePerson');
            }
            $tblPersonList = $tempList;
        }

        if (is_array($tblPersonAll)) {
            $tempList = array();
            foreach ($tblPersonAll as $personAllPerson) {
                $tempList[] = $this->setPersonData($personAllPerson, 'DataAddPerson');
            }
            $tblPersonAll = $tempList;
        }

        if (!$tblFilterGroup && !$tblFilterDivision) {
            $displayAvailablePersons = new Warning(
                'Zum Hinzufügen von Personen zum Warenkorb: '.$tblBasket->getName().' schränken Sie bitte den Personenkreis über die Suche (Gruppe und/oder Klasse) ein.',
                new Exclamation()
            );
        } elseif ($tblPersonAll) {

            $displayAvailablePersons = new TableData(
                $tblPersonAll,
                new \SPHERE\Common\Frontend\Table\Repository\Title('Weitere Personen', 'hinzufügen'),
                array(
                    'Check'       => new Center(new Small('Hinzufügen ').new Enable()),
                    'DisplayName' => 'Name',
                    'Address'     => 'Adresse',
                    'Groups'      => 'Gruppen/Klasse '
                ),
                array(
                    "columnDefs"     => array(
                        array(
                            "orderable" => false,
                            "width"     => "35px",
                            "targets"   => 0
                        ),
                        array(
                            "width"   => "20%",
                            "targets" => 1
                        ),
                        array(
                            "width"   => "40%",
                            "targets" => 2
                        )
                    ),
                    'order'          => array(
                        array('1', 'asc')
                    ),
                    "paging"         => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching"      => false, // Deaktivieren Suchen
                    "info"           => false  // Deaktivieren Such-Info
                )
            );
        } else {
            $displayAvailablePersons = new Warning('Keine weiteren Personen verfügbar.', new Exclamation());
        }

        $form = new Form(array(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        $displayAvailablePersons
                    ), 6),
                    new FormColumn(array(
                        ( $tblPersonList
                            ? new TableData(
                                $tblPersonList,
                                new \SPHERE\Common\Frontend\Table\Repository\Title('Personen aus dem Warenkorb "'.$tblBasket->getName().'"',
                                    'entfernen'),
                                array(
                                    'Check'       => new Center(new Small('Entfernen ').new Disable()),
                                    'DisplayName' => 'Name',
                                    'Address'     => 'Adresse',
                                    'Groups'      => 'Gruppen/Klasse'
                                ),
                                array(
                                    "columnDefs"     => array(
                                        array(
                                            "orderable" => false,
                                            "width"     => "35px",
                                            "targets"   => 0
                                        ),
                                        array(
                                            "width"   => "20%",
                                            "targets" => 1
                                        ),
                                        array(
                                            "width"   => "40%",
                                            "targets" => 2
                                        )
                                    ),
                                    'order'          => array(
                                        array('1', 'asc')
                                    ),
                                    "paging"         => false, // Deaktivieren Blättern
                                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    "searching"      => false, // Deaktivieren Suchen
                                    "info"           => false  // Deaktivieren Such-Info
                                )
                            )
                            : new Warning('Keine Personen zugewiesen.', new Exclamation())
                        )
                    ), 6),
                ))
            ),
        ));

        $form->appendFormButton(new Primary('Speichern', new Save()));
        $form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');

        $Stage->setContent(new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Warenkorb',
                            $tblBasket->getName().' '.new Small(new Muted($tblBasket->getDescription())),
                            Panel::PANEL_TYPE_INFO
                        ), 12
                    ),
                ))
            )),
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Well(
                            Basket::useService()->getPersonFilter(
                                $this->formPersonFilter(), $tblBasket, $Filter
                            )
                        ), 12
                    )
                ))
            ), new Title('Personensuche')),
            ( $Filter == null ?
                new LayoutGroup(array(
                    // TODO: Describe possible Action
//                        new LayoutRow(array(
//                            new LayoutColumn(
//                                new Info('Links können neue Personsn... rechts ...')
//                            )
//                        )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(
                                Basket::useService()->changePersonsToBasket(
                                    $form,
                                    $tblBasket,
                                    $DataAddPerson,
                                    $DataRemovePerson,
                                    $tblFilterGroup ? $tblFilterGroup : null,
                                    $tblFilterDivision ? $tblFilterDivision : null
                                )
                            )
                        ))
                    ))
                ), new Title('Personen', 'für diesen Warenkorb')) : null )
        )));
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendAddBasketPerson($Id = null, $PersonId = null)
    {

        $Stage = new Stage('Warenkorb', 'Person Hinzufügen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = $PersonId === null ? false : Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Person/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $Stage->setContent(Basket::useService()->addBasketPerson($tblBasket, $tblPerson));
        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendRemoveBasketPerson($Id = null)
    {

        $Stage = new Stage('Warenkorb', 'Person Entfernen');
        $tblBasketPerson = $Id === null ? false : Basket::useService()->getBasketPersonById($Id);
        if (!$tblBasketPerson) {
            $Stage->setContent(new Warning('Person/Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        if (!$tblBasketPerson->getTblBasket()) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasket = $tblBasketPerson->getTblBasket();
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( Basket::useService()->removeBasketPerson($tblBasketPerson) )
                                ? new Success('Die Person '.$tblBasketPerson->getServiceTblPerson()->getFullName().' wurde erfolgreich entfernt')
                                .new Redirect('/Billing/Bookkeeping/Basket/Person/Select', Redirect::TIMEOUT_SUCCESS,
                                    array('Id' => $tblBasketPerson->getTblBasket()->getId()))
                                : new Warning('Die Person '.$tblBasketPerson->getServiceTblPerson()->getFullName().' konnte nicht entfernt werden')
                                .new Redirect('/Billing/Bookkeeping/Basket/Person/Select', Redirect::TIMEOUT_ERROR,
                                    array('Id' => $tblBasketPerson->getTblBasket()->getId()))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendBasketCalculation($Id = null)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerification) {
            $Stage->setContent(new Warning('Berechnung schon im Gange'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft(), array('Id' => $tblBasket->getId())));

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Basket::useService()->createBasketVerification($tblBasket)
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendBasketVerification($Id = null)
    {

        $Stage = new Stage('Warenkorb', 'Übersicht');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
        }
//        $Stage->addButton(new Backward());

//        $Stage->addButton(new \SPHERE\Common\Frontend\Link\Repository\Danger('Berechnungen leeren', '/Billing/Bookkeeping/Basket/Verification/Destroy', new Disable()
//            , array('BasketId' => $tblBasket->getId())));
//        $Stage->addButton(new Standard('Zahlung fakturieren', '/Billing/Accounting/Payment/Selection', new Ok()
//            , array('Id' => $tblBasket->getId())));
//        $Stage->addButton(new Standard('Rechnung Test', '/Billing/Bookkeeping/Basket/Invoice/Create', new EyeOpen()
//            , array('Id' => $tblBasket->getId())));

        $tblPersonList = Basket::useService()->getPersonAllByBasket($tblBasket);
        if (!$tblPersonList) {
            $Stage->setContent(new Warning('Keine Personen in der Berechnung enthalten.'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        $TableContent = array();
        if ($tblPersonList) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblBasket) {

                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Address = new \SPHERE\Common\Frontend\Text\Repository\Warning('keine Adresse hinterlegt');
                if (Address::useService()->getAddressByPerson($tblPerson)) {
                    $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
                }
                $Item['Address'] = $Address;
                $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
                $ItemArray = array();
                $Sum = 0;
                if (is_array($tblBasketVerificationList)) {
                    /** @var TblBasketVerification $tblBasketVerification */
                    foreach ($tblBasketVerificationList as $tblBasketVerification) {
                        if ($tblBasketVerification->getServiceTblItem()) {
                            $ItemArray[] = $tblBasketVerification->getServiceTblItem()->getName();
                        }
                        $Sum += $tblBasketVerification->getValue() * $tblBasketVerification->getQuantity();
                    }
                }
                $Item['ItemList'] = implode(', ', $ItemArray);
                $Item['SummaryPrice'] = number_format($Sum, 2).' €';

                $Item['ChildRank'] = '';
                $Item['CourseType'] = '';

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    if ($tblStudent->getTblStudentBilling()->getServiceTblSiblingRank()) {
                        $Item['ChildRank'] = $tblStudent->getTblStudentBilling()->getServiceTblSiblingRank()->getName();
                    }
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblType = $tblStudentTransfer->getServiceTblType();
                            if ($tblType) {
                                $Item['CourseType'] = $tblType->getName();
                            }
                        }
                    }
                }

                $Item['Option'] = new Standard('', '/Billing/Bookkeeping/Basket/Verification/Person', new EyeOpen(),
                        array('PersonId' => $tblPerson->getId(),
                              'BasketId' => $tblBasket->getId()), 'Artikel anzeigen')
                    .new Standard('', '/Billing/Bookkeeping/Basket/Verification/Person/Remove', new Disable(),
                        array('Id'       => $tblBasket->getId(),
                              'PersonId' => $tblPerson->getId()), 'Person aus Berechnung entfernen');

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'LastName'     => 'Nachname',
                                    'FirstName'    => 'Vorname',
                                    'Address'      => 'Adresse',
                                    // Todo entfernen?
                                    'ChildRank'    => 'Geschwisterkind',
                                    'CourseType'   => 'Schulart',
                                    //
                                    'ItemList'     => 'Artikel Liste',
                                    'SummaryPrice' => 'Gesammtpreis',
                                    'Option'       => '',
                                ))
                        )
                    ), new Title(new ListingTable().' Übersicht')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Info('Sind alle Beträge kontrolliert und die Anzahl der Artikel richtig eingegeben, kann die Rechnung hier erstellt werden.
                            Benötigte änderungen der bereits vergebenen Bezahlzuweisungen müssen vor dem faktuieren abgeändert oder gelöscht werden.
                            Alle nicht zugewiesenen Bezahlzuweisungen werden hier abgefragt und für die wiederverwendung gespeichert.<br/>'
                                .new Standard('Zahlungen fakturieren '.new ChevronRight(), '/Billing/Accounting/Payment/Selection', null
                                    , array('Id' => $tblBasket->getId()))
                                .new \SPHERE\Common\Frontend\Link\Repository\Warning('Rechnung Test', '/Billing/Bookkeeping/Basket/Invoice/Create', new EyeOpen()
                                    , array('Id' => $tblBasket->getId())))
                            , 6),
                        new LayoutColumn(
                            new Warning('Ist die Rechnung nicht mehr aktuell, da Personen oder Artikel fehlen oder grundlegende Preise geändert wurden, muss der Warenkorb zurück gesetzt werden.
                            Hierbei gehen alle preisbezogenen Einstellungen verloren. Personen und Artikel bleiben weiterhin im Warenkorb enthalten.<br/>'
                                .new \SPHERE\Common\Frontend\Link\Repository\Danger('Berechnungen leeren', '/Billing/Bookkeeping/Basket/Verification/Destroy', new Disable()
                                    , array('BasketId' => $tblBasket->getId())))
                            , 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     * @param null $BasketId
     *
     * @return Stage|string
     */
    public function frontendBasketVerificationPersonShow($PersonId = null, $BasketId = null)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblPerson = $PersonId === null ? false : Person::useService()->getPersonById($PersonId);
        $tblBasket = $BasketId === null ? false : Basket::useService()->getBasketById($BasketId);

        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $Address = new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
        if (Address::useService()->getAddressByPerson($tblPerson)) {
            $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiLayout();
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Verification', new ChevronLeft(),
            array('Id' => $tblBasket->getId())));
//        $Stage->addButton(new Backward());

        $TableContent = array();
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
        if ($tblBasketVerificationList) {
            /** @var TblBasketVerification $tblBasketVerification */
            array_walk($tblBasketVerificationList, function (TblBasketVerification $tblBasketVerification) use (&$TableContent) {

                $tblItem = $tblBasketVerification->getServiceTblItem();
                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDescription();
                $Item['Type'] = $tblItem->getTblItemType()->getName();
                $Item['SinglePrice'] = $tblBasketVerification->getPrice();
                $Item['Quantity'] = $tblBasketVerification->getQuantity();
                $Item['Summary'] = $tblBasketVerification->getSummaryPrice();
                $Item['Option'] = new Standard('', '/Billing/Bookkeeping/Basket/Verification/Edit', new Edit(),
                        array('Id' => $tblBasketVerification->getId()), 'Preis / Anzahl bearbeiten')
                    .new Standard('', '/Billing/Bookkeeping/Basket/Verification/Destroy', new Disable(),
                        array('Id' => $tblBasketVerification->getId()), 'Artikel von Person entfernen');

                array_push($TableContent, $Item);
            });
        } else {
            $Stage->setContent(new Warning('Keine Artikel an dieser Person'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR, array('Id' => $tblBasket->getId()));
        }
        $SiblingRank = '-';
        $SchoolType = '-';
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                    $SiblingRank = $tblSiblingRank->getName();
                }
            }

            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            if ($tblTransferType) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblType = $tblStudentTransfer->getServiceTblType();
                    if ($tblType) {
                        $SchoolType = $tblType->getName();
                    }
                }
            }
        }

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Person', array($tblPerson->getFullName(),
                                    'Geschwisterkind: '.$SiblingRank
                                , 'Schulart: '.$SchoolType)
                                , Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(
                            new Panel('Adresse', array($Address)
                                , Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                    )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array('Name'        => 'Artikel',
                                      'Description' => 'Beschreibung',
                                      'Type'        => 'Art',
                                      'SinglePrice' => 'Einzelpreis',
                                      'Quantity'    => 'Anzahl',
                                      'Summary'     => 'Gesammtpreis',
                                      'Option'      => '',
                                ))
                        )
                    ), new Title(new ListingTable().' Übersicht')
                )
            )
        );
        return $Stage;
    }

    public function frontendCreateInvoice($Id = null)
    {

        $Stage = new Stage('Rechnungen', 'erstellen');

        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket', new ChevronLeft()));
//        $Stage->addButton(new Backward());

        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        if (Invoice::useService()->createInvoice($tblBasket)) {
            $Stage->setContent(new Success('Rechnungen erstellt'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_SUCCESS);
        } else {
            $Content = new Warning('Rechnungserstellungen fehlgeschlagen');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $Content
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Item
     *
     * @return Stage|string
     */
    public function frontendEditBasketVerification($Id = null, $Item = null)
    {

        $Stage = new Stage('Warenkorb', 'Berechnung');
        $tblBasketVerification = $Id === null ? false : Basket::useService()->getBasketVerificationById($Id);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Warenkorbinhalt nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Verification/Person', new ChevronLeft(),
            array('PersonId' => $tblBasketVerification->getServiceTblPerson()->getId(),
                  'BasketId' => $tblBasketVerification->getTblBasket()->getId())));
//        $Stage->addButton(new Backward(true));

        $tblItem = $tblBasketVerification->getServiceTblItem();
        $tblPerson = $tblBasketVerification->getServiceTblPerson();
        $tblBasket = $tblBasketVerification->getTblBasket();
        if (!$tblItem) {
            $Stage->setContent(new Warning('Artikel nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        }
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasketVerification->getTblBasket()->getId()));
        }
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
//        $Address = 'Keine Adresse hinterlegt';
//        if (Address::useService()->getAddressByPerson($tblPerson)) {
//            $Address = Address::useService()->getAddressByPerson($tblPerson)->getGuiString();
//        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Item'] )) {
            $Global->POST['Item']['Price'] = $tblBasketVerification->getPrice();
            $Global->POST['Item']['Quantity'] = $tblBasketVerification->getQuantity();
            $Global->POST['Item']['PriceChoice'] = 'Einzelpreis';
            $Global->savePost();
        }

        $SiblingRank = '-';
        $SchoolType = '-';
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                    $SiblingRank = $tblSiblingRank->getName();
                }
            }

            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
            if ($tblTransferType) {
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblTransferType);
                if ($tblStudentTransfer) {
                    $tblType = $tblStudentTransfer->getServiceTblType();
                    if ($tblType) {
                        $SchoolType = $tblType->getName();
                    }
                }
            }
        }

        $layout = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', array($tblPerson->getFullName(),
                                'Geschwisterkind: '.$SiblingRank
                            , 'Schulart: '.$SchoolType)
                            , Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                    new LayoutColumn(
                        new Panel('Artikel: '.$tblItem->getName(), array(
                            'Beschreibung: '.$tblItem->getDescription(),
                            'Einzelpreis: '.$tblBasketVerification->getPrice(),
                            'Anzahl: '.$tblBasketVerification->getQuantity(),
                            'Gesamtpreis: '.$tblBasketVerification->getSummaryPrice(),
                        ), Panel::PANEL_TYPE_SUCCESS)
                        , 6),
                )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person / '.new CommodityItem().' Artikel')
            )
        );

        $Form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikelpreis', array(
                                new TextField('Item[Price]', '', 'Preis', new Money()),
                                new RadioBox('Item[PriceChoice]', 'Einzelpreis', 'Einzelpreis'),
                                new RadioBox('Item[PriceChoice]', 'Gesamtpreis', 'Gesamtpreis'),)
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Anzahl der Artikel', array(new TextField('Item[Quantity]', '', 'Anzahl', new Quantity()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutBasket($tblBasket)
            .$layout
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Title(new Edit().' Bearbeiten'),
                            new Well(
                                Basket::useService()->changeBasketVerification($Form,
                                    $tblBasketVerification, $Item)
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $PersonId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendRemoveVerificationPerson($Id = null, $PersonId = null, $Confirm = false)
    {

        $Stage = new Stage('Eintrag', 'Löschen');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Verification', new ChevronLeft(), array('Id' => $tblBasket->getId())));
        }
        $tblPerson = $PersonId === null ? false : Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblBasket->getId()));
        }

        if (!$Confirm) {
            $Person = $tblPerson->getFullName();
            $tblBasketVerificationList = Basket::useService()->getBasketVerificationByPersonAndBasket($tblPerson, $tblBasket);
            $Content = array();
            if ($tblBasketVerificationList) {
                foreach ($tblBasketVerificationList as $Key => $tblBasketVerification) {
                    $Content[$Key] = $tblBasketVerification->getServiceTblItem()->getName();
                    $Content[$Key] .= ' - '.$tblBasketVerification->getSummaryPrice();
                }
            }
            if (empty( $Content )) {
                $Content = 'Keine Artikel zugewiesen.';
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Diese Person "'.$Person.'" inklusive folgender Artikel wirklich entfernen?', $Content
                        , Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Bookkeeping/Basket/Verification/Person/Remove', new Ok(),
                            array('Id' => $Id, 'PersonId' => $tblPerson->getId(), 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Bookkeeping/Basket/Verification', new Disable(),
                            array('Id' => $tblBasket->getId())
                        )
                    )
                    , 6))))
            );
        } else {

            $tblBasketPerson = Basket::useService()->getBasketPersonByBasketAndPerson($tblBasket, $tblPerson);
            if (!$tblBasketPerson) {
                $Stage->setContent(new Warning('Person in Fakturierung nicht gefunden'));
                return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblBasket->getId()));
            }

            // Destroy Basket
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Basket::useService()->removeBasketPerson($tblBasketPerson) ?
                            new Success('Person erfolgreich aus der Fakturierung entfernt')
                            .new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblBasket->getId())) :
                            new Danger('Person konnte nicht aus der Fakturierung entfernt werden.')
                            .new Redirect('/Billing/Bookkeeping/Basket/Verification', Redirect::TIMEOUT_ERROR,
                                array('Id' => $tblBasket->getId())) )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $BasketId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyVerification($Id = null, $BasketId = null, $Confirm = false)
    {

        $Stage = new Stage('Preise', 'Löschen');
//        $Stage->addButton(new Backward(true));
        if ($Id !== null) {
            $tblBasketVerification = Basket::useService()->getBasketVerificationById($Id);
            if (!$tblBasketVerification) {
                $Stage->setContent(new Warning('Artikel nicht gefunden'));
                return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
            } else {
                $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Verification/Person', new ChevronLeft(),
                    array('PersonId' => $tblBasketVerification->getServiceTblPerson()->getId(),
                          'BasketId' => $tblBasketVerification->getTblBasket()->getId())));
            }

            if (!$Confirm) {
                $Person = '';
                $Item = '';
                $Price = '';
                $ItemType = '';
                if ($tblBasketVerification->getServiceTblPerson()) {
                    $Person = $tblBasketVerification->getServiceTblPerson()->getFullName();
                }
                if ($tblBasketVerification->getServiceTblItem()) {
                    $Item = $tblBasketVerification->getServiceTblItem()->getName();
                    $Price = $tblBasketVerification->getSummaryPrice();
                    if ($tblBasketVerification->getServiceTblItem()->getTblItemType()) {
                        $ItemType = $tblBasketVerification->getServiceTblItem()->getTblItemType()->getName();
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                        new Panel(new Question().' Diesen Eintrag mit folgenden Daten wirklich entfernen?',
                            array(
                                'Person: '.$Person,
                                'Artikel: '.$Item,
                                'Preis: '.$Price,
                                'Artikel Typ: '.$ItemType,
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Bookkeeping/Basket/Verification/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Bookkeeping/Basket/Verification/Person', new Disable(),
                                array('PersonId' => $tblBasketVerification->getServiceTblPerson()->getId(),
                                      'BasketId' => $tblBasketVerification->getTblBasket()->getId())
                            )
                        )
                        , 6),
                        new LayoutColumn(
                            new Warning('Entfernt den Artikel von der Person.
                                Entfernte Artikel können nicht über '.new Bold('"Warenkorb Übersicht"').' zugewiesen werden.
                                Sollen ein Artikel den Personen zugeordnet werden, muss der '.new Bold('"Warenkorb Übersicht"').'
                                geleert werden und in der '.new Bold('"Warenkorb Zusammenstellung"').' hinzugefügt werden.')
                            , 6)
                    ))))
                );
            } else {

                // Destroy Basket
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            Basket::useService()->destroyBasketVerification($tblBasketVerification)
                        )))
                    )))
                );
            }
        } else {
            if (null !== $BasketId) {
                $tblBasket = Basket::useService()->getBasketById($BasketId);
                if ($tblBasket) {
                    $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Basket/Verification', new ChevronLeft(),
                        array('Id' => $tblBasket->getId())));
                    $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
                    if ($tblBasketVerificationList) {
                        if (!$Confirm) {
                            $CountVerification = count($tblBasketVerificationList);
                            $PanelPersonContent = array();
                            $PanelItemContent = array();
                            if (!empty( $tblBasketVerificationList )) {
                                $PersonIdList = array();
                                $ItemIdList = array();
                                foreach ($tblBasketVerificationList as $tblBasketVerification) {
                                    $PersonIdList[] = $tblBasketVerification->getServiceTblPerson()->getId();
                                    $ItemIdList[] = $tblBasketVerification->getServiceTblItem()->getId();
                                }
                                $PersonIdList = array_unique($PersonIdList);
                                $ItemIdList = array_unique($ItemIdList);

                                $PersonList = array();
                                foreach ($PersonIdList as $PersonId) {
                                    $tblPerson = Person::useService()->getPersonById($PersonId);
                                    if ($tblPerson) {
                                        $PersonList[] = $tblPerson->getLastFirstName();
                                    }
                                }
                                $ItemList = array();
                                foreach ($ItemIdList as $ItemId) {
                                    $tblItem = Item::useService()->getItemById($ItemId);
                                    if ($tblItem) {
                                        $ItemList[] = $tblItem->getName();
                                    }
                                }
                                $PanelPersonContent[] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($PersonList);
                                $PanelItemContent[] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($ItemList);

                            }
                            $Stage->setContent(
                                new Layout(
                                    new LayoutGroup(
                                        new LayoutRow(
                                            new LayoutColumn(
                                                new Warning('Ist die Rechnung nicht mehr aktuell da Personen oder Artikel fehlen muss der Warenkorb zurück gesetzt werden.
                                                Hierbei gehen alle preisbezogenen Einstellungen verloren. Personen und Artikel bleiben weiterhin im Warenkorb enthalten.')
                                            )
                                        )
                                    )
                                )
                                .new Layout(new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                                    new Panel('Personen', $PanelPersonContent, Panel::PANEL_TYPE_WARNING)
                                    , 6),
                                    new LayoutColumn(
                                        new Panel('Artikel', $PanelItemContent, Panel::PANEL_TYPE_WARNING)
                                        , 6),
                                ))))
                                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                    new Panel(
                                        ( $CountVerification == 1 ? new Question().' Die Berechnung des einen Preises wirklich löschen?' :
                                            new Question().' Die Berechnung der '.$CountVerification.' Preise wirklich löschen?' )
                                        ,
                                        '',
                                        Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Billing/Bookkeeping/Basket/Verification/Destroy', new Ok(),
                                            array('BasketId' => $BasketId, 'Confirm' => true)
                                        )
                                        .new Standard(
                                            'Nein', '/Billing/Bookkeeping/Basket/Verification', new Disable(),
                                            array('Id' => $tblBasket->getId())
                                        )
                                    )
                                    , 6))))
                            );
                            return $Stage;
                        } else {

                            // Destroy BasketVerification
                            $Error = false;
                            foreach ($tblBasketVerificationList as $tblBasketVerification) {
                                if (!Basket::useService()->destroyBasketVerificationList($tblBasketVerification)) {
                                    $Error = true;
                                }
                            }
                            if (!$Error) {
                                $Stage->setContent(new Success('Berechnung wurde geleert'));
                                return $Stage.new Redirect('/Billing/Bookkeeping/Basket/Content', Redirect::TIMEOUT_SUCCESS,
                                    array('Id' => $tblBasket->getId()));
                            } else {
                                $Stage->setContent(new Danger('Berechnung konnte nicht geleert werden'));
                                return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
                            }
                        }
                    }
                }
            }
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $DataName
     *
     * @return array
     */
    private function setPersonData(TblPerson $tblPerson, $DataName)
    {
        $result = array();
        $result['Check'] = new CheckBox(
            $DataName.'['.$tblPerson->getId().']',
            ' ',
            1
        );
        $result['DisplayName'] = $tblPerson->getLastFirstName();
        $tblAddress = $tblPerson->fetchMainAddress();
        $result['Address'] = $tblAddress ? $tblAddress->getGuiString() : '';
        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
        $groups = array();
        if ($tblGroupList) {
            foreach ($tblGroupList as $item) {
                if ($item->getMetaTable() !== 'COMMON') {
                    $groups[] = $item->getName();
                }
            }
        }

        // current Division
        $tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPerson);

        $result['Groups'] = ( !empty( $groups ) ? implode(', ', $groups).( $tblDivision ? ', ' : '' ) : '' )
            .( $tblDivision ? 'Klasse '.$tblDivision->getDisplayName() : '' );

        return $result;
    }

    /**
     * @return Form
     */
    private function formPersonFilter()
    {

        $tblGroupAll = Group::useService()->getGroupAllSorted();
        $tblDivisionList = array();
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblYear);
                if ($tblDivisionAllByYear) {
                    foreach ($tblDivisionAllByYear as $tblDivision) {
                        $tblDivisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }
        }

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Filter[Group]', 'Gruppe', array('Name' => $tblGroupAll)), 6
                    ),
                    new FormColumn(
                        new SelectBox('Filter[Division]', 'Klasse', array('DisplayName' => $tblDivisionList)), 6
                    ),
                    new FormColumn(
                        new Primary('Suchen', new Filter())
                    ),
                ))
            )
        );
    }
}
