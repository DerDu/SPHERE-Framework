<?php
namespace SPHERE\UnitTest\Console;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Application\Transfer\Gateway\Operation\FESH;
use SPHERE\Application\Transfer\Gateway\Operation\YubiEGE;
use SPHERE\Application\Transfer\Gateway\Structure\MasterDataManagement;

/**
 * Setup: Php
 */

header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
session_start();
session_write_close();
set_time_limit(240);
ob_implicit_flush();
ini_set('display_errors', 1);

/**
 * Setup: Loader
 */
require_once( __DIR__.'/../../Library/MOC-V/Core/AutoLoader/AutoLoader.php' );
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__.'/../../Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__.'/../../', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__.'/../../Library/Markdownify/2.1.6/src');

print '<pre>';
new YubiEGE( __DIR__.'/lehrer-yubikeys.xlsx' );


//
//$I = new FESH(
//    __DIR__.'/../bearbeitet interessenten.xlsx', new MasterDataManagement()
//);
//
//$Xml = $I->getStructure()->getXml();
//
//var_dump( $Xml );
//
//$dom = new \DOMDocument;
//$dom->preserveWhiteSpace = false;
//$dom->loadXML($Xml, LIBXML_PARSEHUGE);
//var_dump( $dom->getElementsByTagName('Import') );
//var_dump( $dom->getElementsByTagName('Fragment') );


