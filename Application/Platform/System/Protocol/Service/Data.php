<?php
namespace SPHERE\Application\Platform\System\Protocol\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Manager;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\System\Protocol\Service
 */
class Data extends AbstractData
{
    /** @var null|Manager $BulkManager */
    private static $BulkManager = null;

    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     *
     * @since  1.1.0
     *
     * @param  object $object __PHP_Incomplete_Class
     *
     * @return object
     */
    private static function fixObject($object)
    {

        if (!is_object($object) && gettype($object) == 'object') {
            // preg_replace_callback handler. Needed to calculate new key-length.
            $fix_key = create_function(
                '$matches',
                'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
            );
            // 1. Serialize the object to a string.
            $dump = serialize($object);
            // 2. Change class-type to 'stdClass'.
            preg_match('/^O:\d+:"[^"]++"/', $dump, $match);
            $dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $dump);
            // 3. Make private and protected properties public.
            $dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $fix_key, $dump);
            // 4. Unserialize the modified object again.
            $dump = unserialize($dump);
            $dump->ERROR = new Danger("Structure mismatch!<br/>".$match[0]."<br/>Please delete this Item");
            return $dump;
        } else {
            return $object;
        }
    }

    /**
     * Get available Database-Name-List
     *
     * (Distinct)
     *
     * @return array
     */
    public function getProtocolDatabaseNameList()
    {
        $Builder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $Query = $Builder
            ->select('P.ProtocolDatabase')
            ->from(__NAMESPACE__ . '\Entity\TblProtocol', 'P')
            ->distinct()
            ->getQuery();

        return $Query->getResult("COLUMN_HYDRATOR");
    }

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

    }

    /**
     * @return TblProtocol[]|bool
     */
    public function getProtocolAll()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Query = $Manager->getQueryBuilder()
            ->select('P')
            ->from(__NAMESPACE__ . '\Entity\TblProtocol', 'P')
            ->orderBy('P.Id', 'DESC')
            ->setMaxResults(10000)
            ->getQuery();

        $EntityList = $Query->getResult();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     *
     */
    public function flushBulkSave()
    {
        if (self::$BulkManager) {
            self::$BulkManager->flushCache();
        }
    }

    /**
     * @param string $DatabaseName
     * @param null|TblAccount $tblAccount
     * @param null|TblConsumer $tblConsumer
     * @param null|Element $FromEntity
     * @param null|Element $ToEntity
     * @param bool $useBulkSave
     *
     * @return false|TblProtocol
     */
    public function createProtocolEntry(
        $DatabaseName,
        TblAccount $tblAccount = null,
        TblConsumer $tblConsumer = null,
        Element $FromEntity = null,
        Element $ToEntity = null,
        $useBulkSave = false
    ) {

        // Skip if nothing changed
        if (null !== $FromEntity && null !== $ToEntity) {
            $From = $FromEntity->__toArray();
            sort($From);
            $To = $ToEntity->__toArray();
            sort($To);
            if ($From === $To) {
                return false;
            }
        }

        if ($useBulkSave) {
            if (self::$BulkManager) {
                $Manager = self::$BulkManager;
            } else {
                $Manager = self::$BulkManager = $this->getConnection()->getEntityManager();
            }
        } else {
            $Manager = $this->getConnection()->getEntityManager();
        }

        $Entity = new TblProtocol();
        $Entity->setProtocolDatabase($DatabaseName);
        $Entity->setProtocolTimestamp(time());
        if ($tblAccount) {
            $Entity->setServiceTblAccount($tblAccount);
            $Entity->setAccountUsername($tblAccount->getUsername());
        }
        if ($tblConsumer) {
            $Entity->setServiceTblConsumer($tblConsumer);
            $Entity->setConsumerName($tblConsumer->getName());
            $Entity->setConsumerAcronym($tblConsumer->getAcronym());
        }
        $Entity->setEntityFrom(( $FromEntity ? serialize($FromEntity) : null ));
        $Entity->setEntityTo(( $ToEntity ? serialize($ToEntity) : null ));

        if( $useBulkSave ) {
            $Manager->bulkSaveEntity($Entity);
        } else {
            $Manager->saveEntity($Entity);
        }

        return $Entity;
    }

    /**
     * @return TblProtocol[]|bool
     */
    public function getProtocolAllCreateSession()
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();

        $OneMonthAgo = new \DateTime(date("Ymd"));
        $OneMonthAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 31 )).'D'));
        $FourWeeksAgo = new \DateTime(date("Ymd"));
        $FourWeeksAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 28 )).'D'));
        $TwoWeeksAgo = new \DateTime(date("Ymd"));
        $TwoWeeksAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 14 )).'D'));
        $LastWeek = new \DateTime(date("Ymd"));
        $LastWeek->sub(new \DateInterval('P'.abs(( 7 - date("N") - 7 )).'D'));
        $ThisWeek = new \DateTime(date("Ymd"));
        $ThisWeek->add(new \DateInterval('P'.abs(( 7 - date("N") )).'D'));

        $Query = $Builder
            ->select('P')
            ->from(__NAMESPACE__.'\Entity\TblProtocol', 'P')
            ->where(
                $Builder->expr()->eq('P.ProtocolDatabase', '?1')
            )->andWhere(
                $Builder->expr()->isNull('P.EntityFrom')
            )->andWhere(
                $Builder->expr()->like('P.EntityTo', '?2')
            )->andWhere(
                $Builder->expr()->gte('P.EntityCreate', '?3')
            )
            ->setParameter(1, 'PlatformGatekeeperAuthorizationAccount')
            ->setParameter(2, '%TblSession%')
            ->setParameter(3, $this->getProtocolTimestamp())
            ->orderBy('P.EntityCreate', 'DESC')
            ->setMaxResults(10000)
            ->getQuery();

        $Query->useQueryCache(false);
        $Query->useResultCache(false);
        $EntityList = $Query->getResult();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblProtocol[]
     */
    public function getProtocolLastActivity(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();

        $Query = $Builder
            ->select('P')
            ->from(__NAMESPACE__.'\Entity\TblProtocol', 'P')
            ->andWhere(
                $Builder->expr()->like('P.serviceTblAccount', '?1')
            )->andWhere(
                $Builder->expr()->gte('P.EntityCreate', '?2')
            )
            ->setParameter(1, $tblAccount->getId())
            ->setParameter(2, $this->getProtocolTimestamp())
            ->orderBy('P.EntityCreate', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        $EntityList = $Query->getResult();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return \DateTime
     */
    private function getProtocolTimestamp()
    {

        $OneMonthAgo = new \DateTime(date("Ymd"));
        $OneMonthAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 31 )).'D'));
        $FourWeeksAgo = new \DateTime(date("Ymd"));
        $FourWeeksAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 28 )).'D'));
        $TwoWeeksAgo = new \DateTime(date("Ymd"));
        $TwoWeeksAgo->sub(new \DateInterval('P'.abs(( 7 - date("N") - 14 )).'D'));
        $LastWeek = new \DateTime(date("Ymd"));
        $LastWeek->sub(new \DateInterval('P'.abs(( 7 - date("N") - 7 )).'D'));
        $ThisWeek = new \DateTime(date("Ymd"));
        $ThisWeek->add(new \DateInterval('P'.abs(( 7 - date("N") )).'D'));

        return $OneMonthAgo;
    } 
}
