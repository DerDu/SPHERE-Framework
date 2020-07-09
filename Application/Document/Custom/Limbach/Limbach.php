<?php
namespace SPHERE\Application\Document\Custom\Limbach;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Limbach
 *
 * @package SPHERE\Application\Document\Standard\Limbach
 */
class Limbach extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schulvertrag'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSchoolContract'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillSchoolContract'
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
    public static function frontendSchoolContract()
    {

        $Stage = new Stage('Schulvertrag', 'Interessent auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $ReservationYear = '';
                    if(($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))){
                        if(($tblProspectReservation = $tblProspect->getTblProspectReservation())){
                            $ReservationYear = $tblProspectReservation->getReservationDivision();
                        }
                    }

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Reservation' => $ReservationYear,
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('PersonId' => $tblPerson->getId()))
                    );
                }
            }
        }

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
                                    'Reservation' => 'für Klassenstufe',
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
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
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendFillSchoolContract($PersonId = null)
    {

        $Stage = new Stage('Schulvertrag', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Custom/Limbach', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Gender = false;
        if ($tblPerson) {
            $Global->POST['Data']['PersonId'] = $PersonId;
            $Global->POST['Data']['FirstLastName'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

            // Allgemeine Daten der Person
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $Gender = $tblCommonGender->getName();
                    }
                }
            }

            // Daten des Interessenten
            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
            if ($tblProspect) {
                if(($tblReservation = $tblProspect->getTblProspectReservation())){
                    if($tblReservation->getServiceTblTypeOptionA()){
                        $Global->POST['Data']['SchoolTypeId'] = $tblReservation->getServiceTblTypeOptionA()->getId();
                    }
                    $Global->POST['Data']['ReservationDivision'] = $tblReservation->getReservationDivision();
                    if($tblReservation->getReservationYear()){
                        $Global->POST['Data']['ReservationDate'] = '01.08.'.substr($tblReservation->getReservationYear(), 0, 4);
                    }
                }
            }

            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType);
            if($tblRelationshipList){
                foreach($tblRelationshipList as $tblRelationship){
                    $Ranking = $tblRelationship->getRanking();
                    $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                    $Global->POST['Data']['FirstNameCustody'.$Ranking] = $tblPersonCustody->getFirstName();
                    $Global->POST['Data']['LastNameCustody'.$Ranking] = $tblPersonCustody->getLastName();

                    // Hauptadresse der sorgeberechtigten Person
                    $tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody);
                    if ($tblAddressCustody) {
                        $Global->POST['Data']['AddressStreet'.$Ranking] = $tblAddressCustody->getStreetName().' '.$tblAddressCustody->getStreetNumber();
                        $tblCityCustody = $tblAddressCustody->getTblCity();
                        if ($tblCityCustody) {
                            $Global->POST['Data']['AddressPLZ'.$Ranking] = $tblCityCustody->getCode();
                            $Global->POST['Data']['AddressCity'.$Ranking] = $tblCityCustody->getName();
                            $Global->POST['Data']['AddressDistrict'.$Ranking] = $tblCityCustody->getDistrict();
                        }
                    }
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentDocument($Gender);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Schulvertrag herunterladen',
            'SPHERE\Application\Api\Document\Custom\Limbach\SchoolContract\Create',
            new Download(), array('Data' => array('empty')),
            'Schulvertrag herunterladen'));

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
                            new Title('Vorlage des Dokuments "Schulvertrag"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/SchulVertragFELS.png')
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
     * @param $Gender
     *
     * @return Form
     */
    private function formStudentDocument($Gender)
    {

        $SchoolTypeList = array(new TblType());
        $SchoolType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE);
        if($SchoolType){
            array_push($SchoolTypeList, $SchoolType);
        }
        $SchoolType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
        if($SchoolType){
            array_push($SchoolTypeList, $SchoolType);
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastName]', 'Vorname, Name',
                                                            'Vorname, Name '.
                                                            ($Gender == 'Männlich'
                                                                ? 'des Schülers'
                                                                : ($Gender == 'Weiblich'
                                                                    ? 'der Schülerin'
                                                                    : 'des Schülers/der Schülerin')
                                                            ))
                                                    , 12)
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geboren am',
                                                            'Geburtstag')
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[ReservationDate]', '01.08.XXXX',
                                                            'Vorraussichtlicher Schulbeginn')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[ReservationDivision]', 'Vorraussichtliche Klasse',
                                                            'Vorraussichtliche Klasse')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new SelectBox('Data[SchoolTypeId]', 'Schulart', array('{{ Name }}' => $SchoolTypeList))
                                                        , 4),
                                                ))
                                            ))
                                        )
                                    )),

                                    new LayoutColumn(
                                        new Title('Eltern/Personensorgeberechtigte')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup( array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Title('S1 - Sorgeberechtigte(r)')
                                                    , 5),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstNameCustody1]', 'Vorname', 'Vorname')
                                                    , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[LastNameCustody1]', 'Nachname', 'Nachname')
                                                    , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ1]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity1]', 'Ort',
                                                            'Ort')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet1]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        , 4),
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup( array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Title('S2 - Sorgeberechtigte(r)')
                                                        , 5),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstNameCustody2]', 'Vorname', 'Vorname')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[LastNameCustody2]', 'Nachname', 'Nachname')
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ2]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity2]', 'Ort',
                                                            'Ort')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet2]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        , 4),
                                                )),
                                            ))
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Custom\Limbach\SchoolContract\Create'
        );
    }
}