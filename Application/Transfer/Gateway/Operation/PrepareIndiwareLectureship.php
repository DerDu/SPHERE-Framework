<?php
namespace SPHERE\Application\Transfer\Gateway\Operation;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\Error;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\System\Database\Fitting\Element;

class PrepareIndiwareLectureship extends AbstractConverter
{

    /** @var null|TblYear $tblYear */
    private $tblYear = null;

    /**
     * PrepareIndiwareLectureship constructor.
     *
     * @param string  $File
     * @param TblYear $tblYear
     *
     * @throws \Exception
     */
    public function __construct($File, TblYear $tblYear)
    {

        $this->tblYear = $tblYear;

        /**
         * IF NOT SET LARGE NUMBERS WONT WORK!
         */
        ini_set('precision', '25');

        $this->loadFile($File);

        // Default

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->addSanitizer(array($this, 'sanitizeTblSubject'), 'TblSubject');
        $this->addSanitizer(array($this, 'sanitizeTblPerson'), 'TblPerson');
        $this->addSanitizer(array($this, 'sanitizeTblDivision'), 'TblDivision');
        $this->addSanitizer(array($this, 'sanitizeTblSubjectGroup'), 'TblSubjectGroup');

        $this->setPointer(new FieldPointer('D', 'TblSubject'));

        $this->setPointer(new FieldPointer('E', 'TblPerson'));
        $this->setPointer(new FieldPointer('F', 'TblPerson'));
        $this->setPointer(new FieldPointer('G', 'TblPerson'));

        $this->setPointer(new FieldPointer('L', 'TblDivision'));
        $this->setPointer(new FieldPointer('M', 'TblDivision'));
        $this->setPointer(new FieldPointer('N', 'TblDivision'));
        $this->setPointer(new FieldPointer('O', 'TblDivision'));
        $this->setPointer(new FieldPointer('P', 'TblDivision'));
        $this->setPointer(new FieldPointer('Q', 'TblDivision'));
        $this->setPointer(new FieldPointer('R', 'TblDivision'));
        $this->setPointer(new FieldPointer('S', 'TblDivision'));
        $this->setPointer(new FieldPointer('T', 'TblDivision'));
        $this->setPointer(new FieldPointer('U', 'TblDivision'));
        $this->setPointer(new FieldPointer('V', 'TblDivision'));
        $this->setPointer(new FieldPointer('W', 'TblDivision'));
        $this->setPointer(new FieldPointer('X', 'TblDivision'));
        $this->setPointer(new FieldPointer('Y', 'TblDivision'));
        $this->setPointer(new FieldPointer('Z', 'TblDivision'));
        $this->setPointer(new FieldPointer('AA', 'TblDivision'));
        $this->setPointer(new FieldPointer('AB', 'TblDivision'));
        $this->setPointer(new FieldPointer('AC', 'TblDivision'));
        $this->setPointer(new FieldPointer('AD', 'TblDivision'));
        $this->setPointer(new FieldPointer('AE', 'TblDivision'));

        $this->setPointer(new FieldPointer('AF', 'TblSubjectGroup'));
    }

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    public function runConvert($Row)
    {

        $ErrorList = array();
        $WarningList = array();
        $SuccessList = array();
        foreach ($Row as $Col) {
            $Col = current($Col);
            if ($Col instanceof Error) {
                // Minimum Level to Show
                if ($Col->getLevel() > Error::ERROR_LEVEL_INFO_0) {
                    array_push($ErrorList, new Small($Col));
                }
            } else {
                if ($Col instanceof Element) {
                    if ($Col instanceof TblDivision) {
                        array_push($SuccessList,
                            new Small(new Success(new Ok().new Bold(' Klasse ').$Col->getDisplayName().' ('.$Col->getTypeName().')')));
                    } else {
                        if ($Col instanceof TblSubject) {
                            array_push($SuccessList,
                                new Small(new Success(new Ok().new Bold(' Fach ').$Col->getAcronym().' ('.$Col->getName().')')));
                        } else {
                            array_push($SuccessList,
                                new Small(new Success(new Ok().' '.get_class($Col).': '.json_encode($Col->__toArray()))));
                        }
                    }
                } else {
                    if (is_array($Col)) {
                        foreach ($Col as $Item) {
                            if ($Item instanceof Error) {
                                array_push($WarningList, $Item->getImpactGui());
                            }
                        }
                    }
                }
            }
        }

        if (empty( $ErrorList ) && empty( $WarningList )) {
            // DO IT
            return $SuccessList;
        } else {
//            return array_merge($ErrorList, $WarningList);
            return array_merge($ErrorList, $WarningList, $SuccessList);
        }
    }

    /**
     * @param $Value
     *
     * @return string
     * @throws \Exception
     */
    protected function sanitizeTblSubject($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_DANGER_1, 'Es muss ein Fach angegeben sein!');
        }

        // ACRONYM to Upper !
        $Value = strtoupper($Value);
        // ACRONYM Exists?
        if (( $tblSubject = Subject::useService()->getSubjectByAcronym($Value) )) {
            return $tblSubject;
        } else {
            return new Error($Value, Error::ERROR_LEVEL_DANGER_0, 'Das Fach ist in KREDA nicht vorhanden');
        }
    }

    /**
     * @param $Value
     *
     * @return string
     * @throws \Exception
     */
    protected function sanitizeTblPerson($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_INFO_0, 'Lehrer nicht angegeben');
        }

        if (in_array($Value, array('Mey', 'La'))) {
            return $Value;
        }

        // TODO: Meta-Kürzel -> Person-Entity
        return new Error($Value, Error::ERROR_LEVEL_DANGER_0, 'Der Lehrer ist in KREDA nicht vorhanden');
    }

    /**
     * @param       $Value
     * @param array $Payload Sanitized-Row-Data (till now, if available)
     *
     * @return string
     */
    protected function sanitizeTblSubjectGroup($Value, $Payload)
    {

        $MultipleResultList = array();

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_INFO_1, 'Gruppe nicht angegeben');
        }

        $SanitizedSubjectList = array_slice($Payload, 0, 1, true);
        /** @var TblSubject $Subject */
        $Subject = current(current($SanitizedSubjectList));

        if ($Subject instanceof Element) {

            $SanitizedTeacherList = array_slice($Payload, 1, 3, true);
            foreach ($SanitizedTeacherList as $TeacherList) {
                foreach ($TeacherList as $Teacher) {
                    // Skip Error Messages, Only use Elements
                    if ($Teacher instanceof Error || empty( $Teacher )) {
                        continue;
                    }

                    foreach ($Payload as $Content) {
                        foreach ($Content as $Name => $SanitizedValue) {
                            /** @var TblDivision $SanitizedValue */
                            if ($Name == 'TblDivision' && $SanitizedValue instanceof Element) {
                                $MultipleResultList[] = new Error($Subject->getAcronym().' > '.$SanitizedValue->getDisplayName().' > '.$Value.' > '.$Teacher,
                                    Error::ERROR_LEVEL_WARNING_2, 'TEST');
//                                Debugger::screenDump($Subject->getAcronym().' > '.$SanitizedValue->getDisplayName().' > '.$Value.' > '.$Teacher);
                            }
                        }
                    }
                }
            }
        }
        if (empty( $MultipleResultList )) {
            return new Error($Value, Error::ERROR_LEVEL_DANGER_0,
                'Die Fach-Klassen Gruppe ist in KREDA nicht vorhanden');
        } else {
            return $MultipleResultList;
        }
        // TODO: Name -> Group-Entity
        return new Error($Value, Error::ERROR_LEVEL_DANGER_0, 'Die Fach-Klassen Gruppe ist in KREDA nicht vorhanden');
    }

    /**
     * @param $Value
     *
     * @return string
     * @throws \Exception
     */
    protected function sanitizeTblDivision($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_INFO_0, 'Klasse nicht angegeben');
        }

        $Level = '';
        $Group = '';
        // [0-9]+[a-z]+
        if (preg_match('!^([0-9]+)([a-z]+)?$!is', $Value, $Match)) {
            if (isset( $Match[1] ) && isset( $Match[2] )) {
                $Level = $Match[1];
                $Group = $Match[2];
            }
        }

        // TODO: Division: Combined Classes (Year/Group)
        if (empty( $Level ) && empty( $Group )) {
            return new Error($Value, Error::ERROR_LEVEL_DANGER_3, 'Klassenbezeichner kann nicht interpretiert werden');
        }

        // Check Multiple-Choice: School-Type
        if (( $tblDivisionAll = Division::useService()->getDivisionAllByNameAndYear($Group, $this->tblYear) )) {
            $DivisionList = array();
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionAll as $tblDivision) {

                $CheckGroup = $tblDivision->getName();
                $CheckLevel = ( $tblDivision->getTblLevel() ? $tblDivision->getTblLevel()->getName() : '' );

                if ($CheckLevel == $Level && $CheckGroup == $Group) {
                    if (!array_key_exists($CheckLevel.$CheckGroup, $DivisionList)) {
                        $DivisionList[$CheckLevel.$CheckGroup] = $tblDivision;
                    } else {
                        return new Error($Value, Error::ERROR_LEVEL_DANGER_3,
                            'Die Daten können nicht eindeutig zugeordnet werden');
                    }
                }
            }
            if (isset( $DivisionList[$Level.$Group] )) {
                return $DivisionList[$Level.$Group];
            }
        }

        return new Error($Value, Error::ERROR_LEVEL_DANGER_0, 'Die Klasse ist in KREDA nicht vorhanden');
    }
}
