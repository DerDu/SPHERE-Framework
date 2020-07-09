<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.01.2019
 * Time: 13:28
 */

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;

/**
 * Class ApiMailToCompany
 *
 * @package SPHERE\Application\Api\Contact
 */
class ApiMailToCompany extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadMailToCompanyContent');

        $Dispatcher->registerMethod('openCreateMailToCompanyModal');
        $Dispatcher->registerMethod('saveCreateMailToCompanyModal');

        $Dispatcher->registerMethod('openEditMailToCompanyModal');
        $Dispatcher->registerMethod('saveEditMailToCompanyModal');

        $Dispatcher->registerMethod('openDeleteMailToCompanyModal');
        $Dispatcher->registerMethod('saveDeleteMailToCompanyModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineLoadMailToCompanyContent($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MailToCompanyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMailToCompanyContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateMailToCompanyModal($CompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateMailToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineCreateMailToCompanySave($CompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateMailToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditMailToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditMailToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineEditMailToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditMailToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteMailToCompanyModal($CompanyId, $ToCompanyId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteMailToCompanyModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteMailToCompanySave($CompanyId, $ToCompanyId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteMailToCompanyModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CompanyId' => $CompanyId,
            'ToCompanyId' => $ToCompanyId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CompanyId
     *
     * @return string
     */
    public function loadMailToCompanyContent($CompanyId)
    {
        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return Mail::useFrontend()->frontendLayoutCompanyNew($tblCompany);
    }

    /**
     * @param $CompanyId
     *
     * @return string
     */
    public function openCreateMailToCompanyModal($CompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        return $this->getMailToCompanyModal(Mail::useFrontend()->formAddressToCompany($CompanyId), $tblCompany);
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openEditMailToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        return $this->getMailToCompanyModal(Mail::useFrontend()->formAddressToCompany($CompanyId, $ToCompanyId, true), $tblCompany, $ToCompanyId);
    }

    /**
     * @param $form
     * @param TblCompany $tblCompany
     * @param null $ToCompanyId
     *
     * @return string
     */
    private function getMailToCompanyModal($form, TblCompany $tblCompany,  $ToCompanyId = null)
    {
        if ($ToCompanyId) {
            $title = new Title(new Edit() . ' E-Mail Adresse bearbeiten');
        } else {
            $title = new Title(new Plus() . ' E-Mail Adresse hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new Building() . ' Institution',
                                    new Bold($tblCompany ? $tblCompany->getDisplayName() : ''),
                                    Panel::PANEL_TYPE_SUCCESS

                                )
                            )
                        ),
                    )),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return string
     */
    public function openDeleteMailToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' E-Mail Adresse löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Institution',
                                new Bold($tblCompany->getDisplayName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                            . new Panel(new Question() . ' Diese E-Mail Adresse wirklich löschen?', array(
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                                ($tblMail = $tblToCompany->getTblMail()) ? $tblMail->getAddress() : '',
                                ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                            ),
                                Panel::PANEL_TYPE_DANGER)
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteMailToCompanySave($CompanyId, $ToCompanyId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $CompanyId
     * @param $Address
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveCreateMailToCompanyModal($CompanyId, $Address, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        $mailAddress = str_replace(' ', '', $Address);
        if (($form = Mail::useService()->checkFormMailToCompany($tblCompany, $mailAddress, $Type))) {
            // display Errors on form
            return $this->getMailToCompanyModal($form, $tblCompany);
        }

        if (Mail::useService()->createMailToCompany($tblCompany, $mailAddress, $Type)) {
            return new Success('Die E-Mail Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadMailToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     * @param $Address
     * @param $Type
     *
     * @return Danger|string
     */
    public function saveEditMailToCompanyModal($CompanyId, $ToCompanyId, $Address, $Type)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        $mailAddress = str_replace(' ', '', $Address);
        if (($form = Mail::useService()->checkFormMailToCompany($tblCompany, $mailAddress, $Type, $tblToCompany))) {
            // display Errors on form
            return $this->getMailToCompanyModal($form, $tblCompany, $ToCompanyId);
        }

        if (Mail::useService()->updateMailToCompany($tblToCompany, $mailAddress, $Type)) {
            return new Success('Die E-Mail Adresse wurde erfolgreich gespeichert.')
                . self::pipelineLoadMailToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $CompanyId
     * @param $ToCompanyId
     *
     * @return Danger|string
     */
    public function saveDeleteMailToCompanyModal($CompanyId, $ToCompanyId)
    {

        if (!($tblCompany = Company::useService()->getCompanyById($CompanyId))) {
            return new Danger('Die Institution wurde nicht gefunden', new Exclamation());
        }

        if (!($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            return new Danger('Die E-Mail Adresse wurde nicht gefunden', new Exclamation());
        }

        if (Mail::useService()->removeMailToCompany($tblToCompany)) {
            return new Success('Die E-Mail Adresse wurde erfolgreich gelöscht.')
                . self::pipelineLoadMailToCompanyContent($CompanyId)
                . self::pipelineClose();
        } else {
            return new Danger('Die E-Mail Adresse konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}