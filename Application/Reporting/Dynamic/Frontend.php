<?php
namespace SPHERE\Application\Reporting\Dynamic;

use SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilter;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterMask;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterOption;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Database;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\More;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\Task;
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
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
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

    private $FilterPackAll = array('Adresse-Personen', 'Person-Adressen', 'Person-Personenbeziehung-Person', 'Person-Sorgeberechtigte-Adressen',
        'Firmen und Beziehungen', 'Schüler-Befreiung', 'Schüler-Einverständnis', 'Schüler-Fehltage', 'Schüler-Förderbedarf-Antrag',
        'Schüler-Förderbedarf-Schwerpunkte', 'Schüler-Förderbedarf-Teilstörung', 'Schüler-Krankenakte', 'Schüler-Schließfach',
        'Schüler-Taufe', 'Schüler-Transfer', 'Schüler-Transport');

    /**
     * @param string $FilterName
     * @param int    $IsPublic
     *
     * @return Stage
     */
    public function frontendCreateFilter($FilterName = null, $IsPublic = 0)
    {

        $tblAccount = Account::useService()->getAccountBySession();
//        if ($tblAccount) {
//            if (!Dynamic::useService()->getDynamicFilterAllByAccount($tblAccount)) {
////                Dynamic::useService()->createStandardFilter($tblAccount);
////                $StandardButton = new Standard('Standard-Auswertungen', '/Reporting/Dynamic/Standard', new Plus());
//            }
//        }

        $Stage = new Stage('Flexible Auswertung', 'Übersicht');
        $Stage->setMessage('');
        $Stage->addButton(new Standard('Auswertung Vorlagen', '/Reporting/Dynamic/Standard', new Task(), array(), 'Hinzufügen von vorbereiteten Auswertungen'));

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
            $Owner = '';
            $Option = '';
            $MatchFilterName = '';
            if ($tblAccount == $tblDynamicFilter->getServiceTblAccount()) {
                // markieren der eigenen Vorlagen
                if (in_array($tblDynamicFilter->getFilterName(), $this->FilterPackAll)) {
                    $MatchFilterName = new Center(new InfoText(new Enable()));
                }

                $Person = array();
                if (( $tblPersonAccountList = Account::useService()->getUserAllByAccount($tblAccount) )) {
                    foreach ($tblPersonAccountList as $tblPersonAccount) {
                        $tblPersonAcc = $tblPersonAccount->getServiceTblPerson();
                        $Person[] = $tblPersonAcc->getFirstName().' '.$tblPersonAcc->getLastName();
                    }
                    if (!empty( $Person )) {
                        $Person = implode(', ', $Person);
                    }
                }
                if (!empty( $Person )) {
                    $Owner = $tblAccount->getUsername().' '.new Small('('.$Person.')');
                } else {
                    $Owner = $tblAccount->getUsername();
                }
//                $Owner = $tblAccount->getUsername();
                $Option = ( new LinkGroup() )
                    ->addLink(new Standard('', '/Reporting/Dynamic/Update', new Edit(), array('Id' => $tblDynamicFilter->getId()), 'Bearbeiten'))
                    ->addLink(new Standard('', '/Reporting/Dynamic/Remove', new Remove(), array('Id' => $tblDynamicFilter->getId()), 'Löschen'));
                $Option .= ( new LinkGroup() )
                    ->addLink(new Standard('', new Route(__NAMESPACE__.'/Setup'), new Setup(),
                        array('DynamicFilter' => $tblDynamicFilter->getId()), 'Einstellungen'
                    ));
            } else {
                $DynamicAccount = $tblDynamicFilter->getServiceTblAccount();
                if ($DynamicAccount) {
                    $Person = array();
                    if (( $tblPersonAccountList = Account::useService()->getUserAllByAccount($DynamicAccount) )) {
                        foreach ($tblPersonAccountList as $tblPersonAccount) {
                            $tblPersonAcc = $tblPersonAccount->getServiceTblPerson();
                            $Person[] = $tblPersonAcc->getFirstName().' '.$tblPersonAcc->getLastName();
                        }
                        if (!empty( $Person )) {
                            $Person = implode(', ', $Person);
                        }
                    }
                    if (!empty( $Person )) {
                        $Owner = new InfoText($DynamicAccount->getUsername().' '.new Small('('.$Person.')'));
                    } else {
                        $Owner = new InfoText($DynamicAccount->getUsername());
                    }
                }
            }
            $Option .= ( new LinkGroup() )->addLink(new Standard('', new Route(__NAMESPACE__.'/Filter'), new View(),
                array('DynamicFilter' => $tblDynamicFilter->getId()),
                'Anzeigen / Verwenden'
            ));

            array_push($DynamicFilterList, array(
                'Standard'                             => $MatchFilterName,
                'Option'                               => $Option,
                TblDynamicFilter::PROPERTY_FILTER_NAME => $tblDynamicFilter->getFilterName(),
                'Owner'                                => $Owner,
                TblDynamicFilter::PROPERTY_IS_PUBLIC   => new Center(
                    ( $tblAccount == $tblDynamicFilter->getServiceTblAccount()
                        ? ( $tblDynamicFilter->isPublic()
                            ? new Person().new Container(new Label('Sichtbar', Label::LABEL_TYPE_WARNING))
                            : new Person().new Container(new Label('Privat', Label::LABEL_TYPE_SUCCESS))
                        )
                        : new PersonGroup().new Container(new Label('Geteilt', Label::LABEL_TYPE_INFO))
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
                                'IsPublic'   => 'Sichtbarkeit',
                                'Standard'   => 'Vorlage',
                                'FilterName' => 'Name der Auswertung',
                                'Owner'      => 'Account (Person)',
                                'Option'     => ''
                            ), array(
                                'order'      => array(
                                    array('0', 'desc'),
                                    array('1', 'asc')),
                                "columnDefs" => array(
                                    array("width" => "5%", "targets" => array(0)),
                                    array("width" => "3%", "targets" => array(1)),
                                    array("width" => "25%", "targets" => array(3)),
                                    array("width" => "15%", "targets" => array(4))
                                )
                            ))
                        )
                    ), new Title(new Database().' Verfügbare Auswertungen')
                ),
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Well(
                                    Dynamic::useService()->createDynamicFilter(
                                        $this->formCreateFilter()->appendFormButton(new Primary('Speichern', new Save())),
                                        $FilterName, $IsPublic
                                    )
                                )
                                , 12)
                        )
                    ), new Title(new PlusSign().' Neue Auswertung anlegen')
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

    /**
     * @param null $Data
     * @param null $Reset
     *
     * @return Stage
     */
    public function frontendSetupStandard($Data = null, $Reset = null)
    {

        $Stage = new Stage('Standard-Auswertungen');
        $Stage->setMessage(new Info('Standard-Auswertungen werden über den Namen Definiert. Änderung des Namens 
                                     lösen die hierrüber erstellten Auswertung aus dem Standard.'));
        $Stage->addButton(new Standard('Zurück', '/Reporting/Dynamic', new ChevronLeft()));

        $tblAccount = Account::useService()->getAccountBySession();

        $Form = $this->formCreateStandard($tblAccount);

        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Dynamic::useService()->createStandardFilter($Form, $tblAccount, $Data, $Reset), Panel::PANEL_TYPE_INFO
                        ))
                    )
                )
            )
        );


        return $Stage;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return Form
     */
    private function formCreateStandard(TblAccount $tblAccount)
    {

        // possible Filter
        $DataAll = $this->FilterPackAll;
        $TableListLeft = array();
        $TableListRight = array();
        foreach ($DataAll as $Key => $Name) {
            if (!Dynamic::useService()->getDynamicFilterAllByName($Name, $tblAccount)) {
                $Item['CheckBox'] = new CheckBox('Data['.$Key.']', ' ', $Name);
                $Item['Name'] = $Name;
                array_push($TableListLeft, $Item);
                unset( $Item );
            } else {
                $Item['CheckBox'] = new CheckBox('Reset['.$Key.']', ' ', $Name);
                $Item['Name'] = $Name;
                array_push($TableListRight, $Item);
                unset( $Item );
            }
        }
//        $Global = $this->getGlobal();
//        if($Data === null){
//            foreach($DataAll as $Key =>$Name){
//                if(Dynamic::useService()->getDynamicFilterAllByName($Name, $tblAccount)){
//                    $Global->POST['Data'][$Key] = $Name;
//                }
//            }
//            Debugger::screenDump($Global->POST);
//            $Global->savePost();
//        }
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Neue Standard-Auswertung',
                            new TableData($TableListLeft, null,
//                            new \SPHERE\Common\Frontend\Table\Repository\Title(new Plus().' Hinzufügen', 'von Standard-Auswertungen'),
                                array('CheckBox' => 'Erstellen',
                                      'Name'     => 'Name'),
                                array('order'          => array(array(1, 'asc')),
                                      "columnDefs"     => array(
                                          array("width" => "10%", "targets" => array(0)),
                                      ),
                                      "paging"         => false, // Deaktiviert Blättern
                                      "iDisplayLength" => -1,    // Alle Einträge zeigen
                                      "searching"      => false, // Deaktiviert Suche
                                      "info"           => false  // Deaktiviert Such-Info)
                                )
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Benutzte Standard-Auswertung',
                            new TableData($TableListRight, null,
//                            new \SPHERE\Common\Frontend\Table\Repository\Title(new Plus().' Resetten', 'von Standard-Auswertungen'),
                                array('CheckBox' => 'Zurücksetzen',
                                      'Name'     => 'Name'),
                                array('order'          => array(array(1, 'asc')),
                                      "columnDefs"     => array(
                                          array("width" => "10%", "targets" => array(0)),
                                      ),
                                      "paging"         => false, // Deaktiviert Blättern
                                      "iDisplayLength" => -1,    // Alle Einträge zeigen
                                      "searching"      => false, // Deaktiviert Suche
                                      "info"           => false  // Deaktiviert Such-Info)
                                )
                            ), Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            )
        );
    }

    /**
     * @param int         $DynamicFilter
     * @param null|string $DynamicFilterMask
     * @param null|array  $FilterFieldName
     *
     * @return Stage
     */
    public function frontendSetupFilter($DynamicFilter = 0, $DynamicFilterMask = null, $FilterFieldName = null)
    {

        $Stage = new Stage('Flexible Auswertung', 'Filter definieren');
        $Stage->setMessage('');

        $StartViewList = array(
            new ViewPeopleGroupMember()
//            ,new ViewPerson()
//            ,new ViewAddressToPerson()
        , new ViewCompanyGroupMember()
        , new ViewYear()
//        , new ViewItem()
//        , new ViewSubject()
        );

        $tblDynamicFilter = Dynamic::useService()->getDynamicFilterById($DynamicFilter);
        $Stage->addButton(new Standard('Zurück', '/Reporting/Dynamic', new ChevronLeft()));

        // Add/Remove Filter Mask
        if ($DynamicFilterMask) {
            if (( $DynamicFilterMask = json_decode(base64_decode($DynamicFilterMask)) )) {
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

                $tblDynamicFilterOptionAll = Dynamic::useService()->getDynamicFilterOptionAllByMask($tblDynamicFilterMask);
                $View = $tblDynamicFilterMask->getFilterClassInstance();
                $FieldList = array();
                $Object = new \ReflectionObject($View);
                $Properties = $Object->getProperties(\ReflectionProperty::IS_PROTECTED);
                /** @var \ReflectionProperty $Property */
                foreach ($Properties as $Property) {
                    $Name = $Property->getName();
                    if (!preg_match(AbstractView::DISABLE_PATTERN, $Name)
                        && !$View->getDisableDefinition($Name)
                    ) {
                        $Enabled = false;
                        if ($tblDynamicFilterOptionAll) {
                            $Enabled = array_filter($tblDynamicFilterOptionAll, function (TblDynamicFilterOption $tblDynamicFilterOption) use ($Name) {
                                return $tblDynamicFilterOption->getFilterFieldName() == $Name;
                            });
                            if (!empty( $Enabled )) {
                                $Enabled = true;
                            }
                        }
                        $CheckBox = new CheckBox(
                            'FilterFieldName['.$tblDynamicFilterMask->getFilterPileOrder().']['.$Name.']',
                            $View->getNameDefinition($Name), 1
                        );
                        if ($Enabled) {
                            $CheckBox->setDefaultValue(1, true);
                        }
                        $FieldList[] = $CheckBox;
                    }
                }

                $SelectedFilterList[] = new LayoutColumn(
                    (string)new Panel(
                        new PullClear(
                            ( count($SelectedFilterList) < 5 ? new PullLeft(new ChevronRight()) : new PullLeft(new More()) )
                            .new PullRight($View->getViewGuiName())
                        ), $FieldList
                        , Panel::PANEL_TYPE_INFO, array(
                        ( $tblDynamicFilterMask == $tblDynamicFilterMaskLast
                            ? new Standard('', new Route(__NAMESPACE__.'/Setup'), new Remove(), array(
                                'DynamicFilter'     => $DynamicFilter,
                                'DynamicFilterMask' => base64_encode(json_encode(array(
                                    'FilterPileOrder' => $tblDynamicFilterMaskLast->getFilterPileOrder(),
                                    'Action'          => 'REMOVE'
                                )))
                            ))
                            : '' )
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
                        .( count($SelectedFilterList) < 5 ? new PullRight(new More()) : new PullRight(new More()) )
                    )
                )), array(new Paragraph(''))), 2);
            }
        }

        if (count($tblDynamicFilterMaskAll) < 6) {
            $FilterMaskTableList = array();
            /** @var AbstractView $AvailableView */
            foreach ($AvailableViewList as $Index => $AvailableView) {

                $FieldList = $AvailableView->getNameDefinitionList();
                $ViewList = $AvailableView->getForeignViewList();

                foreach ($FieldList as $FieldIndex => $Field) {
                    $FieldList[$FieldIndex] = substr($Field, strpos($Field, ': ') + 1);
                }
                /**
                 * @var string $ViewIndex
                 * @var AbstractView[] $View
                 */
                foreach ($ViewList as $ViewIndex => $View) {
                    $ViewList[$ViewIndex] = $View[1]->getViewGuiName();
                }

                $FilterMaskTableList[] = array(
                    'Name'      => new Center($AvailableView->getViewGuiName()),
                    'FieldList' => implode(', ', $FieldList),
                    'ChildList' => implode(', ', $ViewList),
                    'Option'    =>
                        new Standard('Hinzufügen', new Route(__NAMESPACE__.'/Setup'), new ChevronDown(), array(
                            'DynamicFilter'     => $DynamicFilter,
                            'DynamicFilterMask' => base64_encode(json_encode(array(
                                'FilterPileOrder' => ( $tblDynamicFilterMaskLast
                                    ? $tblDynamicFilterMaskLast->getFilterPileOrder() + 1
                                    : 1
                                ),
                                'FilterClassName' => $AvailableView->getViewClassName(),
                                'Action'          => 'ADD'
                            )))
                        ))
                );
            }
        } else {
            $FilterMaskTableList = false;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ( $FilterMaskTableList
                                ? new TableData($FilterMaskTableList, null, array(
                                    'Name'      => 'Suchen nach',
                                    'FieldList' => 'Verfügbare Suchfelder',
                                    'ChildList' => 'Möglich weitere Filter',
                                    'Option'    => ''
                                ))
                                : new Warning('Es können keine weiteren Filter hinzugefügt werden.') )
                        )
                    )
                ), new Title('Verfügbare Filtermasken für '.new Bold(new InfoText($tblDynamicFilter->getFilterName())))),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Dynamic::useService()->createDynamicFilterOption(
                                    ( new Form(
                                        new FormGroup(
                                            new FormRow(
                                                $SelectedFilterList
                                            )
                                        )
                                    ) )->appendFormButton(
                                        new Primary('Speichern', new Save())
                                    ), $tblDynamicFilter, $FilterFieldName
                                )
                            )
                        )
                    )
                ), new Title('Filtermasken für '.new Bold(new InfoText($tblDynamicFilter->getFilterName())))),
            ))
        );

        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $FilterName
     * @param null $IsPublic
     *
     * @return Stage|string
     */
    public function frontendUpdateFilter($Id, $FilterName = null, $IsPublic = null)
    {
        $Stage = new Stage('Flexible Auswertung', 'Bearbeiten');

        $tblDynamicFilter = $Id === null ? false : Dynamic::useService()->getDynamicFilterById($Id);
        if (!$tblDynamicFilter) {
            $Stage->setContent(new Warning('Auswertung konnte nicht aufgerufen werden.'));
            return $Stage.new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/Dynamic', new ChevronLeft()));

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Data'] )) {
            $Global->POST['FilterName'] = $tblDynamicFilter->getFilterName();
            $Global->POST['IsPublic'] = $tblDynamicFilter->isPublic();
            $Global->savePost();
        }

        $Form = $this->formCreateFilter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Dynamic::useService()->changeDynamicFilter(
                                    $Form, $tblDynamicFilter, $FilterName, $IsPublic
                                ))
                            , 12)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendRemoveFilter($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Flexible Auswertung', 'Löschen');
        $tblDynamicFilter = $Id === null ? false : Dynamic::useService()->getDynamicFilterById($Id);
        if (!$tblDynamicFilter) {
            $Stage->setContent(new Warning('Auswertung nicht gefunden'));
            return $Stage.new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Dynamic', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Flexible Auswertung',
                        'Auswertung mit dem Namen "<b>'.$tblDynamicFilter->getFilterName().'</b>" und der Sichtbarkeit '.
                        ( $tblDynamicFilter->isPublic()
                            ? new Label('Sichtbar', Label::LABEL_TYPE_WARNING)
                            : new Label('Privat', Label::LABEL_TYPE_SUCCESS) )
                        .' wirklich löschen?',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Reporting/Dynamic/Remove', new Ok(),
                            array('Id' => $Id, 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Reporting/Dynamic', new Disable()
                        )
                    )
                    , 6))))
            );
        } else {

            // Destroy DynamicFilter
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        Dynamic::useService()->destroyDynamicFilter($tblDynamicFilter)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param int   $DynamicFilter
     * @param array $SearchFieldName
     *
     * @return Stage
     */
    public function frontendRunFilter($DynamicFilter = 0, $SearchFieldName = array())
    {
        // BlackFire.io
//        $DynamicFilter = 2;

        $Stage = new Stage('Flexible Auswertung', 'Suchen');
        $Stage->setMessage('');

        $tblDynamicFilter = Dynamic::useService()->getDynamicFilterById($DynamicFilter);
        $Stage->addButton(new Standard('Zurück', '/Reporting/Dynamic', new ChevronLeft()));

        // Get All Filter Masks
        $tblDynamicFilterMaskAll = Dynamic::useService()->getDynamicFilterMaskAllByFilter($tblDynamicFilter);

        if ($tblDynamicFilterMaskAll) {

            // Sort MASK-List in PILE-Order (Graph-Network)
            $tblDynamicFilterMaskAll = $this->getSorter($tblDynamicFilterMaskAll)
                ->sortObjectBy(TblDynamicFilterMask::PROPERTY_FILTER_PILE_ORDER);

        } else {
            $tblDynamicFilterMaskAll = array();
            $Warning = new Warning('Es wurden keine Filtermaske für dieser Auswertung eingestellt');
        }

        $SelectedFilterList = array();
        /**
         * Prepare Selected-Filter Gui
         */
        /** @var TblDynamicFilterMask $tblDynamicFilterMask */
        foreach ($tblDynamicFilterMaskAll as $Index => $tblDynamicFilterMask) {

            if (( $tblDynamicFilterOptionList = Dynamic::useService()->getDynamicFilterOptionAllByMask(
                $tblDynamicFilterMask
            ) )
            ) {
                $FieldList = array();
                $View = $tblDynamicFilterMask->getFilterClassInstance();
                foreach ($tblDynamicFilterOptionList as $tblDynamicFilterOption) {
                    if (!$View->getDisableDefinition($tblDynamicFilterOption->getFilterFieldName())) {

                        if (preg_match('!_Is[A-Z]!s', $tblDynamicFilterOption->getFilterFieldName())) {
                            $FieldList[] = new SelectBox(
                                'SearchFieldName['.$tblDynamicFilterMask->getFilterPileOrder().']['.$tblDynamicFilterOption->getFilterFieldName().']',
                                $View->getNameDefinition($tblDynamicFilterOption->getFilterFieldName()),
                                array(
                                    0 => '',
                                    1 => 'Ja',
                                    2 => 'Nein',
                                )
                            );
                        } else {
                            $FieldList[] = new TextField(
                                'SearchFieldName['.$tblDynamicFilterMask->getFilterPileOrder().']['.$tblDynamicFilterOption->getFilterFieldName().']',
                                $View->getNameDefinition($tblDynamicFilterOption->getFilterFieldName()),
                                $View->getNameDefinition($tblDynamicFilterOption->getFilterFieldName())
                            );
                        }
                    }
                }
//
//                $LinkList = array('Zusätzliche Ergebnis-Daten');
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

                        , $FieldList, Panel::PANEL_TYPE_INFO/*, new Panel( array_shift( $LinkList ), $LinkList, Panel::PANEL_TYPE_WARNING)*/)
                    , 2);
            } else {
                $View = $tblDynamicFilterMask->getFilterClassInstance();
                $SelectedFilterList[] = new LayoutColumn(
                    (string)new Panel(
                        new PullClear(implode(array(
                            new PullLeft($View->getViewGuiName()),
                            new PullRight(new ChevronRight())
                        )))

                        , array(
                        new HiddenField(
                            'SearchFieldName['.$tblDynamicFilterMask->getFilterPileOrder().'][]'
                        ).
                        new Warning('Keine Eigenschaften zur Filterung gewählt')
                    ), Panel::PANEL_TYPE_INFO/*, new Panel( array_shift( $LinkList ), $LinkList, Panel::PANEL_TYPE_WARNING)*/)
                    , 2);
            }
        }

        // Search
        $Filter = $SearchFieldName;

        if (!empty( $Filter )) {
            ksort($Filter);
            array_walk($Filter, function (&$Input) {

                array_walk($Input, function (&$String) {

                    if (strlen($String)) {
                        $String = explode(' ', $String);
                    } else {
                        $String = false;
                    }
                });
                $Input = array_filter($Input, function ($Value) {
                    if (is_array($Value) || strlen($Value)) {
                        return true;
                    }
                    return false;
                });
            });
            $Filter = array_values($Filter);
        }

        if (!empty( $Filter )) {
            /**
             * Prepare Pile-Filter Structure
             */
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);

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
                    ( $Last ? $Last->getForeignLinkPropertyChild($Current) : null ),
                    ( $Next ? $Current->getForeignLinkPropertyParent($Next) : null )
                );
                $Last = $Current;
            }

            $Result = $Pile->searchPile($Filter, 10);

            $Table = array();
            if ($Result) {
                array_walk($Result, function ($Row) use (&$Table) {

                    $RowSet = array();
                    $Index = 1;
                    array_walk($Row, function (AbstractView $Element) use (&$RowSet, &$Index) {

                        $RowData = $Element->__toView();
                        $RowKeys = array_keys($RowData);

                        if (array_intersect(array_keys($RowSet), $RowKeys)) {

                            array_walk($RowKeys, function (&$K) use ($Index) {

                                $K = $K.' '.new Small(new Label(new InfoText($Index), Label::LABEL_TYPE_NORMAL));
                            });
                            $Index++;
                        }
                        $RowSet = array_merge($RowSet, array_combine($RowKeys, array_values($RowData)));
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

        $TableData = new TableData($Table, null, array(),
            array(
                'responsive' => false,
                'ExtensionColVisibility' => array(
                    'Enabled' => true
                ),
                'ExtensionDownloadExcel' => array(
                    'Enabled' => true
                )
            )
        );

//        // Export Flag im Header
//        $ExportCounter = count(current($Table));
//        $ExportSwitchRow = array();
//        for( $Run = 0; $Run < $ExportCounter; $Run++ ) {
//            $ExportSwitchRow[] = new TableColumn(
//                (new SelectBox('Export['.$Run.']','',array(1=>'Exportieren',2=>'Ignorieren')))
//            );
//        }
//
//        $TableData->prependHead(
//            new TableHead(
//                (new TableRow( $ExportSwitchRow ))
//            )
//        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ( isset( $Warning ) ? $Warning : '' )
                        ),
                        new LayoutColumn(
                            new Well(
                                ( new Form(
                                    new FormGroup(
                                        new FormRow(
                                            $SelectedFilterList
                                        )
                                    )
                                ) )->appendFormButton(
                                    new Primary('Suchen', new Search())
                                )
                            )
                        )
                    ))
                ), new Title('Suche '.( $tblDynamicFilter->getFilterName() ? new InfoText(new Bold($tblDynamicFilter->getFilterName())) : '' ))),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            ( $Timeout
                                ? new Danger('Die Suche konnte auf Grund der Datenmenge nicht abgeschlossen werden. Bitte geben Sie weitere Filter an, um die Datenmenge einzuschränken')
                                :
                                ( empty( $Filter ) ? ''
                                    : new Success('Die Suche wurde vollständig durchgeführt.') )
                            ),
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            $TableData
                                        )
                                    )
                                )
//                            , new Primary('Download')
                            )
                        ))
                    )
                ), new Title('Ergebnis '.( $tblDynamicFilter->getFilterName() ? new InfoText(new Bold($tblDynamicFilter->getFilterName())) : '' ))),
            ))
        );

        return $Stage;
    }
}
