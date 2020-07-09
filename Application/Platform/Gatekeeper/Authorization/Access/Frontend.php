<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Frontend\Summary;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\TileList;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Rechteverwaltung');
        $this->menuButton($Stage);

        $TwigData = array();
        $tblRoleList = Access::useService()->getRoleAll();
        /** @var TblRole $tblRole */
        foreach ((array)$tblRoleList as $tblRole) {
            if (!$tblRole) {
                continue;
            }
            $TwigData[$tblRole->getId()] = array('Role' => $tblRole);
            $tblLevelList = Access::useService()->getLevelAllByRole($tblRole);
            /** @var TblLevel $tblLevel */
            foreach ((array)$tblLevelList as $tblLevel) {
                if (!$tblLevel) {
                    continue;
                }
                $TwigData[$tblRole->getId()]['LevelList'][$tblLevel->getId()] = array('Level' => $tblLevel);
                $tblPrivilegeList = Access::useService()->getPrivilegeAllByLevel($tblLevel);
                /** @var TblPrivilege $tblPrivilege */
                foreach ((array)$tblPrivilegeList as $tblPrivilege) {
                    if (!$tblPrivilege) {
                        continue;
                    }
                    $TwigData[$tblRole->getId()]['LevelList'][$tblLevel->getId()]['PrivilegeList'][$tblPrivilege->getId()] = array('Privilege' => $tblPrivilege);
                    $tblRightList = Access::useService()->getRightAllByPrivilege($tblPrivilege);
                    foreach ((array)$tblRightList as $tblRight) {
                        if (!$tblRight) {
                            continue;
                        }
                        if (!isset( $TwigData[$tblRole->getId()]['LevelList'][$tblLevel->getId()]['PrivilegeList'][$tblPrivilege->getId()]['RightList'] )) {
                            $TwigData[$tblRole->getId()]['LevelList'][$tblLevel->getId()]['PrivilegeList'][$tblPrivilege->getId()]['RightList'] = array();
                        }
                        $TwigData[$tblRole->getId()]['LevelList'][$tblLevel->getId()]['PrivilegeList'][$tblPrivilege->getId()]['RightList'][] = $tblRight;
                    }
                }
            }

        }
        $Stage->setContent(
            new Summary($TwigData)
        );

        return $Stage;
    }

    /**
     * @param Stage $Stage
     */
    private function menuButton(Stage $Stage)
    {

        $Stage->addButton(new Standard('Rollen', new Link\Route(__NAMESPACE__.'/Role'), new TagList(), array(),
            'Zusammenstellung von Berechtigungen'));
        $Stage->addButton(new Standard('Zugriffslevel', new Link\Route(__NAMESPACE__.'/Level'), new Tag(), array(),
            'Gruppen von Privilegien'));
        $Stage->addButton(new Standard('Privilegien', new Link\Route(__NAMESPACE__.'/Privilege'), new TileBig(),
            array(), 'Gruppen von Rechten'));
        $Stage->addButton(new Standard('Rechte', new Link\Route(__NAMESPACE__.'/Right'), new TileList(), array(),
            'Geschützte Routen'));
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendLevel($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Zugriffslevel');
        $this->menuButton($Stage);
        $tblLevelAll = Access::useService()->getLevelAll();
        $levelList = array();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel &$tblLevel) use (&$levelList) {

                $tblPrivilegeList = Access::useService()->getPrivilegeAllByLevel($tblLevel);
                if (empty( $tblPrivilegeList )) {

                    $levelList[] = array(
                        'Name' => $tblLevel->getName(),
                        'Option' => new Warning('Keine Privilegien vergeben')
                        .new PullRight(new Danger('Privilegien hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                            null, array('Id' => $tblLevel->getId())
                        )));
                } else {

                    $privilegeList = array();
                    array_walk($tblPrivilegeList, function (TblPrivilege &$tblPrivilege) use (&$privilegeList) {

                        $privilegeList[] = $tblPrivilege->getName();
                    });
                    array_unshift($privilegeList, '');

                    $levelList[] = array(
                        'Name' => $tblLevel->getName(),
                        'Option' => new Panel('Privilegien', $privilegeList)
                        .new PullRight(new Danger('Privilegien bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                            null, array('Id' => $tblLevel->getId())
                        )));
                }
            });
        }

        $Stage->setContent(
            ( !empty($levelList)
                ? new TableData($levelList, new Title('Bestehende Zugriffslevel'), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Zugriffslevel vorhanden')
            )
            .Access::useService()->createLevel(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Zugriffslevel anlegen'))
                    , new Primary('Hinzufügen')
                ), $Name
            )
        );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendPrivilege($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Privilegien');
        $this->menuButton($Stage);
        $tblPrivilegeAll = Access::useService()->getPrivilegeAll();
        $privilegeList = array();
        if ($tblPrivilegeAll) {
            array_walk($tblPrivilegeAll, function (TblPrivilege &$tblPrivilege) use (&$privilegeList) {

                $tblRightList = Access::useService()->getRightAllByPrivilege($tblPrivilege);
                if (empty( $tblRightList )) {

                    $privilegeList[] = array(
                        'Name' => $tblPrivilege->getName(),
                        'Option' => new Warning('Keine Rechte vergeben')
                        .new PullRight(new Danger('Rechte hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                            null, array('Id' => $tblPrivilege->getId())
                        )));
                } else {
                    $rightList = array();
                    array_walk($tblRightList, function (TblRight &$tblRight) use (&$rightList) {

                        $rightList[] = $tblRight->getRoute();
                    });
                    array_unshift($rightList, '');

                    $privilegeList[] = array(
                        'Name' => $tblPrivilege->getName(),
                        'Option' => new Panel('Rechte (Routen)', $rightList)
                        .new PullRight(new Danger('Rechte bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                            null, array('Id' => $tblPrivilege->getId())
                        )));
                }
            });
        }

        $Stage->setContent(
            ( $privilegeList
                ? new TableData($privilegeList, new Title('Bestehende Privilegien'), array(
                    'Name'   => 'Name',
                    'Option' => ''
                ))
                : new Warning('Keine Privilegien vorhanden')
            )
            .Access::useService()->createPrivilege(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Privileg anlegen'))
                    , new Primary('Speichern', new Save())
                ), $Name
            )
        );

        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendRight($Name = null)
    {

        $Stage = new Stage('Berechtigungen', 'Rechte');
        $this->menuButton($Stage);
        $tblRightAll = Access::useService()->getRightAll();

        $PublicRouteAll = Main::getDispatcher()->getPublicRoutes();
        $publicRightList = array();
        $publicRouteList = array(
            '/',
            '/Document/LegalNotice',
            '/Document/License',
            '/Platform/Assistance',
            '/Platform/Assistance/Error',
            '/Platform/Assistance/Error/Authenticator',
            '/Platform/Assistance/Error/Authorization',
            '/Platform/Assistance/Error/Shutdown',
            '/Platform/Assistance/Support',
            '/Platform/Gatekeeper/Authentication/Offline',
            '/Document/DataProtectionOrdinance',
            '/Manual/Request',
            '/Platform/Gatekeeper/Saml/MetaData',
            '/Platform/Gatekeeper/Saml/Login/EVSSN',
            '/Platform/Gatekeeper/Authentication/Saml/EVSSN',
        );
        if ($PublicRouteAll) {
            array_walk($PublicRouteAll, function (&$Route) use (&$publicRightList, $publicRouteList) {
                // only routes that haven't to be public
                if (!in_array($Route, $publicRouteList)) {
                    $item['Route'] = $Route;
                    $item['Option'] = new External('Öffnen', $Route, null, array(), false)
                        .new Danger('Hinzufügen', '/Platform/Gatekeeper/Authorization/Access/Right/Create', null,
                            array('Name' => $Route)
                        );
                    array_push($publicRightList, $item);
                }
            });
        }
        $Stage->setContent(
            (!empty($publicRightList)
                ? new TableData($publicRightList, new Title('Öffentliche Routen', 'PUBLIC ACCESS'))
                :new Warning('Keine neuen Routen vorhanden')
            )
            .Access::useService()->createRightForm(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Recht anlegen', 'Route'))
                    , new Primary('Hinzufügen')
                ), $Name
            )
            .( $tblRightAll
                ? new TableData($tblRightAll, new Title('Bestehende Rechte', 'Routen'), array(
                    'Route' => 'Name'
                ))
                : new Warning('Keine Routen vorhanden')
            )
        );
        return $Stage;
    }

    public function frontendCreateRight($Name = '')
    {

        $Stage = new Stage('Recht hinzufügen');

        $Stage->setContent(Access::useService()->createRight($Name));

        return $Stage;
    }

    /**
     * @param null|string $Name
     * @param bool $IsSecure
     * @param bool $IsIndividual
     *
     * @return Stage
     */
    public function frontendRole($Name, $IsSecure = false, $IsIndividual = false)
    {

        $Stage = new Stage('Berechtigungen', 'Rollen');
        $this->menuButton($Stage);
        $tblRoleAll = Access::useService()->getRoleAll();
        $roleList = array();
        if ($tblRoleAll) {
            array_walk($tblRoleAll, function (TblRole &$tblRole) use (&$roleList) {

                $tblLevel = Access::useService()->getLevelAllByRole($tblRole);

                if (empty( $tblLevel )) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $option = new Warning('Keine Zugriffslevel vergeben')
                        .new PullRight(new Danger('Zugriffslevel hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                            null, array('Id' => $tblRole->getId())
                        ));
                } else {
                    array_walk($tblLevel, function (TblLevel &$tblLevel) {

                        $tblLevel = $tblLevel->getName();
                    });
                    array_unshift($tblLevel, '');
                    /** @noinspection PhpUndefinedFieldInspection */
                    $option = new Panel('Zugriffslevel', $tblLevel)
                        .new PullRight(new Danger('Zugriffslevel bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                            null, array('Id' => $tblRole->getId())
                        ));
                }

                $roleList[] = array(
                    'Name' => $tblRole->getName(),
                    'IsInternal' => $tblRole->isInternal() ? 'ja' : 'nein',
                    'IsSecure' => $tblRole->isSecure()  ? 'ja' : 'nein',
                    'IsIndividual' => $tblRole->isIndividual()  ? 'ja' : 'nein',
                    'Option' => $option
                );
            });
        }
        $Stage->setContent(
            ( !empty($roleList)
                ? new TableData($roleList, new Title('Bestehende Rollen'), array(
                    'Name'   => 'Name',
                    'IsInternal' => 'Intern',
                    'IsSecure' => 'Erfordert Yubikey',
                    'IsIndividual' => 'Individuell',
                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Rollen vorhanden')
            )
            .Access::useService()->createRole(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new Panel('Rolle anlegen', array(
                                    new TextField('Name', 'Name', 'Name'),
                                    new CheckBox('IsSecure', 'Nur mit Hardware-Token', 1),
                                    new CheckBox('IsIndividual', 'Individuelle Rolle (Rolle lässt sich pro Mandant ein- und ausblenden)', 1)
                                ), Panel::PANEL_TYPE_INFO)
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Neue Rolle anlegen'))
                    , new Primary('Hinzufügen')
                ), $Name, $IsSecure, $IsIndividual
            )
        );
        return $Stage;
    }

    /**
     * @param integer      $Id
     *
     * @return Stage
     */
    public function frontendRoleGrantLevel($Id)
    {

        $Stage = new Stage('Berechtigungen', 'Rolle');
        $this->menuButton($Stage);
        $tblRole = Access::useService()->getRoleById($Id);

        $tblAccessList = Access::useService()->getLevelAllByRole($tblRole);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getLevelAll(), $tblAccessList,
            function (TblLevel $ObjectA, TblLevel $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );

        $tableContentRight = array();
        array_walk($tblAccessListAvailable, function (TblLevel $tblLevel) use ($Id, &$tableContentRight){

            $item['Name'] = $tblLevel->getName();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'tblLevel' => $tblLevel->getId()
            ));
            array_push($tableContentRight, $item);
        });

        $tableContentLeft = array();
        array_walk($tblAccessList, function (TblLevel $tblLevel) use ($Id, &$tableContentLeft){

            $item['Name'] = $tblLevel->getName();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(
                'Id'       => $Id,
                'tblLevel' => $tblLevel->getId()
            ));
            array_push($tableContentLeft, $item);
        });

        $Stage->setContent(
            new Info($tblRole->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Zugriffslevel', 'Zugewiesen'),
                            new TableData($tableContentLeft, null,
                                array('Exchange' => '', 'Name' => 'Name'), array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                                        'Handler' => array(
                                            'From' => 'glyphicon-minus-sign',
                                            'To'   => 'glyphicon-plus-sign',
                                            'All'  => 'TableRemoveAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableCurrent',
                                            'To'   => 'TableAvailable',
                                        )
                                    )
                                )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll' )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Zugriffslevel', 'Verfügbar'),
                            new TableData($tableContentRight, null,
                                array('Exchange' => ' ', 'Name' => 'Name '), array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                                        'Handler' => array(
                                            'From' => 'glyphicon-plus-sign',
                                            'To'   => 'glyphicon-minus-sign',
                                            'All'  => 'TableAddAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableAvailable',
                                            'To'   => 'TableCurrent',
                                        ),
                                    )
                                )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll' )
                        ), 6)
                    ))
                )
            )
        );
        return $Stage;
    }

    /**
     * @param integer      $Id
     *
     * @return Stage
     */
    public function frontendLevelGrantPrivilege($Id)
    {

        $Stage = new Stage('Berechtigungen', 'Zugriffslevel');
        $this->menuButton($Stage);

        $tblLevel = Access::useService()->getLevelById($Id);
        $tblAccessList = Access::useService()->getPrivilegeAllByLevel($tblLevel);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getPrivilegeAll(), $tblAccessList,
            function (TblPrivilege $ObjectA, TblPrivilege $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );

        $tableContentRight = array();
        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessListAvailable, function (TblPrivilege $tblPrivilege) use ($Id, &$tableContentRight){

            $item['Name'] = $tblPrivilege->getName();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'           => $Id,
                'tblPrivilege' => $tblPrivilege->getId()
            ));

            array_push($tableContentRight, $item);
        });

        $tableContentLeft = array();
        array_walk($tblAccessList, function (TblPrivilege $tblPrivilege) use ($Id, &$tableContentLeft){
            $item['Name'] = $tblPrivilege->getName();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(
                'Id'           => $Id,
                'tblPrivilege' => $tblPrivilege->getId()
            ));
            array_push($tableContentLeft, $item);
        });

        $Stage->setContent(
            new Info($tblLevel->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Zugriffslevel', 'Zugewiesen'),
                            new TableData($tableContentLeft, null,
                                array('Exchange' => '', 'Name' => 'Name'), array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                                        'Handler' => array(
                                            'From' => 'glyphicon-minus-sign',
                                            'To'   => 'glyphicon-plus-sign',
                                            'All'  => 'TableRemoveAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableCurrent',
                                            'To'   => 'TableAvailable',
                                        )
                                    )
                                )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll' )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Rechte', 'Verfügbar'),
                            new TableData($tableContentRight, null,
                                array('Exchange' => ' ', 'Name' => 'Name '), array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                                        'Handler' => array(
                                            'From' => 'glyphicon-plus-sign',
                                            'To'   => 'glyphicon-minus-sign',
                                            'All'  => 'TableAddAll'
                                        ),
                                        'Connect' => array(
                                            'From' => 'TableAvailable',
                                            'To'   => 'TableCurrent',
                                        ),
                                    )
                                )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll' )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param integer      $Id
     *
     * @return Stage
     */
    public function frontendPrivilegeGrantRight($Id)
    {

        $Stage = new Stage('Berechtigungen', 'Privileg');
        $this->menuButton($Stage);

        $tblPrivilege = Access::useService()->getPrivilegeById($Id);
        $tblAccessList = Access::useService()->getRightAllByPrivilege($tblPrivilege);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getRightAll(), $tblAccessList,
            function (TblRight $ObjectA, TblRight $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );
        $tableContentRight = array();
        array_walk($tblAccessListAvailable, function (TblRight $tblRight) use ($Id, &$tableContentRight){
            $item['Route'] = $tblRight->getRoute();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'tblRight' => $tblRight->getId()
            ));
            array_push($tableContentRight, $item);
        });

        $tableContentLeft = array();
        array_walk($tblAccessList, function (TblRight $tblRight) use ($Id, &$tableContentLeft){
            $item['Route'] = $tblRight->getRoute();
            $item['Exchange'] = new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(
                'Id'       => $Id,
                'tblRight' => $tblRight->getId()
            ));
            array_push($tableContentLeft, $item);
        });

        $Stage->setContent(
            new Info($tblPrivilege->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Rechte', 'Zugewiesen'),
                            new TableData($tableContentLeft, null,
                                    array('Exchange' => '', 'Route' => 'Route'), array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                                            'Handler' => array(
                                                'From' => 'glyphicon-minus-sign',
                                                'To'   => 'glyphicon-plus-sign',
                                                'All'  => 'TableRemoveAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableCurrent',
                                                'To'   => 'TableAvailable',
                                            )
                                        )
                                    )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll' )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Rechte', 'Verfügbar'),
                            new TableData($tableContentRight, null,
                                    array('Exchange' => ' ', 'Route' => 'Route '), array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                                            'Handler' => array(
                                                'From' => 'glyphicon-plus-sign',
                                                'To'   => 'glyphicon-minus-sign',
                                                'All'  => 'TableAddAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableAvailable',
                                                'To'   => 'TableCurrent',
                                            ),
                                        )
                                    )
                            ),
                            new Exchange( Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll' )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }
}
