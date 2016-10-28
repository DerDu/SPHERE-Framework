<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Publicly;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendLayoutAccount()
    {

        $Stage = new Stage('Benutzerkonten');
        $Stage->setMessage('Hier können neue Nutzerzugänge angelegt und bestehende Benutzerkonten bearbeitet bzw. gelöscht werden');
        $Stage->addButton(new Standard(
            'Neues Benutzerkonto anlegen', '/Setting/Authorization/Account/Create', new Pencil()
        ));
        $Stage->setContent($this->layoutAccount());
        return $Stage;
    }

    /**
     * @return Layout
     */
    public function layoutAccount()
    {

        $tblAccountAll = Account::useService()->getAccountAll();
        if ($tblAccountAll) {
            array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                if (
                    ( $tblAccount->getServiceTblIdentification()
                        && $tblAccount->getServiceTblIdentification()->getId() != Account::useService()->getIdentificationByName('System')->getId() )
                    && $tblAccount->getServiceTblConsumer()
                    && $tblAccount->getServiceTblConsumer()->getId() == Consumer::useService()->getConsumerBySession()->getId()
                ) {

                    $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
                    if ($tblPersonAll) {
                        array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                            $tblPerson = $tblPerson->getFullName();
                        });
                    }

                    $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                    if ($tblAuthorizationAll) {
                        array_walk($tblAuthorizationAll, function (TblAuthorization &$tblAuthorization) {

                            if ($tblAuthorization->getServiceTblRole()) {
                                $tblAuthorization = $tblAuthorization->getServiceTblRole()->getName();
                            } else {
                                $tblAuthorization = false;
                            }
                        });
                        $tblAuthorizationAll = array_filter($tblAuthorizationAll);
                    }

                    $tblAccount = array(
                        'Username'       => new Listing(array($tblAccount->getUsername())),
                        'Person'         => new Listing(!empty( $tblPersonAll )
                            ? $tblPersonAll
                            : array(new Danger(new Exclamation().new Small(' Keine Person angeben')))
                        ),
                        'Authentication' => new Listing(array($tblAccount->getServiceTblIdentification() ? $tblAccount->getServiceTblIdentification()->getDescription() : '')),
                        'Authorization'  => new Listing(!empty( $tblAuthorizationAll )
                            ? $tblAuthorizationAll
                            : array(new Danger(new Exclamation().new Small(' Keine Berechtigungen vergeben')))
                        ),
                        'Token'          => new Listing(array(
                            $tblAccount->getServiceTblToken()
                                ? substr($tblAccount->getServiceTblToken()->getSerial(), 0,
                                    4).' '.substr($tblAccount->getServiceTblToken()->getSerial(), 4, 4)
                                : new Muted(new Small('Kein Hardware-Schlüssel vergeben'))
                        )),
                        'Option'         =>
                            new Standard('',
                                '/Setting/Authorization/Account/Edit',
                                new Edit(), array('Id' => $tblAccount->getId()),
                                'Benutzer '.$tblAccount->getUsername().' bearbeiten'
                            )
                            .new Standard('',
                                '/Setting/Authorization/Account/Destroy',
                                new Remove(), array('Id' => $tblAccount->getId()),
                                'Benutzer '.$tblAccount->getUsername().' löschen'
                            )
                    );
                } else {
                    $tblAccount = false;
                }
            });
            $tblAccountAll = array_filter($tblAccountAll);
        }

        return new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData(
                            $tblAccountAll,
                            null,
                            array(
                                'Username'       => new PersonKey().' Benutzerkonto',
                                'Person'         => new Person().' Person',
                                'Authentication' => new Lock().' Kontotyp',
                                'Authorization'  => new Nameplate().' Berechtigungen',
                                'Token'          => new Key().' Hardware-Schlüssel',
                                'Option'         => 'Optionen'
                            )
                        )
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Listing().' Übersicht')
            )
        );
    }

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendCreateAccount($Account = null)
    {

        $Stage = new Stage('Benutzerkonto', 'Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Authorization/Account', new ChevronLeft()));
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Account::useService()->createAccount(
                                $this->formAccount()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Account)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return Form
     */
    private function formAccount(TblAccount $tblAccount = null)
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        // Identification
        $tblIdentificationAll = Account::useService()->getIdentificationAll();
        if ($tblIdentificationAll) {
            array_walk($tblIdentificationAll, function (TblIdentification &$tblIdentification) {

                if ($tblIdentification->getName() == 'System') {
                    $tblIdentification = false;
                } else {
                    switch (strtoupper($tblIdentification->getName())) {
                        case 'CREDENTIAL':
                            $Global = $this->getGlobal();
                            if (!isset( $Global->POST['Account']['Identification'] )) {
                                $Global->POST['Account']['Identification'] = $tblIdentification->getId();
                                $Global->savePost();
                            }
                            $Label = $tblIdentification->getDescription();
                            break;
                        default:
                            $Label = $tblIdentification->getDescription().' ('.new Key().')';
                    }
                    $tblIdentification = new RadioBox(
                        'Account[Identification]', $Label, $tblIdentification->getId()
                    );
                }
            });
            $tblIdentificationAll = array_filter($tblIdentificationAll);
        } else {
            $tblIdentificationAll = array();
        }

        // Role
        $tblRoleAll = Access::useService()->getRoleAll();
        $tblRoleAll = $this->getSorter($tblRoleAll)->sortObjectBy( TblRole::ATTR_NAME, new StringGermanOrderSorter() );
        if ($tblRoleAll) {
            array_walk($tblRoleAll, function (TblRole &$tblRole) {

                if ($tblRole->isInternal()) {
                    $tblRole = false;
                } else {
                    $tblRole = new CheckBox('Account[Role]['.$tblRole->getId().']',
                        ( $tblRole->isSecure() ? new YubiKey() : new Publicly() ).' '.$tblRole->getName(),
                        $tblRole->getId()
                    );
                }
            });
            $tblRoleAll = array_filter($tblRoleAll);
        } else {
            $tblRoleAll = array();
        }

        // Token
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Account']['Token'] )) {
            $Global->POST['Account']['Token'] = 0;
            $Global->savePost();
        }

        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());
        if ($tblAccount) {
            $tblToken = $tblAccount->getServiceTblToken();
        } else {
            $tblToken = false;
        }
        if ($tblTokenAll) {
            array_walk($tblTokenAll, function (TblToken &$tblTokenItem) use ($tblToken) {

                if (
                    ( $tblToken === false || $tblTokenItem->getId() != $tblToken->getId() )
                    && !Account::useService()->getAccountAllByToken($tblTokenItem)
                ) {
                    $tblTokenItem = new RadioBox('Account[Token]',
                        implode(' ', str_split($tblTokenItem->getSerial(), 4)), $tblTokenItem->getId());
                } else {
                    $tblTokenItem = false;
                }
            });
            $tblTokenAll = array_filter($tblTokenAll);
        } else {
            $tblTokenAll = array();
        }
        array_unshift($tblTokenAll,
            new RadioBox('Account[Token]',
                new Danger('KEIN Hardware-Schlüssel notwendig'),
                0
            )
        );

        // Token Panel
        if ($tblToken) {
            array_unshift($tblTokenAll, new Danger('ODER einen anderen Schlüssel wählen: '));
            array_unshift($tblTokenAll,
                new RadioBox('Account[Token]',
                    implode(' ', str_split($tblToken->getSerial(), 4)), $tblToken->getId())
            );
            array_unshift($tblTokenAll, new Danger('AKTUELL hinterlegter Schlüssel, '));

            $PanelToken = new Panel(new Key().' mit folgendem Hardware-Schlüssel', $tblTokenAll
                , Panel::PANEL_TYPE_INFO);
        } else {
            $PanelToken = new Panel(new Person().' mit folgendem Hardware-Schlüssel',
                $tblTokenAll
                , Panel::PANEL_TYPE_INFO);
        }

        // Person
        if ($tblAccount) {
            $User = Account::useService()->getUserAllByAccount($tblAccount);
        }
        if (!empty( $User )) {
            $tblPerson = $User[0]->getServiceTblPerson();
        } else {
            $tblPerson = false;
        }

        $tblPersonAll = \SPHERE\Application\People\Person\Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson &$tblPersonItem) use ($tblPerson, $Global) {

                if (
                    (
                        $tblPerson === false
                        || $tblPersonItem->getId() != $tblPerson->getId()
                    )
                    && (
                        !isset( $Global->POST['Account']['User'] )
                        || (
                            isset( $Global->POST['Account']['User'] ) && $Global->POST['Account']['User'] != $tblPersonItem->getId()
                        )
                    )
                ) {
                    $tblPersonItem = array(
                        'Person'  => new RadioBox('Account[User]', $tblPersonItem->getFullName(),
                            $tblPersonItem->getId()),
                        'Address' => $tblPersonItem->fetchMainAddress() ? $tblPersonItem->fetchMainAddress()->getGuiString() : ''
                    );
                } else {
                    $tblPersonItem = array(
                        'Person'  => new Warning($tblPersonItem->getFullName()).' (Aktuell hinterlegt)',
                        'Address' => $tblPersonItem->fetchMainAddress() ? $tblPersonItem->fetchMainAddress()->getGuiString() : ''
                    );
                }
            });
            $tblPersonAll = array_filter($tblPersonAll);
        } else {
            $tblPersonAll = array();
        }

        // Person Panel
        if ($tblPerson) {
            $PanelPerson = new Panel(new Person().' für folgende Person', array(
                new Danger('AKTUELL hinterlegte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
                new Danger('ODER eine andere Person wählen: '),
                new TableData($tblPersonAll, null, array('Person' => 'Person wählen', 'Address' => 'Adresse')),
            ), Panel::PANEL_TYPE_INFO);
        } elseif (isset( $Global->POST['Account']['User'] )) {
            $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($Global->POST['Account']['User']);
            $PanelPerson = new Panel(new Person().' für folgende Person', array(
                new Warning('AKTUELL selektierte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
                new Danger('ODER eine andere Person wählen: '),
                new TableData($tblPersonAll, null, array('Person' => 'Person wählen', 'Address' => 'Adresse')),
            ), Panel::PANEL_TYPE_INFO);
        } else {
            $PanelPerson = new Panel(new Person().' für folgende Person', array(
                new TableData($tblPersonAll, null, array('Person' => 'Person wählen', 'Address' => 'Adresse')),
            ), Panel::PANEL_TYPE_INFO);
        }

        // Username Panel
        if ($tblAccount) {
            $UsernamePanel = new Panel(new PersonKey().' Benutzerkonto', array(
                (new TextField('Account[Name]', 'Benutzername (min. 5 Zeichen)', 'Benutzername',
                    new Person()))
                    ->setPrefixValue($tblConsumer->getAcronym())->setDisabled(),
                new Danger('Die Passwort-Felder nur ausfüllen wenn das Passwort dieses Benutzers geändert werden soll'),
                new PasswordField(
                    'Account[Password]', 'Passwort (min. 8 Zeichen)', 'Passwort', new Lock()),
                new PasswordField(
                    'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                    new Repeat()
                ),
            ), Panel::PANEL_TYPE_INFO);
        } else {
            $UsernamePanel = new Panel(new PersonKey().' Benutzerkonto', array(
                (new TextField('Account[Name]', 'Benutzername (min. 5 Zeichen)', 'Benutzername',
                    new Person()))
                    ->setPrefixValue($tblConsumer->getAcronym()),
                new PasswordField(
                    'Account[Password]', 'Passwort (min. 8 Zeichen)', 'Passwort', new Lock()),
                new PasswordField(
                    'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                    new Repeat()
                ),
            ), Panel::PANEL_TYPE_INFO);
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        $UsernamePanel,
                        new Panel(new Lock().' Authentifizierungstyp wählen', $tblIdentificationAll,
                            Panel::PANEL_TYPE_INFO),
                        $PanelToken
                    ), 4),
                    new FormColumn(array(
                        $PanelPerson,
                        new Panel(new Nameplate().' mit folgenden Berechtigungen', $tblRoleAll, Panel::PANEL_TYPE_INFO),
                    ), 8),
                )),
            )),
        ));
    }

    /**
     * @param null|int   $Id
     * @param null|array $Account
     *
     * @return Stage
     */
    public function frontendUpdateAccount($Id = null, $Account = null)
    {

        $Stage = new Stage('Benutzerkonto', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Setting/Authorization/Account', new ChevronLeft()));
        $tblAccount = Account::useService()->getAccountById($Id);
        if ($tblAccount) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Account']['Identification'] = $tblAccount->getServiceTblIdentification()
                    ? $tblAccount->getServiceTblIdentification()->getId() : 0;
                $Global->POST['Account']['Token'] = (
                $tblAccount->getServiceTblToken()
                    ? $tblAccount->getServiceTblToken()->getId()
                    : 0
                );
                $User = Account::useService()->getUserAllByAccount($tblAccount);
                if ($User) {
                    $tblPerson = $User[0]->getServiceTblPerson();
                    if ($tblPerson) {
                        $Global->POST['Account']['User'] = $tblPerson->getId();
                    }
                }

                $Authorization = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                if ($Authorization) {
                    /** @var TblAuthorization $Role */
                    foreach ((array)$Authorization as $Role) {
                        if ($Role->getServiceTblRole()) {
                            $Global->POST['Account']['Role'][$Role->getServiceTblRole()->getId()] = $Role->getServiceTblRole()->getId();
                        }
                    }
                }

            }
            $Global->POST['Account']['Name'] = preg_replace('!^(.*?)-!is', '', $tblAccount->getUsername());
            $Global->savePost();

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Well(
                                Account::useService()->changeAccount(
                                    $this->formAccount($tblAccount)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                                    $tblAccount, $Account)
                            ))
                        ), new Title(new Pencil().' Bearbeiten')
                    ),
                ))
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Danger(
                                    'Das Benutzerkonto konnte nicht gefunden werden'
                                )
                            )
                        ), new Title('Benutzerkonto ändern')
                    )
                )
            );
        }
        return $Stage;
    }

    /**
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyAccount($Id, $Confirm = false)
    {

        $Stage = new Stage('Benutzerkonto', 'Löschen');
        if ($Id) {
            $tblAccount = Account::useService()->getAccountById($Id);
            if (!$Confirm) {

                $Content = array(
                    $tblAccount->getUsername(),
                    ( $tblAccount->getServiceTblIdentification() ? new Lock().' '.$tblAccount->getServiceTblIdentification()->getDescription() : '' )
                    .( $tblAccount->getServiceTblToken() ? ' '.new Key().' '.$tblAccount->getServiceTblToken()->getSerial() : '' )
                );

                $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAll) {
                    array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                        $tblPerson = new Person().' '.$tblPerson->getFullName();
                    });
                    $Content = array_merge($Content, $tblPersonAll);
                }

                $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                if ($tblAuthorizationAll) {
                    array_walk($tblAuthorizationAll, function (TblAuthorization &$tblAuthorization) {

                        if ($tblAuthorization->getServiceTblRole()) {
                            $tblAuthorization = new Nameplate().' '.$tblAuthorization->getServiceTblRole()->getName();
                        } else {
                            $tblAuthorization = false;
                        }
                    });
                    $tblAuthorizationAll = array_filter(( $tblAuthorizationAll ));
                    $Content = array_merge($Content, $tblAuthorizationAll);
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonKey().' Benutzerkonto', $Content, Panel::PANEL_TYPE_SUCCESS),
                        new Panel(new Question().' Dieses Benutzerkonto wirklich löschen?', array(),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Authorization/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Authorization/Account', new Disable()
                            )
                        )
                    )))))
                );
            } else {

                // Remove Session
                $tblSessionAll = Account::useService()->getSessionAllByAccount($tblAccount);
                if (!empty( $tblSessionAll )) {
                    /** @var TblSession $tblSession */
                    foreach ($tblSessionAll as $tblSession) {
                        Account::useService()->destroySession(null, $tblSession->getSession());
                    }
                }

                // Remove User
                $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
                if (!empty( $tblPersonAll )) {
                    /** @var TblPerson $tblPerson */
                    foreach ($tblPersonAll as $tblPerson) {
                        Account::useService()->removeAccountPerson($tblAccount, $tblPerson);
                    }
                }

                // Remove Authentication
                $tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount);
                if (!empty( $tblAuthentication )) {
                    Account::useService()->removeAccountAuthentication($tblAccount,
                        $tblAuthentication->getTblIdentification());
                }

                // Remove Authorization
                $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                if (!empty( $tblAuthorizationAll )) {
                    /** @var TblAuthorization $tblAuthorization */
                    foreach ($tblAuthorizationAll as $tblAuthorization) {
                        if ($tblAuthorization->getServiceTblRole()) {
                            Account::useService()->removeAccountAuthorization($tblAccount,
                                $tblAuthorization->getServiceTblRole());
                        }
                    }
                }

                // Remove Setting
                $tblSettingAll = Account::useService()->getSettingAllByAccount($tblAccount);
                if (!empty( $tblSettingAll )) {
                    /** @var TblAuthorization $tblAuthorization */
                    foreach ($tblSettingAll as $tblSetting) {
                        Account::useService()->destroySetting($tblSetting);
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Account::useService()->destroyAccount($tblAccount)
                                ? new Success('Das Benutzerkonto wurde gelöscht')
                                : new Danger('Das Benutzerkonto konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Das Benutzerkonto konnte nicht gefunden werden'),
                        new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
