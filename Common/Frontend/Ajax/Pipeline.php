<?php
namespace SPHERE\Common\Frontend\Ajax;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Form\Structure\Form;

/**
 * Class Pipeline
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
class Pipeline
{

    /** @var string $SuccessTitle */
    private $SuccessTitle = '';
    /** @var string $SuccessMessage */
    private $SuccessMessage = '';
    /** @var string $LoadingTitle */
    private $LoadingTitle = '';
    /** @var string $LoadingMessage */
    private $LoadingMessage = '';
    /** @var AbstractEmitter[] $Emitter */
    private $Emitter = array();

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    public function setSuccessMessage($Title, $Message = '')
    {
        $this->SuccessTitle = $Title;
        $this->SuccessMessage = $Message;
        return $this;
    }

    /**
     * @param string $Title
     * @param string $Message
     * @return $this
     */
    public function setLoadingMessage($Title, $Message = '')
    {
        $this->LoadingTitle = $Title;
        $this->LoadingMessage = $Message;
        return $this;
    }

    /**
     * @param AbstractEmitter $AbstractEmitter
     * @return $this
     */
    public function addEmitter(AbstractEmitter $AbstractEmitter)
    {
        array_push($this->Emitter, $AbstractEmitter);
        return $this;
    }

    /**
     * @internal
     * @deprecated
     * @param Pipeline $Pipeline
     * @return $this
     */
    public function addPipeline(Pipeline $Pipeline)
    {
        $this->Emitter = array_merge($this->Emitter, $Pipeline->getEmitter());
        return $this;
    }

    /**
     * @return AbstractEmitter[]
     */
    public function getEmitter()
    {
        return $this->Emitter;
    }

    /**
     *
     */
    public function __toString()
    {
        return (string)'<script type="text/javascript">'
            . 'executeScript(function() {'
            . 'Client.Use("ModAjax", function(){'
            . $this->parseScript()
            . '});'
            . '});'
            . '</script>';
    }

    /**
     * @param Form $Form
     * @return string
     * @throws \Exception
     */
    public function parseScript(Form $Form = null)
    {
        foreach ($this->Emitter as $Index => $Emitter) {
            // ServerEmitter
            if ($Emitter instanceof ServerEmitter) {
                $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();
                if ($Form === null) {
                    if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                        $Method = 'GET';
                    } else {
                        $Method = 'POST';
                    }
                    $Data = $Emitter->getAjaxPostPayload();
                } else {
                    $Method = 'POST';
                    if (strlen($Emitter->getAjaxPostPayload()) > 2) {
                        $Data = 'var EmitterData = ' . $Emitter->getAjaxPostPayload() . ';';
                        $Data .= 'var FormData = jQuery("form#' . $Form->getHash() . '").serializeArray();';
                        $Data .= 'for( var Index in FormData ) { EmitterData[FormData[Index]["name"]] = FormData[Index]["value"]; };';
                        $Data .= 'return EmitterData;';
                    } else {
                        $Data = 'return jQuery("form#' . $Form->getHash() . '").serializeArray();';
                    }
                }

                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach ($ReceiverList as $Receiver) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }

                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} );'
                    );
                } else {
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString($Template->getContent());
                }

                $Template->setVariable('Method', json_encode($Method));
                $Template->setVariable('Url', json_encode($Url));
                $Template->setVariable('Data', json_encode($Data));
                $Template->setVariable('Receiver', json_encode($ReceiverContext));
                $Template->setVariable('Hash', json_encode(sha1(json_encode($Method) . json_encode($Url) . json_encode($Data) . json_encode($ReceiverContext))));
            }
            // ClientEmitter
            if ($Emitter instanceof ClientEmitter || $Emitter instanceof ScriptEmitter) {

                $Content = $Emitter->getContent();

                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach ($ReceiverList as $Receiver) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }

                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadContent( {{ Content }}, {{ Callback }} );'
                    );
                } else {
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, ' . $this->getNotifyMessage($Emitter) . ' } }).loadContent( {{ Content }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString($Template->getContent());
                }
                $Template->setVariable('Content', $Content);
                $Template->setVariable('Receiver', json_encode($ReceiverContext));
                $Template->setVariable('Hash', json_encode(sha1(json_encode($Content) . json_encode($ReceiverContext))));

            }
        }
        if (isset($Template)) {
            $Template->setVariable('Callback', json_encode(false));
            return $Template->getContent();
        } else {
            throw new \Exception('Pipeline has no Emitter');
        }
    }

    /**
     * @param AbstractEmitter $Emitter
     * @return string
     */
    private function getNotifyMessage(AbstractEmitter $Emitter)
    {
        if (empty(($LoadingTitle = $Emitter->getLoadingTitle()))) {
            $LoadingTitle = $this->LoadingTitle;
        }
        if (empty(($LoadingMessage = $Emitter->getLoadingMessage()))) {
            $LoadingMessage = $this->LoadingMessage;
        }
        if (empty(($SuccessTitle = $Emitter->getSuccessTitle()))) {
            $SuccessTitle = $this->SuccessTitle;
        }
        if (empty(($SuccessMessage = $Emitter->getSuccessMessage()))) {
            $SuccessMessage = $this->SuccessMessage;
        }

        return 'onLoad: { Title: ' . json_encode($LoadingTitle)
            . ', Message: ' . json_encode($LoadingMessage)
            . ' }, onSuccess: { Title: ' . json_encode($SuccessTitle)
            . ', Message: ' . json_encode($SuccessMessage)
            . ' }';
    }
}
