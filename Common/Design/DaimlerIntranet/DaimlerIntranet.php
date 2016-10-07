<?php
namespace SPHERE\Common\Design\DaimlerIntranet;

use SPHERE\Common\Design\IDesignInterface;
use SPHERE\Common\Style;

/**
 * Class DaimlerIntranet
 *
 * @package SPHERE\Common\Design\DaimlerIntranet
 */
class DaimlerIntranet implements IDesignInterface
{
    /**
     * Design constructor.
     *
     * @param Style $Style
     */
    public function __construct(Style $Style)
    {

        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/Bootstrap.css');

        $Style->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons/web/html_css/css/glyphicons.css');
        $Style->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-halflings/web/html_css/css/glyphicons-halflings.css');
        $Style->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-filetypes/web/html_css/css/glyphicons-filetypes.css');
        $Style->setSource('/Library/Bootstrap.Glyphicons/1.9.2/glyphicons-social/web/html_css/css/glyphicons-social.css');
        $Style->setSource('/Library/Foundation.Icons/3.0/foundation-icons.css');

        $Style->setSource('/Library/jQuery.Selecter/3.2.4/jquery.fs.selecter.min.css', false, true);
        $Style->setSource('/Library/jQuery.Stepper/3.0.8/jquery.fs.stepper.css', false, true);
        $Style->setSource('/Library/jQuery.iCheck/1.0.2/skins/all.css', false, true);
        $Style->setSource('/Library/jQuery.Gridster/0.6.10/dist/jquery.gridster.min.css', false, true);
        $Style->setSource('/Library/Bootstrap.Checkbox/0.3.3/awesome-bootstrap-checkbox.css', false, true);

        $Style->setSource('/Library/DataTables/Responsive-2.1.0/css/responsive.bootstrap.min.css', false, true);
        $Style->setSource('/Library/DataTables/RowReorder-1.1.2/css/rowReorder.bootstrap.min.css', false, true);

        $Style->setSource('/Library/Bootstrap.DateTimePicker/4.14.30/build/css/bootstrap-datetimepicker.min.css', false,
            true);
        $Style->setSource('/Library/Bootstrap.FileInput/4.1.6/css/fileinput.min.css', false, true);
        $Style->setSource('/Library/Bootstrap.Select/1.6.4/dist/css/bootstrap-select.min.css', false, true);
        $Style->setSource('/Library/Twitter.Typeahead.Bootstrap/1.0.1/typeaheadjs.css', false, true);

        $Style->setSource('/Library/jQuery.jCarousel/0.3.3/examples/responsive/jcarousel.responsive.css', false, true);
        $Style->setSource('/Library/jQuery.FlowPlayer/6.0.3/skin/functional.css', false, true);
        $Style->setSource('/Library/Highlight.js/8.8.0/styles/docco.css', false, true);

        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/Correction.css');
        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/DataTable.Correction.css', false, true);

        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/CleanSlate/0.10.1/cleanslate.css', false, true);
        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/PhpInfo.css', false, true);
        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/Addition.css');
        $Style->setSource('/Common/Design/DaimlerIntranet/Basic/Animate.css');

        $Style->setSource('/Common/Design/DaimlerIntranet/Assets/bootstrap.css', false, true);
        $Style->setSource('/Common/Design/DaimlerIntranet/Assets/datatables.css', false, true);
    }
}