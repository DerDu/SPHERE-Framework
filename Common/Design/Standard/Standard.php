<?php
namespace SPHERE\Common\Design\Standard;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Common\Design\IDesignInterface;
use SPHERE\Common\Style;

/**
 * Class Standard
 *
 * @package SPHERE\Common\Design\Standard
 */
class Standard implements IDesignInterface
{

    /**
     * Standard constructor.
     * @param Style $Style
     */
    public function __construct( Style $Style )
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
                $Style->setSource('/Common/Style/Bootstrap.css');
                break;
            case 2:
                $Style->setSource('/Common/Style/Application.css');
                break;
            default:
                $Style->setSource('/Common/Style/Bootstrap.css');
        }

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

        switch ($SettingSurface) {
            case 1:
                $Style->setSource('/Common/Style/Correction.css', false, true);
                $Style->setSource('/Common/Style/DataTable.Correction.css', false, true);
                break;
            case 2:
                $Style->setSource('/Common/Style/Application.Correction.css', false, true);
                $Style->setSource('/Common/Style/Application.DataTable.Correction.css', false, true);
                break;
            default:
                $Style->setSource('/Common/Style/Correction.css', false, true);
                $Style->setSource('/Common/Style/DataTable.Correction.css', false, true);
        }

        $Style->setSource('/Common/Style/CleanSlate/0.10.1/cleanslate.css', false, true);
        $Style->setSource('/Common/Style/PhpInfo.css', false, true);
        $Style->setSource('/Common/Style/Addition.css');
        $Style->setSource('/Common/Style/Animate.css');
    }
}