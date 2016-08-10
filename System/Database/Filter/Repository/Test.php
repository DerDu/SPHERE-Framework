<?php
namespace SPHERE\System\Database\Filter\Repository;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Test
 *
 * @package SPHERE\System\Database\Filter\Repository
 */
class Test
{

    /**
     * Test constructor.
     */
    public function __construct()
    {

        $Result = (new Pile())
            ->addPile(Protocol::useService(), new TblProtocol(), null, 'Id')
            ->searchPile(array(
                array(
                    'EntityTo'         => 'Trend',
                    'ProtocolDatabase' => 'Gradebook',
                    'ConsumerAcronym'  => 'demo',
//                    'AccountUsername' => 'Kauschke'
                    'EntityFrom'       => null
                )
            ));

        /*
                $Result = (new Pile())
                    ->addPile(Group::useService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson')
                    ->addPile(Person::useService(), new ViewPerson(), 'TblPerson_Id', 'TblPerson_Id')
                    ->addPile(Address::useService(), new ViewAddressToPerson(), 'TblToPerson_serviceTblPerson', 'TblToPerson_serviceTblPerson')
        //            ->addPile(Relationship::useService(), new ViewRelationshipToPerson(), 'TblToPerson_serviceTblPersonFrom',
        //                'TblToPerson_serviceTblPersonTo')
        //            ->addPile(Person::useService(), new ViewPerson(), 'Id', 'Id')
                    ->searchPile(array(
        //                0 => array(
        //                    'TblGroup_Name' => 'leh'
        //                ),
        //                array(),
        //                array(),
        //                array('FirstName' => 'Lehr'),
        //                array('tblType' => '1'),
        //                array(),
        //                array()
        //                0 => array(
        //                    'TblSalutation_Salutation' => 'f',
        //                    'TblPerson_FirstName' => 'Vorname',
        //                    'TblPerson_LastName' => 'Nachname'
        //                ),
        //                2 => array( 'TblSalutation_Salutation' => 'f'),
        //                4 => array(
        //                    'TblAddress_StreetName' => 'b',
        //                    'TblAddress_StreetNumber' => '4',
        //                    'TblCity_Name' => 'z'
        //                )
                    ));
        */
        array_walk($Result, function (&$Data) {

            array_walk($Data, function (Element &$Element) {

                $Element = $Element->__toArray();
                foreach ($Element as $Index => $Payload) {
                    if (preg_match('!^O:[0-9]+:"[a-z0-9\\\]+":.*?$!is', $Payload)) {
                        $Element[$Index] = unserialize($Payload);
                    }
                }
            });
        });

        Debugger::screenDump($Result);
    }
}
