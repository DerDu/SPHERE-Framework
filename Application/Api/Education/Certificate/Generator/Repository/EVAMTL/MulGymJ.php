<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVAMTL;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class MulGymJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MulGymJ extends Certificate
{

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
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($this->isSample()) {
            $Header =
                (new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%'
                        )
                        ->addElementColumn((new Element\Sample())
                            ->styleTextSize('30px')
                        )
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVAMTL.jpg',
                            'auto', '50px'))
                            , '35%')
                    );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '65%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVAMTL.jpg',
                        'auto', '50px'))
                        , '35%')
                );
        }

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Information über den zum Ende des Schuljahres erreichten Leistungsstand'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getGradeLanes($personId, '14px', false, '5px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung:')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                    {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('30px')
                    )
                )
                ->styleMarginTop('10px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('10px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))
                ->styleHeight('270px')
            )
            ->addSlice($this->getProfileStandard($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Arbeitsgemeinschaften:')
                        , '23%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.TeamExtra is not empty) %}
                                    {{ Content.P'.$personId.'.Input.TeamExtra }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('25px')
                        , '77%')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '30px', '5px'))
            ->addSlice($this->getTransfer($personId))
            ->addSlice($this->getDateLine($personId, '15px'))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice($this->getParentSign('15px'))
            ->addSlice($this->getInfo('5px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)'
//                    ,
//                    '¹ Zutreffendes ist zu unterstreichen.',
//                    '² In Klassenstufe 8 ist der Zusatz „mit informatischer Bildung“ zu streichen. Beim sprachlichen
//                    Profil ist der Zusatz „mit informatischer Bildung“ zu streichen und die Fremdsprache anzugeben.'
            )
            );
    }
}
