<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.05.2018
 * Time: 08:20
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Text\Repository\Sup;

/**
 * Class GymAbg
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class GymAbgSekII extends Certificate
{

    /**
     * @return array
     */
    public static function getLeaveTerms()
    {
        return array(
            1 => "während des Kurshalbjahres",
            2 => "am Ende des Kurshalbjahres"
        );
    }

    /**
     * @return array
     */
    public static function getMidTerms()
    {
        return array(
            1 => '11/1',
            2 => '11/2',
            3 => '12/1',
            4 => '12/2'
        );
    }

    /**
     * @var array|false
     */
    private $AdvancedCourses = false;

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample());

        $pageList[] = (new Page());

        $pageList[] = (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('ABGANGSZEUGNIS')
                    ->styleTextSize('27px')
                    ->styleAlignCenter()
                    ->styleMarginTop('28%')
                    ->styleTextBold()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('des Gymnasiums')
                    ->styleTextSize('22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                )
            )->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('(gymnasiale Oberstufe)')
                    ->styleTextSize('22px')
                    ->styleAlignCenter()
                    ->styleMarginTop('20px')
                )
            );

        $leaveTerm = 'während/am Ende des Kurshalbjahres';
        $leaveTermWidth = '58%';
        $midTerm = '/';

        if ($tblPerson
            && ($tblDivision = $this->getTblDivision())
            && ($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblDivision))
        ){
            if (($leaveTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'LeaveTerm'))) {
                $leaveTerm = $leaveTermInformation->getValue();
                $leaveTermWidth = '50%';
            }
            if (($midTermInformation = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'MidTerm'))) {
                $midTerm = $midTermInformation->getValue();
            }
        }

        $advancedSubjects = '&nbsp;';
        if ($tblPerson) {
            $this->setCourses($tblPerson);
            if ($this->AdvancedCourses) {
                $advancedSubjects = implode(', ', $this->AdvancedCourses);
            }
        }

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Zuname')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('in')
                        ->styleAlignCenter()
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('25px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('wohnhaft in')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Person.Address.City.Name) %}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Name }}
                                    {{ Content.P' . $personId . '.Person.Address.Street.Number }},
                                    {{ Content.P' . $personId . '.Person.Address.City.Code }}
                                    {{ Content.P' . $personId . '.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('25px')

            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('verlässt das Gymnasium ' . $leaveTerm)
                        ->styleMarginTop('40px')
                    , $leaveTermWidth)
                    ->addElementColumn((new Element())
                        ->setContent($midTerm)
                        ->styleAlignCenter()
                        ->styleMarginTop('40px')
                        ->styleBorderBottom()
                        , '15%')
                    ->addElementColumn((new Element())
                        ->styleMarginTop('40px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('und belegte in der gymnasialen Oberstufe Leistungskurse in den Fächern')
                        ->styleMarginTop('30px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent($advancedSubjects)
                        ->styleBorderBottom()
                        ->styleMarginTop('30px')
                    , '99%')
                    ->addElementColumn((new Element())
                        ->setContent('.')
                        ->styleMarginTop('30px')
                    , '1%')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Sie
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Er
                                {% else %}
                                    Sie/Er
                                {% endif %}
                            {% endif %}
                            hat die Vollzeitschulpflicht gemäß § 28 Absatz 1 Nummer 1, Absatz 2 des Sächsischen
                            Schulgesetzes erfüllt.
                        ')
                        ->styleMarginTop('30px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
                                Frau
                            {% else %}
                                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
                                    Herr
                                {% else %}
                                    Frau/Herr
                                {% endif %}
                            {% endif %}
                            <u>&nbsp;&nbsp;&nbsp;&nbsp; {{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }} &nbsp;&nbsp;&nbsp;&nbsp;</u>
                            hat gemäß § 7 Absatz 7 Satz 2 des Sächsischen Schulgesetzes mit dem Versetzungszeugnis von
                            Klassenstufe 10 in die Jahrgangsstufe 11 des Gymnasiums
                            einen dem Realschulabschluss gleichgestellten mittleren Schulabschluss erworben.
                        ')
                        ->styleMarginTop('40px')
                    )
                )
            )
            ->addSlice((new Slice)
                ->addElement((new Element())
                    ->setContent('Bemerkungen:')
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice($this->getDescriptionContent($personId, '200px'))
            ->addSlice($this->getSchoolName($personId, '30px'))
            ->addSlice($this->setPointsOverview('120px'))
        ;

        $textSize = '13px';
        $padding = '8.5px';
        $padding2 = '7.5px';

        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize($textSize)
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fach')
                                ->stylePaddingLeft('3px')
                                ->styleTextSize($textSize)
                                ->styleTextBold()
                            , '50%')
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Fremdsprache')
                                        ->styleTextSize($textSize)
                                        ->stylePaddingLeft('3px')
                                    )
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('&nbsp;')
                                        ->styleTextSize($textSize)
                                    )
                                )
                                ->styleBackgroundColor('lightgrey')
                                ->styleBorderTop()
                                ->styleBorderLeft()
                            , '50%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('&nbsp;')
                                ->styleTextSize($textSize)
                                ->styleTextBold()
                                , '50%')
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('von')
                                        ->styleAlignCenter()
                                        ->styleTextSize($textSize)
                                        ->stylePaddingTop($padding)
                                        ->stylePaddingBottom($padding2)
                                    )
                                    ->addElementColumn((new Element())
                                        ->setContent('bis')
                                        ->styleAlignCenter()
                                        ->styleTextSize($textSize)
                                        ->stylePaddingTop($padding)
                                        ->stylePaddingBottom($padding2)
                                    )
                                )
                                ->styleBackgroundColor('lightgrey')
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '50%')
                        )
                        ->styleBorderTop()
                        ->styleBorderLeft()
                        ->styleMarginTop('15px')
                    , '44%')
                ->addSliceColumn(
                    (new Slice)
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Punktzahlen')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 11')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '50%')
                            ->addElementColumn((new Element())
                                ->setContent('Jahrgangsstufe 12')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop($padding)
                                ->stylePaddingBottom($padding)
                                , '50%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('1. Halb-')
                                ->styleTextSize($textSize)
                                ->styleBorderTop()
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('1. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('2. Halb-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderTop()
                                ->styleBorderLeft()
                                , '25%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('jahr')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                , '25%')
                        )
                        ->styleBorderLeft()
                        ->styleBorderTop()
                        ->styleMarginTop('15px')
                    , '36%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Durch-')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop('18px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('schnitt¹')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingBottom('31.5px')
                            )
                        )
                        ->styleBorderTop()
                        ->styleMarginTop('15px')
                    , '8%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Abgangsnote²')
                                ->styleTextSize($textSize)
                                ->styleAlignCenter()
                                ->styleBorderLeft()
                                ->stylePaddingTop('25px')
                                ->stylePaddingBottom('40.5px')
                            )
                        )
                        ->styleBorderTop()
                        ->styleBorderRight()
                        ->styleMarginTop('15px')
                     )
                )
            ->addSection($this->setFieldRow('Sprachlich-literarisch-künstlerisches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Deutsch'))
            ->addSection($this->setSubjectRow($personId, 'Sorbisch'))
            ->addSection($this->setSubjectRow($personId, 'Englisch', true))
            ->addSection($this->setSubjectRow($personId, 'Französisch', true))
            ->addSection($this->setSubjectRow($personId, 'Griechisch', true))
            ->addSection($this->setSubjectRow($personId, 'Latein', true))
            ->addSection($this->setSubjectRow($personId, 'Polnisch', true))
            ->addSection($this->setSubjectRow($personId, 'Russisch', true))
            ->addSection($this->setSubjectRow($personId, 'Spanisch', true))
            ->addSection($this->setSubjectRow($personId, 'Tschechisch', true))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, 'Kunst'))
            ->addSection($this->setSubjectRow($personId, 'Musik'))
            ->addSection($this->setFieldRow('Gesellschaftswissenschaftliches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Geschichte'))
            ->addSection($this->setSubjectRow($personId, 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft'))
            ->addSection($this->setSubjectRow($personId, 'Geographie'))
            ->addSection($this->setFieldRow('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld'))
            ->addSection($this->setSubjectRow($personId, 'Mathematik'))
            ->addSection($this->setSubjectRow($personId, 'Biologie'))
            ->addSection($this->setSubjectRow($personId, 'Chemie'))
            ->addSection($this->setSubjectRow($personId, 'Physik'))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, 'RELIGION'))
            ->addSection($this->setSubjectRow($personId, 'Sport'))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setFieldRow())
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId))
            ->addSection($this->setSubjectRow($personId, '&nbsp;', false, true))
        ;

        $pageList[] = (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    )
                )->styleMarginTop('60px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($slice)
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}
                            ')
                        ->styleMarginTop('70px')
                        ->styleBorderBottom()
//                        ->styleTextSize($textSize)
                        , '35%')
                    ->addElementColumn((new Element()))
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                Ort, Datum
                            ')
                        ->styleTextSize($textSize)
                        ->styleMarginTop('0px')
                        , '35%')
                    ->addElementColumn((new Element()))
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '35%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                        )
                    ->addElementColumn((new Element())
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
                        ->styleTextSize($textSize)
                        , '35%')
                    ->addElementColumn((new Element())
                        ->setContent('der Schule')
                        ->styleTextSize($textSize)
                        ->styleAlignCenter()
                    )
                    ->addElementColumn((new Element())
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
                        ->styleTextSize($textSize)
                    , '35%')
                )
                ->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('55px')
                ->addSection($this->setInfoRow(1, 'Ist das arithmetische Mittel der in den Kurshalbjahren erreichten Punktzahlen nicht ganzzahlig, so wird auf die nächstgrößere ganze Zahl gerundet.'))
                ->addSection($this->setInfoRow(2, 'Aus dem Punktzahldurchschnitt ergibt sich die Abgangsnote gemäß Tabelle auf Seite 2.'))
                ->addSection($this->setInfoRow(3, 'An Gymnasien gemäß § 38 Absatz 2 der Schulordnung Gymnasien Abiturprüfung sind die Fächer Ev./Kath. Religion dem gesellschaftswissenschaftlichen
                    Aufgabenfeld zugeordnet.'))
            )
            ;

        return $pageList;
    }

    /**
     * @param string $name
     * @param bool $isBold
     *
     * @return Section
     */
    private function setFieldRow($name = '&nbsp;', $isBold = true)
    {

        $textSize = '13px';
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($name)
                ->stylePaddingLeft('3px')
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->styleTextSize($textSize)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
            );
    }

    private function setSubjectRow($personId, $subjectName = '&nbsp;', $isForeignLanguage = false, $isLastRow = false)
    {

        $textSize = '13px';
        $colorPoints = '#BBB';
        $colorForeignLanguage = 'lightgrey';

        $tblLeaveStudent = false;
        $tblPerson = Person::useService()->getPersonById($personId);

        $postFix = '';
        // tatsächliche Religion aus der Schülerakte bestimmen
        if ($subjectName == 'RELIGION') {
            $subjectName = 'Ev./Kath. Religion/Ethik';
            if ($tblPerson
                && ($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
                && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1'))
                && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                    $tblStudentSubjectType, $tblStudentSubjectRanking))
                && ($tblReligionSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                $subjectName = $tblReligionSubject->getName();

                $postFix = '³';
            }
        }

        $grades = array(
            '11-1' => '&ndash;',
            '11-2' => '&ndash;',
            '12-1' => '&ndash;',
            '12-2' => '&ndash;',
        );

        $tblSubject = Subject::useService()->getSubjectByName($subjectName);
        if (!$tblSubject && $subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('GRW');
        }

        $subjectName .= $postFix;

        if ($tblPerson
            && ($tblDivision = $this->getTblDivision())
            && ($tblLeaveStudent = Prepare::useService()->getLeaveStudentBy($tblPerson, $tblDivision))
            && $tblSubject
        ) {
            for ($level = 11; $level < 13; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        $value = str_pad($tblLeaveAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                        $grades[$midTerm] = $value;
                    }
                }
            }
        }

        $points = '&ndash;';
        $finalGrade = '&ndash;';
        if ($tblLeaveStudent && $tblSubject) {
            $points = Prepare::useService()->calcAbiturLeaveGradePointsBySubject($tblLeaveStudent, $tblSubject);
            $finalGrade = Prepare::useService()->getAbiturLeaveGradeBySubject($points);
        }

        $section = new Section();
        if ($isForeignLanguage) {

            $levelFrom = '&ndash;';
            $levelTill = '&ndash;';

            if ($tblPerson
                && $tblSubject
                && ($tblStudent = $tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
            ) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())
                        && $tblSubjectItem->getId() == $tblSubject->getId()
                    ) {
                        if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())) {
                            $levelFrom = $tblLevelFrom->getName();
                        }

                        if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill())) {
                            $levelTill = $tblLevelTill->getName();
                        }

                        break;
                    }
                }
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent($subjectName)
                    ->stylePaddingLeft('3px')
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '22%')
                ->addElementColumn((new Element())
                    ->setContent($levelFrom)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBackgroundColor($colorForeignLanguage)
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '11%')
                ->addElementColumn((new Element())
                    ->setContent($levelTill)
                    ->styleAlignCenter()
                    ->styleTextSize($textSize)
                    ->styleBorderTop()
                    ->styleBackgroundColor($colorForeignLanguage)
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '11%');;
        } else {
            $section
                ->addElementColumn((new Element())
                    ->setContent($subjectName)
                    ->styleTextSize($textSize)
                    ->stylePaddingLeft('3px')
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderBottom($isLastRow ? '1px' : '0px')
                    , '44%');
        }

        $section
            ->addElementColumn((new Element())
                ->setContent($grades['11-1'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['11-2'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-1'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($grades['12-2'])
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '9%')
            ->addElementColumn((new Element())
                ->setContent($points)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
                , '8%')
            ->addElementColumn((new Element())
                ->setContent($finalGrade)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBackgroundColor($colorPoints)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleBorderBottom($isLastRow ? '1px' : '0px')
            );

        return $section;
    }

    /**
     * @param $i
     * @param $text
     *
     * @return Section
     */
    private function setInfoRow($i, $text)
    {
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent(new Sup($i))
                ->styleTextSize('7px')
                ->styleMarginTop('2px')
                , '3%')
            ->addElementColumn((new Element())
                ->setContent($text)
                ->styleTextSize('9.5px')
                ->styleMarginTop('2px')
            );

        return $section;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    private function setPointsOverview($MarginTop = '25px')
    {

        $textSize = '10px';
        $slice = new Slice();
        $slice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Für die Umsetzung der Noten in Punkte gilt:')
                    ->styleTextSize($textSize)
                )
            );

        $section = new Section();
        $this->setColumnElement($section, 'Notenstufen', $textSize);
        $this->setColumnElement($section, 'sehr gut', $textSize);
        $this->setColumnElement($section, 'gut', $textSize);
        $this->setColumnElement($section, 'befriedigend', $textSize);
        $this->setColumnElement($section, 'ausreichend', $textSize);
        $this->setColumnElement($section, 'mangelhaft', $textSize);
        $this->setColumnElement($section, 'ungenügend', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Noten', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;2&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;3&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;4&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '+&nbsp;&nbsp;&nbsp;5&nbsp;&nbsp;&nbsp;-', $textSize);
        $this->setColumnElement($section, '6', $textSize, false, true);
        $slice
            ->addSection($section);

        $section = new Section();
        $this->setColumnElement($section, 'Punkte', $textSize, true);
        $this->setColumnElement($section, '15 14 13', $textSize, true);
        $this->setColumnElement($section, '12 11 10', $textSize, true);
        $this->setColumnElement($section, '09 08 07', $textSize, true);
        $this->setColumnElement($section, '06 05 04', $textSize, true);
        $this->setColumnElement($section, '03 02 01', $textSize, true);
        $this->setColumnElement($section, '00', $textSize, true, true);
        $slice
            ->addSection($section);

        return $slice
            ->styleMarginTop($MarginTop);
    }

    /**
     * @param Section $section
     * @param string $name
     * @param $textSize
     * @param bool $isBorderBottom
     * @param bool $isBorderRight
     */
    private function setColumnElement(
        Section $section,
        $name,
        $textSize,
        $isBorderBottom = false,
        $isBorderRight = false
    ) {

        $section
            ->addElementColumn((new Element())
                ->setContent($name)
                ->styleTextSize($textSize)
                ->styleAlignCenter()
                ->styleBorderLeft()
                ->styleBorderTop()
                ->styleBorderRight($isBorderRight ? '1px' : '0px')
                ->styleBorderBottom($isBorderBottom ? '1px' : '0px')
                , '14.28%');
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    private function setCourses(TblPerson $tblPerson = null)
    {

        $advancedCourses = array();
        if ($tblPerson && ($tblDivision = $this->getTblDivision())
            && ($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))
        ) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubjectGroup->isAdvancedCourse()) {
                                    if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                        $advancedCourses[0] = $tblSubject->getName();
                                    } else {
                                        $advancedCourses[1] = $tblSubject->getName();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($advancedCourses)) {
            ksort($advancedCourses);
            $this->AdvancedCourses = $advancedCourses;
        }
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getSchoolName($personId, $MarginTop = '20px')
    {

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed'))
            && $tblSetting->getValue()
        ) {
            $isSchoolExtendedNameDisplayed = true;
        } else {
            $isSchoolExtendedNameDisplayed = false;
        }
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator'))
            && $tblSetting->getValue()
        ) {
            $separator = $tblSetting->getValue();
        } else {
            $separator = false;
        }
        $name = '&nbsp;';
        $address = '&nbsp;';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $this->getTblDivision() ? $this->getTblDivision() : null))
        ) {
            $name = $isSchoolExtendedNameDisplayed ? $tblCompany->getName() .
                ($separator ? ' ' . $separator . ' ' : ' ') . $tblCompany->getExtendedName() : $tblCompany->getName();

            if (($mainAddress = $tblCompany->fetchMainAddress())) {
                $address = $mainAddress->getGuiString();
            }
        }

        $schoolSlice = (new Slice());
        $schoolSlice
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($name)
                    ->styleAlignCenter()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($address)
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name und Anschrift der Schule')
                    ->styleAlignCenter()
                )
            )
        ;

        return $schoolSlice;
    }
}