<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CosHjSek
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosHjSek extends Certificate
{

    const TEXT_SIZE = '13px';

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesCoswig($personId, self::TEXT_SIZE, false, '10px');
        $subjectLanesSlice = $this->getSubjectLanesCoswig($personId, true, array(), self::TEXT_SIZE,
            false);
        $obligationToVotePart = $this->getObligationToVotePartCustomForCoswig($personId,
            self::TEXT_SIZE);

        return $this->buildContentPage($personId, $this->isSample(), 'Halbjahresinformation der Schule (Sekundarstufe)',
            '1. Schulhalbjahr', $gradeLanesSlice, $subjectLanesSlice, $obligationToVotePart
        );
    }

    /**
     * @param $personId
     * @param $isSample
     * @param string $title
     * @param $term
     * @param Slice $gradeLanesSlice
     * @param Slice $subjectLanesSlice
     * @param Slice $obligationToVotePart
     *
     * @return Page
     */
    public static function buildContentPage(
        $personId,
        $isSample,
        $title = 'Halbjahresinformation der Schule (Sekundarstufe)',
        $term,
        Slice $gradeLanesSlice,
        Slice $subjectLanesSlice,
        Slice $obligationToVotePart
    ) {

        $subjectLanesSlice->styleHeight('248px');

        if ($isSample) {
            $Header = array(
                (new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSC.jpg',
                                '100px', '100px'))
                                ->stylePaddingTop('12px')
                                ->styleHeight('0px')
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('FREISTAAT SACHSEN')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize('21px')
                                ->styleAlignCenter()
                                ->stylePaddingTop('22px')
                                , '50%')
                            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                                '165px', '50px'))
                                ->stylePaddingTop('12px')
                                , '25%')
                        )
                    ),
                (new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element\Sample())
                                ->styleTextSize('30px')
                                ->styleMarginTop('55px')
                                ->styleHeight('0px')
                            )
                            ->addElementColumn((new Element())
                                ->setContent('Evangelische Schule Coswig')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize('21px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('40px')
                                ->styleLineHeight('85%')
                                , '50%')
                            ->addElementColumn((new Element())
                                , '25%')
                        )
                    )
            );
        } else {
            $Header = array(
                (new Section())
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSC.jpg',
                        '100px', '100px'))
                        ->stylePaddingTop('12px')
                        ->styleHeight('20px')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('FREISTAAT SACHSEN')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleTextSize('21px')
                        ->styleAlignCenter()
                        ->stylePaddingTop('22px')
                        , '50%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        ->stylePaddingTop('12px')
                        , '25%'),
                (new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Evangelische Schule Coswig')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleTextSize('21px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('40px')
                        ->styleLineHeight('85%')
                    )
            );
        }

        return (new Page())
//                ->addSlice($Header)
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSectionList($Header)
                        ->addElement((new Element())
                            ->setContent('staatlich anerkannte Ersatzschule')
                            ->styleFontFamily('Trebuchet MS')
                            ->styleTextSize('16px')
                            ->styleAlignCenter()
                        )
                        ->addElement((new Element())
                            ->setContent($title)
                            ->styleFontFamily('Trebuchet MS')
                            ->styleTextSize('20px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            ->styleMarginTop('25px')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Klasse')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '7%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '10%')
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '57%')
                                    ->addElementColumn((new Element())
                                        ->setContent($term)
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '16%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '10%')
                                )->styleMarginTop('15px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Vor- und Zuname:')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '18%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '64%')
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '18%')
                                )->styleMarginTop('10px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('
                                {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                                        nahm am Unterricht der Schulart Mittelschule mit dem Ziel des
                                        {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '100%')
                                )->styleMarginTop('0px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($gradeLanesSlice)
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addElement((new Element())
                                    ->setContent('Leistung in den einzelnen Fächern')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextItalic()
                                    ->styleTextBold()
                                    ->styleMarginTop('15px')
                                    ->styleTextSize(self::TEXT_SIZE)
                                )
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($subjectLanesSlice)
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($obligationToVotePart)
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addElement((new Element())
                                    ->setContent('Bemerkungen:')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextItalic()
                                    ->styleTextSize(self::TEXT_SIZE)
                                )->stylePaddingTop('5px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleLineHeight('85%')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '85%')
                                )->styleHeight('55px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fehltage entschuldigt:')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize(self::TEXT_SIZE)
                                , '22%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize(self::TEXT_SIZE)
                                , '7%')
                            ->addElementColumn((new Element())
                                , '5%')
                            ->addElementColumn((new Element())
                                ->setContent('unentschuldigt:')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize(self::TEXT_SIZE)
                                , '15%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize(self::TEXT_SIZE)
                                , '7%')
                            ->addElementColumn((new Element())
                                , '44%')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Datum:')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '7%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                                    {{ Content.P' . $personId . '.Input.Date }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleBorderBottom()
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '20%')
                                    ->addElementColumn((new Element())
                                        , '56%')
                                )->styleMarginTop('15px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '35%')
                                    ->addElementColumn((new Element())
                                        , '30%')
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '35%')
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('
                                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                                {% else %}
                                                    Schulleiter/in
                                                {% endif %}
                                            ')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize('11px')
                                        , '35%'
                                    )
                                    ->addElementColumn((new Element())
                                        , '30%'
                                    )
                                    ->addElementColumn((new Element())
                                        ->setContent('
                                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                                {% else %}
                                                    Klassenleiter/in
                                                {% endif %}
                                            ')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize('11px')
                                        , '35%')
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                                    {{ Content.P' . $personId . '.Headmaster.Name }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}'
                                        )
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleLineHeight('85%')
                                        ->styleTextSize('11px')
                                        ->stylePaddingBottom('3px')
                                        , '35%')
                                    ->addElementColumn((new Element())
                                        , '30%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleLineHeight('85%')
                                        ->styleTextSize('11px')
                                        ->stylePaddingBottom('3px')
                                        , '35%')

                                )->styleMarginTop('25px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Zur Kenntnis genommen:')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '25%')
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleBorderBottom()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '75%')
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Personensorgeberechtigte/r')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleAlignCenter()
                                        ->styleTextSize('11px')
                                        , '100%')
                                )
                                ->styleMarginTop('25px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addElement((new Element())
                                    ->setContent('Notenstufen 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextSize('9px')
                                    ->styleMarginTop('5px')
                                )
                            )
                        )
                    )
                )
                ->styleBorderAll()
                ->stylePaddingLeft('20px')
                ->stylePaddingRight('20px')
            );
    }
}
