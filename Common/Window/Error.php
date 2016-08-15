<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\System\Extension\Extension;

/**
 * Class Error
 *
 * @package SPHERE\Common\Window
 */
class Error extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param integer|string $Code
     * @param null           $Message
     * @param bool           $IsReportable
     */
    public function __construct($Code, $Message = null, $IsReportable = true)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Error.twig');

        $this->Template->setVariable('ErrorCode', $Code);
        if (null === $Message) {
            switch ($Code) {
                case 404:
                    $this->Template->setVariable('ErrorMessage',
                        'Die angeforderte Ressource konnte nicht gefunden werden');
                    break;
                default:
                    $this->Template->setVariable('ErrorMessage', '');
            }
        } else {

            $Path = parse_url( $this->getRequest()->getUrl(), PHP_URL_PATH );
            parse_str( parse_url( $this->getRequest()->getUrl(), PHP_URL_QUERY ), $Query );
            unset( $Query['_Sign'] );
            $Query = json_encode( $Query );

            $Message = new Paragraph('Error-Log: ['.$this->getRequest()->getHost().'] '.$Path.' > '.$Query).$Message;

            $this->Template->setVariable('ErrorMessage', $Message);
            if( $IsReportable ) {
                $this->Template->setVariable('ErrorMenu', array(
                        new Form(
                            new FormGroup(
                                new FormRow(
                                    new FormColumn(array(
                                        ( new HiddenField('TicketSubject') )->setDefaultValue(urlencode($Code.' Account: '.
                                            ( ( $Account = Account::useService()->getAccountBySession() ) ? $Account->getId() : '' ))),
                                        ( new HiddenField('TicketMessage') )->setDefaultValue(urlencode($Message)),
                                        new Primary('Fehlerbericht senden')
                                    ))
                                )
                            )
                            , null, '/Platform/Assistance/Support')
                    )
                );
            }
        }
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

        return $this->Template->getContent();
    }
}
