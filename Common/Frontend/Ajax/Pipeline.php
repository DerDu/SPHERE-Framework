<?php
namespace SPHERE\Common\Frontend\Ajax;

use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\TwigHandler;

/**
 * Class Pipeline
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
class Pipeline
{

    /** @var AbstractEmitter[] $Emitter */
    private $Emitter = array();
    /** @var string $SuccessMessage */
    private $SuccessMessage = '';

    /**
     * @param $Message
     * @return $this
     */
    public function setSuccessMessage($Message)
    {
        $this->SuccessMessage = $Message;
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
     * @param Form $Form
     * @return string
     */
    public function parseScript( Form $Form = null )
    {

        (new CacheFactory())->createHandler(new TwigHandler())->clearCache();
        $Template = Template::getTemplate(__DIR__ . '/Pipeline.twig');

        foreach ($this->Emitter as $Index => $Emitter) {

            $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();

            if ($Index !== 0) {
                $Template->setVariable('CALLBACK', '.always(function(){' . file_get_contents(__DIR__ . '/Pipeline.twig') . '})');
                $Template = Template::getTwigTemplateString($Template->getContent());
            }

            if( $Form === null ) {
                if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                    $Method = 'GET';
                } else {
                    $Method = 'POST';
                }
                $Template->setVariable('METHOD', $Method);
                $Template->setVariable('POST', $Emitter->getAjaxPostPayload());
            } else {
                $Template->setVariable('METHOD', 'POST');
                $Template->setVariable('POST', 'jQuery("form#'.$Form->getHash().'").serializeArray()');
            }

            $Template->setVariable('URL', $Url);
            $Template->setVariable('URL_BASE', $Emitter->getAjaxUri());

            $Receiver = $Emitter->getAjaxReceiver();
            $Template->setVariable('ReceiverList', $Receiver);

            $Template->setVariable('RESPONSE', AbstractReceiver::RESPONSE_CONTAINER);
        }

        if( !empty( $this->SuccessMessage ) ) {
            $Template->setVariable('NOTIFY', "
            $.notifyClose();
            $.notify({
            // options
            message: '" . $this->SuccessMessage . "'
            }, {
            // settings
            newest_on_top: true,
            type: 'success',
            delay: 1000,
            placement: {
            from: 'top',
            align: 'center'
            }
            });");
        }

        return $Template->getContent();
    }

    /**
     *
     */
    public function __toString()
    {
        return (string)'<script type="text/javascript">'
            . 'executeScript(function() {'
            . 'Client.Use("ModAlways", function(){'
            . $this->parseScript()
            . '});'
            . '});'
            . '</script>';
    }

    /**
     * @return AbstractEmitter[]
     */
    public function getEmitter()
    {
        return $this->Emitter;
    }
}