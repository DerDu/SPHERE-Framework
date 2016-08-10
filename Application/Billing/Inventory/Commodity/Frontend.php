<?php

namespace SPHERE\Application\Billing\Inventory\Commodity;

use Doctrine\Common\Cache\ArrayCache;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Commodity
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Commodity
     *
     * @return Stage
     */
    public function frontendStatus($Commodity = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistungen');
        $Stage->setDescription('Übersicht');
//        new Backward();

        $tblCommodityAll = Commodity::useService()->getCommodityAll();

        $TableContent = array();
        if (!empty( $tblCommodityAll )) {
            array_walk($tblCommodityAll, function (TblCommodity $tblCommodity) use (&$TableContent) {

                $Item['Name'] = $tblCommodity->getName();
                $Item['Description'] = $tblCommodity->getDescription();
                $ItemList = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                $ItemArray = array();
                if ($ItemList) {
                    foreach ($ItemList as $ItemL) {
                        $ItemArray[] = $ItemL->getName();
                    }
                }
                $ItemStringList = '';
                if (!empty( $ItemArray )) {
                    $ItemStringList = implode(', ', $ItemArray);
                }
                $Item['ItemList'] = $ItemStringList;
                $Item['Option'] = (new Standard('', '/Billing/Inventory/Commodity/Change',
                        new Pencil(), array(
                            'Id' => $tblCommodity->getId()
                        ), 'Bearbeiten'))->__toString().
                    (new Standard('', '/Billing/Inventory/Commodity/Item/Select',
                        new Listing(), array(
                            'Id' => $tblCommodity->getId()
                        ), 'Artikel auswählen'))->__toString();
//                    .(new Standard('Löschen', '/Billing/Inventory/Commodity/Destroy',
//                        new Remove(), array(
//                            'Id' => $tblCommodity->getId()
//                        )))->__toString();
                array_push($TableContent, $Item);
            });
        }
        $Form = $this->formCommodity()
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
                                    'ItemList'    => 'Artikel',
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
                            Commodity::useService()->createCommodity($Form, $Commodity)
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
    public function formCommodity()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Leistung', array(new TextField('Commodity[Name]', 'Name', 'Name', new Conversation()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Commodity[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    /**
     * @param null $Id
     * @param null $Commodity
     *
     * @return Stage
     */
    public function frontendChange($Id = null, $Commodity = null)
    {

        $Stage = new Stage('Leistungen', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Commodity', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $tblCommodity = Commodity::useService()->getCommodityById($Id);
        if (!$tblCommodity) {
            $Stage->setContent(new Warning('Die Leistung konnte nicht abgerufen werden'));
            return $Stage.new Redirect('/Billing/Inventory/Commodity', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Commodity'] )) {
            $Global->POST['Commodity']['Name'] = $tblCommodity->getName();
            $Global->POST['Commodity']['Description'] = $tblCommodity->getDescription();
            $Global->savePost();
        }

        $PanelValue = array();
        $PanelValue[0] = $tblCommodity->getName();
        $PanelValue[1] = $tblCommodity->getDescription();
        $PanelContent = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $PanelValue[0], Panel::PANEL_TYPE_INFO)
                        , 6),
                    new LayoutColumn(
                        new Panel('Beschreibung', $PanelValue[1], Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            )
        );

        $Form = $this->formCommodity()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelContent
                        )
                    )
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Commodity::useService()->changeCommodity($Form, $tblCommodity, $Commodity)
                        ))
                    ), new Title(new Pencil().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $tblCommodityId
     * @param null $tblItemId
     *
     * @return Stage
     */
    public function frontendAddItem($tblCommodityId = null, $tblItemId = null)
    {

        $Stage = new Stage('Leistung', 'Artikel Hinzufügen');
        $tblCommodity = Commodity::useService()->getCommodityById($tblCommodityId);
        $tblItem = Item::useService()->getItemById($tblItemId);
        if (!$tblCommodity) {
            $Stage->setContent(new Warning('Leistung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Inventory/Commodity', Redirect::TIMEOUT_ERROR);
        }
        if (!$tblItem) {
            $Stage->setContent(new Warning('Artikel nicht gefunden'));
            return $Stage.new Redirect('/Billing/Inventory/Commodity', Redirect::TIMEOUT_ERROR);
        }

        if (!empty( $tblCommodityId ) && !empty( $tblItemId )) {
            $Stage->setContent(Commodity::useService()->addItemToCommodity($tblCommodity, $tblItem));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendItemSelect($Id = null)
    {

        $Stage = new Stage('Leistung', 'Artikel auswählen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Commodity', new ChevronLeft()));
//        $Stage->addButton(new Backward());
        $tblCommodity = Commodity::useService()->getCommodityById($Id);
        if (!$tblCommodity) {
            $Stage->setContent(new Warning('Die Leistung konnte nicht abgerufen werden'));
            return $Stage.new Redirect('/Billing/Inventory/Commodity', Redirect::TIMEOUT_ERROR);
        } else {
            $tblCommodityItem = Commodity::useService()->getCommodityItemAllByCommodity($tblCommodity);
            $tblItemAllByCommodity = Commodity::useService()->getItemAllByCommodity($tblCommodity);
            $tblItemAll = Item::useService()->getItemAll();

            if (!empty( $tblItemAllByCommodity )) {
                $tblItemAll = array_udiff($tblItemAll, $tblItemAllByCommodity,
                    function (TblItem $ObjectA, TblItem $ObjectB) {

                        return $ObjectA->getId() - $ObjectB->getId();
                    }
                );
            }

            $TableCommodityContent = array();
            if (!empty( $tblCommodityItem )) {
                array_walk($tblCommodityItem, function (TblCommodityItem $tblCommodityItem) use (&$TableCommodityContent) {

                    $tblItem = $tblCommodityItem->getTblItem();

                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDisplayDescription();
                    $Item['Type'] = $tblItem->getTblItemType()->getName();
                    $Item['Option'] =
                        (new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen', '/Billing/Inventory/Commodity/Item/Remove',
                            new Minus(), array(
                                'Id' => $tblCommodityItem->getId()
                            )))->__toString();

                    array_push($TableCommodityContent, $Item);
                });
            }

            $TableItemContent = array();
            if (!empty( $tblItemAll )) {
                /** @var TblItem $tblItem */
                array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableItemContent, $tblCommodity) {

                    $Item['Name'] = $tblItem->getName();
                    $Item['Description'] = $tblItem->getDisplayDescription();
                    $Item['Type'] = $tblItem->getTblItemType()->getName();
                    $Item['Option'] =
                        (new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen', '/Billing/Inventory/Commodity/Item/Add',
                            new Plus(), array(
                                'tblCommodityId' => $tblCommodity->getId(),
                                'tblItemId'      => $tblItem->getId(),
                            )))->__toString();
                    array_push($TableItemContent, $Item);
                });
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name der aktuellen Leistung', $tblCommodity->getName(), Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            new LayoutColumn(
                                new Panel('Beschreibung', $tblCommodity->getDescription(),
                                    Panel::PANEL_TYPE_SUCCESS), 8
                            )
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title('vorhandene Artikel'),
                                ( empty( $TableCommodityContent ) ?
                                    new Warning('Keine Artikel in aktueller Leistung') :
                                    new TableData($TableCommodityContent, null,
                                        array(
                                            'Name'        => 'Name',
                                            'Description' => 'Beschreibung',
                                            'Type'        => 'Typ',
                                            'Option'      => ''
                                        )
                                    ) )
                            ), 6
                            ),
                            new LayoutColumn(array(
                                new Title('mögliche Artikel'),
                                ( empty( $TableItemContent ) ?
                                    new Warning('Keine weiteren Artikel verfügbar') :
                                    new TableData($TableItemContent, null,
                                        array(
                                            'Name'        => 'Name',
                                            'Description' => 'Beschreibung',
                                            'Type'        => 'Typ',
                                            'Option'      => ''
                                        )
                                    ) )
                            ), 6
                            )
                        )),
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendRemoveItem($Id = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Artikel Entfernen');
        $tblCommodityItem = Commodity::useService()->getCommodityItemById($Id);
        if (!$tblCommodityItem) {
            $Stage->setContent(new Warning('Verknüpfung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Inventory/Commodity', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Commodity::useService()->removeItemToCommodity($tblCommodityItem));

        return $Stage;
    }
}
