<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Address as AddressLayout;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Address
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Id
     * @param array $Street
     * @param array $City
     * @param int $State
     * @param array $Type
     * @param null $County
     * @param null $Nation
     *
     * @return Stage
     */
    public function frontendCreateToPerson($Id, $Street, $City, $State, $Type, $County = null, $Nation = null)
    {

        $tblPerson = Person::useService()->getPersonById($Id);

        $Stage = new Stage('Adresse', 'Hinzufügen');
        $Stage->addButton( new Backward(true) );
        $Stage->setMessage('Eine Adresse zur gewählten Person hinzufügen');

        if(!$tblPerson){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

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
                                Address::useService()->createAddressToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblPerson, $Street, $City, $State, $Type, $County, $Nation
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

        $tblAddress = Address::useService()->getAddressAll();
        $tblCity = Address::useService()->getCityAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));
        $tblType = Address::useService()->getTypeAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anschrift', array(
                            new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblType),
                                new TileBig()),
                            new AutoCompleter('Street[Name]', 'Straße', 'Straße',
                                array('StreetName' => $tblAddress), new MapMarker()
                            ),
                            new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Stadt', array(
                            new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array('Code' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('City[Name]', 'Ort', 'Ort',
                                array('Name' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil',
                                array('District' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('County', 'Landkreis', 'Landkreis',
                                array('County' => $tblAddress), new Map()
                            ),
                            new SelectBox('State', 'Bundesland',
                                array('Name' => $tblState), new Map()
                            ),
                            new AutoCompleter('Nation', 'Land', 'Land',
                                array('Nation' => $tblAddress), new Map()
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param array $Street
     * @param array $City
     * @param int $State
     * @param array $Type
     * @param null $County
     * @param null $Nation
     *
     * @return Stage
     */
    public function frontendCreateToCompany($Id, $Street, $City, $State, $Type, $County = null, $Nation = null)
    {

        $tblCompany = Company::useService()->getCompanyById($Id);

        $Stage = new Stage('Adresse', 'Hinzufügen');
        $Stage->addButton( new Backward(true) );
        $Stage->setMessage('Eine Adresse zur gewählten Firma hinzufügen');

        if ($tblCompany) {

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
                                    Address::useService()->createAddressToCompany(
                                        $this->formAddress()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblCompany, $Street, $City, $State, $Type, $County, $Nation
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
     * @param array $Street
     * @param array $City
     * @param int $State
     * @param array $Type
     * @param null $County
     * @param null $Nation
     *
     * @return Stage
     */
    public function frontendUpdateToPerson($Id, $Street, $City, $State, $Type, $County = null, $Nation = null)
    {

        $tblToPerson = Address::useService()->getAddressToPersonById($Id);

        $Stage = new Stage('Adresse', 'Bearbeiten');
        $Stage->addButton( new Backward(true) );
        $Stage->setMessage('Die Adresse der gewählten Person ändern');

        if (!$tblToPerson) {
            // Back to Person with Backward
            return $Stage.new Danger('Adresse nicht gefunden', new Ban());
//            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        if(!$tblToPerson->getServiceTblPerson()){
            return $Stage . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Address'])) {
            $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
            $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
            $Global->POST['Street']['Name'] = $tblToPerson->getTblAddress()->getStreetName();
            $Global->POST['Street']['Number'] = $tblToPerson->getTblAddress()->getStreetNumber();
            $Global->POST['City']['Code'] = $tblToPerson->getTblAddress()->getTblCity()->getCode();
            $Global->POST['City']['Name'] = $tblToPerson->getTblAddress()->getTblCity()->getName();
            $Global->POST['City']['District'] = $tblToPerson->getTblAddress()->getTblCity()->getDistrict();
            if ($tblToPerson->getTblAddress()->getTblState()) {
                $Global->POST['State'] = $tblToPerson->getTblAddress()->getTblState()->getId();
            }
            $Global->POST['County'] = $tblToPerson->getTblAddress()->getCounty();
            $Global->POST['Nation'] = $tblToPerson->getTblAddress()->getNation();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(new PersonIcon() . ' Person',
                                $tblToPerson->getServiceTblPerson()
                                    ? new Bold($tblToPerson->getServiceTblPerson()->getFullName())
                                    : 'Person nicht gefunden.'   ,
                                Panel::PANEL_TYPE_SUCCESS
                            )
                        )
                    ),
                )),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Address::useService()->updateAddressToPerson(
                                    $this->formAddress()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblToPerson, $Street, $City, $State, $Type, $County, $Nation
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
     * @param array $Street
     * @param array $City
     * @param int $State
     * @param array $Type
     * @param null $County
     * @param null $Nation
     *
     * @return Stage
     */
    public function frontendUpdateToCompany($Id, $Street, $City, $State, $Type, $County = null, $Nation = null)
    {

        $tblToCompany = Address::useService()->getAddressToCompanyById($Id);

        $Stage = new Stage('Adresse', 'Bearbeiten');
        $Stage->addButton( new Backward(true) );
        $Stage->setMessage('Die Adresse der gewählten Firma ändern');
        if ($tblToCompany->getServiceTblCompany()) {


            $Global = $this->getGlobal();
            if (!isset($Global->POST['Address'])) {
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->POST['Street']['Name'] = $tblToCompany->getTblAddress()->getStreetName();
                $Global->POST['Street']['Number'] = $tblToCompany->getTblAddress()->getStreetNumber();
                $Global->POST['City']['Code'] = $tblToCompany->getTblAddress()->getTblCity()->getCode();
                $Global->POST['City']['Name'] = $tblToCompany->getTblAddress()->getTblCity()->getName();
                $Global->POST['City']['District'] = $tblToCompany->getTblAddress()->getTblCity()->getDistrict();
                if ($tblToCompany->getTblAddress()->getTblState()) {
                    $Global->POST['State'] = $tblToCompany->getTblAddress()->getTblState()->getId();
                }
                $Global->POST['County'] = $tblToCompany->getTblAddress()->getCounty();
                $Global->POST['Nation'] = $tblToCompany->getTblAddress()->getNation();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel(new PersonIcon() . ' Firma',
                                    $tblToCompany->getServiceTblCompany()
                                        ? array(
                                        new Bold($tblToCompany->getServiceTblCompany()->getName()),
                                        $tblToCompany->getServiceTblCompany()->getExtendedName())
                                        : 'Firma nicht gefunden.',
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            )
                        ),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    Address::useService()->updateAddressToCompany(
                                        $this->formAddress()
                                            ->appendFormButton(new Primary('Speichern', new Save()))
                                            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                        , $tblToCompany, $Street, $City, $State, $Type, $County, $Nation
                                    )
                                )
                            )
                        )
                    ), new Title(new Edit() . ' Bearbeiten')),
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
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToPerson($Id, $Confirm = false)
    {

        $Stage = new Stage('Adresse', 'Löschen');
        $Stage->addButton( new Backward(true) );
        if ($Id) {
            $tblToPerson = Address::useService()->getAddressToPersonById($Id);
            $tblPerson = $tblToPerson->getServiceTblPerson();
            if (!$tblPerson) {
                return $Stage . new Danger('Person nicht gefunden', new Ban())
                . new Redirect('/People/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon() . ' Person',
                            new Bold($tblPerson->getFullName()),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question() . ' Diese Adresse wirklich löschen?', array(
                            $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription(),
                            new AddressLayout($tblToPerson->getTblAddress()),
                            ($tblToPerson->getRemark() ? new Muted(new Small($tblToPerson->getRemark())) : '')
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/People/Person/Address/Destroy', new Ok(),
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
                            (Address::useService()->removeAddressToPerson($tblToPerson)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Adresse wurde gelöscht')
                                : new Danger(new Ban() . ' Die Adresse konnte nicht gelöscht werden')
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
                        new Danger(new Ban() . ' Die Adresse konnte nicht gefunden werden'),
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

        $Stage = new Stage('Adresse', 'Löschen');
        $Stage->addButton( new Backward(true) );
        if ($Id) {
            $tblToCompany = Address::useService()->getAddressToCompanyById($Id);

            $tblCompany = $tblToCompany->getServiceTblCompany();
            if(!$tblCompany){
                return $Stage . new Danger('Firma nicht gefunden', new Ban())
                . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR);
            }

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new Building() . ' Company',
                            array(
                                new Bold($tblCompany->getName()),
                                $tblCompany->getExtendedName()),
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard('Zurück zur Firma', '/Corporation/Company', new ChevronLeft(),
                                array('Id' => $tblCompany->getId())
                            )
                        ),
                        new Panel(new Question() . ' Diese Adresse wirklich löschen?', array(
                            $tblToCompany->getTblType()->getName() . ' ' . $tblToCompany->getTblType()->getDescription(),
                            new AddressLayout($tblToCompany->getTblAddress()),
                            ($tblToCompany->getRemark() ? new Muted(new Small($tblToCompany->getRemark())) : '')
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Company/Address/Destroy', new Ok(),
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
                            (Address::useService()->removeAddressToCompany($tblToCompany)
                                ? new Success('Die Adresse wurde gelöscht')
                                : new Danger('Die Adresse konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Corporation/Company', 1, array('Id' => $tblCompany->getId()))
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Adresse konnte nicht gefunden werden'),
                        new Redirect('/Corporation/Search/Group')
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function frontendLayoutPerson(TblPerson $tblPerson)
    {

        $addressExistsList = array();
        $tblAddressAll = Address::useService()->getAddressAllByPerson($tblPerson);
        if ($tblAddressAll !== false) {
            array_walk($tblAddressAll, function (TblToPerson &$tblToPerson) use ($addressExistsList) {

                if (array_key_exists($tblToPerson->getId(), $addressExistsList)){
                    $tblToPerson = false;
                } else {
                    $addressExistsList[$tblToPerson->getId()] = $tblToPerson;

                    $Panel = array($tblToPerson->getTblAddress()->getGuiLayout());
                    if ($tblToPerson->getRemark()) {
                        array_push($Panel, new Muted(new Small($tblToPerson->getRemark())));
                    }

                    $tblToPerson = new LayoutColumn(
                        new Panel(
                            new MapMarker() . ' ' . $tblToPerson->getTblType()->getName(), $Panel,
                            Panel::PANEL_TYPE_SUCCESS,
                            new Standard(
                                '', '/People/Person/Address/Edit', new Edit(),
                                array('Id' => $tblToPerson->getId()),
                                'Bearbeiten'
                            )
                            . new Standard(
                                '', '/People/Person/Address/Destroy', new Remove(),
                                array('Id' => $tblToPerson->getId()), 'Löschen'
                            )
                        )
                        , 3);
                }
            });

            $tblAddressAll = array_filter($tblAddressAll);
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblRelationshipAddressAll = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                        if ($tblRelationshipAddressAll) {
                            foreach ($tblRelationshipAddressAll as $tblAddress) {
                                if (!array_key_exists($tblAddress->getId(), $addressExistsList)) {
                                    $addressExistsList[$tblAddress->getId()] = $tblAddress;

                                    $Panel = array($tblAddress->getTblAddress()->getGuiLayout());
                                    if ($tblAddress->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblAddress->getRemark())));
                                    }

                                    $tblAddress = new LayoutColumn(
                                        new Panel(
                                            new MapMarker() . ' ' . $tblAddress->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonFrom()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonFrom()->getFullName()
                                        )
                                        , 3);

                                    if ($tblAddressAll !== false) {
                                        $tblAddressAll[] = $tblAddress;
                                    } else {
                                        $tblAddressAll = array();
                                        $tblAddressAll[] = $tblAddress;
                                    }
                                }
                            }
                        }
                    }

                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonTo()->getId()) {
                        $tblRelationshipAddressAll = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                        if ($tblRelationshipAddressAll) {
                            foreach ($tblRelationshipAddressAll as $tblAddress) {
                                if (!array_key_exists($tblAddress->getId(), $addressExistsList)) {
                                    $addressExistsList[$tblAddress->getId()] = $tblAddress;

                                    $Panel = array($tblAddress->getTblAddress()->getGuiLayout());
                                    if ($tblAddress->getRemark()) {
                                        array_push($Panel, new Muted(new Small($tblAddress->getRemark())));
                                    }

                                    $tblAddress = new LayoutColumn(
                                        new Panel(
                                            new MapMarker() . ' ' . $tblAddress->getTblType()->getName(), $Panel,
                                            Panel::PANEL_TYPE_DEFAULT,
                                            new Standard(
                                                '', '/People/Person', new PersonIcon(),
                                                array('Id' => $tblRelationship->getServiceTblPersonTo()->getId()),
                                                'Zur Person'
                                            )
                                            . '&nbsp;' . $tblRelationship->getServiceTblPersonTo()->getFullName()
                                        )
                                        , 3);

                                    if ($tblAddressAll !== false) {
                                        $tblAddressAll[] = $tblAddress;
                                    } else {
                                        $tblAddressAll = array();
                                        $tblAddressAll[] = $tblAddress;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($tblAddressAll === false) {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt')
                )
            );
        }


        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblAddress
         */
        foreach ($tblAddressAll as $tblAddress) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblAddress);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return Layout
     */
    public function frontendLayoutCompany(TblCompany $tblCompany)
    {

        $tblAddressAll = Address::useService()->getAddressAllByCompany($tblCompany);
        if ($tblAddressAll !== false) {
            array_walk($tblAddressAll, function (TblToCompany &$tblToCompany) {

                $Panel = array($tblToCompany->getTblAddress()->getGuiLayout());
                if ($tblToCompany->getRemark()) {
                    array_push($Panel, new Muted(new Small($tblToCompany->getRemark())));
                }

                $tblToCompany = new LayoutColumn(
                    new Panel(
                        new MapMarker() . ' ' . $tblToCompany->getTblType()->getName(), $Panel,
                        Panel::PANEL_TYPE_SUCCESS,
                        new Standard(
                            '', '/Corporation/Company/Address/Edit', new Edit(),
                            array('Id' => $tblToCompany->getId()),
                            'Bearbeiten'
                        )
                        . new Standard(
                            '', '/Corporation/Company/Address/Destroy', new Remove(),
                            array('Id' => $tblToCompany->getId()), 'Löschen'
                        )
                    )
                    , 3);
            });
        } else {
            $tblAddressAll = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblAddress
         */
        foreach ($tblAddressAll as $tblAddress) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblAddress);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }
}
