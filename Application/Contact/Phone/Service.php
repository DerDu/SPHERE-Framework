<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Data;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\Contact\Phone\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Phone
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

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return false|ViewPhoneToPerson[]
     */
    public function viewPhoneToPerson()
    {

        return ( new Data($this->getBinding()) )->viewPhoneToPerson();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPhone
     */
    public function getPhoneById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneById($Id);
    }

    /**
     * @return bool|TblPhone[]
     */
    public function getPhoneAll()
    {

        return (new Data($this->getBinding()))->getPhoneAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPhoneAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getPhoneAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getPhoneAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getPhoneAllByCompany($tblCompany);
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return string
     */
    public function getPhoneTypeShort(TblToPerson $tblToPerson)
    {

        $tblType = $tblToPerson->getTblType();
        if ($tblType) {
            if ($tblType->getName() == 'Privat') {
                return 'p.';
            } elseif ($tblType->getName() == 'Geschäftlich') {
                return 'd.';
            } elseif ($tblType->getName() == 'Notfall') {
                return 'N.';
            } elseif ($tblType->getName() == 'Fax') {
                return 'F.';
            }
        }
        return '';
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createPhoneToPerson(
        IFormInterface $Form,
        TblPerson $tblPerson,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblPhone = (new Data($this->getBinding()))->createPhone($Number);

            if ((new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()));
            } else {
                return new Danger(new Ban() . ' Die Telefonnummer konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Number
     * @param TblType   $tblType
     *
     * @param           $Remark
     */
    public function insertPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        TblType $tblType,
        $Remark
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        (new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Remark);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Number
     * @param TblType $tblType
     * @param $Remark
     */
    public function insertPhoneToCompany(
        TblCompany $tblCompany,
        $Number,
        TblType $tblType,
        $Remark
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        (new Data($this->getBinding()))->addPhoneToCompany($tblCompany, $tblPhone, $tblType, $Remark);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createPhoneToCompany(
        IFormInterface $Form,
        TblCompany $tblCompany,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblPhone = (new Data($this->getBinding()))->createPhone($Number);

            if ((new Data($this->getBinding()))->addPhoneToCompany($tblCompany, $tblPhone, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde erfolgreich hinzugefügt')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger(new Ban() . ' Die Telefonnummer konnte nicht hinzugefügt werden')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updatePhoneToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
            // Remove current
            (new Data($this->getBinding()))->removePhoneToPerson($tblToPerson);

            if ($tblToPerson->getServiceTblPerson()) {
                // Add new
                if ((new Data($this->getBinding()))->addPhoneToPerson($tblToPerson->getServiceTblPerson(), $tblPhone,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde erfolgreich geändert')
                    . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die Telefonnummer konnte nicht geändert werden')
                    . new Redirect('/People/Person', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
                }
            } else {
                return new Danger('Person nicht gefunden', new Ban());
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToCompany   $tblToCompany
     * @param string         $Number
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updatePhoneToCompany(
        IFormInterface $Form,
        TblToCompany $tblToCompany,
        $Number,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Number) {
            return $Form;
        }

        $Error = false;

        if (isset( $Number ) && empty( $Number )) {
            $Form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $Form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $Error = true;
        } else {
            $Form->setSuccess('Type[Type]');
        }

        if (!$Error) {
            $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
            // Remove current
            (new Data($this->getBinding()))->removePhoneToCompany($tblToCompany);

            if ($tblToCompany->getServiceTblCompany()) {
                // Add new
                if ((new Data($this->getBinding()))->addPhoneToCompany($tblToCompany->getServiceTblCompany(), $tblPhone,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde erfolgreich geändert')
                    . new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die Telefonnummer konnte nicht geändert werden')
                    . new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
                }
            } else {
                return new Danger('Firma nicht gefunden', new Ban());
            }
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getPhoneToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getPhoneToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePhoneToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removePhoneToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removePhoneToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removePhoneToCompany($tblToCompany);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeSoftPhoneAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblPhoneToPersonList = $this->getPhoneAllByPerson($tblPerson))){
            foreach($tblPhoneToPersonList as $tblToPerson){
                $this->removePhoneToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }
}
