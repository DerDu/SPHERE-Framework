<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSetting
 * @package SPHERE\Application\Api\Billing\Inventory
 */
class ApiSetting extends Extension implements IApiInterface
{

    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // PersonGroup
        $Dispatcher->registerMethod('showPersonGroup');
        $Dispatcher->registerMethod('showFormPersonGroup');
        $Dispatcher->registerMethod('changePersonGroup');
        //Other Setting's
        $Dispatcher->registerMethod('showSetting');
        $Dispatcher->registerMethod('showFormSetting');
        $Dispatcher->registerMethod('changeSetting');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverPersonGroup($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('PersonGroupReceiver');
    }

    /**
     * @param string $Content
     * @param string $Idenifier
     *
     * @return BlockReceiver
     */
    public static function receiverSetting($Content = '', $Idenifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('SettingReceiver'.$Idenifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver())->setIdentifier('ShowModal');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowPersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showPersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowFormPersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showFormPersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSavePersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changePersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Category
     *
     * @return Pipeline
     */
    public static function pipelineShowSetting($Category)
    {
        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Category
     *
     * @return Pipeline
     */
    public static function pipelineShowFormSetting($Category)
    {

        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showFormSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveSetting($Category)
    {
        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Layout
     */
    public function showPersonGroup()
    {

        return Setting::useFrontend()->displayPersonGroup();
    }

    /**
     * @return string
     */
    public function showFormPersonGroup()
    {

        return Setting::useFrontend()->formPersonGroup();
    }

    /**
     * @param array $PersonGroup
     *
     * @return Layout|string
     */
    public function changePersonGroup($PersonGroup)
    {

        if(isset($PersonGroup)
            && !empty($PersonGroup)
            && ($GroupIdList = $PersonGroup)){
            // clear all PersonGroup that exists but not be selected
            $tblSettingGroupPersonExist = Setting::useService()->getSettingGroupPersonAll();
            foreach($tblSettingGroupPersonExist as $tblSettingGroupPerson) {
                $tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson();
                // Gruppe nicht mehr vorhanden
                if(!$tblGroup){
                    Setting::useService()->destroySettingGroupPerson($tblSettingGroupPerson);
                } elseif(!in_array($tblGroup->getId(), $GroupIdList)){
                    // Gruppe nicht mehr ausgewählt
                    Setting::useService()->destroySettingGroupPerson($tblSettingGroupPerson);
                }
            }
            foreach($GroupIdList as $GroupId) {
                $tblGroup = Group::useService()->getGroupById($GroupId);
                Setting::useService()->createSettingGroupPerson($tblGroup);
            }
        }
        return Setting::useFrontend()->displayPersonGroup();
    }

    /**
     * @param string $Category
     *
     * @return Layout
     */
    public function showSetting($Category)
    {

        return Setting::useFrontend()->displaySetting($Category);
    }

    /**
     * @param $Category
     *
     * @return Layout
     */
    public function showFormSetting($Category)
    {

        return Setting::useFrontend()->formSetting($Category);
    }

    /**
     * @param array  $Setting
     * @param string $Category
     *
     * @return Layout
     */
    public function changeSetting($Setting, $Category)
    {

//        Debugger::screenDump($Setting);
//        exit;

        switch($Category){
            case TblSetting::CATEGORY_REGULAR:
                // aktuell leer
            break;
            case TblSetting::CATEGORY_SEPA:
                $IsSepaAccountNeed = (isset($Setting[TblSetting::IDENT_IS_SEPA]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_SEPA, $IsSepaAccountNeed);
                $IsAutoReferenceNumber = (isset($Setting[TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER, $IsAutoReferenceNumber);
                $SepaRemark = (isset($Setting[TblSetting::IDENT_SEPA_REMARK]) ? $Setting[TblSetting::IDENT_SEPA_REMARK]: '');
                Setting::useService()->createSetting(TblSetting::IDENT_SEPA_REMARK, $SepaRemark);
                $SepaFee = (isset($Setting[TblSetting::IDENT_SEPA_FEE]) ? $Setting[TblSetting::IDENT_SEPA_FEE]: '');
                Setting::useService()->createSetting(TblSetting::IDENT_SEPA_FEE, $SepaFee);
            break;
            case TblSetting::CATEGORY_DATEV:
                $IsDatev = (isset($Setting[TblSetting::IDENT_IS_DATEV]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_DATEV, $IsDatev);
                $DebtorNumberCount = (isset($Setting[TblSetting::IDENT_DEBTOR_NUMBER_COUNT]) ? $Setting[TblSetting::IDENT_DEBTOR_NUMBER_COUNT] : 5);
                Setting::useService()->createSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, $DebtorNumberCount);
                $ConsultNumber = (isset($Setting[TblSetting::IDENT_CONSULT_NUMBER]) ? $Setting[TblSetting::IDENT_CONSULT_NUMBER] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_CONSULT_NUMBER, $ConsultNumber);
                $ClientNumber = (isset($Setting[TblSetting::IDENT_CLIENT_NUMBER]) ? $Setting[TblSetting::IDENT_CLIENT_NUMBER] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_CLIENT_NUMBER, $ClientNumber);
                $ProperAccountLength = (isset($Setting[TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH]) ? $Setting[TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH] : 8);
                Setting::useService()->createSetting(TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH, $ProperAccountLength);
                $IsAutoDebtorNumber = (isset($Setting[TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER, $IsAutoDebtorNumber);
                $DatevRemark = (isset($Setting[TblSetting::IDENT_DATEV_REMARK]) ? $Setting[TblSetting::IDENT_DATEV_REMARK] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_DATEV_REMARK, $DatevRemark);
                $FibuAccount = (isset($Setting[TblSetting::IDENT_FIBU_ACCOUNT]) ? $Setting[TblSetting::IDENT_FIBU_ACCOUNT] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_FIBU_ACCOUNT, $FibuAccount);
                $FibuAccountAsDebtorNumber = (isset($Setting[TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR, $FibuAccountAsDebtorNumber);
                $FibuToAccount = (isset($Setting[TblSetting::IDENT_FIBU_TO_ACCOUNT]) ? $Setting[TblSetting::IDENT_FIBU_TO_ACCOUNT] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_FIBU_TO_ACCOUNT, $FibuToAccount);
                $Kost1 = (isset($Setting[TblSetting::IDENT_KOST_1]) ? $Setting[TblSetting::IDENT_KOST_1] : '0');
                Setting::useService()->createSetting(TblSetting::IDENT_KOST_1, $Kost1);
                $Kost2 = (isset($Setting[TblSetting::IDENT_KOST_2]) ? $Setting[TblSetting::IDENT_KOST_2] : '0');
                Setting::useService()->createSetting(TblSetting::IDENT_KOST_2, $Kost2);
                $BuKey = (isset($Setting[TblSetting::IDENT_BU_KEY]) ? $Setting[TblSetting::IDENT_BU_KEY] : '0');
                Setting::useService()->createSetting(TblSetting::IDENT_BU_KEY, $BuKey);
            break;
        }

        return Setting::useFrontend()->displaySetting($Category);
    }
}