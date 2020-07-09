<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class EsbdStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
abstract class EsbdStyle extends Certificate
{

    const TEXT_SIZE = '10pt';
    const TEXT_SIZE_BIG = '18pt';

    const COLOR_GREEN = '#29948E';

    /**
     * @param string $SchoolName
     * @param string $secondLine
     *
     * @return Slice
     */
    protected function getHeadConsumer($SchoolName = '', $secondLine = '')
    {

        // Grundschullogo muss kleiner sein, damit genügend Platz um das Sachsenlogo ist
        $height = '50px';
        $isPrimarySchool = true;
        if(strpos($SchoolName, 'Gymnasium') || strpos($SchoolName, 'Oberschule')){
            $isPrimarySchool = false;
            $height = '62px';
        }
        $picturePath = 'Common/Style/Resource/Logo/ESBD.jpg';

        $Head = new Slice();
        $SectionLogo = new Section();
        $SectionSchoolName = new Section();
        $SectionTitle = new Section();

        // Individually Logo
        $SectionLogo->addElementColumn((new Element\Image($picturePath, 'auto', $height)), '74%');

        if($isPrimarySchool){
            // Standard Logo
            $SectionLogo->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                '165px', '50px'))
                ->styleAlignRight()
//                ->styleMarginTop('6px')
                ->styleMarginBottom('20px')
                , '26%');
        } else {
            // Standard Logo
            $SectionLogo->addElementColumn((new Element())
            ->setContent('&nbsp;')
                , '26%');
        }

        // large Schoolname
        if (strlen($SchoolName) > 60) {
            $SectionSchoolName
                ->addElementColumn((new Element())
                    ->setContent('Name der Schule:')
                    ->styleMarginTop('20px')
                    ->styleTextSize($isPrimarySchool ? '14px' : '11.5pt')
                    , '20%')
                ->addElementColumn((new Element())
                    ->setContent($SchoolName . ($secondLine != '' ? '<br>' . $secondLine : ''))
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                    ->styleMarginBottom('10px')
                    ->styleTextSize($isPrimarySchool ? '14px' : '11.5pt')
                    , '80%');
        } else {
            $SectionSchoolName
                ->addElementColumn((new Element())
                    ->setContent('Name der Schule:')
                    ->styleMarginTop('20px')
                    ->styleTextSize($isPrimarySchool ? '14px' : '11.5pt')
                    , '20%')
                ->addElementColumn((new Element())
                    ->setContent($SchoolName . ($secondLine != '' ? '<br>' . $secondLine : ''))
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    ->styleAlignCenter()
                    ->styleTextSize($isPrimarySchool ? '14px' : '11.5pt')
                    ->styleMarginTop('20px')
                    , '60%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;' . ($secondLine != '' ? '<br>' . '&nbsp;' : ''))
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    ->styleMarginTop('20px')
                    ->styleTextSize($isPrimarySchool ? '14px' : '11.5pt')
                    ->styleMarginBottom('10px')
                    , '20%');
        }

        if($this->isSample()){
            $SectionTitle->addElementColumn((new Element\Sample())
                ->styleTextSize('30px')
                ->styleHeight('0px')
                ->styleAlignLeft()
            );
        }

        if($this->isSample()){
            return $Head->addSectionList(array($SectionLogo, $SectionSchoolName, $SectionTitle));
        } else {
            return $Head->addSectionList(array($SectionLogo, $SectionSchoolName));
        }
    }

    /**
     * @param string $HeadLine
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCertificateHeadConsumer($HeadLine = '', $MarginTop = '15px')
    {
        $CertificateSlice = (new Slice());
        $CertificateSlice->addElement((new Element())
            ->setContent($HeadLine)
            ->styleTextSize('18px')
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
    protected function getDivisionAndYearConsumer($personId, $MarginTop = '20px', $YearString = 'Schuljahr')
    {
        $YearDivisionSlice = (new Slice());
        $YearDivisionSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klasse:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
//                ->styleBorderBottom('1px', self::COLOR_GREEN)
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
//                ->styleBorderBottom('1px', self::COLOR_GREEN)
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '13%')
        )->styleMarginTop($MarginTop);
        return $YearDivisionSlice;
    }

    /**
     * @param $personId
     * @param bool $withBirthday
     *
     * @return Slice
     */
    protected function getStudentNameConsumer($personId, $withBirthday = false)
    {
        $StudentSlice = (new Slice());
        if ($withBirthday) {
            $StudentSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorname und Name:')
                    , '21%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    , '41%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                , '10%')
                ->addElementColumn((new Element())
                    ->setContent('Geburtsdatum:')
                    , '15%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    , '13%')
            )->styleMarginTop('5px');
        } else {
            $StudentSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorname und Name:')
                    , '21%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                              {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleBorderBottom('1px', self::COLOR_GREEN)
                    , '79%')
            )->styleMarginTop('5px');
        }

        return $StudentSlice;
    }

    protected function getGradeInfo()
    {
        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('(ergibt sich aus den Bereichen Teamfähigkeit, Kommunikation, Anstrengungsbereitschaft,
            Konzentration und Ruhe sowie Zuverlässigkeit und Vertrauen)')
            ->styleMarginTop('10px')
        );
        return $Slice;
    }

    /**
     * @param $personId
     * @param $Height
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getDescriptionConsumer($personId, $Height, $MarginTop = '15px')
    {
        $DescriptionSlice = (new Slice());
        $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                            Bemerkungen: {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                        {% else %}
                            Bemerkungen: &nbsp;
                        {% endif %}'
                    ))
            )->styleMarginTop($MarginTop)
            ->styleHeight($Height);

        return $DescriptionSlice;
    }

    /**
     * @param $personId
     * @param bool $isMissing
     *
     * @return Slice
     */
    protected function getDescriptionHeadConsumer($personId, $isMissing = false, $MarginTop = '15px', $Content = 'Bemerkungen:')
    {
        $DescriptionSlice = (new Slice());
        if ($isMissing) {
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($Content)
                    , '16%')
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
//                    ->styleBorderBottom('1px')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
//                    ->styleBorderBottom('1px')
                    ->styleAlignCenter()
                    , '4%')
            )
                ->styleMarginTop($MarginTop);
        } else {
            // #SSW-1018 Das Wort Bemerkung auf der Vorlage entfernen, nachfolgender Textbeginn bleibt wo er ist
            $DescriptionSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($Content))
            )->styleMarginTop($MarginTop);
        }
        return $DescriptionSlice;
    }

    /**
     * @param $personId
     * @param string $Height
     * @param string $MarginTop
     * @param string $PreRemark
     * @param string|bool $TextSize
     * @return Slice
     */
    protected function getDescriptionContentConsumer($personId, $Height = '150px', $MarginTop = '0px', $PreRemark = '', $TextSize = false)
    {

        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $Element = (new Element());
        $Element->setContent($PreRemark.
            '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleHeight($Height)
            ->styleMarginTop($MarginTop);

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
     *
     * @return Slice
     */
    protected function getTransferConsumer($personId, $MarginTop = '5px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Versetzungsvermerk:')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                                        {{ Content.P' . $personId . '.Input.Transfer }}.
                                    {% else %}
                                          &nbsp;
                                    {% endif %}')
                ->styleBorderBottom('1px')
                , '58%')
            ->addElementColumn((new Element())
                , '20%')
        )
            ->styleMarginTop($MarginTop);
        return $TransferSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getDateLineConsumer($personId, $MarginTop = '25px')
    {
        $DateSlice = (new Slice());
        $DateSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum:')
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                ->styleBorderBottom('1px', '#000')
                ->styleAlignCenter()
                , '23%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        return $DateSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getBottomLineConsumer($MarginTop = '15px')
    {

        $Slice = (new Slice())->addElement((new Element())
            ->styleBorderBottom('5px', self::COLOR_GREEN)
            ->styleMarginTop($MarginTop)
        );
        return $Slice;
    }

    /**o
     * @param $personId
     *
     * @return Slice
     */
    protected function getCourseConsumer($personId)
    {

        $Slice = (new Slice())
            ->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Student.Course.Degree is not empty) %}
                    nahm am Unterricht mit dem Ziel des
                    {{ Content.P' . $personId . '.Student.Course.Degree }} teil.
                {% else %}
                    &nbsp;
                {% endif %}'
                )
                ->styleMarginTop('5px')
                ->styleHeight('18px')
            );
        return $Slice;
    }

    /**
     * @param $personId
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSignPartConsumer($personId, $isExtended = true, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
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
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
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
                        , '30%')
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
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
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
                        , '30%')
                );
        } else {
            $SignSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderBottom('1px', '#000')
                    , '30%')
            )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
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
                        , '30%')
                );
        }
        return $SignSlice;
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getOwnSignPartConsumer($personId, $MarginTop = '25px')
    {
        $SignSlice = (new Slice());

        $SignSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('Dienstsiegel')
                ->styleAlignCenter()
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom('1px', '#000')
                , '30%')
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
//                    ->styleTextSize('11px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('der Schule')
                    ->styleAlignCenter()
//                        ->styleTextSize('11px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if(Content.P' . $personId . '.Tudor.Description is not empty) %}
                                {{ Content.P' . $personId . '.Tudor.Description }}
                            {% else %}
                                Tutor(in)
                            {% endif %}'
                    )
                    ->styleAlignCenter()
//                        ->styleTextSize('11px')
                    , '30%')
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
//                        ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                    )
//                        ->styleTextSize('11px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
            );

        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getParentSignConsumer($MarginTop = '25px')
    {
        $ParentSlice = (new Slice());
        $ParentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '40%')
            ->addElementColumn((new Element())
                , '30%')
        )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Eltern')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->styleMarginTop($MarginTop);
        return $ParentSlice;
    }

    /**
     * @param string $MarginTop
     * @param string $LineOne
     * @param string $LineTwo
     * @param string $LineThree
     * @param string $LineFour
     * @param string $LineFive
     *
     * @return Slice
     */
    protected function getInfoConsumer(
        $MarginTop = '10px',
        $LineOne = '',
        $LineTwo = '',
        $LineThree = '',
        $LineFour = '',
        $LineFive = ''
    ) {
        $InfoSlice = (new Slice());
        $InfoSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->styleBorderBottom()
                , '30%')
            ->addElementColumn((new Element())
                , '70%')
        )
            ->styleMarginTop($MarginTop);
        if ($LineOne !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineOne)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineTwo !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineTwo)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineThree !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineThree)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFour !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFour)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }
        if ($LineFive !== '') {
            $InfoSlice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($LineFive)
                    ->styleTextSize('9.5px')
                    , '30%')
            );
        }

        return $InfoSlice;
    }

    public function getSecondPageDescription($personId)
    {
        $textSize = '11.5pt';
        $Slice = array();
        //
        $SliceYou = (new Slice())
            ->addElement((new Element())
                ->setContent('Im Dialog mit DIR')
                ->styleMarginTop('30px')
                ->styleTextSize(self::TEXT_SIZE_BIG)
                ->styleTextBold()
                ->styleAlignCenter()
            );
        $SliceYouDescription = (new Slice())
            ->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.DialoguesWithYou is not empty) %}
                    {{ Content.P' . $personId . '.Input.DialoguesWithYou }}
                {% else %}
                    &nbsp;
                {% endif %}'
                    )
                ->styleAlignJustify()
                ->styleHeight('190px')
                ->styleMarginTop('15px')
                ->styleTextSize($textSize)
            );

        //
        $SliceParent = (new Slice())
            ->addElement((new Element())
                ->setContent('Im Dialog mit deinen ELTERN')
                ->styleMarginTop('15px')
                ->styleTextSize(self::TEXT_SIZE_BIG)
                ->styleTextBold()
                ->styleAlignCenter()
            );
        $SliceParentDescription = (new Slice())
            ->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.DialoguesWithParent is not empty) %}
                    {{ Content.P' . $personId . '.Input.DialoguesWithParent }}
                {% else %}
                    &nbsp;
                {% endif %}'
                )
                ->styleAlignJustify()
                ->styleHeight('190px')
                ->styleMarginTop('15px')
                ->styleTextSize($textSize)
            );

        //
        $SliceUs = (new Slice())
            ->addElement((new Element())
                ->setContent('Im Dialog mit UNS')
                ->styleMarginTop('15px')
                ->styleTextSize(self::TEXT_SIZE_BIG)
                ->styleTextBold()
                ->styleAlignCenter()
            );
        $SliceUsDescription = ((new Slice())
            ->addElement((new Element())
                ->setContent('
                {% if(Content.P' . $personId . '.Input.DialoguesWithUs is not empty) %}
                    {{ Content.P' . $personId . '.Input.DialoguesWithUs }}
                {% else %}
                    &nbsp;
                {% endif %}'
                )
                ->styleAlignJustify()
                ->styleHeight('190px')
                ->styleMarginTop('15px')
                ->styleMarginBottom('10px')
                ->styleTextSize($textSize)
            )
        );

        $Slice[] = $SliceYou;
        $Slice[] = $SliceYouDescription;
        $Slice[] = $SliceParent;
        $Slice[] = $SliceParentDescription;
        $Slice[] = $SliceUs;
        $Slice[] = $SliceUsDescription;

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public function getMissingConsumer($personId)
    {
        return (new Slice())
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
            ->styleMarginTop('15px');
    }
}