<?php
namespace SPHERE\System\Saml;

use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Saml
 *
 * @package SPHERE\System\Saml
 */
class Saml extends Extension
{

    /** @var ITypeInterface $Type */
    private $Type = null;

    /**
     * Saml constructor.
     *
     * @param ITypeInterface $Type
     */
    public function __construct(ITypeInterface $Type)
    {
        require_once(__DIR__ . '/2.10.2/_toolkit_loader.php');

        $this->Type = $Type;
        if ($this->Type->getConfiguration() !== null) {
            $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__);
            $Configuration = parse_ini_file(__DIR__.'/Configuration.ini', true);
            if (isset( $Configuration[$this->Type->getConfiguration()] )) {
                $this->Type->setConfiguration($Configuration[$this->Type->getConfiguration()]);
            }
        }
    }

    /**
     * @return ITypeInterface
     */
    public function getSaml()
    {

        return $this->Type;
    }
}