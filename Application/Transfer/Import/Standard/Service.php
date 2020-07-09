<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use DateTime;
use Exception;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use PHPExcel_Shared_Date;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType as TblTypeRelationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Standard
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     * @param array|null          $Data
     *
     * @return string
     */
    public function createStudentsFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));
            return $Form;
        }
        if(isset($Data['Year']) && !empty($Data['Year'])){
            $YearString = substr($Data['Year'], 0, 4);
        } else {
            // fallback
            $YearString = (new DateTime())->format('Y');
        }

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(),
            $File->getFilename().'.'.$File->getClientOriginalExtension());

        /**
         * Read
         */
        //$File->getMimeType()
        /** @var PhpExcel $Document */
        $Document = Document::getDocument($File->getPathname());
        if (!$Document instanceof PhpExcel) {
            $Form->setError('File', 'Fehler');
            return $Form;
        }

        $X = $Document->getSheetColumnCount();
        $Y = $Document->getSheetRowCount();

        /**
         * Header -> Location
         */
        $Location = array(
            'Nr'                       => null,
//            'Bezug_Nr.'        => null,
            'Schüler_Nr'               => null,
            // name
            'Geschlecht'               => null,
            'Name'                     => '',
            'Vorname'                  => null,
            '2ter_Vorname'             => null,
            'Rufname'                  => null,
            // common
            'Geburtsdatum'             => null,
            'Geburtsort'               => null,
            'Staatsangehörigkeit'      => null,
            // address
            'PLZ'                      => null,
            'Ort'                      => null,
            'Ortsteil'                 => null,
            'Straße'                   => null,
            'HNR'                      => null,
            'Land'                     => null,
            // contact
            'Notfall_Festnetz'         => null,
            'Notfall_Mobil'            => null,
            'Privat_Festnetz'          => null,
            'Privat_Mobil'             => null,
            'E_Mail_Privat'            => null,

            // S1
            'S1_Anrede'                => null,
            'S1_Titel'                 => null,
            'S1_Name'                  => null,
            'S1_Vorname'               => null,
            // adress
            'S1_PLZ'                   => null,
            'S1_Ort'                   => null,
            'S1_Ortsteil'              => null,
            'S1_Straße'                => null,
            'S1_HNR'                   => null,
            'S1_Land'                  => null,
            // contact
            'S1_Geschäftlich_Festnetz' => null,
            'S1_Geschäftlich_Mobil'    => null,
            'S1_Notfall_Festnetz'      => null,
            'S1_Notfall_Mobil'         => null,
            'S1_Privat_Festnetz'       => null,
            'S1_Privat_Mobil'          => null,
            'S1_E_Mail_Geschäftlich'   => null,
            'S1_E_Mail_Privat'         => null,
            // common
            'S1_Mitarbeitbereitschaft' => null,
            'S1_Mitgliedsnummer'       => null,
            // custody
            'S1_Beruf'                 => null,
            'S1_Arbeitsstelle'         => null,
            'S1_Bemerkungen'           => null,
            // account
            'S1_IBAN'                  => null,
            'S1_BIC'                   => null,
            'S1_Bankname'              => null,

            // S2
            'S2_Anrede'                => null,
            'S2_Titel'                 => null,
            'S2_Name'                  => null,
            'S2_Vorname'               => null,
            // adress
            'S2_PLZ'                   => null,
            'S2_Ort'                   => null,
            'S2_Ortsteil'              => null,
            'S2_Straße'                => null,
            'S2_HNR'                   => null,
            'S2_Land'                  => null,
            // contact
            'S2_Geschäftlich_Festnetz' => null,
            'S2_Geschäftlich_Mobil'    => null,
            'S2_Notfall_Festnetz'      => null,
            'S2_Notfall_Mobil'         => null,
            'S2_Privat_Festnetz'       => null,
            'S2_Privat_Mobil'          => null,
            'S2_E_Mail_Geschäftlich'   => null,
            'S2_E_Mail_Privat'         => null,
            // common
            'S2_Mitarbeitbereitschaft' => null,
            'S2_Mitgliedsnummer'       => null,
            // custody
            'S2_Beruf'                 => null,
            'S2_Arbeitsstelle'         => null,
            'S2_Bemerkungen'           => null,
            // account
            'S2_IBAN'                  => null,
            'S2_BIC'                   => null,
            'S2_Bankname'              => null,

            // maybe S3 ?

            // student extended
            'Klasse/Kurs'          => null,
            'Schulart'             => null,
            'Bildungsgang'         => null,
            'Religion'             => null,
            'Stammgruppe'          => null,
            'Ersteinschlung_Datum' => null,
            'Allergien'            => null,
            'Medikamente'          => null,
            'Krankenkasse'         => null,
            'Hort'                 => null,
            'Abholberechtigte'     => null,

        );

        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($Document->getValue($Document->getCell($RunX, 1)));
            if (array_key_exists($Value, $Location)) {
                $Location[$Value] = $RunX;
            }
        }

        /**
         * Import
         * Es müssen alle Spalten vorhanden sein
         */
        if (!in_array(null, $Location, true)) {
            $countStudent = 0;
            $countS1 = 0;
            $countS2 = 0;
            $countS1Exists = 0;
            $countS2Exists = 0;

            $error = array();
            $info = array();
            for ($RunY = 2; $RunY < $Y; $RunY++) {
                set_time_limit(300);
                // Student ---------------------------------------------------------------------------------------------
                $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                $Nr = trim($Document->getValue($Document->getCell($Location['Nr'], $RunY)));
                if ($firstName === '' || $lastName === '') {
                    $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                    continue;
                }
                // person check
                $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
                if($tblPerson){
                    $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Schüler '.$tblPerson->getLastFirstName()
                        .' wurde nicht hinzugefügt. "bereits vorhanden"';
                    continue;
                }

                $secondName = trim($Document->getValue($Document->getCell($Location['2ter_Vorname'], $RunY)));
                $callName = trim($Document->getValue($Document->getCell($Location['Rufname'], $RunY)));
                $Stammgruppe = trim($Document->getValue($Document->getCell($Location['Stammgruppe'], $RunY)));
                $Hort = trim($Document->getValue($Document->getCell($Location['Hort'], $RunY)));
                $tblPerson = $this->setPersonStudent($firstName, $secondName, $callName, $lastName, $Stammgruppe, $Hort);
                $countStudent++;

                // common & birthday
                $studentGender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                $studentBirth = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                $birthPlace = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
                $nationality = trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY)));
                $denomination = ''; // ToDO Konfession nachpflegen?
                $remark = trim($Document->getValue($Document->getCell($Location['Abholberechtigte'], $RunY)));
                $this->setPersonBirth($tblPerson, $studentBirth, $birthPlace, $studentGender, $nationality, $denomination, $remark, $RunY, $Nr, $error);

                // student
                $Identification = trim($Document->getValue($Document->getCell($Location['Schüler_Nr'], $RunY)));
                $schoolAttendanceStartDate = trim($Document->getValue($Document->getCell($Location['Ersteinschlung_Datum'], $RunY)));
                // medicine
                $tblStudentMedicalRecord = null;
                $disease = trim($Document->getValue($Document->getCell($Location['Allergien'], $RunY)));
                $medication = trim($Document->getValue($Document->getCell($Location['Medikamente'], $RunY)));
                $insurance = trim($Document->getValue($Document->getCell($Location['Krankenkasse'], $RunY)));
                $religion = trim($Document->getValue($Document->getCell($Location['Religion'], $RunY)));
                $course = trim($Document->getValue($Document->getCell($Location['Bildungsgang'], $RunY)));
                $this->setPersonTblStudent($tblPerson, $Identification, $schoolAttendanceStartDate, $disease, $medication, $insurance, $religion, $course, $RunY, $Nr, $error);

                // division
                $divisionString = trim($Document->getValue($Document->getCell($Location['Klasse/Kurs'], $RunY)));
                $schoolType = trim($Document->getValue($Document->getCell($Location['Schulart'], $RunY)));
                $this->setPersonDivision($tblPerson, $YearString, $divisionString, $schoolType, $RunY, $Nr, $error);

                // address
                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                $streetNumber = trim($Document->getValue($Document->getCell($Location['HNR'], $RunY)));
                $city = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                $district = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                $nation = trim($Document->getValue($Document->getCell($Location['Land'], $RunY)));
                $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $nation, $RunY, $Nr, $error);

                // contact
                $emergencyPhone = trim($Document->getValue($Document->getCell($Location['Notfall_Festnetz'], $RunY)));
                $emergencyMobile = trim($Document->getValue($Document->getCell($Location['Notfall_Mobil'], $RunY)));
                $privatePhone = trim($Document->getValue($Document->getCell($Location['Privat_Festnetz'], $RunY)));
                $privateMobile = trim($Document->getValue($Document->getCell($Location['Privat_Mobil'], $RunY)));
                $privateMail = trim($Document->getValue($Document->getCell($Location['E_Mail_Privat'], $RunY)));
                $this->setPersonContact($tblPerson, $emergencyPhone, $emergencyMobile, $privatePhone, $privateMobile, '', '', $privateMail, '');

                // S1 --------------------------------------------------------------------------------------------------
                $firstName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Vorname'], $RunY)));
                $lastName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Name'], $RunY)));
                $cityCode_S1 = trim($Document->getValue($Document->getCell($Location['S1_PLZ'], $RunY)));
                // nur vorhandene Datensätze
                if($firstName_S1 != '' && $lastName_S1 != ''){
                    $addInformation = true;
                    $tblPerson_S1 = Person::useService()->existsPerson($firstName_S1, $lastName_S1, $cityCode_S1);
                    if(!$tblPerson_S1)
                    {
                        $salutation_S1 = trim($Document->getValue($Document->getCell($Location['S1_Anrede'], $RunY)));
                        $title_S1 = trim($Document->getValue($Document->getCell($Location['S1_Titel'], $RunY)));
                        $memberNumber_S1 = trim($Document->getValue($Document->getCell($Location['S1_Mitgliedsnummer'], $RunY)));
                        $assistance_S1 = trim($Document->getValue($Document->getCell($Location['S1_Mitarbeitbereitschaft'], $RunY)));
                        $tblPerson_S1 = $this->setPersonCustody($salutation_S1, $title_S1, $firstName_S1, $lastName_S1, $memberNumber_S1, $assistance_S1);
                        $countS1++;
                    } else {
                        $info[] = new Muted(new Small(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1)).' Der Sorgeberechtigte S1 wurde nicht angelegt, da schon eine 
                        Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                        Person verknüpft'));
                        $countS1Exists++;
                        // keine doppelte Datenpflege
                        $addInformation = false;
                    }
                    if($addInformation){
                        // custody
                        $occupation = trim($Document->getValue($Document->getCell($Location['S1_Beruf'], $RunY)));
                        $employment = trim($Document->getValue($Document->getCell($Location['S1_Arbeitsstelle'], $RunY)));
                        $remark = trim($Document->getValue($Document->getCell($Location['S1_Bemerkungen'], $RunY)));
                        Custody::useService()->insertMeta($tblPerson_S1, $occupation, $employment, $remark);

                        // S1 address
                        $streetName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Straße'], $RunY)));
                        $streetNumber_S1 = trim($Document->getValue($Document->getCell($Location['S1_HNR'], $RunY)));
                        $city_S1 = trim($Document->getValue($Document->getCell($Location['S1_Ort'], $RunY)));
                        $cityCode_S1 = trim($Document->getValue($Document->getCell($Location['S1_PLZ'], $RunY)));
                        $district_S1 = trim($Document->getValue($Document->getCell($Location['S1_Ortsteil'], $RunY)));
                        $nation = trim($Document->getValue($Document->getCell($Location['S1_Land'], $RunY)));
                        $this->setPersonAddress($tblPerson_S1, $streetName_S1, $streetNumber_S1, $city_S1, $cityCode_S1, $district_S1, $nation, $RunY, $Nr, $error);

                        // S1 contact
                        $emergencyPhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Festnetz'], $RunY)));
                        $emergencyMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Mobil'], $RunY)));
                        $privatePhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Privat_Festnetz'], $RunY)));
                        $privateMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Privat_Mobil'], $RunY)));
                        $businessPhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Geschäftlich_Festnetz'], $RunY)));
                        $businessMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Geschäftlich_Mobil'], $RunY)));
                        $privateMail_S1 = trim($Document->getValue($Document->getCell($Location['S1_E_Mail_Privat'], $RunY)));
                        $businessMail_S1 = trim($Document->getValue($Document->getCell($Location['S1_E_Mail_Geschäftlich'], $RunY)));
                        $this->setPersonContact($tblPerson_S1, $emergencyPhone_S1, $emergencyMobile_S1, $privatePhone_S1,
                            $privateMobile_S1, $businessPhone_S1, $businessMobile_S1, $privateMail_S1, $businessMail_S1);

                        // Billing
                        $bankName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Bankname'], $RunY)));
                        $IBAN_S1 = trim($Document->getValue($Document->getCell($Location['S1_IBAN'], $RunY)));
                        $BIC_S1 = trim($Document->getValue($Document->getCell($Location['S1_BIC'], $RunY)));
                        // nur vollständige Daten importieren
                        if($bankName_S1 != '' && $IBAN_S1 != '' && $BIC_S1 != ''){
                            $this->setPersonBankAccount($tblPerson_S1, $bankName_S1, $IBAN_S1, $BIC_S1);
                        }
                    }

                    // relationship
                    $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                    Relationship::useService()->insertRelationshipToPerson($tblPerson_S1, $tblPerson, $tblRelationshipType, '', 1);
                }

                // S2 --------------------------------------------------------------------------------------------------
                $firstName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Vorname'], $RunY)));
                $lastName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Name'], $RunY)));
                $cityCode_S2 = trim($Document->getValue($Document->getCell($Location['S2_PLZ'], $RunY)));
                // nur vorhandene Datensätze
                if($firstName_S2 != '' && $lastName_S2 != ''){
                    $addInformation = true;
                    $tblPerson_S2 = Person::useService()->existsPerson($firstName_S2, $lastName_S2, $cityCode_S2);
                    if(!$tblPerson_S2)
                    {
                        $salutation_S2 = trim($Document->getValue($Document->getCell($Location['S2_Anrede'], $RunY)));
                        $title_S2 = trim($Document->getValue($Document->getCell($Location['S2_Titel'], $RunY)));
                        $memberNumber_S2 = trim($Document->getValue($Document->getCell($Location['S2_Mitgliedsnummer'], $RunY)));
                        $assistance_S2 = trim($Document->getValue($Document->getCell($Location['S2_Mitarbeitbereitschaft'], $RunY)));
                        $tblPerson_S2 = $this->setPersonCustody($salutation_S2, $title_S2, $firstName_S2, $lastName_S2, $memberNumber_S2, $assistance_S2);
                        $countS2++;
                    } else {
                        $info[] = new Muted(new Small(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1)).' Der Sorgeberechtigte S2 wurde nicht angelegt, da schon eine 
                        Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                        Person verknüpft'));
                        $countS2Exists++;
                        // keine doppelte Datenpflege
                        $addInformation = false;
                    }
                    if($addInformation){
                        // custody
                        $occupation = trim($Document->getValue($Document->getCell($Location['S1_Beruf'], $RunY)));
                        $employment = trim($Document->getValue($Document->getCell($Location['S1_Arbeitsstelle'], $RunY)));
                        $remark = trim($Document->getValue($Document->getCell($Location['S1_Bemerkungen'], $RunY)));
                        Custody::useService()->insertMeta($tblPerson_S2, $occupation, $employment, $remark);
                        // S2 address
                        $streetName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Straße'], $RunY)));
                        $streetNumber_S2 = trim($Document->getValue($Document->getCell($Location['S2_HNR'], $RunY)));
                        $city_S2 = trim($Document->getValue($Document->getCell($Location['S2_Ort'], $RunY)));
                        $cityCode_S2 = trim($Document->getValue($Document->getCell($Location['S2_PLZ'], $RunY)));
                        $district_S2 = trim($Document->getValue($Document->getCell($Location['S2_Ortsteil'], $RunY)));
                        $nation = trim($Document->getValue($Document->getCell($Location['S2_Land'], $RunY)));
                        $this->setPersonAddress($tblPerson_S2, $streetName_S2, $streetNumber_S2, $city_S2, $cityCode_S2, $district_S2, $nation, $RunY, $Nr, $error);

                        // S2 contact
                        $emergencyPhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Festnetz'], $RunY)));
                        $emergencyMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Mobil'], $RunY)));
                        $privatePhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Privat_Festnetz'], $RunY)));
                        $privateMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Privat_Mobil'], $RunY)));
                        $businessPhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Geschäftlich_Festnetz'], $RunY)));
                        $businessMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Geschäftlich_Mobil'], $RunY)));
                        $privateMail_S2 = trim($Document->getValue($Document->getCell($Location['S2_E_Mail_Privat'], $RunY)));
                        $businessMail_S2 = trim($Document->getValue($Document->getCell($Location['S2_E_Mail_Geschäftlich'], $RunY)));
                        $this->setPersonContact($tblPerson_S2, $emergencyPhone_S2, $emergencyMobile_S2, $privatePhone_S2,
                            $privateMobile_S2, $businessPhone_S2, $businessMobile_S2, $privateMail_S2, $businessMail_S2);

                        // Billing
                        $bankName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Bankname'], $RunY)));
                        $IBAN_S2 = trim($Document->getValue($Document->getCell($Location['S2_IBAN'], $RunY)));
                        $BIC_S2 = trim($Document->getValue($Document->getCell($Location['S2_BIC'], $RunY)));
                        // nur vollständige Daten importieren
                        if($bankName_S2 != '' && $IBAN_S2 != '' && $BIC_S2 != ''){
                            $this->setPersonBankAccount($tblPerson_S2, $bankName_S2, $IBAN_S2, $BIC_S2);
                        }
                    }

                    // relationship
                    $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                    Relationship::useService()->insertRelationshipToPerson($tblPerson_S2, $tblPerson, $tblRelationshipType, '', 2);
                }
            }

            if(empty($error)){
                $error = new SuccessText('Keine');
            }

            $AccordionInfo = new Accordion();
            $AccordionInfo->addItem('Information - Vorhandene Personen', new Listing($info));

            return new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.', null, false, '25', '5')
                    , 4),
                    new LayoutColumn(
                        new Success('Es wurden '.$countS1.' Sorgeberechtigte S1 erfolgreich angelegt.'.
                        ($countS1Exists > 0
                            ? new Warning(' ('.$countS1Exists.' dopplungen) ', null, false, '1', '5')
                              .($countS1 + $countS1Exists).' Zuweisungen zu Schülern.'
                            : '')
                        , null, false, '3', '5')
                    , 4),
                    new LayoutColumn(
                        new Success('Es wurden '.$countS2.' Sorgeberechtigte S2 erfolgreich angelegt.'.
                        ($countS2Exists > 0
                            ? new Warning(' ('.$countS2Exists.' dopplungen) ', null, false, '1', '5')
                              .($countS2 + $countS2Exists).' Zuweisungen zu Schülern.'
                            : '')
                        , null, false, '3', '5')
                    , 4),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Fehler',
                            $error,
                            Panel::PANEL_TYPE_DANGER
                        )
                    ),
                    new LayoutColumn(
                        $AccordionInfo
                    )
                ))
            )));

        } else {
            $MissingColumn = array();
            foreach($Location as $Key => $Column){
                if($Column === null){
                    $MissingColumn[] = $Key.': '.new DangerText('Spalte nicht gefunden!');
                }
            }
            return new Warning(new Listing($MissingColumn)).new Danger(
                    "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename().'.'.$File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                // ToDO Add Custody after #SSW-922 & #SSW-926
                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Nr'                  => null,
                    'Geschlecht'          => null,
                    'Name'                => null,
                    'Vorname'             => null,
                    'Rufname'             => null,
                    '2ter_Vorname'        => null,
                    'Geburtsdatum'        => null,
                    'Geburtsort'          => null,
                    'Staatsangehörigkeit' => null,
                    // address
                    'PLZ'                 => null,
                    'Ort'                 => null,
                    'Ortsteil'            => null,
                    'Straße'              => null,
                    'HNR'                 => null,
                    'Land'                => null,
                    // prospect
                    'Interessent_Jahr'    => null,
                    'Klassenstufe'        => null,
                    'Eingangsdatum'       => null,
                    'Aufnahmegespräch'    => null,
                    'Schnuppertag'        => null,
                    'Bemerkung'           => null,
                    'Schulart'            => null,
                    // contact
                    'Notfall_Festnetz'    => null,
                    'Notfall_Mobil'       => null,
                    'Privat_Festnetz'     => null,
                    'Privat_Mobil'        => null,
                    'E_Mail_Privat'       => null,

//                    // S1
//                    'S1_Anrede' => null,
//                    'S1_Titel' => null,
//                    'S1_Name' => null,
//                    'S1_Vorname' => null,
//                    // adress
//                    'S1_PLZ' => null,
//                    'S1_Ort' => null,
//                    'S1_Ortsteil' => null,
//                    'S1_Straße' => null,
//                    'S1_HNR' => null,
//                    'S1_Land' => null,
//                    // contact
//                    'S1_Geschäftlich_Festnetz' => null,
//                    'S1_Geschäftlich_Mobil' => null,
//                    'S1_Notfall_Festnetz' => null,
//                    'S1_Notfall_Mobil' => null,
//                    'S1_Privat_Festnetz' => null,
//                    'S1_Privat_Mobil' => null,
//                    'S1_E_Mail_Geschäftlich' => null,
//                    'S1_E_Mail_Privat' => null,
//                    // common
//                    'S1_Mitarbeitbereitschaft' => null,
//                    'S1_Mitgliedsnummer' => null,
//                    // custody
//                    'S1_Beruf' => null,
//                    'S1_Arbeitsstelle' => null,
//                    'S1_Bemerkungen' => null,
//                    // account
//                    'S1_IBAN' => null,
//                    'S1_BIC' => null,
//                    'S1_Bankname' => null,

//                    // S2
//                    'S2_Anrede' => null,
//                    'S2_Titel' => null,
//                    'S2_Name' => null,
//                    'S2_Vorname' => null,
//                    // adress
//                    'S2_PLZ' => null,
//                    'S2_Ort' => null,
//                    'S2_Ortsteil' => null,
//                    'S2_Straße' => null,
//                    'S2_HNR' => null,
//                    'S2_Land' => null,
//                    // contact
//                    'S2_Geschäftlich_Festnetz' => null,
//                    'S2_Geschäftlich_Mobil' => null,
//                    'S2_Notfall_Festnetz' => null,
//                    'S2_Notfall_Mobil' => null,
//                    'S2_Privat_Festnetz' => null,
//                    'S2_Privat_Mobil' => null,
//                    'S2_E_Mail_Geschäftlich' => null,
//                    'S2_E_Mail_Privat' => null,
//                    // common
//                    'S2_Mitarbeitbereitschaft' => null,
//                    'S2_Mitgliedsnummer' => null,
//                    // custody
//                    'S2_Beruf' => null,
//                    'S2_Arbeitsstelle' => null,
//                    'S2_Bemerkungen' => null,
//                    // account
//                    'S2_IBAN' => null,
//                    'S2_BIC' => null,
//                    'S2_Bankname' => null,


                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 1)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStudent = 0;
//                    $countS1 = 0;
//                    $countS2 = 0;
//                    $countS1Exists = 0;
//                    $countS2Exists = 0;

                    $error = array();

                    for ($RunY = 2; $RunY < $Y; $RunY++) {

                        set_time_limit(300);
                        // Prospect ------------------------------------------------------------------------------------
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        $Nr = trim($Document->getValue($Document->getCell($Location['Nr'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1)))
                                .' Interessent wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                            continue;
                        }
                        // person check
                        $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                        $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
                        if($tblPerson){
                            $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Interessent '.$tblPerson->getLastFirstName()
                                .' wurde nicht hinzugefügt. "bereits vorhanden"';
                            continue;
                        }


                        $secondName = trim($Document->getValue($Document->getCell($Location['2ter_Vorname'], $RunY)));
                        $callName = trim($Document->getValue($Document->getCell($Location['Rufname'], $RunY)));
                        $Stammgruppe = '';
//                        $Hort = trim($Document->getValue($Document->getCell($Location['Hort'], $RunY)));
                        $Hort = '';
                        //
                        $tblPerson = $this->setPersonStudent($firstName, $secondName, $callName, $lastName, $Stammgruppe, $Hort, true);
                        $countStudent++;

                        // common & birthday
                        $studentGender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                        $studentBirth = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                        $birthPlace = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
                        $nationality = trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY)));
                        $denomination = '';
                        $remark = '';
                        $this->setPersonBirth($tblPerson, $studentBirth, $birthPlace, $studentGender, $nationality, $denomination, $remark, $RunY, $Nr, $error);

                        // address
                        $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                        $streetNumber = trim($Document->getValue($Document->getCell($Location['HNR'], $RunY)));
                        $city = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                        $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                        $district = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                        $nation = trim($Document->getValue($Document->getCell($Location['Land'], $RunY)));
                        $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $nation, $RunY, $Nr, $error);

                        // contact
                        $emergencyPhone = trim($Document->getValue($Document->getCell($Location['Notfall_Festnetz'], $RunY)));
                        $emergencyMobile = trim($Document->getValue($Document->getCell($Location['Notfall_Mobil'], $RunY)));
                        $privatePhone = trim($Document->getValue($Document->getCell($Location['Privat_Festnetz'], $RunY)));
                        $privateMobile = trim($Document->getValue($Document->getCell($Location['Privat_Mobil'], $RunY)));
                        $privateMail = trim($Document->getValue($Document->getCell($Location['E_Mail_Privat'], $RunY)));
                        $this->setPersonContact($tblPerson, $emergencyPhone, $emergencyMobile, $privatePhone, $privateMobile, '', '', $privateMail, '');

                        // prospect
                        $SchoolType = trim($Document->getValue($Document->getCell($Location['Schulart'], $RunY)));
                        $Year = trim($Document->getValue($Document->getCell($Location['Interessent_Jahr'], $RunY)));
                        $Level = trim($Document->getValue($Document->getCell($Location['Klassenstufe'], $RunY)));
                        $ReservationDate = trim($Document->getValue($Document->getCell($Location['Eingangsdatum'], $RunY)));
                        $InterviewDate = trim($Document->getValue($Document->getCell($Location['Aufnahmegespräch'], $RunY)));
                        $TrialDate = trim($Document->getValue($Document->getCell($Location['Schnuppertag'], $RunY)));
                        $ProspectRemark = trim($Document->getValue($Document->getCell($Location['Bemerkung'], $RunY)));
                        $this->setProspect($tblPerson, $SchoolType, $Year, $Level, $ReservationDate, $InterviewDate, $TrialDate, $ProspectRemark, $RunY, $Nr, $error);

                    }

                    // prepare Info what is not imported
//                    $AccordionInfo = new Accordion();
//                    $AccordionInfo->addItem('Information - Vorhandene Personen', new Listing($info));

                    if(empty($error)){
                        $error = new SuccessText('Keine');
                    }

                    return new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Success('Es wurden '.$countStudent.' Interessenten erfolgreich angelegt.', null, false, '25', '5')
                                , 4),
//                            new LayoutColumn(
//                                new Success('Es wurden '.$countS1.' Sorgeberechtigte S1 erfolgreich angelegt.'.
//                                    ($countS1Exists > 0
//                                        ? new Warning(' ('.$countS1Exists.' dopplungen) ', null, false, '1', '5')
//                                        .($countS1 + $countS1Exists).' Zuweisungen zu Schülern.'
//                                        : '')
//                                    , null, false, '3', '5')
//                                , 4),
//                            new LayoutColumn(
//                                new Success('Es wurden '.$countS2.' Sorgeberechtigte S2 erfolgreich angelegt.'.
//                                    ($countS2Exists > 0
//                                        ? new Warning(' ('.$countS2Exists.' dopplungen) ', null, false, '1', '5')
//                                        .($countS2 + $countS2Exists).' Zuweisungen zu Schülern.'
//                                        : '')
//                                    , null, false, '3', '5')
//                                , 4),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                )
                            ),
//                            new LayoutColumn(
//                                $AccordionInfo
//                            )
                        ))
                    )));
                } else {
                    $MissingColumn = array();
                    foreach($Location as $Key => $Column){
                        if($Column === null){
                            $MissingColumn[] = $Key.': '.new DangerText('Spalte nicht gefunden!');
                        }
                    }
                    return new Warning(new Listing($MissingColumn)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile        $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Nr'                    => null,
                    'Anrede'                => null,
                    'Titel'                 => null,
                    'Name'                  => null,
                    'Vorname'               => null,
                    'Kürzel'                => null,
                    'Geburtsdatum'          => null,
                    'PLZ'                   => null,
                    'Ort'                   => null,
                    'Ortsteil'              => null,
                    'Straße'                => null,
                    'HNR'                   => null,
                    'Land'                  => null,
                    'Geschäftlich_Festnetz' => null,
                    'Geschäftlich_Mobil'    => null,
                    'Notfall_Festnetz'      => null,
                    'Notfall_Mobil'         => null,
                    'Privat_Festnetz'       => null,
                    'Privat_Mobil'          => null,
                    'E_Mail_Geschäftlich'   => null,
                    'E_Mail_Privat'         => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 1)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    for ($RunY = 2; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Teacher ---------------------------------------------------------------------------------------------
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        $Nr = trim($Document->getValue($Document->getCell($Location['Nr'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Mitarbeiter wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                            continue;
                        }
                        // person check
                        $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                        $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
                        if($tblPerson){
                            $info[] = new Muted(new Small(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1)).' Person '.$tblPerson->getLastFirstName().' gefunden, wird zusätzlich Mitarbeiter.'));
                            $countStaffExists++;
                            $this->setGroupStaff($tblPerson);
                        } else {
                            // nicht vorhandene Personen werden angelegt
                            $salutation = trim($Document->getValue($Document->getCell($Location['Anrede'], $RunY)));
                            $title = trim($Document->getValue($Document->getCell($Location['Titel'], $RunY)));
                            $tblPerson = $this->setPersonStaff($salutation, $title, $firstName, $lastName);

//                            $gender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
//                            $birthPlace = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
//                            $nationality = trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY)));
                            $gender = '';
                            $birthPlace = '';
                            $nationality = '';
                            $birth = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                            $denomination = '';
                            $remark = '';
                            $this->setPersonBirth($tblPerson, $birth, $birthPlace, $gender, $nationality, $denomination, $remark, $RunY, $Nr, $error);

                            // address
                            $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                            $streetNumber = trim($Document->getValue($Document->getCell($Location['HNR'], $RunY)));
                            $city = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                            $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                            $district = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                            $nation = trim($Document->getValue($Document->getCell($Location['Land'], $RunY)));
                            $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $nation, $RunY, $Nr, $error);
                            $countStaff++;
                        }

                        // contact expand if exist is ok
                        $emergencyPhone = trim($Document->getValue($Document->getCell($Location['Notfall_Festnetz'], $RunY)));
                        $emergencyMobile = trim($Document->getValue($Document->getCell($Location['Notfall_Mobil'], $RunY)));
                        $privatePhone = trim($Document->getValue($Document->getCell($Location['Privat_Festnetz'], $RunY)));
                        $privateMobile = trim($Document->getValue($Document->getCell($Location['Privat_Mobil'], $RunY)));
                        $businessPhone = trim($Document->getValue($Document->getCell($Location['Geschäftlich_Festnetz'], $RunY)));
                        $businessMobile = trim($Document->getValue($Document->getCell($Location['Geschäftlich_Mobil'], $RunY)));
                        $businessMail = trim($Document->getValue($Document->getCell($Location['E_Mail_Geschäftlich'], $RunY)));
                        $privateMail = trim($Document->getValue($Document->getCell($Location['E_Mail_Privat'], $RunY)));
                        $this->setPersonContact($tblPerson, $emergencyPhone, $emergencyMobile, $privatePhone, $privateMobile, $businessPhone, $businessMobile, $privateMail, $businessMail);

                        // add teacher info
                        $acronym = trim($Document->getValue($Document->getCell($Location['Kürzel'], $RunY)));
                        Teacher::useService()->insertTeacher($tblPerson, $acronym);

                    }

                    if(empty($error)){
                        $error = new SuccessText('Keine');
                    }

                    return new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Success('Es wurden '.$countStaff.' Mitarbeiter/Lehrer erfolgreich angelegt.', null, false, '25', '5')
                                , 4),
                            new LayoutColumn(
                                new Success($countStaffExists.' Mitarbeiter/Lehrer davon existierten bereits als Person.', null, false, '25', '5')
                                , 4),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                )
                            ),
                        ))
                    )));

                } else {
                    $MissingColumn = array();
                    foreach($Location as $Key => $Column){
                        if($Column === null){
                            $MissingColumn[] = $Key.': '.new DangerText('Spalte nicht gefunden!');
                        }
                    }
                    return new Warning(new Listing($MissingColumn)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $firstName
     * @param string $secondName
     * @param string $callName
     * @param string $lastName
     * @param string $Stammgruppe
     * @param string $Hort
     * @param bool   $isProspect
     *
     * @return bool|TblPerson
     */
    private function setPersonStudent($firstName, $secondName, $callName, $lastName, $Stammgruppe, $Hort, $isProspect = false)
    {

        // Auswahl der Stammgruppe
        $tblGroupS = false;
        $GroupList = array();
        if($isProspect){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        } else {
            if ($Stammgruppe){
                $tblGroupS = Group::useService()->createGroupFromImport($Stammgruppe, 'Stammgruppe');
            }
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        }


        if($tblGroupS){
            $GroupList[] = $tblGroupS;
        }
        if($Hort != ''){
            $tblGroupH = Group::useService()->createGroupFromImport('Hort');
            $GroupList[] = $tblGroupH;
        }

        return Person::useService()->insertPerson(
            Person::useService()->getSalutationByName(TblSalutation::VALUE_STUDENT),
            '',
            $firstName,
            $secondName,
            $lastName,
            $GroupList,
            '',
            '',
            $callName
        );
    }

    /**
     * @param string $salutation
     * @param string $title
     * @param string $firstName
     * @param string $lastName
     * @param string $memberNumber
     * @param string $assistance
     *
     * @return bool|TblPerson
     */
    private function setPersonCustody($salutation, $title, $firstName, $lastName, $memberNumber, $assistance)
    {

        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if($memberNumber){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB);
        }

        $tblSalutation = false;
        $gender = 0;
        if($salutation){
            $salutation = strtolower($salutation);
            switch($salutation){
                case 'herr':
                case 'm':
                case 'h':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_MAN);
                    $gender = TblCommonGender::VALUE_MALE;
                break;
                case 'frau':
                case 'w':
                case 'f':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_WOMAN);
                    $gender = TblCommonGender::VALUE_FEMALE;
                break;
            }
        }

        $tblPerson = Person::useService()->insertPerson(
            $tblSalutation,
            $title,
            $firstName,
            '',
            $lastName,
            $GroupList
        );

        $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_NULL;
        if($assistance){
            $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_YES;
        }

        Common::useService()->insertMeta(
            $tblPerson,
            '',
            '',
            $gender,
            '',
            '',
            $isAssistance,
            $assistance,
            ''
        );

        Club::useService()->insertMeta($tblPerson, $memberNumber);

        return $tblPerson;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $birthdayString
     * @param string    $birthPlace
     * @param string    $gender
     * @param string    $nationality
     * @param string    $denomination
     * @param string    $remark
     * @param int       $RunY
     * @param string    $Nr
     * @param array     $error
     */
    private function setPersonBirth(TblPerson $tblPerson, $birthdayString, $birthPlace, $gender, $nationality, $denomination, $remark, $RunY, $Nr, &$error)
    {
        // controll conform DateTime string
        $birthday = $this->checkDate($birthdayString, 'Ungültiges Geburtsdatum:', $RunY, $Nr, $error);
        if($gender != ''){
            $gender = strtolower($gender);
            switch ($gender){
                case 'm':
                case 'männlich':
                case 'mann':
                    $gender = TblCommonGender::VALUE_MALE;
                break;
                case 'w':
                case 'weiblich':
                case 'frau':
                    $gender = TblCommonGender::VALUE_FEMALE;
                break;
                default:
                    // Geschlecht nicht zuweisbar
                    $gender = '';
            }
        }

        Common::useService()->insertMeta(
            $tblPerson,
            $birthday,
            $birthPlace,
            $gender,
            $nationality,
            $denomination,
            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
            '',
            $remark
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $YearString
     * @param string    $divisionString
     * @param string    $schoolType
     * @param int       $RunY
     * @param string    $Nr
     * @param array     $error
     */
    private function setPersonDivision(TblPerson $tblPerson, $YearString, $divisionString, $schoolType, $RunY, $Nr, &$error)
    {

        $year = (int)$YearString;
        $yearShort = (int)substr($YearString, 2, 2);

        $tblDivision = false;
        if ($divisionString !== '') {
            $tblYear = Term::useService()->insertYear($year.'/'.($yearShort + 1));
            if ($tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                if (!$tblPeriodList) {
                    // firstTerm
                    $tblPeriod = Term::useService()->insertPeriod(
                        '1. Halbjahr',
                        '01.08.'.$year,
                        '31.01.'.($year + 1)
                    );
                    if ($tblPeriod) {
                        Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                    }

                    // secondTerm
                    $tblPeriod = Term::useService()->insertPeriod(
                        '2. Halbjahr',
                        '01.02.'.($year + 1),
                        '31.07.'.($year + 1)
                    );
                    if ($tblPeriod) {
                        Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                    }
                }

                if (strlen($divisionString) > 1) {
                    if (is_numeric(substr($divisionString, 0, 2))) {
                        $pos = 2;
                        $level = substr($divisionString, 0, $pos);
                        // remove the "-"
                        if (substr($divisionString, $pos, 1) == '-') {
                            $pos = 3;
                            $division = trim(substr($divisionString, $pos));
                        } else {
                            $division = trim(substr($divisionString, $pos));
                        }
                    } else {
                        $pos = 1;
                        $level = substr($divisionString, 0, $pos);
                        $division = trim(substr($divisionString, $pos));
                    }
                } else {
                    $level = $divisionString;
                    $division = '';
                }

                $schoolType = strtolower($schoolType);
                switch($schoolType){
                    case 'gs':
                    case 'grundschule':
                        $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GRUND_SCHULE);
                    break;
                    case 'ms':
                    case 'os':
                    case 'mittelschule':
                    case 'oberschule':
                    case 'mittelschule/oberschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE);
                    break;
                    case 'gym':
                    case 'gymnasium':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
                    break;
                    case 'bs':
                    case 'berufsschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_SCHULE);
                    break;
                    case 'bfs':
                    case 'berufsfachschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_FACH_SCHULE);
                    break;
                    default:
                        $tblSchoolType = false;
                }

                if($tblSchoolType){
                    $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                    if ($tblLevel) {
                        $tblDivision = Division::useService()->insertDivision(
                            $tblYear,
                            $tblLevel,
                            $division
                        );
                    }
                }
            }

            if ($tblDivision) {
                Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
            } else {
                if($tblSchoolType){
                    $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Der Schüler konnte keiner Klasse zugeordnet werden.';
                } else {
                    $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' Die Schulart ist nicht verwendbar. Schüler keiner
                    Klasse zugeordnet.';
                }

            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $streetName
     * @param string    $streetNumber
     * @param string    $city
     * @param string    $cityCode
     * @param string    $district
     * @param string    $nation
     * @param int       $RunY
     * @param string    $Nr
     * @param array     $error
     */
    private function setPersonAddress(TblPerson $tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $nation, $RunY, $Nr, &$error)
    {

        if($district == ''){
            $cityTemp = $city;
            if (preg_match('!(\w*\s)(OT\s\w*)!is', $cityTemp, $found)) {
                $city = $found[1];
                $district = $found[2];
            }
        }

        if($streetNumber == ''){
            $street = $streetName;
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }

        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $city
        ) {
                Address::useService()->insertAddressToPerson(
                    $tblPerson, $streetName, $streetNumber, $cityCode, $city,
                    $district, '', '', $nation, null
                );
        } else {
            $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' '.$tblPerson->getLastFirstName().' Adresse konnte nicht angelegt werden.';
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $emergencyPhone
     * @param string    $emergencyMobile
     * @param string    $privatePhone
     * @param string    $privateMobile
     * @param string    $businessPhone
     * @param string    $businessMobile
     * @param string    $privateMail
     * @param string    $businessMail
     */
    private function setPersonContact(TblPerson $tblPerson, $emergencyPhone = '', $emergencyMobile = '',
        $privatePhone = '', $privateMobile = '', $businessPhone = '', $businessMobile = '', $privateMail = ''
        , $businessMail = '')
    {

        // phone/mobile
        if($emergencyPhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_EMERCENCY, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $emergencyPhone, $tblType, '');
        }
        if($emergencyMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_EMERCENCY, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $emergencyMobile, $tblType, '');
        }
        if($privatePhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $privatePhone, $tblType, '');
        }
        if($privateMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $privateMobile, $tblType, '');
        }
        if($businessPhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_BUSINESS, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $businessPhone, $tblType, '');
        }
        if($businessMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_BUSINESS, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $businessMobile, $tblType, '');
        }

        // mail
        if($privateMail){
            $tblType = Mail::useService()->getTypeById(1); // private
            Mail::useService()->insertMailToPerson($tblPerson, $privateMail, $tblType, '');
        }
        if($businessMail){
            $tblType = Mail::useService()->getTypeById(2); // business
            Mail::useService()->insertMailToPerson($tblPerson, $businessMail, $tblType, '');
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $bankName
     * @param string    $IBAN
     * @param string    $BIC
     */
    private function setPersonBankAccount(TblPerson $tblPerson, $bankName, $IBAN, $BIC)
    {

        $Owner = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
        Debtor::useService()->createBankAccount($tblPerson, $Owner, $bankName, $IBAN, $BIC);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
        Group::useService()->addGroupPerson($tblGroup, $tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Identification
     * @param string    $schoolAttendanceStartDate
     * @param string    $disease
     * @param string    $medication
     * @param string    $insurance
     * @param string    $religion
     * @param string    $course
     * @param int       $RunY
     * @param string    $Nr
     * @param array     $error
     */
    private function setPersonTblStudent(TblPerson $tblPerson, $Identification, $schoolAttendanceStartDate, $disease, $medication, $insurance, $religion, $course, $RunY, $Nr, &$error)
    {
        // controll conform DateTime string
        $schoolAttendanceStartDate = $this->checkDate($schoolAttendanceStartDate, 'Ungültiges Einschulungsdatum:', $RunY, $Nr, $error);

        $tblStudentMedicalRecord = null;
        if($disease != '' || $medication != '' || $insurance != ''){
            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord($disease, $medication, $insurance);
        }

        // Student
        $tblStudent = Student::useService()->insertStudent($tblPerson, $Identification, $tblStudentMedicalRecord, null, null, null, null, null, $schoolAttendanceStartDate);

        if($religion){
            $tblSubject = Subject::useService()->getSubjectByAcronym($religion);
            if(!$tblSubject){
                $tblSubject = Subject::useService()->getSubjectByName($religion);
            }
            if($tblSubject){
                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION');
                $tblSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                Student::useService()->addStudentSubject($tblStudent, $tblStudentSubjectType,$tblSubjectRanking, $tblSubject);
            }
        }

        if($course){
            $tblCourseType = Course::useService()->getCourseByName($course);
            if($tblCourseType){
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::PROCESS);
                Student::useService()->insertStudentTransfer($tblStudent, $tblStudentTransferType, null, null, $tblCourseType);
            }
        }

    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $schoolType
     * @param string    $Year
     * @param string    $Level
     * @param string    $ReservationDate
     * @param string    $InterviewDate
     * @param string    $TrialDate
     * @param string    $ProspectRemark
     * @param int       $RunY
     * @param string    $Nr
     * @param array     $error
     */
    private function setProspect(TblPerson $tblPerson, $schoolType, $Year, $Level, $ReservationDate, $InterviewDate, $TrialDate, $ProspectRemark, $RunY, $Nr, &$error)
    {

        $tblType = null;
        $schoolType = strtoupper($schoolType);
        switch ($schoolType) {
            case 'GS':
                $tblType = Type::useService()->getTypeByName(TblType::IDENT_GRUND_SCHULE);
                break;
            case 'OS':
                $tblType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE);
                break;
            case 'GYM':
                $tblType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
                break;
        }
        // controll conform DateTime string
        $ReservationDate = $this->checkDate($ReservationDate, 'Ungültiges Eingangsdatum:', $RunY, $Nr, $error);
        $InterviewDate = $this->checkDate($InterviewDate, 'Ungültiges Datum Aufnahmegespräch:', $RunY, $Nr, $error);
        $TrialDate = $this->checkDate($TrialDate, 'Ungültiges Datum Schnuppertag:', $RunY, $Nr, $error);

        //ToDO Option 2 für Schulart pflegen
        Prospect::useService()->insertMeta($tblPerson, $ReservationDate, $InterviewDate, $TrialDate, $Year, $Level, $tblType, null, $ProspectRemark);
    }

    /**
     * @param $Date
     * @param $ErrorMessage
     * @param $RunY
     * @param $Nr
     * @param $error
     *
     * @return false|string
     */
    private function checkDate($Date, $ErrorMessage, $RunY, $Nr, &$error)
    {

        if ($Date !== '') {
            try {
                $Date = date('d.m.Y', PHPExcel_Shared_Date::ExcelToPHP($Date));
            } catch (Exception $ex) {
                $Date = '';
                $error[] = new DangerText(($Nr ? 'Nr.: '.$Nr : 'Zeile: '.($RunY + 1))).' '.$ErrorMessage.' '.$ex->getMessage();
            }
        } else {
            $Date = '';
        }
        return $Date;
    }

    /**
     * @param string $salutation
     * @param string $titel
     * @param string $firstName
     * @param string $lastName
     * @param bool   $isTeacher
     * @param bool   $isStaff
     *
     * @return bool|TblPerson
     */
    private function setPersonStaff($salutation, $titel, $firstName, $lastName, $isTeacher = true, $isStaff = true)
    {

        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        if($isStaff){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        }
        if($isTeacher){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        }

        $tblSalutation = false;
        if($salutation){
            $salutation = strtolower($salutation);
            switch($salutation){
                case 'herr':
                case 'm':
                case 'h':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_MAN);
                    break;
                case 'frau':
                case 'w':
                case 'f':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_WOMAN);
                    break;
            }
        }

        return Person::useService()->insertPerson(
            $tblSalutation,
            $titel,
            $firstName,
            '',
            $lastName,
            $GroupList
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $isTeacher
     * @param bool      $isStaff
     */
    private function setGroupStaff(TblPerson $tblPerson, $isTeacher = true, $isStaff = true)
    {

        $GroupList = array();
        if($isStaff){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        }
        if($isTeacher){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        }

        // Add to Group
        if (!empty( $GroupList )) {
            foreach ($GroupList as $tblGroup) {
                Group::useService()->addGroupPerson(
                    Group::useService()->getGroupById($tblGroup), $tblPerson
                );
            }
        }
    }
}