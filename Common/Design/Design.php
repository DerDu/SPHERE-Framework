<?php
namespace SPHERE\Common\Design;
use SPHERE\Common\Style;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;

/**
 * Class Design
 * @package SPHERE\Common\Design
 */
class Design
{
    /** @var null|Style $Style */
    private $Style = null;

    /**
     * Design constructor.
     */
    public function __construct()
    {
        $Confguration = (new ConfigFactory())->createReader(__DIR__ . '/Configuration.ini', new IniReader());

        $ThemeName = $Confguration->getValue( 'Design' )->getContainer( 'Theme' )->getValue();
        $ThemeClass = __NAMESPACE__.'\\'.$ThemeName.'\\'.$ThemeName;

        $this->Style = new Style();

        new $ThemeClass( $this->Style );
    }

    /**
     * @return null|Style
     */
    public function getStyle()
    {
        return $this->Style;
    }
}