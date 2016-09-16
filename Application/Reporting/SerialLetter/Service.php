<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Reporting\SerialLetter\Service\Data;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return (new Data($this->getBinding()))->getSerialLetterById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialPerson
     */
    public function getSerialPersonById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonById($Id);
    }

    /**
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return (new Data($this->getBinding()))->getSerialLetterAll();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterCount($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblSerialPerson[]
     */
    public function getSerialPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @return false|TblPerson[]
     */
    public function getPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {
        return ( new Data($this->getBinding()) )->getPersonBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllByPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        return (new Data($this->getBinding()))->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return (new Data($this->getBinding()))->getAddressPersonAllBySerialLetter($tblSerialLetter);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $SerialLetter
     *
     * @return IFormInterface|string
     */
    public function createSerialLetter(IFormInterface $Stage = null, $SerialLetter)
    {

        /**
         * Skip to Frontend
         */
        if (null === $SerialLetter) {
            return $Stage;
        }

        $Error = false;
        if (isset($SerialLetter['Name']) && empty($SerialLetter['Name'])) {
            $Stage->setError('SerialLetter[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createSerialLetter(
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Adressliste für Serienbriefe ist erfasst worden')
            . new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }


    /**
     * @param IFormInterface  $Form
     * @param TblSerialLetter $tblSerialLetter
     * @param                 $Check
     *
     * @return IFormInterface|string
     */
    public function setPersonAddressSelection(
        IFormInterface $Form,
        TblSerialLetter $tblSerialLetter,
        $Check
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Check) {
            return $Form;
        }

        if (!empty($Check)) {
            foreach ($Check as $personId => $list) {
                $tblPerson = Person::useService()->getPersonById($personId);
                if ($tblPerson) {
                    // alle Einträge zum Serienbrief dieser Person löschen
                    ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                    if (is_array($list) && !empty($list)) {
                        foreach ($list as $key => $item) {
                            if (isset($item['Address'])) {
                                $tblToPerson = Address::useService()->getAddressToPersonById($key);
                                if ($tblToPerson && $tblToPerson->getServiceTblPerson()) {
                                    if (isset($item['Salutation'])) {
                                        if ($item['Salutation'] == TblAddressPerson::SALUTATION_FAMILY) {
                                            $tblSalutation = new TblSalutation('Familie');
                                            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
                                        } else {
                                            $tblSalutation = Person::useService()->getSalutationById($item['Salutation']);
                                        }

                                        $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                            $tblToPerson->getServiceTblPerson(), $tblToPerson,
                                            $tblSalutation ? $tblSalutation : null);
                                    }
                                }
                            }
                        }
                    }
                    return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    .new Redirect('/Reporting/SerialLetter/Address/Edit', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblSerialLetter->getId(), 'PersonId' => $tblPerson->getId()));
                }
            }
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS,
            array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     * @param TblPerson $tblPersonToAddress
     * @param TblToPerson $tblToPerson
     * @param TblSalutation|null $tblSalutation
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson,
        TblSalutation $tblSalutation = null
    ) {

        return (new Data($this->getBinding()))->createAddressPerson($tblSerialLetter, $tblPerson, $tblPersonToAddress,
            $tblToPerson, $tblSalutation);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     */
    public function createSerialLetterExcel(TblSerialLetter $tblSerialLetter)
    {

        $tblAddressPersonAllBySerialLetter = $this->getAddressPersonAllBySerialLetter($tblSerialLetter);
        if ($tblAddressPersonAllBySerialLetter) {

            $row = 0;
            $column = 0;
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, $row), "Anrede");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Adresse 1");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Ort");
            $export->setValue($export->getCell($column++, $row), "Person_Vorname");
            $export->setValue($export->getCell($column, $row), "Person_Nachname");

            $row = 1;
            foreach ($tblAddressPersonAllBySerialLetter as $tblAddressPerson) {
                if ($tblAddressPerson->getServiceTblPerson()
                    && $tblAddressPerson->getServiceTblPersonToAddress()
                    && $tblAddressPerson->getServiceTblToPerson()
                ) {
                    $column = 0;
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getSalutation() : '');
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPersonToAddress()->getFirstName());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPersonToAddress()->getLastName());
                    $tblAddress = $tblAddressPerson->getServiceTblToPerson()->getTblAddress();
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber());
                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getCode());
                    $export->setValue($export->getCell($column++, $row), $tblAddress->getTblCity()->getDisplayName());
                    $export->setValue($export->getCell($column++, $row),
                        $tblAddressPerson->getServiceTblPerson()->getFirstName());
                    $export->setValue($export->getCell($column, $row),
                        $tblAddressPerson->getServiceTblPerson()->getLastName());
                    $row++;
                }
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->addSerialPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function removeSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->removeSerialPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialPerson $tblSerialPerson
     *
     * @return bool
     */
    public function destroySerialPerson(TblSerialPerson $tblSerialPerson)
    {

        return ( new Data($this->getBinding()) )->destroySerialPerson($tblSerialPerson);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblSerialLetter $tblSerialLetter
     * @param $SerialLetter
     *
     * @return IFormInterface|string
     */
    public function updateSerialLetter(
        IFormInterface $Stage = null,
        TblSerialLetter $tblSerialLetter,
        $SerialLetter = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $SerialLetter) {
            return $Stage;
        }

        $Error = false;
        if (isset($SerialLetter['Name']) && empty($SerialLetter['Name'])) {
            $Stage->setError('SerialLetter[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateSerialLetter(
                $tblSerialLetter,
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Adressliste für Serienbriefe ist geändert worden')
            . new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Destroy Address
                    SerialLetter::useService()->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                }
                // Destroy SerialPerson
                SerialLetter::useService()->destroySerialPerson($tblSerialPerson);
            }
        }
        // Destroy SerialLetter
        return ( new Data($this->getBinding()) )->destroySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
    }
}