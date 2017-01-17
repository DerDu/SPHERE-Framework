<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPerson
 *
 * @package SPHERE\Application\Api\People
 */
class ApiPerson extends Extension implements IApiInterface
{

    const API_DISPATCHER = 'MethodName';

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('pieceFormCreatePerson');
        $Dispatcher->registerMethod('pieceFormValidatePerson');

        return $Dispatcher->callMethod($MethodName);
    }


    public function pieceFormValidatePerson( $CreatePersonReceiver, $Person = null, $Confirm = false )
    {

        if( (!isset( $Person['FirstName'] ) || empty( $Person['FirstName'] ) )
            || ( !isset( $Person['LastName'] ) || empty( $Person['LastName'] ) )
        ) {

            $Form = Person::useFrontend()->formPerson();

            if( (!isset( $Person['FirstName'] ) || empty( $Person['FirstName'] ) ) ) {
                $Form->setError( 'Person[FirstName]', 'VN' );
            }
            if( (!isset( $Person['LastName'] ) || empty( $Person['LastName'] ) ) ) {
                $Form->setError( 'Person[LastName]', 'NN' );
            }

            $Form->appendFormButton(
                new Primary('Speichern', new Save())
            )
                ->ajaxPipelineOnSubmit(
                    $this->pipelineValidatePerson( $CreatePersonReceiver )
                );

            return (string)$Form.(new Notify('Person konnte nicht angelegt werden','Bitte überprüfen Sie ihre Eingaben', Notify::TYPE_DANGER, 10000));
        }


        $Pile = new Pile();
        $Pile->addPile( Person::useService(), new ViewPerson() );
        $Result = $Pile->searchPile( array(
            array(
                ViewPerson::TBL_PERSON_FIRST_NAME => explode( ' ', $Person['FirstName'] ),
                ViewPerson::TBL_PERSON_LAST_NAME => explode( ' ', $Person['LastName'] )
            )
        ));

        if( empty($Result) || $Confirm ) {

            $Form = Person::useFrontend()->formPerson();
            $Form->appendFormButton(
                new Primary('Speichern', new Save())
            )
                ->ajaxPipelineOnSubmit(
                    $this->pipelineValidatePerson( $CreatePersonReceiver )
                );

            return Person::useService()->createPerson( $Form, $Person );

        } else {

            $TableList = array();
            /** @var ViewPerson[] $ViewPerson */
            foreach( $Result as $Index => $ViewPerson ) {
                $TableList[$Index] = current($ViewPerson)->__toArray();
                // TODO: Link
                $TableList[$Index]['Option'] = new Standard('123','');
            }

            $ConfirmPersonReceiver = (new BlockReceiver())->setIdentifier( $CreatePersonReceiver );
            $ConfirmPersonPipeline = new Pipeline();
            $ConfirmPersonEmitter = new ServerEmitter( $ConfirmPersonReceiver, ApiPerson::getRoute() );
            $ConfirmPersonEmitter->setGetPayload(array(
                ApiPerson::API_DISPATCHER => 'pieceFormValidatePerson',
                'CreatePersonReceiver' => $CreatePersonReceiver,
                'Confirm' => 1
            ));
            $ConfirmPersonEmitter->setPostPayload(array( 'Person' => $Person));
            $ConfirmPersonPipeline->addEmitter( $ConfirmPersonEmitter );

            return new TableData( $TableList, null, array(
                    ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                    ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                    ViewPerson::TBL_PERSON_SECOND_NAME => 'Zweiter Vorname',
                    ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                    ViewPerson::TBL_PERSON_BIRTH_NAME => 'Geburtsname',
                ) ).(new Standard('Mache Sicher?','#'))->ajaxPipelineOnClick( $ConfirmPersonPipeline );
        }
    }

    public function pieceFormCreatePerson( $CreatePersonReceiver )
    {
        $Form = Person::useFrontend()->formPerson();
        $Form->appendFormButton(
            new Primary('Speichern', new Save())
        )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
        ->ajaxPipelineOnSubmit(
            $this->pipelineValidatePerson( $CreatePersonReceiver )
        );

        return (string)$Form;
    }

    private function pipelineValidatePerson( $CreatePersonReceiver )
    {
        $ValidatePersonReceiver = (new BlockReceiver())->setIdentifier( $CreatePersonReceiver );
        $ValidatePersonPipeline = new Pipeline();
        $ValidatePersonEmitter = new ServerEmitter( $ValidatePersonReceiver, ApiPerson::getRoute() );
        $ValidatePersonEmitter->setGetPayload(array(
            ApiPerson::API_DISPATCHER => 'pieceFormValidatePerson',
            'CreatePersonReceiver' => $CreatePersonReceiver
        ));
        $ValidatePersonEmitter->setLoadingMessage('Daten werden überprüft...');
        $ValidatePersonPipeline->addEmitter( $ValidatePersonEmitter );
        return $ValidatePersonPipeline;
    }
}