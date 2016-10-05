<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'BeGs');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', 'Mittelschule Klasse 5-6', 'BeMi');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 3);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInformation');
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true,
                Student::useService()->getStudentLiberationCategoryById(1));
            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3, false);
            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 4, false);
        }

        $this->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse', 'GsHjOneInfo');

        $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', '', 'GsJa');
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true,
                Student::useService()->getStudentLiberationCategoryById(1));
            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3, false);
            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 4, false);
        }

        $this->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        $this->createCertificate('Gymnasium Abgangszeugnis', 'Hauptschulabschluss Klasse 9', 'GymAbgHs');
        $this->createCertificate('Gymnasium Abgangszeugnis', 'Realschulabschluss Klasse 10', 'GymAbgRs');
        $this->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        $this->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        $this->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        $this->createCertificate('Mittelschule Abgangszeugnis', 'Hauptschule', 'MsAbgHs');
        $this->createCertificate('Mittelschule Abgangszeugnis', 'Realschule', 'MsAbgRs');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule qualifiziert', 'MsAbsHsQ');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Realschule', 'MsJRs');

        /*
         * Noteninformation
         */
        $tblCertificate = $this->createCertificate('Noteninformation', '',
            'GradeInformation', null, true);
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                && $tblConsumer->getAcronym() == 'ESZC'
            ) {
                $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BIO', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'TC', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'RELI', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            } else {
                $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BI', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'IN', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'REV', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            }
        }

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer) {
            if ($tblConsumer->getAcronym() == 'ESZC' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESZC');
                if ($tblConsumerCertificate) {

                    $tblCertificate = $this->createCertificate(
                        'Bildungsempfehlung', 'Klassenstufe 4', 'ESZC\CheBeGs', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Bildungsempfehlung', 'Gymnasium', 'ESZC\CheBeGym', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                    }

                    $this->createCertificate(
                        'Bildungsempfehlung', 'Mittelschule', 'ESZC\CheBeMi', $tblConsumerCertificate
                    );

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Hauptschule', 'ESZC\CheHjInfoHs', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Klasse 5-6', 'ESZC\CheHjInfo', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Realschule', 'ESZC\CheHjInfoRs', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Gymnasium', 'ESZC\CheHjGymInfo', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
                    }

                    $this->createCertificate(
                        'Halbjahreszeugnis', 'Gymnasium', 'ESZC\CheHjGym', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Halbjahreszeugnis', 'Hauptschule', 'ESZC\CheHjHs', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Halbjahreszeugnis', 'Klasse 5-6', 'ESZC\CheHj', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Halbjahreszeugnis', 'Realschule', 'ESZC\CheHjRs', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Jahreszeugnis', 'Mittelschule', 'ESZC\CheJ', $tblConsumerCertificate
                    );

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Gymnasium', 'ESZC\CheJGym', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        // Ethik gibt es nicht?
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'INFO', 2, 8);
                    }

                    $this->createCertificate(
                        'Jahreszeugnis', 'Grundschule Klasse 2-4', 'ESZC\CheJGs', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Jahreszeugnis', 'Grundschule Klasse 1', 'ESZC\CheJGsOne', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Habljahresinformation', 'Grundschule Klasse 2-4', 'ESZC\CheHjInfoGs', $tblConsumerCertificate
                    );
                    $this->createCertificate(
                        'Habljahresinformation', 'Grundschule Klasse 1', 'ESZC\CheHjInfoGsOne', $tblConsumerCertificate
                    );
                }
            }

            if ($tblConsumer->getAcronym() == 'EVSC' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSC');
                if ($tblConsumerCertificate) {

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Primarstufe', 'EVSC\CosHjPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Sekundarstufe', 'EVSC\CosHjSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Primarstufe', 'EVSC\CosJPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Sekundarstufe', 'EVSC\CosJSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'FESH' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('FESH');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '', 'FESH\HorHj', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $this->createCertificate(
                        'Halbjahresinformation', '1. Klasse', 'FESH\HorHjOne', $tblConsumerCertificate
                    );

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '', 'FESH\HorJ', $tblConsumerCertificate
                    );
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $this->createCertificate(
                        'Jahreszeugnis', '1. Klasse', 'FESH\HorJOne', $tblConsumerCertificate
                    );
                }
            }
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Certificate
     * @param TblConsumer|null $tblConsumer
     * @param bool $IsGradeInformation
     *
     * @return null|object|TblCertificate
     */
    public function createCertificate(
        $Name,
        $Description,
        $Certificate,
        TblConsumer $tblConsumer = null,
        $IsGradeInformation = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array(
            TblCertificate::ATTR_CERTIFICATE => $Certificate
        ));

        if (null === $Entity) {
            $Entity = new TblCertificate();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCertificate($Certificate);
            $Entity->setServiceTblConsumer($tblConsumer);
            $Entity->setIsGradeInformation($IsGradeInformation);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblGradeType $tblGradeType
     *
     * @return null|object|TblCertificateGrade
     */
    public function createCertificateGrade(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblGradeType $tblGradeType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array(
            TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateGrade::ATTR_LANE => $LaneIndex,
            TblCertificateGrade::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateGrade();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblGradeType($tblGradeType);
            $Entity->setEssential(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function updateCertificateGrade(TblCertificateGrade $tblCertificateGrade, TblGradeType $tblGradeType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateGrade $Entity */
        $Entity = $Manager->getEntityById('TblCertificateGrade', $tblCertificateGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblGradeType($tblGradeType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     * @param null|TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return TblCertificateSubject
     */
    public function createCertificateSubject(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblSubject $tblSubject,
        $IsEssential = false,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array(
            TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateSubject::ATTR_LANE => $LaneIndex,
            TblCertificateSubject::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateSubject();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblStudentLiberationCategory($tblStudentLiberationCategory);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     * @param null|TblStudentLiberationCategory $tblStudentLiberationCategory
     *
     * @return bool
     */
    public function updateCertificateSubject(
        TblCertificateSubject $tblCertificateSubject,
        TblSubject $tblSubject,
        $IsEssential = false,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setServiceTblStudentLiberationCategory($tblStudentLiberationCategory);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function removeCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return false|TblCertificate[]
     */
    public function getTemplateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate');
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null)
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }


    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificate', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_CERTIFICATE => $Class
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateSubject', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(TblCertificate $tblCertificate, TblSubject $tblSubject)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateSubject',
            array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::ATTR_LANE => $LaneIndex,
                TblCertificateSubject::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateGrade::ATTR_LANE => $LaneIndex,
                TblCertificateGrade::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $SubjectAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     * @param bool|true $IsEssential
     * @param TblStudentLiberationCategory|null $tblStudentLiberationCategory
     */
    private function setCertificateSubject(
        TblCertificate $tblCertificate,
        $SubjectAcronym,
        $LaneIndex,
        $LaneRanking,
        $IsEssential = true,
        TblStudentLiberationCategory $tblStudentLiberationCategory = null
    ) {

        if (($tblSubject = Subject::useService()->getSubjectByAcronym($SubjectAcronym))) {
            $this->createCertificateSubject($tblCertificate, $LaneIndex, $LaneRanking, $tblSubject,
                $IsEssential, $tblStudentLiberationCategory);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $GradeTypeAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     */
    private function setCertificateGrade(
        TblCertificate $tblCertificate,
        $GradeTypeAcronym,
        $LaneIndex,
        $LaneRanking
    ) {

        if (($tblGradeType = Gradebook::useService()->getGradeTypeByCode($GradeTypeAcronym))) {
            $this->createCertificateGrade($tblCertificate, $LaneIndex, $LaneRanking, $tblGradeType);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     */
    private function setCertificateGradeAllStandard(
        TblCertificate $tblCertificate
    ) {
        $this->setCertificateGrade($tblCertificate, 'KBE', 1, 1);
        $this->setCertificateGrade($tblCertificate, 'KFL', 1, 2);

        $this->setCertificateGrade($tblCertificate, 'KMI', 2, 1);
        $this->setCertificateGrade($tblCertificate, 'KOR', 2, 2);
    }
}
