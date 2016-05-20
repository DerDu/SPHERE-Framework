<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.05.2016
 * Time: 08:14
 */

namespace SPHERE\Application\People\Meta\Teacher;

use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array $Meta
     * @param null $Group
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array(), $Group = null)
    {

        $Stage = new Stage();

        $Stage->setDescription(
            new Danger(
                new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta'])) {
                /** @var TblTeacher $tblTeacher */
                $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                if ($tblTeacher) {
                    $Global->POST['Meta']['Acronym'] = $tblTeacher->getAcronym();
                    $Global->savePost();
                }
            }
        }

        $Stage->setContent(
            Teacher::useService()->createMeta(
                (new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new Panel('Lehrer', array(
                                    new TextField(
                                        'Meta[Acronym]', 'Kürzel', 'Kürzel'
                                    ),
                                ), Panel::PANEL_TYPE_INFO
                                ), 12),
                        )),
                    )),
                ), new Primary('Speichern', new Save())
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta, $Group)
        );

        return $Stage;
    }
}