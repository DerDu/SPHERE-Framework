<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\Billing;

use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Library\NumberToWord\NumberToWord;

/**
 * Class DocumentWarning Mahnung
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Billing
 */
class DocumentWarning
{

    /** @var null|Frame $Document */
    private $Document = null;

    /** @var null|TblItem $tblItem */
    private $tblItem = null;

    /** @var null|array $Data  */
    private $Data = null;

    const TEXT_SIZE = '14px';

    public function __construct(TblItem $tblItem, $Data)
    {
        $this->tblItem = $tblItem;
        $this->Data = $Data;
    }

    /**
     * @param TblPerson $tblPersonDebtor
     * @param TblPerson $tblPersonCauser
     *
     * @return \MOC\V\Component\Template\Component\IBridgeInterface
     */
    public function createSingleDocument(
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser
    ) {
        $pageList[] = $this->buildPage($tblPersonDebtor, $tblPersonCauser);

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
     * @param $Text
     * @param $InvoiceNumber
     * @param $BillTime
     * @param $BillName
     * @param $TargetTime
     * @param $ItemName
     * @param $Count
     * @param $Price
     * @param $SummaryPrice
     * @param $DebtorSalutation
     * @param $DebtorFirstName
     * @param $DebtorLastName
     * @param $CauserSalutation
     * @param $CauserFirstName
     * @param $CauserLastName
     * @param $Birthday
     * @param $Date
     * @param $Location
     * @param $CompanyName
     * @param $CompanyExtendedName
     * @param $CompanyAddress
     *
     * @return mixed
     */
    private function setPlaceholders ($Text, $InvoiceNumber, $BillTime, $BillName, $TargetTime, $ItemName, $Count,
        $Price, $SummaryPrice, $DebtorSalutation, $DebtorFirstName, $DebtorLastName, $CauserSalutation, $CauserFirstName,
        $CauserLastName, $Birthday, $Date, $Location, $CompanyName, $CompanyExtendedName, $CompanyAddress)
    {

        $Text = str_replace('[Rechnungsnummer]', $InvoiceNumber, $Text);
        $Text = str_replace('[Abrechnungszeitraum]', $BillTime, $Text);
        $Text = str_replace('[Name der Abrechnung]', $BillName, $Text);
        $Text = str_replace('[Fälligkeit]', $TargetTime, $Text);
        $Text = str_replace('[Beitragsart]', $ItemName, $Text);
        $Text = str_replace('[Anzahl]', $Count, $Text);
        $Text = str_replace('[Einzelpreis]', $Price, $Text);
        $Price2Word = NumberToWord::float2Text($Price, true);
        $Text = str_replace('[Einzelpreis als Wort]', $Price2Word, $Text);
        $Text = str_replace('[Gesamtpreis]', $SummaryPrice, $Text);
        $SummaryPrice2Word = NumberToWord::float2Text($SummaryPrice, true);
        $Text = str_replace('[Gesamtpreis als Wort]', $SummaryPrice2Word, $Text);
        $Text = str_replace('[Beitragszahler Anrede]', $DebtorSalutation, $Text);
        $Text = str_replace('[Beitragszahler Vorname]', $DebtorFirstName, $Text);
        $Text = str_replace('[Beitragszahler Nachname]', $DebtorLastName, $Text);
        $Text = str_replace('[Beitragsverursacher Anrede]', $CauserSalutation, $Text);
        $Text = str_replace('[Beitragsverursacher Vorname]', $CauserFirstName, $Text);
        $Text = str_replace('[Beitragsverursacher Nachname]', $CauserLastName, $Text);
        $Text = str_replace('[Beitragsverursacher Geburtstag]', $Birthday, $Text);
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
     *
     * @return Page
     */
    private function buildPage(
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser
    ) {
        $Data = $this->Data;
        $InvoiceNumber = $Data['InvoiceNumber'];
        $TargetTime = $Data['TargetTime'];
        $CompanyName = $Data['CompanyName'];
        $CompanyExtendedName = $Data['CompanyExtendedName'];
        $CompanyAddress = $Data['CompanyAddress'];
        $Subject = $Data['Subject'];
        $Content = $Data['Content'];
        $Date = '_________________';
        $Location = '_________________';
        $BillTime = $Data['BillTime'];
        $BillName = $Data['BillName'];
        $Count = $Data['Count'];
        $Price = $Data['Price'];
        $SummaryPrice = $Data['SummaryPrice'];

        $ItemName = $this->tblItem->getName();
        $DebtorSalutation = $tblPersonDebtor->getSalutation();
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

        $InvoiceNumber = $this->setEmptyString($InvoiceNumber);
        $TargetTime = $this->setEmptyString($TargetTime);
        $CompanyName = $this->setEmptyString($CompanyName);
        $CompanyExtendedName = $this->setEmptyString($CompanyExtendedName);
        $CompanyAddress = $this->setEmptyString($CompanyAddress);
        $Subject = $this->setEmptyString($Subject);
        $Content = $this->setEmptyString($Content);
        $Date = $this->setEmptyString($Date);
        $Location = $this->setEmptyString($Location);
        $BillTime = $this->setEmptyString($BillTime);
        $BillName = $this->setEmptyString($BillName);
        $Count = $this->setEmptyString($Count);
        $Price = $this->setEmptyString($Price);
        $SummaryPrice = $this->setEmptyString($SummaryPrice);
        $ItemName = $this->setEmptyString($ItemName);
        $DebtorSalutation = $this->setEmptyString($DebtorSalutation);
        $DebtorFirstName = $this->setEmptyString($DebtorFirstName);
        $DebtorLastName = $this->setEmptyString($DebtorLastName);
        $CauserSalutation = $this->setEmptyString($CauserSalutation);
        $CauserFirstName = $this->setEmptyString($CauserFirstName);
        $CauserLastName = $this->setEmptyString($CauserLastName);
        $Birthday = $this->setEmptyString($Birthday);

        $Subject = self::setPlaceholders($Subject, $InvoiceNumber, $BillTime, $BillName, $TargetTime, $ItemName,
            $Count, $Price, $SummaryPrice, $DebtorSalutation, $DebtorFirstName, $DebtorLastName, $CauserSalutation,
            $CauserFirstName, $CauserLastName, $Birthday, $Date, $Location, $CompanyName, $CompanyExtendedName, $CompanyAddress);

        $Content = self::setPlaceholders($Content, $InvoiceNumber, $BillTime, $BillName, $TargetTime, $ItemName,
            $Count, $Price, $SummaryPrice, $DebtorSalutation, $DebtorFirstName, $DebtorLastName, $CauserSalutation,
            $CauserFirstName, $CauserLastName, $Birthday, $Date, $Location, $CompanyName, $CompanyExtendedName, $CompanyAddress);

        return (new Page())
            ->addSlice($this->getHeaderSlice('150px'))
            ->addSlice($this->getAddressSlice($CompanyName, $CompanyExtendedName, $CompanyAddress, $tblPersonDebtor))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;') // $Location . ', den ' . $Date)
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
                    ->styleMarginTop('20px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(nl2br($Content))
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignJustify()
                    ->styleMarginTop('25px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;') // $Location . ', den ' . $Date)
                    ->setContent($Location . ', den ' . $Date)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize('8pt')
                        ->styleAlignRight()
                        ->styleHeight('10px')
                        , '53%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ort')
                        ->styleTextSize('8pt')
                        ->styleHeight('10px')
                        , '26%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Datum')
                        ->styleTextSize('8pt')
                        ->styleHeight('10px')
                        , '21%'
                    )
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