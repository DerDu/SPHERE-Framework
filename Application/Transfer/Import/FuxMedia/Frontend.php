<?php

namespace SPHERE\Application\Transfer\Import\FuxMedia;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendStudent($Select = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Schülerdaten');

        $tblYearAll = Term::useService()->getYearAll();
        $tblTypeAll = Type::useService()->getTypeAll();

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                        new Well(
                            FuxSchool::useService()->getTypeAndYear(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new SelectBox('Select[Year]', 'Schuljahr',
                                                    array('{{Name}}' => $tblYearAll)),
                                                6
                                            ),
                                            new FormColumn(
                                                new SelectBox('Select[Type]', 'Schulart',
                                                    array('{{Name}}' => $tblTypeAll)),
                                                6
                                            )
                                        )),
                                    ))
                                    , new Primary('Auswählen', new Select())
                                ), $Select, '/Transfer/Import/FuxMedia/Student/Import'
                            )
                        )
                    )
                )
            )))
        );

        return $View;
    }

    /**
     * @param UploadedFile|null $File
     * @param null              $TypeId
     * @param null              $YearId
     *
     * @return Stage
     */
    public function frontendStudentImport(UploadedFile $File = null, $TypeId = null, $YearId = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Schülerdaten');

        $tblType = $tblYear = null;
        if ($TypeId !== null) {
            $tblType = Type::useService()->getTypeById($TypeId);
        }
        if ($YearId !== null) {
            $tblYear = Term::useService()->getYearById($YearId);
        }

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Schuljahr:', $tblYear ? $tblYear->getDisplayName() : '',
                        Panel::PANEL_TYPE_INFO), 6),
                new LayoutColumn(
                    new Panel('Schulart:', $tblType ? $tblType->getName() : '',
                        Panel::PANEL_TYPE_INFO), 6),
                new LayoutColumn(
                    new Well(
                        FuxSchool::useService()->createStudentsFromFile(
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                        )
                                    )
                                )
                                , new Primary('Hochladen')
                            ), $File, $TypeId, $YearId
                        )
                        . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                    )
                )
            ))))
        );

        return $View;
    }

    /**
     * @param UploadedFile|null $File
     *
     * @return Stage
     */
    public function frontendTeacherImport(UploadedFile $File = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Lehrerdaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                FuxSchool::useService()->createTeachersFromFile(
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
                                . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        )
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $Select
     *
     * @return Stage
     */
    public function frontendDivision($Select = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Klassendaten');

        $tblYearAll = Term::useService()->getYearAll();
        $tblTypeAll = Type::useService()->getTypeAll();

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                        new Well(
                            FuxSchool::useService()->getTypeAndYear(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new SelectBox('Select[Year]', 'Schuljahr',
                                                    array('{{Name}}' => $tblYearAll)),
                                                6
                                            ),
                                            new FormColumn(
                                                new SelectBox('Select[Type]', 'Schulart',
                                                    array('{{Name}}' => $tblTypeAll)),
                                                6
                                            )
                                        )),
                                    ))
                                    , new Primary('Auswählen', new Select())
                                ), $Select, '/Transfer/Import/FuxMedia/Division/Import'
                            )
                        )
                    )
                )
            )))
        );

        return $View;
    }

    /**
     * @param UploadedFile|null $File
     * @param null              $TypeId
     * @param null              $YearId
     *
     * @return Stage
     */
    public function frontendDivisionImport(UploadedFile $File = null, $TypeId = null, $YearId = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Klassendaten');

        $tblType = $tblYear = null;
        if ($TypeId !== null) {
            $tblType = Type::useService()->getTypeById($TypeId);
        }
        if ($YearId !== null) {
            $tblYear = Term::useService()->getYearById($YearId);
        }

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Schuljahr:', $tblYear ? $tblYear->getDisplayName() : '',
                        Panel::PANEL_TYPE_INFO), 6),
                new LayoutColumn(
                    new Panel('Schulart:', $tblType ? $tblType->getName() : '',
                        Panel::PANEL_TYPE_INFO), 6),
                new LayoutColumn(
                    new Well(
                        FuxSchool::useService()->createDivisionsFromFile(
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                        )
                                    )
                                )
                                , new Primary('Hochladen')
                            ), $File, $TypeId, $YearId
                        )
                        . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                    )
                )
            ))))
        );

        return $View;
    }

    /**
     * @param UploadedFile|null $File
     *
     * @return Stage
     */
    public function frontendCompanyImport(UploadedFile $File = null)
    {

        $View = new Stage();
        $View->setTitle('FuxSchool Import');
        $View->setDescription('Institutionendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                FuxSchool::useService()->createCompaniesFromFile(
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
                                . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        )
                    )
                )
            )
        );

        return $View;
    }
}
