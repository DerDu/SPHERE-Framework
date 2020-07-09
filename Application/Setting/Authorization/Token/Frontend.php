<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null|string $CredentialKey
     *
     * @return Stage
     */
    public function frontendYubiKey($CredentialKey = null)
    {

        $Stage = new Stage('Hardware-Schlüssel', 'Übersicht');

        $Stage->setContent(
            $this->frontendLayoutToken() .
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Token::useService()->createToken(
                                $this->formYubiKey()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
//                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $CredentialKey, Consumer::useService()->getConsumerBySession())
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @return Layout
     */
    public function frontendLayoutToken()
    {

        $headerList = array(
            'Serial' => 'Seriennummer',
            'Link' => 'Verknüpfung',
            'Accounts' => 'Benutzerkonten',
            'Option' => ''
        );

        $dataList = array();
        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());

        if ($tblTokenAll !== false) {
            foreach ($tblTokenAll as $tblToken) {

                $Serial = $tblToken->getSerial();
                $Serial = substr($Serial, 0, 4) . ' ' . substr($Serial, 4, 4);

                $Content = array();
                $tblAccountAll = $tblToken->getAccountAllByToken();
                if ($tblAccountAll) {
                    array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                        $tblAccount = new PullClear(
                            new PullLeft(new PersonKey() . ' ' . $tblAccount->getUsername())
                            . new PullRight(new Standard('',
                                    '/Setting/Authorization/Account/Edit',
                                    new PersonKey(), array('Id' => $tblAccount->getId()),
                                    'zu ' . $tblAccount->getUsername() . ' wechseln'
                                )
                            ));
                    });
                    $Content = array_merge($Content, $tblAccountAll);
                    $Content = array_filter($Content);

                    $dataList[] = array(
                        'Serial' => $Serial,
                        'Link' => new Info(new Exclamation() . ' ' . new Small('Benutzerkonten verknüpft')),
                        'Accounts' => implode('<br>', $Content),
                        'Option' => new Muted(new Small('Der Schlüssel kann nicht entfernt werden'))
                    );
                } else {
                    $dataList[] = array(
                        'Serial' => $Serial,
                        'Link' => new Muted(new Small('Keine Benutzerkonten verknüpft')),
                        'Accounts' => new Muted(new Small('Keine Benutzerkonten verknüpft')),
                        'Option' => new Standard('',
                            '/Setting/Authorization/Token/Destroy',
                            new Remove(), array('Id' => $tblToken->getId()),
                            'Schlüssel ' . $Serial . ' löschen'
                        )
                    );
                }
            }
        }

        if (empty($dataList)) {
            $layoutContent = new Warning('Keine Hardware-Schlüssel hinterlegt');
        } else {
            $layoutContent = new TableData(
                $dataList,
                null,
                $headerList
            );
        }

        return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($layoutContent)), new Title(
            new ListingTable() . ' Übersicht'
        )));
    }

    /**
     * @return Form
     */
    private function formYubiKey()
    {

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Neuer Hardware-Schlüssel', array(
                            (new PasswordField('CredentialKey', 'YubiKey', 'YubiKey'))->setAutoFocus(),
                        ), Panel::PANEL_TYPE_INFO)
                    )),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToken($Id, $Confirm = false)
    {

        $Stage = new Stage('Hardware-Schlüssel', 'Löschen');
        if ($Id) {
            $tblToken = Token::useService()->getTokenById($Id);
            $tblAccountAll = $tblToken->getAccountAllByToken();
            if (empty($tblAccountAll)) {
                if (!$Confirm) {

                    $Serial = $tblToken->getSerial();
                    $Serial = substr($Serial, 0, 4) . ' ' . substr($Serial, 4, 4);

                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel(new YubiKey() . ' Hardware-Schlüssel', array(
                                $Serial,
                                strtoupper($tblToken->getIdentifier())
                            ), Panel::PANEL_TYPE_SUCCESS),
                            new Panel(new Question() . ' Diesen Hardware-Schlüssel wirklich löschen?', array(),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Setting/Authorization/Token/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Setting/Authorization/Token', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Token::useService()->destroyToken($tblToken)
                                    ? new Success('Der Hardware-Schlüssel wurde gelöscht')
                                    : new Danger('Der Hardware-Schlüssel konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Setting/Authorization/Token', Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Der Hardware-Schlüssel kann nicht gelöscht werden'),
                            new Redirect('/Setting/Authorization/Token', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Hardware-Schlüssel konnte nicht gefunden werden'),
                        new Redirect('/Setting/Authorization/Token', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
