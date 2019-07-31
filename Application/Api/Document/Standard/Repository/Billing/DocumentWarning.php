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
        $DebtorSalutation = isset($Data['SalutationFamily']) ? 'Familie' : $tblPersonDebtor->getSalutation();
        $DebtorFirstName = $tblPersonDebtor->getFirstSecondName();
        $DebtorLastName = $tblPersonDebtor->getLastName();
        $CauserSalutation = $tblPersonCauser->getSalutation();
        $CauserFirstName = $tblPersonCauser->getFirstSecondName();
        $CauserLastName = $tblPersonCauser->getLastName();

        $Subject = str_replace('[Rechnungsnummer]', $InvoiceNumber, $Subject);
        $Subject = str_replace('[Abrechnungszeitraum]', $BillTime, $Subject);
        $Subject = str_replace('[Name der Abrechnung]', $BillName, $Subject);
        $Subject = str_replace('[Fälligkeit]', $TargetTime, $Subject);
        $Subject = str_replace('[Beitragsart]', $ItemName, $Subject);
        $Subject = str_replace('[Anzahl]', $Count, $Subject);
        $Subject = str_replace('[Einzelpreis]', $Price, $Subject);
        $Subject = str_replace('[Gesamtpreis]', $SummaryPrice, $Subject);
        $Subject = str_replace('[Beitragszahler Anrede]', $DebtorSalutation, $Subject);
        $Subject = str_replace('[Beitragszahler Vorname]', $DebtorFirstName, $Subject);
        $Subject = str_replace('[Beitragszahler Nachname]', $DebtorLastName, $Subject);
        $Subject = str_replace('[Beitragsverursacher Anrede]', $CauserSalutation, $Subject);
        $Subject = str_replace('[Beitragsverursacher Vorname]', $CauserFirstName, $Subject);
        $Subject = str_replace('[Beitragsverursacher Nachname]', $CauserLastName, $Subject);
        $Subject = str_replace('[Datum]', $Date, $Subject);
        $Subject = str_replace('[Ort]', $Location, $Subject);
        $Subject = str_replace('[Trägername]', $CompanyName, $Subject);
        $Subject = str_replace('[Trägerzusatz]', $CompanyExtendedName, $Subject);
        $Subject = str_replace('[Trägeradresse]', $CompanyAddress, $Subject);

        $Content = str_replace('[Rechnungsnummer]', $InvoiceNumber, $Content);
        $Content = str_replace('[Abrechnungszeitraum]', $BillTime, $Content);
        $Content = str_replace('[Name der Abrechnung]', $BillName, $Content);
        $Content = str_replace('[Fälligkeit]', $TargetTime, $Content);
        $Content = str_replace('[Beitragsart]', $ItemName, $Content);
        $Content = str_replace('[Anzahl]', $Count, $Content);
        $Content = str_replace('[Einzelpreis]', $Price, $Content);
        $Content = str_replace('[Gesamtpreis]', $SummaryPrice, $Content);
        $Content = str_replace('[Beitragszahler Anrede]', $DebtorSalutation, $Content);
        $Content = str_replace('[Beitragszahler Vorname]', $DebtorFirstName, $Content);
        $Content = str_replace('[Beitragszahler Nachname]', $DebtorLastName, $Content);
        $Content = str_replace('[Beitragsverursacher Anrede]', $CauserSalutation, $Content);
        $Content = str_replace('[Beitragsverursacher Vorname]', $CauserFirstName, $Content);
        $Content = str_replace('[Beitragsverursacher Nachname]', $CauserLastName, $Content);
        $Content = str_replace('[Datum]', $Date, $Content);
        $Content = str_replace('[Ort]', $Location, $Content);
        $Content = str_replace('[Trägername]', $CompanyName, $Content);
        $Content = str_replace('[Trägerzusatz]', $CompanyExtendedName, $Content);
        $Content = str_replace('[Trägeradresse]', $CompanyAddress, $Content);

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