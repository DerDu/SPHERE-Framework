<?php
namespace SPHERE\Application\Api\Corporation;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class ContactPerson
 *
 * @package SPHERE\Application\Api\Corporation
 */
class ContactPerson extends Extension implements IApiInterface
{

    private static $Sleep = 0;

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Similar', __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {

        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('ajaxLayoutSimilarPerson');
        $Dispatcher->registerMethod('ajaxFormCreateContactPerson');
        $Dispatcher->registerMethod('ajaxContent');
        $Dispatcher->registerMethod('frontendDingens');
        $Dispatcher->registerMethod('ajaxFormDingens');

        return $Dispatcher->callMethod($MethodName);
    }

    public function ajaxFormDingens()
    {
        sleep(self::$Sleep);
        return (string)(new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TextField('TestA')
                    )
                )
            )))->setConfirm('ajax :)');
    }

    public function frontendDingens()
    {
        sleep(self::$Sleep);
        return Person::useFrontend()->frontendPerson();
    }

    public function ajaxLayoutSimilarPerson( $TblSalutation_Id = 1, $TblPerson_FirstName, $TblPerson_LastName, $Reload = null, $E4 = null )
    {
        sleep(self::$Sleep);
        $Search = new Pile();
        $Search->addPile( Person::useService(), new ViewPerson() );

        $FirstName = explode( ' ', $TblPerson_FirstName );
        $LastName = explode( ' ', $TblPerson_LastName );

        $Result = $Search->searchPile(array(
            array(
//                ViewPerson::TBL_SALUTATION_ID => $TblSalutation_Id ? array($TblSalutation_Id) : array(''),
//                ViewPerson::TBL_PERSON_FIRST_NAME => $FirstName,
//                ViewPerson::TBL_PERSON_LAST_NAME => $LastName,
                ViewPerson::TBL_SALUTATION_ID => array($TblSalutation_Id),
                ViewPerson::TBL_PERSON_FIRST_NAME => array('g'),
                ViewPerson::TBL_PERSON_LAST_NAME => array(''),
            )
        ));

        $Result = array_slice( $Result, 0, 100 );

        $R = new InlineReceiver();

        $Table = array();
        foreach( $Result as $Index => $Row ) {

            $P = new Pipeline();
//            $P->addEmitter( new ClientEmitter($R1 = new ModalReceiver(), new Warning('Click Fertig').$R) );
//            $R1->setIdentifier( $Reload );
            $P->addEmitter( $E = new ServerEmitter($R, new Route(__NAMESPACE__ . '/Similar')) );
            $E->setGetPayload(array( 'MethodName' => 'ajaxFormCreateContactPerson' ));
            $E->setPostPayload( array(
                ViewPerson::TBL_SALUTATION_ID => 2,
                ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
                ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
                'Reload' => $Reload,
                'E4' => $E4
            ));

            $ViewPerson = $Row[0]->__toArray();

            $P->setLoadingMessage('Bitte warten', 'Datensatz (' . $ViewPerson[ViewPerson::TBL_PERSON_FIRST_NAME] . ') wird verbunden..');
            $P->setSuccessMessage('Erfolgreich', 'Datensatz (' . $ViewPerson[ViewPerson::TBL_PERSON_FIRST_NAME] . ') wurde verbunden');

            $ViewPerson['DTOption'] = (new Standard('Ansprechpartner anlegen','#'))->ajaxPipelineOnClick( $P );
            $Table[] = $ViewPerson;
        }

        $P = new Pipeline();
//        $P->addEmitter( new ClientEmitter($R1 = new ModalReceiver(), new Warning('Click Fertig').$R) );
//        $R1->setIdentifier( $Reload );
        $P->addEmitter( $E = new ServerEmitter($R, new Route(__NAMESPACE__ . '/Similar')) );
        $E->setGetPayload(array( 'MethodName' => 'ajaxFormCreateContactPerson' ));
        $E->setPostPayload( array(
            ViewPerson::TBL_SALUTATION_ID => 3,
            ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
            ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
            'Reload' => $Reload,
            'E4' => $E4
        ));
        $P->setLoadingMessage('Bitte warten', 'Neuer Datensatz wird erzeugt..');
        $P->setSuccessMessage('Erfolgreich', 'Neuer Datensatz wurde erzeugt');

        // E4
        $P4 = new Pipeline();
        $P4->addEmitter( new ClientEmitter($R4 = new InlineReceiver(),new Success('Modal Fertig')) );
        $R4->setIdentifier( $E4 );

        return new TableData($Table, null, array(
            ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
            ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
            ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                'DTOption' => 'O',
                'DTSelect' => 'S'
        ),array(
            "columnDefs" => array(
                array( "searchable" => false, "targets" => -1 ),
                array( "type" => "natural", "targets" => '_all' )
            )
            )
            ).


            (new Standard('Ansprechpartner anlegen','#'))->ajaxPipelineOnClick( $P ).$R.$P4;
    }

    public function ajaxFormCreateContactPerson( $TblSalutation_Id, $TblPerson_FirstName, $TblPerson_LastName, $Reload, $E4 )
    {
        sleep(self::$Sleep+1);

        $P = new Pipeline();
        $P->setLoadingMessage('Ansicht wird aktualisiert', 'Daten werde neu geladen...');
        $P->setSuccessMessage('Erfolgreich', 'Daten wurden neu geladen');
        // E4
        $P->addEmitter( new ClientEmitter($R4 = new InlineReceiver(), new Warning('Click Fertig')) );
        $R4->setIdentifier( $E4 );
        $P->addEmitter( $E = new ServerEmitter($R = new ModalReceiver(), new Route(__NAMESPACE__ . '/Similar')) );
        $R->setIdentifier( $Reload );
        $E->setGetPayload(array( 'MethodName' => 'ajaxLayoutSimilarPerson' ));
        $E->setPostPayload( array(
            ViewPerson::TBL_SALUTATION_ID => $TblSalutation_Id,
            ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
            ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
            'Reload' => $Reload,
            'E4' => $E4
        ));

        return new Success('Done '.time()).$P;
    }

    public function ajaxContent()
    {
        sleep(self::$Sleep);

        return new Info( 'AjaxContent' );
    }
}