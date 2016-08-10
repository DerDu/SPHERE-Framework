<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Token\Token as HardwareToken;
use SPHERE\System\Token\Type\YubiKey;
use SPHERE\System\Token\YubiKey\BadOTPException;
use SPHERE\System\Token\YubiKey\ComponentException;
use SPHERE\System\Token\YubiKey\ReplayedOTPException;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Token
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblToken[]
     */
    public function getTokenAllByConsumer(TblConsumer $tblConsumer)
    {

        return (new Data($this->getBinding()))->getTokenAllByConsumer($tblConsumer);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    public function getTokenById($Id)
    {

        return (new Data($this->getBinding()))->getTokenById($Id);
    }

    /**
     * @return TblToken[]|bool
     */
    public function getTokenAll()
    {

        return (new Data($this->getBinding()))->getTokenAll();
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool
     */
    public function destroyToken(TblToken $tblToken)
    {

        return (new Data($this->getBinding()))->destroyToken($tblToken);
    }

    /**
     * @param  string                $Identifier (12)
     * @param TblConsumer|null $tblConsumer
     *
     * @return $this
     */
    public function insertToken($Identifier, TblConsumer $tblConsumer = null)
    {
        if (false === $this->getTokenByIdentifier($Identifier)) {
            ( new Data($this->getBinding()) )->createToken($Identifier, $tblConsumer);
        }
        return $this;
    }

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
                    if ((new Data($this->getBinding()))->createToken(substr($CredentialKey, 0, 12), $tblConsumer)) {
                        $Form->setSuccess('CredentialKey',
                            'Der YubiKey wurde hinzugefügt'.new Redirect('/Sphere/Management/Token', 3)
                        );
                    }
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

    /**
     * @param string $Value
     *
     * @return bool
     * @throws \Exception
     */
    public function isTokenValid($Value)
    {

        /** @var YubiKey $YubiKey */
        $YubiKey = (new HardwareToken(new YubiKey()))->getToken();
        $Key = $YubiKey->parseKey($Value);
        return $YubiKey->verifyKey($Key);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    public function getTokenByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getTokenByIdentifier($Identifier);
    }
}
