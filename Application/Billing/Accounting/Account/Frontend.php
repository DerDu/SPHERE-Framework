<?php

namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\BarCode;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Account
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendAccountFibu($Account = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('FIBU-Konten');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt die verfügbaren Finanzbuchhaltungskonten an');
//        $Stage->addButton(
//            new Standard('FIBU-Konto anlegen', '/Billing/Accounting/Account/Create', new Plus())
//        );

        $tblAccountAll = Account::useService()->getAccountAll();
        $TableContent = array();
        if (!empty( $tblAccountAll )) {
            /** @var TblAccount $tblAccount */
            array_walk($tblAccountAll, function (TblAccount $tblAccount) use (&$TableContent) {

                if ($tblAccount->isActive() === true) {
                    $Item['Option'] = (new Standard('', '/Billing/Accounting/Account/Deactivate',
                        new Remove(), array('Id' => $tblAccount->getId()),
                        'Deaktivieren'))->__toString();
                } else {
                    $Item['Option'] = (new Standard('', '/Billing/Accounting/Account/Activate',
                        new Ok(), array(
                            'Id' => $tblAccount->getId()
                        ), 'Aktivieren'))->__toString();
                }
                $Item['Number'] = $tblAccount->getNumber();
                $Item['Description'] = $tblAccount->getDescription();
                $Item['Taxes'] = $tblAccount->getTblAccountKey()->getValue();
                $Item['Code'] = $tblAccount->getTblAccountKey()->getCode();
                $Item['Typ'] = $tblAccount->getTblAccountType()->getName();
                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formFiBu()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Number'      => 'Kennziffer',
                                    'Description' => 'Beschreibung',
                                    'Typ'         => 'Konto',
                                    'Taxes'       => 'MwSt.',
                                    'Code'        => 'Code',
                                    'Option'      => ''
                                )
                            )
                        )
                    ), new Title(new ListingTable().' Übersicht')
                )
            ).
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Account::useService()->createAccount($Form, $Account)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formFiBu()
    {

        $tblAccountKey = Account::useService()->getKeyValueAll();
        $tblAccountType = Account::useService()->getTypeValueAll();

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('FiBu Konto',
                            array(new TextField('Account[Number]', 'Kennziffer', 'Kennziffer', new BarCode()),
                                new SelectBox('Account[Type]', 'Typ', array('Name' => $tblAccountType)),
                                new SelectBox('Account[Key]', 'Mehrwertsteuer', array('Value' => $tblAccountKey))),
                            Panel::PANEL_TYPE_INFO
                        )
                        , 6),
                    new FormColumn(
                        new Panel('Sonnstiges',
                            new TextArea('Account[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendActivateAccountFibu($Id = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Aktivierung');
        $tblAccount = $Id === null ? false : Account::useService()->getAccountById($Id);
        if ($tblAccount) {
            Account::useService()->changeFibuActivate($tblAccount);
            $Stage->setContent(new Success('Aktivierung erfolgreich')
                .new Redirect('/Billing/Accounting/Account', Redirect::TIMEOUT_SUCCESS));
        } else {
            $Stage->setContent(new Danger('Aktivierung fehlgeschlagen Account nicht gefunden')
                .new Redirect('/Billing/Accounting/Account', Redirect::TIMEOUT_ERROR));
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendDeactivateAccountFibu($Id = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Deaktivierung');
        $tblAccount = $Id === null ? false : Account::useService()->getAccountById($Id);
        if ($tblAccount) {
            Account::useService()->changeFibuDeactivate($tblAccount);
            $Stage->setContent(new Success('Deaktivierung erfolgreich')
                .new Redirect('/Billing/Accounting/Account', Redirect::TIMEOUT_SUCCESS));
        } else {
            $Stage->setContent(new Danger('Deaktivierung fehlgeschlagen Account nicht gefunden')
                .new Redirect('/Billing/Accounting/Account', Redirect::TIMEOUT_ERROR));
        }

        return $Stage;
    }
}
