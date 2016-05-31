<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Subject
     *
     * @return Stage
     */
    public function frontendCreateSubject($Subject = null)
    {

        $Stage = new Stage('Fächer', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $TableContent = array();
        if ($tblSubjectAll) {
            array_walk($tblSubjectAll, function (TblSubject &$tblSubject) use (&$TableContent) {

                $Temp['Acronym'] = $tblSubject->getAcronym();
                $Temp['Name'] = $tblSubject->getName();
                $Temp['Description'] = $tblSubject->getDescription();
                $Temp['Option'] = (new Standard('', '/Education/Lesson/Subject/Change/Subject', new Pencil(),
                        array('Id' => $tblSubject->getId())))
                    . (new Standard('', '/Education/Lesson/Subject/Destroy/Subject', new Remove(),
                        array('Id' => $tblSubject->getId())));
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Acronym' => 'Kürzel',
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Subject::useService()->createSubject(
                                    $this->formSubject()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Subject
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblSubject $tblSubject
     *
     * @return Form
     */
    public function formSubject(TblSubject $tblSubject = null)
    {

        $acAcronymAll = array();
        $acNameAll = array();
        if (( $tblSubjectAll = Subject::useService()->getSubjectAll() )) {
            array_walk($tblSubjectAll, function (TblSubject $tblSubject) use (&$acAcronymAll, &$acNameAll) {

                if (!in_array($tblSubject->getAcronym(), $acAcronymAll)) {
                    array_push($acAcronymAll, $tblSubject->getAcronym());
                }
                if (!in_array($tblSubject->getName(), $acNameAll)) {
                    array_push($acNameAll, $tblSubject->getName());
                }
            });
        }
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Subject']) && $tblSubject) {
            $Global->POST['Subject']['Acronym'] = $tblSubject->getAcronym();
            $Global->POST['Subject']['Name'] = $tblSubject->getName();
            $Global->POST['Subject']['Description'] = $tblSubject->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fach',
                            array(
                                new AutoCompleter('Subject[Acronym]', 'Kürzel', 'z.B: DE', $acAcronymAll, new Pencil()),
                                new AutoCompleter('Subject[Name]', 'Name', 'z.B: Deutsch', $acNameAll, new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Subject[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $Subject
     *
     * @return Stage
     */
    public function frontendChangeSubject($Id, $Subject)
    {

        $Stage = new Stage('Fach', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject/Create/Subject', new ChevronLeft()));
        $tblSubject = Subject::useService()->getSubjectById($Id);

        if (!$tblSubject) {
            return $Stage . new Danger('Fach nicht gefunden.', new Ban())
            . new Redirect('/Education/Lesson/Subject/Create/Subject', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Id']) && $tblSubject) {
            $Global->POST['Subject']['Acronym'] = $tblSubject->getAcronym();
            $Global->POST['Subject']['Name'] = $tblSubject->getName();
            $Global->POST['Subject']['Description'] = $tblSubject->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Fach', $tblSubject->getAcronym() . ' - ' . $tblSubject->getName(),
                                Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            )
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline(new Edit() . ' Bearbeiten'),
                            new Well(
                                Subject::useService()->changeSubject($this->formSubject($tblSubject)
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Subject, $Id))
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null|array $Category
     *
     * @return Stage
     */
    public function frontendCreateCategory($Category = null)
    {

        $Stage = new Stage('Kategorien', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblCategoryAll = Subject::useService()->getCategoryAll();
        $TableContent = array();
        if ($tblCategoryAll) {
            array_walk($tblCategoryAll, function (TblCategory &$tblCategory) use (&$TableContent) {

                $Temp['Name'] = $tblCategory->getName();
                $Temp['Description'] = $tblCategory->getDescription();
                $Temp['Option'] = new Standard('', '/Education/Lesson/Subject/Change/Category', new Pencil(),
                        array('Id' => $tblCategory->getId()))
                    . ($tblCategory->isLocked() ? ''
                        : new Standard('', '/Education/Lesson/Subject/Destroy/Category', new Remove(),
                            array('Id' => $tblCategory->getId())));
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        )
                    ), new Title(new ListingTable() . ' Übersicht')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Subject::useService()->createCategory(
                                    $this->formCategory()
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Category
                                )
                            )
                        )
                    ), new Title(new PlusSign() . ' Hinzufügen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblCategory $tblCategory
     *
     * @return Form
     */
    public function formCategory(TblCategory $tblCategory = null)
    {

        $tblCategoryAll = Subject::useService()->getCategoryAll();
        $acAcronymAll = array();
        $acNameAll = array();
        array_walk($tblCategoryAll, function (TblCategory $tblCategory) use (&$acAcronymAll, &$acNameAll) {

            if (!in_array($tblCategory->getName(), $acNameAll)) {
                array_push($acNameAll, $tblCategory->getName());
            }
        });

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Category']) && $tblCategory) {
            $Global->POST['Category']['Name'] = $tblCategory->getName();
            $Global->POST['Category']['Description'] = $tblCategory->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kategorie',
                            array(
                                new AutoCompleter('Category[Name]', 'Name',
                                    'z.B: Soziales und gesellschaftliches Handeln', $acNameAll, new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Category[Description]', 'zb: enthält nur Vertiefungskurse', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $Category
     *
     * @return Stage
     */
    public function frontendChangeCategory($Id, $Category)
    {

        $Stage = new Stage('Kategorie', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject/Create/Category', new ChevronLeft()));
        $tblCategory = Subject::useService()->getCategoryById($Id);
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Id']) && $tblCategory) {
            $Global->POST['Subject']['Name'] = $tblCategory->getName();
            $Global->POST['Subject']['Description'] = $tblCategory->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Kategorie',
                                $tblCategory->getName() . ' ' . new Muted(new Small($tblCategory->getDescription())),
                                Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            )
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline(new Edit() . ' Bearbeiten'),
                            new Well(
                                Subject::useService()->changeCategory($this->formCategory($tblCategory)
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $Category, $Id))
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param null|array $Category
     *
     * @return Stage
     */
    public function frontendLinkCategory($Id, $Category = null)
    {

        $Stage = new Stage('Kategorien', 'Verknüpfung');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblGroup = Subject::useService()->getGroupById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Gruppe',
                                $tblGroup->getName(),
                                Panel::PANEL_TYPE_INFO)
                        )
                    ),
                )),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Subject::useService()->changeGroupCategory(
                                    $this->formLinkCategory($tblGroup)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblGroup, $Category
                                )
                            )
                        )
                    ), new Title(new Check() . ' Zuordnen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblGroup $tblGroup
     *
     * @return Form
     */
    public function formLinkCategory(TblGroup $tblGroup = null)
    {

        $tblCategoryAllSelected = $tblGroup->getTblCategoryAll();
        if ($tblCategoryAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblCategoryAllSelected, function (TblCategory &$tblCategory) use (&$Global) {

                $Global->POST['Category'][$tblCategory->getId()] = $tblCategory->getId();
            });
            $Global->savePost();
        }

        $tblCategoryAllAvailable = Subject::useService()->getCategoryAll();
        array_walk($tblCategoryAllAvailable, function (TblCategory &$tblCategory) {

            $tblSubjectAll = $tblCategory->getTblSubjectAll();
            if ($tblSubjectAll) {
                array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {

                    $tblSubject = $tblSubject->getAcronym();
                });
                $tblSubjectAll = '(' . implode(', ', $tblSubjectAll) . ')';
            } else {
                $tblSubjectAll = '';
            }

            $tblCategory = new CheckBox(
                'Category[' . $tblCategory->getId() . ']',
                $tblCategory->getName() . ' ' . new Muted($tblCategory->getDescription() . ' ' . new Small($tblSubjectAll)),
                $tblCategory->getId()
            );
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kategorien', $tblCategoryAllAvailable, Panel::PANEL_TYPE_INFO)
                    ),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param null|array $Subject
     *
     * @return Stage
     */
    public function frontendLinkSubject($Id, $Subject = null)
    {

        $Stage = new Stage('Fächer', 'Verknüpfung');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblCategory = Subject::useService()->getCategoryById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Kategorie',
                                $tblCategory->getName(),
                                Panel::PANEL_TYPE_INFO)
                        )
                    ),
                )),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Subject::useService()->changeCategorySubject(
                                    $this->formLinkSubject($tblCategory)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                    , $tblCategory, $Subject
                                )
                            )
                        )
                    ), new Title(new Check() . ' Zuordnen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblCategory $tblCategory
     *
     * @return Form
     */
    public function formLinkSubject(TblCategory $tblCategory = null)
    {

        $tblSubjectAllSelected = $tblCategory->getTblSubjectAll();
        if ($tblSubjectAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblSubjectAllSelected, function (TblSubject &$tblSubject) use (&$Global) {

                $Global->POST['Subject'][$tblSubject->getId()] = $tblSubject->getId();
            });
            $Global->savePost();
        }

        $tblSubjectAllAvailable = Subject::useService()->getSubjectAll();
        $tblSubjectAllAvailable = $this->getSorter($tblSubjectAllAvailable)->sortObjectBy('Name');
        array_walk($tblSubjectAllAvailable, function (TblSubject &$tblSubject) {

            $tblSubject = new CheckBox(
                'Subject[' . $tblSubject->getId() . ']',
                new Bold($tblSubject->getAcronym()) . ' ' . $tblSubject->getName() . ' ' . new Muted($tblSubject->getDescription()),
                $tblSubject->getId()
            );
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fächer', $tblSubjectAllAvailable, Panel::PANEL_TYPE_INFO)
                    ),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroySubject(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Fach', 'Löschen');

        $tblSubject = Subject::useService()->getSubjectById($Id);
        if ($tblSubject) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Lesson/Subject/Create/Subject', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                                new Panel(
                                    'Fach',
                                    $tblSubject->getAcronym() . ' ' . $tblSubject->getName()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblSubject->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Dieses Fach wirklich löschen?',
                                    array(
                                        $tblSubject->getAcronym(),
                                        $tblSubject->getName(),
                                        $tblSubject->getDescription() ? $tblSubject->getDescription() : null
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Lesson/Subject/Destroy/Subject', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Lesson/Subject/Create/Subject', new Disable())
                                )
                            )
                        )
                    )))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            Subject::useService()->destroySubject($tblSubject)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Fach nicht gefunden.', new Ban())
            . new Redirect('/Education/Lesson/Subject/Create/Subject', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyCategory(
        $Id = null,
        $Confirm = false
    ) {

        $Stage = new Stage('Kategorie', 'Löschen');

        $tblCategory = Subject::useService()->getCategoryById($Id);
        if ($tblCategory) {
            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Lesson/Subject/Create/Category', new ChevronLeft())
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                                new Panel(
                                    'Kategorie',
                                    $tblCategory->getName()
                                    . '&nbsp;&nbsp;' . new Muted(new Small(new Small(
                                        $tblCategory->getDescription()))),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Dieses Kategorie wirklich löschen?',
                                    array(
                                        $tblCategory->getName(),
                                        $tblCategory->getDescription() ? $tblCategory->getDescription() : null
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Lesson/Subject/Destroy/Category', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true)
                                    )
                                    . new Standard(
                                        'Nein', '/Education/Lesson/Subject/Create/Category', new Disable())
                                )
                            )
                        )
                    )))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            Subject::useService()->destroyCategory($tblCategory)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Kategorie nicht gefunden.', new Ban())
            . new Redirect('/Education/Lesson/Subject/Create/Category', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }
}
