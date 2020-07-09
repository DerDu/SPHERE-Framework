<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
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
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
                        && $tblAccount->getServiceTblIdentification()->getId() != Account::useService()->getIdentificationByName('System')->getId()
                        && $tblAccount->getServiceTblIdentification()->getId() != Account::useService()->getIdentificationByName('UserCredential')->getId()
                    )
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

                        if ($tblAuthorizationAll) {
                            sort($tblAuthorizationAll);
                        }
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
        $tblAuthentication = Account::useService()->getIdentificationByName('Token');
        if ($tblAuthentication) {
            $Global = $this->getGlobal();
            $Global->POST['Account']['Identification'] = $tblAuthentication->getId();
            $Global->savePost();
        }
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

//        $TeacherRole = array(
//            'Bildung: Klassenbuch (Lehrer)' => false,
//            'Bildung: pädagogisches Tagebuch (Klassenlehrer)' => false,
//            'Bildung: Zensurenvergabe (Lehrer)' => false,
//            'Bildung: Zeugnis (Drucken - Klassenlehrer)' => false,
//            'Bildung: Zeugnis (Vorbereitung - Klassenlehrer)' => false,
//            'Einstellungen: Benutzer' => false
//        );

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        // Role
        $tblRoleAll = Access::useService()->getRoleAll();
        $tblRoleAll = $this->getSorter($tblRoleAll)->sortObjectBy(TblRole::ATTR_NAME, new StringGermanOrderSorter());
        if ($tblRoleAll){
            array_walk($tblRoleAll, function(TblRole &$tblRole) use(&$TeacherRole){
//                if(array_key_exists($tblRole->getName(), $TeacherRole)){
//                    $TeacherRole[$tblRole->getName()] = 'Account[Role]['.$tblRole->getId().']';
//                }

                if ($tblRole->isInternal()){
                    $tblRole = false;
                } else {
                    if (!$tblRole->isIndividual()
                        || (
                            ($tblAccount = Account::useService()->getAccountBySession())
                            && ($tblConsumer = $tblAccount->getServiceTblConsumer())
                            && (Access::useService()->getRoleConsumerBy($tblRole, $tblConsumer))
                        )
                    ){
                        $tblRole = new CheckBox('Account[Role]['.$tblRole->getId().']',
                            ($tblRole->isSecure() ? new YubiKey() : new Publicly()).' '.$tblRole->getName(),
                            $tblRole->getId()
                        );
                    } else {
                        $tblRole = false;
                    }
                }
            });
            $tblRoleAll = array_filter($tblRoleAll);
        } else {
            $tblRoleAll = array();
        }

//        $TeacherRole

        // Token
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Account']['Token'])){
            $Global->POST['Account']['Token'] = 0;
            $Global->savePost();
        }

        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());
        if ($tblAccount){
            $tblToken = $tblAccount->getServiceTblToken();
        } else {
            $tblToken = false;
        }
        if ($tblTokenAll){
            $tblTokenAll = $this->getSorter($tblTokenAll)->sortObjectBy(TblToken::ATTR_SERIAL);
            array_walk($tblTokenAll, function(TblToken &$tblTokenItem) use ($tblToken){

                if (
                    ($tblToken === false || $tblTokenItem->getId() != $tblToken->getId())
                    && !Account::useService()->getAccountAllByToken($tblTokenItem)
                ){
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
        if ($tblToken){
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
        if ($tblAccount){
            $User = Account::useService()->getUserAllByAccount($tblAccount);
        }
        if (!empty($User)){
            $tblPerson = $User[0]->getServiceTblPerson();
        } else {
            $tblPerson = false;
        }

        $tblPersonAll = array();
        if (!$tblPerson){
            $tblGroup = Group::useService()->getGroupByMetaTable('STAFF');
            if ($tblGroup){
                $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
            }
            if ($tblPersonAll){
                array_walk($tblPersonAll, function(TblPerson &$tblPersonItem) use ($tblPerson, $Global){
                    $tblPersonItem = array(
                        'Select'  => new RadioBox('Account[User]', '&nbsp;', $tblPersonItem->getId()),
                        'Person'  => $tblPersonItem->getLastFirstName(),
                        'Address' => $tblPersonItem->fetchMainAddress() ? $tblPersonItem->fetchMainAddress()->getGuiTwoRowString() : ''
                    );
                });
                $tblPersonAll = array_filter($tblPersonAll);
            }
        }

        $columns = array(
            'Select' => '',
            'Person' => 'Name',
            'Address' => 'Adresse'
        );

        $interactive =  array(
            'order' => array(
                array(1, 'asc'),
            ),
            'responsive' => false,
            'lengthMenu' => [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            'pageLength' => 5
        );

        // Person Panel
        if ($tblPerson) {
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
//                new Danger('AKTUELL hinterlegte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
//                new Danger('ODER eine andere Person wählen: '),
//                new TableData(
//                    $tblPersonAll,
//                    null,
//                    $columns,
//                    $interactive
//                )
            ), Panel::PANEL_TYPE_INFO);
        } elseif (isset( $Global->POST['Account']['User'] )) {
            $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($Global->POST['Account']['User']);
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
//                new Warning('AKTUELL selektierte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
//                new Danger('ODER eine andere Person wählen: '),
//                new TableData(
//                    $tblPersonAll,
//                    null,
//                    $columns,
//                    $interactive
//                ),
            ), Panel::PANEL_TYPE_INFO);
        } else {
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
                new TableData(
                    $tblPersonAll,
                    null,
                    $columns,
                    $interactive
                ),
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
                        $PanelToken,
                        $PanelPerson
                    ), 5),
                    new FormColumn(array(
                        new Panel(new Nameplate().' mit folgenden Berechtigungen', $tblRoleAll, Panel::PANEL_TYPE_INFO),
//                        $PanelPersonRight,
                    ), 7),
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

            $extraButton = '';
            if($tblSessionAccount = \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account::useService()->getAccountBySession()){
                if($tblIdentification = $tblSessionAccount->getServiceTblIdentification()){
                    if($tblIdentification->getName() == 'System'){
                        $extraButton = new Center(new ToggleCheckbox('Alles auswählen/abwählen', $this->formAccount($tblAccount)));
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Well(
                                $extraButton
                                .Account::useService()->changeAccountForm(
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
     * @param int   $tblAccountId
     * @param array $Account
     * @param bool  $confirm
     *
     * @return \SPHERE\Common\Frontend\Form\IFormInterface|Panel|string
     */
    public function frontendConfirmChange($tblAccountId, $Account, $confirm = false)
    {

        $tblAccount = Account::useService()->getAccountById($tblAccountId);
        $Panel = new Panel('Alle Benutzerrrechte dieses Accounts werden entfernt. Wollen Sie diese Änderung vornehmen?',
            new \SPHERE\Common\Frontend\Link\Repository\Danger('Ja', '/Setting/Authorization/Account/Edit/Confirm', new Ok(),
                array(
                    'tblAccountId' => $tblAccount->getId(),
                    'Account'    => $Account,
                    'confirm'    => true
                ))
            . new Standard('Nein', ''), Panel::PANEL_TYPE_DANGER);
        if($confirm){
            unset($Account['Role']);
            return Account::useService()->changeAccount($tblAccount->getId(), $Account);
        }

        return $Panel;
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
