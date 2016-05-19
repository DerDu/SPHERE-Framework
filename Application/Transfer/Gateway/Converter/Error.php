<?php
namespace SPHERE\Application\Transfer\Gateway\Converter;

use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted as MutedText;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;

class Error
{

    const ERROR_LEVEL_INFO_0 = 10;
    const ERROR_LEVEL_INFO_1 = 11;
    const ERROR_LEVEL_INFO_2 = 12;
    const ERROR_LEVEL_INFO_3 = 13;
    const ERROR_LEVEL_WARNING_0 = 20;
    const ERROR_LEVEL_WARNING_1 = 21;
    const ERROR_LEVEL_WARNING_2 = 22;
    const ERROR_LEVEL_WARNING_3 = 23;
    const ERROR_LEVEL_DANGER_0 = 30;
    const ERROR_LEVEL_DANGER_1 = 31;
    const ERROR_LEVEL_DANGER_2 = 32;
    const ERROR_LEVEL_DANGER_3 = 33;

    /** @var int $Level */
    private $Level = 0;
    /** @var string $Message */
    private $Message = '';
    /** @var string $Description */
    private $Description = '';

    /**
     * Error constructor.
     *
     * @param string $Message
     * @param int    $Level
     * @param string $Description
     */
    public function __construct($Message, $Level = self::ERROR_LEVEL_INFO_0, $Description = '')
    {

        $this->Message = $Message;
        $this->Level = $Level;
        $this->Description = $Description;
    }

    /**
     * @return int
     */
    public function getLevel()
    {

        return $this->Level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {

        return $this->Message;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @return string
     */
    function __toString()
    {

        return (string)$this->getImpactGui();
    }

    public function getImpactGui()
    {

        if ($this->Message && $this->Description) {
            $Description = new MutedText(': '.$this->Description);
        } else {
            $Description = ( $this->Description ? new MutedText(' '.$this->Description) : '' );
        }
        $Message = ( $this->Message ? ' '.new Bold($this->Message) : '' );
        switch ($this->Level) {
            case self::ERROR_LEVEL_INFO_0:
                $Frontend = new InfoText(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_INFO_1:
                $Frontend = new InfoText(new WarningIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_WARNING_0:
                $Frontend = new WarningText(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_WARNING_1:
                $Frontend = new WarningText(new WarningIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_DANGER_0:
                $Frontend = new DangerText(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_DANGER_1:
                $Frontend = new DangerText(new WarningIcon().$Message.$Description);
                break;

            case self::ERROR_LEVEL_INFO_2:
                $Frontend = new InfoMessage(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_INFO_3:
                $Frontend = new InfoMessage(new WarningIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_WARNING_2:
                $Frontend = new WarningMessage(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_WARNING_3:
                $Frontend = new WarningMessage(new WarningIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_DANGER_2:
                $Frontend = new DangerMessage(new InfoIcon().$Message.$Description);
                break;
            case self::ERROR_LEVEL_DANGER_3:
                $Frontend = new DangerMessage(new WarningIcon().$Message.$Description);
                break;
            default:
                $Frontend = new InfoText(new InfoIcon().$Message.$Description);
        }
        return $Frontend;
    }
}
