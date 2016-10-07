<?php
namespace SPHERE\Application\Transfer\Import\Indiware;

use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Gateway\Converter\Error;
use SPHERE\Application\Transfer\Gateway\Operation\PrepareIndiwareLectureship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Indiware
 */
class Frontend extends Extension implements IFrontendInterface
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
        if (($tblPartition = Storage::useService()->getPartitionByIdentifier(TblPartition::IDENTIFIER_IMPORT_STORAGE))) {
            if (($tblDirectoryAll = Storage::useService()->getDirectoryAllByPartition($tblPartition))) {
                array_walk($tblDirectoryAll, function (TblDirectory $tblDirectory) use (&$FileList) {

                    if ($tblDirectory->getName() == Account::useService()->getAccountBySession()->getUsername()) {
                        if (($tblDirectoryAll = Storage::useService()->getDirectoryAllByParent($tblDirectory))) {
                            array_walk($tblDirectoryAll, function (TblDirectory $tblDirectory) use (&$FileList) {

                                if ($tblDirectory->getName() == 'INDIWARE-LECTURESHIP') {
                                    $tblFileAll = Storage::useService()->getFileAllByDirectory($tblDirectory);
                                    array_walk($tblFileAll, function (TblFile $tblFile) use (&$FileList) {

                                        $FileList[] = array(
                                            'File' => $tblFile->getName() . ' ' . new Muted($tblFile->getDescription()),
                                            'Option' => new Standard(
                                                'Importieren',
                                                '/Transfer/Import/Indiware/Lectureship/Year',
                                                new ChevronRight(), array(
                                                    'FileId' => $tblFile->getId()
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
            ))), new Title(new PlusSign() . ' Neue Datei', 'Hochladen')),
            new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                new TableData($FileList, null, array('File' => 'Datei', 'Option' => ''))
            ))), new Title('Hochgeladene Datei', 'Importieren')),
        ));

        $Stage->setContent($Layout);

        return $Stage;
    }

    /**
     * @param null|int $FileId
     * @param null|int $YearId
     *
     * @return Stage
     */
    public function frontendLectureshipPrepare($FileId = null, $YearId = null)
    {

        $Stage = new Stage('Indiware-Import', 'Lehraufträge');

        $tblFile = Storage::useService()->getFileById($FileId);
        $FilePointer = $tblFile->getFilePointer();

        $tblYear = Term::useService()->getYearById($YearId);
        $Stage->setMessage(
            'Schuljahr: ' . $tblYear->getDisplayName()
            . '<br/>'
            . 'Datei: ' . $tblFile->getName()
        );

        $Converter = new PrepareIndiwareLectureship($FilePointer->getRealPath(), $tblYear);

        // TODO: Improve Result
        $Protocol = $Converter->scanFile(1);

//        $this->getDebugger()->screenDump($Protocol);

        $ProtocolStack = array();
        $ProtocolLayoutError = array();
        $ProtocolLayoutSuccess = array();
        $ErrorImport = false;
        foreach ($Protocol as $LineIndex => $TypeList) {
            $UniqueProtocol = array();
            if (isset($TypeList['ERROR'])) {
                $ErrorImport = true;
                $MessageList = $TypeList['ERROR'];
                /** @var Error $Message */
                foreach ($MessageList as $Message) {
                    if (
                        !in_array($Message, $UniqueProtocol)
                        && !in_array($Message, $ProtocolStack)
//                        && false !== strpos($Message->getDescription(), 'ist in KREDA nicht vorhanden')
                    ) {
                        $ProtocolStack[] = $Message;
                        $UniqueProtocol[] = $Message;
                    }
                }
                $UniqueProtocol = $this->getSorter($UniqueProtocol)->sortObjectBy(
                    'Description', new Sorter\Object\StringNaturalOrderSorter()
                );
                if (!empty($UniqueProtocol)) {
                    $ProtocolLayoutError[] = new Panel('Zeile: ' . $LineIndex, $UniqueProtocol,
                        Panel::PANEL_TYPE_DANGER);
                }
            }
        }
        if (!$ErrorImport) {
            /*
             * Existing Lectorship
             */
            $existingLectureShipList = array();
            foreach ($Protocol as $LineIndex => $TypeList) {
                if (isset($TypeList['VALID'])) {

                    /** @var TblDivision[]|TblSubject[]|TblPerson[]|TblSubjectGroup[]|array $Lectureship */
                    foreach ($TypeList['VALID'] as $Lectureship) {
                        if( !is_array( $Lectureship ) ) {
                            continue;
                        }

                        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision(
                                $Lectureship['TblDivision'])
                        )) {
                            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                                if (($tblDivision = $tblDivisionSubjectItem->getTblDivision())
                                    && ($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                    && ($tblSubjectTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubjectItem))
                                ) {
                                    foreach ($tblSubjectTeacherList as $tblSubjectTeacher) {
                                        if (($tblPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                                            if (!isset($existingLectureShipList[$tblDivision->getId()][$tblSubject->getId()]
                                                [$tblDivisionSubjectItem->getTblSubjectGroup()
                                                    ? $tblDivisionSubjectItem->getTblSubjectGroup()->getId() : 0]
                                                [$tblPerson->getId()]
                                            )
                                            ) {
                                                $existingLectureShipList[$tblDivision->getId()][$tblSubject->getId()]
                                                [$tblDivisionSubjectItem->getTblSubjectGroup()
                                                    ? $tblDivisionSubjectItem->getTblSubjectGroup()->getId() : 0]
                                                [$tblPerson->getId()] = true;
                                            }
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }

//            Debugger::screenDump($existingLectureShipList);

            foreach ($Protocol as $LineIndex => $TypeList) {
                if (isset($TypeList['VALID'])) {

                    $SuccessProtocol = $this->createLectureship($TypeList['VALID'], $existingLectureShipList);
                    $ProtocolLayoutSuccess[] = new Panel('Zeile: ' . $LineIndex, $SuccessProtocol,
                        Panel::PANEL_TYPE_SUCCESS);
                }
            }

            /**
             * Delete Lectorship
             */
            $deleteList = array();
            if (!empty($existingLectureShipList)) {
                foreach ($existingLectureShipList as $divisionId => $subjectArray) {
                    if (($tblDivision = Division::useService()->getDivisionById($divisionId))
                        && is_array($subjectArray)
                    ) {
                        foreach ($subjectArray as $subjectId => $groupArray) {
                            if (($tblSubject = Subject::useService()->getSubjectById($subjectId))
                                && is_array($groupArray)
                            ) {
                                foreach ($groupArray as $groupId => $personArray) {
                                    $tblSubjectGroup = false;
                                    if (($groupId == 0
                                            || $tblSubjectGroup = Division::useService()->getSubjectGroupById($groupId))
                                        && is_array($personArray)
                                    ) {
                                        foreach ($personArray as $personId => $value) {
                                            if (($tblPerson = Person::useService()->getPersonById($personId))
                                                && $value
                                            ) {

                                                if (($tblDivisionSubject = Division::useService()->getDivisionSubject(
                                                    $tblDivision, $tblSubject,
                                                    $tblSubjectGroup ? $tblSubjectGroup : null))
                                                    && ($tblSubjectTeacher = Division::useService()->getSubjectTeacherBy(
                                                        $tblDivisionSubject, $tblPerson))

                                                ) {
                                                    Division::useService()->removeSubjectTeacher($tblSubjectTeacher);

                                                    $deleteList[] =
                                                        new Muted('Klasse: ') . $tblDivision->getDisplayName() .
                                                        new Muted(' Fach: ') . $tblSubject->getName() .
                                                        ($tblSubjectGroup ? new Muted(' Gruppe: ') . $tblSubjectGroup->getName() : '') .
                                                        new Muted(' Person: ') . $tblPerson->getFullName();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $Layout = new Layout(array(
            $LayoutGroupError = new LayoutGroup(array(), new Title('Indiware-Import Fehler ',
                ($ErrorImport ? count($ProtocolLayoutError) . ' Meldungen (zusammengefasst)' : 'Keine Fehler gefunden.'))),
            $LayoutGroupSuccess = new LayoutGroup(array(), new Title('Indiware-Import Ergebnis',
                ($ErrorImport ? 'Import nicht durchgeführt!' : count($ProtocolLayoutSuccess) . ' Datensätze importiert')
                . (empty($deleteList) ? '' :  ', ' . count($deleteList) . ' Lehraufträge gelöscht')
            )),
        ));

        // ERROR
        $LayoutRow = new LayoutRow(array(
            $Column1 = new LayoutColumn(array(), 3),
            $Column2 = new LayoutColumn(array(), 3),
            $Column3 = new LayoutColumn(array(), 3),
            $Column4 = new LayoutColumn(array(), 3),
        ));
        $LayoutGroupError->addRow($LayoutRow);
        $StepColumn = 1;
        foreach ($ProtocolLayoutError as $Index => $Panel) {
            if ($StepColumn > 4) {

                $LayoutRow = new LayoutRow(array(
                    $Column1 = new LayoutColumn(array(), 3),
                    $Column2 = new LayoutColumn(array(), 3),
                    $Column3 = new LayoutColumn(array(), 3),
                    $Column4 = new LayoutColumn(array(), 3),
                ));
                $LayoutGroupError->addRow($LayoutRow);

                $StepColumn = 1;
            }
            ${'Column' . $StepColumn}->addFrontend($Panel);

            $StepColumn++;
        }
        // SUCCESS
        $LayoutRow = new LayoutRow(array(
            $Column1 = new LayoutColumn(array(), 3),
            $Column2 = new LayoutColumn(array(), 3),
            $Column3 = new LayoutColumn(array(), 3),
            $Column4 = new LayoutColumn(array(), 3),
        ));
        $LayoutGroupSuccess->addRow($LayoutRow);
        $StepColumn = 1;
        foreach ($ProtocolLayoutSuccess as $Index => $Panel) {
            if ($StepColumn > 4) {

                $LayoutRow = new LayoutRow(array(
                    $Column1 = new LayoutColumn(array(), 3),
                    $Column2 = new LayoutColumn(array(), 3),
                    $Column3 = new LayoutColumn(array(), 3),
                    $Column4 = new LayoutColumn(array(), 3),
                ));
                $LayoutGroupSuccess->addRow($LayoutRow);

                $StepColumn = 1;
            }
            ${'Column' . $StepColumn}->addFrontend($Panel);

            $StepColumn++;
        }
        if (!empty($deleteList)){
            $LayoutGroupSuccess->addRow(new LayoutRow(new LayoutColumn(new Panel(
                'Gelöschte Lehraufträge',
                $deleteList,
                Panel::PANEL_TYPE_DANGER
            ))));
        }
        $Stage->setContent($Layout);

        return $Stage;
    }

    /**
     * @param array $LectureshipList
     * @param array $ExistingLectureshipList
     *
     * @return array
     */
    private function createLectureship($LectureshipList, &$ExistingLectureshipList)
    {
        $SuccessList = array();
        /** @var TblDivision[]|TblSubject[]|TblPerson[]|TblSubjectGroup[]|array $Lectureship */
        foreach ($LectureshipList as $Lectureship) {
            if( !is_array( $Lectureship ) ) {
                continue;
            }

            if (isset($ExistingLectureshipList
                    [$Lectureship['TblDivision']->getId()]
                    [$Lectureship['TblSubject']->getId()]
                    [isset($Lectureship['TblSubjectGroup']) ? $Lectureship['TblSubjectGroup']->getId() : 0]
                    [$Lectureship['TblPerson']->getId()])
            ) {
                $ExistingLectureshipList
                [$Lectureship['TblDivision']->getId()]
                [$Lectureship['TblSubject']->getId()]
                [isset($Lectureship['TblSubjectGroup']) ? $Lectureship['TblSubjectGroup']->getId() : 0]
                [$Lectureship['TblPerson']->getId()] = false;
            }

            // Subject-Teacher
            if (count($Lectureship) == 3) {
                // Teacher not in Division-Subject? -> ADD
                if (!Division::useService()->checkSubjectTeacherExists(
                    $Lectureship['TblDivision'],
                    $Lectureship['TblSubject'],
                    $Lectureship['TblPerson'],
                    null
                )
                ) {
                    if (($tblDivisionSubject = Division::useService()->getDivisionSubject(
                        $Lectureship['TblDivision'],
                        $Lectureship['TblSubject'],
                        null
                    ))
                    ) {
                        Division::useService()->createSubjectTeacher(
                            $tblDivisionSubject,
                            $Lectureship['TblPerson']
                        );
                        $SuccessList[] = new Success(
                            new Bold(' Klasse: ') . $Lectureship['TblDivision']->getDisplayName()
                            . '<br/>' . new Bold(' Fach: ') . $Lectureship['TblSubject']->getAcronym() . ' (' . $Lectureship['TblSubject']->getName() . ')'
                            . '<br/>' . new Bold(' Lehrer: ') . $Lectureship['TblPerson']->getFullName()
                        );
                        $SuccessList[] = new Warning(new Transfer() . ' Verknüpfungen angelegt');
                    }
                } else {
                    $SuccessList[] = new Success(
                        new Bold(' Klasse: ') . $Lectureship['TblDivision']->getDisplayName()
                        . '<br/>' . new Bold(' Fach: ') . $Lectureship['TblSubject']->getAcronym() . ' (' . $Lectureship['TblSubject']->getName() . ')'
                        . '<br/>' . new Bold(' Lehrer: ') . $Lectureship['TblPerson']->getFullName()
                    );
                    $SuccessList[] = new Info(new Transfer() . ' Verknüpfungen bereits vorhanden');
                }
                // Subject-Group-Teacher
            } elseif (count($Lectureship) == 4) {
                // Teacher not in Division-Subject-Group? -> ADD
                if (!Division::useService()->checkSubjectTeacherExists(
                    $Lectureship['TblDivision'],
                    $Lectureship['TblSubject'],
                    $Lectureship['TblPerson'],
                    $Lectureship['TblSubjectGroup']
                )
                ) {
                    if (($tblDivisionSubject = Division::useService()->getDivisionSubject(
                        $Lectureship['TblDivision'],
                        $Lectureship['TblSubject'],
                        $Lectureship['TblSubjectGroup']
                    ))
                    ) {
                        Division::useService()->createSubjectTeacher(
                            $tblDivisionSubject,
                            $Lectureship['TblPerson']
                        );
                        $SuccessList[] = new Success(
                            new Bold(' Klasse: ') . $Lectureship['TblDivision']->getDisplayName()
                            . '<br/>' . new Bold(' Fach: ') . $Lectureship['TblSubject']->getAcronym() . ' (' . $Lectureship['TblSubject']->getName() . ')'
                            . '<br/>' . new Bold(' Gruppe: ') . $Lectureship['TblSubjectGroup']->getName()
                            . '<br/>' . new Bold(' Lehrer: ') . $Lectureship['TblPerson']->getFullName()
                        );
                        $SuccessList[] = new Warning(new Transfer() . ' Verknüpfungen angelegt');
                    }
                } else {
                    $SuccessList[] = new Success(
                        new Bold(' Klasse: ') . $Lectureship['TblDivision']->getDisplayName()
                        . '<br/>' . new Bold(' Fach: ') . $Lectureship['TblSubject']->getAcronym() . ' (' . $Lectureship['TblSubject']->getName() . ')'
                        . '<br/>' . new Bold(' Gruppe: ') . $Lectureship['TblSubjectGroup']->getName()
                        . '<br/>' . new Bold(' Lehrer: ') . $Lectureship['TblPerson']->getFullName()
                    );
                    $SuccessList[] = new Info(new Transfer() . ' Verknüpfungen bereits vorhanden');
                }
            } else {
                // TODO ERROR
            }
        }

        return $SuccessList;
    }

    /**
     * @param null $FileId
     *
     * @return Stage
     */
    public function frontendSelectYear($FileId = null)
    {

        $Stage = new Stage('Indiware-Import', 'Schuljahr auswählen');

        $yearList = array();
        if (($tblYearAll = Term::useService()->getYearAll())) {
            foreach ($tblYearAll as $tblYear) {
                $yearList[] = array(
                    'Name' => $tblYear->getDisplayName(),
                    'Option' => new Standard(
                        '',
                        '/Transfer/Import/Indiware/Lectureship/Prepare',
                        new Select(),
                        array(
                            'FileId' => $FileId,
                            'YearId' => $tblYear->getId()
                        ),
                        'Auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($yearList, null, array(
                                'Name' => 'Schuljahr',
                                'Option' => 'Auswählen'
                            ), array(
                                'order' => array(
                                    array('0', 'desc')
                                )
                            ))
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }
}
