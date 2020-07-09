<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GymJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GymJ extends Certificate
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

        $Header = $this->getHead($this->isSample());

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis des Gymnasiums'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getGradeLanesSmall($personId, '14px', false, '5px'))
            ->addSlice($this->getRatingContent($personId, '35px', '5px'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('5px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall($personId, true, array('Lane' => 1, 'Rank' => 3))
                ->styleHeight('220px')
            )
            ->addSlice($this->getProfileStandardNew($personId, '14px', false, true))
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
                ->styleMarginTop('5px')
            )
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '35px', '5px'))
            ->addSlice($this->getTransfer($personId, '2px'))
            ->addSlice($this->getDateLine($personId, '10px'))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice($this->getParentSign('33px'))
            ->addSlice($this->getInfo('2px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ &nbsp;&nbsp;&nbsp; Die Bezeichnung des besuchten schulspezifischen Profils ist anzugeben. Beim Erlernen einer 
                    dritten Fremdsprache ist anstelle des Profils die Fremdsprache anzugeben.',
                '² &nbsp;&nbsp;&nbsp; gemäß § 30 Absatz 11 der Schulordnung Gymnasien Abiturprüfung'
            ));
    }
}