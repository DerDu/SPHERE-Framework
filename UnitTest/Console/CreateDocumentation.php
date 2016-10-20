<?php
/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
session_write_close();
date_default_timezone_set('Europe/Berlin');
ini_set('memory_limit','1G');
/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
use MOC\V\Component\Documentation\Component\Parameter\Repository\DirectoryParameter;
use MOC\V\Component\Documentation\Component\Parameter\Repository\ExcludeParameter;
use MOC\V\Component\Documentation\Documentation;
use MOC\V\Core\AutoLoader\AutoLoader;

AutoLoader::getNamespaceAutoLoader('\MOC\V', __DIR__.'/../../Library/MOC-V/', '\MOC\V');

Documentation::getDocumentation(
    'SPHERE-Framework',
    '1.8.5',
    new DirectoryParameter(__DIR__.'/../../'),
    new DirectoryParameter(__DIR__.'/../../UnitTest/Documentation/'),
    new ExcludeParameter(array(
        '/.idea/*',
        '/.git/*',
        '*/Documentation/*',
        '*/TestSuite/*',
        '*/UnitTest/*',
        '*/Library/*',
    ))
);
