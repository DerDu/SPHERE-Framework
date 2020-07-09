<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EMSP;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EmspGsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EMSP
 */
class EmspGsHj extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice(EmspStyle::getHeader($this->isSample()))
            ->addSlice(EmspStyle::getCertificateHead('HALBJAHRESINFORMATION DER GRUNDSCHULE'))
            ->addSlice(EmspStyle::getDivisionAndYear($personId))
            ->addSlice(EmspStyle::getStudentName($personId))
            ->addSlice(EmspStyle::getBirthRow($personId))
            ->addSlice(EmspStyle::getDescriptionContent($personId, '467px'))
//            ->addSlice(EmspStyle::getTransfer($personId))
            ->addSlice(EmspStyle::getMiss($personId, '37px'))
            ->addSlice(EmspStyle::getDateLine($personId))
            ->addSlice(EmspStyle::getSignPart($personId, false))
            ->addSlice(EmspStyle::getParentSign());

        return $pageList;
    }
}