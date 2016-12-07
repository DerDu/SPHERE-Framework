<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpWord;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpWord;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use PhpOffice\PhpWord\IOFactory;

/**
 * Class File
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpWord
 */
abstract class File extends Config
{

    /**
     * @param FileParameter $Location
     *
     * @return PhpWord
     */
    public function newFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);
        $this->Source = new \PhpOffice\PhpWord\PhpWord();
        return $this;
    }

    /**
     * @param FileParameter $Location
     *
     * @return PhpWord
     * @throws TypeFileException
     */
    public function loadFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);

        $Info = $Location->getFileInfo();
        $ReaderType = $this->getReaderType($Info);

        if ($ReaderType) {
            /** @var \PhpOffice\PhpWord\Reader\ReaderInterface $Reader */
            $Reader = IOFactory::createReader($ReaderType);
            $this->Source = $Reader->load($Location->getFile());
        } else {
            throw new TypeFileException('No Reader for '.$Info->getExtension().' available!');
        }
        return $this;
    }

    /**
     * @param \SplFileInfo $Info
     *
     * @return string
     */
    private function getReaderType(\SplFileInfo $Info)
    {

        $ReaderList = array(
            'Word2007' => array(
                'docx',
            ),
            'MsDoc'    => array(
                'doc',
            )
        );

        $ReaderType = null;
        $Extension = $Info->getExtension();
        array_walk($ReaderList, function ($TypeList, $Reader) use (&$ReaderType, $Extension) {

            if (in_array($Extension, $TypeList)) {
                $ReaderType = $Reader;
            }
        });
        return $ReaderType;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return PhpWord
     * @throws TypeFileException
     */
    public function saveFile(FileParameter $Location = null)
    {

        if (null === $Location) {
            $Info = $this->getFileParameter()->getFileInfo();
        } else {
            $Info = $Location->getFileInfo();
        }

        $WriterType = $this->getWriterType($Info);

        if (null === $Location) {
            $Location = $this->getFileParameter();
        } else {
            $Location = $Location->getFile();
        }

        if ($WriterType) {
            $Writer = IOFactory::createWriter($this->Source, $WriterType);
            $Writer->save($Location);
        } else {
            // @codeCoverageIgnoreStart
            throw new TypeFileException('No Writer for '.$Info->getExtension().' available!');
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    /**
     * @param \SplFileInfo $Info
     *
     * @return null|string
     */
    private function getWriterType(\SplFileInfo $Info)
    {

        $WriterList = array(
            'Word2007' => array(
                'docx',
            ),
        );

        $WriterType = null;
        $Extension = $Info->getExtension();
        array_walk($WriterList, function ($TypeList, $Writer) use (&$WriterType, $Extension) {

            if (in_array($Extension, $TypeList)) {
                $WriterType = $Writer;
            }
        });
        return $WriterType;
    }
}
