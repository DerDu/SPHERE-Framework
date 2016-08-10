<?php
namespace SPHERE\Application\Billing\Accounting\SchoolAccount;

use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Data;
use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Entity\TblSchoolAccount;
use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\SchoolAccount
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
     * @param $Id
     *
     * @return false|TblSchoolAccount
     */
    public function getSchoolAccountById($Id)
    {

        return (new Data($this->getBinding()))->getSchoolAccountById($Id);
    }

    /**
     * @return false|TblSchoolAccount[]
     */
    public function getSchoolAccountAll()
    {

        return (new Data($this->getBinding()))->getSchoolAccountAll();
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return false|TblSchoolAccount
     */
    public function getSchoolAccountByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return (new Data($this->getBinding()))->getSchoolAccountByCompanyAndType($tblCompany, $tblType);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Account
     *
     * @return IFormInterface|string
     */
    public function createSchoolAccount(IFormInterface &$Stage = null, $Account = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Account) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Account['BankName'] ) && empty( $Account['BankName'] )) {
            $Stage->setError('Account[BankName]', 'Bitte geben Sie den Namen der Bank an');
            $Error = true;
        }
        if (isset( $Account['Owner'] ) && empty( $Account['Owner'] )) {
            $Stage->setError('Account[Owner]', 'Bitte geben Sie den Besitzer an');
            $Error = true;
        }
        if (isset( $Account['IBAN'] ) && empty( $Account['IBAN'] )) {
            $Stage->setError('Account[IBAN]', 'Bitte geben Sie die IBAN an');
            $Error = true;
        }
        if (isset( $Account['BIC'] ) && empty( $Account['BIC'] )) {
            $Stage->setError('Account[BIC]', 'Bitte geben Sie die BIC an');
            $Error = true;
        }
        if (!( $tblSchool = School::useService()->getSchoolById($Account['School']) )) {
            $Stage->setError('Account[School]', 'Bitte geben Sie eine Schule an');
            $Error = true;
        }

        if (!$Error) {
            $tblCompany = $tblSchool->getServiceTblCompany();
            $tblType = $tblSchool->getServiceTblType();

            if ((new Data($this->getBinding()))->createSchoolAccount($tblCompany, $tblType, $Account['BankName'], $Account['Owner'],
                $Account['IBAN'], $Account['BIC'])
            ) {
                return new Success('Kontoinformationen gespeichert')
                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Kontoinformationen konnten nicht gespeichert werden')
                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_ERROR);
            }
        }

        return $Stage;
    }

    /**
     * @param IFormInterface   $Stage
     * @param TblSchoolAccount $tblSchoolAccount
     * @param null             $Account
     *
     * @return IFormInterface|string
     */
    public function updateSchoolAccount(IFormInterface &$Stage, TblSchoolAccount $tblSchoolAccount, $Account = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Account) {
            return $Stage;
        }
        $Error = false;
        if (isset( $Account['BankName'] ) && empty( $Account['BankName'] )) {
            $Stage->setError('Account[BankName]', 'Bitte geben Sie den Namen der Bank an');
            $Error = true;
        }
        if (isset( $Account['Owner'] ) && empty( $Account['Owner'] )) {
            $Stage->setError('Account[Owner]', 'Bitte geben Sie den Besitzer an');
            $Error = true;
        }
        if (isset( $Account['IBAN'] ) && empty( $Account['IBAN'] )) {
            $Stage->setError('Account[IBAN]', 'Bitte geben Sie die IBAN an');
            $Error = true;
        }
        if (isset( $Account['BIC'] ) && empty( $Account['BIC'] )) {
            $Stage->setError('Account[BIC]', 'Bitte geben Sie die BIC an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateSchoolAccount($tblSchoolAccount, $Account['BankName'], $Account['Owner'],
                $Account['IBAN'], $Account['BIC'])
            ) {
                return new Success('Änderungen sind erfasst')
                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Änderungen am Konto konnten nicht vorgenommen werden')
                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param TblSchoolAccount $tblSchoolAccount
     *
     * @return bool
     */
    public function destroySchoolAccount(TblSchoolAccount $tblSchoolAccount)
    {

        return (new Data($this->getBinding()))->destroySchoolAccount($tblSchoolAccount);
    }

}
