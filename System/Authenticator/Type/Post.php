<?php
namespace SPHERE\System\Authenticator\Type;

use SPHERE\System\Authenticator\ITypeInterface;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Post
 *
 * @package SPHERE\System\Authenticator\Type
 */
class Post extends Extension implements ITypeInterface
{

    /** @var string $Secret */
    private $Secret = '';

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration)
    {

        $this->Secret = $Configuration['Secret'];
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {

        return 'Post';
    }

    /**
     * @return bool|null
     */
    public function validateSignature()
    {

        $Global = $this->getGlobal();
        array_walk_recursive($Global->POST, array($this, 'preventXSS'));
        array_walk_recursive($Global->POST, array($this, 'trimInput'));
        $Global->savePost();

        return true;
    }

    /**
     * @param array       $Data
     * @param null|string $Location
     *
     * @return array
     */
    public function createSignature($Data, $Location = null)
    {

        // MUST NOT USE
        $this->getLogger(new ErrorLogger())
            ->addLog(__METHOD__.' Error: SIGNATURE - MUST NOT BE USED!');
        return array();
    }

    /**
     * @param $Value
     */
    protected function trimInput(&$Value)
    {

        $Value = trim($Value);
    }

    /**
     * @param $Value
     */
    protected function preventXSS(&$Value)
    {

        $Value = strip_tags($Value);
    }
}
