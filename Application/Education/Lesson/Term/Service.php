<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Data;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblHolidayType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearHoliday;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     *
     * @param TblYear $tblYear
     *
     * @return bool|TblPeriod[]
     */
    public function getPeriodAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getPeriodAllByYear($tblYear);
    }

    /**
     * @return bool|TblYear[]
     */
    public function getYearAll()
    {

        return (new Data($this->getBinding()))->getYearAll();
    }

    /**
     * @param int $Year
     *
     * @return bool|TblYear[]
     */
    public function getYearAllSinceYears($Year)
    {

        $Now = (new \DateTime('now'))->sub(new \DateInterval('P' . $Year . 'Y'));

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                if ($tblPeriodList) {
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new \DateTime($tblPeriod->getToDate()) > new \DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new \DateTime($To) >= new \DateTime($Now->format('d.m.Y'))) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param int $Year
     *
     * @return bool|TblYear[]
     */
    public function getYearAllFutureYears($Year)
    {

        $Now = (new \DateTime('now'))->add(new \DateInterval('P' . $Year . 'Y'));

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                if ($tblPeriodList) {
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new \DateTime($tblPeriod->getToDate()) > new \DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new \DateTime($To) >= new \DateTime($Now->format('d.m.Y'))) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @return bool|TblPeriod[]
     */
    public function getPeriodAll()
    {

        return (new Data($this->getBinding()))->getPeriodAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|array $Year
     *
     * @return IFormInterface|string
     */
    public function createYear(
        IFormInterface $Form,
        $Year
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Year) {
            return $Form;
        }

        $Error = false;

        if (isset($Year['Year']) && empty($Year['Year'])) {
            $Form->setError('Year[Year]', 'Bitte geben sie ein Jahr an');
            $Error = true;
        } else {
            if (($tblYear = Term::useService()->checkYearExist($Year['Year'], $Year['Description']))) {
                $Form->setError('Year[Description]', 'Bitte geben sie eine andere Beschreibung an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->createYear($Year['Year'], $Year['Description'])) {
                return new Success('Das Schuljahr wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Das Schuljahr konnte nicht hinzugefügt werden')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    public function checkYearExist($Year, $Description)
    {

        return (new Data($this->getBinding()))->checkYearExist($Year, $Description);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblYear
     */
    public function getYearsByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getYearsByYear($tblYear);
    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return array|bool
     */
    public function getYearByPeriod(TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->getYearByPeriod($tblPeriod);
    }

    /**
     * @param $String
     *
     * @return false|Service\Entity\TblYear[]
     */
    public function getYearByName($String)
    {
        return (new Data($this->getBinding()))->getYearByName($String);
    }

    /**
     * @param \DateTime $Date
     *
     * @return bool|TblYear[]
     */
    public function getYearAllByDate(\DateTime $Date)
    {

        $EntityList = array();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll) {
            foreach ($tblYearAll as $tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                if ($tblPeriodList) {
                    $From = '';
                    $To = '';
                    $tblPeriodTemp = new TblPeriod();
                    foreach ($tblPeriodList as $tblPeriod) {
                        if (new \DateTime($tblPeriod->getFromDate()) < new \DateTime($From) || $From == '') {
                            $From = $tblPeriod->getFromDate();
                        }
                        if (new \DateTime($tblPeriod->getToDate()) > new \DateTime($To) || $To == '') {
                            $To = $tblPeriod->getToDate();
                        }
                        if ($tblPeriod) {
                            $tblPeriodTemp = $tblPeriod;
                        }
                    }
                    if (new \DateTime($From) <= new \DateTime($Date->format('d.m.Y')) &&
                        new \DateTime($To) >= new \DateTime($Date->format('d.m.Y'))
                    ) {
                        $tblYearTempList = Term::useService()->getYearByPeriod($tblPeriodTemp);
                        if ($tblYearTempList) {
                            foreach ($tblYearTempList as $tblYearTemp) {
                                /** @var TblYear $tblYearTemp */
                                $EntityList[$tblYearTemp->getId()] = $tblYearTemp;
                            }
                        }
                    }
                }
            }
        }
//        $UsedPeriodList = array();
//        $tblPeriodAll = Term::useService()->getPeriodAll();
//        if ($tblPeriodAll) {
//            foreach ($tblPeriodAll as $tblPeriod) {
//                if (new \DateTime($tblPeriod->getFromDate()) < new \DateTime($Now->format('d.m.Y'))
//                    && new \DateTime($tblPeriod->getToDate()) > new \DateTime($Now->format('d.m.Y'))
//                ) {
//                    $UsedPeriodList[] = $tblPeriod;
//                }
//            }
//            if (!empty( $UsedPeriodList )) {
//                foreach ($UsedPeriodList as $UsedPeriod) {
//                    $EntiyArrayList = Term::useService()->getYearByPeriod($UsedPeriod);
//                    if (!empty( $EntiyArrayList )) {
//                        foreach ($EntiyArrayList as $EntityArray) {
//                            $EntityList[] = $EntityArray;
//                        }
//                    }
//                }
//            }
//        }

        $EntityList = array_filter($EntityList);

        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @return bool|Service\Entity\TblYear[]
     */
    public function getYearByNow()
    {

        $Now = new \DateTime('now');
        return $this->getYearAllByDate($Now);
    }

    /**
     * @param $Year
     * @param $Period
     *
     * @return Success
     */
    public function addYearPeriod($Year, $Period)
    {

        $tblYear = $this->getYearById($Year);
        $tblPeriod = $this->getPeriodById($Period);

        if ((new Data($this->getBinding()))->addYearPeriod($tblYear, $tblPeriod)) {
            return new Success('Zeitraum festgelegt') .
            new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_SUCCESS);
        }
        return new Warning('Zeitraum konnte nicht festgelegt werden') .
        new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblYear
     */
    public function getYearById($Id)
    {

        return (new Data($this->getBinding()))->getYearById($Id);
    }

    /**
     * @param string $Id
     *
     * @return bool|TblPeriod
     */
    public function getPeriodById($Id)
    {

        return (new Data($this->getBinding()))->getPeriodById($Id);
    }

    /**
     * @param $Year
     * @param $Period
     *
     * @return Success
     */
    public function removeYearPeriod($Year, $Period)
    {

        $tblYear = $this->getYearById($Year);
        $tblPeriod = $this->getPeriodById($Period);

        if ((new Data($this->getBinding()))->removeYearPeriod($tblYear, $tblPeriod)) {
            return new Success('Zeitraum entfernt') .
            new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_SUCCESS);
        }
        return new Warning('Zeitraum konnte nicht entfernt werden') .
        new Redirect('/Education/Lesson/Term', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array $Period
     *
     * @return IFormInterface|string
     */
    public function createPeriod(
        IFormInterface $Form,
        $Period
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Period) {
            return $Form;
        }

        $Error = false;

        if (isset($Period['Name']) && empty($Period['Name'])) {
            $Form->setError('Period[Name]', 'Bitte geben Sie einen eineindeutigen Namen an');
            $Error = true;
        }

        if (isset($Period['From']) && empty($Period['From'])) {
            $Form->setError('Period[From]', 'Bitte geben Sie Start-Datum an');
            $Error = true;
        }
        if (isset($Period['To']) && empty($Period['To'])) {
            $Form->setError('Period[To]', 'Bitte geben Sie Ende-Datum an');
            $Error = true;
        }
        $tblPeriod = $this->getPeriodByName($Period['Name']);
        if (!empty($tblPeriod)) {
            if ($tblPeriod->getFromDate() === $Period['From']
                && $tblPeriod->getToDate() === $Period['To']
                && $tblPeriod->getDescription() === $Period['Description']
            ) {
                $Form->setError('Period[From]', 'Kombination vergeben');
                $Form->setError('Period[To]', 'Kombination vergeben');
                $Form->setError('Period[Name]', 'Kombination vergeben');
                $Form->setError('Period[Description]', 'Kombination vergeben');
                $Form .= new Warning('Kombination aus Name, Beschreibung und Zeitraum schon vorhanden!');
                $Error = true;
            }
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createPeriod(
                $Period['Name'], $Period['From'], $Period['To'], $Period['Description'])
            ) {
                return new Success('Der Zeitraum wurde erfolgreich hinzugefügt')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Zeitraum konnte nicht hinzugefügt werden')
                . new Redirect($this->getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
            }
        }
        return $Form;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPeriod
     */
    public function getPeriodByName($Name)
    {

        return (new Data($this->getBinding()))->getPeriodByName($Name);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblYear $tblYear
     * @param                     $Year
     *
     * @return IFormInterface|string
     */
    public function changeYear(
        IFormInterface &$Stage = null,
        TblYear $tblYear,
        $Year
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Year
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset($Year['Year']) && empty($Year['Year'])) {
            $Stage->setError('Year[Year]', 'Bitte geben Sie ein Jahr an');
            $Error = true;
        } else {
            if (($CheckYear = Term::useService()->checkYearExist($Year['Year'], $Year['Description']))) {
                if ($tblYear->getId() !== $CheckYear->getId()) {
                    $Stage->setError('Year[Description]', 'Bitte geben sie eine andere Beschreibung an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateYear(
                $tblYear,
                $Year['Year'],
                $Year['Description']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
            };
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPeriod $tblPeriod
     * @param null|array $Period
     *
     * @return IFormInterface|string
     */
    public function changePeriod(
        IFormInterface &$Stage = null,
        TblPeriod $tblPeriod,
        $Period
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Period
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset($Period['Name']) && empty($Period['Name'])) {
            $Stage->setError('Period[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset($Period['From']) && empty($Period['From'])) {
            $Stage->setError('Period[From]', 'Bitte geben Sie ein Startdatum an');
            $Error = true;
        }
        if (isset($Period['To']) && empty($Period['To'])) {
            $Stage->setError('Period[To]', 'Bitte geben Sie ein Enddatum an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updatePeriod(
                $tblPeriod,
                $Period['Name'],
                $Period['Description'],
                $Period['From'],
                $Period['To']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_SUCCESS);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
            };
        }
        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function destroyYear(TblYear $tblYear)
    {

        if (null === $tblYear) {
            return '';
        }
        if ((new Data($this->getBinding()))->destroyYear($tblYear)) {
            return new Success('Das Jahr wurde erfolgreich gelöscht')
            . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Das Jahr konnte nicht gelöscht werden')
            . new Redirect('/Education/Lesson/Term/Create/Year', Redirect::TIMEOUT_ERROR);
        }

    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return string
     */
    public function destroyPeriod(TblPeriod $tblPeriod)
    {

        if (null === $tblPeriod) {
            return '';
        }
        $Error = false;

        if ($this->getPeriodExistWithYear($tblPeriod)) {
            $Error = true;
        }
        if (!$Error) {
            if ((new Data($this->getBinding()))->destroyPeriod($tblPeriod)) {
                return new Success('Der Zeitraum wurde erfolgreich gelöscht')
                . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Zeitraum konnte nicht gelöscht werden')
                . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
            }
        }
        return new Danger('Der Zeitraum wird benutzt!')
        . new Redirect('/Education/Lesson/Term/Create/Period', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function getPeriodExistWithYear(TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->getPeriodExistWithYear($tblPeriod);
    }

    /**
     * @param string $Year
     * @param string $Description
     *
     * @return TblYear
     */
    public function insertYear($Year, $Description = '')
    {

        return (new Data($this->getBinding()))->createYear($Year, $Description);
    }

    /**
     * @param string $Name
     * @param string $From
     * @param string $To
     * @param string $Description
     *
     * @return TblPeriod
     */
    public function insertPeriod($Name, $From, $To, $Description = '')
    {

        return (new Data($this->getBinding()))->createPeriod($Name, $From, $To, $Description);
    }

    /**
     * @param TblYear $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return TblYearPeriod
     */
    public function insertYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        return (new Data($this->getBinding()))->addYearPeriod($tblYear, $tblPeriod);
    }

    /**
     * @param $Id
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeById($Id)
    {

        return (new Data($this->getBinding()))->getHolidayTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getHolidayTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblHolidayType[]
     */
    public function getHolidayTypeAll()
    {

        return (new Data($this->getBinding()))->getHolidayTypeAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblHoliday
     */
    public function getHolidayById($Id)
    {

        return (new Data($this->getBinding()))->getHolidayById($Id);
    }

    /**
     * @param TblYear $tblYear
     * @param \DateTime $date
     *
     * @return false|TblHoliday
     */
    public function getHolidayByDay(TblYear $tblYear, \DateTime $date)
    {

        return (new Data($this->getBinding()))->getHolidayByDay($tblYear, $date);
    }

    /**
     * @return false|TblHoliday[]
     */
    public function getHolidayAll()
    {

        return (new Data($this->getBinding()))->getHolidayAll();
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getHolidayAllByYear($tblYear);
    }

    /**
     * @param $Id
     *
     * @return false|TblYearHoliday
     */
    public function getYearHolidayById($Id)
    {

        return (new Data($this->getBinding()))->getYearHolidayById($Id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getYearHolidayAllByYear($tblYear);
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return false|TblYearHoliday[]
     */
    public function getYearHolidayAllByHoliday(TblHoliday $tblHoliday)
    {

        return (new Data($this->getBinding()))->getYearHolidayAllByHoliday($tblHoliday);
    }

        /**
     * Alle möglichen Holidays innerhalb des Schuljahres
     *
     * @param TblYear $tblYear
     *
     * @return false|TblHoliday[]
     */
    public function getHolidayAllWhereYear(TblYear $tblYear)
    {

        return (new Data($this->getBinding()))->getHolidayAllWhereYear($tblYear);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createHoliday(IFormInterface $Stage = null, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        $tblHolidayType = $this->getHolidayTypeById($Data['Type']);
        if (!$tblHolidayType) {
            $Stage->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createHoliday(
                $tblHolidayType,
                $Data['Name'],
                $Data['FromDate'],
                $Data['ToDate']
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage sind erfasst worden.')
            . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS
            );
        }

        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblHoliday $tblHoliday
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateHoliday(IFormInterface $Stage = null, TblHoliday $tblHoliday, $Data)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $Error = false;
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $Stage->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $Error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $Stage->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        $tblHolidayType = $this->getHolidayTypeById($Data['Type']);
        if (!$tblHolidayType) {
            $Stage->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateHoliday(
                $tblHoliday,
                $tblHolidayType,
                $Data['Name'],
                $Data['FromDate'],
                $Data['ToDate']
            );

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Unterrichtsfreien Tage sind geändert worden.')
            . new Redirect('/Education/Lesson/Term/Holiday', Redirect::TIMEOUT_SUCCESS
            );
        }

        return $Stage;
    }

    /**
     * @param IFormInterface $Form
     * @param TblYear $tblYear
     * @param null $DataAddHoliday
     * @param null $DataRemoveHoliday
     *
     * @return IFormInterface|string
     */
    public function addHolidaysToYear(
        IFormInterface $Form,
        TblYear $tblYear,
        $DataAddHoliday = null,
        $DataRemoveHoliday = null
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Form;
        }

        // entfernen
        if ($DataRemoveHoliday !== null){
            foreach ($DataRemoveHoliday as $yearHolidayId => $item){
                $tblYearHoliday = $this->getYearHolidayById($yearHolidayId);
                if ($tblYearHoliday){
                    (new Data($this->getBinding()))->removeYearHoliday($tblYearHoliday);
                }
            }
        }

        // hinzufügen
        if ($DataAddHoliday !== null) {
            foreach ($DataAddHoliday as $holidayId => $value) {
                $tblHoliday = $this->getHolidayById($holidayId);
                if ($tblHoliday) {
                    (new Data($this->getBinding()))->addYearHoliday($tblYear, $tblHoliday);
                }
            }
        }

        return new Success('Daten erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
        . new Redirect('/Education/Lesson/Term/Holiday/Select', Redirect::TIMEOUT_SUCCESS, array(
            'YearId' => $tblYear->getId(),
        ));
    }

    /**
     * @param TblHoliday $tblHoliday
     *
     * @return bool
     */
    public function destroyHoliday(TblHoliday $tblHoliday)
    {

        // Verknüpfungen löschen
        $tblYearHolidayList = $this->getYearHolidayAllByHoliday($tblHoliday);
        if ($tblYearHolidayList){
            foreach ($tblYearHolidayList as $tblYearHoliday){
                (new Data($this->getBinding()))->removeYearHoliday($tblYearHoliday);
            }
        }

        return (new Data($this->getBinding()))->destroyHoliday($tblHoliday);
    }
}
