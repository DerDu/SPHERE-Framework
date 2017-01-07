<?php
namespace SPHERE\Library;

use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\FolderClosed;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Library\Style\Library;

/**
 * Class Style
 *
 * @package SPHERE\Library
 */
class Style
{

    /** @var array $Register */
    private static $Register = array();
    /** @var null|Library $Library */
    private $Library = null;

    /**
     * Script constructor.
     *
     * @param string|null $Library
     * @param string|null $Version
     * @return null|Style
     * @throws \Exception
     */
    public function __construct($Library, $Version = null)
    {
        if (empty(self::$Register)) {
            $this->setupRegister();
        }
        if ($Library !== null) {
            if (!($LibraryDefinition = $this->getLibraryVersion($Library, $Version))) {
                if (!($LibraryDefinition = $this->getLibraryLatest($Library))) {
                    throw new \Exception('Library ' . $Library . ' (' . $Version . ') does not exist');
                }
            }
            $this->setLibrary($LibraryDefinition);
        }
    }

    /**
     * Register All Available Libraries
     */
    private function setupRegister()
    {
        $Location = '/Library';

        $this->addLibrary(new Library('Bootstrap.Glyphicons.Glyphicons', '1.9.2',
            $Location.'/Bootstrap.Glyphicons/1.9.2/glyphicons/web/html_css/css/glyphicons.css'
        ));
        $this->addLibrary(new Library('Bootstrap.Glyphicons.Halflings', '1.9.2',
            $Location.'/Bootstrap.Glyphicons/1.9.2/glyphicons-halflings/web/html_css/css/glyphicons-halflings.css'
        ));
        $this->addLibrary(new Library('Bootstrap.Glyphicons.Filetypes', '1.9.2',
            $Location.'/Bootstrap.Glyphicons/1.9.2/glyphicons-filetypes/web/html_css/css/glyphicons-filetypes.css'
        ));
        $this->addLibrary(new Library('Bootstrap.Glyphicons.Social', '1.9.2',
            $Location.'/Bootstrap.Glyphicons/1.9.2/glyphicons-social/web/html_css/css/glyphicons-social.css'
        ));
        $this->addLibrary(new Library('Bootstrap.Checkbox', '0.3.3',
            $Location . '/Bootstrap.Checkbox/0.3.3/awesome-bootstrap-checkbox.css'
        ));

        $this->addLibrary(new Library('Foundation.Icons', '3.0',
            $Location . '/Foundation.Icons/3.0/foundation-icons.css'
        ));

        $this->addLibrary(new Library('jQuery.Formstone.Selecter', '3.2.4',
            $Location . '/jQuery.Selecter/3.2.4/jquery.fs.selecter.min.css'
        ));
        $this->addLibrary(new Library('jQuery.Formstone.Stepper', '3.0.8',
            $Location . '/jQuery.Stepper/3.0.8/jquery.fs.stepper.css'
        ));

    }

    /**
     * @param Library $Library
     * @throws \Exception
     */
    private function addLibrary(Library $Library)
    {
        if (!is_array(self::$Register)) {
            self::$Register = array();
        }
        if (!$this->existsLibrary($Library->getName())) {
            self::$Register[$Library->getName()] = array();
        }
        if (!$this->existsVersion($Library->getName(), $Library->getVersion())) {
            if (file_exists(getcwd() . current(explode('?', $Library->getSource())))) {
                self::$Register[$Library->getName()][$Library->getVersion()] = $Library;
                ksort(self::$Register[$Library->getName()], SORT_NATURAL);
                ksort(self::$Register, SORT_NATURAL);
            } else {
                throw new \Exception('Library-Source ' . getcwd() . $Library->getSource() . ' (' . $Library->getVersion() . ') does not exist');
            }
        }
    }

    /**
     * @param string $Name
     * @return bool
     */
    private function existsLibrary($Name)
    {
        return isset(self::$Register[$Name]);
    }

    /**
     * @param string $Name
     * @param string $Version
     * @return bool
     */
    private function existsVersion($Name, $Version)
    {
        if ($this->existsLibrary($Name)) {
            return isset(self::$Register[$Name][$Version]);
        }
        return false;
    }

    /**
     * @param string $Name
     * @param string $Version
     *
     * @return null|Library
     */
    private function getLibraryVersion($Name, $Version)
    {
        if ($this->existsVersion($Name, $Version)) {
            return self::$Register[$Name][$Version];
        }
        return null;
    }

    /**
     * @param string $Name
     *
     * @return null|Library
     */
    private function getLibraryLatest($Name)
    {
        if ($this->existsLibrary($Name)) {
            $VersionList = array_keys(self::$Register[$Name]);
            sort($VersionList, SORT_NATURAL);
            $Latest = end($VersionList);
            return self::$Register[$Name][$Latest];
        }
        return null;
    }

    /**
     * @param null|Library $Library
     */
    private function setLibrary($Library)
    {
        $this->Library = $Library;
    }

    /**
     * @return null|Library
     */
    public function getLibrary()
    {
        return $this->Library;
    }

    /**
     * @return TableData
     */
    public function getShow()
    {
        $ShowList = array();
        foreach (self::$Register as $Name => $VersionList) {
            $LibraryList = '';
            /**
             * @var string $Version
             * @var Library $Library
             */
            foreach ($VersionList as $Version => $Library) {
                $LibraryList .= new Listing(array(
                    new Info(new Tag() . ' ' . $Library->getVersion()),
                    new Small(new Muted(new FolderClosed() . ' ' . $Library->getSource()))
                ));
            }
            $Name = explode('.', $Name);
            foreach ($Name as $Index => $Part) {
                switch ($Index) {
                    case 0:
                        $Name[$Index] = new Success($Part);
                        break;
                    case 1:
                        $Name[$Index] = new Info($Part);
                        break;
                    case 2:
                        $Name[$Index] = new Danger($Part);
                        break;
                    case 3:
                        $Name[$Index] = new Warning($Part);
                        break;
                    case 4:
                        $Name[$Index] = new Muted($Part);
                        break;
                }
            }

            $ShowList[] = array(
                'Name' => new Listing(array(
                    new Muted(new CogWheels()) . '&nbsp;&nbsp;' . implode(' ' . new Muted(new Small(new ChevronRight())) . ' ',
                        $Name)
                )),
                'Available' => $LibraryList
            );
        }

        return new TableData($ShowList, new Title('Style Library', 'Content'), array(), false, true);
    }
}
