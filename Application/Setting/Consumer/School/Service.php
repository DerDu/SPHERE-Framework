<?php
namespace SPHERE\Application\Setting\Consumer\School;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\Service\Data;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\School\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\School
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @return bool|TblSchool[]
     */
    public function getSchoolAll()
    {

        return (new Data($this->getBinding()))->getSchoolAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchool
     */
    public function getSchoolById($Id)
    {

        return (new Data($this->getBinding()))->getSchoolById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param integer        $School
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createSchool(
        IFormInterface $Form,
        $Type,
        $School
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Type) {
            return $Form;
        }

        $Error = false;
        if (!( Type::useService()->getTypeById($Type['Type']) )) {
            $Form->setError('Type[Type]', 'Bitte geben Sie eine Schulart an');
            $Error = true;
        }
        if (null === $School) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schule aus')))));
            $Error = true;
        } else {
            $tblCompany = Company::useService()->getCompanyById($School);
            if (!$tblCompany){
                $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schule aus')))));
                $Error = true;
            }
        }

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($School);
            $tblType = Type::useService()->getTypeById($Type['Type']);
            if ($tblCompany && (new Data($this->getBinding()))->addSchool($tblCompany, $tblType)
            ) {
                return new Success('Die Schule wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Die Schule konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/School', Redirect::TIMEOUT_ERROR, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param TblSchool $tblSchool
     *
     * @return bool
     */
    public function destroySchool(TblSchool $tblSchool)
    {

        return (new Data($this->getBinding()))->removeSchool($tblSchool);
    }
}
