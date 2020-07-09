<?php
namespace SPHERE\Application\Corporation\Group;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Data;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblMember;
use SPHERE\Application\Corporation\Group\Service\Entity\ViewCompanyGroupMember;
use SPHERE\Application\Corporation\Group\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Corporation\Group
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewCompanyGroupMember[]
     */
    public function viewCompanyGroupMember()
    {

        return ( new Data($this->getBinding()) )->viewCompanyGroupMember();
    }

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
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return (new Data($this->getBinding()))->getGroupAll();
    }

    /**
     * Sortierung erst feste Gruppen, dann individuelle Gruppen
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllSorted()
    {

        $lockedList = array();
        $customList = array();
        $tblGroupAll = $this->getGroupAll();
        if ($tblGroupAll) {
            foreach ($tblGroupAll as $tblGroup) {
                if ($tblGroup->isLocked()) {
                    $lockedList[$tblGroup->getId()] = $tblGroup;
                } else {
                    $customList[$tblGroup->getId()] = $tblGroup;
                }
            }
        }

        $lockedList = $this->getSorter($lockedList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());
        $customList = $this->getSorter($customList)->sortObjectBy('Name', new Sorter\StringNaturalOrderSorter());

        return array_merge($lockedList, $customList);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return (new Data($this->getBinding()))->getGroupById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param array          $Group
     *
     * @return IFormInterface|Redirect
     */
    public function createGroup(IFormInterface $Form = null, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        } else {
            if ($this->getGroupByName($Group['Name'])) {
                $Form->setError('Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->createGroup(
                $Group['Name'], $Group['Description'], $Group['Remark']
            )
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Gruppe wurde erfolgreich erstellt')
                .new Redirect('/Corporation/Group', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban() . ' Die Gruppe konnte nicht erstellt werden')
                .new Redirect('/Corporation/Group', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param $Group
     *
     * @return TblGroup
     */
    public function createGroupFromImport($Group)
    {

        return (new Data($this->getBinding()))->createGroup($Group, '', '');
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName($Name)
    {

        return (new Data($this->getBinding()))->getGroupByName($Name);
    }

    /**
     * @param string $MetaTable
     *
     * @return bool|TblGroup
     */
    public function getGroupByMetaTable($MetaTable)
    {

        return (new Data($this->getBinding()))->getGroupByMetaTable($MetaTable);
    }

    /**
     * @param IFormInterface $Form
     * @param TblGroup       $tblGroup
     * @param array          $Group
     *
     * @return IFormInterface|Redirect
     */
    public function updateGroup(IFormInterface $Form = null, TblGroup $tblGroup, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Group) {
            return $Form;
        }

        $Error = false;

        if (isset( $Group['Name'] ) && empty( $Group['Name'] )) {
            $Form->setError('Group[Name]', 'Bitte geben Sie einen Namen für die Gruppe an');
            $Error = true;
        } else {
            $tblGroupTwin = $this->getGroupByName($Group['Name']);
            if ($tblGroupTwin && $tblGroupTwin->getId() != $tblGroup->getId()) {
                $Form->setError('Group[Name]', 'Bitte geben Sie einen eineindeutigen Namen für die Gruppe an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateGroup(
                $tblGroup, $Group['Name'], $Group['Description'], $Group['Remark']
            )
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Änderungen wurden erfolgreich gespeichert')
                .new Redirect('/Corporation/Group', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban() . ' Die Änderungen konnte nicht gespeichert werden')
                .new Redirect('/Corporation/Group', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCompany[]
     */
    public function getCompanyAllByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->getCompanyAllByGroup($tblGroup);
    }


    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAllHavingNoGroup()
    {

        return (new Data($this->getBinding()))->getCompanyAllHavingNoGroup();
    }

    /**
     * @deprecated countCompanyAllByGroup -> countMemberAllByGroup
     *             
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countCompanyAllByGroup(TblGroup $tblGroup)
    {

        return $this->countMemberAllByGroup($tblGroup);
    }

    /**
     * @deprecated use countMemberByGroup
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countMemberAllByGroup(TblGroup $tblGroup)
    {

        return $this->countEntityList((new Data($this->getBinding()))->getMemberAllByGroup($tblGroup));
    }

    /**
     * @param TblGroup $tblGroup
     * @return int
     */
    public function countMemberByGroup(TblGroup $tblGroup)
    {
        return (new Data($this->getBinding()))->countMemberByGroup($tblGroup);
    }

    /**
     *
     * @param TblCompany $tblCompany
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getGroupAllByCompany($tblCompany);
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->removeGroupCompany($tblGroup, $tblCompany);
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return TblMember
     */
    public function addGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->addGroupCompany($tblGroup, $tblCompany);
    }

    /**
     * @param TblGroup $tblGroup
     * @param bool     $IsForced
     *
     * @return false|TblMember[]
     */
    public function getMemberAllByGroup(TblGroup $tblGroup, $IsForced = false)
    {
        return (new Data($this->getBinding()))->getMemberAllByGroup($tblGroup, ( $IsForced ? $IsForced : null ));
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblMember[]
     */
    public function getMemberAllByCompany(TblCompany $tblCompany)
    {
        return (new Data($this->getBinding()))->getMemberAllByCompany($tblCompany);
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function removeMember(TblMember $tblMember)
    {

        return (new Data($this->getBinding()))->removeMember($tblMember);
    }

    /**
     * @param TblMember $tblMember
     *
     * @return bool
     */
    public function destroyMember(TblMember $tblMember)
    {

        return (new Data($this->getBinding()))->destroyMember($tblMember);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool
     */
    public function destroyGroup(TblGroup $tblGroup)
    {

        $tblMemberList = Group::useService()->getMemberAllByGroup($tblGroup, true);
        if ($tblMemberList) {
            foreach ($tblMemberList as $tblMember) {
                Group::useService()->destroyMember($tblMember);
            }
        }

        return (new Data($this->getBinding()))->destroyGroup($tblGroup);
    }

    /**
     * @param TblGroup $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return bool|TblMember
     */
    public function existsGroupCompany(TblGroup $tblGroup, TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->existsGroupCompany($tblGroup, $tblCompany);
    }
}
