<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class GradebookOverview
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview
 */
class GradebookOverview extends AbstractDocument
{

    // inclusive average
    const MINIMUM_GRADE_COUNT = 6;

    /**
     * GradebookOverview constructor.
     *
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     */
    function __construct(TblPerson $tblPerson, TblDivision $tblDivision)
    {
        $this->setTblPerson($tblPerson);
        $this->setTblDivision($tblDivision);
    }

    /**
     * @var TblDivision|null
     */
    private $tblDivision = null;

    /**
     * @return false|TblDivision
     */
    public function getTblDivision()
    {
        if (null === $this->tblDivision) {
            return false;
        } else {
            return $this->tblDivision;
        }
    }

    /**
     * @param false|TblDivision $tblDivision
     */
    public function setTblDivision(TblDivision $tblDivision = null)
    {

        $this->tblDivision = $tblDivision;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Notenübersicht';
    }


    /**
     * @param array $List
     *
     * @return Sorter
     */
    public function getSorter($List)
    {
        return new Sorter($List);
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice $PageHeader
     */
    public function getPageHeaderSlice()
    {
        return (new Slice())
            ->addSection((new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler: ' . ($this->getTblPerson() ? $this->getTblPerson()->getLastFirstName() : ''))
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse: ' . ($this->getTblDivision() ? $this->getTblDivision()->getDisplayName() : ''))
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Stand: ' . (new \DateTime())->format('d.m.Y'))
                        )
                    )
                    , '33%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Schülerübersicht')
                    ->styleAlignCenter()
                    ->styleTextSize('30px')
                    ->styleTextUnderline(), '34%'
                )
                ->addElementColumn((new Element())
                    ->setContent(''), '33%'
                )
            )->stylePaddingBottom('25px');
    }

    /**
     * @return Slice
     */
    public function getGradebookOverviewSlice()
    {

        if ($this->getTblDivision()
            && ($tblPerson = $this->getTblPerson())
            && ($tblYear = $this->getTblDivision()->getServiceTblYear())
        ) {

            $divisionList = array();
            if ($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson)) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    if (($tblDivisionTemp = $tblDivisionStudent->getTblDivision())
                        && ($tblYearTemp = $tblDivisionTemp->getServiceTblYear())
                        && $tblYear->getId() == $tblYearTemp->getId()
                        && !$tblDivisionStudent->isInActive()
                    ) {
                        $divisionList[$tblDivisionTemp->getId()] = $tblDivisionTemp;
                    }
                }
            }

            $data = array();
            $maxGradesPerPeriodCount = array();
            $tblLevel = $this->tblDivision->getTblLevel();
            $tblPeriodList = $tblYear->getTblPeriodAll($tblLevel && $tblLevel->getName() == '12');
            foreach ($divisionList as $tblDivision) {
                if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if ($tblDivisionSubject->getServiceTblSubject() && $tblDivisionSubject->getTblDivision()) {
                            if (!$tblDivisionSubject->getTblSubjectGroup()) {
                                $hasStudentSubject = false;
                                $tblDivisionSubjectWhereGroup =
                                    Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
                                        $tblDivision,
                                        $tblDivisionSubject->getServiceTblSubject()
                                    );

                                if ($tblDivisionSubjectWhereGroup) {
                                    foreach ($tblDivisionSubjectWhereGroup as $tblDivisionSubjectGroup) {

                                        if (Division::useService()->getSubjectStudentByDivisionSubjectAndPerson($tblDivisionSubjectGroup,
                                            $tblPerson)
                                        ) {
                                            $hasStudentSubject = true;
                                        }
                                    }
                                } else {
                                    $hasStudentSubject = true;
                                }

                                if ($hasStudentSubject) {
                                    if ($tblPeriodList
                                        && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
                                    ) {
                                        $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                                            $tblDivisionSubject->getTblDivision(),
                                            $tblDivisionSubject->getServiceTblSubject(),
                                            $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null
                                        );

                                        $hasGrades = false;
                                        $yearGradeList = array();
                                        foreach ($tblPeriodList as $tblPeriod) {
                                            $maxCount = 0;
                                            $tblGradeList = Gradebook::useService()->getGradesAllByStudentAndYearAndSubject(
                                                $tblPerson,
                                                $tblYear,
                                                $tblDivisionSubject->getServiceTblSubject(),
                                                $tblTestType,
                                                $tblPeriod
                                            );

                                            if ($tblGradeList) {
                                                $hasGrades = true;
                                                // Sortieren der Zensuren
                                                $gradeListSorted = $this->getSorter($tblGradeList)->sortObjectBy('DateForSorter', new Sorter\DateTimeSorter());

                                                $yearGradeList = array_merge($yearGradeList, $gradeListSorted);

                                                /**@var TblGrade $tblGrade * */
                                                foreach ($gradeListSorted as $tblGrade) {
                                                    $tblTest = $tblGrade->getServiceTblTest();
                                                    if ($tblTest && ($tblGrade->getGrade() !== null && $tblGrade->getGrade() !== '')) {
                                                        $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                                        [$tblPeriod->getId()][$tblTest->getId()] = $tblGrade;
                                                        $maxCount++;
                                                    }
                                                }

                                                // period Average
                                                $average = Gradebook::useService()->calcStudentGrade(
                                                    $tblPerson, $tblDivisionSubject->getTblDivision(),
                                                    $tblDivisionSubject->getServiceTblSubject(),
                                                    Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                    $tblScoreRule ? $tblScoreRule : null, $tblPeriod,
                                                    $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                    false,
                                                    false,
                                                    $gradeListSorted
                                                );
                                                if (is_array($average)) {
                                                    $average = 'Fehler';
                                                } elseif (is_string($average) && strpos($average,
                                                        '(')
                                                ) {
                                                    $average = substr($average, 0,
                                                        strpos($average, '('));
                                                }
                                                $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                                [$tblPeriod->getId()]['Average'] = $average;
                                                $maxCount++;

                                                if (isset($maxGradesPerPeriodCount[$tblPeriod->getId()])) {
                                                    if ($maxGradesPerPeriodCount[$tblPeriod->getId()] < $maxCount) {
                                                        $maxGradesPerPeriodCount[$tblPeriod->getId()] = $maxCount;
                                                    }
                                                } else {
                                                    $maxGradesPerPeriodCount[$tblPeriod->getId()] = $maxCount;
                                                }
                                            } else {
                                                $maxGradesPerPeriodCount[$tblPeriod->getId()] = 0;
                                                // Fächer ohne Zensuren auch mit anzeigen
                                                $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()][$tblPeriod->getId()] = array(
                                                    'Average' => ''
                                                );
                                            }
                                        }

                                        if ($hasGrades) {
                                            // Total average
                                            $average = Gradebook::useService()->calcStudentGrade(
                                                $tblPerson, $tblDivisionSubject->getTblDivision(),
                                                $tblDivisionSubject->getServiceTblSubject(),
                                                Evaluation::useService()->getTestTypeByIdentifier('TEST'),
                                                $tblScoreRule ? $tblScoreRule : null, null,
                                                $tblDivisionSubject->getTblSubjectGroup() ? $tblDivisionSubject->getTblSubjectGroup() : null,
                                                false,
                                                false,
                                                $yearGradeList
                                            );
                                            if (is_array($average)) {
                                                $average = 'Fehler';
                                            } elseif (is_string($average) && strpos($average,
                                                    '(')
                                            ) {
                                                $average = substr($average, 0,
                                                    strpos($average, '('));
                                            }
                                            $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]
                                            ['Total']['Average'] = $average;
                                        } else {
                                            // Fächer ohne Zensuren auch mit anzeigen
                                            $data[$tblDivisionSubject->getServiceTblSubject()->getAcronym()]['Total']['Average'] = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $widthSubject = 5;
            $widthSubjectString = $widthSubject . '%';

            // grade width
            $totalGradeCount = 0;
            foreach ($maxGradesPerPeriodCount as &$value) {
                if ($value < self::MINIMUM_GRADE_COUNT) {
                    $value = self::MINIMUM_GRADE_COUNT;
                }
                $totalGradeCount += $value;
            }
            // +1 für durchschnitt am Ende
            $totalGradeCount++;
            $widthGrade = (100 - $widthSubject) / $totalGradeCount;
            $widthGradeString = $widthGrade . '%';

            // header
            $count = 0;
            $slice = new Slice();
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleTextBold()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBackgroundColor('lightgrey')
                    , $widthSubjectString);
            if ($tblPeriodList) {
                foreach ($tblPeriodList as $tblPeriod) {
                    if (isset($maxGradesPerPeriodCount[$tblPeriod->getId()])) {
                        $width = ($widthGrade * $maxGradesPerPeriodCount[$tblPeriod->getId()]) . '%';
                    } else {
                        $width = '10%';
                    }
                    $section
                        ->addElementColumn((new Element())
                            ->setContent($tblPeriod->getDisplayName())
                            ->styleBorderRight()
                            ->styleBorderLeft($count++ < 1 ? '1px' : '0px')
                            ->styleTextBold()
                            ->stylePaddingTop('5px')
                            ->stylePaddingBottom('5px')
                            ->styleBackgroundColor('lightgrey')
                            , $width);
                }
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('&#216;')
                    ->styleBorderRight()
                    ->styleTextBold()
                    ->stylePaddingTop('5px')
                    ->stylePaddingBottom('5px')
                    ->styleBackgroundColor('lightgrey')
                    , $widthGradeString);
            $slice
                ->addSection($section)
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleAlignCenter();

            ksort($data);
            foreach ($data as $acronym => $periodArray) {
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent($acronym)
                        ->styleBorderTop()
                        ->stylePaddingTop('10px')
                        ->stylePaddingBottom('9.9px')
                        ->styleTextBold()
                        ->styleBackgroundColor('lightgrey')
                        , $widthSubjectString);
                if (is_array($periodArray)) {
                    $count = 0;
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (isset($periodArray[$tblPeriod->getId()])) {
                            foreach ($periodArray[$tblPeriod->getId()] as $key => $tblGrade) {
                                if ($key != 'Average') {
                                    if (($tblTest = $tblGrade->getServiceTblTest())) {
                                        if ($tblTest->isContinues()) {
                                            if ($tblGrade->getDate()) {
                                                $date = $tblGrade->getDate();
                                            } else {
                                                $date = $tblTest->getFinishDate();
                                            }
                                        } else {
                                            $date = $tblTest->getDate();
                                        }
                                        if (strlen($date) > 6) {
                                            $date = substr($date, 0, 6);
                                        }
                                        $text = $date . '<br>'
                                            . $tblTest->getServiceTblGradeType()->getCode() . '<br>'
                                            . ($tblGrade->getDisplayGrade() !== null
                                            && $tblGrade->getDisplayGrade() !== '' ? $tblGrade->getDisplayGrade() : '&nbsp;');
                                        $section
                                            ->addElementColumn((new Element())
                                                ->setContent($text)
                                                ->styleTextSize('10px')
                                                ->styleBorderTop()
                                                ->styleBorderRight()
                                                ->styleBorderLeft($count++ < 1 ? '1px' : '0px')
                                                ->styleTextBold($tblTest->getServiceTblGradeType()->isHighlighted() ? 'bold' : 'normal')
                                                , $widthGradeString);
                                    }
                                }
                            }

                            // leer auffüllen
                            if (count($periodArray[$tblPeriod->getId()]) < $maxGradesPerPeriodCount[$tblPeriod->getId()]) {
                                for ($i = 0; $i < $maxGradesPerPeriodCount[$tblPeriod->getId()] - count($periodArray[$tblPeriod->getId()]); $i++) {
                                    $section
                                        ->addElementColumn((new Element())
                                            ->setContent(
                                                '&nbsp;<br>&nbsp;<br>&nbsp;'
                                            )
                                            ->styleTextSize('10px')
                                            ->styleBorderTop()
                                            ->styleBorderRight()
                                            ->styleBorderLeft($count == 0 ? '1px' : '0px')
                                            , $widthGradeString);
                                    $count++;
                                }
                            }

                            if (isset($periodArray[$tblPeriod->getId()]['Average'])) {
                                $section
                                    ->addElementColumn((new Element())
                                        ->setContent(
                                            '&#216;<br>' . $periodArray[$tblPeriod->getId()]['Average'] . '<br> &nbsp;'
                                        )
                                        ->styleTextSize('10px')
                                        ->styleBorderTop()
                                        ->styleBorderRight()
                                        ->styleTextBold()
                                        ->styleBackgroundColor('lightgrey')
                                        , $widthGradeString);
                            }
                        } else {
                            for ($i = 0; $i < $maxGradesPerPeriodCount[$tblPeriod->getId()]; $i++) {
                                $section
                                    ->addElementColumn((new Element())
                                        ->setContent(
                                            '&nbsp;<br>&nbsp;<br>&nbsp;'
                                        )
                                        ->styleTextSize('10px')
                                        ->styleBorderTop()
                                        ->styleBorderRight()
                                        ->styleBorderLeft($i < 1 ? '1px' : '0px')
                                        , $widthGradeString);
                            }
                            $count++;
                        }
                    }

                    if (isset($periodArray['Total'])) {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent(
                                    '&nbsp;<br>' . $periodArray['Total']['Average'] . '<br> &nbsp;'
                                )
                                ->styleTextSize('10px')
                                ->styleBorderTop()
                                ->styleBorderRight()
                                ->styleTextBold()
                                ->styleBackgroundColor('lightgrey')
                                , $widthGradeString);
                    }
                } else {
                    for ($i = 0; $i < $totalGradeCount; $i++) {
                        $section
                            ->addElementColumn((new Element())
                                ->setContent(
                                   '&nbsp;<br>&nbsp;<br>&nbsp;'
                                )
                                ->styleTextSize('10px')
                                ->styleBorderTop()
                                ->styleBorderRight()
                                ->styleBorderLeft($i < 1 ? '1px' : '0px')
                                , $widthGradeString);
                    }
                }
                $slice
                    ->addSection($section);
            }

            return $slice
                    ->styleBorderBottom();
        }

        return new Slice();
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        return (new Page())
            ->addSlice($this->getPageHeaderSlice())
            ->addSlice($this->getGradebookOverviewSlice());
    }
}