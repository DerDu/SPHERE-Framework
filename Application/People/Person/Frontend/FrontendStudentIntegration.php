<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.12.2018
 * Time: 10:43
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStudentIntegration
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentIntegration extends FrontendReadOnly
{
    const TITLE = 'Integration-Daten';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getIntegrationTitle($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            $showLink = (new Link(new EyeOpen() . ' Anzeigen', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadIntegrationContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                new Info('Die Integrations-Daten sind ausgeblendet. Bitte klicken Sie auf Anzeigen.'),
                array($showLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag(),
                true
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getIntegrationContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            // Schreibrecht für Integration
            if (Access::useService()->hasAuthorization('/Api/People/Meta/Support/ApiSupport')) {
                $content = self::getEditContent($tblPerson);
            }
            // nur Anzeige
            elseif (Access::useService()->hasAuthorization('/Api/People/Meta/Support/ApiSupportReadOnly')) {
                $content = self::getReadOnlyContent($tblPerson);
            } else {
                $content = '';
            }

            $hideLink = (new Link(new EyeMinus() . ' Ausblenden', ApiPersonReadOnly::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonReadOnly::pipelineLoadIntegrationTitle($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($hideLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Tag()
            );
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getEditContent(TblPerson $tblPerson)
    {
        $SupportContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SupportTable');
        $SpecialContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'SpecialTable');
        $HandyCapContent = ApiSupport::receiverTableBlock(new SuccessMessage('Lädt'), 'HandyCapTable');

        $Accordion = new Accordion('');
        $Accordion->addItem('Förderantrag/Förderbescheid '.ApiSupport::receiverInline('', 'SupportCount'), $SupportContent, true);
        $Accordion->addItem('Entwicklungsbesonderheiten '.ApiSupport::receiverInline('', 'SpecialCount'), $SpecialContent, false);
        $Accordion->addItem('Nachteilsausgleich '.ApiSupport::receiverInline('', 'HandyCapCount'), $HandyCapContent, false);

        $content = ApiSupport::pipelineLoadTable($tblPerson->getId())
            . new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning('Für Lehrer sind nur die aktuellsten Einträge sichtbar')
                            , 6)
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                                ApiSupport::receiverModal(),
                                (new Standard('Förderantrag/Förderbescheid hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSupportModal($tblPerson->getId())),
                                (new Standard('Entwicklungsbesonderheiten hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateSpecialModal($tblPerson->getId())),
                                (new Standard('Nachteilsausgleich hinzufügen', '#'))
                                    ->ajaxPipelineOnClick(ApiSupport::pipelineOpenCreateHandyCapModal($tblPerson->getId())),
                                new Ruler()
                            )
                        )
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            $Accordion,
                        ))
                    )
                ),
            ));

        return $content;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getReadOnlyContent(TblPerson $tblPerson)
    {

        $Accordion = new Accordion('');
        $Accordion->addItem('Förderantrag/Förderbescheid', Student::useFrontend()->getSupportTable($tblPerson, false), true);
        $Accordion->addItem('Entwicklungsbesonderheiten', Student::useFrontend()->getSpecialTable($tblPerson, false), false);
        $Accordion->addItem('Nachteilsausgleich', Student::useFrontend()->getHandyCapTable($tblPerson, false), false);

        $content = new Layout(array(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Warning('Für Lehrer sind nur die aktuellsten Einträge sichtbar')
                        , 6)
                )
            ),
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(array(
                        $Accordion,
                    ))
                )
            ),
        ));

        return $content;
    }
}