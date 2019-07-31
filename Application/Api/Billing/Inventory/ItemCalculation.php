<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class ItemCalculation
 * @package SPHERE\Application\Api\Billing\Inventory
 *
 * ApiItem -> ItemVariant -> ItemCalculation
 */
class ItemCalculation extends Extension
{

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddCalculationModal($Identifier, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId'  => $VariantId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddCalculation($Identifier, $VariantId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveAddCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'VariantId'  => $VariantId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditCalculationModal($Identifier, $VariantId, $CalculationId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showEditCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'VariantId'     => $VariantId,
            'CalculationId' => $CalculationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditCalculation($Identifier, $VariantId, $CalculationId)
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveEditCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'VariantId'     => $VariantId,
            'CalculationId' => $CalculationId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteCalculationModal($Identifier = '', $CalculationId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showDeleteCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'CalculationId' => $CalculationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteCalculation($Identifier = '', $CalculationId = '')
    {

        $Receiver = ApiItem::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'deleteCalculation'
        ));
        $Emitter->setPostPayload(array(
            'Identifier'    => $Identifier,
            'CalculationId' => $CalculationId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return Form
     */
    public function formCalculation($Identifier, $VariantId, $CalculationId = '')
    {

        // choose between Add and Edit
        $SaveButton = new Primary('Speichern', ApiItem::getEndpoint(), new Save());
        if('' !== $CalculationId){
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveEditCalculation($Identifier, $VariantId,
                $CalculationId));
        } else {
            $SaveButton->ajaxPipelineOnClick(self::pipelineSaveAddCalculation($Identifier, $VariantId));
        }

        $VariantName = 'Name der Variante nicht gefunden!';
        if(($tblItemVariant = Item::useService()->getItemVariantById($VariantId))){
            $VariantName = $tblItemVariant->getName();
            if(($Item = $tblItemVariant->getTblItem())){
                $VariantName = $Item->getName().' - '.$VariantName;
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(new Title($VariantName))
                ),
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Calculation[Value]', '0,00', 'Preis'))->setRequired()
                        , 4),
                    new FormColumn(
                        (new DatePicker('Calculation[DateFrom]', 'z.B.(01.01.2019)', 'Gültig ab',
                            new Clock()))->setRequired()
                        , 4),
                    new FormColumn(
                        new DatePicker('Calculation[DateTo]', 'z.B.(01.01.2020)', 'Gültig bis', new Clock())
                        , 4),
                    new FormColumn(
                        $SaveButton
                    )
                ))
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $VariantId
     * @param string $CalculationId
     * @param array  $Calculation
     *
     * @return false|string|Form
     */
    private function checkInputCalculation($Identifier, $VariantId, $CalculationId, $Calculation = array())
    {
        $Error = false;
        $form = $this->formCalculation($Identifier, $VariantId, $CalculationId);

        $Warning = '';
        if (!($tblItemVariant = Item::useService()->getItemVariantById($VariantId))){
            $Warning = new Danger('Beitrags-Variante ist nicht mehr vorhanden!');
            $Error = true;
        } else {
            if (isset($Calculation['Value']) && empty($Calculation['Value']) && $Calculation['Value'] !== '0'){
                $form->setError('Calculation[Value]', 'Bitte geben Sie einen Preis an');
                $Error = true;
            } elseif (isset($Calculation['Value']) && $Calculation['Value'] < 0) {
                $form->setError('Calculation[Value]', 'Bitte geben Sie einen Preis im positiven Bereich an');
                $Error = true;
            }
            if (isset($Calculation['DateFrom']) && empty($Calculation['DateFrom'])){
                $form->setError('Calculation[DateFrom]', 'Bitte geben Sie einen Beginn der Gültigkeit an');
                $Error = true;
            } else {
                if (($tblItemVariantList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                    $FromDate = new \DateTime($Calculation['DateFrom']);
                    if (isset($Calculation['DateTo']) && !empty($Calculation['DateTo'])){
                        $ToDate = new \DateTime($Calculation['DateTo']);
                    } else {
                        $ToDate = false;
                    }
                    foreach ($tblItemVariantList as $tblItemVariantCompare) {
                        // Alle von / Bis Datumsvergleiche
                        if ($tblItemVariantCompare->getDateTo()){
                            // Datumsangaben liegen in anderen Zeiträumen
                            if ($FromDate >= $tblItemVariantCompare->getDateFrom(true)
                                && $FromDate <= $tblItemVariantCompare->getDateTo(true)
                                || $ToDate
                                && $ToDate >= $tblItemVariantCompare->getDateFrom(true)
                                && $ToDate <= $tblItemVariantCompare->getDateTo(true)
                                || $ToDate
                                && $FromDate <= $tblItemVariantCompare->getDateFrom(true)
                                && $ToDate >= $tblItemVariantCompare->getDateTo(true)){
                                if ($tblItemVariantCompare->getId() != $CalculationId){
                                    $form->setError('Calculation[DateFrom]',
                                        'Datum liegt im Gültigkeitsbereich eines anderer Preises ('
                                        .$tblItemVariantCompare->getDateFrom().' - '.$tblItemVariantCompare->getDateTo().')');
                                    $Error = true;
                                    break;
                                }
                            }
                            // Datumsangaben überlagern sich mit anderen Zeiträumen
                            if ($ToDate
                                && $FromDate >= $tblItemVariantCompare->getDateFrom(true)
                                && $ToDate <= $tblItemVariantCompare->getDateTo(true)
                            ){
                                if ($tblItemVariantCompare->getId() != $CalculationId){
                                    $form->setError('Calculation[DateFrom]',
                                        'Datum liegt im Gültigkeitsbereich eines anderer Preises ('
                                        .$tblItemVariantCompare->getDateFrom().' - '.$tblItemVariantCompare->getDateTo().')');
                                    $Error = true;
                                    break;
                                }
                            }
                        } else {
                            // Update nur, wenn es keine Bearbeitung ist & das Datum in der Zukunft liegt.
                            if ($tblItemVariantCompare->getDateFrom(true) < $FromDate){
                                // Es gibt keine "Bis" Angabe
                                // Update nur durchführen, wenn Eingabe funktioniert
                                if (!$Error
                                    && !$tblItemVariantCompare->getDateFrom()
                                    || $tblItemVariantCompare->getDateFrom(true) <= $FromDate){
                                    // alte Calculation updaten ('DateFrom' minus 1 Tag)
                                    // Objekt muss geklont werden, da es sonnst im Vergleich nicht funktioniert
                                    $TemoFromDate = clone $FromDate;
                                    $DateTime = (date_sub($TemoFromDate
                                        , date_interval_create_from_date_string('1 days')));
                                    $DateTime = $DateTime->format('d.m.Y');
                                    // Update der fehlenden "Bis" Angabe
                                    Item::useService()->changeItemCalculation($tblItemVariantCompare,
                                        $tblItemVariantCompare->getValue(),
                                        $tblItemVariantCompare->getDateFrom(), $DateTime);
                                }
                            }
                            if ($tblItemVariantCompare->getDateFrom(true) > $FromDate
                                && !$ToDate
                                && $tblItemVariantCompare->getId() != $CalculationId) {
                                $form->setError('Calculation[DateTo]',
                                    'Bitte geben Sie für das Datum ein "Gültig bis" Zeitraum an.');
                                $Error = true;
                                break;
                            }
                            if ($tblItemVariantCompare->getId() != $CalculationId
                                && $tblItemVariantCompare->getDateFrom(true) == $FromDate) {
                                $form->setError('Calculation[DateFrom]',
                                    'Bitte geben Sie für das Datum ein Freies Datum "Gültig ab" an. (
                                    Preisangabe zu diesem Datum schon vorhanden)');
                                $Error = true;
                                break;
                            }

                            if ($ToDate
                                && $ToDate >= $tblItemVariantCompare->getDateFrom(true)
                                && $tblItemVariantCompare->getId() != $CalculationId) {
                                $form->setError('Calculation[DateTo]',
                                    'Bitte geben Sie für das Datum ein Freies Datum "Gültig bis" an. (
                                    Preisangabe zu diesem Datum schon vorhanden '
                                    .$tblItemVariantCompare->getDateFrom().
                                    ($tblItemVariantCompare->getDateTo() ? ' - '.$tblItemVariantCompare->getDateTo() : '').')');
                                $Error = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if($Error){
            if($Warning){
                return $Warning.new Well($form);
            }
            return new Well($form);
        }

        return $Error;
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     *
     * @return string
     */
    public function showAddCalculation($Identifier, $VariantId)
    {

        return new Well(self::formCalculation($Identifier, $VariantId));
    }

    /**
     * @param       $Identifier
     * @param       $VariantId
     * @param array $Calculation
     *
     * @return string
     */
    public function saveAddCalculation($Identifier, $VariantId, $Calculation = array())
    {

        // Handle error's
        if($form = $this->checkInputCalculation($Identifier, $VariantId, '', $Calculation)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $Calculation['Value'];
            $Global->POST['Calculation']['DateFrom'] = $Calculation['DateFrom'];
            $Global->POST['Calculation']['DateTo'] = $Calculation['DateTo'];
            $Global->savePost();
            return $form;
        }

        $tblCalculation = false;
        if(($tblItemVariant = Item::useService()->getItemVariantById($VariantId))){
            $tblCalculation = Item::useService()->createItemCalculation($tblItemVariant, $Calculation['Value'],
                $Calculation['DateFrom'], $Calculation['DateTo']);
        }

        return ($tblCalculation
            ? new Success('Preis erfolgreich angelegt').ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Preis konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     *
     * @return string
     */
    public function showEditCalculation($Identifier, $VariantId, $CalculationId)
    {

        if('' !== $CalculationId && ($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $tblItemCalculation->getValue(true);
            $Global->POST['Calculation']['DateFrom'] = $tblItemCalculation->getDateFrom();
            $Global->POST['Calculation']['DateTo'] = $tblItemCalculation->getDateTo();
            $Global->savePost();
        }

        return new Well(self::formCalculation($Identifier, $VariantId, $CalculationId));
    }

    /**
     * @param string     $Identifier
     * @param int|string $VariantId
     * @param int|string $CalculationId
     * @param array      $Calculation
     *
     * @return string
     */
    public function saveEditCalculation($Identifier, $VariantId, $CalculationId, $Calculation = array())
    {

        // Handle error's
        if($form = $this->checkInputCalculation($Identifier, $VariantId, $CalculationId, $Calculation)){
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Calculation']['Value'] = $Calculation['Value'];
            $Global->POST['Calculation']['DateFrom'] = $Calculation['DateFrom'];
            $Global->POST['Calculation']['DateTo'] = $Calculation['DateTo'];
            $Global->savePost();
            return $form;
        }

        $Success = false;
        if(($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            if((Item::useService()->changeItemCalculation($tblItemCalculation, $Calculation['Value']
                , $Calculation['DateFrom'], $Calculation['DateTo']))){
                $Success = true;
            }
        }

        return ($Success
            ? new Success('Preis erfolgreich angelegt').ApiItem::pipelineCloseModal($Identifier)
            : new Danger('Preis konnte nicht gengelegt werden'));
    }

    /**
     * @param string $Identifier
     * @param string $CalculationId
     *
     * @return string
     */
    public function showDeleteCalculation($Identifier = '', $CalculationId = '')
    {

        $tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId);
        if($tblItemCalculation){
            $VariantName = '';
            $ItemName = '';
            if(($tblItemVariant = $tblItemCalculation->getTblItemVariant())){
                $VariantName = $tblItemVariant->getName();
                if(($tblItem = $tblItemVariant->getTblItem())){
                    $ItemName = new Bold($tblItem->getName());
                }
            }
            $Content[] = 'Preis: '.$tblItemCalculation->getPriceString();
            $Content[] = 'Zeitraum: '.$tblItemCalculation->getDateFrom().' - '.$tblItemCalculation->getDateTo();

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Preis von Beitrags-Variante '.new Bold($VariantName).' der Beitragsart '.$ItemName.' wirklich entfernen?'
                                , new Listing($Content), Panel::PANEL_TYPE_DANGER)
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', ApiItem::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteCalculation($Identifier, $CalculationId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Preis wurde nicht gefunden');
        }
    }

    /**
     * @param string     $Identifier
     * @param int|string $CalculationId
     *
     * @return string
     */
    public function deleteCalculation($Identifier = '', $CalculationId = '')
    {

        if(($tblItemCalculation = Item::useService()->getItemCalculationById($CalculationId))){
            Item::useService()->removeItemCalculation($tblItemCalculation);
            return new Success('Preis wurde erfolgreich entfernt').ApiItem::pipelineCloseModal($Identifier);
        }
        return new Danger('Preis konnte nicht entfernt werden');
    }
}