<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
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
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Item
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Item
     *
     * @return Stage
     */
    public function frontendItemStatus($Item = null)
    {

        $Stage = new Stage('Artikel', 'Übersicht');
//        new Backward();
        $tblItemAll = Item::useService()->getItemAll();

        $TableContent = array();
        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Item['Name'] = $tblItem->getName();
                $Item['Description'] = $tblItem->getDisplayDescription();
                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
                $CalculationContent = array();
                if ($tblCalculationList) {
                    $ItemCount = count($tblCalculationList);
                    /** @var TblCalculation $tblCalculation */

                    foreach ($tblCalculationList as $Key => $tblCalculation) {
                        $CalculationContent[$Key] = 'Preis: '.new Bold($tblCalculation->getPriceString()).' Bedingung: ';
                        if ($tblCalculation->getServiceTblType() && $tblCalculation->getServiceTblSiblingRank()) {
                            $CalculationContent[$Key] .= $tblCalculation->getServiceTblType()->getName().' - '.$tblCalculation->getServiceTblSiblingRank()->getName();
                        } elseif ($tblCalculation->getServiceTblType()) {
                            $CalculationContent[$Key] .= $tblCalculation->getServiceTblType()->getName();
                        } elseif ($tblCalculation->getServiceTblSiblingRank()) {
                            $CalculationContent[$Key] .= $tblCalculation->getServiceTblSiblingRank()->getName();
                        }

                        if (!$tblCalculation->getServiceTblSiblingRank() && !$tblCalculation->getServiceTblType()) {
                            if ($tblItem->getTblItemType()->getName() == 'Einzelleistung') {
                                if ($ItemCount == 1) {
                                    $CalculationContent[$Key] = 'Standardpreis: '.new Bold($tblCalculation->getPriceString());
                                } else {
                                    $CalculationContent[$Key] = false;
                                }
                            } else {
                                $CalculationContent[$Key] = 'Gesamtpreis: '.new Bold($tblCalculation->getPriceString());
                            }
                        }
                    }
                    $CalculationContent = array_filter($CalculationContent);
                }
                $Item['Condition'] = new Listing($CalculationContent);

                $Item['Option'] =
                    new Standard('', '/Billing/Inventory/Item/Change', new Pencil(), array('Id' => $tblItem->getId()), 'Bearbeiten')
                    .new Standard('', '/Billing/Inventory/Item/Calculation', new Equalizer(), array('Id' => $tblItem->getId()), 'Preise / Bedingungen eintragen');

                array_push($TableContent, $Item);
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Item'] )) {
            $Global->POST['Item']['ItemType'] = 'Einzelleistung';
            $Global->savePost();
        }

        $Form = $this->formItem()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'ItemType'    => 'Art',
                                    'Condition'   => 'Preis - Schulart - Geschwister',
                                    'Option'      => ''
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
                            Item::useService()->createItem($Form, $Item)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formItem()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikel',
                            array(
                                new TextField('Item[Name]', 'Name', 'Name', new Conversation()),
                                new TextField('Item[Value]', 'Preis', 'Standardpreis', new Money()),
                                new RadioBox('Item[ItemType]', 'Einzelleistung', 'Einzelleistung'),
                                new RadioBox('Item[ItemType]', 'Sammelleistung', 'Sammelleistung'),

//                                new CheckBox('Item[ItemType]', 'Einzelleistung', 'Einzelleistung', array('Item[CalculationType]')),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextArea('Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    public function formItemChange()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Artikel',
                            array(
                                new TextField('Item[Name]', 'Name', 'Name', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextArea('Item[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            ), Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    /**
     * @param null       $Id
     * @param null       $CalculationId
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyCalculation($Id = null, $CalculationId = null, $Confirm = false)
    {

        $Stage = new Stage('Zuordnung', 'Entfernen');
        if ($CalculationId) {
            $tblCalculation = Item::useService()->getCalculationById($CalculationId);
            $tblItem = Item::useService()->getItemById($Id);
            if (!$tblItem) {
                $Stage->setContent(new Warning('Bedingung nicht gefunden'));
                return $Stage.new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
            }
            if ($tblCalculation && $tblItem) {
                $Content = array();
                $Content[] = 'Preis: '.$tblCalculation->getPriceString();
                if ($tblCalculation->getServiceTblType()) {
                    $Content[] = 'Schulart: '.$tblCalculation->getServiceTblType()->getName();
                }
                if ($tblCalculation->getServiceTblSiblingRank()) {
                    $Content[] = 'Geschwister: '.$tblCalculation->getServiceTblSiblingRank()->getName();
                }
                if (!$Confirm) {
//                    $Stage->addButton(new Backward());
                    $Stage->addButton(new Standard(
                        'Zurück', '/Billing/Inventory/Item/Calculation', new ChevronLeft(), array('Id' => $tblItem->getId())));
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(new Question().' Diese Zurodnung wirklich entfernen?',
                                $Content,
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Billing/Inventory/Item/Calculation/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true, 'CalculationId' => $CalculationId)
                                )
                                .new Standard(
                                    'Nein', '/Billing/Inventory/Item/Calculation', new Disable(), array('Id' => $tblItem->getId())
                                )
                            )
                        ))))
                    );
                } else {

                    // Destroy Group
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( Item::useService()->destroyCalculation($tblCalculation, $tblItem)
                                    ? new Success('Die Zuordnung wurde gelöscht')
                                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_SUCCESS,
                                        array('Id' => $tblItem->getId()))
                                    : new Danger('Die Zuordnung konnte nicht gelöscht werden')
                                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_ERROR,
                                        array('Id' => $tblItem->getId()))
                                )
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Bedingung nicht gefunden'),
                        new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Item
     *
     * @return Stage
     */
    public function frontendChangeItem($Id = null, $Item = null)
    {

        $Stage = new Stage('Artikel', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item', new ChevronLeft()));
//        $Stage->addButton(new Backward());

        $tblItem = Item::useService()->getItemById($Id);
        if (!$tblItem) {
            $Stage->setContent(new Warning('Der Artikel konnten nicht aufgerufen werden'));
            return $Stage.new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
        }
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Item'] )) {
            $Global->POST['Item']['Name'] = $tblItem->getName();
            $Global->POST['Item']['Description'] = $tblItem->getDescription();
//                $Global->POST['Item']['ItemType'] = $tblItem->getTblItemType()->getId();
            $Global->savePost();
        }

        $Form = $this->formItemChange()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutItem($tblItem)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new Well(
                            Item::useService()->changeItem($Form, $tblItem, $Item)
                        ))
                    )), new Title(new Pencil().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Calculation
     *
     * @return Stage
     */
    public function frontendItemCalculation($Id = null, $Calculation = null)
    {

        $Stage = new Stage('Preise', 'mit Bedingungen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $tblItem = Item::useService()->getItemById($Id);
        if (!$tblItem) {
            $Stage->setContent(new Warning('Kein Artikel gefunden'));
            return $Stage.new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
        }
        $tblCalculationList = Item::useService()->getCalculationAllByItem($tblItem);
        $TableContent = array();
        if (is_array($tblCalculationList)) {
            array_walk($tblCalculationList, function (TblCalculation $tblCalculation) use (&$TableContent, $tblItem) {

                $Item['Price'] = $tblCalculation->getPriceString();
                $Item['Cours'] = '';
                $Item['SiblingRank'] = '';

                if ($tblCalculation->getServiceTblType()) {
                    $Item['Cours'] = $tblCalculation->getServiceTblType()->getName();
                }
                if ($tblCalculation->getServiceTblSiblingRank()) {
                    $Item['SiblingRank'] = $tblCalculation->getServiceTblSiblingRank()->getName();
                }

                if ($Item['SiblingRank'] === '' && $Item['Cours'] === '') {
                    $Item['Option'] = new Standard('', '/Billing/Inventory/Item/Calculation/Change', new Pencil(),
                        array('Id'            => $tblItem->getId(),
                              'CalculationId' => $tblCalculation->getId()));
                } else {
                    $Item['Option'] = new Standard('', '/Billing/Inventory/Item/Calculation/Change', new Pencil(),
                            array('Id'            => $tblItem->getId(),
                                  'CalculationId' => $tblCalculation->getId()))
                        .new Standard('', '/Billing/Inventory/Item/Calculation/Destroy', new Disable(),
                            array('Id'            => $tblItem->getId(),
                                  'CalculationId' => $tblCalculation->getId()));
                }

                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formItemCalculation()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutItem($tblItem)
            .new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent,
                                null,
                                array('Price'       => 'Preis',
                                      'Cours'       => 'Schulart',
                                      'SiblingRank' => 'Geschwister',
                                      'Option'      => '',))
                        )
                    ), new Title(new ListingTable().' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Item::useService()->createCalculation(
                                    $Form, $tblItem, $Calculation)
                            )
                        )
                    ), new Title(new PlusSign().' Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return Layout
     */
    public function layoutItem(TblItem $tblItem)
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Artikel', $tblItem->getName(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Beschreibung', $tblItem->getDisplayDescription(), Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Art', $tblItem->getTblItemType()->getName(), Panel::PANEL_TYPE_SUCCESS)
                        , 4)
                ))
                , new Title(new CommodityItem().' Artikel'))
        );
    }

    /**
     * @return Form
     */
    public function formItemCalculation()
    {

        $tblSchoolType = Type::useService()->getTypeAll();
        $tblSchoolType[] = new TblType();
        $tblSiblingRank = Relationship::useService()->getSiblingRankAll();
        $tblSiblingRank[] = new TblSiblingRank();

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Preis', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Schulart', array(new SelectBox('Calculation[SchoolType]', '',
                            array('Name' => $tblSchoolType))), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Geschwisterkind', array(new SelectBox('Calculation[SiblingRank]', '',
                            array('Name' => $tblSiblingRank))), Panel::PANEL_TYPE_INFO)
                        , 4)
                ))
            )
        );
    }

    /**
     * @param null $Id
     * @param null $CalculationId
     * @param null $Calculation
     *
     * @return Stage
     */
    public function frontendChangeCalculation($Id = null, $CalculationId = null, $Calculation = null)
    {

        $Stage = new Stage('Bedinung', 'Bearbeiten');
        $tblItem = Item::useService()->getItemById($Id);
        $tblCalculation = Item::useService()->getCalculationById($CalculationId);
        if (!$tblItem && !$tblCalculation) {
            $Stage->addButton(new Standard('Zurück', '/Billing/Invoice/Item', new ChevronLeft()));
            $Stage->setContent(
                new Warning('Artikel oder Bedingung nicht gefunden')
                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR)
            );
        } else {
            $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item/Calculation', new ChevronLeft(),
                array('Id' => $tblItem->getId())));
//            $Stage->addButton(new Backward());

            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Calculation'] )) {
                $Global->POST['Calculation']['Value'] = str_replace('.', ',', number_format($tblCalculation->getValue(), 2));
                if ($tblCalculation->getServiceTblType()) {
                    $Global->POST['Calculation']['SchoolType'] = $tblCalculation->getServiceTblType()->getId();
                }
                if ($tblCalculation->getServiceTblSiblingRank()) {
                    $Global->POST['Calculation']['SiblingRank'] = $tblCalculation->getServiceTblSiblingRank()->getId();
                }
                $Global->savePost();
            }
            $schoolType = 'Nicht ausgewählt';
            $siblingRank = 'Nicht ausgewählt';
            if ($tblCalculation->getServiceTblType()) {
                $schoolType = $tblCalculation->getServiceTblType()->getName();
            }
            if ($tblCalculation->getServiceTblSiblingRank()) {
                $siblingRank = $tblCalculation->getServiceTblSiblingRank()->getName();
            }
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Panel('Preis', array(new TextField('Calculation[Value]', '', '')), Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            );
            $Form->appendFormButton(new Primary('Speichern', new Save()));
            $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                $this->layoutItem($tblItem)
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Preis', $tblCalculation->getPriceString(), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Bedingungen',
                                    new Layout(
                                        new LayoutGroup(
                                            new LayoutRow(array(
                                                new LayoutColumn(
                                                    new Panel('Schulart:', $schoolType)
                                                    , 6),
                                                new LayoutColumn(
                                                    new Panel('Geschwisterkind:', $siblingRank)
                                                    , 6),
                                            ))
                                        )
                                    ), Panel::PANEL_TYPE_INFO)
                                , 8)
                        )), new Title('Zu bearbeitende Bedingung:')
                    )
                )
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Well(
                                    Item::useService()->changeCalculation(
                                        $Form, $tblItem, $tblCalculation, $Calculation))
                                , 6)
                        ), new Title(new Pencil().' Bearbeiten')
                    )
                )
            );
        }

        return $Stage;
    }
}
