<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVGSM;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GsJ extends Certificate
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
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null){

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample());

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
            ->addSlice(GsHjInfo::getDivisionAndYearIndividuell($personId))
            ->addSlice($this->getStudentName($personId))
                ->addSlice($this->getDescriptionContent($personId, '500px', '20px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage entschuldigt:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Missing }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage unentschuldigt:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                        {% else %}
                                            &nbsp;
                                        {% endif %}')
                    )
                )
                ->styleMarginTop('15px')
            )
            ->addSlice($this->getTransfer($personId, '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice($this->getParentSign());
    }
}