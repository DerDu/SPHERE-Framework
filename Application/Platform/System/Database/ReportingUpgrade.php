<?php

namespace SPHERE\Application\Platform\System\Database;

use Doctrine\DBAL\Schema\View;
use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Database as MocDatabase;
use MOC\V\Component\Template\Template;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Icon\Repository\FileExtension;
use SPHERE\Common\Frontend\Icon\Repository\Task;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Scrollable;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Error;
use SPHERE\System\Database\Type\MySql;

/**
 * Class ReportingUpgrade
 * @package SPHERE\Application\Platform\System\Database
 */
class ReportingUpgrade
{
    private $Host = '127.0.0.1';
    private $User = 'root';
    private $Password = '';

    /**
     * @var array $TemplateList array( 'viewNameInDatabase' => 'TemplateFileName.sql' )
     */
    private $TemplateList = array(
        'viewPeopleGroupMember' => 'viewPeopleGroupMember.twig',
        'viewContactAddress' => 'viewContactAddress.twig',
        'viewContactMail' => 'viewContactMail.twig',
        'viewContactPhone' => 'viewContactPhone.twig',
        'viewEducationStudent' => 'viewEducationStudent.twig',
//        'viewEducationTeacher' => 'viewEducationTeacher.twig',    // wird aktuell nicht benutzt
        'viewGroup' => 'viewGroup.twig',
        'viewGroupClub' => 'viewGroupClub.twig',
        'viewGroupCustody' => 'viewGroupCustody.twig',
        'viewGroupProspect' => 'viewGroupProspect.twig',
        'viewGroupStudentTransfer' => 'viewGroupStudentTransfer.twig',
        'viewGroupStudent' => 'viewGroupStudent.twig',
        'viewGroupStudentBasic' => 'viewGroupStudentBasic.twig',
//        'viewGroupStudentIntegration' => 'viewGroupStudentIntegration.twig',
        'viewGroupStudentSubject' => 'viewGroupStudentSubject.twig',
        'viewGroupTeacher' => 'viewGroupTeacher.twig',
        'viewPeopleMetaCommon' => 'viewPeopleMetaCommon.twig',
        'viewPerson' => 'viewPerson.twig',
        'viewPersonContact' => 'viewPersonContact.twig',
        'viewRelationshipToPerson' => 'viewRelationshipToPerson.twig',
        'viewStudent' => 'viewStudent.twig',
        'viewStudentCustody' => 'viewStudentCustody.twig',
        'viewStudentAuthorized' => 'viewStudentAuthorized.twig',
        'viewProspectCustody' => 'viewProspectCustody.twig'
    );

    /**
     * ReportingUpgrade constructor.
     * @param string $Host 127.0.0.1
     * @param string $User root
     * @param string $Password {none}
     */
    public function __construct($Host = '127.0.0.1', $User = 'root', $Password = '')
    {
        $this->Host = $Host;
        $this->User = $User;
        $this->Password = $Password;
    }

    /**
     * @return string
     */
    public function migrateReporting()
    {
        // All available Consumer
        try {
            $ConsumerList = $this->getConsumerList();
        } catch (\Exception $Exception) {
            $ConsumerList = array();
        }
        if (empty($ConsumerList)) {
            return new Warning('No Consumer available!')
                . (isset($Exception) ? $this->parseException($Exception, 'Consumer') : '');
        }

        // Location of SQL Templates
        $TemplateDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'ReportingUpgrade';

        // Execution Protocol
        $ProtocolList = array();
        $ProtocolError = 0;

        // Repeat for every Consumer
        foreach ($ConsumerList as $Acronym) {
            // Connect to DB Server
            try {
                $Connection = $this->getConnection($this->Host, $this->User, $this->Password, $Acronym);
            } catch (\Exception $Exception) {
                $Connection = null;
            }
            if (empty($Connection)) {
                return new Danger('No Connection to Server!')
                    . (isset($Exception) ? $this->parseException($Exception, 'Database') : '');

            }

            // Get Schema DBAL
            $SchemaManager = $Connection->getSchemaManager();
            if (empty($Connection)) {
                return new Danger('No Schema Manager available!')
                    . (isset($Exception) ? $this->parseException($Exception, 'Schema') : '');

            }

            // Repeate for every Template
            if (empty($this->TemplateList)) {
                return new Warning('No Template available!')
                    . (isset($Exception) ? $this->parseException($Exception, 'Template') : '');
            }
            foreach ($this->TemplateList as $ViewName => $TemplateFile) {

                // Check Template-File
                $File = $TemplateDirectory . DIRECTORY_SEPARATOR . $TemplateFile;
                if (file_exists($File) && is_readable($File)) {

                    // Open Template
                    $Content = file_get_contents($TemplateDirectory . DIRECTORY_SEPARATOR . $TemplateFile);

                    // Set View-Name & Consumer-Acronym
                    $Template = Template::getTwigTemplateString($Content);
                    $Template->setVariable('ConsumerAcronym', $Acronym);
                    $Template->setVariable('ViewName', $ViewName);

                    // Build View
                    $View = new View($ViewName, $Template->getContent());

                    // Create/Upgrade View
                    if (array_key_exists(strtolower($ViewName), $SchemaManager->listViews())) {
                        // View exists
                        $ProtocolList[] = new Task() . ' Update existing ' . $ViewName . ' @ SettingConsumer_' . strtoupper($Acronym) . new Scrollable('<pre><code class="sql">' . $View->getSql() . '</code></pre>', 100);
                        try {
                            $SchemaManager->dropAndCreateView($View);
                        } catch (\Exception $Exception) {
                            $ProtocolList[] = new Danger('Schema Upgrade failed!')
                                . (isset($Exception) ? $this->parseException($Exception, 'Schema') : '');
                            $ProtocolError++;
                            continue;
                        }
                    } else {
                        // View is missing
                        $ProtocolList[] = new FileExtension() . ' Create new ' . $ViewName . ' @ SettingConsumer_' . strtoupper($Acronym) . new Scrollable('<pre><code class="sql">' . $View->getSql() . '</code></pre>', 100);
                        try {
                            $SchemaManager->createView($View);
                        } catch (\Exception $Exception) {
                            $ProtocolList[] = new Danger('Schema Upgrade failed!')
                                . (isset($Exception) ? $this->parseException($Exception, 'Schema') : '');
                            $ProtocolError++;
                            continue;
                        }
                    }
                } else {
                    return new Danger('Template ' . $File . ' not available!')
                        . (isset($Exception) ? $this->parseException($Exception, 'Template') : '');
                }
            }
        }

        if( $ProtocolError > 0 ) {
            return new Danger('Migration failed on '.$ProtocolError.' Views') . new Listing($ProtocolList);
        } else {
            return new Success('Migration finished') . new Listing($ProtocolList);
        }
    }

    /**
     * @return array
     */
    private function getConsumerList()
    {
        $tblConsumerAll = Consumer::useService()->getConsumerAll();
        $ConsumerAcronymList = array();
        if ($tblConsumerAll) {
            array_walk($tblConsumerAll, function (TblConsumer $tblConsumer) use (&$ConsumerAcronymList) {
                $ConsumerAcronymList[] = $tblConsumer->getAcronym();
            });
        }
        return $ConsumerAcronymList;
    }

    /**
     * @param \Exception $Exception
     * @param string $Name
     * @return Error
     */
    private function parseException(\Exception $Exception, $Name = '')
    {

        $TraceList = '';
        foreach ((array)$Exception->getTrace() as $Trace) {
            $TraceList .= nl2br('<samp class="text-info small">'
                . (isset($Trace['type']) && isset($Trace['function']) ? 'Method: ' . $Trace['type'] . $Trace['function'] : 'Method: ')
                . (isset($Trace['class']) ? '<br/>Class: ' . $Trace['class'] : '<br/>Class: ')
                . (isset($Trace['file']) ? '<br/>File: ' . $Trace['file'] : '<br/>File: ')
                . (isset($Trace['line']) ? '<br/>Line: ' . $Trace['line'] : '<br/>Line: ')
                . '</samp><br/>');
        }
        $Hit = '<hr/><samp class="text-danger"><div class="h6">' . get_class($Exception) . '<br/><br/>' . nl2br($Exception->getMessage()) . '</div>File: ' . $Exception->getFile() . '<br/>Line: ' . $Exception->getLine() . '</samp><hr/><div class="small">' . $TraceList . '</div>';
        return new Error(
            $Exception->getCode() == 0 ? $Name : $Exception->getCode(), $Hit, false
        );

    }

    /**
     * @param string $Host Server-Address (IP)
     * @param string $User
     * @param string $Password
     * @param string $Acronym DatabaseName will get prefix 'SettingConsumer_' e.g. SettingConsumer_{Acronym}
     * @return bool|IBridgeInterface
     */
    private function getConnection($Host, $User, $Password, $Acronym)
    {
        $Connection = MocDatabase::getDatabase(
            $User, $Password, 'SettingConsumer_' . strtoupper($Acronym), (new MySql())->getIdentifier(), $Host
        );
        if ($Connection->getConnection()->isConnected()) {
            return $Connection;
        }
        return false;
    }
}
