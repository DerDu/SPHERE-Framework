<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.09.2016
 * Time: 10:14
 */

namespace SPHERE\Application\Education\Graduation\Evaluation\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTestLink")
 * @Cache(usage="READ_ONLY")
 */
class TblTestLink extends Element
{

    const ATTR_TBL_TEST = 'tblTest';
    const ATTR_TBL_LINK_ID = 'LinkId';

    /**
     * @Column(type="bigint")
     */
    protected $LinkId;

    /**
     * @Column(type="bigint")
     */
    protected $tblTest;

    /**
     * @return bool|TblTest
     */
    public function getTblTest()
    {

        if (null === $this->tblTest) {
            return false;
        } else {
            return Evaluation::useService()->getTestById($this->tblTest);
        }
    }

    /**
     * @param TblTest|null $tblTest
     */
    public function setTblTest($tblTest)
    {

        $this->tblTest = ( null === $tblTest ? null : $tblTest->getId() );
    }

    /**
     * @return int
     */
    public function getLinkId()
    {
        return $this->LinkId;
    }

    /**
     * @param int $LinkId
     */
    public function setLinkId($LinkId)
    {
        $this->LinkId = $LinkId;
    }
}