<?php
namespace SPHERE\System\Database\Filter\Preparation;

/**
 * Class Address
 * @package SPHERE\System\Database\Filter\Preparation
 */
class Address extends AbstractPreparation
{

    /**
     * Address constructor.
     *
     * @param int $Id
     */
    public function __construct($Id)
    {

        $tblAddress = \SPHERE\Application\Contact\Address\Address::useService()->getAddressById($Id);

        $this->setPropertyList('Id', $tblAddress->getId());

        $this->setPropertyList('StreetName', $tblAddress->getStreetName());
        $this->setPropertyList('StreetNumber', $tblAddress->getStreetNumber());

        $tblCity = $tblAddress->getTblCity();

        $this->setPropertyList('CityName', $tblCity->getName());
        $this->setPropertyList('CityCode', $tblCity->getCode());

        $tblState = $tblAddress->getTblState();

        $this->setPropertyList('StateName', $tblState->getName());
    }
}
