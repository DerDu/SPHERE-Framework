<?php


namespace SPHERE\Application\Api\Education\School;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiCourse
 * 
 * @package SPHERE\Application\Api\Education\School
 */
class ApiCourse  extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadTechnicalCourseContent');
        $Dispatcher->registerMethod('openCreateTechnicalCourseModal');
        $Dispatcher->registerMethod('saveCreateTechnicalCourseModal');
        $Dispatcher->registerMethod('openEditTechnicalCourseModal');
        $Dispatcher->registerMethod('saveEditTechnicalCourseModal');

        $Dispatcher->registerMethod('loadTechnicalSubjectAreaContent');
        $Dispatcher->registerMethod('openCreateTechnicalSubjectAreaModal');
        $Dispatcher->registerMethod('saveCreateTechnicalSubjectAreaModal');
        $Dispatcher->registerMethod('openEditTechnicalSubjectAreaModal');
        $Dispatcher->registerMethod('saveEditTechnicalSubjectAreaModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadTechnicalCourseContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TechnicalCourseContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTechnicalCourseContent',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateTechnicalCourseModal()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateTechnicalCourseModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCreateTechnicalCourseSave()
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateTechnicalCourseModal'
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $TechnicalCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditTechnicalCourseModal($TechnicalCourseId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditTechnicalCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TechnicalCourseId' => $TechnicalCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TechnicalCourseId
     *
     * @return Pipeline
     */
    public static function pipelineEditTechnicalCourseSave($TechnicalCourseId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditTechnicalCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'TechnicalCourseId' => $TechnicalCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Danger|TableData
     */
    public function loadTechnicalCourseContent()
    {
        return \SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->loadTechnicalCourseTable();
    }

    /**
     * @return Danger|string
     */
    public function openCreateTechnicalCourseModal()
    {
        return $this->getTechnicalCourseModal(\SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->formTechnicalCourse());
    }

    /**
     * @param $form
     * @param null $TechnicalCourseId
     *
     * @return string
     */
    private function getTechnicalCourseModal($form, $TechnicalCourseId = null)
    {
        if ($TechnicalCourseId) {
            $title = new Title(new Edit() . ' Berufsbildenden Bildungsgang bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Berufsbildenden Bildungsgang hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param array $Data
     *
     * @return Danger|string
     */
    public function saveCreateTechnicalCourseModal($Data = null)
    {
        if (($form = Course::useService()->checkFormTechnicalCourse($Data))) {
            // display Errors on form
            return $this->getTechnicalCourseModal($form);
        }

        if (Course::useService()->createTechnicalCourse($Data['Name'], $Data['GenderMaleName'], $Data['GenderFemaleName'])) {
            return new Success('Der berufsbildende Bildungsgang wurde erfolgreich gespeichert.')
                . self::pipelineLoadTechnicalCourseContent()
                . self::pipelineClose();
        } else {
            return new Danger('Der berufsbildende Bildungsgang konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $TechnicalCourseId
     *
     * @return string
     */
    public function openEditTechnicalCourseModal($TechnicalCourseId)
    {
        if (!($tblTechnicalCourse = Course::useService()->getTechnicalCourseById($TechnicalCourseId))) {
            return new Danger('Der berufsbildende Bildungsgang wurde nicht gefunden', new Exclamation());
        }

        return $this->getTechnicalCourseModal(\SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->formTechnicalCourse(
            $TechnicalCourseId, true
        ), $TechnicalCourseId);
    }

    /**
     * @param $TechnicalCourseId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditTechnicalCourseModal($TechnicalCourseId, $Data)
    {
        if (!($tblTechnicalCourse = Course::useService()->getTechnicalCourseById($TechnicalCourseId))) {
            return new Danger('Der berufsbildende Bildungsgang wurde nicht gefunden', new Exclamation());
        }

        if (($form = Course::useService()->checkFormTechnicalCourse($Data, $tblTechnicalCourse))) {
            // display Errors on form
            return $this->getTechnicalCourseModal($form, $TechnicalCourseId);
        }

        if (Course::useService()->updateTechnicalCourse($tblTechnicalCourse, $Data['Name'], $Data['GenderMaleName'], $Data['GenderFemaleName'])) {
            return new Success('Der berufsbildende Bildungsgang wurde erfolgreich gespeichert.')
                . self::pipelineLoadTechnicalCourseContent()
                . self::pipelineClose();
        } else {
            return new Danger('Der berufsbildende Bildungsgang konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadTechnicalSubjectAreaContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TechnicalSubjectAreaContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTechnicalSubjectAreaContent',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Danger|TableData
     */
    public function loadTechnicalSubjectAreaContent()
    {
        return \SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->loadTechnicalSubjectAreaTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateTechnicalSubjectAreaModal()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateTechnicalSubjectAreaModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Danger|string
     */
    public function openCreateTechnicalSubjectAreaModal()
    {
        return $this->getTechnicalSubjectAreaModal(\SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->formTechnicalSubjectArea());
    }

    /**
     * @param $form
     * @param null $TechnicalSubjectAreaId
     *
     * @return string
     */
    private function getTechnicalSubjectAreaModal($form, $TechnicalSubjectAreaId = null)
    {
        if ($TechnicalSubjectAreaId) {
            $title = new Title(new Edit() . ' Fachrichtung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Fachrichtung hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCreateTechnicalSubjectAreaSave()
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateTechnicalSubjectAreaModal'
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array $Data
     *
     * @return Danger|string
     */
    public function saveCreateTechnicalSubjectAreaModal($Data = null)
    {
        if (($form = Course::useService()->checkFormTechnicalSubjectArea($Data))) {
            // display Errors on form
            return $this->getTechnicalSubjectAreaModal($form);
        }

        if (Course::useService()->createTechnicalSubjectArea($Data['Acronym'], $Data['Name'])) {
            return new Success('Die Fachrichtung wurde erfolgreich gespeichert.')
                . self::pipelineLoadTechnicalSubjectAreaContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Fachrichtung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $TechnicalSubjectAreaId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditTechnicalSubjectAreaModal($TechnicalSubjectAreaId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditTechnicalSubjectAreaModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'TechnicalSubjectAreaId' => $TechnicalSubjectAreaId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TechnicalSubjectAreaId
     *
     * @return string
     */
    public function openEditTechnicalSubjectAreaModal($TechnicalSubjectAreaId)
    {
        if (!($tblTechnicalSubjectArea = Course::useService()->getTechnicalSubjectAreaById($TechnicalSubjectAreaId))) {
            return new Danger('Die Fachrichtung wurde nicht gefunden', new Exclamation());
        }

        return $this->getTechnicalSubjectAreaModal(\SPHERE\Application\Education\Lesson\Course\Course::useFrontend()->formTechnicalSubjectArea(
            $TechnicalSubjectAreaId, true
        ), $TechnicalSubjectAreaId);
    }


    /**
     * @param $TechnicalSubjectAreaId
     *
     * @return Pipeline
     */
    public static function pipelineEditTechnicalSubjectAreaSave($TechnicalSubjectAreaId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditTechnicalSubjectAreaModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'TechnicalSubjectAreaId' => $TechnicalSubjectAreaId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TechnicalSubjectAreaId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditTechnicalSubjectAreaModal($TechnicalSubjectAreaId, $Data)
    {
        if (!($tblTechnicalSubjectArea = Course::useService()->getTechnicalSubjectAreaById($TechnicalSubjectAreaId))) {
            return new Danger('Die Fachrichtung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Course::useService()->checkFormTechnicalSubjectArea($Data, $tblTechnicalSubjectArea))) {
            // display Errors on form
            return $this->getTechnicalSubjectAreaModal($form, $TechnicalSubjectAreaId);
        }

        if (Course::useService()->updateTechnicalSubjectArea($tblTechnicalSubjectArea, $Data['Acronym'], $Data['Name'])) {
            return new Success('Die Fachrichtung wurde erfolgreich gespeichert.')
                . self::pipelineLoadTechnicalSubjectAreaContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Fachrichtung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }
}