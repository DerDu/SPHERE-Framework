<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 11:50
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class F01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym
 */
class F01
{
    /**
     * @return Slice[]
     */
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('F01. Inklusiv unterrichtete Schüler mit sonderpädagogischem Förderbedarf im Schuljahr
                    {{ Content.SchoolYear.Current }} </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; nach 
                    Förderschwerpunkten und Klassen- bzw. Jahrgangsstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Förderschwerpunkt(e)')
                    ->styleBorderRight()
                    ->stylePaddingTop('43.35px')
                    ->stylePaddingBottom('43.35px'), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Klassenstufe')
                    ->styleBorderRight()
                    ->stylePaddingTop('43.35px')
                    ->stylePaddingBottom('43.35px'), '15%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('34.25px')
                            ->stylePaddingBottom('34.25px'), '30%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '50%'
                        )
                    ), '21.66%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('<b>Darunter</b> von Spalte Schüler'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler, deren<br/>Herkunftssprache nicht<br/>oder nicht ausschl.<br/>Deutsch ist')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schüler mit<br/>gutachterl.<br/>best. Autismus<br/>&nbsp;')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '25%'
                        )
                    )
                    , '43.34%'
                )
            );

        for ($i = 0; $i < 8; $i++) {
            $isBold = false;
            $paddingTop = '72.1px';
            $paddingBottom = '72.1px';
            switch ($i) {
                case 0:
                    $text = 'Lernen';
                    break;
                case 1:
                    $text = 'Sehen';
                    break;
                case 2:
                    $text = 'Hören';
                    break;
                case 3:
                    $text = 'Sprache';
                    break;
                case 4:
                    $text = 'Körperlich-motorische Entwicklung';
                    $paddingTop = '55px';
                    $paddingBottom = '55px';
                    break;
                case 5:
                    $text = 'Geistige Entwicklung';
                    break;
                case 6:
                    $text = 'Sozial-emotionale Entwicklung';
                    $paddingTop = '64px';
                    $paddingBottom = '64px';
                    break;
                case 7:
                    $text = 'Insgesamt';
                    $isBold = true;
                    break;
                default:
                    $text = '';
            }

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom), '20%'
                );

            // Klassenstufe
            $lineSectionList = array();
            for ($j = 5; $j < 13; $j++) {
                $lineSection = new Section();
                $lineSection
                    ->addElementColumn((new Element())
                        ->setContent($j)
                        ->styleBorderBottom()
                    );
                $lineSectionList[] = $lineSection;
            }
            $lineSectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zusammen')
                    ->styleTextBold()
                );
            $section
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSectionList($lineSectionList)
                    , '15%'
                );

            // Schüler
            self::setColumn($section, $text, 'Student', 'm');
            self::setColumn($section, $text, 'Student', 'w');

            // Migrationshintergrund
            self::setColumn($section, $text, 'HasMigrationBackground', 'm');
            self::setColumn($section, $text, 'HasMigrationBackground', 'w');

            // Autismus
            self::setColumn($section, $text, 'Autism', 'm');
            self::setColumn($section, $text, 'Autism', 'w');

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->addSection($section);
        }

        return $sliceList;
    }

    /**
     * @param $section
     * @param $text
     * @param $identifier
     * @param $gender
     */
    private static function setColumn(Section $section, $text, $identifier, $gender)
    {

        if ($text == 'Insgesamt') {
            $name = 'TotalCount';
            $isGrey = true;
        } else {
            $name = preg_replace('/[^a-zA-Z]/', '', $text);
            $isGrey = false;
        }

        $lineSectionList = array();
        for ($j = 5; $j < 13; $j++) {
            $lineSection = new Section();
            $lineSection
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.F01.' . $name . '.' . $identifier . '.L' . $j . '.' . $gender . ' is not empty) %}
                                {{ Content.F01.' . $name . '.' . $identifier . '.L' . $j . '.' . $gender . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderBottom()
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                );
            $lineSectionList[] = $lineSection;
        }
        $lineSectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.F01.' . $name . '.' . $identifier . '.TotalCount.' . $gender . ' is not empty) %}
                        {{ Content.F01.' . $name . '.' . $identifier . '.TotalCount.' . $gender . ' }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleTextBold()
                ->styleBackgroundColor('lightgrey')
            );
        $section
            ->addSliceColumn((new Slice())
                ->styleBorderRight()
                ->addSectionList($lineSectionList)
                , '10.833%'
            );
    }
}