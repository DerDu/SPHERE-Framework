<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class View
 *
 * @package SPHERE\System\Database\Fitting
 */
class View
{

    /** @var string $Pattern */
    private $Pattern = '|^[a-z\söäüß\-&\(\)]+$|is';
    /** @var string $Name */
    private $Name;
    /** @var Structure $Structure */
    private $Structure;

    /** @var array $LinkList */
    private $LinkList = array();

    /**
     * View constructor.
     *
     * @param string    $Name DB-UNIQUE!
     * @param Structure $Structure
     *
     * @throws \Exception
     */
    public function __construct(Structure $Structure, $Name)
    {

        if (!preg_match($this->Pattern, $Name)) {
            throw new \Exception(__CLASS__.' > Pattern mismatch: ('.$Name.') ['.$this->Pattern.']');
        }
        $this->Name = $Name;
        $this->Structure = $Structure;
    }

    /**
     * Add ORM-Node-Link
     *
     * @param Element $From
     * @param string  $FromKey
     * @param Element $To
     * @param string  $ToKey Default: "Id"
     *
     * @return $this
     */
    public function addLink(Element $From, $FromKey, Element $To = null, $ToKey = 'Id')
    {

        array_push($this->LinkList, array('From' => $From, 'FromKey' => $FromKey, 'To' => $To, 'ToKey' => $ToKey));
        return $this;
    }

    /**
     * Get Doctrine-View Object
     *
     * @return \Doctrine\DBAL\Schema\View
     */
    public function getView()
    {

        return new \Doctrine\DBAL\Schema\View($this->getName(), $this->buildView()->getSQL());
    }

    /**
     * Get View-Name
     *
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @return QueryBuilder
     */
    private function buildView()
    {

        $TableList = $this->LinkList;
        $QueryBuilder = $this->Structure->getQueryBuilder();

        // SELECT
        $Select = array();
        /**
         * @var int              $Index
         * @var Element[]|string $Link
         */
        foreach ($TableList as $Index => $Link) {

            if ($Index === 0) {
                $Select[] = $this->convertPropertyList($Link['From'], false);
                $Select[] = $this->convertPropertyList($Link['From']);
                if( $Link['To'] ) {
                    $Select[] = $this->convertPropertyList($Link['To']);
                }
            } else {
                if( $Link['To'] ) {
                    $Select[] = $this->convertPropertyList($Link['To']);
                }
            }
        }
        $Select = implode(', ', $Select);
        $QueryBuilder->select($Select);
        // FROM
        /** @var Element $FromSource */
        $FromSource = $TableList[0]['From'];
        $QueryBuilder->from(
            $this->convertWordCase($FromSource->getEntityShortName()),
            $FromSource->getEntityShortName()
        );
        // JOIN
        foreach ($TableList as $Link) {
            /** @var Element $From */
            $From = $Link['From'];
            /** @var Element $To */
            $To = $Link['To'];
            if( $Link['To'] ) {
                $QueryBuilder->leftJoin(
                    $From->getEntityShortName(),
                    $this->convertWordCase($To->getEntityShortName()),
                    $To->getEntityShortName(),
                    $QueryBuilder->expr()->andX(
                        $QueryBuilder->expr()->eq($From->getEntityShortName() . '.' . $Link['FromKey'],
                            $To->getEntityShortName() . '.' . $Link['ToKey']),
                        $QueryBuilder->expr()->isNull($To->getEntityShortName() . '.EntityRemove')
                    )
                );
            }
        }
        $QueryBuilder->where(
            $QueryBuilder->expr()->isNull($FromSource->getEntityShortName().'.EntityRemove')
        );

        return $QueryBuilder;
    }

    /**
     * Prepare Property-Selector
     *
     * @param Element $Entity
     * @param bool    $Prefix
     *
     * @return string
     */
    private function convertPropertyList(Element $Entity, $Prefix = true)
    {

        if ($Prefix) {
            $PropertyList = (new \ReflectionClass($Entity))->getProperties(\ReflectionProperty::IS_PROTECTED);
        } else {
            $PropertyList = (new \ReflectionClass('SPHERE\System\Database\Fitting\Element'))->getProperties(\ReflectionProperty::IS_PROTECTED);
        }

        array_walk($PropertyList, function (\ReflectionProperty &$Property) use ($Entity, $Prefix) {

            $Property = $this->convertSelectAlias($Property->getName(), $Entity->getEntityShortName(), $Prefix);
        });
        return implode(', ', $PropertyList);
    }

    /**
     * Prepend Table-Alias
     *
     * @param      $Field
     * @param      $Table
     * @param bool $Prefix
     *
     * @return string
     */
    private function convertSelectAlias($Field, $Table, $Prefix = true)
    {

        if (false === $Prefix) {
            return $Table.'.'.$Field.' '.$Field;
        } else {
            return $Table.'.'.$Field.' '.$Table.'_'.$Field;
        }
    }

    /**
     * TODO: Replace with Entity-Annotation-Tag-Reader (ORM:Table)
     *
     * @param $TableName
     *
     * @return string
     */
    private function convertWordCase($TableName)
    {

        return preg_replace('!^Tbl!', 'tbl', $TableName);
    }

    /**
     * Get VIEW SELECT
     *
     * @internal Usage: Debug
     * @return string
     */
    public function getSQL()
    {

        return $this->buildView()->getSQL();
    }
}
