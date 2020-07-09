<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsJ extends Certificate
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

        $bfsHj = new BfsHj(
            $this->getTblDivision() ? $this->getTblDivision() : null,
            $this->getTblPrepareCertificate() ? $this->getTblPrepareCertificate() : null,
            $this->isSample()
        );

        $pageList[] = (new Page())
            ->addSlice($bfsHj->getSchoolHead($personId, 'Jahreszeugnis'))
            ->addSlice($bfsHj->getStudentHead($personId, 'Schuljahr'))
            ->addSlice($bfsHj->getSubjectLineAcross($personId, $this->getCertificateEntity()))
            ->addSlice($bfsHj->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich'))
        ;

        $pageList[] = (new Page())
            ->addSlice($bfsHj->getSecondPageHead($personId, 'Jahreszeugnis'))
            ->addSlice($bfsHj->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 10, true, '180px'))
            ->addSlice($bfsHj->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($bfsHj->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($bfsHj->getDescriptionBsContent($personId, '80px'))
            ->addSlice($this->getTransfer($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('140px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($bfsHj->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getTransfer($personId, $MarginTop = '0px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                ->styleTextUnderline()
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                        {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
                    {% else %}
                        Frau/Herr
                    {% endif %}
                    {% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}')
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , '75%')
        )
            ->styleMarginTop($MarginTop)
            ->styleBorderLeft('0.5px', '#000', 'dotted')
            ->styleBorderRight('0.5px', '#000', 'dotted')
            ->styleBorderBottom('0.5px', '#000', 'dotted');
        return $TransferSlice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public function getIndividuallySignPart($personId)
    {
        $Slice = (new Slice());

        $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Company.Address.City.Name }}')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px', '#BBB')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Input.Date }}')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px', '#BBB')
                    , '35%')
            )
            ->styleMarginTop('25px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ort')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Siegel')
                    ->styleTextColor('gray')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '20%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Datum')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );

        $marginTop = '40px';
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px', '#BBB')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop($marginTop)
                ->styleBorderBottom('0.5px', '#BBB')
                , '35%')
        )
        ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                )
                ->styleAlignCenter()
                ->styleTextSize('11px')
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}'
                )
                ->styleAlignCenter()
                ->styleTextSize('11px')
                , '35%')
        )
        ->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                )
                ->styleTextSize('11px')
                ->stylePaddingTop('2px')
                ->styleAlignCenter()
                , '35%')
            ->addElementColumn((new Element())
                , '30%')
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                )
                ->styleTextSize('11px')
                ->stylePaddingTop('2px')
                ->styleAlignCenter()
                , '35%')
        );

        $Slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleHeight('30px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px', '#BBB', 'dotted')
                , '73%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('Eltern')
                ->styleTextSize('10px')
                ->styleAlignCenter()
                , '73%'
            )
        );

        return $Slice;
    }
}