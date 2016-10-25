<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Data;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Person\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Person
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPerson[]
     */
    public function viewPerson()
    {

        return (new Data($this->getBinding()))->viewPerson();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        return (new Data($this->getBinding()))->getSalutationAll();
    }

    /**
     * int
     */
    public function countPersonAll()
    {

        return (new Data($this->getBinding()))->countPersonAll();
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return (new Data($this->getBinding()))->getPersonAll();
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countPersonAllByGroup(TblGroup $tblGroup)
    {

        return Group::useService()->countMemberAllByGroup($tblGroup);
    }

    /**
     * @param IFormInterface|null $Form
     * @param $Person
     *
     * @return IFormInterface|string
     */
    public function createPerson(IFormInterface $Form = null, $Person)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $Error = true;
        }

        if (!$Error) {

            if (( $tblPerson = (new Data($this->getBinding()))->createPerson(
                $this->getSalutationById($Person['Salutation']), $Person['Title'], $Person['FirstName'],
                $Person['SecondName'], $Person['LastName'], $Person['BirthName']) )
            ) {
                // Add to Group
                if (isset( $Person['Group'] )) {
                    foreach ((array)$Person['Group'] as $tblGroup) {
                        Group::useService()->addGroupPerson(
                            Group::useService()->getGroupById($tblGroup), $tblPerson
                        );
                    }
                }
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde erfolgreich erstellt')
                .new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId())
                );
            } else {
                return new Danger(new Ban() . ' Die Person konnte nicht erstellt werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        return (new Data($this->getBinding()))->getSalutationById($Id);
    }

    /**
     * @param        $Salutation
     * @param        $Title
     * @param        $FirstName
     * @param        $SecondName
     * @param        $LastName
     * @param        $GroupList
     * @param string $BirthName
     *
     * @return bool|TblPerson
     */
    public function insertPerson($Salutation, $Title, $FirstName, $SecondName, $LastName, $GroupList, $BirthName = '')
    {

        if (( $tblPerson = (new Data($this->getBinding()))->createPerson(
            $Salutation, $Title, $FirstName, $SecondName, $LastName, $BirthName) )
        ) {
            // Add to Group
            if (!empty( $GroupList )) {
                foreach ($GroupList as $tblGroup) {
                    Group::useService()->addGroupPerson(
                        Group::useService()->getGroupById($tblGroup), $tblPerson
                    );
                }
            }
            return $tblPerson;
        } else {
            return false;
        }
    }

    /**
     * @param $Id
     * @param bool $IsForced
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id, $IsForced = false)
    {

        return (new Data($this->getBinding()))->getPersonById($Id, $IsForced);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param $Person
     * @param $Group
     *
     * @return IFormInterface|string
     */
    public function updatePerson(IFormInterface $Form = null, TblPerson $tblPerson, $Person, $Group)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Person) {
            return $Form;
        }

        $Error = false;

        if (isset( $Person['FirstName'] ) && empty( $Person['FirstName'] )) {
            $Form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $Error = true;
        }
        if (isset( $Person['LastName'] ) && empty( $Person['LastName'] )) {
            $Form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updatePerson($tblPerson, $Person['Salutation'], $Person['Title'],
                $Person['FirstName'], $Person['SecondName'], $Person['LastName'], $Person['BirthName'])
            ) {
                // Change Groups
                if (isset( $Person['Group'] )) {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
                    }
                    // Add current Groups
                    foreach ((array)$Person['Group'] as $tblGroup) {
                        Group::useService()->addGroupPerson(
                            Group::useService()->getGroupById($tblGroup), $tblPerson
                        );
                    }
                } else {
                    // Remove all Groups
                    $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                    foreach ($tblGroupList as $tblGroup) {
                        Group::useService()->removeGroupPerson($tblGroup, $tblPerson);
                    }
                }
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde erfolgreich aktualisiert')
                .new Redirect(null, Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger(new Ban() . 'Die Person konnte nicht aktualisiert werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Form;
    }

    /**
     * @param array $IdArray of TblPerson->Id
     *
     * @return TblPerson[]
     */
    public function fetchPersonAllByIdList($IdArray)
    {

        return (new Data($this->getBinding()))->fetchPersonAllByIdList($IdArray);
    }

    /**
     * @param $FirstName
     * @param $LastName
     * @param $ZipCode
     * @return bool|TblPerson
     */
    public function  existsPerson($FirstName, $LastName, $ZipCode)
    {

        $exists = false;

        if (( $persons = (new Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName) )
        ) {
            foreach ($persons as $person) {
                if (( $addresses = Address::useService()->getAddressAllByPerson($person) )) {
                    if ($addresses[0]->getTblAddress()->getTblCity()->getCode() == $ZipCode) {
                        $exists = $person;
                    }
                }
            }
        }

        return $exists;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function destroyPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->destroyPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function softRemovePersonReferences(TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->softRemovePersonReferences($tblPerson);
    }
}
