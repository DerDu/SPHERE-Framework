<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\MPdf;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpWord;
use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Template\Template;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class BridgeTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class BridgeTest extends AbstractTestCase
{

    /**
     * @codeCoverageIgnore
     */
    public function tearDown()
    {

        if (false !== ( $Path = realpath(__DIR__.'/Content') )) {
            $Iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($Path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var \SplFileInfo $FileInfo */
            foreach ($Iterator as $FileInfo) {
                if (
                    $FileInfo->getBasename() != 'README.md'
                    && $FileInfo->getBasename() != 'BridgeTest.tpl'
                    && $FileInfo->getBasename() != 'BridgeTest.twig'
                    && $FileInfo->getBasename() != 'BridgeTest.xml'
                ) {
                    if ($FileInfo->isFile()) {
                        unlink($FileInfo->getPathname());
                    }
                    if ($FileInfo->isDir()) {
                        rmdir($FileInfo->getPathname());
                    }
                }
            }
        }

        $Template = new \MOC\V\TestSuite\Tests\Component\Template\BridgeTest();
        $Template->tearDown();
    }

    public function testPhpExcelDocument()
    {

        $Bridge = new PhpExcel();

        try {
            $Bridge->loadFile(new FileParameter(__FILE__));
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('MOC\V\Component\Document\Component\Exception\Repository\TypeFileException',
                $Exception);
        }
        $Bridge->newFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel.xlsx'));

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );

        /**
         * Sheet Size
         */

        $this->assertEquals(1, $Bridge->getSheetColumnCount());
        $this->assertEquals(1, $Bridge->getSheetRowCount());

        /**
         * Cell Index
         */

        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell('A1')
        );
        $this->assertEquals(0, $Cell->getColumn());
        $this->assertEquals(1, $Cell->getRow());

        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell('B2')
        );
        $this->assertEquals(1, $Cell->getColumn());
        $this->assertEquals(2, $Cell->getRow());

        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell(0, 0)
        );
        $this->assertEquals(0, $Cell->getColumn());
        $this->assertEquals(1, $Cell->getRow());

        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Cell',
            $Cell = $Bridge->getCell(1, 1)
        );
        $this->assertEquals(1, $Cell->getColumn());
        $this->assertEquals(2, $Cell->getRow());

        /**
         * Cell Value
         */

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setValue($Cell, '01')
        );
        $this->assertEquals('01', $Bridge->getValue($Cell));

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setValue($Cell, 'öäüß')
        );
        $this->assertEquals('öäüß', $Bridge->getValue($Cell));

        /**
         * Cell Style
         */

        /** Single Cell */
        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Style',
            $Style = $Bridge->setStyle($Bridge->getCell('A1'))
        );

        // Font Size
        $this->assertEquals(11, $Style->getFontSize());
        $Style->setFontSize();
        $this->assertEquals(11, $Style->getFontSize());
        $Style->setFontSize(12);
        $this->assertEquals(12, $Style->getFontSize());

        // Font Bold
        $this->assertEquals(false, $Style->getFontBold());
        $Style->setFontBold();
        $this->assertEquals(true, $Style->getFontBold());
        $Style->setFontBold(false);
        $this->assertEquals(false, $Style->getFontBold());
        $Style->setFontBold(true);
        $this->assertEquals(true, $Style->getFontBold());

        // Column Width
        $this->assertEquals(-1, $Style->getColumnWidth());
        $Style->setColumnWidth();
        $this->assertEquals(-1, $Style->getColumnWidth());
        $Style->setColumnWidth(10.0);
        $this->assertEquals(10.0, $Style->getColumnWidth());

        // Border
        $Style->setBorderTop();
        $Style->setBorderRight();
        $Style->setBorderBottom();
        $Style->setBorderLeft();
        $Style->setBorderOutline();
        $Style->setBorderVertical();
        $Style->setBorderHorizontal();
        $Style->setBorderAll();
        $Style->setBorderAll(1);
        $Style->setBorderAll(2);
        $Style->setBorderAll(3);
        $Style->setBorderAll(0);

        // Alignment
        $Style->setAlignmentLeft();
        $Style->setAlignmentCenter();
        $Style->setAlignmentRight();
        $Style->setAlignmentTop();
        $Style->setAlignmentMiddle();
        $Style->setAlignmentBottom();

        // Merge
        $Style->mergeCells();

        /** Cell Range **/
        $this->assertInstanceOf('MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel\Style',
            $Style = $Bridge->setStyle($Bridge->getCell('B1'), $Bridge->getCell('C2'))
        );

        // Font Size
        $this->assertEquals(array('B1' => 11, 'B2' => 11, 'C1' => 11, 'C2' => 11), $Style->getFontSize());
        $Style->setFontSize();
        $this->assertEquals(array('B1' => 11, 'B2' => 11, 'C1' => 11, 'C2' => 11), $Style->getFontSize());
        $Style->setFontSize(12);
        $this->assertEquals(array('B1' => 12, 'B2' => 12, 'C1' => 12, 'C2' => 12), $Style->getFontSize());

        // Font Bold
        $this->assertEquals(array('B1' => false, 'B2' => false, 'C1' => false, 'C2' => false), $Style->getFontBold());
        $Style->setFontBold();
        $this->assertEquals(array('B1' => true, 'B2' => true, 'C1' => true, 'C2' => true), $Style->getFontBold());
        $Style->setFontBold(false);
        $this->assertEquals(array('B1' => false, 'B2' => false, 'C1' => false, 'C2' => false), $Style->getFontBold());
        $Style->setFontBold(true);
        $this->assertEquals(array('B1' => true, 'B2' => true, 'C1' => true, 'C2' => true), $Style->getFontBold());

        // Column Width
        $this->assertEquals(array('B' => -1, 'C' => -1), $Style->getColumnWidth());
        $Style->setColumnWidth();
        $this->assertEquals(array('B' => -1, 'C' => -1), $Style->getColumnWidth());
        $Style->setColumnWidth(10);
        $this->assertEquals(array('B' => 10.0, 'C' => 10.0), $Style->getColumnWidth());

        // Border
        $Style->setBorderTop();
        $Style->setBorderRight();
        $Style->setBorderBottom();
        $Style->setBorderLeft();
        $Style->setBorderOutline();
        $Style->setBorderVertical();
        $Style->setBorderHorizontal();
        $Style->setBorderAll();
        $Style->setBorderAll(1);
        $Style->setBorderAll(2);
        $Style->setBorderAll(3);
        $Style->setBorderAll(0);

        // Alignment
        $Style->setAlignmentLeft();
        $Style->setAlignmentCenter();
        $Style->setAlignmentRight();
        $Style->setAlignmentTop();
        $Style->setAlignmentMiddle();
        $Style->setAlignmentBottom();

        // Merge
        $Style->mergeCells();

        /**
         * Save/Load File
         */

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel.xlsx'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.csv'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.csv'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.xls'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.xls'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.xlsx'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.xlsx'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.html'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Excel-As.html'))
        );

    }

    public function testPhpWordDocument()
    {

        $Bridge = new PhpWord();

        try {
            $Bridge->loadFile(new FileParameter(__FILE__));
        } catch (\Exception $Exception) {
            $this->assertInstanceOf('MOC\V\Component\Document\Component\Exception\Repository\TypeFileException',
                $Exception);
        }
        $Bridge->newFile(new FileParameter(__DIR__.'/Content/BridgeTest-Word.docx'));

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );

        /**
         * Save/Load File
         */

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Word.docx'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Word-As.docx'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Word-As.docx'))
        );
    }

    public function testDomPdfDocument()
    {

        $Bridge = new DomPdf();

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Twig.pdf'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent(Template::getTemplate(__DIR__.'/Content/BridgeTest.twig'))
        );

        $this->assertStringStartsWith('%PDF-', $Bridge->getContent());

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Twig-As.pdf'))
        );

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Tpl.pdf'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent(Template::getTemplate(__DIR__.'/Content/BridgeTest.tpl'))
        );

        $this->assertStringStartsWith('%PDF-', $Bridge->getContent());

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile()
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->saveFile(new FileParameter(__DIR__.'/Content/BridgeTest-Tpl-As.pdf'))
        );
    }

    public function testMPdfDocument()
    {

        $Bridge = new MPdf();

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Twig.pdf'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent(Template::getTwigTemplate(__DIR__.'/Content/BridgeTest.twig'))
        );
//
//        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );
//
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile()
//        );
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Twig-As.pdf' ) )
//        );
//
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest-Tpl.pdf'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperSizeParameter(new PaperSizeParameter('A4'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setPaperOrientationParameter(new PaperOrientationParameter('PORTRAIT'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->setContent(Template::getSmartyTemplate(__DIR__.'/Content/BridgeTest.tpl'))
        );
//
//        $this->assertStringStartsWith( '%PDF-', $Bridge->getContent() );
//
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile()
//        );
//        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
//            $Bridge->saveFile( new FileParameter( __DIR__.'/Content/BridgeTest-Tpl-As.pdf' ) )
//        );
    }

    public function testUniversalXmlDocument()
    {

        $Bridge = new UniversalXml();

        $this->assertInstanceOf('MOC\V\Component\Document\Component\IBridgeInterface',
            $Bridge->loadFile(new FileParameter(__DIR__.'/Content/BridgeTest.xml'))
        );
        $this->assertInstanceOf('MOC\V\Component\Document\Vendor\UniversalXml\Source\Node',
            $Bridge->getContent()
        );
    }
}
