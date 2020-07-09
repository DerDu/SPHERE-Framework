<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVGSM;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsHjInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GsHjInfo extends Certificate
{

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
            ->addSlice($this->getCertificateHead('Halbjahresinformation der Grundschule'))
            ->addSlice(self::getDivisionAndYearIndividuell($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getDescriptionContent($personId, '530px', '20px'))
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
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, false))
            ->addSlice($this->getParentSign());
    }

    /**
     * @param $personId
     * @param string $MarginTop
     * @param string $YearString
     *
     * @return Slice
     */
    public static function getDivisionAndYearIndividuell(
        $personId, $MarginTop = '20px', $YearString = 'Schuljahr'
    ) {

        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.Input.DivisionName is not empty) %}
                        {{ Content.P' . $personId . '.Input.DivisionName }}
                    {% else %}
                        &nbsp;
                    {% endif %}'
                )
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '7%')
            ->addElementColumn((new Element())
                , '55%')
            ->addElementColumn((new Element())
                ->setContent($YearString . ':')
                ->styleAlignRight()
                , '18%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
        )->styleMarginTop($MarginTop);

        return $YearDivisionSlice;
    }
}