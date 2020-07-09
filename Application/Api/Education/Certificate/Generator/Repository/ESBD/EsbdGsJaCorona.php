<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdGsJaCorona
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository\ESBD
 */
class EsbdGsJaCorona extends EsbdStyle
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
    public function buildPages(TblPerson $tblPerson = null){

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Grundschule', '(staatlich anerkannte Ersatzschule)'))
            ->addSlice($this->getCertificateHeadConsumer('Jahreszeugnis der Grundschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId))
            ->addSlice($this->getStudentNameConsumer($personId, true))
            ->addSlice((new Slice())
                ->addElement(( new Element() )
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)
                ->styleHeight('65px')
            )
//            ->addSlice($this->getDescriptionHeadConsumer($personId, false))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Einschätzung:')
                    ->styleMarginTop('10px')
                )
            )

            ->addSlice((new Slice())
                ->addSection(( new Section() )
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Rating is not empty) %}
                                {{ Content.P' . $personId . '.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleHeight('370px')
                        ->styleAlignJustify()
                    )
                )
                ->styleMarginTop('15px')
            )
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getTransferConsumer($personId, '15px'))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('20px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
            (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            ->addSlice($this->getBottomLineConsumer());
    }
}
