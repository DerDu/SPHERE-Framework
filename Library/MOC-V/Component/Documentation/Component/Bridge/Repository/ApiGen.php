<?php
namespace MOC\V\Component\Documentation\Component\Bridge\Repository;

use MOC\V\Component\Documentation\Component\Bridge\Bridge;
use MOC\V\Component\Documentation\Component\IBridgeInterface;
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;
use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\FileSystem;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;
use Nette\Config\Adapters\NeonAdapter;

/**
 * Class ApiGen
 *
 * @package MOC\V\Component\Documentation\Component\Bridge\Repository
 */
class ApiGen extends Bridge implements IBridgeInterface
{

    /** @var string $Project */
    private $Project = '';
    /** @var string $Title */
    private $Title = '';
    /** @var DirectoryParameter|null $Source */
    private $Source = null;
    /** @var DirectoryParameter|null $Destination */
    private $Destination = null;
    /** @var ExcludeParameter|null $Exclude */
    private $Exclude = null;


    /**
     * @param string                $Project
     * @param string                $Title
     * @param DirectoryParameter    $Source
     * @param DirectoryParameter    $Destination
     * @param null|ExcludeParameter $Exclude
     */
    public function __construct(
        $Project,
        $Title,
        DirectoryParameter $Source,
        DirectoryParameter $Destination,
        ExcludeParameter $Exclude = null
    ) {

        AutoLoader::getNamespaceAutoLoader('ApiGen', __DIR__.'/../../../Vendor/ApiGen');
        AutoLoader::getNamespaceAutoLoader('TokenReflection', __DIR__.'/../../../Vendor/ApiGen/libs/TokenReflection');
        AutoLoader::getNamespaceAutoLoader('FSHL', __DIR__.'/../../../Vendor/ApiGen/libs/FSHL');

        $this->Project = $Project;
        $this->Title = $Title;
        $this->Source = $Source;
        $this->Destination = $Destination;
        $this->Exclude = $Exclude;

        set_time_limit(0);
        $Config = $this->getConfig();

        require_once( __DIR__.'/../../../Vendor/ApiGen/libs/Nette/Nette/loader.php' );
        $Neon = new NeonAdapter();

        $File = FileSystem::getFileWriter(__DIR__.'/ApiGen.config');
        file_put_contents($File->getLocation(), str_replace( "\t", "    ", $Neon->dump($Config)));

        $SERVER = GlobalsKernel::getGlobals()->getSERVER();
        $SERVER['argv'] = array(
            'DUMMY-SHELL-ARGS',
            '--config',
            $File->getLocation()
        );
        GlobalsKernel::getGlobals()->setSERVER($SERVER);

//        include( __DIR__.'/../../../Vendor/ApiGen/apigen.php' );
        exec(
            'D:\PhpVersion\5.5.30-64-TS\php.exe '.__DIR__.'/../../../Vendor/4.1/apigen.phar'
            .' generate'
            .' --config '.$File->getLocation()
            .' --debug',
            $out, $ret
        );
        var_dump( $out, $ret );
    }

    /** @codeCoverageIgnore */
    private function getConfig()
    {

// 4.1        "accessLevels", "annotationGroups", "baseUrl", "charset", "config", "debug", "deprecated", "destination", "download", "exclude", "extensions", "googleAnalytics", "googleCseId", "groups", "internal", "main", "noSourceCode", "php", "skipDocPath", "source", "sourceCode", "template", "templateConfig", "templateTheme", "title", "todo", "tree", "visibilityLevels"

        $Default = array(
            // Source file or directory to parse
            'source'         => array($this->Source->getDirectory()),
            // Directory where to save the generated documentation
            'destination'    => $this->Destination->getDirectory(),
            // List of allowed file extensions
            'extensions'     => array('php'),
            // Mask to exclude file or directory from processing
            // 'exclude'        => "'".$this->Exclude->getGlobList()."'",
            // Don't generate documentation for classes from file or directory with this mask
            //'skipDocPath' => '',
            // Don't generate documentation for classes with this name prefix
            //'skipDocPrefix' => '',
            // Character set of source files
//            'charset'        => array( 'auto' ),
            // Main project name prefix
//            'main'           => array($this->Project),
            // Title of generated documentation
            'title'          => $this->Title,
            // Documentation base URL
            //'baseUrl' => '',
            // Google Custom Search ID
            //'googleCseId' => '',
            // Google Custom Search label
            //'googleCseLabel' => '',
            // Google Analytics tracking code
            //'googleAnalytics' => '',
            // Template config file
            'templateConfig' => __DIR__.'/../../../Vendor/4.1/Template/src/config.neon',
            // Grouping of classes
            //'groups'         => 'auto',
            // List of allowed HTML tags in documentation
// 4.1            'allowedHtml'    => array('b', 'i', 'a', 'ul', 'ol', 'li', 'p', 'br', 'var', 'samp', 'kbd', 'tt'),
            // Element types for search input autocomplete
// 4.1            'autocomplete'   => array('classes', 'constants', 'functions'),
            // Generate documentation for methods and properties with given access level
            'accessLevels'   => array('public', 'protected', 'private'),
            // Generate documentation for elements marked as internal and display internal documentation parts
            'internal'       => true,
            // Generate documentation for PHP internal classes
            'php'            => true,
            // Generate tree view of classes, interfaces and exceptions
            'tree'           => true,
            // Generate documentation for deprecated classes, methods, properties and constants
            'deprecated'     => true,
            // Generate documentation of tasks
            'todo'           => true,
            // Generate highlighted source code files
            'sourceCode'     => true,
            // Add a link to download documentation as a ZIP archive
            'download'       => false,
            // Save a check style report of poorly documented elements into a file
// 4.1            'report'         => $this->Destination->getDirectory().DIRECTORY_SEPARATOR.'_improve.xml',
            // Wipe out the destination directory first
// 4.1            'wipeout'        => false,
            // Don't display scanning and generating messages
//            'quiet'          => false,
            // Display progressbar
// 4.1            'progressbar'    => false,
            // Use colors
// 4.1            'colors'         => false,
            // Check for update
// 4.1            'updateCheck'    => false,
            // Display additional information in case of an error
            'debug'          => true
        );
        if (null === $this->Exclude) {
            return $Default;
        } else {
            return array_merge($Default, array(
// 4.1                'exclude' => $this->Exclude->getGlobString()
                'exclude' => $this->Exclude->getGlobList()
            ));
        }
    }
}
