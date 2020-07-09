<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Data;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialCompany;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Setup;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

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
     * @param int $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterById($Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblSerialLetter
     */
    public function getSerialLetterByName($Name)
    {

        return ( new Data($this->getBinding()) )->getSerialLetterByName($Name);
    }

    /**
     * @param int $Id
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryById($Id)
    {

        return ( new Data($this->getBinding()) )->getFilterCategoryById($Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryByName($Name)
    {

        return ( new Data($this->getBinding()) )->getFilterCategoryByName($Name);
    }

    /**
     * @return false|TblFilterCategory[]
     */
    public function getFilterCategoryAll()
    {

        return ( new Data($this->getBinding()) )->getFilterCategoryAll();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblFilterField[]
     */
    public function getFilterFieldAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getFilterFieldAllBySerialLetter($tblSerialLetter);
    }

//    /**
//     * @param TblSerialLetter $tblSerialLetter
//     *
//     * @return bool|TblFilterField[]
//     */
//    public function getFilterFieldActiveAllBySerialLetter(TblSerialLetter $tblSerialLetter)
//    {
//
//        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
//        if ($tblFilterCategory) {
//            return ( new Data($this->getBinding()) )->getFilterFieldActiveAllBySerialLetter($tblSerialLetter, $tblFilterCategory);
//        }
//        return false;
//    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialPerson
     */
    public function getSerialPersonById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialCompany
     */
    public function getSerialCompanyById($Id)
    {

        return ( new Data($this->getBinding()) )->getSerialCompanyById($Id);
    }

    /**
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return ( new Data($this->getBinding()) )->getSerialLetterAll();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        $result = 0;
        $tblSerialLetterPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);

        if ($tblSerialLetterPersonList) {
            foreach ($tblSerialLetterPersonList as $tblPerson) {
                $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
                if ($tblAddressPersonList) {
                    $Address = array();
                    foreach ($tblAddressPersonList as $tblAddressPerson) {
                        $tblToPerson = $tblAddressPerson->getServiceTblToPerson();
                        if ($tblToPerson) {
                            $tblAddress = $tblToPerson->getTblAddress();
                            if ($tblAddress) {
                                if (!in_array($tblAddress->getId(), $Address)) {
                                    $result++;
                                }
                                $Address[] = $tblAddress->getId();
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return int
     */
    public function getSerialLetterCompanyCount(TblSerialLetter $tblSerialLetter)
    {

        $result = 0;
        if(($tblSerialCompanyList = SerialLetter::useService()->getSerialCompanyBySerialLetter($tblSerialLetter))){
            foreach($tblSerialCompanyList as $tblSerialCompany){
                if(!$tblSerialCompany->getisIgnore()){
                    $result++;
                }
            }
        }

        return $result;
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
     * @param null|bool       $isIgnore
     *
     * @return false|TblSerialCompany[]
     */
    public function getSerialCompanyBySerialLetter(TblSerialLetter $tblSerialLetter, $isIgnore = null)
    {

        return ( new Data($this->getBinding()) )->getSerialCompanyBySerialLetter($tblSerialLetter, $isIgnore);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblCompany      $tblCompany
     * @param bool|null       $isIgnore (true or false if necessary)
     *
     * @return TblSerialCompany[]|bool
     */
    public function getSerialCompanyByCompany(TblSerialLetter $tblSerialLetter, TblCompany $tblCompany, $isIgnore = null)
    {

        return ( new Data($this->getBinding()) )->getSerialCompanyByCompany($tblSerialLetter, $tblCompany, $isIgnore);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblCompany      $tblCompany
     * @param TblPerson|null  $tblPerson
     *
     * @return false|TblSerialCompany
     */
    public function getSerialCompanyBySerialLetterAndCompanyAndPerson(TblSerialLetter $tblSerialLetter, TblCompany $tblCompany
        , TblPerson $tblPerson = null)
    {

        return ( new Data($this->getBinding()) )->getSerialCompanyBySerialLetterAndCompanyAndPerson($tblSerialLetter, $tblCompany, $tblPerson);
    }

    /** @deprecated
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return false|TblSerialPerson
     */
    public function getSerialPersonBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getSerialPersonBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {
        return ( new Data($this->getBinding()) )->getPersonBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool|Service\Entity\TblAddressPerson[]
     */
    public function getAddressPersonAllByPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        $FirstGender = 'F';
        if(($Setting = Consumer::useService()->getSetting('Reporting', 'SerialLetter', 'GenderSort', 'FirstFemale'))){
            if(!$Setting->getValue()){
                $FirstGender = 'M';
            }
        }

        $tblAddressPersonList = ( new Data($this->getBinding()) )->getAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);

        if ($tblAddressPersonList && $FirstGender != null) {
            $AddressPersonList = array();
                // Sortierung erste Person "Geschlecht"
            foreach ($tblAddressPersonList as $AddressPerson) {
                $tblPerson = $AddressPerson->getServiceTblPersonToAddress();
                if ($tblPerson) {
                    if ($FirstGender === 'F' && $tblPerson->getSalutation() === 'Frau') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                    if ($FirstGender === 'M' && $tblPerson->getSalutation() === 'Herr') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                }
            }
                // Sortierung zweite Person "Geschlecht"
            foreach ($tblAddressPersonList as $AddressPerson) {
                $tblPerson = $AddressPerson->getServiceTblPersonToAddress();
                if ($tblPerson) {
                    if ($FirstGender === 'M' && $tblPerson->getSalutation() === 'Frau') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                    if ($FirstGender === 'F' && $tblPerson->getSalutation() === 'Herr') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                }
            }
                // Sortierung Person ohne Anrede (Herr/Frau) am Schluss
            foreach ($tblAddressPersonList as $AddressPerson) {
                $tblPerson = $AddressPerson->getServiceTblPersonToAddress();
                if ($tblPerson) {
                    if ($tblPerson->getSalutation() !== 'Herr'
                        && $tblPerson->getSalutation() !== 'Frau'
                    ) {
                        $AddressPersonList[] = $AddressPerson;
                    }
                }
            }
        } else {
            $AddressPersonList = $tblAddressPersonList;
        }
        return ( !empty($AddressPersonList) ? $AddressPersonList : false );
    }

    /** @deprecated
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->getAddressPersonAllBySerialLetter($tblSerialLetter);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param array               $SerialLetter
     * @param null                $FilterGroup
     * @param null                $FilterPerson
     * @param null                $FilterStudent
     * @param null                $FilterYear
     * @param null                $FilterProspect
     * @param null                $FilterCompany
     * @param null                $FilterRelationship
     * @param null                $FilterCategory
     *
     * @return IFormInterface|string
     */
    public function createSerialLetter(
        IFormInterface $Stage = null,
        $SerialLetter,
        $FilterGroup = null,
        $FilterPerson = null,
        $FilterStudent = null,
        $FilterYear = null,
        $FilterProspect = null,
        $FilterCompany = null,
        $FilterRelationship = null,
        $FilterCategory = null
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
        } else {
            if (SerialLetter::useService()->getSerialLetterByName($SerialLetter['Name'])) {
                $Stage->setError('SerialLetter[Name]', 'Der Name für den Serienbrief exisitert bereits. Bitte wählen Sie einen anderen.');
                $Error = true;
            }
        }
        if ($FilterCategory != null) {
            $tblFilterCategory = SerialLetter::useService()->getFilterCategoryById($FilterCategory);
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                if (isset($FilterGroup['TblGroup_Id'][0]) && $FilterGroup['TblGroup_Id'][0] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id][0]', 'Bitte geben Sie eine Gruppe an');
                    $Error = true;
                }
            }
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                if (isset($FilterGroup['TblGroup_Id'][0]) && $FilterGroup['TblGroup_Id'][0] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id][0]', 'Benutzen Sie bitte die Gruppe "Schüler" zur Filterung');
                    $Error = true;
                }
            }
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                if (isset($FilterGroup['TblGroup_Id'][0]) && $FilterGroup['TblGroup_Id'][0] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id][0]', 'Benutzen Sie bitte die Gruppe "Interessent" zur Filterung');
                    $Error = true;
                }
            }
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY) {
                if (isset($FilterGroup['TblGroup_Id'][0]) && $FilterGroup['TblGroup_Id'][0] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id][0]', 'Bitte geben Sie eine Gruppe an');
                    $Error = true;
                }
            }
        }


        if (!$Error) {
            $TabActive = 'STATIC';
            if ($FilterCategory === null) {
                ( new Data($this->getBinding()) )->createSerialLetter(
                    $SerialLetter['Name'],
                    $SerialLetter['Description']
                );
            } else {
                $tblFilterCategory = SerialLetter::useService()->getFilterCategoryById($FilterCategory);
                $tblSerialLetter = ( new Data($this->getBinding()) )->createSerialLetter(
                    $SerialLetter['Name'],
                    $SerialLetter['Description'],
                    $tblFilterCategory

                );

                if ($tblFilterCategory) {
                    // save Group Field
                    if (isset($FilterGroup) && !empty($FilterGroup)) {
                        foreach ($FilterGroup as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Person Field
                    if (isset($FilterPerson) && !empty($FilterPerson)) {
                        foreach ($FilterPerson as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Student Field
                    if (isset($FilterStudent) && !empty($FilterStudent)) {
                        foreach ($FilterStudent as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Year Field
                    if (isset($FilterYear) && !empty($FilterYear)) {
                        foreach ($FilterYear as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Prospect Field
                    if (isset($FilterProspect) && !empty($FilterProspect)) {
                        foreach ($FilterProspect as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Company Field
                    if (isset($FilterCompany) && !empty($FilterCompany)) {
                        foreach ($FilterCompany as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }
                    // save Prospect Field
                    if (isset($FilterRelationship) && !empty($FilterRelationship)) {
                        foreach ($FilterRelationship as $FieldName => $FilterList) {
                            foreach ($FilterList as $FilterNumber => $Value) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                    $FieldName, $Value, $FilterNumber);
                            }
                        }
                    }

                    if ($tblFilterCategory) {
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                            $Result = (new SerialLetterFilter())->getGroupFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'PERSONGROUP';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                            $Result = (new SerialLetterFilter())->getStudentFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'STUDENT';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                            $Result = (new SerialLetterFilter())->getProspectFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'PROSPECT';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                            $Result = (new SerialLetterFilter())->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
                            $tblCompanySearchList = (new SerialLetterFilter())->getCompanyListByResult($Result);
                            SerialLetter::useService()->updateDynamicSerialCompany($tblSerialLetter, $tblCompanySearchList);
                            $TabActive = 'COMPANY';
                        }
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe ist erfasst worden')
                .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS, array('TabActive' => $TabActive));
        }

        return $Stage;
    }


    /**
     * @param IFormInterface  $Form
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     * @param array           $Check
     * @param string          $Route
     *
     * @return IFormInterface|string
     */
    public function setPersonAddressSelection(
        IFormInterface $Form,
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        $Check,
        $Route = '/Reporting/SerialLetter/Address'
    ) {

        // Get Submit Info
        $Global = $this->getGlobal();

        /**
         * Skip to Frontend
         */
        if (null === $Check && !isset($Global->POST['SubmitCheck'])) {
            return $Form;
        }
        $isCompany = false;
        $FilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_COMPANY_GROUP);
        if ($FilterCategory && ( $tblFilterCategory = $tblSerialLetter->getFilterCategory() )) {
            if ($FilterCategory->getId() == $tblFilterCategory->getId()) {
                $isCompany = true;
            }
        }

        if (!empty($Check)) {
            foreach ($Check as $personId => $list) {
                if ($isCompany) {
                    // alle Einträge zum Serienbrief dieser Person löschen
                    ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                    if (is_array($list) && !empty($list)) {
                        foreach ($list as $key => $item) {
                            if (isset($item['Address'])) {
                                $tblToCompany = Address::useService()->getAddressToCompanyById($key);
                                if ($tblToCompany) {
                                    $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                        $tblPerson, null, $tblToCompany);
                                }
                            }
                        }
                    }
                } else {
                    // alle Einträge zum Serienbrief dieser Person löschen
                    ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                    if (is_array($list) && !empty($list)) {
                        foreach ($list as $key => $item) {
                            if (isset($item['Address'])) {
                                $tblToPerson = Address::useService()->getAddressToPersonById($key);
                                if ($tblToPerson && $tblToPerson->getServiceTblPerson()) {
                                    if ($tblPersonToPerson = $tblToPerson->getServiceTblPerson()) {
                                        $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                            $tblToPerson->getServiceTblPerson(), $tblToPerson, null);
                                    } else {
                                        $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                            $tblToPerson->getServiceTblPerson(), $tblToPerson);
                                    }
                                }
                            }
                        }
                    }
                }
                return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblSerialLetter->getId(), 'PersonId' => $tblPerson->getId()));
            }
        } else {
            ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
        }

        return new Success('Erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            .new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Warning|string
     */
    public function createAddressPersonSelf(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            $CreateArray = array();
            /** @var TblSerialPerson $tblSerialPerson */
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        $tblToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
                        if ($tblToPersonList) {
                            $tblType = Address::useService()->getTypeById(1);
                            $tblToPersonChoose = null;
                            // Ziehen der ersten Hauptadresse (die aktuellste)
                            foreach ($tblToPersonList as $tblToPerson) {
                                if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                    $tblToPersonChoose = $tblToPerson;
                                }
                            }
//                            // Ziehen irgendeiner Adresse
//                            if ($tblToPersonChoose === null) {
//                                foreach ($tblToPersonList as $tblToPerson) {
//                                    $tblToPersonChoose = $tblToPerson;
//                                }
//                            }
                            $tblSalutation = $tblPerson->getTblSalutation();
                            if (!$tblSalutation) {
                                $tblSalutation = null;
                            }
                            $CreateArray[$tblSerialLetter->getId()][$tblPerson->getId()][$tblPerson->getId()] = $tblToPersonChoose;
                        }
                    }
                }
            }
            if (!empty($CreateArray)) {
                ( new Data($this->getBinding()) )->createAddressPersonList($CreateArray);
            }
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
            .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Warning|string
     */
    public function createAddressPersonGuardian(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            $CreateArray = array();
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        $tblGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                        if ($tblGuardianList) {
                            $tblTypeRelationship = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                            $GuardianList = array();
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $tblGuardian */
                            foreach ($tblGuardianList as $tblGuardian) {
                                // Alle Sorgeberechtigte
                                if ($tblTypeRelationship->getId() === $tblGuardian->getTblType()->getId()) {
                                    if ($tblPerson->getId() !== $tblGuardian->getServiceTblPersonFrom()->getId()) {
                                        $GuardianList[] = $tblGuardian->getServiceTblPersonFrom();
                                    }
                                }
                            }
                            $Person = null;
                            $ToPersonChooseList = array();
                            $SalutationList = array();
                            /** @var TblPerson[] $GuardianList */
                            if (!empty($GuardianList)) {
                                // Alle Sorgeberechtigten
                                foreach ($GuardianList as $Parent) {
                                    $tblToPersonList = Address::useService()->getAddressAllByPerson($Parent);
                                    if ($tblToPersonList) {
                                        $tblType = Address::useService()->getTypeById(1);
                                        $tblToPersonChoose = null;
                                        // Ziehen der ersten Hauptadresse
                                        /** @var TblToPerson $tblToPerson */
                                        foreach ($tblToPersonList as $tblToPerson) {
                                            if ($tblToPerson->getTblType()->getId() === $tblType->getId() && $tblToPersonChoose === null) {
                                                $ToPersonChooseList[] = $tblToPerson;
                                                $tblSalutation = $Parent->getTblSalutation();
                                                $SalutationList[] = $tblSalutation;
                                                $Person[] = $Parent;
                                            }
                                        }
                                    }
                                }

                                /** @var TblToPerson[] $ToPersonChooseList */
                                if (!empty($ToPersonChooseList)) {

                                    $count = 0;
                                    foreach ($ToPersonChooseList as $ToPersonChoose) {

                                        $tblToPersonChoose = $ToPersonChoose;
                                        if (isset($SalutationList[$count])) {
                                            $PersonTo = $Person[$count];
                                        } else {
                                            $PersonTo = false;
                                        }

                                        $CreateArray[$tblSerialLetter->getId()][$tblPerson->getId()][$PersonTo->getId()] = $tblToPersonChoose;
//                                        SerialLetter::useService()->createAddressPerson(
//                                            $tblSerialLetter, $tblPerson, $PersonTo, $tblToPersonChoose, null, ( $tblSalutation ? $tblSalutation : null ));
                                        $count++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ( new Data($this->getBinding()) )->createAddressPersonList($CreateArray);
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
            .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return Warning|string
     */
    public function createAddressPersonCompany(TblSerialLetter $tblSerialLetter)
    {

        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        $tblType = Address::useService()->getTypeById(1);
        if ($tblSerialPersonList) {
            $CreateArray = array();
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Nur Personen die noch keine Adressen haben
                    if (!SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson)) {
                        $tblRelationshipCompanyList = Relationship::useService()->getCompanyRelationshipAllByPerson($tblPerson);
                        if ($tblRelationshipCompanyList) {
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToCompany $tblToCompany */
                            $count = 0;
                            $tblCompany = false;
                            // Existieren die Institutionen noch zu der Beziehung?
                            foreach ($tblRelationshipCompanyList as $tblRelationshipCompany) {
                                if ($tblRelationshipCompany->getServiceTblCompany()) {
                                    $tblCompany = $tblRelationshipCompany->getServiceTblCompany();
                                    $count++;
                                }
                            }
                            // Automatik nur mit einer Institution
                            if ($tblCompany && $count == 1) {
                                $tblToCompanyList = Address::useService()->getAddressAllByCompany($tblCompany);
                                if ($tblToCompanyList) {
                                    foreach ($tblToCompanyList as $tblToCompany) {
                                        if ($tblToCompany->getTblType()->getId() === $tblType->getId()) {
//                                            $tblSalutation = $tblPerson->getTblSalutation();
                                            $PersonTo = $tblPerson;
                                            $CreateArray[$tblSerialLetter->getId()][$tblPerson->getId()][$PersonTo->getId()] = $tblToCompany;
//                                            SerialLetter::useService()->createAddressPerson(
//                                                $tblSerialLetter, $tblPerson, $PersonTo, null, $tblToCompany, ( $tblSalutation ? $tblSalutation : null ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ( new Data($this->getBinding()) )->createAddressPersonList($CreateArray, true);
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche und eindeutige Adressenzuweisungen wurde vorgenommen')
            .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter    $tblSerialLetter
     * @param TblPerson          $tblPerson
     * @param TblPerson          $tblPersonToAddress
     * @param null|TblToPerson   $tblToPerson
     * @param null|TblToCompany  $tblToCompany
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson = null,
        TblToCompany $tblToCompany = null
    ) {

        return ( new Data($this->getBinding()) )->createAddressPerson($tblSerialLetter, $tblPerson, $tblPersonToAddress,
            $tblToPerson, $tblToCompany);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createSerialLetterExcel(TblSerialLetter $tblSerialLetter)
    {

        $tblPersonList = $this->getPersonAllBySerialLetter($tblSerialLetter);
        $ExportData = array();
        $AddressPersonCount = 1;
        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
        if ($tblPersonList) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                    $tblPerson);
                if ($tblAddressPersonAllByPerson) {
                    /** @var TblAddressPerson $tblAddressPerson */
                    $AddressList = array();
                    array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson)
                    use (&$AddressList, $tblPerson, &$AddressPersonCount, $tblFilterCategory) {
                        if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                            if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                    if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                        //Person SerialLetter
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                            $tblPerson->getSalutation();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['FirstName'] =
                                            $tblPerson->getFirstName();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['LastName'] =
                                            $tblPerson->getLastName();

                                        //Person Address
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonSalutation'][] =
                                            $PersonToAddress->getSalutation();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName'][] =
                                            $PersonToAddress->getFirstName();
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonLastName'][] =
                                            $PersonToAddress->getLastName();

                                        if (isset($AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName'])) {
                                            if ($AddressPersonCount < count($AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName'])) {
                                                $AddressPersonCount = count($AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName']);
                                            }
                                        }

                                        if (( $tblAddress = $tblAddressPerson->getServiceTblToPerson()->getTblAddress() )) {
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['StreetName'] =
                                                $tblAddress->getStreetName();
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['StreetNumber'] =
                                                $tblAddress->getStreetNumber();;
                                            if (( $tblCity = $tblAddress->getTblCity() )) {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['District'] =
                                                    $tblCity->getDistrict();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['Code'] =
                                                    $tblCity->getCode();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['City'] =
                                                    $tblCity->getName();
                                            }
                                        }
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Division'] =
                                            Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['SchoolCourse'] = '';
                                        if ($tblStudent) {
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $tblStudent->getIdentifierComplete();
                                            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                                            if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                                                if($tblStudentTransfer->getServiceTblCourse()){
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['SchoolCourse'] = $tblStudentTransfer->getServiceTblCourse()->getName();
                                                }
                                            }
                                        } else {
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = '';
                                        }

                                    }
                                }
                            }
                        }
                    });

                    if ($AddressList) {
                        foreach ($AddressList as $Address) {

                            // fill AddressLine
                            $firstAddressLine = '';
                            $secondAddressLine = '';
                            $AddressName = '';
                            $firstLetter = '';
                            $secondLetter = '';
                            $thirdLetter = '';
                            $fourLetter = '';
                            $isReady = true;

                            // only 1 or 2 Person with Salutation "Herr" or "Frau"
                            if (isset($Address['PersonSalutation']) && !empty($Address['PersonSalutation'])) {
                                foreach ($Address['PersonSalutation'] as $Key => $Salutation) {
                                    if ($Key > 2) {
                                        break;
                                    }
                                    if ($Key > 1) {
                                        if ($Salutation === 'Herr' || $Salutation === 'Frau') {
                                            $isReady = false;
                                        }
                                    } else {
                                        if ($Salutation !== 'Herr' && $Salutation !== 'Frau') {
                                            $isReady = false;
                                        }
                                    }
                                }
                            }
                            if ($isReady) {
                                if (isset($Address['PersonLastName']) && !empty($Address['PersonLastName'])) {
                                    if (isset($Address['PersonSalutation'])
                                        && count($Address['PersonLastName']) > 1
                                    ) {
                                        // Personen mit gleichem Nachnamen
                                        if (count(array_unique($Address['PersonLastName'])) === 1) {
                                            foreach ($Address['PersonLastName'] as $Key => $LastName) {
                                                if ($Key > 1) {
                                                    break;
                                                }
                                                if ($AddressName === '') {
                                                    $AddressName = $Address['PersonFirstName'][$Key];
                                                    if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                        $firstLetter = 'Sehr geehrter '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter = 'Lieber '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    } elseif ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                        $firstLetter = 'Sehr geehrte '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter = 'Liebe '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    }
                                                    $thirdLetter = 'Sehr geehrte Familie '.$LastName;
                                                    $fourLetter = 'Liebe Familie '.$LastName;
                                                } else {
                                                    $AddressName .= ' u. '.$Address['PersonFirstName'][$Key].' '.$LastName;
                                                    if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                        $firstLetter .= ', sehr geehrter '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter .= ', lieber '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    } elseif ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                        $firstLetter .= ', sehr geehrte '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter .= ', liebe '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    }
                                                }
                                            }
                                        } else { // Personen mit unterschiedlichem Nachnamen
                                            foreach ($Address['PersonLastName'] as $Key => $LastName) {
                                                if ($Key > 1) {
                                                    break;
                                                }

                                                if ($AddressName === '') {
                                                    $AddressName = $Address['PersonFirstName'][$Key].' '.$LastName;
                                                    if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                        $firstLetter = 'Sehr geehrter '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter = 'Lieber '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    } elseif ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                        $firstLetter = 'Sehr geehrte '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter = 'Liebe '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    }
                                                    $thirdLetter = 'Sehr geehrte Familie '.$LastName;
                                                    $fourLetter = 'Liebe Familie '.$LastName;
                                                } else {
                                                    $AddressName .= ' u. '.$Address['PersonFirstName'][$Key].' '.$LastName;
                                                    if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                        $firstLetter .= ', sehr geehrter '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter .= ', lieber '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    } elseif ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                        $firstLetter .= ', sehr geehrte '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                        $secondLetter .= ', liebe '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    }
                                                    $thirdLetter .= ' / '.$LastName;
                                                    $fourLetter .= ' / '.$LastName;
                                                }
                                            }
                                        }

                                        // Personenunabhängig
                                        foreach ($Address['PersonLastName'] as $Key => $LastName) {
                                            if ($firstAddressLine === '') {
                                                if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                    $firstAddressLine = $Address['PersonSalutation'][$Key].'n';
                                                }
                                                if ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                    $firstAddressLine = $Address['PersonSalutation'][$Key];
                                                }
                                            } else {
                                                // Herrn steht immer vorne (DIN 5008)
                                                if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                    $firstAddressLine = $Address['PersonSalutation'][$Key].'n und '. $firstAddressLine;
                                                }
                                                // Frau steht immer hinten (DIN 5008)
                                                if ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                    $firstAddressLine = $firstAddressLine.' und '.$Address['PersonSalutation'][$Key];
                                                }
                                            }
                                            if ($secondAddressLine === '') {
                                                if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                    $secondAddressLine = $Address['PersonSalutation'][$Key].'n';
                                                }
                                                if ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                    $secondAddressLine = $Address['PersonSalutation'][$Key];
                                                }
                                            } else {
                                                $secondAddressLine = 'Familie';
                                            }
                                        }
                                    } elseif (count($Address['PersonLastName']) === 1) {     // Einzelpersonen
                                        foreach ($Address['PersonLastName'] as $Key => $LastName) {
                                            if ($firstAddressLine === '') {
                                                if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                    $firstAddressLine = $Address['PersonSalutation'][$Key].'n';
                                                    $secondAddressLine = $Address['PersonSalutation'][$Key].'n';
                                                    $firstLetter = 'Sehr geehrter '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    $secondLetter = 'Lieber '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                }
                                                if ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                    $firstAddressLine = $Address['PersonSalutation'][$Key];
                                                    $secondAddressLine = $Address['PersonSalutation'][$Key];
                                                    $firstLetter = 'Sehr geehrte '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                    $secondLetter = 'Liebe '.$Address['PersonSalutation'][$Key].' '.$LastName;
                                                }
                                                $thirdLetter = 'Sehr geehrte Familie '.$LastName;
                                                $fourLetter = 'Liebe Familie '.$LastName;
                                                $AddressName = $Address['PersonFirstName'][$Key].' '.$LastName;
                                            }
                                        }
                                    }
                                }
                            }
                            $ExportData[] = array(
                                'firstAddressLine'  => $firstAddressLine,
                                'secondAddressLine' => $secondAddressLine,
                                'AddressName'       => $AddressName,
                                'firstLetter'       => $firstLetter,
                                'secondLetter'      => $secondLetter,
                                'thirdLetter'       => $thirdLetter,
                                'fourLetter'        => $fourLetter,
                                'SalutationList'    => ( isset($Address['PersonSalutation']) ? $Address['PersonSalutation'] : array() ),
                                'FirstNameList'     => ( isset($Address['PersonFirstName']) ? $Address['PersonFirstName'] : array() ),
                                'LastNameList'      => ( isset($Address['PersonLastName']) ? $Address['PersonLastName'] : array() ),
                                'District'          => ( isset($Address['District']) ? $Address['District'] : '' ),
                                'StreetName'        => ( isset($Address['StreetName']) ? $Address['StreetName'] : '' ),
                                'StreetNumber'      => ( isset($Address['StreetNumber']) ? $Address['StreetNumber'] : '' ),
                                'Code'              => ( isset($Address['Code']) ? $Address['Code'] : '' ),
                                'City'              => ( isset($Address['City']) ? $Address['City'] : '' ),
                                'Salutation'        => ( isset($Address['Salutation']) ? $Address['Salutation'] : '' ),
                                'FirstName'         => ( isset($Address['FirstName']) ? $Address['FirstName'] : '' ),
                                'LastName'          => ( isset($Address['LastName']) ? $Address['LastName'] : '' ),
                                'StudentNumber'     => ( isset($Address['StudentNumber']) ? $Address['StudentNumber'] : '' ),
                                'Division'          => ( isset($Address['Division']) ? $Address['Division'] : '' ),
                                'SchoolCourse'      => ( isset($Address['SchoolCourse']) ? $Address['SchoolCourse'] : '' ),
                            );
                        }
                    }
                }
            }
        }

        if (!empty($ExportData)) {

            $row = 0;
            $column = 0;
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell($column++, $row), "Adressanrede 1");
            $export->setValue($export->getCell($column++, $row), "Adressanrede 2 (Familie)");
            $export->setValue($export->getCell($column++, $row), "Adressname 1");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 1 (Sehr geehrter)");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 2 (Lieber)");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 3 (Sehr geehrte Familie)");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 4 (Liebe Familie)");
            for ($i = 0; $i < $AddressPersonCount; $i++) {
                $export->setValue($export->getCell($column++, $row), "Anrede ".( $i + 1 ));
                $export->setValue($export->getCell($column++, $row), "Vorname ".( $i + 1 ));
                $export->setValue($export->getCell($column++, $row), "Nachname ".( $i + 1 ));
            }
            $export->setValue($export->getCell($column++, $row), "Ortsteil");
            $export->setValue($export->getCell($column++, $row), "Straße");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Ort");
            $export->setValue($export->getCell($column++, $row), "PLZ/Ort");
            $export->setValue($export->getCell($column++, $row), "");
            $export->setValue($export->getCell($column++, $row), "Person_Vorname");
            $export->setValue($export->getCell($column++, $row), "Person_Nachname");
            $export->setValue($export->getCell($column++, $row), "Person_Schüler-Nr.");
            $export->setValue($export->getCell($column++, $row), "Person_Aktuelle Klasse(n)");
            $export->setValue($export->getCell($column, $row), "Bildungsgang");

            $row = 1;
            /** @var TblAddressPerson $tblAddressPerson */
            foreach ($ExportData as $Export) {

                $column = 0;
                $PersonLoop = 0;

                $export->setValue($export->getCell($column++, $row), $Export['firstAddressLine']);
                $export->setValue($export->getCell($column++, $row), $Export['secondAddressLine']);
                $export->setValue($export->getCell($column++, $row), $Export['AddressName']);
                $export->setValue($export->getCell($column++, $row), $Export['firstLetter']);
                $export->setValue($export->getCell($column++, $row), $Export['secondLetter']);
                $export->setValue($export->getCell($column++, $row), $Export['thirdLetter']);
                $export->setValue($export->getCell($column++, $row), $Export['fourLetter']);

                for ($j = 0; $j < $AddressPersonCount; $j++) {
                    $export->setValue($export->getCell($column++, $row),
                        ( isset($Export['SalutationList'][$PersonLoop]) ? $Export['SalutationList'][$PersonLoop] : '' ));
                    $export->setValue($export->getCell($column++, $row),
                        ( isset($Export['FirstNameList'][$PersonLoop]) ? $Export['FirstNameList'][$PersonLoop] : '' ));
                    $export->setValue($export->getCell($column++, $row),
                        ( isset($Export['LastNameList'][$PersonLoop]) ? $Export['LastNameList'][$PersonLoop] : '' ));
                    $PersonLoop++;
                }

                $export->setValue($export->getCell($column++, $row),
                    $Export['District']);
                $export->setValue($export->getCell($column++, $row),
                    $Export['StreetName'].' '.$Export['StreetNumber']);
                $export->setValue($export->getCell($column++, $row), $Export['Code']);
                $export->setValue($export->getCell($column++, $row), $Export['City']);
                $export->setValue($export->getCell($column++, $row), $Export['Code'].' '.$Export['City']);
                $export->setValue($export->getCell($column++, $row), '');
                $export->setValue($export->getCell($column++, $row), $Export['FirstName']);
                $export->setValue($export->getCell($column++, $row), $Export['LastName']);
                $export->setValue($export->getCell($column++, $row), $Export['StudentNumber']);
                $export->setValue($export->getCell($column++, $row), $Export['Division']);
                $export->setValue($export->getCell($column, $row), $Export['SchoolCourse']);

                $row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     * @throws \PHPExcel_Reader_Exception
     */
    public function createSerialLetterCompanyExcel(TblSerialLetter $tblSerialLetter)
    {

        $ExportData = array();
        $tblSerialCompanyList = $this->getSerialCompanyBySerialLetter($tblSerialLetter, false);

        // sort company by name
        $Company = array();
        foreach ($tblSerialCompanyList as $key => $row) {
            $Company[$key] = ($row->getServiceTblCompany() ? $row->getServiceTblCompany()->getName() : '');
        }
        array_multisort($Company, SORT_ASC, $tblSerialCompanyList);

        if ($tblSerialCompanyList) {
            array_walk($tblSerialCompanyList, function (TblSerialCompany $tblSerialCompany) use(&$ExportData) {

                // person defaults
                $Item['Salutation'] = '';
                $Item['Title'] = '';
                $Item['FirstName'] = '';
//                $Item['SecondName'] = '';
                $Item['LastName'] = '';

                if(($tblPerson = $tblSerialCompany->getServiceTblPerson())) {
                    $Item['Salutation'] = $tblPerson->getSalutation();
                    $Item['Title'] = $tblPerson->getTitle();
                    $Item['FirstName'] = str_replace('.', '', $tblPerson->getFirstName());
//                    $Item['SecondName'] = $tblPerson->getSecondName();
                    $Item['LastName'] = $tblPerson->getLastName();
                }

                // company values
                $Item['CompanyName'] = '';
                $Item['CompanyExtendedName'] = '';
                $Item['Description'] = '';
                $Item['Street'] = '';
                $Item['Code'] = '';
                $Item['City'] = '';
                $Item['CodeCity'] = '';
                $Item['District'] = '';
                if(($tblCompany = $tblSerialCompany->getServiceTblCompany())){
                    $Item['CompanyName'] = $tblCompany->getName();
                    $Item['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    $Item['CompanyDescription'] = $tblCompany->getDescription();
                    if(($tblAddress = Address::useService()->getAddressByCompany($tblCompany))){
                        $Item['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                        if(($tblCity = $tblAddress->getTblCity())){
                            $Item['Code'] = $tblCity->getCode();
                            $Item['City'] = $tblCity->getName();
                            $Item['CodeCity'] = $tblCity->getCode().' '.$tblCity->getName();
                            $Item['District'] = $tblCity->getDistrict();
                        }
                    }
                }

                // calculate extra columns
                // default extra columns
                $Item['AddressSalutation'] = '';
                $Item['AddressName'] = '';
                $Item['FirstLetter'] = 'Sehr geehrte Damen und Herren';
                $Item['SecondLetter'] = 'Liebe Damen und Herren';

                // first column
                if($Item['Salutation']){
                    if($Item['Salutation'] == 'Herr'){
                        $Item['AddressSalutation'] = 'Herrn';
                    } elseif($Item['Salutation'] == 'Frau'){
                        $Item['AddressSalutation'] = 'Frau';
                    }
                }
                // second column
                if($Item['FirstName'] && $Item['LastName']){
                    $Item['AddressName'] = ($Item['Title'] ? $Item['Title'].' ': '').$Item['FirstName'].' '.$Item['LastName'];
                }
                // third column
                if($Item['Salutation']){
                    if($Item['Salutation'] == 'Herr'){
                        $Item['FirstLetter'] = 'Sehr geehrter '.$Item['Salutation'].' '.($Item['Title'] ? $Item['Title'].' ': '').$Item['LastName'];
                    } elseif($Item['Salutation'] == 'Frau'){
                        $Item['FirstLetter'] = 'Sehr geehrte '.$Item['Salutation'].' '.($Item['Title'] ? $Item['Title'].' ': '').$Item['LastName'];
                    }
                }
                // fourth column
                if($Item['Salutation']){
                    if($Item['Salutation'] == 'Herr'){
                        $Item['SecondLetter'] = 'Lieber '.$Item['Salutation'].' '.($Item['Title'] ? $Item['Title'].' ': '').$Item['LastName'];
                    } elseif($Item['Salutation'] == 'Frau'){
                        $Item['SecondLetter'] = 'Liebe '.$Item['Salutation'].' '.($Item['Title'] ? $Item['Title'].' ': '').$Item['LastName'];
                    }
                }
                array_push($ExportData, $Item);
            });
        }

        if (!empty($ExportData)) {

            $row = 0;
            $column = 0;
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell($column++, $row), "Adressanrede");
            $export->setValue($export->getCell($column++, $row), "Adressname");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 1 (Sehr geehrter/geehrte)");
            $export->setValue($export->getCell($column++, $row), "Briefanrede 2 (Lieber/Liebe)");

            $export->setValue($export->getCell($column++, $row), "Anrede");
            $export->setValue($export->getCell($column++, $row), "Titel");
            $export->setValue($export->getCell($column++, $row), "Vorname");
//            $export->setValue($export->getCell($column++, $row), "Zweiter Vorname");
            $export->setValue($export->getCell($column++, $row), "Nachname");

            $export->setValue($export->getCell($column++, $row), "Ortsteil");
            $export->setValue($export->getCell($column++, $row), "Straße");
            $export->setValue($export->getCell($column++, $row), "PLZ");
            $export->setValue($export->getCell($column++, $row), "Ort");
            $export->setValue($export->getCell($column++, $row), "PLZ/Ort");
            $export->setValue($export->getCell($column++, $row), "Institution");
            $export->setValue($export->getCell($column++, $row), "Institution Zusatz");
            $export->setValue($export->getCell($column, $row), "Institution Beschreibung");

            $row = 1;
            /** @var TblAddressPerson $tblAddressPerson */
            foreach ($ExportData as $Export) {
                $column = 0;
                // letter columns
                $export->setValue($export->getCell($column++, $row), $Export['AddressSalutation']);
                $export->setValue($export->getCell($column++, $row), $Export['AddressName']);
                $export->setValue($export->getCell($column++, $row), $Export['FirstLetter']);
                $export->setValue($export->getCell($column++, $row), $Export['SecondLetter']);
                // person columns
                $export->setValue($export->getCell($column++, $row), $Export['Salutation']);
                $export->setValue($export->getCell($column++, $row), $Export['Title']);
                $export->setValue($export->getCell($column++, $row), $Export['FirstName']);
//                $export->setValue($export->getCell($column++, $row), $Export['SecondName']);
                $export->setValue($export->getCell($column++, $row), $Export['LastName']);
                // company columns
                $export->setValue($export->getCell($column++, $row), $Export['District']);
                $export->setValue($export->getCell($column++, $row), $Export['Street']);
                $export->setValue($export->getCell($column++, $row), $Export['Code']);
                $export->setValue($export->getCell($column++, $row), $Export['City']);
                $export->setValue($export->getCell($column++, $row), $Export['CodeCity']);
                $export->setValue($export->getCell($column++, $row), $Export['CompanyName']);
                $export->setValue($export->getCell($column++, $row), $Export['CompanyExtendedName']);
                $export->setValue($export->getCell($column, $row), $Export['CompanyDescription']);

                $row++;
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
     * @param array           $tblPersonList
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialPersonBulk(TblSerialLetter $tblSerialLetter, $tblPersonList)
    {

        return ( new Data($this->getBinding()) )->addSerialPersonBulk($tblSerialLetter, $tblPersonList);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $tblCompanyList
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialCompanyBulk(TblSerialLetter $tblSerialLetter, $tblCompanyList)
    {

        return ( new Data($this->getBinding()) )->addSerialCompanyBulk($tblSerialLetter, $tblCompanyList);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblSerialLetter     $tblSerialLetter
     * @param array               $SerialLetter
     * @param null                $FilterGroup
     * @param null                $FilterStudent
     * @param null                $FilterYear
     * @param null                $FilterProspect
     * @param null                $FilterCompany
     * @param null                $FilterRelationship
     *
     * @return IFormInterface|string
     */
    public function updateSerialLetter(
        IFormInterface $Stage = null,
        TblSerialLetter $tblSerialLetter,
        $SerialLetter = null,
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterYear = null,
        $FilterProspect = null,
        $FilterCompany = null,
        $FilterRelationship = null
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
        } else {
            if (( $tblSerialLetterByName = SerialLetter::useService()->getSerialLetterByName($SerialLetter['Name']) )) {
                if ($tblSerialLetterByName->getId() !== $tblSerialLetter->getId()) {
                    $Stage->setError('SerialLetter[Name]', 'Der Name für den Serienbrief exisitert bereits. Bitte wählen Sie einen anderen');
                    $Error = true;
                }
            }
        }
        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
        if (!$Error) {
            $tblSerialLetter = ( new Data($this->getBinding()) )->updateSerialLetter(
                $tblSerialLetter,
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );

            if ($tblSerialLetter) {

                if ($tblFilterCategory) {

                    $SaveFilterField = true;

                    // remove all exist FilterField
                    ( new Data($this->getBinding()) )->destroyFilterFiledAllBySerialLetter($tblSerialLetter);

                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        // update PersonList
                        $Result = (new SerialLetterFilter())->getGroupFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }
                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        if ($SaveFilterField) {
                                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                                $FieldName, $FieldValue, $FilterNumber);
                                        }
                                    }
                                }
                            }
                        }
                        if (!empty($FilterStudent)) {
                            foreach ($FilterStudent as $FieldName => $FilterNumberList) {
                                foreach ($FilterNumberList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        if (!empty($FilterYear)) {
                            foreach ($FilterYear as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        // update PersonList
                        $Result = (new SerialLetterFilter())->getStudentFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }
                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        if (!empty($FilterProspect)) {
                            foreach ($FilterProspect as $FieldName => $FilterNumberList) {
                                foreach ($FilterNumberList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        // update PersonList
                        $Result = (new SerialLetterFilter())->getProspectFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }

                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        if (!empty($FilterCompany)) {
                            foreach ($FilterCompany as $FieldName => $FilterNumberList) {
                                foreach ($FilterNumberList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        if (!empty($FilterRelationship)) {
                            foreach ($FilterRelationship as $FieldName => $FilterList) {
                                foreach ($FilterList as $FilterNumber => $FieldValue) {
                                    if ($FieldValue) {
                                        ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory,
                                            $FieldName, $FieldValue, $FilterNumber);
                                    }
                                }
                            }
                        }
                        // update CompanyList
                        $Result = (new SerialLetterFilter())->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
                        $tblCompanySearchList = (new SerialLetterFilter())->getCompanyListByResult($Result);
                        SerialLetter::useService()->updateDynamicSerialCompany($tblSerialLetter, $tblCompanySearchList);
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe wurde gespeichert')
                .new Redirect('/Reporting/SerialLetter/Edit', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
        }

        return $Stage;
    }

    /**
     * @param TblSerialLetter   $tblSerialLetter
     * @param TblFilterCategory $tblFilterCategory
     */
    public function updateSerialPerson(TblSerialLetter $tblSerialLetter, TblFilterCategory $tblFilterCategory)
    {

        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
            $Result = (new SerialLetterFilter())->getGroupFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
            $Result = (new SerialLetterFilter())->getStudentFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
            $Result = (new SerialLetterFilter())->getProspectFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = (new SerialLetterFilter())->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
    }

    /**
     * @param TblSerialLetter   $tblSerialLetter
     */
    public function updateSerialCompany(TblSerialLetter $tblSerialLetter)
    {

        $Result = (new SerialLetterFilter())->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
        $tblCompanySearchList = (new SerialLetterFilter())->getCompanyListByResult($Result);
        SerialLetter::useService()->updateDynamicSerialCompany($tblSerialLetter, $tblCompanySearchList);
    }

    /**
     * @param TblSerialCompany $tblSerialCompany
     * @param                  $IsIgnore
     *
     * @return bool
     */
    public function changeSerialCompanyStatus(TblSerialCompany $tblSerialCompany, $IsIgnore)
    {

        return ( new Data($this->getBinding()) )->changeSerialCompanyStatus($tblSerialCompany, $IsIgnore);
    }

    /**
     * @param TblSerialLetter  $tblSerialLetter
     * @param bool|TblPerson[] $tblPersonSearchList
     */
    public function updateDynamicSerialPerson(TblSerialLetter $tblSerialLetter, $tblPersonSearchList)
    {

        if ($tblPersonSearchList) {

            // existing SerialPersonList
            $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            $tblPersonList = array();
            if ($tblSerialPersonList) {
                foreach ($tblSerialPersonList as $tblSerialPerson) {
                    $tblPersonList[] = $tblSerialPerson->getServiceTblPerson();
                }
            }
            // remove Person on SerialPerson without matching Filter
            $PersonRemoveList = array_diff($tblPersonList, $tblPersonSearchList);
            $PersonRemoveList = array_filter($PersonRemoveList);
            if (!empty($PersonRemoveList)) {
                $this->removeSerialPersonBulk($tblSerialLetter, $PersonRemoveList);
            }
            // add Person with matching Filter that not exist on SerialPerson
            $PersonAddList = array_diff($tblPersonSearchList, $tblPersonList);
            $PersonAddList = array_filter($PersonAddList);
            if (!empty($PersonAddList)) {
                $this->addSerialPersonBulk($tblSerialLetter, $PersonAddList);
            }
        } else {
            // delete all exist SerialPerson if result is false
            SerialLetter::useService()->destroySerialPerson($tblSerialLetter);
        }
    }

    /**
     * @param TblSerialLetter  $tblSerialLetter
     * @param bool|array $tblCompanySearchList
     */
    public function updateDynamicSerialCompany(TblSerialLetter $tblSerialLetter, $tblCompanySearchList)
    {

        if (!empty($tblCompanySearchList)) {

            // existing SerialPersonList
            $tblCompanyFilterList = array();

            foreach ($tblCompanySearchList as $CompanyId => $PersonList) {
                $tblCompany = Company::useService()->getCompanyById($CompanyId);
                $tblSerialCompanyList = array();
                if($tblCompany && !empty($PersonList)){
                    foreach($PersonList as $PersonId){
                        if($PersonId){
                            if(($tblPerson = Person::useService()->getPersonById($PersonId))){
                                // with person
                                $tblSerialCompanyList[] = SerialLetter::useService()->getSerialCompanyBySerialLetterAndCompanyAndPerson(
                                    $tblSerialLetter, $tblCompany, $tblPerson);
                            }
                        } else {
                            // without person
                            $tblSerialCompany = SerialLetter::useService()->getSerialCompanyBySerialLetterAndCompanyAndPerson(
                                $tblSerialLetter, $tblCompany, null);
                        }
                    }
                }

                // add company with person (more than on is possible)
                if(!empty($tblSerialCompanyList)) {
                    $tblSerialCompanyList = array_filter($tblSerialCompanyList);
                    /** @var TblSerialCompany $tblSerialCompany */
                    array_walk($tblSerialCompanyList, function(TblSerialCompany $tblSerialCompany) use(&$tblCompanyFilterList) {
                        $tblCompanyFilterList[$tblSerialCompany->getId()] = $tblSerialCompany->getId();
                    });
                }
                // add company without person
                if(isset($tblSerialCompany) && $tblSerialCompany){
                    $tblCompanyFilterList[$tblSerialCompany->getId()] = $tblSerialCompany->getServiceTblPerson();
                }
            }

            //find Entry that miss in filter
            $SerialCompanyDivList = array();
            if (($tblSerialCompanyList = SerialLetter::useService()->getSerialCompanyBySerialLetter($tblSerialLetter))) {

                $tblSerialCompanyIdList = array();
                array_walk($tblSerialCompanyList, function(TblSerialCompany $tblSerialCompany) use (&$tblSerialCompanyIdList){
                    $tblSerialCompanyIdList[$tblSerialCompany->getId()] = $tblSerialCompany->getId();
                });
                $SerialCompanyDivList = array_diff($tblSerialCompanyIdList, $tblCompanyFilterList);
            }

            // remove Person on SerialPerson without matching Filter
            if (!empty($SerialCompanyDivList)) {
                $this->removeSerialCompanyBulk($SerialCompanyDivList);
            }
            // add Company and Person with matching Filter that not exist on SerialCompany

//            $PersonAddList = array_diff($tblPersonSearchList, $tblPersonList);
            $tblCompanySearchList = array_filter($tblCompanySearchList);
            if (!empty($tblCompanySearchList)) {
                $this->addSerialCompanyBulk($tblSerialLetter, $tblCompanySearchList);
            }
        } else {
            // delete all exist SerialCompany if result is false
            SerialLetter::useService()->destroySerialCompany($tblSerialLetter);
        }
    }

//    /**
//     * @param TblSerialLetter  $tblSerialLetter
//     * @param bool|TblPerson[] $tblPersonSearchList
//     */
//    public function updateDynamicSerialPerson(TblSerialLetter $tblSerialLetter, $tblPersonSearchList)
//    {
//
//    }

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
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $tblPersonList
     */
    public function removeSerialPersonBulk(TblSerialLetter $tblSerialLetter, $tblPersonList)
    {

        return ( new Data($this->getBinding()) )->removeSerialPersonBulk($tblSerialLetter, $tblPersonList);
    }

    /**
     * @param array $SerialCompanyDivList
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function removeSerialCompanyBulk($SerialCompanyDivList)
    {

        return ( new Data($this->getBinding()) )->removeSerialCompanyBulk($SerialCompanyDivList);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     */
    public function destroySerialPerson(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->destroySerialPerson($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     */
    public function destroySerialCompany(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->destroySerialCompany($tblSerialLetter);
    }

    /**
     * @param TblAddressPerson $tblAddressPerson
     *
     * @return bool
     */
    public function destroySerialAddressPerson(TblAddressPerson $tblAddressPerson)
    {
        return ( new Data($this->getBinding()) )->destroyAddressPerson($tblAddressPerson);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $this->destroySerialCompanyBySerialLetter($tblSerialLetter);
        $this->destroyAddressPersonAllBySerialLetter($tblSerialLetter);
        $this->destroyFilterFiledAllBySerialLetter($tblSerialLetter);
        $this->destroySerialPerson($tblSerialLetter);

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

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->destroyAddressPersonAllBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialCompanyBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->destroySerialCompanyBySerialLetter($tblSerialLetter);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyFilterFiledAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return ( new Data($this->getBinding()) )->destroyFilterFiledAllBySerialLetter($tblSerialLetter);
    }
}