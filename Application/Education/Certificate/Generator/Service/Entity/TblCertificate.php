<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificate extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_CERTIFICATE = 'Certificate';
    const SERVICE_TBL_CONSUMER = 'serviceTblConsumer';
    const ATTR_IS_GRADE_INFORMATION = 'IsGradeInformation';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblConsumer;

    /**
     * @Column(type="string")
     */
    protected $Certificate;

    /**
     * @Column(type="boolean")
     */
    protected $IsGradeInformation;

    /**
     * @return bool|TblConsumer
     */
    public function getServiceTblConsumer()
    {

        if (null === $this->serviceTblConsumer) {
            return false;
        } else {
            return Consumer::useService()->getConsumerById($this->serviceTblConsumer);
        }
    }

    /**
     * @param TblConsumer|null $serviceTblConsumer
     */
    public function setServiceTblConsumer($serviceTblConsumer)
    {

        $this->serviceTblConsumer = ( null === $serviceTblConsumer ? null : $serviceTblConsumer->getId() );
    }

    /**
     * @param TblPerson   $tblPerson
     * @param TblDivision $tblDivision
     * @param bool        $IsSample
     *
     * @return bool|Certificate
     */
    public function getDocument(TblPerson $tblPerson, TblDivision $tblDivision, $IsSample = true)
    {

        $Class = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\'.$this->getCertificate();
        if (class_exists($Class)) {

            return new $Class($tblPerson, $tblDivision, $IsSample);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {

        return $this->Certificate;
    }

    /**
     * @param string $Certificate
     */
    public function setCertificate($Certificate)
    {

        $this->Certificate = $Certificate;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return boolean
     */
    public function isGradeInformation()
    {
        return $this->IsGradeInformation;
    }

    /**
     * @param boolean $IsGradeInformation
     */
    public function setIsGradeInformation($IsGradeInformation)
    {
        $this->IsGradeInformation = $IsGradeInformation;
    }

    /**
     * @return string
     */
    public function getDisplayCategory()
    {

        return $this->isGradeInformation() ? 'Noteninformation' : 'Zeugnis';
    }
}
