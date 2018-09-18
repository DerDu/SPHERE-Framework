<?php

namespace SPHERE\Application\Api\People\Meta\Support;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiSupportReadOnly extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openOverViewModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverOverViewModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalOverViewReciever');
    }


    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenOverViewModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openOverViewModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openOverViewModal($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if(!$tblPerson){
            $HeadPanel = new Warning('Person wurde nicht gefunden');
            $WellFocus = '';
            $WellDisorder = '';
            $WellHandyCap = '';
            $WellLegalBasis = new LayoutColumn('');
            $WellLearnTarget = new LayoutColumn('');
        } else {
            $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
            $WellFocus = new Well('Keine Förderschwerpunkte');
            if(($tblSupport = Student::useService()->getSupportByPersonNewest($tblPerson, array('Förderbescheid')))){
                $WellFocus = new Title('Förderschwerpunkte:');
                if(($tblFocusPrimary = Student::useService()->getPrimaryFocusBySupport($tblSupport))) {
                    $WellFocus .= new Container(new Bold($tblFocusPrimary->getName()));
                }
                if(($tblFocusList = Student::useService()->getFocusListBySupport($tblSupport))){
                    foreach($tblFocusList as $tblFocus){
                        // doppelter Focus nicht doppelt abbilden
                        if($tblFocusPrimary->getId() != $tblFocus->getId()){
                            $WellFocus .= new Container($tblFocus->getName());
                        }
                    }
                    $WellFocus .= new Ruler().new Container(new Bold('letzter Bearbeiter: ').$tblSupport->getPersonEditor());
                }
                $WellFocus = new Well($WellFocus);
            }

            $WellLegalBasis = new LayoutColumn('');
            $WellLearnTarget = new LayoutColumn('');

            $WellDisorder = new Well('Keine Entwicklungsbesonderheiten');
            if(($tblSpecial = Student::useService()->getSpecialByPersonNewest($tblPerson))) {
                $WellDisorder = new Title('Entwicklungsbesonderheiten:');
                if(($tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial))) {
                    foreach ($tblSpecialDisorderTypeList as $tblSpecialDisorderType) {
                        $WellDisorder .= new Container($tblSpecialDisorderType->getName());
                    }
                    $WellDisorder .= new Ruler().new Container(new Bold('letzter Bearbeiter: ').$tblSpecial->getPersonEditor());
                }
                $WellDisorder = new Well($WellDisorder);
            }

            $WellHandyCap = new Well('Keine Maßnahmen / Beschluss Klassenkonferenz (Nachteilsausgleich)');
            if(($tblHandyCap = Student::useService()->getHandyCapByPersonNewest($tblPerson))){
                $WellHandyCap = new Title('Maßnahmen / Beschluss Klassenkonferenz (Nachteilsausgleich):');
                $WellHandyCap .= new Container($tblHandyCap->getDate());
                $WellLegalBasis = new LayoutColumn(new Well(new Bold('Rechtliche Grundlagen: ').
                    ($tblHandyCap->getLegalBasis()
                        ? $tblHandyCap->getLegalBasis()
                        : '&nbsp; ---'
                    )), 6);
                $WellLearnTarget = new LayoutColumn(new Well(new Bold('Lernziel: ').
                    ($tblHandyCap->getLearnTarget()
                        ? $tblHandyCap->getLearnTarget()
                        : '&nbsp; ---'
                    )), 6);
                $WellHandyCap .= new Container('&nbsp;');
                $WellHandyCap .= new Container(new Bold('Besonderheiten im Unterricht:'));
                $WellHandyCap .= new Container(($tblHandyCap->getRemarkLesson()
                    ? $tblHandyCap->getRemarkLesson()
                    : '---'
                ));
                $WellHandyCap .= new Container('&nbsp;');
                $WellHandyCap .= new Container(new Bold('Besonderheiten bei Leistungsbewertungen:'));
                $WellHandyCap .= new Container(($tblHandyCap->getRemarkRating()
                    ? $tblHandyCap->getRemarkRating()
                    : '---'
                ));

                $WellHandyCap .= new Ruler().new Container(new Bold('letzter Bearbeiter: ').$tblHandyCap->getPersonEditor());
                $WellHandyCap = new Well($WellHandyCap);
            }
        }


        return new Title('Integration')
        .new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $HeadPanel
                    , 12),
                    new LayoutColumn(
                        $WellFocus
                    , 6),
                    new LayoutColumn(
                        $WellDisorder
                    , 6),
                )),
                new LayoutRow(array(
                    $WellLegalBasis,
                    $WellLearnTarget,
                    new LayoutColumn(
                        $WellHandyCap
                    , 12),
                ))
            ))
        );
    }
}