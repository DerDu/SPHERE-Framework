<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Setting\UserAccount
 */
class ApiUserAccount extends Extension implements IApiInterface
{

    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openAccountModal');
        $Dispatcher->registerMethod('serviceAccount');
        $Dispatcher->registerMethod('openAccountModalResult');

        $Dispatcher->registerMethod('loadingScreen');
        $Dispatcher->registerMethod('openFilter');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverAccountModal()
    {

        return (new ModalReceiver('Erstellen der '.new Bold('Benutzer'), null, false))
            ->setIdentifier('Loadingscreen');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverAccountService()
    {

        return (new InlineReceiver(''))
            ->setIdentifier('AccountService');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {

        return (new BlockReceiver($Content))
            ->setIdentifier('Filter');
    }

    /**
     * @param string $Type (S = Student; C = Custody)
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccount($Type = 'S')
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter = new ServerEmitter(self::receiverAccountService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceAccount'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     *
     * @param string $GroupByTime
     *
     * @return Pipeline
     */
    public static function pipelineShowLoad($GroupByTime = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadingScreen'
        ));
        $Emitter->setPostPayload(array(
            'GroupByTime' => $GroupByTime
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     *
     * @param string $GroupByTime
     *
     * @return Pipeline
     */
    public static function pipelineShowFilter($GroupByTime = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openFilter'
        ));
        $Emitter->setPostPayload(array(
            'GroupByTime' => $GroupByTime
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }


    /**
     * @param string $GroupByTime
     *
     * @return Layout
     */
    public function loadingScreen($GroupByTime = '')
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Lädt'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(
                    // ermöglicht das schneller runterscrollen bei Ajax-Click
                        '<div style="height: 780px">&nbsp;</div>'
                    ),
                    new LayoutColumn(
                        self::pipelineShowFilter($GroupByTime)
                    ),
                ))
            )
        );
    }

    /**
     * @param string $GroupByTime
     *
     * @return Layout|string
     */
    public function openFilter($GroupByTime = '')
    {

        $form = $this->formFilterPdf($GroupByTime);

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Well($form)
                    ),
                ))
            )
        );
    }

    /**
     * @param string $GroupByTime
     * @param bool   $IsParent
     * @param null   $Data
     *
     * @return Form|string
     */
    private function formFilterPdf($GroupByTime, $IsParent = false, $Data = null)
    {

        $SelectBoxContent = array();

        $tblCompany = false;
        $CompanyName = '';
        $CompanyExtendedName = '';
        $CompanyDistrict = '';
        $CompanyStreet = '';
        $CompanyCity = '';
        $CompanyPLZCity = '';
        $CompanyPhone = '';
        $CompanyFax = '';
        $CompanyMail = '';
        $CompanyWeb = '';

        $tblSchoolAll = School::useService()->getSchoolAll();
        if($tblSchoolAll && count($tblSchoolAll) == 1){
            $tblCompany = $tblSchoolAll[0]->getServiceTblCompany();
            // get school from student
        }

        $ErrorAccountList = array();
        if($GroupByTime){
            $tblUserAccountGroup = Account::useService()->getUserAccountByTimeGroupLimitList(new \DateTime($GroupByTime));
            $tblPerson = false;
            foreach ($tblUserAccountGroup as $GroupIdentifier => $tblUserAccountList){
                $IsError = false;
                /** @var TblUserAccount $tblUserAccount */
                foreach($tblUserAccountList as $tblUserAccount) {
                    if(!$tblUserAccount->getServiceTblPerson()){
                        if(($tblAccount = $tblUserAccount->getServiceTblAccount())){
                            $ErrorAccountList[] = $tblAccount->getUsername();
                            $IsError = true;
                        }
                    }
                }
                $SelectBoxContent[$GroupIdentifier] = $GroupIdentifier.'. Liste aus '.count($tblUserAccountList).' Personen'.
                    ($IsError ? new Small(' Hinweis siehe Oben') : '');

                // Suchen der Company wenn keine eindeutige gefunden wurde
                if(!$tblCompany) {
                    /** @var TblUserAccount $tblUserAccount */
                    foreach ($tblUserAccountList as $tblUserAccount) {
                        if ($tblUserAccount->getType() == 'CUSTODY') {
                            $IsParent = true;
                        }
                        if (!$tblPerson && ($tblPersonByAccount = $tblUserAccount->getServiceTblPerson())) {
                            if (Account::useService()->getCompanySchoolByPerson($tblPersonByAccount, $IsParent)) {
                                $tblCompany = Account::useService()->getCompanySchoolByPerson($tblPersonByAccount,
                                    $IsParent);

                                break;
                            }
                        }
                    }
                }
            }

        }


        if($tblCompany){
            $CompanyName = $tblCompany->getName();
            $CompanyExtendedName = $tblCompany->getExtendedName();
            if(($tblCompanyAddress = Address::useService()->getAddressByCompany($tblCompany))){
                $CompanyStreet = $tblCompanyAddress->getStreetName().' '.$tblCompanyAddress->getStreetNumber();
                if(($tblCity = $tblCompanyAddress->getTblCity())){
                    $CompanyDistrict = $tblCity->getDistrict();
                    $CompanyCity = $tblCity->getName();
                    $CompanyPLZCity = $tblCity->getCode().' '.$tblCity->getName();
                }
            }
            if(($tblPhoneToCompanyList = Phone::useService()->getPhoneAllByCompany($tblCompany))){
                $tblPhone = false;
                $tblFax = false;
                foreach($tblPhoneToCompanyList as $tblPhoneToCompany){
                    if(($tblType = $tblPhoneToCompany->getTblType())
                        && $tblType->getName() == 'Geschäftlich'){
                        $tblPhone = $tblPhoneToCompany->getTblPhone();
                    }
                    if(($tblType = $tblPhoneToCompany->getTblType())
                        && $tblType->getName() == 'Fax'){
                        $tblFax = $tblPhoneToCompany->getTblPhone();
                    }
                }
                if($tblPhone){
                    $CompanyPhone = $tblPhone->getNumber();
                }
                if($tblFax){
                    $CompanyFax = $tblFax->getNumber();
                }
            }
            if(($tblMailToCompanyList = \SPHERE\Application\Contact\Mail\Mail::useService()->getMailAllByCompany($tblCompany))){
                $tblMail = false;
                foreach($tblMailToCompanyList as $tblMailToCompany){
                    if(($tblType = $tblMailToCompany->getTblType())
                        && $tblType->getName() == 'Geschäftlich'){
                        $tblMail = $tblMailToCompany->getTblMail();
                    }
                }
                if($tblMail){
                    $CompanyMail = $tblMail->getAddress();
                }
            }
            if(($tblWebToCompanyList = Web::useService()->getWebAllByCompany($tblCompany))){
                $tblWebToCompany = current($tblWebToCompanyList);
                if(($tblWeb = $tblWebToCompany->getTblWeb())){
                    $CompanyWeb = $tblWeb->getAddress();
                }
            }
        }

        if(!isset($Data)){
            $Global = $this->getGlobal();
            // HiddenField
            $Global->POST['Data']['GroupByTime'] = $GroupByTime;
            $Global->POST['Data']['IsParent'] = $IsParent;
//            $Global->POST['Data']['PersonId'] = $tblPerson->getId();
            // School
            $Global->POST['Data']['CompanyName']= $CompanyName;
            $Global->POST['Data']['CompanyExtendedName'] = $CompanyExtendedName;
            $Global->POST['Data']['CompanyDistrict'] = $CompanyDistrict;
            $Global->POST['Data']['CompanyStreet'] = $CompanyStreet;
            $Global->POST['Data']['CompanyCity'] = $CompanyPLZCity;
            $Global->POST['Data']['Phone'] = $CompanyPhone;
            $Global->POST['Data']['Fax'] = $CompanyFax;
            $Global->POST['Data']['Mail'] = $CompanyMail;
            $Global->POST['Data']['Web'] = $CompanyWeb;
            // Signer
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $Global->POST['Data']['Place'] = $CompanyCity;
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new FormTitle(new TileBig().' Listenauswahl für den Download '.new Muted(new Small('Erstellung am: '.$GroupByTime)))
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (count($SelectBoxContent) <= 1
                            ? new DangerMessage('Bitte achten Sie darauf, den nächsten PDF-Download erst zu starten,
                                                wenn der vorherige abgeschlossen ist')
                            : new DangerMessage(
                                new Container('Es sind '.count($SelectBoxContent).' Listen enthalten! Bitte wählen Sie
                                               diese nacheinander in der Selectbox aus.').
                                new Container('Bitte achten Sie darauf, den nächsten PDF-Download erst zu starten,
                                               wenn der vorherige abgeschlossen ist')
                            )
                        )
                    )),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(
                                    new LayoutColumn(
                                        (!empty($ErrorAccountList)
                                            ? new WarningMessage('Hinweis: Accounts ohne Person: '.implode(', ', $ErrorAccountList))
                                            : ''
                                        )
                                    )
                                )
                            )
                        )
                    )
                ))
            ,
                new FormRow(
                    new FormColumn(
                        new Panel('Listen Auswahl '.new Muted(new Small('(Max 30 Personen)')), new SelectBox('Data[GroupByCount]', '', $SelectBoxContent),
                            Panel::PANEL_TYPE_INFO)
                    )
                ),
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[GroupByTime]')
                        , 4),
                    new FormColumn(
                        new HiddenField('Data[IsParent]')
                        , 1),
                )),
                new FormRow(array(
                    new FormColumn(
                        new FormTitle(new TileBig().' Informationen Schule')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Name der Schule',array(
                            (new TextField('Data[CompanyName]', '', 'Name'))->setRequired(),
                            new TextField('Data[CompanyExtendedName]', '', 'Namenszusatz')
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Adresse der Schule',array(
                            new TextField('Data[CompanyDistrict]', '', 'Ortsteil'),
                            (new TextField('Data[CompanyStreet]', '', 'Straße'))->setRequired(),
                            (new TextField('Data[CompanyCity]', '', 'PLZ / Ort'))->setRequired(),
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new FormTitle(new TileBig().' Informationen Briefkontakt')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kontaktinformation',array(
                            (new TextField('Data[Phone]', '', 'Telefon'))->setRequired(),
                            new TextField('Data[Fax]', '', 'Fax'),
                        ),Panel::PANEL_TYPE_INFO), 4),
                    new FormColumn(
                        new Panel('Internet Präsenz',array(
                            (new TextField('Data[Mail]', '', 'E-Mail'))->setRequired(),
                            new TextField('Data[Web]', '', 'Internet')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Ort, Datum', array(
                            new TextField('Data[Place]', '', 'Ort'),
                            (new TextField('Data[Date]', '', 'Datum'))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
            )), new Primary('Download', null, true) , '\Api\Document\Standard\MultiPassword\Create'
        );
    }

    /**
     * @return Layout
     */
    public function openAccountModal()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(self::receiverAccountService())
                ))
            )
        );
    }

    /**
     * @param array  $PersonIdArray
     * @param string $Type
     *
     * @return Pipeline
     */
    public function serviceAccount($PersonIdArray = array(), $Type = 'S')
    {

        $result = Account::useService()->createAccount($PersonIdArray, $Type);
        return self::pipelineSaveAccountResult($result, $Type);
    }

    /**
     *
     * @var array    $result
     *
     * @param string $Type
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccountResult($result = array(), $Type = 'S')
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModalResult'
        ));
        $Emitter->setPostPayload(array(
            'result' => $result,
            'Type'   => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param array  $result
     * @param string $Type
     *
     * @return string
     */
    public function openAccountModalResult($result = array(), $Type = 'S')
    {

        $Content = '';
        $Time = false;
        if (isset($result['Time']) && $result['Time']) {
            $Time = $result['Time'];
        }

        if (isset($result['AccountExistCount']) && $result['AccountExistCount'] > 0) {
            $Content .= new WarningMessage($result['AccountExistCount'].' Personen haben bereits einen Account (ignoriert)');
        }
        if (isset($result['SuccessCount']) && $result['SuccessCount'] > 0) {
            $Content .= new SuccessMessage($result['SuccessCount'].' Benutzer wurden erfolgreich angelegt.');
        }
        if (isset($result['AccountError']) && $result['AccountError'] > 0) {

            $ErrorLog = array();
            foreach($result as $Key => $item){
                if(is_numeric($Key)){
                    $ErrorLog[] = $item;
                }
            }
            $Error = '';
            if(!empty($ErrorLog)){
                $Error = new Listing($ErrorLog);
            }

            $Content .= new DangerMessage('<div style="padding-bottom: 10px;">'.$result['AccountError'].
                ' Account(s) konnten nicht angelegt werden.</div>'.$Error);

        }

        $BackwardRoute = '/Setting/User/Account/Student/Add';
        if ($Type == 'C') {
            $BackwardRoute = '/Setting/User/Account/Custody/Add';
        }

        if ($Content == '') {
            $Content = new DangerMessage('Es wurden keine Benutzer angelegt')
                .new Container(new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Center(new Standard('Zurück', $BackwardRoute)))
                        )
                    )
                ));
        } else {
            $Content .= new Container(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Center(new Standard('Zurück', $BackwardRoute)
                            .new Standard('Export', '/Setting/User/Account/Export', null, array('Time' => $Time))))
                    )
                )
            ));
        }

        return $Content;
//            .new Code(print_r($result, true));
    }
}