<?php
namespace SPHERE\Application\Reporting\SerialLetter;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\Application\Reporting\SerialLetter\Service\Data;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

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

        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
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

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblFilterField[]
     */
    public function getFilterFieldActiveAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $tblFilterCategory = $tblSerialLetter->getFilterCategory();
        if ($tblFilterCategory) {
            return ( new Data($this->getBinding()) )->getFilterFieldActiveAllBySerialLetter($tblSerialLetter, $tblFilterCategory);
        }
        return false;
    }

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
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return ( new Data($this->getBinding()) )->getSerialLetterAll();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param bool            $isCompany
     *
     * @return int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter, $isCompany = false)
    {

        $result = 0;
        $tblSerialLetterPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);

        if ($isCompany) {
//            return ( new Data($this->getBinding()) )->getSerialLetterCount($tblSerialLetter);

            if ($tblSerialLetterPersonList) {
                foreach ($tblSerialLetterPersonList as $tblPerson) {
                    $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
                    if ($tblAddressPersonList) {
                        $result = $result + count($tblAddressPersonList);
                    }
                }
            }
        } else {
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
     * @param string          $FirstGender 'M'(ale) or 'F'(emale)
     *
     * @return bool|Service\Entity\TblAddressPerson[]
     */
    public function getAddressPersonAllByPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        $FirstGender = null
    ) {
//        $FirstGender = 'F';

        $tblAddressPersonList = ( new Data($this->getBinding()) )->getAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);

        if ($tblAddressPersonList && $FirstGender != null) {
            $AddressPersonList = array();
            foreach ($tblAddressPersonList as $AddressPerson) {
                $tblPerson = $AddressPerson->getServiceTblPersonToAddress();
                if ($tblPerson) {
                    if ($FirstGender === 'M' && $tblPerson->getSalutation() === 'Herr') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                    if ($FirstGender === 'F' && $tblPerson->getSalutation() === 'Frau') {
                        $AddressPersonList[] = $AddressPerson;
                    }
                }
            }

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

    /**
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
                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id]', 'Bitte geben Sie eine Gruppe an.');
                    $Error = true;
                }
            }
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id]', 'Bitte geben Sie eine Gruppe an.');
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
                        foreach ($FilterGroup as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Person Field
                    if (isset($FilterPerson) && !empty($FilterPerson)) {
                        foreach ($FilterPerson as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Student Field
                    if (isset($FilterStudent) && !empty($FilterStudent)) {
                        foreach ($FilterStudent as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Year Field
                    if (isset($FilterYear) && !empty($FilterYear)) {
                        foreach ($FilterYear as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Prospect Field
                    if (isset($FilterProspect) && !empty($FilterProspect)) {
                        foreach ($FilterProspect as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Prospect Field
                    if (isset($FilterCompany) && !empty($FilterCompany)) {
                        foreach ($FilterCompany as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }
                    // save Prospect Field
                    if (isset($FilterRelationship) && !empty($FilterRelationship)) {
                        foreach ($FilterRelationship as $FieldName => $Value) {
                            ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $Value);
                        }
                    }

                    if ($tblFilterCategory) {
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                            $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'PERSONGROUP';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                            $Result = SerialLetter::useService()->getStudentFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'STUDENT';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                            $Result = SerialLetter::useService()->getProspectFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                            $TabActive = 'PROSPECT';
                        }
                        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                            $Result = SerialLetter::useService()->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
                            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
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
        if (null === $Check && !isset($Global->POST['Button'])) {
            return $Form;
        }
        $isCompany = false;
        $FilterCategory = SerialLetter::useService()->getFilterCategoryByName(TblFilterCategory::IDENTIFIER_COMPANY_GROUP);
        if (( $tblFilterCategory = $tblSerialLetter->getFilterCategory() )) {
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
                                    $tblSalutation = $tblPerson->getTblSalutation();
                                    $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                        $tblPerson, null, $tblToCompany,
                                        ( $tblSalutation ? $tblSalutation : null ));
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
                                        $tblSalutation = $tblPersonToPerson->getTblSalutation();

                                        $this->createAddressPerson($tblSerialLetter, $tblPerson,
                                            $tblToPerson->getServiceTblPerson(), $tblToPerson, null,
                                            ( $tblSalutation ? $tblSalutation : null ));
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
                            SerialLetter::useService()->createAddressPerson($tblSerialLetter, $tblPerson, $tblPerson, $tblToPersonChoose, null, $tblSalutation);
                        }
                    }
                }
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
                                            $tblSalutation = $SalutationList[$count];
                                        } else {
                                            $tblSalutation = false;
                                        }
                                        if (isset($SalutationList[$count])) {
                                            $PersonTo = $Person[$count];
                                        } else {
                                            $PersonTo = false;
                                        }
                                        SerialLetter::useService()->createAddressPerson(
                                            $tblSerialLetter, $tblPerson, $PersonTo, $tblToPersonChoose, null, ( $tblSalutation ? $tblSalutation : null ));
                                        $count++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return new Warning('Es sind keine Personen im Serienbrief hinterlegt');
        }
        return new Success('Mögliche Adressenzuweisungen wurde vorgenommen')
            .new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter    $tblSerialLetter
     * @param TblPerson          $tblPerson
     * @param TblPerson          $tblPersonToAddress
     * @param null|TblToPerson   $tblToPerson
     * @param null|TblToCompany  $tblToCompany
     * @param TblSalutation|null $tblSalutation
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson = null,
        TblToCompany $tblToCompany = null,
        TblSalutation $tblSalutation = null
    ) {

        return ( new Data($this->getBinding()) )->createAddressPerson($tblSerialLetter, $tblPerson, $tblPersonToAddress,
            $tblToPerson, $tblToCompany, $tblSalutation);
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
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
                    $tblPerson, 'M');    // ToDO choose FirstGender
                if ($tblAddressPersonAllByPerson) {
                    /** @var TblAddressPerson $tblAddressPerson */
                    $AddressList = array();
                    array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson)
                    use (&$AddressList, $tblPerson, &$AddressPersonCount, $tblFilterCategory) {

                        if ($tblFilterCategory
                            && TblFilterCategory::IDENTIFIER_COMPANY_GROUP == $tblFilterCategory->getName()
                        ) {
                            $tblToCompany = $tblAddressPerson->getServiceTblToPerson($tblFilterCategory);
                            $tblAddress = $tblToCompany->getTblAddress();
                            if ($tblAddress) {
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                    $tblPerson->getSalutation();
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['FirstName'] =
                                    $tblPerson->getFirstName();
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['LastName'] =
                                    $tblPerson->getLastName();

                                // choose Person
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonSalutation'][] =
                                    $tblPerson->getSalutation();
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonFirstName'][] =
                                    $tblPerson->getFirstName();
                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonLastName'][] =
                                    $tblPerson->getLastName();
                                // Address
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

                        } else {
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
                                            if ($tblStudent) {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $tblStudent->getIdentifier();
                                            } else {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = '';
                                            }

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
                                                if ($Address['PersonSalutation'][$Key] == 'Herr') {
                                                    $firstAddressLine .= ' und '.$Address['PersonSalutation'][$Key].'n';
                                                }
                                                if ($Address['PersonSalutation'][$Key] == 'Frau') {
                                                    $firstAddressLine .= ' und '.$Address['PersonSalutation'][$Key];
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
            $export->setValue($export->getCell($column, $row), "Person_Aktuelle Klasse(n)");

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
                $export->setValue($export->getCell($column, $row), $Export['Division']);

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
     * @param TblAddressPerson $tblAddressPerson
     *
     * @return bool
     */
    public function destroySerialAddressPerson(TblAddressPerson $tblAddressPerson)
    {
        return ( new Data($this->getBinding()) )->destroyAddressPerson($tblAddressPerson);
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
        if ($tblFilterCategory) {
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id]', 'Bitte geben Sie eine Gruppe an.');
                    $Error = true;
                }
            }
            if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] == 0) {
                    $Stage->setError('FilterGroup[TblGroup_Id]', 'Bitte geben Sie eine Gruppe an.');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblSerialLetter = ( new Data($this->getBinding()) )->updateSerialLetter(
                $tblSerialLetter,
                $SerialLetter['Name'],
                $SerialLetter['Description']
            );

            if ($tblSerialLetter) {

                if ($tblFilterCategory) {
//                    // remove all exist FilterField
//                    ( new Data($this->getBinding()) )->destroyFilterFiledAllBySerialLetter($tblSerialLetter);

                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        // update PersonList
                        $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }
                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        if (!empty($FilterStudent)) {
                            foreach ($FilterStudent as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        if (!empty($FilterYear)) {
                            foreach ($FilterYear as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        // update PersonList
                        $Result = SerialLetter::useService()->getStudentFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }
                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        if (!empty($FilterProspect)) {
                            foreach ($FilterProspect as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        // update PersonList
                        $Result = SerialLetter::useService()->getProspectFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
                    }

                    if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                        if (!empty($FilterGroup)) {
                            foreach ($FilterGroup as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        if (!empty($FilterCompany)) {
                            foreach ($FilterCompany as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        if (!empty($FilterRelationship)) {
                            foreach ($FilterRelationship as $FieldName => $FieldValue) {
                                ( new Data($this->getBinding()) )->createFilterField($tblSerialLetter, $tblFilterCategory, $FieldName, $FieldValue);
                            }
                        }
                        // update PersonList
                        $Result = SerialLetter::useService()->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
                        $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
                        SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
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
            $Result = SerialLetter::useService()->getGroupFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT) {
            $Result = SerialLetter::useService()->getStudentFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT) {
            $Result = SerialLetter::useService()->getProspectFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
        if ($tblFilterCategory->getName() === TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
            $Result = SerialLetter::useService()->getCompanyFilterResultListBySerialLetter($tblSerialLetter);
            $tblPersonSearchList = SerialLetter::useService()->getPersonListByResult($tblSerialLetter, $Result);
            SerialLetter::useService()->updateDynamicSerialPerson($tblSerialLetter, $tblPersonSearchList);
        }
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
     *
     * @return bool
     */
    public function removeSerialLetterAddress(TblSerialLetter $tblSerialLetter)
    {
        $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
        if ($tblSerialPersonList) {
            foreach ($tblSerialPersonList as $tblSerialPerson) {
                $tblPerson = $tblSerialPerson->getServiceTblPerson();
                if ($tblPerson) {
                    // Destroy Address
                    SerialLetter::useService()->destroyAddressPersonAllBySerialLetterAndPerson($tblSerialLetter, $tblPerson);
                }
            }
            return true;
        } else {
            return false;
        }
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
     * @param TblSerialLetter  $tblSerialLetter
     * @param bool|TblPerson[] $tblPersonSearchList
     */
    public function updateDynamicSerialPerson(TblSerialLetter $tblSerialLetter, $tblPersonSearchList)
    {

        if ($tblPersonSearchList) {

            $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            $tblPersonList = array();
            if ($tblSerialPersonList) {
                foreach ($tblSerialPersonList as $tblSerialPerson) {
                    $tblPersonList[] = $tblSerialPerson->getServiceTblPerson();
                }
            }

            $PersonRemoveList = array_diff($tblPersonList, $tblPersonSearchList);
            if ($PersonRemoveList) {
                foreach ($PersonRemoveList as $PersonRemove) {
                    if ($PersonRemove) {
                        $this->removeSerialPerson($tblSerialLetter, $PersonRemove);
                    }
                }
            }
            $PersonAddList = array_diff($tblPersonSearchList, $tblPersonList);
            if (!empty($PersonAddList)) {
                foreach ($PersonAddList as $PersonAdd) {
                    if ($PersonAdd) {
                        $this->addSerialPerson($tblSerialLetter, $PersonAdd);
                    }
                }
            }
        } else {
            // delete all exist SerialPerson if result is false
            $tblSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            if ($tblSerialPersonList) {
                foreach ($tblSerialPersonList as $tblSerialPerson) {
                    if ($tblSerialPerson && $tblSerialPerson->getServiceTblPerson()) {
                        $this->removeSerialPerson($tblSerialLetter, $tblSerialPerson->getServiceTblPerson());
                    }
                }
            }
        }
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param                      $Result
     *
     * @return array|bool TblPerson[]
     */
    public function getPersonListByResult(TblSerialLetter $tblSerialLetter = null, $Result)
    {
        $tblCategory = false;
        if ($tblSerialLetter !== null) {
            $tblCategory = $tblSerialLetter->getFilterCategory();
        }

        $PersonList = array();
        $PersonIdList = array();
        if ($Result && !empty($Result)) {
            if (!$tblCategory
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT
                || $tblCategory->getName() == TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT
            ) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[1]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            } elseif ($tblCategory->getName() == TblFilterCategory::IDENTIFIER_COMPANY_GROUP) {
                /** @var AbstractView[]|ViewPerson[] $Row */
                foreach ($Result as $Index => $Row) {
                    $DataPerson = $Row[3]->__toArray();
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $PersonIdList)) {
                        $PersonIdList[$DataPerson['TblPerson_Id']] = $DataPerson['TblPerson_Id'];
                    }
                }
            }

            if (!empty($PersonIdList)) {
                foreach ($PersonIdList as $PersonId) {
                    $PersonList[] = Person::useService()->getPersonById($PersonId);
                }
            }
        }
        return ( !empty($PersonList) ? $PersonList : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroup
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getGroupFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroup = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldActiveAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroup[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        $Result = array();

        //Filter Group
        if (isset($FilterGroup['TblGroup_Id']) && !empty($FilterGroup['TblGroup_Id'])
        ) {
            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );

            if ($FilterGroup) {
                // Preparation FilterGroup
                array_walk($FilterGroup, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterGroup = array_filter($FilterGroup);
            } else {
                $FilterGroup = array();
            }
            // Preparation FilterPerson
            $FilterPerson = array();

            $Result = $Pile->searchPile(array(
                0 => $FilterGroup,
                1 => $FilterPerson
            ));
            // get Timeout status
            $IsTimeout = $Pile->isTimeout();
        }

        return ( !empty($Result) ? $Result : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroup
     * @param array                $FilterStudent
     * @param array                $FilterYear
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getStudentFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroup = array(),
        $FilterStudent = array(),
        $FilterYear = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldActiveAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            /** @var TblFilterField $tblFilterField */
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroup[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblLevel_')) {
                    $FilterStudent[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblDivision_')) {
                    $FilterStudent[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblYear_')) {
                    $FilterYear[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }
        $Result = array();

        //Filter Group
        if (isset($FilterGroup['TblGroup_Id']) && !empty($FilterGroup['TblGroup_Id'])
        ) {

            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
                ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
            );
            $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
                ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
            );

            if ($FilterGroup) {
                // Preparation FilterGroup
                array_walk($FilterGroup, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterGroup = array_filter($FilterGroup);
            } else {
                $FilterGroup = array();
            }
            // Preparation FilterPerson
            $FilterPerson = array();

            // Preparation $FilterStudent
            if ($FilterStudent) {
                array_walk($FilterStudent, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterStudent = array_filter($FilterStudent);
            } else {
                $FilterStudent = array();
            }
            // Preparation $FilterYear
            if ($FilterYear) {
                array_walk($FilterYear, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterYear = array_filter($FilterYear);
            } else {
                $FilterYear = array();
            }

            $Result = $Pile->searchPile(array(
                0 => $FilterGroup,
                1 => $FilterPerson,
                2 => $FilterStudent,
                3 => $FilterYear
            ));
            // get Timeout status
            $IsTimeout = $Pile->isTimeout();

        }

        return ( !empty($Result) ? $Result : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroup
     * @param array                $FilterProspect
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getProspectFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroup = array(),
        $FilterProspect = array(),
        &$IsTimeout = false
    )
    {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldActiveAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroup[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblProspectReservation_')) {
                    $FilterProspect[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }

        $Result = array();

        //Filter Group
        if (isset($FilterGroup['TblGroup_Id']) && !empty($FilterGroup['TblGroup_Id'])
        ) {
            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewPeopleMetaProspect() )->getViewService(), new ViewPeopleMetaProspect(),
                ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON, ViewPeopleMetaProspect::TBL_PROSPECT_SERVICE_TBL_PERSON
            );

            if ($FilterGroup) {
                // Preparation FilterGroup
                array_walk($FilterGroup, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterGroup = array_filter($FilterGroup);
            } else {
                $FilterGroup = array();
            }
            // Preparation FilterPerson
            $FilterPerson = array();

            // Preparation FilterProspect
            if ($FilterProspect) {
                array_walk($FilterProspect, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterProspect = array_filter($FilterProspect);
            } else {
                $FilterProspect = array();
            }
            // Filter first time
            $Result = $Pile->searchPile(array(
                0 => $FilterGroup,
                1 => $FilterPerson,
                2 => $FilterProspect
            ));
            // get Timeout status
            $IsTimeout = $Pile->isTimeout();

            // change OptionA to Option B
            if (isset($FilterProspect['TblProspectReservation_serviceTblTypeOptionA'])) {
                $FilterProspect['TblProspectReservation_serviceTblTypeOptionB'] = $FilterProspect['TblProspectReservation_serviceTblTypeOptionA'];
                unset($FilterProspect['TblProspectReservation_serviceTblTypeOptionA']);
                $Result2 = array();
                //Filter Group first Time
                if (isset($FilterGroup['TblGroup_Id']) && !empty($FilterGroup['TblGroup_Id'])
                ) {
                    // Preparation FilterProspect
                    if ($FilterProspect) {
                        array_walk($FilterProspect, function (&$Input) {
                            if (!is_array($Input)) {
                                if (!empty($Input)) {
                                    $Input = explode(' ', $Input);
                                    $Input = array_filter($Input);
                                } else {
                                    $Input = false;
                                }
                            }
                        });
                        $FilterProspect = array_filter($FilterProspect);
                    } else {
                        $FilterProspect = array();
                    }
                    // Filter second time
                    $Result2 = $Pile->searchPile(array(
                        0 => $FilterGroup,
                        1 => $FilterPerson,
                        2 => $FilterProspect
                    ));
                    if ($IsTimeout == false) {
                        // get Timeout status
                        $IsTimeout = $Pile->isTimeout();
                    }
                }
            }
        }
        if (!empty($Result) && isset($Result2) && !empty($Result2)) {
            foreach ($Result2 as $Row) {
                $Result[] = $Row;
            }
        } elseif (empty($Result) && isset($Result2) && !empty($Result2)) {
            $Result = $Result2;
        }

        return ( !empty($Result) ? $Result : false );
    }

    /**
     * @param TblSerialLetter|null $tblSerialLetter
     * @param array                $FilterGroup
     * @param array                $FilterCompany
     * @param array                $FilterRelationship
     * @param bool                 $IsTimeout (if search reach timeout)
     *
     * @return array|bool
     */
    public function getCompanyFilterResultListBySerialLetter(
        TblSerialLetter $tblSerialLetter = null,
        $FilterGroup = array(),
        $FilterCompany = array(),
        $FilterRelationship = array(),
        &$IsTimeout = false
    ) {
        $tblFilterFieldList = ( $tblSerialLetter != null
            ? SerialLetter::useService()->getFilterFieldActiveAllBySerialLetter($tblSerialLetter)
            : false );
        if ($tblFilterFieldList) {
            foreach ($tblFilterFieldList as $tblFilterField) {
                if (stristr($tblFilterField->getField(), 'TblGroup_')) {
                    $FilterGroup[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblCompany_')) {
                    $FilterCompany[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
                if (stristr($tblFilterField->getField(), 'TblType_')) {
                    $FilterRelationship[$tblFilterField->getField()] = $tblFilterField->getValue();
                }
            }
        }

        $Result = array();

        //Filter Group
        if (isset($FilterGroup['TblGroup_Id']) && !empty($FilterGroup['TblGroup_Id'])
        ) {
            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewCompanyGroupMember() )->getViewService(), new ViewCompanyGroupMember(),
                null, ViewCompanyGroupMember::TBL_MEMBER_SERVICE_TBL_COMPANY
            );
            $Pile->addPile(( new ViewCompany() )->getViewService(), new ViewCompany(),
                ViewCompany::TBL_COMPANY_ID, ViewCompany::TBL_COMPANY_ID
            );
            $Pile->addPile(( new ViewRelationshipToCompany() )->getViewService(), new ViewRelationshipToCompany(),
                ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );

            if ($FilterGroup) {
                // Preparation FilterGroup
                array_walk($FilterGroup, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterGroup = array_filter($FilterGroup);
            } else {
                $FilterGroup = array();
            }
            // Preparation FilterCompany
            if ($FilterCompany) {
                array_walk($FilterCompany, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterCompany = array_filter($FilterCompany);
            } else {
                $FilterCompany = array();
            }
            // Preparation FilterRelationship
            if ($FilterRelationship) {
                array_walk($FilterRelationship, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty($Input)) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterRelationship = array_filter($FilterRelationship);
            } else {
                $FilterRelationship = array();
            }
            // Preparation FilterPerson
            $FilterPerson = array();

            $Result = $Pile->searchPile(array(
                0 => $FilterGroup,
                1 => $FilterCompany,
                2 => $FilterRelationship,
                3 => $FilterPerson
            ));
            // get Timeout status
            $IsTimeout = $Pile->isTimeout();
        }

        return ( !empty($Result) ? $Result : false );
    }
}