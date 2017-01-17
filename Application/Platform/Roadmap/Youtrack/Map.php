<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Debugger\Logger\CacheLogger;
use SPHERE\System\Debugger\Logger\QueryLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Map
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Map extends Extension
{

    /** @var Sprint[] $Sprints */
    private $Sprints = array();
    /** @var Issue[] $Pool */
    private $Pool = array();
    /** @var null|string $VersionPreview */
    private $VersionPreview = null;
    /** @var null|string $VersionRelease */
    private $VersionRelease = null;

    /**
     * @param Sprint $Sprint
     */
    public function addSprint(Sprint $Sprint)
    {

        $this->Sprints[] = $Sprint;
    }

    /**
     * @param Issue $Issue
     */
    public function addIssue(Issue $Issue)
    {

        $this->Pool[] = $Issue;
    }

    /**
     * @return null|string
     */
    public function getVersionPreview()
    {

        if ($this->VersionPreview === null) {
            $Cache = $this->getCache(new MemcachedHandler());
            if (!( $Result = $Cache->getValue(__METHOD__, __CLASS__) )) {
                $Sprints = $this->getSprints();
                /** @var Sprint $Sprint */
                foreach ((array)$Sprints as $Index => $Sprint) {
                    if ($Sprint->isDone()) {
                        if (isset( $Sprints[( $Index + 1 )] ) && $Sprints[( $Index + 1 )]->isDone()) {
                            $this->VersionPreview = $Sprints[( $Index + 1 )]->getVersion();
                        } else {
                            $this->VersionPreview = $Sprint->getVersion();
                        }
                    } else {
                        break;
                    }
                }
                (new DebuggerFactory())->createLogger(new QueryLogger())->addLog( __METHOD__.' '.$this->VersionPreview);
                $Cache->setValue(__METHOD__, $this->VersionPreview, 0, __CLASS__);
            } else {
                $this->VersionPreview = $Result;
            }
        }
        return $this->VersionPreview;
    }

    /**
     * @return Sprint[]
     */
    public function getSprints()
    {

        usort( $this->Sprints, function( Sprint $A, Sprint $B ) {
            return strnatcmp( $A->getVersion(), $B->getVersion() );
        });

        if( count( $this->Sprints ) > 4 ) {
            return array_slice($this->Sprints, -5);
        }
        return $this->Sprints;
    }

    /**
     * @return Issue[]
     */
    public function getPool()
    {

        return $this->Pool;
    }

    /**
     * @return null|string
     */
    public function getVersionRelease()
    {

        if ($this->VersionRelease === null) {
            $Cache = $this->getCache(new MemcachedHandler());
            if (!( $Result = $Cache->getValue(__METHOD__, __CLASS__) )) {
                $Sprints = $this->getSprints();
                /** @var Sprint $Sprint */
                foreach ((array)$Sprints as $Index => $Sprint) {
                    if ($Sprint->isDone()) {
                        if (isset( $Sprints[( $Index - 1 )] ) && $Sprints[( $Index - 1 )]->isDone()) {
                            $this->VersionRelease = $Sprints[( $Index - 1 )]->getVersion();
                        } else {
                            $this->VersionRelease = $Sprint->getVersion();
                        }
                    } else {
                        break;
                    }
                }
                (new DebuggerFactory())->createLogger(new QueryLogger())->addLog( __METHOD__.' '.$this->VersionRelease);
                $Cache->setValue(__METHOD__, $this->VersionRelease, 0, __CLASS__);
            } else {
                $this->VersionRelease = $Result;
            }
        }
        return $this->VersionRelease;
    }
}
