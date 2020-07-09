<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service;

use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Bookkeeping\Basket\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createBasketType(TblBasketType::IDENT_ABRECHNUNG, '');
        $this->createBasketType(TblBasketType::IDENT_AUSZAHLUNG, '');
        $this->createBasketType(TblBasketType::IDENT_GUTSCHRIFT, '');
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketType', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblBasketVerification', $Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblBasketType
     */
    public function getBasketTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketType', array(
            TblBasketType::ATTR_NAME => $Name
        ));
    }

    /**
     * @param string      $Name
     * @param string|bool $Month
     * @param string|bool $Year
     *
     * @return false|TblBasket
     */
    public function getBasketByName($Name, $Month = false, $Year = false)
    {

        if($Month && $Year){
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket', array(
                TblBasket::ATTR_NAME  => $Name,
                TblBasket::ATTR_MONTH => $Month,
                TblBasket::ATTR_YEAR  => $Year,
            ));
        }
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket', array(
            TblBasket::ATTR_NAME => $Name
        ));
    }

    /**
     * @param bool $IsArchive
     *
     * @return bool|TblBasket[]
     */
    public function getBasketAll($IsArchive = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasket', array(
            TblBasket::ATTR_IS_ARCHIVE => $IsArchive
        ));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem',
            array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblBasketItem[]
     */
    public function getBasketItemAllByItem(TblItem $tblItem)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem',
            array(TblBasketItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketItem[]
     */
    public function getBasketItemByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketItem',
            array(
                TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()
            ));
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByBasket(TblBasket $tblBasket)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblBasketVerification',
            array(TblBasketVerification::ATTR_TBL_BASKET => $tblBasket->getId()));
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByBankReference(TblBankReference $tblBankReference)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification',
            array(TblBasketVerification::ATTR_SERVICE_TBL_BANK_REFERENCE => $tblBankReference->getId())
        );
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationAllByDebtorSelection(TblDebtorSelection $tblDebtorSelection)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBasketVerification',
            array(TblBasketVerification::ATTR_SERVICE_TBL_DEBTOR_SELECTION => $tblDebtorSelection->getId())
        );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|Element
     */
    public function countDebtorSelectionCountByBasket(TblBasket $tblBasket)
    {

        $Count = $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblBasketVerification',
            array(TblBasketVerification::ATTR_TBL_BASKET => $tblBasket->getId()));

        return $Count;
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblBasketType
     */
    public function createBasketType(
        $Name,
        $Description
    ){

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasketType')->findOneBy(array(
            TblBasketType::ATTR_NAME  => $Name,
        ));

        if(null === $Entity){
            $Entity = new TblBasketType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasket               $tblBasket
     * @param TblItem                 $tblItem
     * @param float                   $Price
     * @param TblPerson               $tblPersonCauser
     * @param TblPerson|null          $tblPersonDebtor
     * @param TblItemVariant|null     $tblItemVariant
     * @param TblBankAccount|null     $tblBankAccount
     * @param TblBankReference|null   $tblBankReference
     * @param TblPaymentType|null     $tblPaymentType
     * @param TblDebtorSelection|null $tblDebtorSelection
     *
     * @return TblBasketVerification
     */
    public function createBasketVerification(
        TblBasket $tblBasket,
        TblItem $tblItem,
        $Price,
        TblPerson $tblPersonCauser,
        TblPerson $tblPersonDebtor = null,
        TblItemVariant $tblItemVariant = null,
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null,
        TblPaymentType $tblPaymentType = null,
        TblDebtorSelection $tblDebtorSelection = null
    ){

        $Manager = $this->getConnection()->getEntityManager();


        if($tblPersonDebtor){
            $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array(
                TblBasketVerification::ATTR_TBL_BASKET                => $tblBasket->getId(),
                TblBasketVerification::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPersonCauser->getId(),
                TblBasketVerification::ATTR_SERVICE_TBL_PERSON_DEBTOR => $tblPersonDebtor->getId(),
                TblBasketVerification::ATTR_SERVICE_TBL_ITEM          => $tblItem->getId()
            ));
        } else {
            $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array(
                TblBasketVerification::ATTR_TBL_BASKET       => $tblBasket->getId(),
                TblBasketVerification::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
            ));
        }

        if(null === $Entity){
            $Entity = new TblBasketVerification();
            $Entity->setTblBasket($tblBasket);
            $Entity->setServiceTblPersonCauser($tblPersonCauser);
            $Entity->setServiceTblPersonDebtor($tblPersonDebtor);
            $Entity->setServiceTblItemVariant($tblItemVariant);
            $Entity->setServiceTblBankAccount($tblBankAccount);
            $Entity->setServiceTblBankReference($tblBankReference);
            $Entity->setServiceTblPaymentType($tblPaymentType);
            $Entity->setServiceTblDebtorSelection($tblDebtorSelection);
            $Entity->setServiceTblItem($tblItem);
            $Entity->setValue($Price);
            $Entity->setQuantity(1);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     * @param array     $DebtorDataArray
     *
     * @return bool
     */
    public function createBasketVerificationBulk(
        TblBasket $tblBasket,
        TblItem $tblItem,
        $DebtorDataArray = array()
    ){

        $Manager = $this->getConnection()->getEntityManager();

        if(!empty($DebtorDataArray)){
//            /** @var TblDebtorSelection $tblDebtorSelection */
            foreach($DebtorDataArray as $Item) {
                $PersonCauserId = $Item['Causer'];
                $PersonDebtorId = $Item['Debtor'];
                $ItemVariantId = $Item['ItemVariant'];
                $BankAccountId = $Item['BankAccount'];
                $BankReferenceId = $Item['BankReference'];
                $PaymentTypeId = $Item['PaymentType'];
                $DebtorSelectionId = $Item['DebtorSelection'];
                $Price = $Item['Price'];

                if($PersonDebtorId){
                    $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array(
                        TblBasketVerification::ATTR_TBL_BASKET                => $tblBasket->getId(),
                        TblBasketVerification::ATTR_SERVICE_TBL_PERSON_CAUSER => $PersonCauserId,
                        TblBasketVerification::ATTR_SERVICE_TBL_PERSON_DEBTOR => $PersonDebtorId,
                        TblBasketVerification::ATTR_SERVICE_TBL_ITEM          => $tblItem->getId()
                    ));
                } else {
                    $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array(
                        TblBasketVerification::ATTR_TBL_BASKET                => $tblBasket->getId(),
                        TblBasketVerification::ATTR_SERVICE_TBL_PERSON_CAUSER => $PersonCauserId,
                        TblBasketVerification::ATTR_SERVICE_TBL_ITEM          => $tblItem->getId()
                    ));
                }

                if(null === $Entity){
                    $Entity = new TblBasketVerification();
                    $Entity->setTblBasket($tblBasket);
                    $Entity->setServiceTblPersonCauser(Person::useService()->getPersonById($PersonCauserId));
                    if($PersonDebtorId){
                        $Entity->setServiceTblPersonDebtor(Person::useService()->getPersonById($PersonDebtorId));
                    }
                    $Entity->setServiceTblItemVariant(null === $ItemVariantId ? null : Item::useService()->getItemVariantById($ItemVariantId));
                    $Entity->setServiceTblBankAccount(null === $BankAccountId ? null : Debtor::useService()->getBankAccountById($BankAccountId));
                    $Entity->setServiceTblBankReference(null === $BankReferenceId ? null : Debtor::useService()->getBankReferenceById($BankReferenceId));
                    if($PaymentTypeId){
                        $Entity->setServiceTblPaymentType(Balance::useService()->getPaymentTypeById($PaymentTypeId));
                    }
                    $Entity->setServiceTblDebtorSelection(null === $DebtorSelectionId ? null : Debtor::useService()->getDebtorSelectionById($DebtorSelectionId));
                    $Entity->setServiceTblItem($tblItem);
                    $Entity->setValue($Price);
                    $Entity->setQuantity(1);
                    $Manager->bulkSaveEntity($Entity);
                    Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                        $Entity, true);
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param string              $Name
     * @param string              $Description
     * @param string              $Year
     * @param string              $Month
     * @param \DateTime           $TargetTime
     * @param \DateTime|null      $BillTime
     * @param TblBasketType       $tblBasketType
     * @param TblCreditor|null    $tblCreditor
     * @param TblDivision|null    $tblDivision
     * @param TblType|null        $tblType
     * @param TblDebtorPeriodType $tblDebtorPeriodType
     * @param string              $FibuAccount
     * @param string              $FibuToAccount
     *
     * @return TblBasket
     */
    public function createBasket(
        $Name,
        $Description,
        $Year,
        $Month,
        $TargetTime,
        $BillTime,
        $tblBasketType,
        TblCreditor $tblCreditor = null,
        TblDivision $tblDivision = null,
        TblType $tblType = null,
        TblDebtorPeriodType $tblDebtorPeriodType = null,
        $FibuAccount = '',
        $FibuToAccount = ''
    ){

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasket')->findOneBy(array(
            TblBasket::ATTR_NAME  => $Name,
            TblBasket::ATTR_MONTH => $Month,
            TblBasket::ATTR_YEAR  => $Year,
        ));

        if(null === $Entity){
            $Entity = new TblBasket();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setYear($Year);
            $Entity->setMonth($Month);
            $Entity->setTargetTime($TargetTime);
            $Entity->setBillTime($BillTime);
            $Entity->setIsDone(false);
            $Entity->setIsArchive(false);
            $Entity->setTblBasketType($tblBasketType);
            $Entity->setServiceTblCreditor($tblCreditor);
            $Entity->setServiceTblDivision($tblDivision);
            $Entity->setServiceTblType($tblType);
            $Entity->setServiceTblDebtorPeriodType($tblDebtorPeriodType);
            $Entity->setFibuAccount($FibuAccount);
            $Entity->setFibuToAccount($FibuToAccount);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return TblBasketItem
     */
    public function createBasketItem(TblBasket $tblBasket, TblItem $tblItem)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array(
            TblBasketItem::ATTR_TBL_BASKET       => $tblBasket->getId(),
            TblBasketItem::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
        ));

        if(null === $Entity){
            $Entity = new TblBasketItem();
            $Entity->setServiceTblItem($tblItem);
            $Entity->setTblBasket($tblBasket);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBasket        $tblBasket
     * @param string           $Name
     * @param string           $Description
     * @param \DateTime|null   $TargetTime
     * @param \DateTime|null   $BillTime
     * @param TblCreditor|null $tblCreditor
     * @param string           $FibuAccount
     * @param string           $FibuToAccount
     *
     * @return bool
     */
    public function updateBasket(
        TblBasket $tblBasket,
        $Name,
        $Description,
        $TargetTime,
        $BillTime,
        TblCreditor $tblCreditor = null,
        $FibuAccount = '',
        $FibuToAccount = ''
    ){

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTargetTime($TargetTime);
            $Entity->setBillTime($BillTime);
            $Entity->setServiceTblCreditor($tblCreditor);
            $Entity->setFibuAccount($FibuAccount);
            $Entity->setFibuToAccount($FibuToAccount);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
//            Debugger::screenDump($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param bool      $IsDone
     *
     * @return bool
     */
    public function updateBasketDone(TblBasket $tblBasket, $IsDone = true)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setName($tblBasket->getName());
            $Entity->setIsDone($IsDone);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param bool      $IsArchive
     *
     * @return bool
     */
    public function updateBasketArchive(TblBasket $tblBasket, $IsArchive = true)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setName($tblBasket->getName());
            $Entity->setIsArchive($IsArchive);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $PersonName
     *
     * @return bool
     */
    public function updateBasketSepa(TblBasket $tblBasket, $PersonName)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setName($tblBasket->getName());
            $Entity->setSepaUser($PersonName);
            $Entity->setSepaDate(new \DateTime('now'));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $PersonName
     *
     * @return bool
     */
    public function updateBasketDatev(TblBasket $tblBasket, $PersonName)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasket $Entity */
        $Entity = $Manager->getEntityById('TblBasket', $tblBasket->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setName($tblBasket->getName());
            $Entity->setDatevUser($PersonName);
            $Entity->setDatevDate(new \DateTime('now'));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param                       $Quantity
     *
     * @return bool
     */
    public function updateBasketVerificationInQuantity(TblBasketVerification $tblBasketVerification, $Quantity)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketVerification $Entity */
        $Entity = $Manager->getEntityById('TblBasketVerification', $tblBasketVerification->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setQuantity($Quantity);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param string                $Price
     *
     * @return bool
     */
    public function changeBasketVerificationInPrice(TblBasketVerification $tblBasketVerification, $Price)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketVerification $Entity */
        $Entity = $Manager->getEntityById('TblBasketVerification', $tblBasketVerification->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setValue($Price);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param TblDebtorSelection    $tblDebtorSelection
     *
     * @return bool
     */
    public function updateBasketVerificationInDebtorSelection(TblBasketVerification $tblBasketVerification,
        TblDebtorSelection $tblDebtorSelection)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketVerification $Entity */
        $Entity = $Manager->getEntityById('TblBasketVerification', $tblBasketVerification->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){

            if(!($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                $tblItemVariant = null;
            }
            $Entity->setServiceTblDebtorSelection($tblDebtorSelection);
            $Entity->setServiceTblItemVariant($tblItemVariant);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param TblPerson             $tblPersonDebtor
     * @param TblPaymentType        $tblPaymentType
     * @param string                $Value
     * @param TblItemVariant|null   $tblItemVariant
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool
     */
    public function updateBasketVerificationDebtor(
        TblBasketVerification $tblBasketVerification,
        TblPerson $tblPersonDebtor,
        TblPaymentType $tblPaymentType,
        $Value = '0',
        TblItemVariant $tblItemVariant = null,
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null
    ){

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBasketVerification $Entity */
        $Entity = $Manager->getEntityById('TblBasketVerification', $tblBasketVerification->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setServiceTblPersonDebtor($tblPersonDebtor);
            $Entity->setServiceTblPaymentType($tblPaymentType);
            $Entity->setServiceTblBankAccount($tblBankAccount);
            $Entity->setServiceTblBankReference($tblBankReference);
            $Entity->setValue($Value);
            $Entity->setServiceTblItemVariant($tblItemVariant);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(
        TblBasket $tblBasket
    ){

        if($tblBasket !== null){
            $Manager = $this->getConnection()->getEntityManager();

            $EntityList = $Manager->getEntity('TblBasketItem')->findBy(array(TblBasketItem::ATTR_TBL_BASKET => $tblBasket->getId()));
            foreach($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->bulkKillEntity($Entity);
            }
            $Entity = $Manager->getEntity('TblBasket')->findOneBy(array('Id' => $tblBasket->getId()));
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
//            $Manager->flushCache();
            return true;
        }

        return false;
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return bool
     */
    public function destroyBasketItem(
        TblBasketItem $tblBasketItem
    ){

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(
            array(
                'Id' => $tblBasketItem->getId()
            ));
        if(null !== $Entity){
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param array $BasketItemIdList
     *
     * @return bool
     */
    public function destroyBasketItemBulk($BasketItemIdList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($BasketItemIdList)){
            foreach($BasketItemIdList as $BasketItemId) {
                if(($tblBasketItem = Basket::useService()->getBasketItemById($BasketItemId))){
                    $Entity = $Manager->getEntity('TblBasketItem')->findOneBy(array('Id' => $tblBasketItem->getId()));
                    /**@var TblBasketItem $Entity */
                    if(null !== $Entity){
                        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                            $Entity, true);
                        $Manager->bulkKillEntity($Entity);
                    }
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return bool
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array('Id' => $tblBasketVerification->getId()));
        /**@var TblBasketVerification $Entity */
        if(null !== $Entity){
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }

    /**
     * @param array $BasketVerificationIdList
     *
     * @return bool
     */
    public function destroyBasketVerificationBulk($BasketVerificationIdList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($BasketVerificationIdList)){
            foreach($BasketVerificationIdList as $BasketVerificationId) {
                if(($tblBasketVerification = Basket::useService()->getBasketVerificationById($BasketVerificationId))){
                    $Entity = $Manager->getEntity('TblBasketVerification')->findOneBy(array('Id' => $tblBasketVerification->getId()));
                    /**@var TblBasketVerification $Entity */
                    if(null !== $Entity){
                        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                            $Entity, true);
                        $Manager->bulkKillEntity($Entity);
                    }
                }
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
            return true;
        }
        return false;
    }
}
