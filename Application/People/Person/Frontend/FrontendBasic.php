<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.12.2018
 * Time: 12:16
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\System\Database\Filter\Link\Pile;

/**
 * Class FrontendBasic
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendBasic extends FrontendReadOnly
{

    const TITLE = 'Grunddaten';

    /**
     * @return string
     */
    public function getCreatePersonContent()
    {

        return new Well($this->getCreatePersonForm())
            . ApiPersonEdit::receiverBlock('', 'SimilarPersonContent');
    }

    /**
     * @return Form
     */
    private function getCreatePersonForm()
    {
        $frontendCommon = (new FrontendCommon());

        $form = (new Form(array(
            new FormGroup(array(
                new FormRow(
                    new FormColumn($this->getEditBasicTitle(null, true)
                )),
                new FormRow(
                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        ApiPersonEdit::receiverBlock('', 'SimilarPersonMessage')
                    ))))
                )),
                $this->getBasicFormRow(true)
            )),
            new FormGroup(array(
                new FormRow(new FormColumn(
                    $frontendCommon->getEditCommonTitle(null, true))
                ),
                $frontendCommon->getCommonFormRow(),
                new FormRow(new FormColumn(
                    (new Primary(new Save() . ' Speichern', ApiPersonEdit::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveCreatePersonContent())
                ))
            )),
        )))->disableSubmitAction();

        return $form;
    }

    public function loadSimilarPersonContent($Person)
    {
        if ((!isset($Person['FirstName']) || empty($Person['FirstName']))
            || (!isset($Person['LastName']) || empty($Person['LastName']))
        ) {

            return '';
        } else {
            // dynamic search
            $Pile = new Pile();
            $Pile->addPile(Person::useService(), new ViewPerson());
            // find Input fields in ViewPerson
            $Result = $Pile->searchPile(array(
                array(
                    ViewPerson::TBL_PERSON_FIRST_NAME => explode(' ', $Person['FirstName']),
                    ViewPerson::TBL_PERSON_LAST_NAME => explode(' ', $Person['LastName'])
                )
            ));

            if (!empty($Result)) { // show Person

                $TableList = array();
                /** @var ViewPerson[] $ViewPerson */
                foreach ($Result as $Index => $ViewPerson) {
                    $TableList[$Index] = current($ViewPerson)->__toArray();

                    $PersonId = $PersonName = '';
                    $Address = new Warning('Keine Adresse hinterlegt');
                    $BirthDay = new Warning('Kein Datum hinterlegt');
                    if (isset($TableList[$Index]['TblPerson_Id'])) {
                        $PersonId = $TableList[$Index]['TblPerson_Id'];
                        $tblPerson = Person::useService()->getPersonById($PersonId);
                        if ($tblPerson) {
                            $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                            if ($tblCommon) {
                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                if ($tblCommonBirthDates) {
                                    if ($tblCommonBirthDates->getBirthday() != '') {
                                        $BirthDay = $tblCommonBirthDates->getBirthday();
                                    }
                                }
                            }

                            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                            if ($tblAddress) {
                                $Address = $tblAddress->getGuiString();
                            }
                        }
                    }
                    $TableList[$Index]['BirthDay'] = $BirthDay;
                    $TableList[$Index]['Address'] = $Address;
                    $TableList[$Index]['Option'] = new Standard('', '/People/Person', new \SPHERE\Common\Frontend\Icon\Repository\Person()
                        , array('Id' => $PersonId), 'Zur Person');
                }

                $Table = new TableData($TableList, new \SPHERE\Common\Frontend\Table\Repository\Title('Ähnliche Personen'), array(
                    ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                    ViewPerson::TBL_PERSON_TITLE => 'Titel',
                    ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                    ViewPerson::TBL_PERSON_SECOND_NAME => 'Zweiter Vorname',
                    ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                    ViewPerson::TBL_PERSON_BIRTH_NAME => 'Geburtsname',
                    'BirthDay' => 'Geburtstag',
                    'Address' => 'Adresse',
                    'Option' => '',
                ), array('order'      => array(
                    array(4, 'asc'),
                    array(2, 'asc')
                ),
                    'columnDefs' => array(
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                    )));

                return ApiPersonEdit::pipelineLoadSimilarPersonMessage(count($TableList), '', $Table->getHash()) . (string)$Table; // . $InfoPersonPipeline;
            }

            return ApiPersonEdit::pipelineLoadSimilarPersonMessage(0, $Person['FirstName'] . ' ' . $Person['LastName'], '') . '';
        }
    }

    /**
     * @param $countSimilarPerson
     * @param $name
     * @param $hash
     *
     * @return Danger|\SPHERE\Common\Frontend\Message\Repository\Success
     */
    public static function getSimilarPersonMessage($countSimilarPerson, $name, $hash) {
        if ($countSimilarPerson > 0) {
            return new Danger(new Bold($countSimilarPerson . ' Personen mit ähnlichem Namen gefunden. Ist diese Person schon angelegt?')
                . new Link('Zur Liste springen', null, null, array(), false, $hash)
            );
        } else {
            return new \SPHERE\Common\Frontend\Message\Repository\Success('Keine Personen zu ' . $name . ' gefunden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getBasicContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId, true))) {
            $groups = array();
            if (($tblGroupList = Group::useService()->getGroupAllSortedByPerson($tblPerson))) {
                foreach ($tblGroupList as $tblGroup) {
                    $groups[] = $tblGroup->getName();
                }
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Anrede'),
                    self::getLayoutColumnValue($tblPerson->getSalutation()),
                    self::getLayoutColumnLabel('Vorname'),
                    self::getLayoutColumnValue($tblPerson->getFirstName()),
                    self::getLayoutColumnLabel('Nachname'),
                    self::getLayoutColumnValue($tblPerson->getLastName()),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Titel'),
                    self::getLayoutColumnValue($tblPerson->getTitle()),
                    self::getLayoutColumnLabel('Zweiter Vorname'),
                    self::getLayoutColumnValue($tblPerson->getSecondName()),
                    self::getLayoutColumnLabel('Geburtsname'),
                    self::getLayoutColumnValue($tblPerson->getBirthName()),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnLabel('Rufname'),
                    self::getLayoutColumnValue($tblPerson->getCallName()),
                    self::getLayoutColumnEmpty(),
                    self::getLayoutColumnEmpty(),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Gruppen'),
                    self::getLayoutColumnValue(implode(', ', $groups), 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditBasicContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                self::getSubContent('Grunddaten', $content),
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new PersonParent()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditBasicContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if ($tblPerson->getTblSalutation()) {
                $Global->POST['Person']['Salutation'] = $tblPerson->getTblSalutation()->getId();
            }
            $Global->POST['Person']['Title'] = $tblPerson->getTitle();
            $Global->POST['Person']['FirstName'] = $tblPerson->getFirstName();
            $Global->POST['Person']['SecondName'] = $tblPerson->getSecondName();
            $Global->POST['Person']['CallName'] = $tblPerson->getCallName();
            $Global->POST['Person']['LastName'] = $tblPerson->getLastName();
            $Global->POST['Person']['BirthName'] = $tblPerson->getBirthName();
            $tblGroupAll = Group::useService()->getGroupAllByPerson($tblPerson);
            if (!empty($tblGroupAll)) {
                /** @var TblGroup $tblGroup */
                foreach ((array)$tblGroupAll as $tblGroup) {
                    $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                }
            }

            $Global->savePost();
        }

        return $this->getEditBasicTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditBasicForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param bool $isCreatePerson
     *
     * @return Title
     */
    private function getEditBasicTitle(TblPerson $tblPerson = null, $isCreatePerson = false)
    {
        return new Title(new PersonParent() . ' ' . self::TITLE, 'der Person '
            . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '')
            . ($isCreatePerson ? ' anlegen' : ' bearbeiten'));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditBasicForm(TblPerson $tblPerson = null)
    {

        return new Form(
            new FormGroup(array(
                $this->getBasicFormRow(false),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveBasicContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelBasicContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        );
    }

    /**
     * @param bool $isCreate
     * @return FormRow
     */
    private function getBasicFormRow($isCreate)
    {
        $tblSalutationAll = Person::useService()->getSalutationAll();

        $tblGroupList = Group::useService()->getGroupAllSorted();
        if ($tblGroupList) {
            // Create CheckBoxes
            $tabIndex = 8;
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupList, function (TblGroup &$tblGroup) use (&$tabIndex) {

                switch (strtoupper($tblGroup->getMetaTable())) {
                    case 'COMMON':
                        $Global = $this->getGlobal();
                        $Global->POST['Person']['Group'][$tblGroup->getId()] = $tblGroup->getId();
                        $Global->savePost();
                        $tblGroup = new RadioBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription())),
                            $tblGroup->getId()
                        );
                        break;
                    default:
                        $tblGroup = (new CheckBox(
                            'Person[Group][' . $tblGroup->getId() . ']',
                            $tblGroup->getName() . ' ' . new Muted(new Small($tblGroup->getDescription(true))),
                            $tblGroup->getId()
                        ))->setTabIndex($tabIndex++);
                }
            });
        } else {
            $tblGroupList = array(new Warning('Keine Gruppen vorhanden'));
        }

        $firstNameInput = (new TextField('Person[FirstName]', 'Vorname', 'Vorname'))->setRequired()
            ->setTabIndex(2);
        $lastNameInput = (new TextField('Person[LastName]', 'Nachname', 'Nachname'))->setRequired()
            ->setTabIndex(3);
        $salutationSelectBox = (new SelectBox('Person[Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
            new Conversation()))->setTabIndex(1);
        if ($isCreate) {
            $firstNameInput->ajaxPipelineOnKeyUp(ApiPersonEdit::pipelineLoadSimilarPersonContent());
            $lastNameInput->ajaxPipelineOnKeyUp(ApiPersonEdit::pipelineLoadSimilarPersonContent());
            $salutationSelectBox->ajaxPipelineOnChange(ApiPersonEdit::pipelineChangeSelectedGender());
        }

        return new FormRow(array(
            new FormColumn(
                new Panel('Anrede', array(
                    $salutationSelectBox,
                    (new AutoCompleter('Person[Title]', 'Titel', 'Titel', array('Dipl.- Ing.'),
                        new Conversation()))->setTabIndex(4),
                ), Panel::PANEL_TYPE_INFO), 2),
            new FormColumn(
                new Panel('Vorname', array(
                    $firstNameInput,
                    (new TextField('Person[SecondName]', 'weitere Vornamen',
                        'Zweiter Vorname'))->setTabIndex(5),
                    (new TextField('Person[CallName]', 'Rufname', 'Rufname'))->setTabIndex(6),
                ), Panel::PANEL_TYPE_INFO), 3),
            new FormColumn(
                new Panel('Nachname', array(
                    $lastNameInput,
                    (new TextField('Person[BirthName]', 'Geburtsname', 'Geburtsname'))->setTabIndex(7),
                ), Panel::PANEL_TYPE_INFO), 3),
            new FormColumn(
                new Panel('Gruppen', $tblGroupList, Panel::PANEL_TYPE_INFO)
                , 4),
        ));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param $Person
     *
     * @return bool|string
     */
    public function checkInputBasicContent(TblPerson $tblPerson = null, $Person)
    {
        $error = false;
        $form = $this->getEditBasicForm($tblPerson ? $tblPerson : null);
        if (isset($Person['FirstName'] ) && empty($Person['FirstName'] )) {
            $form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $error = true;
        }
        if (isset($Person['LastName'] ) && empty($Person['LastName'] )) {
            $form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $error = true;
        }

        if ($error) {
            return $this->getEditBasicTitle($tblPerson ? $tblPerson : null)
                . new Well($form);
        }

        return $error;
    }

    /**
     * @param $Person
     *
     * @return bool|Well
     */
    public function checkInputCreatePersonContent($Person)
    {
        $error = false;
        $form = $this->getCreatePersonForm();
        if (isset($Person['FirstName'] ) && empty($Person['FirstName'] )) {
            $form->setError('Person[FirstName]', 'Bitte geben Sie einen Vornamen an');
            $error = true;
        }
        if (isset($Person['LastName'] ) && empty($Person['LastName'] )) {
            $form->setError('Person[LastName]', 'Bitte geben Sie einen Nachnamen an');
            $error = true;
        }

        if ($error) {
            return new Well($form);
        }

        return $error;
    }
}