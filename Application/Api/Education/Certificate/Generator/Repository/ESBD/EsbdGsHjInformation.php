<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdGsHjInformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
class EsbdGsHjInformation extends EsbdStyle
{

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
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Grundschule', '(staatlich anerkannte Ersatzschule)'))
            ->addSlice($this->getCertificateHeadConsumer('Halbjahresinformation der Grundschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId, true))
            ->addSlice($this->getGradeLanes($personId, '14px', false, '5px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)
                ->styleHeight('60px'))
            ->addSlice($this->getDescriptionHeadConsumer($personId, false))
            ->addSlice($this->getDescriptionContentConsumer($personId, '410px', '17px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId, false))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('20px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
                (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            ->addSlice($this->getBottomLineConsumer());
    }
}
