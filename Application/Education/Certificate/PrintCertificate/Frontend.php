<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:33
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPrintCertificate()
    {

        $Stage = new Stage('Zeugnis', 'Übersicht (nicht gedruckte Zeugnisse)');
        $Stage->addButton(new Standard('Historie', '/Education/Certificate/PrintCertificate/History', null, array(),
            'Bereits gedruckte Zeugnisse ansehen und drucken'));

        // freigebene und nicht gedruckte Zeugnisse
        $tableContent = array();
        $tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllWhere(true, false);
        $prepareList = array();
        if ($tblPrepareStudentList) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                ) {
                    if (!isset($prepareList[$tblPrepare->getId()])) {
                        $prepareList[$tblPrepare->getId()] = $tblPrepare;
                    }
                }
            }
        }

        /** @var TblPrepareCertificate $tblPrepare */
        foreach ($prepareList as $tblPrepare) {
            if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                $tableContent[] = array(
                    'Year' => $tblDivision->getServiceTblYear()
                        ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                    'Division' => $tblDivision->getDisplayName(),
                    'Name' => $tblPrepare->getName(),
                    'Option' => new Standard(
                        'Zeugnisse herunterladen und revisionssicher speichern',
                        '/Education/Certificate/PrintCertificate/Confirm',
                        new Download(),
                        array(
                            'PrepareId' => $tblPrepare->getId(),
                        ))
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($tableContent)
                                ? new Warning('Keine Zeugnisse zum Druck verfügbar', new Exclamation())
                                : new TableData(
                                $tableContent,
                                null,
                                array(
                                    'Year' => 'Schuljahr',
                                    'Division' => 'Klasse',
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc'),
                                        array(1, 'asc'),
                                        array(2, 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => 1),
                                    )
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PrepareId
     *
     * @return Stage
     */
    public function frontendConfirmPrintCertificate($PrepareId = null)
    {

        $Stage = new Stage('Zeugnis', 'Herunterladen und revisionssicher abspeichern');

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
            ));

            $data = array();
            if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                        && $tblPrepareStudent->getServiceTblCertificate()
                        && $tblPrepareStudent->isApproved()
                        && !$tblPrepareStudent->isPrinted()
                    ) {
                        $data[] = $tblPerson->getLastFirstName();
                    }
                }
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                    new Panel(
                        'Klasse',
                        $tblDivision->getDisplayName(),
                        Panel::PANEL_TYPE_INFO
                    ),
                    new Panel(
                        new Question() . ' Dieses Zeugnis wirklich drucken und revisionssicher abspeichern?',
                        $data,
                        Panel::PANEL_TYPE_DANGER,
                        (new External(
                            'Ja',
                            '/Api/Education/Certificate/Generator/DownloadZip',
                            new Ok(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                            ),
                            'Zeugnisse herunterladen und revisionssicher abspeichern'))
                            ->setRedirect('/Education/Certificate/PrintCertificate', 20)
                        . new Standard(
                            'Nein', '/Education/Certificate/PrintCertificate', new Disable()
                        )
                    ),
                )))))
            );
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Das Zeugnis konnte nicht gefunden werden'),
                        new Redirect('/Education/Certificate/PrintCertificate', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendPrintCertificateHistory()
    {

        $Stage = new Stage('Zeugnis', 'Person auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/PrintCertificate', new ChevronLeft()
        ));

        $tblFileList = Storage::useService()->getCertificateRevisionFileAll();

        $personList = array();
        if ($tblFileList) {
            foreach ($tblFileList as $tblFile) {
                if (strpos($tblFile->getTblDirectory()->getIdentifier(), 'TBL-PERSON-ID:') !== false) {
                    $personId = substr($tblFile->getTblDirectory()->getIdentifier(), strlen('TBL-PERSON-ID:'));
                    if (Person::useService()->getPersonById($personId)) {
                        if (isset($personList[$personId])) {
                            $personList[$personId] = $personList[$personId] + 1;
                        } else {
                            $personList[$personId] = 1;
                        }
                    }
                }
            }
        }

        $dataList = array();
        foreach ($personList as $key => $value) {
            if (($tblPerson = Person::useService()->getPersonById($key))) {
                $dataList[] = array(
                    'Name' => $tblPerson->getLastFirstName(),
                    'Address' => $tblPerson->fetchMainAddress() ? $tblPerson->fetchMainAddress()->getGuiString() : '',
                    'Count' => $value,
                    'Option' => new Standard(
                        '',
                        '/Education/Certificate/PrintCertificate/History/Person',
                        new Select(),
                        array(
                            'PersonId' => $tblPerson->getId()
                        ),
                        'Person auswählen'
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            empty($dataList)
                                ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                : new TableData(
                                $dataList, null, array(
                                    'Name' => 'Name',
                                    'Address' => 'Adresse',
                                    'Count' => 'Zeugnisse',
                                    'Option' => ''
                                )
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function frontendPrintCertificateHistoryPerson($PersonId = null)
    {

        $Stage = new Stage('Zeugnis', 'Auswahl');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/PrintCertificate/History', new ChevronLeft()));

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson);
            $dataList = array();
            if ($tblFileList) {
                foreach ($tblFileList as $tblFile) {
                    $name = explode(' - ', $tblFile->getName());
                    if (count($name) == 3) {
                        $dataList[] = array(
                            'Year' => $name[0],
                            'Date' => $tblFile->getEntityCreate()->format('d.m.Y'),
                            'Certificate' => $name[2],
                            'Option' => new External(
                                'Herunterladen',
                                '/Api/Education/Certificate/Generator/Download',
                                new Download(),
                                array(
                                    'FileId' => $tblFile->getId(),
                                ),
                                'Zeugnis herunterladen')
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Person',
                                    $tblPerson->getLastFirstName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                empty($dataList)
                                    ? new Warning('Keine Zeugnisse vorhanden', new Exclamation())
                                    : new TableData(
                                    $dataList, null, array(
                                    'Year' => 'Jahr',
                                    'Date' => 'Gedruckt am',
                                    'Certificate' => 'Zeugnis',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 1)
                                        )
                                    )
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage
            . new Danger('Person nicht gefunden', new Ban())
            . new Redirect('/Education/Certificate/PrintCertificate/History', Redirect::TIMEOUT_ERROR);
        }
    }

}