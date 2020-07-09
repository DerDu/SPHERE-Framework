<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Year
     *
     * @return Stage
     */
    public function frontendCreateYear($Year = null)
    {

        $Stage = new Stage('Schuljahr', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblYearAll = Term::useService()->getYearAll();
        $TableContent = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear &$tblYear) use (&$TableContent) {

                $tblPeriodAll = $tblYear->getTblPeriodAll(false, true);
                $Temp['Year'] = $tblYear->getYear();
                $Temp['Description'] = $tblYear->getDescription();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__ . '\Edit\Year', new Pencil(),
                        array('Id' => $tblYear->getId())
                    ) .
                    (empty($tblPeriodAll)
                        ? new Standard('', __NAMESPACE__ . '\Destroy\Year', new Remove(),
                            array('Id' => $tblYear->getId())
                        ) : ''
                    );
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Year' => 'Jahr',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Term::useService()->createYear(
                                    $this->formYear()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Year
                                )
                            )
                            , 12)
                    ), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblYear $tblYear
     *
     * @return Form
     */
    public function formYear(TblYear $tblYear = null)
    {

        $YearList = array();
        for ($i = -1; $i < 5; $i++) {
            $this->addYear($YearList, $i);
        }

        // bereits existierende Schuljahr stehen nicht zur Auswahl
        if (($tblYearAll = Term::useService()->getYearAll())) {
            foreach ($tblYearAll as $item) {
                if (!$tblYear && isset($YearList[$item->getYear()])) {
                    unset($YearList[$item->getYear()]);
                }
            }
        }
        // Fügt ein leeres Element hinzu (sonst Fehlermeldung)
        if(count($YearList) <= 1){
            $YearList[] = '';
        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Year']) && $tblYear) {
            $Global->POST['Year']['Year'] = $tblYear->getYear();
            $Global->POST['Year']['Description'] = $tblYear->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schuljahr', array(
                                new SelectBox('Year[Year]', 'Jahr', $YearList, new Select())
                            )
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                                new TextField('Year[Description]', '', 'Beschreibung', new Pencil())
                            )
                            , Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                )),
            ))
        );
    }

    private function addYear(&$YearList, $diff)
    {
        $value = (date('Y') + $diff) . '/' . (date('y') + $diff + 1);
        $YearList[$value] = $value;
    }

    /**
     * @param null|array $Period
     *
     * @return Stage
     */
    public function frontendCreatePeriod($Period = null)
    {

        $Stage = new Stage('Zeitraum', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $TableContent = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod &$tblPeriod) use (&$TableContent) {

                $Temp['Name'] = $tblPeriod->getName();
                $Temp['Description'] = $tblPeriod->getDescription();
                $Temp['PeriodFrom'] = $tblPeriod->getFromDate();
                $Temp['PeriodTo'] = $tblPeriod->getToDate();
                $Temp['IsLevel12'] = $tblPeriod->isLevel12() ? new Check() : new Unchecked();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__ . '\Edit\Period', new Pencil(),
                        array('Id' => $tblPeriod->getId()))
                    . ((Term::useService()->getPeriodExistWithYear($tblPeriod) === false) ?
                        new Standard('', __NAMESPACE__ . '\Destroy\Period', new Remove(),
                            array('Id' => $tblPeriod->getId()))
                        : '');
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name' => 'Name',
                                    'Description' => 'Beschreibung',
                                    'IsLevel12' => 'Für 12. Klasse',
                                    'PeriodFrom' => 'Zeitraum von',
                                    'PeriodTo' => 'Zeitraum Bis',
                                    'Option' => '',
                                ),
                                array(
                                    'order' => array(
                                        array('3', 'desc'),
                                        array('4', 'desc'),
                                        array('0', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'de_date', 'targets' => array(3, 4)),
                                    ),
                                )
                            )
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Term::useService()->createPeriod(
                                    $this->formPeriod()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Period
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblPeriod $tblPeriod
     *
     * @return Form
     */
    public function formPeriod(TblPeriod $tblPeriod = null)
    {

        $tblPeriodAll = Term::useService()->getPeriodAll();
        $acNameAll = array();
        if ($tblPeriodAll) {
            array_walk($tblPeriodAll, function (TblPeriod $tblPeriod) use (&$acNameAll) {

                if (!in_array($tblPeriod->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblPeriod->getName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Period']) && $tblPeriod) {
            $Global->POST['Period']['Name'] = $tblPeriod->getName();
            $Global->POST['Period']['Description'] = $tblPeriod->getDescription();
            $Global->POST['Period']['From'] = $tblPeriod->getFromDate();
            $Global->POST['Period']['To'] = $tblPeriod->getToDate();
            $Global->POST['Period']['IsLevel12'] = $tblPeriod->isLevel12();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Zeitraum',
                            array(
                                new AutoCompleter('Period[Name]', 'Name', 'z.B: 1.Halbjahr',
                                    $acNameAll, new Pencil()),
                                new TextField('Period[Description]', 'z.B: für Gymnasium', 'Beschreibung',
                                    new Pencil()),
                                new CheckBox('Period[IsLevel12]', 'Ist ein Halbjahr für die 12. Klasse', 1)
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Datum',
                            array(
                                new DatePicker('Period[From]', 'Beginn', 'Von', new Calendar()),
                                new DatePicker('Period[To]', 'Ende', 'Bis', new Calendar()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param      $Id
     * @param null $Period
     * @param null $Remove
     *
     * @return Stage
     */
    public function frontendChoosePeriod($Id = null, $Period = null, $Remove = null)
    {

        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if ($tblYear) {
            $Stage = new Stage('Zeiträume', 'Bearbeiten');
            $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));
            $Stage->setContent(new Warning('Jahr nicht gefunden'));
        }
        $Stage = new Stage('Zeitraum', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        if ($tblYear && null !== $Period && ($Period = Term::useService()->getPeriodById($Period))) {
            if ($Remove) {
                Term::useService()->removeYearPeriod($tblYear->getId(), $Period);
                $Stage->setContent(
                    new Success('Zeitraum erfolgreich entfernt')
                    . new Redirect('/Education/Lesson/Term/Choose/Period', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Term::useService()->addYearPeriod($tblYear->getId(), $Period);
                $Stage->setContent(
                    new Success('Zeitraum erfolgreich hinzugefügt')
                    . new Redirect('/Education/Lesson/Term/Choose/Period', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }

        $tblPeriodUsedList = Term::useService()->getPeriodAllByYear($tblYear, false, true);
        $tblPeriodAll = Term::useService()->getPeriodAll();

        $contentPeriodUsed = array();
        $contentPeriodAvailable = array();

        if (is_array($tblPeriodUsedList)) {
            $tblPeriodAvailableList = array_udiff($tblPeriodAll, $tblPeriodUsedList,
                function (TblPeriod $ObjectA, TblPeriod $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );

            foreach ($tblPeriodUsedList as $tblPeriodUsed) {
                $contentPeriodUsed[] = array(
                    'Name' => $tblPeriodUsed->getName(),
                    'FromDate' => $tblPeriodUsed->getFromDate(),
                    'ToDate' => $tblPeriodUsed->getToDate(),
                    'Description' => $tblPeriodUsed->getDescription(),
                    'IsLevel12' => $tblPeriodUsed->isLevel12() ? new Check() : new Unchecked(),
                    'Option' => new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Term/Choose/Period', new Minus(),
                        array(
                            'Id' => $Id,
                            'Period' => $tblPeriodUsed->getId(),
                            'Remove' => true
                        ))
                    )
                );
            }
        } else {
            $tblPeriodAvailableList = $tblPeriodAll;
        }

        if (is_array($tblPeriodAvailableList)) {
            foreach ($tblPeriodAvailableList as $tblPeriodAvailable) {
                $contentPeriodAvailable[] = array(
                    'Name' => $tblPeriodAvailable->getName(),
                    'FromDate' => $tblPeriodAvailable->getFromDate(),
                    'ToDate' => $tblPeriodAvailable->getToDate(),
                    'Description' => $tblPeriodAvailable->getDescription(),
                    'IsLevel12' => $tblPeriodAvailable->isLevel12() ? new Check() : new Unchecked(),
                    'Option' => new PullRight(
                        new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                            '/Education/Lesson/Term/Choose/Period', new Plus(),
                            array(
                                'Id' => $Id,
                                'Period' => $tblPeriodAvailable->getId()
                            ))
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schuljahr', $tblYear->getName() .
                                ($tblYear->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small($tblYear->getDescription())) : ''),
                                Panel::PANEL_TYPE_INFO)
                        ),
                        new LayoutColumn(array(
                            new Title('Zeiträume', 'Zugewiesen'),
                            (empty($tblPeriodUsedList)
                                ? new Warning('Kein Zeitraum zugewiesen')
                                : new TableData($contentPeriodUsed, null,
                                    array(
                                        'Name' => 'Name',
                                        'FromDate' => 'Von',
                                        'ToDate' => 'Bis',
                                        'Description' => 'Beschreibung',
                                        'IsLevel12' => 'Für 12. Klasse',
                                        'Option' => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array('1', 'desc'),
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => array(1, 2)),
                                        ),
                                        "responsive" => false
                                    )
                                )
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Zeiträume', 'Verfügbar'),
                            (empty($tblPeriodAvailableList)
                                ? new Info('Keine weiteren Zeiträume verfügbar')
                                : new TableData($contentPeriodAvailable, null,
                                    array(
                                        'Name'        => 'Name',
                                        'FromDate'    => 'Von',
                                        'ToDate'      => 'Bis',
                                        'Description' => 'Beschreibung',
                                        'IsLevel12' => 'Für 12. Klasse',
                                        'Option'      => ' '
                                    ),
                                    array(
                                        'order' => array(
                                            array('1', 'desc'),
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => array(1, 2)),
                                        ),
                                        "responsive" => false
                                    )
                                )
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|Layout
     */
    public function layoutYear(
        TblYear $tblYear
    ) {

        if ($tblYear) {
            $Panel = new Panel('<b>' . (($tblYear->getDescription()) ? ($tblYear->getDescription()) : 'Schuljahr') . '&nbsp'
                . $tblYear->getDisplayName() . '</b>', '', Panel::PANEL_TYPE_INFO);
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($Panel, 6))));
        }
        return false;
    }

    /**
     * @param null $PeriodId
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendRemovePeriod($PeriodId = null, $Id = null)
    {

        $Stage = new Stage('Zeitraum', 'entfernen');
        if ($PeriodId === null || $Id === null) {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->removeYearPeriod($Id, $PeriodId));
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Year
     *
     * @return Stage|string
     */
    public function frontendEditYear($Id = null, $Year = null)
    {

        $Stage = new Stage('Schuljahr', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Year', new ChevronLeft()));
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if (!$tblYear) {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }
        $Form = $this->formYear($tblYear)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            array(
                                new Panel('Jahr',
                                    $tblYear->getDisplayName() . ' ' . new Small(new Muted($tblYear->getDescription())),
                                    Panel::PANEL_TYPE_INFO) .
                                new Headline(new Edit() . ' Bearbeiten'),
                                new Well(Term::useService()->changeYear($Form, $tblYear, $Year)),
                            ), 12)
                    )
                )
            )
        );
//            $Stage->setContent( Term::useService()->changeYear($this->formYear($tblYear)
//            ->appendFormButton(new Primary('Änderungen speichern'))
//            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
//            ,$tblYear, $Year)
//            );
        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyYear($Id = null)
    {

        $Stage = new Stage('Jahr', 'Entfernen');
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if (!$tblYear) {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyYear($tblYear));

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Period
     *
     * @return Stage|string
     */
    public function frontendEditPeriod($Id = null, $Period = null)
    {

        $Stage = new Stage('Zeitraum', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Period', new ChevronLeft()));
        $tblPeriod = $Id === null ? false : Term::useService()->getPeriodById($Id);

        if (!$tblPeriod) {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden!'));
            return $Stage . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $PeriodName = $tblPeriod->getName();
        $PeriodDescription = $tblPeriod->getDescription();
        $PeriodFrom = $tblPeriod->getFromDate();
        $PeriodTo = $tblPeriod->getToDate();
        $Panel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Zeitraum', array(
                            $PeriodName . ' ' . new Muted(new Small($PeriodDescription)),
                            'Zeitraum ' . $PeriodFrom . ' - ' . $PeriodTo
                        ), Panel::PANEL_TYPE_INFO)
                    )
                )
            )
        );

        $Form = $this->formPeriod($tblPeriod)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($Panel .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(Term::useService()->changePeriod($Form, $tblPeriod, $Period))
                        )
                    ), new Title(new Edit() . ' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param int $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyPeriod($Id = null)
    {

        $Stage = new Stage('Zeitraum', 'Entfernen');
        $tblPeriod = $Id === null ? false : Term::useService()->getPeriodById($Id);
        if (!$tblPeriod) {
            return $Stage . new Warning('Zeitraum nicht gefunden!')
            . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyPeriod($tblPeriod));
        return $Stage;
    }

    /**
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendHoliday($Data = null)
    {

        $Stage = new Stage('Unterrichtsfreie Tage', 'Übersicht');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Lesson/Term', new ChevronLeft()
        ));

        $tableData = array();
        $tblHolidayAll = Term::useService()->getHolidayAll();
        if ($tblHolidayAll) {
            foreach ($tblHolidayAll as $tblHoliday) {

                $tableData[] = array(
                    'FromDate' => $tblHoliday->getFromDate(),
                    'ToDate' => $tblHoliday->getToDate(),
                    'Name' => $tblHoliday->getName(),
                    'Type' => $tblHoliday->getTblHolidayType()->getName(),
                    'Option' => (new Standard(
                            '', '/Education/Lesson/Term/Holiday/Edit', new Edit(),
                            array('Id' => $tblHoliday->getId()), 'Bearbeiten'
                        ))
                        . (new Standard(
                            '', '/Education/Lesson/Term/Holiday/Destroy', new Remove(),
                            array('Id' => $tblHoliday->getId()), 'Löschen'
                        ))
                );
            }
        }

        $Form = $this->formHoliday()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($tableData, null, array(
                                    'FromDate' => 'Datum von',
                                    'ToDate' => 'Datum bis',
                                    'Name' => 'Name',
                                    'Type' => 'Typ',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => array(0,1)),
                                        )
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Term::useService()->createHoliday($Form, $Data))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }


    /**
     * @return Form
     */
    private function formHoliday()
    {

        $tblHolidayTypeAll = Term::useService()->getHolidayTypeAll();
        if (!$tblHolidayTypeAll) {
            $tblHolidayTypeAll = array();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[Type]', 'Typ', array('Name' => $tblHolidayTypeAll)), 4
                ),
                new FormColumn(
                    new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 4
                ),
                new FormColumn(
                    new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 4
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[Name]', '', 'Name'), 12
                ),
            )),
        )));
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditHoliday($Id = null, $Data = null)
    {

        $Stage = new Stage('Unterrichtsfreie Tage', 'Bearbeiten');
        $tblHoliday = Term::useService()->getHolidayById($Id);
        if ($tblHoliday) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Lesson/Term/Holiday', new ChevronLeft()
            ));

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['FromDate'] = $tblHoliday->getFromDate();
                $Global->POST['Data']['ToDate'] = $tblHoliday->getToDate();
                $Global->POST['Data']['Type'] = $tblHoliday->getTblHolidayType()->getId();
                $Global->POST['Data']['Name'] = $tblHoliday->getName();
                $Global->savePost();
            }

            $Form = $this->formHoliday()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Unterrichtsfreie Tage',
                                        $tblHoliday->getName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Term::useService()->updateHoliday($Form, $tblHoliday, $Data))
                                )
                            ))
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister', new ChevronLeft()
            ));

            return $Stage . new Danger('Unterrichtsfreie Tage nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $YearId
     * @param null $DataAddHoliday
     * @param null $DataRemoveHoliday
     *
     * @return Stage|string
     */
    public function frontendSelectHoliday(
        $YearId = null,
        $DataAddHoliday = null,
        $DataRemoveHoliday = null
    ) {

        $Stage = new Stage('Schuljahr', 'Unterrichtsfreie Tage zuweisen');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term', new ChevronLeft()));

        if (($tblYear = Term::useService()->getYearById($YearId))) {

            // ToDo JohK Filter und Zuweisung Schulweise

            $tblYearHolidayList = Term::useService()->getYearHolidayAllByYear($tblYear);
            $tblHolidayAllWhereYears = Term::useService()->getHolidayAllWhereYear($tblYear);
            if ($tblHolidayAllWhereYears
                && ($tblHolidayAllByYear = Term::useService()->getHolidayAllByYear($tblYear))
            ) {
                $tblHolidayAll = array_udiff($tblHolidayAllWhereYears, $tblHolidayAllByYear,
                    function (TblHoliday $tblHolidayA, TblHoliday $tblHolidayB) {

                        return $tblHolidayA->getId() - $tblHolidayB->getId();
                    }
                );
            } else {
                $tblHolidayAll = $tblHolidayAllWhereYears;
            }

            $tblHolidayList = false;
            if ($tblYearHolidayList) {
                $tempList = array();
                foreach ($tblYearHolidayList as $tblYearHoliday) {
                    $tblHoliday = $tblYearHoliday->getTblHoliday();
                    $tempList[] = array(
                        'Check' => new CheckBox('DataRemoveHoliday[' . $tblYearHoliday->getId() . ']', ' ', 1),
                        'Name' => $tblHoliday->getName(),
                        'FromDate' => $tblHoliday->getFromDate(),
                        'ToDate' => $tblHoliday->getToDate(),
                        'Type' => $tblHoliday->getTblHolidayType()->getName()
                    );
                }
                $tblHolidayList = $tempList;
            }
//
            if (is_array($tblHolidayAll)) {
                $tempList = array();
                /** @var TblHoliday $tblHoliday */
                foreach ($tblHolidayAll as $tblHoliday) {
                    $tempList[] = array(
                        'Check' => new CheckBox('DataAddHoliday[' . $tblHoliday->getId() . ']', ' ', 1),
                        'Name' => $tblHoliday->getName(),
                        'FromDate' => $tblHoliday->getFromDate(),
                        'ToDate' => $tblHoliday->getToDate(),
                        'Type' => $tblHoliday->getTblHolidayType()->getName()
                    );
                }
                $tblHolidayAll = $tempList;
            }

            $form = new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            ($tblHolidayList
                                ? new TableData(
                                    $tblHolidayList,
                                    new \SPHERE\Common\Frontend\Table\Repository\Title('Unterrichtsfreie Tage des Schuljahrs "' . $tblYear->getName() . '"',
                                        'Entfernen'),
                                    array(
                                        'Check' => new Center(new Small('Entfernen ') . new Disable()),
                                        'FromDate' => 'Datum von',
                                        'ToDate' => 'Datum bis',
                                        'Name' => 'Name',
                                        'Type' => 'Typ'
                                    ),
                                    array(
                                        'order' => array(
                                            array(1, 'desc'),
                                            array(2, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('orderable' => false, 'targets' => 0),
                                            array('type' => 'de_date', 'targets' => 1),
                                            array('type' => 'de_date', 'targets' => 2),
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                        "searching" => false, // Deaktivieren Suchen
                                        "info" => false  // Deaktivieren Such-Info
                                    )
                                )
                                : new Warning('Keine Unterrichtsfreien Tage zugewiesen.', new Exclamation())
                            )
                        ), 6),
                        new FormColumn(array(
                            ($tblHolidayAll
                                ? new TableData(
                                    $tblHolidayAll,
                                    new \SPHERE\Common\Frontend\Table\Repository\Title('Weitere mögliche Unterrichtsfreie Tage dem Schuljahr "' . $tblYear->getDisplayName() . '"',
                                        'Hinzufügen'),
                                    array(
                                        'Check' => new Center(new Small('Hinzufügen ') . new Enable()),
                                        'FromDate' => 'Datum von',
                                        'ToDate' => 'Datum bis',
                                        'Name' => 'Name',
                                        'Type' => 'Typ'
                                    ),
                                    array(
                                        'order' => array(
                                            array(1, 'desc'),
                                            array(2, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('orderable' => false, 'targets' => 0),
                                            array('type' => 'de_date', 'targets' => 1),
                                            array('type' => 'de_date', 'targets' => 2),
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                        "searching" => false, // Deaktivieren Suchen
                                        "info" => false  // Deaktivieren Such-Info
                                    )
                                )
                                : new Warning('Keine weiteren Unterrichtsfreien Tage verfügbar.', new Exclamation())
                            )
                        ), 6),
                    ))
                ),
            ));

            if ($tblHolidayList || $tblHolidayAll) {
                $form->appendFormButton(new Primary('Speichern', new Save()));
                $form->setConfirm('Die Zuweisung der Unterrichtsfreien Tage wurde noch nicht gespeichert.');
            }

            $Stage->setContent(new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Schuljahr',
                                $tblYear->getName() . ' ' . new Small(new Muted($tblYear->getDescription())),
                                Panel::PANEL_TYPE_INFO
                            ), 12
                        ),
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(
                                Term::useService()->addHolidaysToYear($form, $tblYear, $DataAddHoliday,
                                    $DataRemoveHoliday)
                            )
                        ))
                    ))
                ), new Title('Zusammensetzung', 'der Unterrichtsfreien Tage'))
            )));

        } else {
            return $Stage
            . new Danger('Schuljahr nicht gefunden.', new Ban())
            . new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyHoliday($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Unterrichtsfreie Tage', 'Löschen');

        if (($tblHoliday = Term::useService()->getHolidayById($Id))) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Lesson/Term/Holiday', new ChevronLeft()
            ));

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question() . ' Diese Unterrichtsfreien Tage wirklich löschen?',
                            array(
                                $tblHoliday->getFromDate()
                                . ($tblHoliday->getToDate() ? ' -  ' . $tblHoliday->getToDate() : ''),
                                $tblHoliday->getName(),
                                $tblHoliday->getTblHolidayType()->getName()
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Lesson/Term/Holiday/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Education/Lesson/Term/Holiday', new Disable()
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Term::useService()->destroyHoliday($tblHoliday)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage wurde gelöscht')
                                . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Ban() . ' Die Unterrichtsfreien Tage konnte nicht gelöscht werden')
                                . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Unterrichtsfreien Tage konnte nicht gefunden werden'),
                        new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

}
