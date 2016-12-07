<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Service\Entity\ViewCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Group as PeopleGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\ViewRelationshipToCompany;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Form
     */
    private function formContact()
    {
        $tblSalutationAll = Person::useService()->getSalutationAll();
        $tblRelationshipAll = Relationship::useService()->getTypeAllByGroup(
            Relationship::useService()->getGroupByIdentifier( 'COMPANY' )
        );

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Beziehung', array(
                            (new SelectBox('Person[' . ViewRelationshipToCompany::TBL_TYPE_ID . ']', 'Art des Ansprechpartners', array('Name' => $tblRelationshipAll),
                                new Conversation()))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Anrede', array(
                            (new SelectBox('Person[' . ViewPerson::TBL_SALUTATION_ID . ']', 'Anrede', array('Salutation' => $tblSalutationAll),
                                new Conversation()))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Vorname', array(
                            (new TextField('Person[' . ViewPerson::TBL_PERSON_FIRST_NAME . ']', 'Rufname', 'Vorname'))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(
                        new Panel('Nachname', array(
                            (new TextField('Person[' . ViewPerson::TBL_PERSON_LAST_NAME . ']', 'Nachname', 'Nachname'))->setRequired(),
                        ), Panel::PANEL_TYPE_INFO), 3),
                ))
            ))
            , new Primary('Ansprechpartner prüfen')
        );
    }


    /**
     * @param null|int $Id Company-Id
     * @param null|array $Person Form-Person-Data
     * @param null|int $Group Company-Group-Id
     * @param null|int $tblPerson Link-Person-Id
     * @param null|int $tblType Link-Relationship-Id
     * @param bool $doCreate New-Person-Toggle
     * @return Stage
     */
    public function frontendContact($Id = null, $Person = null, $Group = null, $tblPerson = null, $tblType = null, $doCreate = false)
    {
        $Stage = new Stage('Firmen', 'Ansprechpartner');
        $Stage->addButton(new Standard('Zurück', '/Corporation/Company', new ChevronLeft(), array(
            'Id' => $Id,
            'Group' => $Group
        )));

        $tblCompany = Company::useService()->getCompanyById($Id);
        $tblGroup = PeopleGroup::useService()->getGroupByMetaTable(
            \SPHERE\Application\People\Group\Service\Entity\TblGroup::META_TABLE_COMPANY_CONTACT
        );

        // Link Person to Company
        if( $tblCompany && $tblPerson && $tblType ) {
            $tblPerson = Person::useService()->getPersonById( $tblPerson );
            $tblType = Relationship::useService()->getTypeById( $tblType );
            if( $tblPerson && $tblType && $tblGroup ) {
                Relationship::useService()->addCompanyRelationshipToPerson(
                    $tblCompany, $tblPerson, $tblType
                );
                PeopleGroup::useService()->addGroupPerson( $tblGroup, $tblPerson );
                $Stage->setContent(
                    new Success(new Ok() . ' Der Ansprechpartner wurde hinzugefügt').
                    new Redirect( $this->getRequest()->getPathInfo(), Redirect::TIMEOUT_SUCCESS, array(
                    'Id' => $Id,
                    'Group' => $Group
                ) ) );
                return $Stage;
            } else {
                $Stage->setContent(
                    new Danger(new Ban() . ' Ansprechpartner konnte nicht hinzugefügt werden').
                    new Redirect( $this->getRequest()->getPathInfo(), Redirect::TIMEOUT_ERROR, array(
                    'Id' => $Id,
                    'Group' => $Group
                ) ) );
                return $Stage;
            }
        }

        // Create Person to Company
        if( $doCreate && $tblCompany ) {
            $tblType = Relationship::useService()->getTypeById( $Person[ViewRelationshipToCompany::TBL_TYPE_ID] );
            if( $tblType ) {
                $tblPerson = Person::useService()->insertPerson(
                    $Person[ViewPerson::TBL_SALUTATION_ID], '',
                    $Person[ViewPerson::TBL_PERSON_FIRST_NAME], '',
                    $Person[ViewPerson::TBL_PERSON_LAST_NAME],
                    array(
                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                        1 => $tblGroup
                    )
                );
                if ($tblPerson) {
                    Relationship::useService()->addCompanyRelationshipToPerson(
                        $tblCompany, $tblPerson, $tblType
                    );
                    $Stage->setContent(
                        new Success(new Ok() . ' Der Ansprechpartner wurde hinzugefügt') .
                        new Redirect($this->getRequest()->getPathInfo(), Redirect::TIMEOUT_SUCCESS, array(
                            'Id' => $Id,
                            'Group' => $Group
                        )));
                    return $Stage;
                }
            }
        }

        $Search = new Pile(Pile::JOIN_TYPE_INNER);
        $Search->addPile(Company::useService(), new ViewCompany(), null, ViewCompany::TBL_COMPANY_ID);
        $Search->addPile(Relationship::useService(), new ViewRelationshipToCompany(), ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY, ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_PERSON);
        $Search->addPile(Person::useService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID);

        $Result = $Search->searchPile(array(
            0 => array(
                ViewCompany::TBL_COMPANY_NAME => array($tblCompany->getName()),
                ViewCompany::TBL_COMPANY_EXTENDED_NAME => array($tblCompany->getExtendedName()),
                ViewCompany::TBL_COMPANY_DESCRIPTION => array($tblCompany->getDescription()),
            ),
            1 => array(
                ViewRelationshipToCompany::TBL_TO_COMPANY_SERVICE_TBL_COMPANY => array($Id)
            )
        ));

        $Table = array();
        /** @var AbstractView[] $Row */
        foreach ($Result as $Row) {
            $ViewArray1 = $Row[1]->__toArray();
            $ViewArray2 = $Row[2]->__toArray();
            $ViewArray1['DTOption'] = new Standard( new PersonIcon(),'/People/Person',null, array(
                'Id' => $ViewArray2['TblPerson_Id']
            ));
            $Table[] = array_merge( $ViewArray1, $ViewArray2 );
        }

        $Form = $this->formContact();

        $Error = true;
        if( $Person ) {
            $Error = false;

            if (isset($Person[ViewRelationshipToCompany::TBL_TYPE_ID]) && empty($Person[ViewRelationshipToCompany::TBL_TYPE_ID])) {
                $Form->setError('Person['.ViewRelationshipToCompany::TBL_TYPE_ID.']', 'Bitte wählen Sie eine Beziehung aus');
                $Error = true;
            }
            if (isset($Person[ViewPerson::TBL_SALUTATION_ID]) && empty($Person[ViewPerson::TBL_SALUTATION_ID])) {
                $Form->setError('Person['.ViewPerson::TBL_SALUTATION_ID.']', 'Bitte wählen Sie eine Anrede aus');
                $Error = true;
            }
            if (isset($Person[ViewPerson::TBL_PERSON_FIRST_NAME]) && empty($Person[ViewPerson::TBL_PERSON_FIRST_NAME])) {
                $Form->setError('Person['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Bitte geben Sie einen Vornamen an');
                $Error = true;
            }
            if (isset($Person[ViewPerson::TBL_PERSON_LAST_NAME]) && empty($Person[ViewPerson::TBL_PERSON_LAST_NAME])) {
                $Form->setError('Person['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Bitte geben Sie einen Nachamen an');
                $Error = true;
            }
        }

        $Layout = new Layout(array(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel( 'Firma', array(
                            $tblCompany->getName(),
                            $tblCompany->getExtendedName(),
                            $tblCompany->getDescription(),
                        ), Panel::PANEL_TYPE_SUCCESS, array(

                            empty($Table)
                                ? new Info( 'Keine Ansprechpartner zugewiesen' )
                                :new TableData($Table, null, array(
                                ViewRelationshipToCompany::TBL_TYPE_NAME => 'Beziehung',
                                ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                                ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                                ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                                'DTOption' => ''
                            ),false)
                        ) )
                    )
                )
                , new Title( new \SPHERE\Common\Frontend\Icon\Repository\Success().' Zugewiesene Ansprechpartner')),
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well( $Form )
                    )
                )
                , new Title( new Search().' Mögliche Ansprechpartner suchen'))
        ));

        if( $Person && !$Error ) {
            $Search = new Pile();
            $Search->addPile( Person::useService(), new ViewPerson() );

            $Filter = array();
            $Filter[ViewPerson::TBL_SALUTATION_ID] = array($Person[ViewPerson::TBL_SALUTATION_ID]);
            $Filter[ViewPerson::TBL_PERSON_FIRST_NAME] = explode( ' ', $Person[ViewPerson::TBL_PERSON_FIRST_NAME] );
            $Filter[ViewPerson::TBL_PERSON_LAST_NAME] = explode( ' ', $Person[ViewPerson::TBL_PERSON_LAST_NAME] );

            $Result = $Search->searchPile(array(
                $Filter
            ));

            $Result = array_slice( $Result, 0, 10 );

            $PossibleTable = array();
            /** @var AbstractView[] $Row */
            foreach( $Result as $Row ) {
                $ViewArray = $Row[0]->__toArray();
                $Address = Person::useService()->getPersonById( $ViewArray[ViewPerson::TBL_PERSON_ID] )->fetchMainAddress();
                $ViewArray['DTAddress'] = ( $Address ? $Address->getGuiTwoRowString() : '');
                $ViewArray['DTOption'] = new \SPHERE\Common\Frontend\Link\Repository\Primary(
                    'Ansprechpartner hinzufügen',$this->getRequest()->getPathInfo(), new PlusSign(), array(
                        'Id' => $Id,
                        'Group' => $Group,
                        'tblPerson' => $ViewArray[ViewPerson::TBL_PERSON_ID],
                        'tblType' => $Person[ViewRelationshipToCompany::TBL_TYPE_ID]
                ));
                $PossibleTable[] = $ViewArray;
            }

            $Layout->addGroup(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (
                            empty($PossibleTable)
                                ? new Success( 'Keine ähnlichen Personen gefunden' )
                                :new TableData($PossibleTable, null, array(
                                ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                                ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                                ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                                'DTAddress' => 'Adresse',
                                'DTOption' => ''
                            ))
                            )
                        )
                    ),
                    new Title( new Question().' Mögliche verfügbare Personen', 'um doppelte Personen zu vermeiden' ))
            );

            $Layout->addGroup(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Neue Person anlegen',array(
                                'Anrede: '.Person::useService()
                                    ->getSalutationById($Person[ViewPerson::TBL_SALUTATION_ID])
                                    ->getSalutation(),
                                'Vorname: '.$Person[ViewPerson::TBL_PERSON_FIRST_NAME],
                                'Nachname: '.$Person[ViewPerson::TBL_PERSON_LAST_NAME],
                            ))
                        ),4),
                        new LayoutColumn(array(
                            new Panel('Beziehung erstellen',array(
                                'Gruppe: '.$tblGroup->getName(),
                                'Beziehung: '.Relationship::useService()
                                    ->getTypeById( $Person[ViewRelationshipToCompany::TBL_TYPE_ID] )
                                    ->getName()
                            ))
                        ),4),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Link\Repository\Primary('Ansprechpartner anlegen',$this->getRequest()->getPathInfo(), new Save(), array(
                                'Id' => $Id,
                                'Group' => $Group,
                                'Person' => $Person,
                                'doCreate' => 1
                            ))
                        ))
                    ))
                ), new Title( new PlusSign().' Neue Person anlegen',
                    'Es wird eine neue Person angelegt und mit der Firma verknüpft'
                ))
            );
        }


        $Stage->setContent(
            $Layout
        );

        return $Stage;
    }

    /**
     * @param bool|false $TabActive
     *
     * @param null|int $Id
     * @param null|array $Company
     * @param null|array $Meta
     * @param null|int $Group
     *
     * @return Stage
     */
    public function frontendCompany($TabActive = false, $Id = null, $Company = null, $Meta = null, $Group = null)
    {

        $Stage = new Stage('Firmen', 'Datenblatt ' . ($Id ? 'bearbeiten' : 'anlegen'));
        if ($Group) {
            $Stage->addButton(new Standard('Zurück', '/Corporation/Search/Group', new ChevronLeft(),
                array('Id' => $Group)));
        }

        if (!$Id) {

            $BasicTable = Company::useService()->createCompany(
                $this->formCompany()
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                $Company);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(new LayoutColumn(new Well($BasicTable))),
                        new Title(new Building() . ' Grunddaten', 'der Firma')
                    ),
                ))
            );

        } else {
            $tblCompany = Company::useService()->getCompanyById($Id);

            if ($tblCompany) {
                $Stage->addButton(new Standard('Ansprechpartner hinzufügen', '/Corporation/Company/Contact/Create', new PlusSign(), array(
                    'Id' => $tblCompany->getId(),
                    'Group' => $Group
                )));

                $Global = $this->getGlobal();
                if (!isset($Global->POST['Company'])) {
                    $Global->POST['Company']['Name'] = $tblCompany->getName();
                    $Global->POST['Company']['ExtendedName'] = $tblCompany->getExtendedName();
                    $Global->POST['Company']['Description'] = $tblCompany->getDescription();
                    $tblGroupAll = Group::useService()->getGroupAllByCompany($tblCompany);
                    if (!empty($tblGroupAll)) {
                        /** @var TblGroup $tblGroup */
                        foreach ((array)$tblGroupAll as $tblGroup) {
                            $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        }
                    }
                    $Global->savePost();
                }

                $BasicTable = Company::useService()->updateCompany(
                    $this->formCompany()
                        ->appendFormButton(new Primary('Speichern', new Save()))
                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                    $tblCompany, $Company, $Group);

                $MetaTabs = Group::useService()->getGroupAllByCompany($tblCompany);
                // Sort by Name
                usort($MetaTabs, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                    return strnatcmp($ObjectA->getName(), $ObjectB->getName());
                });
                // Create Tabs
                /** @noinspection PhpUnusedParameterInspection */
                array_walk($MetaTabs, function (TblGroup &$tblGroup) use ($tblCompany) {

                    switch (strtoupper($tblGroup->getMetaTable())) {
//                    case 'COMMON':
//                        $tblGroup = new LayoutTab( 'Allgemein', $tblGroup->getMetaTable(),
//                            array( 'tblCompany' => $tblCompany->getId() )
//                        );
//                        break;
                        default:
                            $tblGroup = false;
                    }
                });
                /** @var LayoutTab[] $MetaTabs */
                $MetaTabs = array_filter($MetaTabs);
                // Folded ?
                if (!empty($MetaTabs)) {
                    if (!$TabActive || $TabActive == '#') {
                        array_unshift($MetaTabs, new LayoutTab('&nbsp;' . new ChevronRight() . '&nbsp;', '#',
                            array('Id' => $tblCompany->getId())
                        ));
                        $MetaTabs[0]->setActive();
                    } else {
                        array_unshift($MetaTabs, new LayoutTab('&nbsp;' . new ChevronUp() . '&nbsp;', '#',
                            array('Id' => $tblCompany->getId())
                        ));
                    }
                }

//            switch (strtoupper($TabActive)) {
//                case 'COMMON':
//                    $MetaTable = Common::useFrontend()->frontendMeta( $tblCompany, $Meta );
//                    break;
//                default:
//                    if (!empty( $MetaTabs )) {
//                        $MetaTable = new Well(new Muted('Bitte wählen Sie eine Rubrik'));
//                    } else {
//                        $MetaTable = new Well(new Warning('Keine Informationen verfügbar'));
//                    }
//            }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(
                            new LayoutRow(new LayoutColumn(array(
                                new Well(
                                    $BasicTable
                                )
                            ))),
                            new Title(new Building() . ' Grunddaten', 'der Firma')
                        ),
//                    new LayoutGroup(array(
//                        new LayoutRow(new LayoutColumn(new LayoutTabs($MetaTabs))),
//                        new LayoutRow(new LayoutColumn($MetaTable)),
//                    ), new Title(new Tag().' Informationen', 'zur Firma')),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                Address::useFrontend()->frontendLayoutCompany($tblCompany)
                            )),
                        ), (new Title(new TagList() . ' Adressdaten', 'der Firma'))
                            ->addButton(
                                new Standard('Adresse hinzufügen', '/Corporation/Company/Address/Create',
                                    new ChevronDown(), array('Id' => $tblCompany->getId())
                                )
                            )
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                Phone::useFrontend()->frontendLayoutCompany($tblCompany)
                                . Mail::useFrontend()->frontendLayoutCompany($tblCompany)
                            )),
                        ), (new Title(new TagList() . ' Kontaktdaten', 'der Firma'))
                            ->addButton(
                                new Standard('Telefonnummer hinzufügen', '/Corporation/Company/Phone/Create',
                                    new ChevronDown(), array('Id' => $tblCompany->getId())
                                )
                            )
                            ->addButton(
                                new Standard('E-Mail Adresse hinzufügen', '/Corporation/Company/Mail/Create',
                                    new ChevronDown(), array('Id' => $tblCompany->getId())
                                )
                            )
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                Relationship::useFrontend()->frontendLayoutCompany($tblCompany)
                            ))),
                        ), (new Title(new TagList() . ' Beziehungen', 'zu Personen'))
                        ),
                    ))
                );

            } else {
                return $Stage . new Danger(new Ban() . ' Firma nicht gefunden.')
                    . new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group));
            }
        }

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formCompany()
    {

        $tblGroupList = Group::useService()->getGroupAll();
        if ($tblGroupList) {
            // Sort by Name
            usort($tblGroupList, function (TblGroup $ObjectA, TblGroup $ObjectB) {

                return strnatcmp($ObjectA->getName(), $ObjectB->getName());
            });
            // Create CheckBoxes
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Company']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Company[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = new CheckBox(
                            'Company[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Firmenname', array(
                            (new TextField('Company[Name]', 'Name', 'Name'))->setRequired(),
                            new TextField('Company[ExtendedName]', 'Zusatz', 'Zusatz'),
                            new TextField('Company[Description]', 'Beschreibung', 'Beschreibung'),
                        ), Panel::PANEL_TYPE_INFO), 8),
                    new FormColumn(
                        new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO), 4),
                ))
            ))
        );
    }

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @param null $Group
     * @return Stage
     */
    public function frontendDestroyCompany($Id = null, $Confirm = false, $Group = null)
    {

        $Stage = new Stage('Firma', 'Löschen');
        if ($Id) {
            if ($Group) {
                $Stage->addButton(new Standard('Zurück', '/People/Search/Group', new ChevronLeft(), array('Id' => $Group)));
            }
            $tblCompany = Company::useService()->getCompanyById($Id);
            if (!$tblCompany) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Die Firma konnte nicht gefunden werden.', new Ban()),
                            new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Firma', new Bold($tblCompany->getName() . ($tblCompany->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblCompany->getDescription()))) : '')),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question() . ' Diese Firma wirklich löschen?', array(
                                $tblCompany->getName(),
                                $tblCompany->getExtendedName(),
                                $tblCompany->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Corporation/Company/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true, 'Group' => $Group)
                                )
                                . new Standard(
                                    'Nein', '/Corporation/Search/Group', new Disable(), array('Id' => $Group)
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Company::useService()->destroyCompany($tblCompany)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Firma wurde gelöscht.')
                                    : new Danger(new Ban() . ' Die Firma konnte nicht gelöscht werden.')
                                ),
                                new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_SUCCESS, array('Id' => $Group))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Daten nicht abrufbar.', new Ban()),
                        new Redirect('/Corporation/Search/Group', Redirect::TIMEOUT_ERROR, array('Id' => $Group))
                    )))
                )))
            );
        }
        return $Stage;
    }
}
