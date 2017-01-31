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
 * Class CosHjSek
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosHjSek extends Certificate
{

    const TEXT_SIZE = '13px';

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {
        if ($IsSample) {
            $Header = array(( new Section() )
                ->addSliceColumn(( new Slice() )
                    ->addSection(( new Section() )
                        ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/Coswig_logo_300dpi.jpg',
                            '100px', '100px') )
                            ->stylePaddingTop('12px')
                            ->styleHeight('0px')
                            ->styleAlignCenter()
                            , '25%')
                        ->addElementColumn(( new Element() )
                            ->setContent('FREISTAAT SACHSEN')
                            ->styleFontFamily('Trebuchet MS')
                            ->styleTextSize('21px')
                            ->styleAlignCenter()
                            ->stylePaddingTop('22px')
                            , '50%')
                        ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                            '165px', '50px') )
                            ->stylePaddingTop('12px')
                            , '25%')
                    )
                ),
                ( new Section() )
                    ->addSliceColumn(( new Slice() )
                        ->addSection(( new Section() )
                            ->addElementColumn(( new Element\Sample() )
                                ->styleTextSize('30px')
                                ->styleMarginTop('55px')
                                ->styleHeight('0px')
                            )
                            ->addElementColumn(( new Element() )
                                ->setContent('Evangelische Schule Coswig')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize('21px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('40px')
                                ->styleLineHeight('85%')
                                , '50%')
                            ->addElementColumn(( new Element() )
                                , '25%')
                        )
                    )
            );
        } else {
            $Header = array(( new Section() )
                ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/Coswig_logo_300dpi.jpg',
                    '100px', '100px') )
                    ->stylePaddingTop('12px')
                    ->styleHeight('20px')
                    ->styleAlignCenter()
                    , '25%')
                ->addElementColumn(( new Element() )
                    ->setContent('FREISTAAT SACHSEN')
                    ->styleFontFamily('Trebuchet MS')
                    ->styleTextSize('21px')
                    ->styleAlignCenter()
                    ->stylePaddingTop('22px')
                    , '50%')
                ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                    '165px', '50px') )
                    ->stylePaddingTop('12px')
                    , '25%'),
                ( new Section() )
                    ->addElementColumn(( new Element() )
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

        return ( new Frame() )->addDocument(( new Document() )
            ->addPage(( new Page() )
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
                            ->addElement(( new Element() )
                                ->setContent('Halbjahresinformation der Schule (Sekundarstufe)')
                                ->styleFontFamily('Trebuchet MS')
                                ->styleTextSize('20px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('25px')
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Klasse')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '7%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleAlignCenter()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '10%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('&nbsp;')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '57%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('1. Schulhalbjahr')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{{ Content.Division.Data.Year }}')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleAlignCenter()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '10%')
                                    )->styleMarginTop('15px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Vor- und Zuname:')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '18%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '64%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('&nbsp;')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '18%')
                                    )->styleMarginTop('10px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('
                                {% if(Content.Student.Course.Degree is not empty) %}
                                        nahm am Unterricht der Schulart Mittelschule mit dem Ziel des
                                        {{ Content.Student.Course.Degree }} teil.
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '100%')
                                    )->styleMarginTop('0px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn($this->getGradeLanesCoswig(self::TEXT_SIZE, false, '10px'))
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addElement(( new Element() )
                                        ->setContent('Leistung in den einzelnen Fächern')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextItalic()
                                        ->styleTextBold()
                                        ->styleMarginTop('15px')
                                        ->styleTextSize(self::TEXT_SIZE)
                                    )
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn($this->getSubjectLanesCoswig(true, array(), self::TEXT_SIZE, false)
                                    ->styleHeight('248px'))
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn($this->getObligationToVotePart())
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addElement(( new Element() )
                                        ->setContent('Bemerkungen:')
                                        ->styleFontFamily('Trebuchet MS')
                                        ->styleTextItalic()
                                        ->styleTextSize(self::TEXT_SIZE)
                                    )->stylePaddingTop('5px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                                    {{ Content.Input.Remark|nl2br }}
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
                            ->addSection(( new Section() )
                                ->addElementColumn(( new Element() )
                                    ->setContent('Fehltage entschuldigt:')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    , '22%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    , '7%')
                                ->addElementColumn(( new Element() )
                                    , '5%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('unentschuldigt:')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    , '15%')
                                ->addElementColumn(( new Element() )
                                    ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                                    ->styleFontFamily('Trebuchet MS')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    , '7%')
                                ->addElementColumn(( new Element() )
                                    , '44%')
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Datum:')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '7%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                                    {{ Content.Input.Date }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleBorderBottom()
                                            ->styleAlignCenter()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '20%')
                                        ->addElementColumn(( new Element() )
                                            , '56%')
                                    )->styleMarginTop('15px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('&nbsp;')
                                            ->styleBorderBottom()
                                            ->styleAlignCenter()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '35%')
                                        ->addElementColumn(( new Element() )
                                            , '30%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('&nbsp;')
                                            ->styleBorderBottom()
                                            ->styleAlignCenter()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '35%')
                                    )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Schulleiter/in')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize('11px')
                                            , '35%'
                                        )
                                        ->addElementColumn(( new Element() )
                                            , '30%'
                                        )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Klassenleiter/in')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize('11px')
                                            , '35%')
                                    )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{% if(Content.Headmaster.Name is not empty) %}
                                                    {{ Content.Headmaster.Name }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}'
                                            )
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleLineHeight('85%')
                                            ->styleTextSize('11px')
                                            ->stylePaddingBottom('3px')
                                            , '35%')
                                        ->addElementColumn(( new Element() )
                                            , '30%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('{% if(Content.DivisionTeacher.Name is not empty) %}
                                                    {{ Content.DivisionTeacher.Name }}
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
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Zur Kenntnis genommen:')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '25%')
                                        ->addElementColumn(( new Element() )
                                            ->setContent('&nbsp;')
                                            ->styleBorderBottom()
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '75%')
                                    )
                                    ->addSection(( new Section() )
                                        ->addElementColumn(( new Element() )
                                            ->setContent('Personensorgeberechtigte/r')
                                            ->styleFontFamily('Trebuchet MS')
                                            ->styleAlignCenter()
                                            ->styleTextSize('11px')
                                            , '100%')
                                    )
                                    ->styleMarginTop('25px')
                                )
                            )
                            ->addSection(( new Section() )
                                ->addSliceColumn(( new Slice() )
                                    ->addElement(( new Element() )
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
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
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
                                &ndash;
                            {% endif %}')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#E9E9E9')
                        ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                        ->stylePaddingTop()
                        ->stylePaddingBottom()
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
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
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
                                &ndash;
                            {% endif %}')
                            ->styleFontFamily('Trebuchet MS')
                            ->styleLineHeight('85%')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#E9E9E9')
                            ->styleBorderBottom($IsGradeUnderlined ? '1px' : '0px', '#000')
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
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
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
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
                        ->setContent('Neigungskurs')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                        , '48%')
                    ->addElementColumn((new Element()), '4%')
                    ->addElementColumn((new Element())
                        ->setContent('2. Fremdsprache (abschlussorientiert)')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
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
                        ->setContent('Neigungskurs')
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
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
                        ->styleFontFamily('Trebuchet MS')
                        ->styleLineHeight('85%')
                        ->styleBorderTop()
                        ->styleMarginTop('5px')
                        ->styleTextSize('11px')
                    );
                $sectionList[] = $section;
            }
        }

        return empty($sectionList)
            ? $slice->addElement(( new Element() )
                ->setContent('&nbsp;')
            )->styleHeight('76px')
            : $slice->addSectionList($sectionList);
    }
}
