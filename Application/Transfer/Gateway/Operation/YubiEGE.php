<?php
namespace SPHERE\Application\Transfer\Gateway\Operation;

use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\System\Database\Filter\Link\Pile;

class YubiEGE extends AbstractConverter
{


    public function __construct($File)
    {

        $this->loadFile($File);

        // Default

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        // Specific

        $this->setPointer(new FieldPointer('A', 'K'));
        $this->setPointer(new FieldPointer('B', 'E'));
        $this->setPointer(new FieldPointer('E', 'V'));
        $this->setPointer(new FieldPointer('D', 'N'));
        $this->setPointer(new FieldPointer('G', 'Key'))->setSanitizer(
            new FieldSanitizer('G', 'Key', array($this, 'checkToken'))
        );

        $this->scanFile(1);
    }

    /**
     * @param array $Row
     *
     * @return mixed|void
     */
    public function runConvert($Row)
    {

        if (!empty( $Row['E']['V'] ) && !empty( $Row['E']['V'] )) {
            $Filter = array(
                array('TblGroup_MetaTable' => 'STAFF'),
                array(
                    'TblPerson_FirstName' => $Row['E']['V'],
                    'TblPerson_LastName'  => $Row['D']['N']
                )
            );

            $Result = ( new Pile() )
                ->addPile(
                    Group::useService(), new ViewPeopleGroupMember(),
                    null, 'TblMember_serviceTblPerson'
                )
                ->addPile(
                    Person::useService(), new ViewPerson(),
                    'TblPerson_Id', 'TblPerson_Id'
                )
                ->searchPile($Filter);

            $tblPerson = false;
            if (isset( $Result[0][1] )) {
                /** @var ViewPerson $viewPerson */
                $viewPerson = $Result[0][1];
                if (method_exists($viewPerson, '__toArray')) {
                    $viewPerson = $viewPerson->__toArray();

                    $tblPerson = Person::useService()->getPersonById($viewPerson['TblPerson_Id']);
                }
            }

            $tblConsumer = Consumer::useService()->getConsumerByAcronym('EGE');
            if ($tblConsumer && $Row['G']['Key']) {

                $tblToken = Token::useService()
                    ->insertToken($this->getModHex($Row['G']['Key'])->getIdentifier(), $tblConsumer)
                    ->getTokenByIdentifier($Row['G']['Key']);

                if (! ($tblAccount = Account::useService()->getAccountByUsername('EGE-'.strtoupper($Row['A']['K'])))) {



                    $tblAccount = Account::useService()->insertAccount('EGE-'.strtoupper($Row['A']['K']),
                        $Row['B']['E'], $tblToken, $tblConsumer);

                    $tblIdentification = Account::useService()->getIdentificationByName('Token');
                    Account::useService()->addAccountAuthentication( $tblAccount, $tblIdentification );

                    if( $tblPerson ) {
                        Account::useService()->addAccountPerson($tblAccount, $tblPerson);
                        if(
                            !Teacher::useService()->getTeacherByPerson( $tblPerson )
                            && !Teacher::useService()->getTeacherByAcronym( $Row['A']['K'] )
                        ) {
                            Teacher::useService()->insertTeacher( $tblPerson, $Row['A']['K'] );
                        }

                    }
                } else {
                    $tblIdentification = Account::useService()->getIdentificationByName('Token');
                    Account::useService()->addAccountAuthentication( $tblAccount, $tblIdentification );
                }
            }


        }
    }

    protected function checkToken($Value)
    {

        if (preg_match('!^[a-z]{12}$!is', $Value)) {
            return $Value;
        }
        return false;
    }

}
