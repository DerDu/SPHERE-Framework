<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
{

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param string         $CredentialLock
     * @param string         $CredentialLockSafety
     *
     * @return IFormInterface|Redirect|string
     */
    public function updatePassword(
        IFormInterface $Form,
        TblAccount $tblAccount,
        $CredentialLock,
        $CredentialLockSafety
    ) {

        if (null === $CredentialLock
            && null === $CredentialLockSafety
        ) {
            return $Form;
        }

        $Error = false;

        if (empty( $CredentialLock )) {
            $Form->setError('CredentialLock', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        } else {
            if (strlen($CredentialLock) >= 8) {
                $Form->setSuccess('CredentialLock', '');
            } else {
                $Form->setError('CredentialLock', 'Das Passwort muss mindestens 8 Zeichen lang sein');
                $Form->setError('CredentialLockSafety', '');
                $Error = true;
            }
        }

        if (empty( $CredentialLockSafety )) {
            $Form->setError('CredentialLockSafety', 'Bitte geben Sie ein Passwort an');
            $Error = true;
        }
        if ($CredentialLock != $CredentialLockSafety && !$Error) {
            $Form->setError('CredentialLock', '');
            $Form->setError('CredentialLockSafety', 'Die beiden Passworte stimmen nicht überein');
            $Error = true;
        } elseif (!$Error) {
            if (!empty( $CredentialLock ) && !empty( $CredentialLockSafety )) {
                $Form->setSuccess('CredentialLock', '');
                $Form->setSuccess('CredentialLockSafety', '');
            } else {
                $Form->setError('CredentialLock', '');
                $Form->setError('CredentialLockSafety', '');
            }
        }

        // are enough criteria matched?
        $Step = 0;
        if ($CredentialLock && !$Error) {
            if (preg_match('![a-z]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![A-Z]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![0-9]!s', $CredentialLock)) {
                $Step++;
            }
            if (preg_match('![^\w\d]!s', $CredentialLock)) {
                $Step++;
            }
            // min 3 criteria
            if ($Step < 3) {
                $Form->setError('CredentialLock', 'Nicht genügend Sicherheitskriterien erfüllt');
                $Form->setError('CredentialLockSafety', '');
                $Error = true;
            }
        }

        if ($Error) {
            return $Form;
        } else {
            $tblAccountUpdate = $tblAccount->getEntityUpdate();
            if (Account::useService()->changePassword($CredentialLock, $tblAccount)) {
                // erste PW Änderung von UserAccounts -> Weiterleitung Startseite
                if ($tblAccountUpdate === null) {
                    $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
                    if ($tblUserAccount) {
                        return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/',
                                Redirect::TIMEOUT_SUCCESS);
                    }
                }
                return new Success('Das Passwort wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Passwort konnte nicht geändert werden').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_ERROR);
            }
        }
    }

    /**
     * @param TblAccount  $tblAccount
     * @param TblConsumer $tblConsumer
     *
     * @return string
     */
    public function updateConsumer(
        TblAccount $tblAccount,
        TblConsumer $tblConsumer
    ) {

        if (Account::useService()->changeConsumer($tblConsumer, $tblAccount)) {
            return new Success('Der Mandant wurde erfolgreich geändert').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Der Mandant konnte nicht geändert werden').new Redirect('/Setting/MyAccount', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param array          $Setting
     *
     * @return IFormInterface|Redirect|string
     */
    public function updateSetting(
        IFormInterface $Form,
        TblAccount $tblAccount,
        $Setting
    ) {

        if (empty( $Setting )) {
            return $Form;
        }

        $Error = false;

        foreach ((array)$Setting as $Identifier => $Value) {
            if (!$this->setSettingByAccount($tblAccount, $Identifier, $Value)) {
                $Error = true;
            }
        }

        if ($Error) {
            return new Danger('Einige Einstellungen konnten nicht gespeichert werden').new Redirect('/Setting/MyAccount',
                Redirect::TIMEOUT_ERROR);
        } else {
            return new Success('Die Einstellungen wurden erfolgreich gespeichert').new Redirect('/Setting/MyAccount',
                Redirect::TIMEOUT_SUCCESS);
        }
    }

}
