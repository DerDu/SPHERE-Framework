<?php

namespace SPHERE\Application\Billing\Accounting\SchoolAccount;

use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Entity\TblSchoolAccount;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
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
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\SchoolAccount
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendSchoolAccount($Account = null)
    {

        $Stage = new Stage('Schulkonten', 'Übersicht');
        $Stage->setMessage(new Info('Hier werden alle Kontodaten der Schule für die Rechnungserstellung festgelegt.'));

        $tblSchoolAccountList = SchoolAccount::useService()->getSchoolAccountAll();
        $TableContent = array();
        if ($tblSchoolAccountList) {
            array_walk($tblSchoolAccountList, function (TblSchoolAccount $tblSchoolAccount) use (&$TableContent) {
                $tblCompany = $tblSchoolAccount->getServiceTblCompany();
                $tblType = $tblSchoolAccount->getServiceTblType();
                if ($tblCompany && $tblType) {
                    $Item['BankName'] = '';
                    $Item['Owner'] = '';
                    $Item['IBAN'] = '';
                    $Item['BIC'] = '';
                    $Item['CompanyName'] = '';
                    $tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompany, $tblType);
                    if ($tblSchool) {
                        $Item['BankName'] = $tblSchoolAccount->getBankName();
                        $Item['Owner'] = $tblSchoolAccount->getOwner();
                        $Item['IBAN'] = $tblSchoolAccount->getIBAN();
                        $Item['BIC'] = $tblSchoolAccount->getBIC();
                        if ($tblSchoolAccount->getServiceTblType()) {
                            $Item['CompanyName'] = new WarningText(new Small($tblSchoolAccount->getServiceTblType()->getName()))
                                .' '.$tblCompany->getDisplayName();
                        } else {
                            $Item['CompanyName'] = $tblCompany->getDisplayName();
                        }

                        $Item['Option'] = new Standard('', '/Billing/Accounting/SchoolAccount/Edit', new Edit(), array('Id' => $tblSchoolAccount->getId()))
                            .new Standard('', '/Billing/Accounting/SchoolAccount/Destroy', new Disable(), array('Id' => $tblSchoolAccount->getId()));

                        array_push($TableContent, $Item);
                    } else {
                        $Item['BankName'] = $tblSchoolAccount->getBankName();
                        $Item['Owner'] = $tblSchoolAccount->getOwner();
                        $Item['IBAN'] = $tblSchoolAccount->getIBAN();
                        $Item['BIC'] = $tblSchoolAccount->getBIC();
                        if ($tblSchoolAccount->getServiceTblType()) {
                            $Item['CompanyName'] = $tblSchoolAccount->getServiceTblType()->getName().' '.$tblCompany->getDisplayName();
                        } else {
                            $Item['CompanyName'] = $tblCompany->getDisplayName();
                        }

                        $Item['Option'] = new Standard('', '/Billing/Accounting/SchoolAccount/Destroy', new Disable(), array('Id' => $tblSchoolAccount->getId()));

                        array_push($TableContent, $Item);
                    }
                }
            });
        }

        $Form = new Form(new FormGroup(new FormRow(new FormColumn(new Warning(nl2br(( 'Keine Schulen ohne Kontodaten gefunden
                                                                    (Schule unter Einstellungen/Mandant/Schulen auswählen um die Auswahl zu erweitern)' )))))));
        $tblSchoolAll = School::useService()->getSchoolAll();

        $tblSchoolList = array();
        if ($tblSchoolAll) {
            foreach ($tblSchoolAll as $tblSchool) {
                if (!SchoolAccount::useService()->getSchoolAccountByCompanyAndType($tblSchool->getServiceTblCompany(), $tblSchool->getServiceTblType())) {
                    $tblSchoolList[] = $tblSchool;
                }
            }
        }
//        $tblCompanyList = array_filter($tblCompanyList);
        if (!empty( $tblSchoolList )) {
            $Form = $this->formSchoolAccountCreate($tblSchoolList);
            $Form->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( empty( $TableContent ) ? new Warning('Keine Schule mit Kontodaten hinterlegt') :
                                new TableData($TableContent, null, array('CompanyName' => 'Schule',
                                                                         'Owner'       => 'Besitzer',
                                                                         'BankName'    => 'Name der Bank',
                                                                         'IBAN'        => 'IBAN',
                                                                         'BIC'         => 'BIC',
                                                                         'Option'      => '',
                                ))
                            )
                        )
                    )
                    , new Title(new ListingTable().' Übersicht'))
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(SchoolAccount::useService()->createSchoolAccount($Form, $Account))
                        )
                    )
                    , new Title(new PlusSign().' Kontoinformationen eintragen'))
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Account
     *
     * @return Stage|string
     */
    public function frontendSchoolAccountEdit($Id = null, $Account = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Schulkonto');
        $Stage->setDescription('Bearbeiten');

        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/SchoolAccount', new ChevronLeft()));

        $tblSchoolAccount = ( $Id == null ? false : SchoolAccount::useService()->getSchoolAccountById($Id) );
        if (!$tblSchoolAccount) {
            return $Stage.new Warning('Kontodaten der Schule nicht gefunden').
            new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_ERROR);
        }
        $Content = 'fehlt';
        $tblCompany = $tblSchoolAccount->getServiceTblCompany();
        if ($tblCompany) {
            $Content = $tblCompany->getDisplayName();
        }

        $Form = $this->formSchoolAccountEdit($tblSchoolAccount);
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schule', $Content, Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(
                            new Title(new Edit().' Bearbeiten')
                            .new Well(SchoolAccount::useService()->updateSchoolAccount($Form, $tblSchoolAccount, $Account))
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $tblSchoolAll
     *
     * @return Form
     */
    public function formSchoolAccountCreate($tblSchoolAll)
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Schule', array(
                            new SelectBox('Account[School]', 'Auswahl', array(
                                '{{ ServiceTblCompany.getDisplayName }} - {{ ServiceTblType.getName }}' => $tblSchoolAll
                            ), new Education())
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Account[Owner]', '', 'Kontoinhaber'),
                            new TextField('Account[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Account[IBAN]', '', 'IBAN'),
                                new TextField('Account[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 6),
                ))
            )
        );
    }

    /**
     * @param TblSchoolAccount $tblSchoolAccount
     *
     * @return Form
     */
    public function formSchoolAccountEdit(TblSchoolAccount $tblSchoolAccount)
    {

        $Global = $this->getGlobal();
        $Global->POST['Account']['Owner'] = $tblSchoolAccount->getOwner();
        $Global->POST['Account']['BankName'] = $tblSchoolAccount->getBankName();
        $Global->POST['Account']['IBAN'] = $tblSchoolAccount->getIBAN();
        $Global->POST['Account']['BIC'] = $tblSchoolAccount->getBIC();
        $Global->savePost();


        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Account[Owner]', '', 'Kontoinhaber'),
                            new TextField('Account[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Account[IBAN]', '', 'IBAN'),
                                new TextField('Account[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 6),
                )), new \SPHERE\Common\Frontend\Form\Repository\Title(new PlusSign().' Kontoinformationen eintragen')
            )
        );
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendSchoolAccountDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Kontoinformationen', 'Löschen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/SchoolAccount', new ChevronLeft()));
        if (( $tblSchoolAccount = ( $Id == null ? false : SchoolAccount::useService()->getSchoolAccountById($Id) ) )) {
            if (!$Confirm) {

                $Content = 'Schule nicht gefunden';
                $tblCompany = $tblSchoolAccount->getServiceTblCompany();
                if ($tblCompany) {
                    $Content = $tblCompany->getDisplayName();
                }

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel(new Question().' Die Kontoinformationen dieser Schule wirklich löschen?',
                                        $Content, Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Billing/Accounting/SchoolAccount/Destroy', new Ok(),
                                            array('Id' => $Id, 'Confirm' => true))
                                        .new Standard('Nein', '/Billing/Accounting/SchoolAccount', new Disable())
                                    )
                                    , 6),
                            ))
                        )
                    )
                );
            } else {
                // Destroy Division
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( SchoolAccount::useService()->destroySchoolAccount($tblSchoolAccount)
                                ? new Success('Kontoinformationen entfernt',
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success())
                                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_SUCCESS)
                                : new Danger('Die Kontoinformationen konnte nicht entfernt werden',
                                    new Ban())
                                .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            return $Stage.new Warning('Schule nicht gefunden!', new Ban())
            .new Redirect('/Billing/Accounting/SchoolAccount', Redirect::TIMEOUT_ERROR);
        }
        return $Stage;
    }
}
