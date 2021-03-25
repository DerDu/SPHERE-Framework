<?php
namespace SPHERE\Application\Education\Lesson\Course;

use SPHERE\Application\Api\Education\School\ApiCourse;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Course
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendCourse()
    {

        $Stage = new Stage('Berufsbildende Schulen', 'Bildungsgang / Berufsbezeichnung / Ausbildung');

//        $Stage->setMessage(
//            new Warning('Bildungsgänge sind im Moment fest hinterlegt')
//        );
//
//        $Stage->setContent(Main::getDispatcher()->fetchDashboard('School-Course'));

        $receiver = ApiCourse::receiverBlock($this->loadTechnicalCourseTable(), 'TechnicalCourseContent');
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiCourse::receiverModal(),
                            (new Primary(
                                new Plus() . ' Berufsbildenden Bildungsgang hinzufügen',
                                ApiCourse::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiCourse::pipelineOpenCreateTechnicalCourseModal())
                        ))
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            '&nbsp;'
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $receiver
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function loadTechnicalCourseTable()
    {
        $dataList = array();
        if (($tblTechnicalCourseAll = Course::useService()->getTechnicalCourseAll())) {
            foreach ($tblTechnicalCourseAll as $tblTechnicalCourse) {
                $dataList[] = array(
                    'Name' => $tblTechnicalCourse->getName(),
                    'GenderMaleName' => $tblTechnicalCourse->getGenderMaleName(),
                    'GenderFemaleName' => $tblTechnicalCourse->getGenderFemaleName(),
                    'Options' => (new Standard(
                        '',
                        ApiCourse::getEndpoint(),
                        new Edit(),
                        array(),
                        'Bearbeiten'
                    ))->ajaxPipelineOnClick(ApiCourse::pipelineOpenEditTechnicalCourseModal($tblTechnicalCourse->getId()))
                );
            }
        }

        $columns = array(
            'Name' => 'Name',
            'GenderMaleName' => 'Männlicher Name',
            'GenderFemaleName' => 'Weiblicher Name',
            'Options' => ' '
        );

        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                ),
                'order' => array(
                    array(0, 'asc')
                ),
            )
        );
    }

    /**
     * @param null $TechnicalCourseId
     * @param false $setPost
     *
     * @return Form
     */
    public function formTechnicalCourse($TechnicalCourseId = null, $setPost = false)
    {
        if ($TechnicalCourseId && ($tblTechnicalCourse = Course::useService()->getTechnicalCourseById($TechnicalCourseId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Name'] = $tblTechnicalCourse->getName();
                $Global->POST['Data']['GenderMaleName'] = $tblTechnicalCourse->getGenderMaleName();
                $Global->POST['Data']['GenderFemaleName'] = $tblTechnicalCourse->getGenderFemaleName();

                $Global->savePost();
            }
        }

        if ($TechnicalCourseId) {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineEditTechnicalCourseSave($TechnicalCourseId));
        } else {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineCreateTechnicalCourseSave());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Name]', 'Ergotherapeut/in', 'Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[GenderMaleName]', 'Ergotherapeut', 'Männlicher Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[GenderFemaleName]', 'Ergotherapeutin', 'Weiblicher Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @return Stage
     */
    public function frontendSubjectArea()
    {

        $Stage = new Stage('Berufsbildende Schulen', 'Fachrichtung');

        $receiver = ApiCourse::receiverBlock($this->loadTechnicalSubjectAreaTable(), 'TechnicalSubjectAreaContent');
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiCourse::receiverModal(),
                            (new Primary(
                                new Plus() . ' Fachrichtung hinzufügen',
                                ApiCourse::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiCourse::pipelineOpenCreateTechnicalSubjectAreaModal())
                        ))
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            '&nbsp;'
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $receiver
                        )
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function loadTechnicalSubjectAreaTable()
    {
        $dataList = array();
        if (($tblTechnicalSubjectAreaAll = Course::useService()->getTechnicalSubjectAreaAll())) {
            foreach ($tblTechnicalSubjectAreaAll as $tblTechnicalSubjectArea) {
                $dataList[] = array(
                    'Acronym' => $tblTechnicalSubjectArea->getAcronym(),
                    'Name' => $tblTechnicalSubjectArea->getName(),
                    'Options' => (new Standard(
                        '',
                        ApiCourse::getEndpoint(),
                        new Edit(),
                        array(),
                        'Bearbeiten'
                    ))->ajaxPipelineOnClick(ApiCourse::pipelineOpenEditTechnicalSubjectAreaModal($tblTechnicalSubjectArea->getId()))
                );
            }
        }

        $columns = array(
            'Acronym' => 'Kürzel',
            'Name' => 'Name',
            'Options' => ' '
        );

        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                ),
                'order' => array(
                    array(0, 'asc')
                ),
            )
        );
    }

    /**
     * @param null $TechnicalSubjectAreaId
     * @param false $setPost
     *
     * @return Form
     */
    public function formTechnicalSubjectArea($TechnicalSubjectAreaId = null, $setPost = false)
    {
        if ($TechnicalSubjectAreaId && ($tblTechnicalSubjectArea = Course::useService()->getTechnicalSubjectAreaById($TechnicalSubjectAreaId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Acronym'] = $tblTechnicalSubjectArea->getAcronym();
                $Global->POST['Data']['Name'] = $tblTechnicalSubjectArea->getName();

                $Global->savePost();
            }
        }

        if ($TechnicalSubjectAreaId) {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineEditTechnicalSubjectAreaSave($TechnicalSubjectAreaId));
        } else {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineCreateTechnicalSubjectAreaSave());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Acronym]', '', 'Kürzel')
                        , 4),
                    new FormColumn(
                        new TextField('Data[Name]', '', 'Name')
                        , 8),
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}
