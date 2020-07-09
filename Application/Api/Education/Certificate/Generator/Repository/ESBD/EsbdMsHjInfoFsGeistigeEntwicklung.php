<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdMsHjInfoFsGeistigeEntwicklung
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
class EsbdMsHjInfoFsGeistigeEntwicklung extends EsbdStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = $this->getPageOne($personId);
        $pageList[] = $this->getPageTwo($personId);

        return $pageList;
    }

    /**
     * @param $personId
     *
     * @param string $title
     * @param bool $isSignPartExtended
     * @param string $term
     * @return Page
     */
    public function getPageOne(
        $personId,
        $title = 'Halbjahresinformation der Oberschule',
        $isSignPartExtended = false,
        $term = '1. Schulhalbjahr'
    ) {
        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule'))
            ->addSlice($this->getCertificateHeadConsumer($title, '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '20px', $term))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSlice($this->getSupportContent($personId, '530px', '20px', 'Inklusive Unterrichtung¹: '))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId, $isSignPartExtended))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('30px',
                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'
            ))
            ->addSlice($this->getBottomLineConsumer());
    }

    /**
     * @param $personId
     * @param string $term
     *
     * @return Page
     */
    public function getPageTwo($personId, $term = '1. Schulhalbjahr')
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('DIALOGUS')
                    ->styleTextSize('28pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice($this->getDivisionAndYearConsumer($personId, '10px', $term))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSliceArray($this->getSecondPageDescription($personId))
            ->addSlice($this->getBottomLineConsumer('42px'));
    }
}