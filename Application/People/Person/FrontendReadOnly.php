<?php

namespace SPHERE\Application\People\Person;

use SPHERE\Application\Api\Contact\ApiAddressToPerson;
use SPHERE\Application\Api\Contact\ApiMailToPerson;
use SPHERE\Application\Api\Contact\ApiPhoneToPerson;
use SPHERE\Application\Api\Contact\ApiRelationshipToCompany;
use SPHERE\Application\Api\Contact\ApiRelationshipToPerson;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Filter\Service as FilterService;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendStudent;
use SPHERE\Application\People\Person\Frontend\FrontendStudentIntegration;
use SPHERE\Application\People\Person\Frontend\FrontendTeacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class FrontendReadOnly
 *
 * @package SPHERE\Application\People\Person
 */
class FrontendReadOnly extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPersonCreate()
    {
        $stage = new Stage('Person', 'Datenblatt anlegen');
        if (Access::useService()->hasAuthorization('/Api/People/Person/ApiPersonEdit')) {
            $createPersonContent = ApiPersonEdit::receiverBlock(
                (new FrontendBasic())->getCreatePersonContent(), 'PersonContent'
            );
        } else {
            $createPersonContent = new Danger('Sie haben nicht das Recht neue Personen anzulegen', new Exclamation());
        }

        $stage->setContent(
            $createPersonContent
        );

        return $stage;
    }

    /**
     *
     * @param null|int $Id
     * @param null|int $Group
     *
     * @return Stage
     */
    public function frontendPersonReadOnly($Id = null, $Group = null)
    {

        $stage = new Stage('Person', 'Datenblatt ' . ($Id ? 'bearbeiten' : 'anlegen'));
        $stage->addButton(
            new Standard('Zurück', '/People/Search/Group', new ChevronLeft(), array('Id' => $Group))
        );

        // Person bearbeiten
        if ($Id != null && ($tblPerson = Person::useService()->getPersonById($Id))) {

            $validationMessage = FilterService::getPersonMessageTable($tblPerson);

            $basicContent = ApiPersonReadOnly::receiverBlock(
                FrontendBasic::getBasicContent($Id), 'BasicContent'
            );

            $commonContent = ApiPersonReadOnly::receiverBlock(
                FrontendCommon::getCommonContent($Id), 'CommonContent'
            );

            $prospectContent = ApiPersonReadOnly::receiverBlock(
                FrontendProspect::getProspectContent($Id), 'ProspectContent'
            );

            $teacherContent = ApiPersonReadOnly::receiverBlock(
                FrontendTeacher::getTeacherContent($Id), 'TeacherContent'
            );

            $custodyContent = ApiPersonReadOnly::receiverBlock(
                FrontendCustody::getCustodyContent($Id), 'CustodyContent'
            );

            $clubContent = ApiPersonReadOnly::receiverBlock(
                FrontendClub::getClubContent($Id), 'ClubContent'
            );


            $studentContent = ApiPersonReadOnly::receiverBlock(
              FrontendStudent::getStudentTitle($Id), 'StudentContent'
            );

            $integrationContent = ApiPersonReadOnly::receiverBlock(
                FrontendStudentIntegration::getIntegrationTitle($Id), 'IntegrationContent'
            );

            $addressReceiver = ApiAddressToPerson::receiverBlock(Address::useFrontend()->frontendLayoutPersonNew($tblPerson),
                'AddressToPersonContent');
            $addressContent = ApiAddressToPerson::receiverModal()
                . TemplateReadOnly::getContent(
                    'Adressdaten',
                    $addressReceiver,
                    array(
                        (new Link(
                            new Plus() . ' Adresse hinzufügen',
                            ApiAddressToPerson::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenCreateAddressToPersonModal($tblPerson->getId()))
                    ),
                    'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                    new MapMarker()
                );

            $phoneReceiver = ApiPhoneToPerson::receiverBlock(Phone::useFrontend()->frontendLayoutPersonNew($tblPerson),
                'PhoneToPersonContent');
            $phoneContent = ApiPhoneToPerson::receiverModal()
                . TemplateReadOnly::getContent(
                    'Telefonnummern',
                    $phoneReceiver,
                    array(
                        (new Link(
                            new Plus() . ' Telefonnummer hinzufügen',
                            ApiPhoneToPerson::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenCreatePhoneToPersonModal($tblPerson->getId())),
                    ),
                    'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                    new \SPHERE\Common\Frontend\Icon\Repository\Phone()
                );

            $mailReceiver = ApiMailToPerson::receiverBlock(Mail::useFrontend()->frontendLayoutPersonNew($tblPerson),
                'MailToPersonContent');
            $mailContent = ApiMailToPerson::receiverModal()
                . TemplateReadOnly::getContent(
                'E-Mail Adressen',
                $mailReceiver,
                array(
                    (new Link(
                        new Plus() . '  E-Mail Adresse hinzufügen',
                        ApiMailToPerson::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenCreateMailToPersonModal($tblPerson->getId())),
                ),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new \SPHERE\Common\Frontend\Icon\Repository\Mail()
            );

            $relationshipToPersonReceiver = ApiRelationshipToPerson::receiverBlock(Relationship::useFrontend()->frontendLayoutPersonNew($tblPerson),
                'RelationshipToPersonContent');
            $relationshipToCompanyReceiver = ApiRelationshipToCompany::receiverBlock(Relationship::useFrontend()->frontendLayoutCompanyNew($tblPerson),
                'RelationshipToCompanyContent');
            $relationshipContent = TemplateReadOnly::getContent(
                'Beziehungen',
                $relationshipToPersonReceiver
                . $relationshipToCompanyReceiver,
                array(
                    (new Link(
                        new Plus() . '  Personenbeziehung hinzufügen',
                        ApiRelationshipToPerson::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiRelationshipToPerson::pipelineOpenCreateRelationshipToPersonModal($tblPerson->getId())),
                    (new Link(
                        new Plus() . '  Institutionenbeziehung hinzufügen',
                        ApiRelationshipToCompany::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiRelationshipToCompany::pipelineOpenCreateRelationshipToCompanyModal($tblPerson->getId())),
                ),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())) . ' zu Personen und Institutionen',
                new \SPHERE\Common\Frontend\Icon\Repository\Link(),
                false
            );

            $stage->setContent(
                ($validationMessage ? $validationMessage : '')
                . $basicContent
                . $commonContent
                . $prospectContent
                . $teacherContent
                . $studentContent
                . $custodyContent
                . $clubContent
                . $integrationContent

                . $addressContent
                . $phoneContent
                . $mailContent
                . $relationshipContent
            );
        // neue Person anlegen
        } else {
            if (Access::useService()->hasAuthorization('/Api/People/Person/ApiPersonEdit')) {
                $createPersonContent = ApiPersonEdit::receiverBlock(
                    (new FrontendBasic())->getCreatePersonContent(), 'PersonContent'
                );
            } else {
                $createPersonContent = new Danger('Sie haben nicht das Recht neue Personen anzulegen', new Exclamation());
            }

            $stage->setContent(
                $createPersonContent
            );
        }

        return $stage;
    }

    /**
     * @param string $label
     * @param int $size
     *
     * @return LayoutColumn
     */
    public static function getLayoutColumnLabel($label, $size = 2)
    {
        return new LayoutColumn(new Bold($label . ':'), $size);
    }

    /**
     * @param string $value
     * @param int $size
     * @return LayoutColumn
     */
    public static function getLayoutColumnValue($value, $size = 2)
    {
        return new LayoutColumn($value ? $value : '&ndash;', $size);
    }

    /**
     * @param int $size
     *
     * @return LayoutColumn
     */
    public static function getLayoutColumnEmpty($size = 2)
    {
        return new LayoutColumn('&nbsp;', $size);
    }

    /**
     * @return Danger
     */
    public static function getDataProtectionMessage()
    {

        return new Danger(
            new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    protected static function getEditTitleDescription(TblPerson $tblPerson = null)
    {
        return 'der Person'
            . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '')
            . ' bearbeiten';
    }

    /**
     * @param $title
     * @param $content
     * @param string $options
     * @param string $panelType
     * @return Panel
     */
    public static function getContactPanel($title, $content, $options = '', $panelType = Panel::PANEL_TYPE_DEFAULT)
    {
        return new Panel(
            $title . ($options ? new PullRight($options) : ''),
            $content,
            $panelType
        );
    }

    /**
     * @param string $title
     * @param array|string $content
     *
     * @return Panel
     */
    public static function getSubContent($title, $content)
    {

        if (!is_array($content)) {
            $content = array($content);
        }

        if ($title != '') {
            array_unshift($content, new Bold(new \SPHERE\Common\Frontend\Text\Repository\Info($title)));
        }
        array_unshift($content, '&nbsp;');

        return new Panel(
            '',
            $content,
            Panel::PANEL_TYPE_INFO
        );
    }
}