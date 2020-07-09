<?php

namespace SPHERE\Application\Reporting\CheckList;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup as CompanyGroupEntity;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\People\Group\Service\Entity\TblGroup as PersonGroupEntity;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListObjectList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblObjectType;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\CheckList
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $List
     *
     * @return Stage
     */
    public function frontendList($List = null)
    {

        $Stage = new Stage('Check-Listen', 'Übersicht');

        $tblListAll = CheckList::useService()->getListAll();
        $TableContent = array();

        if ($tblListAll) {
            array_walk($tblListAll, function (TblList $tblList) use (&$TableContent) {
                $Item['Name'] = $tblList->getName();
                $Item['Description'] = $tblList->getDescription();
                $Item['Option'] =
                    (new Standard('',
                        '/Reporting/CheckList/Edit', new Edit(),
                        array('Id' => $tblList->getId()), 'Liste bearbeiten'))
                    . (new Standard('',
                        '/Reporting/CheckList/Destroy', new Remove(),
                        array('Id' => $tblList->getId()), 'Liste löschen'))
                    . (new Standard('(' . CheckList::useService()->countListElementListByList($tblList) . ')',
                        '/Reporting/CheckList/Element/Select', new Equalizer(),
                        array('Id' => $tblList->getId()), 'Elemente (CheckBox, Datum ...) auswählen'))
                    . (new Standard('(' . CheckList::useService()->countListObjectListByList($tblList) . ')',
                        '/Reporting/CheckList/Object/Select', new Listing(),
                        array('ListId' => $tblList->getId()), 'Person / Institution / Gruppe / Klasse auswählen') )
                    .( new Standard(new View(), '/Reporting/CheckList/Object/Element/Show', new CommodityItem(),
                        array('Id' => $tblList->getId()), 'Check-Listen-Inhalt anzeigen') );

                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formList()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Option'      => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(CheckList::useService()->createList($Form, $List))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formList()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('List[Name]', 'Name', 'Name'), 12
                ),
                new FormColumn(
                    new TextField('List[Description]', 'Beschreibung', 'Beschreibung'), 12
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $List
     *
     * @return Stage|string
     */
    public function frontendListEdit($Id = null, $List = null)
    {

        $Stage = new Stage('Check-List', 'Bearbeiten');
        $Stage->addButton(
            new Standard('Zur&uuml;ck', '/Reporting/CheckList', new ChevronLeft())
        );

        if ($Id == null) {
            return $Stage . new Danger(new Ban() . ' Daten nicht abrufbar.')
                .new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        $tblList = CheckList::useService()->getListById($Id);
        if ($tblList) {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['List']['Name'] = $tblList->getName();
                $Global->POST['List']['Description'] = $tblList->getDescription();

                $Global->savePost();
            }

            $Form = $this->formList()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Check-List',
                                    $tblList->getName() .
                                    ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                        . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(CheckList::useService()->updateList($Form, $Id, $List))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger(new Ban() . ' Liste nicht gefunden.')
                .new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @return Stage
     */
    public function frontendDestroyList($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Check-List', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft())
            );
            $tblList = CheckList::useService()->getListById($Id);
            if (!$tblList) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban() . ' Die Liste konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Check-Liste', new Bold($tblList->getName()) .
                                ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                Panel::PANEL_TYPE_SUCCESS),
                            new Panel(new Question() . ' Diese Liste wirklich löschen?', array(
                                $tblList->getName() . ' ' . $tblList->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/CheckList/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Reporting/CheckList', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (CheckList::useService()->destroyList($tblList)
                                    ? new Success(new SuccessIcon().' Die Liste wurde gelöscht')
                                    : new Danger(new Ban() . ' Die Liste konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Element
     *
     * @return Stage
     */
    public function frontendListElementSelect($Id = null, $Element = null)
    {

        $Stage = new Stage('Check-Listen', 'Elemente einer Check-Liste zuordnen');

        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));

        if (empty($Id)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblList = CheckList::useService()->getListById($Id);
            if (empty($tblList)) {
                $Stage->setContent(new Warning('Die Check-Liste konnte nicht abgerufen werden'));
            } else {

                $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);

                $count = 0;
                $contentTable = array();
                if ($tblListElementListByList) {
                    foreach ($tblListElementListByList as &$tblListElementList) {
                        $count++;
                        $Item['Number'] = ( $tblListElementList->getSortOrder() != ''
                            ? $tblListElementList->getSortOrder()
                            : $count );
                        $Item['Named'] = new PullClear(
                            new PullLeft(new ResizeVertical().' '.$tblListElementList->getName()));
                        $Item['Type'] = $tblListElementList->getTblElementType()->getName();
                        $Item['Option'] = new Standard('', '/Reporting/CheckList/Element/Edit', new Edit(),
                                array(
                                    'Id'        => $tblList->getId(),
                                    'ElementId' => $tblListElementList->getId()
                                )).
                            new Standard('', '/Reporting/CheckList/Element/Remove', new Remove(),
                                array(
                                    'Id' => $tblListElementList->getId()
                                ));

                        array_push($contentTable, $Item);
                    }
                }

                $Form = $this->formElement()
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', new Bold($tblList->getName()) .
                                        ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                            . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                        Panel::PANEL_TYPE_SUCCESS),
                                    4
                                ),
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($contentTable, null,
                                        array(
                                            'Number' => '#',
                                            'Named'  => 'Name',
                                            'Type'   => 'Typ',
                                            'Option' => ''
                                        )
                                        , array(
                                            'ExtensionRowReorder' => array(
                                                'Enabled' => true,
                                                'Url'     => '/Api/Reporting/CheckList/Reorder',
                                                'Data'    => array(
                                                    'Id' => $tblList->getId()
                                                )
                                            ),
                                            'columnDefs'          => array(
                                                array('width' => '1%', 'targets' => 0)
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Well(CheckList::useService()->addElementToList($Form, $Id, $Element))
                                ))
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formElement()
    {

        $tblElementTypeAll = CheckList::useService()->getElementTypeAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('Element[Name]', 'Name', 'Name'), 6
                ),
                new FormColumn(
                    new SelectBox('Element[Type]', 'Typ', array('{{ Name }}' => $tblElementTypeAll)), 6
                ),
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $ElementId
     * @param null $ElementName
     *
     * @return Stage|string
     */
    public function frontendListElementEdit($Id = null, $ElementId = null, $ElementName = null)
    {

        $Stage = new Stage('Name des Elements', 'bearbeiten');
        $tblList = ( $Id !== null ? CheckList::useService()->getListById($Id) : false );
        if (!$tblList) {
            $Stage->setContent(new Warning('Check-Liste nicht gefunden!'));
            return $Stage.new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList/Element/Select', new ChevronLeft(), array('Id' => $tblList->getId())));
        $tblListElementList = ( $ElementId !== null ? CheckList::useService()->getListElementListById($ElementId) : false );
        if (!$tblListElementList) {
            $Stage->setContent(new Warning('Check-Listen Element nicht gefunden!'));
            return $Stage.new Redirect('/Reporting/CheckList/Element/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblList->getId()));
        }
        if ($ElementName === null) {
            $Global = $this->getGlobal();
            $Global->POST['ElementName'] = $tblListElementList->getName();
            $Global->savePost();
        }

        $Form = new Form(new FormGroup(new FormRow(new FormColumn(
            new TextField('ElementName', 'Name', 'Name')
        ))));
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Check-Liste', new Bold($tblList->getName()).
                                ( $tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblList->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Element', $tblListElementList->getName(), Panel::PANEL_TYPE_SUCCESS)
                            , 4)
                    ))
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            CheckList::useService()->updateListElementList($Form, $tblList, $tblListElementList, $ElementName)
                        ))
                    )
                    , new Title(new Edit().' Bearbeiten'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendListElementRemove($Id = null)
    {

        return CheckList::useService()->removeElementFromList($Id);
    }

    /**
     * @param null $ListId
     * @param null $ObjectTypeId
     * @param null $ObjectTypeSelect
     *
     * @return Stage
     */
    public function frontendListObjectSelect(
        $ListId = null,
        $ObjectTypeId = null,
        $ObjectTypeSelect = null
    ) {

        $Stage = new Stage('Check-Listen', 'Eine Person / Institution / Gruppe / Klasse einer Check-Liste zuordnen');
        $Stage->setMessage('Der aktuell ausgewählten Checkliste können hier Personen, Institutionen, Gruppen oder Klassen zugeordnet werden.
        Bei der Gruppenauswahl besteht zudem die Möglichkeit eine dynamische Verteilung vorzunehmen,
        d.h. bei Änderung von Positionen in der Gruppe wird die Checkliste automatisch aktualisiert (Standardeinstellung).');

        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));

        $availableHeader = array(
            'DisplayName' => 'Name',
            'Option'      => ''
        );

        if (empty($ListId)) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblList = CheckList::useService()->getListById($ListId);
            if (empty($tblList)) {
                $Stage->setContent(new Warning('Die Check-Liste konnte nicht abgerufen werden'));
            } else {

                $contentListObjectList = array();
                $tblListObjectListByList = CheckList::useService()->getListObjectListByList($tblList);
                if ($tblListObjectListByList) {
                    /** @var TblListObjectList $tblListObjectList */
                    foreach ($tblListObjectListByList as $tblListObjectList) {
                        $item = array();
                        if (($tblObject = $tblListObjectList->getServiceTblObject())) {
                            if ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSON') {
                                /** @var TblPerson $tblObject */
                                // display groups
                                $groups = array();
                                $tblGroupList = Group::useService()->getGroupAllByPerson($tblObject);
                                if ($tblGroupList) {
                                    foreach ($tblGroupList as $tblGroup) {
                                        $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                                        if ($tblGroupCommon->getId() != $tblGroup->getId()) {
                                            $groups[] = $tblGroup->getName();
                                        }
                                    }
                                }
                                if (empty($groups)) {
                                    $groups = '';
                                } else {
                                    $groups = implode(', ', $groups);
                                }

                                $item = array(
                                    'DisplayName' => $tblObject->getLastFirstName(),
                                    'Groups'      => $groups
                                );
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANY') {
                                /** @var TblCompany $tblObject */
                                // display groups
                                $groups = array();
                                $tblGroupList = CompanyGroup::useService()->getGroupAllByCompany($tblObject);
                                if ($tblGroupList) {
                                    foreach ($tblGroupList as $tblGroup) {
                                        $tblGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');
                                        if ($tblGroupCommon->getId() != $tblGroup->getId()) {
                                            $groups[] = $tblGroup->getName();
                                        }
                                    }
                                }
                                if (empty($groups)) {
                                    $groups = '';
                                } else {
                                    $groups = implode(', ', $groups);
                                }

                                $item = array(
                                    'DisplayName' => $tblObject->getName() . new Container($tblObject->getExtendedName()),
                                    'Groups'      => $groups
                                );
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                                /** @var PersonGroupEntity $tblObject */

                                $item = array(
                                    'DisplayName' => $tblObject->getName()
                                        . ' (' . PersonGroup::useService()->countMemberByGroup($tblObject) . ')',
                                    'Groups'      => ''
                                );
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                                /** @var CompanyGroupEntity $tblObject */

                                $item = array(
                                    'DisplayName' => $tblObject->getName()
                                        . ' (' . CompanyGroup::useService()->countMemberByGroup($tblObject) . ')',
                                    'Groups'      => ''
                                );
                            } elseif ($tblListObjectList->getTblObjectType()->getIdentifier() === 'DIVISIONGROUP') {
                                /** @var TblDivision $tblObject */
                                $tblYear = $tblObject->getServiceTblYear();

                                $item = array(
                                    'DisplayName' => ($tblYear ? $tblYear->getDisplayName() . ' ' : '')
                                        . $tblObject->getDisplayName()
                                        . ' (' . Division::useService()->countDivisionStudentAllByDivision($tblObject) . ')',
                                    'Groups'      => ''
                                );
                            } else {
                                $item = false;
                            }

                            if ($item) {
                                $item['Option'] =
                                    (new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen',
                                        '/Reporting/CheckList/Object/Remove',
                                        new Minus(), array(
                                            'Id' => $tblListObjectList->getId()
                                        )))->__toString();
                            }
                        }

                        if ($item) {
                            $contentListObjectList[] = $item;
                        }
                    }
                }

                $tblObjectTypeAll = CheckList::useService()->getObjectTypeAll();
                if ($tblObjectTypeAll) {
                    array_push($tblObjectTypeAll, new TblObjectType());
                }
                $tblObjectType = false;
                $selectList = array();

                if ($ObjectTypeId !== null) {
                    $Global = $this->getGlobal();
                    if (!$Global->POST) {
                        $Global->POST['ObjectTypeSelect']['Id'] = $ObjectTypeId;
                        $Global->savePost();
                    }

                    $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);
                    if ($tblObjectType) {
                        if ($tblObjectType->getIdentifier() === 'PERSON') {

                            $tblPersonAll = Person::useService()->getPersonAll();
                            $tblPersonInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblPersonAll && $tblPersonInList) {
                                $tblPersonAll = array_udiff($tblPersonAll, $tblPersonInList,
                                    function (TblPerson $ObjectA, TblPerson $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblPersonAll) {
                                foreach ($tblPersonAll as $tblPerson) {
                                    // display groups
                                    $groups = array();
                                    if ($tblPerson) {
                                        $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                                        if ($tblGroupList) {
                                            foreach ($tblGroupList as $tblGroup) {
                                                $tblGroupCommon = Group::useService()->getGroupByMetaTable('COMMON');
                                                if ($tblGroupCommon->getId() != $tblGroup->getId()) {
                                                    $groups[] = $tblGroup->getName();
                                                }
                                            }
                                        }
                                    }
                                    if (empty($groups)) {
                                        $groups = '';
                                    } else {
                                        $groups = implode(', ', $groups);
                                    }

                                    $selectList[] = array(
                                        'DisplayName' => $tblPerson->getLastFirstName(),
                                        'Groups'      => $groups,
                                        'Option'      => ( new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId'       => $tblList->getId(),
                                                'ObjectId'     => $tblPerson->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {

                            $tblCompanyAll = Company::useService()->getCompanyAll();
                            $tblCompanyInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblCompanyAll && $tblCompanyInList) {
                                $tblCompanyAll = array_udiff($tblCompanyAll, $tblCompanyInList,
                                    function (TblCompany $ObjectA, TblCompany $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblCompanyAll) {
                                /** @var TblCompany $tblCompany */
                                foreach ($tblCompanyAll as $tblCompany) {
                                    // display groups
                                    $groups = array();
                                    $tblGroupList = CompanyGroup::useService()->getGroupAllByCompany($tblCompany);
                                    if ($tblGroupList) {
                                        foreach ($tblGroupList as $tblGroup) {
                                            $tblGroupCommon = CompanyGroup::useService()->getGroupByMetaTable('COMMON');
                                            if ($tblGroupCommon->getId() != $tblGroup->getId()) {
                                                $groups[] = $tblGroup->getName();
                                            }
                                        }
                                    }
                                    if (empty($groups)) {
                                        $groups = '';
                                    } else {
                                        $groups = implode(', ', $groups);
                                    }

                                    $selectList[] = array(
                                        'DisplayName' => $tblCompany->getName() . new Container($tblCompany->getExtendedName()),
                                        'Groups'      => $groups,
                                        'Option'      => ( new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId'       => $tblList->getId(),
                                                'ObjectId'     => $tblCompany->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'PERSONGROUP') {

                            $tblPersonGroupAll = PersonGroup::useService()->getGroupAll();
                            $tblPersonGroupInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblPersonGroupAll && $tblPersonGroupInList) {
                                $tblPersonGroupAll = array_udiff($tblPersonGroupAll, $tblPersonGroupInList,
                                    function (PersonGroupEntity $ObjectA, PersonGroupEntity $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblPersonGroupAll) {
                                foreach ($tblPersonGroupAll as $tblPersonGroup) {
                                    $Global->POST['Option'][$tblPersonGroup->getId()] = 1;
                                    $Global->savePost();

                                    $selectList[] = array(
                                        'DisplayName' => $tblPersonGroup->getName()
                                            . ' (' . PersonGroup::useService()->countMemberByGroup($tblPersonGroup) . ')',
                                        'Groups'      => '',
                                        'Option'      => ( new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new CheckBox('Option[' . $tblPersonGroup->getId() . ']',
                                                            'dynamisch', 1)
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId'       => $tblList->getId(),
                                                'ObjectId'     => $tblPersonGroup->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'COMPANYGROUP') {

                            $tblCompanyGroupAll = CompanyGroup::useService()->getGroupAll();
                            $tblCompanyGroupInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblCompanyGroupAll && $tblCompanyGroupInList) {
                                $tblCompanyGroupAll = array_udiff($tblCompanyGroupAll, $tblCompanyGroupInList,
                                    function (CompanyGroupEntity $ObjectA, CompanyGroupEntity $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblCompanyGroupAll) {
                                foreach ($tblCompanyGroupAll as $tblCompanyGroup) {
                                    $Global->POST['Option'][$tblCompanyGroup->getId()] = 1;
                                    $Global->savePost();

                                    $selectList[] = array(
                                        'DisplayName' => $tblCompanyGroup->getName()
                                            . ' (' . CompanyGroup::useService()->countMemberByGroup($tblCompanyGroup) . ')',
                                        'Groups'      => '',
                                        'Option'      => ( new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new CheckBox('Option[' . $tblCompanyGroup->getId() . ']',
                                                            'dynamisch', 1)
                                                        , 7),
                                                    new FormColumn(
                                                        new Primary('Hinzufügen',
                                                            new Plus())
                                                        , 5)
                                                ))
                                            ), null,
                                            '/Reporting/CheckList/Object/Add', array(
                                                'ListId'       => $tblList->getId(),
                                                'ObjectId'     => $tblCompanyGroup->getId(),
                                                'ObjectTypeId' => $tblObjectType->getId()
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        } elseif ($tblObjectType->getIdentifier() === 'DIVISIONGROUP') {

                            $tblDivisionAll = Division::useService()->getDivisionAll();
                            $tblDivisionInList = CheckList::useService()->getObjectAllByListAndObjectType($tblList,
                                $tblObjectType);
                            if ($tblDivisionAll && $tblDivisionInList) {
                                $tblDivisionAll = array_udiff($tblDivisionAll, $tblDivisionInList,
                                    function (TblDivision $ObjectA, TblDivision $ObjectB) {

                                        return $ObjectA->getId() - $ObjectB->getId();
                                    }
                                );
                            }

                            if ($tblDivisionAll) {
                                foreach ($tblDivisionAll as $tblDivision) {
                                    $Global->POST['Option'][$tblDivision->getId()] = 1;
                                    $Global->savePost();
                                    $tblYear = $tblDivision->getServiceTblYear();

                                    $selectList[] = array(
                                        'DisplayName' => ($tblYear ? $tblYear->getDisplayName() . ' ' : '')
                                            . $tblDivision->getDisplayName()
                                            . ' (' . Division::useService()->countDivisionStudentAllByDivision($tblDivision) . ')',
                                        'Groups'      => '',
                                        'Option'      =>
                                            (new Form(
                                                new FormGroup(
                                                    new FormRow(array(
                                                        new FormColumn(
                                                            new CheckBox('Option[' . $tblDivision->getId() . ']',
                                                                'dynamisch', 1)
                                                            , 7),
                                                        new FormColumn(
                                                            new Primary('Hinzufügen',
                                                                new Plus())
                                                            , 5)
                                                    ))
                                                ), null,
                                                '/Reporting/CheckList/Object/Add', array(
                                                    'ListId'       => $tblList->getId(),
                                                    'ObjectId'     => $tblDivision->getId(),
                                                    'ObjectTypeId' => $tblObjectType->getId()
                                                )
                                            ))->__toString()
                                    );
                                }
                            }
                        }

                        if ($tblObjectType->getIdentifier() === 'PERSON' || $tblObjectType->getIdentifier() === 'COMPANY') {
                            $availableHeader = array(
                                'DisplayName' => 'Name',
                                'Groups'      => 'Gruppen ', // space important
                                'Option'      => ''
                            );
                        } else {
                            $availableHeader = array(
                                'DisplayName' => 'Name',
                                'Option'      => ''
                            );
                        }
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Check-Liste', new Bold($tblList->getName()) .
                                        ($tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                            . new Muted(new Small(new Small($tblList->getDescription()))) : ''),
                                        Panel::PANEL_TYPE_SUCCESS),
                                    4
                                ),
                                new LayoutColumn(new Well(
                                    CheckList::useService()->getObjectType(
                                        new Form(new FormGroup(array(
                                            new FormRow(array(
                                                new FormColumn(
                                                    new SelectBox('ObjectTypeSelect[Id]',
                                                        'Person / Institution / Gruppe / Klasse',
                                                        array(
                                                            '{{ Name }}' => $tblObjectTypeAll
                                                        )),
                                                    12
                                                ),
                                            )),
                                        )), new Primary('Auswählen', new Select()))
                                        , $tblList->getId(), $ObjectTypeSelect)
                                ))
                            ))
                        ))
                    ))
                    . (empty($ObjectTypeSelect) ? ($tblObjectType ?
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel('Objekt-Typ:',
                                $tblObjectType->getName(),
                                Panel::PANEL_TYPE_INFO), 12
                        ))))
                        . new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Objekte'),
                                    new TableData($contentListObjectList, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Groups'      => 'Gruppen',
                                            'Option'      => ''
                                        )
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Title('Verfügbare', 'Objekte'),
                                    new TableData($selectList, null, $availableHeader
                                    )
                                ), 6),
                            ))
                        )))
                        : new Layout(new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Title('Ausgewählte', 'Objekte'),
                                    new TableData($contentListObjectList, null,
                                        array(
                                            'DisplayName' => 'Name',
                                            'Groups'      => 'Gruppen',
                                            'Option'      => ''
                                        )
                                    )
                                ), 12)
                            ))
                        )))) : '')
                );
            }
        }

        return $Stage;
    }

    /**
     * @param null $ListId
     * @param null $ObjectId
     * @param null $ObjectTypeId
     * @param null $Option
     *
     * @return Stage|string
     */
    public function frontendListObjectAdd($ListId = null, $ObjectId = null, $ObjectTypeId = null, $Option = null)
    {

        $Stage = new Stage('Check-Listen', 'Ein Object einer Check-Liste hinzufügen');

        if ($ListId === null || $ObjectId === null || $ObjectTypeId === null) {
            return $Stage . new Danger(new Ban() . ' Daten nicht abrufbar.')
                .new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        $tblList = CheckList::useService()->getListById($ListId);
        $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);

        if ($tblList && $tblObjectType && $ObjectId !== null) {
            if ($tblObjectType->getIdentifier() === 'PERSON') {
                $tblPerson = Person::useService()->getPersonById($ObjectId);
                if ($tblPerson) {
                    if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblPerson)) {
                        return $Stage.new Success(new SuccessIcon().
                                ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                            .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }
                }
                return $Stage . new Danger(new Ban() .
                        ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                    .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
            } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
                $tblCompany = Company::useService()->getCompanyById($ObjectId);
                if ($tblCompany) {
                    if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblCompany)) {
                        return $Stage.new Success(new SuccessIcon().
                                ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                            .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }
                }
                return $Stage . new Danger(new Ban() .
                        ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                    .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                        array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
            } elseif ($tblObjectType->getIdentifier() === 'PERSONGROUP') {
                $tblPersonGroup = PersonGroup::useService()->getGroupById($ObjectId);
                if ($tblPersonGroup) {
                    if (isset($Option[$tblPersonGroup->getId()])) {

                        if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblPersonGroup)) {
                            return $Stage.new Success(new SuccessIcon().
                                    ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        } else {
                            return $Stage . new Danger(new Ban() .
                                    ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        }

                    } else {
                        $tblPersonAllByGroup = PersonGroup::useService()->getPersonAllByGroup($tblPersonGroup);
                        $countAdd = 0;
                        $countExists = 0;
                        if ($tblPersonAllByGroup) {
                            foreach ($tblPersonAllByGroup as $tblPerson) {
                                if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                    $tblList, CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson)
                                ) {
                                    $countExists++;
                                } else {
                                    CheckList::useService()->addObjectToList($tblList,
                                        CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson);
                                    $countAdd++;
                                }
                            }
                        }

                        return $Stage.new Success(new SuccessIcon().
                                ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                            .new Success(new SuccessIcon().$countAdd.' Person/en hinzugefügt.')
                            .( $countExists > 0 ? new Warning($countExists.' Person/en existierten bereits in der Check-Liste') : '' )
                            .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }
                } else {
                    return $Stage . new Danger(new Ban() .
                            ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                        .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'COMPANYGROUP') {
                $tblCompanyGroup = CompanyGroup::useService()->getGroupById($ObjectId);
                if ($tblCompanyGroup) {
                    if (isset($Option[$tblCompanyGroup->getId()])) {

                        if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblCompanyGroup)) {
                            return $Stage.new Success(new SuccessIcon().
                                    ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        } else {
                            return $Stage . new Danger(new Ban() .
                                    ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        }

                    } else {
                        $tblCompanyByGroup = CompanyGroup::useService()->getCompanyAllByGroup($tblCompanyGroup);
                        $countAdd = 0;
                        $countExists = 0;
                        if ($tblCompanyByGroup) {
                            foreach ($tblCompanyByGroup as $tblCompany) {
                                if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                    $tblList, CheckList::useService()->getObjectTypeByIdentifier('COMPANY'),
                                    $tblCompany)
                                ) {
                                    $countExists++;
                                } else {
                                    CheckList::useService()->addObjectToList($tblList,
                                        CheckList::useService()->getObjectTypeByIdentifier('COMPANY'), $tblCompany);
                                    $countAdd++;
                                }
                            }
                        }

                        return $Stage.new Success(new SuccessIcon().
                                ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                            .new Success(new SuccessIcon().$countAdd.' Institution/en hinzugefügt.')
                            .( $countExists > 0 ? new Warning($countExists.' Institution/en existierten bereits in der Check-Liste') : '' )
                            .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }
                } else {
                    return $Stage . new Danger(new Ban() .
                            ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                        .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            } elseif ($tblObjectType->getIdentifier() === 'DIVISIONGROUP') {
                $tblDivision = Division::useService()->getDivisionById($ObjectId);
                if ($tblDivision) {
                    if (isset($Option[$tblDivision->getId()])) {

                        if (CheckList::useService()->addObjectToList($tblList, $tblObjectType, $tblDivision)) {
                            return $Stage.new Success(new SuccessIcon().
                                    ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        } else {
                            return $Stage . new Danger(new Ban() .
                                    ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                                .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                                    array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                        }

                    } else {
                        $tblDivisionStudentByGroup = Division::useService()->getStudentAllByDivision($tblDivision);
                        $countAdd = 0;
                        $countExists = 0;
                        if ($tblDivisionStudentByGroup) {
                            foreach ($tblDivisionStudentByGroup as $tblPerson) {
                                if (CheckList::useService()->getListObjectListByListAndObjectTypeAndObject(
                                    $tblList, CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson)
                                ) {
                                    $countExists++;
                                } else {
                                    CheckList::useService()->addObjectToList($tblList,
                                        CheckList::useService()->getObjectTypeByIdentifier('PERSON'), $tblPerson);
                                    $countAdd++;
                                }
                            }
                        }

                        return $Stage.new Success(new SuccessIcon().
                                ' Die '.$tblObjectType->getName().' ist zur Check-Liste hinzugefügt worden.')
                            .new Success(new SuccessIcon().$countAdd.' Person/en hinzugefügt.')
                            .( $countExists > 0 ? new Warning($countExists.' Person/en existierten bereits in der Check-Liste') : '' )
                            .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_SUCCESS,
                                array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                    }
                } else {
                    return $Stage . new Danger(new Ban() .
                            ' Die '.$tblObjectType->getName().' konnte zur Check-Liste nicht hinzugefügt werden.')
                        .new Redirect('/Reporting/CheckList/Object/Select', Redirect::TIMEOUT_ERROR,
                            array('ListId' => $tblList->getId(), 'ObjectTypeId' => $tblObjectType->getId()));
                }
            }

        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return string
     */
    public function frontendListObjectRemove(
        $Id = null
    ) {

        return CheckList::useService()->removeObjectFromList($Id);
    }

    /**
     * @param null $Id
     * @param null $Filter
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOption1Id
     * @param null $SchoolOption2Id
     *
     * @return string|Stage
     */
    public function frontendListObjectElementShow
    (
        $Id = null,
        $Filter = null,
//        $Data = null,
//        $HasData = null,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOption1Id = null,
        $SchoolOption2Id = null
    ) {

        // test it with much more Field's required much more space
        // keep in mind to deal with it if it is Live necessary
//        ini_set('memory_limit','2G');

        $Stage = new Stage('Check-Liste', 'Ansicht');
        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList', new ChevronLeft()));
        if (!$Id) {
            return $Stage.new Danger(new Ban().' Liste nicht gefunden')
                .new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }
        $tblList = CheckList::useService()->getListById($Id);
        if (!$tblList) {
            return $Stage.new Danger(new Ban().' Liste nicht gefunden')
                .new Redirect('/Reporting/CheckList', Redirect::TIMEOUT_ERROR);
        }

        $columnDefinition = array(
            'Name' => 'Name',
        );
        $list = array();
        $objectList = array();

        $isProspectList = false;
        $filterPersonObjectList = array();
        $hasFilter = false;
        $filterYear = false;
        $filterLevel = false;
        $filterSchoolOption1 = false;
        $filterSchoolOption2 = false;

        $EditHead = 'Option';

        $countPerson = 0;
        $countTotalPerson = 0;
        $countCompany = 0;

        // filter
        if ($YearPersonId !== null) {
            $Global = $this->getGlobal();
            $Global->POST['Filter']['Year'] = $YearPersonId;
            $Global->savePost();

            $yearPerson = Person::useService()->getPersonById($YearPersonId);
            if ($yearPerson) {
                $hasFilter = true;
                $tblProspect = Prospect::useService()->getProspectByPerson($yearPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $filterYear = trim($tblProspectReservation->getReservationYear());
                    }
                }
            }
        }
        if ($LevelPersonId !== null) {
            $Global = $this->getGlobal();
            $Global->POST['Filter']['Level'] = $LevelPersonId;
            $Global->savePost();

            $levelPerson = Person::useService()->getPersonById($LevelPersonId);
            if ($levelPerson) {
                $hasFilter = true;
                $tblProspect = Prospect::useService()->getProspectByPerson($levelPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $filterLevel = trim($tblProspectReservation->getReservationDivision());
                    }
                }
            }
        }
        if ($SchoolOption1Id !== null) {
            $Global = $this->getGlobal();
            $Global->POST['Filter']['SchoolOption1'] = $SchoolOption1Id;
            $Global->savePost();

            $schoolOption = Type::useService()->getTypeById($SchoolOption1Id);
            if ($schoolOption) {
                $hasFilter = true;
                $filterSchoolOption1 = $schoolOption;
            }
        }
        if ($SchoolOption2Id !== null) {
            $Global = $this->getGlobal();
            $Global->POST['Filter']['SchoolOption2'] = $SchoolOption2Id;
            $Global->savePost();

            $schoolOption = Type::useService()->getTypeById($SchoolOption2Id);
            if ($schoolOption) {
                $hasFilter = true;
                $filterSchoolOption2 = $schoolOption;
            }
        }

        if ($tblList) {

            $ListObjectListContent = CheckList::useService()->getListObjectListContentByList($tblList);

            $ObjectContentList = array();
            if($ListObjectListContent){
                foreach($ListObjectListContent as $ObjectContent){
                    $ObjectContentList[$ObjectContent['ObjectId']][$ObjectContent['ListElementListId']] = $ObjectContent['Value'];
                }
            }

            if(($tblListObjectElementListArray = CheckList::useService()->getListObjectElementListByList($tblList))){
                foreach($tblListObjectElementListArray as $ListObjectElementList){
                    $ObjectId = false;
                    $ListElementListId = false;
                    if($ListObjectElementList->getServiceTblObject()){
                        $ObjectId = $ListObjectElementList->getServiceTblObject()->getId();
                    }
                    if($ListObjectElementList->getTblListElementList()){
                        $ListElementListId = $ListObjectElementList->getTblListElementList()->getId();
                    }
                    if($ObjectId && $ListElementListId)
                        $ListContent[$ObjectId][$ListElementListId] = $ListObjectElementList->getValue();
                }
            }


            // set Header
            $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);
            if ($tblListElementListByList) {
                // set Edit Column
                $columnDefinition['FieldOptionLeft'] = $EditHead;
                foreach ($tblListElementListByList as $tblListElementList) {
                    $columnDefinition['Field'.$tblListElementList->getId()] = $tblListElementList->getName();
                }
                // set Edit Column
                $columnDefinition['FieldOptionRight'] = $EditHead;
            }

            $tblListObjectListByList = CheckList::useService()->getListObjectListByList($tblList);

            // get Objects
            $objectList = CheckList::useService()->getObjectList($tblListObjectListByList, $objectList);
            if ($hasFilter) {
                // build filter selectBox content from all
                if (!empty( $objectList )) {
                    foreach ($objectList as $objectTypeId => $objects) {
                        if (!empty( $objects )) {
                            $tblObjectType = CheckList::useService()->getObjectTypeById($objectTypeId);
                            if ($tblObjectType->getIdentifier() === 'PERSON') {
                                foreach ($objects as $objectId => $value) {
                                    $countTotalPerson++;
                                    $tblPerson = Person::useService()->getPersonById($objectId);
                                    if ($tblPerson) {
                                        $filterPersonObjectList[$tblPerson->getId()] = $tblPerson;
                                    }
                                }
                            }
                        }
                    }
                }

                $objectList = CheckList::useService()->filterObjectList($objectList, $filterYear, $filterLevel,
                    $filterSchoolOption1, $filterSchoolOption2);
            }

            // sort $objectList
//            $objectList = CheckList::useService()->sortObjectList($objectList);

            if (!empty( $objectList )) {

                // prospectList
                $isProspectList = true;
                if (!$hasFilter) {
                    $prospectGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                    foreach ($objectList as $objectTypeId => $objects) {
                        $tblObjectType = CheckList::useService()->getObjectTypeById($objectTypeId);
                        if ($tblObjectType->getIdentifier() == 'PERSON') {
                            if (!empty( $objects )) {
                                foreach ($objects as $objectId => $value) {
                                    $tblPerson = Person::useService()->getPersonById($objectId);
                                    if ($tblPerson && !Group::useService()->existsGroupPerson($prospectGroup,$tblPerson)) {
                                        $isProspectList = false;
                                        break 2;
                                    }
                                }
                            }
                        } else {
                            $isProspectList = false;
                            break;
                        }
                    }
                }
                if ($isProspectList) {
                    $columnDefinition = array(
                        'Name'            => 'Interessentenname'.str_repeat('&nbsp;', 18 ),
                        'FieldOptionLeft' => $EditHead,
                        'Year'            => 'Schul&shy;jahr',
                        'Level'           => 'Kl. - Stufe',
                        'SchoolOption'    => 'Schulart',
                        'ReservationDate' => 'Eingangs&shy;datum',
                        'Phone'           => 'Telefon Interessent',
                        'PhoneGuardian'   => 'Telefon Sorgeberechtigte',
                        'Address'         => 'Adresse'
                    );
                    // set Header for prospectList
                    $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);
                    if ($tblListElementListByList) {
                        foreach ($tblListElementListByList as $tblListElementList) {
                            $columnDefinition['Field'.$tblListElementList->getId()] = $tblListElementList->getName();
                        }
                        // set Edit Column
                        $columnDefinition['FieldOptionRight'] = $EditHead;
                    }
                }

                $count = 0;
                foreach ($objectList as $objectTypeId => $objects) {
                    $tblObjectType = CheckList::useService()->getObjectTypeById($objectTypeId);

//                    $tblObject = CheckList::useService()->getObjectAllByListAndObjectType($tblList, $tblObjectType);
                    if (!empty( $objects )) {
                        foreach ($objects as $objectId => $value) {
                            if ($tblObjectType->getIdentifier() == 'PERSON') {
                                $countPerson++;
                                $tblPerson = Person::useService()->getPersonById($objectId);
                                if ($tblPerson) {
                                    $list[$count]['Name'] = new PullClear($tblPerson->getLastFirstName()
                                        .new PullRight(new Standard('', '/People/Person',
                                            new \SPHERE\Common\Frontend\Icon\Repository\Person(),
                                            array('Id' => $tblPerson->getId()), 'Zur Person')));

                                    if ($isProspectList) {

                                        if (!$hasFilter) {
                                            $filterPersonObjectList[$tblPerson->getId()] = $tblPerson;
                                        }

                                        // Prospect
                                        $level = false;
                                        $year = false;
                                        $option = false;
                                        $Phone = false;
                                        $PhoneGuardian = false;
                                        $Address = false;
                                        $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                                        if ($tblProspect) {
                                            $tblProspectReservation = $tblProspect->getTblProspectReservation();
                                            if ($tblProspectReservation) {
                                                $level = $tblProspectReservation->getReservationDivision();
                                                $year = $tblProspectReservation->getReservationYear();
                                                $optionA = $tblProspectReservation->getServiceTblTypeOptionA();
                                                $optionB = $tblProspectReservation->getServiceTblTypeOptionB();
                                                if ($optionA && $optionB) {
                                                    $option = $optionA->getName().', '.$optionB->getName();
                                                } elseif ($optionA) {
                                                    $option = $optionA->getName();
                                                } elseif ($optionB) {
                                                    $option = $optionB->getName();
                                                }
                                            }
                                            $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                                            if ($tblProspectAppointment) {
                                                $list[$count]['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                                            } else {
                                                $list[$count]['ReservationDate'] = '';
                                            }
                                        } else {
                                            $list[$count]['ReservationDate'] = '';
                                        }
                                        // display Address
                                        if (( $tblAddress = Address::useService()->getAddressByPerson($tblPerson) )) {
                                            $Address = $tblAddress->getGuiTwoRowString(false);
                                        }
                                        // display PhoneNumber
                                        if(($tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                                            $ProspectPhoneList = array();
                                            foreach ($tblToPhoneList as $tblToPhone) {
                                                if (($tblPhone = $tblToPhone->getTblPhone())) {
                                                    $ProspectPhoneList[] = $tblPhone->getNumber()
                                                        .' '.Phone::useService()->getPhoneTypeShort($tblToPhone)[0];
                                                }
                                            }
                                            $Phone = $tblPerson->getFirstName().' '.$tblPerson->getLastName()
                                                .' ('.implode( ', ', $ProspectPhoneList ).')';
                                        }
                                        // fill phoneGuardian
                                        $TblTypeGuardian = Relationship::useService()->getTypeByName( TblType::IDENTIFIER_GUARDIAN );
                                        $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $TblTypeGuardian);
                                        if ($guardianList) {
                                            foreach ($guardianList as $guardian) {
                                                $tblPersonGuardian = $guardian->getServiceTblPersonFrom();
                                                if ($tblPersonGuardian && $guardian->getTblType()->getId() == 1) {
                                                    // get PhoneNumber by Guardian
                                                    $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                                                    if ($tblToPhoneList) {
                                                        $GuardianPhoneList = array();
                                                        foreach ($tblToPhoneList as $tblToPhone) {
                                                            if (( $tblPhone = $tblToPhone->getTblPhone() )) {
                                                                $GuardianPhoneList[] = $tblPhone->getNumber().' '.Phone::useService()->getPhoneTypeShort($tblToPhone)[0];
                                                            }
                                                        }
                                                        $Item[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName().' '.$tblPersonGuardian->getLastName()
                                                            .' ('.implode(', ', $GuardianPhoneList).')';
                                                    }
                                                    if (!$PhoneGuardian && isset($Item[$tblPersonGuardian->getId()])) {
                                                        $PhoneGuardian = $Item[$tblPersonGuardian->getId()];
                                                    } elseif ($PhoneGuardian && isset($Item[$tblPersonGuardian->getId()])) {
                                                        $PhoneGuardian .= ', <br/>'.$Item[$tblPersonGuardian->getId()];
                                                    }
                                                }
                                            }
                                        }

                                        $list[$count]['Year'] = $year;
                                        $list[$count]['Level'] = $level;
                                        $list[$count]['SchoolOption'] = $option;
                                        $list[$count]['Phone'] = $Phone;
                                        $list[$count]['PhoneGuardian'] = $PhoneGuardian;
                                        $list[$count]['Address'] = $Address;
                                    }
                                }
                            } elseif ($tblObjectType->getIdentifier() == 'COMPANY') {
                                $tblCompany = Company::useService()->getCompanyById($objectId);
                                if ($tblCompany) {
                                    $countCompany++;
                                    $list[$count]['Name'] = new PullClear($tblCompany->getName().new Container($tblCompany->getExtendedName())
                                        .new PullRight(new Standard('', '/Corporation/Company',
                                            new Building(),
                                            array('Id' => $tblCompany->getId()), 'Zur Institution')));
                                } else {
                                    $list[$count]['Name'] = '';
                                }
                            } else {
                                $list[$count]['Name'] = '';
                            }

                            if ($tblListElementListByList){
                                foreach ($tblListElementListByList as $tblListElementList) {
                                    if ($tblListElementList->getTblElementType()->getIdentifier() === 'CHECKBOX'){
                                        if (isset($ObjectContentList[$objectId][$tblListElementList->getId()]) && $ObjectContentList[$objectId][$tblListElementList->getId()] == 1){
                                            $list[$count]['Field'.$tblListElementList->getId()] = new Center(new SuccessIcon());
                                        } else {
                                            $list[$count]['Field'.$tblListElementList->getId()] = '';
                                        }
                                    } else {
                                        if (isset($ObjectContentList[$objectId][$tblListElementList->getId()])){
                                            $list[$count]['Field'.$tblListElementList->getId()] = $ObjectContentList[$objectId][$tblListElementList->getId()];
                                            // show string 0 in Datatable
                                            if ($ObjectContentList[$objectId][$tblListElementList->getId()] === "0"){
                                                $list[$count]['Field'.$tblListElementList->getId()] = ' '.$ObjectContentList[$objectId][$tblListElementList->getId()];
                                            }
                                        } else {
                                            $list[$count]['Field'.$tblListElementList->getId()] = '';
                                        }
                                    }
                                }
                            }

                            // Old Version keep in mind until everything work's fine
//                            if ($tblListElementListByList) {
//                                foreach ($tblListElementListByList as $tblListElementList) {
//                                    $list[$count]['Field'.$tblListElementList->getId()] = '';
//
//                                    $tblListObjectElementList = CheckList::useService()->getListObjectElementListByListAndListElementListAndObjectTypeAndObjectId(
//                                        $tblList,
//                                        $tblListElementList,
//                                        $tblObjectType,
//                                        $objectId);
//                                    if ($tblListObjectElementList){
//                                        if ($tblListElementList->getTblElementType()->getIdentifier() === 'CHECKBOX'){
//                                            if ($tblListObjectElementList->getValue() == 1){
//                                                $list[$count]['Field'.$tblListElementList->getId()] = new Center(new SuccessIcon());
//                                            }
//                                        } else {
//                                            $list[$count]['Field'.$tblListElementList->getId()] = $tblListObjectElementList->getValue();
//                                            // show string 0 in Datatable
//                                            if ($tblListObjectElementList->getValue() === "0"){
//                                                $list[$count]['Field'.$tblListElementList->getId()] = ' '.$tblListObjectElementList->getValue();
//                                            }
//                                        }
//                                    }
//                                }
//                            }
                            // Edit Button left = right
                            $list[$count]['FieldOptionLeft'] = $list[$count]['FieldOptionRight'] = new Standard('', '/Reporting/CheckList/Object/Element/Edit', new Edit(),
                                array('ObjectId'        => $objectId,
                                      'ListId'          => $tblList->getId(),
                                      'ObjectTypeId'    => $tblObjectType->getId(),
                                      'YearPersonId'    => $YearPersonId,
                                      'LevelPersonId'   => $LevelPersonId,
                                      'SchoolOption1Id' => $SchoolOption1Id,
                                      'SchoolOption2Id' => $SchoolOption2Id
                                ));
                            $count++;
                        }
                    }
                }
            } else {
                if ($hasFilter) {
                    $columnDefinition = array(
                        'Name'            => 'Interessentenname&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'FieldOptionLeft' => $EditHead,
                        'Year'            => 'Schul&shy;jahr',
                        'Level'           => 'Kl. - Stufe',
                        'SchoolOption'    => 'Schulart',
                        'ReservationDate' => 'Eingangs&shy;datum'
                    );
                    // set Header for prospectList
                    $tblListElementListByList = CheckList::useService()->getListElementListByList($tblList);
                    if ($tblListElementListByList) {
                        foreach ($tblListElementListByList as $tblListElementList) {
                            $columnDefinition['Field'.$tblListElementList->getId()] = $tblListElementList->getName();
                        }
                        // set Edit Column
                        $columnDefinition['FieldOptionRight'] = $EditHead;
                    }
                }
            }
        }

        if (!empty( $list )) {
            $Stage->addButton(
                new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                    '/Api/Reporting/CheckList/Download', new Download(), array(
                        'ListId'          => $tblList->getId(),
                        'YearPersonId'    => $YearPersonId,
                        'LevelPersonId'   => $LevelPersonId,
                        'SchoolOption1Id' => $SchoolOption1Id,
                        'SchoolOption2Id' => $SchoolOption2Id
                    ))
            );
        }

        if ($isProspectList || $hasFilter) {
            $form = $this->formCheckListFilter($filterPersonObjectList)->appendFormButton(new Primary('Filtern',
                new Filter()));
        } else {
            $form = $this->formCheckListFilter(array())->appendFormButton(new Primary('Filtern', new Filter()));
        }

        if ($filterSchoolOption1 && $filterSchoolOption2) {
            $filterSchoolOptionText = $filterSchoolOption1->getName().', '.$filterSchoolOption2->getName();
        } elseif ($filterSchoolOption1) {
            $filterSchoolOptionText = $filterSchoolOption1->getName();
        } elseif ($filterSchoolOption2) {
            $filterSchoolOptionText = $filterSchoolOption2->getName();
        } else {
            $filterSchoolOptionText = '';
        }

        $Stage->setContent(
            new Layout(array(
                ( $isProspectList || $hasFilter ? new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new Title(new Filter().' Filter'),
                                new Well(CheckList::useService()->getFilteredCheckList($form, $Id, $Filter))
                            )
                        ),
                    ))
                )) : null ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Check-Liste', new Bold($tblList->getName()).
                                ( $tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblList->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_SUCCESS),
                            $hasFilter ? 6 : 12
                        ),
                        ( $hasFilter ?
                            new LayoutColumn(
                                new Panel(new Filter().' Filter',
                                    ( $filterYear ? new Bold('Schuljahr: ').$filterYear : '' ).'&nbsp;&nbsp;'.
                                    ( $filterLevel ? new Bold(' Klassenstufe: ').$filterLevel : '' ).'&nbsp;&nbsp;'.
                                    ( $filterSchoolOption1 || $filterSchoolOption2 ? new Bold(' Schulart: ').
                                        $filterSchoolOptionText : '' ),
                                    Panel::PANEL_TYPE_INFO),
                                $hasFilter ? 6 : 12
                            ) : null )
                    ))
                )),
                ( empty( $Filter ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Title(new Edit().' Bearbeiten'),
                                new Bold(
                                    $isProspectList
                                        ? ( $hasFilter
                                        ? new Info($countPerson.' von '.$countTotalPerson.' Interessenten')
                                        : new Info($countPerson.' Interessenten') )
                                        : new Info(
                                        'Anzahl der Objekte: '.( $countPerson + $countCompany ).' (Personen: '.$countPerson
                                        .', Institutionen: '.$countCompany.')'
                                    )),
                                new TableData($list, null, $columnDefinition,
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        ),
//                                        "pageLength" => -1,
                                        "responsive" => false
                                    ))
                            ))
                        ))
                    ))
                    : null )
            ))
        );

        return $Stage;
    }

    /**
     * @param null $ObjectId
     * @param null $ListId
     * @param null $ObjectTypeId
     * @param null $Data
     * @param null $YearPersonId
     * @param null $LevelPersonId
     * @param null $SchoolOption1Id
     * @param null $SchoolOption2Id
     *
     * @return Stage
     */
    public function frontendListObjectElementEdit(
        $ObjectId = null,
        $ListId = null,
        $ObjectTypeId = null,
        $Data = null,
        $YearPersonId = null,
        $LevelPersonId = null,
        $SchoolOption1Id = null,
        $SchoolOption2Id = null
    )
    {

        $Stage = new Stage('Eintrag', 'Bearbeiten');
        if ($ObjectId == null || $ListId == null || $ObjectTypeId == null) {
            $Stage->addButton(new Standard('Zur Listenauswahl', '/Reporting/CheckList', new ChevronLeft()));
            return $Stage->setContent(new Warning('Fehlende Parameter'));
        }
        $tblObjectType = CheckList::useService()->getObjectTypeById($ObjectTypeId);
        $tblList = CheckList::useService()->getListById($ListId);
        if (!$tblObjectType || !$tblList) {
            $Stage->addButton(new Standard('Zur Listenauswahl', '/Reporting/CheckList', new ChevronLeft()));
            return $Stage->setContent(new Warning('Fehlerhafte Parameter'));
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/CheckList/Object/Element/Show', new ChevronLeft(),
            array('Id'              => $tblList->getId(),
                  'YearPersonId'    => $YearPersonId,
                  'LevelPersonId'   => $LevelPersonId,
                  'SchoolOption1Id' => $SchoolOption1Id,
                  'SchoolOption2Id' => $SchoolOption2Id)
        ));

        $formColumnArray = array();
        $formRowArray = array();
        $tblPerson = false;
        $tblCompany = false;
        $PanelName = '';

        if ($tblObjectType->getIdentifier() === 'PERSON') {
            $tblPerson = Person::useService()->getPersonById($ObjectId);
            if ($tblPerson) {
                $PanelName = $tblPerson->getFullName();
            }
        } elseif ($tblObjectType->getIdentifier() === 'COMPANY') {
            $tblCompany = Company::useService()->getCompanyById($ObjectId);
            if ($tblCompany) {
                $PanelName = $tblCompany->getDisplayName();
            }
        }

//        $tblObject = CheckList::useService()->getObjectByObjectTypeAndListAndId($tblObjectType, $tblList, $ObjectId);
        $ListElementList = CheckList::useService()->getListElementListByList($tblList);

        // set Post
        $tblListObjectElementListArray = CheckList::useService()->getListObjectElementListByListAndObjectId($tblList, $ObjectId);
        $Global = $this->getGlobal();
        if ($tblListObjectElementListArray && empty( $Data ) && !isset( $Global->POST['Button'] )) {
            /** @var TblListObjectElementList $tblListObjectElementList */
            foreach ($tblListObjectElementListArray as $tblListObjectElementList) {
                if ($tblListObjectElementList->getServiceTblObject()) {
                    if ($tblListObjectElementList->getServiceTblObject()->getId() == $ObjectId) {
                        $Global->POST['Data'][$tblListObjectElementList->getTblListElementList()->getId()] = $tblListObjectElementList->getValue();
                    }
                }
            }
            $Global->savePost();
        }

        $count = 0;
        if ($ListElementList) {
            foreach ($ListElementList as $tblListElement) {
                $count++;
//                if($tblListElement)
//                $PanelList[] = new Panel($tblListElement->getName(), new TextField('Data[Field'.$count.']'), Panel::PANEL_TYPE_INFO);

                if ($tblListElement->getTblElementType()->getIdentifier() === 'CHECKBOX') {
                    $formColumnArray[] = new FormColumn(new Panel($tblListElement->getName(), new CheckBox(
                        'Data['.$tblListElement->getId().']',
                        ' ', 1), Panel::PANEL_TYPE_INFO), 2);
                } elseif ($tblListElement->getTblElementType()->getIdentifier() === 'DATE') {
                    $formColumnArray[] = new FormColumn(new Panel($tblListElement->getName(), new DatePicker(
                        'Data['.$tblListElement->getId().']',
                        '', '', new Calendar()), Panel::PANEL_TYPE_INFO), 2);
                } elseif ($tblListElement->getTblElementType()->getIdentifier() === 'TEXT') {
                    $formColumnArray[] = new FormColumn(new Panel($tblListElement->getName(), new TextField(
                        'Data['.$tblListElement->getId().']',
                        '', '', new Comment()), Panel::PANEL_TYPE_INFO), 2);
                }

                if ($count % 6 === 0) {
                    // fill 6 FormColumns in FormRow
//                    $formRowArray[] = new FormRow(new FormColumn( new \SPHERE\Common\Frontend\Form\Repository\Title('Spalte ' ), 12)); // .($count - 6).' - '.$count )
                    $formRowArray[] = new FormRow($formColumnArray);
                    $formColumnArray = array();
                }
            }
            // Fill rest of FormColumns in FormRow
            if (!empty( $formColumnArray )) {
                $formRowArray[] = new FormRow($formColumnArray);
            }
        }


        $Form = new Form(new FormGroup($formRowArray));
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(( $tblCompany ? 'Institution' : ( $tblPerson ? 'Person' : '' ) ), $PanelName, Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Check-Liste', new Bold($tblList->getName()).
                                ( $tblList->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblList->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_SUCCESS),
                            4
                        ),
                        new LayoutColumn(new Well(
                            CheckList::useService()->updateListObjectElement(
                                $Form, $tblList, $tblObjectType, $ObjectId, $Data, $YearPersonId, $LevelPersonId, $SchoolOption1Id, $SchoolOption2Id
                            )
                        ))
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $filterPersonObjectList
     * @return Form
     */
    private function formCheckListFilter(
        $filterPersonObjectList
    ) {

        $yearAll = array();
        $levelAll = array();
        $schoolOptionAll = array();

        if (is_array($filterPersonObjectList) && !empty($filterPersonObjectList)) {
            /** @var TblPerson $tblPerson */
            foreach ($filterPersonObjectList as $tblPerson) {
                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $level = trim($tblProspectReservation->getReservationDivision());
                        $year = trim($tblProspectReservation->getReservationYear());
                        if ($year !== '') {
                            $yearAll[$tblPerson->getId()] = $year;
                        }
                        if ($level !== '') {
                            $levelAll[$tblPerson->getId()] = $level;
                        }
                        $optionA = $tblProspectReservation->getServiceTblTypeOptionA();
                        $optionB = $tblProspectReservation->getServiceTblTypeOptionB();
                        if ($optionA) {
                            $schoolOptionAll[$optionA->getId()] = $optionA->getName();
                        }
                        if ($optionB) {
                            $schoolOptionAll[$optionB->getId()] = $optionB->getName();
                        }
                    }
                }

            }
            $yearAll = array_unique($yearAll, SORT_REGULAR);
            $yearAll[0] = '';
            $levelAll = array_unique($levelAll, SORT_REGULAR);
            $levelAll[0] = '';
            $schoolOptionAll = array_unique($schoolOptionAll, SORT_REGULAR);
            $schoolOptionAll[0] = '';
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Filter[Year]', 'Schuljahr', $yearAll), 3
                    ),
                    new FormColumn(
                        new SelectBox('Filter[Level]', 'Klassenstufe', $levelAll), 3
                    ),
                    new FormColumn(
                        new SelectBox('Filter[SchoolOption1]', 'Schulart', $schoolOptionAll), 3
                    ),
                    new FormColumn(
                        new SelectBox('Filter[SchoolOption2]', 'Schulart', $schoolOptionAll), 3
                    ),
                ))
            ))
        ));
    }

}
