<?php

namespace SPHERE\Application\Document\Standard\AccidentReport;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Sup;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class AccidentReport
 *
 * @package SPHERE\Application\Document\Standard\AccidentReport
 */
class AccidentReport extends Extension
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Unfallanzeige'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillAccidentReport'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Unfallanzeige', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$dataList) {
                    $Data['PersonId'] = $tblPerson->getId();

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('Id' => $tblPerson->getId()))
//                            .new External('Herunterladen',
//                                'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
//                                new Download(), array('Data' => $Data),
//                                'Schulbescheinigung herunterladen')
                    );
                });
            }
        }

        $YearString = '(SJ ';
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            $YearString .= current($tblYearList)->getYear();
        } else {
            $YearString .= new ToolTip(new Danger((new \DateTime())->format('Y')),
                'Kein Schuljahr mit aktuellem Zeitraum');
        }
        $YearString .= ')';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse '.$YearString,
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('width' => '1%', 'targets' => -1),
                                    ),
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendFillAccidentReport($Id = null)
    {

        $Stage = new Stage('Unfallanzeige', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/AccidentReport', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();
        $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Sachsen';
        $Global->POST['Data']['TargetAddressStreet'] = 'Postfach 42';
        $Global->POST['Data']['TargetAddressCity'] = '01651 Meißen';
        if ($tblPerson) {
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                    $Global->POST['Data']['Nationality'] = $tblCommonInformation->getNationality();
                }
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $tblCommonGender->getName();
                    }
                }
            }

            $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
            if ($tblResponsibilityList) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = current($tblResponsibilityList);
                $Global->POST['Data']['CompanyNumber'] = $tblResponsibility->getCompanyNumber();
                $tblResponsibilityCompany = $tblResponsibility->getServiceTblCompany();
                if ($tblResponsibilityCompany) {
                    $Global->POST['Data']['SchoolResponsibility'] = $tblResponsibilityCompany->getDisplayName();
                }
            }

            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                // Schuldaten der Schule des Schülers
                $tblCompanySchool = Student::useService()->getCurrentSchoolByPerson($tblPerson);
                $tblType = false;

                // Schulart über Klasse in der der Schüler aktuell ist
                $tblDivision = Student::useService()->getCurrentDivisionByPerson($tblPerson);
                if($tblDivision && ($tblLevel = $tblDivision->getTblLevel())){
                    $tblType = $tblLevel->getServiceTblType();
                }
                // Unternehmensnummer wird sofern möglich und vorhanden aus den Mandantenschulen gezogen
                // und überschreibt damit die Unternehmensnummer der Schulträger
                if($tblType){
                    // Schule aus Mandanteneinstellung mit Schulart
                    if(($tblSchoolList = School::useService()->getSchoolByType($tblType))){
                        // bei einer Schule kann diese genommen werden. (Normalfall)
                        if(count($tblSchoolList) == 1){
                            $tblSchool = current($tblSchoolList);
                            // Übernahme nur, wenn eine Unternehmensnummer hinterlegt ist
                            if($tblSchool->getCompanyNumber() != ''){
                                $Global->POST['Data']['CompanyNumber'] = $tblSchool->getCompanyNumber();
                            }
                        } else {
                            // mehr als eine Schule mit gleicher Schulart
                            if(($tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompanySchool, $tblType))){
                                if($tblSchool){
                                    // Übernahme nur, wenn eine Unternehmensnummer hinterlegt ist
                                    if($tblSchool->getCompanyNumber() != '') {
                                        $Global->POST['Data']['CompanyNumber'] = $tblSchool->getCompanyNumber();
                                    }
                                }
                            }
                        }
                    }
                }
                if ($tblCompanySchool) {
                    $Global->POST['Data']['School'] = $tblCompanySchool->getName();
                    $Global->POST['Data']['SchoolExtended'] = $tblCompanySchool->getExtendedName();
                    $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                    if ($tblAddressSchool) {
                        $Global->POST['Data']['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().', '.$tblAddressSchool->getStreetNumber();
                        $tblCitySchool = $tblAddressSchool->getTblCity();
                        if ($tblCitySchool) {
                            $Global->POST['Data']['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                        }
                    }

                    $tblToPersonList = Phone::useService()->getPhoneAllByCompany($tblCompanySchool);
                    $tblToPersonPhoneList = array();
                    $tblToPersonFaxList = array();
                    if ($tblToPersonList) {
                        foreach ($tblToPersonList as $tblToPerson) {
                            if ($tblType = $tblToPerson->getTblType()) {
                                $TypeName = $tblType->getName();
                                $TypeDescription = $tblType->getDescription();
                                if (($TypeName == 'Privat' || $TypeName == 'Geschäftlich') && $TypeDescription == 'Festnetz') {
                                    $tblToPersonPhoneList[] = $tblToPerson;
                                }
                                if ($TypeName == 'Fax') {
                                    $tblToPersonFaxList[] = $tblToPerson;
                                }
                            }
                        }
                        if (!empty($tblToPersonPhoneList)) {
                            /** @var TblToPersonPhone $tblPersonToPhone */
                            $tblPersonToPhone = current($tblToPersonPhoneList);
                            $tblPhone = $tblPersonToPhone->getTblPhone();
                            if ($tblPhone) {
                                $Global->POST['Data']['Phone'] = $tblPhone->getNumber();
                            }
                        }
                        if (!empty($tblToPersonFaxList)) {
                            /** @var TblToPersonPhone $tblPersonToFax */
                            $tblPersonToFax = current($tblToPersonFaxList);
                            $tblPhoneFax = $tblPersonToFax->getTblPhone();
                            if ($tblPhoneFax) {
                                $Global->POST['Data']['Fax'] = $tblPhoneFax->getNumber();
                            }
                        }
                    }
                }
                // Datum Aufnahme
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $EntryDate = $tblStudentTransfer->getTransferDate();
                    $Global->POST['Data']['SchoolEntry'] = $EntryDate;
                    if ($EntryDate != '') {
                        $tblYearList = Term::useService()->getYearAllByDate(new \DateTime($EntryDate));
                        if ($tblYearList) {
                            foreach ($tblYearList as $tblYear) {
                                $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                                if ($tblDivision && $tblDivision->getTblLevel()) {
                                    $Global->POST['Data']['SchoolEntryDivision'] = $tblDivision->getTblLevel()->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Hauptadresse Schüler
            $ChildAddressId = false;
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().', '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if ($tblCity) {
                    $Global->POST['Data']['AddressPLZ'] = $tblCity->getCode();
                    $Global->POST['Data']['AddressCity'] = $tblCity->getDisplayName();
                }
                $ChildAddressId = $tblAddress->getId();
            }

            // Sorgeberechtigte
            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblToPersonCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType);
            if ($tblToPersonCustodyList) {
                // female preferred (first run)
                $PersonCustodySameAddress = array();
                $PersonCustodyDifferentAddress = array();
                $AddressString = '';
                foreach ($tblToPersonCustodyList as $tblToPersonCustody) {
                    $tblPersonCustody = $tblToPersonCustody->getServiceTblPersonFrom();

                    $tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody);
                    if ($ChildAddressId
                        && $tblAddressCustody
                        && $ChildAddressId == $tblAddressCustody->getId()) {
                        $AddressString = $tblAddressCustody->getGuiString(false);
                        $PersonCustodySameAddress[] = $tblPersonCustody;
                        continue;
                    } else {
                        $PersonCustodyDifferentAddress[] = $tblPersonCustody;
                    }
                }
                if (!empty($PersonCustodySameAddress)) {
                    /** @var TblPerson $PersonCustody */
                    $ParentList = array();
                    foreach ($PersonCustodySameAddress as $PersonCustody) {
                        $ParentList[] = $PersonCustody->getSalutation().' '.
                            ($PersonCustody->getTitle()
                                ? $PersonCustody->getTitle().' '
                                : '')
                            .$PersonCustody->getFirstName().' '.$PersonCustody->getLastName();
                    }
                    if (!empty($ParentList)) {
                        $Global->POST['Data']['Custody'] = implode(', ', $ParentList);
                        $Global->POST['Data']['CustodyAddress'] = $AddressString;
                    }
                } elseif (!empty($PersonCustodyDifferentAddress)) {
                    foreach ($PersonCustodyDifferentAddress as $PersonCustody) {
                        /** @var TblCommon $tblCommonCustody */
                        if (($tblCommonCustody = $PersonCustody->getCommon())) {
                            if (($tblBirthdates = $tblCommonCustody->getTblCommonBirthDates())) {
                                if (($tblGenderCustody = $tblBirthdates->getTblCommonGender())) {
                                    if ($tblGenderCustody->getName() == 'Weiblich') {
                                        if (!isset($Global->POST['Data']['Custody'])) {
                                            $tblAddressCustody = Address::useService()->getAddressByPerson($PersonCustody);
                                            if ($tblAddressCustody) {
                                                $Global->POST['Data']['CustodyAddress'] = $tblAddressCustody->getGuiString();
                                            }
                                            $Global->POST['Data']['Custody'] = $PersonCustody->getSalutation().' '.
                                                ($PersonCustody->getTitle()
                                                    ? $PersonCustody->getTitle().' '
                                                    : '')
                                                .$PersonCustody->getFirstName().' '.$PersonCustody->getLastName();
                                        }
                                    }
                                }
                            }
                        }

                        if (!isset($Global->POST['Data']['Custody'])) {
                            $tblAddressCustody = Address::useService()->getAddressByPerson($PersonCustody);
                            if ($tblAddressCustody) {
                                $Global->POST['Data']['CustodyAddress'] = $tblAddressCustody->getGuiString();
                            }

                            $Global->POST['Data']['Custody'] = $PersonCustody->getSalutation().' '.
                                ($PersonCustody->getTitle()
                                    ? $PersonCustody->getTitle().' '
                                    : '')
                                .$PersonCustody->getFirstName().' '.$PersonCustody->getLastName();
                        }
                    }
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentTransfer();

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Unfallanzeige herunterladen',
            'SPHERE\Application\Api\Document\Standard\AccidentReport\Create',
            new Download(), array('Data' => array('empty')),
            'Unfallanzeige herunterladen'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                            , 7)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                            , 7),
                        new LayoutColumn(
                            new Title('Vorlage des Standard-Dokuments "Unfallanzeige"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/AccidentReport.PNG')
                                , ''
                            )
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formStudentTransfer()
    {
//        $Data[] = 'BohrEy';

        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School]', 'Schule',
                                                            new Sup(1).' Schule')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolExtended]', 'Zusatz',
                                                            new Sup(1).' Zusatz')
                                                        , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            new Sup(1).' Straße Hausnummer')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                                            new Sup(1).' PLZ Ort')
                                                        , 6)
                                                ))
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Träger')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolResponsibility]',
                                                            'Träger der Einrichtung',
                                                            new Sup(2).' Träger der Einrichtung')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[CompanyNumber]', 'Unternehmensnr.',
                                                            new Sup(3).' Unternehmensnr.')
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Empfänger')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressTarget]', 'Empfänger',
                                                            new Sup(4).' Empfänger')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[TargetAddressStreet]', 'Straße Hausnummer',
                                                            new Sup(4).' Straße Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[TargetAddressCity]', 'PLZ Ort',
                                                            new Sup(4).' PLZ Ort')
                                                        , 4)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Versicherter')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[LastFirstName]', 'Name, Vorname',
                                                            new Sup(5).' Name, Vorname des Schülers/der Schülerin')
                                                        , 8),
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geburtstag',
                                                            new Sup(6).' Geburtstag')
                                                        , 4),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                                            new Sup(7).' Straße, Hausnummer')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort',
                                                            'Ort')
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Bold(new Sup(8).' Geschlecht').
                                                        new Listing(array(
                                                            new RadioBox('Data[Gender]', 'Männlich',
                                                                'Männlich'),
                                                            new RadioBox('Data[Gender]', 'Weiblich',
                                                                'Weiblich')
                                                        ))
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[Nationality]', 'Staatsangehörigkeit',
                                                            new Sup(9).' Staatsangehörigkeit')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[Custody]', 'Vertreter',
                                                            new Sup(10).' Vertreter')
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        '&nbsp;'
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[CustodyAddress]', 'Str Nr. PLZ Ort',
                                                            new Sup(10).' Anschrift Vertreter')
                                                        , 6),
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Unfall')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new PullClear(new Bold(new Sup(11).' Tödlicher Unfall?')).
                                                        new PullLeft(new CheckBox('Data[DeathAccidentYes]',
                                                            'Ja &nbsp;&nbsp;&nbsp;&nbsp;', true)).
                                                        new PullLeft(new CheckBox('Data[DeathAccidentNo]',
                                                            'Nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            new Sup(12).' Datum des Unfalls')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentHour]', 'Stunde',
                                                            new Sup(12).' Stunde Unfallzeitpunkt')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentMinute]', 'Minute',
                                                            new Sup(12).' Minute Unfallzeitpunkt')
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentPlace]', 'Ort',
                                                            new Sup(13).' Unfallort')
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextArea('Data[AccidentDescription]', 'Beschreibung',
                                                            new Sup(14).' Ausführliche Beschreibung des Unfallhergangs')
                                                    )
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new PullLeft(new Header(new Bold(new Sup(14).' Die Angaben beruhen auf der Schilderung &nbsp;&nbsp;&nbsp;&nbsp;')))
                                                        .new Container(
                                                            new PullLeft(new CheckBox('Data[DescriptionActive]',
                                                                'des Versicherten &nbsp;&nbsp;&nbsp;&nbsp;'
                                                                , true))
                                                            .new PullLeft(new CheckBox('Data[DescriptionPassive]',
                                                                'andere Personen'
                                                                , true))
                                                        )
                                                    )
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentBodyParts]',
                                                            'Kopf, Bein, Arm, etc.',
                                                            new Sup(15).' Verletzte Körperteile')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[AccidentType]', 'Art',
                                                            new Sup(16).' Art der Verletzung')
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Bold(new Sup(17).' Hat der Versicherte den Besuch der Einrichtung unterbrochen?')
                                                        .new PullClear(
                                                            new PullLeft(new CheckBox('Data[BreakNo]',
                                                                'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                            .new PullLeft(new CheckBox('Data[BreakYes]',
                                                                'sofort &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                            .new PullLeft(new CheckBox('Data[BreakAt]',
                                                                'später am &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                        )
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[BreakDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            'Datum der Unterbrechung')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[BreakHour]', 'Stunde',
                                                            'Zeitpunkt der Unterbrechung')
                                                        , 3)
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Bold(new Sup(18).' Hat der Versicherte den Besuch der Einrichtung wieder aufgenommen?')
                                                        .new PullClear(
                                                            new PullLeft(new CheckBox('Data[ReturnNo]',
                                                                'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                            .new PullLeft(new CheckBox('Data[ReturnYes]',
                                                                'ja, am &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                        )
                                                        , 9),
                                                    new LayoutColumn(
                                                        new TextField('Data[ReturnDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            new Sup(18).' Datum der Wiederaufnahme')
                                                        , 3),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[WitnessInfo]', 'Name, Vorname Adresse',
                                                            new Sup(19).' Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift von Zeugen)')
                                                        , 9),
                                                    new LayoutColumn(
                                                        new Bold('War diese Person Augenzeuge?')
                                                        .new PullClear(
                                                            new PullLeft(new CheckBox('Data[EyeWitnessYes]',
                                                                'ja &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                            .new PullLeft(new CheckBox('Data[EyeWitnessNo]',
                                                                'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                                        )
                                                        , 3),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Doctor]', 'Name',
                                                            new Sup(20).' Name des erstbehandelnden Arztes / Krankenhaus')
                                                        .new TextField('Data[DoctorAddress]', 'Adresse',
                                                            new Sup(20).' Adresse des erstbehandelnden Arztes / Krankenhaus')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new Header(new Bold(new Sup(21).' Beginn des Besuchs der Einrichtung'))
                                                        .new TextField('Data[LocalStartHour]', 'Stunde', 'Stunde')
                                                        .new TextField('Data[LocalStartMinute]', 'Minute', 'Minute')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new Header(new Bold(new Sup(21).' Ende des Besuchs der Einrichtung'))
                                                        .new TextField('Data[LocalEndHour]', 'Stunde', 'Stunde')
                                                        .new TextField('Data[LocalEndMinute]', 'Minute', 'Minute')
                                                        , 3),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new Title('')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Date]', (new \DateTime())->format('d.m.Y'),
                                                            'Datum')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[LocalLeader]', 'Leiter',
                                                            'Leiter (Beauftragter) der Einrichtung')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[Recall]', 'Telefonnummer',
                                                            'Telefon-Nr. für Rückfragen (Ansprechpartner)')
                                                        , 5),
                                                )),
                                            ))
                                        )
                                    ))
                                ))
                            )
                        )
                    )
                ),
//                new FormRow(array(
//                    new FormColumn(
//                        ApiAccidentReport::receiverService(ApiAccidentReport::pipelineButtonRefresh($PersonId))
////                        (new Standard('PDF erzeugen', ApiAccidentReport::getEndpoint()))->ajaxPipelineOnClick(ApiAccidentReport::pipelineDownload($PersonId))
//                    )
//                ))
            ))
            , new Primary('Download', new Download(), true), '\Api\Document\Standard\AccidentReport\Create'
        );
    }
}