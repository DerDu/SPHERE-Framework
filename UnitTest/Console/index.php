<?php
namespace SPHERE\UnitTest\Console;

/**
 * Setup: Php
 */
use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Template\Template;
use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\HttpKernel\Component\Bridge\Repository\UniversalRequest;
use MOC\V\Core\HttpKernel\HttpKernel;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR\RadebeulKinderbrief;
use SPHERE\Application\Document\Storage\FilePointer;

//header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
set_time_limit(240);
ini_set('display_errors',1);

/**
 * Setup: Loader
 */
require_once(__DIR__ . '/../../Library/MOC-V/Core/AutoLoader/AutoLoader.php');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__ . '/../../Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__ . '/../../', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__ . '/../../Library/Markdownify/2.1.6/src');
AutoLoader::getNamespaceAutoLoader('phpFastCache', __DIR__ . '/../../Library/PhpFastCache/4.3.6/src');

$Template = Template::getTwigTemplateString( file_get_contents(__DIR__.'/Twig.twig'));
$Template->setVariable('HOST', (new UniversalRequest())->getHost() );

$Fp = new FilePointer( 'pdf' );
$Fp->saveFile();

/** @var DomPdf $Pdf */
$Pdf = Document::getPdfDocument( $Fp->getRealPath() );

$Pdf->setContent( $Template );
$Pdf->saveFile();

if (function_exists('mime_content_type')) {
    $Type = mime_content_type($Fp->getRealPath());
} else {
    if (function_exists('finfo_file')) {
        $Handler = finfo_open(FILEINFO_MIME);
        $Type = finfo_file($Handler, $Fp->getRealPath());
        finfo_close($Handler);
    } else {
        $Type = "application/force-download";
    }
}
header('Content-Type: '.$Type);
print file_get_contents($Fp->getRealPath());
