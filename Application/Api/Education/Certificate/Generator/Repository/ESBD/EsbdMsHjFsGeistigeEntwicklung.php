<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdMsHjFsGeistigeEntwicklung
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD
 */
class EsbdMsHjFsGeistigeEntwicklung extends EsbdStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $certificate = (new EsbdMsHjInfoFsGeistigeEntwicklung(
            $this->getTblDivision() ? $this->getTblDivision() : null,
            $this->getTblPrepareCertificate() ? $this->getTblPrepareCertificate() : null,
            $this->isSample()
        ));

        $pageList[] = $certificate->getPageOne($personId, 'Halbjahreszeugnis der Oberschule', true);
        $pageList[] = $certificate->getPageTwo($personId);

        return $pageList;
    }
}