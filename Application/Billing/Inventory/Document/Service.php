<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.03.2019
 * Time: 14:55
 */

namespace SPHERE\Application\Billing\Inventory\Document;


use SPHERE\Application\Billing\Inventory\Document\Service\Data;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocumentInformation;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocumentItem;
use SPHERE\Application\Billing\Inventory\Document\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Billing\Inventory\Document
 */
class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($Id)
    {
        return (new Data($this->getBinding()))->getDocumentById($Id);
    }

    /**
     * @param      $Name
     * @param bool $IsWarning
     *
     * @return false|TblDocument
     */
    public function getDocumentByName($Name, $IsWarning = false)
    {
        return (new Data($this->getBinding()))->getDocumentByName($Name, $IsWarning);
    }

    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {
        return (new Data($this->getBinding()))->getDocumentAll();
    }

    /**
     * @param TblDocument $tblDocument
     *
     * @return false|TblDocumentItem[]
     */
    public function getDocumentItemAllByDocument(TblDocument $tblDocument)
    {
        return (new Data($this->getBinding()))->getDocumentItemAllByDocument($tblDocument);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDocument[]
     */
    public function getDocumentAllByItem(TblItem $tblItem)
    {

        $list = array();
        if (($tblDocumentItemList = (new Data($this->getBinding()))->getDocumentItemAllByItem($tblItem))){
            foreach ($tblDocumentItemList as $tblDocumentItem) {
                if (($tblDocument = $tblDocumentItem->getTblDocument())) {
                    $list[] = $tblDocument;
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param $Data
     * @param TblDocument|null $tblDocument
     *
     * @return bool|Form
     */
    public function checkFormDocument(
        $Data,
        TblDocument $tblDocument = null
    ) {

        $error = false;
        $form = Document::useFrontend()->formDocument($tblDocument ? $tblDocument->getId() : null);
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name für die Bescheinigung an');
            $error = true;
        } else {
            $form->setSuccess('Data[Name]');
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     *
     * @return bool
     */
    public function createDocument($Data)
    {
        if (($tblDocument = (new Data($this->getBinding()))->createDocument($Data['Name'], $Data['Description']))) {
            if (isset($Data['Items'])) {
                foreach ($Data['Items'] as $itemId => $value) {
                    if (($tblItem = Item::useService()->getItemById($itemId))) {
                        (new Data($this->getBinding()))->addDocumentItem($tblDocument, $tblItem);
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblDocument $tblDocument
     * @param $Data
     *
     * @return bool
     */
    public function updateDocument(TblDocument $tblDocument, $Data)
    {
        if ((new Data($this->getBinding()))->updateDocument($tblDocument, $Data['Name'], $Data['Description'])) {
            if (($tblDocumentItemList = $this->getDocumentItemAllByDocument($tblDocument))) {
                foreach ($tblDocumentItemList as $tblDocumentItem) {
                    if (($tblItemTemp = $tblDocumentItem->getServiceTblItem())
                        && !isset($Data['Items'][$tblItemTemp->getId()])
                    ) {
                        (new Data($this->getBinding()))->removeDocumentItem($tblDocumentItem);
                    }
                }
            }

            if (isset($Data['Items'])) {
                foreach ($Data['Items'] as $itemId => $value) {
                    if (($tblItem = Item::useService()->getItemById($itemId))) {
                        (new Data($this->getBinding()))->addDocumentItem($tblDocument, $tblItem);
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblDocument $tblDocument
     *
     * @return bool
     */
    public function removeDocument(TblDocument $tblDocument)
    {
        return (new Data($this->getBinding()))->removeDocument($tblDocument);
    }

    /**
     * @param TblDocument $tblDocument
     * @param $Field
     *
     * @return false|TblDocumentInformation
     */
    public function getDocumentInformationBy(TblDocument $tblDocument, $Field)
    {
        return (new Data($this->getBinding()))->getDocumentInformationBy($tblDocument, $Field);
    }
    /**
     * @param TblDocument $tblDocument
     *
     * @return false|TblDocumentInformation[]
     */
    public function getDocumentInformationAllByDocument(TblDocument $tblDocument)
    {
        return (new Data($this->getBinding()))->getDocumentInformationAllByDocument($tblDocument);
    }

    /**
     * @param IFormInterface $Form
     * @param TblDocument $tblDocument
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public function updateDocumentInformation(
        IFormInterface $Form,
        TblDocument $tblDocument,
        $Data = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        foreach ($Data as $Field => $Value) {
            if (($tblDocumentInformation = $this->getDocumentInformationBy($tblDocument, $Field))) {
                (new Data($this->getBinding()))->updateDocumentInformation($tblDocumentInformation, $Field, $Value);
            } else {
                (new Data($this->getBinding()))->createDocumentInformation($tblDocument, $Field, $Value);
            }
        }

        return new Success('Der Bescheinigungsinhalt wurde gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Billing/Inventory/Document', Redirect::TIMEOUT_SUCCESS);
    }
}