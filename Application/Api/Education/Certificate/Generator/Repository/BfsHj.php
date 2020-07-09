<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsHj extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice($this->getSchoolHead($personId))
            ->addSlice($this->getStudentHead($personId))
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity()))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Berufsbezogener Bereich'))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 10, true, '220px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('100px')
            ))
            ->addSlice($this->getBottomInformation($personId))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }

    /**
     * @param        $personId
     * @param string $CertificateName
     *
     * @return Slice
     */
    public function getSchoolHead($personId, $CertificateName = 'Halbjahresinformation')
    {

        $name = '';
        $secondLine = '';
        // get company name
        if (($tblPerson = Person::useService()->getPersonById($personId))
            && ($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson, $this->getTblDivision() ? $this->getTblDivision() : null))
        ) {
            $name = $tblCompany->getName();
            $secondLine = $tblCompany->getExtendedName();
        }

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent($name ? $name : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('22px')
            ->styleHeight('28px')
            ->stylePaddingTop('25px')
        );
        $Slice->addElement((new Element())
            ->setContent($secondLine ? $secondLine : '&nbsp;')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleHeight('42px')
//            ->stylePaddingTop('20px')
        );
        $Slice->addSection($this->getIndividuallyLogo($this->isSample()));
        $Slice->addElement((new Element())
            ->setContent($CertificateName)
            ->styleAlignCenter()
            ->styleTextSize('30px')
        );
        $Slice->addElement((new Element())
            ->setContent('der Berufsfachschule für {% if(Content.P' . $personId . '.Input.BsDestination is not empty) %}
                        {{ Content.P' . $personId . '.Input.BsDestination }}
                    {% endif %}')
            ->stylePaddingTop('4px')
            ->styleAlignCenter()
            ->styleTextSize('22px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param string $period
     *
     * @return Slice
     */
    public function getStudentHead($personId, $period = 'Schulhalbjahr')
    {

        $Slice = new Slice();

        $Slice->stylePaddingTop('20px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Klassenstufe {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px', '#000', 'dotted')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '40%'
            )
            ->addElementColumn((new Element())
                ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px', '#000', 'dotted')
                , '30%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
//            ->setContent('
//            {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 2 %}
//                Frau
//            {% else %}
//                {% if Content.P' . $personId . '.Person.Common.BirthDates.Gender == 1 %}
//                    Herr
//                {% else %}
//                    Frau/Herr
//                {% endif %}
//            {% endif %}
//            {{ Content.P' . $personId . '.Person.Data.Name.First }}
//            {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
            ->styleBorderBottom('0.5px', '#000', 'dotted')
            ->styleAlignCenter()
            ->styleTextSize('26px')
            ->stylePaddingTop('20px')
            ->styleMarginBottom('20px')
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am  {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px', '#BBB')
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp')
                , '40%'
            )
            ->addElementColumn((new Element())
                ->setContent('in {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthplace }}')
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px', '#BBB')
                , '30%'
            )
        );

        $Slice->addElement((new Element())
            ->setContent('hat im zurückliegenden ' . $period . ' folgende Leistungen erreicht:')
            ->styleAlignCenter()
            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
            ->styleBorderBottom('0.5px', '#BBB')
        );

        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     *
     * @return Slice
     */
    public function getSubjectLineAcross($personId, TblCertificate $tblCertificate)
    {

        $Slice = (new Slice());
        $Slice->addElement((new Element())
            ->setContent('Pflichtbereich')
            ->styleAlignCenter()
            ->styleTextSize('18px')
            ->styleTextBold()
            ->stylePaddingTop('40px')
        );
        $Slice->addElement((new Element())
            ->setContent('Berufsübergreifender Bereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);
        $tblGradeList = $this->getGrade();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);
                    if($tblCertificateSubject->getRanking() <= 3){
                        // Grade Exists? => Add Subject to Certificate
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString][$LaneString]['SubjectAcronym'] = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString][$LaneString]['SubjectName'] = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            // Berufsübergreifender Bereich
            $SubjectList1 = array();
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $Ranking => $SubjectList) {
                foreach ($SubjectList as $Lane => $Subject) {
                    $SubjectList1[$Ranking][$Lane] = $Subject;
//                    $LaneCounter[$Lane]++;
                }
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList1 as $SubjectList) {
                // Sort Lane-Ranking (1,2...)
                ksort($SubjectList);
                $SubjectSection = (new Section());
                if (count($SubjectList) == 1 && isset($SubjectList["02"])) {
                    $SubjectSection->addElementColumn((new Element()), 'auto');
                }

                foreach ($SubjectList as $Lane => $Subject) {
                    if ($Lane > 1){
                        $SubjectSection->addElementColumn((new Element())
                            , '4%');
                    }

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent($Subject['SubjectName'])
                        ->stylePaddingTop()
                        ->styleMarginTop('15px')
                        ->stylePaddingBottom('1px')
                        ->styleTextSize($TextSize)
                        ->styleBorderBottom('0.5px', '#BBB')
                        , '39%');

                    $SubjectSection->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                                 {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                             {% else %}
                                 &ndash;
                             {% endif %}')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('#BBB')
                        ->styleMarginTop('15px')
                        ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                        ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                        ->styleTextSize(
                            '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                        )
                        , '9%');
                }
                if (count($SubjectList) == 1 && isset($SubjectList["01"])) {
                    $SubjectSection->addElementColumn((new Element()), '52%');
                }

                $Slice->addSection($SubjectSection);
            }
        }

        return $Slice;
    }

    /**
     * @param        $personId
     * @param TblCertificate $tblCertificate
     * @param string $Title
     * @param int $Length
     * @param bool $isPageTwo
     * @param string $Height
     *
     * @return Slice
     */
    public function getSubjectLineBase($personId, TblCertificate $tblCertificate, $Title = '&nbsp;', $Length = 10, $isPageTwo = false, $Height = 'auto')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent($Title)
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);
        $tblGradeList = $this->getGrade();

        $CountSubjectMissing = 0;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 5 && $tblCertificateSubject->getRanking() < 13){
                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])){
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            if(!$isPageTwo){
                $CountSubjectMissing = 10;
            } else {
                $CountSubjectMissing = 4;
            }

            $SubjectList = array();
            ksort($SubjectStructure);
            $SubjectCount = 1;
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                if($SubjectCount <= $Length && !$isPageTwo){
                    $SubjectList[$RankingLane] = $Subject;
                    $CountSubjectMissing--;
                } elseif($SubjectCount > $Length && $isPageTwo){
                    $SubjectList[$RankingLane] = $Subject;
                }
                $SubjectCount++;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';

            $countLF = 1;
            if($isPageTwo){
                $countLF = $Length + 1;
            }
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent('LF'.$countLF++.' '.$Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px', '#BBB')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    /**
     * @param $personId
     * @param string $title
     *
     * @return Slice
     */
    public function getSecondPageHead($personId, $title = 'Halbjahresinformation')
    {

        $Slice = new Slice();

        $Slice->addElement((new Element())
            ->setContent($title . ' für
            {% if(Content.P'.$personId.'.Person.Data.Name.Salutation is not empty) %}
                {{ Content.P'.$personId.'.Person.Data.Name.Salutation }}
            {% else %}
                Frau/Herr
            {% endif %}
            {{ Content.P' . $personId . '.Person.Data.Name.First }}
            {{ Content.P' . $personId . '.Person.Data.Name.Last }},
            geboren am {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday }} - 2. Seite')
            ->styleAlignCenter()
//            ->styleTextSize('16px')
            ->stylePaddingTop('20px')
            ->styleBorderBottom()
        );

        return $Slice;
    }

    /**
     * @param        $personId
     * @param TblCertificate $tblCertificate
     * @param string $Height
     *
     * @return Slice
     */
    public function getSubjectLineChosen($personId, TblCertificate $tblCertificate, $Height = '130px')
    {
        $Slice = (new Slice());

        $Slice->addElement((new Element())
            ->setContent('Wahlpflichtbereich')
            ->styleAlignCenter()
            ->stylePaddingTop('20px')
            ->stylePaddingBottom('10px')
        );

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);
        $tblGradeList = $this->getGrade();
        $CountSubjectMissing = 0;
        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() >= 13
                        && $tblCertificateSubject->getRanking() < 15) {

                        if (isset($tblGradeList['Data'][$tblSubject->getAcronym()])) {
                            $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                = $tblSubject->getAcronym();
                            $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                = $tblSubject->getName();
                        } else {
                            // Grade Missing, But Subject Essential => Add Subject to Certificate
                            if ($tblCertificateSubject->isEssential()){
                                $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                                    = $tblSubject->getAcronym();
                                $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                                    = $tblSubject->getName();
                            }
                        }
                    }
                }
            }

            $SubjectList = array();

            // Anzahl der Abzubildenden Einträge (auch ohne Fach)
            $CountSubjectMissing = 2;
            ksort($SubjectStructure);
            foreach ($SubjectStructure as $RankingLane => $Subject) {
                $SubjectList[] = $Subject;
                $CountSubjectMissing--;
            }

            $TextSize = '14px';
            $TextSizeSmall = '8px';
            foreach ($SubjectList as $Subject) {
                // Jedes Fach auf separate Zeile
                $SubjectSection = (new Section());

                $SubjectSection->addElementColumn((new Element())
                    ->setContent($Subject['SubjectName'])
                    ->stylePaddingTop()
                    ->styleMarginTop('10px')
                    ->stylePaddingBottom('1px')
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom('0.5px', '#BBB')
                    , '91%');


                $SubjectSection->addElementColumn((new Element())
                    ->setContent('{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('#BBB')
                    ->styleMarginTop('10px')
                    ->stylePaddingTop('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                    ->stylePaddingBottom('{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.5px
                             {% else %}
                                 1.5px
                             {% endif %}')
                    ->styleTextSize(
                        '{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                    )
                    , '9%');
                $Slice->addSection($SubjectSection);
            }
        }

        if($CountSubjectMissing > 0){
            $Slice = $this->getEmptySubjectField($Slice, $CountSubjectMissing);
        }

        $Slice->styleHeight($Height);

        return $Slice;
    }

    private function getEmptySubjectField(Slice $Slice, $count = 0)
    {

        $TextSize = '14px';
        for($i = 0; $i < $count; $i++){
            $Section = new Section();
            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop()
                ->styleMarginTop('10px')
                ->stylePaddingBottom('1px')
                ->styleTextSize($TextSize)
                ->styleBorderBottom('0.5px', '#BBB')
                , '91%');


            $Section->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor('#BBB')
                ->styleMarginTop('10px')
                ->stylePaddingTop('2px')
                ->stylePaddingBottom('1.5px')
                ->styleTextSize($TextSize)
                , '9%');
            $Slice->addSection($Section);
        }
        return $Slice;
    }

    /**
     * @param $personId
     * @param TblCertificate $tblCertificate
     *
     * @return Slice
     */
    public function getPraktika($personId, TblCertificate $tblCertificate)
    {

        $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);

        $Subject = array();

        if (!empty($tblCertificateSubjectAll)) {
            $SubjectStructure = array();
            foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                $tblSubject = $tblCertificateSubject->getServiceTblSubject();
                if ($tblSubject) {
                    $RankingString = str_pad($tblCertificateSubject->getRanking(), 2 ,'0', STR_PAD_LEFT);
                    $LaneString = str_pad($tblCertificateSubject->getLane(), 2 ,'0', STR_PAD_LEFT);

                    if($tblCertificateSubject->getRanking() == 15) {

                        // Wird immer ausgewiesen (Fach wird nicht abgebildet)
                        $SubjectStructure[$RankingString.$LaneString]['SubjectAcronym']
                            = $tblSubject->getAcronym();
                        $SubjectStructure[$RankingString.$LaneString]['SubjectName']
                            = $tblSubject->getName();
                    }
                }
            }
            $Subject = current($SubjectStructure);
        }

        $TextSize = '14px';
        $TextSizeSmall = '8px';

        $Slice = new Slice();
        $Slice->styleBorderAll('0.5px', '#000', 'dotted');
        $Slice->styleMarginTop('30px');
        $Slice->stylePaddingTop('10px');
        $Slice->stylePaddingBottom('10px');
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>Berufspraktische Ausbildung</b> (Dauer: 20 Wochen)')
                ->stylePaddingLeft('5px')
                , '91%'
            )
            ->addElementColumn((new Element())      //ToDO richtiges Acronym auswählen
                ->setContent(empty($Subject) ? '&ndash;'
                         :'{% if(Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] is not empty) %}
                             {{ Content.P'.$personId.'.Grade.Data["'.$Subject['SubjectAcronym'].'"] }}
                         {% else %}
                             &ndash;
                         {% endif %}')
                ->styleAlignCenter()
                ->styleBackgroundColor('#BBB')
                ->stylePaddingTop(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 5.3px
                             {% else %}
                                 2px
                             {% endif %}')
                ->stylePaddingBottom(empty($Subject) ? '2px'
                    :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 6px
                             {% else %}
                                 2px
                             {% endif %}')
                ->styleTextSize(empty($Subject) ? '2px'
                            :'{% if((Content.P' . $personId . '.Grade.Data.IsShrinkSize["' . $Subject['SubjectAcronym'] . '"] is not empty)
                                and (Content.P' . $personId . '.Grade.Data["' . $Subject['SubjectAcronym'] . '"] is not empty)
                            ) %}
                                 ' . $TextSizeSmall . '
                             {% else %}
                                 ' . $TextSize . '
                             {% endif %}'
                )
                , '9%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation1 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime1 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime1 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation2 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime2 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime2 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('Dauer gesamt: {{ Content.P' . $personId . '.Input.OperationTime1 + Content.P' . $personId . '.Input.OperationTime2 + Content.P' . $personId . '.Input.OperationTime3 }} Wochen')
                ->stylePaddingTop('10px')
                ->styleAlignRight()
                ->stylePaddingRight('15px')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>{% if(Content.P' . $personId . '.Input.Operation3 is not empty) %}
                        {{ Content.P' . $personId . '.Input.Operation3 }}
                    {% else %}
                        < EINSATZGEBIETE >
                    {% endif %}</b> (Dauer 
                     {% if(Content.P' . $personId . '.Input.OperationTime3 is not empty) %}
                        {{ Content.P' . $personId . '.Input.OperationTime3 }}
                    {% else %}
                        X
                    {% endif %}
                     Wochen)')
                ->stylePaddingTop('10px')
                ->stylePaddingLeft('5px')
            )
        );

        return $Slice;
    }

    /**
     * @param        $personId
     * @param string $Height
     *
     * @return Slice
     */
    public function getDescriptionBsContent($personId, $Height = '195px')
    {

        $Slice = new Slice();

        $Slice->styleMarginTop('20px');
        $Slice->stylePaddingTop('5px');
        $Slice->styleHeight($Height);
        $Slice->styleBorderAll('0.5px', '#000', 'dotted');

        $Slice->addElement((new Element())
            ->setContent('Bemerkungen:')
            ->styleTextUnderline()
            ->stylePaddingLeft('5px')
        );
        $Slice->addElement((new Element())
            ->setContent('{% if(Content.P' . $personId . '.Input.RemarkWithoutTeam is not empty) %}
                        {{ Content.P' . $personId . '.Input.RemarkWithoutTeam|nl2br }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
            ->styleAlignJustify()
            ->stylePaddingLeft('5px')
            ->stylePaddingRight('5px')
        );

        return $Slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    private function getBottomInformation($personId)
    {

        $Slice = new Slice();

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {{ Content.P' . $personId . '.Company.Address.City.Name }}, {{ Content.P' . $personId . '.Input.Date }}'
                )
                ->styleAlignCenter()
                ->styleBorderBottom('0.5px', '#BBB')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom('0.5px', '#888')
                , '40%'
            )
        );

        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Ort, Datum')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                    {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                        {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                    {% else %}
                        Klassenlehrer/in
                    {% endif %}
                ')
                ->styleAlignCenter()
                ->styleTextSize('10px')
                , '40%'
            )
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
                ->styleBorderBottom('0.5px', '#BBB')
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

    /**
     * @param string $PaddingTop
     * @param string $Content
     *
     * @return Slice
     */
    public function getBsInfo($PaddingTop = '20px', $Content = '')
    {
        $Slice = new Slice();
        $Slice->stylePaddingTop($PaddingTop);
        $Slice->addElement((new Element())
                ->setContent($Content)
                ->styleTextSize('9.5px')
        );
        return $Slice;
    }
}
