<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
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
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                $Temp['Year'] = $tblYear->getYear();
                $Temp['Description'] = $tblYear->getDescription();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__.'\Edit\Year', new Pencil(),
                        array('Id' => $tblYear->getId())
                    ).
                    ( empty( $tblPeriodAll )
                        ? new Standard('', __NAMESPACE__.'\Destroy\Year', new Remove(),
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
                                'Year'        => 'Jahr',
                                'Description' => 'Beschreibung',
                                'Option'      => '',
                            ))
                        )
                    ), new Title(new ListingTable().' Übersicht')
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
                    ), new Title(new PlusSign().' Hinzufügen')
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

        $tblYearAll = Term::useService()->getYearAll();
        $acNameAll = array();

        $YearList[] = '';
//        $YearList[(date('Y') - 4).'/'.(date('y') - 3)] = (date('Y') - 4).'/'.(date('y') - 3);
//        $YearList[(date('Y') - 3).'/'.(date('y') - 2)] = (date('Y') - 3).'/'.(date('y') - 2);
        $YearList[( date('Y') - 2 ).'/'.( date('y') - 1 )] = ( date('Y') - 2 ).'/'.( date('y') - 1 );
        $YearList[date('Y') - 1 .'/'.date('y')] = date('Y') - 1 .'/'.date('y');
        $YearList[date('Y').'/'.( date('y') + 1 )] = date('Y').'/'.( date('y') + 1 );
        $YearList[( date('Y') + 1 ).'/'.( date('y') + 2 )] = ( date('Y') + 1 ).'/'.( date('y') + 2 );
        $YearList[( date('Y') + 2 ).'/'.( date('y') + 3 )] = ( date('Y') + 2 ).'/'.( date('y') + 3 );
        $YearList[( date('Y') + 3 ).'/'.( date('y') + 4 )] = ( date('Y') + 3 ).'/'.( date('y') + 4 );
        $YearList[( date('Y') + 4 ).'/'.( date('y') + 5 )] = ( date('Y') + 4 ).'/'.( date('y') + 5 );
//        $YearList[(date('Y') + 5).'/'.(date('y') + 6)] = (date('Y') + 5).'/'.(date('y') + 6);

        $TypeList = Type::useService()->getTypeAll();
        $TypeList[] = new TblType('');

        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$acNameAll) {

                if (!in_array($tblYear->getDisplayName(), $acNameAll)) {
                    array_push($acNameAll, $tblYear->getDisplayName());
                }
            });
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Year'] ) && $tblYear) {
            $Global->POST['Year']['Year'] = $tblYear->getYear();
            $Global->POST['Year']['Description'] = $tblYear->getDescription();
            $Global->savePost();
        }
        if (!isset( $Global->POST['Year'] )) {
            $Global->POST['Year']['Year'] = date('Y').'/'.( date('y') + 1 );
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schuljahr', array(
                                new SelectBox('Year[Year]', 'Jahr', $YearList, new Select()))
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                                new TextField('Year[Description]', 'z.B: für Gymnasium', 'Beschreibung', new Pencil()))
                            , Panel::PANEL_TYPE_INFO
                        )
                        , 6)
                )),
            ))
        );
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
                $Temp['Period'] = $tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                $Temp['Option'] =
                    new Standard('', __NAMESPACE__.'\Edit\Period', new Pencil(),
                        array('Id' => $tblPeriod->getId()))
                    .( ( Term::useService()->getPeriodExistWithYear($tblPeriod) === false ) ?
                        new Standard('', __NAMESPACE__.'\Destroy\Period', new Remove(),
                            array('Id' => $tblPeriod->getId()))
                        : '' );
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
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'Period'      => 'Zeitraum',
                                    'Option'      => '',
                                ),
                                array(
                                    'order' => array(
                                        array('2', 'desc'),
                                        array('0', 'asc'),
                                    )
                                )
                            )
                        )
                    ), new Title(new ListingTable().' Übersicht')
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
                    ), new Title(new PlusSign().' Hinzufügen')
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
        if (!isset( $Global->POST['Period'] ) && $tblPeriod) {
            $Global->POST['Period']['Name'] = $tblPeriod->getName();
            $Global->POST['Period']['Description'] = $tblPeriod->getDescription();
            $Global->POST['Period']['From'] = $tblPeriod->getFromDate();
            $Global->POST['Period']['To'] = $tblPeriod->getToDate();
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
                                    new Pencil())
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

        if ($tblYear && null !== $Period && ( $Period = Term::useService()->getPeriodById($Period) )) {
            if ($Remove) {
                Term::useService()->removeYearPeriod($tblYear->getId(), $Period);
                $Stage->setContent(
                    new Success('Zeitraum erfolgreich entfernt')
                    .new Redirect('/Education/Lesson/Term/Choose/Period', Redirect::TIMEOUT_SUCCESS, array('Id' => $Id))
                );
                return $Stage;
            } else {
                Term::useService()->addYearPeriod($tblYear->getId(), $Period);
                $Stage->setContent(
                    new Success('Zeitraum erfolgreich hinzugefügt')
                    .new Redirect('/Education/Lesson/Term/Choose/Period', Redirect::TIMEOUT_SUCCESS, array('Id' => $Id))
                );
                return $Stage;
            }
        }

        $tblPeriodUsedList = Term::useService()->getPeriodAllByYear($tblYear);

        $tblPeriodAll = Term::useService()->getPeriodAll();

        if (is_array($tblPeriodUsedList)) {
            $tblPeriodAvailable = array_udiff($tblPeriodAll, $tblPeriodUsedList,
                function (TblPeriod $ObjectA, TblPeriod $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        } else {
            $tblPeriodAvailable = $tblPeriodAll;
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (is_array($tblPeriodUsedList)) {
            array_walk($tblPeriodUsedList, function (TblPeriod &$Entity) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                        '/Education/Lesson/Term/Choose/Period', new Minus(),
                        array(
                            'Id'     => $Id,
                            'Period' => $Entity->getId(),
                            'Remove' => true
                        ))
                );
            }, $Id);
        }

        /** @noinspection PhpUnusedParameterInspection */
        if (isset( $tblPeriodAvailable ) && is_array($tblPeriodAvailable)) {
            array_walk($tblPeriodAvailable, function (TblPeriod &$Entity) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $Entity->Option = new PullRight(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen',
                        '/Education/Lesson/Term/Choose/Period', new Plus(),
                        array(
                            'Id'     => $Id,
                            'Period' => $Entity->getId()
                        ))
                );
            });
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
                            ( empty( $tblPeriodUsedList )
                                ? new Warning('Kein Zeitraum zugewiesen')
                                : new TableData($tblPeriodUsedList, null,
                                    array(
                                        'Name'        => 'Name',
                                        'FromDate'    => 'Von',
                                        'ToDate'      => 'Bis',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array('0', 'asc'),
                                            array('1', 'asc'),
                                        )
                                    )
                                )
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title('Zeiträume', 'Verfügbar'),
                            ( empty( $tblPeriodAvailable )
                                ? new Info('Keine weiteren Zeiträume verfügbar')
                                : new TableData($tblPeriodAvailable, null,
                                    array(
                                        'Name'        => 'Name',
                                        'FromDate'    => 'Von',
                                        'ToDate'      => 'Bis',
                                        'Description' => 'Beschreibung',
                                        'Option'      => ''
                                    ),
                                    array(
                                        'order' => array(
                                            array('0', 'asc'),
                                            array('1', 'asc'),
                                        )
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
            $Panel = new Panel('<b>'.( ( $tblYear->getDescription() ) ? ( $tblYear->getDescription() ) : 'Schuljahr' ).'&nbsp'
                .$tblYear->getDisplayName().'</b>', '', Panel::PANEL_TYPE_INFO);
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($Panel, 6))));
        }
        return false;
    }

    /**
     * @param null $PeriodId
     * @param null $Id
     *
     * @return \SPHERE\Common\Frontend\Message\Repository\Success
     */
    public function frontendRemovePeriod($PeriodId = null, $Id = null)
    {

        $Stage = new Stage('Zeitraum', 'entfernen');
        if ($PeriodId === null || $Id === null) {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden'));
            return $Stage.new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->removeYearPeriod($Id, $PeriodId));
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Year
     *
     * @return Stage
     */
    public function frontendEditYear($Id = null, $Year = null)
    {

        $Stage = new Stage('Schuljahr', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Year', new ChevronLeft()));
        $tblYear = $Id === null ? false : Term::useService()->getYearById($Id);
        if (!$tblYear) {
            $Stage->setContent(new Warning('Jahr nicht gefunden!'));
            return $Stage.new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
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
                                    $tblYear->getDisplayName().' '.new Small(new Muted($tblYear->getDescription())),
                                    Panel::PANEL_TYPE_INFO).
                                new Headline(new Edit().' Bearbeiten'),
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
            return $Stage.new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyYear($tblYear));

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Period
     *
     * @return Stage
     */
    public function frontendEditPeriod($Id = null, $Period = null)
    {

        $Stage = new Stage('Zeitraum', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Term/Create/Period', new ChevronLeft()));
        $tblPeriod = $Id === null ? false : Term::useService()->getPeriodById($Id);

        if (!$tblPeriod) {
            $Stage->setContent(new Warning('Zeitraum nicht gefunden!'));
            return $Stage.new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
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
                            $PeriodName.' '.new Muted(new Small($PeriodDescription)),
                            'Zeitraum '.$PeriodFrom.' - '.$PeriodTo
                        ), Panel::PANEL_TYPE_INFO)
                    )
                )
            )
        );

        $Form = $this->formPeriod($tblPeriod)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($Panel.
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(Term::useService()->changePeriod($Form, $tblPeriod, $Period))
                        )
                    ), new Title(new Edit().' Bearbeiten')
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
            return $Stage.new Warning('Zeitraum nicht gefunden!')
            .new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(Term::useService()->destroyPeriod($tblPeriod));
        return $Stage;
    }
}
