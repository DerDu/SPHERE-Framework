<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Api\Billing\Inventory\ApiDocument;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Document\Document;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{
    const FILTER_CLASS = 1;
    const FILTER_GROUP = 2;
    const FILTER_PERSON = 3;

    const MAX_PDF_PAGES = 30;

    public function frontendBalance()
    {

        $Stage = new Stage('Dashboard', 'Druck');
        return $Stage;
    }

    public function frontendBalanceExcel($Balance = array())
    {

        $Stage = new Stage('Bescheinigung', 'Serienbrief');

        if(!isset($Balance['Year'])){
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['From'])){
            $_POST['Balance']['From'] = '1';
        }
        if(!isset($Balance['To'])){
            $_POST['Balance']['To'] = '12';
        }
        if(!isset($Balance['Filter'])){
            $_POST['Balance']['Filter'] = self::FILTER_CLASS;
        }
        // required from Select2
        if(!isset($Balance['BasketType'])){
            $_POST['Balance']['BasketType'] = '-1';
        }
        // Standard Download
        $Download = (new PrimaryLink('Herunterladen', '', new Download()))->setDisabled();
        $tblPersonList = array();

        $tblItemList = array();
        if(isset($Balance['ItemList'])){
            foreach($Balance['ItemList'] as $ItemId){
                $tblItemList[] = Item::useService()->getItemById($ItemId);
            }
        }

        $tableContent = array();
        $tblPerson = false;
        $error = false;
        $message = null;
        if(!empty($Balance)){
            if (isset($Balance['Search'])) {
                if (!($tblPerson = Person::useService()->getPersonById(isset($Balance['PersonId']) ? $Balance['PersonId'] : 0))) {
                    $message = new Warning('Bitte wählen Sie eine Person aus', new Exclamation());
                    $error = true;
                }
            }
        }

//        if(empty($tblItemList)){
//            $message = new Warning('Bitte wählen Sie mindestens eine Beitragsart aus', new Exclamation());
//            $error = true;
//        }

        $filterForm = $this->getFilterForm($Balance, $message, '1');
        $filterBlock = ApiDocument::receiverBlock($filterForm, 'changeFilter');

        if(!empty($Balance) && !empty($tblItemList)){
            $tblDivision = false;
            $tblGroup = false;
            if (isset($Balance['Division'])) {
                if (!($tblDivision = Division::useService()->getDivisionById($Balance['Division']))) {
                    $filterForm->setError('Balance[Division]', 'Bitte wählen Sie eine Klasse aus');
                    $error = true;
                }
            }
            if (isset($Balance['Group'])) {
                if (!($tblGroup = Group::useService()->getGroupById($Balance['Group']))) {
                    $filterForm->setError('Balance[Group]', 'Bitte wählen Sie eine Gruppe aus');
                    $error = true;
                }
            }

            if($tblDivision){
                // Pesronenliste aus der Klasse:
                $tblPersonList = Division::useService()->getPersonAllByDivisionList(array($tblDivision));
            } elseif($tblGroup) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            } elseif($tblPerson){
                $tblPersonList = array($tblPerson);
            }

            // Suche ohne Filterung erstmal deaktiviert
//            } else {
//                // Personenliste, wenn keine Klasse gewählt wurde:
//                $tblPersonList = Balance::useService()->getPersonListByInvoiceTime($Balance['Year'],
//                    $Balance['From'], $Balance['To']);
//            }

            $ItemIdString = '';
            /** @var TblItem $tblItem */
            foreach($tblItemList as $tblItemDownload){
                if($ItemIdString === ''){
                    $ItemIdString = $tblItemDownload->getId();
                } else {
                    $ItemIdString .= ','.$tblItemDownload->getId();
                }
            }
            if($tblPersonList){
                $PriceList = array();
                foreach($tblPersonList as $tblPerson){
                    /** @var TblItem $tblItem */
                    foreach($tblItemList as $tblItem){
                        if(isset($Balance['BasketType'])){
                            $BasketTypeId = $Balance['BasketType'];
                        } else {
                            $tblBasketType = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
                            $BasketTypeId = $tblBasketType->getId();
                        }
                        // Rechnungen zusammengefasst (je Beitragsart)
                        $PriceList = Balance::useService()->getPriceListByItemAndPerson($tblItem, $Balance['Year'],
                            $Balance['From'], $Balance['To'], $tblPerson, $BasketTypeId, $PriceList);
                    }
                }
                $PriceList = Balance::useService()->getSummaryByItemPrice($PriceList);
                $tableContent = Balance::useService()->getTableContentByItemPriceList($PriceList);
                if($tblDivision){
                    $Download = new PrimaryLink('Herunterladen', '/Api/Billing/Balance/Balance/Print/Download',
                        new Download(), array(
                            'ItemIdString' => $ItemIdString,
                            'Year'       => $Balance['Year'],
                            'From'       => $Balance['From'],
                            'To'         => $Balance['To'],
                            'DivisionId' => $Balance['Division'],
                            'BasketTypeId' => $Balance['BasketType'],
                        ));
                } elseif($tblGroup) {
                    $Download = new PrimaryLink('Herunterladen', '/Api/Billing/Balance/Balance/Print/Download',
                        new Download(), array(
                            'ItemIdString' => $ItemIdString,
                            'Year'       => $Balance['Year'],
                            'From'       => $Balance['From'],
                            'To'         => $Balance['To'],
                            'GroupId' => $Balance['Group'],
                            'BasketTypeId' => $Balance['BasketType'],
                        ));
                } elseif($tblPerson) {
                    $Download = new PrimaryLink('Herunterladen', '/Api/Billing/Balance/Balance/Print/Download',
                        new Download(), array(
                            'ItemIdString' => $ItemIdString,
                            'Year'       => $Balance['Year'],
                            'From'       => $Balance['From'],
                            'To'         => $Balance['To'],
                            'PersonId' => $Balance['PersonId'],
                            'BasketTypeId' => $Balance['BasketType'],
                        ));
                }
            }
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';

        if(empty($Balance) || $error){
            $Table = new Info('Bitte benutzen Sie die Filterung');
        } elseif(empty($tblItemList)) {
            $Table = new Info('Bitte wählen Sie mindestens eine Beitragsart aus');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }

        if(!empty($tableContent) && $tblItemList){

            $TableHead['Debtor'] = 'Beitragszahler Test';
            $TableHead['Causer'] = 'Beitragsverursacher';
            foreach($tblItemList as $tblItem){
                $TableHead['Id'.$tblItem->getId()] = $tblItem->getName();
            }
            $TableHead['Summary'] = 'Gesamt';
            $TableHead['Info'] = new EyeOpen();

            $Table = new TableData($tableContent, null, $TableHead, array(
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(2)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
                // First column should not be with Tabindex
                // solve the problem with responsive false
                "responsive" => false,
            ));
            $Space = '';
        } else {
            $Download->setDisabled();
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(new Well($filterBlock))
                ),
                new LayoutRow(
                    new LayoutColumn(new Container($Download).new Container('&nbsp;'))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param array $Balance
     *
     * @return Stage
     */
    public function frontendBalancePdf($Balance = array())
    {

        $Stage = new Stage('Bescheinigung', 'PDF');
        if(!isset($Balance['Year'])){
            $Now = new \DateTime();
            $_POST['Balance']['Year'] = $Now->format('Y');
        }
        if(!isset($Balance['From'])){
            $_POST['Balance']['From'] = '1';
        }
        if(!isset($Balance['To'])){
            $_POST['Balance']['To'] = '12';
        }
        // required from Select2
        if(!isset($Balance['BasketType'])){
            $_POST['Balance']['BasketType'] = '-1';
        }

        if(!isset($Balance['Filter'])){
            $_POST['Balance']['Filter'] = self::FILTER_CLASS;
        }

        $tableContent = array();
        $countPdfs = 0;
        $tblItem = false;
        $error = false;
        $message = null;
        $tblPerson = false;
        if(!empty($Balance)){
            if (isset($Balance['Search'])) {
                if (!($tblPerson = Person::useService()->getPersonById(isset($Balance['PersonId']) ? $Balance['PersonId'] : 0))) {
                    $message = new Warning('Bitte wählen Sie eine Person aus', new Exclamation());
                    $error = true;
                }
            }
        }

        $filterForm = $this->getFilterForm($Balance, $message);
        $filterBlock = ApiDocument::receiverBlock($filterForm, 'changeFilter');

        if(!empty($Balance)){
            $tblDivision = false;
            $tblGroup = false;
            if (isset($Balance['Division'])) {
                if (!($tblDivision = Division::useService()->getDivisionById($Balance['Division']))) {
                    $filterForm->setError('Balance[Division]', 'Bitte wählen Sie eine Klasse aus');
                    $error = true;
                }
            }
            if (isset($Balance['Group'])) {
                if (!($tblGroup = Group::useService()->getGroupById($Balance['Group']))) {
                    $filterForm->setError('Balance[Group]', 'Bitte wählen Sie eine Gruppe aus');
                    $error = true;
                }
            }
            if (($tblItem = Item::useService()->getItemById($Balance['Item']))) {
                if (!$error) {
                    $PriceList = array();
                    if (isset($Balance['Search'])) {
                        if ($tblPerson) {
                            if(isset($Balance['BasketType'])){
                                $BasketTypeId = $Balance['BasketType'];
                            } else {
                                $tblBasketType = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
                                $BasketTypeId = $tblBasketType->getId();
                            }
                            $PriceList = Balance::useService()->getPriceListByItemAndPerson(
                                $tblItem,
                                $Balance['Year'],
                                $Balance['From'],
                                $Balance['To'],
                                $tblPerson,
                                $BasketTypeId
                            );
                            $PriceList = Balance::useService()->getSummaryByItemPrice($PriceList);
                        }
                    } else {
                        if(isset($Balance['BasketType'])){
                            $BasketTypeId = $Balance['BasketType'];
                        } else {
                            $tblBasketType = Basket::useService()->getBasketTypeByName(TblBasketType::IDENT_ABRECHNUNG);
                            $BasketTypeId = $tblBasketType->getId();
                        }
                        $PriceList = Balance::useService()->getPriceListByItemAndYear(
                            $tblItem,
                            $Balance['Year'],
                            $BasketTypeId,
                            $Balance['From'],
                            $Balance['To'],
                            $tblDivision ? $tblDivision->getId() : '0',
                            $tblGroup ? $tblGroup->getId() : '0'
                        );
                    }

                    $tableContent = Balance::useService()->getTableContentByItemPriceList($PriceList);
                    $countPdfs = count($tableContent);
                }
            } else {
                $filterForm->setError('Balance[Item]', 'Bitte wählen Sie eine Betragsart aus');
                $error = true;
            }
        }

        // Selectbox soll nach unten aufklappen (tritt nur noch bei Anwendungsansicht auf)
        $Space = '<div style="height: 100px;"></div>';
        if (empty($Balance) || $error) {
            $Table = new Info('Bitte benutzen Sie die Filterung');
        } else {
            $Table = new Warning('Keine Ergebnisse gefunden');
        }
        if (!empty($tableContent)) {
            $Table = new TableData($tableContent, null, array(
                'Debtor' => 'Beitragszahler',
                'Causer' => 'Beitragsverursacher',
                'Value'  => 'Summe',
            ), array(
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0, 1)),
                    array('type' => 'natural', 'targets' => array(2)),
//                    array("orderable" => false, "targets"   => array(5, -1)),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'asc')
                ),
                // First column should not be with Tabindex
                // solve the problem with responsive false
                "responsive" => false,
            ));

            if ($tblItem) {
                if (($tblDocumentList = Document::useService()->getDocumentAllByItem($tblItem))
                ) {
                    $Table .= new Well($this->getPdfForm($tblItem, $tblDocumentList, $countPdfs, $Balance));
                } else {
                    $Table .= new Warning('Für die Beitragsart: ' . $tblItem->getName() . ' ist keine Bescheinigung eingestellt.', new Exclamation());
                }
            }
            $Space = '';
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(new Well(new Title('Filterung für Bescheinigung', '') . $filterBlock))
                ),
                new LayoutRow(
                    new LayoutColumn($Table)
                ),
                new LayoutRow(
                    new LayoutColumn($Space)
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param array             $Balance
     * @param IMessageInterface $message
     * @param string            $IsMultiItem
     *
     * @return IFormInterface
     */
    public function getFilterForm($Balance, IMessageInterface $message = null, $IsMultiItem = '0')
    {

        $filterOptions = array(
            self::FILTER_CLASS => 'Klasse',
            self::FILTER_GROUP => 'Gruppe',
            self::FILTER_PERSON => 'Einzel-Person'
        );

        if (isset($Balance['Filter'])) {
            $Filter = $Balance['Filter'];
        } else {
            $Filter = self::FILTER_CLASS;
        }

        if ($Filter) {
            $YearList = Invoice::useService()->getYearList(1, 1);
            $MonthList = Invoice::useService()->getMonthList();
            $tblItemAll = Item::useService()->getItemAll();

            // Inhalt Selectbox
            $BasketTypeSelect = array('-1' => 'Abrechnung - Gutschrift', '2' => 'Auszahlung', '3' => 'Gutschrift',);

            // ohne Mehrfachauswahl Beitragsarten
            $ItemSelect = array(new FormColumn((new SelectBox('Balance[Item]', 'Beitragsart',
                array('{{ Name }}' => $tblItemAll)))->setRequired(), 6),
                new FormColumn(
                    new SelectBox('Balance[BasketType]', 'Variantenauswahl', $BasketTypeSelect)
                    , 6));
            // mit Mehrfachauswahl Beitragsarten
            if($IsMultiItem != '0'){
                $CheckboxItemList = array();
                if($tblItemAll){
                    foreach($tblItemAll as $tblItem){
                        $CheckboxItemList[] = new CheckBox('Balance[ItemList]['.$tblItem->getId().']', $tblItem->getName(), $tblItem->getId());
                    }
                }
                $ItemSelect = array(new FormColumn(
                    new Panel(new Bold('Beitragsarten '.new DangerText('*')),
                        $CheckboxItemList, Panel::PANEL_TYPE_INFO)
                    , 6),
                    new FormColumn(
                        new SelectBox('Balance[BasketType]', 'Variantenauswahl', $BasketTypeSelect)
                    , 6));
            }

            $tblYear = false;
            if (($tblYearList = Term::useService()->getYearByNow())) {
                $tblYear = current($tblYearList);
            }
            if ($Filter == self::FILTER_CLASS || $Filter == self::FILTER_GROUP) {
                $tblDivisionList = array();
                if ($tblYear) {
                    if (!($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                        $tblDivisionList = array();
                    }
                }
                if(empty($tblDivisionList)){
                    $tblDivisionList[] = new TblDivision();
                }

                if ($Filter == self::FILTER_CLASS) {
                    $selectBox = (new SelectBox('Balance[Division]', 'Klasse ' . new ToolTip(new InfoIcon(),
                            'Klassen aus dem aktuellem Schuljahr (Datum ' . (new \DateTime())->format('d.m.Y') . ')'),
                        array('{{ tblLevel.Name }} {{ Name }}' => $tblDivisionList), null, true,
                        null))->setRequired();
                } else {
                    $groups = array();
                    if (($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())) {
                        foreach ($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                            if (($tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson())) {
                                $groups[] = $tblGroup;
                            }
                        }
                    }

                    $selectBox = (new SelectBox('Balance[Group]', 'Gruppe ',
                        array('{{ Name }}' => $groups), null, true,
                        null))->setRequired();
                }

                return
                    new Form(
                        new FormGroup(array(
                            new FormRow(
                                new FormColumn(
                                    new Panel(
                                        'Filter für',
                                        (new SelectBox('Balance[Filter]', '', $filterOptions))
                                            ->ajaxPipelineOnChange(ApiDocument::pipelineChangeFilter($IsMultiItem))
                                            ->configureLibrary(Selectbox::LIBRARY_SELECTER),
                                        Panel::PANEL_TYPE_PRIMARY
                                    )
                                , 3)
                            ),
                            new FormRow(array(
                                new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 3),
                                new FormColumn(new SelectBox('Balance[From]', 'Zeitraum Von', $MonthList, null, true,
                                    null), 3),
                                new FormColumn(new SelectBox('Balance[To]', 'Zeitraum Bis', $MonthList, null, true,
                                    null), 3),
                                new FormColumn($selectBox, 3),
                            )),
                            new FormRow(
                                $ItemSelect
                            ),
                            new FormRow(
                                new FormColumn(new Primary('Filtern', new Filter()))
                            )
                        ))
                    );
            } elseif ($Filter = self::FILTER_PERSON) {
                return
                    new Form(
                        new FormGroup(array(
                            new FormRow(
                                new FormColumn(
                                    new Panel(
                                        'Filter für',
                                        (new SelectBox('Balance[Filter]', '', $filterOptions))
                                            ->ajaxPipelineOnChange(ApiDocument::pipelineChangeFilter($IsMultiItem))
                                            ->configureLibrary(Selectbox::LIBRARY_SELECTER),
                                        Panel::PANEL_TYPE_PRIMARY
                                    )
                                , 3)
                            ),
                            new FormRow(array(
                                new FormColumn((new SelectBox('Balance[Year]', 'Jahr', $YearList))->setRequired(), 4),
                                new FormColumn(new SelectBox('Balance[From]', 'Zeitraum Von', $MonthList, null, true,
                                    null), 4),
                                new FormColumn(new SelectBox('Balance[To]', 'Zeitraum Bis', $MonthList, null, true,
                                    null), 4),
                            )),
                            new FormRow(
                                $ItemSelect
                            ),
                            new FormRow(array(
                                new FormColumn(array(
                                    (new TextField(
                                        'Balance[Search]',
                                        '',
                                        'Suche des Beitragsverursachers',
                                        new Search()
                                    ))->ajaxPipelineOnKeyUp(ApiDocument::pipelineSearchPerson()),
                                    ApiDocument::receiverBlock(
                                        $this->loadPersonSearch(isset($Balance['Search']) ? $Balance['Search'] : '', $message),
                                        'SearchPerson'
                                    )
                                ))
                            )),
                            new FormRow(
                                new FormColumn(new Primary('Filtern', new Filter()))
                            )
                        ))
                    );
            }
        }

        return null;
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadPersonSearch($Search, IMessageInterface $message = null)
    {

        if ($Search != '' && strlen($Search) > 2) {
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $resultList = array();
                foreach ($tblPersonList as $tblPerson) {
                    // onchange only by student, prospect
                    $radio = new RadioBox('Balance[PersonId]', '&nbsp;', $tblPerson->getId());

                    $resultList[] = array(
                        'Select' => $radio,
                        'FirstName' => $tblPerson->getFirstSecondName(),
                        'LastName' => $tblPerson->getLastName(),
                        'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : ''
                    );
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Address' => 'Adresse'
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            } else {
                $result = new Warning('Es wurden keine entsprechenden Personen gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ? $message : '');
    }

    /**
     * @param TblItem $tblItem
     * @param $tblDocumentList
     * @param $countPdfs
     * @param null $Balance
     * @param null $Data
     *
     * @return Form
     */
    public function getPdfForm(TblItem $tblItem, $tblDocumentList, $countPdfs, $Balance = null, $Data = null)
    {

        $Location = '';
        $Date = '';
        if ($Data === null) {
            $global = $this->getGlobal();

            $firstDocument = reset($tblDocumentList);
            $global->POST['Data']['Document'] = $firstDocument->getId();
            $global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $Date = (new \DateTime())->format('d.m.Y');

            if (($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll())) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = reset($tblResponsibilityAll);
                if (($tblCompany = $tblResponsibility->getServiceTblCompany())) {
                    $global->POST['Data']['CompanyName'] = $tblCompany->getName();
                    $global->POST['Data']['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    if (($tblAddress = $tblCompany->fetchMainAddress())
                        && ($tblCity = $tblAddress->getTblCity())
                    ) {
                        $global->POST['Data']['CompanyDistrict'] = $tblCity->getDistrict();
                        $global->POST['Data']['CompanyStreet'] = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
                        $global->POST['Data']['CompanyCity'] = $tblCity->getCode() . ' ' . $tblCity->getName();

                        $global->POST['Data']['Location'] = $tblCity->getName();
                        $Location = $tblCity->getName();
                    }
                }
            }

            // Filterdaten
            if ($Balance) {
                $global->POST['Data']['Item'] = $Balance['Item'];
                $global->POST['Data']['Year'] = $Balance['Year'];
                $global->POST['Data']['From'] = $Balance['From'];
                $global->POST['Data']['To'] = $Balance['To'];
                $global->POST['Data']['BasketType'] = $Balance['BasketType'];
                if (isset($Balance['Division'])) {
                    $global->POST['Data']['Division'] = $Balance['Division'];
                }
                if (isset($Balance['Group'])) {
                    $global->POST['Data']['Group'] = $Balance['Group'];
                }
                if (isset($Balance['PersonId'])) {
                    $global->POST['Data']['PersonId'] = $Balance['PersonId'];
                }
                $global->POST['Data']['CountPdfs'] = $countPdfs;
            }

            $global->savePost();
        }

        $formGroup = new FormGroup(array(
            // Filterdaten
            new FormRow(array(
                new FormColumn(
                    new HiddenField('Data[Item]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[Year]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[From]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[To]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[BasketType]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[Division]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[Group]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[PersonId]')
                    , 1),
                new FormColumn(
                    new HiddenField('Data[CountPdfs]')
                    , 1),
            )),
            new FormRow(array(
                new FormColumn(
                    new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen der Bescheinigung')
                    , 12)
            )),
            new FormRow(array(
                new FormColumn(
                        ApiDocument::receiverBlock(ApiDocument::pipelineLoadDocumentContent($tblItem->getId(), $Location, $Date), 'loadDocumentContent')
                    , 12),
            )),
            new FormRow(array(
                new FormColumn(
                    new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen des Schulträger')
                    , 12)
            )),
            new FormRow(array(
                new FormColumn(
                    new Panel('Name des Schulträgers',array(
                        new TextField('Data[CompanyName]', '', 'Name'),
                        new TextField('Data[CompanyExtendedName]', '', 'Namenszusatz')
                    ), Panel::PANEL_TYPE_INFO)
                    , 6),
                new FormColumn(
                    new Panel('Adressinformation des Schulträgers',array(
                        new TextField('Data[CompanyDistrict]', '', 'Ortsteil'),
                        new TextField('Data[CompanyStreet]', '', 'Straße'),
                        new TextField('Data[CompanyCity]', '', 'PLZ/Ort'),
                    ), Panel::PANEL_TYPE_INFO)
                    , 6),
            )),
        ));

        if ($countPdfs > self::MAX_PDF_PAGES ) {
            $modulo = $countPdfs % self::MAX_PDF_PAGES;
            $countLists = intval($countPdfs / self::MAX_PDF_PAGES);
            if ($modulo > 0) {
                $countLists++;
            }

            $content = array();
            for ($i = 1; $i <= $countLists; $i++) {
                $Data['List'] = $i;
                $content[] = new SelectBoxItem($i, $i . '. Liste aus ' . ($i == $countLists && $modulo > 0 ? $modulo : self::MAX_PDF_PAGES) . ' Bescheinigungen');
            }

            return new Form(
                array(
                    $formGroup,
                    new FormGroup(array(
                        new FormRow(
                            new FormColumn(
                                new Danger(
                                    new Container('Es sind ' . $countLists . ' Listen enthalten! Bitte wählen Sie
                                               diese nacheinander in der Selectbox aus.')
                                    . new Container('Bitte achten Sie darauf, den nächsten PDF-Download erst zu starten,
                                               wenn der vorherige abgeschlossen ist')
                                )
                            )
                        ),
                        new FormRow(array(
                            new FormColumn(
                                new SelectBox('Data[List]', '', array('{{ Name }}' => $content))
                                , 3),
                            new FormColumn(
                                new Primary('Herunterladen', new Download(), true)
                                , 3)
                        )),
                    ))
                ),
                null,
                '/Api/Document/Standard/BillingDocument/Create'
            );
        } else {
            return new Form(
                $formGroup,
                new Primary('Herunterladen', new Download(), true),
                '/Api/Document/Standard/BillingDocument/Create'
            );
        }
    }

    /**
     * @param TblItem $tblItem
     * @param $Data
     * @param $Location
     * @param $Date
     *
     * @return Panel
     */
    public function getDocumentPanel(TblItem $tblItem, $Data, $Location, $Date)
    {
        $global = $this->getGlobal();
        $tblDocumentList = Document::useService()->getDocumentAllByItem($tblItem);
        if (!($tblDocument = Document::useService()->getDocumentById($Data['Document']))) {
            $tblDocument = reset($tblDocumentList);
        }
        if ($tblDocument) {

            $global->POST['Data']['Document'] = $tblDocument->getId();
            if (($tblDocumentSubject = Document::useService()->getDocumentInformationBy($tblDocument, 'Subject'))) {
                $global->POST['Data']['Subject'] = $tblDocumentSubject->getValue();
            } else {
                $global->POST['Data']['Subject'] = '';
            }
            if (($tblDocumentContent = Document::useService()->getDocumentInformationBy($tblDocument, 'Content'))) {
                $global->POST['Data']['Content'] = $tblDocumentContent->getValue();
            } else {
                $global->POST['Data']['Content'] = '';
            }
        }
        $global->POST['Data']['Location'] = $Location;
        $global->POST['Data']['Date'] = $Date;
        $global->savePost();

        return new Panel(
            'Bescheinigung',
            array(
                (new SelectBox('Data[Document]', 'Bescheinigung', array('{{ Name }}' => $tblDocumentList)))
                    ->ajaxPipelineOnChange(ApiDocument::pipelineLoadDocumentContent($tblItem->getId(), $Location,
                        $Date))
                    ->configureLibrary(SelectBox::LIBRARY_SELECTER),
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(array(
                        new TextField('Data[Subject]',
                            'z.B. Schulgeldbescheinigung für das Kalenderjahr [Jahr]', 'Betreff'),
                        new TextArea('Data[Content]', 'Inhalt der Bescheinigung', 'Inhalt', null, 20)
                    ), 9),
                    new LayoutColumn(new Panel('Platzhalter', Document::useFrontend()->getFreeFields(),
                        Panel::PANEL_TYPE_INFO), 3)
                )))),
                new CheckBox('Data[SalutationFamily]', 'Statt der Beitragszahler Anrede die Anrede Familie verwenden', 1),
                new TextField('Data[Location]', '', 'Ort', new MapMarker()),
                new DatePicker('Data[Date]', '', 'Datum', new Calendar())
            ),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @return int
     */
    public function getMaxPdfPages()
    {
        return self::MAX_PDF_PAGES;
    }
}
