<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.12.2018
 * Time: 12:25
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Sheriff;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendCommon
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendCommon extends FrontendReadOnly
{
    const TITLE = 'Personendaten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getCommonContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblCommon = $tblPerson->getCommon())
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonInformation = $tblCommon->getTblCommonInformation())
            ) {
                $birthday = $tblCommonBirthDates->getBirthday();
                $birthplace = $tblCommonBirthDates->getBirthplace();
                $gender = ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())
                    ? $tblCommonGender->getName() : '';

                $nationality = $tblCommonInformation->getNationality();
                $denomination = $tblCommonInformation->getDenomination();
                $isAssistance = $tblCommonInformation->isAssistance();
                if ($isAssistance == TblCommonInformation::VALUE_IS_ASSISTANCE_YES) {
                    $isAssistance = 'Ja';
                } elseif ($isAssistance == TblCommonInformation::VALUE_IS_ASSISTANCE_NO) {
                    $isAssistance = 'Nein';
                } else {
                    $isAssistance = '';
                }
                $assistanceActivity = $tblCommonInformation->getAssistanceActivity();

                $remark = $tblCommon->getRemark();
            } else {
                $birthday = '';
                $birthplace = '';
                $gender = '';

                $nationality = '';
                $denomination = '';
                $isAssistance = '';
                $assistanceActivity = '';

                $remark = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geburtstag'),
                    self::getLayoutColumnValue($birthday),
                    self::getLayoutColumnLabel('Staatsangehörigkeit'),
                    self::getLayoutColumnValue($nationality),
                    self::getLayoutColumnLabel('Mitarbeitbereitschaft'),
                    self::getLayoutColumnValue($isAssistance),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geburtsort'),
                    self::getLayoutColumnValue($birthplace),
                    self::getLayoutColumnLabel('Konfession'),
                    self::getLayoutColumnValue($denomination),
                    self::getLayoutColumnLabel('Mitarbeitbereitschaft - Tätigkeiten'),
                    self::getLayoutColumnValue($assistanceActivity),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Geschlecht'),
                    self::getLayoutColumnValue($gender),
                    self::getLayoutColumnEmpty(8),
                )),
                new LayoutRow(array(
                    self::getLayoutColumnLabel('Bemerkungen'),
                    self::getLayoutColumnValue($remark, 10),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditCommonContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                self::getSubContent('Personendaten', $content),
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditCommonContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();

            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                $Global->POST['Meta']['Remark'] = $tblCommon->getRemark();

                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Meta']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    $Global->POST['Meta']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                    // post für "Gender" wird an anderer Stelle gesetzt
//                    if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
//                        $Global->POST['Meta']['BirthDates']['Gender'] = $tblCommonGender->getId();
//                    }
                }

                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                    $Global->POST['Meta']['Information']['Nationality'] = $tblCommonInformation->getNationality();
                    $Global->POST['Meta']['Information']['Denomination'] = $tblCommonInformation->getDenomination();
                    $Global->POST['Meta']['Information']['IsAssistance'] = $tblCommonInformation->isAssistance();
                    $Global->POST['Meta']['Information']['AssistanceActivity'] = $tblCommonInformation->getAssistanceActivity();
                }

                $Global->savePost();
            }
        }

        return $this->getEditCommonTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditCommonForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param bool $isCreatePerson
     *
     * @return Title|string
     */
    public function getEditCommonTitle(TblPerson $tblPerson = null, $isCreatePerson = false)
    {
        $title = new Title(new Tag() . ' ' . self::TITLE, 'der Person '
            . ($tblPerson ? new Bold(new Success($tblPerson->getFullName())) : '')
            . ($isCreatePerson ? ' anlegen' : ' bearbeiten'));
        if ($isCreatePerson) {
            return $title;
        } else {
            return $title . self::getDataProtectionMessage();
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    public function getEditCommonForm(TblPerson $tblPerson = null)
    {

        $genderId = 0;
        if($tblPerson){
            if(($tblCommonGender = $tblPerson->getGender())){
                $genderId = $tblCommonGender->getId();
            }
        }

        return new Form(array(
            new FormGroup(array(
                $this->getCommonFormRow($genderId),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveCommonContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelCommonContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        ));
    }

    /**
     * @param int $genderId
     *
     * @return FormRow
     */
    public function getCommonFormRow($genderId = 0)
    {

        // get all existing City names (without deleted Person's)
        $viewPeopleMetaCommonAll = Common::useService()->getViewPeopleMetaCommonAll();

        $tblCommonInformationAll = Common::useService()->getCommonInformationAll();
        $tblNationalityAll = array();
        $tblDenominationAll = array();
        if ($tblCommonInformationAll) {
            array_walk($tblCommonInformationAll,
                function (TblCommonInformation &$tblCommonInformation) use (&$tblNationalityAll, &$tblDenominationAll) {

                    if ($tblCommonInformation->getNationality()) {
                        if (!in_array($tblCommonInformation->getNationality(), $tblNationalityAll)) {
                            array_push($tblNationalityAll, $tblCommonInformation->getNationality());
                        }
                    }
                    if ($tblCommonInformation->getDenomination()) {
                        if (!in_array($tblCommonInformation->getDenomination(), $tblDenominationAll)) {
                            array_push($tblDenominationAll, $tblCommonInformation->getDenomination());
                        }
                    }
                });
            $DefaultDenomination = array(
                'Altkatholisch',
                'Evangelisch',
                'Evangelisch-lutherisch',
                'Evangelisch-reformiert',
                'Französisch-reformiert',
                'Freireligiöse Landesgemeinde Baden',
                'Freireligiöse Landesgemeinde Pfalz',
                'Israelitische Religionsgemeinschaft Baden',
                'Römisch-katholisch',
                'Saarland: israelitisch'
            );
            array_walk($DefaultDenomination, function ($Denomination) use (&$tblDenominationAll) {

                if (!in_array($Denomination, $tblDenominationAll)) {
                    array_push($tblDenominationAll, $Denomination);
                }
            });
        }

        $genderReceiver = ApiPersonReadOnly::receiverBlock($this->getGenderSelectBox($genderId), 'SelectedGender');

        return new FormRow(array(
            new FormColumn(array(
                new Panel('Geburtsdaten', array(
                    new DatePicker('Meta[BirthDates][Birthday]', 'Geburtstag', 'Geburtstag',
                        new Calendar()),
                    new AutoCompleter('Meta[BirthDates][Birthplace]', 'Geburtsort', 'Geburtsort',
                        array('Birthplace' => $viewPeopleMetaCommonAll),
                        new MapMarker()),
                    $genderReceiver,
                ), Panel::PANEL_TYPE_INFO),
            ), 3),
            new FormColumn(array(
                new Panel('Ausweisdaten / Informationen', array(
                    new AutoCompleter('Meta[Information][Nationality]', 'Staatsangehörigkeit',
                        'Staatsangehörigkeit',
                        $tblNationalityAll, new Nameplate()
                    ),
                    new AutoCompleter('Meta[Information][Denomination]', 'Konfession',
                        'Konfession',
                        $tblDenominationAll, new TempleChurch()
                    ),
                ), Panel::PANEL_TYPE_INFO),
            ), 3),
            new FormColumn(array(
                new Panel('Mitarbeit', array(
                    new SelectBox('Meta[Information][IsAssistance]', 'Mitarbeitsbereitschaft', array(
                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL => '',
                        TblCommonInformation::VALUE_IS_ASSISTANCE_YES  => 'Ja',
                        TblCommonInformation::VALUE_IS_ASSISTANCE_NO   => 'Nein'
                    ), new Sheriff()
                    ),
                    new TextArea('Meta[Information][AssistanceActivity]',
                        'Mitarbeitsbereitschaft - Tätigkeiten',
                        'Mitarbeitsbereitschaft - Tätigkeiten', new Pencil()
                    ),
                ), Panel::PANEL_TYPE_INFO)
            ), 3),
            new FormColumn(array(
                new Panel('Sonstiges', array(
                    new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                ), Panel::PANEL_TYPE_INFO)
            ), 3),
        ));
    }

    /**
     * @param $GenderId
     *
     * @return SelectBox
     */
    public function getGenderSelectBox($GenderId)
    {
        $global = $this->getGlobal();
        $global->POST['Meta']['BirthDates']['Gender'] = $GenderId;
        $global->savePost();

        return new SelectBox('Meta[BirthDates][Gender]', 'Geschlecht', array(
            TblCommonBirthDates::VALUE_GENDER_NULL => '',
            TblCommonBirthDates::VALUE_GENDER_MALE => 'Männlich',
            TblCommonBirthDates::VALUE_GENDER_FEMALE => 'Weiblich'
        ), new Child());
    }
}