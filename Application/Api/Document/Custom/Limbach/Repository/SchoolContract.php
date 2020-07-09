<?php
namespace SPHERE\Application\Api\Document\Custom\Limbach\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;

/**
 * Class SchoolContract
 *
 * @package SPHERE\Application\Api\Document\Custom\Zwickau\Repository
 */
class SchoolContract extends AbstractDocument
{

    const TEXT_SIZE = '11pt';

    /**
     * AccidentReport constructor.
     *
     * @param array $Data
     */
    function __construct($Data)
    {

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();

    /**
     * @param $DataPost
     *
     * @return $this
     */
    private function setFieldValue($DataPost)
    {

        //getPerson
        $this->FieldValue['PersonId'] = $PersonId = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        $this->FieldValue['Gender'] = false;
        if ($PersonId) {
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                //get Gender
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                        if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                            $this->FieldValue['Gender'] = $tblCommonGender->getName();
                        }
                    }
                }
            }
        }

        $this->FieldValue['SchoolType'] = 'das Gymnasium/die Oberschule';
        if(isset($DataPost['SchoolTypeId'])){
            $tblSchoolType = Type::useService()->getTypeById($DataPost['SchoolTypeId']);
            if($tblSchoolType && $tblSchoolType->getName() == TblType::IDENT_OBER_SCHULE){
                $this->FieldValue['SchoolType'] = 'die Oberschule';
            } elseif($tblSchoolType && $tblSchoolType->getName() == TblType::IDENT_GYMNASIUM){
                $this->FieldValue['SchoolType'] = 'das Gymnasium';
            }
        }

        // student
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');

        // division prepare
        $this->FieldValue['ReservationDivision'] = (isset($DataPost['ReservationDivision']) && $DataPost['ReservationDivision'] != '' ? $DataPost['ReservationDivision'] : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $this->FieldValue['ReservationDate'] = (isset($DataPost['ReservationDate']) && $DataPost['ReservationDate'] != '' ? $DataPost['ReservationDate']
            : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

        // custody
        $this->FieldValue['FirstNameCustody1'] = (isset($DataPost['FirstNameCustody1']) && $DataPost['FirstNameCustody1'] != '' ? $DataPost['FirstNameCustody1'] : '&nbsp;');
        $this->FieldValue['LastNameCustody1'] = (isset($DataPost['LastNameCustody1']) && $DataPost['LastNameCustody1'] != '' ? $DataPost['LastNameCustody1'] : '&nbsp;');
//        $this->FieldValue['AddressDistrict1'] = (isset($DataPost['AddressDistrict1']) && $DataPost['AddressDistrict1'] != '' ? $DataPost['AddressDistrict1'] : '&nbsp;');
        $this->FieldValue['AddressStreet1'] = (isset($DataPost['AddressStreet1']) && $DataPost['AddressStreet1'] != '' ? $DataPost['AddressStreet1'] : '&nbsp;');
        $this->FieldValue['AddressPLZ1'] = (isset($DataPost['AddressPLZ1']) && $DataPost['AddressPLZ1'] != '' ? $DataPost['AddressPLZ1'] : '&nbsp;');
        $this->FieldValue['AddressCity1'] = (isset($DataPost['AddressCity1']) && $DataPost['AddressCity1'] != '' ? $DataPost['AddressCity1'] : '&nbsp;');

        $this->FieldValue['FirstNameCustody2'] = (isset($DataPost['FirstNameCustody2']) && $DataPost['FirstNameCustody2'] != '' ? $DataPost['FirstNameCustody2'] : '&nbsp;');
        $this->FieldValue['LastNameCustody2'] = (isset($DataPost['LastNameCustody2']) && $DataPost['LastNameCustody2'] != '' ? $DataPost['LastNameCustody2'] : '&nbsp;');
//        $this->FieldValue['AddressDistrict2'] = (isset($DataPost['AddressDistrict2']) && $DataPost['AddressDistrict2'] != '' ? $DataPost['AddressDistrict2'] : '&nbsp;');
        $this->FieldValue['AddressStreet2'] = (isset($DataPost['AddressStreet2']) && $DataPost['AddressStreet2'] != '' ? $DataPost['AddressStreet2'] : '&nbsp;');
        $this->FieldValue['AddressPLZ2'] = (isset($DataPost['AddressPLZ2']) && $DataPost['AddressPLZ2'] != '' ? $DataPost['AddressPLZ2'] : '&nbsp;');
        $this->FieldValue['AddressCity2'] = (isset($DataPost['AddressCity2']) && $DataPost['AddressCity2'] != '' ? $DataPost['AddressCity2'] : '&nbsp;');

        // Compare Custody Address
        $this->FieldValue['IsCompare'] = false;
        if($this->FieldValue['AddressStreet1'] == $this->FieldValue['AddressStreet2']
        && $this->FieldValue['AddressPLZ1'] ==  $this->FieldValue['AddressPLZ2']
        && $this->FieldValue['AddressCity1'] ==  $this->FieldValue['AddressCity2']){
            $this->FieldValue['IsCompare'] = true;
        }
        // one Custody
        if($this->FieldValue['LastNameCustody2'] == '&nbsp;'){
            $this->FieldValue['IsCompare'] = true;
        }

        // Custody
        $this->FieldValue['CustodyString'] = '';
        if($this->FieldValue['LastNameCustody1'] == $this->FieldValue['LastNameCustody2'] && $this->FieldValue['LastNameCustody2'] != '&nbsp;'){
            $this->FieldValue['CustodyString'] =
                $this->FieldValue['LastNameCustody1'].' '.$this->FieldValue['FirstNameCustody1'].' und '.
                $this->FieldValue['FirstNameCustody2'];
        } else {
            $this->FieldValue['CustodyString'] =
                $this->FieldValue['LastNameCustody1'].' '.$this->FieldValue['FirstNameCustody1']
                .($this->FieldValue['FirstNameCustody2'] != '&nbsp;'
                    ? ' und '.$this->FieldValue['LastNameCustody2'].' '.$this->FieldValue['FirstNameCustody2']
                    : '');
        }

        // Gender
        $this->FieldValue['Male'] = 'false';
        $this->FieldValue['Female'] = 'false';
        if (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Männlich') {
            $this->FieldValue['Male'] = 'true';
        } elseif (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Weiblich') {
            $this->FieldValue['Female'] = 'true';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schulvertrag';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'6%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleHeight('120px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulvertrag')
                                    ->styleTextSize('24pt')
                                    ->styleTextBold()
                                    ->stylePaddingBottom('25px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Zwischen')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('25px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Evangelischer Schulverein Limbach-Oberfrohna e.V.<br/>
                                                  Vertreten durch den Vorstand<br/>
                                                  Friedrichstraße 10<br/>
                                                  09212 Limbach-Oberfrohna')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingLeft('90px')
                                    ->stylePaddingBottom('25px')
                                    ->styleLineHeight('130%')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('- Nachfolgend Schulträger genannt -')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('25px')
                                    ->stylePaddingLeft('90px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Und')
                                    ->styleTextSize(self::TEXT_SIZE)
                                )
                            )
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'6%'
                        )
                    )
                )
                ->addSlice($this->getCustodyAddress())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'6%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('- Nachfolgend Eltern/Erziehungsberechtigte genannt –')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('15px')
                                    ->stylePaddingLeft('90px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Als gesetzlicher Vertreter 
                                    {% if '.$this->FieldValue['Female'].' == "true" %}
                                        der Schülerin
                                    {% else %}
                                        {% if '.$this->FieldValue['Male'].' == "true" %}
                                            des  Schülers
                                        {% else %}
                                            des Schülers/der Schülerin
                                        {% endif %}
                                    {% endif %}')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('15px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['FirstLastName'])
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingLeft('90px')
                                    ->styleLineHeight('130%')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Geboren am '.$this->FieldValue['Birthday'])
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('50px')
                                    ->stylePaddingLeft('90px')
                                    ->styleLineHeight('130%')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Wird nachfolgender Vertrag für '.$this->FieldValue['SchoolType'].' geschlossen:')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('30px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('§1 Vertragsziel')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleTextBold()
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Die Eltern wünschen in Wahrnehmung ihres Grundrechts nach Artikel 7
                                    Grundgesetz, dass das o.g. Kind in dem Bekenntnis des Schulträgers auf der Grundlage
                                     des apostolischen Glaubensbekenntnisses und der gemeinsamen Basis des Glaubens der
                                      Deutschen Evangelischen Allianz sowie gemäß des pädagogischen Konzeptes des
                                       Schulträgers von diesem unterrichtet und erzogen wird. Diese Bekenntnisdokumente
                                        sind den Eltern bekannt und als Anhänge Teil dieses Vertrages.')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleAlignJustify()
                                    ->styleLineHeight('130%')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Personensorgeberechtigte und Schüler/in verstehen sich, ebenso wie
                                     alle angestellten und ehrenamtlichen Mitarbeiter, ausdrücklich als Teil dieser
                                      Bekenntnisgemeinschaft.')
                                    ->styleLineHeight('130%')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('25px')
                                    ->styleAlignJustify()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Hierzu wird 
                                    {% if '.$this->FieldValue['Female'].' == "true" %}
                                        die Schülerin
                                    {% else %}
                                        {% if '.$this->FieldValue['Male'].' == "true" %}
                                            der Schüler
                                        {% else %}
                                            der Schüler/die Schülerin
                                        {% endif %}
                                    {% endif %}
                                    am Freien Evangelischen Limbacher Schulzentrum ab dem 
                                    <b>'.$this->FieldValue['ReservationDate'].'</b>
                                    in die <b>Klasse 
                                    '.$this->FieldValue['ReservationDivision'].' </b>
                                    aufgenommen.')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleAlignJustify()
                                    ->styleLineHeight('130%')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulvertrag')
                                    ->styleTextSize('9pt')
                                    ->styleMarginTop('67px')
                                    ->styleHeight('0px')
                                    , '20%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Freies Evangelisches Limbacher Schulzentrum')
                                    ->styleTextSize('9pt')
                                    ->styleMarginTop('67px')
                                    ->styleHeight('0px')
                                    ->styleAlignCenter()
                                    , '48%'
                                )
//                                ->addElementColumn((new Element())
//                                    ->setContent('Seite 1 von 10')
//                                    ->styleTextSize('9pt')
//                                    ->styleAlignRight()
//                                    ->styleMarginTop('67px')
//                                    ->styleHeight('0px')
//                                    , '20%'
//                                )
                            )
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'6%'
                        )
                    )
                )
            )
        );
    }

    /**
     * @return Slice
     */
    private function getCustodyAddress()
    {

        if($this->FieldValue['IsCompare']){
            // same address
            $AddressSlice = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Familie<br/>
                            '.$this->FieldValue['CustodyString'].'<br/>
                            '.$this->FieldValue['AddressStreet1'].'<br/>
                            '.$this->FieldValue['AddressPLZ1'].' '.$this->FieldValue['AddressCity1'])
                        ->styleTextSize(self::TEXT_SIZE)
                        ->stylePaddingLeft('90px')
                        ->stylePaddingBottom('25px')
                        ->styleLineHeight('130%')
                    )
                );
        } else {
            // different Address
            $AddressSlice = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Familie<br/>
                        '.$this->FieldValue['LastNameCustody1'].' '.$this->FieldValue['FirstNameCustody1'].'<br/>
                        '.$this->FieldValue['AddressStreet1'].'<br/>
                        '.$this->FieldValue['AddressPLZ1'].' '.$this->FieldValue['AddressCity1'])
                    ->styleTextSize(self::TEXT_SIZE)
                    ->stylePaddingLeft('90px')
                    ->stylePaddingBottom('25px')
                    ->styleLineHeight('130%')
                    , '44%'
                )
                ->addElementColumn((new Element())
                    ->setContent('<br/>
                        '.$this->FieldValue['LastNameCustody2'].' '.$this->FieldValue['FirstNameCustody2'].'<br/>
                        '.$this->FieldValue['AddressStreet2'].'<br/>
                        '.$this->FieldValue['AddressPLZ2'].' '.$this->FieldValue['AddressCity2'])
                    ->styleTextSize(self::TEXT_SIZE)
                    ->stylePaddingBottom('25px')
                    ->styleLineHeight('130%')
                    , '44%'
                )
            );
        }

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'),'6%'
                )
                ->addSliceColumn($AddressSlice)
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'),'6%'
                )
            );

    }
}