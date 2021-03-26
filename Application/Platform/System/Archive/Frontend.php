<?php
namespace SPHERE\Application\Platform\System\Archive;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Platform\System\Archive
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendArchive()
    {

        $Stage = new Stage('Archivierung');

//        $Archive = false;
//
//        if ($Environment) {
//            if ($Environment['Consumer']) {
//                $tblConsumer = Consumer::useService()->getConsumerById($Environment['Consumer']);
//                $Archive = Archive::useService()->getArchiveAllByConsumer($tblConsumer);
//            } else {
//                $Archive = Archive::useService()->getArchiveAll();
//            }
//        }
//
//        if ($Archive) {
//            array_walk($Archive, function (TblArchive &$Element) {
//
//                $Entry = array(
//                    new Layout(new LayoutGroup(new LayoutRow(array(
//                        new LayoutColumn(new Muted('Eintrag:'), 3),
//                        new LayoutColumn($Element->getId(), 9)
//                    )))),
//                    new Layout(new LayoutGroup(new LayoutRow(array(
//                        new LayoutColumn(new Muted('Mandant:'), 3),
//                        new LayoutColumn($Element->getConsumerAcronym().' '.$Element->getConsumerName(), 9)
//                    )))),
//                    new Layout(new LayoutGroup(new LayoutRow(array(
//                        new LayoutColumn(new Muted('Datenbank:'), 3),
//                        new LayoutColumn($Element->getArchiveDatabase(), 9)
//                    )))),
//                    new Layout(new LayoutGroup(new LayoutRow(array(
//                        new LayoutColumn(new Muted('Zeitpunkt:'), 3),
//                        new LayoutColumn(date('d.m.Y H:i:s ', $Element->getArchiveTimestamp()), 9)
//                    )))),
//                );
//                /** @var TblArchive $Element */
//                $Element = Archive::useService()->fixArchive($Element);
//                switch ($Element->getArchiveType()) {
//                    case TblArchive::ARCHIVE_TYPE_CREATE:
//                        $Entry[] = new Layout(new LayoutGroup(new LayoutRow(array(
//                            new LayoutColumn(new Muted('Typ:'), 3),
//                            new LayoutColumn('Create', 9)
//                        ))));
//                        break;
//                    case TblArchive::ARCHIVE_TYPE_UPDATE:
//                        $Entry[] = new Layout(new LayoutGroup(new LayoutRow(array(
//                            new LayoutColumn(new Muted('Typ:'), 3),
//                            new LayoutColumn('Update', 9)
//                        ))));
//                        break;
//                    default:
//                        $Entry[] = new Layout(new LayoutGroup(new LayoutRow(array(
//                            new LayoutColumn(new Muted('Typ:'), 3),
//                            new LayoutColumn('-NA-', 9)
//                        ))));
//                        break;
//                }
//                $Element->Entry = new Listing($Entry);
//                $Element->Payload = $this->prepareOutput(unserialize($Element->Entity));
//            });
//        } else {
//            $Archive = array();
//        }
//
//        $Stage->setContent(
//            '<style>.list-group{ margin-bottom: 0; } .list-group .list-group-item{ padding: 2px 3px }</style>'
//            .new Layout(array(
//                new LayoutGroup(array(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            $this->formArchive()
//                                ->appendFormButton(new Primary('Filtern', new Search()))
//                        )
//                    ),
//                ), new Title('Archiv', 'Filter')),
//                new LayoutGroup(array(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            '
//                            <div class="form-group">
//                                <label>Volltextsuche</label>
//                                <div class="input-group">
//                                    <span class="input-group-addon">'.new Search().'</span>
//                                    <textarea name="GlobalSearch" class="form-control" rows="2"></textarea>
//                                </div>
//                            </div>
//                            '
//                            .'<script>
//                                Client.Use("ModTable", function()
//                                {
//                                    var Search = jQuery(\'input[type="search"]\');
//                                    Search.parent().hide();
//                                    var GlobalSearch = jQuery(\'textarea[name="GlobalSearch"]\').on("keyup",function(){
//                                        Search.val( jQuery(this).val() ).trigger("keyup");
//                                    });
//                                });
//                            </script>'
//                        )
//                    ),
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new TableData($Archive, null, array(
//                                'Entry'   => 'Eintrag',
//                                'Payload' => 'Daten'
//                            ), array(
//                                "autoWidth" => true,
//                                "order"     => array(array(0, 'desc')),
//                                "stateSave" => false
//                            ))
//                        )
//                    ),
//                ), new Title('Filter', 'Ergebnis'))
//            ))
//        );
        return $Stage;
    }

//    /**
//     * @param $Data
//     *
//     * @return Listing
//     */
//    private function prepareOutput($Data)
//    {
//
//        foreach ((array)$Data as $Key => $Value) {
//            if (0 === strpos($Key, 'Entity')) {
//                $Data['*'.$Key] = $Value;
//                unset( $Data[$Key] );
//                $Key = '*'.$Key;
//            }
//
//            if (is_bool($Value)) {
//                if ($Value) {
//                    $Value = new Success(new Italic('true'));
//                } else {
//                    $Value = new Danger(new Italic('false'));
//                }
//            }
//            if ($Key == 'Id') {
//                unset( $Data[$Key] );
//                continue;
//            }
//            if ($Key == '*EntityCreate' || $Key == '*EntityUpdate') {
//                if ($Value) {
//                    $Value = new Info(new Italic(date('d.m.Y H:i:s', $Value)));
//                } else {
//                    $Value = new Muted(new Italic('-NA-'));
//                }
//            }
//
//            if ($Key == '*EntityName') {
//                $Value = new Info(new Italic($Value));
//            }
//            if ($Value == '-NA-') {
//                $Value = new Muted(new Italic($Value));
//            }
//            if (is_array($Value)) {
//                $Data[$Key] = $this->prepareOutput($Value);
//            } else {
//                $Data[$Key] =
//                    new Layout(
//                        new LayoutGroup(
//                            new LayoutRow(array(
//                                new LayoutColumn(
//                                    new Small(new Muted($Key)).':'
//                                    , 2),
//                                new LayoutColumn(
//                                    new Small($Value)
//                                    , 10)
//                            ))
//                        )
//                    );
//            }
//        }
//        uksort($Data, function ($KeyA, $KeyB) {
//
//            return strnatcmp($KeyA, $KeyB);
//        });
//
//        return new Listing($Data);
//    }

    /**
     * @return Form
     */
    public function formArchive()
    {

        $tblConsumerAll = Consumer::useService()->getConsumerAll();
        array_push($tblConsumerAll, new TblConsumer(''));

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Umgebung', array(
                            new SelectBox('Environment[Consumer]', 'Mandant', array(
                                '{{ Acronym }}: {{ Name }}' => $tblConsumerAll
                            ), new Building())
                        ))
                    ),
                )),
            ))
        );
    }
}
