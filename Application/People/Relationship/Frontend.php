<?php
namespace SPHERE\Application\People\Relationship;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Link;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Relationship
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param int $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Hinzufügen');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage(
            'Eine Beziehungen zur gewählten Person hinzufügen'
            . '<br/>Beispiel: Die Person (Vater) > hat folgende Beziehung (Sorgeberechtigt), Bemerkung: Vater > zu folgender Person (Kind)'
        );

        $tblPerson = Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(
                new Danger('Person nicht gefunden', new Ban())
                .new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
            );
            return $Stage;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Die Person',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Relationship::useService()->createRelationshipToPerson(
                                    $this->formRelationshipToPerson()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $To, $Type
                                )
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return Form
     */
    private function formRelationshipToPerson(TblToPerson $tblToPerson = null)
    {

        if ($tblToPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['To'])) {
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->POST['To'] = $tblToPerson->getServiceTblPersonTo()
                    ? $tblToPerson->getServiceTblPersonTo()->getId() : 0;
                $Global->savePost();
            }

            $currentPerson = $tblToPerson->getServiceTblPersonTo();
        } else {
            $currentPerson = false;
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('PERSON');
        $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
        $tblPersonAll = Person::useService()->getPersonAll();

        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($currentPerson) {

                // alle Hauptadressen
                $tblAddressToPersonList = Address::useService()->getAddressAllByPersonAndType($tblPerson,
                    Address::useService()->getTypeById(1));
                if ($tblAddressToPersonList) {
                    /** @var \SPHERE\Application\Contact\Address\Service\Entity\TblToPerson $tblAddressToPerson */
                    $tblAddressToPerson = reset($tblAddressToPersonList);
                } else {
                    $tblAddressToPerson = false;
                }

                if ($currentPerson && $currentPerson->getId() == $tblPerson->getId()) {
                    $tblPerson = array(
                        'Person' => new \SPHERE\Common\Frontend\Text\Repository\Warning($tblPerson->getFullName()) . ' (Aktuell hinterlegt)',
                        'Address' => $tblAddressToPerson ? $tblAddressToPerson->getTblAddress()->getGuiString() : ''
                    );
                } else {
                    $tblPerson = array(
                        'Person' => new RadioBox('To', $tblPerson->getFullName(), $tblPerson->getId())
                            . new PullRight(new Standard('', '/People/Person',
                                new PersonIcon(),
                                array('Id' => $tblPerson->getId()),
                                'zu ' . $tblPerson->getFullName() . ' wechseln')),
                        'Address' => $tblAddressToPerson ? $tblAddressToPerson->getTblAddress()->getGuiString() : ''
                    );
                }
            });
            $tblPersonAll = array_filter($tblPersonAll);
        } else {
            $tblPersonAll = array();
        }

        // Person Panel
        if ($currentPerson) {
            $PanelPerson = new Panel('zur folgenden Person ' . new PersonIcon(),
                array(
                    new \SPHERE\Common\Frontend\Text\Repository\Danger('AKTUELL hinterlegte Person, '),
                    new PullLeft(new RadioBox('To', $currentPerson->getFullName(), $currentPerson->getId()))
                    . new PullRight(new Standard('', '/People/Person',
                        new PersonIcon(),
                        array('Id' => $currentPerson->getId()),
                        'zu ' . $currentPerson->getFullName() . ' wechseln')),
                    new \SPHERE\Common\Frontend\Text\Repository\Danger('ODER eine andere Person wählen: '),
                    new TableData($tblPersonAll, null, array('Person' => 'Person wählen', 'Address' => 'Adresse')),
                ), Panel::PANEL_TYPE_INFO,
                new Standard('Neue Person anlegen', '/People/Person', new PersonIcon()
                    , array(), 'Die aktuell gewählte Person verlassen'
                )
            );
        } else {
            $PanelPerson = new Panel('zur folgenden Person ' . new PersonIcon(),
                array(
                    new TableData($tblPersonAll, null, array('Person' => 'Person wählen', 'Address' => 'Adresse')),
                ), Panel::PANEL_TYPE_INFO,
                new Standard('Neue Person anlegen', '/People/Person', new PersonIcon()
                    , array(), 'Die aktuell gewählte Person verlassen'
                )
            );
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung',
                            array(
                                new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new TextArea('Type[Remark]', 'Bemerkungen - z.B: Mutter / Vater / ..', 'Bemerkungen',
                                    new Pencil()
                                ),
                                new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                    new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 6),
                    new FormColumn(array(
                        $PanelPerson
                    ), 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param int $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Hinzufügen');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage(
            'Eine Beziehungen zur gewählten Person hinzufügen'
        );

        $tblPerson = Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(
                new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
            );
            return $Stage;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Die Person',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Relationship::useService()->createRelationshipToCompany(
                                    $this->formRelationshipToCompany()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $To, $Type
                                )
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return Form
     */
    private function formRelationshipToCompany(TblToCompany $tblToCompany = null)
    {

        if ($tblToCompany) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['To'])) {
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->POST['To'] = $tblToCompany->getServiceTblCompany()
                    ? $tblToCompany->getServiceTblCompany()->getId() : 0;
                $Global->savePost();
            }
            $currentCompany = $tblToCompany->getServiceTblCompany();
        } else {
            $currentCompany = false;
        }

        $tblGroup = Relationship::useService()->getGroupByIdentifier('COMPANY');
        $tblTypeAll = Relationship::useService()->getTypeAllByGroup($tblGroup);
        $tblCompanyAll = Company::useService()->getCompanyAll();

        if ($tblCompanyAll) {
            array_walk($tblCompanyAll, function (TblCompany &$tblCompany) use ($currentCompany) {

                // alle Hauptadressen
                $tblAddressToCompanyList = Address::useService()->getAddressAllByCompanyAndType($tblCompany,
                    Address::useService()->getTypeById(1));
                if ($tblAddressToCompanyList) {
                    /** @var \SPHERE\Application\Contact\Address\Service\Entity\TblToCompany $tblAddressToCompany */
                    $tblAddressToCompany = reset($tblAddressToCompanyList);
                } else {
                    $tblAddressToCompany = false;
                }

                if ($currentCompany && $currentCompany->getId() == $tblCompany->getId()) {
                    $tblCompany = array(
                        'Company' => new \SPHERE\Common\Frontend\Text\Repository\Warning($tblCompany->getName()) . ' (Aktuell hinterlegt)',
                        'Address' => $tblAddressToCompany ? $tblAddressToCompany->getTblAddress()->getGuiString() : ''
                    );
                } else {
                    $tblCompany = array(
                        'Company' => new RadioBox('To', $tblCompany->getName()
                                . new Container(new Container($tblCompany->getExtendedName()))
                                . new Container(new Container(new Muted($tblCompany->getDescription()))),
                                $tblCompany->getId())
                            . new PullRight(new Standard('', '/Corporation/Company',
                                new Building(),
                                array('Id' => $tblCompany->getId()),
                                'zu ' . $tblCompany->getName() . ' wechseln')),
                        'Address' => $tblAddressToCompany ? $tblAddressToCompany->getTblAddress()->getGuiString() : ''
                    );
                }
            });
            $tblCompanyAll = array_filter($tblCompanyAll);
        } else {
            $tblCompanyAll = array();
        }

        // Company Panel
        if ($currentCompany) {
            $PanelCompany = new Panel('zu folgender Firma ' . new Building(),
                array(
                    new \SPHERE\Common\Frontend\Text\Repository\Danger('AKTUELL hinterlegte Firma, '),
                    new PullLeft(new RadioBox('To', $currentCompany->getName()
                        . new Container(new Container($currentCompany->getExtendedName()))
                        . new Container(new Container(new Muted($currentCompany->getDescription()))),
                        $currentCompany->getId()))
                    . new PullRight(new Standard('', '/Corporation/Company',
                        new Building(),
                        array('Id' => $currentCompany->getId()),
                        'zu ' . $currentCompany->getDisplayName() . ' wechseln')),
                    new \SPHERE\Common\Frontend\Text\Repository\Danger('ODER eine andere Firma wählen: '),
                    new TableData($tblCompanyAll, null, array('Company' => 'Firma wählen', 'Address' => 'Adresse')),
                ), Panel::PANEL_TYPE_INFO,
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), 'Die aktuell gewählte Person verlassen'
                )
            );
        } else {
            $PanelCompany = new Panel('zu folgender Firma ' . new Building(),
                array(
                    new TableData($tblCompanyAll, null, array('Company' => 'Firma wählen', 'Address' => 'Adresse')),
                ), Panel::PANEL_TYPE_INFO,
                new Standard('Neue Firma anlegen', '/Corporation/Company', new Building()
                    , array(), 'Die aktuell gewählte Person verlassen'
                )
            );
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('hat folgende Beziehung',
                            array(
                                new SelectBox('Type[Type]', 'Beziehungstyp',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ),
                                new TextArea('Type[Remark]', 'Bemerkungen - z.B: Schulleiter / Geschäftsführer / ..',
                                    'Bemerkungen',
                                    new Pencil()
                                ),
                                new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                    new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
                                ),
                            ), Panel::PANEL_TYPE_INFO
                        ),
                    ), 6),
                    new FormColumn(array(
                        $PanelCompany
                    ), 6),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param int $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Bearbeiten');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage('Eine Beziehungen der gewählten Person ändern');

        /** @Var TblToPerson $tblToPerson */
        $tblToPerson = Relationship::useService()->getRelationshipToPersonById($Id);

        if (!$tblToPerson->getServiceTblPersonFrom()) {
            $Stage->setContent(
                new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
            );
            return $Stage;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                $tblToPerson->getServiceTblPersonFrom()->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Relationship::useService()->updateRelationshipToPerson(
                                    $this->formRelationshipToPerson($tblToPerson)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToPerson, $tblToPerson->getServiceTblPersonFrom(), $To, $Type
                                )
                            )
                        )
                    )
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param int $To
     * @param array $Type
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $To, $Type)
    {

        $Stage = new Stage('Beziehungen', 'Bearbeiten');
        $Stage->addButton(new Backward(true));
        $Stage->setMessage('Eine Beziehungen der gewählten Person ändern');

        /** @Var TblToPerson $tblToPerson */
        $tblToCompany = Relationship::useService()->getRelationshipToCompanyById($Id);

        if (!$tblToCompany->getServiceTblPerson()) {
            $Stage->setContent(
                new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
            );
            return $Stage;
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                $tblToCompany->getServiceTblPerson()->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Relationship::useService()->updateRelationshipToCompany(
                                    $this->formRelationshipToCompany($tblToCompany)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToCompany, $tblToCompany->getServiceTblPerson(), $To, $Type
                                )
                            )
                        )
                    )
                )),
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

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToPerson &$tblToPerson) use ($tblPerson) {

                if ($tblToPerson->getServiceTblPersonFrom() && $tblToPerson->getServiceTblPersonTo()) {
                    if ($tblToPerson->getTblType()->isBidirectional()) {
                        $sign = ' ' . new ChevronLeft() . new ChevronRight() . ' ';
                    } else {
                        $sign = ' ' . new ChevronRight() . ' ';
                    }
                    $Panel = array(
                        ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            ? $tblPerson->getLastFirstName() . $sign . $tblToPerson->getServiceTblPersonTo()->getLastFirstName()
                            : $tblToPerson->getServiceTblPersonFrom()->getLastFirstName() . $sign . $tblPerson->getLastFirstName()
                        )
                    );
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            new PersonIcon() . ' ' . new Link() . ' ' . $tblToPerson->getTblType()->getName(),
                            $Panel,
                            ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                            || $tblToPerson->getTblType()->isBidirectional()
                                ? Panel::PANEL_TYPE_SUCCESS
                                : Panel::PANEL_TYPE_DEFAULT
                            ),
                            ($tblToPerson->getServiceTblPersonFrom()->getId() == $tblPerson->getId()
                                ? new Standard(
                                    '', '/People/Person/Relationship/Edit', new Edit(),
                                    array('Id' => $tblToPerson->getId()),
                                    'Bearbeiten'
                                )
                                . new Standard(
                                    '', '/People/Person/Relationship/Destroy', new Remove(),
                                    array('Id' => $tblToPerson->getId()), 'Löschen'
                                )
                                . new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToPerson->getServiceTblPersonTo()->getId()), 'zur Person'
                                )
                                :
                                new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToPerson->getServiceTblPersonFrom()->getId()), 'zur Person'
                                )

                            )
                        )
                        , 3);
                } else {
                    $tblToPerson = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Personenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param TblCompany|TblPerson|Element $tblEntity
     *
     * @return Layout
     */
    public function frontendLayoutCompany(Element $tblEntity)
    {

        if ($tblEntity instanceof TblPerson) {
            $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByPerson($tblEntity);
        } else {
            if ($tblEntity instanceof TblCompany) {
                $tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblEntity);
            } else {
                $tblRelationshipAll = false;
            }
        }

        if ($tblRelationshipAll !== false) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRelationshipAll, function (TblToCompany &$tblToCompany) use ($tblEntity) {

                if ($tblToCompany->getServiceTblPerson() && $tblToCompany->getServiceTblCompany()) {
                    $Panel = array(
                        $tblToCompany->getServiceTblPerson()->getFullName(),
                        $tblToCompany->getServiceTblCompany()->getName()
                    );
                    if ($tblToCompany->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                    }

                    $tblToCompany = new LayoutColumn(
                        new Panel(
                            new Building() . ' ' . new Link() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                            ($tblEntity instanceof TblPerson
                                ? Panel::PANEL_TYPE_INFO
                                : Panel::PANEL_TYPE_DEFAULT
                            ),
                            ($tblEntity instanceof TblPerson
                                ? new Standard(
                                    '', '/Corporation/Company/Relationship/Edit', new Edit(),
                                    array('Id' => $tblToCompany->getId()),
                                    'Bearbeiten'
                                )
                                . new Standard(
                                    '', '/Corporation/Company/Relationship/Destroy', new Remove(),
                                    array('Id' => $tblToCompany->getId()), 'Löschen'
                                )
                                . new Standard(
                                    '', '/Corporation/Company', new Building(),
                                    array('Id' => $tblToCompany->getServiceTblCompany()->getId()), 'zur Firma'
                                )
                                :
                                new Standard(
                                    '', '/People/Person', new PersonIcon(),
                                    array('Id' => $tblToCompany->getServiceTblPerson()->getId()), 'zur Person'
                                )
                            )
                        )
                        , 3);
                } else {
                    $tblToCompany = false;
                }
            });
            $tblRelationshipAll = array_filter($tblRelationshipAll);
        } else {
            $tblRelationshipAll = array(
                new LayoutColumn(
                    new Warning('Keine Firmenbeziehungen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($tblRelationshipAll as $tblRelationship) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblRelationship);
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

        $Stage = new Stage('Beziehung', 'Löschen');
        $Stage->addButton(new Backward(true));
        if ($Id) {
            $tblToPerson = Relationship::useService()->getRelationshipToPersonById($Id);
            $tblPersonFrom = $tblToPerson->getServiceTblPersonFrom();
            if (!$tblToPerson || !$tblPersonFrom) {
                $Stage->setContent(
                    new Danger('Person nicht gefunden', new Ban())
                    . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
                );
                return $Stage;
            }

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            $tblPersonFrom->getFullName(),
                            Panel::PANEL_TYPE_INFO
                        ),
                        new Panel(new Question() . ' Diese Beziehung wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            $tblToPerson->getServiceTblPersonTo() ? $tblToPerson->getServiceTblPersonTo()->getFullName() : '',
                            new Muted(new Small($tblToPerson->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Relationship/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            . new Standard(
                                'Nein', '/People/Person', new Disable(),
                                array('Id' => $tblPersonFrom->getId())
                            )
                        )
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Relationship::useService()->removePersonRelationshipToPerson($tblToPerson)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Beziehung wurde gelöscht')
                                : new Danger(new Ban() . ' Die Beziehung konnte nicht gelöscht werden')
                            ),
                            new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblPersonFrom->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Beziehung konnte nicht gefunden werden'),
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

        $Stage = new Stage('Beziehung', 'Löschen');
        $Stage->addButton(new Backward(true));
        if ($Id) {
            $tblToCompany = Relationship::useService()->getRelationshipToCompanyById($Id);
            if (!$tblToCompany) {
                $Stage->setContent(
                    new Danger('Firma nicht gefunden', new Ban())
                    . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
                );
                return $Stage;
            }
            $tblPerson = $tblToCompany->getServiceTblPerson();
            if (!$tblPerson) {
                $Stage->setContent(
                    new Danger('Person nicht gefunden', new Ban())
                    . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
                );
                return $Stage;
            }

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            $tblPerson->getFullName(),
                            Panel::PANEL_TYPE_INFO
                        ),
                        new Panel(new Question() . ' Diese Beziehung wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            $tblToCompany->getServiceTblCompany() ? $tblToCompany->getServiceTblCompany()->getName() : '',
                            new Muted(new Small($tblToCompany->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Relationship/Destroy', new Ok(),
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
                            (Relationship::useService()->removeCompanyRelationshipToPerson($tblToCompany)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Beziehung wurde gelöscht')
                                : new Danger(new Ban() . ' Die Beziehung konnte nicht gelöscht werden')
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
                        new Danger(new Ban() . ' Die Beziehung konnte nicht gefunden werden'),
                        new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
