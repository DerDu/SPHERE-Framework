<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 05.12.2016
 * Time: 15:07
 */

namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateField")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateField extends Element
{

    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const ATTR_FIELD_NAME = 'FieldName';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificate;

    /**
     * @Column(type="string")
     */
    protected $FieldName;

    /**
     * @Column(type="integer")
     */
    protected $CharCount;

    /**
     * @return bool|TblCertificate
     */
    public function getTblCertificate()
    {

        if (null === $this->tblCertificate) {
            return false;
        } else {
            return Generator::useService()->getCertificateById($this->tblCertificate);
        }
    }

    /**
     * @param TblCertificate|null $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = (null === $tblCertificate ? null : $tblCertificate->getId());
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->FieldName;
    }

    /**
     * @param string $FieldName
     */
    public function setFieldName($FieldName)
    {
        $this->FieldName = $FieldName;
    }

    /**
     * @return integer
     */
    public function getCharCount()
    {
        return $this->CharCount;
    }

    /**
     * @param integer $CharCount
     */
    public function setCharCount($CharCount)
    {
        $this->CharCount = $CharCount;
    }
}