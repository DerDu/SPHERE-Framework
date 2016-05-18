<?php
namespace SPHERE\Application\Transfer\Import\Indiware;

use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Gateway\Operation\PrepareIndiwareLectureship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Indiware
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param UploadedFile|null $File
     *
     * @return Stage
     */
    public function frontendLectureship(UploadedFile $File = null)
    {

        $Stage = new Stage('Indiware-Import', 'Lehraufträge');

        $FileList = array();
        if (( $tblPartition = Storage::useService()->getPartitionByIdentifier(TblPartition::IDENTIFIER_IMPORT_STORAGE) )) {
            if (( $tblDirectoryAll = Storage::useService()->getDirectoryAllByPartition($tblPartition) )) {
                array_walk($tblDirectoryAll, function (TblDirectory $tblDirectory) use (&$FileList) {

                    if ($tblDirectory->getName() == Account::useService()->getAccountBySession()->getUsername()) {
                        if (( $tblDirectoryAll = Storage::useService()->getDirectoryAllByParent($tblDirectory) )) {
                            array_walk($tblDirectoryAll, function (TblDirectory $tblDirectory) use (&$FileList) {

                                if ($tblDirectory->getName() == 'INDIWARE-LECTURESHIP') {
                                    $tblFileAll = Storage::useService()->getFileAllByDirectory($tblDirectory);
                                    array_walk($tblFileAll, function (TblFile $tblFile) use (&$FileList) {

                                        $FileList[] = array(
                                            'File'   => $tblFile->getName().' '.new Muted($tblFile->getDescription()),
                                            'Option' => new Standard(
                                                'Importieren',
                                                '/Transfer/Import/Indiware/Lectureship/Prepare',
                                                new ChevronRight(), array(
                                                    'Id' => $tblFile->getId()
                                                )
                                            )
                                        );
                                    });
                                }
                            });
                        }
                    }
                });
            }
        }

        $Layout = new Layout(array(
            new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                Indiware::useService()->uploadLectureshipFile(
                    new Form(new FormGroup(new FormRow(new FormColumn(array(
                        new FileUpload('File', 'Indiware-Datei', 'Indiware-Datei', null, array('showPreview' => false))
                    )))), new Primary('Hochladen', new Upload())), $File)
            ))), new Title(new PlusSign().' Neue Datei', 'Hochladen')),
            new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new TableData($FileList, null, array('File' => 'Datei', 'Option' => ''))
            ))), new Title('Hochgeladene Datei', 'Importieren')),
        ));

        $Stage->setContent($Layout);

        return $Stage;
    }

    /**
     * @param null|int $Id
     *
     * @return Stage
     */
    public function frontendLectureshipPrepare($Id = null)
    {

        $Stage = new Stage('Indiware-Import', 'Lehraufträge');

        $tblFile = Storage::useService()->getFileById($Id);
        $Dummy = $tblFile->getDummyFile();

        $tblYear = Term::useService()->getYearById(3);
        Debugger::screenDump($tblYear);

        $Converter = new PrepareIndiwareLectureship($Dummy->getRealPath(), $tblYear);

        return $Stage;
    }
}
