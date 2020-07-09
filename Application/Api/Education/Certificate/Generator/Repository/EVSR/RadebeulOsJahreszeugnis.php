<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.11.2018
 * Time: 10:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;

class RadebeulOsJahreszeugnis extends Certificate
{
    const TEXT_COLOR_BLUE = 'rgb(25,59,100)';
    const TEXT_COLOR_RED = 'rgb(202,23,63)';
    const TEXT_SIZE = '11pt';
    const FONT_FAMILY = 'MetaPro';
    const LINE_HEIGHT = '85%';

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
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice(self::getHeader('Jahreszeugnis'))
            ->addSliceArray($this->getBody($personId, true));
    }

    /**
     * @param $name
     * @param string $schoolType
     * @param string $extra
     *
     * @return Slice
     */
    public static function getHeader($name, $schoolType = '- Oberschule -', $extra = 'genehmigte')
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '10%')
                ->addSliceColumn((new Slice())
                    ->styleMarginTop('15px')
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement(' Evangelisches Schulzentrum Radebeul', '26px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement($schoolType, '22px', '10px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                'Staatlich ' . $extra . ' Ersatzschule in freier Trägerschaft'
                            )
                            ->styleMarginTop('-4px')
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->styleTextSize('15px')
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                'im Freistaat Sachsen'
                            )
                            ->styleMarginTop('-4px')
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->styleTextSize('15px')
                            ->styleAlignCenter()
                            ->styleFontFamily(self::FONT_FAMILY)
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn(
                            self::getHeaderElement($name, '32px', '20px')
                        )
                    )
                )
                ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSR.jpg',
                    '80px', '80px'))
                    ->styleMarginTop('30px')
                    ->styleAlignCenter()
                    , '10%')
            );
    }

    /**
     * @param $content
     * @param string $textSize
     * @param string $marginTop
     * @param bool $isBold
     *
     * @return Element
     */
    private static function getHeaderElement($content, $textSize = '22pt', $marginTop = '13px', $isBold = true)
    {

        return (new Element())
            ->setContent($content)
            ->styleAlignCenter()
            ->styleTextSize($textSize)
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleLineHeight(self::LINE_HEIGHT)
            ->styleMarginTop($marginTop)
            ->styleTextColor(self::TEXT_COLOR_RED);
    }

    /**
     * @param $personId
     * @param bool $hasTransfer
     *
     * @return Slice[]
     */
    public function getBody($personId, $hasTransfer)
    {
        // zusammen 100%
        $width1 = '20%';
        $width2 = '45%';
        $width3 = '4%';
        $width4 = '15%';
        $width5 = '16%';

        $sliceArray = array();

        $sliceArray[] = (new Slice())
            ->styleMarginTop('15px')
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'Vor- und Zuname:'
                ), $width1)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}'
                    , true
                ), $width2)
                ->addElementColumn((new Element()
                ), $width3)
                ->addElementColumn(self::getBodyElement(
                    'Klasse:'
                ), $width4)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Division.Data.Level.Name }} {{ Content.P' . $personId . '.Division.Data.Name }}'
                    , true
                ), $width5)
            )
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'geboren am:'
                ), $width1)
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                        {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                    {% else %}
                        &nbsp;
                    {% endif %}'
                    , true
                ), $width2)
                ->addElementColumn((new Element()
                ), $width3)
                ->addElementColumn(self::getBodyElement(
                    'Schuljahr:'
                ), $width4)
                ->addElementColumn(self::getBodyElement(
                    '{{ Content.P' . $personId . '.Division.Data.Year }}'
                    , true
                ), $width5)
            );

        $course = 'nahm am Unterricht der Schulart Mittelschule teil.';
        if (($tblDivision = $this->getTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && intval($tblLevel->getName()) > 6
            && ($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblCourse = $tblStudent->getCourse())
        ) {
            if ($tblCourse->getName() == 'Realschule') {
                $course = 'nahm am Unterricht der Schulart Mittelschule mit dem Ziel des Realschulabschlusses teil.';
            } elseif ($tblCourse->getName() == 'Hauptschule') {
                $course = 'nahm am Unterricht der Schulart Mittelschule mit dem Ziel des Hauptschulabschlusses teil.';
            }
        }

        $sliceArray[] = (new Slice)
            ->addElement(self::getBodyElement($course));

        $sliceArray[] = $this->getGradeLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt'
        );

        $sliceArray[] = (new Slice())
            ->addElement(self::getBodyElement('Leistung in den einzelnen Fächern:', true, '10px'));

        $sliceArray[] = $this->getSubjectLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt',
            'rgb(224,226,231)',
            false,
            '8px',
            28,
            self::FONT_FAMILY,
            '205px'
        );

        $sliceArray[] = self::getOrientation($personId);

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement(
                    'Bemerkungen:', true, '0px'
                ))
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                        {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleFontFamily(self::FONT_FAMILY)
                    ->styleLineHeight(self::LINE_HEIGHT)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('0px')
                    ->styleHeight($hasTransfer ? '85px' : '115px'))
            );

        if ($hasTransfer) {
            $sliceArray[] = (new Slice)
                ->addSection((new Section())
                    ->addElementColumn(self::getBodyElement('Versetzungsvermerk:')
                        , '22%')
                    ->addElementColumn(self::getBodyElement(
                        '{% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P' . $personId . '.Input.Transfer }}.
                        {% else %}
                              &nbsp;
                        {% endif %}'
                    ))
                );
        }

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement('Fehltage entschuldigt:'), '25%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        0
                    {% endif %}')
                    , '10%')
                ->addElementColumn(self::getBodyElement('unentschuldigt:'), '19%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        0
                    {% endif %}')
                )
            );

        $sliceArray[] = (new Slice)
            ->addSection((new Section())
                ->addElementColumn(self::getBodyElement('Datum:'), '15%')
                ->addElementColumn(self::getBodyElement(
                    '{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                        {{ Content.P' . $personId . '.Input.Date }}
                    {% else %}
                        0
                    {% endif %}')
                )
            );

        $sliceArray[] = self::getSignIndividualPart($personId);

        return $sliceArray;
    }

    /**
     * @param $content
     * @param bool $isBold
     * @param string $marginTop
     *
     * @return Element
     */
    private static function getBodyElement($content, $isBold = false, $marginTop = '7px')
    {

        return (new Element())
            ->setContent($content)
            ->styleTextSize(self::TEXT_SIZE)
            ->styleTextBold($isBold ? 'bold' : 'normal')
            ->styleFontFamily(self::FONT_FAMILY)
            ->styleTextColor(self::TEXT_COLOR_BLUE)
            ->styleMarginTop($marginTop);
    }

    /**
     * @param $personId
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private static function getOrientation($personId, $TextSize = self::TEXT_SIZE, $IsGradeUnderlined = false)
    {

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;

        $subjectWidth = 72;
        $gradeWidth = 28;
        $gradeColor = 'rgb(224,226,231)';

        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblStudent = $tblPerson->getStudent())
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {

                    if (($tblSetting = Consumer::useService()->getSetting('Api', 'Education', 'Certificate',
                            'OrientationAcronym'))
                        && ($value = $tblSetting->getValue())
                    ) {
                        $subjectAcronymForGrade = $value;
                    } else {
                        $subjectAcronymForGrade = $tblSubject->getAcronym();
                    }

                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.P' . $personId . '.Student.Orientation["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.Student.Orientation["' . $tblSubject->getAcronym() . '"].Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextColor(self::TEXT_COLOR_BLUE)
                        ->stylePaddingTop('-3px')
                        ->stylePaddingBottom('2px')
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $subjectAcronymForGrade . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                        ->styleFontFamily(self::FONT_FAMILY)
                        ->styleTextColor(self::TEXT_COLOR_BLUE)
                        ->styleAlignCenter()
                        ->styleBackgroundColor($gradeColor)
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', self::TEXT_COLOR_BLUE)
                        ->stylePaddingTop('-4px')
                        ->stylePaddingBottom('2px')
                        ->styleTextSize($TextSize);
                }
            }

            // 2. Fremdsprache
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getTblStudentSubjectRanking()
                        && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                        && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        $elementForeignLanguageName = new Element();
                        $elementForeignLanguageName
                            ->setContent('
                            {% if(Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                 {{ Content.P' . $personId . '.Student.ForeignLanguage["' . $tblSubject->getAcronym() . '"].Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->stylePaddingTop('-3px')
                            ->stylePaddingBottom('2px')
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                {{ Content.P' . $personId . '.Grade.Data["' . $tblSubject->getAcronym() . '"] }}
                            {% else %}
                                &ndash;
                            {% endif %}')
                            ->styleFontFamily(self::FONT_FAMILY)
                            ->styleTextColor(self::TEXT_COLOR_BLUE)
                            ->styleAlignCenter()
                            ->styleBackgroundColor($gradeColor)
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', self::TEXT_COLOR_BLUE)
                            ->stylePaddingTop('-4px')
                            ->stylePaddingBottom('2px')
                            ->styleTextSize($TextSize);
                    }
                }
            }
        }

        // unterstrichen wird leider zu durchgestrichen
//        if ($elementOrientationName) {
//            $textCategory = '(<u>Neigungskurs</u> / 2. Fremdsprache, abschlussorientiert)';
//        } elseif ($elementForeignLanguageName) {
//            $textCategory = '(Neigungskurs / <u>2. Fremdsprache, abschlussorientiert)</u>';
//        } else {
//            $textCategory = '(Neigungskurs / 2. Fremdsprache, abschlussorientiert)';
//        }
        if ($elementOrientationName) {
            $textCategory = '(Neigungskurs / <u>2. Fremdsprache, abschlussorientiert)</u>';
        } elseif ($elementForeignLanguageName) {
            $textCategory = '(<u>Neigungskurs</u> / 2. Fremdsprache, abschlussorientiert)';
        } else {
            $textCategory = '(Neigungskurs / 2. Fremdsprache, abschlussorientiert)';
        }

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Wahlpflichtbereich:')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextColor(self::TEXT_COLOR_BLUE)
                ->styleTextBold()
                ->styleTextSize($TextSize)
                , '21%')
            ->addElementColumn((new Element())
                ->setContent($textCategory)
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextColor(self::TEXT_COLOR_BLUE)
                ->stylePaddingTop('3px')
                ->styleTextSize('9pt')
            );
        $sectionList[] = $section;

        if ($elementOrientationName) {
            $section = new Section();
            $section
                ->addElementColumn($elementOrientationName, (string)$subjectWidth . '%')
                ->addElementColumn($elementOrientationGrade, (string)$gradeWidth . '%');
            $sectionList[] = $section;
        } elseif ($elementForeignLanguageName) {
            $section = new Section();
            $section
                ->addElementColumn($elementForeignLanguageName, (string)$subjectWidth . '%')
                ->addElementColumn($elementForeignLanguageGrade, (string)$gradeWidth . '%');
            $sectionList[] = $section;
        } else {
            $elementName = (new Element())
                ->setContent('&nbsp;')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextColor(self::TEXT_COLOR_BLUE)
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('&ndash;')
                ->styleFontFamily(self::FONT_FAMILY)
                ->styleTextColor(self::TEXT_COLOR_BLUE)
                ->styleAlignCenter()
                ->styleBackgroundColor($gradeColor)
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', self::TEXT_COLOR_BLUE)
                ->stylePaddingTop('-4px')
                ->stylePaddingBottom('2px')
                ->styleTextSize($TextSize);

            $section = new Section();
            $section
                ->addElementColumn($elementName
                    , (string)$subjectWidth . '%')
                ->addElementColumn($elementGrade
                    , (string)$gradeWidth . '%');
            $sectionList[] = $section;
        }

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    private static function getSignIndividualPart($personId)
    {

        $textSize = self::TEXT_SIZE;
        $fontFamily = self::FONT_FAMILY;

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('10px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Dienstsiegel der Schule')
                    ->styleAlignCenter()
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('20px')
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('10px')
                    , '30%')
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
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleTextSize('10px')
                    , '30%')
                ->addElementColumn((new Element())
                    , '40%')
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleTextSize('10px')
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
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
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
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    ->styleTextSize($textSize)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize($textSize)
                    ->styleBorderBottom('1px', self::TEXT_COLOR_BLUE)
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                    , '70%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Personensorgeberechtigte/r')
                    ->styleAlignCenter()
                    ->styleMarginTop('-3px')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    , '40%')
                ->addElementColumn((new Element())
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Notenstufen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend,
                                5 = mangelhaft, 6 = ungenügend')
                    ->styleTextSize('10px')
                    ->styleFontFamily($fontFamily)
                    ->styleTextColor(self::TEXT_COLOR_BLUE)
                    ->styleMarginTop('15px')
                )
            );
    }
}