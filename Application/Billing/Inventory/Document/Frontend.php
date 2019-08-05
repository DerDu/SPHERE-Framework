<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.03.2019
 * Time: 14:46
 */

namespace SPHERE\Application\Billing\Inventory\Document;

use SPHERE\Application\Api\Billing\Inventory\ApiDocument;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Billing\Inventory\Document
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDocument()
    {
        $Stage = new Stage('Belegdruck', 'Übersicht');

        $Stage->addButton((new Primary('Beleg hinzufügen', ApiDocument::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiDocument::pipelineOpenCreateDocumentModal()));

        $Stage->setContent(
            ApiDocument::receiverModal()
            . new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiDocument::receiverBlock($this->loadDocumentOverviewContent(), 'DocumentOverviewContent')
                        )
                    )
                ), new Title(new ListingTable().' Übersicht'))
            )
        );

        return $Stage;
    }

    /**
     * @return string
     */
    public function loadDocumentOverviewContent()
    {
        if (($tblDocumentList = Document::useService()->getDocumentAll())) {
            $contentTable = array();
            foreach ($tblDocumentList as $tblDocument) {
                $items = array();
                if (($tblDocumentItemList = Document::useService()->getDocumentItemAllByDocument($tblDocument))) {
                    foreach ($tblDocumentItemList as $tblDocumentItem) {
                        if (($tblItem = $tblDocumentItem->getServiceTblItem())) {
                            $items[] = $tblItem->getName();
                        }
                    }
                }
                if($tblDocument->getIsWarning()){
                    $Option =(new Standard(
                            '', '',
                            new Edit())
                        )->setDisabled()
                        .(new Standard(
                            '',
                            '/Billing/Inventory/Document/EditInformation',
                            new ListingTable(),
                            array('DocumentId' => $tblDocument->getId()),
                            'Inhalt des Belegs bearbeiten'
                        ))
                        .(new Standard(
                            '', '',
                            new Remove())
                        )->setDisabled();
                    $items = array('Alle');
                } else {
                    $Option = (new Standard(
                            '',
                            ApiDocument::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiDocument::pipelineOpenEditDocumentModal($tblDocument->getId()))
                        . (new Standard(
                            '',
                            '/Billing/Inventory/Document/EditInformation',
                            new ListingTable(),
                            array('DocumentId' => $tblDocument->getId()),
                            'Inhalt des Belegs bearbeiten'
                        ))
                        . (new Standard(
                            '',
                            ApiDocument::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiDocument::pipelineOpenDeleteDocumentModal($tblDocument->getId()));
                }

                $contentTable[] = array(
                    'Name' => $tblDocument->getName(),
                    'Description' => $tblDocument->getDescription(),
                    'Items' => implode(', ', $items),
                    'Options' => $Option

                );
            }

            $content = new TableData(
                $contentTable,
                null,
                array(
                    'Name' => 'Name',
                    'Description' => 'Beschreibung',
                    'Items' => 'Beitragsarten',
                    'Options' => ''
                )
            );
        } else {
            $content = new Warning('Es wurden noch keine Belege angelegt.', new Ban());
        }

        return $content;
    }

    /**
     * @param null $DocumentId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formDocument($DocumentId = null, $setPost = false)
    {
        if ($DocumentId && ($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Name'] = $tblDocument->getName();
                $Global->POST['Data']['Description'] = $tblDocument->getDescription();
                if (($tblDocumentItemList = Document::useService()->getDocumentItemAllByDocument($tblDocument))) {
                    foreach ($tblDocumentItemList as $tblDocumentItem) {
                        if (($tblItem = $tblDocumentItem->getServiceTblItem())) {
                            $Global->POST['Data']['Items'][$tblItem->getId()] = 1;
                        }
                    }
                }
                $Global->savePost();
            }
        }

        if ($DocumentId) {
            $saveButton = (new Primary('Speichern', ApiDocument::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDocument::pipelineEditDocumentSave($DocumentId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDocument::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDocument::pipelineCreateDocumentSave());
        }

        $contentItems = array();
        if (($tblItemAll = Item::useService()->getItemAll())) {
            foreach ($tblItemAll as $tblItem) {
                $contentItems[] = new CheckBox('Data[Items][' . $tblItem->getId() . ']', $tblItem->getName(), 1);
            }
        } else {
            $contentItems[] = new Warning('Es wurden keine Betragsarten gefunden', new Exclamation());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Beleg',
                            array(
                                (new TextField('Data[Name]', 'Name des Belegs', 'Name'))->setRequired(),
                                (new TextArea('Data[Description]', 'Beschreibung des Belegs', 'Beschreibung'))
                            ),
                            Panel::PANEL_TYPE_INFO
                        ), 12
                    ),
                    new FormColumn(
                        new Panel(
                            'verfügbar für die folgenden Beitragsarten',
                            $contentItems,
                            Panel::PANEL_TYPE_INFO
                        ), 12
                    ),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param null $DocumentId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditDocumentInformation($DocumentId = null, $Data = null)
    {
        $Stage = new Stage('Belegdruck', 'Inhalt bearbeiten');
        $Stage->addButton(new Standard(
            'Zurück', '/Billing/Inventory/Document', new ChevronLeft()
        ));

        if (!($tblDocument = Document::useService()->getDocumentById($DocumentId))) {
            return $Stage . new Danger('Der Beleg wurde nicht gefunden', new Exclamation())
                . new Redirect('/Billing/Inventory/Document', Redirect::TIMEOUT_ERROR);
        }

        if ($Data === null
            && ($tblDocumentInformationList = Document::useService()->getDocumentInformationAllByDocument($tblDocument))
        ) {
            $global = $this->getGlobal();
            foreach ($tblDocumentInformationList as $tblDocumentInformation) {
                $global->POST['Data'][$tblDocumentInformation->getField()] = $tblDocumentInformation->getValue();
            }
            $global->savePost();
        }

        $form = new Form(new FormGroup(array(
            new FormRow(new FormColumn(array(
                new TextField('Data[Subject]', 'z.B. Schulgeldbescheinigung für das Kalenderjahr [Jahr]', 'Betreff'),
                new TextArea('Data[Content]', 'Inhalt des Belegs', 'Inhalt', null, 17)
            )))
        )));
        $form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));

        $IsWarning = false;
        if($tblDocument->getIsWarning()){
            $IsWarning = true;
        }
        $freeFields = $this->getFreeFields($IsWarning);

        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Beleg',
                        array(
                            $tblDocument->getName(),
                            $tblDocument->getDescription()
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(new Well(Document::useService()->updateDocumentInformation($form, $tblDocument, $Data)), 9),
                new LayoutColumn(new Panel('Platzhalter', $freeFields, Panel::PANEL_TYPE_INFO), 3)
            ))
        ))));

        return $Stage;
    }

    /**
     * @param bool $IsWarning
     *
     * @return array
     */
    public function getFreeFields($IsWarning = false)
    {

        if($IsWarning){
            return array(
                '[Rechnungsnummer]',
                '[Abrechnungszeitraum]',
                '[Name der Abrechnung]',
                '[Fälligkeit]',
                '[Beitragsart]',
                '[Anzahl]',
                '[Einzelpreis]',
                '[Gesamtpreis]',
                '[Beitragszahler Anrede]',
                '[Beitragszahler Vorname]',
                '[Beitragszahler Nachname]',
                '[Beitragsverursacher Anrede]',
                '[Beitragsverursacher Vorname]',
                '[Beitragsverursacher Nachname]',
                '[Beitragsverursacher Geburtstag]',
                '[Datum]',
                '[Ort]',
                '[Trägername]',
                '[Trägerzusatz]',
                '[Trägeradresse]'
            );
        }
        return array(
            '[Jahr]',
            '[Zeitraum von]',
            '[Zeitraum bis]',
            '[Beitragsart]',
            '[Beitragssumme]',
            '[Beitragszahler Anrede]',
            '[Beitragszahler Vorname]',
            '[Beitragszahler Nachname]',
            '[Beitragsverursacher Anrede]',
            '[Beitragsverursacher Vorname]',
            '[Beitragsverursacher Nachname]',
            '[Beitragsverursacher Geburtstag]',
            '[Datum]',
            '[Ort]',
            '[Trägername]',
            '[Trägerzusatz]',
            '[Trägeradresse]'
        );
    }
}