<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class MsJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsJHs extends Certificate
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
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        if ($IsSample) {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() )
                        ->setContent('&nbsp;')
                        ->styleTextSize('12px')
                        ->styleTextColor('#CCC')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn(( new Element\Sample() )
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        } else {
            $Header = ( new Slice() )
                ->addSection(( new Section() )
                    ->addElementColumn(( new Element() ), '75%')
                    ->addElementColumn(( new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px') )
                        , '25%')
                );
        }

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice($this->getSchoolName())
                ->addSlice($this->getCertificateHead('Jahreszeugnis'))
                ->addSlice($this->getDivisionAndYear('20px'))
                ->addSlice($this->getStudentName())
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('nahm am Unterricht der Schulart Mittelschule mit dem Ziel des Hauptschulabschlusses teil.')
                        ->styleTextSize('12px')
                        ->styleMarginTop('8px')
                    )
                )
                ->addSlice($this->getGradeLanes())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Einschätzung:')
                        )
                    )
                    ->addSection(( new Section() )
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Rating is not empty) %}
                                    {{ Content.Input.Rating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('40px')
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
                ->addSlice($this->getSubjectLanes()
                    ->styleHeight('270px'))
                ->addSlice($this->getOrientationStandard())
                ->addSlice($this->getDescriptionHead(true))
                ->addSlice($this->getDescriptionContent('45px', '15px'))
                ->addSlice($this->getTransfer())
                ->addSlice($this->getDateLine('10px'))
                ->addSlice($this->getSignPart(true, '15px'))
                ->addSlice($this->getParentSign('15px'))
                ->addSlice($this->getInfo('5px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            )
        );
    }
}
