<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\System\Extension\Extension;

/**
 * Class Table
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class Table extends Extension implements ITemplateInterface
{

    /** @var TableHead[] $TableHead */
    protected $TableHead = array();
    /** @var TableBody[] $TableBody */
    protected $TableBody = array();
    /** @var TableFoot[] $TableFoot */
    protected $TableFoot = array();
    /** @var bool|string|array|null $Interactive */
    protected $Interactive = false;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Hash */
    protected $Hash = '';

    /**
     * @param TableHead  $TableHead
     * @param TableBody  $TableBody
     * @param Title      $TableTitle
     * @param bool|array $Interactive
     * @param TableFoot  $TableFoot
     */
    public function __construct(
        TableHead $TableHead,
        TableBody $TableBody,
        Title $TableTitle = null,
        $Interactive = false,
        TableFoot $TableFoot = null
    ) {

        $this->Interactive = $Interactive;

        if (!is_array($TableHead)) {
            $TableHead = array($TableHead);
        }
        $this->TableHead = $TableHead;
        if (!is_array($TableBody)) {
            $TableBody = array($TableBody);
        }
        $this->TableBody = $TableBody;
        if (!is_array($TableFoot)) {
            if($TableFoot === null) {
                $TableFoot = array();
            } else {
                $TableFoot = array($TableFoot);
            }
        }
        $this->TableFoot = $TableFoot;
        if ($Interactive) {
            $this->Template = $this->getTemplate(__DIR__.'/TableData.twig');
            if (is_array($Interactive)) {
                $Options = json_encode($Interactive);
                $Options = preg_replace( '!"(function\s*\(.*?\)\s*\{.*?\})"!is', '${1}', $Options );
                $this->Template->setVariable('InteractiveOption', $Options);
            }
        } elseif ($Interactive === null) {
            $this->Template = $this->getTemplate(__DIR__.'/TableData.twig');
            $Interactive = array(
                "paging"         => false,
                "searching"      => false,
                "iDisplayLength" => -1,
                "info"           => false
            );
            $Options = json_encode($Interactive);
            $Options = preg_replace( '!"(function\s*\(.*?\)\s*\{.*?\})"!is', '${1}', $Options );
            $this->Template->setVariable('InteractiveOption', $Options);
        } else {
            $this->Template = $this->getTemplate(__DIR__.'/Table.twig');
        }
        $this->Template->setVariable('TableTitle', $TableTitle);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('HeadList', $this->TableHead);
        $this->Template->setVariable('BodyList', $this->TableBody);
        $this->Template->setVariable('FootList', $this->TableFoot);
        $this->Template->setVariable('Hash', $this->getHash());

        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if (empty( $this->Hash )) {
            $HeadList = $this->TableHead;
            array_walk($HeadList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $BodyList = $this->TableBody;
            array_walk($BodyList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $FootList = $this->TableFoot;
            array_walk($FootList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $this->Hash = md5(json_encode($HeadList) . json_encode($BodyList) . json_encode($FootList));
        }
        return $this->Hash;
    }

    /**
     * @param TableHead $TableHead
     */
    public function appendHead(TableHead $TableHead)
    {

        array_push($this->TableHead, $TableHead);
    }

    /**
     * @param TableHead $TableHead
     */
    public function prependHead(TableHead $TableHead)
    {

        array_unshift($this->TableHead, $TableHead);
    }

    /**
     * @param TableBody $TableBody
     */
    public function appendBody(TableBody $TableBody)
    {

        array_push($this->TableBody, $TableBody);
    }

    /**
     * @param TableBody $TableBody
     */
    public function prependBody(TableBody $TableBody)
    {

        array_unshift($this->TableBody, $TableBody);
    }

    /**
     * @param TableFoot $TableFoot
     */
    public function appendFoot(TableFoot $TableFoot)
    {

        array_push($this->TableFoot, $TableFoot);
    }

    /**
     * @param TableFoot $TableFoot
     */
    public function prependFoot(TableFoot $TableFoot)
    {

        array_unshift($this->TableFoot, $TableFoot);
    }

    /**
     * @return string
     */
    public function getName()
    {

        return '';
    }
}
