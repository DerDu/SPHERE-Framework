<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblAccount extends Element
{

    const ATTR_USERNAME = 'Username';
    const ATTR_PASSWORD = 'Password';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';
    const SERVICE_TBL_TOKEN = 'serviceTblToken';
    const ATTR_USER_ALIAS = 'UserAlias';

    /**
     * @Column(type="string")
     */
    protected $Username;
    /**
     * @Column(type="string")
     */
    protected $Password;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToken;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

    /**
     * @Column(type="string")
     */
    protected $UserAlias;

    /**
     * @param string $Username
     */
    public function __construct($Username)
    {

        $this->Username = $Username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param string $Password
     */
    public function setPassword($Password)
    {

        $this->Password = $Password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {

        return $this->Username;
    }

    /**
     * @param string $Username
     */
    public function setUsername($Username)
    {

        $this->Username = $Username;
    }

    /**
     * @return bool|TblConsumer
     */
    public function getServiceTblConsumer()
    {

        if (null === $this->serviceTblConsumer) {
            return false;
        } else {
            return Consumer::useService()->getConsumerById($this->serviceTblConsumer);
        }
    }

    /**
     * @param null|TblConsumer $tblConsumer
     */
    public function setServiceTblConsumer(TblConsumer $tblConsumer = null)
    {

        $this->serviceTblConsumer = ( null === $tblConsumer ? null : $tblConsumer->getId() );
    }

    /**
     * @return bool|TblToken
     */
    public function getServiceTblToken()
    {

        if (null === $this->serviceTblToken) {
            return false;
        } else {
            return Token::useService()->getTokenById($this->serviceTblToken);
        }
    }

    /**
     * @param null|TblToken $tblToken
     */
    public function setServiceTblToken(TblToken $tblToken = null)
    {

        $this->serviceTblToken = ( null === $tblToken ? null : $tblToken->getId() );
    }

    /**
     * @return bool|TblIdentification
     */
    public function getServiceTblIdentification()
    {

        $Authentication = Account::useService()->getAuthenticationByAccount($this);
        if ($Authentication) {
            return $Authentication->getTblIdentification();
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getUserAlias()
    {
        return $this->UserAlias;
    }

    /**
     * @param string $UserAlias
     */
    public function setUserAlias($UserAlias)
    {
        $this->UserAlias = $UserAlias;
    }
}
