<?php

namespace SPHERE\Application\Reporting\Standard\Company;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Search\Group\Group;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Company
 */
class Service extends Extension
{

    /**
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $tblCompanyList = Group::useService()->getCompanyAllByGroup($tblGroup);
        $count = 0;
        $TableContent = array();
        if ($tblCompanyList) {
            $tblCompanyList = $this->getSorter($tblCompanyList)->sortObjectBy('DisplayName', new StringGermanOrderSorter());

            array_walk($tblCompanyList, function (TblCompany $tblCompany) use (&$TableContent, &$count) {
                $count++;
                $Item['Number'] = $count;
                $Item['Name'] = $tblCompany->getName();
                $Item['ExtendedName'] = $tblCompany->getExtendedName();
                $Item['Description'] = $tblCompany->getDescription();
                $Item['ContactPerson'] = '';
                $Item['StreetName'] = '';
                $Item['StreetNumber'] = '';
                $Item['Code'] = '';
                $Item['City'] = '';
                $Item['District'] = '';
                $Item['Address'] = '';
                $Item['PhoneNumber'] = '';
                $Item['MobilPhoneNumber'] = '';
                $Item['Mail'] = '';

                // address
                if (($tblAddress = Address::useService()->getAddressByCompany($tblCompany))) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                // phone
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
                    $Item['PhoneNumber'] = implode(', ', $phoneArray);
                }
                if (count($mobilePhoneArray) >= 1) {
                    $Item['MobilPhoneNumber'] = implode(', ', $mobilePhoneArray);
                }
                // mails
                $mailAddressList = Mail::useService()->getMailAllByCompany($tblCompany);
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $Item['Mail'] = $mailAddress->getTblMail()->getAddress();
                        break;
                    }
                }

                // contactPerson
                $PersonList = array();
                $tblRelationshipList = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany);
                if($tblRelationshipList){
                    foreach($tblRelationshipList as$tblRelationship){
                        if(($tblPerson = $tblRelationship->getServiceTblPerson())){
                            $PersonList[] = $tblPerson->getFullNameWithoutFirstName();
                        }
                    }
                }
                if(!empty($PersonList)){
                    $Item['ContactPerson'] = implode(', ', $PersonList);
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $companyList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createGroupListExcel($companyList)
    {

        if (!empty( $companyList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $column = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($column++, "0"), "lfd. Nr.");
            $export->setValue($export->getCell($column++, "0"), "Name");
            $export->setValue($export->getCell($column++, "0"), "Zusatz");
            $export->setValue($export->getCell($column++, "0"), "Beschreibung");
            $export->setValue($export->getCell($column++, "0"), "Ansprechpartner");
            $export->setValue($export->getCell($column++, "0"), "Straße");
            $export->setValue($export->getCell($column++, "0"), "Str. Nr");
            $export->setValue($export->getCell($column++, "0"), "PLZ");
            $export->setValue($export->getCell($column++, "0"), "Ort");
            $export->setValue($export->getCell($column++, "0"), "Ortsteil");
            $export->setValue($export->getCell($column++, "0"), "Telefon Festnetz");
            $export->setValue($export->getCell($column++, "0"), "Telefon Mobil");
            $export->setValue($export->getCell($column, "0"), "E-mail");

            $Row = 1;

            foreach ($companyList as $Item) {
                $column = 0;
                $export->setValue($export->getCell($column++, $Row), $Item['Number']);
                $export->setValue($export->getCell($column++, $Row), $Item['Name']);
                $export->setValue($export->getCell($column++, $Row), $Item['ExtendedName']);
                $export->setValue($export->getCell($column++, $Row), $Item['Description']);
                $export->setValue($export->getCell($column++, $Row), $Item['ContactPerson']);
                $export->setValue($export->getCell($column++, $Row), $Item['StreetName']);
                $export->setValue($export->getCell($column++, $Row), $Item['StreetNumber']);
                $export->setValue($export->getCell($column++, $Row), $Item['Code']);
                $export->setValue($export->getCell($column++, $Row), $Item['City']);
                $export->setValue($export->getCell($column++, $Row), $Item['District']);
                $export->setValue($export->getCell($column++, $Row), $Item['PhoneNumber']);
                $export->setValue($export->getCell($column++, $Row), $Item['MobilPhoneNumber']);
                $export->setValue($export->getCell($column, $Row), $Item['Mail']);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
