<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\Billing;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Library\NumberToWord\NumberToWord;

/**
 * Class Billing
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Billing
 */
class Billing
{

    /** @var null|Frame $Document */
    private $Document = null;

    /** @var null|TblItem $tblItem */
    private $tblItem = null;

    /** @var null|TblDocument $tblDocument */
    private $tblDocument = null;

    /** @var null|array $Data  */
    private $Data = null;

    const TEXT_SIZE = '14px';

    public function __construct(TblItem $tblItem, TblDocument $tblDocument, $Data)
    {
        $this->tblItem = $tblItem;
        $this->tblDocument = $tblDocument;
        $this->Data = $Data;
    }

    /**
     * @param TblPerson $tblPersonDebtor
     * @param TblPerson $tblPersonCauser
     * @param $TotalPrice
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function createSingleDocument(
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser,
        $TotalPrice
    ) {
        $pageList[] = $this->buildPage($tblPersonDebtor, $tblPersonCauser, $TotalPrice);

        $this->Document = $this->buildDocument($pageList);

        return $this->Document->getTemplate();
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    private function buildDocument($pageList = array())
    {
        $document = new Document();

        foreach ($pageList as $subjectPages) {
            if (is_array($subjectPages)) {
                foreach ($subjectPages as $page) {
                    $document->addPage($page);
                }
            } else {
                $document->addPage($subjectPages);
            }
        }

        $InjectStyle = 'body { margin-left: 1.0cm !important; margin-right: 1.0cm !important; }';

        return (new Frame($InjectStyle))->addDocument($document);
    }

    /**
     * @param string $Text
     * @param string $ItemName
     * @param string $Year
     * @param string $TotalPrice
     * @param string $DebtorSalutation
     * @param string $DebtorFirstName
     * @param string $DebtorLastName
     * @param string $CauserSalutation
     * @param string $CauserFirstName
     * @param string $CauserLastName
     * @param string $Birthday
     * @param string $From
     * @param string $To
     * @param string $Date
     * @param string $Location
     * @param string $CompanyName
     * @param string $CompanyExtendedName
     * @param string $CompanyAddress
     * @param string $StudentIdentifier
     *
     * @return string
     */
    private function setPlaceholders($Text, $ItemName, $Year, $TotalPrice, $DebtorSalutation, $DebtorFirstName,
        $DebtorLastName, $CauserSalutation, $CauserFirstName, $CauserLastName, $Birthday, $From, $To, $Date, $Location,
        $CompanyName, $CompanyExtendedName, $CompanyAddress, $StudentIdentifier)
    {
        $Text = str_replace('[Jahr]', $Year, $Text);
        $Text = str_replace('[Zeitraum von]', $From, $Text);
        $Text = str_replace('[Zeitraum bis]', $To, $Text);
        $Text = str_replace('[Beitragsart]', $ItemName, $Text);
        $Text = str_replace('[Beitragssumme]', $TotalPrice, $Text);
        $TotalPrice2Word = NumberToWord::float2Text($TotalPrice, true);
        $Text = str_replace('[Beitragssumme als Wort]', $TotalPrice2Word, $Text);
        $Text = str_replace('[Beitragszahler Anrede]', $DebtorSalutation, $Text);
        $Text = str_replace('[Beitragszahler Vorname]', $DebtorFirstName, $Text);
        $Text = str_replace('[Beitragszahler Nachname]', $DebtorLastName, $Text);
        $Text = str_replace('[Beitragsverursacher Anrede]', $CauserSalutation, $Text);
        $Text = str_replace('[Beitragsverursacher Vorname]', $CauserFirstName, $Text);
        $Text = str_replace('[Beitragsverursacher Nachname]', $CauserLastName, $Text);
        $Text = str_replace('[Beitragsverursacher Geburtstag]', $Birthday, $Text);
        $Text = str_replace('[Schülernummer]', $StudentIdentifier, $Text);
        $Text = str_replace('[Datum]', $Date, $Text);
        $Text = str_replace('[Ort]', $Location, $Text);
        $Text = str_replace('[Trägername]', $CompanyName, $Text);
        $Text = str_replace('[Trägerzusatz]', $CompanyExtendedName, $Text);
        $Text = str_replace('[Trägeradresse]', $CompanyAddress, $Text);

        return $Text;
    }

    /**
     * @param TblPerson $tblPersonDebtor
     * @param TblPerson $tblPersonCauser
     * @param $TotalPrice
     *
     * @return Page
     */
    private function buildPage(
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser,
        $TotalPrice
    ) {
        $Data = $this->Data;
        $CompanyName = $Data['CompanyName'];
        $CompanyExtendedName = $Data['CompanyExtendedName'];
        $CompanyAddress = $Data['CompanyAddress'];
        $Location = $Data['Location'];
        $Date = $Data['Date'];
        $Subject = $Data['Subject'];
        $Content = $Data['Content'];
        $Year = $Data['Year'];
        $From = $Data['From'];
        $To = $Data['To'];
        // Aus Zahlen werden die Namen der Monate
        $From = Invoice::useService()->getMonthName($From);
        $To = Invoice::useService()->getMonthName($To);

        $ItemName = $this->tblItem->getName();
        $DebtorSalutation = isset($Data['SalutationFamily']) ? 'Familie' : $tblPersonDebtor->getSalutation();
        $DebtorFirstName = $tblPersonDebtor->getFirstSecondName();
        $DebtorLastName = $tblPersonDebtor->getLastName();
        $CauserSalutation = $tblPersonCauser->getSalutation();
        $CauserFirstName = $tblPersonCauser->getFirstSecondName();
        $CauserLastName = $tblPersonCauser->getLastName();
        $Birthday = '';
        if(($tblCommon = $tblPersonCauser->getCommon())){
            if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                $Birthday = $tblCommonBirthDates->getBirthday();
            }
        }
        $StudentIdentifier = '';
        if(($tblStudent = Student::useService()->getStudentByPerson($tblPersonCauser))){
            $StudentIdentifier = $tblStudent->getIdentifier();
        }

        // Umgang mit nicht gefüllten Werten
        $ItemName = $this->setEmptyString($ItemName);
        $DebtorSalutation = $this->setEmptyString($DebtorSalutation);
        $DebtorFirstName = $this->setEmptyString($DebtorFirstName);
        $DebtorLastName = $this->setEmptyString($DebtorLastName);
        $CauserSalutation = $this->setEmptyString($CauserSalutation);
        $CauserFirstName = $this->setEmptyString($CauserFirstName);
        $CauserLastName = $this->setEmptyString($CauserLastName);
        $Birthday = $this->setEmptyString($Birthday);



        $Subject = $this->setPlaceholders($Subject, $ItemName, $Year, $TotalPrice, $DebtorSalutation, $DebtorFirstName,
            $DebtorLastName, $CauserSalutation, $CauserFirstName, $CauserLastName, $Birthday, $From, $To, $Date,
            $Location, $CompanyName, $CompanyExtendedName, $CompanyAddress, $StudentIdentifier);

        $Content = $this->setPlaceholders($Content, $ItemName, $Year, $TotalPrice, $DebtorSalutation, $DebtorFirstName,
            $DebtorLastName, $CauserSalutation, $CauserFirstName, $CauserLastName, $Birthday, $From, $To, $Date,
            $Location, $CompanyName, $CompanyExtendedName, $CompanyAddress, $StudentIdentifier);

        return (new Page())
            ->addSlice($this->getHeaderSlice('150px'))
            ->addSlice($this->getAddressSlice($CompanyName, $CompanyExtendedName, $CompanyAddress, $tblPersonDebtor))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($Location . ', den ' . $Date)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($Subject)
                    ->styleTextSize('18px')
                    ->styleTextBold()
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(nl2br($Content))
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignJustify()
                    ->styleMarginTop('25px')
                )
            );
    }

    /**
     * @param string $Value
     *
     * @return string
     */
    private function setEmptyString($Value = '')
    {

        if($Value === ''){
            return '...';
        }
        return $Value;
    }

    /**
     * @param string $Height
     *
     * @return Slice
     */
    private function getHeaderSlice($Height = '200px')
    {
        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'Billing_PictureAddress'))
        ) {
            $pictureAddress = (string)$tblSetting->getValue();
        } else {
            $pictureAddress = '';
        }

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'Billing_PictureHeight'))
        ) {
            $pictureHeight = (string)$tblSetting->getValue();
        } else {
            $pictureHeight = '';
        }

        if ($pictureAddress != '') {
            $pictureHeight = $pictureHeight ? $pictureHeight : '90px';
            $element = (new Element\Image($pictureAddress, 'auto', $pictureHeight));
        } else {
            $element = (new Element())
                ->setContent('&nbsp;');
        }

        return (new Slice())
            ->addElement($element)
            ->styleAlignRight()
            ->styleHeight($Height);
    }

    /**
     * @param $CompanyName
     * @param $CompanyExtendedName
     * @param $CompanyAddress
     * @param TblPerson $tblPersonDebtor
     * @param string $TextSize
     *
     * @return Slice
     */
    private function getAddressSlice(
        $CompanyName,
        $CompanyExtendedName,
        $CompanyAddress,
        TblPerson $tblPersonDebtor,
        $TextSize = '8px'
    ) {
        $address1 = '&nbsp;';
        $address2 = '&nbsp;';
        if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))) {
            $address1 = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
            if (($tblCity = $tblAddress->getTblCity())) {
                $address2 = $tblCity->getCode() . ' ' . $tblCity->getDisplayName();
            }
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent($CompanyName . ($CompanyExtendedName ?  ' ' . $CompanyExtendedName : ''))
                ->styleTextSize($TextSize)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($CompanyAddress)
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom()
                , '30%')
                ->addElementColumn((new Element()))
            )
            ->addElement((new Element())
                ->setContent(isset($this->Data['SalutationFamily']) ? 'Familie' : $tblPersonDebtor->getSalutation())
                ->styleTextSize(self::TEXT_SIZE)
                ->styleMarginTop('14px')
            )
            ->addElement((new Element())
                ->setContent($tblPersonDebtor->getFirstSecondName() . ' ' . $tblPersonDebtor->getLastName())
                ->styleTextSize(self::TEXT_SIZE)
            )
            ->addElement((new Element())
                ->setContent($address1)
                ->styleTextSize(self::TEXT_SIZE)
            )
            ->addElement((new Element())
                ->setContent($address2)
                ->styleTextSize(self::TEXT_SIZE)
            )
        ;
    }
}