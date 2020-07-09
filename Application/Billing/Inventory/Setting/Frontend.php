<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Api\Billing\Inventory\ApiSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use \SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Repository\WellReadOnly;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function FrontendSetting()
    {

        $Stage = new Stage('Einstellungen', 'für die Fakturierung');

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        ApiSetting::receiverPersonGroup($this->displayPersonGroup())
                    )
                ),
//                new LayoutRow(array(
//                    new LayoutColumn(
//                        ApiSetting::receiverSetting($this->displaySetting(TblSetting::CATEGORY_REGULAR), TblSetting::CATEGORY_REGULAR)
//                    , 6),
//                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiSetting::receiverSetting($this->displaySetting(TblSetting::CATEGORY_SEPA), TblSetting::CATEGORY_SEPA)
                    , 6),
                    new LayoutColumn(
                        ApiSetting::receiverSetting($this->displaySetting(TblSetting::CATEGORY_DATEV), TblSetting::CATEGORY_DATEV)
                    , 6),
                ))
            ))
        ). ApiSetting::receiverModal()
        );

        return $Stage;
    }

    /**
     * @return Layout|string
     */
    public function displayPersonGroup()
    {
        if(($tblSettingGroupPersonList = Setting::useService()->getSettingGroupPersonAll())){
            $tblGroupList = array();
            foreach($tblSettingGroupPersonList as $tblSettingGroupPerson) {
                $tblGroupList[] = $tblSettingGroupPerson->getServiceTblGroupPerson();
            }
            $NameListLeft = array();
            $NameListRight = array();
//            $tblGroupAll = Group::useService()->getGroupAll();
            /** @var TblGroup $tblGroup */
            $tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name');
            $NameListLeft[] = '<div style="height: 7px;"></div>';
            $NameListRight[] = '<div style="height: 7px;"></div>';
            foreach($tblGroupList as $tblGroup) {
                $divStart = '<div style="padding: 2.5px 8px">';
                $divEnd = '</div>';
                if($tblGroup->getMetaTable()){
                    if($tblGroup->getMetaTable() == 'COMMON'){
                        continue;
                    }
//                    if(in_array($tblGroup, $tblGroupList)){
                        $NameListLeft[] = $divStart.new SuccessText(new Check().' '.$tblGroup->getName()).$divEnd;
//                    } else {
//                        $NameListLeft[] = $divStart.new Unchecked().' '.$tblGroup->getName().$divEnd;
//                    }
                } else {
//                    if(in_array($tblGroup, $tblGroupList)){
                        $NameListRight[] = $divStart.new SuccessText(new Check().' '.$tblGroup->getName()).$divEnd;
//                    } else {
//                        $NameListRight[] = $divStart.new Unchecked().' '.$tblGroup->getName().$divEnd;
//                    }
                }
            }
            return new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('Auswahl der Personengruppen für die Beitragsarten: '
                                ,(new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pen()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineShowFormPersonGroup()))
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(new Well(new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Title('Standard Personengruppen')
                                    , 6),
                                new LayoutColumn(
                                    new Title('Individuelle Personengruppen')
                                    , 6),
                                new LayoutColumn(
                                    implode('', $NameListLeft)
//                                    new Listing($NameListLeft)
                                    , 6),
                                new LayoutColumn(
                                    implode('', $NameListRight)
//                                    new Listing($NameListRight)
                                    , 6),
                            ))
                        ))))
                    )
                ))
            );
        }
        return 'Personengruppen konnten nicht geladen werden.';
    }

    /**
     * @return Danger|string
     */
    public function formPersonGroup()
    {
        if(($tblSettingGroupList = Setting::useService()->getSettingGroupPersonAll())){
            $Global = $this->getGlobal();
            foreach($tblSettingGroupList as $tblSettingGroup) {
                if(($tblGroup = $tblSettingGroup->getServiceTblGroupPerson())){
                    $Global->POST['PersonGroup'][$tblGroup->getId()] = $tblGroup->getId();
                }
            }
            $Global->savePost();
        }
        $LeftList = $RightList = array();
        if(($tblGroupAll = Group::useService()->getGroupAll())){
            foreach($tblGroupAll as &$tblGroup) {
                if($tblGroup->getMetaTable() === 'COMMON'){
                    $tblGroup = false;
                }
            }
            $tblGroupAll = array_filter($tblGroupAll);

            $tblGroupAll = $this->getSorter($tblGroupAll)->sortObjectBy('Name');
            // sort left Standard, right Individual
            array_walk($tblGroupAll, function(TblGroup $tblGroup) use (&$LeftList, &$RightList){
                if($tblGroup->getMetaTable()){
                    $LeftList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(),
                        $tblGroup->getId());
                } else {
                    $RightList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(),
                        $tblGroup->getId());
                }
            });

            return new Title('Auswahl der Personengruppen für die Beitragsarten:')
                .new Well((new Form(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(new FormTitle('Standard Personengruppen'), 6),
                            new FormColumn(new FormTitle('Individuelle Personengruppen'), 6),
                            new FormColumn($LeftList, 6),
                            new FormColumn($RightList, 6),
                            new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                (new Primary('Speichern', ApiSetting::getEndpoint(), new Save()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineSavePersonGroup())
                                .(new Primary('Abbrechen', ApiSetting::getEndpoint(), new Disable()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineShowPersonGroup())
                            )))))
                        ))
                    )
                ))->disableSubmitAction());
        } else {
            return new Danger('Es stehen keine Gruppen zur Verfügung!');
        }
    }

    /**
     * @param string $Category
     *
     * @return Layout
     */
    public function displaySetting($Category = TblSetting::CATEGORY_REGULAR)
    {

        $tblSettingList = Setting::useService()->getSettingAllByCategory($Category);
        $Listing = array();
        if($tblSettingList){
            foreach($tblSettingList as &$tblSetting){
                switch($tblSetting->getIdentifier()){
                    // REGULAR
                    // noch leer
                    // SEPA
                    case TblSetting::IDENT_IS_SEPA:
                        $Listing[0] ='&nbsp;Eingabepflicht relevanter Eingaben für SEPA-Lastschrift: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText(new Check())
                                : new DangerText(new Unchecked())));
                        break;
                    case TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER:
                        $Listing[1] ='&nbsp;Vorschlag höchste Mandatsreferenznummer: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText(new Check())
                                : new DangerText(new Unchecked())));
                        break;
                    case TblSetting::IDENT_SEPA_REMARK:
                        $Listing[2] ='&nbsp;SEPA-Verwendungszweck &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : 'Nicht hinterlegt '.new ToolTip(new Info(), 'Eingabe wird automatisch für alle Beitragsarten als
                                 Grundwert bestimmt. Individuelle anpassungen können an der Beitragsart hinerlegt werden.')));
                        break;
                    case TblSetting::IDENT_SEPA_FEE:
                        $Listing[3] ='&nbsp;Kosten Rücklastschrift &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : 'Nicht hinterlegt '));
                        break;

                    // DATEV
                    case TblSetting::IDENT_IS_DATEV:
                        $Listing[0] = '&nbsp;Eingabepflicht relevanter Eingaben für DATEV: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText(new Check())
                                : new DangerText(new Unchecked())));
                        break;
                    case TblSetting::IDENT_DEBTOR_NUMBER_COUNT:
                        $Listing[1] = '&nbsp;Länge der Debitoren-Nr.: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : new DangerText('Nicht hinterlegt!')));
                        break;
                    case TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER:
                        $Listing[2] ='&nbsp;Vorschlag höchste Debitorennummer: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText(new Check())
                                : new DangerText(new Unchecked())));
                        break;
                    case TblSetting::IDENT_CONSULT_NUMBER:
                        $Listing[3] = '&nbsp;Beraternummer: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : new DangerText('Nicht hinterlegt!')));
                        break;
                    case TblSetting::IDENT_CLIENT_NUMBER:
                        $Listing[4] = '&nbsp;Mandantennummer: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : new DangerText('Nicht hinterlegt!')));
                        break;
                    case TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH:
                        $Listing[5] = '&nbsp;Länge der Sachkontennummern: &nbsp;'
                            .new Bold(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue())
                                : new DangerText('Nicht hinterlegt!')));
                        break;
                    case TblSetting::IDENT_DATEV_REMARK:
                        $Listing[6] ='&nbsp;DATEV-Buchungstext: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : 'Nicht hinterlegt '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als 
                                Standardwert verwendet. Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                    case TblSetting::IDENT_FIBU_ACCOUNT:
                        $Listing[7] ='&nbsp;FiBu-Konto: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : ' '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet. 
                                Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                    case TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR:
                        $Listing[8] ='&nbsp;FiBu-Konto ist die Debitor-Nr.: &nbsp;'
                            .new Bold($tblSetting->getValue()
                                ? new SuccessText(new Check())
                                : new Unchecked());
                        break;
                    case TblSetting::IDENT_FIBU_TO_ACCOUNT:
                        $Listing[9] ='&nbsp;FiBu-Gegenkonto: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : ' '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                    case TblSetting::IDENT_KOST_1:
                        $Listing[10] ='&nbsp;Kostenstelle 1: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : ' '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                    case TblSetting::IDENT_KOST_2:
                        $Listing[11] ='&nbsp;Kostenstelle 2: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : ' '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                    case TblSetting::IDENT_BU_KEY:
                        $Listing[12] ='&nbsp;BU-Schlüssel: &nbsp;'
                            .new Bold(new ToolTip(($tblSetting->getValue()
                                ? new SuccessText($tblSetting->getValue().' '.new Info())
                                : ' '.new Info()), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                        break;
                }
            }
        }

        ksort($Listing);
        $Title = $this->getTitleByCategory($Category);
        return new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title($Title,
                        (new Link('Bearbeiten', ApiSetting::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiSetting::pipelineShowFormSetting($Category))))
            ),
            new LayoutRow(array(
                new LayoutColumn(
                    new Well(new Listing($Listing))
                ),
            ))
        )));
    }

    /**
     * @param string $Category
     *
     * @return Layout
     */
    public function formSetting($Category = TblSetting::CATEGORY_REGULAR)
    {

        $elementList = array();
        $tblSettingList = Setting::useService()->getSettingAllByCategory($Category);
        foreach($tblSettingList as &$tblSetting){

            switch($tblSetting->getIdentifier()){
                    // Regular Option's
                // erstmal leer
                    // Sepa Option's
                case TblSetting::IDENT_IS_SEPA:
                    // Sepa ElementGroup
                    $SepaElementInWell = new WellReadOnly(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                new CheckBox('Setting['.TblSetting::IDENT_IS_SEPA.']', ' Eingabepflicht relevanter Eingaben für SEPA-Lastschrift aktivieren', true)
                            ),
                            new LayoutColumn(
                                $this->showSepaInfo()
                            ),
                        ))))
                    );
                    $_POST['Setting'][TblSetting::IDENT_IS_SEPA] = $tblSetting->getValue();
                    $elementList[0] = $SepaElementInWell;
//                    $elementList[0] = new CheckBox('Setting['.TblSetting::IDENT_IS_SEPA.']', ' Eingabepflicht relevanter Eingaben für SEPA-Lastschrift aktivieren', true);
//                    $elementList[1] = $this->showSepaInfo();
                break;
                case TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER:
                    $_POST['Setting'][TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER] = $tblSetting->getValue();
                    $elementList[2] = new CheckBox('Setting['.TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER.']', ' Vorschlag höchste Mandatsreferenznummer', true);
                break;
                case TblSetting::IDENT_SEPA_REMARK:
                    $_POST['Setting'][TblSetting::IDENT_SEPA_REMARK] = $tblSetting->getValue();
                    $elementList[3] =
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(new Panel('SEPA-Verwendungszweck', array(
                                new TextField('Setting['.TblSetting::IDENT_SEPA_REMARK.']', '', ''),
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(new Bold('Freifelder für Verwendungszweck')),
                                    new LayoutColumn('[GID] Gläubiger-ID', 4),
                                    new LayoutColumn('[SN] Mandantsreferenz&shy;nummer', 4),
                                    new LayoutColumn('[BVN] Beitragsverursacher Name', 4),
                                    new LayoutColumn('[BVV] Beitragsverursacher Vorname', 4),
                                    new LayoutColumn('[BA] Beitragsart', 4),
                                    new LayoutColumn('[BAEP] Beitragsart mit Einzelpreis', 4),
                                    new LayoutColumn('[DEB] Debitoren-Nr.', 4),
                                    new LayoutColumn('[BAM] Abrechnungszeitraum (Jahr+Monat)', 4),
                                )))),
                            ), Panel::PANEL_TYPE_INFO)),
                        ))));
                break;
                case TblSetting::IDENT_SEPA_FEE:
                    $_POST['Setting'][TblSetting::IDENT_SEPA_FEE] = $tblSetting->getValue();
                    $elementList[4] = new TextField('Setting['.TblSetting::IDENT_SEPA_FEE.']', 'z.B.: 3,85', 'Kosten einer Rücklastschrift');
                    break;

                    // Datev Option's
                case TblSetting::IDENT_IS_DATEV:
                    // Datev ElementGroup
                    $DatevElementInWell = new WellReadOnly(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                new CheckBox('Setting['.TblSetting::IDENT_IS_DATEV.']', ' Eingabepflicht relevanter Eingaben Für DATEV aktivieren', true)
                            ),
                            new LayoutColumn(
                                $this->showDatevInfo()
                            ),
                        ))))
                    );
                    $_POST['Setting'][TblSetting::IDENT_IS_DATEV] = $tblSetting->getValue();
                    $elementList[0] = $DatevElementInWell;
                break;
                case TblSetting::IDENT_DEBTOR_NUMBER_COUNT:
                    $_POST['Setting'][TblSetting::IDENT_DEBTOR_NUMBER_COUNT] = $tblSetting->getValue();
                    $elementList[1] = new TextField('Setting['.TblSetting::IDENT_DEBTOR_NUMBER_COUNT.']', '', 'Länge der Debitoren-Nr.');
                break;
                case TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER:
                    $_POST['Setting'][TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER] = $tblSetting->getValue();
                    $elementList[2] = new CheckBox('Setting['.TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER.']', ' Vorschlag höchste Debitorennummer', true);
                break;
                case TblSetting::IDENT_CONSULT_NUMBER:
                    $_POST['Setting'][TblSetting::IDENT_CONSULT_NUMBER] = $tblSetting->getValue();
                    $elementList[3] = new TextField('Setting['.TblSetting::IDENT_CONSULT_NUMBER.']', '', 'Beraternummer '
                    .new ToolTip(new Info(), 'Kann bis zu 7 Zeichen enthalten'));
                    break;
                case TblSetting::IDENT_CLIENT_NUMBER:
                    $_POST['Setting'][TblSetting::IDENT_CLIENT_NUMBER] = $tblSetting->getValue();
                    $elementList[4] = new TextField('Setting['.TblSetting::IDENT_CLIENT_NUMBER.']', '', 'Mandantennummer '
                    .new ToolTip(new Info(), 'Kann bis zu 5 Zeichen enthalten'));
                    break;
                case TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH:
                    $_POST['Setting'][TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH] = $tblSetting->getValue();
                    $elementList[5] = new NumberField('Setting['.TblSetting::IDENT_PROPER_ACCOUNT_NUMBER_LENGTH.']', '', 'Länge der Sachkontennummern '
                        .new ToolTip(new Info(), 'Bitte geben Sie eine Anzahl zwischen 4 und 8 an'));
                break;
                case TblSetting::IDENT_DATEV_REMARK:
                    $_POST['Setting'][TblSetting::IDENT_DATEV_REMARK] = $tblSetting->getValue();
                    $elementList[6] =
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(new Panel('DATEV-Buchungstext '.new ToolTip(new Info(), 'Datev erlaubt maximal 60 Zeichen, der rest wird abgeschnitten'), array(
                                new TextField('Setting['.TblSetting::IDENT_DATEV_REMARK.']', '', ''),
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(new Bold('Freifelder für Buchungstext')),
                                    new LayoutColumn('[GID] Gläubiger-ID', 4),
                                    new LayoutColumn('[SN] Mandantsreferenz&shy;nummer', 4),
                                    new LayoutColumn('[BVN] Beitragsverursacher Name', 4),
                                    new LayoutColumn('[BVV] Beitragsverursacher Vorname', 4),
                                    new LayoutColumn('[BA] Beitragsart', 4),
                                    new LayoutColumn('[BAEP] Beitragsart mit Einzelpreis', 4),
                                    new LayoutColumn('[DEB] Debitoren-Nr.', 4),
                                    new LayoutColumn('[BAM] Abrechnungszeitraum (Jahr+Monat)', 4),
                                )))),
                            ), Panel::PANEL_TYPE_INFO)),
                        ))));
                break;
                case TblSetting::IDENT_FIBU_ACCOUNT:
                    $_POST['Setting'][TblSetting::IDENT_FIBU_ACCOUNT] = $tblSetting->getValue();
                    $elementList[7] = new TextField('Setting['.TblSetting::IDENT_FIBU_ACCOUNT.']', '', 'Fibu-Konto '
                        .new ToolTip(new Info(), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.')
                        , null, '99999999');
                break;
                case TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR:
                    $_POST['Setting'][TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR] = $tblSetting->getValue();
                    $elementList[8] = new CheckBox('Setting['.TblSetting::IDENT_FIBU_ACCOUNT_AS_DEBTOR.']', 'FiBu-Konto ist die Debitor-Nr.', true);
                break;
                case TblSetting::IDENT_FIBU_TO_ACCOUNT:
                    $_POST['Setting'][TblSetting::IDENT_FIBU_TO_ACCOUNT] = $tblSetting->getValue();
                    $elementList[9] = new TextField('Setting['.TblSetting::IDENT_FIBU_TO_ACCOUNT.']', '', 'Fibu-Gegenkonto '
                        .new ToolTip(new Info(), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.')
                        , null, '99999999');
                break;
                case TblSetting::IDENT_KOST_1:
                    $_POST['Setting'][TblSetting::IDENT_KOST_1] = $tblSetting->getValue();
                    $elementList[10] = new NumberField('Setting['.TblSetting::IDENT_KOST_1.']', '', 'Kostenstelle 1 '
                        .new ToolTip(new Info(), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                break;
                case TblSetting::IDENT_KOST_2:
                    $_POST['Setting'][TblSetting::IDENT_KOST_2] = $tblSetting->getValue();
                    $elementList[11] = new NumberField('Setting['.TblSetting::IDENT_KOST_2.']', '', 'Kostenstelle 2 '
                        .new ToolTip(new Info(), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                break;
                case TblSetting::IDENT_BU_KEY:
                    $_POST['Setting'][TblSetting::IDENT_BU_KEY] = $tblSetting->getValue();
                    $elementList[12] = new NumberField('Setting['.TblSetting::IDENT_BU_KEY.']', '', 'BU-Schlüssel '
                        .new ToolTip(new Info(), 'Diese Vorgabe wird für alle Beitragsarten als Standardwert verwendet.
                                 Individuelle Einstellungen können an der Beitragsart vorgenommen werden.'));
                break;
            }
        }
        ksort($elementList);
        $Title = $this->getTitleByCategory($Category);

        return new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title($Title)
                )
            ),
            new LayoutRow(
                new LayoutColumn(new Well(
                    (new Form(new FormGroup(array(
                        new FormRow(new FormColumn($elementList, 12)),
                        new FormRow(new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            (new Primary('Speichern', ApiSetting::getEndpoint(), new Save()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineSaveSetting($Category))
                            .(new Primary('Abbrechen', ApiSetting::getEndpoint(), new Disable()))
                                ->ajaxPipelineOnClick(ApiSetting::pipelineShowSetting($Category))
                        ))))))
                    ))))->disableSubmitAction()
                ))
            )
        )));
    }

    /**
     * @return Warning
     */
    private function showSepaInfo()
    {

        $Content = new Warning(new Container('- Bei der Bezahlart "SEPA-Lastschrift" werden folgende Felder zu
                    Pflichtangaben: Kontodaten, Mandatsreferenznummer')
                    .new Container('- Ermöglicht den Download einer SEPA-XML-Datei für externe Banking-Programme')
            , null, false, 5, 0);
        return $Content;
    }

    /**
     * @return Warning
     */
    private function showDatevInfo()
    {

        $Content = new Warning(
                     new Container('- Bei der Verwendung von DATEV wird folgendes Feld zur Pflichtangabe: Debitorennummer')
                    .new Container('- Ermöglicht den Download einer DATEV-CSV-Datei für externe Programme')
            , null, false, 5, 0);
        return $Content;
    }

    private function getTitleByCategory($Category = '')
    {

        $Title = '';
        switch($Category){
            case TblSetting::CATEGORY_REGULAR:
                $Title = 'Allgemeine Einstellungen:';
                break;
            case TblSetting::CATEGORY_SEPA:
                $Title = 'SEPA Einstellungen:';
                break;
            case TblSetting::CATEGORY_DATEV:
                $Title = 'DATEV Einstellungen:';
                break;
        }
        return $Title;
    }
}