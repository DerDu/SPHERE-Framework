<?php
namespace SPHERE\System\Extension\Repository;

//use OneLogin\Saml2\Error;
//use OneLogin\Saml2\Settings;
//use OneLogin\Saml2\Utils;
use OneLogin_Saml2_Auth;
use OneLogin_Saml2_Error;
use OneLogin_Saml2_Settings;
use OneLogin_Saml2_Utils;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlEVSSN;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Redirect;

class phpSaml
{

    private $config = array();

    /**
     * PdfMerge constructor.
     */
    public function __construct($config)
    {

        $this->config = $config;

        require_once(__DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Library'
            . DIRECTORY_SEPARATOR . 'php-saml2'
            . DIRECTORY_SEPARATOR . 'php-saml-master'
            . DIRECTORY_SEPARATOR . '_toolkit_loader.php');

        OneLogin_Saml2_Utils::setProxyVars(true);
    }

    /**
     * @throws OneLogin_Saml2_Error
     */
    public function samlLogin()
    {

        $Auth = new OneLogin_Saml2_Auth($this->config);
        $Auth->login();
    }

    /**
     * @return string
     */
    public function getMetaData()
    {

        try {
            #$auth = new OneLogin_Saml2_Auth($settingsInfo);
            #$settings = $auth->getSettings();
            // Now we only validate SP settings
            $settings = new OneLogin_Saml2_Settings(SamlEVSSN::getSAML());
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
            if (empty($errors)) {
                header('Content-Type: text/xml');
                return $metadata;
            } else {
                throw new OneLogin_Saml2_Error(
                    'Invalid SP metadata: '.implode(', ', $errors),
                    OneLogin_Saml2_Error::METADATA_SP_INVALID
                );
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function getAuthRequest()
    {

        // Session davor
//        echo '<pre>';
//        echo 'Session:<br/>';
//        var_dump($_SESSION);
//        echo '</pre>';

        $needsAuth = empty($_SESSION['samlUserdata']);

        // put SAML settings into an array to avoid placing files in the
        // composer vendor/ directories
        $auth = new OneLogin_Saml2_Auth($this->config);
        if ($needsAuth) {
            if (!empty($_REQUEST['SAMLResponse']) && !empty($_REQUEST['RelayState'])) {
                $auth->processResponse(null);
                $errors = $auth->getErrors();
                if (empty($errors)) {
                    // user has authenticated successfully
                    $needsAuth = false;
                    $_SESSION['samlUserdata'] = $auth->getAttributes();
                }
            }

            // return to Login Mask
            if ($needsAuth) {
                return (!empty($errors)
                    ? new Panel('Error', $errors, Panel::PANEL_TYPE_DANGER)
                    : new Panel('Error', new DangerText('Missing Userdata'), Panel::PANEL_TYPE_DANGER))
                    .new Redirect('/', Redirect::TIMEOUT_ERROR);
            }
        }

        $_SESSION['isAuthenticated'] = $auth->isAuthenticated();
        return false; // no errors

        // Session danach
//        echo '<pre>';
//        echo '<br/>Session:<br/>';
//        var_dump($_SESSION);
//        echo '</pre>';

    }
}