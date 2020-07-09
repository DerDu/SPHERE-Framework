<?php

namespace SPHERE\Application\Api\Document\Custom\Radebeul\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class StudentCard
 *
 * @package SPHERE\Application\Api\Document\Custom\Radebeul\Repository#
 */
class StudentCard extends AbstractDocument
{

    const TEXT_SIZE = '16px';

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerbogen';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        $textSize = '16px';
        $fontFamily = 'MetaPro';
        $PaddingBottom = '18px';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Evangelisches Schulzentrum Radebeul')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->stylePaddingTop('10px')
                            ->styleAlignCenter()
                            ->styleTextSize('23px')
                            , '60%'
                        )
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSR.jpg',
                            '120px', '120px'))
                            ->stylePaddingTop('10px')
                            ->styleHeight('10px')
                            , '20%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schülerbogen')
                        ->styleFontFamily($fontFamily)
                        ->styleTextBold()
                        ->stylePaddingTop()
                        ->styleAlignCenter()
                        ->styleTextSize('23px')
                        ->styleHeight('100px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Name:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleTextSize($textSize)
                            ->stylePaddingBottom($PaddingBottom)
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.Last is not empty) %}
                                    {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '55%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vorname:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleTextSize($textSize)
                            ->stylePaddingBottom($PaddingBottom)
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.First is not empty) %}
                                    {{ Content.Person.Data.Name.First }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '55%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('geboren am:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleTextSize($textSize)
                            ->stylePaddingBottom($PaddingBottom)
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleAlignCenter()
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '13%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('in:&nbsp;')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleAlignRight()
                            ->styleTextSize($textSize)
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthplace is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthplace }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anschrift:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleTextSize($textSize)
                            ->stylePaddingBottom($PaddingBottom)
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.Street.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {% if(Content.Person.Address.Street.Number) %}
                                        {{ Content.Person.Address.Street.Number }}
                                    {% endif %}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->stylePaddingBottom('26px')
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '45%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Mail-Adresse für Elternbriefe:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextBold()
                        ->styleTextSize($textSize)
                    )
                    ->addElement((new Element())
                        ->setContent('
                            {% if(Content.Person.Contact.All.Mail) %}
                                {{ Content.Person.Contact.All.Mail }}
                            {% else %}
                                  &nbsp;
                            {% endif %}
                        ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleBorderBottom('1px', '#000', 'dotted')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Telefonnummer Sorgeberechtigte')
                        ->styleFontFamily($fontFamily)
                        ->styleTextBold()
                        ->styleTextSize($textSize)
                        ->stylePaddingTop($PaddingBottom)
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleTextSize('11px')
                            , '31%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Privat')
                            ->styleTextSize('11px')
                            , '23%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Geschäftlich')
                            ->styleTextSize('11px')
                            , '23%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Mobil')
                            ->styleTextSize('11px')
                            , '23%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Name.LastFirst is not empty) %}
                                    {{ Content.Person.Parent.Father.Name.LastFirst }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '31%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Phone.Private is not empty) %}
                                    {{ Content.Person.Parent.Father.Phone.Private }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Phone.Business is not empty) %}
                                    {{ Content.Person.Parent.Father.Phone.Business }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Phone.Mobil is not empty) %}
                                    {{ Content.Person.Parent.Father.Phone.Mobil }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Name.LastFirst is not empty) %}
                                    {{ Content.Person.Parent.Mother.Name.LastFirst }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '31%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Phone.Private is not empty) %}
                                    {{ Content.Person.Parent.Mother.Phone.Private }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Phone.Business is not empty) %}
                                    {{ Content.Person.Parent.Mother.Phone.Business }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Phone.Mobil is not empty) %}
                                    {{ Content.Person.Parent.Mother.Phone.Mobil }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '23%')
                    )

                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Im Notfall zu benachrichtigen:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextBold()
                        ->styleTextSize($textSize)
                        ->stylePaddingTop($PaddingBottom)
                    )
                    ->addElement((new Element())
                        ->setContent('
                                {% if(Content.Person.Contact.Phone.Radebeul.EmergencyNumber) %}
                                    {{ Content.Person.Contact.Phone.Radebeul.EmergencyNumber }}
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleBorderBottom('1px', '#000', 'dotted')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Besonderheiten / <br/> Wichtiges:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextBold()
                            ->styleTextSize($textSize)
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.MedicalRecord.Disease) %}
                                    {{ Content.Student.MedicalRecord.Disease }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('10px')
                            ->stylePaddingLeft('10px')
                            ->stylePaddingRight('10px')
                            ->styleHeight('135px')
                            ->styleBorderAll('1px', '#000', 'dotted')
                            , '65%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '10%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('75px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Document.PlaceDate) %}
                                    {{ Content.Document.PlaceDate }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            ->styleAlignCenter()
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleBorderBottom('1px', '#000', 'dotted')
                            , '30%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Ort, Datum')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleAlignCenter()
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Unterschrift')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleAlignCenter()
                            , '30%'
                        )
                    )
                )
            )
        );
    }
}