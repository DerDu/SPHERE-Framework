<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlEVSSN;
use SPHERE\System\Extension\Repository\phpSaml;

/**
 * Class Frontend
 * @package SPHERE\Application\Platform\Gatekeeper\Saml
 */
class Frontend
{

    public function XMLMetaData()
    {

        // no config needed
        $PhpSaml = new phpSaml('');
        echo $PhpSaml->getMetaData();
        exit;
    }

    public function frontendLoginEVSSN()
    {

        $PhpSaml = new phpSaml(SamlEVSSN::getSAML());
        $PhpSaml->samlLogin();
    }

//    // EKM -> Beispiel kann für zukünftige IDP's verwendet werden
//    public function frontendLoginEKM()
//    {
//
//        $PhpSaml = new phpSaml(SamlEKM::getSAML());
//        $PhpSaml->samlLogin();
//    }
}