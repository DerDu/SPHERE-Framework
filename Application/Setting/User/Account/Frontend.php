<?php
namespace SPHERE\Application\Setting\User\Account;

use DateTime;
use SPHERE\Application\Api\Contact\ApiContactAddress;
use SPHERE\Application\Api\Setting\UserAccount\ApiUserAccount;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Mail;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\User\Account
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Übersicht', 'Accounts');

        $countUserAccountStudent = Account::useService()->countUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $countUserAccountCustody = Account::useService()->countUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $Sum = $countUserAccountStudent + $countUserAccountCustody;
        $Ratio = 0;
        $Empty = 100;
        if ($Sum) {
            $Ratio = 100 / $Sum;
            $Empty = 0;
        }


        $PanelLeft = new Panel('Account-Verteilung', array(
            (new ProgressBar($countUserAccountStudent * $Ratio, $countUserAccountCustody * $Ratio, $Empty, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING),
            'Anzahl der Schüler-Accounts: '.$countUserAccountStudent.'<span style="width: 40px; float: left; padding: 3px">'.
            (new ProgressBar(100, 0, 0, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING)
            .'</span>',
            'Anzahl der Sorgeberechtigten-Accounts: '.$countUserAccountCustody.'<span style="width: 40px; float: left; padding: 3px">'.
            (new ProgressBar(0, 100, 0, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING)
            .'</span>',
        ));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelLeft
                            , 6)
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     *
     * @return Stage
     * @throws \Exception
     */
    public function frontendStudentAdd($Person = null, $Year = null, $Division = null)
    {

        $Stage = new Stage('Schüler-Accounts', 'Erstellen');

        $form = $this->getStudentFilterForm();

        $Result = $this->getStudentFilterResult($Person, $Year, $Division);
        $MaxResult = 800;
        $TableContent = $this->getStudentTableContent($Result, $MaxResult);

        // erlaubte Schularten:
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
        $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
        if($tblSchoolTypeList){
            // erzeuge eine Id Liste, wenn Schularten erlaubt werden
            foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                $tblSchoolTypeControl = $tblSchoolTypeControl->getName();
            }
        }

        $Table = new TableData($TableContent, null, array(
            'Check'         => 'Auswahl',
            'Name'          => 'Name',
            'StudentNumber' => 'Schüler-Nr.',
            'Course'        => 'Schulart',
            'Division'      => 'Klasse',
            'Address'       => 'Adresse',
            'Option'        => '',
        ),
            array(
                'order'      => array(array(5, 'asc')),
                'columnDefs' => array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
//                    array('width' => '1%', 'targets' => 0), //ToDO handle changing with
//                    array('width' => '1%', 'targets' => -1),
                ),
                'pageLength' => -1,
                'paging'     => false,
//                'info'       => false,
                'searching'  => false,
                'responsive' => false,
            )
//            false
        );

        //get ErrorMessage by Filter
        $formResult = new Form(new FormGroup(new FormRow(new FormColumn(
            (isset($Year[ViewYear::TBL_YEAR_ID]) && $Year[ViewYear::TBL_YEAR_ID] != 0
                ? new WarningMessage(new Container('Filterung findet keine Personen (ohne Account)')
                . ($tblSchoolTypeList
                    ? new Container('Folgende Schularten werden in den Einstellungen erlaubt: '.implode(', ', $tblSchoolTypeList))
                    : ''))
                : new WarningMessage('Die Filterung benötigt ein Schuljahr'))
        ))));
        if (!empty($TableContent)) {
            $formResult = (new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn($Table),
                        new FormColumn((new PrimaryLink('Benutzerkonten anlegen', ApiContactAddress::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiUserAccount::pipelineSaveAccount('S'))
                        )
                    ))
                )
            ))
//                ->appendFormButton((new Primary('Speichern', new Save())))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            $form
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ApiContactAddress::receiverModal()
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            (count($TableContent) >= $MaxResult
                                ? new WarningMessage(new WarningIcon().' Maximalanzahl der Personen erreicht.
                                Die Filterung ist nicht komplett!')
                                : ''
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ApiUserAccount::receiverAccountModal()
                            .new Panel('Filterung', array(
                                (!empty($TableContent) ? new ToggleCheckbox('Alle wählen/abwählen', $Table) : ''),
                                $formResult
//                                Account::useService()->createAccount($formResult, $PersonIdArray, 'S')
                            ))
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function getStudentFilterForm()
    {
        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
//                    new FormColumn(
//                        new Panel('Person', array(
//                            new TextField('Person['.ViewPerson::TBL_PERSON_FIRST_NAME.']', '', 'Vorname'),
//                            new TextField('Person['.ViewPerson::TBL_PERSON_LAST_NAME.']', '', 'Nachname')
//                        ), Panel::PANEL_TYPE_INFO)
//                        , 4),
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList)),
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),

                    new FormColumn(
                        new Panel('Filter-Information', new Info('Das Filterlimit beträgt 800 Personen')
                            .new Info('Es werden nur Personen ohne Account abgebildet')
                            , Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(
                    new FormColumn(
                        new Primary('Filtern')
                    )
                )
            ))
//            , new Primary('Filtern')
        );
    }

    /**
     * @param array $Person
     * @param array $Year
     * @param array $Division
     *
     * @return array
     */
    private function getStudentFilterResult($Person, $Year, $Division)
    {

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = array();

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter $FilterYear
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $FilterYear = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterPerson
            if (isset($Person) && $Person) {
                array_walk($Person, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterPerson = array_filter($Person);
            } else {
                $FilterPerson = array();
            }
            // Preparation $FilterDivision
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterDivision = array_filter($Division);
            } else {
                $FilterDivision = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => $FilterPerson,
                2 => $FilterDivision,
                3 => $FilterYear
            ));
        }

        return $Result;
    }

    /**
     * @param array $Result
     * @param int   $MaxResult
     *
     * @return array
     * @throws \Exception
     */
    public function getStudentTableContent($Result, $MaxResult = 800)
    {

        $SearchResult = array();
        if (!empty($Result)) {

            // erlaubte Schularten:
            $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
            $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
            if($tblSchoolTypeList){
                // erzeuge eine Id Liste, wenn Schularten erlaubt werden
                foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                    $tblSchoolTypeControl = $tblSchoolTypeControl->getId();
                }
            }

            $countRow = 0;
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                // ignor Person with existing Accounts (By Person)
                if ($tblPerson && !AccountAuthorization::useService()->getAccountAllByPerson($tblPerson)) {
                    $DataPerson['Name'] = false;
                    $DataPerson['Check'] = '';
                    $DataPerson['Course'] = '';
                    $DataPerson['Course'] = '';
                    $DataPerson['Address'] = $this->apiChangeMainAddressField($tblPerson);
                    $DataPerson['Option'] = $this->apiChangeMainAddressButton($tblPerson);

                    if ($tblPerson) {
                        $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                            $tblPerson->getId()
                            , array($tblPerson->getId())))->setChecked();
                        $DataPerson['Name'] = $tblPerson->getLastFirstName();
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if ($tblStudent) {
                            $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                            if ($tblTransferType) {
                                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                    $tblTransferType);
                                if ($tblStudentTransfer && ($tblCourse = $tblStudentTransfer->getServiceTblCourse())) {
                                    $DataPerson['Course'] = $tblCourse->getName();
                                }
                            }
                        }
                    }
                    $DataPerson['Division'] = '';
                    $DataPerson['Level'] = '';

                    $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                    if ($tblDivision) {
                        $DataPerson['Division'] = $tblDivision->getDisplayName();
                        // Schüler, die sich nicht in erlaubte Schularten befinden sollen übersprungen werden (wenn es diese Einstellung gibt)
                        if($tblSchoolTypeList && ($tblLevel = $tblDivision->getTblLevel())){
                            if(($tblType = $tblLevel->getServiceTblType()) && !in_array($tblType->getId(), $tblSchoolTypeList)){
                                // Schüler Überspringen
                                continue;
                            }
                        }
                    }
                    $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                    if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                        $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }

                    if (!isset($DataPerson['ProspectYear'])) {
                        $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                    }
                    if (!isset($DataPerson['ProspectDivision'])) {
                        $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                    }


                    // ignore duplicated Person
                    if ($DataPerson['Name']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            if ($countRow >= $MaxResult) {
                                break;
                            }
                            $countRow++;
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return \SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver
     */
    public function apiChangeMainAddressField(TblPerson $tblPerson)
    {
        $Content = ApiContactAddress::receiverColumn($tblPerson->getId());
        return $Content;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string|Standard
     */
    public function apiChangeMainAddressButton(TblPerson $tblPerson)
    {
        $Button = (new Standard('', ApiContactAddress::getEndpoint(), new Edit(), array(),
            'Bearbeiten der Hauptadresse'))
            ->ajaxPipelineOnClick(ApiContactAddress::pipelineOpen($tblPerson->getId()));

        return $Button;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $TypeId
     *
     * @return Stage
     */
    public function frontendCustodyAdd($Person = null, $Year = null, $Division = null, $TypeId = null)
    {
        $Stage = new Stage('Sorgeberechtigten-Accounts', 'Erstellen');

        $form = $this->getCustodyFilterForm();

        $Result = $this->getStudentFilterResult($Person, $Year, $Division);
        $MaxResult = 800;
        $TableContent = $this->getCustodyTableContent($Result, $MaxResult, $TypeId);

        // erlaubte Schularten:
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
        $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
        if($tblSchoolTypeList){
            // erzeuge eine Id Liste, wenn Schularten erlaubt werden
            foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                $tblSchoolTypeControl = $tblSchoolTypeControl->getName();
            }
        }

        $Table = new TableData($TableContent, null, array(
            'Check'   => 'Auswahl',
            'Name'    => 'Name',
            'Type'    => 'Beziehung-Typ',
            'Address' => 'Adresse',
            'Option'  => '',
        ),
            array(
                'order'      => array(array(2, 'asc')),
                'columnDefs' => array(
                    array('type' => 'german-string', 'targets' => 1),
//                    array('width' => '1%', 'targets' => 0), //ToDO handle changing with
//                    array('width' => '1%', 'targets' => -1),
                ),
                'pageLength' => -1,
                'paging'     => false,
//                'info'       => false,
                'searching'  => false,
                'responsive' => false,
            )
//            false
        );

        //get ErrorMessage by Filter
        $formResult = new Form(new FormGroup(new FormRow(new FormColumn(
            (isset($Year[ViewYear::TBL_YEAR_ID]) && $Year[ViewYear::TBL_YEAR_ID] != 0
                ? new WarningMessage(new Container('Filterung findet keine Personen (ohne Account)')
                    .($tblSchoolTypeList
                        ? new Container('Folgende Schularten werden in den Einstellungen erlaubt: '.implode(', ', $tblSchoolTypeList))
                        : ''))
                : new WarningMessage('Die Filterung benötigt ein Schuljahr'))
        ))));
        if (!empty($TableContent)) {
            $formResult = (new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn($Table),
                        new FormColumn((new PrimaryLink('Benutzerkonten anlegen', ApiContactAddress::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiUserAccount::pipelineSaveAccount('C'))
                        )
                    ))
                )
            ))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            $form
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ApiContactAddress::receiverModal()
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            (count($TableContent) >= $MaxResult
                                ? new WarningMessage(new WarningIcon().' Maximalanzahl der Personen erreicht.
                                Die Filterung ist nicht komplett!')
                                : ''
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ApiUserAccount::receiverAccountModal()
                            .new Panel('Filterung', array(
                                (!empty($TableContent) ? new ToggleCheckbox('Alle wählen/abwählen', $Table) : ''),
                                $formResult
                            ))
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function getCustodyFilterForm()
    {
        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }
        $TypeList = $this->getRelationshipList();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
//                    new FormColumn(
//                        new Success('')
//                        , 4),
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Klasse', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList)),
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Beziehungstyp', array(
                            new SelectBox('TypeId', 'Beziehungstyp',
                                array('{{ Name }}' => $TypeList))
                        ), Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Filter-Information', new Info('Das Filterlimit beträgt 800 Personen')
                            .new Info('Es werden nur Personen ohne Account abgebildet')
                            , Panel::PANEL_TYPE_INFO)
                        , 3),
                )),
                new FormRow(
                    new FormColumn(
                        new Primary('Filtern')
                    )
                )
            ))
        );
    }

    /**
     * @return TblType[]
     */
    private function getRelationshipList()
    {

        $TypeList = array();
        $TypeNameList = array(
            TblType::IDENTIFIER_GUARDIAN,       // Sorgeberechtigt
            TblType::IDENTIFIER_AUTHORIZED,     // Bevollmächtigt
            TblType::IDENTIFIER_GUARDIAN_SHIP   // Vormund
        );
        foreach($TypeNameList as $TypeName){
            if(($tblType = Relationship::useService()->getTypeByName($TypeName))){
                $TypeList[] = $tblType;
            }
        }
        return $TypeList;
    }

    /**
     * @param array $Result
     * @param int   $MaxResult
     * @param null  $TypeId
     *
     * @return array
     */
    public function getCustodyTableContent($Result, $MaxResult = 800, $TypeId = null)
    {

        $SearchResult = array();
        if (!empty($Result)) {

            // erlaubte Schularten:
            $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
            $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
            if($tblSchoolTypeList){
                // erzeuge eine Id Liste, wenn Schularten erlaubt werden
                foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                    $tblSchoolTypeControl = $tblSchoolTypeControl->getId();
                }
            }

            if(($tblType = Relationship::useService()->getTypeById($TypeId))){
                $tblTypeList[] = $tblType;
            } else {
                $tblTypeList = $this->getRelationshipList();
            }

            $countRow = 0;
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {
                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
//                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPersonStudent = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                // Schüler, die sich nicht in erlaubte Schularten befinden sollen übersprungen werden (wenn es diese Einstellung gibt)
                $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                if ($tblSchoolTypeList && $tblDivision) {
                    // $DataPerson['Division'] = $tblDivision->getDisplayName();
                    if(($tblLevel = $tblDivision->getTblLevel())){
                        if(($tblTypeLevel = $tblLevel->getServiceTblType()) && !in_array($tblTypeLevel->getId(), $tblSchoolTypeList)){
                            // Schüler Überspringen
                            continue;
                        }
                    }
                }

                foreach($tblTypeList as $tblType) {
                    $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonStudent, $tblType);
                    if ($tblToPersonList){
                        foreach ($tblToPersonList as $tblToPerson) {
                            $tblPerson = $tblToPerson->getServiceTblPersonFrom();
                            if ($tblToPerson->getTblType() && $tblToPerson->getTblType()->getId() == $tblType->getId()){
                                $DataPerson['Name'] = false;
                                $DataPerson['Check'] = '';
                                $DataPerson['Type'] = $tblType->getName();

                                $DataPerson['Address'] = $this->apiChangeMainAddressField($tblPerson);
                                $DataPerson['Option'] = $this->apiChangeMainAddressButton($tblPerson);

                                if ($tblPerson){
                                    $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                                        $tblPerson->getId()
                                        , array($tblPerson->getId())))->setChecked();
                                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                                }

                                // ignor Person with existing Accounts (By Person)
                                if (!AccountAuthorization::useService()->getAccountAllByPerson($tblPerson)){
                                    // ignore duplicated Person
                                    if (!array_key_exists($tblPerson->getId(), $SearchResult)){
                                        if ($countRow >= $MaxResult){
                                            break;
                                        }
                                        $countRow++;
                                        $SearchResult[$tblPerson->getId()] = $DataPerson;
                                    }
                                }
                            }
                        }
                        if ($countRow >= $MaxResult){
                            break;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @return Stage
     */
    public function frontendStudentShow()
    {
        $Stage = new Stage('Schüler-Accounts', 'Übersicht');

        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));

        $tblUserAccountAll = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['Address'] = '';
                $Item['PersonListCustody'] = '';
                $Item['Division'] = new Muted('-NA-');
                $Item['PersonListStudent'] = '';
                $Item['GroupByTime'] = ($tblUserAccount->getAccountCreator()
                        ? ''.$tblUserAccount->getAccountCreator().' - '
                        : new Muted('-NA-  ')
                    ).$tblUserAccount->getGroupByTime('d.m.Y');
                $Item['LastUpdate'] = '';
                $Item['Option'] =
                    new Standard('', '/Setting/User/Account/Password/Generation', new Mail(),
                        array(
                            'Id'   => $tblUserAccount->getId(),
                            'Path' => '/Setting/User/Account/Student/Show'
                        )
                        , 'Neues Passwort generieren')
                    .new Standard('', '/Setting/User/Account/Reset', new Repeat(),
                        array(
                            'Id'   => $tblUserAccount->getId(),
                            'Path' => '/Setting/User/Account/Student/Show'
                        )
                        , 'Passwort zurücksetzen')
                    .new Standard('', '/Setting/User/Account/Destroy', new Remove(),
                        array('Id' => $tblUserAccount->getId()), 'Benutzer entfernen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {
                    $Item['Address'] = $this->apiChangeMainAddressField($tblPerson);
                    $Item['Option'] = $this->apiChangeMainAddressButton($tblPerson).$Item['Option'];

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    if(($tblDivision =  Student::useService()->getCurrentDivisionByPerson($tblPerson))){
                        $Item['Division'] = $tblDivision->getDisplayName();
                    }

                    $CustodyList = array();
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = new Container($tblPersonCustody->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($CustodyList)) {
                        $Item['PersonListCustody'] = implode($CustodyList);
                    }
                }


                if($tblUserAccount->getUpdateDate()){
                    $UpdateTypeString = '';
                    $UpdateTypeAcronym = '';
                    $UpdateType = $tblUserAccount->getUpdateType();
                    if($UpdateType == TblUserAccount::VALUE_UPDATE_TYPE_RESET){
                        $UpdateTypeAcronym = 'Z';
                        $UpdateTypeString = 'Zurückgesetzt';
                    } elseif($UpdateType == TblUserAccount::VALUE_UPDATE_TYPE_RENEW){
                        $UpdateTypeAcronym = 'G';
                        $UpdateTypeString = 'Neu Generiert';
                    }

                    $Updater = new Muted('-NA- ');
                    if($tblUserAccount->getAccountUpdater()){
                        $Updater = $tblUserAccount->getAccountUpdater();
                    }
                    $updateTime = $tblUserAccount->getUpdateDate('d.m.Y');
                    $Item['LastUpdate'] = new ToolTip($UpdateTypeAcronym.' '.$Updater.' '.$updateTime, $UpdateTypeString);
                }

                $Item['Option'] = '<div style="width: 155px">'.$Item['Option'].'</div>';

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiContactAddress::receiverModal()
                        ),
                        new LayoutColumn(
                            (!empty($TableContent)
                                ? new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'Address'           => 'Adresse',
                                        'PersonListCustody' => 'Sorgeberechtigte',
                                        'Division'          => 'aktuelle Klasse',
                                        'GroupByTime'       => new ToolTip('Erstellung '.new InfoIcon(),'Benutzer - Datum'),
                                        'LastUpdate'        => new ToolTip('Passwort bearbeitet '.new InfoIcon(), 'Art - Benutzer - Datum'),
                                        'Option'            => ''
                                    ), array(
                                        'order'      => array(array(1, 'asc')),
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('width' => '142px', 'orderable' => false, 'targets' => -1)
                                        )
                                    )
                                )
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.')
                            )
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendCustodyShow()
    {
        $Stage = new Stage('Sorgeberechtigten-Accounts', 'Übersicht');

        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));

        $tblUserAccountAll = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['UserPassword'] = '';
                $Item['Address'] = '';
                $Item['PersonListCustody'] = '';
                $Item['PersonListStudent'] = '';
                $Item['GroupByTime'] = ($tblUserAccount->getAccountCreator()
                        ? ''.$tblUserAccount->getAccountCreator().' - '
                        : new Muted('-NA-  ')
                    ).$tblUserAccount->getGroupByTime('d.m.Y');
                $Item['LastUpdate'] = '';
                $Item['Option'] =
                    new Standard('', '/Setting/User/Account/Password/Generation', new Mail(),
                        array(
                            'Id'   => $tblUserAccount->getId(),
                            'Path' => '/Setting/User/Account/Custody/Show',
                            'IsParent' => true
                        )
                        , 'Neues Passwort generieren')
                    .new Standard('', '/Setting/User/Account/Reset', new Repeat(),
                        array(
                            'Id'   => $tblUserAccount->getId(),
                            'Path' => '/Setting/User/Account/Custody/Show'
                        )
                        , 'Passwort zurücksetzen')
                    .new Standard('', '/Setting/User/Account/Destroy', new Remove(),
                        array('Id' => $tblUserAccount->getId()), 'Benutzer entfernen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {
                    $Item['Address'] = $this->apiChangeMainAddressField($tblPerson);
                    $Item['Option'] = $this->apiChangeMainAddressButton($tblPerson).$Item['Option'];

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $StudentList = array();
                    $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                        $tblRelationshipType);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = new Container($tblPersonStudent->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($StudentList)) {
                        $Item['PersonListStudent'] = implode($StudentList);
                    }
                }

                if($tblUserAccount->getUpdateDate()){
                    $UpdateTypeString = '';
                    $UpdateTypeAcronym = '';
                    $UpdateType = $tblUserAccount->getUpdateType();
                    if($UpdateType == TblUserAccount::VALUE_UPDATE_TYPE_RESET){
                        $UpdateTypeAcronym = 'Z';
                        $UpdateTypeString = 'Zurückgesetzt';
                    } elseif($UpdateType == TblUserAccount::VALUE_UPDATE_TYPE_RENEW){
                        $UpdateTypeAcronym = 'G';
                        $UpdateTypeString = 'Neu Generiert';
                    }

                    $Updater = new Muted('-NA- ');
                    if($tblUserAccount->getAccountUpdater()){
                        $Updater = $tblUserAccount->getAccountUpdater();
                    }
                    $updateTime = $tblUserAccount->getUpdateDate('d.m.Y');
                    $Item['LastUpdate'] = new ToolTip($UpdateTypeAcronym.' '.$Updater.' '.$updateTime, $UpdateTypeString);
                }

                $Item['Option'] = '<div style="width: 155px">'.$Item['Option'].'</div>';

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiContactAddress::receiverModal()
                        ),
                        new LayoutColumn(
                            (!empty($TableContent)
                                ? new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'Address'           => 'Adresse',
                                        'PersonListStudent' => 'Sorgeberechtigt für',
                                        'GroupByTime'       => new ToolTip('Erstellung '.new InfoIcon(),'Benutzer - Datum'),
                                        'LastUpdate'        => new ToolTip('Passwort bearbeitet '.new InfoIcon(), 'Art - Benutzer - Datum'),
                                        'Option'            => ''
                                    ), array(
                                        'order'      => array(array(1, 'asc')),
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('width' => '142px', 'orderable' => false, 'targets' => -1)
                                        )
                                    )
                                )
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.')
                            )
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null|string $Time
     *
     * @return Stage
     */
    public function frontendAccountExport($Time = null)
    {

        $Stage = new Stage('Account', 'Serienbrief Export');
        $Stage->setMessage('Neu erstellte Benutzerzugänge können auf dieser Seite als Excel-Datei für den 
            Serienbriefdruck heruntergeladen werden.'
            .new Container('Dabei enthalten sind Benutzername, das automatisch generierte Passwort, Name und 
            Adressdaten.'));

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $tblUserAccountList = Account::useService()->getGroupOfUserAccountList($tblUserAccountAll);
        $TableContent = array();
        if ($tblUserAccountList) {

            /** @var TblUserAccount[] $UserAccountList */
            array_walk($tblUserAccountList, function ($tblUserAccountList, $GroupByTime) use (&$TableContent, $Time) {
                /** @var TblUserAccount $tblUserAccountTarget */
                if (($tblUserAccountTarget = current($tblUserAccountList)) && $tblUserAccountTarget->getUserPassword()) {
                    // Last Download
                    if(!isset($LastDownload)){
                        $LastDownload = Account::useService()->getLastExport($tblUserAccountList);
                    }

                    // Success Entry if linked
                    if ($Time && $Time == $GroupByTime) {
                        $item['GroupByTime'] = new SuccessMessage(new Bold($GroupByTime).' Aktuell erstellte Benutzer');
                        $item['UserAccountCount'] = new SuccessMessage(count($tblUserAccountList));
                        $item['ExportInfo'] = new SuccessMessage('&nbsp;');
                        if ($tblUserAccountTarget->getExportDate()) {
                            $item['ExportInfo'] = new SuccessMessage($tblUserAccountTarget->getLastDownloadAccount()
                                .' ('.$tblUserAccountTarget->getExportDate().')');
                        }

                        if ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                            $item['AccountType'] = new SuccessMessage('Schüler-Accounts');
                        } elseif ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                            $item['AccountType'] = new SuccessMessage('Sorgeberechtigten-Accounts');
                        }
                    } else {
                        $item['GroupByTime'] = $GroupByTime;
                        $item['UserAccountCount'] = count($tblUserAccountList);
                        $item['ExportInfo'] = '';
                        if($LastDownload){
                            //ToDO better performance with Querybuilder
                            $tblLastUserAccountList = Account::useService()->getUserAccountByLastExport(new DateTime($GroupByTime), new DateTime($LastDownload));
                            if($tblLastUserAccountList && ($tblLastUserAccount = $tblLastUserAccountList[0])){
                                $item['ExportInfo'] = $tblLastUserAccount->getLastDownloadAccount()
                                    .' ('.$tblLastUserAccount->getExportDate().')';
                            }
                        }

                        if ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                            $item['AccountType'] = 'Schüler-Accounts';
                        } elseif ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                            $item['AccountType'] = 'Sorgeberechtigten-Accounts';
                        }
                    }

                    $PdfButton = '';
                    if($tblUserAccountTarget->getGroupByCount()){
                        $PdfButton = (new Standard('', ApiUserAccount::getEndpoint(), new Mail(), array()
                            , 'Download als PDF'
                        ))->ajaxPipelineOnClick(ApiUserAccount::pipelineShowLoad($GroupByTime));
                    }

                    $item['Option'] = new External('', '/Api/Setting/UserAccount/Download', new Download()
                            , array('GroupByTime' => $GroupByTime), 'Download als Excel')
                        .($PdfButton ? $PdfButton->setScrollDown(10000, 1000) : '')
                        .new Standard('', '/Setting/User/Account/Clear', new Remove(),
                            array('GroupByTime' => $GroupByTime),
                            'Entfernen der Klartext Passwörter und des damit verbundenem verfügbaren Download');

                    array_push($TableContent, $item);
                }
            });
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new DangerMessage(new InfoIcon().' Bitte löschen Sie nach der Erstellung bzw. Versand des Serienbriefes die Excel-Datei 
                        auf Ihrem PC und auch den Excel-Download auf dieser Seite in der Schulsoftware.')
                    ),
                    new LayoutColumn(
                        new TableData($TableContent, null
                            , array(
                                'GroupByTime'      => 'Erstellung am',
                                'UserAccountCount' => 'Anzahl Accounts',
                                'AccountType'      => 'Account Typ',
                                'ExportInfo'       => 'letzter Download',
                                'Option'           => '',
                            ),
                            array(
                                'columnDefs' => array(
                                    array('type' => 'de_date', 'targets' => 0),
                                )
                            )
                        )
                    ),
                    new LayoutColumn(
                        ApiUserAccount::receiverFilter()
                    )
                ))
            )
        ));

        return $Stage;
    }

    /**
     * @param null   $Id
     * @param string $Path
     * @param bool   $IsParent
     * @param null   $Data
     *
     * @return Stage|string
     */
    public function frontendPasswordGeneration($Id = null, $Path = '/Setting/User', $IsParent = false, $Data = null)
    {

        $Stage = new Stage('Account Passwort', 'neu generieren');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect($Path, Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if (!$tblAccount) {
                return $Stage->setContent(new WarningMessage('Account nicht vorhanden')
                    .new Redirect($Path, Redirect::TIMEOUT_ERROR));
            }
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', new WarningMessage('Person wurde nicht gefunden')
                            . new DangerMessage('Account ohne Person kann nicht angeschrieben werden.'))
                        .new Redirect($Path, Redirect::TIMEOUT_ERROR)
                    )
                )))));
            }

            $Stage->addButton(
                new Standard('Zurück', $Path, new ChevronLeft())
            );
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName()),
                                'Account: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Well(
                            Account::useService()->generatePdfControl(
                                $this->getPdfForm($tblPerson, $tblUserAccount, $IsParent), $tblUserAccount, $Data,
                                '\Api\Document\Standard\MultiPassword\Create')
                        ),
                    )
                ))))
            );
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect($Path, Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param TblPerson      $tblPerson
     * @param TblUserAccount $tblUserAccount
     * @param bool           $IsParent
     *
     * @return Form|Redirect
     */
    private function getPdfForm(TblPerson $tblPerson, TblUserAccount $tblUserAccount, $IsParent = false)
    {

//        $tblStudentCompanyId = false;
        $tblSchoolAll = School::useService()->getSchoolAll();
//        $tblSchoolAll = false;
        // use school if only one exist
        $tblCompany = false;
        $CompanyId = '';
        $CompanyName = '';
        $CompanyExtendedName = '';
        $CompanyDistrict = '';
        $CompanyStreet = '';
        $CompanyCity = '';
        $CompanyPLZCity = '';
        $CompanyPhone = '';
        $CompanyFax = '';
        $CompanyMail = '';
        $CompanyWeb = '';
        if($tblSchoolAll && count($tblSchoolAll) == 1){
            $tblCompany = $tblSchoolAll[0]->getServiceTblCompany();
        } elseif($tblSchoolAll && count($tblSchoolAll) > 1) {
            if($tblPerson){
                // get school from student
                $tblCompany = Account::useService()->getCompanySchoolByPerson($tblPerson, $IsParent);
            }
            // old method
//            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
//            if($tblStudent){
//                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
//                if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
//                    if(($tblTransferCompany = $tblStudentTransfer->getServiceTblCompany())){
//                        $tblCompany = $tblTransferCompany;
//                    }
//                }
//            }

            // display error if no option exist
//        } elseif(!$tblSchoolAll){
//            $Warning = new WarningMessage('Es sind keine Schulen in den Mandanteneinstellungen hinterlegt.
//            Um diese Funktionalität nutzen zu können ist dies zwingend erforderlich.');
        }
        if($tblCompany){
            $CompanyId = $tblCompany->getId();
            $CompanyName = $tblCompany->getName();
            $CompanyExtendedName = $tblCompany->getExtendedName();
            if(($tblCompanyAddress = Address::useService()->getAddressByCompany($tblCompany))){
                $CompanyStreet = $tblCompanyAddress->getStreetName().' '.$tblCompanyAddress->getStreetNumber();
                if(($tblCity = $tblCompanyAddress->getTblCity())){
                    $CompanyDistrict = $tblCity->getDistrict();
                    $CompanyPLZCity = $tblCity->getCode().' '.$tblCity->getName();
                    $CompanyCity = $tblCity->getName();
                }
            }
            if(($tblPhoneToCompanyList = Phone::useService()->getPhoneAllByCompany($tblCompany))){
                $tblPhone = false;
                $tblFax = false;
                foreach($tblPhoneToCompanyList as $tblPhoneToCompany){
                    if(($tblType = $tblPhoneToCompany->getTblType())
                        && $tblType->getName() == 'Geschäftlich'){
                        $tblPhone = $tblPhoneToCompany->getTblPhone();
                    }
                    if(($tblType = $tblPhoneToCompany->getTblType())
                        && $tblType->getName() == 'Fax'){
                        $tblFax = $tblPhoneToCompany->getTblPhone();
                    }
                }
                if($tblPhone){
                    $CompanyPhone = $tblPhone->getNumber();
                }
                if($tblFax){
                    $CompanyFax = $tblFax->getNumber();
                }
            }
            if(($tblMailToCompanyList = \SPHERE\Application\Contact\Mail\Mail::useService()->getMailAllByCompany($tblCompany))){
                $tblMail = false;
                foreach($tblMailToCompanyList as $tblMailToCompany){
                    if(($tblType = $tblMailToCompany->getTblType())
                        && $tblType->getName() == 'Geschäftlich'){
                        $tblMail = $tblMailToCompany->getTblMail();
                    }
                }
                if($tblMail){
                    $CompanyMail = $tblMail->getAddress();
                }
            }
            if(($tblWebToCompanyList = Web::useService()->getWebAllByCompany($tblCompany))){
                $tblWebToCompany = current($tblWebToCompanyList);
                if(($tblWeb = $tblWebToCompany->getTblWeb())){
                    $CompanyWeb = $tblWeb->getAddress();
                }
            }
        }

        if(!isset($Data)){
            $Global = $this->getGlobal();
            // HiddenField
            $Global->POST['Data']['PersonId'] = $tblPerson->getId();
            $Global->POST['Data']['UserAccountId'] = $tblUserAccount->getId();
            $Global->POST['Data']['IsParent'] = $IsParent;
            $Global->POST['Data']['CompanyId'] = $CompanyId;
            // School
            $Global->POST['Data']['CompanyName']= $CompanyName;
            $Global->POST['Data']['CompanyExtendedName'] = $CompanyExtendedName;
            $Global->POST['Data']['CompanyDistrict'] = $CompanyDistrict;
            $Global->POST['Data']['CompanyStreet'] = $CompanyStreet;
            $Global->POST['Data']['CompanyCity'] = $CompanyPLZCity;
            $Global->POST['Data']['Phone'] = $CompanyPhone;
            $Global->POST['Data']['Fax'] = $CompanyFax;
            $Global->POST['Data']['Mail'] = $CompanyMail;
            $Global->POST['Data']['Web'] = $CompanyWeb;
            // Signer
            $Global->POST['Data']['Date'] = (new DateTime())->format('d.m.Y');
            $Global->POST['Data']['Place'] = $CompanyCity;
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                        , 1),
                    new FormColumn(
                        new HiddenField('Data[UserAccountId]')
                        , 1),
                    new FormColumn(
                        new HiddenField('Data[IsParent]')
                        , 1),
//                    new FormColumn(
//                        new HiddenField('Data[CompanyId]')
//                    , 1),
                )),
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen Schule')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Name der Schule',array(
                            (new TextField('Data[CompanyName]', '', 'Name'))->setRequired(),
                            new TextField('Data[CompanyExtendedName]', '', 'Namenszusatz')
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Adressinformation der Schule',array(
                            new TextField('Data[CompanyDistrict]', '', 'Ortsteil'),
                            (new TextField('Data[CompanyStreet]', '', 'Straße'))->setRequired(),
                            (new TextField('Data[CompanyCity]', '', 'PLZ / Ort'))->setRequired(),
                        ),Panel::PANEL_TYPE_INFO)
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new TileBig().' Informationen Briefkontakt')
                        , 12)
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kontaktinformation',array(
                            (new TextField('Data[Phone]', '', 'Telefon'))->setRequired(),
                            new TextField('Data[Fax]', '', 'Fax'),
                        ),Panel::PANEL_TYPE_INFO), 4),
                    new FormColumn(
                        new Panel('Internet Präsenz',array(
                            (new TextField('Data[Mail]', '', 'E-Mail'))->setRequired(),
                            new TextField('Data[Web]', '', 'Internet')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Ort, Datum', array(
                            new TextField('Data[Place]', '', 'Ort'),
                                (new TextField('Data[Date]', '', 'Datum'))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
            )),
            new Primary('Download', null, true), '\Api\Document\Standard\PasswordChange\Create'
        );
    }

    /**
     * @param null   $Id
     * @param bool   $Confirm
     * @param string $Path
     *
     * @return Stage|string
     */
    public function frontendResetAccount($Id = null, $Confirm = false, $Path = '/Setting/User')
    {

        $Stage = new Stage('Account Passwort', 'zurücksetzen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect($Path, Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if (!$tblAccount) {
                return $Stage->setContent(new WarningMessage('Account nicht vorhanden')
                    .new Redirect($Path, Redirect::TIMEOUT_ERROR));
            }
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', new WarningMessage('Person wurde nicht gefunden')),
                        new Panel(new Question().' Das Passwort dieses Benutzers wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true, 'Path' => $Path)
                            )
                            .new Standard('Nein', $Path, new Disable())
                        )
                    )
                )))));
            }

            $Stage->addButton(
                new Standard('Zurück', $Path, new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName()),
                                'Benutzer: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Das Passwort dieses Benutzers wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true, 'Path' => $Path)
                            )
                            .new Standard('Nein', $Path, new Disable())
                        )
                    )))))
                );
            } else {
                $IsChanged = false;
                $Password = $tblUserAccount->getAccountPassword();
                // remove tblAccount
                if ($tblAccount && $Password) {
                    if (AccountAuthorization::useService()->resetPassword($Password, $tblAccount)) {
                        $IsChanged = true;
                    }
                    Account::useService()->changeUpdateDate($tblUserAccount, TblUserAccount::VALUE_UPDATE_TYPE_RESET);
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsChanged
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde Zurückgesetzt')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht Zurückgesetzt werden')
                            ),
                            new Redirect($Path, Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect($Path, Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyPrepare($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Benutzer', 'Löschen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                // remove prepare if person are deleted (without asking)
                if ($tblAccount) {
                    // remove tblAccount
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                Account::useService()->removeUserAccount($tblUserAccount);
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            array(
                                new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht'),
                                new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                            )
                        ))
                    )))
                );
                return $Stage;
            }
            //default
            $Route = '/Setting/User/Account/Student/Show';
            if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                $Route = '/Setting/User/Account/Student/Show';
            } elseif ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                $Route = '/Setting/User/Account/Custody/Show';
            }
            $Stage->addButton(
                new Standard('Zurück', $Route, new ChevronLeft())
            );
            if (!$Confirm) {
                $UserName = '';
                if($tblAccount){
                    $UserName = $tblAccount->getUserName();
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName()),
                                'Benutzer: '.new Bold($UserName)
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Diesen Benutzer wirklich löschen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', $Route, new Disable())
                        )
                    )))))
                );
            } else {
                $IsDestroy = false;
                // remove tblAccount
                if ($tblAccount) {
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                if (Account::useService()->removeUserAccount($tblUserAccount)) {
                    $IsDestroy = true;
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsDestroy
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht gelöscht werden')
                            ),
                            new Redirect($Route, Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param string $GroupByTime
     * @param bool   $Confirm
     *
     * @return Stage|string
     */
    public function clearPassword($GroupByTime, $Confirm = false)
    {
        $Stage = new Stage('Benutzer', 'Klartext Passwörter');
        if ($GroupByTime) {
            $GroupByTime = new DateTime($GroupByTime);
            $tblUserAccountList = Account::useService()->getUserAccountByTime($GroupByTime);
            if (!$tblUserAccountList) {
                return $Stage.new DangerMessage('Export nicht gefunden', new Ban())
                    .new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_ERROR);
            }
            $Stage->addButton(
                new Standard('Zurück', '/Setting/User/Account/Export', new ChevronLeft())
            );
            if (!$Confirm) {

                $AccountType = '';
                if (($tblUserAccount = current($tblUserAccountList))) {
                    if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                        $AccountType = 'Schüler';
                    }
                    if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                        $AccountType = 'Sorgeberechtigte';
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzer',
                            array(
                                'Anzahl: '.new Bold(count($tblUserAccountList)),
                                'Account Typ: '.new Bold($AccountType)
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Klartext-Passwörter dieser Accounts wirklich löschen?',
                            'Passwörter können hiernach nicht mehr exportiert werden!
                            Das zurücksetzen von Passwörtern ist weiterhin möglich.',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Clear', new Ok(),
                                array('GroupByTime' => $GroupByTime->format('d.m.Y H:i:s'), 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User/Account/Export', new Disable())
                        )
                    )))))
                );
            } else {
                $IsDestroy = false;
                // remove tblUserAccount
                if (Account::useService()->clearPassword($GroupByTime)) {
                    $IsDestroy = true;
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(
                        new LayoutColumn(array(
                            ($IsDestroy
                                ? new SuccessMessage(new SuccessIcon().' Der Klartext wurde gelöscht')
                                : new DangerMessage(new Ban().' Der Klartext konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_SUCCESS)
                        ))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(array(
                        new DangerMessage(new Ban().' Export nicht gefunden'),
                        new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_ERROR)
                    ))
                )))
            );
        }
        return $Stage;
    }
}