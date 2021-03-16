<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2019
 * Time: 08:55
 */

namespace SPHERE\Application\Transfer\Import;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import
 */
class Service
{
    /**
     * @var array
     */
    private $Location;

    /** @var PhpExcel $Document */
    private $Document;

    public function __construct($Location, $Document)
    {
        $this->Location = $Location;
        $this->Document = $Document;
    }

    /**
     * @param $year
     *
     * @return TblYear
     */
    public function insertSchoolYear($year)
    {
        $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
        if ($tblYear) {
            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
            if (!$tblPeriodList) {
                // firstTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '1. Halbjahr',
                    '01.08.20' . $year,
                    '31.01.20' . ($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }

                // secondTerm
                $tblPeriod = Term::useService()->insertPeriod(
                    '2. Halbjahr',
                    '01.02.20' . ($year + 1),
                    '31.07.20' . ($year + 1)
                );
                if ($tblPeriod) {
                    Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                }
            }
        }

        return $tblYear;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Remark
     */
    public function insertPrivatePhone($tblPerson, $columnName, $RunY, $Remark = '')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            if (0 === strpos($phoneNumber, '1')) {
                $phoneNumber = '0' . $phoneNumber;
            }

            $tblType = Phone::useService()->getTypeById(1);
            if (0 === strpos($phoneNumber, '01')) {
                $tblType = Phone::useService()->getTypeById(2);
            }

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, $Remark);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Separator
     */
    public function insertPrivatePhoneWithSeparator($tblPerson, $columnName, $RunY, $Separator = ';')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            if ($Separator != '' && (strpos($phoneNumber, $Separator) !== false)) {
                $array = preg_split('/' . $Separator . '/', $phoneNumber);
            } else {
                $array = array(0 => $phoneNumber);
            }

            foreach ($array as $item) {
                $number = trim($item);
                if (0 === strpos($number, '1')) {
                    $number = '0' . $number;
                }

                $tblType = Phone::useService()->getTypeById(1);
                if (0 === strpos($number, '01')) {
                    $tblType = Phone::useService()->getTypeById(2);
                }

                Phone::useService()->insertPhoneToPerson($tblPerson, $number, $tblType, '');
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Remark
     */
    public function insertPrivateFax($tblPerson, $columnName, $RunY, $Remark = '')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            $tblType = Phone::useService()->getTypeById(7);

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, $Remark);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Remark
     */
    public function insertBusinessPhone($tblPerson, $columnName, $RunY, $Remark = '')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            $tblType = Phone::useService()->getTypeById(3);
            if (0 === strpos($phoneNumber, '01')) {
                $tblType = Phone::useService()->getTypeById(4);
            }

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, $Remark);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     * @param string $Remark
     */
    public function insertBusinessFax($tblPerson, $columnName, $RunY, $Remark = '')
    {
        $phoneNumber = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($phoneNumber != '') {
            $tblType = Phone::useService()->getTypeById(8);

            Phone::useService()->insertPhoneToPerson($tblPerson, $phoneNumber, $tblType, $Remark);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     */
    public function insertPrivateMail($tblPerson, $columnName, $RunY)
    {
        $mailAddress = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($mailAddress != '') {
            Mail::useService()->insertMailToPerson(
                $tblPerson,
                $mailAddress,
                Mail::useService()->getTypeById(1),
                ''
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $columnName
     * @param integer $RunY
     */
    public function insertBusinessMail($tblPerson, $columnName, $RunY)
    {
        $mailAddress = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($mailAddress != '') {
            Mail::useService()->insertMailToPerson(
                $tblPerson,
                $mailAddress,
                Mail::useService()->getTypeById(2),
                ''
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $columnName
     * @param $RunY
     * @param string $separator
     */
    public function insertGroupsByName(TblPerson $tblPerson, $columnName, $RunY, $separator = ';')
    {
        $groupNames = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($groupNames != '') {
            $list = preg_split('/' . $separator . '/', $groupNames);
            foreach ($list as $name) {
                if ($tblGroup = Group::useService()->insertGroup(trim($name))) {
                    Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                }
            }
        }
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return array
     */
    public function splitStreet($columnName, $RunY)
    {
        $streetName = '';
        $streetNumber = '';
        $street = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($street != '') {
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }

        return array($streetName, $streetNumber);
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return array
     */
    public function splitCity($columnName, $RunY)
    {
        $cityDistrict = '';
        $cityName = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($cityName != '') {
            $pos = strpos($cityName, " OT ");
            if ($pos !== false) {
                $cityDistrict = trim(substr($cityName, $pos + 4));
                $cityName = trim(substr($cityName, 0, $pos));
            }
        }

        return array($cityName, $cityDistrict);
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return string
     */
    public function formatZipCode($columnName, $RunY)
    {
        if ($this->Location[$columnName] !== null) {
            $code = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
            if ($code) {
                return str_pad(
                    $code,
                    5,
                    "0",
                    STR_PAD_LEFT
                );
            }
        }

        return '';
    }

    /**
     * @param $columnName
     * @param $RunY
     * @param TblStudent $tblStudent
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string $trueValue
     */
    public function setStudentAgreement(
        $columnName,
        $RunY,
        TblStudent $tblStudent,
        TblStudentAgreementCategory $tblStudentAgreementCategory,
        $trueValue = 'ja'
    ) {
        $agreement = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($agreement == $trueValue) {
            if (($tblStudentAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory))) {
                foreach ($tblStudentAgreementTypeList as $tblStudentAgreementType) {
                    Student::useService()->insertStudentAgreement($tblStudent, $tblStudentAgreementType);
                }
            }
        }
    }

    /**
     * @param $columnName
     * @param $RunY
     *
     * @return array
     */
    public function splitPersonName($columnName, $RunY)
    {
        $firstName = '';
        $lastName = '';
        $name = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
        if ($name != '') {
            $split = preg_split('/ /', $name);
            if (is_array($split)) {
                $lastName = end($split);
                $firstName = trim(str_replace($lastName, '', $name));
            }
        }

        return array($firstName, $lastName);
    }

    /**
     * @param $columnName
     * @param $RunY
     * @param $error
     *
     * @return false|string
     */
    public function formatDateString($columnName, $RunY, $error)
    {
        if ($this->Location[$columnName] !== null) {
            $date = trim($this->Document->getValue($this->Document->getCell($this->Location[$columnName], $RunY)));
            if ($date != '') {
                $len = strlen($date);
                switch ($len) {
                    case 6:
                        $result = substr($date, 0, 2) . '.' . substr($date, 2, 2) . '.' . substr($date, 4, 2);
                        break;
                    case 7:
                        $date = '0' . $date;
                    case 8:
                        $result = substr($date, 0, 2) . '.' . substr($date, 2, 2) . '.' . substr($date, 4, 4);
                        break;
                    default:
                        $error[] = 'Zeile: ' . ($RunY + 1) . $columnName . ':' . $date
                            . ' konnte nicht in ein Datum umgewandelt werden.';
                        $result = '';
                }

                return $result;
            }
        }

        return '';
    }
}