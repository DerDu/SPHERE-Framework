<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\PasswordChange;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;

class PasswordChange extends AbstractDocument
{

    const BLOCK_SPACE = '30px';

    /**
     * PasswordChange constructor.
     *
     * @param array $Data
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    function __construct($Data)
    {

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();


    /**
     * @param array $DataPost
     *
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function setFieldValue($DataPost)
    {

        $tblAccount = false;
        // Common
        $this->FieldValue['PersonSalutation'] = '';
        $this->FieldValue['PersonFirstLastName'] = '';
        $this->FieldValue['PersonTitle'] = '';
        $this->FieldValue['PersonLastName'] = '';
        $this->FieldValue['Gender'] = 0;
        $this->FieldValue['UserAccount'] = '';
        $this->FieldValue['Street'] = '';
        $this->FieldValue['District'] = '';
        $this->FieldValue['City'] = '';
        // Text choose decision
        $this->FieldValue['IsParent'] = (isset($DataPost['IsParent']) ? $DataPost['IsParent'] : false);
        $this->FieldValue['ChildCount'] = 0;
        // School
        $this->FieldValue['CompanyName'] = (isset($DataPost['CompanyName']) && $DataPost['CompanyName'] != '' ? $DataPost['CompanyName'] : '&nbsp;');
        $this->FieldValue['CompanyExtendedName'] = (isset($DataPost['CompanyExtendedName']) && $DataPost['CompanyExtendedName'] != '' ? $DataPost['CompanyExtendedName'] : '&nbsp;');
        $this->FieldValue['CompanyStreet'] = (isset($DataPost['CompanyStreet']) && $DataPost['CompanyStreet'] != '' ? $DataPost['CompanyStreet'] : '&nbsp;');
        $this->FieldValue['CompanyDistrict'] = (isset($DataPost['CompanyDistrict']) && $DataPost['CompanyDistrict'] != '' ? $DataPost['CompanyDistrict'] : '&nbsp;');
        $this->FieldValue['CompanyCity'] = (isset($DataPost['CompanyCity']) && $DataPost['CompanyCity'] != '' ? $DataPost['CompanyCity'] : '&nbsp;');
        // Contact
        $this->FieldValue['Phone'] = (isset($DataPost['Phone']) && $DataPost['Phone'] != '' ? $DataPost['Phone'] : '&nbsp;');
        $this->FieldValue['Fax'] = (isset($DataPost['Fax']) && $DataPost['Fax'] != '' ? $DataPost['Fax'] : '&nbsp;');
        $this->FieldValue['Mail'] = (isset($DataPost['Mail']) && $DataPost['Mail'] != '' ? $DataPost['Mail'] : '&nbsp;');
        $this->FieldValue['Web'] = (isset($DataPost['Web']) && $DataPost['Web'] != '' ? $DataPost['Web'] : '&nbsp;');
        //Signer
        $this->FieldValue['Place'] = (isset($DataPost['Place']) && $DataPost['Place'] != '' ? $DataPost['Place'].', den ' : '');
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');

        //Account
        $UserAccountId = (isset($DataPost['UserAccountId']) && $DataPost['UserAccountId'] != '' ? $DataPost['UserAccountId'] : false);
        if($UserAccountId){
            $tblUserAccount = Account::useService()->getUserAccountById($UserAccountId);
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if($tblAccount){
                $this->FieldValue['UserAccount'] = $tblAccount->getUsername();
            }
        }

        // Student/Custody
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        if ($this->FieldValue['PersonId'] && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))) {

            $ChildCount = 0;
            if($this->FieldValue['IsParent']){
                if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
                    foreach($tblRelationshipList as $tblRelationship){
                        $tblType = $tblRelationship->getTblType();
                        if($tblType->getName() == 'Sorgeberechtigt'){
                            $ChildCount++;
                        }
                    }
                }
            }
            if($ChildCount == 1){
                $this->FieldValue['ChildCount'] = 1;
            }elseif($ChildCount > 1){
                $this->FieldValue['ChildCount'] = 2;
            }

            if(($tblCommon = $tblPerson->getCommon())) {
                if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                    if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                        if($tblCommonGender->getName() == "Männlich"){
                            $this->FieldValue['Gender'] = 1;
                        }
                        if($tblCommonGender->getName() == "Weiblich") {
                            $this->FieldValue['Gender'] = 2;
                        }
                    }
                }
            }

            $this->FieldValue['PersonTitle'] = $tblPerson->getTitle();
            $this->FieldValue['PersonLastName'] = $tblPerson->getLastName();
            $this->FieldValue['PersonSalutation'] = $tblPerson->getSalutation();
            $this->FieldValue['PersonFirstLastName'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if($tblAddress){
                $this->FieldValue['Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if($tblCity){
                    $this->FieldValue['District'] = $tblCity->getDistrict();
                    $this->FieldValue['City'] = $tblCity->getCode().' '.$tblCity->getName();
                }
            }
        }

        //generate new Password
        $this->FieldValue['Password'] = $Password = Account::useService()->generatePassword();
        // remove tblAccount
        if ($tblAccount && $Password) {
            if(($tblUserAccount = Account::useService()->getUserAccountByAccount($tblAccount))){
                AccountAuthorization::useService()->changePassword($Password, $tblAccount);
                Account::useService()->changePassword($tblUserAccount, $Password);
                Account::useService()->changeUpdateDate($tblUserAccount, TblUserAccount::VALUE_UPDATE_TYPE_RENEW);
            }
        };

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Passwort-Schulsoftware';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPageOne())
        );
    }

    /**
     * @return Slice
     */
    private function getAddressHead()
    {
        $Slice = new Slice();

        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['CompanyName'])
            ->styleTextSize('8pt')
        );
        if($this->FieldValue['CompanyExtendedName'] != '&nbsp;'){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['CompanyExtendedName'])
                ->styleTextSize('8pt')
            );
        }
        $Slice->addElement((new Element())
            ->setContent(
                ($this->FieldValue['CompanyDistrict'] != '&nbsp;'
                    ? $this->FieldValue['CompanyDistrict'].' '
                    : '')
                .$this->FieldValue['CompanyStreet'].' '
                .$this->FieldValue['CompanyCity'])
            ->styleTextSize('8pt')
            ->stylePaddingBottom('15px')
        );

//        $Slice->addElement((new Element())
//            ->setContent('Empfänger')
//            ->styleTextSize('8pt')
//        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['PersonSalutation'].' '.$this->FieldValue['PersonFirstLastName'])
        );
        if($this->FieldValue['District']){
            $Slice->addElement((new Element())
                ->setContent($this->FieldValue['District'])
            );
        }
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['Street'])
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['City'])
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getContactData()
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefon:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Phone'])
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Telefax:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Fax'])
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('E-Mail:')
                ->styleTextSize('8pt')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Mail'])
                ->styleTextSize('8pt')
                , '80%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Internet:')
                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Web'])
                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
                , '80%')
        );
        $Slice->addElement((new Element())
                ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
//                ->styleTextSize('8pt')
                ->stylePaddingTop('10px')
        );

        $Slice->stylePaddingTop('10px');

        return $Slice;
    }

    private function getFirstLetterContent($Height = '500px')
    {

        $Slice = new Slice();
        if ($this->FieldValue['IsParent']) {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Ihre neuen Zugangsdaten zur Notenübersicht
                    {% if '.$this->FieldValue['ChildCount'].' == 1 %}
                        Ihres Kindes
                    {% elseif '.$this->FieldValue['ChildCount'].' == 2 %}
                        Ihrer Kinder
                    {% else %}
                        Ihres Kindes / Ihrer Kinder
                    {% endif %}')
                    ->styleTextBold()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                ))
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('40px')
                )
                ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                    {% if '.$this->FieldValue['Gender'].' == 1 %}
                        Lieber 
                    {% elseif '.$this->FieldValue['Gender'].' == 2 %}
                        Liebe 
                    {% else %}
                        Liebe(r) 
                    {% endif %}'
                    .'{% if '.$this->FieldValue['PersonSalutation'].' == "" %}
                        Herr/Frau
                    {% else %}
                        '.$this->FieldValue["PersonSalutation"].' '.'
                    {% endif %}'
                        . $this->FieldValue['PersonTitle'].' '
                        . $this->FieldValue['PersonLastName'].',')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('sollten Sie das Passwort vergessen, bestehen zwei Möglichkeiten, das Passwort zurückzusetzen:')
                    ->stylePaddingTop('12px')
                    ->styleAlignJustify()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('1. Wir setzen das Passwort auf das Initialpasswort zurück. Diese Möglichkeit ist für Sie kostenfrei.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                    ->stylePaddingLeft('35px')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('2. Wir generieren neue Zugangsdaten für Sie und übersenden Ihnen diese. Hierfür erheben
                    wir einen Unkostenbeitrag von 5 €, der Ihnen bei der nächsten Abrechnung belastet wird.')
                    ->stylePaddingTop('5px')
                    ->styleAlignJustify()
                    ->stylePaddingLeft('35px')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Wunschgemäß übersenden wir Ihnen Ihre neuen Zugangsdaten zur Einsicht der Noten 
                    {% if '.$this->FieldValue['ChildCount'].' == 1 %}
                        Ihres Kindes.
                    {% elseif '.$this->FieldValue['ChildCount'].' == 2 %}
                        Ihrer Kinder.
                    {% else %}
                        Ihres Kindes / Ihrer Kinder.
                    {% endif %}')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Benutzername:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingLeft('35px')
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['UserAccount'])
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingLeft('10px')
                    , '70%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Passwort:')
                    ->stylePaddingTop()
                    ->stylePaddingLeft('35px')
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['Password'])
                    ->stylePaddingTop()
                    ->stylePaddingLeft('10px')
                    , '70%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Bitte ändern Sie das Initialpasswort und notieren Sie sich das neue Passwort hier:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '73%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '19%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Hierfür erheben wir einen Unkostenbeitrag von 5 €, der Ihnen bei der nächsten Abrechnung belastet wird.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Bitte heben Sie sich dieses Schreiben gut auf.')
                    ->styleTextBold()
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Für Rückfragen stehen wir Ihnen gern zur Verfügung.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Dieses Schreiben wurde maschinell erstellt und ist auch ohne Unterschrift rechtsgültig.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->styleHeight($Height);
        } else {
            $Slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Deine neuen Zugangsdaten zur Notenübersicht')
                    ->styleTextBold()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                ))
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('40px')
                )
                ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                    {% if '.$this->FieldValue['Gender'].' == 1 %}
                        Lieber 
                    {% elseif '.$this->FieldValue['Gender'].' == 2 %}
                        Liebe 
                    {% else %}
                        Liebe(r) 
                    {% endif %}'
                    . $this->FieldValue['PersonFirstLastName'].',')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('solltest Du das Passwort vergessen, bestehen zwei Möglichkeiten, das Passwort zurückzusetzen:')
                    ->stylePaddingTop('12px')
                    ->styleAlignJustify()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('1. Wir setzen das Passwort auf das Initialpasswort zurück. Diese Möglichkeit ist für Deine Eltern kostenfrei.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                    ->stylePaddingLeft('35px')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('2. Wir generieren neue Zugangsdaten für Dich und übersenden Dir diese. Hierfür erheben wir einen Unkostenbeitrag von 5 €, der Deinen Eltern berechnet wird.')
                    ->stylePaddingTop('5px')
                    ->styleAlignJustify()
                    ->stylePaddingLeft('35px')
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Wunschgemäß übersenden wir Dir neue Zugangsdaten zur Einsicht Deiner Noten.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleAlignJustify()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Benutzername:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingLeft('35px')
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['UserAccount'])
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->stylePaddingLeft('10px')
                    , '70%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Passwort:')
                    ->stylePaddingTop()
                    ->stylePaddingLeft('35px')
                    , '22%'
                )
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['Password'])
                    ->stylePaddingTop()
                    ->stylePaddingLeft('10px')
                    , '70%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Bitte ändere das Initialpasswort und notiere das neue Passwort hier:')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    , '61%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                    ->styleBorderBottom()
                    , '31%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Hierfür erheben wir einen Unkostenbeitrag von 5 €, der Deinen Eltern berechnet wird.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Bitte hebe dieses Schreiben gut auf.')
                    ->styleTextBold()
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Falls Du Rückfragen oder Probleme mit der Anwendung hast, so wende Dich bitte an unser Sekretariat.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Dieses Schreiben wurde maschinell erstellt und ist auch ohne Unterschrift rechtsgültig.')
                    ->stylePaddingTop(self::BLOCK_SPACE)
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '4%'
                )
            )
            ->styleHeight($Height);
        }
        return $Slice;
    }

    /**
     * @return Page
     */
    private function buildPageOne()
    {

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '50%'
                    )
                    ->addElementColumn(
                        $this->getPicturePasswordChange()
                            ->styleAlignCenter()
                        , '50%')
                )
                ->styleHeight('120px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                    ->addSliceColumn(
                        $this->getAddressHead()
                    , '52%')
                    ->addSliceColumn(
                        $this->getContactData()
                    , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '4%'
                    )
                )
                ->styleHeight('160px')
//                ->styleBorderAll()
            )
            ->addSlice($this->getFirstLetterContent());
    }

    /**
     * @param string $with
     *
     * @return Element|Element\Image
     */
    protected function getPicturePasswordChange($with = 'auto')
    {

        $picturePath = $this->getPasswordUsedPicture();
        if ($picturePath != '') {
            $height = $this->getPasswordPictureHeight();
            $column = (new Element\Image($picturePath, $with, $height));
        } else {
            $column = (new Element())
                ->setContent('&nbsp;');
        }
        return $column;
    }

    /**
     * @return string
     */
    private function getPasswordUsedPicture()
    {
        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'PasswordChange_PictureAddress'))
        ) {
            return (string)$tblSetting->getValue();
        }
        return '';
    }

    /**
     * @return string
     */
    private function getPasswordPictureHeight()
    {

        $value = '';

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'PasswordChange_PictureHeight'))
        ) {
            $value = (string)$tblSetting->getValue();
        }

        return $value ? $value : '120px';
    }
}