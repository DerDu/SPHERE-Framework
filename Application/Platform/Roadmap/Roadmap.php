<?php
namespace SPHERE\Application\Platform\Roadmap;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Roadmap\Youtrack\Credentials;
use SPHERE\Application\Platform\Roadmap\Youtrack\Issue;
use SPHERE\Application\Platform\Roadmap\Youtrack\Parser;
use SPHERE\Application\Platform\Roadmap\Youtrack\Sprint;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Extension\Extension;

/**
 * Class Roadmap
 *
 * @package SPHERE\Application\Platform\Roadmap
 */
class Roadmap extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Roadmap'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    public static function registerModule()
    {

    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Schulsoftware','Roadmap');

        try {
            $Map = $this->getRoadmap();
            $Pool = $this->getPool();
        } catch (\Exception $Exception) {
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(new Warning('Roadmap konnte nicht abgerufen werden'))
            ))));
            return $Stage;
        }

        $Stage->setMessage(
            'Aktuelle Versionen: <br/>'.
            new Label('Preview: '.$Map->getVersionPreview(), Label::LABEL_TYPE_WARNING)
            .' '.
            new Label('Release: '.$Map->getVersionRelease(), Label::LABEL_TYPE_PRIMARY)
        );

        $Cache = $this->getCache(new MemcachedHandler(), 'Memcached');
        if (!( $Content = $Cache->getValue('Roadmap', __METHOD__) )) {

            // Is System-Account
            $SystemLink = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblIdentification = $tblAccount->getServiceTblIdentification();
                if ($tblIdentification) {
                    $SystemLink = ( $tblIdentification->getName() == TblIdentification::NAME_SYSTEM );
                }
            }

            $Sprints = $Map->getSprints();

            $PriorityColor = array(
                'Kritisch' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
                'Hoch'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
                'Normal'   => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
                'Niedrig'  => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
            );

            $StateColor = array(
                'Erfasst'            => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
                'Offen'              => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
                'In Bearbeitung'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
                'Behoben'            => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
                'Integriert'         => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
                'Zu besprechen'      => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
                'Wird nicht behoben' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger'
            );

            $TypeColor = array(
                'Bug'         => '\SPHERE\Common\Frontend\Layout\Repository\Label\Danger',
                'Feature'     => '\SPHERE\Common\Frontend\Layout\Repository\Label\Success',
                'Optimierung' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning'
            );

            $SubsystemColor = array(
                '0' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Warning',
                '1' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info',
                '2' => '\SPHERE\Common\Frontend\Layout\Repository\Label\Info'
            );

            $SprintCurrent = null;
            $LayoutColumns = array();
            /** @var Sprint $Sprint */
            foreach ((array)$Sprints as $SprintIndex => $Sprint) {

                $SprintComplete = true;
                /** @var LayoutColumn[] $IssueList */
                $IssueList = array();
                /** @var LayoutColumn[] $ResolvedList */
                $ResolvedList = array();
                $Issues = $Sprint->getIssues();
                /** @var Issue $Issue */
                foreach ((array)$Issues as $Issue) {
                    $ColumnList = array();
                    if ($Issue->getState() == 'Behoben' || $Issue->getState() == 'Integriert') {

                        $Description = $this->sanitizeDescription($Issue->getDescription(), 0);
                        $Title = $this->sanitizeTitle($Issue->getTitle());

                        $ColumnList[] = new LayoutColumn(
                            new Panel(

                                new Small($Title), $Description,
                                Panel::PANEL_TYPE_SUCCESS,
                                new Label($Issue->getId()).( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0,
                                        1)] )
                                    ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]($Issue->getSubsystem())
                                    : $Issue->getSubsystem()
                                )
                                .( isset( $TypeColor[$Issue->getType()] )
                                    ? new $TypeColor[$Issue->getType()]($Issue->getType())
                                    : $Issue->getType()
                                )

                            ), 3);

                        $ResolvedList = array_merge($ResolvedList, $ColumnList);
                    } else {
                        $SprintComplete = false;
                        if (!$SprintCurrent) {
                            $SprintCurrent = $Sprint->getVersion();
                        }

                        if ($Issue->getState() == 'In Bearbeitung') {
                            $ProgressBar = new ProgressBar($Issue->getTimePercent(), 100 - $Issue->getTimePercent(),
                                100);
                        } else {
                            $ProgressBar = new ProgressBar($Issue->getTimePercent(), 0, 100);
                        }

                        $Title = $this->sanitizeTitle($Issue->getTitle());
                        $Description = $this->sanitizeDescription($Issue->getDescription(), 0);

                        $ColumnList[] = new LayoutColumn(array(
                            new Panel($Title.' '.$ProgressBar,
                                ( strlen($Description) == 0 ? '' : $Description ),
                                Panel::PANEL_TYPE_INFO,
                                new Layout(
                                    new LayoutGroup(
                                        new LayoutRow(array(
                                            new LayoutColumn(array(
                                                new PullClear(
                                                    new PullLeft(
                                                        new Label($Issue->getId())
                                                    ).
                                                    new PullLeft(
                                                        ( isset( $PriorityColor[$Issue->getPriority()] )
                                                            ? new $PriorityColor[$Issue->getPriority()]($Issue->getPriority())
                                                            : $Issue->getPriority()
                                                        )
                                                    )
                                                    .
                                                    new PullLeft(
                                                        ( isset( $StateColor[$Issue->getState()] )
                                                            ? new $StateColor[$Issue->getState()]($Issue->getState())
                                                            : $Issue->getState()
                                                        )
                                                    ).
                                                    new PullLeft(
                                                        ( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)] )
                                                            ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]
                                                            ($Issue->getSubsystem())
                                                            : $Issue->getSubsystem()
                                                        )
                                                    ).
                                                    new PullLeft(
                                                        ( isset( $TypeColor[$Issue->getType()] )
                                                            ? new $TypeColor[$Issue->getType()]($Issue->getType())
                                                            : $Issue->getType()
                                                        )
                                                    ).( $SystemLink
                                                        ? new PullRight(
                                                            new External($Issue->getId(),
                                                                'https://ticket.swe.haus-der-edv.de/issue/'.$Issue->getId()
                                                            )
                                                        )
                                                        : ''
                                                    )
                                                )
                                            ))
                                        ))
                                    )
                                )

                            )
                        ), 4);

                        $IssueList = array_merge($IssueList, $ColumnList);
                    }
                }

                $FoldSprint = false;
                // Create Sprint-Content Layout
                if ($SprintComplete) {

                    // Fold Sprint?
                    if (isset( $Sprints[( $SprintIndex + 1 )] ) && $Sprints[( $SprintIndex + 1 )]->isDone()) {
                        $FoldSprint = true;
                    }

                    $LayoutRowList = array();
                    $LayoutRowCount = 0;
                    $LayoutRow = null;
                    foreach ($ResolvedList as $LayoutColumn) {
                        if ($LayoutRowCount % 4 == 0) {
                            $LayoutRow = new LayoutRow(array());
                            $LayoutRowList[] = $LayoutRow;
                        }
                        $LayoutRow->addColumn($LayoutColumn);
                        $LayoutRowCount++;
                    }

                    $SprintList = new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Layout(new LayoutGroup($LayoutRowList))
                            ),
                        )),
                    )));
                } else {
                    $LayoutRowList = array();
                    $LayoutRowCount = 0;
                    $LayoutRow = null;
                    foreach ($ResolvedList as $LayoutColumn) {
                        if ($LayoutRowCount % 4 == 0) {
                            $LayoutRow = new LayoutRow(array());
                            $LayoutRowList[] = $LayoutRow;
                        }
                        $LayoutRow->addColumn($LayoutColumn);
                        $LayoutRowCount++;
                    }
                    /** @var LayoutRow[] $ResolvedList */
                    $ResolvedList = $LayoutRowList;

                    $LayoutRowList = array();
                    $LayoutRowCount = 0;
                    $LayoutRow = null;
                    foreach ($IssueList as $LayoutColumn) {
                        if ($LayoutRowCount % 3 == 0) {
                            $LayoutRow = new LayoutRow(array());
                            $LayoutRowList[] = $LayoutRow;
                        }
                        $LayoutRow->addColumn($LayoutColumn);
                        $LayoutRowCount++;
                    }
                    /** @var LayoutRow[] $IssueList */
                    $IssueList = $LayoutRowList;

                    if (empty( $ResolvedList )) {
                        $SprintList = new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Layout(new LayoutGroup($IssueList,
                                        new Title(new Danger('Geplante Änderungen'), 'in '.$Sprint->getVersion())))
                                ),
                            ))
                        )));
                    } else {
                        $SprintList = new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Layout(new LayoutGroup($IssueList,
                                        new Title(new Danger('Geplante Änderungen'), 'in '.$Sprint->getVersion())))
                                ),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Layout(new LayoutGroup($ResolvedList,
                                        new Title(new \SPHERE\Common\Frontend\Text\Repository\Success('Neuerungen'),
                                            'in '.$Sprint->getVersion())))
                                ),
                            )),
                        )));
                    }
                }

                if ($SprintComplete) {
                    $VersionHeader = new Success(new PullClear(
                        new PullLeft('Änderungen in '.$Sprint->getVersion())
                        .new PullRight(
                            new Label('Freigabe Demo @ '.date('m / Y',
                                    strtotime($Sprint->getTimestampFinish().' +1 day')),
                                Label::LABEL_TYPE_WARNING)
                            .new Label('Freigabe Live @ '.date('m / Y',
                                    strtotime($Sprint->getTimestampFinish().' +2 month')),
                                Label::LABEL_TYPE_PRIMARY)
                        )
                    ));
                    if (!$Sprint->isDone()) {
                        $SprintPercent = $this->getDatePercent($Sprint->getTimestampStart(),
                            $Sprint->getTimestampFinish());
                        $VersionHeader .= new ProgressBar($SprintPercent, 100 - $SprintPercent, 100);
                    }
                    $VersionFooter = null;
                } else {
                    $SprintColor = ( $Sprint->getVersion() == $SprintCurrent
                        ? '\SPHERE\Common\Frontend\Message\Repository\Info'
                        : '\SPHERE\Common\Frontend\Message\Repository\Warning'
                    );

                    $VersionHeader = new $SprintColor(new PullClear(
                        new PullLeft('Version '.$Sprint->getVersion())
                        .new PullRight(
                            new Label('Freigabe Demo @ '.date('m / Y',
                                    strtotime($Sprint->getTimestampFinish().' +1 day')),
                                Label::LABEL_TYPE_WARNING)
                            .new Label('Freigabe Live @ '.date('m / Y',
                                    strtotime($Sprint->getTimestampFinish().' +2 month')),
                                Label::LABEL_TYPE_PRIMARY)
                        )
                    ));
                    $VersionHeader .= new ProgressBar($this->getDatePercent($Sprint->getTimestampStart(),
                        $Sprint->getTimestampFinish()), 0, 100);
                    $VersionFooter = new Muted(new Small($Sprint->getTimestampStart().' - '.$Sprint->getTimestampFinish()));
                }

                $LayoutColumns[] = new LayoutColumn(
                    new Panel(
                        $VersionHeader,
                        ( $FoldSprint ? (new Accordion())->addItem(new Small(new Italic(new Muted('[Änderungen anzeigen]'))),
                            (string)$SprintList) : (string)$SprintList ),
                        ( $SprintComplete ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING ),
                        $VersionFooter
                    )
                );
            }

            // Pool
            $Issues = $Pool->getPool();
            /** @var LayoutColumn[] $PoolList */
            $PoolList = array();
            /** @var Issue $Issue */
            foreach ((array)$Issues as $Issue) {
                $ColumnList = array();

                $Title = $this->sanitizeTitle($Issue->getTitle());
                $Description = $this->sanitizeDescription($Issue->getDescription(), 0);

                $ColumnList[] = new LayoutColumn(array(
                    new Panel($Title,
                        ( strlen($Description) == 0 ? '' : $Description ),
                        Panel::PANEL_TYPE_INFO,
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(array(
                                        new PullClear(
                                            new PullLeft(
                                                new Label($Issue->getId())
                                            ).
                                            new PullLeft(
                                                ( isset( $PriorityColor[$Issue->getPriority()] )
                                                    ? new $PriorityColor[$Issue->getPriority()]($Issue->getPriority())
                                                    : $Issue->getPriority()
                                                )
                                            )
                                            .
                                            new PullLeft(
                                                ( isset( $StateColor[$Issue->getState()] )
                                                    ? new $StateColor[$Issue->getState()]($Issue->getState())
                                                    : $Issue->getState()
                                                )
                                            ).
                                            new PullLeft(
                                                ( isset( $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)] )
                                                    ? new $SubsystemColor[substr($Issue->getSubsystem(), 0, 1)]
                                                    ($Issue->getSubsystem())
                                                    : $Issue->getSubsystem()
                                                )
                                            ).
                                            new PullLeft(
                                                ( isset( $TypeColor[$Issue->getType()] )
                                                    ? new $TypeColor[$Issue->getType()]($Issue->getType())
                                                    : $Issue->getType()
                                                )
                                            ).( $SystemLink
                                                ? new PullRight(
                                                    new External($Issue->getId(),
                                                        'https://ticket.swe.haus-der-edv.de/issue/'.$Issue->getId()
                                                    )
                                                )
                                                : ''
                                            )
                                        )
                                    ))
                                ))
                            )
                        )

                    )
                ), 4);

                $PoolList = array_merge($PoolList, $ColumnList);
            }

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            foreach ($PoolList as $LayoutColumn) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($LayoutColumn);
                $LayoutRowCount++;
            }

            $SprintList = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Layout(new LayoutGroup($LayoutRowList))
                    ),
                )),
            )));

            $LayoutColumns[] = new LayoutColumn(
                new Panel(
                    new \SPHERE\Common\Frontend\Message\Repository\Danger('Ideen & Feedback'),
                    (string)$SprintList,
                    Panel::PANEL_TYPE_DANGER
                )
            );

            $Content = (new Layout(new LayoutGroup(new LayoutRow($LayoutColumns))))->__toString();
            $Cache->setValue('Roadmap', $Content, ( 60 * 60 * 4 ), __METHOD__);
        }

        $Stage->setContent($Content);

        return $Stage;
    }

    /**
     * Get RoadMap-Object
     *
     * @return Youtrack\Map
     * @trows \Exception
     */
    public function getRoadmap()
    {

        $Parser = new Parser(
            new Credentials(),
            'Sichtbar für: {Alle Benutzer} Typ: Feature,Bug,Optimierung Teilsystem: {1*},{2*} Status: Erfasst, Offen,{In Bearbeitung},Behoben,{Zu besprechen},Integriert Beheben in: -{Nicht definiert}'
        );
        return $Parser->getMap();
    }

    public function getPool()
    {

        $Parser = new Parser(
            new Credentials(),
            'Sichtbar für: {Alle Benutzer} Typ: Feature,Bug,Optimierung Teilsystem: {1*},{2*} Status: Erfasst, Offen,{In Bearbeitung} ,{Zu besprechen} Beheben in: {Nicht definiert}'
        );
        return $Parser->getPool();
    }

    /**
     * @param string $Value
     * @param int    $MaxLineCount
     *
     * @return string
     */
    private function sanitizeDescription($Value, $MaxLineCount = 3)
    {

        $Value = explode("\n", $Value);
        array_walk($Value, function (&$Line) {

            if (empty( $Line )) {
                $Line = false;
            }
            if (strpos($Line, '@') === 0) {
                $Line = false;
            }
            if (strpos($Line, 'Line:') === 0) {
                $Line = false;
            }
            if (strpos($Line, '!') === 0) {
                $Line = false;
            }
        });
        $Value = array_filter($Value);
        $Value = preg_replace('!\s+?[\-]+\>!is', ': ', $Value);
        $Value = preg_replace('#!image.*?\..*?!#is', ' [IMAGE] ', $Value);
        $Value = preg_replace('!{html.*?}!is', ' ', $Value);

        $ShortDescription = trim(implode("\n", array_slice($Value, 0, $MaxLineCount)));
        $LongDescription = trim(implode("\n", array_slice($Value, $MaxLineCount)));

        if (strlen($ShortDescription) == 0 && $MaxLineCount > 0) {
            return new Small(new Italic(new Muted('Keine Beschreibung angegeben')));
        }

        if (strlen($LongDescription) > 0) {
            return new Small(nl2br($ShortDescription))
            .(new Accordion())->addItem(
                new Italic(new Small('[Beschreibung anzeigen]')),
                new Small(nl2br($LongDescription))
            )->getContent();
        } else {
            if (strlen($ShortDescription) == 0) {
                return new Small(new Italic(new Muted('Keine Beschreibung angegeben')));
            }
            return new Small(nl2br($ShortDescription));
        }
    }

    /**
     * @param $Value
     *
     * @return string
     */
    private function sanitizeTitle($Value)
    {

        $Value = preg_replace('!Account: [0-9]+!is', '[System Report]', $Value);
        $Value = preg_replace('!^Error!is', 'Anwendungsfehler', $Value);
        $Value = preg_replace('!^Exception!is', 'Absturz der Anwendung', $Value);
        $Value = preg_replace('!^Shutdown!is', 'Absturz der Anwendung', $Value);
        $Value = preg_replace('!\bBug\b!is', 'Fehler', $Value);
        $Value = preg_replace('!\s+?[\-]+\>!is', ': ', $Value);
        $Value = preg_replace('!\bnull\b!is', 'leer', $Value);
        return $Value;
    }

    /**
     * @param $Start
     * @param $Finish
     *
     * @return float|int
     */
    private function getDatePercent($Start, $Finish)
    {

        $Start = strtotime($Start);
        $Finish = strtotime($Finish);
        $Now = time();

        if ($Finish - $Start <= 0 || $Now >= $Finish) {
            $Percent = 100;
        } else {
            $Percent = ( $Now - $Start ) / ( $Finish - $Start ) * 100;
        }

        return number_format($Percent, 2, ',', '.');
    }
}
