<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:18
 */

namespace SPHERE\Application\Document\Generator\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Style\Font\Font;

/**
 * Class Frame
 *
 * @package SPHERE\Application\Document\Generator\Repository
 */
class Frame
{

    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var array $Data */
    private $Data = array();

    /** @var array $Documents */
    private $Documents = array();

    /**
     * Frame constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"><style type="text/css">'.file_get_contents(__DIR__.'/../Style.css').(new Font()).'{{ PreviewCss }}</style></head><body>{{ Documents }}</body></html>');
    }

    /**
     * @param Document $Document
     *
     * @return $this
     */
    public function addDocument(Document $Document)
    {

        $this->Documents[] = $Document;
        return $this;
    }

    /**
     * @param array $Data
     *
     * @return $this
     */
    public function setData($Data)
    {

        $this->Data = array_merge($this->Data, $Data);
        return $this;
    }

    /**
     * @return array
     */
    public function getPlaceholder()
    {

        $Prepare = clone $this->Template;
        $Prepare->setVariable('Documents', implode("\n", $this->Documents));
        $Text = $Prepare->getContent();
        preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $Text, $MatchList);
        if (isset( $MatchList[2] )) {
            $MatchList = array_values(array_filter($MatchList[2]));
            array_walk($MatchList, function (&$Placeholder) {

                $Placeholder = trim(preg_replace('!\|.*?$!', '', $Placeholder));
            });
            return array_unique($MatchList);
        }
        return array();
    }

    /**
     * @return IBridgeInterface
     */
    public function getTemplate()
    {

        $Prepare = clone $this->Template;
        $Prepare->setVariable('Documents', implode("\n", $this->Documents));
        $Payload = Template::getTwigTemplateString($Prepare->getContent());
        // TODO: Remove 'Data'
        $Payload->setVariable('Data', $this->Data);
        $Payload->setVariable('Content', $this->Data);
        return $Payload;
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

        $Prepare = clone $this->Template;
        $Prepare->setVariable('PreviewCss', file_get_contents(__DIR__.'/../Preview.css'));
        $Prepare->setVariable('Documents', implode("\n", $this->Documents));
        $Payload = Template::getTwigTemplateString($Prepare->getContent());
        // TODO: Remove 'Data'
        $Payload->setVariable('Data', $this->Data);
        $Payload->setVariable('Content', $this->Data);
        return $Payload->getContent();
    }
}
