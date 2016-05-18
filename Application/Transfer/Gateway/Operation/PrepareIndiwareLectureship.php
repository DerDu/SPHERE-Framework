<?php
namespace SPHERE\Application\Transfer\Gateway\Operation;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\System\Extension\Repository\Debugger;

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

        ini_set('precision', '25');
        Debugger::screenDump($File);

        $this->loadFile($File);

        // Default

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->addSanitizer(array($this, 'sanitizeTblSubject'), 'TblSubject');
        $this->addSanitizer(array($this, 'sanitizeTblDivision'), 'TblDivision');

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

        $this->scanFile(1);

    }

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    public function runConvert($Row)
    {

        // TODO: Implement runConvert() method.

        if (
        is_object($Row['L']['TblDivision'])
        ) {
            Debugger::screenDump($Row);
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
            return $this->returnWarning();
        }

        // ACRONYM to Upper !
        $Value = strtoupper($Value);
        // ACRONYM Exists?
        if (( $tblSubject = Subject::useService()->getSubjectByAcronym($Value) )) {
            return $tblSubject;
        } else {
            return $this->returnError($Value);
        }
    }

    private function returnWarning()
    {

        return (string)'Keine Import-Daten vorhanden!';
    }

    private function returnError($Value)
    {

        return (string)'Nicht in KREDA vorhanden! ('.$Value.')';
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
            return $this->returnWarning();
        }

        $Level = '';
        $Group = '';
        // [0-9]+[a-z]+
        if (preg_match('!^([0-9]+)([a-z]+)$!', $Value, $Match)) {
            if (isset( $Match[1] ) && isset( $Match[2] )) {
                $Level = $Match[1];
                $Group = $Match[2];
            }
        }

        // TODO: Filter: School-Type
        if (( $tblDivisionAll = Division::useService()->getDivisionAllByNameAndYear($Group, $this->tblYear) )) {
            array_walk($tblDivisionAll, function (TblDivision &$tblDivision) use ($Level) {

                if (!(
                    $tblDivision->getTblLevel()
                    && $tblDivision->getTblLevel()->getName() == $Level
                )
                ) {
                    $tblDivision = false;
                }
            });
            $tblDivisionAll = array_filter($tblDivisionAll);
        }
        if (empty( $tblDivisionAll )) {
            return $this->returnError($Value);
        }
        return current($tblDivisionAll);
    }
}
