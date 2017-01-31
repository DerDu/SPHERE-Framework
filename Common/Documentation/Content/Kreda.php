<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTrace;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Italic;

/**
 * Class Kreda
 *
 * @package SPHERE\Common\Documentation\Content
 */
class Kreda
{

    /** @var Designer\Book $Book */
    private $Book = null;

    /**
     * @param null|string $Chapter
     * @param null|string $Page
     * @param null|string $Search
     */
    public function __construct($Chapter = null, $Page = null, $Search = null)
    {

        $this->ShowChapter = $Chapter;
        $this->ShowPage = $Page;

        $Designer = new Designer();

        $this->Book = $Designer->createBook('Schulsoftware Handbuch');
        $this->Book->setVisible($Chapter, $Page);

        $this->setChapterGrades($Search);
    }

    /**
     * @param $Search
     */
    private function setChapterGrades($Search)
    {

        $Chapter = $this->Book->createChapter('Zensuren', '', true);
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

            /*
             * GradeType
             */
            $Page = $Chapter->createPage('Zensuren-Typen', '', $Search, true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'           => 'Bildung',
                'Graduation'          => 'Zensuren',
                'Gradebook/GradeType' => 'Zensuren-Typ'
            )));
            $Page->addParagraph(
                'Die Zensuren-Typen bestimmen die Arten/Typen von Zensuren der Schulsoftware. Sie werden in die Kategorien '
                .new Italic('Kopfnote').' und '.new Italic('Leistungsüberprüfung').' unterteilt. Die Kateogie
                bestimmt, wo die Zensuren-Typen ausgewählt werden können. Die Zensuren-Typen der Kategorie '
                .new Italic('Kopfnote').' können nur bei Kopfnotenaufträgen (für Zeugnisse) gewählt werden, hingegen die Zensuren-Typen
                 der Kategorie '.new Italic('Leistungsüberprüfung').' können nur bei Leistungsüberprüfungen gewählt werden.'
            );

            $Page->addHeadline('Wie erstelle ich einen neuen Zensuren-Typ?', 'Anlegen', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'           => 'Bildung',
                'Graduation'          => 'Zensuren',
                'Gradebook/GradeType' => 'Zensuren-Typ'
            )));
            $Page->addParagraph(
                'Auf der Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen Zensuren-Typen.
                 Im unteren Bereich der Seite kann ein neuer Zensuren-Typ hinzugefügt werden. Dazu wählen Sie bitte eine
                  Kategorie aus, geben eine Abkürzung und einen Namen ein. Außerdem kann optional eine Beschreibung eingeben
                  werden und der Zensuren-Typ Fett markiert werden (für wichtigte Zensuren-Typen z.B.: Klassenarbeiten).'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleGradeTypeNew.jpg'),
                    ''
                )
            );
            $Page->addHeadline('Wie bearbeite ich einen vorhandenen Zensuren-Typ?', 'Bearbeiten', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'           => 'Bildung',
                'Graduation'          => 'Zensuren',
                'Gradebook/GradeType' => 'Zensuren-Typ'
            )));
            $Page->addParagraph(
                'Auf der Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen Zensuren-Typen.
                 Um einen Zensuren-Typ zu bearbeiten klicken Sie bitte rechts auf den '.new Edit()
                .' -Button des entsprechenden Zensuren-Typs.'
            );

            /*
             * ScoreRule
             */
            $Page = $Chapter->createPage('Berechnungsvorschrift', '', $Search, true);
            $Page->addParagraph(
                ''
            );

            /*
             * Gradebook
             */
            $Page = $Chapter->createPage('Notenbuch', '', $Search, true);
            $Page->addParagraph(
                'Das '.new Italic('Notenbuch').' dient der Anzeige der Notenbücher (Zensuren-Übersicht der Schüler), wo
                der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist. Hingegen beim '
                .new Italic('Notenbuch (Leitung)').' werden alle Notenbücher angezeigt.'
            );

            $Page->addHeadline('Wie wähle ich ein Notenbuch aus?', 'Auswählen', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'           => 'Bildung',
                'Graduation'          => 'Zensuren',
                'Gradebook/Gradebook' => 'Notenbuch'
            )));
            $Page->addParagraph(
                'Auf der Auswahl-Seite befindet sich eine Übersicht über die Fach-Klassen(-Gruppen).
                 Um ein Notenbuch zu öffnen klicken Sie bitte rechts auf den '.new Select()
                .' -Button der entsprechenden Fach-Klasse.'
            );

            $Page->addHeadline('Welche Informationen werden im Notenbuch angezeigt?', 'Anzeigen', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'           => 'Bildung',
                'Graduation'          => 'Zensuren',
                'Gradebook/Gradebook' => 'Notenbuch'
            )));
            $Page->addParagraph(
                'Links oben wird die ausgewählte Fach-Klasse (und falls vorhanden die Gruppe) angezeigt. Wenn eine Berechnungsvorschrift bei der Fach-Klasse hinterlegt ist, wird diese rechts oben angezeigt.'
            );
            $Page->addParagraph(
                'Als erstes wird bei der Berechnungsvorschrift der Name, gefolgt von den Berechnungsvarianten (beginnend
                  mit der höchsten Priorität [kleinste Zahl]), angezeigt. Unterhalb der Fach-Klasse und der
                  Berechnungsvorschrift befindet sich die Zensuren-Übersicht. Bei dieser werden links alle Schüler der
                  Fach-Klasse(-Gruppe) dargestellt. Zusätzlich wird der Zensuren-Durchschnitt des Schülers für das
                  gesamte Schuljahr, und in Klammern die Priorität der verwendenten Berechnungsvariante, angezeigt.'
            );
            $Page->addParagraph(
                'Neben den Schülern werden alle Zeiträume des Schuljahres mit den Zensuren der Schüler in diesem Zeitraum dargestellt.
                  Dazu wird über den Zensuren das Datum und die Abkürzung der zugehörigen Leistungsüberprüfung angezeigt.
                  Zum Abschluss eines jeden Zeitraums wird der Zensuren-Durchschnitt des Schülers für diesen
                  Zeitraum, und in Klammern die Priorität der verwendenten Berechnungsvariante, angezeigt.'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleGradebookView.jpg'),
                    ''
                )
            );

            /*
            * Test
            */
            $Page = $Chapter->createPage('Leistungsüberprüfung', 'Zensuren vergeben', $Search, true);
            $Page->addParagraph(
                ' Die '.new Italic('Leistungsüberprüfung').' dient der Verwaltung von Leistungsüberprüfungen
                 (inklusive der Eingabe von Zensuren), wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer
                  hinterlegt ist. Hingegen bei der '.new Italic('Leistungsüberprüfung (Leitung)').' können
                  alle Leistungsüberprüfungen verwaltet werden. Zusätzlich zu den Leistungsüberprüfungen werden hier
                  auch die Zensuren für die Kopfnoten- und Stichtagsnotenaufträge vergeben, diese werden angezeigt sobald
                  die Schulleitung, welche erteilt hat.'
            );

            $Page->addHeadline('Wie erstelle Ich eine neue Leistungsüberprüfung?', 'Anlegen', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'       => 'Bildung',
                'Graduation'      => 'Zensuren',
                'Evaluation/Test' => 'Leistungsüberprüfung'
            )));
            $Page->addParagraph(
                'Auf der Auswahl-Seite befindet sich eine Übersicht über die Fach-Klassen(-Gruppen).
                 Um eine neue Leistungsüberprüfung zu erstellen klicken Sie bitte rechts auf den '.new Select()
                .' -Button der entsprechenden Fach-Klasse für die Sie eine neue Leistungsüberprüfung anlegen möchten.'
            );
            $Page->addParagraph(
                'Auf der folgenden Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen
                  Leistungsüberprüfungen der gewählten Fach-Klasse. Im unteren Bereich der Seite kann eine neue
                  Leistungsüberprüfung hinzugefügt werden. Dazu wählen Sie bitte einen Zeitraum und einen Zensuren-Typ aus.
                  Außerdem kann optional eine Beschreibung eingeben werden, sowie die Daten für die den Tag der
                  Leistungsüberprüfung, der Korrektur und der Rückgabe.'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleTestNew.jpg'),
                    ''
                )
            );

            $Page->addHeadline('Wie bearbeite Ich eine vorhandene Leistungsüberprüfung?', 'Bearbeiten', true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'       => 'Bildung',
                'Graduation'      => 'Zensuren',
                'Evaluation/Test' => 'Leistungsüberprüfung'
            )));
            $Page->addParagraph(
                'Auf der Auswahl-Seite befindet sich eine Übersicht über die Fach-Klassen(-Gruppen).
                 Um eine vorhandene Leistungsüberprüfung zu bearbeiten klicken Sie bitte rechts auf den '.new Select()
                .' -Button der entsprechenden Fach-Klasse für die Sie eine vorhandene Leistungsüberprüfung bearbeiten möchten.'
            );
            $Page->addParagraph(
                'Auf der folgenden Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen
                  Leistungsüberprüfungen der gewählten Fach-Klasse. Um eine Leistungsüberprüfung zu bearbeiten klicken
                  Sie bitte rechts auf den '.new Edit().' -Button der entsprechenden Leistungsüberprüfung. Hier können
                  Sie die Beschreibung, das Datum, das Korrekturdatum und das Rückgabedatum der gewählten
                  Leistungsüberprüfung ändern.'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleTestEdit.jpg'),
                    ''
                )
            );

            $Page->addHeadline('Wie vergebe Ich Zensuren zu einer vorhandenen Leistungsüberprüfung?', 'Eintragen',
                true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'       => 'Bildung',
                'Graduation'      => 'Zensuren',
                'Evaluation/Test' => 'Leistungsüberprüfung'
            )));
            $Page->addParagraph(
                'Auf der Auswahl-Seite befindet sich eine Übersicht über die Fach-Klassen(-Gruppen).
                 Um Zensuren zu einer vorhandenen Leistungsüberprüfung zu vergeben klicken Sie bitte rechts auf den '.new Select()
                .' -Button der entsprechenden Fach-Klasse für die Sie Zensuren vergeben möchten.'
            );
            $Page->addParagraph(
                'Auf der folgenden Seite befindet sich im oberen Bereich eine Übersicht über die aktuell vorhandenen
                  Leistungsüberprüfungen der gewählten Fach-Klasse. Um Zensuren zu einer vorhandenen Leistungsüberprüfung
                  zu vergeben  klicken Sie bitte rechts auf den '.new Listing().' -Button der entsprechenden
                  Leistungsüberprüfung.'
            );
            $Page->addParagraph(
                'Auf der folgenden Seite befindet sich im oberen Bereich die ausgewählte Fach-Klasse
                  (und falls vorhanden die Gruppe), der Schuljahres-Zeitraum und er Zensuren-Typ der Leistungsüberprüfung.
                  Falls für die Fach-Klasse das Bewertungssystem '.new Italic('Noten (1-6)').' oder '.new Italic('Punkte (0-15)')
                .' hinterlegt ist, wird anschließend der entsprechende Notenspiegel angezeigt. Unter dem Notenspiegel
                  können die Zensuren für alle Schüler der Fach-Klasse(-Gruppe) vergeben werden. Diese gliedert sich
                  in '.new Italic('Zensur, Kommentar').' und der Möglichkeit: '.new Italic('Nicht teilgenommen.')
            );
            $Page->addParagraph(
                new Info().new Bold(' Hinweis:')
                .' Die Eingabe-Möglichkeiten für die '.new Italic('Zensur').' wird durch das hinterlegte
                  Bewertungssystem bestimmt.'
                .new Table(
                    new TableHead(array(
                        new TableRow(array(
                            new TableColumn(
                                'Berechnungssystem'
                            ),
                            new TableColumn(
                                'Zensur'
                            ),
                        ))
                    ))
                    , new TableBody(array(
                        new TableRow(array(
                            new TableColumn(
                                'Nicht ausgewählt'
                            ),
                            new TableColumn(
                                'Ganzzahlig (ohne Wertebeschränkung) + Tendenz-Auswahl'
                            ),
                        )),
                        new TableRow(array(
                            new TableColumn(
                                'Noten (1-6)'
                            ),
                            new TableColumn(
                                'Ganzzahlig (mit Wertebeschränkung zw. 1 und 6) + Tendenz-Auswahl'
                            ),
                        )),
                        new TableRow(array(
                            new TableColumn(
                                'Punkte (0-15)'
                            ),
                            new TableColumn(
                                'Ganzzahlig (mit Wertebeschränkung zw. 0 und 15)'
                            ),
                        )),
                        new TableRow(array(
                            new TableColumn(
                                'Verbale Bewertung'
                            ),
                            new TableColumn(
                                'Text'
                            ),
                        )),
                    ))
                )
                .new Info().new Bold(' Hinweis:')
                .' Der Fachlehrer kann die Zensuren nur einmal vergeben und danach nicht mehr bearbeiten.
                  Nur die Schulleitung ('.new Italic('Leistungsüberprüfung (Leitung)').') und der Klassenlehrer
                  können Zensuren noch nachträglich ändern.');
            $Page->addParagraph(
                new Info().new Bold(' Hinweis:')
                .' Der Notenspiegel akualisiert sich erst nach dem Speichern der Zensuren.'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleTestGradesEdit.jpg'),
                    ''
                )
            );

            $Page->addHeadline('Wie vergebe Ich Zensuren zu einem Kopfnotenauftrag?', 'Eintragen',
                true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'       => 'Bildung',
                'Graduation'      => 'Zensuren',
                'Evaluation/Test' => 'Leistungsüberprüfung'
            )));
            $Page->addParagraph(
                'Falls die Schulleitung einen Kopfnotenauftrag erteilt hat, werden für jeden Zensuren-Typ der Kateogrie
                  Kopfnote jeweils ein Eintrag in der Übersicht der Leistungsüberprüfungen angezeigt. Ansonsten werden die
                  Zensuren für den Kopfnotenauftrag analog dem vorherigen Abschnitt (Wie vergebe Ich Zensuren zu einer
                  vorhandenen Leistungsüberprüfung?) vergeben.'
            );

            $Page->addHeadline('Wie vergebe Ich Zensuren zu einem Stichtagsnotenauftrag?', 'Eintragen',
                true);
            $Page->addParagraph(new LayoutTrace(array(
                'Education'       => 'Bildung',
                'Graduation'      => 'Zensuren',
                'Evaluation/Test' => 'Leistungsüberprüfung'
            )));
            $Page->addParagraph(
                'Falls die Schulleitung einen Stichtagsnotenauftrag erteilt hat, wird dieser ebenfalls in der Übersicht
                  der Leistungsüberprüfungen angezeigt. Die Zensuren Vergabe bei Stichtagsnotenaufträgen funktioniert
                  prinzipiell wie bei Leistungsüberprüfungen. Falls bei der Fach-Klasse eine Berechnungsvorschrift
                  hinterlegt ist, wird diese zusätzlich mit ihren Berechnungsvarianten angezeigt. Außerdem werden alle
                  Zensuren von Leistungsüberprüfungen nach Zeiträumen, die Priorität der verwendeten Berechungsvariante (für
                  die Berechnung der Durchschnittsnote) und die Durchschnittsnote angezeigt.'
            );
            $Page->addParagraph(
                new Thumbnail(
                    FileSystem::getFileLoader('/Common/Style/Resource/Example/exampleApointedDateTaskGradesEdit.jpg'),
                    ''
                )
            );

        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}
