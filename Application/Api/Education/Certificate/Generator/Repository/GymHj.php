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
 * Class GymHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GymHj extends Certificate
{

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
                ->addSlice($this->getCertificateHead('Halbjahreszeugnis des Gymnasiums'))
                ->addSlice($this->getDivisionAndYear('20px', '1. Schulhalbjahr'))
                ->addSlice($this->getStudentName())
                ->addSlice( $this->getGradeLanes() )
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanes(true, array('Lane' => 1, 'Rank' => 3)))
                ->addSlice($this->getProfileStandard())
                ->addSlice($this->getDescriptionHead(true))
                ->addSlice($this->getDescriptionContent('80px', '15px'))
                ->addSlice($this->getDateLine())
                ->addSlice($this->getSignPart(true))
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('10px',
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
