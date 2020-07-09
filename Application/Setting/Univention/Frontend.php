<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Univention
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendUnivention()
    {
        $Stage = new Stage('Univention', '');
        $Stage->addButton(new Standard('Zurück', '/Setting', new ChevronLeft()));

        //ToDO Erklärung der Schnittstelle? + Vorraussetzungen

        return $Stage;
    }

    /**
     * @param bool $Upload
     *
     * @return Stage
     */
    public function frontendUnivAPI($Upload = '')
    {

        $Stage = new Stage('Univention', 'Online Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention', new ChevronLeft()));
        // Removed for Live
        $Stage->addButton(new Standard('Accounts komplett abgleichen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'All')));
        $Stage->addButton(new Standard('Accounts hinzufügen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Create')));
        $Stage->addButton(new Standard('Accounts ändern', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Update')));
        $Stage->addButton(new Standard('Accounts löschen', '/Setting/Univention/Api', new Upload(), array('Upload' => 'Delete')));
//        $beginn = microtime(true);
//        $dauer = microtime(true) - $beginn;
//        echo "Verbindung zur API: $dauer Sek.</br>";

// ToDO Arbeiten mit der API
//
//        // dynamsiche Rollenliste
//        $roleList = (new UniventionRole())->getAllRoles();
//        Debugger::screenDump($roleList);
//
//        // dynamsiche Schulliste
//        $schoolList = (new UniventionSchool())->getAllSchools();
////        Debugger::screenDump($schoolList);
//
//        // Benutzerliste suchen
//        $UserList = (new UniventionUser())->getUserListByProperty('name','ref-', true);
//
//        // early break for no answer
//        if(!is_array($roleList) || !is_array($schoolList)){
//            $Stage->setContent(new Warning('Univention liefert keine Informationen'));
//            return $Stage;
//        }
//
//        // Benutzerliste suchen für den Vergleich
//        $UniventionUserList = (new UniventionUser())->getUserListByProperty('name',$Acronym.'-', true);
//
        $UserUniventionList = array();
//        if($UniventionUserList){
//            foreach($UniventionUserList as $User){
//                // dn, url, ucsschool_roles[], name, school, firstname, lastname, birthday, disabled, email, record_uid, roles, schools, school_classes, source_uid, udm_properties
//                $UserUniventionList[$User->record_uid] = array(
//                    'name' => $User->name,
//                    'school' => $User->school,
//                    'firstname' => $User->firstname,
//                    'lastname' => $User->lastname,
//                    'birthday' => $User->birthday,
//                    'email' => $User->email,
//                    'roles' => $User->roles,
//                    'schools' => $User->schools,
//                    'school_classes' => $User->school_classes,
//                    'source_uid' => $User->source_uid,
//                    'udm_properties' => $User->udm_properties,
//                );
//            }
//        }

        $ErrorLog = array();
        $AccountActiveList = array();
        $Acronym = Account::useService()->getMandantAcronym();
        $tblYear = Term::useService()->getYearByNow();
        if($tblYear){
            $tblYear = current($tblYear);
            // Lehraufträge
            $TeacherSchools = array();
            $TeacherClasses = array();
            if(($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))){
                foreach($tblDivisionList as $tblDivision){
                    if(($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))){
                        foreach($tblDivisionSubjectList as $tblDivisionSubject){
                            if(($tblDivisionTeacherList = Division::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject))){
                                foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                                    if(($tblPersonTeacher = $tblDivisionTeacher->getServiceTblPerson())){
                                        $SchoolString = '';
                                        // wichtig für Schulgetrennte Klassen (nicht Mandantenweise)
                                        if(($tblCompany = $tblDivision->getServiceTblCompany())
                                            && Consumer::useService()->isSchoolSeparated()){
                                            if(($tblSchoolType = $tblDivision->getType())){
                                                $tblSchoolTypeString = Type::useService()->getSchoolTypeString($tblSchoolType);
                                                $SchoolString = $Acronym.$tblSchoolTypeString.$tblCompany->getId();
                                                $TeacherSchools[$tblPersonTeacher->getId()][$tblCompany->getId().'_'.$tblSchoolTypeString] = $SchoolString;
                                                $SchoolString .= '-';
                                            }
                                        }
                                        $TeacherClasses[$tblPersonTeacher->getId()][$tblDivision->getId()] = $SchoolString.$tblDivision->getTblLevel()->getName().$tblDivision->getName();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $AccountActiveList = Univention::useService()->getAccountActive($tblYear, $Acronym, $TeacherSchools, $TeacherClasses);
        } else {
            $ErrorLog[] = 'kein aktuelles Jahr gefunden';
        }

        // Vergleich
        // create: AccountActive welche nicht in der API vorhanden sind
        $ApiList['Create'] = array();
        $count['create'] = 0;
        $count['noCreate'] = 0;
        $countNoCreate = 0;
        // update: Accounts welche Vorhanden sind, aber unterschiedliche Werte aufweisen
        $ApiList['Update'] = array();
        $count['update'] = 0;
        $count['noUpdate'] = 0;
        // delete: Accounts, die in der API vorhaden sind, aber nicht in der Schulsoftware
        $ApiList['Delete'] = array();
        $count['delete'] = 0;
        foreach($AccountActiveList as $AccountActive){
            if(isset($UserUniventionList[$AccountActive['source_uid']])
            // Lokal Test
                || $AccountActive['name'] == 'REF-ArAn06'
            ){
                if(($Error = $this->controlAccount($AccountActive))){
                    $ErrorLog[] = $Error;
                    $count['noUpdate']++;
                } else {
                    $count['update']++;
//                    $ApiList['update'][] = $AccountActive;
                    // Lokaler Test
                    $ApiList['Update'][] = $AccountActive['name'];
                }
                unset($UserUniventionList[$AccountActive['source_uid']]);
            } else {
                if(($Error = $this->controlAccount($AccountActive))){
                    $ErrorLog[] = $Error;
                    $count['noCreate']++;
                } else {
                    $count['create']++;
//                    $ApiList['create'][] = $AccountActive;
                    $ApiList['Create'][] = $AccountActive['name'];
                }
                $ApiCreateList[] = $AccountActive;

                unset($UserUniventionList[$AccountActive['source_uid']]);
            }
        }
        $ErrorLog = array_filter($ErrorLog);
        $ApiList['Delete'] = $UserUniventionList;
        $count['delete'] = count($UserUniventionList);

        echo 'Api Kommunikation';
//        Debugger::screenDump($ApiList);
//        echo'Zählung:';
//        Debugger::screenDump($count);
//        echo'ErrorLog:';
//        Debugger::screenDump($ErrorLog);

        // Upload erst nach ausführlicher bestätigung
        if($Upload == 'All' || $Upload == 'Create' || $Upload == 'Update' || $Upload == 'Delete'){
            foreach($ApiList as $Type => $AccountList){
                if($Type == 'Create' && !empty($AccountList) && ($Upload == 'All' || $Upload == 'Create')){
                    Debugger::screenDump('create:');
                    Debugger::screenDump($AccountList);
                    foreach($AccountList as $Account){
                        // create with API
//                        (new UniventionUser())->createUser($Account['name'], $Account['email'], $Account['firstname'], $Account['lastname'], $Account['record_uid'], $Account['roles'],
//                            $Account['schools'], $Account['source_uid']);
                    }
                }
                if($Type == 'Update' && !empty($AccountList) && ($Upload == 'All' || $Upload == 'Update')){
                    Debugger::screenDump('update:');
                    Debugger::screenDump($AccountList);
                    foreach($AccountList as $Account){
                        // update with API
//                        (new UniventionUser())->updateUser($Account['name'], $Account['email'], $Account['firstname'], $Account['lastname'], $Account['record_uid'], $Account['roles'],
//                            $Account['schools'], $Account['source_uid']);
                    }
                }
                //ToDo Nutzer in Univention löschen, wenn sie in der Schulsoftware gelöscht werden?
                //ToDO Löschen funktioniert nicht mit Umlauten, Fehlersuche wenn API wieder verfügbar
                if($Type == 'Delete' && !empty($AccountList) && ($Upload == 'All' || $Upload == 'Delete')){
                    Debugger::screenDump('delete:');
                    Debugger::screenDump($AccountList);
                    foreach($AccountList as $Account){
                        // delete with API
//                        $ErrorLog[] = (new UniventionUser())->deleteUser($Account['name']);
                    }
                }
            }
        }

        $Stage->setContent('ToDO Ausgabe');

        return $Stage;
    }

    /**
     * @param array $Account
     * correct Account values return false
     * incorrect Accounts return ErrorLog
     *
     * @return array|bool
     */
    public function controlAccount($Account = array())
    {

        $ErrorLog = array();
        // Handle Error Entity
        if($Account['name'] == ''
            || $Account['firstname'] == ''
            || $Account['lastname'] == ''
            || $Account['record_uid'] == ''
            || $Account['source_uid'] == ''
            || $Account['school_classes'] == ''
            || empty($Account['roles'])
            || empty($Account['schools'])) {

            foreach($Account as $Key => $Value){
                if(is_array($Value)){

                    $MouseOver = '';
                    switch ($Key){
                        case 'roles':
                            $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
                                'Mögliche Fehler:</br>'
                                .'Schüler '.new DangerText('keine aktive Klasse').'</br>'
                                .'Person in keiner der Folgenen Personengruppen:</br>'
                                .new DangerText('Schüler / Mitarbeiter / Lehrer')
                            )))->enableHtml();
                            break;
                        case 'schools':
                            $MouseOver = (new ToolTip(new Info(), htmlspecialchars(
                                'Schüler ist keiner Klasse zugewiesen </br>'
                                .'oder Schule fehlt in Univention')))->enableHtml();
                            break;
                    }

                    if(empty($Value)){
                        $ErrorLog[] = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                    }else {
                        $ErrorLog[] = $Key.' Ok';
                    }

                }else {
                    if($Value === ''){
                        $MouseOver = '';
                        switch ($Key){
                            case 'lastname':
                                $MouseOver = new ToolTip(new Info(), 'keine Person am Account');
                                break;
                            case 'school_classes':
                                $MouseOver = new ToolTip(new Info(), 'Person muss mindestens einer Klasse zugewiesen sein');
                                break;
                        }
                        $ErrorLog[] = $Key.' '.new DangerText('nicht vorhanden! ').$MouseOver;
                    }
                }
            }
        }
        return (!empty($ErrorLog) ? $ErrorLog : false);
    }

//        // Benutzer anlegen
//        $ErrorLog[] = (new UniventionUser())->createUser('MaxMustermann', 'Kukane', 'Klimpel', '7', array($roleList['student']),
//            array($schoolList['DEMOSCHOOL'], $schoolList['DEMOSCHOOL2']), $Acronym.'-7');
//        $ErrorLog[] = (new UniventionUser())->createUser('MustermannMax', 'Valentina', 'Allgaier', '8', array($roleList['staff'],$roleList['teacher']),
//            array($schoolList['DEMOSCHOOL']), $Acronym.'-8');
//
//        // Benutzerliste suchen
//        $UserList = (new UniventionUser())->getUserListByProperty('name','ref-', true);
//        Debugger::screenDump($UserList);
//        // Benutzer entfernen
//        if($UserList){
//            foreach($UserList as $ResourceUser){
//                Debugger::screenDump(utf8_encode($ResourceUser->name));
//                Debugger::screenDump($ResourceUser->name);
//                $ErrorLog[] = (new UniventionUser())->deleteUser($ResourceUser->name);
//            }
//        }
//
//        $ErrorLog[] = (new UniventionUser())->deleteUser('DEMO-login');

    /**
     * @return Stage
     */
    public function frontendUnivCSV()
    {
        $Stage = new Stage('Univention', 'Online Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting/Univention', new ChevronLeft()));
        $Stage->addButton(new Standard('CSV Schulen herunterladen', '/Api/Reporting/Univention/SchoolList/Download', new Download(), array(), 'Schulen aus den Mandanten Einstellungen'));
        $Stage->addButton(new Standard('CSV User herunterladen', '/Api/Reporting/Univention/User/Download', new Download(), array(), 'Es werden auch Schüler ohne Account hinzugefügt.'));

        $ErrorLog = array();
        if(($AccountPrepareList = Univention::useService()->getExportAccount(true))){

            $isCoreGroupUsage = false;
            // kontrolle Stammgruppennutzung
            if(Group::useService()->getGroupListByIsCoreGroup()){
                $isCoreGroupUsage = true;
            }

            foreach($AccountPrepareList as $Data){
                $IsError = false;
//                $Data['name'];
//                $Data['firstname'];       // Account ohne Person wird bereits davor ausgefiltert
//                $Data['lastname'];
//                $Data['record_uid'];      // Accountabhängig
//                $Data['source_uid'];      // Accountabhängig
//                $Data['roles'];           // benutzer ohne Rollen werden bereits entfernt. nachträglich werden Schüler herrangezogen, die besitzen immer Student
//                $Data['schools'];
//                $Data['password'];        // noch keine Prüfung
//                $Data['school_classes'];
//                $Data['groupArray'];

                if(!$Data['name']){
                    $Data['name'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().' Person als '.
                        new Bold('Schüler').' besitzt keinen Account')))->enableHtml().
                        new DangerText('Account fehlt ');
                    $IsError = true;
                }
                if(!$Data['schools']){
                    $Data['schools'] = (new ToolTip(new Exclamation(),
                        htmlspecialchars(new Minus().' Lehrer erhält alle Schulen aus Mandanteneinstellungen<br/>'
                            .new Minus().' Schüler benötigt aktuelle Klasse<br/>'
                            .new Minus().' Schüler benötigt aktuelle Schule in S-Akte'
                        )))->enableHtml().
                        new DangerText(' Keine Schule hinterlegt');
                    $IsError = true;
                } else {
                    $Data['schools'] = new SuccessText(new SuccessIcon().' gefunden');
                }
                if(!$Data['school_classes'] && preg_match("/student/",$Data['roles'])){
                    $Data['school_classes'] = (new ToolTip(new Exclamation(), htmlspecialchars(new Minus().
                            ' Schüler benötigt eine aktuelle Klasse')))->enableHtml().
                            new DangerText(' Keine Klasse');
                    $IsError = true;
                } else {
                    $Data['school_classes'] = new SuccessText(new SuccessIcon().' gefunden');
                }

                // Stammgruppe nur für Schüler
                if($isCoreGroupUsage && empty($Data['groupArray']) && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = new DangerText('Keine Stammgruppe');
                    $IsError = true;
                } elseif($isCoreGroupUsage && count($Data['groupArray']) > 1 && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = new DangerText('mehr als eine Stammgruppe: '.implode(', ',$Data['group']));
                    $IsError = true;
                } elseif($isCoreGroupUsage && preg_match("/student/",$Data['roles'])){
                    $Data['group'] = $Data['school_classes'] = new SuccessText(new SuccessIcon().' '.$Data['groupArray'][0]);
                }

                if($IsError){
                    $ErrorLog[] = $Data;
                }
            }
        }

        $Columnlist = array();
        if(!empty($ErrorLog)){
            foreach ($ErrorLog as $Notification){
                $PanelContent = array();
                $PanelContent[] = 'Person: '.$Notification['firstname'].' '. $Notification['lastname'];
                $PanelContent[] = 'Schule: '.$Notification['schools'];
                $PanelContent[] = 'Klasse: '.$Notification['school_classes'];
                if(isset($Notification['group'])){
                    $PanelContent[] = 'Stammgruppe: '.$Notification['group'];
                }

                $Columnlist[] = new LayoutColumn(
                    new Panel($Notification['name'], $PanelContent)
                , 2);
            }
        }

        $Stage->setcontent(new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn(
                    new Title(count($Columnlist).' Warnungen')
                )
            ),
            new LayoutRow(
                $Columnlist
            )
        ))));

        return $Stage;
    }
}