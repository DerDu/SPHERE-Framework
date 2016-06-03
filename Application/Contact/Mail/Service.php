<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Data;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType;
use SPHERE\Application\Contact\Mail\Service\Setup;
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
 * @package SPHERE\Application\Contact\Mail
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
     * @param integer $Id
     *
     * @return bool|TblMail
     */
    public function getMailById($Id)
    {

        return (new Data($this->getBinding()))->getMailById($Id);
    }

    /**
     * @return bool|TblMail[]
     */
    public function getMailAll()
    {

        return (new Data($this->getBinding()))->getMailAll();
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
    public function getMailAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getMailAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getMailAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getMailAllByCompany($tblCompany);
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createMailToPerson(
        IFormInterface $Form,
        TblPerson $tblPerson,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
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
            $tblMail = (new Data($this->getBinding()))->createMail($Address);

            if ((new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die E-Mail Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblPerson->getId()));
            } else {
                return new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', Redirect::TIMEOUT_ERROR, array('Id' => $tblPerson->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Address
     * @param TblType   $tblType
     * @param           $Remark
     *
     * @return TblToPerson
     */
    public function insertMailToPerson(
        TblPerson $tblPerson,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        return (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Remark);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param TblType $tblType
     * @param $Remark
     *
     * @return TblToCompany
     */
    public function insertMailToCompany(
        TblCompany $tblCompany,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        return (new Data($this->getBinding()))->addMailToCompany($tblCompany, $tblMail, $tblType, $Remark);
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
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createMailToCompany(
        IFormInterface $Form,
        TblCompany $tblCompany,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
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
            $tblMail = (new Data($this->getBinding()))->createMail($Address);

            if ((new Data($this->getBinding()))->addMailToCompany($tblCompany, $tblMail, $tblType, $Type['Remark'])
            ) {
                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .  ' Die E-Mail Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/Corporation/Company', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateMailToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
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
            $tblMail = (new Data($this->getBinding()))->createMail($Address);
            // Remove current
            (new Data($this->getBinding()))->removeMailToPerson($tblToPerson);

            if ($tblToPerson->getServiceTblPerson()) {
                // Add new
                if ((new Data($this->getBinding()))->addMailToPerson($tblToPerson->getServiceTblPerson(), $tblMail,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die E-Mail Adresse wurde erfolgreich geändert')
                    . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht geändert werden')
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
     * @param string         $Address
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateMailToCompany(
        IFormInterface $Form,
        TblToCompany $tblToCompany,
        $Address,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Address) {
            return $Form;
        }

        $Error = false;

        $Address = filter_var($Address, FILTER_VALIDATE_EMAIL);

        if (isset( $Address ) && empty( $Address )) {
            $Form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
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
            $tblMail = (new Data($this->getBinding()))->createMail($Address);
            // Remove current
            (new Data($this->getBinding()))->removeMailToCompany($tblToCompany);

            if ($tblToCompany->getServiceTblCompany()) {
                // Add new
                if ((new Data($this->getBinding()))->addMailToCompany($tblToCompany->getServiceTblCompany(), $tblMail,
                    $tblType, $Type['Remark'])
                ) {
                    return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die E-Mail Adresse wurde erfolgreich geändert')
                    . new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
                } else {
                    return new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht geändert werden')
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
    public function getMailToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getMailToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getMailToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getMailToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removeMailToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->getBinding()))->removeMailToPerson($tblToPerson);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeMailToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removeMailToCompany($tblToCompany);
    }
}
