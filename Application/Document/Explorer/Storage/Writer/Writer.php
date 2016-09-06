<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer;

use SPHERE\Application\Document\Explorer\Storage\Writer\Type\Database;
use SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary;

/**
 * @deprecated
 * Class Writer
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer
 */
class Writer
{

    /**
     * @deprecated
     *
*@param string $Extension
     * @param string $Prefix
     * @param bool   $Destruct
     *
     * @return Temporary
     */
    public function getTemporary($Extension = 'storage', $Prefix = 'SPHERE-Temporary', $Destruct = true)
    {

        return new Temporary($Prefix, $Extension, $Destruct);
    }

    /**
     * @deprecated
     *
*@param null|int $Id
     *
     * @return Database
     */
    public function getDatabase($Id = null)
    {

        return new Database($Id);
    }
}
