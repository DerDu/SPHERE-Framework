<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EMSP;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class EmspStyle
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EMSP
 */
class EmspStyle
{
    /**
     * @param $isSample
     *
     * @return Slice
     */
    public static function getHeader($isSample)
    {
        $pictureHeight = '95px';

        if ($isSample) {
            $header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('EVANGELISCHE MONTESSORI<br/>GRUNDSCHULE PLAUEN')
                    ->styleTextSize('12pt')
                        , '45%'
                    )
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EMSP.jpg',
                        'auto', $pictureHeight))->styleAlignRight()
                        ->stylePaddingTop('-19px')
                        ->stylePaddingRight('-10px')
                        , '25%')
                );
        } else {
            $header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('EVANGELISCHE MONTESSORI<br/>GRUNDSCHULE PLAUEN')
                        , '75%'
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EMSP.jpg',
                        'auto', $pictureHeight))->styleAlignRight()
                        , '25%')
                );
        }

        return $header->styleHeight('50px');
    }

    /**
     * @param string $HeadLine
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getCertificateHead($HeadLine = '', $MarginTop = '37px')
    {
        $CertificateSlice = (new Slice());
        $CertificateSlice->addElement((new Element())
            ->setContent($HeadLine)
            ->styleTextSize('24px')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleMarginTop($MarginTop)
        );
        return $CertificateSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     * @param string $YearString
     *
     * @return Slice
     */
    public static function getDivisionAndYear($personId, $YearString = '1. Schulhalbjahr', $MarginTop = '45px')
    {
        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse: {{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent($YearString)
                ->styleAlignCenter()
                , '50%')
            ->addElementColumn((new Element())
                ->setContent('Schuljahr: {{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignRight()
                , '25%')
        )->styleMarginTop($MarginTop);
        return $YearDivisionSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getStudentName($personId, $MarginTop = '18px')
    {
        $StudentSlice = (new Slice());
        $StudentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Vor- und Zuname:')
                ->stylePaddingTop('8px')
                , '24%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                ->styleTextSize('22px')
                ->styleTextBold()
                , '76%')
        )->styleMarginTop($MarginTop);
        return $StudentSlice;
    }

    public static function getBirthRow($personId)
    {
        $BirthSlice = (new Slice());
        $BirthSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Geboren am:')
                , '15%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                        {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                , '85%')
        )->styleMarginTop('25px');
        return $BirthSlice;
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string|bool $TextSize
     * @return Slice
     */
    public static function getDescriptionContent($personId, $Height = '467px', $TextSize = false)
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        $Element->setContent('{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
            {% else %}
                &nbsp;
            {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop('10px')
            ->styleLineHeight('150%');

        if($tblSetting && $tblSetting->getValue()){
            $Element->styleAlignJustify();
        }
        if($TextSize){
            $Element->styleTextSize($TextSize);
        }

        return (new Slice())->addElement($Element);
    }

    /**
     * @param $personId
     * @param string $MarginTop
     * @param bool $hasTransfer
     *
     * @return Slice
     */
    public static function getTransfer($personId, $MarginTop = '5px', $hasTransfer = true)
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addElement((new Element())
            ->setContent(
                $hasTransfer
                    ? 'Versetzungsvermerk: {% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }} in Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name + 1 }}.
                    {% else %}
                          &nbsp;
                    {% endif %}'
                    : '&nbsp;'
            )
            ->styleMarginTop($MarginTop)
        );
        return $TransferSlice;
    }

    /**
     * @param string $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    public static function getMiss($personId, $marginTop = '15px')
    {
        $DescriptionSlice = (new Slice());
        $DescriptionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Fehltage entschuldigt: &nbsp;&nbsp;
                {% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                    {{ Content.P' . $personId . '.Input.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
//                    ->styleBorderBottom('1px')
                , '60%')
            ->addElementColumn((new Element())
                ->setContent('unentschuldigt: &nbsp;&nbsp;
                {% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                {% else %}
                    &nbsp;
                {% endif %}')
                , '40%')
        )
            ->styleMarginTop($marginTop);
        return $DescriptionSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getDateLine($personId, $MarginTop = '15px')
    {
        $DateSlice = (new Slice());
        $DateSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum: ')
                , '8%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    &nbsp;{{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                , '92%')
        )
            ->styleMarginTop($MarginTop);
        return $DateSlice;
    }

    /**
     * @param $personId
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getSignPart($personId, $isExtended = true, $MarginTop = '20px')
    {

        $widthName = 42;
        $widthSpace = 16;
        $widthCombine = $widthName + $widthSpace;

        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , $widthName.'%')
                ->addElementColumn((new Element())
                    , $widthSpace.'%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , $widthName.'%')
            )
                ->styleMarginTop($MarginTop)
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
                        , $widthName.'%')
                    ->addElementColumn((new Element())
                        , $widthSpace.'%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenleiter/in
                            {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , $widthName.'%')
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
                        , $widthName.'%')
                    ->addElementColumn((new Element())
                        , $widthSpace.'%')
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
                        , $widthName.'%')
                );
        } else {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    , $widthCombine.'%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , $widthName.'%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , $widthCombine.'%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenleiter/in
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , $widthName.'%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , $widthCombine.'%')
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
                        , $widthName.'%')
                );
        }
        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getParentSign($MarginTop = '25px')
    {
        $ParentSlice = (new Slice());
        $ParentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '70%')
        )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Erziehungsberechtigte(r)')
                    ->styleAlignCenter()
//                    ->styleTextSize('11px')
                    ->styleHeight('0px')
                    , '70%')
            )
            ->styleMarginTop($MarginTop);
        return $ParentSlice;
    }
}