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
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

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
                    Debugger::screenDump($MaskFieldName);
                    $this->insertDynamicFilterOption(current($tblDynamicFilterMask), $MaskFieldName);
                }
            }
        }

        Debugger::screenDump( $tblDynamicFilter, $FilterFieldName );

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
}
