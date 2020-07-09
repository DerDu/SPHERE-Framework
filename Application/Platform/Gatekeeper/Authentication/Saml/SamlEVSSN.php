<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication\Saml;

class SamlEVSSN
{

    /**
     * @return array
     */
    public static function getSAML(){
        return[
            // If 'strict' is True, then the PHP Toolkit will reject unsigned
            // or unencrypted messages if it expects them signed or encrypted
            // Also will reject the messages if not strictly follow the SAML
            // standard: Destination, NameId, Conditions ... are validated too.
            'strict' => true,

            // Enable debug mode (to print errors)
            'debug' => true,

            // Set a BaseURL to be used instead of try to guess
            // the BaseURL of the view that process the SAML Message.
            // Ex. http://sp.example.com/
            //     http://example.com/sp/
            'baseurl' => null,

            // Service Provider Data that we are deploying
            'sp' => array(
                // Identifier of the SP entity  (must be a URI)
                'entityId' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Saml/MetaData',
                // Specifies info about where and how the <AuthnResponse> message MUST be
                // returned to the requester, in this case our SP.
                'assertionConsumerService' => array(
                    // URL Location where the <Response> from the IdP will be returned
                    'url' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Authentication/Saml/EVSSN',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-POST binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
//        // If you need to specify requested attributes, set a
//        // attributeConsumingService. nameFormat, attributeValue and
//        // friendlyName can be omitted. Otherwise remove this section.
//        "attributeConsumingService"=> array(
//                "serviceName" => "Schulsoftware",
//                "serviceDescription" => "SSO Schulsoftware",
//                "requestedAttributes" => array(
//                    array(
//                        "name" => "record_uid",
//                        "isRequired" => true,
//                        "nameFormat" => "",
//                        "friendlyName" => "",
//                        "attributeValue" => ""
//                    )
//                )
//        ),
                // Specifies info about where and how the <Logout Response> message MUST be
                // returned to the requester, in this case our SP.
                'singleLogoutService' => array(
                    // URL Location where the <Response> from the IdP will be returned
                    'url' => 'https://schulsoftware.schule/Platform/Gatekeeper/Authentication/SLO',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Specifies constraints on the name identifier to be used to
                // represent the requested subject.
                // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

                // Usually x509cert and privateKey of the SP are provided by files placed at
                // the certs folder. But we can also provide them with the following parameters
                'x509cert' => '',
                'privateKey' => '',

                /*
                 * Key rollover
                 * If you plan to update the SP x509cert and privateKey
                 * you can define here the new x509cert and it will be
                 * published on the SP metadata so Identity Providers can
                 * read them and get ready for rollover.
                 */
                // 'x509certNew' => '',
            ),

            // Identity Provider Data that we want connect with our SP
            'idp' => array(
                // Identifier of the IdP entity  (must be a URI)
                'entityId' => 'https://ucs-sso.connexion.evssn.de/simplesamlphp/saml2/idp/metadata.php',
                // SSO endpoint info of the IdP. (Authentication Request protocol)
                'singleSignOnService' => array(
                    // URL Target of the IdP where the SP will send the Authentication Request Message
                    'url' => 'https://ucs-sso.connexion.evssn.de/simplesamlphp/saml2/idp/SSOService.php',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // SLO endpoint info of the IdP.
                'singleLogoutService' => array(
                    // URL Location of the IdP where the SP will send the SLO Request
                    'url' => 'https://ucs-sso.connexion.evssn.de/simplesamlphp/saml2/idp/SingleLogoutService.php',
                    // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
                    // if not set, url for the SLO Request will be used
                    'responseUrl' => '',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  Onelogin Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Public x509 certificate of the IdP
                'x509cert' => 'MIIFQjCCBCqgAwIBAgIBAjANBgkqhkiG9w0BAQsFADCBvTELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzELMAkGA1UEChMCVVMxJDAiBgNVBAsTG1VuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlcjE6MDgGA1UEAxMxVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyIFJvb3QgQ0EgKElEPWFEc1JjdWVkKTElMCMGCSqGSIb3DQEJARYWc3NsQGNvbm5leGlvbi5ldnNzbi5kZTAeFw0yMDAzMjAxMDM2NTZaFw0yNTAzMTkxMDM2NTZaMIGmMQswCQYDVQQGEwJVUzELMAkGA1UECBMCVVMxCzAJBgNVBAcTAlVTMQswCQYDVQQKEwJVUzEkMCIGA1UECxMbVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyMSMwIQYDVQQDExp1Y3Mtc3NvLmNvbm5leGlvbi5ldnNzbi5kZTElMCMGCSqGSIb3DQEJARYWc3NsQGNvbm5leGlvbi5ldnNzbi5kZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAK9NAAogilphnZmgypebJWG54RDbDa+41cc5jnND75OtDFbMQ2Yrg3UR6x9zGqMBK7j13MhuMc9hItF8ATJwYm9eX4gWn7DshllBGKSTdgLEEPxf3zISpKKgQtbVP/k78F3nzepEpYkD/DjCi7AaBCP9TeC8Lt+v2jQl3lsstzmCsb89pQmxDwVRrf2b/hGayiuxwpcI+y5qgN1Zj33EPeWIqbKhDyPxKgDf4t44rhGKTJ9P92aaL2SIIws8JmUjo4gkBCwwJMZWyFpn2nPjb62r800VXJvWK5L40Cmyr8oY6mnOd5GejyBpgspazlRJDL97jelJQqvh7pUnPQhMXksCAwEAAaOCAWAwggFcMAkGA1UdEwQCMAAwHQYDVR0OBBYEFEtF5CQUgCXwTSfG7LPHzhZMdi3WMIHyBgNVHSMEgeowgeeAFO5cWZwCe/k1BTegP3bpTvqyLwYnoYHDpIHAMIG9MQswCQYDVQQGEwJVUzELMAkGA1UECBMCVVMxCzAJBgNVBAcTAlVTMQswCQYDVQQKEwJVUzEkMCIGA1UECxMbVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyMTowOAYDVQQDEzFVbml2ZW50aW9uIENvcnBvcmF0ZSBTZXJ2ZXIgUm9vdCBDQSAoSUQ9YURzUmN1ZWQpMSUwIwYJKoZIhvcNAQkBFhZzc2xAY29ubmV4aW9uLmV2c3NuLmRlggkA7DcM5i1JUoowCwYDVR0PBAQDAgXgMC4GA1UdEQQnMCWCGnVjcy1zc28uY29ubmV4aW9uLmV2c3NuLmRlggd1Y3Mtc3NvMA0GCSqGSIb3DQEBCwUAA4IBAQA8BzAI7Dpw9PZhLRKRGuuTRsHHSsLNZ3l0QH3dFAilmS8/VFvVLwAKNl2zcSe4s7+uwu9xAVgABXPLaYSdw2eoOL4yn8EUzStawJEi/ys1bNUQLcnDUZX7fRVT8bzqr17hfjdwbRHsR5k4F2xOlDjZCLCOeLqBze8DYonAo7s03HsXL1Jn21PN7YDyO3X0g2oGu7IcsIGP6hkv7e88p/gRSEBATjQMRHKCn8nujpXW0DvG/mKfa2+sRy7lGLpxU00Gt9/IV2BWWJxfz28M267FkfFsgfqjG7G1Z7OxHhPCOlRcPJedzup8ibOjGhOp1VS+PzU6HQbxa7Wsf4MYa6Fq',
                /*
                 *  Instead of use the whole x509cert you can use a fingerprint in
                 *  order to validate the SAMLResponse, but we don't recommend to use
                 *  that method on production since is exploitable by a collision
                 *  attack.
                 *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
                 *   or add for example the -sha256 , -sha384 or -sha512 parameter)
                 *
                 *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
                 *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
                 *  'sha1' is the default value.
                 */
                // 'certFingerprint' => '',
                // 'certFingerprintAlgorithm' => 'sha1',

                /* In some scenarios the IdP uses different certificates for
                 * signing/encryption, or is under key rollover phase and more
                 * than one certificate is published on IdP metadata.
                 * In order to handle that the toolkit offers that parameter.
                 * (when used, 'x509cert' and 'certFingerprint' values are
                 * ignored).
                 */
                // 'x509certMulti' => array(
                //      'signing' => array(
                //          0 => '<cert1-string>',
                //      ),
                //      'encryption' => array(
                //          0 => '<cert2-string>',
                //      )
                // ),
            ),
        ];
    }
}