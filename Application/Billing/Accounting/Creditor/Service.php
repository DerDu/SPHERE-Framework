<?php
namespace SPHERE\Application\Billing\Accounting\Creditor;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Data;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Creditor
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
     * @param $Id
     *
     * @return false|TblCreditor
     */
    public function getCreditorById($Id)
    {

        return (new Data($this->getBinding()))->getCreditorById($Id);
    }

    /**
     * @return false|TblCreditor[]
     */
    public function getCreditorAll()
    {

        return (new Data($this->getBinding()))->getCreditorAll();
    }

    /**
     * @param string $Owner
     * @param string $Street
     * @param string $Number
     * @param string $Code
     * @param string $City
     * @param string $District
     * @param string $CreditorId
     * @param string $BankName
     * @param string $IBAN
     * @param string $BIC
     *
     * @return null|object|TblCreditor
     */
    public function createCreditor($Owner = '', $Street = '', $Number = '', $Code = '', $City = '', $District = ''
        , $CreditorId = '', $BankName = '', $IBAN = '', $BIC = ''
    ){

        $IBAN = str_replace(' ', '', $IBAN);
        $BIC = str_replace(' ', '', $BIC);
        $IBAN = strtoupper($IBAN);
        return (new Data($this->getBinding()))->createCreditor($Owner, $Street, $Number, $Code, $City, $District,
            $CreditorId
            , $BankName, $IBAN, $BIC);
    }

    /**
     * @param TblCreditor $tblCreditor
     * @param string      $Owner
     * @param string      $Street
     * @param string      $Number
     * @param string      $Code
     * @param string      $City
     * @param string      $District
     * @param string      $CreditorId
     * @param string      $BankName
     * @param string      $IBAN
     * @param string      $BIC
     *
     * @return bool
     */
    public function changeCreditor(TblCreditor $tblCreditor, $Owner = '', $Street = '', $Number = '', $Code = '',
        $City = '', $District = ''
        , $CreditorId = '', $BankName = '', $IBAN = '', $BIC = ''
    ){

        $IBAN = str_replace(' ', '', $IBAN);
        $BIC = str_replace(' ', '', $BIC);
        $IBAN = strtoupper($IBAN);
        return (new Data($this->getBinding()))->updateCreditor($tblCreditor, $Owner, $Street, $Number, $Code, $City,
            $District, $CreditorId
            , $BankName, $IBAN, $BIC);
    }

    /**
     * @param TblCreditor $tblCreditor
     *
     * @return bool
     */
    public function removeCreditor(TblCreditor $tblCreditor)
    {
        return (new Data($this->getBinding()))->removeCreditor($tblCreditor);
    }
}
