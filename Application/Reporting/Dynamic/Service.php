<?php
namespace SPHERE\Application\Reporting\Dynamic;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Reporting\Dynamic\Service\Data;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilter;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterMask;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterOption;
use SPHERE\Application\Reporting\Dynamic\Service\Entity\TblDynamicFilterSearch;
use SPHERE\Application\Reporting\Dynamic\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Dynamic
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = ( new Setup($this->getStructure()) )->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            ( new Data($this->getBinding()) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilter
     */
    public function getDynamicFilterById($Id)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterById($Id);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAll(TblAccount $tblAccount)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterAll($tblAccount);
    }

    /**
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAllByIsPublic()
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterAllByIsPublic();
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return false|TblAccount[]
     */
    public function getDynamicFilterAllByAccount(TblAccount $tblAccount)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterAllByAccount($tblAccount);
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterMask
     */
    public function getDynamicFilterMaskById($Id)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterMaskById($Id);
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param null|int         $FilterPileOrder
     *
     * @return false|TblDynamicFilterMask[]
     */
    public function getDynamicFilterMaskAllByFilter(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder = null)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterMaskAllByFilter($tblDynamicFilter, $FilterPileOrder);
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterOption
     */
    public function getDynamicFilterOptionById($Id)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterOptionById($Id);
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     *
     * @return false|TblDynamicFilterOption[]
     */
    public function getDynamicFilterOptionAllByMask(TblDynamicFilterMask $tblDynamicFilterMask = null)
    {
        return ( new Data($this->getBinding()) )->getDynamicFilterOptionAll($tblDynamicFilterMask);
    }

    /**
     * @param $Id
     *
     * @return false|TblDynamicFilterSearch
     */
    public function getDynamicFilterSearchById($Id)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterSearchById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param string         $FilterName
     * @param int            $IsPublic
     *
     * @return Form|string
     */
    public function createDynamicFilter(IFormInterface $Form, $FilterName, $IsPublic)
    {

        if (null === $FilterName) {
            return $Form;
        }

        $Error = false;

        if (empty( $FilterName )) {
            $Error = true;
            $Form->setError('FilterName', 'Bitte geben Sie einen Namen für die Auswertung an');
        }

        if (Dynamic::useService()->getDynamicFilterAllByName($FilterName,
            Account::useService()->getAccountBySession())
        ) {
            $Error = true;
            $Form->setError('FilterName', 'Dieser Name wird bereits für eine Auswertung verwendet');
        }

        if ($Error) {
            return $Form;
        }

        if (( new Data($this->getBinding()) )->createDynamicFilter(Account::useService()->getAccountBySession(),
            $FilterName, (bool)$IsPublic)
        ) {

            return new Success('Die Auswertung ist erstellt worden',
                new \SPHERE\Common\Frontend\Icon\Repository\Success()
            ).new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_SUCCESS);
        } else {

            return new Danger('Die Auswertung konnte nicht erstellt werden',
                new \SPHERE\Common\Frontend\Icon\Repository\Disable()
            ).new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDynamicFilter    $tblDynamicFilter
     * @param null                $FilterName
     * @param bool                $IsPublic
     *
     * @return IFormInterface|string
     */
    public function changeDynamicFilter(IFormInterface &$Stage = null, TblDynamicFilter $tblDynamicFilter, $FilterName = null, $IsPublic = false)
    {

        /**
         * Skip to Frontend
         */

        if (null === $FilterName) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Filter['FilterName'] ) && empty( $Filter['FilterName'] )) {
            $Stage->setError('Filter[FilterName]', 'Bitte geben sie die Debitorennummer an');
            $Error = true;
        }

        if (!$Error) {

            if (( new Data($this->getBinding()) )->updateDynamicFilter($tblDynamicFilter, $FilterName, $IsPublic)) {
                return new Success('Der Filtername wurde angepasst')
                .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Filtername konnte nicht angepasst werden')
                .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     *
     * @return string
     */
    public function destroyDynamicFilter(TblDynamicFilter $tblDynamicFilter)
    {
        $FilterMaskList = Dynamic::useService()->getDynamicFilterMaskAllByFilter($tblDynamicFilter);
        if ($FilterMaskList) {
            foreach ($FilterMaskList as $FilterMask) {
                $FilterOptionList = Dynamic::useService()->getDynamicFilterOptionAllByMask($FilterMask);
                if ($FilterOptionList) {
                    foreach ($FilterOptionList as $FilterOption) {
                        ( new Data($this->getBinding()) )->removeDynamicFilterOption($FilterMask, $FilterOption->getFilterFieldName());
                    }
                }
                ( new Data($this->getBinding()) )->removeDynamicFilterMask($tblDynamicFilter, $FilterMask->getFilterPileOrder());
            }
        }

        $result = ( new Data($this->getBinding()) )->destroyDynamicFilter($tblDynamicFilter);
        if ($result) {
            return new Success('Die Auswertung wurde erfolgreich gelöscht')
            .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Warning('Die Auswertung konnte nicht gelöscht werden')
            .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface   $Form
     * @param TblDynamicFilter $tblDynamicFilter
     * @param array            $FilterFieldName
     *
     * @return Form|string
     */
    public function createDynamicFilterOption(IFormInterface $Form, TblDynamicFilter $tblDynamicFilter, $FilterFieldName)
    {
        if (null === $FilterFieldName) {
            return $Form;
        }

        $Error = false;

        if ($Error) {
            return $Form;
        }

        // Remove all Mask-Option-Checkboxes
        if(($tblDynamicFilterMaskList = $this->getDynamicFilterMaskAllByFilter( $tblDynamicFilter ))) {
            foreach($tblDynamicFilterMaskList as $tblDynamicFilterMask ) {
                if(($tblDynamicFilterOptionList = $this->getDynamicFilterOptionAllByMask($tblDynamicFilterMask))) {
                    foreach($tblDynamicFilterOptionList as $tblDynamicFilterOption ) {
                        $this->deleteDynamicFilterOption( $tblDynamicFilterMask, $tblDynamicFilterOption->getFilterFieldName() );
                    }
                }
            }
        }

        foreach ($FilterFieldName as $FilterPileOrder => $MaskFieldSelection) {
            if(count($tblDynamicFilterMask = $this->getDynamicFilterMaskAllByFilter($tblDynamicFilter, $FilterPileOrder)) == 1) {
                foreach ($MaskFieldSelection as $MaskFieldName => $Selected) {
                    $this->insertDynamicFilterOption(current($tblDynamicFilterMask), $MaskFieldName);
                }
            }
        }

        return $Form;
    }

    /**
     * @param string     $FilterName
     * @param TblAccount $tblAccount
     *
     * @return false|TblDynamicFilter[]
     */
    public function getDynamicFilterAllByName($FilterName, TblAccount $tblAccount = null)
    {

        return ( new Data($this->getBinding()) )->getDynamicFilterAllByName($FilterName, $tblAccount);
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param int              $FilterPileOrder
     * @param string           $FilterClassName
     *
     * @return bool
     */
    public function insertDynamicFilterMask(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder, $FilterClassName)
    {

        if (( new Data($this->getBinding()) )->addDynamicFilterMask(
            $tblDynamicFilter, $FilterPileOrder, $FilterClassName
        )
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param int              $FilterPileOrder
     *
     * @return bool
     */
    public function deleteDynamicFilterMask(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder)
    {

        if (( new Data($this->getBinding()) )->removeDynamicFilterMask(
            $tblDynamicFilter, $FilterPileOrder
        )
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     * @param string               $FilterFieldName
     * @param bool                 $IsMandatory
     *
     * @return bool
     */
    public function insertDynamicFilterOption(
        TblDynamicFilterMask $tblDynamicFilterMask,
        $FilterFieldName,
        $IsMandatory = false
    ) {

        if (( new Data($this->getBinding()) )->addDynamicFilterOption(
            $tblDynamicFilterMask, $FilterFieldName, $IsMandatory)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     * @param string               $FilterFieldName
     *
     * @return bool
     */
    public function deleteDynamicFilterOption(
        TblDynamicFilterMask $tblDynamicFilterMask,
        $FilterFieldName
    ) {
        if (( new Data($this->getBinding()) )->removeDynamicFilterOption(
            $tblDynamicFilterMask, $FilterFieldName)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblAccount $Account
     * @param            $FilterName
     * @param bool       $IsPublic
     *
     * @return TblDynamicFilter
     */
    public function addDynamicFilter(TblAccount $Account, $FilterName, $IsPublic = true)
    {

        return ( new Data($this->getBinding()) )->createDynamicFilter($Account, $FilterName, $IsPublic);
    }

    /**
     * @param TblDynamicFilter $tblDynamicFilter
     * @param                  $FilterPileOrder
     * @param                  $FilterClassName
     *
     * @return TblDynamicFilterMask
     */
    public function addDynamicFilterMask(TblDynamicFilter $tblDynamicFilter, $FilterPileOrder, $FilterClassName)
    {

        return ( new Data($this->getBinding()) )->addDynamicFilterMask($tblDynamicFilter, $FilterPileOrder, $FilterClassName);
    }

    /**
     * @param TblDynamicFilterMask $tblDynamicFilterMask
     * @param                      $FilterFieldName
     * @param bool                 $IsMandatory
     *
     * @return TblDynamicFilterOption
     */
    public function addDynamicFilterOption(TblDynamicFilterMask $tblDynamicFilterMask, $FilterFieldName, $IsMandatory = false)
    {

        return ( new Data($this->getBinding()) )->addDynamicFilterOption($tblDynamicFilterMask, $FilterFieldName, $IsMandatory);
    }

    /**
     * @param IFormInterface $Form
     * @param TblAccount     $tblAccount
     * @param null           $Data
     * @param null           $Reset
     *
     * @return IFormInterface|Layout|string
     */
    public function createStandardFilter(IFormInterface $Form, TblAccount $tblAccount, $Data = null, $Reset = null)
    {

        if ($Data === null && $Reset === null) {
            return $Form;
        }

        if ($Reset) {
            foreach ($Reset as $ResetName) {
                if (( $tblDynamicFilterList = $this->getDynamicFilterAllByName($ResetName, $tblAccount) )) {
                    foreach ($tblDynamicFilterList as $tblDynamicFilter) {
                        $this->destroyDynamicFilter($tblDynamicFilter);
                    }
                }
            }
        }

        if ($Data && $Reset) {
            $Data = array_merge($Data, $Reset);
        } elseif ($Reset) {
            $Data = $Reset;
        }

        $MissMatch = array();
        $Implement = array();
        if ($Data) {

        }
        foreach ($Data as $Name) {


            $Match = false;
            if ($Name === 'Adresse-Personen' && !$this->getDynamicFilterAllByName('Adresse-Personen', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Adresse-Personen', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetName');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetNumber');
                $this->addDynamicFilterOption($Mask, 'TblAddress_PostOfficeBox');
                $this->addDynamicFilterOption($Mask, 'TblAddress_County');
                $this->addDynamicFilterOption($Mask, 'TblAddress_Nation');
                $this->addDynamicFilterOption($Mask, 'TblCity_Code');
                $this->addDynamicFilterOption($Mask, 'TblCity_Name');
                $this->addDynamicFilterOption($Mask, 'TblCity_District');
                $this->addDynamicFilterOption($Mask, 'TblState_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblSalutation_Salutation');
                $this->addDynamicFilterOption($Mask, 'TblPerson_Title');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_SecondName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_BirthName');
                $Match = true;
            }

            if ($Name === 'Person-Adressen' && !$this->getDynamicFilterAllByName('Person-Adressen', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Person-Adressen', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_SecondName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_BirthName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetName');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetNumber');
                $this->addDynamicFilterOption($Mask, 'TblAddress_PostOfficeBox');
                $this->addDynamicFilterOption($Mask, 'TblAddress_Nation');
                $this->addDynamicFilterOption($Mask, 'TblCity_Code');
                $this->addDynamicFilterOption($Mask, 'TblCity_Name');
                $this->addDynamicFilterOption($Mask, 'TblCity_District');
                $Match = true;
            }

            if ($Name === 'Person-Personenbeziehung-Person' && !$this->getDynamicFilterAllByName('Person-Personenbeziehung-Person', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Person-Personenbeziehung-Person', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblSalutation_Salutation');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToPerson');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblSalutation_Salutation');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Match = true;
            }

            if ($Name === 'Person-Sorgeberechtigte-Adressen' && !$this->getDynamicFilterAllByName('Person-Sorgeberechtigte-Adressen', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Person-Sorgeberechtigte-Adressen', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipFromPerson');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblSalutation_Salutation');
                $this->addDynamicFilterOption($Mask, 'TblPerson_Title');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_SecondName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_BirthName');
                $Mask = $this->addDynamicFilterMask($Filter, 5, 'SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetName');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetNumber');
                $this->addDynamicFilterOption($Mask, 'TblAddress_PostOfficeBox');
                $this->addDynamicFilterOption($Mask, 'TblAddress_County');
                $this->addDynamicFilterOption($Mask, 'TblAddress_Nation');
                $this->addDynamicFilterOption($Mask, 'TblCity_Code');
                $this->addDynamicFilterOption($Mask, 'TblCity_Name');
                $this->addDynamicFilterOption($Mask, 'TblCity_District');
                $Match = true;
            }

            if ($Name === 'Firmen und Beziehungen' && !$this->getDynamicFilterAllByName('Firmen und Beziehungen', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Firmen und Beziehungen', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany');
                $this->addDynamicFilterOption($Mask, 'TblCompany_Name');
                $this->addDynamicFilterOption($Mask, 'TblCompany_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToCompany');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetName');
                $this->addDynamicFilterOption($Mask, 'TblAddress_StreetNumber');
                $this->addDynamicFilterOption($Mask, 'TblCity_Code');
                $this->addDynamicFilterOption($Mask, 'TblCity_Name');
                $this->addDynamicFilterOption($Mask, 'TblCity_District');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany');
                $this->addDynamicFilterOption($Mask, 'TblType_Name');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 5, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblSalutation_Salutation');
                $this->addDynamicFilterOption($Mask, 'TblPerson_Title');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_SecondName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_BirthName');
                $Match = true;
            }

            if ($Name === 'Schüler-Befreiung' && !$this->getDynamicFilterAllByName('Schüler-Befreiung', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Befreiung', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLiberation');
                $this->addDynamicFilterOption($Mask, 'TblStudentLiberationType_Name');
                $this->addDynamicFilterOption($Mask, 'TblStudentLiberationCategory_Name');
                $Match = true;
            }

            if ($Name === 'Schüler-Einverständnis' && !$this->getDynamicFilterAllByName('Schüler-Einverständnis', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Einverständnis', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentAgreement');
                $this->addDynamicFilterOption($Mask, 'TblStudentAgreementType_Name');
                $this->addDynamicFilterOption($Mask, 'TblStudentAgreementCategory_Name');
                $Match = true;
            }

            if ($Name === 'Schüler-Fehltage' && !$this->getDynamicFilterAllByName('Schüler-Fehltage', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Fehltage', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear');
                $this->addDynamicFilterOption($Mask, 'TblYear_Year');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision');
                $this->addDynamicFilterOption($Mask, 'TblLevel_Name');
                $this->addDynamicFilterOption($Mask, 'TblDivision_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence');
                $this->addDynamicFilterOption($Mask, 'TblAbsence_FromDate');
                $this->addDynamicFilterOption($Mask, 'TblAbsence_ToDate');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Match = true;
            }

            if ($Name === 'Schüler-Förderbedarf-Antrag' && !$this->getDynamicFilterAllByName('Schüler-Förderbedarf-Antrag', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Förderbedarf-Antrag', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentIntegration');
                $this->addDynamicFilterOption($Mask, 'TblStudentIntegration_CoachingRequestDate');
                $this->addDynamicFilterOption($Mask, 'TblStudentIntegration_CoachingCounselDate');
                $this->addDynamicFilterOption($Mask, 'TblStudentIntegration_CoachingDecisionDate');
                $this->addDynamicFilterOption($Mask, 'TblStudentIntegration_CoachingTime');
                $this->addDynamicFilterOption($Mask, 'TblStudentIntegration_CoachingRemark');
                $Match = true;
            }

            if ($Name === 'Schüler-Förderbedarf-Schwerpunkte' && !$this->getDynamicFilterAllByName('Schüler-Förderbedarf-Schwerpunkte', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Förderbedarf-Schwerpunkte', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentFocus');
                $this->addDynamicFilterOption($Mask, 'TblStudentFocusType_Name');
                $Match = true;
            }

            if ($Name === 'Schüler-Förderbedarf-Teilstörung' && !$this->getDynamicFilterAllByName('Schüler-Förderbedarf-Teilstörung', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Förderbedarf-Teilstörung', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentDisorder');
                $this->addDynamicFilterOption($Mask, 'TblStudentDisorderType_Name');
                $Match = true;
            }

            if ($Name === 'Schüler-Krankenakte' && !$this->getDynamicFilterAllByName('Schüler-Krankenakte', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Krankenakte', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentMedicalRecord');
                $this->addDynamicFilterOption($Mask, 'TblStudentMedicalRecord_Disease');
                $this->addDynamicFilterOption($Mask, 'TblStudentMedicalRecord_Medication');
                $this->addDynamicFilterOption($Mask, 'TblStudentMedicalRecord_Insurance');
                $Match = true;
            }

            if ($Name === 'Schüler-Schließfach' && !$this->getDynamicFilterAllByName('Schüler-Schließfach', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Schließfach', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentLocker');
                $this->addDynamicFilterOption($Mask, 'TblStudentLocker_KeyNumber');
                $this->addDynamicFilterOption($Mask, 'TblStudentLocker_LockerNumber');
                $this->addDynamicFilterOption($Mask, 'TblStudentLocker_LockerLocation');
                $Match = true;
            }

            if ($Name === 'Schüler-Taufe' && !$this->getDynamicFilterAllByName('Schüler-Taufe', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Taufe', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentBaptism');
                $this->addDynamicFilterOption($Mask, 'TblStudentBaptism_BaptismDate');
                $this->addDynamicFilterOption($Mask, 'TblStudentBaptism_Location');
                $Match = true;
            }

            if ($Name === 'Schüler-Transfer' && !$this->getDynamicFilterAllByName('Schüler-Transfer', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Transfer', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransfer');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransfer_TransferDate');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransfer_Remark');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransferType_Name');
                $Match = true;
            }

            if ($Name === 'Schüler-Transport' && !$this->getDynamicFilterAllByName('Schüler-Transport', $tblAccount)) {
                $Filter = $this->addDynamicFilter($tblAccount, 'Schüler-Transport', false);
                $Mask = $this->addDynamicFilterMask($Filter, 1, 'SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember');
                $this->addDynamicFilterOption($Mask, 'TblGroup_Name');
                $Mask = $this->addDynamicFilterMask($Filter, 2, 'SPHERE\Application\People\Person\Service\Entity\ViewPerson');
                $this->addDynamicFilterOption($Mask, 'TblPerson_FirstName');
                $this->addDynamicFilterOption($Mask, 'TblPerson_LastName');
                $Mask = $this->addDynamicFilterMask($Filter, 3, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudent');
                $this->addDynamicFilterOption($Mask, 'TblStudent_Identifier');
                $Mask = $this->addDynamicFilterMask($Filter, 4, 'SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransport');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransport_Route');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransport_StationEntrance');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransport_StationExit');
                $this->addDynamicFilterOption($Mask, 'TblStudentTransport_Remark');
                $Match = true;
            }
            if (!$Match) {
                $MissMatch[] = $Name;
            }
            if ($Match) {
                $Implement[] = $Name;
            }
        }
        if (empty( $MissMatch )) {
            return new Success('Standard-Auswertungen hinzugefügt')
            .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_SUCCESS);
        } else {
            return // $Form.
                new Layout(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Warning('Folgende Standard-Auswertungen konnten nicht erstellt werden:')
                                , 6),
                            new LayoutColumn(
                                new Success('Folgende Standard-Auswertungen wurden erstellt:')
                                , 6)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Warning(new Listing($MissMatch))
                                , 6),
                            new LayoutColumn(
                                new Success(new Listing($Implement))
                                , 6)
                        ))
                    ))
                );
//            .new Redirect('/Reporting/Dynamic', Redirect::TIMEOUT_WAIT);
        }
    }
}
