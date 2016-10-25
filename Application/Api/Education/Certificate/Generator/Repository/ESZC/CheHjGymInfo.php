<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class CheHjGymInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CheHjGymInfo extends Certificate
{

    private function getLanguagesWithStartLevel()
    {

        $tblSubject = Subject::useService()->getSubjectByAcronym('FRZ');
        $result = array();
        if ($tblSubject) {
            $result[$tblSubject->getAcronym()] = array(
                'SubjectAcronym' => $tblSubject->getAcronym(),
                'Ranking' => 2,               // 2. Fremdsprache
                'Lane' => 1
            );
        }

        return $result;
    }

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        if ($IsSample) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Chemnitz Halbjahresinformation Gymnasium.pdf')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        , '25%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '25%')
                    ->addElementColumn((new Element()))
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        , '25%')
                );
        }

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Company.Data.Name is not empty) %}
                                {{ Content.Company.Data.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        ->styleTextSize('16px')
                        ->styleTextBold()
                    )->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Name der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Halbjahresinformation des Gymnasiums')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('15px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse:')
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schuljahr:')
                            ->styleAlignRight()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Year }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '13%'
                        )
                    )->styleMarginTop('20px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname und Name:')
                            , '21%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            , '79%')
                    )->styleMarginTop('5px')
                )
                ->addSlice($this->getGradeLanes('14px', false))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanes(true, $this->getLanguagesWithStartLevel(), '14px', false))
                ->addSlice($this->getObligationToVotePart('14px', false))
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '14%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '4%')
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('110px')
                        )
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#000')
                            ->styleAlignCenter()
                            , '23%')
                        ->addElementColumn((new Element())
                            , '70%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#000')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '70%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer(in)')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '70%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            ->styleAlignCenter()
                            , '30%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
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
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            , '70%')
                    )->styleMarginTop('51px')
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Notenerläuterung:'
                                . new Container('1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                                6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)')
                            )
                            ->styleTextSize('9.5px')
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
    private function getObligationToVotePart($TextSize = '14px', $IsGradeUnderlined = true)
    {

        $slice = new Slice();
        $sectionList = array();

        $tblSubject = false;

        // Profil
        if ($this->getTblPerson()
            && ($tblStudent = Student::useService()->getStudentByPerson($this->getTblPerson()))
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);
            if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                $tblSubject = $tblSubjectProfile;
            }
        }

        if ($tblSubject){
            $elementName = (new Element())
                ->setContent('
                   {% if(Content.Student.Profile.' . $tblSubject->getAcronym() . ' is not empty) %}
                       {{ Content.Student.Profile.' . $tblSubject->getAcronym() . '.Name' . ' }}
                   {% else %}
                        &nbsp;
                   {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('
                    {% if(Content.Grade.Data.' . $tblSubject->getAcronym() . ' is not empty) %}
                        {{ Content.Grade.Data.' . $tblSubject->getAcronym() . ' }}
                    {% else %}
                        ---
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        } else {
            $elementName = (new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);

            $elementGrade = (new Element())
                ->setContent('---')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                ->stylePaddingTop('0px')
                ->stylePaddingBottom('0px')
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize);
        }

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Wahlpflichtbereich:')
                ->styleTextBold()
                ->styleMarginTop('10px')
                ->styleTextSize($TextSize)
                , '20%')
            ->addElementColumn($elementName
                , '50%')
            ->addElementColumn((new Element()), '30%');
        $sectionList[] = $section;
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('besuchtes Profil')
                ->styleAlignCenter()
                ->styleTextSize('11px')
                , '50%')
            ->addElementColumn((new Element()), '30%');
        $sectionList[] = $section;

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Profil')
                ->styleTextSize($TextSize)
                ->styleMarginTop('10px')
                , '39%')
            ->addElementColumn($elementGrade
                , '9%')
            ->addElementColumn((new Element()), '52%');
        $sectionList[] = $section;

        return $slice->addSectionList($sectionList);
    }
}
