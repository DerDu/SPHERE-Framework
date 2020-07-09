<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Api\Contact\ApiMailToCompany;
use SPHERE\Application\Api\Contact\ApiMailToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Mail
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formAddressToPerson($PersonId, $ToPersonId = null, $setPost = false)
    {

        if ($ToPersonId && ($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Address']['Mail'] = $tblToPerson->getTblMail()->getAddress();
                $Global->POST['Address']['Alias'] = $tblToPerson->isAccountUserAlias();
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->savePost();
            }
        }

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineEditMailToPersonSave($PersonId, $ToPersonId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineCreateMailToPersonSave($PersonId));
        }

        $tblTypeAll = Mail::useService()->getTypeAll();

        // Account exist?
        $isConnexion = false;
        if(($tblConsumer = Consumer::useService()->getConsumerBySession())){
            if(($tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumer($tblConsumer))){
                if($tblConsumerLogin->getSystemName() == TblConsumerLogin::VALUE_SYSTEM_UCS){
                    $isConnexion = true;
                }
            }
        }

        $CheckBox = '';
        if($isConnexion){
            $tblPerson = Person::useService()->getPersonById($PersonId);
            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
                $CheckBox = new CheckBox('Address[Alias]', 'E-Mail als CONNEXION Benutzername verwenden', 1);
            } else {
                $CheckBox = new ToolTip((new CheckBox('Address[Alias]', 'E-Mail als CONNEXION Benutzername verwenden', 1))
                    ->setDisabled(), 'Person benötigt ein Benutzerkonto');
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('E-Mail Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new MailField('Address[Mail]', 'E-Mail Adresse', 'E-Mail Adresse', new MailIcon() ))->setRequired(),
                                $CheckBox
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6
                    ),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $CompanyId
     * @param null $ToCompanyId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formAddressToCompany($CompanyId, $ToCompanyId = null, $setPost = false)
    {

        if ($ToCompanyId && ($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Address'] = $tblToCompany->getTblMail()->getAddress();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->savePost();
            }
        }

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToCompany::pipelineEditMailToCompanySave($CompanyId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToCompany::pipelineCreateMailToCompanySave($CompanyId));
        }

        $tblTypeAll = Mail::useService()->getTypeAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('E-Mail Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new MailField('Address', 'E-Mail Adresse', 'E-Mail Adresse', new MailIcon() ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6
                    ),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function frontendLayoutPersonNew(TblPerson $tblPerson)
    {

        $mailList = array();
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))){
            foreach ($tblMailList as $tblToPerson) {
                if (($tblMail = $tblToPerson->getTblMail())) {
                    $mailList[$tblMail->getId()][$tblToPerson->getTblType()->getId()][$tblPerson->getId()] = $tblToPerson;
                }
            }
        }

        if (($tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblPersonRelationship = $tblRelationship->getServiceTblPersonFrom();
                    } else {
                        $tblPersonRelationship = $tblRelationship->getServiceTblPersonTo();
                    }
                    $tblRelationshipMailAll = Mail::useService()->getMailAllByPerson($tblPersonRelationship);
                    if ($tblRelationshipMailAll) {
                        foreach ($tblRelationshipMailAll as $tblToPerson) {
                            if (($tblMail = $tblToPerson->getTblMail())) {
                                $mailList[$tblMail->getId()][$tblToPerson->getTblType()->getId()][$tblPersonRelationship->getId()] = $tblToPerson;
                            }
                        }
                    }
                }
            }
        }

        if (empty($mailList)) {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail Adressen hinterlegt')))));
        } else {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($mailList as $mailId => $typeArray) {
                if (($tblMail = Mail::useService()->getMailById($mailId))) {
                    foreach ($typeArray as $typeId => $personArray) {
                        if (($tblType = Mail::useService()->getTypeById($typeId))) {
                            $content = array();
                            if (isset($personArray[$tblPerson->getId()])) {
                                /** @var TblToPerson $tblToPerson */
                                $tblToPerson = $personArray[$tblPerson->getId()];
                                $panelType = Panel::PANEL_TYPE_SUCCESS;
                                $options =
                                    (new Link(
                                        new Edit(),
                                        ApiMailToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Bearbeiten'
                                    ))->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenEditMailToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ))
                                    . ' | '
                                    . (new Link(
                                        new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                        ApiMailToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Löschen'
                                    ))->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenDeleteMailToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ));
                            } else {
                                $panelType = Panel::PANEL_TYPE_DEFAULT;
                                $options = '';
                            }

                            $content[] = new Mailto($tblMail->getAddress(), $tblMail->getAddress(), new Envelope());

                            if (isset($personArray[$tblPerson->getId()])) {
                                if(($tblToPersonCurrent =  $personArray[$tblPerson->getId()])){
                                    /** @var $tblToPersonCurrent TblToPerson */
                                    if($tblToPersonCurrent->isAccountUserAlias()){
                                        $content[] = new Check().' CONNEXION Benutzername';
                                    }
                                }
                            }

                            /**
                             * @var TblToPerson $tblToPerson
                             */
                            foreach ($personArray as $personId => $tblToPerson) {
                                if (($tblPersonMail = Person::useService()->getPersonById($personId))) {
                                    $content[] = ($tblPerson->getId() != $tblPersonMail->getId()
                                            ? new Link(
                                                new PersonIcon() . ' ' . $tblPersonMail->getFullName(),
                                                '/People/Person',
                                                null,
                                                array('Id' => $tblPersonMail->getId()),
                                                'Zur Person'
                                            )
                                            : $tblPersonMail->getFullName())
//                                        . (($remark = $tblToPerson->getRemark())  ? ' ' . new ToolTip(new Info(), $remark) : '');
                                        . (($remark = $tblToPerson->getRemark())  ? ' ' . new Small(new Muted($remark)) : '');
                                }
                            }

                            $panel = FrontendReadOnly::getContactPanel(
                                new MailIcon() . ' ' . $tblType->getName(),
                                $content,
                                $options,
                                $panelType
                            );

                            if ($LayoutRowCount % 4 == 0) {
                                $LayoutRow = new LayoutRow(array());
                                $LayoutRowList[] = $LayoutRow;
                            }
                            $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                            $LayoutRowCount++;
                        }
                    }
                }
            }

            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        }
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return string
     */
    public function frontendLayoutCompanyNew(TblCompany $tblCompany)
    {

        if (($tblMailList = Mail::useService()->getMailAllByCompany($tblCompany))){
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($tblMailList as $tblToCompany) {
                if (($tblMail = $tblToCompany->getTblMail())
                    && ($tblType = $tblToCompany->getTblType())
                ) {
                    $content = array();

                    $panelType = (preg_match('!Notfall!is',
                        $tblType->getName() . ' ' . $tblType->getDescription())
                        ? Panel::PANEL_TYPE_DANGER
                        : Panel::PANEL_TYPE_SUCCESS
                    );

                    $options =
                        (new Link(
                            new Edit(),
                            ApiMailToCompany::getEndpoint(),
                            null,
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiMailToCompany::pipelineOpenEditMailToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ))
                        . ' | '
                        . (new Link(
                            new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                            ApiMailToCompany::getEndpoint(),
                            null,
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiMailToCompany::pipelineOpenDeleteMailToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ));

                    $content[] = new Mailto($tblMail->getAddress(), $tblMail->getAddress(), new Envelope());
                    if (($remark = $tblToCompany->getRemark())) {
                        $content[] = new Muted($remark);
                    }

                    $panel = FrontendReadOnly::getContactPanel(
                        new MailIcon() . ' ' . $tblType->getName(),
                        $content,
                        $options,
                        $panelType
                    );

                    if ($LayoutRowCount % 4 == 0) {
                        $LayoutRow = new LayoutRow(array());
                        $LayoutRowList[] = $LayoutRow;
                    }
                    $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                    $LayoutRowCount++;
                }
            }

            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        } else {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail Adressen hinterlegt')))));
        }
    }
}
