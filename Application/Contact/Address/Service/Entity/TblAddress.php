<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Address as LayoutAddress;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblAddress")
 * @Cache(usage="READ_ONLY")
 */
class TblAddress extends Element
{

    // GuiStringOrder (Consumer Setting)
    const VALUE_PLZ_ORT_OT_STR_NR = '1';
    const VALUE_OT_STR_NR_PLZ_ORT = '2';

    const ATTR_STREET_NAME = 'StreetName';
    const ATTR_STREET_NUMBER = 'StreetNumber';
    const ATTR_POST_OFFICE_BOX = 'PostOfficeBox';
    const ATTR_TBL_CITY = 'tblCity';
    const ATTR_TBL_STATE = 'tblState';
    const ATTR_COUNTY = 'County';
    const ATTR_NATION = 'Nation';

    /**
     * @Column(type="string")
     */
    protected $StreetName;
    /**
     * @Column(type="string")
     */
    protected $StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $PostOfficeBox;
    /**
     * @Column(type="bigint")
     */
    protected $tblCity;
    /**
     * @Column(type="string")
     */
    protected $County;
    /**
     * @Column(type="bigint")
     */
    protected $tblState;
    /**
     * @Column(type="string")
     */
    protected $Nation;

    /**
     * @return string
     */
    public function getPostOfficeBox()
    {

        return $this->PostOfficeBox;
    }

    /**
     * @param string $PostOfficeBox
     */
    public function setPostOfficeBox($PostOfficeBox)
    {

        $this->PostOfficeBox = $PostOfficeBox;
    }

    /**
     * @return LayoutAddress
     */
    public function getGuiLayout()
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Return = $Cache->getValue($this->getId(), __METHOD__))) {
            $Return = new LayoutAddress($this);
            $Cache->setValue($this->getId(), (string)$Return, 0, __METHOD__);
        }
        return $Return;
    }

    /**
     * @param bool $Extended (true = with Location (Country, State, Nation))
     *
     * @return string
     */
    public function getGuiString($Extended = true)
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Return = $Cache->getValue($this->getId(), __METHOD__))) {

            // 0 as Default
            $Value = '0';
            $tblSetting = Consumer::useService()->getSetting('Contact', 'Address', 'Address', 'Format_GuiString');
            if ($tblSetting) {
                $Value = $tblSetting->getValue();
            }
            switch ($Value) {
                case $this::VALUE_PLZ_ORT_OT_STR_NR:
                    $Return =
                        $this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()).',' : ',')
                        .' '.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .($Extended
                            ? ($this->getLocation() ? ' ('.$this->getLocation().')' : '')
                            : ''
                        );
                    break;
                case $this::VALUE_OT_STR_NR_PLZ_ORT:
                    $Return =
                        ($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()) : '')
                        .' '.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .', '.$this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($Extended
                            ? ($this->getLocation() ? ' ('.$this->getLocation().')' : '')
                            : ''
                        );
                    break;
                default:
                    $Return =
                        $this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()).',' : ',')
                        .' '.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .($this->getLocation() ? ' ('.$this->getLocation().')' : '');
            }
            $Cache->setValue($this->getId(), $Return, 0, __METHOD__);
        }
        return $Return;
    }

    /**
     * @param bool $Extended
     *
     * @return string
     */
    public function getGuiTwoRowString($Extended = true)
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Return = $Cache->getValue($this->getId(), __METHOD__))) {

            // 0 as Default
            $Value = '0';
            $tblSetting = Consumer::useService()->getSetting('Contact', 'Address', 'Address', 'Format_GuiString');
            if ($tblSetting) {
                $Value = $tblSetting->getValue();
            }
            switch ($Value) {
                case $this::VALUE_PLZ_ORT_OT_STR_NR:
                    $Return =
                        $this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()) : '')
                        .',<br>'.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .($Extended
                            ? ($this->getLocation() ? ' ('.$this->getLocation().')' : '')
                            : ''
                        );
                    break;
                case $this::VALUE_OT_STR_NR_PLZ_ORT:
                    $Return =
                        ($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()) : '')
                        .' '.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .',<br>'.$this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($Extended
                            ? ($this->getLocation() ? ' ('.$this->getLocation().')' : '')
                            : ''
                        );
                    break;
                default:
                    $Return =
                        $this->getTblCity()->getCode()
                        .' '.$this->getTblCity()->getName()
                        .($this->getTblCity()->getDisplayDistrict() !== '' ? ' '.($this->getTblCity()->getDisplayDistrict()) : '')
                        .',<br>'.$this->getStreetName()
                        .' '.$this->getStreetNumber()
                        .($this->getLocation() ? ' ('.$this->getLocation().')' : '');
            }

            $Cache->setValue($this->getId(), $Return, 0, __METHOD__);
        }
        return $Return;
    }

    /**
     * @return string
     */
    public function getStreetName()
    {

        return $this->StreetName;
    }

    /**
     * @param string $StreetName
     */
    public function setStreetName($StreetName)
    {

        $this->StreetName = $StreetName;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {

        return $this->StreetNumber;
    }

    /**
     * @param string $StreetNumber
     */
    public function setStreetNumber($StreetNumber)
    {

        $this->StreetNumber = $StreetNumber;
    }

    /**
     * @return bool|TblCity
     */
    public function getTblCity()
    {

        if (null === $this->tblCity) {
            return false;
        } else {
            return Address::useService()->getCityById($this->tblCity);
        }
    }

    /**
     * @param null|TblCity $tblCity
     */
    public function setTblCity(TblCity $tblCity = null)
    {

        $this->tblCity = (null === $tblCity ? null : $tblCity->getId());
    }

    /**
     * @return bool|TblState
     */
    public function getTblState()
    {

        if (null === $this->tblState) {
            return false;
        } else {
            return Address::useService()->getStateById($this->tblState);
        }
    }

    /**
     * @param null|TblState $tblState
     */
    public function setTblState(TblState $tblState = null)
    {

        $this->tblState = (null === $tblState ? null : $tblState->getId());
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->County;
    }

    /**
     * @param mixed $County
     */
    public function setCounty($County)
    {
        $this->County = trim($County);
    }

    /**
     * @return mixed
     */
    public function getNation()
    {
        return $this->Nation;
    }

    /**
     * @param mixed $Nation
     */
    public function setNation($Nation)
    {
        $this->Nation = trim($Nation);
    }

    /**
     * @return bool|string
     */
    public function getLocation()
    {
        $result = array();
        if ($this->County !== '') {
            $result[] = $this->County;
        }
        if ($this->getTblState()) {
            $result[] = $this->getTblState()->getName();
        }
        if ($this->Nation !== '') {
            $result[] = $this->Nation;
        }

        return empty($result) ? '' : implode(', ', $result);
    }
}
