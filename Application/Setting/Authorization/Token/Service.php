<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Token\YubiKey\BadOTPException;
use SPHERE\System\Token\YubiKey\ComponentException;
use SPHERE\System\Token\YubiKey\ReplayedOTPException;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Service extends \SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service
{

    /**
     * @param IFormInterface $Form
     * @param string         $CredentialKey
     * @param TblConsumer    $tblConsumer
     *
     * @return bool|TblToken
     */
    public function createToken(IFormInterface $Form, $CredentialKey, TblConsumer $tblConsumer = null)
    {

        try {
            if (null !== $CredentialKey && !empty( $CredentialKey )) {
                $this->isTokenValid($CredentialKey);
                if (false === $this->getTokenByIdentifier(substr($CredentialKey, 0, 12))) {
                    (new Data($this->getBinding()))->createToken(substr($CredentialKey, 0, 12), $tblConsumer);
                    return new Success('Der YubiKey wurde hinzugefügt')
                    .new Redirect('/Setting/Authorization/Token', Redirect::TIMEOUT_SUCCESS);
                } else {
                    $Form->setError('CredentialKey', 'Der von Ihnen angegebene YubiKey wurde bereits registriert');
                }
            } elseif (null !== $CredentialKey && empty( $CredentialKey )) {
                $Form->setError('CredentialKey', 'Bitte verwenden Sie Ihren YubiKey um dieses Feld zu befüllen');
            }
            return $Form;
        } catch (BadOTPException $E) {
            $Form->setError('CredentialKey',
                'Der von Ihnen angegebene YubiKey ist nicht gültig<br/>Bitte verwenden Sie einen YubiKey um dieses Feld zu befüllen'
            );
            return $Form;
        } catch (ReplayedOTPException $E) {
            $Form->setError('CredentialKey',
                'Der von Ihnen angegebene YubiKey wurde bereits verwendet<br/>Bitte verwenden Sie einen YubiKey um dieses Feld neu zu befüllen'
            );
            return $Form;
        } catch (ComponentException $E) {
            $Form->setError('CredentialKey',
                'Der YubiKey konnte nicht überprüft werden<br/>Bitte versuchen Sie es später noch einmal'
            );
            return $Form;
        }
    }
}
