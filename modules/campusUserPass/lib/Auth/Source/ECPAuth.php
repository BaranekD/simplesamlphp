<?php

declare(strict_types=1);

namespace SimpleSAML\Module\campusUserPass\Auth\Source;

use SimpleSAML\Auth\Source;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\AuthSource;
use SimpleSAML\Error\Error;
use SimpleSAML\Error\Exception;
use SimpleSAML\Module;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Utils\HTTP;
use SimpleXMLElement;

class ECPAuth extends UserPassBase
{
    private $sp;
    private $ecpIdpUrl;

    public function __construct($info, $config)
    {
        parent::__construct($info, $config);

        $this->sp = $config['sp'];
        if (empty($this->sp)) {
            throw new Exception('Missing mandatory configuration option \'sp\'.');
        }

        $this->ecpIdpUrl = $config['ecpIdpUrl'];
        if (empty($this->ecpIdpUrl)) {
            throw new Exception('Missing mandatory configuration option \'ecpIdpUrl\'.');
        }
    }

    public function authenticate(&$state)
    {
        $state[self::AUTHID] = $this->authId;
        $id = State::saveState($state, self::STAGEID);

        $url = Module::getModuleURL('campusUserPass/campusUserPass.php');
        $params = ['AuthState' => $id];

        if (isset($_POST['username']) && isset($_POST['password'])) {
            $params['username'] = $_POST['username'];
            $params['password'] = $_POST['password'];
        }

        HTTP::redirectTrustedURL($url, $params);
    }

    protected function login($username, $password)
    {
        $source = Source::getById($this->sp);
        if ($source === null) {
            throw new AuthSource($this->sp, 'Could not find authentication source.');
        }

        $spconfig = $source-> getMetadata();

        $xml = new SimpleXMLElement('<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"></S:Envelope>');

        $xmlBody = $xml->addChild('S:Body');
        $xmlRequest = $xmlBody->addChild('samlp:AuthnRequest', null, 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xmlRequest->addAttribute('ID', '' . rand() . '');
        $xmlRequest->addAttribute('IssueInstant', '' . gmdate('Y-m-d\TH:i:s\Z', time()) . '');
        $xmlRequest->addAttribute('Version', '2.0');
        $xmlRequest->addAttribute('AssertionConsumerServiceIndex', '2');

        $xmlRequest->addChild('saml:Issuer', $spconfig->getString('entityid'), 'urn:oasis:names:tc:SAML:2.0:assertion');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->ecpIdpUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml->asXML());
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseXml = new SimpleXMLElement($response);
        $responseXml->registerXPathNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
        $responseXml->registerXPathNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $responseXml->registerXPathNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        $statusCodeValue = $responseXml
            ->xpath("/SOAP-ENV:Envelope/SOAP-ENV:Body/samlp:Response/samlp:Status/samlp:StatusCode")[0]
            ->attributes()
            ->Value;

        if (strpos($statusCodeValue->asXML(), 'Success') === false) {
            throw new Error('WRONGUSERPASS');
        }

        $attributeStatement = $responseXml->xpath(
            "/SOAP-ENV:Envelope/SOAP-ENV:Body/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute"
        );

        $result = [];
        foreach ($attributeStatement as $child) {
            $attrValue = (string) $child->xpath('saml:AttributeValue')[0];
            $attrName = (string) $child->attributes()->Name;

            $result[$attrName] = [$attrValue];
        }

        return $result;
    }
}
