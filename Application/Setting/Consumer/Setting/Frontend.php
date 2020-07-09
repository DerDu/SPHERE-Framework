<?php

namespace SPHERE\Application\Setting\Consumer\Setting;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Consumer\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendSettings($Data = null)
    {
        $stage = new Stage('Mandant' , 'Einstellungen');

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblIdentification = $tblAccount->getServiceTblIdentification())
            && $tblIdentification->getName() == 'System'
        ) {
            $isSystem = true;

            $stage->setMessage(new Warning(
                'Für den Mandanten Hormersdorf (FESH) gibt es jeweils noch eigene Einstellungen:' . '</br>'
                . '&nbsp;&nbsp;&nbsp;&nbsp; &#9679; Hormersdorf: Schriftgröße des Bemerkungsfeldes für jede einzelne Zeugnisvorlage
                    (RemarkTextSizeHorHj, RemarkTextSizeHorHjOne, RemarkTextSizeHorJ, RemarkTextSizeHorJOne)' . '</br>'
            ));
        } else {
            $isSystem = false;
        }

        if (($tblSettingList = Consumer::useService()->getSettingAll($isSystem))) {
            if ($Data == null) {
                $global = $this->getGlobal();
                foreach ($tblSettingList as $tblSetting) {
                    $global->POST['Data'][$tblSetting->getId()] = $tblSetting->getValue();
                }
                $global->savePost();
            }

            $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_PLZ_ORT_OT_STR_NR, 'PLZ_ORT_OT_STR_NR');
            $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_OT_STR_NR_PLZ_ORT, 'OT_STR_NR_PLZ_ORT');

            $fields = array();
            foreach ($tblSettingList as $tblSetting) {

                // werden automatisch vom System gesetzt
                if ($tblSetting->getIdentifier() == 'InterfaceFilterMessageDate'
                    || $tblSetting->getIdentifier() == 'InterfaceFilterMessageCount'
                ) {
                    continue;
                }

                $public = $isSystem && $tblSetting->isPublic() ? new Globe() . ' ' : '';
                $option = ($isSystem ? new PullRight(new Standard('', '/Setting/Consumer/Setting/AllConsumers',
                    new EyeOpen(), array('SettingId' => $tblSetting->getId()), 'Einstellung für alle Mandanten ansehen')):'');

                $description = $tblSetting->getDescription()
                    ? $public . $tblSetting->getDescription()
                    : $public . $tblSetting->getIdentifier() . ' (keine Beschreibung verfügbar)';

                $field = false;
                if ($tblSetting->getIdentifier() == 'Format_GuiString') {
                    $field = new SelectBox('Data[' . $tblSetting->getId() . ']', $description, array('{{ Name }}' => $selectBoxContent));
                } elseif ($tblSetting->getIdentifier() == 'GenderOfS1') {
                    $field = new SelectBox('Data[' . $tblSetting->getId() . ']', $description, array('{{ Name }}' =>
                        Common::useService()->getCommonGenderAll()
                    ));
                } elseif ($tblSetting->getIdentifier() == 'YearOfUserView') {
                    $field = new SelectBox('Data[' . $tblSetting->getId() . ']', $description, array('{{ Year }}' =>
                        Term::useService()->getYearAll()
                    ));
                } elseif ($tblSetting->getType() == TblSetting::TYPE_BOOLEAN) {
                    $field = new CheckBox('Data[' . $tblSetting->getId() . ']', $description, 1);
                } elseif ($tblSetting->getType() == TblSetting::TYPE_STRING) {
                    $field = new TextField('Data[' . $tblSetting->getId() . ']', '', $description, new Comment());
                } elseif ($tblSetting->getType() == TblSetting::TYPE_INTEGER) {
                    $field = new NumberField('Data[' . $tblSetting->getId() . ']', '', $description, new Quantity());
                }

                if ($field) {
                    if ($isSystem) {
                        $item = new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn($field, 11),
                                    new LayoutColumn($option, 1),
                                ))
                            )
                        );
                    } else {
                        $item = $field;
                    }

                    $fields[$tblSetting->getCategory()][] = $item;
                }
            }

            ksort($fields);
            $formColumns = array();
            foreach ($fields as $category => $content) {
                $isIndividual = $category == 'Individuell';
                $column = new FormColumn(new Panel(
                    $category,
                    $content,
                    $isIndividual ? Panel::PANEL_TYPE_WARNING: Panel::PANEL_TYPE_INFO
                ));

                if ($isIndividual) {
                    array_unshift($formColumns, $column);
                } else {
                    $formColumns[] = $column;
                }
            }

            $form = new Form(new FormGroup(new FormRow($formColumns)));

            $form->appendFormButton(new Primary('Speichern', new Save()));

            $stage->setContent(new Well(
                Consumer::useService()->updateSettingList($form, $Data, $isSystem)
            ));
        }

        return $stage;
    }

    /**
     * @param null $SettingId
     *
     * @return Stage
     */
    public function frontendSettingAllConsumers($SettingId = null)
    {
        $stage = new Stage('Mandant' , 'Einstellungen');
        $stage->addButton(new Standard('Zurück', '/Setting/Consumer/Setting', new ChevronLeft()));
        if (($tblSetting = Consumer::useService()->getSettingById($SettingId))) {
            $content = array();
            if (($tblConsumerAll = \SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer::useService()->getConsumerAll())) {
                //  aktuell nicht genutzte Mandanten
                $blackList = array();
                $blackList['DWO'] = 1;
                $blackList['EMSP'] = 1;
                $blackList['ESA'] = 1;
                $blackList['ESL'] = 1;
                $blackList['ESVL'] = 1;
                $blackList['EVAP'] = 1;
                $blackList['EVMS'] = 1;
                $blackList['EVMSH'] = 1;
                $blackList['EVOSG'] = 1;
                $blackList['EVSB'] = 1;
                $blackList['EVSL'] = 1;
                $blackList['EWM'] = 1;
                $blackList['EWS'] = 1;
                $blackList['FV'] = 1;

                $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_PLZ_ORT_OT_STR_NR, 'PLZ_ORT_OT_STR_NR');
                $selectBoxContent[] = new SelectBoxItem(TblAddress::VALUE_OT_STR_NR_PLZ_ORT, 'OT_STR_NR_PLZ_ORT');

                foreach ($tblConsumerAll as $tblConsumer) {
                    if (!isset($blackList[$tblConsumer->getAcronym()])) {
                        $value =  Consumer::useService()->getSettingByConsumer($tblSetting, $tblConsumer);
                        if ($tblSetting->getIdentifier() == 'Format_GuiString') {
                            if ($value == TblAddress::VALUE_PLZ_ORT_OT_STR_NR) {
                                $value = 'PLZ_ORT_OT_STR_NR';
                            } elseif ($value == TblAddress::VALUE_OT_STR_NR_PLZ_ORT) {
                                $value = 'OT_STR_NR_PLZ_ORT';
                            }
                        } elseif ($tblSetting->getIdentifier() == 'GenderOfS1') {
                            if ($value && ($tblCommonGender = Common::useService()->getCommonGenderById($value))) {
                                $value = $tblCommonGender->getName();
                            }
                        } elseif ($tblSetting->getType() == TblSetting::TYPE_BOOLEAN) {
                            if ($value === '0') {
                                $value = new Unchecked();
                            } elseif ($value === '1') {
                                $value = new Check();
                            }
                        }

                        $content[] = array(
                            'Acronym' => $tblConsumer->getAcronym(),
                            'Name' => $tblConsumer->getName(),
                            'Value' => $value
                        );
                    }
                }

                $stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Panel(
                                        'Mandanteneinstellung',
                                        array(
                                            $tblSetting->getIdentifier(),
                                            $tblSetting->getDescription()
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                )
                            )
                        )
                    )
                    . new TableData(
                        $content,
                        null,
                        array(
                            'Acronym' => 'Kürzel',
                            'Name' => 'Name',
                            'Value' => 'Wert'
                        )
                    )
                );
            }
        }

        return $stage;
    }
}