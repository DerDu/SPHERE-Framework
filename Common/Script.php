<?php
namespace SPHERE\Common;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Library\Script\Library;
use SPHERE\Library\Script as ScriptLibrary;
use SPHERE\System\Extension\Extension;

/**
 * Class Script
 *
 * @package SPHERE\Common
 */
class Script extends Extension
{

    /** @var array $SourceList */
    private static $SourceList = array();
    /** @var array $ModuleList */
    private static $ModuleList = array();

    /**
     * Default
     */
    private function __construct()
    {

        /**
         * Source (Library)
         */

        try {
            $this->setLibrary((new ScriptLibrary('jQuery', '1.11.3'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery-Ui', '1.11.4'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Selecter', '3.2.4'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Stepper', '3.0.8'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.CountDown', '2.0.5'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Sisyphus', '1.1.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.CheckBox', '1.0.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.StorageApi', '1.7.4'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Gridster', '0.6.10'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Mask', '3.1.63'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable', '1.10.12'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Bootstrap', '1.10.12'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Responsive', '2.1.0'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.RowReorder', '1.1.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Buttons', '1.2.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Buttons.Bootstrap', '1.2.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Buttons.ColVis', '1.2.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Buttons.HtmlExport', '1.2.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Buttons.FlashExport', '1.2.2'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Plugin.Sorting.DateTime', '1.10.7'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Plugin.Sorting.GermanString', '1.10.7'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.DataTable.Plugin.Sorting.Natural', '1.10.7'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.Carousel', '0.3.3'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.FlowPlayer', '6.0.3'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('jQuery.iCheck', '1.0.2'))->getLibrary());

            $this->setLibrary((new ScriptLibrary('Bootstrap', '3.3.5'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootstrap.DatetimePicker', '4.14.30'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootstrap.FileInput', '4.1.6'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootstrap.Select', '1.6.4'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootstrap.Notify', '3.1.3'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootstrap.Validator', '0.11.x'))->getLibrary());

            $this->setLibrary((new ScriptLibrary('Twitter.Typeahead', '0.11.1'))->getLibrary());

            $this->setLibrary((new ScriptLibrary('Moment.Js', '2.8.4'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('List.Js', '1.1.1'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('MathJax', '2.5.0'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Highlight.js', '8.8.0'))->getLibrary());
            $this->setLibrary((new ScriptLibrary('Bootbox.js', '4.4.0'))->getLibrary());
        } catch ( \Exception $Exception ) {
            Main::getDisplay()->setException( $Exception, 'JavaScript Library' );
        }

///*
//        <script type="text/javascript" src="Bootstrap-3.3.6/js/bootstrap.js"></script>
//        <script type="text/javascript" src="JSZip-2.5.0/jszip.js"></script>
//        <script type="text/javascript" src="pdfmake-0.1.18/build/pdfmake.js"></script>
//        <script type="text/javascript" src="pdfmake-0.1.18/build/vfs_fonts.js"></script>
//        <script type="text/javascript" src="DataTables-1.10.12/js/jquery.dataTables.js"></script>
//        <script type="text/javascript" src="DataTables-1.10.12/js/dataTables.bootstrap.js"></script>
//        <script type="text/javascript" src="AutoFill-2.1.2/js/dataTables.autoFill.js"></script>
//        <script type="text/javascript" src="AutoFill-2.1.2/js/autoFill.bootstrap.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/dataTables.buttons.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/buttons.bootstrap.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/buttons.colVis.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/buttons.flash.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/buttons.html5.js"></script>
//        <script type="text/javascript" src="Buttons-1.2.2/js/buttons.print.js"></script>
//        <script type="text/javascript" src="ColReorder-1.3.2/js/dataTables.colReorder.js"></script>
//        <script type="text/javascript" src="FixedColumns-3.2.2/js/dataTables.fixedColumns.js"></script>
//        <script type="text/javascript" src="FixedHeader-3.1.2/js/dataTables.fixedHeader.js"></script>
//        <script type="text/javascript" src="KeyTable-2.1.3/js/dataTables.keyTable.js"></script>
//        <script type="text/javascript" src="Responsive-2.1.0/js/dataTables.responsive.js"></script>
//        <script type="text/javascript" src="RowReorder-1.1.2/js/dataTables.rowReorder.js"></script>
//        <script type="text/javascript" src="Scroller-1.4.2/js/dataTables.scroller.js"></script>
//        <script type="text/javascript" src="Select-1.2.0/js/dataTables.select.js"></script>
//*/

///*
//        $this->setSource(
//            'jQuery.DataTable.Responsive',
//            '/Library/jQuery.DataTables/1.10.7/extensions/Responsive/js/dataTables.responsive.min.js',
//            "'undefined' !== typeof jQuery.fn.DataTable.Responsive"
//        );
//*/
///*
//        $this->setSource(
//            'jQuery.DataTable.Plugin.Sorting.Weekday',
//            '/Library/jQuery.DataTables.Plugins/1.0.1/sorting/weekday.js',
//            "'undefined' !== typeof jQuery.fn.dataTable.ext.type.order['weekday-pre']"
//        );
//*/

        /**
         * Module (jQuery plugin)
         */

        $this->setModule(
            'ModAlways', array(/*'Highlight.js',*/
                'Bootbox.js',
                'List.Js',
                'Bootstrap.Notify',
                'Bootstrap',
                'jQuery-Ui',
                'jQuery'
            )
        );
        $this->setModule(
            'ModAjax', array(
                'Bootbox.js',
                'List.Js',
                'Bootstrap.Notify',
                'Bootstrap',
                'jQuery.Ui',
                'jQuery'
            )
        );

        $this->setModule(
            'ModTable',
            array(
//                'jQuery.DataTable.Plugin.Sorting.Weekday',
                'jQuery.DataTable.Plugin.Sorting.DateTime',
                'jQuery.DataTable.Plugin.Sorting.GermanString',
                'jQuery.DataTable.Plugin.Sorting.Natural',
                'jQuery.DataTable.Buttons.FlashExport',
                'jQuery.DataTable.Buttons.HtmlExport',
                'jQuery.DataTable.Buttons.ColVis',
                'jQuery.DataTable.Buttons.Bootstrap',
                'jQuery.DataTable.Buttons',
                'jQuery.DataTable.RowReorder',
                'jQuery.DataTable.Responsive',
                'jQuery.DataTable.Bootstrap',
                'jQuery.DataTable',
                'jQuery'
            )
        );
        $this->setModule(
            'ModPicker', array('Bootstrap.DatetimePicker', 'Moment.Js', 'jQuery')
        );
        $this->setModule(
            'ModSelecter', array('jQuery.Selecter', 'jQuery')
        );
        $this->setModule(
            'ModCarousel', array('jQuery.Carousel', 'jQuery')
        );
        $this->setModule(
            'ModVideo', array('jQuery.FlowPlayer', 'jQuery')
        );
        $this->setModule(
            'ModSelect', array('Bootstrap.Select', 'Bootstrap', 'jQuery')
        );
        $this->setModule(
            'ModCountDown', array('jQuery.CountDown', 'Bootstrap', 'Moment.Js', 'jQuery')
        );
        $this->setModule(
            'ModCompleter', array('Twitter.Typeahead', 'Bootstrap', 'jQuery')
        );
        $this->setModule(
            'ModUpload', array('Bootstrap.FileInput', 'Bootstrap', 'jQuery')
        );
        $this->setModule(
            'ModCheckBox', array('jQuery.CheckBox', 'jQuery')
        );
        $this->setModule(
            'ModMathJax', array('MathJax', 'jQuery')
        );
        $this->setModule(
            'ModProgress', array('jQuery')
        );
        $this->setModule(
            'ModGrid', array('jQuery.Gridster', 'jQuery.StorageApi', 'jQuery')
        );
        $this->setModule(
            'ModSortable', array('jQuery-Ui', 'jQuery')
        );
        $this->setModule(
            'ModForm', array( 'Bootstrap.Validator', 'jQuery.Sisyphus', 'jQuery.Mask', 'jQuery')
        );
        $this->setModule(
            'ModCleanStorage', array('jQuery')
        );
    }

    /**
     * @param Library $Library
     */
    public function setLibrary( Library $Library ) {
        $this->setSource( $Library->getName(), $Library->getSource(), $Library->getTest() );
    }

    /**
     * @param string $Alias
     * @param string $Location
     * @param string $Test
     */
    public function setSource($Alias, $Location, $Test)
    {

        $PathBase = $this->getRequest()->getPathBase();
        if (!in_array($Alias, self::$SourceList)) {
            $RealPath = FileSystem::getFileLoader($Location)->getRealPath();
            if( !empty($RealPath) ) {
                $cTag = '?cTAG-' . md5_file($RealPath);
            } else {
                $cTag = '?cTAG-' . 'MISS-'.time();
            }
            self::$SourceList[$Alias] = "Client.Source('" . $Alias . "','" . $PathBase . $Location . $cTag . "',function(){return " . $Test . ";});";
        }
    }

    /**
     * @param string $Alias
     * @param array  $Dependencies
     */
    public function setModule($Alias, $Dependencies = array())
    {

        if (!in_array($Alias, self::$ModuleList)) {
            $RealPath = FileSystem::getFileLoader('/Common/Script/' . $Alias . '.js')->getRealPath();
            if( !empty($RealPath) ) {
                $cTag = '?cTAG-' . md5_file($RealPath);
            } else {
                $cTag = '?cTAG-' . 'MISS-'.time();
            }
            self::$ModuleList[$Alias] = "Client.Module('" . $Alias . "'," . json_encode($Dependencies) . ",'" . $cTag . "');";
        }
    }

    /**
     * @return Script
     */
    public static function getManager()
    {

        return new Script();
    }

    /**
     * @return string
     */
    public function __toString()
    {

        $ScriptTagOpen = '<script type="text/javascript">';
        $ScriptTagClose = '</script>';
        $LineBreak = "\n";
        return $ScriptTagOpen
        .implode("\n", self::$SourceList).$LineBreak
        .implode("\n", self::$ModuleList).$LineBreak
        .$ScriptTagClose;
    }

}
