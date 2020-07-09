<?php

namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateInformation extends Element
{
    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const ATTR_FIELD_NAME = 'FieldName';
    const ATTR_PAGE = 'Page';

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
    protected $Page;

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
     * @param null|TblCertificate $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = ( null === $tblCertificate ? null : $tblCertificate->getId() );
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
    public function getPage()
    {
        return $this->Page;
    }

    /**
     * @param integer $Page
     */
    public function setPage($Page)
    {
        $this->Page = $Page;
    }
}