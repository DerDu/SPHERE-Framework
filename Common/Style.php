<?php
namespace SPHERE\Common;

use MOC\V\Core\HttpKernel\Vendor\Universal\Request;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Style
 *
 * @package SPHERE\Common
 */
class Style extends Extension
{

    /** @var array $SourceList */
    private static $SourceList = array();

    /** @var array $CombinedList */
    private static $CombinedList = array();

    /** @var array $AdditionalList */
    private static $AdditionalList = array();

    /**
     * Default
     */
    private function __construct()
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $SettingSurface = MyAccount::useService()->getSettingByAccount($tblAccount, 'Surface');
            if ($SettingSurface) {
                $SettingSurface = $SettingSurface->getValue();
            } else {
                $SettingSurface = 1;
            }
        } else {
            $SettingSurface = 1;
        }

        switch ($SettingSurface) {
            case 1:
                $this->setSource('/Common/Style/Bootstrap.css');
                break;
            case 2:
                $this->setSource('/Common/Style/Application.css');
                break;
            default:
                $this->setSource('/Common/Style/Bootstrap.css');
        }

        $this->setSource('/Library/Bootstrap/3.3.5/dist/css/bootstrap-theme.css');

        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons/web/html_css/css/glyphicons.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-halflings/web/html_css/css/glyphicons-halflings.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-filetypes/web/html_css/css/glyphicons-filetypes.css');
        $this->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-social/web/html_css/css/glyphicons-social.css');
        $this->setSource('/Library/Foundation.Icons/3.0/foundation-icons.css');

        $this->setSource('/Library/jQuery.Selecter/3.2.4/jquery.fs.selecter.min.css', false, true);
        $this->setSource('/Library/jQuery.Stepper/3.0.8/jquery.fs.stepper.css', false, true);
        $this->setSource('/Library/jQuery.iCheck/1.0.2/skins/all.css', false, true);
        $this->setSource('/Library/jQuery.Gridster/0.6.10/dist/jquery.gridster.min.css', false, true);
        $this->setSource('/Library/Bootstrap.Checkbox/0.3.3/awesome-bootstrap-checkbox.css', false, true);

        //        <link rel="stylesheet" type="text/css" href="Bootstrap-3.3.6/css/bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="DataTables-1.10.12/css/dataTables.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="AutoFill-2.1.2/css/autoFill.bootstrap.min.css"/>
        //        <link rel="stylesheet" type="text/css" href="Buttons-1.2.2/css/buttons.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="ColReorder-1.3.2/css/colReorder.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="FixedColumns-3.2.2/css/fixedColumns.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="FixedHeader-3.1.2/css/fixedHeader.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="KeyTable-2.1.3/css/keyTable.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="Responsive-2.1.0/css/responsive.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="RowReorder-1.1.2/css/rowReorder.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="Scroller-1.4.2/css/scroller.bootstrap.css"/>
        //        <link rel="stylesheet" type="text/css" href="Select-1.2.0/css/select.bootstrap.css"/>
        //

        $this->setSource('/Library/DataTables/Responsive-2.1.0/css/responsive.bootstrap.min.css', false,
            true);
        $this->setSource('/Library/DataTables/RowReorder-1.1.2/css/rowReorder.bootstrap.min.css', false,
            true);

////        $this->setSource( '/Library/jQuery.DataTables/1.10.7/media/css/jquery.dataTables.min.css' );
//        $this->setSource('/Library/jQuery.DataTables/1.10.7/extensions/Responsive/css/dataTables.responsive.css', false,
//            true);
//        $this->setSource('/Library/jQuery.DataTables.Plugins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css',
//            false, true);


        $this->setSource('/Library/Bootstrap.DateTimePicker/4.14.30/build/css/bootstrap-datetimepicker.min.css', false,
            true);
        $this->setSource('/Library/Bootstrap.FileInput/4.1.6/css/fileinput.min.css', false, true);
        $this->setSource('/Library/Bootstrap.Select/1.6.4/dist/css/bootstrap-select.min.css', false, true);
        $this->setSource('/Library/Twitter.Typeahead.Bootstrap/1.0.1/typeaheadjs.css', false, true);

        $this->setSource('/Library/jQuery.jCarousel/0.3.3/examples/responsive/jcarousel.responsive.css', false, true);
        $this->setSource('/Library/jQuery.FlowPlayer/6.0.3/skin/functional.css', false, true);
        $this->setSource('/Library/Highlight.js/8.8.0/styles/docco.css', false, true);

        switch ($SettingSurface) {
            case 1:
                $this->setSource('/Common/Style/Correction.css', false, true);
                $this->setSource('/Common/Style/DataTable.Correction.css', false, true);
                break;
            case 2:
                $this->setSource('/Common/Style/Application.Correction.css', false, true);
                $this->setSource('/Common/Style/Application.DataTable.Correction.css', false, true);
                break;
            default:
                $this->setSource('/Common/Style/Correction.css', false, true);
                $this->setSource('/Common/Style/DataTable.Correction.css', false, true);
        }

        $this->setSource('/Common/Style/CleanSlate/0.10.1/cleanslate.css',false,true);
        $this->setSource('/Common/Style/PhpInfo.css', false, true);
        $this->setSource('/Common/Style/Addition.css');
        $this->setSource('/Common/Style/Animate.css');
    }

    /**
     * @param string $Location
     * @param bool   $Combined
     * @param bool   $Additional
     */
    public function setSource($Location, $Combined = false, $Additional = false)
    {

        $PathBase = $this->getRequest()->getPathBase();
        if ($Combined) {
            if (!in_array(md5($Location), self::$CombinedList)) {
                self::$CombinedList[md5($Location)] = $PathBase . $Location;
            }
        } elseif ($Additional) {
            if (!in_array(md5($Location), self::$AdditionalList)) {
                self::$AdditionalList[md5($Location)] = $PathBase . $Location;
            }
        } else {
            if (!in_array(md5($Location), self::$SourceList)) {
                self::$SourceList[md5($Location)] = $PathBase . $Location;
            }
        }
    }

    /**
     * @return Style
     */
    public static function getManager()
    {

        return new Style();
    }

    /**
     * @param bool $withTag
     *
     * @return string
     */
    public function getCombinedStyle($withTag = true)
    {

        if ($withTag) {
            return $this->getCombinedStyleTag(
                implode("\n", array(
                    $this->parseCombinedStyle(self::$CombinedList),
                    $this->parseCombinedStyle(self::$SourceList)
                ))
            );
        } else {
            return implode("\n", array(
                $this->parseCombinedStyle(self::$CombinedList),
                $this->parseCombinedStyle(self::$SourceList)
            ));
        }
    }

    /**
     * @param string $Content
     *
     * @return string
     */
    private function getCombinedStyleTag($Content)
    {

        if (empty( $Content )) {
            return '';
        } else {
            return '<style type="text/css">'.$Content.'</style>';
        }
    }

    /**
     * @param array $FileList
     *
     * @return string
     */
    private function parseCombinedStyle($FileList)
    {

        $Result = '';
        array_walk($FileList, function ($Location) use (&$Result) {

            $Path = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.$Location);
            if ($Path) {
                $Content = $this->compactStyle(file_get_contents($Path));
                preg_match_all('!url\(([^\)]*?)\)!is', $Content, $Match);
                if (!empty( $Match[0] )) {
                    array_walk($Match[0], function ($Item, $Index) use ($Match, $Path, &$Content) {

                        $Match[1][$Index] = trim($Match[1][$Index], ' \'"');
                        if (
                            false === strpos($Item, 'http')
                            && false === strpos($Item, 'data:')
                        ) {
                            $Directory = dirname($Path);
                            $File = $Match[1][$Index];
                            if (false !== strpos($File, '?')) {
                                $Parts = explode('?', $Match[1][$Index]);
                                $Location = realpath($Directory.DIRECTORY_SEPARATOR.array_shift($Parts));
                                if (!empty( $Parts )) {
                                    $Parts = '?'.implode('?', $Parts);
                                }
                            } elseif (false !== strpos($File, '#')) {
                                $Parts = explode('#', $Match[1][$Index]);
                                $Location = realpath($Directory.DIRECTORY_SEPARATOR.array_shift($Parts));
                                if (!empty( $Parts )) {
                                    $Parts = '#'.implode('#', $Parts);
                                }
                            } else {
                                $Location = realpath($Directory.DIRECTORY_SEPARATOR.$File);
                                $Parts = '';
                            }
                            if ($Location) {
                                $Target = preg_replace('!'.preg_quote($_SERVER['DOCUMENT_ROOT'], '!').'!is', '',
                                        $Location).$Parts;
                                $Request = new Request();
                                $Replacement = $Request->getSymfonyRequest()->getUriForPath($Target);
                                $Content = str_replace($Match[0][$Index], "url('".$Replacement."')", $Match[0][$Index],
                                    $Content);
                            }
                        }
                    });
                }
                $Result .= "\n\n".$Content;
            } else {
                $this->getLogger(new ErrorLogger())->addLog('Style not found ' . $Location);
            }
        });
        return $Result;
    }

    /**
     * @param string $Content
     *
     * @return string
     */
    private function compactStyle($Content)
    {

        /* remove comments */
        $Content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $Content);
        /* remove tabs, spaces, newlines, etc. */
        $Content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $Content);

        return $Content;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        $Content = $this->parseCombinedStyle(self::$CombinedList);

        $StyleList = array_merge(self::$SourceList, self::$AdditionalList);

        array_walk($StyleList, function (&$Location) {

            $Location = '<link rel="stylesheet" href="'.$Location.'">';
        });
        array_unshift($StyleList, $this->getCombinedStyleTag($Content));
        return implode("\n", $StyleList);
    }
}
