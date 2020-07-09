<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EMSP;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EmspGsJ
 *
 * @package Application\Api\Education\Certificate\Generator\Repository\EMSP
 */
class EmspGsJ extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        // Klasse 1 hat keinen Versetzungsvermerk
        if (($tblDivision = $this->getTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && intval($tblLevel->getName()) == 1
        ) {
            $hasTransfer = false;
        } else {
            $hasTransfer = true;
        }

        $pageList[] = (new Page())
            ->addSlice(EmspStyle::getHeader($this->isSample()))
            ->addSlice(EmspStyle::getCertificateHead('JAHRESZEUGNIS DER GRUNDSCHULE'))
            ->addSlice(EmspStyle::getDivisionAndYear($personId, '2. Schulhalbjahr'))
            ->addSlice(EmspStyle::getStudentName($personId))
            ->addSlice(EmspStyle::getBirthRow($personId))
            ->addSlice(EmspStyle::getDescriptionContent($personId, '467px'))
            ->addSlice(EmspStyle::getTransfer($personId, '5px', $hasTransfer))
            ->addSlice(EmspStyle::getMiss($personId))
            ->addSlice(EmspStyle::getDateLine($personId))
            ->addSlice(EmspStyle::getSignPart($personId))
            ->addSlice(EmspStyle::getParentSign());

        return $pageList;
    }
}
