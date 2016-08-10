<?php
namespace SPHERE\Application\People\Search\Filter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendSearch($Filter = null)
    {

        $Stage = new Stage('Suche', 'nach Eigenschaften');
        $Stage->addButton(new Backward());
        Group::useFrontend()->addGroupSearchStageButton($Stage);

        $Form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(new Panel('Gruppe', $this->createFilter(0, new ViewPeopleGroupMember())
                    , Panel::PANEL_TYPE_INFO)
                , 3),
            new FormColumn(new Panel('Person', $this->createFilter(1, new ViewPerson())
                    , Panel::PANEL_TYPE_INFO)
                , 3),
            new FormColumn(new Panel('Meta', $this->createFilter(2, new ViewPeopleMetaCommon())
                    , Panel::PANEL_TYPE_INFO)
                , 3),
            new FormColumn(new Panel('Address', $this->createFilter(3, new ViewAddressToPerson()),
                    Panel::PANEL_TYPE_INFO)
                , 3),
        ))), new Primary('Suchen'));

        if (!empty( $Filter )) {
            ksort($Filter);
            array_walk($Filter, function (&$Input) {

                array_walk($Input, function (&$String) {

                    if (!empty( $String )) {
                        $String = explode(' ', $String);
                    } else {
                        $String = false;
                    }
                });
                $Input = array_filter($Input);
            });
        }

        if (!empty( $Filter )) {
            $Result = (new Pile())
                ->addPile(
                    \SPHERE\Application\People\Group\Group::useService(), new ViewPeopleGroupMember(),
                    null, 'TblMember_serviceTblPerson'
                )
                ->addPile(
                    Person::useService(), new ViewPerson(),
                    'TblPerson_Id', 'TblPerson_Id'
                )
                ->addPile(
                    Common::useService(), new ViewPeopleMetaCommon(),
                    'TblCommon_serviceTblPerson', 'TblCommon_serviceTblPerson'
                )
                ->addPile(
                    Address::useService(), new ViewAddressToPerson(),
                    'TblToPerson_serviceTblPerson', null
                )
                ->searchPile(
                    $Filter
                );
            $Table = array();
            array_walk($Result, function ($Row) use (&$Table, $Filter) {

                $RowSet = array();
                array_walk($Row, function (AbstractView $Element) use (&$RowSet, $Filter) {

                    $RowSet = array_merge($RowSet, $Element->__toView());
                });
                array_push($Table, $RowSet);
            });
        } else {
            $Table = array();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well($Form)
                        )
                    )
                    , new Title('Suche')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new TableData($Table, null, array(
//                                'TblSalutation_Salutation'     => ViewPerson::convertPropertyNameToDisplay('TblSalutation_Salutation'),
//                                'TblPerson_FirstName'     => ViewPerson::convertPropertyNameToDisplay('TblPerson_FirstName'),
//                                'TblPerson_LastName'     => ViewPerson::convertPropertyNameToDisplay('TblPerson_LastName'),
//                                'TblGroup_Name'           => ViewPeopleGroupMember::convertPropertyNameToDisplay('TblGroup_Name'),
//                                'TblAddress_StreetName'   => ViewAddressToPerson::convertPropertyNameToDisplay('TblAddress_StreetName'),
//                                'TblAddress_StreetNumber' => ViewAddressToPerson::convertPropertyNameToDisplay('TblAddress_StreetNumber'),
//                                'TblCity_Code'            => ViewAddressToPerson::convertPropertyNameToDisplay('TblCity_Code'),
//                                'TblCity_Name'            => ViewAddressToPerson::convertPropertyNameToDisplay('TblCity_Name'),
                            ), false)
                        ))
                    )
                    , new Title('Ergebnis')),
            ))
        );

        return $Stage;
    }

    private function createFilter($Index, AbstractView $View)
    {

        $Result = array();
        $Object = new \ReflectionObject($View);
        $Properties = $Object->getProperties(\ReflectionProperty::IS_PROTECTED);
        /** @var \ReflectionProperty $Property */
        foreach ($Properties as $Property) {
            $Name = $Property->getName();
            if (!preg_match('!(_Id|_service|_tbl|Locked|MetaTable|^Id$|^Entity)!s', $Name)) {
                $Result[] = $this->createFilterField($Index, $Name, $View);
            }
        }
        return $Result;
    }

    private function createFilterField($PileIndex, $PropertyName, AbstractView $View)
    {

        return new TextField(
            'Filter['.$PileIndex.']['.$PropertyName.']',
            $View->getNameDefinition($PropertyName),
            $View->getNameDefinition($PropertyName)
        );
    }

    /**
     * @param array  $Payload
     * @param array  $Search
     * @param string $Name
     *
     * @return string
     */
    private function markFilter($Payload, $Search, $Name)
    {

        if (isset( $Search[$Name] )) {
            if (!empty( $Search[$Name] )) {
                array_walk($Search[$Name], function (&$Text) {

                    $Text = '!'.preg_quote(trim($Text), '!').'!is';
                });
                return preg_replace($Search[$Name], '<span style="background-color: yellow;">${0}</span>',
                    $Payload[$Name]);
            }
        }
        return $Payload[$Name];
    }
}
