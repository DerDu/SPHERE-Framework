<?php
namespace SPHERE\Application\Api\Corporation;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ApiEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class ContactPerson
 *
 * @package SPHERE\Application\Api\Corporation
 */
class ContactPerson implements IApiInterface
{

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

        return $Dispatcher->callMethod($MethodName);
    }


    public function ajaxLayoutSimilarPerson( $TblSalutation_Id, $TblPerson_FirstName, $TblPerson_LastName )
    {
        $Search = new Pile();
        $Search->addPile( Person::useService(), new ViewPerson() );

        $FirstName = explode( ' ', $TblPerson_FirstName );
        $LastName = explode( ' ', $TblPerson_LastName );

        $Result = $Search->searchPile(array(
            array(
                ViewPerson::TBL_SALUTATION_ID => $TblSalutation_Id ? array($TblSalutation_Id) : array(''),
                ViewPerson::TBL_PERSON_FIRST_NAME => $FirstName,
                ViewPerson::TBL_PERSON_LAST_NAME => $LastName,
            )
        ));

        $Result = array_slice( $Result, 0, 10 );

        $Table = array();
        foreach( $Result as $Row ) {
            $Table[] = $Row[0];
        }

//        ob_start();
//        Debugger::screenDump( $Result );
//        return ob_get_clean();

        $P = new Pipeline();
        $P->addEmitter( $E = new ApiEmitter( new Route(__NAMESPACE__.'/Similar'), $R = new InlineReceiver() ) );
        $E->setGetPayload(array( 'MethodName' => 'ajaxFormCreateContactPerson' ));
        $E->setPostPayload( array(
            ViewPerson::TBL_SALUTATION_SALUTATION => $TblSalutation_Id,
            ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
            ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName
        ));

        return new TableData($Table, null, array(
            ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
            ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
            ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname'
        ), false)


            .(new Standard('Ansprechpartner anlegen','#'))->ajaxPipelineOnClick( $P ).$R;
    }

    public function ajaxFormCreateContactPerson( $TblSalutation_Id, $TblPerson_FirstName, $TblPerson_LastName )
    {

        return new Redirect(null);
    }
}