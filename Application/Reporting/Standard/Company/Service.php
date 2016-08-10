<?php

namespace SPHERE\Application\Reporting\Standard\Company;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Search\Group\Group;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Company
 */
class Service extends Extension
{

    /**
     * @param IFormInterface|null $Stage
     * @param null                $Select
     * @param                     $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getGroup(IFormInterface $Stage = null, $Select = null, $Redirect)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblGroup = Group::useService()->getGroupById($Select['Group']);
        if ($tblGroup){
            return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
                'GroupId' => $tblGroup->getId(),
            ));
        } else {
            $Stage->setError('Select[Group]', 'Bitte wählen Sie eine Gruppe aus');
            return $Stage;
        }
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCompany[]
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $groupList = Group::useService()->getCompanyAllByGroup($tblGroup);
        $count = 0;
        if ($groupList) {

            $groupList = $this->getSorter($groupList)->sortObjectBy('DisplayName');

            /** @var TblCompany $tblCompany */
            foreach ($groupList as $tblCompany) {

                $count++;
                $tblCompany->Number = $count;
                if (( $addressList = Address::useService()->getAddressAllByCompany($tblCompany) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                if ($address !== null) {
                    $tblCompany->StreetName = $address->getTblAddress()->getStreetName();
                    $tblCompany->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblCompany->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblCompany->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblCompany->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblCompany->StreetName = $tblCompany->StreetNumber = $tblCompany->Code = $tblCompany->City = '';
                    $tblCompany->Address = '';
                }

                $phoneList = Phone::useService()->getPhoneAllByCompany($tblCompany);

                $phoneArray = array();
                $mobilePhoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getDescription() === 'Festnetz') {
                            $phoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                        if ($phone->getTblType()->getDescription() === 'Mobil') {
                            $mobilePhoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                }
                if (count($phoneArray) >= 1) {
                    $tblCompany->PhoneNumber = implode(', ', $phoneArray);
                } else {
                    $tblCompany->PhoneNumber = '';
                }
                if (count($mobilePhoneArray) >= 1) {
                    $tblCompany->MobilPhoneNumber = implode(', ', $mobilePhoneArray);
                } else {
                    $tblCompany->MobilPhoneNumber = '';
                }
                $mailAddressList = Mail::useService()->getMailAllByCompany($tblCompany);
                $mailList = array();
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $mailList[] = $mailAddress->getTblMail()->getAddress();
                    }
                }

                if (count($mailList) >= 1) {
                    $tblCompany->Mail = $mailList[0];
                } else {
                    $tblCompany->Mail = '';
                }
            }
        }

        return $groupList;
    }

    /**
     * @param $groupList
     *
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createGroupListExcel($groupList)
    {

        if (!empty( $groupList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Zusatz");
            $export->setValue($export->getCell("3", "0"), "Beschreibung");
            $export->setValue($export->getCell("4", "0"), "Anschrift");
            $export->setValue($export->getCell("5", "0"), "Telefon Festnetz");
            $export->setValue($export->getCell("6", "0"), "Telefon Mobil");
            $export->setValue($export->getCell("7", "0"), "E-mail");

            $Row = 1;

            foreach ($groupList as $tblCompany) {

                $export->setValue($export->getCell("0", $Row), $tblCompany->Number);
                /** @var TblCompany $tblCompany */
                $export->setValue($export->getCell("1", $Row), $tblCompany->getName());
                $export->setValue($export->getCell("2", $Row), $tblCompany->getExtendedName());
                $export->setValue($export->getCell("3", $Row), $tblCompany->getDescription());
                /** @var $tblCompany */
                $export->setValue($export->getCell("4", $Row), $tblCompany->Address);
                $export->setValue($export->getCell("5", $Row), $tblCompany->PhoneNumber);
                $export->setValue($export->getCell("6", $Row), $tblCompany->MobilPhoneNumber);
                $export->setValue($export->getCell("7", $Row), $tblCompany->Mail);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
