<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 16:04
 */

namespace SPHERE\Application\Api\Document\Custom\Lebenswelt\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class EmergencyDocument extends AbstractDocument
{
    /**
     * @return string
     */
    public function getName()
    {

        return 'Notfallzettel';
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
                            ->setContent('
                                Lebenswelt Schule
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleTextBold()
                            ->styleTextSize('25px')
                            , '70%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Notfallzettel
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleTextBold()
                            ->styleAlignRight()
                            ->styleTextSize('25px')
                            , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Name:
                            ')
                            ->stylePaddingTop('25px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.First is not empty) %}
                                    {{ Content.Person.Data.Name.First }} {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop('25px')
                            ->stylePaddingLeft()
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Geburtsdatum:
                            ')
                            ->stylePaddingTop('25px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                 {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop('25px')
                            ->stylePaddingLeft()
                            , '35%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Adresse:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.Street.Name) %}
                                    {{ Content.Person.Address.Street.Name }}
                                    {{ Content.Person.Address.Street.Number }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Address.City.Name) %}
                                    {{ Content.Person.Address.City.Code }}
                                    {{ Content.Person.Address.City.Name }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderLeft()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Namen der Eltern:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Name.First) %}
                                    {{ Content.Person.Parent.Mother.Name.First }}
                                    {{ Content.Person.Parent.Mother.Name.Last }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Name.First) %}
                                    {{ Content.Person.Parent.Father.Name.First }}
                                    {{ Content.Person.Parent.Father.Name.Last }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderLeft()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                E-Mail:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Contact.Mail) %}
                                    {{ Content.Person.Contact.Mail }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                Erreichbarkeit der Erziehungsberechtigten:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Mother.Contact.Phone) %}
                                    {{ Content.Person.Parent.Mother.Contact.Phone }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Parent.Father.Contact.Phone) %}
                                    {{ Content.Person.Parent.Father.Contact.Phone }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderLeft()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                weitere Notfallnummmern:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                )
                ->addSlice($this->getEmergencySlice())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Allergien, Unverträglichkeiten, Asthma:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.MedicalRecord.Disease) %}
                                    {{ Content.Student.MedicalRecord.Disease }}
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                            ->styleHeight('45px')
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Bei Erkrankung/Verletzung des Kindes
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Student.MedicalRecord.InsuranceState) %}
                                    Das Kind ist {{ Content.Student.MedicalRecord.InsuranceState }}.
                                {% else %}
                                      &nbsp;
                                {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Krankenkasse
                            ')
                            ->stylePaddingTop('35px')
                            ->stylePaddingLeft()
                        , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('
                               {% if(Content.Student.MedicalRecord.Insurance) %}
                                   {{ Content.Student.MedicalRecord.Insurance }}
                               {% else %}
                                   &nbsp;
                               {% endif %}
                            ')
                            ->stylePaddingTop('35px')
                            ->stylePaddingLeft()
                            , '85%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Abholberechtigte:
                            ')
                            ->styleTextSize('18px')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               {% if(Content.Person.AuthorizedPersons) %}
                                   {{ Content.Person.AuthorizedPersons }}
                               {% else %}
                                   &nbsp;
                               {% endif %}
                            ')
                            ->stylePaddingTop()
                            ->stylePaddingLeft()
                            ->styleBorderAll()
                            ->styleHeight('45px')
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Das Kind darf sich nach Absprache mit den LehrerInnen / ErzieherInnen frei im Schulhaus
                               und auf dem Schulgelände aufhalten.
                            ')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                               Das Kind darf allein mit dem Bus nach Hause fahren. ja / nein
                            ')
                            ->stylePaddingTop('5px')
                            ->stylePaddingLeft()
                        )
                    )
                )
            )
        );
    }
}
