<?php
namespace SPHERE\Application\Reporting\Dynamic;

use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilter;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterMask;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Database;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\More;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Dynamic
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $DynamicFilter
     * @param null|string $DynamicFilterMask
     * @param null|array $FilterFieldName
     *
     * @return Stage
     */
    public function frontendSetupFilter($DynamicFilter = 0, $DynamicFilterMask = null, $FilterFieldName = null)
    {

        $Stage = new Stage('Flexible Auswertung', 'Filter definieren');
        $Stage->setMessage('');

        $StartViewList = array(
            new ViewPeopleGroupMember(),
            new ViewPerson(),
            new ViewAddressToPerson()
        );

        $tblDynamicFilter = Dynamic::useService()->getDynamicFilterById($DynamicFilter);

        // Add/Remove Filter Mask
        if ($DynamicFilterMask) {
            if (($DynamicFilterMask = json_decode(base64_decode($DynamicFilterMask)))) {
                if ($DynamicFilterMask->Action == 'ADD') {
                    Dynamic::useService()->insertDynamicFilterMask(
                        $tblDynamicFilter, $DynamicFilterMask->FilterPileOrder, $DynamicFilterMask->FilterClassName
                    );
                }
                if ($DynamicFilterMask->Action == 'REMOVE') {
                    Dynamic::useService()->deleteDynamicFilterMask(
                        $tblDynamicFilter, $DynamicFilterMask->FilterPileOrder
                    );
                }
            }
        }

        // Get All Filter Masks
        $tblDynamicFilterMaskAll = Dynamic::useService()->getDynamicFilterMaskAllByFilter($tblDynamicFilter);

        if ($tblDynamicFilterMaskAll) {

            // Sort MASK-List in PILE-Order (Graph-Network)
            $tblDynamicFilterMaskAll = $this->getSorter($tblDynamicFilterMaskAll)
                ->sortObjectBy(TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER);

            // Get LAST Mask
            /** @var TblDynamicFilterMask $tblDynamicFilterMaskLast */
            $tblDynamicFilterMaskLast = end($tblDynamicFilterMaskAll);
        } else {
            $tblDynamicFilterMaskLast = null;
        }

        $SelectedFilterList = array();
        $AvailableViewList = array();
        if (!$tblDynamicFilterMaskAll) {
            $AvailableViewList = $StartViewList;
        } else {

            /**
             * Prepare Selected-Filter Gui
             */
            /** @var TblDynamicFilterMask $tblDynamicFilterMask */
            foreach ($tblDynamicFilterMaskAll as $Index => $tblDynamicFilterMask) {

                $View = $tblDynamicFilterMask->getFilterClassInstance();
                $FieldList = array();
                $Object = new \ReflectionObject($View);
                $Properties = $Object->getProperties(\ReflectionProperty::IS_PROTECTED);
                /** @var \ReflectionProperty $Property */
                foreach ($Properties as $Property) {
                    $Name = $Property->getName();
                    if (!preg_match('!(_Id|_service|_tbl|_Is|Locked|MetaTable|^Id$|^Entity)!s', $Name)) {
                        $FieldList[] = new CheckBox(
                            'FilterFieldName[' . $tblDynamicFilterMask->getFilterPileOrder() . '][' . $Name . ']',
                            $View->getNameDefinition($Name), 1
                        );
                    }
                }

                $SelectedFilterList[] = new LayoutColumn(
                    (string)new Panel(
                        new PullClear(
                            (count($SelectedFilterList) < 5 ? new PullLeft(new ChevronRight()) : new PullLeft(new More()))
                            . new PullRight($View->getViewGuiName())
                        ), $FieldList
                        , Panel::PANEL_TYPE_INFO, array(
                        ($tblDynamicFilterMask == $tblDynamicFilterMaskLast
                            ? new Standard('', new Route(__NAMESPACE__ . '/Setup'), new Remove(), array(
                                'DynamicFilter' => $DynamicFilter,
                                'DynamicFilterMask' => base64_encode(json_encode(array(
                                    'FilterPileOrder' => $tblDynamicFilterMaskLast->getFilterPileOrder(),
                                    'Action' => 'REMOVE'
                                )))
                            ))
                            : '')
                    )), 2);
            }

            /**
             * Prepare Available-Filter Gui
             */
            // Get ForeignView-List
            $ForeignViewList = $tblDynamicFilterMaskLast->getFilterClassInstance()->getForeignViewList();
            // Define as AvailableView-List
            foreach ($ForeignViewList as $ForeignView) {
                // Index 1 contains FQ Class-Name
                array_push($AvailableViewList, new $ForeignView[1]);
            }
        }

        // Blank Filter-Slots
        if (count($SelectedFilterList) < 6) {
            for ($Run = count($SelectedFilterList); $Run < 6; $Run++) {
                $SelectedFilterList[] = new FormColumn(new Panel(new Small(new Muted(
                    new PullClear(
                        new PullLeft('Freier Platz für Filter')
                        . (count($SelectedFilterList) < 5 ? new PullRight(new More()) : new PullRight(new More()))
                    )
                )), array(new Paragraph(''))), 2);
            }
        }

        $FilterMaskTableList = array();
        /** @var AbstractView $AvailableView */
        foreach ($AvailableViewList as $Index => $AvailableView) {

            $FieldList = $AvailableView->getNameDefinitionList();
            $ViewList = $AvailableView->getForeignViewList();

            foreach ($FieldList as $FieldIndex => $Field) {
                $FieldList[$FieldIndex] = substr($Field, strpos($Field, ': ') + 1);
            }
            foreach ($ViewList as $ViewIndex => $View) {
                $ViewList[$ViewIndex] = $View[1]->getViewGuiName();
            }

            $FilterMaskTableList[] = array(
                'Name' => new Center($AvailableView->getViewGuiName()),
                'FieldList' => implode(', ', $FieldList),
                'ChildList' => implode(', ', $ViewList),
                'Option' =>

                    new Standard('Hinzufügen', new Route(__NAMESPACE__ . '/Setup'), new ChevronDown(), array(
                        'DynamicFilter' => $DynamicFilter,
                        'DynamicFilterMask' => base64_encode(json_encode(array(
                            'FilterPileOrder' => ($tblDynamicFilterMaskLast
                                ? $tblDynamicFilterMaskLast->getFilterPileOrder() + 1
                                : 1
                            ),
                            'FilterClassName' => $AvailableView->getViewClassName(),
                            'Action' => 'ADD'
                        )))
                    ))
            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($FilterMaskTableList, null, array(
                                'Name' => 'Suchen nach',
                                'FieldList' => 'Verfügbare Suchfelder',
                                'ChildList' => 'Möglich weitere Filter',
                                'Option' => ''
                            ))
                        )
                    )
                ), new Title('Verfügbare Filtermasken')),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Dynamic::useService()->createDynamicFilterOption(
                                    (new Form(
                                        new FormGroup(
                                            new FormRow(
                                                $SelectedFilterList
                                            )
                                        )
                                    ))->appendFormButton(
                                        new Primary('Speichern', new Save())
                                    ), $tblDynamicFilter, $FilterFieldName
                                )
                            )
                        )
                    )
                ), new Title('Filtermasken')),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $DynamicFilter
     * @param array $SearchFieldName
     * @return Stage
     */
    public function frontendRunFilter($DynamicFilter = 0, $SearchFieldName = array())
    {
        // BlackFire.io
//        $DynamicFilter = 2;

        $Stage = new Stage('Flexible Auswertung', 'Suchen');
        $Stage->setMessage('');

        $tblDynamicFilter = Dynamic::useService()->getDynamicFilterById($DynamicFilter);

        // Get All Filter Masks
        $tblDynamicFilterMaskAll = Dynamic::useService()->getDynamicFilterMaskAllByFilter($tblDynamicFilter);

        if ($tblDynamicFilterMaskAll) {

            // Sort MASK-List in PILE-Order (Graph-Network)
            $tblDynamicFilterMaskAll = $this->getSorter($tblDynamicFilterMaskAll)
                ->sortObjectBy(TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER);

        } else {
            $tblDynamicFilterMaskAll = array();
        }

        $SelectedFilterList = array();
        /**
         * Prepare Selected-Filter Gui
         */
        /** @var TblDynamicFilterMask $tblDynamicFilterMask */
        foreach ($tblDynamicFilterMaskAll as $Index => $tblDynamicFilterMask) {

            if (($tblDynamicFilterOptionList = Dynamic::useService()->getDynamicFilterOptionAllByMask(
                $tblDynamicFilterMask
            ))
            ) {
                $FieldList = array();
                $View = $tblDynamicFilterMask->getFilterClassInstance();
                foreach ($tblDynamicFilterOptionList as $tblDynamicFilterOption) {
                    $FieldList[] = new TextField(
                        'SearchFieldName[' . $tblDynamicFilterMask->getFilterPileOrder() . '][' . $tblDynamicFilterOption->getFilterFieldName() . ']',
                        $View->getNameDefinition($tblDynamicFilterOption->getFilterFieldName()),
                        $View->getNameDefinition($tblDynamicFilterOption->getFilterFieldName())
                    );
                }
//
//                $LinkList = array();
//                $ForeignViewList = $View->getForeignViewList();
//                /** @var AbstractView $ForeignView */
//                foreach ($ForeignViewList as $ForeignView) {
//                    $LinkList[] = new CheckBox(
//                        'LinkForeignView['.$ForeignView[1]->getViewClassName().']', $ForeignView[1]->getViewGuiName(), 1
//                    );
//                }

                $SelectedFilterList[] = new LayoutColumn(
                    (string)new Panel(
                        new PullClear(implode(array(
                            new PullLeft($View->getViewGuiName()),
                            new PullRight(new ChevronRight())
                        )))

                        , $FieldList, Panel::PANEL_TYPE_INFO/*, new Listing($LinkList)*/)
                    , 2);
            }
        }

        // Search
        $Filter = $SearchFieldName;

        if (!empty($Filter)) {
            ksort($Filter);
            array_walk($Filter, function (&$Input) {

                array_walk($Input, function (&$String) {

                    if (!empty($String)) {
                        $String = explode(' ', $String);
                    } else {
                        $String = false;
                    }
                });
                $Input = array_filter($Input);
            });
            $Filter = array_values($Filter);
        }

        if (true || !empty($Filter)) {
            /**
             * Prepare Pile-Filter Structure
             */
            $Pile = new Pile();

            /** @var AbstractView $Last */
            $Last = null;
            /** @var AbstractView $Next */
            $Next = null;
            /** @var TblDynamicFilterMask $tblDynamicFilterMask */
            foreach ($tblDynamicFilterMaskAll as $Index => $tblDynamicFilterMask) {
                $Current = $tblDynamicFilterMask->getFilterClassInstance();
                if (count($tblDynamicFilterMaskAll) > $Index + 1) {
                    $Next = $tblDynamicFilterMaskAll[$Index + 1]->getFilterClassInstance();
                } else {
                    $Next = null;
                }

                $Pile->addPile($Current->getViewService(), $Current,
                    ($Last ? $Last->getForeignLinkPropertyChild($Current) : null),
                    ($Next ? $Current->getForeignLinkPropertyParent($Next) : null)
                );
                $Last = $Current;
            }

            $Result = $Pile->searchPile($Filter, 10);

            $Table = array();
            if ($Result) {
                array_walk($Result, function ($Row) use (&$Table, $Filter) {

                    $RowSet = array();
                    $Index = 1;
                    array_walk($Row, function (AbstractView $Element) use (&$RowSet, $Filter, &$Index) {

                        $RowData = $Element->__toView();
                        $RowKeys = array_keys($RowData);

                        if (array_intersect(array_keys($RowSet), $RowKeys)) {

                            array_walk($RowKeys, function (&$K) use ($Index) {

                                $K = $K.' '.new Small(new Label(new Info($Index), Label::LABEL_TYPE_NORMAL));
                            });
                            $Index++;
                        }

                        $RowSet = array_merge($RowSet, array_combine($RowKeys, array_values($RowData)));

//                        Debugger::screenDump( get_class($Element), $Element->getForeignViewList(), $Element->__toView() );
//                        $RowSet = array_merge($RowSet, $Element->__toView());
                    });
                    array_push($Table, $RowSet);
                });
            }
            $Timeout = $Pile->isTimeout();

//            if( !$Timeout ) {
//                Debugger::screenDump( $Table );
//                /** @var PhpExcel $Document */
//                $Document = Document::getDocument('Export.xlsx');
//                foreach( $Table as $RowIndex => $Row ) {
//                    $ColumnIndex = 0;
//                    foreach( $Row as $Value ) {
//                        $ColumnIndex ++;
//                        $Document->setValue( $Document->getCell($ColumnIndex, $RowIndex), $Value );
//                    }
//                }
//
//            }
        } else {
            $Table = array();
            $Timeout = false;
        }


        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                (new Form(
                                    new FormGroup(
                                        new FormRow(
                                            $SelectedFilterList
                                        )
                                    )
                                ))->appendFormButton(
                                    new Primary('Suchen', new Search())
                                )
                            )
                        )
                    )
                ), new Title('Suche')),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            ($Timeout
                                ? new Danger('Die Suche konnte auf Grund der Datenmenge nicht abgeschlossen werden. Bitte geben Sie weitere Filter an, um die Datenmenge einzuschränken')
                                : new Success('Die Suche wurde vollständig durchgeführt.')),
                            new TableData($Table)
                        ))
                    )
                ), new Title('Ergebnis')),
            ))
        );


        return $Stage;
    }

    /**
     * @param string $FilterName
     * @param int $IsPublic
     *
     * @return Stage
     */
    public function frontendCreateFilter($FilterName = null, $IsPublic = 0)
    {

        $Stage = new Stage('Flexible Auswertung', 'Filter erstellen');
        $Stage->setMessage('');

        $tblAccount = Account::useService()->getAccountBySession();

        $tblDynamicFilterListOwner = Dynamic::useService()->getDynamicFilterAll($tblAccount);
        if (!$tblDynamicFilterListOwner) {
            $tblDynamicFilterListOwner = array();
        }

        $tblDynamicFilterListPublic = Dynamic::useService()->getDynamicFilterAllByIsPublic();
        if (!$tblDynamicFilterListPublic) {
            $tblDynamicFilterListPublic = array();
        }

        $tblDynamicFilterList = array_unique(
            array_merge($tblDynamicFilterListPublic, $tblDynamicFilterListOwner)
        );

        $DynamicFilterList = array();
        array_walk($tblDynamicFilterList, function (TblDynamicFilter $tblDynamicFilter)
        use (&$DynamicFilterList, $tblAccount) {

            $Option = '';
            if ($tblAccount == $tblDynamicFilter->getServiceTblAccount()) {
                $Option = (new LinkGroup())
                    ->addLink(new Standard('', '', new Edit(), array(), 'Bearbeiten'))
                    ->addLink(new Standard('', '', new Remove(), array(), 'Löschen'));
                $Option .= (new LinkGroup())
                    ->addLink(new Standard('', new Route(__NAMESPACE__ . '/Setup'), new Setup(),
                        array('DynamicFilter' => $tblDynamicFilter->getId()), 'Einstellungen'
                    ));
            }
            $Option .= (new LinkGroup())->addLink(new Standard('', new Route(__NAMESPACE__ . '/Filter'), new View(),
                array('DynamicFilter' => $tblDynamicFilter->getId()),
                'Anzeigen / Verwenden'
            ));

            array_push($DynamicFilterList, array(
                'Option' => $Option,
                TblDynamicFilter::PROPERTY_FILTER_NAME => $tblDynamicFilter->getFilterName(),
                TblDynamicFilter::PROPERTY_IS_PUBLIC => new Center(
                    ($tblAccount == $tblDynamicFilter->getServiceTblAccount()
                        ? ($tblDynamicFilter->isPublic()
                            ? new Person() . new Container(new Label('Sichtbar', Label::LABEL_TYPE_WARNING))
                            : new Person() . new Container(new Label('Privat', Label::LABEL_TYPE_SUCCESS))
                        )
                        : new PersonGroup() . new Container(new Label('Geteilt', Label::LABEL_TYPE_INFO))
                    )
                )
            ));
        });

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($DynamicFilterList, null, array(
                                'IsPublic' => 'Sichtbarkeit',
                                'FilterName' => 'Name der Auswertung',
                                'Option' => ''
                            ), array(
                                "columnDefs" => array(
                                    array("width" => "5%", "targets" => array(0)),
                                    array("width" => "15%", "targets" => array(2))
                                )
                            ))
                        )
                    ), new Title(new Database() . ' Verfügbare Auswertungen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Dynamic::useService()->createDynamicFilter(
                                    $this->formCreateFilter()->appendFormButton(new Primary('Speichern', new Save())),
                                    $FilterName, $IsPublic
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Neue Auswertung anlegen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formCreateFilter()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Filter definieren', array(
                            new TextField('FilterName', 'Name der Auswertung', 'Name der Auswertung', new Nameplate()),
                            new CheckBox('IsPublic', 'Auswertung für Alle sichtbar machen', 1),
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                ))
            )
        );
    }
}
