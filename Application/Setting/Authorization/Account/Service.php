<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access as GatekeeperAccess;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as GatekeeperAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token as GatekeeperToken;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{
    const MINIMAL_PASSWORD_LENGTH = 8;
    const MINIMAL_USERNAME_LENGTH = 3;

    /**
     * @param IFormInterface $Form
     * @param array          $Account
     *
     * @return IFormInterface|string
     */
    public function createAccount(IFormInterface $Form, $Account)
    {

        if (null === $Account) {

            return $Form;
        }

        $Error = false;

        $Username = trim($Account['Name']);
        $Password = trim($Account['Password']);
        $PasswordSafety = trim($Account['PasswordSafety']);

        $tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession();
        if (!isset( $Account['Token'] ) || !( $tblToken = GatekeeperToken::useService()->getTokenById((int)$Account['Token']) )) {
            $tblToken = null;
        }

        if (empty( $Username )) {
            $Form->setError('Account[Name]', 'Bitte geben Sie einen Benutzernamen an');
            $Error = true;
        } else {
            if (preg_match('!^[a-z0-9öäüß]{'.self::MINIMAL_USERNAME_LENGTH.',}$!is', $Username)) {
                $Username = $tblConsumer->getAcronym().'-'.$Username;
                if (!GatekeeperAccount::useService()->getAccountByUsername($Username)) {
                    $Form->setSuccess('Account[Name]', '');
                } else {
                    $Form->setError('Account[Name]', 'Der angegebene Benutzername ist bereits vergeben');
                    $Error = true;
                }
            } else {
                $Form->setError('Account[Name]',
                    'Der Benutzername darf nur Buchstaben und Zahlen enthalten und muss mindestens '.self::MINIMAL_USERNAME_LENGTH.' Zeichen lang sein');
                $Error = true;
            }
        }

        if (empty( $Password )) {
            $Form->setError('Account[Password]', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        } else {
            if (strlen($Password) >= self::MINIMAL_PASSWORD_LENGTH) {
                $Form->setSuccess('Account[Password]', '');
            } else {
                $Form->setError('Account[Password]', 'Das Passwort muss mindestens '.self::MINIMAL_PASSWORD_LENGTH.' Zeichen lang sein');
                $Error = true;
            }
        }

        if (empty( $PasswordSafety )) {
            $Form->setError('Account[PasswordSafety]', 'Bitte geben Sie das Passwort erneut an');
            $Error = true;
        }
        if ($Password != $PasswordSafety) {
            $Form->setError('Account[Password]', '');
            $Form->setError('Account[PasswordSafety]', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        } else {
            if (!empty( $Password ) && !empty( $PasswordSafety )) {
                $Form->setSuccess('Account[PasswordSafety]', '');
            } else {
                $Form->setError('Account[PasswordSafety]', '');
            }
        }

        if (!isset( $Account['User'] )) {
            $Form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie einen Besitzer des Kontos aus (Person wählen)'))))
            );
            $Error = true;
        }

        if (!$Error) {
            $tblAccount = GatekeeperAccount::useService()->insertAccount($Username, $Password, $tblToken, $tblConsumer, true);
            if ($tblAccount) {
                $tblIdentification = GatekeeperAccount::useService()->getIdentificationByName('Token');
                GatekeeperAccount::useService()->addAccountAuthentication($tblAccount, $tblIdentification);
                if (isset( $Account['Role'] )) {
                    foreach ((array)$Account['Role'] as $Role) {
                        $tblRole = GatekeeperAccess::useService()->getRoleById($Role);
                        if(
                            $tblIdentification->getName() == TblIdentification::NAME_CREDENTIAL
                            && !$tblRole->isSecure()
                        ) {
                            GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                        } else if (
                            !$tblRole->isSecure()
                            || (
                                $tblIdentification->getName() != TblIdentification::NAME_CREDENTIAL
                                && $tblToken
                            )
                        ) {
                            GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                        }
                    }
                }
                if (isset( $Account['User'] )) {
                    $tblPerson = Person::useService()->getPersonById($Account['User']);
                    if ($tblPerson) {
                        GatekeeperAccount::useService()->addAccountPerson($tblAccount, $tblPerson);
                    }
                }
                return new Success('Das Benutzerkonto wurde erstellt')
                .new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Benutzerkonto konnte nicht erstellt werden')
                .new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param array          $Account
     *
     * @return IFormInterface|string
     */
    public function changeAccountForm(IFormInterface $Form, TblAccount $tblAccount, $Account)
    {

        if (null === $Account) {

            return $Form;
        }

        $Error = false;

        $Password = trim($Account['Password']);
        $PasswordSafety = trim($Account['PasswordSafety']);

        if (!isset( $Account['Token'] ) || !( $tblToken = GatekeeperToken::useService()->getTokenById((int)$Account['Token']) )) {
            $tblToken = null;
        }

        if (!empty( $Password )) {
            if (strlen($Password) >= self::MINIMAL_PASSWORD_LENGTH) {
                $Form->setSuccess('Account[Password]', '');
            } else {
                $Form->setError('Account[Password]', 'Das Passwort muss mindestens '.self::MINIMAL_PASSWORD_LENGTH.' Zeichen lang sein');
                $Error = true;
            }
        }
        if (!empty( $Password ) && empty( $PasswordSafety )) {
            $Form->setError('Account[PasswordSafety]', 'Bitte geben Sie das Passwort erneut an');
            $Error = true;
        }
        if (!empty( $Password ) && $Password != $PasswordSafety) {
            $Form->setError('Account[Password]', '');
            $Form->setError('Account[PasswordSafety]', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        }

//        if (!isset( $Account['User'] )) {
//            $Form->prependGridGroup(
//                new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie einen Besitzer des Kontos aus (Person wählen)'))))
//            );
//            $Error = true;
//        }

        if (!$Error) {
            if ($tblAccount) {
                // entfernen aller Rechte nur, wenn ein Token entfernt wird
                if($tblAccount->getServiceTblToken()){
                    if($Account['Token'] === '0'){
                        return Account::useFrontend()->frontendConfirmChange($tblAccount->getId(), $Account);
                    }
                }

                // Edit Token
                GatekeeperAccount::useService()->changeToken($tblToken, $tblAccount);

                $tblIdentification = $tblAccount->getServiceTblIdentification();
                // there is no reason to delete/change the Identification (Support Account without Identification Error)
//                // Edit Identification (Authentication)
//                GatekeeperAccount::useService()->removeAccountAuthentication($tblAccount,
//                    $tblAccount->getServiceTblIdentification());
//                $tblIdentification = GatekeeperAccount::useService()->getIdentificationById($Account['Identification']);
//                GatekeeperAccount::useService()->addAccountAuthentication($tblAccount, $tblIdentification);

                // remove with SSW-927 no changes allowed
//                // Edit User
//                $tblPersonList = GatekeeperAccount::useService()->getPersonAllByAccount($tblAccount);
//                if ($tblPersonList) {
//                    foreach ($tblPersonList as $tblPersonRemove) {
//                        GatekeeperAccount::useService()->removeAccountPerson($tblAccount, $tblPersonRemove);
//                    }
//                }
//                $tblPerson = Person::useService()->getPersonById($Account['User']);
//                if ($tblPerson) {
//                    GatekeeperAccount::useService()->addAccountPerson($tblAccount, $tblPerson);
//                }

                // Edit Access
                $tblAccessList = GatekeeperAccount::useService()->getAuthorizationAllByAccount($tblAccount);
                if ($tblAccessList) {
                    foreach ($tblAccessList as $tblAccessRemove) {
                        GatekeeperAccount::useService()->removeAccountAuthorization($tblAccount,
                            $tblAccessRemove->getServiceTblRole());
                    }
                }
                if (isset( $Account['Role'] )) {
                    foreach ((array)$Account['Role'] as $Role) {
                        $tblRole = GatekeeperAccess::useService()->getRoleById($Role);
                        if(
                            $tblIdentification->getName() == TblIdentification::NAME_CREDENTIAL
                            && !$tblRole->isSecure()
                        ) {
                            GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                        } else if (
                            !$tblRole->isSecure()
                            || (
                                $tblIdentification->getName() != TblIdentification::NAME_CREDENTIAL
                                && $tblToken
                            )
                        ) {
                            GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                        }
                    }
                }

                // Edit Password
                if (!empty( $Password )) {
                    GatekeeperAccount::useService()->changePassword($Password, $tblAccount);
                }

                return new Success('Das Benutzerkonto wurde geändert')
                .new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Benutzerkonto konnte nicht geändert werden')
                .new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param int   $tblAccountId
     * @param array $Account
     *
     * @return IFormInterface|string
     */
    public function changeAccount($tblAccountId, $Account)
    {

        $tblAccount = Account::useService()->getAccountById($tblAccountId);

        $Error = false;
        $Password = trim($Account['Password']);
        $PasswordSafety = trim($Account['PasswordSafety']);

        if (!isset( $Account['Token'] ) || !( $tblToken = GatekeeperToken::useService()->getTokenById((int)$Account['Token']) )) {
            $tblToken = null;
        }

        if (!empty( $Password )) {
            if (!strlen($Password) >= self::MINIMAL_PASSWORD_LENGTH) {
                $Error = true;
            }
        }
        if (!empty( $Password ) && empty( $PasswordSafety )) {
            $Error = true;
        }
        if (!empty( $Password ) && $Password != $PasswordSafety) {
            $Error = true;
        }

        if (!isset( $Account['User'] )) {
            $Error = true;
        }

        $Stage = new Stage('Benutzerkonto', 'Bearbeiten');
        if (!$Error) {

            // Edit Token
            GatekeeperAccount::useService()->changeToken($tblToken, $tblAccount);

            $tblIdentification = $tblAccount->getServiceTblIdentification();
            // there is no reason to delete/change the Identification (Support Account without Identification Error)
//                // Edit Identification (Authentication)
//                GatekeeperAccount::useService()->removeAccountAuthentication($tblAccount,
//                    $tblAccount->getServiceTblIdentification());
//                $tblIdentification = GatekeeperAccount::useService()->getIdentificationById($Account['Identification']);
//                GatekeeperAccount::useService()->addAccountAuthentication($tblAccount, $tblIdentification);

            // Edit User
            $tblPersonList = GatekeeperAccount::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPersonRemove) {
                    GatekeeperAccount::useService()->removeAccountPerson($tblAccount, $tblPersonRemove);
                }
            }
            $tblPerson = Person::useService()->getPersonById($Account['User']);
            if ($tblPerson) {
                GatekeeperAccount::useService()->addAccountPerson($tblAccount, $tblPerson);
            }

            // Edit Access
            $tblAccessList = GatekeeperAccount::useService()->getAuthorizationAllByAccount($tblAccount);
            if ($tblAccessList) {
                foreach ($tblAccessList as $tblAccessRemove) {
                    GatekeeperAccount::useService()->removeAccountAuthorization($tblAccount,
                        $tblAccessRemove->getServiceTblRole());
                }
            }
            if (isset( $Account['Role'] )) {
                foreach ((array)$Account['Role'] as $Role) {
                    $tblRole = GatekeeperAccess::useService()->getRoleById($Role);
                    if(
                        $tblIdentification->getName() == TblIdentification::NAME_CREDENTIAL
                        && !$tblRole->isSecure()
                    ) {
                        GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    } else if (
                        !$tblRole->isSecure()
                        || (
                            $tblIdentification->getName() != TblIdentification::NAME_CREDENTIAL
                            && $tblToken
                        )
                    ) {
                        GatekeeperAccount::useService()->addAccountAuthorization($tblAccount, $tblRole);
                    }
                }
            }

            // Edit Password
            if (!empty( $Password )) {
                GatekeeperAccount::useService()->changePassword($Password, $tblAccount);
            }

            return $Stage->setContent(new Success('Das Benutzerkonto wurde geändert')
                .new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_SUCCESS));
        }

        return $Stage->setContent(new Danger('Das Benutzerkonto konnte nicht geändert werden')
            .new Redirect('/Setting/Authorization/Account/Edit', Redirect::TIMEOUT_ERROR, array('Id' => $tblAccount->getId())));
    }
}
