<?php
namespace Bar;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\FileSystem;

/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
session_write_close();
date_default_timezone_set('Europe/Berlin');
/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader('\MOC\V', __DIR__.'/../../');

print FileSystem::getDownload(__FILE__);
