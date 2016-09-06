<?php
namespace SPHERE\Application\Document\Explorer;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\FolderClosed;
use SPHERE\Common\Frontend\Icon\Repository\FolderOpen;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTrace;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * @deprecated
 * Class Frontend
 *
 * @package SPHERE\Application\Document\Explorer
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @deprecated
     *
*@param int|null $Id
     *
     * @return Stage
     */
    public function frontendExplorer($Id = null)
    {

        $Stage = new Stage('Explorer');

        // Directory-Current
        if ($Id) {
            $tblDirectoryCurrent = Storage::useService()->getDirectoryById($Id);
        } else {
            // Empty: Not Selected
            $tblDirectoryCurrent = new TblDirectory();
        }

        // Directory-Trace
        $DirectoryTrace = array();
        if ($tblDirectoryCurrent->getId()) {
            $tblDirectoryTrace = $tblDirectoryCurrent;
            array_push($DirectoryTrace, new Link(
                $tblDirectoryCurrent->getName(), $this->getRequest()->getPathInfo(), new FolderOpen(), array(
                'Id' => $tblDirectoryCurrent->getId()
            )));
            /** @var bool|TblDirectory $tblDirectoryParent */
            while (false !== ( $tblDirectoryParent = $tblDirectoryTrace->getTblDirectory() )) {
                array_push($DirectoryTrace, new Link(
                    $tblDirectoryParent->getName(), $this->getRequest()->getPathInfo(), new FolderOpen(), array(
                    'Id' => $tblDirectoryParent->getId()
                )));
                $tblDirectoryTrace = $tblDirectoryParent;
            }
        }
        array_push($DirectoryTrace,
            new Link(( empty( $DirectoryTrace ) ? 'Cloud' : '' ), $this->getRequest()->getPathInfo(), new Home(),
                array('Id' => 0)));
        krsort($DirectoryTrace);

        // Directory-List
        $tblDirectoryAllByParent = Storage::useService()->getDirectoryAllByParent($tblDirectoryCurrent);
        $DirectoryList = array();
        if ($tblDirectoryAllByParent) {
            array_walk($tblDirectoryAllByParent, function (TblDirectory $tblDirectory) use (&$DirectoryList) {

                array_push($DirectoryList,
                    array(
                        'Directory' => new Link(
                            $tblDirectory->getName(), $this->getRequest()->getPathInfo(), new FolderClosed(), array(
                            'Id' => $tblDirectory->getId()
                        ))
                    )
                );
            });
        }

        // File-List TODO: File By Directory
        $tblFileAllByParent = Storage::useService()->getFileAll();
        $FileList = array();
        if ($tblFileAllByParent) {
            array_walk($tblFileAllByParent, function (TblFile $tblFile) use (&$FileList) {

                array_push($FileList,
                    array(
                        'File' => new Link(
                            $tblFile->getName(), $this->getRequest()->getPathInfo(), new Envelope(), array(
                            'Id' => $tblFile->getId()
                        ))
                    )
                );
            });
        }

        $Stage->setMessage(
            new LayoutTrace($DirectoryTrace)
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Verzeichnisse',
                                ( $tblDirectoryCurrent->getId() ? 'in '.$tblDirectoryCurrent->getName() : '' )),
                            new TableData($DirectoryList, null, array(
                                'Directory' => 'Verzeichnis'
                            ))
                        ), 5),
                        new LayoutColumn(array(
                            new Title('Dateien',
                                ( $tblDirectoryCurrent->getId() ? 'in '.$tblDirectoryCurrent->getName() : '' )),
                            new TableData($FileList, null, array(
                                'File' => 'Datei'
                            ))
                        ), 7),
                    ))
                ))
            )

        );

        return $Stage;
    }
}
