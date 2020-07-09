<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Data;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Mail\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
     * @return false|ViewMailToPerson[]
     */
    public function viewMailToPerson()
    {

        return ( new Data($this->getBinding()) )->viewMailToPerson();
    }

    /**
     * @return false|ViewMailToCompany[]
     */
    public function viewMailToCompany()
    {

        return ( new Data($this->getBinding()) )->viewMailToCompany();
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
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getMailAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getMailAllByPerson($tblPerson, $isForced);
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
     * @param TblPerson $tblPerson
     * @param $Address
     * @param $Type
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormMailToPerson(
        TblPerson $tblPerson,
        $Address,
        $Type,
        TblToPerson $tblToPerson = null,
        $Alias = null
    ) {

        $error = false;

        $form = Mail::useFrontend()->formAddressToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }
        if($Alias !== null){
            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
                /** @var TblAccount $tblAccount */
                $tblAccount = current($tblAccountList);
                // prüfen ob Alias eineindeutig ist
                if (($tblAccountList = Account::useService()->getAccountAllByUserAlias($Address))) {
                    foreach ($tblAccountList as $item) {
                        if ($tblAccount->getId() != $item->getId()) {
                            if($tblAccount->getServiceTblConsumer()->getId() == $item->getServiceTblConsumer()->getId()){
                                $PersonString = 'Person nicht gefunden';
                                if(($tblPersonList = Account::useService()->getPersonAllByAccount($item))){
                                    $foundPerson = current($tblPersonList);
                                    /** @var TblPerson $foundPerson */
                                    $PersonString = $foundPerson->getLastFirstName();
                                }
                                $form->setError('Alias', 'E-Mail Adresse wird bereits verwendet. ('.$item->getUsername().' - '.$PersonString.')');
                            } else {
                                $form->setError('Alias', 'E-Mail Adresse wird bereits verwendet.');
                            }
                            $error = true;
                            break;
                        }
                    }
                }
            }

        }

        return $error ? $form : false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormMailToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type,
        TblToCompany $tblToCompany = null
    ) {

        $error = false;

        $form = Mail::useFrontend()->formAddressToCompany($tblCompany->getId(), $tblToCompany ? $tblToCompany->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Address
     * @param           $Type
     * @param bool      $IsAccountUserAlias
     * @param string    $ErrorString
     *
     * @return bool
     */
    public function createMailToPerson(
        TblPerson $tblPerson,
        $Address,
        $Type,
        $IsAccountUserAlias = false,
        &$ErrorString = ''
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblMail) {
            return false;
        }

        if (($tblToPerson = (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Type['Remark']))) {
            if($IsAccountUserAlias){
                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
                    $tblAccount = current($tblAccountList);
                    // remove existing entry's
                    if(($tblToPersonList = Mail::useService()->getMailAllByPerson($tblPerson))){
                        foreach($tblToPersonList as $tblToPersonTemp){
                            if($tblToPerson->getId() != $tblToPersonTemp->getId() && $tblToPersonTemp->isAccountUserAlias()){
                                Account::useService()->changeUserAlias($tblAccount,'');
                                Mail::useService()->updateMailToPersonAlias($tblToPersonTemp, false);
                            }
                        }
                    }

                    // prüfen ob Alias eineindeutig ist
                    if (($tblAccountList = Account::useService()->getAccountAllByUserAlias($Address))) {
                        foreach ($tblAccountList as $item) {
                            if ($tblAccount->getId() != $item->getId()) {
                                if($tblAccount->getServiceTblConsumer()->getId() == $item->getServiceTblConsumer()->getId()){
                                    $PersonString = 'Person nicht gefunden';
                                    if(($tblPersonList = Account::useService()->getPersonAllByAccount($item))){
                                        $foundPerson = current($tblPersonList);
                                        /** @var TblPerson $foundPerson */
                                        $PersonString = $foundPerson->getFirstName().', '.$foundPerson->getLastName();
                                    }
                                    $ErrorString = 'E-Mail '.new Bold($Address).' bereits verwendet. ('.($item->getUsername().' - '.$PersonString.')');
                                } else {
                                    $ErrorString = 'E-Mail '.new Bold($Address).' bereits verwendet.';
                                }
                            }
                        }
                    }
                    if((Account::useService()->changeUserAlias($tblAccount, $Address))){
                        Mail::useService()->updateMailToPersonAlias($tblToPerson, $IsAccountUserAlias);
                    }
                } else {
                    $ErrorString = 'Person hat keinen Benutzeraccount';
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function createMailToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblMail) {
            return false;
        }

        if ((new Data($this->getBinding()))->addMailToCompany($tblCompany, $tblMail, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Address
     * @param TblType $tblType
     * @param           $Remark
     * @param bool $IsUserAlias
     *
     * @return TblToPerson
     */
    public function insertMailToPerson(
        TblPerson $tblPerson,
        $Address,
        TblType $tblType,
        $Remark,
        $IsUserAlias = false
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        // falls die Emailadresse bereits vorhanden ist, diese überschreiben
        if (($tblToPersonList = $this->getMailAllByPerson($tblPerson))) {
            foreach ($tblToPersonList as $tblToPerson) {
                if (($tblMailTemp = $tblToPerson->getTblMail())
                    && $tblMail->getId() == $tblMailTemp->getId()
                ) {
                    return (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail, $tblType, $Remark, $IsUserAlias);
                }
            }
        }

        return (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Remark, $IsUserAlias);
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
     * @param TblToPerson $tblToPerson
     * @param             $Address
     * @param             $Type
     * @param bool        $IsAccountUserAlias
     * @param string      $ErrorString
     *
     * @return bool
     */
    public function updateMailToPerson(
        TblToPerson $tblToPerson,
        $Address,
        $Type,
        $IsAccountUserAlias = false,
        &$ErrorString = ''
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
//        // Remove current
//        (new Data($this->getBinding()))->removeMailToPerson($tblToPerson);

        if ($tblToPerson->getServiceTblPerson()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            if(!$IsAccountUserAlias){
                if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblToPerson->getServiceTblPerson()))){
                    $tblAccount = current($tblAccountList);
                    Account::useService()->changeUserAlias($tblAccount, '');
                }
                return (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail,
                    $tblType, $Type['Remark'], $IsAccountUserAlias);
            }
            // update
            if ((new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail,
                $tblType, $Type['Remark'], $IsAccountUserAlias)
            ){
                $this->updateAlias($tblToPerson, $Address, $ErrorString);
                if($ErrorString){
                    // bei Fehlern den Aliasflag entfernen
                    (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail,
                        $tblType, $Type['Remark'], false);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param string      $Alias
     * @param string      $ErrorString
     *
     * @return bool
     */
    private function updateAlias(TblToPerson $tblToPerson, $Alias, &$ErrorString)
    {
        $isAlias = false;
        if($Alias){
            $isAlias = true;
        }

        if (($tblPerson = $tblToPerson->getServiceTblPerson())
            && ($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))){
            $tblAccount = current($tblAccountList);
            // remove existing entry's
            if(($tblToPersonList = Mail::useService()->getMailAllByPerson($tblToPerson->getServiceTblPerson()))){
                foreach($tblToPersonList as $tblToPersonTemp){
                    if($tblToPerson->getId() != $tblToPersonTemp->getId() && $tblToPersonTemp->isAccountUserAlias()){
                        Account::useService()->changeUserAlias($tblAccount,'');
                        Mail::useService()->updateMailToPersonAlias($tblToPersonTemp, false);
                    }
                }
            }
            // prüfen ob Alias eineindeutig ist
            if (($tblAccountList = Account::useService()->getAccountAllByUserAlias($Alias))){
                foreach ($tblAccountList as $item) {
                    if ($tblAccount->getId() != $item->getId()){
                        if ($tblAccount->getServiceTblConsumer()->getId() == $item->getServiceTblConsumer()->getId()){
                            $PersonString = 'Person nicht gefunden';
                            if (($tblPersonList = Account::useService()->getPersonAllByAccount($item))){
                                $foundPerson = current($tblPersonList);
                                /** @var TblPerson $foundPerson */
                                $PersonString = $foundPerson->getFirstName().', '.$foundPerson->getLastName();
                            }
                            $ErrorString = 'E-Mail '.new Bold($Alias).' bereits verwendet. ('.($item->getUsername().' - '.$PersonString.')');
                        } else {
                            $ErrorString = 'E-Mail '.new Bold($Alias).' bereits verwendet.';
                        }
                    }
                }
            }
            if ((Account::useService()->changeUserAlias($tblAccount, $Alias))){
                return Mail::useService()->updateMailToPersonAlias($tblToPerson, $isAlias);
            }
        } else {
            $ErrorString = 'Person hat keinen Benutzeraccount';
        }
        return $ErrorString;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsAccountUserAlias
     *
     * @return bool
     */
    public function updateMailToPersonAlias(
        TblToPerson $tblToPerson,
        $IsAccountUserAlias = false
    ) {

        if ((new Data($this->getBinding()))->updateMailToPersonAlias($tblToPerson, $IsAccountUserAlias)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param $Address
     * @param TblType $tblType
     * @param $Remark
     * @param bool $IsAccountUserAlias
     *
     * @return bool
     */
    public function updateMailToPersonService(
        TblToPerson $tblToPerson,
        $Address,
        TblType $tblType,
        $Remark,
        $IsAccountUserAlias = false
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if ($tblToPerson->getServiceTblPerson()) {
            return (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail, $tblType, $Remark, $IsAccountUserAlias);
        } else {
            return false;
        }
    }


    /**
     * @param TblToCompany $tblToCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function updateMailToCompany(
        TblToCompany $tblToCompany,
        $Address,
        $Type
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        // Remove current
        (new Data($this->getBinding()))->removeMailToCompany($tblToCompany);

        if ($tblToCompany->getServiceTblCompany()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addMailToCompany($tblToCompany->getServiceTblCompany(), $tblMail,
                $tblType, $Type['Remark'])
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
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
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeMailToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        if($tblToPerson->isAccountUserAlias()){
            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblToPerson->getServiceTblPerson()))){
                $tblAccount = current($tblAccountList);
                Account::useService()->changeUserAlias($tblAccount, '');
            }
        }
        return (new Data($this->getBinding()))->removeMailToPerson($tblToPerson, $IsSoftRemove);
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

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeSoftMailAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblMailToPersonList = $this->getMailAllByPerson($tblPerson))){
            foreach($tblMailToPersonList as $tblToPerson){
                $this->removeMailToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->getBinding()))->restoreToPerson($tblToPerson);
    }
}
