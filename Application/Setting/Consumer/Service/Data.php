<?php

namespace SPHERE\Application\Setting\Consumer\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Database;
use SPHERE\System\Database\Fitting\Element;
use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Database as MocDatabase;
use SPHERE\System\Database\Type\MySql;

/**
 * Class Data
 * @package SPHERE\Application\Setting\Consumer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        if (($tblSetting = $this->createSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein', 'Die Schülernummern werden automatisch vom System erstellt.
                In diesem Fall können die Schülernummern nicht per Hand vergeben werden. [Standard: Nein]');
        }

        if (($tblSetting = $this->createSetting('People', 'Person', 'Relationship', 'GenderOfS1', TblSetting::TYPE_INTEGER, 2, 'Allgemein',
            'Für Personenbeziehungen vom Typ: Sorgeberechtigt wird das folgende Geschlecht für S1 vorausgewählt. [Standard: Weiblich]', true))
        ) {
            $this->updateSettingDescription(
                $tblSetting,
                $tblSetting->getCategory(),
                'Für Personenbeziehungen vom Typ: Sorgeberechtigt wird das folgende Geschlecht für S1 vorausgewählt. [Standard: Weiblich]',
                $tblSetting->isPublic()
            );
        }

        if (($tblSetting = $this->createSetting('Transfer', 'Indiware', 'Import',
            'Lectureship_ConvertDivisionLatinToGreek', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Indiware',
                'Ersetzung der Klassengruppennamen beim Import in ausgeschriebene Griechische Buchstaben. (z.B. a => alpha) [Standard: Nein]');
        }

        if (($tblSetting = $this->createSetting('Contact', 'Address', 'Address', 'Format_GuiString',
            TblSetting::TYPE_STRING, TblAddress::VALUE_PLZ_ORT_OT_STR_NR))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein',
                'Reihenfolge der Adressanzeige [Standard: PLZ_ORT_OT_STR_NR]');
        }

        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureAddress',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Eltern und Schülerzugänge sowie Passwortänderungsanschreiben kann ein Bild (Logo) hinterlegt werden.
                Adresse des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureHeight',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Eltern und Schülerzugänge sowie Passwortänderungsanschreiben kann ein Bild (Logo) hinterlegt werden.
                Höhe des Bildes (Maximal 120px): [Standard: 120px]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureAddress',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureHeight',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes (Maximal 120px): [Standard: 80px]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes (Maximal 120px): [Standard: 90px]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'Billing_PictureAddress',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für den Bescheinigung der Fakturierung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'Standard', 'Billing_PictureHeight',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Für den Bescheinigung der Fakturierung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes (Maximal 150px): [Standard: 90px]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Dokumente',
                'Anzeige des Schulnamens (Stempel-Feld oben rechts) auf der Schülerkartei für die Grundschule. [Standard: Ja]',
                true);
        }

        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Für die Standard-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250.
                Adresse des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeight',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Für die Standard-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250. Das Bild
                ist ohne Einstellung 66px Hoch, wie das Sachsenlogo. Höhe des Bildes: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddressForDiplomaCertificate', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: []');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeightForDiplomaCertificate', TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes: []');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'PictureDisplayLocationForDiplomaCertificate', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse wird das Logo auf der 2. Seite unter dem Abschluss angezeigt (ansonsten auf dem Cover oben links): [Ja]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate', 'DocumentBorder', TblSetting::TYPE_INTEGER, '2'))) {

            $this->updateSettingDescription($tblSetting, 'Zeugnisse', 'Festlegung der seitlichen Ränder für Zeugnisse
            mittels Zahleneingabe (1=mittlerer Rand, 2=breiter Rand): [Standard: 2]', true);
        }
        if (($tblSetting = $this->createSetting('Api', 'Education', 'Certificate', 'OrientationAcronym',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Werden die Neigungskurse in der Bildung nicht einzeln gepflegt, sondern nur ein einzelner Standard-Neigungskurs, 
                kann hier das Kürzel des Standard-Neigungskurses (z.B. NK) hinterlegt werden. Für die Zeugnisse wir dann der
                eigentliche Neigungskurs aus der Schülerakte des Schülers gezogen. [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Api', 'Education', 'Certificate', 'ProfileAcronym',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Werden die Profile in der Bildung nicht einzeln gepflegt, sondern nur ein einzelnes Standard-Profil, 
                kann hier das Kürzel des Standard-Profils (z.B. PRO) hinterlegt werden. Für die Zeugnisse wir dann das
                eigentliche Profil aus der Schülerakte des Schülers gezogen. [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige der Zensuren im Wortlaut auf Abschlusszeugnissen [Standard: Nein]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige des Schul-Zusatzes (Institutionszusatz) auf Zeugnissen [Standard: Nein]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige des Schul-Zusatzes (Institutionszusatz) auf Zeugnissen mit dem Trennzeichen: [Standard: ]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige der Kopfnoten aus allen Kopfnotenaufträge bei der Zeugnisvorbereitung, wenn pro Halbjahr mehrere Kopfnotenaufträge erteilt wurden. [Standard: Nein]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generate',
            'UseCourseForCertificateChoosing', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Es wird der Bildungsgang des Schülers verwendet, um die entsprechende Zeugnisvorlage (Mittelschule) dem
                Schüler automatisch zuzuordnen. [Standard: Ja]');
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnLeave',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige der Zensuren im Wortlaut auf Abgangszeugnissen [Standard: Nein]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'ShowParentTitle',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Anzeige des Personentitels von Sorgeberechtigten in der Bildungsempfehlung [Standard: Ja]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Diploma', 'PreArticleForSchoolName',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Artikel vor dem Schulnamen auf Abschlusszeugnissen und Abgangszeugnissen (z.B. das): [Standard: ]');
        }
        $this->createSetting('Education', 'Certificate', 'Diploma', 'AlternateSchoolName', TblSetting::TYPE_STRING,
            '', 'Zeugnisse', 'Schulname auf Abschlusszeugnissen und Abgangszeugnissen: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Diploma', 'AlternateExtendedSchoolName', TblSetting::TYPE_STRING,
            '', 'Zeugnisse', 'Schul-Zusatz-Name auf Abschlusszeugnissen und Abgangszeugnissen: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Diploma', 'ShowExtendedSchoolName', TblSetting::TYPE_BOOLEAN,
            '', 'Zeugnisse', 'Schul-Zusatz-Name von der Institution auf Abschlusszeugnissen und Abgangszeugnissen anzeigen [Standard: Nein]'
            , true);
        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'HasRemarkBlocking',
            TblSetting::TYPE_BOOLEAN, '1'))
        ) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'Sollen leere Bemerkungsfelder auf Zeugnissen gesperrt werden ("---"). [Standard: Ja]',
                true);
        }

        if (($tblSetting = $this->createSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify', // "Blocksatz"
            TblSetting::TYPE_BOOLEAN, '1'))
        ) {
            $this->updateSettingDescription($tblSetting, 'Zeugnisse',
                'die Schülereinschätzung als Blocksatz dargestellen [Standard: Ja]',
                false);
        }

        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook',
            'IsShownAverageInStudentOverview', TblSetting::TYPE_BOOLEAN, false))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige des Notendurchschnitts in der Eltern/Schüler-Übersicht [Standard: Nein]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownScoreInStudentOverview',
            TblSetting::TYPE_BOOLEAN, false))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige des Notenspiegels und des Fach-Klassen-Durchschnitts in der Eltern/Schüler-Übersicht. [Standard: Nein]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowHighlightedTestsInGradeOverview', TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige der geplanten Großen Noten (fettmarkiert, z.B. Klassenarbeiten) in der Notenübersicht für Schüler/Eltern und in der Schülerübersicht [Standard: Ja]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'SortHighlighted',
            TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen Noten. Bei Deaktivierung erfolgt Sortierung nach Datum. [Standard: Nein]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Bei der Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen Noten, stehen die Großen Noten rechts. [Standard: Ja]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige des Notendurchschnitts im heruntergeladenen Notenbuch (PDF) [Standard: Ja]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowCertificateGradeInPdf',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige der Zeugnisnote im heruntergeladenen Notenbuch (PDF) [Standard: Ja]', true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'YearOfUserView',
            TblSetting::TYPE_STRING, ''))) {
            $this->updateSettingDescription($tblSetting, 'Notenbücher',
                'Anzeige der Noten in der Eltern/Schüler-Übersicht ab folgenden Schuljahr (z.B. 2019/20).
                Wenn leer werden Noten aller Schuljahre angezeigt [Standard: ]', true);
        }
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType',
            TblSetting::TYPE_STRING, '', 'Notenbücher',
            'Eingrenzung der Schüler- und Elternzugänge nach Schulart(en). Festlegung der Zugänge mittels Zahleneingabe.
             Mehrere Schularten sind mit Komma zu trennen. 1=GS, 2=OS, 3=GYM [Standard: ]'
            , true);
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'AddNameRowAtCount',
            TblSetting::TYPE_INTEGER, 10, 'Notenbücher',
            'Anzeige zusätzliche Namensspalte im Notenbuch bei mehr als (Anzahl) Noten im gesamten Schuljahr [Standard: 10]'
            , true);

        if (($tblSetting = $this->createSetting('Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel',
            TblSetting::TYPE_INTEGER, 1))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein', 'Validierung 1. Fremdsprache im Stammdaten- und Bildungsmodul sowie Modul Kamenzstatistik.
             Klassenstufe, ab welcher ist 1. Fremdsprache unterrichtet wird. [Standard: 1]', true);
        }
        if (($tblSetting = $this->createSetting('Reporting', 'SerialLetter', 'GenderSort', 'FirstFemale',
            TblSetting::TYPE_BOOLEAN, 1))) {
            $this->updateSettingDescription($tblSetting, 'Adresslisten für Serienbriefe',
                'Im Excel-Download wird in den Briefanreden nach DIN 5008 die Frau zuerst angesprochen (Sehr geehrte Frau, sehr geehrter Herr). [Standard: Ja]',
                true);
        }

        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Sort', 'SortMaleFirst',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Klassenbücher',
                'Bei der Sortierung der Schüler im Klassenbuch nach Geschlecht, stehen die männlichen Schüler zuerst. [Standard: Ja]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Frontend', 'ShowDownloadButton',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Klassenbücher',
                'Fachlehrer können sich im Klassenbuch die Standard-Klassenlisten-Auswertung (Excel) herunterladen. [Standard: Ja]',
                true);
        }

        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Evaluation',
            'HasBehaviorGradesForSubjectsWithNoGrading', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Leistungsüberprüfungen',
                'Bei Kopfnotenaufträgen können auch Kopfnoten für Fächer vergeben werden, welche nicht benotet werden. [Standard: Nein]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Evaluation',
            'AutoPublicationOfTestsAfterXDays', TblSetting::TYPE_INTEGER, '28'))) {
            $this->updateSettingDescription($tblSetting, 'Leistungsüberprüfungen',
                'Automatische Bekanntgabe von Leistungsüberprüfungen für die Notenübersicht der Schüler/Eltern nach x Tagen: [Standard: 28]',
                true);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Evaluation',
            'ShowProposalBehaviorGrade', TblSetting::TYPE_BOOLEAN, '0'))) {
            $this->updateSettingDescription($tblSetting, 'Leistungsüberprüfungen',
                'Anzeige der Kopfnoten der Klassenlehrer als Notenvorschlag [Standard: Nein]',
                true);
        }

        if (($tblSetting = $this->createSetting('Education', 'Lesson', 'Subject', 'HasOrientationSubjects',
            TblSetting::TYPE_BOOLEAN, '1', 'Allgemein', 'Es werden Neigungskurse verwendet. [Standard: Ja]'))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein', 'Validierung Neigungskurse im Stammdaten- und Bildungsmodul
             sowie Modul Kamenzstatistik. Schulträger unterrichtet Neigungskurse. [Standard: Ja]', true);
        }

        if (($tblSetting = $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein', 'Bei der alphabetischen Sortierung von Namen werden Umlaute ersetzt nach DIN 5007-2 (z.B. ä => ae). 
            Bei Deaktivierung erfolgt Sortierung nach DIN 5007-1 (z.B. Ä/ä und a sind gleich) [Standard: Ja]', true);
        }
        if (($tblSetting = $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords',
            TblSetting::TYPE_BOOLEAN, '1'))) {
            $this->updateSettingDescription($tblSetting, 'Allgemein',
                'Bei der alphabetischen Sortierung von Namen werden Präpositionen (z.B. von, de, etc.) für die Sortierung berücksichtigt [Standard: Ja]',
                true);
        }

        // Fehlzeiten
        $this->createSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence', TblSetting::TYPE_BOOLEAN,
            '1', 'Fehlzeiten', 'Automatische Übernahme der Fehlzeiten aus dem Klassenbuch [Standard: Ja]'
            , true);
    }

    /**
     * @param        $Cluster
     * @param        $Application
     * @param null   $Module
     * @param string $Identifier
     *
     * @return false|TblSetting
     */
    public function getSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier = ''
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblSetting',
            array(
                TblSetting::ATTR_CLUSTER => $Cluster,
                TblSetting::ATTR_APPLICATION => $Application,
                TblSetting::ATTR_MODULE => $Module ? $Module : null,
                TblSetting::ATTR_IDENTIFIER => $Identifier,
            )
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSetting
     */
    public function getSettingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSetting', $Id);
    }

    /**
     * @param bool $IsSystem
     *
     * @return false|TblSetting[]
     */
    public function getSettingAll($IsSystem = false)
    {

        if ($IsSystem) {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_DESCRIPTION => self::ORDER_ASC
            ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_IS_PUBLIC => true
            ), array(
                TblSetting::ATTR_DESCRIPTION => self::ORDER_ASC
            ));
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            $Id);
    }

    /**
     * @param TblAccount $tblAccountStudent
     *
     * @return false|TblStudentCustody[]
     */
    public function getStudentCustodyByStudent(TblAccount $tblAccountStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId()
            ));
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyByStudentAndCustody(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId(),
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_CUSTODY => $tblAccountCustody->getId()
            ));
    }

    /**
     * @param $Cluster
     * @param $Application
     * @param $Module
     * @param $Identifier
     * @param string $Type
     * @param $Value
     * @param string $Category
     * @param string $Description
     * @param bool $IsPublic
     *
     * @return TblSetting
     */
    public function createSetting(
        $Cluster,
        $Application,
        $Module,
        $Identifier,
        $Type,
        $Value,
        $Category = 'Allgemein',
        $Description = '',
        $IsPublic = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_CLUSTER => $Cluster,
            TblSetting::ATTR_APPLICATION => $Application,
            TblSetting::ATTR_MODULE => $Module ? $Module : null,
            TblSetting::ATTR_IDENTIFIER => $Identifier,
        ));
        if ($Entity === null) {
            $Entity = new TblSetting();
            $Entity->setCluster($Cluster);
            $Entity->setApplication($Application);
            $Entity->setModule($Module);
            $Entity->setIdentifier($Identifier);
            $Entity->setType($Type);
            $Entity->setValue($Value);
            $Entity->setCategory($Category);
            $Entity->setDescription($Description);
            $Entity->setIsPublic($IsPublic);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param $value
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updateSetting(TblSetting $tblSetting, $value)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string $category
     * @param string $description
     * @param bool $isPublic
     *
     * @return bool
     */
    public function updateSettingDescription(TblSetting $tblSetting, $category, $description, $isPublic = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setCategory($category);
            $Entity->setDescription($description);
            $Entity->setIsPublic($isPublic);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     * @param TblAccount $tblAccountBlocker
     *
     * @return TblStudentCustody
     */
    public function createStudentCustody(
        TblAccount $tblAccountStudent,
        TblAccount $tblAccountCustody,
        TblAccount $tblAccountBlocker
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentCustody();
        $Entity->setServiceTblAccountStudent($tblAccountStudent);
        $Entity->setServiceTblAccountCustody($tblAccountCustody);
        $Entity->setServiceTblAccountBlocker($tblAccountBlocker);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeStudentCustody(TblStudentCustody $tblStudentCustody)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentCustody')->findOneBy(array('Id' => $tblStudentCustody->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param TblConsumer $tblConsumer
     *
     * @return string
     */
    public function getSettingByConsumer(TblSetting $tblSetting, TblConsumer $tblConsumer)
    {

        $value = '';
        $connection = false;
        $container = Database::getDataBaseConfig($tblConsumer);

        if ($container) {
            try {
                $connection = $this->getConnectionByAcronym(
                    $container->getContainer('Host')->getValue(),
                    $container->getContainer('Username')->getValue(),
                    $container->getContainer('Password')->getValue(),
                    $tblConsumer->getAcronym()
                );

                if ($connection) {
                    $queryBuilder = $connection->getQueryBuilder();

                    $query = $queryBuilder->select('S.Value')
                        ->from('SettingConsumer_' . $tblConsumer->getAcronym() . '.tblSetting', 'S')
                        ->where('S.Identifier = :identifier')
                        ->setParameter('identifier', $tblSetting->getIdentifier());

                    $result = $query->execute();
                    $array = $result->fetch();

                    if (isset($array['Value'])) {
                        $value = $array['Value'];
                    }

                    $connection->getConnection()->close();
                }
            } catch (\Exception $Exception) {
                if ($connection) {
                    $connection->getConnection()->close();
                }
                $connection = null;
            }
        }

        return $value;
    }

    /**
     * @param string $Host Server-Address (IP)
     * @param string $User
     * @param string $Password
     * @param string $Acronym DatabaseName will get prefix 'SettingConsumer_' e.g. SettingConsumer_{Acronym}
     *
     * @return bool|IBridgeInterface
     */
    private function getConnectionByAcronym($Host, $User, $Password, $Acronym)
    {
        $Connection = MocDatabase::getDatabase(
            $User, $Password, 'SettingConsumer_' . strtoupper($Acronym), (new MySql())->getIdentifier(), $Host
        );
        if ($Connection->getConnection()->isConnected()) {
            return $Connection;
        }
        return false;
    }
}