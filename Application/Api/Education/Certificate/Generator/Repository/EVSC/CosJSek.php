<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;

/**
 * Class CosJSek
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosJSek extends Certificate
{

    const TEXT_SIZE = '13px';

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn(
                            $IsSample
                                ? (new Element\Sample())
                                ->setContent('MUSTER')
                                ->styleTextSize('30px')
                                : (new Element())
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('FREISTAAT SACHSEN')
                            ->styleTextSize('20px')
                            ->styleAlignCenter()
                            , '50%')
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '165px', '50px'))
                            ->styleAlignCenter()
                            , '25%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('Evangelische Schule Coswig')
                                ->styleTextSize('20px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('15px')
                            )
                            ->addElement((new Element())
                                ->setContent('staatlich anerkannte Ersatzschule')
                                ->styleTextSize('16px')
                                ->styleAlignCenter()
                            )
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Jahreszeugnis der Schule (Sekundarstufe)')
                        ->styleTextSize('20px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '38%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '32%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '64%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '18%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('nahm am Unterricht der Schulart Mittelschule mit dem Ziel des {{ Content.Student.Course.Degree }} teil.')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '100%')
                    )->styleMarginTop('12px')
                )
                ->addSlice($this->getGradeLanes(self::TEXT_SIZE, false))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistung in den einzelnen Fächern')
                        ->styleTextItalic()
                        ->styleTextBold()
                        ->styleMarginTop('20px')
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
                ->addSlice($this->getSubjectLanes(true, array(), self::TEXT_SIZE, false))
                ->addSlice($this->getObligationToVotePart())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                            ->styleTextItalic()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '33%')
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            , '5%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            , '11%')
                    )
                    ->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '85%')
                    )
                    ->styleHeight('105px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            ->styleTextSize(self::TEXT_SIZE)
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '20%')
                        ->addElementColumn((new Element())
                            , '56%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
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
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('11px')
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Klassenleiter/in')
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            , '35%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zur Kenntnis genommen:')
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
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '100%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenstufen 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                            ->styleTextSize('9px')
                            ->styleMarginTop('17px')
                        )
                    )
                )
            )
        );
    }

    /**
     * @param string $TextSize
     * @param bool $IsGradeUnderlined
     *
     * @return Slice
     */
    private function getObligationToVotePart($TextSize = self::TEXT_SIZE, $IsGradeUnderlined = false)
    {

        $marginTop = '5px';

        $slice = new Slice();
        $sectionList = array();

        $elementOrientationName = false;
        $elementOrientationGrade = false;
        $elementForeignLanguageName = false;
        $elementForeignLanguageGrade = false;
        if ($this->getTblPerson()
            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
        ) {

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblSubjectList);
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $elementOrientationName = new Element();
                    $elementOrientationName
                        ->setContent('
                            {% if(Content.Student.Orientation.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.Student.Orientation.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
                        ->styleTextSize($TextSize);

                    $elementOrientationGrade = new Element();
                    $elementOrientationGrade
                        ->setContent('
                            {% if(Content.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                ---
                            {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop('0px')
                        ->stylePaddingBottom('0px')
                        ->styleMarginTop($marginTop)
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
                            {% if(Content.Student.ForeignLanguage.' . $tblSubject->getAcronym() . ' is not empty) %}
                                 {{ Content.Student.ForeignLanguage.' . $tblSubject->getAcronym() . '.Name' . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);

                        $elementForeignLanguageGrade = new Element();
                        $elementForeignLanguageGrade
                            ->setContent('
                            {% if(Content.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                ---
                            {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#BBB')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop('0px')
                            ->stylePaddingBottom('0px')
                            ->styleMarginTop($marginTop)
                            ->styleTextSize($TextSize);
                    }
                }
            }

            if ($elementOrientationName || $elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Wahlpflichtbereich:')
                        ->styleTextItalic()
                        ->styleTextBold()
                        ->styleMarginTop('20px')
                        ->styleTextSize($TextSize)
                    );
                $sectionList[] = $section;
            }

            if ($elementOrientationName && $elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '39%')
                    ->addElementColumn($elementOrientationGrade, '9%')
                    ->addElementColumn((new Element()), '4%')
                    ->addElementColumn($elementForeignLanguageName, '39%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich)')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                        , '48%')
                    ->addElementColumn((new Element()), '4%')
                    ->addElementColumn((new Element())
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                        , '48%'
                    );
                $sectionList[] = $section;
            } elseif ($elementOrientationName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementOrientationName, '39%')
                    ->addElementColumn($elementOrientationGrade, '9%')
                    ->addElementColumn((new Element()), '52%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('Neigungskurs (Neigungskursbereich)')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                    );
                $sectionList[] = $section;
            } elseif ($elementForeignLanguageName) {
                $section = new Section();
                $section
                    ->addElementColumn($elementForeignLanguageName, '39%')
                    ->addElementColumn($elementForeignLanguageGrade, '9%')
                    ->addElementColumn((new Element()), '52%');
                $sectionList[] = $section;

                $section = new Section();
                $section
                    ->addElementColumn((new Element())
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                    );
                $sectionList[] = $section;
            }
        }

        return empty($sectionList) ? (new Slice())->styleHeight('60px') : $slice->addSectionList($sectionList);
    }
}