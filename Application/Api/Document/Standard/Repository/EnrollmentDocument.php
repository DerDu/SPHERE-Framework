<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:06
 */

namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class EnrollmentDocument extends AbstractDocument
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schulbescheinigung';
    }

    /**
     * @return Frame
     */
    public function buildDocument()
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulbescheinigung')
                            ->stylePaddingTop('180px')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('25px')
                            ->styleTextBold()
                            ->styleTextItalic()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                    Die Schülerin
                                {% else %}
                                    {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                        Der Schüler
                                    {% else %}
                                        Die Schülerin/Der Schüler
                                    {% endif %}
                                {% endif %}
                            ')
                            ->stylePaddingTop('50px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.First is not empty) %}
                                    {{ Content.Person.Data.Name.First }} {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->stylePaddingTop('50px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren in')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('wohnhaft')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                               {% if(Content.Person.Address.Street.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                               {% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('besucht zur Zeit die Klasse')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                               {% if(Content.Student.Division.Name) %}
                                    {{ Content.Student.Division.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                    Sie
                                {% else %}
                                    {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                        Er
                                    {% else %}
                                        Sie/Er
                                    {% endif %}
                                {% endif %}
                                wird voraussichtlich bis zum
                            ')
                            ->stylePaddingTop('100px')
                            ->stylePaddingLeft()
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.LeaveDate) %}
                                    {{ Content.Student.LeaveDate }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop('100px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '65%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if Content.Person.Common.BirthDates.Gender == 2 %}
                                    Schülerin
                                {% else %}
                                    {% if Content.Person.Common.BirthDates.Gender == 1 %}
                                        Schüler
                                    {% else %}
                                        Schülerin/Schüler
                                    {% endif %}
                                {% endif %}
                                unserer Schule sein.
                            ')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Document.PlaceDate) %}
                                    {{ Content.Document.PlaceDate }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop('100px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '45%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      &nbsp;
                             ')
                            ->stylePaddingTop('100px')
                            ->stylePaddingLeft()
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      &nbsp;
                             ')
                            ->stylePaddingTop('100px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Ort, Datum
                            ')
                            ->styleTextSize('12px')
                            , '45%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      &nbsp;
                             ')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      Schulstempel
                             ')
                            ->stylePaddingTop('0px')
                            ->styleMarginTop('0px')
                            ->styleTextSize('12px')
                            ->stylePaddingLeft()
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                &nbsp;
                            ')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            , '65%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      &nbsp;
                             ')
                            ->stylePaddingTop('30px')
                            ->stylePaddingLeft()
                            ->styleBorderBottom()
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                      &nbsp;
                             ')
                            , '65%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                      Schulleiter/in
                             ')
                            ->stylePaddingTop('0px')
                            ->styleMarginTop('0px')
                            ->styleTextSize('12px')
                            ->stylePaddingLeft()
                            , '35%')
                    )
                )
            )
        );
    }
}