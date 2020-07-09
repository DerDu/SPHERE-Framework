<?php

namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlEVSSN;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Setting\Agb\Agb;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Family;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Picture;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\ITextInterface;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Debugger\Logger\FileLogger;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\phpSaml;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage('Willkommen', '', '');
        $IsMaintenance = false;
        $contentTeacherWelcome = false;
        $contentHeadmasterWelcome = false;
        $IsEqual = false;
        $IsNavigationAssistance = false;

        $tblIdentificationSearch = Account::useService()->getIdentificationByName(TblIdentification::NAME_USER_CREDENTIAL);
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
                if ($tblPerson
                    && ($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                    && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
                ) {

                    $contentTeacherWelcome = Evaluation::useService()->getTeacherWelcome($tblPerson);
                }

                if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Type/Select')) {
                    $contentHeadmasterWelcome = Gradebook::useService()->getMissingSubjectsWithScoreType();
                }
            }
        }
        if ($tblAccount && $tblIdentificationSearch) {
            $tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount);
            if ($tblAuthentication && ($tblIdentification = $tblAuthentication->getTblIdentification())) {
                if ($tblIdentificationSearch->getId() == $tblIdentification->getId()) {
                    // Alle TblUserAccounts erhalten direktlink Button
                    $IsNavigationAssistance = true;
                    $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
                    if ($tblUserAccount) {
                        $Password = $tblUserAccount->getAccountPassword();
                        if ($tblAccount->getPassword() == $Password) {
                            $IsEqual = true;
                        }
                    }
                }
            }
        }
        $maintenanceMessage = '';
        if ($IsMaintenance) {
            $now = new \DateTime();
            if ($now >= new \DateTime('22:00')) {
                $maintenanceMessage = new DangerMessage(new WarningIcon().' Achtung heute ('.$now->format('d.m.Y').') ab 22:00 Wartungsarbeiten ');
            } elseif ($now >= new \DateTime('20:00')) {
                $maintenanceMessage = new Warning(new WarningIcon().' Achtung heute ('.$now->format('d.m.Y').') ab 22:00 Wartungsarbeiten ');
            }
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ($IsMaintenance
                                ? $maintenanceMessage
                                : ''
                            )
                        )
                    )
                )
            )
            .($IsEqual
                ? $this->layoutPasswordChange()
                : ''
            )
            .($IsNavigationAssistance
                ? $this->layoutNavigationAssistance()
                : ''
            )
            . ($contentHeadmasterWelcome ? $contentHeadmasterWelcome : '')
            .($contentTeacherWelcome ? $contentTeacherWelcome : '')
            .$this->getCleanLocalStorage()
        );

        return $Stage;
    }

    /**
     * @return string|Layout
     */
    private function layoutNavigationAssistance()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(
                        new Panel(new Center('Notenübersicht'),
                            array(
                                new Container('&nbsp;').new Center(new Paragraph('Die Notenübersicht erreichen Sie über das Menü '
                                        .new Bold('Bildung => Zensuren => Notenübersicht').' oder über folgenden Link')
                                    .new Standard('Notenübersicht', '/Education/Graduation/Gradebook/Student/Gradebook',
                                        new Family())
                                )
                            )
                            , Panel::PANEL_TYPE_INFO
                        )
                        , 8)
                ))
            )
        );
    }

    /**
     * @return Layout
     */
    private function layoutPasswordChange()
    {
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(
                        new Center(new Panel('Passwortänderung',
                            new Warning('Bitte ändern Sie zu Ihrer Sicherheit das Passwort.')
                            , Panel::PANEL_TYPE_DANGER,
                            new Standard('Passwort ändern', '/Setting/MyAccount/Password'
                                , new Key(), array(), 'Schnellzugriff der Passwort Änderung')))
                        , 8)
                ))
            )
        );
    }

    /**
     * @return string
     */
    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }

    /**
     * Step 1/3
     *
     * @param string $CredentialName
     * @param string $CredentialLock
     *
     * @return Stage
     */
    public function frontendIdentificationCredential($CredentialName = null, $CredentialLock = null)
    {
        $View = new Stage(new Nameplate().' Anmelden', '', $this->getIdentificationEnvironment());

        // Search for matching Account
        $tblAccount = null;
        $tblIdentification = null;
        if ($CredentialName && $CredentialLock) {
            if (!$tblAccount) {
                // Check Credential with Token
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_TOKEN);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
            if (!$tblAccount) {
                // Check Credential with Token (System-Admin)
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_SYSTEM);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
            if (!$tblAccount) {
                // Check Credential
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_CREDENTIAL);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
            if (!$tblAccount) {
                // Check Credential
                $tblIdentification = Account::useService()->getIdentificationByName(TblIdentification::NAME_USER_CREDENTIAL);
                $tblAccount = Account::useService()
                    ->getAccountByCredential($CredentialName, $CredentialLock, $tblIdentification);
            }
        }

        // Matching Account found?
        if ($tblAccount && $tblIdentification) {
            switch ($tblIdentification->getName()) {
                case TblIdentification::NAME_TOKEN:
                case TblIdentification::NAME_SYSTEM:
                    return $this->frontendIdentificationToken($tblAccount->getId(), $tblIdentification->getId());
                case TblIdentification::NAME_CREDENTIAL:
                case TblIdentification::NAME_USER_CREDENTIAL:
                    return $this->frontendIdentificationAgb($tblAccount->getId(), $tblIdentification->getId());
            }
        }

        // Field Definition
        $CredentialNameField = (new TextField('CredentialName', 'Benutzername', 'Benutzername', new Person()))
            ->setRequired()->setAutoFocus();
        $CredentialLockField = (new PasswordField('CredentialLock', 'Passwort', 'Passwort', new Lock()))
            ->setRequired()->setDefaultValue($CredentialLock, true);

        // Error Handling
        if ($CredentialName !== null) {
            if (empty($CredentialName)) {
                $CredentialNameField->setError('Bitte geben Sie Ihren Benutzernamen an');
            }
        }
        if ($CredentialLock !== null) {
            if (empty($CredentialLock)) {
                $CredentialLockField->setError('Bitte geben Sie Ihr Passwort an');
            }
        }
        $FormError = new Container('');
        if ($CredentialName && $CredentialLock && !$tblAccount) {
            $CredentialNameField->setError('');
            $CredentialLockField->setError('');
            $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
        }

        // Create Form
        $Form = new Form(
            new FormGroup(array(
                    new FormRow(
                        new FormColumn(array(
                            new Headline('Anmeldung Schulsoftware'),
                            new Ruler(),
                            new Listing(array(
                                new Container($CredentialNameField) .
                                new Container($CredentialLockField)
                            )),
                            $FormError
                        ))
                    ),
                    new FormRow(
                        new FormColumn(array(
                            (new Primary('Login')),
                        ))
                    )
                )
            )
        );

        // set depending information
        if(strtolower($this->getRequest()->getHost()) == 'www.schulsoftware.schule'
//            || $this->getRequest()->getHost() == '192.168.75.128' // local test
        ){
            $Form.= new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                    '<br/><br/><br/><br/>',
                    new Title('Anmeldung CONNEXION (Pilot)'),
                    new PrimaryLink('Login', 'SPHERE\Application\Platform\Gatekeeper\Saml\Login\EVSSN')
                    //. new Link('.', 'SPHERE\Application\Platform\Gatekeeper\Saml\Login\EKM') // EKM -> Beispiel kann für zukünftige IDP's verwendet werden
                ))
            )));
        }

        setcookie('cookies_available', 'enabled', time() + (86400 * 365), '/');

        $View->setContent($this->getIdentificationLayout($Form));

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendIdentificationSamlEVSSN()
    {

        return $this->LoginSecondPageLogic(SamlEVSSN::getSAML());
    }

//    /**
//     * // EKM -> Beispiel kann für zukünftige IDP's verwendet werden
//     * @return Stage
//     */
//    public function frontendIdentificationSamlEKM()
//    {
//
//        return $this->LoginSecondPageLogic(SamlEKM::getSAML());
//    }

    private function LoginSecondPageLogic($Config = array())
    {

        $Stage = new Stage(new Nameplate().' Anmelden', '', $this->getIdentificationEnvironment());

        $Saml = new phpSaml($Config);
        if(($Error = $Saml->getAuthRequest())){
            $Stage->setContent($Error);
            return $Stage;
        }
        $tblAccount = null;
        $LoginOk = false;

        if(isset($_SESSION['samlUserdata']['ucsschoolRecordUID']) && $_SESSION['samlUserdata']['ucsschoolRecordUID']){
            $AccountId = current($_SESSION['samlUserdata']['ucsschoolRecordUID']);

            $tblAccount = Account::useService()->getAccountById($AccountId);
        }
        if(isset($_SESSION['isAuthenticated']) && $_SESSION['isAuthenticated']){
            $LoginOk = true;
        }

        if(($ExistSessionAccount = Account::useService()->getAccountBySession())){
            // is requested account the same like session account go to welcome
            if($tblAccount && $ExistSessionAccount->getId() == $tblAccount->getId()){
                $Stage->setContent(new Redirect('/', Redirect::TIMEOUT_SUCCESS));
                return $Stage;
            }
            // remove existing Session if User is not the same
            if($Session = session_id()){
                Account::useService()->destroySession(null, $Session);
            }

        }

        $tblIdentification = null;
        if($tblAccount
            && $LoginOk
            && ($tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount))){
            $tblIdentification = $tblAuthentication->getTblIdentification();
        }

        // Matching Account found?
        if ($tblAccount && $tblIdentification) {
            // Anfragen von SAML müssen Cookies aktiviert haben
            $isCookieAvailable = true;
            switch ($tblIdentification->getName()) {
                case TblIdentification::NAME_TOKEN:
                case TblIdentification::NAME_SYSTEM:
                    return $this->frontendIdentificationToken($tblAccount->getId(), $tblIdentification->getId(), null, $isCookieAvailable);
                case TblIdentification::NAME_CREDENTIAL:
                case TblIdentification::NAME_USER_CREDENTIAL:
                    return $this->frontendIdentificationAgb($tblAccount->getId(), $tblIdentification->getId(), 0, $isCookieAvailable);
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
            new LayoutColumn(new Warning('Ihr Login von CONNEXION ist im System nicht bekannt, bitte wenden Sie sich an einen zuständigen Administrator'))
        ))));

        return $Stage;
    }

    /**
     * Environment Information
     *
     * @return ITextInterface
     */
    private function getIdentificationEnvironment()
    {
        switch (strtolower($this->getRequest()->getHost())) {
            case 'www.schulsoftware.schule':
            case 'www.kreda.schule':
                return new InfoText('');
                break;
            case 'demo.schulsoftware.schule':
            case 'demo.kreda.schule':
                return new Danger(new Picture().' Demo-Umgebung');
                break;
            default:
                return new WarningText( new Globe().' '.$this->getRequest()->getHost());
        }
    }

    /**
     * @param int         $tblAccount
     * @param int         $tblIdentification
     * @param null|string $CredentialKey
     * @param bool        $isCookieAvailable
     *
     * @return Stage
     */
    public function frontendIdentificationToken($tblAccount, $tblIdentification, $CredentialKey = null, $isCookieAvailable = false)
    {
        $View = new Stage(new YubiKey().' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);
        $tblIdentification = Account::useService()->getIdentificationById($tblIdentification);

        // Return on Input Error
        if (
            !$tblAccount
            || !$tblIdentification
            || !$tblAccount->getServiceTblIdentification()
            || !$tblAccount->getServiceTblIdentification()->getId() == $tblIdentification->getId()
        ) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        // Field Definition
        $CredentialKeyField = (new PasswordField('CredentialKey', 'YubiKey', 'YubiKey', new YubiKey()))
            ->setRequired()->setAutoFocus();

        // Search for matching Token
        $FormError = new Container('');
        if ($CredentialKey) {
            $Identifier = $this->getModHex($CredentialKey)->getIdentifier();
            $tblToken = Token::useService()->getTokenByIdentifier($Identifier);
            if (
                $tblToken
                && $tblAccount->getServiceTblToken()
                && $tblAccount->getServiceTblToken()->getId() == $tblToken->getId()
            ) {
                // Credential correct, Token correct -> LOGIN
                try {
                    if (Token::useService()->isTokenValid($CredentialKey)) {
                        if (session_status() == PHP_SESSION_ACTIVE) {
                            session_regenerate_id();
                        }
                        Account::useService()->createSession($tblAccount, session_id());
                        $View->setTitle(new Ok() . ' Anmelden');
                        $View->setContent(
                            $this->getIdentificationLayout(
                                new Headline('Anmelden', 'Bitte warten...')
                                . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                            )
                        );
                        return $View;
                    } else {
                        // Error Token invalid
                        $CredentialKeyField->setError('');
                        $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                    }
                } catch (\Exception $Exception) {

                    (new DebuggerFactory())->createLogger(new ErrorLogger())->addLog('YubiKey-Api Error: '.$Exception->getMessage());
                    (new DebuggerFactory())->createLogger(new FileLogger())->addLog('YubiKey-Api Error: '.$Exception->getMessage());

                    // Error Token API Error
                    $CredentialKeyField->setError('');
                    $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
                }
            } else {
                // Error Token not registered
                $CredentialKeyField->setError('');
                $FormError = new Listing(array(new Danger(new Exclamation() . ' Die eingegebenen Zugangsdaten sind nicht gültig')));
            }
        }

        // Switch User/Account (Restart Identification Process)
        $FormInformation = array(
            $tblAccount->getServiceTblConsumer()->getAcronym().' - '.$tblAccount->getServiceTblConsumer()->getName(),
            'Benutzer: ' . $tblAccount->getUsername()
            // . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
        );

        if (isset($_COOKIE['cookies_available']) || $isCookieAvailable) {
            // Create Form
            $Form = new Form(
                new FormGroup(array(
                        new FormRow(
                            new FormColumn(array(
                                new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                                new Ruler(),
                                new Listing($FormInformation),
                                new Listing(array(
                                    new Container($CredentialKeyField)
                                )),
                                $FormError
                            ))
                        ),
                        new FormRow(
                            new FormColumn(array(
                                (new Primary('Bestätigen'))
                            ))
                        )
                    )
                )
                , null, new Route(__NAMESPACE__ . '/Token'), array(
                'tblAccount' => $tblAccount,
                'tblIdentification' => $tblIdentification
            ));

            $View->setContent($this->getIdentificationLayout($Form));
        } else {
            // es sind keine Cookies erlaubt -> Login ist nicht möglich
            $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(array(
                    new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                    new Ruler(),
                    new Listing($FormInformation),
                    $this->getCookieMessage()
                ))
            ))));
            $View->setContent($this->getIdentificationLayout($layout));
        }

        return $View;
    }

    /**
     * @return DangerMessage
     */
    private function getCookieMessage()
    {
        return new DangerMessage(
            new Title(new Exclamation().' Browsereinstellungen')
            .'Sie scheinen Cookies in Ihrem Browser deaktiviert zu haben.' . '<br/>'
            .'Bitte überprüfen Sie die Einstellungen in Ihrem Browser und versuchen Sie es erneut.'
//            'Ihre Browsereinstellungen lassen keine Cookies zu.' . '<br><br>'
//            . 'Um auf die Schulsoftware zu können, müssen Sie Cookies in Ihrem Browser zulassen.' . '<br><br>'
//            . 'Darum sind Cookies notwendig:' . '<br>'
//            . 'Aus Sicherheitsgründen wird beim Login ein Cookie auf Ihrem Rechner gespeichert. ' . '<br>'
//            . 'So wird sichergestellt, dass nur Sie während einer Sitzung auf die Schulsoftware zugreifen können.' . '<br>'
//            . 'Wenn Sie sich ausloggen oder das Browserfenster schließen, wird das Cookie gelöscht und Ihre Sitzung dadurch ungültig gemacht.'
        );
    }

    /**
     * Stage Layout
     *
     * @param $Content
     * @return Layout
     */
    private function getIdentificationLayout($Content)
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    ''
                    , 2),
                new LayoutColumn(
                    $Content
                    , 8),
                new LayoutColumn(
                    ''
                    , 2),
            )),
        )));
    }

    /**
     * @param int  $tblAccount
     * @param int  $tblIdentification
     * @param int  $doAccept 0|1
     * @param bool $isCookieAvailable
     *
     * @return Stage
     */
    public function frontendIdentificationAgb($tblAccount, $tblIdentification, $doAccept = 0, $isCookieAvailable = false)
    {

        $View = new Stage(new MoreItems().' Anmelden', '', $this->getIdentificationEnvironment());

        $tblAccount = Account::useService()->getAccountById($tblAccount);
        $tblIdentification = Account::useService()->getIdentificationById($tblIdentification);

        // Return on Input Error
        if (
            !$tblAccount
            || !$tblIdentification
            || !$tblAccount->getServiceTblIdentification()
            || !$tblAccount->getServiceTblIdentification()->getId() == $tblIdentification->getId()
            || !$tblAccount->getServiceTblConsumer()
        ) {
            // Restart Identification Process
            return $this->frontendIdentificationCredential();
        }

        // es sind keine Cookies erlaubt -> Login ist nicht möglich
        if (!isset($_COOKIE['cookies_available'])) {
            // Bypass Cookies
            if(!$isCookieAvailable){
                $FormInformation = array(
                    $tblAccount->getServiceTblConsumer()->getAcronym() . ' - ' . $tblAccount->getServiceTblConsumer()->getName(),
                    'Benutzer: ' . $tblAccount->getUsername()
                );

                $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(array(
                        new Headline('Bitte geben Sie Ihre Zugangsdaten ein'),
                        new Ruler(),
                        new Listing($FormInformation),
                        $this->getCookieMessage()
                    ))
                ))));

                $View->setContent($this->getIdentificationLayout($layout));

                return $View;
            }
        }

        $Headline = 'Allgemeine Geschäftsbedingungen';

        // IS Accepted?
        // Sanatize Agb Setting
        $tblSetting = Account::useService()->getSettingByAccount($tblAccount, 'AGB');
        if (!$tblSetting) {
            $tblSetting = Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_EMPTY_AGB);
        }
        // Check/Set Agb Setting
        if( $tblSetting->getValue() == TblSetting::VAR_ACCEPT_AGB || $doAccept == 1 ) {
            if( $doAccept == 1 ) {
                Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_ACCEPT_AGB);
            }
            // Credential correct, Agb accepted -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // NOT Accepted?
        // Check if Parent-Account || Student-Account
        $tblUserAccount = UserAccount::useServiceByConsumer($tblAccount->getServiceTblConsumer())->getUserAccountByAccount($tblAccount);
        if ($tblUserAccount &&
            ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY || $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT)) {
            // IS Parent-Account || Student-Account
            if($tblSetting->getValue() == TblSetting::VAR_UPDATE_AGB) {
                $Headline = 'Allgemeine Geschäftsbedingungen - Aktualisierung';
            }
            $View->setDescription( $Headline );
        } else {
            // NOT Parent-Account
            // Credential correct, NO Agb check -> LOGIN
            Account::useService()->createSession($tblAccount);
            $View->setTitle( new Ok().' Anmelden' );
            $View->setContent(
                $this->getIdentificationLayout(
                    new Headline('Anmelden', 'Bitte warten...')
                    . new Redirect('/', Redirect::TIMEOUT_SUCCESS)
                )
            );
            return $View;
        }

        // Switch User/Account (Restart Identification Process)
        $FormInformation = array(
            $tblAccount->getServiceTblConsumer()->getAcronym().' - '.$tblAccount->getServiceTblConsumer()->getName(),
            'Benutzer: ' . $tblAccount->getUsername()
            // . new PullRight(new Small(new Link('Mit einem anderen Benutzer anmelden', new Route(__NAMESPACE__))))
        );
        // Create Form
        $Form = new Layout(
            new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Bestätigung der Allgemeine Geschäftsbedingungen:'),
                            new Paragraph('Wenn Sie vorstehenden Regelungen einverstanden sind und die elektronische
                            Notenübersicht nutzen möchten, so klicken sie unten auf [Einwilligen].')
                            .new Paragraph('Andernfalls klicken Sie auf [Ablehnen], um keinen Zugang zum elektronischen
                            Notenbuch zu erhalten.'),
                            new Listing($FormInformation)
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new PullLeft( new Success('Einwilligen',new Route(__NAMESPACE__ . '/Agb'), new Enable(), array(
                                'tblAccount' => $tblAccount,
                                'tblIdentification' => $tblIdentification,
                                'doAccept' => 1
                            )) ),
                            new PullRight( new DangerLink('Ablehnen',new Route(__NAMESPACE__ ), new Disable(), array()) )
                        ))
                    )
                )
            ));

        $View->setContent(
            $this->getIdentificationLayout(
                new Listing(array(
                    new Header(new Bold($Headline)),
                    Agb::useFrontend()->getAgbContent($tblAccount)
                ))
                . $Form
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendDestroySession()
    {
        $Stage = new Stage(new Off().' Abmelden', '', $this->getIdentificationEnvironment());

//        $tblAccount = Account::useService()->getAccountBySession();
//        if($tblAccount->getId() == 3823 ){
//            $phpSaml = new phpSaml();
//            $phpSaml->getSLO();
//        }
        $Stage->setContent(
            $this->getIdentificationLayout(
                new Headline('Abmelden', 'Bitte warten...').
                Account::useService()->destroySession(
                    new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
                ) . $this->getCleanLocalStorage()
            )
        );

        return $Stage;
    }

    /**
     * Prepare if other sign out will come. now not in use.
     * Route deprecated
     * @return Stage
     */
    public function frontendSLO()
    {

        $Stage = new Stage(new Off().' Abmelden', '', $this->getIdentificationEnvironment());

        $Stage->setContent(
            $this->getIdentificationLayout(
                new Headline('Abmelden', 'Bitte warten...').
                Account::useService()->destroySession(
                    new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_SUCCESS)
                ) . $this->getCleanLocalStorage()
            )
        );

        return $Stage;
    }
}
