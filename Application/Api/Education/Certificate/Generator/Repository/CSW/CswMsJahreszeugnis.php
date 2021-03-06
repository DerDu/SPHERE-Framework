<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.06.2018
 * Time: 09:20
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswMsJahreszeugnis
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswMsJahreszeugnis extends Certificate
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
        $pictureHeight = '90px';

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%'
                    )
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW_Logo_EOK_100x100.jpg',
                        'auto', $pictureHeight))->styleAlignRight()
                        , '25%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '75%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW_Logo_EOK_100x100.jpg',
                        'auto', $pictureHeight))->styleAlignRight()
                        , '25%')
                );
        }
        $Header->styleHeight('50px');

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice(CswMsHalbjahresinformation::getIndividualSchoolLine($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis'))
            ->addSlice($this->getDivisionAndYear($personId, '20px'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('nahm am Unterricht der Schulart Mittelschule teil.')
                    ->styleTextSize('12px')
                    ->styleMarginTop('8px')
                )
            )
            ->addSlice($this->getGradeLanes($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung: {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleHeight('50px')
                    )
                )
                ->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)
                ->styleHeight('250px')
            )
            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '55px', '8px'))
            ->addSlice($this->getTransfer($personId, '13px'))
            ->addSlice($this->getDateLine($personId, '10px'))
            ->addSlice($this->getSignPart($personId, true, '25px'))
            ->addSlice($this->getParentSign('25px'))
            ->addSlice($this->getInfo('3px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                (6 = ungenügend nur bei der Bewertung der Leistungen)')
        );
    }
}
