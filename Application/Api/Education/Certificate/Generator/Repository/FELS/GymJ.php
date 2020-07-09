<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GymJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS
 */
class GymJ extends Certificate
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
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice(FelsStyle::getHeader($this->isSample(), 'Gymnasium'))
//            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Leistungsübersicht des Gymnasiums'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getGradeLanesSmall($personId))
            ->addSlice($this->getRatingContent($personId, '35px', '5px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('5px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall($personId, true, array('Lane' => 1, 'Rank' => 3))
                ->styleHeight('220px')
            )
            ->addSlice($this->getProfileStandardNew($personId, '14px', false, true, false))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Teilnahme an zusätzlichen schulischen Veranstaltungen:
                            {% if(Content.P' . $personId . '.Input.TeamExtra is not empty) %}
                                {{ Content.P' . $personId . '.Input.TeamExtra|nl2br }}
                            {% else %}
                                ---
                            {% endif %}')
                        ->styleHeight('25px')
                    )
                )
                ->styleMarginTop('5px')
            )
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '100px', '5px'))
            ->addSlice($this->getTransfer($personId, '2px'))
            ->addSlice($this->getDateLine($personId, '10px'))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice($this->getParentSign('33px'))
            ->addSlice($this->getInfo('20px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)'
            ));
    }
}