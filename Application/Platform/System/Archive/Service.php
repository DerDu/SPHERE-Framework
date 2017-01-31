<?php
namespace SPHERE\Application\Platform\System\Archive;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Archive\Service\Data;
use SPHERE\Application\Platform\System\Archive\Service\Entity\TblArchive;
use SPHERE\Application\Platform\System\Archive\Service\Setup;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\Archive
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string           $DatabaseName
     * @param null|TblAccount  $tblAccount
     * @param null|TblConsumer $tblConsumer
     * @param null|Element     $Entity
     * @param int              $Type
     * @param bool $useBulkSave
     *
     * @return false|TblArchive
     */
    public function createArchiveEntry(
        $DatabaseName,
        TblAccount $tblAccount = null,
        TblConsumer $tblConsumer = null,
        Element $Entity = null,
        $Type = TblArchive::ARCHIVE_TYPE_CREATE,
        $useBulkSave = false
    ) {

        return (new Data($this->getBinding()))->createArchiveEntry(
            $DatabaseName, $tblAccount, $tblConsumer, $Entity, $Type, $useBulkSave
        );
    }

    /**
     * @return TblArchive[]|bool
     */
    public function getArchiveAll()
    {

        return (new Data($this->getBinding()))->getArchiveAll();
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblArchive[]
     */
    public function getArchiveAllByConsumer(TblConsumer $tblConsumer)
    {

        return (new Data($this->getBinding()))->getArchiveAllByConsumer($tblConsumer);
    }

    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     *
     * @param  object $Object __PHP_Incomplete_Class
     *
     * @return object
     */
    public function fixArchive($Object)
    {

        if (!is_object($Object) && gettype($Object) == 'object') {
            // preg_replace_callback handler. Needed to calculate new key-length.
            $Fix = create_function(
                '$matches',
                'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
            );
            // 1. Serialize the object to a string.
            $Dump = serialize($Object);
            // 2. Change class-type to 'stdClass'.
            preg_match('/^O:\d+:"[^"]++"/', $Dump, $match);
            $Dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $Dump);
            // 3. Make private and protected properties public.
            $Dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $Fix, $Dump);
            // 4. Unserialize the modified object again.
            $Dump = unserialize($Dump);
            $Dump->ERROR = new Danger("Structure mismatch!<br/>".$match[0]."<br/>Please delete this Item");
            return $Dump;
        } else {
            if (is_string($Object)) {
                // preg_replace_callback handler. Needed to calculate new key-length.
                $Fix = create_function(
                    '$matches',
                    'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
                );
                // 1. Serialize the object to a string.
                $Dump = $Object;
                // 2. Change class-type to 'stdClass'.
                preg_match('/^O:\d+:"[^"]++"/', $Dump, $match);
                $Dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $Dump);
                // 3. Make private and protected properties public.
                $Dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $Fix, $Dump);
                // 4. Unserialize the modified object again.
                $Dump = unserialize($Dump);
                return $Dump;
            }
            return $Object;
        }
    }

    /**
     * MUST call if "useBulkSave" parameter was used with
     * - createDeleteEntry
     * - createUpdateEntry
     * - createInsertEntry
     */
    public function flushBulkEntries()
    {
        (new Data($this->getBinding()))->flushBulkSave();
    }
}
