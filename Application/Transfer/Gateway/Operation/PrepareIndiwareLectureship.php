<?php
namespace SPHERE\Application\Transfer\Gateway\Operation;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\Error;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class PrepareIndiwareLectureship
 * @package SPHERE\Application\Transfer\Gateway\Operation
 */
class PrepareIndiwareLectureship extends AbstractConverter
{

//    /** @var int $RowCount */
//    private static $RowCount = 0;
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

        $ColumnList = $this->findColumnByContent( '!^Fach([0-9]*)?$!s' );
        //$this->getDebugger()->screenDump($ColumnList);
        foreach( $ColumnList as $ColumnName ) {
            $this->setPointer(new FieldPointer( $ColumnName, 'TblSubject'));
        }

        $ColumnList = $this->findColumnByContent( '!^Lehrer([0-9]*)?$!s' );
        //$this->getDebugger()->screenDump($ColumnList);
        foreach( $ColumnList as $ColumnName ) {
            $this->setPointer(new FieldPointer( $ColumnName, 'TblPerson'));
        }

        $ColumnList = $this->findColumnByContent( '!^Klasse([0-9]*)?$!s' );
        //$this->getDebugger()->screenDump($ColumnList);
        foreach( $ColumnList as $ColumnName ) {
            $this->setPointer(new FieldPointer( $ColumnName, 'TblDivision'));
        }

        $ColumnList = $this->findColumnByContent( '!^Gruppe([0-9]*)?$!s' );
        //$this->getDebugger()->screenDump($ColumnList);
        foreach( $ColumnList as $ColumnName ) {
            $this->setPointer(new FieldPointer( $ColumnName, 'TblSubjectGroup'));
        }
    }

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    public function runConvert($Row)
    {

//        Debugger::screenDump($Row);

        $MinErrorLevel = Error::ERROR_LEVEL_INFO_1;
        $ErrorList = array();
        $SuccessList = array();
        $DebugList = array();
        // Run Single Row
        foreach ($Row as $RowIndex => $Col) {
            // Get Pointer Content
            $Payload = current($Col);
            // Multiple Objects?
            if (is_array($Payload)) {
                // Every Object
                foreach ($Payload as $Element) {
                    //
                    if ($Element instanceof Error) {
                        if ($Element->getLevel() >= $MinErrorLevel) {
                            $ErrorList[] = $Element;
                        } else {
                            $DebugList[] = $Element;
                        }
                    } else {
                        // Is Valid Element for Import
                        $SuccessList[] = $Element;
                    }
                }
            } else {
                // Single Object
                $Element = $Payload;
                //
                if ($Element instanceof Error) {
                    if ($Element->getLevel() >= $MinErrorLevel) {
                        $ErrorList[] = $Element;
                    } else {
                        $DebugList[] = $Element;
                    }
                } else {
                    // Is Valid Element for Import
                    $SuccessList[] = $Element;
                }
            }
        }

//        $this->getDebugger()->screenDump($ErrorList);
//        $this->getDebugger()->screenDump($DebugList);
//        $this->getDebugger()->screenDump($SuccessList);

        if (!empty( $ErrorList )) {
            return array('ERROR' => $ErrorList, 'DEBUG' => $DebugList, 'SUCCESS' => $SuccessList);
        } else {
            return array('VALID' => $Row['AF']['TblSubjectGroup']);
        }

        /*
                self::$RowCount++;
        
                $ErrorList = array();
                $WarningList = array();
                $SuccessList = array();
                $InfoList = array();
                foreach ($Row as $Col) {
                    $Col = current($Col);
                    if ($Col instanceof Error) {
                        // Minimum Level to Show
                        if ($Col->getLevel() > Error::ERROR_LEVEL_INFO_3) {
                            array_push($ErrorList, new Small($Col));
                        } else {
                            if ($Col->getLevel() > Error::ERROR_LEVEL_INFO_1) {
                                array_push($InfoList, new Small($Col));
                            }
                        }
                    } else {
                        if ($Col instanceof Element) {
                            if ($Col instanceof TblDivision) {
                                array_push($SuccessList,
                                    new Small(new Success(new Bold(' Klasse ').$Col->getDisplayName().' ('.$Col->getTypeName().')')));
                            } else {
                                if ($Col instanceof TblSubject) {
                                    array_push($SuccessList,
                                        new Small(new Success(new Bold(' Fach ').$Col->getAcronym().' ('.$Col->getName().')')));
                                } else {
                                    if ($Col instanceof TblPerson) {
                                        array_push($SuccessList,
                                            new Small(new Success(new Bold(' Person ').$Col->getFullName())));
                                    } else {
                                        array_push($SuccessList,
                                            new Small(new Success(new Ok().' '.get_class($Col).': '.json_encode($Col->__toArray()))));
                                    }
                                }
                            }
                        } else {
                            if (is_array($Col)) {
                                foreach ($Col as $Item) {
                                    if ($Item instanceof Error) {
                                        array_push($WarningList, $Item->getImpactGui());
                                    } else {
                                        if (is_array($Item)) {
                                            foreach ($Item as $Element) {
                                                if ($Element instanceof TblSubjectGroup) {
                                                    array_push($SuccessList,
                                                        new Small(new Success(new Bold(' Gruppe ').$Element->getName())));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
        
                $SuccessList = array(
                    implode(', ', $SuccessList )
                );
        
                if (empty( $ErrorList ) && empty( $WarningList )) {
                    $LectureshipList = $Row['AF']['TblSubjectGroup'];
                    foreach ($LectureshipList as $Lectureship) {
                        // Division-Teacher
                        if (count($Lectureship) == 3) {
                            // Teacher not in Division-Subject? -> ADD
                            if( !Division::useService()->checkSubjectTeacherExists(
                                $Lectureship['TblDivision'],
                                $Lectureship['TblSubject'],
                                $Lectureship['TblPerson'],
                                null
                            )) {
                                if(($tblDivisionSubject = Division::useService()->getDivisionSubject(
                                    $Lectureship['TblDivision'],
                                    $Lectureship['TblSubject'],
                                    null
                                ))) {
                                    Division::useService()->createSubjectTeacher(
                                        $tblDivisionSubject,
                                        $Lectureship['TblPerson']
                                    );
                                    $SuccessList[] = new Error( new Transfer().' Fach-Verknüpfung angelegt', Error::ERROR_LEVEL_INFO_2);
                                }
                            } else {
                                $SuccessList[] = new Error( new Transfer().' Fach-Verknüpfung bereits vorhanden', Error::ERROR_LEVEL_INFO_2);
                            }
                        // Division-Group-Teacher
                        } else {
                            // Teacher not in Division-Subject-Group? -> ADD
                            if( !Division::useService()->checkSubjectTeacherExists(
                                $Lectureship['TblDivision'],
                                $Lectureship['TblSubject'],
                                $Lectureship['TblPerson'],
                                $Lectureship['TblSubjectGroup']
                            )) {
                                if(($tblDivisionSubject = Division::useService()->getDivisionSubject(
                                    $Lectureship['TblDivision'],
                                    $Lectureship['TblSubject'],
                                    $Lectureship['TblSubjectGroup']
                                ))) {
                                    Division::useService()->createSubjectTeacher(
                                        $tblDivisionSubject,
                                        $Lectureship['TblPerson']
                                    );
                                    $SuccessList[] = new Error( new Transfer().' Gruppen-Verknüpfung angelegt', Error::ERROR_LEVEL_INFO_2);
                                }
                            } else {
                                $SuccessList[] = new Error( new Transfer().' Gruppen-Verknüpfung bereits vorhanden', Error::ERROR_LEVEL_INFO_2);
                            }
                        }
                    }
        
                    // DO IT
                    return new Panel(new Small('Zeile: '.self::$RowCount), array_merge($SuccessList, $InfoList),
                        Panel::PANEL_TYPE_SUCCESS);
                } else {
                    if (empty( $ErrorList )) {
                        // DO IT
                        return new Panel(new Small('Zeile: '.self::$RowCount),
                            array_merge($WarningList, $SuccessList, $InfoList), Panel::PANEL_TYPE_WARNING);
                    } else {
        //            return array_merge($ErrorList, $WarningList);
                        return new Panel(new Small('Zeile: '.self::$RowCount),
                            array_merge($ErrorList, $WarningList, $SuccessList, $InfoList), Panel::PANEL_TYPE_DANGER);
                    }
                }
        */
    }

    /**
     * @param $Value
     *
     * @return TblSubject|Error
     */
    final protected function sanitizeTblSubject($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_DANGER_1, 'Es muss ein Fach angegeben sein!');
        }

        if (( $tblSubject = Subject::useService()->getSubjectByAcronym(strtoupper($Value)) )) {
            return $tblSubject;
        }

        return new Error($Value, Error::ERROR_LEVEL_DANGER_0, 'Das Fach ist in KREDA nicht vorhanden');
    }

    /**
     * @param $Value
     *
     * @return TblPerson|Error
     */
    final protected function sanitizeTblPerson($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_INFO_0, 'Lehrer nicht angegeben');
        }

        if (( $tblTeacher = Teacher::useService()->getTeacherByAcronym($Value) )) {
            if (( $tblPerson = $tblTeacher->getServiceTblPerson() )) {
                return $tblPerson;
            }
        }

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

        $SanitizedSubjectList = array_slice($Payload, 0, 1, true);
        /** @var TblSubject|Error $Subject */
        $Subject = current(current($SanitizedSubjectList));

        if (empty( $Value )) {

            if ($Subject instanceof Element) {

                $SanitizedDivisionList = array_slice($Payload, 4, 20, true);
                foreach ($SanitizedDivisionList as $DivisionList) {
                    /** @var TblDivision|Error $Division */
                    foreach ($DivisionList as $Division) {
                        // Skip Error Messages, Use valid Elements only
                        if ($Division instanceof Error || empty( $Division )) {
                            continue;
                        }

                        /**
                         * ADD Division-Subject, if non exists
                         */
                        if (!Division::useService()->getDivisionSubjectBySubjectAndDivision($Subject, $Division)) {
                            Division::useService()->addSubjectToDivision($Division, $Subject);
                        }

                        if (( $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectBySubjectAndDivision(
                            $Subject, $Division
                        ) )
                        ) {

                            $SanitizedTeacherList = array_slice($Payload, 1, 3, true);
                            foreach ($SanitizedTeacherList as $TeacherList) {
                                /** @var TblPerson|Error $Division */
                                foreach ($TeacherList as $Teacher) {
                                    // Skip Error Messages, Use valid Elements only
                                    if ($Teacher instanceof Error || empty( $Teacher )) {
                                        continue;
                                    }

                                    $MultipleResultList[] = array(
                                        'TblSubject'  => $Subject,
                                        'TblPerson'   => $Teacher,
                                        'TblDivision' => $Division,
                                    );
                                }
                            }
                        } else {
                            $MultipleResultList[] = new Error($Division->getDisplayName().' - '.$Subject->getAcronym(),
                                Error::ERROR_LEVEL_DANGER_3, 'Die Fach-Klasse ist in KREDA nicht vorhanden');
                        }
                    }

                }
            } else {
                $MultipleResultList[] = new Error('', Error::ERROR_LEVEL_DANGER_2,
                    'Es konnte keine gültige Zuweisung erzeugt werden');
            }

            if (empty( $MultipleResultList )) {
                return array(
                    new Error('', Error::ERROR_LEVEL_DANGER_3,
                        'Es konnte kein gültiger (einzelner) Datensatz erzeugt werden')
                );
            } else {
                return $MultipleResultList;
            }

        } else {

            if ($Subject instanceof Element) {

                $SanitizedDivisionList = array_slice($Payload, 4, 20, true);
                foreach ($SanitizedDivisionList as $DivisionList) {
                    /** @var TblDivision|Error $Division */
                    foreach ($DivisionList as $Division) {
                        // Skip Error Messages, Use valid Elements only
                        if ($Division instanceof Error || empty( $Division )) {
                            continue;
                        }

                        /**
                         * ADD Division-Subject, if non exists
                         */
                        if (!Division::useService()->getDivisionSubjectBySubjectAndDivision($Subject, $Division)) {
                            Division::useService()->addSubjectToDivision($Division, $Subject);
                        }

                        if (( $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectBySubjectAndDivision(
                            $Subject, $Division
                        ) )
                        ) {

                            $Combination = array();

                            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                                if (( $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup() )) {
                                    if ($tblSubjectGroup->getName() == $Value) {
                                        $Combination[$Division->getId()] = $tblDivisionSubject;
                                    }
                                } else {
                                    // ELSE NOT USABLE CAUSE OF TWINS (w/wo GROUP)
                                }
                            }

                            /**
                             * ADD Division-Subject/Subject-Group, if non exists
                             */
                            if (!isset( $Combination[$Division->getId()] )) {
                                $tblSubjectGroup = Division::useService()->createSubjectGroup($Value);
                                Division::useService()->createDivisionSubjectGroup($Division, $Subject,
                                    $tblSubjectGroup);

                                if (( $tblDivisionSubjectAll = Division::useService()->getDivisionSubjectBySubjectAndDivision(
                                    $Subject, $Division
                                ) )
                                ) {

                                    $Combination = array();
                                    foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                                        if (( $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup() )) {
                                            if ($tblSubjectGroup->getName() == $Value) {
                                                $Combination[$Division->getId()] = $tblDivisionSubject;
                                            }
                                        } else {
                                            // ELSE NOT USABLE CAUSE OF TWINS (w/wo GROUP)
                                        }
                                    }
                                }
                            }

                            if (!isset( $Combination[$Division->getId()] )) {
                                $MultipleResultList[] = new Error($Division->getDisplayName().' - '.$Subject->getAcronym().' - '.$Value,
                                    Error::ERROR_LEVEL_DANGER_3,
                                    'Die Fach-Klassen-Gruppe ist in KREDA nicht vorhanden');
                            } else {

                                $SanitizedTeacherList = array_slice($Payload, 1, 3, true);
                                foreach ($SanitizedTeacherList as $TeacherList) {
                                    /** @var TblPerson|Error $Division */
                                    foreach ($TeacherList as $Teacher) {
                                        // Skip Error Messages, Use valid Elements only
                                        if ($Teacher instanceof Error || empty( $Teacher )) {
                                            continue;
                                        }

                                        $MultipleResultList[] = array(
                                            'TblSubject'      => $Subject,
                                            'TblPerson'       => $Teacher,
                                            'TblDivision'     => $Division,
                                            'TblSubjectGroup' => $Combination[$Division->getId()]->getTblSubjectGroup()
                                        );
                                    }
                                }
                            }
                        } else {
                            $MultipleResultList[] = new Error($Division->getDisplayName().' - '.$Subject->getAcronym(),
                                Error::ERROR_LEVEL_DANGER_3, 'Die Fach-Klasse ist in KREDA nicht vorhanden');
                        }
                    }
                }
            } else {
                $MultipleResultList[] = new Error('', Error::ERROR_LEVEL_DANGER_2,
                    'Es konnte keine gültige Zuweisung erzeugt werden');
            }

            if (empty( $MultipleResultList )) {
                return array(
                    new Error('', Error::ERROR_LEVEL_DANGER_3,
                        'Es konnte kein gültiger (gruppierter) Datensatz erzeugt werden')
                );
            } else {
                return $MultipleResultList;
            }
        }

    }

    /**
     * @param $Value
     *
     * @return TblDivision|Error
     */
    final protected function sanitizeTblDivision($Value)
    {

        if (empty( $Value )) {
            return new Error($Value, Error::ERROR_LEVEL_INFO_0, 'Klasse nicht angegeben');
        }

        $Level = '';
        $Group = '';
        // [0-9]+[a-z]+
        // TODO: Division: Combined Classes (Year/Group)
        if (preg_match('!^([0-9]+)([a-z]+)?$!is', $Value, $Match)) {
            if (isset( $Match[1] ) && isset( $Match[2] )) {
                $Level = $Match[1];
                $Group = $Match[2];
            }
        }
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
