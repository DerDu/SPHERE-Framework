<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository\ESBD
 */
class EsbdGsJOne extends EsbdStyle
{

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
            ->addSlice($this->getDescriptionHeadConsumer($personId, false, '20px', '&nbsp;'))
            ->addSlice($this->getDescriptionContentConsumer($personId, '540px', '17px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getBottomLineConsumer());
    }
}
