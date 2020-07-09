<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccountInitial;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Token\YubiKey\ComponentException;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Account
 */
class Service extends AbstractService
{

    /** @var TblAccount[] $AccountByIdCache */
    private static $AccountByIdCache = array();
    /** @var TblIdentification[] $IdentificationByIdCache */
    private static $IdentificationByIdCache = array();

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblConsumer $tblConsumer
     * @return bool|TblGroup[]
     */
    public function getGroupAll(TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->getGroupAll($tblConsumer);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->getBinding()))->getGroupById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName($Name)
    {
        return (new Data($this->getBinding()))->getGroupByName($Name);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool
     */
    public function destroyGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->destroyGroup($tblGroup);
    }


    /**
     * @param null|string $Session
     *
     * @return bool|TblAccount
     */
    public function getAccountBySession($Session = null)
    {

        return (new Data($this->getBinding()))->getAccountBySession($Session);
    }

    /**
     * @return bool|string
     */
    public function getMandantAcronym()
    {

        if(($tblAccount = $this->getAccountBySession())){
            if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                return $tblConsumer->getAcronym();
            }
        }
        return false;
    }

    /**
     * @param string $Username
     *
     * @return bool|TblAccount
     */
    public function getAccountByUsername($Username)
    {

        return (new Data($this->getBinding()))->getAccountByUsername($Username);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById($Id)
    {

        if (array_key_exists($Id, self::$AccountByIdCache)) {
            return self::$AccountByIdCache[$Id];
        }
        self::$AccountByIdCache[$Id] = (new Data($this->getBinding()))->getAccountById($Id);
        return self::$AccountByIdCache[$Id];
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationById($Id)
    {

        if (array_key_exists($Id, self::$IdentificationByIdCache)) {
            return self::$IdentificationByIdCache[$Id];
        }
        self::$IdentificationByIdCache[$Id] = (new Data($this->getBinding()))->getIdentificationById($Id);
        return self::$IdentificationByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationByName($Name)
    {

        return (new Data($this->getBinding()))->getIdentificationByName($Name);
    }

    /**
     * @return TblIdentification[]|bool
     */
    public function getIdentificationAll()
    {

        return (new Data($this->getBinding()))->getIdentificationAll();
    }

    /**
     * @param null|Redirect $Redirect
     * @param null|string $Session
     *
     * @return bool|Redirect
     */
    public function destroySession(Redirect $Redirect = null, $Session = null)
    {

        if (null === $Session) {
            (new Data($this->getBinding()))->destroySession($Session);
            if (!headers_sent()) {
                // Destroy Cookie
                $params = session_get_cookie_params();
                setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'],
                    isset($params['httponly']));
                session_start();
                // Generate New Id
                session_regenerate_id(true);
            }
            $this->getCache(new MemcachedHandler())->clearSlot('PUBLIC');
            return $Redirect;
        } else {
            return (new Data($this->getBinding()))->destroySession($Session);
        }
    }

    /**
     * @param IFormInterface $Form
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param TblIdentification $tblIdentification
     *
     * @return IFormInterface|string
     */
    public function createSessionCredential(
        IFormInterface &$Form,
        $CredentialName,
        $CredentialLock,
        TblIdentification $tblIdentification
    )
    {

        if ($tblIdentification->isActive()) {
            switch ($this->isCredentialValid($CredentialName, $CredentialLock, false, $tblIdentification)) {
                case false: {
                    if (null !== $CredentialName && empty($CredentialName)) {
                        $Form->setError('CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein');
                    }
                    if (null !== $CredentialName && !empty($CredentialName)) {
                        $Form->setError('CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein');
                    }
                    if (null !== $CredentialLock && empty($CredentialLock)) {
                        $Form->setError('CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein');
                    }
                    if (null !== $CredentialLock && !empty($CredentialLock)) {
                        $Form->setError('CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein');
                    }
                    break;
                }
                case true: {
                    return new Redirect('/', Redirect::TIMEOUT_SUCCESS);
                    break;
                }
            }
        } else {
            if ($CredentialName || $CredentialLock) {
                return new Warning('Die Anmeldung mit Benutzername und Passwort ist derzeit leider deaktiviert')
                    . new Redirect('/', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param bool|string $TokenString
     * @param TblIdentification $tblIdentification
     *
     * @return bool|null
     * @throws \Exception
     */
    private function isCredentialValid($Username, $Password, $TokenString, TblIdentification $tblIdentification)
    {

        if (false === ($tblAccount = $this->getAccountByCredential($Username, $Password, $tblIdentification))) {
            return false;
        } else {
            if (false === $TokenString) {
                if (session_status() == PHP_SESSION_ACTIVE) {
                    session_regenerate_id();
                }
                $this->createSession($tblAccount, session_id());
                return true;
            } else {
                try {
                    if (Token::useService()->isTokenValid($TokenString)) {
                        if (false === ($Token = $tblAccount->getServiceTblToken())) {
                            return null;
                        } else {
                            if ($Token->getIdentifier() == substr($TokenString, 0, 12)) {
                                if (session_status() == PHP_SESSION_ACTIVE) {
                                    session_regenerate_id();
                                }
                                $this->createSession($tblAccount, session_id());
                                return true;
                            } else {
                                return null;
                            }
                        }
                    } else {
                        return null;
                    }
                } catch (\Exception $E) {
                    if ($E instanceof ComponentException) {
                        return null;
                    }
                    throw $E;
                }
            }
        }
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param TblIdentification $tblIdentification
     *
     * @return bool|TblAccount
     */
    public function getAccountByCredential($Username, $Password, TblIdentification $tblIdentification = null)
    {

        $tblAccount = (new Data($this->getBinding()))->getAccountByCredential($Username, $Password, $tblIdentification);

        // Credential can be also UserCredential
        if (!$tblAccount && null !== $tblIdentification && $tblIdentification->getName() == 'Credential') {
            $tblIdentification = Account::useService()->getIdentificationByName('UserCredential');
            if ($tblIdentification) {
                $tblAccount = (new Data($this->getBinding()))->getAccountByCredential($Username, $Password,
                    $tblIdentification);
            }
        }

        return $tblAccount;
    }

    /**
     * @param TblAccount $tblAccount
     * @param null|string $Session
     * @param integer $Timeout
     *
     * @return Service\Entity\TblSession
     */
    public function createSession(TblAccount $tblAccount, $Session = null, $Timeout = 1800)
    {

        return (new Data($this->getBinding()))->createSession($tblAccount, $Session, $Timeout);
    }

    /**
     * @param IFormInterface $Form
     * @param string $CredentialName
     * @param string $CredentialLock
     * @param string $CredentialKey
     * @param TblIdentification $tblIdentification
     *
     * @return IFormInterface|string
     */
    public function createSessionCredentialToken(
        IFormInterface &$Form,
        $CredentialName,
        $CredentialLock,
        $CredentialKey,
        TblIdentification $tblIdentification
    )
    {

        if ($tblIdentification->isActive()) {
            $Auth = $this->isCredentialValid($CredentialName, $CredentialLock, $CredentialKey, $tblIdentification);
            if ($Auth === false) {
                if (null !== $CredentialName && empty($CredentialName)) {
                    $Form->setError('CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein');
                }
                if (null !== $CredentialName && !empty($CredentialName)) {
                    $Form->setError('CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein');
                }
                if (null !== $CredentialLock && empty($CredentialLock)) {
                    $Form->setError('CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein');
                }
                if (null !== $CredentialLock && !empty($CredentialLock)) {
                    $Form->setError('CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein');
                }
            }
            if ($Auth === null) {
                $Form->setSuccess('CredentialName', '');
                $Form->setSuccess('CredentialLock', '');
                $Form->setError('CredentialKey', 'Der von Ihnen angegebene YubiKey konnte nicht überprüft werden.'
                    . '<br/>Bitte versuchen Sie es erneut und verwenden Sie Ihren YubiKey um dieses Feld zu befüllen.');
            }
            if ($Auth === true) {
                return new Success('Anmeldung erfolgreich', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS);
            }
        } else {
            if ($CredentialKey) {
                return new Warning('Die Anmeldung mit Hardware-Token ist derzeit leider deaktiviert')
                    . new Redirect('/', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    /**
     * @return TblAccount[]|bool
     */
    public function getAccountAll()
    {

        return (new Data($this->getBinding()))->getAccountAll();
    }

    /**
     * @return TblSession[]|bool
     */
    public function getSessionAll()
    {

        return (new Data($this->getBinding()))->getSessionAll();
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByToken(TblToken $tblToken)
    {

        return (new Data($this->getBinding()))->getAccountAllByToken($tblToken);
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblRole $tblRole
     *
     * @return bool
     */
    public function hasAuthorization(TblAccount $tblAccount, TblRole $tblRole)
    {

        $tblAuthorization = $this->getAuthorizationAllByAccount($tblAccount);
        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAuthorization, function (TblAuthorization &$tblAuthorization) use ($tblRole) {

            if ($tblAuthorization->getServiceTblRole()
                && $tblAuthorization->getServiceTblRole()->getId() != $tblRole->getId()
            ) {
                $tblAuthorization = false;
            }
        });
        $tblAuthorization = array_filter($tblAuthorization);
        if (!empty($tblAuthorization)) {
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAuthorization[]
     */
    public function getAuthorizationAllByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getAuthorizationAllByAccount($tblAccount);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAuthentication
     */
    public function getAuthenticationByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getAuthenticationByAccount($tblAccount);

    }

    /**
     * @param IFormInterface $Form
     * @param array $Group
     * @param Pipeline|null $pipelineSuccess
     * @return IFormInterface|string
     */
    public function createGroup(
        IFormInterface $Form,
        $Group,
        Pipeline $pipelineSuccess = null
    )
    {

        /**
         * Service
         */
        if ($Group === null) {
            return $Form;
        }

        $Error = false;

        if (!isset($Group['Name']) || empty($Group['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            if ((Account::useService()->getGroupByName($Group['Name']))) {
                $Form->setError('Group[Name]', 'Der angegebene Name wird bereits verwendet');
                $Error = true;
            }
        }
        if (!isset($Group['Description'])) {
            $Form->setError('Group[Description]', 'Bitte geben Sie eine Beschreibung ein');
            $Error = true;
        }

        if ($Error) {
            return $Form . new Notify(
                    'Benutzergruppe konnte nicht angelegt werden',
                    'Bitte füllen Sie die benötigten Felder korrekt aus',
                    Notify::TYPE_WARNING,
                    5000
                );
        } else {

            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblConsumer) {
                if ($this->insertGroup($Group['Name'], $Group['Description'], $tblConsumer)) {
                    return $Form. new Notify(
                            'Benutzergruppe ' . $Group['Name'],
                            'Erfolgreich angelegt',
                            Notify::TYPE_SUCCESS
                        ).($pipelineSuccess ? $pipelineSuccess : '');
                }
            }
            return $Form . new Notify(
                    'Benutzergruppe ' . $Group['Name'],
                    'Konnte nicht angelegt werden',
                    Notify::TYPE_DANGER,
                    5000
                );
        }
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup $tblGroup
     * @param array $Group
     * @param Pipeline|null $pipelineSuccess
     * @return IFormInterface|string
     */
    public function editGroup(
        IFormInterface $Form,
        TblGroup $tblGroup,
        $Group,
        Pipeline $pipelineSuccess = null
    )
    {

        /**
         * Service
         */
        if ($Group === null) {
            return $Form;
        }

        $Error = false;

        if (!isset($Group['Name']) || empty($Group['Name'])) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            if (
                ($tblGroupExists = Account::useService()->getGroupByName($Group['Name']))
                && $tblGroupExists->getId() != $tblGroup->getId()
            ) {
                $Form->setError('Group[Name]', 'Der angegebene Name wird bereits verwendet');
                $Error = true;
            }
        }
        if (!isset($Group['Description'])) {
            $Form->setError('Group[Description]', 'Bitte geben Sie eine Beschreibung ein');
            $Error = true;
        }

        if ($Error) {
            return $Form . new Notify(
                    'Benutzergruppe konnte nicht geändert werden',
                    'Bitte füllen Sie die benötigten Felder korrekt aus',
                    Notify::TYPE_WARNING,
                    5000
                );
        } else {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblConsumer) {
                if ($this->changeGroup( $tblGroup, $Group['Name'], $Group['Description'], $tblConsumer)) {
                    return new Notify(
                            'Benutzergruppe ' . $Group['Name'],
                            'Erfolgreich geändert',
                            Notify::TYPE_SUCCESS
                        ).($pipelineSuccess ? $pipelineSuccess : '');
                }
            }
            return $Form . new Notify(
                    'Benutzergruppe ' . $Group['Name'],
                    'Konnte nicht geändert werden',
                    Notify::TYPE_DANGER,
                    5000
                );
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param null|TblConsumer $tblConsumer
     *
     * @return TblGroup
     */
    public function insertGroup($Name, $Description, TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->createGroup($Name, $Description, $tblConsumer);
    }

    /**
     * @param TblGroup $tblGroup
     * @param string $Name
     * @param string $Description
     * @param null|TblConsumer $tblConsumer
     *
     * @return false|TblGroup
     */
    public function changeGroup( TblGroup $tblGroup, $Name, $Description, TblConsumer $tblConsumer = null)
    {

        return (new Data($this->getBinding()))->changeGroup( $tblGroup, $Name, $Description, $tblConsumer);
    }


    /**
     * @param string           $Username
     * @param string           $Password
     * @param null|TblToken    $tblToken
     * @param null|TblConsumer $tblConsumer
     * @param bool             $SaveInitialPW
     *
     * @return TblAccount
     */
    public function insertAccount($Username, $Password, TblToken $tblToken = null, TblConsumer $tblConsumer = null, $SaveInitialPW = false)
    {

        $tblAccount = (new Data($this->getBinding()))->createAccount($Username, $Password, $tblToken, $tblConsumer);
        if($SaveInitialPW){
            (new Data($this->getBinding()))->createAccountInitial($tblAccount);
        }
        return $tblAccount;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAccountInitial
     */
    public function getAccountInitialByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getAccountInitialByAccount($tblAccount);
    }


    public function isAccountPWInitial(TblAccount $tblAccount)
    {
        if(($tblAccountInitial = $this->getAccountInitialByAccount($tblAccount))){
            if($tblAccount->getPassword() == $tblAccountInitial->getPassword()){
                return true;
            }
        }
        return false;

    }

    /**
     * @param TblAccount $tblAccount
     * @param TblIdentification $tblIdentification
     *
     * @return TblAuthentication
     */
    public function addAccountAuthentication(TblAccount $tblAccount, TblIdentification $tblIdentification)
    {

        return (new Data($this->getBinding()))->addAccountAuthentication($tblAccount, $tblIdentification);
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblRole $tblRole
     *
     * @return TblAuthorization
     */
    public function addAccountAuthorization(TblAccount $tblAccount, TblRole $tblRole)
    {

        return (new Data($this->getBinding()))->addAccountAuthorization($tblAccount, $tblRole);
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAllHavingNoAccount()
    {

        return (new Data($this->getBinding()))->getPersonAllHavingNoAccount();
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     *
     * @return TblUser
     */
    public function addAccountPerson(TblAccount $tblAccount, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->addAccountPerson($tblAccount, $tblPerson);
    }

    /**
     * @deprecated -> no person change from account
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeAccountPerson(TblAccount $tblAccount, TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->removeAccountPerson($tblAccount, $tblPerson);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getPersonAllByAccount($tblAccount);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $isForce
     *
     * @return false|TblAccount[]
     */
    public function getAccountAllByPerson(TblPerson $tblPerson, $isForce = false)
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        return (new Data($this->getBinding()))->getAccountAllByPerson($tblPerson, $tblConsumer, $isForce);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblAccount[]
     */
    public function getAccountListByActiveConumser()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        return (new Data($this->getBinding()))->getAccountListByConumser($tblConsumer);
    }

    /**
     * @param TblIdentification $tblIdentification
     *
     * @return TblAccount[]|bool
     */
    public function getAccountListByIdentification(TblIdentification $tblIdentification)
    {

        $returnList = array();
        if(($tblAccountList = $this->getAccountListByActiveConumser())){
            foreach($tblAccountList as $tblAccount){

                if(($tblIdentificationSet = $tblAccount->getServiceTblIdentification())){
                    if($tblIdentificationSet->getId() == $tblIdentification->getId()){
                        $returnList[] = $tblAccount;
                    }
                }
            }
        }

        return (!empty($returnList) ? $returnList : false);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblUser[]
     */
    public function getUserAllByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getUserAllByAccount($tblAccount);

    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblSession[]
     */
    public function getSessionAllByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getSessionAllByAccount($tblAccount);
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblRole $tblRole
     *
     * @return bool
     */
    public function removeAccountAuthorization(TblAccount $tblAccount, TblRole $tblRole)
    {

        return (new Data($this->getBinding()))->removeAccountAuthorization($tblAccount, $tblRole);
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblIdentification $tblIdentification
     *
     * @return bool
     */
    public function removeAccountAuthentication(TblAccount $tblAccount, TblIdentification $tblIdentification)
    {

        return (new Data($this->getBinding()))->removeAccountAuthentication($tblAccount, $tblIdentification);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->destroyAccount($tblAccount);
    }

    /**
     * @param string $Password
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changePassword($Password, TblAccount $tblAccount = null)
    {

        return (new Data($this->getBinding()))->changePassword($Password, $tblAccount);
    }

    /**
     * @param string     $Password (sha256) no clear text
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function resetPassword($Password, TblAccount $tblAccount = null)
    {

        return (new Data($this->getBinding()))->resetPassword($Password, $tblAccount);
    }

    /**
     * @param TblConsumer $tblConsumer
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeConsumer(TblConsumer $tblConsumer, TblAccount $tblAccount = null)
    {

        return (new Data($this->getBinding()))->changeConsumer($tblConsumer, $tblAccount);
    }

    /**
     * @param TblToken $tblToken
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeToken(TblToken $tblToken = null, TblAccount $tblAccount = null)
    {

        return (new Data($this->getBinding()))->changeToken($tblToken, $tblAccount);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     *
     * @return bool|TblSetting
     */
    public function getSettingByAccount(TblAccount $tblAccount, $Identifier)
    {

        return (new Data($this->getBinding()))->getSettingByAccount($tblAccount, $Identifier);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     * @param string $Value
     *
     * @return bool|TblSetting
     */
    public function setSettingByAccount(TblAccount $tblAccount, $Identifier, $Value)
    {

        return (new Data($this->getBinding()))->setSettingByAccount($tblAccount, $Identifier, $Value);
    }

    /**
     * @param TblSetting $tblSetting
     *
     * @return bool
     */
    public function destroySetting(TblSetting $tblSetting)
    {

        return (new Data($this->getBinding()))->destroySetting($tblSetting);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblSetting[]
     */
    public function getSettingAllByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getSettingAllByAccount($tblAccount);
    }

    /**
     * @param $UserAlias
     *
     * @return false|TblAccount[]
     */
    public function getAccountAllByUserAlias($UserAlias)
    {

        return (new Data($this->getBinding()))->getAccountAllByUserAlias($UserAlias);
    }

    /**
     * @return int
     */
    public function countSessionAll()
    {

        return (new Data($this->getBinding()))->countSessionAll();
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $userAlias
     *
     * @return bool
     */
    public function changeUserAlias(TblAccount $tblAccount, $userAlias)
    {
        return (new Data($this->getBinding()))->changeUserAlias($tblAccount, $userAlias);
    }
}
