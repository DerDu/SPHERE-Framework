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
 * Class GymJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
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
                        ->setContent('&nbsp;')
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
                    ->addElementColumn((new Element()), '75%')
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
                ->addSlice($this->getSchoolName())
                ->addSlice($this->getCertificateHead('Jahreszeugnis des Gymnasiums'))
                ->addSlice($this->getDivisionAndYear())
                ->addSlice($this->getStudentName())
                ->addSlice($this->getGradeLanes('14px', false, '5px'))
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
                ->addSlice($this->getSubjectLanes(true, array('Lane' => 1, 'Rank' => 3))
                    ->styleHeight('270px')
                )
                ->addSlice($this->getProfileStandard())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Arbeitsgemeinschaften:')
                            , '23%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.TeamExtra is not empty) %}
                                    {{ Content.Input.TeamExtra }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleHeight('25px')
                            , '77%')
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice($this->getDescriptionHead(true))
                ->addSlice($this->getDescriptionContent('30px', '5px'))
                ->addSlice($this->getTransfer())
                ->addSlice($this->getDateLine('15px'))
                ->addSlice($this->getSignPart(true))
                ->addSlice($this->getParentSign('15px'))
                ->addSlice($this->getInfo('5px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)'
//                    ,
//                    '¹ Zutreffendes ist zu unterstreichen.',
//                    '² In Klassenstufe 8 ist der Zusatz „mit informatischer Bildung“ zu streichen. Beim sprachlichen
//                    Profil ist der Zusatz „mit informatischer Bildung“ zu streichen und die Fremdsprache anzugeben.'
                ))
            )
        );
    }
}
