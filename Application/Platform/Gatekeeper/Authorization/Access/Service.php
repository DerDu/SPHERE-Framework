<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilegeRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Service extends AbstractService
{

    /** @var array $AuthorizationRequest */
    private static $AuthorizationRequest = array();
    /** @var array $AuthorizationCache */
    private static $AuthorizationCache = array();
    /** @var TblRole[] $RoleByIdCache */
    private static $RoleByIdCache = array();
    /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[] $LevelByIdCache */
    private static $LevelByIdCache = array();
    /** @var TblPrivilege[] $PrivilegeByIdCache */
    private static $PrivilegeByIdCache = array();

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
     * @param string $Route
     *
     * @return bool
     */
    public function hasAuthorization($Route)
    {

        // Sanitize Route
        $Route = '/'.trim($Route, '/');

        // Cache
        $this->hydrateAuthorization();
        if (in_array($Route, self::$AuthorizationCache) || in_array($Route, self::$AuthorizationRequest)) {
            return true;
        }
        if ($this->existsRightByName($Route) || preg_match('!^/Api/!is', $Route)) {
            // MUST BE protected -> Access denied
            return false;
        } else {
            // Access valid PUBLIC -> Access granted
            self::$AuthorizationRequest[] = $Route;
            return true;
        }
    }

    private function hydrateAuthorization()
    {

        if (empty( self::$AuthorizationCache )) {
            if (false !== ( $tblAccount = Account::useService()->getAccountBySession() )) {
                $Cache = $this->getCache(new MemcachedHandler());
                if (!( $AuthorizationCache = $Cache->getValue($tblAccount->getId(), __METHOD__) )) {
                    if (false !== ( $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount) )) {
                        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization $tblAuthorization */
                        foreach ($tblAuthorizationAll as $tblAuthorization) {
                            $tblRole = $tblAuthorization->getServiceTblRole();
                            if ($tblRole && (false !== ( $tblLevelAll = $tblRole->getTblLevelAll() ))) {
                                /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel */
                                foreach ($tblLevelAll as $tblLevel) {
                                    $tblPrivilegeAll = $tblLevel->getTblPrivilegeAll();
                                    if ($tblPrivilegeAll) {
                                        /** @var TblPrivilege $tblPrivilege */
                                        foreach ($tblPrivilegeAll as $tblPrivilege) {
                                            $tblRightAll = $tblPrivilege->getTblRightAll();
                                            if ($tblRightAll) {
                                                /** @var TblRight $tblRight */
                                                foreach ($tblRightAll as $tblRight) {
                                                    if (!in_array($tblRight->getRoute(), self::$AuthorizationCache)) {
                                                        array_push(self::$AuthorizationCache, $tblRight->getRoute());
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $Cache->setValue($tblAccount->getId(), self::$AuthorizationCache, 0, __METHOD__);
                } else {
                    self::$AuthorizationCache = $AuthorizationCache;
                }
            }
        }
    }

    /**
     * @param string $Name
     *
     * @return bool
     */
    public function existsRightByName($Name)
    {

        return (new Data($this->getBinding()))->existsRightByName($Name);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRight
     */
    public function getRightByName($Name)
    {

        return (new Data($this->getBinding()))->getRightByName($Name);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRight
     */
    public function getRightById($Id)
    {

        return (new Data($this->getBinding()))->getRightById($Id);
    }

    /**
     * @return bool|TblRight[]
     */
    public function getRightAll()
    {

        return (new Data($this->getBinding()))->getRightAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createRight(IFormInterface $Form, $Name)
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError('Name', 'Bitte geben Sie einen Namen ein');
        }
        if (!empty( $Name )) {
            $Form->setSuccess('Name', 'Das Recht wurde hinzugefügt');
            (new Data($this->getBinding()))->createRight($Name);
            return new Redirect('/Platform/Gatekeeper/Authorization/Access/Right', 0);
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createPrivilege(IFormInterface $Form, $Name)
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError('Name', 'Bitte geben Sie einen Namen ein');
        }
        if (!empty( $Name )) {
            $Form->setSuccess('Name', 'Das Privileg wurde hinzugefügt');
            (new Data($this->getBinding()))->createPrivilege($Name);
            return new Redirect('/Platform/Gatekeeper/Authorization/Access/Privilege', 0);
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeById($Id)
    {

        if (array_key_exists($Id, self::$PrivilegeByIdCache)) {
            return self::$PrivilegeByIdCache[$Id];
        }
        self::$PrivilegeByIdCache[$Id] = (new Data($this->getBinding()))->getPrivilegeById($Id);
        return self::$PrivilegeByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeByName($Name)
    {

        return (new Data($this->getBinding()))->getPrivilegeByName($Name);
    }

    /**
     * @return bool|TblPrivilege[]
     */
    public function getPrivilegeAll()
    {

        return (new Data($this->getBinding()))->getPrivilegeAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createLevel(IFormInterface $Form, $Name)
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError('Name', 'Bitte geben Sie einen Namen ein');
        }
        if (!empty( $Name )) {
            $Form->setSuccess('Name', 'Das Zugriffslevel wurde hinzugefügt');
            (new Data($this->getBinding()))->createLevel($Name);
            return new Redirect('/Platform/Gatekeeper/Authorization/Access/Level', 0);
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelById($Id)
    {

        if (array_key_exists($Id, self::$LevelByIdCache)) {
            return self::$LevelByIdCache[$Id];
        }
        self::$LevelByIdCache[$Id] = (new Data($this->getBinding()))->getLevelById($Id);
        return self::$LevelByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelByName($Name)
    {

        return (new Data($this->getBinding()))->getLevelByName($Name);
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[]
     */
    public function getLevelAll()
    {

        return (new Data($this->getBinding()))->getLevelAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @param bool           $IsSecure
     *
     * @return IFormInterface|Redirect
     */
    public function createRole(IFormInterface $Form, $Name, $IsSecure = false)
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError('Name', 'Bitte geben Sie einen Namen ein');
        }
        if (!empty( $Name )) {
            $Form->setSuccess('Name', 'Die Rolle wurde hinzugefügt');
            (new Data($this->getBinding()))->createRole($Name, $IsSecure);
            return new Redirect('/Platform/Gatekeeper/Authorization/Access/Role', Redirect::TIMEOUT_SUCCESS);
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRole
     */
    public function getRoleById($Id)
    {

        if (array_key_exists($Id, self::$RoleByIdCache)) {
            return self::$RoleByIdCache[$Id];
        }
        self::$RoleByIdCache[$Id] = (new Data($this->getBinding()))->getRoleById($Id);
        return self::$RoleByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole
     */
    public function getRoleByName($Name)
    {

        return (new Data($this->getBinding()))->getRoleByName($Name);
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole[]
     */
    public function getRoleAll()
    {

        return (new Data($this->getBinding()))->getRoleAll();
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole $tblRole
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[]
     */
    public function getLevelAllByRole(TblRole $tblRole)
    {

        return (new Data($this->getBinding()))->getLevelAllByRole($tblRole);
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     *
     * @return bool|TblRight[]
     */
    public function getRightAllByPrivilege(TblPrivilege $tblPrivilege)
    {

        return (new Data($this->getBinding()))->getRightAllByPrivilege($tblPrivilege);
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege[]
     */
    public function getPrivilegeAllByLevel(TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->getPrivilegeAllByLevel($tblLevel);
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole  $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel
     */
    public function addRoleLevel(TblRole $tblRole, TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->addRoleLevel($tblRole, $tblLevel);
    }

    /**
     * @param TblRole                                                                              $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel
     */
    public function removeRoleLevel(TblRole $tblRole, TblLevel $tblLevel)
    {

        return (new Data($this->getBinding()))->removeRoleLevel($tblRole, $tblLevel);
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool
     */
    public function removeLevelPrivilege(TblLevel $tblLevel, TblPrivilege $tblPrivilege)
    {

        return (new Data($this->getBinding()))->removeLevelPrivilege($tblLevel, $tblPrivilege);
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return bool
     */
    public function removePrivilegeRight(TblPrivilege $tblPrivilege, TblRight $tblRight)
    {

        return (new Data($this->getBinding()))->removePrivilegeRight($tblPrivilege, $tblRight);
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return TblPrivilegeRight
     */
    public function addPrivilegeRight(TblPrivilege $tblPrivilege, TblRight $tblRight)
    {

        return (new Data($this->getBinding()))->addPrivilegeRight($tblPrivilege, $tblRight);
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege
     */
    public function addLevelPrivilege(TblLevel $tblLevel, TblPrivilege $tblPrivilege)
    {

        return (new Data($this->getBinding()))->addLevelPrivilege($tblLevel, $tblPrivilege);
    }
}
