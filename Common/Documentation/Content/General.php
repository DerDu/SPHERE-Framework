<?php
namespace SPHERE\Common\Documentation\Content;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Documentation\Designer\Book;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Text\Repository\Bold;

/**
 * Class General
 *
 * @package SPHERE\Common\Documentation\Content
 */
class General
{

    /** @var Book $Book */
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

        $this->Book = $Designer->createBook('Allgemeine Hilfe');
        $this->Book->setVisible($Chapter, $Page);

        $Chapter = $this->Book->createChapter('Internet Browser', '', true);
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

            $Page = $Chapter->createPage('Was ist ein Browser', '', $Search, true);
            $Page->addParagraph('Ein Browser ist ein Software-Programm, mit dem Sie Internet- Seiten (auch Webseiten genannt) aus dem Internet abrufen, anzeigen und ansehen können. Er wird auch Webbrowser genannt.');
            $Page->addHeadline('Welchen Browser verwende Ich?', '', true);
            $Page->addParagraph('Diese Frage lässt sich recht einfach durch das aufrufen folgender Webseite beantworten');
            $Page->addParagraph('http://www.whatbrowser.org/');
            $Page->addParagraph(new External('Meine Browser-Version anzeigen', 'http://www.whatbrowser.org/'));

            $Page->addHeadline('Welcher Browser ist der Schnellste?', '', true);
            $Page->addParagraph('Diese Frage ist eigentlich schnell geklärt. Bislang schein wohl der Google Chrome gefolgt vom Apple Safari bei der Geschwindigkeit die Nase vorn zu haben. Trotz vieler Optimierungen seitens Mozilla ist der Firefox immer noch sehr träge und benötigt einige Zeit zum starten. Auch der neue Browser “Microsoft Edge” ist sehr schnell.');
            $Page->addParagraph('Auch beim Aufbau der Webseiten und gerade bei der Ausführung von Java Script ist der Firefox das Schlusslicht. Moderne Webseiten besitzen immer mehr dynamische Seitenelemente und Inhalte, die per Java Skript nachgeladen werden. Wer also einen stabilen und schnellen Browser sucht, ist mit dem Chrome sehr gut beraten.');

            $Page->addHeadline('Welcher Browser wird für die Schulsoftware empfohlen?', '', true);
            $Page->addParagraph(

                new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_chrome.jpg'),
                                'Google Chrome',
                                'Beim Aufbau der Webseiten und gerade bei der Ausführung von Java Script ist dieser Browser einer der schnellsten',
                                new External('Download', 'https://www.google.de/chrome/browser/desktop/')
                            ), 5),
                        )),
                    ))
                )

            );

            $Page = $Chapter->createPage('Welche Browser werden unterstützt', '', $Search, true);
            $Page->addParagraph(new Bold('Es sollten alle gängigen Browser unterstützt werden'));
            $Page->addParagraph('Die folgenden Browser wurden von uns speziell getestet');

            $Page->addParagraph(

                new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_chrome.jpg'), 'Chrome'
                            ), 3),
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_firefox.jpg'),
                                'Firefox'
                            ), 3),
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_IE.jpg'),
                                'Internet Explorer'
                            ), 3),
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_opera.jpg'), 'Opera'
                            ), 3),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Browser/browser_safari.jpg'), 'Safari'
                            ), 3),
                        )),
                    ))
                )

            );

        }
        /*
                $Chapter = $this->Book->createChapter('Browser Cache', '', true);
                if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

                    $Page = $Chapter->createPage('Browser Cache', '', $Search, true);
                    $Page->addHeadline('Was ist das?', '', true);
                    $Page->addParagraph('http://www.whatbrowser.org/');
                    $Page->addParagraph('http://www.krohn.io/technik/web/browser-cache-leeren/');
                    $Page->addSeparator();
                    $Page->addHeadline('Welche Auswirkungen hat der Browser Cache', '', true);
                    $Page->addParagraph('');
                    $Page->addSeparator();
                    $Page->addHeadline('Den Browser-Cache leeren', '', true);
                    $Page->addParagraph('');
                    $Page->addSeparator();
                }
        */
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}
