<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 06.07.2016
 * Time: 08:53
 */

namespace SPHERE\Application\Transfer\Import\Annaberg;

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

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Import\Annaberg
 */
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

        $View = new Stage('Import Annaberg', 'Schüler');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Annaberg::useService()->createStudentsFromFile(new Form(
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
        $View->setTitle('Import Annaberg');
        $View->setDescription('Mitarbeiter/Lehrer');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Annaberg::useService()->createStaffsFromFile(new Form(
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
     */
    public function frontendCompanyImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Radebeul Import');
        $View->setDescription('Schulen (Firmen)');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Annaberg::useService()->createCompaniesFromFile(
                                    new Form(
                                        new FormGroup(
                                            new FormRow(
                                                new FormColumn(
                                                    new FileUpload('File', 'Datei auswählen', 'Datei auswählen',
                                                        null,
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

}