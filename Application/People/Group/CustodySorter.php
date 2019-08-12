<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.04.2019
 * Time: 11:19
 */

namespace SPHERE\Application\People\Group;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;

class CustodySorter
{

    /** @var TblPerson $tblPerson */
    protected $tblPerson;

    /** @var Custody[] $CustodyList */
    protected $CustodyList = array();

    /** @var Custody|null $Custody1 */
    protected $Custody1;

    /** @var Custody|null $Custody2 */
    protected $Custody2;

    /** @var Custody|null $Custody3 */
    protected $Custody3;

    /** @var Custody|null */
    protected $UnAssigned1;

    /** @var Custody|null */
    protected $UnAssigned2;

    /** @var Custody|null */
    protected $UnAssigned3;

    /**
     * CustodySorter constructor.
     *
     * @param TblPerson $tblPersonTo
     * @param TblToPerson $tblToPerson
     */
    public function __construct(TblPerson $tblPersonTo, TblToPerson $tblToPerson)
    {
        $this->tblPerson = $tblPersonTo;
        $this->CustodyList = array($tblToPerson->getId() => new Custody($tblToPerson));
    }

    /**
     * @param TblToPerson $tblToPerson
     */
    public function addCustody(TblToPerson $tblToPerson)
    {
        $this->CustodyList[$tblToPerson->getId()] = new Custody($tblToPerson);
    }

    /**
     * @return TblPerson
     */
    public function getTblPerson()
    {
        return $this->tblPerson;
    }

    /**
     * @param string $genderSetting
     *
     * @return bool
     */
    public function assign($genderSetting)
    {
        $unAssigned = array();
        $assignedCount = 0;
        $unAssignedCount = 0;
        $show = false;
        foreach ($this->CustodyList as $custody) {
            switch ($custody->getRanking()) {
                case 1: $this->Custody1 = $custody; $assignedCount++; break;
                case 2: $this->Custody2 = $custody; $assignedCount++; break;
                case 3: $this->Custody3 = $custody; $assignedCount++; break;
                default: $unAssigned[++$unAssignedCount] = $custody;
            }
        }

        // 2 Sorgeberechtigte, keiner zugeordnet
        if ($unAssignedCount == 2 && $assignedCount == 0
            && ($gender1 = $unAssigned[1]->getGenderName())
            && ($gender2 = $unAssigned[2]->getGenderName())
            && $gender1 != $gender2
        ) {
            if ($gender1 == $genderSetting) {
                $this->Custody1 = $unAssigned[1];
                $this->Custody1->setIsModified(true);

                $this->Custody2 = $unAssigned[2];
                $this->Custody2->setIsModified(true);
            } else {
                $this->Custody1 = $unAssigned[2];
                $this->Custody1->setIsModified(true);

                $this->Custody2 = $unAssigned[1];
                $this->Custody2->setIsModified(true);
            }
        // 1 zugeordneter + 1 unzugeordneter
        } elseif ($unAssignedCount == 1 && $assignedCount == 1
            && (($this->Custody1
                && ($gender1 = $this->Custody1->getGenderName())
                && ($gender2 = $unAssigned[1]->getGenderName())
                && $gender1 != $gender2
                ) || ($this->Custody2
                    && ($gender2 = $this->Custody2->getGenderName())
                    && ($gender1 = $unAssigned[1]->getGenderName())
                    && $gender1 != $gender2
                )
            )
        ) {
            if ($this->Custody1) {
                if ($gender1 == $genderSetting) {
                    $this->Custody2 = $unAssigned[1];
                    $this->Custody2->setIsModified(true);
                } else {
                    $this->Custody2 = $this->Custody1;
                    $this->Custody2->setIsModified(true);

                    $this->Custody1 = $unAssigned[1];
                    $this->Custody1->setIsModified(true);
                }
            } else {
                if ($gender1 == $genderSetting) {
                    $this->Custody1 = $unAssigned[1];
                    $this->Custody1->setIsModified(true);
                } else {
                    $this->Custody1 = $this->Custody2;
                    $this->Custody1->setIsModified(true);

                    $this->Custody2 = $unAssigned[1];
                    $this->Custody2->setIsModified(true);
                }
            }
        // 2 Sorberechtigte falsch zugeordnet
        } elseif ($assignedCount == 2 && $unAssignedCount == 0
            && $this->Custody1
            && $this->Custody2
            && ($gender1 = $this->Custody1->getGenderName())
            && ($gender2 = $this->Custody2->getGenderName())
            && $gender1 != $gender2
            && $gender1 != $genderSetting
        ) {
            $temp = $this->Custody1;
            $this->Custody1 = $this->getCustody2();
            $this->Custody1->setIsModified(true);
            $this->Custody2 = $temp;
            $this->Custody2->setIsModified(true);
        // 1 unzugeordneter Sorgeberechtigter mit dem Geschlecht von S1 (Mandanteneinstellung)
        } elseif ($assignedCount == 0 && $unAssignedCount == 1
            && ($gender1 = $unAssigned[1]->getGenderName())
            && $gender1 == $genderSetting
        ) {
            $this->Custody1 = $unAssigned[1];
            $this->Custody1->setIsModified(true);
        } else {
            if ($this->Custody1
                && $this->Custody1->getGenderName()
                && $this->Custody1->getGenderName() != $genderSetting
            ) {
                $this->Custody1->setIsWarning(true);
                $show = true;
            }
            if ($this->Custody2
                && $this->Custody2->getGenderName()
                && $this->Custody2->getGenderName() == $genderSetting
            ) {
                $this->Custody2->setIsWarning(true);
                $show = true;
            }

            if ($unAssignedCount > 0) {
                $show = true;
            }

            foreach ($unAssigned as $key => $item) {
                switch ($key) {
                    case 1: $this->UnAssigned1 = $item; break;
                    case 2: $this->UnAssigned2 = $item; break;
                    case 3: $this->UnAssigned3 = $item; break;
                }
            }
        }

        return $show;
    }

    /**
     * @return Custody|null
     */
    public function getCustody1()
    {
        return $this->Custody1;
    }

    /**
     * @return Custody|null
     */
    public function getCustody2()
    {
        return $this->Custody2;
    }

    /**
     * @return Custody|null
     */
    public function getCustody3()
    {
        return $this->Custody3;
    }

    /**
     * @return Custody|null
     */
    public function getUnAssigned1()
    {
        return $this->UnAssigned1;
    }

    /**
     * @return Custody|null
     */
    public function getUnAssigned2()
    {
        return $this->UnAssigned2;
    }

    /**
     * @return Custody|null
     */
    public function getUnAssigned3()
    {
        return $this->UnAssigned3;
    }

    /**
     * @param $ModifyList
     */
    public function addModifyList(&$ModifyList)
    {
        if ($this->Custody1 && $this->Custody1->isModified()) {
            $ModifyList[$this->Custody1->getToPersonId()] = 1;
        }
        if ($this->Custody2 && $this->Custody2->isModified()) {
            $ModifyList[$this->Custody2->getToPersonId()] = 2;
        }
        if ($this->Custody3 && $this->Custody3->isModified()) {
            $ModifyList[$this->Custody3->getToPersonId()] = 3;
        }
    }
}