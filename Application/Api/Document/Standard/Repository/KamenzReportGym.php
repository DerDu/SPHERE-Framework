<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 08:27
 */

namespace SPHERE\Application\Api\Document\Standard\Repository;


use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\B02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\C01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E02;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E02_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E03;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E04;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E04_1;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E05;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E07;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E08;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E12;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E15;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E16;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E17;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\E18;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\F01;
use SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym\G01;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;

class KamenzReportGym extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Kamenz-Statistik Gymnasium';
    }

    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSliceArray(B02::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(C01::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E01::getContent())
                ->addSliceArray(E02::getContent())
                ->addSliceArray(E02_1::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E03::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E04::getContent())
                ->addSliceArray(E04_1::getContent())
                ->addSliceArray(E05::getContent())
                ->addSliceArray(E07::getContent())
                ->addSliceArray(E08::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E12::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E15::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E16::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E17::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(E18::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(F01::getContent())
            )
            ->addPage((new Page())
                ->addSliceArray(G01::getContent())
            )
        );
    }
}