<?php
namespace SPHERE\Application\Platform\System\Protocol\Service\Entity;

use SPHERE\System\Database\Fitting\Element;

/**
 * Class LoginAttemptHistory
 *
 * @package SPHERE\Application\Platform\System\Protocol\Service\Entity
 */
class LoginAttemptHistory extends Element
{
    /** @var null|string $CredentialName */
    protected $CredentialName;
    /** @var null|string $CredentialLock */
    protected $CredentialLock;
    /** @var null|string $CredentialKey */
    protected $CredentialKey = null;

    /**
     * @return null|string
     */
    public function getCredentialName()
    {
        return $this->CredentialName;
    }

    /**
     * @param null|string $CredentialName
     */
    public function setCredentialName($CredentialName)
    {
        $this->CredentialName = $CredentialName;
    }

    /**
     * @return null|string
     */
    public function getCredentialLock()
    {
        return $this->CredentialLock;
    }

    /**
     * @param null|string $CredentialLock
     */
    public function setCredentialLock($CredentialLock)
    {
        $this->CredentialLock = hash( 'sha256', $CredentialLock );
    }

    /**
     * @return null|string
     */
    public function getCredentialKey()
    {
        return $this->CredentialKey;
    }

    /**
     * @param null|string $CredentialKey
     */
    public function setCredentialKey($CredentialKey)
    {
        $this->CredentialKey = $CredentialKey;
    }


}
