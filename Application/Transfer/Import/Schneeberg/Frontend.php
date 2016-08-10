<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 29.06.2016
 * Time: 08:06
 */

namespace SPHERE\Application\Transfer\Import\Schneeberg;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStudentImport($File = null)
    {

        $View = new Stage('Import Schneeberg', 'Schüler-Daten Oberschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createStudentsFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStaffImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Schneeberg Import');
        $View->setDescription('Mitarbeiter/Lehrer Oberschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createStaffsFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendInterestedPersonImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Schneeberg Import');
        $View->setDescription('Interessentendaten Oberschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createInterestedPersonsFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStudentImportPrimarySchool($File = null)
    {

        $View = new Stage('Import Schneeberg', 'Schüler-Daten Grundschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createStudentsPrimarySchoolFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     * @param null $IsMother
     *
     * @return Stage
     */
    public function frontendCustodyImport($File = null, $IsMother = null)
    {

        $View = new Stage('Import Schneeberg', ($IsMother ? 'Mütter ' : 'Väter ') . 'Grundschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createCustodiesFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File, $IsMother
                                ),
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendContactImport($File = null)
    {

        $View = new Stage('Import Schneeberg', 'Kontaktdaten Grundschule');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Schneeberg::useService()->createContactsFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }

}