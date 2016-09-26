<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Mail
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $Address, $Type)
    {

        $Stage = new Stage('E-Mail Adresse', 'Hinzufügen');
        $Stage->setMessage('Eine E-Mail Adresse zur gewählten Person hinzufügen');

        $tblPerson = Person::useService()->getPersonById($Id);
        if(!$tblPerson){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/People/Person', new ChevronLeft(),
                array('Id' => $tblPerson->getId())
            )
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblPerson->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Mail::useService()->createMailToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new PlusSign() . ' Hinzufügen')),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formAddress()
    {

        $tblMailAll = Mail::useService()->getMailAll();
        $tblTypeAll = Mail::useService()->getTypeAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('E-Mail Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new MailField('Address', 'E-Mail Adresse', 'E-Mail Adresse', new MailIcon() ))->setRequired()
//                                (new AutoCompleter('Address', 'E-Mail Adresse', 'E-Mail Adresse',
//                                    array('Address' => $tblMailAll), new MailIcon()
//                                ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $Address, $Type)
    {

        $Stage = new Stage('E-Mail Adresse', 'Hinzufügen');
        $Stage->setMessage('Eine E-Mail Adresse zur gewählten Firma hinzufügen');

        $tblCompany = Company::useService()->getCompanyById($Id);
        if ($tblCompany) {

            $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                array('Id' => $tblCompany->getId())
            ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Firma',
                                    array(
                                        new Bold($tblCompany->getName()),
                                        $tblCompany->getExtendedName()),
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            )
                        ),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Mail::useService()->createMailToCompany(
                                        $this->formAddress()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblCompany, $Address, $Type
                                    )
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Hinzufügen')),
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Firma nicht gefunden.')
            . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $Address, $Type)
    {

        $Stage = new Stage('E-Mail Adresse', 'Bearbeiten');
        $Stage->setMessage('Die E-Mail Adresse der gewählten Person ändern');

        $tblToPerson = Mail::useService()->getMailToPersonById($Id);

        if (!$tblToPerson->getServiceTblPerson()){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/People/Person', new ChevronLeft(),
                array('Id' => $tblToPerson->getServiceTblPerson()->getId())
            )
        );

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Address'])) {
            $Global->POST['Address'] = $tblToPerson->getTblMail()->getAddress();
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                new Bold($tblToPerson->getServiceTblPerson()->getFullName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Mail::useService()->updateMailToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToPerson, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new Edit() . ' Bearbeiten')),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param string $Address
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $Address, $Type)
    {

        $Stage = new Stage('E-Mail Adresse', 'Bearbeiten');
        $Stage->setMessage('Die E-Mail Adresse der gewählten Firma ändern');

        $tblToCompany = Mail::useService()->getMailToCompanyById($Id);

        if (!$tblToCompany->getServiceTblCompany()){
            return $Stage . new Danger('Firma nicht gefunden', new Ban())
            . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
            array('Id' => $tblToCompany->getServiceTblCompany()->getId())
        ));

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Address'])) {
            $Global->POST['Address'] = $tblToCompany->getTblMail()->getAddress();
            $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building().' Firma', array(
                                new Bold($tblToCompany->getServiceTblCompany()->getName()),
                                $tblToCompany->getServiceTblCompany()->getExtendedName()),
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Mail::useService()->updateMailToCompany(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToCompany, $Address, $Type
                                )
                            )
                        )
                    )
                ), new Title(new Edit() . ' Bearbeiten')),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson(TblPerson $tblPerson)
    {

        $mailExistsList = array();
        $tblMailAll = Mail::useService()->getMailAllByPerson($tblPerson);
        if ($tblMailAll !== false) {
            array_walk($tblMailAll, function (TblToPerson &$tblToPerson) use ($mailExistsList) {

                if (array_key_exists($tblToPerson->getId(), $mailExistsList)){
                    $tblToPerson = false;
                } else {
                    $mailExistsList[$tblToPerson->getId()] = $tblToPerson;

                    $Panel = array(
                        new Mailto($tblToPerson->getTblMail()->getAddress()
                            , $tblToPerson->getTblMail()->getAddress(), new Envelope())
                    );
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            new MailIcon() . ' ' . $tblToPerson->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_SUCCESS,

                            new Standard(
                                '', '/People/Person/Mail/Edit', new Edit(),
                                array('Id' => $tblToPerson->getId()),
                                'Bearbeiten'
                            )
                            . new Standard(
                                '', '/People/Person/Mail/Destroy', new Remove(),
                                array('Id' => $tblToPerson->getId()), 'Löschen'
                            )
                        )
                        , 3);
                }
            });

            $tblMailAll = array_filter($tblMailAll);
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblRelationshipMailAll = Mail::useService()->getMailAllByPerson($tblRelationship->getServiceTblPersonFrom());
                        if ($tblRelationshipMailAll) {
                            foreach ($tblRelationshipMailAll as $tblMail) {
                                if (!array_key_exists($tblMail->getId(), $mailExistsList)) {
                                    $mailExistsList[$tblMail->getId()] = $tblMail;

                                    $Panel = array(
                                        new Mailto($tblMail->getTblMail()->getAddress()
                                            , $tblMail->getTblMail()->getAddress(), new Envelope())
                                    );
                                    if ($tblMail->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblMail->getRemark())));
                                    }

                                    $tblMail = new LayoutColumn(
                                        new Panel(
                                            new MailIcon() . ' ' . $tblMail->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonFrom()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonFrom()->getFullName()
                                        )
                                        , 3);

                                    if ($tblMailAll !== false) {
                                        $tblMailAll[] = $tblMail;
                                    } else {
                                        $tblMailAll = array();
                                        $tblMailAll[] = $tblMail;
                                    }

                                }
                            }
                        }
                    }

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                        $tblRelationshipMailAll = Mail::useService()->getMailAllByPerson($tblRelationship->getServiceTblPersonTo());
                        if ($tblRelationshipMailAll) {
                            foreach ($tblRelationshipMailAll as $tblMail) {
                                if (!array_key_exists($tblMail->getId(), $mailExistsList)) {
                                    $mailExistsList[$tblMail->getId()] = $tblMail;

                                    $Panel = array(
                                        new Mailto($tblMail->getTblMail()->getAddress()
                                            , $tblMail->getTblMail()->getAddress(), new Envelope())
                                    );
                                    if ($tblMail->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblMail->getRemark())));
                                    }

                                    $tblMail = new LayoutColumn(
                                        new Panel(
                                            new MailIcon() . ' ' . $tblMail->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonTo()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonTo()->getFullName()
                                        )
                                        , 3);

                                    if ($tblMailAll !== false) {
                                        $tblMailAll[] = $tblMail;
                                    } else {
                                        $tblMailAll = array();
                                        $tblMailAll[] = $tblMail;
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tblMailAll === false) {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning('Keine E-Mail Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblMail
         */
        foreach ($tblMailAll as $tblMail) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblMail);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToPerson($Id, $Confirm = false)
    {

        $Stage = new Stage('E-Mail Adresse', 'Löschen');
        if ($Id) {
            $tblToPerson = Mail::useService()->getMailToPersonById($Id);
            $tblPerson = $tblToPerson->getServiceTblPerson();

            if (!$tblPerson){
                return $Stage . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            $Stage->addButton(
                new Standard('Zurück', '/People/Person', new ChevronLeft(),
                    array('Id' => $tblPerson->getId())
                )
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            new Bold($tblPerson->getFullName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese E-Mail Adresse wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getTblMail()->getAddress(),
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Mail/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/People/Person', new Disable(),
                                array('Id' => $tblPerson->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Mail::useService()->removeMailToPerson($tblToPerson)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die E-Mail Adresse wurde gelöscht')
                                : new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht gelöscht werden')
                            ),
                            new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblPerson->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToCompany($Id, $Confirm = false)
    {

        $Stage = new Stage('E-Mail Adresse', 'Löschen');
        if ($Id) {
            $tblToCompany = Mail::useService()->getMailToCompanyById($Id);
            $tblCompany = $tblToCompany->getServiceTblCompany();

            if (!$tblCompany){
                return $Stage . new Danger('Firma nicht gefunden', new Ban())
                . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            $Stage->addButton( new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                array('Id' => $tblCompany->getId())
            ));
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Firma',
                            array(
                                new Bold($tblCompany->getName()),
                                $tblCompany->getExtendedName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese E-Mail Adresse wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getTblMail()->getAddress(),
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Mail/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/Corporation/Company', new Disable(),
                                array('Id' => $tblCompany->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Mail::useService()->removeMailToCompany($tblToCompany)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() .  ' Die E-Mail Adresse wurde gelöscht')
                                : new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Corporation/Company', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die E-Mail Adresse konnte nicht gefunden werden'),
                        new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return Layout
     */
    public function frontendLayoutCompany(TblCompany $tblCompany)
    {

        $tblMailAll = Mail::useService()->getMailAllByCompany($tblCompany);
        if ($tblMailAll !== false) {
            array_walk($tblMailAll, function (TblToCompany &$tblToCompany) {

                $Panel = array(new Mailto($tblToCompany->getTblMail()->getAddress()
                    , $tblToCompany->getTblMail()->getAddress(), new Envelope()));
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new MailIcon() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                        Panel::PANEL_TYPE_SUCCESS,

                        new Standard(
                            '', '/Corporation/Company/Mail/Edit', new Edit(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Mail/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblMailAll = array(
                new LayoutColumn(
                    new Warning('Keine E-Mail Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblMail
         */
        foreach ($tblMailAll as $tblMail) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblMail);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }
}
