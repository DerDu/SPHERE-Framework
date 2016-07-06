<?php
namespace SPHERE\Application\Manual\Support;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Mail;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Support\Support as SystemSupport;
use SPHERE\System\Support\Type\YouTrack;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Manual\Support
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Ticket
     * @param null $Attachment
     *
     * @return Stage
     */
    public function frontendSupport($Ticket = null, $Attachment = null)
    {

        $Stage = new Stage('Feedback & Support', 'Ticket eröffnen');

        /** @var YouTrack $YouTrack */
        $YouTrack = (new SystemSupport(new YouTrack()))->getSupport();

        try {
            $TicketList = $YouTrack->ticketList();
            array_walk($TicketList, function (&$Ticket) {

                $tblAccount = Account::useService()->getAccountBySession();
                if ($tblAccount) {
                    if (!preg_match('!\bAccount: '.$tblAccount->getId().'\b!is',
                        $Ticket[0])
                    ) {
                        $Ticket = false;
                    } else {
                        if (!isset( $Ticket[1] )) {
                            $Ticket[1] = '';
                        }
                        if (!isset( $Ticket[1] )) {
                            $Ticket[2] = '';
                        }
                        switch (strtoupper($Ticket[2])) {
                            case 'ERFASST': {
                                $Label = 'label-primary';
                                break;
                            }
                            case 'ZU BESPRECHEN': {
                                $Label = 'label-warning';
                                break;
                            }
                            case 'OFFEN': {
                                $Label = 'label-danger';
                                break;
                            }
                            case 'IN BEARBEITUNG': {
                                $Label = 'label-success';
                                break;
                            }
                            default:
                                $Label = 'label-default';
                        }
                        $Ticket[1] = preg_replace(array('!^\{[^\}]*?\}!is', '!\{[^\}]*?\}$!is'), '', $Ticket[1]);
                        $Ticket[1] = utf8_decode($Ticket[1]);
                        $Ticket = new Info(
                            '<strong>'.$Ticket[0].'</strong>'
                            .'<div class="pull-right label '.$Label.'"><samp>'.$Ticket[2].'</samp></div>'
                            .'<hr/><small>'.nl2br($Ticket[1]).'</small>'
                        );
                    }
                } else {
                    $Ticket = false;
                }
            });
            $TicketList = array_filter($TicketList);
            if (empty( $TicketList )) {
                $TicketList = new Success('Sie haben keine offenen Supportanfragen');
            } else {
                krsort($TicketList);
            }
        } catch (\Exception $Exception) {
            $TicketList = new Warning('Ihre aktuellen Anfragen konnten leider nicht geladen werden');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            Support::useService()->createTicket(
                                $this->formTicket()
                                , $Ticket, $Attachment)
                            , 6),
                        new LayoutColumn(
                            new Panel('Ihre Support-Anfragen',
                                $TicketList
                            )
                            , 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formTicket()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Feedback oder Support-Anfrage', array(
                            new TextField('Ticket[Mail]', 'meine@email.de', 'Ihre Email-Adresse'.new Danger(' *')),
                            new TextField('Ticket[Subject]', 'Thema', 'Betreff der Anfrage'.new Danger(' *')),
                            new TextField('Ticket[CallBackNumber]', 'Vorwahl/Telefonnummer', 'Rückrufnummer'),
                            new TextArea('Ticket[Body]', 'Meine Frage oder mein Problem',
                                'Inhalt der Nachricht'.new Danger(' *')),
                            new FileUpload('Attachment', 'z.B. ein Screenshot', 'Optionaler Datei-Anhang'),
                        ), Panel::PANEL_TYPE_INFO,
                            new Primary('Absenden', new Mail()).new Danger(new Small(' (* Pflichtfeld)')))),
                ))
            )
        );
    }
}
