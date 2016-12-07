<?php
namespace SPHERE\System\Extension;

use Markdownify\Converter;
use MOC\V\Component\Packer\Packer;
use MOC\V\Component\Template\Template;
use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\HandlerInterface;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Database\Fitting\Repository;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\LoggerInterface;
use SPHERE\System\Extension\Repository\DataTables;
use SPHERE\System\Extension\Repository\Debugger;
use SPHERE\System\Extension\Repository\ModHex;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\SuperGlobal;
use SPHERE\System\Extension\Repository\Upload;

/**
 * Class Extension
 *
 * @package SPHERE\System\Extension
 */
class Extension
{

    /**
     * @return \MOC\V\Core\HttpKernel\Component\IBridgeInterface
     */
    public static function getRequest()
    {

        return HttpKernel::getRequest();
    }

    /**
     * @param HandlerInterface $Handler
     * @return HandlerInterface
     */
    public function getCache(HandlerInterface $Handler)
    {

        return (new CacheFactory())->createHandler($Handler);
    }

    /**
     * @param string $Location Zip File
     *
     * @return \MOC\V\Component\Packer\Component\IBridgeInterface
     */
    public function getPacker($Location)
    {
        return Packer::getPacker($Location);
    }
    
    /**
     * @param LoggerInterface $Logger
     * @return LoggerInterface
     */
    public function getLogger(LoggerInterface $Logger)
    {

        return (new DebuggerFactory())->createLogger($Logger);
    }

    /**
     * @return Debugger
     */
    public function getDebugger()
    {

        return new Debugger();
    }

    /**
     * @param Repository $EntityRepository
     * @param array $Filter array( 'ColumnName' => 'Value', ... )
     *
     * @return DataTables
     */
    public function getDataTable(Repository $EntityRepository, $Filter = array())
    {

        return new DataTables($EntityRepository, $Filter);
    }

    /**
     * @param string $String
     *
     * @return ModHex
     */
    public function getModHex($String)
    {

        return ModHex::withString($String);
    }

    /**
     * @param string $Location
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function getTemplate($Location)
    {
        $Key = md5($Location);
        $Cache = $this->getCache(new MemoryHandler());
        if (!($Template = $Cache->getValue($Key, __METHOD__))) {
            $Template = Template::getTemplate($Location);
            $Cache->setValue($Key, $Template, 0, __METHOD__);
            return clone $Template;
        } else {
            return clone $Template;
        }
    }

    /**
     * @return SuperGlobal
     */
    public function getGlobal()
    {

        return new SuperGlobal($_GET, $_POST, $_SESSION);
    }

    /**
     * @param string $FileKey Key-Name in $_FILES
     * @param string $Location Storage-Directory
     * @param bool $Overwrite File in Storage-Directory
     *
     * @return Upload
     */
    public function getUpload($FileKey, $Location, $Overwrite = false)
    {

        return new Upload($FileKey, $Location, $Overwrite);
    }

    /**
     * @return Converter
     */
    public function getMarkdownify()
    {

        return new Converter();
    }

    /**
     * @param array $List
     *
     * @return Sorter
     */
    public function getSorter($List)
    {

        return new Sorter($List);
    }

    /**
     * Check if input is valid IDN Mail-Address
     *
     * @param string $Address Mail-Address
     * @return false|string returns false if address is invalid or address string if valid IDN
     */
    public function validateMailAddress($Address)
    {
        $PartList = explode('@', $Address);
        if(
            count($PartList) == 2
            && (filter_var(
                $PartList[0] . '@' . idn_to_ascii($PartList[1]), FILTER_VALIDATE_EMAIL
                ) !== false
            )
        ) {
            return $Address;
        }
        return false;
    }
}
