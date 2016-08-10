<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
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
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
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
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\PhoneLink;
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
 * @package SPHERE\Application\Contact\Phone
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Hinzufügen');
        $Stage->setMessage('Eine Telefonnummer zur gewählten Person hinzufügen');

        $tblPerson = Person::useService()->getPersonById($Id);
        if(!$tblPerson){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück zur Person', '/People/Person', new ChevronLeft(),
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
                                Phone::useService()->createPhoneToPerson(
                                    $this->formNumber()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $Number, $Type
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
    private function formNumber()
    {

        $tblPhoneAll = Phone::useService()->getPhoneAll();
        $tblTypeAll = Phone::useService()->getTypeAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Telefonnummer',
                            array(
                                new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new AutoCompleter('Number', 'Telefonnummer', 'Telefonnummer',
                                    array('Number' => $tblPhoneAll), new PhoneIcon()
                                )
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
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Hinzufügen');
        $Stage->setMessage('Eine Telefonnummer zur gewählten Firma hinzufügen');

        $tblCompany = Company::useService()->getCompanyById($Id);
        if ($tblCompany) {
            $Stage->addButton(
                new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                    array('Id' => $tblCompany->getId())
                )
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new Building() . ' Firma',
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
                                    Phone::useService()->createPhoneToCompany(
                                        $this->formNumber()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblCompany, $Number, $Type
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
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Bearbeiten');
        $Stage->setMessage('Die Telefonnummer der gewählten Person ändern');

        $tblToPerson = Phone::useService()->getPhoneToPersonById($Id);

        if(!$tblToPerson->getServiceTblPerson()){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(
            new Standard('Zurück', '/People/Person', new ChevronLeft(),
                array('Id' => $tblToPerson->getServiceTblPerson()->getId())
            )
        );

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Number'])) {
            $Global->POST['Number'] = $tblToPerson->getTblPhone()->getNumber();
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new Building() . ' Person',
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
                                Phone::useService()->updatePhoneToPerson(
                                    $this->formNumber()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToPerson, $Number, $Type
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
     * @param string $Number
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $Number, $Type)
    {

        $Stage = new Stage('Telefonnummer', 'Bearbeiten');
        $Stage->setMessage('Die Telefonnummer der gewählten Firma ändern');

        $tblToCompany = Phone::useService()->getPhoneToCompanyById($Id);

        if (!$tblToCompany->getServiceTblCompany()){
            return $Stage . new Danger('Firma nicht gefunden', new Ban())
            . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
            array('Id' => $tblToCompany->getServiceTblCompany()->getId())
        ));

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Number'])) {
            $Global->POST['Number'] = $tblToCompany->getTblPhone()->getNumber();
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
                                Phone::useService()->updatePhoneToCompany(
                                    $this->formNumber()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToCompany, $Number, $Type
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

        $phoneExistsList = array();
        $tblPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPerson);
        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll, function (TblToPerson &$tblToPerson) use ($phoneExistsList) {

                if (array_key_exists($tblToPerson->getId(), $phoneExistsList)){
                    $tblToPerson = false;
                } else {
                    $phoneExistsList[$tblToPerson->getId()] = $tblToPerson;

                    $Panel = array(
                        new PhoneLink($tblToPerson->getTblPhone()->getNumber(),
                            $tblToPerson->getTblPhone()->getNumber(), new PhoneIcon())
                    );
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            (preg_match('!Fax!is',
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                                ? new PhoneFax()
                                : (preg_match('!Mobil!is',
                                    $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                                    ? new PhoneMobil()
                                    : new PhoneIcon()
                                )
                            ) . ' ' . $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $Panel,
                            (preg_match('!Notfall!is',
                                $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription())
                                ? Panel::PANEL_TYPE_DANGER
                                : Panel::PANEL_TYPE_SUCCESS
                            ),
                            new Standard(
                                '', '/People/Person/Phone/Edit', new Edit(),
                                array('Id' => $tblToPerson->getId()),
                                'Bearbeiten'
                            )
                            . new Standard(
                                '', '/People/Person/Phone/Destroy', new Remove(),
                                array('Id' => $tblToPerson->getId()), 'Löschen'
                            )
                        )
                        , 3);
                }
            });

            $tblPhoneAll = array_filter($tblPhoneAll);
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblRelationshipPhoneAll = Phone::useService()->getPhoneAllByPerson($tblRelationship->getServiceTblPersonFrom());
                        if ($tblRelationshipPhoneAll) {
                            foreach ($tblRelationshipPhoneAll as $tblPhone) {
                                if (!array_key_exists($tblPhone->getId(), $phoneExistsList)) {
                                    $phoneExistsList[$tblPhone->getId()] = $tblPhone;

                                    $Panel = array(
                                        new PhoneLink($tblPhone->getTblPhone()->getNumber(),
                                            $tblPhone->getTblPhone()->getNumber(), new PhoneIcon())
                                    );
                                    if ($tblPhone->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblPhone->getRemark())));
                                    }

                                    $tblPhone = new LayoutColumn(
                                        new Panel(
                                            (preg_match('!Fax!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? new PhoneFax()
                                                : (preg_match('!Mobil!is',
                                                    $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                    ? new PhoneMobil()
                                                    : new PhoneIcon()
                                                )
                                            ) . ' ' . $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription(),
                                            $Panel,
                                            (preg_match('!Notfall!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? Panel::PANEL_TYPE_DANGER
                                                : Panel::PANEL_TYPE_DEFAULT
                                            ),
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonFrom()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonFrom()->getFullName()
                                        )
                                        , 3);

                                    if ($tblPhoneAll !== false) {
                                        $tblPhoneAll[] = $tblPhone;
                                    } else {
                                        $tblPhoneAll = array();
                                        $tblPhoneAll[] = $tblPhone;
                                    }

                                }
                            }
                        }
                    }

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                        $tblRelationshipPhoneAll = Phone::useService()->getPhoneAllByPerson($tblRelationship->getServiceTblPersonTo());
                        if ($tblRelationshipPhoneAll) {
                            foreach ($tblRelationshipPhoneAll as $tblPhone) {
                                if (!array_key_exists($tblPhone->getId(), $phoneExistsList)) {
                                    $phoneExistsList[$tblPhone->getId()] = $tblPhone;

                                    $Panel = array(
                                        new PhoneLink($tblPhone->getTblPhone()->getNumber(),
                                            $tblPhone->getTblPhone()->getNumber(), new PhoneIcon())
                                    );
                                    if ($tblPhone->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblPhone->getRemark())));
                                    }

                                    $tblPhone = new LayoutColumn(
                                        new Panel(
                                            (preg_match('!Fax!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? new PhoneFax()
                                                : (preg_match('!Mobil!is',
                                                    $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                    ? new PhoneMobil()
                                                    : new PhoneIcon()
                                                )
                                            ) . ' ' . $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription(),
                                            $Panel,
                                            (preg_match('!Notfall!is',
                                                $tblPhone->getTblType()->getName() . ' ' . $tblPhone->getTblType()->getDescription())
                                                ? Panel::PANEL_TYPE_DANGER
                                                : Panel::PANEL_TYPE_DEFAULT
                                            ),
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonTo()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonTo()->getFullName()
                                        )
                                        , 3);

                                    if ($tblPhoneAll !== false) {
                                        $tblPhoneAll[] = $tblPhone;
                                    } else {
                                        $tblPhoneAll = array();
                                        $tblPhoneAll[] = $tblPhone;
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tblPhoneAll === false) {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
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

        $Stage = new Stage('Telefonnummer', 'Löschen');
        $Stage->addButton(new Backward(true));

        if ($Id) {
            $tblToPerson = Phone::useService()->getPhoneToPersonById($Id);
            if (!$tblToPerson) {
                //
                return $Stage.new Danger('Telefonnummer nicht gefunden', new Ban());
//                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
            }
            $tblPerson = $tblToPerson->getServiceTblPerson();

            if (!$tblPerson){
                return $Stage . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
            }

//            $Stage->addButton(
//                new Standard('Zurück', '/People/Person', new ChevronLeft(),
//                    array('Id' => $tblPerson->getId())
//                )
//            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            new Bold($tblPerson->getFullName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getTblPhone()->getNumber(),
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Phone/Destroy', new Ok(),
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
                            (Phone::useService()->removePhoneToPerson($tblToPerson)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde gelöscht')
                                : new Danger(new Ban() . ' Die Telefonnummer konnte nicht gelöscht werden')
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
                        new Danger(new Ban() . ' Die Telefonnummer konnte nicht gefunden werden'),
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

        $Stage = new Stage('Telefonnummer', 'Löschen');
        if ($Id) {
            $tblToCompany = Phone::useService()->getPhoneToCompanyById($Id);
            $tblCompany = $tblToCompany->getServiceTblCompany();

            if (!$tblCompany){
                return $Stage . new Danger('Firma nicht gefunden', new Ban())
                . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(),
                array('Id' => $tblCompany->getId())
            ));
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new Building() . ' Firma',
                            array(
                                new Bold($tblCompany->getName()),
                                $tblCompany->getExtendedName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese Telefonnummer wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getTblPhone()->getNumber(),
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Phone/Destroy', new Ok(),
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
                            (Phone::useService()->removePhoneToCompany($tblToCompany)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Telefonnummer wurde gelöscht')
                                : new Danger(new Ban() . ' Die Telefonnummer konnte nicht gelöscht werden')
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
                        new Danger(new Ban() . ' Die Telefonnummer konnte nicht gefunden werden'),
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

        $tblPhoneAll = Phone::useService()->getPhoneAllByCompany($tblCompany);
        if ($tblPhoneAll !== false) {
            array_walk($tblPhoneAll, function (TblToCompany &$tblToCompany) {

                $Panel = array(new PhoneLink($tblToCompany->getTblPhone()->getNumber(),
                    $tblToCompany->getTblPhone()->getNumber(), new PhoneIcon() ));
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        (preg_match('!Fax!is',
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                            ? new PhoneFax()
                            : (preg_match('!Mobil!is',
                                $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                                ? new PhoneMobil()
                                : new PhoneIcon()
                            )
                        ) . ' ' . $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                        $Panel,
                        (preg_match('!Notfall!is',
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription())
                            ? Panel::PANEL_TYPE_DANGER
                            : Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Standard(
                            '', '/Corporation/Company/Phone/Edit', new Edit(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Phone/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblPhoneAll = array(
                new LayoutColumn(
                    new Warning('Keine Telefonnummern hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblPhone
         */
        foreach ($tblPhoneAll as $tblPhone) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblPhone);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }
}
