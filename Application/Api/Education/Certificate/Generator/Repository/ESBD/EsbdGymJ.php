<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdGymJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository\ESBD
 */
class EsbdGymJ extends EsbdStyle
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = $this->getPageOne($personId);
        $pageList[] = $this->getPageTwo($personId);

        return $pageList;
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageOne($personId)
    {
        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Gymnasium'))
            ->addSlice($this->getCertificateHeadConsumer('Jahreszeugnis des Gymnasiums', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSlice($this->getGradeLanes($personId, '14px', false, '0px'))
            ->addSlice($this->getGradeInfo())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung: {% if(Content.P' . $personId . '.Input.Rating is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Rating|nl2br }}
                                {% else %}
                                    ---
                                {% endif %}')
                        ->styleHeight('20px')
                    )
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))
                ->styleHeight('270px')
            )
            ->addSlice($this->getProfileStandardNew($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Teilnahme an zusätzlichen schulischen Veranstaltungen²:
                            {% if(Content.P' . $personId . '.Input.TeamExtra is not empty) %}
                                {{ Content.P' . $personId . '.Input.TeamExtra|nl2br }}
                            {% else %}
                                ---
                            {% endif %}')
                        ->styleHeight('25px')
                    )
                )
                ->styleMarginTop('10px')
            )
            ->addSlice($this->getDescriptionConsumer($personId, '15px', '10px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getTransferConsumer($personId, '10px'))
            ->addSlice($this->getDateLineConsumer($personId, '10px'))
            ->addSlice($this->getSignPartConsumer($personId))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('10px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ &nbsp;&nbsp;&nbsp; Die Bezeichnung des besuchten schulspezifischen Profils ist anzugeben. Beim Erlernen einer 
                    dritten Fremdsprache ist anstelle des Profils die Fremdsprache anzugeben.',
                '² &nbsp;&nbsp;&nbsp; gemäß § 30 Absatz 11 der Schulordnung Gymnasien Abiturprüfung'
            ))
            ->addSlice($this->getBottomLineConsumer('7px'));
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageTwo($personId)
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Gymnasium'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('DIALOGUS')
                    ->styleTextSize('28pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
//            ->addSlice($this->getCertificateHead('Halbjahreszeugnis der Oberschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '10px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSliceArray($this->getSecondPageDescription($personId))
            ->addSlice($this->getBottomLineConsumer('42px'));
    }
}